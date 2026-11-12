<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentStageEntry extends Model
{
    use HasFactory, HasUuids, Auditable;

    protected $table = 'shipment_stage_entries';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'shipment_id',
        'stage_number',
        'stage_code',
        'entry_mode', // NATIVE, IMPORTED, SKIPPED
        'original_date',
        'source_note',
        'backfill_status', // COMPLETE, PENDING, NOT_APPLICABLE
    ];

    protected $casts = [
        'stage_number' => 'integer',
        'original_date' => 'date',
    ];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class, 'shipment_id');
    }
}
