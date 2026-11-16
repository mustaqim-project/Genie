<div class="space-y-4 text-xs">
    <!-- Card 1: Eksportir -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-5 border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.06]">
            <div>
                <h3 class="font-bold text-sm text-slate-900 dark:text-white">Eksportir (Perusahaan Lokal)</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Identitas entitas eksportir pabean di Indonesia.</p>
            </div>
            <span class="px-2 py-0.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 font-bold font-mono text-[10px]">
                ENTITAS 1 (EKSPORTIR)
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Jenis Identitas</label>
                <select x-model="w.entitas.eksportir_jenis_identitas" :disabled="isReadOnly" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-semibold">
                    <option value="6">NPWP 16 Digit</option>
                    <option value="5">NPWP 15 Digit</option>
                    <option value="0">KTP / NIK</option>
                    <option value="1">Paspor</option>
                </select>
            </div>

            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Nomor NPWP / NIK <span class="text-rose-500">*</span></label>
                <input type="text" x-model="w.entitas.eksportir_npwp" :disabled="isReadOnly" placeholder="16 Digit NPWP" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono font-bold">
            </div>

            <div class="sm:col-span-2">
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Nama Eksportir <span class="text-rose-500">*</span></label>
                <input type="text" x-model="w.entitas.eksportir_nama" :disabled="isReadOnly" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-semibold uppercase">
            </div>

            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Status Eksportir</label>
                <select x-model="w.entitas.eksportir_status" :disabled="isReadOnly" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white">
                    <option value="PRODUSEN">PRODUSEN</option>
                    <option value="NON_PRODUSEN">NON PRODUSEN / TRADER</option>
                    <option value="LAINNYA">LAINNYA</option>
                </select>
            </div>

            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">NIB</label>
                <input type="text" x-model="w.entitas.eksportir_nib" :disabled="isReadOnly" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono">
            </div>

            <div class="sm:col-span-2">
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Alamat Eksportir</label>
                <input type="text" x-model="w.entitas.eksportir_alamat" :disabled="isReadOnly" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white">
            </div>
        </div>
    </div>

    <!-- Card 2: Pemilik Barang -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-5 border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.06]">
            <div>
                <h3 class="font-bold text-sm text-slate-900 dark:text-white">Pemilik Barang</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Pihak pemilik barang komoditas ekspor.</p>
            </div>
            <button type="button" @click="copyEksportirToPemilik()"
                    :disabled="isReadOnly"
                    class="px-2.5 py-1 rounded-lg border border-slate-200 dark:border-white/15 bg-slate-50 dark:bg-white/5 hover:bg-slate-100 text-slate-700 dark:text-slate-300 font-semibold text-[10px] cursor-pointer">
                Sama dengan Eksportir
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">NPWP Pemilik Barang</label>
                <input type="text" x-model="w.entitas.pemilik_npwp" :disabled="isReadOnly" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono">
            </div>
            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Nama Pemilik Barang</label>
                <input type="text" x-model="w.entitas.pemilik_nama" :disabled="isReadOnly" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white uppercase font-semibold">
            </div>
            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Alamat Pemilik Barang</label>
                <input type="text" x-model="w.entitas.pemilik_alamat" :disabled="isReadOnly" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white">
            </div>
        </div>
    </div>

    <!-- Card 3: Pembeli / Penerima Luar Negeri -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-5 border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.06]">
            <div>
                <h3 class="font-bold text-sm text-slate-900 dark:text-white">Pembeli / Penerima Luar Negeri (Buyer/Consignee)</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Mitra dagang internasional penerima kargo ekspor.</p>
            </div>
            <span class="px-2 py-0.5 rounded-lg bg-sky-50 dark:bg-sky-950/40 text-[#0284c7] font-bold font-mono text-[10px]">
                BUYER / CONSIGNEE
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Nama Pembeli <span class="text-rose-500">*</span></label>
                <input type="text" x-model="w.entitas.buyer_nama" :disabled="isReadOnly" placeholder="Contoh: PACIFIC DISTRIBUTORS INC" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white uppercase font-semibold">
            </div>
            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Negara Pembeli <span class="text-rose-500">*</span></label>
                <input type="text" x-model="w.entitas.buyer_negara" :disabled="isReadOnly" placeholder="Kode ISO 2 Huruf: US, CN, SG, JP" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white uppercase font-mono font-bold">
            </div>
            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Alamat Pembeli</label>
                <input type="text" x-model="w.entitas.buyer_alamat" :disabled="isReadOnly" placeholder="Alamat lengkap di negara tujuan" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white">
            </div>
        </div>
    </div>

    <!-- Card 4: PPJK (Opsional) -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-5 border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.06]">
            <div>
                <h3 class="font-bold text-sm text-slate-900 dark:text-white">PPJK (Pengusaha Pengurusan Jasa Kepabeanan)</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Diisi jika pengurusan PEB dikuasakan ke perusahaan PPJK.</p>
            </div>
            <span class="text-[10px] font-mono text-slate-400">OPSIONAL</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">NPWP PPJK</label>
                <input type="text" x-model="w.entitas.ppjk_npwp" :disabled="isReadOnly" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono">
            </div>
            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Nama PPJK</label>
                <input type="text" x-model="w.entitas.ppjk_nama" :disabled="isReadOnly" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white uppercase font-semibold">
            </div>
            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Alamat PPJK</label>
                <input type="text" x-model="w.entitas.ppjk_alamat" :disabled="isReadOnly" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white">
            </div>
        </div>
    </div>
</div>
