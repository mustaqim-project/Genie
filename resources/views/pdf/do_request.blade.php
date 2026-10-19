<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Permohonan Rilis DO - {{ $shipment->bl_number ?? $shipment->shipment_number }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5pt;
            color: #1e293b;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 16px;
        }
        .company-title {
            font-size: 14pt;
            font-weight: bold;
            color: #0f172a;
            margin: 0 0 2px 0;
        }
        .company-subtitle {
            font-size: 8.5pt;
            color: #475569;
            margin: 0;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
        }
        .data-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 10px;
        }
        .data-table td.label {
            background-color: #f8fafc;
            font-weight: bold;
            width: 32%;
        }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="background: #1e293b; color: white; padding: 10px 15px; margin-bottom: 15px; border-radius: 6px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <strong style="font-size: 11pt;">Surat Permohonan Rilis Delivery Order (DO)</strong>
            <span style="font-size: 9pt; color: #94a3b8; margin-left: 10px;">Shipment: {{ $shipment->shipment_number }}</span>
        </div>
        <div>
            <button onclick="window.print()" style="background: #f59e0b; color: white; border: none; padding: 6px 14px; border-radius: 4px; font-weight: bold; cursor: pointer;">
                Cetak Surat / Simpan PDF
            </button>
        </div>
    </div>

    <!-- Kop Surat Resmi -->
    <table class="header-table">
        <tr>
            <td>
                <div class="company-title">PT AGUNG TRANS SOLUSINDO</div>
                <div class="company-subtitle">
                    International Freight Forwarder & Customs Brokerage (PPJK)<br>
                    Jl. Enggano Raya No. 45, Tanjung Priok, Jakarta Utara 14310 | Telp: (021) 4390-8821<br>
                    Email: do-desk@agungtrans.co.id | NPWP: 03.882.910.4-041.000
                </div>
            </td>
        </tr>
    </table>

    <table style="width: 100%; margin-bottom: 15px;">
        <tr>
            <td width="60%">
                <strong>Nomor Surat:</strong> ATS/DO-REQ/{{ date('Ym') }}/{{ substr($shipment->id, 0, 8) }}<br>
                <strong>Lampiran:</strong> Copy Bill of Lading & Bukti Pembayaran Resmi<br>
                <strong>Perihal:</strong> <u>Permohonan Penerbitan dan Rilis Delivery Order (DO) Online</u>
            </td>
            <td width="40%" style="text-align: right; vertical-align: top;">
                Jakarta, {{ now()->translatedFormat('d F Y') }}
            </td>
        </tr>
    </table>

    <div style="margin-bottom: 15px;">
        Kepada Yth.<br>
        <strong>Bagian Customer Service / Import DO Release</strong><br>
        <strong>{{ $shipment->shippingVendor->name ?? 'PT MAERSK INDONESIA' }}</strong><br>
        {{ $shipment->shippingVendor->address ?? 'Wisma 46 Kota BNI Lt. 35, Jl. Jend. Sudirman Kav. 1, Jakarta Pusat' }}<br>
        Email: {{ $shipment->shippingVendor->email ?? 'id.import@maersk.com' }}
    </div>

    <p>Dengan hormat,</p>
    <p>
        Sehubungan dengan kedatangan sarana pengangkut kargo impor di Pelabuhan Tanjung Priok, bersama ini kami bertindak atas kuasa pemilik barang (Consignee) mengajukan permohonan penerbitan dan rilis berkas <strong>Delivery Order (DO)</strong> atas pengiriman dengan rincian data sebagai berikut:
    </p>

    <table class="data-table">
        <tr>
            <td class="label">Nomor Bill of Lading (B/L)</td>
            <td class="font-bold">{{ $shipment->bl_number ?? 'MAEU-982182910' }}</td>
        </tr>
        <tr>
            <td class="label">Nama Kapal & No. Voyage</td>
            <td>{{ $shipment->vessel_name ?? 'CMA CGM TITAN' }} / {{ $shipment->voyage_number ?? '039W' }}</td>
        </tr>
        <tr>
            <td class="label">Nama Consignee (Importir)</td>
            <td class="font-bold">{{ $shipment->client->name ?? 'PT MITRA MAKMUR SENTOSA' }}</td>
        </tr>
        <tr>
            <td class="label">Pelabuhan Muat / Bongkar</td>
            <td>{{ $shipment->port_of_loading }} &rarr; {{ $shipment->port_of_discharge }}</td>
        </tr>
        <tr>
            <td class="label">Perkiraan Waktu Tiba (ETA)</td>
            <td>{{ $shipment->eta ? $shipment->eta->translatedFormat('d F Y') : '-' }}</td>
        </tr>
        <tr>
            <td class="label">Nomor Petikemas / Kontainer</td>
            <td class="font-bold">MSKU-9182740 (1 x 40' High Cube)</td>
        </tr>
        <tr>
            <td class="label">Status Masa Bebas (Free Time)</td>
            <td>7 Hari Bebas Demurrage & Detention (s/d {{ now()->addDays(7)->translatedFormat('d F Y') }})</td>
        </tr>
    </table>

    <p>
        Bersama surat ini kami lampirkan:
    </p>
    <ul style="margin: 4px 0 10px 20px; padding: 0;">
        <li>Salinan (Copy) Bill of Lading (B/L) yang telah di-endorse sah oleh importir.</li>
        <li>Bukti transfer pelunasan biaya lokal pelayaran (Ocean Freight, THC, DO Admin Fee).</li>
        <li>Surat Kuasa Pengurusan DO dari Consignee kepada PT Agung Trans Solusindo.</li>
    </ul>

    <p>
        Mohon berkas Delivery Order (DO) tersebut dapat segera dirilis melalui sistem DO Online / portal pelayaran atau dikirimkan ke alamat email operasional kami: <strong>do-desk@agungtrans.co.id</strong>.
    </p>

    <p>Demikian surat permohonan ini kami sampaikan. Atas kerja sama dan bantuan yang diberikan, kami ucapkan terima kasih.</p>

    <table style="width: 100%; margin-top: 30px;">
        <tr>
            <td width="50%">
                Mengetahui Pemilik Barang (Consignee),<br>
                <strong>{{ $shipment->client->name ?? 'PT MITRA MAKMUR SENTOSA' }}</strong><br><br><br><br>
                ( ___________________________ )<br>
                <small>Import Manager / Authorized Signatory</small>
            </td>
            <td width="50%" class="text-center">
                Hormat kami,<br>
                <strong>PT AGUNG TRANS SOLUSINDO</strong><br><br><br><br>
                ( <strong>RENDI SAPUTRA</strong> )<br>
                <small>Head of Shipping Line & DO Clearance</small>
            </td>
        </tr>
    </table>

</body>
</html>
