<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * F12 & F13: AI Agent, MCP Server, Antrean Aksi Human-in-the-Loop, & Job Impor Data
     */
    public function up(): void
    {
        // 1. MCP Credentials (Token Akses Tool Antigravity / Claude / Cursor ke Database)
        if (!Schema::hasTable('mcp_credentials')) {
            Schema::create('mcp_credentials', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('user_id')->nullable()->constrained('users')->onDelete('cascade');
                $table->json('scope_roles');
                $table->string('token_hash')->unique();
                $table->timestamp('revoked_at')->nullable();
                $table->timestamps();
            });
        }

        // 2. MCP Call Logs (Audit Forensik Pemanggilan Tool Eksekusi AI)
        if (!Schema::hasTable('mcp_call_logs')) {
            Schema::create('mcp_call_logs', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('credential_id')->nullable()->constrained('mcp_credentials')->onDelete('set null');
                $table->string('tool');
                $table->json('args')->nullable();
                $table->string('result_status')->nullable(); // SUCCESS, ERROR
                $table->string('provider')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        // 3. AI Action Queue (Antrean Proposal Rekomendasi AI Menunggu Persetujuan Operator)
        if (!Schema::hasTable('ai_action_queue')) {
            Schema::create('ai_action_queue', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('kind'); // CLASSIFY_HS, SEND_DO_EMAIL, EXTRACT_INVOICE
                $table->json('proposal');
                $table->foreignUuid('shipment_id')->nullable()->constrained('shipments')->onDelete('cascade');
                $table->string('status')->default('PROPOSED'); // PROPOSED, APPROVED, REJECTED, EXECUTED
                $table->foreignUuid('decided_by_id')->nullable()->constrained('users')->onDelete('set null');
                $table->text('reason')->nullable();
                $table->timestamp('executed_at')->nullable();
                $table->timestamps();
            });
        }

        // 4. AI Reply Rules (Instruksi & Koridor AI: Do & Don't Saat Membalas Klien)
        if (!Schema::hasTable('ai_reply_rules')) {
            Schema::create('ai_reply_rules', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('topic');
                $table->text('do_rules')->nullable();
                $table->text('dont_rules')->nullable();
                $table->boolean('escalate')->default(false);
                $table->timestamps();
            });
        }

        // 5. AI Daily Reports (Laporan Otomatis Sintesis Harian AI: KPI & Isu Pabean)
        if (!Schema::hasTable('ai_daily_reports')) {
            Schema::create('ai_daily_reports', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->date('report_date')->unique();
                $table->json('content');
                $table->timestamps();
            });
        }

        // 6. Import Jobs (Job Antrean Impor Massal Excel Pos Tarif / Klien / Kontainer)
        if (!Schema::hasTable('import_jobs')) {
            Schema::create('import_jobs', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('kind'); // HS_CODE_BTKI, CLIENT_LIST, TARIFF_MASTER
                $table->foreignUuid('file_id')->nullable()->constrained('documents')->onDelete('set null');
                $table->string('status')->default('PENDING'); // PENDING, PROCESSING, COMPLETED, FAILED
                $table->integer('total_rows')->default(0);
                $table->integer('ok_rows')->default(0);
                $table->integer('error_rows')->default(0);
                $table->foreignUuid('created_by_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
            });
        }

        // 7. Import Errors (Rincian Baris Gagal Impor Excel & Pesan Validasi)
        if (!Schema::hasTable('import_errors')) {
            Schema::create('import_errors', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('job_id')->constrained('import_jobs')->onDelete('cascade');
                $table->integer('row_no')->nullable();
                $table->text('message')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_errors');
        Schema::dropIfExists('import_jobs');
        Schema::dropIfExists('ai_daily_reports');
        Schema::dropIfExists('ai_reply_rules');
        Schema::dropIfExists('ai_action_queue');
        Schema::dropIfExists('mcp_call_logs');
        Schema::dropIfExists('mcp_credentials');
    }
};
