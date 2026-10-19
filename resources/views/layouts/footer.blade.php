<!-- ============================================================== -->
<!-- PATS_CEISA ERP - APPLE DESIGN SYSTEM FOOTER                    -->
<!-- Clean, Minimalist Administrative Footer                        -->
<!-- ============================================================== -->

<footer class="mt-auto bg-white/60 backdrop-blur-md border-t border-slate-200/60 text-xs text-slate-500 py-4 transition-all">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-3 text-[11px]">
        
        <!-- Left: Legal Copyright -->
        <div class="flex items-center space-x-2 text-center md:text-left">
            <span class="font-bold text-slate-800 tracking-tight">PATS<span class="text-sky-600">_CEISA</span></span>
            <span class="text-slate-300">&bull;</span>
            <span class="text-slate-600">&copy; {{ date('Y') }} PT Agung Trans Solusindo.</span>
            <span class="hidden sm:inline text-slate-400">Hak Cipta Dilindungi.</span>
        </div>

        <!-- Right: Gateway SLA, Versions & Quick Support Links -->
        <div class="flex flex-wrap items-center justify-center md:justify-end gap-x-3 gap-y-1 text-slate-500">
            <span class="inline-flex items-center space-x-1.5 font-medium text-slate-700">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                <span>H2H CEISA 4.0 DJBC</span>
            </span>
            <span class="text-slate-300 hidden sm:inline">&bull;</span>
            <span class="text-slate-400 font-mono">v4.0.2</span>
            <span class="text-slate-300 hidden sm:inline">&bull;</span>
            <a href="{{ route('config.index') }}" class="text-sky-600 hover:text-sky-700 hover:underline font-semibold transition-colors">
                Koneksi Gateway
            </a>
            <span class="text-slate-300 hidden sm:inline">&bull;</span>
            <span class="text-slate-400">Tanjung Priok &bull; Soekarno-Hatta</span>
        </div>

    </div>
</footer>
