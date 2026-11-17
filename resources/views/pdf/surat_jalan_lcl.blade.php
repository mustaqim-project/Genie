<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan Kargo LCL / Udara — {{ $shipment->surat_jalan_number ?? $shipment->project_no }}</title>
    <style>
        @page { size: A4 portrait; margin: 15mm; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; line-height: 1.4; color: #1e293b; margin: 0; }
        .header-table { width: 100%; border-bottom: 2px solid #6366f1; padding-bottom: 10px; margin-bottom: 12px; }
        .company-name { font-size: 18px; font-weight: bold; color: #4338ca; letter-spacing: -0.5px; }
        .company-sub { font-size: 9px; color: #64748b; line-height: 1.3; }
        .doc-title { text-align: right; }
        .doc-title h1 { margin: 0; font-size: 16px; color: #0f172a; text-transform: uppercase; }
        .doc-title .badge { display: inline-block; background: #e0e7ff; color: #4338ca; padding: 2px 8px; border-radius: 4px; font-weight: bold; font-size: 10px; margin-top: 3px; }
        .meta-table { width: 100%; margin-bottom: 12px; }
        .meta-table td { vertical-align: top; font-size: 11px; }
        .info-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 10px; }
        .info-card h4 { margin: 0 0 4px 0; font-size: 10px; text-transform: uppercase; color: #64748b; }
        .item-table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 15px; }
        .item-table th { background: #1e1b4b; color: white; padding: 6px 8px; font-size: 10px; text-transform: uppercase; text-align: left; }
        .item-table td { border: 1px solid #cbd5e1; padding: 6px 8px; font-size: 10px; }
        .item-table tr:nth-child(even) { background: #f8fafc; }
        .checklist-table { width: 100%; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px; margin-bottom: 15px; background: #faf5ff; font-size: 10px; }
        .sign-table { width: 100%; margin-top: 20px; border-collapse: collapse; }
        .sign-table td { width: 25%; text-align: center; vertical-align: top; font-size: 10px; }
        .sign-box { border: 1px dashed #94a3b8; height: 65px; border-radius: 4px; margin: 5px 10px; }
        .footer-note { margin-top: 15px; font-size: 9px; color: #64748b; border-top: 1px dotted #cbd5e1; padding-top: 6px; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td style="width: 55%;">
                <div class="company-name">GENIE LOGISTICS &amp; AIR-CARGO ERP</div>
                <div class="company-sub">
                    PT AGUNG TRANS LOGISTIK NUSANTARA (PPJK &amp; AIR-FREIGHT SERVICES)<br>
                    Pergudangan Bandara Kargo Lini 1 Soekarno-Hatta / Juanda<br>
                    Telp: (021) 5591-8822 | Email: aircargo@cooca.id | IATA Reg: 12-8821
                </div>
            </td>
            <td style="width: 45%;" class="doc-title">
                <h1>SURAT JALAN KARGO LCL &amp; UDARA</h1>
                <div class="badge">TEMPLATE 2: GUDANG CFS / AIRPORT $\rightarrow$ GUDANG KLIEN</div>
                <div style="font-size: 12px; font-weight: bold; margin-top: 5px; font-family: monospace;">
                    NO: {{ $shipment->surat_jalan_number ?: 'SJ-LCL-' . date('Ym') . '-' . substr($shipment->id, 0, 6) }}
                </div>
                <div style="font-size: 10px; color: #64748b;">Tanggal: {{ \Carbon\Carbon::parse($shipment->delivery_date ?? now())->translatedFormat('d F Y') }}</div>
            </td>
        </tr>
    </table>

    <table class="meta-table">
        <tr>
            <td style="width: 49%;">
                <div class="info-card">
                    <h4>Penerima / Alamat Bongkar Kargo</h4>
                    <div style="font-size: 12px; font-weight: bold; color: #0f172a;">{{ $shipment->client->name ?? 'KLIEN' }}</div>
                    <div>Alamat: {{ $shipment->destination_address ?? ($shipment->client->address ?? 'Alamat Penerima Kargo') }}</div>
                    <div>Kontak: {{ $shipment->client->pic_name ?? '-' }} ({{ $shipment->client->phone ?? '-' }})</div>
                </div>
            </td>
            <td style="width: 2%;"></td>
            <td style="width: 49%;">
                <div class="info-card">
                    <h4>Armada Angkutan Box / Kargo</h4>
                    <div>Vendor Ekspedisi: <b>{{ $shipment->trucking_vendor_name ?? 'Armada Box ATS' }}</b></div>
                    <div>No. Polisi Armada: <b style="font-family: monospace; font-size: 12px; color: #4338ca;">{{ $shipment->truck_plate_number ?? '-' }}</b></div>
                    <div>Nama Pengemudi: <b>{{ $shipment->driver_name ?? '-' }}</b></div>
                    <div>Lokasi Muat: <b>{{ $shipment->tps_location ?? 'Gudang CFS LCL / Cargo Bandara' }}</b></div>
                </div>
            </td>
        </tr>
    </table>

    <table class="meta-table">
        <tr>
            <td style="width: 100%;">
                <div class="info-card" style="background: #eef2ff; border-color: #c7d2fe;">
                    <h4>Informasi Penerbangan / Stripping CFS</h4>
                    <table style="width: 100%; font-size: 10px;">
                        <tr>
                            <td>No. Master AWB / BL: <b>{{ $shipment->nomor_bl_awb ?? '-' }}</b></td>
                            <td>Flight / Vessel: <b>{{ $shipment->nama_kapal ?? '-' }} {{ $shipment->voyage_no ?? '' }}</b></td>
                            <td>Asal (Origin): <b>{{ $shipment->pelabuhan_muat ?? '-' }}</b></td>
                        </tr>
                        <tr>
                            <td>No. PIB / Dokumen: <b>{{ $shipment->project_no }}</b></td>
                            <td>Tgl Tiba: <b>{{ $shipment->actual_arrival_date ? \Carbon\Carbon::parse($shipment->actual_arrival_date)->format('d/m/Y H:i') : '-' }}</b></td>
                            <td>Status Pabean: <b style="color: #4338ca;">SPPB KARGO LCL/UDARA</b></td>
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
                <th style="width: 35%;">Uraian Barang / Komoditas</th>
                <th style="width: 20%; text-align: center;">Kemasan / Packaging</th>
                <th style="width: 20%; text-align: right;">Berat Bruto (Kg)</th>
                <th style="width: 20%; text-align: center;">Tanda / Marking Koli</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center; font-weight: bold;">1</td>
                <td>
                    <b>{{ $shipment->commodity_summary ?? 'Kargo Barang Impor' }}</b><br>
                    <span style="font-size: 9px; color: #64748b;">Kondisi: Barang LCL sudah selesai stripping dari kontainer induk</span>
                </td>
                <td style="text-align: center; font-weight: bold; font-family: monospace;">
                    {{ $shipment->total_kemasan ? $shipment->total_kemasan . ' ' . ($shipment->jenis_kemasan ?? 'Colli/Cartons') : '48 Cartons on Pallet' }}
                </td>
                <td style="text-align: right; font-weight: bold; font-family: monospace;">
                    {{ number_format($shipment->total_bruto ?? 850, 1, ',', '.') }} Kg
                </td>
                <td style="text-align: center; font-family: monospace;">
                    {{ $shipment->client->code ?? 'ATS' }}-{{ substr($shipment->id, 0, 5) }}
                </td>
            </tr>
        </tbody>
    </table>

    @php
        $dtLcl = \App\Models\DocumentTemplate::getTemplate('SJ_LCL_DEFAULT', 'traffic');
        $termsLcl = $dtLcl?->terms_and_conditions ?? '1. Jumlah koli, kemasan, dan tanda marking kargo wajib dicocokkan dengan delivery note sebelum serah terima kargo. 2. Kargo parsial ditimbang dan dihitung ulang di gudang CFS penimbunan sementara. 3. Lembar stempel penerima menjadi bukti sah penyerahan kargo freight forwarder.';
        $signeeLcl = $dtLcl?->signee_name ?? \App\Models\SystemSetting::get('surat_jalan_signee_name', 'Bambang Wijaya (Manager Ops Lapangan)');
        $phoneLcl = $dtLcl?->emergency_phone ?? \App\Models\SystemSetting::get('surat_jalan_emergency_phone', '+62 811-3322-1100');
    @endphp

    <div style="background: #fffbeb; border: 1px solid #fef3c7; padding: 6px 10px; border-radius: 4px; font-size: 8.5px; color: #92400e; margin-bottom: 8px;">
        <b>SYARAT &amp; KETENTUAN PENGIRIMAN KARGO LCL / UDARA:</b><br>
        {!! nl2br(e($termsLcl)) !!}
        @if($phoneLcl)
            <div style="margin-top: 3px; font-weight: bold; color: #b45309;">Hotline / Emergency Dispatch: {{ $phoneLcl }}</div>
        @endif
    </div>

    <table class="checklist-table">
        <tr>
            <td style="width: 33%;"><b>Checklist Fisik:</b> [ √ ] Kemasan Utuh &amp; Tidak Basah</td>
            <td style="width: 33%;">[ √ ] Label Koli Terbaca Sesuai AWB</td>
            <td style="width: 34%;">[ √ ] Jumlah Koli Lengkap Dihitung</td>
        </tr>
    </table>

    <table class="sign-table">
        <tr>
            <td>
                <div>Pengemudi / Kurir</div>
                <div class="sign-box"></div>
                <div>( {{ $shipment->driver_name ?? '.......................' }} )</div>
            </td>
            <td>
                <div>Petugas Gudang CFS</div>
                <div class="sign-box"></div>
                <div>( {{ $signeeLcl }} )</div>
            </td>
            <td>
                <div>Pemeriksa Security Gate</div>
                <div class="sign-box"></div>
                <div>( ....................... )</div>
            </td>
            <td>
                <div>Penerima Barang Klien</div>
                <div class="sign-box"></div>
                <div>( ....................... )</div>
                <div style="font-size: 8px; color: #94a3b8;">Nama Terang, Tgl &amp; Cap</div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Diterbitkan oleh GENIE System. Lembar 1: Arsip Penagihan Klien, Lembar 2: Tanda Terima Gudang Klien, Lembar 3: Ekspedisi.
    </div>

</body>
</html>
