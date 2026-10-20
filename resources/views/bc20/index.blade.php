@extends('layouts.app')

@section('title', 'Dokumen BC 2.0 (Impor / PIB) — CEISA 4.0')

@section('content')
<div class="max-w-[1360px] mx-auto px-4 sm:px-6 space-y-6 pt-4 pb-28 lg:pb-12" x-data="coocaTableSort(0, false)">
    <!-- 1. ZONE 1: BREADCRUMB, HEADER & EXECUTIVE KPI SNAPSHOT -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-5 sm:p-6 border border-black/[0.06] dark:border-white/[0.08] shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <nav class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 font-medium mb-1.5" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors flex items-center gap-1">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i>
                    <span>Dashboard</span>
                </a>
                <i data-lucide="chevron-right" class="w-3 h-3 text-slate-300 dark:text-slate-600"></i>
                <span class="text-slate-800 dark:text-white font-semibold">Pemberitahuan Impor Barang (BC 2.0)</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Dokumen Pabean Impor (BC 2.0 PIB)</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Manajemen pengajuan PIB CEISA 4.0 DJBC, status penjaluran (Merah/Kuning/Hijau), dan perizinan Lartas impor.</p>
        </div>
        <div class="flex items-center gap-2">
            @can('document.create')
            <a href="{{ route('bc20.create') }}" 
               class="btn-apple-filled px-4 py-2.5 text-xs inline-flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Buat Pengajuan BC 2.0 Baru</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Apple HIG Bento Grid KPI Summary -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- KPI 1: Total PIB -->
        <div class="p-5 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs flex items-center gap-3.5 hover:border-[#007AFF]/30 transition-all">
            <div class="w-10 h-10 rounded-xl bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                <i data-lucide="arrow-down-left" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total PIB Impor</div>
                <div class="text-xl font-bold text-slate-900 dark:text-white font-mono tabular-nums">{{ $stats['total'] ?? $documents->total() }}</div>
                <div class="text-[10px] text-slate-400 dark:text-slate-500">Seluruh dokumen terdaftar</div>
            </div>
        </div>

        <!-- KPI 2: Draft Pengisian -->
        <div class="p-5 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs flex items-center gap-3.5 hover:border-amber-500/30 transition-all">
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                <i data-lucide="file-edit" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Draft Disiapkan</div>
                <div class="text-xl font-bold text-amber-600 dark:text-amber-400 font-mono tabular-nums">{{ $stats['draft'] ?? 0 }}</div>
                <div class="text-[10px] text-slate-400 dark:text-slate-500">Belum disubmit ke CEISA</div>
            </div>
        </div>

        <!-- KPI 3: Menunggu Billing MPN -->
        <div class="p-5 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs flex items-center gap-3.5 hover:border-purple-500/30 transition-all">
            <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                <i data-lucide="receipt" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Billing Terbit</div>
                <div class="text-xl font-bold text-purple-600 dark:text-purple-400 font-mono tabular-nums">{{ $stats['billing'] ?? 0 }}</div>
                <div class="text-[10px] text-slate-400 dark:text-slate-500">Menunggu pelunasan MPN G3</div>
            </div>
        </div>

        <!-- KPI 4: SPPB Terbit -->
        <div class="p-5 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs flex items-center gap-3.5 hover:border-emerald-500/30 transition-all">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <i data-lucide="check-circle-2" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">SPPB Terbit</div>
                <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400 font-mono tabular-nums">{{ $stats['sppb'] ?? 0 }}</div>
                <div class="text-[10px] text-slate-400 dark:text-slate-500">Siap pengeluaran barang</div>
            </div>
        </div>
    </div>

    <!-- 2. ZONE 2: FILTER & SEARCH TOOLBAR -->
    <div class="bg-white dark:bg-[#1C1C1E] p-4 rounded-2xl border border-black/[0.06] dark:border-white/[0.08] shadow-xs">
        <form action="{{ route('bc20.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center text-xs">
            <div class="sm:col-span-5 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari Nomor Aju (26 digit), Pengirim, No Daftar..." 
                       class="w-full pl-9 pr-3.5 py-2 text-xs rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
            </div>

            <div class="sm:col-span-4">
                <select name="status" class="w-full px-3 py-2 text-xs rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-slate-900 dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    <option value="">-- Semua Status Dokumen --</option>
                    <option value="DRAFT" {{ request('status') === 'DRAFT' ? 'selected' : '' }}>DRAFT (Pengisian Awal)</option>
                    <option value="SUBMITTED" {{ request('status') === 'SUBMITTED' ? 'selected' : '' }}>SUBMITTED (Terkirim ke CEISA)</option>
                    <option value="PROSES" {{ request('status') === 'PROSES' ? 'selected' : '' }}>PROSES (Validasi Sistem Pabean)</option>
                    <option value="BILLING" {{ request('status') === 'BILLING' ? 'selected' : '' }}>BILLING (Menunggu Pelunasan)</option>
                    <option value="SPPB" {{ request('status') === 'SPPB' ? 'selected' : '' }}>SPPB (Izin Pengeluaran Terbit)</option>
                    <option value="REJECT" {{ request('status') === 'REJECT' ? 'selected' : '' }}>REJECT (Ditolak Sistem)</option>
                </select>
            </div>

            <div class="sm:col-span-3 flex items-center gap-2">
                <button type="submit" class="btn-apple-filled flex-1 py-2 text-xs">
                    Filter
                </button>
                <a href="{{ route('bc20.index') }}" class="btn-apple-gray py-2 px-3.5 text-xs">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- 3. ZONE 3: DATA LEDGER TABLE -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl border border-black/[0.06] dark:border-white/[0.08] shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-black/[0.02] dark:bg-white/[0.02] text-slate-500 dark:text-slate-400 font-semibold uppercase text-[10px] tracking-wider border-b border-black/[0.06] dark:border-white/[0.06]">
                    <tr>
                        <th class="py-3.5 px-4 cursor-pointer hover:text-slate-800 dark:hover:text-white transition-colors" @click="sortBy(0, 'string', $event)">
                            <div class="flex items-center gap-1">
                                <span>Nomor Aju & Tanggal</span>
                                <i data-lucide="chevrons-up-down" class="w-3 h-3 opacity-40"></i>
                            </div>
                        </th>
                        <th class="py-3.5 px-4">Kantor Pabean</th>
                        <th class="py-3.5 px-4">Supplier / Pengirim</th>
                        <th class="py-3.5 px-4 text-right cursor-pointer hover:text-slate-800 dark:hover:text-white transition-colors" @click="sortBy(3, 'number', $event)">
                            <div class="flex items-center justify-end gap-1">
                                <span>Valuasi CIF (Pabean)</span>
                                <i data-lucide="chevrons-up-down" class="w-3 h-3 opacity-40"></i>
                            </div>
                        </th>
                        <th class="py-3.5 px-4 text-center">Status CEISA</th>
                        <th class="py-3.5 px-4">Billing MPN G3</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.04]">
                    @forelse($documents as $doc)
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                            <td class="py-3.5 px-4 font-mono" data-sort-value="{{ $doc->nomor_aju }}">
                                <a href="{{ route('bc20.show', $doc->id) }}" class="font-bold text-[#007AFF] hover:underline">
                                    {{ $doc->nomor_aju }}
                                </a>
                                @if($doc->nomor_daftar)
                                    <div class="text-[10px] text-slate-400 dark:text-slate-500 font-mono mt-0.5 tabular-nums">No Pen: {{ $doc->nomor_daftar }} ({{ $doc->tanggal_daftar?->format('d/m/Y') }})</div>
                                @else
                                    <div class="text-[10px] text-slate-400 dark:text-slate-500 font-sans mt-0.5 tabular-nums">{{ $doc->created_at ? $doc->created_at->format('d M Y, H:i') : '-' }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 dark:text-slate-300">
                                <span class="font-semibold text-slate-800 dark:text-white font-mono">{{ $doc->kode_kantor }}</span>
                                <div class="text-[10px] text-slate-400 dark:text-slate-500 truncate max-w-[140px]">{{ $doc->nama_kantor }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-slate-800 dark:text-white truncate max-w-[180px]">{{ $doc->nama_mitra_luar_negeri }}</div>
                                <div class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">Negara: {{ $doc->negara_mitra_luar_negeri ?? 'LN' }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-right" data-sort-value="{{ $doc->total_nilai }}">
                                <div class="font-bold text-slate-900 dark:text-white font-mono tabular-nums">{{ $doc->kode_valuta }} {{ number_format($doc->total_nilai, 2) }}</div>
                                <div class="text-[10px] text-slate-400 dark:text-slate-500 font-mono tabular-nums">Rp {{ number_format($doc->total_nilai_idr, 0, ',', '.') }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold {{ $doc->status_badge_class }}">
                                    {{ $doc->status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono">
                                @if($doc->kode_billing)
                                    <span class="text-purple-600 dark:text-purple-400 font-bold tabular-nums">{{ $doc->kode_billing }}</span>
                                    <div class="text-[10px] text-slate-400 dark:text-slate-500 tabular-nums">Rp {{ number_format($doc->total_billing, 0, ',', '.') }}</div>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500 text-[11px]">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ route('bc20.show', $doc->id) }}" 
                                   class="btn-apple-gray text-[11px] px-3 py-1.5 inline-flex items-center gap-1">
                                    <span>Kelola</span>
                                    <i data-lucide="chevron-right" class="w-3 h-3"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400 dark:text-slate-500">
                                <div class="w-10 h-10 rounded-2xl bg-black/5 dark:bg-white/10 text-slate-400 mx-auto flex items-center justify-center text-lg mb-2">
                                    <i data-lucide="file-text" class="w-5 h-5"></i>
                                </div>
                                <div class="font-semibold text-slate-700 dark:text-slate-300 text-xs">Belum ada pengajuan dokumen impor BC 2.0</div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Klik tombol di atas untuk membuat dokumen PIB baru.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($documents->hasPages())
            <div class="p-4 border-t border-black/[0.06] dark:border-white/[0.06] bg-black/[0.02] dark:bg-white/[0.02]">
                {{ $documents->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
