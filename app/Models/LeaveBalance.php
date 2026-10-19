<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'leave_balances';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'employee_id',
        'year',
        'kind',
        'balance',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'balance' => 'float',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function scopeCurrentYear($query)
    {
        return $query->where('year', (int) date('Y'));
    }

    public function deduct(float $days): bool
    {
        if ($this->balance < $days) {
            return false;
        }
        $this->balance -= $days;
        return $this->save();
    }
}
