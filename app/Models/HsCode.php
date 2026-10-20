<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HsCode extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'description',
        'duty_bm',
        'duty_ppn',
        'duty_pph',
        'duty_bk',
        'satuan',
        'has_lartas',
        'lartas_desc',
        'btki',
        'source',
        'valid_from',
        'valid_to',
    ];

    protected $casts = [
        'duty_bm' => 'decimal:2',
        'duty_ppn' => 'decimal:2',
        'duty_pph' => 'decimal:2',
        'duty_bk' => 'decimal:2',
        'has_lartas' => 'boolean',
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    /**
     * Dapatkan kode bersih tanpa titik (8 digit) untuk pengiriman ke CEISA 4.0
     */
    public function getCleanCodeAttribute(): string
    {
        return str_replace(['.', ' ', '-'], '', $this->code);
    }
}
