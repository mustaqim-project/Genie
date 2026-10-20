<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLog extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'attendance_logs';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'employee_id',
        'kind',
        'at',
        'lat',
        'lng',
        'location_id',
        'face_match',
        'valid',
        'photo_url',
        'notes',
        'is_geofenced',
        'distance_m',
        'device_info',
    ];

    protected function casts(): array
    {
        return [
            'at' => 'datetime',
            'lat' => 'float',
            'lng' => 'float',
            'face_match' => 'float',
            'valid' => 'boolean',
            'is_geofenced' => 'boolean',
            'distance_m' => 'integer',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(WorkLocation::class, 'location_id');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('at', now()->toDateString());
    }

    public function scopeClockIn($query)
    {
        return $query->where('kind', 'CLOCK_IN');
    }

    public function scopeClockOut($query)
    {
        return $query->where('kind', 'CLOCK_OUT');
    }

    public function scopeValid($query)
    {
        return $query->where('valid', true);
    }

    public function getFormattedTimeAttribute(): string
    {
        return $this->at ? $this->at->format('H:i:s') : '--:--:--';
    }

    public function getFaceMatchPercentageAttribute(): string
    {
        if (is_null($this->face_match)) {
            return 'N/A';
        }
        return number_format($this->face_match * 100, 1) . '%';
    }
}
