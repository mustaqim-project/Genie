<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>BC 2.0 - Pemberitahuan Impor Barang (PIB) - {{ $shipment->ceisaDocument->nomor_aju ?? $shipment->project_no }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm 10mm 10mm 10mm;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 7.5pt;
            color: #000;
            line-height: 1.15;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }
        .page {
            width: 100%;
            position: relative;
            background: white;
        }
        .page-break {
            page-break-before: always;
        }
        .border-all {
            border: 1px solid #000;
        }
        .border-t { border-top: 1px solid #000; }
        .border-b { border-bottom: 1px solid #000; }
        .border-l { border-left: 1px solid #000; }
        .border-r { border-right: 1px solid #000; }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            padding: 0;
        }
        td, th {
            padding: 2px 3px;
            vertical-align: top;
            font-size: 7.5pt;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        
        .header-title {
            text-align: center;
            font-size: 10pt;
            font-weight: bold;
            margin-bottom: 3px;
            letter-spacing: 0.5px;
        }
        .doc-type-badge {
            font-size: 10pt;
            font-weight: bold;
            text-align: right;
        }
        .check-box {
            display: inline-block;
            width: 11px;
            height: 11px;
            border: 1px solid #000;
            text-align: center;
            line-height: 10px;
            font-size: 8pt;
            font-weight: bold;
            margin-right: 3px;
            vertical-align: middle;
        }
        .sub-label {
            font-size: 6.5pt;
            color: #222;
        }
        .table-items th {
            background-color: #f5f5f5;
            font-weight: bold;
            border: 1px solid #000;
            text-align: left;
            font-size: 6.8pt;
            padding: 3px;
        }
        .table-items td {
            border: 1px solid #000;
            font-size: 7pt;
            padding: 2px 3px;
        }
        .table-levy th, .table-levy td {
            border: 1px solid #000;
            font-size: 7pt;
            padding: 2px 3px;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; background: none; }
        }
    </style>
</head>
<body>

@php
    $items = $shipment->items;
    $itemCount = $items->count();
    $chunkSize = 11;
    $itemChunks = $items->chunk($chunkSize);
    $totalItemSheets = max(1, $itemChunks->count());
    // Lembar Utama (1) + Lembar Lanjutan Barang (totalItemSheets jika > 1) + Lembar Dokumen (1)
    $totalPages = $itemCount > 1 ? (1 + $totalItemSheets + 1) : 2;
    $client = $shipment->client;
    $ceisa = $shipment->ceisaDocument;
    $nomorAju = $ceisa->nomor_aju ?? '00002090556620240621000039';
    $tglAju = $ceisa->tanggal_aju ? date('d-m-Y', strtotime($ceisa->tanggal_aju)) : '03-07-2024';
    $nomorDaftar = $ceisa->nomor_daftar ?? '600124';
    $tglDaftar = $ceisa->tanggal_daftar ? date('d-m-Y', strtotime($ceisa->tanggal_daftar)) : '05-07-2024';
    $kantorPabean = $ceisa->nama_kantor_pendek ?? 'KPPBC TMP B SAMARINDA';
    $kodeKantor = $ceisa->kode_kantor ?? '100500';
    $valuta = $shipment->kode_valuta ?: 'CNY';
    $kursNdbm = (float)($shipment->ndbm_kurs ?: 2249.69);
    $totalCifValuta = (float)($shipment->total_nilai_valuta ?: 487940.90);
    $itemsSumFob = (float)($items->sum('nilai_fob') ?: 471875.90);
    $freightValuta = max(0, $totalCifValuta - $itemsSumFob);
    $totalCifIdr = (float)($shipment->total_nilai_idr ?: ($totalCifValuta * $kursNdbm));

    // Perhitungan Pungutan (Real / Estimated)
    $tax = $shipment->estimated_taxes;
    $bmBayar = (float)($tax['bea_masuk'] ?? 127000.00);
    $ppnBayar = (float)($tax['ppn'] ?? 120762588.00);
    $pphBayar = (float)($tax['pph'] ?? 27444375.00);
    $totalBayar = $bmBayar + $ppnBayar + $pphBayar;
@endphp

    <!-- Print Control Bar (Screen Only) -->
    <div class="no-print" style="background: #0f172a; color: white; padding: 10px 16px; margin-bottom: 12px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
        <div>
            <strong style="font-size: 11pt; color: #38bdf8;">Pratinjau Dokumen BC 2.0 (Pemberitahuan Impor Barang)</strong>
            <span style="font-size: 8.5pt; color: #94a3b8; margin-left: 12px;">
                Format Standar DJBC 1:1 | No. Aju: <strong>{{ $nomorAju }}</strong> | Total: <strong>{{ $itemCount }} Jenis Barang</strong>
            </span>
        </div>
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" style="background: #2563eb; color: white; border: none; padding: 6px 14px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 8.5pt;">
                🖨️ Cetak / Simpan PDF
            </button>
            <a href="?download=1" style="background: #059669; color: white; text-decoration: none; padding: 6px 14px; border-radius: 6px; font-weight: bold; font-size: 8.5pt; display: inline-block;">
                📥 Download PDF Langsung
            </a>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- HALAMAN 1: LEMBAR UTAMA BC 2.0 (PIB)                      -->
    <!-- ======================================================== -->
    <div class="page">
        <!-- Top Title & Identitas Lembar -->
        <table style="margin-bottom: 2px;">
            <tr>
                <td style="width: 25%;"></td>
                <td style="width: 50%; text-align: center;">
                    <div class="header-title">PEMBERITAHUAN IMPOR BARANG (PIB)</div>
                </td>
                <td style="width: 25%; text-align: right;">
                    <span class="doc-type-badge">BC 2.0</span>
                </td>
            </tr>
        </table>

        <!-- Header Information Box -->
        <table class="border-all" style="margin-bottom: 3px;">
            <tr>
                <td style="width: 14%;" class="font-bold">Kantor Pabean</td>
                <td style="width: 1%;">:</td>
                <td style="width: 45%;">{{ $kantorPabean }}</td>
                <td style="width: 15%; text-align: center;" class="border-l border-r font-bold">{{ $kodeKantor }}</td>
                <td style="width: 25%; text-align: right;">Halaman ke-1 dari {{ $totalPages }}</td>
            </tr>
            <tr class="border-t">
                <td class="font-bold">Nomor Pengajuan</td>
                <td>:</td>
                <td class="font-bold" style="font-family: monospace; font-size: 8pt;">{{ $nomorAju }}</td>
                <td class="border-l font-bold">Tanggal Pengajuan</td>
                <td style="text-align: right;">: {{ $tglAju }}</td>
            </tr>
        </table>

        <!-- Checklist Baris A, B, C -->
        <table class="border-all" style="margin-bottom: 3px; font-size: 7.2pt;">
            <tr>
                <td style="width: 28%;" class="border-r">
                    <strong>A. JENIS PIB</strong> &nbsp;&nbsp;&nbsp;&nbsp;: 
                    <span class="check-box">1</span> 1. Biasa;&nbsp;&nbsp; 2. Berkala.
                </td>
                <td style="width: 40%;" class="border-r">
                    <strong>B. JENIS IMPOR</strong> &nbsp;&nbsp;: 
                    <span class="check-box">1</span> 1. Untuk Dipakai;&nbsp;&nbsp; 2. Sementara;<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    5. Pelayanan Segera;&nbsp;&nbsp; 9. Gabungan 1 & 2.
                </td>
                <td style="width: 32%;">
                    <strong>C. CARA PEMBAYARAN</strong> : 
                    <span class="check-box">1</span> 1. Biasa/Tunai;&nbsp;&nbsp; 2. Berkala;<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    3. Dengan Jaminan;&nbsp;&nbsp; 9. Lainnya
                </td>
            </tr>
        </table>

        <!-- Bagian D. DATA (2 Kolom Berdampingan) -->
        <div style="font-weight: bold; font-size: 7.5pt; margin-bottom: 2px;">D. DATA</div>
        <table class="border-all" style="margin-bottom: 3px;">
            <tr>
                <!-- KOLOM KIRI (Entitas Bisnis: Pengirim, Penjual, Importir, Pemilik, PPJK) -->
                <td style="width: 50%; padding: 0; vertical-align: top;" class="border-r">
                    <table style="width: 100%;">
                        <tr>
                            <td style="background: #fafafa; font-weight: bold;" colspan="2">PENGIRIM</td>
                            <td style="background: #fafafa; text-align: right; font-weight: bold; width: 35px;" class="border-l text-center">
                                {{ $shipment->country_of_loading ?? 'CN' }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" style="padding-bottom: 4px;">
                                <strong>1. Nama, Alamat :</strong> {{ $shipment->supplier_name ?? 'WEIFANG SINO-TRUST TRADING CO.,LTD' }}<br>
                                <span style="font-size: 6.8pt; color: #333;">
                                    {{ $shipment->supplier_address ?? '1316, BLOCK B, QUANFUYUAN BUSINESS CENTER, XINGLONG ROAD, LINQU COUNTY, CHINA' }}
                                </span>
                            </td>
                        </tr>
                        <tr class="border-t">
                            <td style="background: #fafafa; font-weight: bold;" colspan="2">PENJUAL</td>
                            <td style="background: #fafafa; text-align: right; font-weight: bold;" class="border-l text-center">
                                {{ $shipment->country_of_loading ?? 'CN' }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" style="padding-bottom: 4px;">
                                <strong>1a. Nama, Alamat :</strong> {{ $shipment->supplier_name ?? 'WEIFANG SINO-TRUST TRADING CO.,LTD' }}<br>
                                <span style="font-size: 6.8pt; color: #333;">
                                    {{ $shipment->supplier_address ?? '1316, BLOCK B, QUANFUYUAN BUSINESS CENTER, XINGLONG ROAD, LINQU COUNTY, CHINA' }}
                                </span>
                            </td>
                        </tr>
                        <tr class="border-t">
                            <td style="background: #fafafa; font-weight: bold;" colspan="3">IMPORTIR</td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <strong>2. Identitas :</strong> {{ $client->npwp ?? '752967141041000' }} / {{ $client->npwp ?? '752967141041000' }}<br>
                                <strong>3. Nama, Alamat :</strong> {{ $client->name ?? 'PT. JINDI MATERIAL INDONESIA' }}<br>
                                <span style="font-size: 6.8pt; color: #333;">
                                    {{ $client->address ?? 'JLN. PANTAI INDAH BARAT KOMPLEK TOHO BLOK I NO 3, KAMAL MUARA -' }}
                                </span><br>
                                <table style="margin-top: 2px;">
                                    <tr>
                                        <td style="width: 50%; padding: 0;"><strong>4. Status :</strong> LAINNYA</td>
                                        <td style="width: 50%; padding: 0;"><strong>5. NIB :</strong> {{ $client->nib ?? '8120007862149' }}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr class="border-t">
                            <td style="background: #fafafa; font-weight: bold;" colspan="3">PEMILIK BARANG</td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <strong>2a. Identitas :</strong> {{ $client->npwp ?? '752967141041000' }}<br>
                                <strong>3a. Nama, Alamat :</strong> {{ $client->name ?? 'PT. JINDI MATERIAL INDONESIA' }}<br>
                                <span style="font-size: 6.8pt; color: #333;">
                                    {{ $client->address ?? 'JLN. PANTAI INDAH BARAT KOMPLEK TOHO BLOK I NO 3, KAMAL MUARA -' }}
                                </span>
                            </td>
                        </tr>
                        <tr class="border-t">
                            <td style="background: #fafafa; font-weight: bold;" colspan="3">PPJK</td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <strong>6. NPWP :</strong> 905566048034000<br>
                                <strong>7. Nama, Alamat :</strong> AGUNG TRANS SOLUSINDO<br>
                                <span style="font-size: 6.8pt; color: #333;">
                                    THE MANSION - TOWER FONTANA, LANTAI 30 UNIT BF-30BI, JALAN TREMBESI BLOK D4 BANDAR BARU KOMPLEK KEMAYORAN, JAKARTA
                                </span><br>
                                <strong>8. NP-PPJK :</strong> -
                            </td>
                        </tr>
                    </table>
                </td>

                <!-- KOLOM KANAN (Pengangkutan, Pelabuhan, Dokumen, Valuta & Nilai Transaksi) -->
                <td style="width: 50%; padding: 0; vertical-align: top;">
                    <table style="width: 100%;">
                        <tr>
                            <td style="width: 65%;"><strong>G. Nomor dan Tanggal Pendaftaran</strong></td>
                            <td style="width: 35%; text-align: right;" class="font-bold">{{ $nomorDaftar }} &nbsp; {{ $tglDaftar }}</td>
                        </tr>
                        <tr class="border-t">
                            <td colspan="2">
                                <strong>9. Cara Pengangkutan :</strong> LAUT &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 1
                            </td>
                        </tr>
                        <tr class="border-t">
                            <td colspan="2">
                                <strong>10. Nama Sarana Pengangkutan & No. Voy/Flight dan Bendera :</strong><br>
                                <div style="display: flex; justify-content: space-between;">
                                    <span><strong>{{ $shipment->nama_kapal ?? 'EVER VIM' }}</strong></span>
                                    <span>{{ $shipment->voyage_no ?? '0731-004S' }}</span>
                                    <span>CHINA (CN)</span>
                                </div>
                            </td>
                        </tr>
                        <tr class="border-t">
                            <td colspan="2">
                                <strong>11. Perkiraan Tanggal Tiba :</strong> {{ $shipment->eta_date ? date('d-m-Y', strtotime($shipment->eta_date)) : '28-06-2024' }}
                            </td>
                        </tr>
                        <tr class="border-t">
                            <td colspan="2">
                                <table style="width: 100%;">
                                    <tr>
                                        <td style="width: 50%; padding: 0;"><strong>12. Pelabuhan Muat :</strong> QINGDAO</td>
                                        <td style="width: 50%; padding: 0; text-align: right;" class="font-bold">CNTAO</td>
                                    </tr>
                                    <tr>
                                        <td style="width: 50%; padding: 0;"><strong>13. Pelabuhan Transit :</strong> -</td>
                                        <td style="width: 50%; padding: 0; text-align: right;">-</td>
                                    </tr>
                                    <tr>
                                        <td style="width: 50%; padding: 0;"><strong>14. Pelabuhan Tujuan :</strong> SAMARINDA</td>
                                        <td style="width: 50%; padding: 0; text-align: right;" class="font-bold">IDSRD</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr class="border-t">
                            <td colspan="2">
                                <strong>15. Invoice :</strong> No. {{ $shipment->invoice_number ?? 'HZ20240519' }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Tgl. 19-05-2024<br>
                                <strong>16. Transaksi :</strong> LAI<br>
                                <strong>17. House-BL/AWB :</strong> No. {{ $shipment->nomor_bl_awb ?? 'EGLV140401309432' }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Tgl. 28-05-2024<br>
                                &nbsp;&nbsp;&nbsp;&nbsp; <strong>Master-BL/AWB :</strong> No. -<br>
                                <strong>18. BC 1.1/1.2 :</strong> No. - &nbsp;&nbsp;&nbsp; Tgl. - &nbsp;&nbsp;&nbsp; Pos. - &nbsp;&nbsp;&nbsp; Sub Pos. -
                            </td>
                        </tr>
                        <tr class="border-t">
                            <td colspan="2">
                                <table style="width: 100%;">
                                    <tr>
                                        <td style="padding: 0;"><strong>19. Pemenuhan Persyaratan/Fasilitas Impor :</strong><br>
                                            NO. E24MA3BXKGH80073 &nbsp;&nbsp;&nbsp;&nbsp; TGL. 21-06-2024
                                        </td>
                                        <td style="padding: 0; text-align: right; width: 25px;" class="font-bold">54</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr class="border-t">
                            <td colspan="2">
                                <table style="width: 100%;">
                                    <tr>
                                        <td style="padding: 0;"><strong>20. Tempat Penimbunan :</strong> KAWASAN PABEAN</td>
                                        <td style="padding: 0; text-align: right;" class="font-bold">A001</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr class="border-t">
                            <td colspan="2">
                                <table style="width: 100%;">
                                    <tr>
                                        <td style="width: 50%; padding: 0;"><strong>21. Valuta :</strong> {{ $valuta }} ({{ $valuta === 'CNY' ? 'YUAN RENMINBI' : ($valuta === 'USD' ? 'US DOLLAR' : $valuta) }})</td>
                                        <td style="width: 50%; padding: 0; text-align: right;"><strong>22. NDPBM :</strong> {{ number_format($kursNdbm, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 0;"><strong>23. Nilai FOB :</strong> {{ number_format($itemsSumFob, 2) }}</td>
                                        <td style="padding: 0; text-align: right;" rowspan="2">
                                            <strong>26. Nilai Pabean :</strong><br>
                                            <span class="font-bold">{{ number_format($totalCifValuta, 2) }}</span><br>
                                            <span style="font-size: 7pt; color: #1e3a8a;">RP {{ number_format($totalCifIdr, 2) }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 0;">
                                            <strong>24. Asuransi/LDN :</strong> 0.00<br>
                                            <strong>25. Freight :</strong> {{ number_format($freightValuta, 2) }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <!-- Baris Peti Kemas, Kemasan, dan Berat (Full Width Kolom 27 - 30) -->
            <tr class="border-t">
                <td colspan="2" style="padding: 0;">
                    <table style="width: 100%;">
                        <tr>
                            <td style="width: 32%;" class="border-r">
                                <strong>27. Nomor, Ukuran, dan Tipe Peti Kemas</strong><br>
                                <span class="font-bold" style="font-size: 8pt;">{{ $shipment->nomor_kontainer ?? 'EGSU9374923' }} &nbsp; 40 FCL</span>
                            </td>
                            <td style="width: 34%;" class="border-r">
                                <strong>28. Jumlah, Jenis, dan Merek Kemas :</strong><br>
                                <span class="font-bold">{{ $shipment->total_kemasan ?? 421 }} PACKAGE, Tanpa Merk</span>
                            </td>
                            <td style="width: 17%;" class="border-r">
                                <strong>29. Berat Kotor (Kg)</strong><br>
                                <span class="font-bold">{{ number_format($shipment->total_bruto ?? 28741.35, 4) }}</span>
                            </td>
                            <td style="width: 17%;">
                                <strong>30. Berat Bersih</strong><br>
                                <span class="font-bold">{{ number_format($shipment->total_netto ?? 28563.00, 4) }}</span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <!-- Header Kolom 31 s.d. 36 -->
            <tr class="border-t">
                <td colspan="2" style="padding: 0;">
                    <table class="table-items" style="width: 100%; border: none;">
                        <thead>
                            <tr>
                                <th style="width: 3%; text-align: center;">31.<br>No.</th>
                                <th style="width: 33%;">32. - Pos Tarif HS<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Uraian Jenis Barang, Merek, Tipe, Spesifikasi Wajib<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Negara Asal Barang
                                </th>
                                <th style="width: 18%;">33. Keterangan<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Fasilitas & No. Urut<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Persyaratan & No. Urut
                                </th>
                                <th style="width: 14%;">34. Tarif dan<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fasilitas</th>
                                <th style="width: 16%;">35. - Jumlah dan Jenis<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Satuan Barang<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Berat Bersih (Kg)<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Jumlah dan Jenis
                                </th>
                                <th style="width: 16%;">36. - Nilai Pabean<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Jenis<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Nilai yang Ditambahkan
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- JIKA BARANG LEBIH DARI 1: TIDAK ADA HS CODE DI HALAMAN UTAMA! -->
                            @if($itemCount > 1)
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 22px 10px; font-weight: bold; font-size: 8pt; background: #fff;">
                                        ---------------------------------------- {{ $itemCount }} Jenis barang. Lihat lembar lanjutan ----------------------------------------
                                    </td>
                                </tr>
                            @elseif($itemCount === 1)
                                @php $single = $items->first(); @endphp
                                <tr>
                                    <td style="text-align: center;">1</td>
                                    <td>
                                        <strong>Pos Tarif : {{ $single->kode_hs }}</strong> Kode Brg : -<br>
                                        Uraian : {{ $single->uraian_barang }}<br>
                                        Merk: -, Tipe: -, Ukuran: -, Spesifikasi lain: -<br>
                                        Kondisi Brg : BARU<br>
                                        Negara : CHINA (CN)
                                    </td>
                                    <td>- PREFERENSI TARIF IMPORTASI ASEAN-CHINA (ACFTA) (4)</td>
                                    <td>
                                        BM {{ $single->tarif_bm_persen }}% 100% BYR<br>
                                        PPH {{ $single->tarif_pph_persen }}% 100% BYR<br>
                                        PPN {{ $single->tarif_ppn_persen }}% 100% BYR
                                    </td>
                                    <td>
                                        {{ number_format($single->jumlah_kemasan) }} {{ $single->jenis_kemasan }}<br>
                                        {{ number_format($single->berat_netto, 4) }}<br>
                                        {{ number_format($single->jumlah_kemasan) }} {{ $single->jenis_kemasan }}
                                    </td>
                                    <td>
                                        {{ number_format($single->nilai_fob, 2) }}<br>
                                        - NTR<br>
                                        - 0
                                    </td>
                                </tr>
                            @else
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 15px; color: #666;">
                                        Data barang belum tersedia di sistem.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </td>
            </tr>

            <!-- Tabel Jenis Pungutan Impor (Baris 37 s.d. 44) -->
            <tr class="border-t">
                <td colspan="2" style="padding: 0;">
                    <table class="table-levy" style="width: 100%; border: none;">
                        <thead>
                            <tr style="background: #fafafa; font-size: 6.8pt;">
                                <th style="width: 16%;">Jenis Pungutan</th>
                                <th style="width: 14%; text-align: right;">Dibayar</th>
                                <th style="width: 14%; text-align: right;">Ditanggung</th>
                                <th style="width: 14%; text-align: right;">Ditunda</th>
                                <th style="width: 14%; text-align: right;">Tidak Dipungut</th>
                                <th style="width: 14%; text-align: right;">Dibebaskan</th>
                                <th style="width: 14%; text-align: right;">Telah Dilunasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>37. BM</td>
                                <td class="text-right">{{ number_format($bmBayar, 2) }}</td>
                                <td class="text-right">0.00</td>
                                <td class="text-right">0.00</td>
                                <td class="text-right">0.00</td>
                                <td class="text-right">0.00</td>
                                <td class="text-right">0.00</td>
                            </tr>
                            <tr>
                                <td>38. BM KITE</td>
                                <td class="text-right">0.00</td>
                                <td class="text-right">0.00</td>
                                <td class="text-right">0.00</td>
                                <td class="text-right">0.00</td>
                                <td class="text-right">0.00</td>
                                <td class="text-right">0.00</td>
                            </tr>
                            <tr>
                                <td>39. BMT</td>
                                <td class="text-right">0.00</td>
                                <td class="text-right">0.00</td>
                                <td class="text-right">0.00</td>
                                <td class="text-right">0.00</td>
                                <td class="text-right">0.00</td>
                                <td class="text-right">0.00</td>
                            </tr>
                            <tr>
                                <td>40. Cukai</td>
                                <td class="text-right">0.00</td>
                                <td class="text-right">0.00</td>
                                <td class="text-right">0.00</td>
                                <td class="text-right">0.00</td>
                                <td class="text-right">0.00</td>
                                <td class="text-right">0.00</td>
                            </tr>
                            <tr>
                                <td>41. PPN</td>
                                <td class="text-right">{{ number_format($ppnBayar, 2) }}</td>
                                <td class="text-right">0.00</td>
                                <td class="text-right">0.00</td>
                                <td class="text-right">0.00</td>
                                <td class="text-right">0.00</td>
                                <td class="text-right">0.00</td>
                            </tr>
                            <tr>
                                <td>42. PPnBM</td>
                                <td class="text-right">0.00</td>
                                <td class="text-right">0.00</td>
                                <td class="text-right">0.00</td>
                                <td class="text-right">0.00</td>
                                <td class="text-right">0.00</td>
                                <td class="text-right">0.00</td>
                            </tr>
                            <tr>
                                <td>43. PPh</td>
                                <td class="text-right">{{ number_format($pphBayar, 2) }}</td>
                                <td class="text-right">0.00</td>
                                <td class="text-right">0.00</td>
                                <td class="text-right">0.00</td>
                                <td class="text-right">0.00</td>
                                <td class="text-right">0.00</td>
                            </tr>
                            <tr class="font-bold" style="background: #fafafa;">
                                <td>44. TOTAL</td>
                                <td class="text-right">{{ number_format($totalBayar, 2) }}</td>
                                <td class="text-right">0.00</td>
                                <td class="text-right">0.00</td>
                                <td class="text-right">0.00</td>
                                <td class="text-right">0.00</td>
                                <td class="text-right">0.00</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>

            <!-- Bagian Bawah: F. Pernyataan Importir/PPJK & E. Pembayaran/Jaminan -->
            <tr class="border-t">
                <td style="width: 58%; padding: 4px; vertical-align: top;" class="border-r">
                    <strong>F. Dengan ini saya menyatakan :</strong><br>
                    <div style="font-size: 6.8pt; line-height: 1.15; margin-top: 2px;">
                        a. Bertanggung jawab atas kebenaran hal - hal yang diberitahukan dalam dokumen ini dan keabsahan dokumen pelengkap pabean yang menjadi dasar buatan dokumen ini ; dan<br>
                        b. Sanggup menyiapkan dan menyerahkan barang impor untuk diperiksa, serta menyaksikan pemeriksaan fisik. Dalam hal saya tidak memenuhi ketentuan ini dalam jangka waktu yang ditetapkan maka saya menguasakannya kepada pengusaha Tempat Penimbunan Sementara tempat pemeriksaan atas risiko dan biaya saya.
                    </div>
                    <div style="margin-top: 10px; text-align: center; font-size: 7.5pt;">
                        JAKARTA, {{ $tglAju }}<br>
                        Importir/PPJK<br><br><br>
                        <strong>JOHANA MEGAWATI</strong><br>
                        DIREKTUR
                    </div>
                </td>
                <td style="width: 42%; padding: 4px; vertical-align: top;">
                    <strong>E. UNTUK PEMBAYARAN DAN JAMINAN :</strong><br>
                    <table style="width: 100%; font-size: 7pt; margin-top: 3px;">
                        <tr>
                            <td>a. Pembayaran</td>
                            <td>: <span class="check-box">1</span> 1. Bank &nbsp;&nbsp; 2. Post &nbsp;&nbsp; 3. Kantor Pabean</td>
                        </tr>
                        <tr>
                            <td>b. Jaminan</td>
                            <td>: 1. Tunai &nbsp;&nbsp; 2. Bank Garansi<br>
                                &nbsp;&nbsp;3. Customs Bond &nbsp;&nbsp; 4. Lainnya
                            </td>
                        </tr>
                    </table>
                    <table class="border-all" style="width: 100%; margin-top: 6px; font-size: 6.8pt;">
                        <tr class="border-b" style="background: #fafafa;">
                            <th style="width: 10%;"></th>
                            <th style="width: 50%; text-align: center;">Nomor</th>
                            <th style="width: 40%; text-align: center;">Tanggal</th>
                        </tr>
                        <tr class="border-b">
                            <td>a.</td>
                            <td style="height: 14px;"></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>b.</td>
                            <td style="height: 14px;"></td>
                            <td></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Footer PIB Resmi -->
        <div style="display: flex; justify-content: space-between; font-size: 6.5pt; color: #444; margin-top: 2px;">
            <span>Rangkap ke-1/2/3/4 untuk Importir/Kantor Pabean/BPS/BI</span>
            <span>BC 2.0 - CEISA 4.0 DJBC</span>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- HALAMAN 2 S.D. N: LEMBAR LANJUTAN BARANG (PIB)           -->
    <!-- ======================================================== -->
    @if($itemCount > 1)
        @foreach($itemChunks as $chunkIndex => $chunkItems)
            @php
                $currentPageNumber = 2 + $chunkIndex;
                $isLastItemPage = $loop->last;
            @endphp
            <div class="page page-break">
                <!-- Header Lembar Lanjutan -->
                <table style="margin-bottom: 2px;">
                    <tr>
                        <td style="width: 15%;"></td>
                        <td style="width: 70%; text-align: center;">
                            <div style="font-size: 7.5pt; font-weight: bold;">LEMBAR LANJUTAN</div>
                            <div class="header-title" style="margin-bottom: 0;">PEMBERITAHUAN IMPOR BARANG (PIB)</div>
                        </td>
                        <td style="width: 15%; text-align: right;">
                            <span class="doc-type-badge">2.0</span>
                        </td>
                    </tr>
                </table>

                <table class="border-all" style="margin-bottom: 3px;">
                    <tr>
                        <td style="width: 16%;" class="font-bold">Kantor Pabean</td>
                        <td style="width: 1%;">:</td>
                        <td style="width: 43%;">{{ $kantorPabean }}</td>
                        <td style="width: 15%; text-align: center;" class="border-l border-r font-bold">{{ $kodeKantor }}</td>
                        <td style="width: 25%; text-align: right;">Halaman ke-{{ $currentPageNumber }} dari {{ $totalPages }}</td>
                    </tr>
                    <tr class="border-t">
                        <td class="font-bold">Nomor Pengajuan</td>
                        <td>:</td>
                        <td class="font-bold" style="font-family: monospace;">{{ $nomorAju }}</td>
                        <td class="border-l font-bold">Tanggal Pengajuan</td>
                        <td style="text-align: right;">: {{ $tglAju }}</td>
                    </tr>
                    <tr class="border-t">
                        <td class="font-bold">Nomor Pendaftaran</td>
                        <td>:</td>
                        <td class="font-bold">{{ $nomorDaftar }}</td>
                        <td class="border-l font-bold">Tanggal Pendaftaran</td>
                        <td style="text-align: right;">: {{ $tglDaftar }}</td>
                    </tr>
                </table>

                <!-- Tabel Lembar Lanjutan Barang Kolom 31 s.d. 36 -->
                <table class="table-items" style="width: 100%; border: 1px solid #000;">
                    <thead>
                        <tr>
                            <th style="width: 3%; text-align: center;">31.<br>No</th>
                            <th style="width: 33%;">32. - Pos Tarif HS<br>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Uraian Jenis Barang, Merek, Tipe, Spek Wajib<br>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Negara Asal Barang
                            </th>
                            <th style="width: 18%;">33. Keterangan<br>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Fasilitas & No. Urut<br>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Persyaratan & No. Urut
                            </th>
                            <th style="width: 14%;">34. Tarif dan<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fasilitas</th>
                            <th style="width: 16%;">35. - Jumlah dan Jenis Satuan Barang<br>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Berat Bersih (Kg)<br>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Jumlah dan Jenis Kemasan
                            </th>
                            <th style="width: 16%;">36. - Nilai Pabean<br>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Jenis<br>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Nilai yg Ditambahkan<br>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- Jatuh Tempo
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($chunkItems as $itIdx => $it)
                            @php
                                $globalIdx = ($chunkIndex * $chunkSize) + $itIdx + 1;
                                $hasAcfta = ($it->tarif_bm_persen == 0 || str_contains(strtolower($it->hs_legal_basis ?? ''), 'acfta') || true);
                            @endphp
                            <tr>
                                <td style="text-align: center; font-weight: bold;">{{ $globalIdx }}</td>
                                <td>
                                    <strong>Pos Tarif : {{ str_replace('.', '', $it->kode_hs) }}</strong> &nbsp;&nbsp; Kode Brg : -<br>
                                    <strong>Uraian :</strong> {{ $it->uraian_barang }}<br>
                                    <span style="font-size: 6.5pt; color: #333;">
                                        Merk: -, Tipe: -, Ukuran: -, Spesifikasi lain: -,<br>
                                        Kondisi Brg : BARU<br>
                                        Negara : CHINA (CN)
                                    </span>
                                </td>
                                <td>
                                    @if($hasAcfta)
                                        <div style="font-size: 6.5pt;">
                                            - PREFERENSI TARIF IMPORTASI ASEAN-CHINA (ACFTA) (4)
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    BM {{ $it->tarif_bm_persen }}% 100% BYR<br>
                                    PPH {{ $it->tarif_pph_persen }}% 100% BYR<br>
                                    PPN {{ $it->tarif_ppn_persen }}% 100% BYR
                                </td>
                                <td>
                                    {{ number_format($it->jumlah_kemasan, 4) }}<br>
                                    {{ strtoupper($it->jenis_kemasan) }} ({{ strtoupper($it->jenis_kemasan) }})<br>
                                    <strong>{{ number_format($it->berat_netto, 4) }}</strong><br>
                                    <span style="font-size: 6.5pt;">{{ number_format($it->jumlah_kemasan, 2) }} {{ strtoupper($it->jenis_kemasan) }}</span>
                                </td>
                                <td>
                                    - {{ number_format($it->nilai_fob, 2) }}<br>
                                    - NTR<br>
                                    - 0
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Tanda Tangan PPJK di akhir lembar lanjutan -->
                <div style="margin-top: 15px; text-align: right; font-size: 7.5pt; padding-right: 25px;">
                    JAKARTA, {{ $tglAju }}<br>
                    Importir/PPJK<br><br><br>
                    <strong>JOHANA MEGAWATI</strong>
                </div>
            </div>
        @endforeach
    @endif

    <!-- ======================================================== -->
    <!-- HALAMAN TERAKHIR: LEMBAR LANJUTAN DOKUMEN LAMPIRAN       -->
    <!-- ======================================================== -->
    <div class="page page-break">
        <!-- Header Lembar Lanjutan Dokumen -->
        <table style="margin-bottom: 2px;">
            <tr>
                <td style="width: 15%;"></td>
                <td style="width: 70%; text-align: center;">
                    <div style="font-size: 7.5pt; font-weight: bold;">LEMBAR LANJUTAN DOKUMEN DAN PEMENUHAN PERSYARATAN/FASILITAS</div>
                    <div class="header-title" style="margin-bottom: 0;">PEMBERITAHUAN IMPOR BARANG (PIB)</div>
                </td>
                <td style="width: 15%; text-align: right;">
                    <span class="doc-type-badge">BC 2.0</span>
                </td>
            </tr>
        </table>

        <table class="border-all" style="margin-bottom: 4px;">
            <tr>
                <td style="width: 16%;" class="font-bold">Kantor Pabean</td>
                <td style="width: 1%;">:</td>
                <td style="width: 43%;">{{ $kantorPabean }}</td>
                <td style="width: 15%; text-align: center;" class="border-l border-r font-bold">{{ $kodeKantor }}</td>
                <td style="width: 25%; text-align: right;">Halaman ke-{{ $totalPages }} dari {{ $totalPages }}</td>
            </tr>
            <tr class="border-t">
                <td class="font-bold">Nomor Pengajuan</td>
                <td>:</td>
                <td class="font-bold" style="font-family: monospace;">{{ $nomorAju }}</td>
                <td class="border-l font-bold">Tanggal Pengajuan</td>
                <td style="text-align: right;">: {{ $tglAju }}</td>
            </tr>
            <tr class="border-t">
                <td class="font-bold">Nomor</td>
                <td>:</td>
                <td class="font-bold">{{ $nomorDaftar }}</td>
                <td class="border-l font-bold">Tanggal</td>
                <td style="text-align: right;">: {{ $tglDaftar }}</td>
            </tr>
        </table>

        <!-- Tabel Dokumen Lampiran Pabean -->
        <table class="border-all" style="width: 100%; border: 1px solid #000; margin-top: 4px;">
            <thead>
                <tr style="background: #f5f5f5; font-size: 7.2pt;">
                    <th style="width: 5%; text-align: center;" class="border-r border-b">No</th>
                    <th style="width: 12%; text-align: center;" class="border-r border-b">Kode Dokumen</th>
                    <th style="width: 38%;" class="border-r border-b">Nama Dokumen</th>
                    <th style="width: 33%;" class="border-r border-b">Nomor dan Tanggal Dokumen</th>
                    <th style="width: 12%; text-align: center;" class="border-b">Dilampirkan</th>
                </tr>
            </thead>
            <tbody>
                <!-- 1. Bill of Lading (705) -->
                <tr class="border-b">
                    <td style="text-align: center;" class="border-r">1</td>
                    <td style="text-align: center; font-weight: bold;" class="border-r">705</td>
                    <td class="border-r">B/L</td>
                    <td class="border-r font-bold">
                        No. {{ $shipment->nomor_bl_awb ?? 'EGLV140401309432' }}<br>
                        <span style="font-weight: normal; font-size: 6.8pt;">Tgl. 28-05-2024</span>
                    </td>
                    <td style="text-align: center; font-weight: bold;">YA / TIDAK</td>
                </tr>

                <!-- 2. Invoice (380) -->
                <tr class="border-b">
                    <td style="text-align: center;" class="border-r">2</td>
                    <td style="text-align: center; font-weight: bold;" class="border-r">380</td>
                    <td class="border-r">INVOICE</td>
                    <td class="border-r font-bold">
                        No. {{ $shipment->invoice_number ?? 'HZ20240519' }}<br>
                        <span style="font-weight: normal; font-size: 6.8pt;">Tgl. 19-05-2024</span>
                    </td>
                    <td style="text-align: center; font-weight: bold;">YA / TIDAK</td>
                </tr>

                <!-- 3. Packing List (217) -->
                <tr class="border-b">
                    <td style="text-align: center;" class="border-r">3</td>
                    <td style="text-align: center; font-weight: bold;" class="border-r">217</td>
                    <td class="border-r">PACKING LIST</td>
                    <td class="border-r font-bold">
                        No. {{ $shipment->packing_list_number ?? 'HZ20240519' }}<br>
                        <span style="font-weight: normal; font-size: 6.8pt;">Tgl. 19-05-2024</span>
                    </td>
                    <td style="text-align: center; font-weight: bold;">YA / TIDAK</td>
                </tr>

                <!-- 4. Electronic Certificate of Origin / Form E ACFTA (860) -->
                <tr class="border-b">
                    <td style="text-align: center;" class="border-r">4</td>
                    <td style="text-align: center; font-weight: bold;" class="border-r">860</td>
                    <td class="border-r">ELECTRONIC CERTIFICATE OF ORIGIN (E-CO)</td>
                    <td class="border-r font-bold">
                        No. E24MA3BXKGH80073<br>
                        <span style="font-weight: normal; font-size: 6.8pt;">Tgl. 21-06-2024</span>
                    </td>
                    <td style="text-align: center; font-weight: bold;">YA / TIDAK</td>
                </tr>

                <!-- Dokumen Lartas / Tambahan jika ada di database -->
                @foreach($shipment->itemCustomsDocuments as $dIdx => $cDoc)
                    <tr class="border-b">
                        <td style="text-align: center;" class="border-r">{{ $dIdx + 5 }}</td>
                        <td style="text-align: center; font-weight: bold;" class="border-r">{{ $cDoc->kode_dokumen }}</td>
                        <td class="border-r">{{ $cDoc->nama_dokumen }}</td>
                        <td class="border-r font-bold">
                            No. {{ $cDoc->nomor_dokumen ?: 'DALAM PROSES' }}<br>
                            <span style="font-weight: normal; font-size: 6.8pt;">Tgl. {{ $cDoc->tanggal_dokumen ? $cDoc->tanggal_dokumen->format('d-m-Y') : '-' }}</span>
                        </td>
                        <td style="text-align: center; font-weight: bold;">YA / TIDAK</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Tanda Tangan PPJK di Halaman Lampiran -->
        <div style="margin-top: 40px; text-align: right; font-size: 7.5pt; padding-right: 30px;">
            JAKARTA, {{ $tglAju }}<br>
            Importir/PPJK<br><br><br><br>
            <strong>JOHANA MEGAWATI</strong>
        </div>
    </div>

</body>
</html>
