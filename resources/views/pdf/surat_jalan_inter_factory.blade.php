<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan Transfer Antar Pabrik (Kawasan Berikat) — {{ $shipment->surat_jalan_number ?? $shipment->project_no }}</title>
    <style>
        @page { size: A4 portrait; margin: 15mm; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; line-height: 1.4; color: #1e293b; margin: 0; }
        .header-table { width: 100%; border-bottom: 2px solid #059669; padding-bottom: 10px; margin-bottom: 12px; }
        .company-name { font-size: 18px; font-weight: bold; color: #047857; letter-spacing: -0.5px; }
        .company-sub { font-size: 9px; color: #64748b; line-height: 1.3; }
        .doc-title { text-align: right; }
        .doc-title h1 { margin: 0; font-size: 15px; color: #0f172a; text-transform: uppercase; }
        .doc-title .badge { display: inline-block; background: #d1fae5; color: #047857; padding: 2px 8px; border-radius: 4px; font-weight: bold; font-size: 10px; margin-top: 3px; }
        .meta-table { width: 100%; margin-bottom: 12px; }
        .meta-table td { vertical-align: top; font-size: 11px; }
        .info-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 10px; }
        .info-card h4 { margin: 0 0 4px 0; font-size: 10px; text-transform: uppercase; color: #64748b; }
        .customs-card { background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 6px; padding: 8px 10px; margin-bottom: 12px; }
        .item-table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 15px; }
        .item-table th { background: #064e3b; color: white; padding: 6px 8px; font-size: 10px; text-transform: uppercase; text-align: left; }
        .item-table td { border: 1px solid #cbd5e1; padding: 6px 8px; font-size: 10px; }
        .item-table tr:nth-child(even) { background: #f8fafc; }
        .sign-table { width: 100%; margin-top: 25px; border-collapse: collapse; }
        .sign-table td { width: 25%; text-align: center; vertical-align: top; font-size: 10px; }
        .sign-box { border: 1px dashed #94a3b8; height: 65px; border-radius: 4px; margin: 5px 10px; }
        .footer-note { margin-top: 15px; font-size: 9px; color: #64748b; border-top: 1px dotted #cbd5e1; padding-top: 6px; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td style="width: 55%;">
                <div class="company-name">GENIE BONDED ZONE &amp; LOGISTICS ERP</div>
                <div class="company-sub">
                    PENGELUARAN DOKUMEN PABEAN TRANSFER ANTAR KAWASAN BERIKAT / GUDANG BERIKAT<br>
                    Fasilitas Kepabeanan TPB &amp; Subkontrak Industri Manufaktur<br>
                    Telp: (021) 8934-8800 | Email: bonded@cooca.id
                </div>
            </td>
            <td style="width: 45%;" class="doc-title">
                <h1>SURAT JALAN TRANSFER ANTAR PABRIK</h1>
                <div class="badge">TEMPLATE 3: KAWASAN BERIKAT / TPB (BC 2.7 / BC 4.0)</div>
                <div style="font-size: 12px; font-weight: bold; margin-top: 5px; font-family: monospace;">
                    NO: {{ $shipment->surat_jalan_number ?: 'SJ-TPB-' . date('Ym') . '-' . substr($shipment->id, 0, 6) }}
                </div>
                <div style="font-size: 10px; color: #64748b;">Tanggal: {{ \Carbon\Carbon::parse($shipment->delivery_date ?? now())->translatedFormat('d F Y') }}</div>
            </td>
        </tr>
    </table>

    <table class="meta-table">
        <tr>
            <td style="width: 49%;">
                <div class="info-card">
                    <h4>Pabrik Pengirim (Kawasan Berikat Asal)</h4>
                    <div style="font-size: 12px; font-weight: bold; color: #0f172a;">{{ $shipment->client->name ?? 'PT PENGIRIM KB' }}</div>
                    <div>Alamat Asal: {{ $shipment->client->address ?? 'Kawasan Industri MM2100 Cikarang' }}</div>
                    <div>Izin TPB / Skep: <b>SKEP-KB-8821/WBC.07/2024</b></div>
                </div>
            </td>
            <td style="width: 2%;"></td>
            <td style="width: 49%;">
                <div class="info-card">
                    <h4>Pabrik Penerima (Tujuan Subkontrak / Gudang)</h4>
                    <div style="font-size: 12px; font-weight: bold; color: #047857;">{{ $shipment->destination_address ?? 'Pabrik Mitra Industri / Gudang Cabang' }}</div>
                    <div>Alamat Tujuan: {{ $shipment->destination_address ?? 'Kawasan Industri KIIC Karawang Barat' }}</div>
                    <div>Petugas Penerima: <b>Bagian PPIC / Gudang Bahan Baku</b></div>
                </div>
            </td>
        </tr>
    </table>

    <div class="customs-card">
        <h4 style="margin: 0 0 4px 0; font-size: 10px; text-transform: uppercase; color: #047857;">Dokumen Pengesahan Pabean (CEISA TPB)</h4>
        <table style="width: 100%; font-size: 10px;">
            <tr>
                <td>Jenis Dokumen: <b>BC 2.7 (Pengeluaran Barang Antar TPB)</b></td>
                <td>Nomor Pendaftaran BC: <b>082100-{{ date('Ymd') }}-00918</b></td>
                <td>Segel BC / Timah: <b>{{ $shipment->new_seal_number ?? 'BC-TPB-0081' }}</b></td>
            </tr>
            <tr>
                <td>No. Polisi Truk: <b>{{ $shipment->truck_plate_number ?? 'B 9182 UEX' }}</b></td>
                <td>Supir: <b>{{ $shipment->driver_name ?? 'Supir Pengangkut' }}</b></td>
                <td>Tujuan Pengeluaran: <b>Subkontrak Bahan Baku / Transfer Stock</b></td>
            </tr>
        </table>
    </div>

    <table class="item-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 40%;">Deskripsi Bahan Baku / Komoditas</th>
                <th style="width: 15%; text-align: center;">Nomor Batch / Lot</th>
                <th style="width: 20%; text-align: center;">Kemasan &amp; Jumlah</th>
                <th style="width: 20%; text-align: right;">Berat Netto / Satuan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center; font-weight: bold;">1</td>
                <td>
                    <b>{{ $shipment->commodity_summary ?? 'Raw Material / Komponen Industri Manufaktur' }}</b>
                </td>
                <td style="text-align: center; font-family: monospace;">LOT-{{ date('Ym') }}-81</td>
                <td style="text-align: center; font-weight: bold;">
                    {{ $shipment->total_kemasan ? $shipment->total_kemasan . ' Kemasan' : '12 Pallet (360 Box)' }}
                </td>
                <td style="text-align: right; font-weight: bold;">
                    {{ number_format($shipment->total_netto ?? 8500, 0, ',', '.') }} Kg
                </td>
            </tr>
        </tbody>
    </table>

    @php
        $dtIf = \App\Models\DocumentTemplate::getTemplate('SJ_INTER_FACTORY_DEFAULT', 'traffic');
        $termsIf = $dtIf?->terms_and_conditions ?? '1. Pengeluaran barang antar Kawasan Berikat wajib dilengkapi persetujuan pabean BC 2.7 / BC 4.0 yang telah terbit barcode. 2. Truk pengangkut wajib memiliki izin subkontrak / pergerakan barang berikat dari Kantor Pelayanan Bea dan Cukai. 3. Lembar pelindung pengangkutan wajib dibawa sopir selama perjalanan.';
        $signeeIf = $dtIf?->signee_name ?? \App\Models\SystemSetting::get('surat_jalan_signee_name', 'Bambang Wijaya (Manager Ops Lapangan)');
        $phoneIf = $dtIf?->emergency_phone ?? \App\Models\SystemSetting::get('surat_jalan_emergency_phone', '+62 811-3322-1100');
    @endphp

    <div style="background: #ecfdf5; border: 1px solid #d1fae5; padding: 6px 10px; border-radius: 4px; font-size: 8.5px; color: #065f46; margin-bottom: 8px;">
        <b>KLAUSUL KEPABEANAN ANTAR KAWASAN BERIKAT (BC 2.7 / BC 4.0):</b><br>
        {!! nl2br(e($termsIf)) !!}
        @if($phoneIf)
            <div style="margin-top: 3px; font-weight: bold; color: #047857;">Kontak Pengawalan / Hotline: {{ $phoneIf }}</div>
        @endif
    </div>

    <table class="sign-table">
        <tr>
            <td>
                <div>Pengirim (Gudang Asal)</div>
                <div class="sign-box"></div>
                <div>( {{ $signeeIf }} )</div>
            </td>
            <td>
                <div>Petugas Hanggar Bea Cukai Asal</div>
                <div class="sign-box"></div>
                <div>( ....................... )</div>
                <div style="font-size: 8px; color: #94a3b8;">NIP &amp; Cap Hanggar</div>
            </td>
            <td>
                <div>Pengemudi / Supir Truk</div>
                <div class="sign-box"></div>
                <div>( {{ $shipment->driver_name ?? '.......................' }} )</div>
            </td>
            <td>
                <div>Penerima (Pabrik Tujuan)</div>
                <div class="sign-box"></div>
                <div>( ....................... )</div>
                <div style="font-size: 8px; color: #94a3b8;">Tgl &amp; Cap Terima</div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Barang berada dalam pengawasan pabean Republik Indonesia. Dilarang membuka segel bea cukai tanpa disaksikan petugas hanggar di pabrik tujuan.
    </div>

</body>
</html>
