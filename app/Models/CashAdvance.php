<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashAdvance extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'cash_advances';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'employee_id',
        'amount',
        'installments',
        'balance',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'balance' => 'decimal:2',
            'installments' => 'integer',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }

    public function scopeCleared($query)
    {
        return $query->where('status', 'CLEARED');
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format((float) ($this->amount ?? 0), 0, ',', '.');
    }

    public function getFormattedBalanceAttribute(): string
    {
        return 'Rp ' . number_format((float) ($this->balance ?? 0), 0, ',', '.');
    }

    /**
     * Potong sisa kasbon pada saat payroll run.
     */
    public function deductInstallment(float $deductionAmount): void
    {
        $this->balance = max(0, (float) $this->balance - $deductionAmount);
        if ($this->balance <= 0) {
            $this->status = 'CLEARED';
        }
        $this->save();
    }
}
