@extends('layouts.app')

@section('title', 'Dokumen BC 3.0 (Ekspor / PEB) — CEISA 4.0')

@section('content')
<div class="max-w-[1360px] mx-auto px-4 sm:px-6 space-y-6 pt-4 pb-28 lg:pb-12" 
     x-data="{
        ...coocaTableSort(0, false),
        aiModalOpen: false,
        aiQuery: '',
        aiModel: 'cx/gpt-6-astra',
        aiLoading: false,
        aiResult: null,
        async runAiClassify() {
            if (!this.aiQuery.trim()) {
                alert('Silakan masukkan nama produk atau spesifikasi komoditas terlebih dahulu.');
                return;
            }
            this.aiLoading = true;
            this.aiResult = null;
            try {
                const res = await fetch('{{ route('ai.classify-hs') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        text: this.aiQuery,
                        direction: 'export',
                        model: this.aiModel
                    })
                });
                const data = await res.json();
                if (data.status === 'success' && data.data) {
                    this.aiResult = data.data;
                } else {
                    alert(data.message || 'Gagal mendeteksi pos tarif komoditas.');
                }
            } catch (e) {
                alert('Terjadi kesalahan koneksi AI: ' + e.message);
            } finally {
                this.aiLoading = false;
            }
        }
     }">

    <!-- 1. ZONE 1: BREADCRUMB, HEADER & EXECUTIVE KPI SNAPSHOT -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-5 sm:p-6 border border-black/[0.06] dark:border-white/[0.08] shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <nav class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 font-medium mb-1.5" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors flex items-center gap-1">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i>
                    <span>Dashboard</span>
                </a>
                <i data-lucide="chevron-right" class="w-3 h-3 text-slate-300 dark:text-slate-600"></i>
                <span class="text-slate-800 dark:text-white font-semibold">Pemberitahuan Ekspor Barang (BC 3.0)</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                <span>Dokumen Pabean Ekspor (BC 3.0 PEB)</span>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-400 border border-indigo-200/80">
                    CEISA 4.0 DJBC
                </span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Manajemen pengajuan PEB CEISA 4.0 DJBC, penjaluran pabean ekspor, dan audit kepatuhan perizinan Lartas ekspor.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @can('setting.manage')
            <form action="{{ route('config.sync-all') }}" method="POST">
                @csrf
                <button type="submit"
                        class="px-3.5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs inline-flex items-center gap-2 shadow-xs transition-all active:scale-[0.98] cursor-pointer"
                        title="Tarik data terbaru dari CEISA 4.0 (tanpa mengirim dokumen ke CEISA)">
                    <i data-lucide="download-cloud" class="w-4 h-4"></i>
                    <span>Tarik Data CEISA</span>
                </button>
            </form>
            @endcan

            <!-- Tombol Trigger AI HS & Lartas Detector -->
            <button type="button" 
                    @click="aiModalOpen = true"
                    class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 via-sky-600 to-indigo-600 hover:from-indigo-700 hover:to-sky-700 text-white font-bold text-xs inline-flex items-center gap-2 shadow-xs transition-all active:scale-[0.98] cursor-pointer">
                <span class="text-sm">✨</span>
                <span>Deteksi HS Code & Lartas AI</span>
            </button>

            @can('document.create')
            <a href="{{ route('bc30.create') }}" 
               class="btn-apple-filled px-4 py-2.5 text-xs inline-flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Buat Pengajuan BC 3.0 Baru</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Apple HIG Bento Grid KPI Summary (Termasuk Metrik Lartas) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
        <!-- KPI 1: Total PEB -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs flex items-center gap-3.5 hover:border-[#007AFF]/30 transition-all">
            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                <i data-lucide="arrow-up-right" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total PEB Ekspor</div>
                <div class="text-xl font-bold text-slate-900 dark:text-white font-mono tabular-nums">{{ $stats['total'] ?? $documents->total() }}</div>
                <div class="text-[10px] text-slate-400 dark:text-slate-500">Seluruh dokumen</div>
            </div>
        </div>

        <!-- KPI 2: Draft Disiapkan -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs flex items-center gap-3.5 hover:border-amber-500/30 transition-all">
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                <i data-lucide="file-edit" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Draft Disiapkan</div>
                <div class="text-xl font-bold text-amber-600 dark:text-amber-400 font-mono tabular-nums">{{ $stats['draft'] ?? 0 }}</div>
                <div class="text-[10px] text-slate-400 dark:text-slate-500">Belum disubmit</div>
            </div>
        </div>

        <!-- KPI 3: Proses Validasi -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs flex items-center gap-3.5 hover:border-[#007AFF]/30 transition-all">
            <div class="w-10 h-10 rounded-xl bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                <i data-lucide="zap" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Proses CEISA</div>
                <div class="text-xl font-bold text-[#007AFF] font-mono tabular-nums">{{ $stats['proses'] ?? 0 }}</div>
                <div class="text-[10px] text-slate-400 dark:text-slate-500">Validasi sistem</div>
            </div>
        </div>

        <!-- KPI 4: NPE Terbit -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs flex items-center gap-3.5 hover:border-emerald-500/30 transition-all">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <i data-lucide="check-circle-2" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">NPE Terbit</div>
                <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400 font-mono tabular-nums">{{ $stats['npe'] ?? 0 }}</div>
                <div class="text-[10px] text-slate-400 dark:text-slate-500">Izin muat selesai</div>
            </div>
        </div>

        <!-- KPI 5: INFORMASI PRODUK KENA LARTAS (Permintaan User) -->
        <a href="{{ route('bc30.index', ['lartas' => 'yes']) }}" 
           class="p-4 sm:p-5 rounded-2xl bg-amber-50/60 dark:bg-amber-950/20 border border-amber-300 dark:border-amber-800/60 shadow-xs flex items-center gap-3.5 hover:border-amber-500 transition-all group col-span-2 sm:col-span-1">
            <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                <i data-lucide="shield-alert" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[10px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-300 flex items-center gap-1">
                    <span>Produk Lartas</span>
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                </div>
                <div class="text-xl font-black text-amber-900 dark:text-amber-200 font-mono tabular-nums">
                    {{ $stats['lartas_products'] ?? 0 }} <span class="text-xs font-semibold">Produk</span>
                </div>
                <div class="text-[10px] text-amber-700/80 dark:text-amber-400 font-medium truncate">
                    {{ $stats['lartas_docs'] ?? 0 }} Dok. perlu izin K/L &rarr;
                </div>
            </div>
        </a>
    </div>

    <!-- 2. ZONE 2: FILTER & SEARCH TOOLBAR (Dengan Filter Khusus Lartas) -->
    <div class="bg-white dark:bg-[#1C1C1E] p-4 rounded-2xl border border-black/[0.06] dark:border-white/[0.08] shadow-xs">
        <form action="{{ route('bc30.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center text-xs">
            <div class="sm:col-span-4 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari Nomor Aju, Pembeli LN, No Pen..." 
                       class="w-full pl-9 pr-3.5 py-2 text-xs rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
            </div>

            <div class="sm:col-span-3">
                <select name="status" class="w-full px-3 py-2 text-xs rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-slate-900 dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    <option value="">-- Semua Status Dokumen --</option>
                    <option value="DRAFT" {{ request('status') === 'DRAFT' ? 'selected' : '' }}>DRAFT (Pengisian Awal)</option>
                    <option value="SUBMITTED" {{ request('status') === 'SUBMITTED' ? 'selected' : '' }}>SUBMITTED (Terkirim ke CEISA)</option>
                    <option value="PROSES" {{ request('status') === 'PROSES' ? 'selected' : '' }}>PROSES (Validasi Sistem)</option>
                    <option value="NPE" {{ request('status') === 'NPE' ? 'selected' : '' }}>NPE (Nota Pelayanan Ekspor)</option>
                    <option value="REJECT" {{ request('status') === 'REJECT' ? 'selected' : '' }}>REJECT (Ditolak Sistem)</option>
                </select>
            </div>

            <!-- Filter Kategori Lartas (Permintaan User) -->
            <div class="sm:col-span-3">
                <select name="lartas" class="w-full px-3 py-2 text-xs rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-slate-900 dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-amber-500">
                    <option value="">-- Semua Kategori Lartas --</option>
                    <option value="yes" {{ request('lartas') === 'yes' ? 'selected' : '' }}>⚠️ Wajib Lartas (Perlu Izin K/L)</option>
                    <option value="no" {{ request('lartas') === 'no' ? 'selected' : '' }}>✓ Bebas Lartas (Non-Lartas)</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="btn-apple-filled flex-1 py-2 text-xs">
                    Filter
                </button>
                <a href="{{ route('bc30.index') }}" class="btn-apple-gray py-2 px-3 text-xs">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Quick Filter Chips: Lartas vs Non-Lartas -->
    <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
        <div class="flex items-center gap-1.5 text-xs">
            <span class="text-[11px] font-semibold text-slate-400 dark:text-slate-500 mr-1">Filter Cepat:</span>
            <a href="{{ route('bc30.index', array_merge(request()->except('lartas'), ['lartas' => ''])) }}" 
               class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all {{ !request('lartas') ? 'bg-[#007AFF] text-white shadow-xs' : 'bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-400 hover:bg-slate-200' }}">
                Semua Dokumen ({{ $stats['total'] ?? 0 }})
            </a>
            <a href="{{ route('bc30.index', array_merge(request()->except('lartas'), ['lartas' => 'yes'])) }}" 
               class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all inline-flex items-center gap-1.5 {{ request('lartas') === 'yes' ? 'bg-amber-500 text-white shadow-xs' : 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200/80 hover:bg-amber-100' }}">
                <span>⚠️ Wajib Lartas</span>
                <span class="px-1.5 py-0.2 rounded-md bg-white/30 text-[10px] font-mono font-bold">{{ $stats['lartas_products'] ?? 0 }} Produk</span>
            </a>
            <a href="{{ route('bc30.index', array_merge(request()->except('lartas'), ['lartas' => 'no'])) }}" 
               class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all inline-flex items-center gap-1.5 {{ request('lartas') === 'no' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/80 hover:bg-emerald-100' }}">
                <span>✓ Bebas Lartas</span>
                <span class="px-1.5 py-0.2 rounded-md bg-white/30 text-[10px] font-mono font-bold">{{ $stats['non_lartas_docs'] ?? 0 }} Dok</span>
            </a>
        </div>

        @if(request('lartas') || request('status') || request('search'))
            <div class="text-[11px] text-slate-500 flex items-center gap-2">
                <span>Filter aktif:</span>
                @if(request('lartas') === 'yes')
                    <span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 font-bold text-[10px]">Hanya Wajib Lartas</span>
                @elseif(request('lartas') === 'no')
                    <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 font-bold text-[10px]">Hanya Bebas Lartas</span>
                @endif
                <a href="{{ route('bc30.index') }}" class="text-[#007AFF] hover:underline font-bold text-[10px]">Hapus Filter &times;</a>
            </div>
        @endif
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
                        <th class="py-3.5 px-4">Kantor Pabean Muat</th>
                        <th class="py-3.5 px-4">Pembeli Luar Negeri</th>
                        <th class="py-3.5 px-4">Produk & Status Lartas</th>
                        <th class="py-3.5 px-4 text-right cursor-pointer hover:text-slate-800 dark:hover:text-white transition-colors" @click="sortBy(4, 'number', $event)">
                            <div class="flex items-center justify-end gap-1">
                                <span>Devisa Ekspor (FOB)</span>
                                <i data-lucide="chevrons-up-down" class="w-3 h-3 opacity-40"></i>
                            </div>
                        </th>
                        <th class="py-3.5 px-4 text-center">Status CEISA</th>
                        <th class="py-3.5 px-4">Nomor NPE</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.04]">
                    @forelse($documents as $doc)
                        @php
                            $lInfo = $doc->lartas_info;
                        @endphp
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                            <td class="py-3.5 px-4 font-mono" data-sort-value="{{ $doc->nomor_aju }}">
                                <a href="{{ route('bc30.show', $doc->id) }}" class="font-bold text-[#007AFF] hover:underline">
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
                                <div class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">Tujuan: {{ $doc->negara_mitra_luar_negeri ?? 'LN' }}</div>
                            </td>
                            <!-- Kolom Informasi Produk & Status Lartas (Permintaan User) -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-1.5 mb-1">
                                    <span class="font-bold text-slate-900 dark:text-white font-mono text-[11px]">{{ $lInfo['total_products'] }} Produk</span>
                                </div>
                                @if($lInfo['has_lartas'])
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200/80">
                                        <span>⚠️</span>
                                        <span>{{ $lInfo['lartas_count'] }} Kena Lartas</span>
                                    </span>
                                    @if(!empty($lInfo['primary_lartas']))
                                        <div class="text-[9px] text-slate-500 dark:text-slate-400 mt-0.5 truncate max-w-[160px]" title="{{ $lInfo['primary_lartas'] }}">
                                            {{ $lInfo['primary_lartas'] }}
                                        </div>
                                    @endif
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80">
                                        <span>✓</span>
                                        <span>Bebas Lartas</span>
                                    </span>
                                @endif
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
                                @if($doc->nomor_sppb_npe)
                                    <span class="text-emerald-600 dark:text-emerald-400 font-bold tabular-nums">{{ $doc->nomor_sppb_npe }}</span>
                                    <div class="text-[10px] text-slate-400 dark:text-slate-500 tabular-nums">{{ $doc->tanggal_sppb_npe?->format('d/m/Y') }}</div>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500 text-[11px]">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('bc30.show', $doc->id) }}" 
                                       class="btn-apple-gray text-[11px] px-3 py-1.5 inline-flex items-center gap-1 font-semibold !text-indigo-600 dark:!text-indigo-400 !border-indigo-500/30 hover:!bg-indigo-500/10"
                                       title="Buka pelacakan alur 11 Pos ekspor dan detail pabean">
                                        <i data-lucide="milestone" class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400"></i>
                                        <span>Tracking 11 Pos</span>
                                        <i data-lucide="chevron-right" class="w-3 h-3"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400 dark:text-slate-500">
                                <div class="w-10 h-10 rounded-2xl bg-black/5 dark:bg-white/10 text-slate-400 mx-auto flex items-center justify-center text-lg mb-2">
                                    <i data-lucide="file-text" class="w-5 h-5"></i>
                                </div>
                                <div class="font-semibold text-slate-700 dark:text-slate-300 text-xs">Tidak ada data dokumen ekspor yang cocok dengan filter.</div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Coba ubah kata kunci pencarian atau reset filter.</p>
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

    <!-- 4. MODAL DETEKSI & PENCOCOKAN HS CODE DENGAN PRODUK MENGGUNAKAN AI -->
    <div x-show="aiModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="aiModalOpen = false" 
             class="bg-white dark:bg-[#1C1C1E] rounded-3xl border border-black/10 dark:border-white/10 shadow-2xl max-w-2xl w-full p-6 text-xs space-y-4">
            
            <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.06]">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-500 to-sky-500 text-white flex items-center justify-center font-bold shadow-xs">
                        ✨
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">AI HS Code & Lartas Product Detector (BTKI 2022)</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Deteksi 8-digit pos tarif ekspor, tarif Bea Keluar (BK), dan persyaratan dokumen perizinan K/L.</p>
                    </div>
                </div>
                <button type="button" @click="aiModalOpen = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-white">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Form Input Komoditas Produk -->
            <div class="space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <label class="block font-semibold text-slate-700 dark:text-slate-300">
                        Uraian Produk / Komoditas Ekspor:
                    </label>
                    <div class="flex items-center gap-1.5">
                        <span class="text-[11px] text-slate-400">Model AI:</span>
                        <select x-model="aiModel" class="px-2.5 py-1 rounded-lg border border-black/10 dark:border-white/10 bg-slate-50 dark:bg-white/5 font-mono text-[11px] font-semibold text-slate-800 dark:text-slate-200 focus:outline-none">
                            <option value="cx/gpt-6-astra">cx/gpt-6-astra (Rekomendasi Utama)</option>
                            <option value="cx/gpt-6-astra-xhigh">cx/gpt-6-astra-xhigh (Ultra Precision)</option>
                            <option value="cx/gpt-5.6-terra">cx/gpt-5.6-terra (Customs Specialized)</option>
                            <option value="cx/gpt-5.6-sol">cx/gpt-5.6-sol</option>
                            <option value="ag/gemini-3.8-flash-high">ag/gemini-3.8-flash-high</option>
                        </select>
                    </div>
                </div>

                <textarea x-model="aiQuery" 
                          rows="3" 
                          placeholder="Ketik nama produk, spesifikasi bahan baku, merek, atau deskripsi barang ekspor (contoh: Kopi Arabika Gayo Roasting Kemasan Retail 250gr, Sarang Burung Walet, Minyak Sawit CPO, Sepatu Kulit...)" 
                          class="w-full px-3.5 py-2.5 rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-[#007AFF]"></textarea>

                <div class="flex justify-between items-center pt-1">
                    <div class="flex items-center gap-1 text-[11px] text-slate-400">
                        <span>Contoh cepat:</span>
                        <button type="button" @click="aiQuery = 'Kopi Arabika Roasting Kemasan Retail 250 Gram Asli Gayo'" class="text-[#007AFF] hover:underline font-semibold">Kopi Arabika</button>
                        <span>&bull;</span>
                        <button type="button" @click="aiQuery = 'Sarang Burung Walet Bersih Siap Konsumsi Kualitas Ekspor'" class="text-[#007AFF] hover:underline font-semibold">Sarang Walet</button>
                        <span>&bull;</span>
                        <button type="button" @click="aiQuery = 'Papan Kayu Olahan Merbau Moulding S4S Decking'" class="text-[#007AFF] hover:underline font-semibold">Produk Kayu</button>
                    </div>

                    <button type="button" 
                            @click="runAiClassify()" 
                            :disabled="aiLoading" 
                            class="px-4 py-2 rounded-xl bg-[#007AFF] hover:bg-[#0062CC] text-white font-bold text-xs inline-flex items-center gap-1.5 shadow-xs transition-all active:scale-[0.98] disabled:opacity-50 cursor-pointer">
                        <template x-if="aiLoading">
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="animate-spin h-3.5 w-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span>Menganalisis BTKI...</span>
                            </span>
                        </template>
                        <template x-if="!aiLoading">
                            <span class="inline-flex items-center gap-1.5">
                                <span>Pindai & Cocokkan Pos Tarif AI</span>
                            </span>
                        </template>
                    </button>
                </div>
            </div>

            <!-- Box Hasil Analisis AI -->
            <div x-show="aiResult" x-cloak class="mt-4 p-4 rounded-2xl border border-black/10 dark:border-white/10 bg-slate-50/70 dark:bg-white/[0.02] space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Hasil Pencocokan Pos Tarif BTKI 2022</div>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="font-mono font-black text-lg text-indigo-700 dark:text-indigo-400" x-text="aiResult?.code"></span>
                            <button type="button" @click="navigator.clipboard.writeText(aiResult?.code); alert('Pos tarif disalin!');" class="text-slate-400 hover:text-slate-700 text-xs cursor-pointer" title="Salin Pos Tarif">
                                📋
                            </button>
                        </div>
                        <div class="font-semibold text-slate-900 dark:text-white mt-1" x-text="aiResult?.desc"></div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5" x-text="aiResult?.legal"></div>
                    </div>

                    <!-- Badge Status Lartas Hasil AI -->
                    <div class="shrink-0 text-right">
                        <template x-if="aiResult?.has_lartas">
                            <div>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-300 inline-flex items-center gap-1">
                                    <span>⚠️</span>
                                    <span>WAJIB LARTAS EKSPOR</span>
                                </span>
                                <div class="text-[10px] text-amber-700 dark:text-amber-400 font-semibold mt-1" x-text="aiResult?.lartas"></div>
                            </div>
                        </template>
                        <template x-if="!aiResult?.has_lartas">
                            <div>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300 inline-flex items-center gap-1">
                                    <span>✓</span>
                                    <span>BEBAS LARTAS EKSPOR</span>
                                </span>
                                <div class="text-[10px] text-emerald-700 dark:text-emerald-400 font-medium mt-1">Non-Lartas (Langsung Proses)</div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Tarif Bea Keluar & Dokumen Izin Wajib -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-2 border-t border-black/[0.06] dark:border-white/[0.06] text-center">
                    <div class="p-2 rounded-xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10">
                        <span class="block text-[9px] text-slate-400 uppercase font-bold">Bea Keluar (BK)</span>
                        <span class="font-mono font-bold text-xs text-slate-900 dark:text-white" x-text="(aiResult?.rates?.bk ?? aiResult?.bk ?? 0) + '%'"></span>
                    </div>
                    <div class="p-2 rounded-xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10">
                        <span class="block text-[9px] text-slate-400 uppercase font-bold">Satuan Pabean</span>
                        <span class="font-mono font-bold text-xs text-slate-900 dark:text-white" x-text="aiResult?.satuan || 'KG / PCE'"></span>
                    </div>
                    <div class="p-2 rounded-xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10">
                        <span class="block text-[9px] text-slate-400 uppercase font-bold">AI Model Used</span>
                        <span class="font-mono font-bold text-[10px] text-indigo-600 dark:text-indigo-400" x-text="aiResult?.ai_model_used || aiModel"></span>
                    </div>
                    <div class="p-2 rounded-xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10">
                        <span class="block text-[9px] text-slate-400 uppercase font-bold">Confidence</span>
                        <span class="font-mono font-bold text-xs text-emerald-600" x-text="Math.round((aiResult?.confidence_score || 0.98) * 100) + '%'"></span>
                    </div>
                </div>

                <!-- Daftar Dokumen Perizinan Jika Ada -->
                <template x-if="aiResult?.required_documents && aiResult.required_documents.length > 0">
                    <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 space-y-1.5">
                        <span class="block font-bold text-amber-900 dark:text-amber-200 text-[11px]">
                            Dokumen Perizinan Ekspor Yang Dipersyaratkan:
                        </span>
                        <div class="space-y-1">
                            <template x-for="doc in aiResult.required_documents" :key="doc.kode_dokumen">
                                <div class="text-[10px] text-amber-800 dark:text-amber-300 flex items-center justify-between">
                                    <span>&bull; <strong x-text="'[' + doc.kode_dokumen + '] ' + doc.nama_dokumen"></strong></span>
                                    <span class="italic text-amber-700/70" x-text="doc.instansi"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Action Button untuk langsung buat pengajuan -->
                <div class="pt-2 flex items-center justify-end gap-2">
                    <a :href="'{{ route('bc30.create') }}?hs_code=' + encodeURIComponent((aiResult?.clean_code || aiResult?.code || '').replace(/[^0-9]/g, '')) + '&uraian=' + encodeURIComponent(aiResult?.desc || aiQuery)"
                       class="btn-apple-filled px-4 py-2 text-xs inline-flex items-center gap-1.5">
                        <span>Buat Pengajuan PEB dengan Pos Tarif Ini</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            </div>

            <div class="pt-2 border-t border-black/[0.06] dark:border-white/[0.06] flex justify-end">
                <button type="button" @click="aiModalOpen = false" class="btn-apple-gray px-4 py-2 text-xs">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
