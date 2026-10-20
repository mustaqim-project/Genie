@extends('layouts.app')

@section('title', 'Form Pengajuan BC 3.0 (PEB)')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-16" x-data="bc30FormManager()">
    <!-- Breadcrumb -->
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Tim Document', 'url' => route('core.document.index')],
        ['label' => 'BC 3.0 (Ekspor)', 'url' => route('bc30.index')],
        ['label' => 'Form Pengajuan Baru']
    ]" />

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold font-apple-display text-slate-900 dark:text-white tracking-tight">Formulir Pabean BC 3.0 (PEB)</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pemberitahuan Ekspor Barang (Host-to-Host CEISA 4.0 DJBC)</p>
        </div>
        <div class="flex items-center space-x-2">
            <span class="text-xs font-mono tabular-nums bg-slate-100 dark:bg-white/10 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-white/10 text-slate-700 dark:text-slate-300">
                Aju ID: {{ $defaultNomorAju }}
            </span>
        </div>
    </div>

    <!-- Mode Switcher Tabs (Apple Segmented Pill - Dual Workflow: Otomatis & Manual) -->
    <div class="inline-flex w-full p-1 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 mb-6 font-semibold text-[13px]">
        <button type="button" 
                @click="formMode = 'auto'" 
                :class="formMode === 'auto' ? 'bg-white dark:bg-[#2C2C2E] text-slate-900 dark:text-white shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-black dark:hover:text-white'"
                class="flex-1 py-2 px-4 rounded-[10px] transition-all flex items-center justify-center space-x-2 cursor-pointer">
            <i data-lucide="sparkles" class="w-4 h-4 text-[#007AFF]"></i>
            <span>Unggah Berkas & Ekstraksi Otomatis</span>
            <span class="px-2 py-0.5 bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] font-bold text-[10px] rounded-full">Praktis</span>
        </button>
        <button type="button" 
                @click="formMode = 'manual'" 
                :class="formMode === 'manual' ? 'bg-white dark:bg-[#2C2C2E] text-slate-900 dark:text-white shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-black dark:hover:text-white'"
                class="flex-1 py-2 px-4 rounded-[10px] transition-all flex items-center justify-center space-x-2 cursor-pointer">
            <i data-lucide="file-edit" class="w-4 h-4"></i>
            <span>Form Input Manual Lengkap</span>
        </button>
    </div>

    <!-- PANEL 1: WORKFLOW OTOMATIS (Ekstraksi Berkas & Proyek Shipment Ekspor) -->
    <div x-show="formMode === 'auto'" class="space-y-4 mb-6">
        
        <!-- Opsi A: Integrasi Proyek Shipment Ekspor -->
        <div class="bg-gradient-to-r from-amber-50 to-indigo-50 dark:from-amber-950/30 dark:to-indigo-950/30 rounded-2xl p-4 border border-amber-200/80 dark:border-amber-800/60 shadow-xs text-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center space-x-3">
                    <i data-lucide="zap" class="w-6 h-6 text-amber-500 flex-shrink-0"></i>
                    <div>
                        <strong class="font-bold text-slate-900 dark:text-white">Import Otomatis dari Proyek Shipment Ekspor</strong>
                        <p class="text-[11px] text-slate-600 dark:text-slate-300">Pilih proyek pengapalan ekspor aktif untuk mengisi data sarana pengangkut, valuasi FOB, dan pos tarif HS Code otomatis.</p>
                    </div>
                </div>
                <div class="w-full sm:w-72">
                    <select onchange="if(this.value) window.location.href='{{ route('bc30.create') }}?shipment_id=' + this.value; else window.location.href='{{ route('bc30.create') }}';" 
                            class="w-full px-3 py-2 rounded-xl border border-amber-300 dark:border-amber-700 bg-white dark:bg-[#2C2C2E] font-semibold text-slate-800 dark:text-white focus:ring-2 focus:ring-[#007AFF]">
                        <option value="">-- Pilih Proyek Ekspor --</option>
                        @foreach($shipments as $s)
                            <option value="{{ $s->id }}" {{ $selectedShipment?->id === $s->id ? 'selected' : '' }}>
                                {{ $s->shipment_number }} - BL: {{ $s->bl_number }} ({{ $s->client->name }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            @if($selectedShipment)
                <div class="mt-2.5 pt-2.5 border-t border-amber-200/60 dark:border-amber-800/40 flex items-center justify-between text-[11px] text-amber-900 dark:text-amber-300 font-medium">
                    <span>&check; Menggunakan data proyek: <strong>{{ $selectedShipment->shipment_number }}</strong> (Eksportir: {{ $selectedShipment->client->name }})</span>
                    <a href="{{ route('bc30.create') }}" class="text-rose-600 dark:text-rose-400 hover:underline">Reset Pilihan</a>
                </div>
            @endif
        </div>

        <!-- Opsi B: Unggah Berkas & Ekstraksi Otomatis AI (Persis Desain Modal Buka Proyek) -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-5 border border-slate-200/80 dark:border-white/10 shadow-xs space-y-4">
            <div>
                <div class="flex items-center justify-between">
                    <h3 class="text-[14px] font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="file-up" class="w-4 h-4 text-[#007AFF]"></i>
                        <span>Unggah Berkas Ekspor untuk Ekstraksi Otomatis</span>
                    </h3>
                    <span class="text-[11px] text-slate-400">PDF, Excel, JPG &bull; Maks 15MB</span>
                </div>
                <p class="text-[12px] text-slate-500 dark:text-slate-400 mt-0.5">Unggah berkas kargo ekspor (Booking/BL, Invoice, Packing List). Sistem mengekstrak informasi dan mengisi seluruh formulir pabean PEB di bawah secara otomatis.</p>
            </div>

            <!-- 3 Dropzone Upload Dokumen Utama -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                <!-- 1. Bill of Lading / Booking Sheet -->
                <div class="p-3.5 rounded-[14px] border-2 border-dashed border-[#007AFF]/30 hover:border-[#007AFF] bg-[#007AFF]/5 transition-colors flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-bold text-slate-900 dark:text-white flex items-center space-x-1.5">
                                <i data-lucide="ship" class="w-4 h-4 text-[#007AFF]"></i>
                                <span>Booking Pelayaran / BL</span>
                            </span>
                            <span class="text-[9px] px-1.5 py-0.5 rounded-[4px] bg-[#007AFF]/15 text-[#007AFF] font-bold">Wajib</span>
                        </div>
                        <p class="text-[11px] text-slate-500 mb-2">Diekstrak: No. Booking/BL, Kapal, Pelabuhan Muat/Tujuan.</p>
                    </div>
                    <div>
                        <input type="file" id="bc30-bl-file" accept=".pdf,.png,.jpg,.jpeg,.doc,.docx" class="hidden" @change="updateFileName('bl', $event)">
                        <button type="button" onclick="document.getElementById('bc30-bl-file').click()" 
                                class="w-full py-1.5 px-2.5 rounded-[8px] border border-[#007AFF]/30 bg-white dark:bg-[#2C2C2E] text-[#007AFF] font-semibold text-[11px] truncate flex items-center justify-center space-x-1.5 cursor-pointer">
                            <span x-text="fileNames.bl ? '✓ ' + fileNames.bl : '📂 Pilih File Booking/BL'">📂 Pilih File Booking/BL</span>
                        </button>
                    </div>
                </div>

                <!-- 2. Commercial Invoice Ekspor -->
                <div class="p-3.5 rounded-[14px] border-2 border-dashed border-[#5856D6]/30 hover:border-[#5856D6] bg-[#5856D6]/5 transition-colors flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-bold text-slate-900 dark:text-white flex items-center space-x-1.5">
                                <i data-lucide="receipt" class="w-4 h-4 text-[#5856D6]"></i>
                                <span>Commercial Invoice Ekspor</span>
                            </span>
                            <span class="text-[9px] px-1.5 py-0.5 rounded-[4px] bg-[#5856D6]/15 text-[#5856D6] font-bold">Wajib</span>
                        </div>
                        <p class="text-[11px] text-slate-500 mb-2">Diekstrak: Nilai FOB, Valuta, Pembeli (Buyer), Termin.</p>
                    </div>
                    <div>
                        <input type="file" id="bc30-inv-file" accept=".pdf,.xlsx,.xls,.png,.jpg,.jpeg,.doc,.docx" class="hidden" @change="updateFileName('invoice', $event)">
                        <button type="button" onclick="document.getElementById('bc30-inv-file').click()" 
                                class="w-full py-1.5 px-2.5 rounded-[8px] border border-[#5856D6]/30 bg-white dark:bg-[#2C2C2E] text-[#5856D6] font-semibold text-[11px] truncate flex items-center justify-center space-x-1.5 cursor-pointer">
                            <span x-text="fileNames.invoice ? '✓ ' + fileNames.invoice : '📂 Pilih Invoice Ekspor'">📂 Pilih Invoice Ekspor</span>
                        </button>
                    </div>
                </div>

                <!-- 3. Packing List (P/L) Ekspor -->
                <div class="p-3.5 rounded-[14px] border-2 border-dashed border-[#34C759]/30 hover:border-[#34C759] bg-[#34C759]/5 transition-colors flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-bold text-slate-900 dark:text-white flex items-center space-x-1.5">
                                <i data-lucide="package" class="w-4 h-4 text-[#34C759]"></i>
                                <span>Packing List (P/L) Ekspor</span>
                            </span>
                            <span class="text-[9px] px-1.5 py-0.5 rounded-[4px] bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] font-bold">Wajib</span>
                        </div>
                        <p class="text-[11px] text-slate-500 mb-2">Diekstrak: Rincian barang, kemasan, bobot bruto/netto, HS Code.</p>
                    </div>
                    <div>
                        <input type="file" id="bc30-pl-file" accept=".pdf,.xlsx,.xls,.png,.jpg,.jpeg,.doc,.docx" class="hidden" @change="updateFileName('pl', $event)">
                        <button type="button" onclick="document.getElementById('bc30-pl-file').click()" 
                                class="w-full py-1.5 px-2.5 rounded-[8px] border border-[#34C759]/30 bg-white dark:bg-[#2C2C2E] text-[#248A3D] dark:text-[#30D158] font-semibold text-[11px] truncate flex items-center justify-center space-x-1.5 cursor-pointer">
                            <span x-text="fileNames.pl ? '✓ ' + fileNames.pl : '📂 Pilih Packing List'">📂 Pilih Packing List</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Dokumen Pendukung Khusus (Accordion Opsional) -->
            <div x-data="{ openDocs: false }" class="border-t border-black/5 dark:border-white/5 pt-3 text-xs">
                <button type="button" @click="openDocs = !openDocs" class="text-[12px] font-semibold text-[#007AFF] hover:underline flex items-center space-x-1 cursor-pointer">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 transition-transform" :class="openDocs ? 'rotate-90' : ''"></i>
                    <span>Dokumen Pendukung Khusus Ekspor (COO, Lartas Ekspor, Izin Kemendag) - Opsional</span>
                </button>
                <div x-show="openDocs" x-collapse class="mt-3 p-3 rounded-[12px] bg-slate-50 dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 space-y-2">
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 text-[11px]">Unggah Berkas Pendukung Tambahan (PDF/JPG/PNG)</label>
                    <input type="file" id="bc30-other-file" multiple class="w-full text-xs text-slate-500 file:mr-3 file:py-1 file:px-2.5 file:rounded-[6px] file:border-0 file:text-[11px] file:font-semibold file:bg-[#007AFF]/10 file:text-[#007AFF]">
                </div>
            </div>

            <!-- Action Bar: Tombol Scan & Ekstraksi -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-3 border-t border-black/5 dark:border-white/5 bg-slate-50/50 dark:bg-white/[0.02] -mx-5 -mb-5 p-4 rounded-b-2xl">
                <div class="flex items-center space-x-2.5 text-xs">
                    <div class="w-8 h-8 rounded-[8px] bg-[#007AFF]/12 text-[#007AFF] flex items-center justify-center shrink-0">
                        <i data-lucide="cpu" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <span class="font-bold text-slate-900 dark:text-white block leading-tight">AI Document Parser & BTKI Classifier</span>
                        <span class="text-[11px] text-slate-400">Membaca teks dari berkas ekspor, mendeteksi kargo, dan mengklasifikasikan pos tarif kepabeanan.</span>
                    </div>
                </div>

                <button type="button" 
                        @click="scanAndExtractFiles()" 
                        :disabled="scanning" 
                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[12px] font-semibold transition-all shadow-xs active:scale-[0.98] disabled:opacity-50 cursor-pointer shrink-0">
                    <template x-if="scanning">
                        <span class="inline-flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>Memindai & Mengekstrak...</span>
                        </span>
                    </template>
                    <template x-if="!scanning">
                        <span class="inline-flex items-center gap-2">
                            <i data-lucide="sparkles" class="w-4 h-4"></i>
                            <span>Pindai & Ekstrak Otomatis ke Formulir</span>
                        </span>
                    </template>
                </button>
            </div>

            <!-- Banner Notifikasi Berhasil Ekstrak -->
            <div x-show="scanned" x-cloak class="p-3.5 rounded-[12px] bg-[#34C759]/12 border border-[#34C759]/25 text-[#248A3D] dark:text-[#30D158] text-xs flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <i data-lucide="check-circle" class="w-4 h-4 text-[#34C759] shrink-0"></i>
                    <span><strong>Berkas kargo berhasil diekstrak!</strong> Seluruh seksi formulir pabean PEB di bawah telah terisi otomatis. Anda dapat meninjau, mengoreksi, atau menambahkan item secara manual sebelum submit ke CEISA 4.0.</span>
                </div>
                <button type="button" @click="scanned = false" class="text-slate-400 hover:text-slate-600 ml-2">&times;</button>
            </div>

        </div>
    </div>

    <!-- PANEL 2: NOTIFIKASI JIKA DALAM MODE MANUAL -->
    <div x-show="formMode === 'manual'" class="p-3.5 rounded-xl bg-slate-100 dark:bg-white/[0.04] border border-black/5 dark:border-white/10 text-xs text-slate-600 dark:text-slate-300 flex items-center gap-2.5 mb-6">
        <i data-lucide="info" class="w-4 h-4 text-[#007AFF] shrink-0"></i>
        <span><strong>Mode Input Manual Aktif</strong> — Anda dapat mengisi atau merevisi seluruh 10 seksi formulir kepabeanan BC 3.0 (PEB) di bawah secara mandiri.</span>
    </div>

    <form action="{{ route('bc30.store') }}" method="POST" id="bc30Form" class="space-y-6">
        @csrf
        @if($selectedShipment)
            <input type="hidden" name="shipment_id" value="{{ $selectedShipment->id }}">
        @endif

        <!-- 1. Header & Kantor Pabean -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-6 border border-slate-200/80 dark:border-white/10 shadow-xs">
            <div class="flex items-center space-x-2 text-slate-800 dark:text-white font-bold text-sm mb-4 pb-2 border-b border-slate-100 dark:border-white/10">
                <span class="w-6 h-6 rounded-lg bg-indigo-100 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-400 flex items-center justify-center text-xs font-bold">1</span>
                <span>Kantor Pabean Muat & Kategori Ekspor</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Kantor Pabean Pemuatan Ekspor</label>
                    <select name="kode_kantor" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white focus:ring-2 focus:ring-[#007AFF]">
                        @foreach($kantorList as $code => $name)
                            <option value="{{ $code }}" {{ $code === '040300' ? 'selected' : '' }}>{{ $code }} - {{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Kategori Ekspor</label>
                    <input type="text" value="1 - Ekspor Umum / Biasa" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 text-slate-500 dark:text-slate-400" readonly>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Cara Perdagangan</label>
                    <input type="text" value="1 - Biasa (Tanpa Imbal Dagang)" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 text-slate-500 dark:text-slate-400" readonly>
                </div>
            </div>
        </div>

        <!-- 2. Data Entitas Stakeholder -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-6 border border-slate-200/80 dark:border-white/10 shadow-xs">
            <div class="flex items-center space-x-2 text-slate-800 dark:text-white font-bold text-sm mb-4 pb-2 border-b border-slate-100 dark:border-white/10">
                <span class="w-6 h-6 rounded-lg bg-indigo-100 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-400 flex items-center justify-center text-xs font-bold">2</span>
                <span>Entitas Kepabeanan Ekspor</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs">
                <!-- Eksportir (Lokal) -->
                <div class="p-4 rounded-xl bg-slate-50/70 dark:bg-[#2C2C2E]/60 border border-slate-200 dark:border-white/10">
                    <span class="inline-block px-2 py-0.5 rounded bg-indigo-100 dark:bg-indigo-950/50 text-indigo-800 dark:text-indigo-300 font-bold text-[10px] mb-2">EKSPORTIR (KODE 2)</span>
                    <div class="space-y-2">
                        <div>
                            <label class="block text-slate-500 dark:text-slate-400 font-medium">Nama Perusahaan Eksportir</label>
                            <input type="text" value="{{ $selectedShipment?->client?->name ?? config('ceisa.company.name') }}" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#1C1C1E] font-semibold text-slate-800 dark:text-white" readonly>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-slate-500 dark:text-slate-400 font-medium">NPWP 15/16 Digit</label>
                                <input type="text" value="{{ $selectedShipment?->client?->npwp ?? config('ceisa.company.npwp') }}" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#1C1C1E] font-mono tabular-nums text-slate-700 dark:text-slate-300" readonly>
                            </div>
                            <div>
                                <label class="block text-slate-500 dark:text-slate-400 font-medium">NIB</label>
                                <input type="text" value="{{ $selectedShipment?->client?->nib ?? config('ceisa.company.nib') }}" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#1C1C1E] font-mono tabular-nums text-slate-700 dark:text-slate-300" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pembeli Luar Negeri -->
                <div class="p-4 rounded-xl bg-slate-50/70 dark:bg-[#2C2C2E]/60 border border-slate-200 dark:border-white/10">
                    <span class="inline-block px-2 py-0.5 rounded bg-slate-200 dark:bg-white/10 text-slate-800 dark:text-slate-200 font-bold text-[10px] mb-2">PEMBELI LUAR NEGERI (KODE 8)</span>
                    <div class="space-y-2">
                        <div>
                            <label class="block text-slate-600 dark:text-slate-300 font-semibold mb-1">Nama Pembeli (Buyer) *</label>
                            <input type="text" name="buyer_nama" 
                                   value="{{ old('buyer_nama', 'GLOBAL COMMODITIES TRADING PTE LTD') }}" 
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white focus:ring-2 focus:ring-[#007AFF]" required>
                        </div>
                        <div>
                            <label class="block text-slate-600 dark:text-slate-300 font-semibold mb-1">Negara Tujuan Ekspor *</label>
                            <select name="buyer_negara" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white focus:ring-2 focus:ring-[#007AFF]" required>
                                <option value="SG" selected>SG - Singapura</option>
                                <option value="US">US - Amerika Serikat</option>
                                <option value="JP">JP - Jepang</option>
                                <option value="DE">DE - Jerman</option>
                                <option value="NL">NL - Belanda</option>
                                <option value="AU">AU - Australia</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Transportasi & Dokumen Pelengkap -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-6 border border-slate-200/80 dark:border-white/10 shadow-xs">
            <div class="flex items-center space-x-2 text-slate-800 dark:text-white font-bold text-sm mb-4 pb-2 border-b border-slate-100 dark:border-white/10">
                <span class="w-6 h-6 rounded-lg bg-indigo-100 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-400 flex items-center justify-center text-xs font-bold">3</span>
                <span>Pengangkutan Ekspor & Dokumen</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-xs mb-4">
                <div class="sm:col-span-2">
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Kapal / Sarana Pengangkut Ekspor *</label>
                    <input type="text" name="nama_sarana_pengangkut" 
                           value="{{ old('nama_sarana_pengangkut', $selectedShipment ? ($selectedShipment->vessel_name . ' VOY. ' . $selectedShipment->voyage_number) : 'EVER GIVEN VOY. 082W') }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white focus:ring-2 focus:ring-[#007AFF]" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Pelabuhan Muat *</label>
                    <input type="text" name="pelabuhan_muat" 
                           value="{{ old('pelabuhan_muat', $selectedShipment->port_of_discharge ?? 'IDTPP') }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Pelabuhan Tujuan Bongkar *</label>
                    <input type="text" name="pelabuhan_bongkar" 
                           value="{{ old('pelabuhan_bongkar', 'SGSIN') }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono" required>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs pt-3 border-t border-slate-100 dark:border-white/10">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nomor Ekspor Invoice *</label>
                    <input type="text" name="invoice_no" 
                           value="{{ old('invoice_no', $selectedShipment ? ('EXP-INV/' . $selectedShipment->bl_number) : ('INV-EXP-' . date('Ymd') . '-01')) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Invoice Ekspor *</label>
                    <input type="date" name="invoice_date" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nomor Booking Pelayaran / BL *</label>
                    <input type="text" name="bl_no" 
                           value="{{ old('bl_no', $selectedShipment->bl_number ?? 'BKG-EGLV-918230') }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono" required>
                </div>
            </div>
        </div>

        <!-- 3B. Kesiapan Barang (PKB / Stuffing) & Bank Devisa (DHE) -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-6 border border-slate-200/80 dark:border-white/10 shadow-xs">
            <div class="flex items-center space-x-2 text-slate-800 dark:text-white font-bold text-sm mb-4 pb-2 border-b border-slate-100 dark:border-white/10">
                <span class="w-6 h-6 rounded-lg bg-indigo-100 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-400 flex items-center justify-center text-xs font-bold">3B</span>
                <span>Kesiapan Barang Ekspor & Bank Devisa DHE (Mandatory CEISA 4.0)</span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs mb-3">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama PIC Barang *</label>
                    <input type="text" name="pic_pembuat" value="{{ old('pic_pembuat', 'BAMBANG SUTRISNO') }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-bold" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">No. HP / Telepon PIC *</label>
                    <input type="text" name="telepon_pembuat" value="{{ old('telepon_pembuat', '081298765432') }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono font-bold" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Lokasi Siap Periksa / Stuffing *</label>
                    <input type="text" name="lokasi_siap_periksa" value="{{ old('lokasi_siap_periksa', 'GUDANG EKSPORTIR CAKUNG') }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Kesiapan Periksa *</label>
                    <input type="date" name="tanggal_periksa" value="{{ old('tanggal_periksa', date('Y-m-d')) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white" required>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs pt-3 border-t border-slate-100 dark:border-white/10">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Bank Devisa Hasil Ekspor (DHE) *</label>
                    <input type="text" name="bank_dhe" value="{{ old('bank_dhe', 'BANK MANDIRI (PERSERO) TBK') }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-bold" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Cara Penyerahan Barang (Incoterms)</label>
                    <input type="text" value="FOB - Free On Board" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 text-slate-500 dark:text-slate-400 font-mono" readonly>
                </div>
            </div>
        </div>

        <!-- 4. Valuasi Ekspor & Kurs KMK -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-6 border border-slate-200/80 dark:border-white/10 shadow-xs">
            <div class="flex items-center justify-between pb-2 mb-4 border-b border-slate-100 dark:border-white/10">
                <div class="flex items-center space-x-2 text-slate-800 dark:text-white font-bold text-sm">
                    <span class="w-6 h-6 rounded-lg bg-indigo-100 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-400 flex items-center justify-center text-xs font-bold">4</span>
                    <span>Valuasi Ekspor (FOB) & Kurs NDBM</span>
                </div>
                <div class="flex items-center space-x-2 text-[11px] text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-[#2C2C2E] px-2.5 py-1 rounded-lg border border-slate-200 dark:border-white/10">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>KMK: <strong id="kmkBadge" class="text-slate-800 dark:text-white">{{ $activeKurs['nomor_kmk'] ?? 'KMK-NDPBM' }}</strong></span>
                    <span class="text-slate-400 dark:text-white/20">|</span>
                    <span id="kmkPeriode" class="text-slate-500 dark:text-slate-400 font-mono">{{ $activeKurs['tanggal_mulai'] ?? '' }} s/d {{ $activeKurs['tanggal_selesai'] ?? '' }}</span>
                </div>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs mb-4">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Valuta Ekspor</label>
                    <select name="kode_valuta" id="kode_valuta" onchange="fetchKurs(this.value)" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono font-bold focus:ring-2 focus:ring-[#007AFF]">
                        <option value="USD" {{ ($selectedShipment->currency ?? 'USD') === 'USD' ? 'selected' : '' }}>USD (US Dollar)</option>
                        <option value="SGD" {{ ($selectedShipment->currency ?? '') === 'SGD' ? 'selected' : '' }}>SGD (Singapore Dollar)</option>
                        <option value="EUR" {{ ($selectedShipment->currency ?? '') === 'EUR' ? 'selected' : '' }}>EUR (Euro)</option>
                        <option value="JPY" {{ ($selectedShipment->currency ?? '') === 'JPY' ? 'selected' : '' }}>JPY (Japanese Yen)</option>
                    </select>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="font-semibold text-slate-700 dark:text-slate-300">Kurs KMK Rp *</label>
                        <button type="button" onclick="fetchKurs(document.getElementById('kode_valuta').value)" class="text-[10px] text-[#007AFF] hover:underline font-bold">Refresh Kurs</button>
                    </div>
                    <input type="number" step="0.01" name="ndbm_kurs" id="ndbm_kurs" 
                           value="{{ old('ndbm_kurs', $activeKurs['nilai_kurs'] ?? 15850.00) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] font-mono font-bold text-emerald-700 dark:text-emerald-400 tabular-nums" required oninput="calculateFob()">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Total Nilai FOB *</label>
                    <input type="number" step="0.01" name="fob" id="fob" 
                           value="{{ old('fob', $selectedShipment->total_cif ?? 48500.00) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] font-mono font-bold text-[#007AFF] tabular-nums" required oninput="calculateFob()">
                </div>
                <div class="p-3 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800/60">
                    <span class="block text-[10px] font-bold text-indigo-700 dark:text-indigo-400 uppercase">Valuasi Ekspor IDR</span>
                    <span id="fobIdrDisplay" class="font-mono font-black text-sm text-indigo-900 dark:text-indigo-200 tabular-nums">Rp {{ number_format(($selectedShipment->total_cif ?? 48500.00) * ($activeKurs['nilai_kurs'] ?? 15850.00), 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-2 gap-4 text-xs pt-3 border-t border-slate-100 dark:border-white/10">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Berat Bruto Ekspor (KG) *</label>
                    <input type="number" step="0.01" name="bruto" 
                           value="{{ old('bruto', $selectedShipment->total_bruto ?? 14200.00) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] font-mono text-slate-800 dark:text-white tabular-nums" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Berat Netto Ekspor (KG) *</label>
                    <input type="number" step="0.01" name="netto" 
                           value="{{ old('netto', $selectedShipment->total_netto ?? 13500.00) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] font-mono text-slate-800 dark:text-white tabular-nums" required>
                </div>
            </div>
        </div>

        <!-- 5. Rincian Barang Ekspor & Bea Keluar (BK) -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-6 border border-slate-200/80 dark:border-white/10 shadow-xs">
            <div class="flex items-center space-x-2 text-slate-800 dark:text-white font-bold text-sm mb-4 pb-2 border-b border-slate-100 dark:border-white/10">
                <span class="w-6 h-6 rounded-lg bg-indigo-100 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-400 flex items-center justify-center text-xs font-bold">5</span>
                <span>Komoditas Barang Ekspor & Tarif Bea Keluar (BK)</span>
            </div>

            @php
                $itemList = $selectedShipment && $selectedShipment->items->isNotEmpty() ? $selectedShipment->items : [
                    (object)[
                        'kode_hs' => '0901.11.10',
                        'uraian_barang' => 'INDONESIAN ARABICA COFFEE BEANS GRADE 1 SPECIALTY',
                        'jumlah_kemasan' => 200,
                        'jenis_kemasan' => 'BAG',
                        'nilai_fob' => 48500.00,
                        'tarif_bk' => 0.0,
                    ]
                ];
            @endphp

            @foreach($itemList as $idx => $item)
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-[#2C2C2E]/60 border border-slate-200 dark:border-white/10 mb-4">
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs mb-3">
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">HS Code Ekspor *</label>
                            <input type="text" name="barang[{{ $idx }}][hs_code]" 
                                   value="{{ str_replace('.', '', $item->kode_hs ?? '09011110') }}" 
                                   onchange="lookupHsRow(this, {{ $idx }})"
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white font-mono" required>
                            <div id="hs_badge_{{ $idx }}" class="hidden mt-1 p-1.5 rounded-lg text-[10px]"></div>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Uraian Komoditas Ekspor *</label>
                            <input type="text" name="barang[{{ $idx }}][uraian]" 
                                   value="{{ $item->uraian_barang ?? 'KOMODITAS EKSPOR' }}" 
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white" required>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Merek & Spesifikasi</label>
                            <input type="text" name="barang[{{ $idx }}][merk]" value="GAYO HIGHLAND GRADE 1" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Jumlah Kemasan *</label>
                            <input type="number" name="barang[{{ $idx }}][jumlah]" 
                                   value="{{ $item->jumlah_kemasan ?? 200 }}" 
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white font-mono tabular-nums" required>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Jenis Kemasan *</label>
                            <input type="text" name="barang[{{ $idx }}][kemasan]" 
                                   value="{{ $item->jenis_kemasan ?? 'BAG' }}" 
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white font-mono" required>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">FOB Valas *</label>
                            <input type="number" step="0.01" name="barang[{{ $idx }}][fob]" 
                                   value="{{ $item->nilai_fob ?? 48500.00 }}" 
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white font-mono tabular-nums" required>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tarif Bea Keluar (BK) %</label>
                            <input type="number" step="0.1" name="barang[{{ $idx }}][tarif_bk]" 
                                   value="{{ $item->tarif_bk ?? 0.0 }}" 
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white font-mono tabular-nums">
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- 6. Kontainer Ekspor -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-6 border border-slate-200/80 dark:border-white/10 shadow-xs">
            <div class="flex items-center space-x-2 text-slate-800 dark:text-white font-bold text-sm mb-4 pb-2 border-b border-slate-100 dark:border-white/10">
                <span class="w-6 h-6 rounded-lg bg-indigo-100 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-400 flex items-center justify-center text-xs font-bold">6</span>
                <span>Petikemas Ekspor (Kontainer FCL)</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nomor Kontainer Ekspor *</label>
                    <input type="text" name="nomor_kontainer" 
                           value="{{ old('nomor_kontainer', $selectedShipment->nomor_kontainer ?? 'EMCU9820192') }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono font-bold" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Ukuran Petikemas</label>
                    <select name="ukuran_kontainer" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono">
                        <option value="40" selected>40 Feet Standard / HC</option>
                        <option value="20">20 Feet Standard</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tipe Muat</label>
                    <input type="text" value="FCL - Full Container Load" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 text-slate-500 dark:text-slate-400 font-mono" readonly>
                </div>
            </div>
        </div>

        <!-- Tombol Submit & Action Bar -->
        <div class="flex items-center justify-between pt-4">
            <a href="{{ route('bc30.index') }}" class="btn-apple-gray">
                &larr; Batal & Kembali
            </a>
            <button type="submit" class="btn-apple-filled">
                <span>Simpan Dokumen BC 3.0 & Siapkan Payload</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </button>
        </div>
    </form>
</div>

<script>
    async function fetchKurs(valuta) {
        if (!valuta) return;
        const kursInput = document.getElementById('ndbm_kurs');
        const badge = document.getElementById('kmkBadge');
        const periode = document.getElementById('kmkPeriode');

        kursInput.classList.add('opacity-50');
        try {
            const res = await fetch(`/ceisa/kurs/${valuta}`);
            const data = await res.json();
            if (data && data.nilai_kurs) {
                kursInput.value = parseFloat(data.nilai_kurs).toFixed(2);
                if (badge) badge.innerText = data.nomor_kmk || 'KMK-NDPBM';
                if (periode) periode.innerText = (data.tanggal_mulai || '') + ' s/d ' + (data.tanggal_selesai || '');
                calculateFob();
            }
        } catch (e) {
            console.error('Gagal fetch kurs CEISA:', e);
        } finally {
            kursInput.classList.remove('opacity-50');
        }
    }

    function calculateFob() {
        const kurs = parseFloat(document.getElementById('ndbm_kurs').value) || 0;
        const fob = parseFloat(document.getElementById('fob').value) || 0;
        const fobIdr = fob * kurs;
        document.getElementById('fobIdrDisplay').innerText = 'Rp ' + Math.round(fobIdr).toLocaleString('id-ID');
    }

    async function lookupHsRow(input, idx) {
        const val = input.value.replace(/[^0-9]/g, '');
        if (!val || val.length < 4) return;
        const badge = document.getElementById(`hs_badge_${idx}`);
        try {
            const res = await fetch(`/ceisa/tarif-hs/${val}`);
            const data = await res.json();
            if (data && data.success) {
                const uraianInput = document.querySelector(`input[name="barang[${idx}][uraian]"]`);
                const bkInput = document.querySelector(`input[name="barang[${idx}][tarif_bk]"]`);

                if (uraianInput && (!uraianInput.value || uraianInput.value === 'KOMODITAS EKSPOR')) {
                    uraianInput.value = data.desc || '';
                }
                if (bkInput && data.bk !== undefined) {
                    bkInput.value = data.bk;
                }

                if (badge) {
                    badge.classList.remove('hidden');
                    if (data.has_lartas) {
                        badge.className = 'mt-1 p-1.5 rounded-lg bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300 text-[10px] font-medium';
                        badge.innerHTML = `⚠️ <strong>Lartas Ekspor:</strong> ${data.lartas}`;
                    } else {
                        badge.className = 'mt-1 p-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-[10px] font-medium';
                        badge.innerHTML = `✓ Bebas Lartas Ekspor`;
                    }
                }
            }
        } catch (e) {
            console.error('Error lookup HS row:', e);
        }
    }

    document.addEventListener('DOMContentLoaded', calculateFob);

    function bc30FormManager() {
        return {
            formMode: '{{ $selectedShipment ? "auto" : "auto" }}',
            scanning: false,
            scanned: false,
            fileNames: { bl: '', invoice: '', pl: '', other: '' },
            updateFileName(type, event) {
                const files = event.target.files;
                if (files && files[0]) {
                    this.fileNames[type] = files[0].name;
                }
            },
            async scanAndExtractFiles() {
                const blInput = document.getElementById('bc30-bl-file');
                const invInput = document.getElementById('bc30-inv-file');
                const plInput = document.getElementById('bc30-pl-file');

                if (!blInput?.files?.length && !invInput?.files?.length && !plInput?.files?.length) {
                    alert('Silakan pilih minimal 1 berkas dokumen (B/L, Commercial Invoice, atau Packing List) untuk dipindai.');
                    return;
                }

                this.scanning = true;

                const formData = new FormData();
                if (blInput?.files?.[0]) formData.append('bl_file', blInput.files[0]);
                if (invInput?.files?.[0]) formData.append('invoice_file', invInput.files[0]);
                if (plInput?.files?.[0]) formData.append('packing_list_file', plInput.files[0]);

                try {
                    const response = await fetch('{{ route('core.document.parse-documents') }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: formData
                    });
                    const res = await response.json();
                    if (res.status === 'success' && res.data) {
                        this.applyExtractedData(res.data);
                        this.scanned = true;
                        setTimeout(() => {
                            document.getElementById('bc30Form')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }, 250);
                    } else {
                        alert(res.message || 'Gagal memindai dokumen.');
                    }
                } catch (err) {
                    console.error(err);
                    alert('Terjadi kesalahan koneksi saat memindai dokumen.');
                } finally {
                    this.scanning = false;
                }
            },
            applyExtractedData(d) {
                if (d.vessel_name) {
                    const el = document.querySelector('[name="nama_sarana_pengangkut"]');
                    if (el) el.value = d.vessel_name + (d.voyage_number ? ' VOY. ' + d.voyage_number : '');
                }
                if (d.bl_number) {
                    const el = document.querySelector('[name="bl_no"]');
                    if (el) el.value = d.bl_number;
                }
                if (d.invoice_number) {
                    const el = document.querySelector('[name="invoice_no"]');
                    if (el) el.value = d.invoice_number;
                }
                if (d.invoice_date) {
                    const el = document.querySelector('[name="invoice_date"]');
                    if (el) el.value = d.invoice_date;
                }
                if (d.port_of_loading) {
                    const el = document.querySelector('[name="pelabuhan_muat"]');
                    if (el) el.value = d.port_of_loading;
                }
                if (d.port_of_discharge) {
                    const el = document.querySelector('[name="pelabuhan_bongkar"]');
                    if (el) el.value = d.port_of_discharge;
                }
                if (d.buyer_name) {
                    const el = document.querySelector('[name="buyer_nama"]');
                    if (el) el.value = d.buyer_name;
                }
                if (d.currency) {
                    const el = document.querySelector('[name="kode_valuta"]');
                    if (el) el.value = d.currency;
                }
                if (d.ndbm_kurs) {
                    const el = document.getElementById('ndbm_kurs');
                    if (el) el.value = parseFloat(d.ndbm_kurs).toFixed(2);
                }
                if (d.total_fob) {
                    const el = document.getElementById('fob');
                    if (el) el.value = parseFloat(d.total_fob).toFixed(2);
                }
                if (d.total_bruto) {
                    const el = document.querySelector('[name="bruto"]');
                    if (el) el.value = parseFloat(d.total_bruto).toFixed(2);
                }
                if (d.total_netto) {
                    const el = document.querySelector('[name="netto"]');
                    if (el) el.value = parseFloat(d.total_netto).toFixed(2);
                }

                // Populate HS code / barang jika ada
                if (d.items && d.items.length > 0) {
                    const firstItem = d.items[0];
                    const hsEl = document.querySelector('[name="barang[0][hs_code]"]');
                    const descEl = document.querySelector('[name="barang[0][uraian]"]');
                    const qtyEl = document.querySelector('[name="barang[0][jumlah]"]');
                    const fobEl = document.querySelector('[name="barang[0][fob]"]');
                    if (hsEl && firstItem.hs_code) {
                        hsEl.value = firstItem.hs_code.replace(/[^0-9]/g, '');
                        lookupHsRow(hsEl, 0);
                    }
                    if (descEl && firstItem.description) descEl.value = firstItem.description;
                    if (qtyEl && firstItem.qty) qtyEl.value = firstItem.qty;
                    if (fobEl && firstItem.cif_price) fobEl.value = parseFloat(firstItem.cif_price).toFixed(2);
                }

                if (typeof calculateFob === 'function') {
                    calculateFob();
                }
            }
        };
    }
</script>
@endsection
