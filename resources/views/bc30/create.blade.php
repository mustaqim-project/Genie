@extends('layouts.app')

@section('title', 'Form Pengajuan BC 3.0 (PEB)')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-16">
    <!-- Breadcrumb -->
    <div class="text-xs text-slate-500 font-medium mb-2">
        <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a> / 
        <a href="{{ route('core.document.index') }}" class="hover:underline">Tim Document</a> / 
        <a href="{{ route('bc30.index') }}" class="hover:underline">BC 3.0 (Ekspor)</a> / 
        <span class="text-slate-800">Form Pengajuan Baru</span>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Formulir Pabean BC 3.0 (PEB)</h1>
            <p class="text-xs text-slate-500 mt-0.5">Pemberitahuan Ekspor Barang (Host-to-Host CEISA 4.0 DJBC)</p>
        </div>
        <div class="flex items-center space-x-2">
            <span class="text-xs font-mono bg-slate-100 px-3 py-1.5 rounded-xl border border-slate-200 text-slate-700">
                Aju ID: {{ $defaultNomorAju }}
            </span>
        </div>
    </div>

    <!-- Integrasi Cepat: Pilih dari Proyek Shipment Tim Document -->
    <div class="bg-gradient-to-r from-amber-50 to-indigo-50 rounded-2xl p-4 border border-amber-200/80 shadow-sm mb-6 text-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center space-x-3">
                <span class="text-2xl">⚡</span>
                <div>
                    <strong class="font-bold text-slate-900">Import Otomatis dari Proyek Shipment Ekspor</strong>
                    <p class="text-[11px] text-slate-600">Pilih proyek pengapalan ekspor aktif untuk mengisi data sarana pengangkut, valuasi FOB, dan pos tarif HS Code otomatis.</p>
                </div>
            </div>
            <div class="w-full sm:w-72">
                <select onchange="if(this.value) window.location.href='{{ route('bc30.create') }}?shipment_id=' + this.value; else window.location.href='{{ route('bc30.create') }}';" 
                        class="w-full px-3 py-2 rounded-xl border border-amber-300 bg-white font-semibold text-slate-800 focus:ring-2 focus:ring-amber-500">
                    <option value="">-- Pilih Proyek Ekspor --</option>
                    @foreach($shipments as $s)
                        <option value="{{ $s->id }}" {{ $selectedShipment?->id === $s->id ? 'selected' : '' }}>
                            {{ $s->shipment_number }} - BL: {{ $s->bl_number }} ({{ $s->client->name }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        @if($selectedShipment)
            <div class="mt-2.5 pt-2.5 border-t border-amber-200/60 flex items-center justify-between text-[11px] text-amber-900 font-medium">
                <span>&check; Menggunakan data proyek: <strong>{{ $selectedShipment->shipment_number }}</strong> (Eksportir: {{ $selectedShipment->client->name }})</span>
                <a href="{{ route('bc30.create') }}" class="text-rose-600 hover:underline">Reset Pilihan</a>
            </div>
        @endif
    </div>

    <form action="{{ route('bc30.store') }}" method="POST" id="bc30Form" class="space-y-6">
        @csrf
        @if($selectedShipment)
            <input type="hidden" name="shipment_id" value="{{ $selectedShipment->id }}">
        @endif

        <!-- 1. Header & Kantor Pabean -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <div class="flex items-center space-x-2 text-slate-800 font-bold text-sm mb-4 pb-2 border-b border-slate-100">
                <span class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold">1</span>
                <span>Kantor Pabean Muat & Kategori Ekspor</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Kantor Pabean Pemuatan Ekspor</label>
                    <select name="kode_kantor" class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-brand-500">
                        @foreach($kantorList as $code => $name)
                            <option value="{{ $code }}" {{ $code === '040300' ? 'selected' : '' }}>{{ $code }} - {{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Kategori Ekspor</label>
                    <input type="text" value="1 - Ekspor Umum / Biasa" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-500" readonly>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Cara Perdagangan</label>
                    <input type="text" value="1 - Biasa (Tanpa Imbal Dagang)" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-500" readonly>
                </div>
            </div>
        </div>

        <!-- 2. Data Entitas Stakeholder -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <div class="flex items-center space-x-2 text-slate-800 font-bold text-sm mb-4 pb-2 border-b border-slate-100">
                <span class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold">2</span>
                <span>Entitas Kepabeanan Ekspor</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs">
                <!-- Eksportir (Lokal) -->
                <div class="p-4 rounded-xl bg-slate-50/70 border border-slate-200">
                    <span class="inline-block px-2 py-0.5 rounded bg-indigo-100 text-indigo-800 font-bold text-[10px] mb-2">EKSPORTIR (KODE 2)</span>
                    <div class="space-y-2">
                        <div>
                            <label class="block text-slate-500 font-medium">Nama Perusahaan Eksportir</label>
                            <input type="text" value="{{ $selectedShipment?->client?->name ?? config('ceisa.company.name') }}" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 bg-white font-semibold text-slate-800" readonly>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-slate-500 font-medium">NPWP 15/16 Digit</label>
                                <input type="text" value="{{ $selectedShipment?->client?->npwp ?? config('ceisa.company.npwp') }}" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 bg-white font-mono" readonly>
                            </div>
                            <div>
                                <label class="block text-slate-500 font-medium">NIB</label>
                                <input type="text" value="{{ $selectedShipment?->client?->nib ?? config('ceisa.company.nib') }}" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 bg-white font-mono" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pembeli Luar Negeri -->
                <div class="p-4 rounded-xl bg-slate-50/70 border border-slate-200">
                    <span class="inline-block px-2 py-0.5 rounded bg-slate-200 text-slate-800 font-bold text-[10px] mb-2">PEMBELI LUAR NEGERI (KODE 8)</span>
                    <div class="space-y-2">
                        <div>
                            <label class="block text-slate-600 font-semibold mb-1">Nama Pembeli (Buyer) *</label>
                            <input type="text" name="buyer_nama" 
                                   value="{{ old('buyer_nama', 'GLOBAL COMMODITIES TRADING PTE LTD') }}" 
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-brand-500" required>
                        </div>
                        <div>
                            <label class="block text-slate-600 font-semibold mb-1">Negara Tujuan Ekspor *</label>
                            <select name="buyer_negara" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 focus:ring-2 focus:ring-brand-500" required>
                                <option value="SG" selected>SG - Singapura</option>
                                <option value="US">US - Amerika Serikat</option>
                                <option value="JP">JP - Jepang</option>
                                <option value="DE">DE - Jerman</option>
                                <option value="NL">NL - Belanda</option>
                                <option value="AU">AU - Australia</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Transportasi & Dokumen Pelengkap -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <div class="flex items-center space-x-2 text-slate-800 font-bold text-sm mb-4 pb-2 border-b border-slate-100">
                <span class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold">3</span>
                <span>Pengangkutan Ekspor & Dokumen</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-xs mb-4">
                <div class="sm:col-span-2">
                    <label class="block font-semibold text-slate-700 mb-1">Nama Kapal / Sarana Pengangkut Ekspor *</label>
                    <input type="text" name="nama_sarana_pengangkut" 
                           value="{{ old('nama_sarana_pengangkut', $selectedShipment ? ($selectedShipment->vessel_name . ' VOY. ' . $selectedShipment->voyage_number) : 'EVER GIVEN VOY. 082W') }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-brand-500" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Pelabuhan Muat *</label>
                    <input type="text" name="pelabuhan_muat" 
                           value="{{ old('pelabuhan_muat', $selectedShipment->port_of_discharge ?? 'IDTPP') }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Pelabuhan Tujuan Bongkar *</label>
                    <input type="text" name="pelabuhan_bongkar" 
                           value="{{ old('pelabuhan_bongkar', 'SGSIN') }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono" required>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs pt-3 border-t border-slate-100">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nomor Ekspor Invoice *</label>
                    <input type="text" name="invoice_no" 
                           value="{{ old('invoice_no', $selectedShipment ? ('EXP-INV/' . $selectedShipment->bl_number) : ('INV-EXP-' . date('Ymd') . '-01')) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Tanggal Invoice Ekspor *</label>
                    <input type="date" name="invoice_date" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 rounded-xl border border-slate-200" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nomor Booking Pelayaran / BL *</label>
                    <input type="text" name="bl_no" 
                           value="{{ old('bl_no', $selectedShipment->bl_number ?? 'BKG-EGLV-918230') }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono" required>
                </div>
            </div>
        </div>

        <!-- 3B. Kesiapan Barang (PKB / Stuffing) & Bank Devisa (DHE) -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <div class="flex items-center space-x-2 text-slate-800 font-bold text-sm mb-4 pb-2 border-b border-slate-100">
                <span class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold">3B</span>
                <span>Kesiapan Barang Ekspor & Bank Devisa DHE (Mandatory CEISA 4.0)</span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs mb-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nama PIC Barang *</label>
                    <input type="text" name="pic_pembuat" value="{{ old('pic_pembuat', 'BAMBANG SUTRISNO') }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 font-bold" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">No. HP / Telepon PIC *</label>
                    <input type="text" name="telepon_pembuat" value="{{ old('telepon_pembuat', '081298765432') }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono font-bold" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Lokasi Siap Periksa / Stuffing *</label>
                    <input type="text" name="lokasi_siap_periksa" value="{{ old('lokasi_siap_periksa', 'GUDANG EKSPORTIR CAKUNG') }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Tanggal Kesiapan Periksa *</label>
                    <input type="date" name="tanggal_periksa" value="{{ old('tanggal_periksa', date('Y-m-d')) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200" required>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs pt-3 border-t border-slate-100">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Bank Devisa Hasil Ekspor (DHE) *</label>
                    <input type="text" name="bank_nama" value="{{ old('bank_nama', 'PT BANK MANDIRI (PERSERO) TBK') }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 font-semibold" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Kode Sandi Bank Devisa (Swift / Sandi BI) *</label>
                    <input type="text" name="bank_kode" value="{{ old('bank_kode', 'BMRI') }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono font-bold" required>
                </div>
            </div>
        </div>

        <!-- 4. Valuasi Nilai Ekspor (FOB) -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between pb-2 mb-4 border-b border-slate-100">
                <div class="flex items-center space-x-2 text-slate-800 font-bold text-sm">
                    <span class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold">4</span>
                    <span>Valuasi Ekspor (FOB) & Kurs KMK (NDPBM)</span>
                </div>
                <div class="flex items-center space-x-2 text-[11px] text-slate-600 bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>KMK: <strong id="kmkBadge" class="text-slate-800">{{ $activeKurs['nomor_kmk'] ?? 'KMK-NDPBM' }}</strong></span>
                    <span class="text-slate-400">|</span>
                    <span id="kmkPeriode" class="text-slate-500 font-mono">{{ $activeKurs['tanggal_mulai'] ?? '' }} s/d {{ $activeKurs['tanggal_selesai'] ?? '' }}</span>
                </div>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs mb-4">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Valuta</label>
                    <select name="kode_valuta" id="kode_valuta" onchange="fetchKurs(this.value)" class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono font-bold focus:ring-2 focus:ring-indigo-500">
                        <option value="USD" {{ ($selectedShipment->currency ?? 'USD') === 'USD' ? 'selected' : '' }}>USD (US Dollar)</option>
                        <option value="EUR" {{ ($selectedShipment->currency ?? '') === 'EUR' ? 'selected' : '' }}>EUR (Euro)</option>
                        <option value="SGD" {{ ($selectedShipment->currency ?? '') === 'SGD' ? 'selected' : '' }}>SGD (Singapore Dollar)</option>
                        <option value="CNY" {{ ($selectedShipment->currency ?? '') === 'CNY' ? 'selected' : '' }}>CNY (Chinese Yuan)</option>
                        <option value="JPY" {{ ($selectedShipment->currency ?? '') === 'JPY' ? 'selected' : '' }}>JPY (Japanese Yen)</option>
                    </select>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="font-semibold text-slate-700">Kurs KMK (NDPBM) Rp *</label>
                        <button type="button" onclick="fetchKurs(document.getElementById('kode_valuta').value)" class="text-[10px] text-indigo-600 hover:underline font-bold">Refresh Kurs</button>
                    </div>
                    <input type="number" step="0.01" name="ndbm_kurs" id="ndbm_kurs" 
                           value="{{ old('ndbm_kurs', $activeKurs['nilai_kurs'] ?? ($selectedShipment->ndbm_kurs ?? 15850.00)) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono font-bold text-emerald-700" required oninput="calculateFob()">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Total Nilai FOB ({{ $selectedShipment->currency ?? 'USD' }}) *</label>
                    <input type="number" step="0.01" name="fob" id="fob" 
                           value="{{ old('fob', $selectedShipment->total_cif ?? 18000.00) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono font-bold text-indigo-700" required oninput="calculateFob()">
                </div>
                <div class="p-3 rounded-xl bg-indigo-50 border border-indigo-200">
                    <span class="block text-[10px] font-bold text-indigo-700 uppercase">FOB Rupiah Ekspor</span>
                    <span id="fobIdrDisplay" class="font-mono font-black text-sm text-indigo-900">Rp 291.600.000</span>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-2 gap-4 text-xs pt-3 border-t border-slate-100">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Total Berat Bruto (KG) *</label>
                    <input type="number" step="0.01" name="bruto" 
                           value="{{ old('bruto', $selectedShipment->total_bruto ?? 20500.00) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono" required>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Total Berat Netto (KG) *</label>
                    <input type="number" step="0.01" name="netto" 
                           value="{{ old('netto', $selectedShipment->total_netto ?? 20000.00) }}" 
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono" required>
                </div>
            </div>
        </div>

        <!-- 5. Rincian Barang Ekspor & Bea Keluar -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <div class="flex items-center space-x-2 text-slate-800 font-bold text-sm mb-4 pb-2 border-b border-slate-100">
                <span class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold">5</span>
                <span>Rincian Barang Ekspor & Pos Tarif BTKI</span>
            </div>

            @php
                $itemList = $selectedShipment && $selectedShipment->items->isNotEmpty() ? $selectedShipment->items : [
                    (object)[
                        'kode_hs' => '1511.10.00',
                        'uraian_barang' => 'INDONESIAN CRUDE PALM OIL (CPO) IN BULK / FLEXITANK',
                        'jumlah_kemasan' => 1,
                        'jenis_kemasan' => 'FLX',
                        'nilai_fob' => 18000.00,
                    ]
                ];
            @endphp

            @foreach($itemList as $idx => $item)
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 mb-4">
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs mb-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">HS Code (BTKI) *</label>
                            <input type="text" name="barang[{{ $idx }}][hs_code]" 
                                   value="{{ str_replace('.', '', $item->kode_hs ?? '15111000') }}" 
                                   onchange="lookupHsRow(this, {{ $idx }})"
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-200 font-mono" required>
                            <div id="hs_badge_{{ $idx }}" class="hidden mt-1 p-1.5 rounded-lg text-[10px]"></div>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block font-semibold text-slate-700 mb-1">Uraian Komoditas Ekspor *</label>
                            <input type="text" name="barang[{{ $idx }}][uraian]" 
                                   value="{{ $item->uraian_barang ?? 'KOMODITAS EKSPOR' }}" 
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-200" required>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Kemasan</label>
                            <input type="text" name="barang[{{ $idx }}][satuan]" 
                                   value="{{ $item->jenis_kemasan ?? 'FLX' }}" 
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-200">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Jumlah</label>
                            <input type="number" name="barang[{{ $idx }}][jumlah]" 
                                   value="{{ $item->jumlah_kemasan ?? 1 }}" 
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-200 font-mono" required>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Nilai FOB ({{ $selectedShipment->currency ?? 'USD' }})</label>
                            <input type="number" step="0.01" name="barang[{{ $idx }}][fob]" 
                                   value="{{ $item->nilai_fob ?? 18000.00 }}" 
                                   class="w-full px-3 py-1.5 rounded-lg border border-slate-200 font-mono font-bold" required>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tarif Bea Keluar (BK)</label>
                            <input type="number" step="0.1" name="barang[{{ $idx }}][tarif_bk]" value="0" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 font-mono">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Pungutan Sawit (USD/Ton)</label>
                            <input type="number" step="0.1" name="barang[{{ $idx }}][pungutan_sawit]" value="0" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 font-mono">
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Submit Controls -->
        <div class="flex items-center justify-between pt-4">
            <a href="{{ route('bc30.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
                &larr; Batal & Kembali
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-lg shadow-indigo-600/30 transition-all hover:-translate-y-0.5">
                Simpan Dokumen BC 3.0 & Siapkan Payload PEB &rarr;
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
                calculateFob();
            }
        } catch (e) {
            console.error('Gagal fetch kurs CEISA:', e);
        } finally {
            kursInput.classList.remove('opacity-50');
        }
    }

    function calculateFob() {
        const kurs = parseFloat(document.getElementById('ndbm_kurs').value) || 0;
        const fob = parseFloat(document.getElementById('fob').value) || 0;
        const fobIdr = fob * kurs;
        document.getElementById('fobIdrDisplay').innerText = 'Rp ' + Math.round(fobIdr).toLocaleString('id-ID');
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
                const bkInput = document.querySelector(`input[name="barang[${idx}][tarif_bk]"]`);

                if (uraianInput && (!uraianInput.value || uraianInput.value === 'KOMODITAS EKSPOR')) {
                    uraianInput.value = data.desc || '';
                }
                if (bkInput && data.bk !== undefined) {
                    bkInput.value = data.bk;
                }

                if (badge) {
                    badge.classList.remove('hidden');
                    if (data.has_lartas) {
                        badge.className = 'mt-1 p-1.5 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-[10px] font-medium';
                        badge.innerHTML = `⚠️ <strong>Lartas Ekspor:</strong> ${data.lartas}`;
                    } else {
                        badge.className = 'mt-1 p-1.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-[10px] font-medium';
                        badge.innerHTML = `✓ Bebas Lartas Ekspor`;
                    }
                }
            }
        } catch (e) {
            console.error('Error lookup HS row:', e);
        }
    }

    document.addEventListener('DOMContentLoaded', calculateFob);
</script>
@endsection
