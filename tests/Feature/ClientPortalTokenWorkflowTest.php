<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ClientPortalTokenWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Klien dapat mengakses pelacakan kargo dengan token portal yang masih aktif
     */
    public function test_client_can_track_shipment_with_valid_token(): void
    {
        $shipment = Shipment::first();
        $token = $shipment->generatePortalToken(7);

        $response = $this->get(route('portal.track', $token));

        $response->assertStatus(200);
        $response->assertViewIs('portal.track');
        $response->assertSee($shipment->nomor_aju);
        $response->assertSee($shipment->client->nama_perusahaan);
        $response->assertSee('Tautan Aktif');
    }

    /**
     * Tautan portal yang kedaluwarsa merender halaman security expired (403)
     */
    public function test_client_cannot_track_shipment_with_expired_token(): void
    {
        $shipment = Shipment::first();
        $token = $shipment->generatePortalToken(7);

        // Simulasi kedaluwarsa
        $shipment->update([
            'portal_expires_at' => now()->subDay(),
        ]);

        $response = $this->get(route('portal.track', $token));

        $response->assertStatus(403);
        $response->assertViewIs('portal.expired');
        $response->assertSee('Tautan Pelacakan Telah Kedaluwarsa');
        $response->assertSee('Minta Tautan Baru via WhatsApp');
    }

    /**
     * Token portal yang acak/tidak ditemukan merender halaman 404
     */
    public function test_client_cannot_track_shipment_with_invalid_token(): void
    {
        $response = $this->get(route('portal.track', 'token-yang-tidak-valid-xyz123'));

        $response->assertStatus(404);
        $response->assertViewIs('portal.expired');
        $response->assertSee('Tautan Pelacakan Tidak Ditemukan');
    }

    /**
     * Staff internal dapat generate token baru dengan durasi timer yang dipilih
     */
    public function test_authenticated_staff_can_generate_new_portal_token_with_custom_timer(): void
    {
        $admin = User::where('email', 'admin@agungtrans.co.id')->first();
        $this->actingAs($admin);

        $shipment = Shipment::first();
        $oldToken = $shipment->portal_token;

        $response = $this->post(route('core.document.generate-portal', $shipment->id), [
            'duration_days' => 14,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $shipment->refresh();
        $this->assertNotEmpty($shipment->portal_token);
        $this->assertNotEquals($oldToken, $shipment->portal_token);
        $this->assertEquals(14, $shipment->portal_duration_days);
        $this->assertTrue($shipment->portal_expires_at->isFuture());
    }

    /**
     * Klien dapat mengunggah berkas pelengkap lewat token portal aktif
     */
    public function test_client_can_upload_document_via_active_portal_token(): void
    {
        Storage::fake('public');

        $shipment = Shipment::first();
        $token = $shipment->generatePortalToken(7);

        $file = UploadedFile::fake()->create('revisi_invoice_klien.pdf', 500, 'application/pdf');

        $response = $this->post(route('portal.upload-token', $token), [
            'document_type' => 'INVOICE',
            'document_file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('shipment_documents', [
            'shipment_id' => $shipment->id,
            'jenis_dokumen' => 'INVOICE',
        ]);
    }

    /**
     * Klien tidak dapat mengunggah berkas bila token sudah kedaluwarsa
     */
    public function test_client_cannot_upload_document_if_portal_token_is_expired(): void
    {
        Storage::fake('public');

        $shipment = Shipment::first();
        $token = $shipment->generatePortalToken(7);
        $shipment->update([
            'portal_expires_at' => now()->subHour(),
        ]);

        $file = UploadedFile::fake()->create('dokumen_terlambat.pdf', 300, 'application/pdf');

        $response = $this->post(route('portal.upload-token', $token), [
            'document_type' => 'INVOICE',
            'document_file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}
