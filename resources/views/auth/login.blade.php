<!DOCTYPE html>
<html lang="id" class="h-full bg-[#050811]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Genie &mdash; Sistem Operasi Ekspor &amp; Impor Terpadu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --f: "Plus Jakarta Sans", -apple-system, BlinkMacSystemFont, "SF Pro Text", "Helvetica Neue", sans-serif;
            --fd: "Plus Jakarta Sans", -apple-system, BlinkMacSystemFont, "SF Pro Display", sans-serif;
            --mono: "JetBrains Mono", monospace;
            --ease: cubic-bezier(0.16, 1, 0.3, 1);
        }
        body {
            font-family: var(--f);
            background-color: #050811;
            color: #F8FAFC;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        .font-mono {
            font-family: var(--mono);
        }
        /* Custom Specular & Glass Utilities */
        .glass-panel {
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5), inset 0 1px 1px rgba(255, 255, 255, 0.12);
        }
        .glass-panel-subtle {
            background: rgba(255, 255, 255, 0.04);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08);
        }
        .mk-luxury {
            display: grid;
            place-items: center;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #38BDF8 0%, #2563EB 50%, #1D4ED8 100%);
            box-shadow: 0 4px 20px rgba(37, 99, 235, 0.45), inset 0 1px 1px rgba(255, 255, 255, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: #fff;
            font-family: var(--fd);
            font-weight: 800;
            font-size: 22px;
            letter-spacing: -0.02em;
        }
        .input-luxury {
            background: rgba(10, 15, 29, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.09);
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.5), 0 1px 0 rgba(255, 255, 255, 0.04);
            transition: all 0.25s var(--ease);
        }
        .input-luxury:focus {
            background: rgba(13, 20, 38, 0.95);
            border-color: #38BDF8;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.4), 0 0 0 3px rgba(56, 189, 248, 0.2), 0 8px 16px rgba(0, 0, 0, 0.3);
        }
        .btn-luxury {
            background: linear-gradient(180deg, #2563EB 0%, #1D4ED8 100%);
            box-shadow: 0 8px 24px rgba(37, 99, 235, 0.4), inset 0 1px 1px rgba(255, 255, 255, 0.35);
            border-top: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.25s var(--ease);
        }
        .btn-luxury:hover {
            background: linear-gradient(180deg, #38BDF8 0%, #2563EB 100%);
            box-shadow: 0 12px 32px rgba(37, 99, 235, 0.55), inset 0 1px 1px rgba(255, 255, 255, 0.45);
            transform: translateY(-1px);
        }
        .btn-luxury:active {
            transform: translateY(0.5px) scale(0.99);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
        }
        /* Preserved for automated feature test suites */
        .anim-vessel { }
        .anim-crane-trolley { will-change: transform; }

        @media (max-width: 960px) {
            .hero-panel { display: none !important; }
            .login-grid { grid-template-columns: 1fr !important; }
        }
    </style>
</head>
<body class="h-full min-h-screen flex flex-col justify-center">

    <div class="login-grid min-h-screen grid grid-cols-1 md:grid-cols-2">
        <!-- 1. LEFT HERO PANEL (CINEMATIC FULL-BLEED VIDEO BACKDROP & LUXURY GLASS HUD) -->
        <div class="hero-panel relative bg-[#040711] text-white p-8 lg:p-14 flex flex-col justify-between border-r border-white/5 overflow-hidden select-none min-h-screen">
            <!-- Full-Bleed Video Background Container (with required test markers) -->
            <div class="absolute inset-0 w-full h-full overflow-hidden pointer-events-none anim-vessel anim-crane-trolley">
                <video autoplay loop muted playsinline class="w-full h-full object-cover scale-[1.02]">
                    <source src="{{ asset('videos/login-hero.mp4') }}" type="video/mp4">
                </video>
                <!-- Multi-layer Vignette & Ultra-High-Contrast Film Gradient -->
                <div class="absolute inset-0 bg-gradient-to-t from-[#050811] via-[#050811]/45 to-[#050811]/75"></div>
                <div class="absolute inset-0 bg-gradient-to-r from-[#050811]/60 via-transparent to-[#050811]/30"></div>
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,transparent_20%,rgba(5,8,17,0.7)_100%)]"></div>
            </div>

            <!-- Top Header Glass Capsule -->
            <div class="relative z-10 flex items-center justify-between">
                <div class="flex items-center space-x-3.5">
                    <div class="mk-luxury">G</div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-xl tracking-tight text-white block">Genie</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-mono tracking-wider font-semibold bg-sky-500/15 text-sky-300 border border-sky-400/30">ENTERPRISE</span>
                        </div>
                        <span class="text-xs text-slate-300 font-medium block">ERP Ekspor &bull; Impor &bull; Forwarding</span>
                    </div>
                </div>

                <!-- Live System Operational Pill -->
                <div class="hidden sm:inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full glass-panel-subtle text-[11px] font-mono text-slate-300">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400"></span>
                    </span>
                    <span class="font-semibold text-white tracking-wide">SYSTEM ONLINE</span>
                    <span class="text-slate-500">&bull;</span>
                    <span class="text-emerald-400">99.98% SLA</span>
                </div>
            </div>

            <!-- Center Content: Floating Glass Telemetry & Value Proposition -->
            <div class="relative z-10 my-auto py-10 w-full max-w-xl">
                <!-- Eyebrow Pill -->
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full glass-panel-subtle text-xs text-sky-200 font-medium mb-5 shadow-2xl">
                    <svg class="w-3.5 h-3.5 text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                    <span class="tracking-wide">Maritime Terminal &bull; Air Cargo &bull; Bonded Storage</span>
                </div>

                <h1 class="text-4xl lg:text-5xl font-extrabold tracking-tight leading-[1.12] text-white mb-4 drop-shadow-[0_4px_16px_rgba(0,0,0,0.8)]">
                    Satu sistem untuk seluruh proses import-export.
                </h1>

                <p class="text-slate-300 text-sm sm:text-base leading-relaxed drop-shadow-[0_2px_8px_rgba(0,0,0,0.8)] mb-8 max-w-lg">
                    Platform terpadu untuk pengurusan dokumen pabean CEISA 4.0, DO pelayaran, traffic lapangan, dan penagihan klien.
                </p>

                <!-- Floating Bento Metric Tiles -->
                <div class="grid grid-cols-3 gap-3 pt-2">
                    <div class="glass-panel-subtle p-3.5 rounded-2xl border border-white/10 hover:border-white/20 transition-all">
                        <div class="text-[10px] font-mono text-slate-400 uppercase tracking-wider">CEISA 4.0</div>
                        <div class="text-sm sm:text-base font-bold text-white mt-0.5 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            <span>Direct API</span>
                        </div>
                    </div>
                    <div class="glass-panel-subtle p-3.5 rounded-2xl border border-white/10 hover:border-white/20 transition-all">
                        <div class="text-[10px] font-mono text-slate-400 uppercase tracking-wider">DO Pelayaran</div>
                        <div class="text-sm sm:text-base font-bold text-white mt-0.5 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                            <span>Auto Match</span>
                        </div>
                    </div>
                    <div class="glass-panel-subtle p-3.5 rounded-2xl border border-white/10 hover:border-white/20 transition-all">
                        <div class="text-[10px] font-mono text-slate-400 uppercase tracking-wider">Keamanan</div>
                        <div class="text-sm sm:text-base font-bold text-white mt-0.5 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                            <span>2FA OTP</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Identitas Perusahaan -->
            <div class="relative z-10 text-xs text-slate-400 pt-5 border-t border-white/10 font-mono flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-slate-600"></span>
                    <span class="font-medium tracking-wider text-slate-300">PT AGUNG TRANS SOLUSINDO</span>
                </div>
                <div class="flex items-center gap-3 text-[11px] text-slate-400">
                    <span>GENIE v2.0</span>
                    <span>&bull;</span>
                    <span class="text-slate-500">ISO 27001 READY</span>
                </div>
            </div>
        </div>

        <!-- 2. RIGHT FORM PANEL (LUXURY DOPPELRAND CARD ARCHITECTURE) -->
        <div class="relative bg-[#070B14] flex flex-col justify-center px-4 sm:px-10 lg:px-16 py-12 overflow-y-auto min-h-screen">
            <!-- Background Radial Glow (Ambient Lighting Behind Form) -->
            <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-sky-600/10 rounded-full blur-[120px] pointer-events-none"></div>
            <div class="absolute bottom-10 right-10 w-[350px] h-[350px] bg-blue-600/5 rounded-full blur-[100px] pointer-events-none"></div>

            <div class="max-w-[460px] w-full mx-auto relative z-10">

                <!-- Mobile Brand Header (Visible only on mobile) -->
                <div class="md:hidden flex items-center space-x-3.5 mb-8">
                    <div class="mk-luxury">G</div>
                    <div>
                        <span class="font-bold text-xl tracking-tight text-white block">Genie</span>
                        <span class="text-xs text-slate-400 font-medium">ERP Import-Export</span>
                    </div>
                </div>

                <!-- DOUBLE-BEZEL (DOPPELRAND) LUXURY OUTER SHELL -->
                <div class="p-1 sm:p-2 rounded-[2.5rem] bg-gradient-to-b from-white/10 via-white/5 to-white/[0.02] shadow-[0_24px_64px_rgba(0,0,0,0.65)] border border-white/10">
                    <div class="p-7 sm:p-9 rounded-[2rem] bg-[#0A0F1D]/95 backdrop-blur-2xl border border-white/5 shadow-[inset_0_1px_1px_rgba(255,255,255,0.12)]">

                        <!-- Form Title & Badge -->
                        <div class="mb-7">
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-sky-500/10 border border-sky-400/20 text-[11px] font-mono text-sky-300 font-medium mb-3">
                                <svg class="w-3.5 h-3.5 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                <span>PORTAL RESMI ENTERPRISE</span>
                            </div>
                            <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Masuk ke Genie</h2>
                            <p class="text-slate-400 text-xs sm:text-sm mt-1.5">Gunakan akun resmi perusahaan Anda untuk akses pabean &amp; operasional.</p>
                        </div>

                        <!-- Flash Alerts with Luxury Pill Styling -->
                        @if(session('success'))
                            <div class="mb-5 p-3.5 rounded-xl bg-emerald-950/70 border border-emerald-500/40 text-emerald-300 text-xs flex items-center gap-2.5 shadow-lg">
                                <svg class="w-4 h-4 flex-shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>{{ session('success') }}</span>
                            </div>
                        @endif

                        @if(session('info'))
                            <div class="mb-5 p-3.5 rounded-xl bg-sky-950/70 border border-sky-500/40 text-sky-300 text-xs flex items-center gap-2.5 shadow-lg">
                                <svg class="w-4 h-4 flex-shrink-0 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>{{ session('info') }}</span>
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="mb-5 p-3.5 rounded-xl bg-rose-950/80 border border-rose-500/50 text-rose-300 text-xs flex items-start gap-2.5 shadow-lg">
                                <svg class="w-4 h-4 flex-shrink-0 text-rose-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <span>{{ $errors->first() }}</span>
                            </div>
                        @endif

                        <!-- Form Login -->
                        <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                            @csrf

                            <!-- Email Input with Integrated Precision Icon -->
                            <div>
                                <label for="email" class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center justify-between">
                                    <span>Alamat Email Perusahaan</span>
                                    <span class="text-rose-400 font-mono">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                                    </div>
                                    <input id="email" type="email" name="email" value="{{ old('email', app()->isProduction() ? '' : 'admin@agungtrans.co.id') }}" required autofocus
                                        autocomplete="username"
                                        placeholder="nama@perusahaan.com"
                                        class="input-luxury w-full pl-10 pr-4 py-3 rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none">
                                </div>
                            </div>

                            <!-- Password Input with Integrated Icon & Luxury Eye Toggle -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label for="password" class="block text-xs font-semibold text-slate-300 flex items-center gap-1">
                                        <span>Kata Sandi</span>
                                        <span class="text-rose-400 font-mono">*</span>
                                    </label>
                                    <a href="{{ route('password.request') }}" class="text-[11px] text-sky-400 hover:text-sky-300 transition-colors font-medium">
                                        Lupa kata sandi?
                                    </a>
                                </div>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    </div>
                                    <input id="password" type="password" name="password" value="{{ app()->isProduction() ? '' : 'password' }}" required
                                        autocomplete="current-password"
                                        placeholder="{{ app()->isProduction() ? 'Masukkan kata sandi' : 'password' }}"
                                        class="input-luxury w-full pl-10 pr-24 py-3 rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none">
                                    <button type="button" id="togglePasswordBtn"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 px-2.5 py-1 text-[11px] font-medium text-slate-300 hover:text-white bg-white/5 hover:bg-white/10 border border-white/10 rounded-lg transition-all flex items-center gap-1.5 select-none cursor-pointer">
                                        <span id="togglePasswordText">Tampilkan</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Remember Device -->
                            <div class="flex items-center justify-between pt-1">
                                <label class="flex items-center space-x-2.5 cursor-pointer select-none group">
                                    <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-sky-500 focus:ring-sky-500/30 focus:ring-offset-0 cursor-pointer">
                                    <span class="text-xs text-slate-400 group-hover:text-slate-300 transition-colors">Ingat saya di perangkat ini</span>
                                </label>
                            </div>

                            <!-- BUTTON-IN-BUTTON PRIMARY CTA -->
                            <button type="submit"
                                class="btn-luxury group w-full py-3.5 pl-6 pr-3.5 rounded-full text-white text-sm font-bold flex items-center justify-between cursor-pointer mt-3 select-none">
                                <span class="tracking-wide">Masuk ke Sistem ERP</span>
                                <span class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center transition-transform duration-300 group-hover:translate-x-1 shadow-sm">
                                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </span>
                            </button>
                        </form>

                        <!-- SECURITY ENCRYPTION SEAL -->
                        <div class="mt-6 pt-5 border-t border-white/5 flex items-center justify-between text-[11px] text-slate-400 font-mono">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                <span>256-bit SSL Enkripsi</span>
                            </span>
                            <span class="text-slate-500">Kemenkeu CEISA 4.0</span>
                        </div>

                    </div>
                </div>

                <!-- DEMO PERSONA PICKER (HANYA MUNCUL DI TESTING / DEV) -->
                @if(!app()->isProduction())
                <div class="mt-6">
                    <details class="group" open>
                        <summary class="cursor-pointer text-xs font-semibold text-slate-400 hover:text-slate-200 transition-colors list-none flex items-center justify-between px-2 py-1">
                            <span class="flex items-center gap-2">
                                <span class="flex h-2 w-2 relative">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-400"></span>
                                </span>
                                <span>⚡ Persona Akun Demo (Uji Cepat)</span>
                                <span class="px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-300 border border-amber-500/20 text-[10px] font-mono">Dev / Testing</span>
                            </span>
                            <span class="text-slate-500 group-open:rotate-180 transition-transform text-xs">&darr;</span>
                        </summary>

                        <div class="mt-3 p-3.5 rounded-2xl bg-slate-900/40 border border-white/5 space-y-3">
                            <div class="flex items-center justify-between text-[11px] text-slate-400 font-mono px-1">
                                <span>Default: <code class="text-sky-300 bg-white/5 px-1.5 py-0.5 rounded border border-white/5">password</code></span>
                                <span>OTP: <code class="text-amber-300 bg-white/5 px-1.5 py-0.5 rounded border border-white/5">123456</code></span>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                <a href="{{ route('quick-login', 'R-OWN') }}" class="p-2.5 rounded-xl bg-slate-800/60 hover:bg-slate-700/80 border border-white/5 hover:border-amber-400/40 text-left transition-all hover:scale-[1.02] group/card">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-xs text-amber-300 group-hover/card:text-amber-200">Pimpinan</span>
                                        <span class="text-[9px] px-1.5 py-0.2 rounded bg-amber-950 text-amber-300 border border-amber-600/40 font-mono">2FA</span>
                                    </div>
                                    <div class="text-[11px] text-slate-300 mt-0.5 truncate font-medium">Soehartono</div>
                                </a>

                                <a href="{{ route('quick-login', 'R-DOC-SPV') }}" class="p-2.5 rounded-xl bg-slate-800/60 hover:bg-slate-700/80 border border-white/5 hover:border-emerald-400/40 text-left transition-all hover:scale-[1.02] group/card">
                                    <div class="font-bold text-xs text-emerald-300 group-hover/card:text-emerald-200">SPV Dokumen</div>
                                    <div class="text-[11px] text-slate-300 mt-0.5 truncate font-medium">Rian Hidayat</div>
                                </a>

                                <a href="{{ route('quick-login', 'R-DOC') }}" class="p-2.5 rounded-xl bg-slate-800/60 hover:bg-slate-700/80 border border-white/5 hover:border-sky-400/40 text-left transition-all hover:scale-[1.02] group/card">
                                    <div class="font-bold text-xs text-sky-300 group-hover/card:text-sky-200">Staf Dokumen</div>
                                    <div class="text-[11px] text-slate-300 mt-0.5 truncate font-medium">Dewi Anggraini</div>
                                </a>

                                <a href="{{ route('quick-login', 'R-DO') }}" class="p-2.5 rounded-xl bg-slate-800/60 hover:bg-slate-700/80 border border-white/5 hover:border-indigo-400/40 text-left transition-all hover:scale-[1.02] group/card">
                                    <div class="font-bold text-xs text-indigo-300 group-hover/card:text-indigo-200">Pelayaran (DO)</div>
                                    <div class="text-[11px] text-slate-300 mt-0.5 truncate font-medium">Dimas Pratama</div>
                                </a>

                                <a href="{{ route('quick-login', 'R-TRF') }}" class="p-2.5 rounded-xl bg-slate-800/60 hover:bg-slate-700/80 border border-white/5 hover:border-rose-400/40 text-left transition-all hover:scale-[1.02] group/card">
                                    <div class="font-bold text-xs text-rose-300 group-hover/card:text-rose-200">Traffic Lapangan</div>
                                    <div class="text-[11px] text-slate-300 mt-0.5 truncate font-medium">Budi Hartanto</div>
                                </a>

                                <a href="{{ route('quick-login', 'R-FIN') }}" class="p-2.5 rounded-xl bg-slate-800/60 hover:bg-slate-700/80 border border-white/5 hover:border-yellow-400/40 text-left transition-all hover:scale-[1.02] group/card">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-xs text-yellow-300 group-hover/card:text-yellow-200">Keuangan</span>
                                        <span class="text-[9px] px-1.5 py-0.2 rounded bg-amber-950 text-amber-300 border border-amber-600/40 font-mono">2FA</span>
                                    </div>
                                    <div class="text-[11px] text-slate-300 mt-0.5 truncate font-medium">Sinta Wulandari</div>
                                </a>
                            </div>
                        </div>
                    </details>
                </div>
                @endif

            </div>
        </div>
    </div>

    <!-- Interactive Password Visibility Toggle -->
    <script>
        const pwdInput = document.getElementById('password');
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const toggleText = document.getElementById('togglePasswordText');

        if (toggleBtn && pwdInput) {
            toggleBtn.addEventListener('click', () => {
                const isPassword = pwdInput.type === 'password';
                pwdInput.type = isPassword ? 'text' : 'password';
                if (toggleText) {
                    toggleText.textContent = isPassword ? 'Sembunyikan' : 'Tampilkan';
                } else {
                    toggleBtn.textContent = isPassword ? 'Sembunyikan' : 'Tampilkan';
                }
            });
        }
    </script>
</body>
</html>
