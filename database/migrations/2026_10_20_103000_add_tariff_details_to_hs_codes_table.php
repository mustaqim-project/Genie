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
        Schema::table('hs_codes', function (Blueprint $table) {
            if (!Schema::hasColumn('hs_codes', 'duty_ppn')) {
                $table->decimal('duty_ppn', 5, 2)->default(11.00)->after('duty_bm');
            }
            if (!Schema::hasColumn('hs_codes', 'duty_pph')) {
                $table->decimal('duty_pph', 5, 2)->default(2.50)->after('duty_ppn');
            }
            if (!Schema::hasColumn('hs_codes', 'duty_bk')) {
                $table->decimal('duty_bk', 5, 2)->default(0.00)->after('duty_pph');
            }
            if (!Schema::hasColumn('hs_codes', 'satuan')) {
                $table->string('satuan', 20)->default('PCE')->after('duty_bk');
            }
            if (!Schema::hasColumn('hs_codes', 'has_lartas')) {
                $table->boolean('has_lartas')->default(false)->after('satuan');
            }
            if (!Schema::hasColumn('hs_codes', 'lartas_desc')) {
                $table->text('lartas_desc')->nullable()->after('has_lartas');
            }
            if (!Schema::hasColumn('hs_codes', 'source')) {
                $table->string('source', 50)->default('DJBC_BTKI_2022')->after('lartas_desc');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hs_codes', function (Blueprint $table) {
            $table->dropColumn([
                'duty_ppn',
                'duty_pph',
                'duty_bk',
                'satuan',
                'has_lartas',
                'lartas_desc',
                'source',
            ]);
        });
    }
};
