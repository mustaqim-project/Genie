<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BiometricConsent extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'biometric_consents';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'employee_id',
        'consent_at',
        'revoked_at',
        'retention_until',
    ];

    protected function casts(): array
    {
        return [
            'consent_at' => 'datetime',
            'revoked_at' => 'datetime',
            'retention_until' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Memeriksa keabsahan persetujuan biometrik sesuai UU Pelindungan Data Pribadi (UU PDP No. 27/2022).
     */
    public function isValid(): bool
    {
        if (is_null($this->consent_at)) {
            return false;
        }

        if (!is_null($this->revoked_at)) {
            return false;
        }

        if (!is_null($this->retention_until) && $this->retention_until->isPast()) {
            return false;
        }

        return true;
    }

    public function scopeValid($query)
    {
        $today = now()->toDateString();
        return $query->whereNotNull('consent_at')
            ->whereNull('revoked_at')
            ->where(function ($q) use ($today) {
                $q->whereNull('retention_until')
                  ->orWhere('retention_until', '>=', $today);
            });
    }
}
