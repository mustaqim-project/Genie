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
        Schema::table('mcp_credentials', function (Blueprint $table) {
            if (!Schema::hasColumn('mcp_credentials', 'name')) {
                $table->string('name')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('mcp_credentials', 'provider_hint')) {
                $table->string('provider_hint')->default('all')->after('name');
            }
            if (!Schema::hasColumn('mcp_credentials', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('revoked_at');
            }
            if (!Schema::hasColumn('mcp_credentials', 'last_used_at')) {
                $table->timestamp('last_used_at')->nullable()->after('expires_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mcp_credentials', function (Blueprint $table) {
            $table->dropColumn(['name', 'provider_hint', 'expires_at', 'last_used_at']);
        });
    }
};
