<!-- ============================================================== -->
<!-- GENIE ERP - APPLE DESIGN SYSTEM ENTERPRISE FOOTER               -->
<!-- Clean, Minimalist Administrative Footer                        -->
<!-- ============================================================== -->

<footer class="mt-auto bg-white/70 backdrop-blur-md border-t border-slate-200/70 text-xs text-slate-500 py-4 transition-all">
    <div class="w-full px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-3 text-[11px]">
        
        <!-- Left: Legal Copyright -->
        <div class="flex items-center space-x-2 text-center md:text-left">
            <span class="font-bold text-slate-900 tracking-tight">GENIE<span class="text-sky-600 font-extrabold ml-1">ERP</span></span>
            <span class="text-slate-300">&bull;</span>
            <span class="text-slate-600 font-medium">&copy; {{ date('Y') }} PT Agung Trans Solusindo.</span>
            <span class="hidden sm:inline text-slate-400">Hak Cipta Dilindungi Undang-Undang.</span>
        </div>

        <!-- Right: Gateway SLA, Versions & Quick Support Links -->
        <div class="flex flex-wrap items-center justify-center md:justify-end gap-x-3 gap-y-1 text-slate-500 font-medium">
            <span class="inline-flex items-center space-x-1.5 text-slate-700">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="font-semibold">H2H CEISA 4.0 DJBC</span>
            </span>
            <span class="text-slate-300 hidden sm:inline">&bull;</span>
            <span class="text-slate-400 font-mono">Build v2.4.0</span>
            <span class="text-slate-300 hidden sm:inline">&bull;</span>
            <a href="{{ route('config.index') }}" class="text-sky-600 hover:text-sky-700 hover:underline font-semibold transition-colors">
                Koneksi Gateway
            </a>
            <span class="text-slate-300 hidden sm:inline">&bull;</span>
            <span class="text-slate-400 font-mono">KPU Bea Cukai Tanjung Priok</span>
        </div>

    </div>
</footer>
