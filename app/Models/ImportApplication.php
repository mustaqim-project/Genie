<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportApplication extends Model
{
    use HasFactory, HasUuids, Auditable;

    protected $table = 'import_applications';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'batch_id',
        'shipment_id',
        'idempotency_key',
        'applied_data_payload',
        'applied_by_id',
        'can_rollback_until',
        'is_rolled_back',
    ];

    protected $casts = [
        'applied_data_payload' => 'array',
        'can_rollback_until' => 'datetime',
        'is_rolled_back' => 'boolean',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'batch_id');
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class, 'shipment_id');
    }

    public function applier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applied_by_id');
    }
}
