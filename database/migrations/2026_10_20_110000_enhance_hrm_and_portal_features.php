<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Full Adoption of COOCA Core HRM & ESS Portal Features into GENIE ERP
     */
    public function up(): void
    {
        // 1. Enhanced Columns for employees table
        if (Schema::hasTable('employees')) {
            Schema::table('employees', function (Blueprint $table) {
                if (!Schema::hasColumn('employees', 'bank_name')) {
                    $table->string('bank_name', 50)->nullable()->after('base_salary');
                }
                if (!Schema::hasColumn('employees', 'bank_account_number')) {
                    $table->string('bank_account_number', 50)->nullable()->after('bank_name');
                }
                if (!Schema::hasColumn('employees', 'bank_account_holder')) {
                    $table->string('bank_account_holder', 150)->nullable()->after('bank_account_number');
                }
                if (!Schema::hasColumn('employees', 'bpjs_tk_number')) {
                    $table->string('bpjs_tk_number', 50)->nullable()->after('bank_account_holder');
                }
                if (!Schema::hasColumn('employees', 'bpjs_kes_number')) {
                    $table->string('bpjs_kes_number', 50)->nullable()->after('bpjs_tk_number');
                }
                if (!Schema::hasColumn('employees', 'tax_ptkp_status')) {
                    $table->string('tax_ptkp_status', 20)->default('TK/0')->after('bpjs_kes_number');
                }
                if (!Schema::hasColumn('employees', 'face_data')) {
                    $table->longText('face_data')->nullable()->after('tax_ptkp_status');
                }
                if (!Schema::hasColumn('employees', 'photo_url')) {
                    $table->string('photo_url', 500)->nullable()->after('face_data');
                }
                if (!Schema::hasColumn('employees', 'phone')) {
                    $table->string('phone', 30)->nullable()->after('photo_url');
                }
                if (!Schema::hasColumn('employees', 'emergency_contact_name')) {
                    $table->string('emergency_contact_name', 150)->nullable()->after('phone');
                }
                if (!Schema::hasColumn('employees', 'emergency_contact_phone')) {
                    $table->string('emergency_contact_phone', 30)->nullable()->after('emergency_contact_name');
                }
            });
        }

        // 2. Enhanced Columns for attendance_logs table
        if (Schema::hasTable('attendance_logs')) {
            Schema::table('attendance_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('attendance_logs', 'photo_url')) {
                    $table->longText('photo_url')->nullable()->after('valid');
                }
                if (!Schema::hasColumn('attendance_logs', 'notes')) {
                    $table->string('notes', 500)->nullable()->after('photo_url');
                }
                if (!Schema::hasColumn('attendance_logs', 'is_geofenced')) {
                    $table->boolean('is_geofenced')->default(true)->after('notes');
                }
                if (!Schema::hasColumn('attendance_logs', 'distance_m')) {
                    $table->integer('distance_m')->nullable()->after('is_geofenced');
                }
                if (!Schema::hasColumn('attendance_logs', 'device_info')) {
                    $table->string('device_info', 255)->nullable()->after('distance_m');
                }
            });
        }

        // 3. Enhanced Columns for shifts table
        if (Schema::hasTable('shifts')) {
            Schema::table('shifts', function (Blueprint $table) {
                if (!Schema::hasColumn('shifts', 'grace_period_minutes')) {
                    $table->integer('grace_period_minutes')->default(15)->after('end_time');
                }
                if (!Schema::hasColumn('shifts', 'is_night_shift')) {
                    $table->boolean('is_night_shift')->default(false)->after('grace_period_minutes');
                }
            });
        }

        // 4. Enhanced Columns for work_locations table
        if (Schema::hasTable('work_locations')) {
            Schema::table('work_locations', function (Blueprint $table) {
                if (!Schema::hasColumn('work_locations', 'address')) {
                    $table->text('address')->nullable()->after('radius_m');
                }
                if (!Schema::hasColumn('work_locations', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('address');
                }
            });
        }

        // 5. Create attendance_corrections table (Koreksi Presensi Karyawan)
        if (!Schema::hasTable('attendance_corrections')) {
            Schema::create('attendance_corrections', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('employee_id')->constrained('employees')->onDelete('cascade');
                $table->foreignUuid('attendance_id')->nullable()->constrained('attendance_logs')->onDelete('set null');
                $table->date('correction_date');
                $table->time('proposed_clock_in')->nullable();
                $table->time('proposed_clock_out')->nullable();
                $table->text('reason');
                $table->string('status')->default('PENDING'); // PENDING, APPROVED, REJECTED
                $table->foreignUuid('approver_id')->nullable()->constrained('users')->onDelete('set null');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 6. Create employee_schedules table (Penugasan Shift Kerja Karyawan)
        if (!Schema::hasTable('employee_schedules')) {
            Schema::create('employee_schedules', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('employee_id')->constrained('employees')->onDelete('cascade');
                $table->foreignUuid('shift_id')->constrained('shifts')->onDelete('cascade');
                $table->date('work_date');
                $table->string('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_schedules');
        Schema::dropIfExists('attendance_corrections');

        if (Schema::hasTable('work_locations')) {
            Schema::table('work_locations', function (Blueprint $table) {
                $table->dropColumn(['address', 'is_active']);
            });
        }

        if (Schema::hasTable('shifts')) {
            Schema::table('shifts', function (Blueprint $table) {
                $table->dropColumn(['grace_period_minutes', 'is_night_shift']);
            });
        }

        if (Schema::hasTable('attendance_logs')) {
            Schema::table('attendance_logs', function (Blueprint $table) {
                $table->dropColumn(['photo_url', 'notes', 'is_geofenced', 'distance_m', 'device_info']);
            });
        }

        if (Schema::hasTable('employees')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn([
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
                ]);
            });
        }
    }
};
