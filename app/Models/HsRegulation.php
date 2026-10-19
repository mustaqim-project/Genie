<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HsRegulation extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'hs_from',
        'hs_to',
        'origin_country_id',
        'doc_condition',
        'required_doc',
        'source_regulation',
        'verified',
        'valid_from',
        'valid_to',
    ];

    protected $casts = [
        'doc_condition' => 'array',
        'verified' => 'boolean',
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];
}
