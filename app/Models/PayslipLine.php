<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayslipLine extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'payslip_lines';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'payslip_id',
        'label',
        'kind',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function payslip(): BelongsTo
    {
        return $this->belongsTo(Payslip::class, 'payslip_id');
    }

    public function isAllowance(): bool
    {
        return $this->kind === 'ALLOWANCE';
    }

    public function isDeduction(): bool
    {
        return $this->kind === 'DEDUCTION';
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format((float) ($this->amount ?? 0), 0, ',', '.');
    }
}
