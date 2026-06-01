<?php

declare(strict_types=1);

namespace Capell\Events\Actions;

use Capell\Events\Enums\EventNotificationTypeEnum;
use Capell\Events\Models\EventNotificationLog;
use Capell\Events\Models\EventRegistration;
use Capell\Events\Notifications\EventRegistrationNotification;
use Illuminate\Support\Facades\Notification;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

class ProcessDueEventNotificationLogsAction
{
    use AsAction;

    public function handle(int $limit = 100): int
    {
        $processed = 0;
        $boundedLimit = max(1, $limit);

        EventNotificationLog::query()
            ->with(['registration.occurrence.event'])
            ->where('type', EventNotificationTypeEnum::Reminder)
            ->where('status', 'queued')
            ->where('scheduled_for', '<=', now())
            ->oldest('scheduled_for')
            ->limit($boundedLimit)
            ->get()
            ->each(function (EventNotificationLog $log) use (&$processed): void {
                if (! $this->claimLog($log)) {
                    return;
                }

                $this->sendClaimedLog($log->refresh());
                $processed++;
            });

        return $processed;
    }

    private function claimLog(EventNotificationLog $log): bool
    {
        return EventNotificationLog::query()
            ->whereKey($log->getKey())
            ->where('status', 'queued')
            ->update(['status' => 'sending']) === 1;
    }

    private function sendClaimedLog(EventNotificationLog $log): void
    {
        $log->loadMissing(['registration.occurrence.event']);
        $registration = $log->registration;

        if (! $registration instanceof EventRegistration) {
            $log->forceFill([
                'status' => 'failed',
                'error' => 'Event notification log has no registration.',
            ])->save();

            return;
        }

        $recipientEmail = $log->recipient_email ?: $registration->email;

        try {
            Notification::route('mail', $recipientEmail)
                ->notify(new EventRegistrationNotification($registration, EventNotificationTypeEnum::Reminder));

            $log->forceFill([
                'status' => 'sent',
                'sent_at' => now(),
                'error' => null,
            ])->save();
        } catch (Throwable $throwable) {
            $log->forceFill([
                'status' => 'failed',
                'error' => $throwable->getMessage(),
            ])->save();
        }
    }
}
