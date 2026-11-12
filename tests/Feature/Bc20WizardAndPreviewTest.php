<?php

namespace Tests\Feature;

use App\Models\CeisaDocument;
use App\Models\Client;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Shipment;
use App\Models\User;
use App\Services\Ceisa\DocumentBC20Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Bc20WizardAndPreviewTest extends TestCase
{
    use RefreshDatabase;

    protected User $editorUser;
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $pView = Permission::firstOrCreate(['code' => 'document.view'], ['name' => 'View Document', 'module' => 'core']);
        $pCreate = Permission::firstOrCreate(['code' => 'document.create'], ['name' => 'Create Document', 'module' => 'core']);
        $pEdit = Permission::firstOrCreate(['code' => 'document.edit'], ['name' => 'Edit Document', 'module' => 'core']);

        $role = Role::firstOrCreate(['code' => 'R-DOC-EDITOR'], ['name' => 'Editor Dokumen']);
        $role->permissions()->syncWithoutDetaching([$pView->id, $pCreate->id, $pEdit->id]);

        $this->editorUser = User::factory()->create();
        $this->editorUser->roles()->attach($role->id);

        $this->client = Client::create([
            'code' => 'CL-WIZ-' . rand(100, 999),
            'name' => 'PT Danwin Utama Teknik',
            'npwp' => '0732770896451000',
            'address' => 'Ruko Bolsena Blok E No. 62 Tangerang',
            'nib' => '8120101980731',
        ]);
    }

    public function test_import_wizard_renders_9_stages_and_inline_document_preview(): void
    {
        $payload = DocumentBC20Service::buildPayload([
            'kode_kantor' => '040300',
            'ndbm_kurs' => 16135,
            'kode_valuta' => 'USD',
            'fob' => 5140,
            'freight' => 215,
            'asuransi' => 0,
            'bruto' => 6300,
            'netto' => 6000,
            'supplier_nama' => 'WEIFANG BEACON CHEMICAL BUILDING MATERIALS CO., LTD',
            'supplier_negara' => 'CN',
            'barang' => [
                [
                    'hs_code' => '38244000',
                    'uraian' => 'PCE EARLY STRENGTH TYPE',
                    'jumlah' => 4,
                    'satuan' => 'TNE',
                    'jumlah_kemasan' => 4,
                    'kemasan' => 'TK',
                    'netto' => 4000,
                    'fob' => 3460,
                    'tarif_bm' => 0,
                    'tarif_ppn' => 11,
                    'tarif_pph' => 2.5,
                ],
            ],
        ]);

        $ceisaDoc = CeisaDocument::create([
            'nomor_aju' => $payload['nomorAju'],
            'jenis_dokumen' => '20',
            'kode_kantor' => '040300',
            'nama_kantor' => 'KPU Bea dan Cukai Tipe A Tanjung Priok',
            'status' => 'DRAFT',
            'kode_valuta' => 'USD',
            'ndbm_kurs' => 16135,
            'total_nilai' => 5355,
            'total_nilai_idr' => 86402925,
            'total_bruto' => 6300,
            'total_netto' => 6000,
            'total_kemasan' => 4,
            'total_kontainer' => 1,
            'total_barang' => 1,
            'raw_payload' => $payload,
        ]);

        $shipment = $ceisaDoc->ensureShipment();
        $pdfFile = UploadedFile::fake()->create('INVOICE_BC2026.pdf', 120, 'application/pdf');
        $storedPath = $pdfFile->storeAs('shipment_docs/' . $shipment->id, 'INVOICE_BC2026.pdf', 'public');

        $shipDoc = $shipment->documents()->create([
            'jenis_dokumen' => 'INVOICE',
            'nama_file' => 'INVOICE_BC2026.pdf',
            'file_path' => $storedPath,
            'file_size_kb' => 120,
            'status' => 'UPLOADED',
            'is_current' => true,
            'version' => 1,
        ]);

        // 1. Buka halaman edit wizard BC 2.0 -> pastikan 9 tahap & URL preview muncul
        $res = $this->actingAs($this->editorUser)->get(route('bc20.edit', $ceisaDoc->id));
        $res->assertOk()
            ->assertSee('Kemasan & Peti Kemas', false)
            ->assertSee('Generate Pungutan')
            ->assertSee('Informasi Penandatanganan')
            ->assertSee('INVOICE_BC2026.pdf')
            ->assertSee($shipDoc->id);

        // 2. Uji endpoint inline preview mengembalikan Content-Disposition: inline
        $previewRes = $this->actingAs($this->editorUser)->get(route('core.document.preview-doc', [$shipment->id, $shipDoc->id]));
        $previewRes->assertOk();
        $this->assertStringContainsString('inline;', (string) $previewRes->headers->get('Content-Disposition'));
    }

    public function test_import_wizard_saves_all_9_stages_and_uploads_pdf_for_live_preview(): void
    {
        $payload = DocumentBC20Service::buildPayload([
            'kode_kantor' => '040300',
            'ndbm_kurs' => 16000,
            'kode_valuta' => 'USD',
            'fob' => 3000,
            'freight' => 200,
            'asuransi' => 50,
            'bruto' => 2500,
            'netto' => 2300,
            'barang' => [
                ['hs_code' => '38244000', 'uraian' => 'PCE WR-2', 'fob' => 3000, 'tarif_bm' => 5, 'tarif_ppn' => 11, 'tarif_pph' => 2.5],
            ],
        ]);

        $ceisaDoc = CeisaDocument::create([
            'nomor_aju' => $payload['nomorAju'],
            'jenis_dokumen' => '20',
            'kode_kantor' => '040300',
            'nama_kantor' => 'KPU Tanjung Priok',
            'status' => 'DRAFT',
            'kode_valuta' => 'USD',
            'ndbm_kurs' => 16000,
            'total_nilai' => 3250,
            'total_nilai_idr' => 52000000,
            'total_bruto' => 2500,
            'total_netto' => 2300,
            'total_kemasan' => 6,
            'total_kontainer' => 1,
            'total_barang' => 1,
            'raw_payload' => $payload,
        ]);

        // 1. Unggah dokumen baru langsung dari tab Dokumen wizard
        $newPdf = UploadedFile::fake()->create('COO_FORME_001.pdf', 90, 'application/pdf');
        $uploadRes = $this->actingAs($this->editorUser)->postJson(route('bc20.upload-document', $ceisaDoc->id), [
            'file' => $newPdf,
            'kode_dokumen' => '861',
            'nomor_dokumen' => 'CCPIT450001',
            'tanggal_dokumen' => '2026-11-12',
            'purpose' => 'dokumen',
        ]);

        $uploadRes->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('doc_entry.kodeDokumen', '861')
            ->assertJsonPath('preview_file.nama_file', 'COO_FORME_001.pdf');

        $uploadedDocId = $uploadRes->json('preview_file.id');

        // 2. Simpan payload 9 tahap via JSON PUT
        $wizardPayload = [
            'header' => [
                'pelabuhan_bongkar' => 'IDTPP',
                'kode_kantor' => '040300',
                'kode_jenis_prosedur' => '1',
                'kode_jenis_impor' => '1',
                'kode_cara_bayar' => '1',
            ],
            'entitas' => [
                'importir_jenis_identitas' => '6',
                'importir_npwp' => '0732770896451000',
                'importir_nama' => 'PT DANWIN UTAMA TEKNIK',
                'importir_status' => 'LAINNYA',
                'importir_api' => '01',
                'importir_nib' => '8120101980731',
                'importir_alamat' => 'Tangerang',
                'pemilik_nama' => 'PT DANWIN UTAMA TEKNIK',
                'pemusatan_nama' => 'PT DANWIN UTAMA TEKNIK',
                'supplier_nama' => 'WEIFANG BEACON CO LTD',
                'supplier_negara' => 'CN',
                'supplier_alamat' => 'Shandong, China',
                'seller_nama' => 'WEIFANG BEACON CO LTD',
                'seller_negara' => 'CN',
                'seller_alamat' => 'Shandong, China',
            ],
            'dokumen' => [
                ['seriDokumen' => 1, 'kodeDokumen' => '380', 'nomorDokumen' => 'BC20260907IDN', 'tanggalDokumen' => '2026-11-04'],
                ['seriDokumen' => 2, 'kodeDokumen' => '861', 'nomorDokumen' => 'CCPIT450001', 'tanggalDokumen' => '2026-11-12', 'shipmentDocumentId' => $uploadedDocId],
            ],
            'pengangkut' => [
                'kode_tutup_pu' => '11',
                'nomor_bc11' => '009911',
                'tanggal_bc11' => '2026-11-12',
                'pos_bc11' => '0005',
                'subpos_bc11' => '0001',
                'cara_angkut' => '1',
                'nama_sarana_pengangkut' => 'HONG TAI 658',
                'nomor_voyage' => '009S',
                'bendera_kapal' => 'CN',
                'tanggal_tiba' => '2026-11-14',
                'pelabuhan_muat' => 'CNTAO',
                'pelabuhan_bongkar' => 'IDTPP',
                'kode_tps' => 'PCSA',
            ],
            'kemasan' => [
                ['seriKemasan' => 1, 'jumlahKemasan' => 6, 'kodeJenisKemasan' => 'TK', 'merkKemasan' => '-'],
            ],
            'kontainer' => [
                ['seriKontainer' => 1, 'nomorKontainer' => 'FFAU2895679', 'kodeUkuranKontainer' => '40', 'kodeJenisKontainer' => '8', 'kodeTipeKontainer' => '1'],
            ],
            'transaksi' => [
                'kode_valuta' => 'USD',
                'ndbm_kurs' => 16135,
                'kode_jenis_nilai' => 'KMD',
                'kode_incoterm' => 'FOB',
                'fob' => 5140,
                'biaya_tambahan' => 0,
                'biaya_pengurang' => 0,
                'freight' => 215,
                'kode_asuransi' => 'LN',
                'asuransi' => 0,
                'vd' => 0,
                'bruto' => 6300,
                'netto' => 6000,
            ],
            'barang' => [
                [
                    'group' => 1,
                    'hs_code' => '38244000',
                    'pernyataan_lartas' => 'NON',
                    'uraian' => 'PCE EARLY STRENGTH TYPE',
                    'merk' => 'TANPA MEREK',
                    'tipe' => 'TANPA TIPE',
                    'negara_asal' => 'CN',
                    'jumlah' => 4,
                    'satuan' => 'TNE',
                    'jumlah_kemasan' => 4,
                    'kemasan' => 'TK',
                    'netto' => 4000,
                    'bruto' => 4200,
                    'fob' => 3460,
                    'tarif_bm' => 0,
                    'tarif_ppn' => 11,
                    'tarif_pph' => 2.5,
                    'kode_fasilitas_bm' => '1',
                    'kode_fasilitas_ppn' => '1',
                    'kode_fasilitas_pph' => '1',
                ],
                [
                    'group' => 1,
                    'hs_code' => '38244000',
                    'pernyataan_lartas' => 'NON',
                    'uraian' => 'PCE WR-2',
                    'merk' => 'TANPA MEREK',
                    'tipe' => 'TANPA TIPE',
                    'negara_asal' => 'CN',
                    'jumlah' => 2,
                    'satuan' => 'TNE',
                    'jumlah_kemasan' => 2,
                    'kemasan' => 'TK',
                    'netto' => 2000,
                    'bruto' => 2100,
                    'fob' => 1680,
                    'tarif_bm' => 0,
                    'tarif_ppn' => 11,
                    'tarif_pph' => 2.5,
                    'kode_fasilitas_bm' => '1',
                    'kode_fasilitas_ppn' => '1',
                    'kode_fasilitas_pph' => '1',
                ],
            ],
            'pernyataan' => [
                'nama_ttd' => 'JOHANA MEGAWATI',
                'tanggal_ttd' => '2026-11-12',
                'kota_ttd' => 'JAKARTA',
                'jabatan_ttd' => 'DIREKTUR',
            ],
        ];

        $saveRes = $this->actingAs($this->editorUser)->putJson(route('bc20.update', $ceisaDoc->id), [
            'wizard_payload' => $wizardPayload,
        ]);

        $saveRes->assertOk()->assertJsonPath('success', true);

        $ceisaDoc->refresh();
        $savedPayload = $ceisaDoc->raw_payload;

        $this->assertEquals('HONG TAI 658', $savedPayload['pengangkut'][0]['namaPengangkut']);
        $this->assertEquals('FFAU2895679', $savedPayload['kontainer'][0]['nomorKontainer']);
        $this->assertEquals('TK', $savedPayload['kemasan'][0]['kodeJenisKemasan']);
        $this->assertEquals('JOHANA MEGAWATI', $savedPayload['namaTtd']);
        $this->assertEquals(2, count($savedPayload['barang']));
        $this->assertGreaterThan(0, $savedPayload['pungutan']['TOTAL']['dibayar']);
    }
}

