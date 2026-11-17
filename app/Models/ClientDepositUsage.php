<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientDepositUsage extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'client_deposit_usages';

    protected $fillable = [
        'client_deposit_id',
        'client_id',
        'client_invoice_id',
        'shipment_id',
        'tanggal',
        'nominal_terpakai',
        'saldo_sebelumnya',
        'saldo_setelahnya',
        'keterangan',
        'created_by_user_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal_terpakai' => 'decimal:2',
        'saldo_sebelumnya' => 'decimal:2',
        'saldo_setelahnya' => 'decimal:2',
    ];

    public function deposit(): BelongsTo
    {
        return $this->belongsTo(ClientDeposit::class, 'client_deposit_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(ClientInvoice::class, 'client_invoice_id');
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
