<?php

namespace App\Services\Ceisa;

use App\Models\CeisaDocument;

class DocumentBC20Service
{
    /**
     * Generate Nomor Aju 26 digit untuk BC 2.0
     * Pola: [4 digit kantor] [2 digit tipe doc: 20] [6 digit id perusahaan] [8 digit YYYYMMDD] [6 digit sequence]
     */
    public static function generateNomorAju(?string $kodeKantor = null): string
    {
        $kantor = $kodeKantor ?? config('ceisa.company.default_kode_kantor', '040300');
        $kantor4 = substr($kantor, 0, 4);
        $jenisDoc = '20';
        $idPerusahaan = str_pad(substr(config('ceisa.company.id_perusahaan', '012345'), 0, 6), 6, '0', STR_PAD_RIGHT);
        $today = date('Ymd');

        $existingAju = CeisaDocument::withTrashed()
            ->where('nomor_aju', 'like', "{$kantor4}{$jenisDoc}{$idPerusahaan}{$today}%")
            ->pluck('nomor_aju')
            ->map(fn($num) => (int) substr($num, -6))
            ->toArray();

        $maxSeq = !empty($existingAju) ? max($existingAju) : 0;
        $seq = $maxSeq + 1;
        $seqFormatted = str_pad((string) $seq, 6, '0', STR_PAD_LEFT);

        return "{$kantor4}{$jenisDoc}{$idPerusahaan}{$today}{$seqFormatted}";
    }

    /**
     * Susun Payload JSON standar resmi CEISA 4.0 untuk BC 2.0 (PIB v.0.5.7.23)
     * Struktur: Flat Root Object sesuai JSON Schema Draft-07 DJBC
     */
    public static function buildPayload(array $data): array
    {
        $nomorAju = $data['nomor_aju'] ?? self::generateNomorAju($data['kode_kantor'] ?? null);
        $kodeKantor = $data['kode_kantor'] ?? config('ceisa.company.default_kode_kantor', '040300');
        $kurs = (float) ($data['ndpbm'] ?? ($data['ndbm_kurs'] ?? 15850.00));
        $fob = (float) ($data['fob'] ?? 0);
        $freight = (float) ($data['freight'] ?? 0);
        $asuransi = (float) ($data['asuransi'] ?? 0);
        $cif = (float) ($data['cif'] ?? ($fob + $freight + $asuransi));
        $cifRupiah = round($cif * $kurs);
        $kodeValuta = $data['kode_valuta'] ?? 'USD';

        // Entitas Data
        $importirNpwp = preg_replace('/[^0-9]/', '', $data['importir_npwp'] ?? config('ceisa.company.npwp', '012345678012000'));
        $importirNama = $data['importir_nama'] ?? config('ceisa.company.name', 'PT AGUNG TRANS SOLUSINDO');
        $importirAlamat = $data['importir_alamat'] ?? config('ceisa.company.address', 'Jl. Pelabuhan Nusantara II No. 12, Tanjung Priok, Jakarta Utara');
        $importirNib = $data['importir_nib'] ?? config('ceisa.company.nib', '9120009876543');

        $supplierNama = $data['supplier_nama'] ?? 'OVERSEAS EXPORT CO., LTD.';
        $supplierAlamat = $data['supplier_alamat'] ?? '88 Industrial Park, Harbor City';
        $supplierNegara = $data['supplier_negara'] ?? 'CN';

        // 1. Array Barang & Kalkulasi Pungutan BM/PDRI
        $rawBarang = $data['barang'] ?? [];
        $formattedBarang = [];
        $totalBeaMasuk = 0;
        $totalPpn = 0;
        $totalPph = 0;

        foreach ($rawBarang as $index => $item) {
            $seri = $index + 1;
            $itemCif = (float) ($item['cif'] ?? ($cif / max(1, count($rawBarang))));
            $itemCifRupiah = round($itemCif * $kurs);
            $itemJumlah = (float) ($item['jumlah'] ?? ($item['jumlah_satuan'] ?? ($item['jumlahSatuan'] ?? 1)));
            $itemNetto = (float) ($item['netto'] ?? 1);
            $itemBruto = (float) ($item['bruto'] ?? $itemNetto);
            $itemVolume = (float) ($item['volume'] ?? 0);
            $satuan = $item['satuan'] ?? ($item['kode_satuan'] ?? ($item['kodeSatuanBarang'] ?? 'C62'));
            $jenisKemasan = $item['jenis_kemasan'] ?? ($item['kode_jenis_kemasan'] ?? ($item['kodeJenisKemasan'] ?? ($data['kode_kemasan'] ?? 'CT')));

            // Tarif Bea Masuk & PDRI
            $tarifBm = (float) ($item['tarif_bm'] ?? ($item['tarifBm'] ?? 0));
            $tarifPpn = (float) ($item['tarif_ppn'] ?? ($item['tarifPpn'] ?? 11));
            $tarifPph = (float) ($item['tarif_pph'] ?? ($item['tarifPph'] ?? 2.5));

            $nilaiBm = round(($tarifBm / 100) * $itemCifRupiah);
            $nilaiPpn = round(($tarifPpn / 100) * ($itemCifRupiah + $nilaiBm));
            $nilaiPph = round(($tarifPph / 100) * ($itemCifRupiah + $nilaiBm));

            $totalBeaMasuk += $nilaiBm;
            $totalPpn += $nilaiPpn;
            $totalPph += $nilaiPph;

            $merk = $item['merk'] ?? ($item['merek'] ?? 'STANDARD');
            if (strlen($merk) < 2) {
                $merk = 'STANDARD';
            }

            $tipe = $item['tipe'] ?? 'REGULAR';
            if (strlen($tipe) < 2) {
                $tipe = 'REGULAR';
            }

            $posTarif = preg_replace('/[^0-9]/', '', (string) ($item['hs_code'] ?? ($item['pos_tarif'] ?? ($item['posTarif'] ?? ($item['kode_hs'] ?? '84713020')))));

            $formattedBarang[] = [
                'seriBarang' => $seri,
                'kodeBarang' => $item['kode_barang'] ?? ('BRG-' . str_pad((string) $seri, 3, '0', STR_PAD_LEFT)),
                'posTarif' => $posTarif,
                'uraian' => $item['uraian'] ?? ($item['uraian_barang'] ?? 'Barang Impor'),
                'merk' => $merk,
                'tipe' => $tipe,
                'spesifikasiLain' => $item['spesifikasi'] ?? '-',
                'kodeKondisiBarang' => $item['kode_kondisi'] ?? '1', // 1: Baik, 2: Baru
                'kodeNegaraAsal' => $item['negara_asal'] ?? ($item['kodeNegaraAsal'] ?? $supplierNegara),
                'kodeSatuanBarang' => $satuan,
                'jumlahSatuan' => $itemJumlah,
                'jumlahKemasan' => (float) ($item['jumlah_kemasan'] ?? ($item['jumlahKemasan'] ?? $itemJumlah)),
                'kodeJenisKemasan' => $jenisKemasan,
                'netto' => $itemNetto,
                'bruto' => $itemBruto,
                'volume' => $itemVolume,
                'cif' => $itemCif,
                'cifRupiah' => $itemCifRupiah,
                'fob' => (float) ($item['fob'] ?? $itemCif),
                'freight' => (float) ($item['freight'] ?? 0),
                'asuransi' => (float) ($item['asuransi'] ?? 0),
                'hargaSatuan' => round($itemCif / max(1, $itemJumlah), 4),
                'hargaPenyerahan' => $itemCifRupiah,
                'hargaPerolehan' => $itemCifRupiah,
                'hargaPatokan' => 0.0,
                'hargaEkspor' => 0.0,
                'diskon' => 0.0,
                'ndpbm' => $kurs,
                'nilaiBarang' => $itemCif,
                'nilaiDevisa' => $itemCif,
                'nilaiDanaSawit' => 0.0,
                'nilaiTambah' => 0.0,
                'saldoAwal' => $itemJumlah,
                'saldoAkhir' => $itemJumlah,
                'pernyataanLartas' => 'T',
                'persentaseImpor' => 100,
                'metodePenentuanNilai' => $item['metode_nilai'] ?? 'Metode 1',
                'alasanMetodePenentuanNilai' => null,
                'statementPerbedaanHarga' => 'T',
                'barangVd' => [],
                'barangSpekKhusus' => [],
                'barangDokumen' => $item['barangDokumen'] ?? ($item['barang_dokumen'] ?? []),
                'barangTarif' => [
                    [
                        'kodeJenisPungutan' => 'BM',
                        'kodeJenisTarif' => '1',      // 1: Advalorum
                        'kodeFasilitasTarif' => '1',  // 1: Dibayar
                        'tarifFasilitas' => 0.0,
                        'tarif' => $tarifBm,
                        'nilaiBayar' => $nilaiBm,
                        'nilaiFasilitas' => 0.0,
                        'jumlahSatuan' => $itemJumlah,
                        'seriBarang' => $seri,
                    ],
                    [
                        'kodeJenisPungutan' => 'PPN',
                        'kodeJenisTarif' => '1',
                        'kodeFasilitasTarif' => '1',
                        'tarifFasilitas' => 0.0,
                        'tarif' => $tarifPpn,
                        'nilaiBayar' => $nilaiPpn,
                        'nilaiFasilitas' => 0.0,
                        'jumlahSatuan' => $itemJumlah,
                        'seriBarang' => $seri,
                    ],
                    [
                        'kodeJenisPungutan' => 'PPH',
                        'kodeJenisTarif' => '1',
                        'kodeFasilitasTarif' => '1',
                        'tarifFasilitas' => 0.0,
                        'tarif' => $tarifPph,
                        'nilaiBayar' => $nilaiPph,
                        'nilaiFasilitas' => 0.0,
                        'jumlahSatuan' => $itemJumlah,
                        'seriBarang' => $seri,
                    ],
                ],
            ];
        }

        $totalPungutan = $totalBeaMasuk + $totalPpn + $totalPph;

        // 2. Data Pelabuhan & TPS
        $kodePelMuat = $data['pelabuhan_muat'] ?? 'CNYTN';
        $kodePelTujuan = $data['pelabuhan_bongkar'] ?? ($data['pelabuhan_tujuan'] ?? 'IDTPP');
        $kodeTps = $data['kode_tps'] ?? 'TPS1';
        $tanggalTiba = $data['tanggal_tiba'] ?? ($data['eta'] ?? date('Y-m-d'));

        // 3. Manifes BC 1.1 Inward
        $nomorBc11 = $data['nomor_bc11'] ?? '001234';
        $tanggalBc11 = $data['tanggal_bc11'] ?? date('Y-m-d');
        $posBc11 = str_pad($data['pos_bc11'] ?? '0001', 4, '0', STR_PAD_LEFT);
        $subposBc11 = str_pad($data['subpos_bc11'] ?? '00000001', 8, '0', STR_PAD_LEFT);
        $kodeTutupPu = $data['kode_tutup_pu'] ?? '11'; // 11: BC 1.1

        // 4. Data Penandatangan Dokumen Pabean
        $namaTtd = $data['nama_ttd'] ?? 'AGUNG PRASETYO';
        $jabatanTtd = $data['jabatan_ttd'] ?? 'KUASA PENGUSAHA / PPJK';
        $kotaTtd = $data['kota_ttd'] ?? 'JAKARTA UTARA';
        $tanggalTtd = $data['tanggal_ttd'] ?? date('Y-m-d');

        // 5. Susunan Flat Root Payload Sesuai Standar DJBC JSON Schema Kirim BC 20
        return [
            // Header Properties (Directly at Root)
            'asalData' => 'S',
            'nomorAju' => $nomorAju,
            'kodeDokumen' => '20',
            'kodeKantor' => $kodeKantor,
            'kodeJenisImpor' => $data['kode_jenis_impor'] ?? '1', // 1: Untuk Dipakai
            'kodeJenisProsedur' => $data['kode_jenis_prosedur'] ?? '1', // 1: Biasa
            'kodeCaraBayar' => $data['kode_cara_bayar'] ?? '1',   // 1: Biasa/Tunai
            'kodeValuta' => $kodeValuta,
            'ndpbm' => $kurs,
            'cif' => $cif,
            'fob' => $fob,
            'freight' => $freight,
            'asuransi' => $asuransi,
            'bruto' => (float) ($data['bruto'] ?? 0),
            'netto' => (float) ($data['netto'] ?? 0),
            'volume' => (float) ($data['volume'] ?? 0),
            'biayaTambahan' => (float) ($data['biaya_tambahan'] ?? 0),
            'biayaPengurang' => (float) ($data['biaya_pengurang'] ?? 0),
            'hargaPenyerahan' => $cifRupiah,
            'idPengguna' => config('ceisa.api.username', 'agungtrans_api'),
            'disclaimer' => '1',
            'jumlahKontainer' => (int) ($data['jumlah_kontainer'] ?? 1),
            'kodeAsuransi' => $data['kode_asuransi'] ?? 'LN',
            'kodeIncoterm' => $data['kode_incoterm'] ?? 'CIF',
            'kodeJenisNilai' => $data['kode_jenis_nilai'] ?? '1',
            'kodePelMuat' => $kodePelMuat,
            'kodePelTujuan' => $kodePelTujuan,
            'kodeTps' => $kodeTps,
            'kodeTutupPu' => $kodeTutupPu,
            'nomorBc11' => $nomorBc11,
            'tanggalBc11' => $tanggalBc11,
            'posBc11' => $posBc11,
            'subposBc11' => $subposBc11,
            'tanggalAju' => date('Y-m-d'),
            'tanggalTiba' => $tanggalTiba,
            'tanggalTtd' => $tanggalTtd,
            'namaTtd' => $namaTtd,
            'jabatanTtd' => $jabatanTtd,
            'kotaTtd' => $kotaTtd,
            'nilaiBarang' => $cif,
            'nilaiIncoterm' => $cif,
            'nilaiMaklon' => 0.0,
            'seri' => 1,
            'totalDanaSawit' => 0.0,
            'flagVd' => 'T',
            'vd' => 0.0,

            // Entitas Kepabeanan BC 2.0 (Importir, Pemilik, Pengirim, Penjual)
            'entitas' => [
                [
                    'seriEntitas' => 1,
                    'kodeEntitas' => '1', // Importir
                    'kodeJenisIdentitas' => '5', // 5: NPWP 15 Digit
                    'nomorIdentitas' => $importirNpwp,
                    'nibEntitas' => $importirNib,
                    'namaEntitas' => $importirNama,
                    'alamatEntitas' => $importirAlamat,
                    'kodeJenisApi' => $data['importir_api'] ?? '01', // 01: API-U
                    'kodeStatus' => '1',
                ],
                [
                    'seriEntitas' => 2,
                    'kodeEntitas' => '7', // Pemilik Barang
                    'kodeJenisIdentitas' => '5',
                    'nomorIdentitas' => preg_replace('/[^0-9]/', '', $data['pemilik_npwp'] ?? $importirNpwp),
                    'namaEntitas' => $data['pemilik_nama'] ?? $importirNama,
                    'alamatEntitas' => $data['pemilik_alamat'] ?? $importirAlamat,
                    'kodeAfiliasi' => $data['kode_afiliasi'] ?? 'TAH', // TAH: Tidak Berhubungan
                ],
                [
                    'seriEntitas' => 3,
                    'kodeEntitas' => '9', // Pengirim / Supplier LN
                    'kodeNegara' => $supplierNegara,
                    'namaEntitas' => $supplierNama,
                    'alamatEntitas' => $supplierAlamat,
                ],
                [
                    'seriEntitas' => 4,
                    'kodeEntitas' => '10', // Penjual / Seller LN
                    'kodeNegara' => $data['seller_negara'] ?? $supplierNegara,
                    'namaEntitas' => $data['seller_nama'] ?? $supplierNama,
                    'alamatEntitas' => $data['seller_alamat'] ?? $supplierAlamat,
                ],
            ],

            // Dokumen Pelengkap Pabean
            'dokumen' => !empty($data['dokumen']) ? $data['dokumen'] : [
                [
                    'seriDokumen' => 1,
                    'kodeDokumen' => '380', // Invoice
                    'nomorDokumen' => $data['invoice_no'] ?? 'INV-' . date('Ymd-01'),
                    'tanggalDokumen' => $data['invoice_date'] ?? date('Y-m-d'),
                ],
                [
                    'seriDokumen' => 2,
                    'kodeDokumen' => ($data['cara_angkut'] ?? '1') === '4' ? '740' : '705', // 705: B/L, 740: AWB
                    'nomorDokumen' => $data['bl_no'] ?? 'BL-' . date('Ymd-01'),
                    'tanggalDokumen' => $data['bl_date'] ?? ($data['invoice_date'] ?? date('Y-m-d')),
                ],
                [
                    'seriDokumen' => 3,
                    'kodeDokumen' => '217', // Packing List
                    'nomorDokumen' => $data['pl_no'] ?? 'PL-' . date('Ymd-01'),
                    'tanggalDokumen' => $data['pl_date'] ?? ($data['invoice_date'] ?? date('Y-m-d')),
                ],
            ],

            // Sarana Pengangkut
            'pengangkut' => [
                [
                    'seriPengangkut' => 1,
                    'kodeCaraAngkut' => (string) ($data['cara_angkut'] ?? '1'), // 1: Laut
                    'namaPengangkut' => $data['nama_sarana_pengangkut'] ?? 'COSCO SHIPPING TAURUS',
                    'nomorPengangkut' => $data['nomor_voyage'] ?? '021E',
                    'kodeBendera' => $data['bendera_kapal'] ?? 'PA',
                ],
            ],

            // Kemasan
            'kemasan' => [
                [
                    'seriKemasan' => 1,
                    'kodeJenisKemasan' => $data['kode_kemasan'] ?? 'CT',
                    'jumlahKemasan' => (int) ($data['jumlah_kemasan'] ?? 100),
                    'merkKemasan' => $data['merek_kemasan'] ?? 'N/M',
                ],
            ],

            // Peti Kemas / Kontainer
            'kontainer' => [
                [
                    'seriKontainer' => 1,
                    'nomorKontainer' => $data['nomor_kontainer'] ?? 'TEMU1234567',
                    'kodeUkuranKontainer' => (string) ($data['ukuran_kontainer'] ?? '40'),
                    'kodeJenisKontainer' => (string) ($data['tipe_kontainer'] ?? '8'), // 8: FCL
                    'kodeTipeKontainer' => '1', // 1: General/Dry Cargo
                ],
            ],

            // Detil Barang
            'barang' => $formattedBarang,

            // Komponen Biaya Impor (Default Empty Array)
            'informasiKomponenBiaya' => [],

            // Kompatibilitas untuk Controller Existing
            'header' => [
                'nomorAju' => $nomorAju,
                'kodeDokumen' => '20',
                'kodeKantor' => $kodeKantor,
                'kodeJenisImpor' => $data['kode_jenis_impor'] ?? '1',
                'kodeCaraBayar' => $data['kode_cara_bayar'] ?? '1',
                'kodeValuta' => $kodeValuta,
                'ndbm' => $kurs,
                'cif' => $cif,
                'cifRupiah' => $cifRupiah,
                'fob' => $fob,
                'freight' => $freight,
                'asuransi' => $asuransi,
                'bruto' => (float) ($data['bruto'] ?? 0),
                'netto' => (float) ($data['netto'] ?? 0),
                'totalBeaMasuk' => $totalBeaMasuk,
                'totalPpn' => $totalPpn,
                'totalPph' => $totalPph,
                'totalPungutan' => $totalPungutan,
            ],
        ];
    }
}
