<?php

namespace App\Http\Controllers;

use App\Models\CeisaDocument;
use App\Models\Client;
use App\Models\CrmContact;
use App\Models\CrmConversation;
use App\Models\CrmMessage;
use App\Models\CrmTemplate;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaInteraction;
use App\Models\SocialMediaPost;
use App\Models\WhatsappBroadcastCampaign;
use App\Models\WhatsappBroadcastRecipient;
use App\Models\WhatsappSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CrmController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:crm.view', only: ['index']),
            new Middleware('permission:crm.manage|document.create', only: ['storeClient']),
            new Middleware('permission:crm.manage', except: ['index', 'storeClient']),
        ];
    }

    /**
     * Unified Single-System CRM Dashboard:
     * Pipeline, WhatsApp Meta WABA (Gateway, Broadcast, Templates, Chat), & Social Media (LinkedIn, IG, FB, TikTok).
     */
    public function index(Request $request)
    {
        $currentTab = $request->query('tab', 'pipeline');

        // 1. Pipeline & Clients Data
        $clients = Client::latest()->paginate(10, ['*'], 'clients_page');
        $recentShipments = CeisaDocument::select('nomor_aju', 'nama_mitra_luar_negeri', 'negara_mitra_luar_negeri', 'status', 'status_description', 'created_at')
            ->latest()
            ->take(8)
            ->get();

        // 2. WhatsApp Gateway (Meta WABA Official Single-System)
        $waSetting = WhatsappSetting::current();

        $waBroadcasts = WhatsappBroadcastCampaign::with('recipients')->latest()->paginate(10, ['*'], 'broadcast_page');
        $waTemplates = CrmTemplate::latest()->get();
        $waConversations = CrmConversation::with(['contact.client', 'messages'])->latest('last_message_at')->get();

        $waStats = [
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

        // 3. Multi-Channel Social Media (Official Forwarding Company Accounts)
        $socialAccounts = SocialMediaAccount::withCount('posts')->get();
        $socialPosts = SocialMediaPost::with('account')->latest()->paginate(10, ['*'], 'posts_page');
        $socialInteractions = SocialMediaInteraction::with('account')->latest()->paginate(10, ['*'], 'interactions_page');

        $socialStats = [
            'total_followers' => SocialMediaAccount::sum('followers_count'),
            'total_accounts' => $socialAccounts->count(),
            'total_posts' => SocialMediaPost::count(),
            'published_posts' => SocialMediaPost::where('status', 'published')->count(),
            'scheduled_posts' => SocialMediaPost::where('status', 'scheduled')->count(),
            'unread_interactions' => SocialMediaInteraction::where('status', 'unread')->count(),
        ];

        return view('crm.index', compact(
            'currentTab',
            'clients',
            'recentShipments',
            'waSetting',
            'waBroadcasts',
            'waTemplates',
            'waConversations',
            'waStats',
            'socialAccounts',
            'socialPosts',
            'socialInteractions',
            'socialStats'
        ));
    }

    /**
     * Daftarkan Klien / Pemilik Barang Baru (CRM Master Directory & Dokumen Proyek)
     */
    public function storeClient(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'npwp' => 'nullable|string|max:35',
            'nib' => 'nullable|string|max:35',
            'pic_name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string',
            'surat_kuasa_expired_at' => 'nullable|date',
        ]);

        $nextNum = Client::count() + 1;
        $code = 'CLI-' . date('Ym') . '-' . str_pad((string) $nextNum, 4, '0', STR_PAD_LEFT);

        $client = Client::create([
            'code' => $code,
            'name' => trim($validated['name']),
            'npwp' => $validated['npwp'] ?? null,
            'nib' => $validated['nib'] ?? null,
            'pic_name' => $validated['pic_name'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'surat_kuasa_expired_at' => $validated['surat_kuasa_expired_at'] ?? null,
            'is_active' => true,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => "Klien {$client->name} berhasil didaftarkan.",
                'data' => [
                    'id' => $client->id,
                    'name' => $client->name,
                    'npwp' => $client->npwp ?? '-',
                    'code' => $client->code,
                ],
            ]);
        }

        return back()->with('success', "Klien {$client->name} ({$client->code}) berhasil ditambahkan ke direktori.");
    }

    /**
     * Tampilkan detail klien (JSON atau detail modal).
     */
    public function showClient(string $id)
    {
        $client = Client::withCount(['shipments', 'quotations', 'invoices'])->findOrFail($id);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'status' => 'success',
                'data' => $client,
            ]);
        }

        return redirect()->route('crm.index', ['tab' => 'pipeline', 'client_id' => $id]);
    }

    /**
     * Perbarui data Master Klien.
     */
    public function updateClient(Request $request, string $id)
    {
        $client = Client::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'npwp' => 'nullable|string|max:35',
            'nib' => 'nullable|string|max:35',
            'pic_name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string',
            'surat_kuasa_expired_at' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);

        $client->update([
            'name' => trim($validated['name']),
            'npwp' => $validated['npwp'] ?? null,
            'nib' => $validated['nib'] ?? null,
            'pic_name' => $validated['pic_name'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'surat_kuasa_expired_at' => $validated['surat_kuasa_expired_at'] ?? null,
            'is_active' => isset($validated['is_active']) ? (bool) $validated['is_active'] : $client->is_active,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => "Profil Klien {$client->name} berhasil diperbarui.",
                'data' => $client,
            ]);
        }

        return back()->with('success', "Profil Klien {$client->name} ({$client->code}) berhasil diperbarui.");
    }

    /**
     * Toggle status aktif/nonaktif Master Klien.
     */
    public function toggleClientStatus(Request $request, string $id)
    {
        $client = Client::findOrFail($id);
        $client->is_active = !$client->is_active;
        $client->save();

        $statusStr = $client->is_active ? 'diaktifkan' : 'dinonaktifkan';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => "Status Klien {$client->name} berhasil {$statusStr}.",
                'is_active' => $client->is_active,
            ]);
        }

        return back()->with('success', "Status Klien {$client->name} berhasil {$statusStr}.");
    }

    /**
     * Unggah berkas legalitas klien (NIB, NPWP, Surat Kuasa PPJK PDF).
     */
    public function uploadClientLegal(Request $request, string $id)
    {
        $client = Client::findOrFail($id);

        $validated = $request->validate([
            'doc_type' => 'required|in:NIB,NPWP,SURAT_KUASA,LAINNYA',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $fileName = strtolower($validated['doc_type']) . '_' . time() . '.' . $extension;
        $storageDir = storage_path("app/private/clients/{$client->id}");

        if (!\Illuminate\Support\Facades\File::exists($storageDir)) {
            \Illuminate\Support\Facades\File::makeDirectory($storageDir, 0750, true);
        }

        $file->move($storageDir, $fileName);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => "Dokumen legalitas [{$validated['doc_type']}] untuk klien {$client->name} berhasil diunggah.",
                'file_name' => $fileName,
            ]);
        }

        return back()->with('success', "Dokumen legalitas [{$validated['doc_type']}] untuk klien {$client->name} berhasil diunggah ke repositori aman.");
    }
}
