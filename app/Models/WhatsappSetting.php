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
}
