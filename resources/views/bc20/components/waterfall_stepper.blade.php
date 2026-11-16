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
    $doc = $bc20 ?? ($bc30 ?? null);
    $shp = $shipment ?? ($doc?->shipment ?? null);
    $isExp = ($doc?->jenis_dokumen === '30');
    $editRoute = $isExp ? 'bc30.edit' : 'bc20.edit';
    $showRoute = $isExp ? 'bc30.show' : 'bc20.show';

    $pipeline = [
        [
            'num' => 1,
            'title' => '1. Unggah Dokumen',
            'desc' => 'DRAFT Aman & PDF Terarsip',
            'icon' => 'file-up',
            'wizard_step' => 3,
        ],
        [
            'num' => 2,
            'title' => '2. Ekstraksi OCR/AI',
            'desc' => $isExp ? 'Inv, PL & PEB Draft' : 'Inv, PL, B/L & e-COO',
            'icon' => 'scan-text',
            'wizard_step' => 3,
        ],
        [
            'num' => 3,
            'title' => '3. Rekomendasi HS',
            'desc' => $isExp ? 'BTKI 2022 & Bea Keluar' : 'BTKI 2022 & Skrining Lartas',
            'icon' => 'sparkles',
            'wizard_step' => 7,
        ],
        [
            'num' => 4,
            'title' => $isExp ? '4. Wizard BC 3.0' : '4. Wizard BC 2.0',
            'desc' => $isExp ? '9 Tahap PEB & Ekspor' : '9 Tahap & Sync BC 1.1',
            'icon' => 'edit-3',
            'wizard_step' => 1,
        ],
        [
            'num' => 5,
            'title' => '5. Pre-Check CEISA',
            'desc' => 'Validasi Skema JSON 4.0',
            'icon' => 'shield-check',
            'wizard_step' => 9,
        ],
        [
            'num' => 6,
            'title' => '6. Submit & Billing',
            'desc' => $isExp ? 'Kode MPN G3 / NPE' : 'Kode MPN G3 / Respon DJBC',
            'icon' => 'credit-card',
            'wizard_step' => null,
            'anchor' => '#billing-card',
        ],
        [
            'num' => 7,
            'title' => '7. Tracking Dok',
            'desc' => '11 Pos Operasional Atrans',
            'icon' => 'compass',
            'wizard_step' => null,
            'url' => route('core.tracking.index', ['mode' => 'tracking_dok', 'search' => $shp?->project_no ?? $doc?->nomor_aju]),
        ],
    ];
@endphp

<div class="bg-white dark:bg-[#1C1C1E] rounded-2xl px-5 py-3.5 border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-2.5">
    <!-- Header Waterfall Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2.5 border-b border-black/[0.05] dark:border-white/[0.05] text-[11px]">
        <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full {{ $stage >= 6 ? 'bg-emerald-500' : 'bg-[#0284c7]' }} animate-pulse"></span>
            <span class="font-bold text-slate-800 dark:text-slate-100 uppercase tracking-wider">Skema Alur Waterfall Pabean &amp; Tracking Dokumen</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold font-mono {{ $stage >= 6 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-[#0284c7]/10 text-[#0284c7]' }}">
                Tahap {{ $stage }} dari 7
            </span>
        </div>
        <div class="flex flex-wrap items-center gap-2 text-[10px] text-slate-500 font-mono">
            @if($shp)
                <span>Shipment: <a href="{{ route('core.document.show', $shp->id) }}" class="font-bold text-[#0284c7] hover:underline">{{ $shp->project_no ?? $shp->shipment_number }}</a></span>
                <span class="text-slate-300 dark:text-slate-700">/</span>
            @endif
            @if($doc)
                <span>Aju: <strong class="text-slate-800 dark:text-slate-200">{{ substr($doc->nomor_aju, -8) }}</strong></span>
                <span class="text-slate-300 dark:text-slate-700">/</span>
                <span>Status: <strong class="{{ $doc->status_badge_class }} px-1.5 py-0.2 rounded">{{ $doc->status }}</strong></span>
            @else
                <span>Status: <strong class="text-amber-600">PENGAJUAN BARU</strong></span>
            @endif
            <span class="text-slate-300 dark:text-slate-700">/</span>
            <a href="{{ route('core.tracking.index', ['mode' => 'tracking_dok', 'search' => $shp?->project_no ?? $doc?->nomor_aju]) }}"
               target="_blank"
               class="text-[#0284c7] hover:underline font-semibold flex items-center gap-0.5"
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
                $targetUrl = '#';
                if (!empty($p['url'])) {
                    $targetUrl = $p['url'];
                } elseif ($doc) {
                    if (!empty($p['anchor'])) {
                        $targetUrl = route($showRoute, $doc->id) . $p['anchor'];
                    } elseif (!empty($p['wizard_step'])) {
                        $targetUrl = route($editRoute, $doc->id) . '#step-' . $p['wizard_step'];
                    } else {
                        $targetUrl = route($editRoute, $doc->id);
                    }
                }
            @endphp
            <a href="{{ $targetUrl }}"
               @if(!empty($p['wizard_step']))
                   @click="if (typeof activeStep !== 'undefined') { activeStep = {{ $p['wizard_step'] }}; $event.preventDefault(); window.scrollTo({ top: 320, behavior: 'smooth' }); }"
               @endif
               class="p-2.5 rounded-xl border transition-all block group hover:shadow-xs {{ 
                $isCurrent 
                    ? 'bg-sky-50 dark:bg-sky-950/40 border-[#0284c7]/50 text-[#0284c7] ring-2 ring-[#0284c7]/20 shadow-xs' 
                    : ($isPassed 
                        ? 'bg-emerald-50/80 dark:bg-emerald-950/25 border-emerald-200/80 dark:border-emerald-800/40 text-emerald-900 dark:text-emerald-200 hover:border-emerald-400' 
                        : 'bg-slate-50/70 dark:bg-white/[0.03] border-black/[0.05] dark:border-white/[0.05] text-slate-500 dark:text-slate-400 hover:border-[#0284c7]/40 hover:text-slate-700 dark:hover:text-slate-200')
               }}"
               title="{{ $p['title'] }} — Klik untuk navigasi ke tahapan ini">
                <div class="font-bold flex items-center justify-between gap-1">
                    <span class="truncate group-hover:underline">{{ $p['title'] }}</span>
                    @if($isPassed)
                        <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                    @elseif($isCurrent)
                        <span class="w-2 h-2 rounded-full bg-[#0284c7] shrink-0 animate-ping"></span>
                    @else
                        <i data-lucide="{{ $p['icon'] }}" class="w-3 h-3 text-slate-400 shrink-0 group-hover:text-[#0284c7]"></i>
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
            </a>
        @endforeach
    </div>
</div>

