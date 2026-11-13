@php
    /**
     * Komponen Terpadu: Skema Alur Waterfall Pabean & Tracking Dokumen
     * Digunakan secara konsisten pada Create, Edit, dan Detail (Show) BC 2.0
     *
     * @param int $activeStage (1..7)
     * @param \App\Models\CeisaDocument|null $bc20
     * @param \App\Models\Shipment|null $shipment
     */
    $stage = $activeStage ?? 4;
    $doc = $bc20 ?? null;
    $shp = $shipment ?? ($doc?->shipment ?? null);

    $pipeline = [
        [
            'num' => 1,
            'title' => '1. Unggah Dokumen',
            'desc' => 'DRAFT Aman & PDF Terarsip',
            'icon' => 'file-up',
        ],
        [
            'num' => 2,
            'title' => '2. Ekstraksi OCR/AI',
            'desc' => 'Inv, PL, B/L & e-COO',
            'icon' => 'scan-text',
        ],
        [
            'num' => 3,
            'title' => '3. Rekomendasi HS',
            'desc' => 'BTKI 2022 & Skrining Lartas',
            'icon' => 'sparkles',
        ],
        [
            'num' => 4,
            'title' => '4. Wizard BC 2.0',
            'desc' => '9 Tahap & Sync BC 1.1',
            'icon' => 'edit-3',
        ],
        [
            'num' => 5,
            'title' => '5. Pre-Check CEISA',
            'desc' => 'Validasi Skema JSON 4.0',
            'icon' => 'shield-check',
        ],
        [
            'num' => 6,
            'title' => '6. Submit & Billing',
            'desc' => 'Kode MPN G3 / Respon DJBC',
            'icon' => 'credit-card',
        ],
        [
            'num' => 7,
            'title' => '7. Tracking Dok',
            'desc' => '11 Pos Operasional Atrans',
            'icon' => 'compass',
        ],
    ];
@endphp

<div class="bg-white dark:bg-[#1C1C1E] rounded-2xl px-5 py-3.5 border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-2.5">
    <!-- Header Waterfall Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2.5 border-b border-black/[0.05] dark:border-white/[0.05] text-[11px]">
        <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full {{ $stage >= 6 ? 'bg-emerald-500' : 'bg-[#007AFF]' }} animate-pulse"></span>
            <span class="font-bold text-slate-800 dark:text-slate-100 uppercase tracking-wider">Skema Alur Waterfall Pabean &amp; Tracking Dokumen</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono {{ $stage >= 6 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-[#007AFF]/10 text-[#007AFF]' }}">
                Tahap {{ $stage }} dari 7
            </span>
        </div>
        <div class="flex flex-wrap items-center gap-2 text-[10px] text-slate-500 font-mono">
            @if($shp)
                <span>Shipment: <a href="{{ route('core.document.show', $shp->id) }}" class="font-bold text-[#007AFF] hover:underline">{{ $shp->project_no ?? $shp->shipment_number }}</a></span>
                <span>&bull;</span>
            @endif
            @if($doc)
                <span>Aju: <strong class="text-slate-800 dark:text-slate-200">{{ substr($doc->nomor_aju, -8) }}</strong></span>
                <span>&bull;</span>
                <span>Status: <strong class="{{ $doc->status_badge_class }} px-1.5 py-0.2 rounded">{{ $doc->status }}</strong></span>
            @else
                <span>Status: <strong class="text-amber-600">PENGAJUAN BARU</strong></span>
            @endif
            <span>&bull;</span>
            <a href="{{ route('core.tracking.index', ['mode' => 'tracking_dok', 'search' => $shp?->project_no ?? $doc?->nomor_aju]) }}"
               target="_blank"
               class="text-[#007AFF] hover:underline font-semibold flex items-center gap-0.5"
               title="Buka Cockpit Tracking Dokumen (11 Pos Operasional)">
                <span>Mode Tracking Dok</span>
                <i data-lucide="external-link" class="w-2.5 h-2.5"></i>
            </a>
        </div>
    </div>

    <!-- 7-Stage Stepper Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2 text-[10px]">
        @foreach($pipeline as $p)
            @php
                $isPassed = $stage > $p['num'];
                $isCurrent = $stage === $p['num'];
                $isUpcoming = $stage < $p['num'];
            @endphp
            <div class="p-2.5 rounded-xl border transition-all {{ 
                $isCurrent 
                    ? 'bg-sky-50 dark:bg-sky-950/40 border-[#007AFF]/50 text-[#007AFF] ring-2 ring-[#007AFF]/20 shadow-xs' 
                    : ($isPassed 
                        ? 'bg-emerald-50/80 dark:bg-emerald-950/25 border-emerald-200/80 dark:border-emerald-800/40 text-emerald-900 dark:text-emerald-200' 
                        : 'bg-slate-50/70 dark:bg-white/[0.03] border-black/[0.05] dark:border-white/[0.05] text-slate-500 dark:text-slate-400')
            }}">
                <div class="font-bold flex items-center justify-between gap-1">
                    <span class="truncate">{{ $p['title'] }}</span>
                    @if($isPassed)
                        <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                    @elseif($isCurrent)
                        <span class="w-2 h-2 rounded-full bg-[#007AFF] shrink-0 animate-ping"></span>
                    @else
                        <i data-lucide="{{ $p['icon'] }}" class="w-3 h-3 text-slate-400 shrink-0"></i>
                    @endif
                </div>
                <div class="text-[9px] mt-0.5 truncate {{ 
                    $isCurrent 
                        ? 'text-sky-700 dark:text-sky-300 font-medium' 
                        : ($isPassed 
                            ? 'text-emerald-700/80 dark:text-emerald-300/70' 
                            : 'text-slate-400')
                }}">
                    {{ $p['desc'] }}
                </div>
            </div>
        @endforeach
    </div>
</div>

