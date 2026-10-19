<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payslip extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'payslips';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'run_id',
        'employee_id',
        'gross',
        'net',
        'deductions',
    ];

    protected function casts(): array
    {
        return [
            'gross' => 'decimal:2',
            'net' => 'decimal:2',
            'deductions' => 'decimal:2',
        ];
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class, 'run_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PayslipLine::class, 'payslip_id');
    }

    public function allowances(): HasMany
    {
        return $this->hasMany(PayslipLine::class, 'payslip_id')->where('kind', 'ALLOWANCE');
    }

    public function deductionLines(): HasMany
    {
        return $this->hasMany(PayslipLine::class, 'payslip_id')->where('kind', 'DEDUCTION');
    }

    public function getFormattedGrossAttribute(): string
    {
        return 'Rp ' . number_format((float) ($this->gross ?? 0), 0, ',', '.');
    }

    public function getFormattedNetAttribute(): string
    {
        return 'Rp ' . number_format((float) ($this->net ?? 0), 0, ',', '.');
    }

    public function getFormattedDeductionsAttribute(): string
    {
        return 'Rp ' . number_format((float) ($this->deductions ?? 0), 0, ',', '.');
    }
}
