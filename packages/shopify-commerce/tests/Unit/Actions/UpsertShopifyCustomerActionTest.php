<?php

declare(strict_types=1);

use Capell\ShopifyCommerce\Actions\Customers\UpsertShopifyCustomerAction;
use Capell\ShopifyCommerce\Enums\ShopifyConnectionStatus;
use Capell\ShopifyCommerce\Events\ShopifyCustomerSynced;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Capell\ShopifyCommerce\Models\ShopifyCustomer;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;

it('loads the shopify customer cache table', function (): void {
    expect(Schema::hasTable('shopify_customers'))->toBeTrue()
        ->and(Schema::hasColumn('shopify_customers', 'shopify_gid'))->toBeTrue()
        ->and(Schema::hasColumn('shopify_customers', 'email_hash'))->toBeTrue();
});

it('upserts shopify customer snapshots and dispatches a sync event', function (): void {
    Event::fake([ShopifyCustomerSynced::class]);

    $connection = ShopifyConnection::query()->create([
        'shop_domain' => 'foo.myshopify.com',
        'status' => ShopifyConnectionStatus::Active,
        'access_token' => 'admin-token',
        'scopes' => ['read_products', 'read_customers'],
    ]);

    $customer = UpsertShopifyCustomerAction::run($connection, [
        'id' => 'gid://shopify/Customer/123',
        'email' => 'Buyer@Example.test',
        'firstName' => 'Buyer',
        'lastName' => 'Example',
        'phone' => '+44 20 0000 0004',
        'acceptsMarketing' => true,
        'emailMarketingConsent' => [
            'marketingState' => 'SUBSCRIBED',
        ],
        'numberOfOrders' => 4,
        'amountSpent' => [
            'amount' => '125.50',
            'currencyCode' => 'GBP',
        ],
    ]);

    expect($customer)->toBeInstanceOf(ShopifyCustomer::class)
        ->and($customer->connection->is($connection))->toBeTrue()
        ->and($customer->email_hash)->toBe(ShopifyCustomer::emailHash('buyer@example.test'))
        ->and($customer->first_name)->toBe('Buyer')
        ->and($customer->last_name)->toBe('Example')
        ->and($customer->phone)->toBe('+44 20 0000 0004')
        ->and($customer->accepts_marketing)->toBeTrue()
        ->and($customer->marketing_state)->toBe('SUBSCRIBED')
        ->and($customer->orders_count)->toBe(4)
        ->and($customer->total_spent_amount)->toBe('125.500000')
        ->and($customer->total_spent_currency)->toBe('GBP');

    Event::assertDispatched(
        ShopifyCustomerSynced::class,
        fn (ShopifyCustomerSynced $event): bool => $event->customer->is($customer),
    );

    $updatedCustomer = UpsertShopifyCustomerAction::run($connection, [
        'id' => 'gid://shopify/Customer/123',
        'email' => 'buyer@example.test',
        'firstName' => 'Updated',
    ]);

    expect($updatedCustomer->is($customer))->toBeTrue()
        ->and(ShopifyCustomer::query()->count())->toBe(1)
        ->and($updatedCustomer->first_name)->toBe('Updated');
});

it('declares shopify customer cache metadata in the package manifest', function (): void {
    $manifest = json_decode(
        (string) file_get_contents(__DIR__ . '/../../../capell.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($manifest['database']['requiredTables'])->toContain('shopify_customers')
        ->and($manifest['database']['protectedTables'])->toContain('shopify_customers')
        ->and($manifest['actions']['upsertShopifyCustomer'])->toBe(UpsertShopifyCustomerAction::class)
        ->and($manifest['capabilities'])->toContain(
            'shopify-commerce-customer-cache',
            'shopify-commerce-customer-synced-event',
        );
});
