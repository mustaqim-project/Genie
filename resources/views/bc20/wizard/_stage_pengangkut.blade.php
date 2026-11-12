<div class="space-y-4">
    <div class="flex items-center justify-end">
        <button type="button" @click="smartSyncBc11()"
                class="px-3.5 py-2 rounded-xl bg-[#007AFF] hover:bg-[#0066D6] text-white text-xs font-semibold inline-flex items-center gap-1.5 shadow-xs cursor-pointer">
            <i data-lucide="wand-2" class="w-3.5 h-3.5"></i>
            <span>Smart Sync dengan BC 1.1</span>
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
        <!-- Card 1: BC 1.1 -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-4 border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-3">
            <span class="inline-block px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-white/10 font-bold text-slate-800 dark:text-white">BC 1.1</span>

            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Kode Tutup PU</label>
                <select x-model="w.pengangkut.kode_tutup_pu" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white">
                    <option value="11">11 - BC 1.1</option>
                    <option value="12">12 - BC 1.2</option>
                    <option value="14">14 - BC 1.4</option>
                </select>
            </div>
            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Nomor BC 1.1</label>
                <input type="text" x-model="w.pengangkut.nomor_bc11" placeholder="Nomor BC 1.1" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono">
            </div>
            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Tanggal Tutup PU</label>
                <input type="date" x-model="w.pengangkut.tanggal_bc11" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white">
            </div>
            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Nomor Pos</label>
                    <input type="text" x-model="w.pengangkut.pos_bc11" placeholder="0001" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono">
                </div>
                <div>
                    <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Subpos</label>
                    <input type="text" x-model="w.pengangkut.subpos_bc11" placeholder="0000" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono">
                </div>
                <div>
                    <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Subsubpos</label>
                    <input type="text" x-model="w.pengangkut.subsubpos_bc11" placeholder="0000" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono">
                </div>
            </div>
        </div>

        <!-- Card 2: Pengangkutan -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-4 border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-3">
            <span class="inline-block px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-white/10 font-bold text-slate-800 dark:text-white">Pengangkutan</span>

            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Cara Pengangkutan <span class="text-rose-500">*</span></label>
                <select x-model="w.pengangkut.cara_angkut" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white">
                    <option value="1">1 - LAUT</option>
                    <option value="2">2 - KERETA API</option>
                    <option value="3">3 - DARAT</option>
                    <option value="4">4 - UDARA</option>
                    <option value="5">5 - POS</option>
                    <option value="9">9 - LAINNYA</option>
                </select>
            </div>
            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Nama Sarana Pengangkut <span class="text-rose-500">*</span></label>
                <input type="text" x-model="w.pengangkut.nama_sarana_pengangkut" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white uppercase font-semibold">
            </div>
            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Nomor Voy/Flight/No. Pol <span class="text-rose-500">*</span></label>
                <input type="text" x-model="w.pengangkut.nomor_voyage" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono uppercase">
            </div>
            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Bendera <span class="text-rose-500">*</span></label>
                <select x-model="w.pengangkut.bendera_kapal" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white">
                    @foreach($refOptions['negara'] as $code => $label)
                        <option value="{{ $code }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Perkiraan Tanggal Tiba</label>
                <input type="date" x-model="w.pengangkut.tanggal_tiba" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white">
            </div>
        </div>

        <!-- Card 3: Pelabuhan & Tempat Penimbunan -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-4 border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-3">
            <span class="inline-block px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-white/10 font-bold text-slate-800 dark:text-white">Pelabuhan & Tempat Penimbunan</span>

            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Pelabuhan Muat <span class="text-rose-500">*</span></label>
                <select x-model="w.pengangkut.pelabuhan_muat" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white">
                    @foreach($refOptions['pelabuhan'] as $code => $label)
                        <option value="{{ $code }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Pelabuhan Transit</label>
                <select x-model="w.pengangkut.pelabuhan_transit" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white">
                    <option value="">-- Tanpa Transit --</option>
                    @foreach($refOptions['pelabuhan'] as $code => $label)
                        <option value="{{ $code }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Pelabuhan Tujuan <span class="text-rose-500">*</span></label>
                <select x-model="w.pengangkut.pelabuhan_bongkar" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white">
                    @foreach($refOptions['pelabuhan'] as $code => $label)
                        <option value="{{ $code }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Tempat Penimbunan (TPS)</label>
                <input type="text" x-model="w.pengangkut.kode_tps" placeholder="TPS1 / JICT / NPCT1" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono uppercase">
            </div>
            <div>
                <label class="block font-semibold text-slate-600 dark:text-slate-300 mb-1">Notice of Arrival (NOA)</label>
                <div class="flex items-center gap-2 p-2 rounded-xl border border-slate-200 dark:border-white/15 bg-slate-50 dark:bg-[#2C2C2E]">
                    <input type="file" accept=".pdf,.png,.jpg,.jpeg" @change="handleWizardDocUpload($event, 'noa')" class="text-[11px] w-full text-slate-600 dark:text-slate-300">
                    <button type="button" x-show="w.pengangkut.noa_document_id" @click="previewByDocId(w.pengangkut.noa_document_id)"
                            class="px-2 py-1 rounded-lg bg-[#007AFF]/10 text-[#007AFF] font-semibold text-[10px] shrink-0 cursor-pointer">
                        Preview NOA
                    </button>
                </div>
                <span class="text-[10px] text-slate-400">Ukuran file maksimum: 10MB</span>
            </div>
        </div>
    </div>
</div>

