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
        // 1. Tabel Deposit Klien (MoM Poin 10)
        Schema::create('client_deposits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('deposit_no')->unique(); // Misal: DEP-202611-0001
            $table->foreignUuid('client_id')->constrained('clients')->cascadeOnDelete();
            $table->date('tanggal');
            $table->decimal('nominal', 16, 2);
            $table->decimal('saldo_sisa', 16, 2);
            $table->string('metode_pembayaran', 50)->default('BANK_TRANSFER'); // BANK_TRANSFER, CASH, CHEQUE, GIRO
            $table->string('bank_name', 100)->nullable();
            $table->string('rekening_tujuan', 100)->nullable();
            $table->string('nomor_referensi_bank', 100)->nullable();
            $table->string('bukti_bayar_path')->nullable();
            $table->string('status', 30)->default('CONFIRMED'); // PENDING, CONFIRMED, REFUNDED, DEPLETED
            $table->text('catatan')->nullable();
            $table->foreignUuid('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Tabel Penggunaan / Pemotongan Saldo Deposit Klien (MoM Poin 10)
        Schema::create('client_deposit_usages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('client_deposit_id')->constrained('client_deposits')->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignUuid('client_invoice_id')->nullable()->constrained('client_invoices')->nullOnDelete();
            $table->foreignUuid('shipment_id')->nullable()->constrained('shipments')->nullOnDelete();
            $table->date('tanggal');
            $table->decimal('nominal_terpakai', 16, 2);
            $table->decimal('saldo_sebelumnya', 16, 2);
            $table->decimal('saldo_setelahnya', 16, 2);
            $table->string('keterangan'); // Potong Invoice INV-..., Talangan DO, dsb
            $table->foreignUuid('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 3. Tabel Kamus HS Code per Klien (MoM Poin 12)
        Schema::create('client_hs_codes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignUuid('hs_code_id')->constrained('hs_codes')->cascadeOnDelete();
            $table->string('uraian_barang_client'); // Nama dagang atau istilah barang spesifik klien
            $table->text('spesifikasi_teknis')->nullable(); // Komposisi, merek, tipe teknis
            $table->string('negara_asal_default', 10)->nullable(); // Misal: CN, US, JP
            $table->decimal('tarif_bm_persen', 5, 2)->nullable();
            $table->decimal('tarif_ppn_persen', 5, 2)->nullable();
            $table->decimal('tarif_pph_persen', 5, 2)->nullable();
            $table->text('catatan_lartas')->nullable(); // Ijin Lartas Kemenperin/Kemendag terkait
            $table->foreignUuid('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['client_id', 'hs_code_id', 'uraian_barang_client'], 'uq_client_hs_barang');
        });

        // 4. Tambah kolom pendukung di client_invoices (MoM Poin 8 & 10)
        Schema::table('client_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('client_invoices', 'quotation_id')) {
                $table->foreignUuid('quotation_id')->nullable()->after('shipment_id')->constrained('quotations')->nullOnDelete();
            }
            if (!Schema::hasColumn('client_invoices', 'deposit_deducted_idr')) {
                $table->decimal('deposit_deducted_idr', 16, 2)->default(0)->after('total_tagihan_idr');
            }
            if (!Schema::hasColumn('client_invoices', 'sisa_tagihan_idr')) {
                $table->decimal('sisa_tagihan_idr', 16, 2)->default(0)->after('deposit_deducted_idr');
            }
        });

        // 5. Tambah kolom template surat jalan di shipments (MoM Poin 4)
        Schema::table('shipments', function (Blueprint $table) {
            if (!Schema::hasColumn('shipments', 'surat_jalan_template')) {
                $table->string('surat_jalan_template', 30)->default('TEMPLATE_1_FCL')->after('trucking_vendor_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            if (Schema::hasColumn('shipments', 'surat_jalan_template')) {
                $table->dropColumn('surat_jalan_template');
            }
        });

        Schema::table('client_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('client_invoices', 'quotation_id')) {
                $table->dropConstrainedForeignId('quotation_id');
            }
            if (Schema::hasColumn('client_invoices', 'deposit_deducted_idr')) {
                $table->dropColumn('deposit_deducted_idr');
            }
            if (Schema::hasColumn('client_invoices', 'sisa_tagihan_idr')) {
                $table->dropColumn('sisa_tagihan_idr');
            }
        });

        Schema::dropIfExists('client_hs_codes');
        Schema::dropIfExists('client_deposit_usages');
        Schema::dropIfExists('client_deposits');
    }
};
