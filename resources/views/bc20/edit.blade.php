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

        <div class="flex flex-wrap items-center gap-2">
            <!-- Toast / Save Status -->
            <template x-if="saveToast.message">
                <div :class="saveToast.type === 'error' ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200'"
                     class="px-3 py-1.5 rounded-xl border text-xs font-semibold flex items-center gap-1.5">
                    <span x-text="saveToast.message"></span>
                </div>
            </template>

            <button type="button" @click="saveDraftAjax()" :disabled="saving"
                    class="btn-apple-gray text-xs px-3.5 py-2 inline-flex items-center gap-1.5 cursor-pointer">
                <i data-lucide="save" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                <span x-text="saving ? 'Menyimpan...' : 'Simpan Draft'"></span>
            </button>

            <button type="button" @click="togglePreview()"
                    :class="showPreview ? 'bg-[#007AFF]/10 text-[#007AFF] border-[#007AFF]/30' : 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-white/10 dark:text-slate-200 dark:border-white/15'"
                    class="px-3.5 py-2 rounded-xl border text-xs font-semibold inline-flex items-center gap-1.5 transition-colors cursor-pointer">
                <i data-lucide="panel-left" class="w-3.5 h-3.5"></i>
                <span x-text="showPreview ? 'Tutup Preview PDF' : 'Buka Preview PDF (' + previewFiles.length + ')'"></span>
            </button>
        </div>
    </div>

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
                    <div class="px-4 py-2 bg-white dark:bg-[#1C1C1E] border-b border-black/[0.06] dark:border-white/[0.06] flex items-center justify-between gap-2 text-xs">
                        <div class="flex items-center gap-1.5">
                            <button type="button" @click="prevPreviewDoc()" :disabled="previewFiles.length <= 1"
                                    class="p-1 rounded-lg border border-slate-200 dark:border-white/15 hover:bg-slate-100 disabled:opacity-40 cursor-pointer">
                                <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                            </button>
                            <span class="font-mono font-semibold text-slate-700 dark:text-slate-200 px-1.5"
                                  x-text="previewFiles.length > 0 ? ('Dokumen ' + (activePreviewIndex + 1) + ' / ' + previewFiles.length) : '0 / 0'"></span>
                            <button type="button" @click="nextPreviewDoc()" :disabled="previewFiles.length <= 1"
                                    class="p-1 rounded-lg border border-slate-200 dark:border-white/15 hover:bg-slate-100 disabled:opacity-40 cursor-pointer">
                                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>

                        <div class="flex items-center gap-1">
                            <select x-show="previewFiles.length > 0" @change="setPreviewIndex(parseInt($event.target.value, 10))" :value="activePreviewIndex"
                                    class="max-w-[160px] px-2 py-1 rounded-lg border border-slate-200 dark:border-white/15 bg-slate-50 dark:bg-[#2C2C2E] text-[11px] font-medium truncate">
                                <template x-for="(pf, pIdx) in previewFiles" :key="pf.id">
                                    <option :value="pIdx" x-text="(pIdx + 1) + '. ' + pf.nama_file"></option>
                                </template>
                            </select>

                            <template x-if="activePreviewFile && activePreviewFile.is_image">
                                <div class="flex items-center gap-1 ml-1">
                                    <button type="button" @click="previewZoom = Math.max(50, previewZoom - 15)" class="px-1.5 py-0.5 rounded border border-slate-200 dark:border-white/15 text-[11px] cursor-pointer">-</button>
                                    <span class="font-mono text-[11px] w-10 text-center" x-text="previewZoom + '%'"></span>
                                    <button type="button" @click="previewZoom = Math.min(200, previewZoom + 15)" class="px-1.5 py-0.5 rounded border border-slate-200 dark:border-white/15 text-[11px] cursor-pointer">+</button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Preview Canvas -->
                    <div class="flex-1 bg-slate-100 dark:bg-[#111113] overflow-auto relative flex items-center justify-center">
                        <template x-if="!activePreviewFile">
                            <div class="text-center p-6 space-y-3">
                                <div class="w-12 h-12 rounded-2xl bg-slate-200/70 dark:bg-white/10 text-slate-400 mx-auto flex items-center justify-center">
                                    <i data-lucide="file-search" class="w-6 h-6"></i>
                                </div>
                                <div class="text-xs font-semibold text-slate-600 dark:text-slate-300">Belum ada file PDF / Gambar yang diunggah</div>
                                <p class="text-[11px] text-slate-400 max-w-xs mx-auto">Unggah Invoice, Packing List, B/L, atau e-COO pada tab Dokumen untuk langsung melihat preview di sini.</p>
                            </div>
                        </template>

                        <template x-if="activePreviewFile && activePreviewFile.is_image">
                            <div class="w-full h-full overflow-auto p-3 flex items-start justify-center">
                                <img :src="activePreviewFile.preview_url" :alt="activePreviewFile.nama_file"
                                     :style="'width: ' + previewZoom + '%; max-width: none;'"
                                     class="rounded-lg shadow-md transition-all">
                            </div>
                        </template>

                        <template x-if="activePreviewFile && !activePreviewFile.is_image && activePreviewFile.is_previewable">
                            <iframe :src="activePreviewFile.preview_url" class="w-full h-full border-0 bg-white"></iframe>
                        </template>

                        <template x-if="activePreviewFile && !activePreviewFile.is_previewable">
                            <div class="text-center p-6 space-y-3">
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

        <!-- Sticky Bottom Wizard Navigation Bar -->
        <div class="fixed bottom-0 inset-x-0 z-30 bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-md border-t border-black/[0.08] dark:border-white/[0.1] px-4 sm:px-8 py-3">
            <div class="max-w-[1600px] mx-auto flex items-center justify-between gap-3">
                <button type="button" @click="prevStep()" :disabled="activeStep <= 1"
                        class="px-4 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] text-xs font-semibold text-slate-700 dark:text-slate-200 disabled:opacity-40 inline-flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    <span>Sebelumnya</span>
                </button>

                <button type="button" @click="togglePreview()"
                        :class="showPreview ? 'border-slate-300 dark:border-white/20 text-slate-700 dark:text-slate-200' : 'border-[#007AFF]/40 bg-[#007AFF]/10 text-[#007AFF]'"
                        class="px-4 py-2 rounded-xl border text-xs font-semibold inline-flex items-center gap-2 cursor-pointer">
                    <i data-lucide="eye" class="w-4 h-4"></i>
                    <span x-text="showPreview ? 'Tutup PDF' : 'Buka PDF (' + previewFiles.length + ')'"></span>
                </button>

                <div class="flex items-center gap-2">
                    <button type="button" x-show="activeStep < 9" @click="nextStep()"
                            class="px-5 py-2 rounded-xl bg-[#007AFF] hover:bg-[#0066D6] text-white text-xs font-bold inline-flex items-center gap-1.5 shadow-sm cursor-pointer">
                        <span>Selanjutnya</span>
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </button>

                    <button type="submit" x-show="activeStep === 9"
                            class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold inline-flex items-center gap-1.5 shadow-sm cursor-pointer">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        <span>Simpan & Selesai</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function bc20WizardManager() {
    const initialWizard = @json($wizardState);
    const initialFiles = @json($previewFiles);
    const docLabels = @json($refOptions['kode_dokumen']);

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
        saveToast: { type: 'success', message: '' },

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

        init() {
            this.ensureAtLeastOneBarang();
            this.recalculatePungutan(false);
        },

        get activePreviewFile() {
            return this.previewFiles[this.activePreviewIndex] || null;
        },

        togglePreview() {
            this.showPreview = !this.showPreview;
            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
        },

        setPreviewIndex(idx) {
            if (idx >= 0 && idx < this.previewFiles.length) {
                this.activePreviewIndex = idx;
                this.showPreview = true;
            }
        },

        prevPreviewDoc() {
            if (this.previewFiles.length <= 1) return;
            this.activePreviewIndex = (this.activePreviewIndex - 1 + this.previewFiles.length) % this.previewFiles.length;
        },

        nextPreviewDoc() {
            if (this.previewFiles.length <= 1) return;
            this.activePreviewIndex = (this.activePreviewIndex + 1) % this.previewFiles.length;
        },

        previewByDocId(docId) {
            const idx = this.previewFiles.findIndex(f => f.id === docId);
            if (idx !== -1) {
                this.activePreviewIndex = idx;
                this.showPreview = true;
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
}
</script>
@endpush
@endsection
