<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientInvoice;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientInvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_invoices_index_loads_and_calculates_sum_correctly()
    {
        $admin = User::where('email', 'admin@agungtrans.co.id')->first();
        $this->actingAs($admin);

        $client = Client::first() ?? Client::factory()->create();
        $shipment = Shipment::first() ?? Shipment::factory()->create(['client_id' => $client->id]);

        ClientInvoice::create([
            'invoice_no' => 'INV-TEST-001',
            'client_id' => $client->id,
            'shipment_id' => $shipment->id,
            'tanggal' => now(),
            'jatuh_tempo' => now()->addDays(14),
            'talangan_pabean_idr' => 10000000,
            'biaya_pelayaran_idr' => 2000000,
            'jasa_ppjk_idr' => 1500000,
            'biaya_trucking_idr' => 1000000,
            'subtotal_idr' => 14500000,
            'ppn_jasa_idr' => 16500,
            'total_tagihan_idr' => 14516500,
            'status' => 'UNPAID',
        ]);

        $response = $this->get(route('finance.invoices.index'));
        $response->assertStatus(200);
        $response->assertSee('INV-TEST-001');
        $response->assertSee('14.516.500');

        // Test search filter by invoice_no
        $searchResponse = $this->get(route('finance.invoices.index', ['search' => 'INV-TEST-001']));
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('INV-TEST-001');
    }
}
