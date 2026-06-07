<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Http\Controllers\Webhooks;

use Capell\ShopifyCommerce\Actions\Webhooks\IngestShopifyWebhookAction;
use Capell\ShopifyCommerce\Actions\Webhooks\ValidateShopifyWebhookHmacAction;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ShopifyWebhookController
{
    public function __invoke(Request $request): JsonResponse
    {
        $rawPayload = $request->getContent();

        abort_unless(ValidateShopifyWebhookHmacAction::run(
            $rawPayload,
            $request->header('X-Shopify-Hmac-Sha256'),
            config('capell-shopify-commerce.client_secret'),
        ), 401);

        $shopDomain = $request->header('X-Shopify-Shop-Domain');
        $topic = $request->header('X-Shopify-Topic');

        abort_unless(is_string($shopDomain) && $shopDomain !== '' && is_string($topic) && $topic !== '', 422);

        $connection = ShopifyConnection::query()
            ->where('shop_domain', $shopDomain)
            ->latest('id')
            ->first();

        abort_unless($connection instanceof ShopifyConnection, 404);

        $payload = json_decode($rawPayload, true, 512, JSON_THROW_ON_ERROR);
        abort_unless(is_array($payload), 422);

        IngestShopifyWebhookAction::run($connection, $topic, $payload);

        return response()->json(['ok' => true]);
    }
}
