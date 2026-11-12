<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportItem extends Model
{
    use HasFactory, HasUuids, Auditable;

    protected $table = 'import_items';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'document_id',
        'item_no',
        'description',
        'hs_code',
        'quantity',
        'unit',
        'unit_price',
        'total_price',
        'gross_weight',
        'net_weight',
        'country_of_origin',
        'confidence_score',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'total_price' => 'decimal:4',
        'gross_weight' => 'decimal:4',
        'net_weight' => 'decimal:4',
        'confidence_score' => 'decimal:2',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(ImportDocument::class, 'document_id');
    }
}
