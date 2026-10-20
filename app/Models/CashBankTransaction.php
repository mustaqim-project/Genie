<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashBankTransaction extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'cash_bank_transactions';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'account_id',
        'trx_date',
        'amount',
        'description',
        'reconciled',
        'ref_type',
        'ref_id',
    ];

    protected $casts = [
        'trx_date' => 'date',
        'amount' => 'decimal:2',
        'reconciled' => 'boolean',
    ];

    public function account()
    {
        return $this->belongsTo(CashBankAccount::class, 'account_id');
    }
}
