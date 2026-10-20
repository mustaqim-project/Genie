<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingCeisaDatabaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_setting_page_renders_with_database_ceisa_config()
    {
        $user = User::where('email', 'admin@agungtrans.co.id')->first();
        if (!$user) {
            $user = User::first();
        }

        $response = $this->actingAs($user)->get(route('setting.index', ['tab' => 'gateway']));
        $response->assertStatus(200);
        $response->assertSee('Konfigurasi Gateway CEISA 4.0');
        $response->assertSee('e82fd084-b913-4eb9-832b-29be4912a9f9');
        $response->assertSee('7d52ea4a-ca0d-42e1-b3ca-f611bd1c4ad6');
        $response->assertSee('/api/v1/ceisa/webhook');
    }

    public function test_can_update_ceisa_settings_to_database()
    {
        $user = User::first();

        $response = $this->actingAs($user)->post(route('setting.ceisa.update'), [
            'ceisa_mode' => 'production',
            'ceisa_base_url' => 'https://apis-gw.beacukai.go.id/v2/openapi',
            'ceisa_auth_url' => 'https://apis-gw.beacukai.go.id/v1/openapi-auth/user/login',
            'ceisa_username' => 'agungtrans_live',
            'ceisa_password' => 'NewLivePassword123!',
            'ceisa_api_key' => 'e82fd084-b913-4eb9-832b-29be4912a9f9',
            'ceisa_application_id' => '7d52ea4a-ca0d-42e1-b3ca-f611bd1c4ad6',
            'ceisa_whatsapp_no' => '081908882591',
            'company_name' => 'PT AGUNG TRANS SOLUSINDO',
            'company_nib' => '9120009876543',
            'company_npwp' => '012345678012000',
            'company_address' => 'Jl. Pelabuhan Nusantara II No. 12, Tanjung Priok',
            'company_kode_kantor' => '040300',
            'company_id_perusahaan' => '012345',
        ]);

        $response->assertRedirect(route('setting.index', ['tab' => 'gateway']));
        $this->assertEquals('production', SystemSetting::get('ceisa_env'));
        $this->assertEquals('0', SystemSetting::get('ceisa_mock_mode'));
        $this->assertEquals('agungtrans_live', SystemSetting::get('ceisa_username'));
        $this->assertFalse(config('ceisa.api.mock_mode'));
        $this->assertEquals('agungtrans_live', config('ceisa.api.username'));

        // Restore back to sandbox for safety
        SystemSetting::set('ceisa_env', 'sandbox', 'ceisa');
        SystemSetting::set('ceisa_mock_mode', '1', 'ceisa');
        SystemSetting::set('ceisa_username', 'agungtrans_api', 'ceisa');
        SystemSetting::loadIntoConfig();
    }
}
