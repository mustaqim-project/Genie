<div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-5 sm:p-6 border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-5 text-xs">
    <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.06]">
        <h3 class="font-bold text-base text-slate-900 dark:text-white">Pernyataan Eksportir</h3>
        <button type="button" @click="syncPernyataan()"
                :disabled="isReadOnly"
                class="px-3.5 py-1.5 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] hover:bg-slate-50 text-slate-700 dark:text-slate-200 font-semibold inline-flex items-center gap-1.5 cursor-pointer disabled:opacity-50">
            <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-[#0284c7]"></i>
            <span>Sync Pernyataan</span>
        </button>
    </div>

    <div class="p-4 sm:p-5 rounded-2xl bg-slate-50/80 dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/10 text-slate-700 dark:text-slate-200 space-y-2 leading-relaxed">
        <p class="font-semibold">Dengan ini saya menyatakan:</p>
        <ol class="list-decimal list-inside space-y-1.5">
            <li>bertanggung jawab atas kebenaran hal-hal yang diberitahukan dalam dokumen Pemberitahuan Ekspor Barang (PEB / BC 3.0) ini dan keabsahan dokumen pelengkap pabean yang menjadi dasar pembuatan dokumen ini; dan</li>
            <li>sanggup menyiapkan barang untuk dilakukan pemeriksaan fisik dan/atau pemindaian peti kemas oleh Pejabat Bea dan Cukai di Tempat Penimbunan Sementara (TPS) atau lokasi stuffing yang ditunjuk.</li>
        </ol>
    </div>

    <div class="p-4 sm:p-5 rounded-2xl border border-black/[0.06] dark:border-white/10 space-y-4">
        <h4 class="font-bold text-sm text-slate-900 dark:text-white">Informasi Penandatanganan</h4>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Nama</label>
                <input type="text" x-model="w.pernyataan.nama_ttd" :disabled="isReadOnly" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white uppercase font-semibold">
            </div>
            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Tanggal</label>
                <input type="date" x-model="w.pernyataan.tanggal_ttd" :disabled="isReadOnly" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white">
            </div>
            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Tempat</label>
                <input type="text" x-model="w.pernyataan.kota_ttd" :disabled="isReadOnly" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white uppercase font-semibold">
            </div>
            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Jabatan</label>
                <input type="text" x-model="w.pernyataan.jabatan_ttd" :disabled="isReadOnly" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white uppercase font-semibold">
            </div>
        </div>
    </div>
</div>
