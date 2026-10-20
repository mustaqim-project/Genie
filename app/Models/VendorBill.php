<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorBill extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'vendor_bills';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'vendor_type', // SHIPPING_LINE, TRUCKING, TPS, CUSTOMS
        'shipment_id',
        'ref_no',
        'amount',
        'due_date',
        'status', // PENDING, APPROVED, PAID
        'approved_by_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    public function payments()
    {
        return $this->hasMany(VendorPayment::class, 'bill_id');
    }

    public function getPaidAmountAttribute(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->amount - $this->paid_amount);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status !== 'PAID' && $this->due_date && $this->due_date->isPast();
    }

    public function getVendorTypeLabelAttribute(): string
    {
        return match (strtoupper($this->vendor_type)) {
            'SHIPPING_LINE' => 'Pelayaran (Shipping Line)',
            'TRUCKING' => 'Subkon Trucking Inland',
            'TPS' => 'Terminal Petikemas TPS',
            'CUSTOMS' => 'Bea Cukai / Pabean',
            default => $this->vendor_type,
        };
    }
}
