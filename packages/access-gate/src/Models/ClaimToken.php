<?php

declare(strict_types=1);

namespace Capell\AccessGate\Models;

use Capell\AccessGate\Database\Factories\ClaimTokenFactory;
use Capell\AccessGate\Enums\ClaimTokenStatus;
use Capell\AccessGate\Models\Event as AccessGateEvent;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property int $id
 * @property int $access_area_id
 * @property int|null $registration_id
 * @property int|null $grant_id
 * @property string $token_hash
 * @property ClaimTokenStatus $status
 * @property CarbonInterface|null $expires_at
 * @property CarbonInterface|null $consumed_at
 * @property array<string, mixed>|null $metadata
 */
class ClaimToken extends AccessGateModel
{
    /** @use HasFactory<ClaimTokenFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'access_area_id',
        'registration_id',
        'grant_id',
        'token_hash',
        'status',
        'expires_at',
        'consumed_at',
        'metadata',
    ];

    protected $table = 'access_gate_claim_tokens';

    protected static string $factory = ClaimTokenFactory::class;

    /** @return BelongsTo<Area, $this> */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'access_area_id');
    }

    /** @return BelongsTo<Registration, $this> */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'registration_id');
    }

    /** @return BelongsTo<Grant, $this> */
    public function grant(): BelongsTo
    {
        return $this->belongsTo(Grant::class, 'grant_id');
    }

    /** @return HasMany<AccessGateEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(AccessGateEvent::class, 'claim_token_id');
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'status' => ClaimTokenStatus::class,
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}
