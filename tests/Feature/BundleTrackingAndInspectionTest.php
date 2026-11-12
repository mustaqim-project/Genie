<?php

namespace Tests\Feature;

use App\Models\CeisaDocument;
use App\Models\Client;
use App\Models\InspectionPhoto;
use App\Models\Role;
use App\Models\Shipment;
use App\Models\ShipmentOperationCost;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BundleTrackingAndInspectionTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::firstOrCreate(
            ['email' => 'bundle_test@agungtrans.co.id'],
            [
                'name' => 'Bundle Test Owner',
                'password' => bcrypt('password'),
                'role' => 'R-OWN',
                'is_active' => true,
            ]
        );

        $role = Role::firstOrCreate(
            ['code' => 'R-OWN'],
            ['name' => 'Business Owner', 'description' => 'Owner']
        );

        if (!$this->user->roles()->where('code', 'R-OWN')->exists()) {
            $this->user->roles()->attach($role->id);
        }
    }

    public function test_bundle_parse_and_autofill_bc20_works_100_percent_without_ai(): void
    {
        Storage::fake('public');
        Http::fake(); // Memastikan TIDAK ada panggilan HTTP ke gateway AI maupun CEISA

        $client = Client::create([
            'code' => 'CL-NOAI-' . rand(100, 999),
            'name' => 'PT Uji Tanpa AI Nusantara',
            'npwp' => '01.234.567.8-901.000',
            'address' => 'Kawasan Industri Cikarang Barat',
            'nib' => '1234567890123',
        ]);

        $blContent = implode("\n", [
            "OCEAN BILL OF LADING",
            "B/L NO: OOLU274819203",
            "VESSEL: MV EVER GLORY V.018E",
            "PORT OF LOADING: CNSHA (SHANGHAI)",
            "PORT OF DISCHARGE: IDTPP (TANJUNG PRIOK)",
            "CONTAINER NO: OOLU9081726",
            "GROSS WEIGHT: 12500.00 KG",
            "NET WEIGHT: 11800.00 KG",
            "TOTAL PACKAGES: 25 CTN",
        ]);

        $invPlContent = implode("\n", [
            "COMMERCIAL INVOICE & PACKING LIST",
            "INVOICE NO: INV-2026-8891",
            "1 | HYDRAULIC BREAKER MACHINE | 10 | 5800.00 | 5900.00 | 8500.00",
            "2 | EXCAVATOR BUCKET TEETH | 15 | 6000.00 | 6600.00 | 4000.00",
            "TOTAL FOB USD 12500.00",
        ]);

        $cooContent = "CERTIFICATE OF ORIGIN FORM E\nREF: E2601928374";

        $blFile = UploadedFile::fake()->createWithContent('01_Bill_of_Lading.txt', $blContent);
        $invFile = UploadedFile::fake()->createWithContent('02_Invoice_PackingList.csv', $invPlContent);
        $cooFile = UploadedFile::fake()->createWithContent('03_Form_E_COO.txt', $cooContent);

        // 1. Uji Live Preview AJAX dengan use_ai = 0 (Tanpa AI)
        $previewResp = $this->actingAs($this->user)->postJson(route('core.document.parse-documents'), [
            'client_id' => $client->id,
            'use_ai' => '0',
            'type' => 'IMPORT',
            'bundle_files' => [$blFile, $invFile, $cooFile],
        ]);

        $previewResp->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.extraction_mode', 'RULE_ENGINE_NO_AI')
            ->assertJsonPath('data.ai_used', false)
            ->assertJsonPath('data.type', 'IMPORT');

        $data = $previewResp->json('data');
        $this->assertNotEmpty($data['bundle_files_detected']);
        $this->assertEquals('BL', $data['bundle_files_detected'][0]['doc_type']);
        $this->assertGreaterThanOrEqual(2, count($data['items']));

        $aiHost = parse_url((string) config('ai.endpoint'), PHP_URL_HOST) ?: 'abc-tunnel';
        Http::assertNotSent(function ($request) use ($aiHost) {
            return str_contains($request->url(), 'chat/completions')
                || str_contains($request->url(), $aiHost);
        });

        // 2. Submit bundle untuk membuat proyek & auto-fill form BC 2.0 (PIB)
        $blFile2 = UploadedFile::fake()->createWithContent('01_Bill_of_Lading.txt', $blContent);
        $invFile2 = UploadedFile::fake()->createWithContent('02_Invoice_PackingList.csv', $invPlContent);
        $cooFile2 = UploadedFile::fake()->createWithContent('03_Form_E_COO.txt', $cooContent);

        $storeResp = $this->actingAs($this->user)->post(route('core.document.store'), [
            'client_id' => $client->id,
            'use_ai' => '0',
            'after_action' => 'fill_bc_form',
            'type' => 'IMPORT',
            'bl_number' => 'OOLU274819203',
            'nomor_kontainer' => 'OOLU9081726',
            'total_kemasan' => 25,
            'total_bruto' => 12500,
            'total_netto' => 11800,
            'total_cif' => 12500,
            'currency' => 'USD',
            'bundle_files' => [$blFile2, $invFile2, $cooFile2],
        ]);

        $storeResp->assertRedirect();
        $shipment = Shipment::where('client_id', $client->id)->latest()->firstOrFail();
        $this->assertEquals('IMPORT_BC20', $shipment->jenis_layanan);
        $this->assertNotNull($shipment->doc_received_at);
        $this->assertNotNull($shipment->ceisa_document_id);

        $ceisaDoc = CeisaDocument::findOrFail($shipment->ceisa_document_id);
        $this->assertEquals('20', $ceisaDoc->jenis_dokumen);
        $this->assertEquals('PT Uji Tanpa AI Nusantara', $ceisaDoc->nama_perusahaan_lokal);
        $this->assertEquals('01.234.567.8-901.000', $ceisaDoc->npwp_perusahaan_lokal);

        $payload = $ceisaDoc->raw_payload;
        $importirEntitas = collect($payload['entitas'] ?? [])->firstWhere('kodeEntitas', '1');
        $this->assertNotNull($importirEntitas);
        $this->assertEquals('PT UJI TANPA AI NUSANTARA', strtoupper($importirEntitas['namaEntitas'] ?? ''));
        $this->assertEquals('012345678901000', $importirEntitas['nomorIdentitas'] ?? '');
        $this->assertEquals('OOLU9081726', $payload['kontainer'][0]['nomorKontainer'] ?? '');
        $this->assertNotEmpty($payload['barang']);

        // Pastikan halaman Document Index, Detail Show, & Traffic Cockpit merender mulus tanpa error Blade
        $this->actingAs($this->user)->get(route('core.document.index'))->assertOk();
        $this->actingAs($this->user)->get(route('core.document.show', $shipment->id))->assertOk();
        $this->actingAs($this->user)->get(route('core.traffic.index'))->assertOk();

        $aiHost = parse_url((string) config('ai.endpoint'), PHP_URL_HOST) ?: 'abc-tunnel';
        Http::assertNotSent(function ($request) use ($aiHost) {
            return str_contains($request->url(), 'chat/completions')
                || str_contains($request->url(), $aiHost);
        });
    }

    public function test_tracking_dok_view_quick_updates_and_spjm_inspection_photos_workflow(): void
    {
        Storage::fake('public');

        $client = Client::create([
            'code' => 'CL-TRF-' . rand(100, 999),
            'name' => 'PT Uji Tracking & SPJM',
            'npwp' => '09.876.543.2-100.000',
        ]);

        $shipment = Shipment::create([
            'project_no' => 'PRJ-TEST-' . rand(1000, 9999),
            'client_id' => $client->id,
            'jenis_layanan' => 'IMPORT_BC20',
            'nomor_bl_awb' => 'BL-SPJM-9981',
            'nomor_kontainer' => 'MSCU-1122334 (1x40HC)',
            'status_tahap' => 'INSPECTION_CUSTOMS',
            'status_do' => 'DO_RELEASED',
            'do_is_released' => true,
            'created_by_user_id' => $this->user->id,
        ]);

        // 1. Halaman Tracking Dok Excel View & Export Filter
        $this->actingAs($this->user)
            ->get(route('core.tracking.index', ['mode' => 'tracking_dok', 'direction' => 'IMPORT']))
            ->assertOk()
            ->assertSee($shipment->project_no);

        // 2. Quick toggle SPJM via Tracking Dok AJAX
        $this->actingAs($this->user)
            ->postJson(route('core.tracking.quick-update', $shipment->id), [
                'action' => 'toggle_flag',
                'field' => 'spjm',
            ])
            ->assertOk()
            ->assertJsonPath('customs_responses.SPJM', true)
            ->assertJsonPath('customs_channel', 'MERAH');

        // 3. Upload Foto Pembongkaran & Foto Segel Kembali
        $photoUnload = UploadedFile::fake()->image('bongkar_kontainer.jpg', 800, 600);
        $photoReseal = UploadedFile::fake()->image('segel_kembali_bc.jpg', 800, 600);

        $this->actingAs($this->user)
            ->post(route('core.traffic.inspection-photos.store', $shipment->id), [
                'stage' => 'UNLOADING',
                'old_seal_number' => 'OLD-SEAL-111',
                'caption' => 'Bongkar kontainer disaksikan petugas',
                'photos' => [$photoUnload],
            ])
            ->assertRedirect();

        $this->actingAs($this->user)
            ->post(route('core.traffic.inspection-photos.store', $shipment->id), [
                'stage' => 'RESEALING',
                'old_seal_number' => 'OLD-SEAL-111',
                'seal_number' => 'BC-NEW-7788',
                'caption' => 'Kontainer disegel kembali pasca pemeriksaan',
                'photos' => [$photoReseal],
            ])
            ->assertRedirect();

        $shipment->refresh();
        $this->assertEquals('BC-NEW-7788', $shipment->new_seal_number);
        $this->assertEquals('OLD-SEAL-111', $shipment->old_seal_number);
        $this->assertNotNull($shipment->inspection_scheduled_at);
        $this->assertEquals(2, InspectionPhoto::where('shipment_id', $shipment->id)->count());

        // 4. Catat Biaya Behandle & Trucking
        $this->actingAs($this->user)
            ->post(route('core.traffic.operation-costs.store', $shipment->id), [
                'cost_category' => 'BHDL',
                'vendor_name' => 'TPS Koja',
                'invoice_number' => 'INV-BHDL-01',
                'amount' => 1750000,
            ])
            ->assertRedirect();

        $this->assertEquals(1, ShipmentOperationCost::where('shipment_id', $shipment->id)->where('cost_category', 'BHDL')->count());
    }
}
