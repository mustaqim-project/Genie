<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Quotation;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class Sprint3And4ModulesTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::firstOrCreate(
            ['email' => 'admin_test@agungtrans.co.id'],
            [
                'name' => 'Test Admin',
                'password' => bcrypt('password'),
                'role' => 'R-OWN',
                'is_active' => true,
            ]
        );

        $role = \App\Models\Role::firstOrCreate(
            ['code' => 'R-OWN'],
            ['name' => 'Business Owner', 'description' => 'Owner']
        );

        if (!$this->user->roles()->where('code', 'R-OWN')->exists()) {
            $this->user->roles()->attach($role->id);
        }

        $this->client = Client::firstOrCreate(
            ['code' => 'CLI-TEST-001'],
            [
                'name' => 'PT Mitra Impor Maju',
                'npwp' => '0123456789012345',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'surat_kuasa_expired_at' => now()->addYear(),
            ]
        );
    }

    public function test_todo_creation_and_kanban_status_update()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('todos.store'), [
            'title' => 'Cek Dokumen BC 2.0 ATS-2026',
            'priority' => 'HIGH',
            'category' => 'CUSTOMS',
            'due_date' => now()->addDays(2)->toDateString(),
            'checklist_items' => ['Verifikasi BL', 'Verifikasi Invoice'],
        ]);

        $response->assertRedirect(route('todos.index'));

        $todo = Todo::where('title', 'Cek Dokumen BC 2.0 ATS-2026')->first();
        $this->assertNotNull($todo);
        $this->assertEquals('TODO', $todo->status);
        $this->assertCount(2, $todo->checklists);

        // Update status to DONE
        $this->post(route('todos.status', $todo->id), ['status' => 'DONE']);
        $todo->refresh();
        $this->assertEquals('DONE', $todo->status);
        $this->assertNotNull($todo->completed_at);
    }

    public function test_quotation_discount_approval_guard()
    {
        $this->actingAs($this->user);

        // Kasus 1: Diskon <= 10% langsung SENT
        $res1 = $this->post(route('crm.quotations.store'), [
            'client_id' => $this->client->id,
            'origin_port' => 'SGSIN',
            'destination_port' => 'IDTPP',
            'lines' => [
                [
                    'description' => 'Ocean Freight 20ft Normal',
                    'quantity' => 1,
                    'unit' => 'CONTAINER',
                    'unit_price_idr' => 10000000,
                    'discount_percent' => 5,
                ]
            ],
        ]);
        $res1->assertRedirect(route('crm.quotations.index'));
        
        $quo1 = Quotation::where('client_id', $this->client->id)->latest('created_at')->first();
        $this->assertNotNull($quo1);
        $this->assertEquals('NOT_REQUIRED', $quo1->discount_approval_status);
        $this->assertEquals('SENT', $quo1->status);

        // Kasus 2: Diskon > 10% memerlukan PENDING_APPROVAL
        sleep(1);
        $res2 = $this->post(route('crm.quotations.store'), [
            'client_id' => $this->client->id,
            'origin_port' => 'SGSIN',
            'destination_port' => 'IDTPP',
            'lines' => [
                [
                    'description' => 'Ocean Freight 40ft Special Disc',
                    'quantity' => 1,
                    'unit' => 'CONTAINER',
                    'unit_price_idr' => 20000000,
                    'discount_percent' => 15, // > 10%
                ]
            ],
        ]);
        $res2->assertRedirect(route('crm.quotations.index'));

        $quo2 = Quotation::where('client_id', $this->client->id)->latest('created_at')->first();
        $this->assertNotNull($quo2);
        $this->assertNotEquals($quo1->id, $quo2->id);
        $this->assertEquals('PENDING_APPROVAL', $quo2->discount_approval_status);
        $this->assertEquals('DRAFT', $quo2->status);

        // Approve discount by Owner
        $approveRes = $this->post(route('crm.quotations.approve-discount', $quo2->id), ['action' => 'APPROVE']);
        $approveRes->assertRedirect();
        $quo2->refresh();
        $this->assertEquals('APPROVED', $quo2->discount_approval_status);
        $this->assertEquals('SENT', $quo2->status);
    }

    public function test_sales_visit_geofencing_radius_check()
    {
        $this->actingAs($this->user);

        // Klien berada di -6.2088, 106.8456. Check-in di lokasi yang sama (< 200m)
        $response = $this->post(route('crm.sales-visits.check-in'), [
            'client_id' => $this->client->id,
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'notes' => 'Pertemuan negosiasi kontrak tahunan',
        ]);

        $response->assertRedirect(route('crm.sales-visits.index'));
        $this->assertDatabaseHas('sales_visits', [
            'client_id' => $this->client->id,
            'is_out_of_radius' => false,
        ]);
    }
}
