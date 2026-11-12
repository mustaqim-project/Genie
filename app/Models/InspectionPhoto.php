<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class InspectionPhoto extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'shipment_id',
        'flow_type',
        'stage',
        'file_path',
        'file_name',
        'file_size_kb',
        'mime_type',
        'seal_number',
        'caption',
        'taken_at',
        'uploaded_by',
    ];

    protected $casts = [
        'taken_at' => 'datetime',
        'file_size_kb' => 'integer',
    ];

    public const IMPORT_STAGES = [
        'BEFORE_UNLOAD' => '1. Sebelum Bongkar (Kontainer & Segel Awal)',
        'UNLOADING' => '2. Proses Bongkar / Behandle Bea Cukai',
        'GOODS_INSPECTION' => '3. Foto Pengecekan Fisik Barang',
        'RESEALING' => '4. Foto Segel Kembali (Segel Baru BC)',
        'INSPECTION_REPORT' => '5. Berita Acara Pemeriksaan / SLIM',
    ];

    public const EXPORT_STAGES = [
        'EMPTY_CONTAINER' => '1. Kontainer Kosong Sebelum Muat',
        'STUFFING_PROCESS' => '2. Proses Stuffing / Pemuatan Barang',
        'FULL_CONTAINER' => '3. Kontainer Penuh Sebelum Ditutup',
        'BC_SEAL' => '4. Foto Segel Kembali / Segel Bea Cukai',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): ?string
    {
        if ($this->file_path && Storage::disk('public')->exists($this->file_path)) {
            return Storage::disk('public')->url($this->file_path);
        }
        return null;
    }

    public function getStageLabelAttribute(): string
    {
        return self::IMPORT_STAGES[$this->stage]
            ?? self::EXPORT_STAGES[$this->stage]
            ?? $this->stage;
    }
}
