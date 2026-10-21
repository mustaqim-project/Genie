<?php

namespace Tests\Feature;

use App\Models\CeisaDocument;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_crm_index_loads_with_ceisa_documents_successfully(): void
    {
        $admin = User::where('email', 'admin@agungtrans.co.id')->first();
        $this->actingAs($admin);

        // Buat dummy CeisaDocument
        CeisaDocument::create([
            'nomor_aju' => '040300-123456-20261020-000001',
            'jenis_dokumen' => '20',
            'status' => 'BILLING',
            'status_description' => 'Billing Terbit',
            'nama_mitra_luar_negeri' => 'Shanghai Electric Co',
            'negara_mitra_luar_negeri' => 'CN',
        ]);

        $response = $this->get(route('crm.index'));
        $response->assertStatus(200);
        $response->assertSee('040300-123456-20261020-000001');
        $response->assertSee('Shanghai Electric Co');
        $response->assertSee('Billing Terbit');
    }

    public function test_can_store_client_via_form(): void
    {
        $admin = User::where('email', 'admin@agungtrans.co.id')->first();
        $this->actingAs($admin);

        $postData = [
            'name' => 'PT. INDONESIA MAJU LOGISTIK',
            'npwp' => '01.234.567.8-012.000',
            'nib' => '9120001234567',
            'pic_name' => 'Bpk. Budi Santoso',
            'phone' => '081298765432',
            'email' => 'import@indonesiamaju.co.id',
            'address' => 'Kawasan Industri MM2100, Cikarang',
        ];

        $response = $this->post(route('crm.clients.store'), $postData);
        $response->assertStatus(302);

        $this->assertDatabaseHas('clients', [
            'name' => 'PT. INDONESIA MAJU LOGISTIK',
            'npwp' => '01.234.567.8-012.000',
            'pic_name' => 'Bpk. Budi Santoso',
        ]);
    }

    public function test_can_store_client_via_ajax_json(): void
    {
        $admin = User::where('email', 'admin@agungtrans.co.id')->first();
        $this->actingAs($admin);

        $postData = [
            'name' => 'PT. JINDI MATERIAL INDONESIA',
            'npwp' => '75.296.714.1-041.000',
            'pic_name' => 'Hendra Setiawan',
            'phone' => '081198765432',
        ];

        $response = $this->postJson(route('crm.clients.store'), $postData);
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'name' => 'PT. JINDI MATERIAL INDONESIA',
                'npwp' => '75.296.714.1-041.000',
            ],
        ]);

        $this->assertDatabaseHas('clients', [
            'name' => 'PT. JINDI MATERIAL INDONESIA',
            'npwp' => '75.296.714.1-041.000',
        ]);
    }
}
