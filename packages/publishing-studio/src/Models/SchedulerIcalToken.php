<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Models;

use Capell\PublishingStudio\Enums\SchedulerIcalFeedScopeEnum;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use Override;

/**
 * @property int $id
 * @property string $uuid
 * @property string $token_hash
 * @property SchedulerIcalFeedScopeEnum $scope
 * @property int|null $site_id
 * @property string $owner_type
 * @property int $owner_id
 * @property CarbonImmutable|null $revoked_at
 * @property CarbonImmutable|null $last_used_at
 */
class SchedulerIcalToken extends Model
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    protected $table = 'publishing_scheduler_ical_tokens';

    protected $fillable = [
        'uuid',
        'token_hash',
        'scope',
        'site_id',
        'owner_type',
        'owner_id',
        'revoked_at',
        'last_used_at',
    ];

    /**
     * @return MorphTo<Model, $this>
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    #[Override]
    protected static function booted(): void
    {
        static::creating(function (self $token): void {
            if ($token->uuid === null || $token->uuid === '') {
                $token->uuid = (string) Str::uuid();
            }
        });
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'scope' => SchedulerIcalFeedScopeEnum::class,
            'revoked_at' => 'immutable_datetime',
            'last_used_at' => 'immutable_datetime',
        ];
    }
}
