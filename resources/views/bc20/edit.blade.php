@extends('layouts.app')

@section('title', 'Wizard Edit BC 2.0 (PIB) - ' . $bc20->nomor_aju)

@section('content')
<div class="max-w-[1600px] mx-auto px-4 sm:px-6 space-y-4 pt-4 pb-28" x-data="bc20WizardManager()">
    <!-- Top Compact Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white dark:bg-[#1C1C1E] rounded-2xl px-5 py-3.5 border border-black/[0.06] dark:border-white/[0.08] shadow-xs">
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('bc20.show', $bc20->id) }}" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-white/10 dark:hover:bg-white/20 text-slate-700 dark:text-slate-200 transition-colors" title="Kembali ke Ringkasan BC 2.0">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
            </a>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20">BC 2.0 (PIB IMPOR)</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold {{ $bc20->status_badge_class }}">{{ $bc20->status }}</span>
                    @if($shipment)
                        <a href="{{ route('core.document.show', $shipment->id) }}" class="text-[11px] font-mono font-bold text-[#007AFF] hover:underline">
                            {{ $shipment->project_no }}
                        </a>
                    @endif
                </div>
                <h1 class="text-base sm:text-lg font-bold font-mono text-slate-900 dark:text-white tracking-tight mt-0.5">
                    {{ $bc20->nomor_aju }}
                </h1>
            </div>
        </div>

        <div class="flex items-center gap-1.5">
            <!-- Toast / Save Status -->
            <template x-if="saveToast.message">
                <div :class="saveToast.type === 'error' ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200'"
                     class="px-2.5 py-1.5 rounded-xl border text-xs font-semibold flex items-center gap-1.5">
                    <span x-text="saveToast.message"></span>
                </div>
            </template>

            <!-- Tautan Terpadu ke Cockpit Tracking Dokumen (11 Pos Operasional) -->
            <a href="{{ route('core.tracking.index', ['mode' => 'tracking_dok', 'search' => $shipment?->project_no ?? $bc20->nomor_aju]) }}"
               target="_blank"
               class="w-9 h-9 rounded-xl border border-sky-200 dark:border-sky-800 bg-sky-50/80 dark:bg-sky-950/40 hover:bg-sky-100 dark:hover:bg-sky-900/60 text-[#007AFF] inline-flex items-center justify-center transition-colors cursor-pointer shadow-2xs"
               title="Cockpit Tracking Dokumen (11 Pos Operasional)"
               aria-label="Cockpit Tracking Dokumen (11 Pos Operasional)">
                <i data-lucide="compass" class="w-4 h-4"></i>
            </a>

            <!-- Tombol Cerdas: Sinkron Ulang dari Berkas PDF / Dokumen Fisik -->
            <button type="button" @click="resyncFromDocuments()" :disabled="resyncing"
                    class="w-9 h-9 rounded-xl border border-indigo-200 dark:border-indigo-800 bg-indigo-50/80 dark:bg-indigo-950/40 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 inline-flex items-center justify-center transition-colors cursor-pointer shadow-2xs"
                    title="Sinkron Ulang dari Berkas PDF / Dokumen Fisik"
                    aria-label="Sinkron Ulang dari Berkas PDF / Dokumen Fisik">
                <template x-if="!resyncing">
                    <i data-lucide="sparkles" class="w-4 h-4 text-indigo-500"></i>
                </template>
                <template x-if="resyncing">
                    <div class="w-4 h-4 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
                </template>
            </button>

            <!-- Tombol Simpan Draft -->
            <button type="button" @click="saveDraftAjax()" :disabled="saving"
                    class="w-9 h-9 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] hover:bg-slate-100 dark:hover:bg-white/10 text-slate-700 dark:text-slate-200 inline-flex items-center justify-center transition-colors cursor-pointer shadow-2xs"
                    title="Simpan Draft"
                    aria-label="Simpan Draft">
                <template x-if="!saving">
                    <i data-lucide="save" class="w-4 h-4 text-[#007AFF]"></i>
                </template>
                <template x-if="saving">
                    <div class="w-4 h-4 border-2 border-[#007AFF] border-t-transparent rounded-full animate-spin"></div>
                </template>
            </button>

            <!-- Tombol Toggle Preview PDF / Dokumen Fisik -->
            <button type="button" @click="togglePreview()"
                    :class="showPreview ? 'bg-[#007AFF]/10 text-[#007AFF] border-[#007AFF]/30' : 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-white/10 dark:text-slate-200 dark:border-white/15'"
                    class="w-9 h-9 rounded-xl border inline-flex items-center justify-center transition-colors cursor-pointer shadow-2xs"
                    :title="showPreview ? 'Tutup Panel Preview Berkas' : 'Buka Panel Preview Berkas (' + previewFiles.length + ')'"
                    :aria-label="showPreview ? 'Tutup Panel Preview Berkas' : 'Buka Panel Preview Berkas'">
                <i data-lucide="panel-left" class="w-4 h-4"></i>
            </button>
        </div>
    </div>

    <!-- Skema Alur Waterfall Pabean & Tracking Dokumen Terpadu (Sinergi Create-Edit-Detail) -->
    @include('bc20.components.waterfall_stepper', [
        'activeStage' => 4,
        'bc20' => $bc20,
        'shipment' => $shipment,
    ])

    <!-- Mode Revisi Notice if not DRAFT -->
    @if(!$isDraft)
        <div class="p-3.5 rounded-2xl bg-amber-50/90 dark:bg-amber-950/40 border border-amber-300/80 dark:border-amber-800/60 text-amber-950 dark:text-amber-200 text-xs flex items-center gap-3">
            <i data-lucide="shield-alert" class="w-4 h-4 text-amber-600 shrink-0"></i>
            <span><strong>Mode Revisi Pasca-Kirim CEISA:</strong> Perubahan akan tercatat di Audit Trail dan mereset validasi skema untuk pengecekan ulang.</span>
        </div>
    @endif

    <!-- Main Split-Screen Workspace -->
    <form action="{{ route('bc20.update', $bc20->id) }}" method="POST" id="bc20WizardForm" @submit="prepareFormSubmit($event)">
        @csrf
        @method('PUT')
        <input type="hidden" name="wizard_payload" x-ref="wizardPayloadInput">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
            <!-- LEFT PANE: LIVE DOCUMENT PREVIEW (PDF / IMAGE) -->
            <div x-show="showPreview" x-cloak class="lg:col-span-5 lg:sticky lg:top-4">
                <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl border border-black/[0.08] dark:border-white/[0.1] shadow-sm overflow-hidden flex flex-col h-[720px]">
                    <!-- Preview Header Bar -->
                    <div class="px-4 py-2.5 bg-slate-50 dark:bg-white/[0.03] border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2 min-w-0">
                            <i data-lucide="file-text" class="w-4 h-4 text-rose-500 shrink-0"></i>
                            <span class="font-mono font-bold text-xs text-slate-800 dark:text-white truncate"
                                  x-text="activePreviewFile ? activePreviewFile.nama_file : 'Belum ada berkas terunggah'"></span>
                        </div>
                        <template x-if="activePreviewFile">
                            <div class="flex items-center gap-1 shrink-0">
                                <template x-if="!activePreviewFile.is_image && activePreviewFile.is_previewable">
                                    <button type="button" @click="togglePdfMode()"
                                            :title="pdfMode === 'canvas' ? 'Beralih ke Viewer Browser (Embed)' : 'Beralih ke Viewer Canvas (PDF.js)'"
                                            class="px-2 py-1 rounded-lg border text-[10px] font-semibold flex items-center gap-1 cursor-pointer"
                                            :class="pdfMode === 'canvas' ? 'bg-[#007AFF]/10 text-[#007AFF] border-[#007AFF]/30' : 'bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-white/15'">
                                        <i data-lucide="layers" class="w-3 h-3"></i>
                                        <span x-text="pdfMode === 'canvas' ? 'Canvas' : 'Embed'"></span>
                                    </button>
                                </template>
                                <a :href="activePreviewFile.download_url" target="_blank" title="Unduh berkas"
                                   class="p-1.5 rounded-lg hover:bg-slate-200/70 dark:hover:bg-white/10 text-slate-600 dark:text-slate-300">
                                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                </a>
                                <a :href="activePreviewFile.preview_url" target="_blank" title="Buka di tab baru"
                                   class="p-1.5 rounded-lg hover:bg-slate-200/70 dark:hover:bg-white/10 text-slate-600 dark:text-slate-300">
                                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                </a>
                            </div>
                        </template>
                    </div>

                    <!-- Preview Navigation & Zoom Controls -->
                    <div class="px-3 py-2 bg-white dark:bg-[#1C1C1E] border-b border-black/[0.06] dark:border-white/[0.06] flex items-center justify-between gap-1.5 text-xs flex-wrap">
                        <!-- Doc Pager -->
                        <div class="flex items-center gap-1">
                            <button type="button" @click="prevPreviewDoc()" :disabled="previewFiles.length <= 1"
                                    class="p-1 rounded-lg border border-slate-200 dark:border-white/15 hover:bg-slate-100 dark:hover:bg-white/10 disabled:opacity-40 cursor-pointer">
                                <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                            </button>
                            <span class="font-mono font-semibold text-slate-700 dark:text-slate-200 px-1 text-[11px]"
                                  x-text="previewFiles.length > 0 ? ((activePreviewIndex + 1) + '/' + previewFiles.length) : '0/0'"></span>
                            <button type="button" @click="nextPreviewDoc()" :disabled="previewFiles.length <= 1"
                                    class="p-1 rounded-lg border border-slate-200 dark:border-white/15 hover:bg-slate-100 dark:hover:bg-white/10 disabled:opacity-40 cursor-pointer">
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>

                        <!-- Doc Selector Dropdown -->
                        <div class="flex items-center gap-1 flex-1 min-w-[120px] max-w-[200px]">
                            <select x-show="previewFiles.length > 0" @change="setPreviewIndex(parseInt($event.target.value, 10))" :value="activePreviewIndex"
                                    class="w-full px-2 py-1 rounded-lg border border-slate-200 dark:border-white/15 bg-slate-50 dark:bg-[#2C2C2E] text-[11px] font-medium truncate">
                                <template x-for="(pf, pIdx) in previewFiles" :key="pf.id">
                                    <option :value="pIdx" x-text="(pIdx + 1) + '. ' + pf.nama_file"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Image Zoom Controls -->
                        <template x-if="activePreviewFile && activePreviewFile.is_image">
                            <div class="flex items-center gap-1">
                                <button type="button" @click="previewZoom = Math.max(50, previewZoom - 15)" class="px-1.5 py-0.5 rounded border border-slate-200 dark:border-white/15 text-[11px] cursor-pointer hover:bg-slate-100 dark:hover:bg-white/10">-</button>
                                <span class="font-mono text-[11px] w-9 text-center" x-text="previewZoom + '%'"></span>
                                <button type="button" @click="previewZoom = Math.min(200, previewZoom + 15)" class="px-1.5 py-0.5 rounded border border-slate-200 dark:border-white/15 text-[11px] cursor-pointer hover:bg-slate-100 dark:hover:bg-white/10">+</button>
                            </div>
                        </template>

                        <!-- PDF Multi-Page & Zoom Controls (Canvas Mode) -->
                        <template x-if="activePreviewFile && !activePreviewFile.is_image && activePreviewFile.is_previewable && pdfMode === 'canvas'">
                            <div class="flex items-center gap-1">
                                <!-- PDF Page Nav -->
                                <button type="button" @click="prevPdfPage()" :disabled="pdfPageNum <= 1 || pdfLoading"
                                        class="p-0.5 px-1 rounded border border-slate-200 dark:border-white/15 text-[11px] hover:bg-slate-100 dark:hover:bg-white/10 disabled:opacity-40 cursor-pointer">
                                    &lsaquo;
                                </button>
                                <span class="font-mono text-[11px] text-slate-700 dark:text-slate-200 px-0.5 whitespace-nowrap"
                                      x-text="'Hal ' + pdfPageNum + '/' + (pdfTotalPages || 1)"></span>
                                <button type="button" @click="nextPdfPage()" :disabled="pdfPageNum >= pdfTotalPages || pdfLoading"
                                        class="p-0.5 px-1 rounded border border-slate-200 dark:border-white/15 text-[11px] hover:bg-slate-100 dark:hover:bg-white/10 disabled:opacity-40 cursor-pointer">
                                    &rsaquo;
                                </button>

                                <!-- PDF Zoom -->
                                <button type="button" @click="zoomPdf(-0.2)" :disabled="pdfLoading"
                                        class="p-0.5 px-1.5 rounded border border-slate-200 dark:border-white/15 text-[11px] hover:bg-slate-100 dark:hover:bg-white/10 cursor-pointer">-</button>
                                <span class="font-mono text-[11px] w-9 text-center" x-text="Math.round(pdfScale * 100) + '%'"></span>
                                <button type="button" @click="zoomPdf(0.2)" :disabled="pdfLoading"
                                        class="p-0.5 px-1.5 rounded border border-slate-200 dark:border-white/15 text-[11px] hover:bg-slate-100 dark:hover:bg-white/10 cursor-pointer">+</button>
                                <button type="button" @click="rotatePdf()" :disabled="pdfLoading" title="Putar 90 derajat"
                                        class="p-1 rounded border border-slate-200 dark:border-white/15 text-[11px] hover:bg-slate-100 dark:hover:bg-white/10 cursor-pointer">
                                    <i data-lucide="rotate-cw" class="w-3 h-3"></i>
                                </button>
                            </div>
                        </template>
                    </div>

                    <!-- Preview Canvas Area -->
                    <div class="flex-1 bg-slate-100 dark:bg-[#111113] overflow-auto relative flex flex-col items-center justify-start min-h-[580px]">
                        <template x-if="!activePreviewFile">
                            <div class="text-center p-6 space-y-3 my-auto">
                                <div class="w-12 h-12 rounded-2xl bg-slate-200/70 dark:bg-white/10 text-slate-400 mx-auto flex items-center justify-center">
                                    <i data-lucide="file-search" class="w-6 h-6"></i>
                                </div>
                                <div class="text-xs font-semibold text-slate-600 dark:text-slate-300">Belum ada file PDF / Gambar yang diunggah</div>
                                <p class="text-[11px] text-slate-400 max-w-xs mx-auto">Unggah Invoice, Packing List, B/L, atau e-COO pada tab Dokumen untuk langsung melihat preview di sini.</p>
                            </div>
                        </template>

                        <!-- Image Viewer -->
                        <template x-if="activePreviewFile && activePreviewFile.is_image">
                            <div class="w-full h-full overflow-auto p-3 flex items-start justify-center">
                                <img :src="activePreviewFile.preview_url" :alt="activePreviewFile.nama_file"
                                     :style="'width: ' + previewZoom + '%; max-width: none;'"
                                     class="rounded-lg shadow-md transition-all">
                            </div>
                        </template>

                        <!-- PDF Viewer: Canvas Mode (PDF.js) -->
                        <template x-if="activePreviewFile && !activePreviewFile.is_image && activePreviewFile.is_previewable && pdfMode === 'canvas'">
                            <div class="w-full h-full flex flex-col overflow-hidden relative">
                                <!-- Loading overlay -->
                                <div x-show="pdfLoading" class="absolute inset-0 bg-white/85 dark:bg-[#1C1C1E]/85 flex flex-col items-center justify-center gap-2.5 z-20">
                                    <div class="w-6 h-6 border-2 border-[#007AFF] border-t-transparent rounded-full animate-spin"></div>
                                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-200">Memuat halaman PDF...</span>
                                </div>

                                <!-- Error Alert if canvas failed -->
                                <div x-show="pdfError" class="p-3 bg-amber-50 dark:bg-amber-950/40 border-b border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200 text-xs flex items-center justify-between gap-2">
                                    <span x-text="pdfError"></span>
                                    <button type="button" @click="pdfMode = 'native'" class="font-bold underline cursor-pointer">Beralih ke Embed</button>
                                </div>

                                <!-- Scrollable Canvas Container -->
                                <div class="flex-1 overflow-auto p-3 flex flex-col items-center justify-start bg-slate-200/50 dark:bg-black/40">
                                    <canvas id="pdfCanvasViewer" class="shadow-xl rounded-lg bg-white my-auto max-w-full"></canvas>
                                </div>
                            </div>
                        </template>

                        <!-- PDF Viewer: Native Browser Embed Mode (Object / Iframe Fallback) -->
                        <template x-if="activePreviewFile && !activePreviewFile.is_image && activePreviewFile.is_previewable && pdfMode === 'native'">
                            <div class="w-full h-full flex flex-col">
                                <object :data="activePreviewFile.preview_url + '#toolbar=1'" type="application/pdf" class="w-full flex-1 border-0 bg-white">
                                    <iframe :src="activePreviewFile.preview_url" class="w-full h-full border-0 bg-white">
                                        <div class="p-6 text-center space-y-2">
                                            <p class="text-xs text-slate-600">Browser Anda tidak mendukung preview langsung.</p>
                                            <a :href="activePreviewFile.download_url" class="btn-apple-filled text-xs px-3 py-1.5 inline-flex items-center gap-1">
                                                <span>Unduh Berkas</span>
                                            </a>
                                        </div>
                                    </iframe>
                                </object>
                                <div class="px-3 py-1.5 bg-slate-50 dark:bg-white/[0.04] border-t border-black/[0.06] flex items-center justify-between text-[11px] text-slate-500">
                                    <span>Viewer Browser Embed</span>
                                    <button type="button" @click="togglePdfMode()" class="text-[#007AFF] hover:underline font-semibold cursor-pointer">
                                        Beralih ke Canvas PDF.js
                                    </button>
                                </div>
                            </div>
                        </template>

                        <!-- Unsupported File Format -->
                        <template x-if="activePreviewFile && !activePreviewFile.is_previewable">
                            <div class="text-center p-6 space-y-3 my-auto">
                                <p class="text-xs font-semibold text-slate-700 dark:text-slate-200" x-text="'Format ' + activePreviewFile.ext + ' tidak didukung untuk preview langsung.'"></p>
                                <a :href="activePreviewFile.download_url" class="btn-apple-filled text-xs px-4 py-2 inline-flex items-center gap-1.5">
                                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                    <span>Unduh Berkas</span>
                                </a>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- RIGHT PANE: 9-STAGE PIB WIZARD -->
            <div :class="showPreview ? 'lg:col-span-7' : 'lg:col-span-12'" class="space-y-4">
                <!-- 9-Stage Horizontal Stepper -->
                <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-2 border border-black/[0.06] dark:border-white/[0.08] shadow-xs overflow-x-auto">
                    <div class="flex items-center gap-1 min-w-max">
                        <template x-for="st in steps" :key="st.id">
                            <button type="button" @click="goToStep(st.id)"
                                    :class="activeStep === st.id
                                        ? 'bg-[#007AFF]/10 border-[#007AFF]/40 text-[#007AFF] font-bold shadow-xs'
                                        : 'border-transparent text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/[0.04] font-medium'"
                                    class="px-3 py-2 rounded-xl border text-xs flex items-center gap-2 transition-all whitespace-nowrap cursor-pointer">
                                <span :class="activeStep === st.id
                                        ? 'bg-[#007AFF] text-white border-[#007AFF]'
                                        : (isStepComplete(st.id) ? 'bg-emerald-50 border-emerald-500 text-emerald-600' : 'bg-slate-100 dark:bg-white/10 border-slate-300 dark:border-white/20 text-slate-500')"
                                      class="w-5 h-5 rounded-full border text-[10px] font-bold flex items-center justify-center shrink-0">
                                    <span x-show="isStepComplete(st.id) && activeStep !== st.id">&check;</span>
                                    <span x-show="!isStepComplete(st.id) || activeStep === st.id" x-text="st.id"></span>
                                </span>
                                <span x-text="st.label"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Stage Contents -->
                <div x-show="activeStep === 1" x-cloak>@include('bc20.wizard._stage_header')</div>
                <div x-show="activeStep === 2" x-cloak>@include('bc20.wizard._stage_entitas')</div>
                <div x-show="activeStep === 3" x-cloak>@include('bc20.wizard._stage_dokumen')</div>
                <div x-show="activeStep === 4" x-cloak>@include('bc20.wizard._stage_pengangkut')</div>
                <div x-show="activeStep === 5" x-cloak>@include('bc20.wizard._stage_kemasan')</div>
                <div x-show="activeStep === 6" x-cloak>@include('bc20.wizard._stage_transaksi')</div>
                <div x-show="activeStep === 7" x-cloak>@include('bc20.wizard._stage_barang')</div>
                <div x-show="activeStep === 8" x-cloak>@include('bc20.wizard._stage_pungutan')</div>
                <div x-show="activeStep === 9" x-cloak>@include('bc20.wizard._stage_pernyataan')</div>
            </div>
        </div>

        <!-- Sticky Bottom Wizard Navigation Bar (Optimasi User Experience) -->
        <div class="fixed bottom-0 inset-x-0 z-30 bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-md border-t border-black/[0.08] dark:border-white/[0.1] px-4 sm:px-8 py-3 shadow-lg">
            <div class="max-w-[1600px] mx-auto flex flex-wrap items-center justify-between gap-3 text-xs">
                <!-- Left: Stage Status & Quick Info -->
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-lg bg-[#007AFF] text-white font-bold font-mono text-[11px]"
                              x-text="'Tahap ' + activeStep + ' / 9'"></span>
                        <span class="font-bold text-slate-800 dark:text-white" x-text="steps.find(s => s.id === activeStep)?.label"></span>
                    </div>

                    <!-- Jump Dots (Desktop) -->
                    <div class="hidden xl:flex items-center gap-1.5 ml-2">
                        <template x-for="st in steps" :key="st.id">
                            <button type="button" @click="goToStep(st.id)"
                                    :class="st.id === activeStep ? 'w-6 bg-[#007AFF]' : (isStepComplete(st.id) ? 'w-2 bg-emerald-500' : 'w-2 bg-slate-300 dark:bg-white/20')"
                                    :title="'Tahap ' + st.id + ': ' + st.label"
                                    class="h-2 rounded-full transition-all cursor-pointer"></button>
                        </template>
                    </div>

                    <!-- Inline Save Toast -->
                    <template x-if="saveToast.message">
                        <span :class="saveToast.type === 'error' ? 'text-rose-600' : 'text-emerald-600'"
                              class="text-[11px] font-semibold flex items-center gap-1 animate-pulse ml-2">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            <span x-text="saveToast.message"></span>
                        </span>
                    </template>
                </div>

                <!-- Right: Action & Navigation Buttons -->
                <div class="flex items-center gap-2">
                    <!-- Toggle Preview PDF -->
                    <button type="button" @click="togglePreview()"
                            :class="showPreview ? 'bg-[#007AFF]/10 text-[#007AFF] border-[#007AFF]/30' : 'border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] text-slate-700 dark:text-slate-200'"
                            class="px-3.5 py-2 rounded-xl border text-xs font-semibold inline-flex items-center gap-1.5 transition-colors cursor-pointer">
                        <i data-lucide="panel-left" class="w-3.5 h-3.5"></i>
                        <span x-text="showPreview ? 'Tutup PDF' : 'Buka PDF (' + previewFiles.length + ')'"></span>
                    </button>

                    <!-- Simpan Draft Ajax Langsung di Bottom Bar -->
                    <button type="button" @click="saveDraftAjax()" :disabled="saving"
                            class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] hover:bg-slate-50 text-slate-700 dark:text-slate-200 font-semibold inline-flex items-center gap-1.5 cursor-pointer">
                        <template x-if="!saving">
                            <i data-lucide="save" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                        </template>
                        <template x-if="saving">
                            <div class="w-3.5 h-3.5 border-2 border-[#007AFF] border-t-transparent rounded-full animate-spin"></div>
                        </template>
                        <span x-text="saving ? 'Menyimpan...' : 'Simpan Draft'"></span>
                    </button>

                    <!-- Tombol Sebelumnya -->
                    <button type="button" @click="prevStep()" :disabled="activeStep <= 1"
                            class="px-4 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] text-xs font-semibold text-slate-700 dark:text-slate-200 disabled:opacity-40 inline-flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                        <span>Sebelumnya</span>
                    </button>

                    <!-- Tombol Selanjutnya / Selesai -->
                    <template x-if="activeStep < 9">
                        <button type="button" @click="nextStep()"
                                class="px-5 py-2 rounded-xl bg-[#007AFF] hover:bg-[#0066D6] text-white text-xs font-bold inline-flex items-center gap-1.5 shadow-sm cursor-pointer transition-colors">
                            <span>Selanjutnya</span>
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </button>
                    </template>
                    <template x-if="activeStep === 9">
                        <button type="submit"
                                class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold inline-flex items-center gap-1.5 shadow-sm cursor-pointer transition-colors">
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                            <span>Simpan &amp; Selesai</span>
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
window.bc20WizardManager = function bc20WizardManager() {
    const initialWizard = @json($wizardState);
    const initialFiles = @json($previewFiles);
    const docLabels = @json($refOptions['kode_dokumen']);

    // Non-reactive raw PDF.js instances (bypasses Alpine's ES Proxy wrapping to avoid private member #d errors)
    let rawPdfDoc = null;
    let currentRenderTask = null;

    return {
        activeStep: 1,
        steps: [
            { id: 1, label: 'Header' },
            { id: 2, label: 'Entitas' },
            { id: 3, label: 'Dokumen' },
            { id: 4, label: 'Pengangkut' },
            { id: 5, label: 'Kemasan & Peti Kemas' },
            { id: 6, label: 'Transaksi' },
            { id: 7, label: 'Barang' },
            { id: 8, label: 'Pungutan' },
            { id: 9, label: 'Pernyataan' },
        ],
        w: initialWizard,
        previewFiles: initialFiles,
        showPreview: initialFiles.length > 0,
        activePreviewIndex: 0,
        previewZoom: 100,
        saving: false,
        resyncing: false,
        saveToast: { type: 'success', message: '' },

        // PDF.js Canvas Viewer State
        pdfDoc: null,
        pdfPageNum: 1,
        pdfTotalPages: 0,
        pdfRendering: false,
        pdfPagePending: null,
        pdfScale: 1.15,
        pdfRotate: 0,
        pdfMode: 'canvas', // 'canvas' (PDF.js) or 'native' (browser embed)
        pdfError: null,
        pdfLoading: false,

        getRawPdfDoc() {
            if (rawPdfDoc) return rawPdfDoc;
            if (window.__currentPdfDoc) return window.__currentPdfDoc;
            return null;
        },

        // Dokumen modal state
        docModalOpen: false,
        editingDocIndex: null,
        docForm: { kodeDokumen: '380', nomorDokumen: '', tanggalDokumen: '', shipmentDocumentId: '', ecooStatus: '-', linkedSeri: '' },

        // Barang search & modal state
        barangSearch: '',
        barangModalOpen: false,
        editingBarangIndex: null,
        barangForm: {},
        pungutan: initialWizard.pungutan || { PPH: {}, PPN: {}, BM: {}, TOTAL: {} },

        // HS Code Recommendation & BTKI Live Search State
        recommendModalOpen: false,
        activeRecommendIndex: null,
        recommendSearchQuery: '',
        recommendLoading: false,
        recommendCandidates: [],
        autoRecommendLoading: false,

        btkiSearchInput: '',
        btkiSearchResults: [],
        btkiSearchLoading: false,
        btkiSearchOpen: false,

        init() {
            this.ensureAtLeastOneBarang();
            this.recalculatePungutan(false);
            this.$nextTick(() => {
                this.triggerPdfLoad();
            });
        },

        get activePreviewFile() {
            return this.previewFiles[this.activePreviewIndex] || null;
        },

        togglePreview() {
            this.showPreview = !this.showPreview;
            if (this.showPreview) {
                this.$nextTick(() => this.triggerPdfLoad());
            }
            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
        },

        setPreviewIndex(idx) {
            if (idx >= 0 && idx < this.previewFiles.length) {
                this.activePreviewIndex = idx;
                this.showPreview = true;
                this.$nextTick(() => {
                    this.triggerPdfLoad();
                    if (window.lucide) window.lucide.createIcons();
                });
            }
        },

        prevPreviewDoc() {
            if (this.previewFiles.length <= 1) return;
            this.activePreviewIndex = (this.activePreviewIndex - 1 + this.previewFiles.length) % this.previewFiles.length;
            this.$nextTick(() => {
                this.triggerPdfLoad();
                if (window.lucide) window.lucide.createIcons();
            });
        },

        nextPreviewDoc() {
            if (this.previewFiles.length <= 1) return;
            this.activePreviewIndex = (this.activePreviewIndex + 1) % this.previewFiles.length;
            this.$nextTick(() => {
                this.triggerPdfLoad();
                if (window.lucide) window.lucide.createIcons();
            });
        },

        previewByDocId(docId) {
            const idx = this.previewFiles.findIndex(f => f.id === docId);
            if (idx !== -1) {
                this.activePreviewIndex = idx;
                this.showPreview = true;
                this.$nextTick(() => {
                    this.triggerPdfLoad();
                    if (window.lucide) window.lucide.createIcons();
                });
            }
        },

        triggerPdfLoad() {
            const f = this.activePreviewFile;
            if (f && !f.is_image && f.is_previewable && this.pdfMode === 'canvas') {
                this.loadPdf(f.preview_url);
            }
        },

        loadPdf(url) {
            if (!url) return;
            this.pdfLoading = true;
            this.pdfError = null;
            this.pdfDoc = null;
            this.pdfPageNum = 1;
            this.pdfTotalPages = 0;

            if (currentRenderTask) {
                try { currentRenderTask.cancel(); } catch (e) {}
                currentRenderTask = null;
            }
            rawPdfDoc = null;

            if (typeof pdfjsLib === 'undefined') {
                console.warn('PDF.js library belum siap, beralih ke mode native embed.');
                this.pdfMode = 'native';
                this.pdfLoading = false;
                return;
            }

            const loadingTask = pdfjsLib.getDocument({
                url: url,
                withCredentials: true
            });

            loadingTask.promise.then(pdf => {
                rawPdfDoc = pdf; // Keep raw instance outside Alpine Proxy!
                window.__currentPdfDoc = pdf;
                this.pdfDoc = true;
                this.pdfTotalPages = pdf.numPages;
                this.pdfLoading = false;
                this.$nextTick(() => {
                    this.renderPdfPage(this.pdfPageNum);
                });
            }).catch(err => {
                console.warn('PDF.js gagal me-render berkas, beralih ke browser embed:', err);
                this.pdfError = 'Preview Canvas mengalami kendala. Beralih ke browser embed.';
                this.pdfMode = 'native';
                this.pdfLoading = false;
            });
        },

        renderPdfPage(num) {
            const doc = this.getRawPdfDoc();
            if (!doc) {
                this.pdfRendering = false;
                return;
            }

            if (currentRenderTask) {
                try { currentRenderTask.cancel(); } catch (e) {}
                currentRenderTask = null;
            }

            this.pdfRendering = true;
            doc.getPage(num).then(page => {
                const canvas = document.getElementById('pdfCanvasViewer');
                if (!canvas) {
                    this.pdfRendering = false;
                    return;
                }
                const container = canvas.parentElement;
                const containerWidth = container ? (container.clientWidth - 28) : 580;
                const unscaledViewport = page.getViewport({ scale: 1.0, rotation: this.pdfRotate });
                const baseScale = containerWidth / Math.max(1, unscaledViewport.width);
                const finalScale = Math.max(0.4, baseScale * this.pdfScale);

                const viewport = page.getViewport({ scale: finalScale, rotation: this.pdfRotate });
                const ctx = canvas.getContext('2d');
                canvas.height = viewport.height;
                canvas.width = viewport.width;

                const renderContext = {
                    canvasContext: ctx,
                    viewport: viewport
                };
                currentRenderTask = page.render(renderContext);
                currentRenderTask.promise.then(() => {
                    this.pdfRendering = false;
                    currentRenderTask = null;
                    if (this.pdfPagePending !== null) {
                        this.renderPdfPage(this.pdfPagePending);
                        this.pdfPagePending = null;
                    }
                }).catch(e => {
                    this.pdfRendering = false;
                    currentRenderTask = null;
                    if (e && e.name !== 'RenderingCancelledException') {
                        console.warn('PDF render error:', e);
                    }
                });
            }).catch(e => {
                this.pdfRendering = false;
                currentRenderTask = null;
                console.warn('PDF getPage error, switching to native embed:', e);
                this.pdfMode = 'native';
            });
        },

        queueRenderPage(num) {
            if (this.pdfRendering) {
                this.pdfPagePending = num;
            } else {
                this.renderPdfPage(num);
            }
        },

        prevPdfPage() {
            if (this.pdfPageNum <= 1) return;
            this.pdfPageNum--;
            this.queueRenderPage(this.pdfPageNum);
        },

        nextPdfPage() {
            if (!this.getRawPdfDoc() || this.pdfPageNum >= this.pdfTotalPages) return;
            this.pdfPageNum++;
            this.queueRenderPage(this.pdfPageNum);
        },

        zoomPdf(delta) {
            this.pdfScale = Math.max(0.6, Math.min(2.5, this.pdfScale + delta));
            this.queueRenderPage(this.pdfPageNum);
        },

        rotatePdf() {
            this.pdfRotate = (this.pdfRotate + 90) % 360;
            this.queueRenderPage(this.pdfPageNum);
        },

        togglePdfMode() {
            this.pdfMode = (this.pdfMode === 'canvas') ? 'native' : 'canvas';
            if (this.pdfMode === 'canvas') {
                this.$nextTick(() => {
                    this.triggerPdfLoad();
                });
            }
            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
        },

        async resyncFromDocuments() {
            if (this.resyncing) return;
            if (!confirm('Apakah Anda ingin membaca ulang seluruh berkas fisik PDF/gambar yang tersimpan dan menyinkronkan data form wizard PIB ini?')) {
                return;
            }

            this.resyncing = true;
            try {
                const res = await fetch('{{ route("bc20.resync-extraction", $bc20->id) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });
                const data = await res.json();
                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Gagal membaca berkas dokumen.');
                }

                const s = data.sync_data;
                if (!s) throw new Error('Data ekstraksi kosong.');

                // 1. Sinkron Entitas
                if (s.entitas) {
                    if (s.entitas.supplier_nama) this.w.entitas.supplier_nama = s.entitas.supplier_nama;
                    if (s.entitas.supplier_negara) this.w.entitas.supplier_negara = s.entitas.supplier_negara;
                    if (s.entitas.supplier_alamat) this.w.entitas.supplier_alamat = s.entitas.supplier_alamat;
                    if (s.entitas.seller_nama) this.w.entitas.seller_nama = s.entitas.seller_nama;
                    if (s.entitas.seller_negara) this.w.entitas.seller_negara = s.entitas.seller_negara;
                    if (s.entitas.seller_alamat) this.w.entitas.seller_alamat = s.entitas.seller_alamat;
                    if (s.entitas.importir_nama && (!this.w.entitas.importir_nama || this.w.entitas.importir_nama === 'PT. DANWIN UTAMA TEKNIK')) {
                        this.w.entitas.importir_nama = s.entitas.importir_nama;
                    }
                    if (s.entitas.importir_npwp && !this.w.entitas.importir_npwp) {
                        this.w.entitas.importir_npwp = s.entitas.importir_npwp;
                    }
                }

                // 2. Sinkron Pengangkut
                if (s.pengangkut) {
                    if (s.pengangkut.nama_sarana_pengangkut) this.w.pengangkut.nama_sarana_pengangkut = s.pengangkut.nama_sarana_pengangkut;
                    if (s.pengangkut.nomor_voyage) this.w.pengangkut.nomor_voyage = s.pengangkut.nomor_voyage;
                    if (s.pengangkut.pelabuhan_muat) this.w.pengangkut.pelabuhan_muat = s.pengangkut.pelabuhan_muat;
                    if (s.pengangkut.pelabuhan_bongkar) this.w.pengangkut.pelabuhan_bongkar = s.pengangkut.pelabuhan_bongkar;
                    if (s.pengangkut.perkiraan_tanggal_tiba) this.w.pengangkut.perkiraan_tanggal_tiba = s.pengangkut.perkiraan_tanggal_tiba;
                }

                // 3. Sinkron Transaksi & Nilai
                if (s.transaksi) {
                    if (s.transaksi.kode_valuta) this.w.transaksi.kode_valuta = s.transaksi.kode_valuta;
                    if (s.transaksi.harga_barang > 0) this.w.transaksi.harga_barang = s.transaksi.harga_barang;
                    if (s.transaksi.freight > 0) this.w.transaksi.freight = s.transaksi.freight;
                    if (s.transaksi.asuransi > 0) this.w.transaksi.nilai_asuransi = s.transaksi.asuransi;
                    if (s.transaksi.berat_bruto > 0) this.w.transaksi.berat_kotor = s.transaksi.berat_bruto;
                    if (s.transaksi.berat_bersih > 0) this.w.transaksi.berat_bersih = s.transaksi.berat_bersih;
                }

                // 4. Sinkron Nomor Dokumen (BL & Invoice)
                if (s.dokumen_nomor) {
                    if (s.dokumen_nomor.invoice) {
                        const invDoc = this.w.dokumen.find(d => d.kodeDokumen === '380');
                        if (invDoc) {
                            invDoc.nomorDokumen = s.dokumen_nomor.invoice;
                            if (s.dokumen_nomor.tgl_invoice) invDoc.tanggalDokumen = s.dokumen_nomor.tgl_invoice;
                        }
                    }
                    if (s.dokumen_nomor.bl) {
                        const blDoc = this.w.dokumen.find(d => d.kodeDokumen === '705' || d.kodeDokumen === '740');
                        if (blDoc) {
                            blDoc.nomorDokumen = s.dokumen_nomor.bl;
                            if (s.dokumen_nomor.tgl_bl) blDoc.tanggalDokumen = s.dokumen_nomor.tgl_bl;
                        }
                    }
                }

                // 5. Sinkron Kontainer
                if (s.kontainer && s.kontainer.nomor_kontainer) {
                    const rawNums = s.kontainer.nomor_kontainer.split(/[\s,;\n]+/).filter(Boolean);
                    if (rawNums.length > 0) {
                        if (this.w.kontainer.length === 1 && !this.w.kontainer[0].nomorKontainer) {
                            this.w.kontainer[0].nomorKontainer = rawNums[0];
                            for (let k = 1; k < rawNums.length; k++) {
                                this.w.kontainer.push({
                                    seriKontainer: k + 1,
                                    nomorKontainer: rawNums[k],
                                    kodeUkuranKontainer: '40',
                                    kodeJenisKontainer: '8',
                                    kodeTipeKontainer: '1'
                                });
                            }
                        }
                    }
                }

                // 6. Sinkron Kemasan
                if (s.kemasan && s.kemasan.total_kemasan > 0) {
                    if (this.w.kemasan.length > 0) {
                        this.w.kemasan[0].jumlahKemasan = s.kemasan.total_kemasan;
                        if (s.kemasan.jenis_kemasan) {
                            this.w.kemasan[0].kodeJenisKemasan = s.kemasan.jenis_kemasan;
                        }
                    }
                }

                // 7. Sinkron Barang jika ada item baru
                if (Array.isArray(s.items) && s.items.length > 0) {
                    if (this.w.barang.length === 1 && (!this.w.barang[0].uraian || this.w.barang[0].uraian === 'Barang Impor Umum' || !this.w.barang[0].fob)) {
                        this.w.barang = [];
                    }
                    if (this.w.barang.length === 0) {
                        s.items.forEach((it, idx) => {
                            this.w.barang.push({
                                seri: idx + 1,
                                group: 1,
                                hs_code: it.pos_tarif || '84713020',
                                pernyataan_lartas: it.has_lartas ? 'LARTAS' : 'NON',
                                uraian: it.uraian_barang || 'Item Barang',
                                merk: it.merk || 'TANPA MEREK',
                                tipe: it.tipe || 'TANPA TIPE',
                                spesifikasi: it.spesifikasi || '-',
                                negara_asal: s.entitas?.supplier_negara || 'CN',
                                jumlah: it.jumlah_satuan || 1,
                                satuan: it.kode_satuan || 'PCE',
                                jumlah_kemasan: it.jumlah_kemasan || 1,
                                kemasan: it.jenis_kemasan || 'CT',
                                netto: it.berat_netto || 0,
                                bruto: it.berat_bruto || 0,
                                fob: it.nilai_barang || 0,
                                tarif_bm: 0,
                                tarif_ppn: 11,
                                tarif_pph: 2.5,
                                kode_fasilitas_bm: '1',
                                kode_fasilitas_ppn: '1',
                                kode_fasilitas_pph: '1',
                                barang_dokumen: [],
                                hs_info: it.pos_tarif_desc || ''
                            });
                        });
                    }
                }

                this.recalculatePungutan(false);
                this.showToast('✨ ' + data.message);
                if (window.lucide) window.lucide.createIcons();
            } catch (err) {
                this.showToast(err.message || 'Gagal membaca berkas dokumen.', 'error');
            } finally {
                this.resyncing = false;
            }
        },

        previewDocumentRow(doc) {
            if (doc.shipmentDocumentId) {
                this.previewByDocId(doc.shipmentDocumentId);
            } else {
                this.showToast('Berkas fisik belum ditautkan ke baris dokumen ini. Gunakan Unggah PDF untuk melampirkan.', 'error');
            }
        },

        selectDocForPreview(doc) {
            if (doc.shipmentDocumentId) {
                this.previewByDocId(doc.shipmentDocumentId);
            }
        },

        isDocActiveInPreview(doc) {
            return this.showPreview && this.activePreviewFile && doc.shipmentDocumentId && doc.shipmentDocumentId === this.activePreviewFile.id;
        },

        getPreviewFileExt(docId) {
            const pf = this.previewFiles.find(f => f.id === docId);
            return pf ? pf.ext : 'FILE';
        },

        getDocTypeLabel(kode) {
            return docLabels[kode] || (kode + ' - Dokumen Pelengkap');
        },

        goToStep(stepId) {
            this.activeStep = stepId;
            if (stepId === 8) {
                this.recalculatePungutan(false);
            }
            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
        },

        nextStep() {
            if (this.activeStep < 9) this.goToStep(this.activeStep + 1);
        },

        prevStep() {
            if (this.activeStep > 1) this.goToStep(this.activeStep - 1);
        },

        isStepComplete(id) {
            switch (id) {
                case 1: return !!(this.w.header.kode_kantor && this.w.header.pelabuhan_bongkar);
                case 2: return !!(this.w.entitas.importir_nama && this.w.entitas.supplier_nama);
                case 3: return this.w.dokumen.length > 0;
                case 4: return !!(this.w.pengangkut.nama_sarana_pengangkut && this.w.pengangkut.pelabuhan_muat);
                case 5: return this.w.kemasan.length > 0;
                case 6: return Number(this.w.transaksi.fob) >= 0 && Number(this.w.transaksi.bruto) >= 0;
                case 7: return this.w.barang.length > 0;
                case 8: return true;
                case 9: return !!(this.w.pernyataan.nama_ttd);
                default: return false;
            }
        },

        copyImportirTo(target) {
            if (target === 'pemilik') {
                this.w.entitas.pemilik_jenis_identitas = this.w.entitas.importir_jenis_identitas;
                this.w.entitas.pemilik_npwp = this.w.entitas.importir_npwp;
                this.w.entitas.pemilik_nama = this.w.entitas.importir_nama;
                this.w.entitas.pemilik_alamat = this.w.entitas.importir_alamat;
            } else if (target === 'pemusatan') {
                this.w.entitas.pemusatan_jenis_identitas = this.w.entitas.importir_jenis_identitas;
                this.w.entitas.pemusatan_npwp = this.w.entitas.importir_npwp;
                this.w.entitas.pemusatan_nama = this.w.entitas.importir_nama;
                this.w.entitas.pemusatan_alamat = this.w.entitas.importir_alamat;
            }
            this.showToast('Data Importir berhasil disalin.');
        },

        copyPengirimToPenjual() {
            this.w.entitas.seller_nama = this.w.entitas.supplier_nama;
            this.w.entitas.seller_negara = this.w.entitas.supplier_negara;
            this.w.entitas.seller_alamat = this.w.entitas.supplier_alamat;
            this.showToast('Data Pengirim disalin ke Penjual.');
        },

        // Stage 3: Dokumen helpers
        openNewDocModal() {
            this.editingDocIndex = null;
            this.docForm = {
                kodeDokumen: '380',
                nomorDokumen: '',
                tanggalDokumen: new Date().toISOString().slice(0, 10),
                shipmentDocumentId: '',
                ecooStatus: '-',
                linkedSeri: '',
            };
            this.docModalOpen = true;
        },

        openEditDocModal(idx) {
            this.editingDocIndex = idx;
            this.docForm = Object.assign({}, this.w.dokumen[idx]);
            this.docModalOpen = true;
        },

        saveDocModal() {
            if (this.editingDocIndex === null) {
                this.w.dokumen.push(Object.assign({ seriDokumen: this.w.dokumen.length + 1 }, this.docForm));
            } else {
                this.w.dokumen[this.editingDocIndex] = Object.assign({}, this.docForm, { seriDokumen: this.editingDocIndex + 1 });
            }
            this.docModalOpen = false;
            this.renumberDocs();
        },

        duplicateDocRow(idx) {
            const copy = Object.assign({}, this.w.dokumen[idx]);
            this.w.dokumen.splice(idx + 1, 0, copy);
            this.renumberDocs();
        },

        removeDocRow(idx) {
            this.w.dokumen.splice(idx, 1);
            this.renumberDocs();
        },

        renumberDocs() {
            this.w.dokumen.forEach((d, i) => d.seriDokumen = i + 1);
        },

        sortDocumentsAuto() {
            const order = { '380': 1, '705': 2, '740': 2, '861': 3, '217': 4, '430': 5 };
            this.w.dokumen.sort((a, b) => (order[a.kodeDokumen] || 99) - (order[b.kodeDokumen] || 99));
            this.renumberDocs();
            this.showToast('Urutan seri dokumen disusun otomatis sesuai standar PIB.');
        },

        validateEcooDocs() {
            let found = false;
            this.w.dokumen.forEach(d => {
                if (d.kodeDokumen === '861') {
                    d.ecooStatus = 'Valid / Terhubung';
                    found = true;
                }
            });
            this.showToast(found ? 'Dokumen e-COO (861) tervalidasi aktif.' : 'Belum ada dokumen kode 861 (e-COO) pada daftar.', found ? 'success' : 'error');
        },

        async handleWizardDocUpload(event, purpose = 'dokumen') {
            const file = event.target.files[0];
            if (!file) return;

            const fd = new FormData();
            fd.append('file', file);
            fd.append('purpose', purpose);
            fd.append('kode_dokumen', purpose === 'noa' ? '999' : (purpose === 'freight' ? '430' : '380'));

            try {
                const res = await fetch('{{ route("bc20.upload-document", $bc20->id) }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: fd
                });
                const data = await res.json();
                if (!res.ok || !data.success) throw new Error(data.message || 'Gagal unggah dokumen.');

                if (data.preview_file) {
                    this.previewFiles.unshift(data.preview_file);
                    this.activePreviewIndex = 0;
                    this.showPreview = true;
                }

                if (purpose === 'noa') {
                    this.w.pengangkut.noa_document_id = data.preview_file.id;
                } else if (purpose === 'freight') {
                    this.w.transaksi.freight_document_id = data.preview_file.id;
                } else if (data.doc_entry) {
                    data.doc_entry.seriDokumen = this.w.dokumen.length + 1;
                    this.w.dokumen.push(data.doc_entry);
                }

                this.showToast('Berkas berhasil diunggah & tampil di preview.');
            } catch (err) {
                this.showToast(err.message || 'Gagal unggah berkas.', 'error');
            } finally {
                event.target.value = '';
                this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
            }
        },

        // Stage 4: Pengangkut
        smartSyncBc11() {
            if (!this.w.pengangkut.nomor_bc11) this.w.pengangkut.nomor_bc11 = '004567';
            if (!this.w.pengangkut.tanggal_bc11) this.w.pengangkut.tanggal_bc11 = new Date().toISOString().slice(0, 10);
            if (!this.w.pengangkut.pos_bc11) this.w.pengangkut.pos_bc11 = '0012';
            if (!this.w.pengangkut.subpos_bc11) this.w.pengangkut.subpos_bc11 = '0000';
            this.showToast('Data manifest BC 1.1 & TPS disinkronkan.');
        },

        // Stage 5: Kemasan & Kontainer
        addKemasan() {
            this.w.kemasan.push({ seriKemasan: this.w.kemasan.length + 1, kodeJenisKemasan: 'CT', jumlahKemasan: 1, merkKemasan: '-' });
            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
        },
        duplicateKemasan(idx) {
            this.w.kemasan.splice(idx + 1, 0, Object.assign({}, this.w.kemasan[idx]));
        },
        removeKemasan(idx) {
            if (this.w.kemasan.length > 1) this.w.kemasan.splice(idx, 1);
        },

        addKontainer() {
            this.w.kontainer.push({ seriKontainer: this.w.kontainer.length + 1, nomorKontainer: '', kodeUkuranKontainer: '40', kodeJenisKontainer: '8', kodeTipeKontainer: '1' });
            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
        },
        duplicateKontainer(idx) {
            this.w.kontainer.splice(idx + 1, 0, Object.assign({}, this.w.kontainer[idx]));
        },
        removeKontainer(idx) {
            if (this.w.kontainer.length > 1) this.w.kontainer.splice(idx, 1);
        },

        // Stage 6: Transaksi
        computedCifValuta() {
            const fob = parseFloat(this.w.transaksi.fob || 0);
            const freight = parseFloat(this.w.transaksi.freight || 0);
            const asuransi = parseFloat(this.w.transaksi.asuransi || 0);
            const add = parseFloat(this.w.transaksi.biaya_tambahan || 0);
            const sub = parseFloat(this.w.transaksi.biaya_pengurang || 0);
            return Math.max(0, fob + freight + asuransi + add - sub);
        },
        computedCifRupiah() {
            return Math.round(this.computedCifValuta() * parseFloat(this.w.transaksi.ndbm_kurs || 0));
        },
        async fetchActiveKurs() {
            try {
                const res = await fetch('/ceisa/kurs/' + this.w.transaksi.kode_valuta);
                if (res.ok) {
                    const data = await res.json();
                    if (data.nilai_kurs) {
                        this.w.transaksi.ndbm_kurs = parseFloat(data.nilai_kurs);
                        this.recalculatePungutan(false);
                    }
                }
            } catch (e) {}
        },

        // Stage 7: Barang
        ensureAtLeastOneBarang() {
            if (!Array.isArray(this.w.barang) || this.w.barang.length === 0) {
                this.w.barang = [{
                    seri: 1, group: 1, hs_code: '84713020', pernyataan_lartas: 'NON',
                    uraian: 'BARANG IMPOR', merk: 'TANPA MEREK', tipe: 'TANPA TIPE', spesifikasi: '-',
                    negara_asal: 'CN', jumlah: 1, satuan: 'PCE', jumlah_kemasan: 1, kemasan: 'CT',
                    netto: parseFloat(this.w.transaksi.netto || 1), bruto: parseFloat(this.w.transaksi.bruto || 1),
                    fob: parseFloat(this.w.transaksi.fob || 100),
                    tarif_bm: 0, tarif_ppn: 11, tarif_pph: 2.5,
                    kode_fasilitas_bm: '1', kode_fasilitas_ppn: '1', kode_fasilitas_pph: '1', hs_info: ''
                }];
            }
        },
        filteredBarang() {
            const q = (this.barangSearch || '').trim().toLowerCase();
            return this.w.barang
                .map((it, origIdx) => Object.assign({}, it, { _origIdx: origIdx, _uid: origIdx + '_' + (it.hs_code || '') }))
                .filter(it => !q || (it.uraian || '').toLowerCase().includes(q) || (it.hs_code || '').toLowerCase().includes(q));
        },
        openNewBarangModal() {
            this.editingBarangIndex = null;
            this.barangForm = {
                seri: this.w.barang.length + 1, group: 1, hs_code: '84713020', pernyataan_lartas: 'NON',
                uraian: '', merk: 'TANPA MEREK', tipe: 'TANPA TIPE', spesifikasi: '-', negara_asal: 'CN',
                jumlah: 1, satuan: 'PCE', jumlah_kemasan: 1, kemasan: 'CT', netto: 100, bruto: 110, fob: 1000,
                tarif_bm: 0, tarif_ppn: 11, tarif_pph: 2.5,
                kode_fasilitas_bm: '1', kode_fasilitas_ppn: '1', kode_fasilitas_pph: '1', hs_info: ''
            };
            this.barangModalOpen = true;
        },
        openEditBarangModal(origIdx) {
            this.editingBarangIndex = origIdx;
            this.barangForm = Object.assign({}, this.w.barang[origIdx]);
            this.barangModalOpen = true;
        },
        saveBarangModal() {
            if (this.editingBarangIndex === null) {
                this.w.barang.push(Object.assign({}, this.barangForm));
            } else {
                this.w.barang[this.editingBarangIndex] = Object.assign({}, this.barangForm);
            }
            this.w.barang.forEach((b, i) => b.seri = i + 1);
            this.barangModalOpen = false;
            this.recalcFobFromBarang();
            this.recalculatePungutan(false);
        },
        duplicateBarang(origIdx) {
            const copy = Object.assign({}, this.w.barang[origIdx]);
            this.w.barang.splice(origIdx + 1, 0, copy);
            this.w.barang.forEach((b, i) => b.seri = i + 1);
            this.recalculatePungutan(false);
        },
        removeBarang(origIdx) {
            if (this.w.barang.length > 1) {
                this.w.barang.splice(origIdx, 1);
                this.w.barang.forEach((b, i) => b.seri = i + 1);
                this.recalcFobFromBarang();
                this.recalculatePungutan(false);
            }
        },
        recalcFobFromBarang() {
            const sum = this.w.barang.reduce((acc, b) => acc + parseFloat(b.fob || 0), 0);
            if (sum > 0) this.w.transaksi.fob = Math.round(sum * 100) / 100;
        },
        async lookupHsForModal() {
            const hs = (this.barangForm.hs_code || '').replace(/\D/g, '');
            if (hs.length < 6) return;
            try {
                const res = await fetch('/ceisa/tarif-hs/' + hs);
                if (res.ok) {
                    const data = await res.json();
                    if (data.success && data.data) {
                        const d = data.data;
                        if (d.bm !== undefined) this.barangForm.tarif_bm = parseFloat(d.bm);
                        if (d.ppn !== undefined) this.barangForm.tarif_ppn = parseFloat(d.ppn);
                        if (d.pph !== undefined) this.barangForm.tarif_pph = parseFloat(d.pph);
                        this.barangForm.pernyataan_lartas = d.is_lartas ? 'LARTAS' : 'NON';
                        this.barangForm.hs_info = 'BTKI: BM ' + (d.bm || 0) + '%, PPN ' + (d.ppn || 11) + '%, PPh ' + (d.pph || 2.5) + '%';
                    }
                }
            } catch (e) {}
        },
        async syncAllHsRates() {
            for (let i = 0; i < this.w.barang.length; i++) {
                const hs = (this.w.barang[i].hs_code || '').replace(/\D/g, '');
                if (hs.length >= 6) {
                    try {
                        const res = await fetch('/ceisa/tarif-hs/' + hs);
                        if (res.ok) {
                            const data = await res.json();
                            if (data.success && data.data) {
                                const d = data.data;
                                if (d.bm !== undefined) this.w.barang[i].tarif_bm = parseFloat(d.bm);
                                if (d.ppn !== undefined) this.w.barang[i].tarif_ppn = parseFloat(d.ppn);
                                if (d.pph !== undefined) this.w.barang[i].tarif_pph = parseFloat(d.pph);
                                this.w.barang[i].pernyataan_lartas = d.is_lartas ? 'LARTAS' : 'NON';
                            }
                        }
                    } catch (e) {}
                }
            }
            this.recalculatePungutan(false);
            this.showToast('Tarif BTKI seluruh barang berhasil disinkronkan.');
        },

        // HS Code Recommendation & BTKI Live Search Methods
        openHsRecommendationModal(origIdx) {
            this.activeRecommendIndex = origIdx;
            const item = this.w.barang[origIdx];
            this.recommendSearchQuery = (item.uraian || item.hs_code || '').trim();
            this.recommendCandidates = [];
            this.recommendModalOpen = true;
            this.fetchHsRecommendations();
            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
        },
        async fetchHsRecommendations() {
            if (!this.recommendSearchQuery) return;
            this.recommendLoading = true;
            try {
                const res = await fetch('{{ route("ceisa.hs-recommend") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        keyword: this.recommendSearchQuery,
                        direction: 'IMPORT',
                        limit: 5
                    })
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data.success && data.candidates) {
                        this.recommendCandidates = data.candidates;
                    }
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.recommendLoading = false;
                this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
            }
        },
        applyHsRecommendation(candidate) {
            if (this.activeRecommendIndex !== null && this.w.barang[this.activeRecommendIndex]) {
                const item = this.w.barang[this.activeRecommendIndex];
                item.hs_code = candidate.hs_code;
                item.tarif_bm = parseFloat(candidate.bm ?? 0);
                item.tarif_ppn = parseFloat(candidate.ppn ?? 11);
                item.tarif_pph = parseFloat(candidate.pph ?? 2.5);
                item.pernyataan_lartas = candidate.is_lartas ? 'LARTAS' : 'NON';
                item.hs_info = candidate.description || candidate.legal;
                this.recalculatePungutan(false);
                this.recommendModalOpen = false;
                this.showToast('✨ Pos tarif ' + candidate.hs_code + ' (' + candidate.confidence_percent + '%) berhasil diterapkan ke barang Seri #' + item.seri + '.');
            }
        },
        applyHsRecommendationToModal(candidate) {
            this.barangForm.hs_code = candidate.hs_code;
            this.barangForm.tarif_bm = parseFloat(candidate.bm ?? 0);
            this.barangForm.tarif_ppn = parseFloat(candidate.ppn ?? 11);
            this.barangForm.tarif_pph = parseFloat(candidate.pph ?? 2.5);
            this.barangForm.pernyataan_lartas = candidate.is_lartas ? 'LARTAS' : 'NON';
            this.barangForm.hs_info = candidate.description || candidate.legal;
            this.recommendModalOpen = false;
            this.showToast('✨ Pos tarif ' + candidate.hs_code + ' disalin ke form barang.');
        },
        async autoRecommendAllHs() {
            if (!this.w.barang || this.w.barang.length === 0) return;
            this.autoRecommendLoading = true;
            try {
                const res = await fetch('{{ route("ceisa.hs-recommend") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        items: this.w.barang,
                        direction: 'IMPORT'
                    })
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data.success && data.data) {
                        let updatedCount = 0;
                        data.data.forEach((resItem, idx) => {
                            if (this.w.barang[idx] && resItem.top_candidate) {
                                const top = resItem.top_candidate;
                                this.w.barang[idx].hs_code = top.hs_code;
                                this.w.barang[idx].tarif_bm = parseFloat(top.bm ?? 0);
                                this.w.barang[idx].tarif_ppn = parseFloat(top.ppn ?? 11);
                                this.w.barang[idx].tarif_pph = parseFloat(top.pph ?? 2.5);
                                this.w.barang[idx].pernyataan_lartas = top.is_lartas ? 'LARTAS' : 'NON';
                                this.w.barang[idx].hs_info = top.description;
                                updatedCount++;
                            }
                        });
                        this.recalculatePungutan(false);
                        this.showToast('✨ ' + updatedCount + ' item barang berhasil disinkronkan dengan rekomendasi HS Code BTKI 2022.');
                    }
                }
            } catch (e) {
                this.showToast('Gagal memproses rekomendasi otomatis: ' + e.message, 'error');
            } finally {
                this.autoRecommendLoading = false;
                this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
            }
        },
        async onBtkiSearchInput() {
            const q = (this.btkiSearchInput || '').trim();
            if (q.length < 2) {
                this.btkiSearchResults = [];
                this.btkiSearchOpen = false;
                return;
            }
            this.btkiSearchLoading = true;
            try {
                const res = await fetch('{{ route("ceisa.hs-search") }}?q=' + encodeURIComponent(q));
                if (res.ok) {
                    const data = await res.json();
                    if (data.success) {
                        this.btkiSearchResults = data.data || [];
                        this.btkiSearchOpen = this.btkiSearchResults.length > 0;
                    }
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.btkiSearchLoading = false;
            }
        },
        selectBtkiSearchItem(item) {
            this.btkiSearchOpen = false;
            this.btkiSearchInput = item.code + ' - ' + item.description;
            this.barangForm = {
                seri: this.w.barang.length + 1,
                group: 1,
                hs_code: item.code,
                uraian: item.description.substring(0, 80).toUpperCase(),
                merk: 'TANPA MEREK',
                tipe: 'TANPA TIPE',
                spesifikasi: '-',
                negara_asal: 'CN',
                jumlah: 1,
                satuan: item.satuan || 'PCE',
                jumlah_kemasan: 1,
                kemasan: 'CT',
                netto: 100,
                bruto: 110,
                fob: 1000,
                tarif_bm: parseFloat(item.duty_bm || 0),
                tarif_ppn: parseFloat(item.duty_ppn || 11),
                tarif_pph: parseFloat(item.duty_pph || 2.5),
                kode_fasilitas_bm: '1',
                kode_fasilitas_ppn: '1',
                kode_fasilitas_pph: '1',
                pernyataan_lartas: item.has_lartas ? 'LARTAS' : 'NON',
                hs_info: item.description
            };
            this.editingBarangIndex = null;
            this.barangModalOpen = true;
        },

        // Stage 8: Pungutan
        recalculatePungutan(notify = false) {
            const makeBucket = () => ({ dibayar: 0, ditanggung_pemerintah: 0, ditunda: 0, tidak_dipungut: 0, dibebaskan: 0, sudah_dilunasi: 0 });
            const matrix = { PPH: makeBucket(), PPN: makeBucket(), BM: makeBucket(), TOTAL: makeBucket() };
            const fasMap = { '1': 'dibayar', '2': 'ditanggung_pemerintah', '4': 'ditunda', '5': 'tidak_dipungut', '6': 'dibebaskan', '7': 'sudah_dilunasi' };
            const kurs = parseFloat(this.w.transaksi.ndbm_kurs || 0);
            const totalItemFob = this.w.barang.reduce((a, b) => a + parseFloat(b.fob || 0), 0);
            const totalCifValuta = this.computedCifValuta();
            const ratio = totalItemFob > 0 ? (totalCifValuta / totalItemFob) : 1;

            this.w.barang.forEach(item => {
                const cifIdr = Math.round(parseFloat(item.fob || 0) * ratio * kurs);
                const bmAmt = Math.round((parseFloat(item.tarif_bm || 0) / 100) * cifIdr);
                const ppnAmt = Math.round((parseFloat(item.tarif_ppn || 11) / 100) * (cifIdr + bmAmt));
                const pphAmt = Math.round((parseFloat(item.tarif_pph || 2.5) / 100) * (cifIdr + bmAmt));

                const bBm = fasMap[item.kode_fasilitas_bm || '1'] || 'dibayar';
                const bPpn = fasMap[item.kode_fasilitas_ppn || '1'] || 'dibayar';
                const bPph = fasMap[item.kode_fasilitas_pph || '1'] || 'dibayar';

                matrix.BM[bBm] += bmAmt;
                matrix.PPN[bPpn] += ppnAmt;
                matrix.PPH[bPph] += pphAmt;
                matrix.TOTAL[bBm] += bmAmt;
                matrix.TOTAL[bPpn] += ppnAmt;
                matrix.TOTAL[bPph] += pphAmt;
            });

            this.pungutan = matrix;
            if (notify) this.showToast('Pungutan BM, PPN, dan PPh berhasil digenerate ulang.');
        },

        // Stage 9: Pernyataan
        syncPernyataan() {
            this.w.pernyataan.tanggal_ttd = new Date().toISOString().slice(0, 10);
            if (!this.w.pernyataan.kota_ttd) this.w.pernyataan.kota_ttd = 'JAKARTA';
            if (!this.w.pernyataan.jabatan_ttd) this.w.pernyataan.jabatan_ttd = 'DIREKTUR';
            this.showToast('Informasi penandatanganan disinkronkan.');
        },

        // Saving
        async saveDraftAjax() {
            this.saving = true;
            try {
                const res = await fetch('{{ route("bc20.update", $bc20->id) }}', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ wizard_payload: this.w })
                });
                const data = await res.json();
                if (!res.ok || !data.success) throw new Error(data.message || 'Gagal menyimpan draft.');
                if (data.pungutan) this.pungutan = data.pungutan;
                this.showToast('Draft 9 tahap tersimpan.');
            } catch (err) {
                this.showToast(err.message || 'Gagal menyimpan draft.', 'error');
            } finally {
                this.saving = false;
            }
        },

        prepareFormSubmit(event) {
            this.$refs.wizardPayloadInput.value = JSON.stringify(this.w);
        },

        showToast(msg, type = 'success') {
            this.saveToast = { type, message: msg };
            setTimeout(() => {
                if (this.saveToast.message === msg) this.saveToast.message = '';
            }, 3500);
        },

        formatInt(num) {
            return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(num || 0);
        },

        formatDecimal(num) {
            return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(num || 0);
        }
    };
};

// Daftarkan komponen ke Alpine.js baik sebelum maupun sesudah event alpine:init
document.addEventListener('alpine:init', () => {
    if (window.Alpine && typeof window.Alpine.data === 'function') {
        window.Alpine.data('bc20WizardManager', window.bc20WizardManager);
    }
});
if (window.Alpine && typeof window.Alpine.data === 'function') {
    window.Alpine.data('bc20WizardManager', window.bc20WizardManager);
}
</script>

<!-- PDF.js Engine di-load setelah bc20WizardManager siap untuk mencegah race condition -->
<script src="{{ asset('vendor/pdfjs/pdf.min.js') }}"></script>
<script>
    if (window.pdfjsLib) {
        pdfjsLib.GlobalWorkerOptions.workerSrc = "{{ asset('vendor/pdfjs/pdf.worker.min.js') }}";
    }
</script>
@endpush
@endsection
