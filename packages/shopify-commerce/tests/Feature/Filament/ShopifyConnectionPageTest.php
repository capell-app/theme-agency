<?php

declare(strict_types=1);

use Capell\ShopifyCommerce\Enums\ShopifyConnectionStatus;
use Capell\ShopifyCommerce\Filament\Pages\ShopifyConnectionPage;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Capell\ShopifyCommerce\Models\ShopifyProduct;
use Capell\ShopifyCommerce\Support\Permissions\ShopifyCommercePermission;
use Capell\Tests\Support\Concerns\CreatesAdminUser;
use Spatie\Permission\Models\Permission;

uses(CreatesAdminUser::class);

it('limits shopify connection page access to the package permission', function (): void {
    Permission::findOrCreate(ShopifyCommercePermission::MANAGE, 'web');

    expect(ShopifyConnectionPage::canAccess())->toBeFalse();

    $this->actingAsUser();

    expect(ShopifyConnectionPage::canAccess())->toBeFalse();

    $this->actingAs(test()->createUserWithPermission(ShopifyCommercePermission::MANAGE));

    expect(ShopifyConnectionPage::canAccess())->toBeTrue();
});

it('exposes integration navigation labels', function (): void {
    expect(ShopifyConnectionPage::getNavigationLabel())->toBe('Shopify Commerce')
        ->and(ShopifyConnectionPage::getNavigationGroup())->toBe('Integrations')
        ->and((new ShopifyConnectionPage)->getTitle())->toBe('Shopify Commerce')
        ->and(ShopifyConnectionPage::getNavigationIcon())->not->toBeNull();
});

it('returns null when no active connection exists', function (): void {
    expect((new ShopifyConnectionPage)->getActiveConnection())->toBeNull();
});

it('disconnects the active connection and wipes the token', function (): void {
    Permission::findOrCreate(ShopifyCommercePermission::MANAGE, 'web');
    $this->actingAs(test()->createUserWithPermission(ShopifyCommercePermission::MANAGE));

    $connection = ShopifyConnection::query()->create([
        'shop_domain' => 'foo.myshopify.com',
        'status' => ShopifyConnectionStatus::Active,
        'access_token' => 'admin-token',
        'scopes' => ['read_products'],
    ]);

    (new ShopifyConnectionPage)->disconnect();

    expect($connection->refresh()->status)->toBe(ShopifyConnectionStatus::Revoked)
        ->and($connection->access_token)->toBeNull();
});

it('detects when the connected store already has cached products', function (): void {
    Permission::findOrCreate(ShopifyCommercePermission::MANAGE, 'web');
    $this->actingAs(test()->createUserWithPermission(ShopifyCommercePermission::MANAGE));

    $connection = ShopifyConnection::query()->create([
        'shop_domain' => 'foo.myshopify.com',
        'status' => ShopifyConnectionStatus::Active,
        'access_token' => 'admin-token',
        'scopes' => ['read_products'],
    ]);

    expect((new ShopifyConnectionPage)->hasCachedProducts())->toBeFalse();

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

    expect((new ShopifyConnectionPage)->hasCachedProducts())->toBeTrue();
});
