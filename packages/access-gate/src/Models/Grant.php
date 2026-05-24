<?php

declare(strict_types=1);

namespace Capell\AccessGate\Models;

use Capell\AccessGate\Database\Factories\GrantFactory;
use Capell\AccessGate\Enums\GrantStatus;
use Capell\AccessGate\Enums\GrantSubjectType;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property int $id
 * @property int $access_area_id
 * @property int|null $registration_id
 * @property GrantSubjectType $subject_type
 * @property int|string|null $subject_id
 * @property int|null $user_id
 * @property string|null $email
 * @property GrantStatus $status
 * @property CarbonInterface|null $starts_at
 * @property CarbonInterface|null $expires_at
 * @property CarbonInterface|null $revoked_at
 * @property string|null $discount_label
 * @property string|null $discount_code
 * @property CarbonInterface|null $discount_expires_at
 * @property array<string, mixed>|null $discount_metadata
 * @property array<string, mixed>|null $metadata
 * @property-read Area|null $area
 */
class Grant extends AccessGateModel
{
    /** @use HasFactory<GrantFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'access_area_id',
        'registration_id',
        'subject_type',
        'subject_id',
        'user_id',
        'email',
        'status',
        'starts_at',
        'expires_at',
        'revoked_at',
        'discount_label',
        'discount_code',
        'discount_expires_at',
        'discount_metadata',
        'metadata',
    ];

    protected $table = 'access_gate_grants';

    protected static string $factory = GrantFactory::class;

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

    /** @return HasMany<ClaimToken, $this> */
    public function claimTokens(): HasMany
    {
        return $this->hasMany(ClaimToken::class, 'grant_id');
    }

    /** @return HasMany<BrowserToken, $this> */
    public function browserTokens(): HasMany
    {
        return $this->hasMany(BrowserToken::class, 'grant_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'grant_id');
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'subject_type' => GrantSubjectType::class,
            'status' => GrantStatus::class,
            'metadata' => 'array',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
            'discount_expires_at' => 'datetime',
            'discount_metadata' => 'array',
        ];
    }
}
