@extends('layouts.app')

@section('title', 'Master HS Code Bea Cukai & Konfigurasi Gateway CEISA 4.0')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 pt-6 pb-16">
    <!-- Breadcrumb -->
    <div class="text-xs text-slate-500 font-medium mb-3">
        <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a> / 
        <span class="text-slate-800 font-semibold">Master HS Code & Konfigurasi Gateway CEISA 4.0</span>
    </div>

    @if(session('hs_result'))
        @php $res = session('hs_result'); @endphp
        <div class="mb-6 p-5 rounded-2xl bg-sky-50 border border-sky-200 text-sky-900 text-xs shadow-sm animate-fade-in">
            <div class="flex items-center justify-between pb-3 mb-3 border-b border-sky-200/60">
                <div class="flex items-center space-x-2">
                    <span class="px-2.5 py-1 rounded-lg bg-sky-600 text-white font-mono font-bold text-xs">{{ $res['code'] ?? $res['clean_code'] }}</span>
                    <span class="font-bold text-sm">{{ $res['desc'] ?? 'Hasil Pemeriksaan HS Code' }}</span>
                </div>
                <span class="text-[11px] px-2 py-0.5 rounded-full font-bold {{ ($res['has_lartas'] ?? false) ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-emerald-100 text-emerald-800 border border-emerald-300' }}">
                    {{ ($res['has_lartas'] ?? false) ? 'TERKENA LARTAS' : 'BEBAS LARTAS (NON-LARTAS)' }}
                </span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-xs mb-3">
                <div class="bg-white/80 p-2.5 rounded-xl border border-sky-100">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Bea Masuk (BM)</span>
                    <span class="text-sm font-bold text-slate-800 font-mono">{{ $res['bm'] ?? 0 }}%</span>
                </div>
                <div class="bg-white/80 p-2.5 rounded-xl border border-sky-100">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">PPN Impor</span>
                    <span class="text-sm font-bold text-slate-800 font-mono">{{ $res['ppn'] ?? 11 }}%</span>
                </div>
                <div class="bg-white/80 p-2.5 rounded-xl border border-sky-100">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">PPh Pasal 22</span>
                    <span class="text-sm font-bold text-slate-800 font-mono">{{ $res['pph'] ?? 2.5 }}%</span>
                </div>
                <div class="bg-white/80 p-2.5 rounded-xl border border-sky-100">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Bea Keluar (BK)</span>
                    <span class="text-sm font-bold text-slate-800 font-mono">{{ $res['bk'] ?? 0 }}%</span>
                </div>
                <div class="bg-white/80 p-2.5 rounded-xl border border-sky-100">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Satuan Pabean</span>
                    <span class="text-sm font-bold text-sky-700 font-mono">{{ $res['satuan'] ?? 'PCE' }}</span>
                </div>
            </div>
            @if(!empty($res['required_documents']))
                <div class="mt-2 bg-amber-50/80 p-3 rounded-xl border border-amber-200 text-amber-900">
                    <span class="font-bold block text-[11px] mb-1">Dokumen Wajib Persyaratan Lartas:</span>
                    <ul class="list-disc list-inside space-y-0.5 text-[11px]">
                        @foreach($res['required_documents'] as $doc)
                            <li><span class="font-bold">Kode {{ $doc['kode_dokumen'] }}:</span> {{ $doc['nama_dokumen'] }} ({{ $doc['instansi'] }})</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif

    <!-- Header & Action Buttons -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Master Pos Tarif HS Code & Gateway CEISA 4.0</h1>
            <p class="text-xs text-slate-500 mt-1">Sinkronisasi database Pos Tarif BTKI 2022 (PMK 26/PMK.010/2022), tarif pungutan kepabeanan, regulasi Lartas, dan konfigurasi API DJBC</p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Sinkronisasi BTKI 2022 -->
            <form action="{{ route('config.sync-hs-codes') }}" method="POST">
                @csrf
                <button type="submit" class="inline-flex items-center space-x-2 px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition-all shadow-xs active:scale-95">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Sinkronkan Master BTKI 2022</span>
                </button>
            </form>

            <!-- Uji Koneksi Token OAuth -->
            <form action="{{ route('config.test-auth') }}" method="POST">
                @csrf
                <button type="submit" class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs shadow-xs transition-all active:scale-95">
                    <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span>Uji Token OAuth DJBC</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Metrics Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500">Total Pos Tarif di Database</span>
                <span class="p-2 rounded-xl bg-sky-50 text-sky-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900 font-mono">{{ number_format($hsStats['total_hs_codes'] ?? 0) }}</div>
            <span class="text-[11px] text-slate-400 mt-1 block">Tersimpan dalam tabel hs_codes</span>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500">Komoditas Lartas</span>
                <span class="p-2 rounded-xl bg-amber-50 text-amber-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </span>
            </div>
            <div class="text-2xl font-black text-amber-600 font-mono">{{ number_format($hsStats['total_lartas_codes'] ?? 0) }}</div>
            <span class="text-[11px] text-slate-400 mt-1 block">Wajib dokumen izin tambahan</span>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500">Aturan Regulasi Pabean</span>
                <span class="p-2 rounded-xl bg-indigo-50 text-indigo-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </span>
            </div>
            <div class="text-2xl font-black text-indigo-600 font-mono">{{ number_format($hsStats['total_regulations'] ?? 0) }}</div>
            <span class="text-[11px] text-slate-400 mt-1 block">Tersimpan dalam hs_regulations</span>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500">Status Sinkronisasi</span>
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </span>
            </div>
            <div class="text-sm font-bold text-emerald-700">TERHUBUNG & SINKRON</div>
            <span class="text-[11px] text-slate-400 mt-1 block">
                {{ $hsStats['latest_synced'] ? \Carbon\Carbon::parse($hsStats['latest_synced'])->diffForHumans() : 'Siap Sinkronisasi' }}
            </span>
        </div>
    </div>

    <!-- Quick HS Lookup Tool & Search Bar -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 mb-4 border-b border-slate-100">
            <div>
                <h2 class="text-base font-bold text-slate-900">Uji Simulator Cepat Tarif & Lartas Pos HS</h2>
                <p class="text-xs text-slate-500 mt-0.5">Ketik 8-digit Pos Tarif untuk menguji query database lokal dan verifikasi API DJBC</p>
            </div>
            <form action="{{ route('config.test-hs') }}" method="POST" class="flex items-center space-x-2">
                @csrf
                <input type="text" name="test_hs" placeholder="Contoh: 8431.49.90 / 84713020" required class="px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-mono focus:ring-2 focus:ring-sky-500 focus:outline-none w-56">
                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs transition-colors shadow-sm">
                    Cek Tarif & Lartas
                </button>
            </form>
        </div>

        <!-- Filter Search Table -->
        <form method="GET" action="{{ route('config.index') }}" class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-1">
            <div class="flex-1 w-full sm:w-auto relative">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" name="search_hs" value="{{ $searchHs }}" placeholder="Cari Pos Tarif, Nama Barang, Bab, atau Deskripsi..." class="w-full pl-10 pr-4 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-sky-500 focus:outline-none bg-slate-50/50">
            </div>
            <div class="flex items-center space-x-2 w-full sm:w-auto">
                <select name="lartas_filter" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-sky-500">
                    <option value="">Semua Status Lartas</option>
                    <option value="lartas" {{ $lartasFilter === 'lartas' ? 'selected' : '' }}>Hanya Lartas</option>
                    <option value="non_lartas" {{ $lartasFilter === 'non_lartas' ? 'selected' : '' }}>Hanya Bebas Lartas</option>
                </select>
                <button type="submit" class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs transition-colors">
                    Filter
                </button>
                @if($searchHs || $lartasFilter)
                    <a href="{{ route('config.index') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-xs transition-colors">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Master HS Code Database Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-10">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="font-bold text-slate-900">Database Master Pos Tarif BTKI 2022 Tersimpan</h2>
                <p class="text-xs text-slate-500 mt-0.5">Menampilkan {{ $hsCodes->total() }} Pos Tarif resmi Bea Cukai dengan tarif pungutan dan regulasi dokumen</p>
            </div>
            <span class="text-xs font-mono font-bold text-slate-500 bg-slate-100 px-3 py-1 rounded-lg">
                Halaman {{ $hsCodes->currentPage() }} dari {{ $hsCodes->lastPage() }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 text-slate-600 font-bold border-b border-slate-100 uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Pos Tarif (HS Code)</th>
                        <th class="py-3 px-4">Uraian Barang (BTKI 2022)</th>
                        <th class="py-3 px-3 text-center">BM (%)</th>
                        <th class="py-3 px-3 text-center">PPN (%)</th>
                        <th class="py-3 px-3 text-center">PPh (%)</th>
                        <th class="py-3 px-3 text-center">BK (%)</th>
                        <th class="py-3 px-3 text-center">Satuan</th>
                        <th class="py-3 px-4">Status & Izin Lartas</th>
                        <th class="py-3 px-4">Sumber Data</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-sans">
                    @forelse($hsCodes as $hs)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-800 border border-slate-200 text-xs">{{ $hs->code }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-800 font-medium max-w-md">
                                <p class="line-clamp-2 leading-relaxed" title="{{ $hs->description }}">{{ $hs->description }}</p>
                                @if($hs->btki)
                                    <span class="text-[10px] text-slate-400 block mt-0.5 font-mono">{{ $hs->btki }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-3 text-center font-mono font-bold {{ $hs->duty_bm > 0 ? 'text-amber-700' : 'text-emerald-600' }}">
                                {{ (float) $hs->duty_bm }}%
                            </td>
                            <td class="py-3.5 px-3 text-center font-mono font-semibold text-slate-700">
                                {{ (float) $hs->duty_ppn }}%
                            </td>
                            <td class="py-3.5 px-3 text-center font-mono font-semibold text-slate-700">
                                {{ (float) $hs->duty_pph }}%
                            </td>
                            <td class="py-3.5 px-3 text-center font-mono font-semibold {{ $hs->duty_bk > 0 ? 'text-rose-600 font-bold' : 'text-slate-400' }}">
                                {{ (float) $hs->duty_bk }}%
                            </td>
                            <td class="py-3.5 px-3 text-center font-mono text-slate-600 font-bold">
                                {{ $hs->satuan ?? 'PCE' }}
                            </td>
                            <td class="py-3.5 px-4 max-w-xs">
                                @if($hs->has_lartas)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200 mb-1">
                                        LARTAS
                                    </span>
                                    <p class="text-[11px] text-amber-900 leading-tight">{{ $hs->lartas_desc }}</p>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        BEBAS LARTAS
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-[11px]">
                                <span class="px-2 py-0.5 rounded bg-slate-100 font-mono text-slate-600 font-semibold">
                                    {{ $hs->source ?? 'DJBC_BTKI_2022' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400">
                                <p class="text-sm font-semibold">Tidak ada Pos Tarif yang cocok dengan pencarian.</p>
                                <p class="text-xs mt-1">Gunakan tombol "Sinkronkan Master BTKI 2022" untuk memuat data resmi Bea Cukai.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($hsCodes->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $hsCodes->links() }}
            </div>
        @endif
    </div>

    <!-- Grid Detail Gateway & Petunjuk Integrasi -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
        <!-- Profil Perusahaan & Kredensial (1 col) -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-6">
            <div>
                <h2 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100 mb-3">Profil Entitas Terdaftar DJBC</h2>
                <div class="space-y-2.5 text-xs">
                    <div>
                        <span class="text-slate-400 block font-medium">Nama Perusahaan</span>
                        <span class="font-bold text-slate-800">{{ $config['company']['name'] }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">NIB (Nomor Induk Berusaha)</span>
                        <span class="font-mono font-bold text-slate-800">{{ $config['company']['nib'] }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">NPWP 15/16 Digit</span>
                        <span class="font-mono font-bold text-slate-800">{{ $config['company']['npwp'] }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">ID Unik Perusahaan (Nomor Aju)</span>
                        <span class="font-mono font-bold text-sky-600">{{ $config['company']['id_perusahaan'] }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium">Kantor Pabean Default</span>
                        <span class="font-bold text-slate-800">{{ $config['company']['default_kode_kantor'] }} - {{ $config['kantor_pabean'][$config['company']['default_kode_kantor']] ?? '' }}</span>
                    </div>
                </div>
            </div>

            <div>
                <h2 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100 mb-3">Parameter Gateway CEISA 4.0</h2>
                <div class="space-y-2.5 text-xs font-mono">
                    <div>
                        <span class="text-slate-400 block font-medium font-sans">Mode Lingkungan</span>
                        <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold {{ $config['api']['mock_mode'] ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                            {{ $config['api']['mock_mode'] ? 'SANDBOX / SIMULASI' : 'LIVE GATEWAY DJBC' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium font-sans">Base URL Gateway</span>
                        <span class="text-slate-700 truncate block">{{ $config['api']['base_url'] }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium font-sans">Username API</span>
                        <span class="text-slate-700">{{ $config['api']['username'] }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium font-sans">API Key Header</span>
                        <span class="text-slate-500">beacukai-api-key: {{ substr($config['api']['api_key'], 0, 8) }}***</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panduan REST API H2H untuk ERP Eksternal (2 cols) -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <h2 class="text-base font-bold text-slate-900 mb-2">Panduan Integrasi REST API H2H (Server-to-Server)</h2>
            <p class="text-xs text-slate-500 mb-4">
                Sistem eksternal (seperti ERP COOCA, WMS, atau In-House System lainnya) dapat mengakses data tarif dan mengirimkan dokumen kepabeanan secara otomatis ke endpoint service <strong>PATS_CEISA</strong>:
            </p>

            <div class="space-y-3.5 text-xs font-mono">
                <!-- Endpoint 1: Tarif HS -->
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="flex items-center space-x-2 mb-1.5">
                        <span class="px-2 py-0.5 rounded bg-sky-100 text-sky-800 font-bold text-[10px]">GET</span>
                        <span class="font-bold text-slate-800">/api/v1/ceisa/tarif-hs/{hsCode}</span>
                    </div>
                    <p class="text-slate-500 font-sans text-[11px]">Dapatkan rincian tarif Bea Masuk, PPN 11%, PPh 2.5%, Satuan, dan regulasi Lartas berdasarkan kode pos tarif 8-digit.</p>
                </div>

                <!-- Endpoint 2: Sinkronisasi Tarif -->
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="flex items-center space-x-2 mb-1.5">
                        <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[10px]">POST</span>
                        <span class="font-bold text-slate-800">/api/v1/ceisa/tarif-hs/sync</span>
                    </div>
                    <p class="text-slate-500 font-sans text-[11px]">Trigger sinkronisasi otomatis seluruh master data HS Code BTKI 2022 Bea Cukai ke database.</p>
                </div>

                <!-- Endpoint 3: Submit Dokumen Impor BC 2.0 -->
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="flex items-center space-x-2 mb-1.5">
                        <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[10px]">POST</span>
                        <span class="font-bold text-slate-800">/api/v1/ceisa/bc-20/submit</span>
                    </div>
                    <p class="text-slate-500 font-sans text-[11px]">Submit dokumen Impor BC 2.0 (PIB), auto-generate 26-digit Nomor Aju, dan teruskan ke CEISA 4.0.</p>
                </div>

                <!-- Endpoint 4: Status & Respon -->
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="flex items-center space-x-2 mb-1.5">
                        <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-bold text-[10px]">GET</span>
                        <span class="font-bold text-slate-800">/api/v1/ceisa/dokumen/{nomorAju}/status</span>
                    </div>
                    <p class="text-slate-500 font-sans text-[11px]">Tarik status terkini dan respon resmi (Billing MPN G3 / SPPB / NPE) berdasarkan Nomor Aju.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Complete Audit Trail Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h2 class="font-bold text-slate-900">Jejak Audit Komunikasi API CEISA 4.0 (Immutable Logs)</h2>
            <p class="text-xs text-slate-500 mt-0.5">Catatan seluruh request dan response HTTP ke gateway Bea Cukai</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Nomor Aju</th>
                        <th class="py-3 px-4">Method & Endpoint</th>
                        <th class="py-3 px-4">Status Code</th>
                        <th class="py-3 px-4">Durasi</th>
                        <th class="py-3 px-4">Hasil</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-3 px-4 text-slate-500">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td class="py-3 px-4 font-mono font-semibold text-slate-800">
                                {{ $log->nomor_aju ?? '-' }}
                            </td>
                            <td class="py-3 px-4 font-mono">
                                <span class="px-1.5 py-0.5 rounded font-bold text-[10px] {{ $log->method === 'POST' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ $log->method }}
                                </span>
                                <span class="text-slate-700 ml-1">{{ $log->endpoint }}</span>
                            </td>
                            <td class="py-3 px-4 font-mono font-bold {{ $log->status_code === 200 ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $log->status_code }}
                            </td>
                            <td class="py-3 px-4 font-mono text-slate-500">{{ $log->duration_ms }} ms</td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $log->is_success ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                    {{ $log->is_success ? 'SUCCESS' : 'FAILED' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                Belum ada log transaksi pabean tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
