<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GENIE ERP — Platform SaaS Kepabeanan & Smart Logistics Indonesia</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', '-apple-system', 'BlinkMacSystemFont', 'Inter', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'Menlo', 'monospace'],
                    }
                }
            }
        }
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body class="h-full text-slate-100 flex flex-col justify-between bg-slate-950 min-h-screen selection:bg-sky-500 selection:text-white"
      style="background: radial-gradient(circle at 50% 0%, rgba(14, 165, 233, 0.12), transparent 40%), #020617;">

    <!-- Topbar Portal -->
    <header class="border-b border-slate-800/80 bg-slate-950/70 backdrop-blur-xl sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-slate-900 border border-slate-700 flex items-center justify-center text-white font-extrabold shadow-sm">
                    <svg class="w-5 h-5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center space-x-1.5">
                        <span class="font-extrabold text-base tracking-tight text-white">GENIE <span class="text-sky-400 font-mono text-xs px-1.5 py-0.5 rounded bg-sky-950 border border-sky-800">ERP</span></span>
                        <span class="px-1.5 py-0.5 text-[9px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-md">CEISA 4.0 DJBC</span>
                    </div>
                    <p class="text-[10px] text-slate-400 font-medium">PT Agung Trans Solusindo</p>
                </div>
            </div>

            <div class="flex items-center space-x-2.5">
                @auth
                    <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-xl bg-white text-slate-950 text-xs font-bold shadow-xs transition-all active:scale-95 hover:bg-slate-100">
                        Masuk ke Dashboard &rarr;
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-200 border border-slate-700/80 text-xs font-semibold transition-all">
                        Login Karyawan
                    </a>
                    <a href="{{ route('quick-login', 'R-ADM') }}" class="px-4 py-2 rounded-xl bg-white text-slate-950 text-xs font-bold shadow-xs transition-all active:scale-95 hover:bg-slate-100">
                        Demo Admin (1-Klik) &rarr;
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Hero -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 flex-1 flex flex-col justify-center">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full bg-slate-900/90 border border-slate-800 text-sky-400 text-xs font-medium mb-6">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Host-to-Host Gateway Aktif &bull; Terhubung ke CEISA 4.0 DJBC Gateway v2.0</span>
            </div>
            
            <h1 class="text-4xl sm:text-5xl font-black text-white tracking-tight leading-tight">
                ERP Kepabeanan & Smart Logistics <span class="text-sky-400">GENIE</span>
            </h1>
            
            <p class="mt-4 text-base text-slate-300 leading-relaxed">
                Platform operasional terpadu PT Agung Trans Solusindo untuk automasi pengurusan dokumen BC 2.0 (PIB), BC 3.0 (PEB), penebusan Delivery Order (DO), penjaluran behandle lapangan, hingga penerbitan invoice P19 dan akuntansi logistik.
            </p>

            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('login') }}" class="px-6 py-3 rounded-xl bg-white hover:bg-slate-100 text-slate-950 font-bold text-xs shadow-xs transition-all active:scale-95">
                    Buka Admin Panel ERP &rarr;
                </a>
                <a href="{{ route('quick-login', 'R-ADM') }}" class="px-6 py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-200 border border-slate-700/80 font-semibold text-xs transition-all active:scale-95">
                    Simulasi 12 Peran (RBAC)
                </a>
            </div>
        </div>

        <!-- 8 Pilar Modul ERP Bento Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- 1. Dashboard -->
            <div class="p-5 rounded-2xl bg-slate-900/40 border border-slate-800/80 hover:border-slate-700 transition-all">
                <div class="w-9 h-9 rounded-xl bg-sky-500/10 text-sky-400 flex items-center justify-center font-mono font-bold text-xs mb-3">
                    01
                </div>
                <h3 class="font-bold text-white text-sm">Dashboard & KPI</h3>
                <p class="text-xs text-slate-400 mt-1">Monitoring throughput arus impor/ekspor, bea masuk/keluar, dan status SPPB/NPE realtime.</p>
            </div>

            <!-- 2. Core Bisnis: Tim Document -->
            <div class="p-5 rounded-2xl bg-slate-900/40 border border-slate-800/80 hover:border-slate-700 transition-all">
                <div class="w-9 h-9 rounded-xl bg-sky-500/10 text-sky-400 flex items-center justify-center font-mono font-bold text-xs mb-3">
                    02
                </div>
                <h3 class="font-bold text-white text-sm">Tim Document</h3>
                <p class="text-xs text-slate-400 mt-1">Lifecycle P01-P19, verifikasi 9 berkas, deteksi HS code AI, submit BC 2.0 & BC 3.0 hingga billing MPN.</p>
            </div>

            <!-- 3. Core Bisnis: Tim DO -->
            <div class="p-5 rounded-2xl bg-slate-900/40 border border-slate-800/80 hover:border-slate-700 transition-all">
                <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center font-mono font-bold text-xs mb-3">
                    03
                </div>
                <h3 class="font-bold text-white text-sm">Tim Delivery Order (DO)</h3>
                <p class="text-xs text-slate-400 mt-1">Otomasi email invoice pelayaran, monitoring free time peti kemas, dan rilis DO pelabuhan.</p>
            </div>

            <!-- 4. Core Bisnis: Tim Traffic -->
            <div class="p-5 rounded-2xl bg-slate-900/40 border border-slate-800/80 hover:border-slate-700 transition-all">
                <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center font-mono font-bold text-xs mb-3">
                    04
                </div>
                <h3 class="font-bold text-white text-sm">Tim Traffic & Lapangan</h3>
                <p class="text-xs text-slate-400 mt-1">Penjaluran pabean (Merah/Kuning/Hijau), koordinasi behandle fisik, Gate Out TPS, dan POD.</p>
            </div>

            <!-- 5. HRM -->
            <div class="p-5 rounded-2xl bg-slate-900/40 border border-slate-800/80 hover:border-slate-700 transition-all">
                <div class="w-9 h-9 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center font-mono font-bold text-xs mb-3">
                    05
                </div>
                <h3 class="font-bold text-white text-sm">HRM & Penggajian</h3>
                <p class="text-xs text-slate-400 mt-1">Manajemen pegawai operasional, absensi tim lapangan pelabuhan geofencing, dan slip gaji.</p>
            </div>

            <!-- 6. CRM & Penawaran -->
            <div class="p-5 rounded-2xl bg-slate-900/40 border border-slate-800/80 hover:border-slate-700 transition-all">
                <div class="w-9 h-9 rounded-xl bg-teal-500/10 text-teal-400 flex items-center justify-center font-mono font-bold text-xs mb-3">
                    06
                </div>
                <h3 class="font-bold text-white text-sm">CRM & Quotation</h3>
                <p class="text-xs text-slate-400 mt-1">Database importir/eksportir, integrasi WhatsApp WABA, dan pembuatan surat penawaran harga resmi.</p>
            </div>

            <!-- 7. Finance & Accounting -->
            <div class="p-5 rounded-2xl bg-slate-900/40 border border-slate-800/80 hover:border-slate-700 transition-all">
                <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center font-mono font-bold text-xs mb-3">
                    07
                </div>
                <h3 class="font-bold text-white text-sm">Finance & Accounting</h3>
                <p class="text-xs text-slate-400 mt-1">Invoicing klien (P19), klaim reimbursement talangan pabean, jurnal berpasangan, dan neraca.</p>
            </div>

            <!-- 8. AI Agent & Setting -->
            <div class="p-5 rounded-2xl bg-slate-900/40 border border-slate-800/80 hover:border-slate-700 transition-all">
                <div class="w-9 h-9 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center font-mono font-bold text-xs mb-3">
                    08
                </div>
                <h3 class="font-bold text-white text-sm">AI Agent & MCP Server</h3>
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
                <a href="{{ route('login') }}" class="text-sky-400 hover:underline">Portal Login</a>
            </div>
        </div>
    </footer>

</body>
</html>
