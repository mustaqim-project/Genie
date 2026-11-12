<?php

namespace App\Models;

use App\Traits\Auditable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shipment extends Model
{
    use HasFactory, HasUuids, SoftDeletes, Auditable;

    protected static function booted()
    {
        static::addGlobalScope('client_access', function (\Illuminate\Database\Eloquent\Builder $builder) {
            if (auth()->check()) {
                $user = auth()->user();
                // Eksekutif, SuperAdmin, Manager, dan Tim Operasional Dokumen/DO/Traffic bebas melihat semua shipment
                if (
                    $user->isSuperAdmin()
                    || $user->hasRole(['R-ADM', 'R-OWN', 'R-MGR', 'R-DOC', 'R-DOC-SPV', 'R-DO', 'R-TRF', 'R-SYS'])
                ) {
                    return;
                }

                // Peran lainnya hanya melihat shipment yang dia buat atau ditugaskan sebagai PIC
                $builder->where(function ($query) use ($user) {
                    $query->where('created_by_user_id', $user->id)
                          ->orWhereHas('pics', function ($aq) use ($user) {
                              $aq->where('user_id', $user->id);
                          });
                });
            }
        });
    }

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
        'doc_received_at',
        'doc_completed_at',
        'etd_date',
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
        'customs_channel',
        'trucking_vendor_reason',
        'trucking_vendor_name',
        'last_work_status',
        'odoo_synced',
        'physical_doc_archived',
        'spjm_received',
        'sppb_received',
        'npd_received',
        'dnp_received',
        'nhi_received',
        'spjm_ticket',
        'spjm_received_at',
        'inspection_scheduled_at',
        'old_seal_number',
        'new_seal_number',
        'invoice_jasa_no',
        'invoice_reimburse_no',
        'commodity_summary',
        'tps_location',
        'customs_hold_steps',
        'pod_signed_at',
        'delivery_date',
        'closed_at',
        'catatan',
        'portal_token',
        'portal_expires_at',
        'portal_last_accessed_at',
        'portal_duration_days',
        'created_by_user_id',
    ];

    protected $casts = [
        'tgl_bl_awb' => 'date',
        'eta_date' => 'datetime',
        'doc_received_at' => 'date',
        'doc_completed_at' => 'date',
        'etd_date' => 'datetime',
        'actual_arrival_date' => 'datetime',
        'free_time_expired_at' => 'datetime',
        'do_expired_at' => 'datetime',
        'do_email_sent_at' => 'datetime',
        'pod_signed_at' => 'datetime',
        'delivery_date' => 'date',
        'spjm_received_at' => 'datetime',
        'inspection_scheduled_at' => 'datetime',
        'closed_at' => 'datetime',
        'portal_expires_at' => 'datetime',
        'portal_last_accessed_at' => 'datetime',
        'customs_hold_steps' => 'array',
        'odoo_synced' => 'boolean',
        'physical_doc_archived' => 'boolean',
        'spjm_received' => 'boolean',
        'sppb_received' => 'boolean',
        'npd_received' => 'boolean',
        'dnp_received' => 'boolean',
        'nhi_received' => 'boolean',
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
        return $this->hasMany(ShipmentDocument::class)->where('is_current', true);
    }

    public function allDocuments()
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

    /**
     * Hitung ringkasan status Lartas per barang dan dokumen
     */
    public function getLartasInfoAttribute(): array
    {
        $items = $this->items ?? collect();
        $totalProducts = $items->count();
        $lartasItems = [];
        $nonLartasItems = [];

        // Fallback ke ceisaDocument jika relasi item kosong tetapi ada ceisaDocument
        if ($totalProducts === 0 && $this->ceisaDocument) {
            return $this->ceisaDocument->lartas_info;
        }

        $hsService = app(\App\Services\Ceisa\CeisaHsCodeService::class);

        foreach ($items as $item) {
            $check = !empty($item->kode_hs) 
                ? $hsService->getTariffAndLartas((string)$item->kode_hs) 
                : ['has_lartas' => false, 'lartas' => 'Bebas Lartas'];

            $hasLartas = ($item->customsDocuments && $item->customsDocuments->count() > 0)
                || ($check['has_lartas'] ?? false);

            $itemData = [
                'id' => $item->id,
                'posTarif' => $item->kode_hs,
                'uraian' => $item->uraian_barang,
                'has_lartas' => $hasLartas,
                'lartas_info' => $check['lartas'] ?? ($hasLartas ? 'Wajib Dokumen Perizinan Lartas' : 'Bebas Lartas'),
            ];

            if ($hasLartas) {
                $lartasItems[] = $itemData;
            } else {
                $nonLartasItems[] = $itemData;
            }
        }

        return [
            'has_lartas' => count($lartasItems) > 0,
            'total_products' => $totalProducts,
            'lartas_count' => count($lartasItems),
            'non_lartas_count' => count($nonLartasItems),
            'lartas_items' => $lartasItems,
            'non_lartas_items' => $nonLartasItems,
            'primary_lartas' => count($lartasItems) > 0 ? $lartasItems[0]['lartas_info'] : 'Bebas Lartas (Non-Lartas)',
        ];
    }

    public function ceisaDocument()
    {
        return $this->belongsTo(CeisaDocument::class, 'ceisa_document_id');
    }

    public function invoice()
    {
        return $this->hasOne(ClientInvoice::class);
    }

    public function invoices()
    {
        return $this->hasMany(ClientInvoice::class);
    }

    public function costItems()
    {
        return $this->hasMany(CostItem::class);
    }

    public function vendorBills()
    {
        return $this->hasMany(VendorBill::class);
    }

    public function inspectionPhotos()
    {
        return $this->hasMany(InspectionPhoto::class, 'shipment_id')->orderBy('created_at');
    }

    public function operationCosts()
    {
        return $this->hasMany(ShipmentOperationCost::class, 'shipment_id')->orderBy('created_at');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Relasi ke penugasan tim PIC per pos/tahap shipment.
     */
    public function pics()
    {
        return $this->hasMany(ShipmentPic::class, 'shipment_id');
    }

    /**
     * Relasi ke jejak audit / riwayat aktivitas pengerjaan shipment.
     */
    public function events()
    {
        return $this->hasMany(ShipmentEvent::class, 'shipment_id')->orderBy('created_at', 'desc');
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

    public function getDoIsReleasedAttribute(): bool
    {
        return !empty($this->nomor_do) || ($this->status_do ?? '') === 'DO_RELEASED' || ($this->status_tahap ?? '') === 'DO_RELEASED';
    }

    public function getDoNumberAttribute(): ?string
    {
        return $this->nomor_do;
    }

    public function getDaysUntilFreeTimeExpiresAttribute(): int
    {
        if ($this->free_time_expired_at) {
            return max(0, (int) now()->diffInDays($this->free_time_expired_at, false));
        }
        return (int) ($this->free_time_days ?? 7);
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
            'currency' => $this->kode_valuta ?? 'USD',
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
        $valuta = $this->kode_valuta ?? 'USD';
        $kurs = (float) ($this->ndbm_kurs ?? 0);
        if ($kurs <= 0) {
            $rates = [
                'USD' => 15850.00,
                'EUR' => 17200.00,
                'CNY' => 2200.00,
                'JPY' => 105.00,
                'SGD' => 12100.00,
                'AUD' => 10400.00,
                'MYR' => 3600.00,
                'GBP' => 20200.00,
            ];
            $kurs = $rates[$valuta] ?? 15850.00;
        }

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
        $existing = self::withTrashed()->where('project_no', 'like', "{$prefix}%")->pluck('project_no')->toArray();
        $maxNum = 0;
        foreach ($existing as $pNo) {
            $num = (int) substr($pNo, strlen($prefix));
            if ($num > $maxNum) {
                $maxNum = $num;
            }
        }

        return $prefix . str_pad($maxNum + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Definisi 11 Tahapan Pipeline Operasional Genie (STG)
     */
    public const STAGES_PIPELINE = [
        0 => ['key' => 'doc', 'title' => 'Dokumen', 'role' => 'R-DOC', 'role_name' => 'Staf Document', 'action' => 'Verifikasi dokumen', 'sla_hours' => 4],
        1 => ['key' => 'hs', 'title' => 'HS & Lartas', 'role' => 'R-DOC', 'role_name' => 'Staf Document', 'action' => 'Setujui HS code', 'sla_hours' => 4],
        2 => ['key' => 'pib', 'title' => 'Dokumen pabean', 'role' => 'R-DOC-SPV', 'role_name' => 'Supervisor Document', 'action' => 'Approve & kirim ke CEISA', 'sla_hours' => 2],
        3 => ['key' => 'bill', 'title' => 'Billing pabean', 'role' => 'R-FIN', 'role_name' => 'Finance', 'action' => 'Bayar billing', 'sla_hours' => 4],
        4 => ['key' => 'do', 'title' => 'Pelayaran & DO', 'role' => 'R-DO', 'role_name' => 'Staf Delivery Order', 'action' => 'Terbitkan DO', 'sla_hours' => 8],
        5 => ['key' => 'arr', 'title' => 'Kedatangan & jalur', 'role' => 'R-TRF', 'role_name' => 'Staf Traffic', 'action' => 'Tetapkan jalur', 'sla_hours' => 2],
        6 => ['key' => 'hold', 'title' => 'Hold & notul', 'role' => 'R-TRF', 'role_name' => 'Staf Traffic', 'action' => 'Selesaikan hold', 'sla_hours' => 24],
        7 => ['key' => 'gate', 'title' => 'SPPB & gate out', 'role' => 'R-TRF', 'role_name' => 'Staf Traffic', 'action' => 'Gate out', 'sla_hours' => 4],
        8 => ['key' => 'send', 'title' => 'Pengiriman', 'role' => 'R-TRF', 'role_name' => 'Staf Traffic', 'action' => 'Berangkatkan truk', 'sla_hours' => 2],
        9 => ['key' => 'pod', 'title' => 'POD & closing', 'role' => 'R-TRF', 'role_name' => 'Staf Traffic', 'action' => 'Tutup shipment', 'sla_hours' => 24],
        10 => ['key' => 'inv', 'title' => 'Invoice klien', 'role' => 'R-FIN', 'role_name' => 'Finance', 'action' => 'Kirim invoice', 'sla_hours' => 24],
    ];

    /**
     * Hitung Indeks Tahap Pipeline Saat Ini (0 sampai 11)
     */
    public function getStageIndexAttribute(): int
    {
        if ($this->closed_at !== null || in_array($this->status_tahap, ['INVOICED_P19', 'COMPLETED'])) {
            return 11; // Selesai Penuh
        }

        return match ($this->status_tahap) {
            'DOC_COLLECTING', 'DRAFT', 'DOCUMENT_RECEIVED' => 0,
            'DOC_READY' => 1,
            'HS_DETECTED', 'DRAFT_REVIEW' => 2,
            'CEISA_SUBMITTED', 'BILLING_ISSUED' => 3,
            'BILLING_PAID', 'DO_PROCESSING' => 4,
            'DO_RELEASED', 'ARRIVED', 'STUFFING' => 5,
            'TRAFFIC_HOLD', 'INSPECTION_CUSTOMS' => 6,
            'TRAFFIC_SPPB', 'SPPB_RELEASED', 'NPE_ISSUED' => 7,
            'TRAFFIC_GATE_OUT', 'GATE_OUT', 'VESSEL_DEPARTED' => 8,
            'DELIVERY', 'DELIVERED' => 9,
            'DELIVERED_POD' => 10,
            default => 0,
        };
    }

    /**
     * Matriks Checklist 7 Dokumen (Gaya Sheet TRACKING DOK ATRANS)
     */
    public function getTrackingChecklistAttribute(): array
    {
        $docs = $this->relationLoaded('documents') ? $this->documents : $this->documents()->get();
        $isExport = $this->type === 'EXPORT';

        $slots = $isExport
            ? [
                'INVOICE' => 'INV',
                'PACKING_LIST' => 'PL',
                'BOOKING_RECEIPT' => 'BKG',
                'BL_AWB' => 'BL',
                'COO_FORME' => 'COO',
                'PERIJINAN_LARTAS' => 'LRT',
                'ETC' => 'ETC',
            ]
            : [
                'BL_AWB' => 'BL',
                'INVOICE' => 'INV',
                'PACKING_LIST' => 'PL',
                'COO_FORME' => 'COO',
                'SC_PO' => 'SC/PO',
                'INSURANCE' => 'INS',
                'ETC' => 'ETC',
            ];

        $result = [];
        foreach ($slots as $code => $short) {
            $doc = $docs->firstWhere('jenis_dokumen', $code);
            $done = $doc && in_array($doc->status, ['UPLOADED', 'VERIFIED'], true);
            $result[$code] = [
                'code' => $code,
                'label' => $short,
                'checked' => $done,
                'verified' => $doc && $doc->status === 'VERIFIED',
                'status' => $doc?->status ?? 'MISSING',
                'doc_id' => $doc?->id,
                'file_name' => $doc?->nama_file,
            ];
        }

        return $result;
    }

    /**
     * Ringkasan Respon CEISA (SPJM, SPPB/NPE, NPD, DNP, NHI) dari DB + API CEISA
     */
    public function getCustomsResponsesAttribute(): array
    {
        $ceisa = $this->ceisaDocument;
        $status = strtoupper((string) ($ceisa?->status ?? ''));
        $jalur = strtoupper((string) ($this->customs_channel ?? $ceisa?->jalur ?? ''));

        $spjm = (bool) $this->spjm_received
            || $jalur === 'MERAH'
            || str_contains($status, 'SPJM')
            || str_contains($status, 'PEMERIKSAAN');

        $sppb = (bool) $this->sppb_received
            || !empty($ceisa?->nomor_sppb_npe)
            || in_array($status, ['SPPB', 'NPE', 'GATE_OUT', 'SELESAI'], true);

        return [
            'SPJM' => $spjm,
            'SPPB' => $sppb,
            'NPD' => (bool) $this->npd_received || str_contains($status, 'NPD'),
            'DNP' => (bool) $this->dnp_received || str_contains($status, 'DNP'),
            'NHI' => (bool) $this->nhi_received || str_contains($status, 'NHI'),
        ];
    }

    /**
     * Cek Status Pos Spesifik (0 s.d. 10): 'done', 'cur', 'skip', 'waiting'
     */
    public function getStageStatus(int $index): string
    {
        $currentIdx = $this->stage_index;

        // Pos 6 (Hold & notul) dilewati otomatis saat Jalur Hijau
        if ($index === 6 && $this->is_hold_skipped) {
            return 'skip';
        }

        if ($currentIdx > $index) {
            return 'done';
        }

        if ($currentIdx === $index && $currentIdx < 11) {
            return 'cur';
        }

        return 'waiting';
    }

    /**
     * Status SLA Operasional: 'ok' (Aman), 'warn' (SLA 80%), 'late' (Terlewat)
     */
    public function getSlaStatusAttribute(): string
    {
        if ($this->stage_index >= 11) {
            return 'ok';
        }

        $stageMeta = self::STAGES_PIPELINE[$this->stage_index] ?? null;
        $targetHours = $stageMeta['sla_hours'] ?? 8;

        $elapsedHours = $this->updated_at ? now()->diffInHours($this->updated_at) : 0;

        if ($elapsedHours > $targetHours) {
            return 'late';
        }

        if ($elapsedHours >= ($targetHours * 0.8)) {
            return 'warn';
        }

        return 'ok';
    }

    public function getSlaLabelAttribute(): string
    {
        return match ($this->sla_status) {
            'late' => 'SLA Terlewat',
            'warn' => 'SLA 80%',
            default => 'SLA Aman',
        };
    }

    /**
     * Peta Relasi PIC per Pos/Tahap (eager-load friendly)
     */
    public function getStagePicsMapAttribute(): array
    {
        $map = [];
        $pics = $this->relationLoaded('pics')
            ? $this->pics
            : $this->pics()->with(['user.employee', 'backup.employee'])->get();

        foreach ($pics as $pic) {
            $map[$pic->process_code] = $pic;
        }

        return $map;
    }

    /**
     * Cek Apakah Tahap Hold Dilewati Otomatis (Smart Skip Jalur Hijau)
     */
    public function getIsHoldSkippedAttribute(): bool
    {
        return strtoupper($this->customs_channel ?? '') === 'HIJAU';
    }

    /**
     * Dapatkan Metadata Tahap Aktif
     */
    public function getCurrentStageMetaAttribute(): array
    {
        $idx = $this->stage_index;
        if ($idx >= 11) {
            return [
                'key' => 'done',
                'title' => 'Selesai & Lunas',
                'role' => 'R-FIN',
                'role_name' => 'Finance',
                'action' => 'Shipment Selesai',
                'sla_hours' => 0,
            ];
        }

        return self::STAGES_PIPELINE[$idx] ?? self::STAGES_PIPELINE[0];
    }

    /**
     * Cek apakah portal klien masih aktif berdasarkan timer
     */
    public function isPortalActive(): bool
    {
        return !empty($this->portal_token) && $this->portal_expires_at && $this->portal_expires_at->isFuture();
    }

    /**
     * Dapatkan status portal klien (ACTIVE, EXPIRED, NOT_CONFIGURED)
     */
    public function getPortalStatusAttribute(): string
    {
        if (empty($this->portal_token) || !$this->portal_expires_at) {
            return 'NOT_CONFIGURED';
        }

        return $this->portal_expires_at->isFuture() ? 'ACTIVE' : 'EXPIRED';
    }

    /**
     * Generate token portal unik baru dengan durasi timer tertentu
     */
    public function generatePortalToken(int $durationDays = 7): string
    {
        $token = 'TRK-' . strtoupper(\Illuminate\Support\Str::random(6)) . '-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(8));
        $this->update([
            'portal_token' => $token,
            'portal_expires_at' => now()->addDays($durationDays),
            'portal_duration_days' => $durationDays,
        ]);

        return $token;
    }

    /**
     * URL Portal Klien untuk Shipment ini
     */
    public function getPortalUrlAttribute(): ?string
    {
        if (empty($this->portal_token)) {
            return null;
        }

        return route('portal.track', $this->portal_token);
    }

    /**
     * Nomor Aju Kepabeanan CEISA 4.0 (26 digit) atau fallback project_no
     */
    public function getNomorAjuAttribute(): string
    {
        return $this->ceisaDocument?->nomor_aju ?? $this->project_no;
    }

    /**
     * Cek apakah alur shipment telah mencapai atau melewati tahap Billing Kepabeanan.
     * Setelah billing terbit, dokumen pabean terkunci dari perubahan atau rollback draft.
     */
    public function isBillingLocked(): bool
    {
        return in_array($this->status_tahap, [
            'BILLING_ISSUED', 'BILLING_PAID', 'DO_PROCESSING', 'DO_RELEASED',
            'ARRIVED', 'TRAFFIC_HOLD', 'SPPB_RELEASED', 'GATE_OUT', 'DELIVERY', 'DELIVERED_POD', 'COMPLETED'
        ]) 
        || !empty($this->billing_code) 
        || ($this->ceisaDocument && $this->ceisaDocument->isBillingLocked());
    }
}

