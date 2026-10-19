<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * F1: Klien, Shipment Lifecycle, PIC, Event, & Dokumen Checklist
     */
    public function up(): void
    {
        // 1. Client Contacts
        if (!Schema::hasTable('client_contacts')) {
            Schema::create('client_contacts', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('client_id')->constrained('clients')->onDelete('cascade');
                $table->string('name');
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->string('role_label')->nullable();
                $table->timestamps();
            });
        }

        // 2. Client Warehouses (Gudang Klien)
        if (!Schema::hasTable('client_warehouses')) {
            Schema::create('client_warehouses', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('client_id')->constrained('clients')->onDelete('cascade');
                $table->string('name');
                $table->text('address')->nullable();
                $table->string('city')->nullable();
                $table->timestamps();
            });
        }

        // 3. Documents (Manajemen Berkas Shipment & Versi Checklist)
        if (!Schema::hasTable('documents')) {
            Schema::create('documents', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('shipment_id')->constrained('shipments')->onDelete('cascade');
                $table->string('doc_type');
                $table->string('process_code')->nullable();
                $table->integer('version')->default(1);
                $table->string('file_key');
                $table->string('file_name')->nullable();
                $table->string('status')->default('UPLOADED');
                $table->text('reject_reason')->nullable();
                $table->string('uploaded_by')->nullable();
                $table->date('valid_to')->nullable();
                $table->timestamps();
            });
        }

        // 4. Client POA (Surat Kuasa Pabean Klien)
        if (!Schema::hasTable('client_poa')) {
            Schema::create('client_poa', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('client_id')->constrained('clients')->onDelete('cascade');
                $table->string('number');
                $table->date('valid_from')->nullable();
                $table->date('valid_to');
                $table->foreignUuid('file_id')->nullable()->constrained('documents')->onDelete('set null');
                $table->timestamps();
            });
        }

        // 5. Shipment PIC (Penugasan Tim Operasional per Shipment)
        if (!Schema::hasTable('shipment_pic')) {
            Schema::create('shipment_pic', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('shipment_id')->constrained('shipments')->onDelete('cascade');
                $table->string('process_code'); // P01, P02, dst
                $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignUuid('backup_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
            });
        }

        // 6. Shipment Events (Riwayat Jejak Audit Perubahan Status)
        if (!Schema::hasTable('shipment_events')) {
            Schema::create('shipment_events', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('shipment_id')->constrained('shipments')->onDelete('cascade');
                $table->string('process_code')->nullable();
                $table->string('from_status')->nullable();
                $table->string('to_status');
                $table->foreignUuid('actor_id')->nullable()->constrained('users')->onDelete('set null');
                $table->text('note')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        // 7. Shipment Required Documents (Syarat Berkas Mandatori per Shipment)
        if (!Schema::hasTable('shipment_required_documents')) {
            Schema::create('shipment_required_documents', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('shipment_id')->constrained('shipments')->onDelete('cascade');
                $table->string('doc_type');
                $table->string('status')->default('MISSING');
                $table->string('source')->nullable();
                $table->timestamps();
            });
        }

        // 8. Document Validations (Verifikasi & Diff Isi Dokumen AI vs Fisik)
        if (!Schema::hasTable('document_validations')) {
            Schema::create('document_validations', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('shipment_id')->constrained('shipments')->onDelete('cascade');
                $table->string('check_type');
                $table->json('diff')->nullable();
                $table->foreignUuid('noted_by_id')->nullable()->constrained('users')->onDelete('set null');
                $table->text('note')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_validations');
        Schema::dropIfExists('shipment_required_documents');
        Schema::dropIfExists('shipment_events');
        Schema::dropIfExists('shipment_pic');
        Schema::dropIfExists('client_poa');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('client_warehouses');
        Schema::dropIfExists('client_contacts');
    }
};
