<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostItem extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'cost_items';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'shipment_id',
        'category', // REIMBURSABLE_CUSTOMS, FREIGHT, HANDLING, TRUCKING
        'account_id',
        'source_type',
        'source_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function invoiceLines()
    {
        return $this->hasMany(ClientInvoiceLine::class, 'cost_item_id');
    }
}
