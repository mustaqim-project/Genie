@extends('layouts.app')

@section('title', 'Dashboard Kepabeanan & Logistik')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 pt-6 pb-12" x-data="{ docFilter: 'ALL' }">

    <!-- 0. APPLE MINIMALIST BREADCRUMB BAR -->
    <nav class="flex items-center gap-1.5 text-[12px] text-slate-500 py-0.5 whitespace-nowrap print:hidden mb-3" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors font-medium">Dashboard</a>
        <svg class="w-3.5 h-3.5 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-slate-800 font-medium">Cockpit Operasional CEISA 4.0</span>
    </nav>

    <!-- 1. TOP HERO BANNER & ACTION COCKPIT (COOCA APPLE HIG v2.0 AESTHETICS) -->
    <div class="relative overflow-hidden rounded-[20px] p-6 sm:p-8 text-white shadow-xl mb-8 border border-slate-800"
         style="background: radial-gradient(circle at 90% 10%, rgba(0, 122, 255, 0.22), transparent 45%), radial-gradient(circle at 10% 90%, rgba(88, 86, 214, 0.18), transparent 45%), #0A0F1D;">
        
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="max-w-2xl">
                <!-- Eyebrow Status Pill (Apple Translucent Badge) -->
                <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-white/10 text-sky-300 text-xs font-semibold backdrop-blur-md border border-white/10 mb-3.5 shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-[#34C759] animate-pulse"></span>
                    <span class="tracking-wide font-mono">Host-to-Host CEISA 4.0 DJBC Gateway v2.0</span>
                </div>

                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight text-white leading-tight">
                    Pusat Integrasi Kepabeanan & Smart Logistics
                </h1>
                <p class="text-slate-300 text-xs sm:text-sm mt-2 leading-relaxed">
                    Automasi pertukaran dokumen pabean impor (BC 2.0) & ekspor (BC 3.0), manajemen DO pelayaran, traffic peti kemas, dan akuntansi logistik berstandar industri Indonesia.
                </p>
            </div>

            <!-- Action Buttons Toolbar ("Button-in-Button" & Apple Action Toolbar) -->
            <div class="flex flex-wrap items-center gap-3" x-data="{ quickActionMenu: false }">
                <a href="{{ route('bc20.create') }}" 
                   class="btn-apple-filled active:scale-95 group text-xs sm:text-sm">
                    <svg class="w-4 h-4 text-sky-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                    </svg>
                    <span>Draft BC 2.0 (Impor)</span>
                    <span class="w-6 h-6 rounded-lg bg-white/20 flex items-center justify-center group-hover:translate-x-0.5 transition-transform flex-shrink-0 ml-1">
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </span>
                </a>

                <a href="{{ route('bc30.create') }}" 
                   class="min-h-[44px] px-4 py-2.5 rounded-[12px] bg-white/10 hover:bg-white/15 text-white text-xs sm:text-sm font-semibold border border-white/15 backdrop-blur-md transition-all flex items-center gap-2 group active:scale-95">
                    <svg class="w-4 h-4 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                    </svg>
                    <span>Draft BC 3.0 (Ekspor)</span>
                </a>

                <!-- Quick Action Hub Popover (COOCA Cockpit Pattern) -->
                <div class="relative">
                    <button type="button" @click="quickActionMenu = !quickActionMenu"
                            class="min-h-[44px] px-3.5 py-2.5 rounded-[12px] bg-white/10 hover:bg-white/15 text-white text-xs font-semibold border border-white/15 backdrop-blur-md transition-all flex items-center gap-1.5 active:scale-95"
                            aria-label="Menu Aksi Cepat">
                        <span>Aksi Cepat</span>
                        <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="{ 'rotate-180': quickActionMenu }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Dropdown Popover (Apple Translucent Sheet) -->
                    <div x-show="quickActionMenu" x-cloak @click.outside="quickActionMenu = false"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 translate-y-1"
                         class="absolute right-0 mt-2 w-56 rounded-[16px] bg-slate-900/95 backdrop-blur-2xl border border-white/10 shadow-[0_16px_36px_rgba(0,0,0,0.5)] p-1.5 z-30 divide-y divide-white/10 text-xs">
                        <div class="py-1">
                            <a href="{{ route('finance.bills.index') }}" 
                               class="w-full min-h-[38px] px-3 py-2 rounded-[10px] text-slate-200 hover:bg-white/10 flex items-center gap-2.5 transition">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Catat Tagihan Vendor</span>
                            </a>
                            <a href="{{ route('finance.cash-bank.index') }}" 
                               class="w-full min-h-[38px] px-3 py-2 rounded-[10px] text-slate-200 hover:bg-white/10 flex items-center gap-2.5 transition">
                                <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                <span>Pindah Buku Kas / Bank</span>
                            </a>
                        </div>
                        <div class="py-1">
                            <a href="{{ route('finance.tax.index') }}" 
                               class="w-full min-h-[38px] px-3 py-2 rounded-[10px] text-slate-200 hover:bg-white/10 flex items-center gap-2.5 transition">
                                <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Rekap PPN 1.1% & PPh 23</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. APPLE HIG BENTO STATISTICS GRID (COOCA CORE STANDARD) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        
        <!-- KPI 1: BC 2.0 Impor Card -->
        <div class="kpi-card group cursor-default">
            <div class="kpi-icon kpi-icon-blue">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                </svg>
            </div>
            <div class="kpi-body min-w-0 flex-1">
                <span class="kpi-label">Impor (BC 2.0 PIB)</span>
                <div class="kpi-value">
                    {{ $totalBC20 }} <span class="text-xs font-semibold text-slate-400 font-sans">Dokumen</span>
                </div>
                <div class="kpi-foot">
                    CIF: <b>Rp {{ number_format($totalCifIdr, 0, ',', '.') }}</b>
                </div>
            </div>
        </div>

        <!-- KPI 2: BC 3.0 Ekspor Card -->
        <div class="kpi-card group cursor-default">
            <div class="kpi-icon kpi-icon-violet">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                </svg>
            </div>
            <div class="kpi-body min-w-0 flex-1">
                <span class="kpi-label">Ekspor (BC 3.0 PEB)</span>
                <div class="kpi-value">
                    {{ $totalBC30 }} <span class="text-xs font-semibold text-slate-400 font-sans">Dokumen</span>
                </div>
                <div class="kpi-foot">
                    FOB: <b>Rp {{ number_format($totalFobIdr, 0, ',', '.') }}</b>
                </div>
            </div>
        </div>

        <!-- KPI 3: Persetujuan Terbit (SPPB & NPE) -->
        <div class="kpi-card group cursor-default">
            <div class="kpi-icon kpi-icon-green">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="kpi-body min-w-0 flex-1">
                <span class="kpi-label">Persetujuan Terbit</span>
                <div class="kpi-value text-[#34C759]">
                    {{ $totalSppb + $totalNpe }} <span class="text-xs font-semibold text-slate-400 font-sans">Dokumen</span>
                </div>
                <div class="kpi-foot font-mono">
                    SPPB: <b>{{ $totalSppb }}</b> · NPE: <b>{{ $totalNpe }}</b>
                </div>
            </div>
        </div>

        <!-- KPI 4: Billing MPN G3 Pabean -->
        <div class="kpi-card group cursor-default">
            <div class="kpi-icon kpi-icon-amber">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div class="kpi-body min-w-0 flex-1">
                <span class="kpi-label">Billing MPN G3 Pabean</span>
                <div class="kpi-value text-[#FF9500]">
                    {{ $totalBillingAktif }} <span class="text-xs font-semibold text-slate-400 font-sans">Aktif</span>
                </div>
                <div class="kpi-foot">
                    Status: <b class="text-amber-700">Menunggu Pelunasan</b>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. MAIN WORKSPACE GRID: RECENT DOCUMENTS & CEISA API LOGS -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        
        <!-- Recent Documents Table (2 Columns) with DOM In-Place Sorting (COOCA Engine) -->
        <div class="lg:col-span-2 bg-white rounded-[20px] border border-slate-200/80 shadow-[0_1px_2px_rgba(0,0,0,0.02)] overflow-hidden"
             x-data="coocaTableSort(0, false)">
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="font-bold text-slate-900 text-sm">Pengajuan Dokumen Kepabeanan Terkini</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Daftar dokumen BC 2.0 (PIB) dan BC 3.0 (PEB) yang aktif diproses</p>
                </div>
                
                <!-- Apple Segmented Control Filter Tabs -->
                <div class="flex items-center gap-2">
                    <div class="inline-flex p-0.5 rounded-[9px] bg-slate-100 text-[12px] font-medium">
                        <button type="button" @click="docFilter = 'ALL'" 
                                :class="docFilter === 'ALL' ? 'period-seg period-seg-active' : 'period-seg'">Semua</button>
                        <button type="button" @click="docFilter = '20'" 
                                :class="docFilter === '20' ? 'period-seg period-seg-active' : 'period-seg'">BC 2.0</button>
                        <button type="button" @click="docFilter = '30'" 
                                :class="docFilter === '30' ? 'period-seg period-seg-active' : 'period-seg'">BC 3.0</button>
                    </div>
                    <a href="{{ route('bc20.index') }}" class="text-xs font-bold text-[#007AFF] hover:underline whitespace-nowrap ml-1">
                        Lihat Semua &rarr;
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/90 text-slate-500 font-semibold border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4 cursor-pointer hover:text-slate-800 transition-colors" @click="sortBy(0, 'string', $event)">
                                <div class="flex items-center gap-1">
                                    <span>Nomor Aju & Tanggal</span>
                                    <svg class="w-3 h-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                                </div>
                            </th>
                            <th class="py-3 px-4">Jenis</th>
                            <th class="py-3 px-4 cursor-pointer hover:text-slate-800 transition-colors" @click="sortBy(2, 'string', $event)">
                                <div class="flex items-center gap-1">
                                    <span>Mitra Luar Negeri</span>
                                    <svg class="w-3 h-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                                </div>
                            </th>
                            <th class="py-3 px-4 text-right cursor-pointer hover:text-slate-800 transition-colors" @click="sortBy(3, 'number', $event)">
                                <div class="flex items-center justify-end gap-1">
                                    <span>Nilai Pabean</span>
                                    <svg class="w-3 h-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                                </div>
                            </th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentDocuments as $doc)
                            <tr class="hover:bg-slate-50/60 transition-colors" 
                                x-show="docFilter === 'ALL' || docFilter === '{{ $doc->jenis_dokumen }}'">
                                <td class="py-3.5 px-4 font-mono font-semibold text-slate-900" data-sort-value="{{ $doc->nomor_aju }}">
                                    <a href="{{ $doc->jenis_dokumen === '20' ? route('bc20.show', $doc->id) : route('bc30.show', $doc->id) }}" class="text-[#007AFF] hover:underline">
                                        {{ $doc->nomor_aju }}
                                    </a>
                                    <div class="text-[10px] text-slate-400 font-sans">
                                        {{ $doc->created_at ? $doc->created_at->format('d M Y, H:i') : '-' }}
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-[6px] text-[10px] font-bold font-mono {{ $doc->jenis_dokumen === '20' ? 'bg-[#007AFF]/10 text-[#007AFF]' : 'bg-[#5856D6]/10 text-[#5856D6]' }}">
                                        {{ $doc->jenis_dokumen === '20' ? 'BC 2.0' : 'BC 3.0' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-medium text-slate-700 truncate max-w-[160px]" data-sort-value="{{ $doc->nama_mitra_luar_negeri ?? '' }}">
                                    <div class="font-semibold text-slate-900 truncate">{{ $doc->nama_mitra_luar_negeri ?? '-' }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $doc->negara_mitra_luar_negeri ?? 'NEGARA LN' }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-right font-semibold text-slate-900 font-mono" data-sort-value="{{ $doc->total_nilai }}">
                                    {{ $doc->kode_valuta }} {{ number_format($doc->total_nilai, 2) }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $doc->status_badge_class }}">
                                        {{ $doc->status }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="{{ $doc->jenis_dokumen === '20' ? route('bc20.show', $doc->id) : route('bc30.show', $doc->id) }}" 
                                       class="px-2.5 py-1.5 rounded-[8px] bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-[11px] transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                                        Detail &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-14 text-center">
                                    <div class="max-w-xs mx-auto flex flex-col items-center">
                                        <div class="w-11 h-11 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 mb-2.5 shadow-2xs">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <h3 class="text-xs font-bold text-slate-900">Belum Ada Dokumen Pabean Aktif</h3>
                                        <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">Mulai buat pengajuan dokumen impor PIB (BC 2.0) atau ekspor PEB (BC 3.0) untuk terhubung ke CEISA 4.0 DJBC.</p>
                                        <div class="mt-3.5 flex items-center gap-2">
                                            <a href="{{ route('bc20.create') }}" class="min-h-[36px] px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-[11px] transition-all shadow-xs active:scale-95 inline-flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                <span>Buat BC 2.0</span>
                                            </a>
                                            <a href="{{ route('bc30.create') }}" class="min-h-[36px] px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-[11px] transition-all active:scale-95">
                                                <span>Buat BC 3.0</span>
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent API Logs (1 Column) -->
        <div class="bg-white rounded-[20px] border border-slate-200/80 shadow-[0_1px_2px_rgba(0,0,0,0.02)] overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-slate-900 text-sm">Audit Trail Gateway CEISA 4.0</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Komunikasi H2H API dengan DJBC</p>
                </div>
                <a href="{{ route('config.index') }}" class="text-xs font-bold text-[#007AFF] hover:underline transition-colors">
                    Semua Log
                </a>
            </div>

            <div class="p-4 flex-1 space-y-3 overflow-y-auto max-h-[380px] custom-scrollbar">
                @forelse($recentLogs as $log)
                    <div class="p-3 rounded-[14px] border border-slate-100 bg-slate-50/60 text-xs hover:border-slate-200 transition-colors">
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center space-x-1.5 truncate">
                                <span class="px-1.5 py-0.5 rounded-[6px] font-mono font-bold text-[10px] {{ $log->method === 'POST' ? 'bg-[#34C759]/12 text-[#248A3D]' : 'bg-[#007AFF]/12 text-[#007AFF]' }}">
                                    {{ $log->method }}
                                </span>
                                <span class="font-mono text-slate-700 truncate max-w-[140px] text-[11px]">{{ $log->endpoint }}</span>
                            </div>
                            <span class="px-2 py-0.5 rounded-full font-bold text-[10px] font-mono {{ $log->status_code === 200 ? 'bg-[#34C759]/12 text-[#248A3D] border border-[#34C759]/20' : 'bg-[#FF3B30]/12 text-[#FF3B30] border border-[#FF3B30]/20' }}">
                                {{ $log->status_code }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-slate-400 text-[10px] font-mono">
                            <span>Durasi: {{ $log->duration_ms ?? 0 }} ms</span>
                            <span>{{ $log->created_at ? $log->created_at->diffForHumans() : '-' }}</span>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 mx-auto mb-2.5">
                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                            </svg>
                        </div>
                        <p class="text-xs font-semibold text-slate-800">Menunggu Aktivitas H2H</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Gunakan tombol di bawah untuk menguji koneksi token OAuth DJBC.</p>
                    </div>
                @endforelse
            </div>

            <div class="p-4 bg-slate-50/80 border-t border-slate-100">
                <form action="{{ route('config.test-auth') }}" method="POST">
                    @csrf
                    <button type="submit" 
                            class="w-full min-h-[44px] px-3 rounded-[12px] bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs transition-all shadow-xs flex items-center justify-center space-x-2 active:scale-95">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span>Uji Koneksi Token OAuth DJBC</span>
                    </button>
                </form>
            </div>
        </div>

    </div>

</div>
@endsection
