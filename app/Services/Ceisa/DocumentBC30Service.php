<?php

namespace App\Services\Ceisa;

use App\Models\CeisaDocument;

class DocumentBC30Service
{
    /**
     * Generate Nomor Aju 26 digit untuk BC 3.0 (PEB)
     * Pola: [4 digit kantor] [2 digit tipe doc: 30] [6 digit id perusahaan] [8 digit YYYYMMDD] [6 digit sequence]
     */
    public static function generateNomorAju(?string $kodeKantor = null): string
    {
        $kantor = $kodeKantor ?? config('ceisa.company.default_kode_kantor', '040300');
        $kantor4 = substr($kantor, 0, 4);
        $jenisDoc = '30';
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
     * Susun Payload JSON standar resmi CEISA 4.0 untuk BC 3.0 (PEB v.0.5.29)
     * Struktur: Flat Root Object sesuai JSON Schema Draft-07 DJBC
     */
    public static function buildPayload(array $data): array
    {
        $nomorAju = $data['nomor_aju'] ?? self::generateNomorAju($data['kode_kantor'] ?? null);
        $kodeKantor = $data['kode_kantor'] ?? config('ceisa.company.default_kode_kantor', '040300');
        $kurs = (float) ($data['ndpbm'] ?? ($data['ndbm_kurs'] ?? 15850.00));
        $fob = (float) ($data['fob'] ?? 0);
        $fobRupiah = round($fob * $kurs);
        $freight = (float) ($data['freight'] ?? 0);
        $asuransi = (float) ($data['asuransi'] ?? 0);
        $cif = $fob + $freight + $asuransi;
        $kodeValuta = $data['kode_valuta'] ?? 'USD';

        // Entitas Data
        $eksportirNpwp = preg_replace('/[^0-9]/', '', $data['eksportir_npwp'] ?? config('ceisa.company.npwp', '012345678012000'));
        $eksportirNama = $data['eksportir_nama'] ?? config('ceisa.company.name', 'PT AGUNG TRANS SOLUSINDO');
        $eksportirAlamat = $data['eksportir_alamat'] ?? config('ceisa.company.address', 'Jl. Pelabuhan Nusantara II No. 12, Tanjung Priok, Jakarta Utara');
        $eksportirNib = $data['eksportir_nib'] ?? config('ceisa.company.nib', '9120009876543');

        $buyerNama = $data['buyer_nama'] ?? ($data['pembeli_nama'] ?? 'PACIFIC IMPORT DISTRIBUTORS INC');
        $buyerAlamat = $data['buyer_alamat'] ?? '1200 Pacific Coast Hwy, California';
        $buyerNegara = strtoupper(substr($data['buyer_negara'] ?? ($data['pembeli_negara'] ?? 'US'), 0, 2));

        // 1. Array Barang & Bea Keluar
        $rawBarang = $data['barang'] ?? [];
        $formattedBarang = [];
        $totalBeaKeluar = 0;

        foreach ($rawBarang as $index => $item) {
            $seri = $index + 1;
            $itemFob = (float) ($item['fob'] ?? ($fob / max(1, count($rawBarang))));
            $itemFobRupiah = round($itemFob * $kurs);
            $itemJumlah = (float) ($item['jumlah'] ?? ($item['jumlah_satuan'] ?? ($item['jumlahSatuan'] ?? 1)));
            $itemNetto = (float) ($item['netto'] ?? 1);
            $satuan = $item['satuan'] ?? ($item['kode_satuan'] ?? ($item['kodeSatuanBarang'] ?? 'KGM'));
            $jenisKemasan = $item['jenis_kemasan'] ?? ($item['kode_jenis_kemasan'] ?? ($item['kodeJenisKemasan'] ?? ($data['kode_kemasan'] ?? 'PK')));

            // Tarif Bea Keluar
            $tarifBk = (float) ($item['tarif_bk'] ?? ($item['tarifBk'] ?? ($item['bk'] ?? 0)));
            $nilaiBk = round(($tarifBk / 100) * $itemFobRupiah);
            $totalBeaKeluar += $nilaiBk;

            $merk = $item['merk'] ?? ($item['merek'] ?? 'INDONESIAN ORIGIN');
            $tipe = $item['tipe'] ?? 'GRADE A';

            $posTarif = preg_replace('/[^0-9]/', '', (string) ($item['hs_code'] ?? ($item['pos_tarif'] ?? ($item['posTarif'] ?? ($item['kode_hs'] ?? '09012110')))));

            $formattedBarang[] = [
                'seriBarang' => $seri,
                'kodeBarang' => $item['kode_barang'] ?? ('BRG-EXP-' . str_pad((string) $seri, 3, '0', STR_PAD_LEFT)),
                'posTarif' => $posTarif,
                'uraian' => $item['uraian'] ?? ($item['uraian_barang'] ?? 'Barang Ekspor'),
                'merk' => $merk,
                'tipe' => $tipe,
                'spesifikasiLain' => $item['spesifikasi'] ?? '-',
                'kodeSatuanBarang' => $satuan,
                'jumlahSatuan' => $itemJumlah,
                'jumlahKemasan' => (float) ($item['jumlah_kemasan'] ?? ($item['jumlahKemasan'] ?? $itemJumlah)),
                'kodeJenisKemasan' => $jenisKemasan,
                'netto' => $itemNetto,
                'fob' => $itemFob,
                'hargaSatuan' => round($itemFob / max(1, $itemJumlah), 4),
                'hargaPatokan' => 0.0,
                'hargaPerolehan' => $itemFobRupiah,
                'hargaEkspor' => $itemFobRupiah,
                'kodeDaerahAsal' => substr($item['kode_daerah_asal'] ?? '3171', 0, 4),
                'kodeNegaraAsal' => 'ID',
                'kodeDokumen' => '30',
                'kodeJenisEkspor' => $data['kode_jenis_ekspor'] ?? '1', // 1: Ekspor Biasa
                'ndpbm' => $kurs,
                'nilaiBarang' => $itemFob,
                'nilaiDanaSawit' => 0.0,
                'barangTarif' => [
                    [
                        'kodeJenisPungutan' => 'BK',
                        'kodeJenisTarif' => '1',
                        'kodeFasilitasTarif' => '1',
                        'tarif' => $tarifBk,
                        'nilaiBayar' => $nilaiBk,
                        'tarifFasilitas' => 0.0,
                        'jumlahSatuan' => $itemJumlah,
                        'kodeSatuanBarang' => $satuan,
                        'seriBarang' => $seri,
                    ],
                ],
                'barangDokumen' => $item['barangDokumen'] ?? ($item['barang_dokumen'] ?? []),
                'barangSpekKhusus' => [],
                'barangPemilik' => [],
            ];
        }

        // 2. Data Pelabuhan & Kantor
        $kodePelEkspor = $data['pelabuhan_muat'] ?? 'IDTPP';
        $kodePelMuat = $data['pelabuhan_muat_asal'] ?? $kodePelEkspor;
        $kodePelTujuan = $data['pelabuhan_bongkar'] ?? ($data['pelabuhan_tujuan'] ?? 'SGSIN');
        $kodeKantorEkspor = $data['kode_kantor_ekspor'] ?? $kodeKantor;
        $kodeKantorMuat = $data['kode_kantor_muat'] ?? $kodeKantor;
        $kodeKantorPeriksa = $data['kode_kantor_periksa'] ?? $kodeKantor;

        // 3. Data Penandatangan
        $namaTtd = $data['nama_ttd'] ?? 'AGUNG PRASETYO';
        $jabatanTtd = $data['jabatan_ttd'] ?? 'DIREKTUR / KUASA EKSPORTIR';
        $kotaTtd = $data['kota_ttd'] ?? 'JAKARTA UTARA';
        $tanggalTtd = $data['tanggal_ttd'] ?? date('Y-m-d');
        $tanggalPeriksa = $data['tanggal_periksa'] ?? date('Y-m-d');
        $tanggalEkspor = $data['tanggal_ekspor'] ?? date('Y-m-d');

        // 4. Susunan Flat Root Payload Sesuai Standar DJBC JSON Schema Kirim BC 30
        return [
            // Header Properties (Directly at Root)
            'asalData' => 'S',
            'nomorAju' => $nomorAju,
            'kodeDokumen' => '30',
            'kodeKantor' => $kodeKantor,
            'kodeKantorEkspor' => $kodeKantorEkspor,
            'kodeKantorMuat' => $kodeKantorMuat,
            'kodeKantorPeriksa' => $kodeKantorPeriksa,
            'kodeJenisEkspor' => $data['kode_jenis_ekspor'] ?? '1',     // 1: Ekspor Biasa
            'kodeKategoriEkspor' => $data['kode_kategori_ekspor'] ?? '1', // 1: Ekspor Umum
            'kodeJenisProsedur' => $data['kode_jenis_prosedur'] ?? '1',
            'kodeCaraDagang' => $data['kode_cara_dagang'] ?? '1',         // 1: Imbal Dagang/Biasa
            'kodeCaraBayar' => $data['kode_cara_bayar'] ?? '1',           // 1: Tunai / LC
            'kodeJenisPengangkutan' => (string) ($data['cara_angkut'] ?? '1'), // 1: Laut
            'kodeValuta' => $kodeValuta,
            'ndpbm' => $kurs,
            'fob' => $fob,
            'freight' => $freight,
            'asuransi' => $asuransi,
            'cif' => $cif,
            'bruto' => (float) ($data['bruto'] ?? 0),
            'netto' => (float) ($data['netto'] ?? 0),
            'nilaiMaklon' => 0.0,
            'totalDanaSawit' => 0.0,
            'disclaimer' => '1',
            'flagBarkir' => 'T',  // Non barang kiriman
            'flagCurah' => '2',   // Non curah (required when flagBarkir == T)
            'flagMigas' => '2',   // Non migas
            'idPengguna' => config('ceisa.api.username', 'agungtrans_api'),
            'jumlahKontainer' => (int) ($data['jumlah_kontainer'] ?? 1),
            'kodeAsuransi' => $data['kode_asuransi'] ?? 'DN',
            'kodeIncoterm' => $data['kode_incoterm'] ?? 'FOB',
            'kodeJenisNilai' => $data['kode_jenis_nilai'] ?? '1',
            'kodeLokasi' => $data['kode_lokasi'] ?? '1', // 1: KP Tempat Pemuatan
            'kodeNegaraTujuan' => $buyerNegara,
            'kodePelEkspor' => $kodePelEkspor,
            'kodePelMuat' => $kodePelMuat,
            'kodePelTujuan' => $kodePelTujuan,
            'kodeTps' => $data['kode_tps'] ?? 'TPS1',
            'namaTtd' => $namaTtd,
            'jabatanTtd' => $jabatanTtd,
            'kotaTtd' => $kotaTtd,
            'tanggalAju' => date('Y-m-d'),
            'tanggalEkspor' => $tanggalEkspor,
            'tanggalPeriksa' => $tanggalPeriksa,
            'tanggalTtd' => $tanggalTtd,
            'seri' => 1,

            // Kesiapan Barang (Mandatory array di BC 3.0)
            'kesiapanBarang' => [
                [
                    'kodeJenisBarang' => '1', // 1: Barang Ekspor Gabungan
                    'kodeJenisGudang' => $data['kode_jenis_gudang'] ?? '2', // 2: Gudang Pabrik
                    'namaPic' => $data['pic_pembuat'] ?? ($data['pic_kesiapan'] ?? 'BAMBANG SUTRISNO'),
                    'alamat' => $data['alamat_kesiapan'] ?? $eksportirAlamat,
                    'nomorTelpPic' => $data['telepon_pembuat'] ?? ($data['telp_pic'] ?? '081298765432'),
                    'jumlahContainer20' => 0,
                    'jumlahContainer40' => (int) ($data['jumlah_kontainer'] ?? 1),
                    'lokasiSiapPeriksa' => $data['lokasi_siap_periksa'] ?? 'GUDANG UTAMA PRIOK',
                    'kodeCaraStuffing' => '8', // 8: FCL
                    'tanggalPkb' => $data['tanggal_periksa'] ?? ($data['tanggal_pkb'] ?? date('Y-m-d')),
                    'waktuSiapPeriksa' => ($data['tanggal_periksa'] ?? ($data['tanggal_pkb'] ?? date('Y-m-d'))) . 'T09:00:00+07:00',
                ],
            ],

            // Bank Devisa Hasil Ekspor (DHE - Mandatory array di BC 3.0)
            'bankDevisa' => [
                [
                    'seriBank' => 1,
                    'kodeBank' => $data['bank_kode'] ?? ($data['kode_bank'] ?? 'BMRI'),
                    'namaBank' => $data['bank_nama'] ?? ($data['nama_bank'] ?? 'PT BANK MANDIRI (PERSERO) TBK'),
                ],
            ],

            // Entitas Kepabeanan BC 3.0 (Eksportir, Pemilik, Penerima, Pembeli)
            'entitas' => [
                [
                    'seriEntitas' => 1,
                    'kodeEntitas' => '2', // Eksportir
                    'kodeJenisIdentitas' => '5',
                    'nomorIdentitas' => $eksportirNpwp,
                    'nibEntitas' => $eksportirNib,
                    'namaEntitas' => $eksportirNama,
                    'alamatEntitas' => $eksportirAlamat,
                ],
                [
                    'seriEntitas' => 2,
                    'kodeEntitas' => '7', // Pemilik Barang
                    'kodeJenisIdentitas' => '5',
                    'nomorIdentitas' => preg_replace('/[^0-9]/', '', $data['pemilik_npwp'] ?? $eksportirNpwp),
                    'nibEntitas' => $eksportirNib,
                    'namaEntitas' => $data['pemilik_nama'] ?? $eksportirNama,
                    'alamatEntitas' => $data['pemilik_alamat'] ?? $eksportirAlamat,
                ],
                [
                    'seriEntitas' => 3,
                    'kodeEntitas' => '8', // Penerima LN (Consignee)
                    'kodeNegara' => $buyerNegara,
                    'namaEntitas' => $data['consignee_nama'] ?? $buyerNama,
                    'alamatEntitas' => $data['consignee_alamat'] ?? $buyerAlamat,
                ],
                [
                    'seriEntitas' => 4,
                    'kodeEntitas' => '6', // Pembeli LN (Buyer)
                    'kodeNegara' => $buyerNegara,
                    'namaEntitas' => $buyerNama,
                    'alamatEntitas' => $buyerAlamat,
                ],
            ],

            // Dokumen Pelengkap Pabean
            'dokumen' => !empty($data['dokumen']) ? $data['dokumen'] : [
                [
                    'seriDokumen' => 1,
                    'kodeDokumen' => '380', // Invoice
                    'nomorDokumen' => $data['invoice_no'] ?? 'EXP-INV-' . date('Ymd-01'),
                    'tanggalDokumen' => $data['invoice_date'] ?? date('Y-m-d'),
                ],
                [
                    'seriDokumen' => 2,
                    'kodeDokumen' => '217', // Packing List
                    'nomorDokumen' => $data['pl_no'] ?? 'EXP-PL-' . date('Ymd-01'),
                    'tanggalDokumen' => $data['pl_date'] ?? date('Y-m-d'),
                ],
            ],

            // Sarana Pengangkut
            'pengangkut' => [
                [
                    'seriPengangkut' => 1,
                    'kodeCaraAngkut' => (string) ($data['cara_angkut'] ?? '1'), // 1: Laut
                    'namaPengangkut' => $data['nama_sarana_pengangkut'] ?? 'MAERSK MC-KINNEY MOLLER',
                    'nomorPengangkut' => $data['nomor_voyage'] ?? '2601W',
                    'kodeBendera' => $data['bendera_kapal'] ?? 'DK',
                ],
            ],

            // Kemasan
            'kemasan' => [
                [
                    'seriKemasan' => 1,
                    'kodeJenisKemasan' => $data['kode_kemasan'] ?? 'PK',
                    'jumlahKemasan' => (int) ($data['jumlah_kemasan'] ?? 50),
                    'merkKemasan' => $data['merek_kemasan'] ?? 'AGUNG-EXPORT',
                ],
            ],

            // Peti Kemas / Kontainer
            'kontainer' => [
                [
                    'seriKontainer' => 1,
                    'nomorKontainer' => $data['nomor_kontainer'] ?? 'MSKU9876543',
                    'kodeUkuranKontainer' => (string) ($data['ukuran_kontainer'] ?? '40'),
                    'kodeJenisKontainer' => (string) ($data['tipe_kontainer'] ?? '8'), // 8: FCL
                    'kodeTipeKontainer' => '1',
                ],
            ],

            // Detil Barang
            'barang' => $formattedBarang,

            // Kompatibilitas untuk Controller Existing
            'header' => [
                'nomorAju' => $nomorAju,
                'kodeDokumen' => '30',
                'kodeKantor' => $kodeKantor,
                'kodeKategoriEkspor' => $data['kode_kategori_ekspor'] ?? '1',
                'kodeCaraDagang' => $data['kode_cara_dagang'] ?? '1',
                'kodeCaraBayar' => $data['kode_cara_bayar'] ?? '1',
                'kodeValuta' => $kodeValuta,
                'ndbm' => $kurs,
                'fob' => $fob,
                'fobRupiah' => $fobRupiah,
                'freight' => $freight,
                'asuransi' => $asuransi,
                'bruto' => (float) ($data['bruto'] ?? 0),
                'netto' => (float) ($data['netto'] ?? 0),
                'totalBeaKeluar' => $totalBeaKeluar,
            ],
        ];
    }
}
