@extends('layouts.app')

@section('title', 'Detail Dokumen BC 3.0 - ' . $bc30->nomor_aju)

@section('content')
<div class="max-w-[1360px] mx-auto px-4 sm:px-6 space-y-6 pt-4 pb-28 lg:pb-12">
    <!-- Breadcrumb & Header Toolbar -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-5 sm:p-6 border border-black/[0.06] dark:border-white/[0.08] shadow-xs">
        <nav class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 font-medium mb-2" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors flex items-center gap-1">
                <i data-lucide="home" class="w-3.5 h-3.5"></i>
                <span>Dashboard</span>
            </a>
            <i data-lucide="chevron-right" class="w-3 h-3 text-slate-300 dark:text-slate-600"></i>
            <a href="{{ route('bc30.index') }}" class="hover:text-[#007AFF] transition-colors">BC 3.0 (Ekspor)</a>
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
                    Kantor Pabean Muat: <strong class="text-slate-700 dark:text-slate-300">{{ $bc30->kode_kantor }}</strong> ({{ $bc30->nama_kantor }}) &bull; Dibuat: <span class="tabular-nums">{{ $bc30->created_at->format('d/m/Y H:i') }}</span>
                </p>
            </div>

            <!-- Action Buttons Host-to-Host -->
            <div class="flex flex-wrap items-center gap-2">
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
                        <button type="submit" class="btn-apple-filled text-xs inline-flex items-center gap-1.5 px-4 py-2 cursor-pointer">
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

        <!-- Timeline Status Tracker Ekspor -->
        <div class="mt-6 pt-5 border-t border-black/[0.06] dark:border-white/[0.06]">
            <div class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">Siklus Pemrosesan Ekspor (PEB)</div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                <!-- 1. Draft -->
                <div class="p-3.5 rounded-2xl border {{ in_array($bc30->status, ['DRAFT', 'SUBMITTED', 'PROSES', 'NPE']) ? 'bg-[#007AFF]/10 border-[#007AFF]/20 text-[#007AFF] font-semibold' : 'bg-black/[0.02] dark:bg-white/[0.02] border-black/[0.06] dark:border-white/[0.06] text-slate-400' }}">
                    <div class="text-[10px] uppercase font-bold opacity-75">Tahap 1</div>
                    <div class="mt-1 font-medium">1. Draft PEB</div>
                </div>
                <!-- 2. Terkirim -->
                <div class="p-3.5 rounded-2xl border {{ in_array($bc30->status, ['SUBMITTED', 'PROSES', 'NPE']) ? 'bg-[#007AFF]/10 border-[#007AFF]/20 text-[#007AFF] font-semibold' : 'bg-black/[0.02] dark:bg-white/[0.02] border-black/[0.06] dark:border-white/[0.06] text-slate-400' }}">
                    <div class="text-[10px] uppercase font-bold opacity-75">Tahap 2</div>
                    <div class="mt-1 font-medium">2. Gateway CEISA 4.0</div>
                </div>
                <!-- 3. Nomor Pendaftaran -->
                <div class="p-3.5 rounded-2xl border {{ in_array($bc30->status, ['PROSES', 'NPE']) || $bc30->nomor_daftar ? 'bg-[#007AFF]/10 border-[#007AFF]/20 text-[#007AFF] font-semibold' : 'bg-black/[0.02] dark:bg-white/[0.02] border-black/[0.06] dark:border-white/[0.06] text-slate-400' }}">
                    <div class="text-[10px] uppercase font-bold opacity-75">Tahap 3</div>
                    <div class="mt-1 font-medium">3. Nomor Pendaftaran</div>
                    @if($bc30->nomor_daftar)
                        <div class="font-mono text-[10px] font-bold text-[#007AFF] mt-0.5 tabular-nums">{{ $bc30->nomor_daftar }}</div>
                    @endif
                </div>
                <!-- 4. NPE Final -->
                <div class="p-3.5 rounded-2xl border {{ $bc30->status === 'NPE' ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-700 dark:text-emerald-400 font-bold' : 'bg-black/[0.02] dark:bg-white/[0.02] border-black/[0.06] dark:border-white/[0.06] text-slate-400' }}">
                    <div class="text-[10px] uppercase font-bold opacity-75">Tahap 4</div>
                    <div class="mt-1 font-medium">4. NPE (Izin Muat)</div>
                    @if($bc30->nomor_sppb_npe)
                        <div class="font-mono text-[10px] text-emerald-600 mt-0.5">{{ $bc30->nomor_sppb_npe }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

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
                    <span class="font-bold text-[#007AFF]">{{ $bc30->bank_nama ?? '-' }}</span>
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
                                <td class="py-3 px-3.5 font-mono font-bold text-[#007AFF]">{{ $item['posTarif'] ?? '-' }}</td>
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
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[9px] font-semibold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20">
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
                    <i data-lucide="files" class="w-4 h-4 text-[#007AFF]"></i>
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
                <i data-lucide="chevron-down" class="w-4 h-4 text-[#007AFF] group-open:rotate-180 transition-transform"></i>
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
                    @forelse($bc30->logs as $log)
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
