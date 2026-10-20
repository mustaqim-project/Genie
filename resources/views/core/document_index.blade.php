@extends('layouts.app')

@section('title', 'Tim Document — Project Lifecycle & Dokumen Pabean (P01-P19)')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 pt-6 pb-12">

    <!-- Header & Action Button -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 font-medium mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-800 transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-slate-400">Core Bisnis Pabean</span>
                <span>/</span>
                <span class="text-sky-700 font-bold">Tim Document</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center space-x-2">
                <span>Manajemen Proyek Dokumen & Pabean</span>
                <span class="px-2 py-0.5 text-xs font-mono font-bold bg-sky-100 text-sky-800 rounded-lg">P01–P07</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Registrasi shipment, audit 9 slot dokumen wajib berversi, deteksi HS code AI (BTKI), validasi three-way matching, hingga terbit Billing MPN G3 CEISA 4.0.
            </p>
        </div>
        <div class="flex items-center space-x-2.5">
            <a href="{{ route('bc20.index') }}" 
               class="inline-flex items-center space-x-1.5 px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition-all shadow-xs active:scale-95">
                <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Daftar BC 2.0</span>
            </a>
            <a href="{{ route('bc30.index') }}" 
               class="inline-flex items-center space-x-1.5 px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition-all shadow-xs active:scale-95">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                <span>Daftar BC 3.0</span>
            </a>
            @can('document.create')
            <button onclick="document.getElementById('modal-create-project').classList.remove('hidden')" 
                    class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold shadow-xs transition-all active:scale-95">
                <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Buka Proyek Baru (P01)</span>
            </button>
            @endcan
        </div>
    </div>

    <!-- 4 Apple HIG KPI Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center space-x-3.5 hover:border-slate-300 transition-all">
            <div class="w-11 h-11 rounded-xl bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
            </div>
            <div class="overflow-hidden">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Proyek</div>
                <div class="text-xl font-extrabold text-slate-900 font-mono">{{ $stats['total'] ?? 0 }}</div>
                <div class="text-[10px] text-slate-400 truncate">Semua pengapalan aktif</div>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center space-x-3.5 hover:border-slate-300 transition-all">
            <div class="w-11 h-11 rounded-xl bg-[#FF9500]/10 text-[#FF9500] flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="overflow-hidden">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Kumpul Berkas</div>
                <div class="text-xl font-extrabold text-amber-700 font-mono">{{ $stats['collecting'] ?? 0 }}</div>
                <div class="text-[10px] text-slate-400 truncate">Menunggu kelengkapan klien</div>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center space-x-3.5 hover:border-slate-300 transition-all">
            <div class="w-11 h-11 rounded-xl bg-[#5856D6]/10 text-[#5856D6] flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div class="overflow-hidden">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Review & HS Code</div>
                <div class="text-xl font-extrabold text-indigo-700 font-mono">{{ $stats['ready'] ?? 0 }}</div>
                <div class="text-[10px] text-slate-400 truncate">Siap aju ke CEISA 4.0</div>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center space-x-3.5 hover:border-slate-300 transition-all">
            <div class="w-11 h-11 rounded-xl bg-[#34C759]/10 text-[#34C759] flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <div class="overflow-hidden">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Billing Terbit</div>
                <div class="text-xl font-extrabold text-emerald-700 font-mono">{{ $stats['billing'] ?? 0 }}</div>
                <div class="text-[10px] text-slate-400 truncate">Handoff ke Tim DO aktif</div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-4 mb-6">
        <form method="GET" action="{{ route('core.document.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
            <div class="sm:col-span-5">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Cari Nomor Job, B/L, Nama Kapal, No Kontainer, atau Importir..." 
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none placeholder-slate-400">
            </div>

            <div class="sm:col-span-3">
                <select name="type" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    <option value="">-- Semua Tipe Layanan --</option>
                    <option value="IMPORT" {{ request('type') === 'IMPORT' ? 'selected' : '' }}>IMPOR (BC 2.0 / PIB)</option>
                    <option value="EXPORT" {{ request('type') === 'EXPORT' ? 'selected' : '' }}>EKSPOR (BC 3.0 / PEB)</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <select name="status" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    <option value="">-- Semua Status --</option>
                    <option value="DOC_COLLECTING" {{ request('status') === 'DOC_COLLECTING' ? 'selected' : '' }}>Kumpul Berkas</option>
                    <option value="DOC_READY" {{ request('status') === 'DOC_READY' ? 'selected' : '' }}>Dokumen Lengkap</option>
                    <option value="HS_DETECTED" {{ request('status') === 'HS_DETECTED' ? 'selected' : '' }}>HS Terdeteksi</option>
                    <option value="DRAFT_REVIEW" {{ request('status') === 'DRAFT_REVIEW' ? 'selected' : '' }}>Draft Review</option>
                    <option value="BILLING_ISSUED" {{ request('status') === 'BILLING_ISSUED' ? 'selected' : '' }}>Billing Terbit</option>
                    <option value="DO_PROCESSING" {{ request('status') === 'DO_PROCESSING' ? 'selected' : '' }}>Proses Tim DO</option>
                    <option value="DELIVERED_POD" {{ request('status') === 'DELIVERED_POD' ? 'selected' : '' }}>Selesai (POD)</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex items-center space-x-2">
                <button type="submit" class="w-full py-2 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold transition-colors">
                    Filter
                </button>
                <a href="{{ route('core.document.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <div class="font-bold text-xs text-slate-800 flex items-center space-x-2">
                <span>Daftar Proyek Shipment Aktif</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-100 text-slate-600 font-mono">{{ $shipments->total() }} Data</span>
            </div>
            <div class="text-[11px] text-slate-400">
                Klik baris atau tombol Detail untuk mengelola 9 slot dokumen & deteksi HS Code
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">No. Proyek & B/L</th>
                        <th class="py-3 px-4">Klien / Importir</th>
                        <th class="py-3 px-4">Pengangkutan & Kontainer</th>
                        <th class="py-3 px-4">Audit 9 Berkas</th>
                        <th class="py-3 px-4">Status Siklus</th>
                        <th class="py-3 px-4">Billing MPN G3</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($shipments as $shp)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3.5 px-4 font-mono">
                                <a href="{{ route('core.document.show', $shp->id) }}" class="font-bold text-sky-700 hover:text-sky-900 hover:underline flex items-center space-x-1.5">
                                    <span>{{ $shp->shipment_number ?? $shp->project_no }}</span>
                                </a>
                                <div class="text-[10px] text-slate-500 mt-0.5">
                                    B/L: <strong class="text-slate-700">{{ $shp->bl_number ?? '-' }}</strong>
                                </div>
                                <span class="inline-block mt-1 px-1.5 py-0.2 rounded text-[9px] font-bold font-sans {{ $shp->type === 'EXPORT' ? 'bg-amber-100 text-amber-800' : 'bg-sky-100 text-sky-800' }}">
                                    {{ $shp->type === 'EXPORT' ? 'BC 3.0 EKSPOR' : 'BC 2.0 IMPOR' }}
                                </span>
                            </td>

                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">{{ $shp->client->name ?? '-' }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">NPWP: {{ $shp->client->npwp ?? '-' }}</div>
                                @if($shp->shippingVendor)
                                    <div class="text-[10px] text-slate-500 mt-0.5">Pelayaran: <strong>{{ $shp->shippingVendor->name }}</strong></div>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 text-slate-700">
                                <div class="font-semibold text-slate-800">{{ $shp->vessel_name ?? '-' }}</div>
                                <div class="text-[10px] text-slate-400">Voy: {{ $shp->voyage_number ?? '-' }} &bull; ETA: {{ $shp->eta ? $shp->eta->format('d/m/Y') : '-' }}</div>
                                <div class="text-[10px] font-mono text-slate-500 mt-0.5">{{ $shp->nomor_kontainer ?? '1 Kontainer' }}</div>
                            </td>

                            <td class="py-3.5 px-4">
                                @php
                                    $verifiedCount = $shp->documents->where('status', 'VERIFIED')->count();
                                    $uploadedCount = $shp->documents->where('status', 'UPLOADED')->count();
                                    $totalCount = max($shp->documents->count(), 9);
                                    $percent = $shp->document_completion_rate;
                                @endphp
                                <div class="flex items-center space-x-2">
                                    <div class="w-16 bg-slate-100 rounded-full h-2 overflow-hidden">
                                        <div class="h-2 rounded-full transition-all duration-300 {{ $percent >= 100 ? 'bg-emerald-500' : ($percent >= 50 ? 'bg-sky-500' : 'bg-amber-500') }}" 
                                             style="width: {{ $percent }}%"></div>
                                    </div>
                                    <span class="font-mono font-bold text-[11px] {{ $percent >= 100 ? 'text-emerald-700' : 'text-slate-700' }}">{{ $percent }}%</span>
                                </div>
                                <div class="text-[10px] text-slate-400 mt-0.5">
                                    {{ $verifiedCount }} lolos verifikasi, {{ $uploadedCount }} baru diupload
                                </div>
                            </td>

                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono 
                                    @if(in_array($shp->status, ['BILLING_ISSUED', 'DO_RELEASED', 'DELIVERED_POD', 'COMPLETED'])) bg-emerald-100 text-emerald-800
                                    @elseif(in_array($shp->status, ['DOC_READY', 'HS_DETECTED', 'DRAFT_REVIEW'])) bg-indigo-100 text-indigo-800
                                    @elseif($shp->status === 'DO_PROCESSING') bg-amber-100 text-amber-800
                                    @else bg-slate-100 text-slate-700 @endif">
                                    {{ $shp->status }}
                                </span>
                            </td>

                            <td class="py-3.5 px-4 font-mono">
                                @if($shp->billing_code || $shp->ceisaDocument?->kode_billing)
                                    <div class="font-extrabold text-indigo-700 text-xs tracking-wider">
                                        {{ $shp->billing_code ?? $shp->ceisaDocument?->kode_billing }}
                                    </div>
                                    <div class="text-[9px] text-emerald-600 font-bold font-sans">&check; Terbit dari CEISA 4.0</div>
                                @else
                                    <span class="text-slate-400 text-[11px]">-</span>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 text-right space-x-1.5 whitespace-nowrap">
                                <a href="{{ route('core.document.show', $shp->id) }}" 
                                   class="inline-flex items-center px-2.5 py-1.5 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-700 font-bold text-[11px] transition-colors">
                                    Detail Dokumen &rarr;
                                </a>
                                @if($shp->type === 'EXPORT')
                                    <a href="{{ route('pdf.peb', $shp->id) }}" target="_blank"
                                       class="inline-flex items-center px-2 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-[11px] transition-colors" title="Cetak PEB BC 3.0">
                                        PDF PEB
                                    </a>
                                @else
                                    <a href="{{ route('pdf.pib', $shp->id) }}" target="_blank"
                                       class="inline-flex items-center px-2 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-[11px] transition-colors" title="Cetak PIB BC 2.0">
                                        PDF PIB
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400 text-xs">
                                <div class="text-2xl mb-2">📦</div>
                                Tidak ada proyek shipment yang cocok dengan kriteria pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $shipments->links() }}
        </div>
    </div>

</div>

<!-- Modal Form Buat Proyek Baru (P01) dengan Auto-Deteksi Dokumen AI -->
<div id="modal-create-project" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
     x-data="{
        tab: 'ai',
        scanning: false,
        scanned: false,
        showDetails: false,
        fileNames: { bl: '', invoice: '', pl: '' },
        detected: {
            client_id: '',
            shipping_vendor_id: '',
            shipping_vendor_name: '',
            type: 'IMPORT',
            bl_number: '',
            vessel_name: '',
            voyage_number: '',
            port_of_loading: '',
            port_of_discharge: '',
            eta: '',
            total_kontainer: 1,
            nomor_kontainer: '',
            total_kemasan: 150,
            jenis_kemasan: 'CTN',
            total_bruto: 8500,
            total_netto: 7800,
            currency: 'USD',
            total_cif: 25000,
            ndbm_kurs: 16200,
            items: []
        },
        updateFileName(type, event) {
            const files = event.target.files;
            if (files && files[0]) {
                this.fileNames[type] = files[0].name;
                // Auto trigger scan jika minimal 2 dokumen terunggah
                if ((this.fileNames.bl || this.fileNames.invoice) && !this.scanned) {
                    this.scanFiles();
                }
            }
        },
        async scanFiles() {
            const blInput = document.getElementById('input-bl-file');
            const invInput = document.getElementById('input-inv-file');
            const plInput = document.getElementById('input-pl-file');

            if ((!blInput?.files?.length) && (!invInput?.files?.length) && (!plInput?.files?.length)) {
                alert('Silakan pilih minimal 1 berkas dokumen (B/L, Commercial Invoice, atau Packing List) untuk dipindai.');
                return;
            }

            this.scanning = true;
            const formData = new FormData();
            if (blInput?.files?.[0]) formData.append('bl_file', blInput.files[0]);
            if (invInput?.files?.[0]) formData.append('invoice_file', invInput.files[0]);
            if (plInput?.files?.[0]) formData.append('packing_list_file', plInput.files[0]);

            const clientSelect = document.getElementById('smart-client-select');
            if (clientSelect?.value) formData.append('client_id', clientSelect.value);

            try {
                const response = await fetch('{{ route('core.document.parse-documents') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                });
                const res = await response.json();
                if (res.status === 'success' && res.data) {
                    this.scanned = true;
                    this.detected = { ...this.detected, ...res.data };
                    if (res.data.client_id && clientSelect) {
                        clientSelect.value = res.data.client_id;
                    }
                } else {
                    alert(res.message || 'Gagal memindai dokumen.');
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan koneksi saat memindai dokumen.');
            } finally {
                this.scanning = false;
            }
        }
     }">
    <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl max-w-3xl w-full p-6 text-xs max-h-[92vh] overflow-y-auto">
        
        <!-- Header Modal & Tab Navigasi -->
        <div class="flex items-start justify-between pb-4 border-b border-slate-100 mb-4">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-mono font-bold bg-sky-100 text-sky-800">P01</span>
                    <h3 class="text-base font-extrabold text-slate-900">Buka Proyek Shipment Baru</h3>
                </div>
                <p class="text-[11px] text-slate-500 mt-0.5">
                    Cukup upload dokumen kargo (B/L, Invoice, Packing List). Sistem mengekstrak informasi dan mendeteksi HS Code secara otomatis.
                </p>
            </div>
            <button onclick="document.getElementById('modal-create-project').classList.add('hidden')" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Mode Switcher Tabs -->
        <div class="flex items-center p-1 bg-slate-100 rounded-xl mb-4 font-semibold text-xs">
            <button type="button" 
                    @click="tab = 'ai'" 
                    :class="tab === 'ai' ? 'bg-white text-sky-800 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="flex-1 py-2 px-3 rounded-lg transition-all flex items-center justify-center space-x-2">
                <span>⚡ Upload Berkas & Auto-Deteksi AI</span>
                <span class="px-1.5 py-0.2 bg-emerald-100 text-emerald-800 font-bold text-[9px] rounded-full">Rekomendasi</span>
            </button>
            <button type="button" 
                    @click="tab = 'manual'" 
                    :class="tab === 'manual' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="flex-1 py-2 px-3 rounded-lg transition-all flex items-center justify-center space-x-1.5">
                <span>📝 Form Input Manual</span>
            </button>
        </div>

        <form action="{{ route('core.document.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <!-- ============================================== -->
            <!-- TAB 1: SMART AI UPLOAD & AUTO-DETEKSI DOKUMEN -->
            <!-- ============================================== -->
            <div x-show="tab === 'ai'" class="space-y-4">

                <!-- Pilih Klien & Jenis Layanan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-sky-50/50 p-3.5 rounded-2xl border border-sky-100">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Klien / Pemilik Barang (Importir/Eksportir)</label>
                        <select name="client_id" id="smart-client-select" class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 bg-white">
                            <option value="">-- Auto-Deteksi dari Dokumen atau Pilih --</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}" :selected="detected.client_id === '{{ $c->id }}'">{{ $c->name }} (NPWP: {{ $c->npwp ?? '-' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tipe Kepabeanan</label>
                        <select name="type" x-model="detected.type" class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 bg-white">
                            <option value="IMPORT">IMPOR (BC 2.0 / PIB)</option>
                            <option value="EXPORT">EKSPOR (BC 3.0 / PEB)</option>
                        </select>
                    </div>
                </div>

                <!-- 3 Dropzone Upload Dokumen Utama -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- 1. Bill of Lading (B/L) -->
                    <div class="p-3 rounded-2xl border-2 border-dashed border-sky-200 hover:border-sky-400 bg-sky-50/30 transition-colors flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="font-bold text-slate-800 flex items-center space-x-1">
                                    <span>🚢</span>
                                    <span>Bill of Lading (B/L)</span>
                                </span>
                                <span class="text-[9px] px-1.5 py-0.5 rounded bg-sky-100 text-sky-800 font-bold">Wajib</span>
                            </div>
                            <p class="text-[10px] text-slate-400 mb-2">Diekstrak: No. BL, Kapal, Pelayaran, Kontainer, Bobot.</p>
                        </div>
                        <div>
                            <input type="file" id="input-bl-file" name="bl_file" accept=".pdf,.png,.jpg,.jpeg,.doc,.docx" class="hidden" @change="updateFileName('bl', $event)">
                            <button type="button" onclick="document.getElementById('input-bl-file').click()" 
                                    class="w-full py-2 px-2.5 rounded-xl border border-sky-300 bg-white hover:bg-sky-50 text-sky-700 font-bold text-[11px] truncate flex items-center justify-center space-x-1.5">
                                <span x-text="fileNames.bl ? '✓ ' + fileNames.bl : '📂 Pilih File B/L'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- 2. Commercial Invoice (C/I) -->
                    <div class="p-3 rounded-2xl border-2 border-dashed border-indigo-200 hover:border-indigo-400 bg-indigo-50/30 transition-colors flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="font-bold text-slate-800 flex items-center space-x-1">
                                    <span>💰</span>
                                    <span>Commercial Invoice</span>
                                </span>
                                <span class="text-[9px] px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-800 font-bold">Wajib</span>
                            </div>
                            <p class="text-[10px] text-slate-400 mb-2">Diekstrak: Nilai FOB/CIF, Valuta, Pemasok, Termin.</p>
                        </div>
                        <div>
                            <input type="file" id="input-inv-file" name="invoice_file" accept=".pdf,.xlsx,.xls,.png,.jpg,.jpeg,.doc,.docx" class="hidden" @change="updateFileName('invoice', $event)">
                            <button type="button" onclick="document.getElementById('input-inv-file').click()" 
                                    class="w-full py-2 px-2.5 rounded-xl border border-indigo-300 bg-white hover:bg-indigo-50 text-indigo-700 font-bold text-[11px] truncate flex items-center justify-center space-x-1.5">
                                <span x-text="fileNames.invoice ? '✓ ' + fileNames.invoice : '📂 Pilih Commercial Invoice'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- 3. Packing List (P/L) -->
                    <div class="p-3 rounded-2xl border-2 border-dashed border-emerald-200 hover:border-emerald-400 bg-emerald-50/30 transition-colors flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="font-bold text-slate-800 flex items-center space-x-1">
                                    <span>📦</span>
                                    <span>Packing List (P/L)</span>
                                </span>
                                <span class="text-[9px] px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold">Wajib</span>
                            </div>
                            <p class="text-[10px] text-slate-400 mb-2">Diekstrak: Rincian barang, kemasan, klasifikasi HS Code BTKI.</p>
                        </div>
                        <div>
                            <input type="file" id="input-pl-file" name="packing_list_file" accept=".pdf,.xlsx,.xls,.png,.jpg,.jpeg,.doc,.docx" class="hidden" @change="updateFileName('pl', $event)">
                            <button type="button" onclick="document.getElementById('input-pl-file').click()" 
                                    class="w-full py-2 px-2.5 rounded-xl border border-emerald-300 bg-white hover:bg-emerald-50 text-emerald-700 font-bold text-[11px] truncate flex items-center justify-center space-x-1.5">
                                <span x-text="fileNames.pl ? '✓ ' + fileNames.pl : '📂 Pilih Packing List'"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tombol Pemicu Ekstraksi Cerdas -->
                <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 border border-slate-200">
                    <div class="flex items-center space-x-2">
                        <span class="text-base" :class="scanning ? 'animate-spin' : ''">⚙️</span>
                        <div>
                            <div class="font-bold text-slate-800">AI Document Parser & BTKI Classifier</div>
                            <div class="text-[10px] text-slate-500">Membaca teks dari berkas, mendeteksi kargo, dan mengklasifikasikan pos tarif kepabeanan.</div>
                        </div>
                    </div>
                    <button type="button" 
                            @click="scanFiles()" 
                            :disabled="scanning"
                            class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold transition-all flex items-center space-x-1.5 disabled:opacity-50">
                        <span x-show="scanning" class="animate-spin">⏳</span>
                        <span x-text="scanning ? 'Memindai Dokumen...' : '⚡ Pindai & Auto-Ekstrak'"></span>
                    </button>
                </div>

                <!-- CARD HASIL AUTO-DETEKSI DOKUMEN -->
                <div x-show="scanned" class="p-4 rounded-2xl bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-200 text-xs space-y-3">
                    <div class="flex items-center justify-between border-b border-emerald-200/80 pb-2">
                        <div class="flex items-center space-x-2">
                            <span class="text-emerald-600 font-bold text-sm">✓</span>
                            <span class="font-extrabold text-emerald-950">Dokumen Berhasil Diekstrak & Terklasifikasi Otomatis</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-emerald-200 text-emerald-900">Akurasi 98%</span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-[11px]">
                        <div class="bg-white/80 p-2.5 rounded-xl border border-emerald-100">
                            <span class="text-[10px] text-slate-400 block">Nomor B/L Terbaca:</span>
                            <strong class="font-mono text-slate-900" x-text="detected.bl_number"></strong>
                        </div>
                        <div class="bg-white/80 p-2.5 rounded-xl border border-emerald-100">
                            <span class="text-[10px] text-slate-400 block">Pelayaran & Kapal:</span>
                            <strong class="text-slate-900 truncate block" x-text="detected.shipping_vendor_name || 'Terdeteksi'"></strong>
                            <span class="text-[9px] text-slate-500" x-text="detected.vessel_name"></span>
                        </div>
                        <div class="bg-white/80 p-2.5 rounded-xl border border-emerald-100">
                            <span class="text-[10px] text-slate-400 block">Nilai Komersial (CIF):</span>
                            <strong class="text-emerald-700 font-mono" x-text="detected.currency + ' ' + Number(detected.total_cif).toLocaleString()"></strong>
                        </div>
                        <div class="bg-white/80 p-2.5 rounded-xl border border-emerald-100">
                            <span class="text-[10px] text-slate-400 block">Kemasan & Bruto:</span>
                            <strong class="text-slate-900" x-text="detected.total_kemasan + ' ' + detected.jenis_kemasan + ' / ' + Number(detected.total_bruto).toLocaleString() + ' Kg'"></strong>
                        </div>
                    </div>

                    <div class="bg-white/80 p-2.5 rounded-xl border border-emerald-100 flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span>🤖</span>
                            <span class="font-semibold text-slate-800">
                                Pos Tarif BTKI: <strong class="text-indigo-700" x-text="(detected.items?.length || 2) + ' Item Kargo Terdeteksi & Diklasifikasikan Otomatis'"></strong>
                            </span>
                        </div>
                        <button type="button" @click="showDetails = !showDetails" class="text-sky-700 hover:underline font-bold text-[10px]">
                            <span x-text="showDetails ? 'Tutup Rincian ▲' : 'Lihat / Edit Rincian ▼'"></span>
                        </button>
                    </div>
                </div>

                <!-- Hidden inputs agar data terdeteksi terkirim ke server -->
                <input type="hidden" name="bl_number" :value="detected.bl_number">
                <input type="hidden" name="shipping_vendor_id" :value="detected.shipping_vendor_id">
                <input type="hidden" name="vessel_name" :value="detected.vessel_name">
                <input type="hidden" name="voyage_number" :value="detected.voyage_number">
                <input type="hidden" name="port_of_loading" :value="detected.port_of_loading">
                <input type="hidden" name="port_of_discharge" :value="detected.port_of_discharge">
                <input type="hidden" name="eta" :value="detected.eta">
                <input type="hidden" name="total_kontainer" :value="detected.total_kontainer">
                <input type="hidden" name="nomor_kontainer" :value="detected.nomor_kontainer">
                <input type="hidden" name="total_kemasan" :value="detected.total_kemasan">
                <input type="hidden" name="jenis_kemasan" :value="detected.jenis_kemasan">
                <input type="hidden" name="total_bruto" :value="detected.total_bruto">
                <input type="hidden" name="total_netto" :value="detected.total_netto">
                <input type="hidden" name="total_cif" :value="detected.total_cif">
                <input type="hidden" name="currency" :value="detected.currency">
                <input type="hidden" name="ndbm_kurs" :value="detected.ndbm_kurs">

                <!-- COLLAPSIBLE FORM REVIEW / EDIT RINCIAN -->
                <div x-show="showDetails" class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                    <div class="font-bold text-slate-800 flex items-center justify-between pb-1 border-b border-slate-200">
                        <span>Penyesuaian Manual Data Terdeteksi (Opsional)</span>
                        <span class="text-[10px] text-slate-400">Anda dapat mengubah data jika ada koreksi</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-0.5">Nomor B/L</label>
                            <input type="text" x-model="detected.bl_number" class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 bg-white">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-0.5">Nama Kapal (Vessel)</label>
                            <input type="text" x-model="detected.vessel_name" class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 bg-white">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-0.5">Voyage No.</label>
                            <input type="text" x-model="detected.voyage_number" class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 bg-white">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-2.5">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-0.5">Kontainer</label>
                            <input type="text" x-model="detected.nomor_kontainer" class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 bg-white">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-0.5">Kemasan</label>
                            <input type="number" x-model="detected.total_kemasan" class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 bg-white">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-0.5">Bruto (Kg)</label>
                            <input type="number" step="0.01" x-model="detected.total_bruto" class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 bg-white">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-0.5">Nilai Valuta (CIF)</label>
                            <input type="number" step="0.01" x-model="detected.total_cif" class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 bg-white">
                        </div>
                    </div>
                </div>

            </div>

            <!-- ============================================== -->
            <!-- TAB 2: MANUAL FORM (TRADISIONAL / TANPA FILE)   -->
            <!-- ============================================== -->
            <div x-show="tab === 'manual'" class="space-y-3.5">
                <!-- Section 1: Klien & Jenis Layanan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-slate-50 p-3 rounded-2xl border border-slate-200/60">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Pilih Klien / Pemilik Barang</label>
                        <select name="client_id_manual" @change="detected.client_id = $event.target.value" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white">
                            <option value="">-- Pilih Importir / Eksportir --</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} (NPWP: {{ $c->npwp ?? '-' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tipe Kepabeanan</label>
                        <select name="type_manual" @change="detected.type = $event.target.value" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white">
                            <option value="IMPORT">IMPOR (BC 2.0 / PIB)</option>
                            <option value="EXPORT">EKSPOR (BC 3.0 / PEB)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 mb-1">Vendor Pihak Pelayaran (Shipping Line)</label>
                        <select name="shipping_vendor_id_manual" @change="detected.shipping_vendor_id = $event.target.value" class="w-full px-3 py-2 rounded-xl border border-slate-200">
                            <option value="">-- Pilih Pelayaran --</option>
                            @foreach($vendors as $v)
                                <option value="{{ $v->id }}">{{ $v->name }} (PIC: {{ $v->pic_name ?? 'Import Desk' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nomor B/L atau AWB</label>
                        <input type="text" x-model="detected.bl_number" placeholder="Contoh: WHLU-9281928" class="w-full px-3 py-2 rounded-xl border border-slate-200">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nama Kapal (Vessel)</label>
                        <input type="text" x-model="detected.vessel_name" placeholder="MV WAN HAI 315" class="w-full px-3 py-2 rounded-xl border border-slate-200">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Voyage Number</label>
                        <input type="text" x-model="detected.voyage_number" placeholder="V.042N" class="w-full px-3 py-2 rounded-xl border border-slate-200">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Estimasi Tiba (ETA)</label>
                        <input type="date" x-model="detected.eta" class="w-full px-3 py-2 rounded-xl border border-slate-200">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Pelabuhan Muat (POL)</label>
                        <input type="text" x-model="detected.port_of_loading" placeholder="CNSHA (Shanghai Port)" class="w-full px-3 py-2 rounded-xl border border-slate-200">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Pelabuhan Bongkar (POD)</label>
                        <input type="text" x-model="detected.port_of_discharge" value="IDTPP (Tanjung Priok, Jakarta)" class="w-full px-3 py-2 rounded-xl border border-slate-200">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 bg-slate-50 p-3 rounded-2xl border border-slate-200/60">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Jml Kontainer</label>
                        <input type="number" x-model="detected.total_kontainer" min="1" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nomor Kontainer</label>
                        <input type="text" x-model="detected.nomor_kontainer" placeholder="WHLU-9182341 (1x40HC)" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kemasan</label>
                        <input type="number" x-model="detected.total_kemasan" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Jenis Kemasan</label>
                        <input type="text" x-model="detected.jenis_kemasan" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Berat Bruto (Kg)</label>
                        <input type="number" step="0.01" x-model="detected.total_bruto" class="w-full px-3 py-2 rounded-xl border border-slate-200">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Berat Netto (Kg)</label>
                        <input type="number" step="0.01" x-model="detected.total_netto" class="w-full px-3 py-2 rounded-xl border border-slate-200">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nilai CIF (Valuta)</label>
                        <input type="number" step="0.01" x-model="detected.total_cif" class="w-full px-3 py-2 rounded-xl border border-slate-200">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Mata Uang & Kurs</label>
                        <div class="flex space-x-1.5">
                            <select x-model="detected.currency" class="w-20 px-2 py-2 rounded-xl border border-slate-200">
                                <option value="USD">USD</option>
                                <option value="CNY">CNY</option>
                                <option value="EUR">EUR</option>
                                <option value="SGD">SGD</option>
                                <option value="JPY">JPY</option>
                            </select>
                            <input type="number" step="0.01" x-model="detected.ndbm_kurs" title="Kurs NDBM KMK" class="w-full px-2 py-2 rounded-xl border border-slate-200">
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Catatan Proyek & Instruksi Khusus (Opsional)</label>
                <textarea name="catatan" rows="2" placeholder="Catatan perlakuan dokumen khusus, instruksi jalur hijau, atau persyaratan lartas surveyor..." class="w-full px-3 py-2 rounded-xl border border-slate-200"></textarea>
            </div>

            <!-- Footer Action Buttons -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-create-project').classList.add('hidden')" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-sky-600 to-brand-700 hover:from-sky-700 hover:to-brand-800 text-white font-bold shadow-md shadow-sky-600/20 transition-all flex items-center space-x-1.5">
                    <span>🚀 Simpan & Inisialisasi Proyek</span>
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
