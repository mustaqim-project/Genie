<?php

namespace App\Services\Ceisa;

use App\Models\AuditLog;
use App\Models\CeisaDocument;
use App\Models\Shipment;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CeisaManifestService
{
    protected CeisaAuthService $authService;
    protected string $manifestBaseUrl;
    protected string $tpsBaseUrl;

    public function __construct(CeisaAuthService $authService)
    {
        $this->authService = $authService;
        $this->manifestBaseUrl = config('ceisa.api.manifest_url', 'https://apis-gw.beacukai.go.id/v1/openapi/manifes');
        $this->tpsBaseUrl = config('ceisa.api.tps_url', 'https://apis-gw.beacukai.go.id/v1/openapi/tps-online');
    }

    /**
     * CEISA 4.0 Katalog 3: Tarik Data Manifes Kedatangan Kapal (BC 1.1 Inward)
     */
    public function queryInwardManifest(string $nomorBc11, ?string $tanggalBc11 = null, ?string $pos = null): array
    {
        $token = $this->authService->getAccessToken();
        $isMock = $this->authService->isMockMode();

        if ($isMock) {
            $mockSetting = SystemSetting::get('ceisa_mock_manifest_inward', []);

            return [
                'status' => 'success',
                'source' => 'PARAM_CEISA_CATALOG_3',
                'data' => [
                    'nomor_bc11' => $nomorBc11,
                    'tanggal_bc11' => $tanggalBc11 ?? now()->subDays(2)->format('Y-m-d'),
                    'pos_bc11' => $pos ?? ($mockSetting['pos_bc11'] ?? '0001'),
                    'subpos_bc11' => $mockSetting['subpos_bc11'] ?? '0000',
                    'subsubpos_bc11' => $mockSetting['subsubpos_bc11'] ?? '0000',
                    'nama_kapal' => $mockSetting['nama_kapal'] ?? 'EVER GIVEN',
                    'voyage_no' => $mockSetting['voyage_no'] ?? 'V-2026-N',
                    'pelabuhan_muat' => $mockSetting['pelabuhan_muat'] ?? 'CNSHA',
                    'pelabuhan_bongkar' => $mockSetting['pelabuhan_bongkar'] ?? 'IDTPP',
                    'jumlah_kemasan' => $mockSetting['jumlah_kemasan'] ?? 1200,
                    'jenis_kemasan' => $mockSetting['jenis_kemasan'] ?? 'PK',
                    'berat_bruto' => (float) ($mockSetting['berat_bruto'] ?? 24500.0),
                ],
            ];
        }

        try {
            $response = Http::withToken($token)
                ->timeout(15)
                ->get("{$this->manifestBaseUrl}/inward", [
                    'nomorBc11' => $nomorBc11,
                    'tanggalBc11' => $tanggalBc11,
                    'pos' => $pos,
                ]);

            if ($response->successful()) {
                return [
                    'status' => 'success',
                    'source' => 'LIVE_CEISA_4_API',
                    'data' => $response->json('data') ?? $response->json(),
                ];
            }

            return [
                'status' => 'error',
                'message' => $response->json('message') ?? $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error("Koneksi API Manifes CEISA 4.0 Gagal: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Terapkan hasil penarikan Manifes BC 1.1 ke Dokumen Pabean & Proyek Pengapalan.
     */
    public function applyManifestToShipment(Shipment $shipment, array $manifestData): bool
    {
        $doc = $shipment->ceisaDocument;

        $updates = [
            'nama_kapal' => $manifestData['nama_kapal'] ?? $shipment->nama_kapal,
            'voyage_no' => $manifestData['voyage_no'] ?? $shipment->voyage_no,
        ];

        $shipment->update($updates);

        if ($doc) {
            $doc->update([
                'nomor_bc11' => $manifestData['nomor_bc11'] ?? $doc->nomor_bc11,
                'tanggal_bc11' => $manifestData['tanggal_bc11'] ?? $doc->tanggal_bc11,
                'pos_bc11' => $manifestData['pos_bc11'] ?? $doc->pos_bc11,
                'subpos_bc11' => $manifestData['subpos_bc11'] ?? $doc->subpos_bc11,
                'subsubpos_bc11' => $manifestData['subsubpos_bc11'] ?? $doc->subsubpos_bc11,
                'nama_sarana_pengangkut' => $manifestData['nama_kapal'] ?? $doc->nama_sarana_pengangkut,
                'nomor_voyage' => $manifestData['voyage_no'] ?? $doc->nomor_voyage,
            ]);
        }

        AuditLog::record('CEISA_MANIFEST_APPLIED', 'Shipment', $shipment->id, null, $manifestData);

        return true;
    }

    /**
     * CEISA 4.0 Katalog 4: Tarik Status Gate Out TPS Online (Pelabuhan Tanjung Priok / Perak)
     */
    public function queryTpsGateOut(string $containerNo, ?string $nomorSppb = null): array
    {
        $token = $this->authService->getAccessToken();
        $isMock = $this->authService->isMockMode();

        if ($isMock) {
            $mockTps = SystemSetting::get('ceisa_mock_tps_gate_out', []);

            return [
                'status' => 'success',
                'source' => 'PARAM_CEISA_CATALOG_4',
                'data' => [
                    'nomor_kontainer' => $containerNo,
                    'status_gate' => $mockTps['status_gate'] ?? 'GATE_OUT',
                    'tgl_gate_out' => now()->format('Y-m-d H:i:s'),
                    'nama_tps' => $mockTps['nama_tps'] ?? 'KOJA CONTAINER TERMINAL',
                    'lokasi_tps' => $mockTps['lokasi_tps'] ?? 'IDTPP01',
                    'nomor_truk' => $mockTps['nomor_truk'] ?? 'B 9812 UIK',
                ],
            ];
        }

        try {
            $response = Http::withToken($token)
                ->timeout(15)
                ->get("{$this->tpsBaseUrl}/gate-out", [
                    'nomorKontainer' => $containerNo,
                    'nomorSppb' => $nomorSppb,
                ]);

            if ($response->successful()) {
                return [
                    'status' => 'success',
                    'source' => 'LIVE_TPS_ONLINE_API',
                    'data' => $response->json('data') ?? $response->json(),
                ];
            }

            return [
                'status' => 'error',
                'message' => $response->json('message') ?? $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error("Koneksi API TPS Online CEISA Gagal: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Sinkronisasi Gate Out TPS Online langsung ke Proyek Pengapalan.
     */
    public function syncShipmentGateOut(Shipment $shipment): bool
    {
        if (empty($shipment->nomor_kontainer)) {
            return false;
        }

        $res = $this->queryTpsGateOut($shipment->nomor_kontainer, $shipment->sppb_no);

        if (($res['status'] ?? '') === 'success' && !empty($res['data']['tgl_gate_out'])) {
            $gateOutTime = $res['data']['tgl_gate_out'];

            $shipment->update([
                'tgl_gate_out' => $gateOutTime,
                'status_tahap' => 'GATE_OUT',
            ]);

            AuditLog::record('TPS_GATE_OUT_SYNCED', 'Shipment', $shipment->id, null, $res['data']);
            Log::info("Gate Out terdeteksi otomatis untuk kontainer {$shipment->nomor_kontainer}: {$gateOutTime}");

            return true;
        }

        return false;
    }
}
