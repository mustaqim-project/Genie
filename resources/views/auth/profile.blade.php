@extends('layouts.app')

@section('title', 'Profil & Keamanan Akun')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 pb-16">
    <!-- Breadcrumb -->
    <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Profil Pengguna & Keamanan']
    ]" />

    <!-- Apple HIG Module Header Bar -->
    <div class="mb-8 rounded-[16px] bg-white dark:bg-[#1C1C1E] p-4 sm:p-5 border border-slate-200/80 dark:border-white/10 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold font-apple-display tracking-tight text-slate-900 dark:text-white">
                Profil & Keamanan Akun
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Kelola data profil pribadi, kata sandi, dan periksa ringkasan hak akses otorisasi Anda
            </p>
        </div>
        <div class="flex items-center space-x-2">
            <span class="px-3 py-1.5 rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-xs font-bold flex items-center space-x-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Status: {{ $user->is_active ? 'Akun Aktif' : 'Nonaktif' }}</span>
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column: User Card & Role Summary -->
        <div class="space-y-6">
            <!-- Profile Card -->
            <div class="bg-white dark:bg-[#1C1C1E] rounded-3xl border border-slate-200/80 dark:border-white/10 p-6 shadow-xs text-center">
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-[#007AFF] to-[#5AC8FA] text-white font-black text-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-[#007AFF]/20">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ $user->name }}</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-0.5">{{ $user->email }}</p>

                <div class="mt-4 flex flex-wrap justify-center gap-1.5">
                    @foreach($user->roles as $role)
                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold font-mono bg-sky-50 dark:bg-sky-950/50 text-[#007AFF] border border-sky-200 dark:border-sky-800">
                            [{{ $role->code }}] {{ $role->name }}
                        </span>
                    @endforeach
                </div>

                <div class="mt-6 pt-6 border-t border-slate-100 dark:border-white/10 text-left text-xs space-y-3">
                    <div class="flex justify-between">
                        <span class="text-slate-400 dark:text-slate-500">Jabatan:</span>
                        <span class="font-semibold text-slate-800 dark:text-white">{{ $user->job_title ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400 dark:text-slate-500">Departemen:</span>
                        <span class="font-semibold text-slate-800 dark:text-white">{{ $user->department ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400 dark:text-slate-500">No. Telepon / WA:</span>
                        <span class="font-semibold text-slate-800 dark:text-white tabular-nums">{{ $user->phone ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400 dark:text-slate-500">Terdaftar Sejak:</span>
                        <span class="font-semibold text-slate-800 dark:text-white tabular-nums">{{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Permission Summary Card -->
            <div class="bg-white dark:bg-[#1C1C1E] rounded-3xl border border-slate-200/80 dark:border-white/10 p-6 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-slate-900 dark:text-white text-sm flex items-center space-x-2">
                        <i data-lucide="shield-check" class="w-4 h-4 text-[#007AFF]"></i>
                        <span>Hak Akses Terdaftar</span>
                    </h3>
                    <span class="px-2 py-0.5 rounded-full bg-slate-100 dark:bg-white/10 text-slate-700 dark:text-slate-300 text-[10px] font-bold">
                        {{ $user->isSuperAdmin() ? 'Semua (Bypass)' : count($permissions) . ' Izin' }}
                    </span>
                </div>

                @if($user->isSuperAdmin())
                    <div class="p-3 rounded-xl bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800 text-purple-900 dark:text-purple-200 text-xs">
                        <div class="font-bold mb-1">SuperAdmin Privilege</div>
                        Akun ini memiliki hak akses tanpa batas (SuperAdmin Bypass) untuk mengeksekusi semua modul sistem ERP & CEISA 4.0.
                    </div>
                @else
                    <div class="space-y-1.5 max-h-60 overflow-y-auto pr-1">
                        @forelse($permissions as $perm)
                            <div class="p-2 rounded-xl bg-slate-50 dark:bg-[#2C2C2E]/60 border border-slate-100 dark:border-white/5 flex items-center justify-between text-xs">
                                <div>
                                    <div class="font-semibold text-slate-800 dark:text-white">{{ $perm->name }}</div>
                                    <div class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">{{ $perm->code }}</div>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-sky-50 dark:bg-sky-950/50 text-[#007AFF]">
                                    {{ $perm->module }}
                                </span>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 dark:text-slate-500 italic">Belum ada izin khusus yang diberikan.</p>
                        @endforelse
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Edit Forms -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Edit Profile Form -->
            <div class="bg-white dark:bg-[#1C1C1E] rounded-3xl border border-slate-200/80 dark:border-white/10 p-6 sm:p-8 shadow-xs">
                <h3 class="font-bold text-slate-900 dark:text-white text-base mb-1">Informasi Pribadi</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-6">Perbarui nama dan kontak yang dapat dihubungi oleh tim operasional.</p>

                <form action="{{ route('profile.update') }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white text-xs font-medium focus:ring-2 focus:ring-[#007AFF] focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Alamat Email (Akun)</label>
                            <input type="email" value="{{ $user->email }}" disabled
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-100 dark:bg-white/5 text-slate-500 dark:text-slate-400 text-xs font-medium cursor-not-allowed">
                            <span class="text-[10px] text-slate-400 dark:text-slate-500">Email hanya dapat diubah oleh Administrator Sistem.</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nomor Telepon / WhatsApp</label>
                            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="Contoh: 08123456789"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white text-xs font-medium focus:ring-2 focus:ring-[#007AFF] focus:outline-none tabular-nums">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Jabatan Pekerjaan</label>
                            <input type="text" name="job_title" value="{{ old('job_title', $user->job_title) }}"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white text-xs font-medium focus:ring-2 focus:ring-[#007AFF] focus:outline-none">
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit" class="btn-apple-filled">
                            Simpan Perubahan Profil
                        </button>
                    </div>
                </form>
            </div>

            <!-- Change Password Form -->
            <div class="bg-white dark:bg-[#1C1C1E] rounded-3xl border border-slate-200/80 dark:border-white/10 p-6 sm:p-8 shadow-xs">
                <h3 class="font-bold text-slate-900 dark:text-white text-base mb-1">Keamanan & Ubah Kata Sandi</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-6">Pastikan akun Anda terlindungi dengan menggunakan kata sandi yang aman dan tidak dibagikan kepada pihak lain.</p>

                <form action="{{ route('profile.password') }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Kata Sandi Saat Ini</label>
                        <input type="password" name="current_password" required placeholder="Masukkan kata sandi lama Anda"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white text-xs font-medium focus:ring-2 focus:ring-[#007AFF] focus:outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Kata Sandi Baru</label>
                            <input type="password" name="password" required placeholder="Minimal 6 karakter"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white text-xs font-medium focus:ring-2 focus:ring-[#007AFF] focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Ulangi Kata Sandi Baru</label>
                            <input type="password" name="password_confirmation" required placeholder="Ketik ulang kata sandi baru"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-white/15 dark:bg-[#2C2C2E] dark:text-white text-xs font-medium focus:ring-2 focus:ring-[#007AFF] focus:outline-none">
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit" class="btn-apple-filled">
                            Perbarui Kata Sandi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
