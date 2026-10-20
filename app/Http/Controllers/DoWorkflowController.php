<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\ShippingVendor;
use App\Services\Shipment\ShipmentWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DoWorkflowController extends Controller
{
    protected ShipmentWorkflowService $workflowService;

    public function __construct(ShipmentWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Dashboard & Antrian Tugas Tim DO
     * Hak Akses: do.view
     */
    public function index(Request $request)
    {
        Gate::authorize('do.view');

        $query = Shipment::with(['client', 'shippingVendor', 'ceisaDocument'])
            ->whereIn('status_tahap', [
                'BILLING_ISSUED', 
                'DO_PROCESSING', 
                'DO_REQUESTED', 
                'DO_RELEASED', 
                'TRAFFIC_SPPB', 
                'TRAFFIC_HOLD', 
                'TRAFFIC_GATE_OUT', 
                'DELIVERED_POD'
            ]);

        if ($request->filled('vendor_id')) {
            $query->where('shipping_vendor_id', $request->vendor_id);
        }

        $shipments = $query->latest()->paginate(10);
        $vendors = ShippingVendor::where('is_active', true)->get();

        return view('core.do_index', compact('shipments', 'vendors'));
    }

    /**
     * Otomasi Kirim Email Permintaan Invoice ke Vendor Pelayaran
     * Hak Akses: do.process
     */
    public function sendVendorEmail(string $id, Request $request)
    {
        Gate::authorize('do.process');

        $shipment = Shipment::with('shippingVendor')->findOrFail($id);
        $customNotes = $request->input('notes');

        $emailLog = $this->workflowService->sendVendorEmailForDo($shipment, $customNotes);

        return back()->with('success', "Permintaan invoice pelayaran berhasil dikirimkan via email ke [{$shipment->shippingVendor->name} - {$shipment->shippingVendor->email}]. Status diperbarui ke DO_REQUESTED.");
    }

    /**
     * Konfirmasi Penebusan & Rilis DO (Upload DO Asli & Input Masa Berlaku)
     * Hak Akses: do.release
     */
    public function releaseDo(string $id, Request $request)
    {
        Gate::authorize('do.release');

        $request->validate([
            'do_number' => 'required|string|max:100',
            'do_expired_at' => 'required|date',
            'do_file' => 'nullable|file|mimes:pdf,jpg,png|max:5120',
        ]);

        $shipment = Shipment::findOrFail($id);
        $doFilePath = null;

        if ($request->hasFile('do_file')) {
            $doFilePath = $request->file('do_file')->store("shipments/{$shipment->shipment_number}/do", 'public');
        }

        $this->workflowService->releaseDo($shipment, [
            'do_number' => $request->do_number,
            'do_expired_at' => $request->do_expired_at,
            'do_file_path' => $doFilePath,
        ]);

        return back()->with('success', "Delivery Order No. {$request->do_number} berhasil dirilis. Status pengiriman siap dialihkan ke Tim Traffic.");
    }
}
