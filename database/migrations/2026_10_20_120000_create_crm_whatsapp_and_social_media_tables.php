<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Single-system (Non-SaaS) CRM enhancements: WhatsApp Business WABA & Multi-Channel Social Media.
     */
    public function up(): void
    {
        // 1. WhatsApp Official Gateway Settings (Meta WABA Cloud API)
        if (!Schema::hasTable('whatsapp_settings')) {
            Schema::create('whatsapp_settings', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('waba_id', 100)->nullable();
                $table->string('phone_number_id', 100)->nullable();
                $table->string('display_phone_number', 50)->default('+62 812-9000-8888');
                $table->string('verified_name', 150)->default('PT AGUNG TRANS LOGISTICS');
                $table->text('access_token')->nullable();
                $table->string('app_id', 100)->nullable();
                $table->string('app_secret', 100)->nullable();
                $table->string('webhook_verify_token', 100)->nullable();
                $table->string('quality_rating', 20)->default('GREEN');
                $table->string('messaging_limit_tier', 30)->default('TIER_10K');
                $table->boolean('is_connected')->default(true);
                $table->boolean('auto_send_sppb')->default(true);
                $table->boolean('auto_send_npe')->default(true);
                $table->boolean('auto_send_do')->default(true);
                $table->boolean('auto_send_billing')->default(true);
                $table->timestamps();
            });
        }

        // 2. WhatsApp Broadcast Campaigns (Pengumuman Jadwal Kapal, Regulasi Kepabeanan, Promo Tarif)
        if (!Schema::hasTable('whatsapp_broadcast_campaigns')) {
            Schema::create('whatsapp_broadcast_campaigns', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('title');
                $table->string('category', 50)->default('MARKETING'); // MARKETING, UTILITY, ANNOUNCEMENT
                $table->text('message');
                $table->string('media_url')->nullable();
                $table->string('target_type', 50)->default('all'); // all, importers, exporters, custom
                $table->unsignedInteger('total_recipients')->default(0);
                $table->unsignedInteger('total_sent')->default(0);
                $table->unsignedInteger('total_failed')->default(0);
                $table->string('status', 30)->default('completed'); // draft, scheduled, processing, completed
                $table->timestamp('scheduled_at')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();
            });
        }

        // 3. WhatsApp Broadcast Recipients
        if (!Schema::hasTable('whatsapp_broadcast_recipients')) {
            Schema::create('whatsapp_broadcast_recipients', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('campaign_id')->constrained('whatsapp_broadcast_campaigns')->cascadeOnDelete();
                $table->foreignUuid('client_id')->nullable()->constrained('clients')->nullOnDelete();
                $table->string('recipient_name');
                $table->string('phone_number');
                $table->string('status', 30)->default('sent'); // pending, sent, failed
                $table->timestamp('sent_at')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamps();
            });
        }

        // 4. Social Media Accounts (Kanal Resmi Perusahaan Forwarding: LinkedIn, IG, FB, TikTok, YT)
        if (!Schema::hasTable('social_media_accounts')) {
            Schema::create('social_media_accounts', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('platform', 30); // linkedin, instagram, facebook, tiktok, youtube
                $table->string('account_id', 100);
                $table->string('account_name', 150);
                $table->string('username', 100)->nullable();
                $table->text('profile_picture_url')->nullable();
                $table->text('access_token')->nullable();
                $table->string('status', 30)->default('active'); // active, disconnected
                $table->unsignedInteger('followers_count')->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        // 5. Social Media Posts (Postingan Edukasi Kepabeanan, Jadwal Kapal, Armada)
        if (!Schema::hasTable('social_media_posts')) {
            Schema::create('social_media_posts', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('social_media_account_id')->nullable()->constrained('social_media_accounts')->nullOnDelete();
                $table->string('platform', 30)->default('all');
                $table->text('content');
                $table->string('media_type', 30)->default('image'); // text, image, video, document
                $table->json('media_urls')->nullable();
                $table->string('status', 30)->default('published'); // draft, scheduled, published, failed
                $table->timestamp('scheduled_at')->nullable();
                $table->timestamp('published_at')->nullable();
                $table->json('metrics')->nullable(); // impressions, reach, likes, comments, shares
                $table->text('error_message')->nullable();
                $table->timestamps();
            });
        }

        // 6. Social Media Interactions (Komentar & DM Masuk dari Calon Klien)
        if (!Schema::hasTable('social_media_interactions')) {
            Schema::create('social_media_interactions', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('social_media_account_id')->nullable()->constrained('social_media_accounts')->nullOnDelete();
                $table->string('platform', 30);
                $table->string('type', 30)->default('comment'); // comment, dm, mention
                $table->string('from_name', 150);
                $table->string('from_handle', 100)->nullable();
                $table->text('message');
                $table->string('status', 30)->default('unread'); // unread, replied, archived
                $table->text('reply_message')->nullable();
                $table->timestamp('replied_at')->nullable();
                $table->timestamp('created_time')->nullable();
                $table->timestamps();
            });
        }

        // 7. Enhance crm_templates for WhatsApp Meta Interactive Components
        if (Schema::hasTable('crm_templates')) {
            Schema::table('crm_templates', function (Blueprint $table) {
                if (!Schema::hasColumn('crm_templates', 'category')) {
                    $table->string('category', 50)->default('UTILITY')->after('language');
                }
                if (!Schema::hasColumn('crm_templates', 'header_text')) {
                    $table->string('header_text', 200)->nullable()->after('category');
                }
                if (!Schema::hasColumn('crm_templates', 'footer_text')) {
                    $table->string('footer_text', 200)->nullable()->after('body');
                }
                if (!Schema::hasColumn('crm_templates', 'buttons')) {
                    $table->json('buttons')->nullable()->after('footer_text');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_media_interactions');
        Schema::dropIfExists('social_media_posts');
        Schema::dropIfExists('social_media_accounts');
        Schema::dropIfExists('whatsapp_broadcast_recipients');
        Schema::dropIfExists('whatsapp_broadcast_campaigns');
        Schema::dropIfExists('whatsapp_settings');

        if (Schema::hasTable('crm_templates')) {
            Schema::table('crm_templates', function (Blueprint $table) {
                $table->dropColumn(['category', 'header_text', 'footer_text', 'buttons']);
            });
        }
    }
};
