<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>{{ $title ?? 'Tautan Pelacakan Kedaluwarsa' }} — GENIE ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Inter", sans-serif;
            background-color: #F2F2F7;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/[0.08] dark:border-white/[0.08] shadow-2xl p-6 sm:p-8 text-center space-y-5">
        
        <!-- Icon Lock / Clock Warning -->
        <div class="w-16 h-16 rounded-[20px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center mx-auto shadow-sm">
            <i data-lucide="shield-alert" class="w-8 h-8"></i>
        </div>

        <div class="space-y-2">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF3B30]/10 text-[#FF3B30] uppercase tracking-wider font-mono">
                Akses Kedaluwarsa
            </span>
            <h1 class="text-[20px] font-bold text-slate-900 dark:text-white tracking-tight">
                {{ $title ?? 'Tautan Pelacakan Telah Kedaluwarsa' }}
            </h1>
            <p class="text-[13px] text-slate-500 dark:text-slate-400 leading-relaxed">
                {{ $message }}
            </p>
        </div>

        @if(isset($shipment))
        <!-- Info Proyek Singkat -->
        <div class="p-3.5 rounded-[14px] bg-slate-50 dark:bg-white/[0.04] border border-black/[0.06] dark:border-white/[0.06] text-left text-[12px] space-y-1 font-mono">
            <div class="flex justify-between">
                <span class="text-slate-400">No. Proyek:</span>
                <span class="font-bold text-slate-900 dark:text-white">{{ $shipment->project_no }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">No. B/L:</span>
                <span class="font-bold text-slate-900 dark:text-white">{{ $shipment->nomor_bl_awb }}</span>
            </div>
            @if(isset($expiredAt))
            <div class="flex justify-between text-[#FF3B30]">
                <span>Kedaluwarsa Pada:</span>
                <span class="font-semibold">{{ $expiredAt->format('d/m/Y H:i') }} WIB</span>
            </div>
            @endif
        </div>
        @endif

        <!-- Action: Hubungi Account Manager -->
        <div class="pt-2 space-y-2.5">
            <a href="https://wa.me/6281234567890?text={{ urlencode('Halo Tim Agung Trans Solusindo, mohon bantuan untuk menerbitkan link portal pelacakan baru untuk pengapalan proyek ' . ($shipment->project_no ?? 'kargo kami') . ' karena tautan sebelumnya telah kedaluwarsa. Terima kasih.') }}"
               target="_blank"
               class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-[12px] bg-[#34C759] hover:bg-[#2EB84F] text-white text-[13px] font-semibold transition-all shadow-sm active:scale-98">
                <i data-lucide="message-circle" class="w-4 h-4"></i>
                <span>Minta Tautan Baru via WhatsApp</span>
            </a>

            <div class="text-[11px] text-slate-400 dark:text-slate-500">
                PT Agung Trans Solusindo &bull; Divisi Kepabeanan & Forwarding
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.lucide) lucide.createIcons();
        });
    </script>
</body>
</html>
