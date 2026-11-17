@extends('layouts.app')

@section('title', 'Tim Document & Dokumen Pabean (BC 2.0 & BC 3.0) — CEISA 4.0')

@section('content')
<script>
    // Fallback safe global variables agar template directives Alpine tidak pernah throw ReferenceError
    window.aiModalOpen = false;
    window.productText = '';
    window.direction = 'import';
    window.aiModel = 'cx/gpt-6-astra';
    window.loading = false;
    window.errorMsg = null;
    window.result = null;

    // Inisialisasi Komponen Alpine.js di awal halaman sebelum DOM diparse
    window.aiHsMatcher = function aiHsMatcher() {
        return {
            aiModalOpen: false,
            productText: '',
            direction: 'import',
            aiModel: 'cx/gpt-6-astra',
            loading: false,
            errorMsg: null,
            result: null,

            async detect() {
                if (!this.productText.trim()) return;
                this.loading = true;
                this.errorMsg = null;
                this.result = null;

                try {
                    const res = await fetch('{{ route('ai.classify-hs') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            text: this.productText,
                            direction: this.direction,
                            model: this.aiModel
                        })
                    });

                    const data = await res.json();
                    if (!res.ok) {
                        throw new Error(data.message || 'Gagal memproses klasifikasi AI.');
                    }
                    this.result = data;
                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                } catch (err) {
                    this.errorMsg = err.message || 'Terjadi kesalahan jaringan atau server OpenAI.';
                } finally {
                    this.loading = false;
                }
            }
        };
    };

    if (window.Alpine && typeof window.Alpine.data === 'function') {
        window.Alpine.data('aiHsMatcher', window.aiHsMatcher);
    }
    document.addEventListener('alpine:init', () => {
        if (window.Alpine && typeof window.Alpine.data === 'function') {
            window.Alpine.data('aiHsMatcher', window.aiHsMatcher);
        }
    });
</script>
<div x-data="aiHsMatcher()" class="max-w-[1360px] mx-auto px-4 sm:px-6 space-y-6 pt-4 pb-28 lg:pb-12">

    <!-- 1. TOOLBAR / PAGE HEADER (Gabungan Tim Document + BC 2.0 PIB + BC 3.0 PEB) -->
    <header class="rounded-[16px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-4 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 shadow-sm">
        <div>
            <nav class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 font-medium mb-1" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-[#0284c7] transition-colors flex items-center gap-1">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i>
                    <span>Dashboard</span>
                </a>
                <i data-lucide="chevron-right" class="w-3 h-3 text-slate-300 dark:text-slate-600"></i>
                <span class="text-slate-800 dark:text-white font-semibold">Tim Document &bull; Gabungan BC 2.0 &amp; BC 3.0</span>
            </nav>
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-[20px] sm:text-[22px] font-bold text-black dark:text-white tracking-tight">
                    Tim Document &amp; Dokumen Pabean
                </h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#0284c7]/10 text-[#0284c7]">
                    BC 2.0 (PIB) + BC 3.0 (PEB)
                </span>
            </div>
            <p class="text-[13px] text-black/50 dark:text-white/50 mt-1 leading-snug">
                Workspace terpadu gabungan Pengajuan Impor (BC 2.0 PIB), Pengajuan Ekspor (BC 3.0 PEB), proyek pengapalan, penjaluran CEISA 4.0, Billing/SPPB/NPE, dan audit Lartas.
            </p>
        </div>

        <!-- Action Buttons Toolbar (Icon-Only Apple HIG Compact Group) -->
        <div class="flex items-center gap-1.5 w-full lg:w-auto shrink-0 flex-wrap justify-end">
            @can('setting.manage')
            <!-- Sync Semua CEISA Global -->
            <form action="{{ route('config.sync-all') }}" method="POST" class="inline">
                @csrf
                <button type="submit"
                        class="w-9 h-9 rounded-xl border border-emerald-300 dark:border-emerald-800 bg-emerald-50/80 dark:bg-emerald-950/40 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 text-emerald-700 dark:text-emerald-400 inline-flex items-center justify-center transition-colors shadow-2xs cursor-pointer"
                        title="Tarik seluruh status terbaru dokumen BC 2.0 & BC 3.0 dari CEISA 4.0"
                        aria-label="Sync Semua CEISA Global">
                    <i data-lucide="refresh-cw" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                </button>
            </form>

            <!-- Tarik Data CEISA (By No Aju / No Daftar) -->
            <button type="button"
                    onclick="document.getElementById('modal-sync-ceisa').classList.remove('hidden')"
                    class="w-9 h-9 rounded-xl border border-emerald-300 dark:border-emerald-800 bg-emerald-50/80 dark:bg-emerald-950/40 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 text-emerald-700 dark:text-emerald-400 inline-flex items-center justify-center transition-colors shadow-2xs cursor-pointer"
                    title="Tarik Data Dokumen dari CEISA 4.0"
                    aria-label="Tarik Data CEISA">
                <i data-lucide="download-cloud" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
            </button>
            @endcan

            <!-- Deteksi HS Code & Lartas AI Modal -->
            <button type="button"
                    @click="aiModalOpen = true"
                    class="w-9 h-9 rounded-xl border border-sky-300 dark:border-sky-800 bg-sky-50/80 dark:bg-sky-950/40 hover:bg-sky-100 dark:hover:bg-sky-900/60 text-[#0284c7] inline-flex items-center justify-center transition-colors shadow-2xs cursor-pointer"
                    title="Deteksi HS Code &amp; Skrining Lartas AI"
                    aria-label="Deteksi HS Code & Lartas AI">
                <i data-lucide="sparkles" class="w-4 h-4 text-[#0284c7]"></i>
            </button>

            @can('document.create')
            <!-- Buat BC 2.0 (PIB) -->
            <button type="button"
               onclick="document.getElementById('modal-create-bc20-core').classList.remove('hidden')"
               class="w-9 h-9 rounded-xl border border-[#0284c7]/25 hover:bg-[#0284c7]/10 text-[#0284c7] inline-flex items-center justify-center transition-colors shadow-2xs relative cursor-pointer"
               title="Buat Pengajuan Impor BC 2.0 (PIB)"
               aria-label="Buat BC 2.0 PIB">
                <i data-lucide="arrow-down-left" class="w-4 h-4"></i>
                <span class="absolute -top-1 -right-1 px-1 py-0.2 rounded text-[8px] font-mono font-bold bg-[#0284c7] text-white leading-none">2.0</span>
            </button>

            <!-- Buat BC 3.0 (PEB) -->
            <button type="button"
               onclick="document.getElementById('modal-create-bc30-core').classList.remove('hidden')"
               class="w-9 h-9 rounded-xl border border-indigo-500/25 hover:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 inline-flex items-center justify-center transition-colors shadow-2xs relative cursor-pointer"
               title="Buat Pengajuan Ekspor BC 3.0 (PEB)"
               aria-label="Buat BC 3.0 PEB">
                <i data-lucide="arrow-up-right" class="w-4 h-4"></i>
                <span class="absolute -top-1 -right-1 px-1 py-0.2 rounded text-[8px] font-mono font-bold bg-indigo-600 text-white leading-none">3.0</span>
            </button>

            <!-- Buka Proyek Baru -->
            <button type="button"
                    onclick="document.getElementById('modal-create-project').classList.remove('hidden')"
                    class="btn-apple-filled !w-9 !h-9 !p-0 inline-flex items-center justify-center rounded-xl shadow-xs cursor-pointer"
                    title="Buka Proyek Baru (P01)"
                    aria-label="Buka Proyek Baru">
                <i data-lucide="plus" class="w-4.5 h-4.5"></i>
            </button>
            @endcan
        </div>
    </header>

    <!-- 2. 5 APPLE HIG BENTO KPI STATS CARDS (Gabungan Proyek + BC 2.0 + BC 3.0) -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3.5 sm:gap-4">
        <!-- Card 1: Total Dokumen & Proyek -->
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase tracking-wider font-semibold text-black/45 dark:text-white/45">Total BC 2.0 + BC 3.0</span>
                <div class="w-8 h-8 rounded-[10px] bg-[#0284c7]/10 text-[#0284c7] flex items-center justify-center">
                    <i data-lucide="layers" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <div class="text-[22px] sm:text-[24px] font-bold tabular-nums text-black dark:text-white tracking-tight">
                    {{ $stats['total'] ?? 0 }}
                </div>
                <div class="text-[11px] text-black/50 dark:text-white/50 mt-0.5 truncate">
                    {{ $stats['bc20_total'] ?? 0 }} PIB Impor &bull; {{ $stats['bc30_total'] ?? 0 }} PEB Ekspor
                </div>
            </div>
        </div>

        <!-- Card 2: Draft Disiapkan -->
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase tracking-wider font-semibold text-black/45 dark:text-white/45">Draft &amp; Persiapan</span>
                <div class="w-8 h-8 rounded-[10px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center">
                    <i data-lucide="file-edit" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <div class="text-[22px] sm:text-[24px] font-bold tabular-nums text-[#FF9500] dark:text-[#FF9F0A] tracking-tight">
                    {{ $stats['draft'] ?? 0 }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5 truncate">Belum submit CEISA / kumpul berkas</div>
            </div>
        </div>

        <!-- Card 3: Proses & Billing -->
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase tracking-wider font-semibold text-black/45 dark:text-white/45">Proses &amp; Billing</span>
                <div class="w-8 h-8 rounded-[10px] bg-[#5856D6]/10 text-[#5856D6] flex items-center justify-center">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <div class="text-[22px] sm:text-[24px] font-bold tabular-nums text-[#5856D6] dark:text-[#5E5CE6] tracking-tight">
                    {{ $stats['billing'] ?? 0 }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5 truncate">Validasi CEISA &amp; MPN G3 aktif</div>
            </div>
        </div>

        <!-- Card 4: SPPB & NPE Terbit -->
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase tracking-wider font-semibold text-black/45 dark:text-white/45">SPPB &amp; NPE Terbit</span>
                <div class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <div class="text-[22px] sm:text-[24px] font-bold tabular-nums text-[#248A3D] dark:text-[#30D158] tracking-tight">
                    {{ $stats['released'] ?? 0 }}
                </div>
                <div class="text-[11px] text-black/50 dark:text-white/50 mt-0.5 truncate">
                    {{ $stats['sppb'] ?? 0 }} SPPB Impor &bull; {{ $stats['npe'] ?? 0 }} NPE Ekspor
                </div>
            </div>
        </div>

        <!-- Card 5: Produk Kena Lartas -->
        <a href="{{ route('core.document.index', ['lartas' => 'yes']) }}"
           class="rounded-[16px] bg-amber-50/70 dark:bg-amber-950/20 border border-amber-500/25 dark:border-amber-400/25 p-4 flex flex-col justify-between shadow-sm relative overflow-hidden hover:border-amber-500 transition-all col-span-2 md:col-span-1">
            <div class="absolute -right-3 -bottom-3 w-16 h-16 bg-amber-500/10 rounded-full blur-xl pointer-events-none"></div>
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase tracking-wider font-semibold text-amber-600 dark:text-amber-400">Produk Kena Lartas</span>
                <div class="w-8 h-8 rounded-[10px] bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                    <i data-lucide="shield-alert" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <div class="text-[22px] sm:text-[24px] font-bold tabular-nums text-amber-600 dark:text-amber-400 tracking-tight">
                    {{ $stats['lartas_products'] ?? 0 }} <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Produk</span>
                </div>
                <div class="text-[11px] text-amber-700/80 dark:text-amber-400/80 mt-0.5 truncate">
                    {{ $stats['lartas_shipments'] ?? 0 }} dok/proyek wajib izin K/L &rarr;
                </div>
            </div>
        </a>
    </div>

    <!-- 3. FILTER & SEARCH BAR (Gabungan Pencarian Proyek, Nomor Aju, Status CEISA, BC 2.0/3.0, & Lartas) -->
    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm p-4 space-y-3">
        <form method="GET" action="{{ route('core.document.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-[13px]">
            <div class="sm:col-span-4 relative">
                <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari No. Aju, Nopen, No. Proyek, B/L, Kantor, Mitra LN, Importir/Eksportir..."
                       class="w-full pl-9 pr-3.5 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-black dark:text-white text-[13px] focus:outline-none focus:border-[#0284c7] focus:ring-2 focus:ring-[#0284c7]/20 transition-all placeholder:text-black/40 dark:placeholder:text-white/40">
            </div>

            <div class="sm:col-span-2">
                <select name="type" class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-black dark:text-white text-[13px] focus:outline-none focus:border-[#0284c7] focus:ring-2 focus:ring-[#0284c7]/20 transition-all">
                    <option value="">Semua (BC 2.0 + BC 3.0)</option>
                    <option value="IMPORT" {{ request('type') === 'IMPORT' ? 'selected' : '' }}>Impor — BC 2.0 (PIB)</option>
                    <option value="EXPORT" {{ request('type') === 'EXPORT' ? 'selected' : '' }}>Ekspor — BC 3.0 (PEB)</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <select name="status" class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-black dark:text-white text-[13px] focus:outline-none focus:border-[#0284c7] focus:ring-2 focus:ring-[#0284c7]/20 transition-all">
                    <option value="">Semua Status</option>
                    <optgroup label="Status CEISA 4.0">
                        <option value="DRAFT" {{ request('status') === 'DRAFT' ? 'selected' : '' }}>DRAFT (Pengisian Awal)</option>
                        <option value="SUBMITTED" {{ request('status') === 'SUBMITTED' ? 'selected' : '' }}>SUBMITTED (Terkirim)</option>
                        <option value="PROSES" {{ request('status') === 'PROSES' ? 'selected' : '' }}>PROSES (Validasi)</option>
                        <option value="BILLING" {{ request('status') === 'BILLING' ? 'selected' : '' }}>BILLING (MPN G3)</option>
                        <option value="SPPB" {{ request('status') === 'SPPB' ? 'selected' : '' }}>SPPB (BC 2.0 Keluar)</option>
                        <option value="NPE" {{ request('status') === 'NPE' ? 'selected' : '' }}>NPE (BC 3.0 Muat)</option>
                        <option value="REJECT" {{ request('status') === 'REJECT' ? 'selected' : '' }}>REJECT (Ditolak)</option>
                    </optgroup>
                    <optgroup label="Status Siklus Proyek">
                        <option value="DOC_COLLECTING" {{ request('status') === 'DOC_COLLECTING' ? 'selected' : '' }}>Kumpul Berkas</option>
                        <option value="DOC_READY" {{ request('status') === 'DOC_READY' ? 'selected' : '' }}>Dokumen Lengkap</option>
                        <option value="HS_DETECTED" {{ request('status') === 'HS_DETECTED' ? 'selected' : '' }}>HS Terdeteksi</option>
                        <option value="DRAFT_REVIEW" {{ request('status') === 'DRAFT_REVIEW' ? 'selected' : '' }}>Draft Review</option>
                        <option value="BILLING_ISSUED" {{ request('status') === 'BILLING_ISSUED' ? 'selected' : '' }}>Billing Terbit</option>
                        <option value="DO_PROCESSING" {{ request('status') === 'DO_PROCESSING' ? 'selected' : '' }}>Proses Tim DO</option>
                        <option value="DELIVERED_POD" {{ request('status') === 'DELIVERED_POD' ? 'selected' : '' }}>Selesai (POD)</option>
                    </optgroup>
                </select>
            </div>

            <div class="sm:col-span-2">
                <select name="lartas" class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-black dark:text-white text-[13px] focus:outline-none focus:border-[#0284c7] focus:ring-2 focus:ring-[#0284c7]/20 transition-all font-medium">
                    <option value="">Semua Status Lartas</option>
                    <option value="yes" {{ request('lartas') === 'yes' ? 'selected' : '' }}>⚠️ Wajib Izin Lartas ({{ $stats['lartas_shipments'] ?? 0 }})</option>
                    <option value="no" {{ request('lartas') === 'no' ? 'selected' : '' }}>✓ Bebas Lartas ({{ $stats['non_lartas_shipments'] ?? 0 }})</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex items-center space-x-2">
                <button type="submit" class="btn-apple-gray w-full !h-9 text-[12px] font-semibold hover:!bg-[#0284c7]/10 hover:!text-[#0284c7]">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Terapkan</span>
                </button>
                <a href="{{ route('core.document.index') }}" class="btn-apple-gray !h-9 !px-3 text-[12px]">
                    <span>Reset</span>
                </a>
            </div>
        </form>

        <!-- Quick Filter Chips (Jenis BC 2.0 / BC 3.0 & Lartas) -->
        <div class="flex items-center gap-2 pt-2 border-t border-black/5 dark:border-white/5 text-[12px] overflow-x-auto pb-1">
            <span class="text-black/45 dark:text-white/45 font-medium shrink-0">Filter Cepat:</span>
            <a href="{{ route('core.document.index', array_merge(request()->except('type', 'lartas', 'page'))) }}"
               class="px-3 py-1 rounded-full border transition-all shrink-0 {{ !request()->filled('type') && !request()->filled('lartas') ? 'bg-[#0284c7] text-white border-transparent font-medium shadow-sm' : 'bg-black/[0.03] dark:bg-white/[0.05] border-black/5 dark:border-white/10 text-black/70 dark:text-white/70 hover:bg-black/[0.06]' }}">
                Semua ({{ $stats['total'] ?? 0 }})
            </a>
            <a href="{{ route('core.document.index', array_merge(request()->except('page'), ['type' => 'IMPORT'])) }}"
               class="px-3 py-1 rounded-full border transition-all shrink-0 {{ request('type') === 'IMPORT' ? 'bg-[#0284c7] text-white border-transparent font-semibold shadow-sm' : 'bg-sky-500/10 border-sky-500/20 text-[#0284c7] hover:bg-sky-500/20' }}">
                BC 2.0 Impor / PIB ({{ $stats['bc20_total'] ?? 0 }})
            </a>
            <a href="{{ route('core.document.index', array_merge(request()->except('page'), ['type' => 'EXPORT'])) }}"
               class="px-3 py-1 rounded-full border transition-all shrink-0 {{ request('type') === 'EXPORT' ? 'bg-indigo-600 text-white border-transparent font-semibold shadow-sm' : 'bg-indigo-500/10 border-indigo-500/20 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-500/20' }}">
                BC 3.0 Ekspor / PEB ({{ $stats['bc30_total'] ?? 0 }})
            </a>
            <a href="{{ route('core.document.index', array_merge(request()->except('page'), ['lartas' => 'yes'])) }}"
               class="px-3 py-1 rounded-full border transition-all shrink-0 flex items-center gap-1.5 {{ request('lartas') === 'yes' ? 'bg-amber-500 text-white border-transparent font-semibold shadow-sm' : 'bg-amber-500/10 border-amber-500/20 text-amber-700 dark:text-amber-400 hover:bg-amber-500/20' }}">
                <span>⚠️ Wajib Lartas ({{ $stats['lartas_shipments'] ?? 0 }} dok &bull; {{ $stats['lartas_products'] ?? 0 }} produk)</span>
            </a>
            <a href="{{ route('core.document.index', array_merge(request()->except('page'), ['lartas' => 'no'])) }}"
               class="px-3 py-1 rounded-full border transition-all shrink-0 flex items-center gap-1.5 {{ request('lartas') === 'no' ? 'bg-emerald-600 text-white border-transparent font-semibold shadow-sm' : 'bg-emerald-500/10 border-emerald-500/20 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-500/20' }}">
                <span>✓ Bebas Lartas ({{ $stats['non_lartas_shipments'] ?? 0 }})</span>
            </a>
        </div>
    </div>

    <!-- 4. DATA LEDGER TABLE GABUNGAN (BC 2.0 PIB + BC 3.0 PEB + PROYEK PENGAPALAN) -->
    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm overflow-hidden flex flex-col">
        <div class="p-4 sm:p-5 border-b border-black/5 dark:border-white/5 flex items-center justify-between gap-3 flex-wrap">
            <div class="font-semibold text-[13px] text-black dark:text-white flex items-center space-x-2">
                <span>Ledger Gabungan BC 2.0 (PIB Impor) &amp; BC 3.0 (PEB Ekspor)</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 font-mono">{{ $shipments->total() }} Data</span>
            </div>
            <div class="text-[12px] text-black/45 dark:text-white/45 hidden sm:block">
                Menampilkan pengajuan PIB BC 2.0, PEB BC 3.0, Nomor Aju/Nopen, Lartas, Valuasi CIF/FOB, Billing/SPPB/NPE &amp; Audit Berkas
            </div>
        </div>

        <div class="table-responsive flex-1 overflow-x-auto relative">
            <table class="w-full text-left border-separate border-spacing-0 min-w-max text-[13px] whitespace-nowrap">
                <thead class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider bg-black/[0.02] dark:bg-white/[0.02] whitespace-nowrap">
                    <tr>
                        <th class="py-3 px-4 border-b border-black/10 dark:border-white/10 whitespace-nowrap">Jenis, No. Aju &amp; Proyek</th>
                        <th class="py-3 px-4 border-b border-black/10 dark:border-white/10 whitespace-nowrap">Kantor &amp; Pengangkutan</th>
                        <th class="py-3 px-4 border-b border-black/10 dark:border-white/10 whitespace-nowrap">Entitas &amp; Mitra LN</th>
                        <th class="py-3 px-4 border-b border-black/10 dark:border-white/10 whitespace-nowrap">Produk, Lartas &amp; Berkas</th>
                        <th class="py-3 px-4 text-right border-b border-black/10 dark:border-white/10 whitespace-nowrap">Valuasi (CIF / FOB)</th>
                        <th class="py-3 px-4 text-center border-b border-black/10 dark:border-white/10 whitespace-nowrap">Status CEISA &amp; Siklus</th>
                        <th class="py-3 px-4 border-b border-black/10 dark:border-white/10 whitespace-nowrap">Billing MPN / SPPB / NPE</th>
                        <th class="py-3 px-4 text-center sticky right-0 z-30 bg-white dark:bg-[#1C1C1E] border-b border-l border-black/10 dark:border-white/10 shadow-[-6px_0_12px_rgba(0,0,0,0.06)] whitespace-nowrap table-freeze-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-0">
                    @php
                        $statusMap = [
                            'DOC_COLLECTING' => ['label' => 'Kumpul Berkas', 'class' => 'bg-black/[0.05] text-black/70 dark:bg-white/[0.08] dark:text-white/70'],
                            'DOC_READY' => ['label' => 'Berkas Lengkap', 'class' => 'bg-[#5856D6]/12 text-[#5856D6] dark:text-[#5E5CE6]'],
                            'HS_DETECTED' => ['label' => 'HS Terdeteksi', 'class' => 'bg-[#5856D6]/12 text-[#5856D6] dark:text-[#5E5CE6]'],
                            'DRAFT_REVIEW' => ['label' => 'Review SPV', 'class' => 'bg-[#0284c7]/12 text-[#0284c7]'],
                            'CEISA_SUBMITTED' => ['label' => 'Terkirim CEISA', 'class' => 'bg-[#0284c7]/12 text-[#0284c7]'],
                            'BILLING_ISSUED' => ['label' => 'Billing Terbit', 'class' => 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] font-bold'],
                            'DO_PROCESSING' => ['label' => 'Proses DO', 'class' => 'bg-[#FF9500]/15 text-[#B25E00] dark:text-[#FF9F0A]'],
                            'DO_RELEASED' => ['label' => 'DO Rilis', 'class' => 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] font-bold'],
                            'TRAFFIC_HOLD' => ['label' => 'Hold TPS', 'class' => 'bg-[#FF3B30]/15 text-[#C41E17] dark:text-[#FF453A]'],
                            'TRAFFIC_SPPB' => ['label' => 'SPPB Rilis', 'class' => 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] font-bold'],
                            'TRAFFIC_GATE_OUT' => ['label' => 'Gate Out TPS', 'class' => 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] font-bold'],
                            'DELIVERED_POD' => ['label' => 'Tiba di Gudang', 'class' => 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] font-bold'],
                            'INVOICED_P19' => ['label' => 'Invoice Terbit', 'class' => 'bg-[#5856D6]/12 text-[#5856D6]'],
                            'COMPLETED' => ['label' => 'Selesai', 'class' => 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] font-bold'],
                        ];
                    @endphp
                    @forelse($shipments as $entry)
                        @php
                            $shp = $entry->shipment;
                            $ceisa = $entry->ceisa;
                            $isExport = $entry->flow === 'EXPORT';
                            $lInfo = $entry->lartas_info;
                            $ceisaDetailRoute = $ceisa
                                ? ($isExport ? route('bc30.show', $ceisa->id) : route('bc20.show', $ceisa->id))
                                : null;
                            $ceisaEditRoute = $ceisa
                                ? ($isExport ? route('bc30.edit', $ceisa->id) : route('bc20.edit', $ceisa->id))
                                : null;
                            $canEditCeisa = false;
                            if ($ceisa && !$ceisa->isBillingLocked()) {
                                if ($ceisa->status === 'DRAFT') {
                                    $canEditCeisa = auth()->user()->isSuperAdmin() || auth()->user()->hasAnyPermission(['document.edit', 'document.edit_after_ceisa']);
                                } else {
                                    $canEditCeisa = auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('document.edit_after_ceisa');
                                }
                            }
                        @endphp
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                            <!-- 1. Jenis, No Aju & Proyek -->
                            <td class="py-3.5 px-4 font-mono">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="inline-block px-2 py-0.5 rounded-[6px] text-[10px] font-bold {{ $isExport ? 'bg-indigo-500/12 text-indigo-700 dark:text-indigo-300 border border-indigo-500/20' : 'bg-[#0284c7]/12 text-[#0284c7] border border-[#0284c7]/20' }}">
                                        {{ $isExport ? 'BC 3.0 EKSPOR' : 'BC 2.0 IMPOR' }}
                                    </span>
                                    @if($shp)
                                        <a href="{{ route('core.document.show', $shp->id) }}" class="font-bold text-[#0284c7] hover:underline text-[12px]">
                                            {{ $shp->shipment_number ?? $shp->project_no }}
                                        </a>
                                    @endif
                                </div>

                                @if($ceisa)
                                    <div class="mt-1">
                                        <a href="{{ $ceisaDetailRoute }}" class="text-[11px] font-bold text-slate-900 dark:text-white hover:text-[#0284c7] hover:underline">
                                            {{ $ceisa->nomor_aju }}
                                        </a>
                                    </div>
                                    @if($ceisa->nomor_daftar)
                                        <div class="text-[10px] text-slate-500 dark:text-slate-400 tabular-nums">
                                            No Pen: {{ $ceisa->nomor_daftar }} ({{ $ceisa->tanggal_daftar?->format('d/m/Y') }})
                                        </div>
                                    @endif
                                @endif

                                @if($shp)
                                    <div class="text-[11px] text-black/50 dark:text-white/50 mt-0.5 font-sans">
                                        B/L: <strong class="text-black dark:text-white font-mono">{{ $shp->bl_number ?? '-' }}</strong>
                                    </div>
                                @elseif($ceisa && $ceisa->created_at)
                                    <div class="text-[10px] text-slate-400 dark:text-slate-500 font-sans mt-0.5 tabular-nums">
                                        {{ $ceisa->created_at->format('d M Y, H:i') }}
                                    </div>
                                @endif
                            </td>

                            <!-- 2. Kantor Pabean & Pengangkutan -->
                            <td class="py-3.5 px-4 text-black/80 dark:text-white/80">
                                @if($entry->kode_kantor)
                                    <div class="font-mono font-semibold text-slate-800 dark:text-white text-[12px]">
                                        {{ $entry->kode_kantor }}
                                    </div>
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400 truncate max-w-[160px]" title="{{ $entry->nama_kantor }}">
                                        {{ $entry->nama_kantor }}
                                    </div>
                                @endif
                                @if($shp)
                                    <div class="font-medium text-black dark:text-white text-[12px] truncate max-w-[160px] {{ $entry->kode_kantor ? 'mt-1 pt-1 border-t border-black/5 dark:border-white/5' : '' }}">
                                        {{ $shp->vessel_name ?? '-' }}
                                    </div>
                                    <div class="text-[10px] text-black/45 dark:text-white/45">
                                        Voy: {{ $shp->voyage_number ?? '-' }} &bull; ETA: {{ $shp->eta ? $shp->eta->format('d/m/Y') : '-' }}
                                    </div>
                                    <div class="text-[10px] font-mono text-black/50 dark:text-white/50">{{ $shp->nomor_kontainer ?? '1 Kontainer' }}</div>
                                @elseif(!$entry->kode_kantor)
                                    <span class="text-black/30 dark:text-white/30 text-[11px]">-</span>
                                @endif
                            </td>

                            <!-- 3. Entitas & Mitra Luar Negeri -->
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-black dark:text-white text-[12px] leading-tight truncate max-w-[190px]" title="{{ $entry->local_name }}">
                                    {{ $entry->local_name }}
                                </div>
                                <div class="text-[10px] text-black/45 dark:text-white/45 font-mono mt-0.5">
                                    {{ $isExport ? 'Eksportir' : 'Importir' }} &bull; {{ $entry->local_npwp }}
                                </div>
                                @if($entry->partner_name)
                                    <div class="mt-1 pt-1 border-t border-black/5 dark:border-white/5">
                                        <div class="text-[11px] font-medium text-slate-700 dark:text-slate-300 truncate max-w-[190px]" title="{{ $entry->partner_name }}">
                                            {{ $isExport ? 'Pembeli LN:' : 'Supplier:' }} {{ $entry->partner_name }}
                                        </div>
                                        <div class="text-[10px] text-slate-400 font-mono">
                                            {{ $isExport ? 'Tujuan:' : 'Asal:' }} {{ $entry->partner_country ?? 'LN' }}
                                        </div>
                                    </div>
                                @endif
                                @if($shp?->shippingVendor)
                                    <div class="text-[10px] text-black/55 dark:text-white/55 mt-0.5">Pelayaran: <strong>{{ $shp->shippingVendor?->name ?? 'Pelayaran' }}</strong></div>
                                @endif
                            </td>

                            <!-- 4. Produk, Lartas & Audit 9 Berkas -->
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-black dark:text-white text-[12px] flex items-center gap-1.5 mb-1">
                                    <span class="font-mono tabular-nums">{{ $lInfo['total_products'] ?? 0 }}</span> Barang
                                </div>
                                @if(!empty($lInfo['has_lartas']))
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-500/25">
                                        <i data-lucide="alert-triangle" class="w-3 h-3 text-amber-600 shrink-0"></i>
                                        <span>{{ $lInfo['lartas_count'] }} Kena Lartas</span>
                                    </span>
                                    @if(!empty($lInfo['primary_lartas']))
                                        <div class="text-[10px] text-black/50 dark:text-white/50 mt-0.5 truncate max-w-[165px]" title="{{ $lInfo['primary_lartas'] }}">
                                            {{ $lInfo['primary_lartas'] }}
                                        </div>
                                    @endif
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 border border-emerald-500/25">
                                        <i data-lucide="check" class="w-3 h-3 text-emerald-600 shrink-0"></i>
                                        <span>Bebas Lartas</span>
                                    </span>
                                @endif

                                @if($shp)
                                    @php
                                        $verifiedCount = $shp->documents->where('status', 'VERIFIED')->count();
                                        $uploadedCount = $shp->documents->where('status', 'UPLOADED')->count();
                                        $percent = $shp->document_completion_rate;
                                    @endphp
                                    <div class="mt-2 pt-1.5 border-t border-black/5 dark:border-white/5">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-14 bg-black/[0.06] dark:bg-white/[0.08] rounded-full h-1.5 overflow-hidden">
                                                <div class="h-1.5 rounded-full {{ $percent >= 100 ? 'bg-[#34C759]' : ($percent >= 50 ? 'bg-[#0284c7]' : 'bg-[#FF9500]') }}"
                                                     style="width: {{ $percent }}%"></div>
                                            </div>
                                            <span class="font-mono font-bold text-[10px] tabular-nums {{ $percent >= 100 ? 'text-[#248A3D] dark:text-[#30D158]' : 'text-black/70 dark:text-white/70' }}">{{ $percent }}%</span>
                                        </div>
                                        <div class="text-[10px] text-black/45 dark:text-white/45">{{ $verifiedCount }} verified &bull; {{ $uploadedCount }} baru</div>
                                    </div>
                                @endif
                            </td>

                            <!-- 5. Valuasi Pabean (CIF untuk BC 2.0 / FOB untuk BC 3.0) -->
                            <td class="py-3.5 px-4 text-right font-mono">
                                <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-bold uppercase mb-0.5 {{ $isExport ? 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : 'bg-sky-500/10 text-[#0284c7]' }}">
                                    {{ $entry->valuation_label }}
                                </span>
                                <div class="font-bold text-slate-900 dark:text-white text-[12px] tabular-nums">
                                    {{ $entry->kode_valuta }} {{ number_format($entry->total_nilai, 2) }}
                                </div>
                                <div class="text-[10px] text-slate-400 dark:text-slate-500 tabular-nums">
                                    Rp {{ number_format($entry->total_nilai_idr, 0, ',', '.') }}
                                </div>
                            </td>

                            <!-- 6. Status CEISA & Siklus -->
                            <td class="py-3.5 px-4 text-center space-y-1">
                                @if($ceisa)
                                    <div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold {{ $ceisa->status_badge_class }}">
                                            {{ $ceisa->status }}
                                        </span>
                                    </div>
                                    @if($ceisa->jalur)
                                        <div>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold font-mono {{ $ceisa->jalur === 'HIJAU' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($ceisa->jalur === 'KUNING' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-rose-50 text-rose-700 border border-rose-200') }}">
                                                Jalur {{ $ceisa->jalur }}
                                            </span>
                                        </div>
                                    @endif
                                @endif

                                @if($shp)
                                    @php
                                        $rawStatus = $shp->status ?? $shp->status_tahap;
                                        $statusInfo = $statusMap[$rawStatus] ?? ['label' => str_replace('_', ' ', $rawStatus), 'class' => 'bg-black/[0.05] text-black/70'];
                                    @endphp
                                    <div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $statusInfo['class'] }}">
                                            {{ $statusInfo['label'] }}
                                        </span>
                                    </div>
                                @endif
                            </td>

                            <!-- 7. Billing MPN G3 / Nomor SPPB / Nomor NPE -->
                            <td class="py-3.5 px-4 font-mono">
                                @if($shp?->billing_code || $ceisa?->kode_billing)
                                    <div class="font-bold text-purple-600 dark:text-purple-400 text-[12px] tracking-wider tabular-nums">
                                        {{ $shp?->billing_code ?? $ceisa?->kode_billing }}
                                    </div>
                                    @if($ceisa?->total_billing)
                                        <div class="text-[10px] text-slate-500 dark:text-slate-400 tabular-nums">Rp {{ number_format($ceisa->total_billing, 0, ',', '.') }}</div>
                                    @endif
                                @endif

                                @if($ceisa?->nomor_sppb_npe)
                                    <div class="{{ ($shp?->billing_code || $ceisa?->kode_billing) ? 'mt-1 pt-1 border-t border-black/5 dark:border-white/5' : '' }}">
                                        <span class="text-[9px] font-bold uppercase text-emerald-600 dark:text-emerald-400">
                                            {{ $isExport ? 'NPE:' : 'SPPB:' }}
                                        </span>
                                        <span class="text-emerald-700 dark:text-emerald-300 font-bold text-[11px] tabular-nums">{{ $ceisa->nomor_sppb_npe }}</span>
                                        @if($ceisa->tanggal_sppb_npe)
                                            <div class="text-[10px] text-slate-400 tabular-nums">{{ $ceisa->tanggal_sppb_npe->format('d/m/Y') }}</div>
                                        @endif
                                    </div>
                                @elseif(!($shp?->billing_code || $ceisa?->kode_billing))
                                    <span class="text-black/30 dark:text-white/30 text-[11px]">-</span>
                                @endif
                            </td>

                            <!-- 8. Aksi Gabungan (Freeze Sticky Right Column & Icon-Only Group) -->
                            <td class="py-3.5 px-3 text-center space-x-1 whitespace-nowrap sticky right-0 z-20 bg-white dark:bg-[#1C1C1E] border-b border-l border-black/5 dark:border-white/10 shadow-[-6px_0_12px_rgba(0,0,0,0.06)] group-hover:bg-[#F9F9FA] dark:group-hover:bg-[#202022] table-freeze-right">
                                @if($ceisaDetailRoute)
                                    <a href="{{ $ceisaDetailRoute }}"
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-sky-300 dark:border-sky-800 bg-sky-50/80 dark:bg-sky-950/40 hover:bg-sky-100 dark:hover:bg-sky-900/60 {{ $isExport ? 'text-indigo-600 dark:text-indigo-400' : 'text-[#0284c7]' }} transition-colors"
                                       title="Buka pelacakan alur 11 Pos dan detail pabean {{ $isExport ? 'BC 3.0' : 'BC 2.0' }}"
                                       aria-label="Pelacakan 11 Pos">
                                        <i data-lucide="compass" class="w-4 h-4"></i>
                                    </a>
                                @endif

                                @if($canEditCeisa && $ceisaEditRoute)
                                    <a href="{{ $ceisaEditRoute }}"
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-amber-300 dark:border-amber-800 bg-amber-50/80 dark:bg-amber-950/40 hover:bg-amber-100 dark:hover:bg-amber-900/60 text-amber-700 dark:text-amber-400 transition-colors"
                                       title="Buka Editor / Wizard {{ $isExport ? 'BC 3.0 PEB' : 'BC 2.0 PIB' }}"
                                       aria-label="Edit Dokumen">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>
                                @endif

                                <a href="{{ $shp ? route('core.document.show', $shp->id) : route('core.document.show', $ceisa->id) }}"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-white/15 bg-slate-50 dark:bg-white/5 hover:bg-slate-100 dark:hover:bg-white/10 text-slate-700 dark:text-slate-200 transition-colors"
                                   title="Buka Detail Proyek"
                                   aria-label="Detail Proyek">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>

                                @if($shp)
                                    <button type="button"
                                            onclick="openPortalModal('{{ $shp->id }}', '{{ $shp->project_no }}', '{{ addslashes($shp->client->name ?? '-') }}', '{{ $shp->portal_token ? route('portal.track', $shp->portal_token) : '' }}', '{{ $shp->portal_status }}', '{{ $shp->portal_expires_at ? $shp->portal_expires_at->format('d/m/Y H:i') : '' }}', '{{ $shp->portal_duration_days ?? 7 }}', '{{ $shp->client->pic_phone ?? ($shp->client->phone ?? '') }}', '{{ $shp->nomor_bl_awb ?? '' }}')"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-white/15 hover:bg-slate-100 dark:hover:bg-white/10 {{ $shp->isPortalActive() ? '!text-[#0284c7] !border-[#0284c7]/30 bg-[#0284c7]/5' : 'text-slate-500' }} transition-colors cursor-pointer"
                                            title="Bagikan Tautan Portal Klien"
                                            aria-label="Bagikan Portal">
                                        <i data-lucide="share-2" class="w-4 h-4"></i>
                                    </button>

                                    @if($isExport)
                                        <a href="{{ route('pdf.peb', $shp->id) }}" target="_blank"
                                           class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-white/15 bg-slate-50 dark:bg-white/5 hover:bg-slate-100 dark:hover:bg-white/10 text-slate-700 dark:text-slate-200 transition-colors"
                                           title="Cetak PEB BC 3.0 (PDF)"
                                           aria-label="Cetak PEB">
                                            <i data-lucide="printer" class="w-4 h-4"></i>
                                        </a>
                                    @else
                                        <a href="{{ route('pdf.pib', $shp->id) }}" target="_blank"
                                           class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 dark:border-white/15 bg-slate-50 dark:bg-white/5 hover:bg-slate-100 dark:hover:bg-white/10 text-slate-700 dark:text-slate-200 transition-colors"
                                           title="Cetak PIB BC 2.0 (PDF)"
                                           aria-label="Cetak PIB">
                                            <i data-lucide="printer" class="w-4 h-4"></i>
                                        </a>
                                    @endif
                                @endif

                                @if($entry->is_draft_deletable && (auth()->user()->isSuperAdmin() || auth()->user()->hasAnyPermission(['document.create', 'document.edit', 'document.delete'])))
                                    @php
                                        $destroyAction = $shp
                                            ? route('core.document.destroy', $shp->id)
                                             : ($isExport ? route('bc30.destroy', $ceisa->id) : route('bc20.destroy', $ceisa->id));
                                        $destroyLabel = $shp ? $shp->project_no : ($ceisa?->nomor_aju ?? 'Draft');
                                    @endphp
                                    <form action="{{ $destroyAction }}" method="POST" class="inline"
                                          onsubmit="return confirmSweetAlert(event, 'Hapus draft {{ $destroyLabel }} beserta seluruh aset berkas fisiknya? Tindakan ini tidak dapat dibatalkan.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-rose-300 dark:border-rose-900 bg-rose-50/80 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/60 text-rose-600 dark:text-rose-400 transition-colors cursor-pointer"
                                                title="Hapus Draft Dokumen"
                                                aria-label="Hapus Draft">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-black/40 dark:text-white/40 text-[13px]">
                                <i data-lucide="package-open" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                                <div>Tidak ada dokumen BC 2.0, BC 3.0, atau proyek shipment yang cocok dengan filter.</div>
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

    <!-- Modals Buka Proyek & Pengajuan BC 2.0 / BC 3.0 Cerdas -->
@include('core.components.smart_document_create_modal', [
    'modalId' => 'modal-create-project',
    'pfx' => 'core_gen_',
    'defaultType' => '',
    'lockType' => false,
    'defaultAfterAction' => 'fill_bc_form',
    'title' => 'Buka Proyek Pengapalan & Dokumen Pabean Baru',
    'subtitle' => 'Unggah berkas kargo (B/L, Invoice, Packing List). Sistem mengekstrak informasi dan mencocokkan pos tarif HS Code secara otomatis.',
    'clients' => $clients,
    'vendors' => $vendors
])

@include('core.components.smart_document_create_modal', [
    'modalId' => 'modal-create-bc20-core',
    'pfx' => 'core_bc20_',
    'defaultType' => 'IMPORT',
    'lockType' => true,
    'defaultAfterAction' => 'fill_bc_form',
    'title' => 'Buat Pengajuan Impor BC 2.0 (PIB)',
    'subtitle' => 'Unggah berkas kargo (B/L, Commercial Invoice, Packing List). Sistem mengekstrak informasi dan membuat draft dokumen PIB BC 2.0 secara otomatis.',
    'clients' => $clients,
    'vendors' => $vendors
])

@include('core.components.smart_document_create_modal', [
    'modalId' => 'modal-create-bc30-core',
    'pfx' => 'core_bc30_',
    'defaultType' => 'EXPORT',
    'lockType' => true,
    'defaultAfterAction' => 'fill_bc_form',
    'title' => 'Buat Pengajuan Ekspor BC 3.0 (PEB)',
    'subtitle' => 'Unggah berkas kargo (Booking Confirmation, Commercial Invoice, Packing List). Sistem mengekstrak informasi dan membuat draft dokumen PEB BC 3.0 secara otomatis.',
    'clients' => $clients,
    'vendors' => $vendors
])

<!-- Modal Share Portal Klien & Pengaturan Timer (Per Proyek) -->
<div id="modal-portal-share" class="hidden fixed inset-0 z-[200] overflow-y-auto bg-black/60 backdrop-blur-md flex items-center justify-center p-4">
    <div class="max-w-lg w-full bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/10 dark:border-white/10 shadow-2xl p-6 space-y-4 animate-in fade-in zoom-in duration-150">
        <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5">
            <div class="flex items-center space-x-2.5">
                <div class="w-9 h-9 rounded-[10px] bg-[#0284c7]/10 text-[#0284c7] flex items-center justify-center">
                    <i data-lucide="share-2" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="font-bold text-[15px] text-black dark:text-white">Portal Pelacakan Klien (Per Proyek)</h3>
                    <p id="portal-project-label" class="text-[11px] text-black/50 dark:text-white/50 font-mono">SHP-...</p>
                </div>
            </div>
            <button type="button" onclick="closePortalModal()" class="p-1.5 rounded-lg text-black/40 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Status Timer Box -->
        <div class="p-3.5 rounded-[14px] bg-slate-50 dark:bg-white/[0.04] border border-black/5 dark:border-white/5 text-[12px] space-y-1.5">
            <div class="flex items-center justify-between">
                <span class="text-black/50 dark:text-white/50">Status Akses:</span>
                <span id="portal-status-badge" class="font-bold px-2 py-0.5 rounded-full text-[10px] font-mono"></span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-black/50 dark:text-white/50">Masa Berlaku Timer:</span>
                <span id="portal-expired-label" class="font-semibold text-black dark:text-white font-mono">-</span>
            </div>
            <div class="text-[11px] text-black/40 dark:text-white/40 pt-1 border-t border-black/5 dark:border-white/5 leading-relaxed">
                Tautan ini aman & hanya memuat data proyek pengapalan ini saja. Jika timer habis, tautan otomatis terkunci demi keamanan data kepabeanan.
            </div>
        </div>

        <!-- URL Link & Copy Button -->
        <div class="space-y-1.5">
            <label class="block text-[11px] font-semibold text-black/70 dark:text-white/70">Tautan Pelacakan Mandiri Klien</label>
            <div class="flex items-center gap-2">
                <input id="portal-url-input" type="text" readonly 
                       placeholder="Belum ada link, silakan klik 'Generate Link Baru'"
                       class="flex-1 h-9 px-3 rounded-[9px] bg-black/[0.03] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[12px] font-mono text-black dark:text-white select-all">
                <button type="button" onclick="copyPortalUrl()" class="btn-apple-filled !h-9 !px-3 text-[12px]">
                    <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                    <span id="copy-btn-text">Salin</span>
                </button>
            </div>
        </div>

        <!-- Action Quick Share (WhatsApp & Open Tab) -->
        <div class="grid grid-cols-2 gap-2.5 pt-1">
            <a id="portal-wa-link" href="#" target="_blank" class="flex items-center justify-center gap-2 h-9 rounded-[10px] bg-[#34C759] hover:bg-[#2EB84F] text-white text-[12px] font-semibold transition-all">
                <i data-lucide="message-circle" class="w-4 h-4"></i>
                <span>Kirim WhatsApp</span>
            </a>
            <a id="portal-preview-link" href="#" target="_blank" class="flex items-center justify-center gap-2 h-9 rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.1] text-black dark:text-white text-[12px] font-semibold transition-all">
                <i data-lucide="external-link" class="w-4 h-4"></i>
                <span>Buka Pratinjau</span>
            </a>
        </div>

        <!-- Form Generate Token Baru & Atur Durasi Timer -->
        <div class="border-t border-black/5 dark:border-white/5 pt-3.5 space-y-2">
            <div class="text-[12px] font-bold text-black dark:text-white flex items-center gap-1.5">
                <i data-lucide="clock" class="w-3.5 h-3.5 text-[#0284c7]"></i>
                <span>Atur Timer Kedaluwarsa & Terbitkan Link Baru</span>
            </div>
            <p class="text-[11px] text-black/50 dark:text-white/50">Pilih durasi masa aktif baru. Tautan lama otomatis kedaluwarsa digantikan tautan ini:</p>
            
            <form id="form-generate-portal" method="POST" action="">
                @csrf
                <div class="flex items-center gap-2">
                    <select name="duration_days" id="portal-duration-select" class="flex-1 h-9 px-3 rounded-[9px] bg-black/[0.03] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[12px] text-black dark:text-white">
                        <option value="1">24 Jam (1 Hari)</option>
                        <option value="3">3 Hari</option>
                        <option value="7" selected>7 Hari (Standar)</option>
                        <option value="14">14 Hari (2 Minggu)</option>
                        <option value="30">30 Hari (1 Bulan)</option>
                    </select>
                    <button type="submit" class="btn-apple-gray !h-9 !px-3 text-[12px] font-semibold hover:!bg-[#0284c7] hover:!text-white shrink-0">
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                        <span>Generate Link</span>
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

<script>
    function openPortalModal(shipmentId, projectNo, clientName, portalUrl, portalStatus, expiresAt, durationDays, clientPhone, blNumber) {
        document.getElementById('modal-portal-share').classList.remove('hidden');
        document.getElementById('portal-project-label').innerText = projectNo + ' • ' + clientName;
        document.getElementById('portal-url-input').value = portalUrl || '';
        document.getElementById('form-generate-portal').action = '/core/document/' + shipmentId + '/generate-portal';
        document.getElementById('portal-duration-select').value = durationDays || '7';

        var statusBadge = document.getElementById('portal-status-badge');
        var expiredLabel = document.getElementById('portal-expired-label');

        if (portalStatus === 'ACTIVE' && portalUrl) {
            statusBadge.className = 'font-bold px-2 py-0.5 rounded-full text-[10px] font-mono bg-[#34C759]/15 text-[#248A3D]';
            statusBadge.innerText = 'AKTIF';
            expiredLabel.innerText = expiresAt ? expiresAt + ' WIB' : 'Aktif';
        } else if (portalStatus === 'EXPIRED') {
            statusBadge.className = 'font-bold px-2 py-0.5 rounded-full text-[10px] font-mono bg-[#FF3B30]/15 text-[#C41E17]';
            statusBadge.innerText = 'KEDALUWARSA';
            expiredLabel.innerText = expiresAt ? 'Berakhir pada ' + expiresAt + ' WIB' : 'Kedaluwarsa';
        } else {
            statusBadge.className = 'font-bold px-2 py-0.5 rounded-full text-[10px] font-mono bg-black/10 text-black/60';
            statusBadge.innerText = 'BELUM DIBUAT';
            expiredLabel.innerText = 'Klik Generate Link di bawah untuk membuat tautan.';
        }

        // WhatsApp Share Link
        var waPhone = clientPhone ? clientPhone.replace(/[^0-9]/g, '') : '';
        if (waPhone.startsWith('0')) waPhone = '62' + waPhone.substring(1);
        var companyName = '{{ addslashes(\App\Models\SystemSetting::get('company_profile')['name'] ?? 'PT AGUNG TRANS SOLUSINDO') }}';
        var waMessage = encodeURIComponent('Yth. Bapak/Ibu ' + clientName + ',\n\nBerikut tautan pelacakan pengapalan kargo proyek ' + projectNo + ' (B/L: ' + blNumber + ').\nAnda dapat memantau status kepabeanan & unduh berkas resmi di sini:\n' + portalUrl + '\n\nTautan ini aktif hingga ' + expiresAt + ' WIB.\nTerima kasih.\n\n' + companyName);
        
        var waUrl = waPhone ? 'https://wa.me/' + waPhone + '?text=' + waMessage : 'https://wa.me/?text=' + waMessage;
        document.getElementById('portal-wa-link').href = waUrl;
        document.getElementById('portal-preview-link').href = portalUrl || '#';

        if (window.lucide) lucide.createIcons();
    }

    function closePortalModal() {
        document.getElementById('modal-portal-share').classList.add('hidden');
    }

    function copyPortalUrl() {
        var input = document.getElementById('portal-url-input');
        if (!input.value) return;
        navigator.clipboard.writeText(input.value).then(function() {
            var btnText = document.getElementById('copy-btn-text');
            btnText.innerText = 'Tersalin!';
            setTimeout(function() { btnText.innerText = 'Salin'; }, 2000);
        });
    }
</script>

    <!-- 5. MODAL DETEKSI & PENCOCOKAN HS CODE DENGAN PRODUK MENGGUNAKAN AI -->
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
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Pencocokan pos tarif 8-digit BTKI, tarif BM/BK/PPN, dan persyaratan dokumen perizinan K/L.</p>
                    </div>
                </div>
                <button type="button" @click="aiModalOpen = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-white">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Form Input Komoditas Produk & Direction -->
            <div class="space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Arah Layanan Pabean:
                        </label>
                        <select x-model="direction" class="w-full px-3 py-1.5 rounded-xl border border-black/10 dark:border-white/10 bg-slate-50 dark:bg-white/5 font-medium text-slate-800 dark:text-slate-200 focus:outline-none">
                            <option value="import">Impor (BC 2.0 / PIB)</option>
                            <option value="export">Ekspor (BC 3.0 / PEB)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Model AI Engine:
                        </label>
                        <select x-model="aiModel" class="w-full px-3 py-1.5 rounded-xl border border-black/10 dark:border-white/10 bg-slate-50 dark:bg-white/5 font-mono text-[11px] font-semibold text-slate-800 dark:text-slate-200 focus:outline-none">
                            <option value="cx/gpt-6-astra">cx/gpt-6-astra (Rekomendasi Utama)</option>
                            <option value="cx/gpt-6-astra-xhigh">cx/gpt-6-astra-xhigh (Ultra Precision)</option>
                            <option value="cx/gpt-5.6-terra">cx/gpt-5.6-terra (Customs Specialized)</option>
                            <option value="cx/gpt-5.6-sol">cx/gpt-5.6-sol</option>
                            <option value="ag/gemini-3.8-flash-high">ag/gemini-3.8-flash-high</option>
                            <option value="ag/gemini-3.8-flash-medium">ag/gemini-3.8-flash-medium</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Deskripsi / Uraian Produk Komoditas:
                    </label>
                    <textarea x-model="productText" 
                              rows="3" 
                              placeholder="Ketik nama produk, spesifikasi bahan baku, merek, atau deskripsi barang (contoh: Laptop Asus ROG Intel i7 RAM 16GB, Kopi Arabika Gayo, Kaos Polos Katun Combed 30s...)" 
                              class="w-full px-3.5 py-2.5 rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-slate-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-[#0284c7]"></textarea>
                </div>

                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 pt-1">
                    <div class="flex items-center gap-1 text-[11px] text-slate-400">
                        <span>Contoh cepat:</span>
                        <button type="button" @click="productText = 'Laptop Gaming 15.6 inch Intel Core i7 SSD 512GB RAM 16GB'; direction = 'import';" class="text-[#0284c7] hover:underline font-semibold">Laptop (Lartas)</button>
                        <span>&bull;</span>
                        <button type="button" @click="productText = 'Kopi Arabika Roasting Kemasan Retail 250 Gram Asli Gayo'; direction = 'export';" class="text-[#0284c7] hover:underline font-semibold">Kopi (Ekspor)</button>
                        <span>&bull;</span>
                        <button type="button" @click="productText = 'Kain Tenun Sutra Motif Batik Tradisional'; direction = 'import';" class="text-[#0284c7] hover:underline font-semibold">Tekstil</button>
                    </div>

                    <button type="button" 
                            @click="detect()" 
                            :disabled="loading || !productText.trim()" 
                            class="px-4 py-2 rounded-xl bg-[#0284c7] hover:bg-[#0062CC] text-white font-bold text-xs inline-flex items-center gap-1.5 shadow-xs transition-all active:scale-[0.98] disabled:opacity-50 cursor-pointer">
                        <template x-if="loading">
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="animate-spin h-3.5 w-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span>Menganalisis BTKI & Lartas...</span>
                            </span>
                        </template>
                        <template x-if="!loading">
                            <span class="inline-flex items-center gap-1.5">
                                <span>Pindai & Cocokkan Pos Tarif AI</span>
                            </span>
                        </template>
                    </button>
                </div>
            </div>

            <!-- Pesan Error -->
            <template x-if="errorMsg">
                <div class="p-3 rounded-xl bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 text-xs">
                    <strong class="font-bold">Error:</strong> <span x-text="errorMsg"></span>
                </div>
            </template>

            <!-- Box Hasil Analisis AI -->
            <div x-show="result" x-cloak class="mt-4 p-4 rounded-2xl border border-black/10 dark:border-white/10 bg-slate-50/70 dark:bg-white/[0.02] space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Hasil Pencocokan Pos Tarif BTKI 2022</div>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="font-mono font-black text-lg text-indigo-700 dark:text-indigo-400" x-text="result?.code"></span>
                            <button type="button" @click="navigator.clipboard.writeText(result?.code); window.SwalToast.fire({ icon: 'success', title: 'Pos tarif disalin!' });" class="text-slate-400 hover:text-slate-700 text-xs cursor-pointer" title="Salin Pos Tarif">
                                📋
                            </button>
                        </div>
                        <div class="font-semibold text-slate-900 dark:text-white mt-1" x-text="result?.desc"></div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5" x-text="result?.legal"></div>
                    </div>

                    <!-- Badge Status Lartas Hasil AI -->
                    <div class="shrink-0 text-right">
                        <template x-if="result?.has_lartas">
                            <div>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-300 inline-flex items-center gap-1">
                                    <span>⚠️</span>
                                    <span>WAJIB LARTAS</span>
                                </span>
                                <div class="text-[10px] text-amber-700 dark:text-amber-400 font-semibold mt-1" x-text="result?.lartas"></div>
                            </div>
                        </template>
                        <template x-if="!result?.has_lartas">
                            <div>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300 inline-flex items-center gap-1">
                                    <span>✓</span>
                                    <span>BEBAS LARTAS</span>
                                </span>
                                <div class="text-[10px] text-emerald-700 dark:text-emerald-400 font-medium mt-1">Non-Lartas (Langsung Proses)</div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Tarif Bea Masuk & PPN/PPh -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-2 border-t border-black/[0.06] dark:border-white/[0.06] text-center">
                    <div class="p-2 rounded-xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10">
                        <span class="block text-[9px] text-slate-400 uppercase font-bold" x-text="direction === 'import' ? 'Bea Masuk (BM)' : 'Bea Keluar (BK)'"></span>
                        <span class="font-mono font-bold text-xs text-slate-900 dark:text-white" x-text="(result?.rates?.bm ?? result?.bm ?? result?.rates?.bk ?? result?.bk ?? 0) + '%'"></span>
                    </div>
                    <div class="p-2 rounded-xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10">
                        <span class="block text-[9px] text-slate-400 uppercase font-bold">PPN Impor</span>
                        <span class="font-mono font-bold text-xs text-slate-900 dark:text-white" x-text="(result?.rates?.ppn ?? result?.ppn ?? 11) + '%'"></span>
                    </div>
                    <div class="p-2 rounded-xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10">
                        <span class="block text-[9px] text-slate-400 uppercase font-bold">AI Model Used</span>
                        <span class="font-mono font-bold text-[10px] text-indigo-600 dark:text-indigo-400" x-text="result?.ai_model_used || aiModel"></span>
                    </div>
                    <div class="p-2 rounded-xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10">
                        <span class="block text-[9px] text-slate-400 uppercase font-bold">Confidence</span>
                        <span class="font-mono font-bold text-xs text-emerald-600" x-text="(result?.confidence ?? 98) + '%'"></span>
                    </div>
                </div>

                <div class="pt-2 flex justify-end gap-2 border-t border-black/[0.06] dark:border-white/[0.06]">
                    <template x-if="direction === 'import'">
                        <a :href="'{{ route('bc20.create') }}?pos_tarif=' + encodeURIComponent(result?.code) + '&uraian=' + encodeURIComponent(result?.desc)" 
                           class="btn-apple-filled text-xs py-1.5 px-3 inline-flex items-center gap-1">
                            <span>Buat PIB BC 2.0 dengan HS Ini &rarr;</span>
                        </a>
                    </template>
                    <template x-if="direction === 'export'">
                        <a :href="'{{ route('bc30.create') }}?pos_tarif=' + encodeURIComponent(result?.code) + '&uraian=' + encodeURIComponent(result?.desc)" 
                           class="btn-apple-filled text-xs py-1.5 px-3 inline-flex items-center gap-1">
                            <span>Buat PEB BC 3.0 dengan HS Ini &rarr;</span>
                        </a>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tarik & Impor Data CEISA 4.0 (Read-Only) -->
    @can('setting.manage')
    <div id="modal-sync-ceisa" x-data="{ tab: 'api' }" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl border border-black/10 dark:border-white/10 max-w-xl w-full p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                <div>
                    <h3 class="text-base font-bold text-black dark:text-white">Tarik &amp; Impor Data CEISA 4.0</h3>
                    <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">Tarik status real-time via Nomor Aju atau impor file ekspor Portal CEISA (.xlsx / .json / .pdf) untuk melengkapi item barang, importir, dan kontainer.</p>
                </div>
                <button type="button" onclick="document.getElementById('modal-sync-ceisa').classList.add('hidden')" class="text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white text-lg font-bold px-2">&times;</button>
            </div>

            <div class="grid grid-cols-2 gap-2 p-1 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] text-xs font-semibold">
                <button type="button" @click="tab = 'api'"
                        :class="tab === 'api' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-2xs' : 'text-black/55 dark:text-white/55'"
                        class="py-2 px-3 rounded-lg transition">
                    1. Tarik Status API (26 Digit)
                </button>
                <button type="button" @click="tab = 'import'"
                        :class="tab === 'import' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-2xs' : 'text-black/55 dark:text-white/55'"
                        class="py-2 px-3 rounded-lg transition">
                    2. Impor File CEISA (.xlsx/.json/.pdf)
                </button>
            </div>

            <form x-show="tab === 'api'" action="{{ route('config.sync-document') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="doc_sync_nomor_aju" class="block text-xs font-bold text-black/80 dark:text-white/80 mb-1.5">Nomor Aju (26 Digit) <span class="text-red-500">*</span></label>
                    <input type="text" id="doc_sync_nomor_aju" name="nomor_aju" required maxlength="30"
                           placeholder="Contoh: 000020AGO29220260929001562"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-black/15 dark:border-white/15 bg-white dark:bg-[#2C2C2E] text-sm font-mono text-black dark:text-white">
                </div>
                <label class="inline-flex items-center space-x-2 text-xs text-black/70 dark:text-white/70 cursor-pointer">
                    <input type="checkbox" name="auto_create_shipment" value="1" checked class="w-4 h-4 text-[#0284c7] rounded">
                    <span>Otomatis buat proyek baru di Tim Document jika Nomor Aju belum ada</span>
                </label>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-black/5 dark:border-white/10">
                    <button type="button" onclick="document.getElementById('modal-sync-ceisa').classList.add('hidden')" class="btn-apple-gray text-xs">Batal</button>
                    <button type="submit" class="btn-apple-filled text-xs">Tarik Status CEISA</button>
                </div>
            </form>

            <form x-show="tab === 'import'" action="{{ route('config.import-document') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label for="ceisa_export_file" class="block text-xs font-bold text-black/80 dark:text-white/80 mb-1.5">File Ekspor Portal CEISA 4.0 <span class="text-red-500">*</span></label>
                    <input type="file" id="ceisa_export_file" name="ceisa_file" required accept=".xlsx,.xls,.json,.txt,.pdf"
                           class="w-full px-3 py-2 rounded-xl border border-dashed border-black/20 dark:border-white/20 bg-black/[0.02] dark:bg-white/[0.03] text-xs text-black dark:text-white">
                    <p class="text-[11px] text-black/50 dark:text-white/50 mt-1">Mendukung Excel CEISA (sheet HEADER, ENTITAS, KONTAINER, BARANG), JSON CEISA 4.0, atau PDF Cetak PIB/PEB.</p>
                </div>
                <div>
                    <label for="import_nomor_aju" class="block text-xs font-bold text-black/80 dark:text-white/80 mb-1.5">Nomor Aju (Opsional jika sudah ada di file)</label>
                    <input type="text" id="import_nomor_aju" name="nomor_aju" maxlength="30"
                           placeholder="Otomatis dibaca dari file, atau isi 26 digit jika perlu"
                           class="w-full px-3.5 py-2 rounded-xl border border-black/15 dark:border-white/15 bg-white dark:bg-[#2C2C2E] text-sm font-mono text-black dark:text-white">
                </div>
                <label class="inline-flex items-center space-x-2 text-xs text-black/70 dark:text-white/70 cursor-pointer">
                    <input type="checkbox" name="sync_status_after" value="1" checked class="w-4 h-4 text-[#0284c7] rounded">
                    <span>Otomatis gabungkan dengan status &amp; respon real-time API CEISA (SPJM / SPPB / Gate Out)</span>
                </label>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-black/5 dark:border-white/10">
                    <button type="button" onclick="document.getElementById('modal-sync-ceisa').classList.add('hidden')" class="btn-apple-gray text-xs">Batal</button>
                    <button type="submit" class="btn-apple-filled text-xs">Impor &amp; Sinkronkan Dokumen</button>
                </div>
            </form>
        </div>
    </div>
    @endcan
</div>
@endsection
