<?php

namespace App\Services\Ceisa;

use App\Models\CeisaDocument;
use App\Models\CeisaExchangeRate;
use App\Models\Client;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Smalot\PdfParser\Parser as PdfParser;

class CeisaExportImportService
{
    public function __construct(
        protected CeisaDocumentSyncService $syncService
    ) {}

    /**
     * Impor dokumen ekspor Portal CEISA 4.0 (.xlsx, .xls, .json, .pdf) ke CeisaDocument, Shipment, dan ShipmentItem,
     * lalu gabungkan dengan status & respon real-time dari API CEISA (/status/{nomorAju}).
     */
    public function importFromUploadedFile(
        UploadedFile $file,
        ?string $fallbackNomorAju = null,
        bool $syncStatusAfter = true
    ): array {
        $ext = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();

        if (!$path || !is_file($path)) {
            return ['success' => false, 'message' => 'File unggahan tidak dapat dibaca.'];
        }

        try {
            $parsed = match ($ext) {
                'json', 'txt' => $this->parseJsonFile($path),
                'xlsx', 'xls' => $this->parseExcelFile($path),
                'pdf' => $this->parsePdfFile($path),
                default => throw new \InvalidArgumentException("Format file .{$ext} tidak didukung. Gunakan .xlsx, .json, atau .pdf dari Portal CEISA 4.0."),
            };
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Gagal membaca file CEISA: ' . $e->getMessage(),
            ];
        }

        $nomorAju = trim((string) ($parsed['nomor_aju'] ?? $fallbackNomorAju ?? ''));
        $cleanAju = preg_replace('/[^A-Za-z0-9]/', '', $nomorAju);
        if ($cleanAju === '') {
            return [
                'success' => false,
                'message' => 'Nomor Aju (26 digit) tidak ditemukan di dalam file. Silakan isi kolom Nomor Aju secara manual.',
            ];
        }
        $nomorAju = $cleanAju;

        $jenisDokumen = (string) ($parsed['jenis_dokumen'] ?? '20');
        if (strlen($nomorAju) >= 6 && in_array(substr($nomorAju, 4, 2), ['20', '30', '23', '25', '27', '40'], true)) {
            $jenisDokumen = substr($nomorAju, 4, 2);
        }

        $result = DB::transaction(function () use ($nomorAju, $jenisDokumen, $parsed) {
            $doc = CeisaDocument::where('nomor_aju', $nomorAju)
                ->orWhereRaw("REPLACE(nomor_aju, '-', '') = ?", [$nomorAju])
                ->first();

            $valuta = strtoupper((string) ($parsed['kode_valuta'] ?? 'USD'));
            $kurs = (float) ($parsed['ndbm_kurs'] ?? 0);
            if ($kurs <= 0) {
                $kurs = (float) (CeisaExchangeRate::where('kode_valuta', $valuta)->latest('valid_from')->value('nilai_kurs') ?? 16000);
            }
            $totalNilai = (float) ($parsed['total_nilai'] ?? 0);
            $totalNilaiIdr = (float) ($parsed['total_nilai_idr'] ?? ($totalNilai * $kurs));

            $items = $parsed['items'] ?? [];
            $kodeKantor = $parsed['kode_kantor'] ?? (strlen($nomorAju) >= 4 ? substr($nomorAju, 0, 4) . '00' : config('ceisa.company.default_kode_kantor', '040300'));

            $docAttributes = array_filter([
                'nomor_aju' => $nomorAju,
                'jenis_dokumen' => $jenisDokumen,
                'nomor_daftar' => $parsed['nomor_daftar'] ?? null,
                'tanggal_daftar' => $parsed['tanggal_daftar'] ?? null,
                'kode_kantor' => $kodeKantor,
                'nama_kantor' => config("ceisa.kantor_pabean.{$kodeKantor}", 'Kantor Pelayanan Bea Cukai'),
                'kode_valuta' => $valuta,
                'ndbm_kurs' => $kurs,
                'total_nilai' => $totalNilai,
                'total_nilai_idr' => $totalNilaiIdr,
                'total_bruto' => (float) ($parsed['total_bruto'] ?? 0),
                'total_netto' => (float) ($parsed['total_netto'] ?? 0),
                'total_kemasan' => (int) ($parsed['total_kemasan'] ?? 0),
                'total_kontainer' => (int) ($parsed['total_kontainer'] ?? 0),
                'total_barang' => count($items) > 0 ? count($items) : (int) ($parsed['total_barang'] ?? 0),
                'nama_perusahaan_lokal' => $parsed['nama_perusahaan_lokal'] ?? config('ceisa.company.name'),
                'npwp_perusahaan_lokal' => $parsed['npwp_perusahaan_lokal'] ?? config('ceisa.company.npwp'),
                'nama_mitra_luar_negeri' => $parsed['nama_mitra_luar_negeri'] ?? null,
                'negara_mitra_luar_negeri' => $parsed['negara_mitra_luar_negeri'] ?? null,
                'nomor_bc11' => $parsed['nomor_bc11'] ?? null,
                'tanggal_bc11' => $parsed['tanggal_bc11'] ?? null,
                'pos_bc11' => $parsed['pos_bc11'] ?? null,
                'subpos_bc11' => $parsed['subpos_bc11'] ?? null,
                'kode_tutup_pu' => $parsed['kode_tutup_pu'] ?? '11',
                'kode_tps' => $parsed['kode_tps'] ?? null,
                'raw_payload' => $parsed['raw_payload'] ?? $parsed,
            ], fn ($v) => $v !== null);

            if ($doc) {
                $doc->update($docAttributes);
            } else {
                $docAttributes['status'] = 'SUBMITTED';
                $docAttributes['status_description'] = 'Diimpor dari file ekspor Portal CEISA 4.0';
                $doc = CeisaDocument::create($docAttributes);
            }

            // Hubungkan / buat Client berdasarkan Importir/Eksportir asli di dokumen
            $clientName = trim((string) ($parsed['nama_perusahaan_lokal'] ?? ''));
            if ($clientName === '') {
                $clientName = config('ceisa.company.name', 'Klien CEISA');
            }
            $clientNpwp = preg_replace('/[^0-9]/', '', (string) ($parsed['npwp_perusahaan_lokal'] ?? ''));
            $client = ($clientNpwp !== '')
                ? Client::where('npwp', $clientNpwp)->first()
                : null;
            if (!$client) {
                $client = Client::firstOrCreate(
                    ['name' => $clientName],
                    [
                        'code' => 'CLI-' . strtoupper(substr(md5($clientName), 0, 8)),
                        'npwp' => $clientNpwp !== '' ? $clientNpwp : '0000000000000000',
                        'email' => 'client_' . strtolower(substr(md5($clientName), 0, 6)) . '@genie.test',
                        'address' => $parsed['alamat_perusahaan_lokal'] ?? 'Indonesia',
                    ]
                );
            }

            $shipment = $doc->shipment ?? Shipment::where('ceisa_document_id', $doc->id)->first();
            $kontainerStr = !empty($parsed['nomor_kontainer'])
                ? (is_array($parsed['nomor_kontainer']) ? implode(', ', $parsed['nomor_kontainer']) : (string) $parsed['nomor_kontainer'])
                : null;

            $shipmentData = [
                'client_id' => $client->id,
                'jenis_layanan' => ($jenisDokumen === '30') ? 'EKSPORT_BC30' : 'IMPORT_BC20',
                'nomor_bl_awb' => $parsed['nomor_bl_awb'] ?? ($shipment?->nomor_bl_awb ?? ('BL-' . strtoupper(substr(md5($nomorAju), 0, 8)))),
                'tgl_bl_awb' => $parsed['tgl_bl_awb'] ?? ($shipment?->tgl_bl_awb ?? now()->toDateString()),
                'nama_kapal' => $parsed['nama_kapal'] ?? $shipment?->nama_kapal,
                'voyage_no' => $parsed['voyage_no'] ?? $shipment?->voyage_no,
                'pelabuhan_muat' => $parsed['pelabuhan_muat'] ?? ($shipment?->pelabuhan_muat ?? 'PORT_ORIGIN'),
                'pelabuhan_bongkar' => $parsed['pelabuhan_bongkar'] ?? ($shipment?->pelabuhan_bongkar ?? 'IDTPP'),
                'total_kontainer' => max(0, (int) ($parsed['total_kontainer'] ?? ($shipment?->total_kontainer ?? 0))),
                'nomor_kontainer' => $kontainerStr ?? $shipment?->nomor_kontainer,
                'total_kemasan' => max(0, (int) ($parsed['total_kemasan'] ?? ($shipment?->total_kemasan ?? 0))),
                'jenis_kemasan' => $parsed['jenis_kemasan'] ?? ($shipment?->jenis_kemasan ?? 'PKG'),
                'total_bruto' => (float) ($parsed['total_bruto'] ?? ($shipment?->total_bruto ?? 0)),
                'total_netto' => (float) ($parsed['total_netto'] ?? ($shipment?->total_netto ?? 0)),
                'kode_valuta' => $valuta,
                'total_nilai_valuta' => $totalNilai,
                'ndbm_kurs' => $kurs,
                'total_nilai_idr' => $totalNilaiIdr,
                'ceisa_document_id' => $doc->id,
            ];

            if ($shipment) {
                $shipment->update($shipmentData);
            } else {
                $prefix = ($jenisDokumen === '30') ? 'ATS-EXP-' : 'ATS-IMP-';
                $shipmentData['project_no'] = $prefix . date('Ym') . '-' . str_pad(Shipment::count() + 1, 4, '0', STR_PAD_LEFT);
                $shipmentData['eta_date'] = now()->addDays(2);
                $shipmentData['status_tahap'] = 'CEISA_SUBMITTED';
                $shipmentData['catatan'] = "Impor file CEISA 4.0 (Aju: {$nomorAju})";
                $shipmentData['created_by_user_id'] = auth()->id();
                $shipment = Shipment::create($shipmentData);
            }

            // Simpan/perbarui seluruh item barang jika tersedia di file
            $itemsImported = 0;
            if (!empty($items)) {
                ShipmentItem::where('shipment_id', $shipment->id)->delete();
                foreach ($items as $idx => $item) {
                    ShipmentItem::create([
                        'shipment_id' => $shipment->id,
                        'nomor_urut' => (int) ($item['nomor_urut'] ?? ($idx + 1)),
                        'uraian_barang' => mb_substr((string) ($item['uraian_barang'] ?? 'Barang Pabean'), 0, 1000),
                        'kode_hs' => $this->formatHsCode((string) ($item['kode_hs'] ?? '')),
                        'negara_asal' => $item['negara_asal'] ?? ($parsed['negara_mitra_luar_negeri'] ?? null),
                        'jumlah_kemasan' => (int) ($item['jumlah_kemasan'] ?? 0),
                        'jenis_kemasan' => $item['jenis_kemasan'] ?? ($parsed['jenis_kemasan'] ?? 'PKG'),
                        'berat_netto' => (float) ($item['berat_netto'] ?? 0),
                        'berat_bruto' => (float) ($item['berat_bruto'] ?? 0),
                        'nilai_fob' => (float) ($item['nilai_fob'] ?? 0),
                        'tarif_bm_persen' => (float) ($item['tarif_bm_persen'] ?? 0),
                        'tarif_ppn_persen' => (float) ($item['tarif_ppn_persen'] ?? 11),
                        'tarif_pph_persen' => (float) ($item['tarif_pph_persen'] ?? 2.5),
                    ]);
                    $itemsImported++;
                }
            }

            return [
                'document' => $doc->fresh(),
                'shipment' => $shipment->fresh(),
                'items_imported' => $itemsImported,
            ];
        });

        // Gabungkan status & respon real-time dari API CEISA (/status/{nomorAju})
        $statusMerged = false;
        if ($syncStatusAfter) {
            try {
                $syncRes = $this->syncService->syncByNomorAju($nomorAju, false);
                $statusMerged = (bool) ($syncRes['success'] ?? false);
            } catch (\Throwable $e) {
                Log::warning("Import CEISA berhasil, namun sinkronisasi status API gagal untuk {$nomorAju}: {$e->getMessage()}");
            }
        }

        return [
            'success' => true,
            'nomor_aju' => $nomorAju,
            'document' => $result['document']->fresh(),
            'shipment' => $result['shipment']->fresh(),
            'items_imported' => $result['items_imported'],
            'status_merged' => $statusMerged,
            'message' => "Dokumen CEISA {$nomorAju} berhasil diimpor ({$result['items_imported']} item barang" . ($statusMerged ? ', status real-time tersinkron' : '') . ').',
        ];
    }

    /**
     * Parse format JSON resmi CEISA 4.0 OpenAPI / Portal JSON Export
     */
    protected function parseJsonFile(string $path): array
    {
        $raw = json_decode((string) file_get_contents($path), true);
        if (!is_array($raw)) {
            throw new \RuntimeException('Struktur JSON tidak valid.');
        }

        // Beberapa export membungkus payload di key "data" atau "header"
        $data = (isset($raw['data']) && is_array($raw['data'])) ? $raw['data'] : $raw;

        $namaPerusahaan = null;
        $npwpPerusahaan = null;
        $alamatPerusahaan = null;
        $namaMitra = null;
        $negaraMitra = null;

        foreach (($data['entitas'] ?? []) as $ent) {
            $kodeEntitas = (string) ($ent['kodeEntitas'] ?? '');
            // 1=Importir, 2=Eksportir, 7=Pemilik Barang
            if (in_array($kodeEntitas, ['1', '2', '7'], true) && !$namaPerusahaan) {
                $namaPerusahaan = $ent['namaEntitas'] ?? null;
                $npwpPerusahaan = $ent['nomorIdentitas'] ?? null;
                $alamatPerusahaan = $ent['alamatEntitas'] ?? null;
            }
            // 5=Pemasok, 9=Pengirim, 6=Pembeli
            if (in_array($kodeEntitas, ['5', '9', '6', '10'], true) && !$namaMitra) {
                $namaMitra = $ent['namaEntitas'] ?? null;
                $negaraMitra = $ent['kodeNegara'] ?? null;
            }
        }

        $nomorBl = null;
        $tglBl = null;
        foreach (($data['dokumen'] ?? []) as $dok) {
            $kd = (string) ($dok['kodeDokumen'] ?? '');
            // 705 = B/L, 740/741 = AWB
            if (in_array($kd, ['705', '740', '741'], true)) {
                $nomorBl = $dok['nomorDokumen'] ?? null;
                $tglBl = $this->normalizeDate($dok['tanggalDokumen'] ?? null);
                break;
            }
        }

        $totalKemasan = 0;
        $jenisKemasan = null;
        foreach (($data['kemasan'] ?? []) as $kem) {
            $totalKemasan += (int) ($kem['jumlahKemasan'] ?? 0);
            $jenisKemasan = $jenisKemasan ?? ($kem['kodeJenisKemasan'] ?? null);
        }

        $kontainerList = [];
        foreach (($data['kontainer'] ?? []) as $kon) {
            if (!empty($kon['nomorKontainer'])) {
                $kontainerList[] = trim((string) $kon['nomorKontainer']);
            }
        }

        $namaKapal = null;
        $voyageNo = null;
        if (!empty($data['pengangkut'][0])) {
            $namaKapal = $data['pengangkut'][0]['namaPengangkut'] ?? null;
            $voyageNo = $data['pengangkut'][0]['nomorPengangkut'] ?? null;
        }

        $items = [];
        foreach (($data['barang'] ?? []) as $idx => $brg) {
            $tarifBm = 0.0;
            $tarifPpn = 11.0;
            $tarifPph = 2.5;
            foreach (($brg['barangTarif'] ?? []) as $bt) {
                $jenisTarif = strtoupper((string) ($bt['kodeJenisTarif'] ?? $bt['kodeJenisPungutan'] ?? ''));
                if ($jenisTarif === 'BM' || $jenisTarif === '1') {
                    $tarifBm = (float) ($bt['tarif'] ?? 0);
                } elseif ($jenisTarif === 'PPN' || $jenisTarif === '2') {
                    $tarifPpn = (float) ($bt['tarif'] ?? 11);
                } elseif ($jenisTarif === 'PPH' || $jenisTarif === '3') {
                    $tarifPph = (float) ($bt['tarif'] ?? 2.5);
                }
            }

            $items[] = [
                'nomor_urut' => (int) ($brg['seriBarang'] ?? ($idx + 1)),
                'uraian_barang' => $brg['uraian'] ?? ($brg['merk'] ?? 'Barang'),
                'kode_hs' => $brg['posTarif'] ?? '',
                'negara_asal' => $brg['kodeNegaraAsal'] ?? $negaraMitra,
                'jumlah_kemasan' => (int) ($brg['jumlahKemasan'] ?? $brg['jumlahSatuan'] ?? 0),
                'jenis_kemasan' => $brg['kodeJenisKemasan'] ?? ($brg['kodeSatuanBarang'] ?? 'PKG'),
                'berat_netto' => (float) ($brg['netto'] ?? 0),
                'berat_bruto' => (float) ($brg['bruto'] ?? 0),
                'nilai_fob' => (float) ($brg['cif'] ?? $brg['fob'] ?? $brg['nilaiBarang'] ?? 0),
                'tarif_bm_persen' => $tarifBm,
                'tarif_ppn_persen' => $tarifPpn,
                'tarif_pph_persen' => $tarifPph,
            ];
        }

        return [
            'nomor_aju' => $data['nomorAju'] ?? null,
            'jenis_dokumen' => $data['kodeDokumen'] ?? null,
            'nomor_daftar' => $data['nomorDaftar'] ?? null,
            'tanggal_daftar' => $this->normalizeDate($data['tanggalDaftar'] ?? null),
            'kode_kantor' => $data['kodeKantor'] ?? null,
            'kode_valuta' => $data['kodeValuta'] ?? 'USD',
            'ndbm_kurs' => $data['ndpbm'] ?? ($data['ndbm'] ?? null),
            'total_nilai' => $data['cif'] ?? ($data['fob'] ?? ($data['nilaiBarang'] ?? 0)),
            'total_nilai_idr' => $data['cifRupiah'] ?? null,
            'total_bruto' => $data['bruto'] ?? 0,
            'total_netto' => $data['netto'] ?? 0,
            'total_kemasan' => $totalKemasan,
            'jenis_kemasan' => $jenisKemasan,
            'total_kontainer' => count($kontainerList),
            'nomor_kontainer' => $kontainerList,
            'nama_perusahaan_lokal' => $namaPerusahaan,
            'npwp_perusahaan_lokal' => $npwpPerusahaan,
            'alamat_perusahaan_lokal' => $alamatPerusahaan,
            'nama_mitra_luar_negeri' => $namaMitra,
            'negara_mitra_luar_negeri' => $negaraMitra,
            'nomor_bl_awb' => $nomorBl,
            'tgl_bl_awb' => $tglBl,
            'nama_kapal' => $namaKapal,
            'voyage_no' => $voyageNo,
            'pelabuhan_muat' => $data['kodePelMuat'] ?? ($data['pelabuhanMuat'] ?? null),
            'pelabuhan_bongkar' => $data['kodePelTujuan'] ?? ($data['kodePelBongkar'] ?? null),
            'nomor_bc11' => $data['nomorBc11'] ?? null,
            'tanggal_bc11' => $this->normalizeDate($data['tanggalBc11'] ?? null),
            'pos_bc11' => $data['posBc11'] ?? null,
            'subpos_bc11' => $data['subposBc11'] ?? null,
            'kode_tutup_pu' => $data['kodeTutupPu'] ?? '11',
            'kode_tps' => $data['kodeTps'] ?? null,
            'items' => $items,
            'raw_payload' => $data,
        ];
    }

    /**
     * Parse format Excel Export Portal CEISA 4.0 (multi-sheet: HEADER, ENTITAS, DOKUMEN, KEMASAN, KONTAINER, BARANG, BARANGTARIF)
     */
    protected function parseExcelFile(string $path): array
    {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);

        $sheets = [];
        foreach ($spreadsheet->getSheetNames() as $name) {
            $key = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $name));
            $sheets[$key] = $this->sheetToAssoc($spreadsheet->getSheetByName($name)->toArray(null, false, false, false));
        }

        $header = $sheets['HEADER'][0] ?? [];
        $result = [
            'nomor_aju' => $this->pick($header, ['nomor_aju', 'nomoraju', 'no_aju']),
            'jenis_dokumen' => $this->pick($header, ['kode_dokumen', 'kodedokumen', 'jenis_dokumen']),
            'nomor_daftar' => $this->pick($header, ['nomor_daftar', 'nomordaftar', 'nopen']),
            'tanggal_daftar' => $this->normalizeDate($this->pick($header, ['tanggal_daftar', 'tanggaldaftar'])),
            'kode_kantor' => $this->pick($header, ['kode_kantor', 'kodekantor']),
            'kode_valuta' => $this->pick($header, ['kode_valuta', 'kodevaluta', 'valuta']) ?? 'USD',
            'ndbm_kurs' => $this->toFloat($this->pick($header, ['ndpbm', 'ndbm', 'kurs'])),
            'total_nilai' => $this->toFloat($this->pick($header, ['cif', 'fob', 'nilai_barang', 'total_nilai'])),
            'total_nilai_idr' => $this->toFloat($this->pick($header, ['cif_rupiah', 'cifrupiah', 'nilai_idr'])),
            'total_bruto' => $this->toFloat($this->pick($header, ['bruto', 'berat_kotor', 'total_bruto'])),
            'total_netto' => $this->toFloat($this->pick($header, ['netto', 'berat_bersih', 'total_netto'])),
            'pelabuhan_muat' => $this->pick($header, ['kode_pel_muat', 'kodepelmuat', 'pelabuhan_muat']),
            'pelabuhan_bongkar' => $this->pick($header, ['kode_pel_tujuan', 'kodepeltujuan', 'kode_pel_bongkar', 'pelabuhan_bongkar']),
            'nomor_bc11' => $this->pick($header, ['nomor_bc11', 'nomorbc11']),
            'tanggal_bc11' => $this->normalizeDate($this->pick($header, ['tanggal_bc11', 'tanggalbc11'])),
            'pos_bc11' => $this->pick($header, ['pos_bc11', 'posbc11']),
            'subpos_bc11' => $this->pick($header, ['subpos_bc11', 'subposbc11']),
            'kode_tps' => $this->pick($header, ['kode_tps', 'kodetps']),
            'kode_tutup_pu' => $this->pick($header, ['kode_tutup_pu', 'kodetutuppu']) ?? '11',
            'items' => [],
        ];

        // ENTITAS
        foreach (($sheets['ENTITAS'] ?? []) as $ent) {
            $kode = (string) ($this->pick($ent, ['kode_entitas', 'kodeentitas']) ?? '');
            if (in_array($kode, ['1', '2', '7'], true) && empty($result['nama_perusahaan_lokal'])) {
                $result['nama_perusahaan_lokal'] = $this->pick($ent, ['nama_entitas', 'namaentitas']);
                $result['npwp_perusahaan_lokal'] = $this->pick($ent, ['nomor_identitas', 'nomoridentitas', 'npwp']);
                $result['alamat_perusahaan_lokal'] = $this->pick($ent, ['alamat_entitas', 'alamatentitas']);
            }
            if (in_array($kode, ['5', '9', '6', '10'], true) && empty($result['nama_mitra_luar_negeri'])) {
                $result['nama_mitra_luar_negeri'] = $this->pick($ent, ['nama_entitas', 'namaentitas']);
                $result['negara_mitra_luar_negeri'] = $this->pick($ent, ['kode_negara', 'kodenegara']);
            }
        }

        // DOKUMEN (BL/AWB)
        foreach (($sheets['DOKUMEN'] ?? []) as $dok) {
            $kode = (string) ($this->pick($dok, ['kode_dokumen', 'kodedokumen']) ?? '');
            if (in_array($kode, ['705', '740', '741'], true)) {
                $result['nomor_bl_awb'] = $this->pick($dok, ['nomor_dokumen', 'nomordokumen']);
                $result['tgl_bl_awb'] = $this->normalizeDate($this->pick($dok, ['tanggal_dokumen', 'tanggaldokumen']));
                break;
            }
        }

        // PENGANGKUT
        if (!empty($sheets['PENGANGKUT'][0])) {
            $result['nama_kapal'] = $this->pick($sheets['PENGANGKUT'][0], ['nama_pengangkut', 'namapengangkut']);
            $result['voyage_no'] = $this->pick($sheets['PENGANGKUT'][0], ['nomor_pengangkut', 'nomorpengangkut']);
        }

        // KEMASAN
        $totalKemasan = 0;
        $jenisKemasan = null;
        foreach (($sheets['KEMASAN'] ?? []) as $kem) {
            $totalKemasan += (int) $this->toFloat($this->pick($kem, ['jumlah_kemasan', 'jumlahkemasan']));
            $jenisKemasan = $jenisKemasan ?? $this->pick($kem, ['kode_jenis_kemasan', 'kodejeniskemasan', 'kode_kemasan']);
        }
        $result['total_kemasan'] = $totalKemasan;
        $result['jenis_kemasan'] = $jenisKemasan;

        // KONTAINER
        $kontainer = [];
        foreach (($sheets['KONTAINER'] ?? []) as $kon) {
            $noKon = trim((string) ($this->pick($kon, ['nomor_kontainer', 'nomorkontainer', 'no_kontainer']) ?? ''));
            if ($noKon !== '') {
                $kontainer[] = $noKon;
            }
        }
        $result['nomor_kontainer'] = $kontainer;
        $result['total_kontainer'] = count($kontainer);

        // BARANGTARIF indexed by seri_barang
        $tarifBySeri = [];
        foreach (($sheets['BARANGTARIF'] ?? []) as $bt) {
            $seri = (string) ($this->pick($bt, ['seri_barang', 'seribarang']) ?? '');
            $jenis = strtoupper((string) ($this->pick($bt, ['kode_jenis_tarif', 'kodejenistarif', 'kode_pungutan', 'jenis_tarif']) ?? ''));
            $tarif = $this->toFloat($this->pick($bt, ['tarif', 'tarif_fasilitas']));
            if ($seri !== '' && $jenis !== '') {
                $tarifBySeri[$seri][$jenis] = $tarif;
            }
        }

        // BARANG
        foreach (($sheets['BARANG'] ?? []) as $idx => $brg) {
            $seri = (string) ($this->pick($brg, ['seri_barang', 'seribarang']) ?? ($idx + 1));
            $hs = (string) ($this->pick($brg, ['pos_tarif', 'postarif', 'hs_code', 'kode_hs']) ?? '');
            $uraian = (string) ($this->pick($brg, ['uraian', 'uraian_barang']) ?? '');
            if ($hs === '' && $uraian === '') {
                continue;
            }
            $result['items'][] = [
                'nomor_urut' => (int) $seri,
                'uraian_barang' => $uraian !== '' ? $uraian : "Barang Seri {$seri}",
                'kode_hs' => $hs,
                'negara_asal' => $this->pick($brg, ['kode_negara_asal', 'kodenegaraasal', 'negara_asal']),
                'jumlah_kemasan' => (int) $this->toFloat($this->pick($brg, ['jumlah_kemasan', 'jumlahkemasan', 'jumlah_satuan'])),
                'jenis_kemasan' => $this->pick($brg, ['kode_jenis_kemasan', 'kodejeniskemasan', 'kode_satuan']) ?? ($jenisKemasan ?? 'PKG'),
                'berat_netto' => $this->toFloat($this->pick($brg, ['netto', 'berat_netto'])),
                'berat_bruto' => $this->toFloat($this->pick($brg, ['bruto', 'berat_bruto'])),
                'nilai_fob' => $this->toFloat($this->pick($brg, ['cif', 'fob', 'nilai_barang', 'harga_penyerahan'])),
                'tarif_bm_persen' => $tarifBySeri[$seri]['BM'] ?? 0.0,
                'tarif_ppn_persen' => $tarifBySeri[$seri]['PPN'] ?? 11.0,
                'tarif_pph_persen' => $tarifBySeri[$seri]['PPH'] ?? 2.5,
            ];
        }

        $result['raw_payload'] = $sheets;
        return $result;
    }

    /**
     * Parse PDF Cetak Formulir PIB (BC 2.0) / PEB (BC 3.0) Portal CEISA 4.0
     */
    protected function parsePdfFile(string $path): array
    {
        $parser = new PdfParser();
        $pdf = $parser->parseFile($path);
        $text = $pdf->getText();

        $nomorAju = null;
        if (preg_match('/\b(\d{6}[A-Za-z0-9]{6}\d{14}|\d{6}-\d{6}-\d{8}-\d{6})\b/', $text, $m)) {
            $nomorAju = str_replace('-', '', $m[1]);
        }

        $npwp = null;
        if (preg_match('/\b(\d{2}\.\d{3}\.\d{3}\.\d-\d{3}\.\d{3}|\d{15,16})\b/', $text, $m)) {
            $npwp = preg_replace('/[^0-9]/', '', $m[1]);
        }

        $namaPerusahaan = null;
        if (preg_match('/(?:Importir|Eksportir|Nama)\s*[:\-]\s*(PT\.?\s*[A-Z0-9\s\.,\-]{3,60})/i', $text, $m)) {
            $namaPerusahaan = trim(preg_replace('/\s+/', ' ', $m[1]));
        }

        $kontainer = [];
        if (preg_match_all('/\b([A-Z]{4}\d{7})\b/', $text, $mc)) {
            $kontainer = array_values(array_unique($mc[1]));
        }

        $items = [];
        if (preg_match_all('/\b(\d{4}\.\d{2}\.\d{2})\b\s*([^\n]{5,120})/', $text, $mh, PREG_SET_ORDER)) {
            foreach ($mh as $idx => $match) {
                $items[] = [
                    'nomor_urut' => $idx + 1,
                    'kode_hs' => $match[1],
                    'uraian_barang' => trim($match[2]),
                ];
            }
        }

        return [
            'nomor_aju' => $nomorAju,
            'nama_perusahaan_lokal' => $namaPerusahaan,
            'npwp_perusahaan_lokal' => $npwp,
            'nomor_kontainer' => $kontainer,
            'total_kontainer' => count($kontainer),
            'items' => $items,
            'raw_payload' => ['pdf_text_excerpt' => mb_substr($text, 0, 5000)],
        ];
    }

    protected function sheetToAssoc(array $rows): array
    {
        if (count($rows) < 2) {
            return [];
        }
        $headers = array_map(function ($h) {
            return strtolower(trim((string) preg_replace('/[^a-zA-Z0-9]+/', '_', (string) $h), '_'));
        }, $rows[0]);

        $out = [];
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (!array_filter($row, fn ($v) => $v !== null && trim((string) $v) !== '')) {
                continue;
            }
            $assoc = [];
            foreach ($headers as $colIdx => $key) {
                if ($key !== '') {
                    $assoc[$key] = $row[$colIdx] ?? null;
                }
            }
            $out[] = $assoc;
        }
        return $out;
    }

    protected function pick(array $row, array $keys): mixed
    {
        foreach ($keys as $k) {
            if (array_key_exists($k, $row) && $row[$k] !== null && trim((string) $row[$k]) !== '') {
                return trim((string) $row[$k]);
            }
        }
        return null;
    }

    protected function toFloat(mixed $val): float
    {
        if ($val === null || $val === '') {
            return 0.0;
        }
        if (is_numeric($val)) {
            return (float) $val;
        }
        $clean = str_replace([',', ' '], '', (string) $val);
        return is_numeric($clean) ? (float) $clean : 0.0;
    }

    protected function normalizeDate(mixed $value): ?string
    {
        if (!$value || trim((string) $value) === '') {
            return null;
        }
        $trimmed = trim((string) $value);
        try {
            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $trimmed)) {
                return Carbon::createFromFormat('d-m-Y', $trimmed)->format('Y-m-d');
            }
            return Carbon::parse($trimmed)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    protected function formatHsCode(string $raw): string
    {
        $digits = preg_replace('/[^0-9]/', '', $raw);
        if (strlen($digits) === 8) {
            return substr($digits, 0, 4) . '.' . substr($digits, 4, 2) . '.' . substr($digits, 6, 2);
        }
        return trim($raw) !== '' ? trim($raw) : '0000.00.00';
    }
}
