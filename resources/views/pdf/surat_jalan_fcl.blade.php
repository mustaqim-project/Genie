<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan Pengiriman Kontainer FCL — {{ $shipment->surat_jalan_number ?? $shipment->project_no }}</title>
    <style>
        @page { size: A4 portrait; margin: 15mm; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; line-height: 1.4; color: #1e293b; margin: 0; }
        .header-table { width: 100%; border-bottom: 2px solid #0284c7; padding-bottom: 10px; margin-bottom: 12px; }
        .company-name { font-size: 18px; font-weight: bold; color: #0284c7; letter-spacing: -0.5px; }
        .company-sub { font-size: 9px; color: #64748b; line-height: 1.3; }
        .doc-title { text-align: right; }
        .doc-title h1 { margin: 0; font-size: 16px; color: #0f172a; text-transform: uppercase; }
        .doc-title .badge { display: inline-block; background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px; font-weight: bold; font-size: 10px; margin-top: 3px; }
        .meta-table { width: 100%; margin-bottom: 12px; }
        .meta-table td { vertical-align: top; font-size: 11px; }
        .info-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 10px; }
        .info-card h4 { margin: 0 0 4px 0; font-size: 10px; text-transform: uppercase; color: #64748b; }
        .item-table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 15px; }
        .item-table th { background: #0f172a; color: white; padding: 6px 8px; font-size: 10px; text-transform: uppercase; text-align: left; }
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
                <div class="company-name">GENIE LOGISTICS &amp; CUSTOMS ERP</div>
                <div class="company-sub">
                    PT AGUNG TRANS LOGISTIK NUSANTARA (PPJK &amp; FREIGHT FORWARDING)<br>
                    Kantor Operasional Pelabuhan: Jl. Pelabuhan Raya No. 88, Tanjung Perak / Priok<br>
                    Telp: (021) 4390-8821 | Email: traffic@cooca.id | NIB/PPJK: 010220038891
                </div>
            </td>
            <td style="width: 45%;" class="doc-title">
                <h1>SURAT JALAN PENGELUARAN KONTAINER (FCL)</h1>
                <div class="badge">TEMPLATE 1: PELABUHAN $\rightarrow$ GUDANG KLIEN</div>
                <div style="font-size: 12px; font-weight: bold; margin-top: 5px; font-family: monospace;">
                    NO: {{ $shipment->surat_jalan_number ?: 'SJ-FCL-' . date('Ym') . '-' . substr($shipment->id, 0, 6) }}
                </div>
                <div style="font-size: 10px; color: #64748b;">Tanggal: {{ \Carbon\Carbon::parse($shipment->delivery_date ?? now())->translatedFormat('d F Y') }}</div>
            </td>
        </tr>
    </table>

    <table class="meta-table">
        <tr>
            <td style="width: 49%;">
                <div class="info-card">
                    <h4>Penerima / Gudang Tujuan Pengiriman</h4>
                    <div style="font-size: 12px; font-weight: bold; color: #0f172a;">{{ $shipment->client->name ?? 'KLIEN' }}</div>
                    <div>Alamat: {{ $shipment->destination_address ?? ($shipment->client->address ?? 'Gudang Pabrik Klien') }}</div>
                    <div>Kontak/PIC: {{ $shipment->client->pic_name ?? '-' }} ({{ $shipment->client->phone ?? '-' }})</div>
                </div>
            </td>
            <td style="width: 2%;"></td>
            <td style="width: 49%;">
                <div class="info-card">
                    <h4>Armada Trucking &amp; Data Pengemudi</h4>
                    <div>Vendor Ekspedisi: <b>{{ $shipment->trucking_vendor_name ?? 'Armada Internal ATS' }}</b></div>
                    <div>No. Polisi Truk: <b style="font-family: monospace; font-size: 12px; color: #0284c7;">{{ $shipment->truck_plate_number ?? '-' }}</b></div>
                    <div>Nama Pengemudi / Supir: <b>{{ $shipment->driver_name ?? '-' }}</b></div>
                    <div>Lokasi Muat (Gate Out): <b>{{ $shipment->tps_location ?? 'Terminal Peti Kemas (TPS)' }}</b></div>
                </div>
            </td>
        </tr>
    </table>

    <table class="meta-table">
        <tr>
            <td style="width: 100%;">
                <div class="info-card" style="background: #f0fdf4; border-color: #bbf7d0;">
                    <h4>Referensi Dokumen Kepabeanan &amp; Pelayaran</h4>
                    <table style="width: 100%; font-size: 10px;">
                        <tr>
                            <td>No. Project: <b>{{ $shipment->project_no }}</b></td>
                            <td>No. B/L: <b>{{ $shipment->nomor_bl_awb ?? '-' }}</b></td>
                            <td>Pelayaran: <b>{{ $shipment->shippingVendor->name ?? ($shipment->nama_kapal ?? '-') }}</b></td>
                        </tr>
                        <tr>
                            <td>Kapal / Voyage: <b>{{ $shipment->nama_kapal ?? '-' }} / {{ $shipment->voyage_no ?? '-' }}</b></td>
                            <td>Pelabuhan Bongkar: <b>{{ $shipment->pelabuhan_bongkar ?? 'Tanjung Perak/Priok' }}</b></td>
                            <td>Respon Bea Cukai: <b style="color: #15803d;">{{ $shipment->customs_channel ? 'JALUR ' . $shipment->customs_channel : 'SPPB / HIJAU' }}</b></td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <table class="item-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 30%;">Nomor Kontainer / Peti Kemas</th>
                <th style="width: 15%;">Ukuran / Tipe</th>
                <th style="width: 25%;">Nomor Segel (Seal)</th>
                <th style="width: 25%;">Komoditas &amp; Muatan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center; font-weight: bold;">1</td>
                <td>
                    <b style="font-size: 12px; font-family: monospace;">{{ $shipment->nomor_kontainer ?: ($shipment->total_kontainer ? $shipment->total_kontainer . ' Kontainer' : 'TCLU-8821092') }}</b>
                </td>
                <td>{{ $shipment->jenis_kemasan ?? '40 Feet High Cube (40HC)' }}</td>
                <td>
                    Pelayaran: <b>{{ $shipment->old_seal_number ?? 'ONE-99821' }}</b><br>
                    Bea Cukai: <b>{{ $shipment->new_seal_number ?? 'BC-SEAL-8821' }}</b>
                </td>
                <td>
                    <b>{{ $shipment->commodity_summary ?? 'Barang Impor Sesuai Dokumen PIB/BL' }}</b><br>
                    <span style="color: #64748b;">Bruto: {{ number_format($shipment->total_bruto ?? 24500, 0, ',', '.') }} Kg</span>
                </td>
            </tr>
        </tbody>
    </table>

    @php
        $dtFcl = \App\Models\DocumentTemplate::getTemplate('SJ_FCL_DEFAULT', 'traffic');
        $termsFcl = $dtFcl?->terms_and_conditions ?? \App\Models\SystemSetting::get('surat_jalan_terms', '1. Segel kontainer/koli wajib dalam keadaan utuh saat serah terima. 2. Penerima wajib membubuhkan stempel resmi, tanda tangan, nama jelas & jam terima barang pada lembar putih. 3. Klaim kerusakan/kekurangan kargo harus dicatat langsung di lembar surat jalan sebelum truk meninggalkan lokasi.');
        $signeeFcl = $dtFcl?->signee_name ?? \App\Models\SystemSetting::get('surat_jalan_signee_name', 'Bambang Wijaya (Manager Ops Lapangan)');
        $phoneFcl = $dtFcl?->emergency_phone ?? \App\Models\SystemSetting::get('surat_jalan_emergency_phone', '+62 811-3322-1100');
    @endphp

    <div style="background: #fffbeb; border: 1px solid #fef3c7; padding: 6px 10px; border-radius: 4px; font-size: 8.5px; color: #92400e; margin-bottom: 8px;">
        <b>SYARAT &amp; KETENTUAN PENGIRIMAN (SOP LOGISTIK):</b><br>
        {!! nl2br(e($termsFcl)) !!}
        @if($phoneFcl)
            <div style="margin-top: 3px; font-weight: bold; color: #b45309;">Hotline / Emergency Dispatch: {{ $phoneFcl }}</div>
        @endif
    </div>

    <table class="sign-table">
        <tr>
            <td>
                <div>Pengemudi / Supir</div>
                <div class="sign-box"></div>
                <div>( {{ $shipment->driver_name ?? '.......................' }} )</div>
            </td>
            <td>
                <div>Petugas Traffic / Lapangan</div>
                <div class="sign-box"></div>
                <div>( {{ $signeeFcl }} )</div>
            </td>
            <td>
                <div>Petugas Gate Out TPS</div>
                <div class="sign-box"></div>
                <div>( ....................... )</div>
            </td>
            <td>
                <div>Penerima Gudang Klien</div>
                <div class="sign-box"></div>
                <div>( ....................... )</div>
                <div style="font-size: 8px; color: #94a3b8;">Tgl &amp; Cap Gudang</div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Dokumen ini diterbitkan secara otomatis oleh Sistem GENIE Logistics ERP. Tembusan: 1. Penerima Gudang, 2. Ekspedisi Trucking, 3. Arsip Pabean PPJK, 4. Penagihan Invoice Finance.
    </div>

</body>
</html>
