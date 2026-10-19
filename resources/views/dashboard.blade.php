@extends('layouts.app')

@section('title', 'Dashboard Kepabeanan')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
    <!-- Header Hero -->
    <div class="bg-gradient-to-r from-slate-900 via-brand-900 to-slate-900 rounded-2xl p-6 sm:p-8 text-white shadow-xl shadow-slate-900/10 mb-8 border border-slate-800">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-white/10 text-brand-200 text-xs font-semibold backdrop-blur-sm mb-3">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    <span>Host-to-Host Integration Module</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight">Pusat Integrasi Kepabeanan CEISA 4.0</h1>
                <p class="text-slate-300 text-sm mt-1 max-w-2xl">
                    Automasi pertukaran dokumen pabean impor (BC 2.0) dan ekspor (BC 3.0) PT Agung Trans Solusindo langsung ke Gateway Direktorat Jenderal Bea dan Cukai (DJBC).
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('bc20.create') }}" class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold shadow-md shadow-brand-500/30 transition-all hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Ajukan BC 2.0 (Impor)</span>
                </a>
                <a href="{{ route('bc30.create') }}" class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-sm font-semibold border border-white/20 backdrop-blur-sm transition-all hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Ajukan BC 3.0 (Ekspor)</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Bento Statistics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <!-- BC 2.0 Impor Card -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Impor (BC 2.0)</span>
                <span class="p-2 rounded-xl bg-blue-50 text-blue-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                    </svg>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ $totalBC20 }} <span class="text-xs font-semibold text-slate-400">Dokumen</span></div>
            <div class="text-xs font-medium text-slate-500 mt-2">
                Total Nilai Pabean: <span class="font-bold text-slate-700">Rp {{ number_format($totalCifIdr, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- BC 3.0 Ekspor Card -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Ekspor (BC 3.0)</span>
                <span class="p-2 rounded-xl bg-indigo-50 text-indigo-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                    </svg>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ $totalBC30 }} <span class="text-xs font-semibold text-slate-400">Dokumen</span></div>
            <div class="text-xs font-medium text-slate-500 mt-2">
                Total Devisa Ekspor: <span class="font-bold text-slate-700">Rp {{ number_format($totalFobIdr, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- SPPB & NPE Card -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Persetujuan Terbit</span>
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div class="text-2xl font-black text-emerald-600">{{ $totalSppb + $totalNpe }} <span class="text-xs font-semibold text-slate-400">Persetujuan</span></div>
            <div class="text-xs font-medium text-slate-500 mt-2 flex items-center space-x-2">
                <span>SPPB: <strong>{{ $totalSppb }}</strong></span>
                <span>&bull;</span>
                <span>NPE: <strong>{{ $totalNpe }}</strong></span>
            </div>
        </div>

        <!-- Billing MPN G3 Card -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Billing MPN G3</span>
                <span class="p-2 rounded-xl bg-amber-50 text-amber-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </span>
            </div>
            <div class="text-2xl font-black text-amber-600">{{ $totalBillingAktif }} <span class="text-xs font-semibold text-slate-400">Aktif</span></div>
            <div class="text-xs font-medium text-slate-500 mt-2">
                Menunggu Pembayaran Bea/Pajak
            </div>
        </div>
    </div>

    <!-- Main Grid: Documents Table & Recent Logs -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent Documents Table (2 cols) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-slate-900">Pengajuan Dokumen Kepabeanan Terkini</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Daftar dokumen BC 2.0 dan BC 3.0 yang baru diproses</p>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('bc20.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700">Lihat Semua BC 2.0 &rarr;</a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/80 text-slate-500 font-semibold border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">Nomor Aju</th>
                            <th class="py-3 px-4">Jenis</th>
                            <th class="py-3 px-4">Mitra Luar Negeri</th>
                            <th class="py-3 px-4">Nilai Pabean</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentDocuments as $doc)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-3 px-4 font-mono font-semibold text-slate-800">
                                    <a href="{{ $doc->jenis_dokumen === '20' ? route('bc20.show', $doc->id) : route('bc30.show', $doc->id) }}" class="text-brand-600 hover:underline">
                                        {{ $doc->nomor_aju }}
                                    </a>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold {{ $doc->jenis_dokumen === '20' ? 'bg-blue-100 text-blue-800' : 'bg-indigo-100 text-indigo-800' }}">
                                        {{ $doc->jenis_dokumen === '20' ? 'BC 2.0' : 'BC 3.0' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-medium text-slate-700 truncate max-w-[150px]">
                                    {{ $doc->nama_mitra_luar_negeri ?? '-' }}
                                    <span class="text-slate-400 font-normal">({{ $doc->negara_mitra_luar_negeri ?? 'LN' }})</span>
                                </td>
                                <td class="py-3 px-4 font-semibold text-slate-800">
                                    {{ $doc->kode_valuta }} {{ number_format($doc->total_nilai, 2) }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $doc->status_badge_class }}">
                                        {{ $doc->status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ $doc->jenis_dokumen === '20' ? route('bc20.show', $doc->id) : route('bc30.show', $doc->id) }}" class="inline-flex items-center text-xs font-semibold text-brand-600 hover:text-brand-800">
                                        Detail &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">
                                    Belum ada data dokumen pabean. Silakan buat pengajuan baru melalui tombol di atas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent API Logs (1 col) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-slate-900">Audit Trail API CEISA 4.0</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Aktivitas komunikasi H2H dengan server DJBC</p>
                </div>
                <a href="{{ route('config.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700">Semua Log</a>
            </div>

            <div class="p-4 flex-1 space-y-3 overflow-y-auto max-h-[380px]">
                @forelse($recentLogs as $log)
                    <div class="p-3 rounded-xl border border-slate-100 bg-slate-50/50 text-xs">
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center space-x-1.5">
                                <span class="px-1.5 py-0.5 rounded font-mono font-bold text-[10px] {{ $log->method === 'POST' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ $log->method }}
                                </span>
                                <span class="font-mono text-slate-600 truncate max-w-[140px]">{{ $log->endpoint }}</span>
                            </div>
                            <span class="px-2 py-0.5 rounded-full font-bold text-[10px] {{ $log->status_code === 200 ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-rose-50 text-rose-600 border border-rose-200' }}">
                                {{ $log->status_code }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-slate-400 text-[11px]">
                            <span>{{ $log->duration_ms ?? 0 }} ms</span>
                            <span>{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-400 text-xs">
                        Belum ada aktivitas panggilan API yang tercatat.
                    </div>
                @endforelse
            </div>

            <div class="p-4 bg-slate-50 border-t border-slate-100 text-center">
                <form action="{{ route('config.test-auth') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full py-2 px-3 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold text-xs transition-colors flex items-center justify-center space-x-2">
                        <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
