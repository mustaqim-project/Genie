<?php

namespace App\Http\Controllers;

use App\Models\InspectionPhoto;
use App\Models\Shipment;
use App\Models\ShipmentEvent;
use App\Models\ShipmentOperationCost;
use App\Services\Shipment\ShipmentWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class TrafficWorkflowController extends Controller
{
    protected ShipmentWorkflowService $workflowService;

    public function __construct(ShipmentWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Dashboard & Radar Monitoring Tim Traffic
     * Hak Akses: traffic.view
     */
    public function index(Request $request)
    {
        Gate::authorize('traffic.view');

        $query = Shipment::with(['client', 'ceisaDocument', 'shippingVendor', 'inspectionPhotos', 'operationCosts'])
            ->whereIn('status_tahap', [
                'DO_RELEASED',
                'TRAFFIC_SPPB',
                'SPPB_RELEASED',
                'TRAFFIC_HOLD',
                'INSPECTION_CUSTOMS',
                'STUFFING',
                'NPE_ISSUED',
                'VESSEL_DEPARTED',
                'TRAFFIC_GATE_OUT',
                'GATE_OUT',
                'DELIVERED_POD',
                'DELIVERED',
            ]);

        if ($request->filled('channel')) {
            $query->where('customs_channel', $request->channel);
        }

        $shipments = $query->latest()->paginate(10);

        return view('core.traffic_index', compact('shipments'));
    }

    /**
     * Pembaruan Penjaluran Pabean & Penanganan Behandle / Hold
     * Hak Akses: traffic.process
     */
    public function updateCustomsStatus(string $id, Request $request)
    {
        Gate::authorize('traffic.process');

        $request->validate([
            'channel' => 'required|in:HIJAU,KUNING,MERAH,HOLD',
            'notes' => 'nullable|string',
        ]);

        $shipment = Shipment::findOrFail($id);
        $nextStatus = match($request->channel) {
            'HIJAU' => 'SPPB_RELEASED',
            'KUNING', 'MERAH', 'HOLD' => 'INSPECTION_CUSTOMS',
        };

        $this->workflowService->advanceTraffic($shipment, $nextStatus, [
            'customs_channel' => $request->channel,
            'traffic_notes' => $request->notes,
        ]);

        return back()->with('success', "Penjaluran pabean diperbarui ke Jalur [{$request->channel}]. Status shipment: {$nextStatus}.");
    }

    /**
     * Eksekusi Pengeluaran Petikemas dari Pelabuhan (Gate Out)
     * Hak Akses: traffic.gateout
     */
    public function gateOut(string $id, Request $request)
    {
        Gate::authorize('traffic.gateout');

        $request->validate([
            'truck_plate_number' => 'required|string|max:20',
            'driver_name' => 'required|string|max:100',
            'surat_jalan_number' => 'required|string|max:100',
        ]);

        $shipment = Shipment::findOrFail($id);

        if (!$shipment->do_is_released) {
            return back()->with('error', 'DO belum dirilis! Kontainer tidak dapat keluar sebelum DO sah dari pelayaran.');
        }

        $this->workflowService->advanceTraffic($shipment, 'GATE_OUT', [
            'truck_plate_number' => $request->truck_plate_number,
            'driver_name' => $request->driver_name,
            'surat_jalan_number' => $request->surat_jalan_number,
            'gate_out_at' => now(),
        ]);

        return back()->with('success', "Kontainer berhasil Gate Out dari TPS Pelabuhan menuju gudang klien dengan truk [{$request->truck_plate_number}].");
    }

    /**
     * Konfirmasi Penerimaan Barang & Bukti Terima (POD) di Gudang Klien
     * Hak Akses: traffic.process
     */
    public function deliverPod(string $id, Request $request)
    {
        Gate::authorize('traffic.process');

        $request->validate([
            'receiver_name' => 'required|string|max:100',
            'pod_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $shipment = Shipment::findOrFail($id);
        $podPath = null;

        if ($request->hasFile('pod_file')) {
            $podPath = $request->file('pod_file')->store("shipments/{$shipment->shipment_number}/pod", 'public');
        }

        $this->workflowService->advanceTraffic($shipment, 'DELIVERED', [
            'receiver_name' => $request->receiver_name,
            'pod_file_path' => $podPath,
            'delivered_at' => now(),
        ]);

        // Otomatis terbitkan draf Invoice Penagihan Klien (P19) ke Finance
        $invoice = $this->workflowService->generateClientInvoice($shipment);

        return back()->with('success', "Shipment berhasil diserahterimakan (DELIVERED) ke [{$request->receiver_name}]. Invoice Penagihan Klien [{$invoice->invoice_number}] otomatis terbit di modul Finance.");
    }

    /**
     * Pembaruan Detail Respon Kepabeanan (SPJM, SPPB, NPD, DNP, NHI) + No Segel & Vendor Trucking
     * Hak Akses: traffic.process
     */
    public function updateCustomsResponses(string $id, Request $request)
    {
        Gate::authorize('traffic.process');

        $shipment = Shipment::findOrFail($id);

        $validated = $request->validate([
            'customs_channel' => 'nullable|in:HIJAU,KUNING,MERAH',
            'spjm_ticket' => 'nullable|string|max:100',
            'spjm_received_at' => 'nullable|date',
            'inspection_scheduled_at' => 'nullable|date',
            'spjm_received' => 'nullable|boolean',
            'sppb_received' => 'nullable|boolean',
            'npd_received' => 'nullable|boolean',
            'dnp_received' => 'nullable|boolean',
            'nhi_received' => 'nullable|boolean',
            'tps_location' => 'nullable|string|max:150',
            'old_seal_number' => 'nullable|string|max:100',
            'new_seal_number' => 'nullable|string|max:100',
            'trucking_vendor_name' => 'nullable|string|max:150',
            'odoo_synced' => 'nullable|boolean',
            'physical_doc_archived' => 'nullable|boolean',
            'response_doc_type' => 'nullable|in:SPJM,SPPB,NPD,DNP,NHI,SLIM,BERITA_ACARA',
            'response_doc_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:15360',
        ]);

        $updateData = collect($validated)->only([
            'customs_channel', 'spjm_ticket', 'spjm_received_at', 'inspection_scheduled_at',
            'tps_location', 'old_seal_number', 'new_seal_number', 'trucking_vendor_name',
        ])->toArray();

        $updateData['spjm_received'] = $request->boolean('spjm_received') || !empty($validated['spjm_ticket']) || ($validated['customs_channel'] ?? '') === 'MERAH';
        $updateData['sppb_received'] = $request->boolean('sppb_received');
        $updateData['npd_received'] = $request->boolean('npd_received');
        $updateData['dnp_received'] = $request->boolean('dnp_received');
        $updateData['nhi_received'] = $request->boolean('nhi_received');

        if ($request->has('odoo_synced')) {
            $updateData['odoo_synced'] = $request->boolean('odoo_synced');
        }
        if ($request->has('physical_doc_archived')) {
            $updateData['physical_doc_archived'] = $request->boolean('physical_doc_archived');
        }
        if ($updateData['spjm_received'] && empty($shipment->spjm_received_at) && empty($updateData['spjm_received_at'])) {
            $updateData['spjm_received_at'] = now();
        }
        if (($updateData['customs_channel'] ?? '') === 'MERAH' && !in_array($shipment->status_tahap, ['GATE_OUT', 'DELIVERED', 'COMPLETED'], true)) {
            $updateData['status_tahap'] = 'INSPECTION_CUSTOMS';
        } elseif ($updateData['sppb_received'] && !in_array($shipment->status_tahap, ['GATE_OUT', 'DELIVERED', 'COMPLETED'], true)) {
            $updateData['status_tahap'] = 'SPPB_RELEASED';
        }

        $shipment->update($updateData);

        if ($request->hasFile('response_doc_file')) {
            $docType = $validated['response_doc_type'] ?? ($updateData['sppb_received'] ? 'SPPB' : 'SPJM');
            $this->workflowService->uploadDocument(
                $shipment,
                $docType,
                $request->file('response_doc_file'),
                "Dokumen respon pabean {$docType} diunggah oleh Tim Traffic."
            );
        }

        ShipmentEvent::create([
            'shipment_id' => $shipment->id,
            'process_code' => 'CUSTOMS_RESPONSE',
            'to_status' => $shipment->status_tahap,
            'actor_id' => auth()->id(),
            'note' => trim(sprintf(
                'Update respon pabean: Jalur %s, Tiket SPJM %s, Segel Baru %s.',
                $shipment->customs_channel ?? '-',
                $shipment->spjm_ticket ?? '-',
                $shipment->new_seal_number ?? '-'
            )),
            'created_at' => now(),
        ]);

        return back()->with('success', "Data respon kepabeanan & segel kontainer {$shipment->project_no} berhasil disimpan.");
    }

    /**
     * Upload Multi-Foto Pengecekan Fisik SPJM, Pembongkaran, Segel Kembali, atau Stuffing Ekspor
     * Hak Akses: traffic.process
     */
    public function uploadInspectionPhotos(string $id, Request $request)
    {
        Gate::authorize('traffic.process');

        $shipment = Shipment::findOrFail($id);
        $allowedStages = array_merge(
            array_keys(InspectionPhoto::IMPORT_STAGES),
            array_keys(InspectionPhoto::EXPORT_STAGES)
        );

        $validated = $request->validate([
            'stage' => 'required|string|in:' . implode(',', $allowedStages),
            'old_seal_number' => 'nullable|string|max:100',
            'seal_number' => 'nullable|string|max:100',
            'caption' => 'nullable|string|max:500',
            'photos' => 'required|array|min:1',
            'photos.*' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:10240',
        ]);

        $flowType = array_key_exists($validated['stage'], InspectionPhoto::EXPORT_STAGES) ? 'EXPORT' : 'IMPORT';
        $uploadedCount = 0;

        foreach ($request->file('photos') as $file) {
            $filename = time() . '_' . uniqid() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
            $path = $file->storeAs("shipments/{$shipment->id}/inspection_photos/{$validated['stage']}", $filename, 'public');

            InspectionPhoto::create([
                'shipment_id' => $shipment->id,
                'flow_type' => $flowType,
                'stage' => $validated['stage'],
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_size_kb' => (int) ceil(($file->getSize() ?: 1024) / 1024),
                'mime_type' => $file->getClientMimeType(),
                'seal_number' => ($validated['seal_number'] ?? null) ?: $shipment->new_seal_number,
                'caption' => $validated['caption'] ?? null,
                'taken_at' => now(),
                'uploaded_by' => auth()->id(),
            ]);
            $uploadedCount++;
        }

        // Sinkronkan segel & jadwal pemeriksaan pada shipment
        $shipmentUpdates = [];
        if (!empty($validated['old_seal_number'])) {
            $shipmentUpdates['old_seal_number'] = $validated['old_seal_number'];
        }
        if (!empty($validated['seal_number'])) {
            $shipmentUpdates['new_seal_number'] = $validated['seal_number'];
        }
        if (in_array($validated['stage'], ['UNLOADING', 'GOODS_INSPECTION', 'STUFFING_PROCESS'], true) && !$shipment->inspection_scheduled_at) {
            $shipmentUpdates['inspection_scheduled_at'] = now();
        }
        if (!empty($shipmentUpdates)) {
            $shipment->update($shipmentUpdates);
        }

        $stageLabel = InspectionPhoto::IMPORT_STAGES[$validated['stage']]
            ?? InspectionPhoto::EXPORT_STAGES[$validated['stage']]
            ?? $validated['stage'];

        ShipmentEvent::create([
            'shipment_id' => $shipment->id,
            'process_code' => 'INSPECTION_PHOTO',
            'to_status' => $validated['stage'],
            'actor_id' => auth()->id(),
            'note' => "Unggah {$uploadedCount} foto tahap [{$stageLabel}]" . (!empty($validated['seal_number']) ? " · Segel Baru: {$validated['seal_number']}" : ''),
            'created_at' => now(),
        ]);

        return back()->with('success', "{$uploadedCount} dokumentasi foto [{$stageLabel}] berhasil disimpan.");
    }

    /**
     * Hapus Foto Pemeriksaan Fisik
     */
    public function deleteInspectionPhoto(string $id, string $photoId)
    {
        Gate::authorize('traffic.process');

        $photo = InspectionPhoto::where('shipment_id', $id)->findOrFail($photoId);
        if ($photo->file_path && Storage::disk('public')->exists($photo->file_path)) {
            Storage::disk('public')->delete($photo->file_path);
        }
        $photo->delete();

        return back()->with('success', 'Foto dokumentasi pemeriksaan berhasil dihapus.');
    }

    /**
     * Catat Biaya Operasional Pelabuhan (DO, Extend DO 1..3, Refund, BHDL, STG, TKC, LOLO, Trucking)
     */
    public function storeOperationCost(string $id, Request $request)
    {
        Gate::authorize('traffic.process');

        $shipment = Shipment::findOrFail($id);

        $validated = $request->validate([
            'cost_category' => 'required|string|in:' . implode(',', array_keys(ShipmentOperationCost::CATEGORIES)),
            'vendor_name' => 'nullable|string|max:150',
            'invoice_number' => 'nullable|string|max:100',
            'amount' => 'required|numeric|min:0',
            'cost_date' => 'nullable|date',
            'status' => 'nullable|in:PLANNED,PAID,REIMBURSED',
            'notes' => 'nullable|string|max:500',
        ]);

        ShipmentOperationCost::create([
            'shipment_id' => $shipment->id,
            'cost_category' => $validated['cost_category'],
            'vendor_name' => $validated['vendor_name'] ?? null,
            'invoice_number' => $validated['invoice_number'] ?? null,
            'amount' => $validated['amount'],
            'status' => $validated['status'] ?? 'PAID',
            'cost_date' => $validated['cost_date'] ?? now()->toDateString(),
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'Biaya operasional [' . (ShipmentOperationCost::CATEGORIES[$validated['cost_category']] ?? $validated['cost_category']) . '] berhasil dicatat.');
    }
}
