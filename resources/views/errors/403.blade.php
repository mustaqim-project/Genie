<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Akses Dibatasi — GENIE ERP</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        code, pre, .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="h-full flex items-center justify-center p-4 selection:bg-rose-500 selection:text-white">
    <div class="max-w-xl w-full bg-white rounded-3xl border border-slate-200/80 shadow-2xl overflow-hidden p-8 sm:p-10 text-center">
        <!-- Shield Icon -->
        <div class="mx-auto w-20 h-20 rounded-2xl bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-600 mb-6 shadow-inner">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H10m11-5a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>

        <div class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full bg-rose-100 text-rose-700 text-xs font-bold uppercase tracking-wider mb-3">
            <span>HTTP 403 &bull; Otorisasi RBAC Terbatas</span>
        </div>

        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mb-2">
            Akses Fitur Dibatasi
        </h1>

        <p class="text-sm text-slate-600 mb-6 leading-relaxed">
            {{ $exception->getMessage() ?: 'Anda tidak memiliki hak akses izin (permission) yang cukup untuk membuka halaman atau mengeksekusi aksi ini.' }}
        </p>

        @if(auth()->check())
            <!-- Current User Badge -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 text-left mb-6 text-xs space-y-2">
                <div class="flex items-center justify-between border-b border-slate-200/60 pb-2">
                    <span class="text-slate-500 font-medium">Pengguna Aktif:</span>
                    <span class="font-bold text-slate-800">{{ auth()->user()->name }} ({{ auth()->user()->email }})</span>
                </div>
                <div class="flex items-center justify-between border-b border-slate-200/60 pb-2">
                    <span class="text-slate-500 font-medium">Peran Anda:</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md font-mono font-bold text-[11px] bg-slate-200 text-slate-800">
                        {{ auth()->user()->roles->pluck('name')->implode(', ') ?: 'Tanpa Peran' }}
                    </span>
                </div>
                <div class="flex items-start justify-between pt-1">
                    <span class="text-slate-500 font-medium flex-shrink-0 mr-2">Izin Dimiliki:</span>
                    <span class="font-mono text-[10px] text-slate-600 text-right leading-tight">
                        {{ auth()->user()->isSuperAdmin() ? 'Semua Hak Akses (Bypass SuperAdmin)' : (auth()->user()->allPermissions()->pluck('code')->take(5)->implode(', ') . (auth()->user()->allPermissions()->count() > 5 ? ' +' . (auth()->user()->allPermissions()->count() - 5) . ' lainnya' : '')) }}
                    </span>
                </div>
            </div>

            <!-- Quick Switcher Demo (Jika Ingin Coba Sebagai Role Lain) -->
            <div class="mb-6 p-4 rounded-2xl bg-brand-50 border border-brand-200/60 text-left">
                <div class="text-[11px] font-bold text-brand-800 mb-2 flex items-center space-x-1.5">
                    <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    <span>Simulasi: Uji Coba Masuk Sebagai Peran Berwenang</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5 text-[11px]">
                    <a href="{{ route('quick-login', 'R-ADM') }}" class="p-1.5 rounded-lg bg-white border border-brand-200 text-brand-700 font-semibold text-center hover:bg-brand-600 hover:text-white transition-colors">
                        [R-ADM] Admin
                    </a>
                    <a href="{{ route('quick-login', 'R-OWN') }}" class="p-1.5 rounded-lg bg-white border border-brand-200 text-brand-700 font-semibold text-center hover:bg-brand-600 hover:text-white transition-colors">
                        [R-OWN] Pimpinan
                    </a>
                    <a href="{{ route('quick-login', 'R-DOC-SPV') }}" class="p-1.5 rounded-lg bg-white border border-brand-200 text-brand-700 font-semibold text-center hover:bg-brand-600 hover:text-white transition-colors">
                        [R-DOC-SPV] Spv
                    </a>
                    <a href="{{ route('quick-login', 'R-FIN') }}" class="p-1.5 rounded-lg bg-white border border-brand-200 text-brand-700 font-semibold text-center hover:bg-brand-600 hover:text-white transition-colors">
                        [R-FIN] Finance
                    </a>
                    <a href="{{ route('quick-login', 'R-HR') }}" class="p-1.5 rounded-lg bg-white border border-brand-200 text-brand-700 font-semibold text-center hover:bg-brand-600 hover:text-white transition-colors">
                        [R-HR] HR
                    </a>
                    <a href="{{ route('quick-login', 'R-DO') }}" class="p-1.5 rounded-lg bg-white border border-brand-200 text-brand-700 font-semibold text-center hover:bg-brand-600 hover:text-white transition-colors">
                        [R-DO] Staff DO
                    </a>
                </div>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="flex items-center justify-center space-x-3">
            <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition-colors shadow-md">
                Kembali ke Dashboard
            </a>
            <button onclick="window.history.back()" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-50 transition-colors">
                Halaman Sebelumnya
            </button>
        </div>
    </div>
</body>
</html>
