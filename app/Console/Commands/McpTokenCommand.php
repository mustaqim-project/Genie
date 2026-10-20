<?php

namespace App\Console\Commands;

use App\Models\McpCredential;
use App\Models\User;
use Illuminate\Console\Command;

class McpTokenCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mcp:token 
                            {--name= : Nama deskriptif token (contoh: Claude Desktop, Cursor Tim)}
                            {--provider=all : Provider hint (claude, cursor, antigravity, openai, gemini, python, custom, all)}
                            {--scope=* : Scope permission (default: *)}
                            {--days= : Masa aktif dalam hari (kosongkan jika tidak ada kedaluwarsa)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Buat token akses MCP baru untuk Klien AI (Claude, Cursor, Antigravity, dll)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->option('name');
        if (!$name) {
            $name = $this->ask('Masukkan Nama Token Akses (contoh: Claude Desktop - Laptop Utama)');
        }

        $provider = $this->option('provider') ?: 'all';
        $days = $this->option('days');
        $expiresAt = $days ? now()->addDays((int) $days) : null;

        $user = User::first();
        if (!$user) {
            $this->error('Tidak ada user dalam database untuk mengaitkan token MCP.');
            return 1;
        }

        $result = McpCredential::generateToken(
            user: $user,
            name: $name,
            scopes: ['*'],
            providerHint: $provider,
            expiresAt: $expiresAt
        );

        $this->info('===========================================================');
        $this->info('  GENIE MCP ACCESS TOKEN CREATED SUCCESSFULLY');
        $this->info('===========================================================');
        $this->line("<comment>Nama Token   :</comment> {$result['model']->name}");
        $this->line("<comment>Provider Hint:</comment> {$result['model']->provider_hint}");
        $this->line("<comment>Kedaluwarsa  :</comment> " . ($expiresAt ? $expiresAt->toDateTimeString() : 'Tidak pernah (Permanent)'));
        $this->newLine();
        $this->line('<fg=white;bg=blue;options=bold> TOKEN AKSES PLAIN TEXT (Simpan sekarang, hanya tampil sekali): </fg=white;bg=blue;options=bold>');
        $this->line("<fg=green;options=bold>{$result['token']}</fg=green;options=bold>");
        $this->newLine();
        $this->line("<comment>Contoh Cursor / Claude SSE Endpoint:</comment>");
        $this->line(url("/api/mcp/sse?token={$result['token']}"));
        $this->info('===========================================================');

        return 0;
    }
}
