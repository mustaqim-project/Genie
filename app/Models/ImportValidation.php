<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportValidation extends Model
{
    use HasFactory, HasUuids, Auditable;

    protected $table = 'import_validations';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'document_id',
        'rule_code',
        'severity',
        'message',
        'is_passed',
    ];

    protected $casts = [
        'is_passed' => 'boolean',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(ImportDocument::class, 'document_id');
    }
}
