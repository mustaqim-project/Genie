<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * F6: SLA Kebijakan, Timer Real-time, Notifikasi Pengguna, & Snapshot KPI Radar
     */
    public function up(): void
    {
        // 1. SLA Policies (Standar Batas Waktu Layanan per Klien & Tahap Operasional)
        if (!Schema::hasTable('sla_policies')) {
            Schema::create('sla_policies', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('process_code');
                $table->foreignUuid('client_id')->nullable()->constrained('clients')->onDelete('cascade');
                $table->string('doc_type')->nullable();
                $table->decimal('target_hours', 10, 4);
                $table->integer('warn_pct')->default(80);
                $table->timestamps();
            });
        }

        // 2. SLA Timers (Pencatatan Jam Mulai, Deadline, & Keterlambatan Real-time)
        if (!Schema::hasTable('sla_timers')) {
            Schema::create('sla_timers', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('shipment_id')->constrained('shipments')->onDelete('cascade');
                $table->string('process_code');
                $table->timestamp('started_at');
                $table->timestamp('due_at');
                $table->timestamp('completed_at')->nullable();
                $table->string('status'); // RUNNING, WARNING, BREACHED, COMPLETED
                $table->timestamps();
            });
        }

        // 3. Notifications (Pemberitahuan Sistem & Logistik ke User)
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
                $table->string('kind'); // DEMURRAGE, BILLING, SPPB, REJECT, SLA
                $table->string('channel')->default('IN_APP'); // IN_APP, EMAIL, WHATSAPP
                $table->foreignUuid('shipment_id')->nullable()->constrained('shipments')->onDelete('cascade');
                $table->string('title')->nullable();
                $table->text('body')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }

        // 4. Notification Preferences (Pengaturan Saluran & Frekuensi Notifikasi Akun)
        if (!Schema::hasTable('notification_prefs')) {
            Schema::create('notification_prefs', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
                $table->string('kind');
                $table->string('channel');
                $table->boolean('enabled')->default(true);
                $table->timestamps();
            });
        }

        // 5. KPI Snapshots (Snapshot Metrik Kinerja Eksekutif Berkala)
        if (!Schema::hasTable('kpi_snapshots')) {
            Schema::create('kpi_snapshots', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('scope'); // COMPANY, TEAM, CLIENT
                $table->uuid('subject_id')->nullable();
                $table->string('period'); // 2026-10, Q4-2026, 2026-W41
                $table->json('metrics');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_snapshots');
        Schema::dropIfExists('notification_prefs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('sla_timers');
        Schema::dropIfExists('sla_policies');
    }
};
