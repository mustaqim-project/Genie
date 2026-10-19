<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shipment extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'project_no',
        'client_id',
        'jenis_layanan',
        'nomor_bl_awb',
        'tgl_bl_awb',
        'shipping_vendor_id',
        'nama_kapal',
        'voyage_no',
        'pelabuhan_muat',
        'pelabuhan_bongkar',
        'eta_date',
        'actual_arrival_date',
        'total_kontainer',
        'nomor_kontainer',
        'total_kemasan',
        'jenis_kemasan',
        'total_bruto',
        'total_netto',
        'kode_valuta',
        'total_nilai_valuta',
        'ndbm_kurs',
        'total_nilai_idr',
        'free_time_days',
        'free_time_expired_at',
        'do_expired_at',
        'nomor_do',
        'status_do',
        'do_email_sent_at',
        'do_email_recipient',
        'ceisa_document_id',
        'status_tahap',
        'catatan',
        'created_by_user_id',
    ];

    protected $casts = [
        'tgl_bl_awb' => 'date',
        'eta_date' => 'datetime',
        'actual_arrival_date' => 'datetime',
        'free_time_expired_at' => 'datetime',
        'do_expired_at' => 'datetime',
        'do_email_sent_at' => 'datetime',
        'total_nilai_valuta' => 'decimal:2',
        'total_nilai_idr' => 'decimal:2',
        'ndbm_kurs' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function vendor()
    {
        return $this->belongsTo(ShippingVendor::class, 'shipping_vendor_id');
    }

    public function shippingVendor()
    {
        return $this->belongsTo(ShippingVendor::class, 'shipping_vendor_id');
    }

    public function documents()
    {
        return $this->hasMany(ShipmentDocument::class);
    }

    public function items()
    {
        return $this->hasMany(ShipmentItem::class);
    }

    public function itemCustomsDocuments()
    {
        return $this->hasMany(ShipmentItemDocument::class);
    }

    public function getIsAllLartasDocumentsCompleteAttribute(): bool
    {
        $docs = $this->itemCustomsDocuments()->where('is_mandatory', true)->get();
        if ($docs->isEmpty()) {
            return true;
        }

        foreach ($docs as $doc) {
            if (!$doc->isCompleted()) {
                return false;
            }
        }

        return true;
    }

    public function ceisaDocument()
    {
        return $this->belongsTo(CeisaDocument::class, 'ceisa_document_id');
    }

    public function invoice()
    {
        return $this->hasOne(ClientInvoice::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function pics()
    {
        return $this->hasMany(ShipmentPic::class, 'shipment_id');
    }

    public function getShipmentNumberAttribute()
    {
        return $this->attributes['project_no'] ?? null;
    }

    public function getBlNumberAttribute()
    {
        return $this->attributes['nomor_bl_awb'] ?? null;
    }

    public function getVesselNameAttribute()
    {
        return $this->attributes['nama_kapal'] ?? null;
    }

    public function getVoyageNumberAttribute()
    {
        return $this->attributes['voyage_no'] ?? null;
    }

    public function getPortOfLoadingAttribute()
    {
        return $this->attributes['pelabuhan_muat'] ?? null;
    }

    public function getPortOfDischargeAttribute()
    {
        return $this->attributes['pelabuhan_bongkar'] ?? null;
    }

    public function getCurrencyAttribute()
    {
        return $this->attributes['kode_valuta'] ?? 'USD';
    }

    public function getTotalCifAttribute()
    {
        return $this->attributes['total_nilai_valuta'] ?? 0;
    }

    public function getEtaAttribute()
    {
        return $this->eta_date;
    }

    /**
     * Hitung sisa hari free time sebelum demurrage
     */
    public function getDaysLeftFreeTimeAttribute(): ?int
    {
        if (!$this->free_time_expired_at) {
            return null;
        }

        return (int) now()->diffInDays($this->free_time_expired_at, false);
    }

    /**
     * Cek apakah mendekati denda demurrage (<= 2 hari)
     */
    public function getIsDemurrageWarningAttribute(): bool
    {
        $days = $this->days_left_free_time;
        return $days !== null && $days <= 2;
    }

    /**
     * Cek kelengkapan dokumen wajib
     */
    public function isDocumentComplete(): bool
    {
        $mandatoryTypes = ['INVOICE', 'PACKING_LIST', 'BL_AWB'];
        $verifiedCount = $this->documents()
            ->whereIn('jenis_dokumen', $mandatoryTypes)
            ->where('status', 'VERIFIED')
            ->count();

        return $verifiedCount >= count($mandatoryTypes);
    }

    public function getTypeAttribute(): string
    {
        return ($this->attributes['jenis_layanan'] ?? 'IMPORT_BC20') === 'EKSPORT_BC30' ? 'EXPORT' : 'IMPORT';
    }

    public function getStatusAttribute(): string
    {
        return $this->attributes['status_tahap'] ?? 'DOC_COLLECTING';
    }

    public function getBillingCodeAttribute(): ?string
    {
        return $this->ceisaDocument?->kode_billing;
    }

    public function getDocumentCompletionRateAttribute(): int
    {
        $total = $this->documents()->count();
        if ($total === 0) {
            return 0;
        }
        $verified = $this->documents()->where('status', 'VERIFIED')->count();
        return (int) round(($verified / $total) * 100);
    }

    public function getIsAllMandatoryDocumentsVerifiedAttribute(): bool
    {
        $mandatory = ['INVOICE', 'PACKING_LIST', 'BL_AWB'];
        $count = $this->documents()
            ->whereIn('jenis_dokumen', $mandatory)
            ->where('status', 'VERIFIED')
            ->count();
        return $count >= count($mandatory);
    }

    /**
     * Audit Three-Way Matching (Commercial Invoice vs Packing List vs Bill of Lading)
     */
    public function getThreeWayMatchingAttribute(): array
    {
        $itemsFob = (float) $this->items()->sum('nilai_fob');
        $itemsBruto = (float) $this->items()->sum('berat_bruto');
        $itemsNetto = (float) $this->items()->sum('berat_netto');
        $itemsPackages = (int) $this->items()->sum('jumlah_kemasan');

        $cifDeclared = (float) ($this->total_nilai_valuta ?? 0);
        $brutoDeclared = (float) ($this->total_bruto ?? 0);
        $nettoDeclared = (float) ($this->total_netto ?? 0);
        $packagesDeclared = (int) ($this->total_kemasan ?? 0);

        // Jika items ada, bandingkan dengan declared shipment
        $valueMatch = $itemsFob <= 0 || abs($cifDeclared - $itemsFob) < 0.01;
        $weightMatch = $itemsBruto <= 0 || abs($brutoDeclared - $itemsBruto) < 1.0;
        $packagesMatch = $itemsPackages <= 0 || $packagesDeclared === $itemsPackages;

        $hasInvoice = $this->documents()->where('jenis_dokumen', 'INVOICE')->whereIn('status', ['UPLOADED', 'VERIFIED'])->exists();
        $hasPL = $this->documents()->where('jenis_dokumen', 'PACKING_LIST')->whereIn('status', ['UPLOADED', 'VERIFIED'])->exists();
        $hasBL = $this->documents()->where('jenis_dokumen', 'BL_AWB')->whereIn('status', ['UPLOADED', 'VERIFIED'])->exists();

        $allDocsPresent = $hasInvoice && $hasPL && $hasBL;
        $isFullyMatched = $allDocsPresent && $valueMatch && $weightMatch && $packagesMatch;

        return [
            'is_matched' => $isFullyMatched,
            'all_docs_present' => $allDocsPresent,
            'has_invoice' => $hasInvoice,
            'has_packing_list' => $hasPL,
            'has_bl' => $hasBL,
            'value_match' => $valueMatch,
            'weight_match' => $weightMatch,
            'packages_match' => $packagesMatch,
            'items_fob' => $itemsFob,
            'declared_cif' => $cifDeclared,
            'items_bruto' => $itemsBruto,
            'declared_bruto' => $brutoDeclared,
            'items_packages' => $itemsPackages,
            'declared_packages' => $packagesDeclared,
        ];
    }

    /**
     * Hitung Estimasi Pungutan Impor (Nilai Pabean, BM, PPN, PPh)
     */
    public function getEstimatedTaxesAttribute(): array
    {
        $kurs = (float) ($this->ndbm_kurs ?? 16200);
        $cifValuta = (float) ($this->total_nilai_valuta ?? 0);
        $cifIdr = $cifValuta * $kurs;

        $totalBm = 0;
        $totalPpn = 0;
        $totalPph = 0;

        $items = $this->items;
        if ($items->isNotEmpty()) {
            foreach ($items as $it) {
                $itemCifIdr = (float) ($it->nilai_fob ?? 0) * $kurs;
                $bm = $itemCifIdr * (((float) ($it->tarif_bm_persen ?? 5.0)) / 100);
                $nilaiImpor = $itemCifIdr + $bm;
                $ppn = $nilaiImpor * (((float) ($it->tarif_ppn_persen ?? 11.0)) / 100);
                $pph = $nilaiImpor * (((float) ($it->tarif_pph_persen ?? 2.5)) / 100);

                $totalBm += $bm;
                $totalPpn += $ppn;
                $totalPph += $pph;
            }
        } else {
            // Default rate bila belum ada item
            $totalBm = $cifIdr * 0.05;
            $nilaiImpor = $cifIdr + $totalBm;
            $totalPpn = $nilaiImpor * 0.11;
            $totalPph = $nilaiImpor * 0.025;
        }

        $totalBilling = $totalBm + $totalPpn + $totalPph;

        return [
            'kurs' => $kurs,
            'cif_valuta' => $cifValuta,
            'cif_idr' => round($cifIdr),
            'bea_masuk' => round($totalBm),
            'nilai_impor' => round($cifIdr + $totalBm),
            'ppn' => round($totalPpn),
            'pph' => round($totalPph),
            'total_billing' => round($totalBilling),
        ];
    }

    /**
     * Generator Nomor Project / Shipment Standar
     */
    public static function generateProjectNo(): string
    {
        $prefix = 'SHP-' . date('Ym') . '-';
        $existing = self::where('project_no', 'like', "{$prefix}%")->pluck('project_no')->toArray();
        $maxNum = 0;
        foreach ($existing as $pNo) {
            $num = (int) substr($pNo, strlen($prefix));
            if ($num > $maxNum) {
                $maxNum = $num;
            }
        }

        return $prefix . str_pad($maxNum + 1, 4, '0', STR_PAD_LEFT);
    }
}
