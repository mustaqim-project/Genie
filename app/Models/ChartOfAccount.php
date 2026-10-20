<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'chart_of_accounts';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
        'type', // ASSET, LIABILITY, EQUITY, REVENUE, EXPENSE
        'parent_id',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('code');
    }

    public function journalLines()
    {
        return $this->hasMany(JournalLine::class, 'account_id');
    }

    public function costItems()
    {
        return $this->hasMany(CostItem::class, 'account_id');
    }

    /**
     * Hitung Saldo Akun (Debit vs Credit sesuai Normal Balance)
     */
    public function getBalanceAttribute(): float
    {
        $debit = (float) $this->journalLines()->sum('debit');
        $credit = (float) $this->journalLines()->sum('credit');

        // Normal balance Debit: ASSET & EXPENSE
        if (in_array(strtoupper($this->type), ['ASSET', 'EXPENSE'])) {
            return $debit - $credit;
        }

        // Normal balance Kredit: LIABILITY, EQUITY, REVENUE
        return $credit - $debit;
    }

    /**
     * Label Kategori Akun
     */
    public function getTypeLabelAttribute(): string
    {
        return match (strtoupper($this->type)) {
            'ASSET' => 'Aset / Aktiva',
            'LIABILITY' => 'Kewajiban / Hutang',
            'EQUITY' => 'Ekuitas / Modal',
            'REVENUE' => 'Pendapatan Usaha',
            'EXPENSE' => 'Beban / Biaya HPP',
            default => $this->type,
        };
    }
}
