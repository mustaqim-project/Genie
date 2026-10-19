<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Klien - {{ $client->name }} | Genie</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --f: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Plus Jakarta Sans", sans-serif;
            --fd: -apple-system, BlinkMacSystemFont, "SF Pro Display", "Plus Jakarta Sans", sans-serif;
            --ease: cubic-bezier(.25, .1, .25, 1);
        }
        body { font-family: var(--f); background-color: #F8FAFC; color: #1E293B; }
        .mk {
            display: grid; place-items: center; width: 34px; height: 34px; border-radius: 9px;
            background: linear-gradient(180deg, #2997FF, #0071E3);
            color: #fff; font-family: var(--fd); font-weight: 700; font-size: 18px;
        }
        .btn-apple { border-radius: 980px; transition: all .2s var(--ease); }
        .btn-apple:hover { filter: brightness(1.05); transform: scale(1.01); }
    </style>
</head>
<body class="min-h-screen flex flex-col">

    <!-- 1. Apple Style Portal Header Bar -->
    <header class="sticky top-0 z-30 bg-white/90 backdrop-blur-xl border-b border-slate-200/80 px-4 sm:px-8 py-3.5 flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center space-x-3.5">
            <div class="mk">G</div>
            <div>
                <div class="flex items-center space-x-2">
                    <span class="font-extrabold text-sm sm:text-base text-slate-900 tracking-tight">Portal Klien</span>
                    <span class="text-slate-300">|</span>
                    <span class="text-sm font-bold text-sky-700">{{ $client->name }}</span>
                </div>
                <p class="text-[11px] text-slate-400">Akses aman berbasis enkripsi. Anda hanya dapat melihat shipment milik perusahaan Anda.</p>
            </div>
        </div>

        <div>
            <a href="{{ route('dashboard') }}" class="btn-apple inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-all shadow-sm">
                &larr; Kembali ke Aplikasi Internal Genie
            </a>
        </div>
    </header>

    <!-- 2. Main Container -->
    <main class="flex-1 max-w-5xl w-full mx-auto p-4 sm:p-8 space-y-6">

        <!-- Flash Notification -->
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- List Shipment Klien -->
        @forelse($shipments as $shp)
            @php
                // Map 11 tahapan internal ke 6 fase sederhana bagi klien:
                // 0: Dokumen, 1: Pabean, 2: Pelayaran, 3: Kedatangan, 4: Pengiriman, 5: Selesai
                $idx = $shp->stage_index;
                $clientPhase = match(true) {
                    $idx >= 10 => 5, // Selesai
                    $idx >= 8  => 4, // Pengiriman Truk & POD
                    $idx >= 5  => 3, // Kedatangan Pelabuhan & Gate Out
                    $idx === 4 => 2, // Pelayaran & DO
                    $idx >= 2  => 1, // Pabean (PIB/Billing)
                    default    => 0, // Dokumen
                };

                $phases = ['Dokumen', 'Pabean', 'Pelayaran', 'Kedatangan', 'Pengiriman', 'Selesai'];
            @endphp

            <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm p-6 sm:p-8 space-y-6">
                <!-- Header Kartu Shipment -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 gap-2">
                    <div>
                        <div class="flex items-center space-x-2">
                            <h2 class="text-lg sm:text-xl font-black font-mono text-slate-900 tracking-tight">
                                {{ $shp->project_no }}
                            </h2>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono {{ $shp->type === 'EXPORT' ? 'bg-amber-100 text-amber-800' : 'bg-sky-100 text-sky-800' }}">
                                {{ $shp->type === 'EXPORT' ? 'EKSPOR' : 'IMPOR' }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">
                            No. B/L: <strong class="text-slate-800 font-mono">{{ $shp->nomor_bl_awb }}</strong> &bull; 
                            Kapal: {{ $shp->nama_kapal ?? '-' }} &bull; 
                            Pelabuhan: {{ $shp->pelabuhan_bongkar ?? 'Tanjung Priok' }}
                        </p>
                    </div>

                    <div class="text-left sm:text-right">
                        <span class="text-xs text-slate-400 block">Estimasi Tiba (ETA):</span>
                        <span class="text-sm font-extrabold font-mono text-slate-900">
                            {{ $shp->eta_date?->format('d M Y') ?? 'Segera Diperbarui' }}
                        </span>
                    </div>
                </div>

                <!-- 6-Phase Client Stepper (Apple Prototype Style) -->
                <div>
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Status Perjalanan Pengapalan</h3>
                    <div class="overflow-x-auto pb-2">
                        <div class="flex items-center min-w-[560px] gap-1">
                            @foreach($phases as $pIdx => $pName)
                                @php
                                    $isDone = $pIdx < $clientPhase;
                                    $isCur = $pIdx === $clientPhase;
                                @endphp
                                <div class="flex-1 flex flex-col items-center text-center">
                                    <div class="w-full flex items-center mb-1.5">
                                        <div class="h-1 flex-1 {{ $pIdx === 0 ? 'invisible' : ($isDone || $isCur ? 'bg-sky-500' : 'bg-slate-200') }}"></div>
                                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-all
                                            {{ $isDone ? 'bg-emerald-500 text-white' : ($isCur ? 'bg-sky-600 text-white ring-4 ring-sky-100' : 'bg-slate-100 text-slate-400 border border-slate-300') }}">
                                            {{ $isDone ? '✓' : ($pIdx + 1) }}
                                        </div>
                                        <div class="h-1 flex-1 {{ $pIdx === count($phases) - 1 ? 'invisible' : ($isDone ? 'bg-sky-500' : 'bg-slate-200') }}"></div>
                                    </div>
                                    <span class="text-xs font-bold {{ $isCur ? 'text-sky-700' : ($isDone ? 'text-slate-800' : 'text-slate-400') }}">
                                        {{ $pName }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Checklist Dokumen Klien & Form Unggah Mandiri -->
                <div class="pt-2">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Kelengkapan Berkas Dokumen</h3>
                        <span class="text-xs text-slate-400">Format: PDF, JPG, PNG (Maks 10 MB)</span>
                    </div>

                    <div class="divide-y divide-slate-100 border border-slate-200 rounded-2xl overflow-hidden text-xs">
                        @foreach($shp->documents as $doc)
                            @php
                                $isVerified = $doc->status === 'VERIFIED';
                                $isUploaded = $doc->status === 'UPLOADED';
                            @endphp
                            <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white hover:bg-slate-50/60">
                                <div>
                                    <div class="font-bold text-slate-800 text-sm flex items-center gap-2">
                                        <span>{{ $doc->catatan ?? $doc->jenis_dokumen }}</span>
                                        @if($isVerified)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">✓ Terverifikasi</span>
                                        @elseif($isUploaded)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-sky-100 text-sky-800">Menunggu Verifikasi</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">&#9888; Belum Ada</span>
                                        @endif
                                    </div>
                                    <span class="text-slate-400 text-xs font-mono mt-0.5 block">
                                        {{ $doc->nama_file ? $doc->nama_file . ' (' . ($doc->file_size_kb ?? 0) . ' KB)' : 'Mohon lengkapi berkas ini agar pabean dapat diproses.' }}
                                    </span>
                                </div>

                                <div class="shrink-0">
                                    @if(!$isVerified)
                                        <form action="{{ route('portal.upload', $shp->id) }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
                                            @csrf
                                            <input type="hidden" name="document_type" value="{{ $doc->jenis_dokumen }}">
                                            <label class="btn-apple cursor-pointer inline-flex items-center px-3.5 py-1.5 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold shadow-sm transition-all">
                                                <span>{{ $doc->nama_file ? 'Ganti Berkas' : 'Unggah Sekarang' }}</span>
                                                <input type="file" name="document_file" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xlsx,.xls" class="hidden" onchange="this.form.submit()">
                                            </label>
                                        </form>
                                    @else
                                        <span class="text-xs font-semibold text-emerald-600 flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Selesai Disetujui
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        @empty
            <div class="bg-white rounded-3xl p-12 text-center border border-slate-200">
                <p class="text-slate-400 text-sm">Belum ada pengapalan aktif yang terdaftar untuk klien ini.</p>
            </div>
        @endforelse

    </main>

    <!-- 3. Portal Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-400">
        <p>&copy; 2026 PT Agung Trans Solusindo &bull; Genie ERP Logistics Platform</p>
    </footer>

</body>
</html>
