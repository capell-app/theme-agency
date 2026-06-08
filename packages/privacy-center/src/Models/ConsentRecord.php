<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Models;

use Capell\PrivacyCenter\Enums\ConsentDecision;
use Capell\PrivacyCenter\Enums\CookieCategory;
use Capell\PrivacyCenter\Support\PrivacyCenterOverviewStatsCache;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Override;

/**
 * @property int $id
 * @property int|null $site_id
 * @property int|null $policy_id
 * @property string|null $subject_type
 * @property int|null $subject_id
 * @property string|null $source_type
 * @property int|null $source_id
 * @property string|null $policy_version
 * @property CookieCategory $category
 * @property ConsentDecision $decision
 * @property string|null $jurisdiction
 * @property string|null $ip_hash
 * @property string|null $user_agent_hash
 * @property array<array-key, mixed>|null $evidence
 * @property CarbonImmutable $decided_at
 * @property CarbonImmutable|null $expires_at
 * @property CarbonImmutable|null $revoked_at
 * @property array<array-key, mixed>|null $metadata
 */
class ConsentRecord extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    /** @var array<string> */
    protected $guarded = [];

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-privacy-center.tables.consent_records');

        return is_string($tableName) ? $tableName : 'privacy_consent_records';
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<ConsentPolicy, $this>
     */
    public function policy(): BelongsTo
    {
        return $this->belongsTo(ConsentPolicy::class, 'policy_id');
    }

    #[Override]
    protected static function booted(): void
    {
        static::saved(static fn (ConsentRecord $consentRecord): null => self::flushOverviewStats());
        static::deleted(static fn (ConsentRecord $consentRecord): null => self::flushOverviewStats());
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'category' => CookieCategory::class,
            'decision' => ConsentDecision::class,
            'evidence' => 'array',
            'decided_at' => 'immutable_datetime',
            'expires_at' => 'immutable_datetime',
            'revoked_at' => 'immutable_datetime',
            'metadata' => 'array',
        ];
    }

    private static function flushOverviewStats(): null
    {
        PrivacyCenterOverviewStatsCache::flush();

        return null;
    }
}
