<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * F2 & F3: Tim Document, Pos Tarif BTKI, Regulasi Lartas, & Deklarasi CEISA 4.0
     */
    public function up(): void
    {
        // 1. HS Codes (Master Pos Tarif BTKI & Bea Masuk)
        if (!Schema::hasTable('hs_codes')) {
            Schema::create('hs_codes', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('code')->unique();
                $table->text('description');
                $table->decimal('duty_bm', 10, 4)->nullable();
                $table->text('btki')->nullable();
                $table->date('valid_from')->nullable();
                $table->date('valid_to')->nullable();
                $table->timestamps();
            });
        }

        // 2. HS Regulations (Lartas, Dokumen Persyaratan, & Kondisi Pabean)
        if (!Schema::hasTable('hs_regulations')) {
            Schema::create('hs_regulations', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('hs_from')->nullable();
                $table->string('hs_to')->nullable();
                $table->foreignUuid('origin_country_id')->nullable()->constrained('countries')->onDelete('set null');
                $table->json('doc_condition')->nullable();
                $table->string('required_doc');
                $table->text('source_regulation')->nullable();
                $table->boolean('verified')->default(false);
                $table->date('valid_from')->nullable();
                $table->date('valid_to')->nullable();
                $table->timestamps();
            });
        }

        // 3. Client Product Master (Database Produk Klien Terdaftar & HS Code Valid)
        if (!Schema::hasTable('client_product_master')) {
            Schema::create('client_product_master', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('client_id')->constrained('clients')->onDelete('cascade');
                $table->string('product_name');
                $table->foreignUuid('hs_code_id')->constrained('hs_codes')->onDelete('cascade');
                $table->string('source')->nullable();
                $table->timestamps();
            });
        }

        // 4. Document Types (Template & Skema Dokumen CEISA/EDI)
        if (!Schema::hasTable('document_types')) {
            Schema::create('document_types', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('code')->unique();
                $table->string('name');
                $table->json('header_schema')->nullable();
                $table->json('item_schema')->nullable();
                $table->json('checklist')->nullable();
                $table->json('approval_flow')->nullable();
                $table->json('mapping_api')->nullable();
                $table->integer('version')->default(1);
                $table->date('valid_from')->nullable();
                $table->date('valid_to')->nullable();
                $table->timestamps();
            });
        }

        // 5. Shipment Declarations (Draft & Respon PIB BC 2.0 / PEB BC 3.0)
        if (!Schema::hasTable('shipment_declarations')) {
            Schema::create('shipment_declarations', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('shipment_id')->constrained('shipments')->onDelete('cascade');
                $table->foreignUuid('doc_type_id')->constrained('document_types')->onDelete('cascade');
                $table->integer('revision')->default(0);
                $table->string('status');
                $table->json('payload')->nullable();
                $table->string('aju_no')->nullable()->index();
                $table->string('nopen')->nullable();
                $table->string('channel')->nullable(); // HIJAU, KUNING, MERAH
                $table->timestamp('submitted_at')->nullable();
                $table->foreignUuid('approved_by_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
            });
        }

        // 6. Customs Billings (Kode Billing MPN G3 & Bukti Bayar Bea Pabean)
        if (!Schema::hasTable('customs_billings')) {
            Schema::create('customs_billings', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('shipment_id')->constrained('shipments')->onDelete('cascade');
                $table->string('kind'); // MPN_G3, PNBP, dsb
                $table->decimal('amount', 18, 2);
                $table->string('status'); // UNPAID, PAID, EXPIRED
                $table->timestamp('paid_at')->nullable();
                $table->foreignUuid('proof_id')->nullable()->constrained('documents')->onDelete('set null');
                $table->timestamps();
            });
        }

        // 7. Outbound Jobs (Job Antrean Sinkronisasi CEISA / Vendor)
        if (!Schema::hasTable('outbound_jobs')) {
            Schema::create('outbound_jobs', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('provider');
                $table->string('idempotency_key')->unique();
                $table->foreignUuid('shipment_id')->nullable()->constrained('shipments')->onDelete('cascade');
                $table->json('payload')->nullable();
                $table->string('status'); // PENDING, PROCESSING, COMPLETED, FAILED
                $table->integer('attempts')->default(0);
                $table->timestamp('next_retry_at')->nullable();
                $table->timestamps();
            });
        }

        // 8. API Sync Logs (Log Trafik Request & Response H2H)
        if (!Schema::hasTable('api_sync_logs')) {
            Schema::create('api_sync_logs', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('provider');
                $table->foreignUuid('shipment_id')->nullable()->constrained('shipments')->onDelete('cascade');
                $table->foreignUuid('job_id')->nullable()->constrained('outbound_jobs')->onDelete('set null');
                $table->json('request')->nullable();
                $table->json('response')->nullable();
                $table->integer('http_status')->nullable();
                $table->boolean('success')->default(false);
                $table->timestamp('created_at')->useCurrent();
            });
        }

        // 9. Item HS Suggestions (Prediksi Pos Tarif Otomatis AI)
        if (!Schema::hasTable('item_hs_suggestions')) {
            Schema::create('item_hs_suggestions', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('item_id')->constrained('shipment_items')->onDelete('cascade');
                $table->foreignUuid('hs_code_id')->constrained('hs_codes')->onDelete('cascade');
                $table->decimal('score', 10, 4)->nullable();
                $table->text('reason')->nullable();
                $table->integer('rank')->default(1);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_hs_suggestions');
        Schema::dropIfExists('api_sync_logs');
        Schema::dropIfExists('outbound_jobs');
        Schema::dropIfExists('customs_billings');
        Schema::dropIfExists('shipment_declarations');
        Schema::dropIfExists('document_types');
        Schema::dropIfExists('client_product_master');
        Schema::dropIfExists('hs_regulations');
        Schema::dropIfExists('hs_codes');
    }
};
