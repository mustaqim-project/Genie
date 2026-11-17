<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penawaran Harga Ekspor Impor - {{ $quotation->quotation_number ?? 'QUO-GENIE' }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 24px; }
        .container { max-width: 620px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .header { background: #0f172a; padding: 32px 32px; color: #ffffff; text-align: left; }
        .header h1 { margin: 0; font-size: 20px; font-weight: 700; letter-spacing: -0.5px; }
        .header p { margin: 4px 0 0; font-size: 13px; color: #94a3b8; }
        .content { padding: 32px; }
        .greeting { font-size: 15px; font-weight: 600; margin-bottom: 12px; }
        .lead { font-size: 14px; line-height: 1.6; color: #475569; margin-bottom: 24px; }
        .card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 24px; }
        .card-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #edf2f7; font-size: 13px; }
        .card-row:last-child { border-bottom: none; }
        .card-label { color: #64748b; font-weight: 500; }
        .card-val { color: #0f172a; font-weight: 600; text-align: right; }
        .total-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 16px; text-align: center; margin: 20px 0; }
        .total-label { font-size: 12px; color: #1e40af; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .total-amount { font-size: 24px; color: #1d4ed8; font-weight: 700; margin-top: 4px; }
        .btn-action { display: inline-block; background: #0284c7; color: #ffffff !important; text-decoration: none; padding: 12px 28px; border-radius: 8px; font-weight: 600; font-size: 14px; text-align: center; margin-top: 10px; }
        .footer { background: #f1f5f9; padding: 20px 32px; text-align: center; font-size: 12px; color: #64748b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PT GENIE LOGISTIK INDONESIA</h1>
            <p>Divisi Marketing & Commercial Solutions • Customs Clearance & Freight Forwarding</p>
        </div>

        <div class="content">
            @if(!empty($customBody))
                <div class="lead" style="white-space: pre-wrap; font-size: 13.5px; line-height: 1.7; color: #334155; margin-bottom: 24px;">
                    {!! nl2br(e($customBody)) !!}
                </div>
            @else
                <div class="greeting">Yth. Bapak/Ibu {{ $quotation->client->pic_name ?? 'Pimpinan' }},</div>
                <p class="lead">
                    Terima kasih atas kepercayaan Anda kepada PT GENIE Logistik Indonesia. Bersama email ini kami lampirkan surat penawaran harga resmi (Quotation) untuk kebutuhan jasa kepabeanan dan logistik perusahaan Anda:
                </p>
            @endif

            <div class="card">
                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <tr>
                        <td style="padding: 6px 0; color: #64748b;">Nomor Penawaran</td>
                        <td style="padding: 6px 0; text-align: right; font-weight: 700; color: #0f172a;">{{ $quotation->quotation_number ?? 'QUO-202611-001' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #64748b;">Perusahaan Klien</td>
                        <td style="padding: 6px 0; text-align: right; font-weight: 600;">{{ $quotation->client->nama_perusahaan ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #64748b;">Layanan / Ruang Lingkup</td>
                        <td style="padding: 6px 0; text-align: right; font-weight: 600;">{{ $quotation->service_type ?? 'Customs Clearance & Delivery' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #64748b;">Rute Logistik</td>
                        <td style="padding: 6px 0; text-align: right; font-weight: 600;">{{ $quotation->origin ?? 'Port of Loading' }} → {{ $quotation->destination ?? 'Gudang Klien' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #64748b;">Masa Berlaku Penawaran</td>
                        <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #d97706;">Sampai {{ $quotation->valid_until ? date('d F Y', strtotime($quotation->valid_until)) : '14 Hari ke depan' }}</td>
                    </tr>
                </table>
            </div>

            <div class="total-box">
                <div class="total-label">Estimasi Nilai Penawaran (IDR)</div>
                <div class="total-amount">Rp {{ number_format($quotation->total_amount ?? 0, 0, ',', '.') }}</div>
            </div>

            <p style="font-size: 13px; color: #64748b; line-height: 1.5;">
                * Penawaran ini mencakup jasa pengurusan PIB/PEB, monitoring jalur kepabeanan, koordinasi perizinan lartas, dan trucking delivery. Dokumen PDF lengkap telah terlampir pada email ini.
            </p>

            <div style="text-align: center; margin-top: 24px;">
                <a href="{{ url('/print/quotations/' . ($quotation->id ?? 1)) }}" class="btn-action">
                    Unduh Salinan PDF Resmi
                </a>
            </div>
        </div>

        <div class="footer">
            <p style="margin: 0 0 6px;">Butuh penyesuaian atau negosiasi volume kargo? Balas email ini atau hubungi Account Executive Anda.</p>
            <p style="margin: 0; font-weight: 600; color: #334155;">GENIE ERP System • Integrated Customs & Logistics Intelligence</p>
        </div>
    </div>
</body>
</html>