@php
    $company = \App\Models\SystemSetting::get('company_profile', [
        'name' => 'PT AGUNG TRANS SOLUSINDO',
        'address' => 'Gedung Cyber 2 Lantai 15, Jl. H.R. Rasuna Said Blok X-5 No. 13, Jakarta Selatan',
        'phone' => '+62 21 5299 8000',
        'email' => 'ops@genie-logistics.co.id',
    ]);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $emailSubject }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #1c1c1e; margin: 0; padding: 24px; background-color: #f5f5f7; }
        .card { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; padding: 32px; border: 1px solid #e5e5ea; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .header { border-bottom: 2px solid #0284c7; padding-bottom: 16px; margin-bottom: 24px; }
        .title { font-size: 18px; font-weight: 700; color: #0284c7; margin: 0; }
        .detail-table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 14px; }
        .detail-table td { padding: 8px 12px; border-bottom: 1px solid #f2f2f7; }
        .detail-table td.label { width: 35%; color: #8e8e93; font-weight: 500; }
        .detail-table td.value { font-weight: 600; font-family: monospace; }
        .notes-box { background: #fffbe8; border-left: 4px solid #f59e0b; padding: 12px 16px; margin: 16px 0; border-radius: 8px; font-size: 13px; }
        .footer { margin-top: 32px; padding-top: 16px; border-top: 1px solid #e5e5ea; font-size: 12px; color: #8e8e93; }
    </style>
</head>
<body>
<div class="card">
    <div class="header">
        <h2 class="title">{{ $company['name'] ?? 'PT AGUNG TRANS SOLUSINDO' }} (PPJK RESMI)</h2>
        <div style="font-size: 13px; color: #636366;">Divisi Delivery Order & Hubungan Pelayaran</div>
    </div>

    <p>Kepada Yth.<br><strong>Tim Finance & Rilis DO {{ $shipment->vendor?->name ?? 'Pelayaran' }}</strong></p>

    <p>Dengan hormat,<br>
    Kami dari {{ $company['name'] ?? 'PT AGUNG TRANS SOLUSINDO' }} mengajukan permohonan penerbitan invoice tagihan pelayaran resmi (Ocean Freight, THC, Cleaning, dan DO Release Fee) untuk pengapalan kontainer pabean berikut:</p>

    <table class="detail-table">
        <tr>
            <td class="label">Nomor B/L / AWB</td>
            <td class="value">{{ $shipment->nomor_bl_awb }}</td>
        </tr>
        <tr>
            <td class="label">Nama Kapal & Voyage</td>
            <td class="value">{{ $shipment->nama_kapal }} / {{ $shipment->voyage_no }}</td>
        </tr>
        <tr>
            <td class="label">Nama Consignee / Klien</td>
            <td class="value" style="font-family: inherit;">{{ $shipment->client?->name ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Nomor Kontainer</td>
            <td class="value">{{ $shipment->nomor_kontainer }}</td>
        </tr>
        <tr>
            <td class="label">Estimasi Tiba (ETA)</td>
            <td class="value">{{ $shipment->eta_date ? $shipment->eta_date->format('d/m/Y H:i') : 'Menunggu Jadwal' }}</td>
        </tr>
    </table>

    @if(!empty($customNotes))
    <div class="notes-box">
        <strong>Catatan Khusus Pengajuan:</strong><br>
        {{ $customNotes }}
    </div>
    @endif

    <p>Mohon dapat mengirimkan invoice beserta nomor rekening bank resmi pelayaran agar kami dapat segera melakukan settlement pembayaran sebelum masa <em>Free Time</em> kontainer berakhir.</p>

    <p>Terima kasih atas kerja samanya.</p>

    <div class="footer">
        <strong>{{ $company['name'] ?? 'PT AGUNG TRANS SOLUSINDO' }}</strong><br>
        {{ $company['address'] ?? 'Jakarta Selatan' }}<br>
        Hotline Operasional: {{ $company['phone'] ?? '-' }} | Email: {{ $company['email'] ?? '-' }}
    </div>
</div>
</body>
</html>
