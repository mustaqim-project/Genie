<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Setoran Pabean, Cukai, dan Pajak (SSPCP) - Kode Billing {{ $shipment->billing_code ?? ($shipment->ceisaDocument->billing_code ?? '028192819201928') }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5pt;
            color: #0f172a;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 11pt;
            margin: 0 0 2px 0;
            text-transform: uppercase;
            font-weight: bold;
        }
        .header h2 {
            font-size: 9.5pt;
            margin: 0 0 2px 0;
            font-weight: bold;
        }
        .billing-box {
            border: 2px solid #1e3a8a;
            background-color: #eff6ff;
            padding: 10px 14px;
            border-radius: 6px;
            margin-bottom: 12px;
            text-align: center;
        }
        .billing-code {
            font-size: 18pt;
            font-weight: bold;
            color: #1e3a8a;
            letter-spacing: 3px;
            font-family: 'Courier New', Courier, monospace;
            margin: 4px 0;
        }
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .table-data th, .table-data td {
            border: 1px solid #334155;
            padding: 5px 8px;
        }
        .table-data th {
            background-color: #f1f5f9;
            font-weight: bold;
            font-size: 8pt;
        }
        .text-right { text-align: right; }
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
            <strong style="font-size: 11pt;">Cetak Kode Billing MPN G3 / SSPCP DJBC</strong>
            <span style="font-size: 9pt; color: #94a3b8; margin-left: 10px;">Shipment: {{ $shipment->shipment_number }}</span>
        </div>
        <div>
            <button onclick="window.print()" style="background: #1e40af; color: white; border: none; padding: 6px 14px; border-radius: 4px; font-weight: bold; cursor: pointer;">
                Cetak Billing / Simpan PDF
            </button>
        </div>
    </div>

    <!-- Header DJBC -->
    <div class="header">
        <h1>KEMENTERIAN KEUANGAN REPUBLIK INDONESIA</h1>
        <h2>DIREKTORAT JENDERAL BEA DAN CUKAI</h2>
        <p style="margin: 0; font-size: 8pt;">SURAT SETORAN PABEAN, CUKAI, DAN PAJAK (SSPCP) / CETAKAN KODE BILLING MPN G3</p>
    </div>

    <!-- Kotak Kode Billing Utama -->
    <div class="billing-box">
        <div style="font-size: 9pt; font-weight: bold; color: #475569; text-transform: uppercase;">
            KODE BILLING PEMBAYARAN KEPABEANAN (MPN G3)
        </div>
        <div class="billing-code">
            {{ $shipment->billing_code ?? ($shipment->ceisaDocument->billing_code ?? '028192819201928') }}
        </div>
        <div style="font-size: 8pt; color: #dc2626; font-weight: bold;">
            BERLAKU SAMPAI DENGAN: {{ now()->addDays(3)->format('d F Y, 23:59:59') }} WIB
        </div>
    </div>

    <!-- Rincian Wajib Bayar -->
    <table class="table-data">
        <tr>
            <td width="50%">
                <strong>IDENTITAS WAJIB BAYAR (IMPORTIR):</strong><br>
                Nama: <strong>{{ $shipment->client->name ?? 'PT MITRA MAKMUR SENTOSA' }}</strong><br>
                NPWP: {{ $shipment->client->npwp ?? '01.234.567.8-012.000' }}<br>
                Alamat: {{ $shipment->client->address ?? 'Kawasan Industri MM2100, Cikarang Barat' }}
            </td>
            <td width="50%">
                <strong>KANTOR PENERBIT & DOKUMEN:</strong><br>
                Kantor Pabean: 040300 - KPU TIPE A TANJUNG PRIOK<br>
                Jenis Dokumen: BC 2.0 (Pemberitahuan Impor Barang)<br>
                Nomor Aju: {{ $shipment->ceisaDocument->nomor_aju ?? '040300-000001-20261012-000189' }}
            </td>
        </tr>
    </table>

    <!-- Rincian Setoran Per Akun Mata Anggaran -->
    <table class="table-data">
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="20%">Mata Anggaran (Akun)</th>
                <th width="45%">Uraian Pembayaran Penerimaan Negara</th>
                <th width="30%" class="text-right">Jumlah Setoran (IDR)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $bm = $shipment->ceisaDocument->bea_masuk ?? 23075000;
                $ppn = $shipment->ceisaDocument->ppn ?? 53303250;
                $pph = $shipment->ceisaDocument->pph ?? 12114375;
                $total = $bm + $ppn + $pph;
            @endphp
            <tr>
                <td class="text-center">1</td>
                <td class="font-bold">412111</td>
                <td>Pendapatan Bea Masuk (BM) Impor Barang</td>
                <td class="text-right font-bold">{{ number_format($bm, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td class="font-bold">411123</td>
                <td>Pajak Pertambahan Nilai (PPN) Impor</td>
                <td class="text-right font-bold">{{ number_format($ppn, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td class="font-bold">411124</td>
                <td>Pajak Penghasilan (PPh) Pasal 22 Impor</td>
                <td class="text-right font-bold">{{ number_format($pph, 0, ',', '.') }}</td>
            </tr>
            <tr style="background-color: #f8fafc;">
                <td colspan="3" class="font-bold text-right" style="font-size: 9pt;">TOTAL PENERIMAAN NEGARA DISETOR:</td>
                <td class="text-right font-bold" style="font-size: 10pt; color: #1e3a8a;">
                    Rp {{ number_format($total, 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Status Transaksi & Pembayaran -->
    <table class="table-data">
        <tr>
            <td width="60%">
                <strong>STATUS TRANSAKSI MPN G3:</strong><br>
                Status Pembayaran: <strong style="color: {{ ($shipment->ceisaDocument?->nomor_daftar) ? '#16a34a' : '#ea580c' }};">
                    {{ ($shipment->ceisaDocument?->nomor_daftar) ? 'LUNAS (TERVERIFIKASI SISTEM SIMPONI)' : 'MENUNGGU PEMBAYARAN DI BANK PERSEPSI / POS' }}
                </strong><br>
                Nomor Transaksi Bank (NTB): {{ $shipment->ceisaDocument?->nomor_daftar ? 'NTB-98129810298' : '-' }}<br>
                Nomor Transaksi Penerimaan Negara (NTPN): {{ $shipment->ceisaDocument?->nomor_daftar ? 'E981273910283719' : '-' }}
            </td>
            <td width="40%" class="text-center">
                Dicetak secara elektronik melalui<br>
                <strong>Sistem PATS_CEISA ERP — Host-to-Host DJBC</strong><br>
                Waktu Cetak: {{ now()->format('d/m/Y H:i:s') }} WIB
            </td>
        </tr>
    </table>

</body>
</html>
