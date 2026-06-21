<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Http\Controllers\Webhooks;

use Capell\ShopifyCommerce\Actions\Webhooks\IngestShopifyWebhookAction;
use Capell\ShopifyCommerce\Actions\Webhooks\RecordShopifyWebhookEventAction;
use Capell\ShopifyCommerce\Actions\Webhooks\ValidateShopifyWebhookHmacAction;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

final class ShopifyWebhookController
{
    public function __invoke(Request $request): JsonResponse
    {
        $rawPayload = $request->getContent();

        // Shopify signs every webhook with the single app API secret, so a valid
        // HMAC proves only that *some* shop on this app sent the request — it does
        // NOT prove the request came from the shop named in X-Shopify-Shop-Domain.
        // Per-tenant HMAC secrets are impossible under Shopify's model, so the
        // hardening here is: resolve the connection by exact shop domain, require
        // it to be active, reject stale deliveries, and dedupe by webhook id so a
        // replay of a captured-but-valid request is inert (see below).
        $signatureIsValid = ValidateShopifyWebhookHmacAction::run(
            $rawPayload,
            $request->header('X-Shopify-Hmac-Sha256'),
            config('capell-shopify-commerce.client_secret'),
        );

        abort_unless($signatureIsValid, 401);

        $shopDomain = $request->header('X-Shopify-Shop-Domain');
        $topic = $request->header('X-Shopify-Topic');
        $webhookId = $request->header('X-Shopify-Webhook-Id');

        // X-Shopify-Webhook-Id is required, not optional: it is the idempotency
        // key and Shopify always sends it. The HMAC is computed over the raw body
        // only, so a captured-but-valid destructive webhook stays replayable
        // forever; treating the id as optional would let an attacker skip the
        // dedupe check simply by stripping the header and replaying at will.
        abort_unless(
            is_string($shopDomain) && $shopDomain !== ''
                && is_string($topic) && $topic !== ''
                && is_string($webhookId) && $webhookId !== '',
            422,
        );

        $connection = ShopifyConnection::query()
            ->where('shop_domain', $shopDomain)
            ->latest('id')
            ->first();

        abort_unless($connection instanceof ShopifyConnection, 404);

        // Destructive handlers (products/delete, app/uninstalled, ...) must never
        // run against a revoked or half-connected store.
        abort_unless($connection->isActive(), 403);

        abort_unless($this->deliveryIsFresh($request), 422);

        $payload = json_decode($rawPayload, true, 512, JSON_THROW_ON_ERROR);
        abort_unless(is_array($payload), 422);

        // Idempotency: record the (connection, webhook id) pair and no-op when it
        // has already been seen. This is the mitigation for both replayed requests
        // and Shopify's own at-least-once retry delivery, and it must happen BEFORE
        // any destructive handler is dispatched. The webhook id is guaranteed
        // present above, so recording is unconditional.
        $isFirstDelivery = RecordShopifyWebhookEventAction::run(
            $connection,
            $webhookId,
            $topic,
            $this->triggeredAt($request),
        );

        if (! $isFirstDelivery) {
            return response()->json(['ok' => true, 'duplicate' => true]);
        }

        IngestShopifyWebhookAction::run($connection, $topic, $this->stringKeyedPayload($payload));

        return response()->json(['ok' => true]);
    }

    private function deliveryIsFresh(Request $request): bool
    {
        $triggeredAt = $this->triggeredAt($request);

        // When Shopify omits the header we cannot assess freshness, so we fall back
        // to the idempotency guard alone rather than rejecting legitimate traffic.
        if (! $triggeredAt instanceof CarbonImmutable) {
            return true;
        }

        $toleranceSeconds = $this->integerConfig('capell-shopify-commerce.webhook_freshness_tolerance_seconds', 300);

        return abs(CarbonImmutable::now()->getTimestamp() - $triggeredAt->getTimestamp()) <= $toleranceSeconds;
    }

    private function triggeredAt(Request $request): ?CarbonImmutable
    {
        $triggeredAtHeader = $request->header('X-Shopify-Triggered-At');

        if (! is_string($triggeredAtHeader) || $triggeredAtHeader === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($triggeredAtHeader);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param  array<array-key, mixed>  $payload
     * @return array<string, mixed>
     */
    private function stringKeyedPayload(array $payload): array
    {
        $result = [];

        foreach ($payload as $key => $value) {
            if (is_string($key)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    private function integerConfig(string $key, int $default): int
    {
        $value = config($key, $default);

        return is_numeric($value) ? (int) $value : $default;
    }
}
