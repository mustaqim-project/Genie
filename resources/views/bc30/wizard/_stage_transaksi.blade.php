<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 text-xs">
    <!-- Card 1: Valuta & Nilai FOB -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-4 border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-3">
        <div class="flex items-center justify-between pb-2 border-b border-black/[0.06] dark:border-white/[0.06]">
            <h3 class="font-bold text-sm text-slate-900 dark:text-white">Nilai Ekspor (FOB)</h3>
            <span class="px-2 py-0.5 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200/80 text-[10px] font-semibold">
                Free on Board
            </span>
        </div>

        <div class="grid grid-cols-2 gap-2">
            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Valuta <span class="text-rose-500">*</span></label>
                <select x-model="w.transaksi.kode_valuta" :disabled="isReadOnly" @change="fetchActiveKurs()" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-bold">
                    <option value="USD">USD</option>
                    <option value="CNY">CNY</option>
                    <option value="EUR">EUR</option>
                    <option value="SGD">SGD</option>
                    <option value="JPY">JPY</option>
                    <option value="IDR">IDR</option>
                </select>
            </div>
            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">NDPBM (Kurs) <span class="text-rose-500">*</span></label>
                <input type="number" step="0.01" x-model.number="w.transaksi.ndbm_kurs" :disabled="isReadOnly" @input="recalculatePungutan()" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono tabular-nums">
            </div>
        </div>

        <div>
            <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Cara Penyerahan (Incoterm) <span class="text-rose-500">*</span></label>
            <select x-model="w.transaksi.kode_incoterm" :disabled="isReadOnly" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-semibold">
                <option value="FOB">FOB - Free on Board</option>
                <option value="CFR">CFR - Cost and Freight</option>
                <option value="CIF">CIF - Cost, Insurance and Freight</option>
                <option value="EXW">EXW - Ex Works</option>
                <option value="FCA">FCA - Free Carrier</option>
                <option value="CIP">CIP - Carriage and Insurance Paid</option>
                <option value="DAP">DAP - Delivered at Place</option>
                <option value="DDP">DDP - Delivered Duty Paid</option>
            </select>
        </div>

        <div>
            <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Total Nilai FOB (<span x-text="w.transaksi.kode_valuta"></span>) <span class="text-rose-500">*</span></label>
            <input type="number" step="0.01" x-model.number="w.transaksi.fob" :disabled="isReadOnly" @input="recalculatePungutan()" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono font-bold tabular-nums">
        </div>

        <div class="p-3 rounded-xl bg-slate-50 dark:bg-white/[0.04] border border-black/[0.06] dark:border-white/10">
            <span class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400">Nilai Ekspor dalam Rupiah (FOB &times; NDPBM)</span>
            <div class="font-mono font-bold text-base text-[#0284c7] tabular-nums mt-0.5" x-text="'Rp ' + formatInt(computedFobRupiah())"></div>
        </div>
    </div>

    <!-- Card 2: Biaya Tambahan -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-4 border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-3">
        <div class="pb-2 border-b border-black/[0.06] dark:border-white/[0.06]">
            <h3 class="font-bold text-sm text-slate-900 dark:text-white">Komponen Nilai Tambahan</h3>
            <p class="text-[10px] text-slate-400">Opsional, jika transaksi menggunakan term selain murni FOB.</p>
        </div>

        <div>
            <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Freight (Biaya Tambang)</label>
            <input type="number" step="0.01" x-model.number="w.transaksi.freight" :disabled="isReadOnly" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono tabular-nums">
        </div>

        <div>
            <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Asuransi Ekspor</label>
            <input type="number" step="0.01" x-model.number="w.transaksi.asuransi" :disabled="isReadOnly" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono tabular-nums">
        </div>

        <div>
            <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Nilai Maklon (Jasa Pengerjaan)</label>
            <input type="number" step="0.01" x-model.number="w.transaksi.nilai_maklon" :disabled="isReadOnly" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono tabular-nums">
        </div>
    </div>

    <!-- Card 3: Bank Devisa Hasil Ekspor (DHE) -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-4 border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-3">
        <div class="pb-2 border-b border-black/[0.06] dark:border-white/[0.06]">
            <h3 class="font-bold text-sm text-slate-900 dark:text-white">Bank Devisa (DHE)</h3>
            <p class="text-[10px] text-slate-400">Bank penerima devisa hasil ekspor di Indonesia.</p>
        </div>

        <div>
            <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Kode Bank</label>
            <input type="text" x-model="w.transaksi.bank_kode" :disabled="isReadOnly" placeholder="Contoh: BMRI (Mandiri), BBCA (BCA)" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white uppercase font-mono font-bold">
        </div>

        <div>
            <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Nama Bank Devisa</label>
            <input type="text" x-model="w.transaksi.bank_nama" :disabled="isReadOnly" placeholder="Contoh: PT BANK MANDIRI (PERSERO) TBK" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white uppercase font-semibold">
        </div>
    </div>
</div>
