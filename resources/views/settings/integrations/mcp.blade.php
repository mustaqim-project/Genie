@extends('layouts.app')

@section('title', 'MCP Server & Integrasi AI Kepabeanan')

@section('content')
<div x-data="{
    activeTab: 'tester',
    activeProvider: 'claude',
    testQuery: 'HYDRAULIC BREAKER DYB800 STRAIGHT TYPE',
    testDirection: 'IMPORT_BC20',
    testLoading: false,
    testResult: null,
    
    batchText: @json($samplePackingList),
    batchLoading: false,
    batchResult: null,
    
    copyToast: false,
    copyMsg: '',

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
            const res = await fetch('{{ route('api.mcp.execute') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
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
            const res = await fetch('{{ route('api.mcp.execute') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
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
}" class="space-y-6">

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

    <!-- Header & Status Server Bar -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-400 mb-1">
                <span>Pengaturan Sistem & AI</span>
                <span>&rarr;</span>
                <span class="text-slate-700 font-semibold">Integrasi Model Context Protocol (MCP)</span>
            </div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight flex items-center space-x-2">
                <span>MCP Server & Customs AI Agent</span>
                <span class="px-2 py-0.5 rounded-md bg-sky-100 text-sky-800 text-[10px] font-mono font-bold uppercase tracking-wider">v2.5.0</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1 max-w-2xl leading-relaxed">
                Server Model Context Protocol (MCP) resmi untuk menghubungkan AI Assistant eksternal (Claude Desktop, Cursor, Antigravity, Windsurf) langsung ke database pos tarif BTKI 2022, pencocokan Packing List otomatis, dan gateway CEISA 4.0 Bea Cukai.
            </p>
        </div>

        <!-- Live Server Status Pill -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2.5 bg-slate-50 p-3 rounded-2xl border border-slate-200/70">
            <div class="flex items-center space-x-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="font-bold text-xs text-slate-800">MCP Protocol Online</span>
            </div>
            <span class="hidden sm:inline text-slate-300">|</span>
            <span class="text-[11px] font-mono text-slate-500">Spec: 2024-11-05</span>
            <span class="hidden sm:inline text-slate-300">|</span>
            <span class="text-[11px] font-mono text-indigo-600 font-bold">4 Tools Aktif</span>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-slate-200 gap-2">
        <button type="button" 
                @click="activeTab = 'tester'" 
                :class="activeTab === 'tester' ? 'border-sky-600 text-sky-700 font-bold bg-white' : 'border-transparent text-slate-500 hover:text-slate-800'"
                class="px-4 py-2.5 text-xs rounded-t-xl border-b-2 transition-all flex items-center space-x-2">
            <span>⚡</span>
            <span>Uji Cepat Deteksi HS & Packing List</span>
            <span class="px-1.5 py-0.2 rounded-full text-[9px] bg-emerald-100 text-emerald-800 font-bold">Live</span>
        </button>
        <button type="button" 
                @click="activeTab = 'guide'" 
                :class="activeTab === 'guide' ? 'border-sky-600 text-sky-700 font-bold bg-white' : 'border-transparent text-slate-500 hover:text-slate-800'"
                class="px-4 py-2.5 text-xs rounded-t-xl border-b-2 transition-all flex items-center space-x-2">
            <span>🔌</span>
            <span>Panduan Koneksi AI Client (Claude, Cursor, Antigravity)</span>
        </button>
        <button type="button" 
                @click="activeTab = 'tools'" 
                :class="activeTab === 'tools' ? 'border-sky-600 text-sky-700 font-bold bg-white' : 'border-transparent text-slate-500 hover:text-slate-800'"
                class="px-4 py-2.5 text-xs rounded-t-xl border-b-2 transition-all flex items-center space-x-2">
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
                                class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[10px] font-medium transition-colors">
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
                        class="px-5 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 disabled:bg-slate-300 text-white text-xs font-bold transition-all shadow-sm flex items-center justify-center space-x-1.5 shrink-0">
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
                                      x-text="testResult.data.has_lartas ? '⚠️ Terkena Lartas' : '✅ Bebas Lartas'"></span>
                            </div>
                        </div>
                    </div>
                </template>
                <template x-if="testResult && testResult.data && testResult.data.required_documents && testResult.data.required_documents.length > 0">
                    <div class="mt-2 p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs">
                        <strong class="text-amber-900 block mb-1">Dokumen Perizinan Wajib Bea Cukai:</strong>
                        <ul class="list-disc list-inside space-y-0.5 text-amber-800 text-[11px]">
                            <template x-for="doc in testResult.data.required_documents" :key="doc.kode_dokumen">
                                <li>
                                    <strong x-text="'[' + doc.kode_dokumen + '] ' + doc.nama_dokumen"></strong> 
                                    - <span x-text="doc.instansi"></span>
                                </li>
                            </template>
                        </ul>
                    </div>
                </template>
                <template x-if="testResult && testResult.error">
                    <div class="p-3 bg-rose-50 rounded-xl border border-rose-200 text-rose-800 text-xs font-semibold" x-text="testResult.error"></div>
                </template>
            </div>
        </div>

        <!-- Sub-Card 2: Batch Parsing Packing List Otomatis -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 flex items-center space-x-2">
                        <span class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs">2</span>
                        <span>Uji Parsing Packing List Otomatis (parse_packing_list)</span>
                    </h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">Tempel teks Packing List multi-item untuk di-parsing dan dicocokkan pos tarifnya secara massal oleh MCP Agent.</p>
                </div>
                <span class="text-xs font-mono text-slate-400">Tool: parse_packing_list</span>
            </div>

            <div class="space-y-3">
                <textarea x-model="batchText" 
                          rows="6" 
                          class="w-full px-3 py-2.5 rounded-xl border border-slate-200 font-mono text-[11px] focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                          placeholder="Tempel baris item packing list di sini..."></textarea>

                <div class="flex items-center justify-between">
                    <span class="text-[11px] text-slate-400">Format fleksibel: Nama Barang, Kemasan, Berat, dan Nilai Valuta.</span>
                    <button type="button" 
                            @click="runBatchPackingList()" 
                            :disabled="batchLoading"
                            class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:bg-slate-300 text-white text-xs font-bold transition-all shadow-sm flex items-center space-x-2">
                        <span x-show="!batchLoading">Jalankan Analisis & Auto-Match Pos Tarif &rarr;</span>
                        <span x-show="batchLoading">Menganalisis Dokumen via MCP...</span>
                    </button>
                </div>
            </div>

            <!-- Tabel Hasil Batch Parsing -->
            <template x-if="batchResult && batchResult.items">
                <div class="mt-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-800 text-xs">
                            Hasil Deteksi MCP Agent (<span x-text="batchResult.items.length"></span> Item Berhasil Terklasifikasi)
                        </span>
                        <span class="text-[11px] font-mono font-bold text-indigo-700" 
                              x-text="'Valuta: ' + (batchResult.commercial_values?.currency || 'CNY') + ' | Total FOB: ' + Number(batchResult.commercial_values?.total_fob || 0).toLocaleString()"></span>
                    </div>

                    <div class="overflow-x-auto border border-slate-200 rounded-xl">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                                <tr>
                                    <th class="py-2 px-3">No</th>
                                    <th class="py-2 px-3">Pos Tarif HS</th>
                                    <th class="py-2 px-3">Uraian Komoditas</th>
                                    <th class="py-2 px-3 text-right">Kemasan</th>
                                    <th class="py-2 px-3 text-right">Berat (Kg)</th>
                                    <th class="py-2 px-3 text-right">Nilai FOB</th>
                                    <th class="py-2 px-3 text-center">Tarif BM</th>
                                    <th class="py-2 px-3 text-center">Lartas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-mono text-[11px]">
                                <template x-for="(item, idx) in batchResult.items" :key="idx">
                                    <tr class="hover:bg-slate-50">
                                        <td class="py-2 px-3 text-slate-400" x-text="idx + 1"></td>
                                        <td class="py-2 px-3">
                                            <span class="font-bold text-sky-700" x-text="item.kode_hs"></span>
                                        </td>
                                        <td class="py-2 px-3 font-sans">
                                            <div class="font-bold text-slate-800" x-text="item.uraian_barang"></div>
                                        </td>
                                        <td class="py-2 px-3 text-right" x-text="item.jumlah_kemasan + ' ' + item.jenis_kemasan"></td>
                                        <td class="py-2 px-3 text-right" x-text="item.berat_bruto + ' B'"></td>
                                        <td class="py-2 px-3 text-right font-bold text-slate-900" x-text="Number(item.nilai_fob).toLocaleString()"></td>
                                        <td class="py-2 px-3 text-center">
                                            <span class="px-1.5 py-0.5 rounded bg-slate-100 text-[10px]" x-text="item.tarif_bm_persen + '%'"></span>
                                        </td>
                                        <td class="py-2 px-3 text-center">
                                            <span :class="item.has_lartas ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'"
                                                  class="px-1.5 py-0.5 rounded text-[9px] font-bold"
                                                  x-text="item.has_lartas ? 'Lartas' : 'Bebas'"></span>
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

    <!-- TAB 2: PANDUAN KONEKSI AI CLIENT (CLAUDE / CURSOR / ANTIGRAVITY) -->
    <div x-show="activeTab === 'guide'" class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-6">
        <div>
            <h2 class="text-sm font-bold text-slate-900">Hubungkan AI Assistant Anda ke GENIE MCP Server</h2>
            <p class="text-xs text-slate-500 mt-0.5">Pilih aplikasi AI yang Anda gunakan di bawah ini dan salin konfigurasi langsung ke aplikasi tersebut.</p>
        </div>

        <!-- Provider Buttons -->
        <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-3">
            @foreach([
                'claude' => 'Claude Desktop (Anthropic)',
                'cursor' => 'Cursor IDE',
                'antigravity' => 'Google Antigravity AI',
                'python' => 'Python / Gemini SDK',
                'curl' => 'cURL / REST API'
            ] as $key => $label)
                <button type="button" 
                        @click="activeProvider = '{{ $key }}'" 
                        :class="activeProvider === '{{ $key }}' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                        class="px-3.5 py-1.5 rounded-xl text-xs transition-colors">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <!-- Claude Desktop Guide -->
        <div x-show="activeProvider === 'claude'" class="space-y-4">
            <div class="text-xs text-slate-600">
                Tambahkan konfigurasi berikut ke file <code>claude_desktop_config.json</code> di komputer Anda:
                <div class="mt-1 text-[11px] text-slate-400 font-mono">Windows: %APPDATA%\Claude\claude_desktop_config.json | macOS: ~/Library/Application Support/Claude/claude_desktop_config.json</div>
            </div>
            @php
                $claudeConfig = json_encode([
                    "mcpServers" => [
                        "genie-customs" => [
                            "url" => $sseEndpoint
                        ]
                    ]
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            @endphp
            <div class="relative">
                <pre class="bg-slate-950 text-sky-300 p-4 rounded-xl text-xs font-mono overflow-x-auto">{{ $claudeConfig }}</pre>
                <button type="button" 
                        @click="copyToClipboard(@json($claudeConfig), 'Konfigurasi Claude Desktop disalin!')" 
                        class="absolute top-3 right-3 px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-white text-[11px] font-semibold transition-colors">
                    Salin JSON
                </button>
            </div>
        </div>

        <!-- Cursor IDE Guide -->
        <div x-show="activeProvider === 'cursor'" class="space-y-4">
            <div class="text-xs text-slate-600">
                Buka <strong>Cursor Settings &rarr; Features &rarr; MCP Servers</strong>, lalu tambahkan server baru bertipe <strong>SSE</strong>:
            </div>
            @php
                $cursorConfig = json_encode([
                    "mcpServers" => [
                        "genie-customs" => [
                            "url" => $sseEndpoint
                        ]
                    ]
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            @endphp
            <div class="relative">
                <pre class="bg-slate-950 text-emerald-300 p-4 rounded-xl text-xs font-mono overflow-x-auto">{{ $cursorConfig }}</pre>
                <button type="button" 
                        @click="copyToClipboard(@json($cursorConfig), 'Konfigurasi Cursor disalin!')" 
                        class="absolute top-3 right-3 px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-white text-[11px] font-semibold transition-colors">
                    Salin JSON
                </button>
            </div>
        </div>

        <!-- Antigravity AI Guide -->
        <div x-show="activeProvider === 'antigravity'" class="space-y-4">
            <div class="text-xs text-slate-600">
                Server MCP GENIE mendukung Model Context Protocol standard 2024-11-05. Anda dapat mendaftarkan endpoint SSE langsung pada file konfigurasi MCP:
            </div>
            <div class="p-3 bg-slate-100 rounded-xl font-mono text-xs text-slate-800 flex items-center justify-between">
                <span>{{ $sseEndpoint }}</span>
                <button type="button" 
                        @click="copyToClipboard('{{ $sseEndpoint }}')" 
                        class="text-sky-700 hover:underline font-bold">
                    Salin URL
                </button>
            </div>
        </div>

        <!-- Python / Gemini Guide -->
        <div x-show="activeProvider === 'python'" class="space-y-4">
            <div class="text-xs text-slate-600">
                Contoh memanggil tool MCP via Python Requests:
            </div>
            @php
                $pyCode = "import requests

# 1. Panggil MCP Tool: Cek Pos Tarif HS Code & Dokumen Lartas
response = requests.post(
    '{$executeEndpoint}',
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
print('Hasil Deteksi Pos Tarif:', result['result']['structured_data'])";
            @endphp
            <div class="relative">
                <pre class="bg-slate-950 text-indigo-300 p-4 rounded-xl text-xs font-mono overflow-x-auto">{{ $pyCode }}</pre>
                <button type="button" 
                        @click="copyToClipboard(@json($pyCode), 'Kode Python disalin!')" 
                        class="absolute top-3 right-3 px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-white text-[11px] font-semibold transition-colors">
                    Salin Kode
                </button>
            </div>
        </div>

        <!-- cURL Guide -->
        <div x-show="activeProvider === 'curl'" class="space-y-4">
            <div class="text-xs text-slate-600">
                Contoh request cURL CLI:
            </div>
            @php
                $curlCode = "curl -X POST '{$executeEndpoint}' \\
  -H 'Content-Type: application/json' \\
  -d '{
    \"jsonrpc\": \"2.0\",
    \"id\": 1,
    \"method\": \"tools/call\",
    \"params\": {
      \"name\": \"check_hs_code\",
      \"arguments\": {
        \"query\": \"SBS WATERPROOFING MEMBRANE\"
      }
    }
  }'";
            @endphp
            <div class="relative">
                <pre class="bg-slate-950 text-amber-300 p-4 rounded-xl text-xs font-mono overflow-x-auto">{{ $curlCode }}</pre>
                <button type="button" 
                        @click="copyToClipboard(@json($curlCode), 'cURL command disalin!')" 
                        class="absolute top-3 right-3 px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-white text-[11px] font-semibold transition-colors">
                    Salin cURL
                </button>
            </div>
        </div>
    </div>

    <!-- TAB 3: KATALOG TOOL MCP -->
    <div x-show="activeTab === 'tools'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($tools as $tool)
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-mono font-bold text-sky-800 text-sm bg-sky-50 px-2 py-0.5 rounded border border-sky-200">
                            {{ $tool['name'] }}
                        </span>
                        <span class="text-[10px] text-slate-400 font-mono">Tool Schema 2024-11-05</span>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed mb-3">
                        {{ $tool['description'] }}
                    </p>
                </div>
                <div class="pt-3 border-t border-slate-100">
                    <span class="text-[10px] font-bold text-slate-400 block mb-1">PROPERTIES / ARGUMENTS:</span>
                    <div class="flex flex-wrap gap-1 font-mono text-[10px]">
                        @if(!empty($tool['inputSchema']['properties']))
                            @foreach($tool['inputSchema']['properties'] as $propName => $prop)
                                <span class="px-2 py-0.5 bg-slate-100 rounded text-slate-700">
                                    {{ $propName }} ({{ $prop['type'] ?? 'any' }})
                                </span>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

</div>
@endsection
