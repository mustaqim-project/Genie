<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentPic extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'shipment_pic';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'shipment_id',
        'process_code',
        'user_id',
        'backup_id',
    ];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class, 'shipment_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function backup(): BelongsTo
    {
        return $this->belongsTo(User::class, 'backup_id');
    }

    /**
     * Otomatis alihkan tugas PIC operasional shipment ke staf pengganti (backup PIC)
     * saat PIC utama cuti/izin operasional disetujui.
     */
    public function reassignToBackup(): bool
    {
        if (!$this->backup_id) {
            return false;
        }

        $oldUserId = $this->user_id;
        $this->user_id = $this->backup_id;
        // Simpan mantan PIC sebagai backup sementara saat staf pengganti bertugas
        $this->backup_id = $oldUserId;

        return $this->save();
    }
}
