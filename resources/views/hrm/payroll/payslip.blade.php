<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji - {{ $payslip->employee?->full_name }} ({{ $payslip->run?->period ?: date('Y-m') }})</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #F8FAFC;
            color: #0F172A;
        }
        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }
        @media print {
            body {
                background-color: #FFFFFF !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .print-border-none {
                border: none !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>
<body class="py-8 px-4 sm:px-6">

    <!-- Top Action Bar (Hide in Print) -->
    <div class="max-w-[800px] mx-auto mb-6 flex items-center justify-between no-print">
        <a href="javascript:history.back()" class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 transition shadow-xs">
            &larr; Kembali
        </a>

        <div class="flex items-center space-x-2">
            <button onclick="window.print()" class="px-5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition flex items-center space-x-2 shadow-sm shadow-slate-900/20 cursor-pointer">
                <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak / Simpan PDF</span>
            </button>
        </div>
    </div>

    <!-- Official Printable Payslip Container -->
    <div class="max-w-[800px] mx-auto bg-white border border-slate-200/90 rounded-3xl p-8 sm:p-10 shadow-lg print-border-none space-y-6">

        <!-- 1. Header Perusahaan & Logo -->
        <div class="flex items-start justify-between border-b border-slate-200 pb-6 flex-wrap gap-4">
            <div class="flex items-center space-x-3.5">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-slate-900 to-sky-700 flex items-center justify-center text-white font-black text-xl shadow-md">
                    G
                </div>
                <div>
                    <h1 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">PT AGUNG TRANS SOLUSINDO</h1>
                    <p class="text-xs text-slate-500 font-medium">International Freight Forwarding & PPJK Indonesia</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Kawasan Pelabuhan Tanjung Priok & Bandara Soekarno-Hatta</p>
                </div>
            </div>

            <div class="text-right">
                <div class="inline-block px-3 py-1 rounded-xl bg-purple-50 border border-purple-200 text-purple-800 text-xs font-mono font-bold">
                    SLIP GAJI RESMI
                </div>
                <div class="text-xs font-mono font-bold text-slate-800 mt-1.5">
                    Periode: {{ $payslip->run?->period ?: date('Y-m') }}
                </div>
                <div class="text-[10px] text-slate-400">Diterbitkan: {{ now()->translatedFormat('d F Y') }}</div>
            </div>
        </div>

        <!-- 2. Data Pegawai Identitas -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100 text-xs">
            <div>
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">NIK</span>
                <span class="font-mono font-bold text-slate-800">{{ $payslip->employee?->nik }}</span>
            </div>
            <div>
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Nama Lengkap</span>
                <span class="font-bold text-slate-800">{{ $payslip->employee?->full_name }}</span>
            </div>
            <div>
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Jabatan / Posisi</span>
                <span class="font-bold text-slate-800">{{ $payslip->employee?->position }}</span>
            </div>
            <div>
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Departemen / Tim</span>
                <span class="font-bold text-slate-800">{{ $payslip->employee?->team }}</span>
            </div>
            <div class="pt-2">
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Rekening Bank</span>
                <span class="font-bold text-slate-800">{{ $payslip->employee?->bank_name ?: 'BCA' }} - {{ $payslip->employee?->bank_account_number ?: '-' }}</span>
            </div>
            <div class="pt-2">
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Status PTKP</span>
                <span class="font-bold text-slate-800">{{ $payslip->employee?->tax_ptkp_status ?: 'TK/0' }}</span>
            </div>
            <div class="pt-2">
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">BPJS Ketenagakerjaan</span>
                <span class="font-mono text-slate-700">{{ $payslip->employee?->bpjs_tk_number ?: '-' }}</span>
            </div>
            <div class="pt-2">
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">BPJS Kesehatan</span>
                <span class="font-mono text-slate-700">{{ $payslip->employee?->bpjs_kes_number ?: '-' }}</span>
            </div>
        </div>

        <!-- 3. Rincian Penerimaan vs Potongan (2 Kolom Bersandingan) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">

            <!-- Kolom Kiri: Penerimaan & Tunjangan -->
            <div class="space-y-3">
                <div class="flex items-center justify-between pb-2 border-b-2 border-emerald-500">
                    <span class="font-extrabold uppercase tracking-wider text-emerald-800 text-[11px]">A. Penerimaan (Earnings)</span>
                    <span class="text-[10px] font-bold text-emerald-600">Nominal</span>
                </div>

                <div class="space-y-2">
                    @forelse($payslip->lines->where('kind', 'ALLOWANCE') as $line)
                        <div class="flex justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-700">{{ $line->label }}</span>
                            <span class="font-mono font-semibold text-slate-900">
                                Rp {{ number_format((float) $line->amount, 0, ',', '.') }}
                            </span>
                        </div>
                    @empty
                        <div class="flex justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-700">Gaji Pokok & Tunjangan</span>
                            <span class="font-mono font-semibold text-slate-900">
                                Rp {{ number_format((float) $payslip->gross, 0, ',', '.') }}
                            </span>
                        </div>
                    @endforelse
                </div>

                <div class="flex justify-between pt-2 border-t border-slate-200 font-bold text-slate-900">
                    <span>Total Penghasilan Kotor (Gross):</span>
                    <span class="font-mono text-sm text-emerald-700">Rp {{ number_format((float) $payslip->gross, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Kolom Kanan: Potongan Resmi -->
            <div class="space-y-3">
                <div class="flex items-center justify-between pb-2 border-b-2 border-rose-500">
                    <span class="font-extrabold uppercase tracking-wider text-rose-800 text-[11px]">B. Potongan (Deductions)</span>
                    <span class="text-[10px] font-bold text-rose-600">Nominal</span>
                </div>

                <div class="space-y-2">
                    @forelse($payslip->lines->where('kind', 'DEDUCTION') as $line)
                        <div class="flex justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-700">{{ $line->label }}</span>
                            <span class="font-mono font-semibold text-rose-600">
                                Rp {{ number_format((float) $line->amount, 0, ',', '.') }}
                            </span>
                        </div>
                    @empty
                        <div class="flex justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-700">Total Potongan Resmi</span>
                            <span class="font-mono font-semibold text-rose-600">
                                Rp {{ number_format((float) $payslip->deductions, 0, ',', '.') }}
                            </span>
                        </div>
                    @endforelse
                </div>

                <div class="flex justify-between pt-2 border-t border-slate-200 font-bold text-slate-900">
                    <span>Total Seluruh Potongan:</span>
                    <span class="font-mono text-sm text-rose-600">Rp {{ number_format((float) $payslip->deductions, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- 4. Total Gaji Bersih (Take Home Pay) Box -->
        <div class="p-6 rounded-2xl bg-slate-900 text-white flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                <span class="text-[11px] uppercase tracking-wider text-sky-400 font-bold block">
                    Penghasilan Bersih (Take Home Pay)
                </span>
                <div class="text-2xl sm:text-3xl font-extrabold font-mono text-white mt-1">
                    Rp {{ number_format((float) $payslip->net, 0, ',', '.') }}
                </div>
            </div>

            <!-- QR Code Security Badge -->
            <div class="flex items-center space-x-3 bg-slate-800/80 px-4 py-2.5 rounded-xl border border-slate-700">
                <div class="w-10 h-10 bg-white p-1 rounded-lg flex items-center justify-center">
                    <!-- SVG Simple QR Icon -->
                    <svg class="w-8 h-8 text-slate-900" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3 3h8v8H3V3zm2 2v4h4V5H5zm8-2h8v8h-8V3zm2 2v4h4V5h-4zM3 13h8v8H3v-8zm2 2v4h4v-4H5zm13-2h3v2h-3v-2zm-3 0h2v3h-2v-3zm3 3h3v5h-2v-3h-1v-2zm-5 2h3v1h-3v-1zm0 2h1v2h-1v-2zm2 0h3v2h-3v-2z"/>
                    </svg>
                </div>
                <div class="text-[10px] text-slate-300">
                    <span class="font-bold text-white block">Dokumen Sah Terverifikasi</span>
                    <span class="font-mono text-[9px] text-slate-400">SEC-ID: {{ substr(md5($payslip->id . 'GENIE_PAY'), 0, 10) }}</span>
                </div>
            </div>
        </div>

        <!-- 5. Tanda Tangan & Persetujuan -->
        <div class="grid grid-cols-2 gap-8 pt-8 text-xs text-center border-t border-slate-100">
            <div class="space-y-16">
                <p class="text-slate-500 font-medium">Penerima (Karyawan),</p>
                <div>
                    <p class="font-bold text-slate-900 border-b border-slate-300 inline-block px-8 pb-1">
                        {{ $payslip->employee?->full_name }}
                    </p>
                    <p class="text-[10px] text-slate-400 mt-0.5">NIK: {{ $payslip->employee?->nik }}</p>
                </div>
            </div>

            <div class="space-y-16">
                <p class="text-slate-500 font-medium">HR & Finance Director,</p>
                <div>
                    <p class="font-bold text-slate-900 border-b border-slate-300 inline-block px-8 pb-1">
                        PT Agung Trans Solusindo
                    </p>
                    <p class="text-[10px] text-slate-400 mt-0.5">Internal Authorized System</p>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
