<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50 antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="GENIE ERP">
    <meta name="theme-color" content="#ffffff">
    <title>@yield('title', 'Admin Panel') — GENIE ERP | Freight Forwarding & PPJK Indonesia</title>

    <!-- Design Token Architecture (Apple HIG & 3-Layer Design System) -->
    <link rel="stylesheet" href="{{ asset('css/design-tokens.css') }}">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        surface: 'var(--color-surface)',
                        'page-bg': 'var(--color-page-bg)',
                        brand: {
                            50: '#f0f7ff',
                            100: '#e0effe',
                            200: '#bae0fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0284c7',
                            600: '#0369a1',
                            700: '#075985',
                            800: '#0c4a6e',
                            900: '#082f49',
                            950: '#051b2c',
                        }
                    },
                    borderRadius: {
                        'squircle-sm': 'var(--radius-sm)',
                        'squircle-md': 'var(--radius-md)',
                        'squircle-lg': 'var(--radius-lg)',
                        'squircle-xl': 'var(--radius-xl)',
                        'squircle-2xl': 'var(--radius-2xl)',
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', '-apple-system', 'BlinkMacSystemFont', '"SF Pro Text"', '"SF Pro Display"', 'Inter', 'sans-serif'],
                        mono: ['"JetBrains Mono"', '"SF Mono"', 'Menlo', 'Monaco', 'monospace'],
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js CDN for interactive dropdowns, modals & sidebar -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <!-- Google Fonts: Plus Jakarta Sans & JetBrains Mono (Linear / Apple Tier) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { 
            font-family: "Plus Jakarta Sans", -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", sans-serif; 
            letter-spacing: -0.015em;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        code, pre, .font-mono { 
            font-family: "JetBrains Mono", "SF Mono", Menlo, monospace; 
            letter-spacing: -0.02em;
        }
        [x-cloak] { display: none !important; }
        
        /* Mobile Safe Areas (iPhone Dynamic Island & Home Indicator) */
        .safe-top { padding-top: max(0.75rem, env(safe-area-inset-top)); }
        .safe-bottom { padding-bottom: max(0.75rem, env(safe-area-inset-bottom)); }

        /* iOS Safari Input Auto-Zoom Prevention (Ensures 16px minimum font on mobile viewports) */
        @media screen and (max-width: 639px) {
            input[type="text"],
            input[type="number"],
            input[type="search"],
            input[type="tel"],
            input[type="email"],
            input[type="password"],
            select,
            textarea {
                font-size: 16px !important;
            }
        }
        input, select, textarea, button {
            touch-action: manipulation;
        }

        /* Apple Minimalist Scrollbar */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(148, 163, 184, 0.3); border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(100, 116, 139, 0.55); }

        /* Double-Bezel Card Highlight & Universal Modal Dialog Engine */
        .double-bezel {
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.02), 0 4px 16px 0 rgba(0, 0, 0, 0.03);
        }

        .app-modal-dialog {
            width: 100%;
            max-width: min(calc(100vw - 1.5rem), var(--modal-max-width, 32rem));
            max-height: min(92dvh, calc(100vh - 2rem));
            overflow-y: auto;
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
        }
        .app-modal-dialog-sm { --modal-max-width: 28rem; }
        .app-modal-dialog-md { --modal-max-width: 36rem; }
        .app-modal-dialog-lg { --modal-max-width: 48rem; }
        .app-modal-dialog-xl { --modal-max-width: 64rem; }
    </style>

    <!-- Global Helpers (from COOCA Design Architecture) -->
    <script>
        // Currency Formatter with Tabular Numbers
        window.formatCurrency = function(amount, currency = 'IDR') {
            const num = Number(amount) || 0;
            return (currency === 'IDR' ? 'Rp ' : (currency + ' ')) + num.toLocaleString('id-ID');
        };

        // Universal Client-Side Table Sorting Helper (Alpine.js DOM In-Place, Zero-Reload)
        function coocaTableSort(defaultCol = null, defaultAsc = true) {
            return {
                sortCol: defaultCol,
                sortAsc: defaultAsc,
                sortBy(colIndex, type = 'string', event = null) {
                    if (this.sortCol === colIndex) {
                        this.sortAsc = !this.sortAsc;
                    } else {
                        this.sortCol = colIndex;
                        this.sortAsc = true;
                    }

                    const el = (event && (event.currentTarget || event.target)) || this.$el;
                    const table = (el && el.closest ? el.closest('table') : null) 
                        || (this.$root && (this.$root.tagName === 'TABLE' ? this.$root : this.$root.querySelector('table')))
                        || (this.$el && (this.$el.tagName === 'TABLE' ? this.$el : this.$el.querySelector('table')));
                    if (!table) return;
                    const tbody = table.querySelector('tbody');
                    if (!tbody) return;

                    const rows = Array.from(tbody.children).filter(r => 
                        r.tagName === 'TR' && 
                        !r.hasAttribute('data-empty-row') && 
                        !r.classList.contains('no-sort')
                    );
                    if (rows.length <= 1) return;

                    const multiplier = this.sortAsc ? 1 : -1;

                    const parseSortNumber = (val) => {
                        if (typeof val === 'number') return val;
                        let s = String(val).trim().replace(/[^0-9.,-]/g, '');
                        if (!s || s === '-') return 0;
                        if (s.indexOf('.') !== -1 && s.indexOf(',') !== -1) {
                            s = s.replace(/\./g, '').replace(',', '.');
                        } else if (s.indexOf('.') !== -1 && s.indexOf(',') === -1) {
                            if ((s.match(/\./g) || []).length > 1 || /\.\d{3}$/.test(s)) {
                                s = s.replace(/\./g, '');
                            }
                        }
                        const num = parseFloat(s);
                        return isNaN(num) ? 0 : num;
                    };

                    const getCellVal = (row, idx) => {
                        const cells = Array.from(row.children).filter(c => c.tagName === 'TD' || c.tagName === 'TH');
                        const cell = cells[idx];
                        if (!cell) return '';
                        const attr = cell.getAttribute('data-sort-value');
                        if (attr !== null && attr !== undefined) return attr.trim();
                        return (cell.innerText || cell.textContent || '').trim();
                    };

                    rows.sort((a, b) => {
                        const valA = getCellVal(a, colIndex);
                        const valB = getCellVal(b, colIndex);

                        if (type === 'number') {
                            return (parseSortNumber(valA) - parseSortNumber(valB)) * multiplier;
                        }
                        return String(valA).localeCompare(String(valB), undefined, { numeric: true, sensitivity: 'base' }) * multiplier;
                    });

                    rows.forEach(row => tbody.appendChild(row));
                }
            };
        }
        window.coocaTableSort = coocaTableSort;

        document.addEventListener('alpine:init', () => {
            if (window.Alpine) {
                window.Alpine.data('coocaTableSort', coocaTableSort);
            }
        });
    </script>
</head>
<body class="min-h-[100dvh] text-slate-800 antialiased selection:bg-sky-500 selection:text-white bg-[#F8FAFC] flex flex-col"
      x-data="{ 
          sidebarOpen: false, 
          sidebarCollapsed: false,
          quickSearchOpen: false,
          searchQuery: '' 
      }"
      @keydown.window.prevent.ctrl.k="quickSearchOpen = true"
      @keydown.window.prevent.cmd.k="quickSearchOpen = true"
      @keydown.escape.window="quickSearchOpen = false">

    <div class="flex-1 flex flex-col lg:flex-row w-full min-h-[100dvh]">

        <!-- ============================================================== -->
        <!-- 1. SIDEBAR NAVIGATION (DESKTOP + MOBILE DRAWER)               -->
        <!-- ============================================================== -->
        @include('layouts.sidebar')

        <!-- ============================================================== -->
        <!-- 2. MAIN CONTENT WRAPPER (TOPBAR + BODY + FOOTER)              -->
        <!-- ============================================================== -->
        <div class="flex-1 flex flex-col min-w-0 w-full bg-[#F8FAFC]">

            <!-- TOPBAR HEADER -->
            @include('layouts.topbar')

            <!-- DYNAMIC FLASH ALERTS (APPLE HIG FLOATING NOTIFICATIONS) -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full pt-4">
                @if(session('success'))
                    <div x-data="{ show: true }" 
                         x-show="show" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2"
                         class="mb-4 flex items-center justify-between p-3.5 rounded-2xl bg-emerald-50/90 border border-emerald-200/80 text-emerald-900 text-xs shadow-xs backdrop-blur-xs">
                        <div class="flex items-center space-x-2.5">
                            <span class="w-7 h-7 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600 flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </span>
                            <span class="font-semibold leading-relaxed">{{ session('success') }}</span>
                        </div>
                        <button @click="show = false" class="p-1 rounded-lg text-emerald-500 hover:text-emerald-700 hover:bg-emerald-100/50 transition-colors" aria-label="Tutup notifikasi">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div x-data="{ show: true }" 
                         x-show="show" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2"
                         class="mb-4 flex items-center justify-between p-3.5 rounded-2xl bg-rose-50/90 border border-rose-200/80 text-rose-900 text-xs shadow-xs backdrop-blur-xs">
                        <div class="flex items-center space-x-2.5">
                            <span class="w-7 h-7 rounded-xl bg-rose-100 flex items-center justify-center text-rose-600 flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </span>
                            <span class="font-semibold leading-relaxed">{{ session('error') }}</span>
                        </div>
                        <button @click="show = false" class="p-1 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-100/50 transition-colors" aria-label="Tutup notifikasi">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                @endif

                @if($errors->any())
                    <div x-data="{ show: true }" 
                         x-show="show" 
                         class="mb-4 p-4 rounded-2xl bg-rose-50/90 border border-rose-200/80 text-rose-900 text-xs shadow-xs">
                        <div class="flex items-center justify-between font-bold mb-1.5">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Terdapat beberapa kesalahan validasi input:</span>
                            </div>
                            <button @click="show = false" class="text-rose-500 hover:text-rose-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <ul class="list-disc list-inside space-y-0.5 text-rose-800 pl-2">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <!-- MAIN VIEW BODY (EXPANDED CANVAS) -->
            <main class="flex-1 pb-24 lg:pb-12 w-full">
                @yield('content')
            </main>

            <!-- ENTERPRISE FOOTER -->
            @include('layouts.footer')

        </div>

    </div>

    <!-- ============================================================== -->
    <!-- GLOBAL SPOTLIGHT COMMAND PALETTE MODAL (CTRL + K / ⌘K)        -->
    <!-- ZERO-GAP EDGE-TO-EDGE BACKDROP ARCHITECTURE                    -->
    <!-- ============================================================== -->
    <div x-show="quickSearchOpen" 
         x-cloak
         class="fixed inset-0 z-[200] overflow-y-auto p-4 sm:p-6 md:p-20 flex items-start justify-center" 
         role="dialog" 
         aria-modal="true">
        
        <!-- Standalone Zero-Gap Backdrop -->
        <div x-show="quickSearchOpen"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-950/70 backdrop-blur-md" 
             @click="quickSearchOpen = false"></div>

        <!-- Dialog Card (Double-Bezel Architecture) -->
        <div x-show="quickSearchOpen"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="mx-auto max-w-2xl w-full transform divide-y divide-slate-100 overflow-hidden rounded-3xl bg-white/95 backdrop-blur-2xl shadow-2xl ring-1 ring-black/5 transition-all relative z-10 text-xs">
            
            <!-- Search Bar Input -->
            <div class="relative flex items-center px-4 py-3.5 bg-slate-50/50">
                <svg class="h-5 w-5 text-slate-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" 
                       x-model="searchQuery"
                       class="h-10 w-full bg-transparent text-sm font-medium text-slate-900 placeholder:text-slate-400 focus:outline-none" 
                       placeholder="Cari Dokumen Pabean, No B/L, Kontainer, Invoice, atau Menu..."
                       autofocus>
                <button @click="quickSearchOpen = false" class="text-[11px] font-mono px-2 py-1 rounded-lg bg-slate-200/80 text-slate-600 hover:bg-slate-300 font-semibold transition-colors">
                    ESC
                </button>
            </div>

            <!-- Quick Action Shortcuts -->
            <div class="p-4 text-xs max-h-96 overflow-y-auto">
                <div class="font-bold text-[10px] text-slate-400 uppercase tracking-wider px-2 py-1">Pintasan Cepat Operasional (Spotlight)</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-1.5">
                    <a href="{{ route('core.document.index') }}" class="p-2.5 rounded-2xl hover:bg-slate-100/70 border border-transparent hover:border-slate-200 flex items-center space-x-3 text-slate-700 font-medium transition-all group">
                        <span class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center font-bold text-xs flex-shrink-0 group-hover:scale-105 transition-transform">P01</span>
                        <div class="truncate">
                            <div class="text-xs font-bold text-slate-900 truncate">Project Lifecycle (P01)</div>
                            <div class="text-[10px] text-slate-500 truncate">Buat & kelola berkas pengiriman</div>
                        </div>
                    </a>
                    <a href="{{ route('bc20.create') }}" class="p-2.5 rounded-2xl hover:bg-slate-100/70 border border-transparent hover:border-slate-200 flex items-center space-x-3 text-slate-700 font-medium transition-all group">
                        <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs flex-shrink-0 group-hover:scale-105 transition-transform">BC20</span>
                        <div class="truncate">
                            <div class="text-xs font-bold text-slate-900 truncate">Draft PIB (BC 2.0 Impor)</div>
                            <div class="text-[10px] text-slate-500 truncate">Integrasi CEISA 4.0 DJBC</div>
                        </div>
                    </a>
                    <a href="{{ route('finance.invoices.index') }}" class="p-2.5 rounded-2xl hover:bg-slate-100/70 border border-transparent hover:border-slate-200 flex items-center space-x-3 text-slate-700 font-medium transition-all group">
                        <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs flex-shrink-0 group-hover:scale-105 transition-transform">INV</span>
                        <div class="truncate">
                            <div class="text-xs font-bold text-slate-900 truncate">Piutang & Invoice (P19)</div>
                            <div class="text-[10px] text-slate-500 truncate">Penagihan jasa & reimburse outlay</div>
                        </div>
                    </a>
                    <a href="{{ route('finance.bills.index') }}" class="p-2.5 rounded-2xl hover:bg-slate-100/70 border border-transparent hover:border-slate-200 flex items-center space-x-3 text-slate-700 font-medium transition-all group">
                        <span class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-xs flex-shrink-0 group-hover:scale-105 transition-transform">AP</span>
                        <div class="truncate">
                            <div class="text-xs font-bold text-slate-900 truncate">Hutang Vendor (AP)</div>
                            <div class="text-[10px] text-slate-500 truncate">Pelayaran, Trucking, & Depo TPS</div>
                        </div>
                    </a>
                    <a href="{{ route('finance.cash-bank.index') }}" class="p-2.5 rounded-2xl hover:bg-slate-100/70 border border-transparent hover:border-slate-200 flex items-center space-x-3 text-slate-700 font-medium transition-all group">
                        <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs flex-shrink-0 group-hover:scale-105 transition-transform">CB</span>
                        <div class="truncate">
                            <div class="text-xs font-bold text-slate-900 truncate">Buku Kas & Rekening Bank</div>
                            <div class="text-[10px] text-slate-500 truncate">Mutasi, transfer & rekonsiliasi</div>
                        </div>
                    </a>
                    <a href="{{ route('accounting.index') }}" class="p-2.5 rounded-2xl hover:bg-slate-100/70 border border-transparent hover:border-slate-200 flex items-center space-x-3 text-slate-700 font-medium transition-all group">
                        <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs flex-shrink-0 group-hover:scale-105 transition-transform">GL</span>
                        <div class="truncate">
                            <div class="text-xs font-bold text-slate-900 truncate">Buku Besar, P&L & Neraca</div>
                            <div class="text-[10px] text-slate-500 truncate">Laporan keuangan & job costing</div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Footer Keyboard Instructions -->
            <div class="bg-slate-50/80 px-4 py-2.5 text-[11px] text-slate-500 flex items-center justify-between border-t border-slate-100">
                <span class="flex items-center gap-1">
                    Gunakan <kbd class="px-1.5 py-0.5 bg-white border border-slate-200 rounded text-[10px] font-mono">↑</kbd> <kbd class="px-1.5 py-0.5 bg-white border border-slate-200 rounded text-[10px] font-mono">↓</kbd> untuk memilih
                </span>
                <span>Tekan <kbd class="px-1.5 py-0.5 bg-white border border-slate-200 rounded text-[10px] font-mono">ESC</kbd> untuk keluar</span>
            </div>

        </div>
    </div>

    <!-- ============================================================== -->
    <!-- 4. APPLE iOS MOBILE BOTTOM TAB BAR (PWA Mobile)               -->
    <!-- ============================================================== -->
    @include('layouts.bottom-tab-bar')

</body>
</html>
