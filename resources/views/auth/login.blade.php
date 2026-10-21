<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk ke Genie - ERP Import-Export</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --f: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Plus Jakarta Sans", "Helvetica Neue", sans-serif;
            --fd: -apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Plus Jakarta Sans", "Helvetica Neue", sans-serif;
            --r-ctl: 10px;
            --r-card: 18px;
            --r-sheet: 20px;
            --ease: cubic-bezier(.25, .1, .25, 1);
            --ac: #0071E3;
            --bg: #0B1120;
            --sf: rgba(30, 41, 59, 0.7);
            --ln: rgba(255, 255, 255, 0.1);
        }
        body {
            font-family: var(--f);
            background-color: var(--bg);
            color: #F8FAFC;
            -webkit-font-smoothing: antialiased;
        }
        .mk {
            display: grid;
            place-items: center;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(180deg, #2997FF, #0071E3);
            box-shadow: inset 0 0 0 .5px rgba(255, 255, 255, .35), 0 2px 6px rgba(0, 113, 227, .4);
            color: #fff;
            font-family: var(--fd);
            font-weight: 700;
            font-size: 20px;
            line-height: 1;
        }
        .btn-apple {
            border-radius: 980px;
            transition: all .2s var(--ease);
        }
        .btn-apple:hover {
            filter: brightness(1.08);
            transform: scale(1.005);
        }
        .btn-apple:active {
            transform: scale(0.985);
        }
        @media (max-width: 860px) {
            .hero-panel { display: none !important; }
            .login-grid { grid-template-columns: 1fr !important; }
        }

        /* ============================================================== */
        /* CINEMATIC HERO VIDEO BACKGROUND LAYOUT (FULL BLEED)           */
        /* ============================================================== */
        .anim-vessel {
            /* Full-bleed hero video background */
        }
        .anim-crane-trolley {
            will-change: transform;
        }
    </style>
</head>
<body class="h-full min-h-screen flex flex-col justify-center">

    <div class="login-grid min-h-screen grid grid-cols-1 md:grid-cols-2">
        <!-- 1. LEFT HERO PANEL (FULL VIDEO BACKGROUND LAYOUT) -->
        <div class="hero-panel relative bg-slate-950 text-white p-8 sm:p-12 lg:p-16 flex flex-col justify-between border-r border-slate-800/80 overflow-hidden select-none min-h-screen">
            <!-- Full-Bleed Video Background Container -->
            <div class="absolute inset-0 w-full h-full overflow-hidden pointer-events-none anim-vessel anim-crane-trolley">
                <video autoplay loop muted playsinline class="w-full h-full object-cover select-none">
                    <source src="{{ asset('videos/login-hero.mp4') }}" type="video/mp4">
                </video>
                <!-- Cinematic Dark Gradient Overlays for High Contrast & Readability -->
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/45 to-slate-950/75"></div>
                <div class="absolute inset-0 bg-slate-950/20 backdrop-blur-[0.5px]"></div>
            </div>

            <!-- Brand Header (Top) -->
            <div class="relative z-10 flex items-center space-x-3.5">
                <div class="mk">G</div>
                <div>
                    <span class="font-bold text-xl tracking-tight text-white block">Genie</span>
                    <span class="text-xs text-slate-300 font-medium block">ERP Import-Export</span>
                </div>
            </div>

            <!-- Value Proposition (Center / Bottom) -->
            <div class="relative z-10 my-auto py-8 w-full max-w-xl">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-900/75 backdrop-blur-md border border-white/10 text-xs text-slate-200 font-medium mb-4 shadow-xl">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="tracking-wide">Maritime & Air Freight Logistics</span>
                </div>
                <h1 class="text-3xl lg:text-4xl font-extrabold tracking-tight leading-tight text-white mb-3 drop-shadow-md">
                    Satu sistem untuk seluruh proses import-export.
                </h1>
                <p class="text-slate-300 text-sm leading-relaxed drop-shadow">
                    Platform terpadu untuk pengurusan dokumen pabean CEISA 4.0, DO pelayaran, traffic lapangan, dan penagihan klien.
                </p>
            </div>

            <!-- Footer Identitas Perusahaan -->
            <div class="relative z-10 text-xs text-slate-400 pt-4 border-t border-white/10 font-mono flex items-center justify-between">
                <span>PT Agung Trans Solusindo</span>
                <span class="text-[11px] text-slate-500">v2.0</span>
            </div>
        </div>

        <!-- 2. RIGHT FORM PANEL (INTERACTIVE CARD & DEMO SELECTOR) -->
        <div class="bg-slate-900/90 flex flex-col justify-center px-6 sm:px-12 lg:px-20 py-12 relative overflow-y-auto">
            <div class="max-w-md w-full mx-auto">

                <!-- Mobile Brand Header -->
                <div class="md:hidden flex items-center space-x-3 mb-8">
                    <div class="mk">G</div>
                    <div>
                        <span class="font-bold text-lg text-white block">Genie</span>
                        <span class="text-xs text-slate-400">ERP Import-Export</span>
                    </div>
                </div>

                <!-- Judul Form -->
                <div class="mb-8">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Masuk ke Genie</h2>
                    <p class="text-slate-400 text-sm mt-1.5">Gunakan akun resmi perusahaan Anda.</p>
                </div>

                <!-- Flash Alert Messages -->
                @if(session('success'))
                    <div class="mb-6 p-4 rounded-xl bg-emerald-950/80 border border-emerald-500/40 text-emerald-300 text-xs flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('info'))
                    <div class="mb-6 p-4 rounded-xl bg-sky-950/80 border border-sky-500/40 text-sky-300 text-xs flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('info') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-6 p-4 rounded-xl bg-rose-950/90 border border-rose-500/50 text-rose-300 text-xs flex items-start gap-2.5">
                        <svg class="w-4 h-4 flex-shrink-0 text-rose-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <!-- Form Login -->
                <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-300 mb-1.5">Alamat Email Perusahaan *</label>
                        <input id="email" type="email" name="email" value="{{ old('email', app()->isProduction() ? '' : 'admin@agungtrans.co.id') }}" required autofocus
                            autocomplete="username"
                            placeholder="nama@perusahaan.com"
                            class="w-full px-4 py-3 rounded-xl bg-slate-800/90 border border-slate-700/80 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all">
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-300 mb-1.5">Kata Sandi *</label>
                        <div class="relative">
                            <input id="password" type="password" name="password" value="{{ app()->isProduction() ? '' : 'password' }}" required
                                autocomplete="current-password"
                                placeholder="{{ app()->isProduction() ? 'Masukkan kata sandi' : 'password' }}"
                                class="w-full px-4 py-3 pr-24 rounded-xl bg-slate-800/90 border border-slate-700/80 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all">
                            <button type="button" id="togglePasswordBtn"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 px-2.5 py-1 text-xs font-medium text-slate-400 hover:text-white bg-slate-700/60 rounded-md transition-colors">
                                Tampilkan
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs pt-1">
                        <label class="flex items-center space-x-2 cursor-pointer select-none">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-800 border-slate-700 text-sky-500 focus:ring-sky-500/40">
                            <span class="text-slate-300">Ingat saya di perangkat ini</span>
                        </label>
                        <a href="{{ route('password.request') }}" class="text-sky-400 hover:text-sky-300 transition-colors font-medium">
                            Lupa kata sandi?
                        </a>
                    </div>

                    <button type="submit"
                        class="btn-apple w-full py-3.5 px-6 bg-white hover:bg-slate-100 text-slate-950 text-sm font-bold shadow-xs active:scale-95 transition-all mt-2 cursor-pointer">
                        Masuk ke Sistem ERP &rarr;
                    </button>
                </form>

                <!-- Demo Persona Picker (HANYA DITAMPILKAN DI LINGKUNGAN TESTING / DEV, TIDAK UNTUK PRODUCTION) -->
                @if(!app()->isProduction())
                <details class="mt-6 pt-5 border-t border-slate-800/80 group" open>
                    <summary class="cursor-pointer text-xs font-semibold text-slate-400 hover:text-slate-200 transition-colors list-none flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <span>⚡ Persona Akun Demo (Uji Cepat)</span>
                            <span class="px-2 py-0.5 rounded-full bg-sky-950 text-sky-400 border border-sky-800 text-[10px] font-mono">Dev / Testing</span>
                        </span>
                        <span class="text-slate-500 group-open:rotate-180 transition-transform text-xs">&darr;</span>
                    </summary>

                    <div class="mt-3 space-y-2.5">
                        <div class="flex items-center justify-between text-[11px] text-slate-400 font-mono px-0.5">
                            <span>Password default: <code class="text-sky-400 bg-slate-800/90 px-1 py-0.5 rounded">password</code></span>
                            <span>OTP 2FA: <code class="text-amber-400 bg-slate-800/90 px-1 py-0.5 rounded">123456</code></span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            <a href="{{ route('quick-login', 'R-OWN') }}" class="p-2.5 rounded-xl bg-slate-800/70 hover:bg-slate-700/80 border border-slate-700/60 text-left transition-all hover:scale-[1.02]">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-xs text-amber-400">Pimpinan</span>
                                    <span class="text-[9px] px-1 py-0.2 rounded bg-amber-950 text-amber-300 border border-amber-600/40 font-mono">2FA</span>
                                </div>
                                <div class="text-[11px] text-slate-300 mt-0.5 truncate">Soehartono</div>
                            </a>

                            <a href="{{ route('quick-login', 'R-DOC-SPV') }}" class="p-2.5 rounded-xl bg-slate-800/70 hover:bg-slate-700/80 border border-slate-700/60 text-left transition-all hover:scale-[1.02]">
                                <div class="font-bold text-xs text-emerald-400">SPV Dokumen</div>
                                <div class="text-[11px] text-slate-300 mt-0.5 truncate">Rian Hidayat</div>
                            </a>

                            <a href="{{ route('quick-login', 'R-DOC') }}" class="p-2.5 rounded-xl bg-slate-800/70 hover:bg-slate-700/80 border border-slate-700/60 text-left transition-all hover:scale-[1.02]">
                                <div class="font-bold text-xs text-sky-400">Staf Dokumen</div>
                                <div class="text-[11px] text-slate-300 mt-0.5 truncate">Dewi Anggraini</div>
                            </a>

                            <a href="{{ route('quick-login', 'R-DO') }}" class="p-2.5 rounded-xl bg-slate-800/70 hover:bg-slate-700/80 border border-slate-700/60 text-left transition-all hover:scale-[1.02]">
                                <div class="font-bold text-xs text-indigo-400">Pelayaran (DO)</div>
                                <div class="text-[11px] text-slate-300 mt-0.5 truncate">Dimas Pratama</div>
                            </a>

                            <a href="{{ route('quick-login', 'R-TRF') }}" class="p-2.5 rounded-xl bg-slate-800/70 hover:bg-slate-700/80 border border-slate-700/60 text-left transition-all hover:scale-[1.02]">
                                <div class="font-bold text-xs text-rose-400">Traffic Lapangan</div>
                                <div class="text-[11px] text-slate-300 mt-0.5 truncate">Budi Hartanto</div>
                            </a>

                            <a href="{{ route('quick-login', 'R-FIN') }}" class="p-2.5 rounded-xl bg-slate-800/70 hover:bg-slate-700/80 border border-slate-700/60 text-left transition-all hover:scale-[1.02]">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-xs text-yellow-400">Keuangan</span>
                                    <span class="text-[9px] px-1 py-0.2 rounded bg-amber-950 text-amber-300 border border-amber-600/40 font-mono">2FA</span>
                                </div>
                                <div class="text-[11px] text-slate-300 mt-0.5 truncate">Sinta Wulandari</div>
                            </a>
                        </div>
                    </div>
                </details>
                @endif

            </div>
        </div>
    </div>

    <!-- Toggle Password Visibility & Realistic Port Loading Simulation JS -->
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
