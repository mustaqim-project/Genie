<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Penawaran Harga (Quotation) - {{ $quotation->quotation_number ?? 'QUO-202610-0001' }}</title>
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
        .quotation-badge {
            font-size: 15pt;
            font-weight: bold;
            color: #0369a1;
            text-align: right;
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
        .box-info {
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
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .terms-box {
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 7.5pt;
            margin-bottom: 15px;
        }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="background: #1e293b; color: white; padding: 10px 15px; margin-bottom: 15px; border-radius: 6px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <strong style="font-size: 11pt;">Surat Penawaran Harga (Quotation) Forwarder & PPJK</strong>
            <span style="font-size: 9pt; color: #94a3b8; margin-left: 10px;">ID Quotation: {{ $quotation->id }}</span>
        </div>
        <div>
            <button onclick="window.print()" style="background: #0284c7; color: white; border: none; padding: 6px 14px; border-radius: 4px; font-weight: bold; cursor: pointer;">
                Cetak Penawaran / Simpan PDF
            </button>
        </div>
    </div>

    <!-- Kop PT Agung Trans Solusindo -->
    <table class="header-table">
        <tr>
            <td width="60%">
                <div class="company-title">PT AGUNG TRANS SOLUSINDO</div>
                <div class="company-subtitle">
                    Integrated Freight Logistics & Customs Brokerage Specialist<br>
                    Jl. Enggano Raya No. 45, Tanjung Priok, Jakarta Utara 14310<br>
                    Telp: (021) 4390-8821 | Email: sales@agungtrans.co.id | Web: www.agungtrans.co.id
                </div>
            </td>
            <td width="40%">
                <div class="quotation-badge">QUOTATION</div>
                <div style="font-size: 9pt; font-weight: bold; text-align: right; color: #334155; margin-top: 4px;">
                    NO: {{ $quotation->quotation_number }}
                </div>
                <div style="font-size: 8pt; text-align: right; color: #64748b;">
                    Tanggal Terbit: {{ $quotation->issue_date->translatedFormat('d F Y') }}<br>
                    Berlaku Sampai: <strong>{{ $quotation->valid_until ? $quotation->valid_until->translatedFormat('d F Y') : '-' }}</strong>
                </div>
            </td>
        </tr>
    </table>

    <!-- Data Klien & Rincian Pengiriman -->
    <table class="meta-table">
        <tr>
            <td width="50%">
                <div class="box-info">
                    <strong>KEPADA YTH (CLIENT):</strong><br>
                    <span style="font-size: 10pt; font-weight: bold; color: #0f172a;">{{ $quotation->client->name }}</span><br>
                    <strong>Alamat:</strong> {{ $quotation->client->address ?? 'Kawasan Industri MM2100, Cikarang Barat' }}<br>
                    <strong>Attn:</strong> {{ $quotation->client->pic_name ?? 'Import / Procurement Dept' }}<br>
                    <strong>Email / Telp:</strong> {{ $quotation->client->email ?? '-' }} / {{ $quotation->client->phone ?? '-' }}
                </div>
            </td>
            <td width="50%">
                <div class="box-info">
                    <strong>SPESIFIKASI RUTE & LAYANAN:</strong><br>
                    <strong>Rute Pengiriman:</strong> {{ $quotation->origin_port ?? 'Shanghai (CNSHA)' }} &rarr; {{ $quotation->destination_port ?? 'Tanjung Priok (IDTPP)' }}<br>
                    <strong>Tipe Pengiriman:</strong> Ocean Freight FCL (Full Container Load)<br>
                    <strong>Ukuran Petikemas:</strong> 1 x 40' High Cube Container<br>
                    <strong>Komoditas Barang:</strong> Mesin Industri & Elektronik Otomasi
                </div>
            </td>
        </tr>
    </table>

    <p style="margin: 8px 0;">
        Dengan hormat,<br>
        Bersama ini kami dari <strong>PT Agung Trans Solusindo</strong> menyampaikan penawaran harga resmi untuk penanganan jasa pengiriman dan pengurusan kepabeanan impor/ekspor sebagai berikut:
    </p>

    <!-- Tabel Rincian Penawaran Biaya -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="45%">Uraian Jasa / Layanan</th>
                <th width="15%" class="text-center">Satuan</th>
                <th width="15%" class="text-right">Tarif Satuan (IDR)</th>
                <th width="20%" class="text-right">Jumlah Total (IDR)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td><strong>Ocean Freight (Port-to-Port)</strong><br><small>Shanghai to Tanjung Priok Jakarta via Main Line Vessel</small></td>
                <td class="text-center">1 x 40' HC</td>
                <td class="text-right">18,500,000</td>
                <td class="text-right font-bold">18,500,000</td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td><strong>Terminal Handling Charges (THC) Tanjung Priok</strong><br><small>Biaya penanganan kontainer di dermaga terminal</small></td>
                <td class="text-center">1 x 40' HC</td>
                <td class="text-right">2,850,000</td>
                <td class="text-right font-bold">2,850,000</td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td><strong>Delivery Order (DO) Fee & Admin Pelayaran</strong><br><small>Penebusan berkas rilis kontainer resmi</small></td>
                <td class="text-center">Per B/L</td>
                <td class="text-right">750,000</td>
                <td class="text-right font-bold">750,000</td>
            </tr>
            <tr>
                <td class="text-center">4</td>
                <td><strong>Customs Clearance PPJK (BC 2.0 PIB)</strong><br><small>Penyusunan PIB, penjaluran CEISA 4.0, hingga terbit SPPB</small></td>
                <td class="text-center">Per PIB</td>
                <td class="text-right">1,500,000</td>
                <td class="text-right font-bold">1,500,000</td>
            </tr>
            <tr>
                <td class="text-center">5</td>
                <td><strong>Trucking Inland Transport Trailer</strong><br><small>Pelabuhan Tanjung Priok ke Gudang Cikarang (Door Delivery)</small></td>
                <td class="text-center">1 Armada</td>
                <td class="text-right">3,200,000</td>
                <td class="text-right font-bold">3,200,000</td>
            </tr>
            <tr style="background-color: #f8fafc;">
                <td colspan="4" class="font-bold text-right">TOTAL ESTIMASI PENAWARAN (ESTIMATED TOTAL):</td>
                <td class="text-right font-bold" style="font-size: 10pt; color: #0284c7;">
                    Rp {{ number_format($quotation->total_estimate ?? 26800000, 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Syarat & Ketentuan -->
    <div class="terms-box">
        <strong>SYARAT DAN KETENTUAN (TERMS & CONDITIONS):</strong>
        <ol style="margin: 4px 0 0 15px; padding: 0;">
            <li>Penawaran harga di atas <strong>belum termasuk</strong> pungutan resmi Bea Masuk dan Pajak Impor (PPN, PPh) yang ditetapkan oleh Direktorat Jenderal Bea dan Cukai.</li>
            <li>Belum termasuk biaya denda keterlambatan pengembalian kontainer (Demurrage) dan sewa tempat penumpukan (Storage) melebihi batas waktu bebas (*Free Time*) yang diberikan pihak pelayaran/pelabuhan.</li>
            <li>Belum termasuk biaya pemeriksaan fisik (Behandle SPJM), timbang kargo pelabuhan, atau karantina bilamana ditetapkan oleh Pejabat Bea Cukai.</li>
            <li>Masa berlaku penawaran harga ini adalah 30 hari kalender sejak tanggal terbit.</li>
            <li>Termin pembayaran: 14 hari kalender setelah barang sampai di gudang dan invoice diterima.</li>
        </ol>
    </div>

    <!-- Tanda Tangan & Konfirmasi Order -->
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td width="50%" class="text-center" style="border: 1px solid #cbd5e1; padding: 10px;">
                <strong>Diajukan Oleh,</strong><br>
                <strong>PT AGUNG TRANS SOLUSINDO</strong><br><br><br><br>
                ( <strong>BAYU SETIAWAN</strong> )<br>
                <small>Account Manager & Logistics Consultant</small>
            </td>
            <td width="50%" class="text-center" style="border: 1px solid #cbd5e1; padding: 10px; background-color: #fafafa;">
                <strong>Disetujui / Dikonfirmasi Oleh Klien,</strong><br>
                <strong>{{ $quotation->client->name }}</strong><br><br><br><br>
                ( ___________________________ )<br>
                <small>Nama Terang, Jabatan & Cap Perusahaan</small>
            </td>
        </tr>
    </table>

</body>
</html>
