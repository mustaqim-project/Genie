@extends('layouts.app')

@section('title', 'Detail Dokumen BC 2.0 - ' . $bc20->nomor_aju)

@section('content')
<div class="w-full space-y-6">
    <!-- Breadcrumb & Header Toolbar -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-5 sm:p-6 border border-black/[0.06] dark:border-white/[0.08] shadow-xs">
        <nav class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 font-medium mb-2" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors flex items-center gap-1">
                <i data-lucide="home" class="w-3.5 h-3.5"></i>
                <span>Dashboard</span>
            </a>
            <i data-lucide="chevron-right" class="w-3 h-3 text-slate-300 dark:text-slate-600"></i>
            <a href="{{ route('bc20.index') }}" class="hover:text-[#007AFF] transition-colors">BC 2.0 (Impor)</a>
            <i data-lucide="chevron-right" class="w-3 h-3 text-slate-300 dark:text-slate-600"></i>
            <span class="text-slate-800 dark:text-white font-mono font-semibold">{{ $bc20->nomor_aju }}</span>
        </nav>

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 pt-2">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-semibold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20">
                        BC 2.0 (PIB)
                    </span>
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-semibold {{ $bc20->status_badge_class }}">
                        {{ $bc20->status }}
                    </span>
                    @if($bc20->jalur)
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold font-mono {{ $bc20->jalur === 'HIJAU' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/80' : ($bc20->jalur === 'KUNING' ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200/80' : 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border border-rose-200/80') }}">
                            Jalur {{ $bc20->jalur }}
                        </span>
                    @endif
                </div>
                <h1 class="text-2xl font-bold font-mono text-slate-900 dark:text-white tracking-tight">{{ $bc20->nomor_aju }}</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Kantor Pabean: <strong class="text-slate-700 dark:text-slate-300">{{ $bc20->kode_kantor }}</strong> ({{ $bc20->nama_kantor }}) &bull; Dibuat: <span class="tabular-nums">{{ $bc20->created_at->format('d/m/Y H:i') }}</span>
                </p>
            </div>

            <!-- Action Buttons Host-to-Host -->
            <div class="flex flex-wrap items-center gap-2">
                <form action="{{ route('bc20.check-schema', $bc20->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-apple-gray text-xs inline-flex items-center gap-1.5 px-3.5 py-2">
                        <i data-lucide="check-square" class="w-3.5 h-3.5 text-purple-600"></i>
                        <span>Validasi Skema</span>
                    </button>
                </form>

                @if($bc20->status === 'DRAFT' || $bc20->status === 'REJECT')
                    <form action="{{ route('bc20.submit', $bc20->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-apple-filled text-xs inline-flex items-center gap-1.5 px-4 py-2 cursor-pointer">
                            <i data-lucide="send" class="w-3.5 h-3.5"></i>
                            <span>Kirim ke CEISA 4.0</span>
                        </button>
                    </form>
                @endif

                <form action="{{ route('bc20.check-status', $bc20->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-apple-gray text-xs inline-flex items-center gap-1.5 px-3.5 py-2">
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-slate-500"></i>
                        <span>Cek Status</span>
                    </button>
                </form>

                <form action="{{ route('bc20.fetch-response', $bc20->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-apple-tinted text-xs inline-flex items-center gap-1.5 px-3.5 py-2">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        <span>Tarik Respon (SPPB/Billing)</span>
                    </button>
                </form>
            </div>
        </div>

        @if($bc20->is_schema_valid)
            <div class="mt-4 p-3 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200/80 dark:border-emerald-800/40 text-emerald-800 dark:text-emerald-300 text-xs flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                    <span><strong>Pre-Flight Check Lolos:</strong> {{ $bc20->schema_validation_message }}</span>
                </div>
                <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-300 font-bold">READY TO SUBMIT</span>
            </div>
        @elseif($bc20->schema_validation_message)
            <div class="mt-4 p-3 rounded-2xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200/80 dark:border-rose-900/40 text-rose-800 dark:text-rose-300 text-xs flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i>
                <span><strong>Catatan Skema:</strong> {{ $bc20->schema_validation_message }}</span>
            </div>
        @endif

        <!-- Timeline Status Tracker -->
        <div class="mt-6 pt-5 border-t border-black/[0.06] dark:border-white/[0.06]">
            <div class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">Siklus Pemrosesan Kepabeanan</div>
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-xs">
                <!-- 1. Draft -->
                <div class="p-3.5 rounded-2xl border {{ in_array($bc20->status, ['DRAFT', 'SUBMITTED', 'PROSES', 'BILLING', 'SPPB']) ? 'bg-[#007AFF]/10 border-[#007AFF]/20 text-[#007AFF] font-semibold' : 'bg-black/[0.02] dark:bg-white/[0.02] border-black/[0.06] dark:border-white/[0.06] text-slate-400' }}">
                    <div class="text-[10px] uppercase font-bold opacity-75">Tahap 1</div>
                    <div class="mt-1 font-medium">1. Draft Dibuat</div>
                </div>
                <!-- 2. Terkirim -->
                <div class="p-3.5 rounded-2xl border {{ in_array($bc20->status, ['SUBMITTED', 'PROSES', 'BILLING', 'SPPB']) ? 'bg-[#007AFF]/10 border-[#007AFF]/20 text-[#007AFF] font-semibold' : 'bg-black/[0.02] dark:bg-white/[0.02] border-black/[0.06] dark:border-white/[0.06] text-slate-400' }}">
                    <div class="text-[10px] uppercase font-bold opacity-75">Tahap 2</div>
                    <div class="mt-1 font-medium">2. Gateway CEISA</div>
                </div>
                <!-- 3. Validasi & Penomoran -->
                <div class="p-3.5 rounded-2xl border {{ in_array($bc20->status, ['PROSES', 'BILLING', 'SPPB']) || $bc20->nomor_daftar ? 'bg-[#007AFF]/10 border-[#007AFF]/20 text-[#007AFF] font-semibold' : 'bg-black/[0.02] dark:bg-white/[0.02] border-black/[0.06] dark:border-white/[0.06] text-slate-400' }}">
                    <div class="text-[10px] uppercase font-bold opacity-75">Tahap 3</div>
                    <div class="mt-1 font-medium">3. Nomor Pendaftaran</div>
                    @if($bc20->nomor_daftar)
                        <div class="font-mono text-[10px] font-bold text-[#007AFF] mt-0.5 tabular-nums">{{ $bc20->nomor_daftar }}</div>
                    @endif
                </div>
                <!-- 4. Billing MPN G3 -->
                <div class="p-3.5 rounded-2xl border {{ in_array($bc20->status, ['BILLING', 'SPPB']) || $bc20->kode_billing ? 'bg-amber-500/10 border-amber-500/20 text-amber-700 dark:text-amber-400 font-semibold' : 'bg-black/[0.02] dark:bg-white/[0.02] border-black/[0.06] dark:border-white/[0.06] text-slate-400' }}">
                    <div class="text-[10px] uppercase font-bold opacity-75">Tahap 4</div>
                    <div class="mt-1 font-medium">4. Billing Pembayaran</div>
                    @if($bc20->kode_billing)
                        <div class="font-mono text-[10px] font-bold text-amber-600 mt-0.5 tabular-nums">{{ $bc20->kode_billing }}</div>
                    @endif
                </div>
                <!-- 5. SPPB Final -->
                <div class="p-3.5 rounded-2xl border {{ $bc20->status === 'SPPB' ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-700 dark:text-emerald-400 font-bold' : 'bg-black/[0.02] dark:bg-white/[0.02] border-black/[0.06] dark:border-white/[0.06] text-slate-400' }}">
                    <div class="text-[10px] uppercase font-bold opacity-75">Tahap 5</div>
                    <div class="mt-1 font-medium">5. SPPB (Selesai)</div>
                    @if($bc20->nomor_sppb_npe)
                        <div class="font-mono text-[10px] text-emerald-600 mt-0.5">{{ $bc20->nomor_sppb_npe }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Billing & SPPB Cards if available -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        @if($bc20->kode_billing)
            <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-2xl p-6 text-white shadow-lg shadow-amber-500/20">
                <div class="flex items-center justify-between mb-4">
                    <span class="px-2.5 py-0.5 rounded-full bg-white/20 text-white font-bold text-xs uppercase">
                        Kode Billing MPN G3
                    </span>
                    <span class="text-xs font-medium text-amber-100 tabular-nums">Batas: {{ $bc20->tgl_jatuh_tempo_billing?->format('d/m/Y H:i') ?? '3 Hari' }}</span>
                </div>
                <div class="font-mono text-3xl font-black tracking-wider mb-2 tabular-nums">
                    {{ $bc20->kode_billing }}
                </div>
                <div class="text-sm font-medium text-amber-100 mb-4">
                    Total Tagihan: <strong class="text-white text-lg tabular-nums">Rp {{ number_format($bc20->total_billing ?? 0, 0, ',', '.') }}</strong>
                </div>
                <p class="text-xs text-amber-100/80">
                    Dapat dibayarkan melalui Teller, ATM, Internet Banking, atau Livin/BCA/BNI/Mandiri via menu Penerimaan Negara / MPN G3.
                </p>
            </div>
        @endif

        @if($bc20->nomor_sppb_npe)
            <div class="bg-gradient-to-br from-emerald-600 to-emerald-700 rounded-2xl p-6 text-white shadow-lg shadow-emerald-600/20">
                <div class="flex items-center justify-between mb-4">
                    <span class="px-2.5 py-0.5 rounded-full bg-white/20 text-white font-bold text-xs uppercase">
                        Respon Resmi: SPPB TERBIT
                    </span>
                    <span class="text-xs font-medium text-emerald-100 tabular-nums">{{ $bc20->tanggal_sppb_npe?->format('d/m/Y H:i') }}</span>
                </div>
                <div class="font-mono text-2xl font-black tracking-wider mb-2">
                    {{ $bc20->nomor_sppb_npe }}
                </div>
                <p class="text-xs text-emerald-100 mb-4">
                    Surat Persetujuan Pengeluaran Barang sah digunakan sebagai dokumen legalitas pengeluaran kargo dari Tempat Penimbunan Sementara (TPS) Pelabuhan.
                </p>
                <div class="inline-flex items-center gap-1.5 text-xs font-bold text-white bg-white/10 px-3 py-1.5 rounded-lg">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Status Pabean: Selesai / Siap Gate Out</span>
                </div>
            </div>
        @endif
    </div>

    <!-- Document Breakdown Details -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-5 sm:p-6 border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-6">
        <div>
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-4">Ringkasan Nilai Pabean & Estimasi Pungutan Negara</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs mb-4">
                <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.06]">
                    <span class="text-slate-400 dark:text-slate-500 block font-medium">Valuasi CIF ({{ $bc20->kode_valuta }})</span>
                    <span class="font-mono font-bold text-slate-800 dark:text-white text-sm tabular-nums">{{ $bc20->kode_valuta }} {{ number_format($bc20->total_nilai, 2) }}</span>
                </div>
                <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.06]">
                    <span class="text-slate-400 dark:text-slate-500 block font-medium">Kurs KMK (NDBM)</span>
                    <span class="font-mono font-bold text-slate-800 dark:text-white text-sm tabular-nums">Rp {{ number_format($bc20->ndbm_kurs, 2) }}</span>
                </div>
                <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.06]">
                    <span class="text-slate-400 dark:text-slate-500 block font-medium">Nilai Pabean (IDR)</span>
                    <span class="font-mono font-bold text-slate-800 dark:text-white text-sm tabular-nums">Rp {{ number_format($bc20->total_nilai_idr, 0, ',', '.') }}</span>
                </div>
                <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.06]">
                    <span class="text-slate-400 dark:text-slate-500 block font-medium">Total Bruto / Netto</span>
                    <span class="font-mono font-bold text-slate-800 dark:text-white text-sm tabular-nums">{{ number_format($bc20->total_bruto, 2) }} / {{ number_format($bc20->total_netto, 2) }} KG</span>
                </div>
            </div>
        </div>

        <!-- Manifes BC 1.1 & TPS -->
        <div class="p-4 rounded-2xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.06]">
            <h3 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Manifes Kedatangan BC 1.1 & Tempat Penimbunan (TPS)</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                <div>
                    <span class="text-slate-400 dark:text-slate-500 block font-medium">Nomor / Tanggal BC 1.1</span>
                    <span class="font-mono font-bold text-[#007AFF]">{{ $bc20->nomor_bc11 ?? '-' }} ({{ $bc20->tanggal_bc11?->format('d/m/Y') ?? '-' }})</span>
                </div>
                <div>
                    <span class="text-slate-400 dark:text-slate-500 block font-medium">Pos / Subpos BC 1.1</span>
                    <span class="font-mono font-semibold text-slate-800 dark:text-white">Pos {{ $bc20->pos_bc11 ?? '-' }} / Subpos {{ $bc20->subpos_bc11 ?? '0000' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 dark:text-slate-500 block font-medium">Kode TPS</span>
                    <span class="font-mono font-semibold text-slate-800 dark:text-white">{{ $bc20->kode_tps ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 dark:text-slate-500 block font-medium">Perkiraan Tiba (ETA)</span>
                    <span class="font-mono font-semibold text-slate-800 dark:text-white">{{ $bc20->tanggal_tiba?->format('d/m/Y') ?? '-' }}</span>
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
                '860' => 'Persetujuan Impor (PI Kemendag)',
                '810' => 'Sertifikat Standar / SNI Wajib',
                '853' => 'Sertifikat Standar SDPPI / POSTEL',
                '852' => 'Surat Keterangan Impor (SKI BPOM)',
                '831' => 'Sertifikat Karantina / Fitosanitari (Barantin)',
                '880' => 'Dokumen V-Legal (KLHK)',
                '872' => 'Eksportir Terdaftar (ET) Sawit / CPO',
                '911' => 'Surat Rekomendasi Teknis Kementerian',
            ];
            $barangList = $bc20->raw_payload['barang'] ?? [];
            $dokumenList = $bc20->raw_payload['dokumen'] ?? [];
        @endphp
        <div>
            <h3 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Rincian Barang yang Diberitahukan</h3>
            <div class="overflow-x-auto border border-black/[0.06] dark:border-white/[0.08] rounded-2xl">
                <table class="w-full text-left text-xs">
                    <thead class="bg-black/[0.02] dark:bg-white/[0.02] text-slate-500 dark:text-slate-400 font-semibold uppercase text-[10px] tracking-wider border-b border-black/[0.06] dark:border-white/[0.06]">
                        <tr>
                            <th class="py-3 px-3.5">No</th>
                            <th class="py-3 px-3.5">HS Code (BTKI)</th>
                            <th class="py-3 px-3.5">Uraian & Merk</th>
                            <th class="py-3 px-3.5">Jumlah Satuan</th>
                            <th class="py-3 px-3.5">CIF ({{ $bc20->kode_valuta }})</th>
                            <th class="py-3 px-3.5">Nilai Pabean (IDR)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.04]">
                        @forelse($barangList as $index => $item)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                                <td class="py-3 px-3.5 font-mono text-slate-400">{{ $index + 1 }}</td>
                                <td class="py-3 px-3.5 font-mono font-bold text-[#007AFF]">{{ $item['posTarif'] ?? '-' }}</td>
                                <td class="py-3 px-3.5">
                                    <div class="font-semibold text-slate-800 dark:text-white">{{ $item['uraian'] ?? '-' }}</div>
                                    <div class="text-[10px] text-slate-400 dark:text-slate-500">Merk: {{ $item['merk'] ?? '-' }} | Tipe: {{ $item['tipe'] ?? '-' }}</div>
                                    @if(!empty($item['barangDokumen']))
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            @foreach($item['barangDokumen'] as $bd)
                                                @php
                                                    $matchedDoc = collect($dokumenList)->firstWhere('seriDokumen', $bd['seriDokumen'] ?? null);
                                                    $docCode = $matchedDoc['kodeDokumen'] ?? '';
                                                    $docName = $dokumenRef[$docCode] ?? ($matchedDoc['namaDokumen'] ?? 'Izin Pabean');
                                                    $docNo = $matchedDoc['nomorDokumen'] ?? '-';
                                                @endphp
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[9px] font-semibold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20">
                                                    <span>📑 Seri #{{ $bd['seriDokumen'] }} [{{ $docCode }}]</span>
                                                    <span class="font-mono">{{ $docNo }}</span>
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3 px-3.5 font-mono tabular-nums text-slate-700 dark:text-slate-300">{{ $item['jumlahSatuan'] ?? 0 }} {{ $item['kodeSatuanBarang'] ?? '' }}</td>
                                <td class="py-3 px-3.5 font-mono tabular-nums text-slate-700 dark:text-slate-300">{{ $bc20->kode_valuta }} {{ number_format($item['cif'] ?? 0, 2) }}</td>
                                <td class="py-3 px-3.5 font-mono font-bold text-slate-900 dark:text-white tabular-nums">Rp {{ number_format($item['cifRupiah'] ?? 0, 0, ',', '.') }}</td>
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
                    <i data-lucide="files" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>Lampiran Dokumen Pelengkap Pabean & Perizinan Lartas (CEISA 4.0)</span>
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
                                $nama = $dokumenRef[$kode] ?? ($doc['namaDokumen'] ?? 'Dokumen Pelengkap Pabean');
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
                <i data-lucide="chevron-down" class="w-4 h-4 text-[#007AFF] group-open:rotate-180 transition-transform"></i>
            </summary>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-[11px] font-mono">
                <div>
                    <span class="block text-slate-500 dark:text-slate-400 font-semibold mb-1">Payload Pengiriman (Request):</span>
                    <pre class="p-3.5 rounded-xl bg-slate-950 text-emerald-400 overflow-x-auto max-h-80">{{ json_encode($bc20->raw_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
                <div>
                    <span class="block text-slate-500 dark:text-slate-400 font-semibold mb-1">Respon Gateway DJBC (Response):</span>
                    <pre class="p-3.5 rounded-xl bg-slate-950 text-sky-300 overflow-x-auto max-h-80">{{ json_encode($bc20->last_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            </div>
        </details>
    </div>

    <!-- Audit Logs for this document -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-6 border border-black/[0.06] dark:border-white/[0.08] shadow-xs">
        <h2 class="text-sm font-bold text-slate-900 dark:text-white mb-3 flex items-center gap-2">
            <i data-lucide="scroll-text" class="w-4 h-4 text-[#007AFF]"></i>
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
                    @forelse($bc20->logs as $log)
                        <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                            <td class="py-3 px-3.5 text-slate-500 dark:text-slate-400 tabular-nums">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td class="py-3 px-3.5 font-mono">
                                <span class="font-bold text-[#007AFF]">{{ $log->method }}</span> {{ $log->endpoint }}
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
@endsection
