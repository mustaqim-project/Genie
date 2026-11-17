@extends('layouts.app')

@section('title', 'Tim Traffic & Lapangan — Penjaluran, Gate Out & POD')

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
<div class="max-w-[1360px] mx-auto px-4 sm:px-6 space-y-6 pt-4 pb-28 lg:pb-12">

    <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Toolbar Style from Cooca Core) -->
    <header class="rounded-[16px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
        <div>
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-[#0284c7] transition-colors">Dashboard</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                <span class="text-black/70 dark:text-white/70 font-medium">Core Bisnis</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                <span class="text-black dark:text-white font-semibold">Tim Traffic</span>
            </nav>
            <div class="flex items-center gap-2.5">
                <h1 class="text-[20px] sm:text-[22px] font-bold text-black dark:text-white tracking-tight">
                    Traffic, Penjaluran & Lapangan Pelabuhan
                </h1>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/10 text-[#248A3D] dark:text-[#30D158]">
                    Gate Out & POD
                </span>
            </div>
            <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5 leading-snug">
                Monitoring respon penjaluran CEISA (Hijau/Kuning/Merah), behandle, pengeluaran truk (Gate Out) dan POD serah terima.
            </p>
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
            <a href="{{ route('core.document.index') }}" class="btn-apple-gray w-full sm:w-auto">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali ke Dokumen</span>
            </a>
        </div>
    </header>

    <!-- 2. 4 CUSTOMS CHANNEL FILTER PILLS (Apple HIG Status Indicators) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-[12px] font-semibold">
        <a href="{{ route('core.traffic.index', ['channel' => 'HIJAU']) }}" 
           class="p-3.5 rounded-[14px] border {{ request('channel') === 'HIJAU' ? 'bg-[#34C759]/15 border-[#34C759]/30 text-[#248A3D] dark:text-[#30D158] shadow-xs' : 'bg-white dark:bg-[#1C1C1E] border-black/5 dark:border-white/5 text-black/70 dark:text-white/70 hover:bg-black/[0.02] dark:hover:bg-white/[0.02]' }} flex items-center justify-between transition-all active:scale-[0.99]">
            <span class="inline-flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#34C759] animate-pulse"></span>
                <span>Jalur Hijau (SPPB)</span>
            </span>
            <span class="text-[10px] px-2 py-0.5 rounded-full bg-[#34C759]/20 text-[#248A3D] dark:text-[#30D158] font-bold">Siap Gate Out</span>
        </a>
        <a href="{{ route('core.traffic.index', ['channel' => 'KUNING']) }}" 
           class="p-3.5 rounded-[14px] border {{ request('channel') === 'KUNING' ? 'bg-[#FF9500]/15 border-[#FF9500]/30 text-[#B25E00] dark:text-[#FF9F0A] shadow-xs' : 'bg-white dark:bg-[#1C1C1E] border-black/5 dark:border-white/5 text-black/70 dark:text-white/70 hover:bg-black/[0.02] dark:hover:bg-white/[0.02]' }} flex items-center justify-between transition-all active:scale-[0.99]">
            <span class="inline-flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#FF9500]"></span>
                <span>Jalur Kuning (SPJK)</span>
            </span>
            <span class="text-[10px] px-2 py-0.5 rounded-full bg-[#FF9500]/20 text-[#B25E00] dark:text-[#FF9F0A] font-bold">Periksa Dok</span>
        </a>
        <a href="{{ route('core.traffic.index', ['channel' => 'MERAH']) }}" 
           class="p-3.5 rounded-[14px] border {{ request('channel') === 'MERAH' ? 'bg-[#FF3B30]/15 border-[#FF3B30]/30 text-[#C41E17] dark:text-[#FF453A] shadow-xs' : 'bg-white dark:bg-[#1C1C1E] border-black/5 dark:border-white/5 text-black/70 dark:text-white/70 hover:bg-black/[0.02] dark:hover:bg-white/[0.02]' }} flex items-center justify-between transition-all active:scale-[0.99]">
            <span class="inline-flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#FF3B30]"></span>
                <span>Jalur Merah (SPJM)</span>
            </span>
            <span class="text-[10px] px-2 py-0.5 rounded-full bg-[#FF3B30]/20 text-[#C41E17] dark:text-[#FF453A] font-bold">Behandle Fisik</span>
        </a>
        <a href="{{ route('core.traffic.index', ['channel' => 'HOLD']) }}" 
           class="p-3.5 rounded-[14px] border {{ request('channel') === 'HOLD' ? 'bg-[#AF52DE]/15 border-[#AF52DE]/30 text-[#8E44AD] dark:text-[#BF5AF2] shadow-xs' : 'bg-white dark:bg-[#1C1C1E] border-black/5 dark:border-white/5 text-black/70 dark:text-white/70 hover:bg-black/[0.02] dark:hover:bg-white/[0.02]' }} flex items-center justify-between transition-all active:scale-[0.99]">
            <span class="inline-flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#AF52DE]"></span>
                <span>Sengketa / Notul</span>
            </span>
            <span class="text-[10px] px-2 py-0.5 rounded-full bg-[#AF52DE]/20 text-[#8E44AD] dark:text-[#BF5AF2] font-bold">SPTNP / NPD</span>
        </a>
    </div>

    <!-- 3. TABLE TRAFFIC WORKFLOW -->
    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm overflow-hidden flex flex-col">
        <div class="p-4 sm:p-5 border-b border-black/5 dark:border-white/5 flex items-center justify-between">
            <div class="font-semibold text-[13px] text-black dark:text-white flex items-center space-x-2">
                <span>Antrian Pergerakan Kargo & Armada Truk</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 font-mono">{{ $shipments->total() }} Kargo</span>
            </div>
            <span class="text-[12px] text-black/45 dark:text-white/45 hidden sm:block">Pergerakan fisik TPS menuju pabrik klien</span>
        </div>

        <div class="table-responsive flex-1">
            <table class="w-full text-left text-[13px]">
                <thead class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider bg-black/[0.02] dark:bg-white/[0.02] border-b border-black/5 dark:border-white/10">
                    <tr>
                        <th class="py-3 px-4">No. Shipment &amp; B/L</th>
                        <th class="py-3 px-4">Importir</th>
                        <th class="py-3 px-4">Kapal / Maskapai &amp; ETA</th>
                        <th class="py-3 px-4">Jalur Pabean</th>
                        <th class="py-3 px-4">Status Rilis DO</th>
                        <th class="py-3 px-4">Status Pengiriman</th>
                        <th class="py-3 px-4 text-right">Aksi Lapangan &amp; Trucking</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5">
                    @forelse($shipments as $shp)
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                            <td class="py-3 px-4 font-mono">
                                <span class="font-bold text-black dark:text-white">{{ $shp->shipment_number ?? $shp->project_no }}</span>
                                <div class="text-[11px] text-black/45 dark:text-white/45 font-sans mt-0.5">BL/AWB: {{ $shp->bl_number ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-semibold text-black dark:text-white">{{ $shp->client->name ?? '-' }}</div>
                                <div class="text-[11px] text-black/45 dark:text-white/45">Tujuan: Gudang Pabrik</div>
                            </td>
                            <!-- Kapal / Maskapai & ETA (Monitoring Kedatangan Pos 5) -->
                            <td class="py-3 px-4">
                                @php
                                    $isAir = $shp->is_air_freight;
                                    $etaInfo = $shp->eta_status_info;
                                @endphp
                                <div class="space-y-1">
                                    <div class="flex items-center gap-1.5">
                                        <i data-lucide="{{ $isAir ? 'plane' : 'anchor' }}" class="w-3.5 h-3.5 {{ $isAir ? 'text-indigo-600 dark:text-indigo-400' : 'text-[#0284c7]' }} shrink-0"></i>
                                        <span class="font-bold text-black dark:text-white text-xs">
                                            {{ $shp->nama_kapal ?: ($isAir ? 'Penerbangan' : 'Kapal Belum Diset') }}
                                        </span>
                                        @if($shp->voyage_no)
                                            <span class="px-1.5 py-0.2 rounded bg-black/5 dark:bg-white/10 font-mono text-[10px] text-black/60 dark:text-white/60">
                                                {{ $shp->voyage_no }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-black/50 dark:text-white/50">
                                        {{ $shp->shippingVendor?->name ?? 'Carrier Belum Ditautkan' }}
                                    </div>
                                    <div class="flex flex-wrap items-center gap-1.5 pt-0.5">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold font-mono {{ $etaInfo['badge_class'] }}">
                                            @if($etaInfo['is_arrived'])
                                                <i data-lucide="check-circle" class="w-3 h-3 text-emerald-600"></i>
                                            @elseif($etaInfo['status'] === 'TODAY')
                                                <i data-lucide="clock" class="w-3 h-3 text-amber-600"></i>
                                            @elseif($etaInfo['status'] === 'OVERDUE')
                                                <i data-lucide="alert-triangle" class="w-3 h-3 text-rose-600"></i>
                                            @else
                                                <i data-lucide="navigation" class="w-3 h-3 text-sky-600"></i>
                                            @endif
                                            <span>{{ $etaInfo['detail'] }}</span>
                                        </span>
                                        @if($shp->tps_location)
                                            <span class="text-[10px] text-black/45 dark:text-white/45 font-mono">
                                                {{ $shp->tps_location }}
                                            </span>
                                        @endif
                                    </div>
                                    <div>
                                        <button type="button"
                                                onclick="openVesselTrackingModal(
                                                    '{{ $shp->id }}',
                                                    '{{ $shp->shipment_number ?? $shp->project_no }}',
                                                    '{{ addslashes($shp->nama_kapal ?? '') }}',
                                                    '{{ addslashes($shp->voyage_no ?? '') }}',
                                                    '{{ $shp->shipping_vendor_id ?? '' }}',
                                                    '{{ addslashes($shp->bl_number ?? '') }}',
                                                    '{{ addslashes($shp->pelabuhan_muat ?? '') }}',
                                                    '{{ addslashes($shp->pelabuhan_bongkar ?? '') }}',
                                                    '{{ addslashes($shp->tps_location ?? '') }}',
                                                    '{{ $shp->eta_date ? $shp->eta_date->format('Y-m-d\TH:i') : '' }}',
                                                    '{{ $shp->actual_arrival_date ? $shp->actual_arrival_date->format('Y-m-d\TH:i') : '' }}',
                                                    '{{ addslashes($shp->catatan ?? '') }}',
                                                    {{ $isAir ? 'true' : 'false' }},
                                                    {{ json_encode($shp->tracking_links) }}
                                                )"
                                                class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#0284c7] hover:text-[#0066D6] hover:underline cursor-pointer">
                                            <i data-lucide="radar" class="w-3 h-3"></i>
                                            <span>Live Radar &amp; Atur ETA &rarr;</span>
                                        </button>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                @php
                                    $channel = $shp->customs_channel ?? 'HIJAU';
                                    $photoCnt = $shp->inspectionPhotos->count();
                                @endphp
                                <div class="flex flex-wrap items-center gap-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold font-mono
                                        {{ $channel === 'HIJAU' ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]' : ($channel === 'MERAH' ? 'bg-[#FF3B30]/15 text-[#C41E17] dark:text-[#FF453A]' : 'bg-[#FF9500]/15 text-[#B25E00] dark:text-[#FF9F0A]') }}">
                                        JALUR {{ $channel }}
                                    </span>
                                    @if($shp->spjm_received)
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-[#FF3B30]/15 text-[#FF3B30]">SPJM</span>
                                    @endif
                                    @if($shp->npd_received)
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-[#FF9500]/15 text-[#FF9500]">NPD</span>
                                    @endif
                                    @if($shp->dnp_received)
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-[#FF9500]/15 text-[#FF9500]">DNP</span>
                                    @endif
                                </div>
                                <div class="text-[10px] font-mono text-black/50 dark:text-white/50 mt-1">
                                    {{ $photoCnt }} Foto {{ $shp->new_seal_number ? '/ Segel: ' . $shp->new_seal_number : '' }}
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                @if($shp->do_is_released)
                                    <span class="text-[#248A3D] dark:text-[#30D158] font-bold font-mono text-[11px] flex items-center gap-1">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                        <span>RILIS ({{ $shp->do_number }})</span>
                                    </span>
                                @else
                                    <span class="text-[#FF3B30] font-bold font-mono text-[11px] flex items-center gap-1">
                                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                        <span>BELUM RILIS</span>
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold font-mono
                                    {{ $shp->status === 'DELIVERED' ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]' : ($shp->status === 'GATE_OUT' ? 'bg-[#0284c7]/15 text-[#0284c7]' : 'bg-black/[0.06] dark:bg-white/[0.08] text-black/70 dark:text-white/70') }}">
                                    {{ $shp->status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right space-x-1.5 whitespace-nowrap">
                                <!-- Dropdown Cetak Surat Jalan (4 Template Resmi MoM) -->
                                <div class="relative inline-block text-left" x-data="{ open: false }">
                                    <button type="button" 
                                            @click="open = !open" 
                                            @click.away="open = false"
                                            class="btn-apple-gray !h-8 !px-2.5 !text-[11px] inline-flex items-center gap-1 text-[#0284c7] font-semibold"
                                            title="Cetak Surat Jalan Resmi (4 Template Sesuai MoM)">
                                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                        <span>Surat Jalan</span>
                                        <i data-lucide="chevron-down" class="w-3 h-3 text-slate-400"></i>
                                    </button>
                                    <div x-show="open" 
                                         x-cloak
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="transform opacity-0 scale-95"
                                         x-transition:enter-end="transform opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="transform opacity-100 scale-100"
                                         x-transition:leave-end="transform opacity-0 scale-95"
                                         class="origin-top-right absolute right-0 mt-1 w-64 rounded-xl shadow-xl bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 ring-1 ring-black ring-opacity-5 z-50 py-1.5 text-left text-xs divide-y divide-slate-100 dark:divide-white/5">
                                        <div class="px-3 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                            Pilih Format Surat Jalan:
                                        </div>
                                        <a href="{{ route('pdf.surat-jalan', $shp->id) }}?template=fcl" target="_blank"
                                           class="flex items-center gap-2.5 px-3 py-2 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                                            <i data-lucide="container" class="w-4 h-4 text-blue-500 shrink-0"></i>
                                            <div>
                                                <div class="font-bold text-[11px]">Template 1: Kontainer FCL</div>
                                                <div class="text-[10px] text-slate-400">Pelabuhan ke Gudang Klien</div>
                                            </div>
                                        </a>
                                        <a href="{{ route('pdf.surat-jalan', $shp->id) }}?template=lcl" target="_blank"
                                           class="flex items-center gap-2.5 px-3 py-2 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                                            <i data-lucide="package" class="w-4 h-4 text-amber-500 shrink-0"></i>
                                            <div>
                                                <div class="font-bold text-[11px]">Template 2: LCL / Air Freight</div>
                                                <div class="text-[10px] text-slate-400">Kargo Parsial, CFS & Kargo Udara</div>
                                            </div>
                                        </a>
                                        <a href="{{ route('pdf.surat-jalan', $shp->id) }}?template=inter_factory" target="_blank"
                                           class="flex items-center gap-2.5 px-3 py-2 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                                            <i data-lucide="factory" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                                            <div>
                                                <div class="font-bold text-[11px]">Template 3: Antar Pabrik KB</div>
                                                <div class="text-[10px] text-slate-400">BC 2.7 / BC 4.0 Kawasan Berikat</div>
                                            </div>
                                        </a>
                                        <a href="{{ route('pdf.surat-jalan', $shp->id) }}?template=empty_depo" target="_blank"
                                           class="flex items-center gap-2.5 px-3 py-2 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                                            <i data-lucide="archive-restore" class="w-4 h-4 text-purple-500 shrink-0"></i>
                                            <div>
                                                <div class="font-bold text-[11px]">Template 4: Pengembalian Empty</div>
                                                <div class="text-[10px] text-slate-400">Balik Kontainer Kosong ke Depo</div>
                                            </div>
                                        </a>
                                    </div>
                                </div>

                                <!-- Tombol Email ETA / Status Kargo -->
                                <button type="button" 
                                        onclick="openTrafficEmailModal('{{ $shp->id }}', '{{ $shp->shipment_number ?? $shp->project_no }}', '{{ addslashes($shp->vessel_name ?? '') }}', '{{ addslashes($shp->client?->email ?? '') }}')"
                                        class="btn-apple-gray !h-8 !px-2.5 !text-[11px] inline-flex items-center gap-1 text-sky-600 dark:text-sky-400 font-semibold"
                                        title="Kirim Email Resmi Status Kargo &amp; ETA (Divisi Traffic)">
                                    <i data-lucide="send" class="w-3.5 h-3.5"></i>
                                    <span>Kirim ETA</span>
                                </button>
                                <a href="{{ route('emails.preview.traffic', $shp->id) }}" target="_blank"
                                   class="btn-apple-gray !h-8 !px-2 !text-[11px] inline-flex items-center gap-1 text-slate-500 hover:text-sky-600"
                                   title="Pratinjau Template Email Status Kargo / ETA (Divisi Traffic)">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                </a>

                                <!-- Tombol Rekap Biaya Operasional (DO, Storage, LOLO, Trucking) -->
                                <button type="button"
                                        onclick="openCostRecapModal('{{ $shp->id }}', '{{ $shp->shipment_number ?? $shp->project_no }}', {{ json_encode($shp->operationCosts) }}, '{{ $shp->trucking_vendor_name }}')"
                                        class="btn-apple-gray !h-8 !px-2.5 !text-[11px] inline-flex items-center gap-1.5"
                                        title="Rekap Biaya Operasional & Trucking">
                                    <i data-lucide="receipt" class="w-3.5 h-3.5 text-emerald-600"></i>
                                    <span>Biaya: Rp {{ number_format((float) $shp->operationCosts->sum('amount'), 0, ',', '.') }}</span>
                                </button>

                                <button type="button"
                                        onclick="openPhotoInspectionModal('{{ $shp->id }}', '{{ $shp->shipment_number ?? $shp->project_no }}', '{{ $shp->nomor_kontainer }}', '{{ $shp->new_seal_number }}')"
                                        class="btn-apple-gray !h-8 !px-2.5 !text-[11px] inline-flex items-center gap-1">
                                    <i data-lucide="camera" class="w-3.5 h-3.5 text-[#0284c7]"></i>
                                    <span>Foto SPJM/Segel</span>
                                </button>
                                @if($shp->status === 'GATE_OUT')
                                    <!-- Tombol Konfirmasi POD Modal Trigger -->
                                    <button type="button" 
                                            onclick="openPodModal('{{ $shp->id }}', '{{ $shp->shipment_number ?? $shp->project_no }}')"
                                            class="btn-apple-filled !bg-[#34C759] hover:!bg-[#248A3D] !h-8 !px-3 !text-[11px]">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                        <span>Konfirmasi POD</span>
                                    </button>
                                @elseif($shp->status !== 'DELIVERED')
                                    <!-- Tombol Gate Out Modal Trigger -->
                                    <button type="button" 
                                            onclick="openGateOutModal('{{ $shp->id }}', '{{ $shp->shipment_number ?? $shp->project_no }}', '{{ $shp->client->name ?? '' }}', '{{ $shp->trucking_vendor_name ?? '' }}')"
                                            {{ !$shp->do_is_released ? 'disabled title="Wajib rilis DO terlebih dahulu"' : '' }}
                                            class="btn-apple-filled !h-8 !px-3 !text-[11px] {{ !$shp->do_is_released ? 'opacity-40 cursor-not-allowed' : '' }}">
                                        <i data-lucide="truck" class="w-3.5 h-3.5"></i>
                                        <span>Pilih Trucking &amp; Gate Out</span>
                                    </button>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[#248A3D] dark:text-[#30D158] font-semibold text-[11px] bg-[#34C759]/15 px-2.5 py-1 rounded-full border border-[#34C759]/25">
                                        <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                        <span>Kargo Selesai (POD)</span>
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-black/40 dark:text-white/40 text-[13px]">
                                <i data-lucide="truck" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                                <div>Belum ada shipment pada antrian Tim Traffic.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-black/5 dark:border-white/5">
            {{ $shipments->links() }}
        </div>
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

<!-- Modal Gate Out TPS & Pemilihan Vendor Trucking (Apple HIG) -->
<div id="modal-gate-out" class="hidden fixed inset-0 z-[200] overflow-y-auto bg-black/60 backdrop-blur-md flex items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="app-modal-dialog app-modal-dialog-md rounded-t-[20px] sm:rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl p-6 text-[13px] transform transition-all max-w-lg w-full">
        <div class="w-12 h-1.5 bg-black/10 dark:bg-white/10 rounded-full mx-auto mb-4 sm:hidden"></div>
        <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5 mb-4">
            <div>
                <h3 class="text-[16px] font-bold text-black dark:text-white">Pilih Vendor Trucking &amp; Gate Out TPS</h3>
                <p class="text-[11px] text-black/50 dark:text-white/50">Penugasan armada pengiriman dari pelabuhan menuju gudang klien</p>
            </div>
            <button type="button" onclick="document.getElementById('modal-gate-out').classList.add('hidden')" class="p-1 rounded-[8px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white hover:bg-black/[0.05] dark:hover:bg-white/[0.08] transition-colors cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="form-gate-out" action="" method="POST" class="space-y-3.5">
            @csrf
            <!-- 1. Vendor Trucking -->
            <div>
                <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Vendor Trucking / Ekspedisi <span class="text-[#FF3B30]">*</span></label>
                <input type="text" id="gateout_trucking_vendor" name="trucking_vendor_name" list="trucking_vendor_list" required placeholder="Pilih atau ketik vendor trucking..." class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#0284c7]">
                <datalist id="trucking_vendor_list">
                    @if(isset($shippingVendors))
                        @foreach($shippingVendors as $v)
                            <option value="{{ $v->name }}">{{ $v->code ? '[' . $v->code . ']' : '' }}</option>
                        @endforeach
                    @endif
                    <option value="PT Agung Trans Logistik">Armada Internal ATS</option>
                    <option value="PT Samudera Logistik Nusantara"></option>
                    <option value="PT Dunex Trans Express"></option>
                </datalist>
            </div>

            <!-- 2. Gudang / Pabrik Tujuan -->
            <div>
                <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Gudang / Alamat Tujuan Pengiriman <span class="text-[#FF3B30]">*</span></label>
                <input type="text" id="gateout_destination" name="destination_address" required placeholder="Contoh: Gudang MM2100 Blok C-4 Cikarang Barat" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#0284c7]">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <!-- 3. No Polisi Truk -->
                <div>
                    <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">No. Polisi Truk Trailer <span class="text-[#FF3B30]">*</span></label>
                    <input type="text" name="truck_plate_number" required placeholder="Contoh: B 9281 UEM" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white font-mono uppercase text-[13px] focus:outline-none focus:border-[#0284c7]">
                </div>

                <!-- 4. Nama Supir -->
                <div>
                    <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Nama Pengemudi / Supir <span class="text-[#FF3B30]">*</span></label>
                    <input type="text" name="driver_name" required placeholder="Contoh: Joko Susilo" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#0284c7]">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <!-- 5. No Surat Jalan -->
                <div>
                    <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">No. Surat Jalan Ekspedisi <span class="text-[#FF3B30]">*</span></label>
                    <input type="text" name="surat_jalan_number" required placeholder="Contoh: SJ-ATS-202611-0081" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white font-mono text-[13px] focus:outline-none focus:border-[#0284c7]">
                </div>

                <!-- 6. Ongkos Angkut / Biaya Trucking (Otomatis masuk Rekap Biaya) -->
                <div>
                    <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Biaya Trucking (IDR)</label>
                    <input type="number" step="1000" name="trucking_cost" placeholder="Contoh: 3500000" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white font-mono text-[13px] focus:outline-none focus:border-[#0284c7]">
                    <span class="text-[10px] text-black/40 dark:text-white/40">Otomatis dicatat ke rekapan biaya</span>
                </div>
            </div>

            <div class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-gate-out').classList.add('hidden')" class="btn-apple-gray !h-9 text-[12px]">Batal</button>
                <button type="submit" class="btn-apple-filled !h-9 text-[12px] !bg-[#0284c7] hover:!bg-[#0066D6]">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Tugaskan Trucking &amp; Gate Out</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Rekap Biaya Operasional Lapangan & Pelabuhan (DO, Storage, LOLO, Trucking) -->
<div id="modal-cost-recap" class="hidden fixed inset-0 z-[200] overflow-y-auto bg-black/60 backdrop-blur-md flex items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="app-modal-dialog app-modal-dialog-lg rounded-t-[20px] sm:rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl p-6 text-[13px] transform transition-all max-w-2xl w-full max-h-[90vh] overflow-y-auto space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5">
            <div>
                <h3 class="text-[16px] font-bold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="receipt" class="w-5 h-5 text-emerald-600"></i>
                    <span>Rekapan Biaya Operasional Kargo</span>
                </h3>
                <p class="text-[11px] text-black/50 dark:text-white/50" id="cost_recap_shipment_label">Rincian biaya D/O, Behandle, Storage/Penumpukan, LOLO, dan Trucking</p>
            </div>
            <button type="button" onclick="document.getElementById('modal-cost-recap').classList.add('hidden')" class="p-1 rounded-[8px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white hover:bg-black/[0.05] dark:hover:bg-white/[0.08] transition-colors cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Tabel Rincian Biaya Tersimpan -->
        <div class="rounded-xl border border-black/10 dark:border-white/10 overflow-hidden">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-white/[0.03] text-black/60 dark:text-white/60 font-semibold border-b border-black/10 dark:border-white/10 text-[11px]">
                    <tr>
                        <th class="py-2.5 px-3">Kategori</th>
                        <th class="py-2.5 px-3">Vendor / Penerima</th>
                        <th class="py-2.5 px-3">No. Kwitansi / INV</th>
                        <th class="py-2.5 px-3 text-right">Nominal (IDR)</th>
                    </tr>
                </thead>
                <tbody id="cost_recap_tbody" class="divide-y divide-black/5 dark:divide-white/5">
                    <!-- Dynamic Rows via JS -->
                </tbody>
                <tfoot class="bg-slate-50 dark:bg-white/[0.03] border-t border-black/10 dark:border-white/10 font-bold">
                    <tr>
                        <td colspan="3" class="py-2.5 px-3 text-right text-xs">TOTAL BIAYA OPERASIONAL:</td>
                        <td class="py-2.5 px-3 text-right font-mono text-emerald-600 dark:text-emerald-400 text-xs font-bold" id="cost_recap_total_amount">Rp 0</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Form Tambah Biaya Operasional Cepat -->
        <div class="p-4 rounded-xl bg-slate-50 dark:bg-white/[0.02] border border-black/10 dark:border-white/10 space-y-3">
            <h4 class="font-bold text-xs text-black dark:text-white flex items-center gap-1.5">
                <i data-lucide="plus-circle" class="w-4 h-4 text-[#0284c7]"></i>
                <span>+ Catat Biaya Operasional Baru</span>
            </h4>
            <form id="form-store-operation-cost" action="" method="POST" class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-black/70 dark:text-white/70 mb-1 text-[11px]">Kategori Biaya <span class="text-[#FF3B30]">*</span></label>
                    <select name="cost_category" required class="w-full px-2.5 py-1.5 rounded-lg border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white">
                        @foreach(\App\Models\ShipmentOperationCost::CATEGORIES as $cKey => $cLabel)
                            <option value="{{ $cKey }}">{{ $cLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-black/70 dark:text-white/70 mb-1 text-[11px]">Nama Vendor / Pelayaran</label>
                    <input type="text" name="vendor_name" id="cost_input_vendor_name" placeholder="Contoh: PT Samudera / Dunex" class="w-full px-2.5 py-1.5 rounded-lg border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white">
                </div>
                <div>
                    <label class="block font-semibold text-black/70 dark:text-white/70 mb-1 text-[11px]">No. Kwitansi / Faktur / DO</label>
                    <input type="text" name="invoice_number" placeholder="Contoh: INV-DO-0091" class="w-full px-2.5 py-1.5 rounded-lg border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white font-mono">
                </div>
                <div>
                    <label class="block font-semibold text-black/70 dark:text-white/70 mb-1 text-[11px]">Nominal Biaya (IDR) <span class="text-[#FF3B30]">*</span></label>
                    <input type="number" step="0.01" name="amount" required placeholder="Contoh: 1500000" class="w-full px-2.5 py-1.5 rounded-lg border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white font-mono font-bold">
                </div>
                <div class="sm:col-span-2 flex justify-end pt-1">
                    <button type="submit" class="btn-apple-filled !h-8 !px-4 text-xs !bg-[#0284c7] hover:!bg-[#0066D6] cursor-pointer">
                        <span>+ Simpan Biaya ke Rekapan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal POD Delivery (Apple HIG z-[200] Standalone Modal Architecture) -->
<div id="modal-pod" class="hidden fixed inset-0 z-[200] overflow-y-auto bg-black/60 backdrop-blur-md flex items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="app-modal-dialog app-modal-dialog-md rounded-t-[20px] sm:rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl p-6 text-[13px] transform transition-all">
        <div class="w-12 h-1.5 bg-black/10 dark:bg-white/10 rounded-full mx-auto mb-4 sm:hidden"></div>
        <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5 mb-4">
            <h3 class="text-[16px] font-bold text-black dark:text-white">Konfirmasi Bukti Terima (POD)</h3>
            <button type="button" onclick="document.getElementById('modal-pod').classList.add('hidden')" class="p-1 rounded-[8px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white hover:bg-black/[0.05] dark:hover:bg-white/[0.08] transition-colors cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="form-pod" action="" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Nama Penerima Barang di Gudang Klien <span class="text-[#FF3B30]">*</span></label>
                <input type="text" name="receiver_name" required placeholder="Contoh: Bpk. Bambang Pamungkas (Warehouse Head)" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#0284c7]">
            </div>

            <div>
                <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Unggah Foto Surat Jalan Basah & Serah Terima (POD)</label>
                <input type="file" name="pod_file" class="w-full text-[12px] text-black/60 dark:text-white/60 file:mr-2 file:py-1.5 file:px-3 file:rounded-[8px] file:border-0 file:bg-black/[0.05] dark:file:bg-white/[0.08] file:text-black dark:file:text-white">
            </div>

            <p class="text-[11px] text-black/50 dark:text-white/50 bg-black/[0.02] dark:bg-white/[0.02] p-2.5 rounded-[10px] border border-black/5 dark:border-white/5">
                *Konfirmasi serah terima kargo akan otomatis mengubah status menjadi <strong>DELIVERED</strong> dan menerbitkan draf <strong>Invoice Penagihan Klien</strong> pada modul Finance & Accounting.
            </p>

            <div class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-pod').classList.add('hidden')" class="btn-apple-gray !h-9 text-[12px]">Batal</button>
                <button type="submit" class="btn-apple-filled !bg-[#34C759] hover:!bg-[#248A3D] !h-9 text-[12px]">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <span>Simpan POD & Terbitkan Invoice</span>
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

    fetch(`/core/traffic/${currentTrackingShipmentId}/live-detect`, {
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

function openGateOutModal(shipmentId, shipmentNumber, clientName = '', existingVendor = '') {
    const modal = document.getElementById('modal-gate-out');
    const form = document.getElementById('form-gate-out');
    form.action = `/core/traffic/${shipmentId}/gate-out`;
    
    const destInput = document.getElementById('gateout_destination');
    if (destInput && !destInput.value) {
        destInput.value = clientName ? `Gudang Pabrik ${clientName}` : '';
    }
    const vendorInput = document.getElementById('gateout_trucking_vendor');
    if (vendorInput) {
        vendorInput.value = existingVendor || '';
    }
    
    modal.classList.remove('hidden');
    if (window.lucide) window.lucide.createIcons();
}

function openCostRecapModal(shipmentId, shipmentNumber, costs = [], vendorName = '') {
    const modal = document.getElementById('modal-cost-recap');
    const form = document.getElementById('form-store-operation-cost');
    form.action = `/core/traffic/${shipmentId}/operation-costs`;
    
    const label = document.getElementById('cost_recap_shipment_label');
    if (label) label.textContent = `Shipment No: ${shipmentNumber} — Rincian D/O, Behandle, Storage, LOLO & Trucking`;
    
    const vendorInput = document.getElementById('cost_input_vendor_name');
    if (vendorInput) vendorInput.value = vendorName || '';
    
    const tbody = document.getElementById('cost_recap_tbody');
    tbody.innerHTML = '';
    let total = 0;
    
    if (costs && costs.length > 0) {
        costs.forEach(c => {
            const amt = parseFloat(c.amount) || 0;
            total += amt;
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors';
            tr.innerHTML = `
                <td class="py-2 px-3 font-semibold text-slate-800 dark:text-slate-200">
                    <span class="px-2 py-0.5 rounded bg-black/5 dark:bg-white/10 font-mono text-[10px]">${c.cost_category}</span>
                    <span class="ml-1">${c.category_label || c.cost_category}</span>
                </td>
                <td class="py-2 px-3 text-slate-600 dark:text-slate-400">${c.vendor_name || '-'}</td>
                <td class="py-2 px-3 font-mono text-[11px] text-slate-700 dark:text-slate-300">${c.invoice_number || '-'}</td>
                <td class="py-2 px-3 text-right font-mono font-bold text-slate-900 dark:text-white">Rp ${amt.toLocaleString('id-ID')}</td>
            `;
            tbody.appendChild(tr);
        });
    } else {
        tbody.innerHTML = `<tr><td colspan="4" class="py-5 text-center text-slate-400 dark:text-slate-500 italic">Belum ada rincian biaya operasional yang dicatat untuk shipment ini.</td></tr>`;
    }
    
    const totalEl = document.getElementById('cost_recap_total_amount');
    if (totalEl) totalEl.textContent = `Rp ${total.toLocaleString('id-ID')}`;
    
    modal.classList.remove('hidden');
    if (window.lucide) window.lucide.createIcons();
}

function openPodModal(shipmentId, shipmentNumber) {
    const modal = document.getElementById('modal-pod');
    const form = document.getElementById('form-pod');
    form.action = `/core/traffic/${shipmentId}/deliver-pod`;
    modal.classList.remove('hidden');
    if (window.lucide) window.lucide.createIcons();
}

function openPhotoInspectionModal(shipmentId, shipmentNumber, containerNo, sealNo) {
    const modal = document.getElementById('modal-inspection-photo');
    document.getElementById('form-inspection-photo').action = `/core/traffic/${shipmentId}/inspection-photos`;
    document.getElementById('ip-subtitle').textContent = shipmentNumber;
    document.getElementById('ip-container').value = containerNo || '';
    document.getElementById('ip-seal').value = sealNo || '';
    modal.classList.remove('hidden');
    if (window.lucide) window.lucide.createIcons();
}

function openTrafficEmailModal(shipmentId, shipmentNumber, vesselName, clientEmail) {
    const modal = document.getElementById('modal-send-traffic-email');
    const form = document.getElementById('form-send-traffic-email');
    form.action = `/core/traffic/${shipmentId}/send-eta-email`;
    document.getElementById('traffic_email_shipment_label').value = `${shipmentNumber} (${vesselName || 'Armada Kapal/Pesawat'})`;
    document.getElementById('traffic_email_recipient').value = clientEmail || '';
    document.getElementById('traffic_email_preview_link').href = `/emails/preview/traffic/${shipmentId}`;
    modal.classList.remove('hidden');
    if (window.lucide) window.lucide.createIcons();
}
</script>

<!-- Modal Foto Pemeriksaan Fisik SPJM & Segel Kembali -->
<div id="modal-inspection-photo" class="hidden fixed inset-0 z-[200] overflow-y-auto bg-black/60 backdrop-blur-md flex items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="app-modal-dialog app-modal-dialog-md rounded-t-[20px] sm:rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl p-6 text-[13px]">
        <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5 mb-4">
            <div>
                <h3 class="text-[16px] font-bold text-black dark:text-white">Upload Foto Pemeriksaan Fisik / Segel Kembali</h3>
                <p id="ip-subtitle" class="text-[11px] font-mono text-black/50 dark:text-white/50"></p>
            </div>
            <button type="button" onclick="document.getElementById('modal-inspection-photo').classList.add('hidden')" class="p-1 rounded-[8px] text-black/40 hover:text-black cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="form-inspection-photo" method="POST" enctype="multipart/form-data" class="space-y-3.5">
            @csrf
            <div>
                <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Tahapan Pemeriksaan</label>
                <select name="stage" required class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-[13px]">
                    @foreach(\App\Models\InspectionPhoto::IMPORT_STAGES as $k => $lbl)
                        <option value="{{ $k }}">{{ $lbl }}</option>
                    @endforeach
                    @foreach(\App\Models\InspectionPhoto::EXPORT_STAGES as $k => $lbl)
                        <option value="{{ $k }}">{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Nomor Kontainer</label>
                    <input type="text" name="container_no" id="ip-container" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-[13px]">
                </div>
                <div>
                    <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Nomor Segel Baru</label>
                    <input type="text" name="seal_number" id="ip-seal" placeholder="BC-SEAL-8821" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-[13px]">
                </div>
            </div>
            <div>
                <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Foto Lapangan (Bisa Multi-File)</label>
                <input type="file" name="photos[]" multiple required accept=".jpg,.jpeg,.png,.webp,.pdf" class="w-full text-[12px]">
            </div>
            <div>
                <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Catatan Lapangan</label>
                <input type="text" name="caption" placeholder="Kondisi barang, pemeriksa DJBC, penyegelan ulang..." class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-[13px]">
            </div>
            <div class="pt-3 border-t border-black/5 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-inspection-photo').classList.add('hidden')" class="btn-apple-gray !h-9 text-[12px]">Batal</button>
                <button type="submit" class="btn-apple-filled !h-9 text-[12px]">Simpan Foto Pemeriksaan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Kirim Email Update Posisi Kargo & ETA (Divisi Traffic) -->
<div id="modal-send-traffic-email" class="hidden fixed inset-0 z-[200] overflow-y-auto bg-black/60 backdrop-blur-md flex items-end sm:items-center justify-center p-0 sm:p-4">
    <div class="app-modal-dialog app-modal-dialog-md rounded-t-[20px] sm:rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl p-6 text-[13px] space-y-4 max-w-lg w-full">
        <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-sky-500/15 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold">
                    <i data-lucide="navigation" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-black dark:text-white">Kirim Email Update Status Kargo &amp; ETA</h3>
                    <p class="text-[11px] text-black/50 dark:text-white/50 mt-0.5">Dikirim atas nama resmi Divisi Traffic &amp; Armada Lapangan</p>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('modal-send-traffic-email').classList.add('hidden')" class="btn-apple-gray !p-1.5 rounded-full">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Identitas Pengirim Resmi Divisi Traffic -->
        <div class="p-3 rounded-2xl bg-sky-50/50 dark:bg-sky-950/20 border border-sky-200/80 dark:border-sky-900/40 text-[11px] space-y-1">
            <div class="flex items-center justify-between">
                <span class="font-semibold text-sky-900 dark:text-sky-300 flex items-center gap-1.5">
                    <i data-lucide="badge-check" class="w-3.5 h-3.5 text-sky-600"></i>
                    <span>Divisi Pengirim: <strong>Traffic &amp; Dispatch Lapangan</strong></span>
                </span>
                <span class="px-2 py-0.5 rounded-md font-mono text-[10px] font-bold bg-sky-200/80 dark:bg-sky-900/60 text-sky-900 dark:text-sky-200">
                    {{ \App\Models\SystemSetting::get('email_traffic_from_address', 'traffic@genie-logistics.id') }}
                </span>
            </div>
            <div class="text-slate-600 dark:text-slate-300">
                Nama Pengirim: <strong>{{ \App\Models\SystemSetting::get('email_traffic_from_name', 'GENIE Traffic & Fleet Dispatch') }}</strong> &bull; Reply-To: <span class="font-mono">{{ \App\Models\SystemSetting::get('email_traffic_reply_to', 'traffic@genie-logistics.id') }}</span>
            </div>
        </div>

        <form id="form-send-traffic-email" action="" method="POST" class="space-y-3.5">
            @csrf
            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1 text-[11px]">Proyek / Sarana Pengangkut</label>
                <input type="text" id="traffic_email_shipment_label" readonly class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-700 dark:text-slate-200 text-xs font-semibold cursor-not-allowed">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1 text-[11px]">Email Klien Penerima <span class="text-rose-500">*</span></label>
                <input type="email" name="recipient_email" id="traffic_email_recipient" required placeholder="logistik@klien.com" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono text-xs focus:ring-2 focus:ring-sky-500 focus:outline-none">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1 text-[11px]">Format Lampiran Surat Jalan Resmi (MoM Poin 2)</label>
                <select name="surat_jalan_template" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white text-xs focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    <option value="fcl">Template 1: Pengiriman Kontainer FCL (Pelabuhan ke Gudang Klien)</option>
                    <option value="lcl">Template 2: Pengiriman Kargo LCL / Air Freight Parsial</option>
                    <option value="inter_factory">Template 3: Pengeluaran Antar Pabrik KB (Kawasan Berikat)</option>
                    <option value="empty_depo">Template 4: Pengembalian Kontainer Kosong (Empty Depo Return)</option>
                </select>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1 text-[11px]">Catatan Lapangan / Update ETA (Opsional)</label>
                <textarea name="custom_notes" rows="3" placeholder="Contoh: Kapal telah sandar di dermaga JICT 1. SPPB telah terbit dan truk standby untuk proses pengeluaran kontainer..." class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white text-xs focus:ring-2 focus:ring-sky-500 focus:outline-none"></textarea>
            </div>

            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200/80 dark:border-white/10 flex items-center justify-between text-[11px] text-slate-600 dark:text-slate-300">
                <span class="flex items-center gap-1.5">
                    <i data-lucide="file-text" class="w-3.5 h-3.5 text-rose-500"></i>
                    <span>Lampiran: <strong>Surat Jalan PDF</strong></span>
                </span>
                <a id="traffic_email_preview_link" href="#" target="_blank" class="text-sky-600 dark:text-sky-400 hover:underline font-semibold flex items-center gap-1">
                    <span>Preview Format Email</span>
                    <i data-lucide="external-link" class="w-3 h-3"></i>
                </a>
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-white/10 flex items-center justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-send-traffic-email').classList.add('hidden')" class="btn-apple-gray !h-9 text-[12px]">Batal</button>
                <button type="submit" class="btn-apple-filled !bg-sky-600 hover:!bg-sky-700 !h-9 text-[12px] inline-flex items-center gap-1.5 text-white">
                    <i data-lucide="send" class="w-3.5 h-3.5"></i>
                    <span>Kirim Email Traffic Sekarang</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
