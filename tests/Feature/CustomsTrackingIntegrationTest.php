<?php

namespace Tests\Feature;

use App\Models\CeisaDocument;
use App\Models\Client;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Shipment;
use App\Models\ShippingVendor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomsTrackingIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Client $client;
    protected ShippingVendor $vendor;
    protected CeisaDocument $bc20Doc;
    protected CeisaDocument $bc30Doc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $permissions = [
            'document.view', 'document.create', 'document.edit', 'document.verify', 'document.submit',
            'do.view', 'traffic.view', 'ceisa.view', 'ceisa.submit'
        ];

        $permIds = [];
        foreach ($permissions as $code) {
            $p = Permission::firstOrCreate(['code' => $code], ['name' => $code, 'module' => 'core']);
            $permIds[] = $p->id;
        }

        $superRole = Role::firstOrCreate(['code' => 'R-SUPER-ADMIN'], ['name' => 'Super Administrator']);
        $superRole->permissions()->syncWithoutDetaching($permIds);

        $this->user = User::factory()->create(['name' => 'Test Operator Pabean']);
        $this->user->roles()->sync([$superRole->id]);

        $this->client = Client::firstOrCreate(
            ['code' => 'CL-TEST-CORP'],
            [
                'name' => 'PT GLOBAL TRADING NUSANTARA',
                'npwp' => '019827364512000',
                'nib' => '9120008899112',
                'email' => 'customs@globaltrading.co.id',
                'phone' => '021-88990011',
                'address' => 'Jakarta Barat',
            ]
        );

        $this->vendor = ShippingVendor::firstOrCreate(
            ['code' => 'EVERGREEN'],
            ['name' => 'PT EVERGREEN SHIPPING INDONESIA', 'is_active' => true]
        );

        // Dokumen BC 2.0 (PIB Impor)
        $this->bc20Doc = CeisaDocument::create([
            'nomor_aju' => '04032001234520261022000001',
            'jenis_dokumen' => '20',
            'status' => 'DRAFT',
            'status_description' => 'Draft PIB Impor',
            'kode_kantor' => '040300',
            'nama_kantor' => 'KPU Tanjung Priok',
            'kode_valuta' => 'USD',
            'ndbm_kurs' => 15850.00,
            'total_nilai' => 20000.00,
            'total_nilai_idr' => 317000000.00,
            'total_bruto' => 3500.00,
            'total_netto' => 3200.00,
            'total_kemasan' => 80,
            'total_kontainer' => 1,
            'nama_mitra_luar_negeri' => 'BEIJING ELECTRONICS CO.',
            'negara_mitra_luar_negeri' => 'CN',
        ]);

        // Dokumen BC 3.0 (PEB Ekspor)
        $this->bc30Doc = CeisaDocument::create([
            'nomor_aju' => '04033001234520261022000001',
            'jenis_dokumen' => '30',
            'status' => 'NPE',
            'status_description' => 'Nota Pelayanan Ekspor Diterbitkan',
            'nomor_daftar' => '009812',
            'nomor_sppb_npe' => 'NPE-040300-2026-009812',
            'kode_kantor' => '040300',
            'nama_kantor' => 'KPU Tanjung Priok',
            'kode_valuta' => 'USD',
            'ndbm_kurs' => 15850.00,
            'total_nilai' => 45000.00,
            'total_nilai_idr' => 713250000.00,
            'total_bruto' => 11000.00,
            'total_netto' => 10500.00,
            'total_kemasan' => 40,
            'total_kontainer' => 1,
            'nama_mitra_luar_negeri' => 'GLOBAL COFFEE BUYERS LLC',
            'negara_mitra_luar_negeri' => 'US',
        ]);
    }

    /**
     * 1. Menu 'Semua Proyek' (Core Document) menyajikan data gabungan BC 2.0 dan BC 3.0
     */
    public function test_core_document_index_displays_combined_bc20_and_bc30(): void
    {
        // Pastikan shipment otomatis dibuat untuk dokumen
        $this->bc20Doc->ensureShipment();
        $this->bc30Doc->ensureShipment();

        $response = $this->actingAs($this->user)->get(route('core.document.index'));
        $response->assertStatus(200);

        // Kedua jenis dokumen muncul di Semua Proyek
        $response->assertSee('BC 2.0 IMPOR');
        $response->assertSee('BC 3.0 EKSPOR');
        $response->assertSee('Daftar Proyek Shipment Aktif');
    }

    /**
     * 2. Menu 'BC 2.0 (PIB)' menyajikan dokumen impor secara terpisah
     */
    public function test_bc20_menu_displays_only_bc20_documents(): void
    {
        $response = $this->actingAs($this->user)->get(route('bc20.index'));
        $response->assertStatus(200);

        $response->assertSee($this->bc20Doc->nomor_aju);
        $response->assertDontSee($this->bc30Doc->nomor_aju);
        $response->assertSee('Pemberitahuan Impor Barang (BC 2.0)');
        $response->assertSee('Tracking 11 Pos');
    }

    /**
     * 3. Menu 'BC 3.0 (PEB)' menyajikan dokumen ekspor secara terpisah
     */
    public function test_bc30_menu_displays_only_bc30_documents(): void
    {
        $response = $this->actingAs($this->user)->get(route('bc30.index'));
        $response->assertStatus(200);

        $response->assertSee($this->bc30Doc->nomor_aju);
        $response->assertDontSee($this->bc20Doc->nomor_aju);
        $response->assertSee('Pemberitahuan Ekspor Barang (BC 3.0)');
        $response->assertSee('Tracking 11 Pos');
    }

    /**
     * 4. Halaman detail BC 2.0 memiliki tracking 11 Pos alur pabean
     */
    public function test_bc20_show_has_workflow_11_pos_tracking(): void
    {
        $response = $this->actingAs($this->user)->get(route('bc20.show', $this->bc20Doc->id));
        $response->assertStatus(200);

        // Stepper alur pabean 11 Pos
        $response->assertSee('Semua Proyek');
        $response->assertSee('BC 2.0 (PIB)');
        $response->assertSee('Lihat Proses Sebelumnya');
        $response->assertSee('Pos 1: Pengumpulan');
        $response->assertSee('Pembagian Tugas 11 Pos');
    }

    /**
     * 5. Halaman detail BC 3.0 memiliki tracking 11 Pos alur pabean
     */
    public function test_bc30_show_has_workflow_11_pos_tracking(): void
    {
        $response = $this->actingAs($this->user)->get(route('bc30.show', $this->bc30Doc->id));
        $response->assertStatus(200);

        // Stepper alur pabean 11 Pos
        $response->assertSee('Semua Proyek');
        $response->assertSee('BC 3.0 (PEB)');
        $response->assertSee('Lihat Proses Sebelumnya');
        $response->assertSee('Pos 1: Pengumpulan');
        $response->assertSee('Pembagian Tugas 11 Pos');
    }

    /**
     * 6. Core document show dapat dibuka menggunakan ID Shipment maupun ID CeisaDocument
     */
    public function test_core_document_show_resolves_shipment_or_ceisa_id(): void
    {
        $shipment = $this->bc20Doc->ensureShipment();

        // Akses via ID Shipment
        $resShipment = $this->actingAs($this->user)->get(route('core.document.show', $shipment->id));
        $resShipment->assertStatus(200);
        $resShipment->assertSee($shipment->project_no);

        // Akses via ID CeisaDocument
        $resCeisa = $this->actingAs($this->user)->get(route('core.document.show', $this->bc20Doc->id));
        $resCeisa->assertStatus(200);
        $resCeisa->assertSee($shipment->project_no);
    }
}
