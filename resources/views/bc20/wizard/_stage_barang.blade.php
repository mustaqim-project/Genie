<div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-5 border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-4 text-xs">
    <!-- Top Live BTKI Database Search Bar (Pencarian Cepat Pos Tarif) -->
    <div class="space-y-1.5 relative">
        <div class="flex items-center justify-between">
            <label class="block font-bold text-slate-800 dark:text-slate-100 text-xs">Lihat Database Pos Tarif (BTKI 2022)</label>
            <span class="text-[10px] text-slate-400 font-mono">11.574 Pos Tarif Resmi DJBC &bull; Tekan untuk Autocomplete</span>
        </div>
        <div class="relative">
            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-3"></i>
            <input type="text"
                   x-model="btkiSearchInput"
                   @input.debounce.300ms="onBtkiSearchInput()"
                   placeholder="Search by description, HS code, brand... (press Enter to search)"
                   class="w-full pl-9 pr-14 py-2.5 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white text-xs placeholder:text-slate-400 focus:ring-2 focus:ring-[#007AFF]/20 focus:border-[#007AFF]">
            <div class="absolute right-3 top-2.5 flex items-center gap-1.5">
                <button type="button" x-show="btkiSearchInput" @click="btkiSearchInput = ''; btkiSearchOpen = false"
                        class="p-1 rounded-full text-slate-400 hover:text-slate-700 dark:hover:text-white cursor-pointer" title="Hapus pencarian">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                </button>
                <template x-if="btkiSearchLoading">
                    <div class="w-4 h-4 border-2 border-[#007AFF] border-t-transparent rounded-full animate-spin"></div>
                </template>
            </div>
        </div>

        <!-- Quick Keyword Chips -->
        <div class="flex flex-wrap items-center gap-1.5 pt-0.5">
            <span class="text-[10px] text-slate-400 font-semibold">Pencarian Cepat:</span>
            <button type="button" @click="btkiSearchInput = '3824.40.00'; onBtkiSearchInput()"
                    class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 dark:bg-white/10 dark:hover:bg-white/20 text-[10px] font-mono text-slate-700 dark:text-slate-300 cursor-pointer transition-colors">
                3824.40.00 (PCE/Aditif Semen)
            </button>
            <button type="button" @click="btkiSearchInput = 'Aditif beton'; onBtkiSearchInput()"
                    class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 dark:bg-white/10 dark:hover:bg-white/20 text-[10px] text-slate-700 dark:text-slate-300 cursor-pointer transition-colors">
                Aditif Beton
            </button>
            <button type="button" @click="btkiSearchInput = 'Breaker'; onBtkiSearchInput()"
                    class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 dark:bg-white/10 dark:hover:bg-white/20 text-[10px] text-slate-700 dark:text-slate-300 cursor-pointer transition-colors">
                Hydraulic Breaker
            </button>
            <button type="button" @click="btkiSearchInput = 'Laptop'; onBtkiSearchInput()"
                    class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 dark:bg-white/10 dark:hover:bg-white/20 text-[10px] text-slate-700 dark:text-slate-300 cursor-pointer transition-colors">
                Laptop (8471.30.20)
            </button>
        </div>

        <!-- Autocomplete Results Dropdown -->
        <div x-show="btkiSearchOpen && btkiSearchResults.length > 0"
             @click.away="btkiSearchOpen = false"
             x-cloak
             class="absolute left-0 right-0 top-full mt-1 z-30 bg-white dark:bg-[#1C1C1E] rounded-2xl border border-black/10 dark:border-white/15 shadow-2xl max-h-72 overflow-y-auto divide-y divide-black/5 dark:divide-white/5">
            <div class="p-2.5 bg-slate-50 dark:bg-white/[0.04] text-[10px] font-bold text-slate-500 uppercase tracking-wider flex justify-between items-center">
                <span>Hasil Pencarian Database BTKI 2022</span>
                <span class="text-[#007AFF]" x-text="btkiSearchResults.length + ' Pos Ditemukan'"></span>
            </div>
            <template x-for="item in btkiSearchResults" :key="item.code">
                <div @click="selectBtkiSearchItem(item)"
                     class="p-3 hover:bg-slate-50 dark:hover:bg-white/[0.04] cursor-pointer flex items-start justify-between gap-3 text-xs transition-colors">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-mono font-bold text-[#007AFF]" x-text="item.code"></span>
                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold"
                                  :class="item.has_lartas ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'"
                                  x-text="item.has_lartas ? 'LARTAS' : 'NON LARTAS'"></span>
                        </div>
                        <p class="text-[11px] text-slate-600 dark:text-slate-300 line-clamp-1 mt-0.5" x-text="item.description"></p>
                    </div>
                    <div class="text-right shrink-0 font-mono text-[10px] text-slate-500">
                        <div>BM: <strong class="text-slate-800 dark:text-white" x-text="(item.duty_bm || 0) + '%'"></strong></div>
                        <div>PPN: <strong x-text="(item.duty_ppn || 11) + '%'"></strong> &bull; PPh: <strong x-text="(item.duty_pph || 2.5) + '%'"></strong></div>
                    </div>
                </div>
            </template>
        </div>
    </div>

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

        <div class="flex flex-wrap items-center gap-2">
            <!-- Tombol Rekomendasi HS Code Cerdas Batch (AI / BTKI) -->
            <button type="button" @click="autoRecommendAllHs()" :disabled="autoRecommendLoading"
                    class="px-3.5 py-1.5 rounded-xl border border-amber-300/80 dark:border-amber-700/60 bg-amber-50 dark:bg-amber-950/40 hover:bg-amber-100 text-amber-900 dark:text-amber-200 font-semibold inline-flex items-center gap-1.5 cursor-pointer shadow-xs transition-colors">
                <template x-if="!autoRecommendLoading">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-600"></i>
                </template>
                <template x-if="autoRecommendLoading">
                    <div class="w-3.5 h-3.5 border-2 border-amber-600 border-t-transparent rounded-full animate-spin"></div>
                </template>
                <span x-text="autoRecommendLoading ? 'Menganalisis Pos Tarif...' : '✨ Rekomendasi HS Cerdas (AI / BTKI)'"></span>
            </button>

            <button type="button" @click="openNewBarangModal()"
                    class="px-3 py-1.5 rounded-xl bg-[#007AFF] hover:bg-[#0066D6] text-white font-semibold inline-flex items-center gap-1.5 cursor-pointer shadow-xs">
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
                    <th class="px-3 py-2.5 text-center">Spesifikasi Lain</th>
                    <th class="px-3 py-2.5 text-right">Total Nilai</th>
                    <th class="px-3 py-2.5 text-right">NW</th>
                    <th class="px-3 py-2.5 text-center">Satuan</th>
                    <th class="px-3 py-2.5 text-center">Kemasan</th>
                    <th class="px-3 py-2.5 text-center">Dok</th>
                    <th class="px-3 py-2.5 text-center">PPN</th>
                    <th class="px-3 py-2.5 text-center">PPH</th>
                    <th class="px-3 py-2.5 text-center">BM</th>
                    <th class="px-3 py-2.5 text-center sticky right-0 z-20 bg-slate-50 dark:bg-[#1C1C1E] border-l border-black/[0.06] dark:border-white/[0.08] shadow-[-6px_0_12px_rgba(0,0,0,0.06)]">Aksi</th>
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
                        <td class="px-3 py-3 font-mono font-bold text-slate-800 dark:text-white">
                            <div class="flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full" :class="item.hs_code ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                                <span x-text="item.hs_code"></span>
                            </div>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold"
                                  :class="item.pernyataan_lartas === 'LARTAS' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' : 'bg-teal-50 text-teal-800 dark:bg-teal-950/40 dark:text-teal-300 border border-teal-200 dark:border-teal-800'"
                                  x-text="item.pernyataan_lartas || 'NON'"></span>
                        </td>
                        <td class="px-3 py-3">
                            <div class="font-semibold text-slate-900 dark:text-white uppercase" x-text="item.uraian"></div>
                            <div class="flex flex-wrap items-center gap-1 mt-1">
                                <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-white/10 text-[10px] text-slate-600 dark:text-slate-300" x-text="item.merk || 'TANPA MEREK'"></span>
                                <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-white/10 text-[10px] text-slate-600 dark:text-slate-300" x-text="item.tipe || 'TANPA TIPE'"></span>
                                <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-white/10 text-[10px] font-mono text-slate-600 dark:text-slate-300" x-text="item.negara_asal || 'CN'"></span>

                                <!-- Quick Smart HS Recommendation Pill -->
                                <button type="button" @click="openHsRecommendationModal(item._origIdx)"
                                        class="px-1.5 py-0.5 rounded bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 text-[10px] font-semibold inline-flex items-center gap-1 cursor-pointer transition-colors"
                                        title="Rekomendasikan HS Code BTKI 2022 berdasarkan nama barang ini">
                                    <i data-lucide="sparkles" class="w-2.5 h-2.5"></i>
                                    <span>Cek Rekomendasi HS</span>
                                </button>
                            </div>
                        </td>
                        <td class="px-3 py-3 text-center text-slate-500 font-mono text-[11px]" x-text="item.spesifikasi || '-'"></td>
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
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-slate-100 dark:bg-white/10 text-[10px] font-bold text-slate-700 dark:text-slate-200">
                                <span x-text="item.barang_dokumen ? item.barang_dokumen.length : 1"></span>
                                <i data-lucide="info" class="w-2.5 h-2.5 text-[#007AFF]"></i>
                            </span>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-md bg-sky-50 dark:bg-sky-950/50 text-[#007AFF] font-mono font-bold text-[10px]" x-text="(item.tarif_ppn ?? 11) + '%'"></span>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-md bg-sky-50 dark:bg-sky-950/50 text-[#007AFF] font-mono font-bold text-[10px]" x-text="(item.tarif_pph ?? 2.5) + '%'"></span>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <div>
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-white/10 font-mono font-bold text-[10px]" x-text="(item.tarif_bm ?? 0) + '%'"></span>
                                <div class="text-[9px] text-slate-400 mt-0.5 font-mono">Data INSW</div>
                            </div>
                        </td>
                        <td class="px-3 py-3 text-center whitespace-nowrap sticky right-0 z-10 bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xs border-l border-black/[0.06] dark:border-white/[0.08] shadow-[-6px_0_12px_rgba(0,0,0,0.06)]">
                            <div class="flex items-center justify-center gap-1">
                                <button type="button" @click="openEditBarangModal(item._origIdx)" title="Edit barang" class="p-1.5 rounded-lg text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/10 cursor-pointer">
                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                </button>
                                <button type="button" @click="openHsRecommendationModal(item._origIdx)" title="Rekomendasikan HS Code Cerdas" class="p-1.5 rounded-lg text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/40 cursor-pointer">
                                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
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

    <!-- ============================================================== -->
    <!-- MODAL REKOMENDASI HS CODE CERDAS INTERAKTIF (AI & BTKI 2022)   -->
    <!-- ============================================================== -->
    <div x-show="recommendModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="recommendModalOpen = false" class="bg-white dark:bg-[#1C1C1E] rounded-3xl border border-black/10 dark:border-white/15 shadow-2xl max-w-2xl w-full p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.08]">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 flex items-center justify-center">
                        <i data-lucide="sparkles" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-slate-900 dark:text-white">Rekomendasi Pos Tarif (HS Code BTKI 2022)</h4>
                        <p class="text-[11px] text-slate-500">Pencocokan Semantik AI &amp; Database Master DJBC (11.574 Pos Tarif Resmi)</p>
                    </div>
                </div>
                <button type="button" @click="recommendModalOpen = false" class="text-slate-400 hover:text-slate-800 dark:hover:text-white text-lg cursor-pointer">&times;</button>
            </div>

            <!-- Current Item Context -->
            <div class="p-3 rounded-2xl bg-slate-50 dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-semibold text-slate-600 dark:text-slate-300">Uraian Barang Produk:</span>
                    <span class="font-mono text-slate-500" x-text="activeRecommendIndex !== null ? 'Seri #' + (activeRecommendIndex + 1) : 'Form Barang'"></span>
                </div>
                <div class="flex items-center gap-2">
                    <input type="text" x-model="recommendSearchQuery" @keydown.enter="fetchHsRecommendations()"
                           placeholder="Ketik nama produk atau istilah teknis..."
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white text-xs font-semibold uppercase">
                    <button type="button" @click="fetchHsRecommendations()" :disabled="recommendLoading"
                            class="px-3.5 py-2 rounded-xl bg-[#007AFF] hover:bg-[#0066D6] text-white font-semibold text-xs shrink-0 cursor-pointer inline-flex items-center gap-1.5">
                        <i data-lucide="search" class="w-3.5 h-3.5"></i>
                        <span>Cari Ulang</span>
                    </button>
                </div>
            </div>

            <!-- Loading Spinner -->
            <div x-show="recommendLoading" class="py-8 text-center space-y-2">
                <div class="w-6 h-6 border-2 border-amber-600 border-t-transparent rounded-full animate-spin mx-auto"></div>
                <p class="text-xs text-slate-500">Mencocokkan spesifikasi produk dengan pos tarif BTKI 2022 &amp; regulasi Lartas...</p>
            </div>

            <!-- Candidate Cards List -->
            <div x-show="!recommendLoading" class="space-y-3 max-h-96 overflow-y-auto custom-scrollbar pr-1">
                <template x-for="(cand, cIdx) in recommendCandidates" :key="cand.clean_code || cIdx">
                    <div class="p-4 rounded-2xl border transition-all"
                         :class="cIdx === 0 ? 'bg-amber-50/50 dark:bg-amber-950/20 border-amber-300 dark:border-amber-700/60 ring-1 ring-amber-300/50' : 'bg-white dark:bg-[#2C2C2E]/60 border-black/[0.06] dark:border-white/10'">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-mono text-base font-bold text-slate-900 dark:text-white" x-text="cand.hs_code"></span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                          :class="cIdx === 0 ? 'bg-amber-100 text-amber-900 dark:bg-amber-900/50 dark:text-amber-200' : 'bg-slate-100 text-slate-700 dark:bg-white/10 dark:text-slate-300'"
                                          x-text="cand.confidence_label"></span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                          :class="cand.is_lartas ? 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300'"
                                          x-text="cand.lartas_badge"></span>
                                </div>
                                <h5 class="text-xs font-semibold text-slate-800 dark:text-slate-100 mt-1.5" x-text="cand.description"></h5>
                            </div>
                            <div class="shrink-0">
                                <button type="button" @click="activeRecommendIndex !== null ? applyHsRecommendation(cand) : applyHsRecommendationToModal(cand)"
                                        class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs inline-flex items-center gap-1.5 cursor-pointer shadow-xs transition-colors">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    <span>Gunakan Ini</span>
                                </button>
                            </div>
                        </div>

                        <!-- Rationale & Legal Basis -->
                        <div class="mt-2 text-[11px] text-slate-600 dark:text-slate-300 bg-white/70 dark:bg-black/20 p-2.5 rounded-xl border border-black/[0.04] dark:border-white/[0.04]">
                            <div class="font-semibold text-slate-700 dark:text-slate-200 flex items-center gap-1">
                                <i data-lucide="info" class="w-3 h-3 text-[#007AFF]"></i>
                                <span>Dasar Pertimbangan:</span>
                            </div>
                            <p class="mt-0.5" x-text="cand.rationale"></p>
                            <div class="mt-1 text-[10px] text-slate-400 font-mono truncate" x-text="cand.legal"></div>
                        </div>

                        <!-- Tariff Grid -->
                        <div class="grid grid-cols-4 gap-2 mt-2 pt-2 border-t border-black/[0.05] dark:border-white/[0.05] text-[10px] text-center font-mono">
                            <div class="p-1.5 rounded-lg bg-slate-100 dark:bg-white/[0.04]">
                                <div class="text-slate-500">Bea Masuk (BM)</div>
                                <div class="font-bold text-slate-800 dark:text-white" x-text="(cand.bm || 0) + '%'"></div>
                            </div>
                            <div class="p-1.5 rounded-lg bg-slate-100 dark:bg-white/[0.04]">
                                <div class="text-slate-500">PPN</div>
                                <div class="font-bold text-[#007AFF]" x-text="(cand.ppn || 11) + '%'"></div>
                            </div>
                            <div class="p-1.5 rounded-lg bg-slate-100 dark:bg-white/[0.04]">
                                <div class="text-slate-500">PPh Pasal 22</div>
                                <div class="font-bold text-[#007AFF]" x-text="(cand.pph || 2.5) + '%'"></div>
                            </div>
                            <div class="p-1.5 rounded-lg bg-slate-100 dark:bg-white/[0.04]">
                                <div class="text-slate-500">Bea Keluar (BK)</div>
                                <div class="font-bold text-slate-800 dark:text-white" x-text="(cand.bk || 0) + '%'"></div>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="recommendCandidates.length === 0 && !recommendLoading">
                    <div class="py-8 text-center text-slate-400">
                        <i data-lucide="alert-circle" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                        <p class="text-xs">Tidak ditemukan kandidat rekomendasi untuk kata kunci ini.</p>
                        <p class="text-[11px] mt-1">Coba masukkan nama komoditas umum (misal: "aditif beton", "breaker", "laptop").</p>
                    </div>
                </template>
            </div>

            <div class="flex justify-end pt-2 border-t border-black/[0.06] dark:border-white/[0.08]">
                <button type="button" @click="recommendModalOpen = false" class="btn-apple-gray text-xs px-4 py-2">Tutup</button>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL EDIT / TAMBAH BARANG MANUAL                               -->
    <!-- ============================================================== -->
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
                        <button type="button" @click="lookupHsForModal()" class="px-2 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold shrink-0 cursor-pointer" title="Cek tarif pos ini">Cek</button>
                        <button type="button" @click="recommendSearchQuery = barangForm.uraian || barangForm.hs_code; activeRecommendIndex = null; recommendModalOpen = true; fetchHsRecommendations()"
                                class="px-2.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-semibold shrink-0 cursor-pointer inline-flex items-center gap-1"
                                title="Rekomendasikan pos tarif dari uraian barang">
                            <i data-lucide="sparkles" class="w-3 h-3"></i>
                            <span>AI</span>
                        </button>
                    </div>
                    <p x-show="barangForm.hs_info" class="text-[10px] text-[#007AFF] mt-1 line-clamp-1" x-text="barangForm.hs_info"></p>
                </div>
                <div class="sm:col-span-2">
                    <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Uraian Barang *</label>
                    <input type="text" x-model="barangForm.uraian" placeholder="Contoh: PCE EARLY STRENGTH TYPE" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white uppercase">
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
                    <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Status Lartas &amp; Negara Asal</label>
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
