<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PATS_CEISA ERP Kepabeanan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="h-full text-slate-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-gradient-to-br from-slate-950 via-slate-900 to-sky-950">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <!-- Logo -->
        <div class="flex justify-center mb-3">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-sky-600 to-sky-400 flex items-center justify-center text-white shadow-xl shadow-sky-500/20">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
        </div>
        <h2 class="text-center text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
            PATS<span class="text-sky-400">_CEISA</span>
        </h2>
        <p class="mt-1 text-center text-xs text-slate-400 font-medium">
            Sistem ERP Kepabeanan & Logistik — PT Agung Trans Solusindo
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md px-4">
        <div class="bg-slate-800/80 backdrop-blur-xl py-8 px-6 sm:px-10 shadow-2xl rounded-3xl border border-slate-700/80">
            <!-- Flash Alert -->
            @if(session('success'))
                <div class="mb-5 p-3.5 rounded-xl bg-emerald-950/80 border border-emerald-500/40 text-emerald-300 text-xs">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-5 p-3.5 rounded-xl bg-rose-950/80 border border-rose-500/40 text-rose-300 text-xs">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Alamat Email Terdaftar</label>
                    <input type="email" name="email" value="{{ old('email', 'admin@agungtrans.co.id') }}" required autofocus
                        class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Kata Sandi</label>
                    <input type="password" name="password" value="password" required
                        class="w-full px-4 py-2.5 rounded-xl bg-slate-900/90 border border-slate-700 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all">
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded bg-slate-900 border-slate-700 text-sky-500 focus:ring-sky-500">
                        <span class="text-slate-400">Ingat saya</span>
                    </label>
                    <span class="text-slate-500">Default pwd: <code class="text-sky-400 font-mono">password</code></span>
                </div>

                <div>
                    <button type="submit" class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-sky-500 to-sky-600 hover:from-sky-600 hover:to-sky-700 text-white text-sm font-bold shadow-lg shadow-sky-500/25 transition-all hover:scale-[1.01]">
                        Masuk ke Sistem ERP &rarr;
                    </button>
                </div>
            </form>

            <!-- Quick Login Switcher for 12 Roles Testing -->
            <div class="mt-8 pt-6 border-t border-slate-700/80">
                <div class="text-center mb-3">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                        ⚡ Quick Switch Role (Uji Coba RBAC)
                    </span>
                    <p class="text-[10px] text-slate-500 mt-0.5">Klik salah satu peran di bawah untuk login instan sebagai persona tersebut:</p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-[11px]">
                    <a href="{{ route('quick-login', 'R-ADM') }}" class="p-2 rounded-lg bg-slate-900/80 hover:bg-sky-900/40 border border-slate-700 text-left transition-colors">
                        <div class="font-bold text-sky-400">R-ADM</div>
                        <div class="text-[10px] text-slate-400 truncate">Admin Sistem</div>
                    </a>
                    <a href="{{ route('quick-login', 'R-OWN') }}" class="p-2 rounded-lg bg-slate-900/80 hover:bg-sky-900/40 border border-slate-700 text-left transition-colors">
                        <div class="font-bold text-amber-400">R-OWN</div>
                        <div class="text-[10px] text-slate-400 truncate">Soehartono</div>
                    </a>
                    <a href="{{ route('quick-login', 'R-DOC-SPV') }}" class="p-2 rounded-lg bg-slate-900/80 hover:bg-sky-900/40 border border-slate-700 text-left transition-colors">
                        <div class="font-bold text-emerald-400">R-DOC-SPV</div>
                        <div class="text-[10px] text-slate-400 truncate">Spv Dokumen</div>
                    </a>
                    <a href="{{ route('quick-login', 'R-DOC') }}" class="p-2 rounded-lg bg-slate-900/80 hover:bg-sky-900/40 border border-slate-700 text-left transition-colors">
                        <div class="font-bold text-teal-400">R-DOC</div>
                        <div class="text-[10px] text-slate-400 truncate">Staff Dokumen</div>
                    </a>
                    <a href="{{ route('quick-login', 'R-DO') }}" class="p-2 rounded-lg bg-slate-900/80 hover:bg-sky-900/40 border border-slate-700 text-left transition-colors">
                        <div class="font-bold text-blue-400">R-DO</div>
                        <div class="text-[10px] text-slate-400 truncate">Staff DO</div>
                    </a>
                    <a href="{{ route('quick-login', 'R-TRF') }}" class="p-2 rounded-lg bg-slate-900/80 hover:bg-sky-900/40 border border-slate-700 text-left transition-colors">
                        <div class="font-bold text-indigo-400">R-TRF</div>
                        <div class="text-[10px] text-slate-400 truncate">Staff Traffic</div>
                    </a>
                    <a href="{{ route('quick-login', 'R-FLD') }}" class="p-2 rounded-lg bg-slate-900/80 hover:bg-sky-900/40 border border-slate-700 text-left transition-colors">
                        <div class="font-bold text-purple-400">R-FLD</div>
                        <div class="text-[10px] text-slate-400 truncate">Staff Lapangan</div>
                    </a>
                    <a href="{{ route('quick-login', 'R-FIN') }}" class="p-2 rounded-lg bg-slate-900/80 hover:bg-sky-900/40 border border-slate-700 text-left transition-colors">
                        <div class="font-bold text-yellow-400">R-FIN</div>
                        <div class="text-[10px] text-slate-400 truncate">Finance</div>
                    </a>
                    <a href="{{ route('quick-login', 'R-HR') }}" class="p-2 rounded-lg bg-slate-900/80 hover:bg-sky-900/40 border border-slate-700 text-left transition-colors">
                        <div class="font-bold text-pink-400">R-HR</div>
                        <div class="text-[10px] text-slate-400 truncate">HR Staff</div>
                    </a>
                </div>
            </div>
        </div>

        <div class="mt-6 text-center text-xs text-slate-500">
            &copy; {{ date('Y') }} PT Agung Trans Solusindo &bull; Sistem Terlindungi Enkripsi & RBAC
        </div>
    </div>
</body>
</html>
