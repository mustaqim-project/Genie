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
        Schema::table('shipments', function (Blueprint $table) {
            $table->string('customs_channel', 20)->nullable()->after('status_tahap'); // HIJAU, KUNING, MERAH, HOLD
            $table->text('trucking_vendor_reason')->nullable()->after('customs_channel');
            $table->json('customs_hold_steps')->nullable()->after('trucking_vendor_reason');
            $table->timestamp('pod_signed_at')->nullable()->after('customs_hold_steps');
            $table->timestamp('closed_at')->nullable()->after('pod_signed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn([
                'customs_channel',
                'trucking_vendor_reason',
                'customs_hold_steps',
                'pod_signed_at',
                'closed_at',
            ]);
        });
    }
};
