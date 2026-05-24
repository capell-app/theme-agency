<?php

declare(strict_types=1);

namespace Capell\AccessGate\Models;

use Capell\AccessGate\Database\Factories\RegistrationFactory;
use Capell\AccessGate\Enums\RegistrationStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property int $id
 * @property int $access_area_id
 * @property string $email
 * @property string $email_normalized
 * @property string|null $single_registration_key
 * @property int|null $user_id
 * @property RegistrationStatus $status
 * @property string|null $requested_url
 * @property string|null $requested_host
 * @property int $position
 * @property array<string, array{value: mixed, metadata: array<string, mixed>}>|null $field_values
 * @property array<string, mixed>|null $metadata
 * @property CarbonInterface|null $requested_at
 * @property CarbonInterface|null $approved_at
 * @property CarbonInterface|null $rejected_at
 * @property CarbonInterface|null $claimed_at
 * @property CarbonInterface|null $expired_at
 * @property-read Area|null $area
 */
class Registration extends AccessGateModel
{
    /** @use HasFactory<RegistrationFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'access_area_id',
        'email',
        'email_normalized',
        'single_registration_key',
        'user_id',
        'status',
        'requested_url',
        'requested_host',
        'position',
        'field_values',
        'metadata',
        'requested_at',
        'approved_at',
        'rejected_at',
        'claimed_at',
        'expired_at',
    ];

    protected $table = 'access_gate_registrations';

    protected static string $factory = RegistrationFactory::class;

    /** @return BelongsTo<Area, $this> */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'access_area_id');
    }

    /** @return HasMany<Grant, $this> */
    public function grants(): HasMany
    {
        return $this->hasMany(Grant::class, 'registration_id');
    }

    /** @return HasMany<ClaimToken, $this> */
    public function claimTokens(): HasMany
    {
        return $this->hasMany(ClaimToken::class, 'registration_id');
    }

    /** @return HasMany<Event, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'registration_id');
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'status' => RegistrationStatus::class,
            'field_values' => 'array',
            'metadata' => 'array',
            'requested_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'claimed_at' => 'datetime',
            'expired_at' => 'datetime',
        ];
    }
}
