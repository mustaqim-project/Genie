@extends('layouts.app')

@section('title', 'Dashboard Operasional Kepabeanan & Logistik')

@section('content')
<div class="max-w-[1360px] mx-auto px-4 sm:px-6 space-y-6 pt-4 pb-28 lg:pb-12" x-data="{ docFilter: 'ALL' }">

    <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Toolbar Style from Cooca Core) -->
    <header class="rounded-[16px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
        <div>
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">GENIE ERP</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                <span class="text-black dark:text-white font-medium">Dashboard Operasional</span>
            </nav>
            <div class="flex items-center gap-2.5">
                <h1 class="text-[20px] sm:text-[22px] font-bold text-black dark:text-white tracking-tight">
                    Sistem Operasional Kepabeanan & Logistik
                </h1>
                <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759] animate-pulse"></span>
                    <span>CEISA 4.0 Live</span>
                </span>
            </div>
            <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5 leading-snug">
                Pusat kendali proyek pengapalan: penerimaan berkas, draf PIB/PEB, pengajuan CEISA 4.0, penebusan DO pelayaran, dan pengeluaran barang pelabuhan.
            </p>
        </div>

        <!-- Action Buttons Toolbar -->
        <div class="flex items-center gap-2 w-full sm:w-auto flex-wrap sm:flex-nowrap shrink-0">
            <a href="{{ route('core.document.index') }}" 
               class="btn-apple-filled w-full sm:w-auto">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Proyek Pengapalan</span>
            </a>

            <a href="{{ route('bc20.index') }}" 
               class="btn-apple-gray w-full sm:w-auto">
                <i data-lucide="file-down" class="w-4 h-4 text-[#007AFF]"></i>
                <span>BC 2.0 (PIB)</span>
            </a>

            <a href="{{ route('bc30.index') }}" 
               class="btn-apple-gray w-full sm:w-auto">
                <i data-lucide="file-up" class="w-4 h-4 text-[#5856D6]"></i>
                <span>BC 3.0 (PEB)</span>
            </a>
        </div>
    </header>

    <!-- 2. BENTO STATISTICS GRID (Apple HIG Standard from Cooca Core) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
        
        <!-- KPI 1: Proyek Pengapalan Aktif -->
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase tracking-wider font-semibold text-black/45 dark:text-white/45">Proyek Pengapalan</span>
                <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                    <i data-lucide="folder-kanban" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <div class="text-[22px] sm:text-[24px] font-bold tabular-nums text-black dark:text-white tracking-tight">
                    {{ $totalShipments ?? 0 }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5 truncate">
                    {{ $shipmentsCollecting ?? 0 }} kumpul berkas &bull; {{ $shipmentsBilling ?? 0 }} billing
                </div>
            </div>
        </div>

        <!-- KPI 2: BC 2.0 Impor Card -->
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase tracking-wider font-semibold text-black/45 dark:text-white/45">Impor (BC 2.0 PIB)</span>
                <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                    <i data-lucide="arrow-down-left" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <div class="text-[22px] sm:text-[24px] font-bold tabular-nums text-black dark:text-white tracking-tight">
                    {{ $totalBC20 }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5 truncate tabular-nums">
                    CIF: Rp {{ number_format($totalCifIdr, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <!-- KPI 3: BC 3.0 Ekspor Card -->
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase tracking-wider font-semibold text-black/45 dark:text-white/45">Ekspor (BC 3.0 PEB)</span>
                <div class="w-8 h-8 rounded-[10px] bg-[#5856D6]/10 text-[#5856D6] flex items-center justify-center">
                    <i data-lucide="arrow-up-right" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <div class="text-[22px] sm:text-[24px] font-bold tabular-nums text-black dark:text-white tracking-tight">
                    {{ $totalBC30 }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5 truncate tabular-nums">
                    FOB: Rp {{ number_format($totalFobIdr, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <!-- KPI 4: Persetujuan Terbit & Billing -->
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase tracking-wider font-semibold text-black/45 dark:text-white/45">SPPB & NPE Terbit</span>
                <div class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                    <i data-lucide="badge-check" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <div class="text-[22px] sm:text-[24px] font-bold tabular-nums text-[#248A3D] dark:text-[#30D158] tracking-tight">
                    {{ $totalSppb + $totalNpe }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5 truncate">
                    Billing Aktif: <strong class="text-[#FF9500] dark:text-[#FF9F0A] tabular-nums">{{ $totalBillingAktif }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. MAIN WORKSPACE GRID: PROYEK PENGAPALAN TERBARU & AUDIT LOG -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Kolom Kiri: Proyek Pengapalan Terbaru (2 Kolom) -->
        <div class="lg:col-span-2 rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm overflow-hidden flex flex-col">
            <div class="p-4 sm:p-5 border-b border-black/5 dark:border-white/5 flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-black dark:text-white text-[14px]">Proyek Pengapalan Terkini</h2>
                    <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Daftar berkas pengapalan aktif dan tahap operasional</p>
                </div>
                <a href="{{ route('core.document.index') }}" class="btn-apple-plain text-[12px]">
                    <span>Buka Semua</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="table-responsive flex-1">
                <table class="w-full text-left text-[13px]">
                    <thead class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider bg-black/[0.02] dark:bg-white/[0.02] border-b border-black/5 dark:border-white/10">
                        <tr>
                            <th class="py-3 px-4">No. Proyek & B/L</th>
                            <th class="py-3 px-4">Klien / Importir</th>
                            <th class="py-3 px-4">Kapal & Muatan</th>
                            <th class="py-3 px-4">Status Tahap</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @forelse($recentShipments as $shp)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                                <td class="py-3 px-4 font-mono font-semibold text-black dark:text-white">
                                    <a href="{{ route('core.document.show', $shp->id) }}" class="text-[#007AFF] hover:underline">
                                        {{ $shp->shipment_number ?? $shp->project_no }}
                                    </a>
                                    <div class="text-[11px] text-black/40 dark:text-white/40 font-sans mt-0.5">
                                        B/L: {{ $shp->bl_number ?? '-' }}
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-black dark:text-white truncate max-w-[150px]">{{ $shp->client->name ?? '-' }}</div>
                                    <span class="inline-block mt-0.5 px-2 py-0.5 rounded-[6px] text-[10px] font-bold font-mono {{ $shp->type === 'EXPORT' ? 'bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]' : 'bg-[#007AFF]/12 text-[#007AFF]' }}">
                                        {{ $shp->type === 'EXPORT' ? 'BC 3.0' : 'BC 2.0' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-black/80 dark:text-white/80">
                                    <div class="truncate max-w-[140px]">{{ $shp->vessel_name ?? '-' }}</div>
                                    <div class="text-[11px] font-mono text-black/40 dark:text-white/40 mt-0.5">{{ $shp->nomor_kontainer ?? '1 Kontainer' }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold font-mono 
                                        @if(in_array($shp->status, ['BILLING_ISSUED', 'DO_RELEASED', 'DELIVERED_POD', 'COMPLETED'])) bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]
                                        @elseif(in_array($shp->status, ['DOC_READY', 'HS_DETECTED', 'DRAFT_REVIEW'])) bg-[#5856D6]/15 text-[#5856D6] dark:text-[#5E5CE6]
                                        @elseif($shp->status === 'DO_PROCESSING') bg-[#FF9500]/15 text-[#B25E00] dark:text-[#FF9F0A]
                                        @else bg-black/[0.06] dark:bg-white/[0.08] text-black/70 dark:text-white/70 @endif">
                                        {{ $shp->status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('core.document.show', $shp->id) }}" 
                                       class="btn-apple-gray !h-8 !px-3 !text-[11px]">
                                        <span>Detail</span>
                                        <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-10 text-center text-black/40 dark:text-white/40">
                                    Belum ada proyek shipment terdaftar. <a href="{{ route('core.document.index') }}" class="text-[#007AFF] font-bold hover:underline">Buka proyek baru &rarr;</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Kolom Kanan: Log Respon Gateway CEISA 4.0 (1 Kolom) -->
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm overflow-hidden flex flex-col">
            <div class="p-4 sm:p-5 border-b border-black/5 dark:border-white/5 flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-black dark:text-white text-[14px]">Audit Gateway CEISA 4.0</h2>
                    <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Riwayat kirim & respon DJBC</p>
                </div>
                <a href="{{ route('config.index') }}" class="btn-apple-plain text-[12px]">
                    <span>Konfigurasi</span>
                </a>
            </div>

            <div class="p-4 flex-1 space-y-2.5 overflow-y-auto max-h-[360px] custom-scrollbar">
                @forelse($recentLogs as $log)
                    <div class="p-3 rounded-[12px] border border-black/5 dark:border-white/5 bg-black/[0.02] dark:bg-white/[0.02] text-[12px] hover:border-black/10 dark:hover:border-white/10 transition-colors">
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center space-x-1.5 truncate">
                                <span class="px-1.5 py-0.5 rounded-[6px] font-mono font-bold text-[10px] {{ $log->method === 'POST' ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]' : 'bg-[#007AFF]/15 text-[#007AFF]' }}">
                                    {{ $log->method }}
                                </span>
                                <span class="font-mono text-black/80 dark:text-white/80 truncate max-w-[140px] text-[11px]">{{ $log->endpoint }}</span>
                            </div>
                            <span class="px-2 py-0.5 rounded-full font-bold text-[10px] font-mono {{ $log->status_code === 200 ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]' : 'bg-[#FF3B30]/15 text-[#C41E17] dark:text-[#FF453A]' }}">
                                {{ $log->status_code }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-black/40 dark:text-white/40 text-[10px] font-mono">
                            <span>Durasi: {{ $log->duration_ms ?? 0 }} ms</span>
                            <span>{{ $log->created_at ? $log->created_at->diffForHumans() : '-' }}</span>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-black/40 dark:text-white/40 text-[12px]">
                        Belum ada riwayat transmisi CEISA 4.0.
                    </div>
                @endforelse
            </div>

            <div class="p-4 bg-black/[0.02] dark:bg-white/[0.02] border-t border-black/5 dark:border-white/5">
                <form action="{{ route('config.test-auth') }}" method="POST">
                    @csrf
                    <button type="submit" 
                            class="btn-apple-filled w-full">
                        <i data-lucide="zap" class="w-4 h-4 text-white"></i>
                        <span>Uji Koneksi Token OAuth DJBC</span>
                    </button>
                </form>
            </div>
        </div>

    </div>

</div>
@endsection
