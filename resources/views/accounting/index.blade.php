@extends('layouts.app')

@section('title', 'Accounting & Laporan Keuangan - GENIE')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 pt-6 pb-12" x-data="{ currentTab: '{{ $activeTab }}' }">

    <!-- Header & Period Filter -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <div class="text-xs text-slate-500 font-medium mb-1 flex items-center gap-1.5">
                <a href="{{ route('finance.index') }}" class="hover:underline text-slate-600">Finance</a>
                <span>/</span>
                <span class="text-slate-800 font-semibold">Buku Besar & Laporan Keuangan</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Akuntansi, Laba Rugi, Neraca & Job Costing</h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Buku Besar Double-Entry Otomatis, Laporan Laba Rugi Forwarding (PMK 71/2022), Neraca Seimbang, dan Analisis Margin per Pengiriman
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Filter Tanggal -->
            <form method="GET" action="{{ route('accounting.index') }}" class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-xl border border-slate-200 shadow-xs text-xs">
                <input type="hidden" name="tab" :value="currentTab">
                <span class="text-slate-400 font-semibold">Periode:</span>
                <input type="date" name="date_from" value="{{ $dateFrom->format('Y-m-d') }}" class="bg-transparent border-0 font-medium text-slate-700 focus:ring-0 cursor-pointer text-xs p-0" onchange="this.form.submit()">
                <span class="text-slate-300">s/d</span>
                <input type="date" name="date_to" value="{{ $dateTo->format('Y-m-d') }}" class="bg-transparent border-0 font-medium text-slate-700 focus:ring-0 cursor-pointer text-xs p-0" onchange="this.form.submit()">
            </form>

            <!-- Export CSV Buku Jurnal -->
            <a href="{{ route('accounting.export-ledger', ['date_from' => $dateFrom->format('Y-m-d'), 'date_to' => $dateTo->format('Y-m-d')]) }}" 
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold shadow-xs transition-all">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Export Jurnal (CSV)</span>
            </a>
        </div>
    </div>

    <!-- Apple HIG Bento Grid KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Pendapatan Usaha (Revenue) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs relative overflow-hidden group hover:border-slate-300 transition-all">
            <div class="flex items-center justify-between text-xs text-slate-500 font-medium mb-2">
                <span class="uppercase tracking-wider font-bold text-[10px] text-sky-700">Pendapatan Usaha</span>
                <span class="p-1.5 rounded-lg bg-sky-50 text-sky-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </span>
            </div>
            <div class="text-2xl font-bold font-mono text-slate-900 tracking-tight">
                Rp {{ number_format($totalRevenue, 0, ',', '.') }}
            </div>
            <div class="mt-2 text-[11px] text-slate-500 flex items-center justify-between">
                <span>Jasa PPJK, Trucking & Handling</span>
                <span class="font-bold text-sky-600">Akun 4-xxxx</span>
            </div>
        </div>

        <!-- Beban Pokok Operasional (HPP) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs relative overflow-hidden group hover:border-slate-300 transition-all">
            <div class="flex items-center justify-between text-xs text-slate-500 font-medium mb-2">
                <span class="uppercase tracking-wider font-bold text-[10px] text-rose-700">Beban Pokok (HPP)</span>
                <span class="p-1.5 rounded-lg bg-rose-50 text-rose-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
                </span>
            </div>
            <div class="text-2xl font-bold font-mono text-rose-600 tracking-tight">
                Rp {{ number_format($totalHpp, 0, ',', '.') }}
            </div>
            <div class="mt-2 text-[11px] text-slate-500 flex items-center justify-between">
                <span>DO Pelayaran, Subkon & TPS</span>
                <span class="font-bold text-rose-600">Akun 5-1xxx</span>
            </div>
        </div>

        <!-- Laba Kotor (Gross Profit) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs relative overflow-hidden group hover:border-slate-300 transition-all">
            <div class="flex items-center justify-between text-xs text-slate-500 font-medium mb-2">
                <span class="uppercase tracking-wider font-bold text-[10px] text-emerald-700">Laba Kotor (Gross Profit)</span>
                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                    {{ $grossMarginPct }}%
                </span>
            </div>
            <div class="text-2xl font-bold font-mono text-emerald-600 tracking-tight">
                Rp {{ number_format($grossProfit, 0, ',', '.') }}
            </div>
            <div class="mt-2 text-[11px] text-slate-500 flex items-center justify-between">
                <span>Revenue dikurangi HPP</span>
                <span class="font-bold text-emerald-600">Margin Sehat</span>
            </div>
        </div>

        <!-- Laba Bersih Berjalan (Net Profit) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs relative overflow-hidden group hover:border-slate-300 transition-all">
            <div class="flex items-center justify-between text-xs text-slate-500 font-medium mb-2">
                <span class="uppercase tracking-wider font-bold text-[10px] text-indigo-700">Laba Bersih Berjalan</span>
                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-800">
                    {{ $netMarginPct }}%
                </span>
            </div>
            <div class="text-2xl font-bold font-mono text-indigo-600 tracking-tight">
                Rp {{ number_format($netOperatingProfit, 0, ',', '.') }}
            </div>
            <div class="mt-2 text-[11px] text-slate-500 flex items-center justify-between">
                <span>Setelah Beban OPEX</span>
                <span class="font-bold text-indigo-600">Bottom Line</span>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center space-x-2 border-b border-slate-200 mb-6 text-xs font-semibold">
        <button type="button" @click="currentTab = 'pnl'"
                :class="currentTab === 'pnl' ? 'border-sky-600 text-sky-700 border-b-2 font-bold pb-2.5' : 'text-slate-500 hover:text-slate-800 pb-2.5'">
            1. Laporan Laba Rugi (P&L)
        </button>
        <button type="button" @click="currentTab = 'balance_sheet'"
                :class="currentTab === 'balance_sheet' ? 'border-sky-600 text-sky-700 border-b-2 font-bold pb-2.5' : 'text-slate-500 hover:text-slate-800 pb-2.5'">
            2. Neraca Keuangan (Balance Sheet)
        </button>
        <button type="button" @click="currentTab = 'gl'"
                :class="currentTab === 'gl' ? 'border-sky-600 text-sky-700 border-b-2 font-bold pb-2.5' : 'text-slate-500 hover:text-slate-800 pb-2.5'">
            3. Buku Besar & Jurnal Umum (GL)
        </button>
        <button type="button" @click="currentTab = 'job_costing'"
                :class="currentTab === 'job_costing' ? 'border-sky-600 text-sky-700 border-b-2 font-bold pb-2.5' : 'text-slate-500 hover:text-slate-800 pb-2.5'">
            4. Job Costing per Shipment (Margin)
        </button>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 1: LAPORAN LABA RUGI (PROFIT & LOSS STATEMENT) -->
    <!-- ========================================================================= -->
    <div x-show="currentTab === 'pnl'" class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden p-6 text-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-6 border-b border-slate-100 gap-2">
            <div>
                <h3 class="font-bold text-sm text-slate-900">Laporan Laba Rugi Komprehensif (Income Statement)</h3>
                <p class="text-[11px] text-slate-500">Periode: {{ $dateFrom->translatedFormat('d F Y') }} s/d {{ $dateTo->translatedFormat('d F Y') }}</p>
            </div>
            <span class="px-2.5 py-1 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold text-[11px] self-start sm:self-auto">
                Gross Margin: {{ $grossMarginPct }}% | Net Margin: {{ $netMarginPct }}%
            </span>
        </div>

        <div class="space-y-6">
            <!-- 1. PENDAPATAN USAHA -->
            <div>
                <div class="flex items-center justify-between font-bold text-slate-900 pb-2 border-b border-slate-200 uppercase tracking-wider text-[11px]">
                    <span>I. Pendapatan Usaha Logistik & PPJK (Revenue)</span>
                    <span>Total (IDR)</span>
                </div>
                <div class="divide-y divide-slate-100 mt-2">
                    @foreach($revenueAccounts as $acc)
                        <div class="flex items-center justify-between py-2 text-slate-700 hover:bg-slate-50/50 px-2 rounded-lg">
                            <span class="font-mono text-slate-500">{{ $acc->code }} - <span class="text-slate-800 font-sans font-medium">{{ $acc->name }}</span></span>
                            <span class="font-mono font-semibold text-slate-900">Rp {{ number_format($acc->total_amount, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="flex items-center justify-between pt-3 mt-2 border-t border-slate-200 font-bold text-sky-800 text-xs px-2">
                    <span>Total Pendapatan Usaha (A)</span>
                    <span class="font-mono text-sm">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- 2. BEBAN POKOK OPERASIONAL (HPP) -->
            <div>
                <div class="flex items-center justify-between font-bold text-slate-900 pb-2 border-b border-slate-200 uppercase tracking-wider text-[11px]">
                    <span>II. Beban Pokok Operasional Forwarding (Cost of Goods Sold / HPP)</span>
                    <span>Total (IDR)</span>
                </div>
                <div class="divide-y divide-slate-100 mt-2">
                    @foreach($hppAccounts as $acc)
                        <div class="flex items-center justify-between py-2 text-slate-700 hover:bg-slate-50/50 px-2 rounded-lg">
                            <span class="font-mono text-slate-500">{{ $acc->code }} - <span class="text-slate-800 font-sans font-medium">{{ $acc->name }}</span></span>
                            <span class="font-mono font-semibold text-rose-600">Rp {{ number_format($acc->total_amount, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="flex items-center justify-between pt-3 mt-2 border-t border-slate-200 font-bold text-rose-800 text-xs px-2">
                    <span>Total Beban Pokok Forwarding (B)</span>
                    <span class="font-mono text-sm">Rp {{ number_format($totalHpp, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- LABA KOTOR -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between font-bold text-xs">
                <div>
                    <span class="text-slate-800 text-sm">LABA KOTOR OPERASIONAL (A - B)</span>
                    <div class="text-[10px] text-slate-500 font-normal">Margin Laba Kotor Usaha: {{ $grossMarginPct }}%</div>
                </div>
                <span class="text-base font-mono font-bold text-emerald-600">
                    Rp {{ number_format($grossProfit, 0, ',', '.') }}
                </span>
            </div>

            <!-- 3. BEBAN USAHA & ADMINISTRASI UMUM (OPEX) -->
            <div>
                <div class="flex items-center justify-between font-bold text-slate-900 pb-2 border-b border-slate-200 uppercase tracking-wider text-[11px]">
                    <span>III. Beban Usaha & Administrasi Umum (Operating Expenses / OPEX)</span>
                    <span>Total (IDR)</span>
                </div>
                <div class="divide-y divide-slate-100 mt-2">
                    @forelse($opexAccounts as $acc)
                        <div class="flex items-center justify-between py-2 text-slate-700 hover:bg-slate-50/50 px-2 rounded-lg">
                            <span class="font-mono text-slate-500">{{ $acc->code }} - <span class="text-slate-800 font-sans font-medium">{{ $acc->name }}</span></span>
                            <span class="font-mono font-semibold text-slate-900">Rp {{ number_format($acc->total_amount, 0, ',', '.') }}</span>
                        </div>
                    @empty
                        <div class="py-2 px-2 text-slate-400 italic">Belum ada realisasi beban administrasi umum untuk periode ini.</div>
                    @endforelse
                </div>
                <div class="flex items-center justify-between pt-3 mt-2 border-t border-slate-200 font-bold text-slate-800 text-xs px-2">
                    <span>Total Beban Operasional Umum (C)</span>
                    <span class="font-mono text-sm">Rp {{ number_format($totalOpex, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- LABA BERSIH -->
            <div class="p-5 rounded-2xl bg-slate-900 text-white flex items-center justify-between font-bold shadow-sm">
                <div>
                    <span class="text-sm">LABA BERSIH OPERASIONAL BERJALAN (NET PROFIT)</span>
                    <div class="text-[10px] text-slate-400 font-normal">Laba Kotor dikurangi Beban Operasional Umum</div>
                </div>
                <div class="text-right">
                    <span class="text-xl font-mono text-emerald-400">
                        Rp {{ number_format($netOperatingProfit, 0, ',', '.') }}
                    </span>
                    <div class="text-[10px] text-slate-300 font-mono">Net Margin: {{ $netMarginPct }}%</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 2: NERACA KEUANGAN (BALANCE SHEET) -->
    <!-- ========================================================================= -->
    <div x-show="currentTab === 'balance_sheet'" class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden p-6 text-xs" x-cloak>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-6 border-b border-slate-100 gap-2">
            <div>
                <h3 class="font-bold text-sm text-slate-900">Neraca Posisi Keuangan (Balance Sheet)</h3>
                <p class="text-[11px] text-slate-500">Per Tanggal: {{ $dateTo->translatedFormat('d F Y') }}</p>
            </div>
            <div>
                @if($isBalanceSheetBalanced)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold text-[11px]">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        NERACA SEIMBANG (ASET = KEWAJIBAN + EKUITAS)
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 font-bold text-[11px]">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        SELISIH: Rp {{ number_format($balanceDifference, 2, ',', '.') }}
                    </span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- SISI KIRI: ASET (1-xxxx) -->
            <div class="space-y-4">
                <div class="pb-2 border-b-2 border-slate-900 font-bold text-slate-900 uppercase tracking-wider text-xs flex justify-between">
                    <span>ASET (AKTIVA)</span>
                    <span>JUMLAH (IDR)</span>
                </div>
                
                <div class="divide-y divide-slate-100">
                    @foreach($assetAccounts as $acc)
                        <div class="flex items-center justify-between py-2 text-slate-700 hover:bg-slate-50/50 px-2 rounded-lg">
                            <span class="font-mono text-slate-500 text-[11px]">{{ $acc->code }} - <span class="text-slate-800 font-sans font-medium">{{ $acc->name }}</span></span>
                            <span class="font-mono font-semibold text-slate-900">Rp {{ number_format($acc->total_amount, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="p-4 rounded-xl bg-sky-50/70 border border-sky-200 flex items-center justify-between font-bold text-xs mt-6">
                    <span class="text-sky-900 font-bold uppercase tracking-wider">TOTAL ASET</span>
                    <span class="text-base font-mono text-sky-900 font-bold">
                        Rp {{ number_format($totalAssets, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <!-- SISI KANAN: KEWAJIBAN & EKUITAS (2-xxxx & 3-xxxx) -->
            <div class="space-y-6">
                <!-- KEWAJIBAN -->
                <div>
                    <div class="pb-2 border-b-2 border-slate-900 font-bold text-slate-900 uppercase tracking-wider text-xs flex justify-between">
                        <span>KEWAJIBAN & HUTANG (PASIVA)</span>
                        <span>JUMLAH (IDR)</span>
                    </div>
                    
                    <div class="divide-y divide-slate-100 mt-2">
                        @foreach($liabilityAccounts as $acc)
                            <div class="flex items-center justify-between py-2 text-slate-700 hover:bg-slate-50/50 px-2 rounded-lg">
                                <span class="font-mono text-slate-500 text-[11px]">{{ $acc->code }} - <span class="text-slate-800 font-sans font-medium">{{ $acc->name }}</span></span>
                                <span class="font-mono font-semibold text-slate-900">Rp {{ number_format($acc->total_amount, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex items-center justify-between pt-3 mt-2 border-t border-slate-200 font-bold text-slate-800 text-xs px-2">
                        <span>Total Kewajiban</span>
                        <span class="font-mono text-sm">Rp {{ number_format($totalLiabilities, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- EKUITAS -->
                <div>
                    <div class="pb-2 border-b border-slate-300 font-bold text-slate-900 uppercase tracking-wider text-[11px] flex justify-between">
                        <span>EKUITAS & MODAL PEMILIK</span>
                        <span>JUMLAH (IDR)</span>
                    </div>

                    <div class="divide-y divide-slate-100 mt-2">
                        <div class="flex items-center justify-between py-2 text-slate-700 hover:bg-slate-50/50 px-2 rounded-lg">
                            <span class="font-mono text-slate-500 text-[11px]">3-1100 - <span class="text-slate-800 font-sans font-medium">Modal Disetor Saham</span></span>
                            <span class="font-mono font-semibold text-slate-900">Rp {{ number_format($capitalPaid, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between py-2 text-slate-700 hover:bg-slate-50/50 px-2 rounded-lg">
                            <span class="font-mono text-slate-500 text-[11px]">3-2100 - <span class="text-slate-800 font-sans font-medium">Laba Ditahan</span></span>
                            <span class="font-mono font-semibold text-slate-900">Rp {{ number_format($retainedEarnings, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between py-2 text-slate-700 hover:bg-slate-50/50 px-2 rounded-lg">
                            <span class="font-mono text-slate-500 text-[11px]">3-3100 - <span class="text-slate-800 font-sans font-medium">Laba Periode Berjalan</span></span>
                            <span class="font-mono font-semibold text-emerald-600">Rp {{ number_format($currentEarnings, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-3 mt-2 border-t border-slate-200 font-bold text-slate-800 text-xs px-2">
                        <span>Total Ekuitas</span>
                        <span class="font-mono text-sm">Rp {{ number_format($totalEquity, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-slate-100 border border-slate-300 flex items-center justify-between font-bold text-xs">
                    <span class="text-slate-900 font-bold uppercase tracking-wider">TOTAL KEWAJIBAN & EKUITAS</span>
                    <span class="text-base font-mono text-slate-900 font-bold">
                        Rp {{ number_format($totalLiabilities + $totalEquity, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 3: BUKU BESAR & JURNAL UMUM (GENERAL LEDGER) -->
    <!-- ========================================================================= -->
    <div x-show="currentTab === 'gl'" class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden" x-cloak>
        <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-xs font-bold text-slate-900">Buku Jurnal Umum Otomatis (General Ledger Entries)</h3>
                <p class="text-[11px] text-slate-500">Seluruh entri double-entry yang dihasilkan secara otomatis oleh sistem</p>
            </div>
            
            <!-- Filter GL -->
            <form method="GET" action="{{ route('accounting.index') }}" class="flex items-center gap-2">
                <input type="hidden" name="tab" value="gl">
                <select name="account_id" class="px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs font-medium text-slate-700 focus:outline-none" onchange="this.form.submit()">
                    <option value="">-- Semua Akun (COA) --</option>
                    @foreach($allCoas as $c)
                        <option value="{{ $c->id }}" {{ request('account_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->code }} - {{ $c->name }}
                        </option>
                    @endforeach
                </select>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari memo / no ref..."
                       class="px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs focus:outline-none w-44">
                <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                    Filter
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">Tanggal & Ref Jurnal</th>
                        <th class="py-3 px-4">Keterangan Memo Transaksi</th>
                        <th class="py-3 px-4">Kode & Nama Akun (COA)</th>
                        <th class="py-3 px-4 text-right">Debit (IDR)</th>
                        <th class="py-3 px-4 text-right">Kredit (IDR)</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($journalEntries as $entry)
                        @foreach($entry->lines as $idx => $line)
                            <tr class="hover:bg-slate-50/50 transition-colors {{ $idx === 0 ? 'border-t-2 border-slate-200' : '' }}">
                                @if($idx === 0)
                                    <td rowspan="{{ $entry->lines->count() }}" class="py-3 px-4 align-top bg-white border-r border-slate-100">
                                        <div class="font-bold text-slate-900 font-mono">{{ $entry->entry_date ? $entry->entry_date->format('d/m/Y') : '-' }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono">JU-{{ substr($entry->id, 0, 8) }}</div>
                                        <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-100 text-slate-600">
                                            {{ $entry->source_type }}
                                        </span>
                                    </td>
                                    <td rowspan="{{ $entry->lines->count() }}" class="py-3 px-4 align-top bg-white border-r border-slate-100 text-slate-700 leading-relaxed">
                                        <div class="font-medium">{{ $entry->memo }}</div>
                                        <div class="text-[10px] text-slate-400 mt-1">Dicatat: {{ $entry->postedBy?->name ?? 'System Auto' }}</div>
                                    </td>
                                @endif
                                <td class="py-2.5 px-4 font-mono">
                                    <span class="font-bold text-slate-700">{{ $line->account?->code ?? '-' }}</span>
                                    <span class="text-slate-500 font-sans text-[11px] ml-1">{{ $line->account?->name ?? '-' }}</span>
                                </td>
                                <td class="py-2.5 px-4 text-right font-mono font-semibold {{ $line->debit > 0 ? 'text-slate-900' : 'text-slate-300' }}">
                                    {{ $line->debit > 0 ? 'Rp ' . number_format($line->debit, 0, ',', '.') : '-' }}
                                </td>
                                <td class="py-2.5 px-4 text-right font-mono font-semibold {{ $line->credit > 0 ? 'text-slate-900' : 'text-slate-300' }}">
                                    {{ $line->credit > 0 ? 'Rp ' . number_format($line->credit, 0, ',', '.') : '-' }}
                                </td>
                                @if($idx === 0)
                                    <td rowspan="{{ $entry->lines->count() }}" class="py-3 px-4 align-top bg-white text-center">
                                        @if($entry->is_balanced)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Balanced</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">Unbalanced</span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                Belum ada entri jurnal akuntansi pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($journalEntries->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $journalEntries->links() }}
            </div>
        @endif
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 4: JOB COSTING & PROFIT MARGIN PER SHIPMENT -->
    <!-- ========================================================================= -->
    <div x-show="currentTab === 'job_costing'" class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden" x-cloak>
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-xs font-bold text-slate-900">Analisis Job Costing & Profit Margin per Pengiriman (Shipment)</h3>
                <p class="text-[11px] text-slate-500">Perbandingan riil antara pendapatan jasa forwarding vs beban langsung vendor per order order</p>
            </div>
            <span class="text-xs font-semibold text-slate-500">20 Order Pengiriman Terbaru</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">Shipment & Aju Dokumen</th>
                        <th class="py-3 px-4">Nama Importir / Klien</th>
                        <th class="py-3 px-4 text-right">Revenue Jasa (IDR)</th>
                        <th class="py-3 px-4 text-right">Beban Vendor HPP (IDR)</th>
                        <th class="py-3 px-4 text-right">Gross Margin (IDR)</th>
                        <th class="py-3 px-4 text-center">Margin (%)</th>
                        <th class="py-3 px-4 text-center">Status Keuangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($shipments as $sh)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-3 px-4 font-mono">
                                <div class="font-bold text-sky-600">{{ $sh['number'] }}</div>
                                <div class="text-[10px] text-slate-400 font-sans">Aju: {{ $sh['aju'] }} ({{ $sh['doc_type'] }})</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-semibold text-slate-800">{{ $sh['client'] }}</div>
                                <div class="text-[10px] text-slate-400">Tahap: {{ $sh['status'] }}</div>
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-semibold text-slate-900">
                                Rp {{ number_format($sh['revenue'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-semibold text-rose-600">
                                Rp {{ number_format($sh['cost'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold {{ $sh['margin_idr'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                Rp {{ number_format($sh['margin_idr'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono {{ $sh['margin_pct'] >= 20 ? 'bg-emerald-100 text-emerald-800' : ($sh['margin_pct'] > 0 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                    {{ $sh['margin_pct'] }}%
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($sh['health'] === 'HEALTHY')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Sangat Sehat</span>
                                @elseif($sh['health'] === 'LOW_MARGIN')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Margin Rendah</span>
                                @elseif($sh['health'] === 'LOSS')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">Rugi</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">Belum Ditagihkan</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">Belum ada data shipment untuk dianalisis.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
