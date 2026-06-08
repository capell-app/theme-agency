<?php

declare(strict_types=1);

namespace Capell\Events\Actions;

use Capell\Events\Enums\EventNotificationTypeEnum;
use Capell\Events\Models\EventNotificationLog;
use Capell\Events\Models\EventRegistration;
use Illuminate\Support\Arr;
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

        if ($occurrence->starts_at === null || $this->remindersEnabled($registration) === false) {
            return;
        }

        foreach ($this->reminderOffsetsMinutes($registration) as $offsetMinutes) {
            EventNotificationLog::query()->createOrFirst([
                'event_occurrence_id' => $occurrence->getKey(),
                'event_registration_id' => $registration->getKey(),
                'type' => EventNotificationTypeEnum::Reminder,
                'notification_key' => 'reminder:' . $offsetMinutes,
                'recipient_email' => $registration->email,
            ], [
                'status' => 'queued',
                'scheduled_for' => $occurrence->starts_at->subMinutes($offsetMinutes),
                'meta' => [
                    'offset_minutes' => $offsetMinutes,
                ],
            ]);
        }
    }

    private function remindersEnabled(EventRegistration $registration): bool
    {
        $settings = $registration->occurrence->event->notification_settings ?? [];

        return ($settings['reminders_enabled'] ?? true) !== false;
    }

    /**
     * @return list<int>
     */
    private function reminderOffsetsMinutes(EventRegistration $registration): array
    {
        $settings = $registration->occurrence->event->notification_settings ?? [];
        $offsets = $settings['reminder_offsets_minutes'] ?? config('capell-events.notifications.reminder_offsets_minutes', [1440]);

        $normalized = [];

        foreach (Arr::wrap($offsets) as $offset) {
            if (! is_numeric($offset)) {
                continue;
            }

            $offsetMinutes = (int) $offset;

            if ($offsetMinutes > 0) {
                $normalized[] = $offsetMinutes;
            }
        }

        $normalized = array_values(array_unique($normalized));
        rsort($normalized);

        return $normalized === [] ? [1440] : $normalized;
    }
}
