<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'quotation_no',
        'client_id',
        'tanggal',
        'valid_until',
        'pelabuhan_asal',
        'pelabuhan_tujuan',
        'term_of_shipment',
        'rincian_biaya',
        'subtotal_idr',
        'ppn_idr',
        'total_idr',
        'status',
        'syarat_ketentuan',
        'created_by_user_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'valid_until' => 'date',
        'rincian_biaya' => 'array',
        'subtotal_idr' => 'decimal:2',
        'ppn_idr' => 'decimal:2',
        'total_idr' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function getQuotationNumberAttribute()
    {
        return $this->attributes['quotation_no'] ?? null;
    }

    public function getIssueDateAttribute()
    {
        return $this->tanggal;
    }

    public function getOriginPortAttribute()
    {
        return $this->pelabuhan_asal;
    }

    public function getDestinationPortAttribute()
    {
        return $this->pelabuhan_tujuan;
    }

    public function getTotalEstimateAttribute()
    {
        return $this->total_idr;
    }

    public static function generateQuotationNo(): string
    {
        $prefix = 'QUO-' . date('Ym') . '-';
        $latest = self::where('quotation_no', 'like', "{$prefix}%")->latest('created_at')->first();
        $next = $latest ? ((int) substr($latest->quotation_no, -4) + 1) : 1;

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
