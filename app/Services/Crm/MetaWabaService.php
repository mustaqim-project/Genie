<?php

namespace App\Services\Crm;

use App\Models\CrmContact;
use App\Models\CrmConversation;
use App\Models\CrmMessage;
use App\Models\Shipment;
use App\Models\SystemSetting;
use App\Models\WhatsappSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaWabaService
{
    protected string $baseUrl = 'https://graph.facebook.com/v19.0';
    protected ?WhatsappSetting $setting;
    protected ?string $apiToken;

    public function __construct()
    {
        $metaConfig = SystemSetting::get('whatsapp_meta_config', []);
        $this->setting = WhatsappSetting::first();
        $this->apiToken = config('services.meta.token') ?? env('META_WABA_TOKEN', $metaConfig['api_token'] ?? 'EAAB_MOCK_TOKEN');
        $base = $metaConfig['base_url'] ?? 'https://graph.facebook.com';
        $ver = $metaConfig['api_version'] ?? 'v19.0';
        $this->baseUrl = rtrim($base, '/') . '/' . ltrim($ver, '/');
    }

    /**
     * Kirim pesan teks bebas ke nomor WhatsApp tujuan via Meta Cloud API.
     */
    public function sendTextMessage(string $to, string $message): array
    {
        $phone = preg_replace('/[^0-9]/', '', $to);
        if (str_starts_with($phone, '08')) {
            $phone = '62' . substr($phone, 1);
        }

        $metaConfig = SystemSetting::get('whatsapp_meta_config', []);
        $phoneId = $this->setting?->phone_number_id ?? ($metaConfig['phone_number_id'] ?? '100987654321098');
        $url = "{$this->baseUrl}/{$phoneId}/messages";

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $phone,
            'type' => 'text',
            'text' => [
                'preview_url' => true,
                'body' => $message,
            ],
        ];

        try {
            $response = Http::withToken($this->apiToken)
                ->timeout(10)
                ->post($url, $payload);

            if ($response->successful()) {
                Log::info("Meta WABA Text Message Berhasil Terkirim ke +{$phone}: " . $response->body());
                return [
                    'success' => true,
                    'message_id' => $response->json('messages.0.id'),
                    'response' => $response->json(),
                ];
            }

            Log::warning("Meta WABA API Response Non-200 (+{$phone}): " . $response->body());
            return [
                'success' => false,
                'error' => $response->json('error.message') ?? $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error("Koneksi HTTP Meta Cloud API gagal ke +{$phone}: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Kirim pesan template resmi (Utility/Marketing) via Meta Cloud API.
     */
    public function sendTemplateMessage(string $to, string $templateName, array $parameters = []): array
    {
        $phone = preg_replace('/[^0-9]/', '', $to);
        if (str_starts_with($phone, '08')) {
            $phone = '62' . substr($phone, 1);
        }

        $phoneId = $this->setting?->phone_number_id ?? '109283746501928';
        $url = "{$this->baseUrl}/{$phoneId}/messages";

        $bodyParams = [];
        foreach ($parameters as $param) {
            $bodyParams[] = ['type' => 'text', 'text' => (string) $param];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $phone,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => 'id'],
                'components' => [
                    [
                        'type' => 'body',
                        'parameters' => $bodyParams,
                    ],
                ],
            ],
        ];

        try {
            $response = Http::withToken($this->apiToken)
                ->timeout(10)
                ->post($url, $payload);

            return [
                'success' => $response->successful(),
                'response' => $response->json(),
            ];
        } catch (\Throwable $e) {
            Log::error("Gagal mengirim template Meta WABA ke +{$phone}: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verifikasi Veritoken Webhook Meta Cloud API (GET Request).
     */
    public function verifyWebhook(Request $request)
    {
        $metaConfig = SystemSetting::get('whatsapp_meta_config', []);
        $verifyToken = config('services.meta.verify_token') 
            ?? env('META_WEBHOOK_VERIFY_TOKEN', $metaConfig['webhook_verify_token'] ?? 'GENIE_WHATSAPP_SECURE_TOKEN_2026');
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        return response('Forbidden', 403);
    }

    /**
     * Tangani Pesan Masuk (Inbound Webhook) & Auto-Reply Bot Kargo.
     */
    public function handleInboundWebhook(Request $request): array
    {
        $payload = $request->all();
        $entry = $payload['entry'][0]['changes'][0]['value'] ?? null;

        if (!$entry || empty($entry['messages'])) {
            return ['status' => 'no_messages'];
        }

        $messageData = $entry['messages'][0];
        $from = $messageData['from']; // Nomor pengirim
        $text = $messageData['text']['body'] ?? '';
        $msgId = $messageData['id'];

        Log::info("Inbound WhatsApp Message dari {$from}: {$text}");

        // Cari atau buat kontak
        $contact = CrmContact::firstOrCreate(
            ['phone' => "+{$from}"],
            ['name' => $entry['contacts'][0]['profile']['name'] ?? "Pelanggan +{$from}"]
        );

        $conversation = CrmConversation::firstOrCreate(
            ['contact_id' => $contact->id],
            ['channel' => 'whatsapp', 'status' => 'open', 'last_message_at' => now()]
        );

        // Rekam pesan masuk
        CrmMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'CLIENT',
            'sender_id' => $contact->id,
            'direction' => 'INBOUND',
            'content' => $text,
            'meta_id' => $msgId,
            'sent_at' => now(),
        ]);

        $conversation->update(['last_message_at' => now()]);

        // Auto-reply Bot: Cek format "Status {BL_NUMBER}"
        if (preg_match('/^status\s+([A-Za-z0-9\-]+)/i', trim($text), $matches)) {
            $blNumber = strtoupper(trim($matches[1]));
            $shipment = Shipment::where('nomor_bl_awb', 'like', "%{$blNumber}%")->first();

            if ($shipment) {
                $statusTahap = $shipment->status_tahap ?? 'PROSES';
                $jalur = $shipment->customs_channel ?? ($shipment->ceisaDocument?->jalur ?? 'HIJAU');
                $reply = "Halo Bapak/Ibu,\nStatus kargo B/L *{$blNumber}* saat ini:\n\n"
                       . "• Proyek: {$shipment->project_no}\n"
                       . "• Tahapan: {$statusTahap}\n"
                       . "• Jalur Pabean: {$jalur}\n"
                       . "• Kapal: {$shipment->nama_kapal} ({$shipment->voyage_no})\n"
                       . "• Pelacakan: " . route('portal.track', $shipment->tracking_token ?? $shipment->id);
            } else {
                $reply = "Maaf, nomor B/L *{$blNumber}* tidak ditemukan di sistem Genie ERP PT Agung Trans Solusindo. Mohon periksa kembali nomor Anda.";
            }

            // Kirim balasan bot otomatis
            $this->sendTextMessage($from, $reply);

            CrmMessage::create([
                'conversation_id' => $conversation->id,
                'sender_type' => 'BOT',
                'direction' => 'OUTBOUND',
                'content' => $reply,
                'sent_at' => now(),
            ]);
        }

        return ['status' => 'processed', 'from' => $from];
    }
}
