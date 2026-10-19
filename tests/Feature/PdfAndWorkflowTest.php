<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientInvoice;
use App\Models\Quotation;
use App\Models\Role;
use App\Models\Shipment;
use App\Models\ShippingVendor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PdfAndWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }
    public function test_all_models_use_uuid_and_soft_deletes(): void
    {
        $shipment = Shipment::first();
        $this->assertNotNull($shipment);
        $this->assertTrue(Str::isUuid($shipment->id));

        $client = Client::first();
        $this->assertNotNull($client);
        $this->assertTrue(Str::isUuid($client->id));

        $vendor = ShippingVendor::first();
        $this->assertNotNull($vendor);
        $this->assertTrue(Str::isUuid($vendor->id));

        $invoice = ClientInvoice::first();
        $this->assertNotNull($invoice);
        $this->assertTrue(Str::isUuid($invoice->id));

        $quotation = Quotation::first();
        $this->assertNotNull($quotation);
        $this->assertTrue(Str::isUuid($quotation->id));
    }

    public function test_all_pdf_views_render_successfully(): void
    {
        $shipment = Shipment::with(['client', 'ceisaDocument', 'items', 'shippingVendor'])->first();
        $this->assertNotNull($shipment);

        $invoice = ClientInvoice::with(['client', 'shipment.ceisaDocument'])->first();
        $this->assertNotNull($invoice);

        $quotation = Quotation::with('client')->first();
        $this->assertNotNull($quotation);

        // 1. PIB BC 2.0
        $pibHtml = view('pdf.pib', ['shipment' => $shipment])->render();
        $this->assertStringContainsString('PEMBERITAHUAN IMPOR BARANG', $pibHtml);
        $this->assertStringContainsString($shipment->shipment_number, $pibHtml);

        // 2. PEB BC 3.0
        $pebHtml = view('pdf.peb', ['shipment' => $shipment])->render();
        $this->assertStringContainsString('PEMBERITAHUAN EKSPOR BARANG', $pebHtml);

        // 3. Invoice Klien P19
        $invHtml = view('pdf.invoice', ['invoice' => $invoice])->render();
        $this->assertStringContainsString('INVOICE', $invHtml);
        $this->assertStringContainsString($invoice->invoice_number, $invHtml);

        // 4. Quotation Forwarder & PPJK
        $quoHtml = view('pdf.quotation', ['quotation' => $quotation])->render();
        $this->assertStringContainsString('QUOTATION', $quoHtml);
        $this->assertStringContainsString($quotation->quotation_number, $quoHtml);

        // 5. DO Request Letter
        $doHtml = view('pdf.do_request', ['shipment' => $shipment])->render();
        $this->assertStringContainsString('Delivery Order', $doHtml);

        // 6. Billing MPN G3
        $billHtml = view('pdf.billing_mpn', ['shipment' => $shipment])->render();
        $this->assertStringContainsString('MPN G3', $billHtml);
    }

    public function test_native_laravel_gate_authorization(): void
    {
        // Admin has full access
        $admin = User::where('email', 'admin@agungtrans.co.id')->first();
        $this->assertNotNull($admin);
        $this->assertTrue($admin->can('document.create'));
        $this->assertTrue($admin->can('finance.view'));
        $this->assertTrue($admin->can('do.process'));

        // Document staff has document.create but not finance.pay
        $docStaff = User::where('email', 'doc.staff@agungtrans.co.id')->first();
        $this->assertNotNull($docStaff);
        $this->assertTrue($docStaff->can('document.create'));
        $this->assertFalse($docStaff->can('finance.pay'));
    }

    public function test_authenticated_pdf_routes(): void
    {
        $admin = User::where('email', 'admin@agungtrans.co.id')->first();
        $shipment = Shipment::first();
        $invoice = ClientInvoice::first();
        $quotation = Quotation::first();

        $responsePib = $this->actingAs($admin)->get("/print/shipments/{$shipment->id}/pib");
        $responsePib->assertStatus(200);

        $responsePeb = $this->actingAs($admin)->get("/print/shipments/{$shipment->id}/peb");
        $responsePeb->assertStatus(200);

        $responseDo = $this->actingAs($admin)->get("/print/shipments/{$shipment->id}/do-request");
        $responseDo->assertStatus(200);

        $responseBill = $this->actingAs($admin)->get("/print/shipments/{$shipment->id}/billing");
        $responseBill->assertStatus(200);

        $responseInv = $this->actingAs($admin)->get("/print/invoices/{$invoice->id}");
        $responseInv->assertStatus(200);

        $responseQuo = $this->actingAs($admin)->get("/print/quotations/{$quotation->id}");
        $responseQuo->assertStatus(200);
    }
}
