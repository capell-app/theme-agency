<?php

declare(strict_types=1);

use Capell\ShopifyCommerce\Actions\Customers\SyncShopifyCustomersAction;
use Capell\ShopifyCommerce\Actions\Customers\UpsertShopifyCustomerAction;
use Capell\ShopifyCommerce\Actions\OAuth\PruneExpiredShopifyOAuthStatesAction;
use Capell\ShopifyCommerce\Console\Commands\PruneExpiredShopifyOAuthStatesCommand;
use Capell\ShopifyCommerce\Console\Commands\SyncShopifyCustomersCommand;
use Capell\ShopifyCommerce\Enums\ShopifyConnectionStatus;
use Capell\ShopifyCommerce\Events\ShopifyCustomerSynced;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Capell\ShopifyCommerce\Models\ShopifyCustomer;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

it('syncs shopify customers from paginated admin graphql results', function (): void {
    Event::fake([ShopifyCustomerSynced::class]);

    config()->set('capell-shopify-commerce.customer_sync_page_size', 2);
    config()->set('capell-shopify-commerce.customer_sync_max_pages', 5);

    $connection = ShopifyConnection::query()->create([
        'shop_domain' => 'foo.myshopify.com',
        'status' => ShopifyConnectionStatus::Active,
        'access_token' => 'admin-token',
        'scopes' => ['read_products', 'read_customers'],
    ]);

    Http::fake([
        'foo.myshopify.com/admin/api/2026-04/graphql.json' => Http::sequence()
            ->push([
                'data' => [
                    'customers' => [
                        'edges' => [
                            ['cursor' => 'cursor-1', 'node' => shopifyCustomerNode('gid://shopify/Customer/1', 'first@example.test')],
                            ['cursor' => 'cursor-2', 'node' => shopifyCustomerNode('gid://shopify/Customer/2', 'second@example.test')],
                        ],
                        'pageInfo' => ['hasNextPage' => true, 'endCursor' => 'cursor-2'],
                    ],
                ],
            ])
            ->push([
                'data' => [
                    'customers' => [
                        'edges' => [
                            ['cursor' => 'cursor-3', 'node' => shopifyCustomerNode('gid://shopify/Customer/3', 'third@example.test')],
                        ],
                        'pageInfo' => ['hasNextPage' => false, 'endCursor' => 'cursor-3'],
                    ],
                ],
            ]),
    ]);

    expect(SyncShopifyCustomersAction::run($connection))->toBe(3)
        ->and(ShopifyCustomer::query()->count())->toBe(3)
        ->and(ShopifyCustomer::query()->where('shopify_gid', 'gid://shopify/Customer/2')->value('email_hash'))->toBe(ShopifyCustomer::emailHash('second@example.test'));

    Http::assertSentCount(2);
    Http::assertSent(function (Request $request): bool {
        $payload = $request->data();

        return data_get($payload, 'variables.first') === 2
            && data_get($payload, 'variables.after') === null;
    });
    Http::assertSent(function (Request $request): bool {
        $payload = $request->data();

        return data_get($payload, 'variables.after') === 'cursor-2';
    });
    Event::assertDispatched(ShopifyCustomerSynced::class, 3);
});

it('does not call shopify for revoked or tokenless connections', function (): void {
    Http::fake();

    $revokedConnection = ShopifyConnection::query()->create([
        'shop_domain' => 'revoked.myshopify.com',
        'status' => ShopifyConnectionStatus::Revoked,
        'access_token' => 'admin-token',
        'scopes' => ['read_customers'],
    ]);

    $tokenlessConnection = ShopifyConnection::query()->create([
        'shop_domain' => 'tokenless.myshopify.com',
        'status' => ShopifyConnectionStatus::Active,
        'access_token' => null,
        'scopes' => ['read_customers'],
    ]);

    expect(SyncShopifyCustomersAction::run($revokedConnection))->toBe(0)
        ->and(SyncShopifyCustomersAction::run($tokenlessConnection))->toBe(0)
        ->and(ShopifyCustomer::query()->count())->toBe(0);

    Http::assertNothingSent();
});

it('declares the customer sync producer in the package manifest', function (): void {
    $manifest = capell_json_file_array(__DIR__ . '/../../../capell.json');
    $actions = $manifest['actions'] ?? null;
    $commands = $manifest['commands'] ?? null;

    throw_unless(is_array($actions), RuntimeException::class, 'Expected Shopify manifest actions array.');
    throw_unless(is_array($commands), RuntimeException::class, 'Expected Shopify manifest commands array.');

    expect($actions['syncShopifyCustomers'] ?? null)->toBe(SyncShopifyCustomersAction::class)
        ->and($actions['upsertShopifyCustomer'] ?? null)->toBe(UpsertShopifyCustomerAction::class)
        ->and($actions['pruneExpiredShopifyOAuthStates'] ?? null)->toBe(PruneExpiredShopifyOAuthStatesAction::class)
        ->and($commands['pruneOAuthStates'] ?? null)->toBe('capell-shopify-commerce:prune-oauth-states')
        ->and($commands['syncCustomers'] ?? null)->toBe('capell-shopify-commerce:sync-customers')
        ->and(class_exists(PruneExpiredShopifyOAuthStatesCommand::class))->toBeTrue()
        ->and(class_exists(SyncShopifyCustomersCommand::class))->toBeTrue();
});

/**
 * @return array<string, mixed>
 */
function shopifyCustomerNode(string $gid, string $email): array
{
    return [
        'id' => $gid,
        'email' => $email,
        'firstName' => 'Shopify',
        'lastName' => 'Customer',
        'phone' => '+44 20 0000 0001',
        'acceptsMarketing' => true,
        'emailMarketingConsent' => ['marketingState' => 'SUBSCRIBED'],
        'numberOfOrders' => 2,
        'amountSpent' => [
            'amount' => '89.50',
            'currencyCode' => 'GBP',
        ],
        'updatedAt' => '2026-06-05T09:00:00Z',
    ];
}
