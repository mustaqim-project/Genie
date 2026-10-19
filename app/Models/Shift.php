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
    ];

    public function getFormattedHoursAttribute(): string
    {
        return ($this->start_time ?: '--:--') . ' - ' . ($this->end_time ?: '--:--');
    }
}
