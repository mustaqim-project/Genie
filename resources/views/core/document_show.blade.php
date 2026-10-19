@extends('layouts.app')

@section('title', 'Detail Proyek Dokumen — ' . ($shipment->shipment_number ?? $shipment->project_no))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-16">

    <!-- Header & Navigation Bar -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 font-medium mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-800 transition-colors">Dashboard</a>
                <span>/</span>
                <a href="{{ route('core.document.index') }}" class="hover:text-slate-800 transition-colors">Tim Document</a>
                <span>/</span>
                <span class="text-sky-700 font-bold font-mono">{{ $shipment->shipment_number ?? $shipment->project_no }}</span>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-black font-mono text-slate-900 tracking-tight">
                    {{ $shipment->shipment_number ?? $shipment->project_no }}
                </h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold font-mono 
                    @if(in_array($shipment->status, ['BILLING_ISSUED', 'DO_RELEASED', 'DELIVERED_POD', 'COMPLETED'])) bg-emerald-100 text-emerald-800 border border-emerald-300
                    @elseif(in_array($shipment->status, ['DOC_READY', 'HS_DETECTED', 'DRAFT_REVIEW'])) bg-indigo-100 text-indigo-800 border border-indigo-300
                    @elseif($shipment->status === 'DO_PROCESSING') bg-amber-100 text-amber-800 border border-amber-300
                    @else bg-sky-100 text-sky-800 border border-sky-300 @endif">
                    {{ $shipment->status }}
                </span>
                <span class="px-2 py-0.5 rounded text-[11px] font-bold {{ $shipment->type === 'EXPORT' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                    {{ $shipment->type === 'EXPORT' ? 'BC 3.0 EKSPOR' : 'BC 2.0 IMPOR' }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Klien: <strong class="text-slate-800">{{ $shipment->client->name ?? '-' }}</strong> &bull; 
                No. B/L: <strong class="text-slate-800 font-mono">{{ $shipment->bl_number ?? '-' }}</strong> &bull; 
                Kapal: <span class="text-slate-700">{{ $shipment->vessel_name ?? '-' }} (Voy: {{ $shipment->voyage_number ?? '-' }})</span>
            </p>
        </div>

        <!-- Quick Cetak & Export Bar -->
        <div class="flex flex-wrap items-center gap-2">
            @if($shipment->type === 'EXPORT')
                <a href="{{ route('pdf.peb', $shipment->id) }}" target="_blank"
                   class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all shadow-sm">
                    <span>📄 Cetak PEB BC 3.0</span>
                </a>
            @else
                <a href="{{ route('pdf.pib', $shipment->id) }}" target="_blank"
                   class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all shadow-sm">
                    <span>📄 Cetak PIB BC 2.0</span>
                </a>
            @endif

            @if($shipment->ceisaDocument?->kode_billing || $shipment->billing_code)
                <a href="{{ route('pdf.billing', $shipment->id) }}" target="_blank"
                   class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 text-indigo-700 text-xs font-bold transition-all shadow-sm">
                    <span>🏛️ Cetak Billing MPN G3</span>
                </a>
            @endif

            <form action="{{ route('core.document.sync-from-docs', $shipment->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" 
                        onclick="return confirm('Pindai ulang seluruh dokumen terlampir dan perbarui data proyek secara otomatis?')"
                        class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-sky-50 border border-sky-200 hover:bg-sky-100 text-sky-800 text-xs font-bold transition-all shadow-sm" 
                        title="Pindai ulang dan sinkronkan metadata proyek & kargo dari dokumen">
                    <span>🔄 Sinkronkan Data dari Dokumen</span>
                </button>
            </form>

            <a href="{{ route('pdf.do-request', $shipment->id) }}" target="_blank"
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-amber-50 border border-amber-200 hover:bg-amber-100 text-amber-800 text-xs font-bold transition-all shadow-sm" title="Surat Permohonan Rilis DO ke Pelayaran">
                <span>🚢 Surat Permohonan DO</span>
            </a>
        </div>
    </div>

    <!-- Alert Demurrage & Free Time Warning if applicable -->
    @if($shipment->is_demurrage_warning)
    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs mb-6 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <span class="text-xl">⚠️</span>
            <div>
                <strong class="font-bold">PERINGATAN FREE TIME DEMURRAGE:</strong>
                <span>Sisa free time pelabuhan tinggal <strong>{{ $shipment->days_left_free_time }} hari</strong> (Jatuh tempo: {{ $shipment->free_time_expired_at?->format('d/m/Y') }}). Segera selesaikan penebusan DO dan pengeluaran barang.</span>
            </div>
        </div>
        <span class="px-2 py-0.5 rounded bg-rose-200 font-bold text-[10px] uppercase font-mono">Urgent Attention</span>
    </div>
    @endif

    <!-- Banner Deteksi Arah Pengapalan & Rekomendasi Deklarasi Bea Cukai (PIB vs PEB) -->
    <div class="mb-6 p-4 rounded-2xl bg-gradient-to-r {{ $shipment->type === 'EXPORT' ? 'from-amber-500/10 via-amber-500/5 to-slate-50 border border-amber-200' : 'from-sky-500/10 via-sky-500/5 to-slate-50 border border-sky-200' }} flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-start space-x-3.5">
            <div class="w-10 h-10 rounded-xl {{ $shipment->type === 'EXPORT' ? 'bg-amber-100 text-amber-700' : 'bg-sky-100 text-sky-700' }} flex items-center justify-center text-xl shrink-0">
                {{ $shipment->type === 'EXPORT' ? '🚢' : '📦' }}
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-extrabold text-sm text-slate-900">
                        {{ $shipment->type === 'EXPORT' ? 'Rekomendasi Dokumen: Pemberitahuan Ekspor Barang (PEB BC 3.0)' : 'Rekomendasi Dokumen: Pemberitahuan Impor Barang (PIB BC 2.0)' }}
                    </span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $shipment->type === 'EXPORT' ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-sky-100 text-sky-800 border border-sky-300' }}">
                        AI Auto-Classification
                    </span>
                    @if($shipment->itemCustomsDocuments->isNotEmpty())
                        @php
                            $missingLartasCount = $shipment->itemCustomsDocuments->where('status', 'MISSING')->count();
                        @endphp
                        @if($missingLartasCount > 0)
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                ⚠️ {{ $missingLartasCount }} Dokumen Izin Lartas Perlu Dilengkapi
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                ✓ Seluruh Izin Lartas Terpenuhi
                            </span>
                        @endif
                    @endif
                </div>
                <p class="text-slate-600 text-xs mt-1">
                    Sistem otomatis mendeteksi alur pengapalan & pos tarif kargo dari berkas packing list. Seluruh data item (HS code, nilai, kemasan, bobot, tarif) dan perizinan lartas dapat disunting manual oleh staf penanganan untuk menghindari galat pabean.
                </p>
            </div>
        </div>
        <div class="flex items-center space-x-2 shrink-0">
            @can('document.create')
            <button type="button" onclick="document.getElementById('modal-ai-ocr-scan').classList.remove('hidden')"
                    class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all shadow-sm">
                <span>🤖 Pindai Ulang AI / OCR</span>
            </button>
            <a href="{{ route('core.document.generate-pib-peb', $shipment->id) }}"
               class="inline-flex items-center space-x-1.5 px-4 py-2 rounded-xl {{ $shipment->type === 'EXPORT' ? 'bg-amber-600 hover:bg-amber-700' : 'bg-sky-600 hover:bg-sky-700' }} text-white text-xs font-bold transition-all shadow-md">
                <span>⚡ Generate Draf {{ $shipment->type === 'EXPORT' ? 'PEB BC 3.0' : 'PIB BC 2.0' }}</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- 3 Overview Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

        <!-- Card 1: Identitas Kargo, Kapal & Pelayaran -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm text-xs flex flex-col justify-between">
            <div>
                <div class="font-bold text-slate-800 border-b border-slate-100 pb-2.5 flex items-center justify-between">
                    <span class="flex items-center space-x-2">
                        <span>🚢</span>
                        <span>Identitas Kargo & Pengangkutan</span>
                    </span>
                    <span class="px-2 py-0.5 bg-sky-50 text-sky-700 rounded text-[10px] font-bold font-mono">
                        {{ $shipment->bl_number ?? '-' }}
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-3 pt-3 text-[11px]">
                    <div>
                        <span class="text-slate-400 block text-[10px]">Vendor Pelayaran:</span>
                        <div class="font-bold text-slate-800 truncate">{{ $shipment->shippingVendor?->name ?? 'MAERSK LINE INDONESIA' }}</div>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px]">Nama Sarana Pengangkut:</span>
                        <div class="font-bold text-slate-800 truncate">{{ $shipment->vessel_name ?? '-' }} (Voy: {{ $shipment->voyage_number ?? '-' }})</div>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px]">Pelabuhan Muat (POL):</span>
                        <div class="font-semibold text-slate-700 truncate">{{ $shipment->port_of_loading ?? '-' }}</div>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px]">Pelabuhan Bongkar (POD):</span>
                        <div class="font-semibold text-slate-700 truncate">{{ $shipment->port_of_discharge ?? '-' }}</div>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px]">Kontainer & Ukuran:</span>
                        <div class="font-bold font-mono text-slate-800">{{ $shipment->nomor_kontainer ?? '1x40HC' }}</div>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px]">Kemasan & Paket:</span>
                        <div class="font-bold font-mono text-slate-800">{{ $shipment->total_kemasan }} {{ $shipment->jenis_kemasan ?? 'CTN' }}</div>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px]">Bruto / Netto (Kg):</span>
                        <div class="font-bold font-mono text-slate-800">{{ number_format($shipment->total_bruto, 2) }} / {{ number_format($shipment->total_netto, 2) }}</div>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px]">Nilai CIF Deklarasi:</span>
                        <div class="font-bold font-mono text-emerald-700">{{ $shipment->currency }} {{ number_format($shipment->total_cif, 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-400">
                <span>Free Time: {{ $shipment->free_time_days ?? 7 }} Hari</span>
                <span>ETA: {{ $shipment->eta ? $shipment->eta->format('d/m/Y') : '-' }}</span>
            </div>
        </div>

        <!-- Card 2: Audit 9 Slot Dokumen Wajib -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm text-xs flex flex-col justify-between">
            <div>
                <div class="font-bold text-slate-800 border-b border-slate-100 pb-2.5 flex items-center justify-between">
                    <span class="flex items-center space-x-2">
                        <span>📑</span>
                        <span>Audit 9 Slot Dokumen Wajib</span>
                    </span>
                    <span class="font-mono font-bold text-emerald-700 text-xs">{{ $completionRate }}% Terverifikasi</span>
                </div>
                <div class="mt-3">
                    <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
                        <div class="h-3 rounded-full transition-all duration-500 {{ $completionRate >= 100 ? 'bg-emerald-500' : ($completionRate >= 50 ? 'bg-sky-500' : 'bg-amber-500') }}" 
                             style="width: {{ $completionRate }}%"></div>
                    </div>
                </div>
                <div class="mt-3 space-y-1.5 text-[11px] text-slate-500">
                    <div class="flex items-center justify-between">
                        <span>Commercial Invoice:</span>
                        <span class="font-bold {{ $shipment->documents->where('jenis_dokumen', 'INVOICE')->where('status', 'VERIFIED')->isNotEmpty() ? 'text-emerald-600' : 'text-slate-400' }}">
                            {{ $shipment->documents->where('jenis_dokumen', 'INVOICE')->where('status', 'VERIFIED')->isNotEmpty() ? '✓ VERIFIED' : 'Belum Diverifikasi' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Packing List:</span>
                        <span class="font-bold {{ $shipment->documents->where('jenis_dokumen', 'PACKING_LIST')->where('status', 'VERIFIED')->isNotEmpty() ? 'text-emerald-600' : 'text-slate-400' }}">
                            {{ $shipment->documents->where('jenis_dokumen', 'PACKING_LIST')->where('status', 'VERIFIED')->isNotEmpty() ? '✓ VERIFIED' : 'Belum Diverifikasi' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Bill of Lading (B/L):</span>
                        <span class="font-bold {{ $shipment->documents->where('jenis_dokumen', 'BL_AWB')->where('status', 'VERIFIED')->isNotEmpty() ? 'text-emerald-600' : 'text-slate-400' }}">
                            {{ $shipment->documents->where('jenis_dokumen', 'BL_AWB')->where('status', 'VERIFIED')->isNotEmpty() ? '✓ VERIFIED' : 'Belum Diverifikasi' }}
                        </span>
                    </div>
                    @if($shipment->itemCustomsDocuments->isNotEmpty())
                    <div class="pt-1.5 border-t border-slate-100 flex items-center justify-between">
                        <span>Izin & Dok. Tambahan:</span>
                        @php
                            $totalLartasDocs = $shipment->itemCustomsDocuments->count();
                            $completedLartasDocs = $shipment->itemCustomsDocuments->filter(fn($d) => $d->isCompleted())->count();
                        @endphp
                        <span class="font-bold {{ $completedLartasDocs === $totalLartasDocs ? 'text-emerald-600' : 'text-amber-600' }}">
                            {{ $completedLartasDocs }}/{{ $totalLartasDocs }} {{ $completedLartasDocs === $totalLartasDocs ? '✓ LENGKAP' : 'Perlu Dilengkapi' }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons for Verification & Submission -->
            <div class="mt-4 pt-3 border-t border-slate-100 space-y-2">
                @can('document.verify')
                    @if($completionRate < 100)
                    <form action="{{ route('core.document.verify-all', $shipment->id) }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="w-full py-2 px-3 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold text-[11px] border border-emerald-200 transition-colors flex items-center justify-center space-x-1.5">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Setujui Semua Berkas Sekaligus (Bulk Verify)</span>
                        </button>
                    </form>
                    @endif
                @endcan

                @if($shipment->ceisaDocument)
                <a href="{{ $shipment->ceisaDocument->jenis_dokumen === '20' ? route('bc20.show', $shipment->ceisaDocument->id) : route('bc30.show', $shipment->ceisaDocument->id) }}"
                   class="w-full py-2.5 px-4 rounded-xl font-bold text-xs transition-all flex items-center justify-center space-x-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>📑 Buka Dokumen BC {{ $shipment->ceisaDocument->jenis_dokumen }} &rarr;</span>
                </a>
                @else
                <a href="{{ route('core.document.generate-pib-peb', $shipment->id) }}"
                   onclick="return confirm('Generate otomatis draft dokumen pabean dari Packing List?')"
                   class="w-full py-2.5 px-4 rounded-xl font-bold text-xs transition-all flex items-center justify-center space-x-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <span>📋 Generate Draft {{ $shipment->type === 'EXPORT' ? 'PEB (BC 3.0)' : 'PIB (BC 2.0)' }} &rarr;</span>
                </a>
                @endif

                @can('document.submit')
                <button type="button"
                        onclick="document.getElementById('modal-submit-ceisa').classList.remove('hidden')"
                        {{ !$shipment->is_all_mandatory_documents_verified ? 'disabled' : '' }}
                        class="w-full py-2.5 px-4 rounded-xl font-bold text-xs transition-all flex items-center justify-center space-x-2
                            {{ $shipment->is_all_mandatory_documents_verified 
                                ? 'bg-gradient-to-r from-sky-600 to-emerald-600 hover:from-sky-700 hover:to-emerald-700 text-white shadow-lg shadow-sky-600/20' 
                                : 'bg-slate-100 text-slate-400 cursor-not-allowed' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    <span>Generate & Submit ke CEISA 4.0 &rarr;</span>
                </button>
                @endcan
            </div>
        </div>

        <!-- Card 3: Status Respon Gateway CEISA 4.0 & Billing MPN G3 -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm text-xs flex flex-col justify-between">
            <div>
                <div class="font-bold text-slate-800 border-b border-slate-100 pb-2.5 flex items-center justify-between">
                    <span class="flex items-center space-x-2">
                        <span>🏛️</span>
                        <span>Gateway Pabean CEISA 4.0</span>
                    </span>
                    <span class="flex items-center space-x-1.5 text-[10px] text-emerald-600 font-bold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>H2H DJBC</span>
                    </span>
                </div>
                <div class="space-y-3 pt-3 text-[11px]">
                    <div>
                        <span class="text-slate-400 block text-[10px]">Nomor Aju Pabean (26 Digit):</span>
                        <div class="font-mono font-bold text-slate-800 text-[11px] break-all bg-slate-50 p-2 rounded-xl border border-slate-100 mt-1">
                            {{ $shipment->ceisaDocument->nomor_aju ?? 'Belum ada pengajuan pabean dibuat' }}
                        </div>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px]">Kode Billing MPN G3 (15 Digit):</span>
                        @if($shipment->billing_code || $shipment->ceisaDocument?->kode_billing)
                            <div class="font-mono font-black text-indigo-700 text-lg tracking-wider mt-0.5">
                                {{ $shipment->billing_code ?? $shipment->ceisaDocument?->kode_billing }}
                            </div>
                            <div class="text-[10px] text-slate-400">
                                Total: <strong class="text-slate-800">Rp {{ number_format($shipment->ceisaDocument?->total_billing ?? $taxes['total_billing'], 0, ',', '.') }}</strong>
                                &bull; Jatuh Tempo: {{ $shipment->ceisaDocument?->tgl_jatuh_tempo_billing?->format('d/m/Y') ?? '3 Hari Kerja' }}
                            </div>
                        @else
                            <div class="text-slate-400 text-xs italic mt-0.5">Belum terbit. Diterbitkan otomatis saat submit pabean.</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="mt-3 pt-3 border-t border-slate-100">
                <div class="text-[10px] text-slate-400 mb-1">Status Alur Kerja Divisi:</div>
                @if(in_array($shipment->status, ['BILLING_ISSUED', 'DO_PROCESSING', 'DO_RELEASED', 'TRAFFIC_SPPB', 'COMPLETED']))
                    <div class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 font-bold text-[11px] flex items-center space-x-2">
                        <span>&check;</span>
                        <span>Tahap Tim Dokumen Selesai &rarr; Diteruskan ke Tim DO</span>
                    </div>
                @else
                    <div class="p-2.5 rounded-xl bg-sky-50 border border-sky-200 text-sky-800 font-semibold text-[11px] flex items-center space-x-2">
                        <span>&bull;</span>
                        <span>Sedang Dikerjakan oleh Tim Document</span>
                    </div>
                @endif
            </div>
        </div>

    </div>

    <!-- Three-Way Matching Audit Matrix & Simulasi Pungutan -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6 text-xs">

        <!-- Three-Way Matching Card -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <div>
                    <h3 class="font-bold text-sm text-slate-900 flex items-center space-x-2">
                        <span>🔍</span>
                        <span>Three-Way Matching Matrix Audit</span>
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Verifikasi silang kepatuhan antara Commercial Invoice, Packing List, dan Bill of Lading (B/L).</p>
                </div>
                <div>
                    @if($threeWay['is_matched'])
                        <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px] flex items-center space-x-1 border border-emerald-300">
                            <span>✓</span>
                            <span>100% MATCH VALID</span>
                        </span>
                    @else
                        <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 font-bold text-[10px] flex items-center space-x-1 border border-amber-300">
                            <span>⚠️</span>
                            <span>DISCREPANCY ALERT</span>
                        </span>
                    @endif
                </div>
            </div>

            <div class="space-y-3">
                <!-- Check 1: Dokumen Utama Tersedia -->
                <div class="p-3 rounded-xl border {{ $threeWay['all_docs_present'] ? 'bg-emerald-50/50 border-emerald-200' : 'bg-slate-50 border-slate-200' }} flex items-center justify-between">
                    <div class="flex items-center space-x-2.5">
                        <span class="{{ $threeWay['all_docs_present'] ? 'text-emerald-600 font-bold' : 'text-slate-400' }}">
                            {{ $threeWay['all_docs_present'] ? '✓' : '○' }}
                        </span>
                        <div>
                            <span class="font-bold text-slate-800">Kelengkapan 3 Dokumen Dasar</span>
                            <div class="text-[10px] text-slate-500">Invoice, Packing List, dan Bill of Lading (B/L) harus terlampir</div>
                        </div>
                    </div>
                    <span class="font-mono text-[11px] font-bold {{ $threeWay['all_docs_present'] ? 'text-emerald-700' : 'text-slate-500' }}">
                        {{ $threeWay['all_docs_present'] ? 'LENGKAP' : 'BELUM LENGKAP' }}
                    </span>
                </div>

                <!-- Check 2: Nilai FOB/CIF Match -->
                <div class="p-3 rounded-xl border {{ $threeWay['value_match'] ? 'bg-emerald-50/50 border-emerald-200' : 'bg-rose-50/50 border-rose-200' }} flex items-center justify-between">
                    <div class="flex items-center space-x-2.5">
                        <span class="{{ $threeWay['value_match'] ? 'text-emerald-600 font-bold' : 'text-rose-600 font-bold' }}">
                            {{ $threeWay['value_match'] ? '✓' : '✕' }}
                        </span>
                        <div>
                            <span class="font-bold text-slate-800">Kesesuaian Nilai Valuta (Invoice vs Item)</span>
                            <div class="text-[10px] text-slate-500">
                                Deklarasi: USD {{ number_format($threeWay['declared_cif'], 2) }} | Total Item: USD {{ number_format($threeWay['items_fob'], 2) }}
                            </div>
                        </div>
                    </div>
                    <span class="font-mono text-[11px] font-bold {{ $threeWay['value_match'] ? 'text-emerald-700' : 'text-rose-700' }}">
                        {{ $threeWay['value_match'] ? 'MATCH' : 'SELISIH' }}
                    </span>
                </div>

                <!-- Check 3: Berat Bruto Match -->
                <div class="p-3 rounded-xl border {{ $threeWay['weight_match'] ? 'bg-emerald-50/50 border-emerald-200' : 'bg-rose-50/50 border-rose-200' }} flex items-center justify-between">
                    <div class="flex items-center space-x-2.5">
                        <span class="{{ $threeWay['weight_match'] ? 'text-emerald-600 font-bold' : 'text-rose-600 font-bold' }}">
                            {{ $threeWay['weight_match'] ? '✓' : '✕' }}
                        </span>
                        <div>
                            <span class="font-bold text-slate-800">Kesesuaian Berat Bruto (Packing List vs B/L)</span>
                            <div class="text-[10px] text-slate-500">
                                B/L: {{ number_format($threeWay['declared_bruto'], 2) }} Kg | Manifes: {{ number_format($threeWay['items_bruto'], 2) }} Kg
                            </div>
                        </div>
                    </div>
                    <span class="font-mono text-[11px] font-bold {{ $threeWay['weight_match'] ? 'text-emerald-700' : 'text-rose-700' }}">
                        {{ $threeWay['weight_match'] ? 'MATCH' : 'SELISIH' }}
                    </span>
                </div>

                <!-- Check 4: Jumlah Kemasan Match -->
                <div class="p-3 rounded-xl border {{ $threeWay['packages_match'] ? 'bg-emerald-50/50 border-emerald-200' : 'bg-rose-50/50 border-rose-200' }} flex items-center justify-between">
                    <div class="flex items-center space-x-2.5">
                        <span class="{{ $threeWay['packages_match'] ? 'text-emerald-600 font-bold' : 'text-rose-600 font-bold' }}">
                            {{ $threeWay['packages_match'] ? '✓' : '✕' }}
                        </span>
                        <div>
                            <span class="font-bold text-slate-800">Kesesuaian Kemasan (Packing List vs B/L)</span>
                            <div class="text-[10px] text-slate-500">
                                Deklarasi: {{ $threeWay['declared_packages'] }} PKG | Manifes: {{ $threeWay['items_packages'] }} PKG
                            </div>
                        </div>
                    </div>
                    <span class="font-mono text-[11px] font-bold {{ $threeWay['packages_match'] ? 'text-emerald-700' : 'text-rose-700' }}">
                        {{ $threeWay['packages_match'] ? 'MATCH' : 'SELISIH' }}
                    </span>
                </div>

                <!-- Check 5: Dokumen Perizinan Lartas (Bila Ada) -->
                @if($shipment->itemCustomsDocuments->isNotEmpty())
                @php
                    $isLartasComplete = $shipment->is_all_lartas_documents_complete;
                    $missingLartasCount = $shipment->itemCustomsDocuments->filter(fn($d) => !$d->isCompleted())->count();
                @endphp
                <div class="p-3 rounded-xl border {{ $isLartasComplete ? 'bg-emerald-50/50 border-emerald-200' : 'bg-amber-50/50 border-amber-200' }} flex items-center justify-between">
                    <div class="flex items-center space-x-2.5">
                        <span class="{{ $isLartasComplete ? 'text-emerald-600 font-bold' : 'text-amber-600 font-bold' }}">
                            {{ $isLartasComplete ? '✓' : '⚠️' }}
                        </span>
                        <div>
                            <span class="font-bold text-slate-800">Kepatuhan Dokumen Perizinan Lartas</span>
                            <div class="text-[10px] text-slate-500">
                                {{ $isLartasComplete ? 'Seluruh dokumen lampiran & perizinan lartas telah terpenuhi' : "Ada {$missingLartasCount} dokumen lartas yang belum dilengkapi nomor / berkasnya" }}
                            </div>
                        </div>
                    </div>
                    <span class="font-mono text-[11px] font-bold {{ $isLartasComplete ? 'text-emerald-700' : 'text-amber-700' }}">
                        {{ $isLartasComplete ? 'LENGKAP' : 'BELUM LENGKAP' }}
                    </span>
                </div>
                @endif
            </div>
        </div>

        <!-- Tax Calculation Breakdown Card -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <h3 class="font-bold text-sm text-slate-900 flex items-center space-x-2">
                        <span>💰</span>
                        <span>Estimasi Pungutan Pabean (BM/PPN/PPh)</span>
                    </h3>
                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 font-mono text-[10px]">
                        Kurs: Rp {{ number_format($taxes['kurs'], 0, ',', '.') }}
                    </span>
                </div>

                <div class="space-y-2.5 text-[11px]">
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Nilai CIF Valuta:</span>
                        <span class="font-mono font-bold text-slate-800">{{ $shipment->currency }} {{ number_format($taxes['cif_valuta'], 2) }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Nilai Pabean (CIF Rupiah):</span>
                        <span class="font-mono font-bold text-slate-900">Rp {{ number_format($taxes['cif_idr'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Bea Masuk (BM):</span>
                        <span class="font-mono font-bold text-slate-800">Rp {{ number_format($taxes['bea_masuk'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">Nilai Impor (Pabean + BM):</span>
                        <span class="font-mono font-bold text-slate-800">Rp {{ number_format($taxes['nilai_impor'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">PPN Impor (11%):</span>
                        <span class="font-mono font-bold text-slate-800">Rp {{ number_format($taxes['ppn'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500">PPh Pasal 22 Impor (2.5%):</span>
                        <span class="font-mono font-bold text-slate-800">Rp {{ number_format($taxes['pph'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-200">
                <div class="flex justify-between items-center bg-slate-900 text-white p-3.5 rounded-xl">
                    <span class="font-bold text-xs uppercase tracking-wider text-slate-300">Estimasi Billing MPN G3:</span>
                    <span class="font-mono font-black text-lg text-emerald-400">
                        Rp {{ number_format($taxes['total_billing'], 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

    </div>

    <!-- 9 Slot Checklist Dokumen Wajib Pabean -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 mb-8 text-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3 mb-4">
            <div>
                <h3 class="font-bold text-sm text-slate-900 flex items-center space-x-2">
                    <span>📑</span>
                    <span>Checklist 9 Slot Dokumen Wajib Pabean Berversi</span>
                </h3>
                <p class="text-slate-500 text-[11px] mt-0.5">Unggah berkas asli klien. Mengunggah berkas Packing List akan memicu deteksi pos tarif HS Code otomatis.</p>
            </div>
            <span class="text-[11px] font-mono font-bold text-slate-500">
                {{ $shipment->documents->where('status', 'VERIFIED')->count() }} / {{ $shipment->documents->count() }} Lolos Verifikasi
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($shipment->documents as $doc)
                <div class="p-3.5 rounded-2xl border transition-all flex flex-col justify-between
                    @if($doc->status === 'VERIFIED') bg-emerald-50/30 border-emerald-200 hover:border-emerald-300
                    @elseif($doc->status === 'UPLOADED') bg-sky-50/30 border-sky-200 hover:border-sky-300
                    @elseif($doc->status === 'REJECTED') bg-rose-50/30 border-rose-200 hover:border-rose-300
                    @else bg-slate-50/50 border-slate-200 hover:border-slate-300 @endif">
                    
                    <div>
                        <!-- Header Slot -->
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-mono font-bold text-xs text-slate-800">
                                {{ $doc->jenis_dokumen }}
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold font-mono
                                @if($doc->status === 'VERIFIED') bg-emerald-100 text-emerald-800 border border-emerald-300
                                @elseif($doc->status === 'UPLOADED') bg-sky-100 text-sky-800 border border-sky-300
                                @elseif($doc->status === 'REJECTED') bg-rose-100 text-rose-800 border border-rose-300
                                @else bg-slate-200 text-slate-600 @endif">
                                {{ $doc->status }}
                            </span>
                        </div>

                        <div class="text-[11px] text-slate-600 font-medium mb-1.5 leading-snug">
                            {{ $doc->title_label }}
                        </div>

                        <!-- Info File -->
                        <div class="text-[10px] text-slate-400 mb-3 truncate">
                            @if($doc->nama_file)
                                <span>📄 {{ $doc->nama_file }}</span>
                                @if($doc->file_size_kb) ({{ $doc->file_size_kb }} KB) @endif
                            @else
                                <span class="italic text-slate-400">Belum ada berkas terlampir</span>
                            @endif
                        </div>
                    </div>

                    <!-- Action Controls -->
                    <div class="pt-2 border-t border-slate-200/60 space-y-2">
                        <!-- Upload Form -->
                        @can('document.create')
                        <form action="{{ route('core.document.upload-document', $shipment->id) }}" method="POST" enctype="multipart/form-data" class="flex items-center space-x-1.5">
                            @csrf
                            <input type="hidden" name="document_type" value="{{ $doc->jenis_dokumen }}">
                            <input type="file" name="document_file" required class="text-[10px] text-slate-500 w-full file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-[10px] file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300">
                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-sky-600 hover:bg-sky-700 text-white font-bold text-[10px] flex-shrink-0">
                                Upload
                            </button>
                        </form>
                        @endcan

                        <!-- Action Bar (Download & Verify) -->
                        <div class="flex items-center justify-between pt-1">
                            @if($doc->nama_file)
                                <a href="{{ route('core.document.download-doc', ['id' => $shipment->id, 'docId' => $doc->id]) }}" 
                                   class="text-[10px] font-bold text-sky-700 hover:underline flex items-center space-x-1">
                                    <span>Unduh File</span>
                                </a>
                            @else
                                <span></span>
                            @endif

                            @can('document.verify')
                                @if($doc->status === 'UPLOADED')
                                    <div class="flex items-center space-x-1">
                                        <form action="{{ route('core.document.verify-document', ['id' => $shipment->id, 'docId' => $doc->id]) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="status" value="VERIFIED">
                                            <button type="submit" class="px-2 py-0.5 rounded bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10px]">
                                                Setujui
                                            </button>
                                        </form>
                                        <form action="{{ route('core.document.verify-document', ['id' => $shipment->id, 'docId' => $doc->id]) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="status" value="REJECTED">
                                            <button type="submit" class="px-2 py-0.5 rounded bg-rose-600 hover:bg-rose-700 text-white font-bold text-[10px]">
                                                Tolak
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            @endcan
                        </div>
                    </div>

                </div>
            @endforeach
        </div>
    </div>

    <!-- Dokumen Tambahan & Perizinan Lartas per Barang -->
    @if($shipment->itemCustomsDocuments->isNotEmpty())
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 mb-8 text-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3 mb-4">
            <div>
                <h3 class="font-bold text-sm text-slate-900 flex items-center space-x-2">
                    <span>🛡️</span>
                    <span>Tracking Dokumen Tambahan & Perizinan Lartas per Barang (CEISA 4.0)</span>
                </h3>
                <p class="text-slate-500 text-[11px] mt-0.5">Ketentuan dokumen pabean tambahan (LS, PI Kemendag, SNI, SDPPI, V-Legal, Fitosanitari, dll) yang disyaratkan atas pos tarif barang.</p>
            </div>
            @php
                $totLartas = $shipment->itemCustomsDocuments->count();
                $verifiedLartas = $shipment->itemCustomsDocuments->where('status', 'VERIFIED')->count();
                $uploadedLartas = $shipment->itemCustomsDocuments->where('status', 'UPLOADED')->count();
                $missingLartas = $shipment->itemCustomsDocuments->where('status', 'MISSING')->count();
            @endphp
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold font-mono {{ $missingLartas === 0 ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-amber-100 text-amber-800 border border-amber-300' }}">
                    {{ $verifiedLartas + $uploadedLartas }}/{{ $totLartas }} Dokumen Terpenuhi
                </span>
            </div>
        </div>

        @if($missingLartas > 0)
        <div class="mb-4 p-3.5 rounded-xl bg-amber-50 border border-amber-200 flex items-start space-x-3 text-amber-900">
            <span class="text-lg">⚠️</span>
            <div class="text-[11px]">
                <strong class="font-bold text-amber-950">Perhatian Kepatuhan Pabean:</strong>
                Terdapat <strong>{{ $missingLartas }} dokumen perizinan / dokumen tambahan</strong> yang belum dilengkapi nomor atau berkasnya. Lengkapi nomor dokumen dan tanggal sebelum mengirimkan PIB/PEB ke sistem CEISA 4.0 DJBC agar tidak terkena Nota Pembetulan (NP) atau Penolakan Sistem.
            </div>
        </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="py-2.5 px-3">Kode Dok.</th>
                        <th class="py-2.5 px-3">Nama Dokumen & Instansi</th>
                        <th class="py-2.5 px-3">Barang Terkait</th>
                        <th class="py-2.5 px-3">Nomor & Tanggal Dokumen</th>
                        <th class="py-2.5 px-3 text-center">Berkas Fisik</th>
                        <th class="py-2.5 px-3 text-center">Status</th>
                        <th class="py-2.5 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($shipment->itemCustomsDocuments as $idx => $cdoc)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3 px-3 font-mono">
                            <span class="px-2 py-0.5 rounded-md bg-slate-900 text-amber-300 font-extrabold text-[11px]">
                                {{ $cdoc->kode_dokumen }}
                            </span>
                        </td>
                        <td class="py-3 px-3 max-w-xs">
                            <div class="font-bold text-slate-800">{{ $cdoc->nama_dokumen }}</div>
                            <div class="text-[10px] text-slate-400 mt-0.5">{{ $cdoc->instansi_penerbit ?? 'Kementerian / Lembaga Terkait' }}</div>
                        </td>
                        <td class="py-3 px-3">
                            <div class="font-mono text-sky-800 font-bold text-[11px]">{{ $cdoc->item->kode_hs ?? '-' }}</div>
                            <div class="text-[10px] text-slate-500 truncate max-w-xs">{{ $cdoc->item->uraian_barang ?? '-' }}</div>
                        </td>
                        <td class="py-3 px-3">
                            @if($cdoc->nomor_dokumen)
                                <div class="font-mono font-bold text-slate-800">{{ $cdoc->nomor_dokumen }}</div>
                                <div class="text-[10px] text-slate-400">Tgl: {{ $cdoc->tanggal_dokumen ? $cdoc->tanggal_dokumen->format('d/m/Y') : '-' }}</div>
                            @else
                                <span class="italic text-slate-400 font-mono text-[10px]">Belum diisi</span>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-center">
                            @if($cdoc->file_url)
                                <a href="{{ $cdoc->file_url }}" target="_blank" class="inline-flex items-center space-x-1 text-sky-600 hover:text-sky-800 font-bold text-[10px] underline">
                                    <span>📄 Unduh</span>
                                </a>
                            @else
                                <span class="text-slate-400 text-[10px]">-</span>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold font-mono border {{ $cdoc->status_badge_class }}">
                                {{ $cdoc->status }}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-right space-x-1">
                            @can('document.create')
                            <button type="button" 
                                    onclick='openFulfillDocModal(@json($cdoc), @json($cdoc->item->uraian_barang ?? ""), @json($cdoc->item->kode_hs ?? ""))'
                                    class="px-2 py-1 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-700 font-bold text-[10px] border border-sky-200">
                                Lengkapi / Edit
                            </button>
                            @endcan

                            @can('document.verify')
                                @if($cdoc->status === 'UPLOADED')
                                <form action="{{ route('core.document.item-document.verify', ['id' => $shipment->id, 'docId' => $cdoc->id]) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="status" value="VERIFIED">
                                    <button type="submit" class="px-2 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10px]">
                                        Setujui
                                    </button>
                                </form>
                                @endif
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Rincian Item Kargo & Pos Tarif HS Code BTKI Engine -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 mb-8 text-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3 mb-4">
            <div>
                <h3 class="font-bold text-sm text-slate-900 flex items-center space-x-2">
                    <span>📦</span>
                    <span>Rincian Barang & Hasil Deteksi Pos Tarif HS Code BTKI</span>
                </h3>
                <p class="text-slate-500 text-[11px] mt-0.5">Item terisi otomatis saat berkas Packing List diupload, atau dapat disesuaikan manual oleh staf.</p>
            </div>
            <div class="flex items-center space-x-2">
                @can('document.create')
                <button type="button" onclick="document.getElementById('modal-ai-ocr-scan').classList.remove('hidden')"
                        class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-[11px] border border-indigo-200 transition-colors">
                    <span>🤖 Pindai Packing List (AI & OCR)</span>
                </button>
                <button type="button" onclick="document.getElementById('modal-add-item').classList.remove('hidden')"
                        class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-[11px] transition-colors">
                    <span>+ Tambah Item Manual</span>
                </button>
                @endcan
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="py-2.5 px-3">No</th>
                        <th class="py-2.5 px-3">Pos Tarif (HS Code)</th>
                        <th class="py-2.5 px-3">Uraian Komoditas & Dasar Hukum</th>
                        <th class="py-2.5 px-3 text-right">Kemasan</th>
                        <th class="py-2.5 px-3 text-right">Bruto/Netto (Kg)</th>
                        <th class="py-2.5 px-3 text-right">Nilai FOB/CIF</th>
                        <th class="py-2.5 px-3 text-center">Tarif (BM/PPN/PPh)</th>
                        <th class="py-2.5 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($shipment->items as $idx => $it)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-3 text-slate-400 font-mono">{{ $idx + 1 }}</td>
                            <td class="py-3 px-3 font-mono">
                                <span class="font-extrabold text-sky-800 text-xs">{{ $it->kode_hs }}</span>
                                <div class="mt-0.5">
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        {{ round(($it->hs_confidence_score ?? 1.0) * 100) }}% Confidence
                                    </span>
                                </div>
                            </td>
                            <td class="py-3 px-3 max-w-xs">
                                <div class="font-bold text-slate-800">{{ $it->uraian_barang }}</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">{{ $it->hs_legal_basis ?? '-' }}</div>
                                @if($it->customsDocuments->isNotEmpty())
                                    <div class="mt-2 flex flex-wrap gap-1">
                                        @foreach($it->customsDocuments as $cdoc)
                                             <span class="inline-flex items-center space-x-1 px-1.5 py-0.5 rounded text-[9px] font-medium border {{ $cdoc->status_badge_class }}">
                                                <span class="font-bold">[{{ $cdoc->kode_dokumen }}]</span>
                                                <span class="truncate max-w-[120px]">{{ $cdoc->nomor_dokumen ?: $cdoc->nama_dokumen }}</span>
                                                <button type="button" 
                                                        onclick='openFulfillDocModal(@json($cdoc), @json($it->uraian_barang), @json($it->kode_hs))'
                                                        class="ml-0.5 text-sky-800 hover:text-sky-950 underline font-bold"
                                                        title="Lengkapi / Edit Dokumen">
                                                    ✏️
                                                </button>
                                            </span>
                                        @endforeach
                                    </div>
                                @elseif($it->has_lartas)
                                    <div class="mt-1 text-[10px] text-amber-600 font-semibold">
                                        ⚠️ Memerlukan Dokumen Lartas Tambahan
                                    </div>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-right font-mono">
                                {{ number_format($it->jumlah_kemasan) }} {{ $it->jenis_kemasan }}
                            </td>
                            <td class="py-3 px-3 text-right font-mono">
                                <div>{{ number_format($it->berat_bruto, 2) }} B</div>
                                <div class="text-[10px] text-slate-400">{{ number_format($it->berat_netto, 2) }} N</div>
                            </td>
                            <td class="py-3 px-3 text-right font-mono font-bold text-slate-900">
                                {{ $shipment->currency }} {{ number_format($it->nilai_fob, 2) }}
                            </td>
                            <td class="py-3 px-3 text-center font-mono">
                                <span class="px-1.5 py-0.5 bg-slate-100 rounded text-[10px]">
                                    BM: {{ $it->tarif_bm_persen }}% | PPN: {{ $it->tarif_ppn_persen }}% | PPh: {{ $it->tarif_pph_persen }}%
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right space-x-1.5 whitespace-nowrap">
                                @can('document.create')
                                <button type="button" 
                                        onclick='openEditItemModal(@json($it))'
                                        class="inline-flex items-center px-2 py-1 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-700 font-bold text-[11px] border border-sky-200 transition-colors"
                                        title="Koreksi / Sunting Pos Tarif & Data Barang">
                                    <span>✏️ Edit</span>
                                </button>
                                <form action="{{ route('core.document.items.destroy', ['id' => $shipment->id, 'itemId' => $it->id]) }}" method="POST" class="inline" onsubmit="return confirm('Hapus item ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 hover:text-rose-800 font-semibold text-[11px] border border-rose-200 transition-colors">
                                        Hapus
                                    </button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400 text-xs">
                                Belum ada item barang terdaftar. Unggah Packing List di slot checklist di atas untuk deteksi otomatis atau gunakan tombol Tambah Item Manual.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal 1: Konfirmasi Submit CEISA 4.0 -->
<div id="modal-submit-ceisa" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 text-xs">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <div>
                <h3 class="text-base font-extrabold text-slate-900">Konfirmasi Pengajuan Dokumen Pabean CEISA 4.0</h3>
                <p class="text-[11px] text-slate-400">Dokumen akan disubmit ke Host-to-Host Gateway DJBC untuk diterbitkan Kode Billing MPN G3.</p>
            </div>
            <button onclick="document.getElementById('modal-submit-ceisa').classList.add('hidden')" class="p-1 rounded-lg text-slate-400 hover:text-slate-600">
                ✕
            </button>
        </div>

        <div class="space-y-3 mb-6 bg-slate-50 p-4 rounded-2xl border border-slate-100 text-[11px]">
            <div class="flex justify-between">
                <span class="text-slate-500">Tipe Dokumen:</span>
                <span class="font-bold text-slate-800">{{ $shipment->type === 'EXPORT' ? 'BC 3.0 (PEB EKSPOR)' : 'BC 2.0 (PIB IMPOR)' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Nomor B/L:</span>
                <span class="font-mono font-bold text-slate-800">{{ $shipment->bl_number }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Total CIF Rupiah:</span>
                <span class="font-mono font-bold text-slate-800">Rp {{ number_format($taxes['cif_idr'], 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Estimasi Total Billing:</span>
                <span class="font-mono font-bold text-emerald-700 text-sm">Rp {{ number_format($taxes['total_billing'], 0, ',', '.') }}</span>
            </div>
            @if($shipment->itemCustomsDocuments->isNotEmpty() && !$shipment->is_all_lartas_documents_complete)
            <div class="p-3 rounded-xl bg-amber-50 border border-amber-300 text-amber-900 text-[11px] flex items-start space-x-2">
                <span>⚠️</span>
                <div>
                    <strong>Peringatan Dokumen Lartas:</strong> Masih terdapat dokumen izin lartas yang belum dilengkapi. Pengajuan ke CEISA tanpa dokumen lartas yang valid berisiko tinggi diterbitkan Nota Pembetulan (NP) atau Penolakan Gateway.
                </div>
            </div>
            @endif
            <div class="pt-2 border-t border-slate-200 text-slate-500 text-[10px]">
                Setelah submit berhasil, status pengapalan akan beralih ke <strong>BILLING_ISSUED</strong> dan tugas diteruskan ke antrean kerja <strong>Tim Delivery Order (DO)</strong>.
            </div>
        </div>

        <form action="{{ route('core.document.submit-ceisa', $shipment->id) }}" method="POST">
            @csrf
            <div class="flex items-center justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-submit-ceisa').classList.add('hidden')" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-sky-600 to-emerald-600 hover:from-sky-700 hover:to-emerald-700 text-white font-bold shadow-md shadow-sky-600/20">
                    Ya, Submit ke CEISA 4.0
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Tambah Item Kargo Manual -->
<div id="modal-add-item" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-xl w-full p-6 text-xs">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <h3 class="text-sm font-extrabold text-slate-900">Tambah Item Kargo Manifes Manual</h3>
            <button onclick="document.getElementById('modal-add-item').classList.add('hidden')" class="p-1 rounded-lg text-slate-400 hover:text-slate-600">
                ✕
            </button>
        </div>

        <form action="{{ route('core.document.items.store', $shipment->id) }}" method="POST" class="space-y-3.5">
            @csrf
            <div>
                <label class="block font-bold text-slate-700 mb-1">Pos Tarif HS Code (BTKI) <span class="text-rose-500">*</span></label>
                <input type="text" name="kode_hs" id="modal_kode_hs" onchange="lookupHsModal(this.value)" required placeholder="Contoh: 8452.29.00 / 8471.30.20" class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono">
                <div id="modal_hs_lartas_badge" class="hidden mt-1.5 p-2 rounded-xl text-[11px]"></div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Uraian Komoditas Barang <span class="text-rose-500">*</span></label>
                <input type="text" name="uraian_barang" required placeholder="INDUSTRIAL SEWING MACHINE HEAVY DUTY COMPLETE SET" class="w-full px-3 py-2 rounded-xl border border-slate-200">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Dasar Hukum & Catatan BTKI</label>
                <input type="text" name="hs_legal_basis" placeholder="BTKI 2022 Pos 8452" class="w-full px-3 py-2 rounded-xl border border-slate-200">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Jumlah Kemasan</label>
                    <input type="number" name="jumlah_kemasan" value="50" min="1" required class="w-full px-3 py-2 rounded-xl border border-slate-200">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Jenis Kemasan</label>
                    <input type="text" name="jenis_kemasan" value="CTN" class="w-full px-3 py-2 rounded-xl border border-slate-200">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Berat Bruto (Kg)</label>
                    <input type="number" step="0.01" name="berat_bruto" value="2800.00" required class="w-full px-3 py-2 rounded-xl border border-slate-200">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Berat Netto (Kg)</label>
                    <input type="number" step="0.01" name="berat_netto" value="2500.00" required class="w-full px-3 py-2 rounded-xl border border-slate-200">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nilai FOB/CIF (USD)</label>
                    <input type="number" step="0.01" name="nilai_fob" value="12500.00" required class="w-full px-3 py-2 rounded-xl border border-slate-200">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3 bg-slate-50 p-3 rounded-2xl border border-slate-200/60">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Bea Masuk (%)</label>
                    <input type="number" step="0.1" name="tarif_bm_persen" value="5.0" class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 bg-white">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">PPN (%)</label>
                    <input type="number" step="0.1" name="tarif_ppn_persen" value="11.0" class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 bg-white">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">PPh 22 (%)</label>
                    <input type="number" step="0.1" name="tarif_pph_persen" value="2.5" class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 bg-white">
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-add-item').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold">
                    Simpan Item
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 3: Lengkapi / Perbarui Dokumen Tambahan & Izin Lartas -->
<div id="modal-fulfill-item-doc" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 text-xs">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <div>
                <h3 class="text-sm font-extrabold text-slate-900">Lengkapi Dokumen Tambahan & Perizinan Lartas</h3>
                <p class="text-[11px] text-slate-400">Input nomor dokumen, tanggal terbit, dan unggah berkas izin resmi.</p>
            </div>
            <button type="button" onclick="document.getElementById('modal-fulfill-item-doc').classList.add('hidden')" class="p-1 rounded-lg text-slate-400 hover:text-slate-600">
                ✕
            </button>
        </div>

        <form id="form-fulfill-item-doc" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <!-- Callout Item & Dokumen Info -->
            <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-200/70 space-y-1.5 text-[11px]">
                <div class="flex items-center space-x-2">
                    <span id="fulfill_doc_code_badge" class="px-2 py-0.5 rounded font-mono font-black text-amber-300 bg-slate-900 text-[10px]">861</span>
                    <strong id="fulfill_doc_name" class="text-slate-800 text-xs">Laporan Surveyor (LS Kemendag)</strong>
                </div>
                <div class="text-slate-500 text-[10px]" id="fulfill_doc_instansi">Kementerian Perdagangan / Surveyor</div>
                <div class="pt-1.5 border-t border-slate-200/60 text-slate-600 flex justify-between">
                    <span>Pos Tarif: <strong id="fulfill_item_hs" class="font-mono text-sky-800">8504.40.90</strong></span>
                    <span id="fulfill_item_name" class="truncate max-w-[200px] text-right font-medium">Power Inverter</span>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Nomor Dokumen / Surat Izin <span class="text-rose-500">*</span></label>
                <input type="text" name="nomor_dokumen" id="fulfill_nomor_dokumen" required placeholder="Contoh: LS-08912/KMD/X/2026 atau PI-98218-E" class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono text-xs">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Tanggal Dokumen / Surat Izin <span class="text-rose-500">*</span></label>
                <input type="date" name="tanggal_dokumen" id="fulfill_tanggal_dokumen" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Unggah Berkas Fisik (PDF / Gambar) <span class="text-slate-400 font-normal">(Opsional)</span></label>
                <input type="file" name="document_file" class="text-xs text-slate-500 w-full file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-[11px] file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                <p id="fulfill_current_file" class="text-[10px] text-sky-600 mt-1 hidden"></p>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Catatan Tambahan / Keterangan</label>
                <input type="text" name="catatan" id="fulfill_catatan" placeholder="Contoh: Berlaku s.d 31 Des 2026 / Sesuai kuota PI" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
            </div>

            @can('document.verify')
            <div>
                <label class="block font-bold text-slate-700 mb-1">Status Verifikasi Dokumen</label>
                <select name="status" id="fulfill_status" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-xs font-semibold">
                    <option value="UPLOADED">UPLOADED (Menunggu Verifikasi Final)</option>
                    <option value="VERIFIED">VERIFIED (Disetujui Sah oleh Supervisor)</option>
                    <option value="MISSING">MISSING (Belum Lengkap)</option>
                </select>
            </div>
            @endcan

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-fulfill-item-doc').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold shadow-md shadow-sky-600/20">
                    Simpan Dokumen
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 4: Edit Item Manifes & Koreksi HS Code (Human-in-the-Loop) -->
<div id="modal-edit-item" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-xl w-full p-6 text-xs">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <div>
                <h3 class="text-sm font-extrabold text-slate-900">Koreksi & Sunting Rincian Barang (Human-in-the-Loop)</h3>
                <p class="text-[11px] text-slate-400">Setiap bidang data hasil AI / OCR dapat diedit fleksibel untuk mencegah galat pabean.</p>
            </div>
            <button type="button" onclick="document.getElementById('modal-edit-item').classList.add('hidden')" class="p-1 rounded-lg text-slate-400 hover:text-slate-600">
                ✕
            </button>
        </div>

        <form id="form-edit-item" method="POST" class="space-y-3.5">
            @csrf
            @method('PUT')
            <div>
                <label class="block font-bold text-slate-700 mb-1">Pos Tarif HS Code (BTKI 2022) <span class="text-rose-500">*</span></label>
                <div class="flex space-x-2">
                    <input type="text" name="kode_hs" id="edit_item_kode_hs" onchange="lookupEditHsModal(this.value)" required class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono text-xs">
                    <button type="button" onclick="lookupEditHsModal(document.getElementById('edit_item_kode_hs').value)" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs shrink-0">
                        Cek Pos & Lartas
                    </button>
                </div>
                <div id="edit_hs_lartas_badge" class="hidden mt-1.5 p-2 rounded-xl text-[11px]"></div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Uraian Komoditas Barang <span class="text-rose-500">*</span></label>
                <input type="text" name="uraian_barang" id="edit_item_uraian_barang" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Dasar Hukum & Catatan BTKI</label>
                    <input type="text" name="hs_legal_basis" id="edit_item_hs_legal_basis" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Negara Asal (2-Letter ISO)</label>
                    <input type="text" name="negara_asal" id="edit_item_negara_asal" maxlength="2" class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono text-xs uppercase">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Jumlah Kemasan</label>
                    <input type="number" name="jumlah_kemasan" id="edit_item_jumlah_kemasan" min="1" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Jenis Kemasan</label>
                    <input type="text" name="jenis_kemasan" id="edit_item_jenis_kemasan" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs uppercase">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Berat Bruto (Kg)</label>
                    <input type="number" step="0.01" name="berat_bruto" id="edit_item_berat_bruto" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Berat Netto (Kg)</label>
                    <input type="number" step="0.01" name="berat_netto" id="edit_item_berat_netto" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nilai FOB/CIF ({{ $shipment->currency }})</label>
                    <input type="number" step="0.01" name="nilai_fob" id="edit_item_nilai_fob" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-mono">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3 bg-slate-50 p-3 rounded-2xl border border-slate-200/60">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Bea Masuk (%)</label>
                    <input type="number" step="0.1" name="tarif_bm_persen" id="edit_item_tarif_bm_persen" class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 bg-white text-xs">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">PPN (%)</label>
                    <input type="number" step="0.1" name="tarif_ppn_persen" id="edit_item_tarif_ppn_persen" class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 bg-white text-xs">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">PPh 22 (%)</label>
                    <input type="number" step="0.1" name="tarif_pph_persen" id="edit_item_tarif_pph_persen" class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 bg-white text-xs">
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-edit-item').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-xs">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold shadow-md shadow-sky-600/20 text-xs">
                    Simpan Perubahan Item
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 5: AI & OCR Packing List Scanner (Mendukung MCP Server & File Upload) -->
<div id="modal-ai-ocr-scan" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-2xl w-full p-6 text-xs">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center text-lg">
                    🤖
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Pemindai Dokumen Cerdas (AI & OCR Packing List)</h3>
                    <p class="text-[11px] text-slate-400">Deteksi otomatis arah pabean (PIB/PEB), pos tarif HS Code, dan daftar izin Lartas.</p>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('modal-ai-ocr-scan').classList.add('hidden')" class="p-1 rounded-lg text-slate-400 hover:text-slate-600">
                ✕
            </button>
        </div>

        <form action="{{ route('core.document.detect-hs', $shipment->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <!-- Upload File Option -->
            <div class="p-4 rounded-2xl border-2 border-dashed border-slate-200 hover:border-indigo-400 transition-colors bg-slate-50/50">
                <label class="block font-bold text-slate-800 mb-1">Unggah Berkas Packing List (PDF, Excel XLSX/XLS, Gambar, CSV, TXT)</label>
                <p class="text-[11px] text-slate-500 mb-3">Sistem mengekstrak tabel barang, kemasan, bobot bruto/netto, dan nilai kargo secara otomatis.</p>
                <input type="file" name="packing_list_file" id="scanner_file_input" accept=".pdf,.xlsx,.xls,.csv,.txt,.jpg,.jpeg,.png"
                       class="text-xs text-slate-600 w-full file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700">
            </div>

            <!-- Paste Raw Text / OCR String Option -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="font-bold text-slate-800">Atau Tempel Teks Mentah / Hasil OCR / Input AI Agent:</label>
                    <span class="text-[10px] text-slate-400">Format teks bebas atau baris tabel</span>
                </div>
                <textarea name="raw_text" id="scanner_raw_text" rows="4" placeholder="Contoh:
1. INDUSTRIAL SEWING MACHINE HEAVY DUTY 50 CTN 2500 KGS USD 12500 (HS: 8452.29.00)
2. SPARE PARTS AND NEEDLES 100 BOX 5300 KGS USD 12500 (HS: 8452.90.00)" 
                    class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono text-[11px]"></textarea>
            </div>

            <!-- Live AI Preview Box -->
            <div id="scanner_preview_box" class="hidden p-4 rounded-2xl bg-slate-900 text-white space-y-2 text-[11px]">
                <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                    <span class="font-bold text-emerald-400 flex items-center space-x-1.5">
                        <span>✓</span>
                        <span id="preview_decl_label">Terdeteksi: PIB (BC 2.0 IMPOR)</span>
                    </span>
                    <span class="font-mono text-[10px] text-slate-400" id="preview_items_count">0 Barang Ditemukan</span>
                </div>
                <div id="preview_items_list" class="space-y-1.5 max-h-36 overflow-y-auto pt-1 font-mono text-[10px]"></div>
                <div id="preview_lartas_callout" class="p-2 rounded-lg bg-amber-500/20 text-amber-200 text-[10px] hidden"></div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                <button type="button" onclick="testLiveOcrScan()" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs flex items-center space-x-1.5">
                    <span>⚡ Uji Pratinjau Deteksi AI</span>
                </button>
                <div class="flex items-center space-x-2">
                    <button type="button" onclick="document.getElementById('modal-ai-ocr-scan').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-xs">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20">
                        Terapkan ke Manifes Proyek
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
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
                        badge.className = 'mt-1.5 p-2 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-[11px] font-medium flex items-center space-x-1.5';
                        badge.innerHTML = `<span>⚠️ <strong>LARTAS TERDETEKSI:</strong> ${data.lartas}</span>`;
                    } else {
                        badge.className = 'mt-1.5 p-2 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-[11px] font-medium flex items-center space-x-1.5';
                        badge.innerHTML = `<span>✓ <strong>Bebas Lartas:</strong> Pos Tarif ${data.code} siap impor/ekspor.</span>`;
                    }
                }
            }
        } catch (e) {
            console.error('Gagal lookup HS edit:', e);
        }
    }

    async function testLiveOcrScan() {
        const rawText = document.getElementById('scanner_raw_text').value;
        const fileInput = document.getElementById('scanner_file_input');
        const previewBox = document.getElementById('scanner_preview_box');
        const previewDecl = document.getElementById('preview_decl_label');
        const previewCount = document.getElementById('preview_items_count');
        const previewList = document.getElementById('preview_items_list');
        const previewLartas = document.getElementById('preview_lartas_callout');

        previewBox.classList.remove('hidden');
        previewList.innerHTML = '<div class="text-slate-400 animate-pulse">Sedang memindai dan mengklasifikasikan pos tarif BTKI & aturan Lartas...</div>';

        const formData = new FormData();
        if (fileInput.files.length > 0) {
            formData.append('file', fileInput.files[0]);
        }
        if (rawText) {
            formData.append('raw_text', rawText);
        }

        try {
            const res = await fetch('/api/mcp/parse-packing-list', {
                method: 'POST',
                body: formData,
            });
            const data = await res.json();

            if (data && data.success) {
                previewDecl.textContent = `Terdeteksi: ${data.recommendation} (${data.recommendation_label})`;
                previewCount.textContent = `${data.total_items} Barang & ${data.missing_documents_count || 0} Izin Terdeteksi`;

                let itemsHtml = '';
                (data.items || []).forEach((it, i) => {
                    const lartasIcon = it.has_lartas ? '⚠️ [LARTAS]' : '✓ [NON-LARTAS]';
                    const lartasColor = it.has_lartas ? 'text-amber-300' : 'text-emerald-300';
                    itemsHtml += `<div class="p-1.5 rounded bg-white/5 border border-white/10 flex justify-between items-center">
                        <span class="truncate max-w-[280px]">${i+1}. ${it.uraian_barang}</span>
                        <span class="font-bold text-sky-300">${it.kode_hs}</span>
                        <span class="${lartasColor} text-[9px] font-bold">${lartasIcon}</span>
                    </div>`;
                });
                previewList.innerHTML = itemsHtml || '<div class="text-slate-400">Tidak ada barang terdeteksi.</div>';

                if (data.has_lartas) {
                    previewLartas.classList.remove('hidden');
                    previewLartas.innerHTML = `<strong>Peringatan Dokumen Lartas:</strong> Ditemukan barang yang wajib menyertakan dokumen tambahan. Pastikan izin perizinan diunggah sebelum submit ke CEISA 4.0.`;
                } else {
                    previewLartas.classList.add('hidden');
                }
            } else {
                previewList.innerHTML = `<div class="text-rose-400">Gagal memproses dokumen: ${data.message || 'Error parsing'}</div>`;
            }
        } catch (e) {
            previewList.innerHTML = `<div class="text-rose-400">Terjadi kesalahan koneksi server: ${e.message}</div>`;
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
                        badge.className = 'mt-1.5 p-2 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-[11px] font-medium flex items-center space-x-1.5';
                        badge.innerHTML = `<span>⚠️ <strong>LARTAS TERDETEKSI:</strong> ${data.lartas}</span>`;
                    } else {
                        badge.className = 'mt-1.5 p-2 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-[11px] font-medium flex items-center space-x-1.5';
                        badge.innerHTML = `<span>✓ <strong>Bebas Lartas:</strong> Pos Tarif ${data.code} siap impor/ekspor.</span>`;
                    }
                }
            }
        } catch (e) {
            console.error('Gagal lookup HS:', e);
        }
    }
</script>

@endsection

