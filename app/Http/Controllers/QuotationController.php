<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Quotation;
use App\Services\Shipment\ShipmentWorkflowService;
use Illuminate\Http\Request;
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

        $query = Quotation::with('client');

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
        ];

        return view('crm.quotations_index', compact('quotations', 'clients', 'stats'));
    }

    /**
     * Buat Penawaran Harga Baru (CRM)
     * Hak Akses: crm.deal
     */
    public function store(Request $request)
    {
        Gate::authorize('crm.deal');

        $validated = $request->validate([
            'client_id' => 'required|uuid|exists:clients,id',
            'origin_port' => 'required|string|max:100',
            'destination_port' => 'required|string|max:100',
            'total_estimate' => 'required|numeric|min:0',
            'valid_until' => 'nullable|date',
            'terms' => 'nullable|string',
        ]);

        $nextNum = Quotation::count() + 1;
        $quotationNumber = 'QUO-' . date('Ym') . '-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        $quotation = Quotation::create(array_merge($validated, [
            'quotation_number' => $quotationNumber,
            'issue_date' => now(),
            'status' => 'SENT',
        ]));

        return redirect()->route('crm.quotations.index')
            ->with('success', "Penawaran harga {$quotation->quotation_number} berhasil diterbitkan.");
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
