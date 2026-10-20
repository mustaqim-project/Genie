<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Pelacakan Proyek {{ $shipment->project_no }} — {{ $client->name ?? 'Portal Klien' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Inter", sans-serif;
            background-color: #F2F2F7;
            color: #111827;
        }
        .font-mono {
            font-family: "JetBrains Mono", "SF Mono", monospace;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">

    <!-- 1. Apple Style Minimalist Header -->
    <header class="sticky top-0 z-30 bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-xl border-b border-black/[0.08] dark:border-white/[0.08] transition-all">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between gap-3">
            <div class="flex items-center space-x-3 min-w-0">
                <div class="w-8 h-8 rounded-[9px] bg-gradient-to-tr from-[#007AFF] to-[#5856D6] flex items-center justify-center text-white font-bold shadow-xs flex-shrink-0">
                    <i data-lucide="ship" class="w-4 h-4 text-white"></i>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center space-x-1.5 leading-none">
                        <span class="font-bold text-[14px] text-slate-900 tracking-tight">GENIE PORTAL</span>
                        <span class="text-slate-300">&bull;</span>
                        <span class="text-[12px] font-semibold text-[#007AFF] truncate">{{ $client->name ?? 'Klien Terdaftar' }}</span>
                    </div>
                    <div class="text-[10px] text-slate-400 font-medium truncate mt-0.5">PT Agung Trans Solusindo</div>
                </div>
            </div>

            <!-- Timer Badge Akses Aman Klien -->
            <div class="flex items-center space-x-2 text-[11px] font-mono">
                <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#34C759]/10 text-[#248A3D] font-medium border border-[#34C759]/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759] animate-pulse"></span>
                    <span>Tautan Aktif (Sisa {{ $diffDays > 0 ? $diffDays . ' Hari' : $diffHours . ' Jam' }})</span>
                </span>
                <span class="sm:hidden px-2 py-0.5 rounded-full bg-[#34C759]/10 text-[#248A3D] font-semibold text-[10px]">
                    Aktif
                </span>
            </div>
        </div>
    </header>

    <!-- 2. Main Content Container -->
    <main class="flex-1 max-w-5xl w-full mx-auto px-4 sm:px-6 py-6 space-y-6">

        <!-- Flash Notification -->
        @if(session('success'))
        <div class="p-3.5 rounded-[14px] bg-[#34C759]/12 border border-[#34C759]/25 text-[#248A3D] text-[13px] flex items-center gap-2.5 shadow-2xs">
            <i data-lucide="check-circle" class="w-4 h-4 text-[#34C759] flex-shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @if(session('error'))
        <div class="p-3.5 rounded-[14px] bg-[#FF3B30]/12 border border-[#FF3B30]/25 text-[#C41E17] text-[13px] flex items-center gap-2.5 shadow-2xs">
            <i data-lucide="alert-circle" class="w-4 h-4 text-[#FF3B30] flex-shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        <!-- Card 1: Header Identitas Shipment & Jalur Pabean -->
        <div class="bg-white rounded-[20px] border border-black/[0.08] shadow-sm p-5 sm:p-6 space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between pb-4 border-b border-black/[0.06] gap-3">
                <div>
                    <div class="flex items-center space-x-2.5">
                        <h1 class="text-[20px] sm:text-[22px] font-bold text-slate-900 tracking-tight font-mono">
                            {{ $shipment->project_no }}
                        </h1>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold font-mono {{ $shipment->jenis_layanan === 'EKSPORT_BC30' ? 'bg-[#FF9500]/10 text-[#FF9500]' : 'bg-[#007AFF]/10 text-[#007AFF]' }}">
                            {{ $shipment->jenis_layanan === 'EKSPORT_BC30' ? 'EKSPOR BC 3.0' : 'IMPOR BC 2.0' }}
                        </span>
                    </div>
                    <p class="text-[13px] text-slate-500 mt-1">
                        B/L: <strong class="text-slate-800 font-mono">{{ $shipment->nomor_bl_awb }}</strong> &bull; 
                        Kapal: <span class="font-medium text-slate-700">{{ $shipment->nama_kapal ?? '-' }}</span> &bull; 
                        Pelabuhan: <span class="font-medium text-slate-700">{{ $shipment->pelabuhan_bongkar ?? 'Tanjung Priok' }}</span>
                    </p>
                </div>

                <!-- Jalur Pabean & Status Terkini -->
                <div class="flex items-center gap-2 sm:text-right">
                    @php
                        $channel = strtoupper($shipment->customs_channel ?? '');
                    @endphp
                    @if($channel)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-[10px] text-[12px] font-bold {{ $channel === 'HIJAU' ? 'bg-[#34C759]/15 text-[#248A3D]' : ($channel === 'KUNING' ? 'bg-[#FF9500]/15 text-[#B25E00]' : 'bg-[#FF3B30]/15 text-[#C41E17]') }}">
                        <span class="w-2 h-2 rounded-full {{ $channel === 'HIJAU' ? 'bg-[#34C759]' : ($channel === 'KUNING' ? 'bg-[#FF9500]' : 'bg-[#FF3B30]') }}"></span>
                        <span>JALUR {{ $channel }}</span>
                    </span>
                    @endif

                    <span class="px-3 py-1 rounded-[10px] bg-slate-100 text-slate-700 text-[12px] font-semibold font-mono">
                        {{ str_replace('_', ' ', $shipment->status_tahap) }}
                    </span>
                </div>
            </div>

            <!-- Grid Ringkasan Informasi Kargo -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-[12px]">
                <div class="p-3 rounded-[12px] bg-slate-50/80 border border-black/[0.04]">
                    <span class="text-slate-400 block text-[11px]">Total Kontainer</span>
                    <span class="font-bold text-slate-900 font-mono text-[13px] mt-0.5 block">{{ $shipment->total_kontainer }} x Kontainer</span>
                    <span class="text-[10px] text-slate-500 truncate block">{{ $shipment->nomor_kontainer ?? 'FCL' }}</span>
                </div>
                <div class="p-3 rounded-[12px] bg-slate-50/80 border border-black/[0.04]">
                    <span class="text-slate-400 block text-[11px]">Berat Netto / Bruto</span>
                    <span class="font-bold text-slate-900 font-mono text-[13px] mt-0.5 block">{{ number_format($shipment->total_netto, 2) }} kg</span>
                    <span class="text-[10px] text-slate-500 block">Bruto: {{ number_format($shipment->total_bruto, 2) }} kg</span>
                </div>
                <div class="p-3 rounded-[12px] bg-slate-50/80 border border-black/[0.04]">
                    <span class="text-slate-400 block text-[11px]">Estimasi Tiba (ETA)</span>
                    <span class="font-bold text-slate-900 font-mono text-[13px] mt-0.5 block">
                        {{ $shipment->eta_date ? $shipment->eta_date->format('d/m/Y') : '-' }}
                    </span>
                    <span class="text-[10px] text-slate-500 block">Pelayaran: {{ $shipment->shippingVendor->name ?? '-' }}</span>
                </div>
                <div class="p-3 rounded-[12px] bg-slate-50/80 border border-black/[0.04]">
                    <span class="text-slate-400 block text-[11px]">Masa Bebas (Free Time)</span>
                    <span class="font-bold text-slate-900 font-mono text-[13px] mt-0.5 block">
                        {{ $shipment->free_time_days ?? 7 }} Hari
                    </span>
                    <span class="text-[10px] text-slate-500 block">
                        Expired: {{ $shipment->free_time_expired_at ? $shipment->free_time_expired_at->format('d/m/Y') : '-' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Card 2: 6-Fase Milestones Pelacakan Perjalanan Kargo -->
        <div class="bg-white rounded-[20px] border border-black/[0.08] shadow-sm p-5 sm:p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-black/[0.06]">
                <h2 class="text-[15px] font-bold text-slate-900 tracking-tight flex items-center gap-2">
                    <i data-lucide="milestone" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>Tahapan Perjalanan Kargo</span>
                </h2>
                <span class="text-[11px] text-slate-400 font-medium">Update Terkini &bull; Realtime</span>
            </div>

            <!-- Stepper Linear 6 Fase -->
            @php
                $phases = [
                    ['title' => 'Pengumpulan Dokumen', 'desc' => 'Invoice, Packing List & B/L diterima'],
                    ['title' => 'Pabean & Pajak', 'desc' => 'Draft PIB & Kode Billing MPN Terbit'],
                    ['title' => 'DO Pelayaran', 'desc' => 'Penebusan Delivery Order Pelayaran'],
                    ['title' => 'Kedatangan & SPPB', 'desc' => 'Kapal sandar & SPPB Bea Cukai terbit'],
                    ['title' => 'Pengiriman Darat', 'desc' => 'Gate Out TPS & Truk berangkat ke gudang'],
                    ['title' => 'Selesai & Lunas', 'desc' => 'Kargo diterima di gudang klien (POD)'],
                ];
            @endphp
            <div class="grid grid-cols-2 md:grid-cols-6 gap-2 pt-2">
                @foreach($phases as $pIdx => $phase)
                @php
                    $isDone = $pIdx < $activePhase;
                    $isCurrent = $pIdx === $activePhase;
                @endphp
                <div class="p-3 rounded-[14px] border transition-all {{ $isCurrent ? 'bg-[#007AFF]/10 border-[#007AFF]/30' : ($isDone ? 'bg-slate-50 border-black/[0.06]' : 'bg-transparent border-dashed border-black/[0.08] opacity-50') }}">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-[11px] font-bold font-mono {{ $isCurrent ? 'bg-[#007AFF] text-white shadow-xs' : ($isDone ? 'bg-[#34C759] text-white' : 'bg-slate-200 text-slate-500') }}">
                            @if($isDone)
                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            @else
                                {{ $pIdx + 1 }}
                            @endif
                        </span>
                        @if($isCurrent)
                        <span class="text-[9px] font-bold px-1.5 py-0.2 rounded-full bg-[#007AFF]/15 text-[#007AFF] font-mono animate-pulse">Aktif</span>
                        @endif
                    </div>
                    <div class="text-[12px] font-bold text-slate-900 leading-tight">{{ $phase['title'] }}</div>
                    <p class="text-[10px] text-slate-500 mt-1 leading-snug line-clamp-2">{{ $phase['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Card 3: Dokumen Kepabeanan Resmi Siap Unduh -->
        <div class="bg-white rounded-[20px] border border-black/[0.08] shadow-sm p-5 sm:p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-black/[0.06]">
                <div>
                    <h2 class="text-[15px] font-bold text-slate-900 tracking-tight flex items-center gap-2">
                        <i data-lucide="file-check-2" class="w-4 h-4 text-[#34C759]"></i>
                        <span>Dokumen Resmi Kepabeanan</span>
                    </h2>
                    <p class="text-[11px] text-slate-400 mt-0.5">Dokumen legal resmi yang diterbitkan otoritas dan pelayaran untuk arsip Anda.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                <!-- 1. PIB / PEB -->
                <div class="p-3.5 rounded-[14px] bg-slate-50 border border-black/[0.06] flex items-center justify-between">
                    <div class="min-w-0 pr-2">
                        <div class="text-[12px] font-bold text-slate-900 truncate">
                            {{ $shipment->jenis_layanan === 'EKSPORT_BC30' ? 'PEB (BC 3.0)' : 'PIB (BC 2.0)' }}
                        </div>
                        <span class="text-[10px] text-slate-400">Pemberitahuan Pabean</span>
                    </div>
                    <a href="{{ route('pdf.pib', $shipment->id) }}" target="_blank" class="p-2 rounded-[8px] bg-white border border-black/10 text-[#007AFF] hover:bg-[#007AFF] hover:text-white transition-colors" title="Lihat & Cetak PDF">
                        <i data-lucide="printer" class="w-4 h-4"></i>
                    </a>
                </div>

                <!-- 2. Billing MPN G3 -->
                <div class="p-3.5 rounded-[14px] bg-slate-50 border border-black/[0.06] flex items-center justify-between">
                    <div class="min-w-0 pr-2">
                        <div class="text-[12px] font-bold text-slate-900 truncate">Billing MPN G3</div>
                        <span class="text-[10px] font-mono text-[#007AFF]">{{ $shipment->ceisaDocument?->nomor_billing ?? 'Siap Disetor' }}</span>
                    </div>
                    <a href="{{ route('pdf.billing', $shipment->id) }}" target="_blank" class="p-2 rounded-[8px] bg-white border border-black/10 text-[#007AFF] hover:bg-[#007AFF] hover:text-white transition-colors" title="Lihat Billing Pajak & BM">
                        <i data-lucide="download" class="w-4 h-4"></i>
                    </a>
                </div>

                <!-- 3. Delivery Order (DO) -->
                <div class="p-3.5 rounded-[14px] bg-slate-50 border border-black/[0.06] flex items-center justify-between">
                    <div class="min-w-0 pr-2">
                        <div class="text-[12px] font-bold text-slate-900 truncate">DO Pelayaran</div>
                        <span class="text-[10px] text-slate-400">{{ $shipment->status_do === 'DO_RELEASED' ? 'Sudah Rilis' : 'Dalam Proses' }}</span>
                    </div>
                    <a href="{{ route('pdf.do-request', $shipment->id) }}" target="_blank" class="p-2 rounded-[8px] bg-white border border-black/10 text-slate-700 hover:bg-slate-800 hover:text-white transition-colors" title="Lihat Berkas DO">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                    </a>
                </div>

                <!-- 4. Bukti Serah Terima (POD) -->
                <div class="p-3.5 rounded-[14px] bg-slate-50 border border-black/[0.06] flex items-center justify-between">
                    <div class="min-w-0 pr-2">
                        <div class="text-[12px] font-bold text-slate-900 truncate">Bukti Terima (POD)</div>
                        <span class="text-[10px] text-slate-400">{{ $shipment->pod_signed_at ? 'Telah Diterima' : 'Menunggu Kirim' }}</span>
                    </div>
                    <button type="button" disabled class="p-2 rounded-[8px] bg-slate-100 text-slate-400 cursor-not-allowed">
                        <i data-lucide="file-check" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Card 4: Unggah Berkas Tambahan Mandiri (Jika Ada Revisi Dokumen) -->
        <div class="bg-white rounded-[20px] border border-black/[0.08] shadow-sm p-5 sm:p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-black/[0.06]">
                <div>
                    <h2 class="text-[15px] font-bold text-slate-900 tracking-tight flex items-center gap-2">
                        <i data-lucide="upload-cloud" class="w-4 h-4 text-[#007AFF]"></i>
                        <span>Unggah Berkas Tambahan Klien</span>
                    </h2>
                    <p class="text-[11px] text-slate-400 mt-0.5">Kirimkan revisi Packing List, Invoice, COO Form E, atau izin teknis langsung ke sistem kami.</p>
                </div>
            </div>

            <form action="{{ route('portal.upload-token', $shipment->portal_token) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Pilih Jenis Dokumen</label>
                        <select name="document_type" required class="w-full h-10 px-3 rounded-[10px] border border-black/10 bg-slate-50 text-[12px] text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/20">
                            <option value="PACKING_LIST">Packing List (Revisi)</option>
                            <option value="COMMERCIAL_INVOICE">Commercial Invoice (Revisi)</option>
                            <option value="CERTIFICATE_OF_ORIGIN">COO (Form E / Form D / AKFTA)</option>
                            <option value="LARTAS_PERMIT">Izin Teknis / Lartas (PI / LS)</option>
                            <option value="OTHER_SUPPORTING">Dokumen Pendukung Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Berkas File (PDF / JPG / PNG / XLSX, Maks 15MB)</label>
                        <input type="file" name="document_file" required class="w-full h-10 px-3 py-1.5 rounded-[10px] border border-black/10 bg-slate-50 text-[12px] text-slate-800 file:mr-3 file:py-1 file:px-2.5 file:rounded-[6px] file:border-0 file:text-[11px] file:font-semibold file:bg-[#007AFF]/10 file:text-[#007AFF]">
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[12px] font-semibold transition-all shadow-xs active:scale-98">
                        <i data-lucide="send" class="w-3.5 h-3.5"></i>
                        <span>Kirim Berkas ke Tim Dokumen</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Security & Support Box -->
        <div class="p-4 rounded-[16px] bg-slate-100/80 border border-black/[0.04] flex flex-col sm:flex-row items-center justify-between gap-3 text-[11px] text-slate-500">
            <div class="flex items-center gap-2 text-center sm:text-left">
                <i data-lucide="shield-check" class="w-4 h-4 text-[#34C759] flex-shrink-0"></i>
                <span>Tautan pelacakan aman ini dilindungi enkripsi token. Masa aktif berakhir pada <strong>{{ $shipment->portal_expires_at->format('d/m/Y H:i') }} WIB</strong>.</span>
            </div>
            <a href="https://wa.me/6281234567890?text={{ urlencode('Halo Tim Agung Trans Solusindo, saya ingin menanyakan status pengapalan proyek ' . $shipment->project_no . ' (B/L: ' . $shipment->nomor_bl_awb . ').') }}" 
               target="_blank"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[8px] bg-[#34C759] text-white font-semibold hover:bg-[#2EB84F] transition-colors shrink-0">
                <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                <span>WhatsApp Account Manager</span>
            </a>
        </div>

    </main>

    <!-- Footer -->
    <footer class="mt-auto border-t border-black/[0.06] bg-white/60 py-3 text-center text-[11px] text-slate-400">
        &copy; {{ date('Y') }} PT Agung Trans Solusindo &bull; Sistem Operasional Pengapalan & Kepabeanan Terpadu
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.lucide) lucide.createIcons();
        });
    </script>
</body>
</html>
