@extends('layouts.app')

@section('title', 'Media Sosial Multi-Channel - PT Agung Trans Logistics')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 pt-6 pb-16" x-data="socialComposerManager()">

    <!-- Alerts -->
    @if(session('success'))
        <div class="mb-5 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-800 text-xs sm:text-sm font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center space-x-2.5">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
            <button @click="$el.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900 font-bold text-xs">✕</button>
        </div>
    @endif

    @if(session('error') || $errors->any())
        <div class="mb-5 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-800 text-xs font-semibold shadow-xs">
            <div>{{ session('error') ?? 'Terjadi kesalahan saat memproses data postingan:' }}</div>
            @if($errors->any())
                <ul class="list-disc list-inside mt-1 space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

    <!-- 1. BENTO HEADER -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div class="flex items-center space-x-3.5">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-purple-600 text-white flex items-center justify-center font-bold text-xl shadow-md shadow-indigo-600/20 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                </svg>
            </div>
            <div>
                <div class="text-xs text-slate-500 font-medium mb-0.5">
                    <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a> / <span class="text-slate-800 font-semibold">Media Sosial</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Media Sosial Multi-Channel &amp; Feed Komposer</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Publikasi feed terpadu: LinkedIn B2B, Instagram Business, Facebook Page, dan TikTok resmi PT Agung Trans Logistics.</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('inbox.index') }}"
                class="px-3.5 py-2.5 rounded-xl text-xs font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 shadow-xs flex items-center space-x-2 transition-all">
                <svg class="w-4 h-4 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/>
                </svg>
                <span>Kotak Masuk Medsos</span>
            </a>

            <button type="button" @click="openComposerModal = true"
                class="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 hover:opacity-95 shadow-sm flex items-center space-x-2 transition-all active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Buat Postingan Baru</span>
            </button>

            <a href="{{ route('setting.index', ['tab' => 'social']) }}"
                class="px-3.5 py-2.5 rounded-xl text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 flex items-center space-x-1.5 transition-all">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                </svg>
                <span>Kelola Akun &rarr;</span>
            </a>
        </div>
    </div>

    <!-- 2. BENTO STATS -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="p-4 sm:p-5 rounded-3xl bg-white border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Pengikut (Audience)</span>
            <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 tabular-nums mt-1">{{ number_format($stats['total_followers']) }}</div>
            <div class="mt-2 text-[11px] text-slate-500">Lintas Kanal Resmi Perusahaan</div>
        </div>

        <div class="p-4 sm:p-5 rounded-3xl bg-white border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Kanal Resmi Terhubung</span>
            <div class="text-2xl sm:text-3xl font-extrabold text-blue-600 tabular-nums mt-1">{{ $stats['total_accounts'] }}</div>
            <div class="mt-2 text-[11px] text-slate-500">LinkedIn, IG, FB, TikTok, YT</div>
        </div>

        <div class="p-4 sm:p-5 rounded-3xl bg-white border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Postingan Terbit</span>
            <div class="text-2xl sm:text-3xl font-extrabold text-emerald-600 tabular-nums mt-1">{{ $stats['published_posts'] }}</div>
            <div class="mt-2 text-[11px] text-slate-500">Edukasi Kepabeanan &amp; Jadwal</div>
        </div>

        <div class="p-4 sm:p-5 rounded-3xl bg-white border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Postingan Terjadwal</span>
            <div class="text-2xl sm:text-3xl font-extrabold text-purple-600 tabular-nums mt-1">{{ $stats['scheduled_posts'] }}</div>
            <div class="mt-2 text-[11px] text-slate-500">Siap Tayang Otomatis</div>
        </div>
    </div>

    <!-- 3. FILTER BAR -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-3.5 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
        <div class="flex flex-wrap items-center gap-1.5">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mr-1">Status:</span>
            <a href="{{ route('social-media.index', ['status' => 'all', 'platform' => $platform]) }}"
                class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all {{ $status === 'all' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Semua ({{ $stats['total_posts'] }})
            </a>
            <a href="{{ route('social-media.index', ['status' => 'published', 'platform' => $platform]) }}"
                class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all {{ $status === 'published' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Terbit ({{ $stats['published_posts'] }})
            </a>
            <a href="{{ route('social-media.index', ['status' => 'scheduled', 'platform' => $platform]) }}"
                class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all {{ $status === 'scheduled' ? 'bg-purple-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Terjadwal ({{ $stats['scheduled_posts'] }})
            </a>
        </div>

        <div class="flex items-center space-x-2">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Kanal:</span>
            <select onchange="window.location.href=this.value" class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-medium bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="{{ route('social-media.index', ['status' => $status, 'platform' => 'all']) }}" {{ $platform === 'all' ? 'selected' : '' }}>Semua Kanal</option>
                <option value="{{ route('social-media.index', ['status' => $status, 'platform' => 'linkedin']) }}" {{ $platform === 'linkedin' ? 'selected' : '' }}>LinkedIn</option>
                <option value="{{ route('social-media.index', ['status' => $status, 'platform' => 'instagram']) }}" {{ $platform === 'instagram' ? 'selected' : '' }}>Instagram</option>
                <option value="{{ route('social-media.index', ['status' => $status, 'platform' => 'facebook']) }}" {{ $platform === 'facebook' ? 'selected' : '' }}>Facebook</option>
                <option value="{{ route('social-media.index', ['status' => $status, 'platform' => 'tiktok']) }}" {{ $platform === 'tiktok' ? 'selected' : '' }}>TikTok</option>
            </select>
        </div>
    </div>

    <!-- 4. GRID POSTINGAN TERBIT & TERJADWAL -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden mb-8">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="font-bold text-slate-900 text-sm sm:text-base">Arsip Feed &amp; Konten Terjadwal</h3>
                <p class="text-xs text-slate-500 mt-0.5">Kelola penayangan konten jadwal pelayaran kapal, portofolio cargo handling, dan informasi bea cukai.</p>
            </div>
            <button type="button" @click="openComposerModal = true"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs flex items-center space-x-1.5 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Buat Postingan</span>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 p-5">
            @forelse($posts as $post)
                <div class="rounded-3xl bg-white border border-slate-200/90 p-4 shadow-xs hover:border-slate-300 transition-all flex flex-col justify-between space-y-3">
                    <div>
                        <!-- Header Card -->
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-2.5 py-1 rounded-xl text-[10px] font-bold uppercase
                                {{ $post->platform === 'linkedin' ? 'bg-blue-50 text-blue-700 border border-blue-200' : '' }}
                                {{ $post->platform === 'instagram' ? 'bg-pink-50 text-pink-700 border border-pink-200' : '' }}
                                {{ $post->platform === 'facebook' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : '' }}
                                {{ $post->platform === 'tiktok' ? 'bg-slate-900 text-white' : '' }}
                                {{ !in_array($post->platform, ['linkedin','instagram','facebook','tiktok']) ? 'bg-slate-100 text-slate-700' : '' }}">
                                {{ $post->platform }}
                            </span>

                            @if($post->status === 'published')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 flex items-center space-x-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>Terbit</span>
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 flex items-center space-x-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-purple-500 animate-pulse"></span>
                                    <span>Terjadwal</span>
                                </span>
                            @endif
                        </div>

                        <!-- Media Preview jika ada -->
                        @if(!empty($post->media_urls) && isset($post->media_urls[0]))
                            <div class="rounded-2xl overflow-hidden mb-3 bg-slate-100 border border-slate-100 aspect-video flex items-center justify-center">
                                @if(str_contains(strtolower($post->media_urls[0]), '.mp4') || str_contains(strtolower($post->media_urls[0]), '.mov'))
                                    <video src="{{ $post->media_urls[0] }}" controls class="w-full h-full object-cover"></video>
                                @else
                                    <img src="{{ $post->media_urls[0] }}" alt="Media" class="w-full h-full object-cover">
                                @endif
                            </div>
                        @endif

                        <!-- Caption Content -->
                        <p class="text-xs text-slate-800 line-clamp-3 leading-relaxed whitespace-pre-line">{{ $post->content }}</p>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                        <div class="font-mono">
                            {{ $post->published_at ? $post->published_at->format('d M Y H:i') : ($post->scheduled_at ? $post->scheduled_at->format('d M H:i') : '-') }}
                        </div>
                        
                        <!-- Actions Button -->
                        <div class="flex items-center space-x-2">
                            @if($post->status === 'scheduled')
                                <form action="{{ route('social-media.posts.publish-now', $post->id) }}" method="POST" onsubmit="return confirm('Publikasikan postingan ini ke kanal sekarang juga?')">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold text-[10px] transition-colors">
                                        Tayangkan Sekarang
                                    </button>
                                </form>
                            @endif

                            <form action="{{ route('social-media.posts.destroy', $post->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus postingan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-rose-600 transition-colors p-1" title="Hapus Postingan">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 p-12 text-center text-slate-400 text-xs italic">
                    Belum ada konten publikasi yang dibuat. Klik tombol di atas untuk membuat postingan baru.
                </div>
            @endforelse
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $posts->links() }}
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- 5. UNIFIED COMPOSER MODAL SHEET (Apple HIG Bento Design XXL)   -->
    <!-- Mengadopsi Penuh posts.blade.php cooca_core (Drag & Drop, Preview) -->
    <!-- ============================================================== -->
    <div x-show="openComposerModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-3 sm:p-5 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="openComposerModal = false"
            class="relative w-full max-w-6xl rounded-3xl bg-white border border-slate-200 shadow-2xl p-6 sm:p-8 space-y-6 my-4 max-h-[94vh] overflow-y-auto">

            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center font-bold shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900">Komposer Multi-Channel Media Sosial Resmi</h3>
                        <p class="text-xs text-slate-500">Tulis caption, pilih multi-kanal, unggah media drag &amp; drop, dan nikmati live interactive smartphone preview.</p>
                    </div>
                </div>
                <button type="button" @click="openComposerModal = false" class="text-slate-400 hover:text-slate-600 font-bold text-sm">✕</button>
            </div>

            <!-- Form Content with Bento 2-Column Architecture -->
            <form action="{{ route('social-media.posts.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <input type="hidden" name="media_format" :value="selectedFormat">
                <input type="hidden" name="aspect_ratio" :value="aspectRatio">
                <input type="hidden" name="media_fit" :value="mediaFitMode">

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    <!-- LEFT COLUMN: FORM CONTROLS (Col-span 7) -->
                    <div class="lg:col-span-7 space-y-5">
                        <!-- 1. Multi-Channel Target Selector (Checkboxes) -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-xs font-bold text-slate-800">
                                    Target Saluran Penyiaran <span class="text-rose-500">*</span>
                                </label>
                                <button type="button" @click="toggleSelectAll()" class="text-[11px] font-bold text-blue-600 hover:underline">
                                    <span x-text="selectedAccounts.length === allAccountIds.length ? 'Batal Pilih Semua' : 'Pilih Semua Saluran'"></span>
                                </button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                @forelse($accounts as $acc)
                                    <label class="p-3 rounded-2xl border cursor-pointer transition-all flex items-center justify-between"
                                        :class="selectedAccounts.includes('{{ $acc->id }}') ? 'bg-blue-50/70 border-blue-500 text-blue-900 font-bold' : 'border-slate-200 text-slate-700 hover:bg-slate-50'">
                                        <div class="flex items-center space-x-2.5 min-w-0">
                                            <input type="checkbox" name="target_accounts[]" value="{{ $acc->id }}"
                                                x-model="selectedAccounts" class="rounded text-blue-600 focus:ring-blue-500">
                                            <div class="min-w-0">
                                                <div class="text-xs truncate">{{ $acc->account_name }}</div>
                                                <div class="text-[10px] text-slate-400 font-mono uppercase tracking-wider">{{ $acc->platform }} &bull; @<span>{{ $acc->username }}</span></div>
                                            </div>
                                        </div>
                                        <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-white border border-slate-200 text-slate-600 font-semibold shrink-0 ml-1">
                                            {{ number_format($acc->followers_count) }}
                                        </span>
                                    </label>
                                @empty
                                    <div class="col-span-2 p-3 rounded-2xl border border-dashed border-slate-300 text-xs text-slate-400 text-center">
                                        Belum ada akun medsos resmi terhubung. Anda dapat menghubungkannya di menu <a href="{{ route('setting.index', ['tab' => 'social']) }}" class="text-blue-600 underline font-bold">Setting</a>.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- 2. Format & Aspect Ratio Selector -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-800 mb-1.5">Tipe Format Konten</label>
                                <div class="grid grid-cols-3 gap-1.5">
                                    <button type="button" @click="selectedFormat = 'photo'"
                                        :class="selectedFormat === 'photo' ? 'bg-blue-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                        class="py-2 px-1 rounded-xl text-xs transition-all text-center">
                                        Foto Tunggal
                                    </button>
                                    <button type="button" @click="selectedFormat = 'video'"
                                        :class="selectedFormat === 'video' ? 'bg-purple-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                        class="py-2 px-1 rounded-xl text-xs transition-all text-center">
                                        Video
                                    </button>
                                    <button type="button" @click="selectedFormat = 'text'"
                                        :class="selectedFormat === 'text' ? 'bg-slate-800 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                        class="py-2 px-1 rounded-xl text-xs transition-all text-center">
                                        Teks Saja
                                    </button>
                                </div>
                            </div>

                            <div x-show="selectedFormat !== 'text'">
                                <label class="block text-xs font-bold text-slate-800 mb-1.5">Rasio Tampilan (Aspect Ratio)</label>
                                <div class="grid grid-cols-4 gap-1.5">
                                    <button type="button" @click="aspectRatio = '1:1'"
                                        :class="aspectRatio === '1:1' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                        class="py-2 px-1 rounded-xl text-xs font-mono transition-all text-center">
                                        1:1
                                    </button>
                                    <button type="button" @click="aspectRatio = '4:5'"
                                        :class="aspectRatio === '4:5' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                        class="py-2 px-1 rounded-xl text-xs font-mono transition-all text-center">
                                        4:5
                                    </button>
                                    <button type="button" @click="aspectRatio = '16:9'"
                                        :class="aspectRatio === '16:9' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                        class="py-2 px-1 rounded-xl text-xs font-mono transition-all text-center">
                                        16:9
                                    </button>
                                    <button type="button" @click="aspectRatio = '9:16'"
                                        :class="aspectRatio === '9:16' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                        class="py-2 px-1 rounded-xl text-xs font-mono transition-all text-center">
                                        9:16
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Drag & Drop Media Upload Zone (Cooca Core Pattern) -->
                        <div x-show="selectedFormat !== 'text'" class="space-y-2">
                            <label class="block text-xs font-bold text-slate-800">
                                Unggah Media Lampiran (Foto / Video Armada &amp; Kontainer)
                            </label>

                            <!-- Dropzone Area -->
                            <div 
                                @dragover.prevent="isDragging = true"
                                @dragleave.prevent="isDragging = false"
                                @drop.prevent="handleFileDrop($event)"
                                class="relative border-2 border-dashed rounded-3xl p-5 text-center transition-all"
                                :class="isDragging ? 'border-blue-500 bg-blue-50/50' : 'border-slate-300 hover:border-slate-400 bg-slate-50/50'">
                                
                                <input type="file" name="media_file" id="media_file_input"
                                    @change="handleFileSelect($event)"
                                    accept="image/jpeg,image/png,image/webp,video/mp4,video/quicktime"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">

                                <template x-if="!filePreviewUrl">
                                    <div class="space-y-2 pointer-events-none">
                                        <div class="w-12 h-12 rounded-2xl bg-white text-blue-600 shadow-sm border border-slate-200 flex items-center justify-center mx-auto">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <div class="text-xs font-bold text-slate-800">
                                            Seret &amp; lepas file ke sini, atau <span class="text-blue-600">pilih dari komputer</span>
                                        </div>
                                        <p class="text-[11px] text-slate-400">JPG, PNG, WEBP, MP4, MOV (Maksimal 25MB)</p>
                                    </div>
                                </template>

                                <!-- File Preview Bar jika sudah ada file -->
                                <template x-if="filePreviewUrl">
                                    <div class="flex items-center justify-between p-3 bg-white rounded-2xl border border-slate-200 z-20 relative">
                                        <div class="flex items-center space-x-3 min-w-0">
                                            <div class="w-12 h-12 rounded-xl bg-slate-100 overflow-hidden shrink-0 border border-slate-200 flex items-center justify-center">
                                                <template x-if="fileType === 'video'">
                                                    <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/></svg>
                                                </template>
                                                <template x-if="fileType !== 'video'">
                                                    <img :src="filePreviewUrl" class="w-full h-full object-cover">
                                                </template>
                                            </div>
                                            <div class="text-left min-w-0">
                                                <div class="font-bold text-xs text-slate-900 truncate" x-text="fileName"></div>
                                                <div class="text-[10px] text-slate-400 font-mono" x-text="fileSize"></div>
                                            </div>
                                        </div>
                                        <button type="button" @click.stop="clearUploadedFile()" class="p-1.5 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 font-bold text-xs">
                                            Hapus File
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- 4. Caption Area with Character Counter & Hashtag Chips -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="text-xs font-bold text-slate-800">Isi Konten &amp; Caption Postingan</label>
                                <span class="text-[11px] font-mono font-semibold"
                                    :class="isCaptionExceeded ? 'text-rose-600 font-bold' : 'text-slate-400'">
                                    <span x-text="captionText.length"></span> / <span x-text="currentPlatformLimit"></span> karakter
                                </span>
                            </div>

                            <textarea name="content" rows="6" required x-model="captionText"
                                placeholder="Tuliskan berita jadwal kapal, panduan kepabeanan impor/ekspor, pengumuman tarif container, atau tips regulasi HS code..."
                                class="w-full px-4 py-3 rounded-2xl border text-xs leading-relaxed focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                :class="isCaptionExceeded ? 'border-rose-400 ring-2 ring-rose-200' : 'border-slate-200'"></textarea>

                            <template x-if="isCaptionExceeded">
                                <p class="text-[11px] text-rose-600 font-bold mt-1">
                                    ⚠️ Panjang teks melebihi limit karakter untuk saluran target (<span x-text="currentPlatformLimit"></span> karakter).
                                </p>
                            </template>

                            <!-- Quick Hashtag Inserters -->
                            <div class="mt-2.5 flex flex-wrap gap-1.5 items-center">
                                <span class="text-[10px] text-slate-400 font-semibold mr-1">Rekomendasi Tag Logistik:</span>
                                <button type="button" @click="addHashtag('#FreightForwarding')" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-[10px] font-mono font-semibold text-slate-700">#FreightForwarding</button>
                                <button type="button" @click="addHashtag('#CustomsClearance')" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-[10px] font-mono font-semibold text-slate-700">#CustomsClearance</button>
                                <button type="button" @click="addHashtag('#AgungTransLogistics')" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-[10px] font-mono font-semibold text-slate-700">#AgungTransLogistics</button>
                                <button type="button" @click="addHashtag('#SailingSchedule')" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-[10px] font-mono font-semibold text-slate-700">#SailingSchedule</button>
                                <button type="button" @click="addHashtag('#EksporImpor')" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-[10px] font-mono font-semibold text-slate-700">#EksporImpor</button>
                            </div>
                        </div>

                        <!-- 5. Opsi Jadwal Tayang (Schedule) -->
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                            <label class="flex items-center space-x-2.5 cursor-pointer text-xs font-semibold text-slate-800">
                                <input type="checkbox" name="is_scheduled" value="1" x-model="isScheduled" class="rounded text-blue-600 focus:ring-blue-500">
                                <span>Jadwalkan Waktu Tayang (Schedule Publishing)</span>
                            </label>

                            <div x-show="isScheduled" x-cloak class="pt-2">
                                <label class="block text-[11px] text-slate-600 mb-1">Pilih Tanggal &amp; Jam Publikasi</label>
                                <input type="datetime-local" name="scheduled_at" x-model="scheduledAt"
                                    class="px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-mono bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: INTERACTIVE DEVICE MOCKUP PREVIEW (Col-span 5) -->
                    <div class="lg:col-span-5 bg-slate-100/70 p-5 rounded-3xl border border-slate-200 space-y-3 sticky top-4">
                        <div class="flex items-center justify-between text-xs font-bold text-slate-700">
                            <span>Pratinjau Smartphone (Live Preview)</span>
                            <!-- Platform Preview Switcher Tabs -->
                            <div class="flex items-center space-x-1 bg-white p-1 rounded-xl border border-slate-200">
                                <button type="button" @click="previewPlatform = 'linkedin'"
                                    :class="previewPlatform === 'linkedin' ? 'bg-blue-600 text-white font-bold' : 'text-slate-600 hover:bg-slate-100'"
                                    class="px-2 py-0.5 rounded-lg text-[10px] transition-all">
                                    LI
                                </button>
                                <button type="button" @click="previewPlatform = 'instagram'"
                                    :class="previewPlatform === 'instagram' ? 'bg-pink-600 text-white font-bold' : 'text-slate-600 hover:bg-slate-100'"
                                    class="px-2 py-0.5 rounded-lg text-[10px] transition-all">
                                    IG
                                </button>
                                <button type="button" @click="previewPlatform = 'facebook'"
                                    :class="previewPlatform === 'facebook' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-600 hover:bg-slate-100'"
                                    class="px-2 py-0.5 rounded-lg text-[10px] transition-all">
                                    FB
                                </button>
                                <button type="button" @click="previewPlatform = 'tiktok'"
                                    :class="previewPlatform === 'tiktok' ? 'bg-slate-900 text-white font-bold' : 'text-slate-600 hover:bg-slate-100'"
                                    class="px-2 py-0.5 rounded-lg text-[10px] transition-all">
                                    TT
                                </button>
                            </div>
                        </div>

                        <!-- Realistic Smartphone Outer Shell -->
                        <div class="bg-slate-900 p-2.5 rounded-[40px] shadow-xl border-4 border-slate-800 max-w-[340px] mx-auto">
                            <!-- Speaker / Island Notch -->
                            <div class="w-20 h-4 bg-slate-950 rounded-full mx-auto mb-2 flex items-center justify-center">
                                <div class="w-2 h-2 rounded-full bg-slate-800 mr-2"></div>
                                <div class="w-8 h-1 bg-slate-800 rounded-full"></div>
                            </div>

                            <!-- Screen Canvas -->
                            <div class="bg-white rounded-[32px] overflow-hidden text-xs">
                                <!-- App Header -->
                                <div class="p-3 flex items-center space-x-2.5 border-b border-slate-100 bg-white">
                                    <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                                        AG
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-bold text-slate-900 text-xs flex items-center space-x-1">
                                            <span class="truncate">PT Agung Trans Logistics</span>
                                            <svg class="w-3.5 h-3.5 text-blue-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        <div class="text-[10px] text-slate-400 font-mono">
                                            <span x-text="previewPlatform.toUpperCase()"></span> &bull; Baru saja
                                        </div>
                                    </div>
                                </div>

                                <!-- Media Preview Container with Aspect Ratio -->
                                <div x-show="selectedFormat !== 'text'" 
                                    class="bg-slate-100 w-full overflow-hidden flex items-center justify-center relative"
                                    :class="{
                                        'aspect-square': aspectRatio === '1:1',
                                        'aspect-[4/5]': aspectRatio === '4:5',
                                        'aspect-video': aspectRatio === '16:9',
                                        'aspect-[9/16]': aspectRatio === '9:16'
                                    }">
                                    <template x-if="filePreviewUrl && fileType !== 'video'">
                                        <img :src="filePreviewUrl" alt="Preview Media" class="w-full h-full" :class="mediaFitMode === 'cover' ? 'object-cover' : 'object-contain'">
                                    </template>
                                    <template x-if="filePreviewUrl && fileType === 'video'">
                                        <video :src="filePreviewUrl" controls class="w-full h-full" :class="mediaFitMode === 'cover' ? 'object-cover' : 'object-contain'"></video>
                                    </template>
                                    <template x-if="!filePreviewUrl">
                                        <div class="p-6 text-center text-slate-400 text-[11px]">
                                            <svg class="w-8 h-8 mx-auto mb-1 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <span>Unggah media untuk melihat tampilan</span>
                                        </div>
                                    </template>
                                </div>

                                <!-- Caption Text Mockup with formatted linebreaks -->
                                <div class="p-3 space-y-1.5 bg-white">
                                    <p class="text-xs text-slate-800 whitespace-pre-line leading-relaxed break-words" x-text="captionText"></p>
                                </div>

                                <!-- Platform Specific Action Bar Mockup -->
                                <div class="p-2.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-slate-500 text-[11px] font-semibold">
                                    <template x-if="previewPlatform === 'linkedin'">
                                        <div class="w-full flex items-center justify-around text-slate-500 text-[10px]">
                                            <span>👍 Suka</span>
                                            <span>💬 Komentar</span>
                                            <span>🔁 Repost</span>
                                            <span>↗️ Kirim</span>
                                        </div>
                                    </template>
                                    <template x-if="previewPlatform === 'instagram'">
                                        <div class="w-full flex items-center justify-between px-1">
                                            <div class="flex items-center space-x-3">
                                                <span>❤️ 34</span>
                                                <span>💬 4</span>
                                                <span>↗️</span>
                                            </div>
                                            <span>🔖</span>
                                        </div>
                                    </template>
                                    <template x-if="previewPlatform === 'tiktok'">
                                        <div class="w-full flex items-center justify-around text-[10px] text-slate-700">
                                            <span>❤️ 1.2K</span>
                                            <span>💬 42</span>
                                            <span>↗️ Bagikan</span>
                                        </div>
                                    </template>
                                    <template x-if="previewPlatform === 'facebook'">
                                        <div class="w-full flex items-center justify-around text-[10px]">
                                            <span>👍 Suka</span>
                                            <span>💬 Komentar</span>
                                            <span>↗️ Bagikan</span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                    <button type="button" @click="openComposerModal = false"
                        class="px-5 py-2.5 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs hover:bg-slate-200 transition-all">
                        Batal
                    </button>
                    <button type="submit" :disabled="isCaptionExceeded || selectedAccounts.length === 0"
                        class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-bold text-xs shadow-sm transition-all flex items-center space-x-1.5">
                        <span x-text="isScheduled ? 'Jadwalkan Postingan' : 'Publikasikan Sekarang'">Publikasikan Sekarang</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- Alpine JS Component Logic -->
<script>
function socialComposerManager() {
    return {
        openComposerModal: false,
        selectedAccounts: [
            @foreach($accounts as $acc)
                '{{ $acc->id }}',
            @endforeach
        ],
        allAccountIds: [
            @foreach($accounts as $acc)
                '{{ $acc->id }}',
            @endforeach
        ],
        selectedFormat: 'photo',
        aspectRatio: '4:5',
        mediaFitMode: 'cover',
        previewPlatform: 'linkedin',
        captionText: '🚢 Update Jadwal Kapal Langsung (Direct Service) Pelayaran Shanghai - Tanjung Priok.\n\nTersedia space kontainer FCL 20ft & 40ft HC dengan transit time 7 hari. Hubungi tim kepabeanan dan freight forwarder kami untuk booking slot minggu ini!\n\n#CustomsClearance #FreightForwarding #BeaCukai #EksporImpor #AgungTransLogistics',
        isScheduled: false,
        scheduledAt: '',
        isDragging: false,
        filePreviewUrl: null,
        fileName: '',
        fileSize: '',
        fileType: 'image',

        get currentPlatformLimit() {
            const limits = {
                linkedin: 3000,
                instagram: 2200,
                facebook: 5000,
                tiktok: 2200
            };
            return limits[this.previewPlatform] || 3000;
        },

        get isCaptionExceeded() {
            return this.captionText.length > this.currentPlatformLimit;
        },

        toggleSelectAll() {
            if (this.selectedAccounts.length === this.allAccountIds.length) {
                this.selectedAccounts = [];
            } else {
                this.selectedAccounts = [...this.allAccountIds];
            }
        },

        addHashtag(tag) {
            if (!this.captionText.includes(tag)) {
                this.captionText = this.captionText.trim() + ' ' + tag;
            }
        },

        handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                this.processFile(file);
            }
        },

        handleFileDrop(event) {
            this.isDragging = false;
            const file = event.dataTransfer.files[0];
            if (file) {
                const input = document.getElementById('media_file_input');
                if (input) {
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    input.files = dataTransfer.files;
                }
                this.processFile(file);
            }
        },

        processFile(file) {
            this.fileName = file.name;
            this.fileSize = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
            this.fileType = file.type.startsWith('video/') ? 'video' : 'image';
            this.filePreviewUrl = URL.createObjectURL(file);
            if (this.fileType === 'video') {
                this.selectedFormat = 'video';
                this.aspectRatio = '16:9';
            }
        },

        clearUploadedFile() {
            this.filePreviewUrl = null;
            this.fileName = '';
            this.fileSize = '';
            const input = document.getElementById('media_file_input');
            if (input) input.value = '';
        }
    }
}
</script>
@endsection
