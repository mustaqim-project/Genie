@php
    $stages = App\Models\Shipment::STAGES_PIPELINE;
    $currentIndex = $shipment->stage_index;
    $isHoldSkipped = $shipment->is_hold_skipped;
@endphp

<div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm mb-6">
    <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
        <div class="flex items-center space-x-2">
            <span class="text-base font-bold text-slate-900 tracking-tight">Status Pipeline Operasional (11 Tahap)</span>
            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold font-mono 
                @if($currentIndex >= 11) bg-emerald-100 text-emerald-800 border border-emerald-300
                @else bg-sky-100 text-sky-800 border border-sky-300 @endif">
                {{ $currentIndex >= 11 ? '✓ SELESAI' : 'TAHAP ' . ($currentIndex + 1) . ' DARI 11' }}
            </span>
        </div>

        <div class="flex items-center space-x-2 text-xs text-slate-500">
            @if($shipment->customs_channel)
                <span class="px-2 py-0.5 rounded font-bold uppercase font-mono
                    @if($shipment->customs_channel === 'HIJAU') bg-emerald-100 text-emerald-800 border border-emerald-300
                    @elseif($shipment->customs_channel === 'KUNING') bg-amber-100 text-amber-800 border border-amber-300
                    @else bg-rose-100 text-rose-800 border border-rose-300 @endif">
                    Jalur {{ $shipment->customs_channel }}
                </span>
            @endif

            <span class="text-slate-400">&bull;</span>
            <span>Target Total SLA: <strong class="text-slate-700">96 Jam</strong></span>
        </div>
    </div>

    <!-- Stepper Pipeline Track (Apple Style) -->
    <div class="overflow-x-auto pb-2 -mx-2 px-2 scrollbar-thin">
        <div class="flex items-center min-w-[960px] gap-1.5 py-1">
            @foreach($stages as $idx => $stg)
                @php
                    $isDone = $idx < $currentIndex;
                    $isCurrent = $idx === $currentIndex && $currentIndex < 11;
                    $isSkipped = ($stg['key'] === 'hold' && $isHoldSkipped);
                @endphp

                <!-- Step Item -->
                <div class="flex-1 flex flex-col items-center text-center group relative min-w-[76px]">
                    <div class="w-full flex items-center mb-2">
                        <!-- Left Connector -->
                        <div class="h-0.5 flex-1 {{ $idx === 0 ? 'invisible' : ($isDone || ($isCurrent && !$isSkipped) ? 'bg-sky-500' : 'bg-slate-200') }}"></div>

                        <!-- Circle Node -->
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-all shadow-sm
                            @if($isSkipped)
                                bg-slate-100 text-slate-400 border border-slate-300 line-through
                            @elseif($isDone)
                                bg-emerald-500 text-white shadow-emerald-500/20
                            @elseif($isCurrent)
                                bg-sky-600 text-white ring-4 ring-sky-100 shadow-sky-500/30 scale-110
                            @else
                                bg-slate-100 text-slate-500 border border-slate-300
                            @endif">
                            @if($isSkipped)
                                &ndash;
                            @elseif($isDone)
                                &#10003;
                            @else
                                {{ $idx + 1 }}
                            @endif
                        </div>

                        <!-- Right Connector -->
                        <div class="h-0.5 flex-1 {{ $idx === count($stages) - 1 ? 'invisible' : ($isDone ? 'bg-sky-500' : 'bg-slate-200') }}"></div>
                    </div>

                    <!-- Stage Label -->
                    <span class="text-[11px] font-bold tracking-tight block truncate w-full px-0.5
                        @if($isSkipped) text-slate-400 line-through
                        @elseif($isDone) text-slate-700
                        @elseif($isCurrent) text-sky-700 font-extrabold
                        @else text-slate-400 @endif">
                        {{ $stg['title'] }}
                    </span>

                    <span class="text-[9px] text-slate-400 block truncate w-full">
                        @if($isSkipped)
                            Dilewati
                        @elseif($isCurrent)
                            <span class="text-sky-600 font-semibold">Aktif</span>
                        @else
                            {{ $stg['role_name'] }}
                        @endif
                    </span>
                </div>
            @endforeach
        </div>
    </div>
</div>
