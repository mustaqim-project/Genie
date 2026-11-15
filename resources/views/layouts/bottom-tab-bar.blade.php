<!-- ============================================================== -->
<!-- GENIE ERP - APPLE iOS BOTTOM TAB BAR (PWA Mobile)               -->
<!-- Active on Mobile Screens (lg:hidden) - Thumb Zone Ergonomics   -->
<!-- ============================================================== -->

<nav class="lg:hidden fixed bottom-0 inset-x-0 bg-white/90 dark:bg-[#1C1C1E]/90 backdrop-blur-2xl border-t border-black/[0.08] dark:border-white/[0.08] z-40 px-2 py-1 flex items-center justify-around select-none safe-bottom transition-colors"
     style="min-height: 56px;">
    
    <!-- 1. Dashboard -->
    <a href="{{ route('dashboard') }}" 
       class="flex flex-col items-center justify-center flex-1 min-h-[44px] py-1 text-center transition-transform active:scale-95 group relative {{ request()->routeIs('dashboard') ? 'text-[#0284c7] font-semibold' : 'text-black/45 dark:text-white/45 hover:text-black dark:hover:text-white font-medium' }}">
        @if(request()->routeIs('dashboard'))
            <span class="absolute -top-1 w-5 h-0.5 rounded-full bg-[#0284c7]"></span>
        @endif
        <i data-lucide="layout-grid" class="w-4 h-4 mb-0.5"></i>
        <span class="text-[10px] tracking-tight">Beranda</span>
    </a>

    <!-- 2. Dokumen Pabean (BC 2.0 / 3.0 / P01) -->
    <a href="{{ route('core.document.index') }}" 
       class="flex flex-col items-center justify-center flex-1 min-h-[44px] py-1 text-center transition-transform active:scale-95 group relative {{ request()->routeIs('core.document.*', 'bc20.*', 'bc30.*') ? 'text-[#0284c7] font-semibold' : 'text-black/45 dark:text-white/45 hover:text-black dark:hover:text-white font-medium' }}">
        @if(request()->routeIs('core.document.*', 'bc20.*', 'bc30.*'))
            <span class="absolute -top-1 w-5 h-0.5 rounded-full bg-[#0284c7]"></span>
        @endif
        <i data-lucide="file-text" class="w-4 h-4 mb-0.5"></i>
        <span class="text-[10px] tracking-tight">Dokumen</span>
    </a>

    <!-- 3. DO & Pelabuhan -->
    <a href="{{ route('core.do.index') }}" 
       class="flex flex-col items-center justify-center flex-1 min-h-[44px] py-1 text-center transition-transform active:scale-95 group relative {{ request()->routeIs('core.do.*', 'core.traffic.*', 'do.*', 'traffic.*') ? 'text-[#0284c7] font-semibold' : 'text-black/45 dark:text-white/45 hover:text-black dark:hover:text-white font-medium' }}">
        @if(request()->routeIs('core.do.*', 'core.traffic.*', 'do.*', 'traffic.*'))
            <span class="absolute -top-1 w-5 h-0.5 rounded-full bg-[#0284c7]"></span>
        @endif
        <i data-lucide="truck" class="w-4 h-4 mb-0.5"></i>
        <span class="text-[10px] tracking-tight">DO & Traffic</span>
    </a>

    <!-- 4. Finance & Kas -->
    <a href="{{ route('finance.invoices.index') }}" 
       class="flex flex-col items-center justify-center flex-1 min-h-[44px] py-1 text-center transition-transform active:scale-95 group relative {{ request()->routeIs('finance.*', 'accounting.*') ? 'text-[#0284c7] font-semibold' : 'text-black/45 dark:text-white/45 hover:text-black dark:hover:text-white font-medium' }}">
        @if(request()->routeIs('finance.*', 'accounting.*'))
            <span class="absolute -top-1 w-5 h-0.5 rounded-full bg-[#0284c7]"></span>
        @endif
        <i data-lucide="wallet" class="w-4 h-4 mb-0.5"></i>
        <span class="text-[10px] tracking-tight">Finance</span>
    </a>

    <!-- 5. Native iOS Bottom Sheet Menu Trigger (FIX-27) -->
    <button type="button" 
            @click="mobileMenuOpen = true"
            class="flex flex-col items-center justify-center flex-1 min-h-[44px] py-1 text-center text-black/45 dark:text-white/45 hover:text-black dark:hover:text-white transition-transform active:scale-95 cursor-pointer">
        <i data-lucide="grid" class="w-4 h-4 mb-0.5"></i>
        <span class="text-[10px] tracking-tight">Modul</span>
    </button>
</nav>

<!-- iOS Native Bottom Sheet Menu Modal (FIX-27) -->
<div x-show="mobileMenuOpen"
     x-cloak
     class="fixed inset-0 z-50 lg:hidden flex flex-col justify-end"
     @keydown.escape.window="mobileMenuOpen = false">
    <!-- Backdrop -->
    <div x-show="mobileMenuOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="mobileMenuOpen = false"
         class="fixed inset-0 bg-black/40 backdrop-blur-xs"></div>

    <!-- Sheet Card Container -->
    <div x-show="mobileMenuOpen"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="translate-y-full"
         x-transition:enter-end="translate-y-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="translate-y-0"
         x-transition:leave-end="translate-y-full"
         class="relative bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl rounded-t-[28px] border-t border-black/10 dark:border-white/10 shadow-2xl p-5 max-h-[80vh] overflow-y-auto space-y-4">
        
        <!-- Grab Handle -->
        <div class="w-10 h-1 bg-black/20 dark:bg-white/20 rounded-full mx-auto mb-2"></div>

        <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
            <div class="text-sm font-bold text-black/90 dark:text-white/90">Direktori Modul Genie ERP</div>
            <button @click="mobileMenuOpen = false" class="p-1 rounded-full text-black/40 hover:text-black/70">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Quick Grid Modules -->
        <div class="grid grid-cols-4 gap-3 text-center">
            <a href="{{ route('crm.index') }}" class="flex flex-col items-center gap-1.5 p-2.5 rounded-2xl bg-black/5 dark:bg-white/5 hover:bg-sky-50 dark:hover:bg-sky-950/40 transition">
                <i data-lucide="users" class="w-5 h-5 text-sky-600 dark:text-sky-400"></i>
                <span class="text-[11px] font-medium">CRM</span>
            </a>
            <a href="{{ route('hrm.index') }}" class="flex flex-col items-center gap-1.5 p-2.5 rounded-2xl bg-black/5 dark:bg-white/5 hover:bg-sky-50 dark:hover:bg-sky-950/40 transition">
                <i data-lucide="user-check" class="w-5 h-5 text-sky-600 dark:text-sky-400"></i>
                <span class="text-[11px] font-medium">HRM</span>
            </a>
            <a href="{{ route('vendors.index') }}" class="flex flex-col items-center gap-1.5 p-2.5 rounded-2xl bg-black/5 dark:bg-white/5 hover:bg-sky-50 dark:hover:bg-sky-950/40 transition">
                <i data-lucide="anchor" class="w-5 h-5 text-sky-600 dark:text-sky-400"></i>
                <span class="text-[11px] font-medium">Vendor</span>
            </a>
            <a href="{{ route('finance.cash-bank.index') }}" class="flex flex-col items-center gap-1.5 p-2.5 rounded-2xl bg-black/5 dark:bg-white/5 hover:bg-sky-50 dark:hover:bg-sky-950/40 transition">
                <i data-lucide="landmark" class="w-5 h-5 text-sky-600 dark:text-sky-400"></i>
                <span class="text-[11px] font-medium">Bank/Kas</span>
            </a>
            <a href="{{ route('whatsapp.index') }}" class="flex flex-col items-center gap-1.5 p-2.5 rounded-2xl bg-black/5 dark:bg-white/5 hover:bg-sky-50 dark:hover:bg-sky-950/40 transition">
                <i data-lucide="message-circle" class="w-5 h-5 text-emerald-600 dark:text-emerald-400"></i>
                <span class="text-[11px] font-medium">WhatsApp</span>
            </a>
            <a href="{{ route('social-media.index') }}" class="flex flex-col items-center gap-1.5 p-2.5 rounded-2xl bg-black/5 dark:bg-white/5 hover:bg-sky-50 dark:hover:bg-sky-950/40 transition">
                <i data-lucide="share-2" class="w-5 h-5 text-purple-600 dark:text-purple-400"></i>
                <span class="text-[11px] font-medium">Sosmed</span>
            </a>
            <a href="{{ route('todos.index') }}" class="flex flex-col items-center gap-1.5 p-2.5 rounded-2xl bg-black/5 dark:bg-white/5 hover:bg-sky-50 dark:hover:bg-sky-950/40 transition">
                <i data-lucide="check-square" class="w-5 h-5 text-amber-600 dark:text-amber-400"></i>
                <span class="text-[11px] font-medium">To-Do</span>
            </a>
            <a href="{{ route('settings.roles.index') }}" class="flex flex-col items-center gap-1.5 p-2.5 rounded-2xl bg-black/5 dark:bg-white/5 hover:bg-sky-50 dark:hover:bg-sky-950/40 transition">
                <i data-lucide="shield" class="w-5 h-5 text-slate-600 dark:text-slate-400"></i>
                <span class="text-[11px] font-medium">Akses</span>
            </a>
        </div>

        <!-- Profile & Logout Link -->
        <div class="pt-3 border-t border-black/5 dark:border-white/10 flex items-center justify-between">
            <a href="{{ route('profile') }}" class="flex items-center gap-2 text-xs font-semibold text-black/80 dark:text-white/80">
                <i data-lucide="user" class="w-4 h-4 text-sky-600"></i>
                <span>Profil Pengguna</span>
            </a>
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="text-xs font-semibold text-rose-600 hover:underline">
                    Keluar Sistem
                </button>
            </form>
        </div>
    </div>
</div>
