<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fase 3: Kolom CEISA 4.0 Open API v2 (BC 1.1, TPS, Kesiapan Barang, Validasi Skema)
     * dan Tabel Referensi Kurs Pajak (NDPBM / KMK).
     */
    public function up(): void
    {
        // 1. Tambah Kolom Pabean v2 ke Tabel ceisa_documents
        Schema::table('ceisa_documents', function (Blueprint $table) {
            // Data Manifes BC 1.1 & Pelabuhan/TPS
            $table->string('nomor_bc11', 30)->nullable()->after('negara_mitra_luar_negeri');
            $table->date('tanggal_bc11')->nullable()->after('nomor_bc11');
            $table->string('pos_bc11', 10)->nullable()->after('tanggal_bc11');
            $table->string('subpos_bc11', 10)->nullable()->after('pos_bc11');
            $table->string('kode_tutup_pu', 10)->default('11')->after('subpos_bc11');
            $table->string('kode_tps', 20)->nullable()->after('kode_tutup_pu');
            $table->date('tanggal_tiba')->nullable()->after('kode_tps');

            // Data Kesiapan Barang & Bank Devisa (BC 3.0 PEB)
            $table->string('pic_pembuat')->nullable()->after('tanggal_tiba');
            $table->string('telepon_pembuat', 30)->nullable()->after('pic_pembuat');
            $table->string('lokasi_siap_periksa')->nullable()->after('telepon_pembuat');
            $table->date('tanggal_periksa')->nullable()->after('lokasi_siap_periksa');
            $table->string('bank_kode', 20)->nullable()->after('tanggal_periksa');
            $table->string('bank_nama', 100)->nullable()->after('bank_kode');

            // Status Validasi Skema Pabean (Pre-Flight Check /document/check)
            $table->boolean('is_schema_valid')->default(false)->after('status_description');
            $table->text('schema_validation_message')->nullable()->after('is_schema_valid');
        });

        // 2. Tabel Kurs Pajak / NDPBM Kepabeanan
        if (!Schema::hasTable('ceisa_exchange_rates')) {
            Schema::create('ceisa_exchange_rates', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('kode_valuta', 10)->index();
                $table->decimal('nilai_kurs', 15, 4);
                $table->date('tanggal_mulai')->index();
                $table->date('tanggal_selesai')->index();
                $table->string('nomor_kmk', 100)->nullable();
                $table->string('sumber', 50)->default('CEISA_OPENAPI');
                $table->timestamps();
                $table->softDeletes();

                $table->index(['kode_valuta', 'tanggal_mulai', 'tanggal_selesai']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ceisa_exchange_rates');

        Schema::table('ceisa_documents', function (Blueprint $table) {
            $table->dropColumn([
                'nomor_bc11',
                'tanggal_bc11',
                'pos_bc11',
                'subpos_bc11',
                'kode_tutup_pu',
                'kode_tps',
                'tanggal_tiba',
                'pic_pembuat',
                'telepon_pembuat',
                'lokasi_siap_periksa',
                'tanggal_periksa',
                'bank_kode',
                'bank_nama',
                'is_schema_valid',
                'schema_validation_message',
            ]);
        });
    }
};
