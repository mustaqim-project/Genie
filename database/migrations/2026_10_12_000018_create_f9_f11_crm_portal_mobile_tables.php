<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * F9 - F11: CRM Omnichannel (WhatsApp), Percakapan, Pipeline Deal, Portal Klien, & Push Tokens
     */
    public function up(): void
    {
        // 1. CRM Channels (Kanal Komunikasi: WhatsApp Official, Email, Telegram)
        if (!Schema::hasTable('crm_channels')) {
            Schema::create('crm_channels', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('kind'); // WHATSAPP, EMAIL, TELEGRAM
                $table->string('label')->nullable();
                $table->string('external_id')->nullable();
                $table->string('status')->default('ACTIVE');
                $table->timestamps();
            });
        }

        // 2. CRM Contacts (Buku Kontak Calon Importir / PIC Ekspor)
        if (!Schema::hasTable('crm_contacts')) {
            Schema::create('crm_contacts', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('client_id')->nullable()->constrained('clients')->onDelete('cascade');
                $table->string('name');
                $table->string('phone')->nullable();
                $table->string('handle')->nullable();
                $table->boolean('opt_in')->default(true);
                $table->timestamps();
            });
        }

        // 3. CRM Conversations (Thread Chat / Sesi Percakapan Layanan Pelanggan)
        if (!Schema::hasTable('crm_conversations')) {
            Schema::create('crm_conversations', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('contact_id')->constrained('crm_contacts')->onDelete('cascade');
                $table->foreignUuid('channel_id')->constrained('crm_channels')->onDelete('cascade');
                $table->foreignUuid('assignee_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('status')->default('OPEN'); // OPEN, PENDING, RESOLVED
                $table->timestamp('last_message_at')->nullable();
                $table->timestamps();
            });
        }

        // 4. CRM Templates (Template Pesan WhatsApp Resmi & Notifikasi SPPB)
        if (!Schema::hasTable('crm_templates')) {
            Schema::create('crm_templates', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->string('language')->default('id');
                $table->text('body')->nullable();
                $table->string('meta_status')->nullable();
                $table->timestamps();
            });
        }

        // 5. CRM Messages (Riwayat Pesan Masuk & Keluar / Saran AI)
        if (!Schema::hasTable('crm_messages')) {
            Schema::create('crm_messages', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('conversation_id')->constrained('crm_conversations')->onDelete('cascade');
                $table->string('direction'); // INBOUND / OUTBOUND
                $table->text('body')->nullable();
                $table->foreignUuid('template_id')->nullable()->constrained('crm_templates')->onDelete('set null');
                $table->string('sent_by'); // USER, SYSTEM, BOT
                $table->boolean('ai_generated')->default(false);
                $table->timestamp('created_at')->useCurrent();
            });
        }

        // 6. CRM Pipeline Stages (Tahapan Prospek Forwarder & Negosiasi Tarif)
        if (!Schema::hasTable('crm_pipeline_stages')) {
            Schema::create('crm_pipeline_stages', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->integer('seq')->default(1);
                $table->timestamps();
            });
        }

        // 7. CRM Deals (Peluang Order Muatan Kontainer Klien)
        if (!Schema::hasTable('crm_deals')) {
            Schema::create('crm_deals', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('contact_id')->constrained('crm_contacts')->onDelete('cascade');
                $table->foreignUuid('stage_id')->constrained('crm_pipeline_stages')->onDelete('cascade');
                $table->decimal('value', 18, 2)->nullable();
                $table->foreignUuid('owner_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
            });
        }

        // 8. Client Portal Tokens (Token Magic Link Akses Mandiri Klien)
        if (!Schema::hasTable('client_portal_tokens')) {
            Schema::create('client_portal_tokens', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('client_id')->constrained('clients')->onDelete('cascade');
                $table->string('token_hash')->unique();
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('revoked_at')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamps();
            });
        }

        // 9. Client Portal Activity (Log Aktivitas Klien Melihat Status Pabean & Unduh Billing)
        if (!Schema::hasTable('client_portal_activity')) {
            Schema::create('client_portal_activity', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('token_id')->constrained('client_portal_tokens')->onDelete('cascade');
                $table->string('action'); // VIEW_STATUS, DOWNLOAD_BILLING, VIEW_INVOICE
                $table->foreignUuid('shipment_id')->nullable()->constrained('shipments')->onDelete('set null');
                $table->string('ip')->nullable();
                $table->string('device')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        // 10. Device Tokens (Push Notification Token Mobile PWA / iOS APNs / FCM)
        if (!Schema::hasTable('device_tokens')) {
            Schema::create('device_tokens', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
                $table->string('platform'); // IOS, ANDROID, WEB
                $table->string('push_token')->unique();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
        Schema::dropIfExists('client_portal_activity');
        Schema::dropIfExists('client_portal_tokens');
        Schema::dropIfExists('crm_deals');
        Schema::dropIfExists('crm_pipeline_stages');
        Schema::dropIfExists('crm_messages');
        Schema::dropIfExists('crm_templates');
        Schema::dropIfExists('crm_conversations');
        Schema::dropIfExists('crm_contacts');
        Schema::dropIfExists('crm_channels');
    }
};
