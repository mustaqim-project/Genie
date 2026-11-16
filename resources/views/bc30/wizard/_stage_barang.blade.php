<div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-5 border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-4 text-xs">
    <!-- Top Live BTKI Database Search Bar -->
    <div class="space-y-1.5 relative">
        <div class="flex items-center justify-between">
            <label class="block font-bold text-slate-800 dark:text-slate-100 text-xs">Lihat Database Pos Tarif Ekspor (BTKI 2022)</label>
            <span class="text-[10px] text-slate-400 font-mono">11.574 Pos Tarif Resmi DJBC — Tekan untuk Autocomplete</span>
        </div>
        <div class="relative">
            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-3"></i>
            <input type="text"
                   x-model="btkiSearchInput"
                   @input.debounce.300ms="onBtkiSearchInput()"
                   placeholder="Cari berdasarkan uraian, HS Code 8-digit, komoditas ekspor..."
                   class="w-full pl-9 pr-14 py-2.5 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white text-xs placeholder:text-slate-400 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7]">
            <div class="absolute right-3 top-2.5 flex items-center gap-1.5">
                <button type="button" x-show="btkiSearchInput" @click="btkiSearchInput = ''; btkiSearchOpen = false"
                        class="p-1 rounded-full text-slate-400 hover:text-slate-700 dark:hover:text-white cursor-pointer" title="Hapus pencarian">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                </button>
                <template x-if="btkiSearchLoading">
                    <div class="w-4 h-4 border-2 border-[#0284c7] border-t-transparent rounded-full animate-spin"></div>
                </template>
            </div>
        </div>

        <!-- Autocomplete Results Dropdown -->
        <div x-show="btkiSearchOpen && btkiSearchResults.length > 0"
             @click.away="btkiSearchOpen = false"
             x-cloak
             class="absolute left-0 right-0 top-full mt-1 z-30 bg-white dark:bg-[#1C1C1E] rounded-2xl border border-black/10 dark:border-white/15 shadow-2xl max-h-72 overflow-y-auto divide-y divide-black/5 dark:divide-white/5">
            <div class="p-2.5 bg-slate-50 dark:bg-white/[0.04] text-[10px] font-bold text-slate-500 uppercase tracking-wider flex justify-between items-center">
                <span>Hasil Pencarian Database BTKI 2022</span>
                <span class="text-[#0284c7]" x-text="btkiSearchResults.length + ' Pos Ditemukan'"></span>
            </div>
            <template x-for="item in btkiSearchResults" :key="item.code">
                <div @click="selectBtkiSearchItem(item)"
                     class="p-3 hover:bg-slate-50 dark:hover:bg-white/[0.04] cursor-pointer flex items-start justify-between gap-3 text-xs transition-colors">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-mono font-bold text-[#0284c7]" x-text="item.formatted_code || item.code"></span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] bg-slate-100 dark:bg-white/10 font-semibold" x-text="'BM ' + (item.duty_bm || 0) + '%'"></span>
                        </div>
                        <p class="text-slate-600 dark:text-slate-300 text-[11px] mt-0.5 truncate" x-text="item.description"></p>
                    </div>
                    <span class="text-[10px] text-slate-400 font-semibold shrink-0">Pilih &rarr;</span>
                </div>
            </template>
        </div>
    </div>

    <!-- Toolbar Barang -->
    <div class="flex flex-wrap items-center justify-between gap-2 pt-2 pb-3 border-b border-black/[0.06] dark:border-white/[0.06]">
        <div class="flex items-center gap-2">
            <span class="px-2.5 py-1 rounded-lg bg-[#0284c7]/10 text-[#0284c7] font-bold text-xs font-mono" x-text="'TOTAL: ' + w.barang.length + ' BARANG'"></span>
            <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">Daftar Komoditas Barang Ekspor</span>
        </div>

        <div class="flex items-center gap-1.5">
            <button type="button" @click="openNewBarangModal()"
                    :disabled="isReadOnly"
                    class="px-3.5 py-1.5 rounded-xl bg-[#0284c7] hover:bg-[#0066D6] text-white font-semibold inline-flex items-center gap-1.5 cursor-pointer disabled:opacity-50">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                <span>Tambah Barang</span>
            </button>
        </div>
    </div>

    <!-- Table Barang Ekspor -->
    <div class="overflow-x-auto rounded-xl border border-black/[0.06] dark:border-white/[0.08]">
        <table class="w-full text-xs text-left">
            <thead class="bg-slate-50 dark:bg-white/[0.03] text-slate-500 dark:text-slate-400 font-semibold border-b border-black/[0.06] dark:border-white/[0.08]">
                <tr>
                    <th class="px-3 py-2.5 w-12 text-center">Seri</th>
                    <th class="px-3 py-2.5">HS Code (BTKI)</th>
                    <th class="px-3 py-2.5">Uraian Barang Ekspor</th>
                    <th class="px-3 py-2.5 text-right">Jumlah &amp; Satuan</th>
                    <th class="px-3 py-2.5 text-right">Kemasan</th>
                    <th class="px-3 py-2.5 text-right">Netto (KG)</th>
                    <th class="px-3 py-2.5 text-right">FOB (<span x-text="w.transaksi.kode_valuta"></span>)</th>
                    <th class="px-3 py-2.5 text-center">Tarif BK</th>
                    <th class="px-3 py-2.5 text-center sticky right-0 z-20 bg-slate-50 dark:bg-[#1C1C1E] border-l border-black/[0.06] dark:border-white/[0.08]">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-black/[0.05] dark:divide-white/[0.06]">
                <template x-for="(item, idx) in w.barang" :key="idx">
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-white/[0.02] transition-colors">
                        <td class="px-3 py-3 text-center font-mono font-bold text-slate-700 dark:text-slate-300">
                            #<span x-text="item.seri || (idx + 1)"></span>
                        </td>
                        <td class="px-3 py-3">
                            <span class="font-mono font-bold text-[#0284c7]" x-text="formatHsCode(item.hs_code)"></span>
                        </td>
                        <td class="px-3 py-3">
                            <div class="font-semibold text-slate-800 dark:text-white truncate max-w-[240px]" x-text="item.uraian"></div>
                            <div class="text-[10px] text-slate-400" x-text="(item.merk ? 'Merek: ' + item.merk : '') + (item.tipe ? ' | Tipe: ' + item.tipe : '')"></div>
                        </td>
                        <td class="px-3 py-3 text-right font-mono tabular-nums">
                            <span x-text="formatNumber(item.jumlah)"></span> <span class="text-slate-400 text-[10px]" x-text="item.satuan"></span>
                        </td>
                        <td class="px-3 py-3 text-right font-mono tabular-nums">
                            <span x-text="item.jumlah_kemasan || 1"></span> <span class="text-slate-400 text-[10px]" x-text="item.kemasan || 'PK'"></span>
                        </td>
                        <td class="px-3 py-3 text-right font-mono tabular-nums" x-text="formatNumber(item.netto)"></td>
                        <td class="px-3 py-3 text-right font-mono font-bold tabular-nums text-slate-900 dark:text-white" x-text="formatNumber(item.fob)"></td>
                        <td class="px-3 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full font-mono text-[10px] font-bold"
                                  :class="(item.tarif_bk > 0) ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' : 'bg-slate-100 text-slate-600 dark:bg-white/10 dark:text-slate-400'"
                                  x-text="(item.tarif_bk || 0) + '%'">
                            </span>
                        </td>
                        <td class="px-3 py-3 text-center sticky right-0 z-10 bg-white dark:bg-[#1C1C1E] border-l border-black/[0.06] dark:border-white/[0.08]">
                            <div class="flex items-center justify-center gap-1">
                                <button type="button" @click="editBarangModal(idx)"
                                        class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-white/10 text-[#0284c7] cursor-pointer" title="Edit Barang">
                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                </button>
                                <button type="button" @click="removeBarang(idx)"
                                        x-show="!isReadOnly && w.barang.length > 1"
                                        class="p-1.5 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 text-rose-600 cursor-pointer" title="Hapus Barang">
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
