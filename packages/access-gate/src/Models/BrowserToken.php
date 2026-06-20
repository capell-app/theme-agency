<?php

declare(strict_types=1);

namespace Capell\AccessGate\Models;

use Capell\AccessGate\Database\Factories\BrowserTokenFactory;
use Capell\AccessGate\Enums\BrowserTokenStatus;
use Capell\AccessGate\Models\Event as AccessGateEvent;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property int $id
 * @property int $access_area_id
 * @property int $grant_id
 * @property string $token_hash
 * @property BrowserTokenStatus $status
 * @property string|null $ip_hash
 * @property string|null $user_agent
 * @property CarbonInterface|null $expires_at
 * @property CarbonInterface|null $last_used_at
 * @property CarbonInterface|null $revoked_at
 * @property array<array-key, mixed>|null $metadata
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 * @property-read Grant|null $grant
 */
class BrowserToken extends AccessGateModel
{
    /** @use HasFactory<BrowserTokenFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'access_area_id',
        'grant_id',
        'token_hash',
        'status',
        'ip_hash',
        'user_agent',
        'expires_at',
        'last_used_at',
        'revoked_at',
        'metadata',
    ];

    protected $table = 'access_gate_browser_tokens';

    protected static string $factory = BrowserTokenFactory::class;

    /**
     * @return BelongsTo<Area, $this>
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'access_area_id');
    }

    /**
     * @return BelongsTo<Grant, $this>
     */
    public function grant(): BelongsTo
    {
        return $this->belongsTo(Grant::class, 'grant_id');
    }

    /**
     * @return HasMany<AccessGateEvent, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(AccessGateEvent::class, 'browser_token_id');
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'status' => BrowserTokenStatus::class,
            'expires_at' => 'datetime',
            'last_used_at' => 'datetime',
            'revoked_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}
