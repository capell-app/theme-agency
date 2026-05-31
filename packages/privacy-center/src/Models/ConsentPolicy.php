<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Models;

use Capell\PrivacyCenter\Enums\PolicyType;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property int $id
 * @property int|null $site_id
 * @property PolicyType $type
 * @property string $key
 * @property string $version
 * @property string $title
 * @property string|null $content_hash
 * @property CarbonImmutable|null $effective_at
 * @property CarbonImmutable|null $published_at
 * @property CarbonImmutable|null $retired_at
 * @property array<array-key, mixed>|null $metadata
 */
class ConsentPolicy extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    /** @var array<string> */
    protected $guarded = [];

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-privacy-center.tables.consent_policies');

        return is_string($tableName) ? $tableName : 'privacy_consent_policies';
    }

    /**
     * @return HasMany<ConsentRecord, $this>
     */
    public function consentRecords(): HasMany
    {
        return $this->hasMany(ConsentRecord::class, 'policy_id');
    }

    /**
     * @return HasMany<PolicyAcceptance, $this>
     */
    public function acceptances(): HasMany
    {
        return $this->hasMany(PolicyAcceptance::class, 'policy_id');
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'type' => PolicyType::class,
            'effective_at' => 'immutable_datetime',
            'published_at' => 'immutable_datetime',
            'retired_at' => 'immutable_datetime',
            'metadata' => 'array',
        ];
    }
}
