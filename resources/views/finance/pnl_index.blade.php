@extends('layouts.app')

@section('title', 'Laporan PnL per Job Order - Finance & Akuntansi')

@section('content')
<div class="max-w-[1400px] mx-auto px-4 sm:px-6 space-y-6 pt-4 pb-28 lg:pb-12">
    <!-- Header & Action -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-5 sm:p-6 border border-black/[0.06] dark:border-white/[0.08] shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <nav class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 font-medium mb-1.5" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-[#0284c7] transition-colors flex items-center gap-1">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i>
                    <span>Dashboard</span>
                </a>
                <i data-lucide="chevron-right" class="w-3 h-3 text-slate-300 dark:text-slate-600"></i>
                <a href="{{ route('finance.index') }}" class="hover:text-[#0284c7] transition-colors">Finance</a>
                <i data-lucide="chevron-right" class="w-3 h-3 text-slate-300 dark:text-slate-600"></i>
                <span class="text-slate-800 dark:text-white font-semibold">Laporan PnL per Job</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Laporan Profit & Loss (PnL) per Job Order</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Analisis profitabilitas setiap shipment: Revenue Invoice dikurangi seluruh Beban DO, Storage, LOLO, Trucking & Behandle</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('finance.pnl.export', request()->all()) }}" 
               class="btn-apple-gray px-3.5 py-2 text-xs inline-flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-semibold hover:bg-emerald-50 dark:hover:bg-emerald-950/30">
                <i data-lucide="sheet" class="w-4 h-4"></i>
                <span>Export Excel (.xlsx)</span>
            </a>
            <a href="{{ route('finance.deposits.index') }}" 
               class="btn-apple-gray px-3.5 py-2 text-xs inline-flex items-center gap-1.5 text-[#0284c7]">
                <i data-lucide="wallet" class="w-4 h-4"></i>
                <span>Deposit Klien</span>
            </a>
            <a href="{{ route('finance.invoices.index') }}" 
               class="btn-apple-filled px-3.5 py-2 text-xs inline-flex items-center gap-1.5">
                <i data-lucide="receipt" class="w-4 h-4"></i>
                <span>Kelola Invoice</span>
            </a>
        </div>
    </div>

    <!-- Apple HIG Bento Grid KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Pendapatan / Revenue -->
        <div class="bg-white dark:bg-[#1C1C1E] p-5 rounded-2xl border border-black/[0.06] dark:border-white/[0.08] shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Revenue Terbit</span>
                <span class="p-2 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400">
                    <i data-lucide="trending-up" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                Rp {{ number_format($totalRevenue, 0, ',', '.') }}
            </div>
            <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400">
                Dari akumulasi seluruh invoice aktif
            </div>
        </div>

        <!-- Total Beban Biaya Langsung -->
        <div class="bg-white dark:bg-[#1C1C1E] p-5 rounded-2xl border border-black/[0.06] dark:border-white/[0.08] shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Beban Operasional</span>
                <span class="p-2 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400">
                    <i data-lucide="credit-card" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="text-2xl font-bold text-rose-600 dark:text-rose-400 tracking-tight">
                Rp {{ number_format($totalCost, 0, ',', '.') }}
            </div>
            <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400">
                Tebus DO + Storage + LOLO + Trucking
            </div>
        </div>

        <!-- Total Gross Profit -->
        <div class="bg-white dark:bg-[#1C1C1E] p-5 rounded-2xl border border-black/[0.06] dark:border-white/[0.08] shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Gross Profit (Laba Kotor)</span>
                <span class="p-2 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                    <i data-lucide="coins" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="text-2xl font-bold {{ $totalGrossProfit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} tracking-tight">
                Rp {{ number_format($totalGrossProfit, 0, ',', '.') }}
            </div>
            <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400">
                {{ $totalGrossProfit >= 0 ? 'Surplus Laba Pekerjaan' : 'Defisit Operasional' }}
            </div>
        </div>

        <!-- Margin Rata-rata -->
        <div class="bg-white dark:bg-[#1C1C1E] p-5 rounded-2xl border border-black/[0.06] dark:border-white/[0.08] shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Margin Laba Rata-rata</span>
                <span class="p-2 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400">
                    <i data-lucide="percent" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                {{ $avgMarginPct }}%
            </div>
            <div class="mt-2 text-[11px] text-slate-500 dark:text-slate-400">
                Rasio profitabilitas terhadap revenue
            </div>
        </div>
    </div>

    <!-- Filter & Pencarian Bar -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-4 sm:p-5 border border-black/[0.06] dark:border-white/[0.08] shadow-xs">
        <form method="GET" action="{{ route('finance.pnl') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <!-- Filter Client -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Pilih Klien</label>
                <select name="client_id" class="w-full text-xs rounded-xl border-slate-200 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-slate-800 dark:text-white focus:ring-[#0284c7] focus:border-[#0284c7]">
                    <option value="">-- Semua Klien --</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->nama_perusahaan }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Type -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Layanan</label>
                <select name="type" class="w-full text-xs rounded-xl border-slate-200 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-slate-800 dark:text-white focus:ring-[#0284c7] focus:border-[#0284c7]">
                    <option value="">-- Semua Jenis --</option>
                    <option value="IMPORT" {{ request('type') == 'IMPORT' ? 'selected' : '' }}>Impor (Import)</option>
                    <option value="EXPORT" {{ request('type') == 'EXPORT' ? 'selected' : '' }}>Ekspor (Export)</option>
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Periode Dari</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full text-xs rounded-xl border-slate-200 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-slate-800 dark:text-white focus:ring-[#0284c7] focus:border-[#0284c7]">
            </div>

            <!-- Date To -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">Periode Sampai</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full text-xs rounded-xl border-slate-200 dark:border-neutral-700 bg-slate-50 dark:bg-neutral-800 text-slate-800 dark:text-white focus:ring-[#0284c7] focus:border-[#0284c7]">
            </div>

            <!-- Tombol Aksi -->
            <div class="flex items-center gap-2">
                <button type="submit" class="btn-apple-filled flex-1 py-2 text-xs flex items-center justify-center gap-1.5">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Terapkan</span>
                </button>
                <a href="{{ route('finance.pnl') }}" class="btn-apple-gray py-2 px-3 text-xs flex items-center justify-center text-slate-500">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Tabel Rincian PnL Job Order -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl border border-black/[0.06] dark:border-white/[0.08] shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Rincian Profitabilitas Tiap Pekerjaan (Job Order)</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Margin laba otomatis terkalkulasi saat biaya operasional dan invoice diterbitkan</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 dark:bg-neutral-800 text-slate-600 dark:text-slate-400">
                Total {{ $shipments->total() }} Pekerjaan
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-slate-50/60 dark:bg-neutral-800/40 text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                        <th class="py-3 px-4">No. Job & Tanggal</th>
                        <th class="py-3 px-4">Klien & Layanan</th>
                        <th class="py-3 px-4">B/L / AWB & No Aju</th>
                        <th class="py-3 px-4 text-right">Revenue (Invoice)</th>
                        <th class="py-3 px-4 text-right">Beban DO</th>
                        <th class="py-3 px-4 text-right">Biaya Lapangan</th>
                        <th class="py-3 px-4 text-right">Total Cost</th>
                        <th class="py-3 px-4 text-right">Gross Profit</th>
                        <th class="py-3 px-4 text-center">Margin %</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.04] text-slate-700 dark:text-slate-300">
                    @forelse($shipments as $shipment)
                        @php
                            $rev = (float) $shipment->invoices->where('status', '!=', 'VOID')->sum('total_tagihan_idr');
                            $doCost = (float) ($shipment->biaya_tebus_do_idr ?? 0);
                            $opsCost = (float) $shipment->operationCosts->sum('nominal_idr');
                            $totCost = $doCost + $opsCost;
                            $profit = $rev - $totCost;
                            $margin = $rev > 0 ? round(($profit / $rev) * 100, 1) : ($profit < 0 ? -100 : 0);
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-neutral-800/30 transition-colors">
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-900 dark:text-white">{{ $shipment->project_no ?? 'SHIP-'.$shipment->id }}</span>
                                <div class="text-[10px] text-slate-400 mt-0.5">{{ $shipment->created_at ? $shipment->created_at->format('d M Y') : '-' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $shipment->client->nama_perusahaan ?? 'Non-Client' }}</span>
                                <div class="mt-0.5">
                                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded {{ $shipment->type == 'IMPORT' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' }}">
                                        {{ $shipment->type ?? 'IMPORT' }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-3 px-4 font-mono text-[11px]">
                                <div>{{ $shipment->bl_number ?? $shipment->m_awb ?? '-' }}</div>
                                <div class="text-[10px] text-slate-400">{{ $shipment->nomor_aju ?? 'Aju: -' }}</div>
                            </td>
                            <td class="py-3 px-4 text-right font-semibold text-blue-600 dark:text-blue-400">
                                Rp {{ number_format($rev, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right text-slate-600 dark:text-slate-400">
                                Rp {{ number_format($doCost, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right text-slate-600 dark:text-slate-400">
                                Rp {{ number_format($opsCost, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-medium text-rose-600 dark:text-rose-400">
                                Rp {{ number_format($totCost, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold {{ $profit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                Rp {{ number_format($profit, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $margin >= 20 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : ($margin >= 5 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400') }}">
                                    {{ $margin }}%
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('core.traffic.index') }}" title="Lihat Biaya Operasional di Traffic" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white transition-colors">
                                        <i data-lucide="truck" class="w-4 h-4"></i>
                                    </a>
                                    <a href="{{ route('finance.invoices.index') }}" title="Lihat Invoice P19" class="p-1 rounded-lg text-slate-400 hover:text-blue-600 transition-colors">
                                        <i data-lucide="receipt" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-8 text-center text-slate-400 dark:text-slate-500">
                                <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                                Belum ada data transaksi atau pekerjaan yang cocok dengan filter yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($shipments->hasPages())
            <div class="p-4 border-t border-black/[0.06] dark:border-white/[0.08]">
                {{ $shipments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection