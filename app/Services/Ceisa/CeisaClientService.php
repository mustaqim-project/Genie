<?php

namespace App\Services\Ceisa;

use App\Models\CeisaApiLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CeisaClientService
{
    protected CeisaAuthService $authService;
    protected string $baseUrl;
    protected string $apiKey;
    protected bool $mockMode;

    public function __construct(CeisaAuthService $authService)
    {
        $this->authService = $authService;
        $this->baseUrl = rtrim(config('ceisa.api.base_url'), '/');
        $this->apiKey = config('ceisa.api.api_key');
        $this->mockMode = (bool) config('ceisa.api.mock_mode', false);
    }

    /**
     * Kirim request ke CEISA 4.0 DJBC
     */
    public function send(string $method, string $endpoint, array $payload = [], ?string $nomorAju = null): array
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        $token = $this->authService->getAccessToken();
        $startTime = microtime(true);

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
            'beacukai-api-key' => $this->apiKey,
        ];

        // MODE MOCK / SIMULASI UNTUK PENGEMBANGAN LOKAL
        if ($this->mockMode) {
            $mockResponse = $this->generateMockResponse($endpoint, $payload, $nomorAju);
            $duration = (int) ((microtime(true) - $startTime) * 1000);

            $this->logRequest($nomorAju, $endpoint, $method, 200, $headers, $payload, $mockResponse, $duration, true);
            return $mockResponse;
        }

        try {
            $request = Http::withHeaders($headers)
                ->timeout(config('ceisa.api.timeout', 30));

            $response = match (strtoupper($method)) {
                'POST' => $request->post($url, $payload),
                'PUT' => $request->put($url, $payload),
                'DELETE' => $request->delete($url, $payload),
                default => $request->get($url, $payload),
            };

            $duration = (int) ((microtime(true) - $startTime) * 1000);
            $statusCode = $response->status();
            $responseData = $response->json() ?? ['raw' => $response->body()];
            $isSuccess = $response->successful();

            $this->logRequest($nomorAju, $endpoint, $method, $statusCode, $headers, $payload, $responseData, $duration, $isSuccess);

            return [
                'status_code' => $statusCode,
                'success' => $isSuccess,
                'data' => $responseData,
            ];
        } catch (\Throwable $e) {
            $duration = (int) ((microtime(true) - $startTime) * 1000);
            $errorData = ['error' => $e->getMessage()];

            $this->logRequest($nomorAju, $endpoint, $method, 500, $headers, $payload, $errorData, $duration, false);

            return [
                'status_code' => 500,
                'success' => false,
                'data' => $errorData,
            ];
        }
    }

    /**
     * Catat log audit integrasi API
     */
    protected function logRequest(
        ?string $nomorAju,
        string $endpoint,
        string $method,
        int $statusCode,
        array $headers,
        array $payload,
        array $response,
        int $duration,
        bool $isSuccess
    ): void {
        try {
            CeisaApiLog::create([
                'nomor_aju' => $nomorAju,
                'endpoint' => $endpoint,
                'method' => strtoupper($method),
                'status_code' => $statusCode,
                'request_header' => $headers,
                'request_body' => $payload,
                'response_body' => $response,
                'duration_ms' => $duration,
                'is_success' => $isSuccess,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Gagal mencatat log CEISA: ' . $e->getMessage());
        }
    }

    /**
     * Simulator respon cerdas CEISA 4.0 sesuai standar Gateway v2 DJBC
     */
    protected function generateMockResponse(string $endpoint, array $payload, ?string $nomorAju): array
    {
        $nomorAju = $nomorAju ?? ($payload['nomorAju'] ?? ($payload['header']['nomorAju'] ?? '040300' . date('Ymd') . '000001'));
        $isBC20 = str_contains($nomorAju, '20') || ($payload['kodeDokumen'] ?? ($payload['header']['kodeDokumen'] ?? '')) === '20';

        // 1. Endpoint Validasi Skema Pre-Check (POST /document/check)
        if (str_contains($endpoint, 'check')) {
            if (!empty($payload)) {
                $isDoc20 = ($payload['kodeDokumen'] ?? ($payload['header']['kodeDokumen'] ?? '')) === '20';
                $isDoc30 = ($payload['kodeDokumen'] ?? ($payload['header']['kodeDokumen'] ?? '')) === '30';

                if ($isDoc20) {
                    if (empty($payload['nomorBc11']) || empty($payload['posBc11'])) {
                        return [
                            'status_code' => 400,
                            'success' => false,
                            'data' => [
                                'status' => 'error',
                                'message' => 'Schema Validation Failed: nomorBc11 dan posBc11 wajib diisi untuk BC 2.0 (PIB)',
                            ],
                        ];
                    }
                }

                if ($isDoc30) {
                    if (empty($payload['kesiapanBarang']) || empty($payload['bankDevisa'])) {
                        return [
                            'status_code' => 400,
                            'success' => false,
                            'data' => [
                                'status' => 'error',
                                'message' => 'Schema Validation Failed: kesiapanBarang dan bankDevisa wajib diisi untuk BC 3.0 (PEB)',
                            ],
                        ];
                    }
                }
            }

            return [
                'status_code' => 200,
                'success' => true,
                'data' => [
                    'status' => 'success',
                    'message' => 'JSON Schema Valid dan sesuai spesifikasi CEISA 4.0 DJBC',
                ],
            ];
        }

        // 2. Endpoint Kirim Dokumen Resmi (POST /document)
        if (str_contains($endpoint, 'document') || (str_contains($endpoint, 'dokumen') && !str_contains($endpoint, 'status') && !str_contains($endpoint, 'respon'))) {
            return [
                'status_code' => 200,
                'success' => true,
                'data' => [
                    'status' => 'OK',
                    'message' => 'Sukses, Data Berhasil Ditambahkan',
                    'idHeader' => 'fbb63046-' . substr(md5($nomorAju), 0, 4) . '-4649-98ae-bfcb0aa82f09',
                    'nomorAju' => $nomorAju,
                    'waktuTerima' => now()->toIso8601String(),
                ],
            ];
        }

        // 3. Endpoint Status & Respon Dokumen Terpadu (GET /status/{nomorAju} atau legacy status-dokumen/respon-dokumen)
        if (str_contains($endpoint, 'status') || str_contains($endpoint, 'respon')) {
            $noDaftar = '0' . rand(10000, 99999);
            $tglDaftar = now()->format('Y-m-d');
            $noRespon = ($isBC20 ? 'SPPB-' : 'NPE-') . '040300-' . date('Y') . '-' . rand(10000, 99999);

            return [
                'status_code' => 200,
                'success' => true,
                'data' => [
                    // Struktur Resmi DJBC Gateway v2
                    'dataStatus' => [
                        [
                            'nomorAju' => $nomorAju,
                            'kodeStatus' => '200',
                            'nomorDaftar' => $noDaftar,
                            'tanggalDaftar' => $tglDaftar,
                            'waktuStatus' => now()->toIso8601String(),
                            'keterangan' => 'Dokumen terdaftar dan telah masuk dalam sistem penjaluran DJBC',
                        ],
                    ],
                    'dataRespon' => [
                        [
                            'nomorAju' => $nomorAju,
                            'kodeRespon' => $isBC20 ? 'SPPB' : 'NPE',
                            'nomorDaftar' => $noDaftar,
                            'tanggalDaftar' => $tglDaftar,
                            'nomorRespon' => $noRespon,
                            'tanggalRespon' => now()->format('Y-m-d'),
                            'waktuRespon' => now()->format('H:i:s'),
                            'waktuStatus' => now()->toIso8601String(),
                            'keterangan' => $isBC20 
                                ? 'Persetujuan Pengeluaran Barang (SPPB) Diterbitkan (Jalur Hijau)' 
                                : 'Nota Pelayanan Ekspor (NPE) Diterbitkan. Barang diizinkan dimuat.',
                            'pesan' => [
                                'Dokumen pabean disetujui.',
                                'Pemeriksaan fisik dinyatakan tidak diperlukan (Jalur Hijau).'
                            ],
                            'Pdf' => null,
                        ],
                    ],
                    // Field Kompatibilitas untuk Layanan Existing
                    'nomorAju' => $nomorAju,
                    'nomorDaftar' => $noDaftar,
                    'tanggalDaftar' => $tglDaftar,
                    'kodeDokumen' => $isBC20 ? '20' : '30',
                    'jalur' => 'HIJAU',
                    'status' => $isBC20 ? 'SPPB' : 'NPE',
                    'keterangan' => $isBC20 
                        ? 'Persetujuan Pengeluaran Barang (SPPB) Diterbitkan (Jalur Hijau)' 
                        : 'Nota Pelayanan Ekspor (NPE) Diterbitkan (Jalur Hijau)',
                    'billing' => [
                        'kodeBilling' => '82026' . rand(1000000000, 9999999999),
                        'totalNominal' => $isBC20 ? 53493750 : 2500000,
                        'tglKadaluarsa' => now()->addDays(3)->format('Y-m-d H:i:s'),
                        'akunPenerimaan' => $isBC20 
                            ? '411123 (PPN Impor) & 411124 (PPh 22 Impor)' 
                            : '411125 (Bea Keluar Ekspor)',
                    ],
                    'sppb' => $isBC20 ? [
                        'nomorSppb' => $noRespon,
                        'tanggalSppb' => now()->format('Y-m-d H:i:s'),
                        'namaPejabat' => 'SISTEM ELEKTRONIK OTOMATIS DJBC',
                    ] : null,
                    'npe' => !$isBC20 ? [
                        'nomorNpe' => $noRespon,
                        'tanggalNpe' => now()->format('Y-m-d H:i:s'),
                        'namaPejabat' => 'SISTEM ELEKTRONIK OTOMATIS DJBC',
                    ] : null,
                ],
            ];
        }

        // 4. Endpoint Kurs Real-Time (GET /kurs/{kodeValuta})
        if (str_contains($endpoint, 'kurs')) {
            $parts = explode('/', trim($endpoint, '/'));
            $curr = strtoupper(end($parts));
            $rates = [
                'USD' => 15850.00,
                'EUR' => 17200.00,
                'CNY' => 2200.00,
                'JPY' => 105.00,
                'SGD' => 12100.00,
                'AUD' => 10400.00,
                'MYR' => 3600.00,
                'GBP' => 20200.00,
            ];
            $valuta = isset($rates[$curr]) ? $curr : 'USD';
            $rate = $rates[$valuta] ?? 15850.00;

            return [
                'status_code' => 200,
                'success' => true,
                'data' => [
                    'kodeValuta' => $valuta,
                    'nilaiKurs' => $rate,
                    'nomorKmk' => 'KMK-NDPBM/' . now()->format('Y-W'),
                    'tanggalMulai' => now()->startOfWeek()->format('Y-m-d'),
                    'tanggalSelesai' => now()->endOfWeek()->format('Y-m-d'),
                ],
            ];
        }

        // 5. Endpoint Tarif HS (GET /tarif-hs)
        if (str_contains($endpoint, 'tarif-hs')) {
            return [
                'status_code' => 200,
                'success' => true,
                'data' => [
                    'posTarif' => '84713020',
                    'bm' => 0,
                    'ppn' => 11,
                    'pph' => 2.5,
                ],
            ];
        }

        return [
            'status_code' => 200,
            'success' => true,
            'data' => ['message' => 'OK', 'endpoint' => $endpoint],
        ];
    }
}
