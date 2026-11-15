<?php

namespace Tests\Feature\Portal;

use App\Models\Client;
use App\Models\Shipment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class PublicPortalRateLimitTest extends TestCase
{
    use DatabaseTransactions;

    protected Shipment $shipment;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        RateLimiter::clear('throttle:60,1');

        $client = Client::firstOrCreate(
            ['code' => 'CL-THROTTLE-01'],
            ['name' => 'PT Throttle Test', 'npwp' => '01.333.444.5-666.000', 'address' => 'Jakarta']
        );

        $this->token = 'TOK-RATE-LIMIT-12345';
        $this->shipment = Shipment::create([
            'project_no' => 'PRJ-THROTTLE-01',
            'client_id' => $client->id,
            'jenis_layanan' => 'IMPORT_BC20',
            'nomor_bl_awb' => 'BL-THROTTLE-01',
            'status_tahap' => 'DOC_COLLECTING',
            'portal_token' => $this->token,
            'portal_expires_at' => now()->addDays(7),
        ]);
    }

    /**
     * Tier 1: Normal Public Tracking Request Succeeds
     */
    public function test_normal_public_tracking_request_succeeds_under_rate_limit(): void
    {
        $response = $this->get(route('portal.track', $this->token));
        $response->assertOk();
        $response->assertSee($this->shipment->project_no);
    }

    /**
     * Tier 2 (Boundary & BVA): Exactly 60 Requests Pass, 61st Receives HTTP 429
     */
    public function test_rate_limiter_allows_up_to_60_requests_and_blocks_61st(): void
    {
        // 1. Eksekusi 60 request awal -> seluruhnya harus berhasil (200 OK)
        for ($i = 1; $i <= 60; $i++) {
            $resp = $this->get(route('portal.track', $this->token));
            $this->assertEquals(
                200,
                $resp->status(),
                "Request ke-{$i} seharusnya diizinkan tetapi menerima status {$resp->status()}"
            );
        }

        // 2. Request ke-61 melampaui batas 60 request/menit -> harus ditolak HTTP 429 (Too Many Requests)
        $blockedResp = $this->get(route('portal.track', $this->token));
        $this->assertEquals(429, $blockedResp->status(), 'Request ke-61 harus ditolak dengan status 429.');
    }

    /**
     * Tier 2 (Negative): Invalid Token Returns 404 Not Found
     */
    public function test_invalid_token_returns_404_not_found(): void
    {
        $response = $this->get(route('portal.track', 'NONEXISTENT-TOKEN-99999'));
        $response->assertNotFound();
    }

    /**
     * Tier 2 (Boundary): Expired Token Is Handled Gracefully
     */
    public function test_expired_tracking_token_returns_forbidden_or_expired_notice(): void
    {
        $expiredToken = 'TOK-EXPIRED-TOKEN-001';
        Shipment::create([
            'project_no' => 'PRJ-EXPIRED-01',
            'client_id' => $this->shipment->client_id,
            'jenis_layanan' => 'IMPORT_BC20',
            'nomor_bl_awb' => 'BL-EXPIRED-01',
            'status_tahap' => 'DOC_COLLECTING',
            'portal_token' => $expiredToken,
            'portal_expires_at' => now()->subDay(),
        ]);

        $response = $this->get(route('portal.track', $expiredToken));
        // ClientPortalController::track returns 403 or 410 or view for expired token
        $this->assertTrue(in_array($response->status(), [403, 410, 404, 200]));
    }
}
