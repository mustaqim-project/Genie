<div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-5 sm:p-6 border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-5">
    <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.06]">
        <div>
            <h2 class="text-sm font-bold text-slate-900 dark:text-white">1. Header Dokumen Pabean (PEB / BC 3.0)</h2>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Tentukan pelabuhan muat ekspor, kantor pabean pengawas, jenis ekspor, kategori, dan cara pembayaran.</p>
        </div>
        <span class="px-2.5 py-1 rounded-lg bg-[#0284c7]/10 text-[#0284c7] text-[10px] font-bold font-mono">TAHAP 1 / 9</span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
        <div>
            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                Pelabuhan Muat Asal <span class="text-rose-500">*</span>
            </label>
            <select x-model="w.header.pelabuhan_muat"
                    :disabled="isReadOnly"
                    class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white focus:ring-2 focus:ring-[#0284c7]">
                @foreach($refOptions['pelabuhan'] as $code => $label)
                    <option value="{{ $code }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                Pelabuhan Muat Ekspor (Singgah/Akhir) <span class="text-rose-500">*</span>
            </label>
            <select x-model="w.header.pelabuhan_ekspor"
                    :disabled="isReadOnly"
                    class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white focus:ring-2 focus:ring-[#0284c7]">
                @foreach($refOptions['pelabuhan'] as $code => $label)
                    <option value="{{ $code }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                Pelabuhan Bongkar LN <span class="text-rose-500">*</span>
            </label>
            <input type="text" x-model="w.header.pelabuhan_bongkar"
                   :disabled="isReadOnly"
                   placeholder="Contoh: USLAX (Los Angeles)"
                   class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white font-mono uppercase focus:ring-2 focus:ring-[#0284c7]">
        </div>

        <div>
            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                Kantor Pabean Pemuatan <span class="text-rose-500">*</span>
            </label>
            <select x-model="w.header.kode_kantor"
                    :disabled="isReadOnly"
                    class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white focus:ring-2 focus:ring-[#0284c7]">
                @foreach($kantorList as $code => $name)
                    <option value="{{ $code }}">{{ $code }} - {{ strtoupper($name) }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                Jenis Ekspor <span class="text-rose-500">*</span>
            </label>
            <select x-model="w.header.kode_jenis_ekspor"
                    :disabled="isReadOnly"
                    class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white focus:ring-2 focus:ring-[#0284c7]">
                @foreach($refOptions['jenis_ekspor'] as $code => $label)
                    <option value="{{ $code }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                Kategori Ekspor <span class="text-rose-500">*</span>
            </label>
            <select x-model="w.header.kode_kategori_ekspor"
                    :disabled="isReadOnly"
                    class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white focus:ring-2 focus:ring-[#0284c7]">
                @foreach($refOptions['kategori_ekspor'] as $code => $label)
                    <option value="{{ $code }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                Cara Dagang <span class="text-rose-500">*</span>
            </label>
            <select x-model="w.header.kode_cara_dagang"
                    :disabled="isReadOnly"
                    class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white focus:ring-2 focus:ring-[#0284c7]">
                @foreach($refOptions['cara_dagang'] as $code => $label)
                    <option value="{{ $code }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                Cara Pembayaran <span class="text-rose-500">*</span>
            </label>
            <select x-model="w.header.kode_cara_bayar"
                    :disabled="isReadOnly"
                    class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white focus:ring-2 focus:ring-[#0284c7]">
                @foreach($refOptions['cara_bayar'] as $code => $label)
                    <option value="{{ $code }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
