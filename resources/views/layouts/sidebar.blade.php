<!-- ============================================================== -->
<!-- GENIE ERP - APPLE macOS / iPadOS NAVIGATION SIDEBAR            -->
<!-- Diadopsi secara lengkap dari Cooca Core (Apple HIG Standards)   -->
<!-- Grouped Sections, Hierarchical Menus & Submenus, Dark/Light    -->
<!-- ============================================================== -->

<!-- Mobile Backdrop Blur Sheet -->
<div x-show="sidebarOpen" 
     x-cloak
     x-transition:enter="transition-opacity ease-out duration-250"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 lg:hidden"
     @click="sidebarOpen = false"></div>

<!-- Sidebar Container -->
<aside :class="{
           'translate-x-0': sidebarOpen,
           '-translate-x-full lg:translate-x-0': !sidebarOpen,
           'lg:w-64': !sidebarCollapsed,
           'lg:w-[72px] is-collapsed': sidebarCollapsed
       }"
       class="app-sidebar fixed inset-y-0 left-0 z-40 flex flex-col bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border-r border-black/[0.08] dark:border-white/[0.08] text-black/80 dark:text-white/80 transition-all duration-250 ease-out h-screen max-h-screen w-64 max-w-[85vw] flex-shrink-0 shadow-xl lg:shadow-none select-none">

    <!-- 1. APPLE macOS STYLE HEADER & BRAND LOGO -->
    <div class="h-14 flex items-center justify-between px-3.5 border-b border-black/[0.06] dark:border-white/[0.08] bg-white/40 dark:bg-white/[0.02] shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-2.5 overflow-hidden group min-w-0">
            <div class="w-8 h-8 rounded-[9px] bg-gradient-to-tr from-[#007AFF] to-[#5856D6] flex items-center justify-center text-white font-bold shadow-xs flex-shrink-0">
                <i data-lucide="ship" class="w-4 h-4 text-white"></i>
            </div>
            <div x-show="!sidebarCollapsed" class="transition-opacity duration-200 min-w-0">
                <div class="flex items-center space-x-1.5 leading-none">
                    <span class="font-bold text-[14px] tracking-tight text-black dark:text-white">GENIE</span>
                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-[5px] bg-[#007AFF]/10 text-[#007AFF] font-mono leading-none">ERP</span>
                </div>
                <div class="text-[10px] text-black/40 dark:text-white/40 font-medium truncate mt-0.5">Agung Trans Solusindo</div>
            </div>
        </a>

        <!-- Desktop Collapse Button (macOS sidebar toggle) -->
        <button @click="toggleSidebarCollapse()" 
                type="button"
                class="hidden lg:flex p-1.5 rounded-[7px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white hover:bg-black/[0.05] dark:hover:bg-white/[0.08] transition-colors cursor-pointer"
                title="Sembunyikan / Buka Bilah Samping">
            <i data-lucide="panel-left-close" class="w-4 h-4 transition-transform duration-250" :class="{ 'rotate-180': sidebarCollapsed }"></i>
        </button>

        <!-- Mobile Close Button (44px target) -->
        <button @click="sidebarOpen = false" 
                type="button"
                class="lg:hidden min-w-[40px] min-h-[40px] flex items-center justify-center rounded-[8px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white hover:bg-black/[0.05] dark:hover:bg-white/[0.08] transition-colors">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>

    <!-- 2. MAIN NAVIGATION (APPLE HIG STYLE GROUPED LIST WITH SUBMENUS) -->
    <nav class="flex-1 px-2 py-2.5 space-y-3 overflow-y-auto custom-scrollbar text-[13px] pb-24 lg:pb-4">

        <!-- ============================================================== -->
        <!-- GRUP 1: UTAMA                                                 -->
        <!-- ============================================================== -->
        <div>
            <div x-show="!sidebarCollapsed" class="px-2.5 mb-1.5 text-[10px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500">
                Utama
            </div>
            @if(!auth()->check() || auth()->user()->hasPermission('dashboard.view'))
            <a href="{{ route('dashboard') }}" 
               class="flex items-center space-x-2.5 px-2.5 py-1.5 rounded-[9px] font-medium transition-all group min-h-[36px] active:scale-[0.98] {{ request()->routeIs('dashboard') ? 'bg-[#007AFF] text-white font-semibold shadow-[0_2px_8px_rgba(0,122,255,0.30)]' : 'text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.06]' }}"
               title="Dashboard & KPI Operasional">
                <i data-lucide="layout-dashboard" class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-900 dark:group-hover:text-white' }}"></i>
                <span x-show="!sidebarCollapsed" class="truncate">Dashboard</span>
            </a>
            @endif
        </div>

        <!-- ============================================================== -->
        <!-- GRUP 2: OPERASIONAL & KEPABEANAN                             -->
        <!-- ============================================================== -->
        <div>
            <div x-show="!sidebarCollapsed" class="px-2.5 mb-1.5 text-[10px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500">
                Operasional & Pabean
            </div>

            <!-- MENU 1: PROYEK PENGAPALAN & DOKUMEN PABEAN (ACCORDION SUBMENU) -->
            @if(!auth()->check() || auth()->user()->hasPermission('document.view'))
            @php $isDocActive = request()->routeIs('core.document.*', 'bc20.*', 'bc30.*'); @endphp
            <div x-data="{ open: {{ $isDocActive ? 'true' : 'false' }} }" class="space-y-0.5">
                <button type="button" 
                        @click="if (sidebarCollapsed) { window.location.href = '{{ route('core.document.index') }}'; } else { open = !open; }"
                        class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-[9px] font-medium transition-all group min-h-[36px] cursor-pointer active:scale-[0.98] {{ $isDocActive ? 'bg-black/[0.05] dark:bg-white/[0.06] text-slate-900 dark:text-white font-semibold' : 'text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.06]' }}"
                        title="Proyek Pengapalan & Pabean (PIB/PEB)">
                    <div class="flex items-center space-x-2.5 min-w-0 flex-1 mr-1">
                        <i data-lucide="file-text" class="w-4 h-4 flex-shrink-0 {{ $isDocActive ? 'text-[#007AFF] dark:text-[#0A84FF]' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-900 dark:group-hover:text-white' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Proyek & Pabean</span>
                    </div>
                    <i x-show="!sidebarCollapsed" 
                       data-lucide="chevron-right" 
                       class="w-3.5 h-3.5 transition-transform duration-200 {{ $isDocActive ? 'text-slate-600 dark:text-slate-300' : 'text-slate-400 dark:text-slate-500' }}"
                       :class="{ 'rotate-90': open }"></i>
                </button>

                <!-- SUBMENU PROYEK & PABEAN -->
                <div x-show="open && !sidebarCollapsed" 
                     x-collapse 
                     class="ml-3.5 pl-2.5 border-l border-black/[0.08] dark:border-white/[0.1] space-y-0.5 mt-0.5">
                    <a href="{{ route('core.document.index') }}" 
                       class="flex items-center space-x-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('core.document.*') ? 'bg-[#007AFF] text-white font-semibold shadow-[0_1.5px_4px_rgba(0,122,255,0.28)]' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.05]' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('core.document.*') ? 'bg-white' : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                        <span class="truncate">Semua Proyek</span>
                    </a>
                    <a href="{{ route('bc20.index') }}" 
                       class="flex items-center space-x-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('bc20.*') ? 'bg-[#007AFF] text-white font-semibold shadow-[0_1.5px_4px_rgba(0,122,255,0.28)]' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.05]' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('bc20.*') ? 'bg-white' : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                        <span class="truncate">BC 2.0 (PIB)</span>
                    </a>
                    <a href="{{ route('bc30.index') }}" 
                       class="flex items-center space-x-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('bc30.*') ? 'bg-[#007AFF] text-white font-semibold shadow-[0_1.5px_4px_rgba(0,122,255,0.28)]' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.05]' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('bc30.*') ? 'bg-white' : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                        <span class="truncate">BC 3.0 (PEB)</span>
                    </a>
                </div>
            </div>
            @endif

            <!-- MENU 2: TIM DO (DELIVERY ORDER) -->
            @if(!auth()->check() || auth()->user()->hasPermission('do.view'))
            <a href="{{ route('core.do.index') }}" 
               class="flex items-center space-x-2.5 px-2.5 py-1.5 rounded-[9px] font-medium transition-all group min-h-[36px] active:scale-[0.98] {{ request()->routeIs('core.do.*', 'do.*') ? 'bg-[#007AFF] text-white font-semibold shadow-[0_2px_8px_rgba(0,122,255,0.30)]' : 'text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.06]' }}"
               title="Penebusan Delivery Order Pelayaran & Free Time">
                <i data-lucide="clipboard-check" class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('core.do.*', 'do.*') ? 'text-white' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-900 dark:group-hover:text-white' }}"></i>
                <span x-show="!sidebarCollapsed" class="truncate">DO Pelayaran</span>
            </a>
            @endif

            <!-- MENU 3: TIM TRAFFIC & LAPANGAN -->
            @if(!auth()->check() || auth()->user()->hasPermission('traffic.view'))
            <a href="{{ route('core.traffic.index') }}" 
               class="flex items-center space-x-2.5 px-2.5 py-1.5 rounded-[9px] font-medium transition-all group min-h-[36px] active:scale-[0.98] {{ request()->routeIs('core.traffic.*', 'traffic.*') ? 'bg-[#007AFF] text-white font-semibold shadow-[0_2px_8px_rgba(0,122,255,0.30)]' : 'text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.06]' }}"
               title="Penjaluran SPPB, Gate Out TPS & Bukti Serah Terima (POD)">
                <i data-lucide="truck" class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('core.traffic.*', 'traffic.*') ? 'text-white' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-900 dark:group-hover:text-white' }}"></i>
                <span x-show="!sidebarCollapsed" class="truncate">Traffic & Pelabuhan</span>
            </a>
            @endif
        </div>

        <!-- ============================================================== -->
        <!-- GRUP 3: KEUANGAN & AKUNTANSI                                  -->
        <!-- ============================================================== -->
        <div>
            <div x-show="!sidebarCollapsed" class="px-2.5 mb-1.5 text-[10px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500">
                Keuangan & Akuntansi
            </div>

            <!-- MENU 1: KEUANGAN (FINANCE) - ACCORDION SUBMENU -->
            @if(!auth()->check() || auth()->user()->hasPermission('finance.view'))
            @php $isFinActive = request()->routeIs('finance.*'); @endphp
            <div x-data="{ open: {{ $isFinActive ? 'true' : 'false' }} }" class="space-y-0.5">
                <button type="button" 
                        @click="if (sidebarCollapsed) { window.location.href = '{{ route('finance.invoices.index') }}'; } else { open = !open; }"
                        class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-[9px] font-medium transition-all group min-h-[36px] cursor-pointer active:scale-[0.98] {{ $isFinActive ? 'bg-black/[0.05] dark:bg-white/[0.06] text-slate-900 dark:text-white font-semibold' : 'text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.06]' }}"
                        title="Modul Keuangan (Piutang, Hutang, Kas, Pajak)">
                    <div class="flex items-center space-x-2.5 min-w-0 flex-1 mr-1">
                        <i data-lucide="wallet" class="w-4 h-4 flex-shrink-0 {{ $isFinActive ? 'text-[#34C759]' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-900 dark:group-hover:text-white' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Keuangan (Finance)</span>
                    </div>
                    <i x-show="!sidebarCollapsed" 
                       data-lucide="chevron-right" 
                       class="w-3.5 h-3.5 transition-transform duration-200 {{ $isFinActive ? 'text-slate-600 dark:text-slate-300' : 'text-slate-400 dark:text-slate-500' }}"
                       :class="{ 'rotate-90': open }"></i>
                </button>

                <!-- SUBMENU FINANCE -->
                <div x-show="open && !sidebarCollapsed" 
                     x-collapse 
                     class="ml-3.5 pl-2.5 border-l border-black/[0.08] dark:border-white/[0.1] space-y-0.5 mt-0.5">
                    <a href="{{ route('finance.invoices.index') }}" 
                       class="flex items-center space-x-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('finance.invoices.*') ? 'bg-[#007AFF] text-white font-semibold shadow-[0_1.5px_4px_rgba(0,122,255,0.28)]' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.05]' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('finance.invoices.*') ? 'bg-white' : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                        <span class="truncate">Piutang & Invoice Klien</span>
                    </a>
                    <a href="{{ route('finance.bills.index') }}" 
                       class="flex items-center space-x-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('finance.bills.*') ? 'bg-[#007AFF] text-white font-semibold shadow-[0_1.5px_4px_rgba(0,122,255,0.28)]' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.05]' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('finance.bills.*') ? 'bg-white' : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                        <span class="truncate">Hutang Vendor (AP)</span>
                    </a>
                    <a href="{{ route('finance.cash-bank.index') }}" 
                       class="flex items-center space-x-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('finance.cash-bank.*') ? 'bg-[#007AFF] text-white font-semibold shadow-[0_1.5px_4px_rgba(0,122,255,0.28)]' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.05]' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('finance.cash-bank.*') ? 'bg-white' : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                        <span class="truncate">Buku Kas & Bank</span>
                    </a>
                    <a href="{{ route('finance.tax.index') }}" 
                       class="flex items-center space-x-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('finance.tax.*') ? 'bg-[#007AFF] text-white font-semibold shadow-[0_1.5px_4px_rgba(0,122,255,0.28)]' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.05]' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('finance.tax.*') ? 'bg-white' : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                        <span class="truncate">Perpajakan (PPN & PPh)</span>
                    </a>
                </div>
            </div>
            @endif

            <!-- MENU 2: AKUNTANSI & BUKU BESAR -->
            @if(!auth()->check() || auth()->user()->hasPermission('accounting.view'))
            <a href="{{ route('accounting.index') }}" 
               class="flex items-center space-x-2.5 px-2.5 py-1.5 rounded-[9px] font-medium transition-all group min-h-[36px] active:scale-[0.98] {{ request()->routeIs('accounting.*') ? 'bg-[#007AFF] text-white font-semibold shadow-[0_2px_8px_rgba(0,122,255,0.30)]' : 'text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.06]' }}"
               title="Buku Besar, Jurnal Umum, Laba Rugi & Neraca">
                <i data-lucide="book-open" class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('accounting.*') ? 'text-white' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-900 dark:group-hover:text-white' }}"></i>
                <span x-show="!sidebarCollapsed" class="truncate">Akuntansi & Jurnal</span>
            </a>
            @endif
        </div>

        <!-- ============================================================== -->
        <!-- GRUP 4: BISNIS & SDM                                           -->
        <!-- ============================================================== -->
        <div>
            <div x-show="!sidebarCollapsed" class="px-2.5 mb-1.5 text-[10px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500">
                Bisnis & SDM
            </div>

            <!-- MENU 1: CRM & KOMUNIKASI (ACCORDION SUBMENU) -->
            @if(!auth()->check() || auth()->user()->hasPermission('crm.view'))
            @php $isCrmActive = request()->routeIs('crm.*', 'inbox.*', 'whatsapp.*', 'social-media.*'); @endphp
            <div x-data="{ open: {{ $isCrmActive ? 'true' : 'false' }} }" class="space-y-0.5">
                <button type="button" 
                        @click="if (sidebarCollapsed) { window.location.href = '{{ route('crm.index') }}'; } else { open = !open; }"
                        class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-[9px] font-medium transition-all group min-h-[36px] cursor-pointer active:scale-[0.98] {{ $isCrmActive ? 'bg-black/[0.05] dark:bg-white/[0.06] text-slate-900 dark:text-white font-semibold' : 'text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.06]' }}"
                        title="CRM Klien, Kotak Masuk, WhatsApp WABA & Media Sosial">
                    <div class="flex items-center space-x-2.5 min-w-0 flex-1 mr-1">
                        <i data-lucide="users" class="w-4 h-4 flex-shrink-0 {{ $isCrmActive ? 'text-[#007AFF] dark:text-[#0A84FF]' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-900 dark:group-hover:text-white' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">CRM & Komunikasi</span>
                    </div>
                    <i x-show="!sidebarCollapsed" 
                       data-lucide="chevron-right" 
                       class="w-3.5 h-3.5 transition-transform duration-200 {{ $isCrmActive ? 'text-slate-600 dark:text-slate-300' : 'text-slate-400 dark:text-slate-500' }}"
                       :class="{ 'rotate-90': open }"></i>
                </button>

                <!-- SUBMENU CRM & KOMUNIKASI -->
                <div x-show="open && !sidebarCollapsed" 
                     x-collapse 
                     class="ml-3.5 pl-2.5 border-l border-black/[0.08] dark:border-white/[0.1] space-y-0.5 mt-0.5">
                    <a href="{{ route('crm.index') }}" 
                       class="flex items-center space-x-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('crm.*') ? 'bg-[#007AFF] text-white font-semibold shadow-[0_1.5px_4px_rgba(0,122,255,0.28)]' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.05]' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('crm.*') ? 'bg-white' : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                        <span class="truncate">Direktori Klien & Sales</span>
                    </a>
                    <a href="{{ route('inbox.index') }}" 
                       class="flex items-center space-x-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('inbox.*') ? 'bg-[#007AFF] text-white font-semibold shadow-[0_1.5px_4px_rgba(0,122,255,0.28)]' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.05]' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('inbox.*') ? 'bg-white' : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                        <span class="truncate">Kotak Masuk Terpadu</span>
                    </a>
                    <a href="{{ route('whatsapp.index') }}" 
                       class="flex items-center space-x-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('whatsapp.*') ? 'bg-[#007AFF] text-white font-semibold shadow-[0_1.5px_4px_rgba(0,122,255,0.28)]' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.05]' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('whatsapp.*') ? 'bg-white' : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                        <span class="truncate">WhatsApp WABA Cloud</span>
                    </a>
                    <a href="{{ route('social-media.index') }}" 
                       class="flex items-center space-x-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('social-media.*') ? 'bg-[#007AFF] text-white font-semibold shadow-[0_1.5px_4px_rgba(0,122,255,0.28)]' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.05]' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('social-media.*') ? 'bg-white' : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                        <span class="truncate">Media Sosial Multi-Channel</span>
                    </a>
                </div>
            </div>
            @endif

            <!-- MENU 2: KEPEGAWAIAN (HRM) - ACCORDION SUBMENU -->
            @if(!auth()->check() || auth()->user()->hasPermission('hrm.view'))
            @php $isHrmActive = request()->routeIs('hrm.*'); @endphp
            <div x-data="{ open: {{ $isHrmActive ? 'true' : 'false' }} }" class="space-y-0.5">
                <button type="button" 
                        @click="if (sidebarCollapsed) { window.location.href = '{{ route('hrm.index') }}'; } else { open = !open; }"
                        class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-[9px] font-medium transition-all group min-h-[36px] cursor-pointer active:scale-[0.98] {{ $isHrmActive ? 'bg-black/[0.05] dark:bg-white/[0.06] text-slate-900 dark:text-white font-semibold' : 'text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.06]' }}"
                        title="Kepegawaian, Absensi, Payroll & Portal Mandiri">
                    <div class="flex items-center space-x-2.5 min-w-0 flex-1 mr-1">
                        <i data-lucide="briefcase" class="w-4 h-4 flex-shrink-0 {{ $isHrmActive ? 'text-[#AF52DE]' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-900 dark:group-hover:text-white' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Kepegawaian (HRM)</span>
                    </div>
                    <i x-show="!sidebarCollapsed" 
                       data-lucide="chevron-right" 
                       class="w-3.5 h-3.5 transition-transform duration-200 {{ $isHrmActive ? 'text-slate-600 dark:text-slate-300' : 'text-slate-400 dark:text-slate-500' }}"
                       :class="{ 'rotate-90': open }"></i>
                </button>

                <!-- SUBMENU HRM -->
                <div x-show="open && !sidebarCollapsed" 
                     x-collapse 
                     class="ml-3.5 pl-2.5 border-l border-black/[0.08] dark:border-white/[0.1] space-y-0.5 mt-0.5">
                    <a href="{{ route('hrm.index') }}" 
                       class="flex items-center space-x-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('hrm.index') || (request()->routeIs('hrm.*') && !request()->routeIs('hrm.portal*')) ? 'bg-[#007AFF] text-white font-semibold shadow-[0_1.5px_4px_rgba(0,122,255,0.28)]' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.05]' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('hrm.index') || (request()->routeIs('hrm.*') && !request()->routeIs('hrm.portal*')) ? 'bg-white' : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                        <span class="truncate">Karyawan & Penggajian</span>
                    </a>
                    <a href="{{ route('hrm.portal') }}" 
                       class="flex items-center space-x-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('hrm.portal*') ? 'bg-[#007AFF] text-white font-semibold shadow-[0_1.5px_4px_rgba(0,122,255,0.28)]' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.05]' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('hrm.portal*') ? 'bg-white' : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                        <span class="truncate">Portal Mandiri (ESS)</span>
                    </a>
                </div>
            </div>
            @endif
        </div>

        <!-- ============================================================== -->
        <!-- GRUP 5: SISTEM & PENGATURAN                                   -->
        <!-- ============================================================== -->
        <div>
            <div x-show="!sidebarCollapsed" class="px-2.5 mb-1.5 text-[10px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500">
                Sistem & Pengaturan
            </div>

            <!-- MENU 1: PENGATURAN SISTEM & AKSES (ACCORDION SUBMENU) -->
            @php $isSettingActive = request()->routeIs('settings.roles*', 'setting.*', 'config.*'); @endphp
            <div x-data="{ open: {{ $isSettingActive ? 'true' : 'false' }} }" class="space-y-0.5">
                <button type="button" 
                        @click="if (sidebarCollapsed) { window.location.href = '{{ route('settings.roles.index') }}'; } else { open = !open; }"
                        class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-[9px] font-medium transition-all group min-h-[36px] cursor-pointer active:scale-[0.98] {{ $isSettingActive ? 'bg-black/[0.05] dark:bg-white/[0.06] text-slate-900 dark:text-white font-semibold' : 'text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.06]' }}"
                        title="Manajemen Pengguna, Peran RBAC & Gateway Pabean">
                    <div class="flex items-center space-x-2.5 min-w-0 flex-1 mr-1">
                        <i data-lucide="settings" class="w-4 h-4 flex-shrink-0 {{ $isSettingActive ? 'text-slate-900 dark:text-white' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-900 dark:group-hover:text-white' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Pengaturan Sistem</span>
                    </div>
                    <i x-show="!sidebarCollapsed" 
                       data-lucide="chevron-right" 
                       class="w-3.5 h-3.5 transition-transform duration-200 {{ $isSettingActive ? 'text-slate-600 dark:text-slate-300' : 'text-slate-400 dark:text-slate-500' }}"
                       :class="{ 'rotate-90': open }"></i>
                </button>

                <!-- SUBMENU PENGATURAN -->
                <div x-show="open && !sidebarCollapsed" 
                     x-collapse 
                     class="ml-3.5 pl-2.5 border-l border-black/[0.08] dark:border-white/[0.1] space-y-0.5 mt-0.5">
                    @if(!auth()->check() || auth()->user()->hasPermission('setting.manage') || auth()->user()->hasPermission('setting.view'))
                    <a href="{{ route('settings.roles.index') }}" 
                       class="flex items-center space-x-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('settings.roles*') ? 'bg-[#007AFF] text-white font-semibold shadow-[0_1.5px_4px_rgba(0,122,255,0.28)]' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.05]' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('settings.roles*') ? 'bg-white' : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                        <span class="truncate">Peran & Akses (RBAC)</span>
                    </a>
                    @endif

                    @if(!auth()->check() || auth()->user()->hasPermission('setting.view'))
                    <a href="{{ route('setting.index') }}" 
                       class="flex items-center space-x-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('setting.*') && !request()->routeIs('settings.roles*') ? 'bg-[#007AFF] text-white font-semibold shadow-[0_1.5px_4px_rgba(0,122,255,0.28)]' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.05]' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('setting.*') && !request()->routeIs('settings.roles*') ? 'bg-white' : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                        <span class="truncate">User & Gateway Komunikasi</span>
                    </a>
                    @endif

                    @if(!auth()->check() || auth()->user()->hasPermission('setting.manage'))
                    <a href="{{ route('config.index') }}" 
                       class="flex items-center space-x-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('config.*') ? 'bg-[#007AFF] text-white font-semibold shadow-[0_1.5px_4px_rgba(0,122,255,0.28)]' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.05]' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('config.*') ? 'bg-white' : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                        <span class="truncate">Gateway CEISA 4.0 DJBC</span>
                    </a>
                    @endif
                </div>
            </div>

            <!-- MENU 2: INTEGRASI & PROTOKOL (ACCORDION SUBMENU) -->
            @php $isAiActive = request()->routeIs('ai.*', 'settings.mcp*', 'mcp.*'); @endphp
            <div x-data="{ open: {{ $isAiActive ? 'true' : 'false' }} }" class="space-y-0.5">
                <button type="button" 
                        @click="if (sidebarCollapsed) { window.location.href = '{{ route('settings.mcp') }}'; } else { open = !open; }"
                        class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-[9px] font-medium transition-all group min-h-[36px] cursor-pointer active:scale-[0.98] {{ $isAiActive ? 'bg-black/[0.05] dark:bg-white/[0.06] text-slate-900 dark:text-white font-semibold' : 'text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.06]' }}"
                        title="MCP Server AI & Asisten Pos Tarif HS Code">
                    <div class="flex items-center space-x-2.5 min-w-0 flex-1 mr-1">
                        <i data-lucide="cpu" class="w-4 h-4 flex-shrink-0 {{ $isAiActive ? 'text-[#007AFF] dark:text-[#0A84FF]' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-900 dark:group-hover:text-white' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Integrasi & Protokol</span>
                    </div>
                    <i x-show="!sidebarCollapsed" 
                       data-lucide="chevron-right" 
                       class="w-3.5 h-3.5 transition-transform duration-200 {{ $isAiActive ? 'text-slate-600 dark:text-slate-300' : 'text-slate-400 dark:text-slate-500' }}"
                       :class="{ 'rotate-90': open }"></i>
                </button>

                <!-- SUBMENU INTEGRASI -->
                <div x-show="open && !sidebarCollapsed" 
                     x-collapse 
                     class="ml-3.5 pl-2.5 border-l border-black/[0.08] dark:border-white/[0.1] space-y-0.5 mt-0.5">
                    <a href="{{ route('settings.mcp') }}" 
                       class="flex items-center space-x-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('settings.mcp*') || request()->routeIs('mcp.*') ? 'bg-[#007AFF] text-white font-semibold shadow-[0_1.5px_4px_rgba(0,122,255,0.28)]' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.05]' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('settings.mcp*') || request()->routeIs('mcp.*') ? 'bg-white' : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                        <span class="truncate">MCP Server Protocol</span>
                    </a>
                    @if(!auth()->check() || auth()->user()->hasPermission('ai.view'))
                    <a href="{{ route('ai.index') }}" 
                       class="flex items-center space-x-2 px-2.5 py-1.5 rounded-[7px] text-[12px] font-medium transition-all active:scale-[0.98] {{ request()->routeIs('ai.*') ? 'bg-[#007AFF] text-white font-semibold shadow-[0_1.5px_4px_rgba(0,122,255,0.28)]' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.05]' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('ai.*') ? 'bg-white' : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                        <span class="truncate">Asisten Pos Tarif HS Code</span>
                    </a>
                    @endif
                </div>
            </div>
        </div>

    </nav>

    <!-- 3. MINIMALIST FOOTER STATUS (Version & System Identity) -->
    <div class="px-3.5 py-2.5 border-t border-black/[0.06] dark:border-white/[0.08] text-[11px] text-black/40 dark:text-white/40 flex items-center justify-between shrink-0">
        <span x-show="!sidebarCollapsed" class="truncate font-medium">GENIE ERP v2.4.0</span>
        <span x-show="!sidebarCollapsed" class="font-mono text-[10px] text-[#34C759] font-semibold">● Ready</span>
        <span x-show="sidebarCollapsed" class="font-mono text-[10px] text-center w-full">v2.4</span>
    </div>
</aside>
