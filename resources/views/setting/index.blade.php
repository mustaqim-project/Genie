@extends('layouts.app')

@section('title', 'Setting & Manajemen RBAC Pengguna')

@section('content')
<div class="max-w-[1360px] mx-auto px-4 sm:px-6 space-y-6 pt-4 pb-28 lg:pb-12" x-data="{ activeTab: '{{ request('tab', 'users') }}', showAddUserModal: false, showAddSocialModal: false, resetPasswordModal: false, selectedUserId: null, selectedUserName: '' }">
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

        <button type="button" @click="activeTab = 'waba'" :class="activeTab === 'waba' ? 'border-emerald-600 text-emerald-700 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700'" class="px-4 py-3 border-b-2 text-xs transition-colors whitespace-nowrap flex items-center space-x-2">
            <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.97.53 1.951.815 2.796.815 3.183 0 5.767-2.586 5.768-5.766 0-3.18-2.585-5.766-5.768-5.766zm3.376 8.21c-.14.394-.71.724-1.015.768-.29.043-.664.068-2.148-.546-1.898-.786-3.119-2.73-3.214-2.857-.095-.127-.771-1.026-.771-1.955 0-.928.487-1.385.66-1.574.173-.189.378-.236.504-.236.126 0 .252.001.362.006.116.006.27-.044.423.323.158.378.539 1.314.587 1.408.047.095.079.205.016.331-.063.126-.095.205-.189.315-.094.11-.198.246-.283.33-.095.095-.194.198-.083.388.11.19.49 8.08 1.05 1.306.721.643 1.33.842 1.519.936.19.095.3.079.41-.047.11-.126.473-.551.6-.74.126-.19.252-.158.425-.095.174.063 1.103.52 1.292.615.19.095.316.142.363.22.047.079.047.457-.094.851z"/>
            </svg>
            <span>WhatsApp WABA (Cloud API)</span>
        </button>

        <button type="button" @click="activeTab = 'social'" :class="activeTab === 'social' ? 'border-brand-600 text-brand-700 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700'" class="px-4 py-3 border-b-2 text-xs transition-colors whitespace-nowrap flex items-center space-x-2">
            <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
            </svg>
            <span>Media Sosial Resmi</span>
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
                <button type="button" @click="showAddUserModal = true" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs flex items-center space-x-1.5 transition-all active:scale-95 cursor-pointer">
                    <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        <button type="button" @click="selectedRole = '{{ $role->code }}'" :class="selectedRole === '{{ $role->code }}' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-50 text-slate-700 border border-slate-200 hover:bg-slate-100'" class="px-3 py-1.5 rounded-xl text-xs font-bold font-mono transition-all active:scale-95 cursor-pointer">
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
                                <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition-all active:scale-95 cursor-pointer">
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
        <!-- Gateway Configuration Form (Saved to Database, Independent from .env) -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-100">
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full {{ ($config['api']['mock_mode'] ?? true) ? 'bg-amber-500 animate-pulse' : 'bg-emerald-500 animate-pulse' }}"></span>
                        <h2 class="text-base font-bold text-slate-900">Konfigurasi Gateway CEISA 4.0 & Profil Entitas DJBC</h2>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Seluruh pengaturan tersimpan aman di basis data (<code class="font-mono text-slate-700 bg-slate-100 px-1 py-0.5 rounded">system_settings</code>) tanpa bergantung pada file <code class="font-mono text-slate-700 bg-slate-100 px-1 py-0.5 rounded">.env</code>. Anda dapat mengganti ke Live Production kapan saja.</p>
                </div>
                <div class="flex items-center space-x-2">
                    <form action="{{ route('config.test-auth') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition-all active:scale-95 flex items-center space-x-1.5 cursor-pointer">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span>Uji Koneksi Token OAuth CEISA</span>
                        </button>
                    </form>
                </div>
            </div>

            <form action="{{ route('setting.ceisa.update') }}" method="POST" class="space-y-6">
                @csrf

                <!-- 1. Pemilihan Mode Lingkungan (Segmented Switcher) -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200" x-data="{ currentMode: '{{ old('ceisa_mode', ($config['api']['mock_mode'] ?? true) ? 'sandbox' : 'production') }}' }">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Mode Lingkungan Gateway CEISA 4.0</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <label class="p-3.5 rounded-xl border-2 transition-all flex items-start space-x-3 cursor-pointer"
                               :class="currentMode === 'sandbox' ? 'border-amber-500 bg-white shadow-xs' : 'border-slate-200 bg-slate-100/60 opacity-70 hover:opacity-100'">
                            <input type="radio" name="ceisa_mode" value="sandbox" x-model="currentMode" class="mt-0.5 text-amber-600 focus:ring-amber-500">
                            <div>
                                <span class="font-bold text-amber-700 block">🟡 Mode Sandbox / Simulator DJBC</span>
                                <span class="text-[11px] text-slate-500 leading-relaxed block mt-0.5">
                                    Simulasi lokal terstandar format DJBC. Dokumen PIB/PEB diuji tanpa memotong kuota atau mengirim data transaksi sungguhan ke Bea Cukai.
                                </span>
                            </div>
                        </label>

                        <label class="p-3.5 rounded-xl border-2 transition-all flex items-start space-x-3 cursor-pointer"
                               :class="currentMode === 'production' ? 'border-emerald-500 bg-white shadow-xs' : 'border-slate-200 bg-slate-100/60 opacity-70 hover:opacity-100'">
                            <input type="radio" name="ceisa_mode" value="production" x-model="currentMode" class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <span class="font-bold text-emerald-700 block">🟢 Mode Live Production (Gateway DJBC Asli)</span>
                                <span class="text-[11px] text-slate-500 leading-relaxed block mt-0.5">
                                    Koneksi langsung ke server Open API Bea Cukai. Menggunakan API Key resmi dan menerbitkan respon sah (Billing MPN, SPPB, NPE).
                                </span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- 2. Kredensial Gateway Host to Host (H2H) -->
                <div class="p-5 rounded-2xl border border-slate-200 bg-white space-y-4">
                    <div class="flex items-center space-x-2 border-b border-slate-100 pb-2">
                        <span class="w-6 h-6 rounded-lg bg-sky-100 text-[#007AFF] flex items-center justify-center text-xs font-bold">1</span>
                        <h3 class="font-bold text-xs text-slate-900 uppercase tracking-wider">Kredensial Gateway Host to Host (H2H)</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs font-mono">
                        <div>
                            <label class="block font-sans font-semibold text-slate-700 mb-1">API Key Header (<code class="text-[#007AFF]">beacukai-api-key</code>) *</label>
                            <input type="text" name="ceisa_api_key" 
                                   value="{{ old('ceisa_api_key', $settings['ceisa_api_key'] ?? config('ceisa.api.api_key')) }}" 
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 font-bold text-slate-800 focus:ring-2 focus:ring-[#007AFF]" required>
                        </div>
                        <div>
                            <label class="block font-sans font-semibold text-slate-700 mb-1">Application ID *</label>
                            <input type="text" name="ceisa_application_id" 
                                   value="{{ old('ceisa_application_id', $settings['ceisa_application_id'] ?? config('ceisa.api.application_id')) }}" 
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 font-bold text-slate-800 focus:ring-2 focus:ring-[#007AFF]" required>
                        </div>
                        <div>
                            <label class="block font-sans font-semibold text-slate-700 mb-1">Nomor WhatsApp Notifikasi DJBC</label>
                            <input type="text" name="ceisa_whatsapp_no" 
                                   value="{{ old('ceisa_whatsapp_no', $settings['ceisa_whatsapp_no'] ?? config('ceisa.api.whatsapp_no')) }}" 
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 text-slate-800 focus:ring-2 focus:ring-[#007AFF]">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs font-mono pt-2">
                        <div>
                            <label class="block font-sans font-semibold text-slate-700 mb-1">Username Akun API DJBC *</label>
                            <input type="text" name="ceisa_username" 
                                   value="{{ old('ceisa_username', $settings['ceisa_username'] ?? config('ceisa.api.username')) }}" 
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 text-slate-800 focus:ring-2 focus:ring-[#007AFF]" required>
                        </div>
                        <div>
                            <label class="block font-sans font-semibold text-slate-700 mb-1">Password Akun API DJBC *</label>
                            <input type="password" name="ceisa_password" 
                                   value="{{ old('ceisa_password', $settings['ceisa_password'] ?? config('ceisa.api.password')) }}" 
                                   placeholder="Masukkan password akun DJBC"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 text-slate-800 focus:ring-2 focus:ring-[#007AFF]">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs font-mono pt-2 border-t border-slate-100">
                        <div>
                            <label class="block font-sans font-semibold text-slate-700 mb-1">Base URL Gateway DJBC *</label>
                            <input type="url" name="ceisa_base_url" 
                                   value="{{ old('ceisa_base_url', $settings['ceisa_base_url'] ?? config('ceisa.api.base_url')) }}" 
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 text-slate-800 focus:ring-2 focus:ring-[#007AFF]" required>
                        </div>
                        <div>
                            <label class="block font-sans font-semibold text-slate-700 mb-1">Auth URL Login (OAuth) *</label>
                            <input type="url" name="ceisa_auth_url" 
                                   value="{{ old('ceisa_auth_url', $settings['ceisa_auth_url'] ?? config('ceisa.api.auth_url')) }}" 
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 text-slate-800 focus:ring-2 focus:ring-[#007AFF]" required>
                        </div>
                        <div>
                            <label class="block font-sans font-semibold text-slate-700 mb-1">Refresh Token URL</label>
                            <input type="url" name="ceisa_refresh_token_url" 
                                   value="{{ old('ceisa_refresh_token_url', $settings['ceisa_refresh_token_url'] ?? config('ceisa.api.refresh_token_url')) }}" 
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 text-slate-800 focus:ring-2 focus:ring-[#007AFF]">
                        </div>
                    </div>
                </div>

                <!-- 3. Profil Entitas Perusahaan Terdaftar DJBC -->
                <div class="p-5 rounded-2xl border border-slate-200 bg-white space-y-4">
                    <div class="flex items-center space-x-2 border-b border-slate-100 pb-2">
                        <span class="w-6 h-6 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold">2</span>
                        <h3 class="font-bold text-xs text-slate-900 uppercase tracking-wider">Profil Entitas Perusahaan Terdaftar DJBC</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs font-mono">
                        <div>
                            <label class="block font-sans font-semibold text-slate-700 mb-1">Nama Perusahaan (Sesuai NIB) *</label>
                            <input type="text" name="company_name" 
                                   value="{{ old('company_name', $settings['company_name'] ?? config('ceisa.company.name')) }}" 
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 font-bold text-slate-800 font-sans focus:ring-2 focus:ring-[#007AFF]" required>
                        </div>
                        <div>
                            <label class="block font-sans font-semibold text-slate-700 mb-1">NIB (Nomor Induk Berusaha) *</label>
                            <input type="text" name="company_nib" 
                                   value="{{ old('company_nib', $settings['company_nib'] ?? config('ceisa.company.nib')) }}" 
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 text-slate-800 focus:ring-2 focus:ring-[#007AFF]" required>
                        </div>
                        <div>
                            <label class="block font-sans font-semibold text-slate-700 mb-1">NPWP 15/16 Digit *</label>
                            <input type="text" name="company_npwp" 
                                   value="{{ old('company_npwp', $settings['company_npwp'] ?? config('ceisa.company.npwp')) }}" 
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 text-slate-800 focus:ring-2 focus:ring-[#007AFF]" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs font-mono pt-2">
                        <div>
                            <label class="block font-sans font-semibold text-slate-700 mb-1">Kantor Pabean Default *</label>
                            <select name="company_kode_kantor" class="w-full px-3 py-2 rounded-xl border border-slate-300 font-sans font-semibold text-slate-800 focus:ring-2 focus:ring-[#007AFF]">
                                @foreach($kantorList as $code => $name)
                                    <option value="{{ $code }}" {{ (old('company_kode_kantor', $settings['company_kode_kantor'] ?? config('ceisa.company.default_kode_kantor')) === $code) ? 'selected' : '' }}>
                                        {{ $code }} - {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-sans font-semibold text-slate-700 mb-1">ID Unik Perusahaan (6 Digit Nomor Aju) *</label>
                            <input type="text" name="company_id_perusahaan" 
                                   value="{{ old('company_id_perusahaan', $settings['company_id_perusahaan'] ?? config('ceisa.company.id_perusahaan')) }}" 
                                   maxlength="6"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 text-slate-800 focus:ring-2 focus:ring-[#007AFF]" required>
                        </div>
                        <div>
                            <label class="block font-sans font-semibold text-slate-700 mb-1">Alamat Kantor / Domisili *</label>
                            <input type="text" name="company_address" 
                                   value="{{ old('company_address', $settings['company_address'] ?? config('ceisa.company.address')) }}" 
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 font-sans text-slate-800 focus:ring-2 focus:ring-[#007AFF]" required>
                        </div>
                    </div>
                </div>

                <!-- 4. Parameter Server & Webhook DJBC -->
                <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50/50 space-y-4">
                    <div class="flex items-center space-x-2 border-b border-slate-200/60 pb-2">
                        <span class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold">3</span>
                        <h3 class="font-bold text-xs text-slate-900 uppercase tracking-wider">Integrasi Server & Webhook DJBC</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs font-mono">
                        <div class="p-3.5 rounded-xl bg-white border border-slate-200">
                            <span class="text-slate-400 block font-sans text-[11px] mb-1 font-semibold">IP Public Server (Untuk Form Whitelist DJBC):</span>
                            <span class="font-bold text-slate-900 text-sm block">125.161.95.178</span>
                            <p class="text-[10px] font-sans text-slate-500 mt-1">Masukkan IP ini (atau IP Statis VPS Anda jika sudah di Cloud) ke kolom whitelist portal DJBC.</p>
                        </div>

                        <div class="p-3.5 rounded-xl bg-white border border-slate-200" x-data="{ copied: false }">
                            <span class="text-slate-400 block font-sans text-[11px] mb-1 font-semibold">Webhook URL Callback DJBC (Real-Time):</span>
                            <div class="flex items-center space-x-2">
                                <span class="font-bold text-[#007AFF] text-xs truncate select-all">{{ url('/api/v1/ceisa/webhook') }}</span>
                                <button type="button" @click="navigator.clipboard.writeText('{{ url('/api/v1/ceisa/webhook') }}'); copied = true; setTimeout(() => copied = false, 2000)" 
                                        class="px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 text-[10px] font-bold font-sans cursor-pointer shrink-0">
                                    <span x-text="copied ? '✓ Tersalin' : 'Salin'">Salin</span>
                                </button>
                            </div>
                            <p class="text-[10px] font-sans text-slate-500 mt-1">Salin URL ini ke kolom <em>Webhook URL</em> pada portal Bea Cukai untuk menerima notifikasi SPPB/NPE instan.</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition-all active:scale-95 flex items-center space-x-2 cursor-pointer">
                        <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Simpan Seluruh Pengaturan ke Database</span>
                    </button>
                </div>
            </form>
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

    <!-- TAB 4: WHATSAPP WABA GATEWAY -->
    <div x-show="activeTab === 'waba'" class="space-y-6" x-cloak>
        <!-- Status Card Meta Cloud API -->
        <div class="bg-gradient-to-r from-emerald-900 via-slate-900 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
            <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
                <div class="flex items-start sm:items-center space-x-4">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-500/20 border border-emerald-400/30 flex items-center justify-center text-emerald-400 shadow-inner flex-shrink-0">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.97.53 1.951.815 2.796.815 3.183 0 5.767-2.586 5.768-5.766 0-3.18-2.585-5.766-5.768-5.766zm3.376 8.21c-.14.394-.71.724-1.015.768-.29.043-.664.068-2.148-.546-1.898-.786-3.119-2.73-3.214-2.857-.095-.127-.771-1.026-.771-1.955 0-.928.487-1.385.66-1.574.173-.189.378-.236.504-.236.126 0 .252.001.362.006.116.006.27-.044.423.323.158.378.539 1.314.587 1.408.047.095.079.205.016.331-.063.126-.095.205-.189.315-.094.11-.198.246-.283.33-.095.095-.194.198-.083.388.11.19.49 8.08 1.05 1.306.721.643 1.33.842 1.519.936.19.095.3.079.41-.047.11-.126.473-.551.6-.74.126-.19.252-.158.425-.095.174.063 1.103.52 1.292.615.19.095.316.142.363.22.047.079.047.457-.094.851z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse mr-1.5"></span>
                                Meta Official Cloud API v20.0
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/20 text-blue-300 border border-blue-400/30">
                                Single-System Forwarder ERP
                            </span>
                        </div>
                        <h2 class="text-xl font-bold mt-1.5 flex items-center space-x-2">
                            <span>{{ $waSetting->verified_name ?? 'PT AGUNG TRANS LOGISTICS' }}</span>
                            <svg class="w-5 h-5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </h2>
                        <p class="text-xs text-slate-300 font-mono mt-0.5">
                            Nomor Resmi: {{ $waSetting->display_phone_number ?? '+62 812-9000-8888' }} &bull; WABA ID: {{ $waSetting->waba_id ?? '104928374829103' }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <div class="bg-white/10 backdrop-blur-md px-3.5 py-2 rounded-2xl border border-white/10 text-right">
                        <div class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold">Tier Pengiriman</div>
                        <div class="text-xs font-bold text-white font-mono">{{ $waSetting->messaging_limit_tier ?? 'TIER_10K' }} (10.000/hari)</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md px-3.5 py-2 rounded-2xl border border-white/10 text-right">
                        <div class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold">Quality Rating</div>
                        <div class="text-xs font-bold text-emerald-400 flex items-center justify-end space-x-1">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            <span>{{ $waSetting->quality_rating ?? 'GREEN (Sangat Baik)' }}</span>
                        </div>
                    </div>
                    <a href="{{ route('whatsapp.index') }}" class="px-4 py-2.5 rounded-xl bg-white text-slate-900 font-bold text-xs hover:bg-slate-100 transition-colors shadow-sm flex items-center space-x-1.5">
                        <span>Buka WhatsApp Cockpit</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Form Konfigurasi Kredensial Meta & Trigger Otomasi -->
        <form action="{{ route('setting.whatsapp.update') }}" method="POST" class="space-y-6">
            @csrf
            
            <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-sm">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-6">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Kredensial Meta Business & WhatsApp Cloud API</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Konfigurasi API token sistem permanen dari Meta Developer Console.</p>
                    </div>
                    <span class="text-[11px] font-mono text-slate-400 bg-slate-100 px-2.5 py-1 rounded-lg">Graph API v20.0</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Bisnis Terverifikasi (Verified Name)</label>
                        <input type="text" name="verified_name" value="{{ old('verified_name', $waSetting->verified_name) }}" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <p class="text-[11px] text-slate-400 mt-1">Nama resmi bercentang hijau yang muncul di layar WhatsApp klien.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Nomor Telepon Pengirim (Display Number)</label>
                        <input type="text" name="display_phone_number" value="{{ old('display_phone_number', $waSetting->display_phone_number) }}" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-mono focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <p class="text-[11px] text-slate-400 mt-1">Format internasional dengan kode negara (contoh: +62 812-9000-8888).</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">WhatsApp Business Account ID (WABA ID)</label>
                        <input type="text" name="waba_id" value="{{ old('waba_id', $waSetting->waba_id) }}" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-mono focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <p class="text-[11px] text-slate-400 mt-1">ID akun WABA di Meta Business Suite Manager.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Phone Number ID (Meta Graph API)</label>
                        <input type="text" name="phone_number_id" value="{{ old('phone_number_id', $waSetting->phone_number_id) }}" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-mono focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <p class="text-[11px] text-slate-400 mt-1">Identifier unik nomor pengirim di dashboard Developer Meta.</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Permanent System User Access Token</label>
                        <input type="password" name="access_token" placeholder="EAABw..." value="{{ $waSetting->access_token ? '••••••••••••••••••••••••••••••••••••••••••••••••' : '' }}"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-mono focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <p class="text-[11px] text-slate-400 mt-1">Kosongkan jika tidak ingin mengubah access token permanen Meta yang sudah tersimpan aman.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Webhook Callback URL</label>
                        <div class="flex items-center space-x-2">
                            <input type="text" readonly value="{{ url('/api/v1/webhook/whatsapp') }}"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-mono text-slate-600 focus:outline-none select-all">
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Salin URL ini ke konfigurasi Webhook Meta Cloud API untuk menerima status pesan masuk & centang biru.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Webhook Verify Token</label>
                        <input type="text" name="webhook_verify_token" value="{{ old('webhook_verify_token', $waSetting->webhook_verify_token ?? 'agungtrans_waba_secret_2026') }}" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-mono focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <p class="text-[11px] text-slate-400 mt-1">Token rahasia verifikasi webhook antara server GENIE dan Meta.</p>
                    </div>
                </div>
            </div>

            <!-- Trigger Otomasi Notifikasi Kargo & Logistik -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-sm">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-6">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Trigger Otomasi Notifikasi Kargo & Kepabeanan</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Sistem akan secara otomatis mengirimkan pesan resmi WhatsApp WABA ke klien saat event logistik berikut tercapai.</p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-xl bg-emerald-50 text-emerald-700 text-xs font-bold">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5"></span>
                        Event Listener Active
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="flex items-start space-x-3.5 p-4 rounded-2xl border border-slate-200/80 hover:bg-slate-50/70 transition-colors cursor-pointer {{ $waSetting->auto_send_sppb ? 'bg-emerald-50/30 border-emerald-200' : '' }}">
                        <input type="checkbox" name="auto_send_sppb" value="1" {{ $waSetting->auto_send_sppb ? 'checked' : '' }}
                            class="mt-1 w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                        <div>
                            <div class="font-bold text-xs text-slate-900">Kirim Otomatis SPPB (Jalur Hijau Bea Cukai)</div>
                            <div class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                                Notifikasi instan ke importir saat respon CEISA 4.0 menerbitkan Surat Persetujuan Pengeluaran Barang beserta nomor pendaftaran pabean.
                            </div>
                        </div>
                    </label>

                    <label class="flex items-start space-x-3.5 p-4 rounded-2xl border border-slate-200/80 hover:bg-slate-50/70 transition-colors cursor-pointer {{ $waSetting->auto_send_npe ? 'bg-emerald-50/30 border-emerald-200' : '' }}">
                        <input type="checkbox" name="auto_send_npe" value="1" {{ $waSetting->auto_send_npe ? 'checked' : '' }}
                            class="mt-1 w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                        <div>
                            <div class="font-bold text-xs text-slate-900">Kirim Otomatis NPE (Nota Pelayanan Ekspor)</div>
                            <div class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                                Notifikasi instan ke eksportir begitu dokumen NPE terbit untuk kepastian pemuatan kontainer (stuffing) ke kapal/pesawat.
                            </div>
                        </div>
                    </label>

                    <label class="flex items-start space-x-3.5 p-4 rounded-2xl border border-slate-200/80 hover:bg-slate-50/70 transition-colors cursor-pointer {{ $waSetting->auto_send_do ? 'bg-emerald-50/30 border-emerald-200' : '' }}">
                        <input type="checkbox" name="auto_send_do" value="1" {{ $waSetting->auto_send_do ? 'checked' : '' }}
                            class="mt-1 w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                        <div>
                            <div class="font-bold text-xs text-slate-900">Kirim Otomatis Delivery Order (DO) Release</div>
                            <div class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                                Notifikasi ke pihak consignee & armada trucking bahwa Delivery Order pelayaran telah ditebus dan kontainer siap ditarik dari depo/TPS.
                            </div>
                        </div>
                    </label>

                    <label class="flex items-start space-x-3.5 p-4 rounded-2xl border border-slate-200/80 hover:bg-slate-50/70 transition-colors cursor-pointer {{ $waSetting->auto_send_billing ? 'bg-emerald-50/30 border-emerald-200' : '' }}">
                        <input type="checkbox" name="auto_send_billing" value="1" {{ $waSetting->auto_send_billing ? 'checked' : '' }}
                            class="mt-1 w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                        <div>
                            <div class="font-bold text-xs text-slate-900">Kirim Otomatis Invoice Billing & Kwitansi</div>
                            <div class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                                Kirim ringkasan invoice, link pembayaran/rekening resmi, dan dokumen tagihan saat invoice pengiriman disahkan bagian finance.
                            </div>
                        </div>
                    </label>
                </div>

                <div class="pt-6 mt-6 border-t border-slate-100 flex items-center justify-between">
                    <p class="text-xs text-slate-400">Template pesan resmi yang dikirimkan mengacu pada template yang disetujui Meta di menu WhatsApp WABA.</p>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition-all active:scale-95 flex items-center space-x-2 cursor-pointer">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Simpan Konfigurasi WhatsApp WABA</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- TAB 5: KONEKSI MEDIA SOSIAL RESMI -->
    <div x-show="activeTab === 'social'" class="space-y-6" x-cloak>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm">
            <div>
                <h2 class="text-base font-bold text-slate-900">Koneksi Akun Media Sosial Resmi Perusahaan</h2>
                <p class="text-xs text-slate-500 mt-0.5">Kelola saluran media sosial resmi PT Agung Trans Logistics untuk publikasi konten kargo dan integrasi pesan masuk.</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('social-media.index') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-50 transition-colors flex items-center space-x-1.5">
                    <span>Buka Komposer & Feed</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
                <button type="button" @click="showAddSocialModal = true" class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition-all active:scale-95 flex items-center space-x-1.5 cursor-pointer">
                    <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    <span>Hubungkan Saluran Baru</span>
                </button>
            </div>
        </div>

        <!-- Grid Saluran Terhubung -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @forelse($socialAccounts as $acc)
                @php
                    $isLinked = $acc->status === 'active';
                    $platformColors = [
                        'linkedin' => ['bg' => 'bg-sky-50 text-sky-700 border-sky-200', 'badge' => 'bg-sky-600'],
                        'instagram' => ['bg' => 'bg-pink-50 text-pink-700 border-pink-200', 'badge' => 'bg-gradient-to-r from-purple-500 to-pink-500'],
                        'facebook' => ['bg' => 'bg-blue-50 text-blue-700 border-blue-200', 'badge' => 'bg-blue-600'],
                        'tiktok' => ['bg' => 'bg-slate-900 text-white border-slate-800', 'badge' => 'bg-slate-800'],
                        'youtube' => ['bg' => 'bg-red-50 text-red-700 border-red-200', 'badge' => 'bg-red-600'],
                    ];
                    $theme = $platformColors[$acc->platform] ?? ['bg' => 'bg-slate-50 text-slate-700 border-slate-200', 'badge' => 'bg-slate-600'];
                @endphp
                <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-sm flex flex-col justify-between hover:border-slate-300 transition-all">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-[10px] font-bold uppercase tracking-wider {{ $acc->platform === 'tiktok' ? 'bg-slate-900 text-white' : $theme['bg'] }}">
                                {{ $acc->platform }}
                            </span>
                            @if($isLinked)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                                    Terhubung
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                    Terputus
                                </span>
                            @endif
                        </div>

                        <div class="font-bold text-slate-900 text-sm truncate">{{ $acc->account_name }}</div>
                        <div class="text-xs text-slate-400 font-mono mt-0.5">@<span>{{ $acc->username }}</span></div>

                        <div class="grid grid-cols-2 gap-2 mt-4 pt-4 border-t border-slate-100">
                            <div>
                                <div class="text-[10px] text-slate-400">Pengikut / Audiens</div>
                                <div class="text-xs font-bold text-slate-800 font-mono">{{ number_format($acc->followers_count) }}</div>
                            </div>
                            <div>
                                <div class="text-[10px] text-slate-400">Sinkronisasi</div>
                                <div class="text-[11px] font-bold text-slate-700">{{ $acc->updated_at ? $acc->updated_at->diffForHumans() : 'Hari ini' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 mt-4 border-t border-slate-100">
                        @if($isLinked)
                            <form action="{{ route('setting.social.disconnect', $acc->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memutuskan integrasi akun ini?')">
                                @csrf
                                <button type="submit" class="w-full py-2 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 font-bold text-xs transition-colors">
                                    Putuskan Koneksi
                                </button>
                            </form>
                        @else
                            <button type="button" @click="showAddSocialModal = true" class="w-full py-2 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 font-bold text-xs transition-colors">
                                Hubungkan Kembali
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-4 bg-white rounded-3xl border border-slate-200/80 p-8 text-center">
                    <p class="text-xs text-slate-400 italic">Belum ada akun media sosial yang terhubung.</p>
                </div>
            @endforelse
        </div>

        <!-- Panduan API Meta & LinkedIn -->
        <div class="bg-blue-50/60 rounded-3xl border border-blue-200/70 p-6">
            <h4 class="text-xs font-bold text-blue-900 flex items-center space-x-2">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Panduan Integrasi Multi-Channel Media Sosial</span>
            </h4>
            <p class="text-xs text-blue-800 mt-1 leading-relaxed">
                Untuk penyiaran posting otomatis ke Instagram Business & Facebook Pages, pastikan Halaman Facebook resmi telah tertaut ke Meta Business Manager yang sama dengan WhatsApp Cloud API. Konten yang diterbitkan dari menu Media Sosial akan otomatis disinkronkan ke feed resmi tanpa memotong kuota pihak ketiga.
            </p>
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
                    <button type="submit" class="px-5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition-all active:scale-95 cursor-pointer">
                        Simpan Pengguna
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL HUBUNGKAN AKUN MEDIA SOSIAL -->
    <div x-show="showAddSocialModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-slate-200" @click.outside="showAddSocialModal = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-6">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Hubungkan Saluran Media Sosial</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Tautkan akun resmi perusahaan ke sistem GENIE.</p>
                </div>
                <button type="button" @click="showAddSocialModal = false" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('setting.social.connect') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Platform Media Sosial</label>
                    <select name="platform" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-brand-500 focus:outline-none bg-white">
                        <option value="linkedin">LinkedIn Company Page</option>
                        <option value="instagram">Instagram Professional / Creator</option>
                        <option value="facebook">Facebook Business Page</option>
                        <option value="tiktok">TikTok for Business</option>
                        <option value="youtube">YouTube Channel</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Profil / Akun Resmi</label>
                    <input type="text" name="account_name" required placeholder="Contoh: PT Agung Trans Logistics Indonesia"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Username / Handle (@)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 font-mono text-xs">@</span>
                        <input type="text" name="username" required placeholder="agungtrans.id"
                            class="w-full pl-8 pr-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-mono focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end space-x-3 border-t border-slate-100">
                    <button type="button" @click="showAddSocialModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition-all active:scale-95 cursor-pointer">
                        Hubungkan Akun
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Alpine JS Script for Tab Switching & Modals -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection
