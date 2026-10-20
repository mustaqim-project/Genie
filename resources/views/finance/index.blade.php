@extends('layouts.app')

@section('title', 'Finance - Ikhtisar Keuangan & Tagihan')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 pt-6 pb-12">
    <!-- Header & Quick Navigation -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <div class="text-xs text-slate-500 font-medium mb-1 flex items-center gap-1.5">
                <a href="{{ route('dashboard') }}" class="hover:underline text-slate-600">Dashboard</a>
                <span>/</span>
                <span class="text-slate-800 font-semibold">Finance & Tagihan</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Pusat Keuangan & Penagihan Logistik</h1>
            <p class="text-xs text-slate-500 mt-0.5">Penagihan invoice klien (P19), hutang vendor operasional (AP), likuiditas kas & bank, serta perpajakan</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('finance.invoices.index') }}" 
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold shadow-xs transition-all active:scale-95">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Daftar Invoice P19</span>
            </a>
            <a href="{{ route('finance.bills.index') }}" 
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-800 border border-rose-200/80 text-xs font-semibold shadow-xs transition-all active:scale-95">
                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                </svg>
                <span>Hutang Vendor (AP)</span>
            </a>
            <a href="{{ route('finance.cash-bank.index') }}" 
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-sky-50 hover:bg-sky-100 text-sky-800 border border-sky-200/80 text-xs font-semibold shadow-xs transition-all active:scale-95">
                <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <span>Kas & Bank</span>
            </a>
        </div>
    </div>

    <!-- Apple HIG Bento Grid KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <!-- Piutang Berjalan (AR) -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-[0_1px_3px_rgba(0,0,0,0.02),0_4px_16px_rgba(0,0,0,0.02)] hover:border-slate-300 transition-all group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500">Total Piutang Berjalan (AR)</span>
                <span class="p-2 rounded-xl bg-amber-50 text-amber-600 group-hover:scale-105 transition-transform">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div class="text-3xl font-extrabold font-mono text-slate-900 tracking-tight">
                Rp {{ number_format($totalAr, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-slate-500 mt-2 flex items-center justify-between pt-2 border-t border-slate-100">
                <span>Tagihan Belum Lunas</span>
                <span class="font-bold text-amber-700">Piutang AR</span>
            </div>
        </div>

        <!-- Billing Pabean Terbayar -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-[0_1px_3px_rgba(0,0,0,0.02),0_4px_16px_rgba(0,0,0,0.02)] hover:border-slate-300 transition-all group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500">Billing Pabean Terbayar</span>
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600 group-hover:scale-105 transition-transform">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div class="text-3xl font-extrabold font-mono text-emerald-600 tracking-tight">
                Rp {{ number_format($totalBilling, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-slate-500 mt-2 flex items-center justify-between pt-2 border-t border-slate-100">
                <span>NTPN MPN G3 Terverifikasi</span>
                <span class="font-bold text-emerald-700">Talangan Pass-Through</span>
            </div>
        </div>

        <!-- Kas & Bank Likuiditas Real-Time -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-[0_1px_3px_rgba(0,0,0,0.02),0_4px_16px_rgba(0,0,0,0.02)] hover:border-slate-300 transition-all group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500">Likuiditas Kas & Bank</span>
                <span class="p-2 rounded-xl bg-sky-50 text-sky-600 group-hover:scale-105 transition-transform">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </span>
            </div>
            <div class="text-3xl font-extrabold font-mono text-sky-700 tracking-tight">
                Rp {{ number_format($totalCashBank, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-slate-500 mt-2 flex items-center justify-between pt-2 border-t border-slate-100">
                <span>Saldo Rekening Aktif</span>
                <span class="font-bold text-sky-700">BCA, Mandiri, Kas Kecil</span>
            </div>
        </div>
    </div>

    <!-- Table Invoicing P19 -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-[0_1px_3px_rgba(0,0,0,0.02),0_4px_16px_rgba(0,0,0,0.02)] overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-900 text-sm">Daftar Tagihan Shipment Klien (Invoice P19)</h3>
                <p class="text-xs text-slate-500 mt-0.5">Penagihan jasa kepabeanan PPJK, trucking, serta penggantian talangan pabean</p>
            </div>
            <a href="{{ route('finance.invoices.index') }}" class="text-xs font-bold text-sky-600 hover:text-sky-700 transition-colors">
                Kelola Semua Invoice &rarr;
            </a>
        </div>
        
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/90 text-slate-500 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">Nomor Invoice & Shipment</th>
                        <th class="py-3 px-4">Nama Importir / Klien</th>
                        <th class="py-3 px-4 text-right">Talangan Pabean (IDR)</th>
                        <th class="py-3 px-4 text-right">Total Tagihan (IDR)</th>
                        <th class="py-3 px-4 text-center">Status Pembayaran</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($invoices as $inv)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-semibold text-slate-900">
                                <a href="{{ route('finance.invoices.show', $inv->id) }}" class="font-bold text-sky-600 hover:underline">
                                    {{ $inv->invoice_number ?? $inv->invoice_no }}
                                </a>
                                <div class="text-[10px] text-slate-400 font-sans">
                                    Shipment: {{ $inv->shipment?->shipment_number ?? $inv->shipment?->project_no ?? '-' }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-medium text-slate-800">
                                {{ $inv->client->name ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-semibold text-slate-700">
                                Rp {{ number_format($inv->talangan_pabean_idr ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-900">
                                Rp {{ number_format($inv->total_tagihan_idr ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($inv->status === 'PAID')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Lunas (PAID)
                                    </span>
                                @elseif($inv->jatuh_tempo && $inv->jatuh_tempo->isPast())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                        Jatuh Tempo
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        Menunggu Pelunasan
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1.5 whitespace-nowrap">
                                <a href="{{ route('finance.invoices.show', $inv->id) }}" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] transition-colors">
                                    Detail &rarr;
                                </a>
                                <a href="{{ route('pdf.invoice', $inv->id) }}" target="_blank" class="px-2.5 py-1.5 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-700 font-bold text-[11px] transition-colors">
                                    PDF
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">Belum ada invoice penagihan aktif.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
