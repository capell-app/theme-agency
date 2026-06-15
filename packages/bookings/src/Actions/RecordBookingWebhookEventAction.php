<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Models\BookingWebhookEvent;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static BookingWebhookEvent run(string $provider, string $providerEventId, string $type, array<string, mixed> $payload = [])
 */
class RecordBookingWebhookEventAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(string $provider, string $providerEventId, string $type, array $payload = []): BookingWebhookEvent
    {
        /** @var BookingWebhookEvent $webhookEvent */
        $webhookEvent = BookingWebhookEvent::query()->firstOrCreate(
            [
                'provider' => $provider,
                'provider_event_id' => $providerEventId,
            ],
            [
                'type' => $type,
                'status' => 'received',
                'payload' => $payload,
                'received_at' => CarbonImmutable::now(),
            ],
        );

        return $webhookEvent;
    }
}
