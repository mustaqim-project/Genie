<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollRun extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'payroll_runs';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'period',
        'status',
        'approved_by_id',
    ];

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class, 'run_id');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'DRAFT');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'APPROVED');
    }

    public function scopeDisbursed($query)
    {
        return $query->where('status', 'DISBURSED');
    }

    public function getTotalGrossAttribute(): float
    {
        return (float) $this->payslips()->sum('gross');
    }

    public function getTotalNetAttribute(): float
    {
        return (float) $this->payslips()->sum('net');
    }

    public function getTotalDeductionsAttribute(): float
    {
        return (float) $this->payslips()->sum('deductions');
    }

    public function getFormattedPeriodAttribute(): string
    {
        try {
            return Carbon::createFromFormat('Y-m', $this->period)->translatedFormat('F Y');
        } catch (\Exception $e) {
            return $this->period;
        }
    }
}
