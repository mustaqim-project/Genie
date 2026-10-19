<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShipmentDocument extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'shipment_id',
        'jenis_dokumen',
        'nama_file',
        'file_path',
        'file_size_kb',
        'status',
        'verified_by_user_id',
        'verified_at',
        'catatan',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function getDocumentTypeAttribute(): string
    {
        return $this->jenis_dokumen;
    }

    public function getFileNameAttribute(): ?string
    {
        return $this->nama_file;
    }

    public function getFileSizeAttribute(): ?int
    {
        return $this->file_size_kb ? (int) ($this->file_size_kb * 1024) : null;
    }

    public function getFileUrlAttribute(): ?string
    {
        if ($this->file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->file_path)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->url($this->file_path);
        }
        return null;
    }

    public function getTitleLabelAttribute(): string
    {
        $labels = [
            'INVOICE' => 'Commercial Invoice Pemasok Luar Negeri',
            'PACKING_LIST' => 'Packing List Rincian Barang & Kemasan',
            'BL_AWB' => 'Bill of Lading (B/L) Asli / Sea Waybill',
            'COO_FORME' => 'Certificate of Origin (Form E / Form AK / SKA)',
            'INSURANCE' => 'Polis Sertifikat Asuransi Pengangkutan',
            'PERIJINAN_LARTAS' => 'Persetujuan Impor / LS Surveyor / BPOM (Jika Lartas)',
            'DO_ORIGINAL' => 'Delivery Order (DO) Pelayaran Rilis Resmi',
            'BUKTI_BAYAR_BILLING' => 'Bukti Penerimaan Negara (BPN) Billing MPN G3',
            'POD_SURAT_JALAN' => 'Bukti Pengantaran Gudang / Surat Jalan (POD)',
        ];

        return $labels[$this->jenis_dokumen] ?? $this->jenis_dokumen;
    }
}
