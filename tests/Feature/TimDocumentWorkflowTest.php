<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Shipment;
use App\Models\ShippingVendor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TimDocumentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $docStaff;
    protected User $docSupervisor;
    protected User $unauthorizedUser;
    protected Client $client;
    protected ShippingVendor $vendor;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        // Create Permissions
        $pView = Permission::firstOrCreate(['code' => 'document.view'], ['name' => 'View Document', 'module' => 'core']);
        $pCreate = Permission::firstOrCreate(['code' => 'document.create'], ['name' => 'Create Document', 'module' => 'core']);
        $pVerify = Permission::firstOrCreate(['code' => 'document.verify'], ['name' => 'Verify Document', 'module' => 'core']);
        $pSubmit = Permission::firstOrCreate(['code' => 'document.submit'], ['name' => 'Submit Document CEISA', 'module' => 'core']);
        $pDoView = Permission::firstOrCreate(['code' => 'do.view'], ['name' => 'View DO', 'module' => 'core']);

        // Roles
        $roleStaff = Role::firstOrCreate(['code' => 'R-DOC-STAFF'], ['name' => 'Staff Dokumen Pabean']);
        $roleStaff->permissions()->syncWithoutDetaching([$pView->id, $pCreate->id]);

        $roleSpv = Role::firstOrCreate(['code' => 'R-DOC-SPV'], ['name' => 'Supervisor Dokumen']);
        $roleSpv->permissions()->syncWithoutDetaching([$pView->id, $pCreate->id, $pVerify->id, $pSubmit->id, $pDoView->id]);

        $roleGuest = Role::firstOrCreate(['code' => 'R-GUEST'], ['name' => 'Guest User']);

        // Users
        $this->docStaff = User::factory()->create(['name' => 'Staff Dokumen']);
        $this->docStaff->roles()->sync([$roleStaff->id]);

        $this->docSupervisor = User::factory()->create(['name' => 'Supervisor Dokumen']);
        $this->docSupervisor->roles()->sync([$roleSpv->id]);

        $this->unauthorizedUser = User::factory()->create(['name' => 'Unauthorized User']);
        $this->unauthorizedUser->roles()->sync([$roleGuest->id]);

        // Client & Vendor
        $this->client = Client::firstOrCreate(
            ['code' => 'CLI-TEST-01'],
            [
                'name' => 'PT INDO TEXTILE MAKMUR',
                'npwp' => '01.234.567.8-012.000',
                'nib' => '9120001234567',
                'email' => 'import@indotextile.co.id',
            ]
        );

        $this->vendor = ShippingVendor::firstOrCreate(
            ['code' => 'MAERSK'],
            [
                'name' => 'MAERSK LINE INDONESIA',
                'pic_email' => 'import.support@maersk.com',
                'is_active' => true,
            ]
        );
    }

    public function test_document_staff_can_view_project_index()
    {
        $response = $this->actingAs($this->docStaff)->get(route('core.document.index'));
        $response->assertStatus(200);
        $response->assertSee('Tim Document');
    }

    public function test_unauthorized_user_cannot_view_document_index()
    {
        $response = $this->actingAs($this->unauthorizedUser)->get(route('core.document.index'));
        $response->assertStatus(403);
    }

    public function test_document_staff_can_create_new_project_with_nine_document_slots()
    {
        $postData = [
            'client_id' => $this->client->id,
            'shipping_vendor_id' => $this->vendor->id,
            'type' => 'IMPORT',
            'bl_number' => 'MSKU-829102910',
            'vessel_name' => 'MAERSK HANOI',
            'voyage_number' => '2411E',
            'port_of_loading' => 'CNSHA',
            'port_of_discharge' => 'IDTPP',
            'eta' => now()->addDays(4)->format('Y-m-d'),
            'total_kontainer' => 1,
            'nomor_kontainer' => 'MSKU-1928374 (1x40HC)',
            'total_kemasan' => 150,
            'jenis_kemasan' => 'CTN',
            'total_bruto' => 8500.00,
            'total_netto' => 7800.00,
            'total_cif' => 25000.00,
            'currency' => 'USD',
            'ndbm_kurs' => 16200,
        ];

        $response = $this->actingAs($this->docStaff)->post(route('core.document.store'), $postData);
        $response->assertRedirect();

        $shipment = Shipment::where('nomor_bl_awb', 'MSKU-829102910')->first();
        $this->assertNotNull($shipment);
        $this->assertEquals('IMPORT_BC20', $shipment->jenis_layanan);
        $this->assertEquals(9, $shipment->documents()->count());
    }

    public function test_uploading_packing_list_triggers_hs_code_detection_automatically()
    {
        $shipment = Shipment::create([
            'project_no' => 'SHP-TEST-0001',
            'client_id' => $this->client->id,
            'shipping_vendor_id' => $this->vendor->id,
            'jenis_layanan' => 'IMPORT_BC20',
            'nomor_bl_awb' => 'BL-TEST-PL-01',
            'total_bruto' => 8500,
            'total_netto' => 7800,
            'total_nilai_valuta' => 25000,
            'status_tahap' => 'DOC_COLLECTING',
        ]);

        $file = UploadedFile::fake()->create('packing_list_final.pdf', 300, 'application/pdf');

        $response = $this->actingAs($this->docStaff)->post(
            route('core.document.upload-document', $shipment->id),
            [
                'document_type' => 'PACKING_LIST',
                'document_file' => $file,
            ]
        );

        $response->assertRedirect();

        $shipment->refresh();
        $this->assertGreaterThan(0, $shipment->items()->count());
        $this->assertEquals('HS_DETECTED', $shipment->status_tahap);
    }

    public function test_document_staff_can_add_and_delete_cargo_item()
    {
        $shipment = Shipment::create([
            'project_no' => 'SHP-TEST-ITEM',
            'client_id' => $this->client->id,
            'jenis_layanan' => 'IMPORT_BC20',
            'nomor_bl_awb' => 'BL-ITEM-01',
        ]);

        // Add item
        $response = $this->actingAs($this->docStaff)->post(
            route('core.document.items.store', $shipment->id),
            [
                'kode_hs' => '8471.30.20',
                'uraian_barang' => 'LAPTOP KOMPUTER PORTABEL',
                'hs_legal_basis' => 'BTKI 2022 Pos 8471',
                'jumlah_kemasan' => 10,
                'jenis_kemasan' => 'BOX',
                'berat_bruto' => 50,
                'berat_netto' => 45,
                'nilai_fob' => 5000,
                'tarif_bm_persen' => 0,
                'tarif_ppn_persen' => 11,
                'tarif_pph_persen' => 2.5,
            ]
        );

        $response->assertRedirect();
        $this->assertEquals(1, $shipment->items()->count());

        $item = $shipment->items()->first();

        // Delete item
        $deleteResp = $this->actingAs($this->docStaff)->delete(
            route('core.document.items.destroy', ['id' => $shipment->id, 'itemId' => $item->id])
        );

        $deleteResp->assertRedirect();
        $this->assertEquals(0, $shipment->items()->count());
        $this->assertEquals(1, $shipment->items()->withTrashed()->count()); // Soft delete verified
    }

    public function test_supervisor_can_verify_all_documents_and_submit_to_ceisa()
    {
        $service = app(\App\Services\Shipment\ShipmentWorkflowService::class);
        $shipment = $service->createProject([
            'client_id' => $this->client->id,
            'shipping_vendor_id' => $this->vendor->id,
            'type' => 'IMPORT',
            'bl_number' => 'BL-VERIFY-001',
            'total_cif' => 25000,
        ], $this->docStaff->id);

        // Upload Packing List to generate items
        $service->detectHsCodeFromPackingList($shipment);

        // Bulk verify by supervisor
        $verifyResp = $this->actingAs($this->docSupervisor)->post(route('core.document.verify-all', $shipment->id));
        $verifyResp->assertRedirect();

        $shipment->refresh();
        $this->assertTrue($shipment->is_all_mandatory_documents_verified);

        // Submit to CEISA 4.0
        $submitResp = $this->actingAs($this->docSupervisor)->post(route('core.document.submit-ceisa', $shipment->id));
        $submitResp->assertRedirect();

        $shipment->refresh();
        $this->assertEquals('BILLING_ISSUED', $shipment->status_tahap);
        $this->assertNotNull($shipment->ceisaDocument);
        $this->assertNotNull($shipment->ceisaDocument->kode_billing);
    }

    public function test_pdf_pib_and_billing_can_be_printed()
    {
        $service = app(\App\Services\Shipment\ShipmentWorkflowService::class);
        $shipment = $service->createProject([
            'client_id' => $this->client->id,
            'shipping_vendor_id' => $this->vendor->id,
            'type' => 'IMPORT',
            'bl_number' => 'BL-PDF-001',
            'total_cif' => 25000,
        ]);

        $service->detectHsCodeFromPackingList($shipment);
        $service->verifyAllDocuments($shipment, $this->docSupervisor->id);
        $service->submitCeisaAndAdvanceToDo($shipment);

        // Print PIB
        $pibResp = $this->actingAs($this->docStaff)->get(route('pdf.pib', $shipment->id));
        $pibResp->assertStatus(200);
        $pibResp->assertSee('PEMBERITAHUAN IMPOR BARANG (PIB)');

        // Print Billing
        $billingResp = $this->actingAs($this->docStaff)->get(route('pdf.billing', $shipment->id));
        $billingResp->assertStatus(200);
        $billingResp->assertSee('SURAT SETORAN PABEAN, CUKAI, DAN PAJAK (SSPCP)');
    }

    public function test_document_parsing_endpoint_returns_detected_information()
    {
        $blFile = UploadedFile::fake()->create('bill_of_lading_WHLU9182341.pdf', 200, 'application/pdf');
        $invFile = UploadedFile::fake()->create('commercial_invoice_usd25000.pdf', 150, 'application/pdf');
        $plFile = UploadedFile::fake()->create('packing_list_cartons.pdf', 180, 'application/pdf');

        $response = $this->actingAs($this->docStaff)->postJson(route('core.document.parse-documents'), [
            'client_id' => $this->client->id,
            'bl_file' => $blFile,
            'invoice_file' => $invFile,
            'packing_list_file' => $plFile,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonStructure([
            'status',
            'data' => [
                'bl_number',
                'vessel_name',
                'total_cif',
                'items',
            ]
        ]);

        $data = $response->json('data');
        $this->assertNotEmpty($data['bl_number']);
        $this->assertNotEmpty($data['items']);
    }

    public function test_project_can_be_created_purely_by_uploading_documents_with_auto_detection()
    {
        $blFile = UploadedFile::fake()->create('BL_WHLU-882910.pdf', 300, 'application/pdf');
        $invFile = UploadedFile::fake()->create('Commercial_Invoice_2026.pdf', 250, 'application/pdf');
        $plFile = UploadedFile::fake()->create('Packing_List_Cargo.pdf', 200, 'application/pdf');

        // Note: No manual bl_number or carrier supplied by user!
        $postData = [
            'client_id' => $this->client->id,
            'bl_file' => $blFile,
            'invoice_file' => $invFile,
            'packing_list_file' => $plFile,
        ];

        $response = $this->actingAs($this->docStaff)->post(route('core.document.store'), $postData);
        $response->assertRedirect();

        // Shipment must be automatically created with extracted values
        $shipment = Shipment::where('client_id', $this->client->id)->latest()->first();
        $this->assertNotNull($shipment);
        $this->assertNotEmpty($shipment->nomor_bl_awb);
        $this->assertEquals('IMPORT_BC20', $shipment->jenis_layanan);

        // Uploaded files must be mapped to their document slots
        $blDoc = $shipment->documents()->where('jenis_dokumen', 'BL_AWB')->first();
        $this->assertNotNull($blDoc);
        $this->assertEquals('UPLOADED', $blDoc->status);
        $this->assertNotNull($blDoc->nama_file);

        $invDoc = $shipment->documents()->where('jenis_dokumen', 'INVOICE')->first();
        $this->assertNotNull($invDoc);
        $this->assertEquals('UPLOADED', $invDoc->status);

        $plDoc = $shipment->documents()->where('jenis_dokumen', 'PACKING_LIST')->first();
        $this->assertNotNull($plDoc);
        $this->assertEquals('UPLOADED', $plDoc->status);

        // Cargo items with HS codes must be populated automatically
        $this->assertGreaterThanOrEqual(1, $shipment->items()->count());
        $firstItem = $shipment->items()->first();
        $this->assertNotNull($firstItem->kode_hs);
        $this->assertNotNull($firstItem->uraian_barang);
    }

    public function test_shipment_can_be_resynced_from_documents()
    {
        $service = app(\App\Services\Shipment\ShipmentWorkflowService::class);
        $shipment = $service->createProject([
            'client_id' => $this->client->id,
            'shipping_vendor_id' => $this->vendor->id,
            'type' => 'IMPORT',
            'bl_number' => 'BL-OLD-001',
            'total_cif' => 1000,
        ]);

        // Upload new doc
        $doc = $shipment->documents()->where('jenis_dokumen', 'BL_AWB')->first();
        $doc->update([
            'nama_file' => 'BL_WHLU-999888.pdf',
            'status' => 'UPLOADED',
        ]);

        $response = $this->actingAs($this->docStaff)->post(route('core.document.sync-from-docs', $shipment->id));
        $response->assertRedirect(route('core.document.show', $shipment->id));

        $shipment->refresh();
        $this->assertNotNull($shipment->nomor_bl_awb);
    }
}
