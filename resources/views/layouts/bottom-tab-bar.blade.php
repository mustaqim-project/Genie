<!-- ============================================================== -->
<!-- GENIE ERP - APPLE iOS BOTTOM TAB BAR (PWA Mobile)               -->
<!-- Active on Mobile Screens (lg:hidden) - Thumb Zone Ergonomics   -->
<!-- ============================================================== -->

<nav class="lg:hidden fixed bottom-0 inset-x-0 bg-white/90 dark:bg-[#1C1C1E]/90 backdrop-blur-2xl border-t border-black/[0.08] dark:border-white/[0.08] z-40 px-2 py-1 flex items-center justify-around select-none safe-bottom transition-colors"
     style="min-height: 56px;">
    
    <!-- 1. Dashboard -->
    <a href="{{ route('dashboard') }}" 
       class="flex flex-col items-center justify-center flex-1 min-h-[44px] py-1 text-center transition-transform active:scale-95 group relative {{ request()->routeIs('dashboard') ? 'text-[#007AFF] font-semibold' : 'text-black/45 dark:text-white/45 hover:text-black dark:hover:text-white font-medium' }}">
        @if(request()->routeIs('dashboard'))
            <span class="absolute -top-1 w-5 h-0.5 rounded-full bg-[#007AFF]"></span>
        @endif
        <i data-lucide="layout-grid" class="w-4 h-4 mb-0.5"></i>
        <span class="text-[10px] tracking-tight">Beranda</span>
    </a>

    <!-- 2. Dokumen Pabean (BC 2.0 / 3.0 / P01) -->
    <a href="{{ route('core.document.index') }}" 
       class="flex flex-col items-center justify-center flex-1 min-h-[44px] py-1 text-center transition-transform active:scale-95 group relative {{ request()->routeIs('core.document.*', 'bc20.*', 'bc30.*') ? 'text-[#007AFF] font-semibold' : 'text-black/45 dark:text-white/45 hover:text-black dark:hover:text-white font-medium' }}">
        @if(request()->routeIs('core.document.*', 'bc20.*', 'bc30.*'))
            <span class="absolute -top-1 w-5 h-0.5 rounded-full bg-[#007AFF]"></span>
        @endif
        <i data-lucide="file-text" class="w-4 h-4 mb-0.5"></i>
        <span class="text-[10px] tracking-tight">Dokumen</span>
    </a>

    <!-- 3. DO & Pelabuhan -->
    <a href="{{ route('core.do.index') }}" 
       class="flex flex-col items-center justify-center flex-1 min-h-[44px] py-1 text-center transition-transform active:scale-95 group relative {{ request()->routeIs('core.do.*', 'core.traffic.*', 'do.*', 'traffic.*') ? 'text-[#007AFF] font-semibold' : 'text-black/45 dark:text-white/45 hover:text-black dark:hover:text-white font-medium' }}">
        @if(request()->routeIs('core.do.*', 'core.traffic.*', 'do.*', 'traffic.*'))
            <span class="absolute -top-1 w-5 h-0.5 rounded-full bg-[#007AFF]"></span>
        @endif
        <i data-lucide="truck" class="w-4 h-4 mb-0.5"></i>
        <span class="text-[10px] tracking-tight">DO & Traffic</span>
    </a>

    <!-- 4. Finance & Kas -->
    <a href="{{ route('finance.invoices.index') }}" 
       class="flex flex-col items-center justify-center flex-1 min-h-[44px] py-1 text-center transition-transform active:scale-95 group relative {{ request()->routeIs('finance.*', 'accounting.*') ? 'text-[#007AFF] font-semibold' : 'text-black/45 dark:text-white/45 hover:text-black dark:hover:text-white font-medium' }}">
        @if(request()->routeIs('finance.*', 'accounting.*'))
            <span class="absolute -top-1 w-5 h-0.5 rounded-full bg-[#007AFF]"></span>
        @endif
        <i data-lucide="wallet" class="w-4 h-4 mb-0.5"></i>
        <span class="text-[10px] tracking-tight">Finance</span>
    </a>

    <!-- 5. Menu Drawer Trigger -->
    <button type="button" 
            @click="sidebarOpen = true"
            class="flex flex-col items-center justify-center flex-1 min-h-[44px] py-1 text-center text-black/45 dark:text-white/45 hover:text-black dark:hover:text-white transition-transform active:scale-95 cursor-pointer">
        <i data-lucide="menu" class="w-4 h-4 mb-0.5"></i>
        <span class="text-[10px] tracking-tight">Menu</span>
    </button>

</nav>
