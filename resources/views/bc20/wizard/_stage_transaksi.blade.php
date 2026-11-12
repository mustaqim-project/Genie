<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 text-xs">
    <!-- Card 1: Harga -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-4 border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-3">
        <div class="flex items-center justify-between pb-2 border-b border-black/[0.06] dark:border-white/[0.06]">
            <h3 class="font-bold text-sm text-slate-900 dark:text-white">Harga</h3>
            <span class="px-2 py-0.5 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200/80 text-[10px] font-semibold">
                &#9888; Pastikan valuta sesuai
            </span>
        </div>

        <div class="grid grid-cols-2 gap-2">
            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Valuta <span class="text-rose-500">*</span></label>
                <select x-model="w.transaksi.kode_valuta" @change="fetchActiveKurs()" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-bold">
                    <option value="USD">USD</option>
                    <option value="CNY">CNY</option>
                    <option value="EUR">EUR</option>
                    <option value="SGD">SGD</option>
                    <option value="JPY">JPY</option>
                </select>
            </div>
            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">NDPBM (Kurs) <span class="text-rose-500">*</span></label>
                <input type="number" step="0.01" x-model.number="w.transaksi.ndbm_kurs" @input="recalculatePungutan()" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono tabular-nums">
            </div>
        </div>

        <div>
            <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Jenis Transaksi <span class="text-rose-500">*</span></label>
            <select x-model="w.transaksi.kode_jenis_nilai" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white">
                @foreach($refOptions['jenis_transaksi'] as $code => $label)
                    <option value="{{ $code }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Incoterm <span class="text-rose-500">*</span></label>
            <select x-model="w.transaksi.kode_incoterm" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-semibold">
                @foreach($refOptions['incoterm'] as $inc)
                    <option value="{{ $inc }}">{{ $inc }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Harga Barang (<span x-text="w.transaksi.kode_valuta"></span>) <span class="text-rose-500">*</span></label>
            <input type="number" step="0.01" x-model.number="w.transaksi.fob" @input="recalculatePungutan()" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono font-bold tabular-nums">
        </div>

        <div class="p-3 rounded-xl bg-slate-50 dark:bg-white/[0.04] border border-black/[0.06] dark:border-white/10">
            <span class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400">Nilai Pabean dalam Rupiah (CIF &times; NDPBM)</span>
            <div class="font-mono font-bold text-base text-[#007AFF] tabular-nums mt-0.5" x-text="'Rp ' + formatInt(computedCifRupiah())"></div>
        </div>
    </div>

    <!-- Card 2: Harga Lainnya -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-4 border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-3">
        <div class="pb-2 border-b border-black/[0.06] dark:border-white/[0.06]">
            <h3 class="font-bold text-sm text-slate-900 dark:text-white">Harga Lainnya</h3>
        </div>

        <div>
            <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1" x-text="'Biaya penambahan - ' + w.transaksi.kode_valuta"></label>
            <input type="number" step="0.01" x-model.number="w.transaksi.biaya_tambahan" @input="recalculatePungutan()" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono tabular-nums">
        </div>

        <div>
            <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1" x-text="'Biaya pengurang - ' + w.transaksi.kode_valuta"></label>
            <input type="number" step="0.01" x-model.number="w.transaksi.biaya_pengurang" @input="recalculatePungutan()" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono tabular-nums">
        </div>

        <div>
            <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1" x-text="'Freight - ' + w.transaksi.kode_valuta"></label>
            <input type="number" step="0.01" x-model.number="w.transaksi.freight" @input="recalculatePungutan()" class="w-full px-2.5 py-2 rounded-xl border-l-4 border-l-rose-500 border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono tabular-nums">
        </div>

        <div @click="$refs.freightUploadInput.click()"
             class="p-3 rounded-xl border border-dashed border-slate-300 dark:border-white/20 bg-slate-50/70 dark:bg-white/[0.02] hover:border-[#007AFF] transition-colors cursor-pointer flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                <i data-lucide="file-up" class="w-4 h-4"></i>
            </div>
            <div class="min-w-0">
                <div class="font-bold text-slate-800 dark:text-white text-[11px]">Unggah screenshot atau PDF freight</div>
                <div class="text-[10px] text-slate-500 dark:text-slate-400">Klik untuk memilih gambar / PDF bukti biaya freight</div>
            </div>
            <input type="file" x-ref="freightUploadInput" class="hidden" accept=".pdf,.png,.jpg,.jpeg,.webp" @change="handleWizardDocUpload($event, 'freight')">
        </div>

        <button type="button" x-show="w.transaksi.freight_document_id" @click="previewByDocId(w.transaksi.freight_document_id)"
                class="w-full py-1.5 rounded-xl bg-[#007AFF]/10 text-[#007AFF] font-semibold text-[11px] cursor-pointer">
            &bull; Lihat Preview Bukti Freight
        </button>

        <div class="grid grid-cols-2 gap-2">
            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Asuransi</label>
                <select x-model="w.transaksi.kode_asuransi" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white">
                    <option value="LN">LUAR NEGERI</option>
                    <option value="DN">DALAM NEGERI</option>
                </select>
            </div>
            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1" x-text="'Nilai Asuransi - ' + w.transaksi.kode_valuta"></label>
                <input type="number" step="0.01" x-model.number="w.transaksi.asuransi" @input="recalculatePungutan()" class="w-full px-2.5 py-2 rounded-xl border-l-4 border-l-rose-500 border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono tabular-nums">
            </div>
        </div>

        <div>
            <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Voluntary declaration</label>
            <input type="number" step="0.01" x-model.number="w.transaksi.vd" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono tabular-nums">
        </div>
    </div>

    <!-- Card 3: Berat -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-4 border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-4">
        <div class="pb-2 border-b border-black/[0.06] dark:border-white/[0.06]">
            <h3 class="font-bold text-sm text-slate-900 dark:text-white">Berat</h3>
        </div>

        <div>
            <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Berat Kotor (KGM) <span class="text-rose-500">*</span></label>
            <input type="number" step="0.01" x-model.number="w.transaksi.bruto" class="w-full px-3 py-3 rounded-xl border-l-4 border-l-emerald-500 border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono text-lg font-bold tabular-nums">
        </div>

        <div>
            <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Berat Bersih (KGM) <span class="text-rose-500">*</span></label>
            <input type="number" step="0.01" x-model.number="w.transaksi.netto" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono font-bold tabular-nums">
            <button type="button" @click="w.transaksi.netto = w.transaksi.bruto"
                    class="mt-2 text-[11px] font-semibold text-[#007AFF] hover:underline cursor-pointer">
                Samakan ke berat kotor
            </button>
            <p x-show="parseFloat(w.transaksi.netto) !== parseFloat(w.transaksi.bruto)" class="text-[10px] text-amber-600 dark:text-amber-400 mt-1">
                Berat bersih berbeda dari berat kotor. Gunakan tombol di atas jika ingin menyamakan.
            </p>
        </div>
    </div>
</div>

