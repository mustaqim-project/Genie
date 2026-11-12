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
        // 1. Modul To-Do & Task Delegation Engine (F23)
        if (!Schema::hasTable('todos')) {
            Schema::create('todos', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('title');
                $table->text('description')->nullable();
                $table->enum('priority', ['LOW', 'MEDIUM', 'HIGH', 'URGENT'])->default('MEDIUM');
                $table->enum('status', ['TODO', 'IN_PROGRESS', 'WAITING_EXTERNAL', 'DONE', 'CANCELLED'])->default('TODO');
                $table->date('due_date')->nullable();
                $table->uuid('assigned_to_user_id')->nullable()->index();
                $table->uuid('created_by_user_id')->nullable()->index();
                $table->uuid('shipment_id')->nullable()->index();
                $table->string('category')->default('OPERATIONAL'); // OPERATIONAL, CUSTOMS, FINANCE, SALES, GENERAL
                $table->boolean('is_recurring')->default(false);
                $table->string('recurrence_interval')->nullable(); // DAILY, WEEKLY, MONTHLY
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('todo_checklists')) {
            Schema::create('todo_checklists', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('todo_id')->index();
                $table->string('item_text');
                $table->boolean('is_completed')->default(false);
                $table->uuid('completed_by_user_id')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->foreign('todo_id')->references('id')->on('todos')->onDelete('cascade');
            });
        }

        // 2. Multi-Line Quotation & Sales Order (F20)
        if (!Schema::hasTable('quotation_lines')) {
            Schema::create('quotation_lines', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('quotation_id')->index();
                $table->string('service_code')->nullable();
                $table->string('description');
                $table->decimal('quantity', 12, 2)->default(1);
                $table->string('unit')->default('SHIPMENT'); // CBM, TON, CONTAINER, DOCUMENT, SHIPMENT
                $table->decimal('unit_price_idr', 15, 2)->default(0);
                $table->decimal('discount_percent', 5, 2)->default(0); // e.g. 10.00%
                $table->decimal('discount_amount_idr', 15, 2)->default(0);
                $table->decimal('subtotal_idr', 15, 2)->default(0);
                $table->timestamps();

                $table->foreign('quotation_id')->references('id')->on('quotations')->onDelete('cascade');
            });
        }

        // Add discount approval fields to quotations if missing
        Schema::table('quotations', function (Blueprint $table) {
            if (!Schema::hasColumn('quotations', 'discount_approval_status')) {
                $table->enum('discount_approval_status', ['NOT_REQUIRED', 'PENDING_APPROVAL', 'APPROVED', 'REJECTED'])->default('NOT_REQUIRED')->after('status');
                $table->uuid('approved_by_user_id')->nullable()->after('discount_approval_status');
                $table->text('discount_notes')->nullable()->after('approved_by_user_id');
            }
        });

        if (!Schema::hasTable('sales_orders')) {
            Schema::create('sales_orders', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('order_number')->unique();
                $table->uuid('quotation_id')->nullable()->index();
                $table->uuid('client_id')->index();
                $table->date('order_date');
                $table->date('expected_shipment_date')->nullable();
                $table->decimal('total_amount_idr', 15, 2)->default(0);
                $table->enum('status', ['DRAFT', 'CONFIRMED', 'CONVERTED_TO_SHIPMENT', 'CANCELLED'])->default('DRAFT');
                $table->uuid('shipment_id')->nullable()->index();
                $table->uuid('created_by_user_id')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 3. Modul Kunjungan Sales Lapangan (F18)
        if (!Schema::hasTable('sales_visits')) {
            Schema::create('sales_visits', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('sales_user_id')->index();
                $table->uuid('client_id')->index();
                $table->timestamp('check_in_time');
                $table->timestamp('check_out_time')->nullable();
                $table->decimal('latitude', 10, 7);
                $table->decimal('longitude', 10, 7);
                $table->decimal('distance_meters', 8, 2)->nullable();
                $table->boolean('is_out_of_radius')->default(false);
                $table->text('out_of_radius_reason')->nullable();
                $table->string('photo_path')->nullable();
                $table->enum('result_status', ['INTERESTED', 'DEAL_CLOSE', 'FOLLOW_UP_REQUIRED', 'REJECTED', 'NEEDS_QUOTATION'])->default('FOLLOW_UP_REQUIRED');
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_visits');
        Schema::dropIfExists('sales_orders');
        
        if (Schema::hasColumn('quotations', 'discount_approval_status')) {
            Schema::table('quotations', function (Blueprint $table) {
                $table->dropColumn(['discount_approval_status', 'approved_by_user_id', 'discount_notes']);
            });
        }
        
        Schema::dropIfExists('quotation_lines');
        Schema::dropIfExists('todo_checklists');
        Schema::dropIfExists('todos');
    }
};
