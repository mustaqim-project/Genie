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

    public function getNamaPerusahaanAttribute(): string
    {
        return $this->name ?? '';
    }
}
