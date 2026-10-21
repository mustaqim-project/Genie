@php
    $stages = \App\Models\Shipment::STAGES_PIPELINE;
    $currentIdx = $shipment->stage_index;
    $picsMap = $shipment->stage_pics_map;
@endphp

<div class="space-y-4 mb-6">
    <!-- Top Action & Navigation Row -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <a href="{{ route('core.document.index') }}" 
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-[#007AFF] transition-colors mb-2">
                <span>&larr; Semua shipment</span>
            </a>
            
            <div class="flex flex-wrap items-center gap-2.5">
                <h1 class="text-2xl sm:text-3xl font-extrabold font-mono text-slate-900 dark:text-white tracking-tight">
                    {{ $shipment->shipment_number ?? $shipment->project_no }}
                </h1>

                <!-- Client Pill Badge -->
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-black/5 dark:bg-white/10 text-slate-800 dark:text-white border border-black/5 dark:border-white/10">
                    {{ $shipment->client?->name ?? 'Klien Forwarder' }}
                </span>

                <!-- SLA Status Badge -->
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider
                    @if($shipment->sla_status === 'late') bg-rose-500/15 text-rose-600 dark:text-rose-400 border border-rose-500/30
                    @elseif($shipment->sla_status === 'warn') bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/30
                    @else bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 @endif">
                    {{ $shipment->sla_label }}
                </span>

                @if($shipment->customs_channel)
                    <span class="px-2 py-0.5 rounded-md text-[11px] font-bold uppercase
                        @if($shipment->customs_channel === 'HIJAU') bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20
                        @elseif($shipment->customs_channel === 'KUNING') bg-yellow-500/15 text-yellow-600 dark:text-yellow-400 border border-yellow-500/20
                        @else bg-rose-500/15 text-rose-600 dark:text-rose-400 border border-rose-500/20 @endif">
                        Jalur {{ $shipment->customs_channel }}
                    </span>
                @endif
            </div>

            <!-- Subtitle BL & ETA info -->
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-mono">
                <span>BL {{ $shipment->bl_number ?? $shipment->nomor_bl_awb ?? '-' }}</span>
                <span class="text-slate-300 dark:text-slate-700 mx-1.5">&bull;</span>
                <span>ETA {{ $shipment->eta_date ? $shipment->eta_date->format('d M') : ($shipment->actual_arrival_date ? $shipment->actual_arrival_date->format('d M') : 'TBA') }}</span>
                @if($shipment->nomor_aju)
                    <span class="text-slate-300 dark:text-slate-700 mx-1.5">&bull;</span>
                    <span>Aju: {{ $shipment->nomor_aju }}</span>
                @endif
            </p>
        </div>

        <!-- Quick Top Action Buttons -->
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('core.tracking.index') }}" 
               class="btn-apple-gray text-xs px-3 py-1.5 inline-flex items-center gap-1.5"
               title="Buka Matriks Tracing 11 Pos & Workload Tim">
                <i data-lucide="milestone" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                <span>Matriks Tracing</span>
            </a>

            @if($shipment->type === 'EXPORT')
                <a href="{{ route('pdf.peb', $shipment->id) }}" target="_blank"
                   class="btn-apple-gray text-xs inline-flex items-center gap-1.5 px-3 py-1.5">
                    <i data-lucide="printer" class="w-3.5 h-3.5 text-slate-500"></i>
                    <span>Cetak PEB</span>
                </a>
            @else
                <a href="{{ route('pdf.pib', $shipment->id) }}" target="_blank"
                   class="btn-apple-gray text-xs inline-flex items-center gap-1.5 px-3 py-1.5">
                    <i data-lucide="printer" class="w-3.5 h-3.5 text-slate-500"></i>
                    <span>Cetak PIB</span>
                </a>
            @endif

            @if($shipment->ceisaDocument?->kode_billing || $shipment->billing_code)
                <a href="{{ route('pdf.billing', $shipment->id) }}" target="_blank"
                   class="btn-apple-tinted text-xs inline-flex items-center gap-1.5 px-3 py-1.5">
                    <i data-lucide="receipt" class="w-3.5 h-3.5"></i>
                    <span>Billing MPN</span>
                </a>
            @endif

            <a href="{{ route('pdf.do-request', $shipment->id) }}" target="_blank"
               class="btn-apple-gray text-xs inline-flex items-center gap-1.5 px-3 py-1.5" 
               title="Surat Permohonan Rilis DO ke Pelayaran">
                <i data-lucide="ship" class="w-3.5 h-3.5 text-amber-600"></i>
                <span>Surat DO</span>
            </a>

            <!-- Portal Pelacakan Klien & Timer Button -->
            <button type="button" 
                    onclick="openPortalModalShow()"
                    class="btn-apple-gray text-xs inline-flex items-center gap-1.5 px-3 py-1.5 {{ $shipment->isPortalActive() ? '!text-[#007AFF] !border-[#007AFF]/30' : '' }}"
                    title="Bagikan Tautan Pelacakan Portal Klien & Atur Timer">
                <i data-lucide="share-2" class="w-3.5 h-3.5"></i>
                <span>Portal Klien</span>
                @if($shipment->isPortalActive())
                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                @endif
            </button>

            <button type="button" onclick="document.getElementById('modal-edit-project')?.classList.remove('hidden')" 
                    class="btn-apple-gray text-xs px-3 py-1.5">
                Ubah
            </button>

            <form action="{{ route('core.document.sync-from-docs', $shipment->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" 
                        class="btn-apple-gray text-xs px-3 py-1.5 inline-flex items-center gap-1"
                        title="Pindai ulang seluruh berkas terlampir dan perbarui data proyek secara otomatis">
                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                    <span>Sinkron</span>
                </button>
            </form>
        </div>
    </div>

    <!-- 11 Pos Workflow Stepper Bar (Persis Gambar Acuan) -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 shadow-xs overflow-hidden">
        <div class="flex items-start justify-between gap-1 sm:gap-2 overflow-x-auto py-2 no-scrollbar text-center">
            @foreach($stages as $i => $stage)
                @php
                    $posNumber = $i + 1;
                    $stKey = $stage['key'];
                    $stageStatus = $shipment->getStageStatus($i);
                    $picObj = $picsMap[$stKey] ?? null;
                    $picName = $picObj?->user?->name;
                @endphp
                <div class="flex-1 min-w-[85px] sm:min-w-[96px] group transition-all">
                    <!-- Circle Indicator -->
                    @if($stageStatus === 'done')
                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-emerald-500 border-2 border-emerald-500 text-white flex items-center justify-center font-bold text-xs mx-auto mb-2 shadow-xs transition-transform group-hover:scale-105"
                             title="{{ $stage['title'] }}: Selesai {{ $picName ? '(' . $picName . ')' : '' }}">
                            <i data-lucide="check" class="w-4 h-4 stroke-[3]"></i>
                        </div>
                        <div class="text-[11px] sm:text-[12px] font-medium text-slate-700 dark:text-slate-300 leading-snug truncate"
                             title="{{ $stage['title'] }}">
                            {{ $stage['title'] }}
                        </div>
                        @if($picName)
                            <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-mono truncate mt-0.5">
                                {{ Str::limit(explode(' ', $picName)[0], 8) }}
                            </div>
                        @endif
                    @elseif($stageStatus === 'cur')
                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full border-2 border-[#007AFF] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-extrabold text-xs mx-auto mb-2 ring-4 ring-[#007AFF]/20 transition-transform group-hover:scale-110 shadow-sm"
                             title="Pos Berjalan: {{ $stage['title'] }} {{ $picName ? '— PIC: ' . $picName : '— Belum ada PIC' }}">
                            <span>{{ $posNumber }}</span>
                        </div>
                        <div class="text-[11px] sm:text-[12px] font-bold text-slate-900 dark:text-white leading-snug truncate"
                             title="{{ $stage['title'] }}">
                            {{ $stage['title'] }}
                        </div>
                        <div class="inline-flex items-center gap-1 mt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#007AFF] animate-ping"></span>
                            <span class="text-[10px] font-bold text-[#007AFF] font-mono truncate">
                                {{ $picName ? Str::limit(explode(' ', $picName)[0], 8) : 'Perlu PIC' }}
                            </span>
                        </div>
                    @elseif($stageStatus === 'skip')
                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full border-2 border-dashed border-slate-300 dark:border-white/20 text-slate-400 dark:text-slate-500 flex items-center justify-center font-medium text-xs mx-auto mb-2 opacity-60"
                             title="{{ $stage['title'] }}: Dilewati otomatis (Jalur Hijau)">
                            <span>-</span>
                        </div>
                        <div class="text-[11px] sm:text-[12px] font-normal text-slate-400 dark:text-slate-500 leading-snug truncate opacity-60"
                             title="{{ $stage['title'] }}">
                            {{ $stage['title'] }}
                        </div>
                        <div class="text-[9px] text-slate-400 font-mono mt-0.5 italic opacity-60">
                            Dilewati
                        </div>
                    @else
                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full border-2 border-slate-200 dark:border-white/10 text-slate-400 dark:text-slate-500 flex items-center justify-center font-medium text-xs mx-auto mb-2 transition-colors group-hover:border-slate-300 dark:group-hover:border-white/20"
                             title="{{ $stage['title'] }}: Menunggu giliran {{ $picName ? '(PIC: ' . $picName . ')' : '' }}">
                            <span>{{ $posNumber }}</span>
                        </div>
                        <div class="text-[11px] sm:text-[12px] font-normal text-slate-400 dark:text-slate-500 leading-snug truncate"
                             title="{{ $stage['title'] }}">
                            {{ $stage['title'] }}
                        </div>
                        @if($picName)
                            <div class="text-[10px] text-slate-400 dark:text-slate-600 font-mono truncate mt-0.5">
                                {{ Str::limit(explode(' ', $picName)[0], 8) }}
                            </div>
                        @endif
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
