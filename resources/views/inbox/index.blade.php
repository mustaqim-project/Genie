@extends('layouts.app')

@section('title', 'Kotak Masuk Terpadu (Omnichannel Inbox) - PT Agung Trans Logistics')

@section('content')
<div class="max-w-[1360px] mx-auto px-4 sm:px-6 space-y-6 pt-4 pb-28 lg:pb-12" x-data="{
    channelFilter: '{{ $channelFilter }}',
    statusFilter: '{{ $statusFilter }}',
    selectedType: 'wa',
    selectedWaId: '{{ $waConversations->first()?->id ?? '' }}',
    selectedSocialId: '{{ $socialInteractions->first()?->id ?? '' }}'
}">

    <!-- 1. BENTO HEADER -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div class="flex items-center space-x-3.5">
            <div class="w-12 h-12 rounded-2xl bg-slate-900 text-white flex items-center justify-center font-bold text-xl shadow-md shadow-slate-900/10 shrink-0">
                <svg class="w-6 h-6 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
            </div>
            <div>
                <div class="text-xs text-slate-500 font-medium mb-0.5">
                    <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a> / <span class="text-slate-800 font-semibold">Kotak Masuk Terpadu</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Kotak Masuk Terpadu (Omnichannel Inbox)</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Satu pintu respon komunikasi: Chat pelanggan WhatsApp WABA dan pesan/komentar media sosial resmi.</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-semibold flex items-center space-x-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Live Omnichannel Stream Active</span>
            </span>
        </div>
    </div>

    <!-- 2. CHANNEL & STATUS FILTERS -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-2 bg-slate-200/60 rounded-2xl mb-6">
        <!-- Platform Filter Pills -->
        <div class="flex items-center space-x-1 overflow-x-auto">
            <a href="{{ route('inbox.index', ['channel' => 'all', 'status' => $statusFilter]) }}"
                class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap {{ $channelFilter === 'all' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                Semua Kanal ({{ $stats['total_inbox'] }})
            </a>
            <a href="{{ route('inbox.index', ['channel' => 'whatsapp', 'status' => $statusFilter]) }}"
                class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap {{ $channelFilter === 'whatsapp' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                WhatsApp WABA ({{ $stats['active_wa_chats'] }})
            </a>
            <a href="{{ route('inbox.index', ['channel' => 'linkedin', 'status' => $statusFilter]) }}"
                class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap {{ $channelFilter === 'linkedin' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                LinkedIn
            </a>
            <a href="{{ route('inbox.index', ['channel' => 'instagram', 'status' => $statusFilter]) }}"
                class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap {{ $channelFilter === 'instagram' ? 'bg-pink-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                Instagram
            </a>
            <a href="{{ route('inbox.index', ['channel' => 'facebook', 'status' => $statusFilter]) }}"
                class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all whitespace-nowrap {{ $channelFilter === 'facebook' ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                Facebook
            </a>
        </div>

        <!-- Status Filter Pills -->
        <div class="flex items-center space-x-1 self-end sm:self-auto">
            <a href="{{ route('inbox.index', ['channel' => $channelFilter, 'status' => 'all']) }}"
                class="px-3 py-1 rounded-lg text-xs font-medium {{ $statusFilter === 'all' ? 'bg-white text-slate-900 font-bold shadow-2xs' : 'text-slate-500' }}">
                Semua Status
            </a>
            <a href="{{ route('inbox.index', ['channel' => $channelFilter, 'status' => 'unread']) }}"
                class="px-3 py-1 rounded-lg text-xs font-medium {{ $statusFilter === 'unread' ? 'bg-rose-600 text-white font-bold shadow-2xs' : 'text-slate-500' }}">
                Perlu Dibalas ({{ $stats['unread_social'] }})
            </a>
        </div>
    </div>

    <!-- 3. DUAL-COLUMN INBOX STREAM -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 min-h-[600px]">
        <!-- Col Kiri: Master List (Chat WhatsApp & Interaksi Medsos) -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/80 shadow-xs flex flex-col overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Daftar Percakapan &amp; Interaksi</span>
                <span class="text-[11px] font-semibold text-slate-500">{{ $stats['total_inbox'] }} Percakapan Masuk</span>
            </div>

            <div class="divide-y divide-slate-100 overflow-y-auto flex-1 max-h-[560px]">
                <!-- Stream WhatsApp Conversations -->
                @if($channelFilter === 'all' || $channelFilter === 'whatsapp')
                    @foreach($waConversations as $conv)
                        <button type="button" @click="selectedType = 'wa'; selectedWaId = '{{ $conv->id }}'"
                            :class="selectedType === 'wa' && selectedWaId === '{{ $conv->id }}' ? 'bg-emerald-50/70 border-l-4 border-emerald-500' : 'hover:bg-slate-50'"
                            class="w-full p-4 text-left transition-colors flex items-start space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white font-bold flex items-center justify-center text-xs shrink-0 shadow-xs">
                                WA
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-900 text-xs truncate">{{ $conv->contact?->name }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $conv->last_message_at ? $conv->last_message_at->diffForHumans(null, true) : '-' }}</span>
                                </div>
                                <p class="text-[11px] text-slate-500 truncate mt-0.5">
                                    {{ $conv->messages->last()?->body ?: 'Percakapan baru' }}
                                </p>
                                <div class="flex items-center gap-1.5 mt-1.5">
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800">
                                        WhatsApp WABA
                                    </span>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $conv->contact?->client?->code ?: 'KLIEN' }}</span>
                                </div>
                            </div>
                        </button>
                    @endforeach
                @endif

                <!-- Stream Social Media Interactions -->
                @if($channelFilter !== 'whatsapp')
                    @foreach($socialInteractions as $item)
                        <button type="button" @click="selectedType = 'social'; selectedSocialId = '{{ $item->id }}'"
                            :class="selectedType === 'social' && selectedSocialId === '{{ $item->id }}' ? 'bg-blue-50/70 border-l-4 border-blue-500' : 'hover:bg-slate-50'"
                            class="w-full p-4 text-left transition-colors flex items-start space-x-3">
                            <div class="w-10 h-10 rounded-xl text-white font-bold flex items-center justify-center text-xs shrink-0 shadow-xs
                                {{ $item->platform === 'linkedin' ? 'bg-blue-600' : '' }}
                                {{ $item->platform === 'instagram' ? 'bg-pink-600' : '' }}
                                {{ $item->platform === 'facebook' ? 'bg-indigo-600' : '' }}
                                {{ $item->platform === 'tiktok' ? 'bg-slate-900' : '' }}">
                                {{ strtoupper(substr($item->platform, 0, 2)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-900 text-xs truncate">{{ $item->from_name }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $item->created_time ? $item->created_time->diffForHumans(null, true) : '-' }}</span>
                                </div>
                                <p class="text-[11px] text-slate-600 truncate mt-0.5">"{{ $item->message }}"</p>
                                <div class="flex items-center gap-1.5 mt-1.5">
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase
                                        {{ $item->platform === 'linkedin' ? 'bg-blue-100 text-blue-800' : 'bg-pink-100 text-pink-800' }}">
                                        {{ $item->platform }} • {{ strtoupper($item->type) }}
                                    </span>
                                    @if($item->status === 'unread')
                                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                    @else
                                        <span class="text-[10px] text-emerald-600 font-medium">Sudah dibalas</span>
                                    @endif
                                </div>
                            </div>
                        </button>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Col Kanan: Detail Percakapan & Respon Langsung -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/80 shadow-xs flex flex-col overflow-hidden">
            <!-- Mode 1: WhatsApp Conversation Active -->
            <div x-show="selectedType === 'wa'" class="flex-1 flex flex-col h-full">
                @php
                    $activeConv = $waConversations->first();
                @endphp
                <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shadow-xs">
                            WA
                        </div>
                        <div>
                            <div class="font-bold text-slate-900 text-sm">{{ $activeConv?->contact?->name ?: 'Klien WhatsApp' }}</div>
                            <div class="text-[11px] text-slate-500">No: {{ $activeConv?->contact?->phone ?: '+62 812-9000-8888' }} • Meta WABA Session Active</div>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">WhatsApp Resmi</span>
                </div>

                <div class="p-5 flex-1 overflow-y-auto space-y-3.5 bg-slate-50/30 max-h-[440px]">
                    @if($activeConv && $activeConv->messages->isNotEmpty())
                        @foreach($activeConv->messages as $msg)
                            @if($msg->direction === 'INBOUND')
                                <div class="flex items-start space-x-2.5 max-w-[80%]">
                                    <div class="w-7 h-7 rounded-full bg-slate-200 text-slate-700 font-bold text-[10px] flex items-center justify-center shrink-0">
                                        KL
                                    </div>
                                    <div class="p-3.5 rounded-2xl rounded-tl-sm bg-white border border-slate-200 text-xs shadow-xs text-slate-800">
                                        <p class="whitespace-pre-line leading-relaxed">{{ $msg->body }}</p>
                                        <span class="text-[9px] text-slate-400 font-mono mt-1.5 block text-right">
                                            {{ $msg->created_at ? $msg->created_at->format('H:i') : '' }}
                                        </span>
                                    </div>
                                </div>
                            @else
                                <div class="flex items-start space-x-2.5 max-w-[80%] ml-auto justify-end">
                                    <div class="p-3.5 rounded-2xl rounded-tr-sm bg-emerald-600 text-white text-xs shadow-xs">
                                        <p class="whitespace-pre-line leading-relaxed">{{ $msg->body }}</p>
                                        <div class="flex items-center justify-end space-x-1 mt-1.5 text-[9px] text-emerald-200 font-mono">
                                            <span>{{ $msg->created_at ? $msg->created_at->format('H:i') : '' }}</span>
                                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7m-14 6l4 4L19 7"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="w-7 h-7 rounded-full bg-emerald-700 text-white font-bold text-[10px] flex items-center justify-center shrink-0 shadow-xs">
                                        AG
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>

                <!-- Input Balasan WhatsApp -->
                <div class="p-3.5 border-t border-slate-100 bg-white">
                    <form action="{{ route('inbox.whatsapp.send') }}" method="POST" class="flex items-center space-x-2">
                        @csrf
                        <input type="hidden" name="conversation_id" :value="selectedWaId">
                        <input type="text" name="message" required placeholder="Ketik balasan resmi WhatsApp perusahaan..."
                            class="flex-1 px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <button type="submit"
                            class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center space-x-1.5 shrink-0">
                            <span>Kirim</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Mode 2: Social Media Interaction Active -->
            <div x-show="selectedType === 'social'" class="flex-1 p-6 flex flex-col justify-between">
                @php
                    $firstSocial = $socialInteractions->first();
                @endphp
                @if($firstSocial)
                    <div class="space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                    {{ $firstSocial->platform === 'linkedin' ? 'bg-blue-100 text-blue-800' : 'bg-pink-100 text-pink-800' }}">
                                    {{ $firstSocial->platform }} • {{ strtoupper($firstSocial->type) }}
                                </span>
                                <h3 class="font-bold text-slate-900 text-base mt-2">{{ $firstSocial->from_name }}</h3>
                                <div class="text-xs text-slate-400">{{ $firstSocial->from_handle }}</div>
                            </div>
                            <span class="text-xs text-slate-400 font-mono">{{ $firstSocial->created_time ? $firstSocial->created_time->format('d M Y H:i') : '-' }}</span>
                        </div>

                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-800 leading-relaxed">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Pertanyaan / Pesan Masuk:</span>
                            "{{ $firstSocial->message }}"
                        </div>

                        @if($firstSocial->reply_message)
                            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 leading-relaxed">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 block mb-1">Tanggapan Resmi yang Terkirim:</span>
                                {{ $firstSocial->reply_message }}
                            </div>
                        @endif
                    </div>

                    <form action="{{ route('inbox.social.reply', $firstSocial->id) }}" method="POST" class="pt-4 border-t border-slate-100 space-y-3">
                        @csrf
                        <label class="block text-xs font-bold text-slate-800">Tulis Balasan Langsung ke {{ ucfirst($firstSocial->platform) }}</label>
                        <div class="flex items-center space-x-2">
                            <input type="text" name="reply_message" required placeholder="Tuliskan respon resmi yang ramah dan profesional..."
                                class="flex-1 px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <button type="submit"
                                class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-xs transition-all shrink-0">
                                Kirim Balasan
                            </button>
                        </div>
                    </form>
                @else
                    <div class="p-12 text-center text-slate-400 text-xs">
                        Pilih interaksi di kolom kiri untuk meninjau dan membalas.
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection
