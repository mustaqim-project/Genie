<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientDeposit extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'client_deposits';

    protected $fillable = [
        'deposit_no',
        'client_id',
        'tanggal',
        'nominal',
        'saldo_sisa',
        'metode_pembayaran',
        'bank_name',
        'rekening_tujuan',
        'nomor_referensi_bank',
        'bukti_bayar_path',
        'status',
        'catatan',
        'created_by_user_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'decimal:2',
        'saldo_sisa' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(ClientDepositUsage::class, 'client_deposit_id')->latest('created_at');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Generate Nomor Transaksi Deposit Otomatis (DEP-YYYYMM-XXXX)
     */
    public static function generateDepositNumber(): string
    {
        $prefix = 'DEP-' . date('Ym') . '-';
        $last = self::where('deposit_no', 'like', $prefix . '%')->latest('deposit_no')->first();
        if ($last) {
            $num = (int) substr($last->deposit_no, -4) + 1;
        } else {
            $num = 1;
        }
        return $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
    }
}
