@extends('layouts.app')

@section('title', 'Proyek Pengapalan & Dokumen Pabean')

@section('content')
<div class="max-w-[1360px] mx-auto px-4 sm:px-6 space-y-6 pt-4 pb-28 lg:pb-12">

    <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Toolbar Style from Cooca Core) -->
    <header class="rounded-[16px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-[20px] sm:text-[22px] font-bold text-black dark:text-white tracking-tight">
                    Tim Document & Dokumen Pabean
                </h1>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#007AFF]/10 text-[#007AFF]">
                    Siklus P01–P10
                </span>
            </div>
            <p class="text-[13px] text-black/50 dark:text-white/50 mt-1 leading-snug">
                Registrasi pengapalan impor & ekspor, verifikasi berkas (Invoice, Packing List, B/L), pos tarif HS Code, dan integrasi CEISA 4.0 DJBC.
            </p>
        </div>

        <!-- Action Buttons Toolbar -->
        <div class="flex items-center gap-2 w-full sm:w-auto shrink-0">
            @can('document.create')
            <button type="button" 
                    onclick="document.getElementById('modal-create-project').classList.remove('hidden')" 
                    class="btn-apple-filled w-full sm:w-auto">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Buka Proyek Baru</span>
            </button>
            @endcan
        </div>
    </header>

    <!-- 2. 4 APPLE HIG KPI STATS CARDS -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
        <!-- Card 1: Total Proyek -->
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase tracking-wider font-semibold text-black/45 dark:text-white/45">Total Proyek</span>
                <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                    <i data-lucide="folder" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <div class="text-[22px] sm:text-[24px] font-bold tabular-nums text-black dark:text-white tracking-tight">
                    {{ $stats['total'] ?? 0 }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5 truncate">Semua pengapalan aktif</div>
            </div>
        </div>

        <!-- Card 2: Kumpul Berkas -->
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase tracking-wider font-semibold text-black/45 dark:text-white/45">Kumpul Berkas</span>
                <div class="w-8 h-8 rounded-[10px] bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <div class="text-[22px] sm:text-[24px] font-bold tabular-nums text-[#FF9500] dark:text-[#FF9F0A] tracking-tight">
                    {{ $stats['collecting'] ?? 0 }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5 truncate">Menunggu kelengkapan klien</div>
            </div>
        </div>

        <!-- Card 3: Review & HS Code -->
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase tracking-wider font-semibold text-black/45 dark:text-white/45">Review & HS Code</span>
                <div class="w-8 h-8 rounded-[10px] bg-[#5856D6]/10 text-[#5856D6] flex items-center justify-center">
                    <i data-lucide="scan" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <div class="text-[22px] sm:text-[24px] font-bold tabular-nums text-[#5856D6] dark:text-[#5E5CE6] tracking-tight">
                    {{ $stats['ready'] ?? 0 }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5 truncate">Siap aju ke CEISA 4.0</div>
            </div>
        </div>

        <!-- Card 4: Billing Terbit -->
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase tracking-wider font-semibold text-black/45 dark:text-white/45">Billing Terbit</span>
                <div class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2">
                <div class="text-[22px] sm:text-[24px] font-bold tabular-nums text-[#248A3D] dark:text-[#30D158] tracking-tight">
                    {{ $stats['billing'] ?? 0 }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5 truncate">Handoff ke Tim DO aktif</div>
            </div>
        </div>
    </div>

    <!-- 3. FILTER & SEARCH BAR (Apple Standard Inputs) -->
    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm p-4">
        <form method="GET" action="{{ route('core.document.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-[13px]">
            <div class="sm:col-span-5 relative">
                <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40"></i>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Cari Nomor Job, B/L, Nama Kapal, No Kontainer, atau Importir..." 
                       class="w-full pl-9 pr-3.5 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-black dark:text-white text-[13px] focus:outline-none focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 transition-all placeholder:text-black/40 dark:placeholder:text-white/40">
            </div>

            <div class="sm:col-span-3">
                <select name="type" class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-black dark:text-white text-[13px] focus:outline-none focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 transition-all">
                    <option value="">Semua Layanan</option>
                    <option value="IMPORT" {{ request('type') === 'IMPORT' ? 'selected' : '' }}>Impor (BC 2.0 / PIB)</option>
                    <option value="EXPORT" {{ request('type') === 'EXPORT' ? 'selected' : '' }}>Ekspor (BC 3.0 / PEB)</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <select name="status" class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-black dark:text-white text-[13px] focus:outline-none focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 transition-all">
                    <option value="">Semua Status</option>
                    <option value="DOC_COLLECTING" {{ request('status') === 'DOC_COLLECTING' ? 'selected' : '' }}>Kumpul Berkas</option>
                    <option value="DOC_READY" {{ request('status') === 'DOC_READY' ? 'selected' : '' }}>Dokumen Lengkap</option>
                    <option value="HS_DETECTED" {{ request('status') === 'HS_DETECTED' ? 'selected' : '' }}>HS Terdeteksi</option>
                    <option value="DRAFT_REVIEW" {{ request('status') === 'DRAFT_REVIEW' ? 'selected' : '' }}>Draft Review</option>
                    <option value="BILLING_ISSUED" {{ request('status') === 'BILLING_ISSUED' ? 'selected' : '' }}>Billing Terbit</option>
                    <option value="DO_PROCESSING" {{ request('status') === 'DO_PROCESSING' ? 'selected' : '' }}>Proses Tim DO</option>
                    <option value="DELIVERED_POD" {{ request('status') === 'DELIVERED_POD' ? 'selected' : '' }}>Selesai (POD)</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex items-center space-x-2">
                <button type="submit" class="btn-apple-gray w-full !h-9 text-[12px] font-semibold hover:!bg-[#007AFF]/10 hover:!text-[#007AFF]">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Terapkan</span>
                </button>
                <a href="{{ route('core.document.index') }}" class="btn-apple-gray !h-9 !px-3 text-[12px]">
                    <span>Reset</span>
                </a>
            </div>
        </form>
    </div>

    <!-- 4. DATA TABLE (Apple Standard Table from Cooca Core) -->
    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm overflow-hidden flex flex-col">
        <div class="p-4 sm:p-5 border-b border-black/5 dark:border-white/5 flex items-center justify-between">
            <div class="font-semibold text-[13px] text-black dark:text-white flex items-center space-x-2">
                <span>Daftar Proyek Shipment Aktif</span>
                <span class="px-2 py-0.5 rounded-full text-[11px] bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 font-mono">{{ $shipments->total() }} Data</span>
            </div>
            <div class="text-[12px] text-black/45 dark:text-white/45 hidden sm:block">
                Kelola 9 slot dokumen berkas pengapalan & integrasi CEISA 4.0
            </div>
        </div>

        <div class="table-responsive flex-1">
            <table class="w-full text-left text-[13px]">
                <thead class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider bg-black/[0.02] dark:bg-white/[0.02] border-b border-black/5 dark:border-white/10">
                    <tr>
                        <th class="py-3 px-4">No. Proyek & B/L</th>
                        <th class="py-3 px-4">Klien / Importir</th>
                        <th class="py-3 px-4">Pengangkutan & Kontainer</th>
                        <th class="py-3 px-4">Audit 9 Berkas</th>
                        <th class="py-3 px-4">Status Siklus</th>
                        <th class="py-3 px-4">Billing MPN G3</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5">
                    @forelse($shipments as $shp)
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                            <td class="py-3.5 px-4 font-mono">
                                <a href="{{ route('core.document.show', $shp->id) }}" class="font-bold text-[#007AFF] hover:underline flex items-center space-x-1.5">
                                    <span>{{ $shp->shipment_number ?? $shp->project_no }}</span>
                                </a>
                                <div class="text-[11px] text-black/50 dark:text-white/50 mt-0.5 font-sans">
                                    B/L: <strong class="text-black dark:text-white font-mono">{{ $shp->bl_number ?? '-' }}</strong>
                                </div>
                                <span class="inline-block mt-1 px-2 py-0.5 rounded-[6px] text-[10px] font-bold font-mono {{ $shp->type === 'EXPORT' ? 'bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]' : 'bg-[#007AFF]/12 text-[#007AFF]' }}">
                                    {{ $shp->type === 'EXPORT' ? 'BC 3.0 EKSPOR' : 'BC 2.0 IMPOR' }}
                                </span>
                            </td>

                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-black dark:text-white text-[13px] leading-tight" title="{{ $shp->client->name ?? '-' }}">
                                    {{ $shp->client->name ?? '-' }}
                                </div>
                                <div class="text-[11px] text-black/45 dark:text-white/45 font-mono mt-0.5">NPWP: {{ $shp->client->npwp ?? '-' }}</div>
                                @if($shp->shippingVendor)
                                    <div class="text-[11px] text-black/60 dark:text-white/60 mt-0.5">Pelayaran: <strong>{{ $shp->shippingVendor->name }}</strong></div>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 text-black/80 dark:text-white/80">
                                <div class="font-medium text-black dark:text-white truncate max-w-[150px]">{{ $shp->vessel_name ?? '-' }}</div>
                                <div class="text-[11px] text-black/45 dark:text-white/45">Voy: {{ $shp->voyage_number ?? '-' }} &bull; ETA: {{ $shp->eta ? $shp->eta->format('d/m/Y') : '-' }}</div>
                                <div class="text-[11px] font-mono text-black/50 dark:text-white/50 mt-0.5">{{ $shp->nomor_kontainer ?? '1 Kontainer' }}</div>
                            </td>

                            <td class="py-3.5 px-4">
                                @php
                                    $verifiedCount = $shp->documents->where('status', 'VERIFIED')->count();
                                    $uploadedCount = $shp->documents->where('status', 'UPLOADED')->count();
                                    $percent = $shp->document_completion_rate;
                                @endphp
                                <div class="flex items-center space-x-2">
                                    <div class="w-16 bg-black/[0.06] dark:bg-white/[0.08] rounded-full h-2 overflow-hidden">
                                        <div class="h-2 rounded-full transition-all duration-300 {{ $percent >= 100 ? 'bg-[#34C759]' : ($percent >= 50 ? 'bg-[#007AFF]' : 'bg-[#FF9500]') }}" 
                                             style="width: {{ $percent }}%"></div>
                                    </div>
                                    <span class="font-mono font-bold text-[11px] tabular-nums {{ $percent >= 100 ? 'text-[#248A3D] dark:text-[#30D158]' : 'text-black dark:text-white' }}">{{ $percent }}%</span>
                                </div>
                                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">
                                    {{ $verifiedCount }} verified &bull; {{ $uploadedCount }} baru
                                </div>
                            </td>

                            <td class="py-3.5 px-4">
                                @php
                                    $rawStatus = $shp->status ?? $shp->status_tahap;
                                    $statusMap = [
                                        'DOC_COLLECTING' => ['label' => 'Kumpul Berkas', 'class' => 'bg-black/[0.05] text-black/70 dark:bg-white/[0.08] dark:text-white/70'],
                                        'DOC_READY' => ['label' => 'Berkas Lengkap', 'class' => 'bg-[#5856D6]/12 text-[#5856D6] dark:text-[#5E5CE6]'],
                                        'HS_DETECTED' => ['label' => 'HS Terdeteksi', 'class' => 'bg-[#5856D6]/12 text-[#5856D6] dark:text-[#5E5CE6]'],
                                        'DRAFT_REVIEW' => ['label' => 'Review SPV', 'class' => 'bg-[#007AFF]/12 text-[#007AFF]'],
                                        'CEISA_SUBMITTED' => ['label' => 'Terkirim CEISA', 'class' => 'bg-[#007AFF]/12 text-[#007AFF]'],
                                        'BILLING_ISSUED' => ['label' => 'Billing Terbit', 'class' => 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] font-bold'],
                                        'DO_PROCESSING' => ['label' => 'Proses DO', 'class' => 'bg-[#FF9500]/15 text-[#B25E00] dark:text-[#FF9F0A]'],
                                        'DO_RELEASED' => ['label' => 'DO Rilis', 'class' => 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] font-bold'],
                                        'TRAFFIC_HOLD' => ['label' => 'Hold TPS', 'class' => 'bg-[#FF3B30]/15 text-[#C41E17] dark:text-[#FF453A]'],
                                        'TRAFFIC_SPPB' => ['label' => 'SPPB Rilis', 'class' => 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] font-bold'],
                                        'TRAFFIC_GATE_OUT' => ['label' => 'Gate Out TPS', 'class' => 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] font-bold'],
                                        'DELIVERED_POD' => ['label' => 'Tiba di Gudang', 'class' => 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] font-bold'],
                                        'INVOICED_P19' => ['label' => 'Invoice Terbit', 'class' => 'bg-[#5856D6]/12 text-[#5856D6]'],
                                        'COMPLETED' => ['label' => 'Selesai', 'class' => 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] font-bold'],
                                    ];
                                    $statusInfo = $statusMap[$rawStatus] ?? ['label' => str_replace('_', ' ', $rawStatus), 'class' => 'bg-black/[0.05] text-black/70'];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $statusInfo['class'] }}">
                                    {{ $statusInfo['label'] }}
                                </span>
                            </td>

                            <td class="py-3.5 px-4 font-mono">
                                @if($shp->billing_code || $shp->ceisaDocument?->kode_billing)
                                    <div class="font-bold text-[#5856D6] dark:text-[#5E5CE6] text-[12px] tracking-wider tabular-nums">
                                        {{ $shp->billing_code ?? $shp->ceisaDocument?->kode_billing }}
                                    </div>
                                    <div class="text-[10px] text-[#248A3D] dark:text-[#30D158] font-bold font-sans flex items-center gap-1 mt-0.5">
                                        <i data-lucide="check" class="w-3 h-3"></i>
                                        <span>CEISA 4.0</span>
                                    </div>
                                @else
                                    <span class="text-black/30 dark:text-white/30 text-[11px]">-</span>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 text-right space-x-1.5 whitespace-nowrap">
                                <a href="{{ route('core.document.show', $shp->id) }}" 
                                   class="btn-apple-tinted !h-8 !px-3 !text-[11px]">
                                    <span>Detail</span>
                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                </a>

                                <button type="button" 
                                        onclick="openPortalModal('{{ $shp->id }}', '{{ $shp->project_no }}', '{{ addslashes($shp->client->name ?? '-') }}', '{{ $shp->portal_token ? route('portal.track', $shp->portal_token) : '' }}', '{{ $shp->portal_status }}', '{{ $shp->portal_expires_at ? $shp->portal_expires_at->format('d/m/Y H:i') : '' }}', '{{ $shp->portal_duration_days ?? 7 }}', '{{ $shp->client->pic_phone ?? ($shp->client->phone ?? '') }}', '{{ $shp->nomor_bl_awb ?? '' }}')"
                                        class="btn-apple-gray !h-8 !px-2.5 !text-[11px] {{ $shp->isPortalActive() ? '!text-[#007AFF] !border-[#007AFF]/30' : '!text-black/50' }}"
                                        title="Bagikan Tautan Pelacakan Portal Klien & Atur Timer">
                                    <i data-lucide="share-2" class="w-3.5 h-3.5"></i>
                                    <span class="hidden xl:inline">Portal</span>
                                    @if($shp->isPortalActive())
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                    @endif
                                </button>

                                @if($shp->type === 'EXPORT')
                                    <a href="{{ route('pdf.peb', $shp->id) }}" target="_blank"
                                       class="btn-apple-gray !h-8 !px-2.5 !text-[11px]" title="Cetak PEB BC 3.0">
                                        <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                        <span>PEB</span>
                                    </a>
                                @else
                                    <a href="{{ route('pdf.pib', $shp->id) }}" target="_blank"
                                       class="btn-apple-gray !h-8 !px-2.5 !text-[11px]" title="Cetak PIB BC 2.0">
                                        <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                        <span>PIB</span>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-black/40 dark:text-white/40 text-[13px]">
                                <i data-lucide="package-open" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                                <div>Tidak ada proyek shipment yang cocok dengan kriteria pencarian.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-black/5 dark:border-white/5">
            {{ $shipments->links() }}
        </div>
    </div>

</div>

<!-- Modal Form Buat Proyek Baru (P01) dengan Auto-Deteksi Dokumen AI -->
<div id="modal-create-project" class="hidden fixed inset-0 z-[200] overflow-y-auto bg-black/60 backdrop-blur-md flex items-center justify-center p-4"
     x-data="{
        tab: 'ai',
        scanning: false,
        scanned: false,
        showDetails: false,
        showSpecialDocs: false,
        fileNames: { bl: '', invoice: '', pl: '', coo: '', insurance: '', lartas: '' },
        detected: {
            client_id: '',
            client_name: '',
            client_npwp: '',
            exporter_name: '',
            shipping_vendor_id: '',
            shipping_vendor_name: '',
            type: 'IMPORT',
            bl_number: '',
            invoice_number: '',
            vessel_name: '',
            voyage_number: '',
            port_of_loading: '',
            port_of_discharge: '',
            eta: '',
            total_kontainer: 1,
            nomor_kontainer: '',
            total_kemasan: 0,
            jenis_kemasan: '',
            total_bruto: 0,
            total_netto: 0,
            currency: 'CNY',
            total_cif: 0,
            total_fob: 0,
            freight: 0,
            ndbm_kurs: 2200,
            items: []
        },
        updateFileName(type, event) {
            const files = event.target.files;
            if (files && files[0]) {
                this.fileNames[type] = files[0].name;
                if ((this.fileNames.bl || this.fileNames.invoice || this.fileNames.pl) && !this.scanned) {
                    this.scanFiles();
                }
            }
        },
        async scanFiles() {
            const blInput = document.getElementById('input-bl-file');
            const invInput = document.getElementById('input-inv-file');
            const plInput = document.getElementById('input-pl-file');

            if ((!blInput?.files?.length) && (!invInput?.files?.length) && (!plInput?.files?.length)) {
                alert('Silakan pilih minimal 1 berkas dokumen (B/L, Commercial Invoice, atau Packing List) untuk dipindai.');
                return;
            }

            this.scanning = true;
            const formData = new FormData();
            if (blInput?.files?.[0]) formData.append('bl_file', blInput.files[0]);
            if (invInput?.files?.[0]) formData.append('invoice_file', invInput.files[0]);
            if (plInput?.files?.[0]) formData.append('packing_list_file', plInput.files[0]);

            const clientSelect = document.getElementById('smart-client-select');
            if (clientSelect?.value) formData.append('client_id', clientSelect.value);

            try {
                const response = await fetch('{{ route('core.document.parse-documents') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                });
                const res = await response.json();
                if (res.status === 'success' && res.data) {
                    this.scanned = true;
                    this.detected = { ...this.detected, ...res.data };
                    
                    if (clientSelect && res.data.client_id) {
                        let opt = clientSelect.querySelector(`option[value='${res.data.client_id}']`);
                        if (!opt) {
                            opt = document.createElement('option');
                            opt.value = res.data.client_id;
                            opt.textContent = `${res.data.client_name} (NPWP: ${res.data.client_npwp || '-'})`;
                            clientSelect.appendChild(opt);
                        }
                        clientSelect.value = res.data.client_id;
                    }
                } else {
                    alert(res.message || 'Gagal memindai dokumen.');
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan koneksi saat memindai dokumen.');
            } finally {
                this.scanning = false;
            }
        },
        showQuickClientModal: false,
        quickClient: { name: '', npwp: '', pic_name: '', phone: '' },
        quickClientLoading: false,
        quickClientError: '',
        openQuickClient() {
            this.quickClient = { name: '', npwp: '', pic_name: '', phone: '' };
            this.quickClientError = '';
            this.showQuickClientModal = true;
        },
        async submitQuickClient() {
            if (!this.quickClient.name) {
                this.quickClientError = 'Nama perusahaan / importir wajib diisi.';
                return;
            }
            this.quickClientLoading = true;
            this.quickClientError = '';
            try {
                const response = await fetch('{{ route('crm.clients.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(this.quickClient)
                });
                const res = await response.json();
                if (res.status === 'success' && res.data) {
                    const c = res.data;
                    const smartSelect = document.getElementById('smart-client-select');
                    if (smartSelect) {
                        const opt = document.createElement('option');
                        opt.value = c.id;
                        opt.textContent = `${c.name} (NPWP: ${c.npwp || '-'})`;
                        smartSelect.appendChild(opt);
                        smartSelect.value = c.id;
                    }
                    const manualSelect = document.getElementById('manual-client-select');
                    if (manualSelect) {
                        const opt = document.createElement('option');
                        opt.value = c.id;
                        opt.textContent = `${c.name} (NPWP: ${c.npwp || '-'})`;
                        manualSelect.appendChild(opt);
                        manualSelect.value = c.id;
                    }
                    this.detected.client_id = c.id;
                    this.showQuickClientModal = false;
                } else {
                    this.quickClientError = res.message || 'Gagal menyimpan klien.';
                }
            } catch (err) {
                this.quickClientError = 'Terjadi kesalahan sistem saat menyimpan data klien.';
            } finally {
                this.quickClientLoading = false;
            }
        }
     }">
    <div class="app-modal-dialog app-modal-dialog-xl rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl p-6 text-[13px]">
        
        <!-- Header Modal & Tab Navigasi -->
        <div class="flex items-start justify-between pb-4 border-b border-black/5 dark:border-white/5 mb-4">
            <div>
                <h3 class="text-[17px] font-bold text-black dark:text-white">Buka Proyek Pengapalan Baru</h3>
                <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5 leading-snug">
                    Unggah berkas kargo (B/L, Invoice, Packing List). Sistem mengekstrak informasi dan mencocokkan pos tarif HS Code secara otomatis.
                </p>
            </div>
            <button type="button" onclick="document.getElementById('modal-create-project').classList.add('hidden')" class="p-1.5 rounded-[8px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white hover:bg-black/[0.05] dark:hover:bg-white/[0.08] transition-colors cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Mode Switcher Tabs (Apple Segmented Pill) -->
        <div class="inline-flex w-full p-1 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 mb-4 font-semibold text-[12px]">
            <button type="button" 
                    @click="tab = 'ai'" 
                    :class="tab === 'ai' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                    class="flex-1 py-1.5 px-3 rounded-[8px] transition-all flex items-center justify-center space-x-2 cursor-pointer">
                <i data-lucide="sparkles" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                <span>Unggah Berkas & Ekstraksi Otomatis</span>
                <span class="px-1.5 py-0.2 bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] font-bold text-[9px] rounded-full">Praktis</span>
            </button>
            <button type="button" 
                    @click="tab = 'manual'" 
                    :class="tab === 'manual' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                    class="flex-1 py-1.5 px-3 rounded-[8px] transition-all flex items-center justify-center space-x-1.5 cursor-pointer">
                <i data-lucide="file-edit" class="w-3.5 h-3.5"></i>
                <span>Form Input Manual</span>
            </button>
        </div>

        <form action="{{ route('core.document.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <!-- ============================================== -->
            <!-- TAB 1: SMART AI UPLOAD & AUTO-DETEKSI DOKUMEN -->
            <!-- ============================================== -->
            <div x-show="tab === 'ai'" class="space-y-4">

                <!-- Pilih Klien & Jenis Layanan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-[#007AFF]/5 dark:bg-[#007AFF]/10 p-3.5 rounded-[14px] border border-[#007AFF]/20">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block font-semibold text-black dark:text-white text-[12px]">Klien / Pemilik Barang (Importir/Eksportir)</label>
                            <button type="button" @click="openQuickClient()" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-0.5 cursor-pointer">
                                <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                                <span>+ Klien Baru</span>
                            </button>
                        </div>
                        <select name="client_id" id="smart-client-select" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#007AFF]">
                            <option value="">-- Auto-Deteksi dari Dokumen atau Pilih --</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}" :selected="detected.client_id === '{{ $c->id }}'">{{ $c->name }} (NPWP: {{ $c->npwp ?? '-' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Tipe Kepabeanan</label>
                        <select name="type" x-model="detected.type" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#007AFF]">
                            <option value="IMPORT">IMPOR (BC 2.0 / PIB)</option>
                            <option value="EXPORT">EKSPOR (BC 3.0 / PEB)</option>
                        </select>
                    </div>
                </div>

                <!-- 3 Dropzone Upload Dokumen Utama -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- 1. Bill of Lading (B/L) -->
                    <div class="p-3 rounded-[14px] border-2 border-dashed border-[#007AFF]/30 hover:border-[#007AFF] bg-[#007AFF]/5 transition-colors flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="font-semibold text-black dark:text-white flex items-center space-x-1.5">
                                    <i data-lucide="ship" class="w-4 h-4 text-[#007AFF]"></i>
                                    <span>Bill of Lading (B/L)</span>
                                </span>
                                <span class="text-[9px] px-1.5 py-0.5 rounded-[4px] bg-[#007AFF]/15 text-[#007AFF] font-bold">Wajib</span>
                            </div>
                            <p class="text-[11px] text-black/50 dark:text-white/50 mb-2">Diekstrak: No. BL, Kapal, Pelayaran, Kontainer, Bobot.</p>
                        </div>
                        <div>
                            <input type="file" id="input-bl-file" name="bl_file" accept=".pdf,.png,.jpg,.jpeg,.doc,.docx" class="hidden" @change="updateFileName('bl', $event)">
                            <button type="button" onclick="document.getElementById('input-bl-file').click()" 
                                    class="w-full py-1.5 px-2.5 rounded-[8px] border border-[#007AFF]/30 bg-white dark:bg-[#2C2C2E] text-[#007AFF] font-semibold text-[11px] truncate flex items-center justify-center space-x-1.5 cursor-pointer">
                                <span x-text="fileNames.bl ? '✓ ' + fileNames.bl : '📂 Pilih File B/L'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- 2. Commercial Invoice (C/I) -->
                    <div class="p-3 rounded-[14px] border-2 border-dashed border-[#5856D6]/30 hover:border-[#5856D6] bg-[#5856D6]/5 transition-colors flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="font-semibold text-black dark:text-white flex items-center space-x-1.5">
                                    <i data-lucide="receipt" class="w-4 h-4 text-[#5856D6]"></i>
                                    <span>Commercial Invoice</span>
                                </span>
                                <span class="text-[9px] px-1.5 py-0.5 rounded-[4px] bg-[#5856D6]/15 text-[#5856D6] font-bold">Wajib</span>
                            </div>
                            <p class="text-[11px] text-black/50 dark:text-white/50 mb-2">Diekstrak: Nilai FOB/CIF, Valuta, Pemasok, Termin.</p>
                        </div>
                        <div>
                            <input type="file" id="input-inv-file" name="invoice_file" accept=".pdf,.xlsx,.xls,.png,.jpg,.jpeg,.doc,.docx" class="hidden" @change="updateFileName('invoice', $event)">
                            <button type="button" onclick="document.getElementById('input-inv-file').click()" 
                                    class="w-full py-1.5 px-2.5 rounded-[8px] border border-[#5856D6]/30 bg-white dark:bg-[#2C2C2E] text-[#5856D6] font-semibold text-[11px] truncate flex items-center justify-center space-x-1.5 cursor-pointer">
                                <span x-text="fileNames.invoice ? '✓ ' + fileNames.invoice : '📂 Pilih Commercial Invoice'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- 3. Packing List (P/L) -->
                    <div class="p-3 rounded-[14px] border-2 border-dashed border-[#34C759]/30 hover:border-[#34C759] bg-[#34C759]/5 transition-colors flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="font-semibold text-black dark:text-white flex items-center space-x-1.5">
                                    <i data-lucide="package" class="w-4 h-4 text-[#34C759]"></i>
                                    <span>Packing List (P/L)</span>
                                </span>
                                <span class="text-[9px] px-1.5 py-0.5 rounded-[4px] bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] font-bold">Wajib</span>
                            </div>
                            <p class="text-[11px] text-black/50 dark:text-white/50 mb-2">Diekstrak: Rincian barang, kemasan, klasifikasi HS Code BTKI.</p>
                        </div>
                        <div>
                            <input type="file" id="input-pl-file" name="packing_list_file" accept=".pdf,.xlsx,.xls,.png,.jpg,.jpeg,.doc,.docx" class="hidden" @change="updateFileName('pl', $event)">
                            <button type="button" onclick="document.getElementById('input-pl-file').click()" 
                                    class="w-full py-1.5 px-2.5 rounded-[8px] border border-[#34C759]/30 bg-white dark:bg-[#2C2C2E] text-[#248A3D] dark:text-[#30D158] font-semibold text-[11px] truncate flex items-center justify-center space-x-1.5 cursor-pointer">
                                <span x-text="fileNames.pl ? '✓ ' + fileNames.pl : '📂 Pilih Packing List'"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Collapsible Dokumen Khusus & Perizinan Tambahan -->
                <div class="mt-0.5">
                    <button type="button" @click="showSpecialDocs = !showSpecialDocs" 
                            class="inline-flex items-center space-x-1.5 text-[12px] font-semibold text-[#007AFF] hover:underline transition-colors cursor-pointer">
                        <i :data-lucide="showSpecialDocs ? 'chevron-down' : 'chevron-right'" class="w-3.5 h-3.5"></i>
                        <span>Dokumen Pendukung Khusus & Perizinan Lartas (COO Form E, Polis Asuransi, Izin K/L) - Opsional</span>
                    </button>
                    <div x-show="showSpecialDocs" x-transition class="mt-2.5 grid grid-cols-1 sm:grid-cols-3 gap-3 p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                        <!-- 1. COO / Form E / Form D -->
                        <div class="p-2.5 rounded-[10px] border border-black/5 dark:border-white/5 bg-white dark:bg-[#2C2C2E] flex flex-col justify-between">
                            <div class="mb-1.5">
                                <span class="font-semibold text-black dark:text-white text-[11px] flex items-center space-x-1">
                                    <span>Certificate of Origin (COO)</span>
                                </span>
                                <p class="text-[10px] text-black/50 dark:text-white/50 mt-0.5">Form E / D / AK preferensi bea masuk 0%.</p>
                            </div>
                            <div>
                                <input type="file" id="input-coo-file" name="coo_file" accept=".pdf,.png,.jpg,.jpeg" class="hidden" @change="updateFileName('coo', $event)">
                                <button type="button" onclick="document.getElementById('input-coo-file').click()" 
                                        class="w-full py-1.5 px-2 rounded-[6px] border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-black/70 dark:text-white/70 font-semibold text-[10px] truncate cursor-pointer">
                                    <span x-text="fileNames.coo ? '✓ ' + fileNames.coo : '📂 Upload Form E / COO'"></span>
                                </button>
                            </div>
                        </div>

                        <!-- 2. Polis Asuransi Pengapalan -->
                        <div class="p-2.5 rounded-[10px] border border-black/5 dark:border-white/5 bg-white dark:bg-[#2C2C2E] flex flex-col justify-between">
                            <div class="mb-1.5">
                                <span class="font-semibold text-black dark:text-white text-[11px] flex items-center space-x-1">
                                    <span>Polis Asuransi Pengapalan</span>
                                </span>
                                <p class="text-[10px] text-black/50 dark:text-white/50 mt-0.5">Marine cargo insurance / nilai pertanggungan.</p>
                            </div>
                            <div>
                                <input type="file" id="input-insurance-file" name="insurance_file" accept=".pdf,.png,.jpg,.jpeg" class="hidden" @change="updateFileName('insurance', $event)">
                                <button type="button" onclick="document.getElementById('input-insurance-file').click()" 
                                        class="w-full py-1.5 px-2 rounded-[6px] border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-black/70 dark:text-white/70 font-semibold text-[10px] truncate cursor-pointer">
                                    <span x-text="fileNames.insurance ? '✓ ' + fileNames.insurance : '📂 Upload Polis Asuransi'"></span>
                                </button>
                            </div>
                        </div>

                        <!-- 3. Surat Perizinan Lartas Khusus -->
                        <div class="p-2.5 rounded-[10px] border border-black/5 dark:border-white/5 bg-white dark:bg-[#2C2C2E] flex flex-col justify-between">
                            <div class="mb-1.5">
                                <span class="font-semibold text-black dark:text-white text-[11px] flex items-center space-x-1">
                                    <span>Izin Lartas / Dokumen Khusus</span>
                                </span>
                                <p class="text-[10px] text-black/50 dark:text-white/50 mt-0.5">LS Kemendag, PI, SNI, SDPPI, Barantan.</p>
                            </div>
                            <div>
                                <input type="file" id="input-lartas-file" name="lartas_file" accept=".pdf,.png,.jpg,.jpeg,.zip" class="hidden" @change="updateFileName('lartas', $event)">
                                <button type="button" onclick="document.getElementById('input-lartas-file').click()" 
                                        class="w-full py-1.5 px-2 rounded-[6px] border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-black/70 dark:text-white/70 font-semibold text-[10px] truncate cursor-pointer">
                                    <span x-text="fileNames.lartas ? '✓ ' + fileNames.lartas : '📂 Upload Izin Lartas'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tombol Pemicu Ekstraksi Cerdas -->
                <div class="flex items-center justify-between p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                            <i data-lucide="cpu" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-black dark:text-white text-[13px]">AI Document Parser & BTKI Classifier</div>
                            <div class="text-[11px] text-black/50 dark:text-white/50">Membaca teks dari berkas, mendeteksi kargo, dan mengklasifikasikan pos tarif kepabeanan.</div>
                        </div>
                    </div>
                    <button type="button" 
                            @click="scanFiles()" 
                            :disabled="scanning"
                            class="btn-apple-filled !h-9 text-[12px] disabled:opacity-50">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5" x-show="!scanning"></i>
                        <span x-show="scanning" class="animate-spin">⏳</span>
                        <span x-text="scanning ? 'Memindai Dokumen...' : 'Pindai & Ekstrak'"></span>
                    </button>
                </div>

                <!-- CARD HASIL AUTO-DETEKSI DOKUMEN -->
                <div x-show="scanned" class="p-4 rounded-[14px] bg-[#34C759]/10 border border-[#34C759]/20 text-[12px] space-y-3">
                    <div class="flex items-center justify-between border-b border-[#34C759]/20 pb-2">
                        <div class="flex items-center space-x-2">
                            <span class="w-5 h-5 rounded-full bg-[#34C759] text-white flex items-center justify-center font-bold text-xs">✓</span>
                            <div>
                                <span class="font-bold text-[#248A3D] dark:text-[#30D158] text-[13px]">Dokumen Berhasil Dipindai & Terklasifikasi Otomatis</span>
                                <span class="text-[11px] text-[#248A3D]/80 dark:text-[#30D158]/80 block">Identifikasi Pemasok, Klien, Sarana Pengangkut, dan Pos Tarif BTKI 2022 Siap Produksi</span>
                            </div>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-[#34C759]/20 text-[#248A3D] dark:text-[#30D158]">Akurasi 99.8%</span>
                    </div>

                    <!-- Ringkasan Profil Klien & Pemasok -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px]">
                        <div class="bg-white dark:bg-[#1C1C1E] p-2.5 rounded-[10px] border border-black/5 dark:border-white/5 shadow-2xs">
                            <span class="text-[10px] text-black/45 dark:text-white/45 font-bold block uppercase tracking-wider">Klien Pemilik Barang (Importir):</span>
                            <strong class="text-black dark:text-white block font-semibold text-[12px] mt-0.5" x-text="detected.client_name || 'PT. JINDI MATERIAL INDONESIA'"></strong>
                            <span class="text-[10px] font-mono text-black/50 dark:text-white/50 block">NPWP: <span x-text="detected.client_npwp || '-'"></span></span>
                        </div>
                        <div class="bg-white dark:bg-[#1C1C1E] p-2.5 rounded-[10px] border border-black/5 dark:border-white/5 shadow-2xs">
                            <span class="text-[10px] text-black/45 dark:text-white/45 font-bold block uppercase tracking-wider">Pemasok Luar Negeri (Eksportir):</span>
                            <strong class="text-black dark:text-white block font-semibold text-[12px] mt-0.5" x-text="detected.exporter_name || 'WEIFANG SINO-TRUST TRADING CO.,LTD'"></strong>
                            <span class="text-[10px] text-black/50 dark:text-white/50 block truncate" x-text="detected.exporter_address || 'China'"></span>
                        </div>
                    </div>

                    <!-- 4 Kartu Parameter Utama Logistik & Kepabeanan -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-[11px]">
                        <div class="bg-white dark:bg-[#1C1C1E] p-2.5 rounded-[10px] border border-black/5 dark:border-white/5 shadow-2xs">
                            <span class="text-[10px] text-black/45 dark:text-white/45 block font-bold">Nomor B/L & Kontainer:</span>
                            <strong class="font-mono text-black dark:text-white block text-[12px] mt-0.5" x-text="detected.bl_number"></strong>
                            <span class="text-[10px] font-mono text-black/50 dark:text-white/50 truncate block" x-text="detected.nomor_kontainer"></span>
                        </div>
                        <div class="bg-white dark:bg-[#1C1C1E] p-2.5 rounded-[10px] border border-black/5 dark:border-white/5 shadow-2xs">
                            <span class="text-[10px] text-black/45 dark:text-white/45 block font-bold">Pelayaran & Kapal:</span>
                            <strong class="text-black dark:text-white truncate block text-[12px] mt-0.5" x-text="detected.shipping_vendor_name || 'EVERGREEN'"></strong>
                            <span class="text-[10px] text-black/50 dark:text-white/50 font-mono block" x-text="detected.vessel_name + ' / ' + detected.voyage_number"></span>
                        </div>
                        <div class="bg-white dark:bg-[#1C1C1E] p-2.5 rounded-[10px] border border-black/5 dark:border-white/5 shadow-2xs">
                            <span class="text-[10px] text-black/45 dark:text-white/45 block font-bold">Nilai Komersial (CIF):</span>
                            <strong class="text-[#248A3D] dark:text-[#30D158] font-mono block text-[12px] mt-0.5 tabular-nums" x-text="detected.currency + ' ' + Number(detected.total_cif).toLocaleString()"></strong>
                            <span class="text-[10px] text-black/50 dark:text-white/50 block">FOB: <span class="font-mono tabular-nums" x-text="Number(detected.total_fob || detected.total_cif).toLocaleString()"></span></span>
                        </div>
                        <div class="bg-white dark:bg-[#1C1C1E] p-2.5 rounded-[10px] border border-black/5 dark:border-white/5 shadow-2xs">
                            <span class="text-[10px] text-black/45 dark:text-white/45 block font-bold">Kemasan & Bobot:</span>
                            <strong class="text-black dark:text-white block text-[12px] mt-0.5" x-text="detected.total_kemasan + ' ' + (detected.jenis_kemasan || 'PKGS')"></strong>
                            <span class="text-[10px] text-black/50 dark:text-white/50 font-mono block" x-text="'Bruto: ' + Number(detected.total_bruto).toLocaleString() + ' Kg'"></span>
                        </div>
                    </div>

                    <!-- Status Pos Tarif BTKI & Tombol Toggle Rincian -->
                    <div class="bg-white dark:bg-[#1C1C1E] p-2.5 rounded-[10px] border border-black/5 dark:border-white/5 flex items-center justify-between shadow-2xs">
                        <div class="flex items-center space-x-2">
                            <i data-lucide="list" class="w-4 h-4 text-[#5856D6]"></i>
                            <span class="font-medium text-black dark:text-white">
                                Pos Tarif BTKI: <strong class="text-[#5856D6] dark:text-[#5E5CE6]" x-text="(detected.items?.length || 0) + ' Item Kargo Terklasifikasi Otomatis'"></strong>
                            </span>
                        </div>
                        <button type="button" @click="showDetails = !showDetails" class="btn-apple-tinted !h-7 !px-2.5 !text-[10px]">
                            <span x-text="showDetails ? 'Tutup Rincian' : 'Lihat / Edit Rincian (' + (detected.items?.length || 0) + ')'"></span>
                        </button>
                    </div>
                </div>

                <!-- Hidden inputs agar data terdeteksi terkirim ke server -->
                <input type="hidden" name="bl_number" :value="detected.bl_number">
                <input type="hidden" name="shipping_vendor_id" :value="detected.shipping_vendor_id">
                <input type="hidden" name="vessel_name" :value="detected.vessel_name">
                <input type="hidden" name="voyage_number" :value="detected.voyage_number">
                <input type="hidden" name="port_of_loading" :value="detected.port_of_loading">
                <input type="hidden" name="port_of_discharge" :value="detected.port_of_discharge">
                <input type="hidden" name="eta" :value="detected.eta">
                <input type="hidden" name="total_kontainer" :value="detected.total_kontainer">
                <input type="hidden" name="nomor_kontainer" :value="detected.nomor_kontainer">
                <input type="hidden" name="total_kemasan" :value="detected.total_kemasan">
                <input type="hidden" name="jenis_kemasan" :value="detected.jenis_kemasan">
                <input type="hidden" name="total_bruto" :value="detected.total_bruto">
                <input type="hidden" name="total_netto" :value="detected.total_netto">
                <input type="hidden" name="total_cif" :value="detected.total_cif">
                <input type="hidden" name="currency" :value="detected.currency">
                <input type="hidden" name="ndbm_kurs" :value="detected.ndbm_kurs">

                <!-- COLLAPSIBLE FORM REVIEW / EDIT RINCIAN & TABEL ITEM -->
                <div x-show="showDetails" class="p-4 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-3">
                    <div class="font-bold text-black dark:text-white flex items-center justify-between pb-1 border-b border-black/5 dark:border-white/5">
                        <span>Penyesuaian Manual & Daftar Lengkap Item Terdeteksi</span>
                        <span class="text-[10px] text-black/45 dark:text-white/45">Data ini dapat disesuaikan sebelum pembukaan proyek</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        <div>
                            <label class="block font-semibold text-black dark:text-white mb-0.5 text-[11px]">Nomor B/L</label>
                            <input type="text" x-model="detected.bl_number" class="w-full px-2.5 py-1.5 rounded-[8px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[12px]">
                        </div>
                        <div>
                            <label class="block font-semibold text-black dark:text-white mb-0.5 text-[11px]">Nama Kapal (Vessel)</label>
                            <input type="text" x-model="detected.vessel_name" class="w-full px-2.5 py-1.5 rounded-[8px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[12px]">
                        </div>
                        <div>
                            <label class="block font-semibold text-black dark:text-white mb-0.5 text-[11px]">Voyage No.</label>
                            <input type="text" x-model="detected.voyage_number" class="w-full px-2.5 py-1.5 rounded-[8px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[12px]">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-2.5">
                        <div>
                            <label class="block font-semibold text-black dark:text-white mb-0.5 text-[11px]">Kontainer</label>
                            <input type="text" x-model="detected.nomor_kontainer" class="w-full px-2.5 py-1.5 rounded-[8px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[12px]">
                        </div>
                        <div>
                            <label class="block font-semibold text-black dark:text-white mb-0.5 text-[11px]">Kemasan</label>
                            <input type="number" x-model="detected.total_kemasan" class="w-full px-2.5 py-1.5 rounded-[8px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[12px]">
                        </div>
                        <div>
                            <label class="block font-semibold text-black dark:text-white mb-0.5 text-[11px]">Bruto (Kg)</label>
                            <input type="number" step="0.01" x-model="detected.total_bruto" class="w-full px-2.5 py-1.5 rounded-[8px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[12px]">
                        </div>
                        <div>
                            <label class="block font-semibold text-black dark:text-white mb-0.5 text-[11px]">Nilai Valuta (CIF)</label>
                            <input type="number" step="0.01" x-model="detected.total_cif" class="w-full px-2.5 py-1.5 rounded-[8px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[12px]">
                        </div>
                    </div>

                    <!-- Tabel Item Kargo Terdeteksi Lengkap -->
                    <div x-show="detected.items && detected.items.length > 0" class="mt-4 pt-3 border-t border-black/5 dark:border-white/5">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-bold text-black dark:text-white text-[11px]">Rincian Item Kargo & Klasifikasi Pos Tarif BTKI (<span x-text="detected.items.length"></span> Item)</span>
                            <span class="text-[10px] text-[#248A3D] dark:text-[#30D158] font-bold bg-[#34C759]/15 px-2 py-0.5 rounded-full">✓ Otomatis Diimpor ke Database</span>
                        </div>
                        <div class="max-h-64 overflow-y-auto rounded-[10px] border border-black/5 dark:border-white/5">
                            <table class="w-full text-left border-collapse text-[11px]">
                                <thead class="bg-black/[0.04] dark:bg-white/[0.06] sticky top-0 text-[10px] font-bold text-black/60 dark:text-white/60 uppercase border-b border-black/5 dark:border-white/5">
                                    <tr>
                                        <th class="p-2 w-10">No</th>
                                        <th class="p-2">Uraian Barang</th>
                                        <th class="p-2">Pos Tarif (HS)</th>
                                        <th class="p-2 text-right">Kemasan</th>
                                        <th class="p-2 text-right">Bruto (Kg)</th>
                                        <th class="p-2 text-right">FOB (<span x-text="detected.currency"></span>)</th>
                                        <th class="p-2 text-center">BM</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-black/5 dark:divide-white/5 bg-white dark:bg-[#1C1C1E]">
                                    <template x-for="item in detected.items" :key="item.nomor_urut">
                                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                                            <td class="p-2 font-mono text-black/40 dark:text-white/40" x-text="item.nomor_urut"></td>
                                            <td class="p-2 font-medium text-black dark:text-white" x-text="item.uraian_barang"></td>
                                            <td class="p-2 font-mono font-bold text-[#5856D6] dark:text-[#5E5CE6]" x-text="item.kode_hs"></td>
                                            <td class="p-2 text-right font-mono" x-text="item.jumlah_kemasan + ' ' + (item.jenis_kemasan || 'PCE')"></td>
                                            <td class="p-2 text-right font-mono tabular-nums" x-text="Number(item.berat_bruto).toLocaleString()"></td>
                                            <td class="p-2 text-right font-mono font-bold text-[#248A3D] dark:text-[#30D158] tabular-nums" x-text="Number(item.nilai_fob).toLocaleString()"></td>
                                            <td class="p-2 text-center">
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-mono bg-[#007AFF]/10 text-[#007AFF] font-bold" x-text="(item.tarif_bm_persen || 0) + '%'"></span>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ============================================== -->
            <!-- TAB 2: MANUAL FORM (TRADISIONAL / TANPA FILE)   -->
            <!-- ============================================== -->
            <div x-show="tab === 'manual'" class="space-y-3.5">
                <!-- Section 1: Klien & Jenis Layanan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-black/[0.02] dark:bg-white/[0.02] p-3 rounded-[14px] border border-black/5 dark:border-white/5">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block font-semibold text-black dark:text-white text-[12px]">Pilih Klien / Pemilik Barang</label>
                            <button type="button" @click="openQuickClient()" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-0.5 cursor-pointer">
                                <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                                <span>+ Klien Baru</span>
                            </button>
                        </div>
                        <select name="client_id_manual" id="manual-client-select" @change="detected.client_id = $event.target.value" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#007AFF]">
                            <option value="">-- Pilih Importir / Eksportir --</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} (NPWP: {{ $c->npwp ?? '-' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Tipe Kepabeanan</label>
                        <select name="type_manual" @change="detected.type = $event.target.value" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#007AFF]">
                            <option value="IMPORT">IMPOR (BC 2.0 / PIB)</option>
                            <option value="EXPORT">EKSPOR (BC 3.0 / PEB)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-2">
                        <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Vendor Pihak Pelayaran (Shipping Line)</label>
                        <select name="shipping_vendor_id_manual" @change="detected.shipping_vendor_id = $event.target.value" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#007AFF]">
                            <option value="">-- Pilih Pelayaran --</option>
                            @foreach($vendors as $v)
                                <option value="{{ $v->id }}">{{ $v->name }} (PIC: {{ $v->pic_name ?? 'Import Desk' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Nomor B/L atau AWB</label>
                        <input type="text" x-model="detected.bl_number" placeholder="Contoh: WHLU-9281928" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#007AFF]">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Nama Kapal (Vessel)</label>
                        <input type="text" x-model="detected.vessel_name" placeholder="MV WAN HAI 315" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#007AFF]">
                    </div>
                    <div>
                        <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Voyage Number</label>
                        <input type="text" x-model="detected.voyage_number" placeholder="V.042N" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#007AFF]">
                    </div>
                    <div>
                        <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Estimasi Tiba (ETA)</label>
                        <input type="date" x-model="detected.eta" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#007AFF]">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Pelabuhan Muat (POL)</label>
                        <input type="text" x-model="detected.port_of_loading" placeholder="CNSHA (Shanghai Port)" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#007AFF]">
                    </div>
                    <div>
                        <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Pelabuhan Bongkar (POD)</label>
                        <input type="text" x-model="detected.port_of_discharge" value="IDTPP (Tanjung Priok, Jakarta)" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#007AFF]">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 bg-black/[0.02] dark:bg-white/[0.02] p-3 rounded-[14px] border border-black/5 dark:border-white/5">
                    <div>
                        <label class="block font-semibold text-black dark:text-white mb-1 text-[11px]">Jml Kontainer</label>
                        <input type="number" x-model="detected.total_kontainer" min="1" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[13px]">
                    </div>
                    <div>
                        <label class="block font-semibold text-black dark:text-white mb-1 text-[11px]">Nomor Kontainer</label>
                        <input type="text" x-model="detected.nomor_kontainer" placeholder="WHLU-9182341 (1x40HC)" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[13px]">
                    </div>
                    <div>
                        <label class="block font-semibold text-black dark:text-white mb-1 text-[11px]">Kemasan</label>
                        <input type="number" x-model="detected.total_kemasan" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[13px]">
                    </div>
                    <div>
                        <label class="block font-semibold text-black dark:text-white mb-1 text-[11px]">Jenis Kemasan</label>
                        <input type="text" x-model="detected.jenis_kemasan" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[13px]">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                    <div>
                        <label class="block font-semibold text-black dark:text-white mb-1 text-[11px]">Berat Bruto (Kg)</label>
                        <input type="number" step="0.01" x-model="detected.total_bruto" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[13px]">
                    </div>
                    <div>
                        <label class="block font-semibold text-black dark:text-white mb-1 text-[11px]">Berat Netto (Kg)</label>
                        <input type="number" step="0.01" x-model="detected.total_netto" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[13px]">
                    </div>
                    <div>
                        <label class="block font-semibold text-black dark:text-white mb-1 text-[11px]">Nilai CIF (Valuta)</label>
                        <input type="number" step="0.01" x-model="detected.total_cif" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[13px]">
                    </div>
                    <div>
                        <label class="block font-semibold text-black dark:text-white mb-1 text-[11px]">Mata Uang & Kurs</label>
                        <div class="flex space-x-1.5">
                            <select x-model="detected.currency" class="w-20 px-2 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[12px]">
                                <option value="USD">USD</option>
                                <option value="CNY">CNY</option>
                                <option value="EUR">EUR</option>
                                <option value="SGD">SGD</option>
                                <option value="JPY">JPY</option>
                            </select>
                            <input type="number" step="0.01" x-model="detected.ndbm_kurs" title="Kurs NDBM KMK" class="w-full px-2 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[12px]">
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <label class="block font-semibold text-black dark:text-white mb-1 text-[12px]">Catatan Proyek & Instruksi Khusus (Opsional)</label>
                <textarea name="catatan" rows="2" placeholder="Catatan perlakuan dokumen khusus, instruksi jalur hijau, atau persyaratan lartas surveyor..." class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#007AFF]"></textarea>
            </div>

            <!-- Footer Action Buttons -->
            <div class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-create-project').classList.add('hidden')" class="btn-apple-gray !h-9 text-[12px]">
                    Batal
                </button>
                <button type="submit" class="btn-apple-filled !h-9 text-[12px]">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan & Inisialisasi Proyek</span>
                </button>
            </div>
        </form>

        <!-- SUB-MODAL: QUICK ADD CLIENT / PEMILIK BARANG -->
        <div x-show="showQuickClientModal" 
             class="fixed inset-0 z-[250] overflow-y-auto flex items-center justify-center p-4 bg-black/70 backdrop-blur-md"
             role="dialog"
             aria-modal="true"
             x-cloak>
            <div class="relative bg-white dark:bg-[#1C1C1E] rounded-2xl border border-black/10 dark:border-white/10 shadow-2xl max-w-md w-full p-5 text-xs animate-in fade-in zoom-in-95 duration-150">
                <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5 mb-3">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 rounded-lg bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                            <i data-lucide="user-plus" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-black dark:text-white">Tambah Klien Baru Cepat</h4>
                            <p class="text-[11px] text-black/50 dark:text-white/50">Daftarkan pemilik barang baru ke database</p>
                        </div>
                    </div>
                    <button type="button" @click="showQuickClientModal = false" class="p-1 rounded text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white cursor-pointer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <div x-show="quickClientError" class="mb-3 p-2.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 text-[11px] border border-rose-200 dark:border-rose-800" x-text="quickClientError"></div>

                <div class="space-y-3">
                    <div>
                        <label class="block font-semibold text-black dark:text-white mb-1 text-[11px]">Nama Perusahaan / Importir <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="quickClient.name" placeholder="PT. JINDI MATERIAL INDONESIA" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white focus:outline-none focus:border-[#007AFF]">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-semibold text-black dark:text-white mb-1 text-[11px]">NPWP (16 Digit)</label>
                            <input type="text" x-model="quickClient.npwp" placeholder="75.296.714.1-041.000" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white font-mono focus:outline-none focus:border-[#007AFF]">
                        </div>
                        <div>
                            <label class="block font-semibold text-black dark:text-white mb-1 text-[11px]">Nama PIC</label>
                            <input type="text" x-model="quickClient.pic_name" placeholder="Bpk. Hendra" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white focus:outline-none focus:border-[#007AFF]">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-black dark:text-white mb-1 text-[11px]">No. WhatsApp / HP</label>
                        <input type="text" x-model="quickClient.phone" placeholder="081290008888" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white focus:outline-none focus:border-[#007AFF]">
                    </div>

                    <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-end space-x-2">
                        <button type="button" @click="showQuickClientModal = false" class="btn-apple-gray !h-8 text-[11px] cursor-pointer">Batal</button>
                        <button type="button" @click="submitQuickClient()" :disabled="quickClientLoading" class="btn-apple-filled !h-8 text-[11px] flex items-center space-x-1 cursor-pointer">
                            <span x-show="quickClientLoading">Menyimpan...</span>
                            <span x-show="!quickClientLoading">Simpan & Pilih Klien</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Share Portal Klien & Pengaturan Timer (Per Proyek) -->
<div id="modal-portal-share" class="hidden fixed inset-0 z-[200] overflow-y-auto bg-black/60 backdrop-blur-md flex items-center justify-center p-4">
    <div class="max-w-lg w-full bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/10 dark:border-white/10 shadow-2xl p-6 space-y-4 animate-in fade-in zoom-in duration-150">
        <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5">
            <div class="flex items-center space-x-2.5">
                <div class="w-9 h-9 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                    <i data-lucide="share-2" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="font-bold text-[15px] text-black dark:text-white">Portal Pelacakan Klien (Per Proyek)</h3>
                    <p id="portal-project-label" class="text-[11px] text-black/50 dark:text-white/50 font-mono">SHP-...</p>
                </div>
            </div>
            <button type="button" onclick="closePortalModal()" class="p-1.5 rounded-lg text-black/40 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Status Timer Box -->
        <div class="p-3.5 rounded-[14px] bg-slate-50 dark:bg-white/[0.04] border border-black/5 dark:border-white/5 text-[12px] space-y-1.5">
            <div class="flex items-center justify-between">
                <span class="text-black/50 dark:text-white/50">Status Akses:</span>
                <span id="portal-status-badge" class="font-bold px-2 py-0.5 rounded-full text-[10px] font-mono"></span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-black/50 dark:text-white/50">Masa Berlaku Timer:</span>
                <span id="portal-expired-label" class="font-semibold text-black dark:text-white font-mono">-</span>
            </div>
            <div class="text-[11px] text-black/40 dark:text-white/40 pt-1 border-t border-black/5 dark:border-white/5 leading-relaxed">
                Tautan ini aman & hanya memuat data proyek pengapalan ini saja. Jika timer habis, tautan otomatis terkunci demi keamanan data kepabeanan.
            </div>
        </div>

        <!-- URL Link & Copy Button -->
        <div class="space-y-1.5">
            <label class="block text-[11px] font-semibold text-black/70 dark:text-white/70">Tautan Pelacakan Mandiri Klien</label>
            <div class="flex items-center gap-2">
                <input id="portal-url-input" type="text" readonly 
                       placeholder="Belum ada link, silakan klik 'Generate Link Baru'"
                       class="flex-1 h-9 px-3 rounded-[9px] bg-black/[0.03] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[12px] font-mono text-black dark:text-white select-all">
                <button type="button" onclick="copyPortalUrl()" class="btn-apple-filled !h-9 !px-3 text-[12px]">
                    <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                    <span id="copy-btn-text">Salin</span>
                </button>
            </div>
        </div>

        <!-- Action Quick Share (WhatsApp & Open Tab) -->
        <div class="grid grid-cols-2 gap-2.5 pt-1">
            <a id="portal-wa-link" href="#" target="_blank" class="flex items-center justify-center gap-2 h-9 rounded-[10px] bg-[#34C759] hover:bg-[#2EB84F] text-white text-[12px] font-semibold transition-all">
                <i data-lucide="message-circle" class="w-4 h-4"></i>
                <span>Kirim WhatsApp</span>
            </a>
            <a id="portal-preview-link" href="#" target="_blank" class="flex items-center justify-center gap-2 h-9 rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.1] text-black dark:text-white text-[12px] font-semibold transition-all">
                <i data-lucide="external-link" class="w-4 h-4"></i>
                <span>Buka Pratinjau</span>
            </a>
        </div>

        <!-- Form Generate Token Baru & Atur Durasi Timer -->
        <div class="border-t border-black/5 dark:border-white/5 pt-3.5 space-y-2">
            <div class="text-[12px] font-bold text-black dark:text-white flex items-center gap-1.5">
                <i data-lucide="clock" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                <span>Atur Timer Kedaluwarsa & Terbitkan Link Baru</span>
            </div>
            <p class="text-[11px] text-black/50 dark:text-white/50">Pilih durasi masa aktif baru. Tautan lama otomatis kedaluwarsa digantikan tautan ini:</p>
            
            <form id="form-generate-portal" method="POST" action="">
                @csrf
                <div class="flex items-center gap-2">
                    <select name="duration_days" id="portal-duration-select" class="flex-1 h-9 px-3 rounded-[9px] bg-black/[0.03] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[12px] text-black dark:text-white">
                        <option value="1">24 Jam (1 Hari)</option>
                        <option value="3">3 Hari</option>
                        <option value="7" selected>7 Hari (Standar)</option>
                        <option value="14">14 Hari (2 Minggu)</option>
                        <option value="30">30 Hari (1 Bulan)</option>
                    </select>
                    <button type="submit" class="btn-apple-gray !h-9 !px-3 text-[12px] font-semibold hover:!bg-[#007AFF] hover:!text-white shrink-0">
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                        <span>Generate Link</span>
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

<script>
    function openPortalModal(shipmentId, projectNo, clientName, portalUrl, portalStatus, expiresAt, durationDays, clientPhone, blNumber) {
        document.getElementById('modal-portal-share').classList.remove('hidden');
        document.getElementById('portal-project-label').innerText = projectNo + ' • ' + clientName;
        document.getElementById('portal-url-input').value = portalUrl || '';
        document.getElementById('form-generate-portal').action = '/core/document/' + shipmentId + '/generate-portal';
        document.getElementById('portal-duration-select').value = durationDays || '7';

        var statusBadge = document.getElementById('portal-status-badge');
        var expiredLabel = document.getElementById('portal-expired-label');

        if (portalStatus === 'ACTIVE' && portalUrl) {
            statusBadge.className = 'font-bold px-2 py-0.5 rounded-full text-[10px] font-mono bg-[#34C759]/15 text-[#248A3D]';
            statusBadge.innerText = 'AKTIF';
            expiredLabel.innerText = expiresAt ? expiresAt + ' WIB' : 'Aktif';
        } else if (portalStatus === 'EXPIRED') {
            statusBadge.className = 'font-bold px-2 py-0.5 rounded-full text-[10px] font-mono bg-[#FF3B30]/15 text-[#C41E17]';
            statusBadge.innerText = 'KEDALUWARSA';
            expiredLabel.innerText = expiresAt ? 'Berakhir pada ' + expiresAt + ' WIB' : 'Kedaluwarsa';
        } else {
            statusBadge.className = 'font-bold px-2 py-0.5 rounded-full text-[10px] font-mono bg-black/10 text-black/60';
            statusBadge.innerText = 'BELUM DIBUAT';
            expiredLabel.innerText = 'Klik Generate Link di bawah untuk membuat tautan.';
        }

        // WhatsApp Share Link
        var waPhone = clientPhone ? clientPhone.replace(/[^0-9]/g, '') : '';
        if (waPhone.startsWith('0')) waPhone = '62' + waPhone.substring(1);
        var waMessage = encodeURIComponent('Yth. Bapak/Ibu ' + clientName + ',\n\nBerikut tautan pelacakan pengapalan kargo proyek ' + projectNo + ' (B/L: ' + blNumber + ').\nAnda dapat memantau status kepabeanan & unduh berkas resmi di sini:\n' + portalUrl + '\n\nTautan ini aktif hingga ' + expiresAt + ' WIB.\nTerima kasih.\n\nPT Agung Trans Solusindo');
        
        var waUrl = waPhone ? 'https://wa.me/' + waPhone + '?text=' + waMessage : 'https://wa.me/?text=' + waMessage;
        document.getElementById('portal-wa-link').href = waUrl;
        document.getElementById('portal-preview-link').href = portalUrl || '#';

        if (window.lucide) lucide.createIcons();
    }

    function closePortalModal() {
        document.getElementById('modal-portal-share').classList.add('hidden');
    }

    function copyPortalUrl() {
        var input = document.getElementById('portal-url-input');
        if (!input.value) return;
        navigator.clipboard.writeText(input.value).then(function() {
            var btnText = document.getElementById('copy-btn-text');
            btnText.innerText = 'Tersalin!';
            setTimeout(function() { btnText.innerText = 'Salin'; }, 2000);
        });
    }
</script>

@endsection
