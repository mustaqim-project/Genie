<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    use HasUuids;

    protected $table = 'system_settings';

    protected $fillable = [
        'key',
        'value',
        'group',
        'description',
    ];

    /**
     * Dapatkan nilai pengaturan berdasarkan key
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember('system_setting_' . $key, 3600, function () use ($key, $default) {
            $record = static::where('key', $key)->first();
            if (!$record || $record->value === null) {
                return $default;
            }
            $decoded = json_decode($record->value, true);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : $record->value;
        });
    }

    /**
     * Simpan atau perbarui nilai pengaturan
     */
    public static function set(string $key, mixed $value, string $group = 'general', ?string $description = null): static
    {
        $record = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => json_encode($value),
                'group' => $group,
                'description' => $description,
            ]
        );

        Cache::forget('system_setting_' . $key);
        Cache::forget('all_system_settings');

        return $record;
    }

    /**
     * Ambil semua pengaturan yang dikelompokkan
     */
    public static function getAllGrouped(): array
    {
        return Cache::remember('all_system_settings', 3600, function () {
            return static::all()->mapWithKeys(function ($item) {
                $decoded = json_decode($item->value, true);
                return [$item->key => (json_last_error() === JSON_ERROR_NONE ? $decoded : $item->value)];
            })->toArray();
        });
    }

    /**
     * Terapkan pengaturan database ke runtime Laravel config
     */
    public static function loadIntoConfig(): void
    {
        try {
            $settings = static::getAllGrouped();

            if (empty($settings)) {
                return;
            }

            // 1. Profil Entitas Perusahaan DJBC
            if (isset($settings['company_name'])) config(['ceisa.company.name' => $settings['company_name']]);
            if (isset($settings['company_nib'])) config(['ceisa.company.nib' => $settings['company_nib']]);
            if (isset($settings['company_npwp'])) config(['ceisa.company.npwp' => $settings['company_npwp']]);
            if (isset($settings['company_address'])) config(['ceisa.company.address' => $settings['company_address']]);
            if (isset($settings['company_kode_kantor'])) config(['ceisa.company.default_kode_kantor' => $settings['company_kode_kantor']]);
            if (isset($settings['company_id_perusahaan'])) config(['ceisa.company.id_perusahaan' => $settings['company_id_perusahaan']]);

            // 2. Parameter Gateway CEISA 4.0
            if (isset($settings['ceisa_env'])) config(['ceisa.api.env' => $settings['ceisa_env']]);
            if (isset($settings['ceisa_mock_mode'])) {
                config(['ceisa.api.mock_mode' => filter_var($settings['ceisa_mock_mode'], FILTER_VALIDATE_BOOLEAN)]);
            }
            if (isset($settings['ceisa_base_url'])) config(['ceisa.api.base_url' => $settings['ceisa_base_url']]);
            if (isset($settings['ceisa_auth_url'])) config(['ceisa.api.auth_url' => $settings['ceisa_auth_url']]);
            if (isset($settings['ceisa_refresh_token_url'])) config(['ceisa.api.refresh_token_url' => $settings['ceisa_refresh_token_url']]);
            if (isset($settings['ceisa_username'])) config(['ceisa.api.username' => $settings['ceisa_username']]);
            if (isset($settings['ceisa_password'])) config(['ceisa.api.password' => $settings['ceisa_password']]);
            if (isset($settings['ceisa_api_key'])) config(['ceisa.api.api_key' => $settings['ceisa_api_key']]);
            if (isset($settings['ceisa_application_id'])) config(['ceisa.api.application_id' => $settings['ceisa_application_id']]);
            if (isset($settings['ceisa_whatsapp_no'])) config(['ceisa.api.whatsapp_no' => $settings['ceisa_whatsapp_no']]);

            // 3. Kebijakan Sesi Keamanan Mingguan
            if (isset($settings['session_lifetime_minutes'])) {
                config(['session.lifetime' => (int) $settings['session_lifetime_minutes']]);
            }
        } catch (\Throwable $e) {
            // Fail silently during initial migration or schema checks
        }
    }
}
