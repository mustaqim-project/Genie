<?php

namespace Tests\Feature;

use App\Models\CeisaDocument;
use App\Models\Client;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentEditAndRollbackPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $staffDoc;
    protected User $spvDoc;
    protected User $regularUser;
    protected CeisaDocument $draftDoc;
    protected CeisaDocument $submittedDoc;
    protected Shipment $shipment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        // Register permissions
        $permEdit = Permission::firstOrCreate(['code' => 'document.edit'], ['name' => 'Edit Draft', 'module' => 'document']);
        $permEditAfterCeisa = Permission::firstOrCreate(['code' => 'document.edit_after_ceisa'], ['name' => 'Edit After CEISA', 'module' => 'document']);
        $permRollback = Permission::firstOrCreate(['code' => 'workflow.rollback_stage'], ['name' => 'Rollback Stage', 'module' => 'document']);
        $permView = Permission::firstOrCreate(['code' => 'document.view'], ['name' => 'View Document', 'module' => 'document']);

        // Roles
        $roleDoc = Role::firstOrCreate(['code' => 'R-TEST-DOC'], ['name' => 'Staff Document']);
        $roleDoc->permissions()->syncWithoutDetaching([$permView->id, $permEdit->id]);

        $roleSpv = Role::firstOrCreate(['code' => 'R-TEST-SPV'], ['name' => 'Supervisor Document']);
        $roleSpv->permissions()->syncWithoutDetaching([$permView->id, $permEdit->id, $permEditAfterCeisa->id, $permRollback->id]);

        $roleOther = Role::firstOrCreate(['code' => 'R-TEST-OTHER'], ['name' => 'Other Staff']);
        $roleOther->permissions()->syncWithoutDetaching([$permView->id]);

        $this->staffDoc = User::factory()->create(['name' => 'Staff Document User']);
        $this->staffDoc->roles()->sync([$roleDoc->id]);

        $this->spvDoc = User::factory()->create(['name' => 'Supervisor Document User']);
        $this->spvDoc->roles()->sync([$roleSpv->id]);

        $this->regularUser = User::factory()->create(['name' => 'Regular User']);
        $this->regularUser->roles()->sync([$roleOther->id]);

        // Draft Document
        $this->draftDoc = CeisaDocument::create([
            'nomor_aju' => '00002012345620261022000001',
            'jenis_dokumen' => '20',
            'kode_kantor' => '040300',
            'nama_kantor' => 'KPU Tanjung Priok',
            'status' => 'DRAFT',
            'kode_valuta' => 'USD',
            'ndbm_kurs' => 16200.0,
            'total_nilai' => 15000.0,
            'total_nilai_idr' => 243000000.0,
            'total_bruto' => 8500.0,
            'total_netto' => 7800.0,
            'nama_mitra_luar_negeri' => 'SUPPLIER TEST',
            'negara_mitra_luar_negeri' => 'CN',
            'raw_payload' => [
                'header' => ['fob' => 15000.0, 'freight' => 1000.0, 'asuransi' => 50.0, 'cif' => 16050.0, 'cifRupiah' => 260010000.0],
                'barang' => [
                    [
                        'kodeHs' => '84522900',
                        'uraian' => 'MESIN INDUSTRI',
                        'merk' => 'DDL-8700',
                        'jumlah' => 10,
                        'kemasan' => 'CTN',
                        'fob' => 15000.0,
                        'tarif_bm' => 5.0,
                        'tarif_ppn' => 11.0,
                        'tarif_pph' => 2.5,
                    ]
                ]
            ]
        ]);

        // Submitted Document
        $this->submittedDoc = CeisaDocument::create([
            'nomor_aju' => '00002012345620261022000002',
            'jenis_dokumen' => '20',
            'kode_kantor' => '040300',
            'nama_kantor' => 'KPU Tanjung Priok',
            'status' => 'SUBMITTED',
            'kode_valuta' => 'USD',
            'ndbm_kurs' => 16200.0,
            'total_nilai' => 15000.0,
            'total_nilai_idr' => 243000000.0,
            'total_bruto' => 8500.0,
            'total_netto' => 7800.0,
            'nama_mitra_luar_negeri' => 'SUPPLIER TEST',
            'negara_mitra_luar_negeri' => 'CN',
            'raw_payload' => [
                'header' => ['fob' => 15000.0, 'freight' => 1000.0, 'asuransi' => 50.0, 'cif' => 16050.0, 'cifRupiah' => 260010000.0],
                'barang' => [
                    [
                        'kodeHs' => '84522900',
                        'uraian' => 'MESIN INDUSTRI',
                        'merk' => 'DDL-8700',
                        'jumlah' => 10,
                        'kemasan' => 'CTN',
                        'fob' => 15000.0,
                        'tarif_bm' => 5.0,
                        'tarif_ppn' => 11.0,
                        'tarif_pph' => 2.5,
                    ]
                ]
            ]
        ]);

        $client = Client::firstOrCreate(
            ['code' => 'CL-TEST'],
            ['name' => 'PT TEST CLIENT', 'npwp' => '012345678901000']
        );

        $this->shipment = Shipment::create([
            'project_no' => 'SHP-TEST-001',
            'client_id' => $client->id,
            'jenis_layanan' => 'IMPORT_BC20',
            'nomor_bl_awb' => 'BL-TEST-001',
            'status_tahap' => 'CEISA_SUBMITTED',
            'ceisa_document_id' => $this->submittedDoc->id,
        ]);
    }

    public function test_staff_with_document_edit_can_view_and_update_draft_document(): void
    {
        $response = $this->actingAs($this->staffDoc)->get(route('bc20.edit', $this->draftDoc->id));
        $response->assertStatus(200);

        $updateData = [
            'kode_kantor' => '040300',
            'ndbm_kurs' => 16200,
            'kode_valuta' => 'USD',
            'fob' => 18000,
            'freight' => 1200,
            'asuransi' => 60,
            'bruto' => 9000,
            'netto' => 8200,
            'supplier_nama' => 'SUPPLIER UPDATED CO.',
            'supplier_negara' => 'CN',
            'invoice_no' => 'INV-UPDATED-01',
            'invoice_date' => '2026-10-22',
            'nama_sarana_pengangkut' => 'MV EVERGREEN 01',
            'pelabuhan_muat' => 'CNSHA',
            'pelabuhan_bongkar' => 'IDTPP',
            'barang' => [
                [
                    'hs_code' => '84522900',
                    'uraian' => 'MESIN JAHIT INDUSTRI UPDATED',
                    'merk' => 'BROTHER S-7200C',
                    'jumlah' => 12,
                    'kemasan' => 'CTN',
                    'fob' => 18000,
                    'tarif_bm' => 5,
                    'tarif_ppn' => 11,
                    'tarif_pph' => 2.5,
                ]
            ]
        ];

        $putResponse = $this->actingAs($this->staffDoc)->put(route('bc20.update', $this->draftDoc->id), $updateData);
        $putResponse->assertRedirect(route('bc20.show', $this->draftDoc->id));

        $this->draftDoc->refresh();
        $this->assertEquals('SUPPLIER UPDATED CO.', $this->draftDoc->nama_mitra_luar_negeri);
        $this->assertEquals(9000, $this->draftDoc->total_bruto);
    }

    public function test_staff_without_document_edit_after_ceisa_cannot_edit_submitted_document(): void
    {
        $response = $this->actingAs($this->staffDoc)->get(route('bc20.edit', $this->submittedDoc->id));
        $response->assertStatus(403);
    }

    public function test_supervisor_with_edit_after_ceisa_can_edit_and_reopen_submitted_document(): void
    {
        // 1. Can view edit page
        $response = $this->actingAs($this->spvDoc)->get(route('bc20.edit', $this->submittedDoc->id));
        $response->assertStatus(200);

        // 2. Can reopen to draft
        $reopenResponse = $this->actingAs($this->spvDoc)->post(route('bc20.reopen-draft', $this->submittedDoc->id));
        $reopenResponse->assertRedirect(route('bc20.show', $this->submittedDoc->id));

        $this->submittedDoc->refresh();
        $this->assertEquals('DRAFT', $this->submittedDoc->status);
    }

    public function test_rollback_stage_allows_supervisor_to_return_to_previous_pos(): void
    {
        $this->assertEquals('CEISA_SUBMITTED', $this->shipment->status_tahap);

        $response = $this->actingAs($this->spvDoc)->post(route('core.document.rollback-stage', $this->shipment->id), [
            'target_stage' => 'DOC_COLLECTING',
            'alasan' => 'Revisi packing list dari supplier kargo luar negeri',
            'reopen_ceisa_draft' => 1,
        ]);

        $response->assertSessionHas('success');
        $this->shipment->refresh();
        $this->assertEquals('DOC_COLLECTING', $this->shipment->status_tahap);

        $this->submittedDoc->refresh();
        $this->assertEquals('DRAFT', $this->submittedDoc->status);
    }

    public function test_unauthorized_user_cannot_rollback_stage(): void
    {
        $response = $this->actingAs($this->regularUser)->post(route('core.document.rollback-stage', $this->shipment->id), [
            'target_stage' => 'DOC_COLLECTING',
            'alasan' => 'Percobaan rollback tanpa izin',
        ]);

        $response->assertStatus(403);
    }

    public function test_cannot_edit_or_reopen_document_when_billing_is_issued_but_can_view(): void
    {
        // 1. Dokumen yang sudah terbit Billing MPN G3
        $billingDoc = CeisaDocument::create([
            'nomor_aju' => '00002012345620261022000003',
            'jenis_dokumen' => '20',
            'kode_kantor' => '040300',
            'nama_kantor' => 'KPU Tanjung Priok',
            'status' => 'BILLING',
            'kode_billing' => '100202610220001',
            'total_billing' => 12500000,
            'kode_valuta' => 'USD',
            'ndbm_kurs' => 16200.0,
            'total_nilai' => 15000.0,
            'total_nilai_idr' => 243000000.0,
            'total_bruto' => 8500.0,
            'total_netto' => 7800.0,
            'raw_payload' => [
                'header' => ['fob' => 15000.0, 'cif' => 16000.0, 'cifRupiah' => 259200000.0],
                'barang' => [
                    ['kodeHs' => '84522900', 'uraian' => 'MESIN JAHIT', 'jumlah' => 5]
                ]
            ]
        ]);

        $client = Client::first();
        $shipmentBilling = Shipment::create([
            'project_no' => 'SHP-BILLING-001',
            'client_id' => $client->id,
            'jenis_layanan' => 'IMPORT_BC20',
            'nomor_bl_awb' => 'BL-BILLING-001',
            'status_tahap' => 'BILLING_ISSUED',
            'billing_code' => '100202610220001',
            'ceisa_document_id' => $billingDoc->id,
        ]);

        // 2. Supervisor TIDAK BISA membuka form edit (dilarang saat billing)
        $editResponse = $this->actingAs($this->spvDoc)->get(route('bc20.edit', $billingDoc->id));
        $editResponse->assertRedirect(route('bc20.show', $billingDoc->id));
        $editResponse->assertSessionHas('error');

        // 3. Supervisor TIDAK BISA update data dokumen
        $updateResponse = $this->actingAs($this->spvDoc)->put(route('bc20.update', $billingDoc->id), [
            'kode_kantor' => '040300',
            'ndbm_kurs' => 16200,
            'kode_valuta' => 'USD',
            'fob' => 20000,
            'freight' => 1000,
            'asuransi' => 50,
            'bruto' => 8500,
            'netto' => 7800,
            'supplier_nama' => 'ILLEGAL CHANGE',
            'supplier_negara' => 'CN',
            'invoice_no' => 'INV-LOCKED',
            'invoice_date' => '2026-10-22',
            'nama_sarana_pengangkut' => 'MV EVERGREEN',
            'pelabuhan_muat' => 'CNSHA',
            'pelabuhan_bongkar' => 'IDTPP',
            'barang' => [
                ['hs_code' => '84522900', 'uraian' => 'MESIN JAHIT', 'jumlah' => 5, 'fob' => 20000, 'tarif_bm' => 5, 'tarif_ppn' => 11, 'tarif_pph' => 2.5]
            ]
        ]);
        $updateResponse->assertRedirect(route('bc20.show', $billingDoc->id));
        $updateResponse->assertSessionHas('error');

        // 4. Supervisor TIDAK BISA reopen dokumen ke status DRAFT
        $reopenResponse = $this->actingAs($this->spvDoc)->post(route('bc20.reopen-draft', $billingDoc->id));
        $reopenResponse->assertRedirect(route('bc20.show', $billingDoc->id));
        $reopenResponse->assertSessionHas('error');
        $billingDoc->refresh();
        $this->assertEquals('BILLING', $billingDoc->status);

        // 5. Rollback alur ke tahap sebelum billing (Pos 1-3) DITOLAK
        $rollbackResponse = $this->actingAs($this->spvDoc)->post(route('core.document.rollback-stage', $shipmentBilling->id), [
            'target_stage' => 'DOC_COLLECTING',
            'alasan' => 'Mencoba rollback saat billing aktif',
        ]);
        $rollbackResponse->assertSessionHas('error');
        $shipmentBilling->refresh();
        $this->assertEquals('BILLING_ISSUED', $shipmentBilling->status_tahap);

        // 6. NAMUN TETAP BISA VIEW SELURUH PROSES SEBELUMNYA
        $viewDocResponse = $this->actingAs($this->spvDoc)->get(route('bc20.show', $billingDoc->id));
        $viewDocResponse->assertStatus(200);
        $viewDocResponse->assertSee('Terkunci (Billing Terbit)');
        $viewDocResponse->assertSee('Mode Lihat Saja');

        $viewShipmentResponse = $this->actingAs($this->spvDoc)->get(route('core.document.show', $shipmentBilling->id));
        $viewShipmentResponse->assertStatus(200);
        $viewShipmentResponse->assertSee('Lihat Proses Sebelumnya');
    }
}

