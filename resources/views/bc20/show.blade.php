@extends('layouts.app')

@section('title', 'Detail Dokumen BC 2.0 - ' . $bc20->nomor_aju)

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 pt-6 pb-12">
    <!-- Breadcrumb -->
    <div class="text-xs text-slate-500 font-medium mb-3">
        <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a> / 
        <a href="{{ route('bc20.index') }}" class="hover:underline">BC 2.0 (Impor)</a> / 
        <span class="text-slate-800 font-mono">{{ $bc20->nomor_aju }}</span>
    </div>

    <!-- Header Card -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pb-6 border-b border-slate-100">
            <div>
                <div class="flex items-center space-x-3 mb-2">
                    <span class="px-2.5 py-0.5 rounded text-xs font-bold bg-blue-100 text-blue-800">
                        BC 2.0 (PIB)
                    </span>
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-bold {{ $bc20->status_badge_class }}">
                        {{ $bc20->status }}
                    </span>
                    @if($bc20->jalur)
                        <span class="px-2.5 py-0.5 rounded text-xs font-bold {{ $bc20->jalur === 'HIJAU' ? 'bg-emerald-100 text-emerald-800' : ($bc20->jalur === 'KUNING' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                            Jalur {{ $bc20->jalur }}
                        </span>
                    @endif
                </div>
                <h1 class="text-2xl font-bold font-mono text-slate-900 tracking-tight">{{ $bc20->nomor_aju }}</h1>
                <p class="text-xs text-slate-500 mt-1">
                    Kantor Pabean: <strong class="text-slate-700">{{ $bc20->kode_kantor }}</strong> ({{ $bc20->nama_kantor }}) &bull; Dibuat: {{ $bc20->created_at->format('d/m/Y H:i') }}
                </p>
            </div>

            <!-- Action Buttons Host-to-Host -->
            <div class="flex flex-wrap items-center gap-2">
                <form action="{{ route('bc20.check-schema', $bc20->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center space-x-1.5 px-3.5 py-2.5 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 font-semibold text-xs transition-colors">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Validasi Skema (Dry-Run)</span>
                    </button>
                </form>

                @if($bc20->status === 'DRAFT' || $bc20->status === 'REJECT')
                    <form action="{{ route('bc20.submit', $bc20->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-md shadow-brand-600/30 transition-all hover:-translate-y-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                            <span>Kirim ke CEISA 4.0</span>
                        </button>
                    </form>
                @endif

                <form action="{{ route('bc20.check-status', $bc20->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center space-x-1.5 px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span>Cek Status</span>
                    </button>
                </form>

                <form action="{{ route('bc20.fetch-response', $bc20->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center space-x-1.5 px-3.5 py-2.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 font-semibold text-xs transition-colors">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Tarik Respon (SPPB/Billing)</span>
                    </button>
                </form>
            </div>
        </div>

        @if($bc20->is_schema_valid)
            <div class="mt-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                    <span><strong>Pre-Flight Check Lolos:</strong> {{ $bc20->schema_validation_message }}</span>
                </div>
                <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold">READY TO SUBMIT</span>
            </div>
        @elseif($bc20->schema_validation_message)
            <div class="mt-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center space-x-2">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                <span><strong>Catatan Skema:</strong> {{ $bc20->schema_validation_message }}</span>
            </div>
        @endif

        <!-- Timeline Status Tracker -->
        <div class="mt-6">
            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">Siklus Pemrosesan Kepabeanan</div>
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-xs">
                <!-- 1. Draft -->
                <div class="p-3 rounded-xl border {{ in_array($bc20->status, ['DRAFT', 'SUBMITTED', 'PROSES', 'BILLING', 'SPPB']) ? 'bg-blue-50/70 border-blue-200 text-blue-900 font-semibold' : 'bg-slate-50 border-slate-200 text-slate-400' }}">
                    <div class="text-[10px] uppercase font-bold text-slate-400">Tahap 1</div>
                    <div class="mt-1">1. Draft Dibuat</div>
                </div>
                <!-- 2. Terkirim -->
                <div class="p-3 rounded-xl border {{ in_array($bc20->status, ['SUBMITTED', 'PROSES', 'BILLING', 'SPPB']) ? 'bg-blue-50/70 border-blue-200 text-blue-900 font-semibold' : 'bg-slate-50 border-slate-200 text-slate-400' }}">
                    <div class="text-[10px] uppercase font-bold text-slate-400">Tahap 2</div>
                    <div class="mt-1">2. Gateway CEISA</div>
                </div>
                <!-- 3. Validasi & Penomoran -->
                <div class="p-3 rounded-xl border {{ in_array($bc20->status, ['PROSES', 'BILLING', 'SPPB']) || $bc20->nomor_daftar ? 'bg-blue-50/70 border-blue-200 text-blue-900 font-semibold' : 'bg-slate-50 border-slate-200 text-slate-400' }}">
                    <div class="text-[10px] uppercase font-bold text-slate-400">Tahap 3</div>
                    <div class="mt-1">3. Nomor Pendaftaran</div>
                    @if($bc20->nomor_daftar)
                        <div class="font-mono text-[10px] text-blue-700 mt-0.5">{{ $bc20->nomor_daftar }}</div>
                    @endif
                </div>
                <!-- 4. Billing MPN G3 -->
                <div class="p-3 rounded-xl border {{ in_array($bc20->status, ['BILLING', 'SPPB']) || $bc20->kode_billing ? 'bg-amber-50/70 border-amber-200 text-amber-900 font-semibold' : 'bg-slate-50 border-slate-200 text-slate-400' }}">
                    <div class="text-[10px] uppercase font-bold text-slate-400">Tahap 4</div>
                    <div class="mt-1">4. Billing Pembayaran</div>
                    @if($bc20->kode_billing)
                        <div class="font-mono text-[10px] text-amber-700 mt-0.5">{{ $bc20->kode_billing }}</div>
                    @endif
                </div>
                <!-- 5. SPPB Final -->
                <div class="p-3 rounded-xl border {{ $bc20->status === 'SPPB' ? 'bg-emerald-50/80 border-emerald-300 text-emerald-900 font-bold' : 'bg-slate-50 border-slate-200 text-slate-400' }}">
                    <div class="text-[10px] uppercase font-bold text-slate-400">Tahap 5</div>
                    <div class="mt-1">5. SPPB (Selesai)</div>
                    @if($bc20->nomor_sppb_npe)
                        <div class="font-mono text-[10px] text-emerald-700 mt-0.5">{{ $bc20->nomor_sppb_npe }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Billing & SPPB Cards if available -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        @if($bc20->kode_billing)
            <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-2xl p-6 text-white shadow-lg shadow-amber-500/20">
                <div class="flex items-center justify-between mb-4">
                    <span class="px-2.5 py-0.5 rounded-full bg-white/20 text-white font-bold text-xs uppercase">
                        Kode Billing MPN G3
                    </span>
                    <span class="text-xs font-medium text-amber-100">Batas: {{ $bc20->tgl_jatuh_tempo_billing?->format('d/m/Y H:i') ?? '3 Hari' }}</span>
                </div>
                <div class="font-mono text-3xl font-black tracking-wider mb-2">
                    {{ $bc20->kode_billing }}
                </div>
                <div class="text-sm font-medium text-amber-100 mb-4">
                    Total Tagihan: <strong class="text-white text-lg">Rp {{ number_format($bc20->total_billing ?? 0, 0, ',', '.') }}</strong>
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
                    <span class="text-xs font-medium text-emerald-100">{{ $bc20->tanggal_sppb_npe?->format('d/m/Y H:i') }}</span>
                </div>
                <div class="font-mono text-2xl font-black tracking-wider mb-2">
                    {{ $bc20->nomor_sppb_npe }}
                </div>
                <p class="text-xs text-emerald-100 mb-4">
                    Surat Persetujuan Pengeluaran Barang sah digunakan sebagai dokumen legalitas pengeluaran kargo dari Tempat Penimbunan Sementara (TPS) Pelabuhan.
                </p>
                <div class="inline-flex items-center space-x-2 text-xs font-bold text-white bg-white/10 px-3 py-1.5 rounded-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Status Pabean: Selesai / Siap Gate Out</span>
                </div>
            </div>
        @endif
    </div>

    <!-- Document Breakdown Details -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm mb-6">
        <h2 class="text-base font-bold text-slate-900 mb-4">Ringkasan Nilai Pabean & Estimasi Pungutan Negara</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs mb-6">
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                <span class="text-slate-400 block font-medium">Valuasi CIF (USD)</span>
                <span class="font-mono font-bold text-slate-800 text-sm">USD {{ number_format($bc20->total_nilai, 2) }}</span>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                <span class="text-slate-400 block font-medium">Kurs KMK (NDBM)</span>
                <span class="font-mono font-bold text-slate-800 text-sm">Rp {{ number_format($bc20->ndbm_kurs, 2) }}</span>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                <span class="text-slate-400 block font-medium">Nilai Pabean (IDR)</span>
                <span class="font-mono font-bold text-slate-800 text-sm">Rp {{ number_format($bc20->total_nilai_idr, 0, ',', '.') }}</span>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                <span class="text-slate-400 block font-medium">Total Bruto / Netto</span>
                <span class="font-mono font-bold text-slate-800 text-sm">{{ number_format($bc20->total_bruto, 2) }} / {{ number_format($bc20->total_netto, 2) }} KG</span>
            </div>
        </div>

        <!-- Manifes BC 1.1 & TPS -->
        <div class="mb-6 p-4 rounded-xl bg-slate-50/70 border border-slate-200">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Manifes Kedatangan BC 1.1 & Tempat Penimbunan (TPS)</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                <div>
                    <span class="text-slate-400 block font-medium">Nomor / Tanggal BC 1.1</span>
                    <span class="font-mono font-bold text-indigo-700">{{ $bc20->nomor_bc11 ?? '-' }} ({{ $bc20->tanggal_bc11?->format('d/m/Y') ?? '-' }})</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Pos / Subpos BC 1.1</span>
                    <span class="font-mono font-bold text-slate-800">Pos {{ $bc20->pos_bc11 ?? '-' }} / Subpos {{ $bc20->subpos_bc11 ?? '0000' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Kode TPS</span>
                    <span class="font-mono font-bold text-slate-800">{{ $bc20->kode_tps ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">Perkiraan Tiba (ETA)</span>
                    <span class="font-mono font-bold text-slate-800">{{ $bc20->tanggal_tiba?->format('d/m/Y') ?? '-' }}</span>
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
        <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Rincian Barang yang Diberitahukan</h3>
        <div class="overflow-x-auto border border-slate-100 rounded-xl mb-6">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="py-2.5 px-3">No</th>
                        <th class="py-2.5 px-3">HS Code (BTKI)</th>
                        <th class="py-2.5 px-3">Uraian & Merk</th>
                        <th class="py-2.5 px-3">Jumlah Satuan</th>
                        <th class="py-2.5 px-3">CIF (USD)</th>
                        <th class="py-2.5 px-3">Nilai Pabean (IDR)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($barangList as $index => $item)
                        <tr>
                            <td class="py-2.5 px-3">{{ $index + 1 }}</td>
                            <td class="py-2.5 px-3 font-mono font-bold text-brand-600">{{ $item['posTarif'] ?? '-' }}</td>
                            <td class="py-2.5 px-3">
                                <div class="font-semibold text-slate-800">{{ $item['uraian'] ?? '-' }}</div>
                                <div class="text-[10px] text-slate-400">Merk: {{ $item['merk'] ?? '-' }} | Tipe: {{ $item['tipe'] ?? '-' }}</div>
                                @if(!empty($item['barangDokumen']))
                                    <div class="mt-1 flex flex-wrap gap-1">
                                        @foreach($item['barangDokumen'] as $bd)
                                            @php
                                                $matchedDoc = collect($dokumenList)->firstWhere('seriDokumen', $bd['seriDokumen'] ?? null);
                                                $docCode = $matchedDoc['kodeDokumen'] ?? '';
                                                $docName = $dokumenRef[$docCode] ?? ($matchedDoc['namaDokumen'] ?? 'Izin Pabean');
                                                $docNo = $matchedDoc['nomorDokumen'] ?? '-';
                                            @endphp
                                            <span class="inline-flex items-center space-x-1 px-1.5 py-0.5 rounded text-[9px] font-semibold bg-indigo-50 text-indigo-800 border border-indigo-200">
                                                <span>📑 Seri #{{ $bd['seriDokumen'] }} [{{ $docCode }}]</span>
                                                <span class="font-mono">{{ $docNo }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 font-mono">{{ $item['jumlahSatuan'] ?? 0 }} {{ $item['kodeSatuanBarang'] ?? '' }}</td>
                            <td class="py-2.5 px-3 font-mono">USD {{ number_format($item['cif'] ?? 0, 2) }}</td>
                            <td class="py-2.5 px-3 font-mono font-bold text-slate-800">Rp {{ number_format($item['cifRupiah'] ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-4 text-center text-slate-400">Data barang tidak tersedia di payload.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Lampiran Dokumen Pelengkap & Izin Lartas Section -->
        <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2 flex items-center justify-between">
            <span class="flex items-center space-x-2">
                <span>📑</span>
                <span>Lampiran Dokumen Pelengkap Pabean & Perizinan Lartas (CEISA 4.0)</span>
            </span>
            <span class="text-slate-400 font-mono text-[10px]">{{ count($dokumenList) }} Dokumen Terlampir</span>
        </h3>
        <div class="overflow-x-auto border border-slate-100 rounded-xl mb-6">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="py-2.5 px-3 text-center">Seri</th>
                        <th class="py-2.5 px-3">Kode Dok.</th>
                        <th class="py-2.5 px-3">Nama / Jenis Dokumen</th>
                        <th class="py-2.5 px-3">Nomor Dokumen</th>
                        <th class="py-2.5 px-3">Tanggal Dokumen</th>
                        <th class="py-2.5 px-3">Kategori</th>
                        <th class="py-2.5 px-3">Terkait Barang</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($dokumenList as $doc)
                        @php
                            $kode = $doc['kodeDokumen'] ?? '-';
                            $isLartas = !in_array($kode, ['380', '705', '740', '217']);
                            $nama = $dokumenRef[$kode] ?? ($doc['namaDokumen'] ?? 'Dokumen Pelengkap Pabean');
                            $linkedItems = collect($barangList)->filter(function($b) use ($doc) {
                                return collect($b['barangDokumen'] ?? [])->contains('seriDokumen', $doc['seriDokumen']);
                            });
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-2.5 px-3 text-center font-mono font-bold text-slate-700">#{{ $doc['seriDokumen'] ?? '-' }}</td>
                            <td class="py-2.5 px-3 font-mono">
                                <span class="px-2 py-0.5 rounded font-mono font-extrabold text-[10px] {{ $isLartas ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-slate-100 text-slate-800' }}">
                                    {{ $kode }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 font-semibold text-slate-800">{{ $nama }}</td>
                            <td class="py-2.5 px-3 font-mono font-bold text-slate-900">{{ $doc['nomorDokumen'] ?? '-' }}</td>
                            <td class="py-2.5 px-3 font-mono text-slate-600">{{ $doc['tanggalDokumen'] ?? '-' }}</td>
                            <td class="py-2.5 px-3">
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-bold {{ $isLartas ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $isLartas ? 'Perizinan Lartas' : 'Dokumen Utama' }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-[10px] text-slate-500">
                                @if($linkedItems->isNotEmpty())
                                    @foreach($linkedItems as $li)
                                        <div class="truncate max-w-[180px] font-medium text-slate-700">
                                            Pos {{ $li['posTarif'] ?? '-' }}: {{ $li['uraian'] ?? '-' }}
                                        </div>
                                    @endforeach
                                @else
                                    <span class="text-slate-400 italic">Header Seluruh Kargo</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-4 text-center text-slate-400">Tidak ada data dokumen pelengkap di payload.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Raw JSON Inspector -->
        <details class="group bg-slate-50 rounded-xl p-4 border border-slate-200">
            <summary class="cursor-pointer text-xs font-bold text-slate-700 flex items-center justify-between">
                <span>Inspeksi Raw JSON Payload & Respon CEISA 4.0</span>
                <span class="text-brand-600 group-open:rotate-180 transition-transform">&darr;</span>
            </summary>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-[11px] font-mono">
                <div>
                    <span class="block text-slate-500 font-bold mb-1">Payload Pengiriman (Request):</span>
                    <pre class="p-3 rounded-lg bg-slate-900 text-emerald-400 overflow-x-auto max-h-80">{{ json_encode($bc20->raw_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
                <div>
                    <span class="block text-slate-500 font-bold mb-1">Respon Gateway DJBC (Response):</span>
                    <pre class="p-3 rounded-lg bg-slate-900 text-blue-300 overflow-x-auto max-h-80">{{ json_encode($bc20->last_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            </div>
        </details>
    </div>

    <!-- Audit Logs for this document -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
        <h2 class="text-sm font-bold text-slate-900 mb-3">Audit Log Interaksi API CEISA untuk Dokumen Ini</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="py-2.5 px-3">Waktu</th>
                        <th class="py-2.5 px-3">Method & Endpoint</th>
                        <th class="py-2.5 px-3">Status Code</th>
                        <th class="py-2.5 px-3">Durasi</th>
                        <th class="py-2.5 px-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($bc20->logs as $log)
                        <tr>
                            <td class="py-2 px-3 text-slate-500">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td class="py-2 px-3 font-mono">
                                <span class="font-bold text-brand-600">{{ $log->method }}</span> {{ $log->endpoint }}
                            </td>
                            <td class="py-2 px-3 font-mono font-bold {{ $log->status_code === 200 ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $log->status_code }}
                            </td>
                            <td class="py-2 px-3 font-mono text-slate-500">{{ $log->duration_ms }} ms</td>
                            <td class="py-2 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $log->is_success ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                    {{ $log->is_success ? 'BERHASIL' : 'GAGAL' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-4 text-center text-slate-400">Belum ada panggilan API tercatat untuk nomor aju ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
