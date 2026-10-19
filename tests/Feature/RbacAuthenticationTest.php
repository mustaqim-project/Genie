<?php

namespace Tests\Feature;

use App\Models\CeisaDocument;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RbacAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }
    /**
     * Skenario 1: Tamu (Guest) tanpa sesi login wajib dialihkan ke halaman login
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect(route('login'));

        $responseDoc = $this->get('/bc-20');
        $responseDoc->assertRedirect(route('login'));

        $responseSetting = $this->get('/setting');
        $responseSetting->assertRedirect(route('login'));
    }

    /**
     * Skenario 2: Login berhasil dengan kredensial sah
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::where('email', 'admin@agungtrans.co.id')->first();
        if (!$user) {
            $user = User::create([
                'name' => 'Admin Test',
                'email' => 'admin.test@agungtrans.co.id',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]);
        }

        $response = $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Skenario 3: Akun yang dinonaktifkan ditolak saat login
     */
    public function test_inactive_user_cannot_login(): void
    {
        $inactiveUser = User::create([
            'name' => 'Inactive Staff',
            'email' => 'inactive@agungtrans.co.id',
            'password' => Hash::make('password'),
            'is_active' => false,
        ]);

        $response = $this->post(route('login.post'), [
            'email' => 'inactive@agungtrans.co.id',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /**
     * Skenario 4: Administrator (R-ADM) memiliki akses penuh ke seluruh modul
     */
    public function test_admin_has_full_access_to_dashboard_and_setting(): void
    {
        $admin = User::where('email', 'admin@agungtrans.co.id')->first();
        $this->actingAs($admin);

        $responseDashboard = $this->get(route('dashboard'));
        $responseDashboard->assertStatus(200);

        $responseSetting = $this->get(route('setting.index'));
        $responseSetting->assertStatus(200);

        $responseBc20 = $this->get(route('bc20.index'));
        $responseBc20->assertStatus(200);
    }

    /**
     * Skenario 5: Staff DO (R-DO) memiliki akses ke DO, tetapi 403 saat mengakses modul HRM & Setting
     */
    public function test_staff_do_restricted_from_accessing_setting_and_hrm(): void
    {
        $doStaff = User::where('email', 'do.staff@agungtrans.co.id')->first();
        $this->actingAs($doStaff);

        // Berhak akses DO
        $responseDo = $this->get(route('do.index'));
        $responseDo->assertStatus(200);

        // Ditolak 403 saat akses Setting
        $responseSetting = $this->get(route('setting.index'));
        $responseSetting->assertStatus(403);

        // Ditolak 403 saat akses HRM
        $responseHrm = $this->get(route('hrm.index'));
        $responseHrm->assertStatus(403);
    }

    /**
     * Skenario 6: Pengguna dapat melihat dan mengubah profil mereka sendiri
     */
    public function test_user_can_view_and_update_profile(): void
    {
        $user = User::where('email', 'spv.doc@agungtrans.co.id')->first();
        $this->actingAs($user);

        $response = $this->get(route('profile'));
        $response->assertStatus(200);
        $response->assertSee($user->name);

        $updateResponse = $this->put(route('profile.update'), [
            'name' => 'Rian Hidayat Updated',
            'phone' => '081299988877',
            'job_title' => 'Senior Customs Supervisor',
        ]);

        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Rian Hidayat Updated',
            'phone' => '081299988877',
        ]);
    }

    /**
     * Skenario 7: Pengguna dapat mengganti kata sandi dengan verifikasi kata sandi lama
     */
    public function test_user_can_change_password(): void
    {
        $user = User::where('email', 'spv.doc@agungtrans.co.id')->first();
        $this->actingAs($user);

        // Kata sandi lama salah
        $failResponse = $this->put(route('profile.password'), [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);
        $failResponse->assertSessionHasErrors('current_password');

        // Kata sandi lama benar
        $successResponse = $this->put(route('profile.password'), [
            'current_password' => 'password',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);
        $successResponse->assertSessionHasNoErrors();
        $successResponse->assertRedirect();

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    /**
     * Skenario 8: Administrator dapat menambah pengguna baru dan menonaktifkan status akun
     */
    public function test_admin_can_create_user_and_toggle_status(): void
    {
        $admin = User::where('email', 'admin@agungtrans.co.id')->first();
        $this->actingAs($admin);

        $roleDo = Role::where('code', 'R-DO')->first();

        $createResponse = $this->post(route('setting.users.store'), [
            'name' => 'Staf Baru Test',
            'email' => 'staf.baru@agungtrans.co.id',
            'password' => 'secret123',
            'role_id' => $roleDo->id,
            'phone' => '081288887777',
            'job_title' => 'Staf Penebusan DO',
            'department' => 'Shipping & DO',
        ]);

        $createResponse->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'staf.baru@agungtrans.co.id',
            'is_active' => 1,
        ]);

        $newUser = User::where('email', 'staf.baru@agungtrans.co.id')->first();
        $this->assertTrue($newUser->hasRole('R-DO'));

        // Toggle status menjadi nonaktif
        $toggleResponse = $this->post(route('setting.users.toggle-status', $newUser->id));
        $toggleResponse->assertRedirect();

        $newUser->refresh();
        $this->assertFalse((bool)$newUser->is_active);
    }

    /**
     * Skenario 9: Administrator dapat memperbarui matriks hak akses per peran secara dinamis
     */
    public function test_admin_can_update_role_permissions_matrix(): void
    {
        $admin = User::where('email', 'admin@agungtrans.co.id')->first();
        $this->actingAs($admin);

        $roleDoc = Role::where('code', 'R-DOC')->first();
        $permHrm = Permission::where('code', 'hrm.view')->first();

        $this->assertFalse($roleDoc->hasPermission('hrm.view'));

        // Admin menambahkan hak akses hrm.view ke R-DOC
        $updateMatrixResponse = $this->post(route('setting.roles.permissions', $roleDoc->id), [
            'permissions' => array_merge($roleDoc->permissions->pluck('id')->toArray(), [$permHrm->id]),
        ]);

        $updateMatrixResponse->assertRedirect();
        $roleDoc->refresh();
        $this->assertTrue($roleDoc->hasPermission('hrm.view'));
    }

    /**
     * Skenario 10: Pengiriman dokumen ke CEISA membutuhkan izin ceisa.submit
     */
    public function test_document_submission_requires_ceisa_submit_permission(): void
    {
        // 1. Staff DOC (R-DOC) tanpa ceisa.submit ditolak 403 saat submit
        $docStaff = User::where('email', 'doc.staff@agungtrans.co.id')->first();
        $this->actingAs($docStaff);

        $doc = CeisaDocument::where('jenis_dokumen', '20')->first();
        $submitResponseStaff = $this->post(route('bc20.submit', $doc->id));
        $submitResponseStaff->assertStatus(403);

        // 2. Supervisor Dokumen (R-DOC-SPV) memiliki ceisa.submit berhasil submit
        $docSpv = User::where('email', 'spv.doc@agungtrans.co.id')->first();
        $this->actingAs($docSpv);

        $submitResponseSpv = $this->post(route('bc20.submit', $doc->id));
        $submitResponseSpv->assertRedirect();
        $submitResponseSpv->assertSessionHas('success');
    }
}
