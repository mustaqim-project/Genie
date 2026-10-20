<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'approvals';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'object_type',
        'object_id',
        'step',
        'approver_role',
        'status', // PENDING, APPROVED, REJECTED
        'decided_by_id',
        'decided_at',
        'reason',
    ];

    protected $casts = [
        'step' => 'integer',
        'decided_at' => 'datetime',
    ];

    public function decidedBy()
    {
        return $this->belongsTo(User::class, 'decided_by_id');
    }
}
