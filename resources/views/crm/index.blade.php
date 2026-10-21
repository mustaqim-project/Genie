@extends('layouts.app')

@section('title', 'CRM & Pipeline Penjualan Logistik - PT Agung Trans Logistics')

@section('content')
<div class="max-w-[1360px] mx-auto px-4 sm:px-6 space-y-6 pt-4 pb-28 lg:pb-12" x-data="{
    activeTab: '{{ $currentTab }}',
    showCreateClientModal: false
}">

    <!-- 1. BENTO HEADER & ACTIONS -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div>
            <div class="text-xs text-slate-500 font-medium mb-1">
                <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a> / <span class="text-slate-800 font-semibold">CRM & Manajemen Klien</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Customer Relationship Management (CRM)</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Sistem hubungan klien freight forwarding: pipeline penjualan, penawaran harga (quotation), dan direktori importir/eksportir resmi PT Agung Trans Logistics.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            @canany(['crm.manage', 'document.create'])
            <button type="button" @click="showCreateClientModal = true"
                class="btn-apple-filled !py-2 !px-3.5 text-xs flex items-center gap-1.5 shadow-xs cursor-pointer">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Tambah Klien Baru</span>
            </button>
            @endcanany

            <a href="{{ route('crm.quotations.index') }}"
                class="px-4 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 shadow-xs flex items-center space-x-2 transition-all">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Kelola Quotation</span>
            </a>

            <a href="{{ route('inbox.index') }}"
                class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 shadow-sm flex items-center space-x-2 transition-all active:scale-95">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
                <span>Buka Kotak Masuk Terpadu</span>
            </a>
        </div>
    </div>

    <!-- 2. QUICK ACCESS HUB (OMNICHANNEL DEDICATED MODULES) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4 mb-6">
        <!-- Module 1: Kotak Masuk Terpadu -->
        <a href="{{ route('inbox.index') }}" class="group p-4 sm:p-5 rounded-3xl bg-white border border-slate-200/80 shadow-xs hover:border-emerald-300 hover:shadow-md transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse mr-1"></span>
                        Live Omnichannel
                    </span>
                </div>
                <div class="font-bold text-slate-900 text-sm group-hover:text-emerald-700 transition-colors">Kotak Masuk Terpadu</div>
                <p class="text-xs text-slate-500 mt-1 line-clamp-2">Percakapan chat WhatsApp WABA dan interaksi komentar/DM media sosial dalam 1 meja respon.</p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-emerald-600">
                <span>Buka Pesan Masuk</span>
                <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
            </div>
        </a>

        <!-- Module 2: WhatsApp WABA -->
        <a href="{{ route('whatsapp.index') }}" class="group p-4 sm:p-5 rounded-3xl bg-white border border-slate-200/80 shadow-xs hover:border-emerald-300 hover:shadow-md transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.97.53 1.951.815 2.796.815 3.183 0 5.767-2.586 5.768-5.766 0-3.18-2.585-5.766-5.768-5.766zm3.376 8.21c-.14.394-.71.724-1.015.768-.29.043-.664.068-2.148-.546-1.898-.786-3.119-2.73-3.214-2.857-.095-.127-.771-1.026-.771-1.955 0-.928.487-1.385.66-1.574.173-.189.378-.236.504-.236.126 0 .252.001.362.006.116.006.27-.044.423.323.158.378.539 1.314.587 1.408.047.095.079.205.016.331-.063.126-.095.205-.189.315-.094.11-.198.246-.283.33-.095.095-.194.198-.083.388.11.19.49 8.08 1.05 1.306.721.643 1.33.842 1.519.936.19.095.3.079.41-.047.11-.126.473-.551.6-.74.126-.19.252-.158.425-.095.174.063 1.103.52 1.292.615.19.095.316.142.363.22.047.079.047.457-.094.851z"/>
                        </svg>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                        Tier 10K / Day
                    </span>
                </div>
                <div class="font-bold text-slate-900 text-sm group-hover:text-emerald-700 transition-colors">WhatsApp WABA Cockpit</div>
                <p class="text-xs text-slate-500 mt-1 line-clamp-2">Kampanye siaran jadwal kapal (broadcast blast) & template resmi yang disetujui Meta.</p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-emerald-600">
                <span>Kelola Broadcast WABA</span>
                <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
            </div>
        </a>

        <!-- Module 3: Media Sosial -->
        <a href="{{ route('social-media.index') }}" class="group p-4 sm:p-5 rounded-3xl bg-white border border-slate-200/80 shadow-xs hover:border-blue-300 hover:shadow-md transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                        </svg>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">
                        Multi-Channel
                    </span>
                </div>
                <div class="font-bold text-slate-900 text-sm group-hover:text-blue-700 transition-colors">Media Sosial Resmi</div>
                <p class="text-xs text-slate-500 mt-1 line-clamp-2">Komposer feed 2-kolom interaktif dengan live smartphone preview (LinkedIn, IG, FB, TikTok).</p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-blue-600">
                <span>Buka Komposer Feed</span>
                <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
            </div>
        </a>

        <!-- Module 4: Pengaturan & Gateway -->
        <a href="{{ route('setting.index', ['tab' => 'waba']) }}" class="group p-4 sm:p-5 rounded-3xl bg-white border border-slate-200/80 shadow-xs hover:border-slate-400 hover:shadow-md transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                        Config Hub
                    </span>
                </div>
                <div class="font-bold text-slate-900 text-sm group-hover:text-slate-800 transition-colors">Pengaturan Gateway & API</div>
                <p class="text-xs text-slate-500 mt-1 line-clamp-2">Kredensial Meta WABA, webhook verify token, trigger auto-send SPPB/NPE/DO/Billing.</p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-slate-700">
                <span>Konfigurasi di Setting</span>
                <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
            </div>
        </a>
    </div>

    <!-- 3. PIPELINE STAGES OVERVIEW -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="p-4 rounded-3xl bg-white border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">1. Lead Masuk (Inquiry)</span>
            <div class="text-xl font-bold text-slate-900 mt-1">4 Calon Importir</div>
            <p class="text-[11px] text-slate-500 mt-1">Tanya freight rate FCL Shanghai-Jkt & izin Lartas</p>
        </div>
        <div class="p-4 rounded-3xl bg-blue-50/70 border border-blue-100 shadow-xs">
            <span class="text-[10px] font-bold text-blue-500 uppercase tracking-wider block">2. Kualifikasi & Cek Lartas</span>
            <div class="text-xl font-bold text-blue-900 mt-1">2 Dokumen Aju</div>
            <p class="text-[11px] text-blue-700 mt-1">Komoditas kosmetik & mesin presisi (Verifikasi LS)</p>
        </div>
        <div class="p-4 rounded-3xl bg-amber-50/70 border border-amber-100 shadow-xs">
            <span class="text-[10px] font-bold text-amber-600 uppercase tracking-wider block">3. Penawaran (Quotation)</span>
            <div class="text-xl font-bold text-amber-900 mt-1">3 Penawaran Aktif</div>
            <p class="text-[11px] text-amber-700 mt-1"><a href="{{ route('crm.quotations.index') }}" class="underline font-semibold">Buka modul penawaran harga &rarr;</a></p>
        </div>
        <div class="p-4 rounded-3xl bg-emerald-50/70 border border-emerald-100 shadow-xs">
            <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider block">4. Surat Kuasa PPJK Terbit</span>
            <div class="text-xl font-bold text-emerald-900 mt-1">6 Klien Aktif</div>
            <p class="text-[11px] text-emerald-700 mt-1">Siap order kontainer & PIB/PEB terhubung CEISA</p>
        </div>
    </div>

    <!-- 4. TABEL DAFTAR KLIEN IMPORTIR & EKSPORTIR -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden mb-8">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="font-bold text-slate-900 text-sm sm:text-base">Direktori Buku Kontak Importir & Eksportir</h2>
                <p class="text-xs text-slate-500 mt-0.5">Daftar klien logistik aktif yang memiliki penugasan shipment, dokumen kepabeanan, dan kontak resmi WhatsApp.</p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="text-xs text-slate-500 font-mono mr-2">Total: {{ $clients->total() }} Perusahaan</span>
                @canany(['crm.manage', 'document.create'])
                <button type="button" @click="showCreateClientModal = true" class="btn-apple-filled !py-1.5 !px-3 text-xs inline-flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Tambah Klien</span>
                </button>
                @endcanany
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">Nama Perusahaan / NPWP</th>
                        <th class="py-3 px-4">Kontak PIC & WA</th>
                        <th class="py-3 px-4">Tipe Bisnis</th>
                        <th class="py-3 px-4">Status Klien</th>
                        <th class="py-3 px-4">Terdaftar</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($clients as $client)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900">{{ $client->name }}</div>
                                <div class="text-[11px] font-mono text-slate-400 mt-0.5">NPWP: {{ $client->npwp ?: '-' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="text-slate-800 font-medium">{{ $client->contact_person ?: 'Bagian Impor / Ekspor' }}</div>
                                <div class="text-[11px] font-mono text-emerald-600 flex items-center space-x-1 mt-0.5">
                                    <span>{{ $client->phone ?: '+62 812-9000-8888' }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                                    {{ $client->business_type ?: 'Importir Umum (API-U)' }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $client->status === 'inactive' ? 'bg-slate-100 text-slate-600' : 'bg-emerald-100 text-emerald-800' }}">
                                    {{ $client->status === 'inactive' ? 'Nonaktif' : 'Aktif (PPJK)' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-500 font-mono text-[11px]">
                                {{ $client->created_at ? $client->created_at->format('d/m/Y') : '-' }}
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('inbox.index') }}"
                                    class="inline-flex items-center space-x-1 px-3 py-1.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs transition-colors shadow-xs">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                    <span>Kirim Pesan</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 italic">
                                Belum ada data klien terdaftar di direktori CRM.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $clients->links() }}
        </div>
    </div>

    <!-- 5. AKTIVITAS & DOKUMEN PABEAN TERKINI (CEISA) -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-900 text-sm sm:text-base">Aktivitas Shipment & Status Dokumen Kepabeanan Terkini</h3>
                <p class="text-xs text-slate-500 mt-0.5">Riwayat pergerakan kargo impor/ekspor yang dipantau real-time dari respon Bea Cukai CEISA 4.0.</p>
            </div>
            <span class="text-xs text-slate-400 font-mono">Realtime Log</span>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($recentShipments as $shipment)
                <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50/50 transition-colors">
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs shrink-0">
                            PIB
                        </div>
                        <div>
                            <div class="font-bold text-slate-900 text-xs font-mono">{{ $shipment->nomor_aju }}</div>
                            <div class="text-[11px] text-slate-500 mt-0.5">
                                Mitra Luar Negeri: <strong>{{ $shipment->nama_mitra_luar_negeri ?: 'Overseas Shipper' }}</strong>
                                ({{ $shipment->negara_mitra_luar_negeri ?: 'CN' }})
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center space-x-3 text-right">
                        <div>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ str_contains(strtolower($shipment->status_terakhir ?? ''), 'sppb') ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ $shipment->status_terakhir ?: 'Billing Terbit' }}
                            </span>
                            <div class="text-[10px] text-slate-400 font-mono mt-1">
                                {{ $shipment->created_at ? $shipment->created_at->diffForHumans() : 'Hari ini' }}
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-slate-400 text-xs italic">
                    Belum ada aktivitas dokumen kepabeanan terbaru.
                </div>
            @endforelse
        </div>
    </div>

    <!-- 6. MODAL: FORM TAMBAH KLIEN / PEMILIK BARANG BARU -->
    @canany(['crm.manage', 'document.create'])
    <div x-show="showCreateClientModal" 
         class="fixed inset-0 z-[200] overflow-y-auto flex items-end sm:items-center justify-center p-0 sm:p-4 md:p-6"
         role="dialog"
         aria-modal="true"
         x-cloak>
        <div x-show="showCreateClientModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-md"
             @click="showCreateClientModal = false"
             aria-hidden="true"></div>

        <div x-show="showCreateClientModal"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
             class="relative z-10 bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-3xl border border-slate-200/90 dark:border-white/10 shadow-2xl max-w-lg w-full p-6 text-xs max-h-[90vh] overflow-y-auto">
            
            <div class="sm:hidden w-12 h-1.5 bg-slate-200 dark:bg-white/20 rounded-full mx-auto -mt-2 mb-4"></div>

            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-white/10 mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Tambah Data Klien / Pemilik Barang</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Pendaftaran importir/eksportir ke direktori kontak & opsi pilihan dokumen kargo</p>
                </div>
                <button type="button" @click="showCreateClientModal = false" class="btn-apple-gray !p-2 rounded-full cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form action="{{ route('crm.clients.store') }}" method="POST" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Perusahaan / Importir <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: PT. JINDI MATERIAL INDONESIA" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white focus:ring-2 focus:ring-[#007AFF] focus:outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">NPWP Perusahaan (16 Digit)</label>
                        <input type="text" name="npwp" placeholder="Contoh: 75.296.714.1-041.000" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white focus:ring-2 focus:ring-[#007AFF] focus:outline-none font-mono">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">NIB (Nomor Induk Berusaha)</label>
                        <input type="text" name="nib" placeholder="Contoh: 1234567890123" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white focus:ring-2 focus:ring-[#007AFF] focus:outline-none font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama PIC (Contact Person)</label>
                        <input type="text" name="pic_name" placeholder="Contoh: Bpk. Hendra / Ibu Maya" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white focus:ring-2 focus:ring-[#007AFF] focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Nomor WhatsApp / HP</label>
                        <input type="text" name="phone" placeholder="Contoh: 081290008888" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white focus:ring-2 focus:ring-[#007AFF] focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Email Resmi Klien</label>
                        <input type="email" name="email" placeholder="import@perusahaan.co.id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white focus:ring-2 focus:ring-[#007AFF] focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Masa Berlaku Surat Kuasa PPJK</label>
                        <input type="date" name="surat_kuasa_expired_at" value="{{ now()->addYear()->format('Y-m-d') }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white focus:ring-2 focus:ring-[#007AFF] focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 dark:text-slate-300 mb-1">Alamat Domisili Perusahaan</label>
                    <textarea name="address" rows="2" placeholder="Jalan, Kawasan Industri, Gedung, Kota, Kode Pos" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white focus:ring-2 focus:ring-[#007AFF] focus:outline-none"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-white/10 flex items-center justify-end space-x-2">
                    <button type="button" @click="showCreateClientModal = false" class="btn-apple-gray cursor-pointer">Batal</button>
                    <button type="submit" class="btn-apple-filled cursor-pointer">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Data Klien</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endcanany

</div>
@endsection
