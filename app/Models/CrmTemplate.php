<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CrmTemplate extends Model
{
    use HasUuids;

    protected $table = 'crm_templates';

    protected $fillable = [
        'name',
        'language',
        'category',
        'header_text',
        'body',
        'footer_text',
        'buttons',
        'meta_status',
    ];

    protected $casts = [
        'buttons' => 'array',
    ];
}
