<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AuditLog extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'audit_logs';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'actor_id',
        'actor_type',
        'action',
        'object_type',
        'object_id',
        'old_value',
        'new_value',
        'ip',
        'created_at',
    ];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
        'created_at' => 'datetime',
    ];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * Helper manual untuk mencatat aktivitas sistem
     */
    public static function record(
        string $action,
        string $objectType,
        ?string $objectId = null,
        ?array $oldValue = null,
        ?array $newValue = null,
        ?string $actorId = null,
        string $actorType = 'USER'
    ): self {
        $resolvedActorId = $actorId ?? auth()->id();
        if ($resolvedActorId !== null && !User::where('id', $resolvedActorId)->exists()) {
            $resolvedActorId = null;
        }

        return self::create([
            'id' => (string) Str::uuid(),
            'actor_id' => $resolvedActorId,
            'actor_type' => $actorType,
            'action' => strtoupper($action),
            'object_type' => $objectType,
            'object_id' => $objectId,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'ip' => request()->ip() ?? '127.0.0.1',
            'created_at' => now(),
        ]);
    }
}
