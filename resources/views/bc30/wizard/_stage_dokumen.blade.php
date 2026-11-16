<div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-5 border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-4">
    <!-- Toolbar -->
    <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-black/[0.06] dark:border-white/[0.06]">
        <div class="flex items-center gap-2">
            <span class="px-2.5 py-1 rounded-lg bg-[#0284c7]/10 text-[#0284c7] border border-[#0284c7]/20 font-bold text-xs font-mono"
                  x-text="'TOTAL: ' + w.dokumen.length"></span>
            <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">Dokumen Pelengkap Pabean Ekspor</span>
        </div>

        <div class="flex flex-wrap items-center gap-1.5 text-xs">
            <button type="button" @click="sortDocumentsAuto()"
                    class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] hover:bg-slate-50 dark:hover:bg-white/10 text-slate-700 dark:text-slate-200 font-semibold inline-flex items-center gap-1.5 cursor-pointer">
                <i data-lucide="arrow-up-down" class="w-3.5 h-3.5 text-[#0284c7]"></i>
                <span>Urutkan Otomatis</span>
            </button>
            <button type="button" @click="$refs.wizardDocUploadInput.click()"
                    class="px-3 py-1.5 rounded-xl border border-[#0284c7]/30 bg-[#0284c7]/10 hover:bg-[#0284c7]/20 text-[#0284c7] font-semibold inline-flex items-center gap-1.5 cursor-pointer">
                <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                <span>Unggah PDF</span>
            </button>
            <input type="file" x-ref="wizardDocUploadInput" class="hidden" accept=".pdf,.png,.jpg,.jpeg,.webp" @change="handleWizardDocUpload($event, 'dokumen')">
            <button type="button" @click="openNewDocModal()"
                    :disabled="isReadOnly"
                    class="px-3 py-1.5 rounded-xl bg-[#0284c7] hover:bg-[#0066D6] text-white font-semibold inline-flex items-center gap-1.5 cursor-pointer disabled:opacity-50">
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
                    <th class="px-3 py-2.5">Jenis Dokumen</th>
                    <th class="px-3 py-2.5">Nomor Dokumen</th>
                    <th class="px-3 py-2.5">Tanggal</th>
                    <th class="px-3 py-2.5 text-center">Status Berkas</th>
                    <th class="px-3 py-2.5 text-center sticky right-0 z-20 bg-slate-50 dark:bg-[#1C1C1E] border-l border-black/[0.06] dark:border-white/[0.08] shadow-[-6px_0_12px_rgba(0,0,0,0.06)]">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-black/[0.05] dark:divide-white/[0.06]">
                <template x-for="(doc, idx) in w.dokumen" :key="idx">
                    <tr @click="selectDocForPreview(doc)"
                        :class="isDocActiveInPreview(doc) ? 'bg-[#0284c7]/10 dark:bg-[#0284c7]/15' : 'hover:bg-slate-50/80 dark:hover:bg-white/[0.02]'"
                        class="transition-colors cursor-pointer">
                        <td class="px-3 py-3 text-center">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50 font-mono font-bold text-[11px]">
                                &check; <span x-text="doc.seriDokumen"></span>
                            </span>
                        </td>
                        <td class="px-3 py-3">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-slate-800 dark:text-white" x-text="getDocTypeLabel(doc.kodeDokumen)"></span>
                                <span class="px-1.5 py-0.2 rounded bg-black/5 dark:bg-white/10 font-mono text-[10px] text-slate-500" x-text="doc.kodeDokumen"></span>
                            </div>
                        </td>
                        <td class="px-3 py-3 font-mono font-semibold text-slate-800 dark:text-slate-200" x-text="doc.nomorDokumen || '-'"></td>
                        <td class="px-3 py-3 font-mono text-slate-600 dark:text-slate-400 tabular-nums" x-text="doc.tanggalDokumen || '-'"></td>
                        <td class="px-3 py-3 text-center">
                            <template x-if="doc.shipmentDocumentId">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/80 inline-flex items-center gap-1">
                                    <i data-lucide="file-check" class="w-3 h-3"></i>
                                    <span>Tersimpan</span>
                                </span>
                            </template>
                            <template x-if="!doc.shipmentDocumentId">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 dark:bg-white/10 text-slate-500 inline-flex items-center gap-1">
                                    <i data-lucide="file-text" class="w-3 h-3"></i>
                                    <span>Metadata Saja</span>
                                </span>
                            </template>
                        </td>
                        <td class="px-3 py-3 text-center sticky right-0 z-10 bg-white dark:bg-[#1C1C1E] border-l border-black/[0.06] dark:border-white/[0.08]" @click.stop>
                            <div class="flex items-center justify-center gap-1">
                                <button type="button" @click="editDocModal(idx)"
                                        class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-white/10 text-[#0284c7] cursor-pointer" title="Edit Dokumen">
                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                </button>
                                <button type="button" @click="removeDoc(idx)"
                                        x-show="!isReadOnly"
                                        class="p-1.5 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 text-rose-600 cursor-pointer" title="Hapus Dokumen">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>
