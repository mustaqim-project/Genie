<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportFile extends Model
{
    use HasFactory, HasUuids, Auditable;

    protected $table = 'import_files';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'batch_id',
        'original_name',
        'file_path',
        'file_size_kb',
        'file_hash',
        'mime_type',
        'detected_type',
        'type_confidence',
        'page_count',
        'status',
        'error_message',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'batch_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ImportDocument::class, 'file_id');
    }
}
