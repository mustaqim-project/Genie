<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AttendanceLog;
use App\Models\BiometricConsent;
use App\Models\CashAdvance;
use App\Models\Employee;
use App\Models\EmploymentContract;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\PayrollRun;
use App\Models\Payslip;
use App\Models\PayslipLine;
use App\Models\Shift;
use App\Models\ShipmentPic;
use App\Models\User;
use App\Models\WorkLocation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class HrmController extends Controller implements HasMiddleware
{
    /**
     * RBAC Middleware mapping for HRM operations.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:hrm.view', only: ['index', 'showEmployee', 'showPayslip']),
            new Middleware('permission:hrm.manage', except: [
                'index',
                'showEmployee',
                'showPayslip',
                'clockAttendance',
                'storeConsent',
                'storeLeave',
                'storeOvertime',
            ]),
        ];
    }

    /**
     * Tampilan Utama Dashboard & Modul Operasional HRM.
     */
    public function index(Request $request)
    {
        $activeTab = $request->query('tab', 'employees');
        $search = $request->query('search');
        $teamFilter = $request->query('team');

        // 1. Data Pegawai
        $employeesQuery = Employee::with([
            'user.roles',
            'latestContract',
            'biometricConsent',
            'activeAssetAssignments.asset',
        ]);

        if ($search) {
            $employeesQuery->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%");
            });
        }

        if ($teamFilter) {
            $employeesQuery->where('team', $teamFilter);
        }

        $employees = $employeesQuery->orderBy('full_name')->paginate(12)->withQueryString();

        // 2. Metrics & Statistik Bento Ringkasan
        $totalEmployees = Employee::active()->count();
        $todayClockIns = AttendanceLog::today()->clockIn()->valid()->count();
        $attendanceRate = $totalEmployees > 0 ? round(($todayClockIns / $totalEmployees) * 100) : 100;
        $expiringContracts = EmploymentContract::expiringSoon(30)->count();
        $pendingLeaves = LeaveRequest::pending()->count();
        $pendingOvertimes = OvertimeRequest::pending()->count();
        $latestPayroll = PayrollRun::with('payslips')->latest()->first();
        $assignedAssets = Asset::assigned()->count();
        $totalAssets = Asset::count();

        $metrics = [
            'total_employees' => $totalEmployees,
            'today_clock_ins' => $todayClockIns,
            'attendance_rate' => $attendanceRate,
            'expiring_contracts' => $expiringContracts,
            'pending_leaves' => $pendingLeaves,
            'pending_overtimes' => $pendingOvertimes,
            'latest_payroll' => $latestPayroll,
            'assigned_assets' => $assignedAssets,
            'total_assets' => $totalAssets,
        ];

        // 3. Data Tab Presensi
        $recentAttendances = AttendanceLog::with(['employee', 'location'])
            ->latest('at')
            ->take(20)
            ->get();

        // 4. Data Tab Cuti & Alih Tugas PIC
        $leaveRequests = LeaveRequest::with(['employee.user', 'approver'])
            ->latest('created_at')
            ->take(20)
            ->get();

        // 5. Data Tab Lembur & Kasbon
        $overtimeRequests = OvertimeRequest::with(['employee', 'approver'])
            ->latest('created_at')
            ->take(15)
            ->get();

        $cashAdvances = CashAdvance::with('employee')
            ->active()
            ->latest('created_at')
            ->get();

        // 6. Data Tab Payroll
        $payrollRuns = PayrollRun::with('payslips.employee')
            ->latest('created_at')
            ->take(6)
            ->get();

        // 7. Data Tab Aset
        $assets = Asset::with(['currentAssignment.employee', 'assignments'])
            ->orderBy('code')
            ->get();

        // 8. Master Data Pendukung
        $workLocations = WorkLocation::active()->get();
        $shifts = Shift::all();
        $teams = ['DOCUMENT', 'DO', 'TRAFFIC', 'FIELD', 'OPERATIONS', 'FINANCE', 'HR', 'IT', 'CS'];

        return view('hrm.index', compact(
            'employees',
            'metrics',
            'activeTab',
            'recentAttendances',
            'leaveRequests',
            'overtimeRequests',
            'cashAdvances',
            'payrollRuns',
            'assets',
            'workLocations',
            'shifts',
            'teams'
        ));
    }

    /**
     * Tambah Pegawai Baru beserta Akun Pengguna & Kontrak Kerja.
     */
    public function storeEmployee(Request $request)
    {
        $validated = $request->validate([
            'nik' => 'required|string|unique:employees,nik|max:50',
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email|max:255',
            'position' => 'required|string|max:150',
            'team' => 'required|string|max:50',
            'start_date' => 'required|date',
            'base_salary' => 'required|numeric|min:0',
            'contract_kind' => 'required|in:PKWT,PKWTT,INTERNSHIP',
            'contract_end_date' => 'nullable|date|after:start_date',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $user = null;
            if (!empty($validated['email'])) {
                $user = User::create([
                    'name' => $validated['full_name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($request->input('password', 'password123')),
                    'job_title' => $validated['position'],
                    'department' => $validated['team'],
                    'is_active' => true,
                ]);
            }

            $employee = Employee::create([
                'nik' => $validated['nik'],
                'user_id' => $user?->id,
                'full_name' => $validated['full_name'],
                'position' => $validated['position'],
                'team' => $validated['team'],
                'start_date' => $validated['start_date'],
                'status' => 'ACTIVE',
                'base_salary' => $validated['base_salary'],
            ]);

            if ($user) {
                $user->employee_id = $employee->id;
                $user->save();
            }

            // Buat Kontrak Kerja Awal
            EmploymentContract::create([
                'employee_id' => $employee->id,
                'kind' => $validated['contract_kind'],
                'number' => 'CTR/' . $employee->nik . '/' . date('Y'),
                'start_date' => $validated['start_date'],
                'end_date' => $validated['contract_kind'] === 'PKWT' ? ($validated['contract_end_date'] ?? Carbon::parse($validated['start_date'])->addYear()) : null,
            ]);

            // Buat Saldo Cuti Tahunan Default
            LeaveBalance::create([
                'employee_id' => $employee->id,
                'year' => (int) date('Y'),
                'kind' => 'TAHUNAN',
                'balance' => 12.0,
            ]);
        });

        return redirect()->route('hrm.index', ['tab' => 'employees'])
            ->with('success', "Pegawai {$validated['full_name']} ({$validated['nik']}) berhasil didaftarkan.");
    }

    /**
     * Tampilkan Detail Profil Pegawai.
     */
    public function showEmployee(Employee $employee)
    {
        $employee->load([
            'user.roles',
            'contracts',
            'documents',
            'biometricConsent',
            'leaveBalances',
            'activeAssetAssignments.asset',
            'payslips.run',
        ]);

        return response()->json($employee);
    }

    /**
     * Perbarui Data Pegawai.
     */
    public function updateEmployee(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'position' => 'required|string|max:150',
            'team' => 'required|string|max:50',
            'base_salary' => 'required|numeric|min:0',
            'status' => 'required|in:ACTIVE,INACTIVE,RESIGNED,TERMINATED',
        ]);

        $employee->update($validated);

        if ($employee->user) {
            $employee->user->update([
                'name' => $validated['full_name'],
                'job_title' => $validated['position'],
                'department' => $validated['team'],
                'is_active' => $validated['status'] === 'ACTIVE',
            ]);
        }

        return redirect()->route('hrm.index', ['tab' => 'employees'])
            ->with('success', "Data pegawai {$employee->full_name} berhasil diperbarui.");
    }

    /**
     * Rekam Absensi GPS Geofencing & Verifikasi Wajah.
     * Sesuai UU PDP No. 27/2022, persetujuan pemrosesan data biometrik wajib valid.
     */
    public function clockAttendance(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'kind' => 'required|in:CLOCK_IN,CLOCK_OUT',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'face_match' => 'nullable|numeric|between:0,1',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);

        // 1. Kepatuhan UU PDP No. 27/2022: Validasi Persetujuan Biometrik
        if (!$employee->hasValidBiometricConsent()) {
            return back()->with('error', "Persetujuan pemrosesan data biometrik wajah (UU PDP No. 27/2022) untuk {$employee->full_name} belum disetujui atau sudah dicabut.");
        }

        // 2. Geofencing GPS Radius Check
        $locations = WorkLocation::active()->get();
        $matchedLocation = null;
        $isWithinAnyGeofence = false;

        foreach ($locations as $loc) {
            if ($loc->isWithinRadius($validated['lat'], $validated['lng'])) {
                $matchedLocation = $loc;
                $isWithinAnyGeofence = true;
                break;
            }
        }

        // Simpan log absensi
        $log = AttendanceLog::create([
            'employee_id' => $employee->id,
            'kind' => $validated['kind'],
            'at' => now(),
            'lat' => $validated['lat'],
            'lng' => $validated['lng'],
            'location_id' => $matchedLocation?->id,
            'face_match' => $validated['face_match'] ?? 0.965,
            'valid' => $isWithinAnyGeofence,
        ]);

        $locationName = $matchedLocation ? $matchedLocation->name : 'Luar Radius Kantor/Pelabuhan TPS';
        $statusMsg = $isWithinAnyGeofence ? 'Valid di ' . $locationName : 'Peringatan: Koordinat berada di ' . $locationName;

        return redirect()->route('hrm.index', ['tab' => 'attendance'])
            ->with($isWithinAnyGeofence ? 'success' : 'warning', "Presensi {$validated['kind']} {$employee->full_name} berhasil direkam. ({$statusMsg})");
    }

    /**
     * Rekam Formulir Persetujuan Pemrosesan Biometrik Wajah UU PDP No. 27/2022.
     */
    public function storeConsent(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'agree' => 'required|accepted',
        ]);

        BiometricConsent::updateOrCreate(
            ['employee_id' => $validated['employee_id']],
            [
                'consent_at' => now(),
                'revoked_at' => null,
                'retention_until' => now()->addYears(2)->toDateString(),
            ]
        );

        return redirect()->route('hrm.index', ['tab' => 'attendance'])
            ->with('success', 'Persetujuan pemrosesan data biometrik wajah UU PDP No. 27/2022 berhasil dicatat.');
    }

    /**
     * Pengajuan Permohonan Cuti Karyawan.
     */
    public function storeLeave(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'kind' => 'required|string|in:TAHUNAN,SAKIT,MELAHIRKAN,IZIN_KELUARGA',
            'from_date' => 'required|date|after_or_equal:today',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        LeaveRequest::create([
            'employee_id' => $validated['employee_id'],
            'kind' => $validated['kind'],
            'from_date' => $validated['from_date'],
            'to_date' => $validated['to_date'],
            'status' => 'PENDING',
        ]);

        return redirect()->route('hrm.index', ['tab' => 'leaves'])
            ->with('success', 'Permohonan cuti berhasil diajukan dan menunggu persetujuan atasan.');
    }

    /**
     * Setujui Cuti & Otomatis Alihkan Tugas PIC Operasional Forwarder ke Backup PIC.
     */
    public function approveLeave(LeaveRequest $leaveRequest)
    {
        if (!$leaveRequest->isPending()) {
            return back()->with('error', 'Permohonan cuti ini sudah diproses sebelumnya.');
        }

        $employee = $leaveRequest->employee;
        $reassignedCount = 0;

        DB::transaction(function () use ($leaveRequest, $employee, &$reassignedCount) {
            // 1. Setujui Permohonan Cuti
            $leaveRequest->update([
                'status' => 'APPROVED',
                'approver_id' => auth()->id(),
            ]);

            // 2. Kurangi Saldo Hak Cuti
            $duration = $leaveRequest->duration_days;
            $leaveBalance = LeaveBalance::where('employee_id', $employee->id)
                ->where('year', (int) date('Y'))
                ->first();

            if ($leaveBalance && $leaveRequest->kind === 'TAHUNAN') {
                $leaveBalance->deduct($duration);
            }

            // 3. LOGISTIK FORWARDER AUTO-REASSIGN:
            // Jika pegawai ini adalah PIC operasional pada shipment aktif, alihkan ke backup_id
            if ($employee->user_id) {
                $activePics = ShipmentPic::where('user_id', $employee->user_id)
                    ->whereNotNull('backup_id')
                    ->get();

                foreach ($activePics as $pic) {
                    if ($pic->reassignToBackup()) {
                        $reassignedCount++;
                    }
                }
            }
        });

        $reassignNotice = $reassignedCount > 0
            ? " Berhasil mengalihkan {$reassignedCount} penugasan PIC shipment ke staf cadangan (backup PIC) secara otomatis."
            : '';

        return redirect()->route('hrm.index', ['tab' => 'leaves'])
            ->with('success', "Permohonan cuti {$employee->full_name} telah disetujui.{$reassignNotice}");
    }

    /**
     * Tolak Permohonan Cuti.
     */
    public function rejectLeave(Request $request, LeaveRequest $leaveRequest)
    {
        $leaveRequest->update([
            'status' => 'REJECTED',
            'approver_id' => auth()->id(),
        ]);

        return redirect()->route('hrm.index', ['tab' => 'leaves'])
            ->with('info', "Permohonan cuti {$leaveRequest->employee->full_name} telah ditolak.");
    }

    /**
     * Pengajuan Lembur.
     */
    public function storeOvertime(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'work_date' => 'required|date',
            'hours' => 'required|numeric|min:0.5|max:12',
        ]);

        OvertimeRequest::create([
            'employee_id' => $validated['employee_id'],
            'work_date' => $validated['work_date'],
            'hours' => $validated['hours'],
            'status' => 'PENDING',
        ]);

        return redirect()->route('hrm.index', ['tab' => 'overtime'])
            ->with('success', 'Pengajuan lembur berhasil dicatat.');
    }

    /**
     * Setujui Lembur.
     */
    public function approveOvertime(OvertimeRequest $overtimeRequest)
    {
        $overtimeRequest->update([
            'status' => 'APPROVED',
            'approver_id' => auth()->id(),
        ]);

        return redirect()->route('hrm.index', ['tab' => 'overtime'])
            ->with('success', "Lembur {$overtimeRequest->employee->full_name} ({$overtimeRequest->hours} jam) telah disetujui.");
    }

    /**
     * Tolak Lembur.
     */
    public function rejectOvertime(OvertimeRequest $overtimeRequest)
    {
        $overtimeRequest->update([
            'status' => 'REJECTED',
            'approver_id' => auth()->id(),
        ]);

        return redirect()->route('hrm.index', ['tab' => 'overtime'])
            ->with('info', 'Pengajuan lembur telah ditolak.');
    }

    /**
     * Pengajuan Kasbon Pegawai.
     */
    public function storeCashAdvance(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:100000',
            'installments' => 'required|integer|min:1|max:12',
        ]);

        CashAdvance::create([
            'employee_id' => $validated['employee_id'],
            'amount' => $validated['amount'],
            'installments' => $validated['installments'],
            'balance' => $validated['amount'],
            'status' => 'ACTIVE',
        ]);

        return redirect()->route('hrm.index', ['tab' => 'overtime'])
            ->with('success', 'Kasbon pegawai berhasil diterbitkan.');
    }

    /**
     * Generate Siklus Payroll Otomatis (Perhitungan Kompensasi Forwarder & Potongan).
     */
    public function generatePayrollRun(Request $request)
    {
        $validated = $request->validate([
            'period' => 'required|string|regex:/^\d{4}-\d{2}$/|unique:payroll_runs,period',
        ]);

        $period = $validated['period'];

        DB::transaction(function () use ($period) {
            $payrollRun = PayrollRun::create([
                'period' => $period,
                'status' => 'DRAFT',
                'approved_by_id' => null,
            ]);

            $activeEmployees = Employee::active()->get();

            foreach ($activeEmployees as $emp) {
                $base = (float) $emp->base_salary;
                $allowanceMeal = 1200000;
                $allowanceTransport = 800000;
                $allowanceCustoms = ($emp->team === 'DOCUMENT' || $emp->team === 'FIELD') ? 1500000 : 0;
                $gross = $base + $allowanceMeal + $allowanceTransport + $allowanceCustoms;

                $deductBpjsTk = round($base * 0.02); // 2% JHT
                $deductBpjsKes = round($base * 0.01); // 1% BPJS Kes

                // Cek cicilan kasbon aktif
                $activeAdvance = CashAdvance::where('employee_id', $emp->id)->active()->first();
                $deductKasbon = 0;
                if ($activeAdvance && $activeAdvance->installments > 0) {
                    $installmentValue = round($activeAdvance->amount / $activeAdvance->installments);
                    $deductKasbon = min($installmentValue, (float) $activeAdvance->balance);
                }

                $deductions = $deductBpjsTk + $deductBpjsKes + $deductKasbon;
                $net = $gross - $deductions;

                $slip = Payslip::create([
                    'run_id' => $payrollRun->id,
                    'employee_id' => $emp->id,
                    'gross' => $gross,
                    'net' => $net,
                    'deductions' => $deductions,
                ]);

                // Line items rincian
                PayslipLine::create(['payslip_id' => $slip->id, 'label' => 'Gaji Pokok Pegawai', 'kind' => 'ALLOWANCE', 'amount' => $base]);
                PayslipLine::create(['payslip_id' => $slip->id, 'label' => 'Uang Makan & Operasional Harian', 'kind' => 'ALLOWANCE', 'amount' => $allowanceMeal]);
                PayslipLine::create(['payslip_id' => $slip->id, 'label' => 'Tunjangan Transportasi Pelabuhan', 'kind' => 'ALLOWANCE', 'amount' => $allowanceTransport]);

                if ($allowanceCustoms > 0) {
                    PayslipLine::create(['payslip_id' => $slip->id, 'label' => 'Insentif Sertifikasi Ahli PPJK & Behandle', 'kind' => 'ALLOWANCE', 'amount' => $allowanceCustoms]);
                }

                PayslipLine::create(['payslip_id' => $slip->id, 'label' => 'Potongan Iuran BPJS Ketenagakerjaan (2%)', 'kind' => 'DEDUCTION', 'amount' => $deductBpjsTk]);
                PayslipLine::create(['payslip_id' => $slip->id, 'label' => 'Potongan Iuran BPJS Kesehatan (1%)', 'kind' => 'DEDUCTION', 'amount' => $deductBpjsKes]);

                if ($deductKasbon > 0) {
                    PayslipLine::create(['payslip_id' => $slip->id, 'label' => 'Potongan Cicilan Kasbon Pegawai', 'kind' => 'DEDUCTION', 'amount' => $deductKasbon]);
                }
            }
        });

        return redirect()->route('hrm.index', ['tab' => 'payroll'])
            ->with('success', "Periode payroll {$period} berhasil digenerate dalam status DRAFT.");
    }

    /**
     * Setujui Periode Payroll.
     */
    public function approvePayrollRun(PayrollRun $payrollRun)
    {
        $payrollRun->update([
            'status' => 'APPROVED',
            'approved_by_id' => auth()->id(),
        ]);

        return redirect()->route('hrm.index', ['tab' => 'payroll'])
            ->with('success', "Payroll periode {$payrollRun->period} berhasil disetujui.");
    }

    /**
     * Pencairan Resmi Payroll (Disburse) & Potong Cicilan Kasbon.
     */
    public function disbursePayrollRun(PayrollRun $payrollRun)
    {
        DB::transaction(function () use ($payrollRun) {
            $payrollRun->update(['status' => 'DISBURSED']);

            // Potong saldo cicilan kasbon yang tercatat di payslip lines
            foreach ($payrollRun->payslips as $slip) {
                $kasbonLine = $slip->lines()->where('label', 'like', '%Kasbon%')->first();
                if ($kasbonLine) {
                    $advance = CashAdvance::where('employee_id', $slip->employee_id)->active()->first();
                    if ($advance) {
                        $advance->deductInstallment((float) $kasbonLine->amount);
                    }
                }
            }
        });

        return redirect()->route('hrm.index', ['tab' => 'payroll'])
            ->with('success', "Payroll periode {$payrollRun->period} berhasil dicairkan (DISBURSED).");
    }

    /**
     * Tampilkan Detail Slip Gaji.
     */
    public function showPayslip(Payslip $payslip)
    {
        $payslip->load(['employee', 'run', 'lines']);
        return response()->json($payslip);
    }

    /**
     * Tambah Aset Inventaris Baru.
     */
    public function storeAsset(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:assets,code|max:50',
            'name' => 'required|string|max:255',
            'purchase_value' => 'nullable|numeric|min:0',
        ]);

        Asset::create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'purchase_value' => $validated['purchase_value'] ?? 0,
            'status' => 'AVAILABLE',
        ]);

        return redirect()->route('hrm.index', ['tab' => 'assets'])
            ->with('success', "Aset inventaris {$validated['code']} berhasil ditambahkan.");
    }

    /**
     * Serah Terima Peminjaman Aset ke Pegawai.
     */
    public function assignAsset(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'assigned_at' => 'required|date',
        ]);

        DB::transaction(function () use ($asset, $validated) {
            AssetAssignment::create([
                'asset_id' => $asset->id,
                'employee_id' => $validated['employee_id'],
                'assigned_at' => $validated['assigned_at'],
                'returned_at' => null,
            ]);

            $asset->update(['status' => 'ASSIGNED']);
        });

        return redirect()->route('hrm.index', ['tab' => 'assets'])
            ->with('success', "Aset {$asset->code} berhasil diserahterimakan.");
    }

    /**
     * Pengembalian Aset Inventaris.
     */
    public function returnAsset(AssetAssignment $assignment)
    {
        DB::transaction(function () use ($assignment) {
            $assignment->update(['returned_at' => now()->toDateString()]);
            $assignment->asset->update(['status' => 'AVAILABLE']);
        });

        return redirect()->route('hrm.index', ['tab' => 'assets'])
            ->with('success', "Aset {$assignment->asset->code} berhasil dikembalikan ke status AVAILABLE.");
    }

    /**
     * Tambah Titik Geofencing Lokasi Kerja (Kantor / Pelabuhan).
     */
    public function storeLocation(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'radius_m' => 'required|integer|min:20|max:5000',
        ]);

        WorkLocation::create([
            'name' => $validated['name'],
            'lat' => $validated['lat'],
            'lng' => $validated['lng'],
            'radius_m' => $validated['radius_m'],
            'valid_from' => now()->toDateString(),
        ]);

        return redirect()->route('hrm.index', ['tab' => 'attendance'])
            ->with('success', "Titik geofencing {$validated['name']} berhasil ditambahkan.");
    }
}
