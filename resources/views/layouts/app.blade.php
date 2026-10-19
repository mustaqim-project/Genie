<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="PATS_CEISA">
    <meta name="theme-color" content="#ffffff">
    <title>@yield('title', 'Admin Panel') — PATS_CEISA ERP | PT Agung Trans Solusindo</title>

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
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js CDN for interactive dropdowns, modals & sidebar -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <!-- Google Fonts: Inter & JetBrains Mono (Apple SF Pro Equivalent) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Inter", sans-serif; 
            letter-spacing: -0.011em;
            -webkit-font-smoothing: antialiased;
        }
        code, pre, .font-mono { font-family: "SF Mono", "JetBrains Mono", Menlo, monospace; }
        [x-cloak] { display: none !important; }
        .safe-bottom { padding-bottom: max(0.5rem, env(safe-area-inset-bottom)); }
        
        /* Apple Minimalist Scrollbar */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(148, 163, 184, 0.35); border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(100, 116, 139, 0.6); }
    </style>
</head>
<body class="min-h-screen text-slate-800 antialiased selection:bg-brand-500 selection:text-white bg-slate-50 flex flex-col"
      x-data="{ 
          sidebarOpen: false, 
          sidebarCollapsed: false,
          quickSearchOpen: false,
          searchQuery: '' 
      }"
      @keydown.window.prevent.ctrl.k="quickSearchOpen = true"
      @keydown.window.prevent.cmd.k="quickSearchOpen = true"
      @keydown.escape.window="quickSearchOpen = false">

    <div class="flex-1 flex flex-col lg:flex-row w-full min-h-screen">

        <!-- ============================================================== -->
        <!-- 1. SIDEBAR NAVIGATION                                         -->
        <!-- ============================================================== -->
        @include('layouts.sidebar')

        <!-- ============================================================== -->
        <!-- 2. MAIN CONTENT WRAPPER (TOPBAR + BODY + FOOTER)              -->
        <!-- ============================================================== -->
        <div class="flex-1 flex flex-col min-w-0 w-full bg-slate-50">

            <!-- TOPBAR HEADER -->
            @include('layouts.topbar')

            <!-- DYNAMIC FLASH ALERTS -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full pt-4">
                @if(session('success'))
                    <div class="mb-4 flex items-center justify-between p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs shadow-sm">
                        <div class="flex items-center space-x-2.5">
                            <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="font-medium">{{ session('success') }}</span>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 flex items-center justify-between p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs shadow-sm">
                        <div class="flex items-center space-x-2.5">
                            <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="font-medium">{{ session('error') }}</span>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs shadow-sm">
                        <div class="font-bold flex items-center space-x-1.5 mb-1">
                            <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>Mohon periksa kesalahan input berikut:</span>
                        </div>
                        <ul class="list-disc list-inside space-y-0.5 text-rose-700 pl-2">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <!-- MAIN VIEW BODY (APPLE EXPANDED CANVAS) -->
            <main class="flex-1 pb-24 lg:pb-12 w-full">
                @yield('content')
            </main>

            <!-- ENTERPRISE FOOTER -->
            @include('layouts.footer')

        </div>

    </div>

    <!-- ============================================================== -->
    <!-- GLOBAL QUICK SEARCH / COMMAND PALETTE MODAL (CTRL + K)         -->
    <!-- ============================================================== -->
    <div x-show="quickSearchOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto p-4 sm:p-6 md:p-20" 
         role="dialog" 
         aria-modal="true">
        
        <!-- Backdrop -->
        <div x-show="quickSearchOpen"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity" 
             @click="quickSearchOpen = false"></div>

        <!-- Modal Box -->
        <div x-show="quickSearchOpen"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="mx-auto max-w-2xl transform divide-y divide-slate-100 overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 transition-all relative z-10">
            
            <!-- Search Bar Input -->
            <div class="relative flex items-center px-4 py-3">
                <svg class="h-5 w-5 text-slate-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" 
                       x-model="searchQuery"
                       class="h-9 w-full bg-transparent text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none" 
                       placeholder="Ketik untuk mencari No Aju, BL, Kontainer, Klien, atau menu navigasi..."
                       autofocus>
                <button @click="quickSearchOpen = false" class="text-xs px-2 py-1 rounded bg-slate-100 text-slate-500 hover:bg-slate-200">
                    ESC
                </button>
            </div>

            <!-- Quick Action Shortcuts -->
            <div class="p-3 text-xs">
                <div class="font-bold text-[11px] text-slate-400 uppercase tracking-wider px-2 py-1">Pintasan Cepat Operasional</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 mt-1">
                    <a href="{{ route('core.document.index') }}" class="p-2 rounded-xl hover:bg-slate-50 flex items-center space-x-3 text-slate-700 font-medium transition-colors">
                        <span class="w-7 h-7 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center font-bold">P</span>
                        <div>
                            <div class="text-xs font-bold text-slate-900">Project Lifecycle (P01)</div>
                            <div class="text-[10px] text-slate-500">Buat & kelola 9 berkas shipment</div>
                        </div>
                    </a>
                    <a href="{{ route('bc20.create') }}" class="p-2 rounded-xl hover:bg-slate-50 flex items-center space-x-3 text-slate-700 font-medium transition-colors">
                        <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold">20</span>
                        <div>
                            <div class="text-xs font-bold text-slate-900">Ajukan BC 2.0 (PIB Impor)</div>
                            <div class="text-[10px] text-slate-500">Draft & submit ke CEISA 4.0</div>
                        </div>
                    </a>
                    <a href="{{ route('bc30.create') }}" class="p-2 rounded-xl hover:bg-slate-50 flex items-center space-x-3 text-slate-700 font-medium transition-colors">
                        <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">30</span>
                        <div>
                            <div class="text-xs font-bold text-slate-900">Ajukan BC 3.0 (PEB Ekspor)</div>
                            <div class="text-[10px] text-slate-500">Pemberitahuan Ekspor Barang</div>
                        </div>
                    </a>
                    <a href="{{ route('core.do.index') }}" class="p-2 rounded-xl hover:bg-slate-50 flex items-center space-x-3 text-slate-700 font-medium transition-colors">
                        <span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold">DO</span>
                        <div>
                            <div class="text-xs font-bold text-slate-900">Penebusan DO & Free Time</div>
                            <div class="text-[10px] text-slate-500">Otomasi email pelayaran</div>
                        </div>
                    </a>
                    <a href="{{ route('core.traffic.index') }}" class="p-2 rounded-xl hover:bg-slate-50 flex items-center space-x-3 text-slate-700 font-medium transition-colors">
                        <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">TR</span>
                        <div>
                            <div class="text-xs font-bold text-slate-900">Traffic, Behandle & Gate Out</div>
                            <div class="text-[10px] text-slate-500">Armada truk & bukti kirim (POD)</div>
                        </div>
                    </a>
                    <a href="{{ route('finance.invoices.index') }}" class="p-2 rounded-xl hover:bg-slate-50 flex items-center space-x-3 text-slate-700 font-medium transition-colors">
                        <span class="w-7 h-7 rounded-lg bg-green-50 text-green-600 flex items-center justify-center font-bold">INV</span>
                        <div>
                            <div class="text-xs font-bold text-slate-900">Tagihan Klien (Invoice P19)</div>
                            <div class="text-[10px] text-slate-500">Reimbursement talangan & jasa PPJK</div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Footer Keyboard Instructions -->
            <div class="bg-slate-50 px-4 py-2 text-[10px] text-slate-500 flex items-center justify-between">
                <span>Gunakan panah untuk bernavigasi, Enter untuk memilih</span>
                <span>Tekan <kbd class="px-1 py-0.5 bg-white border border-slate-200 rounded font-mono">ESC</kbd> untuk keluar</span>
            </div>

        </div>
    </div>

    <!-- APPLE iOS MOBILE BOTTOM TAB BAR -->
    @include('layouts.bottom-tab-bar')

</body>
</html>
