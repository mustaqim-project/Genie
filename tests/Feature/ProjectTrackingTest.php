<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Role;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $docUser;
    protected Shipment $shipment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        // Ambil User Admin dari Seeder
        $this->adminUser = User::where('email', 'admin@agungtrans.co.id')->firstOrFail();

        // Buat User Dokumen
        $docRole = Role::where('code', 'R-DOC')->firstOrFail();
        $this->docUser = User::firstOrCreate(
            ['email' => 'tracking_doc@pats-ceisa.test'],
            ['name' => 'Staff Dokumen User', 'password' => bcrypt('password'), 'is_active' => true]
        );
        $this->docUser->roles()->syncWithoutDetaching([$docRole->id]);

        // Siapkan Klien & Shipment
        $client = Client::first();

        $this->shipment = Shipment::firstOrCreate(
            ['project_no' => 'SHP-TEST-TRACK-01'],
            [
                'client_id' => $client->id,
                'type' => 'IMPORT',
                'status' => 'DOC_COLLECTING',
                'nomor_bl_awb' => 'BL-TRACK-12345',
                'tipe_layanan' => 'DOOR_TO_DOOR',
                'jalur_status' => 'MENUNGGU',
                'created_at' => now(),
            ]
        );
    }

    public function test_tracking_index_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('core.tracking.index'));

        $response->assertStatus(200);
        $response->assertSee('Tracking Project &amp; Workflow 11 Pos', false);
        $response->assertSee('SHP-TEST-TRACK-01');
        $response->assertSee('Dokumen');
        $response->assertSee('Invoice klien');
    }

    public function test_auto_assign_pic_for_single_shipment(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('core.document.assign-auto-pic', $this->shipment->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verifikasi bahwa PIC pos 'doc' telah di-assign
        $this->assertDatabaseHas('shipment_pic', [
            'shipment_id' => $this->shipment->id,
            'process_code' => 'doc',
        ]);
    }

    public function test_manual_set_and_remove_pic(): void
    {
        // 1. Set PIC
        $response = $this->actingAs($this->adminUser)
            ->post(route('core.document.set-pic', $this->shipment->id), [
                'process_code' => 'doc',
                'user_id' => $this->docUser->id,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('shipment_pic', [
            'shipment_id' => $this->shipment->id,
            'process_code' => 'doc',
            'user_id' => $this->docUser->id,
        ]);

        // 2. Remove PIC
        $response = $this->actingAs($this->adminUser)
            ->post(route('core.document.remove-pic', $this->shipment->id), [
                'process_code' => 'doc',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('shipment_pic', [
            'shipment_id' => $this->shipment->id,
            'process_code' => 'doc',
        ]);
    }

    public function test_take_over_pic_by_staff(): void
    {
        $response = $this->actingAs($this->docUser)
            ->post(route('core.document.take-over-pic', $this->shipment->id), [
                'process_code' => 'doc',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('shipment_pic', [
            'shipment_id' => $this->shipment->id,
            'process_code' => 'doc',
            'user_id' => $this->docUser->id,
        ]);
    }

    public function test_document_show_renders_11_pos_stepper_and_pic_assignment_card(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('core.document.show', $this->shipment->id));

        $response->assertStatus(200);
        $response->assertSee('Semua shipment');
        $response->assertSee('Pembagian Tugas 11 Pos');
        $response->assertSee('Assign Otomatis');
    }
}
