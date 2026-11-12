<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrder extends Model
{
    use HasFactory, HasUuids, SoftDeletes, Auditable;

    protected $table = 'sales_orders';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'order_number',
        'quotation_id',
        'client_id',
        'order_date',
        'expected_shipment_date',
        'total_amount_idr',
        'status',
        'shipment_id',
        'created_by_user_id',
        'notes',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_shipment_date' => 'date',
        'total_amount_idr' => 'decimal:2',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class, 'shipment_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public static function generateOrderNumber(): string
    {
        $prefix = 'SO-' . date('Ym') . '-';
        $latest = self::where('order_number', 'like', "{$prefix}%")->latest('created_at')->first();
        $next = $latest ? ((int) substr($latest->order_number, -4) + 1) : 1;

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
