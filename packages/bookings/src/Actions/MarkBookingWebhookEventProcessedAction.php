<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Models\BookingWebhookEvent;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static BookingWebhookEvent run(BookingWebhookEvent $webhookEvent, string $status = 'processed')
 */
class MarkBookingWebhookEventProcessedAction
{
    use AsAction;

    public function handle(BookingWebhookEvent $webhookEvent, string $status = 'processed'): BookingWebhookEvent
    {
        $webhookEvent->forceFill([
            'status' => $status,
            'processed_at' => CarbonImmutable::now(),
        ])->save();

        return $webhookEvent->refresh();
    }
}
