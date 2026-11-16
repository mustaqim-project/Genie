@php
    $modalId = $modalId ?? 'modal-create-project';
    $pfx = $pfx ?? '';
    $defaultType = $defaultType ?? '';
    $lockType = $lockType ?? false;
    $defaultAfterAction = $defaultAfterAction ?? 'fill_bc_form';
    $title = $title ?? 'Buka Proyek Pengapalan & Dokumen Pabean Baru';
    $subtitle = $subtitle ?? 'Unggah berkas kargo (B/L, Invoice, Packing List). Sistem mengekstrak informasi dan mencocokkan pos tarif HS Code secara otomatis.';
    $clients = $clients ?? \App\Models\Client::orderBy('name')->get();
    $vendors = $vendors ?? \App\Models\ShippingVendor::orderBy('name')->get();
@endphp

<script>
if (!window.createProjectModalManagerRegistered) {
    window.createProjectModalManagerRegistered = true;
    window.createProjectModalManager = function createProjectModalManager(cfg = {}) {
        const pfx = cfg.pfx || '';
        const defType = cfg.defaultType || '';
        const lock = !!cfg.lockType;
        const defAfter = cfg.defaultAfterAction || 'fill_bc_form';

        return {
            tab: 'ai',
            useAi: false,
            afterAction: defAfter,
            scanning: false,
            scanned: false,
            showDetails: false,
            showSpecialDocs: false,
            bundleFileNames: [],
            detectedBundleFiles: [],
            crossValidation: null,
            fileNames: { bl: '', invoice: '', pl: '', coo: '', insurance: '', lartas: '' },
            detected: {
                client_id: '',
                client_name: '',
                client_npwp: '',
                exporter_name: '',
                exporter_address: '',
                shipping_vendor_id: '',
                shipping_vendor_name: '',
                type: defType,
                bl_number: '',
                invoice_number: '',
                vessel_name: '',
                voyage_number: '',
                port_of_loading: '',
                port_of_discharge: '',
                eta: '',
                etd_date: '',
                commodity_summary: '',
                extraction_mode: 'RULE_ENGINE_NO_AI',
                total_kontainer: 1,
                nomor_kontainer: '',
                total_kemasan: 0,
                jenis_kemasan: '',
                total_bruto: 0,
                total_netto: 0,
                currency: 'USD',
                total_cif: 0,
                total_fob: 0,
                freight: 0,
                ndbm_kurs: 15850,
                items: []
            },
            handleBundleFiles(event) {
                const files = event.target.files;
                this.bundleFileNames = files ? Array.from(files).map(f => f.name) : [];
                if (this.bundleFileNames.length > 0 && ['IMPORT', 'EXPORT'].includes(this.detected.type)) {
                    this.scanFiles();
                }
            },
            updateFileName(type, event) {
                const files = event.target.files;
                if (files && files[0]) {
                    this.fileNames[type] = files[0].name;
                    if ((this.fileNames.bl || this.fileNames.invoice || this.fileNames.pl || this.bundleFileNames.length > 0) && !this.scanned && ['IMPORT', 'EXPORT'].includes(this.detected.type)) {
                        this.scanFiles();
                    }
                }
            },
            async scanFiles() {
                if (!['IMPORT', 'EXPORT'].includes(this.detected.type)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tipe Kepabeanan Wajib',
                        text: 'Wajib memilih Tipe Kepabeanan terlebih dahulu: Impor (BC 2.0) atau Ekspor (BC 3.0).',
                        confirmButtonColor: '#0284c7'
                    });
                    return;
                }
                const bundleInput = document.getElementById(pfx + 'input-bundle-files');
                const blInput = document.getElementById(pfx + 'input-bl-file');
                const invInput = document.getElementById(pfx + 'input-inv-file');
                const plInput = document.getElementById(pfx + 'input-pl-file');
                const cooInput = document.getElementById(pfx + 'input-coo-file');
                const insInput = document.getElementById(pfx + 'input-insurance-file');
                const lartasInput = document.getElementById(pfx + 'input-lartas-file');

                const hasBundle = bundleInput?.files?.length > 0;
                const hasSingle = blInput?.files?.length || invInput?.files?.length || plInput?.files?.length || cooInput?.files?.length || insInput?.files?.length || lartasInput?.files?.length;

                if (!hasBundle && !hasSingle) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Pilih Berkas Dokumen',
                        text: 'Silakan pilih minimal 1 berkas (Bundle Multi-File/ZIP, B/L, Commercial Invoice, atau Packing List) untuk dipindai.',
                        confirmButtonColor: '#0284c7'
                    });
                    return;
                }

                this.scanning = true;
                const formData = new FormData();
                if (hasBundle) {
                    Array.from(bundleInput.files).forEach(f => formData.append('bundle_files[]', f));
                }
                if (blInput?.files?.[0]) formData.append('bl_file', blInput.files[0]);
                if (invInput?.files?.[0]) formData.append('invoice_file', invInput.files[0]);
                if (plInput?.files?.[0]) formData.append('packing_list_file', plInput.files[0]);
                if (cooInput?.files?.[0]) formData.append('coo_file', cooInput.files[0]);
                if (insInput?.files?.[0]) formData.append('insurance_file', insInput.files[0]);
                if (lartasInput?.files?.[0]) formData.append('lartas_file', lartasInput.files[0]);

                formData.append('use_ai', this.useAi ? '1' : '0');
                const chosenType = this.detected.type;
                formData.append('type', chosenType);

                const clientSelect = document.getElementById(pfx + 'smart-client-select');
                if (clientSelect?.value) formData.append('client_id', clientSelect.value);

                try {
                    const response = await fetch('{{ route('core.document.parse-documents') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    });
                    const res = await response.json();
                    if (response.ok && res.status === 'success' && res.data) {
                        this.scanned = true;
                        this.detected = { ...this.detected, ...res.data, type: chosenType };
                        this.detectedBundleFiles = res.data.bundle_files_detected || [];
                        this.crossValidation = res.data.cross_validation || null;

                        if (clientSelect && res.data.client_id) {
                            let opt = clientSelect.querySelector(`option[value='${res.data.client_id}']`);
                            if (!opt) {
                                opt = document.createElement('option');
                                opt.value = res.data.client_id;
                                opt.textContent = `${res.data.client_name} (NPWP: ${res.data.client_npwp || '-'})`;
                                clientSelect.appendChild(opt);
                            }
                            clientSelect.value = res.data.client_id;
                        }
                        if (window.SwalToast) {
                            window.SwalToast.fire({ icon: 'success', title: 'Dokumen berhasil dipindai & diidentifikasi!' });
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Memindai',
                            text: res.message || 'Gagal memindai dokumen.',
                            confirmButtonColor: '#0284c7'
                        });
                    }
                } catch (err) {
                    console.error(err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Kesalahan Koneksi',
                        text: 'Terjadi kesalahan koneksi saat memindai dokumen.',
                        confirmButtonColor: '#0284c7'
                    });
                } finally {
                    this.scanning = false;
                }
            },
            showQuickClientModal: false,
            quickClient: { name: '', npwp: '', pic_name: '', phone: '' },
            quickClientLoading: false,
            quickClientError: '',
            openQuickClient() {
                this.quickClient = { name: '', npwp: '', pic_name: '', phone: '' };
                this.quickClientError = '';
                this.showQuickClientModal = true;
            },
            async submitQuickClient() {
                if (!this.quickClient.name) {
                    this.quickClientError = 'Nama perusahaan / importir wajib diisi.';
                    return;
                }
                this.quickClientLoading = true;
                this.quickClientError = '';
                try {
                    const response = await fetch('{{ route('crm.clients.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(this.quickClient)
                    });
                    const res = await response.json();
                    if (res.status === 'success' && res.data) {
                        const c = res.data;
                        const smartSelect = document.getElementById(pfx + 'smart-client-select');
                        if (smartSelect) {
                            const opt = document.createElement('option');
                            opt.value = c.id;
                            opt.textContent = `${c.name} (NPWP: ${c.npwp || '-'})`;
                            smartSelect.appendChild(opt);
                            smartSelect.value = c.id;
                        }
                        const manualSelect = document.getElementById(pfx + 'manual-client-select');
                        if (manualSelect) {
                            const opt = document.createElement('option');
                            opt.value = c.id;
                            opt.textContent = `${c.name} (NPWP: ${c.npwp || '-'})`;
                            manualSelect.appendChild(opt);
                            manualSelect.value = c.id;
                        }
                        this.detected.client_id = c.id;
                        this.showQuickClientModal = false;
                    } else {
                        this.quickClientError = res.message || 'Gagal menyimpan klien.';
                    }
                } catch (err) {
                    this.quickClientError = 'Terjadi kesalahan sistem saat menyimpan data klien.';
                } finally {
                    this.quickClientLoading = false;
                }
            }
        };
    };

    document.addEventListener('alpine:init', () => {
        if (window.Alpine && typeof window.Alpine.data === 'function') {
            window.Alpine.data('createProjectModalManager', window.createProjectModalManager);
        }
    });
    if (window.Alpine && typeof window.Alpine.data === 'function') {
        window.Alpine.data('createProjectModalManager', window.createProjectModalManager);
    }
}
</script>

<!-- Modal Form Buat Dokumen Pabean (Sama dengan Core Document) -->
<div id="{{ $modalId }}" class="hidden fixed inset-0 z-[200] overflow-y-auto bg-black/60 backdrop-blur-md flex items-center justify-center p-4"
     x-data="createProjectModalManager({ defaultType: '{{ $defaultType }}', lockType: {{ $lockType ? 'true' : 'false' }}, defaultAfterAction: '{{ $defaultAfterAction }}', pfx: '{{ $pfx }}' })">
    <div class="app-modal-dialog app-modal-dialog-xl rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl p-6 text-[13px]">
        
        <!-- Header Modal & Tab Navigasi -->
        <div class="flex items-start justify-between pb-4 border-b border-black/5 dark:border-white/5 mb-4">
            <div>
                <h3 class="text-[17px] font-bold text-black dark:text-white flex items-center gap-2">
                    @if($defaultType === 'IMPORT')
                        <span class="w-2.5 h-2.5 rounded-full bg-[#0284c7]"></span>
                    @elseif($defaultType === 'EXPORT')
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                    @else
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    @endif
                    <span>{{ $title }}</span>
                </h3>
                <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5 leading-snug">
                    {{ $subtitle }}
                </p>
            </div>
            <button type="button" onclick="document.getElementById('{{ $modalId }}').classList.add('hidden')" class="p-1.5 rounded-[8px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white hover:bg-black/[0.05] dark:hover:bg-white/[0.08] transition-colors cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Mode Switcher Tabs (Apple Segmented Pill) -->
        <div class="inline-flex w-full p-1 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 mb-4 font-semibold text-[12px]">
            <button type="button" 
                    @click="tab = 'ai'" 
                    :class="tab === 'ai' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                    class="flex-1 py-1.5 px-3 rounded-[8px] transition-all flex items-center justify-center space-x-2 cursor-pointer">
                <i data-lucide="sparkles" class="w-3.5 h-3.5 text-[#0284c7]"></i>
                <span>Unggah Berkas & Ekstraksi Otomatis</span>
                <span class="px-1.5 py-0.2 bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] font-bold text-[9px] rounded-full">Praktis</span>
            </button>
            <button type="button" 
                    @click="tab = 'manual'" 
                    :class="tab === 'manual' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                    class="flex-1 py-1.5 px-3 rounded-[8px] transition-all flex items-center justify-center space-x-1.5 cursor-pointer">
                <i data-lucide="file-edit" class="w-3.5 h-3.5"></i>
                <span>Form Input Manual</span>
            </button>
        </div>

        <form action="{{ route('core.document.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4"
              @submit="if (!['IMPORT', 'EXPORT'].includes(detected.type)) { $event.preventDefault(); Swal.fire({ icon: 'warning', title: 'Tipe Kepabeanan Wajib', text: 'Wajib pilih Tipe Kepabeanan terlebih dahulu: Impor (BC 2.0) atau Ekspor (BC 3.0).', confirmButtonColor: '#0284c7' }); }">
            @csrf

            <!-- ============================================== -->
            <!-- TAB 1: SMART AI UPLOAD & AUTO-DETEKSI DOKUMEN -->
            <!-- ============================================== -->
            <div x-show="tab === 'ai'" class="space-y-4">

                <!-- Pilih Klien & Jenis Layanan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-[#0284c7]/5 dark:bg-[#0284c7]/10 p-3.5 rounded-[14px] border border-[#0284c7]/20">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block font-semibold text-black dark:text-white text-[12px]">Klien / Pemilik Barang (Importir/Eksportir)</label>
                            <button type="button" @click="openQuickClient()" class="text-[11px] font-semibold text-[#0284c7] hover:underline flex items-center gap-0.5 cursor-pointer">
                                <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                                <span>+ Klien Baru</span>
                            </button>
                        </div>
                        <select name="client_id" id="{{ $pfx }}smart-client-select" x-model="detected.client_id" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#0284c7]">
                            <option value="">-- Auto-Deteksi dari Dokumen atau Pilih --</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} (NPWP: {{ $c->npwp ?? '-' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Tipe Kepabeanan <span class="text-rose-500">*</span></label>
                        @if($lockType && in_array($defaultType, ['IMPORT', 'EXPORT']))
                            <div class="flex items-center gap-2 px-3 py-2 rounded-[10px] border border-[#0284c7]/30 bg-white dark:bg-[#2C2C2E] text-[13px] font-bold text-slate-800 dark:text-white">
                                <span class="w-2 h-2 rounded-full {{ $defaultType === 'IMPORT' ? 'bg-[#0284c7]' : 'bg-indigo-600' }}"></span>
                                <span>{{ $defaultType === 'IMPORT' ? 'IMPOR (BC 2.0 / PIB)' : 'EKSPOR (BC 3.0 / PEB)' }}</span>
                                <span class="ml-auto text-[10px] font-mono px-2 py-0.5 rounded bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60">Terkunci</span>
                            </div>
                            <input type="hidden" name="type" value="{{ $defaultType }}">
                        @else
                            <select name="type" x-model="detected.type" :required="tab === 'ai'"
                                    @change="if (['IMPORT','EXPORT'].includes(detected.type) && !scanned && !scanning && (bundleFileNames.length > 0 || fileNames.bl || fileNames.invoice || fileNames.pl)) { scanFiles(); }"
                                    class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#0284c7]">
                                <option value="">-- Wajib Pilih: Impor atau Ekspor --</option>
                                <option value="IMPORT">IMPOR (BC 2.0 / PIB)</option>
                                <option value="EXPORT">EKSPOR (BC 3.0 / PEB)</option>
                            </select>
                        @endif
                    </div>
                </div>

                <!-- DROPZONE BUNDLE DOKUMEN (MULTI-FILE / ZIP SEKALI UPLOAD) -->
                <div class="p-3.5 rounded-[14px] border-2 border-dashed border-[#0284c7]/40 hover:border-[#0284c7] bg-gradient-to-r from-[#0284c7]/5 via-[#5856D6]/5 to-transparent transition-colors">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-start gap-2.5">
                            <div class="w-9 h-9 rounded-[10px] bg-[#0284c7]/15 text-[#0284c7] flex items-center justify-center shrink-0">
                                <i data-lucide="layers" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <div class="font-bold text-black dark:text-white text-[13px] flex items-center gap-2">
                                    <span>Upload Bundle Dokumen Sekaligus (Multi-File / ZIP)</span>
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">Jalan Tanpa AI</span>
                                </div>
                                <p class="text-[11px] text-black/55 dark:text-white/55 mt-0.5">
                                    Pilih sekaligus BL, Commercial Invoice, Packing List, COO, SC/PO, Asuransi, atau arsip <code>.zip</code> — sistem deteksi otomatis &amp; siap mengisi form PIB/PEB.
                                </p>
                            </div>
                        </div>
                        <div class="shrink-0">
                            <input type="file" id="{{ $pfx }}input-bundle-files" name="bundle_files[]" multiple accept=".pdf,.xlsx,.xls,.csv,.txt,.zip,.png,.jpg,.jpeg" class="hidden" @change="handleBundleFiles($event)">
                            <button type="button" onclick="document.getElementById('{{ $pfx }}input-bundle-files').click()"
                                    class="btn-apple-filled !h-9 !px-3.5 text-[12px] cursor-pointer">
                                <i data-lucide="folder-up" class="w-3.5 h-3.5"></i>
                                <span x-text="bundleFileNames.length ? bundleFileNames.length + ' Berkas Bundle Dipilih' : 'Pilih Bundle Dokumen'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- Daftar Berkas Bundle yang Terklasifikasi Otomatis -->
                    <div x-show="detectedBundleFiles.length > 0" class="mt-3 pt-2.5 border-t border-black/5 dark:border-white/5 grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                        <template x-for="(bf, idx) in detectedBundleFiles" :key="idx">
                            <div class="flex items-center justify-between gap-2 px-2.5 py-1.5 rounded-[8px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 text-[11px]">
                                <div class="min-w-0 flex items-center gap-1.5">
                                    <i data-lucide="file-check" class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i>
                                    <span class="truncate font-medium text-black dark:text-white" x-text="bf.filename"></span>
                                    <span x-show="bf.is_image_only" class="text-[9px] px-1.5 py-0.2 rounded bg-amber-500/15 text-amber-600 font-semibold shrink-0">Scan/Img</span>
                                </div>
                                <select :name="'bundle_classifications[' + bf.filename + ']'" x-model="bf.doc_type"
                                        class="px-2 py-0.5 rounded-[6px] border border-black/10 dark:border-white/10 bg-black/[0.03] dark:bg-white/[0.06] text-[10px] font-bold text-[#0284c7] shrink-0">
                                    <option value="BL">BL / AWB</option>
                                    <option value="INV">Commercial Invoice</option>
                                    <option value="PL">Packing List</option>
                                    <option value="COO">COO / SKA</option>
                                    <option value="SC_PO">SC / PO</option>
                                    <option value="INS">Insurance</option>
                                    <option value="BOOKING_RECEIPT">Booking / NPE</option>
                                    <option value="LARTAS">Izin Lartas</option>
                                    <option value="ETC">ETC</option>
                                </select>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- 3 Dropzone Upload Dokumen Utama (Opsional Per Slot) -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- 1. Bill of Lading (B/L) -->
                    <div class="p-3 rounded-[14px] border-2 border-dashed border-[#0284c7]/30 hover:border-[#0284c7] bg-[#0284c7]/5 transition-colors flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="font-semibold text-black dark:text-white flex items-center space-x-1.5">
                                    <i data-lucide="ship" class="w-4 h-4 text-[#0284c7]"></i>
                                    <span>Bill of Lading (B/L)</span>
                                </span>
                                <span class="text-[9px] px-1.5 py-0.5 rounded-[4px] bg-[#0284c7]/15 text-[#0284c7] font-bold">Wajib</span>
                            </div>
                            <p class="text-[11px] text-black/50 dark:text-white/50 mb-2">Diekstrak: No. BL, Kapal, Pelayaran, Kontainer, Bobot.</p>
                        </div>
                        <div>
                            <input type="file" id="{{ $pfx }}input-bl-file" name="bl_file" accept=".pdf,.png,.jpg,.jpeg,.doc,.docx" class="hidden" @change="updateFileName('bl', $event)">
                            <button type="button" onclick="document.getElementById('{{ $pfx }}input-bl-file').click()" 
                                    class="w-full py-1.5 px-2.5 rounded-[8px] border border-[#0284c7]/30 bg-white dark:bg-[#2C2C2E] text-[#0284c7] font-semibold text-[11px] truncate flex items-center justify-center space-x-1.5 cursor-pointer">
                                <span x-text="fileNames.bl ? '✓ ' + fileNames.bl : '📂 Pilih File B/L'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- 2. Commercial Invoice (C/I) -->
                    <div class="p-3 rounded-[14px] border-2 border-dashed border-[#5856D6]/30 hover:border-[#5856D6] bg-[#5856D6]/5 transition-colors flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="font-semibold text-black dark:text-white flex items-center space-x-1.5">
                                    <i data-lucide="receipt" class="w-4 h-4 text-[#5856D6]"></i>
                                    <span>Commercial Invoice</span>
                                </span>
                                <span class="text-[9px] px-1.5 py-0.5 rounded-[4px] bg-[#5856D6]/15 text-[#5856D6] font-bold">Wajib</span>
                            </div>
                            <p class="text-[11px] text-black/50 dark:text-white/50 mb-2">Diekstrak: Nilai FOB/CIF, Valuta, Pemasok, Termin.</p>
                        </div>
                        <div>
                            <input type="file" id="{{ $pfx }}input-inv-file" name="invoice_file" accept=".pdf,.xlsx,.xls,.png,.jpg,.jpeg,.doc,.docx" class="hidden" @change="updateFileName('invoice', $event)">
                            <button type="button" onclick="document.getElementById('{{ $pfx }}input-inv-file').click()" 
                                    class="w-full py-1.5 px-2.5 rounded-[8px] border border-[#5856D6]/30 bg-white dark:bg-[#2C2C2E] text-[#5856D6] font-semibold text-[11px] truncate flex items-center justify-center space-x-1.5 cursor-pointer">
                                <span x-text="fileNames.invoice ? '✓ ' + fileNames.invoice : '📂 Pilih Commercial Invoice'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- 3. Packing List (P/L) -->
                    <div class="p-3 rounded-[14px] border-2 border-dashed border-[#34C759]/30 hover:border-[#34C759] bg-[#34C759]/5 transition-colors flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="font-semibold text-black dark:text-white flex items-center space-x-1.5">
                                    <i data-lucide="package" class="w-4 h-4 text-[#34C759]"></i>
                                    <span>Packing List (P/L)</span>
                                </span>
                                <span class="text-[9px] px-1.5 py-0.5 rounded-[4px] bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] font-bold">Wajib</span>
                            </div>
                            <p class="text-[11px] text-black/50 dark:text-white/50 mb-2">Diekstrak: Rincian barang, kemasan, klasifikasi HS Code BTKI.</p>
                        </div>
                        <div>
                            <input type="file" id="{{ $pfx }}input-pl-file" name="packing_list_file" accept=".pdf,.xlsx,.xls,.png,.jpg,.jpeg,.doc,.docx" class="hidden" @change="updateFileName('pl', $event)">
                            <button type="button" onclick="document.getElementById('{{ $pfx }}input-pl-file').click()" 
                                    class="w-full py-1.5 px-2.5 rounded-[8px] border border-[#34C759]/30 bg-white dark:bg-[#2C2C2E] text-[#248A3D] dark:text-[#30D158] font-semibold text-[11px] truncate flex items-center justify-center space-x-1.5 cursor-pointer">
                                <span x-text="fileNames.pl ? '✓ ' + fileNames.pl : '📂 Pilih Packing List'"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Collapsible Dokumen Khusus & Perizinan Tambahan -->
                <div class="mt-0.5">
                    <button type="button" @click="showSpecialDocs = !showSpecialDocs" 
                            class="inline-flex items-center space-x-1.5 text-[12px] font-semibold text-[#0284c7] hover:underline transition-colors cursor-pointer">
                        <i :data-lucide="showSpecialDocs ? 'chevron-down' : 'chevron-right'" class="w-3.5 h-3.5"></i>
                        <span>Dokumen Pendukung Khusus & Perizinan Lartas (COO Form E, Polis Asuransi, Izin K/L) - Opsional</span>
                    </button>
                    <div x-show="showSpecialDocs" x-transition class="mt-2.5 grid grid-cols-1 sm:grid-cols-3 gap-3 p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                        <!-- 1. COO / Form E / Form D -->
                        <div class="p-2.5 rounded-[10px] border border-black/5 dark:border-white/5 bg-white dark:bg-[#2C2C2E] flex flex-col justify-between">
                            <div class="mb-1.5">
                                <span class="font-semibold text-black dark:text-white text-[11px] flex items-center space-x-1">
                                    <span>Certificate of Origin (COO)</span>
                                </span>
                                <p class="text-[10px] text-black/50 dark:text-white/50 mt-0.5">Form E / D / AK preferensi tarif.</p>
                            </div>
                            <div>
                                <input type="file" id="{{ $pfx }}input-coo-file" name="coo_file" accept=".pdf,.png,.jpg,.jpeg" class="hidden" @change="updateFileName('coo', $event)">
                                <button type="button" onclick="document.getElementById('{{ $pfx }}input-coo-file').click()" 
                                        class="w-full py-1.5 px-2 rounded-[6px] border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-black/70 dark:text-white/70 font-semibold text-[10px] truncate cursor-pointer">
                                    <span x-text="fileNames.coo ? '✓ ' + fileNames.coo : '📂 Upload Form E / COO'"></span>
                                </button>
                            </div>
                        </div>

                        <!-- 2. Polis Asuransi Pengapalan -->
                        <div class="p-2.5 rounded-[10px] border border-black/5 dark:border-white/5 bg-white dark:bg-[#2C2C2E] flex flex-col justify-between">
                            <div class="mb-1.5">
                                <span class="font-semibold text-black dark:text-white text-[11px] flex items-center space-x-1">
                                    <span>Polis Asuransi Pengapalan</span>
                                </span>
                                <p class="text-[10px] text-black/50 dark:text-white/50 mt-0.5">Marine cargo insurance / nilai pertanggungan.</p>
                            </div>
                            <div>
                                <input type="file" id="{{ $pfx }}input-insurance-file" name="insurance_file" accept=".pdf,.png,.jpg,.jpeg" class="hidden" @change="updateFileName('insurance', $event)">
                                <button type="button" onclick="document.getElementById('{{ $pfx }}input-insurance-file').click()" 
                                        class="w-full py-1.5 px-2 rounded-[6px] border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-black/70 dark:text-white/70 font-semibold text-[10px] truncate cursor-pointer">
                                    <span x-text="fileNames.insurance ? '✓ ' + fileNames.insurance : '📂 Upload Polis Asuransi'"></span>
                                </button>
                            </div>
                        </div>

                        <!-- 3. Surat Perizinan Lartas Khusus -->
                        <div class="p-2.5 rounded-[10px] border border-black/5 dark:border-white/5 bg-white dark:bg-[#2C2C2E] flex flex-col justify-between">
                            <div class="mb-1.5">
                                <span class="font-semibold text-black dark:text-white text-[11px] flex items-center space-x-1">
                                    <span>Izin Lartas / Dokumen Khusus</span>
                                </span>
                                <p class="text-[10px] text-black/50 dark:text-white/50 mt-0.5">LS Kemendag, PI, SNI, SDPPI, Barantan.</p>
                            </div>
                            <div>
                                <input type="file" id="{{ $pfx }}input-lartas-file" name="lartas_file" accept=".pdf,.png,.jpg,.jpeg,.zip" class="hidden" @change="updateFileName('lartas', $event)">
                                <button type="button" onclick="document.getElementById('{{ $pfx }}input-lartas-file').click()" 
                                        class="w-full py-1.5 px-2 rounded-[6px] border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-black/70 dark:text-white/70 font-semibold text-[10px] truncate cursor-pointer">
                                    <span x-text="fileNames.lartas ? '✓ ' + fileNames.lartas : '📂 Upload Izin Lartas'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tombol Pemicu Ekstraksi (Mode Tanpa AI Default + Opsi AI) -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-[8px] bg-[#0284c7]/10 text-[#0284c7] flex items-center justify-center shrink-0">
                            <i data-lucide="cpu" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-black dark:text-white text-[13px] flex items-center gap-2">
                                <span>Document Parser &amp; BTKI Classifier</span>
                                <label class="inline-flex items-center gap-1.5 text-[11px] font-medium px-2 py-0.5 rounded-full bg-black/[0.04] dark:bg-white/[0.08] cursor-pointer">
                                    <input type="checkbox" x-model="useAi" class="rounded border-black/20 text-[#0284c7]">
                                    <span x-text="useAi ? 'Mode AI Aktif' : 'Tanpa AI (Rule Engine + DB BTKI)'"></span>
                                </label>
                            </div>
                            <div class="text-[11px] text-black/50 dark:text-white/50">
                                PDF, Excel, CSV &amp; ZIP diekstrak deterministik 100% tanpa AI; aktifkan AI hanya jika dokumen berupa foto/hasil scan gambar.
                            </div>
                        </div>
                    </div>
                    <button type="button"
                            @click="scanFiles()"
                            :disabled="scanning"
                            class="btn-apple-filled !h-9 text-[12px] disabled:opacity-50 shrink-0 cursor-pointer">
                        <i data-lucide="scan-line" class="w-3.5 h-3.5" x-show="!scanning"></i>
                        <span x-show="scanning" class="animate-spin">⏳</span>
                        <span x-text="scanning ? 'Memindai Bundle...' : 'Pindai & Auto-Detect'"></span>
                    </button>
                </div>

                <!-- CARD HASIL AUTO-DETEKSI DOKUMEN -->
                <div x-show="scanned" class="p-4 rounded-[14px] bg-[#34C759]/10 border border-[#34C759]/20 text-[12px] space-y-3">
                    <div class="flex items-center justify-between border-b border-[#34C759]/20 pb-2">
                        <div class="flex items-center space-x-2">
                            <span class="w-5 h-5 rounded-full bg-[#34C759] text-white flex items-center justify-center font-bold text-xs">✓</span>
                            <div>
                                <span class="font-bold text-[#248A3D] dark:text-[#30D158] text-[13px]">Bundle Dokumen Terdeteksi &amp; Siap Masuk Form CEISA</span>
                                <span class="text-[11px] text-[#248A3D]/80 dark:text-[#30D158]/80 block" x-text="'Engine: ' + (detected.extraction_mode === 'AI_HYBRID' ? 'AI Hybrid + Rule Engine' : 'Rule Engine Offline (100% Tanpa AI)')"></span>
                            </div>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-[#34C759]/20 text-[#248A3D] dark:text-[#30D158]"
                              x-text="'Skor Kesiapan ' + (crossValidation?.score || 95) + '%'"></span>
                    </div>

                    <!-- Cross Validation Checks -->
                    <div x-show="crossValidation && crossValidation.checks" class="grid grid-cols-1 sm:grid-cols-3 gap-1.5 text-[10px]">
                        <template x-for="(chk, cIdx) in (crossValidation?.checks || [])" :key="cIdx">
                            <div class="px-2.5 py-1.5 rounded-[8px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 flex items-start gap-1.5">
                                <span :class="chk.status === 'ok' ? 'text-[#34C759]' : 'text-amber-500'" class="font-bold mt-0.2" x-text="chk.status === 'ok' ? '✓' : '!'"></span>
                                <div class="min-w-0">
                                    <div class="font-semibold text-black dark:text-white truncate" x-text="chk.label"></div>
                                    <div class="text-black/55 dark:text-white/55 truncate" x-text="chk.detail"></div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Ringkasan Profil Klien & Pemasok -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px]">
                        <div class="bg-white dark:bg-[#1C1C1E] p-2.5 rounded-[10px] border border-black/5 dark:border-white/5 shadow-2xs">
                            <span class="text-[10px] text-black/45 dark:text-white/45 font-bold block uppercase tracking-wider">Klien Pemilik Barang:</span>
                            <strong class="text-black dark:text-white block font-semibold text-[12px] mt-0.5" x-text="detected.client_name || '-'"></strong>
                            <span class="text-[10px] font-mono text-black/50 dark:text-white/50 block">NPWP: <span x-text="detected.client_npwp || '-'"></span></span>
                        </div>
                        <div class="bg-white dark:bg-[#1C1C1E] p-2.5 rounded-[10px] border border-black/5 dark:border-white/5 shadow-2xs">
                            <span class="text-[10px] text-black/45 dark:text-white/45 font-bold block uppercase tracking-wider">Mitra Luar Negeri (Pemasok/Pembeli):</span>
                            <strong class="text-black dark:text-white block font-semibold text-[12px] mt-0.5" x-text="detected.exporter_name || '-'"></strong>
                            <span class="text-[10px] text-black/50 dark:text-white/50 block truncate" x-text="detected.exporter_address || '-'"></span>
                        </div>
                    </div>

                    <!-- 4 Kartu Parameter Utama Logistik & Kepabeanan -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-[11px]">
                        <div class="bg-white dark:bg-[#1C1C1E] p-2.5 rounded-[10px] border border-black/5 dark:border-white/5 shadow-2xs">
                            <span class="text-[10px] text-black/45 dark:text-white/45 block font-bold">Nomor B/L & Kontainer:</span>
                            <strong class="font-mono text-black dark:text-white block text-[12px] mt-0.5" x-text="detected.bl_number || '-'"></strong>
                            <span class="text-[10px] font-mono text-black/50 dark:text-white/50 truncate block" x-text="detected.nomor_kontainer || '-'"></span>
                        </div>
                        <div class="bg-white dark:bg-[#1C1C1E] p-2.5 rounded-[10px] border border-black/5 dark:border-white/5 shadow-2xs">
                            <span class="text-[10px] text-black/45 dark:text-white/45 block font-bold">Pelayaran & Kapal:</span>
                            <strong class="text-black dark:text-white truncate block text-[12px] mt-0.5" x-text="detected.shipping_vendor_name || '-'"></strong>
                            <span class="text-[10px] text-black/50 dark:text-white/50 font-mono block" x-text="(detected.vessel_name || '-') + ' / ' + (detected.voyage_number || '-')"></span>
                        </div>
                        <div class="bg-white dark:bg-[#1C1C1E] p-2.5 rounded-[10px] border border-black/5 dark:border-white/5 shadow-2xs">
                            <span class="text-[10px] text-black/45 dark:text-white/45 block font-bold">Nilai Komersial (CIF/FOB):</span>
                            <strong class="text-[#248A3D] dark:text-[#30D158] font-mono block text-[12px] mt-0.5 tabular-nums" x-text="(detected.currency || 'USD') + ' ' + Number(detected.total_cif || detected.total_fob || 0).toLocaleString()"></strong>
                            <span class="text-[10px] text-black/50 dark:text-white/50 block">Kurs: <span class="font-mono tabular-nums" x-text="Number(detected.ndbm_kurs || 15850).toLocaleString()"></span></span>
                        </div>
                        <div class="bg-white dark:bg-[#1C1C1E] p-2.5 rounded-[10px] border border-black/5 dark:border-white/5 shadow-2xs">
                            <span class="text-[10px] text-black/45 dark:text-white/45 block font-bold">Kemasan & Bobot:</span>
                            <strong class="text-black dark:text-white block text-[12px] mt-0.5" x-text="(detected.total_kemasan || 0) + ' ' + (detected.jenis_kemasan || 'PKGS')"></strong>
                            <span class="text-[10px] text-black/50 dark:text-white/50 font-mono block" x-text="'Bruto: ' + Number(detected.total_bruto || 0).toLocaleString() + ' Kg'"></span>
                        </div>
                    </div>

                    <!-- Status Pos Tarif BTKI & Tombol Toggle Rincian -->
                    <div class="bg-white dark:bg-[#1C1C1E] p-2.5 rounded-[10px] border border-black/5 dark:border-white/5 flex items-center justify-between shadow-2xs">
                        <div class="flex items-center space-x-2">
                            <i data-lucide="list" class="w-4 h-4 text-[#5856D6]"></i>
                            <span class="font-medium text-black dark:text-white">
                                Pos Tarif BTKI: <strong class="text-[#5856D6] dark:text-[#5E5CE6]" x-text="(detected.items?.length || 0) + ' Item Kargo Terklasifikasi Otomatis'"></strong>
                            </span>
                        </div>
                        <button type="button" @click="showDetails = !showDetails" class="btn-apple-tinted !h-7 !px-2.5 !text-[10px] cursor-pointer">
                            <span x-text="showDetails ? 'Tutup Rincian' : 'Lihat / Edit Rincian (' + (detected.items?.length || 0) + ')'"></span>
                        </button>
                    </div>
                </div>

                <!-- Hidden inputs agar data terdeteksi terkirim ke server -->
                <input type="hidden" name="bl_number" :value="detected.bl_number" :disabled="tab === 'ai' && !scanned">
                <input type="hidden" name="shipping_vendor_id" :value="detected.shipping_vendor_id" :disabled="tab === 'ai' && !scanned">
                <input type="hidden" name="vessel_name" :value="detected.vessel_name" :disabled="tab === 'ai' && !scanned">
                <input type="hidden" name="voyage_number" :value="detected.voyage_number" :disabled="tab === 'ai' && !scanned">
                <input type="hidden" name="port_of_loading" :value="detected.port_of_loading" :disabled="tab === 'ai' && !scanned">
                <input type="hidden" name="port_of_discharge" :value="detected.port_of_discharge" :disabled="tab === 'ai' && !scanned">
                <input type="hidden" name="eta" :value="detected.eta" :disabled="tab === 'ai' && !scanned">
                <input type="hidden" name="total_kontainer" :value="detected.total_kontainer" :disabled="tab === 'ai' && !scanned">
                <input type="hidden" name="nomor_kontainer" :value="detected.nomor_kontainer" :disabled="tab === 'ai' && !scanned">
                <input type="hidden" name="total_kemasan" :value="detected.total_kemasan" :disabled="tab === 'ai' && !scanned">
                <input type="hidden" name="jenis_kemasan" :value="detected.jenis_kemasan" :disabled="tab === 'ai' && !scanned">
                <input type="hidden" name="total_bruto" :value="detected.total_bruto" :disabled="tab === 'ai' && !scanned">
                <input type="hidden" name="total_netto" :value="detected.total_netto" :disabled="tab === 'ai' && !scanned">
                <input type="hidden" name="total_cif" :value="detected.total_cif" :disabled="tab === 'ai' && !scanned">
                <input type="hidden" name="currency" :value="detected.currency" :disabled="tab === 'ai' && !scanned">
                <input type="hidden" name="ndbm_kurs" :value="detected.ndbm_kurs" :disabled="tab === 'ai' && !scanned">
                <input type="hidden" name="etd_date" :value="detected.etd_date" :disabled="tab === 'ai' && !scanned">
                <input type="hidden" name="commodity_summary" :value="detected.commodity_summary" :disabled="tab === 'ai' && !scanned">
                <input type="hidden" name="use_ai" :value="useAi ? '1' : '0'">
                <input type="hidden" name="after_action" :value="afterAction">

                <!-- COLLAPSIBLE FORM REVIEW / EDIT RINCIAN & TABEL ITEM -->
                <div x-show="showDetails" class="p-4 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-3">
                    <div class="font-bold text-black dark:text-white flex items-center justify-between pb-1 border-b border-black/5 dark:border-white/5">
                        <span>Penyesuaian Manual & Daftar Lengkap Item Terdeteksi</span>
                        <span class="text-[10px] text-black/45 dark:text-white/45">Data ini dapat disesuaikan sebelum pembukaan proyek</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        <div>
                            <label class="block font-semibold text-black dark:text-white mb-0.5 text-[11px]">Nomor B/L</label>
                            <input type="text" x-model="detected.bl_number" class="w-full px-2.5 py-1.5 rounded-[8px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[12px]">
                        </div>
                        <div>
                            <label class="block font-semibold text-black dark:text-white mb-0.5 text-[11px]">Nama Kapal (Vessel)</label>
                            <input type="text" x-model="detected.vessel_name" class="w-full px-2.5 py-1.5 rounded-[8px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[12px]">
                        </div>
                        <div>
                            <label class="block font-semibold text-black dark:text-white mb-0.5 text-[11px]">Voyage No.</label>
                            <input type="text" x-model="detected.voyage_number" class="w-full px-2.5 py-1.5 rounded-[8px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[12px]">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-2.5">
                        <div>
                            <label class="block font-semibold text-black dark:text-white mb-0.5 text-[11px]">Kontainer</label>
                            <input type="text" x-model="detected.nomor_kontainer" class="w-full px-2.5 py-1.5 rounded-[8px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[12px]">
                        </div>
                        <div>
                            <label class="block font-semibold text-black dark:text-white mb-0.5 text-[11px]">Kemasan</label>
                            <input type="number" min="0" x-model="detected.total_kemasan" class="w-full px-2.5 py-1.5 rounded-[8px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[12px]">
                        </div>
                        <div>
                            <label class="block font-semibold text-black dark:text-white mb-0.5 text-[11px]">Bruto (Kg)</label>
                            <input type="number" min="0" step="0.01" x-model="detected.total_bruto" class="w-full px-2.5 py-1.5 rounded-[8px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[12px]">
                        </div>
                        <div>
                            <label class="block font-semibold text-black dark:text-white mb-0.5 text-[11px]">Nilai Valuta (CIF)</label>
                            <input type="number" min="0" step="0.01" x-model="detected.total_cif" class="w-full px-2.5 py-1.5 rounded-[8px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[12px]">
                        </div>
                    </div>

                    <!-- Tabel Item Kargo Terdeteksi Lengkap -->
                    <div x-show="detected.items && detected.items.length > 0" class="mt-4 pt-3 border-t border-black/5 dark:border-white/5">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-bold text-black dark:text-white text-[11px]">Rincian Item Kargo & Klasifikasi Pos Tarif BTKI (<span x-text="detected.items.length"></span> Item)</span>
                            <span class="text-[10px] text-[#248A3D] dark:text-[#30D158] font-bold bg-[#34C759]/15 px-2 py-0.5 rounded-full">✓ Otomatis Diimpor ke Database</span>
                        </div>
                        <div class="max-h-64 overflow-y-auto rounded-[10px] border border-black/5 dark:border-white/5">
                            <table class="w-full text-left border-collapse text-[11px]">
                                <thead class="bg-black/[0.04] dark:bg-white/[0.06] sticky top-0 text-[10px] font-bold text-black/60 dark:text-white/60 uppercase border-b border-black/5 dark:border-white/5">
                                    <tr>
                                        <th class="p-2 w-10">No</th>
                                        <th class="p-2">Uraian Barang</th>
                                        <th class="p-2">Pos Tarif (HS)</th>
                                        <th class="p-2 text-right">Kemasan</th>
                                        <th class="p-2 text-right">Bruto (Kg)</th>
                                        <th class="p-2 text-right">FOB (<span x-text="detected.currency"></span>)</th>
                                        <th class="p-2 text-center">BM</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-black/5 dark:divide-white/5 bg-white dark:bg-[#1C1C1E]">
                                    <template x-for="item in detected.items" :key="item.nomor_urut">
                                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                                            <td class="p-2 font-mono text-black/40 dark:text-white/40" x-text="item.nomor_urut"></td>
                                            <td class="p-2 font-medium text-black dark:text-white" x-text="item.uraian_barang"></td>
                                            <td class="p-2 font-mono font-bold text-[#5856D6] dark:text-[#5E5CE6]" x-text="item.kode_hs"></td>
                                            <td class="p-2 text-right font-mono" x-text="item.jumlah_kemasan + ' ' + (item.jenis_kemasan || 'PCE')"></td>
                                            <td class="p-2 text-right font-mono tabular-nums" x-text="Number(item.berat_bruto).toLocaleString()"></td>
                                            <td class="p-2 text-right font-mono font-bold text-[#248A3D] dark:text-[#30D158] tabular-nums" x-text="Number(item.nilai_fob).toLocaleString()"></td>
                                            <td class="p-2 text-center">
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-mono bg-[#0284c7]/10 text-[#0284c7] font-bold" x-text="(item.tarif_bm_persen || 0) + '%'"></span>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ============================================== -->
            <!-- TAB 2: MANUAL FORM (TRADISIONAL / TANPA FILE)   -->
            <!-- ============================================== -->
            <div x-show="tab === 'manual'" class="space-y-3.5">
                <!-- Section 1: Klien & Jenis Layanan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-black/[0.02] dark:bg-white/[0.02] p-3 rounded-[14px] border border-black/5 dark:border-white/5">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block font-semibold text-black dark:text-white text-[12px]">Pilih Klien / Pemilik Barang</label>
                            <button type="button" @click="openQuickClient()" class="text-[11px] font-semibold text-[#0284c7] hover:underline flex items-center gap-0.5 cursor-pointer">
                                <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                                <span>+ Klien Baru</span>
                            </button>
                        </div>
                        <select name="client_id_manual" id="{{ $pfx }}manual-client-select" x-model="detected.client_id" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#0284c7]">
                            <option value="">-- Pilih Importir / Eksportir --</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} (NPWP: {{ $c->npwp ?? '-' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Tipe Kepabeanan <span class="text-rose-500">*</span></label>
                        @if($lockType && in_array($defaultType, ['IMPORT', 'EXPORT']))
                            <div class="flex items-center gap-2 px-3 py-2 rounded-[10px] border border-[#0284c7]/30 bg-white dark:bg-[#1C1C1E] text-[13px] font-bold text-slate-800 dark:text-white">
                                <span class="w-2 h-2 rounded-full {{ $defaultType === 'IMPORT' ? 'bg-[#0284c7]' : 'bg-indigo-600' }}"></span>
                                <span>{{ $defaultType === 'IMPORT' ? 'IMPOR (BC 2.0 / PIB)' : 'EKSPOR (BC 3.0 / PEB)' }}</span>
                                <span class="ml-auto text-[10px] font-mono px-2 py-0.5 rounded bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60">Terkunci</span>
                            </div>
                            <input type="hidden" name="type_manual" value="{{ $defaultType }}">
                        @else
                            <select name="type_manual" x-model="detected.type" :required="tab === 'manual'" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#0284c7]">
                                <option value="">-- Wajib Pilih: Impor atau Ekspor --</option>
                                <option value="IMPORT">IMPOR (BC 2.0 / PIB)</option>
                                <option value="EXPORT">EKSPOR (BC 3.0 / PEB)</option>
                            </select>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-2">
                        <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Vendor Pihak Pelayaran (Shipping Line)</label>
                        <select name="shipping_vendor_id_manual" x-model="detected.shipping_vendor_id" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#0284c7]">
                            <option value="">-- Pilih Pelayaran --</option>
                            @foreach($vendors as $v)
                                <option value="{{ $v->id }}">{{ $v->name }} (PIC: {{ $v->pic_name ?? 'Import Desk' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Nomor B/L atau AWB</label>
                        <input type="text" x-model="detected.bl_number" placeholder="Contoh: WHLU-9281928" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#0284c7]">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Nama Kapal (Vessel)</label>
                        <input type="text" x-model="detected.vessel_name" placeholder="MV WAN HAI 315" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#0284c7]">
                    </div>
                    <div>
                        <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Voyage Number</label>
                        <input type="text" x-model="detected.voyage_number" placeholder="V.042N" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#0284c7]">
                    </div>
                    <div>
                        <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Estimasi Tiba (ETA)</label>
                        <input type="date" x-model="detected.eta" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#0284c7]">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Pelabuhan Muat (POL)</label>
                        <input type="text" x-model="detected.port_of_loading" placeholder="CNSHA (Shanghai Port)" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#0284c7]">
                    </div>
                    <div>
                        <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Pelabuhan Bongkar (POD)</label>
                        <input type="text" x-model="detected.port_of_discharge" placeholder="IDTPP (Tanjung Priok, Jakarta)" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#0284c7]">
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div>
                        <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Jumlah Kontainer</label>
                        <input type="number" min="0" x-model="detected.total_kontainer" placeholder="1" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#0284c7]">
                    </div>
                    <div>
                        <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Nomor Kontainer</label>
                        <input type="text" x-model="detected.nomor_kontainer" placeholder="WHLU2918231 / 40HC" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#0284c7]">
                    </div>
                    <div>
                        <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Jumlah Kemasan</label>
                        <input type="number" min="0" x-model="detected.total_kemasan" placeholder="600" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#0284c7]">
                    </div>
                    <div>
                        <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Jenis Kemasan</label>
                        <input type="text" x-model="detected.jenis_kemasan" placeholder="CTN / PKGS / BAG" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#0284c7]">
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div>
                        <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Berat Bruto (Kg)</label>
                        <input type="number" min="0" step="0.01" x-model="detected.total_bruto" placeholder="18500" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#0284c7]">
                    </div>
                    <div>
                        <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Nilai CIF / Nilai Barang</label>
                        <input type="number" min="0" step="0.01" x-model="detected.total_cif" placeholder="45000" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#0284c7]">
                    </div>
                    <div>
                        <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Mata Uang (Valuta)</label>
                        <select x-model="detected.currency" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#0284c7]">
                            <option value="USD">USD - US Dollar</option>
                            <option value="CNY">CNY - Chinese Yuan</option>
                            <option value="EUR">EUR - Euro</option>
                            <option value="JPY">JPY - Japanese Yen</option>
                            <option value="SGD">SGD - Singapore Dollar</option>
                            <option value="IDR">IDR - Rupiah</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Kurs NDBM Pajak (Rp)</label>
                        <input type="number" min="0" step="0.01" x-model="detected.ndbm_kurs" placeholder="15850" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#0284c7]">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Ringkasan Komoditas / Uraian Barang</label>
                    <input type="text" x-model="detected.commodity_summary" placeholder="Contoh: SPARE PARTS MOTOR CYCLE DAN AKSESORIS KENDARAAN" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#0284c7]">
                </div>
            </div>

            <!-- Aksi Lanjutan Otomatis (Wajib Draft untuk Ditinjau) -->
            <div class="p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 space-y-2">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <span class="font-bold text-[12px] text-black dark:text-white">Aksi Otomatis Setelah Simpan</span>
                        <p class="text-[11px] text-black/50 dark:text-white/50">Seluruh unggahan dokumen massal disimpan sebagai <strong>DRAFT</strong> untuk ditinjau &amp; diverifikasi sebelum dikirim ke CEISA.</p>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-500/15 text-amber-700 dark:text-amber-300">
                        Mode Draft-First Aktif
                    </span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px]">
                    <label class="flex items-center gap-2 p-2.5 rounded-[10px] border cursor-pointer transition-colors"
                           :class="afterAction === 'project' ? 'border-[#0284c7] bg-[#0284c7]/10 text-[#0284c7] font-semibold' : 'border-black/10 dark:border-white/10 text-black/70 dark:text-white/70'">
                        <input type="radio" x-model="afterAction" value="project" class="text-[#0284c7]">
                        <span>Simpan sebagai Draft Proyek &amp; Tracking</span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 rounded-[10px] border cursor-pointer transition-colors"
                           :class="afterAction === 'fill_bc_form' ? 'border-[#5856D6] bg-[#5856D6]/10 text-[#5856D6] font-semibold' : 'border-black/10 dark:border-white/10 text-black/70 dark:text-white/70'">
                        <input type="radio" x-model="afterAction" value="fill_bc_form" class="text-[#5856D6]">
                        <span>Simpan Draft &amp; Auto-Fill Form {{ $defaultType === 'EXPORT' ? 'PEB (BC 3.0)' : ($defaultType === 'IMPORT' ? 'PIB (BC 2.0)' : 'PIB / PEB') }}</span>
                    </label>
                </div>
            </div>

            <!-- Footer Action Buttons -->
            <div class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-end space-x-2">
                <button type="button" onclick="document.getElementById('{{ $modalId }}').classList.add('hidden')" class="btn-apple-gray !h-9 text-[12px] cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="btn-apple-filled !h-9 text-[12px] cursor-pointer">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span x-text="afterAction === 'fill_bc_form' ? 'Simpan Draft &amp; Isi Form BC' : 'Simpan Draft Proyek'"></span>
                </button>
            </div>
        </form>

        <!-- SUB-MODAL: QUICK ADD CLIENT / PEMILIK BARANG -->
        <div x-show="showQuickClientModal" 
             class="fixed inset-0 z-[250] overflow-y-auto flex items-center justify-center p-4 bg-black/70 backdrop-blur-md"
             role="dialog"
             aria-modal="true"
             x-cloak>
            <div class="relative bg-white dark:bg-[#1C1C1E] rounded-2xl border border-black/10 dark:border-white/10 shadow-2xl max-w-md w-full p-5 text-xs animate-in fade-in zoom-in-95 duration-150">
                <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5 mb-3">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 rounded-lg bg-[#0284c7]/10 text-[#0284c7] flex items-center justify-center">
                            <i data-lucide="user-plus" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-black dark:text-white">Tambah Klien Baru Cepat</h4>
                            <p class="text-[11px] text-black/50 dark:text-white/50">Daftarkan pemilik barang baru ke database</p>
                        </div>
                    </div>
                    <button type="button" @click="showQuickClientModal = false" class="p-1 rounded text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <div x-show="quickClientError" class="mb-3 p-2.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 text-[11px] border border-rose-200 dark:border-rose-800" x-text="quickClientError"></div>

                <div class="space-y-3">
                    <div>
                        <label class="block font-semibold text-black dark:text-white mb-1 text-[11px]">Nama Perusahaan / Importir <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="quickClient.name" placeholder="PT. JINDI MATERIAL INDONESIA" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white focus:outline-none focus:border-[#0284c7]">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-semibold text-black dark:text-white mb-1 text-[11px]">NPWP (16 Digit)</label>
                            <input type="text" x-model="quickClient.npwp" placeholder="75.296.714.1-041.000" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white font-mono focus:outline-none focus:border-[#0284c7]">
                        </div>
                        <div>
                            <label class="block font-semibold text-black dark:text-white mb-1 text-[11px]">Nama PIC</label>
                            <input type="text" x-model="quickClient.pic_name" placeholder="Bpk. Hendra" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white focus:outline-none focus:border-[#0284c7]">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-black dark:text-white mb-1 text-[11px]">No. WhatsApp / HP</label>
                        <input type="text" x-model="quickClient.phone" placeholder="081290008888" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white focus:outline-none focus:border-[#0284c7]">
                    </div>

                    <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-end space-x-2">
                        <button type="button" @click="showQuickClientModal = false" class="btn-apple-gray !h-8 text-[11px] cursor-pointer">Batal</button>
                        <button type="button" @click="submitQuickClient()" :disabled="quickClientLoading" class="btn-apple-filled !h-8 text-[11px] flex items-center space-x-1 cursor-pointer">
                            <span x-show="quickClientLoading">Menyimpan...</span>
                            <span x-show="!quickClientLoading">Simpan & Pilih Klien</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
