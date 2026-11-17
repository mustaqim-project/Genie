<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi & Approval Draft Dokumen Pabean PIB/PEB</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 24px; }
        .container { max-width: 620px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .header { background: #064e3b; padding: 32px 32px; color: #ffffff; text-align: left; }
        .header h1 { margin: 0; font-size: 20px; font-weight: 700; letter-spacing: -0.5px; }
        .header p { margin: 4px 0 0; font-size: 13px; color: #a7f3d0; }
        .content { padding: 32px; }
        .greeting { font-size: 15px; font-weight: 600; margin-bottom: 12px; }
        .lead { font-size: 14px; line-height: 1.6; color: #475569; margin-bottom: 24px; }
        .card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 24px; }
        .alert-box { background: #fffbeb; border: 1px solid #fef3c7; border-left: 4px solid #f59e0b; padding: 14px; border-radius: 8px; font-size: 12px; color: #92400e; margin-bottom: 20px; }
        .btn-action { display: inline-block; background: #059669; color: #ffffff !important; text-decoration: none; padding: 12px 28px; border-radius: 8px; font-weight: 600; font-size: 14px; text-align: center; }
        .footer { background: #f1f5f9; padding: 20px 32px; text-align: center; font-size: 12px; color: #64748b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PT GENIE LOGISTIK INDONESIA</h1>
            <p>Divisi Dokumen Kepabeanan • CEISA 4.0 Host-to-Host Integration</p>
        </div>

        <div class="content">
            @if(!empty($customBody))
                <div class="lead" style="white-space: pre-wrap; font-size: 13.5px; line-height: 1.7; color: #334155; margin-bottom: 24px;">
                    {!! nl2br(e($customBody)) !!}
                </div>
            @else
                <div class="greeting">Yth. Tim Import-Export {{ $shipment->client->nama_perusahaan ?? 'Perusahaan Mitra' }},</div>
                <p class="lead">
                    Tim Dokumen kami telah menyelesaikan penyusunan lembar kerja Draft Pemberitahuan Pabean (PIB/PEB) untuk kargo Anda. Mohon diperiksa dan diberikan konfirmasi approval sebelum kami submit ke sistem CEISA 4.0 Bea Cukai:
                </p>
            @endif

            <div class="alert-box">
                <strong>PENTING:</strong> Pastikan Pos Tarif HS Code, uraian barang, valuta CIF/FOB, dan nomor izin perizinan (jika lartas) sudah sesuai dengan dokumen asli (Invoice, Packing List, BL/AWB).
            </div>

            <div class="card">
                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <tr>
                        <td style="padding: 6px 0; color: #64748b;">Nomor Pengajuan (Aju)</td>
                        <td style="padding: 6px 0; text-align: right; font-weight: 700; color: #0f172a;">{{ $shipment->nomor_aju ?? 'DRAFT-AJU' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #64748b;">Jenis Dokumen</td>
                        <td style="padding: 6px 0; text-align: right; font-weight: 600;">{{ $shipment->type == 'EXPORT' ? 'PEB (Pemberitahuan Ekspor Barang)' : 'PIB (Pemberitahuan Impor Barang)' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #64748b;">Nomor B/L / Master AWB</td>
                        <td style="padding: 6px 0; text-align: right; font-weight: 600;">{{ $shipment->bl_number ?? $shipment->m_awb ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #64748b;">Pelabuhan Bongkar / Muat</td>
                        <td style="padding: 6px 0; text-align: right; font-weight: 600;">{{ $shipment->pod ?? $shipment->pol ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #64748b;">Estimasi Pungutan Bea & Pajak</td>
                        <td style="padding: 6px 0; text-align: right; font-weight: 700; color: #047857;">Rp {{ number_format($shipment->total_billing ?? 0, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>

            <div style="text-align: center; margin-top: 24px;">
                <a href="{{ url('/print/shipments/' . ($shipment->id ?? 1) . '/' . ($shipment->type == 'EXPORT' ? 'peb' : 'pib')) }}" class="btn-action">
                    Review & Unduh Lembar Draft PDF
                </a>
            </div>
        </div>

        <div class="footer">
            <p style="margin: 0 0 6px;">Apabila data telah sesuai, silakan balas email ini dengan format: <strong>"DRAFT APPROVED UNTUK SUBMIT"</strong>.</p>
            <p style="margin: 0; font-weight: 600; color: #334155;">GENIE ERP System • Customs Document Verification Suite</p>
        </div>
    </div>
</body>
</html>