<?php

namespace App\Services\Ceisa;

use App\Models\CeisaExchangeRate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CeisaKursService
{
    protected CeisaClientService $client;

    public function __construct(CeisaClientService $client)
    {
        $this->client = $client;
    }

    /**
     * Dapatkan Kurs Pajak (NDPBM / KMK) yang aktif untuk valuta dan tanggal tertentu.
     * Alur: Cache -> Database Lokal (CeisaExchangeRate) -> API CEISA 4.0 (/kurs/{valuta}) -> Fallback
     */
    public function getActiveKurs(string $kodeValuta = 'USD', ?string $targetDate = null): array
    {
        $valuta = strtoupper(trim($kodeValuta));
        $date = $targetDate ? Carbon::parse($targetDate)->format('Y-m-d') : now()->format('Y-m-d');
        $cacheKey = "ceisa_kurs_{$valuta}_{$date}";

        // 1. Cek Cache In-Memory / Redis / File
        $cached = Cache::get($cacheKey);
        if ($cached) {
            $cached['source'] = 'CACHE';
            return $cached;
        }

        // 2. Cek Database Lokal
        $dbRecord = CeisaExchangeRate::where('kode_valuta', $valuta)
            ->activeOn($date)
            ->latest('created_at')
            ->first();

        if ($dbRecord) {
            $result = [
                'success' => true,
                'kode_valuta' => $dbRecord->kode_valuta,
                'nilai_kurs' => (float) $dbRecord->nilai_kurs,
                'tanggal_mulai' => $dbRecord->tanggal_mulai->format('Y-m-d'),
                'tanggal_selesai' => $dbRecord->tanggal_selesai->format('Y-m-d'),
                'nomor_kmk' => $dbRecord->nomor_kmk,
                'source' => $dbRecord->sumber,
            ];

            // Simpan ke Cache sampai tanggal selesai
            $ttl = Carbon::parse($dbRecord->tanggal_selesai)->endOfDay();
            Cache::put($cacheKey, $result, $ttl);

            return $result;
        }

        // 3. Tarik dari API Resmi CEISA 4.0 Open API
        try {
            $response = $this->client->get("/kurs/{$valuta}");

            if ($response['success'] && !empty($response['data'])) {
                $apiData = $response['data'];
                $nilaiKurs = (float) ($apiData['nilaiKurs'] ?? $apiData['nilai_kurs'] ?? 0);
                $tglMulai = $apiData['tanggalMulai'] ?? $apiData['tanggal_mulai'] ?? Carbon::parse($date)->startOfWeek()->format('Y-m-d');
                $tglSelesai = $apiData['tanggalSelesai'] ?? $apiData['tanggal_selesai'] ?? Carbon::parse($date)->endOfWeek()->format('Y-m-d');
                $nomorKmk = $apiData['nomorKmk'] ?? $apiData['nomor_kmk'] ?? ('KMK-NDPBM/' . Carbon::parse($date)->format('Y-W'));

                // Simpan atau update ke tabel lokal
                $newRecord = CeisaExchangeRate::updateOrCreate(
                    [
                        'kode_valuta' => $valuta,
                        'tanggal_mulai' => $tglMulai,
                        'tanggal_selesai' => $tglSelesai,
                    ],
                    [
                        'nilai_kurs' => $nilaiKurs,
                        'nomor_kmk' => $nomorKmk,
                        'sumber' => 'CEISA_OPENAPI',
                    ]
                );

                $result = [
                    'success' => true,
                    'kode_valuta' => $valuta,
                    'nilai_kurs' => $nilaiKurs,
                    'tanggal_mulai' => $tglMulai,
                    'tanggal_selesai' => $tglSelesai,
                    'nomor_kmk' => $nomorKmk,
                    'source' => 'CEISA_OPENAPI',
                ];

                $ttl = Carbon::parse($tglSelesai)->endOfDay();
                Cache::put($cacheKey, $result, $ttl);

                return $result;
            }
        } catch (\Throwable $e) {
            Log::warning("Gagal fetch kurs dari CEISA 4.0: {$e->getMessage()}");
        }

        // 4. Fallback Default jika API Pabean offline dan belum ada di DB lokal
        $fallbackRates = [
            'USD' => 15850.00,
            'EUR' => 17200.00,
            'CNY' => 2200.00,
            'JPY' => 105.00,
            'SGD' => 12100.00,
            'AUD' => 10400.00,
            'MYR' => 3600.00,
            'GBP' => 20200.00,
        ];

        $fallbackValue = $fallbackRates[$valuta] ?? 15850.00;
        $tglMulai = Carbon::parse($date)->startOfWeek()->format('Y-m-d');
        $tglSelesai = Carbon::parse($date)->endOfWeek()->format('Y-m-d');

        // Simpan fallback ke DB agar persist
        CeisaExchangeRate::firstOrCreate(
            [
                'kode_valuta' => $valuta,
                'tanggal_mulai' => $tglMulai,
                'tanggal_selesai' => $tglSelesai,
            ],
            [
                'nilai_kurs' => $fallbackValue,
                'nomor_kmk' => 'KMK-DEFAULT-FALLBACK',
                'sumber' => 'MANUAL_FALLBACK',
            ]
        );

        return [
            'success' => true,
            'kode_valuta' => $valuta,
            'nilai_kurs' => $fallbackValue,
            'tanggal_mulai' => $tglMulai,
            'tanggal_selesai' => $tglSelesai,
            'nomor_kmk' => 'KMK-DEFAULT-FALLBACK',
            'source' => 'FALLBACK',
        ];
    }

    /**
     * Sinkronisasi massal seluruh kurs valuta asing utama
     */
    public function syncMajorRates(): array
    {
        $currencies = ['USD', 'EUR', 'CNY', 'JPY', 'SGD', 'AUD', 'MYR', 'GBP'];
        $results = [];

        foreach ($currencies as $curr) {
            $results[$curr] = $this->getActiveKurs($curr);
        }

        return $results;
    }
}
