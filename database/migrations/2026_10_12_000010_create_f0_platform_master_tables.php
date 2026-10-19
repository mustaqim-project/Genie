<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * F0: Platform, Master Data, Geografi, Kepabeanan & Integrasi
     */
    public function up(): void
    {
        // 1. Countries
        if (!Schema::hasTable('countries')) {
            Schema::create('countries', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('code')->unique();
                $table->string('name');
                $table->date('valid_from')->nullable();
                $table->date('valid_to')->nullable();
                $table->timestamps();
            });
        }

        // 2. Currencies
        if (!Schema::hasTable('currencies')) {
            Schema::create('currencies', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('code')->unique();
                $table->string('name');
                $table->date('valid_from')->nullable();
                $table->date('valid_to')->nullable();
                $table->timestamps();
            });
        }

        // 3. Exchange Rates
        if (!Schema::hasTable('exchange_rates')) {
            Schema::create('exchange_rates', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('currency_id')->constrained('currencies')->onDelete('cascade');
                $table->decimal('rate', 18, 2);
                $table->string('source')->nullable();
                $table->date('valid_from')->nullable();
                $table->date('valid_to')->nullable();
                $table->timestamps();
            });
        }

        // 4. Ports (Pelabuhan)
        if (!Schema::hasTable('ports')) {
            Schema::create('ports', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('code')->unique();
                $table->string('name');
                $table->foreignUuid('country_id')->nullable()->constrained('countries')->onDelete('set null');
                $table->date('valid_from')->nullable();
                $table->date('valid_to')->nullable();
                $table->timestamps();
            });
        }

        // 5. Customs Offices (Kantor Pabean DJBC)
        if (!Schema::hasTable('customs_offices')) {
            Schema::create('customs_offices', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('code')->unique();
                $table->string('name');
                $table->foreignUuid('port_id')->nullable()->constrained('ports')->onDelete('set null');
                $table->date('valid_from')->nullable();
                $table->date('valid_to')->nullable();
                $table->timestamps();
            });
        }

        // 6. Incoterms
        if (!Schema::hasTable('incoterms')) {
            Schema::create('incoterms', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('code')->unique();
                $table->string('name')->nullable();
                $table->date('valid_from')->nullable();
                $table->date('valid_to')->nullable();
                $table->timestamps();
            });
        }

        // 7. Container Types (20ft, 40ft, HQ, Reefer dll)
        if (!Schema::hasTable('container_types')) {
            Schema::create('container_types', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('code')->unique();
                $table->string('name');
                $table->integer('size_ft')->nullable();
                $table->date('valid_from')->nullable();
                $table->date('valid_to')->nullable();
                $table->timestamps();
            });
        }

        // 8. Hold Types (Jenis Penahanan / Penjaluran Pabean)
        if (!Schema::hasTable('hold_types')) {
            Schema::create('hold_types', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('code')->unique();
                $table->string('name');
                $table->string('default_action')->nullable();
                $table->integer('default_sla_hours')->nullable();
                $table->date('valid_from')->nullable();
                $table->date('valid_to')->nullable();
                $table->timestamps();
            });
        }

        // 9. Holidays (Hari Libur Nasional & Pelabuhan)
        if (!Schema::hasTable('holidays')) {
            Schema::create('holidays', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->date('date')->unique();
                $table->string('name')->nullable();
                $table->timestamps();
            });
        }

        // 10. System Settings (Pengaturan Sistem Terpusat)
        if (!Schema::hasTable('system_settings')) {
            Schema::create('system_settings', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('key')->unique();
                $table->json('value');
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        // 11. Integration Accounts (Akun Integrasi CEISA / API Eksternal)
        if (!Schema::hasTable('integration_accounts')) {
            Schema::create('integration_accounts', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('provider');
                $table->foreignUuid('client_id')->nullable()->constrained('clients')->onDelete('cascade');
                $table->string('label')->nullable();
                $table->text('secret_ref');
                $table->timestamp('token_expires_at')->nullable();
                $table->string('status')->nullable();
                $table->timestamps();
            });
        }

        // 12. Client Assignments (Penugasan Staff ke Klien)
        if (!Schema::hasTable('client_assignments')) {
            Schema::create('client_assignments', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignUuid('client_id')->constrained('clients')->onDelete('cascade');
                $table->string('process_code')->nullable();
                $table->boolean('is_backup')->default(false);
                $table->timestamps();
            });
        }

        // 13. Audit Logs (Log Forensik & Perubahan Sistem)
        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('actor_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('actor_type');
                $table->string('action');
                $table->string('object_type');
                $table->uuid('object_id')->nullable();
                $table->json('old_value')->nullable();
                $table->json('new_value')->nullable();
                $table->string('ip')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('client_assignments');
        Schema::dropIfExists('integration_accounts');
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('holidays');
        Schema::dropIfExists('hold_types');
        Schema::dropIfExists('container_types');
        Schema::dropIfExists('incoterms');
        Schema::dropIfExists('customs_offices');
        Schema::dropIfExists('ports');
        Schema::dropIfExists('exchange_rates');
        Schema::dropIfExists('currencies');
        Schema::dropIfExists('countries');
    }
};
