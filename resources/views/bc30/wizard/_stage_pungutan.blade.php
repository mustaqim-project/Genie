<div class="space-y-4 text-xs">
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-5 border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.06]">
            <div>
                <h3 class="font-bold text-sm text-slate-900 dark:text-white">Pungutan Ekspor (Bea Keluar &amp; Sawit)</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Kalkulasi kewajiban fiskal pabean ekspor berdasarkan tarif BTKI dan regulasi Kementerian Keuangan.</p>
            </div>
            <span class="px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 font-bold font-mono text-[10px]">
                EXPORT DUTY
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-4 rounded-xl bg-slate-50 dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/10 space-y-1">
                <span class="text-[11px] text-slate-500 dark:text-slate-400 font-semibold">Total Nilai Pabean (FOB IDR)</span>
                <div class="text-base font-mono font-bold text-slate-900 dark:text-white tabular-nums" x-text="'Rp ' + formatInt(computedFobRupiah())"></div>
            </div>

            <div class="p-4 rounded-xl bg-slate-50 dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/10 space-y-1">
                <span class="text-[11px] text-slate-500 dark:text-slate-400 font-semibold">Estimasi Bea Keluar (BK)</span>
                <div class="text-base font-mono font-bold text-amber-600 dark:text-amber-400 tabular-nums" x-text="'Rp ' + formatInt(computedTotalBk())"></div>
            </div>

            <div class="p-4 rounded-xl bg-emerald-50/80 dark:bg-emerald-950/30 border border-emerald-200/80 dark:border-emerald-800/50 space-y-1">
                <span class="text-[11px] text-emerald-800 dark:text-emerald-300 font-semibold">Total Pungutan Negara Ekspor</span>
                <div class="text-lg font-mono font-bold text-emerald-700 dark:text-emerald-400 tabular-nums" x-text="'Rp ' + formatInt(computedTotalBk())"></div>
            </div>
        </div>

        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-white/[0.02] border border-black/[0.06] dark:border-white/[0.06] flex items-center justify-between text-[11px]">
            <span class="text-slate-500">Status Pembayaran Bea Keluar:</span>
            <template x-if="computedTotalBk() === 0">
                <span class="px-2.5 py-0.5 rounded-full font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                    BEBAS PUNGUTAN EKSPOR (NIHIL)
                </span>
            </template>
            <template x-if="computedTotalBk() > 0">
                <span class="px-2.5 py-0.5 rounded-full font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                    WAJIB BILLING MPN G3
                </span>
            </template>
        </div>
    </div>
</div>
