<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportDocument extends Model
{
    use HasFactory, HasUuids, Auditable;

    protected $table = 'import_documents';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'file_id',
        'batch_id',
        'shipment_id',
        'document_type',
        'page_range',
        'language',
        'review_status',
        'reviewed_by_id',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function file(): BelongsTo
    {
        return $this->belongsTo(ImportFile::class, 'file_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'batch_id');
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class, 'shipment_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_id');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(ImportField::class, 'document_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ImportItem::class, 'document_id');
    }

    public function validations(): HasMany
    {
        return $this->hasMany(ImportValidation::class, 'document_id');
    }
}
