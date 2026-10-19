<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ceisa_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nomor_aju', 26)->unique()->index();
            $table->string('jenis_dokumen', 10)->default('20')->index(); // 20: BC 2.0 (PIB), 30: BC 3.0 (PEB)
            $table->string('nomor_daftar')->nullable();
            $table->date('tanggal_daftar')->nullable();
            $table->string('kode_kantor', 10)->default('040300');
            $table->string('nama_kantor')->nullable();
            $table->string('status', 30)->default('DRAFT')->index(); // DRAFT, SUBMITTED, REJECT, PROSES, BILLING, JALUR_HIJAU, JALUR_KUNING, JALUR_MERAH, SPPB, NPE
            $table->text('status_description')->nullable();
            $table->string('jalur', 20)->nullable(); // HIJAU, KUNING, MERAH, MITA, AEO
            
            // Finansial & Valuasi Pabean
            $table->string('kode_valuta', 10)->default('USD');
            $table->decimal('ndbm_kurs', 15, 2)->default(15850.00);
            $table->decimal('total_nilai', 18, 2)->default(0.00); // CIF (BC 2.0) / FOB (BC 3.0)
            $table->decimal('total_nilai_idr', 18, 2)->default(0.00);
            $table->decimal('total_bruto', 12, 4)->default(0.00);
            $table->decimal('total_netto', 12, 4)->default(0.00);
            $table->integer('total_kemasan')->default(0);
            $table->integer('total_kontainer')->default(0);
            $table->integer('total_barang')->default(0);

            // Entitas Utama
            $table->string('nama_perusahaan_lokal')->nullable();
            $table->string('npwp_perusahaan_lokal', 30)->nullable();
            $table->string('nama_mitra_luar_negeri')->nullable();
            $table->string('negara_mitra_luar_negeri', 10)->nullable();

            // Pungutan & Billing MPN G3
            $table->string('kode_billing', 20)->nullable()->index();
            $table->decimal('total_billing', 18, 2)->nullable();
            $table->dateTime('tgl_jatuh_tempo_billing')->nullable();
            $table->string('status_billing', 20)->default('UNPAID'); // UNPAID, PAID, EXPIRED

            // Respon Persetujuan Akhir
            $table->string('nomor_sppb_npe')->nullable();
            $table->dateTime('tanggal_sppb_npe')->nullable();

            // Raw Data Audit & Re-submission
            $table->longText('raw_payload')->nullable();
            $table->longText('last_response')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('ceisa_api_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nomor_aju', 30)->nullable()->index();
            $table->string('endpoint');
            $table->string('method', 10);
            $table->integer('status_code');
            $table->text('request_header')->nullable();
            $table->longText('request_body')->nullable();
            $table->longText('response_body')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->boolean('is_success')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ceisa_api_logs');
        Schema::dropIfExists('ceisa_documents');
    }
};
