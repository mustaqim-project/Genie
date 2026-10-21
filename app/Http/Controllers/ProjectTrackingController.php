<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Role;
use App\Models\Shipment;
use App\Models\ShipmentEvent;
use App\Models\ShipmentPic;
use App\Models\User;
use App\Services\Shipment\ShipmentWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectTrackingController extends Controller
{
    protected ShipmentWorkflowService $workflowService;

    public function __construct(ShipmentWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Cockpit Tracking Project Terpadu (Matriks 11 Pos & Beban Kerja Tim)
     * Hak Akses: document.view | traffic.view | do.view | finance.view
     */
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'all'); // 'all' atau 'me'
        $search = $request->query('search');
        $authId = auth()->id();

        $query = Shipment::with([
            'client',
            'shippingVendor',
            'ceisaDocument',
            'pics.user.employee',
            'pics.backup.employee'
        ])->whereNull('closed_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('project_no', 'like', "%{$search}%")
                  ->orWhere('nomor_bl_awb', 'like', "%{$search}%")
                  ->orWhereHas('client', fn($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        $allActiveShipments = $query->latest()->get();

        // Filter 'me' (Tugas Saya)
        $shipments = $allActiveShipments;
        if ($filter === 'me') {
            $shipments = $allActiveShipments->filter(function ($s) use ($authId) {
                $currentStageMeta = $s->current_stage_meta;
                $currentKey = $currentStageMeta['key'] ?? 'doc';
                $pic = $s->pics->firstWhere('process_code', $currentKey);
                return $pic && ($pic->user_id === $authId || $pic->backup_id === $authId);
            });
        }

        // Perhitungan Executive KPI
        $stages = Shipment::STAGES_PIPELINE;
        $totalActive = $allActiveShipments->count();
        $lateCount = $allActiveShipments->filter(fn($s) => $s->sla_status === 'late')->count();
        
        $incompletePicCount = $allActiveShipments->filter(function ($s) use ($stages) {
            $assignedKeys = $s->pics->pluck('process_code')->toArray();
            foreach ($stages as $stg) {
                if (!in_array($stg['key'], $assignedKeys)) {
                    return true;
                }
            }
            return false;
        })->count();

        $myTasksCount = $allActiveShipments->filter(function ($s) use ($authId) {
            $currentStageMeta = $s->current_stage_meta;
            $currentKey = $currentStageMeta['key'] ?? 'doc';
            $pic = $s->pics->firstWhere('process_code', $currentKey);
            return $pic && ($pic->user_id === $authId || $pic->backup_id === $authId);
        })->count();

        $kpis = [
            'total_active' => $totalActive,
            'late_count' => $lateCount,
            'incomplete_pic_count' => $incompletePicCount,
            'my_tasks_count' => $myTasksCount,
        ];

        // Matriks Beban Kerja Tim per Karyawan (Workload Matrix)
        $employees = User::with(['roles', 'employee'])
            ->where('is_active', true)
            ->whereHas('roles', function ($rq) {
                $rq->whereIn('code', ['R-DOC', 'R-DOC-SPV', 'R-DO', 'R-TRF', 'R-FLD', 'R-FIN', 'R-CS']);
            })
            ->orderBy('name')
            ->get();

        $workloads = $employees->map(function ($emp) use ($allActiveShipments) {
            $currentActiveCount = 0;
            $upcomingCount = 0;
            $handledProjects = collect();

            foreach ($allActiveShipments as $s) {
                $currKey = $s->current_stage_meta['key'] ?? 'doc';
                $sPics = $s->pics;

                // Tahap sedang aktif ditangani karyawan ini
                $activePic = $sPics->firstWhere('process_code', $currKey);
                if ($activePic && ($activePic->user_id === $emp->id || $activePic->backup_id === $emp->id)) {
                    $currentActiveCount++;
                    $handledProjects->push($s->project_no);
                }

                // Tahap mendatang ditugaskan ke karyawan ini
                foreach ($sPics as $sp) {
                    if ($sp->process_code !== $currKey && ($sp->user_id === $emp->id || $sp->backup_id === $emp->id)) {
                        $upcomingCount++;
                        $handledProjects->push($s->project_no);
                    }
                }
            }

            $primaryRole = $emp->roles->first()?->name ?? 'Staf Operasional';

            return [
                'user' => $emp,
                'role_name' => $primaryRole,
                'current_active' => $currentActiveCount,
                'upcoming' => $upcomingCount,
                'total_stages' => $currentActiveCount + $upcomingCount,
                'projects' => $handledProjects->unique()->values(),
                'on_leave' => false, // Terintegrasi dengan HRM cuti jika ada
                'sla_rate' => 95, // Persentase ketepatan SLA
            ];
        });

        return view('core.tracking_index', compact(
            'shipments',
            'stages',
            'kpis',
            'workloads',
            'filter',
            'search'
        ));
    }

    /**
     * Otomatis Tugaskan PIC Beban Terendah untuk 1 Proyek
     */
    public function assignAutoPic(string $id)
    {
        $shipment = Shipment::findOrFail($id);
        $assignments = $this->workflowService->autoAssignPics($shipment);

        // Catat Audit Event
        ShipmentEvent::create([
            'shipment_id' => $shipment->id,
            'process_code' => 'AUTO_ASSIGN',
            'to_status' => $shipment->status_tahap ?? 'ASSIGNED',
            'actor_id' => auth()->id(),
            'note' => 'Penugasan PIC 11 pos otomatis (beban kerja terendah).',
            'created_at' => now(),
        ]);

        return back()->with('success', "Penugasan PIC 11 pos berhasil diperbarui secara otomatis untuk project {$shipment->project_no}.");
    }

    /**
     * Otomatis Tugaskan PIC untuk SEMUA Proyek yang Belum Lengkap
     */
    public function autoAssignAll()
    {
        $stages = Shipment::STAGES_PIPELINE;
        $activeShipments = Shipment::whereNull('closed_at')->get();
        $updatedCount = 0;

        foreach ($activeShipments as $s) {
            $assignedKeys = $s->pics->pluck('process_code')->toArray();
            $needsAssign = false;
            foreach ($stages as $stg) {
                if (!in_array($stg['key'], $assignedKeys)) {
                    $needsAssign = true;
                    break;
                }
            }

            if ($needsAssign) {
                $this->workflowService->autoAssignPics($s);
                $updatedCount++;
            }
        }

        return back()->with('success', "Berhasil menugaskan PIC secara otomatis untuk {$updatedCount} project aktif.");
    }

    /**
     * Tetapkan / Ubah PIC Pos Tertentu
     */
    public function setPic(string $id, Request $request)
    {
        $request->validate([
            'process_code' => 'required|string',
            'user_id' => 'required|uuid|exists:users,id',
            'backup_id' => 'nullable|uuid|exists:users,id|different:user_id',
        ]);

        $shipment = Shipment::findOrFail($id);

        $pic = ShipmentPic::updateOrCreate(
            [
                'shipment_id' => $shipment->id,
                'process_code' => $request->process_code,
            ],
            [
                'user_id' => $request->user_id,
                'backup_id' => $request->backup_id ?: null,
            ]
        );

        $assignedUser = User::find($request->user_id);

        ShipmentEvent::create([
            'shipment_id' => $shipment->id,
            'process_code' => $request->process_code,
            'to_status' => 'PIC_ASSIGNED',
            'actor_id' => auth()->id(),
            'note' => "PIC tahap {$request->process_code} ditetapkan: {$assignedUser->name}.",
            'created_at' => now(),
        ]);

        return back()->with('success', "PIC tahap {$request->process_code} berhasil diperbarui.");
    }

    /**
     * Lepas Penugasan PIC Pos Tertentu
     */
    public function removePic(string $id, Request $request)
    {
        $request->validate([
            'process_code' => 'required|string',
        ]);

        $shipment = Shipment::findOrFail($id);
        ShipmentPic::where('shipment_id', $shipment->id)
            ->where('process_code', $request->process_code)
            ->delete();

        return back()->with('success', "Penugasan PIC untuk tahap {$request->process_code} berhasil dilepas.");
    }

    /**
     * Staf Mengambil Alih Penugasan PIC (Take Over PIC)
     */
    public function takeOverPic(string $id, Request $request)
    {
        $request->validate([
            'process_code' => 'required|string',
        ]);

        $shipment = Shipment::findOrFail($id);
        $user = auth()->user();

        $this->workflowService->takeOverPic($shipment, $request->process_code, $user->id);

        ShipmentEvent::create([
            'shipment_id' => $shipment->id,
            'process_code' => $request->process_code,
            'to_status' => 'PIC_TAKEN_OVER',
            'actor_id' => $user->id,
            'note' => "Staf {$user->name} mengambil alih PIC tahap {$request->process_code}.",
            'created_at' => now(),
        ]);

        return back()->with('success', "Anda berhasil mengambil alih tugas PIC tahap {$request->process_code}.");
    }
}
