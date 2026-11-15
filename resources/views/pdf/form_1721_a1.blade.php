@php
    $company = \App\Models\SystemSetting::get('company_profile', [
        'name' => 'PT AGUNG TRANS SOLUSINDO',
        'npwp' => '01.234.567.8-012.000',
    ]);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Formulir 1721-A1 Tahun {{ $reconciliation['year'] }} - {{ $reconciliation['employee']->full_name }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #111; line-height: 1.3; margin: 0; padding: 20px; }
        .border-box { border: 2px solid #000; padding: 12px; margin-bottom: 12px; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .header-title { font-size: 14px; font-weight: bold; text-align: center; text-transform: uppercase; }
        .header-sub { font-size: 10px; text-align: center; color: #444; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .data-table th, .data-table td { border: 1px solid #333; padding: 4px 6px; }
        .data-table th { background-color: #f2f2f2; font-weight: bold; text-align: center; font-size: 10px; }
        .num-col { width: 5%; text-align: center; font-family: monospace; }
        .desc-col { width: 65%; }
        .amt-col { width: 30%; text-align: right; font-family: monospace; }
        .section-hdr { background-color: #e6e6e6; font-weight: bold; padding: 4px 6px; }
        .footer-sig { width: 100%; margin-top: 20px; }
        .footer-sig td { vertical-align: top; }
    </style>
</head>
<body>

<div class="border-box">
    <table class="header-table">
        <tr>
            <td style="width: 20%; vertical-align: middle;">
                <strong>KEMENTERIAN KEUANGAN RI</strong><br>
                DIREKTORAT JENDERAL PAJAK
            </td>
            <td style="width: 60%;" class="header-title">
                BUKTI PEMOTONGAN PAJAK PENGHASILAN PASAL 21 BAGI PEGAWAI TETAP ATAU PENERIMA PENSIUN/TUNJANGAN HARI TUA/JAMINAN HARI TUA BERKALA<br>
                <span style="font-size: 12px;">FORMULIR 1721 - A1</span><br>
                <span class="header-sub">Lembar ke-1 : untuk Penerima Penghasilan | Lembar ke-2 : untuk Pemotong Pajak</span>
            </td>
            <td style="width: 20%; text-align: right; vertical-align: middle;">
                <strong>TAHUN PAJAK</strong><br>
                <span style="font-size: 18px; font-family: monospace; font-weight: bold;">{{ $reconciliation['year'] }}</span>
            </td>
        </tr>
    </table>

    <table style="width: 100%; border: 1px solid #000; padding: 6px; margin-bottom: 8px; font-size: 10px;">
        <tr>
            <td style="width: 18%;">NPWP Pemotong Pajak</td>
            <td style="width: 32%;">: <strong>{{ $company['npwp'] ?? '01.234.567.8-012.000' }}</strong></td>
            <td style="width: 18%;">Nama Pemotong Pajak</td>
            <td style="width: 32%;">: <strong>{{ $company['name'] ?? 'PT AGUNG TRANS SOLUSINDO' }}</strong></td>
        </tr>
    </table>

    <div class="section-hdr">A. IDENTITAS PENERIMA PENGHASILAN YANG DIPOTONG</div>
    <table class="data-table" style="margin-top: 0; margin-bottom: 8px;">
        <tr>
            <td style="width: 25%;">NIK / Paspor</td>
            <td style="width: 75%; font-family: monospace;">{{ $reconciliation['employee']->nik }}</td>
        </tr>
        <tr>
            <td>Nama Lengkap</td>
            <td><strong>{{ $reconciliation['employee']->full_name }}</strong></td>
        </tr>
        <tr>
            <td>Jabatan / Tim</td>
            <td>{{ $reconciliation['employee']->position }} (Tim: {{ $reconciliation['employee']->team ?? 'Operasional' }})</td>
        </tr>
        <tr>
            <td>Status PTKP / Jumlah Tanggungan</td>
            <td><strong>{{ $reconciliation['ptkp_status'] }}</strong></td>
        </tr>
        <tr>
            <td>Masa Perolehan Penghasilan</td>
            <td>01 - {{ str_pad((string) $reconciliation['months_worked'], 2, '0', STR_PAD_LEFT) }} ({{ $reconciliation['months_worked'] }} Bulan)</td>
        </tr>
    </table>

    <div class="section-hdr">B. RINCIAN PENGHASILAN DAN PENGHITUNGAN PPh PASAL 21</div>
    <table class="data-table" style="margin-top: 0;">
        <thead>
            <tr>
                <th>NO</th>
                <th>URAIAN PENGHASILAN & PENGURANG</th>
                <th>JUMLAH (RUPIAH)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="3" style="background-color: #f9f9f9; font-weight: bold;">I. PENGHASILAN BRUTO</td>
            </tr>
            <tr>
                <td class="num-col">1.</td>
                <td class="desc-col">Gaji Pokok, Tunjangan, Uang Lembur, dan Honorarium Lainnya</td>
                <td class="amt-col">Rp {{ number_format($reconciliation['total_gross'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="num-col">2.</td>
                <td class="desc-col"><strong>JUMLAH PENGHASILAN BRUTO (1)</strong></td>
                <td class="amt-col"><strong>Rp {{ number_format($reconciliation['total_gross'], 0, ',', '.') }}</strong></td>
            </tr>

            <tr>
                <td colspan="3" style="background-color: #f9f9f9; font-weight: bold;">II. PENGURANG PENGHASILAN BRUTO</td>
            </tr>
            <tr>
                <td class="num-col">3.</td>
                <td class="desc-col">Biaya Jabatan (5% dari Penghasilan Bruto, maks. Rp 6.000.000/thn)</td>
                <td class="amt-col">Rp {{ number_format($reconciliation['biaya_jabatan'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="num-col">4.</td>
                <td class="desc-col">Iuran Pensiun atau Iuran JHT & JP BPJS Ketenagakerjaan dibayar Karyawan</td>
                <td class="amt-col">Rp {{ number_format($reconciliation['iuran_pensiun_jht'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="num-col">5.</td>
                <td class="desc-col"><strong>JUMLAH PENGURANG (3 + 4)</strong></td>
                <td class="amt-col"><strong>Rp {{ number_format($reconciliation['total_pengurang'], 0, ',', '.') }}</strong></td>
            </tr>

            <tr>
                <td colspan="3" style="background-color: #f9f9f9; font-weight: bold;">III. PENGHITUNGAN PPh PASAL 21</td>
            </tr>
            <tr>
                <td class="num-col">6.</td>
                <td class="desc-col">Jumlah Penghasilan Neto Setahun (2 - 5)</td>
                <td class="amt-col">Rp {{ number_format($reconciliation['penghasilan_neto'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="num-col">7.</td>
                <td class="desc-col">Penghasilan Tidak Kena Pajak (PTKP: {{ $reconciliation['ptkp_status'] }})</td>
                <td class="amt-col">Rp {{ number_format($reconciliation['ptkp_amount'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="num-col">8.</td>
                <td class="desc-col"><strong>Penghasilan Kena Pajak (PKP) Setahun (6 - 7)</strong></td>
                <td class="amt-col"><strong>Rp {{ number_format($reconciliation['pkp'], 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td class="num-col">9.</td>
                <td class="desc-col"><strong>PPh Pasal 21 Terutang Setahun (Tarif Progresif Pasal 17 UU HPP)</strong></td>
                <td class="amt-col"><strong>Rp {{ number_format($reconciliation['pph21_annual_owed'], 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td class="num-col">10.</td>
                <td class="desc-col">PPh Pasal 21 yang Telah Dipotong Bulanan (Masa Jan s.d. Nov / TER)</td>
                <td class="amt-col">Rp {{ number_format($reconciliation['pph21_already_withheld'], 0, ',', '.') }}</td>
            </tr>
            <tr style="background-color: #fff9e6;">
                <td class="num-col">11.</td>
                <td class="desc-col">
                    <strong>PPh Pasal 21 {{ $reconciliation['difference'] >= 0 ? 'Kurang Dipotong (Masa Desember)' : 'Lebih Dipotong (Restitusi)' }} (9 - 10)</strong>
                </td>
                <td class="amt-col" style="color: {{ $reconciliation['difference'] > 0 ? '#b91c1c' : '#047857' }};">
                    <strong>Rp {{ number_format(abs($reconciliation['difference']), 0, ',', '.') }}</strong>
                </td>
            </tr>
        </tbody>
    </table>

    <table class="footer-sig">
        <tr>
            <td style="width: 60%; font-size: 10px;">
                <em>Formulir ini diterbitkan secara otomatis dan sah melalui Sistem ERP Terpadu Genie ({{ $company['name'] ?? 'PT AGUNG TRANS SOLUSINDO' }}) sesuai ketentuan perpajakan Republik Indonesia (UU HPP & PMK No. 168/2023).</em>
            </td>
            <td style="width: 40%; text-align: center; font-size: 10px;">
                Jakarta, 31 Desember {{ $reconciliation['year'] }}<br>
                <strong>Pemotong Pajak / Kuasa,</strong><br><br><br><br>
                <u><strong>Direktur Keuangan & Operasional</strong></u><br>
                {{ $company['name'] ?? 'PT AGUNG TRANS SOLUSINDO' }}
            </td>
        </tr>
    </table>
</div>

</body>
</html>
