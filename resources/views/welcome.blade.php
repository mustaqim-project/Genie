<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PATS_CEISA ERP — PT Agung Trans Solusindo | Portal Kepabeanan & Logistik</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
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
                    }
                }
            }
        }
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="h-full text-slate-100 flex flex-col justify-between bg-gradient-to-br from-slate-950 via-slate-900 to-sky-950 min-h-screen">

    <!-- Topbar Portal -->
    <header class="border-b border-slate-800/80 bg-slate-950/60 backdrop-blur-md sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-700 via-brand-600 to-sky-400 flex items-center justify-center text-white font-extrabold shadow-lg shadow-brand-500/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center space-x-1.5">
                        <span class="font-extrabold text-base tracking-tight text-white">PATS<span class="text-brand-400">_CEISA</span></span>
                        <span class="px-1.5 py-0.5 text-[9px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 rounded-md">H2H DJBC</span>
                    </div>
                    <p class="text-[10px] text-slate-400 font-medium">PT Agung Trans Solusindo</p>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-xl bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold shadow-lg shadow-brand-500/20 transition-all">
                        Masuk ke Dashboard &rarr;
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-semibold transition-all">
                        Login Karyawan
                    </a>
                    <a href="{{ route('quick-login', 'R-ADM') }}" class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold shadow-lg shadow-brand-600/30 transition-all">
                        Demo Admin (1-Klik) &rarr;
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Hero -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 flex-1 flex flex-col justify-center">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full bg-slate-800/80 border border-slate-700/80 text-brand-300 text-xs font-medium mb-6">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Host-to-Host Gateway Aktif &bull; Terhubung ke CEISA 4.0 DJBC</span>
            </div>
            
            <h1 class="text-4xl sm:text-5xl font-black text-white tracking-tight leading-tight">
                ERP Kepabeanan & Logistik Terpadu <span class="bg-gradient-to-r from-sky-400 via-brand-400 to-teal-300 bg-clip-text text-transparent">PATS_CEISA</span>
            </h1>
            
            <p class="mt-4 text-base text-slate-300 leading-relaxed">
                Platform operasional internal PT Agung Trans Solusindo untuk automasi pengurusan dokumen BC 2.0 (PIB), BC 3.0 (PEB), penebusan Delivery Order (DO), penjaluran behandle lapangan, hingga penerbitan invoice P19 dan jurnal akuntansi.
            </p>

            <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                <a href="{{ route('login') }}" class="px-6 py-3.5 rounded-2xl bg-gradient-to-r from-brand-600 via-brand-500 to-sky-500 hover:from-brand-500 hover:to-sky-400 text-white font-bold text-sm shadow-xl shadow-brand-500/25 transition-all hover:scale-105">
                    Buka Admin Panel ERP &rarr;
                </a>
                <a href="{{ route('quick-login', 'R-ADM') }}" class="px-6 py-3.5 rounded-2xl bg-slate-800/90 hover:bg-slate-800 text-slate-200 border border-slate-700 font-bold text-sm transition-all hover:scale-105">
                    Simulasi 12 Peran (RBAC)
                </a>
            </div>
        </div>

        <!-- 8 Pilar Modul ERP Bento Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- 1. Dashboard -->
            <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 hover:border-slate-700 transition-all">
                <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center font-bold mb-3">
                    01
                </div>
                <h3 class="font-bold text-white text-sm">Dashboard & KPI</h3>
                <p class="text-xs text-slate-400 mt-1">Monitoring throughput arus impor/ekspor, bea masuk/keluar, dan status SPPB/NPE realtime.</p>
            </div>

            <!-- 2. Core Bisnis: Tim Document -->
            <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 hover:border-slate-700 transition-all">
                <div class="w-10 h-10 rounded-xl bg-sky-500/10 text-sky-400 flex items-center justify-center font-bold mb-3">
                    02
                </div>
                <h3 class="font-bold text-white text-sm">Tim Document</h3>
                <p class="text-xs text-slate-400 mt-1">Lifecycle P01-P19, verifikasi 9 berkas, deteksi HS code AI, submit BC 2.0 & BC 3.0 hingga billing MPN.</p>
            </div>

            <!-- 3. Core Bisnis: Tim DO -->
            <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 hover:border-slate-700 transition-all">
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold mb-3">
                    03
                </div>
                <h3 class="font-bold text-white text-sm">Tim Delivery Order (DO)</h3>
                <p class="text-xs text-slate-400 mt-1">Otomasi email invoice pelayaran, monitoring free time peti kemas, dan rilis DO pelabuhan.</p>
            </div>

            <!-- 4. Core Bisnis: Tim Traffic -->
            <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 hover:border-slate-700 transition-all">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center font-bold mb-3">
                    04
                </div>
                <h3 class="font-bold text-white text-sm">Tim Traffic & Lapangan</h3>
                <p class="text-xs text-slate-400 mt-1">Penjaluran pabean (Merah/Kuning/Hijau), koordinasi behandle fisik, Gate Out TPS, dan POD.</p>
            </div>

            <!-- 5. HRM -->
            <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 hover:border-slate-700 transition-all">
                <div class="w-10 h-10 rounded-xl bg-pink-500/10 text-pink-400 flex items-center justify-center font-bold mb-3">
                    05
                </div>
                <h3 class="font-bold text-white text-sm">HRM & Penggajian</h3>
                <p class="text-xs text-slate-400 mt-1">Manajemen pegawai operasional, absensi tim lapangan pelabuhan, dan penggajian bulanan.</p>
            </div>

            <!-- 6. CRM & Penawaran -->
            <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 hover:border-slate-700 transition-all">
                <div class="w-10 h-10 rounded-xl bg-teal-500/10 text-teal-400 flex items-center justify-center font-bold mb-3">
                    06
                </div>
                <h3 class="font-bold text-white text-sm">CRM & Quotation</h3>
                <p class="text-xs text-slate-400 mt-1">Database importir/eksportir, integrasi WhatsApp WABA, dan pembuatan surat penawaran harga resmi.</p>
            </div>

            <!-- 7. Finance & Accounting -->
            <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 hover:border-slate-700 transition-all">
                <div class="w-10 h-10 rounded-xl bg-green-500/10 text-green-400 flex items-center justify-center font-bold mb-3">
                    07
                </div>
                <h3 class="font-bold text-white text-sm">Finance & Accounting</h3>
                <p class="text-xs text-slate-400 mt-1">Invoicing klien (P19), klaim reimbursement talangan pabean, jurnal berpasangan, dan neraca.</p>
            </div>

            <!-- 8. AI Agent & Setting -->
            <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 hover:border-slate-700 transition-all">
                <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center font-bold mb-3">
                    08
                </div>
                <h3 class="font-bold text-white text-sm">AI Agent & RBAC</h3>
                <p class="text-xs text-slate-400 mt-1">Asisten cerdas regulasi BTKI/HS code, proteksi fungsi RBAC native Laravel, dan audit log pabean.</p>
            </div>
        </div>
    </main>

    <!-- Footer Portal -->
    <footer class="border-t border-slate-800/80 bg-slate-950/80 py-6 text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                &copy; {{ date('Y') }} PT Agung Trans Solusindo &bull; Sistem ERP Kepabeanan Terlindungi Enkripsi & RBAC
            </div>
            <div class="flex items-center space-x-4 text-[11px]">
                <span class="text-emerald-400 font-mono">v4.0.2 H2H DJBC</span>
                <span>&bull;</span>
                <span>SLA Gateway 99.9%</span>
                <span>&bull;</span>
                <a href="{{ route('login') }}" class="text-brand-400 hover:underline">Portal Login</a>
            </div>
        </div>
    </footer>

</body>
</html>
