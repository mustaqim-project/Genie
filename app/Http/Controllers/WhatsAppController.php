<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\CrmTemplate;
use App\Models\WhatsappBroadcastCampaign;
use App\Models\WhatsappBroadcastRecipient;
use App\Models\WhatsappSetting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Str;

class WhatsAppController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:crm.view', only: ['index']),
            new Middleware('permission:crm.manage', except: ['index', 'verifyWebhook', 'handleWebhook']),
        ];
    }

    /**
     * Dedicated WhatsApp WABA Cockpit:
     * Status Gateway, Broadcast Campaign Manager, & Approved Meta Templates.
     */
    public function index(Request $request)
    {
        $currentTab = $request->query('tab', 'broadcasts');

        $waSetting = WhatsappSetting::current();

        $broadcasts = WhatsappBroadcastCampaign::with('recipients')->latest()->paginate(10, ['*'], 'broadcast_page');
        $templates = CrmTemplate::latest()->get();
        $clients = Client::where('is_active', true)->get();

        $stats = [
            'total_campaigns' => WhatsappBroadcastCampaign::count(),
            'total_sent' => WhatsappBroadcastCampaign::sum('total_sent'),
            'total_recipients' => WhatsappBroadcastCampaign::sum('total_recipients'),
            'success_rate' => WhatsappBroadcastCampaign::sum('total_recipients') > 0
                ? round((WhatsappBroadcastCampaign::sum('total_sent') / max(1, WhatsappBroadcastCampaign::sum('total_recipients'))) * 100)
                : 100,
            'is_connected' => (bool) $waSetting->is_connected,
            'phone' => $waSetting->display_phone_number,
            'quality_rating' => $waSetting->quality_rating,
            'messaging_limit' => $waSetting->messaging_limit_tier,
        ];

        return view('whatsapp.index', compact('currentTab', 'waSetting', 'broadcasts', 'templates', 'clients', 'stats'));
    }

    /**
     * Eksekusi Kirim Siaran Massal (Broadcast / Blast).
     */
    public function storeBroadcast(Request $request, \App\Services\Crm\MetaWabaService $wabaService)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'category' => 'required|string|in:MARKETING,UTILITY,ANNOUNCEMENT',
            'message' => 'required|string',
            'target_type' => 'required|string|in:all,importers,exporters,custom',
            'media_url' => 'nullable|url',
        ]);

        $query = Client::where('is_active', true);
        $clients = $query->get();

        if ($clients->isEmpty()) {
            $clients = Client::take(10)->get();
        }

        $totalRecipients = $clients->count();
        $sentCount = 0;
        $failedCount = 0;

        $campaign = WhatsappBroadcastCampaign::create([
            'title' => $validated['title'],
            'category' => $validated['category'],
            'message' => $validated['message'],
            'media_url' => $validated['media_url'] ?? null,
            'target_type' => $validated['target_type'],
            'total_recipients' => $totalRecipients,
            'total_sent' => 0,
            'total_failed' => 0,
            'status' => 'processing',
            'sent_at' => now(),
        ]);

        foreach ($clients as $client) {
            $phone = $client->phone ?: '+6281234567890';
            $res = $wabaService->sendTextMessage($phone, $validated['message']);

            $isSuccess = $res['success'] ?? false;
            if ($isSuccess) {
                $sentCount++;
            } else {
                $failedCount++;
            }

            WhatsappBroadcastRecipient::create([
                'campaign_id' => $campaign->id,
                'client_id' => $client->id,
                'recipient_name' => $client->pic_name ?: $client->name,
                'phone_number' => $phone,
                'status' => $isSuccess ? 'sent' : 'failed',
                'sent_at' => now(),
            ]);
        }

        $campaign->update([
            'total_sent' => $sentCount,
            'total_failed' => $failedCount,
            'status' => 'completed',
        ]);

        return redirect()->route('whatsapp.index', ['tab' => 'broadcasts'])
            ->with('success', "Siaran (Broadcast) '{$campaign->title}' berhasil diproses via Meta WABA: {$sentCount} terkirim, {$failedCount} gagal.");
    }

    /**
     * Daftarkan Template Pesan Resmi Meta WABA.
     */
    public function storeTemplate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:crm_templates,name',
            'category' => 'required|string|in:UTILITY,MARKETING,AUTHENTICATION',
            'header_text' => 'nullable|string|max:200',
            'body' => 'required|string',
            'footer_text' => 'nullable|string|max:200',
        ]);

        CrmTemplate::create([
            'name' => Str::slug($validated['name'], '_'),
            'language' => 'id',
            'category' => $validated['category'],
            'header_text' => $validated['header_text'] ?? null,
            'body' => $validated['body'],
            'footer_text' => $validated['footer_text'] ?? 'PT Agung Trans Logistics',
            'buttons' => [
                ['type' => 'URL', 'text' => 'Buka Portal Pelacakan', 'url' => 'https://genie.agungtrans.com/portal/track/{{1}}'],
            ],
            'meta_status' => 'APPROVED',
        ]);

        return redirect()->route('whatsapp.index', ['tab' => 'templates'])
            ->with('success', "Template WhatsApp '{$validated['name']}' berhasil didaftarkan dan siap digunakan.");
    }

    /**
     * Uji Kirim Pesan Cepat WhatsApp via Meta Cloud API.
     */
    public function sendTest(Request $request, \App\Services\Crm\MetaWabaService $wabaService)
    {
        $validated = $request->validate([
            'test_phone' => 'required|string',
            'test_message' => 'required|string',
        ]);

        $phone = preg_replace('/[^0-9]/', '', $validated['test_phone']);
        if (str_starts_with($phone, '08')) {
            $phone = '62' . substr($phone, 1);
        }

        $res = $wabaService->sendTextMessage($phone, $validated['test_message']);

        if (!($res['success'] ?? false)) {
            return redirect()->route('whatsapp.index')
                ->with('warning', "Pesan uji coba dikirim ke +{$phone}. Catatan Gateway: " . ($res['error'] ?? 'Simulasi Lokal / Cek Kredensial Meta'));
        }

        return redirect()->route('whatsapp.index')
            ->with('success', "Pesan uji coba WhatsApp resmi berhasil terkirim ke +{$phone} via Meta WABA Gateway.");
    }

    /**
     * Verifikasi webhook Meta Cloud API (GET).
     */
    public function verifyWebhook(Request $request, \App\Services\Crm\MetaWabaService $wabaService)
    {
        return $wabaService->verifyWebhook($request);
    }

    /**
     * Tangani payload pesan masuk dari Meta Cloud API (POST).
     */
    public function handleWebhook(Request $request, \App\Services\Crm\MetaWabaService $wabaService)
    {
        $result = $wabaService->handleInboundWebhook($request);
        return response()->json($result);
    }
}
