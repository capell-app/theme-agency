<?php

declare(strict_types=1);

use Capell\ShopifyCommerce\Actions\Catalog\ImportShopifyProductBulkSyncAction;
use Capell\ShopifyCommerce\Actions\Catalog\PollShopifyProductBulkSyncAction;
use Capell\ShopifyCommerce\Actions\Catalog\SanitizeShopifySyncErrorAction;
use Capell\ShopifyCommerce\Actions\Catalog\SyncShopifyProductsAction;
use Capell\ShopifyCommerce\Enums\ShopifyConnectionStatus;
use Capell\ShopifyCommerce\Exceptions\ShopifyGraphqlException;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Capell\ShopifyCommerce\Models\ShopifyProduct;
use Capell\ShopifyCommerce\Models\ShopifyProductVariant;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;

it('starts a shopify bulk product sync', function (): void {
    $connection = shopifyBulkConnection();
    config()->set('capell-shopify-commerce.http_timeout', 6);
    $timeouts = [];

    Http::fake(function (ClientRequest $request, array $options) use (&$timeouts): PromiseInterface {
        $timeouts[] = $options['timeout'] ?? null;

        return Http::response([
            'data' => [
                'bulkOperationRunQuery' => [
                    'bulkOperation' => ['id' => 'gid://shopify/BulkOperation/1', 'status' => 'CREATED'],
                    'userErrors' => [],
                ],
            ],
        ]);
    });

    expect(SyncShopifyProductsAction::run($connection))->toBe('gid://shopify/BulkOperation/1');

    $connection->refresh();

    expect($connection->sync_status)->toBe('running')
        ->and($connection->bulk_operation_id)->toBe('gid://shopify/BulkOperation/1')
        ->and($connection->last_sync_started_at)->not->toBeNull()
        ->and($timeouts)->toBe([6]);
});

it('marks the connection as errored when starting bulk sync fails', function (): void {
    $connection = shopifyBulkConnection([
        'access_token' => 'shpat_secret_token',
    ]);

    Http::fake([
        'foo.myshopify.com/admin/api/2026-04/graphql.json' => Http::response([
            'errors' => [
                ['message' => 'Access denied'],
            ],
        ]),
    ]);

    expect(static fn (): ?string => SyncShopifyProductsAction::run($connection))
        ->toThrow(ShopifyGraphqlException::class);

    $connection->refresh();

    expect($connection->status)->toBe(ShopifyConnectionStatus::Error)
        ->and($connection->sync_status)->toBe('failed')
        ->and($connection->last_sync_error)->not->toBeNull()
        ->and($connection->last_sync_error)->not->toContain('shpat_secret_token')
        ->and($connection->last_sync_error)->not->toContain('foo.myshopify.com');
});

it('polls completed and failed bulk operations', function (): void {
    $connection = shopifyBulkConnection([
        'sync_status' => 'running',
        'bulk_operation_id' => 'gid://shopify/BulkOperation/1',
    ]);

    Http::fake([
        'foo.myshopify.com/admin/api/2026-04/graphql.json' => Http::sequence()
            ->push([
                'data' => [
                    'currentBulkOperation' => [
                        'id' => 'gid://shopify/BulkOperation/1',
                        'status' => 'COMPLETED',
                        'url' => 'https://bulk.example/products.jsonl',
                    ],
                ],
            ])
            ->push([
                'data' => [
                    'currentBulkOperation' => [
                        'id' => 'gid://shopify/BulkOperation/2',
                        'status' => 'FAILED',
                        'errorCode' => 'ACCESS_DENIED',
                    ],
                ],
            ]),
    ]);

    expect(PollShopifyProductBulkSyncAction::run($connection))->toBe('COMPLETED')
        ->and($connection->refresh()->sync_status)->toBe('completed')
        ->and($connection->bulk_operation_url)->toBe('https://bulk.example/products.jsonl');

    expect(PollShopifyProductBulkSyncAction::run($connection))->toBe('FAILED')
        ->and($connection->refresh()->status)->toBe(ShopifyConnectionStatus::Error)
        ->and($connection->sync_status)->toBe('failed');
});

it('imports bulk jsonl products, variants, prunes stale rows, and preserves money precision', function (): void {
    $connection = shopifyBulkConnection([
        'sync_status' => 'completed',
        'bulk_operation_url' => 'https://bulk.example/products.jsonl',
    ]);
    config()->set('capell-shopify-commerce.http_timeout', 11);
    $timeouts = [];
    $sinks = [];

    ShopifyProduct::query()->create([
        'connection_id' => $connection->getKey(),
        'shopify_gid' => 'gid://shopify/Product/stale',
        'handle' => 'stale',
        'title' => 'Stale',
        'search_text' => 'stale stale',
        'status' => 'active',
        'options' => [],
        'raw_snapshot' => [],
        'synced_at' => now()->subDay(),
    ]);

    Http::fake(function (ClientRequest $request, array $options) use (&$timeouts, &$sinks): PromiseInterface {
        $timeouts[] = $options['timeout'] ?? null;
        $sinks[] = $options['sink'] ?? null;

        return Http::response(implode("\n", [
            json_encode([
                'id' => 'gid://shopify/Product/1',
                'handle' => 'alpha',
                'title' => 'Alpha Shirt',
                'status' => 'ACTIVE',
                'options' => [['name' => 'Size', 'values' => ['M']]],
                'featuredImage' => ['url' => 'https://cdn.example/alpha.jpg', 'altText' => 'Alpha'],
                'variants' => [
                    'edges' => [
                        [
                            'node' => [
                                'id' => 'gid://shopify/ProductVariant/1',
                                'title' => 'Default',
                                'priceV2' => ['amount' => '19.9999', 'currencyCode' => 'GBP'],
                                'availableForSale' => true,
                                'selectedOptions' => [['name' => 'Size', 'value' => 'M']],
                            ],
                        ],
                    ],
                ],
            ], JSON_THROW_ON_ERROR),
        ]));
    });

    expect(ImportShopifyProductBulkSyncAction::run($connection))->toBe(1)
        ->and(ShopifyProduct::query()->where('shopify_gid', 'gid://shopify/Product/stale')->exists())->toBeFalse()
        ->and(ShopifyProduct::query()->where('shopify_gid', 'gid://shopify/Product/1')->value('search_text'))->toBe('alpha shirt alpha')
        ->and(ShopifyProductVariant::query()->where('shopify_gid', 'gid://shopify/ProductVariant/1')->value('price_amount'))->toBe('19.999900')
        ->and($connection->refresh()->status)->toBe(ShopifyConnectionStatus::Active)
        ->and($connection->sync_status)->toBe('idle')
        ->and($timeouts)->toBe([11])
        ->and($sinks[0] ?? null)->toBeString();
});

it('does not reactivate revoked connections during import', function (): void {
    $connection = shopifyBulkConnection([
        'status' => ShopifyConnectionStatus::Revoked,
        'sync_status' => 'completed',
        'bulk_operation_url' => 'https://bulk.example/products.jsonl',
    ]);

    expect(ImportShopifyProductBulkSyncAction::run($connection))->toBe(0)
        ->and($connection->refresh()->status)->toBe(ShopifyConnectionStatus::Revoked);
});

it('sanitizes persisted shopify sync error messages', function (): void {
    $connection = shopifyBulkConnection([
        'access_token' => 'shpat_secret_token',
    ]);

    $message = SanitizeShopifySyncErrorAction::run(
        new RuntimeException('GET https://foo.myshopify.com/admin/api/graphql.json?access_token=shpat_secret_token failed with X-Shopify-Access-Token=shpat_secret_token'),
        $connection,
    );

    expect($message)->toContain('[shopify-url]')
        ->and($message)->not->toContain('foo.myshopify.com')
        ->and($message)->not->toContain('shpat_secret_token')
        ->and($message)->not->toContain('access_token=shpat_secret_token')
        ->and($message)->not->toContain('X-Shopify-Access-Token=shpat_secret_token');
});

/**
 * @param  array<string, mixed>  $overrides
 */
function shopifyBulkConnection(array $overrides = []): ShopifyConnection
{
    /** @var ShopifyConnection $connection */
    $connection = ShopifyConnection::query()->create([
        'shop_domain' => 'foo.myshopify.com',
        'status' => ShopifyConnectionStatus::Active,
        'access_token' => 'admin-token',
        'scopes' => ['read_products'],
        ...$overrides,
    ]);

    return $connection;
}
