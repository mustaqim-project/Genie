@extends('layouts.app')

@section('title', 'WhatsApp Business (Meta WABA) - PT Agung Trans Logistics')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 pt-6 pb-16" x-data="{
    activeTab: '{{ $currentTab }}',
    openBroadcastModal: false,
    openTemplateModal: false,
    openTestModal: false,
    broadcastTitle: '',
    broadcastCategory: 'UTILITY',
    broadcastTarget: 'all',
    broadcastMessage: 'Halo Rekan Importir/Eksportir, berikut adalah pembaruan jadwal kapal langsung (Direct Service) rute Shanghai - Tanjung Priok dan informasi kesiapan respon dokumen kepabeanan CEISA 4.0.',
    broadcastMediaUrl: '',
    applyTemplate(body, category) {
        this.broadcastMessage = body;
        if (category) this.broadcastCategory = category;
    }
}">

    <!-- 1. BENTO HEADER -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div class="flex items-center space-x-3.5">
            <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-bold text-xl shadow-md shadow-emerald-600/20 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </div>
            <div>
                <div class="text-xs text-slate-500 font-medium mb-0.5">
                    <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a> / <span class="text-slate-800 font-semibold">WhatsApp Business</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">WhatsApp WABA Official Gateway</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Pusat siaran broadcast jadwal kapal, pengumuman regulasi pabean, dan template resmi Meta terverifikasi.</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('inbox.index', ['channel' => 'whatsapp']) }}"
                class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 shadow-xs flex items-center space-x-2 transition-all">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
                <span>Buka Live Chat</span>
            </a>

            <button type="button" @click="openTestModal = true"
                class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 shadow-xs flex items-center space-x-2 transition-all">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                <span>Uji Kirim WA</span>
            </button>

            <button type="button" @click="openBroadcastModal = true"
                class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm flex items-center space-x-2 transition-all active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                </svg>
                <span>Kirim Siaran (Blast)</span>
            </button>

            <a href="{{ route('setting.index', ['tab' => 'waba']) }}"
                class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 flex items-center space-x-1.5 transition-all">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                </svg>
                <span>Kelola Kredensial Meta &rarr;</span>
            </a>
        </div>
    </div>

    <!-- 2. BENTO STATS -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Meta WABA Status</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                    {{ $stats['quality_rating'] }}
                </span>
            </div>
            <div class="text-xl font-bold font-mono text-slate-800">{{ $stats['phone'] }}</div>
            <div class="mt-2 text-[11px] text-slate-500 flex items-center justify-between">
                <span>Tier: <strong class="text-slate-700">{{ $stats['messaging_limit'] }}</strong></span>
                <span class="text-emerald-600 font-semibold">Online (24h)</span>
            </div>
        </div>

        <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Pesan Siaran Terkirim</span>
                <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 tabular-nums">{{ number_format($stats['total_sent']) }}</div>
            <div class="mt-2 text-[11px] text-slate-500 flex items-center justify-between">
                <span>{{ $stats['total_campaigns'] }} Kampanye</span>
                <span class="text-emerald-600 font-bold">{{ $stats['success_rate'] }}% Berhasil</span>
            </div>
        </div>

        <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Target Kontak Klien</span>
                <div class="w-7 h-7 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-blue-600 tabular-nums">{{ $clients->count() }}</div>
            <div class="mt-2 text-[11px] text-slate-500 flex items-center justify-between">
                <span>Klien Terverifikasi</span>
                <span class="text-blue-600 font-semibold">Siap Terima Blast</span>
            </div>
        </div>

        <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Template Meta Resmi</span>
                <div class="w-7 h-7 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-purple-600 tabular-nums">{{ $templates->count() }}</div>
            <div class="mt-2 text-[11px] text-slate-500 flex items-center justify-between">
                <span>Notifikasi Otomatis</span>
                <span class="text-purple-600 font-semibold">Meta Approved</span>
            </div>
        </div>
    </div>

    <!-- 3. NAVIGATION TABS -->
    <div class="flex items-center space-x-1.5 p-1 bg-slate-200/60 rounded-2xl mb-6 overflow-x-auto">
        <button type="button" @click="activeTab = 'broadcasts'"
            :class="activeTab === 'broadcasts' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
            class="px-4 py-2 rounded-xl text-xs sm:text-sm transition-all whitespace-nowrap flex items-center space-x-2">
            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
            </svg>
            <span>Riwayat Siaran (Broadcast)</span>
        </button>

        <button type="button" @click="activeTab = 'templates'"
            :class="activeTab === 'templates' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
            class="px-4 py-2 rounded-xl text-xs sm:text-sm transition-all whitespace-nowrap flex items-center space-x-2">
            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span>Template Pesan Resmi Meta</span>
        </button>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 1: RIWAYAT SIARAN (BROADCASTS)                             -->
    <!-- ============================================================== -->
    <div x-show="activeTab === 'broadcasts'" class="space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Riwayat Kampanye Siaran WhatsApp</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Daftar blast massal jadwal kapal, tarif ekspor-impor, dan pengumuman kepabeanan resmi.</p>
                </div>
                <button type="button" @click="openBroadcastModal = true"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs flex items-center space-x-1.5 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Buat Siaran Baru</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="bg-slate-50/80 text-slate-500 text-[11px] uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3.5 font-semibold">Judul Kampanye</th>
                            <th class="px-5 py-3.5 font-semibold">Kategori &amp; Target</th>
                            <th class="px-5 py-3.5 font-semibold">Penerima &amp; Terkirim</th>
                            <th class="px-5 py-3.5 font-semibold">Status Pengiriman</th>
                            <th class="px-5 py-3.5 font-semibold">Waktu Kirim</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($broadcasts as $b)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-5 py-4">
                                    <div class="font-bold text-slate-900 text-xs">{{ $b->title }}</div>
                                    <p class="text-slate-500 text-[11px] line-clamp-1 mt-0.5">{{ $b->message }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                                        {{ $b->category }}
                                    </span>
                                    <span class="text-[11px] text-slate-400 block mt-0.5">Target: {{ strtoupper($b->target_type) }}</span>
                                </td>
                                <td class="px-5 py-4 font-mono">
                                    <span class="font-bold text-emerald-600">{{ $b->total_sent }}</span> / {{ $b->total_recipients }}
                                    <span class="text-[10px] text-slate-400 block">({{ $b->success_rate }}% sukses)</span>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        {{ strtoupper($b->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 font-mono text-slate-500 text-[11px]">
                                    {{ $b->sent_at ? $b->sent_at->format('d M Y H:i') : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-slate-400">Belum ada riwayat siaran broadcast.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $broadcasts->links() }}
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 2: TEMPLATE RESMI META WABA                                -->
    <!-- ============================================================== -->
    <div x-show="activeTab === 'templates'" class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <div>
                <h3 class="font-bold text-slate-900 text-sm">Template Pesan WhatsApp Resmi (Meta Approved)</h3>
                <p class="text-xs text-slate-500 mt-0.5">Template notifikasi pabean dengan parameter dinamis (Nomor Aju PIB/PEB, SPPB, NPE, Kode Billing MPN).</p>
            </div>
            <button type="button" @click="openTemplateModal = true"
                class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold rounded-xl shadow-xs flex items-center space-x-1.5 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Daftarkan Template Baru</span>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($templates as $tmpl)
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-purple-100 text-purple-800">
                                {{ $tmpl->category ?: 'UTILITY' }}
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 flex items-center space-x-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span>{{ $tmpl->meta_status ?: 'APPROVED' }}</span>
                            </span>
                        </div>
                        <h4 class="font-bold text-slate-900 text-xs mb-1 font-mono">{{ $tmpl->name }}</h4>
                        <div class="text-[11px] font-semibold text-slate-700 mb-2">{{ $tmpl->header_text }}</div>

                        <!-- WhatsApp Bubble Mockup -->
                        <div class="p-3.5 rounded-xl bg-emerald-50/60 border border-emerald-100 text-slate-800 text-[11px] leading-relaxed whitespace-pre-line font-sans">
                            {{ $tmpl->body }}
                            @if($tmpl->footer_text)
                                <div class="text-[9px] text-slate-400 mt-2 border-t border-emerald-200/50 pt-1 font-mono">
                                    {{ $tmpl->footer_text }}
                                </div>
                            @endif
                        </div>

                        <!-- Buttons Mockup -->
                        @if(!empty($tmpl->buttons))
                            <div class="mt-2 space-y-1">
                                @foreach($tmpl->buttons as $btn)
                                    <div class="p-1.5 rounded-lg bg-white border border-slate-200 text-center text-[10px] font-bold text-blue-600 shadow-2xs">
                                        {{ $btn['text'] ?? 'Aksi' }}
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                        <span>Bahasa: {{ strtoupper($tmpl->language) }}</span>
                        <span class="text-emerald-600 font-semibold">Siap Digunakan</span>
                    </div>
                </div>
            @empty
                <div class="col-span-3 p-12 text-center text-slate-400 text-xs bg-white rounded-2xl border border-slate-200">
                    Belum ada template terdaftar. Klik tombol di atas untuk mendaftarkan template resmi baru.
                </div>
            @endforelse
        </div>
    </div>

    <!-- MODAL: UJI KIRIM PESAN -->
    <div x-show="openTestModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
        <div @click.away="openTestModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-bold text-slate-900 text-sm">Uji Kirim Pesan WhatsApp Resmi</h3>
                <button @click="openTestModal = false" class="text-slate-400 hover:text-slate-600 text-sm font-bold">✕</button>
            </div>

            <form action="{{ route('whatsapp.test') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nomor WhatsApp Tujuan (Format: 08xx / 628xx)</label>
                    <input type="text" name="test_phone" required placeholder="Contoh: 081234567890"
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Pesan Uji Coba</label>
                    <textarea name="test_message" rows="3" required
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500">Halo, ini adalah pesan uji coba resmi dari sistem ERP PT Agung Trans Logistics via Meta WABA Cloud API.</textarea>
                </div>

                <div class="pt-2 flex justify-end space-x-2">
                    <button type="button" @click="openTestModal = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 text-white font-bold hover:bg-emerald-700">Kirim Sekarang</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: BUAT SIARAN MASSAL (2-Column Architecture with Live WA Preview) -->
    <div x-show="openBroadcastModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
        <div @click.away="openBroadcastModal = false" class="bg-white rounded-3xl max-w-4xl w-full p-6 sm:p-8 shadow-2xl border border-slate-200 space-y-6 my-4 max-h-[92vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-bold shadow-xs">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.97.53 1.951.815 2.796.815 3.183 0 5.767-2.586 5.768-5.766 0-3.18-2.585-5.766-5.768-5.766zm3.376 8.21c-.14.394-.71.724-1.015.768-.29.043-.664.068-2.148-.546-1.898-.786-3.119-2.73-3.214-2.857-.095-.127-.771-1.026-.771-1.955 0-.928.487-1.385.66-1.574.173-.189.378-.236.504-.236.126 0 .252.001.362.006.116.006.27-.044.423.323.158.378.539 1.314.587 1.408.047.095.079.205.016.331-.063.126-.095.205-.189.315-.094.11-.198.246-.283.33-.095.095-.194.198-.083.388.11.19.49 8.08 1.05 1.306.721.643 1.33.842 1.519.936.19.095.3.079.41-.047.11-.126.473-.551.6-.74.126-.19.252-.158.425-.095.174.063 1.103.52 1.292.615.19.095.316.142.363.22.047.079.047.457-.094.851z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900">Buat Kampanye Siaran WhatsApp (Broadcast Blast)</h3>
                        <p class="text-xs text-slate-500">Kirim jadwal pelayaran, tarif kontainer, atau surat edaran pabean resmi ke database importir &amp; eksportir.</p>
                    </div>
                </div>
                <button @click="openBroadcastModal = false" class="text-slate-400 hover:text-slate-600 text-sm font-bold">✕</button>
            </div>

            <form action="{{ route('whatsapp.broadcasts.store') }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                    <!-- LEFT COLUMN: CONTROLS (Col-span 7) -->
                    <div class="lg:col-span-7 space-y-4">
                        <div>
                            <label class="block font-bold text-slate-700 text-xs mb-1">Judul Kampanye Siaran <span class="text-rose-500">*</span></label>
                            <input type="text" name="title" required x-model="broadcastTitle" placeholder="Contoh: Update Sailing Schedule Rute Shanghai Week 44"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-700 text-xs mb-1">Kategori Meta</label>
                                <select name="category" x-model="broadcastCategory" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 bg-white">
                                    <option value="UTILITY">UTILITY (Jadwal &amp; Pabean)</option>
                                    <option value="MARKETING">MARKETING (Promosi &amp; Tarif)</option>
                                    <option value="ANNOUNCEMENT">ANNOUNCEMENT (Edaran)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 text-xs mb-1">Target Segmen Klien</label>
                                <select name="target_type" x-model="broadcastTarget" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 bg-white">
                                    <option value="all">Semua Klien Terdaftar ({{ $clients->count() }})</option>
                                    <option value="importers">Klien Importir Saja</option>
                                    <option value="exporters">Klien Eksportir Saja</option>
                                </select>
                            </div>
                        </div>

                        <!-- Quick Template Inserter Pills -->
                        @if($templates->isNotEmpty())
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Gunakan Template Resmi Meta:</label>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($templates->take(4) as $t)
                                        <button type="button" 
                                            @click="applyTemplate(@js($t->body), '{{ $t->category }}')"
                                            class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-[10px] font-mono font-semibold border border-emerald-200 transition-colors">
                                            + {{ $t->name }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div>
                            <label class="block font-bold text-slate-700 text-xs mb-1">Isi Pesan WhatsApp WABA <span class="text-rose-500">*</span></label>
                            <textarea name="message" rows="5" required x-model="broadcastMessage"
                                placeholder="Tuliskan isi pesan siaran resmi..."
                                class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs leading-relaxed focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 text-xs mb-1">Lampiran Media Brosur / File Dokumen (URL Opsional)</label>
                            <input type="url" name="media_url" x-model="broadcastMediaUrl" placeholder="https://images.unsplash.com/..."
                                class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-mono focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: LIVE WHATSAPP BUBBLE PREVIEW (Col-span 5) -->
                    <div class="lg:col-span-5 bg-[#EFEAE2] p-4 rounded-3xl border border-slate-200 space-y-3 sticky top-2">
                        <div class="flex items-center justify-between text-xs font-bold text-slate-700">
                            <span>Pratinjau Pesan Chat WhatsApp</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Live Bubble</span>
                        </div>

                        <!-- Chat Bubble Card Mockup -->
                        <div class="bg-white rounded-2xl rounded-tr-none p-3.5 shadow-sm border border-black/5 text-xs text-slate-800 space-y-2">
                            <!-- Official Verified Sender -->
                            <div class="flex items-center space-x-1.5 pb-2 border-b border-slate-100 text-[11px] font-bold text-emerald-700">
                                <svg class="w-3.5 h-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span>PT AGUNG TRANS LOGISTICS</span>
                            </div>

                            <!-- Media Attachment Preview -->
                            <div x-show="broadcastMediaUrl" class="rounded-xl overflow-hidden max-h-36 bg-slate-100">
                                <img :src="broadcastMediaUrl" alt="Media Attachment" class="w-full h-36 object-cover">
                            </div>

                            <!-- Body Text -->
                            <p class="whitespace-pre-line leading-relaxed text-[11px]" x-text="broadcastMessage"></p>

                            <!-- Footer & Double Blue Ticks -->
                            <div class="flex items-center justify-end space-x-1 text-[10px] text-slate-400 font-mono pt-1">
                                <span>{{ now()->format('H:i') }}</span>
                                <svg class="w-3.5 h-3.5 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7m-14 6l4 4L19 7"/>
                                </svg>
                            </div>
                        </div>

                        <!-- Recipient Counter Card -->
                        <div class="p-3 bg-white/80 backdrop-blur-sm rounded-2xl border border-slate-200 text-center">
                            <div class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold">Estimasi Penerima Siaran</div>
                            <div class="text-sm font-bold text-emerald-700 font-mono mt-0.5">
                                {{ $clients->count() }} Kontak Importir/Eksportir Terdaftar
                            </div>
                            <div class="text-[10px] text-slate-400 mt-1">Single-System: Kuota Unlimited PT Agung Trans Logistics</div>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                    <button type="button" @click="openBroadcastModal = false" class="px-5 py-2.5 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs hover:bg-slate-200 transition-all">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all flex items-center space-x-1.5">
                        <span>Luncurkan Siaran Sekarang</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: DAFTARKAN TEMPLATE BARU -->
    <div x-show="openTemplateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
        <div @click.away="openTemplateModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-bold text-slate-900 text-sm">Daftarkan Template Pesan Meta Baru</h3>
                <button @click="openTemplateModal = false" class="text-slate-400 hover:text-slate-600 text-sm font-bold">✕</button>
            </div>

            <form action="{{ route('whatsapp.templates.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nama Template (Huruf kecil & underscore)</label>
                    <input type="text" name="name" required placeholder="contoh: sppb_fast_track_notice"
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono focus:ring-2 focus:ring-purple-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Kategori Meta</label>
                        <select name="category" class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-purple-500">
                            <option value="UTILITY">UTILITY (Pemberitahuan Pabean)</option>
                            <option value="MARKETING">MARKETING (Promosi & Penawaran)</option>
                            <option value="AUTHENTICATION">AUTHENTICATION (OTP & Keamanan)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Header Pesan (Opsional)</label>
                        <input type="text" name="header_text" placeholder="Contoh: Surat Jalan Terbit"
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Isi Body Pesan (Gunakan @{{1}}, @{{2}} untuk variabel)</label>
                    <textarea name="body" rows="4" required placeholder="Halo @{{1}}, shipment Anda dengan No Aju @{{2}} telah..."
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-purple-500"></textarea>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Footer Teks</label>
                    <input type="text" name="footer_text" value="PT Agung Trans Logistics"
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-purple-500">
                </div>

                <div class="pt-2 flex justify-end space-x-2">
                    <button type="button" @click="openTemplateModal = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-purple-600 text-white font-bold hover:bg-purple-700">Daftarkan Template</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
