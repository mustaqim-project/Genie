@php
    $tmplDefault = [
        'company_name' => 'PT AGUNG TRANS SOLUSINDO',
        'company_subtitle' => "International Freight Forwarder & Customs Brokerage (PPJK)\nJl. Enggano Raya No. 45, Tanjung Priok, Jakarta Utara 14310 | Telp: (021) 4390-8821\nEmail: do-desk@agungtrans.co.id | NPWP: 03.882.910.4-041.000",
        'letter_prefix' => 'ATS/DO-REQ',
        'lampiran' => 'Copy Bill of Lading & Bukti Pembayaran Resmi',
        'perihal' => 'Permohonan Penerbitan dan Rilis Delivery Order (DO) Online',
        'salam_pembuka' => 'Dengan hormat,',
        'paragraf_pembuka' => 'Sehubungan dengan kedatangan sarana pengangkut kargo impor di Pelabuhan Tanjung Priok, bersama ini kami bertindak atas kuasa pemilik barang (Consignee) mengajukan permohonan penerbitan dan rilis berkas Delivery Order (DO) atas pengiriman dengan rincian data sebagai berikut:',
        'lampiran_items' => [
            'Salinan (Copy) Bill of Lading (B/L) yang telah di-endorse sah oleh importir.',
            'Bukti transfer pelunasan biaya lokal pelayaran (Ocean Freight, THC, DO Admin Fee).',
            'Surat Kuasa Pengurusan DO dari Consignee kepada perusahaan kami.',
        ],
        'instruksi_rilis' => 'Mohon berkas Delivery Order (DO) tersebut dapat segera dirilis melalui sistem DO Online / portal pelayaran atau dikirimkan ke alamat email operasional kami: do-desk@agungtrans.co.id.',
        'paragraf_penutup' => 'Demikian surat permohonan ini kami sampaikan. Atas kerja sama dan bantuan yang diberikan, kami ucapkan terima kasih.',
        'signer_name' => 'RENDI SAPUTRA',
        'signer_title' => 'Head of Shipping Line & DO Clearance',
    ];

    $tmpl = \App\Models\SystemSetting::get('template_do_request', $tmplDefault);
    if (!is_array($tmpl)) {
        $decoded = json_decode($tmpl, true);
        $tmpl = (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : $tmplDefault;
    }

    $lampiranList = $tmpl['lampiran_items'] ?? $tmplDefault['lampiran_items'];
    if (is_string($lampiranList)) {
        $lampiranList = array_filter(array_map('trim', explode("\n", $lampiranList)));
    }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Permohonan Rilis DO - {{ $shipment->bl_number ?? $shipment->shipment_number }}</title>
    <!-- Alpine.js untuk modal kustomisasi interaktif -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5pt;
            color: #1e293b;
            line-height: 1.45;
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
        }
        .page-container {
            max-width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            background: #ffffff;
            padding: 20mm;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            box-sizing: border-box;
            border-radius: 4px;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 10px;
            margin-bottom: 16px;
        }
        .company-title {
            font-size: 14pt;
            font-weight: bold;
            color: #0f172a;
            margin: 0 0 4px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .company-subtitle {
            font-size: 8.5pt;
            color: #475569;
            line-height: 1.4;
            margin: 0;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 14px 0;
        }
        .data-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 10px;
            font-size: 9pt;
        }
        .data-table td.label {
            background-color: #f8fafc;
            font-weight: bold;
            width: 34%;
            color: #334155;
        }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        
        /* Modal & UI Controls */
        .no-print {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
        }
        .modal-box {
            background: white;
            border-radius: 16px;
            width: 100%;
            max-width: 680px;
            max-height: 90vh;
            overflow-y: auto;
            padding: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            border: 1px solid #e2e8f0;
        }
        .form-group {
            margin-bottom: 14px;
        }
        .form-group label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 12px;
            box-sizing: border-box;
            outline: none;
            transition: border-color 0.2s;
            font-family: inherit;
        }
        .form-control:focus {
            border-color: #0284c7;
            box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.2);
        }
        textarea.form-control {
            resize: vertical;
            line-height: 1.4;
        }

        @media print {
            body {
                background: transparent;
            }
            .page-container {
                margin: 0;
                padding: 0;
                box-shadow: none;
                border-radius: 0;
                max-width: 100%;
                min-height: auto;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body x-data="{ showEditModal: false, activeTab: 'surat' }">

    <!-- TOPBAR OPERASIONAL (NO-PRINT) -->
    <div class="no-print" style="position: sticky; top: 0; z-index: 1000; background: #0f172a; color: white; padding: 12px 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #1e293b;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <a href="{{ route('core.do.index') }}" style="display: inline-flex; align-items: center; gap: 6px; background: rgba(255,255,255,0.1); color: #cbd5e1; text-decoration: none; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 600; border: 1px solid rgba(255,255,255,0.15);">
                &larr; Kembali ke Modul DO
            </a>
            <div>
                <strong style="font-size: 13px; color: #ffffff; display: block;">Dokumen Cetak: Surat Permohonan Rilis Delivery Order (DO)</strong>
                <span style="font-size: 11px; color: #94a3b8;">
                    No. Shipment: <code style="color: #38bdf8; font-family: monospace;">{{ $shipment->shipment_number }}</code> | B/L: <strong style="color: #f8fafc;">{{ $shipment->bl_number ?? '-' }}</strong>
                </span>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 10px;">
            @if(session('success'))
                <span style="background: #065f46; color: #a7f3d0; font-size: 11px; font-weight: 600; padding: 6px 12px; border-radius: 6px; border: 1px solid #059669;">
                    ✓ {{ session('success') }}
                </span>
            @endif

            <!-- Tombol Pengaturan Template -->
            <button type="button" @click="showEditModal = true" style="background: #334155; color: #f8fafc; border: 1px solid #475569; padding: 7px 14px; border-radius: 6px; font-size: 11px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                <span>⚙️ Edit Template & Kalimat Surat</span>
            </button>

            <!-- Tautan ke Pengaturan Sistem Master -->
            <a href="{{ route('setting.index', ['tab' => 'parameters']) }}" target="_blank" style="background: rgba(255,255,255,0.08); color: #94a3b8; border: 1px solid rgba(255,255,255,0.15); padding: 7px 12px; border-radius: 6px; font-size: 11px; font-weight: 600; text-decoration: none;">
                Master Parameter
            </a>

            <!-- Tombol Cetak / PDF -->
            <button onclick="window.print()" style="background: #0284c7; color: white; border: none; padding: 7px 18px; border-radius: 6px; font-size: 11px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(2, 132, 199, 0.4);">
                Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <!-- KERTAS SURAT RESMI (PRINTABLE CANVAS) -->
    <div class="page-container">
        <!-- Kop Surat Resmi -->
        <table class="header-table">
            <tr>
                <td>
                    <div class="company-title">{{ $tmpl['company_name'] ?? 'PT AGUNG TRANS SOLUSINDO' }}</div>
                    <div class="company-subtitle">{!! nl2br(e($tmpl['company_subtitle'] ?? '')) !!}</div>
                </td>
            </tr>
        </table>

        <!-- Header Informasi Surat -->
        <table style="width: 100%; margin-bottom: 16px;">
            <tr>
                <td width="62%" style="vertical-align: top;">
                    <strong>Nomor Surat:</strong> {{ $tmpl['letter_prefix'] ?? 'ATS/DO-REQ' }}/{{ date('Ym') }}/{{ strtoupper(substr($shipment->id, 0, 8)) }}<br>
                    <strong>Lampiran:</strong> {{ $tmpl['lampiran'] ?? 'Copy Bill of Lading & Bukti Pembayaran Resmi' }}<br>
                    <strong>Perihal:</strong> <u>{{ $tmpl['perihal'] ?? 'Permohonan Penerbitan dan Rilis Delivery Order (DO) Online' }}</u>
                </td>
                <td width="38%" style="text-align: right; vertical-align: top; font-size: 9pt;">
                    Jakarta, {{ now()->translatedFormat('d F Y') }}
                </td>
            </tr>
        </table>

        <!-- Alamat Penerima Surat (Shipping Vendor / Pelayaran) -->
        <div style="margin-bottom: 14px;">
            Kepada Yth.<br>
            <strong>Bagian Customer Service / Import DO Release</strong><br>
            <strong>{{ $shipment->shippingVendor?->name ?? 'PT MAERSK INDONESIA' }}</strong><br>
            <span>{{ $shipment->shippingVendor?->address ?? 'Wisma 46 Kota BNI Lt. 35, Jl. Jend. Sudirman Kav. 1, Jakarta Pusat' }}</span><br>
            <span>Email: {{ $shipment->shippingVendor?->pic_email ?? ($shipment->shippingVendor?->email ?? ($shipment->do_email_recipient ?? 'id.import@maersk.com')) }}</span>
        </div>

        <!-- Salam & Paragraf Pembuka -->
        <p style="margin-bottom: 6px;">{{ $tmpl['salam_pembuka'] ?? 'Dengan hormat,' }}</p>
        <p style="text-align: justify; margin-top: 4px; margin-bottom: 10px;">
            {{ $tmpl['paragraf_pembuka'] ?? 'Sehubungan dengan kedatangan sarana pengangkut kargo impor di Pelabuhan Tanjung Priok, bersama ini kami bertindak atas kuasa pemilik barang (Consignee) mengajukan permohonan penerbitan dan rilis berkas Delivery Order (DO) atas pengiriman dengan rincian data sebagai berikut:' }}
        </p>

        <!-- Tabel Rincian Data Kargo & Pelayaran -->
        <table class="data-table">
            <tr>
                <td class="label">Nomor Bill of Lading (B/L)</td>
                <td class="font-bold" style="color: #0369a1;">{{ $shipment->bl_number ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Nama Kapal & No. Voyage</td>
                <td>{{ $shipment->vessel_name ?? '-' }} / {{ $shipment->voyage_number ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Nama Consignee (Importir)</td>
                <td class="font-bold">{{ $shipment->client->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Pelabuhan Muat / Bongkar</td>
                <td>{{ $shipment->port_of_loading ?? '-' }} &rarr; {{ $shipment->port_of_discharge ?? 'IDTPP (Tanjung Priok)' }}</td>
            </tr>
            <tr>
                <td class="label">Perkiraan Waktu Tiba (ETA)</td>
                <td>{{ $shipment->eta ? $shipment->eta->translatedFormat('d F Y') : '-' }}</td>
            </tr>
            <tr>
                <td class="label">Nomor Petikemas / Kontainer</td>
                <td class="font-bold">
                    @if($shipment->containers && $shipment->containers->count() > 0)
                        {{ $shipment->containers->pluck('container_number')->implode(', ') }} ({{ $shipment->containers->count() }} Petikemas)
                    @else
                        MSKU-9182740 (1 x 40' High Cube)
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Status Masa Bebas (Free Time)</td>
                <td>
                    {{ $shipment->free_time_days ?? 7 }} Hari Bebas Demurrage & Detention 
                    (s/d {{ now()->addDays($shipment->free_time_days ?? 7)->translatedFormat('d F Y') }})
                </td>
            </tr>
        </table>

        <!-- Lampiran Dokumen Pelengkap -->
        <p style="margin-bottom: 4px; font-weight: bold;">
            Bersama surat ini kami lampirkan dokumen pendukung:
        </p>
        <ul style="margin: 4px 0 12px 20px; padding: 0;">
            @foreach($lampiranList as $lamp)
                <li style="margin-bottom: 3px;">{{ $lamp }}</li>
            @endforeach
        </ul>

        <!-- Instruksi / Catatan Rilis -->
        <p style="text-align: justify; margin-bottom: 10px;">
            {{ $tmpl['instruksi_rilis'] ?? 'Mohon berkas Delivery Order (DO) tersebut dapat segera dirilis melalui sistem DO Online / portal pelayaran atau dikirimkan ke alamat email operasional kami.' }}
        </p>

        <!-- Paragraf Penutup -->
        <p style="text-align: justify; margin-bottom: 24px;">
            {{ $tmpl['paragraf_penutup'] ?? 'Demikian surat permohonan ini kami sampaikan. Atas kerja sama dan bantuan yang diberikan, kami ucapkan terima kasih.' }}
        </p>

        <!-- Bagian Tanda Tangan -->
        <table style="width: 100%; margin-top: 20px;">
            <tr>
                <td width="50%" style="vertical-align: top;">
                    Mengetahui Pemilik Barang (Consignee),<br>
                    <strong>{{ $shipment->client->name ?? 'PT MITRA MAKMUR SENTOSA' }}</strong><br><br><br><br>
                    ( ___________________________ )<br>
                    <small style="color: #64748b;">Import Manager / Authorized Signatory</small>
                </td>
                <td width="50%" class="text-center" style="vertical-align: top;">
                    Hormat kami,<br>
                    <strong>{{ $tmpl['company_name'] ?? 'PT AGUNG TRANS SOLUSINDO' }}</strong><br><br><br><br>
                    ( <strong>{{ $tmpl['signer_name'] ?? 'RENDI SAPUTRA' }}</strong> )<br>
                    <small style="color: #64748b;">{{ $tmpl['signer_title'] ?? 'Head of Shipping Line & DO Clearance' }}</small>
                </td>
            </tr>
        </table>
    </div>

    <!-- MODAL EDIT TEMPLATE & KALIMAT SURAT (ZERO HARDCODING) -->
    <div x-show="showEditModal" x-cloak class="no-print modal-overlay" @keydown.escape.window="showEditModal = false">
        <div class="modal-box" @click.outside="showEditModal = false">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 16px;">
                <div>
                    <h3 style="margin: 0; font-size: 15px; font-weight: 800; color: #0f172a;">⚙️ Konfigurasi Template & Kalimat Surat DO</h3>
                    <p style="margin: 2px 0 0 0; font-size: 11px; color: #64748b;">Seluruh teks disimpan ke tabel parameter sistem secara dinamis dan real-time.</p>
                </div>
                <button type="button" @click="showEditModal = false" style="background: none; border: none; font-size: 18px; color: #94a3b8; cursor: pointer;">✕</button>
            </div>

            <form action="{{ route('setting.parameters.update') }}" method="POST" id="templateForm">
                @csrf
                <input type="hidden" name="group" value="template">
                <input type="hidden" name="key" value="template_do_request">
                <input type="hidden" name="description" value="Template kop, perihal, dan kalimat surat resmi permohonan rilis DO pelayaran">
                
                <!-- Hidden input untuk menampung JSON hasil encode -->
                <input type="hidden" name="value" id="jsonTemplateValue">

                <div class="form-group">
                    <label>1. Nama Resmi Perusahaan (Kop Atas)</label>
                    <input type="text" id="f_company_name" class="form-control" value="{{ $tmpl['company_name'] ?? 'PT AGUNG TRANS SOLUSINDO' }}" required>
                </div>

                <div class="form-group">
                    <label>2. Subtitle Kop (Alamat, Telp, Email, NPWP)</label>
                    <textarea id="f_company_subtitle" rows="3" class="form-control" required>{{ $tmpl['company_subtitle'] ?? '' }}</textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label>3. Prefix Nomor Surat</label>
                        <input type="text" id="f_letter_prefix" class="form-control" value="{{ $tmpl['letter_prefix'] ?? 'ATS/DO-REQ' }}" required>
                    </div>
                    <div class="form-group">
                        <label>4. Keterangan Lampiran Header</label>
                        <input type="text" id="f_lampiran" class="form-control" value="{{ $tmpl['lampiran'] ?? 'Copy Bill of Lading & Bukti Pembayaran Resmi' }}" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>5. Perihal Surat</label>
                    <input type="text" id="f_perihal" class="form-control" value="{{ $tmpl['perihal'] ?? 'Permohonan Penerbitan dan Rilis Delivery Order (DO) Online' }}" required>
                </div>

                <div class="form-group">
                    <label>6. Salam Pembuka</label>
                    <input type="text" id="f_salam_pembuka" class="form-control" value="{{ $tmpl['salam_pembuka'] ?? 'Dengan hormat,' }}" required>
                </div>

                <div class="form-group">
                    <label>7. Paragraf Pembuka Surat</label>
                    <textarea id="f_paragraf_pembuka" rows="3" class="form-control" required>{{ $tmpl['paragraf_pembuka'] ?? '' }}</textarea>
                </div>

                <div class="form-group">
                    <label>8. Dokumen Lampiran Pendukung (Pisahkan Tiap Baris)</label>
                    <textarea id="f_lampiran_items" rows="3" class="form-control" required>{{ is_array($lampiranList) ? implode("\n", $lampiranList) : $lampiranList }}</textarea>
                    <small style="color: #64748b; font-size: 10px;">Setiap baris baru akan otomatis menjadi butir bullet point pada surat.</small>
                </div>

                <div class="form-group">
                    <label>9. Instruksi Rilis & Alamat Email Tujuan DO</label>
                    <textarea id="f_instruksi_rilis" rows="2" class="form-control" required>{{ $tmpl['instruksi_rilis'] ?? '' }}</textarea>
                </div>

                <div class="form-group">
                    <label>10. Paragraf Penutup</label>
                    <textarea id="f_paragraf_penutup" rows="2" class="form-control" required>{{ $tmpl['paragraf_penutup'] ?? '' }}</textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label>11. Nama Penandatangan</label>
                        <input type="text" id="f_signer_name" class="form-control" value="{{ $tmpl['signer_name'] ?? 'RENDI SAPUTRA' }}" required>
                    </div>
                    <div class="form-group">
                        <label>12. Jabatan Penandatangan</label>
                        <input type="text" id="f_signer_title" class="form-control" value="{{ $tmpl['signer_title'] ?? 'Head of Shipping Line & DO Clearance' }}" required>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; border-top: 1px solid #e2e8f0; padding-top: 14px;">
                    <button type="button" @click="showEditModal = false" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 12px; cursor: pointer;">
                        Batal
                    </button>
                    <button type="submit" style="background: #0284c7; color: white; border: none; padding: 8px 20px; border-radius: 8px; font-weight: 700; font-size: 12px; cursor: pointer;">
                        Simpan & Terapkan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('templateForm').addEventListener('submit', function(e) {
            const rawLampiran = document.getElementById('f_lampiran_items').value;
            const lampiranArr = rawLampiran.split('\n').map(s => s.trim()).filter(s => s.length > 0);

            const payload = {
                company_name: document.getElementById('f_company_name').value,
                company_subtitle: document.getElementById('f_company_subtitle').value,
                letter_prefix: document.getElementById('f_letter_prefix').value,
                lampiran: document.getElementById('f_lampiran').value,
                perihal: document.getElementById('f_perihal').value,
                salam_pembuka: document.getElementById('f_salam_pembuka').value,
                paragraf_pembuka: document.getElementById('f_paragraf_pembuka').value,
                lampiran_items: lampiranArr,
                instruksi_rilis: document.getElementById('f_instruksi_rilis').value,
                paragraf_penutup: document.getElementById('f_paragraf_penutup').value,
                signer_name: document.getElementById('f_signer_name').value,
                signer_title: document.getElementById('f_signer_title').value
            };

            document.getElementById('jsonTemplateValue').value = JSON.stringify(payload);
        });
    </script>
</body>
</html>
