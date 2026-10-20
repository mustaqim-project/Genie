<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SocialMediaAccount extends Model
{
    use HasUuids;

    protected $table = 'social_media_accounts';

    protected $fillable = [
        'platform',
        'account_id',
        'account_name',
        'username',
        'profile_picture_url',
        'access_token',
        'status',
        'followers_count',
        'metadata',
    ];

    protected $casts = [
        'followers_count' => 'integer',
        'metadata' => 'array',
    ];

    public function posts(): HasMany
    {
        return $this->hasMany(SocialMediaPost::class, 'social_media_account_id');
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(SocialMediaInteraction::class, 'social_media_account_id');
    }
}
