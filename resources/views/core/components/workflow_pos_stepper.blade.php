@php
    $stages = \App\Models\Shipment::STAGES_PIPELINE;
    $currentIdx = $shipment->stage_index;
    $picsMap = $shipment->stage_pics_map;
    $canRollback = auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('workflow.rollback_stage');
    $isBillingLocked = $shipment->isBillingLocked();
    $taxes = $shipment->estimated_taxes ?? [];
@endphp

<div class="space-y-4 mb-6">
    <!-- Top Action & Navigation Row -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 font-medium mb-1.5" aria-label="Breadcrumb">
                <a href="{{ route('core.document.index') }}" class="hover:text-[#007AFF] transition-colors flex items-center gap-1">
                    <i data-lucide="folder" class="w-3.5 h-3.5"></i>
                    <span>Semua Proyek</span>
                </a>
                <i data-lucide="chevron-right" class="w-3 h-3 text-slate-300 dark:text-slate-600"></i>
                @if($shipment->type === 'EXPORT')
                    <a href="{{ route('bc30.index') }}" class="hover:text-[#007AFF] text-indigo-600 dark:text-indigo-400 font-bold transition-colors flex items-center gap-1">
                        <i data-lucide="arrow-up-right" class="w-3.5 h-3.5"></i>
                        <span>BC 3.0 (PEB)</span>
                    </a>
                @else
                    <a href="{{ route('bc20.index') }}" class="hover:text-[#007AFF] text-[#007AFF] font-bold transition-colors flex items-center gap-1">
                        <i data-lucide="arrow-down-left" class="w-3.5 h-3.5"></i>
                        <span>BC 2.0 (PIB)</span>
                    </a>
                @endif
                <i data-lucide="chevron-right" class="w-3 h-3 text-slate-300 dark:text-slate-600"></i>
                <span class="text-slate-800 dark:text-white font-mono font-semibold">{{ $shipment->shipment_number ?? $shipment->project_no }}</span>
            </div>
            
            <div class="flex flex-wrap items-center gap-2.5">
                <h1 class="text-2xl sm:text-3xl font-extrabold font-mono text-slate-900 dark:text-white tracking-tight">
                    {{ $shipment->shipment_number ?? $shipment->project_no }}
                </h1>

                <!-- Type Badge: BC 2.0 or BC 3.0 -->
                @if($shipment->type === 'EXPORT')
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold font-mono bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-400 border border-indigo-200/80">
                        BC 3.0 (PEB)
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold font-mono bg-sky-50 dark:bg-sky-950/60 text-[#007AFF] border border-sky-200/80">
                        BC 2.0 (PIB)
                    </span>
                @endif

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
            <!-- Tombol Lihat Proses Sebelumnya: Selalu dapat diakses kapan saja -->
            <button type="button" 
                    onclick="openViewPreviousProcessModal()"
                    class="btn-apple-gray text-xs px-3 py-1.5 inline-flex items-center gap-1.5 !text-[#007AFF] !border-[#007AFF]/30 hover:!bg-[#007AFF]/10 cursor-pointer shadow-2xs font-semibold"
                    title="Lihat rincian riwayat dan seluruh proses sebelumnya dari Pos 1 hingga Pos berjalan">
                <i data-lucide="eye" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                <span>Lihat Proses Sebelumnya</span>
            </button>

            <a href="{{ route('core.tracking.index') }}" 
               class="btn-apple-gray text-xs px-3 py-1.5 inline-flex items-center gap-1.5"
               title="Buka Matriks Tracing 11 Pos & Workload Tim">
                <i data-lucide="milestone" class="w-3.5 h-3.5 text-slate-500"></i>
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

            <!-- Tombol Buka Pos / Rollback (Hanya jika belum billing) -->
            @if($canRollback && $currentIdx > 0)
                @if($isBillingLocked)
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-700 dark:text-amber-400 text-xs font-semibold cursor-help"
                         title="Billing MPN G3 telah terbit di DJBC. Dokumen pabean terkunci dan tidak dapat diedit atau di-rollback, namun seluruh data proses sebelumnya tetap dapat Anda lihat via tombol 'Lihat Proses Sebelumnya'.">
                        <i data-lucide="lock" class="w-3.5 h-3.5 text-amber-600"></i>
                        <span>Terkunci (Billing Terbit)</span>
                    </div>
                @else
                    <button type="button" 
                            onclick="document.getElementById('modal-rollback-stage')?.classList.remove('hidden')"
                            class="btn-apple-gray text-xs px-3 py-1.5 inline-flex items-center gap-1.5 !text-amber-700 dark:!text-amber-400 !border-amber-400/40 hover:!bg-amber-500/10 cursor-pointer shadow-2xs font-semibold"
                            title="Buka atau kembalikan proses ke pos sebelumnya untuk perbaikan data">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5 text-amber-600"></i>
                        <span>Buka Pos Sebelumnya</span>
                    </button>
                @endif
            @endif

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

            @if($shipment->isDraftDeletable() && (auth()->user()->isSuperAdmin() || auth()->user()->hasAnyPermission(['document.create', 'document.edit', 'document.delete'])))
                <form action="{{ route('core.document.destroy', $shipment->id) }}" method="POST" class="inline"
                      onsubmit="return confirm('Hapus draft proyek {{ $shipment->project_no }} beserta seluruh aset file dokumen yang terunggah? Tindakan ini tidak dapat dibatalkan.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="btn-apple-gray text-xs px-3 py-1.5 inline-flex items-center gap-1.5 !text-rose-600 dark:!text-rose-400 !border-rose-300/60 hover:!bg-rose-500/10 cursor-pointer font-semibold"
                            title="Hapus Draft Proyek & Aset File Dokumen">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        <span>Hapus Draft</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- 11 Pos Workflow Stepper Bar (Interaktif: Klik pos mana saja untuk melihat data pos terkait) -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 shadow-xs overflow-hidden">
        <div class="flex items-start justify-between gap-1 sm:gap-2 overflow-x-auto py-2 no-scrollbar text-center">
            @php
                $stageMapping = [
                    0 => 'DOC_COLLECTING',
                    1 => 'HS_DETECTED',
                    2 => 'DRAFT_REVIEW',
                    3 => 'BILLING_ISSUED',
                    4 => 'DO_PROCESSING',
                    5 => 'ARRIVED',
                    6 => 'TRAFFIC_HOLD',
                    7 => 'SPPB_RELEASED',
                    8 => 'GATE_OUT',
                    9 => 'DELIVERY',
                    10 => 'DELIVERED_POD',
                ];
            @endphp

            @foreach($stages as $i => $stage)
                @php
                    $posNumber = $i + 1;
                    $stKey = $stage['key'];
                    $stageStatus = $shipment->getStageStatus($i);
                    $picObj = $picsMap[$stKey] ?? null;
                    $picName = $picObj?->user?->name;
                    $targetStageCode = $stageMapping[$i] ?? 'DOC_COLLECTING';
                @endphp
                <div class="flex-1 min-w-[85px] sm:min-w-[96px] group transition-all">
                    <!-- Circle Indicator (Clickable to jump to stage view) -->
                    @if($stageStatus === 'done')
                        <button type="button" 
                                onclick="navigateToWorkflowStage({{ $i }})"
                                class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-emerald-500 border-2 border-emerald-500 text-white flex items-center justify-center font-bold text-xs mx-auto mb-2 shadow-xs transition-transform group-hover:scale-110 cursor-pointer hover:ring-2 hover:ring-[#007AFF]"
                                title="{{ $stage['title'] }}: Selesai {{ $picName ? '(' . $picName . ')' : '' }} — Klik untuk melihat rincian proses pos ini">
                            <i data-lucide="check" class="w-4 h-4 stroke-[3]"></i>
                        </button>
                        <div class="text-[11px] sm:text-[12px] font-medium text-slate-700 dark:text-slate-300 leading-snug truncate cursor-pointer"
                             onclick="navigateToWorkflowStage({{ $i }})"
                             title="{{ $stage['title'] }} — Klik untuk melihat">
                            {{ $stage['title'] }}
                        </div>
                        @if($picName)
                            <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-mono truncate mt-0.5">
                                {{ Str::limit(explode(' ', $picName)[0], 8) }}
                            </div>
                        @endif
                    @elseif($stageStatus === 'cur')
                        <button type="button"
                                onclick="navigateToWorkflowStage({{ $i }})"
                                class="w-7 h-7 sm:w-8 sm:h-8 rounded-full border-2 border-[#007AFF] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-extrabold text-xs mx-auto mb-2 ring-4 ring-[#007AFF]/20 transition-transform group-hover:scale-110 shadow-sm cursor-pointer"
                                title="Pos Berjalan: {{ $stage['title'] }} {{ $picName ? '— PIC: ' . $picName : '— Belum ada PIC' }} — Klik untuk melihat">
                            <span>{{ $posNumber }}</span>
                        </button>
                        <div class="text-[11px] sm:text-[12px] font-bold text-slate-900 dark:text-white leading-snug truncate cursor-pointer"
                             onclick="navigateToWorkflowStage({{ $i }})"
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
                        <button type="button"
                                onclick="navigateToWorkflowStage({{ $i }})"
                                class="w-7 h-7 sm:w-8 sm:h-8 rounded-full border-2 border-slate-200 dark:border-white/10 text-slate-400 dark:text-slate-500 flex items-center justify-center font-medium text-xs mx-auto mb-2 transition-colors group-hover:border-slate-300 dark:group-hover:border-white/20 cursor-pointer"
                                title="{{ $stage['title'] }}: Menunggu giliran {{ $picName ? '(PIC: ' . $picName . ')' : '' }} — Klik untuk menuju tab">
                            <span>{{ $posNumber }}</span>
                        </button>
                        <div class="text-[11px] sm:text-[12px] font-normal text-slate-400 dark:text-slate-500 leading-snug truncate cursor-pointer"
                             onclick="navigateToWorkflowStage({{ $i }})"
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

<!-- ======================================================== -->
<!-- MODAL: LIHAT RINCIAN & RIWAYAT PROSES SEBELUMNYA        -->
<!-- Selalu dapat diakses walau sudah Billing / Terkunci     -->
<!-- ======================================================== -->
<div id="modal-view-previous-process" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs hidden">
    <div class="bg-white dark:bg-[#1C1C1E] w-full max-w-2xl rounded-2xl border border-black/10 dark:border-white/10 shadow-2xl p-6 text-xs text-slate-800 dark:text-slate-200 max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between pb-3 border-b border-black/10 dark:border-white/10 mb-4 shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                    <i data-lucide="eye" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white">Rincian & Riwayat Proses Sebelumnya</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Inspeksi data Pos 1 sampai tahap saat ini secara transparan & read-only</p>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('modal-view-previous-process')?.classList.add('hidden')" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        @if($isBillingLocked)
            <div class="mb-4 p-3 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-800/40 text-amber-800 dark:text-amber-300 text-xs flex items-start gap-2.5 shrink-0">
                <i data-lucide="lock" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5"></i>
                <div>
                    <strong>Dokumen Terkunci (Billing Terbit):</strong>
                    <span>Kode Billing Kepabeanan MPN G3 (<strong class="font-mono">{{ $shipment->billing_code ?? $shipment->ceisaDocument?->kode_billing }}</strong>) telah diterbitkan oleh CEISA DJBC. Pengeditan dinonaktifkan untuk menjaga integritas data fiskal, namun seluruh proses di bawah ini dapat Anda lihat dan tinjau kapan saja.</span>
                </div>
            </div>
        @endif

        <div class="space-y-3.5 overflow-y-auto pr-1">
            <!-- Pos 1: Pengumpulan & Kelengkapan Berkas -->
            <div class="p-4 rounded-xl border border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-emerald-500 text-white font-bold text-[10px] flex items-center justify-center">1</span>
                        <strong class="text-slate-900 dark:text-white text-xs">Pos 1: Pengumpulan & Verifikasi Berkas (P01-P05)</strong>
                    </div>
                    <p class="text-slate-500 dark:text-slate-400 text-[11px]">
                        BL {{ $shipment->bl_number ?? '-' }} &bull; Inv: {{ $shipment->invoice_number ?? '-' }} &bull; Vendor: {{ $shipment->shippingVendor?->name ?? '-' }}
                    </p>
                    <div class="text-[11px] font-mono text-emerald-600 dark:text-emerald-400">
                        {{ $shipment->documents->where('status', 'VERIFIED')->count() }} / {{ $shipment->documents->count() }} Berkas Terverifikasi Sah
                    </div>
                </div>
                <button type="button" onclick="selectTabAndCloseModal('docs')" 
                        class="btn-apple-tinted text-xs px-3 py-1.5 shrink-0 flex items-center gap-1.5 self-start sm:self-auto cursor-pointer">
                    <i data-lucide="files" class="w-3.5 h-3.5"></i>
                    <span>Lihat Tab Berkas</span>
                </button>
            </div>

            <!-- Pos 2: Deteksi HS Code & Lartas -->
            <div class="p-4 rounded-xl border border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full {{ $currentIdx >= 1 ? 'bg-emerald-500 text-white' : 'bg-slate-300 text-slate-700' }} font-bold text-[10px] flex items-center justify-center">2</span>
                        <strong class="text-slate-900 dark:text-white text-xs">Pos 2: Deteksi HS Code & Perizinan Lartas (P06-P08)</strong>
                    </div>
                    <p class="text-slate-500 dark:text-slate-400 text-[11px]">
                        Total {{ $shipment->items->count() }} item barang &bull; Nilai CIF: {{ $shipment->currency }} {{ number_format($shipment->total_cif, 2) }}
                    </p>
                    <div class="text-[11px] {{ $shipment->lartas_info['has_lartas'] ? 'text-amber-600 dark:text-amber-400 font-semibold' : 'text-emerald-600 dark:text-emerald-400' }}">
                        {{ $shipment->lartas_info['has_lartas'] ? '⚠️ ' . $shipment->lartas_info['lartas_count'] . ' Produk Kena Lartas (Izin Terlampir)' : '✅ Seluruh Barang Bebas Lartas' }}
                    </div>
                </div>
                <button type="button" onclick="selectTabAndCloseModal('items')" 
                        class="btn-apple-tinted text-xs px-3 py-1.5 shrink-0 flex items-center gap-1.5 self-start sm:self-auto cursor-pointer">
                    <i data-lucide="package" class="w-3.5 h-3.5"></i>
                    <span>Lihat Tab HS Code</span>
                </button>
            </div>

            <!-- Pos 3: Draf Dokumen Pabean & CEISA -->
            <div class="p-4 rounded-xl border border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full {{ $currentIdx >= 2 ? 'bg-emerald-500 text-white' : 'bg-slate-300 text-slate-700' }} font-bold text-[10px] flex items-center justify-center">3</span>
                        <strong class="text-slate-900 dark:text-white text-xs">Pos 3: Draf Pabean & Gateway CEISA 4.0 (P09-P11)</strong>
                    </div>
                    <p class="text-slate-500 dark:text-slate-400 text-[11px] font-mono">
                        No Aju: {{ $shipment->ceisaDocument?->nomor_aju ?? 'Belum ada draf' }}
                    </p>
                    <div class="text-[11px] text-slate-600 dark:text-slate-400 flex items-center gap-2">
                        <span>Status: <strong class="font-bold text-[#007AFF]">{{ $shipment->ceisaDocument?->status ?? 'DRAFT' }}</strong></span>
                        @if($shipment->customs_channel)
                            &bull; <span>Jalur: <strong class="font-bold">{{ $shipment->customs_channel }}</strong></span>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0 self-start sm:self-auto">
                    <button type="button" onclick="selectTabAndCloseModal('ceisa')" 
                            class="btn-apple-tinted text-xs px-3 py-1.5 flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="landmark" class="w-3.5 h-3.5"></i>
                        <span>Lihat Tab CEISA</span>
                    </button>
                    @if($shipment->ceisaDocument)
                        <a href="{{ $shipment->ceisaDocument->jenis_dokumen === '20' ? route('bc20.show', $shipment->ceisaDocument->id) : route('bc30.show', $shipment->ceisaDocument->id) }}" 
                           class="btn-apple-gray text-xs px-3 py-1.5 flex items-center gap-1.5"
                           title="Buka Lembar Draf Resmi BC {{ $shipment->ceisaDocument->jenis_dokumen }}">
                            <span>Draf Resmi BC</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Pos 4: Billing Kepabeanan MPN G3 -->
            <div class="p-4 rounded-xl border border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full {{ $currentIdx >= 3 || $shipment->billing_code || $shipment->ceisaDocument?->kode_billing ? 'bg-amber-500 text-white' : 'bg-slate-300 text-slate-700' }} font-bold text-[10px] flex items-center justify-center">4</span>
                        <strong class="text-slate-900 dark:text-white text-xs">Pos 4: Billing Kepabeanan MPN G3 (P12-P13)</strong>
                    </div>
                    @if($shipment->billing_code || $shipment->ceisaDocument?->kode_billing)
                        <p class="text-[11px] font-mono font-bold text-amber-600 dark:text-amber-400">
                            Kode Billing: {{ $shipment->billing_code ?? $shipment->ceisaDocument?->kode_billing }}
                        </p>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">
                            Total Bayar: <strong class="text-slate-900 dark:text-white">Rp {{ number_format($shipment->ceisaDocument?->total_billing ?? $taxes['total_billing'] ?? 0, 0, ',', '.') }}</strong>
                            &bull; Jatuh Tempo: {{ $shipment->ceisaDocument?->tgl_jatuh_tempo_billing?->format('d/m/Y') ?? '3 Hari Kerja' }}
                        </p>
                    @else
                        <p class="text-slate-400 text-[11px] italic">Kode billing akan diterbitkan otomatis setelah dokumen dikirim ke CEISA 4.0.</p>
                    @endif
                </div>
                @if($shipment->ceisaDocument?->kode_billing || $shipment->billing_code)
                    <a href="{{ route('pdf.billing', $shipment->id) }}" target="_blank"
                       class="btn-apple-tinted text-xs px-3 py-1.5 shrink-0 flex items-center gap-1.5 self-start sm:self-auto">
                        <i data-lucide="receipt" class="w-3.5 h-3.5"></i>
                        <span>Cetak Billing</span>
                    </a>
                @endif
            </div>

            <!-- Pos 5+: Pelayaran, DO, dan Operasional Lapangan -->
            <div class="p-4 rounded-xl border border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full {{ $currentIdx >= 4 ? 'bg-emerald-500 text-white' : 'bg-slate-300 text-slate-700' }} font-bold text-[10px] flex items-center justify-center">5+</span>
                        <strong class="text-slate-900 dark:text-white text-xs">Pos 5-11: Rilis DO, Lapangan, & Delivery (P14-P19)</strong>
                    </div>
                    <p class="text-slate-500 dark:text-slate-400 text-[11px]">
                        Status: <strong class="text-slate-800 dark:text-slate-200">{{ $shipment->status_tahap }}</strong>
                        &bull; DO: {{ $shipment->do_release_date ? 'Dirilis (' . $shipment->do_release_date->format('d/m/Y') . ')' : 'Dalam Proses' }}
                    </p>
                </div>
                <button type="button" onclick="selectTabAndCloseModal('ops')" 
                        class="btn-apple-tinted text-xs px-3 py-1.5 shrink-0 flex items-center gap-1.5 self-start sm:self-auto cursor-pointer">
                    <i data-lucide="truck" class="w-3.5 h-3.5"></i>
                    <span>Lihat Tab Lapangan & DO</span>
                </button>
            </div>
        </div>

        <div class="pt-4 border-t border-black/10 dark:border-white/10 mt-4 flex items-center justify-between shrink-0">
            @if($canRollback && !$isBillingLocked && $currentIdx > 0)
                <button type="button" 
                        onclick="document.getElementById('modal-view-previous-process')?.classList.add('hidden'); document.getElementById('modal-rollback-stage')?.classList.remove('hidden');"
                        class="text-xs text-amber-600 dark:text-amber-400 hover:underline flex items-center gap-1 cursor-pointer">
                    <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                    <span>Perlu mengubah data pos sebelumnya? Buka form rollback</span>
                </button>
            @else
                <span class="text-[11px] text-slate-400">Seluruh data riwayat di atas tersinkronisasi langsung dengan CEISA 4.0.</span>
            @endif

            <button type="button" onclick="document.getElementById('modal-view-previous-process')?.classList.add('hidden')" class="btn-apple-gray text-xs px-4 py-2 cursor-pointer">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: BUKA / ROLLBACK PROSES SEBELUMNYA                -->
<!-- Hanya aktif jika belum Billing                          -->
<!-- ======================================================== -->
@if($canRollback)
<div id="modal-rollback-stage" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs hidden">
    <div class="bg-white dark:bg-[#1C1C1E] w-full max-w-lg rounded-2xl border border-black/10 dark:border-white/10 shadow-2xl p-6 text-xs text-slate-800 dark:text-slate-200">
        <div class="flex items-center justify-between pb-3 border-b border-black/10 dark:border-white/10 mb-4">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white">Buka / Kembalikan Proses Sebelumnya</h3>
                    <p class="text-[11px] text-slate-500">Pilih tahapan alur shipment yang ingin dibuka kembali</p>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('modal-rollback-stage')?.classList.add('hidden')" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        @if($isBillingLocked)
            <div class="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200/80 dark:border-rose-900/50 text-rose-800 dark:text-rose-300 text-xs mb-4">
                <strong>🔒 Pengeditan & Rollback Terkunci:</strong>
                <p class="mt-1 text-[11px]">Kode Billing Kepabeanan telah terbit. Dokumen tidak dapat dikembalikan ke tahap sebelum billing karena kewajiban pembayaran telah tercatat di CEISA DJBC. Silakan gunakan tombol <strong>"Lihat Proses Sebelumnya"</strong> untuk meninjau data pos sebelumnya secara read-only.</p>
            </div>
        @endif

        <form action="{{ route('core.document.rollback-stage', $shipment->id) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block font-semibold mb-1 text-slate-700 dark:text-slate-300">Pos / Tahapan Target Pembukaan Kembali *</label>
                <select name="target_stage" id="rollback_target_stage" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-semibold focus:ring-2 focus:ring-[#007AFF]">
                    <option value="DOC_COLLECTING" {{ $isBillingLocked ? 'disabled' : '' }} {{ $currentIdx > 0 && !$isBillingLocked ? 'selected' : '' }}>Pos 1: Dokumen (Pengumpulan & Kelengkapan Berkas) {{ $isBillingLocked ? '[Terkunci - Billing Terbit]' : '' }}</option>
                    <option value="HS_DETECTED" {{ $isBillingLocked ? 'disabled' : '' }} {{ $currentIdx > 1 && !$isBillingLocked ? 'selected' : '' }}>Pos 2: HS & Lartas (Pencocokan Pos Tarif BTKI) {{ $isBillingLocked ? '[Terkunci - Billing Terbit]' : '' }}</option>
                    <option value="DRAFT_REVIEW" {{ $isBillingLocked ? 'disabled' : '' }} {{ $currentIdx > 2 && !$isBillingLocked ? 'selected' : '' }}>Pos 3: Dokumen Pabean (Draft Review PIB/PEB) {{ $isBillingLocked ? '[Terkunci - Billing Terbit]' : '' }}</option>
                    @if($currentIdx > 3)
                        <option value="CEISA_SUBMITTED" {{ $isBillingLocked ? 'disabled' : '' }}>Pos 3: CEISA Submitted (Gateway Pengiriman)</option>
                        <option value="BILLING_ISSUED" {{ $isBillingLocked ? 'selected' : '' }}>Pos 4: Billing Pabean Terbit</option>
                    @endif
                    @if($currentIdx > 4)
                        <option value="BILLING_PAID">Pos 5: Pelayaran & DO (Proses Rilis)</option>
                    @endif
                    @if($currentIdx > 5)
                        <option value="ARRIVED">Pos 6: Kedatangan & Jalur</option>
                    @endif
                    @if($currentIdx > 6)
                        <option value="TRAFFIC_HOLD">Pos 7: Hold & Notul Bea Cukai</option>
                    @endif
                    @if($currentIdx > 7)
                        <option value="SPPB_RELEASED">Pos 8: SPPB & Gate Out</option>
                    @endif
                </select>
                <p class="text-[10px] text-slate-400 mt-1">Mengembalikan alur ke tahap ini akan membuka kembali kesempatan mengedit data atau verifikasi pada pos tersebut.</p>
            </div>

            @if($shipment->ceisaDocument)
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#2C2C2E]/60 border border-slate-200 dark:border-white/10">
                    <label class="flex items-start gap-2.5 {{ $isBillingLocked ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }}">
                        <input type="checkbox" name="reopen_ceisa_draft" value="1" {{ $isBillingLocked ? 'disabled' : 'checked' }} class="mt-0.5 rounded text-[#007AFF] focus:ring-[#007AFF]">
                        <div>
                            <span class="font-semibold block text-slate-800 dark:text-white">Buka Kembali Dokumen Pabean (CEISA) ke Status DRAFT</span>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 block mt-0.5">
                                @if($isBillingLocked)
                                    <strong class="text-rose-600 dark:text-rose-400">Terkunci:</strong> Kode Billing telah diterbitkan oleh DJBC. Dokumen tidak dapat dikembalikan ke DRAFT.
                                @else
                                    Mengizinkan operator / staf dokumen untuk merevisi rincian barang, nilai CIF/FOB, dan dokumen pelengkap lartas.
                                @endif
                            </span>
                        </div>
                    </label>
                </div>
            @endif

            <div>
                <label class="block font-semibold mb-1 text-slate-700 dark:text-slate-300">Alasan Pembukaan Kembali (Catatan Audit Trail)</label>
                <textarea name="alasan" rows="3" placeholder="Contoh: Revisi rincian nilai invoice / perubahan pos tarif HS Code dari supplier..." 
                          class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white focus:ring-2 focus:ring-[#007AFF] text-xs"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-black/10 dark:border-white/10">
                <button type="button" onclick="document.getElementById('modal-rollback-stage')?.classList.add('hidden')" class="btn-apple-gray text-xs px-3.5 py-2">
                    Batal
                </button>
                <button type="submit" 
                        {{ $isBillingLocked ? 'disabled' : '' }}
                        class="btn-apple-filled text-xs px-4 py-2 !bg-amber-600 hover:!bg-amber-700 text-white inline-flex items-center gap-1.5 {{ $isBillingLocked ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }}">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    <span>Kembalikan & Buka Proses</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openRollbackModalForStage(stageCode) {
    const modal = document.getElementById('modal-rollback-stage');
    const select = document.getElementById('rollback_target_stage');
    if (modal && select) {
        select.value = stageCode;
        modal.classList.remove('hidden');
    }
}
</script>
@endif

<script>
function navigateToWorkflowStage(posIndex) {
    let tab = 'docs';
    if (posIndex === 0) tab = 'docs';
    else if (posIndex === 1) tab = 'items';
    else if (posIndex === 2 || posIndex === 3) tab = 'ceisa';
    else tab = 'ops';

    // Switch Alpine activeTab
    const container = document.querySelector('[x-data]');
    if (container && window.Alpine) {
        Alpine.$data(container).activeTab = tab;
    } else if (container && container._x_dataStack) {
        container._x_dataStack[0].activeTab = tab;
    }

    // Smooth scroll to workspace tabs
    const target = document.getElementById('workspace-tabs') || document.getElementById('pos-assignment');
    if (target) {
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function openViewPreviousProcessModal() {
    document.getElementById('modal-view-previous-process')?.classList.remove('hidden');
}

function selectTabAndCloseModal(tabName) {
    const container = document.querySelector('[x-data]');
    if (container && window.Alpine) {
        Alpine.$data(container).activeTab = tabName;
    } else if (container && container._x_dataStack) {
        container._x_dataStack[0].activeTab = tabName;
    }
    document.getElementById('modal-view-previous-process')?.classList.add('hidden');
    const target = document.getElementById('workspace-tabs');
    if (target) {
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}
</script>
