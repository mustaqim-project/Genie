<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * F4: Delivery Order (DO), Pelayaran, Kontainer, Free Time, & Tracking
     */
    public function up(): void
    {
        // 1. Carriers (Pelayaran / Shipping Lines / Agen Kapal)
        if (!Schema::hasTable('carriers')) {
            Schema::create('carriers', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('code')->unique();
                $table->string('name');
                $table->string('email')->nullable();
                $table->string('tracking_provider')->nullable();
                $table->timestamps();
            });
        }

        // 2. Shipment Voyages (Jadwal Kapal, Rute POL-POD & Free Time Demurrage)
        if (!Schema::hasTable('shipment_voyages')) {
            Schema::create('shipment_voyages', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('shipment_id')->constrained('shipments')->onDelete('cascade');
                $table->foreignUuid('carrier_id')->constrained('carriers')->onDelete('cascade');
                $table->string('vessel')->nullable();
                $table->string('voyage')->nullable();
                $table->timestamp('etd')->nullable();
                $table->timestamp('eta')->nullable();
                $table->foreignUuid('pol_id')->nullable()->constrained('ports')->onDelete('set null');
                $table->foreignUuid('pod_id')->nullable()->constrained('ports')->onDelete('set null');
                $table->integer('free_time_days')->default(7);
                $table->timestamps();
            });
        }

        // 3. Shipment Containers (Nomor Kontainer, Tipe, & Segel)
        if (!Schema::hasTable('shipment_containers')) {
            Schema::create('shipment_containers', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('shipment_id')->constrained('shipments')->onDelete('cascade');
                $table->foreignUuid('voyage_id')->nullable()->constrained('shipment_voyages')->onDelete('set null');
                $table->string('number');
                $table->foreignUuid('type_id')->nullable()->constrained('container_types')->onDelete('set null');
                $table->string('seal_no')->nullable();
                $table->timestamps();
            });
        }

        // 4. Tracking Events (Event Pelacakan Posisi Kapal Real-time)
        if (!Schema::hasTable('tracking_events')) {
            Schema::create('tracking_events', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('voyage_id')->constrained('shipment_voyages')->onDelete('cascade');
                $table->string('source')->nullable();
                $table->string('event')->nullable();
                $table->json('position')->nullable();
                $table->timestamp('eta')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        // 5. Carrier Emails (Thread Otomasi Korespondensi Penebusan DO)
        if (!Schema::hasTable('carrier_emails')) {
            Schema::create('carrier_emails', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('shipment_id')->constrained('shipments')->onDelete('cascade');
                $table->foreignUuid('carrier_id')->nullable()->constrained('carriers')->onDelete('set null');
                $table->string('thread_id')->nullable();
                $table->string('direction')->default('OUTBOUND'); // INBOUND / OUTBOUND
                $table->string('subject')->nullable();
                $table->text('body')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();
            });
        }

        // 6. Carrier Invoices (Invoice Biaya Pelayaran, THC, & Uang Jaminan)
        if (!Schema::hasTable('carrier_invoices')) {
            Schema::create('carrier_invoices', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('shipment_id')->constrained('shipments')->onDelete('cascade');
                $table->foreignUuid('carrier_id')->constrained('carriers')->onDelete('cascade');
                $table->string('number')->nullable();
                $table->decimal('amount', 18, 2);
                $table->foreignUuid('currency_id')->nullable()->constrained('currencies')->onDelete('set null');
                $table->date('due_date')->nullable();
                $table->string('status'); // PENDING, APPROVED, PAID
                $table->decimal('tariff_diff', 18, 2)->nullable();
                $table->foreignUuid('approved_by_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();
            });
        }

        // 7. DO Validity Events (Status Masa Berlaku DO & Biaya Perpanjangan)
        if (!Schema::hasTable('do_validity_events')) {
            Schema::create('do_validity_events', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('shipment_id')->constrained('shipments')->onDelete('cascade');
                $table->string('status');
                $table->date('valid_to')->nullable();
                $table->decimal('extension_cost', 18, 2)->nullable();
                $table->text('note')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('do_validity_events');
        Schema::dropIfExists('carrier_invoices');
        Schema::dropIfExists('carrier_emails');
        Schema::dropIfExists('tracking_events');
        Schema::dropIfExists('shipment_containers');
        Schema::dropIfExists('shipment_voyages');
        Schema::dropIfExists('carriers');
    }
};
