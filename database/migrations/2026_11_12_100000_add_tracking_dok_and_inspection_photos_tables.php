<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah ENUM ke VARCHAR pada MySQL agar mendukung penuh dokumen tambahan (SC_PO, ETC, BOOKING_RECEIPT, SPJM, NPD, DNP, dll)
        // dan tahapan Export / Inspection tambahan tanpa error strict enum.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE shipments MODIFY COLUMN status_tahap VARCHAR(50) NOT NULL DEFAULT 'DOC_COLLECTING'");
            DB::statement("ALTER TABLE shipment_documents MODIFY COLUMN jenis_dokumen VARCHAR(60) NOT NULL");
        } elseif (DB::getDriverName() === 'sqlite') {
            Schema::table('shipments', function (Blueprint $table) {
                $table->string('status_tahap', 50)->default('DOC_COLLECTING')->change();
            });
            Schema::table('shipment_documents', function (Blueprint $table) {
                $table->string('jenis_dokumen', 60)->change();
            });
        }

        // 1. Tambah kolom Tracking Dok & Operasional Impor/Ekspor pada tabel shipments
        Schema::table('shipments', function (Blueprint $table) {
            $addCols = [
                'doc_received_at' => fn () => $table->date('doc_received_at')->nullable()->after('eta_date'),
                'doc_completed_at' => fn () => $table->date('doc_completed_at')->nullable()->after('doc_received_at'),
                'etd_date' => fn () => $table->dateTime('etd_date')->nullable()->after('doc_completed_at'),
                'tps_location' => fn () => $table->string('tps_location', 100)->nullable()->after('pelabuhan_bongkar'),
                'delivery_date' => fn () => $table->date('delivery_date')->nullable()->after('pod_signed_at'),
                'trucking_vendor_name' => fn () => $table->string('trucking_vendor_name', 150)->nullable()->after('trucking_vendor_reason'),
                'last_work_status' => fn () => $table->string('last_work_status', 150)->nullable()->after('trucking_vendor_name'),
                'odoo_synced' => fn () => $table->boolean('odoo_synced')->default(false)->after('last_work_status'),
                'physical_doc_archived' => fn () => $table->boolean('physical_doc_archived')->default(false)->after('odoo_synced'),
                'spjm_received' => fn () => $table->boolean('spjm_received')->default(false)->after('physical_doc_archived'),
                'sppb_received' => fn () => $table->boolean('sppb_received')->default(false)->after('spjm_received'),
                'npd_received' => fn () => $table->boolean('npd_received')->default(false)->after('sppb_received'),
                'dnp_received' => fn () => $table->boolean('dnp_received')->default(false)->after('npd_received'),
                'nhi_received' => fn () => $table->boolean('nhi_received')->default(false)->after('dnp_received'),
                'spjm_ticket' => fn () => $table->string('spjm_ticket', 100)->nullable()->after('nhi_received'),
                'spjm_received_at' => fn () => $table->dateTime('spjm_received_at')->nullable()->after('spjm_ticket'),
                'inspection_scheduled_at' => fn () => $table->dateTime('inspection_scheduled_at')->nullable()->after('spjm_received_at'),
                'old_seal_number' => fn () => $table->string('old_seal_number', 60)->nullable()->after('inspection_scheduled_at'),
                'new_seal_number' => fn () => $table->string('new_seal_number', 60)->nullable()->after('old_seal_number'),
                'invoice_jasa_no' => fn () => $table->string('invoice_jasa_no', 80)->nullable()->after('new_seal_number'),
                'invoice_reimburse_no' => fn () => $table->string('invoice_reimburse_no', 80)->nullable()->after('invoice_jasa_no'),
                'commodity_summary' => fn () => $table->string('commodity_summary', 255)->nullable()->after('invoice_reimburse_no'),
            ];

            foreach ($addCols as $col => $callback) {
                if (!Schema::hasColumn('shipments', $col)) {
                    $callback();
                }
            }
        });

        // 2. Tabel Galeri Foto Pemeriksaan Bea Cukai (SPJM/Behandle/Segel Ulang) & Stuffing Ekspor
        if (!Schema::hasTable('inspection_photos')) {
            Schema::create('inspection_photos', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('shipment_id')->constrained('shipments')->onDelete('cascade');
                $table->string('flow_type', 30)->default('IMPORT_SPJM'); // IMPORT_SPJM, EXPORT_STUFFING, DELIVERY_POD
                $table->string('stage', 40); // BEFORE_UNLOAD, UNLOADING, GOODS_INSPECTION, RESEALING, INSPECTION_REPORT, EMPTY_CONTAINER, STUFFING_PROCESS, FULL_CONTAINER, BC_SEAL, OTHER
                $table->string('file_path');
                $table->string('file_name');
                $table->unsignedInteger('file_size_kb')->default(0);
                $table->string('mime_type', 80)->nullable();
                $table->string('seal_number', 60)->nullable();
                $table->text('caption')->nullable();
                $table->timestamp('taken_at')->nullable();
                $table->foreignUuid('uploaded_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
                $table->softDeletes();

                $table->index(['shipment_id', 'stage']);
            });
        }

        // 3. Tabel Biaya Operasional Shipment (DO, Extend DO I-III, Refund, BHDL, STG, TKC, LOLO, Trucking)
        if (!Schema::hasTable('shipment_operation_costs')) {
            Schema::create('shipment_operation_costs', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('shipment_id')->constrained('shipments')->onDelete('cascade');
                $table->string('cost_category', 40); // DO, EXTEND_DO_1, EXTEND_DO_2, EXTEND_DO_3, REFUND, BHDL, STG, TKC, LOLO, TRUCKING, OTHER
                $table->string('vendor_name', 150)->nullable();
                $table->string('invoice_number', 100)->nullable();
                $table->decimal('amount', 18, 2)->default(0);
                $table->string('status', 20)->default('PENDING'); // PENDING, PAID
                $table->date('cost_date')->nullable();
                $table->text('notes')->nullable();
                $table->foreignUuid('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();

                $table->index(['shipment_id', 'cost_category']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_operation_costs');
        Schema::dropIfExists('inspection_photos');
    }
};
