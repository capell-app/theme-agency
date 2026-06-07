<?php

declare(strict_types=1);

use Capell\ShopifyCommerce\Enums\ShopifyConnectionStatus;
use Capell\ShopifyCommerce\Enums\ShopifySyncStatus;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Capell\ShopifyCommerce\Models\ShopifyCustomer;
use Capell\ShopifyCommerce\Models\ShopifyProduct;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\TestResponse;

beforeEach(function (): void {
    config()->set('capell-shopify-commerce.client_secret', 'client-secret');
});

it('ingests product update webhooks into the local catalog cache', function (): void {
    $connection = shopifyWebhookConnection();
    $payload = [
        'id' => 123,
        'admin_graphql_api_id' => 'gid://shopify/Product/123',
        'handle' => 'linen-shirt',
        'title' => 'Linen Shirt',
        'status' => 'active',
        'options' => [
            ['name' => 'Size', 'values' => ['S', 'M']],
        ],
        'variants' => [
            [
                'id' => 456,
                'admin_graphql_api_id' => 'gid://shopify/ProductVariant/456',
                'title' => 'Small',
                'price' => '29.00',
                'inventory_quantity' => 5,
                'option1' => 'S',
            ],
        ],
    ];

    shopifyWebhookPost('products/update', $payload)->assertOk();

    $product = ShopifyProduct::query()->with('variants')->where('connection_id', $connection->getKey())->firstOrFail();

    expect($product->shopify_gid)->toBe('gid://shopify/Product/123')
        ->and($product->handle)->toBe('linen-shirt')
        ->and($product->title)->toBe('Linen Shirt')
        ->and($product->variants)->toHaveCount(1)
        ->and($product->variants->first()?->shopify_gid)->toBe('gid://shopify/ProductVariant/456');
});

it('ingests product delete webhooks by removing cached products', function (): void {
    $connection = shopifyWebhookConnection();
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

    shopifyWebhookPost('products/delete', [
        'id' => 123,
    ])->assertOk();

    expect(ShopifyProduct::query()->where('connection_id', $connection->getKey())->exists())->toBeFalse();
});

it('ingests customer update webhooks into the customer cache', function (): void {
    Event::fake();
    $connection = shopifyWebhookConnection();

    shopifyWebhookPost('customers/update', [
        'id' => 789,
        'admin_graphql_api_id' => 'gid://shopify/Customer/789',
        'email' => 'buyer@example.test',
        'first_name' => 'Buyer',
        'last_name' => 'Example',
        'orders_count' => 2,
        'total_spent' => '44.50',
        'currency' => 'GBP',
    ])->assertOk();

    $customer = ShopifyCustomer::query()->where('connection_id', $connection->getKey())->firstOrFail();

    expect($customer->shopify_gid)->toBe('gid://shopify/Customer/789')
        ->and($customer->email)->toBe('buyer@example.test')
        ->and($customer->orders_count)->toBe(2);
});

it('revokes connections when shopify sends app uninstalled', function (): void {
    $connection = shopifyWebhookConnection();

    shopifyWebhookPost('app/uninstalled', ['id' => 1])->assertOk();

    $connection->refresh();

    expect($connection->status)->toBe(ShopifyConnectionStatus::Revoked)
        ->and($connection->access_token)->toBeNull()
        ->and($connection->sync_status)->toBe(ShopifySyncStatus::Revoked->value);
});

it('rejects webhooks with invalid signatures', function (): void {
    shopifyWebhookConnection();

    $this->postJson(route('capell-shopify-commerce.webhooks.shopify'), ['id' => 123], [
        'X-Shopify-Hmac-Sha256' => 'bad-signature',
        'X-Shopify-Shop-Domain' => 'foo.myshopify.com',
        'X-Shopify-Topic' => 'products/update',
    ])->assertUnauthorized();
});

function shopifyWebhookConnection(): ShopifyConnection
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
 */
function shopifyWebhookPost(string $topic, array $payload): TestResponse
{
    $body = json_encode($payload, JSON_THROW_ON_ERROR);
    $hmac = base64_encode(hash_hmac('sha256', $body, 'client-secret', binary: true));

    return test()->postJson(route('capell-shopify-commerce.webhooks.shopify'), $payload, [
        'X-Shopify-Hmac-Sha256' => $hmac,
        'X-Shopify-Shop-Domain' => 'foo.myshopify.com',
        'X-Shopify-Topic' => $topic,
    ]);
}
