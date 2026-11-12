<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Sprint 2: F15 (Batch Import & Smart Extraction) + F16 (Historical Jump Stage)
     */
    public function up(): void
    {
        // ==========================================
        // F15: IMPORT MASSAL & EKSTRAKSI ASINKRON
        // ==========================================

        // 1. import_batches
        if (!Schema::hasTable('import_batches')) {
            Schema::create('import_batches', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('batch_no', 50)->unique();
                $table->foreignUuid('uploaded_by_id')->constrained('users')->onDelete('cascade');
                $table->foreignUuid('client_id')->nullable()->constrained('clients')->onDelete('set null');
                $table->string('status', 30)->default('UPLOADED'); // UPLOADED, PROCESSING, COMPLETED, PARTIALLY_FAILED, FAILED
                $table->unsignedInteger('total_files')->default(0);
                $table->unsignedInteger('processed_files')->default(0);
                $table->unsignedInteger('success_count')->default(0);
                $table->unsignedInteger('error_count')->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 2. import_files
        if (!Schema::hasTable('import_files')) {
            Schema::create('import_files', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('batch_id')->constrained('import_batches')->onDelete('cascade');
                $table->string('original_name');
                $table->string('file_path');
                $table->unsignedBigInteger('file_size_kb')->default(0);
                $table->string('file_hash', 64); // SHA-256
                $table->string('mime_type', 100)->nullable();
                $table->string('detected_type', 50)->nullable(); // INVOICE, PACKING_LIST, BL_AWB, PIB, PEB, DO, COO, dll
                $table->decimal('type_confidence', 5, 2)->default(0); // 0.00 s/d 100.00
                $table->unsignedInteger('page_count')->default(1);
                $table->string('status', 30)->default('UPLOADED'); // UPLOADED, CHECKED, CLASSIFIED, EXTRACTED, VALIDATED, NEEDS_REVIEW, READY, APPROVED, APPLIED, FAILED, REJECTED, DUPLICATE
                $table->text('error_message')->nullable();
                $table->timestamps();
            });
        }

        // 3. import_documents
        if (!Schema::hasTable('import_documents')) {
            Schema::create('import_documents', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('file_id')->constrained('import_files')->onDelete('cascade');
                $table->foreignUuid('batch_id')->constrained('import_batches')->onDelete('cascade');
                $table->foreignUuid('shipment_id')->nullable()->constrained('shipments')->onDelete('set null');
                $table->string('document_type', 50);
                $table->string('page_range', 50)->default('1');
                $table->string('language', 10)->default('id');
                $table->string('review_status', 30)->default('PENDING'); // PENDING, REVIEWED, APPROVED, REJECTED
                $table->foreignUuid('reviewed_by_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();
            });
        }

        // 4. import_fields
        if (!Schema::hasTable('import_fields')) {
            Schema::create('import_fields', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('document_id')->constrained('import_documents')->onDelete('cascade');
                $table->string('field_key', 100);
                $table->string('field_label', 150);
                $table->text('original_value')->nullable();
                $table->text('corrected_value')->nullable();
                $table->decimal('confidence_score', 5, 2)->default(0);
                $table->unsignedInteger('page_number')->default(1);
                $table->json('bounding_box')->nullable(); // {x, y, width, height}
                $table->string('status', 30)->default('DETECTED'); // DETECTED, EDITED, CONFIRMED, REJECTED
                $table->foreignUuid('edited_by_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('edited_at')->nullable();
                $table->timestamps();
            });
        }

        // 5. import_items
        if (!Schema::hasTable('import_items')) {
            Schema::create('import_items', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('document_id')->constrained('import_documents')->onDelete('cascade');
                $table->unsignedInteger('item_no')->default(1);
                $table->text('description');
                $table->string('hs_code', 20)->nullable();
                $table->decimal('quantity', 14, 4)->default(0);
                $table->string('unit', 20)->nullable();
                $table->decimal('unit_price', 18, 4)->default(0);
                $table->decimal('total_price', 18, 4)->default(0);
                $table->decimal('gross_weight', 14, 4)->default(0);
                $table->decimal('net_weight', 14, 4)->default(0);
                $table->string('country_of_origin', 10)->nullable();
                $table->decimal('confidence_score', 5, 2)->default(0);
                $table->timestamps();
            });
        }

        // 6. import_validations
        if (!Schema::hasTable('import_validations')) {
            Schema::create('import_validations', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('document_id')->constrained('import_documents')->onDelete('cascade');
                $table->string('rule_code', 20); // V01 s/d V10
                $table->string('severity', 20)->default('WARNING'); // BLOCKING, WARNING
                $table->text('message');
                $table->boolean('is_passed')->default(true);
                $table->timestamps();
            });
        }

        // 7. import_applications
        if (!Schema::hasTable('import_applications')) {
            Schema::create('import_applications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('batch_id')->constrained('import_batches')->onDelete('cascade');
                $table->foreignUuid('shipment_id')->constrained('shipments')->onDelete('cascade');
                $table->string('idempotency_key', 100)->unique();
                $table->json('applied_data_payload');
                $table->foreignUuid('applied_by_id')->constrained('users')->onDelete('cascade');
                $table->timestamp('can_rollback_until')->nullable();
                $table->boolean('is_rolled_back')->default(false);
                $table->timestamps();
            });
        }

        // ==========================================
        // F16: IMPOR DATA HISTORIS & LOMPAT TAHAP
        // ==========================================

        // 8. stage_prerequisites
        if (!Schema::hasTable('stage_prerequisites')) {
            Schema::create('stage_prerequisites', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('stage_code', 50)->unique();
                $table->string('stage_name', 100);
                $table->unsignedInteger('stage_number');
                $table->json('required_fields');
                $table->json('required_documents');
                $table->boolean('is_approval_required')->default(false);
                $table->string('approver_role', 50)->nullable();
                $table->timestamps();
            });
        }

        // 9. shipment_stage_entries
        if (!Schema::hasTable('shipment_stage_entries')) {
            Schema::create('shipment_stage_entries', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('shipment_id')->constrained('shipments')->onDelete('cascade');
                $table->unsignedInteger('stage_number');
                $table->string('stage_code', 50);
                $table->string('entry_mode', 20)->default('NATIVE'); // NATIVE, IMPORTED, SKIPPED
                $table->date('original_date')->nullable();
                $table->string('source_note')->nullable();
                $table->string('backfill_status', 30)->default('NOT_APPLICABLE'); // COMPLETE, PENDING, NOT_APPLICABLE
                $table->timestamps();
            });
        }

        // 10. project_jump_requests
        if (!Schema::hasTable('project_jump_requests')) {
            Schema::create('project_jump_requests', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('shipment_id')->constrained('shipments')->onDelete('cascade');
                $table->string('from_stage', 50);
                $table->string('to_stage', 50);
                $table->text('reason');
                $table->string('financial_treatment', 30)->default('OPENING_BALANCE'); // OPENING_BALANCE, HISTORICAL_POSTING
                $table->string('status', 30)->default('PENDING'); // PENDING, APPROVED, REJECTED
                $table->foreignUuid('requested_by_id')->constrained('users')->onDelete('cascade');
                $table->foreignUuid('approved_by_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('decided_at')->nullable();
                $table->timestamps();
            });
        }

        // 11. import_sources
        if (!Schema::hasTable('import_sources')) {
            Schema::create('import_sources', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('shipment_id')->constrained('shipments')->onDelete('cascade');
                $table->foreignUuid('import_file_id')->nullable()->constrained('import_files')->onDelete('set null');
                $table->foreignUuid('import_field_id')->nullable()->constrained('import_fields')->onDelete('set null');
                $table->string('target_model', 100);
                $table->string('target_field', 100);
                $table->timestamps();
            });
        }

        // 12. jump_batches & jump_batch_rows
        if (!Schema::hasTable('jump_batches')) {
            Schema::create('jump_batches', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('file_name');
                $table->foreignUuid('uploaded_by_id')->constrained('users')->onDelete('cascade');
                $table->string('status', 30)->default('PROCESSING'); // PROCESSING, COMPLETED, FAILED
                $table->unsignedInteger('total_rows')->default(0);
                $table->unsignedInteger('success_rows')->default(0);
                $table->unsignedInteger('failed_rows')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('jump_batch_rows')) {
            Schema::create('jump_batch_rows', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('batch_id')->constrained('jump_batches')->onDelete('cascade');
                $table->unsignedInteger('row_number');
                $table->string('bl_number', 100)->nullable();
                $table->string('target_stage', 50);
                $table->string('status', 30)->default('PENDING'); // SUCCESS, NEEDS_COMPLETION, PENDING_APPROVAL, REJECTED
                $table->text('error_message')->nullable();
                $table->foreignUuid('result_shipment_id')->nullable()->constrained('shipments')->onDelete('set null');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jump_batch_rows');
        Schema::dropIfExists('jump_batches');
        Schema::dropIfExists('import_sources');
        Schema::dropIfExists('project_jump_requests');
        Schema::dropIfExists('shipment_stage_entries');
        Schema::dropIfExists('stage_prerequisites');
        Schema::dropIfExists('import_applications');
        Schema::dropIfExists('import_validations');
        Schema::dropIfExists('import_items');
        Schema::dropIfExists('import_fields');
        Schema::dropIfExists('import_documents');
        Schema::dropIfExists('import_files');
        Schema::dropIfExists('import_batches');
    }
};
