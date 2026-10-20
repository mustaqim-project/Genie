<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceCorrection extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'attendance_corrections';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'employee_id',
        'attendance_id',
        'correction_date',
        'proposed_clock_in',
        'proposed_clock_out',
        'reason',
        'status',
        'approver_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'correction_date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(AttendanceLog::class, 'attendance_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }
}
