@extends('layouts.app')

@section('title', 'Detail Proyek Pengapalan — ' . ($shipment->shipment_number ?? $shipment->project_no))

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 pt-6 pb-16" x-data="{ activeTab: 'docs' }">

    <!-- 0. Header & Navigation Bar -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 font-medium mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-800 transition-colors">Dashboard</a>
                <span>/</span>
                <a href="{{ route('core.document.index') }}" class="hover:text-slate-800 transition-colors">Proyek Pengapalan</a>
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
                Sarana Pengangkut: <span class="text-slate-700">{{ $shipment->vessel_name ?? '-' }} (Voy: {{ $shipment->voyage_number ?? '-' }})</span>
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-2">
            @if($shipment->type === 'EXPORT')
                <a href="{{ route('pdf.peb', $shipment->id) }}" target="_blank"
                   class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all shadow-xs">
                    <span>📄 Cetak PEB BC 3.0</span>
                </a>
            @else
                <a href="{{ route('pdf.pib', $shipment->id) }}" target="_blank"
                   class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all shadow-xs">
                    <span>📄 Cetak PIB BC 2.0</span>
                </a>
            @endif

            @if($shipment->ceisaDocument?->kode_billing || $shipment->billing_code)
                <a href="{{ route('pdf.billing', $shipment->id) }}" target="_blank"
                   class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 text-indigo-700 text-xs font-bold transition-all shadow-xs">
                    <span>🏛️ Cetak Billing MPN G3</span>
                </a>
            @endif

            <form action="{{ route('core.document.sync-from-docs', $shipment->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" 
                        onclick="return confirm('Pindai ulang seluruh berkas terlampir dan perbarui data proyek secara otomatis?')"
                        class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-sky-50 border border-sky-200 hover:bg-sky-100 text-sky-800 text-xs font-bold transition-all shadow-xs" 
                        title="Pindai ulang dan sinkronkan metadata proyek & kargo dari dokumen">
                    <span>🔄 Sinkronkan Data Dokumen</span>
                </button>
            </form>

            <a href="{{ route('pdf.do-request', $shipment->id) }}" target="_blank"
               class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-amber-50 border border-amber-200 hover:bg-amber-100 text-amber-800 text-xs font-bold transition-all shadow-xs" 
               title="Surat Permohonan Rilis DO ke Pelayaran">
                <span>🚢 Surat Permohonan DO</span>
            </a>
        </div>
    </div>

    <!-- Alert Demurrage & Free Time Warning -->
    @if($shipment->is_demurrage_warning)
    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs mb-6 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <span class="text-xl">⚠️</span>
            <div>
                <strong class="font-bold">PERINGATAN FREE TIME DEMURRAGE:</strong>
                <span>Sisa free time pelabuhan tinggal <strong>{{ $shipment->days_left_free_time }} hari</strong> (Jatuh tempo: {{ $shipment->free_time_expired_at?->format('d/m/Y') }}). Segera selesaikan penebusan DO dan pengeluaran barang.</span>
            </div>
        </div>
        <span class="px-2.5 py-1 rounded bg-rose-200 font-bold text-[10px] uppercase font-mono">Perlu Perhatian</span>
    </div>
    @endif

    <!-- 1. Tahapan Alur Kerja Proyek (4 Milestones) -->
    @include('core.components.pipeline_stepper')

    <!-- 2. Ringkasan Proyek 3 Kolom Bento -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6 text-xs">

        <!-- Card 1: Identitas Kargo, Kapal & Pelayaran -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div>
                <div class="font-bold text-slate-900 border-b border-slate-100 pb-2.5 flex items-center justify-between">
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
                        <span class="text-slate-400 block text-[10px]">Sarana Pengangkut:</span>
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
                        <span class="text-slate-400 block text-[10px]">Kontainer:</span>
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
                <span>Free Time: <strong>{{ $shipment->free_time_days ?? 7 }} Hari</strong></span>
                <span>ETA: <strong>{{ $shipment->eta ? $shipment->eta->format('d/m/Y') : '-' }}</strong></span>
            </div>
        </div>

        <!-- Card 2: Audit Berkas & Kepatuhan Tiga Dokumen -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div>
                <div class="font-bold text-slate-900 border-b border-slate-100 pb-2.5 flex items-center justify-between">
                    <span class="flex items-center space-x-2">
                        <span>📑</span>
                        <span>Verifikasi Tiga Berkas Utama</span>
                    </span>
                    <span class="font-mono font-bold text-emerald-700 text-xs">{{ $completionRate }}% Lengkap</span>
                </div>
                <div class="mt-3">
                    <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                        <div class="h-2.5 rounded-full transition-all duration-500 {{ $completionRate >= 100 ? 'bg-emerald-500' : ($completionRate >= 50 ? 'bg-sky-500' : 'bg-amber-500') }}" 
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
                    <div class="pt-1.5 border-t border-slate-100 flex items-center justify-between text-[10px]">
                        <span>Kesesuaian Tiga Dokumen:</span>
                        <span class="font-bold font-mono {{ $threeWay['is_matched'] ? 'text-emerald-600' : 'text-amber-600' }}">
                            {{ $threeWay['is_matched'] ? '✓ MATCH (Sesuai)' : '⚠️ Ada Selisih' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100">
                @can('document.verify')
                    @if($completionRate < 100)
                    <form action="{{ route('core.document.verify-all', $shipment->id) }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="w-full py-2 px-3 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold text-[11px] border border-emerald-200 transition-colors flex items-center justify-center space-x-1.5">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Setujui Semua Berkas Sekaligus</span>
                        </button>
                    </form>
                    @else
                    <div class="p-2 rounded-xl bg-emerald-50 text-emerald-800 text-[11px] font-semibold text-center border border-emerald-200">
                        ✓ Seluruh berkas wajib telah diverifikasi sah
                    </div>
                    @endif
                @endcan
            </div>
        </div>

        <!-- Card 3: Gateway Pabean CEISA 4.0 & Billing MPN G3 -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div>
                <div class="font-bold text-slate-900 border-b border-slate-100 pb-2.5 flex items-center justify-between">
                    <span class="flex items-center space-x-2">
                        <span>🏛️</span>
                        <span>Gateway Pabean CEISA 4.0</span>
                    </span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono
                        @if($shipment->ceisaDocument?->status === 'SPPB') bg-emerald-100 text-emerald-800
                        @elseif($shipment->ceisaDocument?->status === 'SUBMITTED' || $shipment->ceisaDocument?->status === 'BILLING') bg-sky-100 text-sky-800
                        @else bg-slate-100 text-slate-600 @endif">
                        {{ $shipment->ceisaDocument?->status ?? 'DRAFT' }}
                    </span>
                </div>
                <div class="space-y-2.5 pt-3 text-[11px]">
                    <div>
                        <span class="text-slate-400 block text-[10px]">Nomor Aju Pabean (26 Digit):</span>
                        <div class="font-mono font-bold text-slate-800 text-[11px] break-all bg-slate-50 p-2 rounded-xl border border-slate-100 mt-1">
                            {{ $shipment->ceisaDocument->nomor_aju ?? 'Belum ada pengajuan pabean dibuat' }}
                        </div>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px]">Kode Billing MPN G3 (15 Digit):</span>
                        @if($shipment->billing_code || $shipment->ceisaDocument?->kode_billing)
                            <div class="font-mono font-black text-indigo-700 text-base tracking-wider mt-0.5">
                                {{ $shipment->billing_code ?? $shipment->ceisaDocument?->kode_billing }}
                            </div>
                            <div class="text-[10px] text-slate-400">
                                Total: <strong class="text-slate-800">Rp {{ number_format($shipment->ceisaDocument?->total_billing ?? $taxes['total_billing'], 0, ',', '.') }}</strong>
                                &bull; Jatuh Tempo: {{ $shipment->ceisaDocument?->tgl_jatuh_tempo_billing?->format('d/m/Y') ?? '3 Hari Kerja' }}
                            </div>
                        @else
                            <div class="text-slate-400 text-xs italic mt-0.5">Diterbitkan otomatis saat dokumen disubmit ke CEISA.</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100">
                @if($shipment->ceisaDocument && $shipment->ceisaDocument->status !== 'DRAFT')
                    <a href="{{ $shipment->ceisaDocument->jenis_dokumen === '20' ? route('bc20.show', $shipment->ceisaDocument->id) : route('bc30.show', $shipment->ceisaDocument->id) }}"
                       class="w-full py-2.5 px-4 rounded-xl font-bold text-xs transition-all flex items-center justify-center space-x-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 shadow-xs">
                        <span>Buka Draf Resmi BC {{ $shipment->ceisaDocument->jenis_dokumen }} &rarr;</span>
                    </a>
                @else
                    @can('document.submit')
                    <button type="button"
                            onclick="document.getElementById('modal-submit-ceisa').classList.remove('hidden')"
                            {{ !$shipment->is_all_mandatory_documents_verified ? 'disabled' : '' }}
                            class="w-full py-2.5 px-4 rounded-xl font-bold text-xs transition-all flex items-center justify-center space-x-2
                                {{ $shipment->is_all_mandatory_documents_verified 
                                    ? 'bg-slate-900 hover:bg-slate-800 text-white shadow-xs' 
                                    : 'bg-slate-100 text-slate-400 cursor-not-allowed' }}">
                        <span>Kirim Dokumen ke CEISA 4.0 &rarr;</span>
                    </button>
                    @endcan
                @endif
            </div>
        </div>

    </div>

    <!-- 3. TABS NAVIGASI WORKSPACE UTAMA -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden mb-8">
        
        <!-- Tab Bar Buttons -->
        <div class="border-b border-slate-200 bg-slate-50/70 px-4 sm:px-6 flex items-center space-x-1 sm:space-x-2 overflow-x-auto text-xs font-semibold">
            <button type="button" @click="activeTab = 'docs'"
                    :class="activeTab === 'docs' ? 'border-sky-600 text-sky-700 bg-white shadow-2xs font-bold' : 'border-transparent text-slate-600 hover:text-slate-900'"
                    class="py-3 px-3.5 border-b-2 rounded-t-xl transition-all whitespace-nowrap flex items-center space-x-2">
                <span>📑</span>
                <span>Berkas Lampiran & Lartas</span>
                <span class="px-1.5 py-0.5 rounded text-[10px] bg-slate-100 text-slate-600 font-mono">{{ $shipment->documents->count() }}</span>
            </button>

            <button type="button" @click="activeTab = 'items'"
                    :class="activeTab === 'items' ? 'border-sky-600 text-sky-700 bg-white shadow-2xs font-bold' : 'border-transparent text-slate-600 hover:text-slate-900'"
                    class="py-3 px-3.5 border-b-2 rounded-t-xl transition-all whitespace-nowrap flex items-center space-x-2">
                <span>📦</span>
                <span>Item Barang & Pos Tarif (HS)</span>
                <span class="px-1.5 py-0.5 rounded text-[10px] bg-slate-100 text-slate-600 font-mono">{{ $shipment->items->count() }}</span>
            </button>

            <button type="button" @click="activeTab = 'ops'"
                    :class="activeTab === 'ops' ? 'border-sky-600 text-sky-700 bg-white shadow-2xs font-bold' : 'border-transparent text-slate-600 hover:text-slate-900'"
                    class="py-3 px-3.5 border-b-2 rounded-t-xl transition-all whitespace-nowrap flex items-center space-x-2">
                <span>🚚</span>
                <span>DO Pelayaran & Lapangan</span>
            </button>

            <button type="button" @click="activeTab = 'ceisa'"
                    :class="activeTab === 'ceisa' ? 'border-sky-600 text-sky-700 bg-white shadow-2xs font-bold' : 'border-transparent text-slate-600 hover:text-slate-900'"
                    class="py-3 px-3.5 border-b-2 rounded-t-xl transition-all whitespace-nowrap flex items-center space-x-2">
                <span>🏛️</span>
                <span>Draf Pabean & Log CEISA</span>
            </button>
        </div>

        <!-- ============================================== -->
        <!-- TAB 1: BERKAS LAMPIRAN & PERIZINAN LARTAS      -->
        <!-- ============================================== -->
        <div x-show="activeTab === 'docs'" class="p-5 sm:p-6 space-y-6">
            
            <!-- Seksi 1: Checklist Berkas Wajib -->
            <div>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3 mb-4">
                    <div>
                        <h3 class="font-bold text-sm text-slate-900">Checklist Berkas Pengapalan</h3>
                        <p class="text-slate-500 text-[11px] mt-0.5">Unggah berkas Invoice, Packing List, dan Bill of Lading asli klien untuk validasi pabean.</p>
                    </div>
                    <span class="text-[11px] font-mono font-bold text-slate-500">
                        {{ $shipment->documents->where('status', 'VERIFIED')->count() }} / {{ $shipment->documents->count() }} Lolos Verifikasi
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
                    @foreach($shipment->documents as $doc)
                        <div class="p-3.5 rounded-2xl border transition-all flex flex-col justify-between
                            @if($doc->status === 'VERIFIED') bg-emerald-50/30 border-emerald-200
                            @elseif($doc->status === 'UPLOADED') bg-sky-50/30 border-sky-200
                            @elseif($doc->status === 'REJECTED') bg-rose-50/30 border-rose-200
                            @else bg-slate-50/50 border-slate-200 @endif">
                            
                            <div>
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

                                <div class="text-[11px] text-slate-700 font-medium mb-1.5 leading-snug">
                                    {{ $doc->title_label }}
                                </div>

                                <div class="text-[10px] text-slate-400 mb-3 truncate">
                                    @if($doc->nama_file)
                                        <span>📄 {{ $doc->nama_file }}</span>
                                        @if($doc->file_size_kb) ({{ $doc->file_size_kb }} KB) @endif
                                    @else
                                        <span class="italic text-slate-400">Belum ada berkas terlampir</span>
                                    @endif
                                </div>
                            </div>

                            <div class="pt-2 border-t border-slate-200/60 space-y-2">
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

                                <div class="flex items-center justify-between pt-1">
                                    @if($doc->file_path)
                                        <a href="{{ route('core.document.download-doc', [$shipment->id, $doc->id]) }}" target="_blank"
                                           class="text-[10px] text-sky-700 hover:underline font-bold flex items-center space-x-1">
                                            <span>Lihat Berkas ↗</span>
                                        </a>
                                    @else
                                        <span></span>
                                    @endif

                                    @can('document.verify')
                                        @if($doc->status === 'UPLOADED')
                                        <form action="{{ route('core.document.verify-document', [$shipment->id, $doc->id]) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-2 py-0.5 rounded bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10px]">
                                                ✓ Setujui
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
            <div class="pt-4 border-t border-slate-200">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                    <div>
                        <h3 class="font-bold text-sm text-slate-900">Dokumen Perizinan Khusus & Lartas (Bila Ada)</h3>
                        <p class="text-slate-500 text-[11px] mt-0.5">Surat Persetujuan Impor (PI Kemendag), Laporan Surveyor (LS), Sertifikat SNI, atau dokumen karantina.</p>
                    </div>
                    @can('document.create')
                    <button type="button" onclick="document.getElementById('modal-add-item-customs-doc').classList.remove('hidden')"
                            class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs shadow-xs transition-all">
                        + Tambah Dokumen Lartas
                    </button>
                    @endcan
                </div>

                @if($shipment->itemCustomsDocuments->isNotEmpty())
                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                                <tr>
                                    <th class="p-3">Kode & Nama Dokumen</th>
                                    <th class="p-3">Item Kargo Terkait</th>
                                    <th class="p-3">Instansi Penerbit</th>
                                    <th class="p-3">No. & Tanggal Dokumen</th>
                                    <th class="p-3">Status</th>
                                    <th class="p-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($shipment->itemCustomsDocuments as $customDoc)
                                    <tr class="hover:bg-slate-50/60">
                                        <td class="p-3 font-medium text-slate-800">
                                            <span class="font-mono font-bold text-slate-900">[{{ $customDoc->kode_dokumen }}]</span> {{ $customDoc->nama_dokumen }}
                                        </td>
                                        <td class="p-3 text-slate-600">
                                            <span class="font-mono text-sky-700 font-bold">{{ $customDoc->item->kode_hs ?? '-' }}</span> &bull; {{ Str::limit($customDoc->item->uraian_barang ?? 'Seluruh Barang', 35) }}
                                        </td>
                                        <td class="p-3 text-slate-500">
                                            {{ $customDoc->instansi_penerbit ?? '-' }}
                                        </td>
                                        <td class="p-3 font-mono text-slate-800">
                                            {{ $customDoc->nomor_dokumen ?? 'Belum ada nomor' }}
                                            @if($customDoc->tanggal_dokumen)
                                                <div class="text-[10px] text-slate-400 font-sans">{{ $customDoc->tanggal_dokumen->format('d/m/Y') }}</div>
                                            @endif
                                        </td>
                                        <td class="p-3">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono
                                                @if($customDoc->status === 'VERIFIED') bg-emerald-100 text-emerald-800
                                                @elseif($customDoc->status === 'UPLOADED') bg-sky-100 text-sky-800
                                                @else bg-amber-100 text-amber-800 @endif">
                                                {{ $customDoc->status }}
                                            </span>
                                        </td>
                                        <td class="p-3 text-right space-x-1">
                                            <button type="button" 
                                                    onclick="openFulfillDocModal({{ json_encode($customDoc) }}, '{{ addslashes($customDoc->item->uraian_barang ?? '') }}', '{{ $customDoc->item->kode_hs ?? '' }}')"
                                                    class="px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-[11px]">
                                                Lengkapi
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-slate-500 text-xs text-center">
                        Tidak ada persyaratan izin lartas khusus yang terdeteksi untuk pengapalan ini.
                    </div>
                @endif
            </div>

        </div>

        <!-- ============================================== -->
        <!-- TAB 2: ITEM BARANG & POS TARIF (HS CODE)       -->
        <!-- ============================================== -->
        <div x-show="activeTab === 'items'" class="p-5 sm:p-6 space-y-6">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <div>
                    <h3 class="font-bold text-sm text-slate-900">Daftar Item Kargo & Pos Tarif (HS Code)</h3>
                    <p class="text-slate-500 text-[11px] mt-0.5">Klasifikasi pos tarif 8 digit, tarif Bea Masuk, PPN Impor, dan PPh Pasal 22.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="document.getElementById('modal-ai-ocr-scan').classList.remove('hidden')"
                            class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-all shadow-xs">
                        Ekstraksi Berkas Packing List
                    </button>
                    @can('document.create')
                    <button type="button" onclick="document.getElementById('modal-add-item').classList.remove('hidden')"
                            class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition-all">
                        + Tambah Item Manual
                    </button>
                    @endcan
                </div>
            </div>

            <!-- Tabel Item Barang -->
            <div class="border border-slate-200 rounded-xl overflow-hidden">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                        <tr>
                            <th class="p-3 w-10">No</th>
                            <th class="p-3">Pos Tarif (HS)</th>
                            <th class="p-3">Uraian Komoditas</th>
                            <th class="p-3 text-right">Kemasan</th>
                            <th class="p-3 text-right">Berat (Kg)</th>
                            <th class="p-3 text-right">Nilai CIF ({{ $shipment->currency }})</th>
                            <th class="p-3 text-center">Tarif Pabean</th>
                            <th class="p-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($shipment->items as $idx => $it)
                            <tr class="hover:bg-slate-50/60">
                                <td class="p-3 text-slate-400 font-mono">{{ $idx + 1 }}</td>
                                <td class="p-3 font-mono font-bold text-sky-800 whitespace-nowrap">
                                    {{ $it->kode_hs }}
                                    @if($it->has_lartas)
                                        <span class="block text-[9px] font-sans font-bold text-amber-700">⚠️ Wajib Lartas</span>
                                    @endif
                                </td>
                                <td class="p-3 font-medium text-slate-800">
                                    {{ $it->uraian_barang }}
                                </td>
                                <td class="p-3 text-right font-mono text-slate-700">
                                    {{ number_format($it->jumlah_kemasan) }} {{ $it->jenis_kemasan }}
                                </td>
                                <td class="p-3 text-right font-mono text-slate-700">
                                    {{ number_format($it->berat_netto, 2) }}
                                </td>
                                <td class="p-3 text-right font-mono font-bold text-slate-900">
                                    {{ number_format($it->nilai_fob, 2) }}
                                </td>
                                <td class="p-3 text-center text-[10px] text-slate-600 font-mono">
                                    BM: {{ $it->tarif_bm_persen }}% &bull; PPN: {{ $it->tarif_ppn_persen }}% &bull; PPh: {{ $it->tarif_pph_persen }}%
                                </td>
                                <td class="p-3 text-right whitespace-nowrap space-x-1">
                                    <button type="button" 
                                            onclick="openEditItemModal({{ json_encode($it) }})"
                                            class="px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-[11px]">
                                        Koreksi
                                    </button>
                                    @can('document.create')
                                    <form action="{{ route('core.document.items.destroy', [$shipment->id, $it->id]) }}" method="POST" class="inline" onsubmit="return confirm('Hapus item ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2 py-1 rounded text-rose-600 hover:bg-rose-50 font-semibold text-[11px]">
                                            Hapus
                                        </button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-8 text-center text-slate-400 text-xs">
                                    Belum ada item barang tercatat. Silakan unggah berkas Packing List atau gunakan tombol Tambah Item Manual.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Ringkasan Perhitungan Pungutan Pabean -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                <div class="font-bold text-slate-900 text-xs mb-3 flex items-center justify-between">
                    <span>Estimasi Pungutan Impor Pabean (Kurs NDBM: Rp {{ number_format($taxes['kurs'], 0, ',', '.') }})</span>
                    <span class="text-slate-500 font-mono text-[10px]">Perhitungan Otomatis</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <div class="p-3 rounded-xl bg-white border border-slate-200">
                        <span class="text-slate-400 text-[10px] block">Nilai CIF Rupiah:</span>
                        <strong class="font-mono text-slate-800">Rp {{ number_format($taxes['cif_idr'], 0, ',', '.') }}</strong>
                    </div>
                    <div class="p-3 rounded-xl bg-white border border-slate-200">
                        <span class="text-slate-400 text-[10px] block">Bea Masuk (BM):</span>
                        <strong class="font-mono text-slate-800">Rp {{ number_format($taxes['bea_masuk'], 0, ',', '.') }}</strong>
                    </div>
                    <div class="p-3 rounded-xl bg-white border border-slate-200">
                        <span class="text-slate-400 text-[10px] block">PPN Impor (11%):</span>
                        <strong class="font-mono text-slate-800">Rp {{ number_format($taxes['ppn'], 0, ',', '.') }}</strong>
                    </div>
                    <div class="p-3 rounded-xl bg-white border border-slate-200">
                        <span class="text-slate-400 text-[10px] block">PPh Pasal 22 (2.5%):</span>
                        <strong class="font-mono text-slate-800">Rp {{ number_format($taxes['pph'], 0, ',', '.') }}</strong>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-200 flex justify-between items-center">
                    <span class="font-bold text-slate-700 text-xs">Total Estimasi Billing MPN G3:</span>
                    <span class="font-mono font-black text-base text-emerald-700">Rp {{ number_format($taxes['total_billing'], 0, ',', '.') }}</span>
                </div>
            </div>

        </div>

        <!-- ============================================== -->
        <!-- TAB 3: DO PELAYARAN & LAPANGAN (TRAFFIC)       -->
        <!-- ============================================== -->
        <div x-show="activeTab === 'ops'" class="p-5 sm:p-6 space-y-6">
            
            <!-- Penebusan DO Pelayaran -->
            <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-100 gap-2 mb-4">
                    <div>
                        <h3 class="font-bold text-sm text-slate-900">Penebusan Delivery Order (DO) Pelayaran</h3>
                        <p class="text-slate-500 text-[11px] mt-0.5">Pengurusan pembayaran biaya pelayaran (THC, Ocean Freight, Cleaning) dan surat rilis DO.</p>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold font-mono 
                        @if($shipment->nomor_do) bg-emerald-100 text-emerald-800
                        @else bg-amber-100 text-amber-800 @endif">
                        {{ $shipment->nomor_do ? 'DO RILIS' : 'PROSES PENEBUSAN' }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs mb-4">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 text-[10px] block">Pelayaran Vendor:</span>
                        <strong class="text-slate-800 text-xs">{{ $shipment->shippingVendor?->name ?? 'MAERSK LINE INDONESIA' }}</strong>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 text-[10px] block">Nomor B/L:</span>
                        <strong class="font-mono text-slate-800 text-xs">{{ $shipment->bl_number ?? '-' }}</strong>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 text-[10px] block">Batas Waktu Free Time:</span>
                        <strong class="text-slate-800 text-xs">
                            {{ $shipment->free_time_expired_at ? $shipment->free_time_expired_at->format('d/m/Y') : ($shipment->free_time_days . ' Hari setelah kedatangan') }}
                        </strong>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 pt-2">
                    <form action="{{ route('core.do.send-email', $shipment->id) }}" method="POST">
                        @csrf
                        <button type="submit" 
                                onclick="return confirm('Kirim email permohonan invoice DO otomatis ke vendor pelayaran?')"
                                class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs shadow-xs transition-all">
                            ✉️ Kirim Permohonan Tagihan DO via Email
                        </button>
                    </form>

                    <a href="{{ route('pdf.do-request', $shipment->id) }}" target="_blank"
                       class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-all">
                        📄 Cetak Surat Permohonan DO (PDF)
                    </a>
                </div>
            </div>

            <!-- Status Lapangan & Pengeluaran Peti Kemas -->
            <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-100 gap-2 mb-4">
                    <div>
                        <h3 class="font-bold text-sm text-slate-900">Traffic Pelabuhan & Pengeluaran Barang</h3>
                        <p class="text-slate-500 text-[11px] mt-0.5">Status respon penjaluran kepabeanan, SPPB/NPE, gate out kontainer, dan tanda terima POD.</p>
                    </div>
                    @if($shipment->customs_channel)
                        <span class="px-2.5 py-1 rounded text-xs font-bold uppercase font-mono
                            @if($shipment->customs_channel === 'HIJAU') bg-emerald-100 text-emerald-800 border border-emerald-300
                            @elseif($shipment->customs_channel === 'KUNING') bg-amber-100 text-amber-800 border border-amber-300
                            @else bg-rose-100 text-rose-800 border border-rose-300 @endif">
                            Jalur {{ $shipment->customs_channel }}
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs mb-4">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 text-[10px] block">Status SPPB / NPE:</span>
                        <strong class="font-mono text-slate-800">
                            {{ $shipment->ceisaDocument?->nomor_sppb_npe ?? 'Belum terbit' }}
                        </strong>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 text-[10px] block">Nomor Peti Kemas:</span>
                        <strong class="font-mono text-slate-800">{{ $shipment->nomor_kontainer ?? '-' }}</strong>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 text-[10px] block">Status Gate Out:</span>
                        <strong class="text-slate-800">
                            {{ in_array($shipment->status, ['GATE_OUT', 'TRAFFIC_GATE_OUT', 'DELIVERED_POD', 'COMPLETED']) ? 'Sudah Keluar Pelabuhan' : 'Menunggu Pengeluaran' }}
                        </strong>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 text-[10px] block">Surat Jalan (POD):</span>
                        <strong class="text-slate-800">
                            {{ $shipment->pod_signed_at ? 'Diterima: ' . $shipment->pod_signed_at->format('d/m/Y') : 'Belum Ditandatangani' }}
                        </strong>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <a href="{{ route('core.traffic.index') }}" 
                       class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition-all">
                        Buka Cockpit Tim Traffic &rarr;
                    </a>
                </div>
            </div>

        </div>

        <!-- ============================================== -->
        <!-- TAB 4: DRAF PABEAN & LOG GATEWAY CEISA 4.0     -->
        <!-- ============================================== -->
        <div x-show="activeTab === 'ceisa'" class="p-5 sm:p-6 space-y-6">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="font-bold text-sm text-slate-900">Draf Dokumen Pabean & Log Gateway CEISA 4.0</h3>
                    <p class="text-slate-500 text-[11px] mt-0.5">Detail integrasi Host-to-Host dengan sistem DJBC.</p>
                </div>
                @if($shipment->ceisaDocument)
                    <a href="{{ $shipment->ceisaDocument->jenis_dokumen === '20' ? route('bc20.show', $shipment->ceisaDocument->id) : route('bc30.show', $shipment->ceisaDocument->id) }}"
                       class="px-3.5 py-1.5 rounded-xl bg-indigo-50 text-indigo-700 hover:bg-indigo-100 font-bold text-xs transition-colors border border-indigo-200">
                        Lihat Data JSON Pabean &rarr;
                    </a>
                @endif
            </div>

            @if($shipment->ceisaDocument)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2">
                        <div class="font-bold text-slate-800">Informasi Pendaftaran</div>
                        <div class="text-[11px] space-y-1 text-slate-600">
                            <div>Nomor Aju: <strong class="font-mono text-slate-900">{{ $shipment->ceisaDocument->nomor_aju }}</strong></div>
                            <div>Kantor Pabean: <strong>{{ $shipment->ceisaDocument->nama_kantor }}</strong> ({{ $shipment->ceisaDocument->kode_kantor }})</div>
                            <div>Nilai Pabean: <strong>{{ $shipment->ceisaDocument->kode_valuta }} {{ number_format($shipment->ceisaDocument->total_nilai, 2) }}</strong></div>
                            <div>Status Dokumen: <strong class="text-sky-700">{{ $shipment->ceisaDocument->status }}</strong></div>
                        </div>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2">
                        <div class="font-bold text-slate-800">Respon Resmi & Billing</div>
                        <div class="text-[11px] space-y-1 text-slate-600">
                            <div>Kode Billing: <strong class="font-mono text-indigo-700">{{ $shipment->ceisaDocument->kode_billing ?? 'Belum Terbit' }}</strong></div>
                            <div>Total Billing: <strong>Rp {{ number_format($shipment->ceisaDocument->total_billing ?? 0, 0, ',', '.') }}</strong></div>
                            <div>Jatuh Tempo: <strong>{{ $shipment->ceisaDocument->tgl_jatuh_tempo_billing?->format('d/m/Y') ?? '-' }}</strong></div>
                            <div>SPPB/NPE: <strong class="text-emerald-700">{{ $shipment->ceisaDocument->nomor_sppb_npe ?? '-' }}</strong></div>
                        </div>
                    </div>
                </div>

                <!-- Riwayat Log API -->
                <div class="pt-2">
                    <div class="font-bold text-xs text-slate-800 mb-2">Riwayat Pertukaran Data API:</div>
                    <div class="space-y-2 max-h-56 overflow-y-auto">
                        @forelse($shipment->ceisaDocument->logs as $log)
                            <div class="p-2.5 rounded-xl border border-slate-100 bg-slate-50/70 text-xs flex items-center justify-between font-mono">
                                <div class="flex items-center space-x-2">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $log->method === 'POST' ? 'bg-emerald-100 text-emerald-800' : 'bg-sky-100 text-sky-800' }}">{{ $log->method }}</span>
                                    <span class="text-slate-700">{{ $log->endpoint }}</span>
                                </div>
                                <div class="flex items-center space-x-3 text-slate-400 text-[10px]">
                                    <span class="px-2 py-0.5 rounded {{ $log->status_code === 200 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }} font-bold">{{ $log->status_code }}</span>
                                    <span>{{ $log->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="text-slate-400 text-xs italic">Belum ada riwayat transmisi ke gateway CEISA 4.0.</div>
                        @endforelse
                    </div>
                </div>
            @else
                <div class="p-8 text-center text-slate-400 text-xs">
                    Dokumen pabean draf belum dibuat. Lengkapi berkas dan item kargo terlebih dahulu, lalu klik tombol <strong>Kirim Dokumen ke CEISA 4.0</strong>.
                </div>
            @endif

        </div>

    </div>

</div>

<!-- ============================================== -->
<!-- MODAL WINDOWS                                  -->
<!-- ============================================== -->

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
                    <strong>Peringatan Dokumen Lartas:</strong> Masih terdapat dokumen izin lartas yang belum dilengkapi.
                </div>
            </div>
            @endif
            <div class="pt-2 border-t border-slate-200 text-slate-500 text-[10px]">
                Setelah submit berhasil, status pengapalan akan beralih ke <strong>BILLING_ISSUED</strong> dan tugas diteruskan ke antrean <strong>Tim DO</strong>.
            </div>
        </div>

        <form action="{{ route('core.document.submit-ceisa', $shipment->id) }}" method="POST">
            @csrf
            <div class="flex items-center justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-submit-ceisa').classList.add('hidden')" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold shadow-xs">
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
                <input type="text" name="uraian_barang" required placeholder="Nama spesifikasi komoditas barang lengkap" class="w-full px-3 py-2 rounded-xl border border-slate-200">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Dasar Hukum & Catatan Pos Tarif</label>
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
                    <input type="number" step="0.01" name="berat_bruto" value="100.00" required class="w-full px-3 py-2 rounded-xl border border-slate-200">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Berat Netto (Kg)</label>
                    <input type="number" step="0.01" name="berat_netto" value="95.00" required class="w-full px-3 py-2 rounded-xl border border-slate-200">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nilai FOB/CIF ({{ $shipment->currency }})</label>
                    <input type="number" step="0.01" name="nilai_fob" value="1000.00" required class="w-full px-3 py-2 rounded-xl border border-slate-200">
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
                <h3 class="text-sm font-extrabold text-slate-900">Lengkapi Dokumen Perizinan Lartas</h3>
                <p class="text-[11px] text-slate-400">Input nomor dokumen, tanggal terbit, dan unggah berkas izin resmi.</p>
            </div>
            <button type="button" onclick="document.getElementById('modal-fulfill-item-doc').classList.add('hidden')" class="p-1 rounded-lg text-slate-400 hover:text-slate-600">
                ✕
            </button>
        </div>

        <form id="form-fulfill-item-doc" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-200/70 space-y-1.5 text-[11px]">
                <div class="flex items-center space-x-2">
                    <span id="fulfill_doc_code_badge" class="px-2 py-0.5 rounded font-mono font-black text-amber-800 bg-amber-100 text-[10px]">861</span>
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
                <input type="text" name="catatan" id="fulfill_catatan" placeholder="Masa berlaku s.d 31 Des 2026" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
            </div>

            @can('document.verify')
            <div>
                <label class="block font-bold text-slate-700 mb-1">Status Verifikasi Dokumen</label>
                <select name="status" id="fulfill_status" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-xs font-semibold">
                    <option value="UPLOADED">UPLOADED (Menunggu Verifikasi)</option>
                    <option value="VERIFIED">VERIFIED (Disetujui Sah)</option>
                    <option value="MISSING">MISSING (Belum Lengkap)</option>
                </select>
            </div>
            @endcan

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-fulfill-item-doc').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold shadow-xs">
                    Simpan Dokumen
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 4: Edit Item Manifes & Koreksi HS Code -->
<div id="modal-edit-item" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-xl w-full p-6 text-xs">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <div>
                <h3 class="text-sm font-extrabold text-slate-900">Koreksi & Sunting Rincian Barang</h3>
                <p class="text-[11px] text-slate-400">Edit uraian komoditas, pos tarif HS Code, dan nilai barang.</p>
            </div>
            <button type="button" onclick="document.getElementById('modal-edit-item').classList.add('hidden')" class="p-1 rounded-lg text-slate-400 hover:text-slate-600">
                ✕
            </button>
        </div>

        <form id="form-edit-item" method="POST" class="space-y-3.5">
            @csrf
            @method('PUT')
            <div>
                <label class="block font-bold text-slate-700 mb-1">Pos Tarif HS Code (BTKI) <span class="text-rose-500">*</span></label>
                <div class="flex space-x-2">
                    <input type="text" name="kode_hs" id="edit_item_kode_hs" onchange="lookupEditHsModal(this.value)" required class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono text-xs">
                    <button type="button" onclick="lookupEditHsModal(document.getElementById('edit_item_kode_hs').value)" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs shrink-0">
                        Cek Tarif & Lartas
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
                    <label class="block font-semibold text-slate-700 mb-1">Dasar Hukum & Catatan Pos Tarif</label>
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
                <button type="submit" class="px-5 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs shadow-xs">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 5: Ekstraksi Berkas Packing List -->
<div id="modal-ai-ocr-scan" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-2xl w-full p-6 text-xs">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <div>
                <h3 class="text-sm font-extrabold text-slate-900">Ekstraksi Berkas Packing List</h3>
                <p class="text-[11px] text-slate-400">Pencocokan pos tarif HS Code dan rincian kemasan otomatis dari berkas dokumen.</p>
            </div>
            <button type="button" onclick="document.getElementById('modal-ai-ocr-scan').classList.add('hidden')" class="p-1 rounded-lg text-slate-400 hover:text-slate-600">
                ✕
            </button>
        </div>

        <form action="{{ route('core.document.detect-hs', $shipment->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div class="p-4 rounded-2xl border-2 border-dashed border-slate-200 hover:border-sky-400 transition-colors bg-slate-50/50">
                <label class="block font-bold text-slate-800 mb-1">Unggah Berkas Packing List (PDF, Excel XLSX/XLS, Gambar, CSV)</label>
                <p class="text-[11px] text-slate-500 mb-3">Sistem mengekstrak tabel barang, jumlah kemasan, bobot bruto/netto, dan nilai kargo.</p>
                <input type="file" name="packing_list_file" id="scanner_file_input" accept=".pdf,.xlsx,.xls,.csv,.txt,.jpg,.jpeg,.png"
                       class="text-xs text-slate-600 w-full file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-sky-600 file:text-white hover:file:bg-sky-700">
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="font-bold text-slate-800">Atau Tempel Teks Tabel Barang:</label>
                    <span class="text-[10px] text-slate-400">Format baris teks tabel</span>
                </div>
                <textarea name="raw_text" id="scanner_raw_text" rows="4" placeholder="Contoh:
1. INDUSTRIAL SEWING MACHINE HEAVY DUTY 50 CTN 2500 KGS {{ $shipment->currency }} 12500 (HS: 8452.29.00)
2. SPARE PARTS AND NEEDLES 100 BOX 5300 KGS {{ $shipment->currency }} 12500 (HS: 8452.90.00)" 
                    class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono text-[11px]"></textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-ai-ocr-scan').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-xs">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs shadow-xs">
                    Ekstrak & Terapkan ke Manifes Proyek
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 6: Tambah Dokumen Khusus / Perizinan Lartas Baru -->
<div id="modal-add-item-customs-doc" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 text-xs">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <div>
                <h3 class="text-sm font-extrabold text-slate-900">Tambah Dokumen Khusus / Izin Lartas</h3>
                <p class="text-[11px] text-slate-400">Hubungkan surat izin atau perizinan khusus instansi dengan item kargo.</p>
            </div>
            <button type="button" onclick="document.getElementById('modal-add-item-customs-doc').classList.add('hidden')" class="p-1 rounded-lg text-slate-400 hover:text-slate-600">
                ✕
            </button>
        </div>

        <form action="{{ route('core.document.item-document.store', $shipment->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3.5">
            @csrf

            <div>
                <label class="block font-bold text-slate-700 mb-1">Pilih Item Barang Terkait <span class="text-rose-500">*</span></label>
                <select name="shipment_item_id" id="add_doc_item_id" required class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-xs font-semibold">
                    <option value="">-- Pilih Barang Manifes --</option>
                    @foreach($shipment->items as $it)
                        <option value="{{ $it->id }}">
                            [{{ $it->kode_hs }}] {{ Str::limit($it->uraian_barang, 45) }} ({{ number_format($it->jumlah_kemasan) }} {{ $it->jenis_kemasan }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Jenis & Kode Dokumen Pabean <span class="text-rose-500">*</span></label>
                <select id="preset_doc_selector" onchange="applyCustomsDocPreset(this.value)" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-xs font-medium mb-2">
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
                    <label class="block font-bold text-slate-700 mb-1">Kode Dokumen <span class="text-rose-500">*</span></label>
                    <input type="text" name="kode_dokumen" id="add_doc_kode" required placeholder="861 / 999" class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono text-xs">
                </div>
                <div class="col-span-2">
                    <label class="block font-bold text-slate-700 mb-1">Nama Resmi Dokumen <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_dokumen" id="add_doc_nama" required placeholder="Contoh: Laporan Surveyor" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Instansi Penerbit</label>
                <input type="text" name="instansi_penerbit" id="add_doc_instansi" placeholder="Kementerian Perdagangan / Sucofindo" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nomor Dokumen / Izin</label>
                    <input type="text" name="nomor_dokumen" placeholder="Contoh: LS-90281/KMD/X/2026" class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono text-xs">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Tanggal Dokumen</label>
                    <input type="date" name="tanggal_dokumen" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Unggah Berkas Fisik (PDF / Gambar) <span class="text-slate-400 font-normal">(Opsional)</span></label>
                <input type="file" name="document_file" accept=".pdf,.png,.jpg,.jpeg,.xlsx,.xls"
                       class="text-xs text-slate-500 w-full file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-[11px] file:bg-sky-600 file:text-white hover:file:bg-sky-700">
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-add-item-customs-doc').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-xs">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs">
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
                        badge.innerHTML = `<span>⚠️ <strong>LARTAS:</strong> ${data.lartas}</span>`;
                    } else {
                        badge.className = 'mt-1.5 p-2 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-[11px] font-medium flex items-center space-x-1.5';
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
                        badge.className = 'mt-1.5 p-2 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-[11px] font-medium flex items-center space-x-1.5';
                        badge.innerHTML = `<span>⚠️ <strong>LARTAS:</strong> ${data.lartas}</span>`;
                    } else {
                        badge.className = 'mt-1.5 p-2 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-[11px] font-medium flex items-center space-x-1.5';
                        badge.innerHTML = `<span>✓ <strong>Bebas Lartas:</strong> Pos Tarif ${data.code} siap diajukan.</span>`;
                    }
                }
            }
        } catch (e) {
            console.error('Gagal lookup HS:', e);
        }
    }
</script>

@endsection
