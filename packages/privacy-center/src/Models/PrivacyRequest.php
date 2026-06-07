<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Models;

use Capell\PrivacyCenter\Enums\PrivacyRequestStatus;
use Capell\PrivacyCenter\Enums\PrivacyRequestType;
use Capell\PrivacyCenter\Support\PrivacyCenterOverviewStatsCache;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Override;

/**
 * @property int $id
 * @property int|null $site_id
 * @property string $reference
 * @property PrivacyRequestType $type
 * @property PrivacyRequestStatus $status
 * @property string|null $email_hash
 * @property CarbonImmutable $submitted_at
 * @property CarbonImmutable|null $due_at
 * @property CarbonImmutable|null $verified_at
 * @property CarbonImmutable|null $fulfilled_at
 * @property CarbonImmutable|null $rejected_at
 * @property string|null $rejection_reason
 * @property array<array-key, mixed>|null $workflow_payload
 * @property array<array-key, mixed>|null $metadata
 */
class PrivacyRequest extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    /** @var array<string> */
    protected $guarded = [];

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-privacy-center.tables.privacy_requests');

        return is_string($tableName) ? $tableName : 'privacy_requests';
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function requester(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    #[Override]
    protected static function booted(): void
    {
        static::saved(static fn (PrivacyRequest $privacyRequest): null => self::flushOverviewStats());
        static::deleted(static fn (PrivacyRequest $privacyRequest): null => self::flushOverviewStats());
    }

    private static function flushOverviewStats(): null
    {
        PrivacyCenterOverviewStatsCache::flush();

        return null;
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'type' => PrivacyRequestType::class,
            'status' => PrivacyRequestStatus::class,
            'submitted_at' => 'immutable_datetime',
            'due_at' => 'immutable_datetime',
            'verified_at' => 'immutable_datetime',
            'fulfilled_at' => 'immutable_datetime',
            'rejected_at' => 'immutable_datetime',
            'workflow_payload' => 'array',
            'metadata' => 'array',
        ];
    }
}
