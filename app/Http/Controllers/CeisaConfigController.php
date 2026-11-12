<?php

namespace App\Http\Controllers;

use App\Models\CeisaApiLog;
use App\Models\HsCode;
use App\Models\HsRegulation;
use App\Services\Ceisa\CeisaAuthService;
use App\Services\Ceisa\CeisaBtkiTariffSyncService;
use App\Services\Ceisa\CeisaClientService;
use App\Services\Ceisa\CeisaHsCodeService;
use App\Services\Ceisa\CeisaDocumentSyncService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CeisaConfigController extends Controller implements HasMiddleware
{
    protected CeisaAuthService $authService;
    protected CeisaClientService $clientService;
    protected CeisaBtkiTariffSyncService $btkiSyncService;
    protected CeisaHsCodeService $hsCodeService;
    protected CeisaDocumentSyncService $syncService;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:setting.view', only: ['index']),
            new Middleware('permission:setting.manage', only: ['testAuth', 'syncHsCodes', 'testHsCode', 'syncDocument', 'syncAllActive', 'syncAllEverything', 'saveApiSettings']),
        ];
    }

    public function __construct(
        CeisaAuthService $authService,
        CeisaClientService $clientService,
        CeisaBtkiTariffSyncService $btkiSyncService,
        CeisaHsCodeService $hsCodeService,
        CeisaDocumentSyncService $syncService
    ) {
        $this->authService = $authService;
        $this->clientService = $clientService;
        $this->btkiSyncService = $btkiSyncService;
        $this->hsCodeService = $hsCodeService;
        $this->syncService = $syncService;
    }

    public function index(Request $request)
    {
        $config = config('ceisa');
        $logs = CeisaApiLog::latest()->paginate(10, ['*'], 'logs_page');

        $searchHs = $request->get('search_hs');
        $lartasFilter = $request->get('lartas_filter');

        $hsQuery = HsCode::query();

        if ($searchHs) {
            $hsQuery->where(function ($q) use ($searchHs) {
                $q->where('code', 'like', "%{$searchHs}%")
                  ->orWhere('description', 'like', "%{$searchHs}%")
                  ->orWhere('btki', 'like', "%{$searchHs}%");
            });
        }

        if ($lartasFilter === 'lartas') {
            $hsQuery->where('has_lartas', true);
        } elseif ($lartasFilter === 'non_lartas') {
            $hsQuery->where('has_lartas', false);
        }

        $hsCodes = $hsQuery->orderBy('code')->paginate(12, ['*'], 'hs_page')->withQueryString();
        $hsStats = $this->btkiSyncService->getStats();

        return view('config.index', compact('config', 'logs', 'hsCodes', 'hsStats', 'searchHs', 'lartasFilter'));
    }

    public function testAuth()
    {
        try {
            $this->authService->clearToken();
            $token = $this->authService->getAccessToken();

            return back()->with('success', 'Koneksi ke Otorisasi OAuth CEISA 4.0 BERHASIL! Token: ' . substr($token, 0, 25) . '...');
        } catch (\Throwable $e) {
            return back()->with('error', 'Koneksi GAGAL: ' . $e->getMessage());
        }
    }

    /**
     * Trigger Sinkronisasi Master HS Code BTKI 2022 dari Bea Cukai
     */
    public function syncHsCodes(Request $request)
    {
        try {
            $result = $this->btkiSyncService->syncFromBtkiRegistry(true);

            if ($result['success']) {
                return back()->with('success', "Sinkronisasi Master HS Code BTKI 2022 Berhasil! Total Pos Tarif aktif: {$result['total_in_db']}, Regulasi Dokumen Lartas terhubung: {$result['regulations_synced']}.");
            }

            return back()->with('error', 'Gagal sinkronisasi BTKI 2022: ' . ($result['error'] ?? 'Terjadi kesalahan sistem'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Kesalahan saat sinkronisasi: ' . $e->getMessage());
        }
    }

    /**
     * Tarik dan Sinkronisasikan Data dari CEISA berdasarkan Nomor Aju
     */
    public function syncDocument(Request $request)
    {
        $validated = $request->validate([
            'nomor_aju' => 'required|string|min:6|max:40',
            'auto_create_shipment' => 'nullable|boolean',
        ]);

        try {
            $autoCreate = $request->boolean('auto_create_shipment', true);
            $result = $this->syncService->syncByNomorAju($validated['nomor_aju'], $autoCreate);

            if ($result['success']) {
                $status = $result['status'] ?? 'UPDATE';
                $jalur = $result['jalur'] ? " (Jalur {$result['jalur']})" : '';
                return back()->with('success', "Sinkronisasi Nomor Aju {$validated['nomor_aju']} Berhasil! Status pabean terkini: {$status}{$jalur}.");
            }

            return back()->with('error', $result['message'] ?? 'Gagal sinkronisasi data CEISA.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan saat sinkronisasi: ' . $e->getMessage());
        }
    }

    /**
     * Impor File Ekspor Portal CEISA 4.0 (.xlsx / .json / .pdf) untuk melengkapi
     * Importir, NPWP, Kontainer, BL/AWB, Nilai Pabean, dan seluruh Item Barang.
     */
    public function importDocument(Request $request, \App\Services\Ceisa\CeisaExportImportService $importService)
    {
        $validated = $request->validate([
            'ceisa_file' => 'required|file|mimes:xlsx,xls,json,txt,pdf|max:20480',
            'nomor_aju' => 'nullable|string|min:6|max:40',
            'sync_status_after' => 'nullable|boolean',
        ]);

        try {
            $result = $importService->importFromUploadedFile(
                $request->file('ceisa_file'),
                $validated['nomor_aju'] ?? null,
                $request->boolean('sync_status_after', true)
            );

            if ($result['success'] ?? false) {
                return back()->with('success', $result['message']);
            }

            return back()->with('error', $result['message'] ?? 'Gagal mengimpor file ekspor CEISA 4.0.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan saat impor file CEISA: ' . $e->getMessage());
        }
    }

    /**
     * Tarik Sinkronisasi Massal Seluruh Dokumen Aktif
     */
    public function syncAllActive()
    {
        try {
            $res = $this->syncService->syncAllActiveDocuments();
            return back()->with('success', "Sinkronisasi massal pabean selesai: {$res['synced']} dokumen berhasil diperbarui, {$res['failed']} gagal.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memproses sinkronisasi massal: ' . $e->getMessage());
        }
    }

    /**
     * Tarik SEMUA Data CEISA Sekaligus (Dokumen + Kurs Valuta + Pos Tarif BTKI)
     */
    public function syncAllEverything()
    {
        try {
            $res = $this->syncService->syncAllFromCeisa(true, true);
            $docCount = $res['documents']['synced'] ?? 0;
            $docFail = $res['documents']['failed'] ?? 0;
            $kursCount = $res['kurs']['synced'] ?? 0;
            $hsCount = $res['hs_codes']['total_in_db'] ?? 0;
            $mode = $res['mode'] ?? 'SANDBOX';

            return back()->with(
                'success',
                "Tarik Semua Data CEISA ({$mode}) selesai! Dokumen disinkronkan: {$docCount} (gagal: {$docFail}), Kurs Pajak: {$kursCount} valuta, Master HS Code BTKI aktif: {$hsCount}."
            );
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menarik semua data CEISA: ' . $e->getMessage());
        }
    }

    /**
     * Simpan Pengaturan Gateway API CEISA 4.0
     */
    public function saveApiSettings(Request $request)
    {
        $validated = $request->validate([
            'ceisa_mode' => 'required|in:sandbox,production',
            'ceisa_base_url' => 'required|url',
            'ceisa_auth_url' => 'required|url',
            'ceisa_refresh_token_url' => 'nullable|url',
            'ceisa_username' => 'required|string|max:100',
            'ceisa_password' => 'nullable|string|max:100',
            'ceisa_api_key' => 'required|string|max:100',
            'ceisa_application_id' => 'nullable|string|max:100',
            'ceisa_whatsapp_no' => 'nullable|string|max:50',
            'ceisa_webhook_secret' => 'nullable|string|max:150',
            'ceisa_read_only' => 'nullable|boolean',
            'company_name' => 'required|string|max:200',
            'company_nib' => 'required|string|max:50',
            'company_npwp' => 'required|string|max:50',
            'company_address' => 'required|string|max:500',
            'company_kode_kantor' => 'required|string|max:20',
            'company_id_perusahaan' => 'required|string|max:20',
        ]);

        $mockMode = $validated['ceisa_mode'] === 'sandbox' ? '1' : '0';

        \App\Models\SystemSetting::set('ceisa_env', $validated['ceisa_mode'], 'ceisa', 'Lingkungan CEISA 4.0');
        \App\Models\SystemSetting::set('ceisa_mock_mode', $mockMode, 'ceisa', 'Mode Mock Simulator');
        \App\Models\SystemSetting::set('ceisa_base_url', $validated['ceisa_base_url'], 'ceisa', 'Base URL Gateway DJBC');
        \App\Models\SystemSetting::set('ceisa_auth_url', $validated['ceisa_auth_url'], 'ceisa', 'Auth Login URL DJBC');
        if (!empty($validated['ceisa_refresh_token_url'])) {
            \App\Models\SystemSetting::set('ceisa_refresh_token_url', $validated['ceisa_refresh_token_url'], 'ceisa', 'Refresh Token URL');
        }
        \App\Models\SystemSetting::set('ceisa_username', $validated['ceisa_username'], 'ceisa', 'Username API DJBC');
        if (!empty($validated['ceisa_password'])) {
            \App\Models\SystemSetting::set('ceisa_password', $validated['ceisa_password'], 'ceisa', 'Password API DJBC');
        }
        \App\Models\SystemSetting::set('ceisa_api_key', $validated['ceisa_api_key'], 'ceisa', 'API Key Resmi DJBC');
        \App\Models\SystemSetting::set('ceisa_application_id', $validated['ceisa_application_id'] ?? '', 'ceisa', 'Application ID Resmi DJBC');
        \App\Models\SystemSetting::set('ceisa_whatsapp_no', $validated['ceisa_whatsapp_no'] ?? '', 'ceisa', 'Nomor WhatsApp Notifikasi DJBC');
        \App\Models\SystemSetting::set('ceisa_webhook_secret', $validated['ceisa_webhook_secret'] ?? '', 'ceisa', 'Secret Token Verifikasi Webhook Callback CEISA');
        \App\Models\SystemSetting::set('ceisa_read_only', $request->boolean('ceisa_read_only') ? '1' : '0', 'ceisa', 'Mode Tarik Data Saja (Blokir Kirim ke CEISA)');

        \App\Models\SystemSetting::set('company_name', $validated['company_name'], 'company', 'Nama Resmi Perusahaan');
        \App\Models\SystemSetting::set('company_nib', $validated['company_nib'], 'company', 'Nomor Induk Berusaha');
        \App\Models\SystemSetting::set('company_npwp', $validated['company_npwp'], 'company', 'NPWP 15/16 Digit');
        \App\Models\SystemSetting::set('company_address', $validated['company_address'], 'company', 'Alamat Resmi Sesuai NIB');
        \App\Models\SystemSetting::set('company_kode_kantor', $validated['company_kode_kantor'], 'company', 'Kode Kantor Pabean Default');
        \App\Models\SystemSetting::set('company_id_perusahaan', $validated['company_id_perusahaan'], 'company', 'ID Unik Perusahaan');

        \App\Models\SystemSetting::loadIntoConfig();
        $this->authService->clearToken();

        $modeLabel = $validated['ceisa_mode'] === 'production' ? 'LIVE PRODUCTION (Gateway DJBC Asli)' : 'SANDBOX / SIMULASI';

        return back()->with('success', "Konfigurasi Gateway API CEISA 4.0 berhasil disimpan! Mode aktif: {$modeLabel}.");
    }

    /**
     * Uji Langsung Pos Tarif & Lartas via CEISA 4.0 & Database
     */
    public function testHsCode(Request $request)
    {
        $validated = $request->validate([
            'test_hs' => 'required|string',
        ]);

        $tariff = $this->hsCodeService->getTariffAndLartas($validated['test_hs']);

        return back()->with('hs_result', $tariff);
    }
}
