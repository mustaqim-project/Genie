<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
        'npwp',
        'nib',
        'email',
        'phone',
        'pic_name',
        'address',
        'surat_kuasa_expired_at',
        'is_active',
    ];

    protected $casts = [
        'surat_kuasa_expired_at' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Alias atribut nama_perusahaan untuk kompatibilitas MoM ERP
     */
    public function getNamaPerusahaanAttribute(): string
    {
        return $this->name ?? '';
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function invoices()
    {
        return $this->hasMany(ClientInvoice::class);
    }

    public function deposits()
    {
        return $this->hasMany(ClientDeposit::class)->latest('tanggal');
    }

    public function depositUsages()
    {
        return $this->hasMany(ClientDepositUsage::class)->latest('tanggal');
    }

    public function hsCodes()
    {
        return $this->hasMany(ClientHsCode::class)->latest('created_at');
    }

    public function getSaldoDepositAktifAttribute(): float
    {
        return (float) $this->deposits()->where('status', 'CONFIRMED')->sum('saldo_sisa');
    }
}
