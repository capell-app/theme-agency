<?php

declare(strict_types=1);

use Capell\ShopifyCommerce\Actions\Catalog\FetchShopifyProductAction;
use Capell\ShopifyCommerce\Actions\Catalog\InvalidateShopifyProductSearchCacheAction;
use Capell\ShopifyCommerce\Enums\ShopifyConnectionStatus;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Capell\ShopifyCommerce\Models\ShopifyProduct;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

it('fetches a product through graphql and persists the local catalog row', function (): void {
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
                'product' => [
                    'id' => 'gid://shopify/Product/42',
                    'handle' => 'alpha-shirt',
                    'title' => 'Alpha Shirt',
                    'status' => 'ACTIVE',
                    'featuredImage' => [
                        'url' => 'https://cdn.example.test/alpha.jpg',
                        'altText' => 'Alpha',
                    ],
                ],
            ],
        ]),
    ]);

    $product = FetchShopifyProductAction::run('gid://shopify/Product/42', $connection);

    expect($product)->toBeInstanceOf(ShopifyProduct::class);
    throw_unless($product instanceof ShopifyProduct, RuntimeException::class, 'Expected fetched Shopify product.');

    expect($product->shopify_gid)->toBe('gid://shopify/Product/42')
        ->and($product->handle)->toBe('alpha-shirt')
        ->and($product->title)->toBe('Alpha Shirt')
        ->and($product->status)->toBe('active')
        ->and($product->featured_image)->toBe([
            'url' => 'https://cdn.example.test/alpha.jpg',
            'altText' => 'Alpha',
        ])
        ->and($product->search_text)->toBe('alpha shirt alpha-shirt')
        ->and($product->synced_at)->not->toBeNull()
        ->and(ShopifyProduct::query()->where('shopify_gid', 'gid://shopify/Product/42')->exists())->toBeTrue()
        ->and(InvalidateShopifyProductSearchCacheAction::version(shopifyFetchProductIntValue($connection->getKey())))->toBe(2);

    Http::assertSent(static function (Request $request): bool {
        $variables = $request['variables'] ?? null;
        $query = $request['query'] ?? null;

        return is_array($variables)
            && ($variables['id'] ?? null) === 'gid://shopify/Product/42'
            && is_string($query)
            && str_contains($query, 'query ShopifyProduct');
    });
});

it('does not invoke graphql inside the persistence transaction', function (): void {
    $connection = ShopifyConnection::query()->create([
        'shop_domain' => 'foo.myshopify.com',
        'status' => ShopifyConnectionStatus::Active,
        'access_token' => 'admin-token',
        'scopes' => ['read_products'],
    ]);
    $baselineTransactionLevel = DB::connection()->transactionLevel();
    $transactionLevels = [];

    Http::fake(function (Request $request, array $options) use (&$transactionLevels): PromiseInterface {
        $transactionLevels[] = DB::connection()->transactionLevel();

        return Http::response([
            'data' => [
                'product' => [
                    'id' => 'gid://shopify/Product/99',
                    'handle' => 'outside-transaction',
                    'title' => 'Outside Transaction',
                    'status' => 'ACTIVE',
                    'featuredImage' => null,
                ],
            ],
        ]);
    });

    $product = FetchShopifyProductAction::run('gid://shopify/Product/99', $connection);

    expect($transactionLevels)->toBe([$baselineTransactionLevel])
        ->and($product)->toBeInstanceOf(ShopifyProduct::class)
        ->and(ShopifyProduct::query()->where('shopify_gid', 'gid://shopify/Product/99')->exists())->toBeTrue();
});

function shopifyFetchProductIntValue(mixed $value): int
{
    return is_numeric($value) ? (int) $value : 0;
}
