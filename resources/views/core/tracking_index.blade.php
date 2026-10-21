@extends('layouts.app')

@section('title', 'Tracking Project & Workflow 11 Pos')

@section('content')
<div class="max-w-[1440px] mx-auto px-4 sm:px-6 space-y-6 pt-4 pb-28 lg:pb-12">

    <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Toolbar Style) -->
    <header class="rounded-[16px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-4 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-sm">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold">
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
            <form action="{{ route('core.tracking.auto-assign-all') }}" method="POST" onsubmit="return confirm('Tugaskan PIC secara otomatis dengan beban kerja terendah untuk semua proyek yang belum lengkap?');">
                @csrf
                <button type="submit" 
                        class="px-3 py-1.5 rounded-[9px] text-[12px] font-semibold bg-[#007AFF]/10 hover:bg-[#007AFF]/20 text-[#007AFF] transition-all flex items-center gap-1.5 active:scale-[0.98]">
                    <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                    <span>Auto-Assign PIC ({{ $kpis['incomplete_pic_count'] }} Proyek)</span>
                </button>
            </form>
            @endif

            <a href="{{ route('core.document.index') }}" 
               class="px-3.5 py-1.5 rounded-[9px] text-[12px] font-semibold bg-[#007AFF] text-white hover:bg-[#0062CC] transition-all flex items-center gap-1.5 shadow-[0_2px_8px_rgba(0,122,255,0.25)] active:scale-[0.98]">
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
                <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
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
        <div class="p-4 border-b border-black/5 dark:border-white/5 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <!-- Tabs Segmented Control -->
            <div class="inline-flex p-1 bg-black/[0.04] dark:bg-white/[0.06] rounded-[10px]">
                <a href="{{ route('core.tracking.index', array_merge(request()->query(), ['filter' => 'all'])) }}"
                   class="px-3.5 py-1.5 rounded-[8px] text-[12px] font-semibold transition-all {{ $filter !== 'me' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                    Semua Project ({{ $kpis['total_active'] }})
                </a>
                <a href="{{ route('core.tracking.index', array_merge(request()->query(), ['filter' => 'me'])) }}"
                   class="px-3.5 py-1.5 rounded-[8px] text-[12px] font-semibold transition-all flex items-center gap-1.5 {{ $filter === 'me' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                    <span>Tugas Saya</span>
                    @if($kpis['my_tasks_count'] > 0)
                    <span class="w-4 h-4 rounded-full bg-[#007AFF] text-white text-[10px] flex items-center justify-center font-bold">
                        {{ $kpis['my_tasks_count'] }}
                    </span>
                    @endif
                </a>
            </div>

            <!-- Search Form -->
            <form action="{{ route('core.tracking.index') }}" method="GET" class="relative max-w-sm w-full">
                <input type="hidden" name="filter" value="{{ $filter }}">
                <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40"></i>
                <input type="text" 
                       name="search" 
                       value="{{ $search }}" 
                       placeholder="Cari Project No, B/L, atau Klien..." 
                       class="w-full pl-9 pr-8 py-1.5 rounded-[9px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/5 dark:border-white/10 text-[12px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                @if($search)
                <a href="{{ route('core.tracking.index', ['filter' => $filter]) }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                </a>
                @endif
            </form>
        </div>

        <!-- Matrix Table -->
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse min-w-[1200px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/5 bg-black/[0.015] dark:bg-white/[0.02] text-[11px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">
                        <th class="py-3 px-4 w-[230px] sticky left-0 z-10 bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-md">Proyek & Klien</th>
                        <th class="py-3 px-3 w-[110px] text-center">Status / SLA</th>
                        @foreach($stages as $idx => $stg)
                        <th class="py-3 px-2 text-center w-[78px] font-medium" title="{{ $stg['title'] }} ({{ $stg['role'] }})">
                            <div class="text-[10px] leading-tight font-bold text-black/70 dark:text-white/70">{{ $idx + 1 }}</div>
                            <div class="text-[9px] truncate tracking-normal font-normal text-black/40 dark:text-white/40" style="max-width: 72px;">{{ $stg['title'] }}</div>
                        </th>
                        @endforeach
                        <th class="py-3 px-4 w-[90px] text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5 text-[12px]">
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
                        <td class="py-3 px-4 sticky left-0 z-10 bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-md group-hover:bg-[#F9F9FA] dark:group-hover:bg-[#202022] transition-colors">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('core.document.show', $s->id) }}" class="font-bold text-black dark:text-white hover:text-[#007AFF] text-[13px] tracking-tight">
                                    {{ $s->project_no }}
                                </a>
                                @if($s->tipe_layanan)
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 uppercase">
                                    {{ $s->tipe_layanan }}
                                </span>
                                @endif
                            </div>
                            <div class="text-[11px] text-black/60 dark:text-white/60 truncate max-w-[210px] font-medium mt-0.5">
                                {{ $s->client?->name ?? 'Klien Umum' }}
                            </div>
                            <div class="text-[10px] text-black/40 dark:text-white/40 mt-0.5 flex items-center gap-2">
                                <span>B/L: {{ $s->nomor_bl_awb ?: '-' }}</span>
                                @if($s->eta)
                                <span>• ETA {{ \Carbon\Carbon::parse($s->eta)->format('d/m') }}</span>
                                @endif
                            </div>
                        </td>

                        <!-- SLA & Jalur Badge -->
                        <td class="py-3 px-3 text-center align-middle">
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
                        <td class="py-3 px-1 text-center align-middle">
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
                                       class="relative inline-flex flex-col items-center p-1 rounded-[8px] bg-[#007AFF]/10 border border-[#007AFF]/30 text-[#007AFF] hover:bg-[#007AFF]/20 transition-all hover:scale-105"
                                       title="POS AKTIF: {{ $idx + 1 }}. {{ $stg['title'] }} — PIC: {{ $picName ?: 'Belum Ada PIC' }} (Target: {{ $stg['sla_hours'] ?? 8 }} Jam)">
                                        <div class="relative flex items-center justify-center w-5 h-5 rounded-full bg-[#007AFF] text-white text-[10px] font-bold shadow-sm">
                                            @if($picInitial)
                                                <span>{{ $picInitial }}</span>
                                            @else
                                                <i data-lucide="play" class="w-2.5 h-2.5 fill-white stroke-none ml-0.5"></i>
                                            @endif
                                            <!-- Ping pulse indicator -->
                                            <span class="absolute -top-0.5 -right-0.5 flex h-2 w-2">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#007AFF] opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-2 w-2 bg-[#007AFF]"></span>
                                            </span>
                                        </div>
                                        <span class="text-[9px] font-bold text-[#007AFF] mt-0.5 leading-none">
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
                                            <span class="text-[9px] text-black/25 dark:text-white/25">·</span>
                                        @endif
                                    </div>
                                    @if($picInitial)
                                    <span class="text-[8px] text-black/30 dark:text-white/30 mt-0.5 truncate max-w-[45px]">{{ $picInitial }}</span>
                                    @endif
                                @endif
                            </div>
                        </td>
                        @endforeach

                        <!-- Aksi -->
                        <td class="py-3 px-4 text-right align-middle">
                            <a href="{{ route('core.document.show', $s->id) }}" 
                               class="inline-flex items-center gap-1 px-2.5 py-1 rounded-[7px] text-[11px] font-semibold bg-black/[0.04] dark:bg-white/[0.08] hover:bg-[#007AFF] hover:text-white transition-all text-black/75 dark:text-white/75 group/btn">
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
                    <span class="w-4 h-4 rounded-full bg-[#007AFF] text-white flex items-center justify-center text-[9px] font-bold">
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
                    <span class="w-4 h-4 rounded-full bg-black/5 dark:bg-white/10 text-center text-[9px] flex items-center justify-center">
                        ·
                    </span>
                    <span>Menunggu Giliran (Antrean)</span>
                </div>
            </div>
            <div class="text-[11px] text-black/40 dark:text-white/40">
                Inisial huruf di bawah lingkaran menandakan staf PIC yang ditugaskan.
            </div>
        </div>
    </div>

    <!-- 4. SECTION BEBAN KERJA TIM OPERASIONAL (TEAM WORKLOAD & PIC ALLOCATION) -->
    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden shadow-sm">
        <div class="p-4 border-b border-black/5 dark:border-white/5 flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-[16px] font-bold text-black dark:text-white tracking-tight">
                        Matriks Beban Kerja Staf Pelaksana
                    </h2>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#007AFF]/10 text-[#007AFF]">
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
                                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-[#007AFF]/20 to-[#5856D6]/20 border border-black/5 dark:border-white/10 flex items-center justify-center font-bold text-[#007AFF] text-[11px] shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-black dark:text-white truncate">
                                        {{ $user->name }}
                                        @if($user->id === auth()->id())
                                        <span class="ml-1 text-[10px] text-[#007AFF] font-medium">(Anda)</span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-black/40 dark:text-white/40 truncate">
                                        {{ $emp?->employee_id ?? 'NIP: -' }} • {{ $user->email }}
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
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#007AFF]/10 text-[#007AFF]">
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
@endsection
