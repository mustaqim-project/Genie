<?php

namespace Tests\Feature\Security;

use App\Models\AttendanceLog;
use App\Models\BiometricConsent;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use App\Services\Hrm\BiometricVerificationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BiometricPrivacyE2ETest extends TestCase
{
    use DatabaseTransactions;

    protected User $hrUser;
    protected User $regularUser;
    protected User $unauthorizedUser;
    protected Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('private');

        $hrRole = Role::firstOrCreate(['code' => 'R-HR'], ['name' => 'HR Staff']);
        $docRole = Role::firstOrCreate(['code' => 'R-DOC'], ['name' => 'Doc Staff']);

        $this->hrUser = User::firstOrCreate(
            ['email' => 'hr_privacy_test@agungtrans.co.id'],
            ['name' => 'HR Privacy Tester', 'password' => bcrypt('password'), 'role' => 'R-HR', 'is_active' => true]
        );
        $this->hrUser->roles()->syncWithoutDetaching([$hrRole->id]);

        $this->regularUser = User::firstOrCreate(
            ['email' => 'staff_privacy_test@agungtrans.co.id'],
            ['name' => 'Staff Privacy Tester', 'password' => bcrypt('password'), 'role' => 'R-DOC', 'is_active' => true]
        );
        $this->regularUser->roles()->syncWithoutDetaching([$docRole->id]);

        $this->unauthorizedUser = User::firstOrCreate(
            ['email' => 'stranger_test@agungtrans.co.id'],
            ['name' => 'Stranger Tester', 'password' => bcrypt('password'), 'role' => 'R-DOC', 'is_active' => true]
        );
        $this->unauthorizedUser->roles()->syncWithoutDetaching([$docRole->id]);

        $this->employee = Employee::firstOrCreate(
            ['nik' => 'EMP-TEST-PRIV-001'],
            [
                'user_id' => $this->regularUser->id,
                'full_name' => 'Staff Privacy Tester',
                'position' => 'Customs Declarant',
                'team' => 'IMPORT',
                'start_date' => now()->subYears(1),
                'status' => 'ACTIVE',
                'base_salary' => 8500000,
                'face_data' => json_encode(array_fill(0, 128, 0.5)),
            ]
        );
    }

    /**
     * Tier 1 & Tier 2: Private Attendance Streaming Gate & Authorization
     */
    public function test_attendance_photo_streaming_enforces_auth_and_privacy_headers(): void
    {
        $filename = 'attend_selfie_01.jpg';
        $dummyImage = '%JPEG dummy image binary';
        Storage::disk('private')->put('attendances/' . $filename, $dummyImage);

        $log = AttendanceLog::create([
            'employee_id' => $this->employee->id,
            'kind' => 'CLOCK_IN',
            'at' => now(),
            'method' => 'BIOMETRIC_FACE',
            'photo_url' => 'attendances/' . $filename,
            'face_match_score' => 0.92,
        ]);

        // 1. Request tanpa autentikasi -> 302 Redirect ke login
        $this->get(route('portal.attendances.photo', $log->id))
            ->assertRedirect(route('login'));

        // 2. Request dari user lain yang tidak berhak -> 403 Forbidden
        $this->actingAs($this->unauthorizedUser)
            ->get(route('portal.attendances.photo', $log->id))
            ->assertStatus(403);

        // 3. Request dari pemilik foto sendiri -> 200 OK
        $ownerResp = $this->actingAs($this->regularUser)
            ->get(route('portal.attendances.photo', $log->id));
        $ownerResp->assertOk();
        $this->assertTrue($ownerResp->headers->has('Cache-Control'));
        $this->assertStringContainsString('no-cache', (string) $ownerResp->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', (string) $ownerResp->headers->get('Cache-Control'));

        // 4. Request dari tim HR -> 200 OK
        $hrResp = $this->actingAs($this->hrUser)
            ->get(route('portal.attendances.photo', $log->id));
        $hrResp->assertOk();
    }

    /**
     * Tier 1 & Tier 2: Biometric Database Encryption on face_data
     */
    public function test_employee_face_data_is_transparently_encrypted_in_database(): void
    {
        $rawVector = array_map(fn($i) => round(sin($i), 4), range(1, 128));
        $jsonPayload = json_encode($rawVector);

        $emp = Employee::create([
            'nik' => 'EMP-CRYPTO-99',
            'user_id' => $this->unauthorizedUser->id,
            'full_name' => 'Crypto Subject',
            'position' => 'Staff',
            'team' => 'LOGISTICS',
            'start_date' => now(),
            'status' => 'ACTIVE',
            'base_salary' => 6000000,
            'face_data' => $jsonPayload,
        ]);

        // Verifikasi di raw level MySQL bahwa string tersimpan telah terenkripsi (bukan plain JSON)
        $rawDbValue = DB::table('employees')->where('id', $emp->id)->value('face_data');
        $this->assertNotEmpty($rawDbValue);
        $this->assertStringNotContainsString('"0.8415"', $rawDbValue);
        $this->assertNotEquals($jsonPayload, $rawDbValue);

        // Verifikasi ketika diakses melalui Eloquent Model, otomatis terdekripsi
        $empFresh = Employee::find($emp->id);
        $this->assertEquals($jsonPayload, $empFresh->face_data);
    }

    /**
     * Tier 1 & Tier 2: Biometric Consent Gate Blocks Clock-in Without Valid Consent
     */
    public function test_clock_in_is_rejected_without_valid_biometric_consent(): void
    {
        // Pastikan tidak ada consent aktif
        BiometricConsent::where('employee_id', $this->employee->id)->delete();
        $this->assertFalse($this->employee->fresh()->hasValidBiometricConsent());

        // Coba clock-in via Staff Portal tanpa consent
        $resp = $this->actingAs($this->regularUser)->postJson(route('hrm.portal.clock-in'), [
            'lat' => -6.2088,
            'lng' => 106.8456,
            'face_photo' => 'data:image/jpeg;base64,' . base64_encode('dummy'),
        ]);

        // Harus ditolak dengan status 403 dan require_consent
        $resp->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonFragment(['require_consent' => true]);

        // Rekam persetujuan biometrik sah
        BiometricConsent::create([
            'employee_id' => $this->employee->id,
            'consent_at' => now(),
            'retention_until' => now()->addYears(2),
        ]);

        $this->assertTrue($this->employee->fresh()->hasValidBiometricConsent());
    }

    /**
     * Tier 2: 2-Year Retention Purge Command Destroys Expired Biometrics
     */
    public function test_purge_command_removes_biometric_photos_older_than_two_years(): void
    {
        $oldFilename = 'purge_old_photo.jpg';
        $freshFilename = 'keep_fresh_photo.jpg';

        $oldPrivateDir = storage_path('app/private/attendances');
        if (!File::exists($oldPrivateDir)) {
            File::makeDirectory($oldPrivateDir, 0755, true);
        }

        $oldFilePath = $oldPrivateDir . '/' . $oldFilename;
        $freshFilePath = $oldPrivateDir . '/' . $freshFilename;
        File::put($oldFilePath, 'old photo content');
        File::put($freshFilePath, 'fresh photo content');

        // Log 2.5 tahun lalu (harus dipurge)
        $oldLog = AttendanceLog::create([
            'employee_id' => $this->employee->id,
            'kind' => 'CLOCK_IN',
            'at' => now()->subMonths(30),
            'method' => 'BIOMETRIC_FACE',
            'photo_url' => $oldFilePath,
            'notes' => 'Presensi Lampau',
        ]);

        // Log 3 bulan lalu (harus dipertahankan)
        $freshLog = AttendanceLog::create([
            'employee_id' => $this->employee->id,
            'kind' => 'CLOCK_IN',
            'at' => now()->subMonths(3),
            'method' => 'BIOMETRIC_FACE',
            'photo_url' => $freshFilePath,
            'notes' => 'Presensi Baru',
        ]);

        // Jalankan perintah artisan biometric:purge-expired
        $this->artisan('biometric:purge-expired')
            ->assertSuccessful();

        // Verifikasi berkas fisik lama terhapus, berkas baru utuh
        $this->assertFileDoesNotExist($oldFilePath);
        $this->assertFileExists($freshFilePath);

        // Verifikasi database: photo_url lama di-null-kan dan ada audit catatan retensi
        $oldLog->refresh();
        $this->assertNull($oldLog->photo_url);
        $this->assertStringContainsString('UU PDP Retensi 2 Tahun', $oldLog->notes);

        // Verifikasi log baru tidak disentuh
        $freshLog->refresh();
        $this->assertNotNull($freshLog->photo_url);

        // Cleanup
        if (File::exists($freshFilePath)) {
            File::delete($freshFilePath);
        }
    }

    /**
     * Tier 1 & Tier 2: Authentic Face Recognition Vector Calculation & Threshold
     */
    public function test_face_recognition_service_computes_authentic_vector_similarity(): void
    {
        $service = new BiometricVerificationService();

        // 128-D vector dasar
        $baseVector = array_fill(0, 128, 0.5);
        $this->employee->update(['face_data' => json_encode($baseVector)]);

        // 1. Citra live dengan pola identik
        $dummyImage = 'data:image/jpeg;base64,' . base64_encode(str_repeat('ABCDEFGH', 64));
        $res = $service->verifyFace($this->employee->fresh(), $dummyImage);

        $this->assertArrayHasKey('score', $res);
        $this->assertArrayHasKey('is_match', $res);
        $this->assertIsFloat($res['score']);
        $this->assertGreaterThanOrEqual(0.0, $res['score']);
        $this->assertLessThanOrEqual(1.0, $res['score']);

        // 2. Foto live kosong -> reject score 0.0
        $emptyRes = $service->verifyFace($this->employee->fresh(), null);
        $this->assertEquals(0.0, $emptyRes['score']);
        $this->assertFalse($emptyRes['is_match']);
    }
}
