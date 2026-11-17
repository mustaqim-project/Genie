<div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-5 border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-4">
    <!-- Toolbar Dokumen -->
    <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-black/[0.06] dark:border-white/[0.06]">
        <div class="flex items-center gap-2">
            <span class="px-2.5 py-1 rounded-lg bg-[#0284c7]/10 text-[#0284c7] border border-[#0284c7]/20 font-bold text-xs font-mono"
                  x-text="'TOTAL: ' + w.dokumen.length"></span>
            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Dokumen Pelengkap Pabean (PIB)</span>
        </div>

        <div class="flex flex-wrap items-center gap-2 text-xs">
            <!-- Unggah PDF / Bundle (Multi-File / ZIP) Cerdas -->
            <button type="button" @click="$refs.wizardBundleUploadInput.click()"
                    :disabled="bundleUploading"
                    class="px-3.5 py-1.5 rounded-xl border border-indigo-500/30 bg-indigo-50 hover:bg-indigo-100/80 dark:bg-indigo-950/40 dark:hover:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 font-semibold inline-flex items-center gap-1.5 cursor-pointer shadow-xs disabled:opacity-50 transition-all"
                    title="Unggah satu atau banyak file PDF sekaligus (Invoice, PL, BL, SKA) atau file ZIP. Sistem otomatis membedakan jenis dokumen & menautkan ke tabel.">
                <template x-if="bundleUploading">
                    <i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin"></i>
                </template>
                <template x-if="!bundleUploading">
                    <i data-lucide="layers" class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400"></i>
                </template>
                <span x-text="bundleUploading ? 'Memproses Bundle...' : 'Unggah PDF / Bundle (Multi-File & ZIP)'"></span>
            </button>
            <input type="file" x-ref="wizardBundleUploadInput" class="hidden" multiple accept=".pdf,.png,.jpg,.jpeg,.webp,.zip" @change="handleWizardBundleUpload($event)">

            <!-- Validasi & Sortir Otomatis -->
            <button type="button" @click="validateEcooDocs()"
                    class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] hover:bg-slate-50 dark:hover:bg-white/10 text-slate-700 dark:text-slate-200 font-semibold inline-flex items-center gap-1.5 cursor-pointer transition-colors"
                    title="Cek keaktifan dokumen e-COO (861)">
                <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-600"></i>
                <span>Validasi eCOO</span>
            </button>
            <button type="button" @click="sortDocumentsAuto()"
                    class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] hover:bg-slate-50 dark:hover:bg-white/10 text-slate-700 dark:text-slate-200 font-semibold inline-flex items-center gap-1.5 cursor-pointer transition-colors"
                    title="Urutkan dokumen otomatis sesuai regulasi Ditjen Bea Cukai (380 -> 705 -> 861 -> 217 -> 430)">
                <i data-lucide="arrow-up-down" class="w-3.5 h-3.5 text-[#0284c7]"></i>
                <span>Urutkan Otomatis</span>
            </button>

            <!-- Isi Manual -->
            <button type="button" @click="openNewDocModal()"
                    class="px-3.5 py-1.5 rounded-xl bg-[#0284c7] hover:bg-[#0066D6] text-white font-semibold inline-flex items-center gap-1.5 cursor-pointer shadow-xs transition-colors"
                    title="Tambah data dokumen pelengkap secara manual dengan fitur auto-fill & lampirkan berkas">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                <span>+ Isi manual</span>
            </button>
        </div>
    </div>

    <!-- Smart Bundle Info Banner -->
    <div class="p-3 rounded-xl bg-gradient-to-r from-blue-50/80 via-indigo-50/60 to-purple-50/60 dark:from-blue-950/20 dark:via-indigo-950/20 dark:to-purple-950/20 border border-blue-200/60 dark:border-blue-800/40 text-xs flex flex-wrap items-center justify-between gap-2">
        <div class="flex items-center gap-2 text-slate-700 dark:text-slate-300">
            <i data-lucide="sparkles" class="w-4 h-4 text-[#0284c7] shrink-0"></i>
            <span><strong>Deteksi Bundle Otomatis:</strong> Unggah beberapa file PDF (Inv, PL, B/L, e-COO, Polis) atau ZIP. Sistem otomatis mengklasifikasikan jenis dokumen & menautkan ke baris pabean. Jika ada revisi, Anda dapat <strong>upload ulang langsung per baris</strong>.</span>
        </div>
        <div class="flex items-center gap-2 text-[11px] font-medium text-slate-500 dark:text-slate-400">
            <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> PDF Siap Preview</span>
            <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-500"></span> Metadata Saja</span>
        </div>
    </div>

    <!-- Table Dokumen Pelengkap -->
    <div class="overflow-x-auto rounded-xl border border-black/[0.06] dark:border-white/[0.08]">
        <table class="w-full text-xs text-left">
            <thead class="bg-slate-50 dark:bg-white/[0.03] text-slate-500 dark:text-slate-400 font-semibold border-b border-black/[0.06] dark:border-white/[0.08]">
                <tr>
                    <th class="px-3 py-2.5 w-14 text-center">Seri</th>
                    <th class="px-3 py-2.5">Jenis Dokumen</th>
                    <th class="px-3 py-2.5 text-center">eCOO Status</th>
                    <th class="px-3 py-2.5">Nomor Dokumen</th>
                    <th class="px-3 py-2.5">Tanggal</th>
                    <th class="px-3 py-2.5 text-center">Status Berkas</th>
                    <th class="px-3 py-2.5 text-center sticky right-0 z-20 bg-slate-50 dark:bg-[#1C1C1E] border-l border-black/[0.06] dark:border-white/[0.08] shadow-[-6px_0_12px_rgba(0,0,0,0.06)]">Aksi & Re-Upload</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-black/[0.05] dark:divide-white/[0.06]">
                <template x-for="(doc, idx) in w.dokumen" :key="idx">
                    <tr @click="selectDocForPreview(doc)"
                        :class="isDocActiveInPreview(doc) ? 'bg-[#0284c7]/10 dark:bg-[#0284c7]/15' : 'hover:bg-slate-50/80 dark:hover:bg-white/[0.02]'"
                        class="transition-colors cursor-pointer">
                        <!-- Seri -->
                        <td class="px-3 py-3 text-center">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50 font-mono font-bold text-[11px]">
                                &check; <span x-text="doc.seriDokumen"></span>
                            </span>
                        </td>

                        <!-- Jenis Dokumen -->
                        <td class="px-3 py-3">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-slate-800 dark:text-white" x-text="getDocTypeLabel(doc.kodeDokumen)"></span>
                                <span class="px-1.5 py-0.2 rounded bg-black/5 dark:bg-white/10 font-mono text-[10px] text-slate-500" x-text="doc.kodeDokumen"></span>
                            </div>
                        </td>

                        <!-- eCOO Status -->
                        <td class="px-3 py-3 text-center text-slate-500 dark:text-slate-400">
                            <span x-text="doc.ecooStatus || '-'"
                                  :class="(doc.ecooStatus && doc.ecooStatus !== '-') ? 'px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-semibold text-[10px]' : ''"></span>
                        </td>

                        <!-- Nomor Dokumen -->
                        <td class="px-3 py-3">
                            <div class="space-y-0.5">
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 text-[9px] font-semibold">
                                    &check; Verified
                                </span>
                                <div class="font-mono font-semibold text-slate-800 dark:text-slate-200 text-xs" x-text="doc.nomorDokumen || '-'"></div>
                            </div>
                        </td>

                        <!-- Tanggal Dokumen -->
                        <td class="px-3 py-3">
                            <div class="space-y-0.5">
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 text-[9px] font-semibold">
                                    &check; Verified
                                </span>
                                <div class="font-mono text-slate-700 dark:text-slate-300 text-xs tabular-nums" x-text="doc.tanggalDokumen || '-'"></div>
                            </div>
                        </td>

                        <!-- Status Berkas Fisik -->
                        <td class="px-3 py-3 text-center">
                            <template x-if="doc.shipmentDocumentId">
                                <button type="button" @click.stop="selectDocForPreview(doc)"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/50 hover:bg-emerald-100 transition-colors cursor-pointer"
                                        title="Klik untuk membuka preview di panel kiri">
                                    <i data-lucide="file-check" class="w-3 h-3 text-emerald-600"></i>
                                    <span>PDF Tersedia</span>
                                </button>
                            </template>
                            <template x-if="!doc.shipmentDocumentId">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-medium bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 border border-amber-200/80"
                                      title="Belum ada berkas PDF terhubung. Klik tombol upload di samping untuk melampirkan.">
                                    <i data-lucide="file-warning" class="w-3 h-3 text-amber-600"></i>
                                    <span>Metadata Saja</span>
                                </span>
                            </template>
                        </td>

                        <!-- Aksi & Re-Upload Per Baris -->
                        <td class="px-3 py-3 whitespace-nowrap sticky right-0 z-10 bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xs border-l border-black/[0.06] dark:border-white/[0.08] shadow-[-6px_0_12px_rgba(0,0,0,0.06)]" @click.stop>
                            <div class="flex items-center justify-center gap-1">
                                <!-- Re-upload / Unggah File Khusus Baris Ini -->
                                <button type="button"
                                        @click="$refs['reuploadInput_' + idx].click()"
                                        :disabled="singleDocUploadingIndex === idx"
                                        :title="doc.shipmentDocumentId ? 'Ganti / upload ulang file PDF untuk seri #' + doc.seriDokumen : 'Unggah file PDF fisik untuk seri #' + doc.seriDokumen"
                                        :class="doc.shipmentDocumentId ? 'text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/30' : 'text-[#0284c7] hover:bg-[#0284c7]/10'"
                                        class="p-1.5 rounded-lg transition-colors cursor-pointer inline-flex items-center gap-1">
                                    <template x-if="singleDocUploadingIndex === idx">
                                        <i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin text-[#0284c7]"></i>
                                    </template>
                                    <template x-if="singleDocUploadingIndex !== idx">
                                        <i :data-lucide="doc.shipmentDocumentId ? 'refresh-cw' : 'upload-cloud'" class="w-3.5 h-3.5"></i>
                                    </template>
                                </button>
                                <input type="file" :x-ref="'reuploadInput_' + idx" class="hidden" accept=".pdf,.png,.jpg,.jpeg,.webp" @change="handleSingleDocReupload(idx, $event)">

                                <!-- Preview PDF -->
                                <button type="button"
                                        @click="previewDocumentRow(doc)"
                                        :title="doc.shipmentDocumentId ? 'Preview langsung berkas di panel kiri' : 'Belum ada berkas fisik terlampir'"
                                        :class="doc.shipmentDocumentId ? 'text-[#0284c7] hover:bg-[#0284c7]/10' : 'text-slate-300 dark:text-slate-600 cursor-not-allowed'"
                                        class="p-1.5 rounded-lg transition-colors cursor-pointer">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                </button>

                                <!-- Edit Dokumen -->
                                <button type="button" @click="openEditDocModal(idx)" title="Edit dokumen"
                                        class="p-1.5 rounded-lg text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/10 transition-colors cursor-pointer">
                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                </button>

                                <!-- Duplikat Baris -->
                                <button type="button" @click="duplicateDocRow(idx)" title="Duplikat baris"
                                        class="p-1.5 rounded-lg text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/10 transition-colors cursor-pointer">
                                    <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                </button>

                                <!-- Hapus Dokumen -->
                                <button type="button" @click="removeDocRow(idx)" title="Hapus dokumen"
                                        class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors cursor-pointer">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- Modal Form Cerdas: Tambah / Edit Dokumen Manual & Lampiran Berkas -->
    <div x-show="docModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
        <div class="bg-white dark:bg-[#1C1C1E] rounded-3xl max-w-xl w-full p-6 border border-black/10 dark:border-white/10 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.08]">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-xl bg-[#0284c7]/10 flex items-center justify-center text-[#0284c7]">
                        <i data-lucide="file-plus" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm text-slate-900 dark:text-white" x-text="editingDocIndex === null ? 'Tambah Dokumen Pelengkap' : 'Edit Dokumen Seri #' + (editingDocIndex + 1)"></h3>
                        <p class="text-[11px] text-slate-500">Standar Pengisian Dokumen Pabean CEISA 4.0</p>
                    </div>
                </div>
                <button type="button" @click="docModalOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="space-y-3.5 text-xs">
                <!-- Jenis Dokumen (Pengelompokan Optgroup Standar CEISA) -->
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Jenis Dokumen Pabean <span class="text-rose-500">*</span></label>
                    <select x-model="docForm.kodeDokumen" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-medium">
                        <optgroup label="Dokumen Wajib Impor / Pengangkutan">
                            <option value="380">380 - Commercial Invoice</option>
                            <option value="217">217 - Packing List</option>
                            <option value="705">705 - Bill of Lading (B/L) / AWB</option>
                            <option value="740">740 - Delivery Order (DO) / Booking</option>
                        </optgroup>
                        <optgroup label="Fasilitas Tarif & Asuransi">
                            <option value="861">861 - Certificate of Origin (e-COO / Form SKA)</option>
                            <option value="430">430 - Polis Asuransi Kargo (Insurance)</option>
                        </optgroup>
                        <optgroup label="Perizinan Lartas & Instansi Teknis">
                            <option value="860">860 - Persetujuan Impor (PI Kemendag)</option>
                            <option value="853">853 - Sertifikat Standar / SDPPI / Postel</option>
                            <option value="814">814 - Sertifikat Karantina (KT-2 / KT-9 / HC)</option>
                            <option value="854">854 - Laporan Surveyor (LS)</option>
                            <option value="862">862 - Surat Keterangan / Izin Kementerian Teknis</option>
                        </optgroup>
                        <optgroup label="Lainnya">
                            <option value="999">999 - Dokumen Pelengkap Pabean Lainnya</option>
                        </optgroup>
                    </select>
                </div>

                <!-- Nomor Dokumen (Auto-Uppercase & Quick Fill) -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="font-semibold text-slate-700 dark:text-slate-300">Nomor Dokumen <span class="text-rose-500">*</span></label>
                        <div class="flex items-center gap-1.5 text-[10px]">
                            @if(isset($shipment) && $shipment->nomor_bl_awb)
                            <button type="button" @click="docForm.nomorDokumen = '{{ addslashes($shipment->nomor_bl_awb) }}'" class="text-[#0284c7] font-semibold hover:underline cursor-pointer" title="Gunakan nomor B/L pengapalan">
                                + BL: {{ $shipment->nomor_bl_awb }}
                            </button>
                            @endif
                            @if(isset($shipment) && $shipment->invoice_number)
                            <button type="button" @click="docForm.nomorDokumen = '{{ addslashes($shipment->invoice_number) }}'" class="text-emerald-600 font-semibold hover:underline cursor-pointer" title="Gunakan nomor Invoice pengapalan">
                                + INV: {{ $shipment->invoice_number }}
                            </button>
                            @endif
                        </div>
                    </div>
                    <input type="text"
                           x-model="docForm.nomorDokumen"
                           @input="docForm.nomorDokumen = $event.target.value.toUpperCase()"
                           placeholder="Masukkan nomor dokumen..."
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono font-bold uppercase tracking-wider text-xs">
                </div>

                <!-- Tanggal Dokumen dengan Quick Buttons -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="font-semibold text-slate-700 dark:text-slate-300">Tanggal Dokumen <span class="text-rose-500">*</span></label>
                        <div class="flex gap-1.5 text-[10px]">
                            <button type="button" @click="setDocDateToday()" class="text-[#0284c7] hover:underline font-medium cursor-pointer">Hari Ini</button>
                            <span class="text-slate-300">|</span>
                            <button type="button" @click="setDocDateYesterday()" class="text-[#0284c7] hover:underline font-medium cursor-pointer">Kemarin</button>
                            <span class="text-slate-300">|</span>
                            <button type="button" @click="setDocDateSevenDaysAgo()" class="text-[#0284c7] hover:underline font-medium cursor-pointer">7 Hari Lalu</button>
                        </div>
                    </div>
                    <input type="date" x-model="docForm.tanggalDokumen" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white text-xs">
                </div>

                <!-- Lampirkan Berkas PDF/Gambar Langsung (Optimasi Baru!) -->
                <div class="p-3 rounded-2xl bg-slate-50 dark:bg-white/[0.03] border border-dashed border-slate-200 dark:border-white/15 space-y-2">
                    <label class="block font-semibold text-slate-700 dark:text-slate-300">Lampirkan Berkas Fisik PDF (Opsional)</label>
                    <div class="flex items-center gap-2">
                        <input type="file" x-ref="docModalFileInput" accept=".pdf,.png,.jpg,.jpeg,.webp" class="text-xs file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#0284c7]/10 file:text-[#0284c7] hover:file:bg-[#0284c7]/20 cursor-pointer">
                    </div>
                    <div class="flex items-center justify-between pt-1 text-[11px] text-slate-500">
                        <span>Atau pilih dari berkas tersimpan:</span>
                        <select x-model="docForm.shipmentDocumentId" class="px-2 py-1 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#1C1C1E] dark:text-white text-[11px] max-w-[200px]">
                            <option value="">-- Tanpa Berkas Fisik --</option>
                            <template x-for="pf in previewFiles" :key="pf.id">
                                <option :value="pf.id" x-text="pf.nama_file + ' (' + pf.jenis_dokumen + ')'"></option>
                            </template>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between gap-2 pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                <button type="button" @click="docModalOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-slate-100 hover:bg-slate-200 dark:bg-white/10 dark:hover:bg-white/15 text-slate-700 dark:text-slate-200 font-semibold text-xs transition-colors">
                    Batal
                </button>
                <div class="flex items-center gap-2">
                    <button type="button" @click="saveDocModal(true)" :disabled="modalSaving"
                            x-show="editingDocIndex === null"
                            class="px-3.5 py-2 rounded-xl border border-[#0284c7]/30 bg-[#0284c7]/10 hover:bg-[#0284c7]/20 text-[#0284c7] font-semibold text-xs inline-flex items-center gap-1.5 cursor-pointer shadow-xs disabled:opacity-50 transition-colors"
                            title="Simpan dokumen ini dan tetap buka modal untuk langsung mengisi dokumen berikutnya">
                        <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                        <span>Simpan &amp; Tambah Lagi</span>
                    </button>
                    <button type="button" @click="saveDocModal(false)" :disabled="modalSaving" class="px-4 py-2 rounded-xl bg-[#0284c7] hover:bg-[#0066D6] text-white font-semibold text-xs inline-flex items-center gap-1.5 cursor-pointer shadow-xs disabled:opacity-50 transition-colors">
                        <template x-if="modalSaving">
                            <i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin"></i>
                        </template>
                        <span x-text="modalSaving ? 'Menyimpan...' : 'Simpan Baris Dokumen'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
