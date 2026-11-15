<?php

namespace Tests\Feature\Hrm;

use App\Models\AttendanceLog;
use App\Models\BiometricConsent;
use App\Models\Employee;
use App\Models\Role;
use App\Models\Shipment;
use App\Models\User;
use App\Models\WorkLocation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

class BiometricComplianceFeatureTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected Employee $employee;
    protected WorkLocation $location;

    protected function setUp(): void
    {
        parent::setUp();

        // Siapkan lokasi kerja aktif untuk geofence
        $this->location = WorkLocation::firstOrCreate(
            ['name' => 'Kantor Pusat Mega Kuningan'],
            [
                'lat' => -6.2297,
                'lng' => 106.8294,
                'radius_m' => 500,
                'valid_from' => '2024-01-01',
            ]
        );

        // Buat user & employee untuk pengujian
        $this->user = User::create([
            'name' => 'Test Portal User',
            'email' => 'test.portal.' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $this->employee = Employee::create([
            'nik' => 'TST-' . rand(10000, 99999),
            'user_id' => $this->user->id,
            'full_name' => 'Test Portal Employee',
            'position' => 'Staff Operasional',
            'team' => 'OPERATIONS',
            'start_date' => now()->toDateString(),
            'status' => 'ACTIVE',
            'base_salary' => 7500000,
        ]);

        $this->user->update(['employee_id' => $this->employee->id]);
    }

    /**
     * FIX-08: Clock-In ditolak jika belum memiliki biometric consent UU PDP aktif.
     */
    public function test_clock_in_rejected_without_biometric_consent(): void
    {
        // Pastikan tidak ada consent
        BiometricConsent::where('employee_id', $this->employee->id)->delete();

        // 1. Uji JSON request
        $responseJson = $this->actingAs($this->user)
            ->postJson(route('hrm.portal.clock-in'), [
                'lat' => -6.2297,
                'lng' => 106.8294,
            ]);

        $responseJson->assertStatus(403);
        $responseJson->assertJson([
            'success' => false,
            'require_consent' => true,
        ]);

        // 2. Uji Web request
        $responseWeb = $this->actingAs($this->user)
            ->post(route('hrm.portal.clock-in'), [
                'lat' => -6.2297,
                'lng' => 106.8294,
            ]);

        $responseWeb->assertRedirect(route('hrm.portal', ['tab' => 'attendance']));
        $responseWeb->assertSessionHas('error');
        $responseWeb->assertSessionHas('require_biometric_consent', true);

        // Verifikasi tidak ada log yang tersimpan
        $this->assertEquals(0, AttendanceLog::where('employee_id', $this->employee->id)->count());
    }

    /**
     * FIX-08: Clock-Out ditolak jika belum memiliki biometric consent UU PDP aktif.
     */
    public function test_clock_out_rejected_without_biometric_consent(): void
    {
        BiometricConsent::where('employee_id', $this->employee->id)->delete();

        // 1. Uji JSON request
        $responseJson = $this->actingAs($this->user)
            ->postJson(route('hrm.portal.clock-out'), [
                'lat' => -6.2297,
                'lng' => 106.8294,
            ]);

        $responseJson->assertStatus(403);
        $responseJson->assertJson([
            'success' => false,
            'require_consent' => true,
        ]);

        // 2. Uji Web request
        $responseWeb = $this->actingAs($this->user)
            ->post(route('hrm.portal.clock-out'), [
                'lat' => -6.2297,
                'lng' => 106.8294,
            ]);

        $responseWeb->assertRedirect(route('hrm.portal', ['tab' => 'attendance']));
        $responseWeb->assertSessionHas('error');
        $responseWeb->assertSessionHas('require_biometric_consent', true);
    }

    /**
     * FIX-08: Clock-In dan Clock-Out berhasil jika biometric consent sah.
     */
    public function test_clock_in_and_clock_out_succeed_with_valid_consent(): void
    {
        // Berikan consent yang sah
        BiometricConsent::create([
            'employee_id' => $this->employee->id,
            'consent_at' => now(),
            'retention_until' => now()->addYears(2)->toDateString(),
        ]);

        // Clock In
        $inResponse = $this->actingAs($this->user)
            ->postJson(route('hrm.portal.clock-in'), [
                'lat' => -6.2297,
                'lng' => 106.8294,
            ]);

        $inResponse->assertStatus(200);
        $inResponse->assertJson(['success' => true]);

        $this->assertDatabaseHas('attendance_logs', [
            'employee_id' => $this->employee->id,
            'kind' => 'CLOCK_IN',
        ]);

        // Clock Out
        $outResponse = $this->actingAs($this->user)
            ->post(route('hrm.portal.clock-out'), [
                'lat' => -6.2297,
                'lng' => 106.8294,
            ]);

        $outResponse->assertRedirect(route('hrm.portal', ['tab' => 'attendance']));
        $outResponse->assertSessionHas('success');

        $this->assertDatabaseHas('attendance_logs', [
            'employee_id' => $this->employee->id,
            'kind' => 'CLOCK_OUT',
        ]);
    }

    /**
     * FIX-06: Foto selfie disimpan ke disk storage privat dan tidak ada di webroot public.
     */
    public function test_attendance_photo_stored_in_private_disk(): void
    {
        BiometricConsent::create([
            'employee_id' => $this->employee->id,
            'consent_at' => now(),
            'retention_until' => now()->addYears(2)->toDateString(),
        ]);

        // Fake 1x1 base64 JPEG
        $fakeBase64 = 'data:image/jpeg;base64,' . base64_encode(str_repeat('A', 100));

        $response = $this->actingAs($this->user)
            ->postJson(route('hrm.portal.clock-in'), [
                'lat' => -6.2297,
                'lng' => 106.8294,
                'face_photo' => $fakeBase64,
            ]);

        $response->assertStatus(200);

        $log = AttendanceLog::where('employee_id', $this->employee->id)
            ->where('kind', 'CLOCK_IN')
            ->latest()
            ->first();

        $this->assertNotNull($log);
        $this->assertNotEmpty($log->photo_url);

        $filename = basename($log->photo_url);
        $privatePath = storage_path('app/private/attendances/' . $filename);
        $publicPath = public_path('uploads/attendances/' . $filename);

        // Pastikan berkas tersimpan di storage/app/private/attendances/
        $this->assertTrue(File::exists($privatePath) || Storage::disk('private')->exists('attendances/' . $filename));

        // Pastikan TIDAK ADA di folder public webroot
        $this->assertFalse(File::exists($publicPath));

        // Bersihkan berkas setelah pengujian
        if (File::exists($privatePath)) {
            File::delete($privatePath);
        }
    }

    /**
     * FIX-06: Otorisasi streaming foto presensi privat (UU PDP No. 27/2022).
     */
    public function test_private_attendance_streaming_route_authorization(): void
    {
        // Buat berkas dummy di storage private
        $filename = 'att_test_' . time() . '.jpg';
        $destDir = storage_path('app/private/attendances');
        if (!File::exists($destDir)) {
            File::makeDirectory($destDir, 0750, true);
        }
        File::put($destDir . '/' . $filename, 'FAKE_JPEG_BINARY_DATA');

        $log = AttendanceLog::create([
            'employee_id' => $this->employee->id,
            'kind' => 'CLOCK_IN',
            'at' => now(),
            'lat' => -6.2297,
            'lng' => 106.8294,
            'valid' => true,
            'photo_url' => $filename,
        ]);

        // 1. Pemilik (karyawan bersangkutan) diizinkan mengakses
        $ownerResp = $this->actingAs($this->user)->get(route('private.attendance-photo', $log->id));
        $ownerResp->assertStatus(200);
        $cacheControl = $ownerResp->headers->get('Cache-Control');
        $this->assertStringContainsString('private', $cacheControl);
        $this->assertStringContainsString('no-cache', $cacheControl);
        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('must-revalidate', $cacheControl);

        // Uji alias route
        $aliasResp = $this->actingAs($this->user)->get(route('portal.attendances.photo', $log->id));
        $aliasResp->assertStatus(200);

        // 2. Karyawan lain TANPA hak HR ditolak (403)
        $otherUser = User::create([
            'name' => 'Other Employee User',
            'email' => 'other.' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $otherEmployee = Employee::create([
            'nik' => 'OTH-' . rand(10000, 99999),
            'user_id' => $otherUser->id,
            'full_name' => 'Other Employee',
            'position' => 'Staff',
            'team' => 'TRAFFIC',
            'start_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);
        $otherUser->update(['employee_id' => $otherEmployee->id]);

        $forbiddenResp = $this->actingAs($otherUser)->get(route('private.attendance-photo', $log->id));
        $forbiddenResp->assertStatus(403);

        // 3. User HR dengan hak hrm.manage diizinkan
        $hrRole = Role::firstOrCreate(['code' => 'R-HR'], ['name' => 'HR Manager']);
        $hrUser = User::create([
            'name' => 'HR Admin User',
            'email' => 'hr.admin.' . Str::random(8) . '@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $hrUser->roles()->attach($hrRole->id);

        $hrResp = $this->actingAs($hrUser)->get(route('private.attendance-photo', $log->id));
        $hrResp->assertStatus(200);

        // 4. Guest tanpa signed URL ditolak (403)
        auth()->logout();
        $guestResp = $this->get(route('private.attendance-photo', $log->id));
        $this->assertTrue(in_array($guestResp->status(), [302, 401, 403]));

        // 5. Guest dengan Temporary Signed URL diizinkan
        $signedUrl = URL::temporarySignedRoute('private.attendance-photo', now()->addMinutes(10), ['attendanceLogId' => $log->id]);
        $signedResp = $this->get($signedUrl);
        $signedResp->assertStatus(200);

        // Bersihkan berkas dummy
        if (File::exists($destDir . '/' . $filename)) {
            File::delete($destDir . '/' . $filename);
        }
    }

    /**
     * FIX-09: Perintah artisan biometric:purge-expired membersihkan foto > 2 tahun dan me-masking score biometrik.
     */
    public function test_purge_expired_biometrics_command(): void
    {
        // 1. Buat foto kedaluwarsa 3 tahun lalu
        $oldFilename = 'att_expired_' . time() . '.jpg';
        $destDir = storage_path('app/private/attendances');
        if (!File::exists($destDir)) {
            File::makeDirectory($destDir, 0750, true);
        }
        File::put($destDir . '/' . $oldFilename, 'EXPIRED_FACE_PHOTO');

        $oldLog = AttendanceLog::create([
            'employee_id' => $this->employee->id,
            'kind' => 'CLOCK_IN',
            'at' => now()->subYears(3),
            'lat' => -6.2297,
            'lng' => 106.8294,
            'face_match' => 0.88,
            'valid' => true,
            'photo_url' => $oldFilename,
        ]);

        // 2. Buat foto aktif kemarin
        $recentFilename = 'att_recent_' . time() . '.jpg';
        File::put($destDir . '/' . $recentFilename, 'RECENT_FACE_PHOTO');

        $recentLog = AttendanceLog::create([
            'employee_id' => $this->employee->id,
            'kind' => 'CLOCK_IN',
            'at' => now()->subDay(),
            'lat' => -6.2297,
            'lng' => 106.8294,
            'face_match' => 0.91,
            'valid' => true,
            'photo_url' => $recentFilename,
        ]);

        // 3. Pegawai dengan consent kedaluwarsa
        $expiredConsentEmp = Employee::create([
            'nik' => 'EXP-' . rand(10000, 99999),
            'full_name' => 'Expired Consent Employee',
            'position' => 'Mantan Staf',
            'team' => 'NONE',
            'start_date' => now()->subYears(4)->toDateString(),
            'status' => 'INACTIVE',
            'face_data' => 'BIO_V2_ENCRYPTED_TEMPLATE',
        ]);
        BiometricConsent::create([
            'employee_id' => $expiredConsentEmp->id,
            'consent_at' => now()->subYears(3),
            'retention_until' => now()->subDays(10)->toDateString(),
        ]);

        // Uji DRY RUN: Tidak boleh ada berkas atau DB yang dihapus
        Artisan::call('biometric:purge-expired', ['--dry-run' => true]);
        $this->assertTrue(File::exists($destDir . '/' . $oldFilename));
        $this->assertEquals($oldFilename, $oldLog->fresh()->photo_url);
        $this->assertNotNull($oldLog->fresh()->face_match);
        $this->assertNotNull($expiredConsentEmp->fresh()->face_data);

        // Uji LIVE PURGE
        Artisan::call('biometric:purge-expired');

        // Verifikasi berkas lama terhapus dan dimasking
        $this->assertFalse(File::exists($destDir . '/' . $oldFilename));
        $this->assertNull($oldLog->fresh()->photo_url);
        $this->assertNull($oldLog->fresh()->face_match);
        $this->assertStringContainsString('Foto & Data Biometrik Dimusnahkan', $oldLog->fresh()->notes);

        // Verifikasi berkas aktif TETAP UTUH
        $this->assertTrue(File::exists($destDir . '/' . $recentFilename));
        $this->assertEquals($recentFilename, $recentLog->fresh()->photo_url);

        // Verifikasi template pegawai kedaluwarsa dibersihkan
        $this->assertNull($expiredConsentEmp->fresh()->face_data);

        // Bersihkan berkas aktif
        if (File::exists($destDir . '/' . $recentFilename)) {
            File::delete($destDir . '/' . $recentFilename);
        }
    }

    /**
     * FIX-28: Public tracking route rate limited pada 60 request per menit.
     */
    public function test_portal_tracking_rate_limiting_enforced(): void
    {
        $token = 'test_token_' . Str::random(32);

        // 60 requests diizinkan (< 429)
        for ($i = 1; $i <= 60; $i++) {
            $response = $this->get(route('portal.track', $token));
            $this->assertNotEquals(
                429,
                $response->status(),
                "Request ke-{$i} tidak boleh diblokir oleh rate limiter."
            );
        }

        // Request ke-61 wajib diblokir dengan HTTP 429 Too Many Requests
        $blockedResponse = $this->get(route('portal.track', $token));
        $this->assertEquals(429, $blockedResponse->status(), 'Request ke-61 wajib ditolak dengan HTTP 429.');
    }
}
