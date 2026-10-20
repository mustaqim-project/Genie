<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialMediaPost extends Model
{
    use HasUuids;

    protected $table = 'social_media_posts';

    protected $fillable = [
        'social_media_account_id',
        'platform',
        'content',
        'media_type',
        'media_urls',
        'status',
        'scheduled_at',
        'published_at',
        'metrics',
        'error_message',
    ];

    protected $casts = [
        'media_urls' => 'array',
        'metrics' => 'array',
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(SocialMediaAccount::class, 'social_media_account_id');
    }
}
