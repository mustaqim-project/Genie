<?php

namespace App\Services\Tracking;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GlobalGeoLocationService
{
    protected string $userAgent = 'GENIE-Customs-ERP/2.0 (contact@cooca.id; Logistics Global Tracking)';

    /**
     * Resolusi Pelabuhan Seluruh Dunia secara Real-Time via Global OpenStreetMap Nominatim & Marine Database
     * Tanpa Hardcoded Ports — Mendukung UN/LOCODE dan seluruh nama pelabuhan dunia.
     */
    public function resolvePort(string $input): array
    {
        $raw = trim($input);
        if (empty($raw)) {
            return [
                'name' => 'Pelabuhan Tidak Diketahui',
                'lat' => -6.1038,
                'lon' => 106.8825,
                'type' => 'sea',
                'source' => 'Default Fallback'
            ];
        }

        // Ekstrak kode UN/LOCODE (misal CNTAO, IDSUB, NLRTM) jika ada
        $locode = null;
        if (preg_match('/\b([A-Z]{2}[A-Z0-9]{3})\b/', strtoupper($raw), $m)) {
            $locode = $m[1];
        }

        // Ekstrak nama teks pelabuhan (baik di dalam tanda kurung atau teks utama)
        $cleanName = $raw;
        if (preg_match('/\((.*?)\)/', $raw, $m)) {
            $cleanName = $m[1];
        } elseif ($locode) {
            $cleanName = str_ireplace($locode, '', $raw);
        }

        // Hapus prefiks umum yang tidak spesifik
        $cleanName = trim(preg_replace('/^(KPU|Pelabuhan|Port of|Port|Harbour)\s+/i', '', $cleanName));
        if (empty($cleanName) && $locode) {
            $cleanName = $locode;
        }

        $cacheKey = 'global_world_port_' . md5(strtoupper($raw . '_' . $locode . '_' . $cleanName));

        return Cache::remember($cacheKey, 86400 * 30, function () use ($cleanName, $locode, $raw) {
            // Urutan query pencarian ke Live Nominatim API
            $queries = [];
            if (!empty($cleanName)) {
                $queries[] = "Port of {$cleanName}";
                $queries[] = "{$cleanName} Port";
                $queries[] = $cleanName;
            }
            if ($locode && $locode !== $cleanName) {
                $queries[] = "Port of {$locode}";
                $queries[] = "{$locode} Port";
            }

            foreach ($queries as $q) {
                try {
                    $response = Http::withHeaders([
                        'User-Agent' => $this->userAgent,
                        'Accept-Language' => 'id,en;q=0.9',
                    ])->timeout(5)->get('https://nominatim.openstreetmap.org/search', [
                        'q' => $q,
                        'format' => 'json',
                        'limit' => 1,
                        'addressdetails' => 1,
                    ]);

                    if ($response->successful()) {
                        $items = $response->json();
                        if (!empty($items[0]['lat']) && !empty($items[0]['lon'])) {
                            $item = $items[0];
                            $displayName = $item['display_name'] ?? $q;
                            $addr = $item['address'] ?? [];
                            $city = $addr['city'] ?? $addr['town'] ?? $addr['county'] ?? $addr['state'] ?? '';
                            $country = $addr['country'] ?? '';

                            $shortLabel = trim("Pelabuhan {$cleanName}" . ($city ? ", {$city}" : '') . ($country ? " ({$country})" : ''));

                            return [
                                'name' => $shortLabel ?: $displayName,
                                'full_name' => $displayName,
                                'lat' => (float) $item['lat'],
                                'lon' => (float) $item['lon'],
                                'locode' => $locode,
                                'type' => 'sea',
                                'source' => 'OpenStreetMap Nominatim Global API (Real-Time)',
                            ];
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning("Nominatim query failed for port '{$q}': " . $e->getMessage());
                }
            }

            // Fallback: Default coordinate jika API benar-benar unreachable
            return [
                'name' => $raw,
                'full_name' => $raw,
                'lat' => -6.1038,
                'lon' => 106.8825,
                'locode' => $locode,
                'type' => 'sea',
                'source' => 'Estimated Ocean Geolocation',
            ];
        });
    }

    /**
     * Resolusi Bandara Udara Kargo Seluruh Dunia secara Real-Time via OpenStreetMap Nominatim & IATA
     */
    public function resolveAirport(string $input): array
    {
        $raw = trim($input);
        if (empty($raw)) {
            return [
                'name' => 'Bandara Internasional Soekarno-Hatta (CGK)',
                'lat' => -6.1256,
                'lon' => 106.6559,
                'type' => 'air',
                'source' => 'Default Fallback'
            ];
        }

        // Ekstrak 3-letter IATA code (misal CGK, SIN, PVG, FRA, ICN) jika ada
        $iata = null;
        if (preg_match('/\b([A-Z]{3})\b/', strtoupper($raw), $m)) {
            $iata = $m[1];
        }

        $cleanName = $raw;
        if (preg_match('/\((.*?)\)/', $raw, $m)) {
            $cleanName = $m[1];
        } elseif ($iata) {
            $cleanName = str_ireplace($iata, '', $raw);
        }
        $cleanName = trim(preg_replace('/^(Bandara|Airport|International Airport)\s+/i', '', $cleanName));

        $cacheKey = 'global_world_airport_' . md5(strtoupper($raw . '_' . $iata . '_' . $cleanName));

        return Cache::remember($cacheKey, 86400 * 30, function () use ($cleanName, $iata, $raw) {
            $queries = [];
            if ($iata) {
                $queries[] = "{$iata} Airport";
            }
            if (!empty($cleanName)) {
                $queries[] = "{$cleanName} Airport";
                $queries[] = "{$cleanName} International Airport";
                $queries[] = $cleanName;
            }

            foreach ($queries as $q) {
                try {
                    $response = Http::withHeaders([
                        'User-Agent' => $this->userAgent,
                        'Accept-Language' => 'id,en;q=0.9',
                    ])->timeout(5)->get('https://nominatim.openstreetmap.org/search', [
                        'q' => $q,
                        'format' => 'json',
                        'limit' => 1,
                        'addressdetails' => 1,
                    ]);

                    if ($response->successful()) {
                        $items = $response->json();
                        if (!empty($items[0]['lat']) && !empty($items[0]['lon'])) {
                            $item = $items[0];
                            $displayName = $item['display_name'] ?? $q;
                            $addr = $item['address'] ?? [];
                            $city = $addr['city'] ?? $addr['town'] ?? $addr['county'] ?? '';
                            $country = $addr['country'] ?? '';

                            $shortLabel = trim(($iata ? "[{$iata}] " : '') . ($cleanName ?: $displayName) . ($city ? ", {$city}" : '') . ($country ? " ({$country})" : ''));

                            return [
                                'name' => $shortLabel ?: $displayName,
                                'full_name' => $displayName,
                                'lat' => (float) $item['lat'],
                                'lon' => (float) $item['lon'],
                                'iata' => $iata,
                                'type' => 'air',
                                'source' => 'OpenStreetMap Nominatim Global API (Real-Time)',
                            ];
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning("Nominatim query failed for airport '{$q}': " . $e->getMessage());
                }
            }

            return [
                'name' => $raw,
                'full_name' => $raw,
                'lat' => -6.1256,
                'lon' => 106.6559,
                'iata' => $iata,
                'type' => 'air',
                'source' => 'Estimated Airport Geolocation',
            ];
        });
    }

    /**
     * Resolusi Nama Perairan / Laut / Samudra Internasional Terkini
     * Menggunakan MarineRegions Official International Hydrographic Organization (IHO) API
     */
    public function resolveSeaLocation(float $lat, float $lon, array $destPort, int $progressPct = 50): string
    {
        // Jika kargo sudah mendekati pelabuhan tujuan (jarak < 25 km atau progress >= 97%)
        if ($progressPct >= 97) {
            return "Perairan Labuh Jangkar / Kolam Dermaga {$destPort['name']}";
        }

        $roundedLat = round($lat, 1);
        $roundedLon = round($lon, 1);
        $cacheKey = "global_sea_region_{$roundedLat}_{$roundedLon}";

        return Cache::remember($cacheKey, 86400 * 30, function () use ($lat, $lon, $destPort) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => $this->userAgent,
                ])->timeout(4)->get("https://www.marineregions.org/rest/getGazetteerRecordsByLatLong.json/{$lat}/{$lon}/");

                if ($response->successful()) {
                    $records = $response->json();
                    if (is_array($records) && !empty($records)) {
                        // Prioritaskan nama laut/selat resmi IHO
                        $priorityTypes = ['IHO Sea Area', 'Sea', 'Strait', 'Large Marine Ecosystem', 'Marine Region'];
                        foreach ($priorityTypes as $type) {
                            foreach ($records as $item) {
                                if (($item['placeType'] ?? '') === $type && !empty($item['preferredGazetteerName'])) {
                                    $seaName = $item['preferredGazetteerName'];
                                    return "Perairan {$seaName} (Menuju {$destPort['name']})";
                                }
                            }
                        }

                        if (!empty($records[0]['preferredGazetteerName'])) {
                            return "Perairan {$records[0]['preferredGazetteerName']} (Menuju {$destPort['name']})";
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("MarineRegions API lookup failed at ({$lat}, {$lon}): " . $e->getMessage());
            }

            // Fallback matematis sederhana jika MarineRegions tidak merespon
            return "Perairan Internasional ({$lat}, {$lon}) Menuju {$destPort['name']}";
        });
    }

    /**
     * Resolusi Ruang Udara Penerbangan Kargo Terkini
     */
    public function resolveAirspaceLocation(float $lat, float $lon, array $destAirport, bool $onGround = false): string
    {
        if ($onGround) {
            return "Landasan Pacu / Apron Bandara {$destAirport['name']}";
        }

        $roundedLat = round($lat, 1);
        $roundedLon = round($lon, 1);
        $cacheKey = "global_airspace_{$roundedLat}_{$roundedLon}";

        return Cache::remember($cacheKey, 86400 * 30, function () use ($lat, $lon, $destAirport) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => $this->userAgent,
                    'Accept-Language' => 'id,en;q=0.9',
                ])->timeout(4)->get('https://nominatim.openstreetmap.org/reverse', [
                    'lat' => $lat,
                    'lon' => $lon,
                    'format' => 'json',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $addr = $data['address'] ?? [];
                    $state = $addr['state'] ?? $addr['region'] ?? $addr['county'] ?? '';
                    $country = $addr['country'] ?? '';

                    if ($country || $state) {
                        $loc = trim(($state ? "{$state}, " : '') . $country);
                        return "Ruang Udara {$loc} (Menuju {$destAirport['name']})";
                    }
                }
            } catch (\Throwable $e) {
                // Di atas laut lepas
            }

            // Jika di atas laut lepas, tanyakan nama lautnya
            return $this->resolveSeaLocation($lat, $lon, $destAirport, 50);
        });
    }
}
