<?php

declare(strict_types=1);

namespace Capell\Events\Actions;

use Capell\Events\Enums\EventNotificationTypeEnum;
use Capell\Events\Models\EventNotificationLog;
use Capell\Events\Models\EventRegistration;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static void run(EventRegistration $registration)
 */
class ScheduleEventNotificationsAction
{
    use AsAction;

    public function handle(EventRegistration $registration): void
    {
        SendEventNotificationAction::run($registration, EventNotificationTypeEnum::Confirmation);

        $occurrence = $registration->occurrence;

        if ($occurrence->starts_at === null) {
            return;
        }

        EventNotificationLog::query()->createOrFirst([
            'event_occurrence_id' => $occurrence->getKey(),
            'event_registration_id' => $registration->getKey(),
            'type' => EventNotificationTypeEnum::Reminder,
            'recipient_email' => $registration->email,
        ], [
            'status' => 'queued',
            'scheduled_for' => $occurrence->starts_at->subDay(),
        ]);
    }
}
