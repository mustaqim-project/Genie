@extends('layouts.app')

@section('title', 'Manajemen Peran & Hak Akses (RBAC)')

@section('content')
<div class="max-w-[1360px] mx-auto px-4 sm:px-6 space-y-6 pt-4 pb-28 lg:pb-12" x-data="roleCockpitApp()">

    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Toolbar Style)  -->
    <!-- ===================================================== -->
    <header class="rounded-[16px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
        <div>
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                <span class="text-black/70 dark:text-white/70 font-medium">Pengaturan</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 opacity-40"></i>
                <span class="text-black dark:text-white font-semibold">Peran & Hak Akses (RBAC)</span>
            </nav>
            <div class="flex items-center gap-2.5">
                <h1 class="text-[20px] sm:text-[22px] font-bold text-black dark:text-white tracking-tight flex items-center space-x-2">
                    <span>Manajemen Peran & Hak Akses (RBAC)</span>
                    <span class="px-2 py-0.5 rounded-[6px] bg-[#007AFF]/10 text-[#007AFF] text-[10px] font-mono font-bold uppercase">Multi-Tenant</span>
                </h1>
            </div>
            <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5 leading-snug">
                Kendalikan otoritas hak akses staf operasional dan klien AI pabean secara tersentralisasi dan granular.
            </p>
        </div>

        <!-- Toolbar Actions -->
        <div class="flex items-center gap-2.5 w-full sm:w-auto">
            <button type="button" 
                    @click="openCreateModal()"
                    class="btn-apple-filled w-full sm:w-auto">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Buat Role Custom</span>
            </button>
        </div>
    </header>

    <!-- ===================================================== -->
    <!-- 2. KPI SUMMARY (4 Flat Apple Standard Cards)          -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
        <!-- Tile 1: Total Role -->
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase tracking-wider font-semibold text-black/45 dark:text-white/45">Total Peran (Roles)</span>
                <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                    <i data-lucide="shield" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[22px] sm:text-[24px] font-bold tabular-nums text-black dark:text-white tracking-tight">{{ $roles->count() }}</span>
                <span class="text-[11px] font-medium text-black/45 dark:text-white/45">Preset & Custom</span>
            </div>
        </div>

        <!-- Tile 2: Anggota Tim Aktif -->
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase tracking-wider font-semibold text-black/45 dark:text-white/45">Anggota Tim Terdaftar</span>
                <div class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                    <i data-lucide="users" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[22px] sm:text-[24px] font-bold tabular-nums text-[#248A3D] dark:text-[#30D158] tracking-tight">{{ $members->count() }}</span>
                <span class="text-[11px] font-medium text-[#248A3D] dark:text-[#30D158]">Pengguna Aktif</span>
            </div>
        </div>

        <!-- Tile 3: Cakupan Permissions -->
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase tracking-wider font-semibold text-black/45 dark:text-white/45">Cakupan Izin Granular</span>
                <div class="w-8 h-8 rounded-[10px] bg-[#5856D6]/10 text-[#5856D6] flex items-center justify-center">
                    <i data-lucide="key" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[22px] sm:text-[24px] font-bold tabular-nums text-[#5856D6] dark:text-[#5E5CE6] tracking-tight">{{ collect($permissions)->flatten(1)->count() }}</span>
                <span class="text-[11px] font-medium text-[#5856D6] dark:text-[#5E5CE6]">{{ $permissions->count() }} Modul Sistem</span>
            </div>
        </div>

        <!-- Tile 4: Status Proteksi Keamanan -->
        <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] uppercase tracking-wider font-semibold text-black/45 dark:text-white/45">Tingkat Proteksi</span>
                <div class="w-8 h-8 rounded-[10px] bg-[#AF52DE]/10 text-[#AF52DE] flex items-center justify-center">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[16px] sm:text-[18px] font-bold text-[#8E44AD] dark:text-[#BF5AF2]">RBAC Controller</span>
                <span class="text-[11px] font-medium text-black/45 dark:text-white/45">Dual-Control</span>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 3. SEGMENTED NAVIGATION & VIEW SWITCHER               -->
    <!-- ===================================================== -->
    <div class="flex items-center justify-between gap-3 border-b border-black/5 dark:border-white/5 pb-3">
        <div class="overflow-x-auto pb-1 scrollbar-none">
            <div class="inline-flex p-1 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 text-[12px] font-medium whitespace-nowrap">
                <button type="button" 
                        @click="activeTab = 'roles'"
                        :class="activeTab === 'roles' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                        class="px-3.5 py-1.5 rounded-[8px] transition-all flex items-center gap-2 cursor-pointer">
                    <i data-lucide="shield" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                    <span>Daftar Peran (Roles)</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-[#007AFF]/10 text-[#007AFF]">{{ $roles->count() }}</span>
                </button>

                <button type="button" 
                        @click="activeTab = 'members'"
                        :class="activeTab === 'members' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                        class="px-3.5 py-1.5 rounded-[8px] transition-all flex items-center gap-2 cursor-pointer">
                    <i data-lucide="users" class="w-3.5 h-3.5 text-[#34C759]"></i>
                    <span>Anggota Tim & Pengguna</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">{{ $members->count() }}</span>
                </button>

                <button type="button" 
                        @click="activeTab = 'matrix'"
                        :class="activeTab === 'matrix' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-xs font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white'"
                        class="px-3.5 py-1.5 rounded-[8px] transition-all flex items-center gap-2 cursor-pointer">
                    <i data-lucide="grid" class="w-3.5 h-3.5 text-[#5856D6]"></i>
                    <span>Matriks Akses RBAC</span>
                </button>
            </div>
        </div>

        <button type="button" 
                @click="showGuide = !showGuide"
                class="btn-apple-plain !h-8 text-[12px]">
            <i data-lucide="info" class="w-3.5 h-3.5"></i>
            <span x-text="showGuide ? 'Sembunyikan Panduan' : 'Tampilkan Panduan Otoritas'"></span>
        </button>
    </div>

    <!-- Interactive Apple Callout: Panduan RBAC -->
    <div x-show="showGuide" x-transition class="rounded-[16px] bg-[#007AFF]/5 dark:bg-[#007AFF]/10 border border-[#007AFF]/20 p-4 sm:p-5 space-y-3">
        <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-[#007AFF]"></span>
            <h2 class="text-[11px] font-bold uppercase tracking-wider text-[#007AFF]">Panduan Tingkat Otoritas Hak Akses Peran</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-[12px]">
            <div class="rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 space-y-1 shadow-2xs">
                <span class="font-bold text-[#007AFF]">[R-ADM / R-OWN] Pimpinan & Admin</span>
                <p class="text-black/60 dark:text-white/60 leading-relaxed text-[11px]">Memiliki hak akses penuh tanpa batasan terhadap seluruh modul bisnis, laporan laba rugi, CEISA, dan approval pabean.</p>
            </div>
            <div class="rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 space-y-1 shadow-2xs">
                <span class="font-bold text-[#248A3D] dark:text-[#30D158]">[R-DOC / R-SPV] Tim Dokumen PPJK</span>
                <p class="text-black/60 dark:text-white/60 leading-relaxed text-[11px]">Mengelola pengapalan, ekstraksi packing list, draft PIB/PEB, dan pengajuan submit respon dokumen ke CEISA 4.0.</p>
            </div>
            <div class="rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 space-y-1 shadow-2xs">
                <span class="font-bold text-[#FF9500] dark:text-[#FF9F0A]">[R-FIN] Keuangan & Kasir</span>
                <p class="text-black/60 dark:text-white/60 leading-relaxed text-[11px]">Mengelola piutang invoice P19, pembayaran hutang vendor pelayaran/truk, rekonsiliasi kas & bank, dan rekap pajak.</p>
            </div>
            <div class="rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 space-y-1 shadow-2xs">
                <span class="font-bold text-[#AF52DE] dark:text-[#BF5AF2]">[R-TRF / R-FLD] Tim Traffic Lapangan</span>
                <p class="text-black/60 dark:text-white/60 leading-relaxed text-[11px]">Memantau fisik kontainer pelabuhan, pendampingan behandle jalur merah, koordinasi supir truk, dan upload Surat Jalan.</p>
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
                    $userCount = $role->users->count();
                @endphp
                <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 flex flex-col justify-between gap-4 shadow-sm hover:border-[#007AFF]/30 transition-all">
                    <div class="space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-[16px] font-bold text-black dark:text-white">{{ $role->name }}</h3>
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="font-mono text-[11px] font-bold px-2 py-0.5 rounded-[6px] bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70">
                                        {{ $role->code }}
                                    </span>
                                    @if ($isPreset)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#007AFF]/10 text-[#007AFF]">
                                            Preset Sistem
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#AF52DE]/10 text-[#AF52DE]">
                                            Role Custom
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 bg-black/[0.04] dark:bg-white/[0.06] px-2.5 py-1 rounded-[8px]">
                                {{ $userCount }} Staf
                            </span>
                        </div>

                        <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">
                            {{ $role->description ?? 'Peran operasional sistem kepabeanan & logistik GENIE.' }}
                        </p>

                        <!-- Permissions summary chips -->
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <span class="text-[10px] font-bold text-black/40 dark:text-white/40 block mb-1.5 uppercase">
                                Izin Akses Aktif ({{ count($permIds) }})
                            </span>
                            <div class="flex flex-wrap gap-1 max-h-24 overflow-y-auto">
                                @forelse($role->permissions->take(8) as $p)
                                    <span class="px-2 py-0.5 bg-black/[0.04] dark:bg-white/[0.06] rounded-[6px] text-[10px] font-mono text-black/70 dark:text-white/70 truncate max-w-[140px]" title="{{ $p->name }}">
                                        {{ $p->code }}
                                    </span>
                                @empty
                                    <span class="text-black/40 dark:text-white/40 italic text-[11px]">Belum ada hak akses terhubung</span>
                                @endforelse
                                @if(count($permIds) > 8)
                                    <span class="px-2 py-0.5 bg-[#007AFF]/10 rounded-[6px] text-[10px] font-bold text-[#007AFF]">
                                        +{{ count($permIds) - 8 }} lainnya
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Card Actions -->
                    <div class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-between">
                        <button type="button" 
                                @click="openEditModal({{ json_encode($role) }}, '{{ route('settings.roles.update', $role->id) }}', {{ json_encode($permIds) }})"
                                class="btn-apple-tinted !h-8 !px-3 !text-[11px]">
                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                            <span>Atur Hak Akses</span>
                        </button>

                        @if(!$isPreset)
                        <form action="{{ route('settings.roles.destroy', $role->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus role custom ini?');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-[11px] text-[#FF3B30] hover:underline font-semibold p-1 cursor-pointer" title="Hapus Role">
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
    <div x-show="activeTab === 'members'" class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm overflow-hidden flex flex-col space-y-4">
        <div class="p-4 sm:p-5 border-b border-black/5 dark:border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-[14px] font-bold text-black dark:text-white">Daftar Anggota Tim & Otoritas Peran</h3>
                <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Kelola penugasan peran bagi staf operasional, tim dokumen, dan finance.</p>
            </div>
            <button type="button" 
                    @click="showAddMemberModal = true"
                    class="btn-apple-filled !h-9 text-[12px]">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                <span>Daftarkan Anggota Baru</span>
            </button>
        </div>

        <div class="table-responsive flex-1">
            <table class="w-full text-left text-[13px]">
                <thead class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider bg-black/[0.02] dark:bg-white/[0.02] border-b border-black/5 dark:border-white/10">
                    <tr>
                        <th class="p-3.5">Nama & Kontak</th>
                        <th class="p-3.5">Jabatan / Departemen</th>
                        <th class="p-3.5">Peran Aktif</th>
                        <th class="p-3.5">Status Akun</th>
                        <th class="p-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5">
                    @forelse($members as $m)
                    @php $firstRole = $m->roles->first(); @endphp
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                        <td class="p-3.5">
                            <div class="font-bold text-black dark:text-white">{{ $m->name }}</div>
                            <div class="text-[11px] text-black/45 dark:text-white/45 font-mono">{{ $m->email }} {{ $m->phone ? '• ' . $m->phone : '' }}</div>
                        </td>
                        <td class="p-3.5 text-black/80 dark:text-white/80">
                            <div class="font-medium">{{ $m->job_title ?? 'Staf Operasional' }}</div>
                            <div class="text-[10px] text-black/45 dark:text-white/45">{{ $m->department ?? 'Operasional Logistik' }}</div>
                        </td>
                        <td class="p-3.5">
                            @if($firstRole)
                                <span class="px-2.5 py-1 rounded-[6px] text-[11px] font-bold bg-[#007AFF]/10 text-[#007AFF] font-mono">
                                    [{{ $firstRole->code }}] {{ $firstRole->name }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-[4px] text-[11px] bg-black/[0.05] dark:bg-white/[0.08] text-black/45 dark:text-white/45 italic">Belum Ada Peran</span>
                            @endif
                        </td>
                        <td class="p-3.5">
                            @if($m->is_active)
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">Aktif</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-black/[0.08] dark:bg-white/[0.1] text-black/50 dark:text-white/50">Non-Aktif</span>
                            @endif
                        </td>
                        <td class="p-3.5 text-right space-x-2">
                            <button type="button" 
                                    @click="openChangeRoleModal({{ json_encode($m) }}, '{{ route('settings.roles.members.role', $m->id) }}', '{{ $firstRole?->id ?? '' }}')"
                                    class="btn-apple-tinted !h-7 !px-2.5 !text-[11px]">
                                Ubah Peran
                            </button>
                            @if($m->email !== 'admin@agungtrans.co.id' && $m->id !== auth()->id())
                            <form action="{{ route('settings.roles.members.destroy', $m->id) }}" method="POST" onsubmit="return confirm('Hapus anggota tim ini?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-apple-gray !h-7 !px-2 !text-[11px] text-[#FF3B30] hover:text-[#FF3B30]">
                                    Hapus
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-black/40 dark:text-white/40">Belum ada anggota tim terdaftar.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- TAB 3: MATRIKS AKSES RBAC (Matrix Table)              -->
    <!-- ===================================================== -->
    <div x-show="activeTab === 'matrix'" class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm overflow-hidden space-y-4 p-5">
        <div>
            <h3 class="text-[14px] font-bold text-black dark:text-white">Matriks Hak Akses Granular Seluruh Peran (Role vs Permission)</h3>
            <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Tinjau hak akses yang dimiliki tiap peran di seluruh modul sistem kepabeanan, logistik, dan finance.</p>
        </div>

        <div class="overflow-x-auto border border-black/5 dark:border-white/10 rounded-[12px]">
            <table class="w-full text-left text-[12px]">
                <thead class="bg-black/[0.02] dark:bg-white/[0.02] border-b border-black/5 dark:border-white/10 text-black dark:text-white font-bold">
                    <tr>
                        <th class="p-3 min-w-[240px]">Modul & Izin Sistem</th>
                        @foreach($roles as $r)
                        <th class="p-3 text-center min-w-[100px] border-l border-black/5 dark:border-white/10">
                            <span class="block font-bold text-black dark:text-white font-mono">{{ $r->code }}</span>
                            <span class="text-[10px] font-normal text-black/50 dark:text-white/50 block truncate max-w-[100px]">{{ $r->name }}</span>
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5">
                    @foreach($permissions as $module => $perms)
                    <tr class="bg-black/[0.03] dark:bg-white/[0.04] font-bold text-black dark:text-white">
                        <td colspan="{{ $roles->count() + 1 }}" class="p-2.5 text-[11px] uppercase tracking-wider text-[#007AFF]">
                            📁 Modul: {{ $module }}
                        </td>
                    </tr>
                    @foreach($perms as $p)
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                        <td class="p-2.5 font-medium text-black/80 dark:text-white/80">
                            <div>{{ $p->name }}</div>
                            <code class="text-[10px] text-black/40 dark:text-white/40 font-mono">{{ $p->code }}</code>
                        </td>
                        @foreach($roles as $r)
                        @php $hasPerm = $r->hasPermission($p->code); @endphp
                        <td class="p-2.5 text-center border-l border-black/5 dark:border-white/5">
                            @if($hasPerm)
                                <span class="w-5 h-5 rounded-full bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] inline-flex items-center justify-center font-bold text-xs" title="Diizinkan">✓</span>
                            @else
                                <span class="text-black/20 dark:text-white/20 font-mono text-xs" title="Tidak Memiliki Akses">—</span>
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
         class="fixed inset-0 z-[200] overflow-y-auto bg-black/60 backdrop-blur-md flex items-center justify-center p-4"
         x-transition>
        <div class="app-modal-dialog app-modal-dialog-xl rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl p-6 text-[13px] space-y-5"
             @click.away="showCreateModal = false">
            
            <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                        <i data-lucide="shield" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-[16px] font-bold text-black dark:text-white" x-text="editingRole ? 'Edit Peran: ' + editingRole.name : 'Buat Role Custom Baru'"></h3>
                        <p class="text-[11px] text-black/50 dark:text-white/50">Tentukan nama peran dan pilih hak akses spesifik yang diizinkan.</p>
                    </div>
                </div>
                <button type="button" @click="showCreateModal = false" class="p-1.5 rounded-[8px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white hover:bg-black/[0.05] dark:hover:bg-white/[0.08] transition-colors cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="editFormAction" method="POST" class="space-y-4">
                @csrf
                <template x-if="editingRole">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[12px] font-semibold text-black dark:text-white mb-1">
                            Nama Peran (Role Name) <span class="text-[#FF3B30]">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               x-model="formRoleName" 
                               required 
                               placeholder="Contoh: Operasional Impor Laut" 
                               class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#007AFF]">
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-black dark:text-white mb-1">
                            Kode Peran (Role Code)
                        </label>
                        <input type="text" 
                               name="code" 
                               x-model="formRoleCode" 
                               :disabled="editingRole !== null"
                               placeholder="Contoh: R-SEAOPS" 
                               class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white text-[13px] font-mono uppercase focus:outline-none focus:border-[#007AFF] disabled:opacity-50">
                    </div>
                </div>

                <div>
                    <label class="block text-[12px] font-semibold text-black dark:text-white mb-1">
                        Deskripsi Tanggung Jawab
                    </label>
                    <textarea name="description" 
                              x-model="formRoleDesc" 
                              rows="2" 
                              placeholder="Deskripsi tugas dan tanggung jawab peran..." 
                              class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#007AFF]"></textarea>
                </div>

                <!-- Permission Selector with Filter Search -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-[12px] font-semibold text-black dark:text-white">
                            Pilih Hak Akses Granular (Permissions)
                        </label>
                        <input type="text" 
                               x-model="searchPermission" 
                               placeholder="Cari izin..." 
                               class="px-2.5 py-1 rounded-[8px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white text-[11px] w-40">
                    </div>

                    <div class="border border-black/10 dark:border-white/10 rounded-[12px] max-h-60 overflow-y-auto divide-y divide-black/5 dark:divide-white/5 p-2 bg-black/[0.02] dark:bg-white/[0.02]">
                        @foreach($permissions as $module => $perms)
                        @php $moduleIds = $perms->pluck('id')->toArray(); @endphp
                        <div class="py-2.5 px-2">
                            <div class="flex items-center justify-between mb-1.5 pb-1 border-b border-black/5 dark:border-white/5">
                                <span class="font-bold text-[11px] text-[#007AFF] uppercase">📁 {{ $module }}</span>
                                <button type="button" 
                                        @click="toggleCategory({{ json_encode($moduleIds) }})" 
                                        class="text-[10px] text-[#007AFF] hover:underline font-semibold cursor-pointer">
                                    Pilih/Batal Semua
                                </button>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 mt-1">
                                @foreach($perms as $p)
                                <label x-show="!searchPermission || '{{ strtolower($p->name . ' ' . $p->code) }}'.includes(searchPermission.toLowerCase())" 
                                       class="flex items-start space-x-2 text-[12px] text-black/80 dark:text-white/80 p-1.5 rounded-[8px] hover:bg-white dark:hover:bg-[#2C2C2E] transition-colors cursor-pointer">
                                    <input type="checkbox" 
                                           name="permissions[]" 
                                           value="{{ $p->id }}" 
                                           :checked="selectedPermissions.includes('{{ $p->id }}')"
                                           @change="toggleSinglePermission('{{ $p->id }}')"
                                           class="rounded text-[#007AFF] focus:ring-[#007AFF] mt-0.5">
                                    <div>
                                        <div class="font-semibold text-black dark:text-white text-[11px]">{{ $p->name }}</div>
                                        <code class="text-[9px] text-black/40 dark:text-white/40 font-mono">{{ $p->code }}</code>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-end space-x-2">
                    <button type="button" 
                            @click="showCreateModal = false" 
                            class="btn-apple-gray !h-9 text-[12px]">
                        Batal
                    </button>
                    <button type="submit" 
                            class="btn-apple-filled !h-9 text-[12px]">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Peran & Hak Akses</span>
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
         class="fixed inset-0 z-[200] overflow-y-auto bg-black/60 backdrop-blur-md flex items-center justify-center p-4"
         x-transition>
        <div class="app-modal-dialog app-modal-dialog-md rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl p-6 text-[13px] space-y-4"
             @click.away="showMemberRoleModal = false">
            
            <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                        <i data-lucide="user-check" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-[16px] font-bold text-black dark:text-white">Ubah Peran Anggota</h3>
                        <p class="text-[11px] text-black/50 dark:text-white/50" x-text="editingMember ? editingMember.name : ''"></p>
                    </div>
                </div>
                <button type="button" @click="showMemberRoleModal = false" class="p-1.5 rounded-[8px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white hover:bg-black/[0.05] dark:hover:bg-white/[0.08] transition-colors cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="memberRoleAction" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="_method" value="PUT">

                <div>
                    <label class="block text-[12px] font-semibold text-black dark:text-white mb-1">
                        Pilih Peran Baru (Role) <span class="text-[#FF3B30]">*</span>
                    </label>
                    <select name="role_id" 
                            x-model="memberSelectedRoleId" 
                            required 
                            class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#007AFF]">
                        @foreach($roles as $r)
                        <option value="{{ $r->id }}">[{{ $r->code }}] {{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-end space-x-2">
                    <button type="button" 
                            @click="showMemberRoleModal = false" 
                            class="btn-apple-gray !h-9 text-[12px]">
                        Batal
                    </button>
                    <button type="submit" 
                            class="btn-apple-filled !bg-[#34C759] hover:!bg-[#248A3D] !h-9 text-[12px]">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Perbarui Peran Anggota</span>
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
         class="fixed inset-0 z-[200] overflow-y-auto bg-black/60 backdrop-blur-md flex items-center justify-center p-4"
         x-transition>
        <div class="app-modal-dialog app-modal-dialog-md rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl p-6 text-[13px] space-y-4"
             @click.away="showAddMemberModal = false">
            
            <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-[16px] font-bold text-black dark:text-white">Daftarkan Anggota Tim Baru</h3>
                        <p class="text-[11px] text-black/50 dark:text-white/50">Buat akun staf operasional dan tentukan perannya.</p>
                    </div>
                </div>
                <button type="button" @click="showAddMemberModal = false" class="p-1.5 rounded-[8px] text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white hover:bg-black/[0.05] dark:hover:bg-white/[0.08] transition-colors cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('settings.roles.members.store') }}" method="POST" class="space-y-3">
                @csrf

                <div>
                    <label class="block text-[12px] font-semibold text-black dark:text-white mb-1">Nama Lengkap <span class="text-[#FF3B30]">*</span></label>
                    <input type="text" name="name" required placeholder="Nama lengkap staf..." class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#007AFF]">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[12px] font-semibold text-black dark:text-white mb-1">Email Login <span class="text-[#FF3B30]">*</span></label>
                        <input type="email" name="email" required placeholder="staf@agungtrans.co.id" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#007AFF]">
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-black dark:text-white mb-1">Kata Sandi <span class="text-[#FF3B30]">*</span></label>
                        <input type="password" name="password" required placeholder="Minimal 6 karakter" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#007AFF]">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[12px] font-semibold text-black dark:text-white mb-1">Peran (Role) <span class="text-[#FF3B30]">*</span></label>
                        <select name="role_id" required class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#007AFF]">
                            @foreach($roles as $r)
                            <option value="{{ $r->id }}">[{{ $r->code }}] {{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-black dark:text-white mb-1">No. Telepon / WA</label>
                        <input type="text" name="phone" placeholder="0812..." class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#007AFF]">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[12px] font-semibold text-black dark:text-white mb-1">Jabatan (Job Title)</label>
                        <input type="text" name="job_title" placeholder="Staf Dokumen PPJK" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#007AFF]">
                    </div>
                    <div>
                        <label class="block text-[12px] font-semibold text-black dark:text-white mb-1">Departemen</label>
                        <input type="text" name="department" placeholder="Operasional" class="w-full px-3 py-2 rounded-[10px] border border-black/10 dark:border-white/10 bg-white dark:bg-[#2C2C2E] text-black dark:text-white text-[13px] focus:outline-none focus:border-[#007AFF]">
                    </div>
                </div>

                <div class="pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-end space-x-2">
                    <button type="button" @click="showAddMemberModal = false" class="btn-apple-gray !h-9 text-[12px]">Batal</button>
                    <button type="submit" class="btn-apple-filled !bg-[#34C759] hover:!bg-[#248A3D] !h-9 text-[12px]">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Daftarkan Anggota</span>
                    </button>
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
            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
        },

        openEditModal(role, actionUrl, permIds) {
            this.editingRole = role;
            this.editFormAction = actionUrl;
            this.formRoleName = role.name;
            this.formRoleCode = role.code;
            this.formRoleDesc = role.description || '';
            this.selectedPermissions = permIds.map(String);
            this.showCreateModal = true;
            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
        },

        openChangeRoleModal(member, actionUrl, currentRoleId) {
            this.editingMember = member;
            this.memberRoleAction = actionUrl;
            this.memberSelectedRoleId = currentRoleId;
            this.showMemberRoleModal = true;
            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
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
