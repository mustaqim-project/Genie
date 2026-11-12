<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StagePrerequisite extends Model
{
    use HasFactory, HasUuids, Auditable;

    protected $table = 'stage_prerequisites';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'stage_code',
        'stage_name',
        'stage_number',
        'required_fields',
        'required_documents',
        'is_approval_required',
        'approver_role',
    ];

    protected $casts = [
        'stage_number' => 'integer',
        'required_fields' => 'array',
        'required_documents' => 'array',
        'is_approval_required' => 'boolean',
    ];
}
