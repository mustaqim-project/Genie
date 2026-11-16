@extends('layouts.app')

@section('title', 'Detail Dokumen BC 3.0 - ' . $bc30->nomor_aju)

@section('content')
@php
    $isBillingLocked = $isBillingLocked ?? $bc30->isBillingLocked();
@endphp
<div class="max-w-[1360px] mx-auto px-4 sm:px-6 space-y-6 pt-4 pb-28 lg:pb-12">

    <!-- 0. Header Proyek & 11 Pos Workflow Stepper -->
    @if(isset($shipment))
        @include('core.components.workflow_pos_stepper')

        <!-- Alert Demurrage & Free Time Warning -->
        @if($shipment->is_demurrage_warning)
        <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200/80 dark:border-rose-900/50 text-rose-800 dark:text-rose-300 text-xs flex items-center justify-between gap-4 mb-6">
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

        <!-- 1. Tim & PIC Project (Pembagian Tugas 11 Pos & Delegasi SLA) -->
        <div id="pos-assignment">
            @include('core.components.project_pic_assignment_card')
        </div>
    @endif

    <!-- Banner Transparan Alur & Inspeksi Dokumen (Billing / NPE Terbit) -->
    @if($isBillingLocked)
    <div class="p-4 sm:p-5 rounded-2xl bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-900/50 text-slate-800 dark:text-slate-200 text-xs flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-xs">
        <div class="flex items-start sm:items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/50 text-amber-600 flex items-center justify-center shrink-0">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <strong class="font-bold text-sm text-slate-900 dark:text-white">Dokumen Terbit Resmi CEISA 4.0: {{ $bc30->status }}</strong>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400">
                        {{ $bc30->isFromCeisaPull() ? 'DATA TARIKAN CEISA' : 'INTEGRASI GENIE' }}
                    </span>
                </div>
                <p class="text-slate-600 dark:text-slate-300 text-[11px] mt-0.5">
                    {{ $bc30->isFromCeisaPull() 
                        ? 'Dokumen ini ditarik dari CEISA DJBC. Seluruh tahapan 1 - 9 dapat ditinjau. Anda dapat mengunggah berkas fisik pelengkap (B/L, Invoice, Packing List) sebagai syarat kelengkapan operasional ekspor.' 
                        : 'Respon resmi (NPE / Billing) telah terbit di DJBC. Seluruh proses sebelumnya dan data 9 tahapan pabean tetap dapat Anda buka dalam Mode Inspeksi transparan.' }}
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('bc30.edit', $bc30->id) }}" 
               class="btn-apple-filled text-xs inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl shadow-xs !bg-amber-600 hover:!bg-amber-700">
                <i data-lucide="eye" class="w-4 h-4"></i>
                <span>Buka Wizard 9 Tahap (Inspeksi)</span>
            </a>
            <button type="button" 
                    onclick="document.getElementById('modal-upload-pelengkap-bc30')?.classList.remove('hidden')"
                    class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] hover:bg-slate-50 dark:hover:bg-white/10 text-slate-700 dark:text-slate-200 font-semibold inline-flex items-center gap-1.5 cursor-pointer text-xs shadow-2xs">
                <i data-lucide="upload" class="w-3.5 h-3.5 text-amber-600"></i>
                <span>Unggah Berkas Fisik</span>
            </button>
        </div>
    </div>
    @endif

    <!-- Breadcrumb & Header Toolbar -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-5 sm:p-6 border border-black/[0.06] dark:border-white/[0.08] shadow-xs">
        <nav class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 font-medium mb-2" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="hover:text-[#0284c7] transition-colors flex items-center gap-1">
                <i data-lucide="home" class="w-3.5 h-3.5"></i>
                <span>Dashboard</span>
            </a>
            <i data-lucide="chevron-right" class="w-3 h-3 text-slate-300 dark:text-slate-600"></i>
            <a href="{{ route('bc30.index') }}" class="hover:text-[#0284c7] transition-colors">BC 3.0 (Ekspor)</a>
            <i data-lucide="chevron-right" class="w-3 h-3 text-slate-300 dark:text-slate-600"></i>
            <span class="text-slate-800 dark:text-white font-mono font-semibold">{{ $bc30->nomor_aju }}</span>
        </nav>

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 pt-2">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-semibold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                        BC 3.0 (PEB)
                    </span>
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-semibold {{ $bc30->status_badge_class }}">
                        {{ $bc30->status }}
                    </span>
                    @if($bc30->jalur)
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold font-mono {{ $bc30->jalur === 'HIJAU' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/80' : 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border border-rose-200/80' }}">
                            Jalur {{ $bc30->jalur }}
                        </span>
                    @endif
                </div>
                <h1 class="text-2xl font-bold font-mono text-slate-900 dark:text-white tracking-tight">{{ $bc30->nomor_aju }}</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Kantor Pabean Muat: <strong class="text-slate-700 dark:text-slate-300">{{ $bc30->kode_kantor }}</strong> ({{ $bc30->nama_kantor }}) <span class="text-slate-300 dark:text-slate-600 mx-1">/</span> Dibuat: <span class="tabular-nums">{{ $bc30->created_at->format('d/m/Y H:i') }}</span>
                </p>
            </div>

            <!-- Action Buttons Host-to-Host -->
            <div class="flex flex-wrap items-center gap-2">
                @php
                    $isBillingLocked = $bc30->isBillingLocked();
                    $canEditDraft = auth()->user()->isSuperAdmin() || auth()->user()->hasAnyPermission(['document.edit', 'document.edit_after_ceisa']);
                    $canEditAfterCeisa = auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('document.edit_after_ceisa');
                @endphp

                @if($isBillingLocked)
                    <!-- Tombol Buka Wizard 9 Tahap (Mode Inspeksi atau Penyesuaian Operasional) -->
                    <a href="{{ route('bc30.edit', $bc30->id) }}" 
                       class="btn-apple-filled text-xs inline-flex items-center gap-1.5 px-3.5 py-2 !bg-amber-600 hover:!bg-amber-700"
                       title="{{ $bc30->isFromCeisaPull() ? 'Lengkapi Data Operasional & Berkas (9 Tahap)' : 'Tinjau Tahapan Dokumen (Mode Inspeksi 9 Tahap)' }}">
                        <i data-lucide="{{ $bc30->isFromCeisaPull() ? 'edit-3' : 'eye' }}" class="w-3.5 h-3.5"></i>
                        <span>{{ $bc30->isFromCeisaPull() ? 'Edit Data / Wizard 9 Tahap' : 'Buka Wizard 9 Tahap (Inspeksi)' }}</span>
                    </a>

                    <!-- Indikator Terkunci & Read-Only saat Billing / NPE Terbit -->
                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-700 dark:text-amber-400 text-xs font-semibold"
                         title="Respon Resmi Kepabeanan (NPE/Billing) telah diterbitkan oleh DJBC. Dokumen terkunci permanen untuk menjaga integritas data fiskal. Data tetap dapat dilihat sepenuhnya (Read-Only).">
                        <i data-lucide="lock" class="w-3.5 h-3.5 text-amber-600"></i>
                        <span>Terkunci</span>
                    </div>

                    @if($bc30->shipment)
                        <a href="{{ route('core.document.show', $bc30->shipment->id) }}" 
                           class="btn-apple-gray text-xs inline-flex items-center gap-1.5 px-3.5 py-2 !text-[#34C759] !border-[#34C759]/30 hover:!bg-[#34C759]/10"
                           title="Buka rincian berkas, deteksi HS code, dan proses sebelumnya pada workspace shipment">
                            <i data-lucide="folder" class="w-3.5 h-3.5 text-[#34C759]"></i>
                            <span>Workspace Berkas</span>
                        </a>
                    @endif
                @else
                    @if($bc30->status === 'DRAFT')
                        @if($canEditDraft)
                            <a href="{{ route('bc30.edit', $bc30->id) }}" 
                               class="btn-apple-gray text-xs inline-flex items-center gap-1.5 px-3.5 py-2 !border-[#34C759]/30 !text-[#34C759] hover:!bg-[#34C759]/10">
                                <i data-lucide="edit-3" class="w-3.5 h-3.5 text-[#34C759]"></i>
                                <span>Edit Draft PEB</span>
                            </a>
                        @endif
                        @if($bc30->isDraftDeletable() && (auth()->user()->isSuperAdmin() || auth()->user()->hasAnyPermission(['document.create', 'document.edit', 'document.delete'])))
                            <form action="{{ route('bc30.destroy', $bc30->id) }}" method="POST" class="inline"
                                  onsubmit="return confirmSweetAlert(event, 'Hapus draft BC 3.0 ({{ $bc30->nomor_aju }}) beserta proyek draft & seluruh aset berkas dokumennya?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="btn-apple-gray text-xs inline-flex items-center gap-1.5 px-3.5 py-2 !text-rose-600 dark:!text-rose-400 !border-rose-300/60 hover:!bg-rose-500/10 cursor-pointer">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    <span>Hapus Draft &amp; Berkas</span>
                                </button>
                            </form>
                        @endif
                    @else
                        @if($canEditAfterCeisa)
                            <a href="{{ route('bc30.edit', $bc30->id) }}" 
                               class="btn-apple-gray text-xs inline-flex items-center gap-1.5 px-3.5 py-2 !border-amber-500/40 !text-amber-700 dark:!text-amber-400 hover:!bg-amber-500/10"
                               title="Izin khusus: Revisi data setelah dokumen terkirim ke CEISA">
                                <i data-lucide="edit-3" class="w-3.5 h-3.5 text-amber-600"></i>
                                <span>Revisi Pasca-CEISA</span>
                            </a>

                            <form action="{{ route('bc30.reopen-draft', $bc30->id) }}" method="POST" class="inline"
                                  onsubmit="return confirmSweetAlert(event, 'Apakah Anda yakin ingin membuka kembali dokumen ini ke status DRAFT untuk revisi ulang dan penyesuaian skema CEISA?');">
                                @csrf
                                <button type="submit" 
                                        class="btn-apple-gray text-xs inline-flex items-center gap-1.5 px-3.5 py-2 hover:!text-indigo-600"
                                        title="Kembalikan status dokumen ke DRAFT">
                                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5 text-indigo-600"></i>
                                    <span>Buka Kembali ke Draft</span>
                                </button>
                            </form>
                        @endif
                    @endif
                @endif


                <form action="{{ route('bc30.check-schema', $bc30->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-apple-gray text-xs inline-flex items-center gap-1.5 px-3.5 py-2">
                        <i data-lucide="check-square" class="w-3.5 h-3.5 text-purple-600"></i>
                        <span>Validasi Skema</span>
                    </button>
                </form>

                @if($bc30->status === 'DRAFT' || $bc30->status === 'REJECT')
                    <form action="{{ route('bc30.submit', $bc30->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-apple-filled text-xs inline-flex items-center gap-1.5 px-4 py-2 cursor-pointer !bg-[#34C759] hover:!bg-[#2fb350]">
                            <i data-lucide="send" class="w-3.5 h-3.5"></i>
                            <span>Kirim ke CEISA 4.0</span>
                        </button>
                    </form>
                @endif

                <form action="{{ route('bc30.check-status', $bc30->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-apple-gray text-xs inline-flex items-center gap-1.5 px-3.5 py-2">
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-slate-500"></i>
                        <span>Cek Status</span>
                    </button>
                </form>

                <form action="{{ route('bc30.fetch-response', $bc30->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-apple-tinted text-xs inline-flex items-center gap-1.5 px-3.5 py-2">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        <span>Tarik Respon NPE</span>
                    </button>
                </form>
            </div>
        </div>

        @if($bc30->is_schema_valid)
            <div class="mt-4 p-3 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200/80 dark:border-emerald-800/40 text-emerald-800 dark:text-emerald-300 text-xs flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                    <span><strong>Pre-Flight Check Lolos:</strong> {{ $bc30->schema_validation_message }}</span>
                </div>
                <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-300 font-bold">READY TO SUBMIT</span>
            </div>
        @elseif($bc30->schema_validation_message)
            <div class="mt-4 p-3 rounded-2xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200/80 dark:border-rose-900/40 text-rose-800 dark:text-rose-300 text-xs flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i>
                <span><strong>Catatan Skema:</strong> {{ $bc30->schema_validation_message }}</span>
            </div>
        @endif

        @if(isset($shipment))
        <div class="mt-6 pt-5 border-t border-black/[0.06] dark:border-white/[0.06] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 rounded-lg bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 text-xs font-bold font-mono">
                    {{ $shipment->shipment_number ?? $shipment->project_no }}
                </span>
                <span class="text-xs text-slate-600 dark:text-slate-300">
                    Pelacakan Alur 11 Pos Ekspor: <strong class="text-slate-900 dark:text-white">Tahap {{ $shipment->stage_index + 1 }} dari 11 Pos ({{ $shipment->stage_pipeline_current['title'] ?? 'Alur Berjalan' }})</strong>
                </span>
            </div>
            <a href="{{ route('core.document.show', $shipment->id) }}" 
               class="btn-apple-filled text-xs inline-flex items-center gap-1.5 px-3.5 py-2">
                <span>Buka Workspace 9 Berkas & DO</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>
        @endif
    </div>

    <!-- Skema Alur Waterfall Pabean & Tracking Dokumen Terpadu (Sinergi Create-Edit-Detail) -->
    @php
        $waterfallStage = 4;
        if ($bc30->is_schema_valid && $bc30->status === 'DRAFT') {
            $waterfallStage = 5;
        } elseif ($bc30->kode_billing || in_array($bc30->status, ['SUBMITTED', 'REJECT', 'SIAP_KIRIM'], true)) {
            $waterfallStage = 6;
        } elseif (in_array($bc30->status, ['NPE', 'SPPB', 'GATE_IN', 'SELESAI'], true)) {
            $waterfallStage = 7;
        }
    @endphp
    @include('bc20.components.waterfall_stepper', [
        'activeStage' => $waterfallStage,
        'bc20' => $bc30,
        'shipment' => $shipment ?? null,
    ])

    <!-- NPE Card if available -->
    @if($bc30->nomor_sppb_npe)
        <div class="bg-gradient-to-br from-emerald-600 to-emerald-700 rounded-2xl p-6 text-white shadow-lg shadow-emerald-600/20">
            <div class="flex items-center justify-between mb-4">
                <span class="px-2.5 py-0.5 rounded-full bg-white/20 text-white font-bold text-xs uppercase">
                    Respon Resmi: NOTA PELAYANAN EKSPOR (NPE) TERBIT
                </span>
                <span class="text-xs font-medium text-emerald-100 tabular-nums">{{ $bc30->tanggal_sppb_npe?->format('d/m/Y H:i') }}</span>
            </div>
            <div class="font-mono text-3xl font-black tracking-wider mb-2">
                {{ $bc30->nomor_sppb_npe }}
            </div>
            <p class="text-xs text-emerald-100 mb-4">
                Dokumen NPE telah terverifikasi sah oleh sistem DJBC. Barang resmi diizinkan untuk dimasukkan ke kawasan pabean (gate in) dan dimuat ke dalam sarana pengangkut ekspor.
            </p>
            <div class="inline-flex items-center gap-1.5 text-xs font-bold text-white bg-white/10 px-3 py-1.5 rounded-lg">
                <i data-lucide="check" class="w-4 h-4"></i>
                <span>Status Pabean Ekspor: Selesai / Siap Pemuatan</span>
            </div>
        </div>
    @endif

    <!-- Document Breakdown Details -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-5 sm:p-6 border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-6">
        <div>
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-4">Ringkasan Nilai Pabean Ekspor (FOB)</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs mb-4">
                <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.06]">
                    <span class="text-slate-400 dark:text-slate-500 block font-medium">Devisa Ekspor FOB ({{ $bc30->kode_valuta }})</span>
                    <span class="font-mono font-bold text-slate-800 dark:text-white text-sm tabular-nums">{{ $bc30->kode_valuta }} {{ number_format($bc30->total_nilai, 2) }}</span>
                </div>
                <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.06]">
                    <span class="text-slate-400 dark:text-slate-500 block font-medium">Kurs KMK (NDBM)</span>
                    <span class="font-mono font-bold text-slate-800 dark:text-white text-sm tabular-nums">Rp {{ number_format($bc30->ndbm_kurs, 2) }}</span>
                </div>
                <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.06]">
                    <span class="text-slate-400 dark:text-slate-500 block font-medium">Total Devisa IDR</span>
                    <span class="font-mono font-bold text-slate-800 dark:text-white text-sm tabular-nums">Rp {{ number_format($bc30->total_nilai_idr, 0, ',', '.') }}</span>
                </div>
                <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.06]">
                    <span class="text-slate-400 dark:text-slate-500 block font-medium">Total Bruto / Netto</span>
                    <span class="font-mono font-bold text-slate-800 dark:text-white text-sm tabular-nums">{{ number_format($bc30->total_bruto, 2) }} / {{ number_format($bc30->total_netto, 2) }} KG</span>
                </div>
            </div>
        </div>

        <!-- Kesiapan Barang & Bank Devisa -->
        <div class="p-4 rounded-2xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.06]">
            <h3 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Kesiapan Barang Ekspor & Bank Devisa (DHE)</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                <div>
                    <span class="text-slate-400 dark:text-slate-500 block font-medium">PIC Kesiapan Barang</span>
                    <span class="font-bold text-slate-800 dark:text-white">{{ $bc30->pic_pembuat ?? '-' }}</span>
                    <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">{{ $bc30->telepon_pembuat ?? '-' }}</div>
                </div>
                <div>
                    <span class="text-slate-400 dark:text-slate-500 block font-medium">Lokasi Siap Periksa</span>
                    <span class="font-semibold text-slate-800 dark:text-white">{{ $bc30->lokasi_siap_periksa ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 dark:text-slate-500 block font-medium">Tanggal Kesiapan</span>
                    <span class="font-mono font-bold text-slate-800 dark:text-white">{{ $bc30->tanggal_periksa?->format('d/m/Y') ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 dark:text-slate-500 block font-medium">Bank Devisa Hasil Ekspor</span>
                    <span class="font-bold text-[#0284c7]">{{ $bc30->bank_nama ?? '-' }}</span>
                    <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">Kode Bank: {{ $bc30->bank_kode ?? '-' }}</div>
                </div>
            </div>
        </div>

        <!-- Rincian Barang Table -->
        @php
            $dokumenRef = [
                '380' => 'Invoice (Commercial Invoice)',
                '705' => 'Bill of Lading (B/L)',
                '740' => 'Air Waybill (AWB)',
                '217' => 'Packing List',
                '861' => 'Laporan Surveyor (LS Kemendag)',
                '860' => 'Persetujuan Ekspor / Impor Kemendag',
                '810' => 'Sertifikat Standar / Mutu Ekspor',
                '853' => 'Sertifikat Standar SDPPI / POSTEL',
                '831' => 'Sertifikat Fitosanitari / Karantina (Barantin)',
                '880' => 'Dokumen V-Legal (KLHK)',
                '872' => 'Eksportir Terdaftar (ET) Sawit / CPO Kemendag',
                '911' => 'Surat Rekomendasi Teknis Kementerian',
            ];
            $barangList = $bc30->raw_payload['barang'] ?? [];
            $dokumenList = $bc30->raw_payload['dokumen'] ?? [];
        @endphp
        <div>
            <h3 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Rincian Barang Ekspor yang Diberitahukan</h3>
            <div class="overflow-x-auto border border-black/[0.06] dark:border-white/[0.08] rounded-2xl">
                <table class="w-full text-left text-xs">
                    <thead class="bg-black/[0.02] dark:bg-white/[0.02] text-slate-500 dark:text-slate-400 font-semibold uppercase text-[10px] tracking-wider border-b border-black/[0.06] dark:border-white/[0.06]">
                        <tr>
                            <th class="py-3 px-3.5">No</th>
                            <th class="py-3 px-3.5">HS Code (BTKI)</th>
                            <th class="py-3 px-3.5">Uraian Barang</th>
                            <th class="py-3 px-3.5">Jumlah Satuan</th>
                            <th class="py-3 px-3.5">FOB ({{ $bc30->kode_valuta }})</th>
                            <th class="py-3 px-3.5">Nilai Ekspor (IDR)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.04]">
                        @forelse($barangList as $index => $item)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                                <td class="py-3 px-3.5 font-mono text-slate-400">{{ $index + 1 }}</td>
                                <td class="py-3 px-3.5 font-mono font-bold text-[#0284c7]">{{ $item['posTarif'] ?? '-' }}</td>
                                <td class="py-3 px-3.5">
                                    <div class="font-semibold text-slate-800 dark:text-white">{{ $item['uraian'] ?? '-' }}</div>
                                    <div class="text-[10px] text-slate-400 dark:text-slate-500">Merk: {{ $item['merk'] ?? '-' }} | Daerah: {{ $item['kodeDaerahAsal'] ?? '-' }}</div>
                                    @if(!empty($item['barangDokumen']))
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            @foreach($item['barangDokumen'] as $bd)
                                                @php
                                                    $matchedDoc = collect($dokumenList)->firstWhere('seriDokumen', $bd['seriDokumen'] ?? null);
                                                    $docCode = $matchedDoc['kodeDokumen'] ?? '';
                                                    $docName = $dokumenRef[$docCode] ?? ($matchedDoc['namaDokumen'] ?? 'Izin Ekspor');
                                                    $docNo = $matchedDoc['nomorDokumen'] ?? '-';
                                                @endphp
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[9px] font-semibold bg-[#0284c7]/10 text-[#0284c7] border border-[#0284c7]/20">
                                                    <span>📑 Seri #{{ $bd['seriDokumen'] }} [{{ $docCode }}]</span>
                                                    <span class="font-mono">{{ $docNo }}</span>
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3 px-3.5 font-mono tabular-nums text-slate-700 dark:text-slate-300">{{ $item['jumlahSatuan'] ?? 0 }} {{ $item['kodeSatuanBarang'] ?? '' }}</td>
                                <td class="py-3 px-3.5 font-mono tabular-nums text-slate-700 dark:text-slate-300">{{ $bc30->kode_valuta }} {{ number_format($item['fob'] ?? 0, 2) }}</td>
                                <td class="py-3 px-3.5 font-mono font-bold text-slate-900 dark:text-white tabular-nums">Rp {{ number_format($item['fobRupiah'] ?? 0, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-slate-400 dark:text-slate-500">Data barang tidak tersedia di payload.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Lampiran Dokumen Pelengkap & Izin Lartas Section -->
        <div>
            <h3 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2 flex items-center justify-between">
                <span class="flex items-center gap-2">
                    <i data-lucide="files" class="w-4 h-4 text-[#0284c7]"></i>
                    <span>Lampiran Dokumen Pelengkap Pabean & Perizinan Ekspor Lartas (CEISA 4.0)</span>
                </span>
                <span class="text-slate-400 dark:text-slate-500 font-mono text-[10px]">{{ count($dokumenList) }} Dokumen Terlampir</span>
            </h3>
            <div class="overflow-x-auto border border-black/[0.06] dark:border-white/[0.08] rounded-2xl">
                <table class="w-full text-left text-xs">
                    <thead class="bg-black/[0.02] dark:bg-white/[0.02] text-slate-500 dark:text-slate-400 font-semibold uppercase text-[10px] tracking-wider border-b border-black/[0.06] dark:border-white/[0.06]">
                        <tr>
                            <th class="py-3 px-3.5 text-center">Seri</th>
                            <th class="py-3 px-3.5">Kode Dok.</th>
                            <th class="py-3 px-3.5">Nama / Jenis Dokumen</th>
                            <th class="py-3 px-3.5">Nomor Dokumen</th>
                            <th class="py-3 px-3.5">Tanggal Dokumen</th>
                            <th class="py-3 px-3.5">Kategori</th>
                            <th class="py-3 px-3.5">Terkait Barang</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.04]">
                        @forelse($dokumenList as $doc)
                            @php
                                $kode = $doc['kodeDokumen'] ?? '-';
                                $isLartas = !in_array($kode, ['380', '705', '740', '217']);
                                $nama = $dokumenRef[$kode] ?? ($doc['namaDokumen'] ?? 'Dokumen Pelengkap Ekspor');
                                $linkedItems = collect($barangList)->filter(function($b) use ($doc) {
                                    return collect($b['barangDokumen'] ?? [])->contains('seriDokumen', $doc['seriDokumen']);
                                });
                            @endphp
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                                <td class="py-3 px-3.5 text-center font-mono font-bold text-slate-700 dark:text-slate-300">#{{ $doc['seriDokumen'] ?? '-' }}</td>
                                <td class="py-3 px-3.5 font-mono">
                                    <span class="px-2 py-0.5 rounded font-mono font-bold text-[10px] {{ $isLartas ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200/80' : 'bg-black/5 dark:bg-white/10 text-slate-700 dark:text-slate-300' }}">
                                        {{ $kode }}
                                    </span>
                                </td>
                                <td class="py-3 px-3.5 font-semibold text-slate-800 dark:text-white">{{ $nama }}</td>
                                <td class="py-3 px-3.5 font-mono font-semibold text-slate-900 dark:text-white">{{ $doc['nomorDokumen'] ?? '-' }}</td>
                                <td class="py-3 px-3.5 font-mono text-slate-600 dark:text-slate-300 tabular-nums">{{ $doc['tanggalDokumen'] ?? '-' }}</td>
                                <td class="py-3 px-3.5">
                                    <span class="px-2.5 py-0.5 rounded-full text-[9px] font-semibold {{ $isLartas ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200/80' : 'bg-black/5 dark:bg-white/10 text-slate-600 dark:text-slate-400' }}">
                                        {{ $isLartas ? 'Perizinan Lartas' : 'Dokumen Utama' }}
                                    </span>
                                </td>
                                <td class="py-3 px-3.5 text-[10px] text-slate-500 dark:text-slate-400">
                                    @if($linkedItems->isNotEmpty())
                                        @foreach($linkedItems as $li)
                                            <div class="truncate max-w-[180px] font-medium text-slate-700 dark:text-slate-300">
                                                Pos {{ $li['posTarif'] ?? '-' }}: {{ $li['uraian'] ?? '-' }}
                                            </div>
                                        @endforeach
                                    @else
                                        <span class="text-slate-400 dark:text-slate-500 italic">Header Seluruh Kargo</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-6 text-center text-slate-400 dark:text-slate-500">Tidak ada data dokumen pelengkap di payload.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Raw JSON Inspector -->
        <details class="group bg-black/[0.02] dark:bg-white/[0.03] rounded-2xl p-4 border border-black/[0.06] dark:border-white/[0.06]">
            <summary class="cursor-pointer text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center justify-between">
                <span>Inspeksi Raw JSON Payload & Respon CEISA 4.0</span>
                <i data-lucide="chevron-down" class="w-4 h-4 text-[#0284c7] group-open:rotate-180 transition-transform"></i>
            </summary>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-[11px] font-mono">
                <div>
                    <span class="block text-slate-500 dark:text-slate-400 font-semibold mb-1">Payload Pengiriman (Request):</span>
                    <pre class="p-3.5 rounded-xl bg-slate-950 text-emerald-400 overflow-x-auto max-h-80">{{ json_encode($bc30->raw_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
                <div>
                    <span class="block text-slate-500 dark:text-slate-400 font-semibold mb-1">Respon Gateway DJBC (Response):</span>
                    <pre class="p-3.5 rounded-xl bg-slate-950 text-sky-300 overflow-x-auto max-h-80">{{ json_encode($bc30->last_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            </div>
        </details>
    </div>

    <!-- Audit Logs for this document -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-6 border border-black/[0.06] dark:border-white/[0.08] shadow-xs">
        <h2 class="text-sm font-bold text-slate-900 dark:text-white mb-3 flex items-center gap-2">
            <i data-lucide="scroll-text" class="w-4 h-4 text-[#0284c7]"></i>
            <span>Audit Log Interaksi API CEISA untuk Dokumen Ini</span>
        </h2>
        <div class="overflow-x-auto border border-black/[0.06] dark:border-white/[0.08] rounded-2xl">
            <table class="w-full text-left text-xs">
                <thead class="bg-black/[0.02] dark:bg-white/[0.02] text-slate-500 dark:text-slate-400 font-semibold uppercase text-[10px] tracking-wider border-b border-black/[0.06] dark:border-white/[0.06]">
                    <tr>
                        <th class="py-3 px-3.5">Waktu</th>
                        <th class="py-3 px-3.5">Method & Endpoint</th>
                        <th class="py-3 px-3.5">Status Code</th>
                        <th class="py-3 px-3.5">Durasi</th>
                        <th class="py-3 px-3.5">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.04]">
                    @forelse($bc30->logs as $log)
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                            <td class="py-3 px-3.5 text-slate-500 dark:text-slate-400 tabular-nums">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td class="py-3 px-3.5 font-mono">
                                <span class="font-bold text-[#0284c7]">{{ $log->method }}</span> {{ $log->endpoint }}
                            </td>
                            <td class="py-3 px-3.5 font-mono font-bold {{ $log->status_code === 200 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} tabular-nums">
                                {{ $log->status_code }}
                            </td>
                            <td class="py-3 px-3.5 font-mono text-slate-500 dark:text-slate-400 tabular-nums">{{ $log->duration_ms }} ms</td>
                            <td class="py-3 px-3.5">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $log->is_success ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/80' : 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border border-rose-200/80' }}">
                                    {{ $log->is_success ? 'BERHASIL' : 'GAGAL' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400 dark:text-slate-500">Belum ada panggilan API tercatat untuk nomor aju ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Upload Berkas Pelengkap Pasca-Tarik CEISA atau Kapan Saja (BC 3.0) -->
<div id="modal-upload-pelengkap-bc30" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="bg-white dark:bg-[#1C1C1E] w-full max-w-lg rounded-2xl border border-black/[0.08] dark:border-white/[0.1] shadow-2xl p-5 sm:p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center">
                    <i data-lucide="upload-cloud" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white">Unggah Berkas Fisik Pelengkap PEB</h3>
                    <p class="text-[11px] text-slate-500">Arsip digital ekspor &amp; kelengkapan booking shipping line</p>
                </div>
            </div>
            <button type="button" 
                    onclick="document.getElementById('modal-upload-pelengkap-bc30')?.classList.add('hidden')"
                    class="p-1 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-white cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="form-upload-pelengkap-bc30" action="{{ route('bc30.upload-document', $bc30->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3.5 text-xs">
            @csrf
            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Jenis Dokumen <span class="text-rose-500">*</span></label>
                <select name="kode_dokumen" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white">
                    <option value="380">380 - Commercial Invoice</option>
                    <option value="217">217 - Packing List</option>
                    <option value="705">705 - Bill of Lading (B/L) / Booking Confirmation</option>
                    <option value="861">861 - Certificate of Origin (COO / Form E/D/AK)</option>
                    <option value="430">430 - Polis Asuransi Kargo</option>
                    <option value="999">999 - Dokumen Pelengkap / Perijinan Lainnya</option>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nomor Dokumen</label>
                    <input type="text" name="nomor_dokumen" placeholder="Contoh: INV-EXP-2026-001" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Dokumen</label>
                    <input type="date" name="tanggal_dokumen" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-white dark:bg-[#2C2C2E] dark:text-white">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Pilih Berkas (PDF / Gambar) <span class="text-rose-500">*</span></label>
                <input type="file" name="file" required accept=".pdf,.png,.jpg,.jpeg,.webp" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-amber-500/10 file:text-amber-600 hover:file:bg-amber-500/20 cursor-pointer">
                <span class="text-[10px] text-slate-400 mt-1 block">Format didukung: PDF, PNG, JPG, JPEG (Maks. 10 MB). Berkas akan terintegrasi langsung ke panel preview split-screen.</span>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                <button type="button" 
                        onclick="document.getElementById('modal-upload-pelengkap-bc30')?.classList.add('hidden')"
                        class="px-4 py-2 rounded-xl border border-slate-200 dark:border-white/15 bg-slate-100 dark:bg-white/5 hover:bg-slate-200 text-slate-700 dark:text-slate-200 font-semibold cursor-pointer">
                    Batal
                </button>
                <button type="submit" 
                        class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-semibold inline-flex items-center gap-1.5 shadow-xs cursor-pointer">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan &amp; Unggah</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('form-upload-pelengkap-bc30')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = this;
    const submitBtn = form.querySelector('button[type="submit"]');
    const origBtnHtml = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div><span>Mengunggah...</span>';

    const formData = new FormData(form);
    try {
        const res = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        });
        const data = await res.json();
        if (res.ok && data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: data.message || 'Berkas fisik berhasil diunggah dan tersimpan.',
                confirmButtonColor: '#d97706'
            }).then(() => {
                window.location.reload();
            });
        } else {
            throw new Error(data.message || 'Gagal mengunggah berkas.');
        }
    } catch (err) {
        Swal.fire({
            icon: 'error',
            title: 'Gagal Unggah',
            text: err.message || 'Terjadi kesalahan saat mengunggah berkas.',
            confirmButtonColor: '#d97706'
        });
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = origBtnHtml;
    }
});
</script>
@endsection
