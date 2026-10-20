<!-- ============================================================== -->
<!-- GENIE ERP - APPLE HIG MINIMALIST ADMINISTRATIVE FOOTER          -->
<!-- Focused on utility, functional status, and clean typography    -->
<!-- ============================================================== -->

<footer class="mt-auto bg-white/60 dark:bg-[#1C1C1E]/60 backdrop-blur-md border-t border-black/[0.06] dark:border-white/[0.08] text-[11px] text-black/45 dark:text-white/45 py-2.5 transition-all">
    <div class="px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2">
        
        <!-- Left: Legal Identity & Product Name -->
        <div class="flex items-center space-x-1.5 text-center sm:text-left">
            <span class="font-semibold text-black/75 dark:text-white/75 tracking-tight">GENIE ERP</span>
            <span class="opacity-30">&bull;</span>
            <span>&copy; {{ date('Y') }} PT Agung Trans Solusindo</span>
        </div>

        <!-- Right: Functional Operational Status & Build -->
        <div class="flex items-center space-x-3 text-black/50 dark:text-white/50">
            <a href="{{ route('config.index') }}" 
               class="inline-flex items-center space-x-1.5 hover:text-[#007AFF] transition-colors"
               title="Periksa Koneksi Gateway Pabean">
                <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                <span class="font-medium">Gateway CEISA 4.0</span>
            </a>
            <span class="opacity-30">&bull;</span>
            <span class="font-mono tabular-nums text-black/40 dark:text-white/40">v2.4.0</span>
        </div>

    </div>
</footer>
