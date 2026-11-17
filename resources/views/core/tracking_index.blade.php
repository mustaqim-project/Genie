@extends('layouts.app')

@section('title', 'Tracking Project & Workflow 11 Pos')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
    .leaflet-popup-content-wrapper {
        border-radius: 12px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.4);
    }
    .leaflet-container {
        font-family: inherit;
    }
    @keyframes radar-ping {
        75%, 100% {
            transform: scale(2.2);
            opacity: 0;
        }
    }
    .animate-radar-ping {
        animation: radar-ping 1.6s cubic-bezier(0, 0, 0.2, 1) infinite;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
@endpush

@section('content')
<div class="max-w-[1440px] mx-auto px-4 sm:px-6 space-y-6 pt-4 pb-28 lg:pb-12">

    <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Toolbar Style) -->
    <header class="rounded-[16px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-4 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-sm">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-[10px] bg-[#0284c7]/10 text-[#0284c7] flex items-center justify-center font-bold">
                    <i data-lucide="milestone" class="w-4 h-4"></i>
                </div>
                <h1 class="text-[20px] sm:text-[22px] font-bold text-black dark:text-white tracking-tight">
                    Tracking Project & Workflow 11 Pos
                </h1>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]">
                    Real-Time Cockpit
                </span>
            </div>
            <p class="text-[13px] text-black/50 dark:text-white/50 mt-1 leading-snug">
                Monitoring visual 11 pos tahapan shipment (Dokumen hingga Invoice), status penjaluran kepabeanan, SLA waktu, dan matriks penugasan PIC staf operasional.
            </p>
        </div>

        <!-- Action Toolbar -->
        <div class="flex flex-wrap items-center gap-2 w-full md:w-auto shrink-0">
            @if($kpis['incomplete_pic_count'] > 0)
            <form action="{{ route('core.tracking.auto-assign-all') }}" method="POST" onsubmit="return confirmSweetAlert(event, 'Tugaskan PIC secara otomatis dengan beban kerja terendah untuk semua proyek yang belum lengkap?');">
                @csrf
                <button type="submit" 
                        class="px-3 py-1.5 rounded-[9px] text-[12px] font-semibold bg-[#0284c7]/10 hover:bg-[#0284c7]/20 text-[#0284c7] transition-all flex items-center gap-1.5 active:scale-[0.98]">
                    <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                    <span>Auto-Assign PIC ({{ $kpis['incomplete_pic_count'] }} Proyek)</span>
                </button>
            </form>
            @endif

            <a href="{{ route('core.document.index') }}" 
               class="px-3.5 py-1.5 rounded-[9px] text-[12px] font-semibold bg-[#0284c7] text-white hover:bg-[#0062CC] transition-all flex items-center gap-1.5 shadow-[0_2px_8px_rgba(0,122,255,0.25)] active:scale-[0.98]">
                <i data-lucide="folder-plus" class="w-3.5 h-3.5"></i>
                <span>Buka Proyek Baru</span>
            </a>
        </div>
    </header>

    @if(session('success'))
    <div class="rounded-[12px] bg-[#34C759]/10 border border-[#34C759]/20 px-4 py-3 flex items-center gap-3 text-[#248A3D] dark:text-[#30D158] text-[13px] font-medium">
        <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <!-- 2. 4 APPLE HIG KPI STATS CARDS -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
        <!-- Card 1: Project Aktif -->
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase tracking-wider font-semibold text-black/45 dark:text-white/45">Project Aktif</span>
                <div class="w-8 h-8 rounded-[10px] bg-[#0284c7]/10 text-[#0284c7] flex items-center justify-center">
                    <i data-lucide="ship" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <div class="text-[22px] sm:text-[24px] font-bold tabular-nums text-black dark:text-white tracking-tight">
                    {{ $kpis['total_active'] }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5 truncate">Sedang dalam proses operasional</div>
            </div>
        </div>

        <!-- Card 2: Tahap Terlambat (Overdue SLA) -->
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase tracking-wider font-semibold text-black/45 dark:text-white/45">Overdue SLA</span>
                <div class="w-8 h-8 rounded-[10px] {{ $kpis['late_count'] > 0 ? 'bg-[#FF3B30]/10 text-[#FF3B30]' : 'bg-black/5 dark:bg-white/5 text-black/40 dark:text-white/40' }} flex items-center justify-center">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <div class="text-[22px] sm:text-[24px] font-bold tabular-nums {{ $kpis['late_count'] > 0 ? 'text-[#FF3B30]' : 'text-black dark:text-white' }} tracking-tight">
                    {{ $kpis['late_count'] }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5 truncate">Melebihi target batas waktu SLA</div>
            </div>
        </div>

        <!-- Card 3: Belum Lengkap PIC -->
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase tracking-wider font-semibold text-black/45 dark:text-white/45">PIC Belum Lengkap</span>
                <div class="w-8 h-8 rounded-[10px] {{ $kpis['incomplete_pic_count'] > 0 ? 'bg-[#FF9500]/10 text-[#FF9500]' : 'bg-[#34C759]/10 text-[#34C759]' }} flex items-center justify-center">
                    <i data-lucide="user-x" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <div class="text-[22px] sm:text-[24px] font-bold tabular-nums {{ $kpis['incomplete_pic_count'] > 0 ? 'text-[#FF9500]' : 'text-[#34C759]' }} tracking-tight">
                    {{ $kpis['incomplete_pic_count'] }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5 truncate">
                    {{ $kpis['incomplete_pic_count'] > 0 ? 'Perlu penugasan staf pelaksana' : 'Semua pos telah memiliki PIC' }}
                </div>
            </div>
        </div>

        <!-- Card 4: Tugas Saya Aktif -->
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase tracking-wider font-semibold text-black/45 dark:text-white/45">Tugas Saya Saat Ini</span>
                <div class="w-8 h-8 rounded-[10px] bg-[#5856D6]/10 text-[#5856D6] flex items-center justify-center">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <div class="text-[22px] sm:text-[24px] font-bold tabular-nums text-[#5856D6] dark:text-[#5E5CE6] tracking-tight">
                    {{ $kpis['my_tasks_count'] }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5 truncate">Pos aktif memerlukan tindakan Anda</div>
            </div>
        </div>
    </div>

    <!-- 3. MAIN SECTION: COCKPIT MATRIKS 11 POS SHIPMENT -->
    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden shadow-sm">
        
        <!-- Table Toolbar & Filters -->
        <div class="p-4 border-b border-black/5 dark:border-white/5 flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <!-- View Mode Switcher: Tracking Dok Excel vs Matrix 11 Pos -->
                <div class="inline-flex p-1 bg-[#0284c7]/10 rounded-[10px]">
                    <a href="{{ route('core.tracking.index', array_merge(request()->query(), ['mode' => 'tracking_dok'])) }}"
                       class="px-3 py-1.5 rounded-[8px] text-[12px] font-semibold transition-all flex items-center gap-1.5 {{ $mode === 'tracking_dok' ? 'bg-white dark:bg-[#2C2C2E] text-[#0284c7] shadow-sm' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                        <i data-lucide="table-2" class="w-3.5 h-3.5"></i>
                        <span>Tracking Dok (Excel)</span>
                    </a>
                    <a href="{{ route('core.tracking.index', array_merge(request()->query(), ['mode' => 'matrix'])) }}"
                       class="px-3 py-1.5 rounded-[8px] text-[12px] font-semibold transition-all flex items-center gap-1.5 {{ $mode === 'matrix' ? 'bg-white dark:bg-[#2C2C2E] text-[#0284c7] shadow-sm' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                        <i data-lucide="kanban" class="w-3.5 h-3.5"></i>
                        <span>Matriks 11 Pos</span>
                    </a>
                </div>

                <!-- Direction Filter: Semua / Import / Export -->
                <div class="inline-flex p-1 bg-black/[0.04] dark:bg-white/[0.06] rounded-[10px]">
                    <a href="{{ route('core.tracking.index', array_merge(request()->query(), ['direction' => 'all'])) }}"
                       class="px-2.5 py-1.5 rounded-[8px] text-[11px] font-semibold transition-all {{ $direction === 'all' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm' : 'text-black/60 dark:text-white/60' }}">
                        Semua
                    </a>
                    <a href="{{ route('core.tracking.index', array_merge(request()->query(), ['direction' => 'IMPORT'])) }}"
                       class="px-2.5 py-1.5 rounded-[8px] text-[11px] font-semibold transition-all {{ $direction === 'IMPORT' ? 'bg-white dark:bg-[#2C2C2E] text-[#0284c7] shadow-sm' : 'text-black/60 dark:text-white/60' }}">
                        Impor
                    </a>
                    <a href="{{ route('core.tracking.index', array_merge(request()->query(), ['direction' => 'EXPORT'])) }}"
                       class="px-2.5 py-1.5 rounded-[8px] text-[11px] font-semibold transition-all {{ $direction === 'EXPORT' ? 'bg-white dark:bg-[#2C2C2E] text-[#AF52DE] shadow-sm' : 'text-black/60 dark:text-white/60' }}">
                        Ekspor
                    </a>
                </div>

                <!-- Filter Tugas -->
                <div class="inline-flex p-1 bg-black/[0.04] dark:bg-white/[0.06] rounded-[10px]">
                    <a href="{{ route('core.tracking.index', array_merge(request()->query(), ['filter' => 'all'])) }}"
                       class="px-3 py-1.5 rounded-[8px] text-[11px] font-semibold transition-all {{ $filter !== 'me' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm' : 'text-black/60 dark:text-white/60' }}">
                        Semua ({{ $kpis['total_active'] }})
                    </a>
                    <a href="{{ route('core.tracking.index', array_merge(request()->query(), ['filter' => 'me'])) }}"
                       class="px-3 py-1.5 rounded-[8px] text-[11px] font-semibold transition-all flex items-center gap-1.5 {{ $filter === 'me' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm' : 'text-black/60 dark:text-white/60' }}">
                        <span>Tugas Saya</span>
                        @if($kpis['my_tasks_count'] > 0)
                        <span class="w-4 h-4 rounded-full bg-[#0284c7] text-white text-[10px] flex items-center justify-center font-bold">
                            {{ $kpis['my_tasks_count'] }}
                        </span>
                        @endif
                    </a>
                </div>

                <!-- Filter Prioritas Kerja (Low, Medium, High, Urgent) - Sesuai MoM Poin 10 -->
                <div class="inline-flex p-1 bg-black/[0.04] dark:bg-white/[0.06] rounded-[10px]">
                    <a href="{{ route('core.tracking.index', array_merge(request()->query(), ['priority' => ''])) }}"
                       class="px-2 py-1.5 rounded-[8px] text-[11px] font-semibold transition-all {{ !request('priority') ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm' : 'text-black/50' }}">
                        Prioritas: Semua
                    </a>
                    <a href="{{ route('core.tracking.index', array_merge(request()->query(), ['priority' => 'URGENT'])) }}"
                       class="px-2 py-1.5 rounded-[8px] text-[11px] font-bold transition-all {{ request('priority') == 'URGENT' ? 'bg-rose-600 text-white shadow-sm' : 'text-rose-600 dark:text-rose-400' }}"
                       title="Prioritas Urgent (Free time sisa <= 1 hari / SPJM Hold)">
                        Urgent
                    </a>
                    <a href="{{ route('core.tracking.index', array_merge(request()->query(), ['priority' => 'HIGH'])) }}"
                       class="px-2 py-1.5 rounded-[8px] text-[11px] font-bold transition-all {{ request('priority') == 'HIGH' ? 'bg-amber-500 text-white shadow-sm' : 'text-amber-600 dark:text-amber-400' }}"
                       title="Prioritas High (Jalur Merah / Free time sisa <= 3 hari)">
                        High
                    </a>
                    <a href="{{ route('core.tracking.index', array_merge(request()->query(), ['priority' => 'MEDIUM'])) }}"
                       class="px-2 py-1.5 rounded-[8px] text-[11px] font-semibold transition-all {{ request('priority') == 'MEDIUM' ? 'bg-blue-600 text-white shadow-sm' : 'text-blue-600 dark:text-blue-400' }}">
                        Medium
                    </a>
                </div>

                <!-- Filter Overdue Deadline - Sesuai MoM Poin 10 -->
                <a href="{{ route('core.tracking.index', array_merge(request()->query(), ['overdue' => request('overdue') == '1' ? '' : '1'])) }}"
                   class="px-2.5 py-1.5 rounded-[10px] text-[11px] font-bold transition-all flex items-center gap-1.5 border {{ request('overdue') == '1' ? 'bg-rose-50 border-rose-300 text-rose-700 dark:bg-rose-950/40 dark:border-rose-800 dark:text-rose-300' : 'bg-black/[0.04] dark:bg-white/[0.06] border-transparent text-slate-600 dark:text-slate-400 hover:text-rose-600' }}">
                    <i data-lucide="clock-alert" class="w-3.5 h-3.5 text-rose-500"></i>
                    <span>Hanya Overdue</span>
                </a>
            </div>

            <!-- Search Form -->
            <form action="{{ route('core.tracking.index') }}" method="GET" class="relative max-w-sm w-full">
                <input type="hidden" name="filter" value="{{ $filter }}">
                <input type="hidden" name="direction" value="{{ $direction }}">
                <input type="hidden" name="mode" value="{{ $mode }}">
                <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40"></i>
                <input type="text" 
                       name="search" 
                       value="{{ $search }}" 
                       placeholder="Cari Project, B/L, Kontainer, ODOO, atau Klien..." 
                       class="w-full pl-9 pr-8 py-1.5 rounded-[9px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/5 dark:border-white/10 text-[12px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-1 focus:ring-[#0284c7]">
                @if($search)
                <a href="{{ route('core.tracking.index', ['filter' => $filter, 'direction' => $direction, 'mode' => $mode]) }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                </a>
                @endif
            </form>
        </div>

        @if($mode === 'tracking_dok')
        <!-- ============================================================== -->
        <!-- MODE 1: TRACKING DOK (TAMPILAN EXCEL OPERASIONAL ATRANS)      -->
        <!-- ============================================================== -->
        <div class="overflow-x-auto custom-scrollbar relative">
            <table class="w-full text-left border-separate border-spacing-0 min-w-max text-[11px] whitespace-nowrap">
                <thead>
                    <tr class="bg-black/[0.025] dark:bg-white/[0.03] text-[10px] font-bold uppercase tracking-wider text-black/55 dark:text-white/55 whitespace-nowrap">
                        <th class="py-2.5 px-3 min-w-[260px] sticky left-0 z-30 bg-white dark:bg-[#1C1C1E] border-b border-r border-black/10 dark:border-white/10 shadow-[4px_0_8px_rgba(0,0,0,0.04)] whitespace-nowrap table-freeze-left">No &amp; Importir / Eksportir</th>
                        <th class="py-2.5 px-3 text-center border-b border-black/10 dark:border-white/10 whitespace-nowrap">Tgl Terima</th>
                        <th class="py-2.5 px-3 border-b border-black/10 dark:border-white/10 whitespace-nowrap">Kelengkapan Dokumen</th>
                        <th class="py-2.5 px-3 border-b border-black/10 dark:border-white/10 whitespace-nowrap min-w-[160px]">Komoditi &amp; No BL</th>
                        <th class="py-2.5 px-3 border-b border-black/10 dark:border-white/10 whitespace-nowrap min-w-[170px]">Vessel / Pelayaran</th>
                        <th class="py-2.5 px-3 border-b border-black/10 dark:border-white/10 whitespace-nowrap min-w-[160px]">POL / POD</th>
                        <th class="py-2.5 px-3 border-b border-black/10 dark:border-white/10 whitespace-nowrap min-w-[160px]">Vol &amp; Kontainer</th>
                        <th class="py-2.5 px-3 text-center border-b border-black/10 dark:border-white/10 whitespace-nowrap">ETA / ETD</th>
                        <th class="py-2.5 px-3 border-b border-black/10 dark:border-white/10 whitespace-nowrap min-w-[130px]">AJU &amp; NOPEN</th>
                        <th class="py-2.5 px-3 text-center border-b border-black/10 dark:border-white/10 whitespace-nowrap">Respon Pabean</th>
                        <th class="py-2.5 px-3 text-center border-b border-black/10 dark:border-white/10 whitespace-nowrap">DO / Stuffing &amp; Segel</th>
                        <th class="py-2.5 px-3 border-b border-black/10 dark:border-white/10 whitespace-nowrap min-w-[140px]">Trucking &amp; Delv</th>
                        <th class="py-2.5 px-3 text-center border-b border-black/10 dark:border-white/10 whitespace-nowrap">ODOO / Fisik</th>
                        <th class="py-2.5 px-3 text-right sticky right-0 z-30 bg-white dark:bg-[#1C1C1E] border-b border-l border-black/10 dark:border-white/10 shadow-[-6px_0_12px_rgba(0,0,0,0.06)] whitespace-nowrap min-w-[140px] table-freeze-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-0">
                    @forelse($shipments as $rowIdx => $s)
                    @php
                        $checklist = $s->tracking_checklist;
                        $responses = $s->customs_responses;
                        $isExport = $s->jenis_layanan === 'EKSPORT_BC30';
                        $photoCount = $s->inspectionPhotos->count();
                        $ceisa = $s->ceisaDocument;
                    @endphp
                    <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors whitespace-nowrap group {{ $s->is_overdue ? 'bg-rose-50/20 dark:bg-rose-950/10' : '' }}">
                        <td class="py-2.5 px-3 sticky left-0 z-20 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FA] dark:group-hover:bg-[#202022] border-b border-r border-black/5 dark:border-white/5 shadow-[4px_0_8px_rgba(0,0,0,0.04)] whitespace-nowrap table-freeze-left">
                            <div class="flex items-center gap-2 whitespace-nowrap">
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40 w-4 text-center shrink-0">{{ $rowIdx + 1 }}</span>
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold shrink-0 {{ $isExport ? 'bg-[#AF52DE]/15 text-[#AF52DE]' : 'bg-[#0284c7]/15 text-[#0284c7]' }}">
                                    {{ $isExport ? 'EXP' : 'IMP' }}
                                </span>
                                <!-- Priority Indicator Badge (Sesuai MoM Poin 10) -->
                                @php
                                    $priLevel = $s->priority_level;
                                @endphp
                                @if($priLevel === 'URGENT')
                                    <span class="px-1.5 py-0.5 rounded text-[8px] font-black uppercase tracking-wider bg-rose-600 text-white animate-pulse" title="Prioritas Sangat Mendesak!">URGENT</span>
                                @elseif($priLevel === 'HIGH')
                                    <span class="px-1.5 py-0.5 rounded text-[8px] font-bold uppercase tracking-wider bg-amber-500 text-white" title="Prioritas Tinggi">HIGH</span>
                                @endif

                                @if($s->is_overdue)
                                    <span class="px-1 py-0.5 rounded text-[8px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300 flex items-center gap-0.5" title="Pekerjaan Melebihi Batas Waktu / SLA Overdue!">
                                        <i data-lucide="clock-alert" class="w-2.5 h-2.5"></i>
                                        <span>OVERDUE</span>
                                    </span>
                                @endif

                                <a href="{{ route('core.document.show', $s->id) }}" class="font-bold text-black dark:text-white hover:text-[#0284c7] whitespace-nowrap">
                                    {{ $s->client?->name ?? 'N/A' }}
                                </a>
                            </div>
                            <div class="text-[10px] font-mono text-black/50 dark:text-white/50 mt-0.5 pl-6 whitespace-nowrap">{{ $s->project_no }}</div>
                        </td>
                        <td class="py-2.5 px-3 text-center font-mono border-b border-black/5 dark:border-white/5 whitespace-nowrap">
                            {{ ($s->doc_received_at ?? $s->created_at)?->format('d/m/y') }}
                        </td>
                        <td class="py-2.5 px-3 border-b border-black/5 dark:border-white/5 whitespace-nowrap">
                            <div class="flex items-center gap-1 whitespace-nowrap">
                                @foreach($checklist as $code => $chk)
                                @php $ok = !empty($chk['checked']); @endphp
                                <a href="{{ route('core.document.show', $s->id) }}#tab-docs"
                                   class="px-1.5 py-0.5 rounded-[5px] text-[9px] font-bold border transition-colors whitespace-nowrap {{ $ok ? 'bg-[#34C759]/15 border-[#34C759]/30 text-[#248A3D] dark:text-[#30D158]' : 'bg-black/[0.03] dark:bg-white/[0.04] border-black/10 dark:border-white/10 text-black/35 dark:text-white/35' }}"
                                   title="{{ $chk['label'] ?? $code }}: {{ $chk['status'] ?? ($ok ? 'UPLOADED' : 'MISSING') }}">
                                    {{ $chk['label'] ?? $code }}
                                </a>
                                @endforeach
                            </div>
                        </td>
                        <td class="py-2.5 px-3 border-b border-black/5 dark:border-white/5 whitespace-nowrap">
                            <div class="font-semibold text-black dark:text-white whitespace-nowrap" title="{{ $s->commodity_summary }}">
                                {{ $s->commodity_summary ?: '-' }}
                            </div>
                            <div class="font-mono text-[10px] text-[#0284c7] whitespace-nowrap" title="{{ $s->nomor_bl_awb }}">
                                {{ $s->nomor_bl_awb ?: '-' }}
                            </div>
                        </td>
                        <td class="py-2.5 px-3 border-b border-black/5 dark:border-white/5 whitespace-nowrap">
                            @php
                                $isAir = $s->is_air_freight;
                                $etaInfo = $s->eta_status_info;
                            @endphp
                            <div class="flex items-center gap-1.5 whitespace-nowrap">
                                <i data-lucide="{{ $isAir ? 'plane' : 'anchor' }}" class="w-3.5 h-3.5 {{ $isAir ? 'text-indigo-600 dark:text-indigo-400' : 'text-[#0284c7]' }} shrink-0"></i>
                                <span class="font-semibold text-black dark:text-white whitespace-nowrap">
                                    {{ $s->nama_kapal ?: ($isAir ? 'Penerbangan' : 'Kapal Belum Diset') }}
                                </span>
                                @if($s->voyage_no)
                                    <span class="px-1.5 py-0.2 rounded bg-black/5 dark:bg-white/10 font-mono text-[9px] text-black/60 dark:text-white/60">
                                        {{ $s->voyage_no }}
                                    </span>
                                @endif
                            </div>
                            <div class="text-[10px] text-black/50 dark:text-white/50 whitespace-nowrap mt-0.5">
                                {{ $s->shippingVendor?->name ?? 'Carrier Belum Diset' }}
                            </div>
                            <button type="button"
                                    onclick="openVesselTrackingModal(
                                        '{{ $s->id }}',
                                        '{{ $s->shipment_number ?? $s->project_no }}',
                                        '{{ addslashes($s->nama_kapal ?? '') }}',
                                        '{{ addslashes($s->voyage_no ?? '') }}',
                                        '{{ $s->shipping_vendor_id ?? '' }}',
                                        '{{ addslashes($s->nomor_bl_awb ?? '') }}',
                                        '{{ addslashes($s->pelabuhan_muat ?? '') }}',
                                        '{{ addslashes($s->pelabuhan_bongkar ?? '') }}',
                                        '{{ addslashes($s->tps_location ?? '') }}',
                                        '{{ $s->eta_date ? $s->eta_date->format('Y-m-d\TH:i') : '' }}',
                                        '{{ $s->actual_arrival_date ? $s->actual_arrival_date->format('Y-m-d\TH:i') : '' }}',
                                        '{{ addslashes($s->catatan ?? '') }}',
                                        {{ $isAir ? 'true' : 'false' }},
                                        {{ json_encode($s->tracking_links) }}
                                    )"
                                    class="inline-flex items-center gap-1 text-[10px] font-semibold text-[#0284c7] hover:underline cursor-pointer mt-0.5">
                                <i data-lucide="radar" class="w-3 h-3 text-[#0284c7]"></i>
                                <span>Live Radar AIS &rarr;</span>
                            </button>
                        </td>
                        <td class="py-2.5 px-3 border-b border-black/5 dark:border-white/5 whitespace-nowrap">
                            <div class="text-[10px] text-black/70 dark:text-white/70 whitespace-nowrap">{{ $s->pelabuhan_muat ?: '-' }}</div>
                            <div class="text-[10px] font-semibold text-black dark:text-white whitespace-nowrap">→ {{ $s->pelabuhan_bongkar ?: '-' }}</div>
                        </td>
                        <td class="py-2.5 px-3 border-b border-black/5 dark:border-white/5 whitespace-nowrap">
                            <div class="font-mono font-semibold text-black dark:text-white whitespace-nowrap">
                                {{ $s->total_kontainer ?? 1 }} Ctr / {{ number_format((float) $s->total_bruto, 0) }} KG
                            </div>
                            <div class="font-mono text-[10px] text-black/50 dark:text-white/50 whitespace-nowrap" title="{{ $s->nomor_kontainer }}">
                                {{ $s->nomor_kontainer ?: '-' }}
                            </div>
                        </td>
                        <td class="py-2.5 px-3 text-center font-mono border-b border-black/5 dark:border-white/5 whitespace-nowrap">
                            <button type="button"
                                    onclick="openVesselTrackingModal(
                                        '{{ $s->id }}',
                                        '{{ $s->shipment_number ?? $s->project_no }}',
                                        '{{ addslashes($s->nama_kapal ?? '') }}',
                                        '{{ addslashes($s->voyage_no ?? '') }}',
                                        '{{ $s->shipping_vendor_id ?? '' }}',
                                        '{{ addslashes($s->nomor_bl_awb ?? '') }}',
                                        '{{ addslashes($s->pelabuhan_muat ?? '') }}',
                                        '{{ addslashes($s->pelabuhan_bongkar ?? '') }}',
                                        '{{ addslashes($s->tps_location ?? '') }}',
                                        '{{ $s->eta_date ? $s->eta_date->format('Y-m-d\TH:i') : '' }}',
                                        '{{ $s->actual_arrival_date ? $s->actual_arrival_date->format('Y-m-d\TH:i') : '' }}',
                                        '{{ addslashes($s->catatan ?? '') }}',
                                        {{ $isAir ? 'true' : 'false' }},
                                        {{ json_encode($s->tracking_links) }}
                                    )"
                                    class="block mx-auto text-left cursor-pointer group"
                                    title="Klik untuk melihat live radar & mengatur jadwal ETA / konfirmasi sandar">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-semibold font-mono {{ $etaInfo['badge_class'] }}">
                                    @if($etaInfo['is_arrived'])
                                        <i data-lucide="check-circle" class="w-2.5 h-2.5 text-emerald-600"></i>
                                    @elseif($etaInfo['status'] === 'TODAY')
                                        <i data-lucide="clock" class="w-2.5 h-2.5 text-amber-600"></i>
                                    @elseif($etaInfo['status'] === 'OVERDUE')
                                        <i data-lucide="alert-triangle" class="w-2.5 h-2.5 text-rose-600"></i>
                                    @else
                                        <i data-lucide="navigation" class="w-2.5 h-2.5 text-sky-600"></i>
                                    @endif
                                    <span>{{ $etaInfo['label'] }}</span>
                                </span>
                                <div class="font-bold text-black dark:text-white text-[11px] mt-0.5 group-hover:text-[#0284c7] transition-colors">
                                    {{ $s->eta_date?->format('d/m/y') ?: '-' }}
                                </div>
                                @if($s->actual_arrival_date)
                                    <div class="text-[9px] text-emerald-600 dark:text-emerald-400 font-semibold">ATA {{ $s->actual_arrival_date->format('d/m H:i') }}</div>
                                @elseif($s->etd_date)
                                    <div class="text-[9px] text-black/45 dark:text-white/45">ETD {{ $s->etd_date->format('d/m/y') }}</div>
                                @endif
                            </button>
                        </td>
                        <td class="py-2.5 px-3 border-b border-black/5 dark:border-white/5 whitespace-nowrap">
                            <div class="font-mono text-[10px] font-semibold text-black dark:text-white whitespace-nowrap">
                                {{ $ceisa?->nomor_aju ? substr($ceisa->nomor_aju, -8) : 'Belum AJU' }}
                            </div>
                            <div class="font-mono text-[10px] text-black/55 dark:text-white/55 whitespace-nowrap">
                                {{ $ceisa?->nomor_daftar ? 'Nopen ' . $ceisa->nomor_daftar : '-' }}
                                {{ $ceisa?->tanggal_daftar ? ' (' . \Carbon\Carbon::parse($ceisa->tanggal_daftar)->format('d/m') . ')' : '' }}
                            </div>
                            @if($ceisa)
                                @if($ceisa->jenis_dokumen === '20')
                                    <a href="{{ route('bc20.edit', $ceisa->id) }}"
                                       class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded-[6px] text-[10px] font-bold bg-[#0284c7]/10 text-[#0284c7] hover:bg-[#0284c7] hover:text-white transition-colors whitespace-nowrap"
                                       title="Buka Form Edit BC 2.0 (PIB Impor)">
                                        <i data-lucide="edit-3" class="w-2.5 h-2.5"></i>
                                        <span>Edit BC 2.0</span>
                                    </a>
                                @else
                                    <a href="{{ route('bc30.show', $ceisa->id) }}"
                                       class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded-[6px] text-[10px] font-bold bg-[#AF52DE]/10 text-[#AF52DE] hover:bg-[#AF52DE] hover:text-white transition-colors whitespace-nowrap"
                                       title="Buka Dokumen BC 3.0 (PEB Ekspor)">
                                        <i data-lucide="file-text" class="w-2.5 h-2.5"></i>
                                        <span>BC 3.0</span>
                                    </a>
                                @endif
                            @endif
                        </td>
                        <td class="py-2.5 px-3 whitespace-nowrap text-center border-b border-black/5 dark:border-white/5">
                            <div class="inline-flex items-center justify-center gap-1 whitespace-nowrap">
                                @if($isExport)
                                    {{-- Respon Ekspor: NPE (Izin Muat), PPB (Periksa Fisik), PKBE (Konsolidasi) --}}
                                    @foreach(['npe' => 'NPE', 'ppb' => 'PPB', 'pkbe' => 'PKBE'] as $flagKey => $flagLabel)
                                    @php
                                        $isOn = !empty($responses[strtoupper($flagKey)]);
                                        $onClass = match($flagKey) {
                                            'npe' => 'bg-[#34C759] text-white',
                                            'ppb' => 'bg-[#FF3B30] text-white',
                                            'pkbe' => 'bg-[#FF9500] text-white',
                                            default => 'bg-[#0284c7] text-white',
                                        };
                                    @endphp
                                    <button type="button"
                                            onclick="toggleTrackingFlag('{{ $s->id }}', '{{ $flagKey }}', this)"
                                            class="px-1.5 py-0.5 rounded-[5px] text-[9px] font-bold transition-all cursor-pointer whitespace-nowrap {{ $isOn ? $onClass : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/35 dark:text-white/35 hover:text-black' }}"
                                            title="Klik untuk toggle respon ekspor {{ $flagLabel }}">
                                        {{ $flagLabel }}
                                    </button>
                                    @endforeach
                                @else
                                    {{-- Respon Impor: SPJM, SPPB, NPD, DNP, NHI --}}
                                    @foreach(['spjm' => 'SPJM', 'sppb' => 'SPPB', 'npd' => 'NPD', 'dnp' => 'DNP', 'nhi' => 'NHI'] as $flagKey => $flagLabel)
                                    @php
                                        $isOn = !empty($responses[strtoupper($flagKey)]);
                                        $onClass = match($flagKey) {
                                            'sppb' => 'bg-[#34C759] text-white',
                                            'spjm' => 'bg-[#FF3B30] text-white',
                                            'nhi' => 'bg-purple-600 text-white',
                                            default => 'bg-[#FF9500] text-white',
                                        };
                                    @endphp
                                    <button type="button"
                                            onclick="toggleTrackingFlag('{{ $s->id }}', '{{ $flagKey }}', this)"
                                            class="px-1.5 py-0.5 rounded-[5px] text-[9px] font-bold transition-all cursor-pointer whitespace-nowrap {{ $isOn ? $onClass : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/35 dark:text-white/35 hover:text-black' }}"
                                            title="Klik untuk toggle status {{ $flagLabel }}">
                                        {{ $flagLabel }}
                                    </button>
                                    @endforeach
                                @endif
                            </div>
                        </td>
                        <td class="py-2.5 px-3 text-center border-b border-black/5 dark:border-white/5 whitespace-nowrap">
                            @if($isExport)
                                <div class="text-[10px] font-semibold whitespace-nowrap {{ in_array($s->status_tahap, ['STUFFING', 'ARRIVED', 'GATE_IN', 'TRAFFIC_SPPB', 'TRAFFIC_GATE_OUT', 'VESSEL_DEPARTED', 'DELIVERED_POD', 'COMPLETED']) ? 'text-[#248A3D] dark:text-[#30D158]' : 'text-black/45 dark:text-white/45' }}">
                                    {{ in_array($s->status_tahap, ['STUFFING', 'ARRIVED', 'GATE_IN', 'TRAFFIC_SPPB', 'TRAFFIC_GATE_OUT', 'VESSEL_DEPARTED', 'DELIVERED_POD', 'COMPLETED']) ? 'Stuffing Selesai' : 'Depo / Stuffing' }}
                                </div>
                                <a href="{{ route('core.document.show', $s->id) }}#tab-ops"
                                   class="inline-flex items-center gap-1 mt-0.5 px-1.5 py-0.5 rounded text-[9px] font-semibold whitespace-nowrap {{ $photoCount > 0 ? 'bg-[#AF52DE]/15 text-[#AF52DE]' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/45 dark:text-white/45' }}">
                                    <i data-lucide="camera" class="w-2.5 h-2.5"></i>
                                    <span>{{ $photoCount }} Foto {{ $s->new_seal_number ? '/ Segel: ' . $s->new_seal_number : '' }}</span>
                                </a>
                            @else
                                <div class="text-[10px] font-semibold whitespace-nowrap {{ $s->do_is_released ? 'text-[#248A3D] dark:text-[#30D158]' : 'text-black/45 dark:text-white/45' }}">
                                    {{ $s->do_is_released ? ($s->nomor_do ?: 'DO Rilis') : 'DO Wait' }}
                                </div>
                                <a href="{{ route('core.document.show', $s->id) }}#tab-ops"
                                   class="inline-flex items-center gap-1 mt-0.5 px-1.5 py-0.5 rounded text-[9px] font-semibold whitespace-nowrap {{ $photoCount > 0 ? 'bg-[#0284c7]/15 text-[#0284c7]' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/45 dark:text-white/45' }}">
                                    <i data-lucide="camera" class="w-2.5 h-2.5"></i>
                                    <span>{{ $photoCount }} Foto {{ $s->new_seal_number ? '/ ' . $s->new_seal_number : '' }}</span>
                                </a>
                            @endif
                        </td>
                        <td class="py-2.5 px-3 border-b border-black/5 dark:border-white/5 whitespace-nowrap">
                            <div class="font-medium text-black dark:text-white whitespace-nowrap">
                                {{ $s->trucking_vendor_name ?: '-' }}
                            </div>
                            <div class="font-mono text-[10px] text-black/50 dark:text-white/50 whitespace-nowrap">
                                {{ $s->delivery_date ? 'Delv ' . $s->delivery_date->format('d/m/y') : ($s->pod_signed_at ? 'POD ' . $s->pod_signed_at->format('d/m/y') : 'Belum Delv') }}
                            </div>
                        </td>
                        <td class="py-2.5 px-3 text-center border-b border-black/5 dark:border-white/5 whitespace-nowrap">
                            <div class="inline-flex items-center gap-1 whitespace-nowrap">
                                <button type="button"
                                        onclick="toggleTrackingFlag('{{ $s->id }}', 'odoo_synced', this)"
                                        class="px-1.5 py-0.5 rounded-[5px] text-[9px] font-bold cursor-pointer whitespace-nowrap {{ $s->odoo_synced ? 'bg-[#0284c7]/15 text-[#0284c7]' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/40 dark:text-white/40' }}">
                                    {{ $s->odoo_synced ? 'ODOO ✓' : 'ODOO -' }}
                                </button>
                                <button type="button"
                                        onclick="toggleTrackingFlag('{{ $s->id }}', 'rekap_fisik', this)"
                                        class="px-1.5 py-0.5 rounded-[5px] text-[9px] font-bold cursor-pointer whitespace-nowrap {{ $s->physical_doc_archived ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/40 dark:text-white/40' }}">
                                    {{ $s->physical_doc_archived ? 'Fisik ✓' : 'Fisik Wait' }}
                                </button>
                            </div>
                        </td>
                        <td class="py-2.5 px-3 text-right sticky right-0 z-20 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FA] dark:group-hover:bg-[#202022] border-b border-l border-black/5 dark:border-white/5 shadow-[-6px_0_12px_rgba(0,0,0,0.06)] whitespace-nowrap min-w-[140px] table-freeze-right">
                            @php
                                $editPayload = [
                                    'id' => $s->id,
                                    'project_no' => $s->project_no,
                                    'customs_channel' => $s->customs_channel,
                                    'spjm_ticket' => $s->spjm_ticket,
                                    'spjm_received_at' => $s->spjm_received_at?->format('Y-m-d'),
                                    'inspection_scheduled_at' => $s->inspection_scheduled_at?->format('Y-m-d'),
                                    'old_seal_number' => $s->old_seal_number,
                                    'new_seal_number' => $s->new_seal_number,
                                    'trucking_vendor_name' => $s->trucking_vendor_name,
                                    'tps_location' => $s->tps_location,
                                    'delivery_date' => $s->delivery_date?->format('Y-m-d') ?? '',
                                ];
                            @endphp
                            @if($ceisa)
                                @if($ceisa->jenis_dokumen === '20')
                                <a href="{{ route('bc20.edit', $ceisa->id) }}"
                                   class="inline-flex items-center gap-1 px-2 py-1 rounded-[7px] text-[10px] font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white transition-colors mr-1 whitespace-nowrap"
                                   title="Buka Form Wizard BC 2.0 (PIB Impor)">
                                    <i data-lucide="file-edit" class="w-3 h-3"></i>
                                    <span>BC 2.0</span>
                                </a>
                                @elseif($ceisa->jenis_dokumen === '30')
                                <a href="{{ route('bc30.edit', $ceisa->id) }}"
                                   class="inline-flex items-center gap-1 px-2 py-1 rounded-[7px] text-[10px] font-semibold bg-amber-50 text-amber-700 hover:bg-amber-600 hover:text-white transition-colors mr-1 whitespace-nowrap"
                                   title="Buka Form Wizard BC 3.0 (PEB Ekspor)">
                                    <i data-lucide="file-edit" class="w-3 h-3"></i>
                                    <span>BC 3.0</span>
                                </a>
                                @endif
                            @endif
                            <button type="button"
                                    onclick="openVesselTrackingModal(
                                        '{{ $s->id }}',
                                        '{{ $s->shipment_number ?? $s->project_no }}',
                                        '{{ addslashes($s->nama_kapal ?? '') }}',
                                        '{{ addslashes($s->voyage_no ?? '') }}',
                                        '{{ $s->shipping_vendor_id ?? '' }}',
                                        '{{ addslashes($s->nomor_bl_awb ?? '') }}',
                                        '{{ addslashes($s->pelabuhan_muat ?? '') }}',
                                        '{{ addslashes($s->pelabuhan_bongkar ?? '') }}',
                                        '{{ addslashes($s->tps_location ?? '') }}',
                                        '{{ $s->eta_date ? $s->eta_date->format('Y-m-d\TH:i') : '' }}',
                                        '{{ $s->actual_arrival_date ? $s->actual_arrival_date->format('Y-m-d\TH:i') : '' }}',
                                        '{{ addslashes($s->catatan ?? '') }}',
                                        {{ $s->is_air_freight ? 'true' : 'false' }},
                                        {{ json_encode($s->tracking_links) }}
                                    )"
                                    class="inline-flex items-center gap-1 px-2 py-1 rounded-[7px] text-[10px] font-semibold bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 hover:bg-[#0284c7] hover:text-white transition-colors mr-1 cursor-pointer whitespace-nowrap"
                                    title="Live Tracking Radar AIS & Atur ETA">
                                <i data-lucide="radar" class="w-3 h-3"></i>
                                <span>Radar</span>
                            </button>
                            <!-- Dropdown Cetak Surat Jalan (4 Template Resmi MoM) -->
                            <div class="relative inline-block text-left" x-data="{ open: false }">
                                <button type="button" 
                                        @click="open = !open" 
                                        @click.away="open = false"
                                        class="inline-flex items-center gap-0.5 px-2 py-1 rounded-[7px] text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-600 hover:text-white transition-colors mr-1 cursor-pointer whitespace-nowrap"
                                        title="Cetak Surat Jalan Resmi (4 Template)">
                                    <i data-lucide="printer" class="w-3 h-3"></i>
                                    <span>SJ</span>
                                    <i data-lucide="chevron-down" class="w-2.5 h-2.5 opacity-60"></i>
                                </button>
                                <div x-show="open" 
                                     x-cloak
                                     class="origin-top-right absolute right-0 mt-1 w-56 rounded-xl shadow-xl bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 z-50 py-1 text-left text-xs divide-y divide-slate-100 dark:divide-white/5">
                                    <div class="px-2.5 py-1 text-[9px] font-bold text-slate-400 uppercase tracking-wider">
                                        Pilih Template Surat Jalan:
                                    </div>
                                    <a href="{{ route('pdf.surat-jalan', $s->id) }}?template=fcl" target="_blank"
                                       class="flex items-center gap-2 px-2.5 py-1.5 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-white/5">
                                        <i data-lucide="container" class="w-3.5 h-3.5 text-blue-500 shrink-0"></i>
                                        <span>1. Kontainer FCL</span>
                                    </a>
                                    <a href="{{ route('pdf.surat-jalan', $s->id) }}?template=lcl" target="_blank"
                                       class="flex items-center gap-2 px-2.5 py-1.5 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-white/5">
                                        <i data-lucide="package" class="w-3.5 h-3.5 text-amber-500 shrink-0"></i>
                                        <span>2. LCL / Air Freight</span>
                                    </a>
                                    <a href="{{ route('pdf.surat-jalan', $s->id) }}?template=inter_factory" target="_blank"
                                       class="flex items-center gap-2 px-2.5 py-1.5 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-white/5">
                                        <i data-lucide="factory" class="w-3.5 h-3.5 text-emerald-500 shrink-0"></i>
                                        <span>3. Antar Pabrik KB</span>
                                    </a>
                                    <a href="{{ route('pdf.surat-jalan', $s->id) }}?template=empty_depo" target="_blank"
                                       class="flex items-center gap-2 px-2.5 py-1.5 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-white/5">
                                        <i data-lucide="archive-restore" class="w-3.5 h-3.5 text-purple-500 shrink-0"></i>
                                        <span>4. Empty Depo Return</span>
                                    </a>
                                </div>
                            </div>
                            <button type="button"
                                    data-tracking="{{ htmlspecialchars(json_encode($editPayload), ENT_QUOTES, 'UTF-8') }}"
                                    onclick="openTrackingEditModal(this)"
                                    class="inline-flex items-center gap-1 px-2 py-1 rounded-[7px] text-[10px] font-semibold bg-[#0284c7]/10 text-[#0284c7] hover:bg-[#0284c7] hover:text-white transition-colors mr-1 cursor-pointer whitespace-nowrap">
                                Edit
                            </button>
                            <a href="{{ route('core.document.show', $s->id) }}"
                               class="inline-flex items-center gap-0.5 px-2 py-1 rounded-[7px] text-[10px] font-semibold bg-black/[0.04] dark:bg-white/[0.08] hover:bg-black hover:text-white dark:hover:bg-white dark:hover:text-black transition-colors text-black/75 dark:text-white/75 whitespace-nowrap">
                                Buka
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="15" class="py-12 text-center text-black/45 dark:text-white/45">
                            Tidak ada data Tracking Dok yang sesuai filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @else
        <!-- ============================================================== -->
        <!-- MODE 2: MATRIKS 11 POS WORKFLOW                                -->
        <!-- ============================================================== -->
        <!-- Matrix Table -->
        <div class="overflow-x-auto custom-scrollbar relative">
            <table class="w-full text-left border-separate border-spacing-0 min-w-max whitespace-nowrap">
                <thead>
                    <tr class="bg-black/[0.015] dark:bg-white/[0.02] text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45 whitespace-nowrap">
                        <th class="py-3 px-4 w-[240px] sticky left-0 z-30 bg-white dark:bg-[#1C1C1E] border-b border-r border-black/10 dark:border-white/10 shadow-[4px_0_8px_rgba(0,0,0,0.03)] whitespace-nowrap table-freeze-left">Proyek &amp; Klien</th>
                        <th class="py-3 px-3 w-[110px] text-center border-b border-black/10 dark:border-white/10 whitespace-nowrap">Status / SLA</th>
                        @foreach($stages as $idx => $stg)
                        <th class="py-3 px-2 text-center w-[85px] font-medium border-b border-black/10 dark:border-white/10 whitespace-nowrap" title="{{ $stg['title'] }} ({{ $stg['role'] }})">
                            <div class="text-[10px] leading-tight font-bold text-black/70 dark:text-white/70">{{ $idx + 1 }}</div>
                            <div class="text-[9px] tracking-normal font-normal text-black/40 dark:text-white/40">{{ $stg['title'] }}</div>
                        </th>
                        @endforeach
                        <th class="py-3 px-4 w-[100px] text-right sticky right-0 z-30 bg-white dark:bg-[#1C1C1E] border-b border-l border-black/10 dark:border-white/10 shadow-[-6px_0_12px_rgba(0,0,0,0.06)] whitespace-nowrap table-freeze-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-0 text-[12px]">
                    @forelse($shipments as $s)
                    @php
                        $currMeta = $s->current_stage_meta;
                        $stageIdx = $s->stage_index;
                        $picsMap = $s->stage_pics_map;
                        $slaStatus = $s->sla_status;
                        $customsChannel = $s->jalur_status ?? 'MENUNGGU';
                    @endphp
                    <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors group">
                        
                        <!-- Sticky Project Info -->
                        <td class="py-3 px-4 sticky left-0 z-20 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FA] dark:group-hover:bg-[#202022] border-b border-r border-black/5 dark:border-white/5 shadow-[4px_0_8px_rgba(0,0,0,0.03)] transition-colors whitespace-nowrap table-freeze-left">
                            <div class="flex items-center gap-2 whitespace-nowrap">
                                <a href="{{ route('core.document.show', $s->id) }}" class="font-bold text-black dark:text-white hover:text-[#0284c7] text-[13px] tracking-tight whitespace-nowrap">
                                    {{ $s->project_no }}
                                </a>
                                @if($s->tipe_layanan)
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 uppercase shrink-0">
                                    {{ $s->tipe_layanan }}
                                </span>
                                @endif
                            </div>
                            <div class="text-[11px] text-black/60 dark:text-white/60 font-medium mt-0.5 whitespace-nowrap">
                                {{ $s->client?->name ?? 'Klien Umum' }}
                            </div>
                            <div class="text-[10px] text-black/40 dark:text-white/40 mt-0.5 flex items-center gap-1.5 whitespace-nowrap">
                                <span>B/L: {{ $s->nomor_bl_awb ?: '-' }}</span>
                                @if($s->eta)
                                <span class="text-black/20 dark:text-white/20">/</span>
                                <span>ETA {{ \Carbon\Carbon::parse($s->eta)->format('d/m') }}</span>
                                @endif
                            </div>
                        </td>

                        <!-- SLA & Jalur Badge -->
                        <td class="py-3 px-3 text-center align-middle border-b border-black/5 dark:border-white/5 whitespace-nowrap">
                            <div class="flex flex-col items-center gap-1">
                                <!-- Jalur Kepabeanan Badge -->
                                @if(stripos($customsChannel, 'HIJAU') !== false)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                                    Hijau
                                </span>
                                @elseif(stripos($customsChannel, 'KUNING') !== false)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-[#FF9500]/15 text-[#FF9500]">
                                    Kuning
                                </span>
                                @elseif(stripos($customsChannel, 'MERAH') !== false)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-[#FF3B30]/15 text-[#FF3B30]">
                                    Merah
                                </span>
                                @else
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-black/[0.05] dark:bg-white/[0.08] text-black/50 dark:text-white/50">
                                    -
                                </span>
                                @endif

                                <!-- SLA Status Badge -->
                                @if($slaStatus === 'late')
                                <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-[#FF3B30]/10 text-[#FF3B30]" title="Melewati target SLA">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span>
                                    Late
                                </span>
                                @elseif($slaStatus === 'warn')
                                <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-[#FF9500]/10 text-[#FF9500]" title="Mendekati target SLA (80%)">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span>
                                    SLA 80%
                                </span>
                                @else
                                <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-[10px] font-medium bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]" title="SLA Aman">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                    Aman
                                </span>
                                @endif
                            </div>
                        </td>

                        <!-- 11 Pos Status Cells -->
                        @foreach($stages as $idx => $stg)
                        @php
                            $stgKey = $stg['key'];
                            $stgStatus = $s->getStageStatus($idx);
                            $picData = $picsMap[$stgKey] ?? null;
                            $picUser = $picData['user'] ?? null;
                            $picName = $picUser ? $picUser->name : null;
                            $picInitial = $picName ? collect(explode(' ', $picName))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('') : null;
                            $isMyTask = $picUser && $picUser->id === auth()->id();
                        @endphp
                        <td class="py-3 px-1 text-center align-middle border-b border-black/5 dark:border-white/5 whitespace-nowrap">
                            <div class="flex flex-col items-center justify-center">
                                @if($stgStatus === 'done')
                                    <!-- Pos Selesai -->
                                    <div class="w-6 h-6 rounded-full bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] flex items-center justify-center cursor-default transition-transform hover:scale-110"
                                         title="{{ $idx + 1 }}. {{ $stg['title'] }} — Selesai {{ $picName ? '(PIC: ' . $picName . ')' : '' }}">
                                        <i data-lucide="check" class="w-3.5 h-3.5 stroke-[2.5]"></i>
                                    </div>
                                    @if($picInitial)
                                    <span class="text-[9px] text-black/40 dark:text-white/40 mt-0.5 truncate max-w-[50px]">{{ $picInitial }}</span>
                                    @endif

                                @elseif($stgStatus === 'cur')
                                    <!-- Pos Sedang Aktif (Pulsing & Glow) -->
                                    <a href="{{ route('core.document.show', $s->id) }}#pos-assignment" 
                                       class="relative inline-flex flex-col items-center p-1 rounded-[8px] bg-[#0284c7]/10 border border-[#0284c7]/30 text-[#0284c7] hover:bg-[#0284c7]/20 transition-all hover:scale-105"
                                       title="POS AKTIF: {{ $idx + 1 }}. {{ $stg['title'] }} — PIC: {{ $picName ?: 'Belum Ada PIC' }} (Target: {{ $stg['sla_hours'] ?? 8 }} Jam)">
                                        <div class="relative flex items-center justify-center w-5 h-5 rounded-full bg-[#0284c7] text-white text-[10px] font-bold shadow-sm">
                                            @if($picInitial)
                                                <span>{{ $picInitial }}</span>
                                            @else
                                                <i data-lucide="play" class="w-2.5 h-2.5 fill-white stroke-none ml-0.5"></i>
                                            @endif
                                            <!-- Ping pulse indicator -->
                                            <span class="absolute -top-0.5 -right-0.5 flex h-2 w-2">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#0284c7] opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-2 w-2 bg-[#0284c7]"></span>
                                            </span>
                                        </div>
                                        <span class="text-[9px] font-bold text-[#0284c7] mt-0.5 leading-none">
                                            {{ $isMyTask ? 'Saya' : ($picInitial ?: '?') }}
                                        </span>
                                    </a>

                                @elseif($stgStatus === 'skip')
                                    <!-- Pos Dilewati (Misal Jalur Hijau skip Hold) -->
                                    <div class="w-6 h-6 rounded-full border border-dashed border-black/20 dark:border-white/20 text-black/30 dark:text-white/30 flex items-center justify-center text-[10px] font-medium"
                                         title="{{ $stg['title'] }} — Dilewati (Jalur Hijau)">
                                        -
                                    </div>
                                    <span class="text-[8px] text-black/30 dark:text-white/30 mt-0.5">skip</span>

                                @else
                                    <!-- Pos Menunggu (Waiting) -->
                                    <div class="w-6 h-6 rounded-full bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 text-black/35 dark:text-white/35 flex items-center justify-center text-[10px] font-medium"
                                         title="{{ $idx + 1 }}. {{ $stg['title'] }} — Menunggu {{ $picName ? '(PIC: ' . $picName . ')' : '' }}">
                                        @if($picInitial)
                                            <span class="text-[9px]">{{ $picInitial }}</span>
                                        @else
                                            <span class="w-1.5 h-1.5 rounded-full bg-black/20 dark:bg-white/20"></span>
                                        @endif
                                    </div>
                                    @if($picInitial)
                                    <span class="text-[8px] text-black/30 dark:text-white/30 mt-0.5 truncate max-w-[45px]">{{ $picInitial }}</span>
                                    @endif
                                @endif
                            </div>
                        </td>
                        @endforeach

                        <!-- Aksi (FROZEN / STICKY RIGHT) -->
                        <td class="py-3 px-4 text-right align-middle sticky right-0 z-20 bg-white dark:bg-[#1C1C1E] border-b border-l border-black/5 dark:border-white/5 shadow-[-6px_0_12px_rgba(0,0,0,0.06)] whitespace-nowrap group-hover:bg-[#F9F9FA] dark:group-hover:bg-[#202022] table-freeze-right">
                            <a href="{{ route('core.document.show', $s->id) }}" 
                               class="inline-flex items-center gap-1 px-2.5 py-1 rounded-[7px] text-[11px] font-semibold bg-black/[0.04] dark:bg-white/[0.08] hover:bg-[#0284c7] hover:text-white transition-all text-black/75 dark:text-white/75 group/btn whitespace-nowrap">
                                <span>Buka</span>
                                <i data-lucide="chevron-right" class="w-3 h-3 group-hover/btn:translate-x-0.5 transition-transform"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ 3 + count($stages) }}" class="py-12 text-center text-black/45 dark:text-white/45">
                            <div class="flex flex-col items-center justify-center">
                                <i data-lucide="check-circle" class="w-8 h-8 text-black/20 dark:text-white/20 mb-2"></i>
                                <p class="text-[14px] font-semibold text-black/60 dark:text-white/60">Tidak ada pengapalan aktif yang sesuai kriteria.</p>
                                <p class="text-[12px] text-black/40 dark:text-white/40 mt-0.5">Semua pengapalan telah selesai ditutup atau tidak cocok dengan filter pencarian.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Legend / Keterangan Visual Pos -->
        <div class="px-4 py-3 bg-black/[0.015] dark:bg-white/[0.02] border-t border-black/5 dark:border-white/5 flex flex-wrap items-center justify-between gap-3 text-[11px] text-black/50 dark:text-white/50">
            <div class="flex flex-wrap items-center gap-4">
                <span class="font-bold text-black/70 dark:text-white/70">Keterangan:</span>
                <div class="flex items-center gap-1.5">
                    <span class="w-4 h-4 rounded-full bg-[#34C759]/20 text-[#248A3D] flex items-center justify-center">
                        <i data-lucide="check" class="w-2.5 h-2.5 stroke-[2.5]"></i>
                    </span>
                    <span>Selesai</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-4 h-4 rounded-full bg-[#0284c7] text-white flex items-center justify-center text-[9px] font-bold">
                        1
                    </span>
                    <span>Pos Aktif (In-Progress)</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-4 h-4 rounded-full border border-dashed border-black/30 dark:border-white/30 text-center text-[10px] leading-3 flex items-center justify-center">
                        -
                    </span>
                    <span>Dilewati (Jalur Hijau)</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-4 h-4 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center">
                        <span class="w-1.5 h-1.5 rounded-full bg-black/30 dark:bg-white/40"></span>
                    </span>
                    <span>Menunggu Giliran (Antrean)</span>
                </div>
            </div>
            <div class="text-[11px] text-black/40 dark:text-white/40">
                Inisial huruf di bawah lingkaran menandakan staf PIC yang ditugaskan.
            </div>
        </div>
        @endif
    </div>

    <!-- 4. SECTION BEBAN KERJA TIM OPERASIONAL (TEAM WORKLOAD & PIC ALLOCATION) -->
    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden shadow-sm">
        <div class="p-4 border-b border-black/5 dark:border-white/5 flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-[16px] font-bold text-black dark:text-white tracking-tight">
                        Matriks Beban Kerja Staf Pelaksana
                    </h2>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#0284c7]/10 text-[#0284c7]">
                        {{ $workloads->count() }} Anggota Tim
                    </span>
                </div>
                <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">
                    Distribusi tugas aktif per karyawan berdasarkan peran forwarder untuk mencegah ketimpangan beban kerja.
                </p>
            </div>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse min-w-[850px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/5 bg-black/[0.015] dark:bg-white/[0.02] text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">
                        <th class="py-3 px-4">Nama Staf & NIP</th>
                        <th class="py-3 px-3">Peran Operasional</th>
                        <th class="py-3 px-3 text-center">Pos Sedang Berjalan</th>
                        <th class="py-3 px-3 text-center">Pos Mendatang</th>
                        <th class="py-3 px-3 text-center">Total Beban</th>
                        <th class="py-3 px-4">Project yang Ditangani</th>
                        <th class="py-3 px-4 text-center">Kapasitas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5 text-[12px]">
                    @forelse($workloads as $wl)
                    @php
                        $user = $wl['user'];
                        $emp = $user->employee;
                        $totalStages = $wl['total_stages'];
                        $currentActive = $wl['current_active'];
                    @endphp
                    <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors">
                        <!-- Profil Karyawan -->
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-[#0284c7]/20 to-[#5856D6]/20 border border-black/5 dark:border-white/10 flex items-center justify-center font-bold text-[#0284c7] text-[11px] shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-black dark:text-white truncate">
                                        {{ $user->name }}
                                        @if($user->id === auth()->id())
                                        <span class="ml-1 text-[10px] text-[#0284c7] font-medium">(Anda)</span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-black/40 dark:text-white/40 truncate">
                                        {{ $emp?->employee_id ?? 'NIP: -' }} / {{ $user->email }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Peran Staf -->
                        <td class="py-3 px-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70">
                                {{ $wl['role_name'] }}
                            </span>
                        </td>

                        <!-- Pos Berjalan -->
                        <td class="py-3 px-3 text-center">
                            @if($currentActive > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#0284c7]/10 text-[#0284c7]">
                                {{ $currentActive }} Pos
                            </span>
                            @else
                            <span class="text-black/30 dark:text-white/30 text-[11px]">0</span>
                            @endif
                        </td>

                        <!-- Pos Mendatang -->
                        <td class="py-3 px-3 text-center">
                            <span class="text-black/70 dark:text-white/70 tabular-nums">
                                {{ $wl['upcoming'] }} Pos
                            </span>
                        </td>

                        <!-- Total Beban -->
                        <td class="py-3 px-3 text-center font-bold tabular-nums">
                            @if($totalStages > 8)
                            <span class="text-[#FF3B30]">{{ $totalStages }}</span>
                            @elseif($totalStages > 4)
                            <span class="text-[#FF9500]">{{ $totalStages }}</span>
                            @else
                            <span class="text-[#34C759]">{{ $totalStages }}</span>
                            @endif
                        </td>

                        <!-- Proyek yang Ditangani -->
                        <td class="py-3 px-4">
                            <div class="flex flex-wrap gap-1 max-w-xs">
                                @forelse($wl['projects'] as $projNo)
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60">
                                    {{ $projNo }}
                                </span>
                                @empty
                                <span class="text-[11px] text-black/30 dark:text-white/30 italic">Belum ada penugasan</span>
                                @endforelse
                            </div>
                        </td>

                        <!-- Status Kapasitas -->
                        <td class="py-3 px-4 text-center">
                            @if($totalStages >= 8)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF3B30]/10 text-[#FF3B30]">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span>
                                Padat
                            </span>
                            @elseif($totalStages >= 4)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-[#FF9500]/10 text-[#FF9500]">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span>
                                Sedang
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                Tersedia
                            </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-black/40 dark:text-white/40">
                            Tidak ada data karyawan operasional aktif yang terdaftar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- MODAL QUICK EDIT TRACKING DOK -->
<div id="modal-tracking-edit" class="hidden fixed inset-0 z-[200] overflow-y-auto bg-black/55 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl max-w-lg w-full p-5 text-[12px] space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
            <div>
                <h3 class="text-[15px] font-bold text-black dark:text-white">Edit Cepat Tracking Dok</h3>
                <p id="tracking-edit-subtitle" class="text-[11px] text-black/50 dark:text-white/50 font-mono"></p>
            </div>
            <button type="button" onclick="document.getElementById('modal-tracking-edit').classList.add('hidden')" class="p-1.5 rounded-lg hover:bg-black/5 dark:hover:bg-white/10 cursor-pointer">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <form id="form-tracking-edit" method="POST" action="" class="space-y-3">
            @csrf
            <input type="hidden" name="action" value="update_fields">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-black/70 dark:text-white/70 mb-1">Jalur Pabean</label>
                    <select name="customs_channel" id="te-jalur" class="w-full px-3 py-1.5 rounded-[8px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E]">
                        <option value="">-- Belum Ada --</option>
                        <option value="HIJAU">HIJAU</option>
                        <option value="KUNING">KUNING</option>
                        <option value="MERAH">MERAH (SPJM)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-black/70 dark:text-white/70 mb-1">No. Tiket SPJM</label>
                    <input type="text" name="spjm_ticket" id="te-spjm-ticket" placeholder="SPJM-2026-..." class="w-full px-3 py-1.5 rounded-[8px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E]">
                </div>
                <div>
                    <label class="block font-semibold text-black/70 dark:text-white/70 mb-1">Tgl Terima SPJM</label>
                    <input type="date" name="spjm_received_at" id="te-spjm-tgl" class="w-full px-3 py-1.5 rounded-[8px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E]">
                </div>
                <div>
                    <label class="block font-semibold text-black/70 dark:text-white/70 mb-1">Jadwal Behandle</label>
                    <input type="date" name="inspection_scheduled_at" id="te-insp-tgl" class="w-full px-3 py-1.5 rounded-[8px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E]">
                </div>
                <div>
                    <label class="block font-semibold text-black/70 dark:text-white/70 mb-1">Segel Lama</label>
                    <input type="text" name="old_seal_number" id="te-old-seal" class="w-full px-3 py-1.5 rounded-[8px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E]">
                </div>
                <div>
                    <label class="block font-semibold text-black/70 dark:text-white/70 mb-1">No. Segel Baru (Pasca Periksa)</label>
                    <input type="text" name="new_seal_number" id="te-new-seal" placeholder="BC-SEAL-8821" class="w-full px-3 py-1.5 rounded-[8px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E]">
                </div>
                <div>
                    <label class="block font-semibold text-black/70 dark:text-white/70 mb-1">Vendor Trucking</label>
                    <input type="text" name="trucking_vendor_name" id="te-trucking" placeholder="PT Armada Jaya" class="w-full px-3 py-1.5 rounded-[8px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E]">
                </div>
                <div>
                    <label class="block font-semibold text-black/70 dark:text-white/70 mb-1">Tanggal Delivery</label>
                    <input type="date" name="delivery_date" id="te-delv" class="w-full px-3 py-1.5 rounded-[8px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E]">
                </div>
            </div>
            <div>
                <label class="block font-semibold text-black/70 dark:text-white/70 mb-1">Lokasi TPS / Behandle</label>
                <input type="text" name="tps_location" id="te-tps" placeholder="TPS Koja / JICT..." class="w-full px-3 py-1.5 rounded-[8px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E]">
            </div>
            <div class="pt-3 border-t border-black/5 dark:border-white/10 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-tracking-edit').classList.add('hidden')" class="btn-apple-gray !h-8 text-[12px]">Batal</button>
                <button type="submit" class="btn-apple-filled !h-8 text-[12px]">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Live Tracking Pelayaran & Penerbangan (Monitoring ETA Pos 5) -->
<div id="modal-vessel-tracking" class="hidden fixed inset-0 z-[200] overflow-y-auto bg-black/60 backdrop-blur-md flex items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="app-modal-dialog app-modal-dialog-lg rounded-t-[20px] sm:rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl p-6 text-[13px] transform transition-all max-w-2xl w-full max-h-[92vh] overflow-y-auto space-y-4">
        
        <!-- Modal Header -->
        <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-[#0284c7]/10 text-[#0284c7] flex items-center justify-center shrink-0">
                    <i data-lucide="radar" class="w-5 h-5 text-[#0284c7]"></i>
                </div>
                <div>
                    <h3 class="text-[16px] font-bold text-black dark:text-white flex items-center gap-2">
                        <span>Live Tracking Pelayaran &amp; Penerbangan</span>
                    </h3>
                    <p class="text-[11px] text-black/50 dark:text-white/50" id="trk_modal_subtitle">
                        Monitoring posisi armada, konfirmasi jadwal kedatangan (ETA) &amp; waktu sandar (ATA)
                    </p>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('modal-vessel-tracking').classList.add('hidden')" class="p-1 rounded-[8px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white hover:bg-black/[0.05] dark:hover:bg-white/[0.08] transition-colors cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- 1. LIVE RADAR SATELLITE INTERACTIVE MAP (LEAFLET ENGINE) -->
        <div class="space-y-2">
            <div class="relative rounded-2xl overflow-hidden border border-slate-700/70 shadow-lg bg-slate-950">
                <!-- Map Leaflet Canvas -->
                <div id="vessel_live_leaflet_map" class="w-full h-[250px] bg-slate-950" style="z-index: 1;"></div>
                
                <!-- Map Top-Left Overlay Pill: Status Live Sensor -->
                <div class="absolute top-2.5 left-2.5 z-[10] flex items-center gap-2 px-3 py-1 rounded-full bg-slate-900/90 backdrop-blur-md border border-white/10 text-white shadow-lg">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="font-mono text-[10px] font-bold tracking-wider text-emerald-300" id="radar_overlay_mode_badge">LIVE AIS RADAR</span>
                </div>

                <!-- Map Top-Right Button: Re-Ping Transponder -->
                <div class="absolute top-2.5 right-2.5 z-[10]">
                    <button type="button" 
                            id="btn_live_detect_arrival" 
                            onclick="triggerLiveArrivalDetection()" 
                            class="px-3 py-1 rounded-full bg-slate-900/90 hover:bg-slate-800 text-sky-400 hover:text-white backdrop-blur-md border border-white/10 text-[11px] font-semibold flex items-center gap-1.5 transition-all cursor-pointer shadow-lg active:scale-95">
                        <i data-lucide="radio" class="w-3.5 h-3.5" id="icon_live_detect_btn"></i>
                        <span id="text_live_detect_btn">Re-Ping Posisi</span>
                    </button>
                </div>

                <!-- Map Bottom Overlay: Lokasi Terkini & Koordinat Geografis -->
                <div class="absolute bottom-2.5 left-2.5 right-2.5 z-[10] flex items-center justify-between px-3.5 py-1.5 rounded-xl bg-slate-900/95 backdrop-blur-md border border-white/10 text-white text-[11px] shadow-lg">
                    <div class="flex items-center gap-2 truncate">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-rose-400 shrink-0"></i>
                        <span class="font-semibold text-slate-200 truncate" id="radar_overlay_location_label">Mendeteksi koordinat kapal/pesawat...</span>
                    </div>
                    <div class="font-mono text-[10px] text-sky-300 shrink-0 ml-2 font-bold" id="radar_overlay_coordinates">Scanning transponder...</div>
                </div>
            </div>
        </div>

        <!-- 2. KARTU TELEMETRI STATUS SARANA PENGANGKUT (Apple HIG Glassmorphism) -->
        <div id="box_live_detect_result" class="p-4 rounded-2xl bg-gradient-to-br from-slate-900 via-slate-900 to-indigo-950 text-white space-y-3.5 border border-indigo-900/60 shadow-md">
            <!-- Header Kartu: Status Operasional & Badge -->
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2.5">
                    <div id="res_live_icon_container" class="w-9 h-9 rounded-xl bg-sky-500/20 text-sky-400 flex items-center justify-center shrink-0 border border-sky-400/20">
                        <i data-lucide="ship" class="w-4 h-4" id="res_live_type_icon"></i>
                    </div>
                    <div>
                        <div class="font-bold text-[13px] sm:text-sm text-white flex items-center gap-2" id="res_live_status_title">
                            <span>Mendeteksi Lokasi &amp; Status Armada...</span>
                        </div>
                        <div class="text-[11px] text-slate-300" id="res_live_explanation">
                            Menghubungi satelit AIS &amp; transponder pelayaran internasional...
                        </div>
                    </div>
                </div>
                <span id="res_live_badge" class="px-2.5 py-1 rounded-full text-[10px] font-bold font-mono bg-sky-500/20 text-sky-300 border border-sky-500/40">
                    Scanning Radar
                </span>
            </div>

            <!-- Progress Perjalanan: Origin -> Current Position -> Destination -->
            <div class="space-y-1.5 pt-0.5">
                <div class="flex items-center justify-between text-[11px] font-medium text-slate-300">
                    <span class="flex items-center gap-1.5" id="res_live_origin_label">
                        <i data-lucide="anchor" class="w-3 h-3 text-sky-400"></i>
                        <span class="truncate max-w-[130px] sm:max-w-[200px]" id="res_origin_text">POL (Pelabuhan Muat)</span>
                    </span>
                    <span class="font-mono text-emerald-400 font-bold" id="res_live_progress_text">Progress --%</span>
                    <span class="flex items-center gap-1.5 justify-end" id="res_live_dest_label">
                        <span class="truncate max-w-[130px] sm:max-w-[200px]" id="res_dest_text">POD (Tujuan)</span>
                        <i data-lucide="map-pin" class="w-3 h-3 text-emerald-400"></i>
                    </span>
                </div>
                <div class="w-full h-2 rounded-full bg-slate-800/90 overflow-hidden border border-white/10">
                    <div id="res_live_progress_bar" class="h-full bg-gradient-to-r from-sky-500 via-indigo-500 to-emerald-400 transition-all duration-700 rounded-full" style="width: 0%"></div>
                </div>
            </div>

            <!-- 4 Indikator Telemetri Kunci -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-0.5 font-mono text-[11px]">
                <div class="p-2.5 rounded-xl bg-white/[0.04] border border-white/5">
                    <div class="text-slate-400 text-[10px]">Sarana Pengangkut</div>
                    <div id="res_live_vessel_name" class="font-bold text-white truncate">-</div>
                </div>
                <div class="p-2.5 rounded-xl bg-white/[0.04] border border-white/5">
                    <div class="text-slate-400 text-[10px]">Kecepatan Terkini</div>
                    <div id="res_live_speed" class="font-bold text-sky-300">-</div>
                </div>
                <div class="p-2.5 rounded-xl bg-white/[0.04] border border-white/5">
                    <div class="text-slate-400 text-[10px]">Sisa Jarak ke Tujuan</div>
                    <div id="res_live_dist" class="font-bold text-indigo-300">-</div>
                </div>
                <div class="p-2.5 rounded-xl bg-white/[0.04] border border-white/5">
                    <div class="text-slate-400 text-[10px]">Estimasi Tiba (ETA)</div>
                    <div id="res_live_eta" class="font-bold text-emerald-400 truncate">-</div>
                </div>
            </div>
        </div>

        <!-- 3. TAUTAN CADANGAN RADAR SATELLITE (MarineTraffic, VesselFinder, dll) -->
        <details class="group rounded-xl border border-black/10 dark:border-white/10 bg-slate-50/60 dark:bg-white/[0.02] p-3 text-xs">
            <summary class="font-semibold text-black dark:text-white cursor-pointer flex items-center justify-between select-none">
                <span class="flex items-center gap-1.5 text-sky-600 dark:text-sky-400">
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                    <span>Tautan Satelit Eksternal (MarineTraffic, VesselFinder, Official Carrier)</span>
                </span>
                <span class="text-[10px] text-black/40 dark:text-white/40 group-open:rotate-180 transition-transform">▼</span>
            </summary>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 pt-2.5" id="trk_radar_buttons_container">
                <!-- Dynamic Radar Buttons generated by JS -->
            </div>
        </details>

        <!-- Form Update Data Tracking & Tanggal ETA/ATA -->
        <form id="form-vessel-tracking" action="" method="POST" class="space-y-4">
            @csrf

            <!-- Toggle Pilihan Moda: Pelayaran (Sea) vs Penerbangan (Air) -->
            <div>
                <label class="block font-semibold text-black dark:text-white mb-1.5 text-[12px]">Moda Pengangkutan Utama</label>
                <div class="grid grid-cols-2 gap-2 text-xs font-semibold">
                    <button type="button" id="trk_btn_mode_sea" onclick="switchTrkMode(false)" class="p-2.5 rounded-xl border flex items-center justify-center gap-2 transition-all cursor-pointer">
                        <i data-lucide="anchor" class="w-4 h-4"></i>
                        <span>Pelayaran (Laut / Kapal)</span>
                    </button>
                    <button type="button" id="trk_btn_mode_air" onclick="switchTrkMode(true)" class="p-2.5 rounded-xl border flex items-center justify-center gap-2 transition-all cursor-pointer">
                        <i data-lucide="plane" class="w-4 h-4"></i>
                        <span>Penerbangan (Udara / Pesawat)</span>
                    </button>
                </div>
            </div>

            <!-- Identitas Sarana Pengangkut -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]" id="trk_lbl_vessel">Nama Kapal / Sarana Pengangkut</label>
                    <input type="text" name="nama_kapal" id="trk_input_vessel" required placeholder="Contoh: MV WAN HAI 315 atau EVER GIVEN" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#0284c7]">
                </div>
                <div>
                    <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]" id="trk_lbl_voyage">Nomor Voyage / Flight No</label>
                    <input type="text" name="voyage_no" id="trk_input_voyage" placeholder="Contoh: 042N atau GA-872" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white text-[13px] font-mono focus:outline-none focus:border-[#0284c7]">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Pelayaran / Maskapai (Carrier)</label>
                    <select name="shipping_vendor_id" id="trk_input_vendor_id" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#0284c7]">
                        <option value="">-- Pilih Vendor Pelayaran / Airline --</option>
                        @if(isset($shippingVendors))
                            @foreach($shippingVendors as $v)
                                <option value="{{ $v->id }}">{{ $v->name }} {{ $v->code ? '[' . $v->code . ']' : '' }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Nomor B/L atau Master AWB</label>
                    <input type="text" name="nomor_bl_awb" id="trk_input_bl" placeholder="Contoh: WHS1029384 atau 126-98765432" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white text-[13px] font-mono focus:outline-none focus:border-[#0284c7]">
                </div>
            </div>

            <!-- Rute Muat & Bongkar + Lokasi Terminal TPS -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Pelabuhan Muat (POL)</label>
                    <input type="text" name="pelabuhan_muat" id="trk_input_pol" placeholder="Misal: SHANGHAI (CNSHA)" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#0284c7]">
                </div>
                <div>
                    <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Pelabuhan Bongkar (POD)</label>
                    <input type="text" name="pelabuhan_bongkar" id="trk_input_pod" placeholder="Misal: TANJUNG PRIOK (IDTPP)" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#0284c7]">
                </div>
                <div>
                    <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Terminal TPS / Dermaga</label>
                    <input type="text" name="tps_location" id="trk_input_tps" placeholder="Misal: JICT 1, Koja, NPCT 1, Soetta Cargo" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#0284c7]">
                </div>
            </div>

            <!-- Jadwal Kedatangan: ETA & ATA -->
            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-white/[0.03] border border-black/10 dark:border-white/10 space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="font-bold text-black dark:text-white text-[12px] flex items-center gap-1.5">
                                <i data-lucide="calendar" class="w-3.5 h-3.5 text-[#0284c7]"></i>
                                <span>Perkiraan Waktu Tiba (ETA)</span>
                            </label>
                            <span class="text-[10px] text-slate-400">Jadwal Est.</span>
                        </div>
                        <input type="datetime-local" name="eta_date" id="trk_input_eta" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white text-[13px] font-mono focus:outline-none focus:border-[#0284c7]">
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="font-bold text-black dark:text-white text-[12px] flex items-center gap-1.5">
                                <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-600"></i>
                                <span>Kedatangan Aktual (ATA / Sandar)</span>
                            </label>
                            <button type="button" onclick="setArrivalNow()" class="text-[10px] font-semibold text-emerald-600 hover:text-emerald-700 hover:underline cursor-pointer">
                                + Tiba / Sandar Sekarang
                            </button>
                        </div>
                        <input type="datetime-local" name="actual_arrival_date" id="trk_input_ata" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white text-[13px] font-mono focus:outline-none focus:border-[#0284c7]">
                    </div>
                </div>
            </div>

            <!-- Catatan Lapangan / Posisi Terakhir -->
            <div>
                <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Catatan Posisi Kargo / Info Pelayaran</label>
                <textarea name="catatan" id="trk_input_notes" rows="2" placeholder="Catatan posisi kargo (misal: Kapal lego jangkar di Teluk Jakarta, rencana sandar dermaga 02 JICT pukul 20:00 WIB)..." class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white text-[12px] focus:outline-none focus:border-[#0284c7]"></textarea>
            </div>

            <!-- Modal Action Buttons -->
            <div class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-vessel-tracking').classList.add('hidden')" class="btn-apple-gray !h-9 text-[12px]">
                    Batal
                </button>
                <button type="submit" class="btn-apple-filled !bg-[#0284c7] hover:!bg-[#0066D6] !h-9 text-[12px] inline-flex items-center gap-1.5">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan Jadwal ETA &amp; Data Tracking</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentTrkLinks = {};
    let currentTrackingShipmentId = null;
    let leafletRadarMap = null;
    let leafletRadarMarkers = [];
    let leafletRadarPolyline = null;

    function renderLeafletRadar(pos, isAir, vesselName, statusText, locationLabel) {
        const mapContainer = document.getElementById('vessel_live_leaflet_map');
        if (!mapContainer || typeof L === 'undefined') return;

        const currLat = pos.lat || (isAir ? -6.12 : -6.05);
        const currLon = pos.lon || (isAir ? 106.65 : 106.88);

        if (!leafletRadarMap) {
            leafletRadarMap = L.map('vessel_live_leaflet_map', {
                zoomControl: true,
                attributionControl: false
            }).setView([currLat, currLon], 5);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                subdomains: ['a', 'b', 'c']
            }).addTo(leafletRadarMap);
        }

        // Bersihkan marker lama
        leafletRadarMarkers.forEach(m => {
            try { leafletRadarMap.removeLayer(m); } catch(e){}
        });
        leafletRadarMarkers = [];
        if (leafletRadarPolyline) {
            try { leafletRadarMap.removeLayer(leafletRadarPolyline); } catch(e){}
            leafletRadarPolyline = null;
        }

        const boundsPoints = [];

        // 1. Marker Asal (Origin / POL / Origin Airport)
        if (pos.origin_lat && pos.origin_lon) {
            const originHtml = `
                <div style="background: rgba(15,23,42,0.92); color: #94a3b8; padding: 2px 8px; border-radius: 8px; font-size: 10px; font-weight: bold; border: 1px solid rgba(255,255,255,0.15); white-space: nowrap; box-shadow: 0 4px 10px rgba(0,0,0,0.5); display: flex; align-items: center; gap: 4px;">
                    <span>${isAir ? '🛫' : '⚓'}</span>
                    <span>${pos.origin || 'Asal'}</span>
                </div>
            `;
            const mOrigin = L.marker([pos.origin_lat, pos.origin_lon], {
                icon: L.divIcon({ className: 'custom-radar-origin', html: originHtml, iconSize: [90, 24], iconAnchor: [45, 12] })
            }).addTo(leafletRadarMap);
            mOrigin.bindPopup(`<b>Asal / Muat:</b><br>${pos.origin || '-'}`);
            leafletRadarMarkers.push(mOrigin);
            boundsPoints.push([pos.origin_lat, pos.origin_lon]);
        }

        // 2. Marker Tujuan (POD / Dest Airport)
        if (pos.dest_lat && pos.dest_lon) {
            const destHtml = `
                <div style="background: rgba(15,23,42,0.92); color: #34d399; padding: 2px 8px; border-radius: 8px; font-size: 10px; font-weight: bold; border: 1px solid rgba(52,211,153,0.3); white-space: nowrap; box-shadow: 0 4px 10px rgba(0,0,0,0.5); display: flex; align-items: center; gap: 4px;">
                    <span>${isAir ? '🛬' : '🏁'}</span>
                    <span>${pos.destination || 'Tujuan'}</span>
                </div>
            `;
            const mDest = L.marker([pos.dest_lat, pos.dest_lon], {
                icon: L.divIcon({ className: 'custom-radar-dest', html: destHtml, iconSize: [90, 24], iconAnchor: [45, 12] })
            }).addTo(leafletRadarMap);
            mDest.bindPopup(`<b>Tujuan / Bongkar:</b><br>${pos.destination || '-'}`);
            leafletRadarMarkers.push(mDest);
            boundsPoints.push([pos.dest_lat, pos.dest_lon]);
        }

        // 3. Live Marker Kapal / Pesawat
        const vesselSymbol = isAir ? '✈️' : '🚢';
        const vesselColor = isAir ? '#818cf8' : '#38bdf8';
        const vesselHtml = `
            <div style="position: relative; display: flex; align-items: center; justify-content: center; width: 44px; height: 44px;">
                <div style="position: absolute; width: 42px; height: 42px; border-radius: 50%; background: ${isAir ? 'rgba(129,140,248,0.35)' : 'rgba(56,189,248,0.35)'}; animation: radar-ping 1.6s cubic-bezier(0,0,0.2,1) infinite;"></div>
                <div style="width: 32px; height: 32px; border-radius: 50%; background: #0f172a; border: 2.5px solid ${vesselColor}; display: flex; align-items: center; justify-content: center; font-size: 15px; box-shadow: 0 6px 16px rgba(0,0,0,0.6); z-index: 2;">
                    ${vesselSymbol}
                </div>
            </div>
        `;
        const mVessel = L.marker([currLat, currLon], {
            icon: L.divIcon({ className: 'custom-radar-vessel', html: vesselHtml, iconSize: [44, 44], iconAnchor: [22, 22] }),
            zIndexOffset: 1000
        }).addTo(leafletRadarMap);

        mVessel.bindPopup(`
            <div style="font-size: 12px; line-height: 1.4;">
                <div style="font-weight: bold; color: #0284c7;">${vesselSymbol} ${vesselName || 'Armada Kargo'}</div>
                <div style="color: #64748b; font-size: 11px;">Status: <b>${statusText || 'Dalam Pelayaran'}</b></div>
                <div style="color: #64748b; font-size: 11px;">Lokasi: ${locationLabel || '-'}</div>
                <div style="font-mono; font-size: 10px; color: #94a3b8; margin-top: 3px;">${currLat.toFixed(4)}, ${currLon.toFixed(4)}</div>
            </div>
        `);
        leafletRadarMarkers.push(mVessel);
        boundsPoints.push([currLat, currLon]);

        // 4. Polyline Lintasan
        if (pos.origin_lat && pos.origin_lon && pos.dest_lat && pos.dest_lon) {
            leafletRadarPolyline = L.polyline([
                [pos.origin_lat, pos.origin_lon],
                [currLat, currLon],
                [pos.dest_lat, pos.dest_lon]
            ], {
                color: isAir ? '#818cf8' : '#0284c7',
                weight: 3.5,
                opacity: 0.85,
                dashArray: '7, 9'
            }).addTo(leafletRadarMap);
        }

        if (boundsPoints.length > 1) {
            try {
                leafletRadarMap.fitBounds(boundsPoints, { padding: [40, 40], maxZoom: 7 });
            } catch(e) {}
        } else {
            leafletRadarMap.setView([currLat, currLon], 6);
        }

        setTimeout(() => {
            if (leafletRadarMap) leafletRadarMap.invalidateSize();
        }, 200);
    }

    function switchTrkMode(isAir) {
        const btnSea = document.getElementById('trk_btn_mode_sea');
        const btnAir = document.getElementById('trk_btn_mode_air');
        const lblVessel = document.getElementById('trk_lbl_vessel');
        const lblVoyage = document.getElementById('trk_lbl_voyage');
        const inputVessel = document.getElementById('trk_input_vessel');
        const inputVoyage = document.getElementById('trk_input_voyage');

        if (!btnSea || !btnAir) return;

        if (isAir) {
            btnAir.className = 'p-2.5 rounded-xl border border-indigo-500 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 flex items-center justify-center gap-2 transition-all cursor-pointer font-bold shadow-xs';
            btnSea.className = 'p-2.5 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black/60 dark:text-white/60 flex items-center justify-center gap-2 transition-all cursor-pointer';
            lblVessel.textContent = 'Nama Maskapai / Pesawat Udara';
            lblVoyage.textContent = 'Nomor Penerbangan (Flight No)';
            inputVessel.placeholder = 'Contoh: GARUDA INDONESIA atau SINGAPORE AIRLINES';
            inputVoyage.placeholder = 'Contoh: GA-872 atau SQ-951';
        } else {
            btnSea.className = 'p-2.5 rounded-xl border border-[#0284c7] bg-[#0284c7]/10 text-[#0284c7] flex items-center justify-center gap-2 transition-all cursor-pointer font-bold shadow-xs';
            btnAir.className = 'p-2.5 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black/60 dark:text-white/60 flex items-center justify-center gap-2 transition-all cursor-pointer';
            lblVessel.textContent = 'Nama Kapal / Sarana Pengangkut';
            lblVoyage.textContent = 'Nomor Voyage Kapal';
            inputVessel.placeholder = 'Contoh: MV WAN HAI 315 atau EVER GIVEN';
            inputVoyage.placeholder = 'Contoh: 042N atau 082W';
        }
    }

    function openVesselTrackingModal(shipmentId, shipmentNumber, vessel = '', voyage = '', vendorId = '', blNo = '', pol = '', pod = '', tps = '', eta = '', ata = '', notes = '', isAir = false, trackingLinks = {}) {
        currentTrackingShipmentId = shipmentId;
        const modal = document.getElementById('modal-vessel-tracking');
        const form = document.getElementById('form-vessel-tracking');
        form.action = `/core/traffic/${shipmentId}/tracking`;

        document.getElementById('trk_modal_subtitle').textContent = `Shipment: ${shipmentNumber} | BL/AWB: ${blNo || '-'}`;
        document.getElementById('trk_input_vessel').value = vessel || '';
        document.getElementById('trk_input_voyage').value = voyage || '';
        document.getElementById('trk_input_vendor_id').value = vendorId || '';
        document.getElementById('trk_input_bl').value = blNo || '';
        document.getElementById('trk_input_pol').value = pol || '';
        document.getElementById('trk_input_pod').value = pod || '';
        document.getElementById('trk_input_tps').value = tps || '';
        document.getElementById('trk_input_eta').value = eta || '';
        document.getElementById('trk_input_ata').value = ata || '';
        document.getElementById('trk_input_notes').value = notes || '';

        // Set Default Placeholder State pada Telemetri
        document.getElementById('radar_overlay_mode_badge').textContent = isAir ? 'LIVE ADS-B RADAR' : 'LIVE AIS RADAR';
        document.getElementById('radar_overlay_location_label').textContent = 'Menginisialisasi transponder radar satelit...';
        document.getElementById('radar_overlay_coordinates').textContent = 'Scanning...';
        document.getElementById('res_live_vessel_name').textContent = vessel || (isAir ? 'Pesawat Udara' : 'Kapal Laut');
        document.getElementById('res_live_speed').textContent = 'Mengukur...';
        document.getElementById('res_live_dist').textContent = 'Menghitung...';
        document.getElementById('res_live_eta').textContent = eta || 'Menghitung...';
        document.getElementById('res_origin_text').textContent = pol || 'Origin (POL)';
        document.getElementById('res_dest_text').textContent = pod || 'Destination (POD)';
        document.getElementById('res_live_progress_bar').style.width = '10%';
        document.getElementById('res_live_progress_text').textContent = 'Scanning...';

        switchTrkMode(isAir);

        // Render Quick Radar Links
        const container = document.getElementById('trk_radar_buttons_container');
        container.innerHTML = '';
        currentTrkLinks = trackingLinks || {};

        const keys = Object.keys(currentTrkLinks);
        if (keys.length > 0) {
            keys.forEach(k => {
                const item = currentTrkLinks[k];
                const a = document.createElement('a');
                a.href = item.url;
                a.target = '_blank';
                a.rel = 'noopener noreferrer';
                a.className = 'p-2 rounded-xl bg-black/[0.03] dark:bg-white/[0.05] hover:bg-black/[0.06] dark:hover:bg-white/[0.1] border border-black/10 dark:border-white/10 text-black dark:text-white flex items-center justify-between gap-1.5 transition-all text-xs font-semibold group';
                a.innerHTML = `
                    <div class="truncate">
                        <div class="truncate font-bold">${item.name}</div>
                        <div class="text-[9px] text-[#0284c7] font-mono">${item.badge}</div>
                    </div>
                    <i data-lucide="external-link" class="w-3.5 h-3.5 text-[#0284c7] group-hover:translate-x-0.5 transition-transform shrink-0"></i>
                `;
                container.appendChild(a);
            });
        } else {
            container.innerHTML = `<div class="col-span-full text-center text-slate-400 text-xs py-2 italic">Masukkan nama kapal/pesawat untuk memunculkan tautan radar AIS otomatis.</div>`;
        }

        modal.classList.remove('hidden');
        if (window.lucide) window.lucide.createIcons();

        // ⚡ LANGSUNG OTOMATIS PICU DETEKSI REAL-TIME TANPA HARUS KLIK TOMBOL
        setTimeout(() => {
            triggerLiveArrivalDetection();
        }, 100);
    }

    function triggerLiveArrivalDetection() {
        if (!currentTrackingShipmentId) return;

        const btn = document.getElementById('btn_live_detect_arrival');
        const icon = document.getElementById('icon_live_detect_btn');
        const text = document.getElementById('text_live_detect_btn');

        if (btn) btn.disabled = true;
        if (text) text.textContent = 'Mendeteksi Posisi...';
        if (icon) {
            icon.setAttribute('data-lucide', 'loader-2');
            icon.classList.add('animate-spin');
        }
        if (window.lucide) window.lucide.createIcons();

        fetch(`/core/tracking/${currentTrackingShipmentId}/live-detect`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(r => r.json())
        .then(res => {
            if (res.success && res.data) {
                const d = res.data;
                const pos = d.position || {};
                const isAir = (d.mode === 'AIR');

                // 1. Overlay Map Info
                const badgeModeEl = document.getElementById('radar_overlay_mode_badge');
                if (badgeModeEl) badgeModeEl.textContent = isAir ? 'LIVE ADS-B RADAR' : 'LIVE AIS RADAR';

                const locLabel = pos.location_label || (isAir ? 'Ruang Udara Kargo Internasional' : 'Perairan Terbuka');
                const overlayLocEl = document.getElementById('radar_overlay_location_label');
                if (overlayLocEl) overlayLocEl.textContent = `📍 ${locLabel}`;

                const overlayCoordsEl = document.getElementById('radar_overlay_coordinates');
                if (overlayCoordsEl) {
                    overlayCoordsEl.textContent = pos.lat && pos.lon ? `${pos.lat.toFixed(3)}, ${pos.lon.toFixed(3)}` : 'Terdeteksi';
                }

                // 2. Status Title & Explanation
                const titleEl = document.getElementById('res_live_status_title');
                if (titleEl) {
                    const iconSymbol = isAir ? '✈️' : (d.is_arrived ? '⚓' : '🚢');
                    titleEl.innerHTML = `<span>${iconSymbol} ${d.status}</span>`;
                }

                const expEl = document.getElementById('res_live_explanation');
                if (expEl) expEl.textContent = d.explanation || '';

                const badgeEl = document.getElementById('res_live_badge');
                if (badgeEl) {
                    badgeEl.textContent = d.badge || d.status;
                    badgeEl.className = d.is_arrived 
                        ? 'px-2.5 py-1 rounded-full text-[10px] font-bold font-mono bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' 
                        : 'px-2.5 py-1 rounded-full text-[10px] font-bold font-mono bg-sky-500/20 text-sky-300 border border-sky-500/40';
                }

                // 3. Progress Perjalanan
                const pct = typeof pos.progress_pct !== 'undefined' ? pos.progress_pct : (d.is_arrived ? 100 : 50);
                const pBar = document.getElementById('res_live_progress_bar');
                if (pBar) pBar.style.width = `${Math.min(100, Math.max(0, pct))}%`;

                const pText = document.getElementById('res_live_progress_text');
                if (pText) pText.textContent = `${pct}% Perjalanan Selesai`;

                const origText = document.getElementById('res_origin_text');
                if (origText) origText.textContent = pos.origin || 'Asal (POL)';

                const destText = document.getElementById('res_dest_text');
                if (destText) destText.textContent = pos.destination || 'Tujuan (POD)';

                // 4. 4 Kotak Telemetri
                const nameEl = document.getElementById('res_live_vessel_name');
                if (nameEl) nameEl.textContent = d.flight_or_vessel || '-';

                const speedEl = document.getElementById('res_live_speed');
                if (speedEl) {
                    if (pos.speed_knots) speedEl.textContent = `⚡ ${pos.speed_knots} Knots`;
                    else if (pos.speed_kmh) speedEl.textContent = `⚡ ${pos.speed_kmh} km/h`;
                    else if (pos.speed) speedEl.textContent = `⚡ ${pos.speed} ${pos.speed_unit || ''}`;
                    else speedEl.textContent = d.is_arrived ? '0 Knots (Sandar)' : '16 Knots';
                }

                const distEl = document.getElementById('res_live_dist');
                if (distEl) {
                    if (pos.distance_nm) distEl.textContent = `${pos.distance_nm} NM (${pos.distance_km || Math.round(pos.distance_nm * 1.852)} km)`;
                    else if (pos.distance_km) distEl.textContent = `${pos.distance_km} km`;
                    else distEl.textContent = d.is_arrived ? '0 km (Tiba)' : '-';
                }

                const etaEl = document.getElementById('res_live_eta');
                if (etaEl) {
                    etaEl.textContent = d.eta_display || (d.actual_arrival_date || d.eta_date || '-');
                }

                // 5. Render / Update Leaflet Interactive Map
                renderLeafletRadar(pos, isAir, d.flight_or_vessel, d.status, locLabel);

                // 6. Isi nilai form input otomatis
                if (d.eta_date) {
                    const etaInput = document.getElementById('trk_input_eta');
                    if (etaInput) etaInput.value = d.eta_date.replace(' ', 'T').slice(0, 16);
                }
                if (d.actual_arrival_date) {
                    const ataInput = document.getElementById('trk_input_ata');
                    if (ataInput) ataInput.value = d.actual_arrival_date.replace(' ', 'T').slice(0, 16);
                }
                if (d.explanation) {
                    const notesInput = document.getElementById('trk_input_notes');
                    if (notesInput && !notesInput.value) {
                        notesInput.value = d.explanation;
                    }
                }
            } else {
                console.warn(res.message || 'Gagal memproses posisi radar.');
            }
        })
        .catch(err => {
            console.error(err);
        })
        .finally(() => {
            if (btn) btn.disabled = false;
            if (text) text.textContent = 'Re-Ping Posisi';
            if (icon) {
                icon.setAttribute('data-lucide', 'radio');
                icon.classList.remove('animate-spin');
            }
            if (window.lucide) window.lucide.createIcons();
        });
    }

    function setArrivalNow() {
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        document.getElementById('trk_input_ata').value = now.toISOString().slice(0, 16);
    }
    function toggleTrackingFlag(shipmentId, field, btn) {
        fetch(`/core/tracking/${shipmentId}/quick-update`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ action: 'toggle_flag', field: field })
        })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                window.location.reload();
            }
        });
    }

    function openTrackingEditModal(btn) {
        let data = {};
        try {
            data = JSON.parse(btn.getAttribute('data-tracking') || '{}');
        } catch (e) {
            console.error('Gagal membaca data-tracking JSON:', e);
        }
        document.getElementById('form-tracking-edit').action = `/core/tracking/${data.id}/quick-update`;
        document.getElementById('tracking-edit-subtitle').textContent = data.project_no || '';
        document.getElementById('te-jalur').value = data.customs_channel || '';
        document.getElementById('te-spjm-ticket').value = data.spjm_ticket || '';
        document.getElementById('te-spjm-tgl').value = data.spjm_received_at || '';
        document.getElementById('te-insp-tgl').value = data.inspection_scheduled_at || '';
        document.getElementById('te-old-seal').value = data.old_seal_number || '';
        document.getElementById('te-new-seal').value = data.new_seal_number || '';
        document.getElementById('te-trucking').value = data.trucking_vendor_name || '';
        document.getElementById('te-delv').value = data.delivery_date || '';
        document.getElementById('te-tps').value = data.tps_location || '';
        document.getElementById('modal-tracking-edit').classList.remove('hidden');
        if (window.lucide) window.lucide.createIcons();
    }
</script>
@endsection
