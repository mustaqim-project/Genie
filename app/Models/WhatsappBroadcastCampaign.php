<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsappBroadcastCampaign extends Model
{
    use HasUuids;

    protected $table = 'whatsapp_broadcast_campaigns';

    protected $fillable = [
        'title',
        'category',
        'message',
        'media_url',
        'target_type',
        'total_recipients',
        'total_sent',
        'total_failed',
        'status',
        'scheduled_at',
        'sent_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'total_recipients' => 'integer',
        'total_sent' => 'integer',
        'total_failed' => 'integer',
    ];

    public function recipients(): HasMany
    {
        return $this->hasMany(WhatsappBroadcastRecipient::class, 'campaign_id');
    }

    public function getSuccessRateAttribute(): int
    {
        if ($this->total_recipients <= 0) {
            return 100;
        }
        return (int) round(($this->total_sent / $this->total_recipients) * 100);
    }
}
