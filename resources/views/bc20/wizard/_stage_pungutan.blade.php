<div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-5 sm:p-6 border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-black/[0.06] dark:border-white/[0.06]">
        <div>
            <h3 class="font-bold text-base text-slate-900 dark:text-white">Pungutan</h3>
            <p class="text-[11px] text-slate-500 dark:text-slate-400">Rincian kalkulasi Bea Masuk & Pajak Dalam Rangka Impor (PDRI) dari seluruh seri barang.</p>
        </div>
        <button type="button" @click="recalculatePungutan(true)"
                class="px-3.5 py-2 rounded-xl border border-[#007AFF]/30 text-[#007AFF] hover:bg-[#007AFF]/10 font-semibold text-xs inline-flex items-center gap-1.5 cursor-pointer">
            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
            <span>Generate Pungutan</span>
        </button>
    </div>

    <div class="overflow-x-auto rounded-xl border border-black/[0.06] dark:border-white/[0.08]">
        <table class="w-full text-xs text-right">
            <thead class="bg-slate-50 dark:bg-white/[0.03] text-slate-600 dark:text-slate-300 font-semibold border-b border-black/[0.06] dark:border-white/[0.08]">
                <tr>
                    <th class="px-4 py-3 text-left">Jenis Pungutan</th>
                    <th class="px-4 py-3">Dibayar</th>
                    <th class="px-4 py-3">Ditanggung Pemerintah</th>
                    <th class="px-4 py-3">Ditunda</th>
                    <th class="px-4 py-3">Tidak Dipungut</th>
                    <th class="px-4 py-3">Dibebaskan</th>
                    <th class="px-4 py-3">Sudah Dilunasi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-black/[0.05] dark:divide-white/[0.06] font-mono tabular-nums">
                <template x-for="rowKey in ['PPH', 'PPN', 'BM', 'TOTAL']" :key="rowKey">
                    <tr :class="rowKey === 'TOTAL' ? 'bg-slate-50/80 dark:bg-white/[0.04] font-bold' : 'hover:bg-slate-50/40 dark:hover:bg-white/[0.02]'">
                        <td class="px-4 py-3.5 text-left font-sans font-bold text-slate-800 dark:text-white" x-text="rowKey"></td>
                        <td class="px-4 py-3.5 text-[#007AFF] font-semibold" x-text="formatInt(pungutan[rowKey]?.dibayar || 0)"></td>
                        <td class="px-4 py-3.5 text-slate-600 dark:text-slate-300" x-text="formatInt(pungutan[rowKey]?.ditanggung_pemerintah || 0)"></td>
                        <td class="px-4 py-3.5 text-slate-600 dark:text-slate-300" x-text="formatInt(pungutan[rowKey]?.ditunda || 0)"></td>
                        <td class="px-4 py-3.5 text-slate-600 dark:text-slate-300" x-text="formatInt(pungutan[rowKey]?.tidak_dipungut || 0)"></td>
                        <td class="px-4 py-3.5 text-slate-600 dark:text-slate-300" x-text="formatInt(pungutan[rowKey]?.dibebaskan || 0)"></td>
                        <td class="px-4 py-3.5 text-slate-600 dark:text-slate-300" x-text="formatInt(pungutan[rowKey]?.sudah_dilunasi || 0)"></td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>

