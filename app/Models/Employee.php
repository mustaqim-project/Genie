<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'employees';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'nik',
        'user_id',
        'full_name',
        'position',
        'team',
        'start_date',
        'status',
        'base_salary',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'bpjs_tk_number',
        'bpjs_kes_number',
        'tax_ptkp_status',
        'face_data',
        'photo_url',
        'phone',
        'emergency_contact_name',
        'emergency_contact_phone',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'base_salary' => 'decimal:2',
        ];
    }

    /**
     * Relationship to linked login User account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Employment contracts (PKWT, PKWTT, Internship).
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(EmploymentContract::class, 'employee_id');
    }

    /**
     * Latest employment contract.
     */
    public function latestContract(): HasOne
    {
        return $this->hasOne(EmploymentContract::class, 'employee_id')->latestOfMany('start_date');
    }

    /**
     * Stored compliance & credential documents (KTP, NPWP, PPJK Certificate, BPJS).
     */
    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class, 'employee_id');
    }

    /**
     * UU Perlindungan Data Pribadi (UU PDP) Biometric consent record.
     */
    public function biometricConsent(): HasOne
    {
        return $this->hasOne(BiometricConsent::class, 'employee_id')->latestOfMany('consent_at');
    }

    /**
     * Clock in/out logs with GPS and Face recognition.
     */
    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class, 'employee_id');
    }

    /**
     * Cuti & izin permohonan.
     */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'employee_id');
    }

    /**
     * Kuota hak cuti tahunan per tahun kalender.
     */
    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class, 'employee_id');
    }

    /**
     * Pengajuan lembur lapangan & operasional dokumen.
     */
    public function overtimeRequests(): HasMany
    {
        return $this->hasMany(OvertimeRequest::class, 'employee_id');
    }

    /**
     * Kasbon karyawan.
     */
    public function cashAdvances(): HasMany
    {
        return $this->hasMany(CashAdvance::class, 'employee_id');
    }

    /**
     * Slip gaji karyawan.
     */
    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class, 'employee_id');
    }

    /**
     * History penyerahan inventaris aset (Laptop, HT TPS, ID Card Pelabuhan).
     */
    public function assetAssignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class, 'employee_id');
    }

    /**
     * Aset yang saat ini sedang aktif dipinjamkan.
     */
    public function activeAssetAssignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class, 'employee_id')->whereNull('returned_at');
    }

    /**
     * Relasi many-to-many ke inventaris aset.
     */
    public function assignedAssets(): BelongsToMany
    {
        return $this->belongsToMany(Asset::class, 'asset_assignments', 'employee_id', 'asset_id')
            ->withPivot(['id', 'assigned_at', 'returned_at', 'handover_proof_id'])
            ->withTimestamps();
    }

    // ==========================================
    // Query Scopes
    // ==========================================

    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }

    public function scopeByTeam($query, string $team)
    {
        return $query->where('team', $team);
    }

    // ==========================================
    // Accessors & Helper Methods
    // ==========================================

    public function getFormattedSalaryAttribute(): string
    {
        return 'Rp ' . number_format((float) ($this->base_salary ?? 0), 0, ',', '.');
    }

    public function getInitialsAttribute(): string
    {
        $words = explode(' ', trim($this->full_name));
        $initials = '';
        foreach (array_slice($words, 0, 2) as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
        return $initials ?: 'EM';
    }

    /**
     * Cek apakah persetujuan biometrik wajah masih sah sesuai UU PDP.
     */
    public function hasValidBiometricConsent(): bool
    {
        $consent = $this->biometricConsent;
        return $consent ? $consent->isValid() : false;
    }

    public function corrections(): HasMany
    {
        return $this->hasMany(AttendanceCorrection::class, 'employee_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(EmployeeSchedule::class, 'employee_id');
    }

    public function todayAttendance(): HasOne
    {
        return $this->hasOne(AttendanceLog::class, 'employee_id')
            ->whereDate('at', now()->toDateString())
            ->where('kind', 'CLOCK_IN')
            ->latest('at');
    }

    public function todayAttendanceOut(): HasOne
    {
        return $this->hasOne(AttendanceLog::class, 'employee_id')
            ->whereDate('at', now()->toDateString())
            ->where('kind', 'CLOCK_OUT')
            ->latest('at');
    }

    public function hasFaceRegistered(): bool
    {
        return !empty($this->face_data) || !empty($this->photo_url);
    }
}
