<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Asset extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'assets';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
        'status',
        'purchase_value',
    ];

    protected function casts(): array
    {
        return [
            'purchase_value' => 'decimal:2',
        ];
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class, 'asset_id');
    }

    public function currentAssignment(): HasOne
    {
        return $this->hasOne(AssetAssignment::class, 'asset_id')->whereNull('returned_at');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'AVAILABLE');
    }

    public function scopeAssigned($query)
    {
        return $query->where('status', 'ASSIGNED');
    }

    public function getFormattedValueAttribute(): string
    {
        return 'Rp ' . number_format((float) ($this->purchase_value ?? 0), 0, ',', '.');
    }
}
