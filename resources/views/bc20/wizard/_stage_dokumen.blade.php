<div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-5 border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-4">
    <!-- Toolbar -->
    <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-black/[0.06] dark:border-white/[0.06]">
        <div class="flex items-center gap-2">
            <span class="px-2.5 py-1 rounded-lg bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20 font-bold text-xs font-mono"
                  x-text="'TOTAL: ' + w.dokumen.length"></span>
            <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">Dokumen Pelengkap Pabean</span>
        </div>

        <div class="flex flex-wrap items-center gap-1.5 text-xs">
            <button type="button" @click="validateEcooDocs()"
                    class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] hover:bg-slate-50 dark:hover:bg-white/10 text-slate-700 dark:text-slate-200 font-semibold inline-flex items-center gap-1.5 cursor-pointer">
                <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-600"></i>
                <span>Validasi eCOO</span>
            </button>
            <button type="button" @click="sortDocumentsAuto()"
                    class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] hover:bg-slate-50 dark:hover:bg-white/10 text-slate-700 dark:text-slate-200 font-semibold inline-flex items-center gap-1.5 cursor-pointer">
                <i data-lucide="arrow-up-down" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                <span>Urutkan Otomatis</span>
            </button>
            <button type="button" @click="$refs.wizardDocUploadInput.click()"
                    class="px-3 py-1.5 rounded-xl border border-[#007AFF]/30 bg-[#007AFF]/10 hover:bg-[#007AFF]/20 text-[#007AFF] font-semibold inline-flex items-center gap-1.5 cursor-pointer">
                <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                <span>Unggah PDF</span>
            </button>
            <input type="file" x-ref="wizardDocUploadInput" class="hidden" accept=".pdf,.png,.jpg,.jpeg,.webp" @change="handleWizardDocUpload($event, 'dokumen')">
            <button type="button" @click="openNewDocModal()"
                    class="px-3 py-1.5 rounded-xl bg-[#007AFF] hover:bg-[#0066D6] text-white font-semibold inline-flex items-center gap-1.5 cursor-pointer">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                <span>Isi manual</span>
            </button>
        </div>
    </div>

    <!-- Table Dokumen -->
    <div class="overflow-x-auto rounded-xl border border-black/[0.06] dark:border-white/[0.08]">
        <table class="w-full text-xs text-left">
            <thead class="bg-slate-50 dark:bg-white/[0.03] text-slate-500 dark:text-slate-400 font-semibold border-b border-black/[0.06] dark:border-white/[0.08]">
                <tr>
                    <th class="px-3 py-2.5 w-14 text-center">Seri</th>
                    <th class="px-3 py-2.5">Jenis</th>
                    <th class="px-3 py-2.5 text-center">eCOO Status</th>
                    <th class="px-3 py-2.5">Nomor</th>
                    <th class="px-3 py-2.5">Tanggal</th>
                    <th class="px-3 py-2.5 text-center sticky right-0 z-20 bg-slate-50 dark:bg-[#1C1C1E] border-l border-black/[0.06] dark:border-white/[0.08] shadow-[-6px_0_12px_rgba(0,0,0,0.06)]">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-black/[0.05] dark:divide-white/[0.06]">
                <template x-for="(doc, idx) in w.dokumen" :key="idx">
                    <tr @click="selectDocForPreview(doc)"
                        :class="isDocActiveInPreview(doc) ? 'bg-[#007AFF]/10 dark:bg-[#007AFF]/15' : 'hover:bg-slate-50/80 dark:hover:bg-white/[0.02]'"
                        class="transition-colors cursor-pointer">
                        <td class="px-3 py-3 text-center">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50 font-mono font-bold text-[11px]">
                                &check; <span x-text="doc.seriDokumen"></span>
                            </span>
                        </td>
                        <td class="px-3 py-3">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-slate-800 dark:text-white" x-text="getDocTypeLabel(doc.kodeDokumen)"></span>
                                <template x-if="doc.shipmentDocumentId">
                                    <span class="px-1.5 py-0.5 rounded bg-rose-600 text-white text-[9px] font-bold tracking-wider uppercase"
                                          x-text="getPreviewFileExt(doc.shipmentDocumentId)">PDF</span>
                                </template>
                            </div>
                        </td>
                        <td class="px-3 py-3 text-center text-slate-500 dark:text-slate-400">
                            <span x-text="doc.ecooStatus || '-'"
                                  :class="(doc.ecooStatus && doc.ecooStatus !== '-') ? 'px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-semibold text-[10px]' : ''"></span>
                        </td>
                        <td class="px-3 py-3">
                            <div class="space-y-1">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 text-[10px] font-semibold">
                                    &check; Verified
                                </span>
                                <div class="font-mono font-semibold text-slate-800 dark:text-slate-200" x-text="doc.nomorDokumen || '-'"></div>
                            </div>
                        </td>
                        <td class="px-3 py-3">
                            <div class="space-y-1">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 text-[10px] font-semibold">
                                    &check; Verified
                                </span>
                                <div class="font-mono text-slate-700 dark:text-slate-300 tabular-nums" x-text="doc.tanggalDokumen || '-'"></div>
                            </div>
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap sticky right-0 z-10 bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xs border-l border-black/[0.06] dark:border-white/[0.08] shadow-[-6px_0_12px_rgba(0,0,0,0.06)]" @click.stop>
                            <div class="flex items-center justify-center gap-1">
                                <button type="button"
                                        @click="previewDocumentRow(doc)"
                                        :title="doc.shipmentDocumentId ? 'Preview langsung berkas di panel kiri' : 'Unggah / pilih berkas untuk preview'"
                                        :class="doc.shipmentDocumentId ? 'text-[#007AFF] hover:bg-[#007AFF]/10' : 'text-slate-300 dark:text-slate-600 cursor-not-allowed'"
                                        class="p-1.5 rounded-lg transition-colors cursor-pointer">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </button>
                                <button type="button" @click="openEditDocModal(idx)" title="Edit dokumen"
                                        class="p-1.5 rounded-lg text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/10 transition-colors cursor-pointer">
                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                </button>
                                <button type="button" @click="duplicateDocRow(idx)" title="Duplikat baris"
                                        class="p-1.5 rounded-lg text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/10 transition-colors cursor-pointer">
                                    <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                </button>
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

    <!-- Modal / Inline Drawer Edit / Tambah Dokumen -->
    <div x-show="docModalOpen" x-cloak class="p-4 rounded-2xl bg-slate-50 dark:bg-[#2C2C2E] border border-[#007AFF]/30 space-y-3">
        <div class="flex items-center justify-between">
            <h4 class="font-bold text-xs text-slate-900 dark:text-white" x-text="editingDocIndex === null ? 'Tambah Dokumen Pelengkap' : 'Edit Dokumen Seri #' + (editingDocIndex + 1)"></h4>
            <button type="button" @click="docModalOpen = false" class="text-xs text-slate-500 hover:text-slate-900 dark:hover:text-white cursor-pointer">&times; Tutup</button>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Jenis Dokumen</label>
                <select x-model="docForm.kodeDokumen" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#1C1C1E] dark:text-white">
                    @foreach($refOptions['kode_dokumen'] as $code => $label)
                        <option value="{{ $code }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Nomor Dokumen *</label>
                <input type="text" x-model="docForm.nomorDokumen" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#1C1C1E] dark:text-white font-mono">
            </div>
            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Tanggal *</label>
                <input type="date" x-model="docForm.tanggalDokumen" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#1C1C1E] dark:text-white">
            </div>
            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Tautkan Berkas Preview</label>
                <select x-model="docForm.shipmentDocumentId" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#1C1C1E] dark:text-white">
                    <option value="">-- Tidak ada file --</option>
                    <template x-for="pf in previewFiles" :key="pf.id">
                        <option :value="pf.id" x-text="pf.nama_file + ' (' + pf.jenis_dokumen + ')'"></option>
                    </template>
                </select>
            </div>
        </div>
        <div class="flex justify-end gap-2">
            <button type="button" @click="docModalOpen = false" class="btn-apple-gray text-xs px-3 py-1.5">Batal</button>
            <button type="button" @click="saveDocModal()" class="btn-apple-filled text-xs px-4 py-1.5 cursor-pointer">Simpan Baris Dokumen</button>
        </div>
    </div>
</div>

