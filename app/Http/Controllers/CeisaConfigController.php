<?php

namespace App\Http\Controllers;

use App\Models\CeisaApiLog;
use App\Models\HsCode;
use App\Models\HsRegulation;
use App\Services\Ceisa\CeisaAuthService;
use App\Services\Ceisa\CeisaBtkiTariffSyncService;
use App\Services\Ceisa\CeisaClientService;
use App\Services\Ceisa\CeisaHsCodeService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CeisaConfigController extends Controller implements HasMiddleware
{
    protected CeisaAuthService $authService;
    protected CeisaClientService $clientService;
    protected CeisaBtkiTariffSyncService $btkiSyncService;
    protected CeisaHsCodeService $hsCodeService;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:setting.view', only: ['index']),
            new Middleware('permission:setting.manage', only: ['testAuth', 'syncHsCodes', 'testHsCode']),
        ];
    }

    public function __construct(
        CeisaAuthService $authService,
        CeisaClientService $clientService,
        CeisaBtkiTariffSyncService $btkiSyncService,
        CeisaHsCodeService $hsCodeService
    ) {
        $this->authService = $authService;
        $this->clientService = $clientService;
        $this->btkiSyncService = $btkiSyncService;
        $this->hsCodeService = $hsCodeService;
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
