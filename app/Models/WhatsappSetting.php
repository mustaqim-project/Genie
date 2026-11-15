<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class WhatsappSetting extends Model
{
    use HasUuids;

    protected $table = 'whatsapp_settings';

    protected $fillable = [
        'waba_id',
        'phone_number_id',
        'display_phone_number',
        'verified_name',
        'access_token',
        'app_id',
        'app_secret',
        'webhook_verify_token',
        'quality_rating',
        'messaging_limit_tier',
        'is_connected',
        'auto_send_sppb',
        'auto_send_npe',
        'auto_send_do',
        'auto_send_billing',
    ];

    protected $casts = [
        'is_connected' => 'boolean',
        'auto_send_sppb' => 'boolean',
        'auto_send_npe' => 'boolean',
        'auto_send_do' => 'boolean',
        'auto_send_billing' => 'boolean',
    ];

    /**
     * Pengambilan Pengaturan Terpusat (AUDIT-MED-04 Centralized Settings)
     */
    public static function current(): self
    {
        return self::firstOrCreate(
            [],
            [
                'display_phone_number' => config('services.whatsapp.display_phone', '+62 812-9000-8888'),
                'verified_name' => config('services.whatsapp.verified_name', 'PT AGUNG TRANS LOGISTICS'),
                'phone_number_id' => config('services.whatsapp.phone_number_id', '109283746501928'),
                'waba_id' => config('services.whatsapp.waba_id', '104928374829103'),
                'quality_rating' => 'GREEN',
                'messaging_limit_tier' => 'TIER_10K',
                'is_connected' => false,
                'auto_send_sppb' => true,
                'auto_send_npe' => true,
                'auto_send_do' => true,
                'auto_send_billing' => true,
            ]
        );
    }
}
