<!DOCTYPE html>
<html lang="id" class="h-full" x-data="{
    darkMode: localStorage.getItem('genie-portal-theme') === 'dark' || (!('genie-portal-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
    copiedText: null,
    copyToClipboard(text, label) {
        navigator.clipboard.writeText(text);
        this.copiedText = label;
        setTimeout(() => { if (this.copiedText === label) this.copiedText = null; }, 2500);
    },
    toggleTheme() {
        this.darkMode = !this.darkMode;
        localStorage.setItem('genie-portal-theme', this.darkMode ? 'dark' : 'light');
        if (this.darkMode) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
    }
}" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Pelacakan Proyek {{ $shipment->project_no }} — {{ $client->nama_perusahaan ?? $client->name ?? 'Portal Klien' }}</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        apple: {
                            blue: '#007AFF',
                            green: '#34C759',
                            orange: '#FF9500',
                            red: '#FF3B30',
                            purple: '#AF52DE',
                            indigo: '#5856D6',
                            teal: '#30B0C7',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Lucide Icons & Alpine.js -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Inter", sans-serif;
            background-color: #F2F2F7;
        }
        .dark body {
            background-color: #0E0E10;
        }
        .font-mono {
            font-family: "JetBrains Mono", "SF Mono", monospace;
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen flex flex-col text-slate-800 dark:text-slate-100 transition-colors duration-200">

    <!-- 1. APPLE STYLE GLASS HEADER -->
    <header class="sticky top-0 z-30 bg-white/80 dark:bg-[#1C1C1E]/80 backdrop-blur-xl border-b border-black/[0.06] dark:border-white/[0.08] transition-all">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-3">
            
            <!-- Brand & Client Identity -->
            <div class="flex items-center space-x-3 min-w-0">
                <div class="w-9 h-9 rounded-[11px] bg-gradient-to-tr from-[#007AFF] to-[#5856D6] flex items-center justify-center text-white font-bold shadow-[0_2px_8px_rgba(0,122,255,0.35)] flex-shrink-0">
                    <i data-lucide="ship" class="w-4 h-4 text-white"></i>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center space-x-1.5 leading-none">
                        <span class="font-bold text-[14px] text-slate-900 dark:text-white tracking-tight">GENIE PORTAL</span>
                        <span class="text-slate-300 dark:text-slate-600">&bull;</span>
                        <span class="text-[12px] font-semibold text-[#007AFF] dark:text-[#0A84FF] truncate">{{ $client->nama_perusahaan ?? $client->name ?? 'Portal Pelacakan Kargo' }}</span>
                    </div>
                    <div class="text-[11px] text-slate-400 dark:text-slate-400 font-medium truncate mt-0.5">PT Agung Trans Solusindo &bull; Forwarding & Customs</div>
                </div>
            </div>

            <!-- Actions: Live Countdown Timer & Utility Tools -->
            <div class="flex items-center space-x-2.5">
                
                <!-- Live Expiry Badge (JS Countdown) -->
                <div x-data="{
                    expiresAt: new Date('{{ $shipment->portal_expires_at->toIso8601String() }}').getTime(),
                    timeRemaining: '',
                    isExpired: false,
                    updateTimer() {
                        const now = new Date().getTime();
                        const diff = this.expiresAt - now;
                        if (diff <= 0) {
                            this.timeRemaining = 'Kedaluwarsa';
                            this.isExpired = true;
                            return;
                        }
                        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                        const mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                        const secs = Math.floor((diff % (1000 * 60)) / 1000);
                        
                        if (days > 0) {
                            this.timeRemaining = `${days}h ${hours}j ${mins}m`;
                        } else {
                            this.timeRemaining = `${hours}j ${mins}m ${secs}s`;
                        }
                    }
                }" x-init="updateTimer(); setInterval(() => updateTimer(), 1000);"
                   class="flex items-center">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-[#34C759]/10 text-[#248A3D] dark:bg-[#34C759]/20 dark:text-[#32D74B] text-[11px] font-mono font-medium border border-[#34C759]/20">
                        <span class="w-2 h-2 rounded-full bg-[#34C759] animate-pulse"></span>
                        <span class="hidden sm:inline">Tautan Aktif &bull; Sisa</span>
                        <span class="sm:hidden">Tautan Aktif &bull;</span>
                        <strong x-text="timeRemaining" class="font-bold">...</strong>
                    </span>
                </div>

                <!-- Copy Link Button -->
                <button type="button" 
                        @click="copyToClipboard(window.location.href, 'link')"
                        class="p-2 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-black/[0.08] dark:hover:bg-white/[0.1] transition-all relative cursor-pointer"
                        title="Salin Tautan Halaman Ini">
                    <i data-lucide="share-2" class="w-4 h-4"></i>
                    <span x-show="copiedText === 'link'" x-cloak class="absolute -bottom-8 right-0 text-[10px] bg-slate-900 text-white px-2 py-0.5 rounded shadow whitespace-nowrap">Tersalin!</span>
                </button>

                <!-- Theme Toggle Button -->
                <button type="button" 
                        @click="toggleTheme()" 
                        class="p-2 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-black/[0.08] dark:hover:bg-white/[0.1] transition-all cursor-pointer"
                        title="Ganti Mode Gelap/Terang">
                    <i :data-lucide="darkMode ? 'sun' : 'moon'" class="w-4 h-4 text-[#FF9500]"></i>
                </button>

            </div>
        </div>
    </header>

    <!-- 2. MAIN CONTENT -->
    <main class="flex-1 max-w-6xl w-full mx-auto px-4 sm:px-6 py-6 sm:py-8 space-y-6">

        <!-- Flash Messages -->
        @if(session('success'))
        <div class="p-4 rounded-[16px] bg-[#34C759]/12 border border-[#34C759]/25 text-[#248A3D] dark:text-[#32D74B] text-[13px] flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-3">
                <i data-lucide="check-circle" class="w-5 h-5 flex-shrink-0"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="p-4 rounded-[16px] bg-[#FF3B30]/12 border border-[#FF3B30]/25 text-[#C41E17] dark:text-[#FF453A] text-[13px] flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-3">
                <i data-lucide="alert-triangle" class="w-5 h-5 flex-shrink-0"></i>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        </div>
        @endif

        <!-- Callout 1: Live Operational Update & Notes -->
        @if(!empty($shipment->catatan))
        <div class="p-4 sm:p-5 rounded-[20px] bg-gradient-to-r from-blue-50/90 to-indigo-50/70 dark:from-[#007AFF]/10 dark:to-[#5856D6]/10 border border-[#007AFF]/20 shadow-xs flex items-start gap-4">
            <div class="w-10 h-10 rounded-[12px] bg-[#007AFF] text-white flex items-center justify-center shrink-0 shadow-xs">
                <i data-lucide="bell" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                        Laporan Terkini Tim Operasional
                    </span>
                    <span class="text-[11px] text-slate-400 font-mono">{{ $shipment->updated_at->format('d/m/Y H:i') }} WIB</span>
                </div>
                <p class="text-[13px] sm:text-[14px] text-slate-800 dark:text-slate-200 font-medium mt-1 leading-relaxed">
                    {{ $shipment->catatan }}
                </p>
            </div>
        </div>
        @endif

        <!-- Card 1: HERO IDENTITAS SHIPMENT & VESSEL VOYAGE ROUTE -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-[22px] border border-black/[0.06] dark:border-white/[0.08] shadow-sm p-5 sm:p-7 space-y-6">
            
            <!-- Header Identity Bar -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-5 border-b border-black/[0.06] dark:border-white/[0.06]">
                <div>
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h1 class="text-[22px] sm:text-[24px] font-bold text-slate-900 dark:text-white tracking-tight font-mono">
                            {{ $shipment->project_no }}
                        </h1>
                        <span class="px-3 py-1 rounded-full text-[11px] font-bold font-mono tracking-wide {{ $shipment->jenis_layanan === 'EKSPORT_BC30' ? 'bg-[#FF9500]/15 text-[#FF9500]' : 'bg-[#007AFF]/15 text-[#007AFF] dark:text-[#0A84FF]' }}">
                            {{ $shipment->jenis_layanan === 'EKSPORT_BC30' ? 'EKSPOR BC 3.0' : 'IMPOR BC 2.0' }}
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-[13px] text-slate-500 dark:text-slate-400 mt-1.5">
                        <span>No. B/L: <strong class="text-slate-800 dark:text-slate-200 font-mono">{{ $shipment->nomor_bl_awb ?? '-' }}</strong></span>
                        <span class="text-slate-300 dark:text-slate-600">&bull;</span>
                        <span>No. Aju: <strong class="text-slate-800 dark:text-slate-200 font-mono">{{ $shipment->nomor_aju }}</strong></span>
                    </div>
                </div>

                <!-- Customs Channel & Milestone Status Pill -->
                <div class="flex flex-wrap items-center gap-2.5">
                    @php
                        $channel = strtoupper($shipment->customs_channel ?? $shipment->ceisaDocument?->jalur ?? '');
                    @endphp
                    @if($channel)
                    <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-[12px] text-[12px] font-bold {{ $channel === 'HIJAU' ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#32D74B]' : ($channel === 'KUNING' ? 'bg-[#FF9500]/15 text-[#B25E00] dark:text-[#FF9F0A]' : 'bg-[#FF3B30]/15 text-[#C41E17] dark:text-[#FF453A]') }}">
                        <span class="w-2.5 h-2.5 rounded-full {{ $channel === 'HIJAU' ? 'bg-[#34C759]' : ($channel === 'KUNING' ? 'bg-[#FF9500]' : 'bg-[#FF3B30]') }} animate-pulse"></span>
                        <span>JALUR {{ $channel }}</span>
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[12px] bg-slate-100 dark:bg-white/[0.06] text-slate-600 dark:text-slate-300 text-[12px] font-medium">
                        <i data-lucide="clock" class="w-3.5 h-3.5 text-slate-400"></i>
                        <span>Menunggu SPPB</span>
                    </span>
                    @endif

                    @php
                        $statusLabels = [
                            'DOC_COLLECTED' => 'Kumpul Berkas',
                            'DRAFT_PIB' => 'Draft PIB Siap',
                            'BILLING_ISSUED' => 'Billing MPN G3 Terbit',
                            'BILLING_PAID' => 'Billing Lunas',
                            'DO_PENDING' => 'Proses Tebus DO',
                            'DO_RELEASED' => 'DO Rilis',
                            'SPPB_ISSUED' => 'SPPB Bea Cukai Terbit',
                            'GATE_OUT' => 'Kargo Keluar TPS',
                            'ON_DELIVERY' => 'Truk Menuju Gudang',
                            'DELIVERED' => 'Tiba di Gudang (Selesai)',
                        ];
                        $cleanStatus = $statusLabels[$shipment->status_tahap] ?? str_replace('_', ' ', $shipment->status_tahap);
                    @endphp
                    <span class="px-3.5 py-1.5 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF] text-[12px] font-bold font-mono">
                        {{ $cleanStatus }}
                    </span>
                </div>
            </div>

            <!-- Visual Voyage Route Map (POL -> POD) -->
            <div class="p-4 sm:p-5 rounded-[16px] bg-slate-50 dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06]">
                <div class="grid grid-cols-1 md:grid-cols-3 items-center gap-4 text-center md:text-left">
                    
                    <!-- Port of Loading -->
                    <div class="space-y-1">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-400">Pelabuhan Muat (POL)</span>
                        <div class="font-bold text-[14px] text-slate-900 dark:text-white">{{ $shipment->pelabuhan_muat ?? 'Shanghai Port (CNSHA)' }}</div>
                        <span class="text-[11px] text-slate-500">Shipper Luar Negeri</span>
                    </div>

                    <!-- Vessel Voyage Center Track -->
                    <div class="flex flex-col items-center justify-center space-y-1.5 px-4">
                        <div class="w-full flex items-center justify-center space-x-2 text-slate-300 dark:text-slate-600">
                            <span class="h-0.5 flex-1 bg-slate-200 dark:bg-white/10"></span>
                            <span class="w-8 h-8 rounded-full bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.08] flex items-center justify-center text-[#007AFF] shadow-2xs">
                                <i data-lucide="navigation" class="w-4 h-4 rotate-90"></i>
                            </span>
                            <span class="h-0.5 flex-1 bg-slate-200 dark:bg-white/10"></span>
                        </div>
                        <div class="text-[12px] font-bold text-slate-800 dark:text-slate-200 font-mono">
                            {{ $shipment->nama_kapal ?? 'MV WAN HAI 315' }} <span class="text-slate-400">&bull;</span> {{ $shipment->voyage_no ?? 'V.042N' }}
                        </div>
                        <div class="text-[11px] text-[#007AFF] font-medium">
                            Pelayaran: {{ $shipment->shippingVendor->name ?? 'Ocean Carrier' }}
                        </div>
                    </div>

                    <!-- Port of Discharge -->
                    <div class="space-y-1 md:text-right">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-400">Pelabuhan Bongkar (POD)</span>
                        <div class="font-bold text-[14px] text-slate-900 dark:text-white">{{ $shipment->pelabuhan_bongkar ?? 'KPU Tanjung Priok (IDTPP)' }}</div>
                        <span class="text-[11px] text-slate-500">
                            ETA: <strong class="text-slate-800 dark:text-slate-200 font-mono">{{ $shipment->eta_date ? $shipment->eta_date->format('d/m/Y') : 'Segera Tiba' }}</strong>
                        </span>
                    </div>

                </div>
            </div>

            <!-- Bento 4-Cards: Cargo Technical Specs -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 text-[12px]">
                
                <!-- 1. Kontainer -->
                <div class="p-3.5 sm:p-4 rounded-[16px] bg-slate-50 dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] space-y-1">
                    <div class="flex items-center space-x-2 text-slate-400 dark:text-slate-400 text-[11px]">
                        <i data-lucide="container" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                        <span>Muatan Kontainer</span>
                    </div>
                    <div class="font-bold text-slate-900 dark:text-white font-mono text-[14px]">
                        {{ $shipment->total_kontainer }} x Kontainer
                    </div>
                    <div class="text-[11px] text-slate-500 font-mono truncate" title="{{ $shipment->nomor_kontainer }}">
                        {{ $shipment->nomor_kontainer ?? 'FCL' }}
                    </div>
                </div>

                <!-- 2. Berat & Kemasan -->
                <div class="p-3.5 sm:p-4 rounded-[16px] bg-slate-50 dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] space-y-1">
                    <div class="flex items-center space-x-2 text-slate-400 dark:text-slate-400 text-[11px]">
                        <i data-lucide="scale" class="w-3.5 h-3.5 text-[#5856D6]"></i>
                        <span>Berat & Kemasan</span>
                    </div>
                    <div class="font-bold text-slate-900 dark:text-white font-mono text-[14px]">
                        {{ number_format($shipment->total_netto, 2) }} kg
                    </div>
                    <div class="text-[11px] text-slate-500 truncate">
                        {{ $shipment->total_kemasan }} {{ $shipment->jenis_kemasan ?? 'Colli' }} &bull; Gross: {{ number_format($shipment->total_bruto, 2) }}kg
                    </div>
                </div>

                <!-- 3. Nilai Pabean (CIF) -->
                <div class="p-3.5 sm:p-4 rounded-[16px] bg-slate-50 dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] space-y-1">
                    <div class="flex items-center space-x-2 text-slate-400 dark:text-slate-400 text-[11px]">
                        <i data-lucide="badge-dollar-sign" class="w-3.5 h-3.5 text-[#34C759]"></i>
                        <span>Nilai Pabean (CIF)</span>
                    </div>
                    <div class="font-bold text-slate-900 dark:text-white font-mono text-[14px]">
                        {{ $shipment->kode_valuta ?? 'USD' }} {{ number_format($shipment->total_nilai_valuta, 2) }}
                    </div>
                    <div class="text-[11px] text-slate-500 font-mono truncate">
                        Rp {{ number_format($shipment->total_nilai_idr, 0, ',', '.') }}
                    </div>
                </div>

                <!-- 4. Free Time Pelayaran -->
                <div class="p-3.5 sm:p-4 rounded-[16px] bg-slate-50 dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] space-y-1">
                    <div class="flex items-center space-x-2 text-slate-400 dark:text-slate-400 text-[11px]">
                        <i data-lucide="calendar-clock" class="w-3.5 h-3.5 text-[#FF9500]"></i>
                        <span>Free Time Kontainer</span>
                    </div>
                    <div class="font-bold text-slate-900 dark:text-white font-mono text-[14px]">
                        {{ $shipment->free_time_days ?? 7 }} Hari Bebas
                    </div>
                    <div class="text-[11px] text-slate-500 font-mono truncate">
                        Batas: {{ $shipment->free_time_expired_at ? $shipment->free_time_expired_at->format('d/m/Y') : '-' }}
                    </div>
                </div>

            </div>

        </div>

        <!-- Card 2: 6-FASE CONNECTED MILESTONES STEPPER -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-[22px] border border-black/[0.06] dark:border-white/[0.08] shadow-sm p-5 sm:p-7 space-y-5">
            <div class="flex items-center justify-between pb-4 border-b border-black/[0.06] dark:border-white/[0.06]">
                <div>
                    <h2 class="text-[16px] font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                        <i data-lucide="route" class="w-4 h-4 text-[#007AFF]"></i>
                        <span>Tahapan Perjalanan Kargo</span>
                    </h2>
                    <p class="text-[12px] text-slate-400 mt-0.5">Pantau kemajuan pengurusan kargo Anda dari pelabuhan hingga tiba di gudang tujuan.</p>
                </div>
                <div class="text-right">
                    <span class="text-[11px] font-bold font-mono px-2.5 py-1 rounded-full bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF]">
                        Fase {{ $activePhase + 1 }} dari 6
                    </span>
                </div>
            </div>

            <!-- Connected Stepper Track -->
            @php
                $phases = [
                    [
                        'title' => 'Pengumpulan Dokumen',
                        'desc' => 'Invoice, Packing List & B/L diterima dan diverifikasi.',
                        'icon' => 'files',
                    ],
                    [
                        'title' => 'Pabean & Pajak',
                        'desc' => 'Draft PIB disubmit & Kode Billing MPN G3 terbit.',
                        'icon' => 'receipt',
                    ],
                    [
                        'title' => 'DO Pelayaran',
                        'desc' => 'Penebusan Delivery Order ke agen pelayaran.',
                        'icon' => 'clipboard-check',
                    ],
                    [
                        'title' => 'Kedatangan & SPPB',
                        'desc' => 'Kapal sandar & SPPB Bea Cukai terbit resmi.',
                        'icon' => 'shield-check',
                    ],
                    [
                        'title' => 'Pengiriman Darat',
                        'desc' => 'Gate Out TPS & Truk kontainer bergerak ke gudang.',
                        'icon' => 'truck',
                    ],
                    [
                        'title' => 'Selesai & Lunas',
                        'desc' => 'Kargo diterima di gudang klien (POD selesai).',
                        'icon' => 'check-check',
                    ],
                ];
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 pt-1">
                @foreach($phases as $pIdx => $phase)
                @php
                    $isDone = $pIdx < $activePhase;
                    $isCurrent = $pIdx === $activePhase;
                @endphp
                <div class="p-4 rounded-[16px] border transition-all flex flex-col justify-between {{ $isCurrent ? 'bg-[#007AFF]/8 border-[#007AFF]/35 shadow-xs' : ($isDone ? 'bg-slate-50/80 dark:bg-white/[0.02] border-black/[0.05] dark:border-white/[0.05]' : 'bg-transparent border-dashed border-black/[0.08] dark:border-white/[0.08] opacity-60') }}">
                    <div>
                        <!-- Top Icon & Step Number -->
                        <div class="flex items-center justify-between mb-2">
                            <span class="w-7 h-7 rounded-[9px] flex items-center justify-center text-[12px] font-bold font-mono {{ $isCurrent ? 'bg-[#007AFF] text-white shadow-xs' : ($isDone ? 'bg-[#34C759] text-white' : 'bg-slate-200 dark:bg-white/10 text-slate-500 dark:text-slate-400') }}">
                                @if($isDone)
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                @else
                                    {{ $pIdx + 1 }}
                                @endif
                            </span>

                            @if($isCurrent)
                            <span class="text-[9px] font-bold px-2 py-0.5 rounded-full bg-[#007AFF] text-white font-mono animate-pulse">
                                AKTIF
                            </span>
                            @elseif($isDone)
                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full bg-[#34C759]/15 text-[#248A3D] dark:text-[#32D74B] font-mono">
                                SELESAI
                            </span>
                            @endif
                        </div>

                        <!-- Phase Name -->
                        <h3 class="text-[13px] font-bold text-slate-900 dark:text-white leading-snug">
                            {{ $phase['title'] }}
                        </h3>

                        <!-- Phase Description -->
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                            {{ $phase['desc'] }}
                        </p>
                    </div>

                    <!-- Progress Bar Indicator inside Step -->
                    <div class="mt-3 pt-2 border-t border-black/[0.04] dark:border-white/[0.04]">
                        <div class="h-1 rounded-full w-full {{ $isDone ? 'bg-[#34C759]' : ($isCurrent ? 'bg-[#007AFF]' : 'bg-slate-200 dark:bg-white/10') }}"></div>
                    </div>
                </div>
                @endforeach
            </div>

        </div>

        <!-- Card 3 & 4: 2-COLUMN LAYOUT FOR DOCUMENTS & UPLOAD REVISION -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- LEFT COLUMN (7 COLS): RESMI DOKUMEN KEPABEANAN -->
            <div class="lg:col-span-7 bg-white dark:bg-[#1C1C1E] rounded-[22px] border border-black/[0.06] dark:border-white/[0.08] shadow-sm p-5 sm:p-7 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.06]">
                    <div>
                        <h2 class="text-[16px] font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                            <i data-lucide="file-check" class="w-4 h-4 text-[#34C759]"></i>
                            <span>Dokumen Resmi Kepabeanan</span>
                        </h2>
                        <p class="text-[11px] text-slate-400 mt-0.5">Dokumen resmi otoritas Bea Cukai dan Pelayaran untuk keperluan arsip dan perpajakan.</p>
                    </div>
                </div>

                <!-- 1. Special Spotlight: Billing MPN G3 Payment Card -->
                @php
                    $billingCode = $shipment->ceisaDocument?->kode_billing ?? '019283746501928';
                    $totalBilling = $shipment->ceisaDocument?->total_billing ?? $shipment->total_nilai_idr * 0.11;
                @endphp
                <div class="p-4 sm:p-5 rounded-[18px] bg-gradient-to-tr from-slate-900 to-slate-800 text-white shadow-md space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-6 h-6 rounded-full bg-[#007AFF] text-white flex items-center justify-center text-[11px] font-bold">MPN</span>
                            <span class="text-[12px] font-semibold text-slate-200">KODE BILLING PABEAN G3</span>
                        </div>
                        <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-white/10 text-slate-300">
                            {{ $shipment->ceisaDocument?->status_billing === 'PAID' ? 'LUNAS' : 'SIAP DISETOR' }}
                        </span>
                    </div>

                    <!-- Kode Billing Besar & Tombol Salin -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white/5 p-3 rounded-[12px] border border-white/10">
                        <div>
                            <span class="text-[10px] text-slate-400 block uppercase tracking-wider">Kode Setor Bank (15 Digit)</span>
                            <div class="text-[18px] sm:text-[20px] font-bold font-mono tracking-wider text-[#32D74B]">
                                {{ $billingCode }}
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" 
                                    @click="copyToClipboard('{{ $billingCode }}', 'billing')"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[8px] bg-white/10 hover:bg-white/20 text-white text-[11px] font-semibold transition-all cursor-pointer">
                                <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                <span x-text="copiedText === 'billing' ? 'Tersalin!' : 'Salin Kode'">Salin Kode</span>
                            </button>

                            <a href="{{ route('pdf.billing', $shipment->id) }}" target="_blank" 
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[8px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[11px] font-semibold transition-all">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                <span>PDF</span>
                            </a>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1">
                        <span>Total Tagihan: <strong class="text-white font-mono">Rp {{ number_format($totalBilling, 0, ',', '.') }}</strong></span>
                        <span>Batas Waktu: <strong class="text-slate-300 font-mono">{{ $shipment->ceisaDocument?->tgl_jatuh_tempo_billing ? \Carbon\Carbon::parse($shipment->ceisaDocument->tgl_jatuh_tempo_billing)->format('d/m/Y') : '3 Hari Kerja' }}</strong></span>
                    </div>
                </div>

                <!-- Document List Grid -->
                <div class="space-y-2.5">
                    
                    <!-- Dokumen 1: Pemberitahuan Pabean (PIB / PEB) -->
                    <div class="p-3.5 rounded-[14px] bg-slate-50 dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] flex items-center justify-between gap-3">
                        <div class="flex items-center space-x-3 min-w-0">
                            <div class="w-9 h-9 rounded-[10px] bg-[#007AFF]/12 text-[#007AFF] flex items-center justify-center shrink-0">
                                <i data-lucide="file-text" class="w-4 h-4"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[13px] font-bold text-slate-900 dark:text-white truncate">
                                    {{ $shipment->jenis_layanan === 'EKSPORT_BC30' ? 'Dokumen PEB (BC 3.0)' : 'Dokumen PIB (BC 2.0)' }}
                                </div>
                                <div class="text-[11px] text-slate-400 font-mono truncate">
                                    No. Aju: {{ $shipment->nomor_aju }}
                                </div>
                            </div>
                        </div>

                        <a href="{{ $shipment->jenis_layanan === 'EKSPORT_BC30' ? route('pdf.peb', $shipment->id) : route('pdf.pib', $shipment->id) }}" 
                           target="_blank" 
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[8px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[#007AFF] dark:text-[#0A84FF] hover:bg-[#007AFF] hover:text-white text-[11px] font-semibold transition-all">
                            <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                            <span>Cetak PDF</span>
                        </a>
                    </div>

                    <!-- Dokumen 2: Delivery Order (DO) Pelayaran -->
                    <div class="p-3.5 rounded-[14px] bg-slate-50 dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] flex items-center justify-between gap-3">
                        <div class="flex items-center space-x-3 min-w-0">
                            <div class="w-9 h-9 rounded-[10px] bg-[#FF9500]/12 text-[#FF9500] flex items-center justify-center shrink-0">
                                <i data-lucide="clipboard-check" class="w-4 h-4"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[13px] font-bold text-slate-900 dark:text-white truncate">
                                    Delivery Order (DO) Pelayaran
                                </div>
                                <div class="text-[11px] text-slate-400 truncate">
                                    {{ $shipment->status_do === 'DO_RELEASED' ? 'DO Telah Rilis Resmi dari Agen Pelayaran' : 'Proses Penebusan DO Pelayaran' }}
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('pdf.do-request', $shipment->id) }}" 
                           target="_blank" 
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[8px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-slate-700 dark:text-slate-300 hover:bg-slate-900 hover:text-white text-[11px] font-semibold transition-all">
                            <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                            <span>Berkas DO</span>
                        </a>
                    </div>

                    <!-- Dokumen 3: Bukti Serah Terima Kargo (POD) -->
                    <div class="p-3.5 rounded-[14px] bg-slate-50 dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] flex items-center justify-between gap-3">
                        <div class="flex items-center space-x-3 min-w-0">
                            <div class="w-9 h-9 rounded-[10px] bg-[#34C759]/12 text-[#34C759] flex items-center justify-center shrink-0">
                                <i data-lucide="package-check" class="w-4 h-4"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-[13px] font-bold text-slate-900 dark:text-white truncate">
                                    Surat Jalan & Bukti Serah Terima (POD)
                                </div>
                                <div class="text-[11px] text-slate-400 truncate">
                                    {{ $shipment->pod_signed_at ? 'Telah Diterima di Gudang Penerima' : 'Menunggu Pengiriman Armada Truk' }}
                                </div>
                            </div>
                        </div>

                        <span class="text-[11px] font-medium text-slate-400 px-3 py-1 rounded bg-slate-100 dark:bg-white/5">
                            {{ $shipment->pod_signed_at ? 'Terverifikasi' : 'Menunggu Pengiriman' }}
                        </span>
                    </div>

                </div>
            </div>

            <!-- RIGHT COLUMN (5 COLS): UNGGAH BERKAS TAMBAHAN & DOKUMEN KLIEN -->
            <div class="lg:col-span-5 bg-white dark:bg-[#1C1C1E] rounded-[22px] border border-black/[0.06] dark:border-white/[0.08] shadow-sm p-5 sm:p-7 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.06]">
                    <div>
                        <h2 class="text-[16px] font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                            <i data-lucide="upload-cloud" class="w-4 h-4 text-[#007AFF]"></i>
                            <span>Unggah Berkas Tambahan</span>
                        </h2>
                        <p class="text-[11px] text-slate-400 mt-0.5">Kirimkan revisi Invoice, Packing List, atau Form E ke sistem kami.</p>
                    </div>
                </div>

                <!-- Form Upload -->
                <form action="{{ route('portal.upload-token', $shipment->portal_token) }}" method="POST" enctype="multipart/form-data" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                            Jenis Dokumen
                        </label>
                        <select name="document_type" required class="w-full h-10 px-3 rounded-[10px] border border-black/10 dark:border-white/10 bg-slate-50 dark:bg-white/5 text-[12px] text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/25">
                            <option value="PACKING_LIST">Packing List (Revisi)</option>
                            <option value="COMMERCIAL_INVOICE">Commercial Invoice (Revisi)</option>
                            <option value="CERTIFICATE_OF_ORIGIN">COO (Form E / Form D / AKFTA)</option>
                            <option value="LARTAS_PERMIT">Izin Teknis / Lartas (PI / LS)</option>
                            <option value="OTHER_SUPPORTING">Dokumen Pendukung Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                            Pilih Berkas (PDF, JPG, PNG, XLS, Maks 15MB)
                        </label>
                        <div class="p-3 rounded-[12px] border border-dashed border-black/15 dark:border-white/15 bg-slate-50 dark:bg-white/5 text-center">
                            <input type="file" name="document_file" required class="w-full text-[12px] text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-[8px] file:border-0 file:text-[11px] file:font-semibold file:bg-[#007AFF] file:text-white file:cursor-pointer hover:file:bg-[#0071E3]">
                        </div>
                    </div>

                    <button type="submit" class="w-full flex items-center justify-center gap-2 py-2.5 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[12px] font-semibold transition-all shadow-xs active:scale-[0.98] cursor-pointer">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        <span>Kirim Berkas ke Tim Dokumen</span>
                    </button>
                </form>

                <!-- Riwayat Berkas Yang Sudah Diunggah -->
                @if($shipment->documents && $shipment->documents->count() > 0)
                <div class="pt-3 border-t border-black/[0.06] dark:border-white/[0.06] space-y-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Berkas Terlampir ({{ $shipment->documents->count() }})</span>
                    <div class="space-y-1.5 max-h-48 overflow-y-auto">
                        @foreach($shipment->documents as $doc)
                        <div class="p-2.5 rounded-[10px] bg-slate-50 dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.05] flex items-center justify-between text-[11px]">
                            <div class="min-w-0 pr-2">
                                <span class="font-bold text-slate-800 dark:text-slate-200 block truncate">{{ $doc->nama_file ?? $doc->jenis_dokumen }}</span>
                                <span class="text-slate-400 text-[10px]">{{ $doc->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold font-mono {{ $doc->status === 'VERIFIED' ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#32D74B]' : 'bg-[#FF9500]/15 text-[#FF9500]' }}">
                                {{ $doc->status ?? 'DITERIMA' }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>

        </div>

        <!-- 3. SECURITY & DEDICATED WHATSAPP ASSISTANCE BAR -->
        <div class="p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4 text-[12px]">
            <div class="flex items-center space-x-3 text-center sm:text-left">
                <div class="w-10 h-10 rounded-[12px] bg-[#34C759]/15 text-[#34C759] flex items-center justify-center shrink-0">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                </div>
                <div>
                    <div class="font-bold text-slate-900 dark:text-white">Pelacakan Kargo Dilindungi Enkripsi Token Aman</div>
                    <div class="text-slate-500 dark:text-slate-400 text-[11px] mt-0.5">
                        Tautan ini terikat khusus untuk proyek {{ $shipment->project_no }}. Masa aktif timer hingga <strong>{{ $shipment->portal_expires_at->format('d/m/Y H:i') }} WIB</strong>.
                    </div>
                </div>
            </div>

            <a href="https://wa.me/6281234567890?text={{ urlencode('Halo Tim Agung Trans Solusindo, saya ingin menanyakan status pengapalan proyek ' . $shipment->project_no . ' (B/L: ' . $shipment->nomor_bl_awb . ').') }}" 
               target="_blank" 
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-[12px] bg-[#34C759] hover:bg-[#2EB84F] text-white text-[12px] font-semibold transition-all shadow-xs active:scale-[0.98] shrink-0">
                <i data-lucide="message-circle" class="w-4 h-4"></i>
                <span>Hubungi Account Manager via WhatsApp</span>
            </a>
        </div>

    </main>

    <!-- 4. FOOTER -->
    <footer class="mt-auto border-t border-black/[0.06] dark:border-white/[0.08] bg-white/60 dark:bg-[#1C1C1E]/60 py-4 text-center text-[11px] text-slate-400 dark:text-slate-500">
        &copy; {{ date('Y') }} PT Agung Trans Solusindo &bull; Sistem Operasional Pengapalan & Kepabeanan Terpadu GENIE ERP
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.lucide) {
                lucide.createIcons();
            }
        });
    </script>
</body>
</html>
