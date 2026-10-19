<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * F7: Keuangan, Bagan Akun COA, Invoice P19, Hutang Vendor, Kas Bank & Jurnal
     */
    public function up(): void
    {
        // 1. Chart of Accounts (Bagan Akun Standar / Buku Besar)
        if (!Schema::hasTable('chart_of_accounts')) {
            Schema::create('chart_of_accounts', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('code')->unique();
                $table->string('name');
                $table->string('type'); // ASSET, LIABILITY, EQUITY, REVENUE, EXPENSE
                $table->foreignUuid('parent_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');
                $table->timestamps();
            });
        }

        // 2. Cost Items (Komponen Biaya Operasional per Shipment: Jasa, Talangan, THC)
        if (!Schema::hasTable('cost_items')) {
            Schema::create('cost_items', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('shipment_id')->constrained('shipments')->onDelete('cascade');
                $table->string('category'); // REIMBURSABLE_CUSTOMS, FREIGHT, HANDLING, TRUCKING
                $table->foreignUuid('account_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');
                $table->string('source_type')->nullable();
                $table->uuid('source_id')->nullable();
                $table->decimal('amount', 18, 2);
                $table->timestamps();
            });
        }

        // 3. Client Invoice Lines (Rincian Item Tagihan Invoice Klien P19)
        if (!Schema::hasTable('client_invoice_lines')) {
            Schema::create('client_invoice_lines', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('invoice_id')->constrained('client_invoices')->onDelete('cascade');
                $table->text('description');
                $table->decimal('amount', 18, 2);
                $table->foreignUuid('cost_item_id')->nullable()->constrained('cost_items')->onDelete('set null');
                $table->timestamps();
            });
        }

        // 4. Vendor Bills (Tagihan Pelayaran, Depo Kontainer, TPS & Ekspedisi Truk)
        if (!Schema::hasTable('vendor_bills')) {
            Schema::create('vendor_bills', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('vendor_type'); // SHIPPING_LINE, TRUCKING, TPS, CUSTOMS
                $table->foreignUuid('shipment_id')->nullable()->constrained('shipments')->onDelete('cascade');
                $table->string('ref_no')->nullable();
                $table->decimal('amount', 18, 2);
                $table->date('due_date')->nullable();
                $table->string('status'); // PENDING, APPROVED, PAID
                $table->foreignUuid('approved_by_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
            });
        }

        // 5. Cash Bank Accounts (Daftar Rekening Kas & Bank Perusahaan)
        if (!Schema::hasTable('cash_bank_accounts')) {
            Schema::create('cash_bank_accounts', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->string('bank')->nullable();
                $table->string('account_no')->nullable();
                $table->foreignUuid('currency_id')->nullable()->constrained('currencies')->onDelete('set null');
                $table->timestamps();
            });
        }

        // 6. Vendor Payments (Pencairan Pembayaran Hutang Tagihan Vendor & Bukti Transfer)
        if (!Schema::hasTable('vendor_payments')) {
            Schema::create('vendor_payments', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('bill_id')->constrained('vendor_bills')->onDelete('cascade');
                $table->foreignUuid('account_id')->constrained('cash_bank_accounts')->onDelete('cascade');
                $table->decimal('amount', 18, 2);
                $table->date('paid_at')->nullable();
                $table->foreignUuid('proof_id')->nullable()->constrained('documents')->onDelete('set null');
                $table->timestamps();
            });
        }

        // 7. Cash Bank Transactions (Mutasi Buku Kas / Bank Rekonsiliasi)
        if (!Schema::hasTable('cash_bank_transactions')) {
            Schema::create('cash_bank_transactions', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('account_id')->constrained('cash_bank_accounts')->onDelete('cascade');
                $table->date('trx_date');
                $table->decimal('amount', 18, 2);
                $table->text('description')->nullable();
                $table->boolean('reconciled')->default(false);
                $table->string('ref_type')->nullable();
                $table->uuid('ref_id')->nullable();
                $table->timestamps();
            });
        }

        // 8. Journal Entries (Jurnal Umum Double-Entry Akuntansi)
        if (!Schema::hasTable('journal_entries')) {
            Schema::create('journal_entries', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->date('entry_date');
                $table->string('source_type')->nullable(); // INVOICE, BILL, CASH_PAYMENT
                $table->uuid('source_id')->nullable();
                $table->text('memo')->nullable();
                $table->foreignUuid('posted_by_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
            });
        }

        // 9. Journal Lines (Baris Debit & Kredit Jurnal Umum)
        if (!Schema::hasTable('journal_lines')) {
            Schema::create('journal_lines', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('entry_id')->constrained('journal_entries')->onDelete('cascade');
                $table->foreignUuid('account_id')->constrained('chart_of_accounts')->onDelete('cascade');
                $table->decimal('debit', 18, 2)->nullable();
                $table->decimal('credit', 18, 2)->nullable();
                $table->timestamps();
            });
        }

        // 10. Approvals (Jenjang Otorisasi Keuangan & Talangan Dana PPJK)
        if (!Schema::hasTable('approvals')) {
            Schema::create('approvals', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('object_type');
                $table->uuid('object_id');
                $table->integer('step')->default(1);
                $table->string('approver_role')->nullable();
                $table->string('status'); // PENDING, APPROVED, REJECTED
                $table->foreignUuid('decided_by_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('decided_at')->nullable();
                $table->text('reason')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approvals');
        Schema::dropIfExists('journal_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('cash_bank_transactions');
        Schema::dropIfExists('vendor_payments');
        Schema::dropIfExists('cash_bank_accounts');
        Schema::dropIfExists('vendor_bills');
        Schema::dropIfExists('client_invoice_lines');
        Schema::dropIfExists('cost_items');
        Schema::dropIfExists('chart_of_accounts');
    }
};
