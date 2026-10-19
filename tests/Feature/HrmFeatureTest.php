<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AttendanceLog;
use App\Models\BiometricConsent;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\PayrollRun;
use App\Models\Shipment;
use App\Models\ShipmentPic;
use App\Models\User;
use App\Models\WorkLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HrmFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $hrUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('email', 'admin@agungtrans.co.id')->first();
        $this->hrUser = User::where('email', 'hr@agungtrans.co.id')->first();
    }

    public function test_hrm_dashboard_renders_successfully(): void
    {
        $response = $this->actingAs($this->admin)->get(route('hrm.index'));
        $response->assertStatus(200);
        $response->assertViewHas(['employees', 'metrics', 'workLocations', 'payrollRuns', 'assets']);
    }

    public function test_store_employee_creates_contract_and_leave_balance(): void
    {
        $payload = [
            'nik' => 'PATS-TEST-9999',
            'full_name' => 'Kusuma Wardani',
            'email' => 'kusuma.test@agungtrans.co.id',
            'position' => 'Staff Operasional Pabean',
            'team' => 'DOCUMENT',
            'start_date' => '2026-10-01',
            'base_salary' => 8500000,
            'contract_kind' => 'PKWT',
            'contract_end_date' => '2027-09-30',
        ];

        $response = $this->actingAs($this->admin)->post(route('hrm.employees.store'), $payload);
        $response->assertRedirect(route('hrm.index', ['tab' => 'employees']));

        $this->assertDatabaseHas('employees', ['nik' => 'PATS-TEST-9999', 'full_name' => 'Kusuma Wardani']);
        $employee = Employee::where('nik', 'PATS-TEST-9999')->first();

        $this->assertNotNull($employee->user);
        $this->assertEquals('kusuma.test@agungtrans.co.id', $employee->user->email);
        $this->assertDatabaseHas('employment_contracts', ['employee_id' => $employee->id, 'kind' => 'PKWT']);
        $this->assertDatabaseHas('leave_balances', ['employee_id' => $employee->id, 'balance' => 12.0]);
    }

    public function test_clock_attendance_requires_uu_pdp_consent(): void
    {
        $employee = Employee::first();
        BiometricConsent::where('employee_id', $employee->id)->delete();

        $clockPayload = [
            'employee_id' => $employee->id,
            'kind' => 'CLOCK_IN',
            'lat' => -6.2297,
            'lng' => 106.8294,
            'face_match' => 0.98,
        ];

        $response = $this->actingAs($this->admin)->post(route('hrm.attendance.clock'), $clockPayload);
        $response->assertSessionHas('error');

        $this->actingAs($this->admin)->post(route('hrm.attendance.consent'), [
            'employee_id' => $employee->id,
            'agree' => '1',
        ]);

        $this->assertTrue($employee->fresh()->hasValidBiometricConsent());

        $response2 = $this->actingAs($this->admin)->post(route('hrm.attendance.clock'), $clockPayload);
        $response2->assertRedirect(route('hrm.index', ['tab' => 'attendance']));
        $this->assertDatabaseHas('attendance_logs', [
            'employee_id' => $employee->id,
            'kind' => 'CLOCK_IN',
            'valid' => true,
        ]);
    }

    public function test_leave_approval_triggers_forwarder_backup_pic_reassignment(): void
    {
        $docEmployee = Employee::where('team', 'DOCUMENT')->first();
        $this->assertNotNull($docEmployee->user_id);

        $backupUser = User::where('email', 'do.staff@agungtrans.co.id')->first();
        $shipment = Shipment::first();

        $pic = ShipmentPic::create([
            'shipment_id' => $shipment->id,
            'process_code' => 'P01',
            'user_id' => $docEmployee->user_id,
            'backup_id' => $backupUser->id,
        ]);

        $leaveRequest = LeaveRequest::create([
            'employee_id' => $docEmployee->id,
            'kind' => 'TAHUNAN',
            'from_date' => '2026-10-25',
            'to_date' => '2026-10-26',
            'status' => 'PENDING',
        ]);

        $initialBalance = LeaveBalance::where('employee_id', $docEmployee->id)->first()->balance;

        $response = $this->actingAs($this->admin)->post(route('hrm.leaves.approve', $leaveRequest->id));
        $response->assertRedirect(route('hrm.index', ['tab' => 'leaves']));

        $newBalance = LeaveBalance::where('employee_id', $docEmployee->id)->first()->balance;
        $this->assertEquals($initialBalance - 2, $newBalance);

        $this->assertEquals($backupUser->id, $pic->fresh()->user_id);
    }

    public function test_payroll_run_generation_and_disbursement(): void
    {
        $period = '2026-11';
        $response = $this->actingAs($this->admin)->post(route('hrm.payroll.generate'), ['period' => $period]);
        $response->assertRedirect(route('hrm.index', ['tab' => 'payroll']));

        $this->assertDatabaseHas('payroll_runs', ['period' => $period, 'status' => 'DRAFT']);
        $payrollRun = PayrollRun::where('period', $period)->first();

        $this->assertGreaterThan(0, $payrollRun->payslips()->count());
        $this->assertGreaterThan(0, $payrollRun->total_gross);
        $this->assertGreaterThan(0, $payrollRun->total_net);

        $this->actingAs($this->admin)->post(route('hrm.payroll.approve', $payrollRun->id));
        $this->assertEquals('APPROVED', $payrollRun->fresh()->status);

        $this->actingAs($this->admin)->post(route('hrm.payroll.disburse', $payrollRun->id));
        $this->assertEquals('DISBURSED', $payrollRun->fresh()->status);
    }

    public function test_asset_creation_assignment_and_return(): void
    {
        $assetData = [
            'code' => 'AST-NEW-099',
            'name' => 'MacBook Pro M3 Max (IT Lead)',
            'purchase_value' => 38000000,
        ];

        $this->actingAs($this->admin)->post(route('hrm.assets.store'), $assetData);
        $this->assertDatabaseHas('assets', ['code' => 'AST-NEW-099', 'status' => 'AVAILABLE']);

        $asset = Asset::where('code', 'AST-NEW-099')->first();
        $employee = Employee::first();

        $this->actingAs($this->admin)->post(route('hrm.assets.assign', $asset->id), [
            'employee_id' => $employee->id,
            'assigned_at' => now()->toDateString(),
        ]);

        $this->assertEquals('ASSIGNED', $asset->fresh()->status);
        $assignment = AssetAssignment::where('asset_id', $asset->id)->first();
        $this->assertNotNull($assignment);

        $this->actingAs($this->admin)->post(route('hrm.assets.return', $assignment->id));
        $this->assertEquals('AVAILABLE', $asset->fresh()->status);
        $this->assertNotNull($assignment->fresh()->returned_at);
    }
}
