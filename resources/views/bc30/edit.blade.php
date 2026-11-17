@extends('layouts.app')

@section('title', 'Wizard Edit Dokumen BC 3.0 (PEB) - ' . $bc30->nomor_aju)

@section('content')
<script>
window.bc30WizardManager = function bc30WizardManager() {
    const initialWizard = @json($wizardState);
    const initialFiles = @json($previewFiles);
    const docLabels = @json($refOptions['kode_dokumen']);

    let rawPdfDoc = null;
    let currentRenderTask = null;

    return {
        activeStep: 1,
        isReadOnly: {{ $isReadOnly ? 'true' : 'false' }},
        canSubmitCeisa: {{ $canSubmitCeisa ? 'true' : 'false' }},
        isBillingLocked: {{ $isBillingLocked ? 'true' : 'false' }},
        isFromCeisaPull: {{ $isFromCeisaPull ? 'true' : 'false' }},
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
        pdfMode: 'canvas',
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
        bundleUploading: false,
        singleDocUploadingIndex: null,
        modalSaving: false,
        docForm: { kodeDokumen: '380', nomorDokumen: '', tanggalDokumen: '', shipmentDocumentId: '' },

        // Barang search & modal state
        barangSearch: '',
        barangModalOpen: false,
        editingBarangIndex: null,
        barangForm: {},

        // BTKI Database Search State
        btkiSearchInput: '',
        btkiSearchOpen: false,
        btkiSearchLoading: false,
        btkiSearchResults: [],

        init() {
            this.recalculatePungutan();

            if (this.previewFiles && this.previewFiles.length > 0) {
                this.$nextTick(() => {
                    this.loadActivePreviewFile();
                });
            }

            this.$watch('activePreviewIndex', () => {
                this.loadActivePreviewFile();
            });

            this.$watch('showPreview', (val) => {
                if (val && !this.getRawPdfDoc() && this.activePreviewFile?.is_pdf) {
                    this.$nextTick(() => this.loadActivePreviewFile());
                }
            });
        },

        get activePreviewFile() {
            return this.previewFiles[this.activePreviewIndex] || null;
        },

        togglePreview() {
            this.showPreview = !this.showPreview;
        },

        nextPreviewDoc() {
            if (this.previewFiles.length === 0) return;
            this.activePreviewIndex = (this.activePreviewIndex + 1) % this.previewFiles.length;
        },

        prevPreviewDoc() {
            if (this.previewFiles.length === 0) return;
            this.activePreviewIndex = (this.activePreviewIndex - 1 + this.previewFiles.length) % this.previewFiles.length;
        },

        selectDocForPreview(doc) {
            if (!this.previewFiles || this.previewFiles.length === 0) return;
            if (doc.shipmentDocumentId) {
                const foundIdx = this.previewFiles.findIndex(f => f.id === doc.shipmentDocumentId);
                if (foundIdx !== -1) {
                    this.activePreviewIndex = foundIdx;
                    this.showPreview = true;
                    return;
                }
            }
            this.showPreview = true;
        },

        isDocActiveInPreview(doc) {
            if (!this.activePreviewFile) return false;
            return doc.shipmentDocumentId === this.activePreviewFile.id;
        },

        loadActivePreviewFile() {
            const file = this.activePreviewFile;
            if (!file) return;

            this.pdfError = null;
            this.pdfPageNum = 1;
            this.pdfTotalPages = 0;
            this.pdfRotate = 0;
            this.previewZoom = 100;

            if (file.is_pdf && this.pdfMode === 'canvas') {
                this.initPdfJsCanvas(file.preview_url);
            }
        },

        async initPdfJsCanvas(url) {
            const canvas = document.getElementById('bc30PdfCanvas');
            if (!canvas) {
                setTimeout(() => this.initPdfJsCanvas(url), 120);
                return;
            }

            if (typeof pdfjsLib === 'undefined') {
                this.pdfMode = 'native';
                return;
            }

            pdfjsLib.GlobalWorkerOptions.workerSrc = '{{ asset("vendor/pdfjs/pdf.worker.min.js") }}';

            this.pdfLoading = true;
            this.pdfError = null;

            if (currentRenderTask) {
                try { currentRenderTask.cancel(); } catch (e) {}
                currentRenderTask = null;
            }

            try {
                const loadingTask = pdfjsLib.getDocument({
                    url: url,
                    cMapUrl: '{{ asset("vendor/pdfjs/cmaps/") }}/',
                    cMapPacked: true,
                });
                rawPdfDoc = await loadingTask.promise;
                window.__currentPdfDoc = rawPdfDoc;
                this.pdfTotalPages = rawPdfDoc.numPages;
                this.pdfPageNum = 1;
                this.pdfLoading = false;
                this.renderPdfPage(1);
            } catch (err) {
                if (err?.name === 'RenderingCancelledException') return;
                console.warn('PDF.js Canvas error, fallback ke native:', err);
                this.pdfLoading = false;
                this.pdfError = 'Pratinjau canvas gagal dimuat: ' + (err.message || 'Format tidak didukung');
                this.pdfMode = 'native';
            }
        },

        async renderPdfPage(num) {
            const pdf = this.getRawPdfDoc();
            if (!pdf) return;

            const canvas = document.getElementById('bc30PdfCanvas');
            if (!canvas) return;

            if (currentRenderTask) {
                try { currentRenderTask.cancel(); } catch (e) {}
                currentRenderTask = null;
            }

            this.pdfRendering = true;
            try {
                const page = await pdf.getPage(num);
                const viewport = page.getViewport({ scale: this.pdfScale, rotation: this.pdfRotate });
                const ctx = canvas.getContext('2d');

                canvas.height = viewport.height;
                canvas.width = viewport.width;

                const renderContext = { canvasContext: ctx, viewport: viewport };
                currentRenderTask = page.render(renderContext);
                await currentRenderTask.promise;
                currentRenderTask = null;
                this.pdfRendering = false;

                if (this.pdfPagePending !== null) {
                    const p = this.pdfPagePending;
                    this.pdfPagePending = null;
                    this.renderPdfPage(p);
                }
            } catch (err) {
                if (err?.name === 'RenderingCancelledException') return;
                this.pdfRendering = false;
            }
        },

        prevPdfPage() {
            if (this.pdfPageNum <= 1) return;
            this.pdfPageNum--;
            this.renderPdfPage(this.pdfPageNum);
        },

        nextPdfPage() {
            if (this.pdfPageNum >= this.pdfTotalPages) return;
            this.pdfPageNum++;
            this.renderPdfPage(this.pdfPageNum);
        },

        zoomPdf(delta) {
            const newScale = Math.max(0.6, Math.min(2.5, this.pdfScale + delta));
            this.pdfScale = Math.round(newScale * 100) / 100;
            this.renderPdfPage(this.pdfPageNum);
        },

        rotatePdf() {
            this.pdfRotate = (this.pdfRotate + 90) % 360;
            this.renderPdfPage(this.pdfPageNum);
        },

        // Upload berkas fisik dokumen langsung dari wizard
        async handleWizardDocUpload(event, purpose = 'dokumen') {
            const file = event.target.files?.[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('file', file);
            formData.append('purpose', purpose);
            formData.append('kode_dokumen', purpose === 'dokumen' ? '380' : '705');
            formData.append('nomor_dokumen', file.name.replace(/\.[^/.]+$/, ''));
            formData.append('tanggal_dokumen', new Date().toISOString().slice(0, 10));

            try {
                const res = await fetch('{{ route("bc30.upload-document", $bc30->id) }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: formData
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    this.previewFiles.unshift(data.preview_file);
                    this.activePreviewIndex = 0;
                    this.showPreview = true;
                    if (data.doc_entry) {
                        this.w.dokumen.push(data.doc_entry);
                        this.sortDocumentsAuto();
                    }
                    this.showToast('Dokumen berhasil diunggah.');
                } else {
                    throw new Error(data.message || 'Gagal mengunggah dokumen.');
                }
            } catch (err) {
                this.showToast(err.message || 'Gagal mengunggah dokumen.', 'error');
            } finally {
                event.target.value = '';
            }
        },

        // Navigasi langkah wizard (1 - 9)
        setStep(step) {
            if (step >= 1 && step <= 9) {
                this.activeStep = step;
                window.scrollTo({ top: 320, behavior: 'smooth' });
            }
        },

        nextStep() {
            if (this.activeStep < 9) this.setStep(this.activeStep + 1);
        },

        prevStep() {
            if (this.activeStep > 1) this.setStep(this.activeStep - 1);
        },

        // BTKI Database Autocomplete
        async onBtkiSearchInput() {
            const query = (this.btkiSearchInput || '').trim();
            if (query.length < 2) {
                this.btkiSearchResults = [];
                this.btkiSearchOpen = false;
                return;
            }
            this.btkiSearchLoading = true;
            this.btkiSearchOpen = true;
            try {
                const res = await fetch('{{ route("ceisa.hs-search") }}?q=' + encodeURIComponent(query));
                const data = await res.json();
                this.btkiSearchResults = data.data || [];
            } catch (err) {
                this.btkiSearchResults = [];
            } finally {
                this.btkiSearchLoading = false;
            }
        },

        selectBtkiSearchItem(item) {
            this.btkiSearchOpen = false;
            this.openNewBarangModal({
                hs_code: item.code,
                uraian: item.description,
                tarif_bk: 0.0,
            });
        },

        // Kalkulasi Keuangan Ekspor
        computedFobRupiah() {
            const fob = parseFloat(this.w.transaksi.fob) || 0;
            const kurs = parseFloat(this.w.transaksi.ndbm_kurs) || 0;
            return Math.round(fob * kurs);
        },

        computedTotalBk() {
            let total = 0;
            const kurs = parseFloat(this.w.transaksi.ndbm_kurs) || 0;
            this.w.barang.forEach(b => {
                const itemFobRupiah = (parseFloat(b.fob) || 0) * kurs;
                const tarifBk = parseFloat(b.tarif_bk) || 0;
                total += Math.round((tarifBk / 100) * itemFobRupiah);
            });
            return total;
        },

        recalculatePungutan() {
            let totalFob = 0;
            this.w.barang.forEach(b => {
                totalFob += parseFloat(b.fob) || 0;
            });
            if (totalFob > 0 && (!this.w.transaksi.fob || this.w.transaksi.fob === 0)) {
                this.w.transaksi.fob = Math.round(totalFob * 100) / 100;
            }
        },

        // Modal Barang
        openNewBarangModal(prefill = {}) {
            this.editingBarangIndex = null;
            this.barangForm = {
                seri: this.w.barang.length + 1,
                hs_code: prefill.hs_code || '09011110',
                uraian: prefill.uraian || '',
                merk: 'INDONESIAN ORIGIN',
                tipe: 'GRADE A',
                jumlah: 1,
                satuan: 'KGM',
                jumlah_kemasan: 1,
                kemasan: 'BG',
                netto: 1,
                fob: 100,
                tarif_bk: prefill.tarif_bk || 0.0,
            };
            this.barangModalOpen = true;
        },

        editBarangModal(index) {
            this.editingBarangIndex = index;
            this.barangForm = JSON.parse(JSON.stringify(this.w.barang[index]));
            this.barangModalOpen = true;
        },

        saveBarangModal() {
            if (this.editingBarangIndex !== null) {
                this.w.barang[this.editingBarangIndex] = JSON.parse(JSON.stringify(this.barangForm));
            } else {
                this.w.barang.push(JSON.parse(JSON.stringify(this.barangForm)));
            }
            this.barangModalOpen = false;
            this.recalculatePungutan();
            this.showToast('Data barang berhasil disimpan.');
        },

        removeBarang(index) {
            if (this.w.barang.length > 1) {
                this.w.barang.splice(index, 1);
                this.recalculatePungutan();
                this.showToast('Barang berhasil dihapus.');
            }
        },

        // Modal Dokumen & Deteksi Otomatis Ekspor
        openNewDocModal() {
            this.editingDocIndex = null;
            this.docForm = {
                kodeDokumen: '380',
                nomorDokumen: '',
                tanggalDokumen: new Date().toISOString().slice(0, 10),
                shipmentDocumentId: ''
            };
            if (this.$refs.docModalFileInput) {
                this.$refs.docModalFileInput.value = '';
            }
            this.docModalOpen = true;
        },

        editDocModal(index) {
            this.editingDocIndex = index;
            this.docForm = JSON.parse(JSON.stringify(this.w.dokumen[index]));
            if (this.$refs.docModalFileInput) {
                this.$refs.docModalFileInput.value = '';
            }
            this.docModalOpen = true;
        },

        setDocDateToday() {
            this.docForm.tanggalDokumen = new Date().toISOString().slice(0, 10);
        },

        setDocDateYesterday() {
            const d = new Date();
            d.setDate(d.getDate() - 1);
            this.docForm.tanggalDokumen = d.toISOString().slice(0, 10);
        },

        setDocDateSevenDaysAgo() {
            const d = new Date();
            d.setDate(d.getDate() - 7);
            this.docForm.tanggalDokumen = d.toISOString().slice(0, 10);
        },

        async saveDocModal(keepOpen = false) {
            if (!this.docForm.nomorDokumen || !this.docForm.nomorDokumen.trim()) {
                this.showToast('Nomor dokumen wajib diisi.', 'error');
                return;
            }

            const fileInput = this.$refs.docModalFileInput;
            const file = fileInput && fileInput.files && fileInput.files[0];

            if (file) {
                this.modalSaving = true;
                try {
                    const fd = new FormData();
                    fd.append('file', file);
                    fd.append('kode_dokumen', this.docForm.kodeDokumen);
                    fd.append('nomor_dokumen', this.docForm.nomorDokumen.trim());
                    fd.append('tanggal_dokumen', this.docForm.tanggalDokumen);
                    fd.append('purpose', 'dokumen');

                    const res = await fetch('{{ route("bc30.upload-document", $bc30->id) }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: fd
                    });
                    const data = await res.json();
                    if (!res.ok || !data.success) throw new Error(data.message || 'Gagal mengunggah lampiran berkas.');

                    if (data.preview_file) {
                        this.previewFiles.unshift(data.preview_file);
                        this.activePreviewIndex = 0;
                        this.showPreview = true;
                        this.docForm.shipmentDocumentId = data.preview_file.id;
                    }
                } catch (err) {
                    this.showToast(err.message || 'Gagal mengunggah berkas lampiran.', 'error');
                    this.modalSaving = false;
                    return;
                } finally {
                    this.modalSaving = false;
                    if (fileInput) fileInput.value = '';
                }
            }

            if (this.editingDocIndex !== null) {
                this.w.dokumen[this.editingDocIndex] = JSON.parse(JSON.stringify(this.docForm));
            } else {
                this.docForm.seriDokumen = this.w.dokumen.length + 1;
                this.w.dokumen.push(JSON.parse(JSON.stringify(this.docForm)));
            }

            if (keepOpen) {
                this.showToast('Dokumen berhasil ditambahkan. Silakan isi dokumen berikutnya.');
                this.docForm.nomorDokumen = '';
                this.docForm.shipmentDocumentId = '';
                if (fileInput) fileInput.value = '';
            } else {
                this.docModalOpen = false;
                this.showToast('Dokumen berhasil disimpan.');
            }
            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
        },

        removeDoc(index) {
            this.w.dokumen.splice(index, 1);
            this.w.dokumen.forEach((d, i) => d.seriDokumen = i + 1);
            this.showToast('Dokumen berhasil dihapus.');
        },

        sortDocumentsAuto() {
            const priority = { '380': 1, '217': 2, '705': 3, '740': 3, '861': 4, '430': 5 };
            this.w.dokumen.sort((a, b) => (priority[a.kodeDokumen] || 99) - (priority[b.kodeDokumen] || 99));
            this.w.dokumen.forEach((d, i) => d.seriDokumen = i + 1);
            this.showToast('Dokumen pelengkap diurutkan otomatis.');
        },

        // Unggah Bundle Berkas Ekspor (Multi-File / ZIP) dengan Auto-Matching Cerdas
        async handleWizardBundleUpload(event) {
            const files = event.target.files;
            if (!files || files.length === 0) return;

            const fd = new FormData();
            for (let i = 0; i < files.length; i++) {
                fd.append('bundle_files[]', files[i]);
            }
            fd.append('current_dokumen', JSON.stringify(this.w.dokumen));

            this.bundleUploading = true;
            try {
                const res = await fetch('{{ route("bc30.upload-wizard-bundle", $bc30->id) }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: fd
                });
                const data = await res.json();
                if (!res.ok || !data.success) throw new Error(data.message || 'Gagal memproses bundle dokumen.');

                if (data.preview_files) {
                    this.previewFiles = data.preview_files;
                    if (this.previewFiles.length > 0) {
                        this.activePreviewIndex = 0;
                        this.showPreview = true;
                    }
                }
                if (data.dokumen) {
                    this.w.dokumen = data.dokumen;
                }

                this.showToast(data.message || 'Bundle dokumen ekspor berhasil diproses.');
            } catch (err) {
                this.showToast(err.message || 'Gagal memproses bundle berkas ekspor.', 'error');
            } finally {
                this.bundleUploading = false;
                event.target.value = '';
                this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
            }
        },

        // Re-upload / Ganti Berkas PDF Khusus Baris Dokumen Ekspor Tertentu
        async handleSingleDocReupload(idx, event) {
            const file = event.target.files[0];
            if (!file) return;

            const targetDoc = this.w.dokumen[idx];
            const fd = new FormData();
            fd.append('file', file);
            fd.append('purpose', 'dokumen');
            fd.append('kode_dokumen', targetDoc ? targetDoc.kodeDokumen : '380');
            fd.append('nomor_dokumen', targetDoc ? targetDoc.nomorDokumen : '');
            fd.append('tanggal_dokumen', targetDoc ? targetDoc.tanggalDokumen : '');
            fd.append('doc_index', idx);

            this.singleDocUploadingIndex = idx;
            try {
                const res = await fetch('{{ route("bc30.upload-document", $bc30->id) }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: fd
                });
                const data = await res.json();
                if (!res.ok || !data.success) throw new Error(data.message || 'Gagal mengunggah berkas.');

                if (data.preview_file) {
                    const existingIdx = this.previewFiles.findIndex(f => f.id === data.preview_file.id);
                    if (existingIdx !== -1) {
                        this.previewFiles[existingIdx] = data.preview_file;
                        this.activePreviewIndex = existingIdx;
                    } else {
                        this.previewFiles.unshift(data.preview_file);
                        this.activePreviewIndex = 0;
                    }
                    this.showPreview = true;
                }

                if (this.w.dokumen[idx]) {
                    this.w.dokumen[idx].shipmentDocumentId = data.preview_file ? data.preview_file.id : (data.doc_entry ? data.doc_entry.shipmentDocumentId : null);
                }

                this.showToast('Berkas fisik untuk seri #' + (idx + 1) + ' berhasil diperbarui.');
            } catch (err) {
                this.showToast(err.message || 'Gagal mengunggah berkas revisi.', 'error');
            } finally {
                this.singleDocUploadingIndex = null;
                event.target.value = '';
                this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
            }
        },

        copyEksportirToPemilik() {
            this.w.entitas.pemilik_npwp = this.w.entitas.eksportir_npwp;
            this.w.entitas.pemilik_nama = this.w.entitas.eksportir_nama;
            this.w.entitas.pemilik_alamat = this.w.entitas.eksportir_alamat;
            this.showToast('Data Pemilik disamakan dengan Eksportir.');
        },

        syncPernyataan() {
            this.w.pernyataan.nama_ttd = this.w.entitas.eksportir_nama || 'DIREKTUR';
            this.w.pernyataan.tanggal_ttd = new Date().toISOString().slice(0, 10);
            this.w.pernyataan.kota_ttd = 'JAKARTA';
            this.w.pernyataan.jabatan_ttd = 'DIREKTUR';
            this.showToast('Informasi penandatanganan disinkronkan.');
        },

        async saveDraftAjax() {
            if (this.isReadOnly) {
                this.showToast('Dokumen dalam Mode Inspeksi (Read-Only). Perubahan tidak dapat disimpan ke CEISA.', 'warning');
                return;
            }
            this.saving = true;
            try {
                const res = await fetch('{{ route("bc30.update", $bc30->id) }}', {
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
                this.showToast('Draft 9 tahap PEB tersimpan.');
            } catch (err) {
                this.showToast(err.message || 'Gagal menyimpan draft.', 'error');
            } finally {
                this.saving = false;
            }
        },

        prepareFormSubmit(event) {
            if (this.isReadOnly) {
                event.preventDefault();
                window.location.href = '{{ route("bc30.show", $bc30->id) }}';
                return;
            }
            this.$refs.wizardPayloadInput.value = JSON.stringify(this.w);
        },

        showToast(msg, type = 'success') {
            this.saveToast = { type, message: msg };
            if (window.SwalToast) {
                window.SwalToast.fire({
                    icon: type === 'error' ? 'error' : (type === 'warning' ? 'warning' : 'success'),
                    title: msg
                });
            }
            setTimeout(() => {
                if (this.saveToast.message === msg) this.saveToast.message = '';
            }, 3500);
        },

        formatNumber(num) {
            return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num || 0);
        },

        formatInt(num) {
            return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(num || 0);
        },

        formatHsCode(hs) {
            const clean = String(hs || '').replace(/[^0-9]/g, '');
            if (clean.length === 8) {
                return clean.substring(0, 4) + '.' + clean.substring(4, 6) + '.' + clean.substring(6, 8);
            }
            return hs;
        },

        getDocTypeLabel(code) {
            return docLabels[code] || ('Dokumen Kode ' + code);
        }
    };
};
</script>

<div class="max-w-[1780px] mx-auto px-3 sm:px-5 space-y-4 pt-3 pb-32" x-data="bc30WizardManager()" x-init="init()">
    <!-- Header Toolbar -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-4 sm:p-5 border border-black/[0.06] dark:border-white/[0.08] shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div>
            <nav class="flex items-center gap-1.5 text-xs text-slate-500 font-medium mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#0284c7]">Dashboard</a>
                <span>/</span>
                <a href="{{ route('bc30.index') }}" class="hover:text-[#0284c7]">BC 3.0 (PEB)</a>
                <span>/</span>
                <span class="text-slate-800 dark:text-slate-200 font-mono font-bold">{{ $bc30->nomor_aju }}</span>
            </nav>
            <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-lg font-bold text-slate-900 dark:text-white">Wizard Pengajuan Dokumen BC 3.0 (PEB)</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-[#0284c7]/10 text-[#0284c7]">
                    9 TAHAP CEISA 4.0
                </span>
                @if($isReadOnly)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                        MODE INSPEKSI (READ-ONLY)
                    </span>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2">
            <!-- Simpan Draft Ajax -->
            <button type="button" @click="saveDraftAjax()" :disabled="saving || isReadOnly"
                    x-show="!isReadOnly"
                    class="w-9 h-9 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] hover:bg-slate-100 dark:hover:bg-white/10 text-slate-700 dark:text-slate-200 inline-flex items-center justify-center transition-colors cursor-pointer shadow-2xs"
                    title="Simpan Draft">
                <template x-if="!saving">
                    <i data-lucide="save" class="w-4 h-4 text-[#0284c7]"></i>
                </template>
                <template x-if="saving">
                    <div class="w-4 h-4 border-2 border-[#0284c7] border-t-transparent rounded-full animate-spin"></div>
                </template>
            </button>

            <!-- Toggle Preview PDF -->
            <button type="button" @click="togglePreview()"
                    :class="showPreview ? 'bg-[#0284c7]/10 text-[#0284c7] border-[#0284c7]/30' : 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-white/10 dark:text-slate-200 dark:border-white/15'"
                    class="w-9 h-9 rounded-xl border inline-flex items-center justify-center transition-colors cursor-pointer shadow-2xs"
                    :title="showPreview ? 'Tutup Panel Preview' : 'Buka Panel Preview (' + previewFiles.length + ')'">
                <i data-lucide="panel-left" class="w-4 h-4"></i>
            </button>
        </div>
    </div>

    <!-- Stepper Waterfall Pabean & Tracking Dokumen Terpadu -->
    @include('bc20.components.waterfall_stepper', [
        'activeStage' => 4,
        'bc30' => $bc30,
        'shipment' => $shipment,
    ])

    <!-- Mode Notice Banners -->
    @if($isReadOnly)
        <div class="p-4 rounded-2xl bg-amber-50/90 dark:bg-amber-950/40 border border-amber-300/80 dark:border-amber-800/60 text-amber-950 dark:text-amber-200 text-xs flex items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-3">
                <i data-lucide="lock" class="w-5 h-5 text-amber-600 shrink-0"></i>
                <div>
                    <strong class="font-bold text-sm">Mode Inspeksi &amp; Arsip Dokumen Pabean (Read-Only)</strong>
                    <p class="text-[11px] text-amber-800 dark:text-amber-300 mt-0.5">Dokumen telah berstatus Billing / Respon Resmi NPE di CEISA DJBC. Seluruh 9 tahapan pabean ekspor dan berkas dokumen dapat ditinjau. Pengubahan data pabean dinonaktifkan, namun Anda tetap dapat mengunggah berkas fisik pelengkap.</p>
                </div>
            </div>
            <span class="px-2.5 py-1 rounded-lg bg-amber-200/80 dark:bg-amber-900/60 font-bold font-mono text-[10px] uppercase shrink-0">Terkunci Fiskal</span>
        </div>
    @elseif($isBillingLocked && $isFromCeisaPull)
        <div class="p-4 rounded-2xl bg-sky-50/90 dark:bg-sky-950/40 border border-sky-300/80 dark:border-sky-800/60 text-sky-950 dark:text-sky-200 text-xs flex items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-3">
                <i data-lucide="info" class="w-5 h-5 text-[#0284c7] shrink-0"></i>
                <div>
                    <strong class="font-bold text-sm">Data Hasil Tarikan CEISA 4.0 — Mode Penyesuaian Operasional</strong>
                    <p class="text-[11px] text-sky-800 dark:text-sky-300 mt-0.5">Dokumen ini ditarik langsung dari CEISA DJBC. Anda dapat melengkapi rincian kontainer, kemasan, dokumen pelengkap, dan mengunggah berkas fisik untuk kebutuhan internal di GENIE. Pengiriman ulang ke CEISA dinonaktifkan karena respon resmi telah terbit.</p>
                </div>
            </div>
            <span class="px-2.5 py-1 rounded-lg bg-sky-200/80 dark:bg-sky-900/60 font-bold font-mono text-[10px] uppercase shrink-0">Data Tarikan CEISA</span>
        </div>
    @endif

    <!-- Main Split-Screen Workspace -->
    <form action="{{ route('bc30.update', $bc30->id) }}" method="POST" id="bc30WizardForm" @submit="prepareFormSubmit($event)">
        @csrf
        @method('PUT')
        <input type="hidden" name="wizard_payload" x-ref="wizardPayloadInput">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
            <!-- LEFT PANE: LIVE DOCUMENT PREVIEW -->
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
                                <a :href="activePreviewFile.download_url" target="_blank" class="p-1 rounded-lg border border-slate-200 dark:border-white/15 hover:bg-slate-100 text-slate-700 dark:text-slate-200" title="Unduh Berkas">
                                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                </a>
                                <button type="button" @click="prevPreviewDoc()" :disabled="previewFiles.length <= 1" class="p-1 rounded-lg border border-slate-200 dark:border-white/15 hover:bg-slate-100 disabled:opacity-40 cursor-pointer">
                                    <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                                </button>
                                <span class="text-[11px] font-mono px-1 font-semibold" x-text="(activePreviewIndex + 1) + '/' + previewFiles.length"></span>
                                <button type="button" @click="nextPreviewDoc()" :disabled="previewFiles.length <= 1" class="p-1 rounded-lg border border-slate-200 dark:border-white/15 hover:bg-slate-100 disabled:opacity-40 cursor-pointer">
                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        </template>
                    </div>

                    <!-- Viewer Canvas Area -->
                    <div class="flex-1 bg-slate-100 dark:bg-[#111113] overflow-auto relative flex flex-col items-center justify-start min-h-[580px]">
                        <template x-if="!activePreviewFile">
                            <div class="text-center p-6 space-y-3 my-auto">
                                <div class="w-12 h-12 rounded-2xl bg-slate-200/70 dark:bg-white/10 text-slate-400 mx-auto flex items-center justify-center">
                                    <i data-lucide="file-search" class="w-6 h-6"></i>
                                </div>
                                <div class="text-xs font-semibold text-slate-600 dark:text-slate-300">Belum ada file PDF / Gambar yang diunggah</div>
                                <p class="text-[11px] text-slate-400 max-w-xs mx-auto">Unggah Invoice, Packing List, B/L, atau Draft PEB pada tab Dokumen untuk langsung melihat preview di sini.</p>
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
                                <div x-show="pdfLoading" class="absolute inset-0 bg-white/85 dark:bg-[#1C1C1E]/85 flex flex-col items-center justify-center gap-2.5 z-20">
                                    <div class="w-6 h-6 border-2 border-[#0284c7] border-t-transparent rounded-full animate-spin"></div>
                                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-200">Memuat halaman PDF...</span>
                                </div>

                                <div class="flex-1 overflow-auto p-4 flex items-start justify-center">
                                    <canvas id="bc30PdfCanvas" class="shadow-xl rounded-md bg-white"></canvas>
                                </div>
                            </div>
                        </template>

                        <!-- PDF Viewer: Native Fallback -->
                        <template x-if="activePreviewFile && !activePreviewFile.is_image && activePreviewFile.is_previewable && pdfMode === 'native'">
                            <iframe :src="activePreviewFile.preview_url" class="w-full h-full border-0"></iframe>
                        </template>
                    </div>
                </div>
            </div>

            <!-- RIGHT PANE: 9-STAGE WIZARD FORM -->
            <div :class="showPreview ? 'lg:col-span-7' : 'lg:col-span-12'" class="space-y-4">
                <!-- 9-Stage Tab Navigation Bar -->
                <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-2 border border-black/[0.06] dark:border-white/[0.08] shadow-xs overflow-x-auto">
                    <div class="flex items-center gap-1 min-w-max">
                        <template x-for="s in steps" :key="s.id">
                            <button type="button" @click="setStep(s.id)"
                                    :class="activeStep === s.id 
                                        ? 'bg-[#0284c7] text-white shadow-xs font-bold' 
                                        : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5 font-semibold'"
                                    class="px-3 py-2 rounded-xl text-xs transition-colors cursor-pointer inline-flex items-center gap-1.5 whitespace-nowrap">
                                <span class="w-5 h-5 rounded-full inline-flex items-center justify-center text-[10px] font-mono"
                                      :class="activeStep === s.id ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-slate-300'"
                                      x-text="s.id"></span>
                                <span x-text="s.label"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Stage 1 s/d 9 Contents -->
                <div x-show="activeStep === 1">
                    @include('bc30.wizard._stage_header')
                </div>
                <div x-show="activeStep === 2">
                    @include('bc30.wizard._stage_entitas')
                </div>
                <div x-show="activeStep === 3">
                    @include('bc30.wizard._stage_dokumen')
                </div>
                <div x-show="activeStep === 4">
                    @include('bc30.wizard._stage_pengangkut')
                </div>
                <div x-show="activeStep === 5">
                    @include('bc30.wizard._stage_kemasan')
                </div>
                <div x-show="activeStep === 6">
                    @include('bc30.wizard._stage_transaksi')
                </div>
                <div x-show="activeStep === 7">
                    @include('bc30.wizard._stage_barang')
                </div>
                <div x-show="activeStep === 8">
                    @include('bc30.wizard._stage_pungutan')
                </div>
                <div x-show="activeStep === 9">
                    @include('bc30.wizard._stage_pernyataan')
                </div>

                <!-- Floating Bottom Navigation Bar -->
                <div class="p-3.5 bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-md rounded-2xl border border-black/[0.08] dark:border-white/[0.1] shadow-lg flex items-center justify-between gap-3 sticky bottom-4 z-30">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-mono font-bold text-slate-500">
                            TAHAP <span class="text-[#0284c7]" x-text="activeStep"></span> / 9:
                        </span>
                        <span class="text-xs font-bold text-slate-800 dark:text-white" x-text="steps[activeStep - 1]?.label"></span>
                    </div>

                    <div class="flex items-center gap-2">
                        <template x-if="!isReadOnly">
                            <button type="button" @click="saveDraftAjax()" :disabled="saving"
                                    class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] hover:bg-slate-50 text-slate-700 dark:text-slate-200 font-semibold inline-flex items-center gap-1.5 cursor-pointer text-xs">
                                <template x-if="!saving">
                                    <i data-lucide="save" class="w-3.5 h-3.5 text-[#0284c7]"></i>
                                </template>
                                <template x-if="saving">
                                    <div class="w-3.5 h-3.5 border-2 border-[#0284c7] border-t-transparent rounded-full animate-spin"></div>
                                </template>
                                <span x-text="saving ? 'Menyimpan...' : 'Simpan Draft'"></span>
                            </button>
                        </template>

                        <button type="button" @click="prevStep()" :disabled="activeStep <= 1"
                                class="px-4 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] text-xs font-semibold text-slate-700 dark:text-slate-200 disabled:opacity-40 inline-flex items-center gap-1.5 cursor-pointer">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                            <span>Sebelumnya</span>
                        </button>

                        <template x-if="activeStep < 9">
                            <button type="button" @click="nextStep()"
                                    class="px-5 py-2 rounded-xl bg-[#0284c7] hover:bg-[#0066D6] text-white text-xs font-bold inline-flex items-center gap-1.5 shadow-sm cursor-pointer transition-colors">
                                <span>Selanjutnya</span>
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </button>
                        </template>
                        <template x-if="activeStep === 9 && !isReadOnly">
                            <button type="submit"
                                    class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold inline-flex items-center gap-1.5 shadow-sm cursor-pointer transition-colors">
                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                <span>Simpan &amp; Selesai</span>
                            </button>
                        </template>
                        <template x-if="activeStep === 9 && isReadOnly">
                            <a href="{{ route('bc30.show', $bc30->id) }}"
                               class="px-5 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold inline-flex items-center gap-1.5 shadow-sm cursor-pointer transition-colors">
                                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                                <span>Selesai Meninjau (Kembali)</span>
                            </a>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </form>



    <!-- Modal Form Barang Ekspor -->
    <div x-show="barangModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
        <div class="bg-white dark:bg-[#1C1C1E] rounded-3xl max-w-xl w-full p-6 border border-black/10 dark:border-white/10 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.08]">
                <h3 class="font-bold text-sm text-slate-900 dark:text-white" x-text="editingBarangIndex !== null ? 'Edit Komoditas Barang Ekspor' : 'Tambah Komoditas Barang Ekspor'"></h3>
                <button type="button" @click="barangModalOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-700">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="space-y-3 text-xs">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Pos Tarif (HS Code 8-Digit) <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="barangForm.hs_code" placeholder="Contoh: 09011110" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono font-bold">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tarif Bea Keluar (%)</label>
                        <input type="number" step="0.1" x-model.number="barangForm.tarif_bk" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Uraian Barang Ekspor <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="barangForm.uraian" placeholder="Contoh: BIJI KOPI ARABIKA GAYO GRADE 1" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white uppercase font-semibold">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Merek</label>
                        <input type="text" x-model="barangForm.merk" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white uppercase">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tipe / Kualitas</label>
                        <input type="text" x-model="barangForm.tipe" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white uppercase">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Jumlah Satuan <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.0001" x-model.number="barangForm.jumlah" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Kode Satuan</label>
                        <input type="text" x-model="barangForm.satuan" placeholder="KGM, PCE, UNIT" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white uppercase font-mono font-bold">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Berat Netto (KG)</label>
                        <input type="number" step="0.0001" x-model.number="barangForm.netto" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Jumlah Kemasan</label>
                        <input type="number" x-model.number="barangForm.jumlah_kemasan" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Jenis Kemasan</label>
                        <input type="text" x-model="barangForm.kemasan" placeholder="PK, BG, CT" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white uppercase font-mono font-bold">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nilai FOB Barang</label>
                        <input type="number" step="0.01" x-model.number="barangForm.fob" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono font-bold">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                <button type="button" @click="barangModalOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-slate-100 text-slate-700 font-semibold text-xs">
                    Batal
                </button>
                <button type="button" @click="saveBarangModal()" class="px-4 py-2 rounded-xl bg-[#0284c7] text-white font-semibold text-xs">
                    Simpan Barang
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('vendor/pdfjs/pdf.min.js') }}"></script>
@endpush
@endsection
