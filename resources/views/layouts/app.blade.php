<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <!-- No-Flash Theme Bootstrap (Eliminates FOUC) -->
    <script>
        (function() {
            try {
                var theme = localStorage.getItem('genie-theme') || 'light';
                var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (theme === 'dark' || (theme === 'system' && prefersDark)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
                document.documentElement.setAttribute('data-theme', theme);
            } catch (e) {}
        })();
    </script>

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

    <!-- Google Fonts: Inter fallback & JetBrains Mono (Apple SF Pro Tier) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script>
        (function() {
            const _origWarn = console.warn;
            console.warn = function(...args) {
                if (typeof args[0] === 'string' && args[0].indexOf('cdn.tailwindcss.com') !== -1) return;
                _origWarn.apply(console, args);
            };
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: [
                            '-apple-system', 'BlinkMacSystemFont', '"SF Pro Text"', '"SF Pro Display"',
                            'Inter', 'system-ui', 'sans-serif'
                        ],
                        mono: [
                            '"JetBrains Mono"', '"SF Mono"', 'Menlo', 'Monaco', 'monospace'
                        ],
                    },
                    colors: {
                        apple: {
                            blue: '#007AFF',
                            'blue-dark': '#0A84FF',
                            green: '#34C759',
                            'green-dark': '#30D158',
                            orange: '#FF9500',
                            'orange-dark': '#FF9F0A',
                            red: '#FF3B30',
                            'red-dark': '#FF453A',
                            purple: '#AF52DE',
                            'purple-dark': '#BF5AF2',
                            teal: '#30B0C7',
                            'teal-dark': '#40C8E0',
                            indigo: '#5856D6',
                            'indigo-dark': '#5E5CE6',
                            yellow: '#FFCC00',
                            'yellow-dark': '#FFD60A',
                            gray: '#8E8E93',
                            gray2: '#AEAEB2',
                            gray3: '#C7C7CC',
                            gray4: '#D1D1D6',
                            gray5: '#E5E5EA',
                            gray6: '#F2F2F7',
                        },
                        brand: {
                            50: '#f0f7ff',
                            100: '#e0effe',
                            200: '#bae0fd',
                            300: '#7cc5fb',
                            400: '#36a6f7',
                            500: '#007AFF',
                            600: '#0071E3',
                            700: '#0056b3',
                            800: '#00448f',
                            900: '#00336d',
                            950: '#001e44',
                        }
                    },
                    borderRadius: {
                        'squircle-sm': 'var(--r-sm, 8px)',
                        'squircle-md': 'var(--r-md, 10px)',
                        'squircle-lg': 'var(--r-lg, 14px)',
                        'squircle-xl': 'var(--r-xl, 16px)',
                        'squircle-2xl': 'var(--r-2xl, 20px)',
                    }
                }
            }
        }
    </script>

    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Alpine.js CDN for interactive dropdowns, modals & sidebar -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <style>
        /* === PATS_CEISA / GENIE DESIGN SYSTEM (APPLE HIG STANDARD FROM COOCA CORE) === */
        :root {
            /* Surface & Background */
            --bg: #F2F2F7;
            --surface: #FFFFFF;
            --surface-2: #F2F2F7;
            --surface-3: #E5E5EA;

            /* Typography */
            --text-1: #000000;
            --text-2: rgba(60, 60, 67, 0.6);
            --text-3: rgba(60, 60, 67, 0.3);
            --text-dis: rgba(60, 60, 67, 0.18);

            /* Borders (Hairline Separators) */
            --border: rgba(60, 60, 67, 0.08);
            --border-sub: rgba(60, 60, 67, 0.04);
            --border-str: rgba(60, 60, 67, 0.18);

            /* Brand Colors (System Blue Master Accent) */
            --brand: #007AFF;
            --brand-hover: #0071E3;
            --brand-light: rgba(0, 122, 255, 0.1);
            --brand-text: #007AFF;

            /* Apple Semantic Status Tokens */
            --color-accent: #007AFF;
            --color-success: #34C759;
            --color-warning: #FF9500;
            --color-danger: #FF3B30;
            --color-ai: #AF52DE;
            --color-info: #5856D6;
            --color-teal: #30B0C7;

            --success: #34C759;
            --success-bg: rgba(52, 199, 89, 0.12);
            --success-border: rgba(52, 199, 89, 0.25);
            --success-text: #248A3D;

            --warn: #FF9500;
            --warn-bg: rgba(255, 149, 0, 0.12);
            --warn-border: rgba(255, 149, 0, 0.25);
            --warn-text: #B25E00;

            --danger: #FF3B30;
            --danger-bg: rgba(255, 59, 48, 0.12);
            --danger-border: rgba(255, 59, 48, 0.25);
            --danger-text: #C41E17;

            --info: #5856D6;
            --info-bg: rgba(88, 86, 214, 0.12);
            --info-border: rgba(88, 86, 214, 0.25);
            --info-text: #413FA6;

            /* Navigation & Sidebar */
            --nav-bg: rgba(242, 242, 247, 0.85);
            --nav-border: rgba(60, 60, 67, 0.08);

            /* Apple Squircle Radius Hierarchy */
            --r-sm: 8px;
            --r-md: 10px;
            --r-lg: 14px;
            --r-xl: 16px;
            --r-2xl: 20px;

            /* Elevation Shadows */
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.04);
            --shadow-md: 0 2px 8px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.06);
            --shadow-xl: 0 20px 50px rgba(0, 0, 0, 0.15);
        }

        .dark {
            /* Surface & Background */
            --bg: #1E1E1E;
            --surface: #1C1C1E;
            --surface-2: #2C2C2E;
            --surface-3: #3A3A3C;

            /* Typography */
            --text-1: #FFFFFF;
            --text-2: rgba(235, 235, 245, 0.6);
            --text-3: rgba(235, 235, 245, 0.3);
            --text-dis: rgba(235, 235, 245, 0.18);

            /* Borders */
            --border: rgba(255, 255, 255, 0.08);
            --border-sub: rgba(255, 255, 255, 0.04);
            --border-str: rgba(255, 255, 255, 0.15);

            /* Brand Colors */
            --brand: #0A84FF;
            --brand-hover: #007AFF;
            --brand-light: rgba(10, 132, 255, 0.15);
            --brand-text: #0A84FF;

            /* Apple Semantic Status Tokens */
            --color-accent: #0A84FF;
            --color-success: #30D158;
            --color-warning: #FF9F0A;
            --color-danger: #FF453A;
            --color-ai: #BF5AF2;
            --color-info: #5E5CE6;
            --color-teal: #40C8E0;

            --success: #30D158;
            --success-bg: rgba(48, 209, 88, 0.14);
            --success-border: rgba(48, 209, 88, 0.28);
            --success-text: #30D158;

            --warn: #FF9F0A;
            --warn-bg: rgba(255, 159, 10, 0.14);
            --warn-border: rgba(255, 159, 10, 0.28);
            --warn-text: #FF9F0A;

            --danger: #FF453A;
            --danger-bg: rgba(255, 69, 58, 0.14);
            --danger-border: rgba(255, 69, 58, 0.28);
            --danger-text: #FF453A;

            --info: #5E5CE6;
            --info-bg: rgba(94, 92, 230, 0.14);
            --info-border: rgba(94, 92, 230, 0.28);
            --info-text: #5E5CE6;

            /* Navigation & Sidebar */
            --nav-bg: rgba(28, 28, 30, 0.85);
            --nav-border: rgba(255, 255, 255, 0.08);

            /* Elevation Shadows */
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.3);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.4);
            --shadow-lg: 0 12px 30px rgba(0, 0, 0, 0.5);
            --shadow-xl: 0 20px 50px rgba(0, 0, 0, 0.6);
        }

        html {
            color-scheme: light;
            box-sizing: border-box;
            -webkit-text-size-adjust: 100%;
            scroll-behavior: smooth;
            background-color: var(--bg);
            color: var(--text-1);
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Inter", system-ui, sans-serif;
        }

        html.dark, .dark {
            color-scheme: dark;
        }

        *, *:before, *:after {
            box-sizing: inherit;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Inter", system-ui, sans-serif;
            letter-spacing: -0.012em;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            background-color: var(--bg);
            color: var(--text-1);
        }

        code, pre, .font-mono {
            font-family: "JetBrains Mono", "SF Mono", Menlo, Monaco, monospace;
            letter-spacing: -0.02em;
        }

        [x-cloak] { display: none !important; }

        /* Mobile Safe Areas (iPhone Dynamic Island & Home Indicator) */
        .safe-top { padding-top: max(0.75rem, env(safe-area-inset-top)); }
        .safe-bottom { padding-bottom: max(0.75rem, env(safe-area-inset-bottom)); }

        /* iOS Safari Input Auto-Zoom Prevention */
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
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(60, 60, 67, 0.2); border-radius: 999px; }
        .dark ::-webkit-scrollbar-thumb { background: rgba(235, 235, 245, 0.2); }
        ::-webkit-scrollbar-thumb:hover { background: rgba(60, 60, 67, 0.35); }
        .dark ::-webkit-scrollbar-thumb:hover { background: rgba(235, 235, 245, 0.35); }

        /* Standard Glassmorphic Surfaces */
        .glass-nav {
            background: var(--nav-bg);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border-right: 1px solid var(--nav-border);
        }

        .glass-header {
            background: var(--surface);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border-bottom: 1px solid var(--nav-border);
        }

        .glass-card {
            background: var(--surface);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--border);
            border-radius: var(--r-lg);
        }

        .glass-card-interactive {
            background: var(--surface);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--border);
            border-radius: var(--r-lg);
            transition: all 0.15s ease-out;
        }

        .glass-card-interactive:hover {
            border-color: rgba(0, 122, 255, 0.3);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        /* Standardized Apple Button Utility Classes */
        .btn-apple-filled {
            min-height: 2.25rem;
            padding-left: 1rem;
            padding-right: 1rem;
            border-radius: var(--r-md);
            font-size: 0.8125rem;
            font-weight: 600;
            color: #ffffff;
            background-color: #007AFF;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
            box-shadow: 0 1px 2px rgba(0, 122, 255, 0.25);
            transition: all 0.15s ease-out;
            cursor: pointer;
            border: none;
        }
        .btn-apple-filled:hover {
            background-color: #0071E3;
        }
        .btn-apple-filled:active {
            transform: scale(0.97);
            opacity: 0.85;
        }

        .btn-apple-tinted {
            min-height: 2.25rem;
            padding-left: 1rem;
            padding-right: 1rem;
            border-radius: var(--r-md);
            font-size: 0.8125rem;
            font-weight: 600;
            color: #007AFF;
            background-color: rgba(0, 122, 255, 0.1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
            transition: all 0.15s ease-out;
            cursor: pointer;
            border: none;
        }
        .btn-apple-tinted:hover {
            background-color: rgba(0, 122, 255, 0.15);
        }
        .btn-apple-tinted:active {
            transform: scale(0.97);
            opacity: 0.8;
        }
        .dark .btn-apple-tinted {
            color: #0A84FF;
            background-color: rgba(10, 132, 255, 0.15);
        }
        .dark .btn-apple-tinted:hover {
            background-color: rgba(10, 132, 255, 0.22);
        }

        .btn-apple-gray {
            min-height: 2.25rem;
            padding-left: 1rem;
            padding-right: 1rem;
            border-radius: var(--r-md);
            font-size: 0.8125rem;
            font-weight: 500;
            color: rgba(0, 0, 0, 0.8);
            background-color: rgba(0, 0, 0, 0.06);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
            transition: all 0.15s ease-out;
            cursor: pointer;
            border: none;
        }
        .btn-apple-gray:hover {
            background-color: rgba(0, 0, 0, 0.09);
        }
        .btn-apple-gray:active {
            transform: scale(0.97);
            opacity: 0.8;
        }
        .dark .btn-apple-gray {
            color: rgba(255, 255, 255, 0.85);
            background-color: rgba(255, 255, 255, 0.08);
        }
        .dark .btn-apple-gray:hover {
            background-color: rgba(255, 255, 255, 0.12);
        }

        .btn-apple-plain {
            min-height: 2.25rem;
            padding-left: 0.5rem;
            padding-right: 0.5rem;
            border-radius: var(--r-sm);
            font-size: 0.8125rem;
            font-weight: 500;
            color: #007AFF;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.25rem;
            transition: all 0.15s ease-out;
            cursor: pointer;
            background: transparent;
            border: none;
        }
        .btn-apple-plain:hover {
            background-color: rgba(0, 122, 255, 0.08);
        }
        .btn-apple-plain:active {
            transform: scale(0.97);
            opacity: 0.8;
        }
        .dark .btn-apple-plain {
            color: #0A84FF;
        }

        .btn-apple-danger {
            min-height: 2.25rem;
            padding-left: 1rem;
            padding-right: 1rem;
            border-radius: var(--r-md);
            font-size: 0.8125rem;
            font-weight: 600;
            color: #ffffff;
            background-color: #FF3B30;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
            box-shadow: 0 1px 2px rgba(255, 59, 48, 0.25);
            transition: all 0.15s ease-out;
            cursor: pointer;
            border: none;
        }
        .btn-apple-danger:hover {
            background-color: #E0352B;
        }
        .btn-apple-danger:active {
            transform: scale(0.97);
            opacity: 0.8;
        }

        /* Backward compatibility mappings */
        .btn-brand-primary {
            background: #007AFF;
            color: #ffffff;
            font-weight: 600;
            font-size: 0.8125rem;
            padding: 0.5rem 1rem;
            border-radius: var(--r-md);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
            box-shadow: 0 1px 2px rgba(0, 122, 255, 0.25);
            transition: all 0.15s ease-out;
        }
        .btn-brand-primary:hover { background: #0071E3; }
        .btn-brand-primary:active { transform: scale(0.97); opacity: 0.8; }

        .btn-brand-secondary {
            background: rgba(0, 0, 0, 0.06);
            color: rgba(0, 0, 0, 0.8);
            font-weight: 500;
            font-size: 0.8125rem;
            padding: 0.5rem 1rem;
            border-radius: var(--r-md);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
            transition: all 0.15s ease-out;
        }
        .dark .btn-brand-secondary {
            background: rgba(255, 255, 255, 0.08);
            color: rgba(255, 255, 255, 0.85);
        }

        /* Universal Adaptive Modal Dialog Engine (Bento Apple HIG v2.0) */
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
        .app-modal-dialog-xxl,
        .app-modal-dialog-2xl { --modal-max-width: min(95vw, 84.375rem); }

        /* Universal Responsive Table Utilities */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            overscroll-behavior-x: contain;
        }
        .table-responsive table {
            min-width: 580px;
            width: 100%;
        }
        .table-responsive-wide table {
            min-width: 760px;
            width: 100%;
        }
        .table-responsive th,
        .table-responsive td.cell-nowrap {
            white-space: nowrap;
        }
    </style>

    {{-- Universal Typography Hierarchy (H1 - H6 & Typographic Roles) --}}
    @include('layouts.partials.typography')

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

        // Initialize Lucide Icons on DOM & Alpine Ready
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
        document.addEventListener('alpine:initialized', () => {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
    </script>

    @stack('styles')
</head>
<body class="h-full bg-[#F2F2F7] dark:bg-[#1E1E1E] text-black dark:text-white antialiased selection:bg-[#007AFF]/20 selection:text-[#007AFF] flex flex-col min-h-[100dvh]"
      x-data="{ 
          sidebarOpen: false, 
          sidebarCollapsed: localStorage.getItem('genie-sidebar-collapsed') === 'true',
          quickSearchOpen: false,
          searchQuery: '',
          currentTheme: localStorage.getItem('genie-theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'),
          toggleSidebarCollapse() {
              this.sidebarCollapsed = !this.sidebarCollapsed;
              localStorage.setItem('genie-sidebar-collapsed', this.sidebarCollapsed);
              window.dispatchEvent(new CustomEvent('sidebar-collapsed-changed', { detail: { collapsed: this.sidebarCollapsed } }));
              this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
          },
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
      }"
      @keydown.window.prevent.ctrl.k="quickSearchOpen = true"
      @keydown.window.prevent.cmd.k="quickSearchOpen = true"
      @keydown.escape.window="quickSearchOpen = false">

    <div class="min-h-full flex flex-col w-full">

        <!-- ============================================================== -->
        <!-- 1. SIDEBAR NAVIGATION (DESKTOP + MOBILE DRAWER)               -->
        <!-- ============================================================== -->
        @include('layouts.sidebar')

        <!-- ============================================================== -->
        <!-- 2. MAIN CONTENT WRAPPER (TOPBAR + BODY + FOOTER)              -->
        <!-- ============================================================== -->
        <div :class="sidebarCollapsed ? 'lg:pl-[72px]' : 'lg:pl-64'"
             class="flex-1 flex flex-col min-w-0 w-full min-h-screen transition-[padding] duration-250 ease-out bg-[#F2F2F7] dark:bg-[#1E1E1E]">

            <!-- TOPBAR HEADER -->
            @include('layouts.topbar')

            <!-- DYNAMIC FLASH ALERTS (APPLE HIG NOTIFICATIONS) -->
            <div class="w-full px-4 sm:px-6 lg:px-8 pt-4">
                @if(session('success'))
                    <div x-data="{ show: true }" 
                         x-show="show" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2"
                         class="mb-4 flex items-center justify-between px-4 py-3 rounded-[14px] bg-[#34C759]/12 border border-[#34C759]/20 text-[13px] text-[#248A3D] dark:text-[#30D158] font-medium shadow-2xs backdrop-blur-md">
                        <div class="flex items-center space-x-2.5">
                            <span class="w-2 h-2 rounded-full bg-[#34C759] shrink-0"></span>
                            <span class="leading-relaxed">{{ session('success') }}</span>
                        </div>
                        <button @click="show = false" class="p-1 rounded-lg text-[#248A3D]/70 hover:text-[#248A3D] hover:bg-[#34C759]/10 transition-colors" aria-label="Tutup notifikasi">
                            <i data-lucide="x" class="w-4 h-4"></i>
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
                         class="mb-4 flex items-center justify-between px-4 py-3 rounded-[14px] bg-[#FF3B30]/12 border border-[#FF3B30]/20 text-[13px] text-[#C41E17] dark:text-[#FF453A] font-medium shadow-2xs backdrop-blur-md">
                        <div class="flex items-center space-x-2.5">
                            <span class="w-2 h-2 rounded-full bg-[#FF3B30] shrink-0"></span>
                            <span class="leading-relaxed">{{ session('error') }}</span>
                        </div>
                        <button @click="show = false" class="p-1 rounded-lg text-[#C41E17]/70 hover:text-[#C41E17] hover:bg-[#FF3B30]/10 transition-colors" aria-label="Tutup notifikasi">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                @endif

                @if($errors->any())
                    <div x-data="{ show: true }" 
                         x-show="show" 
                         class="mb-4 p-4 rounded-[14px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[13px] text-[#C41E17] dark:text-[#FF453A] shadow-2xs">
                        <div class="flex items-center justify-between font-bold mb-1.5">
                            <div class="flex items-center space-x-2">
                                <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 text-[#FF3B30]"></i>
                                <span>Terdapat beberapa kesalahan validasi input:</span>
                            </div>
                            <button @click="show = false" class="text-[#C41E17]/70 hover:text-[#C41E17]">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>
                        <ul class="list-disc list-inside space-y-0.5 pl-2 opacity-90 text-[12px]">
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
    <!-- macOS Sonoma Spotlight Architecture                            -->
    <!-- ============================================================== -->
    <div x-show="quickSearchOpen" 
         x-cloak
         class="fixed inset-0 z-[200] overflow-y-auto p-4 sm:p-6 md:p-20 flex items-start justify-center" 
         role="dialog" 
         aria-modal="true">
        
        <!-- Backdrop Blur Sheet -->
        <div x-show="quickSearchOpen"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/60 backdrop-blur-md" 
             @click="quickSearchOpen = false"></div>

        <!-- Dialog Card (macOS Sonoma Spotlight Style) -->
        <div x-show="quickSearchOpen"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="mx-auto max-w-2xl w-full transform overflow-hidden rounded-[20px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/10 dark:border-white/10 shadow-2xl transition-all relative z-10 text-[13px]">
            
            <!-- Search Bar Input -->
            <div class="relative flex items-center px-4 py-3.5 bg-black/[0.02] dark:bg-white/[0.02] border-b border-black/5 dark:border-white/5">
                <i data-lucide="search" class="w-5 h-5 text-black/40 dark:text-white/40 mr-3 flex-shrink-0"></i>
                <input type="text" 
                       x-model="searchQuery"
                       class="h-10 w-full bg-transparent text-[14px] font-medium text-black dark:text-white placeholder:text-black/40 dark:placeholder:text-white/40 focus:outline-none" 
                       placeholder="Cari Dokumen Pabean, No B/L, Kontainer, Invoice, atau Menu..."
                       autofocus>
                <button @click="quickSearchOpen = false" class="text-[11px] font-mono px-2 py-1 rounded-[8px] bg-black/[0.06] dark:bg-white/[0.08] text-black/60 dark:text-white/60 font-semibold transition-colors">
                    ESC
                </button>
            </div>

            <!-- Quick Action Shortcuts -->
            <div class="p-4 text-[13px] max-h-96 overflow-y-auto">
                <div class="font-bold text-[10px] text-black/40 dark:text-white/40 uppercase tracking-wider px-2 py-1">Pintasan Cepat Operasional (Spotlight)</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-1.5">
                    <a href="{{ route('core.document.index') }}" class="p-2.5 rounded-[12px] hover:bg-black/[0.04] dark:hover:bg-white/[0.04] border border-transparent hover:border-black/5 dark:hover:border-white/5 flex items-center space-x-3 text-black/80 dark:text-white/80 font-medium transition-all group">
                        <span class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold text-xs flex-shrink-0 group-hover:scale-105 transition-transform">P01</span>
                        <div class="truncate">
                            <div class="text-[13px] font-bold text-black dark:text-white truncate">Project Lifecycle (P01)</div>
                            <div class="text-[11px] text-black/45 dark:text-white/45 truncate">Buat & kelola berkas pengiriman</div>
                        </div>
                    </a>
                    <a href="{{ route('bc20.create') }}" class="p-2.5 rounded-[12px] hover:bg-black/[0.04] dark:hover:bg-white/[0.04] border border-transparent hover:border-black/5 dark:hover:border-white/5 flex items-center space-x-3 text-black/80 dark:text-white/80 font-medium transition-all group">
                        <span class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold text-xs flex-shrink-0 group-hover:scale-105 transition-transform">BC20</span>
                        <div class="truncate">
                            <div class="text-[13px] font-bold text-black dark:text-white truncate">Draft PIB (BC 2.0 Impor)</div>
                            <div class="text-[11px] text-black/45 dark:text-white/45 truncate">Integrasi CEISA 4.0 DJBC</div>
                        </div>
                    </a>
                    <a href="{{ route('finance.invoices.index') }}" class="p-2.5 rounded-[12px] hover:bg-black/[0.04] dark:hover:bg-white/[0.04] border border-transparent hover:border-black/5 dark:hover:border-white/5 flex items-center space-x-3 text-black/80 dark:text-white/80 font-medium transition-all group">
                        <span class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center font-bold text-xs flex-shrink-0 group-hover:scale-105 transition-transform">INV</span>
                        <div class="truncate">
                            <div class="text-[13px] font-bold text-black dark:text-white truncate">Piutang & Invoice (P19)</div>
                            <div class="text-[11px] text-black/45 dark:text-white/45 truncate">Penagihan jasa & reimburse outlay</div>
                        </div>
                    </a>
                    <a href="{{ route('finance.bills.index') }}" class="p-2.5 rounded-[12px] hover:bg-black/[0.04] dark:hover:bg-white/[0.04] border border-transparent hover:border-black/5 dark:hover:border-white/5 flex items-center space-x-3 text-black/80 dark:text-white/80 font-medium transition-all group">
                        <span class="w-8 h-8 rounded-[10px] bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center font-bold text-xs flex-shrink-0 group-hover:scale-105 transition-transform">AP</span>
                        <div class="truncate">
                            <div class="text-[13px] font-bold text-black dark:text-white truncate">Hutang Vendor (AP)</div>
                            <div class="text-[11px] text-black/45 dark:text-white/45 truncate">Pelayaran, Trucking, & Depo TPS</div>
                        </div>
                    </a>
                    <a href="{{ route('finance.cash-bank.index') }}" class="p-2.5 rounded-[12px] hover:bg-black/[0.04] dark:hover:bg-white/[0.04] border border-transparent hover:border-black/5 dark:hover:border-white/5 flex items-center space-x-3 text-black/80 dark:text-white/80 font-medium transition-all group">
                        <span class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold text-xs flex-shrink-0 group-hover:scale-105 transition-transform">CB</span>
                        <div class="truncate">
                            <div class="text-[13px] font-bold text-black dark:text-white truncate">Buku Kas & Rekening Bank</div>
                            <div class="text-[11px] text-black/45 dark:text-white/45 truncate">Mutasi, transfer & rekonsiliasi</div>
                        </div>
                    </a>
                    <a href="{{ route('accounting.index') }}" class="p-2.5 rounded-[12px] hover:bg-black/[0.04] dark:hover:bg-white/[0.04] border border-transparent hover:border-black/5 dark:hover:border-white/5 flex items-center space-x-3 text-black/80 dark:text-white/80 font-medium transition-all group">
                        <span class="w-8 h-8 rounded-[10px] bg-[#5856D6]/10 text-[#5856D6] flex items-center justify-center font-bold text-xs flex-shrink-0 group-hover:scale-105 transition-transform">GL</span>
                        <div class="truncate">
                            <div class="text-[13px] font-bold text-black dark:text-white truncate">Buku Besar, P&L & Neraca</div>
                            <div class="text-[11px] text-black/45 dark:text-white/45 truncate">Laporan keuangan & job costing</div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Footer Keyboard Instructions -->
            <div class="bg-black/[0.02] dark:bg-white/[0.02] px-4 py-2.5 text-[11px] text-black/45 dark:text-white/45 flex items-center justify-between border-t border-black/5 dark:border-white/5">
                <span class="flex items-center gap-1">
                    Gunakan <kbd class="px-1.5 py-0.5 bg-black/[0.05] dark:bg-white/[0.1] rounded text-[10px] font-mono">↑</kbd> <kbd class="px-1.5 py-0.5 bg-black/[0.05] dark:bg-white/[0.1] rounded text-[10px] font-mono">↓</kbd> untuk navigasi
                </span>
                <span>Tekan <kbd class="px-1.5 py-0.5 bg-black/[0.05] dark:bg-white/[0.1] rounded text-[10px] font-mono">ESC</kbd> untuk keluar</span>
            </div>

        </div>
    </div>

    <!-- ============================================================== -->
    <!-- 4. APPLE iOS MOBILE BOTTOM TAB BAR (PWA Mobile)               -->
    <!-- ============================================================== -->
    @include('layouts.bottom-tab-bar')

    @stack('scripts')
</body>
</html>
