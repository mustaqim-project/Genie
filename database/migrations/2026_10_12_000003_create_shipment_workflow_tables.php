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
        // 1. Master Clients
        Schema::create('clients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 30)->unique()->index();
            $table->string('name');
            $table->string('npwp', 25)->nullable()->index();
            $table->string('nib', 25)->nullable()->index();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('pic_name')->nullable();
            $table->text('address')->nullable();
            $table->date('surat_kuasa_expired_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Master Shipping Line Vendors (Maersk, ONE, Evergreen, Cosco, Samudera dll)
        Schema::create('shipping_vendors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 30)->unique()->index();
            $table->string('name');
            $table->string('pic_name')->nullable();
            $table->string('pic_email')->nullable();
            $table->string('pic_phone')->nullable();
            $table->string('portal_url')->nullable();
            $table->text('address')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_no')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Shipments (Projects)
        Schema::create('shipments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('project_no', 40)->unique()->index(); // SHP-YYYYMM-####
            $table->foreignUuid('client_id')->constrained('clients')->onDelete('cascade');
            $table->enum('jenis_layanan', ['IMPORT_BC20', 'EKSPORT_BC30'])->default('IMPORT_BC20');
            $table->string('nomor_bl_awb', 50)->index();
            $table->date('tgl_bl_awb')->nullable();
            
            // Shipping & Carrier Details
            $table->foreignUuid('shipping_vendor_id')->nullable()->constrained('shipping_vendors')->onDelete('set null');
            $table->string('nama_kapal')->nullable();
            $table->string('voyage_no', 30)->nullable();
            $table->string('pelabuhan_muat', 50)->nullable();
            $table->string('pelabuhan_bongkar', 50)->default('IDTPP (Tanjung Priok)');
            $table->dateTime('eta_date')->nullable(); // Estimated Time of Arrival
            $table->dateTime('actual_arrival_date')->nullable();
            
            // Container & Volume Details
            $table->integer('total_kontainer')->default(1);
            $table->string('nomor_kontainer', 100)->nullable();
            $table->integer('total_kemasan')->default(100);
            $table->string('jenis_kemasan', 50)->default('PKG (Packages)');
            $table->decimal('total_bruto', 14, 2)->default(0);
            $table->decimal('total_netto', 14, 2)->default(0);
            $table->string('kode_valuta', 10)->default('USD');
            $table->decimal('total_nilai_valuta', 16, 2)->default(0);
            $table->decimal('ndbm_kurs', 12, 2)->default(16200);
            $table->decimal('total_nilai_idr', 18, 2)->default(0);

            // Free Time & DO Tracking
            $table->integer('free_time_days')->default(7);
            $table->dateTime('free_time_expired_at')->nullable();
            $table->dateTime('do_expired_at')->nullable();
            $table->string('nomor_do', 50)->nullable();
            $table->enum('status_do', [
                'PENDING_REQUEST',
                'EMAIL_SENT',
                'INVOICE_RECEIVED',
                'PAYMENT_SUBMITTED',
                'DO_RELEASED',
                'EXPIRED'
            ])->default('PENDING_REQUEST');
            $table->dateTime('do_email_sent_at')->nullable();
            $table->string('do_email_recipient')->nullable();

            // CEISA Pabean Link
            $table->foreignUuid('ceisa_document_id')->nullable()->constrained('ceisa_documents')->onDelete('set null');
            
            // State Machine Tahapan Lifecycle (P01 - P19)
            $table->enum('status_tahap', [
                'DOC_COLLECTING',      // P01: Pengumpulan Dokumen Client
                'DOC_READY',           // P02: Dokumen Lengkap & Terverifikasi
                'HS_DETECTED',         // P03: HS Code Terdeteksi dari Packing List
                'DRAFT_REVIEW',        // P04: Draft BC 2.0 / BC 3.0 Siap Review SPV
                'CEISA_SUBMITTED',     // P05: Dokumen Terkirim ke Gateway DJBC
                'BILLING_ISSUED',      // P06: Kode Billing MPN G3 Terbit (Tim Dokumen Selesai)
                'DO_PROCESSING',       // P07: Tim DO Kirim Email Permintaan Tagihan
                'DO_RELEASED',         // P08: DO Pelayaran Berhasil Ditebus
                'TRAFFIC_HOLD',        // P09: Menunggu Penjaluran / Behandle TPS
                'TRAFFIC_SPPB',        // P10: SPPB / NPE Terbit Resmi
                'TRAFFIC_GATE_OUT',    // P11: Kontainer Keluar TPS Pelabuhan
                'DELIVERED_POD',       // P12: Barang Tiba di Gudang Klien (POD Uploaded)
                'INVOICED_P19',        // P13: Invoice Penagihan Klien Terbit
                'COMPLETED'            // P14: Selesai & Lunas
            ])->default('DOC_COLLECTING');

            $table->text('catatan')->nullable();
            $table->foreignUuid('created_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. Shipment Documents Checklist
        Schema::create('shipment_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('shipment_id')->constrained('shipments')->onDelete('cascade');
            $table->enum('jenis_dokumen', [
                'INVOICE',
                'PACKING_LIST',
                'BL_AWB',
                'COO_FORME',
                'INSURANCE',
                'PERIJINAN_LARTAS',
                'DO_ORIGINAL',
                'BUKTI_BAYAR_BILLING',
                'POD_SURAT_JALAN'
            ]);
            $table->string('nama_file')->nullable();
            $table->string('file_path')->nullable();
            $table->integer('file_size_kb')->nullable();
            $table->enum('status', ['MISSING', 'UPLOADED', 'VERIFIED', 'REJECTED'])->default('MISSING');
            $table->foreignUuid('verified_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('verified_at')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 5. Shipment Items (Hasil Ekstraksi Packing List & Deteksi HS Code)
        Schema::create('shipment_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('shipment_id')->constrained('shipments')->onDelete('cascade');
            $table->integer('nomor_urut')->default(1);
            $table->text('uraian_barang');
            $table->string('kode_hs', 20)->nullable()->index();
            $table->decimal('hs_confidence_score', 5, 2)->default(0.95);
            $table->string('hs_legal_basis')->nullable(); // Dasar hukum BTKI 2022
            $table->string('negara_asal', 10)->default('CN');
            $table->integer('jumlah_kemasan')->default(1);
            $table->string('jenis_kemasan', 20)->default('PKG');
            $table->decimal('berat_netto', 12, 2)->default(0);
            $table->decimal('berat_bruto', 12, 2)->default(0);
            $table->decimal('nilai_fob', 14, 2)->default(0);
            $table->decimal('tarif_bm_persen', 5, 2)->default(5.0);
            $table->decimal('tarif_ppn_persen', 5, 2)->default(11.0);
            $table->decimal('tarif_pph_persen', 5, 2)->default(2.5);
            $table->timestamps();
            $table->softDeletes();
        });

        // 6. Quotations (Surat Penawaran Harga Forwarder & PPJK)
        Schema::create('quotations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('quotation_no', 40)->unique()->index(); // QUO-YYYYMM-####
            $table->foreignUuid('client_id')->constrained('clients')->onDelete('cascade');
            $table->date('tanggal');
            $table->date('valid_until');
            $table->string('pelabuhan_asal', 50);
            $table->string('pelabuhan_tujuan', 50);
            $table->string('term_of_shipment', 30)->default('CY / CY');
            $table->json('rincian_biaya'); // Freight, THC, Ocean Doc, PPJK Fee, Trucking
            $table->decimal('subtotal_idr', 16, 2)->default(0);
            $table->decimal('ppn_idr', 16, 2)->default(0);
            $table->decimal('total_idr', 16, 2)->default(0);
            $table->enum('status', ['DRAFT', 'SENT', 'ACCEPTED', 'REJECTED'])->default('DRAFT');
            $table->text('syarat_ketentuan')->nullable();
            $table->foreignUuid('created_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });

        // 7. Client Invoices (Penagihan Klien P19)
        Schema::create('client_invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('invoice_no', 40)->unique()->index(); // INV-YYYYMM-####
            $table->foreignUuid('shipment_id')->nullable()->constrained('shipments')->onDelete('set null');
            $table->foreignUuid('client_id')->constrained('clients')->onDelete('cascade');
            $table->date('tanggal');
            $table->date('jatuh_tempo');
            $table->decimal('talangan_pabean_idr', 16, 2)->default(0); // BM, PPN, PPh Impor
            $table->decimal('biaya_pelayaran_idr', 16, 2)->default(0); // THC, DO Fee, Cleaning
            $table->decimal('jasa_ppjk_idr', 16, 2)->default(0);        // Jasa Pengurusan
            $table->decimal('biaya_trucking_idr', 16, 2)->default(0);   // Pengiriman ke gudang
            $table->decimal('subtotal_idr', 16, 2)->default(0);
            $table->decimal('ppn_jasa_idr', 16, 2)->default(0);         // 11% dari Jasa PPJK
            $table->decimal('total_tagihan_idr', 16, 2)->default(0);
            $table->enum('status', ['UNPAID', 'PARTIALLY_PAID', 'PAID', 'OVERDUE'])->default('UNPAID');
            $table->dateTime('paid_at')->nullable();
            $table->text('catatan_pembayaran')->nullable();
            $table->foreignUuid('created_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_invoices');
        Schema::dropIfExists('quotations');
        Schema::dropIfExists('shipment_items');
        Schema::dropIfExists('shipment_documents');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('shipping_vendors');
        Schema::dropIfExists('clients');
    }
};
