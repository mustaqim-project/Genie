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
           'lg:w-[72px]': sidebarCollapsed
       }"
       class="fixed inset-y-0 left-0 z-50 flex flex-col bg-white/90 dark:bg-[#1C1C1E]/90 backdrop-blur-2xl border-r border-black/[0.08] dark:border-white/[0.08] text-black/80 dark:text-white/80 transition-all duration-250 ease-in-out lg:sticky lg:top-0 lg:h-screen w-64 max-w-[85vw] flex-shrink-0 shadow-xl lg:shadow-none select-none">

    <!-- 1. APPLE macOS STYLE HEADER & BRAND LOGO -->
    <div class="h-14 flex items-center justify-between px-3.5 border-b border-black/[0.06] dark:border-white/[0.08] bg-white/40 dark:bg-white/[0.02]">
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
            <div x-show="!sidebarCollapsed" class="px-2.5 mb-1 text-[10px] font-semibold uppercase tracking-[0.12em] text-black/40 dark:text-white/40">
                Utama
            </div>
            @if(!auth()->check() || auth()->user()->hasPermission('dashboard.view'))
            <a href="{{ route('dashboard') }}" 
               class="flex items-center space-x-2.5 px-2.5 py-1.5 rounded-[8px] font-medium transition-all group min-h-[36px] {{ request()->routeIs('dashboard') ? 'bg-[#007AFF] text-white font-semibold shadow-xs' : 'text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white hover:bg-black/[0.05] dark:hover:bg-white/[0.06]' }}"
               title="Dashboard & KPI Operasional">
                <i data-lucide="layout-dashboard" class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-black/50 dark:text-white/50 group-hover:text-black dark:group-hover:text-white' }}"></i>
                <span x-show="!sidebarCollapsed" class="truncate">Dashboard</span>
            </a>
            @endif
        </div>

        <!-- ============================================================== -->
        <!-- GRUP 2: OPERASIONAL & KEPABEANAN                             -->
        <!-- ============================================================== -->
        <div>
            <div x-show="!sidebarCollapsed" class="px-2.5 mb-1 text-[10px] font-semibold uppercase tracking-[0.12em] text-black/40 dark:text-white/40">
                Operasional & Pabean
            </div>

            <!-- MENU 1: PROYEK PENGAPALAN & DOKUMEN PABEAN (ACCORDION SUBMENU) -->
            @if(!auth()->check() || auth()->user()->hasPermission('document.view'))
            @php $isDocActive = request()->routeIs('core.document.*', 'bc20.*', 'bc30.*'); @endphp
            <div x-data="{ open: {{ $isDocActive ? 'true' : 'false' }} }" class="space-y-0.5">
                <button type="button" 
                        @click="if (sidebarCollapsed) { window.location.href = '{{ route('core.document.index') }}'; } else { open = !open; }"
                        class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-[8px] font-medium transition-all group min-h-[36px] cursor-pointer {{ $isDocActive ? 'bg-[#007AFF] text-white font-semibold shadow-xs' : 'text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white hover:bg-black/[0.05] dark:hover:bg-white/[0.06]' }}"
                        title="Proyek Pengapalan & Pabean (PIB/PEB)">
                    <div class="flex items-center space-x-2.5 min-w-0 flex-1 mr-1">
                        <i data-lucide="file-text" class="w-4 h-4 flex-shrink-0 {{ $isDocActive ? 'text-white' : 'text-black/50 dark:text-white/50 group-hover:text-[#007AFF]' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Proyek & Pabean</span>
                    </div>
                    <i x-show="!sidebarCollapsed" 
                       data-lucide="chevron-right" 
                       class="w-3.5 h-3.5 transition-transform duration-200 {{ $isDocActive ? 'text-white/80' : 'text-black/35 dark:text-white/35' }}"
                       :class="{ 'rotate-90': open }"></i>
                </button>

                <!-- SUBMENU PROYEK & PABEAN -->
                <div x-show="open && !sidebarCollapsed" 
                     x-collapse 
                     class="ml-3 pl-2.5 border-l border-black/[0.08] dark:border-white/[0.08] space-y-0.5 mt-0.5">
                    <a href="{{ route('core.document.index') }}" 
                       class="flex items-center space-x-2 px-2 py-1.5 rounded-[6px] text-[12px] font-medium transition-colors {{ request()->routeIs('core.document.*') ? 'bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.04]' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('core.document.*') ? 'bg-[#007AFF]' : 'bg-black/25 dark:bg-white/25' }}"></span>
                        <span class="truncate">Daftar Proyek Shipment</span>
                    </a>
                    <a href="{{ route('bc20.index') }}" 
                       class="flex items-center space-x-2 px-2 py-1.5 rounded-[6px] text-[12px] font-medium transition-colors {{ request()->routeIs('bc20.*') ? 'bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.04]' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('bc20.*') ? 'bg-[#007AFF]' : 'bg-black/25 dark:bg-white/25' }}"></span>
                        <span class="truncate">Pemberitahuan Impor (BC 2.0)</span>
                    </a>
                    <a href="{{ route('bc30.index') }}" 
                       class="flex items-center space-x-2 px-2 py-1.5 rounded-[6px] text-[12px] font-medium transition-colors {{ request()->routeIs('bc30.*') ? 'bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.04]' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('bc30.*') ? 'bg-[#007AFF]' : 'bg-black/25 dark:bg-white/25' }}"></span>
                        <span class="truncate">Pemberitahuan Ekspor (BC 3.0)</span>
                    </a>
                </div>
            </div>
            @endif

            <!-- MENU 2: TIM DO (DELIVERY ORDER) -->
            @if(!auth()->check() || auth()->user()->hasPermission('do.view'))
            <a href="{{ route('core.do.index') }}" 
               class="flex items-center space-x-2.5 px-2.5 py-1.5 rounded-[8px] font-medium transition-all group min-h-[36px] {{ request()->routeIs('core.do.*', 'do.*') ? 'bg-[#007AFF] text-white font-semibold shadow-xs' : 'text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white hover:bg-black/[0.05] dark:hover:bg-white/[0.06]' }}"
               title="Penebusan Delivery Order Pelayaran & Free Time">
                <i data-lucide="clipboard-check" class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('core.do.*', 'do.*') ? 'text-white' : 'text-black/50 dark:text-white/50 group-hover:text-[#FF9500]' }}"></i>
                <span x-show="!sidebarCollapsed" class="truncate">DO Pelayaran</span>
            </a>
            @endif

            <!-- MENU 3: TIM TRAFFIC & LAPANGAN -->
            @if(!auth()->check() || auth()->user()->hasPermission('traffic.view'))
            <a href="{{ route('core.traffic.index') }}" 
               class="flex items-center space-x-2.5 px-2.5 py-1.5 rounded-[8px] font-medium transition-all group min-h-[36px] {{ request()->routeIs('core.traffic.*', 'traffic.*') ? 'bg-[#007AFF] text-white font-semibold shadow-xs' : 'text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white hover:bg-black/[0.05] dark:hover:bg-white/[0.06]' }}"
               title="Penjaluran SPPB, Gate Out TPS & Bukti Serah Terima (POD)">
                <i data-lucide="truck" class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('core.traffic.*', 'traffic.*') ? 'text-white' : 'text-black/50 dark:text-white/50 group-hover:text-[#34C759]' }}"></i>
                <span x-show="!sidebarCollapsed" class="truncate">Traffic & Pelabuhan</span>
            </a>
            @endif
        </div>

        <!-- ============================================================== -->
        <!-- GRUP 3: KEUANGAN & AKUNTANSI                                  -->
        <!-- ============================================================== -->
        <div>
            <div x-show="!sidebarCollapsed" class="px-2.5 mb-1 text-[10px] font-semibold uppercase tracking-[0.12em] text-black/40 dark:text-white/40">
                Keuangan & Akuntansi
            </div>

            <!-- MENU 1: KEUANGAN (FINANCE) - ACCORDION SUBMENU -->
            @if(!auth()->check() || auth()->user()->hasPermission('finance.view'))
            @php $isFinActive = request()->routeIs('finance.*'); @endphp
            <div x-data="{ open: {{ $isFinActive ? 'true' : 'false' }} }" class="space-y-0.5">
                <button type="button" 
                        @click="if (sidebarCollapsed) { window.location.href = '{{ route('finance.invoices.index') }}'; } else { open = !open; }"
                        class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-[8px] font-medium transition-all group min-h-[36px] cursor-pointer {{ $isFinActive ? 'bg-[#007AFF] text-white font-semibold shadow-xs' : 'text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white hover:bg-black/[0.05] dark:hover:bg-white/[0.06]' }}"
                        title="Modul Keuangan (Piutang, Hutang, Kas, Pajak)">
                    <div class="flex items-center space-x-2.5 min-w-0 flex-1 mr-1">
                        <i data-lucide="wallet" class="w-4 h-4 flex-shrink-0 {{ $isFinActive ? 'text-white' : 'text-black/50 dark:text-white/50 group-hover:text-[#34C759]' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Keuangan (Finance)</span>
                    </div>
                    <i x-show="!sidebarCollapsed" 
                       data-lucide="chevron-right" 
                       class="w-3.5 h-3.5 transition-transform duration-200 {{ $isFinActive ? 'text-white/80' : 'text-black/35 dark:text-white/35' }}"
                       :class="{ 'rotate-90': open }"></i>
                </button>

                <!-- SUBMENU FINANCE -->
                <div x-show="open && !sidebarCollapsed" 
                     x-collapse 
                     class="ml-3 pl-2.5 border-l border-black/[0.08] dark:border-white/[0.08] space-y-0.5 mt-0.5">
                    <a href="{{ route('finance.invoices.index') }}" 
                       class="flex items-center space-x-2 px-2 py-1.5 rounded-[6px] text-[12px] font-medium transition-colors {{ request()->routeIs('finance.invoices.*') ? 'bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.04]' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('finance.invoices.*') ? 'bg-[#007AFF]' : 'bg-black/25 dark:bg-white/25' }}"></span>
                        <span class="truncate">Piutang & Invoice Klien</span>
                    </a>
                    <a href="{{ route('finance.bills.index') }}" 
                       class="flex items-center space-x-2 px-2 py-1.5 rounded-[6px] text-[12px] font-medium transition-colors {{ request()->routeIs('finance.bills.*') ? 'bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.04]' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('finance.bills.*') ? 'bg-[#007AFF]' : 'bg-black/25 dark:bg-white/25' }}"></span>
                        <span class="truncate">Hutang Vendor (AP)</span>
                    </a>
                    <a href="{{ route('finance.cash-bank.index') }}" 
                       class="flex items-center space-x-2 px-2 py-1.5 rounded-[6px] text-[12px] font-medium transition-colors {{ request()->routeIs('finance.cash-bank.*') ? 'bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.04]' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('finance.cash-bank.*') ? 'bg-[#007AFF]' : 'bg-black/25 dark:bg-white/25' }}"></span>
                        <span class="truncate">Buku Kas & Bank</span>
                    </a>
                    <a href="{{ route('finance.tax.index') }}" 
                       class="flex items-center space-x-2 px-2 py-1.5 rounded-[6px] text-[12px] font-medium transition-colors {{ request()->routeIs('finance.tax.*') ? 'bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.04]' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('finance.tax.*') ? 'bg-[#007AFF]' : 'bg-black/25 dark:bg-white/25' }}"></span>
                        <span class="truncate">Perpajakan (PPN & PPh)</span>
                    </a>
                </div>
            </div>
            @endif

            <!-- MENU 2: AKUNTANSI & BUKU BESAR -->
            @if(!auth()->check() || auth()->user()->hasPermission('accounting.view'))
            <a href="{{ route('accounting.index') }}" 
               class="flex items-center space-x-2.5 px-2.5 py-1.5 rounded-[8px] font-medium transition-all group min-h-[36px] {{ request()->routeIs('accounting.*') ? 'bg-[#007AFF] text-white font-semibold shadow-xs' : 'text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white hover:bg-black/[0.05] dark:hover:bg-white/[0.06]' }}"
               title="Buku Besar, Jurnal Umum, Laba Rugi & Neraca">
                <i data-lucide="book-open" class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('accounting.*') ? 'text-white' : 'text-black/50 dark:text-white/50 group-hover:text-[#5856D6]' }}"></i>
                <span x-show="!sidebarCollapsed" class="truncate">Akuntansi & Jurnal</span>
            </a>
            @endif
        </div>

        <!-- ============================================================== -->
        <!-- GRUP 4: BISNIS & SDM                                           -->
        <!-- ============================================================== -->
        <div>
            <div x-show="!sidebarCollapsed" class="px-2.5 mb-1 text-[10px] font-semibold uppercase tracking-[0.12em] text-black/40 dark:text-white/40">
                Bisnis & SDM
            </div>

            <!-- MENU 1: CRM & KOMUNIKASI (ACCORDION SUBMENU) -->
            @if(!auth()->check() || auth()->user()->hasPermission('crm.view'))
            @php $isCrmActive = request()->routeIs('crm.*', 'inbox.*', 'whatsapp.*', 'social-media.*'); @endphp
            <div x-data="{ open: {{ $isCrmActive ? 'true' : 'false' }} }" class="space-y-0.5">
                <button type="button" 
                        @click="if (sidebarCollapsed) { window.location.href = '{{ route('crm.index') }}'; } else { open = !open; }"
                        class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-[8px] font-medium transition-all group min-h-[36px] cursor-pointer {{ $isCrmActive ? 'bg-[#007AFF] text-white font-semibold shadow-xs' : 'text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white hover:bg-black/[0.05] dark:hover:bg-white/[0.06]' }}"
                        title="CRM Klien, Kotak Masuk, WhatsApp WABA & Media Sosial">
                    <div class="flex items-center space-x-2.5 min-w-0 flex-1 mr-1">
                        <i data-lucide="users" class="w-4 h-4 flex-shrink-0 {{ $isCrmActive ? 'text-white' : 'text-black/50 dark:text-white/50 group-hover:text-[#30B0C7]' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">CRM & Komunikasi</span>
                    </div>
                    <i x-show="!sidebarCollapsed" 
                       data-lucide="chevron-right" 
                       class="w-3.5 h-3.5 transition-transform duration-200 {{ $isCrmActive ? 'text-white/80' : 'text-black/35 dark:text-white/35' }}"
                       :class="{ 'rotate-90': open }"></i>
                </button>

                <!-- SUBMENU CRM & KOMUNIKASI (Clean & Minimalist, No Over-decorations) -->
                <div x-show="open && !sidebarCollapsed" 
                     x-collapse 
                     class="ml-3 pl-2.5 border-l border-black/[0.08] dark:border-white/[0.08] space-y-0.5 mt-0.5">
                    <a href="{{ route('crm.index') }}" 
                       class="flex items-center space-x-2 px-2 py-1.5 rounded-[6px] text-[12px] font-medium transition-colors {{ request()->routeIs('crm.*') ? 'bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.04]' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('crm.*') ? 'bg-[#007AFF]' : 'bg-black/25 dark:bg-white/25' }}"></span>
                        <span class="truncate">Direktori Klien & Sales</span>
                    </a>
                    <a href="{{ route('inbox.index') }}" 
                       class="flex items-center space-x-2 px-2 py-1.5 rounded-[6px] text-[12px] font-medium transition-colors {{ request()->routeIs('inbox.*') ? 'bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.04]' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('inbox.*') ? 'bg-[#007AFF]' : 'bg-black/25 dark:bg-white/25' }}"></span>
                        <span class="truncate">Kotak Masuk Terpadu</span>
                    </a>
                    <a href="{{ route('whatsapp.index') }}" 
                       class="flex items-center space-x-2 px-2 py-1.5 rounded-[6px] text-[12px] font-medium transition-colors {{ request()->routeIs('whatsapp.*') ? 'bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.04]' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('whatsapp.*') ? 'bg-[#007AFF]' : 'bg-black/25 dark:bg-white/25' }}"></span>
                        <span class="truncate">WhatsApp WABA Cloud</span>
                    </a>
                    <a href="{{ route('social-media.index') }}" 
                       class="flex items-center space-x-2 px-2 py-1.5 rounded-[6px] text-[12px] font-medium transition-colors {{ request()->routeIs('social-media.*') ? 'bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.04]' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('social-media.*') ? 'bg-[#007AFF]' : 'bg-black/25 dark:bg-white/25' }}"></span>
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
                        class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-[8px] font-medium transition-all group min-h-[36px] cursor-pointer {{ $isHrmActive ? 'bg-[#007AFF] text-white font-semibold shadow-xs' : 'text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white hover:bg-black/[0.05] dark:hover:bg-white/[0.06]' }}"
                        title="Kepegawaian, Absensi, Payroll & Portal Mandiri">
                    <div class="flex items-center space-x-2.5 min-w-0 flex-1 mr-1">
                        <i data-lucide="briefcase" class="w-4 h-4 flex-shrink-0 {{ $isHrmActive ? 'text-white' : 'text-black/50 dark:text-white/50 group-hover:text-[#AF52DE]' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Kepegawaian (HRM)</span>
                    </div>
                    <i x-show="!sidebarCollapsed" 
                       data-lucide="chevron-right" 
                       class="w-3.5 h-3.5 transition-transform duration-200 {{ $isHrmActive ? 'text-white/80' : 'text-black/35 dark:text-white/35' }}"
                       :class="{ 'rotate-90': open }"></i>
                </button>

                <!-- SUBMENU HRM -->
                <div x-show="open && !sidebarCollapsed" 
                     x-collapse 
                     class="ml-3 pl-2.5 border-l border-black/[0.08] dark:border-white/[0.08] space-y-0.5 mt-0.5">
                    <a href="{{ route('hrm.index') }}" 
                       class="flex items-center space-x-2 px-2 py-1.5 rounded-[6px] text-[12px] font-medium transition-colors {{ request()->routeIs('hrm.index') || (request()->routeIs('hrm.*') && !request()->routeIs('hrm.portal*')) ? 'bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.04]' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('hrm.index') || (request()->routeIs('hrm.*') && !request()->routeIs('hrm.portal*')) ? 'bg-[#007AFF]' : 'bg-black/25 dark:bg-white/25' }}"></span>
                        <span class="truncate">Karyawan & Penggajian</span>
                    </a>
                    <a href="{{ route('hrm.portal') }}" 
                       class="flex items-center space-x-2 px-2 py-1.5 rounded-[6px] text-[12px] font-medium transition-colors {{ request()->routeIs('hrm.portal*') ? 'bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.04]' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('hrm.portal*') ? 'bg-[#007AFF]' : 'bg-black/25 dark:bg-white/25' }}"></span>
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
            <div x-show="!sidebarCollapsed" class="px-2.5 mb-1 text-[10px] font-semibold uppercase tracking-[0.12em] text-black/40 dark:text-white/40">
                Sistem & Pengaturan
            </div>

            <!-- MENU 1: PENGATURAN SISTEM & AKSES (ACCORDION SUBMENU) -->
            @php $isSettingActive = request()->routeIs('settings.roles*', 'setting.*', 'config.*'); @endphp
            <div x-data="{ open: {{ $isSettingActive ? 'true' : 'false' }} }" class="space-y-0.5">
                <button type="button" 
                        @click="if (sidebarCollapsed) { window.location.href = '{{ route('settings.roles.index') }}'; } else { open = !open; }"
                        class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-[8px] font-medium transition-all group min-h-[36px] cursor-pointer {{ $isSettingActive ? 'bg-[#007AFF] text-white font-semibold shadow-xs' : 'text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white hover:bg-black/[0.05] dark:hover:bg-white/[0.06]' }}"
                        title="Manajemen Pengguna, Peran RBAC & Gateway Pabean">
                    <div class="flex items-center space-x-2.5 min-w-0 flex-1 mr-1">
                        <i data-lucide="settings" class="w-4 h-4 flex-shrink-0 {{ $isSettingActive ? 'text-white' : 'text-black/50 dark:text-white/50 group-hover:text-black dark:group-hover:text-white' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Pengaturan Sistem</span>
                    </div>
                    <i x-show="!sidebarCollapsed" 
                       data-lucide="chevron-right" 
                       class="w-3.5 h-3.5 transition-transform duration-200 {{ $isSettingActive ? 'text-white/80' : 'text-black/35 dark:text-white/35' }}"
                       :class="{ 'rotate-90': open }"></i>
                </button>

                <!-- SUBMENU PENGATURAN -->
                <div x-show="open && !sidebarCollapsed" 
                     x-collapse 
                     class="ml-3 pl-2.5 border-l border-black/[0.08] dark:border-white/[0.08] space-y-0.5 mt-0.5">
                    @if(!auth()->check() || auth()->user()->hasPermission('setting.manage') || auth()->user()->hasPermission('setting.view'))
                    <a href="{{ route('settings.roles.index') }}" 
                       class="flex items-center space-x-2 px-2 py-1.5 rounded-[6px] text-[12px] font-medium transition-colors {{ request()->routeIs('settings.roles*') ? 'bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.04]' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('settings.roles*') ? 'bg-[#007AFF]' : 'bg-black/25 dark:bg-white/25' }}"></span>
                        <span class="truncate">Peran & Akses (RBAC)</span>
                    </a>
                    @endif

                    @if(!auth()->check() || auth()->user()->hasPermission('setting.view'))
                    <a href="{{ route('setting.index') }}" 
                       class="flex items-center space-x-2 px-2 py-1.5 rounded-[6px] text-[12px] font-medium transition-colors {{ request()->routeIs('setting.*') && !request()->routeIs('settings.roles*') ? 'bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.04]' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('setting.*') && !request()->routeIs('settings.roles*') ? 'bg-[#007AFF]' : 'bg-black/25 dark:bg-white/25' }}"></span>
                        <span class="truncate">User & Gateway Komunikasi</span>
                    </a>
                    @endif

                    @if(!auth()->check() || auth()->user()->hasPermission('setting.manage'))
                    <a href="{{ route('config.index') }}" 
                       class="flex items-center space-x-2 px-2 py-1.5 rounded-[6px] text-[12px] font-medium transition-colors {{ request()->routeIs('config.*') ? 'bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.04]' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('config.*') ? 'bg-[#007AFF]' : 'bg-black/25 dark:bg-white/25' }}"></span>
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
                        class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-[8px] font-medium transition-all group min-h-[36px] cursor-pointer {{ $isAiActive ? 'bg-[#007AFF] text-white font-semibold shadow-xs' : 'text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white hover:bg-black/[0.05] dark:hover:bg-white/[0.06]' }}"
                        title="MCP Server AI & Asisten Pos Tarif HS Code">
                    <div class="flex items-center space-x-2.5 min-w-0 flex-1 mr-1">
                        <i data-lucide="cpu" class="w-4 h-4 flex-shrink-0 {{ $isAiActive ? 'text-white' : 'text-black/50 dark:text-white/50 group-hover:text-[#007AFF]' }}"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">Integrasi & Protokol</span>
                    </div>
                    <i x-show="!sidebarCollapsed" 
                       data-lucide="chevron-right" 
                       class="w-3.5 h-3.5 transition-transform duration-200 {{ $isAiActive ? 'text-white/80' : 'text-black/35 dark:text-white/35' }}"
                       :class="{ 'rotate-90': open }"></i>
                </button>

                <!-- SUBMENU INTEGRASI -->
                <div x-show="open && !sidebarCollapsed" 
                     x-collapse 
                     class="ml-3 pl-2.5 border-l border-black/[0.08] dark:border-white/[0.08] space-y-0.5 mt-0.5">
                    <a href="{{ route('settings.mcp') }}" 
                       class="flex items-center space-x-2 px-2 py-1.5 rounded-[6px] text-[12px] font-medium transition-colors {{ request()->routeIs('settings.mcp*') || request()->routeIs('mcp.*') ? 'bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.04]' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('settings.mcp*') || request()->routeIs('mcp.*') ? 'bg-[#007AFF]' : 'bg-black/25 dark:bg-white/25' }}"></span>
                        <span class="truncate">MCP Server Protocol</span>
                    </a>
                    @if(!auth()->check() || auth()->user()->hasPermission('ai.view'))
                    <a href="{{ route('ai.index') }}" 
                       class="flex items-center space-x-2 px-2 py-1.5 rounded-[6px] text-[12px] font-medium transition-colors {{ request()->routeIs('ai.*') ? 'bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] font-semibold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.04] dark:hover:bg-white/[0.04]' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('ai.*') ? 'bg-[#007AFF]' : 'bg-black/25 dark:bg-white/25' }}"></span>
                        <span class="truncate">Asisten Pos Tarif HS Code</span>
                    </a>
                    @endif
                </div>
            </div>
        </div>

    </nav>

    <!-- 3. BOTTOM ACTIONS (Theme Switcher, Portal & Logout - Apple Clean Functional Bar) -->
    <div class="p-2.5 border-t border-black/[0.06] dark:border-white/[0.08] bg-white/40 dark:bg-white/[0.02] space-y-1"
         x-data="{
             currentTheme: localStorage.getItem('genie-theme') || 'light',
             toggleTheme() {
                 this.currentTheme = this.currentTheme === 'dark' ? 'light' : 'dark';
                 localStorage.setItem('genie-theme', this.currentTheme);
                 if (this.currentTheme === 'dark') {
                     document.documentElement.classList.add('dark');
                 } else {
                     document.documentElement.classList.remove('dark');
                 }
                 document.documentElement.setAttribute('data-theme', this.currentTheme);
                 this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
             }
         }">

        <!-- Quick Functional Dock: Theme Toggle & Portal Link -->
        <div class="flex items-center gap-1">
            <button type="button" 
                    @click="toggleTheme()" 
                    class="flex-1 flex items-center justify-center space-x-1.5 px-2.5 py-1.5 rounded-[8px] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.05] dark:hover:bg-white/[0.06] text-[12px] font-medium transition-colors cursor-pointer"
                    :title="currentTheme === 'dark' ? 'Beralih ke Mode Terang' : 'Beralih ke Mode Gelap'">
                <i :data-lucide="currentTheme === 'dark' ? 'moon' : 'sun'" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                <span x-show="!sidebarCollapsed" class="truncate" x-text="currentTheme === 'dark' ? 'Gelap' : 'Terang'"></span>
            </button>

            <a href="{{ route('portal.index') }}" 
               target="_blank" 
               class="flex items-center justify-center space-x-1.5 px-2.5 py-1.5 rounded-[8px] text-black/60 dark:text-white/60 hover:text-[#007AFF] hover:bg-black/[0.05] dark:hover:bg-white/[0.06] text-[12px] font-medium transition-colors"
               title="Buka Portal Klien (Tab Baru)">
                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                <span x-show="!sidebarCollapsed" class="truncate">Portal</span>
            </a>
        </div>

        <!-- Logout Action Button -->
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" 
                    class="w-full flex items-center space-x-2 px-2.5 py-1.5 rounded-[8px] text-black/50 dark:text-white/50 hover:text-[#FF3B30] hover:bg-[#FF3B30]/10 text-[12px] font-medium transition-colors min-h-[34px] cursor-pointer">
                <i data-lucide="log-out" class="w-3.5 h-3.5 flex-shrink-0"></i>
                <span x-show="!sidebarCollapsed" class="truncate">Keluar Sistem</span>
            </button>
        </form>
    </div>
</aside>
