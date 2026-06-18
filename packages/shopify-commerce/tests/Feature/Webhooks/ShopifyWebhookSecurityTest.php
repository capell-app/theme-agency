<?php

declare(strict_types=1);

use Capell\ShopifyCommerce\Enums\ShopifyConnectionStatus;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Capell\ShopifyCommerce\Models\ShopifyProduct;
use Capell\ShopifyCommerce\Models\ShopifyWebhookEvent;
use Carbon\CarbonImmutable;
use Illuminate\Testing\TestResponse;

use function Pest\Laravel\postJson;

beforeEach(function (): void {
    config()->set('capell-shopify-commerce.client_secret', 'client-secret');
});

function securityWebhookConnection(): ShopifyConnection
{
    return ShopifyConnection::query()->create([
        'shop_domain' => 'foo.myshopify.com',
        'status' => ShopifyConnectionStatus::Active,
        'access_token' => 'admin-token',
        'scopes' => ['read_products', 'read_customers'],
    ]);
}

/**
 * @param  array<string, mixed>  $payload
 * @param  array<string, string>  $extraHeaders
 */
function securityWebhookPost(string $topic, array $payload, array $extraHeaders = []): TestResponse
{
    $body = json_encode($payload, JSON_THROW_ON_ERROR);
    $hmac = base64_encode(hash_hmac('sha256', $body, 'client-secret', binary: true));

    return postJson(route('capell-shopify-commerce.webhooks.shopify'), $payload, [
        'X-Shopify-Hmac-Sha256' => $hmac,
        'X-Shopify-Shop-Domain' => 'foo.myshopify.com',
        'X-Shopify-Topic' => $topic,
        ...$extraHeaders,
    ]);
}

it('ignores a duplicate webhook id without re-running the destructive handler', function (): void {
    $connection = securityWebhookConnection();

    ShopifyProduct::query()->create([
        'connection_id' => $connection->getKey(),
        'shopify_gid' => 'gid://shopify/Product/123',
        'handle' => 'linen-shirt',
        'title' => 'Linen Shirt',
        'search_text' => 'linen shirt linen-shirt',
        'status' => 'active',
        'options' => [],
        'raw_snapshot' => [],
        'synced_at' => now(),
    ]);

    // Pre-record the webhook delivery so the incoming request is a duplicate.
    ShopifyWebhookEvent::query()->create([
        'connection_id' => $connection->getKey(),
        'webhook_id' => 'webhook-abc',
        'topic' => 'products/delete',
        'received_at' => CarbonImmutable::now(),
    ]);

    securityWebhookPost('products/delete', ['id' => 123], [
        'X-Shopify-Webhook-Id' => 'webhook-abc',
    ])
        ->assertOk()
        ->assertJson(['ok' => true, 'duplicate' => true]);

    // The delete handler must NOT have run: the product is still present, and no
    // second event row was written for the same (connection, webhook id).
    expect(ShopifyProduct::query()->where('connection_id', $connection->getKey())->exists())->toBeTrue()
        ->and(ShopifyWebhookEvent::query()
            ->where('connection_id', $connection->getKey())
            ->where('webhook_id', 'webhook-abc')
            ->count())->toBe(1);
});

it('processes a first delivery then ignores its retry with the same webhook id', function (): void {
    $connection = securityWebhookConnection();

    ShopifyProduct::query()->create([
        'connection_id' => $connection->getKey(),
        'shopify_gid' => 'gid://shopify/Product/123',
        'handle' => 'linen-shirt',
        'title' => 'Linen Shirt',
        'search_text' => 'linen shirt linen-shirt',
        'status' => 'active',
        'options' => [],
        'raw_snapshot' => [],
        'synced_at' => now(),
    ]);

    securityWebhookPost('products/delete', ['id' => 123], [
        'X-Shopify-Webhook-Id' => 'webhook-xyz',
    ])->assertOk()->assertJsonMissing(['duplicate' => true]);

    expect(ShopifyProduct::query()->where('connection_id', $connection->getKey())->exists())->toBeFalse();

    // Retry of the same logical event is inert.
    securityWebhookPost('products/delete', ['id' => 123], [
        'X-Shopify-Webhook-Id' => 'webhook-xyz',
    ])->assertOk()->assertJson(['duplicate' => true]);

    expect(ShopifyWebhookEvent::query()
        ->where('connection_id', $connection->getKey())
        ->where('webhook_id', 'webhook-xyz')
        ->count())->toBe(1);
});

it('rejects a stale webhook delivery based on the triggered-at header', function (): void {
    $connection = securityWebhookConnection();

    securityWebhookPost('products/delete', ['id' => 123], [
        'X-Shopify-Webhook-Id' => 'webhook-stale',
        'X-Shopify-Triggered-At' => CarbonImmutable::now()->subSeconds(3600)->toIso8601String(),
    ])->assertStatus(422);

    // No event recorded because the request was rejected before idempotency.
    expect(ShopifyWebhookEvent::query()->where('connection_id', $connection->getKey())->exists())->toBeFalse();
});

it('accepts a fresh webhook delivery within the tolerance window', function (): void {
    securityWebhookConnection();

    securityWebhookPost('products/update', [
        'id' => 123,
        'admin_graphql_api_id' => 'gid://shopify/Product/123',
        'handle' => 'linen-shirt',
        'title' => 'Linen Shirt',
        'status' => 'active',
        'options' => [],
        'variants' => [],
    ], [
        'X-Shopify-Webhook-Id' => 'webhook-fresh',
        'X-Shopify-Triggered-At' => CarbonImmutable::now()->toIso8601String(),
    ])->assertOk();
});

it('rejects a webhook that omits the webhook id so it cannot bypass dedupe', function (): void {
    $connection = securityWebhookConnection();

    ShopifyProduct::query()->create([
        'connection_id' => $connection->getKey(),
        'shopify_gid' => 'gid://shopify/Product/123',
        'handle' => 'linen-shirt',
        'title' => 'Linen Shirt',
        'search_text' => 'linen shirt linen-shirt',
        'status' => 'active',
        'options' => [],
        'raw_snapshot' => [],
        'synced_at' => now(),
    ]);

    // A captured-but-valid destructive webhook replayed with the id header
    // stripped must be rejected, not processed: otherwise the dedupe guard is
    // trivially bypassable and the delete handler would re-run on every replay.
    securityWebhookPost('products/delete', ['id' => 123])
        ->assertStatus(422);

    expect(ShopifyProduct::query()->where('connection_id', $connection->getKey())->exists())->toBeTrue()
        ->and(ShopifyWebhookEvent::query()->where('connection_id', $connection->getKey())->exists())->toBeFalse();
});

it('rejects destructive webhooks for a revoked connection', function (): void {
    $connection = securityWebhookConnection();
    $connection->forceFill([
        'status' => ShopifyConnectionStatus::Revoked,
        'access_token' => null,
    ])->save();

    securityWebhookPost('products/delete', ['id' => 123], [
        'X-Shopify-Webhook-Id' => 'webhook-revoked',
    ])->assertStatus(403);
});
