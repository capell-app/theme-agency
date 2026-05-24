<?php

declare(strict_types=1);

use Capell\ShopifyCommerce\Actions\Catalog\SearchShopifyProductsAction;
use Capell\ShopifyCommerce\Enums\ShopifyConnectionStatus;
use Capell\ShopifyCommerce\Exceptions\ShopifyGraphqlException;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Capell\ShopifyCommerce\Models\ShopifyProduct;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

it('searches local cached products first', function (): void {
    Cache::flush();

    $connection = ShopifyConnection::query()->create([
        'shop_domain' => 'foo.myshopify.com',
        'status' => ShopifyConnectionStatus::Active,
        'access_token' => 'admin-token',
        'scopes' => ['read_products'],
    ]);

    ShopifyProduct::query()->create([
        'connection_id' => $connection->getKey(),
        'shopify_gid' => 'gid://shopify/Product/1',
        'handle' => 'alpha',
        'title' => 'Alpha Shirt',
        'status' => 'active',
        'options' => [],
        'raw_snapshot' => [],
        'synced_at' => now(),
    ]);

    Http::fake();

    $results = SearchShopifyProductsAction::run('Alpha', 20, $connection);

    expect($results)->toHaveCount(1)
        ->and($results->first()?->title)->toBe('Alpha Shirt');
});

it('falls back to live graphql when local search has no matches', function (): void {
    Cache::flush();

    $connection = ShopifyConnection::query()->create([
        'shop_domain' => 'foo.myshopify.com',
        'status' => ShopifyConnectionStatus::Active,
        'access_token' => 'admin-token',
        'scopes' => ['read_products'],
    ]);

    Http::fake([
        'foo.myshopify.com/admin/api/2026-04/graphql.json' => Http::response([
            'data' => [
                'products' => [
                    'nodes' => [
                        [
                            'id' => 'gid://shopify/Product/3',
                            'handle' => 'gamma',
                            'title' => 'Gamma Shoes',
                            'status' => 'ACTIVE',
                            'featuredImage' => null,
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $results = SearchShopifyProductsAction::run('Gamma', 20, $connection);

    expect($results)->toHaveCount(1)
        ->and($results->first()?->shopify_gid)->toBe('gid://shopify/Product/3')
        ->and(ShopifyProduct::query()->where('handle', 'gamma')->exists())->toBeTrue();

    Http::assertSent(static fn (Request $request): bool => $request['variables']['query'] === 'title:*Gamma* OR handle:*Gamma*');
});

it('keeps cached searches scoped by connection', function (): void {
    Cache::flush();

    $firstConnection = ShopifyConnection::query()->create([
        'shop_domain' => 'foo.myshopify.com',
        'status' => ShopifyConnectionStatus::Active,
        'access_token' => 'first-token',
        'scopes' => ['read_products'],
    ]);
    $secondConnection = ShopifyConnection::query()->create([
        'shop_domain' => 'bar.myshopify.com',
        'status' => ShopifyConnectionStatus::Active,
        'access_token' => 'second-token',
        'scopes' => ['read_products'],
    ]);

    ShopifyProduct::query()->create([
        'connection_id' => $firstConnection->getKey(),
        'shopify_gid' => 'gid://shopify/Product/1',
        'handle' => 'alpha',
        'title' => 'Shared Name',
        'status' => 'active',
        'options' => [],
        'raw_snapshot' => [],
        'synced_at' => now(),
    ]);
    ShopifyProduct::query()->create([
        'connection_id' => $secondConnection->getKey(),
        'shopify_gid' => 'gid://shopify/Product/2',
        'handle' => 'beta',
        'title' => 'Shared Name',
        'status' => 'active',
        'options' => [],
        'raw_snapshot' => [],
        'synced_at' => now(),
    ]);

    $firstResults = SearchShopifyProductsAction::run('Shared', 20, $firstConnection);
    $secondResults = SearchShopifyProductsAction::run('Shared', 20, $secondConnection);

    expect($firstResults->first()?->shopify_gid)->toBe('gid://shopify/Product/1')
        ->and($secondResults->first()?->shopify_gid)->toBe('gid://shopify/Product/2');
});

it('does not persist products when live graphql search fails', function (): void {
    Cache::flush();

    $connection = ShopifyConnection::query()->create([
        'shop_domain' => 'foo.myshopify.com',
        'status' => ShopifyConnectionStatus::Active,
        'access_token' => 'admin-token',
        'scopes' => ['read_products'],
    ]);

    Http::fake([
        'foo.myshopify.com/admin/api/2026-04/graphql.json' => Http::response([
            'errors' => [
                ['message' => 'Access denied'],
            ],
        ]),
    ]);

    expect(static fn (): Collection => SearchShopifyProductsAction::run('Missing', 20, $connection))
        ->toThrow(ShopifyGraphqlException::class);

    expect(ShopifyProduct::query()->count())->toBe(0);
});
