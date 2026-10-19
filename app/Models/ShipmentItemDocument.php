<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ShipmentItemDocument extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'shipment_id',
        'shipment_item_id',
        'kode_dokumen',
        'nama_dokumen',
        'instansi_penerbit',
        'nomor_dokumen',
        'tanggal_dokumen',
        'file_name',
        'file_path',
        'status',
        'is_mandatory',
        'catatan',
        'verified_by_user_id',
        'verified_at',
    ];

    protected $casts = [
        'tanggal_dokumen' => 'date',
        'is_mandatory' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function item()
    {
        return $this->belongsTo(ShipmentItem::class, 'shipment_item_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function isCompleted(): bool
    {
        return in_array($this->status, ['VERIFIED', 'UPLOADED']) && !empty($this->nomor_dokumen);
    }

    public function getFileUrlAttribute(): ?string
    {
        if ($this->file_path && Storage::disk('public')->exists($this->file_path)) {
            return Storage::disk('public')->url($this->file_path);
        }
        return null;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'VERIFIED' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'UPLOADED' => 'bg-sky-100 text-sky-800 border-sky-300',
            'REJECTED' => 'bg-rose-100 text-rose-800 border-rose-300',
            default => 'bg-amber-100 text-amber-800 border-amber-300',
        };
    }
}
