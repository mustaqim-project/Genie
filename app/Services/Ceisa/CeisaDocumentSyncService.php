<?php

namespace App\Services\Ceisa;

use App\Models\CeisaDocument;
use App\Models\Shipment;
use App\Models\ShipmentDocument;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CeisaDocumentSyncService
{
    protected CeisaDocumentService $documentService;
    protected CeisaClientService $clientService;

    public function __construct(
        CeisaDocumentService $documentService,
        CeisaClientService $clientService
    ) {
        $this->documentService = $documentService;
        $this->clientService = $clientService;
    }

    /**
     * Tarik dan sinkronkan data pabean dari CEISA ke sistem Genie berdasarkan Nomor Aju.
     * Jika shipment belum ada, opsi autoCreateShipment akan membuat shipment baru di Genie.
     */
    public function syncByNomorAju(string $nomorAju, bool $autoCreateShipment = true): array
    {
        $nomorAju = trim($nomorAju);
        
        // 1. Panggil endpoint status & respon resmi CEISA 4.0
        $baseEndpoint = config('ceisa.endpoints.status_dokumen', '/status');
        $endpoint = rtrim($baseEndpoint, '/') . '/' . $nomorAju;
        $response = $this->clientService->send('GET', $endpoint, [], $nomorAju);

        if (!($response['success'] ?? false)) {
            return [
                'success' => false,
                'message' => 'Gagal menarik data dari CEISA: ' . ($response['message'] ?? 'Respon tidak valid.'),
                'nomor_aju' => $nomorAju,
            ];
        }

        $rawBody = $response['data'] ?? [];

        if (strtolower((string) ($rawBody['status'] ?? '')) === 'failed') {
            return [
                'success' => false,
                'message' => 'CEISA (' . $nomorAju . '): ' . ($rawBody['message'] ?? 'Data Tidak Ditemukan'),
                'nomor_aju' => $nomorAju,
            ];
        }

        $ceisaData = (isset($rawBody['data']) && is_array($rawBody['data'])) ? $rawBody['data'] : $rawBody;

        return DB::transaction(function () use ($nomorAju, $ceisaData, $autoCreateShipment) {
            // 2. Cari atau buat record CeisaDocument
            $cleanAju = str_replace('-', '', $nomorAju);
            $doc = CeisaDocument::where('nomor_aju', $nomorAju)
                ->orWhereRaw("REPLACE(nomor_aju, '-', '') = ?", [$cleanAju])
                ->first();

            // Identifikasi jenis dokumen (20 untuk PIB, 30 untuk PEB)
            $jenisDokumen = '20';
            if (strlen($cleanAju) >= 6 && substr($cleanAju, 4, 2) === '30') {
                $jenisDokumen = '30';
            } elseif (!empty($ceisaData['jenisDokumen'])) {
                $jenisDokumen = (string) $ceisaData['jenisDokumen'];
            }

            // 2.5 Ekstrak & normalisasi seluruh riwayat dataStatus & dataRespon CEISA 4.0
            $parsed = self::parseCeisaStatusResponse($ceisaData, $jenisDokumen);
            $status = $parsed['status'];
            $keterangan = $parsed['status_description'];
            $nomorDaftar = $parsed['nomor_daftar'];
            $tanggalDaftar = $parsed['tanggal_daftar'];
            $jalur = $parsed['jalur'];
            $nomorRespon = $parsed['nomor_sppb_npe'];
            $tanggalRespon = $parsed['tanggal_sppb_npe'];
            $kodeBilling = $parsed['kode_billing'];
            $totalBilling = $parsed['total_billing'];
            $tglBillingExpired = $parsed['tgl_jatuh_tempo_billing'];
            $statusBilling = $parsed['status_billing'];

            $documentAttributes = [
                'nomor_aju' => $nomorAju,
                'jenis_dokumen' => $jenisDokumen,
                'kode_kantor' => $ceisaData['kodeKantor'] ?? config('ceisa.company.default_kode_kantor', '040300'),
                'nama_kantor' => config('ceisa.kantor_pabean.' . ($ceisaData['kodeKantor'] ?? '040300'), 'Kantor Pelayanan Bea Cukai'),
                'status' => $status,
                'status_description' => $keterangan,
                'nomor_daftar' => $nomorDaftar,
                'tanggal_daftar' => $tanggalDaftar,
                'jalur' => $jalur ? strtoupper($jalur) : null,
                'nomor_sppb_npe' => $nomorRespon,
                'tanggal_sppb_npe' => $tanggalRespon,
                'kode_billing' => $kodeBilling,
                'total_billing' => $totalBilling > 0 ? $totalBilling : null,
                'tgl_jatuh_tempo_billing' => $tglBillingExpired,
                'status_billing' => $statusBilling,
                'last_response' => $ceisaData,
            ];

            if ($doc) {
                $doc->update($documentAttributes);
            } else {
                $documentAttributes['nama_perusahaan_lokal'] = $ceisaData['namaImportir'] ?? config('ceisa.company.name');
                $documentAttributes['npwp_perusahaan_lokal'] = $ceisaData['npwpImportir'] ?? config('ceisa.company.npwp');
                $doc = CeisaDocument::create($documentAttributes);
            }

            // 3. Hubungkan ke Proyek Pengapalan (Shipment)
            $shipment = $doc->shipment ?? Shipment::where('ceisa_document_id', $doc->id)->first();

            $targetTahap = self::resolveShipmentStage($status, $jalur, $kodeBilling, $shipment?->status_tahap);

            if (!$shipment && $autoCreateShipment) {
                // Cari atau buat klien acuan
                $clientName = $ceisaData['namaImportir'] ?? $ceisaData['namaEksportir'] ?? 'Klien CEISA Impor';
                $clientCode = 'CLI-' . strtoupper(substr(md5($clientName), 0, 8));
                $client = Client::firstOrCreate(
                    ['name' => $clientName],
                    [
                        'code' => $clientCode,
                        'npwp' => $ceisaData['npwpImportir'] ?? $ceisaData['npwpEksportir'] ?? '0000000000000000',
                        'email' => 'client_' . strtolower(substr(md5($clientName), 0, 6)) . '@genie.test',
                        'address' => $ceisaData['alamatImportir'] ?? 'Indonesia',
                    ]
                );

                $prefix = ($jenisDokumen === '20') ? 'ATS-IMP-' : 'ATS-EXP-';
                $projectNo = $prefix . date('Ym') . '-' . str_pad(Shipment::count() + 1, 4, '0', STR_PAD_LEFT);

                $shipment = Shipment::create([
                    'project_no' => $projectNo,
                    'client_id' => $client->id,
                    'jenis_layanan' => ($jenisDokumen === '20') ? 'IMPORT_BC20' : 'EKSPORT_BC30',
                    'nomor_bl_awb' => $ceisaData['nomorBl'] ?? ('BL-' . strtoupper(substr(md5($nomorAju), 0, 8))),
                    'tgl_bl_awb' => $ceisaData['tanggalBl'] ?? now(),
                    'pelabuhan_muat' => $ceisaData['pelabuhanMuat'] ?? 'PORT_ORIGIN',
                    'pelabuhan_bongkar' => $ceisaData['pelabuhanBongkar'] ?? 'IDTPP',
                    'eta_date' => now()->addDays(2),
                    'total_kontainer' => (int) ($ceisaData['jumlahKontainer'] ?? 1),
                    'nomor_kontainer' => $ceisaData['nomorKontainer'] ?? null,
                    'total_kemasan' => (int) ($ceisaData['jumlahKemasan'] ?? 100),
                    'jenis_kemasan' => $ceisaData['jenisKemasan'] ?? 'PKG',
                    'total_bruto' => (float) ($ceisaData['bruto'] ?? 0),
                    'total_netto' => (float) ($ceisaData['netto'] ?? 0),
                    'kode_valuta' => $ceisaData['kodeValuta'] ?? 'USD',
                    'total_nilai_valuta' => (float) ($ceisaData['cif'] ?? $ceisaData['fob'] ?? 0),
                    'ndbm_kurs' => (float) ($ceisaData['ndbm'] ?? 16000),
                    'total_nilai_idr' => (float) ($ceisaData['cifRupiah'] ?? 0),
                    'status_tahap' => $targetTahap,
                    'customs_channel' => $jalur ? strtoupper($jalur) : null,
                    'ceisa_document_id' => $doc->id,
                    'catatan' => "Tarik otomatis dari CEISA 4.0 (Aju: {$nomorAju})",
                    'created_by_user_id' => auth()->id(),
                ]);
            }

            if ($shipment) {
                // Perbarui status shipment terkini sesuai tahapan di Genie
                $shipmentUpdates = [
                    'ceisa_document_id' => $doc->id,
                    'status_tahap' => self::resolveShipmentStage($status, $jalur, $kodeBilling, $shipment->status_tahap),
                ];

                if ($jalur) {
                    $shipmentUpdates['customs_channel'] = strtoupper($jalur);
                }

                $shipment->update($shipmentUpdates);
            }

            return [
                'success' => true,
                'message' => "Data CEISA untuk Aju {$nomorAju} berhasil disinkronkan ke sistem Genie.",
                'document' => $doc->fresh(),
                'shipment' => $shipment ? $shipment->fresh() : null,
                'status' => $status,
                'jalur' => $jalur,
                'nomor_sppb_npe' => $nomorRespon,
                'kode_billing' => $kodeBilling,
            ];
        });
    }

    /**
     * Tarik massal sinkronisasi seluruh dokumen CEISA aktif yang belum SPPB / NPE
     */
    public function syncAllActiveDocuments(bool $includeCompleted = false): array
    {
        $query = CeisaDocument::query()->latest();
        if (!$includeCompleted) {
            $query->whereNotIn('status', ['SPPB', 'NPE', 'REJECT']);
        }

        $activeDocs = $query->limit(50)->get();

        $syncedCount = 0;
        $failedCount = 0;

        foreach ($activeDocs as $doc) {
            try {
                $res = $this->syncByNomorAju($doc->nomor_aju, true);
                if ($res['success'] ?? false) {
                    $syncedCount++;
                } else {
                    $failedCount++;
                }
            } catch (\Throwable $e) {
                $failedCount++;
                Log::warning("Gagal auto-sync CEISA aju {$doc->nomor_aju}: " . $e->getMessage());
            }
        }

        return [
            'total_active' => $activeDocs->count(),
            'synced' => $syncedCount,
            'failed' => $failedCount,
        ];
    }

    /**
     * Tarik SEMUA data referensi & dokumen dari CEISA 4.0 ke Genie sekaligus:
     * 1. Dokumen Pabean (BC 2.0 / BC 3.0) & Shipment terkait
     * 2. Kurs Pajak / NDPBM Valuta Asing Utama
     * 3. Master Pos Tarif HS Code & Regulasi Lartas BTKI
     */
    public function syncAllFromCeisa(bool $includeKurs = true, bool $includeHsCodes = true): array
    {
        $documents = $this->syncAllActiveDocuments(true);

        $kursSummary = [
            'synced' => 0,
            'sources' => [],
        ];
        if ($includeKurs) {
            $rates = app(CeisaKursService::class)->syncMajorRates(true);
            $kursSummary['synced'] = count($rates);
            $kursSummary['rates'] = $rates;
            $kursSummary['sources'] = array_values(array_unique(array_map(fn ($r) => $r['source'] ?? 'UNKNOWN', $rates)));
        }

        $hsSummary = ['success' => true, 'total_in_db' => 0, 'regulations_synced' => 0];
        if ($includeHsCodes) {
            $hsSummary = app(CeisaBtkiTariffSyncService::class)->syncFromBtkiRegistry(true);
        }

        return [
            'success' => true,
            'mode' => config('ceisa.api.mock_mode') ? 'SANDBOX / MOCK' : 'LIVE PRODUCTION',
            'documents' => $documents,
            'kurs' => $kursSummary,
            'hs_codes' => $hsSummary,
        ];
    }

    /**
     * Petakan status/respon/jalur CEISA ke tahapan workflow Shipment di Genie
     * (CEISA_SUBMITTED -> BILLING_ISSUED -> TRAFFIC_HOLD / TRAFFIC_SPPB -> TRAFFIC_GATE_OUT).
     */
    public static function resolveShipmentStage(
        ?string $status,
        ?string $jalur = null,
        ?string $kodeBilling = null,
        ?string $currentStage = null
    ): string {
        $upperStatus = strtoupper(trim((string) $status));
        $upperJalur = strtoupper(trim((string) $jalur));

        // Pertahankan tahapan pasca-kepabeanan jika sudah selesai di TPS/gudang/keuangan
        if (in_array($currentStage, ['DELIVERED_POD', 'INVOICED_P19', 'COMPLETED'], true)) {
            return $currentStage;
        }

        if (in_array($upperStatus, ['GATE_OUT', 'GATE OUT', 'SPPB_GATE_OUT', 'SELESAI'], true)) {
            return 'TRAFFIC_GATE_OUT';
        }

        if (in_array($upperStatus, ['SPPB', 'NPE'], true)) {
            return 'TRAFFIC_SPPB';
        }

        if ($upperJalur === 'MERAH' || str_contains($upperStatus, 'SPJM') || str_contains($upperStatus, 'PERIKSA') || str_contains($upperStatus, 'NHI') || str_contains($upperStatus, 'HOLD') || str_contains($upperStatus, 'REJECT')) {
            return 'TRAFFIC_HOLD';
        }

        if (in_array($currentStage, ['DO_PROCESSING', 'DO_RELEASED'], true)) {
            return $currentStage;
        }

        if (!empty($kodeBilling)) {
            return 'BILLING_ISSUED';
        }

        return 'CEISA_SUBMITTED';
    }

    /**
     * Normalisasi dan parsing response CEISA 4.0 (/status/{nomorAju}),
     * termasuk array dataStatus (kodeProses) dan dataRespon (2003 SPPB, 2020 BILLING, SPJM, NPD, SPTNP)
     * yang sering dikembalikan dalam urutan tidak kronologis.
     */
    public static function parseCeisaStatusResponse(array $ceisaData, string $jenisDokumen = '20'): array
    {
        $nomorDaftar = $ceisaData['nomorDaftar'] ?? null;
        $tanggalDaftar = self::normalizeCeisaDate($ceisaData['tanggalDaftar'] ?? null);
        $jalur = $ceisaData['jalur'] ?? null;

        // 1. Urutkan dataStatus secara kronologis berdasarkan waktuStatus
        $dataStatus = is_array($ceisaData['dataStatus'] ?? null) ? $ceisaData['dataStatus'] : [];
        usort($dataStatus, function ($a, $b) {
            return strcmp((string) ($a['waktuStatus'] ?? ''), (string) ($b['waktuStatus'] ?? ''));
        });

        $hasPaymentVerification = false;
        $hasGateOut = false;
        $hasSelesai = false;
        $hasSpjmStatus = false;
        $hasSptnpStatus = false;
        $latestStatusItem = null;

        foreach ($dataStatus as $item) {
            if (!empty($item['nomorDaftar'])) {
                $nomorDaftar = $item['nomorDaftar'];
            }
            if (!empty($item['tanggalDaftar'])) {
                $tanggalDaftar = self::normalizeCeisaDate($item['tanggalDaftar']) ?? $tanggalDaftar;
            }

            $kodeProses = trim((string) ($item['kodeProses'] ?? $item['kodeStatus'] ?? ''));
            $ketUpper = strtoupper((string) ($item['keterangan'] ?? ''));

            if ($kodeProses === '210' || str_contains($ketUpper, 'PAYMENT') || (is_numeric($kodeProses) && (int) $kodeProses >= 210)) {
                $hasPaymentVerification = true;
            }
            if ($kodeProses === '501' || str_contains($ketUpper, 'GATE OUT')) {
                $hasGateOut = true;
            }
            if ($kodeProses === '800' || str_contains($ketUpper, 'SELESAI')) {
                $hasSelesai = true;
            }
            if (str_contains($ketUpper, 'SPJM') || str_contains($ketUpper, 'PEMERIKSAAN FISIK')) {
                $hasSpjmStatus = true;
            }
            if ($kodeProses === '440' || str_contains($ketUpper, 'SPTNP')) {
                $hasSptnpStatus = true;
            }

            $latestStatusItem = $item;
        }

        // 2. Urutkan & pindai seluruh dataRespon (tidak hanya elemen terakhir!)
        $dataRespon = is_array($ceisaData['dataRespon'] ?? null) ? $ceisaData['dataRespon'] : [];
        usort($dataRespon, function ($a, $b) {
            $tA = (string) ($a['waktuRespon'] ?? $a['tanggalRespon'] ?? '');
            $tB = (string) ($b['waktuRespon'] ?? $b['tanggalRespon'] ?? '');
            return strcmp($tA, $tB);
        });

        $hasSppbNpe = false;
        $sppbNpeType = ($jenisDokumen === '30') ? 'NPE' : 'SPPB';
        $nomorSppbNpe = null;
        $tanggalSppbNpe = null;
        $hasSpjm = false;
        $hasSptnp = false;
        $hasReject = false;
        $kodeBilling = null;
        $kodeBillingSptnp = null;

        foreach ($dataRespon as $respon) {
            $kode = strtoupper(trim((string) ($respon['kodeRespon'] ?? '')));
            $ket = strtoupper(trim((string) ($respon['keterangan'] ?? '')));

            if ($kode === 'SPPB' || $kode === 'NPE' || $kode === '2003' || str_contains($ket, 'SPPB') || str_contains($ket, 'NPE')) {
                $hasSppbNpe = true;
                $sppbNpeType = ($kode === 'NPE' || str_contains($ket, 'NPE')) ? 'NPE' : 'SPPB';
                if (!empty($respon['nomorRespon'])) {
                    $nomorSppbNpe = $respon['nomorRespon'];
                }
                $tanggalSppbNpe = self::normalizeCeisaDate($respon['tanggalRespon'] ?? $respon['waktuRespon'] ?? null) ?? $tanggalSppbNpe;
            } elseif ($kode === 'SPJM' || str_contains($ket, 'SPJM')) {
                $hasSpjm = true;
            } elseif ($kode === 'BILLING' || $kode === '2020' || $kode === '2028' || str_contains($ket, 'BILLING')) {
                if (str_contains($ket, 'SPTNP') || $kode === '2028') {
                    $kodeBillingSptnp = $respon['nomorRespon'] ?? $kodeBillingSptnp;
                } else {
                    $kodeBilling = $respon['nomorRespon'] ?? $kodeBilling;
                }
            } elseif ($kode === '2026' || str_contains($ket, 'SPTNP')) {
                $hasSptnp = true;
            } elseif (str_contains($ket, 'REJECT') || str_contains($ket, 'PENOLAKAN')) {
                $hasReject = true;
            }
        }

        // Fallback struktur billing/sppb/npe langsung jika ada (mock/legacy)
        $totalBilling = 0.0;
        $tglBillingExpired = null;
        if (!empty($ceisaData['billing']) && is_array($ceisaData['billing'])) {
            $kodeBilling = $ceisaData['billing']['kodeBilling'] ?? $kodeBilling;
            $totalBilling = (float) ($ceisaData['billing']['totalNominal'] ?? 0);
            $tglBillingExpired = $ceisaData['billing']['tglKadaluarsa'] ?? null;
        }
        if (!empty($ceisaData['sppb']) && is_array($ceisaData['sppb'])) {
            $hasSppbNpe = true;
            $sppbNpeType = 'SPPB';
            $nomorSppbNpe = $ceisaData['sppb']['nomorSppb'] ?? $nomorSppbNpe;
            $tanggalSppbNpe = self::normalizeCeisaDate($ceisaData['sppb']['tanggalSppb'] ?? null) ?? $tanggalSppbNpe;
        }
        if (!empty($ceisaData['npe']) && is_array($ceisaData['npe'])) {
            $hasSppbNpe = true;
            $sppbNpeType = 'NPE';
            $nomorSppbNpe = $ceisaData['npe']['nomorNpe'] ?? $nomorSppbNpe;
            $tanggalSppbNpe = self::normalizeCeisaDate($ceisaData['npe']['tanggalNpe'] ?? null) ?? $tanggalSppbNpe;
        }

        // 3. Tentukan status billing (pembayaran PIB/PEB utama)
        $explicitPayStatus = $ceisaData['billing']['statusBayar'] ?? null;
        if ($explicitPayStatus) {
            $statusBilling = strtoupper((string) $explicitPayStatus);
        } elseif ($hasPaymentVerification || $hasSppbNpe || $hasGateOut || $hasSelesai || $hasSpjm) {
            $statusBilling = 'PAID';
        } else {
            $statusBilling = 'UNPAID';
        }

        // 4. Tentukan status dokumen pabean (hindari menyimpan wrapper API "Success")
        $rawApiStatus = trim((string) ($ceisaData['status'] ?? ''));
        $isWrapperSuccess = in_array(strtolower($rawApiStatus), ['success', 'true', 'ok', ''], true);

        if ($hasGateOut || $hasSelesai) {
            $status = 'GATE_OUT';
        } elseif ($hasSppbNpe) {
            $status = $sppbNpeType;
        } elseif ($hasSpjm || $hasSpjmStatus) {
            $status = 'SPJM';
        } elseif ($hasReject) {
            $status = 'REJECT';
        } elseif ($latestStatusItem) {
            $kodeProses = strtoupper(trim((string) ($latestStatusItem['kodeStatus'] ?? $latestStatusItem['kodeProses'] ?? '')));
            $status = match ($kodeProses) {
                '001' => 'DRAFT',
                '104', '105', '106', '110' => 'SUBMITTED',
                '210', '220', '230', '240' => 'PAID',
                '400' => 'PERIKSA',
                '440' => 'SPTNP',
                default => ($kodeProses !== '' ? $kodeProses : ($isWrapperSuccess ? 'SUBMITTED' : $rawApiStatus)),
            };
        } else {
            $status = $isWrapperSuccess ? 'SUBMITTED' : $rawApiStatus;
        }

        // 5. Ringkasan deskripsi yang informatif
        $descParts = [];
        if ($hasGateOut || $hasSelesai) {
            $descParts[] = $hasSelesai ? 'Selesai Proses / Gate Out TPS' : 'Gate Out TPS';
        }
        if ($hasSppbNpe) {
            $descParts[] = $nomorSppbNpe ? "{$sppbNpeType} ({$nomorSppbNpe})" : $sppbNpeType;
        }
        if ($hasSpjm || $hasSpjmStatus) {
            $descParts[] = 'SPJM';
        }
        if ($hasSptnp || $hasSptnpStatus) {
            $descParts[] = 'SPTNP';
        }
        $statusDescription = !empty($descParts)
            ? implode(' • ', $descParts)
            : ($latestStatusItem['keterangan'] ?? $ceisaData['keterangan'] ?? 'Disinkronkan dari CEISA 4.0');

        // 6. Inferensi jalur jika tidak dikirim langsung oleh CEISA
        if (!$jalur) {
            if ($hasSpjm || $hasSpjmStatus) {
                $jalur = 'MERAH';
            } elseif ($hasSppbNpe) {
                $jalur = 'HIJAU';
            }
        }

        return [
            'status' => $status,
            'status_description' => $statusDescription,
            'nomor_daftar' => $nomorDaftar,
            'tanggal_daftar' => $tanggalDaftar,
            'jalur' => $jalur,
            'nomor_sppb_npe' => $nomorSppbNpe,
            'tanggal_sppb_npe' => $tanggalSppbNpe,
            'kode_billing' => $kodeBilling ?? $kodeBillingSptnp,
            'total_billing' => $totalBilling,
            'tgl_jatuh_tempo_billing' => $tglBillingExpired,
            'status_billing' => $statusBilling,
        ];
    }

    protected static function normalizeCeisaDate(?string $value): ?string
    {
        if (!$value) {
            return null;
        }
        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }
        try {
            // Format CEISA Indonesia sering "01-10-2026" (d-m-Y) atau "2026-10-01 13:56:00"
            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $trimmed)) {
                return \Carbon\Carbon::createFromFormat('d-m-Y', $trimmed)->format('Y-m-d');
            }
            return \Carbon\Carbon::parse($trimmed)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
