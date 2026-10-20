<!-- ============================================================== -->
<!-- PATS_CEISA ERP - APPLE DESIGN SYSTEM SIDEBAR                   -->
<!-- Inspired by macOS / iPadOS Navigation & Apple HIG              -->
<!-- ============================================================== -->

<!-- Mobile Backdrop Blur Sheet -->
<div x-show="sidebarOpen" 
     x-cloak
     x-transition:enter="transition-opacity ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-slate-900/40 backdrop-blur-md z-40 lg:hidden"
     @click="sidebarOpen = false"></div>

<!-- Sidebar Container -->
<aside :class="{
           'translate-x-0': sidebarOpen,
           '-translate-x-full lg:translate-x-0': !sidebarOpen,
           'lg:w-64': !sidebarCollapsed,
           'lg:w-20': sidebarCollapsed
       }"
       class="fixed inset-y-0 left-0 z-50 flex flex-col bg-white/90 lg:bg-slate-50/90 backdrop-blur-2xl border-r border-slate-200/80 text-slate-700 transition-all duration-300 ease-in-out lg:sticky lg:top-0 lg:h-screen w-72 flex-shrink-0 shadow-xl lg:shadow-none select-none">

    <!-- 1. APPLE macOS STYLE HEADER & BRAND LOGO -->
    <div class="h-16 flex items-center justify-between px-4 border-b border-slate-200/70 bg-white/50">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 overflow-hidden group">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-sky-600 to-indigo-600 flex items-center justify-center text-white font-bold shadow-md shadow-sky-500/20 group-hover:scale-105 transition-all flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <div x-show="!sidebarCollapsed" class="transition-opacity duration-200">
                <div class="flex items-center space-x-1.5">
                    <span class="font-bold text-[15px] tracking-tight text-slate-900">Genie</span>
                </div>
            </div>
        </a>

        <!-- Desktop Collapse Button (macOS sidebar toggle) -->
        <button @click="sidebarCollapsed = !sidebarCollapsed" 
                type="button"
                class="hidden lg:flex p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 transition-colors"
                title="Sembunyikan / Buka Bilah Samping">
            <svg class="w-4 h-4 transition-transform duration-300" :class="{ 'rotate-180': sidebarCollapsed }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
            </svg>
        </button>

        <!-- Mobile Close Button -->
        <button @click="sidebarOpen = false" 
                type="button"
                class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-200/60">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>


    <!-- 3. MAIN NAVIGATION (APPLE HIG STYLE GROUPED LIST) -->
    <nav class="flex-1 px-2.5 py-2 space-y-4 overflow-y-auto custom-scrollbar text-[13px]">

        <!-- GRUP 1: UTAMA & MONITORING -->
        <div>
            <div x-show="!sidebarCollapsed" class="px-3 mb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                Utama
            </div>
            @if(!auth()->check() || auth()->user()->hasPermission('dashboard.view'))
            <a href="{{ route('dashboard') }}" 
               class="flex items-center space-x-3 px-3 py-2 rounded-xl font-medium transition-all group {{ request()->routeIs('dashboard') ? 'bg-sky-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/50' }}"
               title="Dashboard & KPI">
                <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-slate-500 group-hover:text-sky-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span x-show="!sidebarCollapsed" class="truncate">Dashboard</span>
            </a>
            @endif
        </div>

        <!-- GRUP 2: CORE BISNIS PABEAN (3 TIM UTAMA) -->
        <div>
            <div x-show="!sidebarCollapsed" class="px-3 mb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 flex items-center justify-between">
                <span>Core Bisnis</span>
            </div>

            <!-- TIM DOCUMENT -->
            @if(!auth()->check() || auth()->user()->hasPermission('document.view'))
            <div class="space-y-0.5">
                <a href="{{ route('core.document.index') }}" 
                   class="flex items-center justify-between px-3 py-2 rounded-xl font-medium transition-all group {{ request()->routeIs('core.document.*') ? 'bg-sky-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/50' }}"
                   title="Tim Document: Proyek P01, BC 2.0 & BC 3.0">
                    <div class="flex items-center space-x-3 truncate">
                        <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('core.document.*') ? 'text-white' : 'text-slate-500 group-hover:text-sky-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" class="truncate">Tim Document</span>
                    </div>
                </a>

                <!-- Quick Sub-Links BC 2.0 & BC 3.0 (Clean Apple Segment) -->
                <div x-show="!sidebarCollapsed" class="grid grid-cols-2 gap-1 px-2 pt-0.5 pb-1">
                    <a href="{{ route('bc20.index') }}" 
                       class="px-2 py-1 rounded-lg text-[11px] font-medium transition-colors text-center truncate {{ request()->routeIs('bc20.*') ? 'bg-sky-100 text-sky-800 font-bold border border-sky-200' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-200/40' }}"
                       title="Dokumen PIB Impor">
                        BC 2.0 (PIB)
                    </a>
                    <a href="{{ route('bc30.index') }}" 
                       class="px-2 py-1 rounded-lg text-[11px] font-medium transition-colors text-center truncate {{ request()->routeIs('bc30.*') ? 'bg-sky-100 text-sky-800 font-bold border border-sky-200' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-200/40' }}"
                       title="Dokumen PEB Ekspor">
                        BC 3.0 (PEB)
                    </a>
                </div>
            </div>
            @endif

            <!-- TIM DO (DELIVERY ORDER) -->
            @if(!auth()->check() || auth()->user()->hasPermission('do.view'))
            <a href="{{ route('core.do.index') }}" 
               class="flex items-center space-x-3 px-3 py-2 rounded-xl font-medium transition-all group {{ request()->routeIs('core.do.*', 'do.*') ? 'bg-sky-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/50' }}"
               title="Tim Delivery Order & Pelayaran">
                <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('core.do.*', 'do.*') ? 'text-white' : 'text-slate-500 group-hover:text-amber-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8m-5 5h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293h3.172a1 1 0 00.707-.293l2.414-2.414a1 1 0 01.707-.293H20" />
                </svg>
                <span x-show="!sidebarCollapsed" class="truncate">Tim DO & Pelayaran</span>
            </a>
            @endif

            <!-- TIM TRAFFIC & LAPANGAN -->
            @if(!auth()->check() || auth()->user()->hasPermission('traffic.view'))
            <a href="{{ route('core.traffic.index') }}" 
               class="flex items-center space-x-3 px-3 py-2 rounded-xl font-medium transition-all group {{ request()->routeIs('core.traffic.*', 'traffic.*') ? 'bg-sky-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/50' }}"
               title="Tim Traffic, Behandle & POD">
                <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('core.traffic.*', 'traffic.*') ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                </svg>
                <span x-show="!sidebarCollapsed" class="truncate">Tim Traffic & Lapangan</span>
            </a>
            @endif
        </div>

        <!-- GRUP 3: MANAJEMEN BISNIS & PELANGGAN -->
        <div>
            <div x-show="!sidebarCollapsed" class="px-3 mb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                Manajemen
            </div>

            <!-- CRM & KLIEN -->
            @if(!auth()->check() || auth()->user()->hasPermission('crm.view'))
            <a href="{{ route('crm.index') }}" 
               class="flex items-center justify-between px-3 py-2 rounded-xl font-medium transition-all group {{ request()->routeIs('crm.*') ? 'bg-sky-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/50' }}"
               title="CRM & Data Klien">
                <div class="flex items-center space-x-3 truncate">
                    <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('crm.*') ? 'text-white' : 'text-slate-500 group-hover:text-teal-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">CRM & Klien</span>
                </div>
            </a>
            @endif

            <!-- HRM SDM -->
            @if(!auth()->check() || auth()->user()->hasPermission('hrm.view'))
            <a href="{{ route('hrm.index') }}" 
               class="flex items-center space-x-3 px-3 py-2 rounded-xl font-medium transition-all group {{ request()->routeIs('hrm.*') ? 'bg-sky-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/50' }}"
               title="HRM, Absensi & Gaji">
                <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('hrm.*') ? 'text-white' : 'text-slate-500 group-hover:text-pink-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span x-show="!sidebarCollapsed" class="truncate">HRM (Kepegawaian)</span>
            </a>
            @endif

            <!-- FINANCE (INVOICE P19) -->
            @if(!auth()->check() || auth()->user()->hasPermission('finance.view'))
            <a href="{{ route('finance.invoices.index') }}" 
               class="flex items-center justify-between px-3 py-2 rounded-xl font-medium transition-all group {{ request()->routeIs('finance.invoices.*') ? 'bg-sky-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/50' }}"
               title="Finance & Invoicing (P19)">
                <div class="flex items-center space-x-3 truncate">
                    <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('finance.invoices.*') ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Piutang & Invoice</span>
                </div>
                <span x-show="!sidebarCollapsed" class="text-[9px] font-mono px-1 rounded bg-emerald-100 text-emerald-800 font-bold">P19</span>
            </a>

            <!-- FINANCE (HUTANG VENDOR AP) -->
            <a href="{{ route('finance.bills.index') }}" 
               class="flex items-center justify-between px-3 py-2 rounded-xl font-medium transition-all group {{ request()->routeIs('finance.bills.*') ? 'bg-sky-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/50' }}"
               title="Hutang Vendor & Biaya Operasional (AP)">
                <div class="flex items-center space-x-3 truncate">
                    <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('finance.bills.*') ? 'text-white' : 'text-slate-500 group-hover:text-rose-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z" />
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Hutang Vendor</span>
                </div>
                <span x-show="!sidebarCollapsed" class="text-[9px] font-mono px-1 rounded bg-rose-100 text-rose-800 font-bold">AP</span>
            </a>

            <!-- FINANCE (KAS & BANK) -->
            <a href="{{ route('finance.cash-bank.index') }}" 
               class="flex items-center justify-between px-3 py-2 rounded-xl font-medium transition-all group {{ request()->routeIs('finance.cash-bank.*') ? 'bg-sky-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/50' }}"
               title="Buku Kas & Rekening Bank (Cash Flow)">
                <div class="flex items-center space-x-3 truncate">
                    <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('finance.cash-bank.*') ? 'text-white' : 'text-slate-500 group-hover:text-blue-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">Kas & Bank</span>
                </div>
                <span x-show="!sidebarCollapsed" class="text-[9px] font-mono px-1 rounded bg-blue-100 text-blue-800 font-bold">CB</span>
            </a>
            @endif

            <!-- ACCOUNTING -->
            @if(!auth()->check() || auth()->user()->hasPermission('accounting.view'))
            <a href="{{ route('accounting.index') }}" 
               class="flex items-center space-x-3 px-3 py-2 rounded-xl font-medium transition-all group {{ request()->routeIs('accounting.*') ? 'bg-sky-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/50' }}"
               title="Accounting & Buku Besar">
                <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('accounting.*') ? 'text-white' : 'text-slate-500 group-hover:text-indigo-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span x-show="!sidebarCollapsed" class="truncate">Accounting & Jurnal</span>
            </a>
            @endif
        </div>

        <!-- GRUP 4: AI & PENGATURAN SISTEM -->
        <div>
            <div x-show="!sidebarCollapsed" class="px-3 mb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                Sistem & AI
            </div>

            <!-- AI AGENT -->
            @if(!auth()->check() || auth()->user()->hasPermission('ai.view'))
            <a href="{{ route('ai.index') }}" 
               class="flex items-center justify-between px-3 py-2 rounded-xl font-medium transition-all group {{ request()->routeIs('ai.*') ? 'bg-sky-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/50' }}"
               title="AI Agent & Klasifikasi HS Code BTKI">
                <div class="flex items-center space-x-3 truncate">
                    <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('ai.*') ? 'text-white' : 'text-slate-500 group-hover:text-purple-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span x-show="!sidebarCollapsed" class="truncate">AI Agent Logistik</span>
                </div>
                <span x-show="!sidebarCollapsed" class="w-1.5 h-1.5 rounded-full bg-purple-500 animate-pulse"></span>
            </a>
            @endif

            <!-- USER & RBAC -->
            @if(!auth()->check() || auth()->user()->hasPermission('setting.view'))
            <a href="{{ route('setting.index') }}" 
               class="flex items-center space-x-3 px-3 py-2 rounded-xl font-medium transition-all group {{ request()->routeIs('setting.*') ? 'bg-sky-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/50' }}"
               title="Pengaturan Pengguna & Akses RBAC">
                <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('setting.*') ? 'text-white' : 'text-slate-500 group-hover:text-slate-800' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span x-show="!sidebarCollapsed" class="truncate">User & Role RBAC</span>
            </a>
            @endif

            <!-- CEISA CONFIG -->
            @if(!auth()->check() || auth()->user()->hasPermission('setting.manage'))
            <a href="{{ route('config.index') }}" 
               class="flex items-center space-x-3 px-3 py-2 rounded-xl font-medium transition-all group {{ request()->routeIs('config.*') ? 'bg-sky-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/50' }}"
               title="Kredensial Gateway CEISA 4.0">
                <svg class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('config.*') ? 'text-white' : 'text-slate-500 group-hover:text-rose-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
                <span x-show="!sidebarCollapsed" class="truncate">Gateway CEISA 4.0</span>
            </a>
            @endif
        </div>

    </nav>

    <!-- 4. BOTTOM LOGOUT & QUICK ACTIONS (Apple Clean Minimalist Bar) -->
    <div class="p-3 border-t border-slate-200/70 bg-white/60">
        <a href="{{ route('portal.index') }}" target="_blank" 
           class="w-full flex items-center space-x-2 px-3 py-2 rounded-xl text-sky-700 bg-sky-50 hover:bg-sky-100 text-xs font-bold transition-all mb-2 shadow-xs"
           title="Buka Pratinjau Portal Klien">
            <svg class="w-4 h-4 flex-shrink-0 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            <span x-show="!sidebarCollapsed" class="truncate">Portal Klien ↗</span>
        </a>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" 
                    class="w-full flex items-center space-x-2.5 px-3 py-2 rounded-xl text-slate-500 hover:text-rose-600 hover:bg-rose-50 text-xs font-semibold transition-all">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span x-show="!sidebarCollapsed" class="truncate">Keluar Sistem</span>
            </button>
        </form>
    </div>
</aside>
