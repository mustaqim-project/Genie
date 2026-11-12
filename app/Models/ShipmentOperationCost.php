<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentOperationCost extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'shipment_id',
        'cost_category',
        'vendor_name',
        'invoice_number',
        'amount',
        'status',
        'cost_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'cost_date' => 'date',
    ];

    public const CATEGORIES = [
        'DO' => 'D/O Pelayaran',
        'EXTEND_DO_1' => 'Extend D/O I',
        'EXTEND_DO_2' => 'Extend D/O II',
        'EXTEND_DO_3' => 'Extend D/O III',
        'REFUND' => 'Refund Jaminan',
        'BHDL' => 'BHDL (Handling / Behandle)',
        'STG' => 'STG (Storage / Penumpukan)',
        'TKC' => 'TKC',
        'LOLO' => 'LOLO (Lift On / Lift Off)',
        'TRUCKING' => 'Trucking / Pengiriman',
        'OTHER' => 'Biaya Operasional Lain',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->cost_category] ?? $this->cost_category;
    }
}
