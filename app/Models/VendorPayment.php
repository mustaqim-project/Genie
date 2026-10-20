<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorPayment extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'vendor_payments';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'bill_id',
        'account_id',
        'amount',
        'paid_at',
        'proof_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'date',
    ];

    public function bill()
    {
        return $this->belongsTo(VendorBill::class, 'bill_id');
    }

    public function account()
    {
        return $this->belongsTo(CashBankAccount::class, 'account_id');
    }

    public function proofDocument()
    {
        return $this->belongsTo(ShipmentDocument::class, 'proof_id');
    }
}
