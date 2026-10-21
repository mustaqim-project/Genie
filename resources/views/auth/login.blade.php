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
        /* PORT CRANE & CONTAINER SHIP 60FPS CSS KEYFRAME ANIMATIONS     */
        /* ============================================================== */
        @keyframes vessel-sway {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-4px) rotate(0.8deg); }
        }
        @keyframes ocean-wave-front {
            0% { transform: translateX(0); }
            50% { transform: translateX(-16px); }
            100% { transform: translateX(0); }
        }
        @keyframes ocean-wave-back {
            0% { transform: translateX(0); }
            50% { transform: translateX(16px); }
            100% { transform: translateX(0); }
        }
        @keyframes crane-trolley-cycle {
            0%, 12% { transform: translateX(0px); }
            42%, 62% { transform: translateX(96px); }
            88%, 100% { transform: translateX(0px); }
        }
        @keyframes crane-cable-cycle {
            0%, 12% { transform: scaleY(0.48); }
            24% { transform: scaleY(1.05); }
            42%, 62% { transform: scaleY(0.62); }
            78% { transform: scaleY(1.02); }
            88%, 100% { transform: scaleY(0.48); }
        }
        @keyframes container-lift-cycle {
            0%, 12% { transform: translate(0px, 0px); opacity: 1; }
            24% { transform: translate(0px, 24px); }
            42%, 62% { transform: translate(96px, 8px); }
            78% { transform: translate(96px, 22px); }
            88%, 100% { transform: translate(0px, 0px); }
        }
        @keyframes smoke-float-1 {
            0% { opacity: 0.8; transform: translate(0, 0) scale(0.6); }
            60% { opacity: 0.4; }
            100% { opacity: 0; transform: translate(-18px, -28px) scale(1.9); }
        }
        @keyframes smoke-float-2 {
            0% { opacity: 0.7; transform: translate(0, 0) scale(0.5); }
            60% { opacity: 0.35; }
            100% { opacity: 0; transform: translate(-14px, -22px) scale(1.7); }
        }
        @keyframes radar-spin-2d {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @keyframes beacon-glow {
            0%, 100% { opacity: 0.3; filter: drop-shadow(0 0 1px #38BDF8); }
            50% { opacity: 1; filter: drop-shadow(0 0 8px #38BDF8); }
        }
        @keyframes nav-light-starboard {
            0%, 49% { opacity: 1; filter: drop-shadow(0 0 5px #10B981); }
            50%, 100% { opacity: 0.2; filter: none; }
        }
        @keyframes nav-light-port {
            0%, 49% { opacity: 0.2; filter: none; }
            50%, 100% { opacity: 1; filter: drop-shadow(0 0 5px #EF4444); }
        }
        @keyframes port-crane-light {
            0%, 100% { opacity: 0.65; }
            50% { opacity: 0.95; }
        }

        .anim-vessel {
            animation: vessel-sway 5.2s ease-in-out infinite;
            transform-origin: 260px 145px;
        }
        .anim-wave-front {
            animation: ocean-wave-front 6s ease-in-out infinite;
        }
        .anim-wave-back {
            animation: ocean-wave-back 7.5s ease-in-out infinite;
        }
        .anim-crane-trolley {
            animation: crane-trolley-cycle 12s cubic-bezier(0.45, 0, 0.55, 1) infinite;
        }
        .anim-crane-cable {
            animation: crane-cable-cycle 12s cubic-bezier(0.45, 0, 0.55, 1) infinite;
            transform-origin: 84px 34px;
        }
        .anim-container-lift {
            animation: container-lift-cycle 12s cubic-bezier(0.45, 0, 0.55, 1) infinite;
        }
        .anim-smoke-1 {
            animation: smoke-float-1 3.2s ease-out infinite;
            transform-origin: 326px 66px;
        }
        .anim-smoke-2 {
            animation: smoke-float-2 3.2s ease-out infinite 1.6s;
            transform-origin: 326px 66px;
        }
        .anim-radar {
            animation: radar-spin-2d 4s linear infinite;
            transform-origin: 310px 62px;
        }
        .anim-beacon {
            animation: beacon-glow 2.5s ease-in-out infinite;
        }
        .anim-nav-green {
            animation: nav-light-starboard 1.8s steps(1) infinite;
        }
        .anim-nav-red {
            animation: nav-light-port 1.8s steps(1) infinite;
        }
        .anim-crane-floodlight {
            animation: port-crane-light 4s ease-in-out infinite;
        }
    </style>
</head>
<body class="h-full min-h-screen flex flex-col justify-center">

    <div class="login-grid min-h-screen grid grid-cols-1 md:grid-cols-2">
        <!-- 1. LEFT HERO PANEL (APPLE HIG BLACK BACKDROP) -->
        <div class="hero-panel bg-black text-white p-12 lg:p-16 flex flex-col justify-between border-r border-slate-800">
            <!-- Brand Header -->
            <div class="flex items-center space-x-3.5">
                <div class="mk">G</div>
                <div>
                    <span class="font-bold text-xl tracking-tight text-white block">Genie</span>
                    <span class="text-xs text-slate-400 font-medium block">ERP Import-Export</span>
                </div>
            </div>

            <!-- Value Proposition & Cargo Illustration -->
            <div class="my-auto py-12 max-w-md">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-950/80 border border-sky-500/30 text-sky-400 text-xs font-semibold mb-6">
                    <span class="w-2 h-2 rounded-full bg-sky-400 animate-pulse"></span>
                    Sistem Terintegrasi CEISA 4.0
                </div>

                <h1 class="text-3xl lg:text-4xl font-extrabold tracking-tight leading-tight text-white mb-4">
                    Satu sistem untuk seluruh proses import-export.
                </h1>

                <p class="text-slate-400 text-sm leading-relaxed mb-8">
                    Dari dokumen, CEISA 4.0, DO pelayaran, sampai penanganan lapangan, POD, dan penagihan invoice. Semua tim berkolaborasi di satu tempat.
                </p>

                <!-- SVG Cargo Ship & Containers Illustration With Dynamic Port Animation -->
                <div class="bg-slate-950/90 rounded-2xl p-5 border border-slate-800/80 shadow-2xl backdrop-blur-md overflow-hidden relative group">
                    <svg viewBox="0 0 460 210" width="100%" class="h-auto select-none" role="img" aria-label="Animasi Kapal Kontainer dan Crane Pelabuhan Logistik">
                        <defs>
                            <!-- Sky Gradient -->
                            <linearGradient id="skyGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#050C1B"/>
                                <stop offset="55%" stop-color="#0A1832"/>
                                <stop offset="100%" stop-color="#0E244D"/>
                            </linearGradient>

                            <!-- Ocean Gradient -->
                            <linearGradient id="seaGrad1" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#1D4ED8" stop-opacity="0.9"/>
                                <stop offset="100%" stop-color="#0A1938" stop-opacity="0.95"/>
                            </linearGradient>
                            <linearGradient id="seaGrad2" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#2563EB" stop-opacity="0.75"/>
                                <stop offset="100%" stop-color="#0E234B" stop-opacity="0.9"/>
                            </linearGradient>

                            <!-- Crane Light Beam Cone Gradient -->
                            <linearGradient id="craneLightGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#FDE047" stop-opacity="0.45"/>
                                <stop offset="100%" stop-color="#FDE047" stop-opacity="0.0"/>
                            </linearGradient>

                            <!-- Ship Hull Gradient -->
                            <linearGradient id="hullGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#1E293B"/>
                                <stop offset="70%" stop-color="#0F172A"/>
                                <stop offset="71%" stop-color="#DC2626"/>
                                <stop offset="100%" stop-color="#991B1B"/>
                            </linearGradient>

                            <!-- Container Pattern -->
                            <pattern id="ctnRibs" width="4" height="20" patternUnits="userSpaceOnUse">
                                <line x1="2" y1="0" x2="2" y2="20" stroke="rgba(0,0,0,0.22)" stroke-width="1"/>
                            </pattern>
                        </defs>

                        <!-- 1. Background Sky & Stars -->
                        <rect x="0" y="0" width="460" height="210" rx="14" fill="url(#skyGrad)"/>
                        
                        <!-- Stars in Sky -->
                        <circle cx="45" cy="22" r="1" fill="#FFFFFF" opacity="0.6"/>
                        <circle cx="95" cy="15" r="1.2" fill="#BAE6FD" opacity="0.8"/>
                        <circle cx="160" cy="28" r="0.8" fill="#FFFFFF" opacity="0.5"/>
                        <circle cx="215" cy="18" r="1" fill="#FFFFFF" opacity="0.7"/>
                        <circle cx="270" cy="24" r="1.3" fill="#BAE6FD" opacity="0.8"/>
                        <circle cx="340" cy="14" r="0.9" fill="#FFFFFF" opacity="0.6"/>
                        <circle cx="395" cy="26" r="1.2" fill="#FFFFFF" opacity="0.7"/>
                        <circle cx="430" cy="18" r="1" fill="#BAE6FD" opacity="0.8"/>

                        <!-- Crescent Moon -->
                        <path d="M418 20 A 10 10 0 1 0 428 35 A 12 12 0 1 1 418 20 Z" fill="#F8FAFC" opacity="0.85"/>

                        <!-- Distant Harbor Horizon Silhouette -->
                        <path d="M0 135 L60 135 L75 130 L110 130 L130 135 L460 135 L460 145 L0 145 Z" fill="#061226"/>

                        <!-- 2. Back Sea Wave Layer (Beranimasi) -->
                        <g class="anim-wave-back">
                            <path d="M-40 142 Q 20 138, 80 142 T 200 142 T 320 142 T 440 142 T 560 142 L 560 210 L -40 210 Z" fill="url(#seaGrad2)"/>
                        </g>

                        <!-- 3. CARGO VESSEL WAN HAI 315 (Beranimasi Mengapung & Berayun) -->
                        <g class="anim-vessel">
                            <!-- Waterline wake & reflection -->
                            <ellipse cx="250" cy="154" rx="145" ry="5" fill="#38BDF8" opacity="0.25"/>

                            <!-- Ship Hull (Lambung Kapal) -->
                            <!-- Main Hull Profile: Buritan (Kiri x:115) ke Haluan (Kanan x:395) -->
                            <path d="M120 114 L360 114 L395 130 L380 152 L132 152 L120 135 Z" fill="url(#hullGrad)"/>
                            <!-- Waterline Stripe (Garis Putih Waterline) -->
                            <line x1="130" y1="140" x2="384" y2="140" stroke="#FFFFFF" stroke-width="1.2" opacity="0.85"/>
                            
                            <!-- Vessel Name On Bow -->
                            <text x="345" y="125" font-family="'JetBrains Mono', monospace" font-size="6.5" font-weight="700" fill="#E2E8F0" letter-spacing="0.5">WAN HAI 315</text>
                            
                            <!-- Hawsehole & Anchor -->
                            <circle cx="378" cy="126" r="2" fill="#0B132B"/>
                            <path d="M378 127 L377 132 M375 131 Q 377 134, 379 131" stroke="#475569" stroke-width="0.8" fill="none"/>

                            <!-- Navigation Lights (Haluan Kapal) -->
                            <circle cx="394" cy="128" r="2" fill="#10B981" class="anim-nav-green"/>
                            <circle cx="394" cy="132" r="2" fill="#EF4444" class="anim-nav-red"/>

                            <!-- DECK CONTAINERS (Tumpukan Kontainer di Kapal) -->
                            <!-- Baris 1 (Bawah dek y:92 h:22) -->
                            <!-- Ctn 1: Hapag Orange -->
                            <rect x="130" y="92" width="28" height="22" rx="1.5" fill="#EA580C"/>
                            <rect x="130" y="92" width="28" height="22" fill="url(#ctnRibs)"/>
                            <!-- Ctn 2: Maersk Blue -->
                            <rect x="160" y="92" width="28" height="22" rx="1.5" fill="#0284C7"/>
                            <rect x="160" y="92" width="28" height="22" fill="url(#ctnRibs)"/>
                            <!-- Ctn 3: Evergreen Green -->
                            <rect x="190" y="92" width="28" height="22" rx="1.5" fill="#059669"/>
                            <rect x="190" y="92" width="28" height="22" fill="url(#ctnRibs)"/>
                            <!-- Ctn 4: ONE Magenta -->
                            <rect x="220" y="92" width="28" height="22" rx="1.5" fill="#DB2777"/>
                            <rect x="220" y="92" width="28" height="22" fill="url(#ctnRibs)"/>
                            <!-- Ctn 5: Cosco Dark Blue -->
                            <rect x="250" y="92" width="28" height="22" rx="1.5" fill="#1E40AF"/>
                            <rect x="250" y="92" width="28" height="22" fill="url(#ctnRibs)"/>

                            <!-- Baris 2 (Tengah y:70 h:22) -->
                            <!-- Ctn 6: Cyan -->
                            <rect x="145" y="70" width="28" height="22" rx="1.5" fill="#06B6D4"/>
                            <rect x="145" y="70" width="28" height="22" fill="url(#ctnRibs)"/>
                            <!-- Ctn 7: Maersk Light Blue -->
                            <rect x="175" y="70" width="28" height="22" rx="1.5" fill="#38BDF8"/>
                            <rect x="175" y="70" width="28" height="22" fill="url(#ctnRibs)"/>
                            <!-- Ctn 8: Industrial Yellow -->
                            <rect x="205" y="70" width="28" height="22" rx="1.5" fill="#D97706"/>
                            <rect x="205" y="70" width="28" height="22" fill="url(#ctnRibs)"/>
                            <!-- Ctn 9: Emerald Green -->
                            <rect x="235" y="70" width="28" height="22" rx="1.5" fill="#10B981"/>
                            <rect x="235" y="70" width="28" height="22" fill="url(#ctnRibs)"/>

                            <!-- Baris 3 (Atas y:48 h:22) -->
                            <!-- Ctn 10: ONE Magenta -->
                            <rect x="160" y="48" width="28" height="22" rx="1.5" fill="#BE185D"/>
                            <rect x="160" y="48" width="28" height="22" fill="url(#ctnRibs)"/>
                            <!-- Ctn 11: Pure White Ctn (Reefer) -->
                            <rect x="190" y="48" width="28" height="22" rx="1.5" fill="#E2E8F0"/>
                            <rect x="190" y="48" width="28" height="22" fill="url(#ctnRibs)"/>
                            <circle cx="196" cy="54" r="1.5" fill="#0284C7"/> <!-- Reefer motor icon -->
                            <!-- Ctn 12: Orange -->
                            <rect x="220" y="48" width="28" height="22" rx="1.5" fill="#F97316"/>
                            <rect x="220" y="48" width="28" height="22" fill="url(#ctnRibs)"/>

                            <!-- NAVIGATION BRIDGE / ANJUNGAN KAPAL (x: 290 s/d 342) -->
                            <!-- Lower Tower Deck -->
                            <rect x="290" y="80" width="48" height="34" rx="2" fill="#E2E8F0"/>
                            <rect x="294" y="86" width="40" height="4" rx="1" fill="#0F172A"/> <!-- Portholes -->
                            <!-- Upper Bridge Deck -->
                            <rect x="294" y="58" width="40" height="22" rx="2" fill="#F8FAFC"/>
                            <!-- Bridge Command Windows (Menyala Keemasan Hangat) -->
                            <rect x="298" y="62" width="32" height="7" rx="1" fill="#FDE047" opacity="0.9"/>
                            <line x1="306" y1="62" x2="306" y2="69" stroke="#0F172A" stroke-width="0.8"/>
                            <line x1="314" y1="62" x2="314" y2="69" stroke="#0F172A" stroke-width="0.8"/>
                            <line x1="322" y1="62" x2="322" y2="69" stroke="#0F172A" stroke-width="0.8"/>

                            <!-- Bridge Wings (Sayap Anjungan) -->
                            <rect x="286" y="62" width="8" height="4" rx="1" fill="#CBD5E1"/>
                            <rect x="334" y="62" width="8" height="4" rx="1" fill="#CBD5E1"/>

                            <!-- Ship Funnel / Cerobong Asap (x: 322 y: 44) -->
                            <rect x="320" y="44" width="14" height="15" rx="1" fill="#DC2626"/>
                            <rect x="320" y="44" width="14" height="4" fill="#0F172A"/> <!-- Black top cap -->
                            <circle cx="327" cy="52" r="3" fill="#FFFFFF"/> <!-- Funnel crest -->

                            <!-- Chimney Smoke Animation (Uap Asap Bergerak Halus) -->
                            <circle cx="327" cy="40" r="3.5" fill="#E2E8F0" class="anim-smoke-1"/>
                            <circle cx="327" cy="40" r="3" fill="#E2E8F0" class="anim-smoke-2"/>

                            <!-- Main Mast & Radar & Beacon Lamp -->
                            <line x1="310" y1="58" x2="310" y2="38" stroke="#64748B" stroke-width="1.8"/>
                            <line x1="304" y1="46" x2="316" y2="46" stroke="#64748B" stroke-width="1.2"/>
                            <!-- Rotating Radar Scanner -->
                            <g class="anim-radar">
                                <line x1="305" y1="38" x2="315" y2="38" stroke="#38BDF8" stroke-width="2" stroke-linecap="round"/>
                            </g>
                            <!-- Top Mast Beacon Light -->
                            <circle cx="310" cy="36" r="2" fill="#38BDF8" class="anim-beacon"/>
                        </g>

                        <!-- 4. QUAYSIDE DERMAGA PELABUHAN (KIRI x: 0 s/d 115) -->
                        <g>
                            <!-- Dermaga Beton Pier Profile -->
                            <rect x="0" y="132" width="112" height="78" fill="#1E293B"/>
                            <rect x="0" y="132" width="112" height="7" fill="#334155"/>
                            <!-- Safety Hazard Stripes (Kuning & Hitam) -->
                            <g opacity="0.85">
                                <polygon points="0,132 8,132 3,139 0,139" fill="#F59E0B"/>
                                <polygon points="12,132 20,132 15,139 7,139" fill="#F59E0B"/>
                                <polygon points="24,132 32,132 27,139 19,139" fill="#F59E0B"/>
                                <polygon points="36,132 44,132 39,139 31,139" fill="#F59E0B"/>
                                <polygon points="48,132 56,132 51,139 43,139" fill="#F59E0B"/>
                                <polygon points="60,132 68,132 63,139 55,139" fill="#F59E0B"/>
                                <polygon points="72,132 80,132 75,139 67,139" fill="#F59E0B"/>
                                <polygon points="84,132 92,132 87,139 79,139" fill="#F59E0B"/>
                                <polygon points="96,132 104,132 99,139 91,139" fill="#F59E0B"/>
                                <polygon points="108,132 112,132 111,139 103,139" fill="#F59E0B"/>
                            </g>

                            <!-- Bollard Tambatan Kapal & Mooring Line -->
                            <rect x="98" y="128" width="6" height="5" rx="1.5" fill="#0F172A"/>
                            <path d="M101 130 Q 112 136, 126 138" stroke="#78716C" stroke-width="1" fill="none" stroke-dasharray="2,1"/>

                            <!-- Kontainer Antrean di Dermaga -->
                            <rect x="25" y="112" width="28" height="20" rx="1.5" fill="#059669"/>
                            <rect x="25" y="112" width="28" height="20" fill="url(#ctnRibs)"/>
                            <rect x="55" y="112" width="28" height="20" rx="1.5" fill="#D97706"/>
                            <rect x="55" y="112" width="28" height="20" fill="url(#ctnRibs)"/>
                            <rect x="40" y="92" width="28" height="20" rx="1.5" fill="#0284C7"/>
                            <rect x="40" y="92" width="28" height="20" fill="url(#ctnRibs)"/>
                        </g>

                        <!-- 5. SHIP-TO-SHORE (STS) CONTAINER GANTRY CRANE LENGKAP -->
                        <g>
                            <!-- Crane Floodlight Glow Beam (Cahaya Sorot ke Arah Dek) -->
                            <polygon points="84,36 120,135 240,135 150,36" fill="url(#craneLightGrad)" class="anim-crane-floodlight"/>

                            <!-- Crane Legs (A-Frame Portal Baja Kuning/Oranye) -->
                            <!-- Kaki Kiri -->
                            <line x1="28" y1="132" x2="48" y2="34" stroke="#F59E0B" stroke-width="4.5" stroke-linecap="round"/>
                            <line x1="28" y1="132" x2="48" y2="34" stroke="#D97706" stroke-width="2"/>
                            <!-- Kaki Kanan -->
                            <line x1="88" y1="132" x2="72" y2="34" stroke="#F59E0B" stroke-width="4.5" stroke-linecap="round"/>
                            <line x1="88" y1="132" x2="72" y2="34" stroke="#D97706" stroke-width="2"/>

                            <!-- Crane Cross Bracing (Rangka Silang Penguat Baja) -->
                            <line x1="34" y1="105" x2="82" y2="105" stroke="#F59E0B" stroke-width="2"/>
                            <line x1="38" y1="80" x2="78" y2="80" stroke="#F59E0B" stroke-width="2"/>
                            <line x1="34" y1="105" x2="78" y2="80" stroke="#B45309" stroke-width="1.2"/>
                            <line x1="82" y1="105" x2="38" y2="80" stroke="#B45309" stroke-width="1.2"/>
                            <line x1="42" y1="58" x2="74" y2="58" stroke="#F59E0B" stroke-width="2.5"/>

                            <!-- Operator Cabin & Machinery Deck -->
                            <rect x="44" y="32" width="32" height="12" rx="2" fill="#0F172A"/>
                            <rect x="54" y="34" width="12" height="7" rx="1" fill="#FDE047" opacity="0.95"/> <!-- Cabin window -->

                            <!-- Gantry Boom (Lengan Horizontal Memanjang di Atas Kapal) -->
                            <!-- Upper Boom Truss -->
                            <line x1="12" y1="24" x2="275" y2="24" stroke="#F59E0B" stroke-width="3" stroke-linecap="round"/>
                            <line x1="12" y1="34" x2="275" y2="34" stroke="#F59E0B" stroke-width="3.5" stroke-linecap="round"/>
                            <!-- Diagonal Truss Struts -->
                            <line x1="25" y1="24" x2="35" y2="34" stroke="#D97706" stroke-width="1.4"/>
                            <line x1="45" y1="24" x2="55" y2="34" stroke="#D97706" stroke-width="1.4"/>
                            <line x1="75" y1="24" x2="85" y2="34" stroke="#D97706" stroke-width="1.4"/>
                            <line x1="105" y1="24" x2="115" y2="34" stroke="#D97706" stroke-width="1.4"/>
                            <line x1="135" y1="24" x2="145" y2="34" stroke="#D97706" stroke-width="1.4"/>
                            <line x1="165" y1="24" x2="175" y2="34" stroke="#D97706" stroke-width="1.4"/>
                            <line x1="195" y1="24" x2="205" y2="34" stroke="#D97706" stroke-width="1.4"/>
                            <line x1="225" y1="24" x2="235" y2="34" stroke="#D97706" stroke-width="1.4"/>
                            <line x1="255" y1="24" x2="265" y2="34" stroke="#D97706" stroke-width="1.4"/>

                            <!-- Crane Apex Tower & Stay Cables (Tali Baja Penahan Boom) -->
                            <line x1="58" y1="34" x2="60" y2="8" stroke="#F59E0B" stroke-width="3.5"/>
                            <line x1="60" y1="8" x2="15" y2="24" stroke="#78716C" stroke-width="1.2"/>
                            <line x1="60" y1="8" x2="200" y2="24" stroke="#78716C" stroke-width="1.2"/>
                            <circle cx="60" cy="7" r="1.8" fill="#EF4444" class="anim-beacon"/>

                            <!-- DYNAMIC TROLLEY & SPREADER CONTAINER (Beranimasi Bergerak di Rel Crane) -->
                            <g class="anim-crane-trolley">
                                <!-- Trolley Base -->
                                <rect x="74" y="31" width="20" height="6" rx="1.5" fill="#1E293B"/>
                                <circle cx="78" cy="31" r="2" fill="#D97706"/>
                                <circle cx="90" cy="31" r="2" fill="#D97706"/>
                                <!-- Trolley Floodlight Lamp -->
                                <circle cx="84" cy="37" r="1.8" fill="#FDE047"/>
                            </g>

                            <!-- Hoist Cables (Kabel Baja Katrol Beranimasi Memanjang/Memendek) -->
                            <g class="anim-crane-trolley">
                                <g class="anim-crane-cable">
                                    <line x1="79" y1="36" x2="79" y2="62" stroke="#E2E8F0" stroke-width="1" stroke-dasharray="2,1"/>
                                    <line x1="89" y1="36" x2="89" y2="62" stroke="#E2E8F0" stroke-width="1" stroke-dasharray="2,1"/>
                                </g>
                            </g>

                            <!-- Spreader Bar & Container Yang Sedang Diangkat (Beranimasi Angkat/Pindah) -->
                            <g class="anim-container-lift">
                                <!-- Spreader Bar Kuning Baja -->
                                <rect x="70" y="60" width="28" height="4" rx="1" fill="#F59E0B"/>
                                <rect x="69" y="62" width="2" height="4" fill="#0F172A"/> <!-- Spreader flipper guide -->
                                <rect x="97" y="62" width="2" height="4" fill="#0F172A"/>
                                
                                <!-- Lifted Container: Maersk Sky Blue 20ft -->
                                <rect x="70" y="64" width="28" height="20" rx="1.5" fill="#0284C7"/>
                                <rect x="70" y="64" width="28" height="20" fill="url(#ctnRibs)"/>
                                <text x="74" y="76" font-family="'JetBrains Mono', monospace" font-size="5" font-weight="700" fill="#FFFFFF" opacity="0.9">MAERSK</text>
                            </g>
                        </g>

                        <!-- 6. FOREGROUND OCEAN WAVES (Lapisan Ombak Depan Bergerak Halus) -->
                        <g class="anim-wave-front">
                            <path d="M-20 148 Q 40 143, 100 148 T 220 148 T 340 148 T 460 148 T 580 148 L 580 210 L -20 210 Z" fill="url(#seaGrad1)"/>
                            <!-- Sea Foam Crest Line (Buih Ombak Putih Cyan) -->
                            <path d="M-20 148 Q 40 143, 100 148 T 220 148 T 340 148 T 460 148 T 580 148" stroke="#BAE6FD" stroke-width="1.2" fill="none" opacity="0.75"/>
                        </g>
                    </svg>

                    <!-- Real-Time Telemetry Port Header Under Animated Illustration -->
                    <div class="mt-3.5 flex flex-wrap items-center justify-between gap-2 text-[11px] text-slate-400 border-t border-slate-800/80 pt-2.5 font-mono">
                        <div class="flex items-center gap-2">
                            <span class="text-white font-bold tracking-tight">WAN HAI 315</span>
                            <span class="text-slate-600">&bull;</span>
                            <span class="text-slate-400">BERTH 03 PRIOK</span>
                            <span class="text-slate-600">&bull;</span>
                            <span class="text-sky-400">STS CRANE ACTIVE</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                            <span class="text-emerald-400 font-semibold tracking-wide">CEISA 4.0 CONNECTED</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Identitas Perusahaan -->
            <div class="text-xs text-slate-400 flex items-center justify-between">
                <span>PT Agung Trans Solusindo</span>
                <span>v2.0 &bull; CEISA 4.0 Open API</span>
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
                <details class="mt-8 pt-6 border-t border-slate-800 group" open>
                    <summary class="cursor-pointer text-xs font-semibold text-slate-400 hover:text-slate-200 transition-colors list-none flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <span>⚡ Persona Akun Demo (Uji Coba Cepat RBAC)</span>
                            <span class="px-2 py-0.5 rounded-full bg-sky-950 text-sky-400 border border-sky-800 text-[10px]">Testing / Dev</span>
                        </span>
                        <span class="text-slate-500 group-open:rotate-180 transition-transform">&darr;</span>
                    </summary>

                    <div class="mt-3.5 space-y-2">
                        <p class="text-[11px] text-slate-400 leading-relaxed">
                            Password default: <code class="text-sky-400 bg-slate-800 px-1.5 py-0.5 rounded font-mono">password</code>.
                            <br>
                            <span class="text-amber-400/90 font-medium">&#9888; Khusus <b>Pimpinan (R-OWN)</b> dan <b>Finance (R-FIN)</b> mewajibkan verifikasi 2FA OTP (Kode Demo: <code class="text-amber-300 font-mono">123456</code>).</span>
                        </p>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 pt-2">
                            <a href="{{ route('quick-login', 'R-OWN') }}" class="p-2.5 rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700/80 text-left transition-all hover:scale-[1.02]">
                                <div class="font-bold text-xs text-amber-400">R-OWN (Pimpinan)</div>
                                <div class="text-[11px] text-slate-300">Soehartono</div>
                                <div class="text-[9px] text-amber-500 font-mono mt-0.5">Wajib 2FA (123456)</div>
                            </a>

                            <a href="{{ route('quick-login', 'R-DOC-SPV') }}" class="p-2.5 rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700/80 text-left transition-all hover:scale-[1.02]">
                                <div class="font-bold text-xs text-emerald-400">R-DOC-SPV</div>
                                <div class="text-[11px] text-slate-300">Rian Hidayat</div>
                                <div class="text-[9px] text-slate-400 mt-0.5">Approve PIB/PEB</div>
                            </a>

                            <a href="{{ route('quick-login', 'R-DOC') }}" class="p-2.5 rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700/80 text-left transition-all hover:scale-[1.02]">
                                <div class="font-bold text-xs text-sky-400">R-DOC (Dokumen)</div>
                                <div class="text-[11px] text-slate-300">Dewi Anggraini</div>
                                <div class="text-[9px] text-slate-400 mt-0.5">HS Code & Berkas</div>
                            </a>

                            <a href="{{ route('quick-login', 'R-DO') }}" class="p-2.5 rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700/80 text-left transition-all hover:scale-[1.02]">
                                <div class="font-bold text-xs text-indigo-400">R-DO (Pelayaran)</div>
                                <div class="text-[11px] text-slate-300">Dimas Pratama</div>
                                <div class="text-[9px] text-slate-400 mt-0.5">Rilis DO & Free Time</div>
                            </a>

                            <a href="{{ route('quick-login', 'R-TRF') }}" class="p-2.5 rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700/80 text-left transition-all hover:scale-[1.02]">
                                <div class="font-bold text-xs text-rose-400">R-TRF (Traffic)</div>
                                <div class="text-[11px] text-slate-300">Budi Hartanto</div>
                                <div class="text-[9px] text-slate-400 mt-0.5">Jalur, Gate & Truk</div>
                            </a>

                            <a href="{{ route('quick-login', 'R-FIN') }}" class="p-2.5 rounded-xl bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700/80 text-left transition-all hover:scale-[1.02]">
                                <div class="font-bold text-xs text-yellow-400">R-FIN (Keuangan)</div>
                                <div class="text-[11px] text-slate-300">Sinta Wulandari</div>
                                <div class="text-[9px] text-amber-500 font-mono mt-0.5">Wajib 2FA (123456)</div>
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
