<div class="space-y-4 text-xs">
    <!-- Card 1: Peti Kemas (Kontainer) -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-5 border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.06]">
            <div>
                <h3 class="font-bold text-sm text-slate-900 dark:text-white">Peti Kemas (Kontainer)</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Rincian nomor kontainer ekspor yang digunakan.</p>
            </div>
            <span class="px-2 py-0.5 rounded-lg bg-sky-50 dark:bg-sky-950/40 text-[#0284c7] font-bold font-mono text-[10px]">
                CONTAINER
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Nomor Peti Kemas</label>
                <input type="text" x-model="w.kemasan.nomor_kontainer" :disabled="isReadOnly" placeholder="Contoh: WHSU1234567" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono font-bold uppercase">
            </div>

            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Ukuran Kontainer</label>
                <select x-model="w.kemasan.kode_ukuran_kontainer" :disabled="isReadOnly" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-semibold">
                    <option value="20">20 Feet</option>
                    <option value="40">40 Feet</option>
                    <option value="45">45 Feet</option>
                </select>
            </div>

            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Tipe Muatan</label>
                <select x-model="w.kemasan.kode_tipe_kontainer" :disabled="isReadOnly" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-semibold">
                    <option value="FCL">FCL - Full Container Load</option>
                    <option value="LCL">LCL - Less Container Load</option>
                </select>
            </div>

            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Total Kontainer</label>
                <input type="number" x-model.number="w.kemasan.total_kontainer" :disabled="isReadOnly" min="0" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono font-bold">
            </div>
        </div>
    </div>

    <!-- Card 2: Kemasan & Berat -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-5 border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.06]">
            <div>
                <h3 class="font-bold text-sm text-slate-900 dark:text-white">Kemasan &amp; Berat Kargo</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Total kemasan fisik, berat kotor (bruto), dan berat bersih (netto) dalam satuan KG.</p>
            </div>
            <span class="px-2 py-0.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 font-bold font-mono text-[10px]">
                PACKAGE &amp; WEIGHT
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Jumlah Kemasan <span class="text-rose-500">*</span></label>
                <input type="number" x-model.number="w.kemasan.jumlah_kemasan" :disabled="isReadOnly" min="1" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono font-bold">
            </div>

            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Jenis Kemasan <span class="text-rose-500">*</span></label>
                <select x-model="w.kemasan.kode_kemasan" :disabled="isReadOnly" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-semibold">
                    <option value="PK">PK - PACKAGE</option>
                    <option value="BG">BG - BAG / KARUNG</option>
                    <option value="CT">CT - CARTON / KARDUS</option>
                    <option value="PX">PX - PALLET</option>
                    <option value="DR">DR - DRUM</option>
                    <option value="CS">CS - CASE</option>
                    <option value="RO">RO - ROLL</option>
                </select>
            </div>

            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Berat Kotor (Bruto KG) <span class="text-rose-500">*</span></label>
                <input type="number" step="0.0001" x-model.number="w.kemasan.bruto" :disabled="isReadOnly" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono font-bold">
            </div>

            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Berat Bersih (Netto KG) <span class="text-rose-500">*</span></label>
                <input type="number" step="0.0001" x-model.number="w.kemasan.netto" :disabled="isReadOnly" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono font-bold">
            </div>
        </div>
    </div>
</div>
