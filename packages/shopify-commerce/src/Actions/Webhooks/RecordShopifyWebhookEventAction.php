<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Actions\Webhooks;

use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Capell\ShopifyCommerce\Models\ShopifyWebhookEvent;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Records the delivery of a Shopify webhook for a connection so that duplicate
 * deliveries (Shopify retries, or replays of a previously captured request) can
 * be detected and short-circuited before any destructive handler runs.
 *
 * Shopify guarantees the X-Shopify-Webhook-Id header is stable across retries of
 * the same logical event, so it is the natural idempotency key. This mirrors the
 * payments package's PaymentWebhookEvent / provider_event_id idempotency pattern.
 *
 * @method static bool run(ShopifyConnection $connection, string $webhookId, string $topic, ?CarbonImmutable $triggeredAt = null)
 */
final class RecordShopifyWebhookEventAction
{
    use AsAction;

    /**
     * @return bool True when the event was recorded for the first time (safe to
     *              process); false when it is a duplicate that must be ignored.
     */
    public function handle(ShopifyConnection $connection, string $webhookId, string $topic, ?CarbonImmutable $triggeredAt = null): bool
    {
        $alreadyRecorded = ShopifyWebhookEvent::query()
            ->where('connection_id', $connection->getKey())
            ->where('webhook_id', $webhookId)
            ->exists();

        if ($alreadyRecorded) {
            return false;
        }

        try {
            ShopifyWebhookEvent::query()->create([
                'connection_id' => $connection->getKey(),
                'webhook_id' => $webhookId,
                'topic' => $topic,
                'triggered_at' => $triggeredAt,
                'received_at' => CarbonImmutable::now(),
            ]);

            return true;
        } catch (QueryException) {
            // A concurrent delivery won the unique (connection_id, webhook_id)
            // race. Treat this delivery as a duplicate so the handler runs once.
            return false;
        }
    }
}
