@extends('layouts.app')

@section('title', 'AI Agent & MCP Customs Server (OpenAI BTKI Engine)')

@section('content')
<div class="max-w-[1360px] mx-auto px-4 sm:px-6 space-y-6 pt-4 pb-28 lg:pb-12">

    <!-- Page Header & AI Gateway Status -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="text-xs text-slate-500 font-medium mb-1">
                <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a> / <span class="text-slate-800">AI Agent & MCP</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
                <span>AI Customs Agent & MCP Gateway</span>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wide bg-gradient-to-r from-indigo-500 to-sky-500 text-white shadow-xs">
                    OpenAI Engine v3.0
                </span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Pemindaian cerdas Packing List, pencocokan otomatis pos tarif HS Code BTKI 2022, audit kepatuhan Lartas Bea Cukai, serta penyusunan deklarasi PIB (BC 2.0) dan PEB (BC 3.0).</p>
        </div>
        <div class="flex items-center space-x-2">
            <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-semibold">
                <span class="w-2 h-2 rounded-full bg-emerald-600 mr-2 animate-pulse"></span>
                OpenAI Gateway: ONLINE
            </span>
            <a href="{{ route('api.mcp.tools') }}" target="_blank" class="inline-flex items-center space-x-1 px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-all shadow-sm">
                <span>⚡ Tools Schema JSON</span>
            </a>
        </div>
    </div>

    <!-- Active AI Engine Banner & Model Selector Bar -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-5 text-white shadow-lg border border-indigo-900/60 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center space-x-2 text-indigo-300 text-[11px] font-bold uppercase tracking-wider">
                <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>Gateway Endpoint Terhubung</span>
            </div>
            <div class="font-mono text-xs text-emerald-400 font-semibold flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>{{ $aiEndpoint ?? 'https://r4g77gv.abc-tunnel.us/v1' }}</span>
            </div>
            <p class="text-[11px] text-slate-300">Mendukung 13 Model AI (OpenAI Codex Astra, Sol, Terra, Luna & Google Antigravity Gemini Flash).</p>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center gap-2.5">
            <label for="global_ai_model" class="text-xs text-indigo-200 font-semibold shrink-0">Model AI Aktif:</label>
            <select id="global_ai_model" onchange="syncSelectedModel(this.value)" class="px-3.5 py-2 rounded-xl bg-slate-800/90 border border-indigo-500/40 text-white font-mono text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-sky-400 shadow-inner">
                <optgroup label="⭐ OpenAI Codex Astra (Rekomendasi Utama)">
                    <option value="cx/gpt-6-astra" {{ ($defaultModel ?? '') === 'cx/gpt-6-astra' ? 'selected' : '' }}>cx/gpt-6-astra (Balanced & Cepat) [REKOMENDASI]</option>
                    <option value="cx/gpt-6-astra-xhigh" {{ ($defaultModel ?? '') === 'cx/gpt-6-astra-xhigh' ? 'selected' : '' }}>cx/gpt-6-astra-xhigh (Ultra High Precision)</option>
                </optgroup>
                <optgroup label="🏭 OpenAI Codex Sol (Permesinan & Manufaktur)">
                    <option value="cx/gpt-5.6-sol" {{ ($defaultModel ?? '') === 'cx/gpt-5.6-sol' ? 'selected' : '' }}>cx/gpt-5.6-sol (Stabil Industri)</option>
                    <option value="cx/gpt-5.6-sol-xhigh" {{ ($defaultModel ?? '') === 'cx/gpt-5.6-sol-xhigh' ? 'selected' : '' }}>cx/gpt-5.6-sol-xhigh (High Reasoning)</option>
                </optgroup>
                <optgroup label="📦 OpenAI Codex Terra & Luna (Customs & Logistics)">
                    <option value="cx/gpt-5.6-terra" {{ ($defaultModel ?? '') === 'cx/gpt-5.6-terra' ? 'selected' : '' }}>cx/gpt-5.6-terra (Customs Specialized)</option>
                    <option value="cx/gpt-5.6-luna" {{ ($defaultModel ?? '') === 'cx/gpt-5.6-luna' ? 'selected' : '' }}>cx/gpt-5.6-luna (Ringan & Cepat)</option>
                </optgroup>
                <optgroup label="⚡ OpenAI Codex 5.x">
                    <option value="cx/gpt-5.5" {{ ($defaultModel ?? '') === 'cx/gpt-5.5' ? 'selected' : '' }}>cx/gpt-5.5</option>
                    <option value="cx/gpt-5.5-xhigh" {{ ($defaultModel ?? '') === 'cx/gpt-5.5-xhigh' ? 'selected' : '' }}>cx/gpt-5.5-xhigh</option>
                    <option value="cx/gpt-5.4" {{ ($defaultModel ?? '') === 'cx/gpt-5.4' ? 'selected' : '' }}>cx/gpt-5.4</option>
                </optgroup>
                <optgroup label="🌐 Google Antigravity Gemini Series">
                    <option value="ag/gemini-3.8-flash-high" {{ ($defaultModel ?? '') === 'ag/gemini-3.8-flash-high' ? 'selected' : '' }}>ag/gemini-3.8-flash-high (Multimodal High)</option>
                    <option value="ag/gemini-3.8-flash-medium" {{ ($defaultModel ?? '') === 'ag/gemini-3.8-flash-medium' ? 'selected' : '' }}>ag/gemini-3.8-flash-medium</option>
                    <option value="ag/gemini-3.8-flash-low" {{ ($defaultModel ?? '') === 'ag/gemini-3.8-flash-low' ? 'selected' : '' }}>ag/gemini-3.8-flash-low (Ultra Fast)</option>
                    <option value="ag/gemini-3.7-flash-high" {{ ($defaultModel ?? '') === 'ag/gemini-3.7-flash-high' ? 'selected' : '' }}>ag/gemini-3.7-flash-high</option>
                </optgroup>
            </select>
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
                    <span>Customs Intelligence Briefing</span>
                </div>
                <h2 class="text-lg font-bold mb-2">Analisis Kepabeanan & Risiko</h2>
                <div class="space-y-3 text-xs text-slate-300 mt-4">
                    <div class="p-3 rounded-2xl bg-white/5 border border-white/10">
                        <strong class="text-white block font-semibold mb-0.5">&bull; Integrasi OpenAI & BTKI 2022:</strong>
                        AI memindai dokumen Packing List secara holistik, mengekstrak uraian, kemasan, bobot, FOB, serta mencocokkan pos tarif 8-digit.
                    </div>
                    <div class="p-3 rounded-2xl bg-white/5 border border-white/10">
                        <strong class="text-white block font-semibold mb-0.5">&bull; Audit Perizinan Lartas:</strong>
                        Mendeteksi dokumen izin wajib (LS Kemendag, SNI Wajib, SDPPI, V-Legal Kayu, Karantina, BPOM).
                    </div>
                    <div class="p-3 rounded-2xl bg-white/5 border border-white/10">
                        <strong class="text-white block font-semibold mb-0.5">&bull; Human-in-the-Loop:</strong>
                        Hasil pencocokan AI dapat ditinjau dan langsung diaplikasikan ke formulir draft PIB (BC 2.0) atau PEB (BC 3.0).
                    </div>
                </div>
            </div>
            <div class="mt-6 pt-4 border-t border-indigo-900/50 text-[11px] text-indigo-300 flex items-center justify-between">
                <span>Engine: OpenAI BTKI 2022</span>
                <span class="font-mono text-sky-400" id="current_model_pill">cx/gpt-6-astra</span>
            </div>
        </div>

        <!-- Antrian Usulan Aksi & Approval Manusia (Human-in-the-Loop) -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm lg:col-span-2 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <div>
                        <h2 class="font-bold text-xs text-slate-800 uppercase tracking-wider">Antrian Usulan Tindakan AI (Pending Approvals)</h2>
                        <p class="text-[11px] text-slate-500 mt-0.5">Keputusan final pabean & submit CEISA 4.0 tetap memerlukan konfirmasi staf operasional.</p>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                        {{ $pendingApprovals }} Dokumen Menunggu
                    </span>
                </div>

                <div class="space-y-3">
                    <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/70 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center space-x-2 mb-1">
                                <span class="font-bold text-slate-900">Pencocokan Packing List dengan Pos Tarif HS Code & PIB BC 2.0</span>
                                <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 text-[10px] font-bold">TOOL: MATCH_PACKING_LIST</span>
                            </div>
                            <p class="text-slate-600 text-[11px]">Sistem AI secara otomatis mengekstrak tabel barang, memetakan ke pos tarif 8-digit, dan memvalidasi bobot serta kemasan 3-Way Matching.</p>
                            <div class="text-[10px] text-slate-400 mt-1">Status Gateway: <strong class="font-mono text-emerald-700">ONLINE / READY</strong></div>
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
                                <span class="font-bold text-slate-900">Deteksi Otomatis Dokumen Tambahan Lartas Baru (Karantina / LS / SNI)</span>
                                <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 text-[10px] font-bold">TOOL: LARTAS_AUDIT</span>
                            </div>
                            <p class="text-slate-600 text-[11px]">Memeriksa kepatuhan izin instansi terkait sebelum deklarasi dikirimkan ke CEISA 4.0 DJBC.</p>
                        </div>
                        <div class="flex items-center space-x-2 shrink-0">
                            <span class="px-2.5 py-1 rounded-xl bg-emerald-100 text-emerald-800 font-bold text-[10px]">
                                Real-Time Sync
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
                    <span>AI Packing List & HS Code Matcher Workbench</span>
                </h2>
                <p class="text-slate-500 text-xs mt-0.5">Uji coba interaktif pengunggahan berkas packing list, pencocokan pos tarif BTKI 2022 via OpenAI Engine, audit izin lartas, dan rekomendasi PIB / PEB.</p>
            </div>

            <!-- Tab Buttons -->
            <div class="flex items-center space-x-1.5 p-1 bg-slate-100 rounded-2xl">
                <button type="button" onclick="switchWorkbenchTab('scanner')" id="tab-btn-scanner" class="px-3.5 py-1.5 rounded-xl font-bold text-xs bg-white text-slate-900 shadow-sm transition-all cursor-pointer">
                    📄 Packing List & HS Matcher (AI)
                </button>
                <button type="button" onclick="switchWorkbenchTab('lookup')" id="tab-btn-lookup" class="px-3.5 py-1.5 rounded-xl font-bold text-xs text-slate-600 hover:text-slate-900 transition-all cursor-pointer">
                    🔍 HS Code & Lartas Lookup
                </button>
                <button type="button" onclick="switchWorkbenchTab('mcp-docs')" id="tab-btn-mcp-docs" class="px-3.5 py-1.5 rounded-xl font-bold text-xs text-slate-600 hover:text-slate-900 transition-all cursor-pointer">
                    ⚙️ OpenAI & MCP Config
                </button>
            </div>
        </div>

        <!-- TAB 1: Packing List Scanner -->
        <div id="tab-content-scanner" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- Left Column: Input Form -->
                <div class="lg:col-span-5 space-y-4">
                    <!-- 1. Pilihan Model & Arah Pengapalan -->
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/70 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-800 text-xs">1. Pengaturan AI & Alur Pabean</span>
                            <span class="text-[10px] text-indigo-600 font-semibold" id="model_badge_display">cx/gpt-6-astra</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] text-slate-500 font-semibold mb-1">Arah Pengapalan:</label>
                                <select id="workbench_direction" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 bg-white font-semibold text-xs">
                                    <option value="IMPORT_BC20" selected>Impor &rarr; PIB (BC 2.0)</option>
                                    <option value="EKSPORT_BC30">Ekspor &rarr; PEB (BC 3.0)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] text-slate-500 font-semibold mb-1">Pilih Model AI:</label>
                                <select id="workbench_model_select" onchange="syncSelectedModel(this.value)" class="w-full px-2.5 py-2 rounded-xl border border-slate-200 bg-white font-semibold text-xs">
                                    <option value="cx/gpt-6-astra" selected>cx/gpt-6-astra (Rekomendasi)</option>
                                    <option value="cx/gpt-6-astra-xhigh">cx/gpt-6-astra-xhigh</option>
                                    <option value="cx/gpt-5.6-terra">cx/gpt-5.6-terra (Customs)</option>
                                    <option value="cx/gpt-5.6-sol">cx/gpt-5.6-sol</option>
                                    <option value="cx/gpt-5.6-sol-xhigh">cx/gpt-5.6-sol-xhigh</option>
                                    <option value="cx/gpt-5.6-luna">cx/gpt-5.6-luna</option>
                                    <option value="ag/gemini-3.8-flash-high">ag/gemini-3.8-flash-high</option>
                                    <option value="ag/gemini-3.8-flash-medium">ag/gemini-3.8-flash-medium</option>
                                    <option value="ag/gemini-3.8-flash-low">ag/gemini-3.8-flash-low</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Target Shipment Opsional -->
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/70 space-y-2">
                        <div class="font-bold text-slate-800 text-xs flex items-center justify-between">
                            <span>2. Target Proyek Shipment (Opsional)</span>
                            <span class="text-[10px] text-slate-400">Sinkronisasi otomatis</span>
                        </div>
                        <select id="workbench_shipment_id" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white font-semibold text-xs">
                            <option value="">-- Mode Simulasi Mandiri (Tanpa Simpan) --</option>
                            @foreach($activeShipments as $s)
                                <option value="{{ $s->id }}">{{ $s->shipment_number ?? $s->project_no }} - {{ $s->client->name ?? 'Klien' }} ({{ $s->jenis_layanan ?? $s->type }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 3. Berkas Upload -->
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/70 space-y-2">
                        <div class="font-bold text-slate-800 text-xs flex items-center justify-between">
                            <span>3. Unggah Berkas Dokumen Packing List</span>
                            <span class="text-[10px] text-slate-400">PDF, Excel, CSV, Gambar, TXT</span>
                        </div>
                        <input type="file" id="workbench_file_input" accept=".pdf,.xlsx,.xls,.csv,.txt,.jpg,.jpeg,.png"
                               class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white hover:file:bg-slate-800 cursor-pointer">
                    </div>

                    <!-- 4. Teks Mentah / Paste -->
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/70 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-800 text-xs">4. Atau Tempel Teks Dokumen / Packing List:</span>
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="loadSampleImport()" class="text-[10px] text-indigo-600 hover:text-indigo-800 font-semibold cursor-pointer">
                                    + Contoh Impor
                                </button>
                                <span class="text-slate-300">|</span>
                                <button type="button" onclick="loadSampleExport()" class="text-[10px] text-emerald-600 hover:text-emerald-800 font-semibold cursor-pointer">
                                    + Contoh Ekspor
                                </button>
                            </div>
                        </div>
                        <textarea id="workbench_raw_text" rows="6" placeholder="Tempelkan baris packing list, invoice, atau uraian komoditas di sini..."
                                  class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono text-[11px] bg-white focus:outline-none focus:ring-2 focus:ring-sky-500"></textarea>
                    </div>

                    <!-- Tombol Eksekusi -->
                    <div class="pt-1">
                        <button type="button" onclick="executeWorkbenchScan()" id="btn-run-scan"
                                class="w-full py-3 rounded-2xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 hover:from-slate-800 hover:to-indigo-900 text-white font-bold text-xs shadow-md transition-all active:scale-[0.98] flex items-center justify-center space-x-2 cursor-pointer">
                            <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span>Jalankan Pencocokan HS Code AI</span>
                        </button>
                    </div>
                </div>

                <!-- Right Column: Visual Result Inspector -->
                <div class="lg:col-span-7 flex flex-col justify-between">
                    <div id="workbench_result_container" class="space-y-4">
                        <!-- Placeholder State -->
                        <div id="workbench_empty_state" class="p-12 text-center border-2 border-dashed border-slate-200 rounded-3xl text-slate-400 space-y-3 bg-slate-50/50">
                            <span class="text-4xl block">📦</span>
                            <div class="font-bold text-slate-700 text-sm">Hasil Pencocokan AI Packing List & HS Code Akan Tampil di Sini</div>
                            <p class="text-[11px] text-slate-400 max-w-md mx-auto">
                                Unggah berkas dokumen packing list atau gunakan tombol <span class="font-semibold text-indigo-600 cursor-pointer" onclick="loadSampleImport()">"+ Contoh Impor"</span> di sebelah kiri, lalu klik <span class="font-semibold text-slate-800">"Jalankan Pencocokan HS Code AI"</span>.
                            </p>
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-[10px] font-mono">
                                <span>Powered by OpenAI & CEISA 4.0 BTKI 2022</span>
                            </div>
                        </div>

                        <!-- Result Card (Initially Hidden) -->
                        <div id="workbench_result_card" class="hidden space-y-4">

                            <!-- Header Result & Recommendation -->
                            <div class="p-4 rounded-2xl border flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs" id="result_banner_box">
                                <div class="flex items-center space-x-3">
                                    <span class="text-3xl" id="result_icon">📦</span>
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
                                <div id="result_apply_action" class="shrink-0 flex items-center gap-2"></div>
                            </div>

                            <!-- Metrics Summary Pills -->
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200 text-center">
                                    <div class="text-[10px] font-bold text-slate-400 uppercase">Total Barang</div>
                                    <div class="font-mono font-extrabold text-slate-900 text-sm" id="metric_total_items">0 Item</div>
                                </div>
                                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200 text-center">
                                    <div class="text-[10px] font-bold text-slate-400 uppercase">Total Kemasan</div>
                                    <div class="font-mono font-extrabold text-slate-900 text-sm" id="metric_total_pkg">0 Pkg</div>
                                </div>
                                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200 text-center">
                                    <div class="text-[10px] font-bold text-slate-400 uppercase">Bobot Bruto / Netto</div>
                                    <div class="font-mono font-extrabold text-slate-900 text-xs" id="metric_weights">0 / 0 Kg</div>
                                </div>
                                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200 text-center">
                                    <div class="text-[10px] font-bold text-slate-400 uppercase">Total Nilai FOB</div>
                                    <div class="font-mono font-extrabold text-indigo-700 text-sm" id="metric_total_fob">USD 0.00</div>
                                </div>
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
                            <div class="border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
                                <div class="bg-slate-50 px-4 py-2.5 font-bold text-slate-800 text-xs flex justify-between items-center border-b border-slate-200">
                                    <div class="flex items-center space-x-2">
                                        <span>Rincian Barang & Pencocokan HS Code BTKI 2022</span>
                                        <span class="px-2 py-0.5 rounded bg-indigo-100 text-indigo-800 text-[9px] font-mono font-bold" id="result_model_tag">OpenAI cx/gpt-6-astra</span>
                                    </div>
                                    <span class="font-mono text-slate-500 text-[10px]" id="result_items_count">0 Items</span>
                                </div>
                                <div class="overflow-x-auto max-h-80 overflow-y-auto">
                                    <table class="w-full text-left text-xs">
                                        <thead class="bg-slate-100 text-slate-600 font-semibold border-b border-slate-200 text-[11px]">
                                            <tr>
                                                <th class="py-2.5 px-3">No</th>
                                                <th class="py-2.5 px-3">Pos Tarif (HS)</th>
                                                <th class="py-2.5 px-3">Uraian Komoditas</th>
                                                <th class="py-2.5 px-3 text-right">Kemasan</th>
                                                <th class="py-2.5 px-3 text-right">Bruto (Kg)</th>
                                                <th class="py-2.5 px-3 text-right">Nilai FOB</th>
                                                <th class="py-2.5 px-3 text-center">Tarif Pabean</th>
                                                <th class="py-2.5 px-3 text-center">Lartas</th>
                                            </tr>
                                        </thead>
                                        <tbody id="result_items_tbody" class="divide-y divide-slate-100">
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Direct Create Declaration Action Bar -->
                            <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="text-[11px] text-slate-600">
                                    <span>Gunakan hasil pencocokan AI ini untuk langsung mengisi formulir pabean:</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('bc20.create') }}" id="btn_apply_bc20" class="px-3.5 py-1.5 rounded-xl bg-sky-700 hover:bg-sky-800 text-white font-bold text-xs transition-colors shadow-xs">
                                        📝 Buka Formulir PIB (BC 2.0)
                                    </a>
                                    <a href="{{ route('bc30.create') }}" id="btn_apply_bc30" class="px-3.5 py-1.5 rounded-xl bg-amber-700 hover:bg-amber-800 text-white font-bold text-xs transition-colors shadow-xs">
                                        📝 Buka Formulir PEB (BC 3.0)
                                    </a>
                                </div>
                            </div>

                            <!-- Raw JSON Toggle -->
                            <div class="pt-1">
                                <details class="group bg-slate-900 text-white rounded-2xl overflow-hidden">
                                    <summary class="px-4 py-2 text-xs font-mono font-bold cursor-pointer hover:bg-slate-800 flex items-center justify-between">
                                        <span class="text-indigo-300">Respon Mentah AI & Data JSON Terstruktur</span>
                                        <span class="text-[10px] text-slate-400 group-open:rotate-180 transition-transform">▼</span>
                                    </summary>
                                    <pre id="result_raw_json" class="p-4 text-[10px] font-mono text-emerald-300 overflow-x-auto bg-black/40 max-h-56"></pre>
                                </details>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- TAB 2: HS Code & Lartas Lookup -->
        <div id="tab-content-lookup" class="hidden space-y-6">
            <div class="max-w-3xl bg-slate-50 p-5 rounded-2xl border border-slate-200 space-y-4">
                <div>
                    <label class="block font-bold text-slate-800 mb-1">Cari Pos Tarif HS Code 8-Digit atau Ketikkan Uraian Komoditas (Didukung AI):</label>
                    <p class="text-[11px] text-slate-500 mb-2">Masukkan nama barang, spesifikasi teknis, atau nomor kode HS untuk mencocokkan pos tarif BTKI 2022 beserta tarif BM, PPN, PPh, BK dan izin Lartas.</p>
                    <div class="flex space-x-2">
                        <input type="text" id="lookup_query_input" placeholder="Misal: Sepatu Olahraga Sneakers, Beras Ketan Hitam, Kopi Arabika, 8504.40.90, Mesin Jahit Industri"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-200 font-mono text-xs bg-white focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <button type="button" onclick="executeHsLookup()" id="btn-lookup-hs" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shrink-0 cursor-pointer flex items-center space-x-1.5">
                            <span>🔍 Cari via AI</span>
                        </button>
                    </div>
                </div>

                <div id="lookup_result_box" class="hidden bg-white p-5 rounded-2xl border border-slate-200 space-y-3.5 shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                        <div class="flex items-center space-x-2">
                            <span class="font-mono font-extrabold text-sky-800 text-base" id="lookup_res_code">8504.40.90</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-sky-100 text-sky-800" id="lookup_res_clean">85044090</span>
                        </div>
                        <span id="lookup_res_lartas_badge" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold">LARTAS</span>
                    </div>

                    <div>
                        <strong class="block text-slate-900 font-bold text-sm" id="lookup_res_desc">Deskripsi</strong>
                        <div class="text-[11px] text-slate-600 mt-1 font-mono bg-slate-50 p-2.5 rounded-xl border border-slate-200/60" id="lookup_res_legal">Dasar Hukum</div>
                    </div>

                    <div class="grid grid-cols-4 gap-2 bg-slate-50 p-3 rounded-xl text-center font-mono text-xs border border-slate-200/60">
                        <div class="p-1">
                            <span class="text-[10px] text-slate-400 block font-bold">BEA MASUK (BM)</span>
                            <strong class="text-slate-900" id="lookup_res_bm">5%</strong>
                        </div>
                        <div class="p-1">
                            <span class="text-[10px] text-slate-400 block font-bold">PPN</span>
                            <strong class="text-slate-900" id="lookup_res_ppn">11%</strong>
                        </div>
                        <div class="p-1">
                            <span class="text-[10px] text-slate-400 block font-bold">PPH 22</span>
                            <strong class="text-slate-900" id="lookup_res_pph">2.5%</strong>
                        </div>
                        <div class="p-1">
                            <span class="text-[10px] text-slate-400 block font-bold">BEA KELUAR (BK)</span>
                            <strong class="text-slate-900" id="lookup_res_bk">0%</strong>
                        </div>
                    </div>

                    <div id="lookup_res_docs_container" class="space-y-1.5 pt-1">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Ketentuan Perizinan / Dokumen Lartas Wajib:</span>
                        <div id="lookup_res_docs_list" class="space-y-1.5 text-[11px]"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 3: MCP & OpenAI Config -->
        <div id="tab-content-mcp-docs" class="hidden space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
                <div class="p-5 rounded-2xl bg-slate-900 text-white space-y-3.5">
                    <div class="font-bold text-sm text-indigo-300 flex items-center space-x-2">
                        <span>🤖</span>
                        <span>Konfigurasi OpenAI Gateway & Model</span>
                    </div>
                    <p class="text-[11px] text-slate-300">Gateway OpenAI-compatible ini terhubung ke tunnel server AI berkemampuan tinggi dengan 13 varian model:</p>
                    <div class="p-3 rounded-xl bg-black/60 font-mono text-[11px] space-y-1.5 text-slate-200 border border-white/10">
                        <div><strong class="text-indigo-400">Endpoint:</strong> <span class="text-emerald-300">{{ $aiEndpoint ?? 'https://r4g77gv.abc-tunnel.us/v1' }}</span></div>
                        <div><strong class="text-indigo-400">Auth:</strong> Bearer sk-59807b... (Active)</div>
                        <div><strong class="text-indigo-400">Default Model:</strong> cx/gpt-6-astra</div>
                    </div>
                    <div class="pt-2">
                        <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Daftar Model Tersedia:</span>
                        <div class="grid grid-cols-2 gap-1.5 mt-2 font-mono text-[10px]">
                            <div class="p-1.5 rounded bg-white/5 border border-white/5 text-sky-300">cx/gpt-6-astra-xhigh</div>
                            <div class="p-1.5 rounded bg-white/5 border border-white/5 text-sky-300">cx/gpt-6-astra</div>
                            <div class="p-1.5 rounded bg-white/5 border border-white/5 text-sky-300">cx/gpt-5.6-sol-xhigh</div>
                            <div class="p-1.5 rounded bg-white/5 border border-white/5 text-sky-300">cx/gpt-5.6-sol</div>
                            <div class="p-1.5 rounded bg-white/5 border border-white/5 text-sky-300">cx/gpt-5.6-terra</div>
                            <div class="p-1.5 rounded bg-white/5 border border-white/5 text-sky-300">cx/gpt-5.6-luna</div>
                            <div class="p-1.5 rounded bg-white/5 border border-white/5 text-amber-300">cx/gpt-5.5-xhigh</div>
                            <div class="p-1.5 rounded bg-white/5 border border-white/5 text-amber-300">cx/gpt-5.5</div>
                            <div class="p-1.5 rounded bg-white/5 border border-white/5 text-amber-300">cx/gpt-5.4</div>
                            <div class="p-1.5 rounded bg-white/5 border border-white/5 text-emerald-300">ag/gemini-3.8-flash-high</div>
                            <div class="p-1.5 rounded bg-white/5 border border-white/5 text-emerald-300">ag/gemini-3.8-flash-med</div>
                            <div class="p-1.5 rounded bg-white/5 border border-white/5 text-emerald-300">ag/gemini-3.8-flash-low</div>
                            <div class="p-1.5 rounded bg-white/5 border border-white/5 text-emerald-300">ag/gemini-3.7-flash-high</div>
                        </div>
                    </div>
                </div>

                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                    <div class="font-bold text-sm text-slate-800 flex items-center space-x-2">
                        <span>🌐</span>
                        <span>Endpoint Internal Web & API</span>
                    </div>
                    <div class="space-y-2 font-mono text-[11px]">
                        <div class="p-2.5 bg-white rounded-xl border border-slate-200">
                            <span class="font-bold text-emerald-700">POST</span> /ai/match-packing-list
                            <div class="text-[10px] text-slate-500 font-sans mt-0.5">Mencocokkan file/teks Packing List dengan pos tarif BTKI 2022 via OpenAI Engine.</div>
                        </div>
                        <div class="p-2.5 bg-white rounded-xl border border-slate-200">
                            <span class="font-bold text-emerald-700">POST</span> /ai/classify-hs
                            <div class="text-[10px] text-slate-500 font-sans mt-0.5">Klasifikasi komoditas tunggal ke pos tarif 8-digit.</div>
                        </div>
                        <div class="p-2.5 bg-white rounded-xl border border-slate-200">
                            <span class="font-bold text-sky-700">POST</span> /core/document/parse-documents
                            <div class="text-[10px] text-slate-500 font-sans mt-0.5">Ekstraksi berkas pabean di form pembuatan PIB BC 2.0 & PEB BC 3.0.</div>
                        </div>
                        <div class="p-2.5 bg-white rounded-xl border border-slate-200">
                            <span class="font-bold text-slate-700">POST</span> /api/mcp/parse-packing-list
                            <div class="text-[10px] text-slate-500 font-sans mt-0.5">Endpoint MCP Protocol resmi untuk Claude / Cursor / Antigravity.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

<script>
    function syncSelectedModel(model) {
        document.getElementById('global_ai_model').value = model;
        const sel = document.getElementById('workbench_model_select');
        if (sel) sel.value = model;
        const pill = document.getElementById('current_model_pill');
        if (pill) pill.textContent = model;
        const badge = document.getElementById('model_badge_display');
        if (badge) badge.textContent = model;
    }

    function switchWorkbenchTab(tab) {
        const tabs = ['scanner', 'lookup', 'mcp-docs'];
        tabs.forEach(t => {
            const btn = document.getElementById(`tab-btn-${t}`);
            const content = document.getElementById(`tab-content-${t}`);
            if (t === tab) {
                btn.className = 'px-3.5 py-1.5 rounded-xl font-bold text-xs bg-white text-slate-900 shadow-sm transition-all cursor-pointer';
                content.classList.remove('hidden');
            } else {
                btn.className = 'px-3.5 py-1.5 rounded-xl font-bold text-xs text-slate-600 hover:text-slate-900 transition-all cursor-pointer';
                content.classList.add('hidden');
            }
        });
    }

    function loadSampleImport() {
        document.getElementById('workbench_direction').value = 'IMPORT_BC20';
        document.getElementById('workbench_raw_text').value = `PACKING LIST & COMMERCIAL INVOICE
INVOICE NO: INV/2026/SH-JKT/0912
DATE: 2026-10-15
SHIPPER: SHANGHAI TEXTILE & MACHINERY CO., LTD
CONSIGNEE: PT AGUNG MAJU MANDIRI, JAKARTA, INDONESIA
PORT OF LOADING: SHANGHAI, CHINA (CNSHA)
PORT OF DISCHARGE: TANJUNG PRIOK, JAKARTA (IDTPP)

ITEMS:
1. HIGH SPEED INDUSTRIAL SEWING MACHINE MODEL DDL-8700 | 20 CTN | GW: 1,400 KGS | NW: 1,200 KGS | TOTAL FOB USD 8,000.00
2. SEWING MACHINE SPARE PARTS: NEEDLES, BOBBINS, ROTARY HOOKS | 5 BOX | GW: 150 KGS | NW: 130 KGS | TOTAL FOB USD 1,500.00
3. 100% COMBED COTTON WOVEN FABRIC DYED WIDTH 58 INCH | 40 ROLLS | GW: 2,200 KGS | NW: 2,100 KGS | TOTAL FOB USD 14,000.00
4. SOLAR INVERTER GRID TIE 5KW WITH MPPT CONTROLLER | 10 CTN | GW: 250 KGS | NW: 220 KGS | TOTAL FOB USD 4,500.00`;
    }

    function loadSampleExport() {
        document.getElementById('workbench_direction').value = 'EKSPORT_BC30';
        document.getElementById('workbench_raw_text').value = `EXPORT PACKING LIST & COMMERCIAL INVOICE
INVOICE NO: EXP/2026/JKT-ROT/0451
EXPORTER: PT AGUNG AGRO NUSANTARA (JAKARTA, INDONESIA)
BUYER: ROTTERDAM COMMODITIES B.V. (NETHERLANDS)
PORT OF LOADING: TANJUNG PERAK, SURABAYA (IDSUB)
PORT OF DISCHARGE: ROTTERDAM (NLRTM)

ITEMS:
1. INDONESIAN ARABICA SPECIALTY GREEN COFFEE BEANS GRADE 1 | 300 JUTE BAGS | GW: 18,300 KGS | NW: 18,000 KGS | FOB USD 54,000.00
2. ORGANIC PALM SUGAR GRANULATED PREMIUM | 100 CTN | GW: 2,500 KGS | NW: 2,400 KGS | FOB USD 7,200.00
3. NATURAL TEAK WOOD INDOOR FURNITURE DINING TABLE SET | 25 CRATES | GW: 3,200 KGS | NW: 2,900 KGS | FOB USD 18,500.00`;
    }

    async function executeWorkbenchScan() {
        const rawText = document.getElementById('workbench_raw_text').value;
        const fileInput = document.getElementById('workbench_file_input');
        const shipmentId = document.getElementById('workbench_shipment_id').value;
        const direction = document.getElementById('workbench_direction').value;
        const model = document.getElementById('global_ai_model').value;
        const btn = document.getElementById('btn-run-scan');

        if (!rawText && fileInput.files.length === 0) {
            alert('Silakan pilih berkas dokumen Packing List atau tempelkan teks baris barang terlebih dahulu.');
            return;
        }

        const originalBtnText = btn.innerHTML;
        btn.innerHTML = `<span class="inline-flex items-center gap-2"><svg class="animate-spin h-4 w-4 text-sky-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg> Memindai & Mencocokkan via OpenAI (${model})...</span>`;
        btn.disabled = true;

        const formData = new FormData();
        if (fileInput.files.length > 0) {
            formData.append('file', fileInput.files[0]);
        }
        if (rawText) {
            formData.append('raw_text', rawText);
        }
        formData.append('direction', direction);
        formData.append('model', model);
        if (shipmentId) {
            formData.append('shipment_id', shipmentId);
        }

        try {
            const res = await fetch('{{ route('ai.match-packing-list') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData,
            });

            const data = await res.json();

            if (!res.ok || data.success === false) {
                throw new Error(data.error || data.message || 'Gagal memproses dokumen.');
            }

            document.getElementById('workbench_empty_state').classList.add('hidden');
            document.getElementById('workbench_result_card').classList.remove('hidden');

            // Render Banner Recommendation
            const banner = document.getElementById('result_banner_box');
            const icon = document.getElementById('result_icon');
            const title = document.getElementById('result_decl_title');
            const badge = document.getElementById('result_badge_direction');
            const desc = document.getElementById('result_desc');

            const isExport = (data.recommendation === 'PEB' || data.direction === 'EKSPORT_BC30' || data.detected_type === 'EXPORT');
            
            if (isExport) {
                banner.className = 'p-4 rounded-2xl border bg-amber-50/80 border-amber-300 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs';
                icon.textContent = '🚢';
                title.textContent = 'Rekomendasi Dokumen: Pemberitahuan Ekspor Barang (PEB BC 3.0)';
                badge.className = 'px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-amber-100 text-amber-800 border border-amber-300';
                badge.textContent = 'EKSPORT_BC30';
                desc.textContent = data.analysis_summary || 'Terdeteksi pengapalan Ekspor dari pelabuhan muat Indonesia.';
            } else {
                banner.className = 'p-4 rounded-2xl border bg-sky-50/80 border-sky-300 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs';
                icon.textContent = '📦';
                title.textContent = 'Rekomendasi Dokumen: Pemberitahuan Impor Barang (PIB BC 2.0)';
                badge.className = 'px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-sky-100 text-sky-800 border border-sky-300';
                badge.textContent = 'IMPORT_BC20';
                desc.textContent = data.analysis_summary || 'Terdeteksi pengapalan Impor dengan tujuan pelabuhan bongkar Indonesia.';
            }

            // Sync or Open Shipment Action
            const actionContainer = document.getElementById('result_apply_action');
            if (shipmentId) {
                actionContainer.innerHTML = `<a href="/core/document/${shipmentId}" class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition-colors shrink-0 shadow-xs">
                    Buka Proyek Shipment &rarr;
                </a>`;
            } else {
                const targetUrl = direction === 'export' ? '{{ route('bc30.create') }}' : '{{ route('bc20.create') }}';
                const label = direction === 'export' ? 'Buat Draft PEB (BC 3.0)' : 'Buat Draft PIB (BC 2.0)';
                actionContainer.innerHTML = `
                    <a href="${targetUrl}" class="px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-indigo-600 to-sky-600 hover:from-indigo-700 hover:to-sky-700 text-white font-bold text-xs transition-all shadow-xs shrink-0 flex items-center gap-1.5">
                        <span>✨</span>
                        <span>${label} &rarr;</span>
                    </a>
                `;
            }

            // Summary Metrics
            const totals = data.totals || {};
            const items = data.items || [];
            document.getElementById('metric_total_items').textContent = `${items.length} Item`;
            document.getElementById('metric_total_pkg').textContent = `${totals.total_kemasan || 0} Pkg`;
            document.getElementById('metric_weights').textContent = `${Number(totals.total_bruto || 0).toLocaleString('id-ID')} / ${Number(totals.total_netto || 0).toLocaleString('id-ID')} Kg`;
            document.getElementById('metric_total_fob').textContent = `${totals.currency || 'USD'} ${Number(totals.total_fob || 0).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;

            // Model Tag in Table Header
            document.getElementById('result_model_tag').textContent = `AI: ${data.model_used || model}`;
            document.getElementById('result_items_count').textContent = `${items.length} Barang`;

            // Lartas Alert
            const lartasAlert = document.getElementById('result_lartas_alert');
            const lartasText = document.getElementById('result_lartas_text');
            const reqDocs = data.required_customs_documents || [];
            if (data.has_lartas || reqDocs.length > 0) {
                lartasAlert.classList.remove('hidden');
                lartasText.innerHTML = `Terdeteksi <strong>${reqDocs.length} dokumen perizinan / Lartas</strong> yang wajib dipenuhi sebelum submit ke CEISA 4.0: ` + 
                    reqDocs.map(d => `<span class="font-mono font-bold">[${d.kode_dokumen}] ${d.nama_dokumen}</span> (${d.instansi})`).join(', ');
            } else {
                lartasAlert.classList.add('hidden');
            }

            // Populate Table
            const tbody = document.getElementById('result_items_tbody');
            tbody.innerHTML = '';

            items.forEach((it, i) => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-50 transition-colors';
                const hasLartas = it.has_lartas;
                tr.innerHTML = `
                    <td class="py-2.5 px-3 font-mono text-slate-400 font-semibold">${it.nomor_urut || (i + 1)}</td>
                    <td class="py-2.5 px-3">
                        <div class="flex items-center space-x-1.5">
                            <span class="font-mono font-bold text-sky-800 text-xs">${it.kode_hs}</span>
                            <button type="button" onclick="navigator.clipboard.writeText('${it.kode_hs}'); alert('Kode HS disalin!');" title="Salin Pos Tarif" class="text-slate-400 hover:text-slate-700 cursor-pointer">
                                📋
                            </button>
                        </div>
                        <div class="text-[9px] text-slate-400 font-mono">Conf: ${(it.hs_confidence_score * 100).toFixed(0)}%</div>
                    </td>
                    <td class="py-2.5 px-3 max-w-xs">
                        <div class="font-bold text-slate-900 leading-tight">${it.uraian_barang}</div>
                        <div class="text-[10px] text-slate-500 mt-0.5 line-clamp-2">${it.hs_legal_basis || it.hs_reasoning || '-'}</div>
                    </td>
                    <td class="py-2.5 px-3 text-right font-mono font-semibold">${it.jumlah_kemasan} ${it.jenis_kemasan}</td>
                    <td class="py-2.5 px-3 text-right font-mono text-slate-700">${Number(it.berat_bruto).toLocaleString('id-ID')}</td>
                    <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-900">${it.kode_valuta || 'USD'} ${Number(it.nilai_fob).toLocaleString('id-ID', {minimumFractionDigits: 2})}</td>
                    <td class="py-2.5 px-3 text-center font-mono text-[10px]">
                        <span class="text-slate-700">BM: <strong>${it.tarif_bm_persen}%</strong></span> |
                        <span class="text-slate-700">PPN: <strong>${it.tarif_ppn_persen}%</strong></span>
                        ${it.tarif_bk_persen > 0 ? `<div class="text-amber-700 font-bold">BK: ${it.tarif_bk_persen}%</div>` : ''}
                    </td>
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
            alert('Gagal memproses dokumen via OpenAI: ' + e.message);
        } finally {
            btn.innerHTML = originalBtnText;
            btn.disabled = false;
        }
    }

    async function executeHsLookup() {
        const query = document.getElementById('lookup_query_input').value;
        const model = document.getElementById('global_ai_model').value;
        const btn = document.getElementById('btn-lookup-hs');
        if (!query) return;

        const originalBtnText = btn.innerHTML;
        btn.innerHTML = '<span>⏳ Mencari...</span>';
        btn.disabled = true;

        try {
            const res = await fetch('{{ route('ai.classify-hs') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ query: query, model: model }),
            });
            const resp = await res.json();
            if (resp && resp.success) {
                const d = resp.data;
                document.getElementById('lookup_result_box').classList.remove('hidden');
                document.getElementById('lookup_res_code').textContent = d.code;
                document.getElementById('lookup_res_clean').textContent = d.clean_code || d.code.replace(/[^0-9]/g, '');
                document.getElementById('lookup_res_desc').textContent = d.desc;
                document.getElementById('lookup_res_legal').textContent = d.legal + (d.reasoning ? ' — ' + d.reasoning : '');
                document.getElementById('lookup_res_bm').textContent = `${d.bm}%`;
                document.getElementById('lookup_res_ppn').textContent = `${d.ppn}%`;
                document.getElementById('lookup_res_pph').textContent = `${d.pph}%`;
                document.getElementById('lookup_res_bk').textContent = `${d.bk ?? 0}%`;

                const badge = document.getElementById('lookup_res_lartas_badge');
                if (d.has_lartas) {
                    badge.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300';
                    badge.textContent = 'LARTAS (Wajib Izin)';
                } else {
                    badge.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300';
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
                            <span class="text-[10px] text-slate-500 font-sans">${doc.instansi}</span>
                        `;
                        docsList.appendChild(div);
                    });
                } else {
                    docsList.innerHTML = '<span class="italic text-slate-400">Tidak ada dokumen izin tambahan yang dipersyaratkan.</span>';
                }
            }
        } catch (e) {
            alert('Gagal mencari pos tarif: ' + e.message);
        } finally {
            btn.innerHTML = originalBtnText;
            btn.disabled = false;
        }
    }
</script>

@endsection
