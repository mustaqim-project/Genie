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
        if (Schema::hasTable('shipments')) {
            Schema::table('shipments', function (Blueprint $table) {
                if (!Schema::hasColumn('shipments', 'portal_token')) {
                    $table->string('portal_token', 64)->nullable()->unique()->index()->after('catatan');
                }
                if (!Schema::hasColumn('shipments', 'portal_expires_at')) {
                    $table->timestamp('portal_expires_at')->nullable()->after('portal_token');
                }
                if (!Schema::hasColumn('shipments', 'portal_last_accessed_at')) {
                    $table->timestamp('portal_last_accessed_at')->nullable()->after('portal_expires_at');
                }
                if (!Schema::hasColumn('shipments', 'portal_duration_days')) {
                    $table->integer('portal_duration_days')->default(7)->after('portal_last_accessed_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('shipments')) {
            Schema::table('shipments', function (Blueprint $table) {
                $columns = ['portal_token', 'portal_expires_at', 'portal_last_accessed_at', 'portal_duration_days'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('shipments', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
