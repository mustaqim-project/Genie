<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice Penagihan Klien (P19) - {{ $invoice->invoice_number ?? 'INV-202610-0001' }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5pt;
            color: #1e293b;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .company-title {
            font-size: 14pt;
            font-weight: bold;
            color: #0f172a;
            margin: 0 0 2px 0;
        }
        .company-subtitle {
            font-size: 8pt;
            color: #475569;
            margin: 0;
        }
        .invoice-title-box {
            text-align: right;
        }
        .invoice-badge {
            font-size: 16pt;
            font-weight: bold;
            color: #1e40af;
            letter-spacing: 1px;
            margin: 0;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
        }
        .meta-table td {
            vertical-align: top;
            padding: 4px;
        }
        .client-box {
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            padding: 8px 10px;
            border-radius: 4px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .data-table th, .data-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
        }
        .data-table th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: bold;
            font-size: 8pt;
            text-transform: uppercase;
        }
        .category-header {
            background-color: #e2e8f0;
            font-weight: bold;
            color: #1e293b;
            font-size: 8pt;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .total-box {
            width: 45%;
            margin-left: auto;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .total-box td {
            padding: 4px 8px;
        }
        .grand-total {
            background-color: #1e293b;
            color: #ffffff;
            font-size: 10pt;
            font-weight: bold;
        }
        .payment-info {
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            padding: 8px 10px;
            border-radius: 4px;
            font-size: 8pt;
        }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="background: #1e293b; color: white; padding: 10px 15px; margin-bottom: 15px; border-radius: 6px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <strong style="font-size: 11pt;">Invoice Penagihan Klien (P19)</strong>
            <span style="font-size: 9pt; color: #94a3b8; margin-left: 10px;">ID Invoice: {{ $invoice->id }}</span>
        </div>
        <div>
            <button onclick="window.print()" style="background: #2563eb; color: white; border: none; padding: 6px 14px; border-radius: 4px; font-weight: bold; cursor: pointer;">
                Cetak Invoice / Simpan PDF
            </button>
        </div>
    </div>

    <!-- Kop Perusahaan PT Agung Trans Solusindo -->
    <table class="header-table">
        <tr>
            <td width="60%">
                <div class="company-title">PT AGUNG TRANS SOLUSINDO</div>
                <div class="company-subtitle">
                    International Freight Forwarding & Customs Brokerage (PPJK)<br>
                    Jl. Enggano Raya No. 45, Tanjung Priok, Jakarta Utara 14310<br>
                    Telp: (021) 4390-8821 | Email: billing@agungtrans.co.id | NPWP: 03.882.910.4-041.000
                </div>
            </td>
            <td width="40%" class="invoice-title-box">
                <div class="invoice-badge">INVOICE</div>
                <div style="font-size: 9pt; font-weight: bold; color: #334155; margin-top: 4px;">
                    NO: {{ $invoice->invoice_number }}
                </div>
                <div style="font-size: 8pt; color: #64748b;">
                    Tanggal: {{ $invoice->invoice_date->translatedFormat('d F Y') }}<br>
                    Jatuh Tempo: <strong>{{ $invoice->due_date ? $invoice->due_date->translatedFormat('d F Y') : '-' }}</strong>
                </div>
            </td>
        </tr>
    </table>

    <!-- Data Klien & Rujukan Pengiriman -->
    <table class="meta-table">
        <tr>
            <td width="55%">
                <div class="client-box">
                    <strong>TAGIHAN KEPADA (BILL TO):</strong><br>
                    <span style="font-size: 10pt; font-weight: bold; color: #0f172a;">{{ $invoice->client->name }}</span><br>
                    <strong>NPWP:</strong> {{ $invoice->client->npwp ?? '-' }}<br>
                    <strong>Alamat:</strong> {{ $invoice->client->address ?? 'Kawasan Industri MM2100, Cikarang Barat, Bekasi' }}<br>
                    <strong>Attn / PIC:</strong> {{ $invoice->client->pic_name ?? 'Bagian Keuangan & Impor' }} ({{ $invoice->client->phone ?? '-' }})
                </div>
            </td>
            <td width="45%">
                <div class="client-box">
                    <strong>RUJUKAN PENGIRIMAN (SHIPMENT DETAILS):</strong><br>
                    <strong>Job Order:</strong> {{ $invoice->shipment->shipment_number }}<br>
                    <strong>B/L No:</strong> {{ $invoice->shipment->bl_number ?? '-' }}<br>
                    <strong>Kapal / Voyage:</strong> {{ $invoice->shipment->vessel_name ?? '-' }} / {{ $invoice->shipment->voyage_number ?? '-' }}<br>
                    <strong>Pelabuhan:</strong> {{ $invoice->shipment->port_of_loading }} &rarr; {{ $invoice->shipment->port_of_discharge }}<br>
                    <strong>Status Operasional:</strong> {{ $invoice->shipment->status }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Tabel Rincian Biaya -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="55%">Deskripsi Komponen Biaya</th>
                <th width="15%" class="text-center">Kategori</th>
                <th width="25%" class="text-right">Jumlah (IDR)</th>
            </tr>
        </thead>
        <tbody>
            <!-- 1. Biaya Pabean (Talangan Reimbursable) -->
            <tr class="category-header">
                <td colspan="4">A. PUNGUTAN RESMI PABEAN (REIMBURSABLE / TALANGAN BEBAS PPN JASA)</td>
            </tr>
            <tr>
                <td class="text-center">1</td>
                <td>Bea Masuk (BM) Impor — Sesuai Billing MPN G3 No. {{ $invoice->shipment->billing_code ?? '028192819201928' }}</td>
                <td class="text-center"><small>Pabean</small></td>
                <td class="text-right">{{ number_format($invoice->shipment->ceisaDocument->bea_masuk ?? 23075000, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td>PPN Impor (11%) — Sesuai Bukti Penerimaan Negara (BPN SSPCP)</td>
                <td class="text-center"><small>Pabean</small></td>
                <td class="text-right">{{ number_format($invoice->shipment->ceisaDocument->ppn ?? 53303250, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td>PPh Pasal 22 Impor (2.5% API)</td>
                <td class="text-center"><small>Pabean</small></td>
                <td class="text-right">{{ number_format($invoice->shipment->ceisaDocument->pph ?? 12114375, 0, ',', '.') }}</td>
            </tr>

            <!-- 2. Biaya Pelayaran & Terminal -->
            <tr class="category-header">
                <td colspan="4">B. BIAYA PELAYARAN & TERMINAL PELABUHAN (SHIPPING LINE & PORT CHARGES)</td>
            </tr>
            <tr>
                <td class="text-center">4</td>
                <td>Penebusan Delivery Order (DO), THC, & Port Charges Pelayaran</td>
                <td class="text-center"><small>Pelayaran</small></td>
                <td class="text-right">{{ number_format($invoice->biaya_pelayaran_idr, 0, ',', '.') }}</td>
            </tr>

            <!-- 3. Jasa PPJK & Transportasi -->
            <tr class="category-header">
                <td colspan="4">C. JASA PENGURUSAN PPJK & TRUCKING (FORWARDING SERVICES)</td>
            </tr>
            <tr>
                <td class="text-center">5</td>
                <td>Customs Clearance, EDI Pabean & Penanganan Dokumen PPJK</td>
                <td class="text-center"><small>Jasa PPJK</small></td>
                <td class="text-right">{{ number_format($invoice->jasa_ppjk_idr, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">6</td>
                <td>Trucking Inland Transport Trailer (Pelabuhan Priok &rarr; Gudang Klien)</td>
                <td class="text-center"><small>Transport</small></td>
                <td class="text-right">{{ number_format($invoice->biaya_trucking_idr, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Ringkasan Total & Perpajakan -->
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td width="55%" style="vertical-align: top;">
                <div class="payment-info">
                    <strong>PETUNJUK PEMBAYARAN (BANK TRANSFER):</strong><br>
                    Pembayaran mohon ditransfer penuh ke rekening resmi PT Agung Trans Solusindo:<br><br>
                    <strong>BANK MANDIRI (KCP TANJUNG PRIOK)</strong><br>
                    No. Rekening: <strong>120-00-1928374-1</strong> (IDR)<br>
                    Atas Nama: <strong>PT AGUNG TRANS SOLUSINDO</strong><br><br>
                    <strong>BANK CENTRAL ASIA (KCU ENGGANO)</strong><br>
                    No. Rekening: <strong>084-3928172</strong> (IDR)<br>
                    Atas Nama: <strong>PT AGUNG TRANS SOLUSINDO</strong><br><br>
                    <small><em>*Mohon cantumkan No. Invoice [{{ $invoice->invoice_number }}] pada berita transfer dan kirimkan bukti ke finance@agungtrans.co.id.</em></small>
                </div>
            </td>
            <td width="45%" style="vertical-align: top;">
                <table class="total-box">
                    <tr>
                        <td class="font-bold">Total Talangan Pabean (A):</td>
                        <td class="text-right font-bold">Rp {{ number_format($invoice->talangan_pabean_idr, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="font-bold">Total Biaya Pelayaran (B):</td>
                        <td class="text-right font-bold">Rp {{ number_format($invoice->biaya_pelayaran_idr, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="font-bold">Jasa PPJK & Trucking (C):</td>
                        <td class="text-right font-bold">Rp {{ number_format($invoice->jasa_ppjk_idr + $invoice->biaya_trucking_idr, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="font-bold">PPN Jasa (1.1% DPP Nilai Lain):</td>
                        <td class="text-right">Rp {{ number_format($invoice->ppn_jasa_idr, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="grand-total">
                        <td style="padding: 8px;">TOTAL TAGIHAN:</td>
                        <td class="text-right" style="padding: 8px;">Rp {{ number_format($invoice->total_tagihan_idr, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Tanda Tangan Resmi -->
    <table style="width: 100%; margin-top: 25px;">
        <tr>
            <td width="60%">
                <div style="font-size: 8pt; color: #475569;">
                    Status Pembayaran: <strong style="color: {{ $invoice->status === 'PAID' ? '#16a34a' : '#d97706' }};">{{ $invoice->status }}</strong><br>
                    Invoice ini dicetak secara sah melalui Sistem PATS_CEISA ERP.
                </div>
            </td>
            <td width="40%" class="text-center">
                Jakarta, {{ $invoice->invoice_date->translatedFormat('d F Y') }}<br>
                <strong>Finance & Billing Department</strong><br>
                <strong>PT AGUNG TRANS SOLUSINDO</strong><br><br><br><br>
                ( <u>DEWI KURNIAWATI, S.E.</u> )<br>
                <small>Finance Manager</small>
            </td>
        </tr>
    </table>

</body>
</html>
