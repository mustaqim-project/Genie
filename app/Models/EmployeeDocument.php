<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocument extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'employee_documents';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'employee_id',
        'doc_type',
        'file_id',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(ShipmentDocument::class, 'file_id');
    }

    public function getDocTypeLabelAttribute(): string
    {
        return match ($this->doc_type) {
            'KTP' => 'KTP (Identitas Kependudukan)',
            'NPWP' => 'NPWP (Perpajakan)',
            'BPJS_KES' => 'BPJS Kesehatan',
            'BPJS_TK' => 'BPJS Ketenagakerjaan',
            'PPJK_CERT' => 'Sertifikat Ahli Kepabeanan (PPJK)',
            'IJAZAH' => 'Ijazah Terakhir',
            'SIM_B2' => 'SIM B2 Umum (Driver/Field)',
            default => strtoupper(str_replace('_', ' ', $this->doc_type)),
        };
    }
}
