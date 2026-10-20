@php
    $status = $shipment->status;
    $currentPhase = 1;
    if (in_array($status, ['DOC_COLLECTING', 'DOC_READY'])) {
        $currentPhase = 1;
    } elseif (in_array($status, ['HS_DETECTED', 'DRAFT_REVIEW', 'CEISA_SUBMITTED', 'BILLING_ISSUED'])) {
        $currentPhase = 2;
    } elseif (in_array($status, ['DO_PROCESSING', 'DO_RELEASED'])) {
        $currentPhase = 3;
    } else {
        $currentPhase = 4;
    }

    $milestones = [
        1 => ['title' => '1. Berkas & Dokumen', 'desc' => 'Invoice, Packing List, B/L'],
        2 => ['title' => '2. Pos Tarif & Pabean', 'desc' => 'HS Code, PIB/PEB, CEISA 4.0'],
        3 => ['title' => '3. DO Pelayaran', 'desc' => 'Penebusan DO & Free Time'],
        4 => ['title' => '4. Pengeluaran & POD', 'desc' => 'SPPB, Gate Out & Surat Jalan'],
    ];
@endphp

<div class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-xs mb-6">
    <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
        <div class="flex items-center space-x-2">
            <span class="text-sm font-bold text-slate-900 tracking-tight">Tahapan Alur Kerja Proyek</span>
            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold font-mono 
                @if($status === 'COMPLETED' || $status === 'DELIVERED_POD') bg-emerald-100 text-emerald-800 border border-emerald-300
                @else bg-sky-100 text-sky-800 border border-sky-300 @endif">
                Fase {{ $currentPhase }} dari 4
            </span>
        </div>

        <div class="flex items-center space-x-2 text-xs">
            @if($shipment->customs_channel)
                <span class="px-2 py-0.5 rounded font-bold uppercase font-mono text-[10px]
                    @if($shipment->customs_channel === 'HIJAU') bg-emerald-100 text-emerald-800 border border-emerald-300
                    @elseif($shipment->customs_channel === 'KUNING') bg-amber-100 text-amber-800 border border-amber-300
                    @else bg-rose-100 text-rose-800 border border-rose-300 @endif">
                    Jalur {{ $shipment->customs_channel }}
                </span>
            @endif
            @if($shipment->ceisaDocument?->nomor_aju)
                <span class="text-slate-400 font-mono text-[11px] hidden sm:inline">No. Aju: {{ $shipment->ceisaDocument->nomor_aju }}</span>
            @endif
        </div>
    </div>

    <!-- 4 Milestones Progress Bar -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3">
        @foreach($milestones as $step => $meta)
            @php
                $isDone = $step < $currentPhase;
                $isCurrent = $step === $currentPhase;
            @endphp
            <div class="p-3 rounded-xl border transition-all flex flex-col justify-between
                @if($isCurrent) bg-sky-50/70 border-sky-300 ring-2 ring-sky-100
                @elseif($isDone) bg-emerald-50/40 border-emerald-200
                @else bg-slate-50/60 border-slate-200/80 @endif">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-xs font-bold 
                        @if($isCurrent) text-sky-900
                        @elseif($isDone) text-emerald-900
                        @else text-slate-500 @endif">
                        {{ $meta['title'] }}
                    </span>
                    <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold font-mono
                        @if($isDone) bg-emerald-500 text-white
                        @elseif($isCurrent) bg-sky-600 text-white
                        @else bg-slate-200 text-slate-500 @endif">
                        @if($isDone) &#10003; @else {{ $step }} @endif
                    </span>
                </div>
                <div class="text-[10px] text-slate-500 leading-tight">
                    {{ $meta['desc'] }}
                </div>
            </div>
        @endforeach
    </div>
</div>
