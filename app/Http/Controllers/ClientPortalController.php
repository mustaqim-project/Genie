<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Shipment;
use App\Models\ShipmentDocument;
use App\Services\Shipment\ShipmentWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientPortalController extends Controller
{
    protected ShipmentWorkflowService $workflowService;

    public function __construct(ShipmentWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Tampilan Portal Mandiri Klien (6 Fase Perjalanan Kargo)
     */
    public function index(Request $request, ?string $token = null)
    {
        // Cari klien berdasarkan ID atau Kode, atau fallback ke klien utama (Demo: Sumber Makmur)
        $client = null;
        if ($token) {
            $client = Client::where('id', $token)->orWhere('code', $token)->first();
        }

        if (!$client) {
            $client = Client::first();
        }

        if (!$client) {
            return redirect()->route('login')->withErrors(['email' => 'Data klien tidak ditemukan.']);
        }

        $shipments = Shipment::where('client_id', $client->id)
            ->with(['documents', 'shippingVendor', 'ceisaDocument'])
            ->latest()
            ->get();

        return view('portal.index', compact('client', 'shipments'));
    }

    /**
     * Klien Mengunggah Berkas Dokumen Pelengkap secara Mandiri
     */
    public function uploadDocument(string $shipmentId, Request $request)
    {
        $request->validate([
            'document_type' => 'required|string',
            'document_file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx,xlsx,xls|max:10240',
        ]);

        $shipment = Shipment::findOrFail($shipmentId);
        $file = $request->file('document_file');

        $doc = $this->workflowService->uploadDocument(
            $shipment,
            $request->document_type,
            $file,
            'Diunggah mandiri oleh Klien via Portal Klien.'
        );

        return back()->with('success', "Berkas [{$doc->catatan}] berhasil dikirim ke Tim Dokumen untuk verifikasi.");
    }
}
