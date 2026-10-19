<?php

namespace Tests\Unit;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\BiometricConsent;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\PayrollRun;
use App\Models\Payslip;
use App\Models\Shipment;
use App\Models\ShipmentPic;
use App\Models\User;
use App\Models\WorkLocation;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HrmModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }
    public function test_employee_creation_and_relations(): void
    {
        $employee = Employee::first();
        $this->assertNotNull($employee);
        $this->assertNotEmpty($employee->nik);
        $this->assertNotEmpty($employee->full_name);
        $this->assertNotNull($employee->contracts);
        $this->assertNotNull($employee->attendanceLogs);
        $this->assertNotNull($employee->payslips);
    }

    public function test_work_location_geofencing_haversine(): void
    {
        $loc = WorkLocation::where('name', 'like', '%Mega Kuningan%')->first();
        $this->assertNotNull($loc);

        // Koordinat tepat di titik lokasi
        $this->assertTrue($loc->isWithinRadius($loc->lat, $loc->lng));

        // Koordinat jauh di luar radius (misal Monas ~6km)
        $this->assertFalse($loc->isWithinRadius(-6.1754, 106.8272));
    }

    public function test_biometric_consent_uu_pdp(): void
    {
        $consent = BiometricConsent::first();
        $this->assertNotNull($consent);
        $this->assertTrue($consent->isValid());

        // Revoke consent
        $consent->revoked_at = now();
        $this->assertFalse($consent->isValid());
    }

    public function test_shipment_pic_auto_reassign_to_backup(): void
    {
        $user1 = User::first();
        $user2 = User::skip(1)->first();
        $shipment = Shipment::first();

        if ($user1 && $user2 && $shipment) {
            $pic = ShipmentPic::create([
                'shipment_id' => $shipment->id,
                'process_code' => 'P01',
                'user_id' => $user1->id,
                'backup_id' => $user2->id,
            ]);

            $this->assertEquals($user1->id, $pic->user_id);
            $this->assertEquals($user2->id, $pic->backup_id);

            // Reassign to backup when user1 takes leave
            $reassigned = $pic->reassignToBackup();
            $this->assertTrue($reassigned);
            $this->assertEquals($user2->id, $pic->fresh()->user_id);
            $this->assertEquals($user1->id, $pic->fresh()->backup_id);
        }
    }

    public function test_leave_balance_deduction(): void
    {
        $balance = LeaveBalance::first();
        $this->assertNotNull($balance);

        $initial = $balance->balance;
        $this->assertTrue($balance->deduct(2.0));
        $this->assertEquals($initial - 2.0, $balance->fresh()->balance);
    }
}
