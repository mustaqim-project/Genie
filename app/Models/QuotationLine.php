<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationLine extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'quotation_lines';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'quotation_id',
        'service_code',
        'description',
        'quantity',
        'unit',
        'unit_price_idr',
        'discount_percent',
        'discount_amount_idr',
        'subtotal_idr',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price_idr' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount_idr' => 'decimal:2',
        'subtotal_idr' => 'decimal:2',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }
}
