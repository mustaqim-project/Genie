@extends('layouts.app')

@section('title', 'Setting & Manajemen RBAC Pengguna')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-8" x-data="{ activeTab: 'users', showAddUserModal: false, resetPasswordModal: false, selectedUserId: null, selectedUserName: '' }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <div class="text-xs text-slate-500 font-medium mb-1">
                <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a> / <span class="text-slate-800 font-semibold">Setting & Hak Akses</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Pusat Kendali Pengguna & Hak Akses (RBAC)</h1>
            <p class="text-xs text-slate-500 mt-1">Manajemen otorisasi 12 peran resmi, kendali akun, matriks perizinan, dan konfigurasi integrasi CEISA 4.0.</p>
        </div>
        <div class="flex items-center space-x-3">
            <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-slate-900 text-white text-xs font-bold shadow-sm">
                <span class="w-2 h-2 rounded-full bg-emerald-400 mr-2 animate-pulse"></span>
                RBAC Core: ACTIVE
            </span>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center space-x-2 border-b border-slate-200 mb-8 overflow-x-auto">
        <button type="button" @click="activeTab = 'users'" :class="activeTab === 'users' ? 'border-brand-600 text-brand-700 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700'" class="px-4 py-3 border-b-2 text-xs transition-colors whitespace-nowrap flex items-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span>Manajemen Pengguna ({{ $users->total() }})</span>
        </button>

        <button type="button" @click="activeTab = 'matrix'" :class="activeTab === 'matrix' ? 'border-brand-600 text-brand-700 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700'" class="px-4 py-3 border-b-2 text-xs transition-colors whitespace-nowrap flex items-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <span>Matriks Hak Akses (12 Peran)</span>
        </button>

        <button type="button" @click="activeTab = 'gateway'" :class="activeTab === 'gateway' ? 'border-brand-600 text-brand-700 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700'" class="px-4 py-3 border-b-2 text-xs transition-colors whitespace-nowrap flex items-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span>Gateway CEISA 4.0 & Audit Log</span>
        </button>
    </div>

    <!-- TAB 1: MANAJEMEN PENGGUNA -->
    <div x-show="activeTab === 'users'" class="space-y-6">
        <!-- Actions & Summary -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <div class="flex items-center space-x-3 text-xs text-slate-500">
                <span>Total: <strong class="text-slate-800">{{ $users->total() }} Pengguna</strong></span>
                <span>&bull;</span>
                <span class="text-emerald-700 font-semibold">{{ $users->where('is_active', true)->count() }} Aktif</span>
            </div>
            <div>
                <button type="button" @click="showAddUserModal = true" class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-md shadow-brand-600/20 flex items-center space-x-1.5 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <span>Tambah Pengguna Baru</span>
                </button>
            </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-100">
                        <tr>
                            <th class="py-3.5 px-4">Pengguna</th>
                            <th class="py-3.5 px-4">Jabatan & Departemen</th>
                            <th class="py-3.5 px-4">Kontak / WA</th>
                            <th class="py-3.5 px-4">Peran (RBAC)</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 text-right">Aksi Manajemen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($users as $u)
                            <tr class="hover:bg-slate-50/60 transition-colors {{ !$u->is_active ? 'bg-slate-50/50 opacity-70' : '' }}">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 rounded-xl bg-slate-800 text-white font-bold text-xs flex items-center justify-center flex-shrink-0">
                                            {{ substr($u->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 flex items-center space-x-1.5">
                                                <span>{{ $u->name }}</span>
                                                @if($u->id === auth()->id())
                                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-blue-100 text-blue-700">Anda</span>
                                                @endif
                                            </div>
                                            <div class="text-[11px] text-slate-400 font-mono">{{ $u->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-medium text-slate-800">{{ $u->job_title ?? '-' }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $u->department ?? '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-slate-600">
                                    {{ $u->phone ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <form action="{{ route('setting.user-role', $u->id) }}" method="POST" class="flex items-center space-x-1.5">
                                        @csrf
                                        <select name="role_id" onchange="this.form.submit()" class="px-2 py-1 text-xs rounded-lg border border-slate-200 bg-white font-semibold font-mono text-slate-700 focus:ring-1 focus:ring-brand-500">
                                            @foreach($roles as $r)
                                                <option value="{{ $r->id }}" {{ $u->hasRole($r->code) ? 'selected' : '' }}>
                                                    [{{ $r->code }}] {{ $r->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($u->is_active)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <!-- Quick Login Link for Simulation -->
                                        @if($u->roles->isNotEmpty() && $u->is_active)
                                            <a href="{{ route('quick-login', $u->roles->first()->code) }}" title="Login sebagai user ini" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                                </svg>
                                            </a>
                                        @endif

                                        <!-- Toggle Status -->
                                        @if($u->id !== auth()->id() && $u->id !== 1)
                                            <form action="{{ route('setting.users.toggle-status', $u->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" title="{{ $u->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}" class="p-1.5 rounded-lg border {{ $u->is_active ? 'border-amber-200 text-amber-700 hover:bg-amber-50' : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50' }} transition-colors">
                                                    @if($u->is_active)
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                        </svg>
                                                    @else
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                    @endif
                                                </button>
                                            </form>

                                            <!-- Delete User -->
                                            <form action="{{ route('setting.users.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna {{ $u->name }}?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Hapus Pengguna" class="p-1.5 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 transition-colors">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $users->links() }}
            </div>
        </div>
    </div>

    <!-- TAB 2: MATRIKS HAK AKSES PER PERAN -->
    <div x-show="activeTab === 'matrix'" class="space-y-6">
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Matriks Hak Akses Dinamis (12 Peran Resmi)</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Atur izin per modul untuk masing-masing peran. Perubahan langsung tersimpan ke basis data RBAC.</p>
                </div>
            </div>

            <!-- Role Accordions / Tabs -->
            <div class="space-y-4" x-data="{ selectedRole: 'R-DOC' }">
                <!-- Role Selector Badges -->
                <div class="flex flex-wrap gap-2 pb-4 border-b border-slate-100">
                    @foreach($roles as $role)
                        <button type="button" @click="selectedRole = '{{ $role->code }}'" :class="selectedRole === '{{ $role->code }}' ? 'bg-brand-600 text-white shadow-md shadow-brand-600/20' : 'bg-slate-50 text-slate-700 border border-slate-200 hover:bg-slate-100'" class="px-3 py-1.5 rounded-xl text-xs font-bold font-mono transition-colors">
                            [{{ $role->code }}] {{ $role->name }}
                        </button>
                    @endforeach
                </div>

                <!-- Forms per Role -->
                @foreach($roles as $role)
                    <div x-show="selectedRole === '{{ $role->code }}'" class="pt-2">
                        <div class="p-4 rounded-2xl bg-brand-50/50 border border-brand-100 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <div class="font-bold text-sm text-brand-900">Peran: [{{ $role->code }}] {{ $role->name }}</div>
                                <div class="text-xs text-brand-700 mt-0.5">{{ $role->description }}</div>
                            </div>
                            <div class="text-xs font-mono font-bold text-brand-800">
                                {{ count($role->permissions) }} Izin Aktif Saat Ini
                            </div>
                        </div>

                        @if(in_array($role->code, ['R-ADM', 'R-OWN']))
                            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-xs mb-6">
                                <strong>Catatan SuperAdmin:</strong> Peran {{ $role->name }} secara otomatis memiliki bypass penuh ke semua modul sistem sesuai arsitektur Laravel Gate & Klausul PKS.
                            </div>
                        @endif

                        <form action="{{ route('setting.roles.permissions', $role->id) }}" method="POST">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @foreach($permissions as $moduleName => $perms)
                                    <div class="p-4 rounded-2xl border border-slate-200 bg-white shadow-sm">
                                        <div class="font-bold text-xs text-slate-900 border-b border-slate-100 pb-2 mb-3 flex items-center justify-between">
                                            <span>{{ $moduleName }}</span>
                                            <span class="text-[10px] text-slate-400 font-mono">{{ count($perms) }} izin</span>
                                        </div>
                                        <div class="space-y-2">
                                            @foreach($perms as $perm)
                                                <label class="flex items-start space-x-2 text-xs text-slate-700 cursor-pointer hover:text-slate-900">
                                                    <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                                        {{ $role->hasPermission($perm->code) ? 'checked' : '' }}
                                                        class="mt-0.5 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                                    <div>
                                                        <div class="font-semibold">{{ $perm->name }}</div>
                                                        <div class="text-[10px] text-slate-400 font-mono">{{ $perm->code }}</div>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-8 pt-4 border-t border-slate-100 flex justify-end">
                                <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-md shadow-brand-600/20 transition-colors">
                                    Simpan Perubahan Matriks Izin [{{ $role->code }}]
                                </button>
                            </div>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- TAB 3: GATEWAY & AUDIT LOGS -->
    <div x-show="activeTab === 'gateway'" class="space-y-6">
        <!-- Gateway Configuration Card -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Parameter Gateway CEISA 4.0 Bea Cukai</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Konfigurasi host, port, akun integrasi NLE, dan mode pengujian sandbox.</p>
                </div>
                <form action="{{ route('config.test-auth') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 flex items-center space-x-1.5 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span>Uji Koneksi Token OAuth CEISA</span>
                    </button>
                </form>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs font-mono">
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                    <span class="text-slate-400 block text-[10px]">MODE GATEWAY:</span>
                    <span class="font-bold {{ ($config['api']['mock_mode'] ?? true) ? 'text-amber-600' : 'text-emerald-600' }}">
                        {{ ($config['api']['mock_mode'] ?? true) ? 'SANDBOX SIMULATOR' : 'LIVE PRODUCTION' }}
                    </span>
                </div>
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                    <span class="text-slate-400 block text-[10px]">DEFAULT KANTOR PABEAN:</span>
                    <span class="font-bold text-slate-800">{{ $config['company']['default_kode_kantor'] ?? '040300' }} (Tanjung Priok)</span>
                </div>
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                    <span class="text-slate-400 block text-[10px]">USERNAME INTEGRASI:</span>
                    <span class="font-bold text-slate-800 truncate block">{{ $config['api']['username'] ?? 'agungtrans_api' }}</span>
                </div>
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                    <span class="text-slate-400 block text-[10px]">NPWP IMPORTIR/PPJK:</span>
                    <span class="font-bold text-slate-800">{{ $config['company']['npwp'] ?? '012345678012000' }}</span>
                </div>
            </div>
        </div>

        <!-- Audit Logs Table -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-bold text-xs text-slate-800 uppercase tracking-wider">
                    Log Audit Interaksi Gateway CEISA Terbaru
                </h2>
                <span class="text-xs text-slate-400">10 Log Terakhir</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-mono">
                    <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">Waktu</th>
                            <th class="py-3 px-4">Endpoint</th>
                            <th class="py-3 px-4">Method</th>
                            <th class="py-3 px-4">Status HTTP</th>
                            <th class="py-3 px-4">Durasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($logs as $log)
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-3 px-4 text-slate-500">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                <td class="py-3 px-4 text-slate-800">{{ $log->endpoint }}</td>
                                <td class="py-3 px-4 font-bold text-slate-600">{{ $log->method }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $log->response_status >= 200 && $log->response_status < 300 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ $log->response_status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-500">{{ $log->execution_time_ms ?? 120 }} ms</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-400 italic">Belum ada riwayat log audit API.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH PENGGUNA BARU -->
    <div x-show="showAddUserModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-200" @click.outside="showAddUserModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-6">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Tambah Akun Pengguna Baru</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Daftarkan akun staf dan pilih peran RBAC yang berwenang.</p>
                </div>
                <button type="button" @click="showAddUserModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('setting.users.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" required placeholder="Contoh: Rian Hidayat"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email Resmi</label>
                        <input type="email" name="email" required placeholder="nama@agungtrans.co.id"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">No. HP / WA</label>
                        <input type="text" name="phone" placeholder="08123456789"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jabatan</label>
                        <input type="text" name="job_title" placeholder="Contoh: Staff Dokumen"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Departemen</label>
                        <input type="text" name="department" placeholder="Customs & Documentation"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Peran Awal (RBAC)</label>
                    <select name="role_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-brand-500 focus:outline-none bg-white">
                        @foreach($roles as $r)
                            <option value="{{ $r->id }}">[{{ $r->code }}] {{ $r->name }} - {{ $r->description }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kata Sandi Awal</label>
                    <input type="password" name="password" required placeholder="Minimal 6 karakter"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <div class="pt-4 flex items-center justify-end space-x-3 border-t border-slate-100">
                    <button type="button" @click="showAddUserModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-md shadow-brand-600/20 transition-colors">
                        Simpan Pengguna
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Alpine JS Script for Tab Switching & Modals -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection
