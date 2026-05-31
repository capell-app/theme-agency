<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Models;

use Capell\PrivacyCenter\Enums\PolicyType;
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
 * @property PolicyType $policy_type
 * @property string $policy_key
 * @property string $policy_version
 * @property string|null $context
 * @property string|null $ip_hash
 * @property string|null $user_agent_hash
 * @property CarbonImmutable $accepted_at
 * @property array<array-key, mixed>|null $metadata
 */
class PolicyAcceptance extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    /** @var array<string> */
    protected $guarded = [];

    #[Override]
    public function getTable(): string
    {
        $tableName = config('capell-privacy-center.tables.policy_acceptances');

        return is_string($tableName) ? $tableName : 'privacy_policy_acceptances';
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function subject(): MorphTo
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

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'policy_type' => PolicyType::class,
            'accepted_at' => 'immutable_datetime',
            'metadata' => 'array',
        ];
    }
}
