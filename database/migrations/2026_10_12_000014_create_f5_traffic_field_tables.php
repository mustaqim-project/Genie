<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * F5: Traffic, Lapangan, Behandle, Vendor Truk, Dispatch, POD, & Klaim
     */
    public function up(): void
    {
        // 1. Shipment Holds (Penanganan Jalur Kuning, Merah, SPJM, & Pemeriksaan Fisik)
        if (!Schema::hasTable('shipment_holds')) {
            Schema::create('shipment_holds', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('shipment_id')->constrained('shipments')->onDelete('cascade');
                $table->foreignUuid('hold_type_id')->constrained('hold_types')->onDelete('cascade');
                $table->string('status'); // PENDING, RESOLVED, ESCALATED
                $table->timestamp('due_at')->nullable();
                $table->foreignUuid('pic_id')->nullable()->constrained('users')->onDelete('set null');
                $table->text('note')->nullable();
                $table->timestamps();
            });
        }

        // 2. Shipment Hold Steps (SOP Tahapan Penyelesaian Penahanan / Behandle)
        if (!Schema::hasTable('shipment_hold_steps')) {
            Schema::create('shipment_hold_steps', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('hold_id')->constrained('shipment_holds')->onDelete('cascade');
                $table->integer('seq')->default(1);
                $table->string('name');
                $table->string('status');
                $table->foreignUuid('pic_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('done_at')->nullable();
                $table->foreignUuid('evidence_id')->nullable()->constrained('documents')->onDelete('set null');
                $table->timestamps();
            });
        }

        // 3. Field Inspections (Laporan Lapangan Pemeriksaan Behandle & Petugas BC)
        if (!Schema::hasTable('field_inspections')) {
            Schema::create('field_inspections', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('shipment_id')->constrained('shipments')->onDelete('cascade');
                $table->foreignUuid('hold_id')->nullable()->constrained('shipment_holds')->onDelete('set null');
                $table->foreignUuid('inspector_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('result')->nullable(); // SESUAI, TIDAK_SESUAI, REKOMENDASI
                $table->text('notes')->nullable();
                $table->foreignUuid('report_id')->nullable()->constrained('documents')->onDelete('set null');
                $table->timestamps();
            });
        }

        // 4. Delivery Vendors (Ekspedisi Truk, Angkutan Pelabuhan & Trailler)
        if (!Schema::hasTable('delivery_vendors')) {
            Schema::create('delivery_vendors', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->string('phone')->nullable();
                $table->string('status')->default('ACTIVE');
                $table->decimal('score', 10, 4)->nullable();
                $table->timestamps();
            });
        }

        // 5. Vendor Rates (Tarif Standar Ekspedisi per Rute & Ukuran Kontainer)
        if (!Schema::hasTable('vendor_rates')) {
            Schema::create('vendor_rates', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('vendor_id')->constrained('delivery_vendors')->onDelete('cascade');
                $table->string('origin')->nullable();
                $table->string('destination')->nullable();
                $table->integer('container_size')->nullable();
                $table->decimal('rate', 18, 2);
                $table->date('valid_from')->nullable();
                $table->date('valid_to')->nullable();
                $table->timestamps();
            });
        }

        // 6. Vendor Quotes (Penawaran Ongkos Angkut Truk untuk Shipment Terkait)
        if (!Schema::hasTable('vendor_quotes')) {
            Schema::create('vendor_quotes', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('shipment_id')->constrained('shipments')->onDelete('cascade');
                $table->foreignUuid('vendor_id')->constrained('delivery_vendors')->onDelete('cascade');
                $table->decimal('amount', 18, 2);
                $table->boolean('selected')->default(false);
                $table->text('reason')->nullable();
                $table->foreignUuid('approved_by_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
            });
        }

        // 7. Deliveries (Surat Jalan Truk, Supir, Plat Nomor, & Gate Out Pelabuhan)
        if (!Schema::hasTable('deliveries')) {
            Schema::create('deliveries', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('shipment_id')->constrained('shipments')->onDelete('cascade');
                $table->foreignUuid('vendor_id')->nullable()->constrained('delivery_vendors')->onDelete('set null');
                $table->string('truck_plate')->nullable();
                $table->string('driver_name')->nullable();
                $table->foreignUuid('dispatch_note_id')->nullable()->constrained('documents')->onDelete('set null');
                $table->string('status'); // ASSIGNED, EN_ROUTE, DELIVERED, RETURNED
                $table->timestamp('eta')->nullable();
                $table->timestamps();
            });
        }

        // 8. Delivery Checkpoints (Pencatatan Titik Singgah GPS / Pos Pelabuhan)
        if (!Schema::hasTable('delivery_checkpoints')) {
            Schema::create('delivery_checkpoints', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('delivery_id')->constrained('deliveries')->onDelete('cascade');
                $table->string('location')->nullable();
                $table->decimal('lat', 10, 4)->nullable();
                $table->decimal('lng', 10, 4)->nullable();
                $table->text('note')->nullable();
                $table->string('source')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        // 9. POD Records (Proof of Delivery / Tanda Terima Basah di Gudang Klien)
        if (!Schema::hasTable('pod_records')) {
            Schema::create('pod_records', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('delivery_id')->constrained('deliveries')->onDelete('cascade');
                $table->string('receiver')->nullable();
                $table->timestamp('received_at')->nullable();
                $table->foreignUuid('proof_id')->nullable()->constrained('documents')->onDelete('set null');
                $table->foreignUuid('closed_confirmed_by_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
            });
        }

        // 10. Claims (Klaim Kerusakan Barang / Kontainer / Keterlambatan)
        if (!Schema::hasTable('claims')) {
            Schema::create('claims', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('delivery_id')->constrained('deliveries')->onDelete('cascade');
                $table->foreignUuid('vendor_id')->nullable()->constrained('delivery_vendors')->onDelete('set null');
                $table->string('kind');
                $table->decimal('amount', 18, 2)->nullable();
                $table->string('status');
                $table->text('note')->nullable();
                $table->timestamps();
            });
        }

        // 11. Container Returns (Pengembalian Kontainer Kosong ke Depo Pelayaran)
        if (!Schema::hasTable('container_returns')) {
            Schema::create('container_returns', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('container_id')->constrained('shipment_containers')->onDelete('cascade');
                $table->date('return_due')->nullable();
                $table->timestamp('returned_at')->nullable();
                $table->decimal('cost', 18, 2)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('container_returns');
        Schema::dropIfExists('claims');
        Schema::dropIfExists('pod_records');
        Schema::dropIfExists('delivery_checkpoints');
        Schema::dropIfExists('deliveries');
        Schema::dropIfExists('vendor_quotes');
        Schema::dropIfExists('vendor_rates');
        Schema::dropIfExists('delivery_vendors');
        Schema::dropIfExists('field_inspections');
        Schema::dropIfExists('shipment_hold_steps');
        Schema::dropIfExists('shipment_holds');
    }
};
