<?php

namespace Tests\Feature;

use App\Models\CeisaDocument;
use App\Models\Client;
use App\Models\ImportBatch;
use App\Models\ImportDocument;
use App\Models\ImportFile;
use App\Models\InspectionPhoto;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Shipment;
use App\Models\ShipmentDocument;
use App\Models\ShipmentItem;
use App\Models\ShipmentItemDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BulkDraftAndDeleteAssetsTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $viewerOnly;
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $ownerRole = Role::firstOrCreate(
            ['code' => 'R-OWN'],
            ['name' => 'Business Owner', 'description' => 'Owner']
        );

        $this->owner = User::create([
            'name' => 'Owner Tester',
            'email' => 'owner_bulk_test@agungtrans.co.id',
            'password' => bcrypt('password'),
            'role' => 'R-OWN',
            'is_active' => true,
        ]);
        $this->owner->roles()->attach($ownerRole->id);

        $viewPerm = Permission::firstOrCreate(
            ['code' => 'document.view'],
            ['name' => 'View Document', 'module' => 'document']
        );
        $viewerRole = Role::firstOrCreate(
            ['code' => 'R-VIEW-ONLY'],
            ['name' => 'Viewer Only', 'description' => 'Read Only']
        );
        $viewerRole->permissions()->sync([$viewPerm->id]);

        $this->viewerOnly = User::create([
            'name' => 'Viewer Only',
            'email' => 'viewer_bulk_test@agungtrans.co.id',
            'password' => bcrypt('password'),
            'role' => 'R-VIEW-ONLY',
            'is_active' => true,
        ]);
        $this->viewerOnly->roles()->attach($viewerRole->id);

        $this->client = Client::create([
            'code' => 'CL-BULK-001',
            'name' => 'PT Uji Bulk Draft Nusantara',
            'npwp' => '01.234.567.8-901.000',
            'address' => 'Jakarta Utara',
            'nib' => '1234567890123',
        ]);
    }

    public function test_bulk_bundle_upload_always_saves_as_draft_even_when_post_ceisa_requested(): void
    {
        Storage::fake('public');
        Http::fake();
        config(['ceisa.api.read_only' => false]);

        $blFile = UploadedFile::fake()->createWithContent('01_BL.txt', "OCEAN BILL OF LADING\nB/L NO: OOLU99887766\nVESSEL: MV TEST\nGROSS WEIGHT: 1000 KG\nNET WEIGHT: 950 KG\nTOTAL PACKAGES: 10 CTN");
        $invFile = UploadedFile::fake()->createWithContent('02_Invoice_PL.csv', "INVOICE NO: INV-BULK-01\n1 | HYDRAULIC PUMP | 10 | 950 | 1000 | 5000\nTOTAL FOB USD 5000");

        $resp = $this->actingAs($this->owner)->post(route('core.document.store'), [
            'client_id' => $this->client->id,
            'use_ai' => '0',
            'after_action' => 'post_ceisa',
            'type' => 'IMPORT',
            'bl_number' => 'OOLU99887766',
            'total_kemasan' => 10,
            'total_bruto' => 1000,
            'total_netto' => 950,
            'total_cif' => 5000,
            'currency' => 'USD',
            'bundle_files' => [$blFile, $invFile],
        ]);

        $resp->assertRedirect();

        $shipment = Shipment::where('client_id', $this->client->id)->latest()->firstOrFail();
        $ceisaDoc = CeisaDocument::findOrFail($shipment->ceisa_document_id);

        $this->assertEquals('DRAFT', $ceisaDoc->status);
        $this->assertTrue($shipment->isDraftDeletable());
        $this->assertTrue($ceisaDoc->isDraftDeletable());

        $uploadedPaths = $shipment->allDocuments()->pluck('file_path')->filter()->values()->all();
        $this->assertNotEmpty($uploadedPaths);
        foreach ($uploadedPaths as $path) {
            Storage::disk('public')->assertExists($path);
        }

        Http::assertNothingSent();
    }

    public function test_deleting_draft_project_removes_database_records_and_physical_document_assets(): void
    {
        Storage::fake('public');

        $blFile = UploadedFile::fake()->createWithContent('bl_draft.pdf', '%PDF-1.4 dummy bl');
        $invFile = UploadedFile::fake()->createWithContent('inv_draft.pdf', '%PDF-1.4 dummy inv');

        $this->actingAs($this->owner)->post(route('core.document.store'), [
            'client_id' => $this->client->id,
            'use_ai' => '0',
            'after_action' => 'fill_bc_form',
            'type' => 'IMPORT',
            'bl_number' => 'BL-DEL-001',
            'bundle_files' => [$blFile, $invFile],
        ])->assertRedirect();

        $shipment = Shipment::where('client_id', $this->client->id)->latest()->firstOrFail();
        $ceisaId = $shipment->ceisa_document_id;

        // Tambahkan ekstra item doc & inspection photo agar ikut teruji penghapusan asetnya
        $item = ShipmentItem::create([
            'shipment_id' => $shipment->id,
            'nomor_urut' => 1,
            'uraian_barang' => 'Pompa Uji',
            'kode_hs' => '8413.70.90',
            'negara_asal' => 'CN',
            'jumlah_kemasan' => 5,
            'jenis_kemasan' => 'CTN',
            'berat_netto' => 100,
            'berat_bruto' => 110,
            'nilai_fob' => 1000,
            'tarif_bm_persen' => 5,
            'tarif_ppn_persen' => 11,
            'tarif_pph_persen' => 2.5,
        ]);

        $extraItemPath = Storage::disk('public')->putFile('shipment_item_docs', UploadedFile::fake()->create('lartas.pdf', 10));
        ShipmentItemDocument::create([
            'shipment_id' => $shipment->id,
            'shipment_item_id' => $item->id,
            'kode_dokumen' => '911',
            'nama_dokumen' => 'SKB Uji',
            'file_name' => 'lartas.pdf',
            'file_path' => $extraItemPath,
            'status' => 'UPLOADED',
        ]);

        $extraPhotoPath = Storage::disk('public')->putFile("inspections/{$shipment->id}", UploadedFile::fake()->image('kontainer.jpg'));
        InspectionPhoto::create([
            'shipment_id' => $shipment->id,
            'flow_type' => 'IMPORT',
            'stage' => 'BEFORE_UNLOAD',
            'file_path' => $extraPhotoPath,
            'file_name' => 'kontainer.jpg',
        ]);

        $assetPaths = array_merge(
            $shipment->allDocuments()->pluck('file_path')->filter()->all(),
            [$extraItemPath, $extraPhotoPath]
        );
        $this->assertGreaterThanOrEqual(3, count($assetPaths));
        foreach ($assetPaths as $p) {
            Storage::disk('public')->assertExists($p);
        }

        // Hapus draft proyek beserta asetnya
        $delResp = $this->actingAs($this->owner)->delete(route('core.document.destroy', $shipment->id));
        $delResp->assertRedirect(route('core.document.index'))
            ->assertSessionHas('success');

        $this->assertNull(Shipment::withoutGlobalScopes()->withTrashed()->find($shipment->id));
        $this->assertNull(CeisaDocument::find($ceisaId));
        $this->assertEquals(0, ShipmentDocument::withTrashed()->where('shipment_id', $shipment->id)->count());
        $this->assertEquals(0, ShipmentItemDocument::withTrashed()->where('shipment_id', $shipment->id)->count());
        $this->assertEquals(0, InspectionPhoto::withTrashed()->where('shipment_id', $shipment->id)->count());

        foreach ($assetPaths as $p) {
            Storage::disk('public')->assertMissing($p);
        }
    }

    public function test_bulk_import_batch_creates_draft_and_delete_removes_batch_and_physical_files(): void
    {
        Storage::fake('public');

        $f1 = UploadedFile::fake()->createWithContent('batch_inv_01.txt', "COMMERCIAL INVOICE\nINVOICE NO: INV-9001\nTOTAL USD 1500");
        $f2 = UploadedFile::fake()->createWithContent('batch_bl_01.txt', "BILL OF LADING\nB/L NO: BL-9001\nGROSS WEIGHT: 500 KG");

        $resp = $this->actingAs($this->owner)->post(route('core.import.store'), [
            'client_id' => $this->client->id,
            'type' => 'IMPORT',
            'files' => [$f1, $f2],
            'notes' => 'Uji batch draft',
        ]);
        $resp->assertRedirect();

        $batch = ImportBatch::latest()->firstOrFail();
        $this->assertEquals('DRAFT', $batch->status, 'Errors: ' . json_encode($batch->files()->pluck('error_message')->all()));
        $this->assertEquals(2, $batch->documents()->count());
        $this->assertEquals(2, $batch->documents()->where('review_status', 'PENDING')->count());

        $files = $batch->files()->get();
        $this->assertCount(2, $files);
        foreach ($files as $f) {
            Storage::disk('public')->assertExists($f->file_path);
        }

        // Hapus satu dokumen draft dalam batch -> hanya file itu yang terhapus
        $firstDoc = $batch->documents()->firstOrFail();
        $firstPath = $firstDoc->file->file_path;
        $secondPath = $batch->files()->where('id', '!=', $firstDoc->file_id)->firstOrFail()->file_path;

        $this->actingAs($this->owner)->delete(route('core.import.doc.destroy', $firstDoc->id))
            ->assertRedirect(route('core.import.show', $batch->id));

        $this->assertNull(ImportDocument::find($firstDoc->id));
        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists($secondPath);

        // Hapus keseluruhan batch draft -> sisa file & batch terhapus bersih
        $this->actingAs($this->owner)->delete(route('core.import.destroy', $batch->id))
            ->assertRedirect(route('core.import.index'))
            ->assertSessionHas('success');

        $this->assertNull(ImportBatch::find($batch->id));
        $this->assertEquals(0, ImportFile::where('batch_id', $batch->id)->count());
        Storage::disk('public')->assertMissing($secondPath);
    }

    public function test_submitted_or_billing_locked_project_and_bc20_cannot_be_deleted(): void
    {
        Storage::fake('public');

        $ceisaDoc = CeisaDocument::create([
            'nomor_aju' => '000020123456' . date('Ymd') . '000001',
            'jenis_dokumen' => '20',
            'kode_kantor' => '040300',
            'nama_kantor' => 'KPU TANJUNG PRIOK',
            'nama_perusahaan_lokal' => $this->client->name,
            'npwp_perusahaan_lokal' => $this->client->npwp,
            'status' => 'SUBMITTED',
            'kode_billing' => '820261112000111',
            'raw_payload' => [],
        ]);

        $shipment = Shipment::create([
            'project_no' => 'PRJ-LOCKED-001',
            'client_id' => $this->client->id,
            'ceisa_document_id' => $ceisaDoc->id,
            'jenis_layanan' => 'IMPORT_BC20',
            'nomor_bl_awb' => 'BL-LOCKED-01',
            'status_tahap' => 'BILLING_ISSUED',
            'billing_code' => '820261112000111',
            'created_by_user_id' => $this->owner->id,
        ]);

        $docPath = Storage::disk('public')->putFile("shipment_docs/{$shipment->id}", UploadedFile::fake()->create('locked.pdf', 10));
        ShipmentDocument::create([
            'shipment_id' => $shipment->id,
            'jenis_dokumen' => 'BL_AWB',
            'nama_file' => 'locked.pdf',
            'file_path' => $docPath,
            'status' => 'VERIFIED',
            'is_current' => true,
        ]);

        $this->assertFalse($shipment->isDraftDeletable());
        $this->assertFalse($ceisaDoc->isDraftDeletable());

        $this->actingAs($this->owner)
            ->deleteJson(route('core.document.destroy', $shipment->id))
            ->assertStatus(422);

        $this->actingAs($this->owner)
            ->deleteJson(route('bc20.destroy', $ceisaDoc->id))
            ->assertStatus(422);

        $this->assertNotNull(Shipment::find($shipment->id));
        $this->assertNotNull(CeisaDocument::find($ceisaDoc->id));
        Storage::disk('public')->assertExists($docPath);
    }

    public function test_user_without_create_or_edit_permission_cannot_delete_draft(): void
    {
        $shipment = Shipment::create([
            'project_no' => 'PRJ-DRAFT-PERM',
            'client_id' => $this->client->id,
            'jenis_layanan' => 'IMPORT_BC20',
            'nomor_bl_awb' => 'BL-PERM-01',
            'status_tahap' => 'DOC_COLLECTING',
            'created_by_user_id' => $this->viewerOnly->id,
        ]);

        $this->actingAs($this->viewerOnly)
            ->delete(route('core.document.destroy', $shipment->id))
            ->assertStatus(403);

        $this->assertNotNull(Shipment::withoutGlobalScopes()->find($shipment->id));
    }

    public function test_deleting_draft_bc20_also_removes_linked_draft_shipment_and_document_assets(): void
    {
        Storage::fake('public');

        $blFile = UploadedFile::fake()->createWithContent('bc20_draft_bl.pdf', '%PDF-1.4 dummy');

        $this->actingAs($this->owner)->post(route('core.document.store'), [
            'client_id' => $this->client->id,
            'use_ai' => '0',
            'after_action' => 'fill_bc_form',
            'type' => 'IMPORT',
            'bl_number' => 'BL-BC20-DEL',
            'bundle_files' => [$blFile],
        ])->assertRedirect();

        $shipment = Shipment::where('client_id', $this->client->id)->latest()->firstOrFail();
        $ceisaDoc = CeisaDocument::findOrFail($shipment->ceisa_document_id);
        $assetPath = $shipment->allDocuments()->whereNotNull('file_path')->value('file_path');
        $this->assertNotEmpty($assetPath);
        Storage::disk('public')->assertExists($assetPath);

        // Pastikan halaman Blade utama terkait draft/delete dapat dirender tanpa error
        $this->actingAs($this->owner)->get(route('core.document.index'))->assertOk();
        $this->actingAs($this->owner)->get(route('core.document.show', $shipment->id))->assertOk();
        $this->actingAs($this->owner)->get(route('core.import.index'))->assertOk();
        $this->actingAs($this->owner)->get(route('bc20.index'))->assertOk();
        $this->actingAs($this->owner)->get(route('bc20.show', $ceisaDoc->id))->assertOk();

        // Hapus melalui endpoint bc20.destroy -> draft BC20, shipment, dan file asset ikut terhapus
        $this->actingAs($this->owner)
            ->delete(route('bc20.destroy', $ceisaDoc->id))
            ->assertRedirect(route('bc20.index'))
            ->assertSessionHas('success');

        $this->assertNull(CeisaDocument::find($ceisaDoc->id));
        $this->assertNull(Shipment::withoutGlobalScopes()->withTrashed()->find($shipment->id));
        Storage::disk('public')->assertMissing($assetPath);
    }

    public function test_type_import_or_export_is_strictly_required_everywhere_without_auto_detection(): void
    {
        Storage::fake('public');

        $blFile = UploadedFile::fake()->createWithContent('EXPORT_PEB_INV.txt', "BILL OF LADING\nSHIPPER: PT EKSPOR NUSANTARA\nEXPORT PEB\nB/L NO: BL-EXP-001");

        // 1. parse-documents tanpa type harus ditolak 422
        $this->actingAs($this->owner)->postJson(route('core.document.parse-documents'), [
            'client_id' => $this->client->id,
            'bundle_files' => [$blFile],
        ])->assertStatus(422)
            ->assertJsonPath('status', 'error');

        // 2. core.document.store (bundle upload) tanpa type harus gagal validasi
        $this->actingAs($this->owner)->post(route('core.document.store'), [
            'client_id' => $this->client->id,
            'bundle_files' => [$blFile],
        ])->assertSessionHasErrors('type');

        // 3. core.document.store (manual) tanpa type harus gagal validasi
        $this->actingAs($this->owner)->post(route('core.document.store'), [
            'client_id' => $this->client->id,
            'bl_number' => 'BL-NO-TYPE-MANUAL',
        ])->assertSessionHasErrors('type');

        // 4. core.import.store tanpa type harus gagal validasi
        $batchFile = UploadedFile::fake()->createWithContent('batch_inv.txt', "COMMERCIAL INVOICE\nINVOICE NO: INV-REQ-01");
        $this->actingAs($this->owner)->post(route('core.import.store'), [
            'client_id' => $this->client->id,
            'files' => [$batchFile],
        ])->assertSessionHasErrors('type');

        // 5. core.import.store dengan type EXPORT tersimpan sebagai EXPORT dan apply membuat EKSPORT_BC30
        $batchFileExp = UploadedFile::fake()->createWithContent('batch_export_01.txt', "COMMERCIAL INVOICE\nINVOICE NO: INV-EXP-777\nGROSS WEIGHT: 300 KG");
        $this->actingAs($this->owner)->post(route('core.import.store'), [
            'client_id' => $this->client->id,
            'type' => 'EXPORT',
            'files' => [$batchFileExp],
        ])->assertRedirect();

        $exportBatch = ImportBatch::latest()->firstOrFail();
        $this->assertEquals('EXPORT', $exportBatch->type);

        $exportDoc = $exportBatch->documents()->firstOrFail();
        $this->actingAs($this->owner)->post(route('core.import.doc.apply', $exportDoc->id))
            ->assertRedirect();

        $exportDoc->refresh();
        $this->assertNotNull($exportDoc->applied_shipment_id);
        $appliedShipment = Shipment::withoutGlobalScopes()->findOrFail($exportDoc->applied_shipment_id);
        $this->assertEquals('EKSPORT_BC30', $appliedShipment->jenis_layanan);

        // 6. DocumentExtractionService langsung tanpa preferredType harus lempar InvalidArgumentException
        $svc = app(\App\Services\Shipment\DocumentExtractionService::class);
        $this->expectException(\InvalidArgumentException::class);
        $svc->extractFromDocuments(['bundle' => [$blFile]], false, null);
    }

    public function test_core_document_index_displays_combined_bc20_and_bc30_documents_and_filters(): void
    {
        $bc20Standalone = CeisaDocument::create([
            'nomor_aju' => '000020111222' . date('Ymd') . '000020',
            'jenis_dokumen' => '20',
            'kode_kantor' => '040300',
            'nama_kantor' => 'KPU TANJUNG PRIOK',
            'nama_perusahaan_lokal' => $this->client->name,
            'npwp_perusahaan_lokal' => $this->client->npwp,
            'nama_mitra_luar_negeri' => 'SHANGHAI SUPPLIER LTD',
            'negara_mitra_luar_negeri' => 'CN',
            'kode_valuta' => 'USD',
            'total_nilai' => 12500,
            'total_nilai_idr' => 200000000,
            'status' => 'BILLING',
            'kode_billing' => '820261112999020',
            'total_billing' => 15000000,
            'raw_payload' => ['barang' => [['posTarif' => '84713020', 'uraian' => 'Laptop Industrial']]],
        ]);

        $bc30Standalone = CeisaDocument::create([
            'nomor_aju' => '000030333444' . date('Ymd') . '000030',
            'jenis_dokumen' => '30',
            'kode_kantor' => '050100',
            'nama_kantor' => 'KPPBC BELAWAN',
            'nama_perusahaan_lokal' => $this->client->name,
            'npwp_perusahaan_lokal' => $this->client->npwp,
            'nama_mitra_luar_negeri' => 'TOKYO BUYER CORP',
            'negara_mitra_luar_negeri' => 'JP',
            'kode_valuta' => 'USD',
            'total_nilai' => 34000,
            'total_nilai_idr' => 544000000,
            'status' => 'NPE',
            'nomor_sppb_npe' => 'NPE-2026-00991',
            'tanggal_sppb_npe' => now(),
            'raw_payload' => ['barang' => [['posTarif' => '09011110', 'uraian' => 'Kopi Arabika Gayo']]],
        ]);

        // Tampilan gabungan /core/document menampilkan baik BC 2.0 maupun BC 3.0
        $this->actingAs($this->owner)
            ->get(route('core.document.index'))
            ->assertOk()
            ->assertSee('BC 2.0 (PIB) + BC 3.0 (PEB)')
            ->assertSee($bc20Standalone->nomor_aju)
            ->assertSee('SHANGHAI SUPPLIER LTD')
            ->assertSee('820261112999020')
            ->assertSee($bc30Standalone->nomor_aju)
            ->assertSee('TOKYO BUYER CORP')
            ->assertSee('NPE-2026-00991')
            ->assertSee(route('bc20.show', $bc20Standalone->id), false)
            ->assertSee(route('bc30.show', $bc30Standalone->id), false);

        // Filter type=IMPORT hanya menampilkan BC 2.0
        $this->actingAs($this->owner)
            ->get(route('core.document.index', ['type' => 'IMPORT']))
            ->assertOk()
            ->assertSee($bc20Standalone->nomor_aju)
            ->assertDontSee($bc30Standalone->nomor_aju);

        // Filter type=EXPORT hanya menampilkan BC 3.0
        $this->actingAs($this->owner)
            ->get(route('core.document.index', ['type' => 'EXPORT']))
            ->assertOk()
            ->assertSee($bc30Standalone->nomor_aju)
            ->assertDontSee($bc20Standalone->nomor_aju);

        // Filter status=NPE & search nomor_aju bekerja lintas BC 2.0 / BC 3.0
        $this->actingAs($this->owner)
            ->get(route('core.document.index', ['status' => 'NPE', 'search' => 'TOKYO BUYER']))
            ->assertOk()
            ->assertSee($bc30Standalone->nomor_aju)
            ->assertDontSee($bc20Standalone->nomor_aju);
    }
}

