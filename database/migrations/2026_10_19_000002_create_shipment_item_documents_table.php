<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel pelacakan dokumen pelengkap & perizinan lartas bea cukai per item barang kargo.
     */
    public function up(): void
    {
        if (!Schema::hasTable('shipment_item_documents')) {
            Schema::create('shipment_item_documents', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('shipment_id')->constrained('shipments')->onDelete('cascade');
                $table->foreignUuid('shipment_item_id')->constrained('shipment_items')->onDelete('cascade');
                $table->string('kode_dokumen', 10)->index(); // Kode Standar CEISA (860: PI, 861: LS, 810: SNI, 853: SDPPI, 880: V-Legal, dll)
                $table->string('nama_dokumen', 255);
                $table->string('instansi_penerbit', 255)->nullable();
                $table->string('nomor_dokumen', 100)->nullable();
                $table->date('tanggal_dokumen')->nullable();
                $table->string('file_name', 255)->nullable();
                $table->string('file_path', 255)->nullable();
                $table->enum('status', ['MISSING', 'UPLOADED', 'VERIFIED', 'REJECTED'])->default('MISSING')->index();
                $table->boolean('is_mandatory')->default(true);
                $table->text('catatan')->nullable();
                $table->foreignUuid('verified_by_user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->dateTime('verified_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['shipment_id', 'status']);
                $table->index(['shipment_item_id', 'kode_dokumen']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_item_documents');
    }
};
