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
        // 1. Tambah Kolom Pabean v2 ke Tabel ceisa_documents secara idempoten
        Schema::table('ceisa_documents', function (Blueprint $table) {
            // Data Manifes BC 1.1 & Pelabuhan/TPS
            if (!Schema::hasColumn('ceisa_documents', 'nomor_bc11')) {
                $table->string('nomor_bc11', 30)->nullable()->after('negara_mitra_luar_negeri');
            }
            if (!Schema::hasColumn('ceisa_documents', 'tanggal_bc11')) {
                $table->date('tanggal_bc11')->nullable()->after('nomor_bc11');
            }
            if (!Schema::hasColumn('ceisa_documents', 'pos_bc11')) {
                $table->string('pos_bc11', 10)->nullable()->after('tanggal_bc11');
            }
            if (!Schema::hasColumn('ceisa_documents', 'subpos_bc11')) {
                $table->string('subpos_bc11', 10)->nullable()->after('pos_bc11');
            }
            if (!Schema::hasColumn('ceisa_documents', 'kode_tutup_pu')) {
                $table->string('kode_tutup_pu', 10)->default('11')->after('subpos_bc11');
            }
            if (!Schema::hasColumn('ceisa_documents', 'kode_tps')) {
                $table->string('kode_tps', 20)->nullable()->after('kode_tutup_pu');
            }
            if (!Schema::hasColumn('ceisa_documents', 'tanggal_tiba')) {
                $table->date('tanggal_tiba')->nullable()->after('kode_tps');
            }

            // Data Kesiapan Barang & Bank Devisa (BC 3.0 PEB)
            if (!Schema::hasColumn('ceisa_documents', 'pic_pembuat')) {
                $table->string('pic_pembuat')->nullable()->after('tanggal_tiba');
            }
            if (!Schema::hasColumn('ceisa_documents', 'telepon_pembuat')) {
                $table->string('telepon_pembuat', 30)->nullable()->after('pic_pembuat');
            }
            if (!Schema::hasColumn('ceisa_documents', 'lokasi_siap_periksa')) {
                $table->string('lokasi_siap_periksa')->nullable()->after('telepon_pembuat');
            }
            if (!Schema::hasColumn('ceisa_documents', 'tanggal_periksa')) {
                $table->date('tanggal_periksa')->nullable()->after('lokasi_siap_periksa');
            }
            if (!Schema::hasColumn('ceisa_documents', 'bank_kode')) {
                $table->string('bank_kode', 20)->nullable()->after('tanggal_periksa');
            }
            if (!Schema::hasColumn('ceisa_documents', 'bank_nama')) {
                $table->string('bank_nama', 100)->nullable()->after('bank_kode');
            }

            // Status Validasi Skema Pabean (Pre-Flight Check /document/check)
            if (!Schema::hasColumn('ceisa_documents', 'is_schema_valid')) {
                $table->boolean('is_schema_valid')->default(false)->after('status_description');
            }
            if (!Schema::hasColumn('ceisa_documents', 'schema_validation_message')) {
                $table->text('schema_validation_message')->nullable()->after('is_schema_valid');
            }
        });

        // 2. Tabel Kurs Pajak / NDPBM Kepabeanan
        if (Schema::hasTable('ceisa_exchange_rates')) {
            Schema::dropIfExists('ceisa_exchange_rates');
        }

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

            // Explicit index name <= 64 chars to prevent MySQL 1059 Identifier name is too long error
            $table->index(['kode_valuta', 'tanggal_mulai', 'tanggal_selesai'], 'ceisa_rates_valuta_dates_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ceisa_exchange_rates');

        Schema::table('ceisa_documents', function (Blueprint $table) {
            $columns = [
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
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('ceisa_documents', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
