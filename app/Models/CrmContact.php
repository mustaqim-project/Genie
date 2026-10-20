<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CrmContact extends Model
{
    use HasUuids;

    protected $table = 'crm_contacts';

    protected $fillable = [
        'client_id',
        'name',
        'phone',
        'handle',
        'opt_in',
    ];

    protected $casts = [
        'opt_in' => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(CrmConversation::class, 'contact_id');
    }
}
