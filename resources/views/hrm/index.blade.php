@extends('layouts.app')

@section('title', 'SDM & Penggajian (HRM Hub) — GENIE ERP')

@section('content')
<div class="max-w-[1360px] mx-auto px-4 sm:px-6 space-y-6 pt-4 pb-28 lg:pb-12"
     x-data="{
        activeTab: '{{ request('tab', $activeTab ?? 'employees') }}',
        setTab(tab) {
            this.activeTab = tab;
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tab);
            window.history.replaceState({}, '', url.toString());
        },

        // Modals
        showCreateEmployeeModal: false,
        showCreateLocationModal: false,
        showCreateShiftModal: false,
        showCreateScheduleModal: false,
        showCreateAssetModal: false,
        showAssignAssetModal: false,
        showCashAdvanceModal: false,
        showPhotoModal: false,
        activePhotoUrl: '',
        selectedAssetId: '',
        selectedAssetName: '',

        // Import Employee State
        showImportEmployeeModal: false,
        importEmployeeFile: null,
        importEmployeeFileName: '',
        importEmployeePreview: null,
        importEmployeeDuplicateStrategy: 'skip',
        importEmployeeLoading: false,

        handleImportEmployeeFile(e) {
            const file = e.target.files[0];
            if (file) {
                this.importEmployeeFile = file;
                this.importEmployeeFileName = file.name;
                this.previewEmployeeUpload();
            }
        },

        previewEmployeeUpload() {
            if (!this.importEmployeeFile) return;
            this.importEmployeeLoading = true;
            const formData = new FormData();
            formData.append('file', this.importEmployeeFile);
            formData.append('_token', '{{ csrf_token() }}');

            fetch('{{ route('hrm.employees.import-preview') }}', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(res => {
                this.importEmployeeLoading = false;
                if (res.success) {
                    this.importEmployeePreview = res.data;
                } else {
                    alert(res.message || 'Gagal memproses file import');
                }
            })
            .catch(err => {
                this.importEmployeeLoading = false;
                alert('Terjadi kesalahan unggah: ' + err.message);
            });
        },

        executeEmployeeImport() {
            if (!this.importEmployeePreview || !this.importEmployeePreview.rows) return;
            if (!confirm('Proses ' + this.importEmployeePreview.valid_count + ' data pegawai baru?')) return;

            fetch('{{ route('hrm.employees.import-execute') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    rows: this.importEmployeePreview.rows,
                    duplicate_strategy: this.importEmployeeDuplicateStrategy
                })
            })
            .then(res => res.json())
            .then(res => {
                alert(res.message);
                window.location.reload();
            });
        },

        // Import Attendance State
        showImportAttendanceModal: false,
        importAttendanceFile: null,
        importAttendanceFileName: '',
        importAttendancePreview: null,
        importAttendanceLoading: false,

        handleImportAttendanceFile(e) {
            const file = e.target.files[0];
            if (file) {
                this.importAttendanceFile = file;
                this.importAttendanceFileName = file.name;
                this.previewAttendanceUpload();
            }
        },

        previewAttendanceUpload() {
            if (!this.importAttendanceFile) return;
            this.importAttendanceLoading = true;
            const formData = new FormData();
            formData.append('file', this.importAttendanceFile);
            formData.append('_token', '{{ csrf_token() }}');

            fetch('{{ route('hrm.attendance.import-preview') }}', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(res => {
                this.importAttendanceLoading = false;
                if (res.success) {
                    this.importAttendancePreview = res.data;
                } else {
                    alert(res.message || 'Gagal memproses file log presensi');
                }
            })
            .catch(err => {
                this.importAttendanceLoading = false;
                alert('Terjadi kesalahan: ' + err.message);
            });
        },

        executeAttendanceImport() {
            if (!this.importAttendancePreview || !this.importAttendancePreview.rows) return;
            fetch('{{ route('hrm.attendance.import-execute') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ rows: this.importAttendancePreview.rows })
            })
            .then(res => res.json())
            .then(res => {
                alert(res.message);
                window.location.reload();
            });
        }
     }">

    <!-- ============================================================== -->
    <!-- 1. PAGE TITLE & EXECUTIVE ACTION CTA                           -->
    <!-- ============================================================== -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="text-xs text-slate-500 font-medium mb-1">
                <a href="{{ route('dashboard') }}" class="hover:underline text-slate-500">Dashboard</a>
                <span class="text-slate-400 mx-1">/</span>
                <span class="text-slate-800 font-semibold">HRM & Kepegawaian</span>
            </div>
            <div class="flex items-center space-x-2.5">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Manajemen SDM & Penggajian</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800 border border-sky-200">
                    Genie Hub
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Kelola profil staf forwarder/PPJK, presensi geofencing pelabuhan & wajah, persetujuan koreksi, shift kerja, dan siklus penggajian otomatis.
            </p>
        </div>

        <div class="flex items-center space-x-2 flex-wrap">
            <a href="{{ route('hrm.portal') }}" target="_blank"
               class="min-h-[42px] px-4 py-2 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-semibold shadow-xs transition-all flex items-center space-x-2 active:scale-95">
                <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                <span>Portal Mandiri Staf (ESS)</span>
            </a>

            <button type="button" @click="showCreateEmployeeModal = true"
                    class="min-h-[42px] px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold shadow-xs transition-all flex items-center space-x-2 active:scale-[0.98]">
                <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Pegawai</span>
            </button>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- 2. BENTO GRID METRICS SUMMARY (Apple HIG Architecture)          -->
    <!-- ============================================================== -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Kehadiran Hari Ini -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col justify-between space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Hadir Hari Ini</span>
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
            </div>
            <div>
                <div class="text-2xl sm:text-3xl font-extrabold text-emerald-600 tracking-tight font-mono">
                    {{ $metrics['attendance_rate'] ?? 100 }}%
                </div>
                <p class="text-xs text-slate-500 font-medium mt-0.5">
                    {{ $todayPresentCount }} dari {{ $totalStaff }} staf aktif
                </p>
            </div>
            <div class="text-[10px] text-slate-400 font-medium">Valid Radius TPS Pelabuhan & Kantor</div>
        </div>

        <!-- Card 2: Beban Gaji & BPJS -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col justify-between space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Beban Gaji Pokok</span>
                <svg class="w-4 h-4 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <div>
                <div class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight font-mono">
                    Rp {{ number_format($totalBaseSalary, 0, ',', '.') }}
                </div>
                <p class="text-xs text-slate-500 font-medium mt-0.5">
                    BPJS TK: Rp {{ number_format($totalBpjsTk, 0, ',', '.') }} &bull; Kes: Rp {{ number_format($totalBpjsKes, 0, ',', '.') }}
                </p>
            </div>
            <div class="text-[10px] text-slate-400 font-medium">Iuran 2% JHT & 1% BPJS Kes</div>
        </div>

        <!-- Card 3: Saldo Kasbon Berjalan -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col justify-between space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Saldo Kasbon Aktif</span>
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            </div>
            <div>
                <div class="text-xl sm:text-2xl font-extrabold text-amber-600 tracking-tight font-mono">
                    Rp {{ number_format($totalActiveLoans, 0, ',', '.') }}
                </div>
                <p class="text-xs text-slate-500 font-medium mt-0.5">
                    Dipotong otomatis di siklus payroll bulanan
                </p>
            </div>
            <div class="text-[10px] text-slate-400 font-medium">Cicilan kasbon aktif staf</div>
        </div>

        <!-- Card 4: Status Payroll Terakhir -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col justify-between space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Payroll Terakhir</span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800">
                    {{ $latestPaidPayroll ? 'LUNAS (DISBURSED)' : ($metrics['latest_payroll']?->status ?? 'DRAFT') }}
                </span>
            </div>
            <div>
                <div class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight font-mono">
                    Periode {{ $latestPaidPayroll?->period ?? ($metrics['latest_payroll']?->period ?? date('Y-m')) }}
                </div>
                <p class="text-xs text-slate-500 font-medium mt-0.5">
                    THP: Rp {{ number_format((float) ($latestPaidPayroll?->total_net ?? $metrics['latest_payroll']?->total_net ?? 0), 0, ',', '.') }}
                </p>
            </div>
            <a href="{{ route('hrm.payrolls.create') }}" class="text-[10px] text-sky-600 font-bold hover:underline">
                Buat Siklus Payroll Baru &rarr;
            </a>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- 3. APPLE HIG SEGMENTED NAVIGATION CONTROLS (8 TABS)            -->
    <!-- ============================================================== -->
    <div class="bg-slate-200/80 p-1.5 rounded-2xl overflow-x-auto no-scrollbar shadow-inner">
        <div class="flex items-center space-x-1 min-w-max">
            <button type="button" @click="setTab('employees')"
                    :class="activeTab === 'employees' ? 'bg-white text-slate-900 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                    class="min-h-[44px] px-3.5 py-2 rounded-xl text-xs transition-all flex items-center space-x-2">
                <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span>Data Pegawai</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 text-slate-700 font-bold">{{ $totalStaff }}</span>
            </button>

            <button type="button" @click="setTab('attendance')"
                    :class="activeTab === 'attendance' ? 'bg-white text-slate-900 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                    class="min-h-[44px] px-3.5 py-2 rounded-xl text-xs transition-all flex items-center space-x-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <span>Log Presensi</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-emerald-100 text-emerald-800 font-bold">{{ $todayPresentCount }}</span>
            </button>

            <button type="button" @click="setTab('corrections')"
                    :class="activeTab === 'corrections' ? 'bg-white text-slate-900 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                    class="min-h-[44px] px-3.5 py-2 rounded-xl text-xs transition-all flex items-center space-x-2">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                <span>Koreksi Presensi</span>
                @if($pendingCorrectionsCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] bg-rose-600 text-white font-bold animate-pulse">{{ $pendingCorrectionsCount }}</span>
                @else
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 text-slate-700 font-bold">{{ $corrections->total() }}</span>
                @endif
            </button>

            <button type="button" @click="setTab('locations')"
                    :class="activeTab === 'locations' ? 'bg-white text-slate-900 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                    class="min-h-[44px] px-3.5 py-2 rounded-xl text-xs transition-all flex items-center space-x-2">
                <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Titik Geofence (TPS)</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 text-slate-700 font-bold">{{ $allLocations->count() }}</span>
            </button>

            <button type="button" @click="setTab('shifts')"
                    :class="activeTab === 'shifts' ? 'bg-white text-slate-900 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                    class="min-h-[44px] px-3.5 py-2 rounded-xl text-xs transition-all flex items-center space-x-2">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Shift Kerja</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 text-slate-700 font-bold">{{ $workShifts->count() }}</span>
            </button>

            <button type="button" @click="setTab('schedules')"
                    :class="activeTab === 'schedules' ? 'bg-white text-slate-900 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                    class="min-h-[44px] px-3.5 py-2 rounded-xl text-xs transition-all flex items-center space-x-2">
                <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>Jadwal Staf</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 text-slate-700 font-bold">{{ $employeeSchedules->total() }}</span>
            </button>

            <button type="button" @click="setTab('payrolls')"
                    :class="activeTab === 'payrolls' ? 'bg-white text-slate-900 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                    class="min-h-[44px] px-3.5 py-2 rounded-xl text-xs transition-all flex items-center space-x-2">
                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Payroll Engine</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 text-slate-700 font-bold">{{ $payrolls->total() }}</span>
            </button>

            <button type="button" @click="setTab('loans')"
                    :class="activeTab === 'loans' ? 'bg-white text-slate-900 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                    class="min-h-[44px] px-3.5 py-2 rounded-xl text-xs transition-all flex items-center space-x-2">
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Kasbon Staf</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 text-slate-700 font-bold">{{ $loans->total() }}</span>
            </button>

            <button type="button" @click="setTab('assets')"
                    :class="activeTab === 'assets' ? 'bg-white text-slate-900 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                    class="min-h-[44px] px-3.5 py-2 rounded-xl text-xs transition-all flex items-center space-x-2">
                <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span>Aset Inventaris</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-100 text-slate-700 font-bold">{{ $assets->count() }}</span>
            </button>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 1: DATA PEGAWAI & KONTRAK (EMPLOYEES)                      -->
    <!-- ============================================================== -->
    <div x-show="activeTab === 'employees'" class="space-y-4">
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <!-- Filter & Search -->
            <form method="GET" action="{{ route('hrm.index') }}" class="flex items-center space-x-2 flex-1 max-w-lg">
                <input type="hidden" name="tab" value="employees">
                <div class="relative flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari NIK, Nama Pegawai, Posisi..."
                           class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 bg-slate-50 text-xs focus:ring-2 focus:ring-sky-500/20">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <select name="team" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-xs font-semibold text-slate-700">
                    <option value="">Semua Divisi</option>
                    @foreach($teams as $t)
                        <option value="{{ $t }}" {{ request('team') === $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </form>

            <!-- Export / Import Actions -->
            <div class="flex items-center space-x-2 flex-wrap">
                <a href="{{ route('hrm.employees.export') }}"
                   class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center space-x-1.5"
                   title="Export Data ke Excel/CSV">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Export Excel</span>
                </a>

                <a href="{{ route('hrm.employees.template') }}"
                   class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition"
                   title="Download Template CSV">
                    Template
                </a>

                <button type="button" @click="showImportEmployeeModal = true"
                        class="px-3.5 py-2 rounded-xl bg-sky-50 hover:bg-sky-100 text-sky-800 border border-sky-200 text-xs font-bold transition flex items-center space-x-1.5 cursor-pointer">
                    <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    <span>Import Excel / CSV</span>
                </button>
            </div>
        </div>

        <!-- Tabel Data Pegawai -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-5 py-3.5">Pegawai</th>
                            <th class="px-4 py-3.5">Divisi & Jabatan</th>
                            <th class="px-4 py-3.5">Kontrak Kerja</th>
                            <th class="px-4 py-3.5">Gaji Pokok</th>
                            <th class="px-4 py-3.5">Rekening Bank</th>
                            <th class="px-4 py-3.5">Biometrik Wajah</th>
                            <th class="px-4 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($employees as $emp)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-9 h-9 rounded-xl bg-sky-100 text-sky-800 font-bold flex items-center justify-center text-xs shrink-0 overflow-hidden">
                                            @if($emp->photo_url)
                                                <img src="{{ $emp->photo_url }}" class="w-full h-full object-cover">
                                            @else
                                                {{ $emp->initials }}
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900">{{ $emp->full_name }}</div>
                                            <div class="font-mono text-[10px] text-slate-400">{{ $emp->nik }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-800">{{ $emp->team }}</span>
                                    <div class="text-[11px] text-slate-500 mt-0.5">{{ $emp->position }}</div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-sky-50 text-sky-800 border border-sky-100">
                                        {{ $emp->latestContract?->kind ?? 'PKWT' }}
                                    </span>
                                    <div class="text-[10px] text-slate-400 mt-0.5">Mulai: {{ $emp->start_date ? $emp->start_date->format('d/m/Y') : '-' }}</div>
                                </td>
                                <td class="px-4 py-3.5 font-mono font-bold text-slate-800">
                                    Rp {{ number_format((float) $emp->base_salary, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3.5 font-mono text-[11px] text-slate-600">
                                    {{ $emp->bank_name ?: 'BCA' }} - {{ $emp->bank_account_number ?: '-' }}
                                </td>
                                <td class="px-4 py-3.5">
                                    @if($emp->hasFaceRegistered())
                                        <span class="inline-flex items-center space-x-1 text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full text-[10px] font-bold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span>Terverifikasi</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center space-x-1 text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full text-[10px] font-bold">
                                            <span>Belum Rekam</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <div class="flex items-center justify-end space-x-1.5">
                                        <form method="POST" action="{{ route('hrm.employees.destroy', $emp->id) }}" onsubmit="return confirm('Hapus/nonaktifkan pegawai ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus Pegawai">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-8 text-center text-slate-400">
                                    Belum ada data pegawai yang cocok dengan pencarian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $employees->links() }}
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 2: LOG PRESENSI HARIAN & BULANAN (ATTENDANCE)              -->
    <!-- ============================================================== -->
    <div x-show="activeTab === 'attendance'" class="space-y-4">
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-bold text-slate-900">Log Presensi Harian Satelit & Geofencing</h2>
                <p class="text-xs text-slate-500">Rekaman presensi masuk/pulang, foto selfie verifikasi, dan jarak toleransi meter.</p>
            </div>

            <div class="flex items-center space-x-2 flex-wrap">
                <a href="{{ route('hrm.attendance.export') }}"
                   class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center space-x-1.5">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Export Presensi</span>
                </a>

                <button type="button" @click="showImportAttendanceModal = true"
                        class="px-3.5 py-2 rounded-xl bg-sky-50 hover:bg-sky-100 text-sky-800 border border-sky-200 text-xs font-bold transition flex items-center space-x-1.5 cursor-pointer">
                    <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    <span>Import Mesin Absensi</span>
                </button>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3.5">Waktu</th>
                            <th class="px-4 py-3.5">Pegawai</th>
                            <th class="px-4 py-3.5">Tipe</th>
                            <th class="px-4 py-3.5">Titik Geofence</th>
                            <th class="px-4 py-3.5">Jarak Radius</th>
                            <th class="px-4 py-3.5">Foto Selfie</th>
                            <th class="px-4 py-3.5">Status Validasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentAttendances as $log)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-4 py-3.5 font-mono text-slate-800">
                                    {{ $log->at?->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="font-bold text-slate-900">{{ $log->employee?->full_name }}</div>
                                    <div class="text-[10px] font-mono text-slate-400">{{ $log->employee?->nik }}</div>
                                </td>
                                <td class="px-4 py-3.5 font-bold">
                                    @if($log->kind === 'CLOCK_IN')
                                        <span class="text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded text-[10px]">MASUK</span>
                                    @else
                                        <span class="text-sky-700 bg-sky-100 px-2 py-0.5 rounded text-[10px]">PULANG</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-slate-700">
                                    {{ $log->location?->name ?? 'Titik Bebas' }}
                                </td>
                                <td class="px-4 py-3.5 font-mono">
                                    {{ $log->distance_m ?? 0 }} m
                                </td>
                                <td class="px-4 py-3.5">
                                    @if($log->photo_url)
                                        <button type="button" @click="activePhotoUrl = '{{ $log->photo_url }}'; showPhotoModal = true"
                                                class="w-8 h-8 rounded-lg overflow-hidden border border-slate-200 shadow-xs hover:scale-110 transition cursor-pointer">
                                            <img src="{{ $log->photo_url }}" class="w-full h-full object-cover">
                                        </button>
                                    @else
                                        <span class="text-slate-400 text-[10px]">Tidak ada</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 font-bold">
                                    @if($log->is_geofenced)
                                        <span class="text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded text-[10px]">DALAM RADIUS</span>
                                    @else
                                        <span class="text-amber-600 bg-amber-50 px-2 py-0.5 rounded text-[10px]">LUAR RADIUS</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-8 text-center text-slate-400">
                                    Belum ada log presensi tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 3: APPROVAL KOREKSI PRESENSI (CORRECTIONS)                 -->
    <!-- ============================================================== -->
    <div x-show="activeTab === 'corrections'" class="space-y-4">
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">Pusat Persetujuan Koreksi Presensi</h2>
                <p class="text-xs text-slate-500">Tinjau permohonan koreksi jam masuk/pulang yang diajukan oleh staf.</p>
            </div>
            <span class="px-3 py-1 rounded-xl text-xs font-bold bg-amber-100 text-amber-800">
                {{ $pendingCorrectionsCount }} Menunggu Review
            </span>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3.5">Tanggal Usulan</th>
                            <th class="px-4 py-3.5">Pegawai</th>
                            <th class="px-4 py-3.5">Usulan Jam</th>
                            <th class="px-4 py-3.5">Alasan Koreksi</th>
                            <th class="px-4 py-3.5">Status</th>
                            <th class="px-4 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($corrections as $corr)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-4 py-3.5 font-bold font-mono text-slate-900">
                                    {{ $corr->correction_date?->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="font-bold text-slate-900">{{ $corr->employee?->full_name }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $corr->employee?->position }}</div>
                                </td>
                                <td class="px-4 py-3.5 font-mono">
                                    In: <span class="font-bold text-emerald-700">{{ $corr->proposed_clock_in ?: '--:--' }}</span> |
                                    Out: <span class="font-bold text-sky-700">{{ $corr->proposed_clock_out ?: '--:--' }}</span>
                                </td>
                                <td class="px-4 py-3.5 max-w-xs truncate" title="{{ $corr->reason }}">
                                    {{ $corr->reason }}
                                </td>
                                <td class="px-4 py-3.5 font-bold">
                                    @if($corr->status === 'APPROVED')
                                        <span class="text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded text-[10px]">DISETUJUI</span>
                                    @elseif($corr->status === 'REJECTED')
                                        <span class="text-rose-700 bg-rose-100 px-2 py-0.5 rounded text-[10px]">DITOLAK</span>
                                    @else
                                        <span class="text-amber-700 bg-amber-100 px-2 py-0.5 rounded text-[10px] animate-pulse">MENUNGGU</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    @if($corr->status === 'PENDING')
                                        <div class="flex items-center justify-end space-x-1.5">
                                            <form method="POST" action="{{ route('hrm.attendance.corrections.approve', $corr->id) }}">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] shadow-xs">
                                                    Setujui
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('hrm.attendance.corrections.reject', $corr->id) }}">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-bold text-[11px] shadow-xs">
                                                    Tolak
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-slate-400 text-[10px]">Selesai ditinjau</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                                    Tidak ada pengajuan koreksi presensi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-100">
                {{ $corrections->links() }}
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 4: TITIK GEOFENCE MULTI-LOKASI (LOCATIONS)                 -->
    <!-- ============================================================== -->
    <div x-show="activeTab === 'locations'" class="space-y-4">
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">Titik Geofencing & Penugasan Lapangan</h2>
                <p class="text-xs text-slate-500">Koordinat GPS kantor pusat, TPS JICT Tanjung Priok, TPS Bandara Soetta, dan depo kontainer.</p>
            </div>
            <button type="button" @click="showCreateLocationModal = true"
                    class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition flex items-center space-x-1.5">
                <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Lokasi Baru</span>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($allLocations as $loc)
                <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Titik Geofence</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $loc->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                                {{ $loc->is_active ? 'AKTIF' : 'NON-AKTIF' }}
                            </span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 leading-snug">{{ $loc->name }}</h3>
                        <p class="text-xs text-slate-500 mt-1">{{ $loc->address ?: 'Alamat belum diatur' }}</p>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 space-y-1.5 text-xs font-mono">
                        <div class="flex justify-between">
                            <span class="text-slate-400 font-sans">Koordinat Lat/Lng:</span>
                            <span class="text-slate-800 font-bold">{{ number_format((float) $loc->lat, 4) }}, {{ number_format((float) $loc->lng, 4) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400 font-sans">Radius Toleransi:</span>
                            <span class="text-sky-700 font-bold">{{ $loc->radius_m }} meter</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-2 pt-2 border-t border-slate-50">
                        <form method="POST" action="{{ route('hrm.locations.destroy', $loc->id) }}" onsubmit="return confirm('Hapus titik lokasi ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 transition" title="Hapus Lokasi">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 5: MASTER SHIFT KERJA (SHIFTS)                             -->
    <!-- ============================================================== -->
    <div x-show="activeTab === 'shifts'" class="space-y-4">
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">Master Shift Jam Kerja</h2>
                <p class="text-xs text-slate-500">Atur jam masuk, jam pulang, toleransi keterlambatan, dan shift 24 jam operasional.</p>
            </div>
            <button type="button" @click="showCreateShiftModal = true"
                    class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition flex items-center space-x-1.5">
                <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Shift Baru</span>
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($workShifts as $sh)
                <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Shift Jam Kerja</span>
                            @if($sh->is_night_shift)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800">SHIFT MALAM (24H)</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">REGULER</span>
                            @endif
                        </div>
                        <h3 class="text-base font-bold text-slate-900">{{ $sh->name }}</h3>
                        <div class="text-xl font-mono font-extrabold text-sky-700 mt-2">
                            {{ $sh->formatted_hours }}
                        </div>
                    </div>

                    <div class="text-xs text-slate-500 flex justify-between pt-2 border-t border-slate-100">
                        <span>Toleransi Keterlambatan:</span>
                        <span class="font-bold text-slate-800">{{ $sh->grace_period_minutes ?? 15 }} Menit</span>
                    </div>

                    <div class="flex items-center justify-end space-x-2 pt-2">
                        <form method="POST" action="{{ route('hrm.shifts.destroy', $sh->id) }}" onsubmit="return confirm('Hapus shift ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 transition" title="Hapus Shift">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 6: PENUGASAN JADWAL STAF (SCHEDULES)                       -->
    <!-- ============================================================== -->
    <div x-show="activeTab === 'schedules'" class="space-y-4">
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">Jadwal Kerja Karyawan</h2>
                <p class="text-xs text-slate-500">Tugaskan shift khusus untuk petugas lapangan, checker dokumen, dan tim traffic.</p>
            </div>
            <button type="button" @click="showCreateScheduleModal = true"
                    class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition flex items-center space-x-1.5">
                <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tugaskan Jadwal</span>
            </button>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3.5">Tanggal Kerja</th>
                            <th class="px-4 py-3.5">Nama Pegawai</th>
                            <th class="px-4 py-3.5">Shift yang Ditugaskan</th>
                            <th class="px-4 py-3.5">Jam Operasional</th>
                            <th class="px-4 py-3.5">Catatan</th>
                            <th class="px-4 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($employeeSchedules as $sch)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-4 py-3.5 font-bold font-mono text-slate-900">
                                    {{ $sch->work_date?->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="font-bold text-slate-900">{{ $sch->employee?->full_name }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $sch->employee?->position }}</div>
                                </td>
                                <td class="px-4 py-3.5 font-bold text-slate-800">
                                    {{ $sch->shift?->name }}
                                </td>
                                <td class="px-4 py-3.5 font-mono text-sky-700 font-bold">
                                    {{ $sch->shift?->formatted_hours }}
                                </td>
                                <td class="px-4 py-3.5 text-slate-500">
                                    {{ $sch->notes ?: '-' }}
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <form method="POST" action="{{ route('hrm.schedules.destroy', $sch->id) }}" onsubmit="return confirm('Hapus jadwal kerja ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 transition" title="Hapus Jadwal">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                                    Belum ada penugasan jadwal kerja harian staf.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-100">
                {{ $employeeSchedules->links() }}
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 7: PAYROLL ENGINE BULANAN & BATCH RUN                      -->
    <!-- ============================================================== -->
    <div x-show="activeTab === 'payrolls'" class="space-y-4">
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">Riwayat Batch Penggajian (Payroll Engine)</h2>
                <p class="text-xs text-slate-500">Kalkulasi bulanan kompensasi staf, cetak slip gaji digital, dan ekspor transfer BCA/Mandiri.</p>
            </div>
            <a href="{{ route('hrm.payrolls.create') }}"
               class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition flex items-center space-x-1.5 shadow-sm">
                <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Buat Batch Payroll Baru (Wizard)</span>
            </a>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3.5">Periode Penggajian</th>
                            <th class="px-4 py-3.5">Status Siklus</th>
                            <th class="px-4 py-3.5">Jumlah Staf</th>
                            <th class="px-4 py-3.5">Total Take Home Pay (Net)</th>
                            <th class="px-4 py-3.5 text-right">Aksi & Ekspor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($payrolls as $run)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-4 py-3.5 font-bold font-mono text-base text-slate-900">
                                    {{ $run->period }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold
                                        {{ $run->status === 'DISBURSED' ? 'bg-emerald-100 text-emerald-800' : ($run->status === 'APPROVED' ? 'bg-sky-100 text-sky-800' : 'bg-slate-100 text-slate-700') }}">
                                        {{ $run->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 font-bold text-slate-700">
                                    {{ $run->payslips->count() }} Orang
                                </td>
                                <td class="px-4 py-3.5 font-mono font-extrabold text-slate-900 text-sm">
                                    Rp {{ number_format((float) $run->payslips->sum('net'), 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="{{ route('hrm.payrolls.show', $run->id) }}"
                                           class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition">
                                            Rincian & Audit &rarr;
                                        </a>
                                        <a href="{{ route('hrm.payrolls.export-bank', $run->id) }}"
                                           class="px-2.5 py-1.5 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold text-xs border border-purple-200 transition"
                                           title="Download CSV Bank BCA">
                                            CSV BCA
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-slate-400">
                                    Belum ada batch payroll yang dibuat. Klik tombol di atas untuk memulai.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-100">
                {{ $payrolls->links() }}
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 8: KASBON & PINJAMAN KARYAWAN (LOANS)                      -->
    <!-- ============================================================== -->
    <div x-show="activeTab === 'loans'" class="space-y-4">
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">Manajemen Kasbon & Pinjaman Karyawan</h2>
                <p class="text-xs text-slate-500">Plafon kasbon, tenor cicilan, sisa saldo, dan riwayat pemotongan gaji otomatis.</p>
            </div>
            <button type="button" @click="showCashAdvanceModal = true"
                    class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition flex items-center space-x-1.5">
                <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Terbitkan Kasbon Baru</span>
            </button>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3.5">Tanggal</th>
                            <th class="px-4 py-3.5">Pegawai</th>
                            <th class="px-4 py-3.5">Plafon Nominal</th>
                            <th class="px-4 py-3.5">Tenor</th>
                            <th class="px-4 py-3.5">Sisa Saldo Pinjaman</th>
                            <th class="px-4 py-3.5">Status</th>
                            <th class="px-4 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($loans as $loan)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-4 py-3.5 font-mono text-slate-800">
                                    {{ $loan->created_at?->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="font-bold text-slate-900">{{ $loan->employee?->full_name }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $loan->employee?->position }}</div>
                                </td>
                                <td class="px-4 py-3.5 font-mono font-bold text-slate-900">
                                    Rp {{ number_format((float) $loan->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3.5 font-bold text-slate-700">
                                    {{ $loan->installments }} Bulan
                                </td>
                                <td class="px-4 py-3.5 font-mono font-bold text-amber-700">
                                    Rp {{ number_format((float) $loan->balance, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $loan->status === 'ACTIVE' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                                        {{ $loan->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    @if($loan->status === 'ACTIVE')
                                        <form method="POST" action="{{ route('hrm.loans.cancel', $loan->id) }}" onsubmit="return confirm('Selesaikan/lunaskan kasbon ini secara manual?')">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] transition">
                                                Lunaskan
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-slate-400 text-[10px]">Tuntas</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-8 text-center text-slate-400">
                                    Belum ada catatan kasbon karyawan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-100">
                {{ $loans->links() }}
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 9: INVENTARIS ASET PERUSAHAAN (ASSETS)                     -->
    <!-- ============================================================== -->
    <div x-show="activeTab === 'assets'" class="space-y-4">
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">Aset & Inventaris Staf Lapangan</h2>
                <p class="text-xs text-slate-500">Laptop kantor, Handy Talkie (HT) TPS Pelabuhan, ID Card Pass Bandara, dan perlengkapan dinas.</p>
            </div>
            <button type="button" @click="showCreateAssetModal = true"
                    class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition flex items-center space-x-1.5">
                <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Aset Baru</span>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($assets as $asset)
                <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-mono text-[10px] font-bold text-slate-400">{{ $asset->code }}</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $asset->status === 'AVAILABLE' ? 'bg-emerald-100 text-emerald-800' : 'bg-sky-100 text-sky-800' }}">
                                {{ $asset->status }}
                            </span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 leading-snug">{{ $asset->name }}</h3>
                        <div class="text-xs text-slate-500 mt-1">
                            Nilai Beli: Rp {{ number_format((float) ($asset->purchase_value ?? 0), 0, ',', '.') }}
                        </div>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 text-xs">
                        @if($asset->currentAssignment)
                            <div class="text-[10px] text-slate-400 uppercase font-bold">Dipinjam Oleh:</div>
                            <div class="font-bold text-slate-800 mt-0.5">{{ $asset->currentAssignment->employee?->full_name }}</div>
                            <div class="text-[10px] text-slate-500">Sejak: {{ $asset->currentAssignment->assigned_at ? $asset->currentAssignment->assigned_at->format('d/m/Y') : '-' }}</div>
                        @else
                            <div class="text-slate-400 italic">Siap diserahterimakan (Tersedia)</div>
                        @endif
                    </div>

                    <div class="flex items-center justify-end space-x-2 pt-2 border-t border-slate-50">
                        @if($asset->status === 'AVAILABLE')
                            <button type="button" @click="selectedAssetId = '{{ $asset->id }}'; selectedAssetName = '{{ $asset->name }}'; showAssignAssetModal = true"
                                    class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition">
                                Serahkan ke Staf
                            </button>
                        @elseif($asset->currentAssignment)
                            <form method="POST" action="{{ route('hrm.assets.return', $asset->currentAssignment->id) }}">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold transition">
                                    Catat Pengembalian
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center text-slate-400">
                    Belum ada data inventaris aset perusahaan.
                </div>
            @endforelse
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL: IMPORT PEGAWAI DENGAN LIVE PREVIEW                      -->
    <!-- ============================================================== -->
    <div x-show="showImportEmployeeModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="showImportEmployeeModal = false"
             class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl border border-slate-200 space-y-4 max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Import Data Pegawai dari Excel / CSV</h3>
                <button type="button" @click="showImportEmployeeModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Upload Area -->
            <div class="border-2 border-dashed border-slate-200 hover:border-sky-400 rounded-2xl p-6 text-center cursor-pointer transition bg-slate-50/50">
                <input type="file" @change="handleImportEmployeeFile($event)" accept=".csv,.txt" class="hidden" id="empFileInput">
                <label for="empFileInput" class="cursor-pointer block space-y-2">
                    <svg class="w-10 h-10 text-sky-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    <div class="text-xs font-bold text-slate-800" x-text="importEmployeeFileName || 'Pilih atau Tarik File CSV Pegawai ke Sini'"></div>
                    <div class="text-[11px] text-slate-400">Gunakan format template yang sesuai untuk mapping otomatis NIK, Nama, dan Gaji.</div>
                </label>
            </div>

            <!-- Preview Data Table -->
            <template x-if="importEmployeeLoading">
                <div class="py-8 text-center text-xs font-bold text-slate-500 animate-pulse">
                    Menganalisa dan memvalidasi baris CSV...
                </div>
            </template>

            <template x-if="importEmployeePreview">
                <div class="space-y-3 flex-1 overflow-y-auto custom-scrollbar">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-bold text-slate-800">
                            Total: <span x-text="importEmployeePreview.total_count"></span> |
                            Valid: <span class="text-emerald-600" x-text="importEmployeePreview.valid_count"></span> |
                            Duplikat: <span class="text-amber-600" x-text="importEmployeePreview.duplicate_count"></span>
                        </span>

                        <div class="flex items-center space-x-2">
                            <span class="text-[11px] text-slate-500 font-bold">Jika Duplikat:</span>
                            <select x-model="importEmployeeDuplicateStrategy" class="text-xs font-bold px-2.5 py-1 rounded-lg border border-slate-200 bg-slate-50">
                                <option value="skip">Lewati (Skip)</option>
                                <option value="overwrite">Timpa (Overwrite)</option>
                            </select>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-500 font-bold text-[10px] uppercase border-b border-slate-200">
                                <tr>
                                    <th class="px-3 py-2">NIK</th>
                                    <th class="px-3 py-2">Nama</th>
                                    <th class="px-3 py-2">Jabatan</th>
                                    <th class="px-3 py-2">Divisi</th>
                                    <th class="px-3 py-2">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="r in importEmployeePreview.rows.slice(0, 8)" :key="r.nik">
                                    <tr>
                                        <td class="px-3 py-2 font-mono" x-text="r.nik"></td>
                                        <td class="px-3 py-2 font-bold" x-text="r.name"></td>
                                        <td class="px-3 py-2 text-slate-600" x-text="r.position"></td>
                                        <td class="px-3 py-2" x-text="r.team"></td>
                                        <td class="px-3 py-2">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold"
                                                  :class="r.is_duplicate ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'"
                                                  x-text="r.is_duplicate ? 'DUPLIKAT' : 'SIAP'"></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>

            <!-- Modal Action Buttons -->
            <div class="flex justify-end space-x-2 pt-3 border-t border-slate-100">
                <button type="button" @click="showImportEmployeeModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold">
                    Batal
                </button>
                <button type="button" @click="executeEmployeeImport()" :disabled="!importEmployeePreview"
                        class="px-5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition shadow-sm disabled:opacity-40">
                    Eksekusi Import Sekarang &rarr;
                </button>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL: IMPORT PRESENSI MESIN                                   -->
    <!-- ============================================================== -->
    <div x-show="showImportAttendanceModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="showImportAttendanceModal = false"
             class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Import Log Mesin Presensi / Fingerprint</h3>
                <button type="button" @click="showImportAttendanceModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="border-2 border-dashed border-slate-200 hover:border-sky-400 rounded-2xl p-6 text-center cursor-pointer transition bg-slate-50/50">
                <input type="file" @change="handleImportAttendanceFile($event)" accept=".csv,.txt" class="hidden" id="attFileInput">
                <label for="attFileInput" class="cursor-pointer block space-y-2">
                    <svg class="w-10 h-10 text-emerald-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div class="text-xs font-bold text-slate-800" x-text="importAttendanceFileName || 'Pilih File CSV Presensi Mesin'"></div>
                    <div class="text-[11px] text-slate-400">Format: NIK, Tanggal (YYYY-MM-DD), Jam Masuk (HH:MM), Jam Pulang (HH:MM).</div>
                </label>
            </div>

            <div class="flex justify-end space-x-2 pt-3 border-t border-slate-100">
                <button type="button" @click="showImportAttendanceModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold">
                    Batal
                </button>
                <button type="button" @click="executeAttendanceImport()" :disabled="!importAttendancePreview"
                        class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-sm disabled:opacity-40">
                    Proses Log Presensi &rarr;
                </button>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL: TAMBAH PEGAWAI BARU                                     -->
    <!-- ============================================================== -->
    <div x-show="showCreateEmployeeModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="showCreateEmployeeModal = false"
             class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl border border-slate-200 space-y-4 max-h-[90vh] overflow-y-auto custom-scrollbar">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Tambah Pegawai & Buat Kontrak Kerja</h3>
                <button type="button" @click="showCreateEmployeeModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('hrm.employees.store') }}" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">NIK (Nomor Induk Karyawan)</label>
                        <input type="text" name="nik" placeholder="Contoh: GENIE-00105" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-sky-500/20 font-mono">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Lengkap</label>
                        <input type="text" name="full_name" placeholder="Nama lengkap karyawan" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-sky-500/20">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Email Akun (Opsional)</label>
                        <input type="email" name="email" placeholder="nama@genie.test"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-sky-500/20">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Divisi / Tim Operasional</label>
                        <select name="team" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-sky-500/20 font-bold">
                            @foreach($teams as $t)
                                <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Jabatan / Posisi</label>
                        <input type="text" name="position" placeholder="Contoh: Customs Specialist PPJK" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-sky-500/20">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Gaji Pokok (Nominal Rp)</label>
                        <input type="number" name="base_salary" placeholder="5000000" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-sky-500/20 font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Jenis Perjanjian Kerja</label>
                        <select name="contract_kind" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold">
                            <option value="PKWT">PKWT (Kontrak Waktu Tertentu)</option>
                            <option value="PKWTT">PKWTT (Pegawai Tetap)</option>
                            <option value="INTERNSHIP">Magang (Internship)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal Mulai Bekerja</label>
                        <input type="date" name="start_date" value="{{ date('Y-m-d') }}" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-sky-500/20">
                    </div>
                </div>

                <div class="flex justify-end space-x-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="showCreateEmployeeModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold shadow-sm shadow-sky-600/20">
                        Simpan Pegawai
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL: TAMBAH TITIK GEOFENCE LOKASI KERJA                      -->
    <!-- ============================================================== -->
    <div x-show="showCreateLocationModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="showCreateLocationModal = false"
             class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Tambah Titik Geofence Baru</h3>
                <button type="button" @click="showCreateLocationModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('hrm.locations.store') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Lokasi Titik Tugas</label>
                    <input type="text" name="name" placeholder="Contoh: Terminal Petikemas Koja TPS Priok" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-sky-500/20">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Latitude</label>
                        <input type="number" step="0.000001" name="lat" value="-6.1038" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-mono">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Longitude</label>
                        <input type="number" step="0.000001" name="lng" value="106.8837" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-mono">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Radius Toleransi (Meter)</label>
                    <input type="number" name="radius_m" value="300" min="20" max="5000" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-mono">
                </div>

                <div class="flex justify-end space-x-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="showCreateLocationModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold">
                        Simpan Titik Geofence
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL: TAMBAH MASTER SHIFT                                     -->
    <!-- ============================================================== -->
    <div x-show="showCreateShiftModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="showCreateShiftModal = false"
             class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Tambah Master Shift Jam Kerja</h3>
                <button type="button" @click="showCreateShiftModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('hrm.shifts.store') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Shift</label>
                    <input type="text" name="name" placeholder="Contoh: Shift 3 Bongkar Muat Malam" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Jam Masuk (HH:MM)</label>
                        <input type="time" name="start_time" value="08:00" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Jam Pulang (HH:MM)</label>
                        <input type="time" name="end_time" value="17:00" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 items-center">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Toleransi (Menit)</label>
                        <input type="number" name="grace_period_minutes" value="15" min="0" max="120"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs">
                    </div>
                    <div class="pt-4">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="is_night_shift" value="1" class="rounded border-slate-300 text-indigo-600">
                            <span class="text-xs font-bold text-slate-700">Shift Malam (24 Jam)</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end space-x-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="showCreateShiftModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold">
                        Simpan Shift
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL: PENUGASAN JADWAL KERJA STAF                             -->
    <!-- ============================================================== -->
    <div x-show="showCreateScheduleModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="showCreateScheduleModal = false"
             class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Tugaskan Shift ke Karyawan</h3>
                <button type="button" @click="showCreateScheduleModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('hrm.schedules.store') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Pilih Pegawai</label>
                    <select name="employee_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold">
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->nik }} - {{ $emp->position }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Pilih Shift</label>
                    <select name="shift_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold">
                        @foreach($workShifts as $sh)
                            <option value="{{ $sh->id }}">{{ $sh->name }} ({{ $sh->formatted_hours }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tanggal Tugas</label>
                    <input type="date" name="work_date" value="{{ date('Y-m-d') }}" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs">
                </div>

                <div class="flex justify-end space-x-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="showCreateScheduleModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold">
                        Tugaskan Shift
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL: PREVIEW FOTO PRESENSI ZOOM                              -->
    <!-- ============================================================== -->
    <div x-show="showPhotoModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md">
        <div @click.away="showPhotoModal = false" class="relative max-w-sm w-full bg-slate-900 rounded-3xl p-3 border border-slate-800">
            <button type="button" @click="showPhotoModal = false"
                    class="absolute -top-3 -right-3 w-8 h-8 rounded-full bg-slate-800 text-white flex items-center justify-center hover:bg-slate-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <img :src="activePhotoUrl" class="w-full h-80 object-cover rounded-2xl" alt="Foto Selfie Presensi" />
            <div class="text-center text-xs font-mono text-slate-400 mt-2">Foto Verifikasi Biometrik Satelit</div>
        </div>
    </div>

</div>
@endsection
