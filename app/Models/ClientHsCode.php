<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientHsCode extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'client_hs_codes';

    protected $fillable = [
        'client_id',
        'hs_code_id',
        'uraian_barang_client',
        'spesifikasi_teknis',
        'negara_asal_default',
        'tarif_bm_persen',
        'tarif_ppn_persen',
        'tarif_pph_persen',
        'catatan_lartas',
        'created_by_user_id',
    ];

    protected $casts = [
        'tarif_bm_persen' => 'decimal:2',
        'tarif_ppn_persen' => 'decimal:2',
        'tarif_pph_persen' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function hsCode(): BelongsTo
    {
        return $this->belongsTo(HsCode::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
