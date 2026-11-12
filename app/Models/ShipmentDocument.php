<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShipmentDocument extends Model
{
    use HasFactory, HasUuids, SoftDeletes, Auditable;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'shipment_id',
        'jenis_dokumen',
        'nama_file',
        'file_path',
        'file_size_kb',
        'status',
        'version',
        'is_current',
        'verified_by_user_id',
        'verified_at',
        'catatan',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'version' => 'integer',
        'is_current' => 'boolean',
    ];

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

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

    public const DOC_TYPES = [
        'BL' => 'Bill of Lading / AWB',
        'BL_AWB' => 'Bill of Lading (B/L) Asli / Sea Waybill',
        'INV' => 'Commercial Invoice',
        'INVOICE' => 'Commercial Invoice Pemasok Luar Negeri',
        'PL' => 'Packing List',
        'PACKING_LIST' => 'Packing List Rincian Barang & Kemasan',
        'COO' => 'Certificate of Origin (COO / SKA)',
        'COO_FORME' => 'Certificate of Origin (Form E / Form AK / SKA)',
        'SC_PO' => 'Sales Contract / Purchase Order (SC/PO)',
        'INS' => 'Marine Cargo Insurance',
        'INSURANCE' => 'Polis Sertifikat Asuransi Pengangkutan',
        'BOOKING_RECEIPT' => 'Booking Confirmation / Shipping Instruction (Ekspor)',
        'LARTAS' => 'Perizinan Lartas / LS / PI / SNI',
        'PERIJINAN_LARTAS' => 'Persetujuan Impor / LS Surveyor / BPOM (Jika Lartas)',
        'ETC' => 'Dokumen Pendukung Lainnya (ETC / COA / MSDS)',
        'DO' => 'Delivery Order (DO) Pelayaran',
        'DO_ORIGINAL' => 'Delivery Order (DO) Pelayaran Rilis Resmi',
        'BUKTI_BAYAR_BILLING' => 'Bukti Penerimaan Negara (BPN) Billing MPN G3',
        'SPJM' => 'Surat Pemberitahuan Jalur Merah (SPJM) & Tiket Behandle',
        'SPPB' => 'Surat Persetujuan Pengeluaran Barang (SPPB)',
        'NPD' => 'Nota Pemberitahuan Dokumen (NPD) Bea Cukai',
        'DNP' => 'Daftar Nilai Pabean (DNP) Bea Cukai',
        'NHI' => 'Nota Hasil Intelijen (NHI)',
        'SLIM' => 'Dokumen Upload SLIM / Surat Tugas Pemeriksaan',
        'BERITA_ACARA' => 'Berita Acara Pemeriksaan Fisik & Segel Bea Cukai',
        'POD_SURAT_JALAN' => 'Bukti Pengantaran Gudang / Surat Jalan (POD)',
    ];

    public function getTitleLabelAttribute(): string
    {
        return self::DOC_TYPES[$this->jenis_dokumen] ?? $this->jenis_dokumen;
    }
}
