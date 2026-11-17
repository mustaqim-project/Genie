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
        Schema::create('document_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 100)->unique();
            $table->string('name', 200);
            $table->string('category', 50)->index(); // EMAIL, SURAT_JALAN, TERMS_KLAUSUL, WHATSAPP
            $table->string('division', 50)->nullable()->index(); // marketing, finance, document, traffic, general
            $table->string('subject', 255)->nullable();
            $table->longText('body')->nullable();
            $table->text('terms_and_conditions')->nullable();
            $table->text('header_notes')->nullable();
            $table->text('footer_notes')->nullable();
            $table->string('signee_name', 150)->nullable();
            $table->string('emergency_phone', 100)->nullable();
            $table->json('variables')->nullable();
            $table->boolean('is_default')->default(false)->index();
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_templates');
    }
};
