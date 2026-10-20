<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Shipment;
use App\Models\ShipmentDocument;
use App\Services\Shipment\ShipmentWorkflowService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ClientPortalController extends Controller
{
    protected ShipmentWorkflowService $workflowService;

    public function __construct(ShipmentWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Index router fallback (Redirects to latest project tracking or list if authenticated)
     */
    public function index(Request $request, ?string $token = null)
    {
        // 1. Jika token adalah token tracking shipment
        if ($token) {
            $shipment = Shipment::where('portal_token', $token)->first();
            if ($shipment) {
                return redirect()->route('portal.track', $token);
            }
        }

        // 2. Fallback: ambil shipment aktif terbaru
        $latestShipment = Shipment::latest()->first();
        if ($latestShipment) {
            if (empty($latestShipment->portal_token) || !$latestShipment->portal_expires_at) {
                $latestShipment->generatePortalToken(7);
            }
            return redirect()->route('portal.track', $latestShipment->portal_token);
        }

        return redirect()->route('login')->withErrors(['email' => 'Belum ada data pengapalan proyek aktif.']);
    }

    /**
     * Halaman Pelacakan Kargo Proyek Mandiri Klien (Dedicated Per Proyek)
     */
    public function track(string $token)
    {
        $shipment = Shipment::where('portal_token', $token)
            ->with(['client', 'shippingVendor', 'ceisaDocument', 'documents', 'items'])
            ->first();

        // 1. Token tidak ditemukan
        if (!$shipment) {
            return response()->view('portal.expired', [
                'reason' => 'NOT_FOUND',
                'title' => 'Tautan Pelacakan Tidak Ditemukan',
                'message' => 'Tautan pelacakan kargo yang Anda buka tidak terdaftar dalam sistem GENIE ERP atau telah diperbarui oleh Tim Operasional.',
                'shipment' => null,
            ], 404);
        }

        // 2. Token kedaluwarsa (Expired Timer)
        if ($shipment->portal_expires_at && $shipment->portal_expires_at->isPast()) {
            return response()->view('portal.expired', [
                'reason' => 'EXPIRED',
                'title' => 'Tautan Pelacakan Telah Kedaluwarsa',
                'message' => 'Demi menjaga keamanan kerahasiaan data kepabeanan dan dokumen ekspor-impor perusahaan Anda, tautan pelacakan ini telah otomatis kedaluwarsa setelah melewati batas durasi timer yang ditentukan.',
                'shipment' => $shipment,
                'expiredAt' => $shipment->portal_expires_at,
            ], 403);
        }

        // 3. Update waktu akses terakhir & log
        $shipment->update(['portal_last_accessed_at' => now()]);

        try {
            DB::table('client_portal_activity')->insert([
                'id' => (string) Str::uuid(),
                'token_id' => $shipment->id, // fallback UUID
                'action' => 'VIEW_TRACKING',
                'shipment_id' => $shipment->id,
                'ip' => request()->ip(),
                'device' => request()->userAgent(),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {}

        $client = $shipment->client;

        // Hitung sisa masa aktif timer
        $diffHours = (int) ceil(now()->diffInHours($shipment->portal_expires_at, false));
        $diffDays = (int) ceil(now()->diffInDays($shipment->portal_expires_at, false));
        $remainingHuman = $shipment->portal_expires_at->diffForHumans(['syntax' => Carbon::DIFF_RELATIVE_TO_NOW]);

        // Map tahapan internal (P01 - P14) ke 6 fase perjalanan kargo ramah klien
        $stageIndex = $shipment->stage_index;
        $activePhase = match(true) {
            $stageIndex >= 11 => 5, // Selesai / Tiba di Gudang Klien
            $stageIndex >= 8  => 4, // Pengiriman Truk & Gate Out TPS
            $stageIndex >= 6  => 3, // Penjaluran Bea Cukai & SPPB
            $stageIndex >= 4  => 2, // Penebusan DO Pelayaran
            $stageIndex >= 2  => 1, // Pengajuan Dokumen Pabean (PIB/Billing)
            default           => 0, // Kumpul Berkas & Verifikasi
        };

        return view('portal.track', compact(
            'shipment',
            'client',
            'activePhase',
            'remainingHuman',
            'diffHours',
            'diffDays'
        ));
    }

    /**
     * Klien Mengunggah Berkas Tambahan Mandiri via Token
     */
    public function uploadDocumentByToken(string $token, Request $request)
    {
        $shipment = Shipment::where('portal_token', $token)->firstOrFail();

        if ($shipment->portal_expires_at && $shipment->portal_expires_at->isPast()) {
            return back()->with('error', 'Tautan pelacakan telah kedaluwarsa. Tidak dapat mengunggah berkas baru.');
        }

        $request->validate([
            'document_type' => 'required|string',
            'document_file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx,xlsx,xls|max:15360',
        ]);

        $file = $request->file('document_file');
        $doc = $this->workflowService->uploadDocument(
            $shipment,
            $request->document_type,
            $file,
            'Diunggah mandiri oleh Klien via Portal Pelacakan Proyek.'
        );

        return back()->with('success', "Berkas [{$doc->catatan}] berhasil dikirim ke Tim Dokumen untuk verifikasi.");
    }

    /**
     * Klien Mengunggah Berkas Dokumen Pelengkap secara Mandiri via Shipment ID (Legacy)
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

    /**
     * Generate Link Portal Baru & Atur Timer Kedaluwarsa (Admin / Staff Dokumen)
     */
    public function generateToken(Request $request, string $id)
    {
        $request->validate([
            'duration_days' => 'required|integer|min:1|max:90',
        ]);

        $shipment = Shipment::findOrFail($id);
        $durationDays = (int) $request->input('duration_days', 7);

        $token = $shipment->generatePortalToken($durationDays);

        $portalUrl = route('portal.track', $token);
        $expiredFormatted = $shipment->portal_expires_at->format('d/m/Y H:i');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'token' => $token,
                'portal_url' => $portalUrl,
                'expires_at' => $expiredFormatted,
                'duration_days' => $durationDays,
                'message' => "Tautan portal berhasil dibuat dengan masa aktif {$durationDays} hari.",
            ]);
        }

        return back()->with('success', "Tautan Portal Klien berhasil diperbarui. Masa aktif berlaku hingga {$expiredFormatted} WIB ({$durationDays} hari).");
    }
}
