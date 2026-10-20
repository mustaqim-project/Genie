<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappBroadcastRecipient extends Model
{
    use HasUuids;

    protected $table = 'whatsapp_broadcast_recipients';

    protected $fillable = [
        'campaign_id',
        'client_id',
        'recipient_name',
        'phone_number',
        'status',
        'sent_at',
        'error_message',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(WhatsappBroadcastCampaign::class, 'campaign_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
