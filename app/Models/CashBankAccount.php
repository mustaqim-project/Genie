<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashBankAccount extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'cash_bank_accounts';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'bank',
        'account_no',
        'currency_id',
    ];

    public function transactions()
    {
        return $this->hasMany(CashBankTransaction::class, 'account_id')->orderBy('trx_date', 'desc');
    }

    public function vendorPayments()
    {
        return $this->hasMany(VendorPayment::class, 'account_id');
    }

    /**
     * Hitung Saldo Real-Time Rekening dari Seluruh Mutasi Masuk/Keluar
     */
    public function getCurrentBalanceAttribute(): float
    {
        return (float) $this->transactions()->sum('amount');
    }
}
