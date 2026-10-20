@extends('layouts.app')

@section('title', 'Form Pengajuan BC 2.0 (PIB)')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-16">
    <!-- Breadcrumb -->
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Tim Document', 'url' => route('core.document.index')],
        ['label' => 'BC 2.0 (Impor)', 'url' => route('bc20.index')],
        ['label' => 'Form Pengajuan Baru']
    ]" />

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold font-apple-display text-slate-900 dark:text-white tracking-tight">Formulir Pabean BC 2.0 (PIB)</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pemberitahuan Impor Barang untuk Dipakai (Host-to-Host CEISA 4.0 DJBC)</p>
        </div>
        <div class="flex items-center space-x-2">
            <span class="text-xs font-mono tabular-nums bg-slate-100 dark:bg-white/10 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-white/10 text-slate-700 dark:text-slate-300">
                Aju ID: {{ $defaultNomorAju }}
            </span>
        </div>
    </div>

    <!-- Integrasi Cepat: Pilih dari Proyek Shipment Tim Document -->
    <div class="bg-gradient-to-r from-sky-50 to-indigo-50 dark:from-sky-950/30 dark:to-indigo-950/30 rounded-2xl p-4 border border-sky-200/80 dark:border-sky-800/60 shadow-xs mb-6 text-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center space-x-3">
                <i data-lucide="zap" class="w-6 h-6 text-[#007AFF] flex-shrink-0"></i>
                <div>
                    <strong class="font-bold text-slate-900 dark:text-white">Import Otomatis dari Proyek Shipment</strong>
                    <p class="text-[11px] text-slate-600 dark:text-slate-300">Pilih proyek pengapalan aktif untuk mengisi seluruh data sarana pengangkut, valuasi CIF, dan pos tarif HS Code otomatis.</p>
                </div>
            </div>
            <div class="w-full sm:w-72">
                <select onchange="if(this.value) window.location.href='{{ route('bc20.create') }}?shipment_id=' + this.value; else window.location.href='{{ route('bc20.create') }}';" 
                        class="w-full px-3 py-2 rounded-xl border border-sky-300 dark:border-sky-700 bg-white dark:bg-[#2C2C2E] font-semibold text-slate-800 dark:text-white focus:ring-2 focus:ring-[#007AFF]">
                    <option value="">-- Pilih Proyek Shipment --</option>
                    @foreach($shipments as $s)
                        <option value="{{ $s->id }}" {{ $selectedShipment?->id === $s->id ? 'selected' : '' }}>
                            {{ $s->shipment_number }} - BL: {{ $s->bl_number }} ({{ $s->client->name }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        @if($selectedShipment)
            <div class="mt-2.5 pt-2.5 border-t border-sky-200/60 dark:border-sky-800/40 flex items-center justify-between text-[11px] text-sky-900 dark:text-sky-300 font-medium">
                <span>&check; Menggunakan data proyek: <strong>{{ $selectedShipment->shipment_number }}</strong> (Klien: {{ $selectedShipment->client->name }})</span>
                <a href="{{ route('bc20.create') }}" class="text-rose-600 dark:text-rose-400 hover:underline">Reset Pilihan</a>
            </div>
        @endif
    </div>

    <form action="{{ route('bc20.store') }}" method="POST" id="bc20Form" class="space-y-6">
        @csrf
        @if($selectedShipment)
            <input type="hidden" name="shipment_id" value="{{ $selectedShipment->id }}">
        @endif

        <!-- 1. Header & Kantor Pabean -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-6 border border-slate-200/80 dark:border-white/10 shadow-xs">
            <div class="flex items-center space-x-2 text-slate-800 dark:text-white font-bold text-sm mb-4 pb-2 border-b border-slate-100 dark:border-white/10">
                <span class="w-6 h-6 rounded-lg bg-sky-100 dark:bg-sky-950/50 text-[#007AFF] flex items-center justify-center text-xs font-bold">1</span>
                <span>Kantor Pabean & Jenis Transaksi</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Kantor Pabean Bongkar / Tujuan</label>
                    <select name="kode_kantor" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white focus:ring-2 focus:ring-[#007AFF]">
                        @foreach($kantorList as $code => $name)
                            <option value="{{ $code }}" {{ $code === '040300' ? 'selected' : '' }}>{{ $code }} - {{ $name }}</option>
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
                            <input type="text" value="{{ $selectedShipment?->client?->name ?? config('ceisa.company.name') }}" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#1C1C1E] font-semibold text-slate-800 dark:text-white" readonly>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-slate-500 dark:text-slate-400 font-medium">NPWP 15/16 Digit</label>
                                <input type="text" value="{{ $selectedShipment?->client?->npwp ?? config('ceisa.company.npwp') }}" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#1C1C1E] font-mono tabular-nums text-slate-700 dark:text-slate-300" readonly>
                            </div>
                            <div>
                                <label class="block text-slate-500 dark:text-slate-400 font-medium">NIB</label>
                                <input type="text" value="{{ $selectedShipment?->client?->nib ?? config('ceisa.company.nib') }}" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#1C1C1E] font-mono tabular-nums text-slate-700 dark:text-slate-300" readonly>
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
                                   value="{{ old('supplier_nama', $selectedShipment ? ($selectedShipment->client->name . ' OVERSEAS CO., LTD') : 'SHENZHEN ELECTRONICS TRADING CO., LTD.') }}" 
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white focus:ring-2 focus:ring-[#007AFF]" required>
                        </div>
                        <div>
                            <label class="block text-slate-600 dark:text-slate-300 font-semibold mb-1">Negara Asal Supplier *</label>
                            <select name="supplier_negara" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white focus:ring-2 focus:ring-[#007AFF]" required>
                                <option value="CN" selected>CN - Tiongkok (China)</option>
                                <option value="SG">SG - Singapura</option>
                                <option value="JP">JP - Jepang</option>
                                <option value="KR">KR - Korea Selatan</option>
                                <option value="US">US - Amerika Serikat</option>
                                <option value="DE">DE - Jerman</option>
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
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-xs mb-4">
                <div class="sm:col-span-2">
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Sarana Pengangkut (Vessel / Kapal) *</label>
                    <input type="text" name="nama_sarana_pengangkut" 
                           value="{{ old('nama_sarana_pengangkut', $selectedShipment ? ($selectedShipment->vessel_name . ' VOY. ' . $selectedShipment->voyage_number) : 'COSCO SHIPPING TAURUS VOY. 021E') }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white focus:ring-2 focus:ring-[#007AFF]" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Pelabuhan Muat *</label>
                    <input type="text" name="pelabuhan_muat" 
                           value="{{ old('pelabuhan_muat', $selectedShipment->port_of_loading ?? 'CNSHA') }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Pelabuhan Bongkar *</label>
                    <input type="text" name="pelabuhan_bongkar" 
                           value="{{ old('pelabuhan_bongkar', $selectedShipment->port_of_discharge ?? 'IDTPP') }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono" required>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs pt-3 border-t border-slate-100 dark:border-white/10">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nomor Invoice Komersial *</label>
                    <input type="text" name="invoice_no" 
                           value="{{ old('invoice_no', $selectedShipment ? ('INV/' . $selectedShipment->bl_number) : ('INV-EXP-' . date('Ymd') . '-01')) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Invoice *</label>
                    <input type="date" name="invoice_date" 
                           value="{{ old('invoice_date', $selectedShipment?->eta ? $selectedShipment->eta->format('Y-m-d') : date('Y-m-d')) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nomor Bill of Lading (B/L) *</label>
                    <input type="text" name="bl_no" 
                           value="{{ old('bl_no', $selectedShipment->bl_number ?? 'COSU639102830') }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono" required>
                </div>
            </div>
        </div>

        <!-- 3B. Manifes Kedatangan BC 1.1 & Tempat Penimbunan Sementara (TPS) -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-6 border border-slate-200/80 dark:border-white/10 shadow-xs">
            <div class="flex items-center space-x-2 text-slate-800 dark:text-white font-bold text-sm mb-4 pb-2 border-b border-slate-100 dark:border-white/10">
                <span class="w-6 h-6 rounded-lg bg-indigo-100 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-400 flex items-center justify-center text-xs font-bold">3B</span>
                <span>Manifes Kedatangan BC 1.1 & TPS Pabean (Mandatory CEISA 4.0)</span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs mb-3">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nomor BC 1.1 *</label>
                    <input type="text" name="nomor_bc11" value="{{ old('nomor_bc11', '004567') }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] font-mono font-bold text-indigo-700 dark:text-indigo-400" placeholder="6 digit no BC 1.1" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal BC 1.1 *</label>
                    <input type="date" name="tanggal_bc11" value="{{ old('tanggal_bc11', date('Y-m-d')) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nomor Pos BC 1.1 *</label>
                    <input type="text" name="pos_bc11" value="{{ old('pos_bc11', '0012') }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] font-mono font-bold text-slate-900 dark:text-white" placeholder="Pos (4 digit)" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Subpos BC 1.1</label>
                    <input type="text" name="subpos_bc11" value="{{ old('subpos_bc11', '0000') }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] font-mono text-slate-900 dark:text-white" placeholder="Subpos (4 digit)">
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs pt-3 border-t border-slate-100 dark:border-white/10">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Kode TPS (Tempat Penimbunan) *</label>
                    <input type="text" name="kode_tps" value="{{ old('kode_tps', 'TPS01') }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] font-mono font-bold text-slate-900 dark:text-white" placeholder="Contoh: KOJA / JICT / TPS01" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Perkiraan Tanggal Tiba (ETA) *</label>
                    <input type="date" name="tanggal_tiba" value="{{ old('tanggal_tiba', $selectedShipment?->eta ? $selectedShipment->eta->format('Y-m-d') : date('Y-m-d')) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Kode Tutup PU</label>
                    <input type="text" value="11 - Pos Ditutup Total" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 text-slate-500 dark:text-slate-400 font-mono" readonly>
                </div>
            </div>
        </div>

        <!-- 4. Valuasi & Kurs KMK (NDBM) -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-6 border border-slate-200/80 dark:border-white/10 shadow-xs">
            <div class="flex items-center justify-between pb-2 mb-4 border-b border-slate-100 dark:border-white/10">
                <div class="flex items-center space-x-2 text-slate-800 dark:text-white font-bold text-sm">
                    <span class="w-6 h-6 rounded-lg bg-sky-100 dark:bg-sky-950/50 text-[#007AFF] flex items-center justify-center text-xs font-bold">4</span>
                    <span>Valuasi Pabean & Kurs NDBM (KMK)</span>
                </div>
                <div class="flex items-center space-x-2 text-[11px] text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-[#2C2C2E] px-2.5 py-1 rounded-lg border border-slate-200 dark:border-white/10">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>KMK: <strong id="kmkBadge" class="text-slate-800 dark:text-white">{{ $activeKurs['nomor_kmk'] ?? 'KMK-NDPBM' }}</strong></span>
                    <span class="text-slate-400 dark:text-white/20">|</span>
                    <span id="kmkPeriode" class="text-slate-500 dark:text-slate-400 font-mono">{{ $activeKurs['tanggal_mulai'] ?? '' }} s/d {{ $activeKurs['tanggal_selesai'] ?? '' }}</span>
                </div>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs mb-4">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Valuta Asing</label>
                    <select name="kode_valuta" id="kode_valuta" onchange="fetchKurs(this.value)" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono font-bold focus:ring-2 focus:ring-[#007AFF]">
                        <option value="USD" {{ ($selectedShipment->currency ?? 'USD') === 'USD' ? 'selected' : '' }}>USD (US Dollar)</option>
                        <option value="EUR" {{ ($selectedShipment->currency ?? '') === 'EUR' ? 'selected' : '' }}>EUR (Euro)</option>
                        <option value="CNY" {{ ($selectedShipment->currency ?? '') === 'CNY' ? 'selected' : '' }}>CNY (Chinese Yuan)</option>
                        <option value="JPY" {{ ($selectedShipment->currency ?? '') === 'JPY' ? 'selected' : '' }}>JPY (Japanese Yen)</option>
                        <option value="SGD" {{ ($selectedShipment->currency ?? '') === 'SGD' ? 'selected' : '' }}>SGD (Singapore Dollar)</option>
                        <option value="AUD" {{ ($selectedShipment->currency ?? '') === 'AUD' ? 'selected' : '' }}>AUD (Australian Dollar)</option>
                    </select>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="font-semibold text-slate-700 dark:text-slate-300">Kurs KMK (NDPBM) Rp *</label>
                        <button type="button" onclick="fetchKurs(document.getElementById('kode_valuta').value)" class="text-[10px] text-[#007AFF] hover:underline font-bold">Refresh Kurs</button>
                    </div>
                    <input type="number" step="0.01" name="ndbm_kurs" id="ndbm_kurs" 
                           value="{{ old('ndbm_kurs', $activeKurs['nilai_kurs'] ?? ($selectedShipment->ndbm_kurs ?? 15850.00)) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] font-mono font-bold text-emerald-700 dark:text-emerald-400 tabular-nums" required oninput="calculateCif()">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nilai FOB *</label>
                    <input type="number" step="0.01" name="fob" id="fob" 
                           value="{{ old('fob', $selectedShipment->total_cif ?? 25000.00) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] font-mono font-bold text-[#007AFF] tabular-nums" required oninput="calculateCif()">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Freight *</label>
                    <input type="number" step="0.01" name="freight" id="freight" value="1200.00" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] font-mono text-slate-800 dark:text-white tabular-nums" required oninput="calculateCif()">
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs pt-3 border-t border-slate-100 dark:border-white/10">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Asuransi *</label>
                    <input type="number" step="0.01" name="asuransi" id="asuransi" value="150.00" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] font-mono text-slate-800 dark:text-white tabular-nums" required oninput="calculateCif()">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Berat Bruto (KG) *</label>
                    <input type="number" step="0.01" name="bruto" 
                           value="{{ old('bruto', $selectedShipment->total_bruto ?? 8500.00) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] font-mono text-slate-800 dark:text-white tabular-nums" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Berat Netto (KG) *</label>
                    <input type="number" step="0.01" name="netto" 
                           value="{{ old('netto', $selectedShipment->total_netto ?? 7800.00) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] font-mono text-slate-800 dark:text-white tabular-nums" required>
                </div>
                <div class="p-3 rounded-xl bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-800/60">
                    <span class="block text-[10px] font-bold text-[#007AFF] uppercase">Total CIF Pabean</span>
                    <span id="cifDisplay" class="font-mono font-black text-sm text-sky-900 dark:text-sky-200 tabular-nums">{{ $selectedShipment->currency ?? 'USD' }} {{ number_format(($selectedShipment->total_nilai_valuta ?? 26350.00), 2, ',', '.') }}</span>
                    <div id="cifIdrDisplay" class="text-[10px] text-sky-700 dark:text-sky-400 font-medium tabular-nums">Rp {{ number_format(($selectedShipment->total_nilai_idr ?? 426870000), 0, ',', '.') }}</div>
                </div>
            </div>
        </div>

        <!-- 5. Rincian Barang & Perhitungan Tarif -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-6 border border-slate-200/80 dark:border-white/10 shadow-xs">
            <div class="flex items-center space-x-2 text-slate-800 dark:text-white font-bold text-sm mb-4 pb-2 border-b border-slate-100 dark:border-white/10">
                <span class="w-6 h-6 rounded-lg bg-sky-100 dark:bg-sky-950/50 text-[#007AFF] flex items-center justify-center text-xs font-bold">5</span>
                <span>Rincian Barang & Pos Tarif BTKI (BM, PPN 11%, PPh 22)</span>
            </div>

            @php
                $itemList = $selectedShipment && $selectedShipment->items->isNotEmpty() ? $selectedShipment->items : [
                    (object)[
                        'kode_hs' => '8452.29.00',
                        'uraian_barang' => 'INDUSTRIAL SEWING MACHINE HEAVY DUTY COMPLETE SET',
                        'jumlah_kemasan' => 50,
                        'jenis_kemasan' => 'CTN',
                        'nilai_fob' => 12500.00,
                        'tarif_bm_persen' => 5.0,
                        'tarif_ppn_persen' => 11.0,
                        'tarif_pph_persen' => 2.5,
                    ]
                ];
            @endphp

            @foreach($itemList as $idx => $item)
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-[#2C2C2E]/60 border border-slate-200 dark:border-white/10 mb-4">
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs mb-3">
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">HS Code (BTKI) *</label>
                            <input type="text" name="barang[{{ $idx }}][hs_code]" 
                                   value="{{ str_replace('.', '', $item->kode_hs ?? '84522900') }}" 
                                   onchange="lookupHsRow(this, {{ $idx }})"
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white font-mono" required>
                            <div id="hs_badge_{{ $idx }}" class="hidden mt-1 p-1.5 rounded-lg text-[10px]"></div>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Uraian Komoditas *</label>
                            <input type="text" name="barang[{{ $idx }}][uraian]" 
                                   value="{{ $item->uraian_barang ?? 'MESIN INDUSTRI' }}" 
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white" required>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Merek & Tipe</label>
                            <input type="text" name="barang[{{ $idx }}][merk]" value="MODEL DDL-8700" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-6 gap-3 text-xs">
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Jumlah</label>
                            <input type="number" name="barang[{{ $idx }}][jumlah]" 
                                   value="{{ $item->jumlah_kemasan ?? 50 }}" 
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white font-mono tabular-nums" required>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Kemasan</label>
                            <input type="text" name="barang[{{ $idx }}][kemasan]" 
                                   value="{{ $item->jenis_kemasan ?? 'CTN' }}" 
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white font-mono" required>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">FOB Valas *</label>
                            <input type="number" step="0.01" name="barang[{{ $idx }}][fob]" 
                                   value="{{ $item->nilai_fob ?? 12500.00 }}" 
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white font-mono tabular-nums" required>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">BM (%) *</label>
                            <input type="number" step="0.1" name="barang[{{ $idx }}][tarif_bm]" 
                                   value="{{ $item->tarif_bm_persen ?? 5.0 }}" 
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white font-mono tabular-nums" required>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">PPN (%) *</label>
                            <input type="number" step="0.1" name="barang[{{ $idx }}][tarif_ppn]" 
                                   value="{{ $item->tarif_ppn_persen ?? 11.0 }}" 
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white font-mono tabular-nums" required>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">PPh 22 (%) *</label>
                            <input type="number" step="0.1" name="barang[{{ $idx }}][tarif_pph]" 
                                   value="{{ $item->tarif_pph_persen ?? 2.5 }}" 
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-white/15 dark:bg-[#1C1C1E] dark:text-white font-mono tabular-nums" required>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- 6. Kontainer & Segel -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-6 border border-slate-200/80 dark:border-white/10 shadow-xs">
            <div class="flex items-center space-x-2 text-slate-800 dark:text-white font-bold text-sm mb-4 pb-2 border-b border-slate-100 dark:border-white/10">
                <span class="w-6 h-6 rounded-lg bg-sky-100 dark:bg-sky-950/50 text-[#007AFF] flex items-center justify-center text-xs font-bold">6</span>
                <span>Petikemas (Kontainer) & Segel Pengaman</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nomor Kontainer *</label>
                    <input type="text" name="nomor_kontainer" 
                           value="{{ old('nomor_kontainer', $selectedShipment->nomor_kontainer ?? 'TGHU8192031') }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono font-bold" placeholder="Contoh: TGHU8192031" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Ukuran & Tipe *</label>
                    <select name="ukuran_kontainer" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white font-mono">
                        <option value="40" selected>40 Feet (High Cube / Standard)</option>
                        <option value="20">20 Feet Standard</option>
                        <option value="45">45 Feet High Cube</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Jenis Kontainer</label>
                    <input type="text" value="FCL - Full Container Load" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 text-slate-500 dark:text-slate-400" readonly>
                </div>
            </div>
        </div>

        <!-- Tombol Submit & Action Bar -->
        <div class="flex items-center justify-between pt-4">
            <a href="{{ route('bc20.index') }}" class="btn-apple-gray">
                &larr; Batal & Kembali
            </a>
            <button type="submit" class="btn-apple-filled">
                <span>Simpan Dokumen BC 2.0 & Siapkan Payload</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </button>
        </div>
    </form>
</div>

<script>
    async function fetchKurs(valuta) {
        if (!valuta) return;
        const kursInput = document.getElementById('ndbm_kurs');
        const badge = document.getElementById('kmkBadge');
        const periode = document.getElementById('kmkPeriode');

        kursInput.classList.add('opacity-50');
        try {
            const res = await fetch(`/ceisa/kurs/${valuta}`);
            const data = await res.json();
            if (data && data.nilai_kurs) {
                kursInput.value = parseFloat(data.nilai_kurs).toFixed(2);
                if (badge) badge.innerText = data.nomor_kmk || 'KMK-NDPBM';
                if (periode) periode.innerText = (data.tanggal_mulai || '') + ' s/d ' + (data.tanggal_selesai || '');
                calculateCif();
            }
        } catch (e) {
            console.error('Gagal fetch kurs CEISA:', e);
        } finally {
            kursInput.classList.remove('opacity-50');
        }
    }

    function calculateCif() {
        const kurs = parseFloat(document.getElementById('ndbm_kurs').value) || 0;
        const fob = parseFloat(document.getElementById('fob').value) || 0;
        const freight = parseFloat(document.getElementById('freight').value) || 0;
        const asuransi = parseFloat(document.getElementById('asuransi').value) || 0;

        const cif = fob + freight + asuransi;
        const cifIdr = cif * kurs;

        const valuta = document.getElementById('kode_valuta')?.value || 'USD';
        document.getElementById('cifDisplay').innerText = valuta + ' ' + cif.toLocaleString('id-ID', { minimumFractionDigits: 2 });
        document.getElementById('cifIdrDisplay').innerText = 'Rp ' + Math.round(cifIdr).toLocaleString('id-ID');
    }

    async function lookupHsRow(input, idx) {
        const val = input.value.replace(/[^0-9]/g, '');
        if (!val || val.length < 4) return;
        const badge = document.getElementById(`hs_badge_${idx}`);
        try {
            const res = await fetch(`/ceisa/tarif-hs/${val}`);
            const data = await res.json();
            if (data && data.success) {
                const uraianInput = document.querySelector(`input[name="barang[${idx}][uraian]"]`);
                const bmInput = document.querySelector(`input[name="barang[${idx}][tarif_bm]"]`);
                const ppnInput = document.querySelector(`input[name="barang[${idx}][tarif_ppn]"]`);
                const pphInput = document.querySelector(`input[name="barang[${idx}][tarif_pph]"]`);

                if (uraianInput && (!uraianInput.value || uraianInput.value === 'MESIN INDUSTRI')) {
                    uraianInput.value = data.desc || '';
                }
                if (bmInput && data.bm !== undefined) bmInput.value = data.bm;
                if (ppnInput && data.ppn !== undefined) ppnInput.value = data.ppn;
                if (pphInput && data.pph !== undefined) pphInput.value = data.pph;

                if (badge) {
                    badge.classList.remove('hidden');
                    if (data.has_lartas) {
                        badge.className = 'mt-1 p-1.5 rounded-lg bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300 text-[10px] font-medium';
                        badge.innerHTML = `⚠️ <strong>Lartas:</strong> ${data.lartas}`;
                    } else {
                        badge.className = 'mt-1 p-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-[10px] font-medium';
                        badge.innerHTML = `✓ Bebas Lartas (Non-Lartas)`;
                    }
                }
            }
        } catch (e) {
            console.error('Error lookup HS row:', e);
        }
    }

    document.addEventListener('DOMContentLoaded', calculateCif);
</script>
@endsection
