<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * F8: Human Resource Management (HRM), Kepegawaian, Absensi GPS Face, Cuti, & Payroll
     */
    public function up(): void
    {
        // 1. Employees (Data Pokok Pegawai & Forwarder Staff)
        if (!Schema::hasTable('employees')) {
            Schema::create('employees', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('nik')->unique();
                $table->string('full_name');
                $table->string('position')->nullable();
                $table->string('team')->nullable(); // DOCUMENT, DO, TRAFFIC, FINANCE, IT
                $table->date('start_date')->nullable();
                $table->string('status')->default('ACTIVE');
                $table->decimal('base_salary', 18, 2)->nullable();
                $table->timestamps();
            });
        }

        // 2. Employment Contracts (Kontrak Kerja PKWT / PKWTT)
        if (!Schema::hasTable('employment_contracts')) {
            Schema::create('employment_contracts', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('employee_id')->constrained('employees')->onDelete('cascade');
                $table->string('kind'); // PKWT, PKWTT, INTERNSHIP
                $table->string('number')->nullable();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->timestamps();
            });
        }

        // 3. Employee Documents (KTP, NPWP, BPJS, Sertifikat PPJK)
        if (!Schema::hasTable('employee_documents')) {
            Schema::create('employee_documents', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('employee_id')->constrained('employees')->onDelete('cascade');
                $table->string('doc_type');
                $table->foreignUuid('file_id')->nullable()->constrained('documents')->onDelete('set null');
                $table->timestamps();
            });
        }

        // 4. Assets (Aset Inventaris Perusahaan: Laptop, HT, ID Card Pelabuhan)
        if (!Schema::hasTable('assets')) {
            Schema::create('assets', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('code')->unique();
                $table->string('name');
                $table->string('status')->default('AVAILABLE');
                $table->decimal('purchase_value', 18, 2)->nullable();
                $table->timestamps();
            });
        }

        // 5. Asset Assignments (Serah Terima Peminjaman Aset Pegawai)
        if (!Schema::hasTable('asset_assignments')) {
            Schema::create('asset_assignments', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('asset_id')->constrained('assets')->onDelete('cascade');
                $table->foreignUuid('employee_id')->constrained('employees')->onDelete('cascade');
                $table->date('assigned_at')->nullable();
                $table->date('returned_at')->nullable();
                $table->foreignUuid('handover_proof_id')->nullable()->constrained('documents')->onDelete('set null');
                $table->timestamps();
            });
        }

        // 6. Work Locations (Titik Geofencing Kantor & Pelabuhan TPS)
        if (!Schema::hasTable('work_locations')) {
            Schema::create('work_locations', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->decimal('lat', 10, 4)->nullable();
                $table->decimal('lng', 10, 4)->nullable();
                $table->integer('radius_m')->default(100);
                $table->date('valid_from')->nullable();
                $table->date('valid_to')->nullable();
                $table->timestamps();
            });
        }

        // 7. Shifts (Jadwal Kerja Operasional Kantor & Lapangan)
        if (!Schema::hasTable('shifts')) {
            Schema::create('shifts', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->string('start_time')->nullable();
                $table->string('end_time')->nullable();
                $table->timestamps();
            });
        }

        // 8. Biometric Consents (Persetujuan Rekam Wajah UU Perlindungan Data Pribadi)
        if (!Schema::hasTable('biometric_consents')) {
            Schema::create('biometric_consents', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('employee_id')->constrained('employees')->onDelete('cascade');
                $table->timestamp('consent_at');
                $table->timestamp('revoked_at')->nullable();
                $table->date('retention_until')->nullable();
                $table->timestamps();
            });
        }

        // 9. Attendance Logs (Presensi Masuk/Pulang: Face Match & Geotagging GPS)
        if (!Schema::hasTable('attendance_logs')) {
            Schema::create('attendance_logs', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('employee_id')->constrained('employees')->onDelete('cascade');
                $table->string('kind'); // CLOCK_IN, CLOCK_OUT
                $table->timestamp('at');
                $table->decimal('lat', 10, 4)->nullable();
                $table->decimal('lng', 10, 4)->nullable();
                $table->foreignUuid('location_id')->nullable()->constrained('work_locations')->onDelete('set null');
                $table->decimal('face_match', 10, 4)->nullable();
                $table->boolean('valid')->default(true);
                $table->timestamps();
            });
        }

        // 10. Leave Requests (Permohonan Izin / Cuti Karyawan)
        if (!Schema::hasTable('leave_requests')) {
            Schema::create('leave_requests', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('employee_id')->constrained('employees')->onDelete('cascade');
                $table->string('kind'); // TAHUNAN, SAKIT, MELAHIRKAN
                $table->date('from_date');
                $table->date('to_date');
                $table->string('status'); // PENDING, APPROVED, REJECTED
                $table->foreignUuid('approver_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
            });
        }

        // 11. Leave Balances (Sisa Kuota Hak Cuti Tahunan)
        if (!Schema::hasTable('leave_balances')) {
            Schema::create('leave_balances', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('employee_id')->constrained('employees')->onDelete('cascade');
                $table->integer('year');
                $table->string('kind');
                $table->decimal('balance', 10, 4)->default(12.0);
                $table->timestamps();
            });
        }

        // 12. Overtime Requests (Lembur Operasional Lapangan & Input Dokumen)
        if (!Schema::hasTable('overtime_requests')) {
            Schema::create('overtime_requests', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('employee_id')->constrained('employees')->onDelete('cascade');
                $table->date('work_date');
                $table->decimal('hours', 10, 4);
                $table->string('status'); // PENDING, APPROVED, REJECTED
                $table->foreignUuid('approver_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
            });
        }

        // 13. Cash Advances (Kasbon Pegawai & Cicilan Payroll)
        if (!Schema::hasTable('cash_advances')) {
            Schema::create('cash_advances', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('employee_id')->constrained('employees')->onDelete('cascade');
                $table->decimal('amount', 18, 2);
                $table->integer('installments')->default(1);
                $table->decimal('balance', 18, 2);
                $table->string('status'); // ACTIVE, CLEARED
                $table->timestamps();
            });
        }

        // 14. Payroll Runs (Periode Penggajian Bulanan Karyawan)
        if (!Schema::hasTable('payroll_runs')) {
            Schema::create('payroll_runs', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('period')->unique(); // 2026-10
                $table->string('status'); // DRAFT, APPROVED, DISBURSED
                $table->foreignUuid('approved_by_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
            });
        }

        // 15. Payslips (Slip Gaji per Karyawan)
        if (!Schema::hasTable('payslips')) {
            Schema::create('payslips', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('run_id')->constrained('payroll_runs')->onDelete('cascade');
                $table->foreignUuid('employee_id')->constrained('employees')->onDelete('cascade');
                $table->decimal('gross', 18, 2);
                $table->decimal('net', 18, 2);
                $table->decimal('deductions', 18, 2);
                $table->timestamps();
            });
        }

        // 16. Payslip Lines (Komponen Gaji: Pokok, Uang Makan, Lembur, Potongan BPJS/Kasbon)
        if (!Schema::hasTable('payslip_lines')) {
            Schema::create('payslip_lines', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('payslip_id')->constrained('payslips')->onDelete('cascade');
                $table->string('label');
                $table->string('kind'); // ALLOWANCE, DEDUCTION
                $table->decimal('amount', 18, 2);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payslip_lines');
        Schema::dropIfExists('payslips');
        Schema::dropIfExists('payroll_runs');
        Schema::dropIfExists('cash_advances');
        Schema::dropIfExists('overtime_requests');
        Schema::dropIfExists('leave_balances');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('attendance_logs');
        Schema::dropIfExists('biometric_consents');
        Schema::dropIfExists('shifts');
        Schema::dropIfExists('work_locations');
        Schema::dropIfExists('asset_assignments');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('employee_documents');
        Schema::dropIfExists('employment_contracts');
        Schema::dropIfExists('employees');
    }
};
