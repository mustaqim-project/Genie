@extends('layouts.app')

@section('title', 'Master HS Code Bea Cukai & Konfigurasi Gateway CEISA 4.0')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 pt-6 pb-16">
    <!-- Breadcrumb -->
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Master HS Code & Konfigurasi Gateway CEISA 4.0']
    ]" />

    @if(session('hs_result'))
        @php $res = session('hs_result'); @endphp
        <div class="mb-6 p-5 rounded-2xl bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-800/60 text-sky-900 dark:text-sky-200 text-xs shadow-xs animate-fade-in">
            <div class="flex items-center justify-between pb-3 mb-3 border-b border-sky-200/60 dark:border-sky-800/40">
                <div class="flex items-center space-x-2">
                    <span class="px-2.5 py-1 rounded-lg bg-[#007AFF] text-white font-mono font-bold text-xs">{{ $res['code'] ?? $res['clean_code'] }}</span>
                    <span class="font-bold text-sm">{{ $res['desc'] ?? 'Hasil Pemeriksaan HS Code' }}</span>
                </div>
                <span class="text-[11px] px-2 py-0.5 rounded-full font-bold {{ ($res['has_lartas'] ?? false) ? 'bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-300 dark:border-amber-700' : 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700' }}">
                    {{ ($res['has_lartas'] ?? false) ? 'TERKENA LARTAS' : 'BEBAS LARTAS (NON-LARTAS)' }}
                </span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-xs mb-3">
                <div class="bg-white/80 dark:bg-[#1C1C1E] p-2.5 rounded-xl border border-sky-100 dark:border-white/10">
                    <span class="text-slate-400 dark:text-slate-500 block text-[10px] uppercase font-bold">Bea Masuk (BM)</span>
                    <span class="text-sm font-bold text-slate-800 dark:text-white font-mono tabular-nums">{{ $res['bm'] ?? 0 }}%</span>
                </div>
                <div class="bg-white/80 dark:bg-[#1C1C1E] p-2.5 rounded-xl border border-sky-100 dark:border-white/10">
                    <span class="text-slate-400 dark:text-slate-500 block text-[10px] uppercase font-bold">PPN Impor</span>
                    <span class="text-sm font-bold text-slate-800 dark:text-white font-mono tabular-nums">{{ $res['ppn'] ?? 11 }}%</span>
                </div>
                <div class="bg-white/80 dark:bg-[#1C1C1E] p-2.5 rounded-xl border border-sky-100 dark:border-white/10">
                    <span class="text-slate-400 dark:text-slate-500 block text-[10px] uppercase font-bold">PPh Pasal 22</span>
                    <span class="text-sm font-bold text-slate-800 dark:text-white font-mono tabular-nums">{{ $res['pph'] ?? 2.5 }}%</span>
                </div>
                <div class="bg-white/80 dark:bg-[#1C1C1E] p-2.5 rounded-xl border border-sky-100 dark:border-white/10">
                    <span class="text-slate-400 dark:text-slate-500 block text-[10px] uppercase font-bold">Bea Keluar (BK)</span>
                    <span class="text-sm font-bold text-slate-800 dark:text-white font-mono tabular-nums">{{ $res['bk'] ?? 0 }}%</span>
                </div>
                <div class="bg-white/80 dark:bg-[#1C1C1E] p-2.5 rounded-xl border border-sky-100 dark:border-white/10">
                    <span class="text-slate-400 dark:text-slate-500 block text-[10px] uppercase font-bold">Satuan Pabean</span>
                    <span class="text-sm font-bold text-[#007AFF] font-mono">{{ $res['satuan'] ?? 'PCE' }}</span>
                </div>
            </div>
            @if(!empty($res['required_documents']))
                <div class="mt-2 bg-amber-50/80 dark:bg-amber-950/30 p-3 rounded-xl border border-amber-200 dark:border-amber-800/60 text-amber-900 dark:text-amber-200">
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
    <div class="mb-6 rounded-[16px] bg-white dark:bg-[#1C1C1E] p-4 sm:p-5 border border-slate-200/80 dark:border-white/10 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold font-apple-display tracking-tight text-slate-900 dark:text-white">
                Master Pos Tarif HS Code & Gateway CEISA 4.0
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Sinkronisasi database Pos Tarif BTKI 2022 (PMK 26/PMK.010/2022), tarif pungutan kepabeanan, regulasi Lartas, dan konfigurasi API DJBC
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Sinkronisasi BTKI 2022 -->
            <form action="{{ route('config.sync-hs-codes') }}" method="POST">
                @csrf
                <button type="submit" class="btn-apple-gray">
                    <i data-lucide="refresh-cw" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                    <span>Sinkronkan Master BTKI 2022</span>
                </button>
            </form>

            <!-- Uji Koneksi Token OAuth -->
            <form action="{{ route('config.test-auth') }}" method="POST">
                @csrf
                <button type="submit" class="btn-apple-filled">
                    <i data-lucide="zap" class="w-4 h-4"></i>
                    <span>Uji Token OAuth DJBC</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Metrics Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-white/10 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Total Pos Tarif di Database</span>
                <span class="p-2 rounded-xl bg-sky-50 dark:bg-sky-950/50 text-[#007AFF]">
                    <i data-lucide="database" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-slate-900 dark:text-white font-mono tabular-nums">{{ number_format($hsStats['total_hs_codes'] ?? 0) }}</div>
            <span class="text-[11px] text-slate-400 dark:text-slate-500 mt-1 block">Tersimpan dalam tabel hs_codes</span>
        </div>

        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-white/10 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Komoditas Lartas</span>
                <span class="p-2 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-amber-600 dark:text-amber-400 font-mono tabular-nums">{{ number_format($hsStats['total_lartas_codes'] ?? 0) }}</div>
            <span class="text-[11px] text-slate-400 dark:text-slate-500 mt-1 block">Wajib dokumen izin tambahan</span>
        </div>

        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-white/10 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Aturan Regulasi Pabean</span>
                <span class="p-2 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400">
                    <i data-lucide="file-check" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="text-2xl font-black text-indigo-600 dark:text-indigo-400 font-mono tabular-nums">{{ number_format($hsStats['total_regulations'] ?? 0) }}</div>
            <span class="text-[11px] text-slate-400 dark:text-slate-500 mt-1 block">Tersimpan dalam hs_regulations</span>
        </div>

        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-white/10 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Status Sinkronisasi</span>
                <span class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="text-sm font-bold text-emerald-700 dark:text-emerald-400">TERHUBUNG & SINKRON</div>
            <span class="text-[11px] text-slate-400 dark:text-slate-500 mt-1 block">
                {{ $hsStats['latest_synced'] ? \Carbon\Carbon::parse($hsStats['latest_synced'])->diffForHumans() : 'Siap Sinkronisasi' }}
            </span>
        </div>
    </div>

    <!-- Quick HS Lookup Tool & Search Bar -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-6 border border-slate-200/80 dark:border-white/10 shadow-xs mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 mb-4 border-b border-slate-100 dark:border-white/10">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Uji Simulator Cepat Tarif & Lartas Pos HS</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Ketik 8-digit Pos Tarif untuk menguji query database lokal dan verifikasi API DJBC</p>
            </div>
            <form action="{{ route('config.test-hs') }}" method="POST" class="flex items-center space-x-2">
                @csrf
                <input type="text" name="test_hs" placeholder="Contoh: 8431.49.90 / 84713020" required class="px-3.5 py-2 rounded-xl border border-slate-300 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white text-xs font-mono focus:ring-2 focus:ring-[#007AFF] focus:outline-none w-56">
                <button type="submit" class="btn-apple-filled !py-2">
                    Cek Tarif & Lartas
                </button>
            </form>
        </div>

        <!-- Filter Search Table -->
        <form method="GET" action="{{ route('config.index') }}" class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-1">
            <div class="flex-1 w-full sm:w-auto relative">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3"></i>
                <input type="text" name="search_hs" value="{{ $searchHs }}" placeholder="Cari Pos Tarif, Nama Barang, Bab, atau Deskripsi..." class="w-full pl-10 pr-4 py-2 rounded-xl border border-slate-200 dark:border-white/15 text-xs focus:ring-2 focus:ring-[#007AFF] focus:outline-none bg-slate-50/50 dark:bg-[#2C2C2E] dark:text-white">
            </div>
            <div class="flex items-center space-x-2 w-full sm:w-auto">
                <select name="lartas_filter" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 dark:border-white/15 text-xs text-slate-700 dark:text-slate-300 bg-white dark:bg-[#2C2C2E] focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    <option value="">Semua Status Lartas</option>
                    <option value="lartas" {{ $lartasFilter === 'lartas' ? 'selected' : '' }}>Hanya Lartas</option>
                    <option value="non_lartas" {{ $lartasFilter === 'non_lartas' ? 'selected' : '' }}>Hanya Bebas Lartas</option>
                </select>
                <button type="submit" class="btn-apple-filled !py-2">
                    Filter
                </button>
                @if($searchHs || $lartasFilter)
                    <a href="{{ route('config.index') }}" class="btn-apple-gray !py-2">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Master HS Code Database Table -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl border border-slate-200/80 dark:border-white/10 shadow-xs overflow-hidden mb-10">
        <div class="p-5 border-b border-slate-100 dark:border-white/10 flex items-center justify-between">
            <div>
                <h2 class="font-bold text-slate-900 dark:text-white">Database Master Pos Tarif BTKI 2022 Tersimpan</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Menampilkan {{ $hsCodes->total() }} Pos Tarif resmi Bea Cukai dengan tarif pungutan dan regulasi dokumen</p>
            </div>
            <span class="text-xs font-mono font-bold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-white/10 px-3 py-1 rounded-lg tabular-nums">
                Halaman {{ $hsCodes->currentPage() }} dari {{ $hsCodes->lastPage() }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 dark:bg-[#2C2C2E] text-slate-600 dark:text-slate-300 font-bold border-b border-slate-100 dark:border-white/10 uppercase text-[10px] tracking-wider">
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
                <tbody class="divide-y divide-slate-100 dark:divide-white/5 font-sans">
                    @forelse($hsCodes as $hs)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900 dark:text-white whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-white/10 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-white/10 text-xs">{{ $hs->code }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-800 dark:text-slate-200 font-medium max-w-md">
                                <p class="line-clamp-2 leading-relaxed" title="{{ $hs->description }}">{{ $hs->description }}</p>
                                @if($hs->btki)
                                    <span class="text-[10px] text-slate-400 dark:text-slate-500 block mt-0.5 font-mono">{{ $hs->btki }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-3 text-center font-mono font-bold tabular-nums {{ $hs->duty_bm > 0 ? 'text-amber-700 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                {{ (float) $hs->duty_bm }}%
                            </td>
                            <td class="py-3.5 px-3 text-center font-mono font-semibold text-slate-700 dark:text-slate-300 tabular-nums">
                                {{ (float) $hs->duty_ppn }}%
                            </td>
                            <td class="py-3.5 px-3 text-center font-mono font-semibold text-slate-700 dark:text-slate-300 tabular-nums">
                                {{ (float) $hs->duty_pph }}%
                            </td>
                            <td class="py-3.5 px-3 text-center font-mono font-semibold tabular-nums {{ $hs->duty_bk > 0 ? 'text-rose-600 dark:text-rose-400 font-bold' : 'text-slate-400 dark:text-slate-500' }}">
                                {{ (float) $hs->duty_bk }}%
                            </td>
                            <td class="py-3.5 px-3 text-center font-mono text-slate-600 dark:text-slate-300 font-bold">
                                {{ $hs->satuan ?? 'PCE' }}
                            </td>
                            <td class="py-3.5 px-4 max-w-xs">
                                @if($hs->has_lartas)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-950/50 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800 mb-1">
                                        LARTAS
                                    </span>
                                    <p class="text-[11px] text-amber-900 dark:text-amber-200 leading-tight">{{ $hs->lartas_desc }}</p>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                        BEBAS LARTAS
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-[11px]">
                                <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-white/10 font-mono text-slate-600 dark:text-slate-400 font-semibold">
                                    {{ $hs->source ?? 'DJBC_BTKI_2022' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400 dark:text-slate-500">
                                <p class="text-sm font-semibold">Tidak ada Pos Tarif yang cocok dengan pencarian.</p>
                                <p class="text-xs mt-1">Gunakan tombol "Sinkronkan Master BTKI 2022" untuk memuat data resmi Bea Cukai.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($hsCodes->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-white/10 bg-slate-50/50 dark:bg-white/[0.02]">
                {{ $hsCodes->links() }}
            </div>
        @endif
    </div>

    <!-- Grid Detail Gateway & Petunjuk Integrasi -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
        <!-- Profil Perusahaan & Kredensial (1 col) -->
        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl p-6 border border-slate-200/80 dark:border-white/10 shadow-xs space-y-6">
            <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-white pb-2 border-b border-slate-100 dark:border-white/10 mb-3">Profil Entitas Terdaftar DJBC</h2>
                <div class="space-y-2.5 text-xs">
                    <div>
                        <span class="text-slate-400 dark:text-slate-500 block font-medium">Nama Perusahaan</span>
                        <span class="font-bold text-slate-800 dark:text-white">{{ $config['company']['name'] }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 dark:text-slate-500 block font-medium">NIB (Nomor Induk Berusaha)</span>
                        <span class="font-mono font-bold text-slate-800 dark:text-white tabular-nums">{{ $config['company']['nib'] }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 dark:text-slate-500 block font-medium">NPWP 15/16 Digit</span>
                        <span class="font-mono font-bold text-slate-800 dark:text-white tabular-nums">{{ $config['company']['npwp'] }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 dark:text-slate-500 block font-medium">ID Unik Perusahaan (Nomor Aju)</span>
                        <span class="font-mono font-bold text-[#007AFF]">{{ $config['company']['id_perusahaan'] }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 dark:text-slate-500 block font-medium">Kantor Pabean Default</span>
                        <span class="font-bold text-slate-800 dark:text-white">{{ $config['company']['default_kode_kantor'] }} - {{ $config['kantor_pabean'][$config['company']['default_kode_kantor']] ?? '' }}</span>
                    </div>
                </div>
            </div>

            <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-white pb-2 border-b border-slate-100 dark:border-white/10 mb-3">Parameter Gateway CEISA 4.0</h2>
                <div class="space-y-2.5 text-xs font-mono">
                    <div>
                        <span class="text-slate-400 dark:text-slate-500 block font-medium font-sans">Mode Lingkungan</span>
                        <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold {{ $config['api']['mock_mode'] ? 'bg-amber-100 dark:bg-amber-950/50 text-amber-800 dark:text-amber-300' : 'bg-emerald-100 dark:bg-emerald-950/50 text-emerald-800 dark:text-emerald-300' }}">
                            {{ $config['api']['mock_mode'] ? 'SANDBOX / SIMULASI' : 'LIVE GATEWAY DJBC' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 dark:text-slate-500 block font-medium font-sans">Base URL Gateway</span>
                        <span class="text-slate-700 dark:text-slate-300 truncate block">{{ $config['api']['base_url'] }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 dark:text-slate-500 block font-medium font-sans">Application ID</span>
                        <span class="text-slate-700 dark:text-slate-300 font-bold block truncate">{{ $config['api']['application_id'] ?: '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 dark:text-slate-500 block font-medium font-sans">API Key Header</span>
                        <span class="text-slate-500 dark:text-slate-400 truncate block">beacukai-api-key: {{ substr($config['api']['api_key'], 0, 8) }}***</span>
                    </div>
                    @if(!empty($config['api']['whatsapp_no']))
                    <div>
                        <span class="text-slate-400 dark:text-slate-500 block font-medium font-sans">WhatsApp Alert H2H</span>
                        <span class="text-emerald-600 dark:text-emerald-400 font-bold">{{ $config['api']['whatsapp_no'] }}</span>
                    </div>
                    @endif
                    <div class="pt-2 border-t border-slate-100 dark:border-white/10">
                        <span class="text-slate-400 dark:text-slate-500 block font-medium font-sans">Webhook URL Callback DJBC</span>
                        <span class="text-[#007AFF] font-bold block select-all break-all text-[11px] bg-sky-50 dark:bg-sky-950/40 p-1.5 rounded-lg border border-sky-200 dark:border-sky-800/50 mt-0.5">
                            {{ url('/api/v1/ceisa/webhook') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panduan REST API H2H untuk ERP Eksternal (2 cols) -->
        <div class="lg:col-span-2 bg-white dark:bg-[#1C1C1E] rounded-2xl p-6 border border-slate-200/80 dark:border-white/10 shadow-xs">
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-2">Panduan Integrasi REST API H2H (Server-to-Server)</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                Sistem eksternal (seperti ERP COOCA, WMS, atau In-House System lainnya) dan Bea Cukai DJBC dapat berkomunikasi secara dua arah (Two-Way H2H) ke endpoint service <strong>PATS_CEISA</strong>:
            </p>

            <div class="space-y-3.5 text-xs font-mono">
                <!-- Endpoint 0: Webhook Listener DJBC -->
                <div class="p-3 rounded-xl bg-purple-50 dark:bg-purple-950/30 border border-purple-200 dark:border-purple-800/40">
                    <div class="flex items-center space-x-2 mb-1.5">
                        <span class="px-2 py-0.5 rounded bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300 font-bold text-[10px]">POST</span>
                        <span class="font-bold text-slate-800 dark:text-white">/api/v1/ceisa/webhook</span>
                        <span class="px-1.5 py-0.5 rounded bg-purple-200/60 dark:bg-purple-800/50 text-purple-800 dark:text-purple-200 text-[9px] font-sans font-bold">Callback DJBC</span>
                    </div>
                    <p class="text-slate-500 dark:text-slate-400 font-sans text-[11px]">Menerima notifikasi push real-time dari Bea Cukai saat SPPB terbit, NPE terbit, penjaluran (Merah/Kuning/Hijau), atau kode billing MPN G3 siap disetor.</p>
                </div>

                <!-- Endpoint 1: Tarif HS -->
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#2C2C2E]/60 border border-slate-200 dark:border-white/10">
                    <div class="flex items-center space-x-2 mb-1.5">
                        <span class="px-2 py-0.5 rounded bg-sky-100 dark:bg-sky-950/50 text-[#007AFF] font-bold text-[10px]">GET</span>
                        <span class="font-bold text-slate-800 dark:text-white">/api/v1/ceisa/tarif-hs/{hsCode}</span>
                    </div>
                    <p class="text-slate-500 dark:text-slate-400 font-sans text-[11px]">Dapatkan rincian tarif Bea Masuk, PPN 11%, PPh 2.5%, Satuan, dan regulasi Lartas berdasarkan kode pos tarif 8-digit.</p>
                </div>

                <!-- Endpoint 2: Submit Dokumen Impor BC 2.0 -->
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#2C2C2E]/60 border border-slate-200 dark:border-white/10">
                    <div class="flex items-center space-x-2 mb-1.5">
                        <span class="px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950/50 text-emerald-800 dark:text-emerald-300 font-bold text-[10px]">POST</span>
                        <span class="font-bold text-slate-800 dark:text-white">/api/v1/ceisa/bc-20/submit</span>
                    </div>
                    <p class="text-slate-500 dark:text-slate-400 font-sans text-[11px]">Submit dokumen Impor BC 2.0 (PIB), auto-generate 26-digit Nomor Aju, dan teruskan ke CEISA 4.0.</p>
                </div>

                <!-- Endpoint 2B: Submit Dokumen Ekspor BC 3.0 -->
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#2C2C2E]/60 border border-slate-200 dark:border-white/10">
                    <div class="flex items-center space-x-2 mb-1.5">
                        <span class="px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950/50 text-emerald-800 dark:text-emerald-300 font-bold text-[10px]">POST</span>
                        <span class="font-bold text-slate-800 dark:text-white">/api/v1/ceisa/bc-30/submit</span>
                    </div>
                    <p class="text-slate-500 dark:text-slate-400 font-sans text-[11px]">Submit dokumen Ekspor BC 3.0 (PEB), auto-generate Nomor Aju, dan teruskan ke CEISA 4.0.</p>
                </div>

                <!-- Endpoint 3: Status & Respon -->
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#2C2C2E]/60 border border-slate-200 dark:border-white/10">
                    <div class="flex items-center space-x-2 mb-1.5">
                        <span class="px-2 py-0.5 rounded bg-sky-100 dark:bg-sky-950/50 text-[#007AFF] font-bold text-[10px]">GET</span>
                        <span class="font-bold text-slate-800 dark:text-white">/api/v1/ceisa/dokumen/{nomorAju}/status</span>
                    </div>
                    <p class="text-slate-500 dark:text-slate-400 font-sans text-[11px]">Tarik status terkini dan respon resmi (Billing MPN G3 / SPPB / NPE) berdasarkan Nomor Aju.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Complete Audit Trail Table -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl border border-slate-200/80 dark:border-white/10 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 dark:border-white/10">
            <h2 class="font-bold text-slate-900 dark:text-white">Jejak Audit Komunikasi API CEISA 4.0 (Immutable Logs)</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Catatan seluruh request dan response HTTP ke gateway Bea Cukai</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-[#2C2C2E] text-slate-600 dark:text-slate-300 font-semibold border-b border-slate-100 dark:border-white/10">
                    <tr>
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Nomor Aju</th>
                        <th class="py-3 px-4">Method & Endpoint</th>
                        <th class="py-3 px-4">Status Code</th>
                        <th class="py-3 px-4">Durasi</th>
                        <th class="py-3 px-4">Hasil</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="py-3 px-4 text-slate-500 dark:text-slate-400 tabular-nums">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td class="py-3 px-4 font-mono font-semibold text-slate-800 dark:text-white tabular-nums">
                                {{ $log->nomor_aju ?? '-' }}
                            </td>
                            <td class="py-3 px-4 font-mono">
                                <span class="px-1.5 py-0.5 rounded font-bold text-[10px] {{ $log->method === 'POST' ? 'bg-emerald-100 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400' : 'bg-sky-100 dark:bg-sky-950/50 text-sky-700 dark:text-sky-400' }}">
                                    {{ $log->method }}
                                </span>
                                <span class="text-slate-700 dark:text-slate-300 ml-1">{{ $log->endpoint }}</span>
                            </td>
                            <td class="py-3 px-4 font-mono font-bold tabular-nums {{ $log->status_code === 200 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                {{ $log->status_code }}
                            </td>
                            <td class="py-3 px-4 font-mono text-slate-500 dark:text-slate-400 tabular-nums">{{ $log->duration_ms }} ms</td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $log->is_success ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800' }}">
                                    {{ $log->is_success ? 'SUCCESS' : 'FAILED' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 dark:text-slate-500">
                                Belum ada log transaksi pabean tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-white/10">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
