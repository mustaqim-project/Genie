@extends('layouts.app')

@section('title', 'AI Agent & MCP Customs Server')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 pt-6 pb-16">

    <!-- Page Header & MCP Status -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="text-xs text-slate-500 font-medium mb-1">
                <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a> / <span class="text-slate-800">AI Agent & MCP</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">AI Agent & Model Context Protocol (MCP) Server</h1>
            <p class="text-xs text-slate-500 mt-0.5">Pemindaian packing list otomatis (OCR/AI), klasifikasi HS Code BTKI 2022, audit izin lartas, dan rekomendasi deklarasi PIB / PEB.</p>
        </div>
        <div class="flex items-center space-x-2">
            <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-semibold">
                <span class="w-2 h-2 rounded-full bg-emerald-600 mr-2 animate-pulse"></span>
                MCP Agent Server: ONLINE (REST & JSON-RPC)
            </span>
            <a href="{{ route('api.mcp.tools') }}" target="_blank" class="inline-flex items-center space-x-1 px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-all shadow-sm">
                <span>⚡ Tools Schema JSON</span>
            </a>
        </div>
    </div>

    <!-- Top Grid: Daily Briefing & Human-in-the-Loop Approval Queue -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- AI Briefing Proaktif -->
        <div class="bg-gradient-to-br from-slate-900 to-indigo-950 rounded-3xl p-6 text-white shadow-xl lg:col-span-1 border border-indigo-900/60 flex flex-col justify-between">
            <div>
                <div class="flex items-center space-x-2 text-indigo-300 text-xs font-bold uppercase tracking-wider mb-3">
                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span>Daily Proactive Briefing</span>
                </div>
                <h2 class="text-lg font-bold mb-2">Laporan Analisis Kepabeanan & Risiko</h2>
                <div class="space-y-3 text-xs text-slate-300 mt-4">
                    <div class="p-3 rounded-2xl bg-white/5 border border-white/10">
                        <strong class="text-white block font-semibold mb-0.5">&bull; Status Gateway CEISA 4.0:</strong>
                        Host-to-host gateway terhubung. Validasi skema open API v2.0 aktif untuk PIB (BC 2.0) dan PEB (BC 3.0).
                    </div>
                    <div class="p-3 rounded-2xl bg-white/5 border border-white/10">
                        <strong class="text-white block font-semibold mb-0.5">&bull; Audit Perizinan Lartas:</strong>
                        AI memantau dokumen lampiran wajib (LS Kemendag, SNI, SDPPI, V-Legal, Karantina) pada setiap rincian barang.
                    </div>
                    <div class="p-3 rounded-2xl bg-white/5 border border-white/10">
                        <strong class="text-white block font-semibold mb-0.5">&bull; Prinsip Human-in-the-Loop:</strong>
                        Seluruh usulan pos tarif dan dokumen pabean wajib disetujui staf penanganan sebelum disubmit ke Bea Cukai.
                    </div>
                </div>
            </div>
            <div class="mt-6 pt-4 border-t border-indigo-900/50 text-[11px] text-indigo-300 flex items-center justify-between">
                <span>Versi Engine: v2.5-BTKI2022</span>
                <span class="font-mono">JSON-RPC 2.0</span>
            </div>
        </div>

        <!-- Antrian Usulan Aksi & Approval Manusia (Human-in-the-Loop) -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm lg:col-span-2 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <div>
                        <h2 class="font-bold text-xs text-slate-800 uppercase tracking-wider">Antrian Usulan Tindakan AI (Pending Approvals)</h2>
                        <p class="text-[11px] text-slate-500 mt-0.5">Sesuai PKS Operasional: Keputusan final pabean & billing wajib disetujui pengguna.</p>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                        {{ $pendingApprovals }} Usulan Menunggu
                    </span>
                </div>

                <div class="space-y-3">
                    <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/70 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center space-x-2 mb-1">
                                <span class="font-bold text-slate-900">Validasi 3-Way Matching & Usulan Pengiriman PIB BC 2.0</span>
                                <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 text-[10px] font-bold">TOOL: CEISA_SUBMIT</span>
                            </div>
                            <p class="text-slate-600 text-[11px]">AI telah memeriksa konsistensi Invoice, Packing List, B/L, serta dokumen lartas terkait barang.</p>
                            <div class="text-[10px] text-slate-400 mt-1">Status Proyek: <strong class="font-mono text-slate-700">DOC_READY / HS_DETECTED</strong></div>
                        </div>
                        <div class="flex items-center space-x-2 shrink-0">
                            <a href="{{ route('core.document.index') }}" class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition-colors">
                                Review Proyek
                            </a>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/70 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center space-x-2 mb-1">
                                <span class="font-bold text-slate-900">Deteksi Otomatis Dokumen Tambahan Lartas Baru</span>
                                <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 text-[10px] font-bold">TOOL: LARTAS_AUDIT</span>
                            </div>
                            <p class="text-slate-600 text-[11px]">AI menemukan komoditas elektronik & mesin jahit yang memerlukan Laporan Surveyor (LS) dan SNI Wajib.</p>
                        </div>
                        <div class="flex items-center space-x-2 shrink-0">
                            <span class="px-2.5 py-1 rounded-xl bg-emerald-100 text-emerald-800 font-bold text-[10px]">
                                Auto-Synced
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-400 flex items-center justify-between">
                <span>Human-in-the-Loop Protection: AKTIF</span>
                <span>Audit Trail Logging: AKTIF</span>
            </div>
        </div>
    </div>

    <!-- MAIN SECTION: AI Agent & MCP Server Interactive Workbench -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 mb-8 text-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4 mb-6">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 flex items-center space-x-2">
                    <span>🔬</span>
                    <span>MCP Server & AI Document Inspector Workbench</span>
                </h2>
                <p class="text-slate-500 text-xs mt-0.5">Uji coba interaktif pengunggahan berkas packing list, ekstraksi OCR/AI, deteksi HS code, pengecekan izin lartas, dan rekomendasi PIB/PEB.</p>
            </div>

            <!-- Tab Buttons -->
            <div class="flex items-center space-x-1.5 p-1 bg-slate-100 rounded-2xl">
                <button type="button" onclick="switchWorkbenchTab('scanner')" id="tab-btn-scanner" class="px-3.5 py-1.5 rounded-xl font-bold text-xs bg-white text-slate-900 shadow-sm transition-all">
                    📄 Packing List Scanner (OCR/AI)
                </button>
                <button type="button" onclick="switchWorkbenchTab('lookup')" id="tab-btn-lookup" class="px-3.5 py-1.5 rounded-xl font-bold text-xs text-slate-600 hover:text-slate-900 transition-all">
                    🔍 HS Code & Lartas Lookup
                </button>
                <button type="button" onclick="switchWorkbenchTab('mcp-docs')" id="tab-btn-mcp-docs" class="px-3.5 py-1.5 rounded-xl font-bold text-xs text-slate-600 hover:text-slate-900 transition-all">
                    ⚙️ MCP Agent Integration Guide
                </button>
            </div>
        </div>

        <!-- TAB 1: Packing List Scanner -->
        <div id="tab-content-scanner" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- Left Column: Input Form -->
                <div class="lg:col-span-5 space-y-4">
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/70 space-y-3.5">
                        <div class="font-bold text-slate-800 text-xs flex items-center justify-between">
                            <span>1. Pilih Target Shipment (Opsional)</span>
                            <span class="text-[10px] text-slate-400">Untuk sinkronisasi langsung</span>
                        </div>
                        <select id="workbench_shipment_id" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white font-semibold text-xs">
                            <option value="">-- Mode Pengujian Mandiri (Tanpa Simpan) --</option>
                            @foreach($activeShipments as $s)
                                <option value="{{ $s->id }}">{{ $s->shipment_number ?? $s->project_no }} - {{ $s->client->name ?? 'Klien' }} ({{ $s->type }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/70 space-y-3.5">
                        <div class="font-bold text-slate-800 text-xs">
                            <span>2. Unggah Berkas Packing List (PDF / Excel / Gambar / CSV)</span>
                        </div>
                        <input type="file" id="workbench_file_input" accept=".pdf,.xlsx,.xls,.csv,.txt,.jpg,.jpeg,.png"
                               class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white hover:file:bg-slate-800">
                        <p class="text-[10px] text-slate-400">Format yang didukung: PDF, Excel (.xlsx, .xls), CSV, Gambar (JPG, PNG), TXT.</p>
                    </div>

                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/70 space-y-3.5">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-800 text-xs">3. Atau Tempel Teks Mentah / Input AI Agent:</span>
                            <button type="button" onclick="loadSamplePackingList()" class="inline-flex items-center gap-1 text-[11px] text-indigo-600 font-semibold hover:text-indigo-800 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                <span>Muat Contoh Nyata</span>
                            </button>
                        </div>
                        <textarea id="workbench_raw_text" rows="5" placeholder="Tempelkan baris packing list di sini..."
                                  class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono text-[11px]"></textarea>
                    </div>

                    <div class="flex items-center space-x-2">
                        <button type="button" onclick="executeWorkbenchScan()" id="btn-run-scan"
                                class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition-all active:scale-95 flex items-center justify-center space-x-2 cursor-pointer">
                            <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span>Jalankan Pengecekan AI & MCP</span>
                        </button>
                    </div>
                </div>

                <!-- Right Column: Visual Result Inspector -->
                <div class="lg:col-span-7 flex flex-col justify-between">
                    <div id="workbench_result_container" class="space-y-4">
                        <!-- Placeholder State -->
                        <div id="workbench_empty_state" class="p-12 text-center border-2 border-dashed border-slate-200 rounded-3xl text-slate-400 space-y-2">
                            <span class="text-3xl block">📋</span>
                            <div class="font-bold text-slate-600 text-xs">Hasil Analisis AI & MCP Akan Tampil di Sini</div>
                            <p class="text-[11px] text-slate-400 max-w-sm mx-auto">
                                Unggah berkas dokumen packing list atau tempelkan teks mentah di kolom sebelah kiri, lalu klik tombol "Jalankan Pengecekan AI & MCP".
                            </p>
                        </div>

                        <!-- Result Card (Initially Hidden) -->
                        <div id="workbench_result_card" class="hidden space-y-4">

                            <!-- Header Result & Recommendation -->
                            <div class="p-4 rounded-2xl border flex flex-col sm:flex-row sm:items-center justify-between gap-3" id="result_banner_box">
                                <div class="flex items-center space-x-3">
                                    <span class="text-2xl" id="result_icon">📦</span>
                                    <div>
                                        <div class="flex items-center space-x-2">
                                            <span class="font-extrabold text-slate-900 text-sm" id="result_decl_title">
                                                Rekomendasi Dokumen: PIB (BC 2.0 IMPOR)
                                            </span>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono" id="result_badge_direction">
                                                IMPORT_BC20
                                            </span>
                                        </div>
                                        <p class="text-slate-600 text-[11px] mt-0.5" id="result_desc">
                                            Sistem mendeteksi arah pengapalan & pos tarif komoditas secara otomatis.
                                        </p>
                                    </div>
                                </div>
                                <div id="result_apply_action"></div>
                            </div>

                            <!-- Lartas Alert if any -->
                            <div id="result_lartas_alert" class="p-3.5 rounded-2xl bg-amber-50 border border-amber-300 text-amber-900 text-xs hidden flex items-start space-x-2.5">
                                <span class="text-lg">⚠️</span>
                                <div>
                                    <strong class="font-bold">Perhatian Kepatuhan Dokumen Tambahan (Lartas):</strong>
                                    <div id="result_lartas_text" class="text-[11px] mt-0.5">Ditemukan barang yang wajib menyertakan perizinan resmi Bea Cukai.</div>
                                </div>
                            </div>

                            <!-- Table of Detected Goods -->
                            <div class="border border-slate-200 rounded-2xl overflow-hidden">
                                <div class="bg-slate-50 px-4 py-2.5 font-bold text-slate-800 text-xs flex justify-between items-center border-b border-slate-200">
                                    <span>Rincian Barang & Klasifikasi HS Code BTKI 2022</span>
                                    <span class="font-mono text-slate-500 text-[10px]" id="result_items_count">0 Items</span>
                                </div>
                                <div class="overflow-x-auto max-h-72 overflow-y-auto">
                                    <table class="w-full text-left text-xs">
                                        <thead class="bg-slate-100 text-slate-600 font-semibold border-b border-slate-200 text-[11px]">
                                            <tr>
                                                <th class="py-2 px-3">No</th>
                                                <th class="py-2 px-3">Pos Tarif (HS)</th>
                                                <th class="py-2 px-3">Uraian Komoditas</th>
                                                <th class="py-2 px-3 text-right">Kemasan</th>
                                                <th class="py-2 px-3 text-right">Berat Bruto</th>
                                                <th class="py-2 px-3 text-right">Nilai FOB</th>
                                                <th class="py-2 px-3 text-center">Lartas</th>
                                            </tr>
                                        </thead>
                                        <tbody id="result_items_tbody" class="divide-y divide-slate-100">
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Raw JSON Toggle -->
                            <div class="pt-2">
                                <details class="group bg-slate-900 text-white rounded-2xl overflow-hidden">
                                    <summary class="px-4 py-2.5 text-xs font-mono font-bold cursor-pointer hover:bg-slate-800 flex items-center justify-between">
                                        <span class="text-indigo-300">Respon Mentah MCP Protocol (JSON-RPC)</span>
                                        <span class="text-[10px] text-slate-400 group-open:rotate-180 transition-transform">▼</span>
                                    </summary>
                                    <pre id="result_raw_json" class="p-4 text-[10px] font-mono text-emerald-300 overflow-x-auto bg-black/40 max-h-48"></pre>
                                </details>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- TAB 2: HS Code & Lartas Lookup -->
        <div id="tab-content-lookup" class="hidden space-y-6">
            <div class="max-w-2xl bg-slate-50 p-5 rounded-2xl border border-slate-200 space-y-4">
                <div>
                    <label class="block font-bold text-slate-800 mb-1">Cek Pos Tarif HS Code 8-Digit atau Kata Kunci Komoditas:</label>
                    <div class="flex space-x-2">
                        <input type="text" id="lookup_query_input" placeholder="Misal: 8504.40.90, Laptop, CPO, Minyak Sawit, Mesin Jahit, Kayu, Inverter"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono text-xs">
                        <button type="button" onclick="executeHsLookup()" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shrink-0">
                            Cari Pos Tarif
                        </button>
                    </div>
                </div>

                <div id="lookup_result_box" class="hidden bg-white p-4 rounded-xl border border-slate-200 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <span class="font-mono font-bold text-sky-800 text-sm" id="lookup_res_code">8504.40.90</span>
                        <span id="lookup_res_lartas_badge" class="px-2 py-0.5 rounded text-[10px] font-bold">LARTAS</span>
                    </div>
                    <div>
                        <strong class="block text-slate-800 font-bold text-xs" id="lookup_res_desc">Deskripsi</strong>
                        <div class="text-[11px] text-slate-500 mt-0.5" id="lookup_res_legal">Dasar Hukum</div>
                    </div>
                    <div class="grid grid-cols-4 gap-2 bg-slate-50 p-2.5 rounded-lg text-center font-mono text-[11px]">
                        <div>BM: <strong id="lookup_res_bm">5%</strong></div>
                        <div>PPN: <strong id="lookup_res_ppn">11%</strong></div>
                        <div>PPh 22: <strong id="lookup_res_pph">2.5%</strong></div>
                        <div>BK: <strong id="lookup_res_bk">0%</strong></div>
                    </div>
                    <div id="lookup_res_docs_container" class="space-y-1.5 pt-1">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Dokumen Izin / Pelengkap Wajib:</span>
                        <div id="lookup_res_docs_list" class="space-y-1 text-[11px]"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 3: MCP Agent Integration Guide -->
        <div id="tab-content-mcp-docs" class="hidden space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
                <div class="p-5 rounded-2xl bg-slate-900 text-white space-y-3">
                    <div class="font-bold text-sm text-indigo-300 flex items-center space-x-2">
                        <span>🤖</span>
                        <span>Konfigurasi Claude Desktop / Antigravity MCP</span>
                    </div>
                    <p class="text-[11px] text-slate-300">Tambahkan konfigurasi berikut ke berkas <code class="text-amber-300 font-mono">claude_desktop_config.json</code> atau direktori MCP agent Anda:</p>
                    <pre class="p-3 rounded-xl bg-black/50 text-[10px] font-mono text-emerald-400 overflow-x-auto">{
  "mcpServers": {
    "genie-customs-agent": {
      "command": "curl",
      "args": [
        "-X", "POST",
        "http://localhost:8000/api/mcp/execute",
        "-H", "Content-Type: application/json"
      ]
    }
  }
}</pre>
                </div>

                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                    <div class="font-bold text-sm text-slate-800 flex items-center space-x-2">
                        <span>🌐</span>
                        <span>Daftar Endpoint REST API MCP</span>
                    </div>
                    <div class="space-y-2 font-mono text-[11px]">
                        <div class="p-2 bg-white rounded-lg border border-slate-200">
                            <span class="font-bold text-sky-700">GET</span> /api/mcp/tools
                            <div class="text-[10px] text-slate-400 font-sans mt-0.5">Daftar tools & JSON Schema spesifikasi MCP.</div>
                        </div>
                        <div class="p-2 bg-white rounded-lg border border-slate-200">
                            <span class="font-bold text-emerald-700">POST</span> /api/mcp/parse-packing-list
                            <div class="text-[10px] text-slate-400 font-sans mt-0.5">Unggah berkas / teks packing list untuk ekstraksi HS & Lartas.</div>
                        </div>
                        <div class="p-2 bg-white rounded-lg border border-slate-200">
                            <span class="font-bold text-emerald-700">POST</span> /api/mcp/check-hs
                            <div class="text-[10px] text-slate-400 font-sans mt-0.5">Pengecekan spesifik tarif & izin Lartas pos HS.</div>
                        </div>
                        <div class="p-2 bg-white rounded-lg border border-slate-200">
                            <span class="font-bold text-emerald-700">POST</span> /api/mcp/shipments/{id}/detect-hs
                            <div class="text-[10px] text-slate-400 font-sans mt-0.5">Deteksi & sinkronkan item ke proyek pengapalan aktif.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

<script>
    function switchWorkbenchTab(tab) {
        const tabs = ['scanner', 'lookup', 'mcp-docs'];
        tabs.forEach(t => {
            const btn = document.getElementById(`tab-btn-${t}`);
            const content = document.getElementById(`tab-content-${t}`);
            if (t === tab) {
                btn.className = 'px-3.5 py-1.5 rounded-xl font-bold text-xs bg-white text-slate-900 shadow-sm transition-all';
                content.classList.remove('hidden');
            } else {
                btn.className = 'px-3.5 py-1.5 rounded-xl font-bold text-xs text-slate-600 hover:text-slate-900 transition-all';
                content.classList.add('hidden');
            }
        });
    }

    function loadSamplePackingList() {
        document.getElementById('workbench_raw_text').value = `PACKING LIST & COMMERCIAL INVOICE
CONSIGNEE: PT AGUNG TRANS SOLUSINDO (JAKARTA)
PORT OF LOADING: CNSHA (SHANGHAI)
PORT OF DISCHARGE: IDTPP (TANJUNG PRIOK)
TOTAL PACKAGES: 60 CTN
GROSS WEIGHT: 3,300 KGS
NET WEIGHT: 3,000 KGS
TOTAL VALUE: USD 17,500.00

ITEMS:
1. INDUSTRIAL SEWING MACHINE HEAVY DUTY (MODEL DDL-8700) | 50 CTN | 2,500 KGS | USD 12,500.00 | HS: 8452.29.00
2. SPARE PARTS AND NEEDLES FOR SEWING MACHINES | 10 BOX | 800 KGS | USD 5,000.00 | HS: 8452.90.00`;
    }

    async function executeWorkbenchScan() {
        const rawText = document.getElementById('workbench_raw_text').value;
        const fileInput = document.getElementById('workbench_file_input');
        const shipmentId = document.getElementById('workbench_shipment_id').value;
        const btn = document.getElementById('btn-run-scan');

        const originalBtnText = btn.innerHTML;
        btn.innerHTML = '<span>⏳ Memproses Dokumen & Menghubungi MCP Engine...</span>';
        btn.disabled = true;

        const formData = new FormData();
        if (fileInput.files.length > 0) {
            formData.append('file', fileInput.files[0]);
        }
        if (rawText) {
            formData.append('raw_text', rawText);
        }

        try {
            let endpoint = '/api/mcp/parse-packing-list';
            if (shipmentId) {
                endpoint = `/api/mcp/shipments/${shipmentId}/detect-hs`;
            }

            const res = await fetch(endpoint, {
                method: 'POST',
                body: formData,
            });
            const data = await res.json();

            document.getElementById('workbench_empty_state').classList.add('hidden');
            document.getElementById('workbench_result_card').classList.remove('hidden');

            // Render Banner
            const banner = document.getElementById('result_banner_box');
            const icon = document.getElementById('result_icon');
            const title = document.getElementById('result_decl_title');
            const badge = document.getElementById('result_badge_direction');
            const desc = document.getElementById('result_desc');

            const isExport = (data.recommendation === 'PEB' || data.detected_type === 'EXPORT' || (data.jenis_layanan && data.jenis_layanan.includes('EKSPORT')));
            
            if (isExport) {
                banner.className = 'p-4 rounded-2xl border bg-amber-50/70 border-amber-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3';
                icon.textContent = '🚢';
                title.textContent = 'Rekomendasi Dokumen: Pemberitahuan Ekspor Barang (PEB BC 3.0)';
                badge.className = 'px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-amber-100 text-amber-800 border border-amber-300';
                badge.textContent = 'EKSPORT_BC30';
                desc.textContent = 'Terdeteksi pengapalan Ekspor dari pelabuhan muat Indonesia.';
            } else {
                banner.className = 'p-4 rounded-2xl border bg-sky-50/70 border-sky-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3';
                icon.textContent = '📦';
                title.textContent = 'Rekomendasi Dokumen: Pemberitahuan Impor Barang (PIB BC 2.0)';
                badge.className = 'px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-sky-100 text-sky-800 border border-sky-300';
                badge.textContent = 'IMPORT_BC20';
                desc.textContent = 'Terdeteksi pengapalan Impor dengan tujuan pelabuhan bongkar Indonesia.';
            }

            // Apply Action Link if target shipment selected
            const actionContainer = document.getElementById('result_apply_action');
            if (shipmentId) {
                actionContainer.innerHTML = `<a href="/core/document/${shipmentId}" class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition-colors shrink-0">
                    Buka Proyek Shipment &rarr;
                </a>`;
            } else {
                actionContainer.innerHTML = `<span class="text-[10px] text-slate-400">Mode Pratinjau Bebas</span>`;
            }

            // Lartas Alert
            const lartasAlert = document.getElementById('result_lartas_alert');
            const lartasText = document.getElementById('result_lartas_text');
            if (data.has_lartas || (data.lartas_compliance && data.lartas_compliance.missing_documents_count > 0)) {
                lartasAlert.classList.remove('hidden');
                const reqDocs = data.required_customs_documents || (data.lartas_compliance ? data.lartas_compliance.required_documents : []);
                lartasText.innerHTML = `Terdeteksi <strong>${reqDocs.length} dokumen perizinan / Lartas</strong> yang wajib dipenuhi sebelum submit ke CEISA 4.0: ` + 
                    reqDocs.map(d => `<span class="font-mono font-bold">[${d.kode_dokumen}] ${d.nama_dokumen}</span>`).join(', ');
            } else {
                lartasAlert.classList.add('hidden');
            }

            // Table of Items
            const items = data.items || [];
            document.getElementById('result_items_count').textContent = `${items.length} Barang`;
            const tbody = document.getElementById('result_items_tbody');
            tbody.innerHTML = '';

            items.forEach((it, i) => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-50 transition-colors';
                const hasLartas = it.has_lartas;
                tr.innerHTML = `
                    <td class="py-2.5 px-3 font-mono text-slate-400">${i + 1}</td>
                    <td class="py-2.5 px-3 font-mono font-bold text-sky-800">${it.kode_hs}</td>
                    <td class="py-2.5 px-3 max-w-xs">
                        <div class="font-bold text-slate-800 truncate">${it.uraian_barang}</div>
                        <div class="text-[10px] text-slate-400">${it.hs_legal_basis || '-'}</div>
                    </td>
                    <td class="py-2.5 px-3 text-right font-mono">${it.jumlah_kemasan} ${it.jenis_kemasan}</td>
                    <td class="py-2.5 px-3 text-right font-mono">${Number(it.berat_bruto).toLocaleString('id-ID')} Kg</td>
                    <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-900">${Number(it.nilai_fob).toLocaleString('id-ID')}</td>
                    <td class="py-2.5 px-3 text-center">
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold ${hasLartas ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-emerald-100 text-emerald-800 border border-emerald-300'}">
                            ${hasLartas ? 'LARTAS' : 'BEBAS'}
                        </span>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            // Raw JSON Output
            document.getElementById('result_raw_json').textContent = JSON.stringify(data, null, 2);

        } catch (e) {
            alert('Gagal memproses dokumen via MCP: ' + e.message);
        } finally {
            btn.innerHTML = originalBtnText;
            btn.disabled = false;
        }
    }

    async function executeHsLookup() {
        const query = document.getElementById('lookup_query_input').value;
        if (!query) return;

        try {
            const res = await fetch('/api/mcp/check-hs', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ query: query }),
            });
            const resp = await res.json();
            if (resp && resp.success) {
                const d = resp.data;
                document.getElementById('lookup_result_box').classList.remove('hidden');
                document.getElementById('lookup_res_code').textContent = d.code;
                document.getElementById('lookup_res_desc').textContent = d.desc;
                document.getElementById('lookup_res_legal').textContent = d.legal;
                document.getElementById('lookup_res_bm').textContent = `${d.bm}%`;
                document.getElementById('lookup_res_ppn').textContent = `${d.ppn}%`;
                document.getElementById('lookup_res_pph').textContent = `${d.pph}%`;
                document.getElementById('lookup_res_bk').textContent = `${d.bk ?? 0}%`;

                const badge = document.getElementById('lookup_res_lartas_badge');
                if (d.has_lartas) {
                    badge.className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300';
                    badge.textContent = 'LARTAS (Wajib Izin)';
                } else {
                    badge.className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300';
                    badge.textContent = 'NON-LARTAS (Bebas Izin)';
                }

                const docsList = document.getElementById('lookup_res_docs_list');
                docsList.innerHTML = '';
                if (d.required_documents && d.required_documents.length > 0) {
                    d.required_documents.forEach(doc => {
                        const div = document.createElement('div');
                        div.className = 'p-2 rounded bg-slate-50 border border-slate-200 flex justify-between items-center';
                        div.innerHTML = `
                            <span><strong>[${doc.kode_dokumen}]</strong> ${doc.nama_dokumen}</span>
                            <span class="text-[10px] text-slate-400 font-sans">${doc.instansi}</span>
                        `;
                        docsList.appendChild(div);
                    });
                } else {
                    docsList.innerHTML = '<span class="italic text-slate-400">Tidak ada dokumen izin tambahan yang dipersyaratkan.</span>';
                }
            }
        } catch (e) {
            alert('Gagal mencari pos tarif: ' + e.message);
        }
    }
</script>

@endsection
