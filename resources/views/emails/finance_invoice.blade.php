<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemberitahuan Tagihan Invoice - {{ $invoice->invoice_number ?? 'INV-GENIE' }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 24px; }
        .container { max-width: 620px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .header { background: #1e1b4b; padding: 32px 32px; color: #ffffff; text-align: left; }
        .header h1 { margin: 0; font-size: 20px; font-weight: 700; letter-spacing: -0.5px; }
        .header p { margin: 4px 0 0; font-size: 13px; color: #c7d2fe; }
        .content { padding: 32px; }
        .greeting { font-size: 15px; font-weight: 600; margin-bottom: 12px; }
        .lead { font-size: 14px; line-height: 1.6; color: #475569; margin-bottom: 24px; }
        .card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 24px; }
        .total-box { background: #faf5ff; border: 1px solid #e9d5ff; border-radius: 10px; padding: 18px; text-align: center; margin: 20px 0; }
        .total-label { font-size: 12px; color: #6b21a8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .total-amount { font-size: 26px; color: #7e22ce; font-weight: 700; margin-top: 4px; }
        .btn-action { display: inline-block; background: #6366f1; color: #ffffff !important; text-decoration: none; padding: 12px 28px; border-radius: 8px; font-weight: 600; font-size: 14px; text-align: center; margin-top: 10px; }
        .bank-info { background: #f1f5f9; border-radius: 10px; padding: 14px 18px; font-size: 12px; color: #334155; margin-top: 18px; }
        .footer { background: #f1f5f9; padding: 20px 32px; text-align: center; font-size: 12px; color: #64748b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PT GENIE LOGISTIK INDONESIA</h1>
            <p>Divisi Finance & Penagihan (Billing Dept) • Faktur Pajak & Invoice P19</p>
        </div>

        <div class="content">
            @if(!empty($customBody))
                <div class="lead" style="white-space: pre-wrap; font-size: 13.5px; line-height: 1.7; color: #334155; margin-bottom: 24px;">
                    {!! nl2br(e($customBody)) !!}
                </div>
            @else
                <div class="greeting">Kepada Yth. Bagian Keuangan / Finance {{ $invoice->client->nama_perusahaan ?? 'Klien Mitra' }},</div>
                <p class="lead">
                    Bersama ini kami sampaikan lembar tagihan resmi (Invoice) atas penyelesaian jasa kepabeanan dan operasional kargo perusahaan Anda:
                </p>
            @endif

            <div class="card">
                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <tr>
                        <td style="padding: 6px 0; color: #64748b;">Nomor Invoice</td>
                        <td style="padding: 6px 0; text-align: right; font-weight: 700; color: #0f172a;">{{ $invoice->invoice_number ?? 'INV-202611-001' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #64748b;">No. Job Order / B/L</td>
                        <td style="padding: 6px 0; text-align: right; font-weight: 600;">{{ $invoice->shipment->project_no ?? '-' }} / {{ $invoice->shipment->bl_number ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #64748b;">Tanggal Jatuh Tempo</td>
                        <td style="padding: 6px 0; text-align: right; font-weight: 700; color: #dc2626;">{{ $invoice->due_date ? date('d F Y', strtotime($invoice->due_date)) : '14 Hari dari Invoice' }}</td>
                    </tr>
                    @if(!empty($invoice->deposit_deducted_idr) && $invoice->deposit_deducted_idr > 0)
                    <tr>
                        <td style="padding: 6px 0; color: #059669;">Pemotongan Deposit Klien</td>
                        <td style="padding: 6px 0; text-align: right; font-weight: 600; color: #059669;">- Rp {{ number_format($invoice->deposit_deducted_idr, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                </table>
            </div>

            <div class="total-box">
                <div class="total-label">Sisa Tagihan yang Wajib Dibayarkan</div>
                <div class="total-amount">Rp {{ number_format($invoice->sisa_tagihan_idr ?? $invoice->total_tagihan_idr ?? 0, 0, ',', '.') }}</div>
            </div>

            <div class="bank-info">
                <strong>Rekening Pembayaran Resmi PT GENIE LOGISTIK INDONESIA:</strong><br>
                Bank Mandiri Cabang Pelabuhan Tanjung Perak: <strong>141-00-9876543-2</strong><br>
                Bank Central Asia (BCA) KCP Surabaya: <strong>010-8899-771</strong><br>
                Atas Nama: <strong>PT GENIE LOGISTIK INDONESIA</strong>
            </div>

            <div style="text-align: center; margin-top: 24px;">
                <a href="{{ url('/print/invoices/' . ($invoice->id ?? 1)) }}" class="btn-action">
                    Unduh Dokumen Faktur & Invoice PDF
                </a>
            </div>
        </div>

        <div class="footer">
            <p style="margin: 0 0 6px;">Mohon mengirimkan bukti transfer via reply email ini agar status tagihan diperbarui otomatis.</p>
            <p style="margin: 0; font-weight: 600; color: #334155;">GENIE ERP System • Financial Integrity & Compliance</p>
        </div>
    </div>
</body>
</html>