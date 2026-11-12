<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Quotation;
use App\Models\QuotationLine;
use App\Models\SalesOrder;
use App\Services\Shipment\ShipmentWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class QuotationController extends Controller
{
    protected ShipmentWorkflowService $workflowService;

    public function __construct(ShipmentWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }
    /**
     * Daftar Penawaran Harga (CRM)
     * Hak Akses: crm.view
     */
    public function index(Request $request)
    {
        Gate::authorize('crm.view');

        $query = Quotation::with(['client', 'lines', 'approver']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $quotations = $query->latest()->paginate(10);
        $clients = Client::orderBy('name')->get();
        $stats = [
            'total' => Quotation::count(),
            'draft' => Quotation::where('status', 'DRAFT')->count(),
            'sent' => Quotation::where('status', 'SENT')->count(),
            'accepted' => Quotation::where('status', 'ACCEPTED')->count(),
            'pending_approval' => Quotation::where('discount_approval_status', 'PENDING_APPROVAL')->count(),
        ];

        return view('crm.quotations_index', compact('quotations', 'clients', 'stats'));
    }

    /**
     * Buat Penawaran Harga Baru Multi-Line (CRM)
     * Hak Akses: crm.deal
     */
    public function store(Request $request)
    {
        Gate::authorize('crm.deal');

        $validated = $request->validate([
            'client_id' => 'required|uuid|exists:clients,id',
            'origin_port' => 'required|string|max:100',
            'destination_port' => 'required|string|max:100',
            'valid_until' => 'nullable|date',
            'terms' => 'nullable|string',
            'lines' => 'nullable|array',
            'lines.*.description' => 'required_with:lines|string',
            'lines.*.quantity' => 'required_with:lines|numeric|min:0.01',
            'lines.*.unit' => 'required_with:lines|string',
            'lines.*.unit_price_idr' => 'required_with:lines|numeric|min:0',
            'lines.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            // Fallback single estimate jika tanpa multi-line
            'total_estimate' => 'nullable|numeric|min:0',
        ]);

        $quotation = DB::transaction(function () use ($validated) {
            $nextNum = Quotation::count() + 1;
            $quotationNumber = 'QUO-' . date('Ym') . '-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

            $hasDiscountExceeded = false;
            $subtotal = 0;
            $totalDiscount = 0;

            if (!empty($validated['lines'])) {
                foreach ($validated['lines'] as $l) {
                    $qty = (float) $l['quantity'];
                    $price = (float) $l['unit_price_idr'];
                    $discPct = (float) ($l['discount_percent'] ?? 0);
                    if ($discPct > 10.0) {
                        $hasDiscountExceeded = true;
                    }
                    $lineSub = $qty * $price;
                    $discAmt = $lineSub * ($discPct / 100);
                    $subtotal += ($lineSub - $discAmt);
                    $totalDiscount += $discAmt;
                }
            } else {
                $subtotal = (float) ($validated['total_estimate'] ?? 0);
            }

            $ppn = $subtotal * 0.11; // PPN 11%
            $total = $subtotal + $ppn;

            $status = $hasDiscountExceeded ? 'DRAFT' : 'SENT';
            $discApproval = $hasDiscountExceeded ? 'PENDING_APPROVAL' : 'NOT_REQUIRED';

            $quo = Quotation::create([
                'quotation_no' => $quotationNumber,
                'client_id' => $validated['client_id'],
                'tanggal' => now(),
                'valid_until' => $validated['valid_until'] ?? now()->addDays(30),
                'pelabuhan_asal' => $validated['origin_port'],
                'pelabuhan_tujuan' => $validated['destination_port'],
                'rincian_biaya' => $validated['lines'] ?? [],
                'subtotal_idr' => $subtotal,
                'ppn_idr' => $ppn,
                'total_idr' => $total,
                'status' => $status,
                'discount_approval_status' => $discApproval,
                'discount_notes' => $hasDiscountExceeded ? 'Diskon melebihi batas toleransi 10% — memerlukan persetujuan Pimpinan (R-OWN).' : null,
                'syarat_ketentuan' => $validated['terms'] ?? null,
                'created_by_user_id' => auth()->id(),
            ]);

            if (!empty($validated['lines'])) {
                foreach ($validated['lines'] as $l) {
                    $qty = (float) $l['quantity'];
                    $price = (float) $l['unit_price_idr'];
                    $discPct = (float) ($l['discount_percent'] ?? 0);
                    $lineSub = $qty * $price;
                    $discAmt = $lineSub * ($discPct / 100);

                    QuotationLine::create([
                        'quotation_id' => $quo->id,
                        'service_code' => $l['service_code'] ?? 'SRV',
                        'description' => $l['description'],
                        'quantity' => $qty,
                        'unit' => $l['unit'] ?? 'SHIPMENT',
                        'unit_price_idr' => $price,
                        'discount_percent' => $discPct,
                        'discount_amount_idr' => $discAmt,
                        'subtotal_idr' => $lineSub - $discAmt,
                    ]);
                }
            }

            return $quo;
        });

        $msg = "Penawaran harga {$quotation->quotation_no} berhasil diterbitkan.";
        if ($quotation->discount_approval_status === 'PENDING_APPROVAL') {
            $msg .= " Menunggu persetujuan diskon (>10%) dari Pimpinan sebelum dapat dikirim.";
        }

        return redirect()->route('crm.quotations.index')->with('success', $msg);
    }

    /**
     * Setujui Diskon Penawaran (Khusus Pimpinan / SuperAdmin R-OWN)
     */
    public function approveDiscount(string $id, Request $request)
    {
        $user = auth()->user();
        $isOwnerOrSys = in_array($user->role ?? '', ['R-OWN', 'R-SYS']) 
            || (method_exists($user, 'hasRole') && $user->hasRole(['R-OWN', 'R-SYS', 'R-ADM']));

        if (!$isOwnerOrSys) {
            abort(403, 'Hanya Pimpinan (R-OWN) yang berwenang menyetujui diskon khusus penawaran.');
        }

        $validated = $request->validate([
            'action' => 'required|in:APPROVE,REJECT',
            'notes' => 'nullable|string',
        ]);

        $quotation = Quotation::findOrFail($id);
        if ($validated['action'] === 'APPROVE') {
            $quotation->update([
                'discount_approval_status' => 'APPROVED',
                'approved_by_user_id' => $user->id,
                'status' => 'SENT',
                'discount_notes' => $validated['notes'] ?? 'Disetujui oleh Pimpinan.',
            ]);
            $msg = "Diskon penawaran {$quotation->quotation_no} disetujui. Status beralih ke SENT.";
        } else {
            $quotation->update([
                'discount_approval_status' => 'REJECTED',
                'approved_by_user_id' => $user->id,
                'status' => 'REJECTED',
                'discount_notes' => $validated['notes'] ?? 'Diskon ditolak oleh Pimpinan.',
            ]);
            $msg = "Diskon penawaran {$quotation->quotation_no} ditolak.";
        }

        return back()->with('success', $msg);
    }

    /**
     * Konversi Penawaran Menjadi Sales Order (SO)
     */
    public function convertToSalesOrder(string $id)
    {
        Gate::authorize('crm.deal');

        $quotation = Quotation::with('client')->findOrFail($id);

        if ($quotation->discount_approval_status === 'PENDING_APPROVAL') {
            return back()->with('error', 'Penawaran ini belum disetujui Pimpinan karena memuat diskon > 10%.');
        }

        $so = DB::transaction(function () use ($quotation) {
            $quotation->update(['status' => 'ACCEPTED']);

            return SalesOrder::create([
                'order_number' => SalesOrder::generateOrderNumber(),
                'quotation_id' => $quotation->id,
                'client_id' => $quotation->client_id,
                'order_date' => now(),
                'expected_shipment_date' => now()->addDays(7),
                'total_amount_idr' => $quotation->total_idr,
                'status' => 'CONFIRMED',
                'created_by_user_id' => auth()->id(),
                'notes' => "Dikonversi dari Penawaran {$quotation->quotation_no}",
            ]);
        });

        return back()->with('success', "Sales Order {$so->order_number} berhasil dibuat dari penawaran {$quotation->quotation_no}.");
    }

    /**
     * Update Status Penawaran (ACCEPTED / REJECTED)
     * Hak Akses: crm.deal
     */
    public function updateStatus(string $id, Request $request)
    {
        Gate::authorize('crm.deal');

        $request->validate([
            'status' => 'required|in:DRAFT,SENT,ACCEPTED,REJECTED,EXPIRED',
        ]);

        $quotation = Quotation::findOrFail($id);
        $quotation->update(['status' => $request->status]);

        return back()->with('success', "Status penawaran diperbarui menjadi {$request->status}.");
    }

    /**
     * Konversi 1-Klik Penawaran (Quotation / SO) Menjadi Project Shipment
     * Hak Akses: crm.deal
     */
    public function convertToProject(string $id, Request $request)
    {
        Gate::authorize('crm.deal');

        $quotation = Quotation::with('client')->findOrFail($id);
        $client = $quotation->client;

        // Validasi Surat Kuasa PPJK Klien
        if (!$client || !$client->surat_kuasa_expired_at) {
            return back()->with('error', 'Surat kuasa PPJK klien belum terdaftar. Lengkapi surat kuasa di data Klien sebelum membuat project.');
        }

        if (now()->isAfter($client->surat_kuasa_expired_at)) {
            return back()->with('error', "Surat kuasa PPJK klien [{$client->name}] telah kedaluwarsa pada " . $client->surat_kuasa_expired_at->format('d/m/Y') . ". Pembukaan project baru diblokir.");
        }

        $validated = $request->validate([
            'nomor_bl_awb' => 'required|string|max:50',
            'eta_date' => 'required|date',
            'jenis_layanan' => 'nullable|in:IMPORT_BC20,EKSPORT_BC30',
        ]);

        $shipment = $this->workflowService->createProject([
            'client_id' => $client->id,
            'nomor_bl_awb' => $validated['nomor_bl_awb'],
            'eta_date' => $validated['eta_date'],
            'jenis_layanan' => $validated['jenis_layanan'] ?? 'IMPORT_BC20',
            'pelabuhan_muat' => $quotation->origin_port,
            'pelabuhan_bongkar' => $quotation->destination_port,
            'catatan' => "Dibuat otomatis dari Penawaran / Sales Order: {$quotation->quotation_number}",
        ], auth()->id());

        $quotation->update(['status' => 'ACCEPTED']);

        return redirect()->route('core.document.show', $shipment->id)
            ->with('success', "Project Shipment [{$shipment->project_no}] berhasil dibuka dari Penawaran {$quotation->quotation_number} dengan penugasan PIC 11 tahapan otomatis.");
    }
}
