<?php

namespace Tests\Feature\Logistics;

use App\Console\Commands\CheckDemurrageAlertsCommand;
use App\Mail\VendorDoRequestMail;
use App\Models\Client;
use App\Models\Role;
use App\Models\Shipment;
use App\Models\ShippingVendor;
use App\Models\Todo;
use App\Models\User;
use App\Services\Shipment\ShipmentWorkflowService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DoReleaseAndDemurrageTest extends TestCase
{
    use DatabaseTransactions;

    protected User $doStaff;
    protected User $opsManager;
    protected Client $client;
    protected ShippingVendor $shippingLine;
    protected Shipment $shipment;
    protected ShipmentWorkflowService $workflowService;

    protected function setUp(): void
    {
        parent::setUp();

        $doRole = Role::firstOrCreate(['code' => 'R-DO'], ['name' => 'DO Staff']);
        $mgrRole = Role::firstOrCreate(['code' => 'R-MGR'], ['name' => 'Ops Manager']);

        $this->doStaff = User::firstOrCreate(
            ['email' => 'do_staff_test@agungtrans.co.id'],
            ['name' => 'DO Staff Tester', 'password' => bcrypt('password'), 'role' => 'R-DO', 'is_active' => true]
        );
        $this->doStaff->roles()->syncWithoutDetaching([$doRole->id]);

        $this->opsManager = User::firstOrCreate(
            ['email' => 'ops_mgr_test@agungtrans.co.id'],
            ['name' => 'Ops Manager Tester', 'password' => bcrypt('password'), 'role' => 'R-MGR', 'is_active' => true]
        );
        $this->opsManager->roles()->syncWithoutDetaching([$mgrRole->id]);

        $this->client = Client::firstOrCreate(
            ['code' => 'CL-DO-01'],
            ['name' => 'PT Logistik Makmur Bahari', 'npwp' => '01.555.666.7-888.000', 'address' => 'Tanjung Priok']
        );

        $this->shippingLine = ShippingVendor::firstOrCreate(
            ['code' => 'VND-ONE-01'],
            [
                'name' => 'Ocean Network Express (ONE)',
                'pic_name' => 'Support DO ONE',
                'pic_email' => 'import.id@one-line.com',
                'pic_phone' => '021-50987654',
                'is_active' => true,
            ]
        );

        $this->shipment = Shipment::create([
            'project_no' => 'PRJ-DO-DEM-001',
            'client_id' => $this->client->id,
            'shipping_vendor_id' => $this->shippingLine->id,
            'jenis_layanan' => 'IMPORT_BC20',
            'nomor_bl_awb' => 'ONEYJKT998811',
            'nama_kapal' => 'ONE HONG KONG',
            'voyage_no' => 'V.2026E',
            'nomor_kontainer' => 'ONEU1234567',
            'status_tahap' => 'BILLING_PAID',
            'status_do' => 'PENDING_REQUEST',
            'free_time_expired_at' => now()->addDays(3),
            'created_by_user_id' => $this->doStaff->id,
        ]);

        $this->workflowService = app(ShipmentWorkflowService::class);
    }

    /**
     * Tier 1: sendVendorEmailForDo Sends Real Mailable and Updates Shipment Status
     */
    public function test_send_vendor_email_for_do_dispatches_mailable_and_updates_status(): void
    {
        Mail::fake();

        $result = $this->workflowService->sendVendorEmailForDo(
            $this->shipment,
            $this->shippingLine->pic_email,
            'Mohon terbitkan invoice THC & EDO segera'
        );

        $this->assertEquals($this->shippingLine->pic_email, $result['recipient']);
        $this->assertStringContainsString('ONEYJKT998811', $result['subject']);

        $this->shipment->refresh();
        $this->assertEquals('EMAIL_SENT', $this->shipment->status_do);
        $this->assertEquals('DO_PROCESSING', $this->shipment->status_tahap);
        $this->assertNotNull($this->shipment->do_email_sent_at);
        $this->assertEquals($this->shippingLine->pic_email, $this->shipment->do_email_recipient);

        // Verifikasi Mail::send menerima instance VendorDoRequestMail yang tepat
        Mail::assertSent(VendorDoRequestMail::class, function ($mail) {
            return $mail->hasTo($this->shippingLine->pic_email)
                && $mail->shipment->id === $this->shipment->id
                && str_contains($mail->customNotes, 'Mohon terbitkan invoice');
        });
    }

    /**
     * Tier 1: releaseDo Records DO Number and Transitions Stage to DO_RELEASED
     */
    public function test_release_do_records_do_number_and_advances_stage(): void
    {
        $doNumber = 'DO-ONE-2026-990011';
        $expiredAt = now()->addDays(7)->format('Y-m-d H:i:s');

        $this->workflowService->releaseDo($this->shipment, $doNumber, $expiredAt);

        $this->shipment->refresh();
        $this->assertEquals('DO_RELEASED', $this->shipment->status_do);
        $this->assertEquals($doNumber, $this->shipment->nomor_do);
        $this->assertEquals('DO_RELEASED', $this->shipment->status_tahap);
    }

    /**
     * Tier 2 (Boundary & Escalation): Demurrage Radar Detects H-3, H-2, H-1 Free-Time Alerts
     */
    public function test_demurrage_check_alerts_creates_todos_for_expiring_containers(): void
    {
        // 1. Shipment H-3 (Sisa 3 hari)
        $shipmentH3 = Shipment::create([
            'project_no' => 'PRJ-DEM-H3',
            'client_id' => $this->client->id,
            'jenis_layanan' => 'IMPORT_BC20',
            'nomor_bl_awb' => 'BL-H3-001',
            'status_tahap' => 'ARRIVED',
            'free_time_expired_at' => now()->addDays(3)->startOfDay(),
        ]);

        // 2. Shipment H-1 (Sisa 1 hari -> CRITICAL)
        $shipmentH1 = Shipment::create([
            'project_no' => 'PRJ-DEM-H1',
            'client_id' => $this->client->id,
            'jenis_layanan' => 'IMPORT_BC20',
            'nomor_bl_awb' => 'BL-H1-001',
            'status_tahap' => 'ARRIVED',
            'free_time_expired_at' => now()->addDays(1)->startOfDay(),
        ]);

        // 3. Shipment yang sudah Gate Out (tidak boleh dialert)
        $shipmentGateOut = Shipment::create([
            'project_no' => 'PRJ-DEM-OUT',
            'client_id' => $this->client->id,
            'jenis_layanan' => 'IMPORT_BC20',
            'nomor_bl_awb' => 'BL-OUT-001',
            'status_tahap' => 'GATE_OUT',
            'free_time_expired_at' => now()->subDay(),
        ]);

        // Jalankan artisan check demurrage
        $this->artisan('shipment:check-demurrage')
            ->assertSuccessful();

        // Verifikasi Todo terbit untuk H-1 dan H-3
        $todoH1 = Todo::where('title', 'like', "%{$shipmentH1->project_no}%")->first();
        $this->assertNotNull($todoH1);
        $this->assertEquals('CRITICAL', $todoH1->priority);

        $todoH3 = Todo::where('title', 'like', "%{$shipmentH3->project_no}%")->first();
        $this->assertNotNull($todoH3);

        // Verifikasi shipment Gate Out tidak dibuatkan todo
        $todoOut = Todo::where('title', 'like', "%{$shipmentGateOut->project_no}%")->first();
        $this->assertNull($todoOut);
    }

    /**
     * Tier 2: Dry Run Mode Does Not Alter Database Records
     */
    public function test_demurrage_check_dry_run_mode_produces_no_side_effects(): void
    {
        $shipmentH2 = Shipment::create([
            'project_no' => 'PRJ-DEM-DRY',
            'client_id' => $this->client->id,
            'jenis_layanan' => 'IMPORT_BC20',
            'nomor_bl_awb' => 'BL-DRY-002',
            'status_tahap' => 'DO_RELEASED',
            'free_time_expired_at' => now()->addDays(2),
        ]);

        $initialTodoCount = Todo::where('title', 'like', "%{$shipmentH2->project_no}%")->count();

        $this->artisan('shipment:check-demurrage', ['--dry-run' => true])
            ->assertSuccessful();

        $this->assertEquals(
            $initialTodoCount,
            Todo::where('title', 'like', "%{$shipmentH2->project_no}%")->count()
        );
    }
}
