<?php

namespace Tests\Feature;

use App\Mail\LoginOtpMail;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LoginOptimizationAndOtpTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $ownerUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->adminUser = User::where('email', 'admin@agungtrans.co.id')->firstOrFail();
        
        // Pimpinan (R-OWN) mewajibkan 2FA
        $ownerRole = Role::where('code', 'R-OWN')->firstOrFail();
        $this->ownerUser = $ownerRole->users()->firstOrFail();
    }

    public function test_login_page_displays_demo_accounts_in_non_production(): void
    {
        // Pastikan bukan production
        Config::set('app.env', 'testing');

        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('Persona Akun Demo');
        $response->assertSee('WAN HAI 315');
        $response->assertSee('STS CRANE ACTIVE');
        $response->assertSee('anim-vessel');
        $response->assertSee('anim-crane-trolley');
    }

    public function test_login_page_hides_demo_accounts_in_production(): void
    {
        // Simulasikan production
        Config::set('app.env', 'production');
        App::detectEnvironment(fn() => 'production');

        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertDontSee('Persona Akun Demo');
        $response->assertDontSee('⚡ Persona Akun Demo (Uji Coba Cepat RBAC)');
        $response->assertDontSee('Wajib 2FA (123456)');
        // Kapal dan crane tetap tampil megah
        $response->assertSee('WAN HAI 315');
        $response->assertSee('STS CRANE ACTIVE');
    }

    public function test_quick_login_is_forbidden_in_production(): void
    {
        Config::set('app.env', 'production');
        App::detectEnvironment(fn() => 'production');

        $response = $this->get(route('quick-login', 'R-OWN'));

        $response->assertStatus(403);
    }

    public function test_otp_is_sent_via_email_in_production_environment(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class);
        Mail::fake();

        Config::set('app.env', 'production');
        App::detectEnvironment(fn() => 'production');

        // Login sebagai Pimpinan (mewajibkan 2FA)
        $response = $this->post(route('login.post'), [
            'email' => $this->ownerUser->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('login.otp'));

        // Verifikasi bahwa Mailable LoginOtpMail terkirim ke email pengguna
        Mail::assertSent(LoginOtpMail::class, function ($mail) {
            return $mail->hasTo($this->ownerUser->email)
                && strlen($mail->otpCode) === 6;
        });

        // Verifikasi di session bahwa demo_code adalah null
        $this->assertEquals(null, session('2fa.demo_code'));
        $this->assertTrue(session('2fa.is_production'));
    }

    public function test_demo_code_123456_is_rejected_in_production(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class);
        Config::set('app.env', 'production');
        App::detectEnvironment(fn() => 'production');

        // Siapkan sesi 2FA dengan is_production = true
        session()->put('2fa', [
            'user_id' => $this->ownerUser->id,
            'email' => $this->ownerUser->email,
            'name' => $this->ownerUser->name,
            'role' => 'R-OWN',
            'code' => '987654', // Kode asli
            'demo_code' => null, // Tidak ada kode demo di production
            'is_production' => true,
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10),
            'remember' => false,
        ]);

        // Coba masukkan demo bypass 123456
        $response = $this->post(route('login.otp.post'), [
            'otp' => '123456',
        ]);

        $response->assertSessionHasErrors('otp');
        $this->assertGuest();

        // Masukkan kode asli yang dikirim via email
        $responseSuccess = $this->post(route('login.otp.post'), [
            'otp' => '987654',
        ]);

        $responseSuccess->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->ownerUser);
    }

    public function test_resend_otp_works_properly(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class);
        Mail::fake();

        Config::set('app.env', 'production');
        App::detectEnvironment(fn() => 'production');

        session()->put('2fa', [
            'user_id' => $this->ownerUser->id,
            'email' => $this->ownerUser->email,
            'name' => $this->ownerUser->name,
            'role' => 'R-OWN',
            'code' => '111111',
            'demo_code' => null,
            'is_production' => true,
            'attempts' => 0,
            'last_sent_at' => now()->subMinutes(2), // Sudah lewat 60 detik cooldown
            'expires_at' => now()->addMinutes(10),
            'remember' => false,
        ]);

        $response = $this->post(route('login.otp.resend'));

        $response->assertRedirect();
        $response->assertSessionHas('info');

        // Mailable terkirim
        Mail::assertSent(LoginOtpMail::class, function ($mail) {
            return $mail->hasTo($this->ownerUser->email);
        });

        // Kode baru di-generate
        $newCode = session('2fa.code');
        $this->assertNotEquals('111111', $newCode);
    }
}
