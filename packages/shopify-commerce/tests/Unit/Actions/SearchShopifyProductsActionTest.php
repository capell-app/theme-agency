<?php

declare(strict_types=1);

use Capell\ShopifyCommerce\Actions\Catalog\SearchShopifyProductsAction;
use Capell\ShopifyCommerce\Enums\ShopifyConnectionStatus;
use Capell\ShopifyCommerce\Exceptions\ShopifyGraphqlException;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Capell\ShopifyCommerce\Models\ShopifyProduct;
use Capell\ShopifyCommerce\Models\ShopifyProductVariant;
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
                            'options' => [
                                [
                                    'name' => 'Size',
                                    'values' => ['8', '9'],
                                ],
                            ],
                            'featuredImage' => [
                                'url' => 'https://cdn.example/gamma.jpg',
                                'altText' => 'Gamma shoes',
                            ],
                            'variants' => [
                                'nodes' => [
                                    [
                                        'id' => 'gid://shopify/ProductVariant/31',
                                        'title' => 'Gamma 8',
                                        'availableForSale' => true,
                                        'priceV2' => [
                                            'amount' => '49.9900',
                                            'currencyCode' => 'GBP',
                                        ],
                                        'selectedOptions' => [
                                            [
                                                'name' => 'Size',
                                                'value' => '8',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $staleProduct = ShopifyProduct::query()->create([
        'connection_id' => $connection->getKey(),
        'shopify_gid' => 'gid://shopify/Product/3',
        'handle' => 'gamma-old',
        'title' => 'Gamma Old',
        'status' => 'active',
        'options' => [],
        'raw_snapshot' => [],
        'synced_at' => now()->subDay(),
    ]);
    ShopifyProductVariant::query()->create([
        'product_id' => $staleProduct->getKey(),
        'shopify_gid' => 'gid://shopify/ProductVariant/stale',
        'title' => 'Stale',
        'price_amount' => '1.00',
        'price_currency' => 'USD',
        'available_for_sale' => false,
        'selected_options' => [],
    ]);

    $results = SearchShopifyProductsAction::run('Gamma', 7, $connection);
    $product = ShopifyProduct::query()
        ->where('shopify_gid', 'gid://shopify/Product/3')
        ->sole();

    expect($results)->toHaveCount(1)
        ->and($results->first()?->shopify_gid)->toBe('gid://shopify/Product/3')
        ->and($product->handle)->toBe('gamma')
        ->and($product->options->toArray())->toBe([
            [
                'name' => 'Size',
                'values' => ['8', '9'],
                'value' => null,
            ],
        ])
        ->and($product->featured_image)->toBe([
            'url' => 'https://cdn.example/gamma.jpg',
            'altText' => 'Gamma shoes',
        ])
        ->and($product->raw_snapshot['id'])->toBe('gid://shopify/Product/3')
        ->and(ShopifyProductVariant::query()->where('shopify_gid', 'gid://shopify/ProductVariant/stale')->exists())->toBeFalse()
        ->and(ShopifyProductVariant::query()->where('shopify_gid', 'gid://shopify/ProductVariant/31')->value('price_amount'))->toBe('49.9900');

    Http::assertSent(static fn (Request $request): bool => $request['variables']['query'] === 'title:*Gamma* OR handle:*Gamma*'
        && $request['variables']['first'] === 7);
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
