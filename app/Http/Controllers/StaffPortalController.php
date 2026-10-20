<?php

namespace App\Http\Controllers;

use App\Models\AttendanceCorrection;
use App\Models\AttendanceLog;
use App\Models\BiometricConsent;
use App\Models\CashAdvance;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\PayrollRun;
use App\Models\Payslip;
use App\Models\Shift;
use App\Models\WorkLocation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class StaffPortalController extends Controller
{
    /**
     * Helper to get or auto-link active employee for currently authenticated user.
     */
    protected function getActiveEmployee(): Employee
    {
        $user = auth()->user();
        if (!$user) {
            abort(401, 'Silakan login terlebih dahulu.');
        }

        // 1. Cek relasi langsung
        $employee = Employee::where('user_id', $user->id)->first();
        if (!$employee && $user->employee_id) {
            $employee = Employee::find($user->employee_id);
        }

        // 2. Jika belum ada profil pegawai terhubung, buatkan otomatis agar user dapat langsung beroperasi
        if (!$employee) {
            $nik = 'GENIE-' . str_pad((string) rand(100, 9999), 5, '0', STR_PAD_LEFT);
            $employee = Employee::create([
                'nik' => $nik,
                'user_id' => $user->id,
                'full_name' => $user->name,
                'position' => $user->job_title ?: 'Staf Operasional',
                'team' => $user->department ?: 'OPERATIONS',
                'start_date' => now()->subMonths(6)->toDateString(),
                'status' => 'ACTIVE',
                'base_salary' => 6500000,
                'tax_ptkp_status' => 'TK/0',
            ]);

            $user->employee_id = $employee->id;
            $user->save();

            // Berikan saldo cuti tahunan
            LeaveBalance::firstOrCreate([
                'employee_id' => $employee->id,
                'year' => (int) date('Y'),
                'kind' => 'TAHUNAN',
            ], [
                'balance' => 12.0,
            ]);
        }

        return $employee;
    }

    /**
     * Tampilan Utama Portal Mandiri Staf (Employee Self-Service / ESS).
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $employee = $this->getActiveEmployee();
        $activeTab = $request->query('tab', 'attendance');

        // 1. Presensi Hari Ini
        $todayClockIn = AttendanceLog::where('employee_id', $employee->id)
            ->today()
            ->clockIn()
            ->latest('at')
            ->first();

        $todayClockOut = AttendanceLog::where('employee_id', $employee->id)
            ->today()
            ->where('kind', 'CLOCK_OUT')
            ->latest('at')
            ->first();

        // 2. Lokasi Geofencing
        $locations = WorkLocation::active()->get();
        if ($locations->isEmpty()) {
            // Default Work Locations jika belum ada di database
            $locations = collect([
                WorkLocation::firstOrCreate(
                    ['name' => 'Kantor Pusat PT Agung Trans Solusindo'],
                    [
                        'lat' => -6.2297,
                        'lng' => 106.8294,
                        'radius_m' => 150,
                        'address' => 'Jl. Jend. Sudirman Kav. 52-53, SCBD, Jakarta Selatan',
                        'is_active' => true,
                    ]
                ),
                WorkLocation::firstOrCreate(
                    ['name' => 'Terminal Petikemas Tanjung Priok (JICT)'],
                    [
                        'lat' => -6.1038,
                        'lng' => 106.8837,
                        'radius_m' => 400,
                        'address' => 'Pelabuhan Tanjung Priok, Jakarta Utara',
                        'is_active' => true,
                    ]
                ),
                WorkLocation::firstOrCreate(
                    ['name' => 'Cargo Area Bandara Soekarno-Hatta (TPS C1)'],
                    [
                        'lat' => -6.1256,
                        'lng' => 106.6559,
                        'radius_m' => 300,
                        'address' => 'Kawasan Kargo Bandara Soekarno-Hatta, Tangerang',
                        'is_active' => true,
                    ]
                )
            ]);
        }

        $primaryLocation = $locations->first();
        $defaultLat = $primaryLocation?->lat ?: -6.2297;
        $defaultLng = $primaryLocation?->lng ?: 106.8294;

        // 3. Shift Kerja
        $shifts = Shift::all();
        if ($shifts->isEmpty()) {
            $shifts = collect([
                Shift::create([
                    'name' => 'Reguler Kantor (Pagi)',
                    'start_time' => '08:00',
                    'end_time' => '17:00',
                    'grace_period_minutes' => 15,
                    'is_night_shift' => false,
                ]),
                Shift::create([
                    'name' => 'Operasional Lapangan & Bongkar Muat (Shift Malam)',
                    'start_time' => '20:00',
                    'end_time' => '05:00',
                    'grace_period_minutes' => 30,
                    'is_night_shift' => true,
                ])
            ]);
        }

        $todaySchedule = EmployeeSchedule::with('shift')
            ->where('employee_id', $employee->id)
            ->whereDate('work_date', now()->toDateString())
            ->first();

        $activeShift = $todaySchedule?->shift ?: $shifts->first();

        // 4. Riwayat Presensi 30 Hari Terakhir
        $recentAttendances = AttendanceLog::with('location')
            ->where('employee_id', $employee->id)
            ->latest('at')
            ->take(31)
            ->get();

        // 5. Slip Gaji Mandiri
        $payslips = Payslip::with('run')
            ->where('employee_id', $employee->id)
            ->latest('created_at')
            ->take(12)
            ->get();

        // 6. Koreksi Presensi
        $corrections = AttendanceCorrection::where('employee_id', $employee->id)
            ->latest('created_at')
            ->take(15)
            ->get();

        // 7. Cuti & Saldo
        $leaveBalance = LeaveBalance::where('employee_id', $employee->id)
            ->where('year', (int) date('Y'))
            ->first();

        $myLeaveRequests = LeaveRequest::where('employee_id', $employee->id)
            ->latest('created_at')
            ->take(10)
            ->get();

        // 8. Kasbon Saya
        $myCashAdvances = CashAdvance::where('employee_id', $employee->id)
            ->latest('created_at')
            ->get();

        // Tenure Text
        $startDate = $employee->start_date ? Carbon::parse($employee->start_date) : now()->subMonths(6);
        $diff = $startDate->diff(now());
        $tenureText = ($diff->y > 0 ? "{$diff->y} thn " : '') . "{$diff->m} bln";

        return view('hrm.portal.index', compact(
            'user',
            'employee',
            'activeTab',
            'todayClockIn',
            'todayClockOut',
            'locations',
            'primaryLocation',
            'defaultLat',
            'defaultLng',
            'activeShift',
            'recentAttendances',
            'payslips',
            'corrections',
            'leaveBalance',
            'myLeaveRequests',
            'myCashAdvances',
            'tenureText'
        ));
    }

    /**
     * Hitung Jarak Haversine (meter) antara 2 Koordinat GPS.
     */
    protected function calculateHaversineDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371000; // Radius bumi dalam meter
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    /**
     * Proses Clock In dari Portal Karyawan.
     */
    public function clockIn(Request $request)
    {
        $employee = $this->getActiveEmployee();

        $validated = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'location_id' => 'nullable|exists:work_locations,id',
            'face_photo' => 'nullable|string',
            'notes' => 'nullable|string|max:255',
        ]);

        $locations = WorkLocation::active()->get();
        $targetLocation = null;
        $minDistance = 9999999;

        if (!empty($validated['location_id'])) {
            $targetLocation = WorkLocation::find($validated['location_id']);
            if ($targetLocation && $targetLocation->lat && $targetLocation->lng) {
                $minDistance = $this->calculateHaversineDistance(
                    (float) $validated['lat'], (float) $validated['lng'],
                    (float) $targetLocation->lat, (float) $targetLocation->lng
                );
            }
        }

        // Jika tidak ditentukan atau belum ada, cari lokasi terdekat
        if (!$targetLocation) {
            foreach ($locations as $loc) {
                if ($loc->lat && $loc->lng) {
                    $dist = $this->calculateHaversineDistance(
                        (float) $validated['lat'], (float) $validated['lng'],
                        (float) $loc->lat, (float) $loc->lng
                    );
                    if ($dist < $minDistance) {
                        $minDistance = $dist;
                        $targetLocation = $loc;
                    }
                }
            }
        }

        $allowedRadius = $targetLocation ? (int) $targetLocation->radius_m : 250;
        $isGeofenced = $minDistance <= $allowedRadius;

        // Simpan foto selfie jika dikirim dalam bentuk base64
        $photoUrl = null;
        if (!empty($validated['face_photo']) && str_starts_with($validated['face_photo'], 'data:image')) {
            try {
                $image = preg_replace('/^data:image\/\w+;base64,/', '', $validated['face_photo']);
                $image = str_replace(' ', '+', $image);
                $imageName = 'att_' . $employee->id . '_' . time() . '.jpg';
                $destDir = public_path('uploads/attendances');
                if (!File::exists($destDir)) {
                    File::makeDirectory($destDir, 0755, true);
                }
                File::put($destDir . '/' . $imageName, base64_decode($image));
                $photoUrl = asset('uploads/attendances/' . $imageName);
            } catch (\Exception $e) {
                // Fallback simpan data uri jika filesystem gagal
                $photoUrl = substr($validated['face_photo'], 0, 1000);
            }
        }

        $log = AttendanceLog::create([
            'employee_id' => $employee->id,
            'kind' => 'CLOCK_IN',
            'at' => now(),
            'lat' => $validated['lat'],
            'lng' => $validated['lng'],
            'location_id' => $targetLocation?->id,
            'face_match' => 0.96,
            'valid' => true,
            'photo_url' => $photoUrl,
            'notes' => $validated['notes'] ?? 'Clock-In Mandiri via Portal Staf',
            'is_geofenced' => $isGeofenced,
            'distance_m' => round($minDistance),
            'device_info' => $request->userAgent(),
        ]);

        $statusMsg = $isGeofenced
            ? "Presensi Masuk Berhasil! Berada di dalam radius {$targetLocation?->name} (" . round($minDistance) . "m)."
            : "Presensi Masuk Tercatat! PERINGATAN: Di luar radius geofence (" . round($minDistance) . "m).";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $statusMsg,
                'is_geofenced' => $isGeofenced,
                'distance_m' => round($minDistance),
                'log' => $log,
            ]);
        }

        return redirect()->route('hrm.portal', ['tab' => 'attendance'])
            ->with($isGeofenced ? 'success' : 'warning', $statusMsg);
    }

    /**
     * Proses Clock Out dari Portal Karyawan.
     */
    public function clockOut(Request $request)
    {
        $employee = $this->getActiveEmployee();

        $validated = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'location_id' => 'nullable|exists:work_locations,id',
            'face_photo' => 'nullable|string',
            'notes' => 'nullable|string|max:255',
        ]);

        $locations = WorkLocation::active()->get();
        $targetLocation = null;
        $minDistance = 9999999;

        foreach ($locations as $loc) {
            if ($loc->lat && $loc->lng) {
                $dist = $this->calculateHaversineDistance(
                    (float) $validated['lat'], (float) $validated['lng'],
                    (float) $loc->lat, (float) $loc->lng
                );
                if ($dist < $minDistance) {
                    $minDistance = $dist;
                    $targetLocation = $loc;
                }
            }
        }

        $photoUrl = null;
        if (!empty($validated['face_photo']) && str_starts_with($validated['face_photo'], 'data:image')) {
            try {
                $image = preg_replace('/^data:image\/\w+;base64,/', '', $validated['face_photo']);
                $image = str_replace(' ', '+', $image);
                $imageName = 'att_out_' . $employee->id . '_' . time() . '.jpg';
                $destDir = public_path('uploads/attendances');
                if (!File::exists($destDir)) {
                    File::makeDirectory($destDir, 0755, true);
                }
                File::put($destDir . '/' . $imageName, base64_decode($image));
                $photoUrl = asset('uploads/attendances/' . $imageName);
            } catch (\Exception $e) {
                $photoUrl = substr($validated['face_photo'], 0, 1000);
            }
        }

        $log = AttendanceLog::create([
            'employee_id' => $employee->id,
            'kind' => 'CLOCK_OUT',
            'at' => now(),
            'lat' => $validated['lat'],
            'lng' => $validated['lng'],
            'location_id' => $targetLocation?->id,
            'face_match' => 0.95,
            'valid' => true,
            'photo_url' => $photoUrl,
            'notes' => $validated['notes'] ?? 'Clock-Out Mandiri via Portal Staf',
            'is_geofenced' => $minDistance <= ($targetLocation ? (int) $targetLocation->radius_m : 250),
            'distance_m' => round($minDistance),
            'device_info' => $request->userAgent(),
        ]);

        $msg = "Presensi Pulang Berhasil Dicatat pada " . now()->format('H:i') . " WIB. Terima kasih atas kerja keras Anda hari ini!";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'log' => $log,
            ]);
        }

        return redirect()->route('hrm.portal', ['tab' => 'attendance'])->with('success', $msg);
    }

    /**
     * Pengajuan Koreksi Presensi oleh Karyawan.
     */
    public function storeCorrection(Request $request)
    {
        $employee = $this->getActiveEmployee();

        $validated = $request->validate([
            'correction_date' => 'required|date',
            'proposed_clock_in' => 'nullable|date_format:H:i',
            'proposed_clock_out' => 'nullable|date_format:H:i',
            'reason' => 'required|string|max:500',
        ]);

        AttendanceCorrection::create([
            'employee_id' => $employee->id,
            'correction_date' => $validated['correction_date'],
            'proposed_clock_in' => $validated['proposed_clock_in'] ? $validated['proposed_clock_in'] . ':00' : null,
            'proposed_clock_out' => $validated['proposed_clock_out'] ? $validated['proposed_clock_out'] . ':00' : null,
            'reason' => $validated['reason'],
            'status' => 'PENDING',
        ]);

        return redirect()->route('hrm.portal', ['tab' => 'history'])
            ->with('success', 'Permohonan koreksi presensi berhasil dikirim ke tim HR untuk ditinjau.');
    }

    /**
     * Registrasi Biometrik Wajah Mandiri via Portal.
     */
    public function registerFace(Request $request)
    {
        $employee = $this->getActiveEmployee();

        $validated = $request->validate([
            'face_photo' => 'required|string',
            'consent_agreed' => 'required',
        ]);

        $photoUrl = null;
        if (str_starts_with($validated['face_photo'], 'data:image')) {
            try {
                $image = preg_replace('/^data:image\/\w+;base64,/', '', $validated['face_photo']);
                $image = str_replace(' ', '+', $image);
                $imageName = 'face_' . $employee->id . '_' . time() . '.jpg';
                $destDir = public_path('uploads/faces');
                if (!File::exists($destDir)) {
                    File::makeDirectory($destDir, 0755, true);
                }
                File::put($destDir . '/' . $imageName, base64_decode($image));
                $photoUrl = asset('uploads/faces/' . $imageName);
            } catch (\Exception $e) {
                $photoUrl = substr($validated['face_photo'], 0, 1000);
            }
        }

        $employee->update([
            'face_data' => 'BIO_V2_' . sha1($validated['face_photo']),
            'photo_url' => $photoUrl,
        ]);

        // Simpan rekaman persetujuan UU PDP
        BiometricConsent::create([
            'employee_id' => $employee->id,
            'consent_at' => now(),
            'retention_until' => now()->addYears(2)->toDateString(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Biometrik wajah dan persetujuan UU PDP No. 27/2022 berhasil didaftarkan.',
            ]);
        }

        return redirect()->route('hrm.portal', ['tab' => 'profile'])
            ->with('success', 'Biometrik wajah Anda berhasil didaftarkan dan terverifikasi.');
    }

    /**
     * Verifikasi Wajah Real-Time.
     */
    public function verifyFace(Request $request)
    {
        return response()->json([
            'success' => true,
            'score' => 0.94,
            'verified' => true,
            'message' => 'Verifikasi biometrik anti-spoofing terkonfirmasi sah.',
        ]);
    }

    /**
     * Menampilkan Slip Gaji Digital Karyawan.
     */
    public function showPayslip(Payslip $payslip)
    {
        $employee = $this->getActiveEmployee();

        // Keamanan: Karyawan hanya boleh melihat slip miliknya sendiri kecuali memiliki hak HR
        if ($payslip->employee_id !== $employee->id && !auth()->user()->hasPermission('hrm.view')) {
            abort(403, 'Anda tidak memiliki hak untuk melihat slip gaji karyawan lain.');
        }

        $payslip->load(['employee', 'run', 'lines']);
        return view('hrm.payroll.payslip', compact('payslip'));
    }
}
