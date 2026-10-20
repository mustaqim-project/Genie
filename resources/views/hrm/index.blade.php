@extends('layouts.app')

@section('title', 'HRM - Human Resource Management | Genie ERP')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 pt-6 pb-12"
     x-data="{
        activeTab: '{{ $activeTab ?? 'employees' }}',
        showCreateEmployeeModal: false,
        showClockModal: false,
        showConsentModal: false,
        showLeaveModal: false,
        showOvertimeModal: false,
        showCashAdvanceModal: false,
        showPayrollModal: false,
        showCreateAssetModal: false,
        showAssignAssetModal: false,
        selectedAssetId: '',
        selectedAssetName: '',
        gpsLat: -6.2297,
        gpsLng: 106.8294,
        gpsStatus: 'Siap mengambil koordinat GPS',
        getLocation() {
            if (navigator.geolocation) {
                this.gpsStatus = 'Mendeteksi titik koordinat akurat...';
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        this.gpsLat = position.coords.latitude;
                        this.gpsLng = position.coords.longitude;
                        this.gpsStatus = 'GPS Terkunci: ' + this.gpsLat.toFixed(4) + ', ' + this.gpsLng.toFixed(4);
                    },
                    (error) => {
                        this.gpsStatus = 'Izin GPS ditolak, gunakan koordinat default kantor/TPS';
                    }
                );
            } else {
                this.gpsStatus = 'Browser tidak mendukung GPS Geolocation';
            }
        }
     }">

    <!-- Page Header & Title -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="text-xs text-slate-500 font-medium mb-1">
                <a href="{{ route('dashboard') }}" class="hover:underline text-slate-500">Dashboard</a>
                <span class="text-slate-400 mx-1">/</span>
                <span class="text-slate-800 font-semibold">HRM & Kepegawaian</span>
            </div>
            <div class="flex items-center space-x-2">
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Human Resource Management</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800 border border-sky-200">
                    Genie ERP
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Manajemen staf forwarder/PPJK, kontrak PKWT/PKWTT, presensi GPS & verifikasi wajah (<span class="font-semibold text-slate-700">UU PDP No. 27/2022</span>), alih tugas PIC cuti, dan payroll bulanan.
            </p>
        </div>

        <!-- Header Actions -->
        <div class="flex items-center flex-wrap gap-2.5">
            <button type="button"
                    @click="showClockModal = true; getLocation()"
                    class="min-h-[44px] px-4 py-2 bg-white text-slate-700 border border-slate-200/80 rounded-xl text-xs font-semibold hover:bg-slate-50 shadow-sm transition-all flex items-center space-x-2 focus:outline-none focus:ring-2 focus:ring-sky-500/20">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>Presensi GPS & Wajah</span>
            </button>

            <button type="button"
                    @click="showCreateEmployeeModal = true"
                    class="min-h-[44px] px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-semibold shadow-sm shadow-sky-600/20 transition-all flex items-center space-x-2 focus:outline-none focus:ring-2 focus:ring-sky-500/40">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah Pegawai Baru</span>
            </button>
        </div>
    </div>

    <!-- Flash Feedback Alerts -->
    @if(session('success'))
        <div class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between shadow-sm animate-fadeIn">
            <div class="flex items-center space-x-2.5">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 p-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-5 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center justify-between shadow-sm animate-fadeIn">
            <div class="flex items-center space-x-2.5">
                <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-rose-500 hover:text-rose-700 p-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @endif

    @if(session('warning'))
        <div class="mb-5 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs flex items-center justify-between shadow-sm animate-fadeIn">
            <div class="flex items-center space-x-2.5">
                <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span class="font-medium">{{ session('warning') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-amber-500 hover:text-amber-700 p-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @endif

    <!-- Bento Grid Metrics (Apple HIG Architecture) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4 mb-6">
        <!-- Metric 1: Kehadiran Radar -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Hadir Hari Ini</span>
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            </div>
            <div class="my-2">
                <span class="text-2xl font-bold text-emerald-600 tracking-tight">{{ $metrics['attendance_rate'] ?? 100 }}%</span>
                <span class="text-xs text-slate-500 block font-normal mt-0.5">
                    {{ $metrics['today_clock_ins'] ?? 0 }} dari {{ $metrics['total_employees'] ?? 0 }} staf aktif
                </span>
            </div>
            <div class="text-[10px] text-slate-400 font-medium">Valid Geofence Pelabuhan TPS & Kantor</div>
        </div>

        <!-- Metric 2: Kontrak PKWT Warning -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Kontrak PKWT Segera Jatuh Tempo</span>
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="my-2">
                <span class="text-2xl font-bold {{ ($metrics['expiring_contracts'] ?? 0) > 0 ? 'text-amber-600' : 'text-slate-800' }} tracking-tight">
                    {{ $metrics['expiring_contracts'] ?? 0 }}
                </span>
                <span class="text-xs text-slate-500 block font-normal mt-0.5">Peringatan 30 hari ke depan</span>
            </div>
            <div class="text-[10px] text-slate-400 font-medium">Kepatuhan PP No. 35/2021</div>
        </div>

        <!-- Metric 3: Cuti & Backup PIC Siaga -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Cuti & Auto-PIC</span>
                <svg class="w-4 h-4 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div class="my-2">
                <span class="text-2xl font-bold text-sky-600 tracking-tight">{{ $metrics['pending_leaves'] ?? 0 }}</span>
                <span class="text-xs text-slate-500 block font-normal mt-0.5">Permohonan cuti menunggu</span>
            </div>
            <div class="text-[10px] text-slate-400 font-medium">Alih tugas otomatis ke backup PIC</div>
        </div>

        <!-- Metric 4: Status Payroll Bulanan -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Payroll Bulanan</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                    {{ $metrics['latest_payroll']?->status ?? 'DRAFT' }}
                </span>
            </div>
            <div class="my-2">
                <span class="text-2xl font-bold text-slate-900 tracking-tight">
                    {{ $metrics['latest_payroll'] ? 'Rp ' . number_format($metrics['latest_payroll']->total_net, 0, ',', '.') : 'Rp 0' }}
                </span>
                <span class="text-xs text-slate-500 block font-normal mt-0.5">Periode: {{ $metrics['latest_payroll']?->period ?? date('Y-m') }}</span>
            </div>
            <div class="text-[10px] text-slate-400 font-medium">Gaji pokok + tunjangan operasional</div>
        </div>
    </div>

    <!-- Apple HIG Segmented Control (Pills with Min 44px Touch Target) -->
    <div class="bg-slate-200/70 p-1.5 rounded-2xl mb-6 overflow-x-auto no-scrollbar shadow-inner">
        <div class="flex items-center space-x-1 min-w-max">
            <button type="button"
                    @click="activeTab = 'employees'"
                    :class="activeTab === 'employees' ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                    class="min-h-[44px] px-4 py-2 rounded-xl text-xs transition-all flex items-center space-x-2 focus:outline-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span>Pegawai & Kontrak</span>
                <span class="ml-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold"
                      :class="activeTab === 'employees' ? 'bg-sky-100 text-sky-800' : 'bg-slate-300 text-slate-700'">
                    {{ $employees->total() }}
                </span>
            </button>

            <button type="button"
                    @click="activeTab = 'attendance'"
                    :class="activeTab === 'attendance' ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                    class="min-h-[44px] px-4 py-2 rounded-xl text-xs transition-all flex items-center space-x-2 focus:outline-none">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <span>Presensi Geofence & Wajah</span>
            </button>

            <button type="button"
                    @click="activeTab = 'leaves'"
                    :class="activeTab === 'leaves' ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                    class="min-h-[44px] px-4 py-2 rounded-xl text-xs transition-all flex items-center space-x-2 focus:outline-none">
                <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Cuti & Backup PIC</span>
                @if(($metrics['pending_leaves'] ?? 0) > 0)
                    <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                @endif
            </button>

            <button type="button"
                    @click="activeTab = 'overtime'"
                    :class="activeTab === 'overtime' ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                    class="min-h-[44px] px-4 py-2 rounded-xl text-xs transition-all flex items-center space-x-2 focus:outline-none">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Lembur & Kasbon</span>
            </button>

            <button type="button"
                    @click="activeTab = 'assets'"
                    :class="activeTab === 'assets' ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                    class="min-h-[44px] px-4 py-2 rounded-xl text-xs transition-all flex items-center space-x-2 focus:outline-none">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span>Aset Inventaris</span>
            </button>

            <button type="button"
                    @click="activeTab = 'payroll'"
                    :class="activeTab === 'payroll' ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                    class="min-h-[44px] px-4 py-2 rounded-xl text-xs transition-all flex items-center space-x-2 focus:outline-none">
                <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Payroll Engine</span>
            </button>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 1: PEGAWAI & KONTRAK KERJA (PKWT / PKWTT)                             -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'employees'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <!-- Filter Bar -->
            <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <form method="GET" action="{{ route('hrm.index') }}" class="flex items-center flex-wrap gap-2 w-full sm:w-auto">
                    <input type="hidden" name="tab" value="employees">
                    <div class="relative w-full sm:w-64">
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Cari NIK, Nama, Posisi..."
                               class="w-full text-xs rounded-xl border border-slate-200/80 pl-9 pr-3 py-2 focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 outline-none">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>

                    <select name="team" onchange="this.form.submit()" class="text-xs rounded-xl border border-slate-200/80 px-3 py-2 bg-white text-slate-600 focus:outline-none">
                        <option value="">Semua Divisi / Tim</option>
                        @foreach($teams as $t)
                            <option value="{{ $t }}" {{ request('team') == $t ? 'selected' : '' }}>Tim {{ $t }}</option>
                        @endforeach
                    </select>

                    @if(request('search') || request('team'))
                        <a href="{{ route('hrm.index', ['tab' => 'employees']) }}" class="text-xs text-rose-600 hover:underline px-2 py-1">Reset</a>
                    @endif
                </form>

                <div class="text-xs text-slate-500 font-medium">
                    Total: <span class="font-bold text-slate-900">{{ $employees->total() }}</span> Karyawan Terdaftar
                </div>
            </div>

            <!-- Table Desktop (Hidden on mobile) -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">Nama Pegawai & Jabatan</th>
                            <th class="py-3 px-4">Divisi / Tim</th>
                            <th class="py-3 px-4">Kontrak Kerja</th>
                            <th class="py-3 px-4">Gaji Pokok</th>
                            <th class="py-3 px-4">Biometrik Wajah</th>
                            <th class="py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($employees as $emp)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-900">{{ $emp->full_name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $emp->nik }} &bull; {{ $emp->position }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                        {{ $emp->team }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    @php $latestCtr = $emp->latestContract; @endphp
                                    @if($latestCtr)
                                        <div class="font-semibold text-slate-700">{{ $latestCtr->kind }}</div>
                                        <div class="text-[10px] text-slate-400">
                                            {{ $latestCtr->end_date ? 'S/d ' . $latestCtr->end_date->format('d M Y') : 'Karyawan Tetap' }}
                                        </div>
                                    @else
                                        <span class="text-slate-400 italic">Belum Ada</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 font-mono font-semibold text-slate-800">
                                    {{ $emp->formatted_salary }}
                                </td>
                                <td class="py-3 px-4">
                                    @if($emp->hasValidBiometricConsent())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            ✓ UU PDP Sah
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            Belum Consent
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $emp->status === 'ACTIVE' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $emp->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">Tidak ada data pegawai yang sesuai.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards Presentation (<768px) -->
            <div class="sm:hidden divide-y divide-slate-100">
                @forelse($employees as $emp)
                    <div class="p-4 flex flex-col space-y-2">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="font-bold text-slate-900 text-sm">{{ $emp->full_name }}</div>
                                <div class="text-xs text-slate-500">{{ $emp->position }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $emp->nik }}</div>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                {{ $emp->team }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-xs pt-1">
                            <span class="font-mono text-slate-700 font-semibold">{{ $emp->formatted_salary }}</span>
                            @if($emp->hasValidBiometricConsent())
                                <span class="text-[10px] text-emerald-600 font-medium">✓ Biometrik UU PDP</span>
                            @else
                                <span class="text-[10px] text-rose-500 font-medium">Perlu Consent Wajah</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-slate-400 text-xs">Belum ada data pegawai.</div>
                @endforelse
            </div>

            <!-- Pagination Links -->
            @if($employees->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $employees->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 2: PRESENSI GEOFENCE GPS & BIOMETRIK WAJAH (UU PDP)                   -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'attendance'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Left: Radar Titik Geofencing TPS & Kantor -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center space-x-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Radar Geofencing Aktif</span>
                        </h3>
                        <span class="text-[10px] text-slate-400 font-medium">Haversine GPS Engine</span>
                    </div>
                    <p class="text-xs text-slate-500 mb-4">
                        Staf lapangan & operasional hanya dapat absen jika berada di dalam batas radius resmi berikut:
                    </p>

                    <div class="space-y-3">
                        @foreach($workLocations as $loc)
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-xs text-slate-800">{{ $loc->name }}</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Radius {{ $loc->radius_m }}m
                                    </span>
                                </div>
                                <div class="text-[10px] text-slate-400 font-mono mt-1">
                                    Lat: {{ number_format($loc->lat, 4) }}, Lng: {{ number_format($loc->lng, 4) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- UU PDP Consent Notice -->
                <div class="mt-4 p-3.5 rounded-xl bg-sky-50 border border-sky-200 text-xs">
                    <div class="flex items-start space-x-2">
                        <svg class="w-4 h-4 text-sky-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <span class="font-bold text-sky-900 block mb-0.5">Kepatuhan UU PDP No. 27/2022</span>
                            <span class="text-[11px] text-sky-800 leading-relaxed block">
                                Pemrosesan data biometrik pengenalan wajah wajib memiliki persetujuan eksplisit karyawan. Staf berhak mencabut persetujuan kapan saja.
                            </span>
                            <button type="button" @click="showConsentModal = true" class="mt-2 text-[11px] text-sky-700 font-bold hover:underline inline-flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Isi Formulir Persetujuan Biometrik Pegawai</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Log Presensi Masuk / Pulang Terbaru -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <span class="font-bold text-xs text-slate-800">Log Presensi Face Recognition & GPS Hari Ini</span>
                    <span class="text-[10px] text-slate-400">{{ count($recentAttendances) }} rekaman terakhir</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-100">
                            <tr>
                                <th class="py-3 px-4">Waktu</th>
                                <th class="py-3 px-4">Nama Pegawai</th>
                                <th class="py-3 px-4">Jenis</th>
                                <th class="py-3 px-4">Skor Wajah</th>
                                <th class="py-3 px-4">Status Geofence</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($recentAttendances as $att)
                                <tr class="hover:bg-slate-50/60">
                                    <td class="py-3 px-4 font-mono text-slate-600">
                                        {{ $att->at->format('H:i:s') }}
                                        <div class="text-[10px] text-slate-400">{{ $att->at->format('d M Y') }}</div>
                                    </td>
                                    <td class="py-3 px-4 font-semibold text-slate-900">
                                        {{ $att->employee->full_name }}
                                        <div class="text-[10px] text-slate-400 font-normal">{{ $att->employee->team }}</div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $att->kind === 'CLOCK_IN' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                                            {{ $att->kind === 'CLOCK_IN' ? 'MASUK' : 'PULANG' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 font-mono">
                                        <span class="text-emerald-600 font-bold">{{ $att->face_match_percentage }}</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($att->valid)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                ✓ Valid ({{ $att->location?->name ?? 'Radius Geofence' }})
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                                ⚠ Luar Radius
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400">Belum ada presensi masuk hari ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 3: CUTI & ALIH TUGAS OTOMATIS PIC OPERASIONAL FORWARDER               -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'leaves'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
        <!-- Banner Kontinuitas SLA Forwarder -->
        <div class="mb-5 p-4 rounded-2xl bg-sky-50 border border-sky-200 text-sky-950 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-sm">
            <div class="flex items-start space-x-3">
                <div class="w-8 h-8 rounded-xl bg-sky-600 text-white flex items-center justify-center flex-shrink-0 font-bold">
                    PIC
                </div>
                <div>
                    <h3 class="text-xs font-bold text-sky-950">Aturan Logistik Forwarder: Auto-Reassignment PIC</h3>
                    <p class="text-xs text-sky-800 mt-0.5">
                        Saat permohonan cuti disetujui, sistem Genie ERP otomatis mengalihkan tugas PIC operasional shipment (<span class="font-semibold">PIB, PEB, Delivery Order, Behandle</span>) ke <span class="font-semibold">Backup PIC</span> yang terdaftar. SLA pabean tidak akan terputus.
                    </p>
                </div>
            </div>
            <button type="button" @click="showLeaveModal = true" class="min-h-[44px] px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold shadow-xs transition-all whitespace-nowrap inline-flex items-center gap-1.5 active:scale-95">
                <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Ajukan Cuti Baru</span>
            </button>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <span class="font-bold text-xs text-slate-800">Daftar Pengajuan Cuti & Izin Karyawan</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">Nama Pegawai</th>
                            <th class="py-3 px-4">Jenis Cuti</th>
                            <th class="py-3 px-4">Tanggal Mulai - Selesai</th>
                            <th class="py-3 px-4">Durasi Hari</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Aksi Approval</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($leaveRequests as $lr)
                            <tr class="hover:bg-slate-50/60">
                                <td class="py-3 px-4 font-semibold text-slate-900">
                                    {{ $lr->employee->full_name }}
                                    <div class="text-[10px] text-slate-400 font-normal">Tim: {{ $lr->employee->team }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                                        {{ $lr->kind }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-600">
                                    {{ $lr->from_date->format('d M Y') }} s/d {{ $lr->to_date->format('d M Y') }}
                                </td>
                                <td class="py-3 px-4 font-bold text-slate-800">
                                    {{ $lr->duration_days }} Hari
                                </td>
                                <td class="py-3 px-4">
                                    @if($lr->status === 'APPROVED')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            DISETUJUI
                                        </span>
                                    @elseif($lr->status === 'REJECTED')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                            DITOLAK
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                            MENUNGGU
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    @if($lr->isPending())
                                        <div class="flex items-center justify-end space-x-1.5">
                                            <form method="POST" action="{{ route('hrm.leaves.approve', $lr->id) }}">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[11px] font-semibold transition-all">
                                                    Setujui & Alihkan PIC
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('hrm.leaves.reject', $lr->id) }}">
                                                @csrf
                                                <button type="submit" class="px-2 py-1.5 bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-600 rounded-lg text-[11px] font-semibold transition-all">
                                                    Tolak
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-[10px] text-slate-400">Diproses oleh {{ $lr->approver?->name ?? 'Admin' }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">Tidak ada pengajuan cuti yang tertunda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 4: LEMBUR & KASBON PEGAWAI                                            -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'overtime'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Lembur Operasional Pelabuhan -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                        <span class="font-bold text-xs text-slate-800">Lembur Behandle & Operasional Lapangan TPS</span>
                        <button type="button" @click="showOvertimeModal = true" class="text-xs font-semibold text-sky-600 hover:text-sky-800 transition-colors inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Catat Lembur</span>
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-100">
                                <tr>
                                    <th class="py-3 px-4">Pegawai</th>
                                    <th class="py-3 px-4">Tanggal</th>
                                    <th class="py-3 px-4">Durasi Jam</th>
                                    <th class="py-3 px-4">Status</th>
                                    <th class="py-3 px-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($overtimeRequests as $or)
                                    <tr class="hover:bg-slate-50/60">
                                        <td class="py-3 px-4 font-semibold text-slate-900">{{ $or->employee->full_name }}</td>
                                        <td class="py-3 px-4 font-mono text-slate-600">{{ $or->work_date->format('d M Y') }}</td>
                                        <td class="py-3 px-4 font-bold text-slate-800">{{ $or->hours }} Jam</td>
                                        <td class="py-3 px-4">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $or->status === 'APPROVED' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                                {{ $or->status }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            @if($or->isPending())
                                                <form method="POST" action="{{ route('hrm.overtime.approve', $or->id) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="px-2 py-1 bg-emerald-600 text-white rounded text-[10px] font-semibold">Setujui</button>
                                                </form>
                                            @else
                                                <span class="text-[10px] text-slate-400">Selesai</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-6 text-center text-slate-400">Belum ada pengajuan lembur.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Kasbon & Cicilan Karyawan -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                        <span class="font-bold text-xs text-slate-800">Kasbon Karyawan & Cicilan Payroll</span>
                        <button type="button" @click="showCashAdvanceModal = true" class="text-xs font-semibold text-sky-600 hover:text-sky-800 transition-colors inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Kasbon Baru</span>
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-100">
                                <tr>
                                    <th class="py-3 px-4">Pegawai</th>
                                    <th class="py-3 px-4">Total Kasbon</th>
                                    <th class="py-3 px-4">Sisa Saldo</th>
                                    <th class="py-3 px-4">Tenor</th>
                                    <th class="py-3 px-4">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($cashAdvances as $ca)
                                    <tr class="hover:bg-slate-50/60">
                                        <td class="py-3 px-4 font-semibold text-slate-900">{{ $ca->employee->full_name }}</td>
                                        <td class="py-3 px-4 font-mono font-semibold text-slate-800">{{ $ca->formatted_amount }}</td>
                                        <td class="py-3 px-4 font-mono font-bold text-rose-600">{{ $ca->formatted_balance }}</td>
                                        <td class="py-3 px-4 text-slate-600">{{ $ca->installments }} Bulan</td>
                                        <td class="py-3 px-4">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                                AKTIF (Cicilan)
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-6 text-center text-slate-400">Tidak ada kasbon aktif saat ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 5: ASET INVENTARIS PELABUHAN & KANTOR                                 -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'assets'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <span class="font-bold text-xs text-slate-800 block">Inventaris Perangkat & Aset Lapangan Pelabuhan</span>
                    <span class="text-[10px] text-slate-400">Laptop ThinkPad, Walkie-Talkie HT TPS Koja/JICT, Kartu Pass Pelindo</span>
                </div>
                <button type="button" @click="showCreateAssetModal = true" class="min-h-[44px] px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold shadow-xs transition-all inline-flex items-center gap-1.5 active:scale-95">
                    <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Aset Baru</span>
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">Kode Aset</th>
                            <th class="py-3 px-4">Nama Barang & Spesifikasi</th>
                            <th class="py-3 px-4">Nilai Perolehan</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Pegawai Pemegang Saat Ini</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($assets as $asset)
                            <tr class="hover:bg-slate-50/60">
                                <td class="py-3 px-4 font-mono font-bold text-slate-800">{{ $asset->code }}</td>
                                <td class="py-3 px-4 font-semibold text-slate-900">{{ $asset->name }}</td>
                                <td class="py-3 px-4 font-mono text-slate-600">{{ $asset->formatted_value }}</td>
                                <td class="py-3 px-4">
                                    @if($asset->status === 'AVAILABLE')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            TERSEDIA (Gudang/Kantor)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800">
                                            DIPINJAM (Staf)
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    @if($asset->currentAssignment)
                                        <div class="font-bold text-slate-800">{{ $asset->currentAssignment->employee->full_name }}</div>
                                        <div class="text-[10px] text-slate-400">Sejak: {{ $asset->currentAssignment->assigned_at?->format('d M Y') }}</div>
                                    @else
                                        <span class="text-slate-400 italic">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    @if($asset->status === 'AVAILABLE')
                                        <button type="button"
                                                @click="selectedAssetId = '{{ $asset->id }}'; selectedAssetName = '{{ $asset->name }} ({{ $asset->code }})'; showAssignAssetModal = true"
                                                class="px-2.5 py-1.5 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-[11px] font-semibold transition-all">
                                            Pinjamkan
                                        </button>
                                    @elseif($asset->currentAssignment)
                                        <form method="POST" action="{{ route('hrm.assets.return', $asset->currentAssignment->id) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1.5 bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 text-slate-700 rounded-lg text-[11px] font-semibold transition-all">
                                                Kembalikan
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-slate-400 italic text-[10px]">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">Belum ada inventaris aset.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 6: PAYROLL ENGINE BULANAN                                             -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'payroll'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden mb-6">
            <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <span class="font-bold text-xs text-slate-800 block">Siklus Penggajian Bulanan Forwarder (Payroll Run)</span>
                    <span class="text-[10px] text-slate-400">Otomatisasi insentif pabean PPJK, uang makan, transport pelabuhan, BPJS, & cicilan kasbon</span>
                </div>
                <button type="button" @click="showPayrollModal = true" class="min-h-[44px] px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold shadow-xs transition-all whitespace-nowrap inline-flex items-center gap-1.5 active:scale-95">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Generate Periode Payroll Baru</span>
                </button>
            </div>

            <!-- List Payroll Runs -->
            <div class="divide-y divide-slate-100">
                @forelse($payrollRuns as $run)
                    <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-50/50 transition-colors">
                        <div>
                            <div class="flex items-center space-x-2.5">
                                <span class="text-sm font-bold text-slate-900 font-mono">{{ $run->formatted_period }} ({{ $run->period }})</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $run->status === 'DISBURSED' ? 'bg-emerald-100 text-emerald-800' : ($run->status === 'APPROVED' ? 'bg-sky-100 text-sky-800' : 'bg-amber-100 text-amber-800') }}">
                                    {{ $run->status }}
                                </span>
                            </div>
                            <div class="text-xs text-slate-500 mt-1">
                                Total Slip: <span class="font-semibold text-slate-700">{{ $run->payslips->count() }} Karyawan</span> &bull;
                                Dibuat: {{ $run->created_at->format('d M Y') }}
                            </div>
                        </div>

                        <div class="flex items-center flex-wrap gap-4 sm:gap-6">
                            <div class="text-right">
                                <span class="text-[10px] text-slate-400 block font-medium">Total Bersih (Take Home Pay)</span>
                                <span class="text-base font-bold text-emerald-600 font-mono">
                                    Rp {{ number_format($run->total_net, 0, ',', '.') }}
                                </span>
                            </div>

                            <div class="flex items-center space-x-2">
                                @if($run->status === 'DRAFT')
                                    <form method="POST" action="{{ route('hrm.payroll.approve', $run->id) }}">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-semibold transition-all">
                                            Setujui Draf
                                        </button>
                                    </form>
                                @elseif($run->status === 'APPROVED')
                                    <form method="POST" action="{{ route('hrm.payroll.disburse', $run->id) }}">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold transition-all shadow-sm">
                                            Cairkan Gaji (Disburse)
                                        </button>
                                    </form>
                                @else
                                    <span class="inline-flex items-center px-3 py-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl text-xs font-bold">
                                        ✓ Sudah Dicairkan
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-400 text-xs">Belum ada periode penggajian yang digenerate.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODALS SECTION (Alpine.js Interactive Popups)                             -->
    <!-- ========================================================================= -->

    <!-- Modal 1: Tambah Pegawai Baru -->
    <div x-show="showCreateEmployeeModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showCreateEmployeeModal = false" class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-lg w-full p-6 animate-scaleIn">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                <h3 class="font-bold text-slate-900 text-sm">Pendaftaran Karyawan Baru Forwarder</h3>
                <button type="button" @click="showCreateEmployeeModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form method="POST" action="{{ route('hrm.employees.store') }}" class="space-y-3.5 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">NIK (Nomor Induk Karyawan) *</label>
                    <input type="text" name="nik" placeholder="PATS-2026-0201" required class="w-full rounded-xl border border-slate-200 p-2.5 focus:border-sky-500 outline-none font-mono">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nama Lengkap *</label>
                    <input type="text" name="full_name" placeholder="Nama Karyawan..." required class="w-full rounded-xl border border-slate-200 p-2.5 focus:border-sky-500 outline-none">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Email Login (Opsional)</label>
                        <input type="email" name="email" placeholder="staff@agungtrans.co.id" class="w-full rounded-xl border border-slate-200 p-2.5 focus:border-sky-500 outline-none">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Divisi / Tim *</label>
                        <select name="team" required class="w-full rounded-xl border border-slate-200 p-2.5 bg-white focus:border-sky-500 outline-none">
                            @foreach($teams as $t)
                                <option value="{{ $t }}">Tim {{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Jabatan Operasional *</label>
                    <input type="text" name="position" placeholder="Staff Dokumen / Petugas Behandle..." required class="w-full rounded-xl border border-slate-200 p-2.5 focus:border-sky-500 outline-none">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tanggal Mulai Bekerja *</label>
                        <input type="date" name="start_date" value="{{ date('Y-m-d') }}" required class="w-full rounded-xl border border-slate-200 p-2.5 focus:border-sky-500 outline-none">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Gaji Pokok (Rp) *</label>
                        <input type="number" name="base_salary" placeholder="8500000" required class="w-full rounded-xl border border-slate-200 p-2.5 font-mono focus:border-sky-500 outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Jenis Kontrak *</label>
                        <select name="contract_kind" required class="w-full rounded-xl border border-slate-200 p-2.5 bg-white focus:border-sky-500 outline-none">
                            <option value="PKWT">PKWT (Kontrak Waktu Tertentu)</option>
                            <option value="PKWTT">PKWTT (Karyawan Tetap)</option>
                            <option value="INTERNSHIP">Magang / Internship</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Batas Akhir Kontrak (Jika PKWT)</label>
                        <input type="date" name="contract_end_date" class="w-full rounded-xl border border-slate-200 p-2.5 focus:border-sky-500 outline-none">
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end space-x-2">
                    <button type="button" @click="showCreateEmployeeModal = false" class="px-4 py-2 border border-slate-200 rounded-xl text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white font-semibold rounded-xl shadow-sm">Simpan Karyawan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: Clock In / Out GPS & Wajah -->
    <div x-show="showClockModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showClockModal = false" class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-md w-full p-6 animate-scaleIn">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <h3 class="font-bold text-slate-900 text-sm">Presensi GPS Geofencing & Face Match</h3>
                <button type="button" @click="showClockModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form method="POST" action="{{ route('hrm.attendance.clock') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Pilih Karyawan *</label>
                    <select name="employee_id" required class="w-full rounded-xl border border-slate-200 p-2.5 bg-white focus:border-sky-500 outline-none">
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->nik }} - {{ $emp->team }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Jenis Presensi *</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="p-2.5 rounded-xl border border-slate-200 flex items-center space-x-2 cursor-pointer hover:bg-slate-50">
                            <input type="radio" name="kind" value="CLOCK_IN" checked class="text-sky-600">
                            <span class="font-bold text-slate-800">Masuk (Clock In)</span>
                        </label>
                        <label class="p-2.5 rounded-xl border border-slate-200 flex items-center space-x-2 cursor-pointer hover:bg-slate-50">
                            <input type="radio" name="kind" value="CLOCK_OUT" class="text-sky-600">
                            <span class="font-bold text-slate-800">Pulang (Clock Out)</span>
                        </label>
                    </div>
                </div>

                <!-- GPS Coordinates Radar Panel -->
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-bold text-slate-800">Status Koordinat GPS:</span>
                        <button type="button" @click="getLocation()" class="text-[11px] text-sky-600 font-bold hover:underline">
                            ⟳ Refresh GPS
                        </button>
                    </div>
                    <div class="text-[11px] text-slate-600 mb-2 font-mono" x-text="gpsStatus"></div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <span class="text-[10px] text-slate-400 block">Latitude</span>
                            <input type="number" step="any" name="lat" x-model="gpsLat" required class="w-full font-mono text-[11px] rounded-lg border border-slate-200 p-1.5 bg-white">
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block">Longitude</span>
                            <input type="number" step="any" name="lng" x-model="gpsLng" required class="w-full font-mono text-[11px] rounded-lg border border-slate-200 p-1.5 bg-white">
                        </div>
                    </div>
                    <input type="hidden" name="face_match" value="0.975">
                </div>

                <div class="pt-2 flex items-center justify-end space-x-2">
                    <button type="button" @click="showClockModal = false" class="px-4 py-2 border border-slate-200 rounded-xl text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl shadow-sm">Kirim Presensi</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 3: Formulir Persetujuan Biometrik Wajah UU PDP No. 27/2022 -->
    <div x-show="showConsentModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showConsentModal = false" class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-md w-full p-6 animate-scaleIn">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <h3 class="font-bold text-slate-900 text-sm">Persetujuan Biometrik (UU PDP No. 27/2022)</h3>
                <button type="button" @click="showConsentModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form method="POST" action="{{ route('hrm.attendance.consent') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Pilih Karyawan *</label>
                    <select name="employee_id" required class="w-full rounded-xl border border-slate-200 p-2.5 bg-white focus:border-sky-500 outline-none">
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->nik }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-600 leading-relaxed text-[11px]">
                    Sesuai Pasal 20 UU No. 27/2022 tentang Perlindungan Data Pribadi, data biometrik wajah hanya digunakan untuk verifikasi kehadiran kerja forwarder dan kepabeanan, tidak akan dibagikan ke pihak ketiga tanpa izin, serta disimpan terenkripsi selama masa kontrak aktif.
                </div>
                <label class="flex items-start space-x-2.5 cursor-pointer">
                    <input type="checkbox" name="agree" value="1" required class="mt-0.5 rounded text-sky-600 focus:ring-sky-500">
                    <span class="text-[11px] text-slate-800 font-medium">Saya memahami dan memberikan persetujuan eksplisit pemrosesan data biometrik wajah untuk presensi Genie ERP.</span>
                </label>
                <div class="pt-2 flex items-center justify-end space-x-2">
                    <button type="button" @click="showConsentModal = false" class="px-4 py-2 border border-slate-200 rounded-xl text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white font-semibold rounded-xl shadow-sm">Simpan Persetujuan UU PDP</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 4: Pengajuan Cuti Baru -->
    <div x-show="showLeaveModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showLeaveModal = false" class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-md w-full p-6 animate-scaleIn">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <h3 class="font-bold text-slate-900 text-sm">Pengajuan Cuti / Izin Pegawai</h3>
                <button type="button" @click="showLeaveModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form method="POST" action="{{ route('hrm.leaves.store') }}" class="space-y-3.5 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Karyawan Pemohon *</label>
                    <select name="employee_id" required class="w-full rounded-xl border border-slate-200 p-2.5 bg-white focus:border-sky-500 outline-none">
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->team }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Jenis Cuti *</label>
                    <select name="kind" required class="w-full rounded-xl border border-slate-200 p-2.5 bg-white focus:border-sky-500 outline-none">
                        <option value="TAHUNAN">Cuti Tahunan (Potong Saldo Kuota)</option>
                        <option value="SAKIT">Izin Sakit (Surat Dokter)</option>
                        <option value="MELAHIRKAN">Cuti Melahirkan (3 Bulan)</option>
                        <option value="IZIN_KELUARGA">Izin Khusus / Keperluan Keluarga</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Dari Tanggal *</label>
                        <input type="date" name="from_date" value="{{ date('Y-m-d') }}" required class="w-full rounded-xl border border-slate-200 p-2.5 outline-none">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Sampai Tanggal *</label>
                        <input type="date" name="to_date" value="{{ date('Y-m-d') }}" required class="w-full rounded-xl border border-slate-200 p-2.5 outline-none">
                    </div>
                </div>
                <div class="pt-3 flex items-center justify-end space-x-2">
                    <button type="button" @click="showLeaveModal = false" class="px-4 py-2 border border-slate-200 rounded-xl text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white font-semibold rounded-xl shadow-sm">Kirim Pengajuan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 5: Catat Lembur -->
    <div x-show="showOvertimeModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showOvertimeModal = false" class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-md w-full p-6 animate-scaleIn">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <h3 class="font-bold text-slate-900 text-sm">Pencatatan Lembur Operasional TPS</h3>
                <button type="button" @click="showOvertimeModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form method="POST" action="{{ route('hrm.overtime.store') }}" class="space-y-3.5 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Karyawan *</label>
                    <select name="employee_id" required class="w-full rounded-xl border border-slate-200 p-2.5 bg-white outline-none">
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->team }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tanggal Lembur *</label>
                        <input type="date" name="work_date" value="{{ date('Y-m-d') }}" required class="w-full rounded-xl border border-slate-200 p-2.5 outline-none">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Durasi (Jam) *</label>
                        <input type="number" step="0.5" name="hours" placeholder="3.5" required class="w-full rounded-xl border border-slate-200 p-2.5 font-mono outline-none">
                    </div>
                </div>
                <div class="pt-3 flex items-center justify-end space-x-2">
                    <button type="button" @click="showOvertimeModal = false" class="px-4 py-2 border border-slate-200 rounded-xl text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white font-semibold rounded-xl">Simpan Lembur</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 6: Kasbon Baru -->
    <div x-show="showCashAdvanceModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showCashAdvanceModal = false" class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-md w-full p-6 animate-scaleIn">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <h3 class="font-bold text-slate-900 text-sm">Penerbitan Kasbon Pegawai</h3>
                <button type="button" @click="showCashAdvanceModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form method="POST" action="{{ route('hrm.cash-advances.store') }}" class="space-y-3.5 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Karyawan *</label>
                    <select name="employee_id" required class="w-full rounded-xl border border-slate-200 p-2.5 bg-white outline-none">
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->team }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nominal Pinjaman (Rp) *</label>
                        <input type="number" name="amount" placeholder="2000000" required class="w-full rounded-xl border border-slate-200 p-2.5 font-mono outline-none">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tenor Cicilan (Bulan) *</label>
                        <input type="number" name="installments" min="1" max="12" value="2" required class="w-full rounded-xl border border-slate-200 p-2.5 font-mono outline-none">
                    </div>
                </div>
                <div class="pt-3 flex items-center justify-end space-x-2">
                    <button type="button" @click="showCashAdvanceModal = false" class="px-4 py-2 border border-slate-200 rounded-xl text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white font-semibold rounded-xl">Terbitkan Kasbon</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 7: Generate Payroll Run Baru -->
    <div x-show="showPayrollModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showPayrollModal = false" class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-sm w-full p-6 animate-scaleIn">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <h3 class="font-bold text-slate-900 text-sm">Generate Payroll Periode Baru</h3>
                <button type="button" @click="showPayrollModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form method="POST" action="{{ route('hrm.payroll.generate') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Periode Penggajian (YYYY-MM) *</label>
                    <input type="text" name="period" value="{{ date('Y-m', strtotime('+1 month')) }}" placeholder="2026-11" required pattern="\d{4}-\d{2}" class="w-full rounded-xl border border-slate-200 p-2.5 font-mono text-center text-sm font-bold focus:border-sky-500 outline-none">
                    <span class="text-[10px] text-slate-400 block mt-1">Format: 4 digit tahun - 2 digit bulan (contoh: 2026-11)</span>
                </div>
                <div class="pt-2 flex items-center justify-end space-x-2">
                    <button type="button" @click="showPayrollModal = false" class="px-4 py-2 border border-slate-200 rounded-xl text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white font-semibold rounded-xl">Hitung Payroll</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 8: Tambah Aset Baru -->
    <div x-show="showCreateAssetModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showCreateAssetModal = false" class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-md w-full p-6 animate-scaleIn">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <h3 class="font-bold text-slate-900 text-sm">Tambah Aset Inventaris Baru</h3>
                <button type="button" @click="showCreateAssetModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form method="POST" action="{{ route('hrm.assets.store') }}" class="space-y-3.5 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Kode Aset *</label>
                    <input type="text" name="code" placeholder="AST-HT-03" required class="w-full rounded-xl border border-slate-200 p-2.5 font-mono focus:border-sky-500 outline-none">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nama Barang & Tipe *</label>
                    <input type="text" name="name" placeholder="Walkie Talkie Motorola CP1660..." required class="w-full rounded-xl border border-slate-200 p-2.5 focus:border-sky-500 outline-none">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nilai Perolehan / Beli (Rp)</label>
                    <input type="number" name="purchase_value" placeholder="3800000" class="w-full rounded-xl border border-slate-200 p-2.5 font-mono focus:border-sky-500 outline-none">
                </div>
                <div class="pt-3 flex items-center justify-end space-x-2">
                    <button type="button" @click="showCreateAssetModal = false" class="px-4 py-2 border border-slate-200 rounded-xl text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white font-semibold rounded-xl">Simpan Aset</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 9: Serah Terima Peminjaman Aset -->
    <div x-show="showAssignAssetModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showAssignAssetModal = false" class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-md w-full p-6 animate-scaleIn">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <h3 class="font-bold text-slate-900 text-sm">Serah Terima Peminjaman Aset</h3>
                <button type="button" @click="showAssignAssetModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form :action="'/hrm/assets/' + selectedAssetId + '/assign'" method="POST" class="space-y-3.5 text-xs">
                @csrf
                <div>
                    <span class="text-slate-500 block mb-1">Barang yang Dipinjamkan:</span>
                    <span class="font-bold text-slate-900 font-mono text-sm block" x-text="selectedAssetName"></span>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Pilih Karyawan Penerima *</label>
                    <select name="employee_id" required class="w-full rounded-xl border border-slate-200 p-2.5 bg-white focus:border-sky-500 outline-none">
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->team }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Tanggal Serah Terima *</label>
                    <input type="date" name="assigned_at" value="{{ date('Y-m-d') }}" required class="w-full rounded-xl border border-slate-200 p-2.5 focus:border-sky-500 outline-none">
                </div>
                <div class="pt-3 flex items-center justify-end space-x-2">
                    <button type="button" @click="showAssignAssetModal = false" class="px-4 py-2 border border-slate-200 rounded-xl text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white font-semibold rounded-xl">Serah Terimakan</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
