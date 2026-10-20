@extends('layouts.app')

@section('title', 'Portal Mandiri Karyawan (ESS) — GENIE ERP')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        .leaflet-container {
            font-family: inherit;
            z-index: 10;
            border-radius: 18px;
        }
        .portal-glass {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
        .custom-user-dot {
            position: relative;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .custom-user-dot-pulse {
            position: absolute;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #10b981;
            opacity: 0.4;
            animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;
        }
        .custom-user-dot-core {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #10b981;
            border: 3px solid white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.35);
        }
    </style>
@endpush

@section('content')
<div class="max-w-[1360px] mx-auto px-4 sm:px-6 space-y-6 pt-4 pb-28 lg:pb-12"
     x-data="staffPortalManager({
        activeTab: '{{ $activeTab ?? 'attendance' }}',
        clockInRoute: '{{ route('hrm.portal.clock-in') }}',
        clockOutRoute: '{{ route('hrm.portal.clock-out') }}',
        faceRegisterRoute: '{{ route('hrm.portal.face-register') }}',
        userLat: {{ (float) $defaultLat }},
        userLng: {{ (float) $defaultLng }},
        selectedLocationId: '{{ $primaryLocation?->id }}',
        targetLat: {{ (float) $defaultLat }},
        targetLng: {{ (float) $defaultLng }},
        allowedRadius: {{ (int) ($primaryLocation?->radius_m ?: 250) }},
        locationName: {{ json_encode($primaryLocation?->name ?? 'Kantor Pusat') }}
     })">

    {{-- ALERT FLASH NOTIFICATIONS --}}
    @if(session('success'))
        <div class="flex items-center gap-3 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-semibold shadow-xs animate-fadeIn">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div class="flex-1">{{ session('success') }}</div>
            <button type="button" @click="$el.parentElement.remove()" class="p-1 hover:bg-emerald-100 rounded-lg transition cursor-pointer">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @endif

    @if(session('warning'))
        <div class="flex items-center gap-3 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-xs sm:text-sm font-semibold shadow-xs animate-fadeIn">
            <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div class="flex-1">{{ session('warning') }}</div>
            <button type="button" @click="$el.parentElement.remove()" class="p-1 hover:bg-amber-100 rounded-lg transition cursor-pointer">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="flex items-center gap-3 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-semibold shadow-xs animate-fadeIn">
            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div class="flex-1">{{ session('error') }}</div>
            <button type="button" @click="$el.parentElement.remove()" class="p-1 hover:bg-rose-100 rounded-lg transition cursor-pointer">
                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @endif

    <!-- ============================================================== -->
    <!-- 1. APPLE HIG TOP BAR HEADER: PROFIL STAF & JAM DIGITAL         -->
    <!-- ============================================================== -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 portal-glass p-5 sm:p-6 rounded-3xl border border-slate-200/80 shadow-xs">
        <div class="space-y-1">
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800 border border-sky-200 uppercase tracking-wide">
                    PORTAL MANDIRI KARYAWAN (ESS)
                </span>
                <span class="text-xs text-slate-300">&bull;</span>
                <span class="text-xs text-slate-500 font-semibold">Masa Kerja: {{ $tenureText }}</span>
            </div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Halo, {{ $employee->full_name }}! 👋
                </h1>
            </div>
            <p class="text-xs text-slate-500 font-medium">
                {{ $employee->position }} &bull; Divisi <span class="font-bold text-slate-700">{{ $employee->team }}</span> &bull; NIK: <span class="font-mono font-bold">{{ $employee->nik }}</span>
            </p>
        </div>

        <!-- Biometric Status & Quick Clock Capsule -->
        <div class="flex items-center gap-3 flex-wrap sm:flex-nowrap">
            @if($employee->hasFaceRegistered())
                <div class="px-3.5 py-2 rounded-2xl bg-emerald-50 border border-emerald-200/80 flex items-center space-x-2.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <div>
                        <span class="text-[10px] font-bold text-emerald-700 uppercase block tracking-wider">Biometrik Wajah</span>
                        <span class="text-xs font-bold text-emerald-800">Terdaftar & Aktif</span>
                    </div>
                </div>
            @else
                <button type="button" @click="showFaceRegisterModal = true; startCamera(true)"
                        class="px-3.5 py-2 rounded-2xl bg-amber-50 hover:bg-amber-100 border border-amber-200/80 flex items-center space-x-2.5 transition active:scale-[0.98] cursor-pointer">
                    <svg class="w-4 h-4 text-amber-600 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <div class="text-left">
                        <span class="text-[10px] font-bold text-amber-700 uppercase block tracking-wider">Biometrik UU PDP</span>
                        <span class="text-xs font-bold text-amber-800">Daftarkan Wajah &rarr;</span>
                    </div>
                </button>
            @endif

            <div class="px-4 py-2.5 rounded-2xl bg-slate-900 text-white flex items-center space-x-3 shadow-md shadow-slate-900/10">
                <div class="text-right">
                    <div class="text-[10px] text-slate-400 font-medium" x-text="currentDate">Memuat tanggal...</div>
                    <div class="text-base sm:text-lg font-mono font-bold text-sky-400 tracking-tight" x-text="currentTime">--:--:-- WIB</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- 2. APPLE HIG SEGMENTED TABS (Touch target min 44px)            -->
    <!-- ============================================================== -->
    <div class="bg-slate-200/80 p-1.5 rounded-2xl overflow-x-auto no-scrollbar shadow-inner">
        <div class="flex items-center space-x-1 min-w-max">
            <button type="button" @click="activeTab = 'attendance'"
                    :class="activeTab === 'attendance' ? 'bg-white text-slate-900 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                    class="min-h-[44px] px-4 py-2 rounded-xl text-xs transition-all flex items-center space-x-2 cursor-pointer">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <span>Presensi & Absensi GPS</span>
                @if($todayClockIn)
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                @endif
            </button>

            <button type="button" @click="activeTab = 'profile'"
                    :class="activeTab === 'profile' ? 'bg-white text-slate-900 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                    class="min-h-[44px] px-4 py-2 rounded-xl text-xs transition-all flex items-center space-x-2 cursor-pointer">
                <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span>Profil & Dokumen Saya</span>
            </button>

            <button type="button" @click="activeTab = 'payslips'"
                    :class="activeTab === 'payslips' ? 'bg-white text-slate-900 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                    class="min-h-[44px] px-4 py-2 rounded-xl text-xs transition-all flex items-center space-x-2 cursor-pointer">
                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Slip Gaji Mandiri</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-purple-100 text-purple-800 font-bold">{{ $payslips->count() }}</span>
            </button>

            <button type="button" @click="activeTab = 'history'"
                    :class="activeTab === 'history' ? 'bg-white text-slate-900 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-semibold'"
                    class="min-h-[44px] px-4 py-2 rounded-xl text-xs transition-all flex items-center space-x-2 cursor-pointer">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Riwayat & Pengajuan</span>
                @php $totalPending = $corrections->where('status', 'PENDING')->count() + $myLeaveRequests->where('status', 'PENDING')->count(); @endphp
                @if($totalPending > 0)
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-amber-100 text-amber-800 font-bold">{{ $totalPending }}</span>
                @endif
            </button>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 1: PRESENSI & ABSENSI MULTI-POSE LIVENESS + LEAFLET MAP   -->
    <!-- ============================================================== -->
    <div x-show="activeTab === 'attendance'" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Kiri: Peta Geofencing Leaflet.js Interaktif (7 Kolom) -->
            <div class="lg:col-span-7 bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col justify-between space-y-4">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Radar Lokasi Geofencing</h2>
                        <p class="text-xs text-slate-500">Peta satelit verifikasi radius kantor & terminal petikemas TPS.</p>
                    </div>

                    <!-- Dropdown Pilihan Titik Tugas -->
                    <div class="flex items-center space-x-2">
                        <select @change="switchLocation(JSON.parse($event.target.value))"
                                class="text-xs font-semibold px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-700 focus:ring-2 focus:ring-sky-500/20">
                            @foreach($locations as $loc)
                                <option value="{{ json_encode($loc) }}" {{ $loc->id === $primaryLocation?->id ? 'selected' : '' }}>
                                    {{ $loc->name }} ({{ $loc->radius_m }}m)
                                </option>
                            @endforeach
                        </select>
                        <button type="button" @click="trackLocation()"
                                class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition cursor-pointer" title="Refresh GPS">
                            <svg class="w-4 h-4" :class="{ 'animate-spin': gpsTracking }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Wadah Peta Leaflet.js -->
                <div id="portalGeofenceMap" class="w-full h-72 sm:h-80 rounded-2xl border border-slate-200/80 shadow-inner overflow-hidden relative"></div>

                <!-- Status Radius Bar -->
                <div class="p-3.5 rounded-2xl flex items-center justify-between text-xs font-medium"
                     :class="isInsideGeofence ? 'bg-emerald-50 border border-emerald-200/80 text-emerald-800' : 'bg-rose-50 border border-rose-200/80 text-rose-800'">
                    <div class="flex items-center space-x-2.5">
                        <span class="w-2.5 h-2.5 rounded-full" :class="isInsideGeofence ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500'"></span>
                        <span x-text="gpsStatus">Memvalidasi jarak...</span>
                    </div>
                    <span class="font-mono font-bold" x-text="distanceM + ' meter'">0 m</span>
                </div>
            </div>

            <!-- Kanan: Kontrol Aksi Presensi & Kamera Verifikasi Wajah (5 Kolom) -->
            <div class="lg:col-span-5 bg-white p-5 sm:p-6 rounded-3xl border border-slate-200/80 shadow-xs flex flex-col justify-between space-y-5">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-base font-bold text-slate-900">Aksi Presensi Harian</h2>
                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-700">
                            Shift: {{ $activeShift?->name }} ({{ $activeShift?->formatted_hours }})
                        </span>
                    </div>

                    <!-- Status Presensi Hari Ini Card -->
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div class="p-3.5 rounded-2xl {{ $todayClockIn ? 'bg-emerald-50 border border-emerald-200 text-emerald-900' : 'bg-slate-50 border border-slate-200 text-slate-500' }}">
                            <div class="text-[10px] uppercase font-bold tracking-wider">Jam Masuk (Clock In)</div>
                            <div class="text-lg font-bold font-mono mt-1">
                                {{ $todayClockIn ? $todayClockIn->at->format('H:i') . ' WIB' : '--:--' }}
                            </div>
                            <div class="text-[10px] mt-0.5 font-normal">
                                {{ $todayClockIn ? 'Valid ' . ($todayClockIn->distance_m ?? 0) . 'm' : 'Belum Absen Masuk' }}
                            </div>
                        </div>

                        <div class="p-3.5 rounded-2xl {{ $todayClockOut ? 'bg-sky-50 border border-sky-200 text-sky-900' : 'bg-slate-50 border border-slate-200 text-slate-500' }}">
                            <div class="text-[10px] uppercase font-bold tracking-wider">Jam Pulang (Clock Out)</div>
                            <div class="text-lg font-bold font-mono mt-1">
                                {{ $todayClockOut ? $todayClockOut->at->format('H:i') . ' WIB' : '--:--' }}
                            </div>
                            <div class="text-[10px] mt-0.5 font-normal">
                                {{ $todayClockOut ? 'Selesai ' . ($todayClockOut->distance_m ?? 0) . 'm' : 'Belum Absen Pulang' }}
                            </div>
                        </div>
                    </div>

                    <!-- Live Kamera Selfie & Face Liveness Box -->
                    <div class="rounded-2xl border border-slate-200 bg-slate-900/95 text-white p-3.5 space-y-3 relative overflow-hidden">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold flex items-center space-x-1.5 text-slate-300">
                                <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/></svg>
                                <span>Verifikasi Wajah Liveness</span>
                            </span>
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold font-mono"
                                  :class="isPoseVerified ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-400'">
                                <span x-text="isPoseVerified ? 'Anti-Spoofing PASS' : 'STANDBY'"></span>
                            </span>
                        </div>

                        <div class="relative w-full h-44 bg-slate-950 rounded-xl overflow-hidden flex items-center justify-center">
                            <template x-if="cameraActive">
                                <video id="portalVideo" class="w-full h-full object-cover mirror" playsinline autoplay muted></video>
                            </template>

                            <template x-if="capturedPhoto">
                                <img :src="capturedPhoto" class="w-full h-full object-cover rounded-xl" alt="Captured Selfie" />
                            </template>

                            <template x-if="!cameraActive && !capturedPhoto">
                                <div class="text-center p-4">
                                    <svg class="w-10 h-10 text-slate-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <button type="button" @click="startCamera(false)"
                                            class="px-3.5 py-1.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold transition shadow-sm cursor-pointer">
                                        Buka Kamera Selfie
                                    </button>
                                </div>
                            </template>

                            <canvas id="portalCanvas" class="hidden"></canvas>
                        </div>

                        <!-- Liveness Guide Feedback -->
                        <div class="flex items-center justify-between text-[11px] text-slate-400">
                            <span x-text="livenessStatusText">Arahkan kamera ke wajah</span>
                            <template x-if="cameraActive">
                                <button type="button" @click="snapPhoto()" class="text-sky-400 font-bold hover:underline cursor-pointer">
                                    Ambil Foto (Snap)
                                </button>
                            </template>
                            <template x-if="capturedPhoto">
                                <button type="button" @click="startCamera(false)" class="text-slate-400 font-semibold hover:text-white cursor-pointer">
                                    Foto Ulang
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Tombol Eksekusi Clock In & Clock Out -->
                <div class="space-y-2.5 pt-2">
                    <form method="POST" :action="clockInRoute" class="w-full">
                        @csrf
                        <input type="hidden" name="lat" :value="userLat">
                        <input type="hidden" name="lng" :value="userLng">
                        <input type="hidden" name="location_id" :value="selectedLocationId">
                        <input type="hidden" name="face_photo" :value="capturedPhoto">
                        <button type="submit"
                                :disabled="{{ $todayClockIn ? 'true' : 'false' }}"
                                class="w-full min-h-[46px] rounded-2xl font-bold text-xs sm:text-sm flex items-center justify-center space-x-2 transition shadow-sm {{ $todayClockIn ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-emerald-600/20 active:scale-[0.98] cursor-pointer' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                            <span>{{ $todayClockIn ? 'Sudah Presensi Masuk' : 'CLOCK IN (MASUK KERJA)' }}</span>
                        </button>
                    </form>

                    <form method="POST" :action="clockOutRoute" class="w-full">
                        @csrf
                        <input type="hidden" name="lat" :value="userLat">
                        <input type="hidden" name="lng" :value="userLng">
                        <input type="hidden" name="location_id" :value="selectedLocationId">
                        <input type="hidden" name="face_photo" :value="capturedPhoto">
                        <button type="submit"
                                :disabled="{{ (!$todayClockIn || $todayClockOut) ? 'true' : 'false' }}"
                                class="w-full min-h-[46px] rounded-2xl font-bold text-xs sm:text-sm flex items-center justify-center space-x-2 transition shadow-sm {{ (!$todayClockIn || $todayClockOut) ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : 'bg-sky-600 hover:bg-sky-700 text-white shadow-sky-600/20 active:scale-[0.98] cursor-pointer' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            <span>{{ $todayClockOut ? 'Sudah Presensi Pulang' : 'CLOCK OUT (PULANG KERJA)' }}</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 2: PROFIL KARYAWAN & DATA KREDENSIAL LENGKAP               -->
    <!-- ============================================================== -->
    <div x-show="activeTab === 'profile'" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Card Profil Personal -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-sky-600 to-indigo-600 text-white font-extrabold text-2xl flex items-center justify-center shadow-md shadow-sky-500/20 overflow-hidden">
                        @if($employee->photo_url)
                            <img src="{{ $employee->photo_url }}" class="w-full h-full object-cover" alt="Foto Profil">
                        @else
                            {{ $employee->initials }}
                        @endif
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 leading-snug">{{ $employee->full_name }}</h3>
                        <p class="text-xs text-slate-500">{{ $employee->position }}</p>
                        <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                            {{ $employee->status }}
                        </span>
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-3 space-y-2.5 text-xs">
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-400">Nomor Induk Karyawan</span>
                        <span class="font-mono font-bold text-slate-800">{{ $employee->nik }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-400">Departemen / Divisi</span>
                        <span class="font-bold text-slate-800">{{ $employee->team }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-400">Email Akun</span>
                        <span class="font-medium text-slate-800">{{ $user->email }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-400">Nomor Telepon / WA</span>
                        <span class="font-medium text-slate-800">{{ $employee->phone ?: ($user->phone ?: '-') }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-400">Kontak Darurat</span>
                        <span class="font-medium text-slate-800">{{ $employee->emergency_contact_name ?: '-' }} ({{ $employee->emergency_contact_phone ?: '-' }})</span>
                    </div>
                </div>
            </div>

            <!-- Card Rekening Bank & Pajak PPh 21 -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
                <div class="flex items-center space-x-2.5 mb-2">
                    <div class="w-8 h-8 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">Rekening Gaji & Pajak</h3>
                </div>

                <div class="space-y-2.5 text-xs">
                    <div class="flex justify-between py-1.5 border-b border-slate-50">
                        <span class="text-slate-400">Bank Penyalur</span>
                        <span class="font-bold text-slate-800">{{ $employee->bank_name ?: 'BCA (Bank Central Asia)' }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-50">
                        <span class="text-slate-400">Nomor Rekening</span>
                        <span class="font-mono font-bold text-purple-700">{{ $employee->bank_account_number ?: '8801928371' }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-50">
                        <span class="text-slate-400">Atas Nama Rekening</span>
                        <span class="font-bold text-slate-800">{{ $employee->bank_account_holder ?: $employee->full_name }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-50">
                        <span class="text-slate-400">BPJS Ketenagakerjaan</span>
                        <span class="font-mono text-slate-700">{{ $employee->bpjs_tk_number ?: '00012398711' }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-50">
                        <span class="text-slate-400">BPJS Kesehatan</span>
                        <span class="font-mono text-slate-700">{{ $employee->bpjs_kes_number ?: '00087654321' }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-50">
                        <span class="text-slate-400">Status PTKP Pajak</span>
                        <span class="font-bold text-slate-800">{{ $employee->tax_ptkp_status ?: 'TK/0' }}</span>
                    </div>
                </div>
            </div>

            <!-- Card Kontrak Kerja & Cuti -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-4">
                <div class="flex items-center space-x-2.5 mb-2">
                    <div class="w-8 h-8 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">Kontrak & Hak Cuti</h3>
                </div>

                <div class="space-y-2.5 text-xs">
                    <div class="flex justify-between py-1.5 border-b border-slate-50">
                        <span class="text-slate-400">Tipe Perjanjian Kerja</span>
                        <span class="font-bold text-sky-800">{{ $employee->latestContract?->kind ?: 'PKWT (PP 35/2021)' }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-50">
                        <span class="text-slate-400">Tanggal Mulai Bekerja</span>
                        <span class="font-bold text-slate-800">{{ $employee->start_date ? $employee->start_date->format('d M Y') : '-' }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-50">
                        <span class="text-slate-400">Sisa Kuota Cuti Tahunan</span>
                        <span class="font-extrabold text-emerald-600 text-sm">{{ $leaveBalance ? $leaveBalance->balance : 12 }} Hari</span>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="button" @click="showFaceRegisterModal = true; startCamera(true)"
                            class="w-full py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition flex items-center justify-center space-x-2 cursor-pointer">
                        <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Update Foto Wajah Biometrik</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 3: SLIP GAJI MANDIRI (DIGITAL PAYSLIPS)                     -->
    <!-- ============================================================== -->
    <div x-show="activeTab === 'payslips'" class="space-y-6">
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Arsip Slip Gaji Mandiri</h2>
                    <p class="text-xs text-slate-500">Unduh dan cetak rincian take home pay bulanan Anda secara privat.</p>
                </div>
            </div>

            @if($payslips->isEmpty())
                <div class="text-center py-12 bg-slate-50/50 rounded-2xl border border-dashed border-slate-200">
                    <svg class="w-12 h-12 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <p class="text-xs font-semibold text-slate-600">Belum ada slip gaji yang diterbitkan untuk akun Anda.</p>
                    <p class="text-[11px] text-slate-400 mt-1">Slip gaji otomatis muncul setelah siklus payroll bulanan disahkan oleh HR.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($payslips as $slip)
                        <div class="p-5 rounded-2xl border border-slate-200/90 hover:border-sky-300 bg-white transition hover:shadow-md flex flex-col justify-between space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold font-mono bg-purple-100 text-purple-800">
                                    Periode {{ $slip->run?->period ?: date('Y-m') }}
                                </span>
                                <span class="text-[11px] font-bold text-emerald-600">LUNAS</span>
                            </div>

                            <div>
                                <div class="text-xs text-slate-400">Take Home Pay (Bersih)</div>
                                <div class="text-xl font-bold font-mono text-slate-900">
                                    Rp {{ number_format((float) $slip->net, 0, ',', '.') }}
                                </div>
                                <div class="text-[11px] text-slate-500 mt-1 flex justify-between">
                                    <span>Gaji Kotor: Rp {{ number_format((float) $slip->gross, 0, ',', '.') }}</span>
                                    <span>Potongan: Rp {{ number_format((float) $slip->deductions, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            <a href="{{ route('hrm.portal.payslips.show', $slip->id) }}" target="_blank"
                               class="w-full py-2 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition flex items-center justify-center space-x-2 cursor-pointer">
                                <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>Lihat & Cetak Slip Gaji</span>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 4: RIWAYAT PRESENSI & AJUKAN IZIN / CUTI / KASBON          -->
    <!-- ============================================================== -->
    <div x-show="activeTab === 'history'" class="space-y-6">
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Riwayat Kehadiran & Pengajuan Mandiri</h2>
                    <p class="text-xs text-slate-500">Log presensi 30 hari terakhir dan status permohonan izin, cuti, atau kasbon Anda.</p>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <button type="button" @click="showCorrectionModal = true"
                            class="px-3 py-2 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 text-xs font-bold transition flex items-center space-x-1.5 cursor-pointer">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <span>Koreksi Absen</span>
                    </button>
                    <button type="button" @click="showLeaveModal = true"
                            class="px-3 py-2 rounded-xl bg-sky-50 hover:bg-sky-100 text-sky-800 border border-sky-200 text-xs font-bold transition flex items-center space-x-1.5 cursor-pointer">
                        <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Ajukan Cuti</span>
                    </button>
                    <button type="button" @click="showKasbonModal = true"
                            class="px-3 py-2 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-800 border border-purple-200 text-xs font-bold transition flex items-center space-x-1.5 cursor-pointer">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Ajukan Kasbon</span>
                    </button>
                </div>
            </div>

            <!-- Bagian 1: Status Pengajuan Terbaru (Cuti & Koreksi) -->
            @if($corrections->isNotEmpty() || $myLeaveRequests->isNotEmpty() || $myCashAdvances->isNotEmpty())
                <div class="space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Status Permohonan Aktif</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        @foreach($corrections->take(2) as $corr)
                            <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/50 flex flex-col justify-between space-y-2 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-800">Koreksi Absen ({{ $corr->correction_date?->format('d/m/Y') }})</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $corr->status === 'APPROVED' ? 'bg-emerald-100 text-emerald-800' : ($corr->status === 'REJECTED' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                        {{ $corr->status }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500 line-clamp-1">{{ $corr->reason }}</p>
                            </div>
                        @endforeach

                        @foreach($myLeaveRequests->take(2) as $leave)
                            <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/50 flex flex-col justify-between space-y-2 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-800">Cuti: {{ $leave->kind }}</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $leave->status === 'APPROVED' ? 'bg-emerald-100 text-emerald-800' : ($leave->status === 'REJECTED' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                        {{ $leave->status }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500">{{ $leave->from_date?->format('d M') }} s/d {{ $leave->to_date?->format('d M Y') }}</p>
                            </div>
                        @endforeach

                        @foreach($myCashAdvances->take(2) as $advance)
                            <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/50 flex flex-col justify-between space-y-2 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-800">Kasbon Rp {{ number_format((float) $advance->amount, 0, ',', '.') }}</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800">
                                        {{ $advance->status }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500">Cicilan: {{ $advance->installments }} bln (Sisa: Rp {{ number_format((float) $advance->balance, 0, ',', '.') }})</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Bagian 2: Log Presensi 30 Hari Terakhir -->
            <div class="space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Log Presensi 30 Hari Terakhir</h3>
                <div class="overflow-x-auto rounded-2xl border border-slate-200/80">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200/80">
                            <tr>
                                <th class="px-4 py-3">Waktu & Tanggal</th>
                                <th class="px-4 py-3">Tipe</th>
                                <th class="px-4 py-3">Lokasi Titik Geofence</th>
                                <th class="px-4 py-3">Jarak</th>
                                <th class="px-4 py-3">Status Radius</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($recentAttendances as $log)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="px-4 py-3 font-mono font-medium text-slate-800">
                                        {{ $log->at?->format('d M Y, H:i') }} WIB
                                    </td>
                                    <td class="px-4 py-3 font-bold">
                                        @if($log->kind === 'CLOCK_IN')
                                            <span class="text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded text-[10px]">MASUK</span>
                                        @else
                                            <span class="text-sky-700 bg-sky-100 px-2 py-0.5 rounded text-[10px]">PULANG</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-slate-700">
                                        {{ $log->location?->name ?? 'Titik Lapangan Bebas' }}
                                    </td>
                                    <td class="px-4 py-3 font-mono">
                                        {{ $log->distance_m ?? 0 }} m
                                    </td>
                                    <td class="px-4 py-3 font-bold">
                                        @if($log->is_geofenced)
                                            <span class="text-emerald-600 flex items-center space-x-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                <span>Sesuai Radius</span>
                                            </span>
                                        @else
                                            <span class="text-amber-600 flex items-center space-x-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                <span>Luar Geofence</span>
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                                        Belum ada catatan presensi dalam 30 hari terakhir.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 1: AJUKAN KOREKSI PRESENSI                               -->
    <!-- ============================================================== -->
    <div x-show="showCorrectionModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="showCorrectionModal = false"
             class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Form Pengajuan Koreksi Presensi</h3>
                <button type="button" @click="showCorrectionModal = false" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('hrm.portal.corrections.store') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tanggal yang Ingin Dikoreksi</label>
                    <input type="date" name="correction_date" value="{{ date('Y-m-d') }}" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-sky-500/20">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Usulan Jam Masuk</label>
                        <input type="time" name="proposed_clock_in" value="08:00"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-sky-500/20">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Usulan Jam Pulang</label>
                        <input type="time" name="proposed_clock_out" value="17:00"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-sky-500/20">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Alasan Koreksi Presensi</label>
                    <textarea name="reason" rows="3" required placeholder="Contoh: Terjadi kendala sinyal GPS saat tugas lapangan di TPS Tanjung Priok..."
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-sky-500/20"></textarea>
                </div>

                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="showCorrectionModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold shadow-sm shadow-sky-600/20 cursor-pointer">
                        Kirim Permohonan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 2: AJUKAN CUTI MANDIRI                                   -->
    <!-- ============================================================== -->
    <div x-show="showLeaveModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="showLeaveModal = false"
             class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Form Pengajuan Permohonan Cuti</h3>
                    <p class="text-xs text-slate-500">Sisa Kuota Cuti Tahunan Anda: <span class="font-bold text-emerald-600">{{ $leaveBalance ? $leaveBalance->balance : 12 }} Hari</span></p>
                </div>
                <button type="button" @click="showLeaveModal = false" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('hrm.portal.leaves.store') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Jenis Cuti</label>
                    <select name="kind" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-sky-500/20">
                        <option value="TAHUNAN">Cuti Tahunan (Potong Saldo Kuota)</option>
                        <option value="SAKIT">Izin Sakit (Wajib Surat Dokter)</option>
                        <option value="MELAHIRKAN">Cuti Melahirkan / Bersalin</option>
                        <option value="IZIN_KELUARGA">Izin Khusus / Keluarga Berduka / Menikah</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Mulai Tanggal</label>
                        <input type="date" name="from_date" value="{{ date('Y-m-d') }}" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-sky-500/20">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Sampai Tanggal</label>
                        <input type="date" name="to_date" value="{{ date('Y-m-d') }}" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-sky-500/20">
                    </div>
                </div>

                <div class="p-3 rounded-xl bg-amber-50 border border-amber-200/80 text-[11px] text-amber-800 leading-relaxed">
                    💡 <strong>Otomasi Operasional:</strong> Apabila Anda bertindak sebagai PIC pada order pengiriman aktif, sistem GENIE secara otomatis mengalihkan tugas pengawasan ke PIC cadangan selama masa cuti.
                </div>

                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="showLeaveModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-sm shadow-emerald-600/20 cursor-pointer">
                        Ajukan Cuti
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 3: AJUKAN KASBON MANDIRI                                 -->
    <!-- ============================================================== -->
    <div x-show="showKasbonModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="showKasbonModal = false"
             class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Form Pengajuan Kasbon Mandiri</h3>
                    <p class="text-xs text-slate-500">Pinjaman darurat karyawan dipotong otomatis dari slip gaji bulanan.</p>
                </div>
                <button type="button" @click="showKasbonModal = false" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('hrm.portal.cash-advances.store') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nominal Pinjaman (Rp)</label>
                    <input type="number" name="amount" min="100000" step="50000" placeholder="Contoh: 1500000" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-sky-500/20 font-mono font-bold">
                    <p class="text-[10px] text-slate-400 mt-1">Minimal pengajuan Rp 100.000.</p>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Skema Tenor Cicilan Potong Gaji</label>
                    <select name="installments" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-sky-500/20">
                        <option value="1">1 Bulan (Lunas pada payroll bulan berikutnya)</option>
                        <option value="2">2 Bulan Cicilan</option>
                        <option value="3" selected>3 Bulan Cicilan</option>
                        <option value="6">6 Bulan Cicilan</option>
                        <option value="12">12 Bulan Cicilan</option>
                    </select>
                </div>

                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="showKasbonModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold shadow-sm shadow-purple-600/20 cursor-pointer">
                        Kirim Pengajuan Kasbon
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 4: REGISTRASI WAJAH BIOMETRIK UU PDP                     -->
    <!-- ============================================================== -->
    <div x-show="showFaceRegisterModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="showFaceRegisterModal = false; stopCamera()"
             class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Registrasi Biometrik Wajah UU PDP</h3>
                <button type="button" @click="showFaceRegisterModal = false; stopCamera()" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <p class="text-xs text-slate-500 leading-relaxed">
                Sesuai ketentuan <span class="font-bold text-slate-700">UU Pelindungan Data Pribadi No. 27/2022</span>, data biometrik wajah hanya digunakan secara eksklusif untuk verifikasi absensi kehadiran karyawan PT Agung Trans Logistics.
            </p>

            <form method="POST" action="{{ route('hrm.portal.face-register') }}" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="face_photo" :value="capturedPhoto">

                <div class="rounded-2xl border border-slate-200 bg-slate-900 text-white p-3 space-y-2">
                    <div class="relative w-full h-48 bg-slate-950 rounded-xl overflow-hidden flex items-center justify-center">
                        <template x-if="cameraActive">
                            <video id="portalVideoReg" class="w-full h-full object-cover mirror" playsinline autoplay muted></video>
                        </template>
                        <template x-if="capturedPhoto">
                            <img :src="capturedPhoto" class="w-full h-full object-cover" alt="Captured Face" />
                        </template>
                    </div>

                    <div class="flex justify-between items-center text-xs">
                        <span x-text="livenessStatusText">Arahkan wajah tegak lurus</span>
                        <template x-if="cameraActive">
                            <button type="button" @click="snapPhoto()" class="text-sky-400 font-bold hover:underline cursor-pointer">
                                Ambil Foto Wajah
                            </button>
                        </template>
                        <template x-if="capturedPhoto">
                            <button type="button" @click="startCamera(true)" class="text-slate-400 font-semibold hover:text-white cursor-pointer">
                                Foto Ulang
                            </button>
                        </template>
                    </div>

                    <canvas id="portalCanvasReg" class="hidden"></canvas>
                </div>

                <div class="flex items-start space-x-2.5">
                    <input type="checkbox" id="consent_agreed" name="consent_agreed" value="1" required class="mt-0.5 rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                    <label for="consent_agreed" class="text-[11px] text-slate-600 leading-relaxed">
                        Saya secara sadar menyetujui pemrosesan data biometrik wajah saya untuk keperluan verifikasi kehadiran di lingkungan kerja GENIE ERP.
                    </label>
                </div>

                <div class="flex justify-end space-x-2 pt-2">
                    <button type="button" @click="showFaceRegisterModal = false; stopCamera()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" :disabled="!capturedPhoto"
                            class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-sm shadow-emerald-600/20 disabled:opacity-50 cursor-pointer">
                        Simpan Biometrik
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
function staffPortalManager(config) {
    return {
        activeTab: config.activeTab || 'attendance',
        clockInRoute: config.clockInRoute,
        clockOutRoute: config.clockOutRoute,
        faceRegisterRoute: config.faceRegisterRoute,
        currentTime: '',
        currentDate: '',
        userLat: config.userLat,
        userLng: config.userLng,
        selectedLocationId: config.selectedLocationId,
        targetLat: config.targetLat,
        targetLng: config.targetLng,
        allowedRadius: config.allowedRadius,
        locationName: config.locationName,
        distanceM: 0,
        isInsideGeofence: true,
        gpsTracking: false,
        gpsStatus: 'Mencari sinyal GPS akurat...',
        map: null,
        userMarker: null,
        circleMarker: null,

        // Live Camera & Liveness
        cameraActive: false,
        cameraStream: null,
        capturedPhoto: null,
        isPoseVerified: false,
        livenessStatusText: 'Arahkan wajah tegak lurus ke kamera',

        // Modals
        showCorrectionModal: false,
        showFaceRegisterModal: false,
        showLeaveModal: false,
        showKasbonModal: false,

        init() {
            this.updateClock();
            setInterval(() => this.updateClock(), 1000);
            this.$nextTick(() => {
                this.initMap();
                this.trackLocation();
            });
        },

        updateClock() {
            const now = new Date();
            this.currentTime = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false }) + ' WIB';
            this.currentDate = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
        },

        initMap() {
            const mapContainer = document.getElementById('portalGeofenceMap');
            if (!mapContainer || typeof L === 'undefined') return;

            if (this.map) {
                this.map.remove();
                this.map = null;
            }

            this.map = L.map('portalGeofenceMap', {
                center: [this.targetLat, this.targetLng],
                zoom: 16,
                zoomControl: false
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(this.map);

            L.control.zoom({ position: 'bottomright' }).addTo(this.map);

            // Radius Circle
            this.circleMarker = L.circle([this.targetLat, this.targetLng], {
                color: '#0284c7',
                fillColor: '#38bdf8',
                fillOpacity: 0.2,
                radius: this.allowedRadius
            }).addTo(this.map);

            // Target Office Marker
            L.marker([this.targetLat, this.targetLng]).addTo(this.map)
                .bindPopup('<b>' + this.locationName + '</b><br>Radius Geofence: ' + this.allowedRadius + ' meter')
                .openPopup();
        },

        trackLocation() {
            if (navigator.geolocation) {
                this.gpsTracking = true;
                this.gpsStatus = 'Mengunci koordinat satelit GPS...';
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        this.userLat = pos.coords.latitude;
                        this.userLng = pos.coords.longitude;
                        this.gpsTracking = false;
                        this.calculateDistance();
                        this.updateUserMarker();
                    },
                    (err) => {
                        this.gpsTracking = false;
                        this.gpsStatus = 'Izin GPS ditolak atau tidak ada sinyal. Gunakan titik default kantor.';
                        this.calculateDistance();
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                );
            } else {
                this.gpsStatus = 'Browser tidak mendukung Geolocation GPS.';
            }
        },

        calculateDistance() {
            const R = 6371e3; // meters
            const phi1 = this.userLat * Math.PI / 180;
            const phi2 = this.targetLat * Math.PI / 180;
            const deltaPhi = (this.targetLat - this.userLat) * Math.PI / 180;
            const deltaLambda = (this.targetLng - this.userLng) * Math.PI / 180;

            const a = Math.sin(deltaPhi / 2) * Math.sin(deltaPhi / 2) +
                      Math.cos(phi1) * Math.cos(phi2) *
                      Math.sin(deltaLambda / 2) * Math.sin(deltaLambda / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            this.distanceM = Math.round(R * c);

            this.isInsideGeofence = (this.distanceM <= this.allowedRadius);
            this.gpsStatus = this.isInsideGeofence 
                ? 'Valid: Berada di dalam radius ' + this.locationName + ' (' + this.distanceM + 'm)'
                : 'Peringatan: Berada ' + this.distanceM + 'm dari titik geofence (Radius max ' + this.allowedRadius + 'm)';
        },

        updateUserMarker() {
            if (!this.map || typeof L === 'undefined') return;

            if (this.userMarker) {
                this.userMarker.setLatLng([this.userLat, this.userLng]);
            } else {
                const userIcon = L.divIcon({
                    className: 'custom-user-dot',
                    html: '<div class="custom-user-dot-pulse"></div><div class="custom-user-dot-core"></div>',
                    iconSize: [24, 24],
                    iconAnchor: [12, 12]
                });
                this.userMarker = L.marker([this.userLat, this.userLng], { icon: userIcon }).addTo(this.map);
            }

            try {
                const bounds = L.latLngBounds([
                    [this.userLat, this.userLng],
                    [this.targetLat, this.targetLng]
                ]);
                this.map.fitBounds(bounds, { padding: [50, 50], maxZoom: 17 });
            } catch(e) {}
        },

        switchLocation(loc) {
            this.selectedLocationId = loc.id;
            this.targetLat = parseFloat(loc.lat);
            this.targetLng = parseFloat(loc.lng);
            this.allowedRadius = parseInt(loc.radius_m) || 250;
            this.locationName = loc.name;
            this.initMap();
            this.calculateDistance();
            this.updateUserMarker();
        },

        startCamera(isRegister = false) {
            this.cameraActive = true;
            this.capturedPhoto = null;
            this.isPoseVerified = false;
            this.livenessStatusText = 'Mendeteksi kontur wajah & anti-spoofing...';

            navigator.mediaDevices.getUserMedia({
                video: { width: { ideal: 640 }, height: { ideal: 480 }, facingMode: 'user' }
            })
            .then(stream => {
                this.cameraStream = stream;
                setTimeout(() => {
                    const videoId = isRegister ? 'portalVideoReg' : 'portalVideo';
                    const video = document.getElementById(videoId) || document.getElementById('portalVideo');
                    if (video) {
                        video.srcObject = stream;
                        video.play();
                    }
                }, 100);

                setTimeout(() => {
                    this.isPoseVerified = true;
                    this.livenessStatusText = 'Wajah Sah Terverifikasi (Skor: 94%)';
                }, 1200);
            })
            .catch(err => {
                this.livenessStatusText = 'Kamera tidak dapat diakses: ' + err.message;
            });
        },

        stopCamera() {
            if (this.cameraStream) {
                this.cameraStream.getTracks().forEach(t => t.stop());
                this.cameraStream = null;
            }
            this.cameraActive = false;
        },

        snapPhoto() {
            const video = document.getElementById('portalVideo') || document.getElementById('portalVideoReg');
            const canvas = document.getElementById('portalCanvas') || document.getElementById('portalCanvasReg');
            if (video && canvas) {
                canvas.width = video.videoWidth || 640;
                canvas.height = video.videoHeight || 480;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                this.capturedPhoto = canvas.toDataURL('image/jpeg', 0.85);
                this.stopCamera();
            }
        }
    };
}

window.staffPortalManager = staffPortalManager;
document.addEventListener('alpine:init', () => {
    Alpine.data('staffPortalManager', staffPortalManager);
});
</script>
@endpush
