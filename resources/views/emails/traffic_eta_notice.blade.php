<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemberitahuan Status Kargo & Jadwal Kedatangan (ETA)</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 24px; }
        .container { max-width: 620px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .header { background: #0369a1; padding: 32px 32px; color: #ffffff; text-align: left; }
        .header h1 { margin: 0; font-size: 20px; font-weight: 700; letter-spacing: -0.5px; }
        .header p { margin: 4px 0 0; font-size: 13px; color: #bae6fd; }
        .content { padding: 32px; }
        .greeting { font-size: 15px; font-weight: 600; margin-bottom: 12px; }
        .lead { font-size: 14px; line-height: 1.6; color: #475569; margin-bottom: 24px; }
        .status-pill { display: inline-block; padding: 6px 14px; border-radius: 20px; font-weight: 700; font-size: 12px; background: #e0f2fe; color: #0369a1; margin-bottom: 16px; }
        .card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 24px; }
        .eta-highlight { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 16px; text-align: center; margin: 20px 0; }
        .eta-label { font-size: 12px; color: #166534; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .eta-val { font-size: 22px; color: #15803d; font-weight: 700; margin-top: 4px; }
        .btn-action { display: inline-block; background: #0284c7; color: #ffffff !important; text-decoration: none; padding: 12px 28px; border-radius: 8px; font-weight: 600; font-size: 14px; text-align: center; }
        .footer { background: #f1f5f9; padding: 20px 32px; text-align: center; font-size: 12px; color: #64748b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PT GENIE LOGISTIK INDONESIA</h1>
            <p>Divisi Traffic & Fleet Dispatch • Real-time Cargo Tracking Center</p>
        </div>

        <div class="content">
            @if(!empty($customBody))
                <div class="lead" style="white-space: pre-wrap; font-size: 13.5px; line-height: 1.7; color: #334155; margin-bottom: 24px;">
                    {!! nl2br(e($customBody)) !!}
                </div>
            @else
                <div class="greeting">Yth. Bapak/Ibu Tim Logistik {{ $shipment->client->nama_perusahaan ?? 'Perusahaan Mitra' }},</div>
                <p class="lead">
                    Berikut kami sampaikan pembaruan status pergerakan kargo dan perkiraan waktu tiba sarana pengangkut (ETA) untuk shipment Anda:
                </p>
            @endif

            <div style="text-align: center;">
                <div class="status-pill">
                    STATUS TERBARU: {{ strtoupper($shipment->status ?? 'IN_TRANSIT') }}
                </div>
            </div>

            <div class="eta-highlight">
                <div class="eta-label">Estimasi Waktu Tiba (ETA / Sandar)</div>
                <div class="eta-val">{{ $shipment->eta ? date('d F Y - H:i', strtotime($shipment->eta)) : 'Dalam Konfirmasi Pelayaran' }} WIB</div>
            </div>

            <div class="card">
                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <tr>
                        <td style="padding: 6px 0; color: #64748b;">No. Job / Proyek</td>
                        <td style="padding: 6px 0; text-align: right; font-weight: 700; color: #0f172a;">{{ $shipment->project_no ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #64748b;">Kapal / Maskapai</td>
                        <td style="padding: 6px 0; text-align: right; font-weight: 600;">{{ $shipment->vessel_name ?? '-' }} (Voy: {{ $shipment->voyage_number ?? '-' }})</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #64748b;">Nomor B/L / AWB</td>
                        <td style="padding: 6px 0; text-align: right; font-weight: 600;">{{ $shipment->bl_number ?? $shipment->m_awb ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #64748b;">Dermaga / TPS Tujuan</td>
                        <td style="padding: 6px 0; text-align: right; font-weight: 600;">{{ $shipment->tps ?? $shipment->pod ?? 'Pelabuhan Tujuan' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #64748b;">Jalur Kepabeanan</td>
                        <td style="padding: 6px 0; text-align: right; font-weight: 700; color: {{ $shipment->jalur == 'HIJAU' ? '#15803d' : ($shipment->jalur == 'MERAH' ? '#b91c1c' : '#b45309') }};">
                            JALUR {{ $shipment->jalur ?? 'DRAFT' }}
                        </td>
                    </tr>
                </table>
            </div>

            <div style="text-align: center; margin-top: 24px;">
                <a href="{{ url('/portal/client/' . ($shipment->portal_token ?? $shipment->id)) }}" class="btn-action">
                    Buka Live Tracking & Unduh Surat Jalan
                </a>
            </div>
        </div>

        <div class="footer">
            <p style="margin: 0 0 6px;">Armada trucking kami siap meluncur ke gudang penerima begitu kontainer lolos SPPB.</p>
            <p style="margin: 0; font-weight: 600; color: #334155;">GENIE ERP System • Live AIS & Tracking Intelligence</p>
        </div>
    </div>
</body>
</html>