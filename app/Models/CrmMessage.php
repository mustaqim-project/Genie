<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmMessage extends Model
{
    use HasUuids;

    protected $table = 'crm_messages';

    public $timestamps = false;

    protected $fillable = [
        'conversation_id',
        'direction',
        'body',
        'template_id',
        'sent_by',
        'ai_generated',
        'created_at',
    ];

    protected $casts = [
        'ai_generated' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(CrmConversation::class, 'conversation_id');
    }
}
