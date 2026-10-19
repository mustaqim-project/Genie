<?php

namespace App\Models;

use App\Traits\HasRolesAndPermissions;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRolesAndPermissions, HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'job_title',
        'department',
        'employee_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relasi ke profil kepegawaian (HRM Employee profile).
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id')
            ?? $this->hasOne(Employee::class, 'user_id');
    }

    /**
     * Penugasan PIC shipment forwarder staf ini.
     */
    public function assignedShipments()
    {
        return $this->hasMany(ShipmentPic::class, 'user_id');
    }

    /**
     * Penugasan backup PIC staf ini untuk shipment aktif.
     */
    public function backupShipments()
    {
        return $this->hasMany(ShipmentPic::class, 'backup_id');
    }

    /**
     * Pengajuan cuti yang disetujui/ditolak oleh user ini.
     */
    public function approvedLeaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'approver_id');
    }

    /**
     * Pengajuan lembur yang disetujui/ditolak oleh user ini.
     */
    public function approvedOvertimeRequests()
    {
        return $this->hasMany(OvertimeRequest::class, 'approver_id');
    }

    /**
     * Periode payroll yang disetujui oleh user ini.
     */
    public function approvedPayrollRuns()
    {
        return $this->hasMany(PayrollRun::class, 'approved_by_id');
    }
}
