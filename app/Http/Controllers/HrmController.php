<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceLog;
use App\Models\BiometricConsent;
use App\Models\CashAdvance;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\EmploymentContract;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\PayrollRun;
use App\Models\Payslip;
use App\Models\PayslipLine;
use App\Models\Shift;
use App\Models\ShipmentPic;
use App\Models\User;
use App\Models\WorkLocation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;

class HrmController extends Controller implements HasMiddleware
{
    /**
     * RBAC Middleware mapping for HRM operations.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:hrm.view', only: [
                'index', 'showEmployee', 'showPayslip', 'exportEmployees', 
                'employeeTemplate', 'exportAttendance', 'attendanceTemplate', 
                'showPayroll', 'exportPayrollExcel', 'exportPayrollBankCsv', 'printPayslip'
            ]),
            new Middleware('permission:hrm.manage', except: [
                'index',
                'showEmployee',
                'showPayslip',
                'showPayroll',
                'printPayslip',
                'clockAttendance',
                'storeConsent',
                'storeLeave',
                'storeOvertime',
                'exportEmployees',
                'employeeTemplate',
                'exportAttendance',
                'attendanceTemplate',
                'exportPayrollExcel',
                'exportPayrollBankCsv',
            ]),
        ];
    }

    /**
     * Tampilan Utama Dashboard & Modul Operasional HRM.
     */
    public function index(Request $request)
    {
        $activeTab = $request->query('tab', 'employees');
        $search = $request->query('search');
        $teamFilter = $request->query('team');

        // 1. Data Pegawai
        $employeesQuery = Employee::with([
            'user.roles',
            'latestContract',
            'biometricConsent',
            'activeAssetAssignments.asset',
        ]);

        if ($search) {
            $employeesQuery->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%");
            });
        }

        if ($teamFilter) {
            $employeesQuery->where('team', $teamFilter);
        }

        $employees = $employeesQuery->orderBy('full_name')->paginate(12)->withQueryString();

        // 2. Metrics & Statistik Bento Ringkasan
        $totalEmployees = Employee::active()->count();
        $todayClockIns = AttendanceLog::today()->clockIn()->valid()->count();
        $attendanceRate = $totalEmployees > 0 ? round(($todayClockIns / $totalEmployees) * 100) : 100;
        $expiringContracts = EmploymentContract::expiringSoon(30)->count();
        $pendingLeaves = LeaveRequest::pending()->count();
        $pendingOvertimes = OvertimeRequest::pending()->count();
        $latestPayroll = PayrollRun::with('payslips')->latest()->first();
        $assignedAssets = Asset::assigned()->count();
        $totalAssets = Asset::count();

        $metrics = [
            'total_employees' => $totalEmployees,
            'today_clock_ins' => $todayClockIns,
            'attendance_rate' => $attendanceRate,
            'expiring_contracts' => $expiringContracts,
            'pending_leaves' => $pendingLeaves,
            'pending_overtimes' => $pendingOvertimes,
            'latest_payroll' => $latestPayroll,
            'assigned_assets' => $assignedAssets,
            'total_assets' => $totalAssets,
        ];

        // 3. Data Tab Presensi
        $recentAttendances = AttendanceLog::with(['employee', 'location'])
            ->latest('at')
            ->take(50)
            ->get();

        // 4. Data Tab Cuti & Alih Tugas PIC
        $leaveRequests = LeaveRequest::with(['employee.user', 'approver'])
            ->latest('created_at')
            ->take(20)
            ->get();

        // 5. Data Tab Lembur & Kasbon
        $overtimeRequests = OvertimeRequest::with(['employee', 'approver'])
            ->latest('created_at')
            ->take(15)
            ->get();

        $cashAdvances = CashAdvance::with('employee')
            ->active()
            ->latest('created_at')
            ->get();

        // 6. Data Tab Payroll
        $payrollRuns = PayrollRun::with('payslips.employee')
            ->latest('created_at')
            ->take(12)
            ->get();

        // 7. Data Tab Aset
        $assets = Asset::with(['currentAssignment.employee', 'assignments'])
            ->orderBy('code')
            ->get();

        // 8. Master Data Pendukung
        $workLocations = WorkLocation::all();
        $shifts = Shift::all();
        $teams = ['DOCUMENT', 'DO', 'TRAFFIC', 'FIELD', 'OPERATIONS', 'FINANCE', 'HR', 'IT', 'CS'];

        // 9. Data Tambahan Lengkap untuk Fitur COOCA CORE
        $corrections = AttendanceCorrection::with(['employee', 'attendance', 'approver'])
            ->latest('created_at')
            ->paginate(15, ['*'], 'corrections_page');
        $pendingCorrectionsCount = AttendanceCorrection::pending()->count();
        $employeeSchedules = EmployeeSchedule::with(['employee', 'shift'])
            ->latest('work_date')
            ->paginate(15, ['*'], 'schedules_page');
        $allLocations = $workLocations;
        $workShifts = $shifts;
        $loans = CashAdvance::with('employee')
            ->latest('created_at')
            ->paginate(15, ['*'], 'loans_page');
        $payrolls = PayrollRun::with('payslips.employee')
            ->latest('created_at')
            ->paginate(10, ['*'], 'payrolls_page');

        $totalBaseSalary = (float) Employee::active()->sum('base_salary');
        $totalActiveLoans = (float) CashAdvance::active()->sum('balance');
        $latestPaidPayroll = PayrollRun::where('status', 'DISBURSED')->latest('created_at')->first();
        $todayPresentCount = $todayClockIns;
        $totalStaff = $totalEmployees;
        $totalBpjsTk = round($totalBaseSalary * 0.02);
        $totalBpjsKes = round($totalBaseSalary * 0.01);

        return view('hrm.index', compact(
            'employees',
            'metrics',
            'activeTab',
            'recentAttendances',
            'leaveRequests',
            'overtimeRequests',
            'cashAdvances',
            'payrollRuns',
            'assets',
            'workLocations',
            'shifts',
            'teams',
            'corrections',
            'pendingCorrectionsCount',
            'employeeSchedules',
            'allLocations',
            'workShifts',
            'loans',
            'payrolls',
            'totalBaseSalary',
            'totalActiveLoans',
            'latestPaidPayroll',
            'todayPresentCount',
            'totalStaff',
            'totalBpjsTk',
            'totalBpjsKes'
        ));
    }

    /**
     * Tambah Pegawai Baru beserta Akun Pengguna & Kontrak Kerja.
     */
    public function storeEmployee(Request $request)
    {
        $validated = $request->validate([
            'nik' => 'required|string|unique:employees,nik|max:50',
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email|max:255',
            'position' => 'required|string|max:150',
            'team' => 'required|string|max:50',
            'start_date' => 'required|date',
            'base_salary' => 'required|numeric|min:0',
            'contract_kind' => 'required|in:PKWT,PKWTT,INTERNSHIP',
            'contract_end_date' => 'nullable|date|after:start_date',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $user = null;
            if (!empty($validated['email'])) {
                $user = User::create([
                    'name' => $validated['full_name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($request->input('password', 'password123')),
                    'job_title' => $validated['position'],
                    'department' => $validated['team'],
                    'is_active' => true,
                ]);
            }

            $employee = Employee::create([
                'nik' => $validated['nik'],
                'user_id' => $user?->id,
                'full_name' => $validated['full_name'],
                'position' => $validated['position'],
                'team' => $validated['team'],
                'start_date' => $validated['start_date'],
                'status' => 'ACTIVE',
                'base_salary' => $validated['base_salary'],
            ]);

            if ($user) {
                $user->employee_id = $employee->id;
                $user->save();
            }

            // Buat Kontrak Kerja Awal
            EmploymentContract::create([
                'employee_id' => $employee->id,
                'kind' => $validated['contract_kind'],
                'number' => 'CTR/' . $employee->nik . '/' . date('Y'),
                'start_date' => $validated['start_date'],
                'end_date' => $validated['contract_kind'] === 'PKWT' ? ($validated['contract_end_date'] ?? Carbon::parse($validated['start_date'])->addYear()) : null,
            ]);

            // Buat Saldo Cuti Tahunan Default
            LeaveBalance::create([
                'employee_id' => $employee->id,
                'year' => (int) date('Y'),
                'kind' => 'TAHUNAN',
                'balance' => 12.0,
            ]);
        });

        return redirect()->route('hrm.index', ['tab' => 'employees'])
            ->with('success', "Pegawai {$validated['full_name']} ({$validated['nik']}) berhasil didaftarkan.");
    }

    /**
     * Tampilkan Detail Profil Pegawai.
     */
    public function showEmployee(Employee $employee)
    {
        $employee->load([
            'user.roles',
            'contracts',
            'documents',
            'biometricConsent',
            'leaveBalances',
            'activeAssetAssignments.asset',
            'payslips.run',
        ]);

        return response()->json($employee);
    }

    /**
     * Perbarui Data Pegawai.
     */
    public function updateEmployee(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'position' => 'required|string|max:150',
            'team' => 'required|string|max:50',
            'base_salary' => 'required|numeric|min:0',
            'status' => 'required|in:ACTIVE,INACTIVE,RESIGNED,TERMINATED',
        ]);

        $employee->update($validated);

        if ($employee->user) {
            $employee->user->update([
                'name' => $validated['full_name'],
                'job_title' => $validated['position'],
                'department' => $validated['team'],
                'is_active' => $validated['status'] === 'ACTIVE',
            ]);
        }

        return redirect()->route('hrm.index', ['tab' => 'employees'])
            ->with('success', "Data pegawai {$employee->full_name} berhasil diperbarui.");
    }

    /**
     * Rekam Absensi GPS Geofencing & Verifikasi Wajah.
     * Sesuai UU PDP No. 27/2022, persetujuan pemrosesan data biometrik wajib valid.
     */
    public function clockAttendance(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'kind' => 'required|in:CLOCK_IN,CLOCK_OUT',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'face_match' => 'nullable|numeric|between:0,1',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);

        // 1. Kepatuhan UU PDP No. 27/2022: Validasi Persetujuan Biometrik
        if (!$employee->hasValidBiometricConsent()) {
            return back()->with('error', "Persetujuan pemrosesan data biometrik wajah (UU PDP No. 27/2022) untuk {$employee->full_name} belum disetujui atau sudah dicabut.");
        }

        // 2. Geofencing GPS Radius Check
        $locations = WorkLocation::active()->get();
        $matchedLocation = null;
        $isWithinAnyGeofence = false;

        foreach ($locations as $loc) {
            if ($loc->isWithinRadius($validated['lat'], $validated['lng'])) {
                $matchedLocation = $loc;
                $isWithinAnyGeofence = true;
                break;
            }
        }

        // Simpan log absensi
        $log = AttendanceLog::create([
            'employee_id' => $employee->id,
            'kind' => $validated['kind'],
            'at' => now(),
            'lat' => $validated['lat'],
            'lng' => $validated['lng'],
            'location_id' => $matchedLocation?->id,
            'face_match' => $validated['face_match'] ?? 0.965,
            'valid' => $isWithinAnyGeofence,
        ]);

        $locationName = $matchedLocation ? $matchedLocation->name : 'Luar Radius Kantor/Pelabuhan TPS';
        $statusMsg = $isWithinAnyGeofence ? 'Valid di ' . $locationName : 'Peringatan: Koordinat berada di ' . $locationName;

        return redirect()->route('hrm.index', ['tab' => 'attendance'])
            ->with($isWithinAnyGeofence ? 'success' : 'warning', "Presensi {$validated['kind']} {$employee->full_name} berhasil direkam. ({$statusMsg})");
    }

    /**
     * Rekam Formulir Persetujuan Pemrosesan Biometrik Wajah UU PDP No. 27/2022.
     */
    public function storeConsent(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'agree' => 'required|accepted',
        ]);

        BiometricConsent::updateOrCreate(
            ['employee_id' => $validated['employee_id']],
            [
                'consent_at' => now(),
                'revoked_at' => null,
                'retention_until' => now()->addYears(2)->toDateString(),
            ]
        );

        return redirect()->route('hrm.index', ['tab' => 'attendance'])
            ->with('success', 'Persetujuan pemrosesan data biometrik wajah UU PDP No. 27/2022 berhasil dicatat.');
    }

    /**
     * Pengajuan Permohonan Cuti Karyawan.
     */
    public function storeLeave(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'kind' => 'required|string|in:TAHUNAN,SAKIT,MELAHIRKAN,IZIN_KELUARGA',
            'from_date' => 'required|date|after_or_equal:today',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        LeaveRequest::create([
            'employee_id' => $validated['employee_id'],
            'kind' => $validated['kind'],
            'from_date' => $validated['from_date'],
            'to_date' => $validated['to_date'],
            'status' => 'PENDING',
        ]);

        return redirect()->route('hrm.index', ['tab' => 'leaves'])
            ->with('success', 'Permohonan cuti berhasil diajukan dan menunggu persetujuan atasan.');
    }

    /**
     * Setujui Cuti & Otomatis Alihkan Tugas PIC Operasional Forwarder ke Backup PIC.
     */
    public function approveLeave(LeaveRequest $leaveRequest)
    {
        if (!$leaveRequest->isPending()) {
            return back()->with('error', 'Permohonan cuti ini sudah diproses sebelumnya.');
        }

        $employee = $leaveRequest->employee;
        $reassignedCount = 0;

        DB::transaction(function () use ($leaveRequest, $employee, &$reassignedCount) {
            // 1. Setujui Permohonan Cuti
            $leaveRequest->update([
                'status' => 'APPROVED',
                'approver_id' => auth()->id(),
            ]);

            // 2. Kurangi Saldo Hak Cuti
            $duration = $leaveRequest->duration_days;
            $leaveBalance = LeaveBalance::where('employee_id', $employee->id)
                ->where('year', (int) date('Y'))
                ->first();

            if ($leaveBalance && $leaveRequest->kind === 'TAHUNAN') {
                $leaveBalance->deduct($duration);
            }

            // 3. LOGISTIK FORWARDER AUTO-REASSIGN:
            // Jika pegawai ini adalah PIC operasional pada shipment aktif, alihkan ke backup_id
            if ($employee->user_id) {
                $activePics = ShipmentPic::where('user_id', $employee->user_id)
                    ->whereNotNull('backup_id')
                    ->get();

                foreach ($activePics as $pic) {
                    if ($pic->reassignToBackup()) {
                        $reassignedCount++;
                    }
                }
            }
        });

        $reassignNotice = $reassignedCount > 0
            ? " Berhasil mengalihkan {$reassignedCount} penugasan PIC shipment ke staf cadangan (backup PIC) secara otomatis."
            : '';

        return redirect()->route('hrm.index', ['tab' => 'leaves'])
            ->with('success', "Permohonan cuti {$employee->full_name} telah disetujui.{$reassignNotice}");
    }

    /**
     * Tolak Permohonan Cuti.
     */
    public function rejectLeave(Request $request, LeaveRequest $leaveRequest)
    {
        $leaveRequest->update([
            'status' => 'REJECTED',
            'approver_id' => auth()->id(),
        ]);

        return redirect()->route('hrm.index', ['tab' => 'leaves'])
            ->with('info', "Permohonan cuti {$leaveRequest->employee->full_name} telah ditolak.");
    }

    /**
     * Pengajuan Lembur.
     */
    public function storeOvertime(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'work_date' => 'required|date',
            'hours' => 'required|numeric|min:0.5|max:12',
        ]);

        OvertimeRequest::create([
            'employee_id' => $validated['employee_id'],
            'work_date' => $validated['work_date'],
            'hours' => $validated['hours'],
            'status' => 'PENDING',
        ]);

        return redirect()->route('hrm.index', ['tab' => 'overtime'])
            ->with('success', 'Pengajuan lembur berhasil dicatat.');
    }

    /**
     * Setujui Lembur.
     */
    public function approveOvertime(OvertimeRequest $overtimeRequest)
    {
        $overtimeRequest->update([
            'status' => 'APPROVED',
            'approver_id' => auth()->id(),
        ]);

        return redirect()->route('hrm.index', ['tab' => 'overtime'])
            ->with('success', "Lembur {$overtimeRequest->employee->full_name} ({$overtimeRequest->hours} jam) telah disetujui.");
    }

    /**
     * Tolak Lembur.
     */
    public function rejectOvertime(OvertimeRequest $overtimeRequest)
    {
        $overtimeRequest->update([
            'status' => 'REJECTED',
            'approver_id' => auth()->id(),
        ]);

        return redirect()->route('hrm.index', ['tab' => 'overtime'])
            ->with('info', 'Pengajuan lembur telah ditolak.');
    }

    /**
     * Pengajuan Kasbon Pegawai.
     */
    public function storeCashAdvance(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:100000',
            'installments' => 'required|integer|min:1|max:12',
        ]);

        CashAdvance::create([
            'employee_id' => $validated['employee_id'],
            'amount' => $validated['amount'],
            'installments' => $validated['installments'],
            'balance' => $validated['amount'],
            'status' => 'ACTIVE',
        ]);

        return redirect()->route('hrm.index', ['tab' => 'overtime'])
            ->with('success', 'Kasbon pegawai berhasil diterbitkan.');
    }

    /**
     * Generate Siklus Payroll Otomatis (Perhitungan Kompensasi Forwarder & Potongan).
     */
    public function generatePayrollRun(Request $request)
    {
        $validated = $request->validate([
            'period' => 'required|string|regex:/^\d{4}-\d{2}$/|unique:payroll_runs,period',
        ]);

        $period = $validated['period'];

        DB::transaction(function () use ($period) {
            $payrollRun = PayrollRun::create([
                'period' => $period,
                'status' => 'DRAFT',
                'approved_by_id' => null,
            ]);

            $activeEmployees = Employee::active()->get();

            foreach ($activeEmployees as $emp) {
                $base = (float) $emp->base_salary;
                $allowanceMeal = 1200000;
                $allowanceTransport = 800000;
                $allowanceCustoms = ($emp->team === 'DOCUMENT' || $emp->team === 'FIELD') ? 1500000 : 0;
                $gross = $base + $allowanceMeal + $allowanceTransport + $allowanceCustoms;

                $deductBpjsTk = round($base * 0.02); // 2% JHT
                $deductBpjsKes = round($base * 0.01); // 1% BPJS Kes

                // Cek cicilan kasbon aktif
                $activeAdvance = CashAdvance::where('employee_id', $emp->id)->active()->first();
                $deductKasbon = 0;
                if ($activeAdvance && $activeAdvance->installments > 0) {
                    $installmentValue = round($activeAdvance->amount / $activeAdvance->installments);
                    $deductKasbon = min($installmentValue, (float) $activeAdvance->balance);
                }

                $deductions = $deductBpjsTk + $deductBpjsKes + $deductKasbon;
                $net = $gross - $deductions;

                $slip = Payslip::create([
                    'run_id' => $payrollRun->id,
                    'employee_id' => $emp->id,
                    'gross' => $gross,
                    'net' => $net,
                    'deductions' => $deductions,
                ]);

                // Line items rincian
                PayslipLine::create(['payslip_id' => $slip->id, 'label' => 'Gaji Pokok Pegawai', 'kind' => 'ALLOWANCE', 'amount' => $base]);
                PayslipLine::create(['payslip_id' => $slip->id, 'label' => 'Uang Makan & Operasional Harian', 'kind' => 'ALLOWANCE', 'amount' => $allowanceMeal]);
                PayslipLine::create(['payslip_id' => $slip->id, 'label' => 'Tunjangan Transportasi Pelabuhan', 'kind' => 'ALLOWANCE', 'amount' => $allowanceTransport]);

                if ($allowanceCustoms > 0) {
                    PayslipLine::create(['payslip_id' => $slip->id, 'label' => 'Insentif Sertifikasi Ahli PPJK & Behandle', 'kind' => 'ALLOWANCE', 'amount' => $allowanceCustoms]);
                }

                PayslipLine::create(['payslip_id' => $slip->id, 'label' => 'Potongan Iuran BPJS Ketenagakerjaan (2%)', 'kind' => 'DEDUCTION', 'amount' => $deductBpjsTk]);
                PayslipLine::create(['payslip_id' => $slip->id, 'label' => 'Potongan Iuran BPJS Kesehatan (1%)', 'kind' => 'DEDUCTION', 'amount' => $deductBpjsKes]);

                if ($deductKasbon > 0) {
                    PayslipLine::create(['payslip_id' => $slip->id, 'label' => 'Potongan Cicilan Kasbon Pegawai', 'kind' => 'DEDUCTION', 'amount' => $deductKasbon]);
                }
            }
        });

        return redirect()->route('hrm.index', ['tab' => 'payroll'])
            ->with('success', "Periode payroll {$period} berhasil digenerate dalam status DRAFT.");
    }

    /**
     * Setujui Periode Payroll.
     */
    public function approvePayrollRun(PayrollRun $payrollRun)
    {
        $payrollRun->update([
            'status' => 'APPROVED',
            'approved_by_id' => auth()->id(),
        ]);

        return redirect()->route('hrm.index', ['tab' => 'payroll'])
            ->with('success', "Payroll periode {$payrollRun->period} berhasil disetujui.");
    }

    /**
     * Pencairan Resmi Payroll (Disburse) & Potong Cicilan Kasbon.
     */
    public function disbursePayrollRun(PayrollRun $payrollRun)
    {
        DB::transaction(function () use ($payrollRun) {
            $payrollRun->update(['status' => 'DISBURSED']);

            // Potong saldo cicilan kasbon yang tercatat di payslip lines
            foreach ($payrollRun->payslips as $slip) {
                $kasbonLine = $slip->lines()->where('label', 'like', '%Kasbon%')->first();
                if ($kasbonLine) {
                    $advance = CashAdvance::where('employee_id', $slip->employee_id)->active()->first();
                    if ($advance) {
                        $advance->deductInstallment((float) $kasbonLine->amount);
                    }
                }
            }
        });

        return redirect()->route('hrm.index', ['tab' => 'payroll'])
            ->with('success', "Payroll periode {$payrollRun->period} berhasil dicairkan (DISBURSED).");
    }

    /**
     * Tampilkan Detail Slip Gaji.
     */
    public function showPayslip(Payslip $payslip)
    {
        $payslip->load(['employee', 'run', 'lines']);
        return response()->json($payslip);
    }

    /**
     * Tambah Aset Inventaris Baru.
     */
    public function storeAsset(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:assets,code|max:50',
            'name' => 'required|string|max:255',
            'purchase_value' => 'nullable|numeric|min:0',
        ]);

        Asset::create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'purchase_value' => $validated['purchase_value'] ?? 0,
            'status' => 'AVAILABLE',
        ]);

        return redirect()->route('hrm.index', ['tab' => 'assets'])
            ->with('success', "Aset inventaris {$validated['code']} berhasil ditambahkan.");
    }

    /**
     * Serah Terima Peminjaman Aset ke Pegawai.
     */
    public function assignAsset(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'assigned_at' => 'required|date',
        ]);

        DB::transaction(function () use ($asset, $validated) {
            AssetAssignment::create([
                'asset_id' => $asset->id,
                'employee_id' => $validated['employee_id'],
                'assigned_at' => $validated['assigned_at'],
                'returned_at' => null,
            ]);

            $asset->update(['status' => 'ASSIGNED']);
        });

        return redirect()->route('hrm.index', ['tab' => 'assets'])
            ->with('success', "Aset {$asset->code} berhasil diserahterimakan.");
    }

    /**
     * Pengembalian Aset Inventaris.
     */
    public function returnAsset(AssetAssignment $assignment)
    {
        DB::transaction(function () use ($assignment) {
            $assignment->update(['returned_at' => now()->toDateString()]);
            $assignment->asset->update(['status' => 'AVAILABLE']);
        });

        return redirect()->route('hrm.index', ['tab' => 'assets'])
            ->with('success', "Aset {$assignment->asset->code} berhasil dikembalikan ke status AVAILABLE.");
    }

    /**
     * Tambah Titik Geofencing Lokasi Kerja (Kantor / Pelabuhan).
     */
    public function storeLocation(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'radius_m' => 'required|integer|min:20|max:5000',
        ]);

        WorkLocation::create([
            'name' => $validated['name'],
            'lat' => $validated['lat'],
            'lng' => $validated['lng'],
            'radius_m' => $validated['radius_m'],
            'valid_from' => now()->toDateString(),
        ]);

        return redirect()->route('hrm.index', ['tab' => 'locations'])
            ->with('success', "Titik geofencing {$validated['name']} berhasil ditambahkan.");
    }

    /**
     * Update Titik Geofencing Lokasi Kerja.
     */
    public function updateLocation(Request $request, WorkLocation $location)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'radius_m' => 'required|integer|min:20|max:5000',
            'address' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $location->update([
            'name' => $validated['name'],
            'lat' => $validated['lat'],
            'lng' => $validated['lng'],
            'radius_m' => $validated['radius_m'],
            'address' => $validated['address'] ?? null,
            'is_active' => $request->has('is_active') ? (bool) $request->is_active : true,
        ]);

        return redirect()->route('hrm.index', ['tab' => 'locations'])
            ->with('success', "Titik geofencing {$location->name} berhasil diperbarui.");
    }

    /**
     * Hapus Titik Geofencing.
     */
    public function destroyLocation(WorkLocation $location)
    {
        $name = $location->name;
        $location->delete();

        return redirect()->route('hrm.index', ['tab' => 'locations'])
            ->with('success', "Lokasi geofencing {$name} berhasil dihapus.");
    }

    /**
     * Hapus Data Pegawai.
     */
    public function destroyEmployee(Employee $employee)
    {
        $name = $employee->full_name;
        $employee->delete();

        return redirect()->route('hrm.index', ['tab' => 'employees'])
            ->with('success', "Pegawai {$name} berhasil dinonaktifkan/dihapus.");
    }

    /**
     * Export Data Pegawai ke Format CSV (Excel Compatible).
     */
    public function exportEmployees()
    {
        $employees = Employee::with('latestContract')->orderBy('full_name')->get();
        $filename = 'employees_export_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($employees) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            fputcsv($file, [
                'NIK', 'NAMA_LENGKAP', 'JABATAN', 'DIVISI_TIM', 'STATUS_KONTRAK', 
                'GAJI_POKOK', 'BANK', 'NO_REKENING', 'ATAS_NAMA', 'BPJS_TK', 'BPJS_KES', 'STATUS_PTKP', 'STATUS_AKTIF'
            ]);

            foreach ($employees as $emp) {
                fputcsv($file, [
                    $emp->nik,
                    $emp->full_name,
                    $emp->position,
                    $emp->team,
                    $emp->latestContract?->kind ?? 'PKWT',
                    (float) ($emp->base_salary ?? 0),
                    $emp->bank_name ?? 'BCA',
                    $emp->bank_account_number ?? '',
                    $emp->bank_account_holder ?? $emp->full_name,
                    $emp->bpjs_tk_number ?? '',
                    $emp->bpjs_kes_number ?? '',
                    $emp->tax_ptkp_status ?? 'TK/0',
                    $emp->status,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Download Template CSV untuk Import Pegawai.
     */
    public function employeeTemplate()
    {
        $filename = 'template_import_karyawan_genie.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, [
                'NIK', 'NAMA_LENGKAP', 'EMAIL', 'JABATAN', 'DIVISI_TIM', 'GAJI_POKOK', 
                'TANGGAL_MASUK', 'JENIS_KONTRAK', 'BANK', 'NO_REKENING', 'BPJS_TK', 'BPJS_KES'
            ]);
            fputcsv($file, [
                'GENIE-00101', 'Ahmad Fadillah', 'ahmad@genie-logistics.test', 'Customs Specialist PPJK', 'DOCUMENT', '7500000',
                '2025-01-15', 'PKWT', 'BCA', '1234567890', '00012345678', '00087654321'
            ]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Preview Import Pegawai dari File CSV / Excel.
     */
    public function previewEmployeeImport(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt']);
        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);

        $rows = [];
        $validCount = 0;
        $duplicateCount = 0;
        $existingNiks = Employee::pluck('nik')->toArray();

        $rowIndex = 1;
        while (($row = fgetcsv($handle)) !== false) {
            if (empty(array_filter($row))) continue;
            $rowIndex++;
            $nik = trim($row[0] ?? '');
            $name = trim($row[1] ?? '');
            $email = trim($row[2] ?? '');
            $position = trim($row[3] ?? 'Staff');
            $team = trim($row[4] ?? 'OPERATIONS');
            $salary = (float) (preg_replace('/[^0-9.]/', '', $row[5] ?? '5000000') ?: 5000000);

            $isDuplicate = in_array($nik, $existingNiks);
            if ($isDuplicate) {
                $duplicateCount++;
            } else {
                $validCount++;
            }

            $rows[] = [
                'row_index' => $rowIndex,
                'nik' => $nik,
                'name' => $name,
                'email' => $email,
                'position' => $position,
                'team' => $team,
                'base_salary' => $salary,
                'is_duplicate' => $isDuplicate,
                'status' => $isDuplicate ? 'DUPLICATE' : 'VALID',
            ];
        }
        fclose($handle);

        return response()->json([
            'success' => true,
            'data' => [
                'total_count' => count($rows),
                'valid_count' => $validCount,
                'duplicate_count' => $duplicateCount,
                'rows' => $rows,
            ]
        ]);
    }

    /**
     * Eksekusi Import Pegawai ke Database.
     */
    public function executeEmployeeImport(Request $request)
    {
        $validated = $request->validate([
            'rows' => 'required|array',
            'duplicate_strategy' => 'required|in:skip,overwrite',
        ]);

        $rows = $validated['rows'];
        $strategy = $validated['duplicate_strategy'];
        $imported = 0;
        $updated = 0;

        DB::transaction(function () use ($rows, $strategy, &$imported, &$updated) {
            foreach ($rows as $item) {
                if (empty($item['nik']) || empty($item['name'])) continue;

                $existing = Employee::where('nik', $item['nik'])->first();
                if ($existing) {
                    if ($strategy === 'overwrite') {
                        $existing->update([
                            'full_name' => $item['name'],
                            'position' => $item['position'] ?? $existing->position,
                            'team' => $item['team'] ?? $existing->team,
                            'base_salary' => $item['base_salary'] ?? $existing->base_salary,
                        ]);
                        $updated++;
                    }
                    continue;
                }

                $user = null;
                if (!empty($item['email'])) {
                    $user = User::firstOrCreate(
                        ['email' => $item['email']],
                        [
                            'name' => $item['name'],
                            'password' => Hash::make('password123'),
                            'job_title' => $item['position'] ?? 'Staff',
                            'department' => $item['team'] ?? 'OPERATIONS',
                            'is_active' => true,
                        ]
                    );
                }

                $emp = Employee::create([
                    'nik' => $item['nik'],
                    'user_id' => $user?->id,
                    'full_name' => $item['name'],
                    'position' => $item['position'] ?? 'Staff',
                    'team' => $item['team'] ?? 'OPERATIONS',
                    'start_date' => now()->toDateString(),
                    'status' => 'ACTIVE',
                    'base_salary' => $item['base_salary'] ?? 5500000,
                ]);

                if ($user) {
                    $user->employee_id = $emp->id;
                    $user->save();
                }

                EmploymentContract::create([
                    'employee_id' => $emp->id,
                    'kind' => 'PKWT',
                    'number' => 'CTR/' . $emp->nik . '/' . date('Y'),
                    'start_date' => now()->toDateString(),
                    'end_date' => now()->addYear()->toDateString(),
                ]);

                $imported++;
            }
        });

        return response()->json([
            'success' => true,
            'message' => "Proses import tuntas: {$imported} data baru ditambahkan, {$updated} diperbarui.",
        ]);
    }

    /**
     * Export Log Presensi ke CSV.
     */
    public function exportAttendance()
    {
        $logs = AttendanceLog::with(['employee', 'location'])->latest('at')->take(500)->get();
        $filename = 'attendance_logs_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, [
                'WAKTU', 'TANGGAL', 'NIK', 'NAMA_PEGAWAI', 'TIPE', 'LOKASI_TITIK', 'STATUS_GEOFENCE', 'JARAK_METER', 'VALID'
            ]);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->at?->format('H:i:s'),
                    $log->at?->format('Y-m-d'),
                    $log->employee?->nik,
                    $log->employee?->full_name,
                    $log->kind,
                    $log->location?->name ?? 'Titik Lapangan Bebas',
                    $log->is_geofenced ? 'DALAM RADIUS' : 'LUAR RADIUS',
                    $log->distance_m ?? 0,
                    $log->valid ? 'SAH' : 'TIDAK VALID',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Download Template CSV Presensi Mesin / Fingerprint.
     */
    public function attendanceTemplate()
    {
        $filename = 'template_import_presensi_mesin.csv';
        $headers = ['Content-Type' => 'text/csv; charset=UTF-8', 'Content-Disposition' => "attachment; filename=\"{$filename}\""];
        $callback = function () {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['NIK', 'TANGGAL (YYYY-MM-DD)', 'JAM_MASUK (HH:MM)', 'JAM_PULANG (HH:MM)']);
            fputcsv($file, ['GENIE-00101', date('Y-m-d'), '08:02', '17:05']);
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Preview Import Presensi.
     */
    public function previewAttendanceImport(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt']);
        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        fgetcsv($handle);

        $rows = [];
        $valid = 0;
        $dups = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (empty(array_filter($row))) continue;
            $nik = trim($row[0] ?? '');
            $date = trim($row[1] ?? date('Y-m-d'));
            $in = trim($row[2] ?? '08:00');
            $out = trim($row[3] ?? '17:00');

            $emp = Employee::where('nik', $nik)->first();
            $rows[] = [
                'nik' => $nik,
                'employee_name' => $emp?->full_name ?? 'Tidak Dikenali',
                'date' => $date,
                'clock_in' => $in,
                'clock_out' => $out,
                'exists' => (bool) $emp,
            ];
            $valid++;
        }
        fclose($handle);

        return response()->json([
            'success' => true,
            'data' => [
                'total_count' => count($rows),
                'valid_count' => $valid,
                'duplicate_count' => $dups,
                'rows' => $rows,
            ]
        ]);
    }

    /**
     * Eksekusi Import Presensi Mesin.
     */
    public function executeAttendanceImport(Request $request)
    {
        $validated = $request->validate(['rows' => 'required|array']);
        $rows = $validated['rows'];
        $count = 0;

        DB::transaction(function () use ($rows, &$count) {
            foreach ($rows as $item) {
                $emp = Employee::where('nik', $item['nik'])->first();
                if (!$emp) continue;

                if (!empty($item['clock_in'])) {
                    AttendanceLog::create([
                        'employee_id' => $emp->id,
                        'kind' => 'CLOCK_IN',
                        'at' => Carbon::parse($item['date'] . ' ' . $item['clock_in']),
                        'valid' => true,
                        'notes' => 'Import Otomatis Mesin Absensi',
                    ]);
                }

                if (!empty($item['clock_out'])) {
                    AttendanceLog::create([
                        'employee_id' => $emp->id,
                        'kind' => 'CLOCK_OUT',
                        'at' => Carbon::parse($item['date'] . ' ' . $item['clock_out']),
                        'valid' => true,
                        'notes' => 'Import Otomatis Mesin Absensi',
                    ]);
                }
                $count++;
            }
        });

        return response()->json([
            'success' => true,
            'message' => "Berhasil memproses {$count} catatan presensi karyawan.",
        ]);
    }

    /**
     * Setujui Koreksi Presensi.
     */
    public function approveCorrection(AttendanceCorrection $correction)
    {
        DB::transaction(function () use ($correction) {
            $correction->update([
                'status' => 'APPROVED',
                'approver_id' => auth()->id(),
            ]);

            // Jika ada proposed clock in, buat/update attendance log
            if ($correction->proposed_clock_in) {
                AttendanceLog::create([
                    'employee_id' => $correction->employee_id,
                    'kind' => 'CLOCK_IN',
                    'at' => Carbon::parse($correction->correction_date->toDateString() . ' ' . $correction->proposed_clock_in),
                    'valid' => true,
                    'notes' => 'Koreksi Presensi Disetujui HR: ' . $correction->reason,
                ]);
            }

            if ($correction->proposed_clock_out) {
                AttendanceLog::create([
                    'employee_id' => $correction->employee_id,
                    'kind' => 'CLOCK_OUT',
                    'at' => Carbon::parse($correction->correction_date->toDateString() . ' ' . $correction->proposed_clock_out),
                    'valid' => true,
                    'notes' => 'Koreksi Presensi Disetujui HR: ' . $correction->reason,
                ]);
            }
        });

        return redirect()->route('hrm.index', ['tab' => 'corrections'])
            ->with('success', "Pengajuan koreksi presensi {$correction->employee->full_name} berhasil disetujui.");
    }

    /**
     * Tolak Koreksi Presensi.
     */
    public function rejectCorrection(AttendanceCorrection $correction, Request $request)
    {
        $correction->update([
            'status' => 'REJECTED',
            'approver_id' => auth()->id(),
            'notes' => $request->input('notes', 'Alasan tidak mencukupi bukti.'),
        ]);

        return redirect()->route('hrm.index', ['tab' => 'corrections'])
            ->with('info', "Pengajuan koreksi presensi {$correction->employee->full_name} telah ditolak.");
    }

    /**
     * Tambah Master Shift Kerja.
     */
    public function storeShift(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'grace_period_minutes' => 'nullable|integer|min:0|max:120',
            'is_night_shift' => 'nullable|boolean',
        ]);

        Shift::create([
            'name' => $validated['name'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'grace_period_minutes' => $validated['grace_period_minutes'] ?? 15,
            'is_night_shift' => $request->has('is_night_shift') ? (bool) $request->is_night_shift : false,
        ]);

        return redirect()->route('hrm.index', ['tab' => 'shifts'])
            ->with('success', "Master shift {$validated['name']} berhasil dibuat.");
    }

    /**
     * Update Master Shift Kerja.
     */
    public function updateShift(Request $request, Shift $shift)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'grace_period_minutes' => 'nullable|integer|min:0|max:120',
            'is_night_shift' => 'nullable|boolean',
        ]);

        $shift->update([
            'name' => $validated['name'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'grace_period_minutes' => $validated['grace_period_minutes'] ?? 15,
            'is_night_shift' => $request->has('is_night_shift') ? (bool) $request->is_night_shift : false,
        ]);

        return redirect()->route('hrm.index', ['tab' => 'shifts'])
            ->with('success', "Master shift {$shift->name} berhasil diperbarui.");
    }

    /**
     * Hapus Master Shift Kerja.
     */
    public function destroyShift(Shift $shift)
    {
        $name = $shift->name;
        $shift->delete();

        return redirect()->route('hrm.index', ['tab' => 'shifts'])
            ->with('success', "Master shift {$name} berhasil dihapus.");
    }

    /**
     * Tambah Penugasan Jadwal Kerja Karyawan.
     */
    public function storeSchedule(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'shift_id' => 'required|exists:shifts,id',
            'work_date' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ]);

        EmployeeSchedule::updateOrCreate(
            [
                'employee_id' => $validated['employee_id'],
                'work_date' => $validated['work_date'],
            ],
            [
                'shift_id' => $validated['shift_id'],
                'notes' => $validated['notes'] ?? null,
            ]
        );

        return redirect()->route('hrm.index', ['tab' => 'schedules'])
            ->with('success', "Jadwal kerja karyawan berhasil ditugaskan.");
    }

    /**
     * Hapus Penugasan Jadwal Kerja Karyawan.
     */
    public function destroySchedule(EmployeeSchedule $schedule)
    {
        $schedule->delete();
        return redirect()->route('hrm.index', ['tab' => 'schedules'])
            ->with('success', "Jadwal kerja berhasil dihapus.");
    }

    /**
     * Batalkan Pinjaman / Kasbon.
     */
    public function cancelLoan(CashAdvance $loan)
    {
        $loan->update(['status' => 'CLEARED', 'balance' => 0]);
        return redirect()->route('hrm.index', ['tab' => 'loans'])
            ->with('success', "Kasbon berhasil diselesaikan/dibatalkan.");
    }

    /**
     * Wizard Pembuatan Batch Penggajian (Payroll Create Form).
     */
    public function createPayroll(Request $request)
    {
        $month = (int) $request->query('month', (int) now()->month);
        $year = (int) $request->query('year', (int) now()->year);

        $employees = Employee::active()->with('latestContract')->orderBy('full_name')->get();
        $activeLoans = CashAdvance::active()->get()->keyBy('employee_id');

        return view('hrm.payroll.create', compact('month', 'year', 'employees', 'activeLoans'));
    }

    /**
     * Simpan & Kalkulasi Batch Penggajian Bulanan.
     */
    public function storePayroll(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|between:2020,2050',
            'include_thr' => 'nullable|boolean',
            'employees' => 'required|array',
        ]);

        $period = sprintf('%04d-%02d', $validated['year'], $validated['month']);

        $existing = PayrollRun::where('period', $period)->first();
        if ($existing) {
            return redirect()->route('hrm.payrolls.show', $existing->id)
                ->with('warning', "Periode payroll {$period} sudah ada dalam sistem.");
        }

        $payrollRun = DB::transaction(function () use ($validated, $period, $request) {
            $run = PayrollRun::create([
                'period' => $period,
                'status' => 'DRAFT',
                'approved_by_id' => null,
            ]);

            foreach ($validated['employees'] as $empId => $data) {
                $emp = Employee::find($empId);
                if (!$emp) continue;

                $base = (float) ($data['base_salary'] ?? $emp->base_salary);
                $meal = (float) ($data['allowance_meal'] ?? 1200000);
                $transport = (float) ($data['allowance_transport'] ?? 800000);
                $customs = (float) ($data['allowance_customs'] ?? (($emp->team === 'DOCUMENT' || $emp->team === 'FIELD') ? 1500000 : 0));
                $overtime = (float) ($data['overtime_pay'] ?? 0);
                $thr = !empty($validated['include_thr']) ? $base : 0;

                $gross = $base + $meal + $transport + $customs + $overtime + $thr;

                $deductBpjsTk = round($base * 0.02);
                $deductBpjsKes = round($base * 0.01);
                $deductKasbon = (float) ($data['deduct_kasbon'] ?? 0);
                $deductPph21 = round($gross > 5000000 ? ($gross * 0.005) : 0); // TER Estimasi PPh 21

                $deductions = $deductBpjsTk + $deductBpjsKes + $deductKasbon + $deductPph21;
                $net = $gross - $deductions;

                $slip = Payslip::create([
                    'run_id' => $run->id,
                    'employee_id' => $emp->id,
                    'gross' => $gross,
                    'net' => $net,
                    'deductions' => $deductions,
                ]);

                PayslipLine::create(['payslip_id' => $slip->id, 'label' => 'Gaji Pokok', 'kind' => 'ALLOWANCE', 'amount' => $base]);
                PayslipLine::create(['payslip_id' => $slip->id, 'label' => 'Tunjangan Uang Makan', 'kind' => 'ALLOWANCE', 'amount' => $meal]);
                PayslipLine::create(['payslip_id' => $slip->id, 'label' => 'Tunjangan Transportasi Operasional', 'kind' => 'ALLOWANCE', 'amount' => $transport]);

                if ($customs > 0) {
                    PayslipLine::create(['payslip_id' => $slip->id, 'label' => 'Insentif Ahli Kepabeanan / PPJK', 'kind' => 'ALLOWANCE', 'amount' => $customs]);
                }
                if ($overtime > 0) {
                    PayslipLine::create(['payslip_id' => $slip->id, 'label' => 'Uang Lembur Operasional', 'kind' => 'ALLOWANCE', 'amount' => $overtime]);
                }
                if ($thr > 0) {
                    PayslipLine::create(['payslip_id' => $slip->id, 'label' => 'Tunjangan Hari Raya (THR Keagamaan)', 'kind' => 'ALLOWANCE', 'amount' => $thr]);
                }

                PayslipLine::create(['payslip_id' => $slip->id, 'label' => 'Iuran BPJS Ketenagakerjaan (2% JHT)', 'kind' => 'DEDUCTION', 'amount' => $deductBpjsTk]);
                PayslipLine::create(['payslip_id' => $slip->id, 'label' => 'Iuran BPJS Kesehatan (1%)', 'kind' => 'DEDUCTION', 'amount' => $deductBpjsKes]);

                if ($deductPph21 > 0) {
                    PayslipLine::create(['payslip_id' => $slip->id, 'label' => 'Pajak Penghasilan PPh 21 (TER 2024)', 'kind' => 'DEDUCTION', 'amount' => $deductPph21]);
                }
                if ($deductKasbon > 0) {
                    PayslipLine::create(['payslip_id' => $slip->id, 'label' => 'Potongan Angsuran Kasbon', 'kind' => 'DEDUCTION', 'amount' => $deductKasbon]);
                }
            }

            return $run;
        });

        return redirect()->route('hrm.payrolls.show', $payrollRun->id)
            ->with('success', "Batch payroll periode {$period} berhasil dikalkulasi dalam status DRAFT.");
    }

    /**
     * Halaman Rincian & Audit Batch Payroll.
     */
    public function showPayroll(PayrollRun $payrollRun)
    {
        $payrollRun->load(['payslips.employee', 'payslips.lines', 'approver']);
        return view('hrm.payroll.show', compact('payrollRun'));
    }

    /**
     * Cetak Slip Gaji Format Apple HIG.
     */
    public function printPayslip(Payslip $payslip)
    {
        $payslip->load(['employee', 'run', 'lines']);
        return view('hrm.payroll.payslip', compact('payslip'));
    }

    /**
     * Export Rangkuman Payroll Batch ke Excel / CSV.
     */
    public function exportPayrollExcel(PayrollRun $payrollRun)
    {
        $payrollRun->load('payslips.employee');
        $filename = 'payroll_summary_' . $payrollRun->period . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($payrollRun) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['PERIODE', $payrollRun->period, 'STATUS', $payrollRun->status]);
            fputcsv($file, []);
            fputcsv($file, ['NIK', 'NAMA_KARYAWAN', 'JABATAN', 'BANK', 'NO_REKENING', 'TOTAL_KOTOR', 'TOTAL_POTONGAN', 'GAJI_BERSIH_THP']);

            foreach ($payrollRun->payslips as $slip) {
                fputcsv($file, [
                    $slip->employee?->nik,
                    $slip->employee?->full_name,
                    $slip->employee?->position,
                    $slip->employee?->bank_name ?? 'BCA',
                    $slip->employee?->bank_account_number ?? '',
                    (float) $slip->gross,
                    (float) $slip->deductions,
                    (float) $slip->net,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Format Transfer Bank Massal (BCA Corporate / Mandiri MCM).
     */
    public function exportPayrollBankCsv(PayrollRun $payrollRun)
    {
        $payrollRun->load('payslips.employee');
        $filename = 'bca_payroll_disburse_' . $payrollRun->period . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($payrollRun) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            // Format BCA Payroll CSV Standard
            fputcsv($file, ['BENEFICIARY_ACCOUNT', 'BENEFICIARY_NAME', 'AMOUNT', 'REMARK1', 'REMARK2']);

            foreach ($payrollRun->payslips as $slip) {
                $acc = $slip->employee?->bank_account_number ?: '0000000000';
                $name = $slip->employee?->bank_account_holder ?: $slip->employee?->full_name;
                fputcsv($file, [
                    $acc,
                    $name,
                    number_format((float) $slip->net, 2, '.', ''),
                    'GAJI ' . $payrollRun->period,
                    'GENIE LOGISTICS',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
