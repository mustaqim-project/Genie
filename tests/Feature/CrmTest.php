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
}
