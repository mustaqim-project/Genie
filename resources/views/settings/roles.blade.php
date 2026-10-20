@extends('layouts.app')

@section('title', 'Manajemen Peran & Hak Akses (RBAC)')

@section('content')
<div class="space-y-6 pb-20" x-data="roleCockpitApp()">

    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER (Apple Sonoma Toolbar Style)  -->
    <!-- ===================================================== -->
    <header class="rounded-2xl backdrop-blur-md bg-white border border-slate-200/80 px-4 sm:px-6 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-xs">
        <div>
            <nav class="flex items-center gap-1.5 text-xs text-slate-400 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-sky-600 transition-colors">Dashboard</a>
                <span>&rarr;</span>
                <span class="text-slate-600 font-medium">Pengaturan</span>
                <span>&rarr;</span>
                <span class="text-slate-900 font-semibold">Peran & Hak Akses (RBAC)</span>
            </nav>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight flex items-center space-x-2">
                <span>Manajemen Peran & Hak Akses (RBAC)</span>
                <span class="px-2 py-0.5 rounded-md bg-sky-100 text-sky-800 text-[10px] font-mono font-bold uppercase">Multi-Tenant</span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Kendalikan otoritas hak akses staf operasional dan klien AI pabean secara tersentralisasi dan granular.</p>
        </div>

        <!-- Toolbar Actions -->
        <div class="flex items-center gap-2.5 w-full sm:w-auto">
            <button type="button" 
                    @click="openCreateModal()"
                    class="h-10 px-4 rounded-xl text-xs font-semibold text-white bg-sky-600 hover:bg-sky-700 active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 shadow-sm cursor-pointer w-full sm:w-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Buat Role Custom</span>
            </button>
        </div>
    </header>

    <!-- Flash Notifications (Apple Banner Style) -->
    @if (session('success'))
        <div class="rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-xs text-emerald-800 font-semibold flex items-center gap-3">
            <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
            <div class="flex-1">{{ session('success') }}</div>
        </div>
    @endif
    @if (session('error'))
        <div class="rounded-xl bg-rose-50 border border-rose-200 px-4 py-3 text-xs text-rose-800 font-semibold flex items-center gap-3">
            <span class="w-2 h-2 rounded-full bg-rose-500 shrink-0"></span>
            <div class="flex-1">{{ session('error') }}</div>
        </div>
    @endif

    <!-- ===================================================== -->
    <!-- 2. KPI SUMMARY (4 Flat Apple Standard Cards)          -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Tile 1: Total Role -->
        <div class="rounded-2xl bg-white border border-slate-200/80 p-4 flex flex-col justify-between shadow-2xs">
            <span class="text-xs font-medium text-slate-500">Total Peran (Roles)</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-bold tabular-nums text-slate-900">{{ $roles->count() }}</span>
                <span class="text-[11px] font-medium text-slate-400">Preset & Custom</span>
            </div>
        </div>

        <!-- Tile 2: Anggota Tim Aktif -->
        <div class="rounded-2xl bg-white border border-slate-200/80 p-4 flex flex-col justify-between shadow-2xs">
            <span class="text-xs font-medium text-slate-500">Anggota Tim Terdaftar</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-bold tabular-nums text-emerald-600">{{ $members->count() }}</span>
                <span class="text-[11px] font-medium text-emerald-600">Pengguna Aktif</span>
            </div>
        </div>

        <!-- Tile 3: Cakupan Permissions -->
        <div class="rounded-2xl bg-white border border-slate-200/80 p-4 flex flex-col justify-between shadow-2xs">
            <span class="text-xs font-medium text-slate-500">Cakupan Izin Granular</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-bold tabular-nums text-indigo-600">{{ collect($permissions)->flatten(1)->count() }}</span>
                <span class="text-[11px] font-medium text-indigo-600">{{ $permissions->count() }} Modul Sistem</span>
            </div>
        </div>

        <!-- Tile 4: Status Proteksi Keamanan -->
        <div class="rounded-2xl bg-white border border-slate-200/80 p-4 flex flex-col justify-between shadow-2xs">
            <span class="text-xs font-medium text-slate-500">Tingkat Proteksi</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-lg font-bold text-purple-600">RBAC Level Controller</span>
                <span class="text-[11px] font-medium text-purple-600">Dual-Control</span>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 3. SEGMENTED NAVIGATION & VIEW SWITCHER               -->
    <!-- ===================================================== -->
    <div class="flex items-center justify-between gap-3 border-b border-slate-200 pb-3">
        <div class="overflow-x-auto pb-1 scrollbar-none">
            <div class="inline-flex p-1 rounded-xl bg-slate-100 border border-slate-200 text-xs font-medium whitespace-nowrap">
                <button type="button" 
                        @click="activeTab = 'roles'"
                        :class="activeTab === 'roles' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900'"
                        class="px-3.5 py-1.5 rounded-lg transition-all flex items-center gap-2 cursor-pointer">
                    <span>🛡️</span>
                    <span>Daftar Peran (Roles)</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800">{{ $roles->count() }}</span>
                </button>

                <button type="button" 
                        @click="activeTab = 'members'"
                        :class="activeTab === 'members' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900'"
                        class="px-3.5 py-1.5 rounded-lg transition-all flex items-center gap-2 cursor-pointer">
                    <span>👥</span>
                    <span>Anggota Tim & Pengguna</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">{{ $members->count() }}</span>
                </button>

                <button type="button" 
                        @click="activeTab = 'matrix'"
                        :class="activeTab === 'matrix' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900'"
                        class="px-3.5 py-1.5 rounded-lg transition-all flex items-center gap-2 cursor-pointer">
                    <span>📊</span>
                    <span>Matriks Akses RBAC</span>
                </button>
            </div>
        </div>

        <button type="button" 
                @click="showGuide = !showGuide"
                class="text-xs text-sky-700 hover:underline font-semibold flex items-center gap-1 shrink-0 cursor-pointer">
            <span>ℹ️</span>
            <span x-text="showGuide ? 'Sembunyikan Panduan' : 'Tampilkan Panduan Otoritas'"></span>
        </button>
    </div>

    <!-- Interactive Apple Callout: Panduan RBAC -->
    <div x-show="showGuide" x-transition class="rounded-2xl bg-slate-50 border border-slate-200/80 p-4 sm:p-5 space-y-3">
        <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-sky-500"></span>
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700">Panduan Tingkat Otoritas Hak Akses Peran</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
            <div class="rounded-xl bg-white border border-slate-200 p-3 space-y-1">
                <span class="font-bold text-sky-700">[R-ADM / R-OWN] Pimpinan & Admin</span>
                <p class="text-slate-500 leading-relaxed text-[11px]">Memiliki hak akses penuh tanpa batasan terhadap seluruh modul bisnis, laporan laba rugi, CEISA, dan approval pabean.</p>
            </div>
            <div class="rounded-xl bg-white border border-slate-200 p-3 space-y-1">
                <span class="font-bold text-emerald-700">[R-DOC / R-SPV] Tim Dokumen PPJK</span>
                <p class="text-slate-500 leading-relaxed text-[11px]">Mengelola pengapalan, ekstraksi packing list, draft PIB/PEB, dan pengajuan submit respon dokumen ke CEISA 4.0.</p>
            </div>
            <div class="rounded-xl bg-white border border-slate-200 p-3 space-y-1">
                <span class="font-bold text-amber-700">[R-FIN] Keuangan & Kasir</span>
                <p class="text-slate-500 leading-relaxed text-[11px]">Mengelola piutang invoice P19, pembayaran hutang vendor pelayaran/truk, rekonsiliasi kas & bank, dan rekap pajak.</p>
            </div>
            <div class="rounded-xl bg-white border border-slate-200 p-3 space-y-1">
                <span class="font-bold text-purple-700">[R-TRF / R-FLD] Tim Traffic Lapangan</span>
                <p class="text-slate-500 leading-relaxed text-[11px]">Memantau fisik kontainer pelabuhan, pendampingan behandle jalur merah, koordinasi supir truk, dan upload Surat Jalan.</p>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- TAB 1: DAFTAR ROLE CARDS                              -->
    <!-- ===================================================== -->
    <div x-show="activeTab === 'roles'" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($roles as $role)
                @php
                    $isPreset = in_array($role->code, ['R-ADM', 'R-OWN', 'R-DOC-SPV', 'R-FIN']);
                    $permIds = $role->permissions->pluck('id')->toArray();
                    $permCodes = $role->permissions->pluck('code')->toArray();
                    $userCount = $role->users->count();
                @endphp
                <div class="rounded-2xl bg-white border border-slate-200/80 p-5 flex flex-col justify-between gap-4 shadow-2xs hover:border-sky-300 transition-all">
                    <div class="space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-bold text-slate-900">{{ $role->name }}</h3>
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="font-mono text-[11px] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-700">
                                        {{ $role->code }}
                                    </span>
                                    @if ($isPreset)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                            Preset Sistem
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                            Role Custom
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <span class="text-xs font-bold text-slate-500 bg-slate-50 px-2 py-1 rounded-lg border border-slate-100">
                                {{ $userCount }} Staf
                            </span>
                        </div>

                        <p class="text-xs text-slate-600 leading-relaxed">
                            {{ $role->description ?? 'Peran operasional sistem kepabeanan & logistik GENIE.' }}
                        </p>

                        <!-- Permissions summary chips -->
                        <div class="pt-2 border-t border-slate-100">
                            <span class="text-[10px] font-bold text-slate-400 block mb-1.5 uppercase">
                                Izin Akses Aktif ({{ count($permIds) }})
                            </span>
                            <div class="flex flex-wrap gap-1 max-h-24 overflow-y-auto">
                                @forelse($role->permissions->take(8) as $p)
                                    <span class="px-2 py-0.5 bg-slate-100 rounded text-[10px] font-mono text-slate-700 truncate max-w-[140px]" title="{{ $p->name }}">
                                        {{ $p->code }}
                                    </span>
                                @empty
                                    <span class="text-slate-400 italic text-[11px]">Belum ada hak akses terhubung</span>
                                @endforelse
                                @if(count($permIds) > 8)
                                    <span class="px-2 py-0.5 bg-sky-50 rounded text-[10px] font-bold text-sky-700">
                                        +{{ count($permIds) - 8 }} lainnya
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Card Actions -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                        <button type="button" 
                                @click="openEditModal({{ json_encode($role) }}, '{{ route('settings.roles.update', $role->id) }}', {{ json_encode($permIds) }})"
                                class="text-xs text-sky-700 hover:text-sky-900 font-bold flex items-center gap-1 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>Atur Hak Akses &rarr;</span>
                        </button>

                        @if(!$isPreset)
                        <form action="{{ route('settings.roles.destroy', $role->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus role custom ini?');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-rose-600 hover:text-rose-800 font-medium p-1 cursor-pointer" title="Hapus Role">
                                Hapus
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- TAB 2: ANGGOTA TIM & PENGGUNA                         -->
    <!-- ===================================================== -->
    <div x-show="activeTab === 'members'" class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden space-y-4">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Daftar Anggota Tim & Otoritas Peran</h3>
                <p class="text-xs text-slate-500 mt-0.5">Kelola penugasan peran bagi staf operasional, tim dokumen, dan finance.</p>
            </div>
            <button type="button" 
                    @click="showAddMemberModal = true"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs flex items-center gap-1.5 cursor-pointer">
                <span>+ Daftarkan Anggota Baru</span>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase text-[10px]">
                    <tr>
                        <th class="p-3.5">Nama & Kontak</th>
                        <th class="p-3.5">Jabatan / Departemen</th>
                        <th class="p-3.5">Peran Aktif</th>
                        <th class="p-3.5">Status Akun</th>
                        <th class="p-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($members as $m)
                    @php $firstRole = $m->roles->first(); @endphp
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="p-3.5">
                            <div class="font-bold text-slate-900 text-sm">{{ $m->name }}</div>
                            <div class="text-[11px] text-slate-500 font-mono">{{ $m->email }} {{ $m->phone ? '• ' . $m->phone : '' }}</div>
                        </td>
                        <td class="p-3.5">
                            <div class="font-medium text-slate-800">{{ $m->job_title ?? 'Staf Operasional' }}</div>
                            <div class="text-[10px] text-slate-400">{{ $m->department ?? 'Operasional Logistik' }}</div>
                        </td>
                        <td class="p-3.5">
                            @if($firstRole)
                                <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-sky-50 text-sky-800 border border-sky-200">
                                    [{{ $firstRole->code }}] {{ $firstRole->name }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[11px] bg-slate-100 text-slate-500 italic">Belum Ada Peran</span>
                            @endif
                        </td>
                        <td class="p-3.5">
                            @if($m->is_active)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Aktif</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-600">Non-Aktif</span>
                            @endif
                        </td>
                        <td class="p-3.5 text-right space-x-2">
                            <button type="button" 
                                    @click="openChangeRoleModal({{ json_encode($m) }}, '{{ route('settings.roles.members.role', $m->id) }}', '{{ $firstRole?->id ?? '' }}')"
                                    class="px-2.5 py-1 text-sky-700 hover:bg-sky-50 border border-sky-200 rounded-lg text-[11px] font-bold transition-all cursor-pointer">
                                Ubah Peran
                            </button>
                            @if($m->email !== 'admin@agungtrans.co.id' && $m->id !== auth()->id())
                            <form action="{{ route('settings.roles.members.destroy', $m->id) }}" method="POST" onsubmit="return confirm('Hapus anggota tim ini?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2 py-1 text-rose-600 hover:bg-rose-50 border border-rose-200 rounded-lg text-[11px] font-semibold transition-all cursor-pointer">
                                    Hapus
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-slate-400">Belum ada anggota tim terdaftar.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- TAB 3: MATRIKS AKSES RBAC (Matrix Table)              -->
    <!-- ===================================================== -->
    <div x-show="activeTab === 'matrix'" class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden space-y-4 p-5">
        <div>
            <h3 class="text-sm font-bold text-slate-900">Matriks Hak Akses Granular Seluruh Peran (Role vs Permission)</h3>
            <p class="text-xs text-slate-500 mt-0.5">Tinjau hak akses yang dimiliki tiap peran di seluruh modul sistem kepabeanan, logistik, dan finance.</p>
        </div>

        <div class="overflow-x-auto border border-slate-200 rounded-xl">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 font-bold">
                    <tr>
                        <th class="p-3 min-w-[240px]">Modul & Izin Sistem</th>
                        @foreach($roles as $r)
                        <th class="p-3 text-center min-w-[100px] border-l border-slate-200">
                            <span class="block font-bold text-slate-900">{{ $r->code }}</span>
                            <span class="text-[10px] font-normal text-slate-500 block truncate max-w-[100px]">{{ $r->name }}</span>
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($permissions as $module => $perms)
                    <tr class="bg-slate-100/70 font-bold text-slate-800">
                        <td colspan="{{ $roles->count() + 1 }}" class="p-2.5 text-xs uppercase tracking-wider text-sky-900">
                            📁 Modul: {{ $module }}
                        </td>
                    </tr>
                    @foreach($perms as $p)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="p-2.5 font-medium text-slate-800">
                            <div>{{ $p->name }}</div>
                            <code class="text-[10px] text-slate-400 font-mono">{{ $p->code }}</code>
                        </td>
                        @foreach($roles as $r)
                        @php $hasPerm = $r->hasPermission($p->code); @endphp
                        <td class="p-2.5 text-center border-l border-slate-100">
                            @if($hasPerm)
                                <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 inline-flex items-center justify-center font-bold text-xs" title="Diizinkan">✓</span>
                            @else
                                <span class="text-slate-300 font-mono text-xs" title="Tidak Memiliki Akses">—</span>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- MODAL 1: BUAT / EDIT ROLE & PERMISSIONS               -->
    <!-- ===================================================== -->
    <div x-show="showCreateModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         x-transition>
        <div class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-7 shadow-2xl border border-slate-200 space-y-5"
             @click.away="showCreateModal = false">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center text-sm font-bold">
                        🛡️
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900" x-text="editingRole ? 'Edit Peran: ' + editingRole.name : 'Buat Role Custom Baru'"></h3>
                        <p class="text-[11px] text-slate-500">Tentukan nama peran dan pilih hak akses spesifik yang diizinkan.</p>
                    </div>
                </div>
                <button type="button" @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form :action="editFormAction" method="POST" class="space-y-4">
                @csrf
                <template x-if="editingRole">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Nama Peran (Role Name) <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               x-model="formRoleName" 
                               required 
                               placeholder="Contoh: Operasional Impor Laut" 
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-sky-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Kode Peran (Role Code)
                        </label>
                        <input type="text" 
                               name="code" 
                               x-model="formRoleCode" 
                               :disabled="editingRole !== null"
                               placeholder="Contoh: R-SEAOPS" 
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-mono uppercase focus:ring-2 focus:ring-sky-500 disabled:bg-slate-100">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Deskripsi Tanggung Jawab
                    </label>
                    <textarea name="description" 
                              x-model="formRoleDesc" 
                              rows="2" 
                              placeholder="Deskripsi tugas dan tanggung jawab peran..." 
                              class="w-full p-3 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-sky-500"></textarea>
                </div>

                <!-- Permission Selector with Filter Search -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-bold text-slate-700">
                            Pilih Hak Akses Granular (Permissions)
                        </label>
                        <input type="text" 
                               x-model="searchPermission" 
                               placeholder="Cari izin..." 
                               class="px-2.5 py-1 rounded-lg border border-slate-200 text-[11px] w-40">
                    </div>

                    <div class="border border-slate-200 rounded-xl max-h-60 overflow-y-auto divide-y divide-slate-100 p-2 bg-slate-50/50">
                        @foreach($permissions as $module => $perms)
                        @php $moduleIds = $perms->pluck('id')->toArray(); @endphp
                        <div class="py-2.5 px-2">
                            <div class="flex items-center justify-between mb-1.5 pb-1 border-b border-slate-200">
                                <span class="font-bold text-xs text-sky-900 uppercase">📁 {{ $module }}</span>
                                <button type="button" 
                                        @click="toggleCategory({{ json_encode($moduleIds) }})" 
                                        class="text-[10px] text-sky-700 hover:underline font-bold cursor-pointer">
                                    Pilih/Batal Semua
                                </button>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 mt-1">
                                @foreach($perms as $p)
                                <label x-show="!searchPermission || '{{ strtolower($p->name . ' ' . $p->code) }}'.includes(searchPermission.toLowerCase())" 
                                       class="flex items-start space-x-2 text-xs text-slate-700 p-1.5 rounded-lg hover:bg-white transition-colors cursor-pointer">
                                    <input type="checkbox" 
                                           name="permissions[]" 
                                           value="{{ $p->id }}" 
                                           :checked="selectedPermissions.includes('{{ $p->id }}')"
                                           @change="toggleSinglePermission('{{ $p->id }}')"
                                           class="rounded text-sky-600 focus:ring-sky-500 mt-0.5">
                                    <div>
                                        <div class="font-semibold text-slate-900 text-[11px]">{{ $p->name }}</div>
                                        <code class="text-[9px] text-slate-400 font-mono">{{ $p->code }}</code>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                    <button type="button" 
                            @click="showCreateModal = false" 
                            class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold transition-all shadow-sm cursor-pointer">
                        Simpan Peran & Hak Akses &rarr;
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- MODAL 2: UBAH PERAN ANGGOTA TIM                       -->
    <!-- ===================================================== -->
    <div x-show="showMemberRoleModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         x-transition>
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4"
             @click.away="showMemberRoleModal = false">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-bold">
                        👤
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Ubah Peran Anggota</h3>
                        <p class="text-[11px] text-slate-500" x-text="editingMember ? editingMember.name : ''"></p>
                    </div>
                </div>
                <button type="button" @click="showMemberRoleModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form :action="memberRoleAction" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="_method" value="PUT">

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Pilih Peran Baru (Role) <span class="text-rose-500">*</span>
                    </label>
                    <select name="role_id" 
                            x-model="memberSelectedRoleId" 
                            required 
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-sky-500 bg-white">
                        @foreach($roles as $r)
                        <option value="{{ $r->id }}">[{{ $r->code }}] {{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                    <button type="button" 
                            @click="showMemberRoleModal = false" 
                            class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-sm cursor-pointer">
                        Perbarui Peran Anggota &rarr;
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- MODAL 3: DAFTARKAN ANGGOTA BARU                       -->
    <!-- ===================================================== -->
    <div x-show="showAddMemberModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         x-transition>
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-4"
             @click.away="showAddMemberModal = false">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-bold">
                        ➕
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Daftarkan Anggota Tim Baru</h3>
                        <p class="text-[11px] text-slate-500">Buat akun staf operasional dan tentukan perannya.</p>
                    </div>
                </div>
                <button type="button" @click="showAddMemberModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('settings.roles.members.store') }}" method="POST" class="space-y-3">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="Nama lengkap staf..." class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email Login <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" required placeholder="staf@agungtrans.co.id" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kata Sandi <span class="text-rose-500">*</span></label>
                        <input type="password" name="password" required placeholder="Minimal 6 karakter" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Peran (Role) <span class="text-rose-500">*</span></label>
                        <select name="role_id" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white">
                            @foreach($roles as $r)
                            <option value="{{ $r->id }}">[{{ $r->code }}] {{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">No. Telepon / WA</label>
                        <input type="text" name="phone" placeholder="0812..." class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jabatan (Job Title)</label>
                        <input type="text" name="job_title" placeholder="Staf Dokumen PPJK" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Departemen</label>
                        <input type="text" name="department" placeholder="Operasional" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                    <button type="button" @click="showAddMemberModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-sm cursor-pointer">Daftarkan Anggota &rarr;</button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
function roleCockpitApp() {
    return {
        activeTab: 'roles',
        showGuide: true,
        showCreateModal: false,
        showMemberRoleModal: false,
        showAddMemberModal: false,
        
        editingRole: null,
        editFormAction: '{{ route('settings.roles.store') }}',
        formRoleName: '',
        formRoleCode: '',
        formRoleDesc: '',
        
        searchPermission: '',
        selectedPermissions: [],
        
        editingMember: null,
        memberRoleAction: '',
        memberSelectedRoleId: '',

        openCreateModal() {
            this.editingRole = null;
            this.editFormAction = '{{ route('settings.roles.store') }}';
            this.formRoleName = '';
            this.formRoleCode = '';
            this.formRoleDesc = '';
            this.selectedPermissions = [];
            this.showCreateModal = true;
        },

        openEditModal(role, actionUrl, permIds) {
            this.editingRole = role;
            this.editFormAction = actionUrl;
            this.formRoleName = role.name;
            this.formRoleCode = role.code;
            this.formRoleDesc = role.description || '';
            this.selectedPermissions = permIds.map(String);
            this.showCreateModal = true;
        },

        openChangeRoleModal(member, actionUrl, currentRoleId) {
            this.editingMember = member;
            this.memberRoleAction = actionUrl;
            this.memberSelectedRoleId = currentRoleId;
            this.showMemberRoleModal = true;
        },

        toggleSinglePermission(id) {
            const strId = String(id);
            if (this.selectedPermissions.includes(strId)) {
                this.selectedPermissions = this.selectedPermissions.filter(i => i !== strId);
            } else {
                this.selectedPermissions.push(strId);
            }
        },

        toggleCategory(categoryIds) {
            const strIds = categoryIds.map(String);
            const allSelected = strIds.every(id => this.selectedPermissions.includes(id));
            if (allSelected) {
                this.selectedPermissions = this.selectedPermissions.filter(id => !strIds.includes(id));
            } else {
                strIds.forEach(id => {
                    if (!this.selectedPermissions.includes(id)) {
                        this.selectedPermissions.push(id);
                    }
                });
            }
        }
    };
}
</script>
@endpush
@endsection
