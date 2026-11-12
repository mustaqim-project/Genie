<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShipmentItem extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'shipment_id',
        'nomor_urut',
        'uraian_barang',
        'kode_hs',
        'hs_confidence_score',
        'hs_legal_basis',
        'negara_asal',
        'jumlah_kemasan',
        'jenis_kemasan',
        'berat_netto',
        'berat_bruto',
        'nilai_fob',
        'tarif_bm_persen',
        'tarif_ppn_persen',
        'tarif_pph_persen',
    ];

    protected $casts = [
        'hs_confidence_score' => 'decimal:2',
        'berat_netto' => 'decimal:2',
        'berat_bruto' => 'decimal:2',
        'nilai_fob' => 'decimal:2',
        'tarif_bm_persen' => 'decimal:2',
        'tarif_ppn_persen' => 'decimal:2',
        'tarif_pph_persen' => 'decimal:2',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function customsDocuments()
    {
        return $this->hasMany(ShipmentItemDocument::class, 'shipment_item_id');
    }

    public function getHasLartasAttribute(): bool
    {
        return $this->customsDocuments()->count() > 0;
    }

    public function getIsCustomsDocumentsCompleteAttribute(): bool
    {
        $docs = $this->customsDocuments;
        if ($docs->isEmpty()) {
            return true;
        }

        foreach ($docs as $doc) {
            if ($doc->is_mandatory && !$doc->isCompleted()) {
                return false;
            }
        }

        return true;
    }

    public function getItemNameAttribute()
    {
        return $this->uraian_barang;
    }

    public function getDescriptionAttribute()
    {
        return $this->hs_legal_basis ?? $this->uraian_barang;
    }

    public function getHsCodeAttribute()
    {
        return $this->kode_hs;
    }

    public function getQuantityAttribute()
    {
        return $this->jumlah_kemasan;
    }

    public function getPackageUnitAttribute()
    {
        return $this->jenis_kemasan;
    }

    public function getGrossWeightAttribute()
    {
        return $this->berat_bruto;
    }

    public function getNetWeightAttribute()
    {
        return $this->berat_netto;
    }

    public function getCifAmountAttribute()
    {
        return $this->nilai_fob;
    }

    public function getCountryOfOriginAttribute()
    {
        return $this->negara_asal;
    }

    /**
     * Normalisasi kode jenis kemasan agar selalu aman untuk kolom VARCHAR(20).
     */
    public static function normalizeJenisKemasan(?string $value, string $default = 'PKG'): string
    {
        $clean = trim((string) ($value ?? ''));
        if ($clean === '') {
            return $default;
        }

        if (mb_strlen($clean) > 20) {
            if (preg_match('/^([A-Za-z0-9\-_\/]+)\s*\(/', $clean, $matches)) {
                $clean = $matches[1];
            }
        }

        return mb_substr($clean, 0, 20);
    }

    public function setJenisKemasanAttribute(?string $value): void
    {
        $this->attributes['jenis_kemasan'] = static::normalizeJenisKemasan($value);
    }
}
