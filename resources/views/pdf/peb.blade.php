<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>BC 3.0 - Pemberitahuan Ekspor Barang (PEB) - {{ $shipment->ceisaDocument->nomor_aju ?? $shipment->shipment_number }}</title>
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
        .table-peb {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .table-peb td, .table-peb th {
            border: 1px solid #333;
            padding: 3px 5px;
            vertical-align: top;
        }
        .table-peb th {
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
            <strong style="font-size: 11pt;">Pratinjau Dokumen Pabean Resmi BC 3.0 (PEB)</strong>
            <span style="font-size: 9pt; color: #94a3b8; margin-left: 10px;">ID Shipment: {{ $shipment->id }}</span>
        </div>
        <div>
            <button onclick="window.print()" style="background: #10b981; color: white; border: none; padding: 6px 14px; border-radius: 4px; font-weight: bold; cursor: pointer;">
                Cetak Dokumen / Simpan PDF
            </button>
        </div>
    </div>

    <!-- Header Resmi DJBC -->
    <div class="header">
        <h1>KEMENTERIAN KEUANGAN REPUBLIK INDONESIA</h1>
        <h2>DIREKTORAT JENDERAL BEA DAN CUKAI</h2>
        <p>PEMBERITAHUAN EKSPOR BARANG (PEB) — BC 3.0</p>
    </div>

    <!-- Identifikasi Pengajuan -->
    <table class="table-peb">
        <tr>
            <td width="55%">
                <span class="font-bold">NOMOR PENGAJUAN (NOMOR AJU):</span><br>
                <span class="barcode">{{ $shipment->ceisaDocument->nomor_aju ?? '040300-000003-20261012-000094' }}</span>
            </td>
            <td width="45%">
                <span class="font-bold">KANTOR PABEAN PEMUATAN:</span><br>
                {{ $shipment->ceisaDocument->kantor_pabean ?? '040300 - KPU BEA DAN CUKAI TIPE A TANJUNG PRIOK' }}<br>
                <strong>No. Pendaftaran NPE:</strong> {{ $shipment->ceisaDocument->nomor_daftar ?? '-' }} &nbsp;|&nbsp; 
                <strong>Tgl NPE:</strong> {{ $shipment->ceisaDocument->tanggal_daftar ?? '-' }}
            </td>
        </tr>
    </table>

    <!-- Data Eksportir & PPJK -->
    <div class="section-title">A. IDENTITAS EKSPORTIR, PPJK & PEMBELI</div>
    <table class="table-peb">
        <tr>
            <td width="33%">
                <strong>1. EKSPORTIR:</strong><br>
                <strong>Nama:</strong> {{ $shipment->client->name ?? 'PT AGRO NUSANTARA JAYA' }}<br>
                <strong>NPWP:</strong> {{ $shipment->client->npwp ?? '02.441.229.1-019.000' }}<br>
                <strong>NIB:</strong> {{ $shipment->client->nib ?? '9120309988771' }}<br>
                <strong>Alamat:</strong> Jl. Raya Narogong Km 14, Cileungsi, Bogor
            </td>
            <td width="33%">
                <strong>2. PPJK EKSPOR:</strong><br>
                <strong>Nama:</strong> PT AGUNG TRANS SOLUSINDO<br>
                <strong>NPWP:</strong> 03.882.910.4-041.000<br>
                <strong>No. Izin PPJK:</strong> S-00812/PPJK/KPU.01/2024<br>
                <strong>Alamat:</strong> Tanjung Priok, Jakarta Utara
            </td>
            <td width="34%">
                <strong>3. PEMBELI / BUYER LUAR NEGERI:</strong><br>
                <strong>Nama:</strong> GLOBAL COMMODITIES PTE LTD<br>
                <strong>Negara:</strong> SG (SINGAPORE)<br>
                <strong>Alamat:</strong> 12 Marina Boulevard, Marina Bay Financial Centre, Singapore
            </td>
        </tr>
    </table>

    <!-- Pengangkutan & Pelabuhan -->
    <div class="section-title">B. PENGANGKUTAN & PELABUHAN EKSPOR</div>
    <table class="table-peb">
        <tr>
            <td width="50%">
                <strong>4. Sarana Pengangkut:</strong><br>
                Kapal: {{ $shipment->vessel_name ?? 'EVER GIVEN' }}<br>
                Voyage: {{ $shipment->voyage_number ?? '1044-012E' }}<br>
                Bendera: {{ $shipment->vessel_flag ?? 'PA (Panama)' }}
            </td>
            <td width="50%">
                <strong>5. Pelabuhan:</strong><br>
                Muat: {{ $shipment->port_of_loading ?? 'IDTPP (TANJUNG PRIOK, JAKARTA)' }}<br>
                Bongkar: {{ $shipment->port_of_discharge ?? 'SGSIN (SINGAPORE)' }}<br>
                Negara Tujuan: {{ $shipment->country_destination ?? 'SG (SINGAPORE)' }}
            </td>
        </tr>
    </table>

    <!-- Dokumen Pelengkap Pabean & Perizinan Ekspor Lartas -->
    <div class="section-title">C. DOKUMEN PELENGKAP PABEAN & PERIZINAN EKSPOR LARTAS (LAMPIRAN WAJIB)</div>
    @php
        $dokumenRef = [
            '380' => 'Invoice (Commercial Invoice)',
            '705' => 'Bill of Lading (B/L)',
            '740' => 'Air Waybill (AWB)',
            '217' => 'Packing List',
            '861' => 'Laporan Surveyor Ekspor (LS Kemendag)',
            '860' => 'Persetujuan Ekspor (PE Kemendag)',
            '810' => 'Sertifikat Standar / Mutu Ekspor',
            '853' => 'Sertifikat Standar SDPPI / POSTEL',
            '831' => 'Sertifikat Fitosanitari / Karantina (Barantin)',
            '880' => 'Dokumen V-Legal (KLHK)',
            '872' => 'Eksportir Terdaftar (ET) Sawit Kemendag',
            '911' => 'Surat Rekomendasi Teknis Kementerian',
        ];
        $payloadDocs = $shipment->ceisaDocument?->raw_payload['dokumen'] ?? null;
    @endphp
    <table class="table-peb">
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
                    <td>{{ $dokumenRef[$pdoc['kodeDokumen'] ?? ''] ?? ($pdoc['namaDokumen'] ?? 'Dokumen Pelengkap Ekspor') }}</td>
                    <td class="font-bold">{{ $pdoc['nomorDokumen'] ?? '-' }}</td>
                    <td>{{ $pdoc['tanggalDokumen'] ?? '-' }}</td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td class="text-center">1</td>
                    <td class="font-bold">380</td>
                    <td>Invoice (Commercial Invoice)</td>
                    <td class="font-bold">{{ $shipment->invoice_number ?? 'EXP-INV/2026/10/019' }}</td>
                    <td>{{ date('Y-m-d') }}</td>
                </tr>
                <tr>
                    <td class="text-center">2</td>
                    <td class="font-bold">705</td>
                    <td>Bill of Lading (B/L)</td>
                    <td class="font-bold">{{ $shipment->bl_number ?? 'EGLV-990182740' }}</td>
                    <td>{{ date('Y-m-d') }}</td>
                </tr>
                <tr>
                    <td class="text-center">3</td>
                    <td class="font-bold">217</td>
                    <td>Packing List</td>
                    <td class="font-bold">{{ $shipment->packing_list_number ?? 'EXP-PL/2026/10/019' }}</td>
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

    <!-- Nilai Devisa Ekspor -->
    <div class="section-title">D. DATA NILAI TRANSAKSI EKSPOR (DEVISA HASIL EKSPOR)</div>
    <table class="table-peb">
        <tr>
            <td width="25%"><strong>Valuta:</strong> {{ $shipment->currency ?? 'USD' }}</td>
            <td width="25%"><strong>Nilai FOB Devisa:</strong> {{ number_format($shipment->total_cif ?? 42000.00, 2) }}</td>
            <td width="25%"><strong>Kurs Devisa (KMK):</strong> Rp {{ number_format($shipment->ceisaDocument->ndbm_kurs ?? 16250, 0, ',', '.') }}</td>
            <td width="25%"><strong>Nilai FOB (IDR):</strong> Rp {{ number_format(($shipment->total_cif ?? 42000) * ($shipment->ceisaDocument->ndbm_kurs ?? 16250), 0, ',', '.') }}</td>
        </tr>
    </table>

    <!-- Rincian Barang Ekspor -->
    <div class="section-title">E. RINCIAN BARANG EKSPOR & POS TARIF BTKI</div>
    <table class="table-peb">
        <thead>
            <tr>
                <th width="4%" class="text-center">No</th>
                <th width="15%">Pos Tarif / HS Code</th>
                <th width="35%">Uraian Barang Ekspor</th>
                <th width="12%">Negara Tujuan</th>
                <th width="14%" class="text-right">Jumlah / Netto (Kg)</th>
                <th width="20%" class="text-right">Nilai FOB (USD)</th>
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
                <td>Singapore</td>
                <td class="text-right">{{ number_format($item->quantity, 0) }} {{ $item->package_unit }}<br><small>{{ number_format($item->net_weight ?? $item->gross_weight, 2) }} kg</small></td>
                <td class="text-right font-bold">{{ number_format($item->cif_amount, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td class="text-center">1</td>
                <td class="font-bold">0901.11.10</td>
                <td>INDONESIAN ARABICA GREEN COFFEE BEANS GRADE 1<br><small>Gayo Origin - Moisture 12% Max</small></td>
                <td>Singapore</td>
                <td class="text-right">300 BAGS<br><small>18,000.00 kg</small></td>
                <td class="text-right font-bold">42,000.00</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pungutan Ekspor & NPE -->
    <div class="section-title">F. PUNGUTAN EKSPOR & STATUS NOTA PELAYANAN EKSPOR (NPE)</div>
    <table class="table-peb">
        <tr>
            <td width="50%">
                <strong>PUNGUTAN EKSPOR:</strong><br>
                • Bea Keluar (BK): Rp 0 (Tarif 0%)<br>
                • Pungutan Sawit / BPDPKS: Rp 0 (Bukan Komoditas CPO)<br>
                • Total Pungutan Ekspor: <strong>Rp 0</strong><br>
                <small>Cara Pembayaran: Bebas Pungutan</small>
            </td>
            <td width="50%">
                <strong>STATUS RESPON CEISA 4.0:</strong><br>
                Status Terkini: <span class="font-bold" style="color: #047857;">{{ $shipment->ceisaDocument->status ?? 'NPE_ISSUED (PEMBERITAHUAN EKSPOR DIBERIKAN IJIN MUAT)' }}</span><br>
                No. NPE: <strong>{{ $shipment->ceisaDocument->nomor_daftar ?? 'NPE-040300-20261012-00412' }}</strong><br>
                Tanggal NPE: <strong>{{ $shipment->ceisaDocument->tanggal_daftar ?? now()->format('Y-m-d') }}</strong>
            </td>
        </tr>
    </table>

    <!-- Tanda Tangan -->
    <table class="table-peb">
        <tr>
            <td width="50%">
                <strong>PERNYATAAN EKSPORTIR / PPJK:</strong><br>
                Dengan ini menyatakan bahwa seluruh keterangan yang disampaikan dalam Pemberitahuan Ekspor Barang ini adalah benar dan sah.
            </td>
            <td width="50%" class="text-center">
                Jakarta, {{ now()->translatedFormat('d F Y') }}<br>
                <strong>PPJK Kuasa Eksportir,</strong><br>
                <strong>PT AGUNG TRANS SOLUSINDO</strong><br><br><br>
                ( <u>SITI RAHMAWATI</u> )<br>
                <small>Senior Customs Clearance Specialist</small>
            </td>
        </tr>
    </table>

</body>
</html>
