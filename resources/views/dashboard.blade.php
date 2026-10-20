@extends('layouts.app')

@section('title', 'Dashboard Operasional Kepabeanan & Logistik')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 pt-6 pb-12" x-data="{ docFilter: 'ALL' }">

    <!-- 0. BREADCRUMB -->
    <nav class="flex items-center gap-1.5 text-xs text-slate-500 py-0.5 whitespace-nowrap print:hidden mb-3" aria-label="Breadcrumb">
        <span class="text-slate-800 font-semibold">Dashboard Operasional</span>
    </nav>

    <!-- 1. TOP HEADER & QUICK ACTION BAR -->
    <div class="relative overflow-hidden rounded-2xl p-6 sm:p-7 text-white shadow-lg mb-8 border border-slate-800 bg-slate-900">
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="max-w-2xl">
                <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-slate-800 text-sky-400 text-xs font-semibold border border-slate-700 mb-3">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    <span class="tracking-wide">Host-to-Host CEISA 4.0 DJBC Aktif</span>
                </div>

                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white leading-tight">
                    Sistem Operasional Kepabeanan & Logistik
                </h1>
                <p class="text-slate-300 text-xs sm:text-sm mt-1.5 leading-relaxed">
                    Pusat kendali proyek pengapalan: penerimaan berkas, draf PIB/PEB, pengajuan CEISA 4.0, penebusan DO pelayaran, dan pengeluaran barang pelabuhan.
                </p>
            </div>

            <!-- Action Buttons Toolbar -->
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('core.document.index') }}" 
                   class="min-h-[42px] px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white text-xs sm:text-sm font-semibold shadow-xs transition-all flex items-center gap-2 active:scale-95">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Proyek Pengapalan</span>
                </a>

                <a href="{{ route('bc20.index') }}" 
                   class="min-h-[42px] px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold border border-slate-700 transition-all flex items-center gap-1.5 active:scale-95">
                    <span>Daftar BC 2.0 (PIB)</span>
                </a>

                <a href="{{ route('bc30.index') }}" 
                   class="min-h-[42px] px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold border border-slate-700 transition-all flex items-center gap-1.5 active:scale-95">
                    <span>Daftar BC 3.0 (PEB)</span>
                </a>
            </div>
        </div>
    </div>

    <!-- 2. BENTO STATISTICS GRID -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        
        <!-- KPI 1: Proyek Pengapalan Aktif -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-4 hover:border-slate-300 transition-all">
            <div class="w-11 h-11 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Proyek Pengapalan</div>
                <div class="text-xl font-extrabold text-slate-900 font-mono">{{ $totalShipments ?? 0 }}</div>
                <div class="text-[10px] text-slate-400">
                    {{ $shipmentsCollecting ?? 0 }} kumpul berkas &bull; {{ $shipmentsBilling ?? 0 }} billing
                </div>
            </div>
        </div>

        <!-- KPI 2: BC 2.0 Impor Card -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-4 hover:border-slate-300 transition-all">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Impor (BC 2.0 PIB)</div>
                <div class="text-xl font-extrabold text-slate-900 font-mono">{{ $totalBC20 }}</div>
                <div class="text-[10px] text-slate-400 truncate">
                    CIF: Rp {{ number_format($totalCifIdr, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <!-- KPI 3: BC 3.0 Ekspor Card -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-4 hover:border-slate-300 transition-all">
            <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Ekspor (BC 3.0 PEB)</div>
                <div class="text-xl font-extrabold text-slate-900 font-mono">{{ $totalBC30 }}</div>
                <div class="text-[10px] text-slate-400 truncate">
                    FOB: Rp {{ number_format($totalFobIdr, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <!-- KPI 4: Persetujuan Terbit & Billing -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-4 hover:border-slate-300 transition-all">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">SPPB & NPE Terbit</div>
                <div class="text-xl font-extrabold text-emerald-700 font-mono">{{ $totalSppb + $totalNpe }}</div>
                <div class="text-[10px] text-slate-400 font-mono">
                    Billing Aktif: <strong class="text-amber-700">{{ $totalBillingAktif }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. MAIN WORKSPACE GRID: PROYEK PENGAPALAN TERBARU & AUDIT LOG -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        
        <!-- Kolom Kiri: Proyek Pengapalan Terbaru (2 Kolom) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-slate-900 text-sm">Proyek Pengapalan Terkini</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Daftar berkas pengapalan aktif dan tahap operasional</p>
                </div>
                <a href="{{ route('core.document.index') }}" class="text-xs font-bold text-sky-600 hover:underline">
                    Buka Semua Proyek &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">No. Proyek & B/L</th>
                            <th class="py-3 px-4">Klien / Importir</th>
                            <th class="py-3 px-4">Kapal & Muatan</th>
                            <th class="py-3 px-4">Status Tahap</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentShipments as $shp)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3 px-4 font-mono font-semibold text-slate-900">
                                    <a href="{{ route('core.document.show', $shp->id) }}" class="text-sky-600 hover:underline">
                                        {{ $shp->shipment_number ?? $shp->project_no }}
                                    </a>
                                    <div class="text-[10px] text-slate-400 font-sans">
                                        B/L: {{ $shp->bl_number ?? '-' }}
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-slate-900 truncate max-w-[150px]">{{ $shp->client->name ?? '-' }}</div>
                                    <span class="inline-block mt-0.5 px-1.5 py-0.2 rounded text-[9px] font-bold font-sans {{ $shp->type === 'EXPORT' ? 'bg-amber-100 text-amber-800' : 'bg-sky-100 text-sky-800' }}">
                                        {{ $shp->type === 'EXPORT' ? 'BC 3.0' : 'BC 2.0' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-700">
                                    <div class="truncate max-w-[140px]">{{ $shp->vessel_name ?? '-' }}</div>
                                    <div class="text-[10px] font-mono text-slate-400">{{ $shp->nomor_kontainer ?? '1 Kontainer' }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold font-mono 
                                        @if(in_array($shp->status, ['BILLING_ISSUED', 'DO_RELEASED', 'DELIVERED_POD', 'COMPLETED'])) bg-emerald-100 text-emerald-800
                                        @elseif(in_array($shp->status, ['DOC_READY', 'HS_DETECTED', 'DRAFT_REVIEW'])) bg-indigo-100 text-indigo-800
                                        @elseif($shp->status === 'DO_PROCESSING') bg-amber-100 text-amber-800
                                        @else bg-slate-100 text-slate-700 @endif">
                                        {{ $shp->status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('core.document.show', $shp->id) }}" 
                                       class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-[11px] transition-colors">
                                        Detail &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-10 text-center text-slate-400">
                                    Belum ada proyek shipment terdaftar. <a href="{{ route('core.document.index') }}" class="text-sky-600 font-bold hover:underline">Buka proyek baru &rarr;</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Kolom Kanan: Log Respon Gateway CEISA 4.0 (1 Kolom) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-slate-900 text-sm">Audit Gateway CEISA 4.0</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Riwayat kirim & respon DJBC</p>
                </div>
                <a href="{{ route('config.index') }}" class="text-xs font-bold text-sky-600 hover:underline">
                    Konfigurasi
                </a>
            </div>

            <div class="p-4 flex-1 space-y-3 overflow-y-auto max-h-[360px] custom-scrollbar">
                @forelse($recentLogs as $log)
                    <div class="p-3 rounded-xl border border-slate-100 bg-slate-50/60 text-xs hover:border-slate-200 transition-colors">
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center space-x-1.5 truncate">
                                <span class="px-1.5 py-0.5 rounded font-mono font-bold text-[10px] {{ $log->method === 'POST' ? 'bg-emerald-100 text-emerald-800' : 'bg-sky-100 text-sky-800' }}">
                                    {{ $log->method }}
                                </span>
                                <span class="font-mono text-slate-700 truncate max-w-[140px] text-[11px]">{{ $log->endpoint }}</span>
                            </div>
                            <span class="px-2 py-0.5 rounded-full font-bold text-[10px] font-mono {{ $log->status_code === 200 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                {{ $log->status_code }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-slate-400 text-[10px] font-mono">
                            <span>Durasi: {{ $log->duration_ms ?? 0 }} ms</span>
                            <span>{{ $log->created_at ? $log->created_at->diffForHumans() : '-' }}</span>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-slate-400 text-xs">
                        Belum ada riwayat transmisi CEISA 4.0.
                    </div>
                @endforelse
            </div>

            <div class="p-4 bg-slate-50 border-t border-slate-100">
                <form action="{{ route('config.test-auth') }}" method="POST">
                    @csrf
                    <button type="submit" 
                            class="w-full py-2.5 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs transition-all shadow-xs flex items-center justify-center space-x-2 active:scale-95">
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
