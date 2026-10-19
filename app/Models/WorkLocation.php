<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkLocation extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'work_locations';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'lat',
        'lng',
        'radius_m',
        'valid_from',
        'valid_to',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'float',
            'lng' => 'float',
            'radius_m' => 'integer',
            'valid_from' => 'date',
            'valid_to' => 'date',
        ];
    }

    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class, 'location_id');
    }

    public function scopeActive($query)
    {
        $today = now()->toDateString();
        return $query->where(function ($q) use ($today) {
            $q->whereNull('valid_from')
              ->orWhere('valid_from', '<=', $today);
        })->where(function ($q) use ($today) {
            $q->whereNull('valid_to')
              ->orWhere('valid_to', '>=', $today);
        });
    }

    /**
     * Menghitung jarak koordinat GPS terhadap geofencing radius (Haversine formula).
     *
     * @param float $checkLat
     * @param float $checkLng
     * @return bool
     */
    public function isWithinRadius(float $checkLat, float $checkLng): bool
    {
        if (is_null($this->lat) || is_null($this->lng)) {
            return true; // Jika koordinat tidak diset, tidak batasi geofence
        }

        $earthRadius = 6371000; // meter

        $dLat = deg2rad($checkLat - $this->lat);
        $dLng = deg2rad($checkLng - $this->lng);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($this->lat)) * cos(deg2rad($checkLat)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return $distance <= ($this->radius_m ?: 100);
    }
}
