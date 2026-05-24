<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Models;

use Capell\PublishingStudio\Enums\SchedulerEventStateEnum;
use Capell\PublishingStudio\Enums\SchedulerEventTypeEnum;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Override;
use Throwable;

/**
 * @property int $id
 * @property string $uuid
 * @property SchedulerEventTypeEnum $event_type
 * @property SchedulerEventStateEnum $state
 * @property string $source_type
 * @property int $source_id
 * @property int|null $workspace_id
 * @property int|null $site_id
 * @property int|null $owner_id
 * @property string|null $owner_type
 * @property CarbonImmutable $scheduled_for
 * @property string $display_timezone
 * @property string $idempotency_key
 * @property int $schedule_version
 * @property string|null $actor_type
 * @property int|null $actor_id
 * @property CarbonImmutable|null $claimed_at
 * @property CarbonImmutable|null $last_attempted_at
 * @property CarbonImmutable|null $last_succeeded_at
 * @property CarbonImmutable|null $last_failed_at
 * @property int $failure_count
 * @property string|null $last_failure_class
 * @property string|null $last_failure_message
 * @property string|null $skipped_reason
 * @property array<array-key, mixed>|null $metadata
 * @property-read Workspace|null $workspace
 */
class SchedulerEvent extends Model
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'publishing_scheduler_events';

    protected $fillable = [
        'uuid',
        'event_type',
        'state',
        'source_type',
        'source_id',
        'workspace_id',
        'site_id',
        'owner_type',
        'owner_id',
        'scheduled_for',
        'display_timezone',
        'idempotency_key',
        'schedule_version',
        'actor_type',
        'actor_id',
        'claimed_at',
        'last_attempted_at',
        'last_succeeded_at',
        'last_failed_at',
        'failure_count',
        'last_failure_class',
        'last_failure_message',
        'skipped_reason',
        'metadata',
    ];

    protected $attributes = [
        'state' => 'scheduled',
        'display_timezone' => 'UTC',
        'schedule_version' => 1,
        'failure_count' => 0,
    ];

    /**
     * @return BelongsTo<Workspace, $this>
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /** @return HasMany<SchedulerDelivery, $this> */
    public function deliveries(): HasMany
    {
        return $this->hasMany(SchedulerDelivery::class, 'scheduler_event_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function source(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'source_type', 'source_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function owner(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'owner_type', 'owner_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function actor(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'actor_type', 'actor_id');
    }

    public function markFailed(Throwable $failure): void
    {
        $this->state = SchedulerEventStateEnum::Failed;
        $this->last_failed_at = CarbonImmutable::now();
        $this->failure_count += 1;
        $this->last_failure_class = $failure::class;
        $this->last_failure_message = Str::limit($failure->getMessage(), 1000, '');
        $this->claimed_at = null;
        $this->save();
    }

    public function markSkipped(SchedulerEventStateEnum $state, string $reason): void
    {
        $this->state = $state;
        $this->skipped_reason = $reason;
        $this->last_attempted_at = CarbonImmutable::now();
        $this->claimed_at = null;
        $this->save();
    }

    public function markExecuted(): void
    {
        $this->state = SchedulerEventStateEnum::Executed;
        $this->last_succeeded_at = CarbonImmutable::now();
        $this->claimed_at = null;
        $this->save();
    }

    #[Override]
    protected static function booted(): void
    {
        static::creating(function (self $event): void {
            if ($event->uuid === null || $event->uuid === '') {
                $event->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    protected function scopeDue(Builder $query): Builder
    {
        return $query
            ->where('state', SchedulerEventStateEnum::Scheduled->value)
            ->where('scheduled_for', '<=', now());
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'event_type' => SchedulerEventTypeEnum::class,
            'state' => SchedulerEventStateEnum::class,
            'scheduled_for' => 'immutable_datetime',
            'claimed_at' => 'immutable_datetime',
            'last_attempted_at' => 'immutable_datetime',
            'last_succeeded_at' => 'immutable_datetime',
            'last_failed_at' => 'immutable_datetime',
            'failure_count' => 'integer',
            'schedule_version' => 'integer',
            'metadata' => 'array',
        ];
    }
}
