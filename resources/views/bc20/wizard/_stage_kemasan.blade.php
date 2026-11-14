<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 text-xs">
    <!-- Left Card: Kemasan -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-5 border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.06]">
            <div class="flex items-center gap-2">
                <h3 class="font-bold text-sm text-slate-900 dark:text-white">Kemasan</h3>
                <span class="w-2 h-2 rounded-full bg-rose-500" x-show="w.kemasan.length === 0"></span>
            </div>
            <button type="button" @click="addKemasan()"
                    class="px-3 py-1.5 rounded-xl border border-[#007AFF]/30 text-[#007AFF] hover:bg-[#007AFF]/10 font-semibold inline-flex items-center gap-1 cursor-pointer">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                <span>Tambah Kemasan</span>
            </button>
        </div>

        <div class="overflow-x-auto rounded-xl border border-black/[0.06] dark:border-white/[0.08]">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 dark:bg-white/[0.03] text-slate-500 dark:text-slate-400 font-semibold border-b border-black/[0.06] dark:border-white/[0.08]">
                    <tr>
                        <th class="px-3 py-2.5 w-12 text-center">Seri</th>
                        <th class="px-3 py-2.5 w-24">Jumlah</th>
                        <th class="px-3 py-2.5">Jenis</th>
                        <th class="px-3 py-2.5">Merek</th>
                        <th class="px-3 py-2.5 text-center w-24 sticky right-0 z-20 bg-slate-50 dark:bg-[#1C1C1E] border-l border-black/[0.06] dark:border-white/[0.08] shadow-[-6px_0_12px_rgba(0,0,0,0.06)]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.05] dark:divide-white/[0.06]">
                    <template x-for="(k, kIdx) in w.kemasan" :key="kIdx">
                        <tr>
                            <td class="px-3 py-2.5 text-center font-mono font-bold text-slate-700 dark:text-slate-200" x-text="kIdx + 1"></td>
                            <td class="px-3 py-2.5">
                                <input type="number" min="1" x-model.number="k.jumlahKemasan" class="w-20 px-2 py-1 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono tabular-nums">
                            </td>
                            <td class="px-3 py-2.5">
                                <select x-model="k.kodeJenisKemasan" class="w-full px-2 py-1 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white">
                                    @foreach($refOptions['kemasan'] as $code => $label)
                                        <option value="{{ $code }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-3 py-2.5">
                                <input type="text" x-model="k.merkKemasan" class="w-full px-2 py-1 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white">
                            </td>
                            <td class="px-3 py-2.5 text-center whitespace-nowrap sticky right-0 z-10 bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xs border-l border-black/[0.06] dark:border-white/[0.08] shadow-[-6px_0_12px_rgba(0,0,0,0.06)]">
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button" @click="duplicateKemasan(kIdx)" title="Duplikat" class="p-1.5 rounded-lg text-[#007AFF] hover:bg-[#007AFF]/10 cursor-pointer">
                                        <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <button type="button" @click="removeKemasan(kIdx)" x-show="w.kemasan.length > 1" title="Hapus" class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 cursor-pointer">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right Card: Peti Kemas -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-5 border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.06]">
            <div class="flex items-center gap-2">
                <h3 class="font-bold text-sm text-slate-900 dark:text-white">Peti Kemas</h3>
                <span class="px-2 py-0.5 rounded-md bg-[#007AFF]/10 text-[#007AFF] font-mono font-bold text-[10px]" x-text="'TOTAL: ' + w.kontainer.length"></span>
            </div>
            <button type="button" @click="addKontainer()"
                    class="px-3 py-1.5 rounded-xl border border-[#007AFF]/30 text-[#007AFF] hover:bg-[#007AFF]/10 font-semibold inline-flex items-center gap-1 cursor-pointer">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                <span>Tambah Peti Kemas</span>
            </button>
        </div>

        <div class="overflow-x-auto rounded-xl border border-black/[0.06] dark:border-white/[0.08]">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 dark:bg-white/[0.03] text-slate-500 dark:text-slate-400 font-semibold border-b border-black/[0.06] dark:border-white/[0.08]">
                    <tr>
                        <th class="px-3 py-2.5 w-10 text-center">Seri</th>
                        <th class="px-3 py-2.5">Nomor</th>
                        <th class="px-3 py-2.5 w-20">Ukuran</th>
                        <th class="px-3 py-2.5 w-24">Jenis</th>
                        <th class="px-3 py-2.5">Tipe</th>
                        <th class="px-3 py-2.5 text-center w-20 sticky right-0 z-20 bg-slate-50 dark:bg-[#1C1C1E] border-l border-black/[0.06] dark:border-white/[0.08] shadow-[-6px_0_12px_rgba(0,0,0,0.06)]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.05] dark:divide-white/[0.06]">
                    <template x-for="(c, cIdx) in w.kontainer" :key="cIdx">
                        <tr>
                            <td class="px-3 py-2.5 text-center font-mono font-bold text-slate-700 dark:text-slate-200" x-text="cIdx + 1"></td>
                            <td class="px-3 py-2.5">
                                <input type="text" x-model="c.nomorKontainer" class="w-full min-w-[110px] px-2 py-1 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono uppercase">
                            </td>
                            <td class="px-3 py-2.5">
                                <select x-model="c.kodeUkuranKontainer" class="w-full px-2 py-1 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono">
                                    @foreach($refOptions['ukuran_kontainer'] as $uk)
                                        <option value="{{ $uk }}">{{ $uk }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-3 py-2.5">
                                <select x-model="c.kodeJenisKontainer" class="w-full px-2 py-1 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white">
                                    @foreach($refOptions['jenis_kontainer'] as $code => $label)
                                        <option value="{{ $code }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-3 py-2.5">
                                <select x-model="c.kodeTipeKontainer" class="w-full px-2 py-1 rounded-lg border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white">
                                    @foreach($refOptions['tipe_kontainer'] as $code => $label)
                                        <option value="{{ $code }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-3 py-2.5 text-center whitespace-nowrap sticky right-0 z-10 bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xs border-l border-black/[0.06] dark:border-white/[0.08] shadow-[-6px_0_12px_rgba(0,0,0,0.06)]">
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button" @click="duplicateKontainer(cIdx)" title="Duplikat" class="p-1.5 rounded-lg text-[#007AFF] hover:bg-[#007AFF]/10 cursor-pointer">
                                        <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <button type="button" @click="removeKontainer(cIdx)" x-show="w.kontainer.length > 1" title="Hapus" class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 cursor-pointer">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>

