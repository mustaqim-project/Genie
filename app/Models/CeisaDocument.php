<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CeisaDocument extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'nomor_aju',
        'jenis_dokumen',
        'nomor_daftar',
        'tanggal_daftar',
        'kode_kantor',
        'nama_kantor',
        'status',
        'status_description',
        'jalur',
        'kode_valuta',
        'ndbm_kurs',
        'total_nilai',
        'total_nilai_idr',
        'total_bruto',
        'total_netto',
        'total_kemasan',
        'total_kontainer',
        'total_barang',
        'nama_perusahaan_lokal',
        'npwp_perusahaan_lokal',
        'nama_mitra_luar_negeri',
        'negara_mitra_luar_negeri',
        'nomor_bc11',
        'tanggal_bc11',
        'pos_bc11',
        'subpos_bc11',
        'kode_tutup_pu',
        'kode_tps',
        'tanggal_tiba',
        'pic_pembuat',
        'telepon_pembuat',
        'lokasi_siap_periksa',
        'tanggal_periksa',
        'bank_kode',
        'bank_nama',
        'is_schema_valid',
        'schema_validation_message',
        'kode_billing',
        'total_billing',
        'tgl_jatuh_tempo_billing',
        'status_billing',
        'nomor_sppb_npe',
        'tanggal_sppb_npe',
        'raw_payload',
        'last_response',
    ];

    protected $casts = [
        'tanggal_daftar' => 'date',
        'tanggal_bc11' => 'date',
        'tanggal_tiba' => 'date',
        'tanggal_periksa' => 'date',
        'is_schema_valid' => 'boolean',
        'tgl_jatuh_tempo_billing' => 'datetime',
        'tanggal_sppb_npe' => 'datetime',
        'ndbm_kurs' => 'decimal:2',
        'total_nilai' => 'decimal:2',
        'total_nilai_idr' => 'decimal:2',
        'total_bruto' => 'decimal:4',
        'total_netto' => 'decimal:4',
        'raw_payload' => 'array',
        'last_response' => 'array',
    ];

    public function logs()
    {
        return $this->hasMany(CeisaApiLog::class, 'nomor_aju', 'nomor_aju')->orderByDesc('created_at');
    }

    public function shipment()
    {
        return $this->hasOne(Shipment::class, 'ceisa_document_id')->withoutGlobalScope('client_access');
    }

    public function ensureShipment(): Shipment
    {
        return app(\App\Services\Shipment\ShipmentWorkflowService::class)->ensureShipmentForCeisaDocument($this);
    }

    public function getPayloadAttribute(): array
    {
        return $this->raw_payload ?? [];
    }

    public function getJenisDokumenLabelAttribute(): string
    {
        return match ($this->jenis_dokumen) {
            '20' => 'BC 2.0 (Impor / PIB)',
            '30' => 'BC 3.0 (Ekspor / PEB)',
            default => 'BC ' . $this->jenis_dokumen,
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'DRAFT' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
            'SUBMITTED', 'TERKIRIM', 'PROSES' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
            'BILLING' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
            'JALUR_HIJAU', 'SPPB', 'NPE' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
            'JALUR_KUNING' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
            'JALUR_MERAH' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400',
            'REJECT' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
            default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        };
    }

    public function getStatusTerakhirAttribute(): ?string
    {
        return $this->status_description ?? $this->status;
    }

    /**
     * Dapatkan analisis dan status lartas produk pada dokumen ini.
     *
     * @return array<string, mixed>
     */
    public function getLartasInfoAttribute(): array
    {
        $barang = $this->raw_payload['barang'] ?? [];
        $totalBarang = count($barang);
        $lartasBarang = [];
        $nonLartasBarang = [];

        $hsService = app(\App\Services\Ceisa\CeisaHsCodeService::class);

        foreach ($barang as $b) {
            $pos = preg_replace('/[^0-9]/', '', (string)($b['posTarif'] ?? ($b['hs_code'] ?? ($b['kode_hs'] ?? ''))));
            $uraian = $b['uraian'] ?? ($b['uraian_barang'] ?? ($b['description'] ?? 'Barang'));

            $hasLartas = !empty($b['has_lartas']);
            $lartasDesc = $b['lartas'] ?? ($b['lartas_info'] ?? null);

            if (!$hasLartas && !empty($pos)) {
                $tariff = $hsService->getTariffAndLartas($pos);
                if (!empty($tariff['has_lartas'])) {
                    $hasLartas = true;
                    $lartasDesc = $tariff['lartas'] ?? 'Wajib Perizinan K/L';
                }
            }

            $itemData = [
                'pos' => $pos,
                'uraian' => $uraian,
                'has_lartas' => $hasLartas,
                'lartas' => $lartasDesc ?: 'Bebas Lartas',
            ];

            if ($hasLartas) {
                $lartasBarang[] = $itemData;
            } else {
                $nonLartasBarang[] = $itemData;
            }
        }

        return [
            'has_lartas' => count($lartasBarang) > 0,
            'total_products' => $totalBarang,
            'lartas_count' => count($lartasBarang),
            'non_lartas_count' => count($nonLartasBarang),
            'lartas_items' => $lartasBarang,
            'non_lartas_items' => $nonLartasBarang,
            'primary_lartas' => count($lartasBarang) > 0 ? ($lartasBarang[0]['lartas'] ?? 'Wajib Perizinan K/L') : null,
        ];
    }

    /**
     * Cek apakah dokumen telah mencapai tahap Billing atau sesudahnya (Terkunci & Tidak Dapat Diedit).
     * Dokumen yang telah terbit Billing MPN G3 atau SPPB/NPE terkunci secara fiskal & hukum di DJBC.
     */
    public function isBillingLocked(): bool
    {
        return in_array($this->status, ['BILLING', 'SPPB', 'NPE', 'JALUR_HIJAU', 'JALUR_KUNING', 'JALUR_MERAH'])
            || !empty($this->kode_billing)
            || !empty($this->nomor_sppb_npe)
            || ($this->shipment && (
                in_array($this->shipment->status_tahap, [
                    'BILLING_ISSUED', 'BILLING_PAID', 'DO_PROCESSING', 'DO_RELEASED',
                    'ARRIVED', 'TRAFFIC_HOLD', 'SPPB_RELEASED', 'GATE_OUT', 'DELIVERY', 'DELIVERED_POD', 'COMPLETED'
                ]) || !empty($this->shipment->billing_code)
            ));
    }

    /**
     * Cek apakah dokumen CEISA masih berstatus DRAFT/REJECT dan aman dihapus.
     */
    public function isDraftDeletable(): bool
    {
        if ($this->isBillingLocked()) {
            return false;
        }

        if (!empty($this->nomor_daftar) || !empty($this->nomor_sppb_npe)) {
            return false;
        }

        return in_array(strtoupper((string) $this->status), ['DRAFT', 'REJECT'], true);
    }
}

