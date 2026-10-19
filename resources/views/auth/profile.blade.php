@extends('layouts.app')

@section('title', 'Profil & Keamanan Akun')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Breadcrumb & Header -->
    <div class="mb-8">
        <div class="flex items-center space-x-2 text-xs text-slate-500 mb-2">
            <a href="{{ route('dashboard') }}" class="hover:text-brand-600">Dashboard</a>
            <span>/</span>
            <span class="text-slate-800 font-semibold">Profil Pengguna & Keamanan</span>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Profil & Keamanan Akun</h1>
                <p class="text-sm text-slate-500 mt-1">Kelola data profil pribadi, kata sandi, dan periksa ringkasan hak akses otorisasi Anda.</p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold flex items-center space-x-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Status: {{ $user->is_active ? 'Akun Aktif' : 'Nonaktif' }}</span>
                </span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column: User Card & Role Summary -->
        <div class="space-y-6">
            <!-- Profile Card -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm text-center">
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-brand-700 to-brand-500 text-white font-black text-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-brand-500/20">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <h2 class="text-lg font-bold text-slate-900">{{ $user->name }}</h2>
                <p class="text-xs text-slate-500 font-mono mt-0.5">{{ $user->email }}</p>

                <div class="mt-4 flex flex-wrap justify-center gap-1.5">
                    @foreach($user->roles as $role)
                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold font-mono bg-brand-50 text-brand-700 border border-brand-200">
                            [{{ $role->code }}] {{ $role->name }}
                        </span>
                    @endforeach
                </div>

                <div class="mt-6 pt-6 border-t border-slate-100 text-left text-xs space-y-3">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Jabatan:</span>
                        <span class="font-semibold text-slate-800">{{ $user->job_title ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Departemen:</span>
                        <span class="font-semibold text-slate-800">{{ $user->department ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">No. Telepon / WA:</span>
                        <span class="font-semibold text-slate-800">{{ $user->phone ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Terdaftar Sejak:</span>
                        <span class="font-semibold text-slate-800">{{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Permission Summary Card -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-slate-900 text-sm flex items-center space-x-2">
                        <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span>Hak Akses Terdaftar</span>
                    </h3>
                    <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 text-[10px] font-bold">
                        {{ $user->isSuperAdmin() ? 'Semua (Bypass)' : count($permissions) . ' Izin' }}
                    </span>
                </div>

                @if($user->isSuperAdmin())
                    <div class="p-3 rounded-xl bg-purple-50 border border-purple-200 text-purple-900 text-xs">
                        <div class="font-bold mb-1">SuperAdmin Privilege</div>
                        Akun ini memiliki hak akses tanpa batas (SuperAdmin Bypass) untuk mengeksekusi semua modul sistem ERP & CEISA 4.0.
                    </div>
                @else
                    <div class="space-y-1.5 max-h-60 overflow-y-auto pr-1">
                        @forelse($permissions as $perm)
                            <div class="p-2 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between text-xs">
                                <div>
                                    <div class="font-semibold text-slate-800">{{ $perm->name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $perm->code }}</div>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700">
                                    {{ $perm->module }}
                                </span>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 italic">Belum ada izin khusus yang diberikan.</p>
                        @endforelse
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Edit Forms -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Edit Profile Form -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-sm">
                <h3 class="font-bold text-slate-900 text-base mb-1">Informasi Pribadi</h3>
                <p class="text-xs text-slate-500 mb-6">Perbarui nama dan kontak yang dapat dihubungi oleh tim operasional.</p>

                <form action="{{ route('profile.update') }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-brand-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Email (Akun)</label>
                            <input type="email" value="{{ $user->email }}" disabled
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-100 text-slate-500 text-xs font-medium cursor-not-allowed">
                            <span class="text-[10px] text-slate-400">Email hanya dapat diubah oleh Administrator Sistem.</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Telepon / WhatsApp</label>
                            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="Contoh: 08123456789"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-brand-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Jabatan Pekerjaan</label>
                            <input type="text" name="job_title" value="{{ old('job_title', $user->job_title) }}"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-brand-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-600 text-white font-bold text-xs shadow-md shadow-brand-600/20 hover:bg-brand-700 transition-colors">
                            Simpan Perubahan Profil
                        </button>
                    </div>
                </form>
            </div>

            <!-- Change Password Form -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-sm">
                <h3 class="font-bold text-slate-900 text-base mb-1">Keamanan & Ubah Kata Sandi</h3>
                <p class="text-xs text-slate-500 mb-6">Pastikan akun Anda terlindungi dengan menggunakan kata sandi yang aman dan tidak dibagikan kepada pihak lain.</p>

                <form action="{{ route('profile.password') }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kata Sandi Saat Ini</label>
                        <input type="password" name="current_password" required placeholder="Masukkan kata sandi lama Anda"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kata Sandi Baru</label>
                            <input type="password" name="password" required placeholder="Minimal 6 karakter"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-brand-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Ulangi Kata Sandi Baru</label>
                            <input type="password" name="password_confirmation" required placeholder="Ketik ulang kata sandi baru"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-brand-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-900 text-white font-bold text-xs shadow-md hover:bg-slate-800 transition-colors">
                            Perbarui Kata Sandi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
