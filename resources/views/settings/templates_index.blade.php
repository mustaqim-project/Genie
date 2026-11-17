@extends('layouts.app')

@section('title', 'Master Template & Dokumen Resmi (Email, Surat Jalan, Klausul)')

@section('content')
<div class="max-w-[1440px] mx-auto px-4 sm:px-6 space-y-6 pt-4 pb-28 lg:pb-12"
     x-data="{
         showCreateModal: false,
         showEditModal: false,
         showPreviewModal: false,
         activeTemplate: null,
         previewRendered: null,
         copyTag(tag) {
             navigator.clipboard.writeText(tag);
             if (window.toast) window.toast('Tag ' + tag + ' disalin!', 'success');
             else alert('Tag ' + tag + ' disalin ke clipboard!');
         },
         openEdit(tpl) {
             this.activeTemplate = JSON.parse(JSON.stringify(tpl));
             this.showEditModal = true;
         },
         openPreview(tplId) {
             fetch(`/settings/templates/${tplId}/preview`)
                 .then(res => res.json())
                 .then(data => {
                     this.activeTemplate = data.template;
                     this.previewRendered = data.rendered;
                     this.showPreviewModal = true;
                     if (window.lucide) setTimeout(() => window.lucide.createIcons(), 50);
                 });
         }
     }">

    <!-- Breadcrumb -->
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Pengaturan Sistem', 'url' => route('setting.index')],
        ['label' => 'Master Template & Dokumen']
    ]" />

    <!-- 1. HEADER BAR (macOS Sonoma Style) -->
    <header class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-md border border-slate-200/80 dark:border-white/10 p-5 sm:p-6 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                    <i data-lucide="layout-template" class="w-5 h-5"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl sm:text-2xl font-bold font-apple-display tracking-tight text-slate-900 dark:text-white">
                            Master Template &amp; Dokumen Operasional
                        </h1>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-950/50 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                            CRUD Engine
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Kelola susunan teks template email 4 divisi, format 4 Surat Jalan resmi, klausul kontrak, dan syarat pembayaran secara terpusat tanpa menyentuh kode program.
                    </p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('setting.index', ['tab' => 'mom_config']) }}" class="btn-apple-gray text-xs inline-flex items-center gap-1.5">
                <i data-lucide="settings" class="w-3.5 h-3.5"></i>
                <span>Konfigurasi Pengirim MoM</span>
            </a>
            <button type="button" @click="showCreateModal = true" class="btn-apple-filled !bg-indigo-600 hover:!bg-indigo-700 text-white text-xs inline-flex items-center gap-1.5 shadow-sm">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Tambah Template Baru</span>
            </button>
        </div>
    </header>

    @if(session('success'))
    <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200/80 dark:border-emerald-900/50 text-emerald-800 dark:text-emerald-300 text-xs flex items-center gap-3">
        <i data-lucide="check-circle" class="w-4 h-4 shrink-0 text-emerald-600"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200/80 dark:border-rose-900/50 text-rose-800 dark:text-rose-300 text-xs flex items-center gap-3">
        <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0 text-rose-600"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <!-- 2. BENTO STATS KPI CARDS -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
        <div class="p-4 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-xs">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total Template</span>
            <div class="text-2xl font-bold font-mono text-slate-900 dark:text-white mt-1">{{ $kpis['total'] }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Tersimpan di basis data</div>
        </div>
        <div class="p-4 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-xs">
            <span class="text-[11px] font-semibold text-sky-600 dark:text-sky-400 uppercase tracking-wider">Email 4 Divisi</span>
            <div class="text-2xl font-bold font-mono text-sky-600 dark:text-sky-400 mt-1">{{ $kpis['email'] }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Marketing, Finance, Dok, Traffic</div>
        </div>
        <div class="p-4 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-xs">
            <span class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Surat Jalan</span>
            <div class="text-2xl font-bold font-mono text-emerald-600 dark:text-emerald-400 mt-1">{{ $kpis['surat_jalan'] }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">FCL, LCL, Antar Pabrik, Empty</div>
        </div>
        <div class="p-4 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 shadow-xs">
            <span class="text-[11px] font-semibold text-purple-600 dark:text-purple-400 uppercase tracking-wider">Klausul Syarat &amp; Terms</span>
            <div class="text-2xl font-bold font-mono text-purple-600 dark:text-purple-400 mt-1">{{ $kpis['klausul'] }}</div>
            <div class="text-[10px] text-slate-400 mt-0.5">Klausul hukum penawaran &amp; faktur</div>
        </div>
    </div>

    <!-- 3. KOTAK INFORMASI: PANDUAN CARA EDIT TEMPLATE & DAFTAR TAG DINAMIS -->
    <details class="group rounded-2xl border border-indigo-200/80 dark:border-indigo-900/50 bg-indigo-50/50 dark:bg-indigo-950/20 p-4 text-xs transition-all">
        <summary class="font-bold text-indigo-900 dark:text-indigo-300 cursor-pointer flex items-center justify-between select-none">
            <span class="flex items-center gap-2">
                <i data-lucide="info" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                <span>Panduan Cara Edit Template &amp; Daftar Tag Placeholder Otomatis (Klik untuk Buka)</span>
            </span>
            <span class="text-xs text-indigo-500 group-open:rotate-180 transition-transform">▼</span>
        </summary>
        <div class="pt-3 space-y-3 text-slate-700 dark:text-slate-300">
            <p>
                Anda dapat menyunting teks template, mengganti kalimat pengantar, atau memasukkan variabel dinamis. Sistem akan secara otomatis mengganti setiap tag kurung kurawal <code class="px-1.5 py-0.5 rounded bg-white dark:bg-white/10 font-mono text-indigo-600 dark:text-indigo-400">{tag}</code> dengan data aktual dari proyek/invoice terkait saat proses cetak atau kirim email.
            </p>
            <div>
                <strong class="block text-[11px] uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">Klik tag di bawah untuk menyalin langsung:</strong>
                <div class="flex flex-wrap gap-1.5">
                    @foreach([
                        '{client_name}' => 'Nama Perusahaan Klien',
                        '{contact_person}' => 'Nama PIC / Kontak Klien',
                        '{quotation_no}' => 'Nomor Surat Penawaran',
                        '{invoice_no}' => 'Nomor Tagihan Invoice P19',
                        '{nomor_aju}' => 'Nomor Pengajuan Pabean (Aju)',
                        '{project_no}' => 'Nomor Job / Project Operasional',
                        '{bl_number}' => 'Nomor B/L atau Air Waybill (AWB)',
                        '{vessel_name}' => 'Nama Kapal / Sarana Pengangkut',
                        '{voyage_no}' => 'Nomor Voyage / Penerbangan',
                        '{pol}' => 'Pelabuhan Muat (Port of Loading)',
                        '{pod}' => 'Pelabuhan Bongkar (Port of Discharge)',
                        '{origin_port}' => 'Pelabuhan Asal',
                        '{destination_port}' => 'Pelabuhan Tujuan',
                        '{eta_date}' => 'Estimasi Waktu Tiba (ETA)',
                        '{total_amount}' => 'Total Nominal Biaya / Tagihan',
                        '{due_date}' => 'Batas Tanggal Jatuh Tempo',
                        '{valid_until}' => 'Masa Berlaku Penawaran',
                        '{tps_location}' => 'Lokasi Terminal TPS / Dermaga',
                        '{container_no}' => 'Nomor Kontainer',
                        '{seal_no}' => 'Nomor Segel Pelayaran / BC',
                        '{truck_plate}' => 'Nomor Polisi Truk Armada',
                        '{driver_name}' => 'Nama Pengemudi / Sopir',
                        '{bank_info}' => 'Rincian Rekening Bank Resmi',
                        '{custom_notes}' => 'Catatan Tambahan Pengguna',
                    ] as $tag => $desc)
                        <button type="button" 
                                @click="copyTag('{{ $tag }}')" 
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#2C2C2E] hover:border-indigo-400 text-[11px] font-mono hover:text-indigo-600 transition-all cursor-pointer shadow-2xs"
                                title="{{ $desc }}">
                            <span>{{ $tag }}</span>
                            <span class="text-[9px] text-slate-400 font-sans">({{ $desc }})</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </details>

    <!-- 4. FILTER TABS & SEARCH -->
    <div class="rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Category Filter Tabs -->
        <div class="flex flex-wrap items-center gap-1.5 text-xs font-semibold">
            <a href="{{ route('settings.templates.index', ['category' => 'all', 'division' => $division, 'search' => $search]) }}"
               class="px-3 py-1.5 rounded-xl transition-all {{ $category === 'all' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-white/10' }}">
                Semua Kategori ({{ $kpis['total'] }})
            </a>
            <a href="{{ route('settings.templates.index', ['category' => 'EMAIL', 'division' => $division, 'search' => $search]) }}"
               class="px-3 py-1.5 rounded-xl transition-all flex items-center gap-1.5 {{ $category === 'EMAIL' ? 'bg-sky-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-white/10' }}">
                <i data-lucide="mail" class="w-3.5 h-3.5"></i>
                <span>Email Divisi ({{ $kpis['email'] }})</span>
            </a>
            <a href="{{ route('settings.templates.index', ['category' => 'SURAT_JALAN', 'division' => $division, 'search' => $search]) }}"
               class="px-3 py-1.5 rounded-xl transition-all flex items-center gap-1.5 {{ $category === 'SURAT_JALAN' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-white/10' }}">
                <i data-lucide="truck" class="w-3.5 h-3.5"></i>
                <span>Surat Jalan ({{ $kpis['surat_jalan'] }})</span>
            </a>
            <a href="{{ route('settings.templates.index', ['category' => 'TERMS_KLAUSUL', 'division' => $division, 'search' => $search]) }}"
               class="px-3 py-1.5 rounded-xl transition-all flex items-center gap-1.5 {{ $category === 'TERMS_KLAUSUL' ? 'bg-purple-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-white/10' }}">
                <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                <span>Klausul Kontrak ({{ $kpis['klausul'] }})</span>
            </a>
        </div>

        <!-- Search Bar -->
        <form action="{{ route('settings.templates.index') }}" method="GET" class="relative max-w-xs w-full">
            <input type="hidden" name="category" value="{{ $category }}">
            <input type="hidden" name="division" value="{{ $division }}">
            <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama atau isi template..."
                   class="w-full pl-9 pr-4 py-1.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </form>
    </div>

    <!-- 5. TEMPLATES TABLE & CRUD ACTIONS -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl border border-black/5 dark:border-white/5 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-white/[0.02] border-b border-black/5 dark:border-white/5 text-slate-500 font-semibold">
                    <tr>
                        <th class="py-3.5 px-4">Nama Template &amp; Kode</th>
                        <th class="py-3.5 px-4">Kategori &amp; Divisi</th>
                        <th class="py-3.5 px-4">Subjek / Klausul Ringkas</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi Manajemen (CRUD)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5">
                    @forelse($templates as $tpl)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <span>{{ $tpl->name }}</span>
                                    @if($tpl->is_default)
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-300">
                                            Default
                                        </span>
                                    @endif
                                    @if($tpl->is_system)
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-mono bg-slate-100 text-slate-600 dark:bg-white/10 dark:text-slate-300" title="Template Inti Sistem">
                                            System
                                        </span>
                                    @endif
                                </div>
                                <div class="font-mono text-[10px] text-slate-400 mt-0.5">{{ $tpl->code }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-1.5">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono
                                        {{ $tpl->category === 'EMAIL' ? 'bg-sky-50 text-sky-700 border border-sky-200' : ($tpl->category === 'SURAT_JALAN' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-purple-50 text-purple-700 border border-purple-200') }}">
                                        {{ $categories[$tpl->category] ?? $tpl->category }}
                                    </span>
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                                    Divisi: <strong class="text-slate-700 dark:text-slate-200">{{ $divisions[$tpl->division] ?? ($tpl->division ?: 'Semua') }}</strong>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 max-w-md">
                                @if($tpl->subject)
                                    <div class="font-medium text-slate-800 dark:text-slate-200 truncate">{{ $tpl->subject }}</div>
                                @endif
                                <div class="text-[11px] text-slate-400 line-clamp-2 mt-0.5">
                                    {{ Str::limit($tpl->body ?: $tpl->terms_and_conditions, 120) }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $tpl->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $tpl->is_active ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                    <span>{{ $tpl->is_active ? 'Aktif' : 'Non-Aktif' }}</span>
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1 whitespace-nowrap">
                                <!-- Preview Live -->
                                <button type="button" 
                                        @click="openPreview('{{ $tpl->id }}')" 
                                        class="btn-apple-gray !py-1 !px-2 text-indigo-600 dark:text-indigo-400" 
                                        title="Pratinjau Live Template">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    <span>Preview</span>
                                </button>

                                <!-- Edit -->
                                <button type="button" 
                                        @click="openEdit({{ json_encode($tpl) }})" 
                                        class="btn-apple-gray !py-1 !px-2 text-sky-600 dark:text-sky-400" 
                                        title="Edit Teks & Klausul Template">
                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    <span>Edit</span>
                                </button>

                                <!-- Set Default -->
                                @if(!$tpl->is_default)
                                <form action="{{ route('settings.templates.set-default', $tpl->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="btn-apple-gray !py-1 !px-2 text-emerald-600" title="Tetapkan sebagai Format Default">
                                        <i data-lucide="star" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                                @endif

                                <!-- Duplicate -->
                                <form action="{{ route('settings.templates.duplicate', $tpl->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="btn-apple-gray !py-1 !px-2 text-slate-500" title="Duplikasi Template">
                                        <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>

                                <!-- Delete (non-system only) -->
                                @if(!$tpl->is_system)
                                <form action="{{ route('settings.templates.destroy', $tpl->id) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Hapus template {{ addslashes($tpl->name) }} secara permanen?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-apple-gray !py-1 !px-2 text-rose-600" title="Hapus Template Kustom">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                <i data-lucide="file-question" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
                                <p class="font-semibold">Tidak ada template yang cocok dengan filter pencarian.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-black/5 dark:border-white/5">
            {{ $templates->links() }}
        </div>
    </div>

    <!-- 6. MODAL CREATE TEMPLATE BARU -->
    <div x-show="showCreateModal" class="fixed inset-0 z-[200] overflow-y-auto flex items-end sm:items-center justify-center p-0 sm:p-4" role="dialog" aria-modal="true" x-cloak>
        <div class="fixed inset-0 bg-black/60 backdrop-blur-md" @click="showCreateModal = false"></div>
        <div class="relative bg-white dark:bg-[#1C1C1E] rounded-t-[24px] sm:rounded-2xl border border-black/10 dark:border-white/10 shadow-2xl max-w-2xl w-full p-6 text-xs space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-indigo-500/15 text-indigo-600 flex items-center justify-center font-bold">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Tambah Template Dokumen / Email Baru</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Buat format penawaran, surat jalan, atau email divisi kustom</p>
                    </div>
                </div>
                <button type="button" @click="showCreateModal = false" class="btn-apple-gray !p-1.5 rounded-full"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>

            <form action="{{ route('settings.templates.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Template <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required placeholder="Contoh: Email Penawaran Promo Ramadhan" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/10 dark:bg-[#2C2C2E] text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Kode Unik (Opsional)</label>
                        <input type="text" name="code" placeholder="Auto-generate jika kosong" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/10 dark:bg-[#2C2C2E] font-mono text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Kategori Dokumen <span class="text-rose-500">*</span></label>
                        <select name="category" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/10 dark:bg-[#2C2C2E] text-xs">
                            @foreach($categories as $key => $lbl)
                                <option value="{{ $key }}">{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Divisi Penanggung Jawab</label>
                        <select name="division" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/10 dark:bg-[#2C2C2E] text-xs">
                            <option value="">-- Berlaku untuk Semua Divisi --</option>
                            @foreach($divisions as $key => $lbl)
                                <option value="{{ $key }}">{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Subjek / Judul Surat</label>
                    <input type="text" name="subject" placeholder="Contoh: Penawaran Khusus - {client_name}" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/10 dark:bg-[#2C2C2E] text-xs">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Isi Teks Template / Body Surat (Mendukung Placeholder)</label>
                    <textarea name="body" rows="6" placeholder="Ketik susunan teks pesan di sini. Gunakan tag seperti {client_name}, {vessel_name}, {eta_date}..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-white/10 dark:bg-[#2C2C2E] font-mono text-xs"></textarea>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Klausul Syarat &amp; Ketentuan Hukum (Untuk Surat Jalan / Faktur)</label>
                    <textarea name="terms_and_conditions" rows="3" placeholder="Contoh: 1. Segel wajib utuh. 2. Penerima wajib membubuhkan stempel..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-white/10 dark:bg-[#2C2C2E] text-xs"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Signee / PIC Pengesah</label>
                        <input type="text" name="signee_name" placeholder="Misal: Bambang Wijaya (Manager Ops)" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/10 dark:bg-[#2C2C2E] text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">No. Kontak Hotline Darurat</label>
                        <input type="text" name="emergency_phone" placeholder="+62 811-..." class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/10 dark:bg-[#2C2C2E] font-mono text-xs">
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" name="is_default" value="1" id="create_is_default" class="rounded text-indigo-600">
                    <label for="create_is_default" class="font-semibold text-slate-700 dark:text-slate-300">Jadikan template default untuk kategori ini</label>
                </div>

                <div class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-end gap-2">
                    <button type="button" @click="showCreateModal = false" class="btn-apple-gray">Batal</button>
                    <button type="submit" class="btn-apple-filled !bg-indigo-600 hover:!bg-indigo-700 text-white">Simpan Template Baru</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 7. MODAL EDIT TEMPLATE -->
    <div x-show="showEditModal" class="fixed inset-0 z-[200] overflow-y-auto flex items-end sm:items-center justify-center p-0 sm:p-4" role="dialog" aria-modal="true" x-cloak>
        <div class="fixed inset-0 bg-black/60 backdrop-blur-md" @click="showEditModal = false"></div>
        <div class="relative bg-white dark:bg-[#1C1C1E] rounded-t-[24px] sm:rounded-2xl border border-black/10 dark:border-white/10 shadow-2xl max-w-2xl w-full p-6 text-xs space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-sky-500/15 text-sky-600 flex items-center justify-center font-bold">
                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Edit Template: <span x-text="activeTemplate?.name"></span></h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Perbarui kata-kata, susunan variabel dinamis, dan klausul syarat</p>
                    </div>
                </div>
                <button type="button" @click="showEditModal = false" class="btn-apple-gray !p-1.5 rounded-full"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>

            <form :action="'/settings/templates/' + activeTemplate?.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Template <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" x-model="activeTemplate.name" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/10 dark:bg-[#2C2C2E] text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Kode Unik</label>
                        <input type="text" name="code" x-model="activeTemplate.code" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/10 dark:bg-[#2C2C2E] font-mono text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Kategori Dokumen</label>
                        <select name="category" x-model="activeTemplate.category" required class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/10 dark:bg-[#2C2C2E] text-xs">
                            @foreach($categories as $key => $lbl)
                                <option value="{{ $key }}">{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Divisi Penanggung Jawab</label>
                        <select name="division" x-model="activeTemplate.division" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/10 dark:bg-[#2C2C2E] text-xs">
                            <option value="">-- Berlaku untuk Semua Divisi --</option>
                            @foreach($divisions as $key => $lbl)
                                <option value="{{ $key }}">{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Subjek / Judul Surat</label>
                    <input type="text" name="subject" x-model="activeTemplate.subject" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/10 dark:bg-[#2C2C2E] text-xs">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Isi Teks Template / Body Surat (Mendukung Placeholder)</label>
                    <textarea name="body" x-model="activeTemplate.body" rows="6" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-white/10 dark:bg-[#2C2C2E] font-mono text-xs"></textarea>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Klausul Syarat &amp; Ketentuan Hukum</label>
                    <textarea name="terms_and_conditions" x-model="activeTemplate.terms_and_conditions" rows="3" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-white/10 dark:bg-[#2C2C2E] text-xs"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Signee / PIC Pengesah</label>
                        <input type="text" name="signee_name" x-model="activeTemplate.signee_name" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/10 dark:bg-[#2C2C2E] text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">No. Kontak Hotline Darurat</label>
                        <input type="text" name="emergency_phone" x-model="activeTemplate.emergency_phone" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/10 dark:bg-[#2C2C2E] font-mono text-xs">
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-2">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_default" value="1" :checked="activeTemplate?.is_default" id="edit_is_default" class="rounded text-indigo-600">
                        <label for="edit_is_default" class="font-semibold text-slate-700 dark:text-slate-300">Format Default</label>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" :checked="activeTemplate?.is_active" id="edit_is_active" class="rounded text-indigo-600">
                        <label for="edit_is_active" class="font-semibold text-slate-700 dark:text-slate-300">Status Aktif</label>
                    </div>
                </div>

                <div class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-end gap-2">
                    <button type="button" @click="showEditModal = false" class="btn-apple-gray">Batal</button>
                    <button type="submit" class="btn-apple-filled !bg-sky-600 hover:!bg-sky-700 text-white">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 8. MODAL PREVIEW LIVE DENGAN SAMPLE DATA -->
    <div x-show="showPreviewModal" class="fixed inset-0 z-[200] overflow-y-auto flex items-end sm:items-center justify-center p-0 sm:p-4" role="dialog" aria-modal="true" x-cloak>
        <div class="fixed inset-0 bg-black/60 backdrop-blur-md" @click="showPreviewModal = false"></div>
        <div class="relative bg-white dark:bg-[#1C1C1E] rounded-t-[24px] sm:rounded-2xl border border-black/10 dark:border-white/10 shadow-2xl max-w-2xl w-full p-6 text-xs space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-purple-500/15 text-purple-600 flex items-center justify-center font-bold">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Live Render Preview: <span x-text="activeTemplate?.name"></span></h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Hasil penggantian tag placeholder dengan data sampel real-time</p>
                    </div>
                </div>
                <button type="button" @click="showPreviewModal = false" class="btn-apple-gray !p-1.5 rounded-full"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>

            <div class="space-y-3">
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Subjek Email / Judul Dokumen:</span>
                    <div class="text-xs font-bold text-slate-900 dark:text-white mt-1" x-text="previewRendered?.subject || '-'"></div>
                </div>

                <div class="p-4 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 font-sans whitespace-pre-wrap text-slate-800 dark:text-slate-200 leading-relaxed text-xs" x-text="previewRendered?.body || 'Tidak ada body teks.'"></div>

                <template x-if="previewRendered?.terms_and_conditions">
                    <div class="p-3 rounded-xl bg-amber-50/60 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/40 text-[11px] text-amber-950 dark:text-amber-300">
                        <strong class="block mb-1 font-bold">Klausul Syarat &amp; Ketentuan Sah:</strong>
                        <div class="whitespace-pre-wrap" x-text="previewRendered.terms_and_conditions"></div>
                    </div>
                </template>

                <div class="grid grid-cols-2 gap-2 text-[11px] p-3 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10">
                    <div>
                        <span class="text-slate-400 block text-[10px]">Penanggung Jawab (Signee):</span>
                        <strong class="text-slate-800 dark:text-white" x-text="previewRendered?.signee_name || '-'"></strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px]">Kontak Dispatch Darurat:</span>
                        <strong class="text-slate-800 dark:text-white font-mono" x-text="previewRendered?.emergency_phone || '-'"></strong>
                    </div>
                </div>
            </div>

            <div class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-end">
                <button type="button" @click="showPreviewModal = false" class="btn-apple-filled !bg-slate-900 text-white">Tutup Preview</button>
            </div>
        </div>
    </div>

</div>
@endsection
