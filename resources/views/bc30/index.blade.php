@extends('layouts.app')

@section('title', 'Dokumen BC 3.0 (Ekspor / PEB) — CEISA 4.0')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 pt-6 pb-12" x-data="coocaTableSort(0, false)">
    <!-- 1. ZONE 1: BREADCRUMB, HEADER & EXECUTIVE KPI SNAPSHOT -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <nav class="flex items-center gap-1.5 text-xs text-slate-500 font-medium mb-1.5" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 transition-colors">Dashboard</a>
                <span class="text-slate-300">/</span>
                <span class="text-slate-800 font-semibold">Pemberitahuan Ekspor Barang (BC 3.0)</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Dokumen Pabean Ekspor (BC 3.0 PEB)</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manajemen pengajuan PEB CEISA 4.0 DJBC, penjaluran pabean ekspor, dan penerbitan Nota Pelayanan Ekspor (NPE).</p>
        </div>
        <div class="flex items-center gap-2">
            @can('document.create')
            <a href="{{ route('bc30.create') }}" 
               class="min-h-[42px] px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold shadow-xs transition-all active:scale-95 inline-flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Buat Pengajuan BC 3.0 Baru</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Apple HIG Bento Grid KPI Summary -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
        <!-- KPI 1: Total PEB -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5 hover:border-slate-300 transition-all">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" /></svg>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total PEB Ekspor</div>
                <div class="text-xl font-extrabold text-slate-900 font-mono">{{ $stats['total'] ?? $documents->total() }}</div>
                <div class="text-[10px] text-slate-400">Seluruh dokumen ekspor</div>
            </div>
        </div>

        <!-- KPI 2: Draft Disiapkan -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5 hover:border-slate-300 transition-all">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Draft Disiapkan</div>
                <div class="text-xl font-extrabold text-amber-700 font-mono">{{ $stats['draft'] ?? 0 }}</div>
                <div class="text-[10px] text-slate-400">Belum disubmit ke CEISA</div>
            </div>
        </div>

        <!-- KPI 3: Proses Validasi -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5 hover:border-slate-300 transition-all">
            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Proses CEISA</div>
                <div class="text-xl font-extrabold text-sky-700 font-mono">{{ $stats['proses'] ?? 0 }}</div>
                <div class="text-[10px] text-slate-400">Validasi & penjaluran</div>
            </div>
        </div>

        <!-- KPI 4: NPE Terbit -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3.5 hover:border-slate-300 transition-all">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">NPE Terbit</div>
                <div class="text-xl font-extrabold text-emerald-700 font-mono">{{ $stats['npe'] ?? 0 }}</div>
                <div class="text-[10px] text-slate-400">Izin muat ekspor selesai</div>
            </div>
        </div>
    </div>

    <!-- 2. ZONE 2: FILTER & SEARCH TOOLBAR -->
    <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-slate-200/80 shadow-xs mb-6">
        <form action="{{ route('bc30.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center text-xs">
            <div class="sm:col-span-5 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari Nomor Aju (26 digit), Pembeli LN, No Daftar..." 
                       class="w-full pl-9 pr-3.5 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
            </div>

            <div class="sm:col-span-4">
                <select name="status" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white font-medium text-slate-700">
                    <option value="">-- Semua Status Dokumen --</option>
                    <option value="DRAFT" {{ request('status') === 'DRAFT' ? 'selected' : '' }}>DRAFT (Pengisian Awal)</option>
                    <option value="SUBMITTED" {{ request('status') === 'SUBMITTED' ? 'selected' : '' }}>SUBMITTED (Terkirim ke CEISA)</option>
                    <option value="PROSES" {{ request('status') === 'PROSES' ? 'selected' : '' }}>PROSES (Validasi Sistem Pabean)</option>
                    <option value="NPE" {{ request('status') === 'NPE' ? 'selected' : '' }}>NPE (Nota Pelayanan Ekspor Terbit)</option>
                    <option value="REJECT" {{ request('status') === 'REJECT' ? 'selected' : '' }}>REJECT (Ditolak Sistem)</option>
                </select>
            </div>

            <div class="sm:col-span-3 flex items-center gap-2">
                <button type="submit" class="flex-1 min-h-[38px] px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-xl transition-all shadow-xs cursor-pointer active:scale-95">
                    Filter
                </button>
                <a href="{{ route('bc30.index') }}" class="min-h-[38px] px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition-all flex items-center justify-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- 3. ZONE 3: DATA LEDGER TABLE -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/90 text-slate-500 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-4 cursor-pointer hover:text-slate-800 transition-colors" @click="sortBy(0, 'string', $event)">
                            <div class="flex items-center gap-1">
                                <span>Nomor Aju & Tanggal</span>
                                <svg class="w-3 h-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                            </div>
                        </th>
                        <th class="py-3.5 px-4">Kantor Pabean Muat</th>
                        <th class="py-3.5 px-4">Pembeli Luar Negeri</th>
                        <th class="py-3.5 px-4 text-right cursor-pointer hover:text-slate-800 transition-colors" @click="sortBy(3, 'number', $event)">
                            <div class="flex items-center justify-end gap-1">
                                <span>Devisa Ekspor (FOB)</span>
                                <svg class="w-3 h-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                            </div>
                        </th>
                        <th class="py-3.5 px-4 text-center">Status CEISA</th>
                        <th class="py-3.5 px-4">Nomor NPE</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($documents as $doc)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 font-mono" data-sort-value="{{ $doc->nomor_aju }}">
                                <a href="{{ route('bc30.show', $doc->id) }}" class="font-bold text-indigo-600 hover:text-indigo-800 hover:underline">
                                    {{ $doc->nomor_aju }}
                                </a>
                                @if($doc->nomor_daftar)
                                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">No Pen: {{ $doc->nomor_daftar }} ({{ $doc->tanggal_daftar?->format('d/m/Y') }})</div>
                                @else
                                    <div class="text-[10px] text-slate-400 font-sans mt-0.5">{{ $doc->created_at ? $doc->created_at->format('d M Y, H:i') : '-' }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                <span class="font-bold text-slate-800 font-mono">{{ $doc->kode_kantor }}</span>
                                <div class="text-[10px] text-slate-400 truncate max-w-[140px]">{{ $doc->nama_kantor }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-slate-800 truncate max-w-[180px]">{{ $doc->nama_mitra_luar_negeri }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">Tujuan: {{ $doc->negara_mitra_luar_negeri ?? 'LN' }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-right" data-sort-value="{{ $doc->total_nilai }}">
                                <div class="font-bold text-slate-900 font-mono">{{ $doc->kode_valuta }} {{ number_format($doc->total_nilai, 2) }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">Rp {{ number_format($doc->total_nilai_idr, 0, ',', '.') }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $doc->status_badge_class }}">
                                    {{ $doc->status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono">
                                @if($doc->nomor_sppb_npe)
                                    <span class="text-emerald-700 font-bold">{{ $doc->nomor_sppb_npe }}</span>
                                    <div class="text-[10px] text-slate-400">{{ $doc->tanggal_sppb_npe?->format('d/m/Y') }}</div>
                                @else
                                    <span class="text-slate-400 text-[11px]">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('bc30.show', $doc->id) }}" 
                                   class="inline-flex items-center px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                                    Kelola &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="w-10 h-10 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center text-lg mb-2">
                                    📄
                                </div>
                                <div class="font-bold text-slate-700 text-xs">Belum ada pengajuan dokumen ekspor BC 3.0</div>
                                <p class="text-[11px] text-slate-500 mt-0.5">Klik tombol di atas untuk membuat dokumen PEB baru.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($documents->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $documents->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
