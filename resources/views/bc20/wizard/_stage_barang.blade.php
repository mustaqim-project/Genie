<div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-5 border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-4 text-xs">
    <!-- Toolbar -->
    <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-black/[0.06] dark:border-white/[0.06]">
        <div class="flex flex-wrap items-center gap-2">
            <span class="px-2.5 py-1 rounded-lg bg-[#007AFF] text-white font-bold font-mono" x-text="'TOTAL: ' + w.barang.length"></span>
            <div class="relative">
                <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-2.5"></i>
                <input type="text" x-model="barangSearch" placeholder="Cari berdasarkan uraian atau HS code..."
                       class="pl-8 pr-3 py-1.5 w-64 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white text-xs">
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" @click="openNewBarangModal()"
                    class="px-3 py-1.5 rounded-xl bg-[#007AFF] hover:bg-[#0066D6] text-white font-semibold inline-flex items-center gap-1.5 cursor-pointer">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                <span>Tambah Barang</span>
            </button>
            <button type="button" @click="syncAllHsRates()"
                    class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] hover:bg-slate-50 text-slate-700 dark:text-slate-200 font-semibold inline-flex items-center gap-1.5 cursor-pointer">
                <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-emerald-600"></i>
                <span>Sync Tarif BTKI</span>
            </button>
        </div>
    </div>

    <!-- Table Barang -->
    <div class="overflow-x-auto rounded-xl border border-black/[0.06] dark:border-white/[0.08]">
        <table class="w-full text-xs text-left">
            <thead class="bg-slate-50 dark:bg-white/[0.03] text-slate-500 dark:text-slate-400 font-semibold border-b border-black/[0.06] dark:border-white/[0.08]">
                <tr>
                    <th class="px-3 py-2.5 text-center">Seri</th>
                    <th class="px-3 py-2.5 text-center">Group</th>
                    <th class="px-3 py-2.5">HS</th>
                    <th class="px-3 py-2.5 text-center">Lartas</th>
                    <th class="px-3 py-2.5">Uraian</th>
                    <th class="px-3 py-2.5 text-right">Total Nilai</th>
                    <th class="px-3 py-2.5 text-right">NW</th>
                    <th class="px-3 py-2.5 text-center">Satuan</th>
                    <th class="px-3 py-2.5 text-center">Kemasan</th>
                    <th class="px-3 py-2.5 text-center">PPN</th>
                    <th class="px-3 py-2.5 text-center">PPH</th>
                    <th class="px-3 py-2.5 text-center">BM</th>
                    <th class="px-3 py-2.5 text-center">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-black/[0.05] dark:divide-white/[0.06]">
                <template x-for="(item, idx) in filteredBarang()" :key="item._uid || idx">
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-white/[0.02]">
                        <td class="px-3 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full bg-slate-100 dark:bg-white/10 font-mono font-bold" x-text="item.seri"></span>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-md bg-sky-50 dark:bg-sky-950/50 text-[#007AFF] font-mono font-bold" x-text="item.group || 1"></span>
                        </td>
                        <td class="px-3 py-3 font-mono font-bold text-slate-800 dark:text-white" x-text="item.hs_code"></td>
                        <td class="px-3 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold"
                                  :class="item.pernyataan_lartas === 'LARTAS' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-slate-300'"
                                  x-text="item.pernyataan_lartas || 'NON'"></span>
                        </td>
                        <td class="px-3 py-3">
                            <div class="font-semibold text-slate-900 dark:text-white uppercase" x-text="item.uraian"></div>
                            <div class="flex flex-wrap gap-1 mt-1">
                                <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-white/10 text-[10px] text-slate-600 dark:text-slate-300" x-text="item.merk || 'TANPA MEREK'"></span>
                                <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-white/10 text-[10px] text-slate-600 dark:text-slate-300" x-text="item.tipe || 'TANPA TIPE'"></span>
                                <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-white/10 text-[10px] font-mono text-slate-600 dark:text-slate-300" x-text="item.negara_asal || 'CN'"></span>
                            </div>
                        </td>
                        <td class="px-3 py-3 text-right font-mono font-bold tabular-nums text-slate-900 dark:text-white" x-text="formatDecimal(item.fob)"></td>
                        <td class="px-3 py-3 text-right font-mono tabular-nums text-slate-700 dark:text-slate-300" x-text="formatDecimal(item.netto) + ' kg'"></td>
                        <td class="px-3 py-3 text-center font-mono">
                            <span class="font-bold" x-text="item.jumlah"></span>
                            <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-white/10 text-[10px]" x-text="item.satuan"></span>
                        </td>
                        <td class="px-3 py-3 text-center font-mono">
                            <span class="font-bold" x-text="item.jumlah_kemasan"></span>
                            <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-white/10 text-[10px]" x-text="item.kemasan"></span>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-md bg-sky-50 dark:bg-sky-950/50 text-[#007AFF] font-mono font-bold text-[10px]" x-text="(item.tarif_ppn ?? 11) + '%'"></span>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-md bg-sky-50 dark:bg-sky-950/50 text-[#007AFF] font-mono font-bold text-[10px]" x-text="(item.tarif_pph ?? 2.5) + '%'"></span>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-white/10 font-mono font-bold text-[10px]" x-text="(item.tarif_bm ?? 0) + '%'"></span>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <button type="button" @click="openEditBarangModal(item._origIdx)" title="Edit barang" class="p-1.5 rounded-lg text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/10 cursor-pointer">
                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                </button>
                                <button type="button" @click="duplicateBarang(item._origIdx)" title="Duplikat barang" class="p-1.5 rounded-lg text-[#007AFF] hover:bg-[#007AFF]/10 cursor-pointer">
                                    <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                </button>
                                <button type="button" @click="removeBarang(item._origIdx)" x-show="w.barang.length > 1" title="Hapus barang" class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 cursor-pointer">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- Modal Edit / Tambah Barang -->
    <div x-show="barangModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="barangModalOpen = false" class="bg-white dark:bg-[#1C1C1E] rounded-3xl border border-black/10 dark:border-white/10 shadow-2xl max-w-3xl w-full p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.08]">
                <h4 class="font-bold text-sm text-slate-900 dark:text-white" x-text="editingBarangIndex === null ? 'Tambah Item Barang Baru' : 'Edit Rincian Barang Seri #' + (editingBarangIndex + 1)"></h4>
                <button type="button" @click="barangModalOpen = false" class="text-slate-400 hover:text-slate-800 dark:hover:text-white text-lg cursor-pointer">&times;</button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                <div>
                    <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">HS Code (BTKI) *</label>
                    <div class="flex gap-1.5">
                        <input type="text" x-model="barangForm.hs_code" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono">
                        <button type="button" @click="lookupHsForModal()" class="px-2.5 py-2 rounded-xl bg-[#007AFF]/10 text-[#007AFF] font-semibold shrink-0 cursor-pointer">Cek</button>
                    </div>
                    <p x-show="barangForm.hs_info" class="text-[10px] text-[#007AFF] mt-1" x-text="barangForm.hs_info"></p>
                </div>
                <div class="sm:col-span-2">
                    <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Uraian Barang *</label>
                    <input type="text" x-model="barangForm.uraian" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white uppercase">
                </div>
                <div>
                    <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Merek</label>
                    <input type="text" x-model="barangForm.merk" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white">
                </div>
                <div>
                    <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Tipe</label>
                    <input type="text" x-model="barangForm.tipe" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white">
                </div>
                <div>
                    <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Status Lartas & Negara Asal</label>
                    <div class="grid grid-cols-2 gap-1.5">
                        <select x-model="barangForm.pernyataan_lartas" class="px-2 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white">
                            <option value="NON">NON</option>
                            <option value="LARTAS">LARTAS</option>
                        </select>
                        <select x-model="barangForm.negara_asal" class="px-2 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white">
                            @foreach($refOptions['negara'] as $code => $label)
                                <option value="{{ $code }}">{{ $code }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-6 gap-3 text-xs pt-2 border-t border-black/[0.06] dark:border-white/[0.06]">
                <div>
                    <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Jumlah Satuan</label>
                    <input type="number" step="0.01" x-model.number="barangForm.jumlah" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono">
                </div>
                <div>
                    <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Satuan</label>
                    <select x-model="barangForm.satuan" class="w-full px-2 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono">
                        @foreach($refOptions['satuan'] as $sat)
                            <option value="{{ $sat }}">{{ $sat }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Jml Kemasan</label>
                    <input type="number" x-model.number="barangForm.jumlah_kemasan" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono">
                </div>
                <div>
                    <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Jenis Kemasan</label>
                    <select x-model="barangForm.kemasan" class="w-full px-2 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono">
                        @foreach($refOptions['kemasan'] as $code => $label)
                            <option value="{{ $code }}">{{ $code }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Netto (KG)</label>
                    <input type="number" step="0.01" x-model.number="barangForm.netto" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono">
                </div>
                <div>
                    <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Nilai CIF/FOB *</label>
                    <input type="number" step="0.01" x-model.number="barangForm.fob" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono font-bold">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs pt-2 border-t border-black/[0.06] dark:border-white/[0.06]">
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-white/[0.04] space-y-2">
                    <label class="block font-bold text-slate-700 dark:text-slate-200">BM (%)</label>
                    <div class="grid grid-cols-2 gap-1.5">
                        <input type="number" step="0.1" x-model.number="barangForm.tarif_bm" class="px-2 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#1C1C1E] dark:text-white font-mono">
                        <select x-model="barangForm.kode_fasilitas_bm" class="px-2 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#1C1C1E] dark:text-white text-[11px]">
                            @foreach($refOptions['fasilitas_tarif'] as $fCode => $fLabel)
                                <option value="{{ $fCode }}">{{ $fLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-white/[0.04] space-y-2">
                    <label class="block font-bold text-slate-700 dark:text-slate-200">PPN (%)</label>
                    <div class="grid grid-cols-2 gap-1.5">
                        <input type="number" step="0.1" x-model.number="barangForm.tarif_ppn" class="px-2 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#1C1C1E] dark:text-white font-mono">
                        <select x-model="barangForm.kode_fasilitas_ppn" class="px-2 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#1C1C1E] dark:text-white text-[11px]">
                            @foreach($refOptions['fasilitas_tarif'] as $fCode => $fLabel)
                                <option value="{{ $fCode }}">{{ $fLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-white/[0.04] space-y-2">
                    <label class="block font-bold text-slate-700 dark:text-slate-200">PPh 22 (%)</label>
                    <div class="grid grid-cols-2 gap-1.5">
                        <input type="number" step="0.1" x-model.number="barangForm.tarif_pph" class="px-2 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#1C1C1E] dark:text-white font-mono">
                        <select x-model="barangForm.kode_fasilitas_pph" class="px-2 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#1C1C1E] dark:text-white text-[11px]">
                            @foreach($refOptions['fasilitas_tarif'] as $fCode => $fLabel)
                                <option value="{{ $fCode }}">{{ $fLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" @click="barangModalOpen = false" class="btn-apple-gray text-xs px-4 py-2">Batal</button>
                <button type="button" @click="saveBarangModal()" class="btn-apple-filled text-xs px-5 py-2 cursor-pointer">Simpan Item Barang</button>
            </div>
        </div>
    </div>
</div>

