<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportBatch extends Model
{
    use HasFactory, HasUuids, Auditable;

    protected $table = 'import_batches';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'batch_no',
        'uploaded_by_id',
        'client_id',
        'type',
        'status',
        'total_files',
        'processed_files',
        'success_count',
        'error_count',
        'notes',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(ImportFile::class, 'batch_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ImportDocument::class, 'batch_id');
    }
}
