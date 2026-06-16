<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Models\BookingWebhookEvent;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static BookingWebhookEvent run(BookingWebhookEvent $webhookEvent)
 */
class ReplayBookingWebhookEventAction
{
    use AsAction;

    public function handle(BookingWebhookEvent $webhookEvent): BookingWebhookEvent
    {
        $webhookEvent->forceFill([
            'status' => 'received',
            'processed_at' => null,
        ])->save();

        return $webhookEvent->refresh();
    }
}
