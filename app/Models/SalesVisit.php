<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesVisit extends Model
{
    use HasFactory, HasUuids, SoftDeletes, Auditable;

    protected $table = 'sales_visits';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'sales_user_id',
        'client_id',
        'check_in_time',
        'check_out_time',
        'latitude',
        'longitude',
        'distance_meters',
        'is_out_of_radius',
        'out_of_radius_reason',
        'photo_path',
        'result_status',
        'notes',
    ];

    protected $casts = [
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'distance_meters' => 'decimal:2',
        'is_out_of_radius' => 'boolean',
    ];

    public function salesUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_user_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
