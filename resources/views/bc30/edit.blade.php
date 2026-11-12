@extends('layouts.app')

@section('title', 'Edit Dokumen BC 3.0 (PEB) - ' . $bc30->nomor_aju)

@section('content')
<div class="max-w-[1360px] mx-auto px-4 sm:px-6 space-y-6 pt-4 pb-28 lg:pb-12" x-data="bc30EditManager()">
    <!-- Breadcrumb -->
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Tim Document', 'url' => route('core.document.index')],
        ['label' => 'BC 3.0 (Ekspor)', 'url' => route('bc30.index')],
        ['label' => 'Detail Dokumen', 'url' => route('bc30.show', $bc30->id)],
        ['label' => 'Edit Data']
    ]" />

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-2">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-lg text-xs font-semibold bg-[#34C759]/10 text-[#34C759] border border-[#34C759]/20">
                    BC 3.0 (PEB)
                </span>
                <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-semibold {{ $bc30->status_badge_class }}">
                    Status: {{ $bc30->status }}
                </span>
            </div>
            <h1 class="text-2xl font-bold font-apple-display text-slate-900 dark:text-white tracking-tight">
                {{ $isDraft ? 'Edit Draf Dokumen Pabean BC 3.0 (PEB)' : 'Revisi Data Dokumen BC 3.0 (Pasca-Kirim CEISA)' }}
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 font-mono">
                Nomor Aju: <strong>{{ $bc30->nomor_aju }}</strong> &bull; Dibuat: {{ $bc30->created_at->format('d/m/Y H:i') }}
            </p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('bc30.show', $bc30->id) }}" class="btn-apple-gray text-xs px-3.5 py-2 inline-flex items-center gap-1.5">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                <span>Kembali ke Detail</span>
            </a>
        </div>
    </div>

    <!-- Banner Info Role Hak Akses -->
    @if($isDraft)
        <div class="p-4 rounded-2xl bg-emerald-50/80 dark:bg-emerald-950/30 border border-emerald-200/80 dark:border-emerald-900/40 text-emerald-900 dark:text-emerald-300 text-xs flex items-start gap-3">
            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5"></i>
            <div>
                <strong class="font-bold">Mode Edit Draft Aktif (Izin: document.edit)</strong>
                <p class="text-[11px] text-emerald-700 dark:text-emerald-400 mt-0.5 leading-relaxed">
                    Dokumen ini berstatus DRAFT. Anda dapat menyesuaikan data sarana pengangkut, buyer luar negeri, nilai ekspor FOB, dan rincian pos tarif HS Code sebelum divalidasi dan dikirim ke server CEISA 4.0 DJBC.
                </p>
            </div>
        </div>
    @else
        <div class="p-4 rounded-2xl bg-amber-50/90 dark:bg-amber-950/40 border border-amber-300/80 dark:border-amber-800/60 text-amber-950 dark:text-amber-200 text-xs flex items-start gap-3 shadow-xs">
            <i data-lucide="shield-alert" class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5"></i>
            <div>
                <strong class="font-bold">Mode Revisi Pasca-Kirim CEISA (Izin Khusus: document.edit_after_ceisa)</strong>
                <p class="text-[11px] text-amber-800 dark:text-amber-300 mt-0.5 leading-relaxed">
                    Dokumen ini telah diajukan ke CEISA dengan status <strong>{{ $bc30->status }}</strong>. Melalui hak akses supervisor/khusus ini, Anda diizinkan melakukan pembaruan data PEB yang nantinya dicatat secara otomatis dalam audit trail dan mereset pre-flight check skema untuk pengajuan perbaikan / ralat PEB.
                </p>
            </div>
        </div>
    @endif

    <form action="{{ route('bc30.update', $bc30->id) }}" method="POST" id="bc30EditForm" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- 1. Header & Kantor Pabean -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-6 border border-slate-200/80 dark:border-white/10 shadow-xs">
            <div class="flex items-center space-x-2 text-slate-800 dark:text-white font-bold text-sm mb-4 pb-2 border-b border-slate-100 dark:border-white/10">
                <span class="w-6 h-6 rounded-lg bg-emerald-100 dark:bg-emerald-950/50 text-[#34C759] flex items-center justify-center text-xs font-bold">1</span>
                <span>Kantor Pabean & Jenis Ekspor</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Kantor Pabean Pemuatan *</label>
                    <select name="kode_kantor" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white focus:ring-2 focus:ring-[#34C759]">
                        @foreach($kantorList as $code => $name)
                            <option value="{{ $code }}" {{ old('kode_kantor', $bc30->kode_kantor) === $code ? 'selected' : '' }}>
                                {{ $code }} - {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Jenis Ekspor</label>
                    <input type="text" value="1 - Ekspor Biasa" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 text-slate-500 dark:text-slate-400" readonly>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Kategori Ekspor</label>
                    <input type="text" value="1 - Umum" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 text-slate-500 dark:text-slate-400" readonly>
                </div>
            </div>
        </div>

        <!-- 2. Entitas Kepabeanan -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-6 border border-slate-200/80 dark:border-white/10 shadow-xs">
            <div class="flex items-center space-x-2 text-slate-800 dark:text-white font-bold text-sm mb-4 pb-2 border-b border-slate-100 dark:border-white/10">
                <span class="w-6 h-6 rounded-lg bg-emerald-100 dark:bg-emerald-950/50 text-[#34C759] flex items-center justify-center text-xs font-bold">2</span>
                <span>Entitas Kepabeanan Ekspor</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs">
                <!-- Eksportir Lokal -->
                <div class="p-4 rounded-xl bg-slate-50/70 dark:bg-[#2C2C2E]/60 border border-slate-200 dark:border-white/10">
                    <span class="inline-block px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950/50 text-[#34C759] font-bold text-[10px] mb-2">EKSPORTIR (KODE 2)</span>
                    <div class="space-y-2">
                        <div>
                            <label class="block text-slate-500 dark:text-slate-400 font-medium">Nama Perusahaan Eksportir</label>
                            <input type="text" value="{{ $bc30->nama_perusahaan_lokal ?? config('ceisa.company.name') }}" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#1C1C1E] font-semibold text-slate-800 dark:text-white" readonly>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-slate-500 dark:text-slate-400 font-medium">NPWP Eksportir</label>
                                <input type="text" value="{{ $bc30->npwp_perusahaan_lokal ?? config('ceisa.company.npwp') }}" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#1C1C1E] font-mono tabular-nums text-slate-700 dark:text-slate-300" readonly>
                            </div>
                            <div>
                                <label class="block text-slate-500 dark:text-slate-400 font-medium">NIB</label>
                                <input type="text" value="{{ config('ceisa.company.nib') }}" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#1C1C1E] font-mono tabular-nums text-slate-700 dark:text-slate-300" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pembeli Luar Negeri (Buyer) -->
                <div class="p-4 rounded-xl bg-slate-50/70 dark:bg-[#2C2C2E]/60 border border-slate-200 dark:border-white/10">
                    <span class="inline-block px-2 py-0.5 rounded bg-slate-200 dark:bg-white/10 text-slate-800 dark:text-slate-200 font-bold text-[10px] mb-2">PEMBELI / BUYER (KODE 6)</span>
                    <div class="space-y-2">
                        <div>
                            <label class="block text-slate-600 dark:text-slate-300 font-semibold mb-1">Nama Pembeli (Buyer) *</label>
                            <input type="text" name="buyer_nama" 
                                   value="{{ old('buyer_nama', $bc30->nama_mitra_luar_negeri) }}" 
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white focus:ring-2 focus:ring-[#34C759]" required>
                        </div>
                        <div>
                            <label class="block text-slate-600 dark:text-slate-300 font-semibold mb-1">Negara Tujuan / Pembeli *</label>
                            <select name="buyer_negara" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white focus:ring-2 focus:ring-[#34C759]" required>
                                <option value="US" {{ old('buyer_negara', $bc30->negara_mitra_luar_negeri) === 'US' ? 'selected' : '' }}>US - Amerika Serikat</option>
                                <option value="JP" {{ old('buyer_negara', $bc30->negara_mitra_luar_negeri) === 'JP' ? 'selected' : '' }}>JP - Jepang</option>
                                <option value="CN" {{ old('buyer_negara', $bc30->negara_mitra_luar_negeri) === 'CN' ? 'selected' : '' }}>CN - Tiongkok (China)</option>
                                <option value="SG" {{ old('buyer_negara', $bc30->negara_mitra_luar_negeri) === 'SG' ? 'selected' : '' }}>SG - Singapura</option>
                                <option value="DE" {{ old('buyer_negara', $bc30->negara_mitra_luar_negeri) === 'DE' ? 'selected' : '' }}>DE - Jerman</option>
                                <option value="NL" {{ old('buyer_negara', $bc30->negara_mitra_luar_negeri) === 'NL' ? 'selected' : '' }}>NL - Belanda</option>
                                <option value="AU" {{ old('buyer_negara', $bc30->negara_mitra_luar_negeri) === 'AU' ? 'selected' : '' }}>AU - Australia</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Pengangkutan & Dokumen Pelengkap -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-6 border border-slate-200/80 dark:border-white/10 shadow-xs">
            <div class="flex items-center space-x-2 text-slate-800 dark:text-white font-bold text-sm mb-4 pb-2 border-b border-slate-100 dark:border-white/10">
                <span class="w-6 h-6 rounded-lg bg-emerald-100 dark:bg-emerald-950/50 text-[#34C759] flex items-center justify-center text-xs font-bold">3</span>
                <span>Pengangkutan & Dokumen Pelengkap Ekspor</span>
            </div>
            @php
                $angkutNama = $payload['pengangkut'][0]['namaPengangkut'] ?? ($shipment ? ($shipment->vessel_name . ' ' . $shipment->voyage_number) : 'MV WAN HAI 315');
                $pelMuat = $payload['pelabuhanMuat']['kodePelabuhan'] ?? ($shipment?->port_of_loading ?? 'IDTPP');
                $pelBongkar = $payload['pelabuhanBongkar']['kodePelabuhan'] ?? ($shipment?->port_of_discharge ?? 'USLAX');
                $invNo = $payload['dokumen'][0]['nomorDokumen'] ?? ($shipment?->invoice_number ?? 'PEB-INV-' . date('Ymd'));
                $invDate = $payload['dokumen'][0]['tanggalDokumen'] ?? ($shipment?->invoice_date ? $shipment->invoice_date->format('Y-m-d') : date('Y-m-d'));
            @endphp
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-xs mb-4">
                <div class="sm:col-span-2">
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Kapal / Sarana Pengangkut *</label>
                    <input type="text" name="nama_sarana_pengangkut" 
                           value="{{ old('nama_sarana_pengangkut', $angkutNama) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white focus:ring-2 focus:ring-[#34C759]" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Pelabuhan Muat Ekspor *</label>
                    <input type="text" name="pelabuhan_muat" 
                           value="{{ old('pelabuhan_muat', $pelMuat) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono uppercase focus:ring-2 focus:ring-[#34C759]" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Pelabuhan Bongkar Tujuan *</label>
                    <input type="text" name="pelabuhan_bongkar" 
                           value="{{ old('pelabuhan_bongkar', $pelBongkar) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono uppercase focus:ring-2 focus:ring-[#34C759]" required>
                </div>
            </div>

            <!-- Invoice & Perizinan Ekspor -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs pt-3 border-t border-slate-100 dark:border-white/10">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nomor Invoice Ekspor *</label>
                    <input type="text" name="invoice_no" 
                           value="{{ old('invoice_no', $invNo) }}" 
                           class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Invoice Ekspor *</label>
                    <input type="date" name="invoice_date" 
                           value="{{ old('invoice_date', $invDate) }}" 
                           class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Lokasi Siap Periksa</label>
                    <input type="text" name="lokasi_siap_periksa" 
                           value="{{ old('lokasi_siap_periksa', $bc30->lokasi_siap_periksa ?? 'Gudang Konsolidasi TPS Tanjung Priok') }}" 
                           class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Siap Periksa</label>
                    <input type="date" name="tanggal_periksa" 
                           value="{{ old('tanggal_periksa', $bc30->tanggal_periksa?->format('Y-m-d') ?? date('Y-m-d')) }}" 
                           class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white">
                </div>
            </div>
        </div>

        <!-- 4. Nilai Ekspor & Valuta (FOB) -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-6 border border-slate-200/80 dark:border-white/10 shadow-xs">
            <div class="flex items-center space-x-2 text-slate-800 dark:text-white font-bold text-sm mb-4 pb-2 border-b border-slate-100 dark:border-white/10">
                <span class="w-6 h-6 rounded-lg bg-emerald-100 dark:bg-emerald-950/50 text-[#34C759] flex items-center justify-center text-xs font-bold">4</span>
                <span>Valuta & Nilai Ekspor (Incoterm FOB)</span>
            </div>
            @php
                $valuta = $bc30->kode_valuta ?? 'USD';
                $kurs = (float) ($bc30->ndbm_kurs ?? 16200.0);
                $fobVal = (float) ($header['fob'] ?? ($bc30->total_nilai ?? 48500.0));
                $freightVal = (float) ($header['freight'] ?? 0.0);
                $asuransiVal = (float) ($header['asuransi'] ?? 0.0);
                $brutoVal = (float) ($bc30->total_bruto ?? 12000.0);
                $nettoVal = (float) ($bc30->total_netto ?? 11500.0);
            @endphp
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs mb-4">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Mata Uang (Valuta) *</label>
                    <select name="kode_valuta" x-model="valuta" @change="updateKurs()" 
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-bold focus:ring-2 focus:ring-[#34C759]">
                        <option value="USD" {{ $valuta === 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                        <option value="EUR" {{ $valuta === 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                        <option value="JPY" {{ $valuta === 'JPY' ? 'selected' : '' }}>JPY - Japanese Yen</option>
                        <option value="SGD" {{ $valuta === 'SGD' ? 'selected' : '' }}>SGD - Singapore Dollar</option>
                        <option value="CNY" {{ $valuta === 'CNY' ? 'selected' : '' }}>CNY - Chinese Yuan</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">NDBM Kurs Pajak (KMK) *</label>
                    <input type="number" step="0.01" name="ndbm_kurs" x-model="ndbmKurs" 
                           value="{{ old('ndbm_kurs', $kurs) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono tabular-nums focus:ring-2 focus:ring-[#34C759]" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nilai FOB Ekspor (Valas) *</label>
                    <input type="number" step="0.01" name="fob" x-model="fob" 
                           value="{{ old('fob', $fobVal) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono tabular-nums focus:ring-2 focus:ring-[#34C759]" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Freight (Opsional)</label>
                    <input type="number" step="0.01" name="freight" 
                           value="{{ old('freight', $freightVal) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono tabular-nums">
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Asuransi (Opsional)</label>
                    <input type="number" step="0.01" name="asuransi" 
                           value="{{ old('asuransi', $asuransiVal) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono tabular-nums">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Berat Bruto (KGM) *</label>
                    <input type="number" step="0.01" name="bruto" 
                           value="{{ old('bruto', $brutoVal) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono tabular-nums focus:ring-2 focus:ring-[#34C759]" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Berat Netto (KGM) *</label>
                    <input type="number" step="0.01" name="netto" 
                           value="{{ old('netto', $nettoVal) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono tabular-nums focus:ring-2 focus:ring-[#34C759]" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Total Nilai Ekspor (IDR)</label>
                    <div class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 font-mono font-bold text-slate-900 dark:text-white" x-text="'Rp ' + formatNumber(fob * ndbmKurs)">
                        Rp {{ number_format($fobVal * $kurs, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. Rincian Barang Ekspor & Bea Keluar -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-6 border border-slate-200/80 dark:border-white/10 shadow-xs">
            <div class="flex items-center justify-between text-slate-800 dark:text-white font-bold text-sm mb-4 pb-2 border-b border-slate-100 dark:border-white/10">
                <div class="flex items-center space-x-2">
                    <span class="w-6 h-6 rounded-lg bg-emerald-100 dark:bg-emerald-950/50 text-[#34C759] flex items-center justify-center text-xs font-bold">5</span>
                    <span>Rincian Barang Ekspor & Bea Keluar (BK)</span>
                </div>
                <button type="button" @click="addItem()" class="btn-apple-tinted text-xs px-3 py-1.5 inline-flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                    <span>Tambah Baris Barang</span>
                </button>
            </div>

            <div class="space-y-4">
                <template x-for="(item, index) in items" :key="index">
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-[#2C2C2E]/60 border border-slate-200 dark:border-white/10 relative">
                        <div class="flex items-center justify-between mb-3">
                            <span class="font-bold text-xs text-slate-700 dark:text-slate-300" x-text="'Barang Ekspor #' + (index + 1)"></span>
                            <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="text-rose-600 hover:text-rose-700 text-xs font-semibold">
                                Hapus Baris
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs mb-3">
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">HS Code Ekspor (BTKI) *</label>
                                <input type="text" :name="'barang[' + index + '][hs_code]'" x-model="item.hs_code"
                                       class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white font-mono" required>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Uraian Barang Ekspor *</label>
                                <input type="text" :name="'barang[' + index + '][uraian]'" x-model="item.uraian"
                                       class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white" required>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Merek & Tipe</label>
                                <input type="text" :name="'barang[' + index + '][merk]'" x-model="item.merk"
                                       class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Jumlah Kemasan</label>
                                <input type="number" :name="'barang[' + index + '][jumlah]'" x-model="item.jumlah"
                                       class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white font-mono tabular-nums" required>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Jenis Kemasan</label>
                                <input type="text" :name="'barang[' + index + '][kemasan]'" x-model="item.kemasan"
                                       class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white font-mono" required>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">FOB Ekspor (Valas) *</label>
                                <input type="number" step="0.01" :name="'barang[' + index + '][fob]'" x-model="item.fob" @input="recalcFobFromItems()"
                                       class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white font-mono tabular-nums" required>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tarif BK (%)</label>
                                <input type="number" step="0.1" :name="'barang[' + index + '][tarif_bk]'" x-model="item.tarif_bk"
                                       class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white font-mono tabular-nums">
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- 6. Kontainer & Data Penutup -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-6 border border-slate-200/80 dark:border-white/10 shadow-xs">
            <div class="flex items-center space-x-2 text-slate-800 dark:text-white font-bold text-sm mb-4 pb-2 border-b border-slate-100 dark:border-white/10">
                <span class="w-6 h-6 rounded-lg bg-emerald-100 dark:bg-emerald-950/50 text-[#34C759] flex items-center justify-center text-xs font-bold">6</span>
                <span>Peti Kemas & Data Penanggung Jawab Ekspor</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Total Kemasan</label>
                    <input type="number" name="jumlah_kemasan" 
                           value="{{ old('jumlah_kemasan', $bc30->total_kemasan ?? 200) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono tabular-nums" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama PIC Pembuat</label>
                    <input type="text" name="pic_pembuat" 
                           value="{{ old('pic_pembuat', $bc30->pic_pembuat ?? auth()->user()->name) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Telepon PIC</label>
                    <input type="text" name="telepon_pembuat" 
                           value="{{ old('telepon_pembuat', $bc30->telepon_pembuat ?? '081234567890') }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Bank Devisa Hasil Ekspor (DHE)</label>
                    <input type="text" name="bank_nama" 
                           value="{{ old('bank_nama', $bc30->bank_nama ?? 'BANK MANDIRI (PERSERO) TBK') }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white">
                </div>
            </div>
        </div>

        <!-- Tombol Aksi Simpan & Batal -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 bg-white dark:bg-[#1C1C1E] rounded-2xl border border-black/10 dark:border-white/10 shadow-xs">
            <div class="flex items-center gap-2">
                <a href="{{ route('bc30.show', $bc30->id) }}" class="btn-apple-gray text-xs px-4 py-2.5">
                    Batal
                </a>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="btn-apple-filled text-xs px-6 py-2.5 inline-flex items-center gap-2 cursor-pointer shadow-sm !bg-[#34C759] hover:!bg-[#2fb350]">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Perubahan Dokumen BC 3.0</span>
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function bc30EditManager() {
    return {
        valuta: '{{ $valuta }}',
        ndbmKurs: {{ $kurs }},
        fob: {{ $fobVal }},
        items: [
            @if(!empty($barangList))
                @foreach($barangList as $b)
                    {
                        hs_code: '{{ $b['kodeHs'] ?? $b['hs_code'] ?? '09011110' }}',
                        uraian: '{{ addslashes($b['uraian'] ?? $b['uraianBarang'] ?? '') }}',
                        merk: '{{ addslashes($b['merk'] ?? '') }}',
                        jumlah: {{ $b['jumlahKemasan'] ?? $b['jumlah'] ?? 100 }},
                        kemasan: '{{ $b['jenisKemasan'] ?? $b['kemasan'] ?? 'BAG' }}',
                        fob: {{ (float) ($b['fob'] ?? $b['cif'] ?? 1000) }},
                        tarif_bk: {{ (float) ($b['tarifBk'] ?? $b['tarif_bk'] ?? 0.0) }}
                    },
                @endforeach
            @else
                {
                    hs_code: '09011110',
                    uraian: 'BIJI KOPI ARABIKA GAYO GRADE 1 (EXPORT QUALITY)',
                    merk: 'GAYO HIGHLAND GRADE 1',
                    jumlah: 200,
                    kemasan: 'BAG',
                    fob: {{ $fobVal }},
                    tarif_bk: 0.0
                }
            @endif
        ],

        addItem() {
            this.items.push({
                hs_code: '09011110',
                uraian: 'KOMODITAS EKSPOR INDONESIA',
                merk: '',
                jumlah: 1,
                kemasan: 'BAG',
                fob: 100.0,
                tarif_bk: 0.0
            });
            this.recalcFobFromItems();
        },

        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
                this.recalcFobFromItems();
            }
        },

        recalcFobFromItems() {
            let total = 0;
            this.items.forEach(it => {
                total += parseFloat(it.fob || 0);
            });
            if (total > 0) {
                this.fob = Math.round(total * 100) / 100;
            }
        },

        formatNumber(num) {
            return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num);
        },

        async updateKurs() {
            try {
                const res = await fetch('/ceisa/kurs/' + this.valuta);
                if (res.ok) {
                    const data = await res.json();
                    if (data.nilai_kurs) {
                        this.ndbmKurs = parseFloat(data.nilai_kurs);
                    }
                }
            } catch (err) {
                console.warn('Gagal ambil kurs terkini:', err);
            }
        }
    };
}
</script>
@endpush
@endsection
