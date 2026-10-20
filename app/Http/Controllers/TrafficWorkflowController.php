<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Services\Shipment\ShipmentWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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

        $query = Shipment::with(['client', 'ceisaDocument', 'shippingVendor'])
            ->whereIn('status_tahap', [
                'DO_RELEASED', 
                'TRAFFIC_SPPB', 
                'SPPB_RELEASED', 
                'TRAFFIC_HOLD', 
                'INSPECTION_CUSTOMS', 
                'TRAFFIC_GATE_OUT', 
                'GATE_OUT', 
                'DELIVERED_POD', 
                'DELIVERED'
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
}
