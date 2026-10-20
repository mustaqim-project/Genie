<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Pastikan semua 16 rute navigasi Admin Panel lengkap merender layout (Sidebar, Topbar, Footer) dengan status 200 OK
     */
    public function test_all_admin_panel_routes_render_layout_successfully(): void
    {
        $admin = User::where('email', 'admin@agungtrans.co.id')->first();
        $this->actingAs($admin);

        $routes = [
            route('dashboard'),
            route('core.document.index'),
            route('bc20.index'),
            route('bc30.index'),
            route('core.do.index'),
            route('do.index'),
            route('core.traffic.index'),
            route('traffic.index'),
            route('hrm.index'),
            route('crm.index'),
            route('crm.quotations.index'),
            route('finance.index'),
            route('finance.invoices.index'),
            route('accounting.index'),
            route('ai.index'),
            route('setting.index'),
            route('config.index'),
            route('profile'),
        ];

        foreach ($routes as $route) {
            $response = $this->get($route);
            if ($route === route('do.index')) {
                $response->assertRedirect(route('core.do.index'));
            } elseif ($route === route('traffic.index')) {
                $response->assertRedirect(route('core.traffic.index'));
            } else {
                $response->assertStatus(200);
                $response->assertSee('GENIE', false);
                $response->assertSee('PT Agung Trans Solusindo', false);
            }
        }
    }
}
