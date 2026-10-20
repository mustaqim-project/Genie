<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientInvoiceLine extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'client_invoice_lines';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'invoice_id',
        'description',
        'amount',
        'cost_item_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(ClientInvoice::class, 'invoice_id');
    }

    public function costItem()
    {
        return $this->belongsTo(CostItem::class, 'cost_item_id');
    }
}
