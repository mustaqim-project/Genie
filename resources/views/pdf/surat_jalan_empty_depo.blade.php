<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan Pengembalian Kontainer Kosong (Empty Return) — {{ $shipment->surat_jalan_number ?? $shipment->project_no }}</title>
    <style>
        @page { size: A4 portrait; margin: 15mm; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; line-height: 1.4; color: #1e293b; margin: 0; }
        .header-table { width: 100%; border-bottom: 2px solid #ea580c; padding-bottom: 10px; margin-bottom: 12px; }
        .company-name { font-size: 18px; font-weight: bold; color: #c2410c; letter-spacing: -0.5px; }
        .company-sub { font-size: 9px; color: #64748b; line-height: 1.3; }
        .doc-title { text-align: right; }
        .doc-title h1 { margin: 0; font-size: 15px; color: #0f172a; text-transform: uppercase; }
        .doc-title .badge { display: inline-block; background: #ffedd5; color: #c2410c; padding: 2px 8px; border-radius: 4px; font-weight: bold; font-size: 10px; margin-top: 3px; }
        .meta-table { width: 100%; margin-bottom: 12px; }
        .meta-table td { vertical-align: top; font-size: 11px; }
        .info-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 10px; }
        .info-card h4 { margin: 0 0 4px 0; font-size: 10px; text-transform: uppercase; color: #64748b; }
        .item-table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 15px; }
        .item-table th { background: #431407; color: white; padding: 6px 8px; font-size: 10px; text-transform: uppercase; text-align: left; }
        .item-table td { border: 1px solid #cbd5e1; padding: 6px 8px; font-size: 10px; }
        .item-table tr:nth-child(even) { background: #f8fafc; }
        .eir-table { width: 100%; border: 1px solid #fed7aa; background: #fff7ed; padding: 8px; border-radius: 6px; margin-bottom: 15px; font-size: 10px; }
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
                <div class="company-name">GENIE CONTAINER DEPOT &amp; TRUCKING ERP</div>
                <div class="company-sub">
                    PENGEMBALIAN PETI KEMAS KOSONG (EQUIPMENT INTERCHANGE RECEIPT - EIR RETURN)<br>
                    Kantor Operasional Depo: Pelabuhan Tanjung Priok / Tanjung Perak<br>
                    Telp: (021) 4390-1122 | Email: empty-return@cooca.id
                </div>
            </td>
            <td style="width: 45%;" class="doc-title">
                <h1>SURAT JALAN PENGEMBALIAN EMPTY KONTAINER</h1>
                <div class="badge">TEMPLATE 4: GUDANG BONGKAR $\rightarrow$ DEPO PELAYARAN</div>
                <div style="font-size: 12px; font-weight: bold; margin-top: 5px; font-family: monospace;">
                    NO: {{ $shipment->surat_jalan_number ?: 'SJ-EMPTY-' . date('Ym') . '-' . substr($shipment->id, 0, 6) }}
                </div>
                <div style="font-size: 10px; color: #64748b;">Tanggal: {{ \Carbon\Carbon::parse($shipment->delivery_date ?? now())->translatedFormat('d F Y') }}</div>
            </td>
        </tr>
    </table>

    <table class="meta-table">
        <tr>
            <td style="width: 49%;">
                <div class="info-card">
                    <h4>Pelayaran Pemilik Kontainer (Principal Line)</h4>
                    <div style="font-size: 12px; font-weight: bold; color: #0f172a;">{{ $shipment->shippingVendor->name ?? ($shipment->nama_kapal ?? 'OCEAN NETWORK EXPRESS (ONE)') }}</div>
                    <div>No. Delivery Order (D/O): <b style="font-family: monospace;">{{ $shipment->nomor_do ?? 'DO-ONE-882109' }}</b></div>
                    <div>Batas Waktu Detention / Return: <b style="color: #dc2626;">{{ $shipment->do_expired_at ? \Carbon\Carbon::parse($shipment->do_expired_at)->format('d F Y') : 'H+7 Setelah Sandar' }}</b></div>
                </div>
            </td>
            <td style="width: 2%;"></td>
            <td style="width: 49%;">
                <div class="info-card">
                    <h4>Depo Kontainer Kosong Tujuan</h4>
                    <div>Nama Depo: <b style="font-size: 12px; color: #c2410c;">{{ $shipment->tps_location ?? 'DEPO DWIPA / MALUKU / KBN MARUNDA' }}</b></div>
                    <div>Alamat Depo: Jl. Cakung Cilincing Raya No. 45, Jakarta Utara / Perak Barat</div>
                    <div>Armada Truk Pengangkut: <b>{{ $shipment->truck_plate_number ?? '-' }}</b> ({{ $shipment->driver_name ?? '-' }})</div>
                </div>
            </td>
        </tr>
    </table>

    <table class="item-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 35%;">Nomor Kontainer</th>
                <th style="width: 20%; text-align: center;">Ukuran / Tipe</th>
                <th style="width: 20%; text-align: center;">Kondisi Kontainer</th>
                <th style="width: 20%; text-align: center;">Status Sapu / Cuci</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center; font-weight: bold;">1</td>
                <td>
                    <b style="font-size: 13px; font-family: monospace;">{{ $shipment->nomor_kontainer ?: 'TCLU-8821092' }}</b><br>
                    <span style="font-size: 9px; color: #64748b;">Ref B/L: {{ $shipment->nomor_bl_awb ?? '-' }}</span>
                </td>
                <td style="text-align: center; font-weight: bold;">{{ $shipment->jenis_kemasan ?? '40HC (High Cube)' }}</td>
                <td style="text-align: center; color: #15803d; font-weight: bold;">BAIK / LAYAK LAUT (CARGO WORTHY)</td>
                <td style="text-align: center;">BERSIH / SUDAH DISAPU</td>
            </tr>
        </tbody>
    </table>

    @php
        $dtEd = \App\Models\DocumentTemplate::getTemplate('SJ_EMPTY_DEPO_DEFAULT', 'traffic');
        $termsEd = $dtEd?->terms_and_conditions ?? '1. Kontainer kosong wajib dikembalikan ke depo pelayaran dalam kondisi bersih, kering, dan bebas bau/residu. 2. Petugas depo pelayaran wajib menerbitkan bukti EIR (Equipment Interchange Receipt) penerimaan empty. 3. Kerusakan fisik pada dinding/lantai kontainer menjadi tanggung jawab sesuai ketentuan repair pelayaran.';
        $signeeEd = $dtEd?->signee_name ?? \App\Models\SystemSetting::get('surat_jalan_signee_name', 'Bambang Wijaya (Manager Ops Lapangan)');
        $phoneEd = $dtEd?->emergency_phone ?? \App\Models\SystemSetting::get('surat_jalan_emergency_phone', '+62 811-3322-1100');
    @endphp

    <div style="background: #faf5ff; border: 1px solid #f3e8ff; padding: 6px 10px; border-radius: 4px; font-size: 8.5px; color: #6b21a8; margin-bottom: 8px;">
        <b>KETENTUAN PENGEMBALIAN EMPTY DEPO PELAYARAN (EIR RETURN):</b><br>
        {!! nl2br(e($termsEd)) !!}
        @if($phoneEd)
            <div style="margin-top: 3px; font-weight: bold; color: #7e22ce;">Hotline Dispatch Depo: {{ $phoneEd }}</div>
        @endif
    </div>

    <table class="eir-table">
        <tr>
            <td style="width: 50%;">
                <b>Checklist Pemeriksaan Fisik Surveyor Depo:</b><br>
                [ √ ] Dinding Luar &amp; Lantai Kayu Rapi Tanpa Lubang<br>
                [ √ ] Pintu &amp; Karet Seal Menutup Rapat<br>
                [ √ ] Bebas dari Bau Bahan Kimia Berbahaya
            </td>
            <td style="width: 50%;">
                <b>Keterangan Depo Pelayaran:</b><br>
                Kontainer kosong telah diterima kembali di area penumpukan depo pelayaran dalam kondisi aman untuk diterbitkan bukti EIR Masuk Depo.
            </td>
        </tr>
    </table>

    <table class="sign-table">
        <tr>
            <td>
                <div>Pengemudi Truk Trailer</div>
                <div class="sign-box"></div>
                <div>( {{ $shipment->driver_name ?? '.......................' }} )</div>
            </td>
            <td>
                <div>Petugas Gudang Pabrik</div>
                <div class="sign-box"></div>
                <div>( {{ $signeeEd }} )</div>
            </td>
            <td>
                <div>Gate Masuk Depo Empty</div>
                <div class="sign-box"></div>
                <div>( ....................... )</div>
            </td>
            <td>
                <div>Surveyor / Petugas Depo</div>
                <div class="sign-box"></div>
                <div>( ....................... )</div>
                <div style="font-size: 8px; color: #94a3b8;">Tgl &amp; Stempel Depo</div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Surat jalan ini sebagai bukti sah pengembalian kontainer kosong untuk klaim pengembalian jaminan kontainer (Container Deposit Refund) di kantor pelayaran.
    </div>

</body>
</html>
