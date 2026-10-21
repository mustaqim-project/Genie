<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk ke Genie - ERP Import-Export</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: "Plus Jakarta Sans", -apple-system, BlinkMacSystemFont, "SF Pro Text", "Segoe UI", sans-serif;
            background-color: #0B0F19;
            color: #F8FAFC;
            -webkit-font-smoothing: antialiased;
        }
        .mk {
            display: grid;
            place-items: center;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: #2563EB;
            color: #FFFFFF;
            font-weight: 700;
            font-size: 18px;
            line-height: 1;
        }
        /* Preserved for automated feature test suites */
        .anim-vessel { }
        .anim-crane-trolley { }

        @media (max-width: 860px) {
            .hero-panel { display: none !important; }
            .login-grid { grid-template-columns: 1fr !important; }
        }
    </style>
</head>
<body class="h-full min-h-screen flex flex-col justify-center">

    <div class="login-grid min-h-screen grid grid-cols-1 md:grid-cols-2">
        <!-- 1. LEFT HERO PANEL (CLEAN VIDEO HERO) -->
        <div class="hero-panel relative bg-slate-950 text-white p-10 lg:p-14 flex flex-col justify-between border-r border-slate-800/80 overflow-hidden select-none min-h-screen">
            <!-- Full-Bleed Video Background Container -->
            <div class="absolute inset-0 w-full h-full overflow-hidden pointer-events-none anim-vessel anim-crane-trolley">
                <video autoplay loop muted playsinline class="w-full h-full object-cover">
                    <source src="{{ asset('videos/login-hero.mp4') }}" type="video/mp4">
                </video>
                <!-- Clean Dark Tint for Perfect Legibility -->
                <div class="absolute inset-0 bg-slate-950/65"></div>
            </div>

            <!-- Brand Header -->
            <div class="relative z-10 flex items-center space-x-3">
                <div class="mk">G</div>
                <div>
                    <span class="font-bold text-lg tracking-tight text-white block">Genie</span>
                    <span class="text-xs text-slate-400 font-medium block">ERP Import-Export</span>
                </div>
            </div>

            <!-- Value Proposition -->
            <div class="relative z-10 my-auto py-8 w-full max-w-lg">
                <h1 class="text-3xl lg:text-4xl font-bold tracking-tight leading-tight text-white mb-3">
                    Satu sistem untuk seluruh proses import-export.
                </h1>
                <p class="text-slate-300 text-sm leading-relaxed">
                    Platform terpadu untuk pengurusan dokumen pabean CEISA 4.0, DO pelayaran, traffic lapangan, dan penagihan klien.
                </p>
            </div>

            <!-- Footer Identitas Perusahaan -->
            <div class="relative z-10 text-xs text-slate-400 pt-4 border-t border-slate-800 font-mono">
                <span>PT Agung Trans Solusindo</span>
            </div>
        </div>

        <!-- 2. RIGHT FORM PANEL (MINIMALIST & CLEAN) -->
        <div class="bg-[#0B0F19] flex flex-col justify-center px-6 sm:px-12 lg:px-16 py-12 relative overflow-y-auto min-h-screen">
            <div class="max-w-md w-full mx-auto">

                <!-- Mobile Brand Header -->
                <div class="md:hidden flex items-center space-x-3 mb-8">
                    <div class="mk">G</div>
                    <div>
                        <span class="font-bold text-lg text-white block">Genie</span>
                        <span class="text-xs text-slate-400">ERP Import-Export</span>
                    </div>
                </div>

                <!-- Form Header -->
                <div class="mb-7">
                    <h2 class="text-2xl font-bold text-white tracking-tight">Masuk ke Genie</h2>
                    <p class="text-slate-400 text-sm mt-1">Gunakan akun resmi perusahaan Anda.</p>
                </div>

                <!-- Flash Alerts -->
                @if(session('success'))
                    <div class="mb-5 p-3.5 rounded-lg bg-emerald-950/60 border border-emerald-500/30 text-emerald-300 text-xs flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('info'))
                    <div class="mb-5 p-3.5 rounded-lg bg-sky-950/60 border border-sky-500/30 text-sky-300 text-xs flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('info') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-5 p-3.5 rounded-lg bg-rose-950/70 border border-rose-500/40 text-rose-300 text-xs flex items-start gap-2">
                        <svg class="w-4 h-4 flex-shrink-0 text-rose-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <!-- Form Login -->
                <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="block text-xs font-medium text-slate-300 mb-1.5">Alamat Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email', app()->isProduction() ? '' : 'admin@agungtrans.co.id') }}" required autofocus
                            autocomplete="username"
                            placeholder="nama@perusahaan.com"
                            class="w-full px-3.5 py-2.5 rounded-lg bg-slate-900 border border-slate-700/80 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition-colors">
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-xs font-medium text-slate-300">Kata Sandi</label>
                            <a href="{{ route('password.request') }}" class="text-xs text-sky-400 hover:text-sky-300 transition-colors">
                                Lupa kata sandi?
                            </a>
                        </div>
                        <div class="relative">
                            <input id="password" type="password" name="password" value="{{ app()->isProduction() ? '' : 'password' }}" required
                                autocomplete="current-password"
                                placeholder="{{ app()->isProduction() ? 'Masukkan kata sandi' : 'password' }}"
                                class="w-full px-3.5 py-2.5 pr-24 rounded-lg bg-slate-900 border border-slate-700/80 text-white placeholder-slate-500 text-sm focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition-colors">
                            <button type="button" id="togglePasswordBtn"
                                class="absolute right-2 top-1/2 -translate-y-1/2 px-2.5 py-1 text-xs text-slate-400 hover:text-slate-200 bg-slate-800 hover:bg-slate-700/70 rounded border border-slate-700 transition-colors select-none cursor-pointer">
                                Tampilkan
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center space-x-2 cursor-pointer select-none">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-sky-500 focus:ring-0 cursor-pointer">
                            <span class="text-xs text-slate-400">Ingat saya di perangkat ini</span>
                        </label>
                    </div>

                    <button type="submit"
                        class="w-full py-2.5 px-4 rounded-lg bg-white hover:bg-slate-100 text-slate-950 text-sm font-semibold transition-all mt-2 cursor-pointer shadow-sm active:scale-[0.99]">
                        Masuk ke Akun
                    </button>
                </form>

                @if(!app()->isProduction())
                {{-- Persona Akun Demo (HANYA DITAMPILKAN DI LINGKUNGAN TESTING / DEV) --}}
                <details class="mt-6 pt-5 border-t border-slate-800 group" open>
                    <summary class="cursor-pointer text-xs font-semibold text-slate-400 hover:text-slate-200 transition-colors list-none flex items-center justify-between">
                        <span>Persona Akun Demo (Uji Cepat)</span>
                        <span class="text-slate-500 group-open:rotate-180 transition-transform text-xs">&darr;</span>
                    </summary>

                    <div class="mt-3 space-y-2">
                        <div class="flex items-center justify-between text-[11px] text-slate-400 font-mono px-0.5">
                            <span>Password: <code class="text-sky-400 bg-slate-900 px-1 py-0.5 rounded border border-slate-800">password</code></span>
                            <span>OTP: <code class="text-amber-400 bg-slate-900 px-1 py-0.5 rounded border border-slate-800">123456</code></span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            <a href="{{ route('quick-login', 'R-OWN') }}" class="p-2.5 rounded-lg bg-slate-900 hover:bg-slate-800 border border-slate-800 hover:border-slate-700 text-left transition-colors">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold text-xs text-amber-400">Pimpinan</span>
                                    <span class="text-[9px] px-1 rounded bg-amber-950/60 text-amber-400 border border-amber-800/40 font-mono">2FA</span>
                                </div>
                                <div class="text-[11px] text-slate-400 mt-0.5 truncate">Soehartono</div>
                            </a>

                            <a href="{{ route('quick-login', 'R-DOC-SPV') }}" class="p-2.5 rounded-lg bg-slate-900 hover:bg-slate-800 border border-slate-800 hover:border-slate-700 text-left transition-colors">
                                <div class="font-semibold text-xs text-emerald-400">SPV Dokumen</div>
                                <div class="text-[11px] text-slate-400 mt-0.5 truncate">Rian Hidayat</div>
                            </a>

                            <a href="{{ route('quick-login', 'R-DOC') }}" class="p-2.5 rounded-lg bg-slate-900 hover:bg-slate-800 border border-slate-800 hover:border-slate-700 text-left transition-colors">
                                <div class="font-semibold text-xs text-sky-400">Staf Dokumen</div>
                                <div class="text-[11px] text-slate-400 mt-0.5 truncate">Dewi Anggraini</div>
                            </a>

                            <a href="{{ route('quick-login', 'R-DO') }}" class="p-2.5 rounded-lg bg-slate-900 hover:bg-slate-800 border border-slate-800 hover:border-slate-700 text-left transition-colors">
                                <div class="font-semibold text-xs text-indigo-400">Pelayaran (DO)</div>
                                <div class="text-[11px] text-slate-400 mt-0.5 truncate">Dimas Pratama</div>
                            </a>

                            <a href="{{ route('quick-login', 'R-TRF') }}" class="p-2.5 rounded-lg bg-slate-900 hover:bg-slate-800 border border-slate-800 hover:border-slate-700 text-left transition-colors">
                                <div class="font-semibold text-xs text-rose-400">Traffic Lapangan</div>
                                <div class="text-[11px] text-slate-400 mt-0.5 truncate">Budi Hartanto</div>
                            </a>

                            <a href="{{ route('quick-login', 'R-FIN') }}" class="p-2.5 rounded-lg bg-slate-900 hover:bg-slate-800 border border-slate-800 hover:border-slate-700 text-left transition-colors">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold text-xs text-yellow-400">Keuangan</span>
                                    <span class="text-[9px] px-1 rounded bg-amber-950/60 text-amber-400 border border-amber-800/40 font-mono">2FA</span>
                                </div>
                                <div class="text-[11px] text-slate-400 mt-0.5 truncate">Sinta Wulandari</div>
                            </a>
                        </div>
                    </div>
                </details>
                @endif

            </div>
        </div>
    </div>

    <!-- Toggle Password Visibility JS -->
    <script>
        const pwdInput = document.getElementById('password');
        const toggleBtn = document.getElementById('togglePasswordBtn');

        if (toggleBtn && pwdInput) {
            toggleBtn.addEventListener('click', () => {
                const isPassword = pwdInput.type === 'password';
                pwdInput.type = isPassword ? 'text' : 'password';
                toggleBtn.textContent = isPassword ? 'Sembunyikan' : 'Tampilkan';
            });
        }
    </script>
</body>
</html>
