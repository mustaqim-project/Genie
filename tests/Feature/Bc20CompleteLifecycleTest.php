<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientInvoice;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Shipment;
use App\Models\ShippingVendor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Bc20CompleteLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected User $operator;
    protected Client $client;
    protected ShippingVendor $vendor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        Storage::fake('public');

        // Create Permissions required for all modules
        $permissions = [
            'document.view', 'document.create', 'document.verify', 'document.submit',
            'do.view', 'do.process', 'do.release',
            'traffic.view', 'traffic.process', 'traffic.gateout',
            'ceisa.submit', 'ceisa.view',
        ];

        $permissionIds = [];
        foreach ($permissions as $code) {
            $p = Permission::firstOrCreate(['code' => $code], ['name' => $code, 'module' => 'core']);
            $permissionIds[] = $p->id;
        }

        $superRole = Role::firstOrCreate(['code' => 'R-SUPER-ADMIN'], ['name' => 'Super Administrator']);
        $superRole->permissions()->syncWithoutDetaching($permissionIds);

        $this->operator = User::factory()->create(['name' => 'Operasional Test User']);
        $this->operator->roles()->sync([$superRole->id]);

        $this->client = Client::firstOrCreate(
            ['code' => 'CL-TEST-BC20'],
            [
                'name' => 'PT INDO JAYA MAKMUR',
                'npwp' => '012345678901000',
                'nib' => '9120001234567',
                'email' => 'import@indojaya.co.id',
                'phone' => '021-5551234',
                'address' => 'Kawasan Industri Pulogadung, Jakarta Timur',
            ]
        );

        $this->vendor = ShippingVendor::firstOrCreate(
            ['code' => 'MAEU'],
            [
                'name' => 'MAERSK LINE INDONESIA',
                'email' => 'import.cs@maersk.com',
                'pic_email' => 'import.cs@maersk.com',
                'phone' => '021-30001122',
                'is_active' => true,
            ]
        );
    }

    /**
     * Test alur lengkap BC 2.0 (PIB):
     * Create Project -> Verifikasi 3 Berkas -> Generate & Submit CEISA 4.0 (Billing) ->
     * Handoff Tim DO (Email Pelayaran & Rilis DO) -> Handoff Tim Traffic (Jalur Hijau SPPB, Gate Out TPS, POD) -> Invoicing Finance
     */
    public function test_bc20_full_lifecycle_from_creation_to_pod(): void
    {
        // ------------------------------------------------------------------
        // TAHAP 1: Tim Dokumen - Registrasi Proyek Impor (BC 2.0)
        // ------------------------------------------------------------------
        $blFile = UploadedFile::fake()->create('BL-MSK-202610.pdf', 300, 'application/pdf');
        $invFile = UploadedFile::fake()->create('INV-EXP-202610.pdf', 250, 'application/pdf');
        $plFile = UploadedFile::fake()->create('PL-EXP-202610.pdf', 250, 'application/pdf');

        $responseCreate = $this->actingAs($this->operator)
            ->post(route('core.document.store'), [
                'client_id' => $this->client->id,
                'shipping_vendor_id' => $this->vendor->id,
                'type' => 'IMPORT',
                'bl_number' => 'MSKU9821739',
                'vessel_name' => 'MAERSK MC-KINNEY MOLLER',
                'voyage_number' => 'V.2610N',
                'port_of_loading' => 'CNSHA',
                'port_of_discharge' => 'IDTPP',
                'total_kontainer' => 1,
                'nomor_kontainer' => 'MSKU1234567',
                'total_kemasan' => 200,
                'jenis_kemasan' => 'CTN',
                'total_bruto' => 8500,
                'total_netto' => 8000,
                'total_cif' => 35000,
                'currency' => 'USD',
                'ndbm_kurs' => 16200,
                'bl_file' => $blFile,
                'invoice_file' => $invFile,
                'packing_list_file' => $plFile,
            ]);

        $responseCreate->assertRedirect();
        $shipment = Shipment::where('nomor_bl_awb', 'MSKU9821739')->first();
        $this->assertNotNull($shipment, 'Shipment BC 2.0 gagal dibuat di database');
        $this->assertEquals('IMPORT', $shipment->type);
        $this->assertEquals(9, $shipment->documents()->count(), '9 slot checklist dokumen wajib harus terinisialisasi');

        // Pastikan berkas inti tersimpan di dokumen
        $docBL = $shipment->documents()->where('jenis_dokumen', 'BL_AWB')->first();
        $docInv = $shipment->documents()->where('jenis_dokumen', 'INVOICE')->first();
        $docPL = $shipment->documents()->where('jenis_dokumen', 'PACKING_LIST')->first();

        $this->assertNotNull($docBL);
        $this->assertNotNull($docInv);
        $this->assertNotNull($docPL);

        // ------------------------------------------------------------------
        // TAHAP 2: Verifikasi Three-Way Match (Audit Berkas) & Pos Tarif HS Code
        // ------------------------------------------------------------------
        // Set berkas wajib menjadi VERIFIED
        $this->actingAs($this->operator)->post(route('core.document.verify-document', [$shipment->id, $docBL->id]));
        $this->actingAs($this->operator)->post(route('core.document.verify-document', [$shipment->id, $docInv->id]));
        $this->actingAs($this->operator)->post(route('core.document.verify-document', [$shipment->id, $docPL->id]));

        $shipment->refresh();
        $this->assertTrue($shipment->is_all_mandatory_documents_verified, 'Semua berkas wajib (BL, Invoice, PL) harus berstatus VERIFIED');

        // Pastikan minimal ada item barang untuk PIB
        if ($shipment->items()->count() === 0) {
            $shipment->items()->create([
                'nomor_urut' => 1,
                'uraian_barang' => 'STATIC CONVERTER POWER SUPPLY UNIT',
                'kode_hs' => '8504.40.90',
                'jumlah_kemasan' => 200,
                'jenis_kemasan' => 'CTN',
                'berat_bruto' => 8500,
                'berat_netto' => 8000,
                'nilai_fob' => 35000,
                'tarif_bm_persen' => 5.0,
                'tarif_ppn_persen' => 11.0,
                'tarif_pph_persen' => 2.5,
            ]);
        }

        // ------------------------------------------------------------------
        // TAHAP 3: Submit ke Host-to-Host CEISA 4.0 & Penerbitan Billing MPN G3
        // ------------------------------------------------------------------
        $responseCeisa = $this->actingAs($this->operator)
            ->post(route('core.document.submit-ceisa', $shipment->id));

        $responseCeisa->assertSessionHas('success');
        $shipment->refresh();

        $this->assertNotNull($shipment->ceisaDocument, 'CeisaDocument harus terhubung ke Shipment');
        $this->assertEquals('20', $shipment->ceisaDocument->jenis_dokumen, 'Jenis dokumen harus BC 2.0');
        $this->assertNotEmpty($shipment->ceisaDocument->raw_payload, 'Payload JSON CEISA 4.0 tidak boleh kosong');
        $this->assertNotNull($shipment->ceisaDocument->kode_billing, 'Kode Billing MPN G3 harus terbit');
        $this->assertEquals('BILLING_ISSUED', $shipment->status_tahap, 'Status shipment harus BILLING_ISSUED');

        // ------------------------------------------------------------------
        // TAHAP 4: Tim DO (Delivery Order Pelayaran)
        // ------------------------------------------------------------------
        // 4A. Halaman Tim DO memuat antrian shipment
        $responseDoIndex = $this->actingAs($this->operator)->get(route('core.do.index'));
        $responseDoIndex->assertStatus(200);
        $responseDoIndex->assertSee($shipment->shipment_number);

        // 4B. Tim DO mengirim email permintaan invoice THC/DO ke pelayaran
        $responseSendEmail = $this->actingAs($this->operator)
            ->post(route('core.do.send-email', $shipment->id), [
                'notes' => 'Mohon terbitkan invoice THC & Ocean Freight untuk B/L MSKU9821739.',
            ]);
        $responseSendEmail->assertSessionHas('success');
        $shipment->refresh();
        $this->assertEquals('EMAIL_SENT', $shipment->status_do);

        // 4C. Tim DO menginput nomor rilis DO pelayaran
        $responseReleaseDo = $this->actingAs($this->operator)
            ->post(route('core.do.release-do', $shipment->id), [
                'do_number' => 'DO-MSK-202610-8812',
                'do_expired_at' => now()->addDays(7)->format('Y-m-d'),
            ]);
        $responseReleaseDo->assertSessionHas('success');
        $shipment->refresh();

        $this->assertTrue($shipment->do_is_released, 'DO harus berstatus rilis');
        $this->assertEquals('DO-MSK-202610-8812', $shipment->nomor_do);
        $this->assertEquals('DO_RELEASED', $shipment->status_tahap);

        // ------------------------------------------------------------------
        // TAHAP 5: Tim Traffic & Lapangan Pelabuhan (Penjaluran, Gate Out, POD)
        // ------------------------------------------------------------------
        // 5A. Halaman Tim Traffic memuat kargo yang siap dikeluarkan
        $responseTrafficIndex = $this->actingAs($this->operator)->get(route('core.traffic.index'));
        $responseTrafficIndex->assertStatus(200);
        $responseTrafficIndex->assertSee($shipment->shipment_number);

        // 5B. Update respon penjaluran pabean (Jalur Hijau / SPPB)
        $responseCustoms = $this->actingAs($this->operator)
            ->post(route('core.traffic.customs-status', $shipment->id), [
                'channel' => 'HIJAU',
                'notes' => 'SPPB resmi terbit dari DJBC. Kargo siap dikeluarkan dari TPS.',
            ]);
        $responseCustoms->assertSessionHas('success');
        $shipment->refresh();
        $this->assertEquals('HIJAU', $shipment->customs_channel);
        $this->assertEquals('TRAFFIC_SPPB', $shipment->status_tahap);

        // 5C. Gate Out TPS Pelabuhan (Truk & Supir Ekspedisi)
        $responseGateOut = $this->actingAs($this->operator)
            ->post(route('core.traffic.gate-out', $shipment->id), [
                'truck_plate_number' => 'B 9281 UEM',
                'driver_name' => 'Joko Susilo',
                'surat_jalan_number' => 'SJ-ATS-202610-0081',
            ]);
        $responseGateOut->assertSessionHas('success');
        $shipment->refresh();
        $this->assertEquals('TRAFFIC_GATE_OUT', $shipment->status_tahap);

        // 5D. Konfirmasi Penerimaan di Gudang Klien (Proof of Delivery / POD)
        $podFile = UploadedFile::fake()->create('POD_TTD_BASAH.jpg', 400, 'image/jpeg');
        $responsePod = $this->actingAs($this->operator)
            ->post(route('core.traffic.deliver-pod', $shipment->id), [
                'receiver_name' => 'Bpk. Bambang Pamungkas (Kepala Gudang Klien)',
                'pod_file' => $podFile,
            ]);
        $responsePod->assertSessionHas('success');
        $shipment->refresh();

        // ------------------------------------------------------------------
        // VALIDASI AKHIR: Kargo Sukses Terkirim & Invoice Penagihan Otomatis Terbit
        // ------------------------------------------------------------------
        $this->assertEquals('INVOICED_P19', $shipment->status_tahap);
        $this->assertNotNull($shipment->pod_signed_at, 'Waktu serah terima POD harus tercatat');

        // Pastikan invoice penagihan terbit di modul finance
        $invoice = ClientInvoice::where('shipment_id', $shipment->id)->first();
        $this->assertNotNull($invoice, 'Invoice penagihan klien (P19) harus otomatis dibuat di Finance');
        $this->assertGreaterThan(0, (float) $invoice->total_amount, 'Nilai tagihan invoice harus lebih dari 0');
    }

    /**
     * Test validasi: Gate Out DITOLAK jika DO belum dirilis oleh pelayaran
     */
    public function test_gate_out_is_rejected_if_do_is_not_released(): void
    {
        $shipment = Shipment::create([
            'project_no' => Shipment::generateProjectNo(),
            'client_id' => $this->client->id,
            'shipping_vendor_id' => $this->vendor->id,
            'jenis_layanan' => 'IMPORT_BC20',
            'nomor_bl_awb' => 'BL-UNRELEASED-001',
            'status_tahap' => 'BILLING_ISSUED',
            'status_do' => 'PENDING_REQUEST',
            'nomor_do' => null, // DO belum rilis
        ]);

        $response = $this->actingAs($this->operator)
            ->post(route('core.traffic.gate-out', $shipment->id), [
                'truck_plate_number' => 'B 1234 XYZ',
                'driver_name' => 'Supir Belum Sah',
                'surat_jalan_number' => 'SJ-FAIL-001',
            ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('DO belum dirilis', session('error'));
        $shipment->refresh();
        $this->assertNotEquals('TRAFFIC_GATE_OUT', $shipment->status_tahap);
    }

    /**
     * Test validasi: Submit CEISA DITOLAK jika 3 berkas wajib belum diverifikasi
     */
    public function test_submit_ceisa_is_rejected_if_documents_not_verified(): void
    {
        $shipment = Shipment::create([
            'project_no' => Shipment::generateProjectNo(),
            'client_id' => $this->client->id,
            'shipping_vendor_id' => $this->vendor->id,
            'jenis_layanan' => 'IMPORT_BC20',
            'nomor_bl_awb' => 'BL-UNVERIFIED-001',
            'status_tahap' => 'DOC_COLLECTING',
        ]);

        // Buat 3 dokumen status MISSING
        $shipment->documents()->create(['jenis_dokumen' => 'INVOICE', 'status' => 'MISSING']);
        $shipment->documents()->create(['jenis_dokumen' => 'PACKING_LIST', 'status' => 'MISSING']);
        $shipment->documents()->create(['jenis_dokumen' => 'BL_AWB', 'status' => 'MISSING']);

        $response = $this->actingAs($this->operator)
            ->post(route('core.document.submit-ceisa', $shipment->id));

        $response->assertSessionHas('error');
        $this->assertStringContainsString('Dokumen wajib (Invoice, Packing List, B/L) belum 100% diverifikasi', session('error'));
    }
}
