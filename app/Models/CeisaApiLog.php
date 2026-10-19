<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CeisaApiLog extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'nomor_aju',
        'endpoint',
        'method',
        'status_code',
        'request_header',
        'request_body',
        'response_body',
        'duration_ms',
        'is_success',
    ];

    protected $casts = [
        'is_success' => 'boolean',
        'duration_ms' => 'integer',
        'status_code' => 'integer',
    ];

    public function document()
    {
        return $this->belongsTo(CeisaDocument::class, 'nomor_aju', 'nomor_aju');
    }
}
