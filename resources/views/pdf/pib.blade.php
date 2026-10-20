<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>BC 2.0 - Pemberitahuan Impor Barang (PIB) - {{ $shipment->ceisaDocument->nomor_aju ?? $shipment->shipment_number }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8pt;
            color: #111;
            line-height: 1.2;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }
        .header h1 {
            font-size: 11pt;
            margin: 0 0 2px 0;
            text-transform: uppercase;
            font-weight: bold;
        }
        .header h2 {
            font-size: 9pt;
            margin: 0 0 2px 0;
            font-weight: bold;
        }
        .header p {
            font-size: 7.5pt;
            margin: 0;
        }
        .meta-box {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            border: 1px solid #000;
            padding: 4px 6px;
            background-color: #fcfcfc;
        }
        .table-pib {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .table-pib td, .table-pib th {
            border: 1px solid #333;
            padding: 3px 5px;
            vertical-align: top;
        }
        .table-pib th {
            background-color: #eee;
            font-weight: bold;
            text-align: left;
            font-size: 7.5pt;
        }
        .section-title {
            background-color: #e2e8f0;
            font-weight: bold;
            font-size: 8pt;
            padding: 3px 5px;
            border: 1px solid #333;
            border-bottom: none;
            text-transform: uppercase;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .barcode {
            font-family: 'Courier New', Courier, monospace;
            font-weight: bold;
            letter-spacing: 2px;
            font-size: 9pt;
            padding: 2px;
            border: 1px dashed #666;
            display: inline-block;
        }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="background: #1e293b; color: white; padding: 10px 15px; margin-bottom: 15px; border-radius: 6px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <strong style="font-size: 11pt;">Pratinjau Dokumen Pabean Resmi BC 2.0 (PIB)</strong>
            <span style="font-size: 9pt; color: #94a3b8; margin-left: 10px;">ID Shipment: {{ $shipment->id }}</span>
        </div>
        <div>
            <button onclick="window.print()" style="background: #3b82f6; color: white; border: none; padding: 6px 14px; border-radius: 4px; font-weight: bold; cursor: pointer;">
                Cetak Dokumen / Simpan PDF
            </button>
        </div>
    </div>

    <!-- Header Resmi DJBC -->
    <div class="header">
        <h1>KEMENTERIAN KEUANGAN REPUBLIK INDONESIA</h1>
        <h2>DIREKTORAT JENDERAL BEA DAN CUKAI</h2>
        <p>PEMBERITAHUAN IMPOR BARANG (PIB) — BC 2.0</p>
    </div>

    <!-- Identifikasi Pengajuan -->
    <table class="table-pib">
        <tr>
            <td width="55%">
                <span class="font-bold">NOMOR PENGAJUAN (NOMOR AJU):</span><br>
                <span class="barcode">{{ $shipment->ceisaDocument->nomor_aju ?? '040300-000001-20261012-000189' }}</span>
            </td>
            <td width="45%">
                <span class="font-bold">KANTOR PABEAN BONGKAR:</span><br>
                {{ $shipment->ceisaDocument->kantor_pabean ?? '040300 - KPU BEA DAN CUKAI TIPE A TANJUNG PRIOK' }}<br>
                <strong>No. Pendaftaran:</strong> {{ $shipment->ceisaDocument->nomor_daftar ?? '-' }} &nbsp;|&nbsp; 
                <strong>Tgl:</strong> {{ $shipment->ceisaDocument->tanggal_daftar ?? '-' }}
            </td>
        </tr>
    </table>

    <!-- Data Importir & PPJK -->
    <div class="section-title">A. IDENTITAS IMPORTIR & PPJK</div>
    <table class="table-pib">
        <tr>
            <td width="50%">
                <strong>1. IDENTITAS IMPORTIR / PEMILIK BARANG:</strong><br>
                <strong>Nama:</strong> {{ $shipment->client->name ?? 'PT MITRA MAKMUR SENTOSA' }}<br>
                <strong>NPWP (16 Digit):</strong> {{ $shipment->client->npwp ?? '01.234.567.8-012.000' }}<br>
                <strong>NIB:</strong> {{ $shipment->client->nib ?? '9120001234567' }} &nbsp;|&nbsp; <strong>API:</strong> API-U<br>
                <strong>Alamat:</strong> {{ $shipment->client->address ?? 'Kawasan Industri MM2100, Cikarang Barat, Bekasi' }}
            </td>
            <td width="50%">
                <strong>2. PENGUSAHA PENGURUSAN JASA KEPABEANAN (PPJK):</strong><br>
                <strong>Nama PPJK:</strong> PT AGUNG TRANS SOLUSINDO<br>
                <strong>NPWP PPJK:</strong> 03.882.910.4-041.000<br>
                <strong>Nomor Pokok PPJK:</strong> S-00812/PPJK/KPU.01/2024<br>
                <strong>Alamat:</strong> Jl. Enggano Raya No. 45, Tanjung Priok, Jakarta Utara
            </td>
        </tr>
    </table>

    <!-- Sarana Pengangkut & Dokumen -->
    <div class="section-title">B. PENGANGKUTAN & PELABUHAN BONGKAR</div>
    <table class="table-pib">
        <tr>
            <td width="50%">
                <strong>3. Sarana Pengangkut:</strong><br>
                Kapal: {{ $shipment->vessel_name ?? 'CMA CGM TITAN' }}<br>
                Voyage: {{ $shipment->voyage_number ?? '039W' }}<br>
                Bendera: {{ $shipment->vessel_flag ?? 'ID / Asing' }}
            </td>
            <td width="50%">
                <strong>4. Pelabuhan & Tempat Penimbunan (TPS):</strong><br>
                Muat: {{ $shipment->port_of_loading ?? 'CNSHA (SHANGHAI, CHINA)' }}<br>
                Bongkar: {{ $shipment->port_of_discharge ?? 'IDTPP (TANJUNG PRIOK, JAKARTA)' }}<br>
                TPS: {{ $shipment->ceisaDocument->kode_tps ?? 'TPS MULTI TERMINAL INDONESIA' }}
            </td>
        </tr>
    </table>

    <!-- Dokumen Pelengkap Pabean & Perizinan Lartas -->
    <div class="section-title">C. DOKUMEN PELENGKAP PABEAN & PERIZINAN LARTAS (LAMPIRAN WAJIB)</div>
    @php
        $dokumenRef = [
            '380' => 'Invoice (Commercial Invoice)',
            '705' => 'Bill of Lading (B/L)',
            '740' => 'Air Waybill (AWB)',
            '217' => 'Packing List',
            '861' => 'Laporan Surveyor (LS Kemendag)',
            '860' => 'Persetujuan Impor (PI Kemendag)',
            '810' => 'Sertifikat Standar / SNI Wajib',
            '853' => 'Sertifikat Standar SDPPI / POSTEL',
            '852' => 'Surat Keterangan Impor (SKI BPOM)',
            '831' => 'Sertifikat Karantina / Fitosanitari',
            '880' => 'Dokumen V-Legal (KLHK)',
            '872' => 'Eksportir Terdaftar (ET) Sawit',
            '911' => 'Surat Rekomendasi Teknis Kementerian',
        ];
        $payloadDocs = $shipment->ceisaDocument?->raw_payload['dokumen'] ?? null;
    @endphp
    <table class="table-pib">
        <thead>
            <tr>
                <th width="8%" class="text-center">Seri</th>
                <th width="12%">Kode Dok.</th>
                <th width="35%">Jenis / Nama Dokumen</th>
                <th width="25%">Nomor Dokumen</th>
                <th width="20%">Tanggal Dokumen</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($payloadDocs))
                @foreach($payloadDocs as $pdoc)
                <tr>
                    <td class="text-center">{{ $pdoc['seriDokumen'] ?? '-' }}</td>
                    <td class="font-bold">{{ $pdoc['kodeDokumen'] ?? '-' }}</td>
                    <td>{{ $dokumenRef[$pdoc['kodeDokumen'] ?? ''] ?? ($pdoc['namaDokumen'] ?? 'Dokumen Pelengkap Pabean') }}</td>
                    <td class="font-bold">{{ $pdoc['nomorDokumen'] ?? '-' }}</td>
                    <td>{{ $pdoc['tanggalDokumen'] ?? '-' }}</td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td class="text-center">1</td>
                    <td class="font-bold">380</td>
                    <td>Invoice (Commercial Invoice)</td>
                    <td class="font-bold">{{ $shipment->invoice_number ?? 'INV/2026/09/SH-88' }}</td>
                    <td>{{ date('Y-m-d') }}</td>
                </tr>
                <tr>
                    <td class="text-center">2</td>
                    <td class="font-bold">705</td>
                    <td>Bill of Lading (B/L)</td>
                    <td class="font-bold">{{ $shipment->bl_number ?? 'MAEU-982182910' }}</td>
                    <td>{{ date('Y-m-d') }}</td>
                </tr>
                <tr>
                    <td class="text-center">3</td>
                    <td class="font-bold">217</td>
                    <td>Packing List</td>
                    <td class="font-bold">{{ $shipment->packing_list_number ?? 'PL/2026/09/SH-88' }}</td>
                    <td>{{ date('Y-m-d') }}</td>
                </tr>
                @foreach($shipment->itemCustomsDocuments as $lidx => $ldoc)
                <tr>
                    <td class="text-center">{{ $lidx + 4 }}</td>
                    <td class="font-bold">{{ $ldoc->kode_dokumen }}</td>
                    <td>{{ $ldoc->nama_dokumen }} ({{ $ldoc->instansi_penerbit ?? 'Instansi Terkait' }})</td>
                    <td class="font-bold">{{ $ldoc->nomor_dokumen ?: 'DALAM PROSES' }}</td>
                    <td>{{ $ldoc->tanggal_dokumen ? $ldoc->tanggal_dokumen->format('Y-m-d') : '-' }}</td>
                </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <!-- Data Nilai Pabean -->
    <div class="section-title">D. DATA NILAI TRANSAKSI PABEAN</div>
    <table class="table-pib">
        <tr>
            <td width="25%"><strong>Valuta:</strong> {{ $shipment->currency ?? 'USD' }}</td>
            <td width="25%"><strong>Nilai CIF:</strong> {{ number_format($shipment->total_cif ?? 28400.00, 2) }}</td>
            <td width="25%"><strong>NDPBM (Kurs KMK):</strong> Rp {{ number_format($shipment->ceisaDocument->ndbm_kurs ?? 16250, 0, ',', '.') }}</td>
            <td width="25%"><strong>Nilai Pabean (IDR):</strong> Rp {{ number_format(($shipment->total_cif ?? 28400) * ($shipment->ceisaDocument->ndbm_kurs ?? 16250), 0, ',', '.') }}</td>
        </tr>
    </table>

    <!-- Rincian Barang Pabean -->
    <div class="section-title">E. RINCIAN BARANG PABEAN & POS TARIF</div>
    <table class="table-pib">
        <thead>
            <tr>
                <th width="4%" class="text-center">No</th>
                <th width="15%">Pos Tarif / HS Code</th>
                <th width="35%">Uraian Barang, Merk, Tipe, Spek</th>
                <th width="12%">Negara Asal</th>
                <th width="12%" class="text-right">Jumlah & Berat</th>
                <th width="22%" class="text-right">Nilai Pabean (CIF {{ $shipment->currency ?? 'USD' }})</th>
            </tr>
        </thead>
        <tbody>
            @forelse($shipment->items as $idx => $item)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td class="font-bold">{{ $item->hs_code }}</td>
                <td>
                    {{ $item->item_name }}<br>
                    <small style="color: #555;">{{ $item->description }}</small>
                    @if($item->customsDocuments->isNotEmpty())
                        <div style="font-size: 8px; color: #1e3a8a; margin-top: 2px;">
                            <strong>Lampiran Izin:</strong>
                            @foreach($item->customsDocuments as $idoc)
                                [{{ $idoc->kode_dokumen }}] {{ $idoc->nomor_dokumen ?: 'Proses' }}@if(!$loop->last), @endif
                            @endforeach
                        </div>
                    @endif
                </td>
                <td>{{ $item->country_of_origin ?? 'China' }}</td>
                <td class="text-right">{{ number_format($item->quantity, 0) }} {{ $item->package_unit }}<br><small>{{ number_format($item->gross_weight, 2) }} kg</small></td>
                <td class="text-right font-bold">{{ number_format($item->cif_amount, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td class="text-center">1</td>
                <td class="font-bold">8471.30.20</td>
                <td>INDUSTRIAL CONTROL PANEL UNIT MODEL X-200<br><small>Spare parts machinery automate</small></td>
                <td>China</td>
                <td class="text-right">50 PKG<br><small>4,850.00 kg</small></td>
                <td class="text-right font-bold">28,400.00</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Perhitungan Pungutan Pabean -->
    <div class="section-title">F. PERHITUNGAN PUNGUTAN BEA MASUK & PAJAK IMPOR (PDRI)</div>
    <table class="table-pib">
        <thead>
            <tr>
                <th width="35%">Jenis Pungutan</th>
                <th width="15%" class="text-center">Tarif</th>
                <th width="25%" class="text-right">Dasar Pengenaan (Rp)</th>
                <th width="25%" class="text-right">Dibayar (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $nilaiPabeanIdr = ($shipment->total_cif ?? 28400) * 16250;
                $bm = $shipment->ceisaDocument->bea_masuk ?? ($nilaiPabeanIdr * 0.05);
                $nilaiImpor = $nilaiPabeanIdr + $bm;
                $ppn = $shipment->ceisaDocument->ppn ?? ($nilaiImpor * 0.11);
                $pph = $shipment->ceisaDocument->pph ?? ($nilaiImpor * 0.025);
                $totalPungutan = $bm + $ppn + $pph;
            @endphp
            <tr>
                <td>1. Bea Masuk (BM)</td>
                <td class="text-center">5.0%</td>
                <td class="text-right">{{ number_format($nilaiPabeanIdr, 0, ',', '.') }}</td>
                <td class="text-right font-bold">{{ number_format($bm, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>2. Pajak Pertambahan Nilai (PPN)</td>
                <td class="text-center">11.0%</td>
                <td class="text-right">{{ number_format($nilaiImpor, 0, ',', '.') }}</td>
                <td class="text-right font-bold">{{ number_format($ppn, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>3. Pajak Penghasilan Pasal 22 (PPh Impor)</td>
                <td class="text-center">2.5% (API)</td>
                <td class="text-right">{{ number_format($nilaiImpor, 0, ',', '.') }}</td>
                <td class="text-right font-bold">{{ number_format($pph, 0, ',', '.') }}</td>
            </tr>
            <tr style="background-color: #f1f5f9;">
                <td colspan="3" class="font-bold text-right">TOTAL PUNGUTAN DISETOR (BILLING MPN G3):</td>
                <td class="text-right font-bold" style="color: #0f172a; font-size: 8.5pt;">Rp {{ number_format($totalPungutan, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Billing Info & Pengesahan -->
    <table class="table-pib">
        <tr>
            <td width="55%">
                <strong>KODE BILLING PABEAN (SIMPONI / MPN G3):</strong><br>
                <span style="font-size: 11pt; font-weight: bold; color: #1e3a8a; letter-spacing: 1px;">
                    {{ $shipment->billing_code ?? ($shipment->ceisaDocument->billing_code ?? '028192819201928') }}
                </span><br>
                <small>Batas Waktu Bayar: {{ now()->addDays(3)->format('d/m/Y 23:59') }} WIB</small><br>
                <small>Status Respon: <strong>{{ $shipment->ceisaDocument->status ?? 'BILLING_ISSUED' }}</strong></small>
            </td>
            <td width="45%" class="text-center">
                Jakarta, {{ now()->translatedFormat('d F Y') }}<br>
                <strong>PPJK Kuasa Importir,</strong><br>
                <strong>PT AGUNG TRANS SOLUSINDO</strong><br><br><br>
                ( <u>AHMAD RIFAI</u> )<br>
                <small>Ahli Kepabeanan No. S-1192/BC/2021</small>
            </td>
        </tr>
    </table>

</body>
</html>
