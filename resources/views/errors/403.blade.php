<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Akses Dibatasi — GENIE ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        apple: {
                            blue: '#007AFF',
                            gray: '#8E8E93',
                        }
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --font-fallback: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Inter", system-ui, sans-serif;
        }
        body {
            font-family: var(--font-fallback);
            -webkit-font-smoothing: antialiased;
        }
        .btn-apple-filled {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            min-height: 40px;
            padding: 0.5rem 1.125rem;
            border-radius: 980px;
            font-size: 0.8125rem;
            font-weight: 600;
            background-color: #007AFF;
            color: #ffffff;
            transition: all 0.15s cubic-bezier(0.25, 0.1, 0.25, 1);
        }
        .btn-apple-filled:hover {
            background-color: #0062CC;
            transform: scale(1.01);
        }
        .btn-apple-gray {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            min-height: 40px;
            padding: 0.5rem 1.125rem;
            border-radius: 980px;
            font-size: 0.8125rem;
            font-weight: 600;
            background-color: rgba(142, 142, 147, 0.12);
            color: inherit;
            transition: all 0.15s cubic-bezier(0.25, 0.1, 0.25, 1);
        }
        .btn-apple-gray:hover {
            background-color: rgba(142, 142, 147, 0.2);
        }
    </style>
</head>
<body class="h-full flex items-center justify-center p-4 bg-[#F2F2F7] dark:bg-[#121212] text-slate-900 dark:text-white transition-colors duration-200">
    <div class="max-w-xl w-full bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-slate-200/80 dark:border-white/10 shadow-2xl overflow-hidden p-8 sm:p-10 text-center">
        <!-- Shield Icon -->
        <div class="mx-auto w-16 h-16 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-100 dark:border-rose-900/40 flex items-center justify-center text-rose-600 dark:text-rose-400 mb-5 shadow-xs">
            <i data-lucide="shield-alert" class="w-8 h-8"></i>
        </div>

        <div class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full bg-rose-100 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 text-[11px] font-bold uppercase tracking-wider mb-3">
            <span>HTTP 403 &bull; Otorisasi RBAC Terbatas</span>
        </div>

        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight mb-2">
            Akses Fitur Dibatasi
        </h1>

        <p class="text-xs text-slate-500 dark:text-slate-400 mb-6 leading-relaxed">
            {{ $exception->getMessage() ?: 'Anda tidak memiliki hak akses izin (permission) yang cukup untuk membuka halaman atau mengeksekusi aksi ini.' }}
        </p>

        @if(auth()->check())
            <!-- Current User Badge -->
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-[#2C2C2E]/60 border border-slate-200/80 dark:border-white/10 text-left mb-6 text-xs space-y-2">
                <div class="flex items-center justify-between border-b border-slate-200/60 dark:border-white/10 pb-2">
                    <span class="text-slate-500 dark:text-slate-400 font-medium">Pengguna Aktif:</span>
                    <span class="font-bold text-slate-800 dark:text-white">{{ auth()->user()->name }} ({{ auth()->user()->email }})</span>
                </div>
                <div class="flex items-center justify-between border-b border-slate-200/60 dark:border-white/10 pb-2">
                    <span class="text-slate-500 dark:text-slate-400 font-medium">Peran Anda:</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md font-mono font-bold text-[11px] bg-slate-200 dark:bg-white/10 text-slate-800 dark:text-white">
                        {{ auth()->user()->roles->pluck('name')->implode(', ') ?: 'Tanpa Peran' }}
                    </span>
                </div>
                <div class="flex items-start justify-between pt-1">
                    <span class="text-slate-500 dark:text-slate-400 font-medium flex-shrink-0 mr-2">Izin Dimiliki:</span>
                    <span class="font-mono text-[10px] text-slate-600 dark:text-slate-300 text-right leading-tight">
                        {{ auth()->user()->isSuperAdmin() ? 'Semua Hak Akses (Bypass SuperAdmin)' : (auth()->user()->allPermissions()->pluck('code')->take(5)->implode(', ') . (auth()->user()->allPermissions()->count() > 5 ? ' +' . (auth()->user()->allPermissions()->count() - 5) . ' lainnya' : '')) }}
                    </span>
                </div>
            </div>

            <!-- Quick Switcher Demo (Jika Ingin Coba Sebagai Role Lain) -->
            <div class="mb-6 p-4 rounded-2xl bg-sky-50 dark:bg-sky-950/30 border border-sky-200/60 dark:border-sky-800/40 text-left">
                <div class="text-[11px] font-bold text-sky-800 dark:text-sky-300 mb-2 flex items-center space-x-1.5">
                    <i data-lucide="arrow-left-right" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                    <span>Simulasi: Uji Coba Masuk Sebagai Peran Berwenang</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5 text-[11px]">
                    <a href="{{ route('quick-login', 'R-ADM') }}" class="p-1.5 rounded-lg bg-white dark:bg-[#1C1C1E] border border-sky-200 dark:border-white/15 text-[#007AFF] font-semibold text-center hover:bg-[#007AFF] hover:text-white transition-colors">
                        [R-ADM] Admin
                    </a>
                    <a href="{{ route('quick-login', 'R-OWN') }}" class="p-1.5 rounded-lg bg-white dark:bg-[#1C1C1E] border border-sky-200 dark:border-white/15 text-[#007AFF] font-semibold text-center hover:bg-[#007AFF] hover:text-white transition-colors">
                        [R-OWN] Pimpinan
                    </a>
                    <a href="{{ route('quick-login', 'R-DOC-SPV') }}" class="p-1.5 rounded-lg bg-white dark:bg-[#1C1C1E] border border-sky-200 dark:border-white/15 text-[#007AFF] font-semibold text-center hover:bg-[#007AFF] hover:text-white transition-colors">
                        [R-DOC-SPV] Spv
                    </a>
                    <a href="{{ route('quick-login', 'R-FIN') }}" class="p-1.5 rounded-lg bg-white dark:bg-[#1C1C1E] border border-sky-200 dark:border-white/15 text-[#007AFF] font-semibold text-center hover:bg-[#007AFF] hover:text-white transition-colors">
                        [R-FIN] Finance
                    </a>
                    <a href="{{ route('quick-login', 'R-HR') }}" class="p-1.5 rounded-lg bg-white dark:bg-[#1C1C1E] border border-sky-200 dark:border-white/15 text-[#007AFF] font-semibold text-center hover:bg-[#007AFF] hover:text-white transition-colors">
                        [R-HR] HR
                    </a>
                    <a href="{{ route('quick-login', 'R-DO') }}" class="p-1.5 rounded-lg bg-white dark:bg-[#1C1C1E] border border-sky-200 dark:border-white/15 text-[#007AFF] font-semibold text-center hover:bg-[#007AFF] hover:text-white transition-colors">
                        [R-DO] Staff DO
                    </a>
                </div>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="flex items-center justify-center space-x-3">
            <a href="{{ route('dashboard') }}" class="btn-apple-filled">
                <i data-lucide="home" class="w-4 h-4"></i>
                <span>Kembali ke Dashboard</span>
            </a>
            <button onclick="window.history.back()" class="btn-apple-gray">
                <span>Halaman Sebelumnya</span>
            </button>
        </div>
    </div>

    <script>
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
</body>
</html>
