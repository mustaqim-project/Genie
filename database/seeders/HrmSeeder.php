<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AttendanceLog;
use App\Models\BiometricConsent;
use App\Models\CashAdvance;
use App\Models\Employee;
use App\Models\EmploymentContract;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\PayrollRun;
use App\Models\Payslip;
use App\Models\PayslipLine;
use App\Models\Shift;
use App\Models\User;
use App\Models\WorkLocation;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class HrmSeeder extends Seeder
{
    /**
     * Run the database seeds for HRM Module.
     */
    public function run(): void
    {
        // 1. WORK LOCATIONS (Geofencing Kantor & Pelabuhan TPS)
        $locations = [
            [
                'name' => 'Kantor Pusat Genie / PATS - Mega Kuningan',
                'lat' => -6.2297,
                'lng' => 106.8294,
                'radius_m' => 150,
                'valid_from' => '2024-01-01',
                'valid_to' => null,
            ],
            [
                'name' => 'Pelabuhan Tanjung Priok - Terminal TPS Koja',
                'lat' => -6.1035,
                'lng' => 106.8920,
                'radius_m' => 800,
                'valid_from' => '2024-01-01',
                'valid_to' => null,
            ],
            [
                'name' => 'Pelabuhan Tanjung Priok - Terminal JICT I & II',
                'lat' => -6.1072,
                'lng' => 106.8841,
                'radius_m' => 1000,
                'valid_from' => '2024-01-01',
                'valid_to' => null,
            ],
            [
                'name' => 'Bandara Soekarno-Hatta - Terminal Kargo Lini 1 & 2',
                'lat' => -6.1256,
                'lng' => 106.6559,
                'radius_m' => 1200,
                'valid_from' => '2024-01-01',
                'valid_to' => null,
            ],
        ];

        $locationModels = [];
        foreach ($locations as $loc) {
            $locationModels[$loc['name']] = WorkLocation::firstOrCreate(
                ['name' => $loc['name']],
                $loc
            );
        }

        // 2. SHIFTS
        $shifts = [
            ['name' => 'Shift Reguler Kantor', 'start_time' => '08:30', 'end_time' => '17:30'],
            ['name' => 'Shift Lapangan Pagi (Behandle TPS)', 'start_time' => '07:00', 'end_time' => '15:30'],
            ['name' => 'Shift Lapangan Malam (Bongkar Muat)', 'start_time' => '15:00', 'end_time' => '23:30'],
        ];

        foreach ($shifts as $s) {
            Shift::firstOrCreate(['name' => $s['name']], $s);
        }

        // 3. EMPLOYEES & LINKAGE TO USERS
        $staffData = [
            [
                'email' => 'doc.staff@agungtrans.co.id',
                'nik' => 'PATS-2023-0101',
                'full_name' => 'Dewi Anggraini',
                'position' => 'Staff Dokumen Pabean (PIB/PEB)',
                'team' => 'DOCUMENT',
                'start_date' => '2023-03-01',
                'status' => 'ACTIVE',
                'base_salary' => 8500000,
                'contract_kind' => 'PKWTT',
            ],
            [
                'email' => 'do.staff@agungtrans.co.id',
                'nik' => 'PATS-2023-0102',
                'full_name' => 'Budi Santoso',
                'position' => 'Staff Delivery Order & Pelayaran',
                'team' => 'DO',
                'start_date' => '2023-04-15',
                'status' => 'ACTIVE',
                'base_salary' => 7800000,
                'contract_kind' => 'PKWT',
            ],
            [
                'email' => 'traffic.staff@agungtrans.co.id',
                'nik' => 'PATS-2023-0103',
                'full_name' => 'Ahmad Fauzi',
                'position' => 'Staff Traffic & Trucking Dispatcher',
                'team' => 'TRAFFIC',
                'start_date' => '2023-06-01',
                'status' => 'ACTIVE',
                'base_salary' => 8000000,
                'contract_kind' => 'PKWT',
            ],
            [
                'email' => 'field.staff@agungtrans.co.id',
                'nik' => 'PATS-2023-0104',
                'full_name' => 'Hendra Setiawan',
                'position' => 'Petugas Lapangan & Behandle TPS',
                'team' => 'FIELD',
                'start_date' => '2023-01-10',
                'status' => 'ACTIVE',
                'base_salary' => 7500000,
                'contract_kind' => 'PKWTT',
            ],
            [
                'email' => 'manager.ops@agungtrans.co.id',
                'nik' => 'PATS-2022-0050',
                'full_name' => 'Ir. Gunawan Wibowo',
                'position' => 'Operations Manager',
                'team' => 'OPERATIONS',
                'start_date' => '2022-02-01',
                'status' => 'ACTIVE',
                'base_salary' => 18000000,
                'contract_kind' => 'PKWTT',
            ],
            [
                'email' => 'finance@agungtrans.co.id',
                'nik' => 'PATS-2022-0088',
                'full_name' => 'Siti Nurhaliza, Ak.',
                'position' => 'Senior Finance & Tax Officer',
                'team' => 'FINANCE',
                'start_date' => '2022-05-15',
                'status' => 'ACTIVE',
                'base_salary' => 11000000,
                'contract_kind' => 'PKWTT',
            ],
            [
                'email' => 'hr@agungtrans.co.id',
                'nik' => 'PATS-2022-0099',
                'full_name' => 'Maya Kartika, S.Psi.',
                'position' => 'HR & General Affairs Specialist',
                'team' => 'HR',
                'start_date' => '2022-08-01',
                'status' => 'ACTIVE',
                'base_salary' => 9500000,
                'contract_kind' => 'PKWTT',
            ],
        ];

        $createdEmployees = [];
        $opsManagerUser = User::where('email', 'manager.ops@agungtrans.co.id')->first();

        foreach ($staffData as $item) {
            $user = User::where('email', $item['email'])->first();

            $employee = Employee::firstOrCreate(
                ['nik' => $item['nik']],
                [
                    'user_id' => $user?->id,
                    'full_name' => $item['full_name'],
                    'position' => $item['position'],
                    'team' => $item['team'],
                    'start_date' => $item['start_date'],
                    'status' => $item['status'],
                    'base_salary' => $item['base_salary'],
                ]
            );

            // Hubungkan user account ke employee_id
            if ($user && !$user->employee_id) {
                $user->employee_id = $employee->id;
                $user->save();
            }

            $createdEmployees[] = $employee;

            // Kontrak Kerja PKWT / PKWTT
            EmploymentContract::firstOrCreate(
                ['employee_id' => $employee->id, 'number' => 'CTR/' . $item['nik'] . '/2024'],
                [
                    'kind' => $item['contract_kind'],
                    'start_date' => $item['start_date'],
                    'end_date' => $item['contract_kind'] === 'PKWT' ? '2027-03-31' : null,
                ]
            );

            // Persetujuan Biometrik Wajah UU PDP No. 27/2022
            BiometricConsent::firstOrCreate(
                ['employee_id' => $employee->id],
                [
                    'consent_at' => Carbon::now()->subMonths(6),
                    'revoked_at' => null,
                    'retention_until' => Carbon::now()->addYears(2)->toDateString(),
                ]
            );

            // Sisa Hak Cuti Tahunan
            LeaveBalance::firstOrCreate(
                ['employee_id' => $employee->id, 'year' => 2026, 'kind' => 'TAHUNAN'],
                ['balance' => 12.0]
            );

            // Presensi Masuk Hari Ini
            $primaryLoc = ($item['team'] === 'FIELD' || $item['team'] === 'TRAFFIC')
                ? ($locationModels['Pelabuhan Tanjung Priok - Terminal TPS Koja'] ?? null)
                : ($locationModels['Kantor Pusat Genie / PATS - Mega Kuningan'] ?? null);

            AttendanceLog::firstOrCreate(
                [
                    'employee_id' => $employee->id,
                    'kind' => 'CLOCK_IN',
                    'at' => Carbon::today()->setHour(8)->setMinute(15),
                ],
                [
                    'lat' => $primaryLoc?->lat ?? -6.2297,
                    'lng' => $primaryLoc?->lng ?? 106.8294,
                    'location_id' => $primaryLoc?->id,
                    'face_match' => 0.9650,
                    'valid' => true,
                ]
            );
        }

        // 4. SAMPLE LEAVE REQUESTS & BACKUP
        $fieldEmployee = Employee::where('nik', 'PATS-2023-0104')->first();
        if ($fieldEmployee && $opsManagerUser) {
            LeaveRequest::firstOrCreate(
                [
                    'employee_id' => $fieldEmployee->id,
                    'from_date' => '2026-10-20',
                    'to_date' => '2026-10-22',
                ],
                [
                    'kind' => 'TAHUNAN',
                    'status' => 'PENDING',
                    'approver_id' => $opsManagerUser->id,
                ]
            );
        }

        // 5. SAMPLE OVERTIME REQUESTS
        $docEmployee = Employee::where('nik', 'PATS-2023-0101')->first();
        if ($docEmployee && $opsManagerUser) {
            OvertimeRequest::firstOrCreate(
                [
                    'employee_id' => $docEmployee->id,
                    'work_date' => '2026-10-10',
                ],
                [
                    'hours' => 3.5,
                    'status' => 'APPROVED',
                    'approver_id' => $opsManagerUser->id,
                ]
            );
        }

        // 6. SAMPLE CASH ADVANCE (Kasbon)
        $trafficEmployee = Employee::where('nik', 'PATS-2023-0103')->first();
        if ($trafficEmployee) {
            CashAdvance::firstOrCreate(
                ['employee_id' => $trafficEmployee->id, 'status' => 'ACTIVE'],
                [
                    'amount' => 3000000,
                    'installments' => 3,
                    'balance' => 2000000,
                ]
            );
        }

        // 7. ASSETS & ASSIGNMENTS (Laptop, HT TPS, Kartu Pass Pelabuhan)
        $assets = [
            ['code' => 'AST-LPT-01', 'name' => 'ThinkPad T14s Gen 4 (Staff Dokumen)', 'status' => 'ASSIGNED', 'purchase_value' => 19500000],
            ['code' => 'AST-LPT-02', 'name' => 'ThinkPad E14 Gen 5 (Staff DO)', 'status' => 'ASSIGNED', 'purchase_value' => 14200000],
            ['code' => 'AST-HT-01', 'name' => 'Motorola CP1660 VHF Walkie Talkie (TPS Koja)', 'status' => 'ASSIGNED', 'purchase_value' => 3800000],
            ['code' => 'AST-HT-02', 'name' => 'Motorola CP1660 VHF Walkie Talkie (TPS JICT)', 'status' => 'AVAILABLE', 'purchase_value' => 3800000],
            ['code' => 'AST-PASS-01', 'name' => 'Pelindo Port Pass Card TPS Koja', 'status' => 'ASSIGNED', 'purchase_value' => 500000],
            ['code' => 'AST-PASS-02', 'name' => 'Pelindo Port Pass Card JICT Behandle', 'status' => 'ASSIGNED', 'purchase_value' => 500000],
        ];

        foreach ($assets as $a) {
            $asset = Asset::firstOrCreate(['code' => $a['code']], $a);

            if ($a['status'] === 'ASSIGNED') {
                $targetEmp = match ($a['code']) {
                    'AST-LPT-01' => $docEmployee,
                    'AST-HT-01', 'AST-PASS-01' => $fieldEmployee,
                    'AST-PASS-02' => $trafficEmployee,
                    default => null,
                };

                if ($targetEmp) {
                    AssetAssignment::firstOrCreate(
                        ['asset_id' => $asset->id, 'employee_id' => $targetEmp->id, 'returned_at' => null],
                        ['assigned_at' => '2024-01-15']
                    );
                }
            }
        }

        // 8. PAYROLL RUN & PAYSLIPS (Periode Aktif 2026-10)
        $payrollRun = PayrollRun::firstOrCreate(
            ['period' => '2026-10'],
            [
                'status' => 'DRAFT',
                'approved_by_id' => $opsManagerUser?->id,
            ]
        );

        foreach ($createdEmployees as $emp) {
            $base = (float) $emp->base_salary;
            $allowanceMeal = 1200000;
            $allowanceTransport = 800000;
            $allowanceCustoms = ($emp->team === 'DOCUMENT' || $emp->team === 'FIELD') ? 1500000 : 0;
            $gross = $base + $allowanceMeal + $allowanceTransport + $allowanceCustoms;

            $deductBpjsTk = round($base * 0.02); // 2% JHT
            $deductBpjsKes = round($base * 0.01); // 1% BPJS Kes
            $deductKasbon = ($emp->id === $trafficEmployee?->id) ? 1000000 : 0;
            $deductions = $deductBpjsTk + $deductBpjsKes + $deductKasbon;
            $net = $gross - $deductions;

            $slip = Payslip::firstOrCreate(
                ['run_id' => $payrollRun->id, 'employee_id' => $emp->id],
                [
                    'gross' => $gross,
                    'net' => $net,
                    'deductions' => $deductions,
                ]
            );

            // Lines
            PayslipLine::firstOrCreate(
                ['payslip_id' => $slip->id, 'label' => 'Gaji Pokok Forwarder'],
                ['kind' => 'ALLOWANCE', 'amount' => $base]
            );

            PayslipLine::firstOrCreate(
                ['payslip_id' => $slip->id, 'label' => 'Uang Makan & Operasional'],
                ['kind' => 'ALLOWANCE', 'amount' => $allowanceMeal]
            );

            PayslipLine::firstOrCreate(
                ['payslip_id' => $slip->id, 'label' => 'Tunjangan Transport Pelabuhan'],
                ['kind' => 'ALLOWANCE', 'amount' => $allowanceTransport]
            );

            if ($allowanceCustoms > 0) {
                PayslipLine::firstOrCreate(
                    ['payslip_id' => $slip->id, 'label' => 'Insentif Sertifikasi PPJK & Lapangan TPS'],
                    ['kind' => 'ALLOWANCE', 'amount' => $allowanceCustoms]
                );
            }

            PayslipLine::firstOrCreate(
                ['payslip_id' => $slip->id, 'label' => 'Iuran BPJS Ketenagakerjaan (2%)'],
                ['kind' => 'DEDUCTION', 'amount' => $deductBpjsTk]
            );

            PayslipLine::firstOrCreate(
                ['payslip_id' => $slip->id, 'label' => 'Iuran BPJS Kesehatan (1%)'],
                ['kind' => 'DEDUCTION', 'amount' => $deductBpjsKes]
            );

            if ($deductKasbon > 0) {
                PayslipLine::firstOrCreate(
                    ['payslip_id' => $slip->id, 'label' => 'Potongan Cicilan Kasbon Pegawai'],
                    ['kind' => 'DEDUCTION', 'amount' => $deductKasbon]
                );
            }
        }
    }
}
