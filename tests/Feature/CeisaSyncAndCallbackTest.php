<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\CeisaDocument;
use App\Models\CeisaExchangeRate;
use App\Models\Client;
use App\Models\HsCode;
use App\Models\Shipment;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\Ceisa\CeisaClientService;
use App\Services\Ceisa\CeisaDocumentSyncService;
use App\Services\Ceisa\CeisaKursService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CeisaSyncAndCallbackTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

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
    }

    public function test_webhook_callback_from_ceisa_updates_existing_document_and_shipment()
    {
        $nomorAju = '04030001234520261112000099';

        $client = Client::create([
            'code' => 'CL-TEST-WH-' . rand(100, 999),
            'name' => 'PT Uji Webhook Sejahtera',
        ]);

        $doc = CeisaDocument::create([
            'nomor_aju' => $nomorAju,
            'document_type' => 'BC20',
            'status' => 'SUBMITTED',
            'status_billing' => 'UNPAID',
        ]);

        $shipment = Shipment::create([
            'project_no' => 'PRJ-WH-' . rand(1000, 9999),
            'client_id' => $client->id,
            'ceisa_document_id' => $doc->id,
            'jenis_layanan' => 'IMPORT_BC20',
            'nomor_bl_awb' => 'BL-WH-' . rand(1000, 9999),
            'tgl_bl_awb' => now(),
            'pelabuhan_muat' => 'CNSHA',
            'pelabuhan_bongkar' => 'IDTPP',
            'eta_date' => now()->addDays(2),
            'status_tahap' => 'CEISA_SUBMITTED',
        ]);

        // Kirim webhook callback dari CEISA dengan payload SPPB & Billing
        $response = $this->postJson(route('api.ceisa.webhook'), [
            'nomorAju' => $nomorAju,
            'status' => 'SPPB',
            'jalur' => 'HIJAU',
            'nomorDaftar' => '054321',
            'tanggalDaftar' => '2026-11-12',
            'dataRespon' => [
                [
                    'kodeRespon' => 'SPPB',
                    'nomorRespon' => 'SPPB-040300-20261112-0099',
                    'tanggalRespon' => '2026-11-12',
                    'keterangan' => 'Surat Persetujuan Pengeluaran Barang terbit (Jalur Hijau).',
                ]
            ],
            'billing' => [
                'kodeBilling' => '8202611120099',
                'totalNominal' => 15000000,
                'tglKadaluarsa' => '2026-11-15 23:59:59',
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'nomor_aju' => $nomorAju,
        ]);

        $doc->refresh();
        $shipment->refresh();

        $this->assertEquals('SPPB', $doc->status);
        $this->assertEquals('054321', $doc->nomor_daftar);
        $this->assertEquals('HIJAU', $doc->jalur);
        $this->assertEquals('8202611120099', $doc->kode_billing);
        $this->assertEquals('TRAFFIC_SPPB', $shipment->status_tahap);
        $this->assertEquals('HIJAU', $shipment->customs_channel);

        $this->assertDatabaseHas('ceisa_api_logs', [
            'nomor_aju' => $nomorAju,
            'method' => 'POST',
            'status_code' => 200,
        ]);
    }

    public function test_webhook_callback_enforces_webhook_secret_when_configured()
    {
        config(['ceisa.api.webhook_secret' => 'rahasia-ceisa-2026']);

        $payload = [
            'nomorAju' => '04030001234520261112000077',
            'status' => 'NPE',
        ];

        // Tanpa token / token salah -> 401 Unauthorized
        $unauthorized = $this->postJson(route('api.ceisa.webhook'), $payload);
        $unauthorized->assertStatus(401);

        // Dengan header X-CEISA-Webhook-Secret valid -> 200 OK
        $authorizedHeader = $this->withHeaders([
            'X-CEISA-Webhook-Secret' => 'rahasia-ceisa-2026',
        ])->postJson(route('api.ceisa.webhook'), $payload);
        $authorizedHeader->assertStatus(200);

        // Dengan query param ?token= valid -> 200 OK
        $authorizedQuery = $this->postJson(route('api.ceisa.webhook') . '?token=rahasia-ceisa-2026', $payload);
        $authorizedQuery->assertStatus(200);
    }

    public function test_sync_data_from_ceisa_via_service_and_http_endpoint()
    {
        $this->actingAs($this->user);
        config(['ceisa.api.mock_mode' => true]);

        $nomorAju = '04030001234520261112000088';

        $syncService = app(CeisaDocumentSyncService::class);
        $result = $syncService->syncByNomorAju($nomorAju, true);

        $this->assertTrue($result['success']);
        $this->assertNotNull($result['document']);
        $this->assertEquals($nomorAju, $result['document']->nomor_aju);

        $doc = CeisaDocument::where('nomor_aju', $nomorAju)->first();
        $this->assertNotNull($doc);
        $this->assertNotNull($doc->shipment);

        // Via HTTP endpoint POST /config/sync-document
        $nomorAjuHttp = '04030001234520261112000066';
        $httpResponse = $this->post(route('config.sync-document'), [
            'nomor_aju' => $nomorAjuHttp,
            'auto_create_shipment' => '1',
        ]);

        $httpResponse->assertRedirect();
        $httpResponse->assertSessionHas('success');
        $this->assertDatabaseHas('ceisa_documents', [
            'nomor_aju' => $nomorAjuHttp,
        ]);
    }

    public function test_setting_ceisa_api_form_saves_to_system_settings()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('setting.ceisa.update'), [
            'ceisa_mode' => 'sandbox',
            'ceisa_base_url' => 'https://apis-gw.beacukai.go.id',
            'ceisa_auth_url' => 'https://apis-gw.beacukai.go.id/nle-oauth/v1/user/login',
            'ceisa_refresh_token_url' => 'https://apis-gw.beacukai.go.id/nle-oauth/v1/user/update-token',
            'ceisa_username' => 'user_ppjk_test',
            'ceisa_api_key' => 'key_test_12345',
            'ceisa_application_id' => 'CEISA40-GENIE-01',
            'ceisa_whatsapp_no' => '6281234567890',
            'ceisa_webhook_secret' => 'token-callback-genie',
            'company_name' => 'PT Agung Trans Utama',
            'company_nib' => '1234567890123',
            'company_npwp' => '0123456789012345',
            'company_address' => 'Jl. Raya Pelabuhan No. 1, Jakarta Utara',
            'company_kode_kantor' => '040300',
            'company_id_perusahaan' => '010555',
            'ceisa_read_only' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals('user_ppjk_test', SystemSetting::get('ceisa_username'));
        $this->assertEquals('token-callback-genie', SystemSetting::get('ceisa_webhook_secret'));
        $this->assertEquals('1', SystemSetting::get('ceisa_read_only'));

        // Pastikan halaman Setting tab gateway, Config CEISA, BC20, dan BC30 dapat dirender tanpa error Blade
        $this->get(route('setting.index', ['tab' => 'gateway']))
            ->assertStatus(200)
            ->assertSee('api/v1/ceisa/webhook');

        $this->get(route('config.index'))
            ->assertStatus(200)
            ->assertSee('api/v1/ceisa/webhook');

        $this->get(route('bc20.index'))->assertStatus(200)->assertSee('Tarik Data CEISA');
        $this->get(route('bc30.index'))->assertStatus(200)->assertSee('Tarik Data CEISA');
    }

    public function test_read_only_mode_blocks_sending_documents_to_ceisa_while_allowing_sync_all()
    {
        $this->actingAs($this->user);
        config([
            'ceisa.api.read_only' => true,
            'ceisa.api.mock_mode' => true,
        ]);

        // 1. Pengiriman dokumen (POST /document) harus diblokir di mode Read-Only
        $client = app(CeisaClientService::class);
        $blocked = $client->post('/document', ['nomorAju' => '04030001234520261112000011'], '04030001234520261112000011');
        $this->assertFalse($blocked['success']);
        $this->assertEquals(403, $blocked['status_code']);

        // 2. Endpoint Tarik Semua Data CEISA (POST /config/sync-all) harus berhasil menarik dokumen, kurs, dan HS code
        $syncAllResponse = $this->post(route('config.sync-all'));
        $syncAllResponse->assertRedirect();
        $syncAllResponse->assertSessionHas('success');

        $this->assertGreaterThan(0, CeisaExchangeRate::where('kode_valuta', 'USD')->count());
        $this->assertGreaterThan(0, HsCode::count());
    }

    public function test_live_ceisa_response_format_parses_kurs_array_and_guards_not_found_status()
    {
        config([
            'ceisa.api.mock_mode' => false,
            'ceisa.api.read_only' => true,
            'ceisa.api.base_url' => 'https://apis-gw.beacukai.go.id/v2/openapi',
            'ceisa.api.auth_url' => 'https://apis-gw.beacukai.go.id/v1/openapi-auth/user/login',
            'ceisa.api.username' => 'tester_ceisa',
            'ceisa.api.password' => 'secret',
            'ceisa.api.api_key' => 'test-key',
        ]);

        Cache::forget('ceisa_access_token_' . md5('tester_ceisa'));

        Http::fake([
            'https://apis-gw.beacukai.go.id/v1/openapi-auth/user/login' => Http::response([
                'status' => 'success',
                'item' => ['access_token' => 'fake-live-jwt-token'],
            ], 200),
            'https://apis-gw.beacukai.go.id/v2/openapi/kurs/USD' => Http::response([
                'status' => 'true',
                'message' => 'success',
                'data' => [['nilaiKurs' => '18135.00']],
            ], 200),
            'https://apis-gw.beacukai.go.id/v2/openapi/status/04032001234520261012000099' => Http::response([
                'status' => 'Failed',
                'message' => 'Data Tidak Ditemukan',
            ], 200),
        ]);

        $kursRes = app(CeisaKursService::class)->getActiveKurs('USD', null, true);
        $this->assertEquals(18135.0, $kursRes['nilai_kurs']);
        $this->assertEquals('CEISA_OPENAPI', $kursRes['source']);

        $existingDoc = CeisaDocument::create([
            'nomor_aju' => '04032001234520261012000099',
            'jenis_dokumen' => '20',
            'status' => 'SPPB',
            'status_billing' => 'UNPAID',
        ]);

        $syncRes = app(CeisaDocumentSyncService::class)->syncByNomorAju('04032001234520261012000099', false);
        $this->assertFalse($syncRes['success']);
        $this->assertEquals('SPPB', $existingDoc->fresh()->status);
    }

    public function test_admin_user_can_see_shipments_and_ceisa_documents_on_core_document_page()
    {
        $admRole = \App\Models\Role::firstOrCreate(
            ['code' => 'R-ADM'],
            ['name' => 'Administrator', 'description' => 'System Admin']
        );

        $adminUser = User::create([
            'name' => 'Test R-ADM',
            'email' => 'radm_test_' . rand(100, 999) . '@agungtrans.co.id',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $adminUser->roles()->attach($admRole->id);

        $client = Client::create([
            'code' => 'CL-DOC-' . rand(100, 999),
            'name' => 'PT Uji Tampil Dokumen',
        ]);

        $doc = CeisaDocument::create([
            'nomor_aju' => '04032001234520261112000123',
            'jenis_dokumen' => '20',
            'status' => 'SPPB',
            'status_billing' => 'UNPAID',
        ]);

        $shipment = Shipment::create([
            'project_no' => 'PRJ-VIS-' . rand(1000, 9999),
            'client_id' => $client->id,
            'ceisa_document_id' => $doc->id,
            'jenis_layanan' => 'IMPORT_BC20',
            'nomor_bl_awb' => 'BL-VIS-' . rand(1000, 9999),
            'tgl_bl_awb' => now(),
            'pelabuhan_muat' => 'CNSHA',
            'pelabuhan_bongkar' => 'IDTPP',
            'eta_date' => now()->addDays(2),
            'status_tahap' => 'TRAFFIC_SPPB',
            'created_by_user_id' => null,
        ]);

        $this->actingAs($adminUser)
            ->get(route('core.document.index'))
            ->assertStatus(200)
            ->assertSee($shipment->project_no)
            ->assertSee('04032001234520261112000123')
            ->assertSee('Tarik Data CEISA');

        // Halaman detail /core/document/{id} di Mode Read-Only tidak menampilkan tombol upload ke CEISA
        $this->actingAs($adminUser)
            ->get(route('core.document.show', $shipment->id))
            ->assertStatus(200)
            ->assertSee('Mode Tarik Data Saja (Read-Only)')
            ->assertDontSee('Kirim Dokumen ke CEISA 4.0');

        // Submit ke CEISA via web maupun REST API harus diblokir saat Read-Only aktif
        config(['ceisa.api.read_only' => true]);
        $this->actingAs($adminUser)
            ->post(route('core.document.submit-ceisa', $shipment->id))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->postJson('/api/v1/ceisa/bc-20/submit', [])
            ->assertStatus(403);
    }

    public function test_ceisa_stage_mapping_places_documents_into_exact_genie_stages()
    {
        $this->assertEquals('CEISA_SUBMITTED', CeisaDocumentSyncService::resolveShipmentStage('SUBMITTED', null, null));
        $this->assertEquals('BILLING_ISSUED', CeisaDocumentSyncService::resolveShipmentStage('BILLING', null, '8202611120001'));
        $this->assertEquals('TRAFFIC_HOLD', CeisaDocumentSyncService::resolveShipmentStage('PERIKSA', 'MERAH', '8202611120001'));
        $this->assertEquals('TRAFFIC_SPPB', CeisaDocumentSyncService::resolveShipmentStage('SPPB', 'HIJAU', '8202611120001'));
        $this->assertEquals('TRAFFIC_SPPB', CeisaDocumentSyncService::resolveShipmentStage('NPE', 'HIJAU', null));
        $this->assertEquals('TRAFFIC_GATE_OUT', CeisaDocumentSyncService::resolveShipmentStage('GATE_OUT', 'HIJAU', '8202611120001'));
        $this->assertEquals('COMPLETED', CeisaDocumentSyncService::resolveShipmentStage('SPPB', 'HIJAU', null, 'COMPLETED'));
    }

    public function test_audit_log_handles_stale_nonexistent_actor_id_without_foreign_key_error()
    {
        $log = AuditLog::record(
            'LOGIN_FAILED',
            'User',
            null,
            null,
            ['email' => 'admin@agungtrans.co.id', 'remaining' => 4],
            '00000000-0000-0000-0000-000000000000'
        );

        $this->assertNull($log->actor_id);
        $this->assertEquals('LOGIN_FAILED', $log->action);
    }

    public function test_official_btki_csv_imports_all_hs_codes_while_preserving_curated_lartas_rates()
    {
        $service = app(\App\Services\Ceisa\CeisaBtkiTariffSyncService::class);
        $result = $service->syncFromBtkiRegistry(true);

        $this->assertTrue($result['success']);
        $this->assertEquals(11553, $result['official_csv_parsed']);
        $this->assertGreaterThan(11000, HsCode::count());

        // Official CSV codes from Bab 01 and Bab 99 exist
        $hs0101 = HsCode::where('code', '0101.21.00')->first();
        $this->assertNotNull($hs0101);
        $this->assertStringContainsString('Bibit', $hs0101->description);

        $hs9901 = HsCode::where('code', '9901.90.00')->first();
        $this->assertNotNull($hs9901);

        // Curated registry code keeps its detailed BM & Lartas metadata
        $hsCurated = HsCode::where('code', '8430.69.00')->first();
        $this->assertNotNull($hsCurated);
        $this->assertEquals('DJBC_BTKI_2022', $hsCurated->source);
        $this->assertEquals('0.00', (string) $hsCurated->duty_bm);
    }

    public function test_real_ceisa_status_and_respon_array_parses_payment_spjm_sppb_and_gate_out()
    {
        $realPayload = [
            'status' => 'Success',
            'message' => 'Data Ditemukan',
            'dataStatus' => [
                ['nomorDaftar' => '136953', 'tanggalDaftar' => '01-10-2026', 'kodeProses' => '210', 'waktuStatus' => '2026-09-30 16:28:13.134', 'keterangan' => 'Payment Verification'],
                ['nomorDaftar' => '136953', 'tanggalDaftar' => '01-10-2026', 'kodeProses' => '501', 'waktuStatus' => '2026-10-01 13:56:00.546', 'keterangan' => 'Gate Out TPS'],
                ['nomorDaftar' => '136953', 'tanggalDaftar' => '01-10-2026', 'kodeProses' => '001', 'waktuStatus' => '2026-09-29 16:20:39', 'keterangan' => 'Perekaman Dokumen'],
            ],
            'dataRespon' => [
                ['kodeRespon' => '2003', 'nomorRespon' => '137258/WBC.11/KPP.MP.01/2026', 'tanggalRespon' => '2026-10-01 13:56:00', 'waktuRespon' => '2026-10-01 13:56:00', 'keterangan' => 'SPPB'],
                ['kodeRespon' => '2020', 'nomorRespon' => null, 'tanggalRespon' => null, 'waktuRespon' => '2026-09-30 16:28:13.112', 'keterangan' => 'BILLING'],
            ],
        ];

        $parsed = CeisaDocumentSyncService::parseCeisaStatusResponse($realPayload, '20');
        $this->assertEquals('GATE_OUT', $parsed['status']);
        $this->assertEquals('PAID', $parsed['status_billing']);
        $this->assertEquals('137258/WBC.11/KPP.MP.01/2026', $parsed['nomor_sppb_npe']);
        $this->assertEquals('2026-10-01', $parsed['tanggal_sppb_npe']);
        $this->assertEquals('TRAFFIC_GATE_OUT', CeisaDocumentSyncService::resolveShipmentStage($parsed['status'], $parsed['jalur'], $parsed['kode_billing']));

        // Case with SPJM before SPPB
        $spjmPayload = [
            'status' => 'Success',
            'dataStatus' => [
                ['nomorDaftar' => '136954', 'tanggalDaftar' => '02-10-2026', 'kodeProses' => '210', 'waktuStatus' => '2026-10-02 09:00:00', 'keterangan' => 'Payment Verification'],
            ],
            'dataRespon' => [
                ['kodeRespon' => '2005', 'nomorRespon' => 'SPJM-001/2026', 'tanggalRespon' => '2026-10-02 10:00:00', 'keterangan' => 'SPJM'],
            ],
        ];
        $parsedSpjm = CeisaDocumentSyncService::parseCeisaStatusResponse($spjmPayload, '20');
        $this->assertEquals('SPJM', $parsedSpjm['status']);
        $this->assertEquals('PAID', $parsedSpjm['status_billing']);
        $this->assertEquals('MERAH', $parsedSpjm['jalur']);
        $this->assertEquals('TRAFFIC_HOLD', CeisaDocumentSyncService::resolveShipmentStage($parsedSpjm['status'], $parsedSpjm['jalur'], $parsedSpjm['kode_billing']));
    }

    public function test_import_ceisa_export_json_populates_importer_npwp_containers_and_items()
    {
        $admRole = \App\Models\Role::firstOrCreate(
            ['code' => 'R-ADM'],
            ['name' => 'Administrator', 'description' => 'System Admin']
        );
        $adminUser = User::create([
            'name' => 'Import Tester',
            'email' => 'import_test_' . rand(100, 999) . '@agungtrans.co.id',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $adminUser->roles()->attach($admRole->id);

        $jsonPayload = json_encode([
            'nomorAju' => '00002012345620261112000077',
            'kodeDokumen' => '20',
            'kodeKantor' => '040300',
            'kodeValuta' => 'USD',
            'ndpbm' => 15900,
            'cif' => 25000,
            'bruto' => 12500.5,
            'netto' => 11800.25,
            'kodePelMuat' => 'CNSHA',
            'kodePelTujuan' => 'IDTPP',
            'nomorBc11' => '001234',
            'tanggalBc11' => '2026-11-10',
            'entitas' => [
                ['kodeEntitas' => '1', 'namaEntitas' => 'PT MAJU JAYA IMPOR', 'nomorIdentitas' => '019998887776000'],
                ['kodeEntitas' => '5', 'namaEntitas' => 'SHANGHAI MACHINERY CO LTD', 'kodeNegara' => 'CN'],
            ],
            'dokumen' => [
                ['kodeDokumen' => '705', 'nomorDokumen' => 'COSU6391234567', 'tanggalDokumen' => '2026-11-01'],
            ],
            'kemasan' => [
                ['jumlahKemasan' => 45, 'kodeJenisKemasan' => 'PK'],
            ],
            'kontainer' => [
                ['nomorKontainer' => 'CSLU1234567'],
                ['nomorKontainer' => 'CSLU7654321'],
            ],
            'barang' => [
                [
                    'seriBarang' => 1,
                    'posTarif' => '84306900',
                    'uraian' => 'HYDRAULIC BREAKER EXCAVATOR',
                    'kodeNegaraAsal' => 'CN',
                    'jumlahKemasan' => 45,
                    'kodeJenisKemasan' => 'PK',
                    'netto' => 11800.25,
                    'bruto' => 12500.5,
                    'cif' => 25000,
                    'barangTarif' => [
                        ['kodeJenisTarif' => 'BM', 'tarif' => 5],
                        ['kodeJenisTarif' => 'PPN', 'tarif' => 11],
                        ['kodeJenisTarif' => 'PPH', 'tarif' => 2.5],
                    ],
                ],
            ],
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('ceisa_export.json', $jsonPayload);

        $response = $this->actingAs($adminUser)->post(route('config.import-document'), [
            'ceisa_file' => $file,
            'sync_status_after' => '0',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $doc = CeisaDocument::where('nomor_aju', '00002012345620261112000077')->first();
        $this->assertNotNull($doc);
        $this->assertEquals('PT MAJU JAYA IMPOR', $doc->nama_perusahaan_lokal);
        $this->assertEquals('019998887776000', $doc->npwp_perusahaan_lokal);
        $this->assertEquals('SHANGHAI MACHINERY CO LTD', $doc->nama_mitra_luar_negeri);
        $this->assertEquals(2, $doc->total_kontainer);
        $this->assertEquals(1, $doc->total_barang);

        $shipment = Shipment::where('ceisa_document_id', $doc->id)->first();
        $this->assertNotNull($shipment);
        $this->assertEquals('CSLU1234567, CSLU7654321', $shipment->nomor_kontainer);
        $this->assertEquals('COSU6391234567', $shipment->nomor_bl_awb);
        $this->assertEquals(1, $shipment->items()->count());
        $this->assertEquals('8430.69.00', $shipment->items()->first()->kode_hs);
    }
}
