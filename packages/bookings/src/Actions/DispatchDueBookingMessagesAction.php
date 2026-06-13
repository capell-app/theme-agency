<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Enums\AppointmentRequestStatusEnum;
use Capell\Bookings\Enums\BookingMessageChannelEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static int run(?CarbonImmutable $now = null, ?int $leadMinutes = null, int $limit = 100)
 */
class DispatchDueBookingMessagesAction
{
    use AsAction;

    public function handle(?CarbonImmutable $now = null, ?int $leadMinutes = null, int $limit = 100): int
    {
        $now ??= CarbonImmutable::now();
        $leadMinutesConfig = config('capell-bookings.reminder_lead_minutes', 1440);
        $leadMinutes ??= is_numeric($leadMinutesConfig) ? (int) $leadMinutesConfig : 1440;
        $dueUntil = $now->addMinutes($leadMinutes);
        $queued = 0;

        AppointmentRequest::query()
            ->where('status', AppointmentRequestStatusEnum::Confirmed->value)
            ->whereBetween('requested_starts_at', [$now, $dueUntil])
            ->whereDoesntHave('messageLogs', function (Builder $query): void {
                $query
                    ->where('channel', BookingMessageChannelEnum::Email->value)
                    ->where('type', 'reminder');
            })
            ->orderBy('requested_starts_at')
            ->limit($limit)
            ->get()
            ->each(function (AppointmentRequest $appointmentRequest) use (&$queued): void {
                DispatchBookingMessageAction::run(
                    appointmentRequest: $appointmentRequest,
                    channel: BookingMessageChannelEnum::Email,
                    type: 'reminder',
                    body: __('capell-bookings::notification.reminder.body'),
                    subject: __('capell-bookings::notification.reminder.subject'),
                );

                $queued++;
            });

        return $queued;
    }
}
