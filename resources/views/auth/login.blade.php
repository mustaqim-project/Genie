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
        /* REALISTIC PORT LOGISTICS & 60FPS SHIP BUOYANCY ANIMATIONS     */
        /* ============================================================== */
        @keyframes vessel-heavy-sway {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            35% { transform: translateY(-2px) rotate(0.35deg); }
            70% { transform: translateY(1.5px) rotate(-0.25deg); }
        }
        @keyframes ocean-swell-front {
            0%, 100% { transform: translateX(0); }
            50% { transform: translateX(-16px); }
        }
        @keyframes ocean-swell-back {
            0%, 100% { transform: translateX(0); }
            50% { transform: translateX(18px); }
        }
        @keyframes maritime-radar-sweep {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @keyframes aviation-strobe-flash {
            0%, 86%, 92%, 100% { opacity: 0.2; filter: none; }
            88%, 90% { opacity: 1; filter: drop-shadow(0 0 6px #EF4444); }
        }
        @keyframes masthead-strobe-flash {
            0%, 82%, 88%, 94%, 100% { opacity: 0.25; filter: none; }
            84%, 86% { opacity: 1; filter: drop-shadow(0 0 5px #F8FAFC); }
        }
        @keyframes floodlight-volumetric {
            0%, 100% { opacity: 0.42; }
            50% { opacity: 0.58; }
        }
        @keyframes water-reflection-shimmer {
            0%, 100% { transform: scaleX(1) translateX(0); opacity: 0.4; }
            50% { transform: scaleX(1.1) translateX(-3px); opacity: 0.6; }
        }
        @keyframes funnel-vapor-rise {
            0% { transform: translate(0, 0) scale(0.6); opacity: 0.6; }
            100% { transform: translate(-14px, -22px) scale(1.7); opacity: 0; }
        }

        .anim-vessel {
            animation: vessel-heavy-sway 8.5s ease-in-out infinite;
            transform-origin: 320px 170px;
        }
        .anim-wave-front {
            animation: ocean-swell-front 6.5s ease-in-out infinite;
        }
        .anim-wave-back {
            animation: ocean-swell-back 8s ease-in-out infinite;
        }
        .anim-crane-trolley {
            will-change: transform;
        }
        .anim-smoke-1 {
            animation: funnel-vapor-rise 3.2s ease-out infinite;
            transform-origin: 432px 72px;
        }
        .anim-smoke-2 {
            animation: funnel-vapor-rise 3.2s ease-out infinite 1.6s;
            transform-origin: 432px 72px;
        }
        .anim-radar {
            animation: maritime-radar-sweep 3.6s linear infinite;
            transform-origin: 436px 66px;
        }
        .anim-beacon {
            animation: aviation-strobe-flash 2.2s ease-in-out infinite;
        }
        .anim-nav-white {
            animation: masthead-strobe-flash 2.2s steps(1) infinite;
        }
        .anim-crane-floodlight {
            animation: floodlight-volumetric 5s ease-in-out infinite;
        }
        .anim-reflection {
            animation: water-reflection-shimmer 4.2s ease-in-out infinite;
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
            <div class="my-auto py-6 w-full max-w-2xl">
                <h1 class="text-3xl lg:text-4xl font-extrabold tracking-tight leading-tight text-white mb-3">
                    Satu sistem untuk seluruh proses import-export.
                </h1>

                <p class="text-slate-400 text-sm leading-relaxed mb-6">
                    Platform terpadu untuk pengurusan dokumen pabean CEISA 4.0, DO pelayaran, traffic lapangan, dan penagihan klien.
                </p>

                <!-- SVG Cargo Ship & Containers Illustration With Dynamic Port Animation -->
                <div class="bg-slate-950/90 rounded-2xl p-4 sm:p-5 border border-slate-800/80 shadow-2xl backdrop-blur-md overflow-hidden relative group">
                    <svg viewBox="0 0 540 240" width="100%" class="h-auto select-none" role="img" aria-label="Animasi Kapal Kontainer dan Crane Pelabuhan Logistik">
                        <defs>
                            <!-- Sky Gradient -->
                            <linearGradient id="skyGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#030712"/>
                                <stop offset="55%" stop-color="#07152B"/>
                                <stop offset="100%" stop-color="#0E274D"/>
                            </linearGradient>

                            <!-- Deep Ocean Gradients -->
                            <linearGradient id="seaGrad1" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#1D4ED8" stop-opacity="0.95"/>
                                <stop offset="100%" stop-color="#030A18" stop-opacity="0.98"/>
                            </linearGradient>
                            <linearGradient id="seaGrad2" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#2563EB" stop-opacity="0.75"/>
                                <stop offset="100%" stop-color="#081A38" stop-opacity="0.92"/>
                            </linearGradient>

                            <!-- Volumetric Floodlight Beam -->
                            <linearGradient id="craneFloodGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#FDE047" stop-opacity="0.55"/>
                                <stop offset="45%" stop-color="#FDE047" stop-opacity="0.22"/>
                                <stop offset="100%" stop-color="#FDE047" stop-opacity="0.0"/>
                            </linearGradient>

                            <!-- Ship Hull Realistic Metallic Finish -->
                            <linearGradient id="hullTopsidesGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#334155"/>
                                <stop offset="12%" stop-color="#1E293B"/>
                                <stop offset="100%" stop-color="#0F172A"/>
                            </linearGradient>
                            <linearGradient id="hullBottomGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#991B1B"/>
                                <stop offset="60%" stop-color="#7F1D1D"/>
                                <stop offset="100%" stop-color="#450A0A"/>
                            </linearGradient>

                            <!-- Crane Steel Gradient -->
                            <linearGradient id="craneSteelGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#475569"/>
                                <stop offset="50%" stop-color="#1E293B"/>
                                <stop offset="100%" stop-color="#0F172A"/>
                            </linearGradient>

                            <!-- Crane Safety Amber Gradient -->
                            <linearGradient id="craneAmberGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#FBBF24"/>
                                <stop offset="50%" stop-color="#F59E0B"/>
                                <stop offset="100%" stop-color="#B45309"/>
                            </linearGradient>

                            <!-- Container Vertical Corrugation Rib Pattern -->
                            <pattern id="ctnRibs2" width="3.5" height="18" patternUnits="userSpaceOnUse">
                                <line x1="1" y1="0" x2="1" y2="18" stroke="rgba(255,255,255,0.18)" stroke-width="0.8"/>
                                <line x1="2.5" y1="0" x2="2.5" y2="18" stroke="rgba(0,0,0,0.35)" stroke-width="0.8"/>
                            </pattern>

                            <!-- Quayside Safety Curb Pattern -->
                            <pattern id="quayHazardPattern" width="10" height="7" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">
                                <rect width="5" height="7" fill="#F59E0B"/>
                                <rect x="5" width="5" height="7" fill="#0F172A"/>
                            </pattern>
                        </defs>

                        <!-- 1. Background Sky & Starfield -->
                        <rect x="0" y="0" width="540" height="240" rx="14" fill="url(#skyGrad)"/>
                        
                        <!-- Realistic Subtle Stars -->
                        <circle cx="35" cy="18" r="0.8" fill="#FFFFFF" opacity="0.6"/>
                        <circle cx="85" cy="12" r="1.1" fill="#BAE6FD" opacity="0.8"/>
                        <circle cx="150" cy="22" r="0.7" fill="#FFFFFF" opacity="0.5"/>
                        <circle cx="210" cy="14" r="0.9" fill="#FFFFFF" opacity="0.7"/>
                        <circle cx="280" cy="20" r="1.2" fill="#BAE6FD" opacity="0.8"/>
                        <circle cx="360" cy="12" r="0.8" fill="#FFFFFF" opacity="0.6"/>
                        <circle cx="430" cy="24" r="1.1" fill="#FFFFFF" opacity="0.7"/>
                        <circle cx="495" cy="15" r="0.9" fill="#BAE6FD" opacity="0.8"/>

                        <!-- Crescent Moon with Atmospheric Glow -->
                        <circle cx="490" cy="28" r="16" fill="#F8FAFC" opacity="0.04"/>
                        <path d="M486 20 A 9 9 0 1 0 496 34 A 11 11 0 1 1 486 20 Z" fill="#F1F5F9" opacity="0.9"/>

                        <!-- Distant Harbor Horizon Silhouette & Port Terminals -->
                        <path d="M0 152 L70 152 L85 147 L120 147 L135 152 L260 152 L275 148 L320 148 L335 152 L540 152 L540 162 L0 162 Z" fill="#050E1F"/>
                        <!-- Distant Horizon Crane Silhouettes -->
                        <line x1="285" y1="148" x2="285" y2="132" stroke="#081730" stroke-width="1.2"/>
                        <line x1="275" y1="134" x2="310" y2="134" stroke="#081730" stroke-width="1"/>
                        <circle cx="285" cy="132" r="0.8" fill="#EF4444" opacity="0.7"/>
                        <!-- Distant Terminal Amber Lights -->
                        <circle cx="100" cy="149" r="1" fill="#F59E0B" opacity="0.7"/>
                        <circle cx="112" cy="149" r="0.8" fill="#FDE047" opacity="0.8"/>
                        <circle cx="295" cy="150" r="1" fill="#F59E0B" opacity="0.7"/>

                        <!-- 2. Back Sea Wave Layer (Beranimasi Ombak Alami) -->
                        <g class="anim-wave-back">
                            <path d="M-40 158 Q 30 153, 100 158 T 240 158 T 380 158 T 520 158 T 660 158 L 660 240 L -40 240 Z" fill="url(#seaGrad2)"/>
                        </g>

                        <!-- Water Light Reflections (Pantulan Cahaya Lampu di Permukaan Air) -->
                        <g class="anim-reflection">
                            <!-- Pantulan Lampu Crane -->
                            <ellipse cx="190" cy="180" rx="42" ry="3.5" fill="#FDE047" opacity="0.32" filter="blur(2px)"/>
                            <!-- Pantulan Lampu Anjungan Kapal -->
                            <ellipse cx="445" cy="180" rx="26" ry="2.5" fill="#38BDF8" opacity="0.3" filter="blur(2px)"/>
                        </g>

                        <!-- 3. REALISTIC CONTAINER VESSEL (Beranimasi Buoyancy Hidrostatik Nyata) -->
                        <g class="anim-vessel">
                            <!-- Hull Waterline Shadow & Hydrodynamic Wake -->
                            <ellipse cx="310" cy="174" rx="175" ry="5.5" fill="#020617" opacity="0.55"/>

                            <!-- SHIP HULL (Lambung Kapal) -->
                            <!-- Underwater Red Anti-Fouling Bottom -->
                            <path d="M148 174 L472 174 Q 486 177, 482 186 L476 186 Q 464 179, 160 179 L148 174 Z" fill="url(#hullBottomGrad)"/>
                            <!-- Bulbous Bow Underwater -->
                            <ellipse cx="482" cy="185" rx="5" ry="3.5" fill="#7F1D1D"/>
                            <!-- Crisp White Waterline Stripe (Boot-Topping) -->
                            <path d="M144 173 L476 173 L477 175 L144 175 Z" fill="#FFFFFF" opacity="0.95"/>

                            <!-- Main Hull Freeboard Topsides (Charcoal Navy Steel) -->
                            <path d="M136 142 L420 142 L454 142 Q 476 156, 477 173 L144 173 L136 156 Z" fill="url(#hullTopsidesGrad)"/>
                            <!-- Sheer Strake Highlight (Pancaran Cahaya Tepi Geladak) -->
                            <line x1="136" y1="143" x2="456" y2="143" stroke="#64748B" stroke-width="1.2" opacity="0.85"/>

                            <!-- Anchor Hawse Pipe & Bower Anchor -->
                            <ellipse cx="462" cy="154" rx="2.2" ry="3" fill="#020617"/>
                            <path d="M462 155 L461 161 M458 159 Q 461 163, 464 159" stroke="#94A3B8" stroke-width="1" fill="none"/>
                            <!-- Bow Fairlead & Mooring Line tied to dock -->
                            <circle cx="452" cy="146" r="1.4" fill="#0B132B"/>
                            <path d="M452 147 Q 280 156, 122 158" stroke="#D1D5DB" stroke-width="1" fill="none" opacity="0.7" stroke-dasharray="3,1.5"/>
                            <path d="M142 147 Q 132 154, 118 158" stroke="#D1D5DB" stroke-width="1" fill="none" opacity="0.7"/>

                            <!-- Bow Draft Marks (Indikator Kedalaman Sarang Air) -->
                            <line x1="472" y1="166" x2="474" y2="166" stroke="#FFFFFF" stroke-width="0.8" opacity="0.8"/>
                            <line x1="473" y1="169" x2="475" y2="169" stroke="#FFFFFF" stroke-width="0.8" opacity="0.8"/>
                            <line x1="474" y1="172" x2="476" y2="172" stroke="#FFFFFF" stroke-width="0.8" opacity="0.8"/>

                            <!-- Bow Navigation Lights -->
                            <circle cx="476" cy="156" r="1.8" fill="#10B981"/>
                            <circle cx="476" cy="159" r="1.8" fill="#EF4444"/>

                            <!-- CELL GUIDES & LASHING BRIDGES (Struktur Baja Penahan Kontainer) -->
                            <line x1="228" y1="78" x2="228" y2="142" stroke="#334155" stroke-width="1.8"/>
                            <line x1="322" y1="78" x2="322" y2="142" stroke="#334155" stroke-width="1.8"/>
                            <line x1="416" y1="78" x2="416" y2="142" stroke="#334155" stroke-width="1.8"/>

                            <!-- Lashing Bridge Platforms -->
                            <rect x="225" y="118" width="6" height="2" fill="#64748B"/>
                            <rect x="319" y="118" width="6" height="2" fill="#64748B"/>

                            <!-- ============================================== -->
                            <!-- DYNAMIC 10-CONTAINER SHIP CARGO HOLD SLOTS    -->
                            <!-- (Kontainer menumpuk nyata dari 1 hingga 10)  -->
                            <!-- ============================================== -->

                            <!-- Cell Guide Vertical Structural Rails -->
                            <g stroke="#334155" stroke-width="1.8">
                                <line x1="184" y1="84" x2="184" y2="142"/>
                                <line x1="222" y1="84" x2="222" y2="142"/>
                                <line x1="260" y1="84" x2="260" y2="142"/>
                                <line x1="298" y1="84" x2="298" y2="142"/>
                                <line x1="336" y1="84" x2="336" y2="142"/>
                            </g>

                            <!-- Lashing Platforms -->
                            <rect x="220" y="118" width="4" height="2" fill="#64748B"/>
                            <rect x="258" y="118" width="4" height="2" fill="#64748B"/>
                            <rect x="296" y="118" width="4" height="2" fill="#64748B"/>

                            <!-- TIER 1 (Dasar Geladak Kapal) -->
                            <!-- Slot 1: Col 1, Tier 1 -->
                            <g id="shipCtn1" style="display:none">
                                <rect x="187" y="125" width="34" height="17" rx="1" fill="#EA580C"/>
                                <rect x="187" y="125" width="34" height="17" fill="url(#ctnRibs2)"/>
                                <rect x="187" y="125" width="2" height="2.5" fill="#94A3B8"/>
                                <rect x="219" y="125" width="2" height="2.5" fill="#94A3B8"/>
                                <rect x="187" y="139.5" width="2" height="2.5" fill="#94A3B8"/>
                                <rect x="219" y="139.5" width="2" height="2.5" fill="#94A3B8"/>
                                <line x1="187" y1="126" x2="221" y2="126" stroke="#FFFFFF" stroke-width="0.8" opacity="0.6"/>
                            </g>
                            <!-- Slot 2: Col 2, Tier 1 -->
                            <g id="shipCtn2" style="display:none">
                                <rect x="225" y="125" width="34" height="17" rx="1" fill="#0284C7"/>
                                <rect x="225" y="125" width="34" height="17" fill="url(#ctnRibs2)"/>
                                <rect x="225" y="125" width="2" height="2.5" fill="#94A3B8"/>
                                <rect x="257" y="125" width="2" height="2.5" fill="#94A3B8"/>
                                <rect x="225" y="139.5" width="2" height="2.5" fill="#94A3B8"/>
                                <rect x="257" y="139.5" width="2" height="2.5" fill="#94A3B8"/>
                                <circle cx="242" cy="133.5" r="2.5" fill="#FFFFFF" opacity="0.85"/>
                                <line x1="225" y1="126" x2="259" y2="126" stroke="#FFFFFF" stroke-width="0.8" opacity="0.6"/>
                            </g>
                            <!-- Slot 3: Col 3, Tier 1 -->
                            <g id="shipCtn3" style="display:none">
                                <rect x="263" y="125" width="34" height="17" rx="1" fill="#065F46"/>
                                <rect x="263" y="125" width="34" height="17" fill="url(#ctnRibs2)"/>
                                <rect x="263" y="125" width="2" height="2.5" fill="#94A3B8"/>
                                <rect x="295" y="125" width="2" height="2.5" fill="#94A3B8"/>
                                <rect x="263" y="139.5" width="2" height="2.5" fill="#94A3B8"/>
                                <rect x="295" y="139.5" width="2" height="2.5" fill="#94A3B8"/>
                                <line x1="263" y1="126" x2="297" y2="126" stroke="#FFFFFF" stroke-width="0.8" opacity="0.6"/>
                            </g>
                            <!-- Slot 4: Col 4, Tier 1 -->
                            <g id="shipCtn4" style="display:none">
                                <rect x="301" y="125" width="34" height="17" rx="1" fill="#1E3A8A"/>
                                <rect x="301" y="125" width="34" height="17" fill="url(#ctnRibs2)"/>
                                <rect x="301" y="125" width="2" height="2.5" fill="#94A3B8"/>
                                <rect x="333" y="125" width="2" height="2.5" fill="#94A3B8"/>
                                <rect x="301" y="139.5" width="2" height="2.5" fill="#94A3B8"/>
                                <rect x="333" y="139.5" width="2" height="2.5" fill="#94A3B8"/>
                                <line x1="301" y1="126" x2="335" y2="126" stroke="#FFFFFF" stroke-width="0.8" opacity="0.6"/>
                            </g>

                            <!-- TIER 2 (Tingkat Kedua) -->
                            <!-- Slot 5: Col 1, Tier 2 -->
                            <g id="shipCtn5" style="display:none">
                                <rect x="187" y="108" width="34" height="17" rx="1" fill="#BE185D"/>
                                <rect x="187" y="108" width="34" height="17" fill="url(#ctnRibs2)"/>
                                <rect x="187" y="108" width="2" height="2.5" fill="#94A3B8"/>
                                <rect x="219" y="108" width="2" height="2.5" fill="#94A3B8"/>
                                <rect x="187" y="122.5" width="2" height="2.5" fill="#94A3B8"/>
                                <rect x="219" y="122.5" width="2" height="2.5" fill="#94A3B8"/>
                                <line x1="187" y1="109" x2="221" y2="109" stroke="#FFFFFF" stroke-width="0.8" opacity="0.6"/>
                            </g>
                            <!-- Slot 6: Col 2, Tier 2 (White Reefer) -->
                            <g id="shipCtn6" style="display:none">
                                <rect x="225" y="108" width="34" height="17" rx="1" fill="#F8FAFC"/>
                                <rect x="225" y="108" width="34" height="17" fill="url(#ctnRibs2)"/>
                                <rect x="225" y="108" width="2" height="2.5" fill="#94A3B8"/>
                                <rect x="257" y="108" width="2" height="2.5" fill="#94A3B8"/>
                                <rect x="225" y="122.5" width="2" height="2.5" fill="#94A3B8"/>
                                <rect x="257" y="122.5" width="2" height="2.5" fill="#94A3B8"/>
                                <rect x="250" y="110" width="5.5" height="12" rx="0.5" fill="#0284C7"/>
                                <line x1="225" y1="109" x2="259" y2="109" stroke="#FFFFFF" stroke-width="0.8" opacity="0.6"/>
                            </g>
                            <!-- Slot 7: Col 3, Tier 2 -->
                            <g id="shipCtn7" style="display:none">
                                <rect x="263" y="108" width="34" height="17" rx="1" fill="#D97706"/>
                                <rect x="263" y="108" width="34" height="17" fill="url(#ctnRibs2)"/>
                                <rect x="263" y="108" width="2" height="2.5" fill="#94A3B8"/>
                                <rect x="295" y="108" width="2" height="2.5" fill="#94A3B8"/>
                                <rect x="263" y="122.5" width="2" height="2.5" fill="#94A3B8"/>
                                <rect x="295" y="122.5" width="2" height="2.5" fill="#94A3B8"/>
                                <line x1="263" y1="109" x2="297" y2="109" stroke="#FFFFFF" stroke-width="0.8" opacity="0.6"/>
                            </g>
                            <!-- Slot 8: Col 4, Tier 2 -->
                            <g id="shipCtn8" style="display:none">
                                <rect x="301" y="108" width="34" height="17" rx="1" fill="#0369A1"/>
                                <rect x="301" y="108" width="34" height="17" fill="url(#ctnRibs2)"/>
                                <rect x="301" y="108" width="2" height="2.5" fill="#94A3B8"/>
                                <rect x="333" y="108" width="2" height="2.5" fill="#94A3B8"/>
                                <rect x="301" y="122.5" width="2" height="2.5" fill="#94A3B8"/>
                                <rect x="333" y="122.5" width="2" height="2.5" fill="#94A3B8"/>
                                <line x1="301" y1="109" x2="335" y2="109" stroke="#FFFFFF" stroke-width="0.8" opacity="0.6"/>
                            </g>

                            <!-- TIER 3 (Tingkat Ketiga / Puncak Tumpukan) -->
                            <!-- Slot 9: Col 2, Tier 3 -->
                            <g id="shipCtn9" style="display:none">
                                <rect x="225" y="91" width="34" height="17" rx="1" fill="#B45309"/>
                                <rect x="225" y="91" width="34" height="17" fill="url(#ctnRibs2)"/>
                                <rect x="225" y="91" width="2" height="2.5" fill="#94A3B8"/>
                                <rect x="257" y="91" width="2" height="2.5" fill="#94A3B8"/>
                                <rect x="225" y="105.5" width="2" height="2.5" fill="#94A3B8"/>
                                <rect x="257" y="105.5" width="2" height="2.5" fill="#94A3B8"/>
                                <line x1="225" y1="92" x2="259" y2="92" stroke="#FFFFFF" stroke-width="0.8" opacity="0.6"/>
                            </g>
                            <!-- Slot 10: Col 3, Tier 3 -->
                            <g id="shipCtn10" style="display:none">
                                <rect x="263" y="91" width="34" height="17" rx="1" fill="#0284C7"/>
                                <rect x="263" y="91" width="34" height="17" fill="url(#ctnRibs2)"/>
                                <rect x="263" y="91" width="2" height="2.5" fill="#94A3B8"/>
                                <rect x="295" y="91" width="2" height="2.5" fill="#94A3B8"/>
                                <rect x="263" y="105.5" width="2" height="2.5" fill="#94A3B8"/>
                                <rect x="295" y="105.5" width="2" height="2.5" fill="#94A3B8"/>
                                <circle cx="280" cy="99.5" r="2.5" fill="#FFFFFF" opacity="0.85"/>
                                <line x1="263" y1="92" x2="297" y2="92" stroke="#FFFFFF" stroke-width="0.8" opacity="0.6"/>
                            </g>

                            <!-- ACCOMMODATION SUPERSTRUCTURE & NAVIGATION BRIDGE (Anjungan Kapal) -->
                            <!-- Deckhouse Base Structure -->
                            <rect x="420" y="102" width="38" height="40" rx="1.5" fill="#F8FAFC"/>
                            <!-- Porthole Cabins (Jendela Kabin Awak Kapal) -->
                            <circle cx="428" cy="114" r="1.5" fill="#0F172A"/>
                            <circle cx="436" cy="114" r="1.5" fill="#0F172A"/>
                            <circle cx="444" cy="114" r="1.5" fill="#0F172A"/>
                            <circle cx="428" cy="126" r="1.5" fill="#0F172A"/>
                            <circle cx="436" cy="126" r="1.5" fill="#0F172A"/>
                            <circle cx="444" cy="126" r="1.5" fill="#0F172A"/>

                            <!-- Navigation Bridge Upper Deck -->
                            <rect x="416" y="86" width="46" height="17" rx="1.5" fill="#F8FAFC"/>
                            <!-- Extended Aerodynamic Bridge Wings -->
                            <rect x="412" y="90" width="5" height="4" rx="0.8" fill="#E2E8F0"/>
                            <rect x="461" y="90" width="5" height="4" rx="0.8" fill="#E2E8F0"/>

                            <!-- Illuminated Panoramic Bridge Cockpit Windows -->
                            <rect x="419" y="89" width="38" height="6.5" rx="1" fill="#0B132B"/>
                            <rect x="420" y="90" width="36" height="4.5" rx="0.8" fill="#38BDF8" opacity="0.85"/>
                            <!-- Console Glow Interior -->
                            <circle cx="426" cy="92" r="1.2" fill="#10B981"/>
                            <circle cx="438" cy="92" r="1.2" fill="#FDE047"/>
                            <circle cx="448" cy="92" r="1.2" fill="#38BDF8"/>
                            <line x1="428" y1="89" x2="428" y2="95" stroke="#0F172A" stroke-width="0.8"/>
                            <line x1="436" y1="89" x2="436" y2="95" stroke="#0F172A" stroke-width="0.8"/>
                            <line x1="444" y1="89" x2="444" y2="95" stroke="#0F172A" stroke-width="0.8"/>

                            <!-- Ship Funnel (Cerobong Asap Kapal Modern) -->
                            <path d="M444 86 L454 86 L451 70 L441 70 Z" fill="#DC2626"/>
                            <rect x="441" y="68" width="10" height="3" fill="#0F172A"/>
                            <!-- Funnel Company Emblem -->
                            <circle cx="447" cy="78" r="2.5" fill="#FFFFFF"/>

                            <!-- Funnel Exhaust Heat & Vapor Dispersion -->
                            <circle cx="446" cy="64" r="3.2" fill="#E2E8F0" class="anim-smoke-1"/>
                            <circle cx="446" cy="64" r="3.5" fill="#CBD5E1" class="anim-smoke-2"/>

                            <!-- Main Mast, Radar & Communication Domes -->
                            <line x1="436" y1="86" x2="436" y2="56" stroke="#475569" stroke-width="1.8"/>
                            <line x1="430" y1="67" x2="442" y2="67" stroke="#475569" stroke-width="1.2"/>
                            <!-- Satcom Dome (Antena Satelit Kubah Putih) -->
                            <circle cx="430" cy="84" r="2.5" fill="#F8FAFC"/>
                            <circle cx="436" cy="84" r="2.5" fill="#F8FAFC"/>

                            <!-- Spinning X-Band Marine Radar Scanner -->
                            <g class="anim-radar">
                                <line x1="430" y1="58" x2="442" y2="58" stroke="#38BDF8" stroke-width="2" stroke-linecap="round"/>
                            </g>
                            <!-- Masthead Navigation White Strobe -->
                            <circle cx="436" cy="54" r="1.8" fill="#F8FAFC" class="anim-nav-white"/>
                        </g>

                        <!-- 4. QUAYSIDE DERMAGA (Dermaga Pelabuhan Petikemas Internasional) -->
                        <g>
                            <!-- Concrete Pavement Berth Floor -->
                            <rect x="0" y="156" width="124" height="84" fill="#1E293B"/>
                            <!-- Pavement Concrete Joint Lines -->
                            <line x1="40" y1="156" x2="40" y2="240" stroke="#0F172A" stroke-width="1"/>
                            <line x1="80" y1="156" x2="80" y2="240" stroke="#0F172A" stroke-width="1"/>

                            <!-- Heavy Crane Rail Tracks embedded in pavement -->
                            <line x1="24" y1="156" x2="24" y2="240" stroke="#64748B" stroke-width="2"/>
                            <line x1="90" y1="156" x2="90" y2="240" stroke="#64748B" stroke-width="2"/>

                            <!-- Wharf Edge Concrete Curbing with Safety Hazard Stripes -->
                            <rect x="0" y="156" width="124" height="6" fill="url(#quayHazardPattern)"/>
                            <rect x="0" y="162" width="124" height="3" fill="#0F172A"/>

                            <!-- Hanging Heavy Rubber Cylindrical Marine Fenders -->
                            <g fill="#020617" stroke="#475569" stroke-width="0.8">
                                <!-- Fender 1 -->
                                <line x1="30" y1="165" x2="30" y2="169" stroke="#94A3B8" stroke-width="0.8"/>
                                <rect x="26" y="169" width="8" height="15" rx="3.5"/>
                                <!-- Fender 2 -->
                                <line x1="72" y1="165" x2="72" y2="169" stroke="#94A3B8" stroke-width="0.8"/>
                                <rect x="68" y="169" width="8" height="15" rx="3.5"/>
                                <!-- Fender 3 -->
                                <line x1="112" y1="165" x2="112" y2="169" stroke="#94A3B8" stroke-width="0.8"/>
                                <rect x="108" y="169" width="8" height="15" rx="3.5"/>
                            </g>

                            <!-- Heavy Cast-Steel Mooring Bollards (Bolder Tambat) -->
                            <rect x="94" y="150" width="7" height="7" rx="1.5" fill="#0F172A"/>
                            <circle cx="97.5" cy="150" r="4" fill="#334155"/>
                            <rect x="118" y="150" width="7" height="7" rx="1.5" fill="#0F172A"/>
                            <circle cx="121.5" cy="150" r="4" fill="#334155"/>

                            <!-- DYNAMIC TERMINAL PRIME MOVER & TRUCK (Truk Petikemas Masuk Membawa Kontainer) -->
                            <g id="terminalTruckGroup" transform="translate(0, 0)">
                                <!-- Headlights Beam on Pavement -->
                                <polygon points="34,149 0,147 0,157 34,153" fill="url(#craneFloodGrad)" opacity="0.65"/>
                                
                                <!-- Prime Mover Cabin -->
                                <rect x="34" y="142" width="16" height="13" rx="1.5" fill="#F8FAFC"/>
                                <rect x="34" y="143" width="7" height="6" rx="0.8" fill="#0284C7"/>
                                <rect x="35" y="144" width="5" height="4" rx="0.5" fill="#38BDF8" opacity="0.85"/>
                                <circle cx="34.5" cy="150.5" r="1.2" fill="#FDE047"/>
                                <circle cx="42" cy="141.5" r="1" fill="#F59E0B"/>

                                <!-- Skeletal Chassis Trailer Frame -->
                                <rect x="49" y="149" width="46" height="3.5" rx="0.8" fill="#F59E0B"/>
                                <line x1="56" y1="149" x2="56" y2="154" stroke="#0F172A" stroke-width="1.5"/>
                                <line x1="84" y1="149" x2="84" y2="154" stroke="#0F172A" stroke-width="1.5"/>

                                <!-- Truck Wheels with Rims -->
                                <circle cx="40" cy="154.5" r="2.8" fill="#020617"/>
                                <circle cx="40" cy="154.5" r="1.2" fill="#94A3B8"/>
                                <circle cx="78" cy="154.5" r="2.8" fill="#020617"/>
                                <circle cx="78" cy="154.5" r="1.2" fill="#94A3B8"/>
                                <circle cx="86" cy="154.5" r="2.8" fill="#020617"/>
                                <circle cx="86" cy="154.5" r="1.2" fill="#94A3B8"/>

                                <!-- CONTAINER ON TRUCK (Diangkut oleh truk baru di dermaga) -->
                                <g id="truckCtnGroup">
                                    <rect id="truckCtnRect" x="51" y="132" width="34" height="17" rx="1" fill="#EA580C"/>
                                    <rect x="51" y="132" width="34" height="17" fill="url(#ctnRibs2)"/>
                                    <rect x="51" y="132" width="2" height="2.5" fill="#94A3B8"/>
                                    <rect x="83" y="132" width="2" height="2.5" fill="#94A3B8"/>
                                    <rect x="51" y="146.5" width="2" height="2.5" fill="#94A3B8"/>
                                    <rect x="83" y="146.5" width="2" height="2.5" fill="#94A3B8"/>
                                    <line x1="51" y1="133" x2="85" y2="133" stroke="#FFFFFF" stroke-width="0.8" opacity="0.6"/>
                                </g>
                            </g>
                        </g>

                        <!-- 5. REALISTIC SHIP-TO-SHORE (STS) GANTRY CRANE -->
                        <g>
                            <!-- High-Intensity Volumetric Floodlight Beam Illuminating Ship & Dock -->
                            <polygon points="84,40 28,172 260,172 170,40" fill="url(#craneFloodGrad)" class="anim-crane-floodlight"/>

                            <!-- PORTAL LEGS & TRUSS FRAMEWORK -->
                            <!-- Gantry Travel Bogies on Dock Rails -->
                            <rect x="20" y="152" width="10" height="5" rx="1.2" fill="#0F172A"/>
                            <circle cx="23" cy="155.5" r="1.5" fill="#F59E0B"/>
                            <circle cx="27" cy="155.5" r="1.5" fill="#F59E0B"/>
                            <rect x="85" y="152" width="10" height="5" rx="1.2" fill="#0F172A"/>
                            <circle cx="88" cy="155.5" r="1.5" fill="#F59E0B"/>
                            <circle cx="92" cy="155.5" r="1.5" fill="#F59E0B"/>

                            <!-- Heavy Structural Portal Legs (Twin A-Frames) -->
                            <!-- Waterside Leg -->
                            <line x1="25" y1="152" x2="52" y2="40" stroke="url(#craneSteelGrad)" stroke-width="5" stroke-linecap="round"/>
                            <line x1="25" y1="152" x2="52" y2="40" stroke="#F59E0B" stroke-width="1.8"/>
                            <!-- Landside Leg -->
                            <line x1="90" y1="152" x2="78" y2="40" stroke="url(#craneSteelGrad)" stroke-width="5" stroke-linecap="round"/>
                            <line x1="90" y1="152" x2="78" y2="40" stroke="#F59E0B" stroke-width="1.8"/>

                            <!-- Portal Leg Cross-Bracing Girders & K-Trusses -->
                            <line x1="33" y1="120" x2="84" y2="120" stroke="#1E293B" stroke-width="2.5"/>
                            <line x1="39" y1="90" x2="81" y2="90" stroke="#1E293B" stroke-width="2.5"/>
                            <line x1="33" y1="120" x2="81" y2="90" stroke="#F59E0B" stroke-width="1.2"/>
                            <line x1="84" y1="120" x2="39" y2="90" stroke="#F59E0B" stroke-width="1.2"/>
                            <line x1="45" y1="64" x2="79" y2="64" stroke="#1E293B" stroke-width="3"/>

                            <!-- Safety Access Ladders & Cage -->
                            <line x1="47" y1="120" x2="47" y2="42" stroke="#F59E0B" stroke-width="1" stroke-dasharray="2,2"/>

                            <!-- UPPER MACHINERY HOUSE & APEX TOWER -->
                            <!-- Machinery Room (Counterweight Winch House at Rear) -->
                            <rect x="42" y="36" width="38" height="15" rx="2" fill="#0F172A"/>
                            <rect x="46" y="39" width="12" height="7" rx="1" fill="#FDE047" opacity="0.9"/>
                            <circle cx="52" cy="42.5" r="1.5" fill="#B45309"/>

                            <!-- Tower A-Frame Apex to Boom -->
                            <line x1="62" y1="38" x2="65" y2="12" stroke="#F59E0B" stroke-width="4"/>
                            <line x1="65" y1="12" x2="15" y2="30" stroke="#94A3B8" stroke-width="1.5"/>
                            <line x1="65" y1="12" x2="230" y2="30" stroke="#94A3B8" stroke-width="1.5"/>
                            
                            <!-- Red Aviation Safety Obstruction Beacon at Apex -->
                            <circle cx="65" cy="11" r="2.2" fill="#EF4444" class="anim-beacon"/>

                            <!-- MAIN LATTICE BOOM (Belalai Box-Girder Crane Melintang di atas Kapal) -->
                            <!-- Boom Upper & Lower Main Chords -->
                            <line x1="10" y1="28" x2="330" y2="28" stroke="#F59E0B" stroke-width="3.2" stroke-linecap="round"/>
                            <line x1="10" y1="40" x2="330" y2="40" stroke="#F59E0B" stroke-width="4" stroke-linecap="round"/>
                            <!-- Internal Web Diagonal Lacing along the entire boom -->
                            <g stroke="#B45309" stroke-width="1.4">
                                <line x1="20" y1="28" x2="32" y2="40"/>
                                <line x1="44" y1="28" x2="32" y2="40"/>
                                <line x1="44" y1="28" x2="56" y2="40"/>
                                <line x1="68" y1="28" x2="56" y2="40"/>
                                <line x1="68" y1="28" x2="80" y2="40"/>
                                <line x1="92" y1="28" x2="80" y2="40"/>
                                <line x1="92" y1="28" x2="104" y2="40"/>
                                <line x1="116" y1="28" x2="104" y2="40"/>
                                <line x1="116" y1="28" x2="128" y2="40"/>
                                <line x1="140" y1="28" x2="128" y2="40"/>
                                <line x1="140" y1="28" x2="152" y2="40"/>
                                <line x1="164" y1="28" x2="152" y2="40"/>
                                <line x1="164" y1="28" x2="176" y2="40"/>
                                <line x1="188" y1="28" x2="176" y2="40"/>
                                <line x1="188" y1="28" x2="200" y2="40"/>
                                <line x1="212" y1="28" x2="200" y2="40"/>
                                <line x1="212" y1="28" x2="224" y2="40"/>
                                <line x1="236" y1="28" x2="224" y2="40"/>
                                <line x1="236" y1="28" x2="248" y2="40"/>
                                <line x1="260" y1="28" x2="248" y2="40"/>
                                <line x1="260" y1="28" x2="272" y2="40"/>
                                <line x1="284" y1="28" x2="272" y2="40"/>
                                <line x1="284" y1="28" x2="296" y2="40"/>
                                <line x1="308" y1="28" x2="296" y2="40"/>
                                <line x1="308" y1="28" x2="320" y2="40"/>
                            </g>

                            <!-- Operator's Panoramic Suspended Glass Cabin -->
                            <rect x="58" y="42" width="16" height="12" rx="2" fill="#0F172A"/>
                            <rect x="59" y="44" width="14" height="9" rx="1" fill="#38BDF8" opacity="0.8"/>
                            <circle cx="65" cy="49" r="2" fill="#FDE047"/>

                            <!-- DYNAMIC WORKING TROLLEY & SPREADER ASSEMBLY (Dioperasikan Otomatis 60FPS) -->
                            <g id="craneTrolleyGroup" class="anim-crane-trolley" transform="translate(68, 0)">
                                <!-- Heavy Trolley Carriage Body -->
                                <rect x="-14" y="36" width="28" height="8" rx="2" fill="#0F172A"/>
                                <circle cx="-8" cy="36" r="2.5" fill="#F59E0B"/>
                                <circle cx="8" cy="36" r="2.5" fill="#F59E0B"/>
                                <!-- Trolley Warning Strobe & LED spotlight -->
                                <circle cx="0" cy="43.5" r="2" fill="#FDE047"/>

                                <!-- High-Tensile Hoist Wire Ropes -->
                                <line id="cableL" x1="-8" y1="42" x2="-8" y2="56" stroke="#F1F5F9" stroke-width="1" stroke-dasharray="3,1.5"/>
                                <line id="cableR" x1="8" y1="42" x2="8" y2="56" stroke="#F1F5F9" stroke-width="1" stroke-dasharray="3,1.5"/>

                                <!-- Spreader Assembly (Bergerak Turun/Naik saat Angkat & Tarok) -->
                                <g id="spreaderGroup" transform="translate(0, 56)">
                                    <rect x="-18" y="0" width="36" height="5" rx="1.2" fill="#F59E0B"/>
                                    <rect x="-19" y="2" width="3" height="6" fill="#0F172A"/>
                                    <rect x="16" y="2" width="3" height="6" fill="#0F172A"/>
                                    <!-- Twistlock indicator lights -->
                                    <circle id="twistlockL" cx="-14" cy="2.5" r="1" fill="#10B981"/>
                                    <circle id="twistlockR" cx="14" cy="2.5" r="1" fill="#10B981"/>

                                    <!-- Lifted Container Under Spreader (Aktif saat diangkat dari truck ke kapal) -->
                                    <g id="spreaderCtn" style="display:none">
                                        <rect id="spreaderCtnRect" x="-17" y="5" width="34" height="17" rx="1" fill="#EA580C"/>
                                        <rect x="-17" y="5" width="34" height="17" fill="url(#ctnRibs2)"/>
                                        <rect x="-17" y="5" width="2" height="2.5" fill="#94A3B8"/>
                                        <rect x="15" y="5" width="2" height="2.5" fill="#94A3B8"/>
                                        <rect x="-17" y="19.5" width="2" height="2.5" fill="#94A3B8"/>
                                        <rect x="15" y="19.5" width="2" height="2.5" fill="#94A3B8"/>
                                        <line x1="-17" y1="6" x2="17" y2="6" stroke="#FFFFFF" stroke-width="0.8" opacity="0.6"/>
                                    </g>
                                </g>
                            </g>
                        </g>

                        <!-- 6. FOREGROUND WATER WAVES & BERTH FOAM (Ombak Laut Depan) -->
                        <g class="anim-wave-front">
                            <path d="M-20 178 Q 50 173, 120 178 T 260 178 T 400 178 T 540 178 T 680 178 L 680 240 L -20 240 Z" fill="url(#seaGrad1)"/>
                            <path d="M-20 178 Q 50 173, 120 178 T 260 178 T 400 178 T 540 178 T 680 178" stroke="#93C5FD" stroke-width="1.2" fill="none" opacity="0.8"/>
                        </g>
                    </svg>
                </div>
            </div>

            <!-- Footer Identitas Perusahaan -->
            <div class="text-xs text-slate-500 pt-4 border-t border-slate-900/60 font-mono">
                <span>PT Agung Trans Solusindo</span>
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

        // ==============================================================
        // REALISTIC CONTAINER PORT LOADING SIMULATION (TRUCK -> CRANE -> SHIP)
        // Akumulasi nyata 1 hingga 10 kontainer bertumpuk di kapal, lalu reset
        // ==============================================================
        (function() {
            const ctnData = [
                { colX: 204, tierY: 125, color: '#EA580C' }, // 1: Kolom 1, Tier 1 (Hapag Orange)
                { colX: 242, tierY: 125, color: '#0284C7' }, // 2: Kolom 2, Tier 1 (Maersk Blue)
                { colX: 280, tierY: 125, color: '#065F46' }, // 3: Kolom 3, Tier 1 (Evergreen Green)
                { colX: 318, tierY: 125, color: '#1E3A8A' }, // 4: Kolom 4, Tier 1 (CMA CGM Navy)
                { colX: 204, tierY: 108, color: '#BE185D' }, // 5: Kolom 1, Tier 2 (ONE Magenta)
                { colX: 242, tierY: 108, color: '#F8FAFC' }, // 6: Kolom 2, Tier 2 (White Reefer)
                { colX: 280, tierY: 108, color: '#D97706' }, // 7: Kolom 3, Tier 2 (Yang Ming Gold)
                { colX: 318, tierY: 108, color: '#0369A1' }, // 8: Kolom 4, Tier 2 (COSCO Cyan)
                { colX: 242, tierY: 91,  color: '#B45309' }, // 9: Kolom 2, Tier 3 (MSC Mustard)
                { colX: 280, tierY: 91,  color: '#0284C7' }, // 10: Kolom 3, Tier 3 (Maersk Top Stack)
            ];

            const dockTrolleyX = 68;
            const highSpreaderY = 56;
            const truckSpreaderY = 127;

            let currentCtn = 0; // 0 to 9
            let cycleStart = null;
            let lastTimestamp = null;
            let isResetting = false;
            let resetStartTime = 0;

            const cycleDuration = 6000; // 6.0 detik per siklus kontainer
            const resetPause = 3200;    // 3.2 detik jeda menikmati 10 kontainer penuh sebelum reset

            // DOM Elements
            const trolleyGroup = document.getElementById('craneTrolleyGroup');
            const spreaderGroup = document.getElementById('spreaderGroup');
            const cableL = document.getElementById('cableL');
            const cableR = document.getElementById('cableR');
            const spreaderCtn = document.getElementById('spreaderCtn');
            const spreaderCtnRect = document.getElementById('spreaderCtnRect');
            const twistlockL = document.getElementById('twistlockL');
            const twistlockR = document.getElementById('twistlockR');
            const truckGroup = document.getElementById('terminalTruckGroup');
            const truckCtnGroup = document.getElementById('truckCtnGroup');
            const truckCtnRect = document.getElementById('truckCtnRect');

            if (!trolleyGroup || !spreaderGroup || !truckGroup) return;

            function easeInOutQuad(t) {
                return t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t;
            }
            function easeOutQuad(t) {
                return t * (2 - t);
            }
            function easeInQuad(t) {
                return t * t;
            }

            function setSpreaderY(y) {
                spreaderGroup.setAttribute('transform', 'translate(0, ' + y + ')');
                cableL.setAttribute('y2', y);
                cableR.setAttribute('y2', y);
            }

            function setTrolleyX(x) {
                trolleyGroup.setAttribute('transform', 'translate(' + x + ', 0)');
            }

            function setTruckOffset(dx) {
                truckGroup.setAttribute('transform', 'translate(' + dx + ', 0)');
            }

            function clearShipContainers() {
                for (let i = 1; i <= 10; i++) {
                    const el = document.getElementById('shipCtn' + i);
                    if (el) el.style.display = 'none';
                }
            }

            function step(timestamp) {
                if (!cycleStart) {
                    cycleStart = timestamp;
                    lastTimestamp = timestamp;
                }

                // Antisipasi jeda tab background
                if (timestamp - lastTimestamp > 500) {
                    cycleStart = timestamp;
                }
                lastTimestamp = timestamp;

                // Jeda saat 10 kontainer penuh sebelum reset
                if (isResetting) {
                    const resetElapsed = timestamp - resetStartTime;
                    if (resetElapsed > resetPause) {
                        isResetting = false;
                        currentCtn = 0;
                        clearShipContainers();
                        cycleStart = timestamp;
                    }
                    requestAnimationFrame(step);
                    return;
                }

                const elapsed = timestamp - cycleStart;
                const data = ctnData[currentCtn];
                const targetShipX = data.colX;
                const targetShipSpreaderY = data.tierY - 5; // Spreader bertengger tepat di atas kontainer

                // -------------------------------------------------------------
                // TAHAPAN SIKLUS REALISTIS (0 - 6000ms):
                // -------------------------------------------------------------

                // 1. Truk baru masuk membawa kontainer k (0 - 1100ms)
                if (elapsed < 1100) {
                    const t = easeOutQuad(elapsed / 1100);
                    setTruckOffset(-85 * (1 - t));
                    truckGroup.style.opacity = '1';
                    truckCtnGroup.style.display = 'block';
                    truckCtnRect.setAttribute('fill', data.color);

                    // Trolley menunggu di dermaga, spreader posisi tinggi
                    setTrolleyX(dockTrolleyX);
                    setSpreaderY(highSpreaderY);
                    spreaderCtn.style.display = 'none';
                    if (twistlockL) { twistlockL.setAttribute('fill', '#10B981'); twistlockR.setAttribute('fill', '#10B981'); }
                }
                // 2. Spreader turun menjemput kontainer di sasis truk (1100 - 1800ms)
                else if (elapsed < 1800) {
                    const t = easeInOutQuad((elapsed - 1100) / 700);
                    setTruckOffset(0);
                    setTrolleyX(dockTrolleyX);
                    const y = highSpreaderY + (truckSpreaderY - highSpreaderY) * t;
                    setSpreaderY(y);
                    spreaderCtn.style.display = 'none';
                    truckCtnGroup.style.display = 'block';
                }
                // 3. Twistlock mengunci kontainer (1800 - 2000ms)
                else if (elapsed < 2000) {
                    setTruckOffset(0);
                    setTrolleyX(dockTrolleyX);
                    setSpreaderY(truckSpreaderY);
                    truckCtnGroup.style.display = 'none'; // Kontainer berpindah ke spreader
                    spreaderCtn.style.display = 'block';
                    spreaderCtnRect.setAttribute('fill', data.color);
                    if (twistlockL) { twistlockL.setAttribute('fill', '#F59E0B'); twistlockR.setAttribute('fill', '#F59E0B'); }
                }
                // 4. Spreader mengangkat kontainer ke atas geladak (2000 - 2800ms)
                else if (elapsed < 2800) {
                    const t = easeInOutQuad((elapsed - 2000) / 800);
                    setTruckOffset(0);
                    setTrolleyX(dockTrolleyX);
                    const y = truckSpreaderY - (truckSpreaderY - highSpreaderY) * t;
                    setSpreaderY(y);
                    truckCtnGroup.style.display = 'none';
                    spreaderCtn.style.display = 'block';
                    if (twistlockL) { twistlockL.setAttribute('fill', '#10B981'); twistlockR.setAttribute('fill', '#10B981'); }
                }
                // 5. Troli meluncur ke atas kapal membawa kontainer; Truk kosong jalan keluar (2800 - 4400ms)
                else if (elapsed < 4400) {
                    const t = easeInOutQuad((elapsed - 2800) / 1600);
                    const x = dockTrolleyX + (targetShipX - dockTrolleyX) * t;
                    setTrolleyX(x);
                    setSpreaderY(highSpreaderY);
                    spreaderCtn.style.display = 'block';

                    // Truk kosong melaju keluar ke kanan dermaga
                    const truckT = easeInQuad((elapsed - 2800) / 1600);
                    setTruckOffset(truckT * 140);
                    truckGroup.style.opacity = String(Math.max(0, 1 - truckT * 1.5));
                }
                // 6. Spreader menurunkan kontainer ke dalam kompartemen kapal (4400 - 5200ms)
                else if (elapsed < 5200) {
                    const t = easeInOutQuad((elapsed - 4400) / 800);
                    setTrolleyX(targetShipX);
                    const y = highSpreaderY + (targetShipSpreaderY - highSpreaderY) * t;
                    setSpreaderY(y);
                    spreaderCtn.style.display = 'block';
                }
                // 7. Twistlock dilepas; Kontainer resmi mendarat dan menumpuk di kapal (5200 - 5500ms)
                else if (elapsed < 5500) {
                    setTrolleyX(targetShipX);
                    setSpreaderY(targetShipSpreaderY);
                    spreaderCtn.style.display = 'none';

                    // Kontainer muncul permanen di lambung kapal dan ikut berayun bersama kapal
                    const placedCtn = document.getElementById('shipCtn' + (currentCtn + 1));
                    if (placedCtn) placedCtn.style.display = 'block';
                }
                // 8. Spreader kosong ditarik naik kembali ke atas (5500 - 6000ms)
                else if (elapsed < 6000) {
                    const t = easeInOutQuad((elapsed - 5500) / 500);
                    setTrolleyX(targetShipX);
                    const y = targetShipSpreaderY - (targetShipSpreaderY - highSpreaderY) * t;
                    setSpreaderY(y);
                    spreaderCtn.style.display = 'none';
                }
                // Siklus selesai -> Lanjut ke kontainer berikutnya
                else {
                    currentCtn++;

                    if (currentCtn >= 10) {
                        // Telah mencapai 10 kontainer penuh!
                        isResetting = true;
                        resetStartTime = timestamp;
                        setTrolleyX(dockTrolleyX);
                        setSpreaderY(highSpreaderY);
                    } else {
                        // Persiapan siklus kontainer berikutnya
                        cycleStart = timestamp;
                    }
                }

                requestAnimationFrame(step);
            }

            // Jalankan animasi
            requestAnimationFrame(step);
        })();
    </script>
</body>
</html>
