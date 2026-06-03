<?php

declare(strict_types=1);

namespace Capell\AccessGate\Models;

use Capell\AccessGate\Database\Factories\AreaFactory;
use Capell\AccessGate\Enums\AccessAreaStatus;
use Capell\AccessGate\Enums\ApprovalStrategy;
use Capell\AccessGate\Enums\IdentityMode;
use Capell\AccessGate\Enums\RegistrationPolicy;
use Capell\AccessGate\Enums\TokenPolicy;
use Capell\AccessGate\Models\Event as AccessGateEvent;
use Capell\Core\Models\Site;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property int $id
 * @property string $key
 * @property int|null $site_id
 * @property string $name
 * @property AccessAreaStatus $status
 * @property CarbonInterface|null $opens_at
 * @property CarbonInterface|null $closes_at
 * @property IdentityMode $identity_mode
 * @property ApprovalStrategy $approval_strategy
 * @property int|null $approval_limit
 * @property int|null $grant_duration_days
 * @property RegistrationPolicy $registration_policy
 * @property TokenPolicy $token_policy
 * @property array<int, string|array{url?: string, host?: string, path?: string}>|null $public_allowlist
 * @property array<int, string>|null $claim_url_hosts
 * @property string|null $gate_view
 * @property array<array-key, mixed>|null $metadata
 * @property string|null $discount_label
 * @property string|null $discount_code
 * @property CarbonInterface|null $discount_expires_at
 * @property array<array-key, mixed>|null $discount_metadata
 */
class Area extends AccessGateModel
{
    /** @use HasFactory<AreaFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'key',
        'site_id',
        'name',
        'status',
        'opens_at',
        'closes_at',
        'identity_mode',
        'approval_strategy',
        'approval_limit',
        'grant_duration_days',
        'registration_policy',
        'token_policy',
        'public_allowlist',
        'claim_url_hosts',
        'gate_view',
        'metadata',
        'discount_label',
        'discount_code',
        'discount_expires_at',
        'discount_metadata',
    ];

    protected $table = 'access_gate_areas';

    protected static string $factory = AreaFactory::class;

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * @return HasMany<Registration, $this>
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'access_area_id');
    }

    /**
     * @return HasMany<Grant, $this>
     */
    public function grants(): HasMany
    {
        return $this->hasMany(Grant::class, 'access_area_id');
    }

    /**
     * @return HasMany<ClaimToken, $this>
     */
    public function claimTokens(): HasMany
    {
        return $this->hasMany(ClaimToken::class, 'access_area_id');
    }

    /**
     * @return HasMany<BrowserToken, $this>
     */
    public function browserTokens(): HasMany
    {
        return $this->hasMany(BrowserToken::class, 'access_area_id');
    }

    /**
     * @return HasMany<AccessGateEvent, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(AccessGateEvent::class, 'access_area_id');
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'status' => AccessAreaStatus::class,
            'opens_at' => 'datetime',
            'closes_at' => 'datetime',
            'identity_mode' => IdentityMode::class,
            'approval_strategy' => ApprovalStrategy::class,
            'registration_policy' => RegistrationPolicy::class,
            'token_policy' => TokenPolicy::class,
            'public_allowlist' => 'array',
            'claim_url_hosts' => 'array',
            'metadata' => 'array',
            'discount_expires_at' => 'datetime',
            'discount_metadata' => 'array',
        ];
    }
}
