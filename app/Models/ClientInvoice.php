<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientInvoice extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'invoice_no',
        'shipment_id',
        'client_id',
        'tanggal',
        'jatuh_tempo',
        'talangan_pabean_idr',
        'biaya_pelayaran_idr',
        'jasa_ppjk_idr',
        'biaya_trucking_idr',
        'subtotal_idr',
        'ppn_jasa_idr',
        'total_tagihan_idr',
        'status',
        'paid_at',
        'catatan_pembayaran',
        'created_by_user_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jatuh_tempo' => 'date',
        'paid_at' => 'datetime',
        'talangan_pabean_idr' => 'decimal:2',
        'biaya_pelayaran_idr' => 'decimal:2',
        'jasa_ppjk_idr' => 'decimal:2',
        'biaya_trucking_idr' => 'decimal:2',
        'subtotal_idr' => 'decimal:2',
        'ppn_jasa_idr' => 'decimal:2',
        'total_tagihan_idr' => 'decimal:2',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function lines()
    {
        return $this->hasMany(ClientInvoiceLine::class, 'invoice_id');
    }

    public function getInvoiceNumberAttribute()
    {
        return $this->attributes['invoice_no'] ?? null;
    }

    public function getInvoiceDateAttribute()
    {
        return $this->tanggal;
    }

    public function getDueDateAttribute()
    {
        return $this->jatuh_tempo;
    }

    public function getReimbursableAmountAttribute()
    {
        return $this->talangan_pabean_idr;
    }

    public function getTotalAmountAttribute()
    {
        return $this->total_tagihan_idr;
    }

    public static function generateInvoiceNo(): string
    {
        $prefix = 'INV-' . date('Ym') . '-';
        $latest = self::where('invoice_no', 'like', "{$prefix}%")->latest('created_at')->first();
        $next = $latest ? ((int) substr($latest->invoice_no, -4) + 1) : 1;

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
