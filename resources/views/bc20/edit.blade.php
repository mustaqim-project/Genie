@extends('layouts.app')

@section('title', 'Edit Dokumen BC 2.0 - ' . $bc20->nomor_aju)

@section('content')
<div class="max-w-[1360px] mx-auto px-4 sm:px-6 space-y-6 pt-4 pb-28 lg:pb-12" x-data="bc20EditManager()">
    <!-- Breadcrumb -->
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Tim Document', 'url' => route('core.document.index')],
        ['label' => 'BC 2.0 (Impor)', 'url' => route('bc20.index')],
        ['label' => 'Detail Dokumen', 'url' => route('bc20.show', $bc20->id)],
        ['label' => 'Edit Data']
    ]" />

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-2">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-lg text-xs font-semibold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20">
                    BC 2.0 (PIB)
                </span>
                <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-semibold {{ $bc20->status_badge_class }}">
                    Status: {{ $bc20->status }}
                </span>
            </div>
            <h1 class="text-2xl font-bold font-apple-display text-slate-900 dark:text-white tracking-tight">
                {{ $isDraft ? 'Edit Draf Dokumen Pabean BC 2.0' : 'Revisi Data Dokumen BC 2.0 (Pasca-Kirim CEISA)' }}
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 font-mono">
                Nomor Aju: <strong>{{ $bc20->nomor_aju }}</strong> &bull; Dibuat: {{ $bc20->created_at->format('d/m/Y H:i') }}
            </p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('bc20.show', $bc20->id) }}" class="btn-apple-gray text-xs px-3.5 py-2 inline-flex items-center gap-1.5">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                <span>Kembali ke Detail</span>
            </a>
        </div>
    </div>

    <!-- Banner Info Role Hak Akses -->
    @if($isDraft)
        <div class="p-4 rounded-2xl bg-blue-50/80 dark:bg-blue-950/30 border border-blue-200/80 dark:border-blue-900/40 text-blue-900 dark:text-blue-300 text-xs flex items-start gap-3">
            <i data-lucide="check-circle" class="w-5 h-5 text-[#007AFF] shrink-0 mt-0.5"></i>
            <div>
                <strong class="font-bold">Mode Edit Draft Aktif (Izin: document.edit)</strong>
                <p class="text-[11px] text-blue-700 dark:text-blue-400 mt-0.5 leading-relaxed">
                    Dokumen ini berstatus DRAFT. Anda dapat menyesuaikan seluruh elemen data pengangkutan, supplier, nilai pabean (CIF/FOB), dan rincian pos tarif HS Code sebelum divalidasi dan dikirim ke server CEISA 4.0 DJBC.
                </p>
            </div>
        </div>
    @else
        <div class="p-4 rounded-2xl bg-amber-50/90 dark:bg-amber-950/40 border border-amber-300/80 dark:border-amber-800/60 text-amber-950 dark:text-amber-200 text-xs flex items-start gap-3 shadow-xs">
            <i data-lucide="shield-alert" class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5"></i>
            <div>
                <strong class="font-bold">Mode Revisi Pasca-Kirim CEISA (Izin Khusus: document.edit_after_ceisa)</strong>
                <p class="text-[11px] text-amber-800 dark:text-amber-300 mt-0.5 leading-relaxed">
                    Dokumen ini telah diajukan ke CEISA dengan status <strong>{{ $bc20->status }}</strong>. Melalui hak akses supervisor/khusus ini, Anda diizinkan melakukan pembaruan data pabean yang nantinya dicatat secara otomatis dalam audit trail dan mereset pre-flight check skema untuk pengajuan perbaikan (amendment/notul).
                </p>
            </div>
        </div>
    @endif

    <form action="{{ route('bc20.update', $bc20->id) }}" method="POST" id="bc20EditForm" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- 1. Header & Kantor Pabean -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-6 border border-slate-200/80 dark:border-white/10 shadow-xs">
            <div class="flex items-center space-x-2 text-slate-800 dark:text-white font-bold text-sm mb-4 pb-2 border-b border-slate-100 dark:border-white/10">
                <span class="w-6 h-6 rounded-lg bg-sky-100 dark:bg-sky-950/50 text-[#007AFF] flex items-center justify-center text-xs font-bold">1</span>
                <span>Kantor Pabean & Jenis Transaksi</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Kantor Pabean Bongkar / Tujuan *</label>
                    <select name="kode_kantor" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white focus:ring-2 focus:ring-[#007AFF]">
                        @foreach($kantorList as $code => $name)
                            <option value="{{ $code }}" {{ old('kode_kantor', $bc20->kode_kantor) === $code ? 'selected' : '' }}>
                                {{ $code }} - {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Jenis Impor</label>
                    <input type="text" value="1 - Untuk Dipakai" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 text-slate-500 dark:text-slate-400" readonly>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Cara Pembayaran</label>
                    <input type="text" value="1 - Biasa / Tunai (Billing MPN G3)" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 text-slate-500 dark:text-slate-400" readonly>
                </div>
            </div>
        </div>

        <!-- 2. Data Entitas Stakeholder -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-6 border border-slate-200/80 dark:border-white/10 shadow-xs">
            <div class="flex items-center space-x-2 text-slate-800 dark:text-white font-bold text-sm mb-4 pb-2 border-b border-slate-100 dark:border-white/10">
                <span class="w-6 h-6 rounded-lg bg-sky-100 dark:bg-sky-950/50 text-[#007AFF] flex items-center justify-center text-xs font-bold">2</span>
                <span>Entitas Kepabeanan</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs">
                <!-- Importir (Lokal) -->
                <div class="p-4 rounded-xl bg-slate-50/70 dark:bg-[#2C2C2E]/60 border border-slate-200 dark:border-white/10">
                    <span class="inline-block px-2 py-0.5 rounded bg-sky-100 dark:bg-sky-950/50 text-[#007AFF] font-bold text-[10px] mb-2">IMPORTIR (KODE 1)</span>
                    <div class="space-y-2">
                        <div>
                            <label class="block text-slate-500 dark:text-slate-400 font-medium">Nama Perusahaan Importir</label>
                            <input type="text" value="{{ $bc20->nama_perusahaan_lokal ?? config('ceisa.company.name') }}" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#1C1C1E] font-semibold text-slate-800 dark:text-white" readonly>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-slate-500 dark:text-slate-400 font-medium">NPWP</label>
                                <input type="text" value="{{ $bc20->npwp_perusahaan_lokal ?? config('ceisa.company.npwp') }}" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#1C1C1E] font-mono tabular-nums text-slate-700 dark:text-slate-300" readonly>
                            </div>
                            <div>
                                <label class="block text-slate-500 dark:text-slate-400 font-medium">NIB</label>
                                <input type="text" value="{{ config('ceisa.company.nib') }}" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#1C1C1E] font-mono tabular-nums text-slate-700 dark:text-slate-300" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Supplier Luar Negeri -->
                <div class="p-4 rounded-xl bg-slate-50/70 dark:bg-[#2C2C2E]/60 border border-slate-200 dark:border-white/10">
                    <span class="inline-block px-2 py-0.5 rounded bg-slate-200 dark:bg-white/10 text-slate-800 dark:text-slate-200 font-bold text-[10px] mb-2">SUPPLIER / PENGIRIM (KODE 9)</span>
                    <div class="space-y-2">
                        <div>
                            <label class="block text-slate-600 dark:text-slate-300 font-semibold mb-1">Nama Supplier Luar Negeri *</label>
                            <input type="text" name="supplier_nama" 
                                   value="{{ old('supplier_nama', $bc20->nama_mitra_luar_negeri) }}" 
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white focus:ring-2 focus:ring-[#007AFF]" required>
                        </div>
                        <div>
                            <label class="block text-slate-600 dark:text-slate-300 font-semibold mb-1">Negara Asal Supplier *</label>
                            <select name="supplier_negara" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white focus:ring-2 focus:ring-[#007AFF]" required>
                                <option value="CN" {{ old('supplier_negara', $bc20->negara_mitra_luar_negeri) === 'CN' ? 'selected' : '' }}>CN - Tiongkok (China)</option>
                                <option value="SG" {{ old('supplier_negara', $bc20->negara_mitra_luar_negeri) === 'SG' ? 'selected' : '' }}>SG - Singapura</option>
                                <option value="JP" {{ old('supplier_negara', $bc20->negara_mitra_luar_negeri) === 'JP' ? 'selected' : '' }}>JP - Jepang</option>
                                <option value="KR" {{ old('supplier_negara', $bc20->negara_mitra_luar_negeri) === 'KR' ? 'selected' : '' }}>KR - Korea Selatan</option>
                                <option value="US" {{ old('supplier_negara', $bc20->negara_mitra_luar_negeri) === 'US' ? 'selected' : '' }}>US - Amerika Serikat</option>
                                <option value="DE" {{ old('supplier_negara', $bc20->negara_mitra_luar_negeri) === 'DE' ? 'selected' : '' }}>DE - Jerman</option>
                                <option value="MY" {{ old('supplier_negara', $bc20->negara_mitra_luar_negeri) === 'MY' ? 'selected' : '' }}>MY - Malaysia</option>
                                <option value="TH" {{ old('supplier_negara', $bc20->negara_mitra_luar_negeri) === 'TH' ? 'selected' : '' }}>TH - Thailand</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Transportasi & Dokumen Pelengkap -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-6 border border-slate-200/80 dark:border-white/10 shadow-xs">
            <div class="flex items-center space-x-2 text-slate-800 dark:text-white font-bold text-sm mb-4 pb-2 border-b border-slate-100 dark:border-white/10">
                <span class="w-6 h-6 rounded-lg bg-sky-100 dark:bg-sky-950/50 text-[#007AFF] flex items-center justify-center text-xs font-bold">3</span>
                <span>Pengangkutan & Dokumen Pelengkap</span>
            </div>
            @php
                $angkutNama = $payload['pengangkut'][0]['namaPengangkut'] ?? ($shipment ? ($shipment->vessel_name . ' ' . $shipment->voyage_number) : 'MV WAN HAI 315');
                $pelMuat = $payload['pelabuhanMuat']['kodePelabuhan'] ?? ($shipment?->port_of_loading ?? 'CNSHA');
                $pelBongkar = $payload['pelabuhanBongkar']['kodePelabuhan'] ?? ($shipment?->port_of_discharge ?? 'IDTPP');
                $invNo = $payload['dokumen'][0]['nomorDokumen'] ?? ($shipment?->invoice_number ?? 'INV-' . date('Ymd'));
                $invDate = $payload['dokumen'][0]['tanggalDokumen'] ?? ($shipment?->invoice_date ? $shipment->invoice_date->format('Y-m-d') : date('Y-m-d'));
            @endphp
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-xs mb-4">
                <div class="sm:col-span-2">
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Sarana Pengangkut (Vessel / Kapal) *</label>
                    <input type="text" name="nama_sarana_pengangkut" 
                           value="{{ old('nama_sarana_pengangkut', $angkutNama) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white focus:ring-2 focus:ring-[#007AFF]" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Pelabuhan Muat *</label>
                    <input type="text" name="pelabuhan_muat" 
                           value="{{ old('pelabuhan_muat', $pelMuat) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono uppercase focus:ring-2 focus:ring-[#007AFF]" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Pelabuhan Bongkar *</label>
                    <input type="text" name="pelabuhan_bongkar" 
                           value="{{ old('pelabuhan_bongkar', $pelBongkar) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono uppercase focus:ring-2 focus:ring-[#007AFF]" required>
                </div>
            </div>

            <!-- Manifes BC 1.1 & Invoice -->
            <div class="grid grid-cols-2 sm:grid-cols-6 gap-3 text-xs pt-3 border-t border-slate-100 dark:border-white/10">
                <div class="sm:col-span-2">
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nomor Invoice *</label>
                    <input type="text" name="invoice_no" 
                           value="{{ old('invoice_no', $invNo) }}" 
                           class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono" required>
                </div>
                <div class="sm:col-span-2">
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Invoice *</label>
                    <input type="date" name="invoice_date" 
                           value="{{ old('invoice_date', $invDate) }}" 
                           class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">No. BC 1.1</label>
                    <input type="text" name="nomor_bc11" 
                           value="{{ old('nomor_bc11', $bc20->nomor_bc11 ?? '004128') }}" 
                           class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tgl BC 1.1</label>
                    <input type="date" name="tanggal_bc11" 
                           value="{{ old('tanggal_bc11', $bc20->tanggal_bc11?->format('Y-m-d') ?? date('Y-m-d')) }}" 
                           class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white">
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs mt-3">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Pos BC 1.1</label>
                    <input type="text" name="pos_bc11" 
                           value="{{ old('pos_bc11', $bc20->pos_bc11 ?? '0012') }}" 
                           class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Sub Pos BC 1.1</label>
                    <input type="text" name="subpos_bc11" 
                           value="{{ old('subpos_bc11', $bc20->subpos_bc11 ?? '0000') }}" 
                           class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Kode TPS</label>
                    <input type="text" name="kode_tps" 
                           value="{{ old('kode_tps', $bc20->kode_tps ?? 'JICT') }}" 
                           class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono uppercase">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Estimasi Tanggal Tiba</label>
                    <input type="date" name="tanggal_tiba" 
                           value="{{ old('tanggal_tiba', $bc20->tanggal_tiba?->format('Y-m-d') ?? date('Y-m-d')) }}" 
                           class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white">
                </div>
            </div>
        </div>

        <!-- 4. Valuasi & Nilai Pabean (CIF / FOB) -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-6 border border-slate-200/80 dark:border-white/10 shadow-xs">
            <div class="flex items-center space-x-2 text-slate-800 dark:text-white font-bold text-sm mb-4 pb-2 border-b border-slate-100 dark:border-white/10">
                <span class="w-6 h-6 rounded-lg bg-sky-100 dark:bg-sky-950/50 text-[#007AFF] flex items-center justify-center text-xs font-bold">4</span>
                <span>Valuta Asing & Nilai Pabean (Incoterm CIF)</span>
            </div>
            @php
                $valuta = $bc20->kode_valuta ?? 'USD';
                $kurs = (float) ($bc20->ndbm_kurs ?? 16200.0);
                $fobVal = (float) ($header['fob'] ?? ($bc20->total_nilai ?? 15000.0));
                $freightVal = (float) ($header['freight'] ?? 1500.0);
                $asuransiVal = (float) ($header['asuransi'] ?? 50.0);
                $cifVal = $fobVal + $freightVal + $asuransiVal;
                $brutoVal = (float) ($bc20->total_bruto ?? 8500.0);
                $nettoVal = (float) ($bc20->total_netto ?? 7800.0);
            @endphp
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs mb-4">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Mata Uang (Valuta) *</label>
                    <select name="kode_valuta" x-model="valuta" @change="updateKurs()" 
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-bold focus:ring-2 focus:ring-[#007AFF]">
                        <option value="USD" {{ $valuta === 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                        <option value="CNY" {{ $valuta === 'CNY' ? 'selected' : '' }}>CNY - Chinese Yuan</option>
                        <option value="EUR" {{ $valuta === 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                        <option value="SGD" {{ $valuta === 'SGD' ? 'selected' : '' }}>SGD - Singapore Dollar</option>
                        <option value="JPY" {{ $valuta === 'JPY' ? 'selected' : '' }}>JPY - Japanese Yen</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">NDBM Kurs Pajak (KMK) *</label>
                    <input type="number" step="0.01" name="ndbm_kurs" x-model="ndbmKurs" @input="recalc()"
                           value="{{ old('ndbm_kurs', $kurs) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono tabular-nums focus:ring-2 focus:ring-[#007AFF]" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nilai FOB (Valas) *</label>
                    <input type="number" step="0.01" name="fob" x-model="fob" @input="recalc()"
                           value="{{ old('fob', $fobVal) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono tabular-nums focus:ring-2 focus:ring-[#007AFF]" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Freight *</label>
                    <input type="number" step="0.01" name="freight" x-model="freight" @input="recalc()"
                           value="{{ old('freight', $freightVal) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono tabular-nums focus:ring-2 focus:ring-[#007AFF]" required>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Asuransi *</label>
                    <input type="number" step="0.01" name="asuransi" x-model="asuransi" @input="recalc()"
                           value="{{ old('asuransi', $asuransiVal) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono tabular-nums focus:ring-2 focus:ring-[#007AFF]" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Total CIF (Valas)</label>
                    <div class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 font-mono font-bold text-slate-900 dark:text-white" x-text="formatNumber(cifTotal) + ' ' + valuta">
                        {{ number_format($cifVal, 2) }} {{ $valuta }}
                    </div>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Berat Bruto (KGM) *</label>
                    <input type="number" step="0.01" name="bruto" 
                           value="{{ old('bruto', $brutoVal) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono tabular-nums focus:ring-2 focus:ring-[#007AFF]" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Berat Netto (KGM) *</label>
                    <input type="number" step="0.01" name="netto" 
                           value="{{ old('netto', $nettoVal) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono tabular-nums focus:ring-2 focus:ring-[#007AFF]" required>
                </div>
            </div>
        </div>

        <!-- 5. Rincian Barang & Pos Tarif BTKI -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-6 border border-slate-200/80 dark:border-white/10 shadow-xs">
            <div class="flex items-center justify-between text-slate-800 dark:text-white font-bold text-sm mb-4 pb-2 border-b border-slate-100 dark:border-white/10">
                <div class="flex items-center space-x-2">
                    <span class="w-6 h-6 rounded-lg bg-sky-100 dark:bg-sky-950/50 text-[#007AFF] flex items-center justify-center text-xs font-bold">5</span>
                    <span>Rincian Barang & Pos Tarif BTKI (BM, PPN 11%, PPh 22)</span>
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
                            <span class="font-bold text-xs text-slate-700 dark:text-slate-300" x-text="'Item #' + (index + 1)"></span>
                            <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="text-rose-600 hover:text-rose-700 text-xs font-semibold">
                                Hapus Baris
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs mb-3">
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">HS Code (BTKI) *</label>
                                <input type="text" :name="'barang[' + index + '][hs_code]'" x-model="item.hs_code" @blur="checkHsCode(index)"
                                       class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white font-mono" required>
                                <div x-show="item.hs_info" class="mt-1 p-1 rounded text-[10px] bg-sky-50 dark:bg-sky-950/40 text-[#007AFF]" x-text="item.hs_info"></div>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Uraian Komoditas *</label>
                                <input type="text" :name="'barang[' + index + '][uraian]'" x-model="item.uraian"
                                       class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white" required>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Merek & Tipe</label>
                                <input type="text" :name="'barang[' + index + '][merk]'" x-model="item.merk"
                                       class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-6 gap-3 text-xs">
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Jumlah</label>
                                <input type="number" :name="'barang[' + index + '][jumlah]'" x-model="item.jumlah"
                                       class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white font-mono tabular-nums" required>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Kemasan</label>
                                <input type="text" :name="'barang[' + index + '][kemasan]'" x-model="item.kemasan"
                                       class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white font-mono" required>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">FOB Valas *</label>
                                <input type="number" step="0.01" :name="'barang[' + index + '][fob]'" x-model="item.fob" @input="recalcFobFromItems()"
                                       class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white font-mono tabular-nums" required>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">BM (%) *</label>
                                <input type="number" step="0.1" :name="'barang[' + index + '][tarif_bm]'" x-model="item.tarif_bm"
                                       class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white font-mono tabular-nums" required>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">PPN (%) *</label>
                                <input type="number" step="0.1" :name="'barang[' + index + '][tarif_ppn]'" x-model="item.tarif_ppn"
                                       class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white font-mono tabular-nums" required>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">PPh 22 (%) *</label>
                                <input type="number" step="0.1" :name="'barang[' + index + '][tarif_pph]'" x-model="item.tarif_pph"
                                       class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white font-mono tabular-nums" required>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- 6. Kontainer & Kemasan -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-6 border border-slate-200/80 dark:border-white/10 shadow-xs">
            <div class="flex items-center space-x-2 text-slate-800 dark:text-white font-bold text-sm mb-4 pb-2 border-b border-slate-100 dark:border-white/10">
                <span class="w-6 h-6 rounded-lg bg-sky-100 dark:bg-sky-950/50 text-[#007AFF] flex items-center justify-center text-xs font-bold">6</span>
                <span>Petikemas & Total Kemasan Kargo</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Total Kemasan</label>
                    <input type="number" name="jumlah_kemasan" 
                           value="{{ old('jumlah_kemasan', $bc20->total_kemasan ?? 100) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono tabular-nums" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Jenis Kemasan</label>
                    <input type="text" value="CTN - Cartons" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 text-slate-500 dark:text-slate-400" readonly>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Jumlah Kontainer</label>
                    <input type="number" value="{{ $bc20->total_kontainer ?? 1 }}" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 text-slate-500 dark:text-slate-400 font-mono" readonly>
                </div>
            </div>
        </div>

        <!-- Tombol Aksi Simpan & Batal -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 bg-white dark:bg-[#1C1C1E] rounded-2xl border border-black/10 dark:border-white/10 shadow-xs">
            <div class="flex items-center gap-2">
                <a href="{{ route('bc20.show', $bc20->id) }}" class="btn-apple-gray text-xs px-4 py-2.5">
                    Batal
                </a>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="btn-apple-filled text-xs px-6 py-2.5 inline-flex items-center gap-2 cursor-pointer shadow-sm">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Perubahan Dokumen BC 2.0</span>
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function bc20EditManager() {
    return {
        valuta: '{{ $valuta }}',
        ndbmKurs: {{ $kurs }},
        fob: {{ $fobVal }},
        freight: {{ $freightVal }},
        asuransi: {{ $asuransiVal }},
        cifTotal: {{ $cifVal }},
        items: [
            @if(!empty($barangList))
                @foreach($barangList as $b)
                    {
                        hs_code: '{{ $b['kodeHs'] ?? $b['hs_code'] ?? '84522900' }}',
                        uraian: '{{ addslashes($b['uraian'] ?? $b['uraianBarang'] ?? '') }}',
                        merk: '{{ addslashes($b['merk'] ?? '') }}',
                        jumlah: {{ $b['jumlahKemasan'] ?? $b['jumlah'] ?? 1 }},
                        kemasan: '{{ $b['jenisKemasan'] ?? $b['kemasan'] ?? 'CTN' }}',
                        fob: {{ (float) ($b['cif'] ?? $b['fob'] ?? 1000) }},
                        tarif_bm: {{ (float) ($b['tarifBm'] ?? $b['tarif_bm'] ?? 5.0) }},
                        tarif_ppn: {{ (float) ($b['tarifPpn'] ?? $b['tarif_ppn'] ?? 11.0) }},
                        tarif_pph: {{ (float) ($b['tarifPph'] ?? $b['tarif_pph'] ?? 2.5) }},
                        hs_info: ''
                    },
                @endforeach
            @else
                {
                    hs_code: '84522900',
                    uraian: 'MESIN INDUSTRI',
                    merk: 'MODEL DDL-8700',
                    jumlah: 50,
                    kemasan: 'CTN',
                    fob: {{ $fobVal }},
                    tarif_bm: 5.0,
                    tarif_ppn: 11.0,
                    tarif_pph: 2.5,
                    hs_info: ''
                }
            @endif
        ],

        init() {
            this.recalc();
        },

        addItem() {
            this.items.push({
                hs_code: '84713020',
                uraian: 'KOMPONEN PERALATAN TEKNOLOGI',
                merk: '',
                jumlah: 1,
                kemasan: 'PCE',
                fob: 100.0,
                tarif_bm: 0.0,
                tarif_ppn: 11.0,
                tarif_pph: 2.5,
                hs_info: ''
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
                this.recalc();
            }
        },

        recalc() {
            const f = parseFloat(this.fob || 0);
            const fr = parseFloat(this.freight || 0);
            const as = parseFloat(this.asuransi || 0);
            this.cifTotal = Math.round((f + fr + as) * 100) / 100;
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
        },

        async checkHsCode(index) {
            const hs = (this.items[index].hs_code || '').replace(/\D/g, '');
            if (hs.length >= 8) {
                try {
                    const res = await fetch('/ceisa/tarif-hs/' + hs);
                    if (res.ok) {
                        const data = await res.json();
                        if (data.success && data.data) {
                            const d = data.data;
                            if (d.bm !== undefined) this.items[index].tarif_bm = parseFloat(d.bm);
                            if (d.ppn !== undefined) this.items[index].tarif_ppn = parseFloat(d.ppn);
                            if (d.pph !== undefined) this.items[index].tarif_pph = parseFloat(d.pph);
                            this.items[index].hs_info = '✓ Tarif: BM ' + (d.bm || 0) + '%, PPN ' + (d.ppn || 11) + '%, PPh ' + (d.pph || 2.5) + '%' + (d.is_lartas ? ' (Kena Lartas)' : '');
                        }
                    }
                } catch (e) {
                    // ignore
                }
            }
        }
    };
}
</script>
@endpush
@endsection
