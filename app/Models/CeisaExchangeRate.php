<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CeisaExchangeRate extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'kode_valuta',
        'nilai_kurs',
        'tanggal_mulai',
        'tanggal_selesai',
        'nomor_kmk',
        'sumber',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'nilai_kurs' => 'decimal:4',
    ];

    /**
     * Scope untuk mencari kurs yang berlaku pada tanggal tertentu
     */
    public function scopeActiveOn($query, ?string $date = null)
    {
        $targetDate = $date ?: now()->format('Y-m-d');
        return $query->where('tanggal_mulai', '<=', $targetDate)
                     ->where('tanggal_selesai', '>=', $targetDate);
    }
}
