<?php

declare(strict_types=1);

use Capell\ShopifyCommerce\Actions\Graphql\ExecuteShopifyAdminGraphqlAction;
use Capell\ShopifyCommerce\Enums\ShopifyConnectionStatus;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

it('retries shopify graphql requests after retry-after throttling', function (): void {
    Cache::flush();

    $connection = shopifyGraphqlConnection();

    Http::fake([
        'foo.myshopify.com/admin/api/2026-04/graphql.json' => Http::sequence()
            ->push(['errors' => [['message' => 'throttled']]], 429, ['Retry-After' => '0'])
            ->push(['data' => ['shop' => ['name' => 'Demo']]]),
    ]);

    expect(ExecuteShopifyAdminGraphqlAction::run($connection, '{ shop { name } }'))
        ->toBe(['data' => ['shop' => ['name' => 'Demo']]]);

    Http::assertSentCount(2);
});

it('records carry-forward throttle state from shopify cost metadata', function (): void {
    Cache::flush();

    $connection = shopifyGraphqlConnection();

    Http::fake([
        'foo.myshopify.com/admin/api/2026-04/graphql.json' => Http::response([
            'data' => ['shop' => ['name' => 'Demo']],
            'extensions' => [
                'cost' => [
                    'requestedQueryCost' => 10,
                    'throttleStatus' => [
                        'currentlyAvailable' => 5,
                        'restoreRate' => 1_000_000,
                    ],
                ],
            ],
        ]),
    ]);

    ExecuteShopifyAdminGraphqlAction::run($connection, '{ shop { name } }');

    expect(Cache::get(sprintf('capell-shopify-commerce.graphql.throttle.%s', $connection->getKey())))
        ->toBeNumeric();
});

function shopifyGraphqlConnection(): ShopifyConnection
{
    return ShopifyConnection::query()->create([
        'shop_domain' => 'foo.myshopify.com',
        'status' => ShopifyConnectionStatus::Active,
        'access_token' => 'admin-token',
        'scopes' => ['read_products'],
    ]);
}
