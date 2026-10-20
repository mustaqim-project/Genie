<?php

namespace App\Http\Controllers;

use App\Models\CrmConversation;
use App\Models\CrmMessage;
use App\Models\SocialMediaInteraction;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Str;

class InboxController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:crm.view', only: ['index']),
            new Middleware('permission:crm.manage', except: ['index']),
        ];
    }

    /**
     * Unified Omnichannel Inbox:
     * WhatsApp WABA Live Conversations & Multi-Channel Social Media Comments/DMs in one consolidated place.
     */
    public function index(Request $request)
    {
        $channelFilter = $request->query('channel', 'all');
        $statusFilter = $request->query('status', 'all');

        // 1. WhatsApp Conversations
        $waQuery = CrmConversation::with(['contact.client', 'messages'])->latest('last_message_at');
        $waConversations = $waQuery->get();

        // 2. Social Media Interactions
        $socialQuery = SocialMediaInteraction::with('account')->latest('created_time');
        if ($channelFilter !== 'all' && $channelFilter !== 'whatsapp') {
            $socialQuery->where('platform', $channelFilter);
        }
        if ($statusFilter === 'unread') {
            $socialQuery->where('status', 'unread');
        } elseif ($statusFilter === 'replied') {
            $socialQuery->where('status', 'replied');
        }
        $socialInteractions = $socialQuery->get();

        $stats = [
            'total_inbox' => $waConversations->count() + $socialInteractions->count(),
            'unread_social' => SocialMediaInteraction::where('status', 'unread')->count(),
            'active_wa_chats' => $waConversations->count(),
        ];

        return view('inbox.index', compact('channelFilter', 'statusFilter', 'waConversations', 'socialInteractions', 'stats'));
    }

    /**
     * Balas Pesan Chat WhatsApp.
     */
    public function sendWhatsAppMessage(Request $request)
    {
        $validated = $request->validate([
            'conversation_id' => 'required|uuid|exists:crm_conversations,id',
            'message' => 'required|string',
        ]);

        $conv = CrmConversation::findOrFail($validated['conversation_id']);

        CrmMessage::create([
            'id' => (string) Str::uuid(),
            'conversation_id' => $conv->id,
            'direction' => 'OUTBOUND',
            'body' => $validated['message'],
            'sent_by' => 'USER',
            'ai_generated' => false,
            'created_at' => now(),
        ]);

        $conv->update(['last_message_at' => now()]);

        return redirect()->route('inbox.index', ['channel' => 'whatsapp'])
            ->with('success', 'Balasan WhatsApp resmi berhasil terkirim ke klien.');
    }

    /**
     * Balas Interaksi Komentar / DM Media Sosial.
     */
    public function replySocialInteraction(Request $request, $id)
    {
        $validated = $request->validate([
            'reply_message' => 'required|string',
        ]);

        $interaction = SocialMediaInteraction::findOrFail($id);
        $interaction->update([
            'status' => 'replied',
            'reply_message' => $validated['reply_message'],
            'replied_at' => now(),
        ]);

        return redirect()->route('inbox.index', ['channel' => $interaction->platform])
            ->with('success', "Balasan interaksi kepada {$interaction->from_name} ({$interaction->platform}) berhasil dikirimkan.");
    }
}
