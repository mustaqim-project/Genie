@extends('layouts.app')

@section('title', 'Accounting - Bagan Akun COA & Jurnal')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="text-xs text-slate-500 font-medium mb-1">
                <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a> / <span class="text-slate-800">Accounting</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Accounting & Jurnal Otomatis (General Ledger)</h1>
            <p class="text-xs text-slate-500 mt-0.5">Bagan akun standar (COA), auto-journaling dari transaksi pabean/pelayaran, neraca, dan laba rugi</p>
        </div>
        <div>
            <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 border border-slate-200 text-xs font-semibold">
                Auto-Journal Engine: ACTIVE
            </span>
        </div>
    </div>

    <!-- P&L Mini Widget -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6 text-xs">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-slate-400 font-bold uppercase block mb-1">Pendapatan Usaha (Revenue)</span>
            <span class="text-2xl font-black text-slate-900">Rp {{ number_format($revenue, 0, ',', '.') }}</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-slate-400 font-bold uppercase block mb-1">Beban Pokok Operasional (HPP)</span>
            <span class="text-2xl font-black text-rose-600">Rp {{ number_format($expense, 0, ',', '.') }}</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
            <span class="text-slate-400 font-bold uppercase block mb-1">Laba Bersih Berjalan (Net Profit)</span>
            <span class="text-2xl font-black text-emerald-600">Rp {{ number_format($netProfit, 0, ',', '.') }}</span>
        </div>
    </div>

    <!-- Table Jurnal Otomatis -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <span class="font-bold text-xs text-slate-800">Buku Jurnal Umum Otomatis (Auto-Generated Journal Entries)</span>
            <span class="text-xs text-slate-400">Sinkron dari Dokumen Pabean</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-mono">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-100 font-sans">
                    <tr>
                        <th class="py-3 px-4">Tanggal & No Bukti</th>
                        <th class="py-3 px-4">Kode Akun (COA)</th>
                        <th class="py-3 px-4">Keterangan Transaksi</th>
                        <th class="py-3 px-4 text-right">Debit (IDR)</th>
                        <th class="py-3 px-4 text-right">Kredit (IDR)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($journals as $j)
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-800">{{ $j->created_at->format('Y-m-d') }}</div>
                                <div class="text-[10px] text-slate-400 font-sans">JU-{{ $j->id }}</div>
                            </td>
                            <td class="py-3 px-4 text-slate-700">
                                <div>1101 - Kas & Bank Utama</div>
                                <div class="text-[10px] text-slate-400">4101 - Pendapatan Jasa Forwarder</div>
                            </td>
                            <td class="py-3 px-4 font-sans text-slate-600">
                                Realisasi pengurusan pabean nomor aju {{ $j->nomor_aju }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-slate-900">
                                Rp {{ number_format($j->total_nilai_idr, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-slate-900">
                                Rp {{ number_format($j->total_nilai_idr, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">Belum ada entri jurnal akuntansi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
