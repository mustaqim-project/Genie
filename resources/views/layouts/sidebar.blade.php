<!-- ============================================================== -->
<!-- PATS_CEISA ERP - APPLE DESIGN SYSTEM SIDEBAR                   -->
<!-- Inspired by macOS / iPadOS Navigation & Apple HIG              -->
<!-- Hierarchical Groupings, Menus & Accordion Submenus             -->
<!-- ============================================================== -->

<!-- Mobile Backdrop Blur Sheet (Zero-Gap Edge-to-Edge) -->
<div x-show="sidebarOpen" 
     x-cloak
     x-transition:enter="transition-opacity ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-slate-950/60 backdrop-blur-md z-40 lg:hidden"
     @click="sidebarOpen = false"></div>

<!-- Sidebar Container -->
<aside :class="{
           'translate-x-0': sidebarOpen,
           '-translate-x-full lg:translate-x-0': !sidebarOpen,
           'lg:w-72': !sidebarCollapsed,
           'lg:w-20': sidebarCollapsed
       }"
       class="fixed inset-y-0 left-0 z-50 flex flex-col bg-white/95 lg:bg-slate-50/90 backdrop-blur-2xl border-r border-slate-200/80 text-slate-700 transition-all duration-300 ease-in-out lg:sticky lg:top-0 lg:h-screen w-72 max-w-[84vw] flex-shrink-0 shadow-2xl lg:shadow-none select-none">

    <!-- 1. APPLE macOS STYLE HEADER & BRAND LOGO -->
    <div class="h-16 flex items-center justify-between px-4 border-b border-slate-200/70 bg-white/60">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 overflow-hidden group">
            <div class="w-9 h-9 rounded-2xl bg-gradient-to-tr from-slate-900 via-sky-900 to-sky-600 flex items-center justify-center text-white font-bold shadow-md shadow-sky-500/10 group-hover:scale-105 transition-all flex-shrink-0">
                <svg class="w-5 h-5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <div x-show="!sidebarCollapsed" class="transition-opacity duration-200 min-w-0">
                <div class="flex items-center space-x-1.5">
                    <span class="font-extrabold text-base tracking-tight text-slate-900">GENIE</span>
                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-md bg-sky-100 text-sky-800 font-mono">ERP</span>
                </div>
                <div class="text-[10px] text-slate-400 font-medium truncate">PT Agung Trans Solusindo</div>
            </div>
        </a>

        <!-- Desktop Collapse Button (macOS sidebar toggle) -->
        <button @click="sidebarCollapsed = !sidebarCollapsed" 
                type="button"
                class="hidden lg:flex p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 transition-colors cursor-pointer"
                title="Sembunyikan / Buka Bilah Samping">
            <svg class="w-4 h-4 transition-transform duration-300" :class="{ 'rotate-180': sidebarCollapsed }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
            </svg>
        </button>

        <!-- Mobile Close Button (44px target) -->
        <button @click="sidebarOpen = false" 
                type="button"
                class="lg:hidden min-w-[44px] min-h-[44px] flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- 2. MAIN NAVIGATION (APPLE HIG STYLE GROUPED LIST WITH SUBMENUS) -->
    <nav class="flex-1 px-2.5 py-3 space-y-4 overflow-y-auto custom-scrollbar text-[13px] pb-32 lg:pb-6">

        <!-- ============================================================== -->
        <!-- GRUP 1: UTAMA                                                 -->
        <!-- ============================================================== -->
        <div>
            <div x-show="!sidebarCollapsed" class="px-3 mb-1 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400">
                Utama
            </div>
            @if(!auth()->check() || auth()->user()->hasPermission('dashboard.view'))
            <a href="{{ route('dashboard') }}" 
               class="flex items-center space-x-2.5 px-3 py-2 rounded-xl font-medium transition-all group min-h-[42px] {{ request()->routeIs('dashboard') ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/50' }}"
               title="Dashboard & KPI Operasional">
                <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('dashboard') ? 'text-sky-400' : 'text-slate-500 group-hover:text-slate-900' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span x-show="!sidebarCollapsed" class="truncate font-semibold">Dashboard</span>
            </a>
            @endif
        </div>

        <!-- ============================================================== -->
        <!-- GRUP 2: OPERASIONAL & KEPABEANAN                             -->
        <!-- ============================================================== -->
        <div>
            <div x-show="!sidebarCollapsed" class="px-3 mb-1 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400 flex items-center justify-between">
                <span>Operasional & Pabean</span>
            </div>

            <!-- MENU 1: PROYEK PENGAPALAN & DOKUMEN PABEAN (ACCORDION SUBMENU) -->
            @if(!auth()->check() || auth()->user()->hasPermission('document.view'))
            @php $isDocActive = request()->routeIs('core.document.*', 'bc20.*', 'bc30.*'); @endphp
            <div x-data="{ open: {{ $isDocActive ? 'true' : 'false' }} }" class="space-y-0.5">
                <button type="button" 
                        @click="if (sidebarCollapsed) { window.location.href = '{{ route('core.document.index') }}'; } else { open = !open; }"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-xl font-medium transition-all group min-h-[42px] cursor-pointer {{ $isDocActive ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/50' }}"
                        title="Proyek Pengapalan & Pabean (PIB/PEB)">
                    <div class="flex items-center space-x-2.5 min-w-0 flex-1 mr-1.5">
                        <svg class="w-4 h-4 flex-shrink-0 {{ $isDocActive ? 'text-sky-400' : 'text-slate-500 group-hover:text-sky-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate font-semibold">Proyek & Pabean</span>
                    </div>
                    <svg x-show="!sidebarCollapsed" 
                         class="w-3.5 h-3.5 transition-transform duration-200 text-slate-400 group-hover:text-slate-600"
                         :class="{ 'rotate-90 text-sky-400': open }" 
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                <!-- SUBMENU PROYEK & PABEAN -->
                <div x-show="open && !sidebarCollapsed" 
                     x-collapse 
                     class="border-l border-slate-200 ml-4 pl-2 space-y-1 mt-1">
                    <a href="{{ route('core.document.index') }}" 
                       class="flex items-center space-x-2 px-2.5 py-1.5 rounded-lg text-xs font-medium transition-colors {{ request()->routeIs('core.document.*') ? 'bg-sky-50 text-sky-800 font-bold border border-sky-100' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('core.document.*') ? 'bg-sky-600' : 'bg-slate-300' }}"></span>
                        <span class="truncate">Daftar Proyek Shipment</span>
                    </a>
                    <a href="{{ route('bc20.index') }}" 
                       class="flex items-center space-x-2 px-2.5 py-1.5 rounded-lg text-xs font-medium transition-colors {{ request()->routeIs('bc20.*') ? 'bg-sky-50 text-sky-800 font-bold border border-sky-100' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('bc20.*') ? 'bg-sky-600' : 'bg-slate-300' }}"></span>
                        <span class="truncate">Pemberitahuan Impor (BC 2.0)</span>
                    </a>
                    <a href="{{ route('bc30.index') }}" 
                       class="flex items-center space-x-2 px-2.5 py-1.5 rounded-lg text-xs font-medium transition-colors {{ request()->routeIs('bc30.*') ? 'bg-sky-50 text-sky-800 font-bold border border-sky-100' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('bc30.*') ? 'bg-sky-600' : 'bg-slate-300' }}"></span>
                        <span class="truncate">Pemberitahuan Ekspor (BC 3.0)</span>
                    </a>
                </div>
            </div>
            @endif

            <!-- MENU 2: TIM DO (DELIVERY ORDER) -->
            @if(!auth()->check() || auth()->user()->hasPermission('do.view'))
            <a href="{{ route('core.do.index') }}" 
               class="flex items-center space-x-2.5 px-3 py-2 rounded-xl font-medium transition-all group min-h-[42px] {{ request()->routeIs('core.do.*', 'do.*') ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/50' }}"
               title="Penebusan Delivery Order Pelayaran & Free Time">
                <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('core.do.*', 'do.*') ? 'text-sky-400' : 'text-slate-500 group-hover:text-amber-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8m-5 5h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293h3.172a1 1 0 00.707-.293l2.414-2.414a1 1 0 01.707-.293H20" />
                </svg>
                <span x-show="!sidebarCollapsed" class="truncate font-semibold">DO Pelayaran</span>
            </a>
            @endif

            <!-- MENU 3: TIM TRAFFIC & LAPANGAN -->
            @if(!auth()->check() || auth()->user()->hasPermission('traffic.view'))
            <a href="{{ route('core.traffic.index') }}" 
               class="flex items-center space-x-2.5 px-3 py-2 rounded-xl font-medium transition-all group min-h-[42px] {{ request()->routeIs('core.traffic.*', 'traffic.*') ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/50' }}"
               title="Penjaluran SPPB, Gate Out TPS & Bukti Serah Terima (POD)">
                <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('core.traffic.*', 'traffic.*') ? 'text-sky-400' : 'text-slate-500 group-hover:text-emerald-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                </svg>
                <span x-show="!sidebarCollapsed" class="truncate font-semibold">Traffic & Pelabuhan</span>
            </a>
            @endif
        </div>

        <!-- ============================================================== -->
        <!-- GRUP 3: KEUANGAN & AKUNTANSI                                  -->
        <!-- ============================================================== -->
        <div>
            <div x-show="!sidebarCollapsed" class="px-3 mb-1 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400">
                Keuangan & Akuntansi
            </div>

            <!-- MENU 1: KEUANGAN (FINANCE) - ACCORDION SUBMENU -->
            @if(!auth()->check() || auth()->user()->hasPermission('finance.view'))
            @php $isFinActive = request()->routeIs('finance.*'); @endphp
            <div x-data="{ open: {{ $isFinActive ? 'true' : 'false' }} }" class="space-y-0.5">
                <button type="button" 
                        @click="if (sidebarCollapsed) { window.location.href = '{{ route('finance.invoices.index') }}'; } else { open = !open; }"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-xl font-medium transition-all group min-h-[42px] cursor-pointer {{ $isFinActive ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/50' }}"
                        title="Modul Keuangan (Piutang, Hutang, Kas, Pajak)">
                    <div class="flex items-center space-x-2.5 min-w-0 flex-1 mr-1.5">
                        <svg class="w-4 h-4 flex-shrink-0 {{ $isFinActive ? 'text-sky-400' : 'text-slate-500 group-hover:text-emerald-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate font-semibold">Keuangan (Finance)</span>
                    </div>
                    <svg x-show="!sidebarCollapsed" 
                         class="w-3.5 h-3.5 transition-transform duration-200 text-slate-400 group-hover:text-slate-600"
                         :class="{ 'rotate-90 text-sky-400': open }" 
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                <!-- SUBMENU FINANCE -->
                <div x-show="open && !sidebarCollapsed" 
                     x-collapse 
                     class="border-l border-slate-200 ml-4 pl-2 space-y-1 mt-1">
                    <a href="{{ route('finance.invoices.index') }}" 
                       class="flex items-center space-x-2 px-2.5 py-1.5 rounded-lg text-xs font-medium transition-colors {{ request()->routeIs('finance.invoices.*') ? 'bg-sky-50 text-sky-800 font-bold border border-sky-100' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('finance.invoices.*') ? 'bg-sky-600' : 'bg-slate-300' }}"></span>
                        <span class="truncate">Piutang & Invoice Klien</span>
                    </a>
                    <a href="{{ route('finance.bills.index') }}" 
                       class="flex items-center space-x-2 px-2.5 py-1.5 rounded-lg text-xs font-medium transition-colors {{ request()->routeIs('finance.bills.*') ? 'bg-sky-50 text-sky-800 font-bold border border-sky-100' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('finance.bills.*') ? 'bg-sky-600' : 'bg-slate-300' }}"></span>
                        <span class="truncate">Hutang Vendor (AP)</span>
                    </a>
                    <a href="{{ route('finance.cash-bank.index') }}" 
                       class="flex items-center space-x-2 px-2.5 py-1.5 rounded-lg text-xs font-medium transition-colors {{ request()->routeIs('finance.cash-bank.*') ? 'bg-sky-50 text-sky-800 font-bold border border-sky-100' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('finance.cash-bank.*') ? 'bg-sky-600' : 'bg-slate-300' }}"></span>
                        <span class="truncate">Buku Kas & Bank</span>
                    </a>
                    <a href="{{ route('finance.tax.index') }}" 
                       class="flex items-center space-x-2 px-2.5 py-1.5 rounded-lg text-xs font-medium transition-colors {{ request()->routeIs('finance.tax.*') ? 'bg-sky-50 text-sky-800 font-bold border border-sky-100' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('finance.tax.*') ? 'bg-sky-600' : 'bg-slate-300' }}"></span>
                        <span class="truncate">Perpajakan (PPN & PPh)</span>
                    </a>
                </div>
            </div>
            @endif

            <!-- MENU 2: AKUNTANSI & BUKU BESAR -->
            @if(!auth()->check() || auth()->user()->hasPermission('accounting.view'))
            <a href="{{ route('accounting.index') }}" 
               class="flex items-center space-x-2.5 px-3 py-2 rounded-xl font-medium transition-all group min-h-[42px] {{ request()->routeIs('accounting.*') ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/50' }}"
               title="Buku Besar, Jurnal Umum, Laba Rugi & Neraca">
                <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('accounting.*') ? 'text-sky-400' : 'text-slate-500 group-hover:text-indigo-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span x-show="!sidebarCollapsed" class="truncate font-semibold">Akuntansi & Jurnal</span>
            </a>
            @endif
        </div>

        <!-- ============================================================== -->
        <!-- GRUP 4: MANAJEMEN BISNIS & SDM                                -->
        <!-- ============================================================== -->
        <div>
            <div x-show="!sidebarCollapsed" class="px-3 mb-1 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400">
                Bisnis & SDM
            </div>

            <!-- MENU 1: CRM & KOMUNIKASI (ACCORDION SUBMENU) -->
            @if(!auth()->check() || auth()->user()->hasPermission('crm.view'))
            @php $isCrmActive = request()->routeIs('crm.*', 'inbox.*', 'whatsapp.*', 'social-media.*'); @endphp
            <div x-data="{ open: {{ $isCrmActive ? 'true' : 'false' }} }" class="space-y-0.5">
                <button type="button" 
                        @click="if (sidebarCollapsed) { window.location.href = '{{ route('crm.index') }}'; } else { open = !open; }"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-xl font-medium transition-all group min-h-[42px] cursor-pointer {{ $isCrmActive ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/50' }}"
                        title="CRM Klien, Kotak Masuk, WhatsApp WABA & Media Sosial">
                    <div class="flex items-center space-x-2.5 min-w-0 flex-1 mr-1.5">
                        <svg class="w-4 h-4 flex-shrink-0 {{ $isCrmActive ? 'text-sky-400' : 'text-slate-500 group-hover:text-teal-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate font-semibold">CRM & Komunikasi</span>
                    </div>
                    <svg x-show="!sidebarCollapsed" 
                         class="w-3.5 h-3.5 transition-transform duration-200 text-slate-400 group-hover:text-slate-600"
                         :class="{ 'rotate-90 text-sky-400': open }" 
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                <!-- SUBMENU CRM & KOMUNIKASI -->
                <div x-show="open && !sidebarCollapsed" 
                     x-collapse 
                     class="border-l border-slate-200 ml-4 pl-2 space-y-1 mt-1">
                    <a href="{{ route('crm.index') }}" 
                       class="flex items-center space-x-2 px-2.5 py-1.5 rounded-lg text-xs font-medium transition-colors {{ request()->routeIs('crm.*') ? 'bg-sky-50 text-sky-800 font-bold border border-sky-100' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('crm.*') ? 'bg-sky-600' : 'bg-slate-300' }}"></span>
                        <span class="truncate">Direktori Klien & Sales</span>
                    </a>
                    <a href="{{ route('inbox.index') }}" 
                       class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs font-medium transition-colors {{ request()->routeIs('inbox.*') ? 'bg-sky-50 text-sky-800 font-bold border border-sky-100' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100' }}">
                        <span class="flex items-center space-x-2 truncate">
                            <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('inbox.*') ? 'bg-sky-600' : 'bg-slate-300' }}"></span>
                            <span class="truncate">Kotak Masuk Terpadu</span>
                        </span>
                        <span class="text-[9px] font-bold px-1.5 py-0.2 rounded-full bg-emerald-100 text-emerald-800">Live</span>
                    </a>
                    <a href="{{ route('whatsapp.index') }}" 
                       class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs font-medium transition-colors {{ request()->routeIs('whatsapp.*') ? 'bg-sky-50 text-sky-800 font-bold border border-sky-100' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100' }}">
                        <span class="flex items-center space-x-2 truncate">
                            <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('whatsapp.*') ? 'bg-sky-600' : 'bg-slate-300' }}"></span>
                            <span class="truncate">WhatsApp WABA Cloud</span>
                        </span>
                        <span class="text-[9px] font-mono font-bold px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-800">WABA</span>
                    </a>
                    <a href="{{ route('social-media.index') }}" 
                       class="flex items-center space-x-2 px-2.5 py-1.5 rounded-lg text-xs font-medium transition-colors {{ request()->routeIs('social-media.*') ? 'bg-sky-50 text-sky-800 font-bold border border-sky-100' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('social-media.*') ? 'bg-sky-600' : 'bg-slate-300' }}"></span>
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
                        class="w-full flex items-center justify-between px-3 py-2 rounded-xl font-medium transition-all group min-h-[42px] cursor-pointer {{ $isHrmActive ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/50' }}"
                        title="Kepegawaian, Absensi, Payroll & Portal Mandiri">
                    <div class="flex items-center space-x-2.5 min-w-0 flex-1 mr-1.5">
                        <svg class="w-4 h-4 flex-shrink-0 {{ $isHrmActive ? 'text-sky-400' : 'text-slate-500 group-hover:text-pink-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate font-semibold">Kepegawaian (HRM)</span>
                    </div>
                    <svg x-show="!sidebarCollapsed" 
                         class="w-3.5 h-3.5 transition-transform duration-200 text-slate-400 group-hover:text-slate-600"
                         :class="{ 'rotate-90 text-sky-400': open }" 
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                <!-- SUBMENU HRM -->
                <div x-show="open && !sidebarCollapsed" 
                     x-collapse 
                     class="border-l border-slate-200 ml-4 pl-2 space-y-1 mt-1">
                    <a href="{{ route('hrm.index') }}" 
                       class="flex items-center space-x-2 px-2.5 py-1.5 rounded-lg text-xs font-medium transition-colors {{ request()->routeIs('hrm.index') || (request()->routeIs('hrm.*') && !request()->routeIs('hrm.portal*')) ? 'bg-sky-50 text-sky-800 font-bold border border-sky-100' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('hrm.index') || (request()->routeIs('hrm.*') && !request()->routeIs('hrm.portal*')) ? 'bg-sky-600' : 'bg-slate-300' }}"></span>
                        <span class="truncate">Karyawan & Penggajian</span>
                    </a>
                    <a href="{{ route('hrm.portal') }}" 
                       class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs font-medium transition-colors {{ request()->routeIs('hrm.portal*') ? 'bg-sky-50 text-sky-800 font-bold border border-sky-100' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100' }}">
                        <span class="flex items-center space-x-2 truncate">
                            <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('hrm.portal*') ? 'bg-sky-600' : 'bg-slate-300' }}"></span>
                            <span class="truncate">Portal Mandiri (ESS)</span>
                        </span>
                        <span class="text-[9px] font-mono font-bold px-1.5 py-0.2 rounded bg-indigo-100 text-indigo-800">ESS</span>
                    </a>
                </div>
            </div>
            @endif
        </div>

        <!-- ============================================================== -->
        <!-- GRUP 5: SISTEM & PENGATURAN                                   -->
        <!-- ============================================================== -->
        <div>
            <div x-show="!sidebarCollapsed" class="px-3 mb-1 text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400">
                Sistem & Pengaturan
            </div>

            <!-- MENU 1: PENGATURAN SISTEM & AKSES (ACCORDION SUBMENU) -->
            @php $isSettingActive = request()->routeIs('settings.roles*', 'setting.*', 'config.*'); @endphp
            <div x-data="{ open: {{ $isSettingActive ? 'true' : 'false' }} }" class="space-y-0.5">
                <button type="button" 
                        @click="if (sidebarCollapsed) { window.location.href = '{{ route('settings.roles.index') }}'; } else { open = !open; }"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-xl font-medium transition-all group min-h-[42px] cursor-pointer {{ $isSettingActive ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/50' }}"
                        title="Manajemen Pengguna, Peran RBAC & Gateway Pabean">
                    <div class="flex items-center space-x-2.5 min-w-0 flex-1 mr-1.5">
                        <svg class="w-4 h-4 flex-shrink-0 {{ $isSettingActive ? 'text-sky-400' : 'text-slate-500 group-hover:text-slate-800' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate font-semibold">Pengaturan Sistem</span>
                    </div>
                    <svg x-show="!sidebarCollapsed" 
                         class="w-3.5 h-3.5 transition-transform duration-200 text-slate-400 group-hover:text-slate-600"
                         :class="{ 'rotate-90 text-sky-400': open }" 
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                <!-- SUBMENU PENGATURAN -->
                <div x-show="open && !sidebarCollapsed" 
                     x-collapse 
                     class="border-l border-slate-200 ml-4 pl-2 space-y-1 mt-1">
                    @if(!auth()->check() || auth()->user()->hasPermission('setting.manage') || auth()->user()->hasPermission('setting.view'))
                    <a href="{{ route('settings.roles.index') }}" 
                       class="flex items-center space-x-2 px-2.5 py-1.5 rounded-lg text-xs font-medium transition-colors {{ request()->routeIs('settings.roles*') ? 'bg-sky-50 text-sky-800 font-bold border border-sky-100' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('settings.roles*') ? 'bg-sky-600' : 'bg-slate-300' }}"></span>
                        <span class="truncate">Peran & Akses (RBAC)</span>
                    </a>
                    @endif

                    @if(!auth()->check() || auth()->user()->hasPermission('setting.view'))
                    <a href="{{ route('setting.index') }}" 
                       class="flex items-center space-x-2 px-2.5 py-1.5 rounded-lg text-xs font-medium transition-colors {{ request()->routeIs('setting.*') && !request()->routeIs('settings.roles*') ? 'bg-sky-50 text-sky-800 font-bold border border-sky-100' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('setting.*') && !request()->routeIs('settings.roles*') ? 'bg-sky-600' : 'bg-slate-300' }}"></span>
                        <span class="truncate">User & Gateway Komunikasi</span>
                    </a>
                    @endif

                    @if(!auth()->check() || auth()->user()->hasPermission('setting.manage'))
                    <a href="{{ route('config.index') }}" 
                       class="flex items-center space-x-2 px-2.5 py-1.5 rounded-lg text-xs font-medium transition-colors {{ request()->routeIs('config.*') ? 'bg-sky-50 text-sky-800 font-bold border border-sky-100' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('config.*') ? 'bg-sky-600' : 'bg-slate-300' }}"></span>
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
                        class="w-full flex items-center justify-between px-3 py-2 rounded-xl font-medium transition-all group min-h-[42px] cursor-pointer {{ $isAiActive ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/50' }}"
                        title="MCP Server AI & Asisten Pos Tarif HS Code">
                    <div class="flex items-center space-x-2.5 min-w-0 flex-1 mr-1.5">
                        <svg class="w-4 h-4 flex-shrink-0 {{ $isAiActive ? 'text-sky-400' : 'text-slate-500 group-hover:text-sky-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate font-semibold">Integrasi & Protokol</span>
                    </div>
                    <svg x-show="!sidebarCollapsed" 
                         class="w-3.5 h-3.5 transition-transform duration-200 text-slate-400 group-hover:text-slate-600"
                         :class="{ 'rotate-90 text-sky-400': open }" 
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                <!-- SUBMENU INTEGRASI -->
                <div x-show="open && !sidebarCollapsed" 
                     x-collapse 
                     class="border-l border-slate-200 ml-4 pl-2 space-y-1 mt-1">
                    <a href="{{ route('settings.mcp') }}" 
                       class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs font-medium transition-colors {{ request()->routeIs('settings.mcp*') || request()->routeIs('mcp.*') ? 'bg-sky-50 text-sky-800 font-bold border border-sky-100' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100' }}">
                        <span class="flex items-center space-x-2 truncate">
                            <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('settings.mcp*') || request()->routeIs('mcp.*') ? 'bg-sky-600' : 'bg-slate-300' }}"></span>
                            <span class="truncate">MCP Server Protocol</span>
                        </span>
                        <span class="text-[9px] font-mono font-bold px-1.5 py-0.2 rounded bg-sky-100 text-sky-800">MCP</span>
                    </a>
                    @if(!auth()->check() || auth()->user()->hasPermission('ai.view'))
                    <a href="{{ route('ai.index') }}" 
                       class="flex items-center space-x-2 px-2.5 py-1.5 rounded-lg text-xs font-medium transition-colors {{ request()->routeIs('ai.*') ? 'bg-sky-50 text-sky-800 font-bold border border-sky-100' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('ai.*') ? 'bg-sky-600' : 'bg-slate-300' }}"></span>
                        <span class="truncate">Asisten Pos Tarif HS Code</span>
                    </a>
                    @endif
                </div>
            </div>
        </div>

    </nav>

    <!-- 3. BOTTOM LOGOUT & QUICK ACTIONS (Apple Clean Minimalist Bar) -->
    <div class="p-3 border-t border-slate-200/70 bg-white/70">
        <a href="{{ route('portal.index') }}" target="_blank" 
           class="w-full flex items-center space-x-2 px-3 py-2 rounded-xl text-sky-800 bg-sky-50/80 hover:bg-sky-100/90 text-xs font-bold transition-all mb-2 shadow-2xs border border-sky-200/60 active:scale-[0.98]"
           title="Buka Pratinjau Portal Klien">
            <svg class="w-4 h-4 flex-shrink-0 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            <span x-show="!sidebarCollapsed" class="truncate">Portal Klien ↗</span>
        </a>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" 
                    class="w-full flex items-center space-x-2.5 px-3 py-2 rounded-xl text-slate-500 hover:text-rose-600 hover:bg-rose-50/80 text-xs font-semibold transition-all min-h-[40px] cursor-pointer">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span x-show="!sidebarCollapsed" class="truncate">Keluar Sistem</span>
            </button>
        </form>
    </div>
</aside>
