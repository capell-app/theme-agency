<?php

declare(strict_types=1);

namespace Capell\AccessGate\Models;

use Capell\AccessGate\Database\Factories\EventFactory;
use Capell\AccessGate\Enums\EventType;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Override;

/**
 * @property int $id
 * @property int|null $access_area_id
 * @property int|null $registration_id
 * @property int|null $grant_id
 * @property int|null $claim_token_id
 * @property int|null $browser_token_id
 * @property int|null $user_id
 * @property EventType $type
 * @property string|null $subject_type
 * @property int|string|null $subject_id
 * @property array<array-key, mixed>|null $payload
 * @property array<array-key, mixed>|null $metadata
 * @property CarbonInterface $occurred_at
 */
class Event extends AccessGateModel
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'access_area_id',
        'registration_id',
        'grant_id',
        'claim_token_id',
        'browser_token_id',
        'user_id',
        'type',
        'subject_type',
        'subject_id',
        'payload',
        'metadata',
        'occurred_at',
    ];

    protected $table = 'access_gate_events';

    protected static string $factory = EventFactory::class;

    /**
     * @return BelongsTo<Area, $this>
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'access_area_id');
    }

    /**
     * @return BelongsTo<Registration, $this>
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'registration_id');
    }

    /**
     * @return BelongsTo<Grant, $this>
     */
    public function grant(): BelongsTo
    {
        return $this->belongsTo(Grant::class, 'grant_id');
    }

    /**
     * @return BelongsTo<ClaimToken, $this>
     */
    public function claimToken(): BelongsTo
    {
        return $this->belongsTo(ClaimToken::class, 'claim_token_id');
    }

    /**
     * @return BelongsTo<BrowserToken, $this>
     */
    public function browserToken(): BelongsTo
    {
        return $this->belongsTo(BrowserToken::class, 'browser_token_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'type' => EventType::class,
            'payload' => 'array',
            'metadata' => 'array',
            'occurred_at' => 'datetime',
        ];
    }
}
