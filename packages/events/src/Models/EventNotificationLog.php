<?php

declare(strict_types=1);

namespace Capell\Events\Models;

use Capell\Events\Database\Factories\EventNotificationLogFactory;
use Capell\Events\Enums\EventNotificationTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

class EventNotificationLog extends Model
{
    /** @use HasFactory<EventNotificationLogFactory> */
    use HasFactory;

    protected $table = 'event_notification_logs';

    protected $guarded = [];

    protected static string $factory = EventNotificationLogFactory::class;

    /**
     * @return BelongsTo<EventOccurrence, $this>
     */
    public function occurrence(): BelongsTo
    {
        return $this->belongsTo(EventOccurrence::class, 'event_occurrence_id');
    }

    /**
     * @return BelongsTo<EventRegistration, $this>
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(EventRegistration::class, 'event_registration_id');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'meta' => 'json',
            'scheduled_for' => 'immutable_datetime',
            'sent_at' => 'immutable_datetime',
            'type' => EventNotificationTypeEnum::class,
        ];
    }
}
