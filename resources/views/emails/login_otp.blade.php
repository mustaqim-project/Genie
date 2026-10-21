<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode Verifikasi 2FA - Genie ERP</title>
</head>
<body style="margin: 0; padding: 0; background-color: #0B1120; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #F8FAFC;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #0B1120; padding: 40px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 540px; background-color: #111827; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 20px; overflow: hidden; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);">
                    <!-- Header -->
                    <tr>
                        <td style="padding: 32px 32px 20px; text-align: center; border-bottom: 1px solid rgba(255, 255, 255, 0.08);">
                            <div style="display: inline-block; width: 44px; height: 44px; line-height: 44px; background: linear-gradient(180deg, #2997FF, #0071E3); border-radius: 12px; color: #ffffff; font-weight: 800; font-size: 24px; text-align: center;">
                                G
                            </div>
                            <h1 style="margin: 14px 0 4px; font-size: 20px; font-weight: 700; color: #ffffff; letter-spacing: -0.02em;">Genie ERP</h1>
                            <p style="margin: 0; font-size: 12px; color: #94A3B8; font-weight: 500;">Autentikasi Keamanan Dua Langkah (2FA)</p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 32px;">
                            <p style="margin: 0 0 16px; font-size: 15px; color: #E2E8F0; line-height: 1.5;">
                                Halo, <strong>{{ $user->name }}</strong>,
                            </p>
                            <p style="margin: 0 0 24px; font-size: 13px; color: #94A3B8; line-height: 1.6;">
                                Kami menerima permintaan masuk ke akun Genie ERP Anda. Gunakan kode verifikasi di bawah ini untuk menyelesaikan proses login:
                            </p>

                            <!-- OTP Box -->
                            <div style="background-color: #030712; border: 1px solid #1E293B; border-radius: 14px; padding: 24px; text-align: center; margin-bottom: 24px;">
                                <span style="display: block; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.15em; color: #38BDF8; margin-bottom: 10px;">
                                    Kode Verifikasi 6-Digit
                                </span>
                                <div style="font-family: 'SF Mono', Monaco, Consolas, monospace; font-size: 38px; font-weight: 800; letter-spacing: 10px; color: #ffffff; text-shadow: 0 0 16px rgba(56, 189, 248, 0.4);">
                                    {{ $otpCode }}
                                </div>
                                <span style="display: block; font-size: 11px; color: #64748B; margin-top: 10px;">
                                    Berlaku selama 10 menit &bull; Sekali pakai
                                </span>
                            </div>

                            <p style="margin: 0 0 20px; font-size: 12px; color: #94A3B8; line-height: 1.6;">
                                <strong style="color: #F59E0B;">Peringatan Keamanan:</strong> Jangan pernah membagikan kode ini kepada siapa pun, termasuk staf IT atau pimpinan. Sistem tidak akan pernah meminta kode ini melalui telepon atau pesan teks.
                            </p>

                            <!-- Audit Metadata -->
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 10px; padding: 12px; font-size: 11px; color: #64748B;">
                                <tr>
                                    <td style="padding: 4px 8px;"><strong>Alamat IP:</strong></td>
                                    <td style="padding: 4px 8px; font-family: monospace; color: #94A3B8;">{{ $ipAddress }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 8px;"><strong>Waktu Permintaan:</strong></td>
                                    <td style="padding: 4px 8px; color: #94A3B8;">{{ now()->timezone('Asia/Jakarta')->format('d M Y, H:i:s') }} WIB</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 24px 32px; background-color: #030712; border-top: 1px solid rgba(255, 255, 255, 0.06); text-align: center; font-size: 11px; color: #475569;">
                            <p style="margin: 0 0 6px;">
                                Jika Anda tidak merasa melakukan percobaan masuk ini, segera ubah kata sandi akun Anda atau laporkan ke Administrator Sistem.
                            </p>
                            <p style="margin: 0; color: #334155;">
                                &copy; {{ date('Y') }} PT Agung Trans Solusindo &bull; Genie ERP Enterprise System
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
