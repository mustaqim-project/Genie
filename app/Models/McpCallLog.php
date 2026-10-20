<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class McpCallLog extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'mcp_call_logs';

    public $timestamps = false;

    protected $fillable = [
        'credential_id',
        'tool',
        'args',
        'result_status',
        'provider',
        'created_at',
    ];

    protected $casts = [
        'args' => 'array',
        'created_at' => 'datetime',
    ];

    public function credential(): BelongsTo
    {
        return $this->belongsTo(McpCredential::class, 'credential_id');
    }
}
