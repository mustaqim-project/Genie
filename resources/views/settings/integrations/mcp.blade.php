@extends('layouts.app')

@section('title', 'MCP Server & Integrasi AI Kepabeanan')

@section('content')
<div x-data="mcpIntegrationApp()" class="space-y-6">

    <!-- Toast Notifikasi Copy -->
    <div x-show="copyToast" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed bottom-6 right-6 z-50 px-4 py-2.5 rounded-xl bg-slate-900 text-white text-xs font-semibold shadow-xl border border-slate-800 flex items-center space-x-2">
        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span x-text="copyMsg"></span>
    </div>

    <!-- Banner Kunci Baru Dibuat (One-Time Token Flash Alert) -->
    @if(session('plain_token'))
    <div class="p-5 rounded-2xl bg-amber-500/10 border-2 border-amber-500/40 text-amber-950 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-sm animate-fade-in">
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-xl">⚠️</span>
                <h3 class="text-sm font-black text-amber-900">Token Akses MCP Baru Berhasil Dibuat: "{{ session('token_name') }}"</h3>
            </div>
            <p class="text-xs text-amber-800 mt-1 max-w-2xl">
                Simpan token ini sekarang. Demi keamanan sistem pabean, token ini <strong>hanya ditampilkan satu kali</strong> dan tidak dapat diakses lagi setelah Anda menutup halaman ini.
            </p>
            <div class="mt-3 flex items-center space-x-2">
                <code class="px-3.5 py-2 rounded-xl bg-amber-100/90 font-mono text-xs font-bold text-amber-950 select-all border border-amber-300/80 shadow-2xs">
                    {{ session('plain_token') }}
                </code>
            </div>
        </div>
        <button type="button" 
                @click="copyToClipboard('{{ session('plain_token') }}', 'Token MCP berhasil disalin ke clipboard!')"
                class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-sm transition-all shrink-0 flex items-center space-x-1.5 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
            <span>Salin Kunci Token</span>
        </button>
    </div>
    @endif

    <!-- Header & Status Server Bar -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-400 mb-1">
                <span>Pengaturan Sistem & AI</span>
                <span>&rarr;</span>
                <span class="text-slate-700 font-semibold">Integrasi Model Context Protocol (MCP)</span>
            </div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight flex items-center space-x-2">
                <span>MCP Server & Customs AI Agent Hub</span>
                <span class="px-2 py-0.5 rounded-md bg-sky-100 text-sky-800 text-[10px] font-mono font-bold uppercase tracking-wider">v2.5.0</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1 max-w-2xl leading-relaxed">
                Pusat integrasi Model Context Protocol (MCP) resmi untuk menghubungkan AI Assistant eksternal (Claude Desktop, Cursor, Antigravity, script Python) ke database pos tarif BTKI 2022, pencocokan Packing List otomatis, dan gateway pabean CEISA 4.0.
            </p>
        </div>

        <!-- Live Server Status Pill & Metrics -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2.5 bg-slate-50 p-3 rounded-2xl border border-slate-200/70">
            <div class="flex items-center space-x-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="font-bold text-xs text-slate-800">MCP Online</span>
            </div>
            <span class="hidden sm:inline text-slate-300">|</span>
            <span class="text-[11px] font-mono text-slate-500">Spec: 2024-11-05</span>
            <span class="hidden sm:inline text-slate-300">|</span>
            <span class="text-[11px] font-mono text-indigo-600 font-bold">{{ $activeTokensCount }} Token Aktif</span>
            <span class="hidden sm:inline text-slate-300">|</span>
            <span class="text-[11px] font-mono text-emerald-600 font-bold">{{ count($tools) }} Tools Aktif (8 Modul ERP)</span>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-slate-200 gap-2 overflow-x-auto">
        <button type="button" 
                @click="activeTab = 'tester'" 
                :class="activeTab === 'tester' ? 'border-sky-600 text-sky-700 font-bold bg-white' : 'border-transparent text-slate-500 hover:text-slate-800'"
                class="px-4 py-2.5 text-xs rounded-t-xl border-b-2 transition-all flex items-center space-x-2 shrink-0 cursor-pointer">
            <span>⚡</span>
            <span>Uji Cepat Deteksi HS & Packing List</span>
            <span class="px-1.5 py-0.2 rounded-full text-[9px] bg-emerald-100 text-emerald-800 font-bold">Live</span>
        </button>

        <button type="button" 
                @click="activeTab = 'tokens'" 
                :class="activeTab === 'tokens' ? 'border-sky-600 text-sky-700 font-bold bg-white' : 'border-transparent text-slate-500 hover:text-slate-800'"
                class="px-4 py-2.5 text-xs rounded-t-xl border-b-2 transition-all flex items-center space-x-2 shrink-0 cursor-pointer">
            <span>🔑</span>
            <span>Kunci Akses AI (MCP Tokens)</span>
            <span class="px-1.5 py-0.2 rounded-full text-[9px] bg-sky-100 text-sky-800 font-bold">{{ $activeTokensCount }}</span>
        </button>

        <button type="button" 
                @click="activeTab = 'guide'" 
                :class="activeTab === 'guide' ? 'border-sky-600 text-sky-700 font-bold bg-white' : 'border-transparent text-slate-500 hover:text-slate-800'"
                class="px-4 py-2.5 text-xs rounded-t-xl border-b-2 transition-all flex items-center space-x-2 shrink-0 cursor-pointer">
            <span>🔌</span>
            <span>Panduan Koneksi AI Client</span>
        </button>

        <button type="button" 
                @click="activeTab = 'tools'" 
                :class="activeTab === 'tools' ? 'border-sky-600 text-sky-700 font-bold bg-white' : 'border-transparent text-slate-500 hover:text-slate-800'"
                class="px-4 py-2.5 text-xs rounded-t-xl border-b-2 transition-all flex items-center space-x-2 shrink-0 cursor-pointer">
            <span>🛠️</span>
            <span>Katalog Tool & Schema MCP</span>
        </button>
    </div>

    <!-- TAB 1: LIVE TESTER DETEKSI HS & PACKING LIST -->
    <div x-show="activeTab === 'tester'" class="space-y-6">
        
        <!-- Sub-Card 1: Cek HS Code Tunggal -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 flex items-center space-x-2">
                        <span class="w-6 h-6 rounded-lg bg-sky-100 text-sky-700 flex items-center justify-center text-xs">1</span>
                        <span>Uji Deteksi Pos Tarif HS Code & Lartas (check_hs_code)</span>
                    </h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">Ketik nama komoditas dalam bahasa Inggris atau Indonesia untuk melihat pencocokan pos tarif 8-digit BTKI, tarif BM/PPN/PPh, dan izin Lartas.</p>
                </div>
                <span class="text-xs font-mono text-slate-400">Tool: check_hs_code</span>
            </div>

            <!-- Quick Chips / Contoh Kasus Nyata -->
            <div class="mb-3">
                <span class="text-[11px] font-semibold text-slate-600 mr-2">Contoh Cepat dari Dokumen:</span>
                <div class="inline-flex flex-wrap gap-1.5 mt-1">
                    @foreach([
                        'HYDRAULIC BREAKER DYB800 STRAIGHT TYPE',
                        'HYDRAULIC BREAKER SPAREPART : CHISEL SB40 PLUS TYPE',
                        'HYDRAULIC BREAKER SPAREPART : SIDE BOLT SB40',
                        'SBS WATERPROOFING MEMBRANE PE 10M*1M*3MM',
                        'PNEUMATIC PICK G10',
                        'PNEUMATIC PICK SPAREPART : CHISEL TYPE PLUS TPB60',
                        'HAND PALLET (NYLON CASTER) 3T 1200*685',
                        'HAND PALLET SPAREPART : OIL CYLINDER 2T',
                        'BENDING MACHINE GW-50',
                        'PARANET 75% BLACK 100M*3M',
                        'WEBBING SLING BELT YELLOW 3T*10M'
                    ] as $sampleChip)
                        <button type="button" 
                                @click="testQuery = '{{ $sampleChip }}'; runCheckHs()" 
                                class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[10px] font-medium transition-colors cursor-pointer">
                            {{ $sampleChip }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Input & Action -->
            <div class="flex flex-col sm:flex-row gap-2.5">
                <div class="flex-1">
                    <input type="text" 
                           x-model="testQuery" 
                           @keydown.enter="runCheckHs()"
                           placeholder="Masukkan nama komoditas atau nomor HS Code..." 
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 font-mono text-xs focus:ring-2 focus:ring-sky-500 focus:outline-none">
                </div>
                <div class="w-48">
                    <select x-model="testDirection" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-sky-500">
                        <option value="IMPORT_BC20">Impor (PIB BC 2.0)</option>
                        <option value="EKSPORT_BC30">Ekspor (PEB BC 3.0)</option>
                    </select>
                </div>
                <button type="button" 
                        @click="runCheckHs()" 
                        :disabled="testLoading"
                        class="px-5 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 disabled:bg-slate-300 text-white text-xs font-bold transition-all shadow-sm flex items-center justify-center space-x-1.5 shrink-0 cursor-pointer">
                    <span x-show="!testLoading">Uji Tool via MCP &rarr;</span>
                    <span x-show="testLoading">Memproses...</span>
                </button>
            </div>

            <!-- Result Box -->
            <div x-show="testResult" class="mt-4 p-4 rounded-xl border bg-slate-50/80 border-slate-200 space-y-3">
                <template x-if="testResult && testResult.data">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-xs">
                        <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-2xs">
                            <span class="text-slate-400 block font-medium">Pos Tarif (BTKI 2022)</span>
                            <span class="text-base font-mono font-black text-sky-800" x-text="testResult.data.code"></span>
                            <div class="mt-1">
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200" 
                                      x-text="Math.round((testResult.data.confidence_score || 1.0) * 100) + '% Akurasi'"></span>
                            </div>
                        </div>
                        <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-2xs md:col-span-2">
                            <span class="text-slate-400 block font-medium">Uraian Resmi Kepabeanan</span>
                            <span class="font-bold text-slate-800 block text-xs" x-text="testResult.data.desc"></span>
                            <span class="text-[10px] text-slate-400 mt-0.5 block" x-text="testResult.data.legal"></span>
                        </div>
                        <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-2xs">
                            <span class="text-slate-400 block font-medium">Tarif Pajak Impor</span>
                            <div class="font-mono font-bold text-slate-800 mt-0.5">
                                BM: <span x-text="testResult.data.bm + '%'"></span> | 
                                PPN: <span x-text="testResult.data.ppn + '%'"></span> | 
                                PPh: <span x-text="testResult.data.pph + '%'"></span>
                            </div>
                            <div class="mt-1">
                                <span :class="testResult.data.has_lartas ? 'bg-amber-100 text-amber-800 border-amber-300' : 'bg-emerald-100 text-emerald-800 border-emerald-300'"
                                      class="px-1.5 py-0.5 rounded text-[9px] font-bold border"
                                      x-text="testResult.data.has_lartas ? 'LARTAS AKTIF (' + testResult.data.lartas_note + ')' : 'BEBAS LARTAS'">
                                </span>
                            </div>
                        </div>
                    </div>
                </template>
                <template x-if="testResult && testResult.error">
                    <div class="p-3 bg-rose-50 text-rose-800 border border-rose-200 rounded-xl text-xs font-semibold" x-text="testResult.error"></div>
                </template>
            </div>
        </div>

        <!-- Sub-Card 2: Batch Parsing Packing List -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 flex items-center space-x-2">
                        <span class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs">2</span>
                        <span>Auto-Match Packing List Massal (parse_packing_list)</span>
                    </h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">Tempel teks mentah Packing List dari supplier luar negeri, AI Agent akan mengekstrak item, kemasan, bobot, dan mencocokkan pos tarif HS code otomatis.</p>
                </div>
                <span class="text-xs font-mono text-slate-400">Tool: parse_packing_list</span>
            </div>

            <div class="space-y-3">
                <textarea x-model="batchText" 
                          rows="7" 
                          placeholder="Tempel isi Packing List di sini..." 
                          class="w-full p-3.5 rounded-xl border border-slate-200 font-mono text-xs focus:ring-2 focus:ring-sky-500 focus:outline-none"></textarea>

                <div class="flex items-center justify-between">
                    <button type="button" 
                            @click="batchText = @json($samplePackingList)" 
                            class="text-xs text-sky-700 hover:underline font-semibold cursor-pointer">
                        &larr; Muat Ulang Contoh 17 Item Samarinda
                    </button>
                    <button type="button" 
                            @click="runBatchPackingList()" 
                            :disabled="batchLoading"
                            class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-300 text-white text-xs font-bold transition-all shadow-sm flex items-center space-x-1.5 cursor-pointer">
                        <span x-show="!batchLoading">Jalankan Ekstraksi & Pencocokan HS Massal &rarr;</span>
                        <span x-show="batchLoading">Menganalisis Dokumen...</span>
                    </button>
                </div>
            </div>

            <!-- Batch Result Table -->
            <div x-show="batchResult" class="mt-4 pt-4 border-t border-slate-100 space-y-3">
                <template x-if="batchResult && batchResult.items">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-slate-800">
                                Ditemukan <span class="text-sky-700" x-text="batchResult.items.length"></span> Item Komoditas:
                            </span>
                            <span class="text-[11px] font-mono text-slate-500">
                                Total Gross: <strong x-text="batchResult.total_gross_weight"></strong> KGS
                            </span>
                        </div>
                        <div class="border border-slate-200 rounded-xl overflow-x-auto max-h-96">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase text-[10px]">
                                    <tr>
                                        <th class="p-2.5">No</th>
                                        <th class="p-2.5">Deskripsi Barang</th>
                                        <th class="p-2.5">Qty / Sat</th>
                                        <th class="p-2.5">Berat</th>
                                        <th class="p-2.5">HS Code Rekomendasi</th>
                                        <th class="p-2.5">BM</th>
                                        <th class="p-2.5">Lartas</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-mono text-[11px]">
                                    <template x-for="(item, idx) in batchResult.items" :key="idx">
                                        <tr class="hover:bg-slate-50/80">
                                            <td class="p-2.5 text-slate-400" x-text="idx + 1"></td>
                                            <td class="p-2.5 font-bold font-sans text-slate-800" x-text="item.description"></td>
                                            <td class="p-2.5 text-slate-600" x-text="(item.quantity || '-') + ' ' + (item.unit || '')"></td>
                                            <td class="p-2.5 text-slate-600" x-text="(item.gross_weight || '-') + ' KGS'"></td>
                                            <td class="p-2.5">
                                                <span class="font-bold text-sky-800 bg-sky-50 px-2 py-0.5 rounded border border-sky-200" x-text="item.suggested_hs_code"></span>
                                            </td>
                                            <td class="p-2.5 text-slate-700" x-text="(item.tariff_rates?.bm ?? 0) + '%'"></td>
                                            <td class="p-2.5">
                                                <span :class="item.lartas?.required ? 'text-amber-700 bg-amber-50 border-amber-200' : 'text-emerald-700 bg-emerald-50 border-emerald-200'"
                                                      class="px-1.5 py-0.5 rounded text-[9px] font-bold border font-sans"
                                                      x-text="item.lartas?.required ? 'LARTAS' : 'BEBAS'"></span>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>
            </div>
        </div>

    </div>

    <!-- TAB 2: KUNCI AKSES AI (MCP TOKENS MANAGEMENT) -->
    <div x-show="activeTab === 'tokens'" class="space-y-6">
        
        <!-- Header & Action Button -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-sm font-bold text-slate-900 flex items-center space-x-2">
                    <span>🔑</span>
                    <span>Daftar Kunci Akses MCP (API Bearer Tokens)</span>
                </h2>
                <p class="text-xs text-slate-500 mt-1 max-w-xl">
                    Kunci token digunakan oleh Klien AI (Claude Desktop, Cursor, Antigravity, skrip otomatisasi) untuk autentikasi saat memanggil tool kepabeanan GENIE secara aman.
                </p>
            </div>
            <button type="button" 
                    @click="showTokenModal = true" 
                    class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold shadow-xs transition-all active:scale-95 flex items-center justify-center space-x-2 shrink-0 cursor-pointer">
                <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Buat Token MCP Baru</span>
            </button>
        </div>

        <!-- Tabel Daftar Token Aktif & Terdaftar -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <span class="text-xs font-bold text-slate-800">Token Terdaftar ({{ count($tokens) }})</span>
                <span class="text-[11px] text-slate-500">Format: <code class="font-mono text-sky-700">genie_mcp_live_...</code></span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-bold uppercase text-[10px]">
                        <tr>
                            <th class="p-3.5">Nama Token & Klien</th>
                            <th class="p-3.5">Token Masked / Hash</th>
                            <th class="p-3.5">Izin (Scopes)</th>
                            <th class="p-3.5">Status</th>
                            <th class="p-3.5">Terakhir Digunakan</th>
                            <th class="p-3.5">Dibuat Pada</th>
                            <th class="p-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($tokens as $t)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="p-3.5">
                                <div class="font-bold text-slate-900">{{ $t->name }}</div>
                                <div class="mt-0.5">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono uppercase font-bold
                                        @if($t->provider_hint === 'claude') bg-amber-50 text-amber-700 border border-amber-200
                                        @elseif($t->provider_hint === 'cursor') bg-blue-50 text-blue-700 border border-blue-200
                                        @elseif($t->provider_hint === 'antigravity') bg-purple-50 text-purple-700 border border-purple-200
                                        @else bg-slate-100 text-slate-700 border border-slate-200 @endif">
                                        {{ $t->provider_hint }}
                                    </span>
                                </div>
                            </td>
                            <td class="p-3.5 font-mono text-[11px] text-slate-600">
                                <span>genie_mcp_••••••{{ substr($t->token_hash, -8) }}</span>
                            </td>
                            <td class="p-3.5">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($t->scope_roles ?? ['*'] as $scope)
                                        <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded text-[10px] font-mono">
                                            {{ $scope }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="p-3.5">
                                @if($t->isValid())
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Aktif
                                    </span>
                                @elseif($t->isRevoked())
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                        Dicabut
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700">
                                        Kedaluwarsa
                                    </span>
                                @endif
                            </td>
                            <td class="p-3.5 text-slate-600 text-[11px]">
                                {{ $t->last_used_at ? $t->last_used_at->diffForHumans() : 'Belum pernah' }}
                            </td>
                            <td class="p-3.5 text-slate-500 text-[11px]">
                                {{ $t->created_at->format('d M Y, H:i') }}
                            </td>
                            <td class="p-3.5 text-right">
                                @if($t->isValid())
                                <form action="{{ route('settings.mcp.tokens.revoke', $t->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mencabut kunci token ini? Klien AI yang menggunakannya tidak akan dapat mengakses MCP server lagi.');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 text-rose-600 hover:text-white hover:bg-rose-600 border border-rose-200 rounded-lg text-[11px] font-semibold transition-all cursor-pointer">
                                        Cabut Akses
                                    </button>
                                </form>
                                @else
                                <span class="text-slate-400 text-[11px] italic">Nonaktif</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-400">
                                Belum ada token MCP yang dibuat. Klik tombol <strong>"Buat Token MCP Baru"</strong> di atas untuk membuat kunci pertama Anda.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Audit Trail Log Pemanggilan Tool MCP -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center space-x-2">
                        <span>📋</span>
                        <span>Audit Log Pemanggilan Tool MCP (15 Aktivitas Terakhir)</span>
                    </h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Catatan forensik setiap kali klien AI eksternal mengeksekusi tool kepabeanan GENIE.</p>
                </div>
                <span class="text-xs font-mono text-slate-500">Total Bulan Ini: <strong class="text-slate-800">{{ $totalCallsThisMonth }}</strong> panggilan</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase text-[10px]">
                        <tr>
                            <th class="p-2.5">Waktu</th>
                            <th class="p-2.5">Tool MCP</th>
                            <th class="p-2.5">Klien / Provider</th>
                            <th class="p-2.5">Status</th>
                            <th class="p-2.5">Argumen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-mono text-[11px]">
                        @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/80">
                            <td class="p-2.5 text-slate-500 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td class="p-2.5 font-bold text-sky-800">{{ $log->tool }}</td>
                            <td class="p-2.5">
                                <span class="px-2 py-0.5 rounded text-[10px] bg-slate-100 text-slate-700">
                                    {{ $log->provider ?? 'unknown' }}
                                </span>
                            </td>
                            <td class="p-2.5">
                                @if($log->result_status === 'SUCCESS')
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800">SUCCESS</span>
                                @else
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-rose-100 text-rose-800">{{ $log->result_status ?? 'FAIL' }}</span>
                                @endif
                            </td>
                            <td class="p-2.5 text-slate-600 truncate max-w-xs font-sans text-[11px]">
                                {{ json_encode($log->args) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="p-6 text-center text-slate-400 font-sans">
                                Belum ada log pemanggilan tool.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- TAB 3: PANDUAN KONEKSI KLIEN AI -->
    <div x-show="activeTab === 'guide'" class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-6">
        <div>
            <h2 class="text-sm font-bold text-slate-900 flex items-center space-x-2">
                <span>🔌</span>
                <span>Cara Menghubungkan Klien AI ke GENIE MCP Server</span>
            </h2>
            <p class="text-xs text-slate-500 mt-1">Pilih platform AI yang Anda gunakan di bawah untuk melihat konfigurasi siap salin.</p>
        </div>

        <!-- Token Selector / Custom Token Input -->
        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex-1">
                <label class="block text-xs font-bold text-slate-700 mb-1">
                    Token Akses yang Digunakan dalam Cuplikan Kode:
                </label>
                <input type="text" 
                       x-model="customToken" 
                       placeholder="genie_mcp_live_... (atau buat baru di tab Kunci Akses)" 
                       class="w-full px-3 py-2 rounded-lg border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-sky-500 bg-white">
            </div>
            <div class="shrink-0 flex items-end">
                <button type="button" 
                        @click="activeTab = 'tokens'; showTokenModal = true" 
                        class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold transition-all shadow-xs cursor-pointer active:scale-95">
                    <svg class="w-3.5 h-3.5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Buat Kunci Baru</span>
                </button>
            </div>
        </div>

        <!-- Client Selector Tabs -->
        <div class="flex border-b border-slate-200 gap-2">
            <button type="button" 
                    @click="activeProvider = 'claude'" 
                    :class="activeProvider === 'claude' ? 'border-sky-600 text-sky-700 font-bold' : 'border-transparent text-slate-400 hover:text-slate-700'"
                    class="px-3 py-2 text-xs border-b-2 font-medium cursor-pointer">
                Claude Desktop
            </button>
            <button type="button" 
                    @click="activeProvider = 'cursor'" 
                    :class="activeProvider === 'cursor' ? 'border-sky-600 text-sky-700 font-bold' : 'border-transparent text-slate-400 hover:text-slate-700'"
                    class="px-3 py-2 text-xs border-b-2 font-medium cursor-pointer">
                Cursor IDE
            </button>
            <button type="button" 
                    @click="activeProvider = 'antigravity'" 
                    :class="activeProvider === 'antigravity' ? 'border-sky-600 text-sky-700 font-bold' : 'border-transparent text-slate-400 hover:text-slate-700'"
                    class="px-3 py-2 text-xs border-b-2 font-medium cursor-pointer">
                Google Antigravity AI
            </button>
            <button type="button" 
                    @click="activeProvider = 'python'" 
                    :class="activeProvider === 'python' ? 'border-sky-600 text-sky-700 font-bold' : 'border-transparent text-slate-400 hover:text-slate-700'"
                    class="px-3 py-2 text-xs border-b-2 font-medium cursor-pointer">
                Python Script
            </button>
            <button type="button" 
                    @click="activeProvider = 'curl'" 
                    :class="activeProvider === 'curl' ? 'border-sky-600 text-sky-700 font-bold' : 'border-transparent text-slate-400 hover:text-slate-700'"
                    class="px-3 py-2 text-xs border-b-2 font-medium cursor-pointer">
                cURL CLI
            </button>
        </div>

        <!-- Claude Desktop Guide -->
        <div x-show="activeProvider === 'claude'" class="space-y-4">
            <div class="text-xs text-slate-600">
                Tambahkan konfigurasi berikut ke file <code>claude_desktop_config.json</code> Anda (lokasi: <code>%APPDATA%\Claude\claude_desktop_config.json</code> di Windows):
            </div>
            <div class="relative">
                <pre class="bg-slate-950 text-sky-300 p-4 rounded-xl text-xs font-mono overflow-x-auto" 
                     x-text="JSON.stringify({
    'mcpServers': {
        'genie-customs': {
            'url': '{{ $sseEndpoint }}?token=' + getToken()
        }
    }
}, null, 2)"></pre>
                <button type="button" 
                        @click="copyToClipboard(JSON.stringify({
    'mcpServers': {
        'genie-customs': {
            'url': '{{ $sseEndpoint }}?token=' + getToken()
        }
    }
}, null, 2), 'Konfigurasi Claude Desktop disalin!')" 
                        class="absolute top-3 right-3 px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-white text-[11px] font-semibold transition-colors cursor-pointer">
                    Salin JSON
                </button>
            </div>
        </div>

        <!-- Cursor IDE Guide -->
        <div x-show="activeProvider === 'cursor'" class="space-y-4">
            <div class="text-xs text-slate-600">
                Buka <strong>Cursor Settings &rarr; Features &rarr; MCP Servers</strong>, lalu tambahkan server baru bertipe <strong>SSE</strong>:
            </div>
            <div class="relative">
                <pre class="bg-slate-950 text-emerald-300 p-4 rounded-xl text-xs font-mono overflow-x-auto" 
                     x-text="JSON.stringify({
    'mcpServers': {
        'genie-customs': {
            'url': '{{ $sseEndpoint }}?token=' + getToken()
        }
    }
}, null, 2)"></pre>
                <button type="button" 
                        @click="copyToClipboard(JSON.stringify({
    'mcpServers': {
        'genie-customs': {
            'url': '{{ $sseEndpoint }}?token=' + getToken()
        }
    }
}, null, 2), 'Konfigurasi Cursor disalin!')" 
                        class="absolute top-3 right-3 px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-white text-[11px] font-semibold transition-colors cursor-pointer">
                    Salin JSON
                </button>
            </div>
        </div>

        <!-- Antigravity AI Guide -->
        <div x-show="activeProvider === 'antigravity'" class="space-y-4">
            <div class="text-xs text-slate-600">
                Server MCP GENIE mendukung Model Context Protocol standard 2024-11-05. Anda dapat mendaftarkan endpoint SSE langsung pada konfigurasi MCP Antigravity:
            </div>
            <div class="p-3 bg-slate-100 rounded-xl font-mono text-xs text-slate-800 flex items-center justify-between">
                <span x-text="'{{ $sseEndpoint }}?token=' + getToken()"></span>
                <button type="button" 
                        @click="copyToClipboard('{{ $sseEndpoint }}?token=' + getToken())" 
                        class="text-sky-700 hover:underline font-bold cursor-pointer">
                    Salin URL
                </button>
            </div>
        </div>

        <!-- Python Script Guide -->
        <div x-show="activeProvider === 'python'" class="space-y-4">
            <div class="text-xs text-slate-600">
                Contoh memanggil tool MCP via Python Requests dengan Bearer Token:
            </div>
            <div class="relative">
                <pre class="bg-slate-950 text-indigo-300 p-4 rounded-xl text-xs font-mono overflow-x-auto"
                     x-text="`import requests

GENIE_TOKEN = '${getToken()}'
EXECUTE_URL = '{{ $executeEndpoint }}'

# 1. Panggil MCP Tool: Cek Pos Tarif HS Code & Dokumen Lartas
response = requests.post(
    EXECUTE_URL,
    headers={
        'Authorization': f'Bearer {GENIE_TOKEN}',
        'Content-Type': 'application/json'
    },
    json={
        'jsonrpc': '2.0',
        'id': 1,
        'method': 'tools/call',
        'params': {
            'name': 'check_hs_code',
            'arguments': {
                'query': 'HYDRAULIC BREAKER DYB800 STRAIGHT TYPE',
                'direction': 'IMPORT_BC20'
            }
        }
    }
)

result = response.json()
print('Hasil Deteksi Pos Tarif:', result['result']['structured_data'])`"></pre>
                <button type="button" 
                        @click="copyToClipboard(`import requests

GENIE_TOKEN = '${getToken()}'
EXECUTE_URL = '{{ $executeEndpoint }}'

response = requests.post(
    EXECUTE_URL,
    headers={
        'Authorization': f'Bearer {GENIE_TOKEN}',
        'Content-Type': 'application/json'
    },
    json={
        'jsonrpc': '2.0',
        'id': 1,
        'method': 'tools/call',
        'params': {
            'name': 'check_hs_code',
            'arguments': {
                'query': 'HYDRAULIC BREAKER DYB800 STRAIGHT TYPE',
                'direction': 'IMPORT_BC20'
            }
        }
    }
)

result = response.json()
print('Hasil Deteksi Pos Tarif:', result['result']['structured_data'])`, 'Kode Python disalin!')" 
                        class="absolute top-3 right-3 px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-white text-[11px] font-semibold transition-colors cursor-pointer">
                    Salin Kode
                </button>
            </div>
        </div>

        <!-- cURL Guide -->
        <div x-show="activeProvider === 'curl'" class="space-y-4">
            <div class="text-xs text-slate-600">
                Contoh request cURL CLI:
            </div>
            <div class="relative">
                <pre class="bg-slate-950 text-amber-300 p-4 rounded-xl text-xs font-mono overflow-x-auto"
                     x-text="`curl -X POST '{{ $executeEndpoint }}' \\
  -H 'Authorization: Bearer ${getToken()}' \\
  -H 'Content-Type: application/json' \\
  -d '{
    \"jsonrpc\": \"2.0\",
    \"id\": 1,
    \"method\": \"tools/call\",
    \"params\": {
      \"name\": \"check_hs_code\",
      \"arguments\": {
        \"query\": \"HYDRAULIC BREAKER DYB800\"
      }
    }
  }'`"></pre>
                <button type="button" 
                        @click="copyToClipboard(`curl -X POST '{{ $executeEndpoint }}' \\
  -H 'Authorization: Bearer ${getToken()}' \\
  -H 'Content-Type: application/json' \\
  -d '{
    \"jsonrpc\": \"2.0\",
    \"id\": 1,
    \"method\": \"tools/call\",
    \"params\": {
      \"name\": \"check_hs_code\",
      \"arguments\": {
        \"query\": \"HYDRAULIC BREAKER DYB800\"
      }
    }
  }'`, 'cURL command disalin!')" 
                        class="absolute top-3 right-3 px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-white text-[11px] font-semibold transition-colors cursor-pointer">
                    Salin cURL
                </button>
            </div>
        </div>
    </div>

    <!-- TAB 4: KATALOG TOOL MCP -->
    <div x-show="activeTab === 'tools'" class="space-y-4">
        <!-- Filter Bar & Search -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex items-center space-x-1.5 overflow-x-auto pb-1 md:pb-0">
                <button type="button" 
                        @click="toolCategory = 'ALL'" 
                        :class="toolCategory === 'ALL' ? 'bg-sky-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="px-3 py-1.5 rounded-xl text-xs font-medium transition-all shrink-0 cursor-pointer">
                    Semua ({{ count($tools) }})
                </button>
                @php
                    $categories = collect($tools)->pluck('category')->filter()->unique()->values();
                @endphp
                @foreach($categories as $cat)
                <button type="button" 
                        @click="toolCategory = '{{ $cat }}'" 
                        :class="toolCategory === '{{ $cat }}' ? 'bg-sky-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="px-3 py-1.5 rounded-xl text-xs font-medium transition-all shrink-0 cursor-pointer">
                    {{ $cat }}
                </button>
                @endforeach
            </div>
            <div class="w-full md:w-64">
                <input type="text" 
                       x-model="toolSearch" 
                       placeholder="Cari tool MCP..." 
                       class="w-full px-3 py-1.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-sky-500">
            </div>
        </div>

        <!-- Tool Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($tools as $tool)
                @php
                    $cat = $tool['category'] ?? 'General';
                    $name = $tool['name'];
                @endphp
                <div x-show="(toolCategory === 'ALL' || toolCategory === '{{ $cat }}') && ('{{ strtolower($name) }}'.includes(toolSearch.toLowerCase()) || '{{ strtolower($tool['description']) }}'.includes(toolSearch.toLowerCase()))"
                     class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between hover:border-sky-300 transition-all">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-mono font-bold text-sky-800 text-sm bg-sky-50 px-2 py-0.5 rounded border border-sky-200">
                                {{ $tool['name'] }}
                            </span>
                            <span class="text-[10px] px-2 py-0.5 rounded-md font-bold bg-slate-100 text-slate-600">
                                {{ $cat }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed mb-3">
                            {{ $tool['description'] }}
                        </p>
                    </div>
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                        <div class="flex flex-wrap gap-1 font-mono text-[10px]">
                            @if(!empty($tool['inputSchema']['properties']))
                                @foreach($tool['inputSchema']['properties'] as $propName => $prop)
                                    <span class="px-2 py-0.5 bg-slate-100 rounded text-slate-700">
                                        {{ $propName }} ({{ $prop['type'] ?? 'any' }})
                                    </span>
                                @endforeach
                            @else
                                <span class="text-slate-400 italic text-[11px] font-sans">Tanpa parameter wajib</span>
                            @endif
                        </div>
                        <button type="button" 
                                @click="copyToClipboard('{{ $tool['name'] }}', 'Nama tool disalin!')" 
                                class="text-[11px] text-sky-600 hover:text-sky-800 font-semibold shrink-0 cursor-pointer">
                            Salin Nama
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- MODAL POPUP: BUAT KUNCI AKSES MCP BARU (RBAC Granular Scopes) -->
    <div x-show="showTokenModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-7 shadow-2xl border border-slate-200 space-y-5"
             @click.away="showTokenModal = false">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center space-x-2">
                    <div class="w-9 h-9 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center text-base font-bold shadow-2xs">
                        🔑
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Buat Token Akses MCP Baru</h3>
                        <p class="text-[11px] text-slate-500">Konfigurasikan peran, izin CRUD, dan otoritas persetujuan (Approval) untuk AI Agent Anda.</p>
                    </div>
                </div>
                <button type="button" @click="showTokenModal = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition-colors cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('settings.mcp.tokens.store') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Nama Token -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Nama Token / Identitas Klien <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           required 
                           placeholder="Contoh: Claude Desktop - Impor Samarinda, Cursor IDE Tim AI, Production Agent"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-sky-500 focus:outline-none">
                </div>

                <!-- Provider & Expiry Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Aplikasi / Provider Hint
                        </label>
                        <select name="provider_hint" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-sky-500 bg-white">
                            <option value="all">Universal (Semua Klien)</option>
                            <option value="claude">Claude Desktop</option>
                            <option value="cursor">Cursor IDE</option>
                            <option value="antigravity">Google Antigravity AI</option>
                            <option value="openai">OpenAI / ChatGPT</option>
                            <option value="gemini">Google Gemini</option>
                            <option value="python">Python / Automasi Script</option>
                            <option value="custom">Custom Client</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Masa Berlaku Token
                        </label>
                        <select name="expires_in" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-sky-500 bg-white">
                            <option value="never">Permanen (Tidak Pernah Kedaluwarsa)</option>
                            <option value="30_days">30 Hari</option>
                            <option value="90_days">90 Hari</option>
                            <option value="1_year">1 Tahun</option>
                        </select>
                    </div>
                </div>

                <!-- Hak Akses & Matriks Izin Granular (RBAC Selector) -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-800">
                            Matriks Hak Akses & Otoritas MCP (Granular RBAC)
                        </label>
                        <span class="text-[10px] text-slate-500 font-mono" x-text="selectedScopes.includes('*') ? 'Semua Hak Akses (Administrator)' : selectedScopes.length + ' Izin Dipilih'"></span>
                    </div>

                    <!-- Preset Akses Cepat -->
                    <div class="flex flex-wrap gap-1.5 mb-2.5">
                        <button type="button" 
                                @click="setScopePreset('admin')"
                                :class="selectedScopes.includes('*') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                class="px-2.5 py-1 rounded-lg text-[11px] font-semibold transition-all cursor-pointer">
                            🛡️ Full Admin (*)
                        </button>
                        <button type="button" 
                                @click="setScopePreset('ops')"
                                :class="!selectedScopes.includes('*') && selectedScopes.includes('customs:create') && !selectedScopes.includes('finance:approve') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                class="px-2.5 py-1 rounded-lg text-[11px] font-semibold transition-all cursor-pointer">
                            🚢 Operasional & Kepabeanan
                        </button>
                        <button type="button" 
                                @click="setScopePreset('finance')"
                                :class="!selectedScopes.includes('*') && selectedScopes.includes('finance:approve') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                class="px-2.5 py-1 rounded-lg text-[11px] font-semibold transition-all cursor-pointer">
                            💰 Keuangan & Approval
                        </button>
                        <button type="button" 
                                @click="setScopePreset('readonly')"
                                :class="!selectedScopes.includes('*') && selectedScopes.includes('approval:read') && !selectedScopes.includes('customs:create') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                class="px-2.5 py-1 rounded-lg text-[11px] font-semibold transition-all cursor-pointer">
                            👁️ Auditor (Read-Only)
                        </button>
                    </div>

                    <!-- Daftar Checkbox Granular -->
                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200 space-y-3.5 max-h-64 overflow-y-auto">
                        <!-- Akses Penuh Wildcard -->
                        <div class="pb-2 border-b border-slate-200">
                            <label class="flex items-center space-x-2.5 text-xs font-bold text-slate-900 cursor-pointer">
                                <input type="checkbox" 
                                       name="scope_roles[]" 
                                       value="*" 
                                       :checked="selectedScopes.includes('*')"
                                       @change="toggleScope('*')"
                                       class="rounded text-sky-600 focus:ring-sky-500 w-4 h-4">
                                <span class="flex-1">
                                    <span class="text-sky-700 font-black">* (Akses Penuh Seluruh 27 Tools ERP, CRUD & Approvals)</span>
                                    <span class="block text-[10px] font-normal text-slate-500">Mencakup eksekusi instruksi apa pun yang didukung oleh server GENIE.</span>
                                </span>
                            </label>
                        </div>

                        <!-- 1. Persetujuan & Tata Kelola (Governance) -->
                        <div class="space-y-1.5">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-purple-700 flex items-center gap-1">
                                ⚖️ Persetujuan & Tata Kelola Sistem (Governance)
                            </span>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                <label class="flex items-start space-x-2 cursor-pointer p-1.5 rounded-lg hover:bg-white transition-colors">
                                    <input type="checkbox" name="scope_roles[]" value="governance:approve" 
                                           :checked="isScopeChecked('governance:approve')" @change="toggleScope('governance:approve')"
                                           class="rounded text-purple-600 focus:ring-purple-500 mt-0.5">
                                    <span class="text-[11px] text-slate-700">
                                        <strong class="text-purple-800">governance:approve</strong>
                                        <span class="block text-[10px] text-slate-500">Otoritas Menyetujui/Menolak Aksi (Customs, Bills, Advances, Proposals)</span>
                                    </span>
                                </label>
                                <label class="flex items-start space-x-2 cursor-pointer p-1.5 rounded-lg hover:bg-white transition-colors">
                                    <input type="checkbox" name="scope_roles[]" value="governance:delete" 
                                           :checked="isScopeChecked('governance:delete')" @change="toggleScope('governance:delete')"
                                           class="rounded text-rose-600 focus:ring-rose-500 mt-0.5">
                                    <span class="text-[11px] text-slate-700">
                                        <strong class="text-rose-700">governance:delete</strong>
                                        <span class="block text-[10px] text-slate-500">Otoritas Void / Menghapus Rekaman Data (Shipment, Invoice, Bill)</span>
                                    </span>
                                </label>
                                <label class="flex items-start space-x-2 cursor-pointer p-1.5 rounded-lg hover:bg-white transition-colors sm:col-span-2">
                                    <input type="checkbox" name="scope_roles[]" value="approval:read" 
                                           :checked="isScopeChecked('approval:read')" @change="toggleScope('approval:read')"
                                           class="rounded text-sky-600 focus:ring-sky-500 mt-0.5">
                                    <span class="text-[11px] text-slate-700">
                                        <strong class="text-slate-800">approval:read</strong>
                                        <span class="block text-[10px] text-slate-500">Melihat daftar antrean yang menunggu persetujuan tim</span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <!-- 2. Kepabeanan & CEISA 4.0 -->
                        <div class="space-y-1.5 pt-2 border-t border-slate-200/80">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-sky-700 flex items-center gap-1">
                                🛃 Kepabeanan & CEISA 4.0 (Customs)
                            </span>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                <label class="flex items-start space-x-2 cursor-pointer p-1.5 rounded-lg hover:bg-white transition-colors">
                                    <input type="checkbox" name="scope_roles[]" value="customs:read" 
                                           :checked="isScopeChecked('customs:read')" @change="toggleScope('customs:read')"
                                           class="rounded text-sky-600 focus:ring-sky-500 mt-0.5">
                                    <span class="text-[11px] text-slate-700">
                                        <strong>customs:read</strong>
                                        <span class="block text-[10px] text-slate-500">Cek Pos Tarif BTKI, Kurs Pajak & Status CEISA</span>
                                    </span>
                                </label>
                                <label class="flex items-start space-x-2 cursor-pointer p-1.5 rounded-lg hover:bg-white transition-colors">
                                    <input type="checkbox" name="scope_roles[]" value="customs:create" 
                                           :checked="isScopeChecked('customs:create')" @change="toggleScope('customs:create')"
                                           class="rounded text-sky-600 focus:ring-sky-500 mt-0.5">
                                    <span class="text-[11px] text-slate-700">
                                        <strong>customs:create</strong>
                                        <span class="block text-[10px] text-slate-500">Parse Packing List & Generate Draft Deklarasi PIB/PEB</span>
                                    </span>
                                </label>
                                <label class="flex items-start space-x-2 cursor-pointer p-1.5 rounded-lg hover:bg-white transition-colors">
                                    <input type="checkbox" name="scope_roles[]" value="customs:approve" 
                                           :checked="isScopeChecked('customs:approve')" @change="toggleScope('customs:approve')"
                                           class="rounded text-purple-600 focus:ring-purple-500 mt-0.5">
                                    <span class="text-[11px] text-slate-700">
                                        <strong class="text-purple-700">customs:approve</strong>
                                        <span class="block text-[10px] text-slate-500">Approval Final Deklarasi Pabean Siap Submit</span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <!-- 3. Operasional Pengapalan (P01) -->
                        <div class="space-y-1.5 pt-2 border-t border-slate-200/80">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 flex items-center gap-1">
                                🚢 Operasional Pengapalan & Ekspedisi
                            </span>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                <label class="flex items-start space-x-2 cursor-pointer p-1.5 rounded-lg hover:bg-white transition-colors">
                                    <input type="checkbox" name="scope_roles[]" value="shipment:read" 
                                           :checked="isScopeChecked('shipment:read')" @change="toggleScope('shipment:read')"
                                           class="rounded text-emerald-600 focus:ring-emerald-500 mt-0.5">
                                    <span class="text-[11px] text-slate-700">
                                        <strong>shipment:read</strong>
                                        <span class="block text-[10px] text-slate-500">Lihat Proyek Pengapalan, B/L & Kontainer</span>
                                    </span>
                                </label>
                                <label class="flex items-start space-x-2 cursor-pointer p-1.5 rounded-lg hover:bg-white transition-colors">
                                    <input type="checkbox" name="scope_roles[]" value="shipment:create" 
                                           :checked="isScopeChecked('shipment:create')" @change="toggleScope('shipment:create')"
                                           class="rounded text-emerald-600 focus:ring-emerald-500 mt-0.5">
                                    <span class="text-[11px] text-slate-700">
                                        <strong>shipment:create</strong>
                                        <span class="block text-[10px] text-slate-500">Buat Proyek Shipment & Booking Kargo Baru</span>
                                    </span>
                                </label>
                                <label class="flex items-start space-x-2 cursor-pointer p-1.5 rounded-lg hover:bg-white transition-colors">
                                    <input type="checkbox" name="scope_roles[]" value="shipment:update" 
                                           :checked="isScopeChecked('shipment:update')" @change="toggleScope('shipment:update')"
                                           class="rounded text-emerald-600 focus:ring-emerald-500 mt-0.5">
                                    <span class="text-[11px] text-slate-700">
                                        <strong>shipment:update</strong>
                                        <span class="block text-[10px] text-slate-500">Update Status Milestone & Timeline</span>
                                    </span>
                                </label>
                                <label class="flex items-start space-x-2 cursor-pointer p-1.5 rounded-lg hover:bg-white transition-colors">
                                    <input type="checkbox" name="scope_roles[]" value="shipment:delete" 
                                           :checked="isScopeChecked('shipment:delete')" @change="toggleScope('shipment:delete')"
                                           class="rounded text-rose-600 focus:ring-rose-500 mt-0.5">
                                    <span class="text-[11px] text-slate-700">
                                        <strong class="text-rose-700">shipment:delete</strong>
                                        <span class="block text-[10px] text-slate-500">Hapus / Batalkan Proyek Pengapalan</span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <!-- 4. DO & Traffic Pelabuhan -->
                        <div class="space-y-1.5 pt-2 border-t border-slate-200/80">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 flex items-center gap-1">
                                📑 Delivery Order (DO) & Armada Truk
                            </span>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                <label class="flex items-start space-x-2 cursor-pointer p-1.5 rounded-lg hover:bg-white transition-colors">
                                    <input type="checkbox" name="scope_roles[]" value="do:manage" 
                                           :checked="isScopeChecked('do:manage')" @change="toggleScope('do:manage')"
                                           class="rounded text-amber-600 focus:ring-amber-500 mt-0.5">
                                    <span class="text-[11px] text-slate-700">
                                        <strong>do:manage</strong>
                                        <span class="block text-[10px] text-slate-500">Kelola Release DO, Free-Time & SP2 Pelayaran</span>
                                    </span>
                                </label>
                                <label class="flex items-start space-x-2 cursor-pointer p-1.5 rounded-lg hover:bg-white transition-colors">
                                    <input type="checkbox" name="scope_roles[]" value="traffic:read" 
                                           :checked="isScopeChecked('traffic:read')" @change="toggleScope('traffic:read')"
                                           class="rounded text-amber-600 focus:ring-amber-500 mt-0.5">
                                    <span class="text-[11px] text-slate-700">
                                        <strong>traffic:read</strong>
                                        <span class="block text-[10px] text-slate-500">Monitoring Kontainer di Lapangan & Gate Out</span>
                                    </span>
                                </label>
                                <label class="flex items-start space-x-2 cursor-pointer p-1.5 rounded-lg hover:bg-white transition-colors sm:col-span-2">
                                    <input type="checkbox" name="scope_roles[]" value="traffic:update" 
                                           :checked="isScopeChecked('traffic:update')" @change="toggleScope('traffic:update')"
                                           class="rounded text-amber-600 focus:ring-amber-500 mt-0.5">
                                    <span class="text-[11px] text-slate-700">
                                        <strong>traffic:update</strong>
                                        <span class="block text-[10px] text-slate-500">Penugasan Armada Truk & Sopir Lapangan</span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <!-- 5. Keuangan, Billing & Pajak -->
                        <div class="space-y-1.5 pt-2 border-t border-slate-200/80">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-700 flex items-center gap-1">
                                💳 Keuangan, Billing & Perpajakan
                            </span>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                <label class="flex items-start space-x-2 cursor-pointer p-1.5 rounded-lg hover:bg-white transition-colors">
                                    <input type="checkbox" name="scope_roles[]" value="finance:read" 
                                           :checked="isScopeChecked('finance:read')" @change="toggleScope('finance:read')"
                                           class="rounded text-indigo-600 focus:ring-indigo-500 mt-0.5">
                                    <span class="text-[11px] text-slate-700">
                                        <strong>finance:read</strong>
                                        <span class="block text-[10px] text-slate-500">Lihat Rekap Keuangan & Mutasi Rekening Bank</span>
                                    </span>
                                </label>
                                <label class="flex items-start space-x-2 cursor-pointer p-1.5 rounded-lg hover:bg-white transition-colors">
                                    <input type="checkbox" name="scope_roles[]" value="finance:create" 
                                           :checked="isScopeChecked('finance:create')" @change="toggleScope('finance:create')"
                                           class="rounded text-indigo-600 focus:ring-indigo-500 mt-0.5">
                                    <span class="text-[11px] text-slate-700">
                                        <strong>finance:create</strong>
                                        <span class="block text-[10px] text-slate-500">Buat Invoice P19 & Tagihan Vendor AP</span>
                                    </span>
                                </label>
                                <label class="flex items-start space-x-2 cursor-pointer p-1.5 rounded-lg hover:bg-white transition-colors">
                                    <input type="checkbox" name="scope_roles[]" value="finance:approve" 
                                           :checked="isScopeChecked('finance:approve')" @change="toggleScope('finance:approve')"
                                           class="rounded text-purple-600 focus:ring-purple-500 mt-0.5">
                                    <span class="text-[11px] text-slate-700">
                                        <strong class="text-purple-700">finance:approve</strong>
                                        <span class="block text-[10px] text-slate-500">Persetujuan Tagihan Vendor & Biaya Operasional</span>
                                    </span>
                                </label>
                                <label class="flex items-start space-x-2 cursor-pointer p-1.5 rounded-lg hover:bg-white transition-colors">
                                    <input type="checkbox" name="scope_roles[]" value="finance:delete" 
                                           :checked="isScopeChecked('finance:delete')" @change="toggleScope('finance:delete')"
                                           class="rounded text-rose-600 focus:ring-rose-500 mt-0.5">
                                    <span class="text-[11px] text-slate-700">
                                        <strong class="text-rose-700">finance:delete</strong>
                                        <span class="block text-[10px] text-slate-500">Void / Hapus Invoice Klien & Tagihan Vendor</span>
                                    </span>
                                </label>
                                <label class="flex items-start space-x-2 cursor-pointer p-1.5 rounded-lg hover:bg-white transition-colors">
                                    <input type="checkbox" name="scope_roles[]" value="accounting:read" 
                                           :checked="isScopeChecked('accounting:read')" @change="toggleScope('accounting:read')"
                                           class="rounded text-indigo-600 focus:ring-indigo-500 mt-0.5">
                                    <span class="text-[11px] text-slate-700">
                                        <strong>accounting:read</strong>
                                        <span class="block text-[10px] text-slate-500">Laporan Laba Rugi (P&L) & Margin Pekerjaan</span>
                                    </span>
                                </label>
                                <label class="flex items-start space-x-2 cursor-pointer p-1.5 rounded-lg hover:bg-white transition-colors">
                                    <input type="checkbox" name="scope_roles[]" value="tax:read" 
                                           :checked="isScopeChecked('tax:read')" @change="toggleScope('tax:read')"
                                           class="rounded text-indigo-600 focus:ring-indigo-500 mt-0.5">
                                    <span class="text-[11px] text-slate-700">
                                        <strong>tax:read</strong>
                                        <span class="block text-[10px] text-slate-500">Rekap Faktur Pajak PPN 1.1% & PPh 23</span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <!-- 6. CRM & HRM -->
                        <div class="space-y-1.5 pt-2 border-t border-slate-200/80">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-teal-700 flex items-center gap-1">
                                👥 CRM, Notifikasi WhatsApp & Kepegawaian (HRM)
                            </span>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                                <label class="flex items-start space-x-2 cursor-pointer p-1.5 rounded-lg hover:bg-white transition-colors">
                                    <input type="checkbox" name="scope_roles[]" value="crm:read" 
                                           :checked="isScopeChecked('crm:read')" @change="toggleScope('crm:read')"
                                           class="rounded text-teal-600 focus:ring-teal-500 mt-0.5">
                                    <span class="text-[11px] text-slate-700">
                                        <strong>crm:read</strong>
                                        <span class="block text-[10px] text-slate-500">Database Klien & Kontak PIC</span>
                                    </span>
                                </label>
                                <label class="flex items-start space-x-2 cursor-pointer p-1.5 rounded-lg hover:bg-white transition-colors">
                                    <input type="checkbox" name="scope_roles[]" value="crm:communicate" 
                                           :checked="isScopeChecked('crm:communicate')" @change="toggleScope('crm:communicate')"
                                           class="rounded text-teal-600 focus:ring-teal-500 mt-0.5">
                                    <span class="text-[11px] text-slate-700">
                                        <strong>crm:communicate</strong>
                                        <span class="block text-[10px] text-slate-500">Kirim Notifikasi WhatsApp Status Kargo</span>
                                    </span>
                                </label>
                                <label class="flex items-start space-x-2 cursor-pointer p-1.5 rounded-lg hover:bg-white transition-colors">
                                    <input type="checkbox" name="scope_roles[]" value="hrm:read" 
                                           :checked="isScopeChecked('hrm:read')" @change="toggleScope('hrm:read')"
                                           class="rounded text-teal-600 focus:ring-teal-500 mt-0.5">
                                    <span class="text-[11px] text-slate-700">
                                        <strong>hrm:read</strong>
                                        <span class="block text-[10px] text-slate-500">Presensi GPS Lapangan & Rekap Payroll</span>
                                    </span>
                                </label>
                                <label class="flex items-start space-x-2 cursor-pointer p-1.5 rounded-lg hover:bg-white transition-colors">
                                    <input type="checkbox" name="scope_roles[]" value="hrm:approve" 
                                           :checked="isScopeChecked('hrm:approve')" @change="toggleScope('hrm:approve')"
                                           class="rounded text-purple-600 focus:ring-purple-500 mt-0.5">
                                    <span class="text-[11px] text-slate-700">
                                        <strong class="text-purple-700">hrm:approve</strong>
                                        <span class="block text-[10px] text-slate-500">Approval Cuti & Kasbon Karyawan</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                    <button type="button" 
                            @click="showTokenModal = false" 
                            class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold transition-all shadow-sm cursor-pointer">
                        Generate Token Akses &rarr;
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
function mcpIntegrationApp() {
    return {
        activeTab: @json(session('plain_token') ? 'tokens' : 'tester'),
        activeProvider: 'claude',
        showTokenModal: false,
        customToken: @json(session('plain_token') ?? ''),
        toolCategory: 'ALL',
        toolSearch: '',
        
        testQuery: 'HYDRAULIC BREAKER DYB800 STRAIGHT TYPE',
        testDirection: 'IMPORT_BC20',
        testLoading: false,
        testResult: null,
        
        batchText: @json($samplePackingList),
        batchLoading: false,
        batchResult: null,
        
        copyToast: false,
        copyMsg: '',

        selectedScopes: ['*'],
        scopePresets: {
            admin: ['*'],
            ops: ['customs:read', 'customs:create', 'shipment:read', 'shipment:create', 'shipment:update', 'do:manage', 'traffic:read', 'traffic:update', 'crm:read', 'crm:communicate'],
            finance: ['finance:read', 'finance:create', 'finance:approve', 'accounting:read', 'tax:read', 'approval:read'],
            readonly: ['customs:read', 'shipment:read', 'traffic:read', 'finance:read', 'accounting:read', 'tax:read', 'crm:read', 'hrm:read', 'approval:read']
        },

        setScopePreset(preset) {
            if (preset === 'admin') {
                this.selectedScopes = ['*'];
            } else if (this.scopePresets[preset]) {
                this.selectedScopes = [...this.scopePresets[preset]];
            }
        },

        toggleScope(scope) {
            if (scope === '*') {
                if (this.selectedScopes.includes('*')) {
                    this.selectedScopes = [];
                } else {
                    this.selectedScopes = ['*'];
                }
                return;
            }
            this.selectedScopes = this.selectedScopes.filter(s => s !== '*');
            if (this.selectedScopes.includes(scope)) {
                this.selectedScopes = this.selectedScopes.filter(s => s !== scope);
            } else {
                this.selectedScopes.push(scope);
            }
            if (this.selectedScopes.length === 0) {
                this.selectedScopes = ['customs:read'];
            }
        },

        isScopeChecked(scope) {
            return this.selectedScopes.includes('*') || this.selectedScopes.includes(scope);
        },

        getToken() {
            if (this.customToken && this.customToken.trim().length > 0) {
                return this.customToken.trim();
            }
            return 'genie_mcp_YOUR_TOKEN';
        },

        copyToClipboard(text, msg = 'Tersalin ke clipboard!') {
            navigator.clipboard.writeText(text).then(() => {
                this.copyMsg = msg;
                this.copyToast = true;
                setTimeout(() => { this.copyToast = false; }, 2200);
            });
        },

        async runCheckHs() {
            if (!this.testQuery.trim()) return;
            this.testLoading = true;
            this.testResult = null;
            try {
                const headers = { 'Content-Type': 'application/json' };
                if (this.customToken && this.customToken.trim().length > 0) {
                    headers['Authorization'] = 'Bearer ' + this.customToken.trim();
                }

                const res = await fetch('{{ route('api.mcp.execute') }}', {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify({
                        jsonrpc: '2.0',
                        id: Date.now(),
                        method: 'tools/call',
                        params: {
                            name: 'check_hs_code',
                            arguments: {
                                query: this.testQuery,
                                direction: this.testDirection
                            }
                        }
                    })
                });
                const data = await res.json();
                this.testResult = data.result?.structured_data || data;
            } catch (e) {
                this.testResult = { error: 'Gagal terhubung ke MCP Server: ' + e.message };
            } finally {
                this.testLoading = false;
            }
        },

        async runBatchPackingList() {
            if (!this.batchText.trim()) return;
            this.batchLoading = true;
            this.batchResult = null;
            try {
                const headers = { 'Content-Type': 'application/json' };
                if (this.customToken && this.customToken.trim().length > 0) {
                    headers['Authorization'] = 'Bearer ' + this.customToken.trim();
                }

                const res = await fetch('{{ route('api.mcp.execute') }}', {
                    method: 'POST',
                    headers: headers,
                    body: JSON.stringify({
                        jsonrpc: '2.0',
                        id: Date.now(),
                        method: 'tools/call',
                        params: {
                            name: 'parse_packing_list',
                            arguments: {
                                raw_text: this.batchText,
                                direction: 'IMPORT_BC20'
                            }
                        }
                    })
                });
                const data = await res.json();
                this.batchResult = data.result?.structured_data || data;
            } catch (e) {
                this.batchResult = { error: 'Gagal memproses parsing packing list: ' + e.message };
            } finally {
                this.batchLoading = false;
            }
        }
    };
}
</script>
@endpush
@endsection
