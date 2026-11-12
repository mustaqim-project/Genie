<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Sprint 1: Versioning Dokumen, Periode Akuntansi, & Parameter Pajak TER PMK 168
     */
    public function up(): void
    {
        // 1. Versioning pada Shipment Documents
        if (Schema::hasTable('shipment_documents')) {
            Schema::table('shipment_documents', function (Blueprint $table) {
                if (!Schema::hasColumn('shipment_documents', 'version')) {
                    $table->unsignedInteger('version')->default(1)->after('status');
                }
                if (!Schema::hasColumn('shipment_documents', 'is_current')) {
                    $table->boolean('is_current')->default(true)->after('version');
                }
            });
        }

        // 2. Periode Akuntansi (Accounting Periods)
        if (!Schema::hasTable('accounting_periods')) {
            Schema::create('accounting_periods', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name', 50); // e.g. "Oktober 2026", "November 2026"
                $table->date('start_date');
                $table->date('end_date');
                $table->boolean('is_closed')->default(false);
                $table->timestamp('closed_at')->nullable();
                $table->foreignUuid('closed_by_id')->nullable()->constrained('users')->onDelete('set null');
                $table->text('closing_notes')->nullable();
                $table->timestamps();
            });
        }

        // 3. Parameter Tarif Pajak & BPJS (Payroll Parameters)
        if (!Schema::hasTable('payroll_tax_parameters')) {
            Schema::create('payroll_tax_parameters', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('code', 50)->unique();
                $table->string('category', 50); // TER_A, TER_B, TER_C, BPJS_KES, BPJS_TK_JP, BPJS_TK_JHT, BPJS_TK_JKK_JKM
                $table->string('description')->nullable();
                $table->json('rules_payload');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_tax_parameters');
        Schema::dropIfExists('accounting_periods');

        if (Schema::hasTable('shipment_documents')) {
            Schema::table('shipment_documents', function (Blueprint $table) {
                if (Schema::hasColumn('shipment_documents', 'is_current')) {
                    $table->dropColumn('is_current');
                }
                if (Schema::hasColumn('shipment_documents', 'version')) {
                    $table->dropColumn('version');
                }
            });
        }
    }
};
