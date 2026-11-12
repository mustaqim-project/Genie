<?php

namespace App\Models;

use App\Traits\Auditable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountingPeriod extends Model
{
    use HasFactory, HasUuids, Auditable;

    protected $table = 'accounting_periods';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_closed',
        'closed_at',
        'closed_by_id',
        'closing_notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_closed' => 'boolean',
        'closed_at' => 'datetime',
    ];

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by_id');
    }

    /**
     * Memeriksa apakah tanggal transaksi berada di periode akuntansi yang telah dikunci/ditutup
     */
    public static function isDateLocked(string|Carbon $date): bool
    {
        $d = Carbon::parse($date)->toDateString();

        return self::where('is_closed', true)
            ->where('start_date', '<=', $d)
            ->where('end_date', '>=', $d)
            ->exists();
    }
}
