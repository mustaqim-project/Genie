<!-- ============================================================== -->
<!-- PATS_CEISA ERP - APPLE iOS BOTTOM TAB BAR (PWA Mobile)         -->
<!-- Active on Mobile Screens (lg:hidden) - Thumb Zone Ergonomics   -->
<!-- ============================================================== -->

<nav class="lg:hidden fixed bottom-0 inset-x-0 bg-white/90 backdrop-blur-2xl border-t border-slate-200/80 z-40 px-2 py-1 flex items-center justify-around shadow-[0_-4px_20px_rgba(0,0,0,0.04)] select-none safe-bottom"
     style="min-height: 60px;">
    
    <!-- 1. Dashboard -->
    <a href="{{ route('dashboard') }}" 
       class="flex flex-col items-center justify-center flex-1 min-h-[48px] py-1 text-center transition-transform active:scale-95 group relative {{ request()->routeIs('dashboard') ? 'text-sky-600 font-bold' : 'text-slate-400 hover:text-slate-600 font-medium' }}">
        @if(request()->routeIs('dashboard'))
            <span class="absolute -top-1 w-6 h-0.5 rounded-full bg-sky-600"></span>
        @endif
        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
        </svg>
        <span class="text-[10px] tracking-tight">Beranda</span>
    </a>

    <!-- 2. Dokumen Pabean (BC 2.0 / 3.0 / P01) -->
    <a href="{{ route('core.document.index') }}" 
       class="flex flex-col items-center justify-center flex-1 min-h-[48px] py-1 text-center transition-transform active:scale-95 group relative {{ request()->routeIs('core.document.*', 'bc20.*', 'bc30.*') ? 'text-sky-600 font-bold' : 'text-slate-400 hover:text-slate-600 font-medium' }}">
        @if(request()->routeIs('core.document.*', 'bc20.*', 'bc30.*'))
            <span class="absolute -top-1 w-6 h-0.5 rounded-full bg-sky-600"></span>
        @endif
        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <span class="text-[10px] tracking-tight">Dokumen</span>
    </a>

    <!-- 3. DO & Pelabuhan -->
    <a href="{{ route('core.do.index') }}" 
       class="flex flex-col items-center justify-center flex-1 min-h-[48px] py-1 text-center transition-transform active:scale-95 group relative {{ request()->routeIs('core.do.*', 'core.traffic.*', 'do.*', 'traffic.*') ? 'text-sky-600 font-bold' : 'text-slate-400 hover:text-slate-600 font-medium' }}">
        @if(request()->routeIs('core.do.*', 'core.traffic.*', 'do.*', 'traffic.*'))
            <span class="absolute -top-1 w-6 h-0.5 rounded-full bg-sky-600"></span>
        @endif
        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8m-5 5h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293h3.172a1 1 0 00.707-.293l2.414-2.414a1 1 0 01.707-.293H20" />
        </svg>
        <span class="text-[10px] tracking-tight">DO & Lapangan</span>
    </a>

    <!-- 4. Finance & Kas -->
    <a href="{{ route('finance.invoices.index') }}" 
       class="flex flex-col items-center justify-center flex-1 min-h-[48px] py-1 text-center transition-transform active:scale-95 group relative {{ request()->routeIs('finance.*', 'accounting.*') ? 'text-sky-600 font-bold' : 'text-slate-400 hover:text-slate-600 font-medium' }}">
        @if(request()->routeIs('finance.*', 'accounting.*'))
            <span class="absolute -top-1 w-6 h-0.5 rounded-full bg-sky-600"></span>
        @endif
        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="text-[10px] tracking-tight">Finance</span>
    </a>

    <!-- 5. Menu Drawer -->
    <button type="button" 
            @click="sidebarOpen = true"
            class="flex flex-col items-center justify-center flex-1 min-h-[48px] py-1 text-center text-slate-400 hover:text-slate-700 transition-transform active:scale-95">
        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
        <span class="text-[10px] tracking-tight">Menu</span>
    </button>

</nav>
