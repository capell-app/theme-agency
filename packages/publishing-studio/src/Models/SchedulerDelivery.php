<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Models;

use Capell\PublishingStudio\Enums\SchedulerDeliveryStateEnum;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Override;

/**
 * @property int $id
 * @property int $scheduler_event_id
 * @property SchedulerDeliveryStateEnum $state
 * @property string $recipient_type
 * @property int $recipient_id
 * @property string $dedupe_key
 * @property CarbonImmutable|null $sent_at
 * @property CarbonImmutable|null $failed_at
 * @property CarbonImmutable|null $snoozed_until
 * @property string|null $failure_message
 */
class SchedulerDelivery extends Model
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    protected $table = 'publishing_scheduler_deliveries';

    protected $fillable = [
        'scheduler_event_id',
        'state',
        'recipient_type',
        'recipient_id',
        'dedupe_key',
        'sent_at',
        'failed_at',
        'snoozed_until',
        'failure_message',
    ];

    protected $attributes = [
        'state' => 'pending',
    ];

    /**
     * @return BelongsTo<SchedulerEvent, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(SchedulerEvent::class, 'scheduler_event_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function recipient(): MorphTo
    {
        return $this->morphTo();
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'state' => SchedulerDeliveryStateEnum::class,
            'sent_at' => 'immutable_datetime',
            'failed_at' => 'immutable_datetime',
            'snoozed_until' => 'immutable_datetime',
        ];
    }
}
