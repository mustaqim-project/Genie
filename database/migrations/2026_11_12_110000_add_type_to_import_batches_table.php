<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('import_batches') && !Schema::hasColumn('import_batches', 'type')) {
            Schema::table('import_batches', function (Blueprint $table) {
                $table->string('type', 20)->nullable()->after('client_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('import_batches') && Schema::hasColumn('import_batches', 'type')) {
            Schema::table('import_batches', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
    }
};

