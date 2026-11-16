<div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-5 border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-4 text-xs">
    <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.06]">
        <div>
            <h3 class="font-bold text-sm text-slate-900 dark:text-white">Sarana Pengangkut &amp; Rute Ekspor</h3>
            <p class="text-[11px] text-slate-500 dark:text-slate-400">Informasi kapal/pesawat pengangkut dan jadwal keberangkatan kargo.</p>
        </div>
        <span class="px-2 py-0.5 rounded-lg bg-sky-50 dark:bg-sky-950/40 text-[#0284c7] font-bold font-mono text-[10px]">
            TRANSPORT &amp; ROUTING
        </span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div>
            <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Cara Pengangkutan <span class="text-rose-500">*</span></label>
            <select x-model="w.pengangkut.cara_angkut" :disabled="isReadOnly" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-semibold">
                <option value="1">1 - LAUT</option>
                <option value="2">2 - UDARA</option>
                <option value="3">3 - DARAT</option>
            </select>
        </div>

        <div>
            <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Nama Sarana Pengangkut <span class="text-rose-500">*</span></label>
            <input type="text" x-model="w.pengangkut.nama_sarana_pengangkut" :disabled="isReadOnly" placeholder="Contoh: WAN HAI 315" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white uppercase font-semibold">
        </div>

        <div>
            <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Nomor Voyage / Flight <span class="text-rose-500">*</span></label>
            <input type="text" x-model="w.pengangkut.voyage_no" :disabled="isReadOnly" placeholder="Contoh: 045N" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white uppercase font-mono font-bold">
        </div>

        <div>
            <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Bendera Sarana Pengangkut</label>
            <input type="text" x-model="w.pengangkut.bendera_sarana_pengangkut" :disabled="isReadOnly" placeholder="Kode ISO 2 Huruf: ID, SG, PA, LR" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white uppercase font-mono font-bold">
        </div>

        <div>
            <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Perkiraan Tanggal Ekspor (ETD) <span class="text-rose-500">*</span></label>
            <input type="date" x-model="w.pengangkut.tanggal_ekspor" :disabled="isReadOnly" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono">
        </div>

        <div>
            <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Lokasi Kesiapan Barang / Stuffing</label>
            <input type="text" x-model="w.pengangkut.lokasi_siap_periksa" :disabled="isReadOnly" placeholder="Contoh: GUDANG PT AGUNG TRANS SOLUSINDO" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white uppercase">
        </div>
    </div>
</div>
