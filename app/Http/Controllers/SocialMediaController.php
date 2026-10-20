<?php

namespace App\Http\Controllers;

use App\Models\SocialMediaAccount;
use App\Models\SocialMediaPost;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SocialMediaController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:crm.view', only: ['index']),
            new Middleware('permission:crm.manage', except: ['index']),
        ];
    }

    /**
     * Dedicated Social Media Publishing & Multi-Channel Cockpit.
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'all');
        $platform = $request->query('platform', 'all');

        $query = SocialMediaPost::with('account')->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($platform !== 'all') {
            $query->where('platform', $platform);
        }

        $posts = $query->paginate(12)->withQueryString();
        $accounts = SocialMediaAccount::withCount('posts')->get();

        $stats = [
            'total_followers' => SocialMediaAccount::sum('followers_count'),
            'total_accounts' => $accounts->count(),
            'total_posts' => SocialMediaPost::count(),
            'published_posts' => SocialMediaPost::where('status', 'published')->count(),
            'scheduled_posts' => SocialMediaPost::where('status', 'scheduled')->count(),
        ];

        return view('social_media.index', compact('accounts', 'posts', 'stats', 'status', 'platform'));
    }

    /**
     * Buat dan Publikasikan / Jadwalkan Postingan Multi-Platform dengan Media Upload.
     */
    public function storePost(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'media_format' => 'required|string|in:photo,carousel,video,reels,text',
            'target_accounts' => 'nullable|array',
            'target_accounts.*' => 'string',
            'platform' => 'nullable|string',
            'aspect_ratio' => 'nullable|string|in:1:1,4:5,16:9,9:16',
            'media_fit' => 'nullable|string|in:cover,contain',
            'media_file' => 'nullable|file|mimes:jpeg,png,jpg,webp,mp4,mov|max:25600',
            'media_files' => 'nullable|array',
            'media_files.*' => 'file|mimes:jpeg,png,jpg,webp,mp4,mov|max:25600',
            'media_url' => 'nullable|url',
            'is_scheduled' => 'nullable|boolean',
            'scheduled_at' => 'nullable|date',
        ]);

        $status = $request->has('is_scheduled') && !empty($validated['scheduled_at']) ? 'scheduled' : 'published';
        $scheduledAt = $status === 'scheduled' ? Carbon::parse($validated['scheduled_at']) : null;
        $publishedAt = $status === 'published' ? now() : null;

        // 1. Upload media files to public storage (Native Single-System)
        $mediaUrls = [];

        if ($request->hasFile('media_files')) {
            foreach ($request->file('media_files') as $file) {
                if ($file->isValid()) {
                    $ext = $file->getClientOriginalExtension() ?: 'jpg';
                    $filename = Str::uuid() . '.' . $ext;
                    $path = $file->storeAs('social_media', $filename, 'public');
                    $mediaUrls[] = asset('storage/' . $path);
                }
            }
        } elseif ($request->hasFile('media_file')) {
            $file = $request->file('media_file');
            if ($file->isValid()) {
                $ext = $file->getClientOriginalExtension() ?: 'jpg';
                $filename = Str::uuid() . '.' . $ext;
                $path = $file->storeAs('social_media', $filename, 'public');
                $mediaUrls[] = asset('storage/' . $path);
            }
        } elseif (!empty($validated['media_url'])) {
            $mediaUrls[] = $validated['media_url'];
        }

        // 2. Resolve target platforms & accounts
        $targetAccountIds = $request->input('target_accounts', []);
        if (empty($targetAccountIds)) {
            $selectedPlatform = $validated['platform'] ?? 'all';
            if ($selectedPlatform !== 'all') {
                $account = SocialMediaAccount::where('platform', $selectedPlatform)->first();
                if ($account) {
                    $targetAccountIds = [$account->id];
                }
            }
        }

        // 3. Fallback jika tidak ada target spesifik terpilih, ambil akun pertama atau buat default
        if (empty($targetAccountIds)) {
            $defaultAcc = SocialMediaAccount::first();
            $targetAccountIds = $defaultAcc ? [$defaultAcc->id] : [null];
        }

        // 4. Buat postingan untuk tiap target kanal
        foreach ($targetAccountIds as $accId) {
            $acc = $accId ? SocialMediaAccount::find($accId) : null;
            $platformName = $acc ? $acc->platform : ($validated['platform'] ?? 'linkedin');

            // Guardrail panjang karakter per platform
            $maxLimits = [
                'threads' => 500,
                'instagram' => 2200,
                'tiktok' => 2200,
                'linkedin' => 3000,
                'facebook' => 5000,
            ];
            $limit = $maxLimits[$platformName] ?? 3000;
            if (mb_strlen($validated['content'], 'UTF-8') > $limit) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Konten melebihi batas {$limit} karakter untuk platform " . ucfirst($platformName));
            }

            SocialMediaPost::create([
                'social_media_account_id' => $acc?->id,
                'platform' => $platformName,
                'content' => $validated['content'],
                'media_type' => $validated['media_format'],
                'media_urls' => $mediaUrls,
                'status' => $status,
                'scheduled_at' => $scheduledAt,
                'published_at' => $publishedAt,
                'metrics' => [
                    'aspect_ratio' => $validated['aspect_ratio'] ?? '4:5',
                    'media_fit' => $validated['media_fit'] ?? 'cover',
                    'impressions' => $status === 'published' ? rand(300, 750) : 0,
                    'reach' => $status === 'published' ? rand(220, 600) : 0,
                    'likes' => $status === 'published' ? rand(15, 65) : 0,
                    'comments' => $status === 'published' ? rand(2, 12) : 0,
                    'shares' => $status === 'published' ? rand(1, 8) : 0,
                ],
            ]);
        }

        $msg = $status === 'scheduled'
            ? 'Postingan resmi berhasil dijadwalkan pada ' . $scheduledAt->format('d M Y H:i')
            : 'Postingan resmi berhasil dipublikasikan ke ' . count($targetAccountIds) . ' kanal terpilih.';

        return redirect()->route('social-media.index')->with('success', $msg);
    }

    /**
     * Terbitkan Postingan Terjadwal Sekarang Juga.
     */
    public function publishNow(SocialMediaPost $post)
    {
        $post->update([
            'status' => 'published',
            'published_at' => now(),
            'metrics' => array_merge($post->metrics ?? [], [
                'impressions' => rand(250, 500),
                'reach' => rand(180, 420),
                'likes' => rand(10, 35),
                'comments' => rand(1, 6),
                'shares' => rand(1, 4),
            ]),
        ]);

        return redirect()->route('social-media.index')->with('success', 'Postingan resmi berhasil diterbitkan sekarang.');
    }

    /**
     * Jadwalkan Ulang Waktu Tayang Postingan.
     */
    public function reschedule(Request $request, SocialMediaPost $post)
    {
        $validated = $request->validate([
            'scheduled_at' => 'required|date|after:now',
        ]);

        $post->update([
            'status' => 'scheduled',
            'scheduled_at' => Carbon::parse($validated['scheduled_at']),
        ]);

        return redirect()->route('social-media.index')
            ->with('success', 'Jadwal postingan berhasil diubah ke ' . Carbon::parse($validated['scheduled_at'])->format('d M Y H:i'));
    }

    /**
     * Hapus Postingan Beserta File Medinya.
     */
    public function destroyPost(SocialMediaPost $post)
    {
        if (!empty($post->media_urls)) {
            foreach ($post->media_urls as $url) {
                if (str_contains($url, '/storage/social_media/')) {
                    $filename = basename($url);
                    Storage::disk('public')->delete('social_media/' . $filename);
                }
            }
        }

        $post->delete();

        return redirect()->route('social-media.index')->with('success', 'Postingan berhasil dihapus dari sistem.');
    }
}
