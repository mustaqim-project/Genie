<div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
        <!-- Card 1: Importir -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-4 border-2 border-emerald-400/70 dark:border-emerald-700/60 shadow-xs space-y-3">
            <div class="flex items-center justify-between gap-2 pb-2 border-b border-black/[0.06] dark:border-white/[0.06]">
                <span class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-white/10 font-bold text-slate-800 dark:text-white">Importir</span>
                <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-300/60 font-semibold text-[10px]">
                    &check; Cocok
                </span>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Jenis <span class="text-rose-500">*</span></label>
                    <select x-model="w.entitas.importir_jenis_identitas" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white">
                        @foreach($refOptions['jenis_identitas'] as $code => $label)
                            <option value="{{ $code }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">NPWP Importir <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="w.entitas.importir_npwp" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Nama Importir <span class="text-rose-500">*</span></label>
                <input type="text" x-model="w.entitas.importir_nama" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white uppercase font-semibold">
            </div>

            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Status <span class="text-rose-500">*</span></label>
                <select x-model="w.entitas.importir_status" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white">
                    <option value="LAINNYA">LAINNYA</option>
                    <option value="AEO">AEO</option>
                    <option value="MITA">MITA PRIORITAS</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">API / NIB <span class="text-rose-500">*</span></label>
                    <select x-model="w.entitas.importir_api" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white">
                        <option value="01">API U</option>
                        <option value="02">API P</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">NIB <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="w.entitas.importir_nib" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Alamat Importir <span class="text-rose-500">*</span></label>
                <textarea x-model="w.entitas.importir_alamat" rows="3" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white text-[11px] leading-relaxed"></textarea>
            </div>
        </div>

        <!-- Card 2: Pemilik Barang -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-4 border-2 border-emerald-400/70 dark:border-emerald-700/60 shadow-xs space-y-3">
            <div class="flex items-center justify-between gap-2 pb-2 border-b border-black/[0.06] dark:border-white/[0.06]">
                <span class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-white/10 font-bold text-slate-800 dark:text-white">Pemilik Barang</span>
                <div class="flex items-center gap-1.5">
                    <span class="px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-300/60 font-semibold text-[10px]">&check; Cocok</span>
                    <button type="button" @click="copyImportirTo('pemilik')" class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-[#007AFF] hover:text-white dark:bg-white/10 text-slate-700 dark:text-slate-200 font-semibold text-[10px] transition-colors cursor-pointer">
                        Salin Importir
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Jenis <span class="text-rose-500">*</span></label>
                    <select x-model="w.entitas.pemilik_jenis_identitas" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white">
                        @foreach($refOptions['jenis_identitas'] as $code => $label)
                            <option value="{{ $code }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">NPWP Pemilik <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="w.entitas.pemilik_npwp" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Nama Pemilik Barang <span class="text-rose-500">*</span></label>
                <input type="text" x-model="w.entitas.pemilik_nama" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white uppercase font-semibold">
            </div>

            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Alamat Pemilik Barang <span class="text-rose-500">*</span></label>
                <textarea x-model="w.entitas.pemilik_alamat" rows="4" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white text-[11px] leading-relaxed"></textarea>
            </div>

            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Afiliasi <span class="text-rose-500">*</span></label>
                <select x-model="w.entitas.kode_afiliasi" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white">
                    <option value="TAH">TAH - Tidak berhubungan</option>
                    <option value="HU">HU - Berhubungan (Mempengaruhi Harga)</option>
                    <option value="TH">TH - Berhubungan (Tidak Mempengaruhi Harga)</option>
                </select>
            </div>
        </div>

        <!-- Card 3: Pemusatan -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-4 border-2 border-emerald-400/70 dark:border-emerald-700/60 shadow-xs space-y-3">
            <div class="flex items-center justify-between gap-2 pb-2 border-b border-black/[0.06] dark:border-white/[0.06]">
                <span class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-white/10 font-bold text-slate-800 dark:text-white">Pemusatan</span>
                <div class="flex items-center gap-1.5">
                    <span class="px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-300/60 font-semibold text-[10px]">&check; Cocok</span>
                    <button type="button" @click="copyImportirTo('pemusatan')" class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-[#007AFF] hover:text-white dark:bg-white/10 text-slate-700 dark:text-slate-200 font-semibold text-[10px] transition-colors cursor-pointer">
                        Salin Importir
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Jenis <span class="text-rose-500">*</span></label>
                    <select x-model="w.entitas.pemusatan_jenis_identitas" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white">
                        @foreach($refOptions['jenis_identitas'] as $code => $label)
                            <option value="{{ $code }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">NPWP Pemusatan <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="w.entitas.pemusatan_npwp" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Nama Pemusatan <span class="text-rose-500">*</span></label>
                <input type="text" x-model="w.entitas.pemusatan_nama" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white uppercase font-semibold">
            </div>

            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Alamat Pemusatan <span class="text-rose-500">*</span></label>
                <textarea x-model="w.entitas.pemusatan_alamat" rows="4" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white text-[11px] leading-relaxed"></textarea>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
        <!-- Card 4: Pengirim -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-4 border border-black/[0.08] dark:border-white/10 shadow-xs space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-black/[0.06] dark:border-white/[0.06]">
                <span class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-white/10 font-bold text-slate-800 dark:text-white">Pengirim</span>
                <span class="text-[10px] text-slate-400">Shipper / Supplier LN</span>
            </div>

            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Nama Pengirim <span class="text-rose-500">*</span></label>
                <input type="text" x-model="w.entitas.supplier_nama" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white uppercase font-semibold">
            </div>

            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Negara Pengirim <span class="text-rose-500">*</span></label>
                <select x-model="w.entitas.supplier_negara" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white">
                    @foreach($refOptions['negara'] as $code => $label)
                        <option value="{{ $code }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Alamat Pengirim <span class="text-rose-500">*</span></label>
                <textarea x-model="w.entitas.supplier_alamat" rows="3" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white text-[11px] leading-relaxed"></textarea>
            </div>
        </div>

        <!-- Card 5: Penjual -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-4 border border-black/[0.08] dark:border-white/10 shadow-xs space-y-3">
            <div class="flex items-center justify-between gap-2 pb-2 border-b border-black/[0.06] dark:border-white/[0.06]">
                <span class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-white/10 font-bold text-slate-800 dark:text-white">Penjual</span>
                <button type="button" @click="copyPengirimToPenjual()" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-[#007AFF] hover:text-white dark:bg-white/10 text-slate-700 dark:text-slate-200 font-semibold text-[10px] transition-colors cursor-pointer">
                    Salin Data Pengirim
                </button>
            </div>

            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Nama Penjual <span class="text-rose-500">*</span></label>
                <input type="text" x-model="w.entitas.seller_nama" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white uppercase font-semibold">
            </div>

            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Negara Penjual <span class="text-rose-500">*</span></label>
                <select x-model="w.entitas.seller_negara" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white">
                    @foreach($refOptions['negara'] as $code => $label)
                        <option value="{{ $code }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Alamat Penjual <span class="text-rose-500">*</span></label>
                <textarea x-model="w.entitas.seller_alamat" rows="3" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white text-[11px] leading-relaxed"></textarea>
            </div>
        </div>

        <!-- Card 6: PPJK -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-4 border-2 border-emerald-400/70 dark:border-emerald-700/60 shadow-xs space-y-3">
            <div class="flex items-center justify-between gap-2 pb-2 border-b border-black/[0.06] dark:border-white/[0.06]">
                <span class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-white/10 font-bold text-slate-800 dark:text-white">PPJK</span>
                <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-300/60 font-semibold text-[10px]">
                    &check; Dari akun Anda
                </span>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Jenis <span class="text-rose-500">*</span></label>
                    <select x-model="w.entitas.ppjk_jenis_identitas" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white">
                        @foreach($refOptions['jenis_identitas'] as $code => $label)
                            <option value="{{ $code }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">NPWP PPJK <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="w.entitas.ppjk_npwp" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Nama PPJK <span class="text-rose-500">*</span></label>
                <input type="text" x-model="w.entitas.ppjk_nama" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white uppercase font-semibold">
            </div>

            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Alamat PPJK <span class="text-rose-500">*</span></label>
                <textarea x-model="w.entitas.ppjk_alamat" rows="3" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white text-[11px] leading-relaxed"></textarea>
            </div>
        </div>
    </div>
</div>

