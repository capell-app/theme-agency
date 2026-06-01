<?php

declare(strict_types=1);

use Capell\Core\Models\Site;
use Capell\ShopifyCommerce\Actions\Catalog\FetchShopifyProductAction;
use Capell\ShopifyCommerce\Actions\Catalog\SyncShopifyProductsAction;
use Capell\ShopifyCommerce\Enums\ShopifyConnectionStatus;
use Capell\ShopifyCommerce\Filament\Pages\ShopifyConnectionPage;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Capell\ShopifyCommerce\Models\ShopifyProduct;
use Capell\ShopifyCommerce\Support\Permissions\ShopifyCommercePermission;
use Capell\Tests\Fixtures\Models\User;
use Capell\Tests\Support\Concerns\CreatesAdminUser;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
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

it('fetches a shopify product through graphql and refreshes the local catalog row', function (): void {
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

    expect($product)->toBeInstanceOf(ShopifyProduct::class)
        ->and($product?->shopify_gid)->toBe('gid://shopify/Product/42')
        ->and($product?->handle)->toBe('alpha-shirt')
        ->and($product?->title)->toBe('Alpha Shirt')
        ->and($product?->status)->toBe('active')
        ->and($product?->featured_image)->toBe([
            'url' => 'https://cdn.example.test/alpha.jpg',
            'altText' => 'Alpha',
        ])
        ->and($product?->search_text)->toContain('alpha');

    Http::assertSent(static fn (Request $request): bool => $request['variables']['id'] === 'gid://shopify/Product/42'
        && str_contains((string) $request['query'], 'query ShopifyProduct'));
});

it('searches and queues sync from the shopify connection page workflow', function (): void {
    Cache::flush();
    Queue::fake();
    Permission::findOrCreate(ShopifyCommercePermission::MANAGE, 'web');
    $this->actingAs(test()->createUserWithPermission(ShopifyCommercePermission::MANAGE));

    $connection = ShopifyConnection::query()->create([
        'shop_domain' => 'foo.myshopify.com',
        'status' => ShopifyConnectionStatus::Active,
        'access_token' => 'admin-token',
        'scopes' => ['read_products'],
        'sync_status' => null,
    ]);
    ShopifyProduct::query()->create([
        'connection_id' => $connection->getKey(),
        'shopify_gid' => 'gid://shopify/Product/7',
        'handle' => 'search-result',
        'title' => 'Search Result',
        'search_text' => ShopifyProduct::searchableText('Search Result', 'search-result'),
        'status' => 'active',
        'options' => [],
        'raw_snapshot' => [],
        'synced_at' => now(),
    ]);

    $page = new ShopifyConnectionPage;
    $page->mount();
    $page->selectedSiteId = null;
    $page->searchTerm = 'Search';

    $page->search();
    $page->syncNow();

    SyncShopifyProductsAction::assertPushed(
        1,
        static fn (SyncShopifyProductsAction $action, array $parameters): bool => $parameters === [(int) $connection->getKey()],
    );

    expect($page->searchResults->pluck('shopify_gid')->all())->toBe(['gid://shopify/Product/7'])
        ->and($page->isSyncBusy($connection->refresh()))->toBeTrue()
        ->and($connection->sync_status)->toBe('queued')
        ->and($connection->last_sync_queued_at)->not->toBeNull();
});

it('normalizes tampered site selections before mutating shopify connections', function (): void {
    Cache::flush();
    Queue::fake();
    Permission::findOrCreate(ShopifyCommercePermission::MANAGE, 'web');

    $assignedSite = Site::factory()->create(['name' => 'Assigned Shopify Site']);
    $otherSite = Site::factory()->create(['name' => 'Other Shopify Site']);
    $user = new class extends User
    {
        public int $assignedSiteId = 0;

        protected $table = 'users';

        public function getAssignedSiteIds(): SupportCollection
        {
            return collect([$this->assignedSiteId]);
        }

        public function isGlobalAdmin(): bool
        {
            return false;
        }

        public function getMorphClass(): string
        {
            return User::class;
        }

        protected function getGuardNames(): SupportCollection
        {
            return collect(['web']);
        }

        protected function getDefaultGuardName(): string
        {
            return 'web';
        }
    };
    $user->forceFill([
        'name' => 'Site scoped Shopify manager',
        'email' => fake()->unique()->safeEmail(),
        'password' => bcrypt('password'),
    ]);
    $user->assignedSiteId = (int) $assignedSite->getKey();
    $user->save();
    $user->givePermissionTo(ShopifyCommercePermission::MANAGE);
    $this->actingAs($user);

    $assignedConnection = ShopifyConnection::query()->create([
        'site_id' => $assignedSite->getKey(),
        'shop_domain' => 'assigned.myshopify.com',
        'status' => ShopifyConnectionStatus::Active,
        'access_token' => 'assigned-token',
        'scopes' => ['read_products'],
        'sync_status' => null,
    ]);
    $otherConnection = ShopifyConnection::query()->create([
        'site_id' => $otherSite->getKey(),
        'shop_domain' => 'other.myshopify.com',
        'status' => ShopifyConnectionStatus::Active,
        'access_token' => 'other-token',
        'scopes' => ['read_products'],
        'sync_status' => null,
    ]);

    $page = new ShopifyConnectionPage;
    $page->mount();
    $page->selectedSiteId = (int) $otherSite->getKey();
    $page->syncNow();

    expect($page->selectedSiteId)->toBe((int) $assignedSite->getKey())
        ->and($assignedConnection->refresh()->sync_status)->toBeNull()
        ->and($otherConnection->refresh()->sync_status)->toBeNull();
});
