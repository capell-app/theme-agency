<?php

declare(strict_types=1);

namespace Capell\Events\Database\Factories;

use Capell\Events\Enums\EventNotificationTypeEnum;
use Capell\Events\Models\EventNotificationLog;
use Capell\Events\Models\EventOccurrence;
use Capell\Events\Models\EventRegistration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventNotificationLog>
 */
class EventNotificationLogFactory extends Factory
{
    protected $model = EventNotificationLog::class;

    public function definition(): array
    {
        return [
            'event_occurrence_id' => EventOccurrence::factory(),
            'event_registration_id' => EventRegistration::factory(),
            'type' => EventNotificationTypeEnum::Confirmation->value,
            'notification_key' => EventNotificationTypeEnum::Confirmation->value,
            'recipient_email' => 'attendee@example.com',
            'status' => 'queued',
            'scheduled_for' => now(),
        ];
    }
}
