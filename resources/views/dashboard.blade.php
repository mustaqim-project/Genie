@extends('layouts.app')

@section('title', 'Dashboard Kepabeanan & Logistik')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">

    <!-- 1. HERO BANNER (LINEAR / APPLE DARK-TECH AESTHETICS) -->
    <div class="relative overflow-hidden rounded-3xl p-6 sm:p-8 text-white shadow-xl mb-8 border border-slate-800"
         style="background: radial-gradient(circle at 90% 10%, rgba(14, 165, 233, 0.2), transparent 40%), radial-gradient(circle at 10% 90%, rgba(99, 102, 241, 0.15), transparent 40%), #0B0F19;">
        
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="max-w-2xl">
                <!-- Eyebrow Pill -->
                <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-white/10 text-sky-300 text-xs font-semibold backdrop-blur-md border border-white/10 mb-3.5 shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="tracking-wide">Host-to-Host CEISA 4.0 DJBC Gateway v2.0</span>
                </div>

                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight text-white leading-tight">
                    Pusat Integrasi Kepabeanan & Smart Logistics
                </h1>
                <p class="text-slate-300 text-xs sm:text-sm mt-2 leading-relaxed">
                    Automasi pertukaran dokumen pabean impor (BC 2.0) & ekspor (BC 3.0), manajemen DO pelayaran, traffic peti kemas, dan akuntansi logistik berstandar industri Indonesia.
                </p>
            </div>

            <!-- Action Buttons ("Button-in-Button" Architecture) -->
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('bc20.create') }}" 
                   class="inline-flex items-center space-x-2.5 pl-4 pr-2.5 py-2.5 rounded-2xl bg-sky-500 hover:bg-sky-400 text-white text-xs sm:text-sm font-bold shadow-lg shadow-sky-500/25 transition-all group active:scale-95">
                    <span>Draft BC 2.0 (Impor)</span>
                    <span class="w-7 h-7 rounded-xl bg-white/20 flex items-center justify-center group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform flex-shrink-0">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </span>
                </a>

                <a href="{{ route('bc30.create') }}" 
                   class="inline-flex items-center space-x-2.5 pl-4 pr-2.5 py-2.5 rounded-2xl bg-white/10 hover:bg-white/15 text-white text-xs sm:text-sm font-bold border border-white/15 backdrop-blur-md transition-all group active:scale-95">
                    <span>Draft BC 3.0 (Ekspor)</span>
                    <span class="w-7 h-7 rounded-xl bg-white/10 flex items-center justify-center group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform flex-shrink-0">
                        <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </span>
                </a>
            </div>
        </div>
    </div>

    <!-- 2. BENTO STATISTICS GRID (DOUBLE-BEZEL CONCENTRIC CURVES) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        
        <!-- BC 2.0 Impor Card -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-[0_1px_3px_rgba(0,0,0,0.02),0_4px_16px_rgba(0,0,0,0.02)] hover:border-slate-300 transition-all group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500">Impor (BC 2.0 PIB)</span>
                <span class="p-2 rounded-xl bg-sky-50 text-sky-600 group-hover:scale-105 transition-transform">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                    </svg>
                </span>
            </div>
            <div class="text-3xl font-extrabold font-mono text-slate-900 tracking-tight">
                {{ $totalBC20 }} <span class="text-xs font-semibold text-slate-400 font-sans">Dokumen</span>
            </div>
            <div class="text-[11px] text-slate-500 mt-2 flex items-center justify-between pt-2 border-t border-slate-100">
                <span>Nilai Pabean CIF:</span>
                <span class="font-mono font-bold text-slate-800">Rp {{ number_format($totalCifIdr, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- BC 3.0 Ekspor Card -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-[0_1px_3px_rgba(0,0,0,0.02),0_4px_16px_rgba(0,0,0,0.02)] hover:border-slate-300 transition-all group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500">Ekspor (BC 3.0 PEB)</span>
                <span class="p-2 rounded-xl bg-indigo-50 text-indigo-600 group-hover:scale-105 transition-transform">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                    </svg>
                </span>
            </div>
            <div class="text-3xl font-extrabold font-mono text-slate-900 tracking-tight">
                {{ $totalBC30 }} <span class="text-xs font-semibold text-slate-400 font-sans">Dokumen</span>
            </div>
            <div class="text-[11px] text-slate-500 mt-2 flex items-center justify-between pt-2 border-t border-slate-100">
                <span>Devisa Ekspor FOB:</span>
                <span class="font-mono font-bold text-slate-800">Rp {{ number_format($totalFobIdr, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- SPPB & NPE Card -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-[0_1px_3px_rgba(0,0,0,0.02),0_4px_16px_rgba(0,0,0,0.02)] hover:border-slate-300 transition-all group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500">Persetujuan Terbit</span>
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600 group-hover:scale-105 transition-transform">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div class="text-3xl font-extrabold font-mono text-emerald-600 tracking-tight">
                {{ $totalSppb + $totalNpe }} <span class="text-xs font-semibold text-slate-400 font-sans">Dokumen</span>
            </div>
            <div class="text-[11px] text-slate-500 mt-2 flex items-center justify-between pt-2 border-t border-slate-100 font-mono">
                <span>SPPB: <strong class="text-emerald-700 font-bold">{{ $totalSppb }}</strong></span>
                <span class="text-slate-300">&bull;</span>
                <span>NPE: <strong class="text-indigo-700 font-bold">{{ $totalNpe }}</strong></span>
            </div>
        </div>

        <!-- Billing MPN G3 Card -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-[0_1px_3px_rgba(0,0,0,0.02),0_4px_16px_rgba(0,0,0,0.02)] hover:border-slate-300 transition-all group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500">Billing MPN G3 Pabean</span>
                <span class="p-2 rounded-xl bg-amber-50 text-amber-600 group-hover:scale-105 transition-transform">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </span>
            </div>
            <div class="text-3xl font-extrabold font-mono text-amber-600 tracking-tight">
                {{ $totalBillingAktif }} <span class="text-xs font-semibold text-slate-400 font-sans">Aktif</span>
            </div>
            <div class="text-[11px] text-slate-500 mt-2 flex items-center justify-between pt-2 border-t border-slate-100">
                <span>Status Tagihan:</span>
                <span class="font-bold text-amber-700">Menunggu Pelunasan</span>
            </div>
        </div>
    </div>

    <!-- 3. MAIN WORKSPACE GRID: RECENT DOCUMENTS & CEISA API LOGS -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        
        <!-- Recent Documents Table (2 Columns) -->
        <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200/80 shadow-[0_1px_3px_rgba(0,0,0,0.02),0_4px_16px_rgba(0,0,0,0.02)] overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-slate-900 text-sm">Pengajuan Dokumen Kepabeanan Terkini</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Daftar dokumen BC 2.0 (PIB) dan BC 3.0 (PEB) yang aktif diproses</p>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('bc20.index') }}" class="text-xs font-bold text-sky-600 hover:text-sky-700 transition-colors">
                        Lihat Semua &rarr;
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/90 text-slate-500 font-semibold border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">Nomor Aju & Tanggal</th>
                            <th class="py-3 px-4">Jenis</th>
                            <th class="py-3 px-4">Mitra Luar Negeri</th>
                            <th class="py-3 px-4">Nilai Pabean</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentDocuments as $doc)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3.5 px-4 font-mono font-semibold text-slate-900">
                                    <a href="{{ $doc->jenis_dokumen === '20' ? route('bc20.show', $doc->id) : route('bc30.show', $doc->id) }}" class="text-sky-600 hover:underline">
                                        {{ $doc->nomor_aju }}
                                    </a>
                                    <div class="text-[10px] text-slate-400 font-sans">
                                        {{ $doc->created_at ? $doc->created_at->format('d M Y, H:i') : '-' }}
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold font-mono {{ $doc->jenis_dokumen === '20' ? 'bg-sky-100 text-sky-800' : 'bg-indigo-100 text-indigo-800' }}">
                                        {{ $doc->jenis_dokumen === '20' ? 'BC 2.0' : 'BC 3.0' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-medium text-slate-700 truncate max-w-[160px]">
                                    <div class="font-semibold text-slate-900 truncate">{{ $doc->nama_mitra_luar_negeri ?? '-' }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $doc->negara_mitra_luar_negeri ?? 'NEGARA LN' }}</div>
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-slate-900 font-mono">
                                    {{ $doc->kode_valuta }} {{ number_format($doc->total_nilai, 2) }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $doc->status_badge_class }}">
                                        {{ $doc->status }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="{{ $doc->jenis_dokumen === '20' ? route('bc20.show', $doc->id) : route('bc30.show', $doc->id) }}" 
                                       class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-[11px] transition-colors">
                                        Detail &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    Belum ada data dokumen pabean aktif. Silakan buat draf pengajuan baru di atas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent API Logs (1 Column) -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-[0_1px_3px_rgba(0,0,0,0.02),0_4px_16px_rgba(0,0,0,0.02)] overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-slate-900 text-sm">Audit Trail Gateway CEISA 4.0</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Komunikasi H2H API dengan DJBC</p>
                </div>
                <a href="{{ route('config.index') }}" class="text-xs font-bold text-sky-600 hover:text-sky-700 transition-colors">
                    Semua Log
                </a>
            </div>

            <div class="p-4 flex-1 space-y-3 overflow-y-auto max-h-[380px] custom-scrollbar">
                @forelse($recentLogs as $log)
                    <div class="p-3 rounded-2xl border border-slate-100 bg-slate-50/60 text-xs hover:border-slate-200 transition-colors">
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center space-x-1.5 truncate">
                                <span class="px-1.5 py-0.5 rounded font-mono font-bold text-[10px] {{ $log->method === 'POST' ? 'bg-emerald-100 text-emerald-800' : 'bg-sky-100 text-sky-800' }}">
                                    {{ $log->method }}
                                </span>
                                <span class="font-mono text-slate-700 truncate max-w-[140px] text-[11px]">{{ $log->endpoint }}</span>
                            </div>
                            <span class="px-2 py-0.5 rounded-full font-bold text-[10px] font-mono {{ $log->status_code === 200 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
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
                        Belum ada riwayat aktivitas panggilan API DJBC.
                    </div>
                @endforelse
            </div>

            <div class="p-4 bg-slate-50/80 border-t border-slate-100">
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
