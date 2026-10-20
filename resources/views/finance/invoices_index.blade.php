@extends('layouts.app')

@section('title', 'Finance — Invoicing Klien (P19) & Talangan Pabean')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 pt-6 pb-12" x-data="coocaTableSort(0, false)">

    <!-- 1. ZONE 1: BREADCRUMB, HEADER & EXECUTIVE KPI SUMMARY -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <nav class="flex items-center gap-1.5 text-xs text-slate-500 font-medium mb-1.5" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-sky-600 transition-colors">Dashboard</a>
                <span class="text-slate-300">/</span>
                <a href="{{ route('finance.index') }}" class="hover:text-sky-600 transition-colors">Finance</a>
                <span class="text-slate-300">/</span>
                <span class="text-slate-800 font-semibold">Invoice Klien (P19)</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Invoice Penagihan Klien (P19)</h1>
            <p class="text-xs text-slate-500 mt-0.5">Rekapitulasi talangan pabean (reimbursable), biaya pelayaran, jasa freight forwarder / PPJK, dan PPN 1.1%.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('finance.index') }}" 
               class="min-h-[42px] px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition-all active:scale-95 inline-flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Pusat Keuangan</span>
            </a>
        </div>
    </div>

    <!-- Apple HIG Bento Grid KPI Summary -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
        <!-- KPI 1: Total Invoice -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5 hover:border-slate-300 transition-all">
            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Invoice</div>
                <div class="text-xl font-extrabold text-slate-900 font-mono">{{ $stats['total'] ?? $invoices->total() }}</div>
                <div class="text-[10px] text-slate-400">Seluruh berkas terbit</div>
            </div>
        </div>

        <!-- KPI 2: Lunas (Paid) -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5 hover:border-slate-300 transition-all">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Lunas (Paid)</div>
                <div class="text-xl font-extrabold text-emerald-700 font-mono">{{ $stats['paid'] ?? 0 }}</div>
                <div class="text-[10px] text-slate-400">Pembayaran terverifikasi</div>
            </div>
        </div>

        <!-- KPI 3: Menunggu Pelunasan -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5 hover:border-slate-300 transition-all">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Menunggu Bayar</div>
                <div class="text-xl font-extrabold text-amber-700 font-mono">{{ $stats['unpaid'] ?? 0 }}</div>
                <div class="text-[10px] text-slate-400">Piutang belum tertagih</div>
            </div>
        </div>

        <!-- KPI 4: Total Nilai Tagihan -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5 hover:border-slate-300 transition-all">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Nilai Tagihan</div>
                <div class="text-lg font-extrabold text-purple-800 font-mono truncate">Rp {{ number_format($stats['total_amount'] ?? 0, 0, ',', '.') }}</div>
                <div class="text-[10px] text-slate-400">Akumulasi seluruh invoice</div>
            </div>
        </div>
    </div>

    <!-- 2. ZONE 2: FILTER TOOLBAR -->
    <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-slate-200/80 shadow-xs mb-6">
        <form action="{{ route('finance.invoices.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center text-xs">
            <div class="sm:col-span-6 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari Nomor Invoice, Nama Klien / Importir, No Project..." 
                       class="w-full pl-9 pr-3.5 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-sky-500 bg-white">
            </div>

            <div class="sm:col-span-3">
                <select name="status" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-sky-500 bg-white font-medium text-slate-700">
                    <option value="">-- Semua Status Pembayaran --</option>
                    <option value="UNPAID" {{ request('status') === 'UNPAID' ? 'selected' : '' }}>MENUNGGU PEMBAYARAN</option>
                    <option value="PAID" {{ request('status') === 'PAID' ? 'selected' : '' }}>LUNAS (PAID)</option>
                    <option value="OVERDUE" {{ request('status') === 'OVERDUE' ? 'selected' : '' }}>JATUH TEMPO</option>
                </select>
            </div>

            <div class="sm:col-span-3 flex items-center gap-2">
                <button type="submit" class="flex-1 min-h-[38px] px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-xl transition-all shadow-xs cursor-pointer active:scale-95">
                    Filter
                </button>
                <a href="{{ route('finance.invoices.index') }}" class="min-h-[38px] px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition-all flex items-center justify-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- 3. ZONE 3: DATA LEDGER TABLE -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden mb-8 text-xs">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
            <span class="font-bold text-slate-900 text-sm">Daftar Tagihan Invoice Klien</span>
            <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 font-mono text-[11px] font-bold">Total: {{ $invoices->total() }} invoice</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50/90 text-slate-500 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4 cursor-pointer hover:text-slate-800 transition-colors" @click="sortBy(0, 'string', $event)">
                            <div class="flex items-center gap-1">
                                <span>No. Invoice & Tanggal</span>
                                <svg class="w-3 h-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                            </div>
                        </th>
                        <th class="py-3 px-4">Klien (Importir)</th>
                        <th class="py-3 px-4">Job Order & BL</th>
                        <th class="py-3 px-4 text-right cursor-pointer hover:text-slate-800 transition-colors" @click="sortBy(3, 'number', $event)">
                            <div class="flex items-center justify-end gap-1">
                                <span>Talangan Pabean</span>
                                <svg class="w-3 h-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                            </div>
                        </th>
                        <th class="py-3 px-4 text-right cursor-pointer hover:text-slate-800 transition-colors" @click="sortBy(4, 'number', $event)">
                            <div class="flex items-center justify-end gap-1">
                                <span>Total Tagihan</span>
                                <svg class="w-3 h-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                            </div>
                        </th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($invoices as $inv)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 font-mono" data-sort-value="{{ $inv->invoice_number ?? $inv->invoice_no }}">
                                <a href="{{ route('finance.invoices.show', $inv->id) }}" class="font-bold text-sky-600 hover:text-sky-800 hover:underline block">
                                    {{ $inv->invoice_number ?? $inv->invoice_no }}
                                </a>
                                <div class="text-[10px] text-slate-400 font-sans mt-0.5">{{ $inv->invoice_date ? $inv->invoice_date->format('d/m/Y') : '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800">
                                {{ $inv->client->name ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-600">
                                {{ $inv->shipment->project_no ?? $inv->shipment->shipment_number ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-semibold text-slate-700" data-sort-value="{{ $inv->reimbursable_amount ?? $inv->talangan_pabean_idr ?? 0 }}">
                                Rp {{ number_format($inv->reimbursable_amount ?? $inv->talangan_pabean_idr ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-900" data-sort-value="{{ $inv->total_amount ?? $inv->total_tagihan_idr ?? 0 }}">
                                Rp {{ number_format($inv->total_amount ?? $inv->total_tagihan_idr ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($inv->status === 'PAID')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> LUNAS
                                    </span>
                                @elseif($inv->jatuh_tempo && $inv->jatuh_tempo->isPast())
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span> JATUH TEMPO
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span> MENUNGGU
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1.5 whitespace-nowrap">
                                <a href="{{ route('finance.invoices.show', $inv->id) }}"
                                   class="inline-flex items-center px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 font-bold text-[11px] transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                                    Detail &rarr;
                                </a>
                                <a href="{{ route('pdf.invoice', $inv->id) }}" target="_blank"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-sky-50 text-sky-700 hover:bg-sky-100 font-bold text-[11px] transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-600">
                                    <svg class="w-3.5 h-3.5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                    <span>Cetak PDF</span>
                                </a>
                                @if($inv->status !== 'PAID')
                                    <form action="{{ route('finance.invoices.pay', $inv->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Tandai invoice {{ $inv->invoice_number ?? $inv->invoice_no }} sebagai lunas?')">
                                        @csrf
                                        <input type="hidden" name="payment_date" value="{{ now()->format('Y-m-d') }}">
                                        <input type="hidden" name="payment_method" value="Bank Transfer BCA">
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700 cursor-pointer">
                                            Tandai Lunas
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="w-10 h-10 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center text-lg mb-2">
                                    📄
                                </div>
                                <div class="font-bold text-slate-700 text-xs">Belum ada invoice penagihan klien yang diterbitkan</div>
                                <p class="text-[11px] text-slate-500 mt-0.5">Invoice diterbitkan otomatis setelah dokumen pabean dan biaya operasional shipment tervalidasi.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
            {{ $invoices->links() }}
        </div>
    </div>

</div>
@endsection
