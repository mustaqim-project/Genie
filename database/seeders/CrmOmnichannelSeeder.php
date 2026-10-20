<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\CrmContact;
use App\Models\CrmConversation;
use App\Models\CrmMessage;
use App\Models\CrmTemplate;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaInteraction;
use App\Models\SocialMediaPost;
use App\Models\User;
use App\Models\WhatsappBroadcastCampaign;
use App\Models\WhatsappBroadcastRecipient;
use App\Models\WhatsappSetting;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CrmOmnichannelSeeder extends Seeder
{
    public function run(): void
    {
        // 1. WhatsApp Official WABA Gateway Setting (Single System Company Owned)
        if (WhatsappSetting::count() === 0) {
            WhatsappSetting::create([
                'waba_id' => '104928374829103',
                'phone_number_id' => '109283746501928',
                'display_phone_number' => '+62 812-9000-8888',
                'verified_name' => 'PT AGUNG TRANS LOGISTICS',
                'access_token' => 'EAAK...WABA_SYSTEM_PERMANENT_TOKEN',
                'app_id' => '948271039482710',
                'app_secret' => '948271039482710secret',
                'webhook_verify_token' => 'genie_forwarding_waba_webhook_token_2026',
                'quality_rating' => 'GREEN',
                'messaging_limit_tier' => 'TIER_10K',
                'is_connected' => true,
                'auto_send_sppb' => true,
                'auto_send_npe' => true,
                'auto_send_do' => true,
                'auto_send_billing' => true,
            ]);
        }

        // 2. Official Meta Approved Message Templates
        if (CrmTemplate::count() === 0) {
            CrmTemplate::create([
                'name' => 'sppb_notification_v1',
                'language' => 'id',
                'category' => 'UTILITY',
                'header_text' => 'Pemberitahuan Pengeluaran Barang (SPPB Bea Cukai)',
                'body' => "Halo {{1}},\n\nKabar baik! Dokumen PIB No. Aju *{{2}}* atas shipment *{{3}}* telah resmi diterbitkan *SPPB (Surat Persetujuan Pengeluaran Barang)* oleh Bea Cukai dengan status *JALUR HIJAU*.\n\nEstimasi delivery trucking ke gudang Anda: *{{4}}*.\n\nKlik link di bawah ini untuk melihat detail dokumen dan status peti kemas.",
                'footer_text' => 'PT Agung Trans Logistics • Sistem Otomasi CEISA 4.0',
                'buttons' => [
                    ['type' => 'URL', 'text' => 'Lacak Kargo Real-time', 'url' => 'https://genie.agungtrans.com/portal/track/{{2}}'],
                    ['type' => 'QUICK_REPLY', 'text' => 'Hubungi Traffic / Truk'],
                ],
                'meta_status' => 'APPROVED',
            ]);

            CrmTemplate::create([
                'name' => 'npe_export_readiness',
                'language' => 'id',
                'category' => 'UTILITY',
                'header_text' => 'Nota Pelayanan Ekspor (NPE) Terbit',
                'body' => "Yth. {{1}},\n\nDokumen PEB No. Aju *{{2}}* telah memperoleh *NPE* dari Bea Cukai. Kontainer telah siap muat (stufing) ke kapal *{{3}}* dengan closing time pelabuhan *{{4}}*.\n\nTerima kasih telah mempercayakan pengiriman ekspor bersama Agung Trans.",
                'footer_text' => 'PT Agung Trans Logistics • Ekspor Divisi',
                'buttons' => [
                    ['type' => 'URL', 'text' => 'Unduh NPE Resmi (PDF)', 'url' => 'https://genie.agungtrans.com/portal/npe/{{2}}'],
                ],
                'meta_status' => 'APPROVED',
            ]);

            CrmTemplate::create([
                'name' => 'billing_mpn_reminder',
                'language' => 'id',
                'category' => 'UTILITY',
                'header_text' => 'Kode Billing Pembayaran Pabean (Bea Masuk & Pajak)',
                'body' => "Yth. Finance {{1}},\n\nKode Billing MPN untuk No. Aju *{{2}}* telah terbit sebesar *Rp {{3}}* (Bea Masuk, PPN Impor, PPh 22).\n\nJatuh tempo pembayaran: *{{4}}*.\nHarap segera melakukan setoran via Internet Banking / Bank Persepsi agar jalur pabean dapat segera diproses.",
                'footer_text' => 'Divisi Keuangan & Billing Bea Cukai',
                'buttons' => [
                    ['type' => 'URL', 'text' => 'Salin Kode Billing MPN', 'url' => 'https://genie.agungtrans.com/portal/billing/{{2}}'],
                ],
                'meta_status' => 'APPROVED',
            ]);

            CrmTemplate::create([
                'name' => 'sailing_schedule_broadcast',
                'language' => 'id',
                'category' => 'MARKETING',
                'header_text' => 'Jadwal Kapal Direct Service Asia - Tanjung Priok',
                'body' => "Halo Rekan Bisnis {{1}},\n\nBerikut update Sailing Schedule pelayaran langsung FCL/LCL dari Port of Shanghai, Ningbo, dan Shenzhen ke Jakarta untuk keberangkatan minggu ini.\n\nNikmati penawaran freight rate spesial dan jaminan space kontainer.",
                'footer_text' => 'Sales & Commercial Team • PT Agung Trans',
                'buttons' => [
                    ['type' => 'URL', 'text' => 'Cek Jadwal & Booking', 'url' => 'https://genie.agungtrans.com/schedule'],
                    ['type' => 'QUICK_REPLY', 'text' => 'Minta Penawaran / RFQ'],
                ],
                'meta_status' => 'APPROVED',
            ]);
        }

        // 3. WhatsApp Broadcast Campaigns & Recipients
        if (WhatsappBroadcastCampaign::count() === 0) {
            $campaign1 = WhatsappBroadcastCampaign::create([
                'title' => 'Sailing Schedule Week 43: Direct Service Shanghai - Tanjung Priok',
                'category' => 'MARKETING',
                'message' => "Update Jadwal Kapal Langsung (Direct Service) Pelayaran Shanghai - Jakarta ETA 28 Oktober 2026. Tersedia space 20ft & 40ft HC dengan tarif kompetitif All-In.",
                'media_url' => 'https://images.unsplash.com/photo-1578575437130-527eed3abbec?auto=format&fit=crop&w=800&q=80',
                'target_type' => 'importers',
                'total_recipients' => 12,
                'total_sent' => 12,
                'total_failed' => 0,
                'status' => 'completed',
                'scheduled_at' => Carbon::now()->subDays(2),
                'sent_at' => Carbon::now()->subDays(2)->addMinutes(5),
            ]);

            $campaign2 = WhatsappBroadcastCampaign::create([
                'title' => 'Sosialisasi Kebijakan Lartas Impor Besi Baja & Tekstil Permendag 2026',
                'category' => 'ANNOUNCEMENT',
                'message' => "Pemberitahuan kepada seluruh importir mitra: Mulai 1 November 2026, dokumen Persetujuan Impor (PI) dan Laporan Surveyor (LS) wajib sinkron dengan Sistem INSW CEISA 4.0. Tim kepabeanan kami siap mendampingi verifikasi dokumen Anda.",
                'media_url' => null,
                'target_type' => 'all',
                'total_recipients' => 15,
                'total_sent' => 15,
                'total_failed' => 0,
                'status' => 'completed',
                'scheduled_at' => Carbon::now()->subDays(5),
                'sent_at' => Carbon::now()->subDays(5)->addMinutes(10),
            ]);

            // Add recipients from existing clients
            $clients = Client::all();
            foreach ($clients as $client) {
                WhatsappBroadcastRecipient::create([
                    'campaign_id' => $campaign1->id,
                    'client_id' => $client->id,
                    'recipient_name' => $client->pic_name ?: $client->name,
                    'phone_number' => $client->phone ?: '+6281234567890',
                    'status' => 'sent',
                    'sent_at' => Carbon::now()->subDays(2)->addMinutes(2),
                ]);
            }
        }

        // 4. Social Media Accounts (Official Company Channels)
        if (SocialMediaAccount::count() === 0) {
            $li = SocialMediaAccount::create([
                'platform' => 'linkedin',
                'account_id' => 'urn:li:organization:98471029',
                'account_name' => 'PT Agung Trans Forwarding & Supply Chain',
                'username' => 'agung-trans-logistics',
                'profile_picture_url' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=200&q=80',
                'status' => 'active',
                'followers_count' => 5240,
                'metadata' => [
                    'verified' => true,
                    'category' => 'Freight & Logistics Services',
                    'headquarters' => 'Jakarta Utara, DKI Jakarta',
                ],
            ]);

            $ig = SocialMediaAccount::create([
                'platform' => 'instagram',
                'account_id' => '17841402948201948',
                'account_name' => 'Agung Trans Logistics Indonesia',
                'username' => 'agungtrans.logistics',
                'profile_picture_url' => 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=200&q=80',
                'status' => 'active',
                'followers_count' => 8920,
                'metadata' => [
                    'account_type' => 'BUSINESS',
                    'biography' => 'International Freight Forwarder & PPJK Customs Broker. Spesialis FCL, LCL, Air Cargo & Trucking Pelabuhan Tanjung Priok.',
                ],
            ]);

            $fb = SocialMediaAccount::create([
                'platform' => 'facebook',
                'account_id' => '102938475610293',
                'account_name' => 'PT Agung Trans Forwarding Services',
                'username' => 'agungtransofficial',
                'profile_picture_url' => 'https://images.unsplash.com/photo-1494412574643-ff11b0a5c1c3?auto=format&fit=crop&w=200&q=80',
                'status' => 'active',
                'followers_count' => 3450,
                'metadata' => [
                    'page_category' => 'Cargo & Freight Company',
                ],
            ]);

            $tt = SocialMediaAccount::create([
                'platform' => 'tiktok',
                'account_id' => 'tt_agungtrans_6749',
                'account_name' => 'Agung Trans Logistics',
                'username' => 'agungtrans_official',
                'profile_picture_url' => 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&w=200&q=80',
                'status' => 'active',
                'followers_count' => 14200,
                'metadata' => [
                    'total_likes' => '189.4K',
                ],
            ]);

            // 5. Social Media Posts
            SocialMediaPost::create([
                'social_media_account_id' => $li->id,
                'platform' => 'linkedin',
                'content' => "🚢 Update Rute Pelayaran Langsung (Direct Service) Shanghai - Jakarta Week 43.\n\nBagi para importir manufaktur dan retail, kami mengumumkan jadwal keberangkatan kapal langsung dengan waktu tempuh 7 hari pelayaran dari Port of Shanghai menuju Tanjung Priok. Hubungi tim commercial kami untuk penawaran freight rate terbaik.",
                'media_type' => 'image',
                'media_urls' => ['https://images.unsplash.com/photo-1578575437130-527eed3abbec?auto=format&fit=crop&w=800&q=80'],
                'status' => 'published',
                'published_at' => Carbon::now()->subDays(1),
                'metrics' => [
                    'impressions' => 3820,
                    'reach' => 2940,
                    'likes' => 142,
                    'comments' => 18,
                    'shares' => 24,
                ],
            ]);

            SocialMediaPost::create([
                'social_media_account_id' => $ig->id,
                'platform' => 'instagram',
                'content' => "Tahukah Anda apa perbedaan Jalur Hijau, Jalur Kuning, dan Jalur Merah dalam proses kepabeanan impor Bea Cukai?\n\nGeser carousel untuk memahami alur pemeriksaan dokumen pabean CEISA 4.0 dan bagaimana tim PPJK Agung Trans membantu percepatan SPPB Anda! 📦✨ #CustomsClearance #BeaCukai #EksporImpor #FreightForwarder",
                'media_type' => 'image',
                'media_urls' => ['https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=800&q=80'],
                'status' => 'published',
                'published_at' => Carbon::now()->subDays(3),
                'metrics' => [
                    'impressions' => 6410,
                    'reach' => 5120,
                    'likes' => 385,
                    'comments' => 42,
                    'shares' => 59,
                ],
            ]);

            SocialMediaPost::create([
                'social_media_account_id' => $li->id,
                'platform' => 'linkedin',
                'content' => "Jadwal Pemeliharaan Sistem CEISA 4.0 Bea Cukai dan Kesiapan Sistem AI Genie Forwarding dalam Memitigasi Keterlambatan Pengiriman Dokumen Pabean PIB/PEB.",
                'media_type' => 'text',
                'media_urls' => [],
                'status' => 'scheduled',
                'scheduled_at' => Carbon::now()->addDays(2)->setHour(10)->setMinute(0),
                'metrics' => [],
            ]);

            // 6. Social Media Interactions
            SocialMediaInteraction::create([
                'social_media_account_id' => $li->id,
                'platform' => 'linkedin',
                'type' => 'comment',
                'from_name' => 'Bambang Sugiarto (Supply Chain Manager PT Indo Steel)',
                'from_handle' => '@bambang-sugiarto',
                'message' => 'Apakah Agung Trans menyediakan layanan trucking kontainer heavy duty untuk muatan coil besi di atas 28 ton dari Tanjung Priok ke Cilegon?',
                'status' => 'unread',
                'created_time' => Carbon::now()->subHours(3),
            ]);

            SocialMediaInteraction::create([
                'social_media_account_id' => $ig->id,
                'platform' => 'instagram',
                'type' => 'dm',
                'from_name' => 'Clara Novita (@clara_novita99)',
                'from_handle' => '@clara_novita99',
                'message' => 'Halo admin, untuk impor komoditas kosmetik dari Korea apakah wajib ada Surat Rekomendasi BPOM terlebih dahulu sebelum kapal sandar?',
                'status' => 'unread',
                'created_time' => Carbon::now()->subHours(6),
            ]);
        }

        // 7. CRM Contacts & Conversations
        if (CrmContact::count() === 0) {
            $user = User::first();
            $clients = Client::take(3)->get();

            // Ensure channel exists
            $channel = DB::table('crm_channels')->where('kind', 'WHATSAPP')->first();
            if (!$channel) {
                $channelId = (string) Str::uuid();
                DB::table('crm_channels')->insert([
                    'id' => $channelId,
                    'kind' => 'WHATSAPP',
                    'label' => 'WhatsApp Meta WABA Resmi (+62 812-9000-8888)',
                    'external_id' => '109283746501928',
                    'status' => 'ACTIVE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $channelId = $channel->id;
            }

            foreach ($clients as $client) {
                $contact = CrmContact::create([
                    'client_id' => $client->id,
                    'name' => $client->pic_name ?: $client->name,
                    'phone' => $client->phone ?: '+6281234567890',
                    'handle' => $client->code,
                    'opt_in' => true,
                ]);

                $conv = CrmConversation::create([
                    'contact_id' => $contact->id,
                    'channel_id' => $channelId,
                    'assignee_id' => $user?->id,
                    'status' => 'OPEN',
                    'last_message_at' => Carbon::now()->subMinutes(15),
                ]);

                CrmMessage::create([
                    'id' => (string) Str::uuid(),
                    'conversation_id' => $conv->id,
                    'direction' => 'INBOUND',
                    'body' => "Halo tim Agung Trans, mohon info apakah kontainer TCLU9482710 untuk dokumen aju 040320-00123 sudah keluar SPPB dari Bea Cukai?",
                    'sent_by' => 'CLIENT',
                    'ai_generated' => false,
                    'created_at' => Carbon::now()->subMinutes(25),
                ]);

                CrmMessage::create([
                    'id' => (string) Str::uuid(),
                    'conversation_id' => $conv->id,
                    'direction' => 'OUTBOUND',
                    'body' => "Selamat siang Pak " . ($client->pic_name ?: 'Mitra') . ". SPPB sudah resmi diterbitkan Jalur Hijau per pukul 10:15 WIB hari ini. Surat jalan dan tim truk pengangkut sedang proses gate out di TPS Koja.",
                    'sent_by' => 'USER',
                    'ai_generated' => false,
                    'created_at' => Carbon::now()->subMinutes(15),
                ]);
            }
        }
    }
}
