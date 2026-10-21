<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class WeeklyMondaySessionResetTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = User::where('email', 'admin@agungtrans.co.id')->firstOrFail();
    }

    public function test_user_session_remains_active_within_the_same_monday_cycle(): void
    {
        $currentMonday = now()->startOfWeek(Carbon::MONDAY)->toDateString();

        $response = $this->actingAs($this->user)
            ->withSession(['auth_monday_cycle' => $currentMonday])
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_user_session_is_invalidated_when_crossing_into_new_monday_cycle(): void
    {
        // Simulasikan login dilakukan pada siklus minggu lalu (Senin minggu sebelumnya)
        $pastMonday = now()->startOfWeek(Carbon::MONDAY)->subWeek()->toDateString();

        $response = $this->actingAs($this->user)
            ->withSession(['auth_monday_cycle' => $pastMonday])
            ->get(route('dashboard'));

        // Harus di-redirect ke login
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_session_reset_can_be_disabled_via_system_settings(): void
    {
        // Nonaktifkan reset mingguan di pengaturan sistem
        SystemSetting::set('session_weekly_monday_reset', false);

        $pastMonday = now()->startOfWeek(Carbon::MONDAY)->subWeek()->toDateString();

        $response = $this->actingAs($this->user)
            ->withSession(['auth_monday_cycle' => $pastMonday])
            ->get(route('dashboard'));

        // Karena dinonaktifkan, user tetap login
        $response->assertStatus(200);
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_artisan_command_reset_weekly_monday_sessions(): void
    {
        $exitCode = Artisan::call('session:reset-weekly-monday');
        $this->assertEquals(0, $exitCode);

        $exitCodeForce = Artisan::call('session:reset-weekly-monday', ['--force' => true]);
        $this->assertEquals(0, $exitCodeForce);
    }

    public function test_update_session_settings_via_setting_controller(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('setting.session.update'), [
                'session_weekly_monday_reset' => '1',
                'session_lifetime_days' => '7',
            ]);

        $response->assertRedirect(route('setting.index', ['tab' => 'security']));
        $response->assertSessionHas('success');

        $this->assertTrue(SystemSetting::get('session_weekly_monday_reset'));
        $this->assertEquals(7, SystemSetting::get('session_lifetime_days'));
        $this->assertEquals(10080, SystemSetting::get('session_lifetime_minutes'));
    }
}
