<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialMediaInteraction extends Model
{
    use HasUuids;

    protected $table = 'social_media_interactions';

    protected $fillable = [
        'social_media_account_id',
        'platform',
        'type',
        'from_name',
        'from_handle',
        'message',
        'status',
        'reply_message',
        'replied_at',
        'created_time',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
        'created_time' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(SocialMediaAccount::class, 'social_media_account_id');
    }
}
