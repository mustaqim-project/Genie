<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'shifts';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'grace_period_minutes',
        'is_night_shift',
    ];

    protected function casts(): array
    {
        return [
            'grace_period_minutes' => 'integer',
            'is_night_shift' => 'boolean',
        ];
    }

    public function schedules()
    {
        return $this->hasMany(EmployeeSchedule::class, 'shift_id');
    }

    public function getFormattedHoursAttribute(): string
    {
        return ($this->start_time ?: '--:--') . ' - ' . ($this->end_time ?: '--:--');
    }
}
