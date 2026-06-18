<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Models;

use Capell\AgentBridge\Enums\AgentBridgeTokenLifecycleStatus;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use Override;

/**
 * @property int $id
 * @property string $name
 * @property string $token_hash
 * @property array<int, string> $scopes
 * @property bool $is_enabled
 * @property CarbonImmutable|null $revoked_at
 * @property CarbonImmutable|null $rotated_at
 * @property string|null $created_from_ip
 * @property CarbonImmutable|null $last_used_at
 * @property CarbonImmutable|null $expires_at
 * @property-read AgentBridgeTokenLifecycleStatus $lifecycle_status
 * @property Authenticatable|null $user
 */
final class CapellAgentBridgeToken extends Model
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    protected $fillable = [
        'name',
        'token_hash',
        'scopes',
        'is_enabled',
        'created_from_ip',
        'expires_at',
    ];

    protected $table = 'capell_agent_bridge_tokens';

    /** @var array<string, mixed> */
    protected $attributes = [
        'is_enabled' => true,
    ];

    public static function hashPlainTextToken(string $plainTextToken): string
    {
        return hash_hmac('sha256', $plainTextToken, (string) config('app.key'));
    }

    public static function legacyHashPlainTextToken(string $plainTextToken): string
    {
        return hash('sha256', $plainTextToken);
    }

    public static function findForPlainTextToken(string $plainTextToken): ?self
    {
        $token = self::query()
            ->where('token_hash', self::hashPlainTextToken($plainTextToken))
            ->first();

        if ($token instanceof self) {
            return $token;
        }

        if (! (bool) config('capell-agent-bridge.accept_legacy_token_hashes', false)) {
            return null;
        }

        $legacyToken = self::query()
            ->where('token_hash', self::legacyHashPlainTextToken($plainTextToken))
            ->first();

        return $legacyToken instanceof self ? $legacyToken : null;
    }

    public static function generatePlainTextToken(): string
    {
        return config('capell-agent-bridge.token_prefix', 'cagent-bridge_') . Str::random(48);
    }

    public function hasLegacyHashForPlainTextToken(string $plainTextToken): bool
    {
        return hash_equals($this->token_hash, self::legacyHashPlainTextToken($plainTextToken));
    }

    public function canUseScope(string $scope): bool
    {
        return in_array($scope, $this->scopes, true) || in_array('*', $this->scopes, true);
    }

    public function isExpired(): bool
    {
        return $this->expires_at instanceof CarbonImmutable && $this->expires_at->isPast();
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at instanceof CarbonImmutable;
    }

    public function isUsable(): bool
    {
        return $this->is_enabled && ! $this->isRevoked() && ! $this->isExpired();
    }

    public function lifecycleStatus(): AgentBridgeTokenLifecycleStatus
    {
        if ($this->isRevoked() || ! $this->is_enabled) {
            return AgentBridgeTokenLifecycleStatus::Revoked;
        }

        if ($this->isExpired()) {
            return AgentBridgeTokenLifecycleStatus::Expired;
        }

        return AgentBridgeTokenLifecycleStatus::Active;
    }

    public function getLifecycleStatusAttribute(): AgentBridgeTokenLifecycleStatus
    {
        return $this->lifecycleStatus();
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeWithLifecycleStatus(Builder $query, AgentBridgeTokenLifecycleStatus $status): Builder
    {
        return match ($status) {
            AgentBridgeTokenLifecycleStatus::Active => $query
                ->where('is_enabled', true)
                ->whereNull('revoked_at')
                ->where(function (Builder $query): void {
                    $query
                        ->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                }),
            AgentBridgeTokenLifecycleStatus::Expired => $query
                ->where('is_enabled', true)
                ->whereNull('revoked_at')
                ->whereNotNull('expires_at')
                ->where('expires_at', '<=', now()),
            AgentBridgeTokenLifecycleStatus::Revoked => $query
                ->where(function (Builder $query): void {
                    $query
                        ->where('is_enabled', false)
                        ->orWhereNotNull('revoked_at');
                }),
        };
    }

    /** @return MorphTo<Model, $this> */
    public function user(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return array<string, string> */
    #[Override]
    protected function casts(): array
    {
        return [
            'scopes' => 'array',
            'is_enabled' => 'boolean',
            'revoked_at' => 'immutable_datetime',
            'rotated_at' => 'immutable_datetime',
            'last_used_at' => 'immutable_datetime',
            'expires_at' => 'immutable_datetime',
        ];
    }
}
