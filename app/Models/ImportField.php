<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportField extends Model
{
    use HasFactory, HasUuids, Auditable;

    protected $table = 'import_fields';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'document_id',
        'field_key',
        'field_label',
        'original_value',
        'corrected_value',
        'confidence_score',
        'page_number',
        'bounding_box',
        'status',
        'edited_by_id',
        'edited_at',
    ];

    protected $casts = [
        'confidence_score' => 'decimal:2',
        'bounding_box' => 'array',
        'edited_at' => 'datetime',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(ImportDocument::class, 'document_id');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by_id');
    }

    /**
     * Mendapatkan nilai aktif (nilai koreksi jika ada, atau nilai asli)
     */
    public function getEffectiveValueAttribute(): ?string
    {
        return $this->corrected_value ?? $this->original_value;
    }
}
