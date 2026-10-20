@extends('layouts.app')

@section('title', 'Detail Proyek Pengapalan — ' . ($shipment->shipment_number ?? $shipment->project_no))

@section('content')
<div class="max-w-[1360px] mx-auto px-4 sm:px-6 space-y-6 pt-4 pb-28 lg:pb-12" x-data="{ activeTab: 'docs' }">

    <!-- 0. Header & Breadcrumb Toolbar -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-5 sm:p-6 border border-black/[0.06] dark:border-white/[0.08] shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-5">
        <div>
            <!-- Breadcrumbs -->
            <nav class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 font-medium mb-2" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors flex items-center gap-1">
                    <i data-lucide="home" class="w-3.5 h-3.5"></i>
                    <span>Dashboard</span>
                </a>
                <i data-lucide="chevron-right" class="w-3 h-3 text-slate-300 dark:text-slate-600"></i>
                <a href="{{ route('core.document.index') }}" class="hover:text-[#007AFF] transition-colors">
                    Proyek Pengapalan
                </a>
                <i data-lucide="chevron-right" class="w-3 h-3 text-slate-300 dark:text-slate-600"></i>
                <span class="text-slate-800 dark:text-white font-mono font-semibold">{{ $shipment->shipment_number ?? $shipment->project_no }}</span>
            </nav>

            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold font-mono text-slate-900 dark:text-white tracking-tight">
                    {{ $shipment->shipment_number ?? $shipment->project_no }}
                </h1>
                
                <!-- Status Pill -->
                <span class="px-3 py-1 rounded-full text-xs font-semibold font-mono tracking-tight
                    @if(in_array($shipment->status, ['BILLING_ISSUED', 'DO_RELEASED', 'DELIVERED_POD', 'COMPLETED'])) bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/40
                    @elseif(in_array($shipment->status, ['DOC_READY', 'HS_DETECTED', 'DRAFT_REVIEW'])) bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20
                    @elseif($shipment->status === 'DO_PROCESSING') bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200/80 dark:border-amber-800/40
                    @else bg-slate-100 dark:bg-white/10 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-white/10 @endif">
                    {{ $shipment->status }}
                </span>

                <!-- Type Badge -->
                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ $shipment->type === 'EXPORT' ? 'bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/20' : 'bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20' }}">
                    {{ $shipment->type === 'EXPORT' ? 'BC 3.0 EKSPOR' : 'BC 2.0 IMPOR' }}
                </span>
            </div>

            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 flex flex-wrap items-center gap-x-3 gap-y-1">
                <span>Klien: <strong class="text-slate-800 dark:text-white font-medium">{{ $shipment->client->name ?? '-' }}</strong></span>
                <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                <span>No. B/L: <strong class="text-slate-800 dark:text-white font-mono font-medium">{{ $shipment->bl_number ?? '-' }}</strong></span>
                <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                <span>Sarana Pengangkut: <span class="text-slate-700 dark:text-slate-300">{{ $shipment->vessel_name ?? '-' }} (Voy: {{ $shipment->voyage_number ?? '-' }})</span></span>
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-2">
            @if($shipment->type === 'EXPORT')
                <a href="{{ route('pdf.peb', $shipment->id) }}" target="_blank"
                   class="btn-apple-gray text-xs inline-flex items-center gap-1.5 px-3.5 py-2">
                    <i data-lucide="printer" class="w-3.5 h-3.5 text-slate-500"></i>
                    <span>Cetak PEB BC 3.0</span>
                </a>
            @else
                <a href="{{ route('pdf.pib', $shipment->id) }}" target="_blank"
                   class="btn-apple-gray text-xs inline-flex items-center gap-1.5 px-3.5 py-2">
                    <i data-lucide="printer" class="w-3.5 h-3.5 text-slate-500"></i>
                    <span>Cetak PIB BC 2.0</span>
                </a>
            @endif

            @if($shipment->ceisaDocument?->kode_billing || $shipment->billing_code)
                <a href="{{ route('pdf.billing', $shipment->id) }}" target="_blank"
                   class="btn-apple-tinted text-xs inline-flex items-center gap-1.5 px-3.5 py-2">
                    <i data-lucide="receipt" class="w-3.5 h-3.5"></i>
                    <span>Billing MPN G3</span>
                </a>
            @endif

            <form action="{{ route('core.document.sync-from-docs', $shipment->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" 
                        onclick="return confirm('Pindai ulang seluruh berkas terlampir dan perbarui data proyek secara otomatis?')"
                        class="btn-apple-gray text-xs inline-flex items-center gap-1.5 px-3.5 py-2" 
                        title="Pindai ulang dan sinkronkan metadata proyek & kargo dari dokumen">
                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                    <span>Sinkronkan Data</span>
                </button>
            </form>

            <a href="{{ route('pdf.do-request', $shipment->id) }}" target="_blank"
               class="btn-apple-gray text-xs inline-flex items-center gap-1.5 px-3.5 py-2" 
               title="Surat Permohonan Rilis DO ke Pelayaran">
                <i data-lucide="ship" class="w-3.5 h-3.5 text-amber-600"></i>
                <span>Surat Permohonan DO</span>
            </a>

            <!-- Portal Pelacakan Klien & Timer Button -->
            <button type="button" 
                    onclick="openPortalModalShow()"
                    class="btn-apple-gray text-xs inline-flex items-center gap-1.5 px-3.5 py-2 {{ $shipment->isPortalActive() ? '!text-[#007AFF] !border-[#007AFF]/30' : '' }}"
                    title="Bagikan Tautan Pelacakan Portal Klien & Atur Timer">
                <i data-lucide="share-2" class="w-3.5 h-3.5"></i>
                <span>Portal Klien</span>
                @if($shipment->isPortalActive())
                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                @endif
            </button>
        </div>
    </div>

    <!-- Alert Demurrage & Free Time Warning -->
    @if($shipment->is_demurrage_warning)
    <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200/80 dark:border-rose-900/50 text-rose-800 dark:text-rose-300 text-xs flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                <i data-lucide="alert-triangle" class="w-4 h-4"></i>
            </div>
            <div>
                <strong class="font-semibold block text-slate-900 dark:text-white">Peringatan Free Time Demurrage Pelabuhan</strong>
                <span class="text-rose-700 dark:text-rose-300">Sisa free time pelabuhan tinggal <strong class="font-semibold">{{ $shipment->days_left_free_time }} hari</strong> (Jatuh tempo: {{ $shipment->free_time_expired_at?->format('d/m/Y') }}). Segera selesaikan penebusan DO dan pengeluaran barang.</span>
            </div>
        </div>
        <span class="px-2.5 py-1 rounded-lg bg-rose-200/80 dark:bg-rose-900/60 font-semibold text-[10px] uppercase font-mono tracking-wider shrink-0">
            Perlu Perhatian
        </span>
    </div>
    @endif

    <!-- 1. Tahapan Alur Kerja Proyek (4 Milestones Stepper) -->
    @include('core.components.pipeline_stepper')

    <!-- 2. Ringkasan Proyek 3 Kolom Bento -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 text-xs">

        <!-- Card 1: Identitas Kargo, Kapal & Pelayaran -->
        <div class="p-5 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs flex flex-col justify-between">
            <div>
                <div class="font-bold text-slate-900 dark:text-white border-b border-black/[0.06] dark:border-white/[0.06] pb-3 flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <i data-lucide="ship" class="w-4 h-4 text-[#007AFF]"></i>
                        <span>Identitas Kargo & Pengangkutan</span>
                    </span>
                    <span class="px-2 py-0.5 bg-[#007AFF]/10 text-[#007AFF] rounded-md text-[10px] font-bold font-mono">
                        {{ $shipment->bl_number ?? '-' }}
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-3 pt-3 text-[11px]">
                    <div>
                        <span class="text-slate-400 dark:text-slate-500 block text-[10px]">Vendor Pelayaran:</span>
                        <div class="font-semibold text-slate-800 dark:text-slate-200 truncate">{{ $shipment->shippingVendor?->name ?? 'MAERSK LINE INDONESIA' }}</div>
                    </div>
                    <div>
                        <span class="text-slate-400 dark:text-slate-500 block text-[10px]">Sarana Pengangkut:</span>
                        <div class="font-semibold text-slate-800 dark:text-slate-200 truncate">{{ $shipment->vessel_name ?? '-' }} (Voy: {{ $shipment->voyage_number ?? '-' }})</div>
                    </div>
                    <div>
                        <span class="text-slate-400 dark:text-slate-500 block text-[10px]">Pelabuhan Muat (POL):</span>
                        <div class="font-medium text-slate-700 dark:text-slate-300 truncate">{{ $shipment->port_of_loading ?? '-' }}</div>
                    </div>
                    <div>
                        <span class="text-slate-400 dark:text-slate-500 block text-[10px]">Pelabuhan Bongkar (POD):</span>
                        <div class="font-medium text-slate-700 dark:text-slate-300 truncate">{{ $shipment->port_of_discharge ?? '-' }}</div>
                    </div>
                    <div>
                        <span class="text-slate-400 dark:text-slate-500 block text-[10px]">Kontainer:</span>
                        <div class="font-semibold font-mono text-slate-800 dark:text-slate-200">{{ $shipment->nomor_kontainer ?? '1x40HC' }}</div>
                    </div>
                    <div>
                        <span class="text-slate-400 dark:text-slate-500 block text-[10px]">Kemasan & Paket:</span>
                        <div class="font-semibold font-mono text-slate-800 dark:text-slate-200">{{ $shipment->total_kemasan }} {{ $shipment->jenis_kemasan ?? 'CTN' }}</div>
                    </div>
                    <div>
                        <span class="text-slate-400 dark:text-slate-500 block text-[10px]">Bruto / Netto (Kg):</span>
                        <div class="font-semibold font-mono text-slate-800 dark:text-slate-200 tabular-nums">{{ number_format($shipment->total_bruto, 2) }} / {{ number_format($shipment->total_netto, 2) }}</div>
                    </div>
                    <div>
                        <span class="text-slate-400 dark:text-slate-500 block text-[10px]">Nilai CIF Deklarasi:</span>
                        <div class="font-bold font-mono text-emerald-600 dark:text-emerald-400 tabular-nums">{{ $shipment->currency }} {{ number_format($shipment->total_cif, 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-black/[0.06] dark:border-white/[0.06] flex items-center justify-between text-[10px] text-slate-400 dark:text-slate-500">
                <span>Free Time: <strong class="text-slate-700 dark:text-slate-300">{{ $shipment->free_time_days ?? 7 }} Hari</strong></span>
                <span>ETA: <strong class="text-slate-700 dark:text-slate-300">{{ $shipment->eta ? $shipment->eta->format('d/m/Y') : '-' }}</strong></span>
            </div>
        </div>

        <!-- Card 2: Audit Berkas & Kepatuhan Tiga Dokumen -->
        <div class="p-5 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs flex flex-col justify-between">
            <div>
                <div class="font-bold text-slate-900 dark:text-white border-b border-black/[0.06] dark:border-white/[0.06] pb-3 flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <i data-lucide="file-check-2" class="w-4 h-4 text-emerald-600"></i>
                        <span>Verifikasi Tiga Berkas Utama</span>
                    </span>
                    <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400 text-xs tabular-nums">{{ $completionRate }}% Lengkap</span>
                </div>
                <div class="mt-3">
                    <div class="w-full bg-slate-100 dark:bg-white/10 rounded-full h-2 overflow-hidden">
                        <div class="h-2 rounded-full transition-all duration-500 {{ $completionRate >= 100 ? 'bg-emerald-500' : ($completionRate >= 50 ? 'bg-[#007AFF]' : 'bg-amber-500') }}" 
                             style="width: {{ $completionRate }}%"></div>
                    </div>
                </div>
                <div class="mt-3.5 space-y-2 text-[11px] text-slate-500 dark:text-slate-400">
                    <div class="flex items-center justify-between">
                        <span>Commercial Invoice:</span>
                        <span class="font-semibold {{ $shipment->documents->where('jenis_dokumen', 'INVOICE')->where('status', 'VERIFIED')->isNotEmpty() ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}">
                            {{ $shipment->documents->where('jenis_dokumen', 'INVOICE')->where('status', 'VERIFIED')->isNotEmpty() ? '✓ VERIFIED' : 'Belum Diverifikasi' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Packing List:</span>
                        <span class="font-semibold {{ $shipment->documents->where('jenis_dokumen', 'PACKING_LIST')->where('status', 'VERIFIED')->isNotEmpty() ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}">
                            {{ $shipment->documents->where('jenis_dokumen', 'PACKING_LIST')->where('status', 'VERIFIED')->isNotEmpty() ? '✓ VERIFIED' : 'Belum Diverifikasi' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Bill of Lading (B/L):</span>
                        <span class="font-semibold {{ $shipment->documents->where('jenis_dokumen', 'BL_AWB')->where('status', 'VERIFIED')->isNotEmpty() ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}">
                            {{ $shipment->documents->where('jenis_dokumen', 'BL_AWB')->where('status', 'VERIFIED')->isNotEmpty() ? '✓ VERIFIED' : 'Belum Diverifikasi' }}
                        </span>
                    </div>
                    <div class="pt-2 border-t border-black/[0.06] dark:border-white/[0.06] flex items-center justify-between text-[10px]">
                        <span>Kesesuaian Tiga Dokumen:</span>
                        <span class="font-semibold font-mono {{ $threeWay['is_matched'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                            {{ $threeWay['is_matched'] ? '✓ MATCH (Sesuai)' : '⚠️ Ada Selisih' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-black/[0.06] dark:border-white/[0.06]">
                @can('document.verify')
                    @if($completionRate < 100)
                    <form action="{{ route('core.document.verify-all', $shipment->id) }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="w-full py-2 px-3 rounded-xl bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:hover:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 font-semibold text-xs border border-emerald-200/80 dark:border-emerald-800/40 transition-colors flex items-center justify-center gap-1.5">
                            <i data-lucide="check-check" class="w-3.5 h-3.5 text-emerald-600"></i>
                            <span>Setujui Semua Berkas Sekaligus</span>
                        </button>
                    </form>
                    @else
                    <div class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 text-[11px] font-semibold text-center border border-emerald-200/80 dark:border-emerald-800/40 flex items-center justify-center gap-1.5">
                        <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                        <span>Seluruh berkas wajib telah diverifikasi sah</span>
                    </div>
                    @endif
                @endcan
            </div>
        </div>

        <!-- Card 3: Gateway Pabean CEISA 4.0 & Billing MPN G3 -->
        <div class="p-5 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs flex flex-col justify-between">
            <div>
                <div class="font-bold text-slate-900 dark:text-white border-b border-black/[0.06] dark:border-white/[0.06] pb-3 flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <i data-lucide="landmark" class="w-4 h-4 text-purple-600"></i>
                        <span>Gateway Pabean CEISA 4.0</span>
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold font-mono
                        @if($shipment->ceisaDocument?->status === 'SPPB') bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/80
                        @elseif($shipment->ceisaDocument?->status === 'SUBMITTED' || $shipment->ceisaDocument?->status === 'BILLING') bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20
                        @else bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-white/10 @endif">
                        {{ $shipment->ceisaDocument?->status ?? 'DRAFT' }}
                    </span>
                </div>
                <div class="space-y-3 pt-3 text-[11px]">
                    <div>
                        <span class="text-slate-400 dark:text-slate-500 block text-[10px]">Nomor Aju Pabean (26 Digit):</span>
                        <div class="font-mono font-semibold text-slate-800 dark:text-slate-200 text-[11px] break-all bg-black/[0.02] dark:bg-white/[0.04] p-2.5 rounded-xl border border-black/[0.06] dark:border-white/[0.06] mt-1">
                            {{ $shipment->ceisaDocument->nomor_aju ?? 'Belum ada pengajuan pabean dibuat' }}
                        </div>
                    </div>
                    <div>
                        <span class="text-slate-400 dark:text-slate-500 block text-[10px]">Kode Billing MPN G3 (15 Digit):</span>
                        @if($shipment->billing_code || $shipment->ceisaDocument?->kode_billing)
                            <div class="font-mono font-bold text-purple-600 dark:text-purple-400 text-base tracking-wider mt-0.5 tabular-nums">
                                {{ $shipment->billing_code ?? $shipment->ceisaDocument?->kode_billing }}
                            </div>
                            <div class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">
                                Total: <strong class="text-slate-800 dark:text-white tabular-nums">Rp {{ number_format($shipment->ceisaDocument?->total_billing ?? $taxes['total_billing'], 0, ',', '.') }}</strong>
                                &bull; Jatuh Tempo: {{ $shipment->ceisaDocument?->tgl_jatuh_tempo_billing?->format('d/m/Y') ?? '3 Hari Kerja' }}
                            </div>
                        @else
                            <div class="text-slate-400 dark:text-slate-500 text-xs italic mt-0.5">Diterbitkan otomatis saat dokumen disubmit ke CEISA.</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-black/[0.06] dark:border-white/[0.06]">
                @if($shipment->ceisaDocument && $shipment->ceisaDocument->status !== 'DRAFT')
                    <a href="{{ $shipment->ceisaDocument->jenis_dokumen === '20' ? route('bc20.show', $shipment->ceisaDocument->id) : route('bc30.show', $shipment->ceisaDocument->id) }}"
                       class="btn-apple-tinted w-full py-2.5 px-4 font-semibold text-xs flex items-center justify-center gap-2">
                        <span>Buka Draf Resmi BC {{ $shipment->ceisaDocument->jenis_dokumen }}</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                @else
                    @can('document.submit')
                    <button type="button"
                            onclick="document.getElementById('modal-submit-ceisa').classList.remove('hidden')"
                            {{ !$shipment->is_all_mandatory_documents_verified ? 'disabled' : '' }}
                            class="w-full py-2.5 px-4 rounded-xl font-semibold text-xs transition-all flex items-center justify-center gap-2
                                {{ $shipment->is_all_mandatory_documents_verified 
                                    ? 'btn-apple-filled' 
                                    : 'bg-black/5 dark:bg-white/5 text-slate-400 dark:text-slate-600 cursor-not-allowed' }}">
                        <span>Kirim Dokumen ke CEISA 4.0</span>
                        <i data-lucide="send" class="w-3.5 h-3.5"></i>
                    </button>
                    @endcan
                @endif
            </div>
        </div>

    </div>

    <!-- 3. TABS NAVIGASI WORKSPACE UTAMA -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl border border-black/[0.06] dark:border-white/[0.08] shadow-xs overflow-hidden">
        
        <!-- Tab Bar Buttons macOS Style -->
        <div class="border-b border-black/[0.06] dark:border-white/[0.06] bg-black/[0.02] dark:bg-white/[0.02] p-2 flex items-center gap-1.5 overflow-x-auto text-xs font-semibold">
            <button type="button" @click="activeTab = 'docs'"
                    :class="activeTab === 'docs' ? 'bg-white dark:bg-[#2C2C2E] text-[#007AFF] shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                    class="py-2 px-3.5 rounded-xl transition-all whitespace-nowrap flex items-center gap-2 cursor-pointer">
                <i data-lucide="files" class="w-3.5 h-3.5"></i>
                <span>Berkas Lampiran & Lartas</span>
                <span class="px-1.5 py-0.2 rounded-md text-[10px] bg-black/5 dark:bg-white/10 font-mono">{{ $shipment->documents->count() }}</span>
            </button>

            <button type="button" @click="activeTab = 'items'"
                    :class="activeTab === 'items' ? 'bg-white dark:bg-[#2C2C2E] text-[#007AFF] shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                    class="py-2 px-3.5 rounded-xl transition-all whitespace-nowrap flex items-center gap-2 cursor-pointer">
                <i data-lucide="package" class="w-3.5 h-3.5"></i>
                <span>Item Barang & Pos Tarif (HS)</span>
                <span class="px-1.5 py-0.2 rounded-md text-[10px] bg-black/5 dark:bg-white/10 font-mono">{{ $shipment->items->count() }}</span>
            </button>

            <button type="button" @click="activeTab = 'ops'"
                    :class="activeTab === 'ops' ? 'bg-white dark:bg-[#2C2C2E] text-[#007AFF] shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                    class="py-2 px-3.5 rounded-xl transition-all whitespace-nowrap flex items-center gap-2 cursor-pointer">
                <i data-lucide="truck" class="w-3.5 h-3.5"></i>
                <span>DO Pelayaran & Lapangan</span>
            </button>

            <button type="button" @click="activeTab = 'ceisa'"
                    :class="activeTab === 'ceisa' ? 'bg-white dark:bg-[#2C2C2E] text-[#007AFF] shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                    class="py-2 px-3.5 rounded-xl transition-all whitespace-nowrap flex items-center gap-2 cursor-pointer">
                <i data-lucide="landmark" class="w-3.5 h-3.5"></i>
                <span>Draf Pabean & Log CEISA</span>
            </button>
        </div>

        <!-- ============================================== -->
        <!-- TAB 1: BERKAS LAMPIRAN & PERIZINAN LARTAS      -->
        <!-- ============================================== -->
        <div x-show="activeTab === 'docs'" class="p-5 sm:p-6 space-y-6">
            
            <!-- Seksi 1: Checklist Berkas Wajib -->
            <div>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-black/[0.06] dark:border-white/[0.06] pb-3 mb-4">
                    <div>
                        <h3 class="font-bold text-sm text-slate-900 dark:text-white">Checklist Berkas Pengapalan</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-xs mt-0.5">Unggah berkas Invoice, Packing List, dan Bill of Lading asli klien untuk validasi pabean.</p>
                    </div>
                    <span class="text-xs font-mono font-semibold text-slate-500 dark:text-slate-400 tabular-nums">
                        {{ $shipment->documents->where('status', 'VERIFIED')->count() }} / {{ $shipment->documents->count() }} Lolos Verifikasi
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($shipment->documents as $doc)
                        <div class="p-4 rounded-2xl border transition-all flex flex-col justify-between
                            @if($doc->status === 'VERIFIED') bg-emerald-50/40 dark:bg-emerald-950/20 border-emerald-200/80 dark:border-emerald-800/40
                            @elseif($doc->status === 'UPLOADED') bg-[#007AFF]/5 dark:bg-[#007AFF]/10 border-[#007AFF]/20
                            @elseif($doc->status === 'REJECTED') bg-rose-50/40 dark:bg-rose-950/20 border-rose-200/80 dark:border-rose-900/40
                            @else bg-black/[0.02] dark:bg-white/[0.03] border-black/[0.06] dark:border-white/[0.06] @endif">
                            
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-mono font-bold text-xs text-slate-800 dark:text-white">
                                        {{ $doc->jenis_dokumen }}
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold font-mono
                                        @if($doc->status === 'VERIFIED') bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-300 border border-emerald-300/60
                                        @elseif($doc->status === 'UPLOADED') bg-[#007AFF]/15 text-[#007AFF] border border-[#007AFF]/30
                                        @elseif($doc->status === 'REJECTED') bg-rose-100 dark:bg-rose-900/40 text-rose-800 dark:text-rose-300 border border-rose-300/60
                                        @else bg-slate-200/80 dark:bg-white/10 text-slate-700 dark:text-slate-400 @endif">
                                        {{ $doc->status }}
                                    </span>
                                </div>

                                <div class="text-xs text-slate-700 dark:text-slate-300 font-medium mb-1.5 leading-snug">
                                    {{ $doc->title_label }}
                                </div>

                                <div class="text-[11px] text-slate-400 dark:text-slate-500 mb-3 truncate flex items-center gap-1.5">
                                    @if($doc->nama_file)
                                        <i data-lucide="file" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                        <span class="truncate">{{ $doc->nama_file }}</span>
                                        @if($doc->file_size_kb) <span class="tabular-nums">({{ $doc->file_size_kb }} KB)</span> @endif
                                    @else
                                        <span class="italic text-slate-400">Belum ada berkas terlampir</span>
                                    @endif
                                </div>
                            </div>

                            <div class="pt-3 border-t border-black/[0.06] dark:border-white/[0.06] space-y-2">
                                @can('document.create')
                                <form action="{{ route('core.document.upload-document', $shipment->id) }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
                                    @csrf
                                    <input type="hidden" name="document_type" value="{{ $doc->jenis_dokumen }}">
                                    <input type="file" name="document_file" required class="text-[10px] text-slate-500 dark:text-slate-400 w-full file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[10px] file:font-semibold file:bg-black/5 dark:file:bg-white/10 file:text-slate-700 dark:file:text-slate-200 hover:file:bg-black/10">
                                    <button type="submit" class="btn-apple-filled px-2.5 py-1 text-[11px] shrink-0">
                                        Upload
                                    </button>
                                </form>
                                @endcan

                                <div class="flex items-center justify-between pt-1">
                                    @if($doc->file_path)
                                        <a href="{{ route('core.document.download-doc', [$shipment->id, $doc->id]) }}" target="_blank"
                                           class="text-[11px] text-[#007AFF] hover:underline font-semibold flex items-center gap-1">
                                            <span>Lihat Berkas</span>
                                            <i data-lucide="external-link" class="w-3 h-3"></i>
                                        </a>
                                    @else
                                        <span></span>
                                    @endif

                                    @can('document.verify')
                                        @if($doc->status === 'UPLOADED')
                                        <form action="{{ route('core.document.verify-document', [$shipment->id, $doc->id]) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-[10px] transition-colors flex items-center gap-1 shadow-2xs">
                                                <i data-lucide="check" class="w-3 h-3"></i>
                                                <span>Setujui</span>
                                            </button>
                                        </form>
                                        @endif
                                    @endcan
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Seksi 2: Dokumen Perizinan Lartas Tambahan -->
            <div class="pt-4 border-t border-black/[0.06] dark:border-white/[0.06]">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                    <div>
                        <h3 class="font-bold text-sm text-slate-900 dark:text-white">Dokumen Perizinan Khusus & Lartas (Bila Ada)</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-xs mt-0.5">Surat Persetujuan Impor (PI Kemendag), Laporan Surveyor (LS), Sertifikat SNI, atau dokumen karantina.</p>
                    </div>
                    @can('document.create')
                    <button type="button" onclick="document.getElementById('modal-add-item-customs-doc').classList.remove('hidden')"
                            class="btn-apple-filled px-3 py-1.5 text-xs inline-flex items-center gap-1.5">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>Tambah Dokumen Lartas</span>
                    </button>
                    @endcan
                </div>

                @if($shipment->itemCustomsDocuments->isNotEmpty())
                    <div class="border border-black/[0.06] dark:border-white/[0.08] rounded-2xl overflow-hidden">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-black/[0.02] dark:bg-white/[0.02] text-slate-500 dark:text-slate-400 font-semibold uppercase text-[10px] tracking-wider border-b border-black/[0.06] dark:border-white/[0.06]">
                                <tr>
                                    <th class="p-3.5">Kode & Nama Dokumen</th>
                                    <th class="p-3.5">Item Kargo Terkait</th>
                                    <th class="p-3.5">Instansi Penerbit</th>
                                    <th class="p-3.5">No. & Tanggal Dokumen</th>
                                    <th class="p-3.5">Status</th>
                                    <th class="p-3.5 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.04]">
                                @foreach($shipment->itemCustomsDocuments as $customDoc)
                                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                                        <td class="p-3.5 font-medium text-slate-800 dark:text-white">
                                            <span class="font-mono font-bold text-[#007AFF]">[{{ $customDoc->kode_dokumen }}]</span> {{ $customDoc->nama_dokumen }}
                                        </td>
                                        <td class="p-3.5 text-slate-600 dark:text-slate-300">
                                            <span class="font-mono text-[#007AFF] font-semibold">{{ $customDoc->item->kode_hs ?? '-' }}</span> &bull; {{ Str::limit($customDoc->item->uraian_barang ?? 'Seluruh Barang', 35) }}
                                        </td>
                                        <td class="p-3.5 text-slate-500 dark:text-slate-400">
                                            {{ $customDoc->instansi_penerbit ?? '-' }}
                                        </td>
                                        <td class="p-3.5 font-mono text-slate-800 dark:text-slate-200">
                                            {{ $customDoc->nomor_dokumen ?? 'Belum ada nomor' }}
                                            @if($customDoc->tanggal_dokumen)
                                                <div class="text-[10px] text-slate-400 dark:text-slate-500 font-sans tabular-nums">{{ $customDoc->tanggal_dokumen->format('d/m/Y') }}</div>
                                            @endif
                                        </td>
                                        <td class="p-3.5">
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold font-mono
                                                @if($customDoc->status === 'VERIFIED') bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/80
                                                @elseif($customDoc->status === 'UPLOADED') bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20
                                                @else bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200/80 @endif">
                                                {{ $customDoc->status }}
                                            </span>
                                        </td>
                                        <td class="p-3.5 text-right">
                                            <button type="button" 
                                                    onclick="openFulfillDocModal({{ json_encode($customDoc) }}, '{{ addslashes($customDoc->item->uraian_barang ?? '') }}', '{{ $customDoc->item->kode_hs ?? '' }}')"
                                                    class="btn-apple-gray text-[11px] px-2.5 py-1">
                                                Lengkapi
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-6 rounded-2xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/[0.06] dark:border-white/[0.06] text-slate-500 dark:text-slate-400 text-xs text-center">
                        Tidak ada persyaratan izin lartas khusus yang terdeteksi untuk pengapalan ini.
                    </div>
                @endif
            </div>

        </div>

        <!-- ============================================== -->
        <!-- TAB 2: ITEM BARANG & POS TARIF (HS CODE)       -->
        <!-- ============================================== -->
        <div x-show="activeTab === 'items'" class="p-5 sm:p-6 space-y-6">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-black/[0.06] dark:border-white/[0.06] pb-3">
                <div>
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white">Daftar Item Kargo & Pos Tarif (HS Code)</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-xs mt-0.5">Klasifikasi pos tarif 8 digit, tarif Bea Masuk, PPN Impor, dan PPh Pasal 22.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="document.getElementById('modal-ai-ocr-scan').classList.remove('hidden')"
                            class="btn-apple-gray text-xs px-3.5 py-2 inline-flex items-center gap-1.5">
                        <i data-lucide="scan-text" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                        <span>Ekstraksi Packing List</span>
                    </button>
                    @can('document.create')
                    <button type="button" onclick="document.getElementById('modal-add-item').classList.remove('hidden')"
                            class="btn-apple-filled text-xs px-3.5 py-2 inline-flex items-center gap-1.5">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>Tambah Item Manual</span>
                    </button>
                    @endcan
                </div>
            </div>

            <!-- Tabel Item Barang -->
            <div class="border border-black/[0.06] dark:border-white/[0.08] rounded-2xl overflow-hidden">
                <table class="w-full text-left text-xs">
                    <thead class="bg-black/[0.02] dark:bg-white/[0.02] text-slate-500 dark:text-slate-400 font-semibold uppercase text-[10px] tracking-wider border-b border-black/[0.06] dark:border-white/[0.06]">
                        <tr>
                            <th class="p-3.5 w-10">No</th>
                            <th class="p-3.5">Pos Tarif (HS)</th>
                            <th class="p-3.5">Uraian Komoditas</th>
                            <th class="p-3.5 text-right">Kemasan</th>
                            <th class="p-3.5 text-right">Berat (Kg)</th>
                            <th class="p-3.5 text-right">Nilai CIF ({{ $shipment->currency }})</th>
                            <th class="p-3.5 text-center">Tarif Pabean</th>
                            <th class="p-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.04]">
                        @forelse($shipment->items as $idx => $it)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                                <td class="p-3.5 text-slate-400 dark:text-slate-500 font-mono">{{ $idx + 1 }}</td>
                                <td class="p-3.5 font-mono font-bold text-[#007AFF] whitespace-nowrap">
                                    {{ $it->kode_hs }}
                                    @if($it->has_lartas)
                                        <span class="block text-[9px] font-sans font-semibold text-amber-600 dark:text-amber-400">⚠️ Wajib Lartas</span>
                                    @endif
                                </td>
                                <td class="p-3.5 font-medium text-slate-800 dark:text-white">
                                    {{ $it->uraian_barang }}
                                </td>
                                <td class="p-3.5 text-right font-mono text-slate-700 dark:text-slate-300 tabular-nums">
                                    {{ number_format($it->jumlah_kemasan) }} {{ $it->jenis_kemasan }}
                                </td>
                                <td class="p-3.5 text-right font-mono text-slate-700 dark:text-slate-300 tabular-nums">
                                    {{ number_format($it->berat_netto, 2) }}
                                </td>
                                <td class="p-3.5 text-right font-mono font-bold text-slate-900 dark:text-white tabular-nums">
                                    {{ number_format($it->nilai_fob, 2) }}
                                </td>
                                <td class="p-3.5 text-center text-[10px] text-slate-600 dark:text-slate-400 font-mono tabular-nums">
                                    BM: {{ $it->tarif_bm_persen }}% &bull; PPN: {{ $it->tarif_ppn_persen }}% &bull; PPh: {{ $it->tarif_pph_persen }}%
                                </td>
                                <td class="p-3.5 text-right whitespace-nowrap space-x-1">
                                    <button type="button" 
                                            onclick="openEditItemModal({{ json_encode($it) }})"
                                            class="btn-apple-gray text-[11px] px-2.5 py-1">
                                        Koreksi
                                    </button>
                                    @can('document.create')
                                    <form action="{{ route('core.document.items.destroy', [$shipment->id, $it->id]) }}" method="POST" class="inline" onsubmit="return confirm('Hapus item ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-apple-danger text-[11px] px-2.5 py-1">
                                            Hapus
                                        </button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-8 text-center text-slate-400 dark:text-slate-500 text-xs">
                                    Belum ada item barang tercatat. Silakan unggah berkas Packing List atau gunakan tombol Tambah Item Manual.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Ringkasan Perhitungan Pungutan Pabean -->
            <div class="p-5 rounded-2xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08]">
                <div class="font-bold text-slate-900 dark:text-white text-xs mb-3 flex items-center justify-between">
                    <span>Estimasi Pungutan Impor Pabean (Kurs NDBM: Rp {{ number_format($taxes['kurs'], 0, ',', '.') }})</span>
                    <span class="text-slate-500 dark:text-slate-400 font-mono text-[10px]">Perhitungan Otomatis</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <div class="p-3.5 rounded-xl bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
                        <span class="text-slate-400 dark:text-slate-500 text-[10px] block">Nilai CIF Rupiah:</span>
                        <strong class="font-mono text-slate-900 dark:text-white tabular-nums">Rp {{ number_format($taxes['cif_idr'], 0, ',', '.') }}</strong>
                    </div>
                    <div class="p-3.5 rounded-xl bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
                        <span class="text-slate-400 dark:text-slate-500 text-[10px] block">Bea Masuk (BM):</span>
                        <strong class="font-mono text-slate-900 dark:text-white tabular-nums">Rp {{ number_format($taxes['bea_masuk'], 0, ',', '.') }}</strong>
                    </div>
                    <div class="p-3.5 rounded-xl bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
                        <span class="text-slate-400 dark:text-slate-500 text-[10px] block">PPN Impor (11%):</span>
                        <strong class="font-mono text-slate-900 dark:text-white tabular-nums">Rp {{ number_format($taxes['ppn'], 0, ',', '.') }}</strong>
                    </div>
                    <div class="p-3.5 rounded-xl bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
                        <span class="text-slate-400 dark:text-slate-500 text-[10px] block">PPh Pasal 22 (2.5%):</span>
                        <strong class="font-mono text-slate-900 dark:text-white tabular-nums">Rp {{ number_format($taxes['pph'], 0, ',', '.') }}</strong>
                    </div>
                </div>
                <div class="mt-3.5 pt-3.5 border-t border-black/[0.06] dark:border-white/[0.06] flex justify-between items-center">
                    <span class="font-semibold text-slate-700 dark:text-slate-300 text-xs">Total Estimasi Billing MPN G3:</span>
                    <span class="font-mono font-bold text-lg text-emerald-600 dark:text-emerald-400 tabular-nums">Rp {{ number_format($taxes['total_billing'], 0, ',', '.') }}</span>
                </div>
            </div>

        </div>

        <!-- ============================================== -->
        <!-- TAB 3: DO PELAYARAN & LAPANGAN (TRAFFIC)       -->
        <!-- ============================================== -->
        <div x-show="activeTab === 'ops'" class="p-5 sm:p-6 space-y-6">
            
            <!-- Penebusan DO Pelayaran -->
            <div class="p-5 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.06] gap-2 mb-4">
                    <div>
                        <h3 class="font-bold text-sm text-slate-900 dark:text-white">Penebusan Delivery Order (DO) Pelayaran</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-xs mt-0.5">Pengurusan pembayaran biaya pelayaran (THC, Ocean Freight, Cleaning) dan surat rilis DO.</p>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold font-mono 
                        @if($shipment->nomor_do) bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/80
                        @else bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200/80 @endif">
                        {{ $shipment->nomor_do ? 'DO RILIS' : 'PROSES PENEBUSAN' }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs mb-4">
                    <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.06]">
                        <span class="text-slate-400 dark:text-slate-500 text-[10px] block">Pelayaran Vendor:</span>
                        <strong class="text-slate-800 dark:text-white text-xs font-semibold">{{ $shipment->shippingVendor?->name ?? 'MAERSK LINE INDONESIA' }}</strong>
                    </div>
                    <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.06]">
                        <span class="text-slate-400 dark:text-slate-500 text-[10px] block">Nomor B/L:</span>
                        <strong class="font-mono text-slate-800 dark:text-white text-xs">{{ $shipment->bl_number ?? '-' }}</strong>
                    </div>
                    <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.06]">
                        <span class="text-slate-400 dark:text-slate-500 text-[10px] block">Batas Waktu Free Time:</span>
                        <strong class="text-slate-800 dark:text-white text-xs font-semibold tabular-nums">
                            {{ $shipment->free_time_expired_at ? $shipment->free_time_expired_at->format('d/m/Y') : ($shipment->free_time_days . ' Hari setelah kedatangan') }}
                        </strong>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 pt-2">
                    <form action="{{ route('core.do.send-email', $shipment->id) }}" method="POST">
                        @csrf
                        <button type="submit" 
                                onclick="return confirm('Kirim email permohonan invoice DO otomatis ke vendor pelayaran?')"
                                class="btn-apple-filled px-4 py-2 text-xs inline-flex items-center gap-1.5">
                            <i data-lucide="mail" class="w-3.5 h-3.5"></i>
                            <span>Kirim Permohonan Tagihan DO</span>
                        </button>
                    </form>

                    <a href="{{ route('pdf.do-request', $shipment->id) }}" target="_blank"
                       class="btn-apple-gray px-3.5 py-2 text-xs inline-flex items-center gap-1.5">
                        <i data-lucide="printer" class="w-3.5 h-3.5 text-slate-500"></i>
                        <span>Cetak Surat Permohonan DO</span>
                    </a>
                </div>
            </div>

            <!-- Status Lapangan & Pengeluaran Peti Kemas -->
            <div class="p-5 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.06] gap-2 mb-4">
                    <div>
                        <h3 class="font-bold text-sm text-slate-900 dark:text-white">Traffic Pelabuhan & Pengeluaran Barang</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-xs mt-0.5">Status respon penjaluran kepabeanan, SPPB/NPE, gate out kontainer, dan tanda terima POD.</p>
                    </div>
                    @if($shipment->customs_channel)
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold uppercase font-mono
                            @if($shipment->customs_channel === 'HIJAU') bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/80
                            @elseif($shipment->customs_channel === 'KUNING') bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200/80
                            @else bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border border-rose-200/80 @endif">
                            Jalur {{ $shipment->customs_channel }}
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs mb-4">
                    <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.06]">
                        <span class="text-slate-400 dark:text-slate-500 text-[10px] block">Status SPPB / NPE:</span>
                        <strong class="font-mono text-slate-800 dark:text-white">
                            {{ $shipment->ceisaDocument?->nomor_sppb_npe ?? 'Belum terbit' }}
                        </strong>
                    </div>
                    <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.06]">
                        <span class="text-slate-400 dark:text-slate-500 text-[10px] block">Nomor Peti Kemas:</span>
                        <strong class="font-mono text-slate-800 dark:text-white">{{ $shipment->nomor_kontainer ?? '-' }}</strong>
                    </div>
                    <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.06]">
                        <span class="text-slate-400 dark:text-slate-500 text-[10px] block">Status Gate Out:</span>
                        <strong class="text-slate-800 dark:text-white font-medium">
                            {{ in_array($shipment->status, ['GATE_OUT', 'TRAFFIC_GATE_OUT', 'DELIVERED_POD', 'COMPLETED']) ? 'Sudah Keluar Pelabuhan' : 'Menunggu Pengeluaran' }}
                        </strong>
                    </div>
                    <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.06]">
                        <span class="text-slate-400 dark:text-slate-500 text-[10px] block">Surat Jalan (POD):</span>
                        <strong class="text-slate-800 dark:text-white font-medium tabular-nums">
                            {{ $shipment->pod_signed_at ? 'Diterima: ' . $shipment->pod_signed_at->format('d/m/Y') : 'Belum Ditandatangani' }}
                        </strong>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <a href="{{ route('core.traffic.index') }}" 
                       class="btn-apple-filled px-4 py-2 text-xs inline-flex items-center gap-1.5">
                        <span>Buka Cockpit Tim Traffic</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            </div>

        </div>

        <!-- ============================================== -->
        <!-- TAB 4: DRAF PABEAN & LOG GATEWAY CEISA 4.0     -->
        <!-- ============================================== -->
        <div x-show="activeTab === 'ceisa'" class="p-5 sm:p-6 space-y-6">
            
            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.06] pb-3">
                <div>
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white">Draf Dokumen Pabean & Log Gateway CEISA 4.0</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-xs mt-0.5">Detail integrasi Host-to-Host dengan sistem DJBC.</p>
                </div>
                @if($shipment->ceisaDocument)
                    <a href="{{ $shipment->ceisaDocument->jenis_dokumen === '20' ? route('bc20.show', $shipment->ceisaDocument->id) : route('bc30.show', $shipment->ceisaDocument->id) }}"
                       class="btn-apple-tinted px-3.5 py-1.5 text-xs inline-flex items-center gap-1.5">
                        <span>Lihat Data JSON Pabean</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                @endif
            </div>

            @if($shipment->ceisaDocument)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div class="p-4 rounded-2xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.06] space-y-2">
                        <div class="font-bold text-slate-800 dark:text-white">Informasi Pendaftaran</div>
                        <div class="text-[11px] space-y-1.5 text-slate-600 dark:text-slate-400">
                            <div>Nomor Aju: <strong class="font-mono text-slate-900 dark:text-white">{{ $shipment->ceisaDocument->nomor_aju }}</strong></div>
                            <div>Kantor Pabean: <strong class="text-slate-800 dark:text-slate-200">{{ $shipment->ceisaDocument->nama_kantor }}</strong> ({{ $shipment->ceisaDocument->kode_kantor }})</div>
                            <div>Nilai Pabean: <strong class="text-slate-800 dark:text-slate-200 tabular-nums">{{ $shipment->ceisaDocument->kode_valuta }} {{ number_format($shipment->ceisaDocument->total_nilai, 2) }}</strong></div>
                            <div>Status Dokumen: <strong class="text-[#007AFF]">{{ $shipment->ceisaDocument->status }}</strong></div>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.06] space-y-2">
                        <div class="font-bold text-slate-800 dark:text-white">Respon Resmi & Billing</div>
                        <div class="text-[11px] space-y-1.5 text-slate-600 dark:text-slate-400">
                            <div>Kode Billing: <strong class="font-mono text-purple-600 dark:text-purple-400 tabular-nums">{{ $shipment->ceisaDocument->kode_billing ?? 'Belum Terbit' }}</strong></div>
                            <div>Total Billing: <strong class="text-slate-800 dark:text-white tabular-nums">Rp {{ number_format($shipment->ceisaDocument->total_billing ?? 0, 0, ',', '.') }}</strong></div>
                            <div>Jatuh Tempo: <strong class="text-slate-800 dark:text-slate-200 tabular-nums">{{ $shipment->ceisaDocument->tgl_jatuh_tempo_billing?->format('d/m/Y') ?? '-' }}</strong></div>
                            <div>SPPB/NPE: <strong class="text-emerald-600 dark:text-emerald-400">{{ $shipment->ceisaDocument->nomor_sppb_npe ?? '-' }}</strong></div>
                        </div>
                    </div>
                </div>

                <!-- Riwayat Log API -->
                <div class="pt-2">
                    <div class="font-bold text-xs text-slate-800 dark:text-white mb-2">Riwayat Pertukaran Data API:</div>
                    <div class="space-y-2 max-h-56 overflow-y-auto">
                        @forelse($shipment->ceisaDocument->logs as $log)
                            <div class="p-3 rounded-xl border border-black/[0.06] dark:border-white/[0.06] bg-black/[0.02] dark:bg-white/[0.02] text-xs flex items-center justify-between font-mono">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $log->method === 'POST' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300' : 'bg-[#007AFF]/10 text-[#007AFF]' }}">{{ $log->method }}</span>
                                    <span class="text-slate-700 dark:text-slate-300">{{ $log->endpoint }}</span>
                                </div>
                                <div class="flex items-center gap-3 text-slate-400 dark:text-slate-500 text-[10px]">
                                    <span class="px-2 py-0.5 rounded {{ $log->status_code === 200 ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300' : 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300' }} font-bold tabular-nums">{{ $log->status_code }}</span>
                                    <span class="tabular-nums">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="text-slate-400 dark:text-slate-500 text-xs italic">Belum ada riwayat transmisi ke gateway CEISA 4.0.</div>
                        @endforelse
                    </div>
                </div>
            @else
                <div class="p-8 text-center text-slate-400 dark:text-slate-500 text-xs">
                    Dokumen pabean draf belum dibuat. Lengkapi berkas dan item kargo terlebih dahulu, lalu klik tombol <strong>Kirim Dokumen ke CEISA 4.0</strong>.
                </div>
            @endif

        </div>

    </div>

</div>

<!-- ============================================== -->
<!-- MODAL WINDOWS (APPLE HIG DIALOGS)              -->
<!-- ============================================== -->

<!-- Modal 1: Konfirmasi Submit CEISA 4.0 -->
<div id="modal-submit-ceisa" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/40 backdrop-blur-md flex items-center justify-center p-4">
    <div class="app-modal-dialog-md bg-white dark:bg-[#1C1C1E] rounded-3xl border border-black/10 dark:border-white/10 shadow-2xl p-6 text-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.06]">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Pengajuan Dokumen Pabean CEISA 4.0</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Dokumen akan disubmit ke Host-to-Host Gateway DJBC untuk diterbitkan Kode Billing MPN G3.</p>
            </div>
            <button onclick="document.getElementById('modal-submit-ceisa').classList.add('hidden')" class="btn-apple-plain p-1.5 rounded-lg text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="space-y-2.5 bg-black/[0.02] dark:bg-white/[0.03] p-4 rounded-2xl border border-black/[0.06] dark:border-white/[0.06] text-[11px]">
            <div class="flex justify-between">
                <span class="text-slate-500 dark:text-slate-400">Tipe Dokumen:</span>
                <span class="font-semibold text-slate-800 dark:text-white">{{ $shipment->type === 'EXPORT' ? 'BC 3.0 (PEB EKSPOR)' : 'BC 2.0 (PIB IMPOR)' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500 dark:text-slate-400">Nomor B/L:</span>
                <span class="font-mono font-semibold text-slate-800 dark:text-white">{{ $shipment->bl_number }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500 dark:text-slate-400">Total CIF Rupiah:</span>
                <span class="font-mono font-semibold text-slate-800 dark:text-white tabular-nums">Rp {{ number_format($taxes['cif_idr'], 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500 dark:text-slate-400">Estimasi Total Billing:</span>
                <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400 text-sm tabular-nums">Rp {{ number_format($taxes['total_billing'], 0, ',', '.') }}</span>
            </div>
            @if($shipment->itemCustomsDocuments->isNotEmpty() && !$shipment->is_all_lartas_documents_complete)
            <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200/80 text-amber-900 dark:text-amber-300 text-[11px] flex items-start gap-2">
                <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5"></i>
                <div>
                    <strong>Peringatan Dokumen Lartas:</strong> Masih terdapat dokumen izin lartas yang belum dilengkapi.
                </div>
            </div>
            @endif
            <div class="pt-2 border-t border-black/[0.06] dark:border-white/[0.06] text-slate-500 dark:text-slate-400 text-[10px]">
                Setelah submit berhasil, status pengapalan akan beralih ke <strong>BILLING_ISSUED</strong> dan tugas diteruskan ke antrean <strong>Tim DO</strong>.
            </div>
        </div>

        <form action="{{ route('core.document.submit-ceisa', $shipment->id) }}" method="POST">
            @csrf
            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modal-submit-ceisa').classList.add('hidden')" class="btn-apple-gray px-4 py-2 text-xs">
                    Batal
                </button>
                <button type="submit" class="btn-apple-filled px-5 py-2 text-xs">
                    Ya, Submit ke CEISA 4.0
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Tambah Item Kargo Manual -->
<div id="modal-add-item" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/40 backdrop-blur-md flex items-center justify-center p-4">
    <div class="app-modal-dialog-lg bg-white dark:bg-[#1C1C1E] rounded-3xl border border-black/10 dark:border-white/10 shadow-2xl p-6 text-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.06]">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Tambah Item Kargo Manifes Manual</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Tambahkan pos tarif BTKI 8 digit dan rincian kuantitas kargo.</p>
            </div>
            <button onclick="document.getElementById('modal-add-item').classList.add('hidden')" class="btn-apple-plain p-1.5 rounded-lg text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form action="{{ route('core.document.items.store', $shipment->id) }}" method="POST" class="space-y-3.5">
            @csrf
            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Pos Tarif HS Code (BTKI) <span class="text-rose-500">*</span></label>
                <input type="text" name="kode_hs" id="modal_kode_hs" onchange="lookupHsModal(this.value)" required placeholder="Contoh: 8452.29.00 / 8471.30.20" class="w-full px-3.5 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-slate-900 dark:text-white font-mono text-xs focus:ring-2 focus:ring-[#007AFF] focus:outline-none">
                <div id="modal_hs_lartas_badge" class="hidden mt-1.5 p-2 rounded-xl text-[11px]"></div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Uraian Komoditas Barang <span class="text-rose-500">*</span></label>
                <input type="text" name="uraian_barang" required placeholder="Nama spesifikasi komoditas barang lengkap" class="w-full px-3.5 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-[#007AFF] focus:outline-none">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Dasar Hukum & Catatan Pos Tarif</label>
                <input type="text" name="hs_legal_basis" placeholder="BTKI 2022 Pos 8452" class="w-full px-3.5 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-[#007AFF] focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Jumlah Kemasan</label>
                    <input type="number" name="jumlah_kemasan" value="50" min="1" required class="w-full px-3.5 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-slate-900 dark:text-white text-xs tabular-nums focus:ring-2 focus:ring-[#007AFF] focus:outline-none">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Jenis Kemasan</label>
                    <input type="text" name="jenis_kemasan" value="CTN" class="w-full px-3.5 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-slate-900 dark:text-white text-xs uppercase focus:ring-2 focus:ring-[#007AFF] focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Berat Bruto (Kg)</label>
                    <input type="number" step="0.01" name="berat_bruto" value="100.00" required class="w-full px-3 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-slate-900 dark:text-white text-xs tabular-nums focus:ring-2 focus:ring-[#007AFF] focus:outline-none">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Berat Netto (Kg)</label>
                    <input type="number" step="0.01" name="berat_netto" value="95.00" required class="w-full px-3 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-slate-900 dark:text-white text-xs tabular-nums focus:ring-2 focus:ring-[#007AFF] focus:outline-none">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nilai FOB/CIF ({{ $shipment->currency }})</label>
                    <input type="number" step="0.01" name="nilai_fob" value="1000.00" required class="w-full px-3 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-slate-900 dark:text-white text-xs font-mono tabular-nums focus:ring-2 focus:ring-[#007AFF] focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3 bg-black/[0.02] dark:bg-white/[0.03] p-3 rounded-2xl border border-black/[0.06] dark:border-white/[0.06]">
                <div>
                    <label class="block font-medium text-slate-600 dark:text-slate-400 mb-1 text-[11px]">Bea Masuk (%)</label>
                    <input type="number" step="0.1" name="tarif_bm_persen" value="5.0" class="w-full px-2.5 py-1.5 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-slate-900 dark:text-white text-xs tabular-nums">
                </div>
                <div>
                    <label class="block font-medium text-slate-600 dark:text-slate-400 mb-1 text-[11px]">PPN (%)</label>
                    <input type="number" step="0.1" name="tarif_ppn_persen" value="11.0" class="w-full px-2.5 py-1.5 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-slate-900 dark:text-white text-xs tabular-nums">
                </div>
                <div>
                    <label class="block font-medium text-slate-600 dark:text-slate-400 mb-1 text-[11px]">PPh 22 (%)</label>
                    <input type="number" step="0.1" name="tarif_pph_persen" value="2.5" class="w-full px-2.5 py-1.5 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-slate-900 dark:text-white text-xs tabular-nums">
                </div>
            </div>

            <div class="pt-3 border-t border-black/[0.06] dark:border-white/[0.06] flex items-center justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-add-item').classList.add('hidden')" class="btn-apple-gray px-4 py-2 text-xs">
                    Batal
                </button>
                <button type="submit" class="btn-apple-filled px-5 py-2 text-xs">
                    Simpan Item
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 3: Lengkapi / Perbarui Dokumen Tambahan & Izin Lartas -->
<div id="modal-fulfill-item-doc" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/40 backdrop-blur-md flex items-center justify-center p-4">
    <div class="app-modal-dialog-md bg-white dark:bg-[#1C1C1E] rounded-3xl border border-black/10 dark:border-white/10 shadow-2xl p-6 text-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.06]">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Lengkapi Dokumen Perizinan Lartas</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Input nomor dokumen, tanggal terbit, dan unggah berkas izin resmi.</p>
            </div>
            <button type="button" onclick="document.getElementById('modal-fulfill-item-doc').classList.add('hidden')" class="btn-apple-plain p-1.5 rounded-lg text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="form-fulfill-item-doc" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="bg-black/[0.02] dark:bg-white/[0.03] p-3.5 rounded-2xl border border-black/[0.06] dark:border-white/[0.06] space-y-1.5 text-[11px]">
                <div class="flex items-center gap-2">
                    <span id="fulfill_doc_code_badge" class="px-2 py-0.5 rounded font-mono font-bold text-amber-700 bg-amber-100 dark:bg-amber-900/40 text-[10px]">861</span>
                    <strong id="fulfill_doc_name" class="text-slate-800 dark:text-white text-xs font-semibold">Laporan Surveyor (LS Kemendag)</strong>
                </div>
                <div class="text-slate-500 dark:text-slate-400 text-[10px]" id="fulfill_doc_instansi">Kementerian Perdagangan / Surveyor</div>
                <div class="pt-1.5 border-t border-black/[0.06] dark:border-white/[0.06] text-slate-600 dark:text-slate-300 flex justify-between">
                    <span>Pos Tarif: <strong id="fulfill_item_hs" class="font-mono text-[#007AFF]">8504.40.90</strong></span>
                    <span id="fulfill_item_name" class="truncate max-w-[200px] text-right font-medium">Power Inverter</span>
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nomor Dokumen / Surat Izin <span class="text-rose-500">*</span></label>
                <input type="text" name="nomor_dokumen" id="fulfill_nomor_dokumen" required placeholder="Contoh: LS-08912/KMD/X/2026 atau PI-98218-E" class="w-full px-3.5 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-slate-900 dark:text-white font-mono text-xs focus:ring-2 focus:ring-[#007AFF] focus:outline-none">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Dokumen / Surat Izin <span class="text-rose-500">*</span></label>
                <input type="date" name="tanggal_dokumen" id="fulfill_tanggal_dokumen" required class="w-full px-3.5 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-[#007AFF] focus:outline-none">
            </div>

            <div>
                <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Unggah Berkas Fisik (PDF / Gambar) <span class="text-slate-400 font-normal">(Opsional)</span></label>
                <input type="file" name="document_file" class="text-xs text-slate-500 dark:text-slate-400 w-full file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-[11px] file:font-semibold file:bg-black/5 dark:file:bg-white/10 file:text-slate-700 dark:file:text-slate-200 hover:file:bg-black/10">
                <p id="fulfill_current_file" class="text-[10px] text-[#007AFF] mt-1 hidden"></p>
            </div>

            <div>
                <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Catatan Tambahan / Keterangan</label>
                <input type="text" name="catatan" id="fulfill_catatan" placeholder="Masa berlaku s.d 31 Des 2026" class="w-full px-3.5 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-[#007AFF] focus:outline-none">
            </div>

            @can('document.verify')
            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Status Verifikasi Dokumen</label>
                <select name="status" id="fulfill_status" class="w-full px-3 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-slate-900 dark:text-white text-xs font-semibold">
                    <option value="UPLOADED">UPLOADED (Menunggu Verifikasi)</option>
                    <option value="VERIFIED">VERIFIED (Disetujui Sah)</option>
                    <option value="MISSING">MISSING (Belum Lengkap)</option>
                </select>
            </div>
            @endcan

            <div class="pt-3 border-t border-black/[0.06] dark:border-white/[0.06] flex items-center justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-fulfill-item-doc').classList.add('hidden')" class="btn-apple-gray px-4 py-2 text-xs">
                    Batal
                </button>
                <button type="submit" class="btn-apple-filled px-5 py-2 text-xs">
                    Simpan Dokumen
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 4: Edit Item Manifes & Koreksi HS Code -->
<div id="modal-edit-item" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/40 backdrop-blur-md flex items-center justify-center p-4">
    <div class="app-modal-dialog-lg bg-white dark:bg-[#1C1C1E] rounded-3xl border border-black/10 dark:border-white/10 shadow-2xl p-6 text-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.06]">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Koreksi & Sunting Rincian Barang</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Edit uraian komoditas, pos tarif HS Code, dan nilai barang.</p>
            </div>
            <button type="button" onclick="document.getElementById('modal-edit-item').classList.add('hidden')" class="btn-apple-plain p-1.5 rounded-lg text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="form-edit-item" method="POST" class="space-y-3.5">
            @csrf
            @method('PUT')
            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Pos Tarif HS Code (BTKI) <span class="text-rose-500">*</span></label>
                <div class="flex gap-2">
                    <input type="text" name="kode_hs" id="edit_item_kode_hs" onchange="lookupEditHsModal(this.value)" required class="w-full px-3.5 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-slate-900 dark:text-white font-mono text-xs focus:ring-2 focus:ring-[#007AFF] focus:outline-none">
                    <button type="button" onclick="lookupEditHsModal(document.getElementById('edit_item_kode_hs').value)" class="btn-apple-gray px-3.5 py-2 text-xs shrink-0">
                        Cek Tarif & Lartas
                    </button>
                </div>
                <div id="edit_hs_lartas_badge" class="hidden mt-1.5 p-2 rounded-xl text-[11px]"></div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Uraian Komoditas Barang <span class="text-rose-500">*</span></label>
                <input type="text" name="uraian_barang" id="edit_item_uraian_barang" required class="w-full px-3.5 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-[#007AFF] focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Dasar Hukum & Catatan Pos Tarif</label>
                    <input type="text" name="hs_legal_basis" id="edit_item_hs_legal_basis" class="w-full px-3.5 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-slate-900 dark:text-white text-xs">
                </div>
                <div>
                    <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Negara Asal (2-Letter ISO)</label>
                    <input type="text" name="negara_asal" id="edit_item_negara_asal" maxlength="2" class="w-full px-3.5 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-slate-900 dark:text-white font-mono text-xs uppercase">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Jumlah Kemasan</label>
                    <input type="number" name="jumlah_kemasan" id="edit_item_jumlah_kemasan" min="1" required class="w-full px-3.5 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-slate-900 dark:text-white text-xs tabular-nums">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Jenis Kemasan</label>
                    <input type="text" name="jenis_kemasan" id="edit_item_jenis_kemasan" class="w-full px-3.5 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-slate-900 dark:text-white text-xs uppercase">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Berat Bruto (Kg)</label>
                    <input type="number" step="0.01" name="berat_bruto" id="edit_item_berat_bruto" required class="w-full px-3 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-slate-900 dark:text-white text-xs tabular-nums">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Berat Netto (Kg)</label>
                    <input type="number" step="0.01" name="berat_netto" id="edit_item_berat_netto" required class="w-full px-3 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-slate-900 dark:text-white text-xs tabular-nums">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nilai FOB/CIF ({{ $shipment->currency }})</label>
                    <input type="number" step="0.01" name="nilai_fob" id="edit_item_nilai_fob" required class="w-full px-3 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-slate-900 dark:text-white text-xs font-mono tabular-nums">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3 bg-black/[0.02] dark:bg-white/[0.03] p-3 rounded-2xl border border-black/[0.06] dark:border-white/[0.06]">
                <div>
                    <label class="block font-medium text-slate-600 dark:text-slate-400 mb-1 text-[11px]">Bea Masuk (%)</label>
                    <input type="number" step="0.1" name="tarif_bm_persen" id="edit_item_tarif_bm_persen" class="w-full px-2.5 py-1.5 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-slate-900 dark:text-white text-xs tabular-nums">
                </div>
                <div>
                    <label class="block font-medium text-slate-600 dark:text-slate-400 mb-1 text-[11px]">PPN (%)</label>
                    <input type="number" step="0.1" name="tarif_ppn_persen" id="edit_item_tarif_ppn_persen" class="w-full px-2.5 py-1.5 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-slate-900 dark:text-white text-xs tabular-nums">
                </div>
                <div>
                    <label class="block font-medium text-slate-600 dark:text-slate-400 mb-1 text-[11px]">PPh 22 (%)</label>
                    <input type="number" step="0.1" name="tarif_pph_persen" id="edit_item_tarif_pph_persen" class="w-full px-2.5 py-1.5 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-slate-900 dark:text-white text-xs tabular-nums">
                </div>
            </div>

            <div class="pt-3 border-t border-black/[0.06] dark:border-white/[0.06] flex items-center justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-edit-item').classList.add('hidden')" class="btn-apple-gray px-4 py-2 text-xs">
                    Batal
                </button>
                <button type="submit" class="btn-apple-filled px-5 py-2 text-xs">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 5: Ekstraksi Berkas Packing List -->
<div id="modal-ai-ocr-scan" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/40 backdrop-blur-md flex items-center justify-center p-4">
    <div class="app-modal-dialog-xl bg-white dark:bg-[#1C1C1E] rounded-3xl border border-black/10 dark:border-white/10 shadow-2xl p-6 text-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.06]">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Ekstraksi Berkas Packing List</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Pencocokan pos tarif HS Code dan rincian kemasan otomatis dari berkas dokumen.</p>
            </div>
            <button type="button" onclick="document.getElementById('modal-ai-ocr-scan').classList.add('hidden')" class="btn-apple-plain p-1.5 rounded-lg text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form action="{{ route('core.document.detect-hs', $shipment->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div class="p-5 rounded-2xl border-2 border-dashed border-black/10 dark:border-white/10 hover:border-[#007AFF] transition-colors bg-black/[0.02] dark:bg-white/[0.02]">
                <label class="block font-semibold text-slate-800 dark:text-white mb-1">Unggah Berkas Packing List (PDF, Excel XLSX/XLS, Gambar, CSV)</label>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">Sistem mengekstrak tabel barang, jumlah kemasan, bobot bruto/netto, dan nilai kargo.</p>
                <input type="file" name="packing_list_file" id="scanner_file_input" accept=".pdf,.xlsx,.xls,.csv,.txt,.jpg,.jpeg,.png"
                       class="text-xs text-slate-600 dark:text-slate-300 w-full file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#007AFF] file:text-white hover:file:bg-[#0062CC]">
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="font-semibold text-slate-800 dark:text-white">Atau Tempel Teks Tabel Barang:</label>
                    <span class="text-[10px] text-slate-400">Format baris teks tabel</span>
                </div>
                <textarea name="raw_text" id="scanner_raw_text" rows="4" placeholder="Contoh:
1. INDUSTRIAL SEWING MACHINE HEAVY DUTY 50 CTN 2500 KGS {{ $shipment->currency }} 12500 (HS: 8452.29.00)
2. SPARE PARTS AND NEEDLES 100 BOX 5300 KGS {{ $shipment->currency }} 12500 (HS: 8452.90.00)" 
                    class="w-full px-3.5 py-2.5 rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-slate-900 dark:text-white font-mono text-[11px] focus:ring-2 focus:ring-[#007AFF] focus:outline-none"></textarea>
            </div>

            <div class="pt-3 border-t border-black/[0.06] dark:border-white/[0.06] flex items-center justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-ai-ocr-scan').classList.add('hidden')" class="btn-apple-gray px-4 py-2 text-xs">
                    Batal
                </button>
                <button type="submit" class="btn-apple-filled px-5 py-2 text-xs">
                    Ekstrak & Terapkan ke Manifes Proyek
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 6: Tambah Dokumen Khusus / Perizinan Lartas Baru -->
<div id="modal-add-item-customs-doc" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/40 backdrop-blur-md flex items-center justify-center p-4">
    <div class="app-modal-dialog-md bg-white dark:bg-[#1C1C1E] rounded-3xl border border-black/10 dark:border-white/10 shadow-2xl p-6 text-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/[0.06]">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Tambah Dokumen Khusus / Izin Lartas</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Hubungkan surat izin atau perizinan khusus instansi dengan item kargo.</p>
            </div>
            <button type="button" onclick="document.getElementById('modal-add-item-customs-doc').classList.add('hidden')" class="btn-apple-plain p-1.5 rounded-lg text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form action="{{ route('core.document.item-document.store', $shipment->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3.5">
            @csrf

            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Pilih Item Barang Terkait <span class="text-rose-500">*</span></label>
                <select name="shipment_item_id" id="add_doc_item_id" required class="w-full px-3 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-white dark:bg-[#1C1C1E] text-slate-900 dark:text-white text-xs font-semibold focus:ring-2 focus:ring-[#007AFF]">
                    <option value="">-- Pilih Barang Manifes --</option>
                    @foreach($shipment->items as $it)
                        <option value="{{ $it->id }}">
                            [{{ $it->kode_hs }}] {{ Str::limit($it->uraian_barang, 45) }} ({{ number_format($it->jumlah_kemasan) }} {{ $it->jenis_kemasan }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Jenis & Kode Dokumen Pabean <span class="text-rose-500">*</span></label>
                <select id="preset_doc_selector" onchange="applyCustomsDocPreset(this.value)" class="w-full px-3 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-slate-900 dark:text-white text-xs font-medium mb-2">
                    <option value="">-- Pilih Rekomendasi Jenis Dokumen --</option>
                    <option value="861|Laporan Surveyor (LS Kemendag)|Kementerian Perdagangan / Surveyor">861 - Laporan Surveyor (LS Kemendag)</option>
                    <option value="860|Persetujuan Impor (PI Kemendag)|Kementerian Perdagangan">860 - Persetujuan Impor (PI Kemendag)</option>
                    <option value="810|Sertifikat Standar Nasional Indonesia (SNI)|BSN / LSPro">810 - Sertifikat Standar Nasional Indonesia (SNI)</option>
                    <option value="853|Sertifikat Alat Telekomunikasi (SDPPI)|Kementerian Kominfo">853 - Sertifikat Alat Telekomunikasi (SDPPI)</option>
                    <option value="880|Dokumen V-Legal (SVLK)|Kementerian Lingkungan Hidup dan Kehutanan">880 - Dokumen V-Legal Kayu / Kehutanan (KLHK)</option>
                    <option value="854|Sertifikat Karantina Tumbuhan (KT-9)|Badan Karantina Indonesia">854 - Karantina Tumbuhan / Fitosanitari (Barantan)</option>
                    <option value="852|Sertifikat Karantina Hewan (KH-12)|Badan Karantina Indonesia">852 - Karantina Hewan / Veteriner (Barantan)</option>
                    <option value="856|Sertifikat Karantina Ikan (KI-D12)|Badan Karantina Indonesia">856 - Karantina Ikan / Mutu (Barantan)</option>
                    <option value="999|Dokumen Khusus Lainnya (COA/MSDS/Form E)|Instansi Penerbit">999 - Dokumen Khusus Lainnya (COA / MSDS / Form E)</option>
                </select>
            </div>

            <div class="grid grid-cols-3 gap-2.5">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Kode Dokumen <span class="text-rose-500">*</span></label>
                    <input type="text" name="kode_dokumen" id="add_doc_kode" required placeholder="861 / 999" class="w-full px-3 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-slate-900 dark:text-white font-mono text-xs">
                </div>
                <div class="col-span-2">
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Resmi Dokumen <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_dokumen" id="add_doc_nama" required placeholder="Contoh: Laporan Surveyor" class="w-full px-3 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-slate-900 dark:text-white text-xs">
                </div>
            </div>

            <div>
                <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Instansi Penerbit</label>
                <input type="text" name="instansi_penerbit" id="add_doc_instansi" placeholder="Kementerian Perdagangan / Sucofindo" class="w-full px-3 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-slate-900 dark:text-white text-xs">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Nomor Dokumen / Izin</label>
                    <input type="text" name="nomor_dokumen" placeholder="Contoh: LS-90281/KMD/X/2026" class="w-full px-3 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-slate-900 dark:text-white font-mono text-xs">
                </div>
                <div>
                    <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Tanggal Dokumen</label>
                    <input type="date" name="tanggal_dokumen" class="w-full px-3 py-2 rounded-xl border border-black/10 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.04] text-slate-900 dark:text-white text-xs">
                </div>
            </div>

            <div>
                <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Unggah Berkas Fisik (PDF / Gambar) <span class="text-slate-400 font-normal">(Opsional)</span></label>
                <input type="file" name="document_file" accept=".pdf,.png,.jpg,.jpeg,.xlsx,.xls"
                       class="text-xs text-slate-500 dark:text-slate-400 w-full file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-[11px] file:font-semibold file:bg-[#007AFF] file:text-white hover:file:bg-[#0062CC]">
            </div>

            <div class="pt-3 border-t border-black/[0.06] dark:border-white/[0.06] flex items-center justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-add-item-customs-doc').classList.add('hidden')" class="btn-apple-gray px-4 py-2 text-xs">
                    Batal
                </button>
                <button type="submit" class="btn-apple-filled px-5 py-2 text-xs">
                    Simpan Dokumen
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function applyCustomsDocPreset(presetVal) {
        if (!presetVal) return;
        const parts = presetVal.split('|');
        if (parts[0]) document.getElementById('add_doc_kode').value = parts[0];
        if (parts[1]) document.getElementById('add_doc_nama').value = parts[1];
        if (parts[2]) document.getElementById('add_doc_instansi').value = parts[2];
    }

    function openFulfillDocModal(doc, itemName, hsCode) {
        if (!doc) return;
        const modal = document.getElementById('modal-fulfill-item-doc');
        const form = document.getElementById('form-fulfill-item-doc');
        
        form.action = `/core/document/{{ $shipment->id }}/item-document/${doc.id}`;
        
        document.getElementById('fulfill_doc_code_badge').textContent = doc.kode_dokumen || '-';
        document.getElementById('fulfill_doc_name').textContent = doc.nama_dokumen || 'Dokumen Tambahan';
        document.getElementById('fulfill_doc_instansi').textContent = doc.instansi_penerbit || 'Instansi Terkait';
        document.getElementById('fulfill_item_hs').textContent = hsCode || '-';
        document.getElementById('fulfill_item_name').textContent = itemName || '-';

        document.getElementById('fulfill_nomor_dokumen').value = doc.nomor_dokumen || '';
        document.getElementById('fulfill_tanggal_dokumen').value = doc.tanggal_dokumen ? doc.tanggal_dokumen.substring(0, 10) : '';
        document.getElementById('fulfill_catatan').value = doc.catatan || '';

        const statusSelect = document.getElementById('fulfill_status');
        if (statusSelect) {
            statusSelect.value = doc.status || 'UPLOADED';
        }

        const curFile = document.getElementById('fulfill_current_file');
        if (curFile) {
            if (doc.file_name) {
                curFile.textContent = `Berkas tersimpan: ${doc.file_name}`;
                curFile.classList.remove('hidden');
            } else {
                curFile.classList.add('hidden');
            }
        }

        modal.classList.remove('hidden');
        if (window.lucide) { lucide.createIcons(); }
    }

    function openEditItemModal(item) {
        if (!item) return;
        const modal = document.getElementById('modal-edit-item');
        const form = document.getElementById('form-edit-item');

        form.action = `/core/document/{{ $shipment->id }}/items/${item.id}`;

        document.getElementById('edit_item_kode_hs').value = item.kode_hs || '';
        document.getElementById('edit_item_uraian_barang').value = item.uraian_barang || '';
        document.getElementById('edit_item_hs_legal_basis').value = item.hs_legal_basis || '';
        document.getElementById('edit_item_negara_asal').value = item.negara_asal || 'CN';
        document.getElementById('edit_item_jumlah_kemasan').value = item.jumlah_kemasan || 1;
        document.getElementById('edit_item_jenis_kemasan').value = item.jenis_kemasan || 'CTN';
        document.getElementById('edit_item_berat_bruto').value = item.berat_bruto || 0;
        document.getElementById('edit_item_berat_netto').value = item.berat_netto || 0;
        document.getElementById('edit_item_nilai_fob').value = item.nilai_fob || 0;
        document.getElementById('edit_item_tarif_bm_persen').value = item.tarif_bm_persen ?? 5.0;
        document.getElementById('edit_item_tarif_ppn_persen').value = item.tarif_ppn_persen ?? 11.0;
        document.getElementById('edit_item_tarif_pph_persen').value = item.tarif_pph_persen ?? 2.5;

        lookupEditHsModal(item.kode_hs);
        modal.classList.remove('hidden');
        if (window.lucide) { lucide.createIcons(); }
    }

    async function lookupEditHsModal(hsCode) {
        if (!hsCode || hsCode.length < 4) return;
        const badge = document.getElementById('edit_hs_lartas_badge');
        try {
            const res = await fetch(`/ceisa/tarif-hs/${encodeURIComponent(hsCode)}`);
            const data = await res.json();
            if (data && data.success) {
                if (badge) {
                    badge.classList.remove('hidden');
                    if (data.has_lartas) {
                        badge.className = 'mt-1.5 p-2 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 text-amber-800 dark:text-amber-300 text-[11px] font-medium flex items-center gap-1.5';
                        badge.innerHTML = `<span>⚠️ <strong>LARTAS:</strong> ${data.lartas}</span>`;
                    } else {
                        badge.className = 'mt-1.5 p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/80 text-emerald-800 dark:text-emerald-300 text-[11px] font-medium flex items-center gap-1.5';
                        badge.innerHTML = `<span>✓ <strong>Bebas Lartas:</strong> Pos Tarif ${data.code} siap diajukan.</span>`;
                    }
                }
            }
        } catch (e) {
            console.error('Gagal lookup HS edit:', e);
        }
    }

    async function lookupHsModal(hsCode) {
        if (!hsCode || hsCode.length < 4) return;
        const badge = document.getElementById('modal_hs_lartas_badge');
        try {
            const res = await fetch(`/ceisa/tarif-hs/${encodeURIComponent(hsCode)}`);
            const data = await res.json();
            if (data && data.success) {
                const uraianInput = document.querySelector('#modal-add-item input[name="uraian_barang"]');
                const legalInput = document.querySelector('#modal-add-item input[name="hs_legal_basis"]');
                const bmInput = document.querySelector('#modal-add-item input[name="tarif_bm_persen"]');
                const ppnInput = document.querySelector('#modal-add-item input[name="tarif_ppn_persen"]');
                const pphInput = document.querySelector('#modal-add-item input[name="tarif_pph_persen"]');

                if (uraianInput && (!uraianInput.value || uraianInput.value.includes('INDUSTRIAL SEWING MACHINE'))) {
                    uraianInput.value = data.desc || '';
                }
                if (legalInput) {
                    legalInput.value = data.legal || '';
                }
                if (bmInput && data.bm !== undefined) {
                    bmInput.value = data.bm;
                }
                if (ppnInput && data.ppn !== undefined) {
                    ppnInput.value = data.ppn;
                }
                if (pphInput && data.pph !== undefined) {
                    pphInput.value = data.pph;
                }

                if (badge) {
                    badge.classList.remove('hidden');
                    if (data.has_lartas) {
                        badge.className = 'mt-1.5 p-2 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 text-amber-800 dark:text-amber-300 text-[11px] font-medium flex items-center gap-1.5';
                        badge.innerHTML = `<span>⚠️ <strong>LARTAS:</strong> ${data.lartas}</span>`;
                    } else {
                        badge.className = 'mt-1.5 p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/80 text-emerald-800 dark:text-emerald-300 text-[11px] font-medium flex items-center gap-1.5';
                        badge.innerHTML = `<span>✓ <strong>Bebas Lartas:</strong> Pos Tarif ${data.code} siap diajukan.</span>`;
                    }
                }
            }
        } catch (e) {
            console.error('Gagal lookup HS:', e);
        }
    }

    function openPortalModalShow() {
        document.getElementById('modal-portal-share-show').classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    }

    function closePortalModalShow() {
        document.getElementById('modal-portal-share-show').classList.add('hidden');
    }

    function copyPortalUrlShow() {
        var input = document.getElementById('portal-url-input-show');
        if (!input.value) return;
        navigator.clipboard.writeText(input.value).then(function() {
            var btnText = document.getElementById('copy-btn-text-show');
            btnText.innerText = 'Tersalin!';
            setTimeout(function() { btnText.innerText = 'Salin'; }, 2000);
        });
    }
</script>

<!-- Modal Share Portal Klien & Pengaturan Timer (Per Proyek) -->
<div id="modal-portal-share-show" class="hidden fixed inset-0 z-[200] overflow-y-auto bg-black/60 backdrop-blur-md flex items-center justify-center p-4">
    <div class="max-w-lg w-full bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/10 dark:border-white/10 shadow-2xl p-6 space-y-4 animate-in fade-in zoom-in duration-150">
        <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5">
            <div class="flex items-center space-x-2.5">
                <div class="w-9 h-9 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                    <i data-lucide="share-2" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="font-bold text-[15px] text-black dark:text-white">Portal Pelacakan Klien (Per Proyek)</h3>
                    <p class="text-[11px] text-black/50 dark:text-white/50 font-mono">{{ $shipment->project_no }} &bull; {{ $shipment->client->name ?? '-' }}</p>
                </div>
            </div>
            <button type="button" onclick="closePortalModalShow()" class="p-1.5 rounded-lg text-black/40 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Status Timer Box -->
        <div class="p-3.5 rounded-[14px] bg-slate-50 dark:bg-white/[0.04] border border-black/5 dark:border-white/5 text-[12px] space-y-1.5">
            <div class="flex items-center justify-between">
                <span class="text-black/50 dark:text-white/50">Status Akses:</span>
                @if($shipment->isPortalActive())
                    <span class="font-bold px-2 py-0.5 rounded-full text-[10px] font-mono bg-[#34C759]/15 text-[#248A3D]">AKTIF</span>
                @elseif($shipment->portal_status === 'EXPIRED')
                    <span class="font-bold px-2 py-0.5 rounded-full text-[10px] font-mono bg-[#FF3B30]/15 text-[#C41E17]">KEDALUWARSA</span>
                @else
                    <span class="font-bold px-2 py-0.5 rounded-full text-[10px] font-mono bg-black/10 text-black/60">BELUM DIBUAT</span>
                @endif
            </div>
            <div class="flex items-center justify-between">
                <span class="text-black/50 dark:text-white/50">Masa Berlaku Timer:</span>
                <span class="font-semibold text-black dark:text-white font-mono">
                    {{ $shipment->portal_expires_at ? $shipment->portal_expires_at->format('d/m/Y H:i') . ' WIB' : 'Belum diatur' }}
                </span>
            </div>
            <div class="text-[11px] text-black/40 dark:text-white/40 pt-1 border-t border-black/5 dark:border-white/5 leading-relaxed">
                Tautan ini aman & hanya memuat data proyek pengapalan ini saja. Jika timer habis, tautan otomatis terkunci demi keamanan data kepabeanan.
            </div>
        </div>

        <!-- URL Link & Copy Button -->
        <div class="space-y-1.5">
            <label class="block text-[11px] font-semibold text-black/70 dark:text-white/70">Tautan Pelacakan Mandiri Klien</label>
            <div class="flex items-center gap-2">
                <input id="portal-url-input-show" type="text" readonly 
                       value="{{ $shipment->portal_token ? route('portal.track', $shipment->portal_token) : '' }}"
                       placeholder="Belum ada link, silakan klik 'Generate Link Baru'"
                       class="flex-1 h-9 px-3 rounded-[9px] bg-black/[0.03] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[12px] font-mono text-black dark:text-white select-all">
                <button type="button" onclick="copyPortalUrlShow()" class="btn-apple-filled !h-9 !px-3 text-[12px]">
                    <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                    <span id="copy-btn-text-show">Salin</span>
                </button>
            </div>
        </div>

        <!-- Action Quick Share (WhatsApp & Open Tab) -->
        <div class="grid grid-cols-2 gap-2.5 pt-1">
            @php
                $clientPhone = preg_replace('/[^0-9]/', '', $shipment->client->pic_phone ?? ($shipment->client->phone ?? ''));
                if (str_starts_with($clientPhone, '0')) $clientPhone = '62' . substr($clientPhone, 1);
                $shareMsg = rawurlencode("Yth. Bapak/Ibu " . ($shipment->client->name ?? 'Klien') . ",\n\nBerikut tautan pelacakan kargo pengapalan proyek " . $shipment->project_no . " (B/L: " . $shipment->nomor_bl_awb . ").\nAnda dapat memantau status kepabeanan & unduh berkas resmi di sini:\n" . ($shipment->portal_token ? route('portal.track', $shipment->portal_token) : '') . "\n\nTautan aktif hingga " . ($shipment->portal_expires_at ? $shipment->portal_expires_at->format('d/m/Y H:i') : '') . " WIB.\nTerima kasih.\n\nPT Agung Trans Solusindo");
            @endphp
            <a href="https://wa.me/{{ $clientPhone }}?text={{ $shareMsg }}" target="_blank" class="flex items-center justify-center gap-2 h-9 rounded-[10px] bg-[#34C759] hover:bg-[#2EB84F] text-white text-[12px] font-semibold transition-all">
                <i data-lucide="message-circle" class="w-4 h-4"></i>
                <span>Kirim WhatsApp</span>
            </a>
            @if($shipment->portal_token)
            <a href="{{ route('portal.track', $shipment->portal_token) }}" target="_blank" class="flex items-center justify-center gap-2 h-9 rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.1] text-black dark:text-white text-[12px] font-semibold transition-all">
                <i data-lucide="external-link" class="w-4 h-4"></i>
                <span>Buka Pratinjau</span>
            </a>
            @else
            <button type="button" disabled class="flex items-center justify-center gap-2 h-9 rounded-[10px] bg-black/[0.03] text-black/30 text-[12px] font-semibold cursor-not-allowed">
                <i data-lucide="external-link" class="w-4 h-4"></i>
                <span>Buka Pratinjau</span>
            </button>
            @endif
        </div>

        <!-- Form Generate Token Baru & Atur Durasi Timer -->
        <div class="border-t border-black/5 dark:border-white/5 pt-3.5 space-y-2">
            <div class="text-[12px] font-bold text-black dark:text-white flex items-center gap-1.5">
                <i data-lucide="clock" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                <span>Atur Timer Kedaluwarsa & Terbitkan Link Baru</span>
            </div>
            <p class="text-[11px] text-black/50 dark:text-white/50">Pilih durasi masa aktif baru. Tautan lama otomatis kedaluwarsa digantikan tautan ini:</p>
            
            <form method="POST" action="{{ route('core.document.generate-portal', $shipment->id) }}">
                @csrf
                <div class="flex items-center gap-2">
                    <select name="duration_days" class="flex-1 h-9 px-3 rounded-[9px] bg-black/[0.03] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[12px] text-black dark:text-white">
                        <option value="1" {{ ($shipment->portal_duration_days ?? 7) == 1 ? 'selected' : '' }}>24 Jam (1 Hari)</option>
                        <option value="3" {{ ($shipment->portal_duration_days ?? 7) == 3 ? 'selected' : '' }}>3 Hari</option>
                        <option value="7" {{ ($shipment->portal_duration_days ?? 7) == 7 ? 'selected' : '' }}>7 Hari (Standar)</option>
                        <option value="14" {{ ($shipment->portal_duration_days ?? 7) == 14 ? 'selected' : '' }}>14 Hari (2 Minggu)</option>
                        <option value="30" {{ ($shipment->portal_duration_days ?? 7) == 30 ? 'selected' : '' }}>30 Hari (1 Bulan)</option>
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
@endsection
