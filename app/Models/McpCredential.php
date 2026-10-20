<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class McpCredential extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'mcp_credentials';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'name',
        'provider_hint',
        'scope_roles',
        'token_hash',
        'revoked_at',
        'expires_at',
        'last_used_at',
    ];

    protected $casts = [
        'scope_roles' => 'array',
        'revoked_at' => 'datetime',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function callLogs(): HasMany
    {
        return $this->hasMany(McpCallLog::class, 'credential_id');
    }

    /**
     * Cek apakah token masih valid (tidak dicabut dan belum kedaluwarsa).
     */
    public function isValid(): bool
    {
        return !$this->isRevoked() && !$this->isExpired();
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Tandai waktu terakhir token digunakan.
     */
    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Cek izin spesifik (scope) dari token.
     */
    public function hasScope(string $scope): bool
    {
        $scopes = $this->scope_roles ?? [];

        if (in_array('*', $scopes, true)) {
            return true;
        }

        if (in_array($scope, $scopes, true)) {
            return true;
        }

        foreach ($scopes as $pattern) {
            if (str_ends_with($pattern, ':*')) {
                $prefix = substr($pattern, 0, -1);
                if (str_starts_with($scope, $prefix)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Generate secure MCP Access Token baru untuk AI Client.
     *
     * @param  array<int, string>  $scopes
     * @return array{token: string, model: self}
     */
    public static function generateToken(
        User $user,
        string $name,
        array $scopes = ['*'],
        string $providerHint = 'all',
        ?Carbon $expiresAt = null
    ): array {
        $plainText = 'genie_mcp_live_' . Str::random(40);
        $hash = hash('sha256', $plainText);

        $model = self::create([
            'user_id' => $user->id,
            'name' => trim($name),
            'provider_hint' => $providerHint ?: 'all',
            'scope_roles' => array_values(array_unique($scopes)),
            'token_hash' => $hash,
            'expires_at' => $expiresAt,
            'revoked_at' => null,
            'last_used_at' => null,
        ]);

        return [
            'token' => $plainText,
            'model' => $model,
        ];
    }
}
