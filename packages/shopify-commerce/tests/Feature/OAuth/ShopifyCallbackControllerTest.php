<?php

declare(strict_types=1);

use Capell\ShopifyCommerce\Actions\Catalog\SyncShopifyProductsAction;
use Capell\ShopifyCommerce\Actions\OAuth\CreateShopifyOAuthStateAction;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Capell\ShopifyCommerce\Support\Permissions\ShopifyCommercePermission;
use Capell\Tests\Support\Concerns\CreatesAdminUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Permission;

uses(CreatesAdminUser::class);

beforeEach(function (): void {
    Permission::findOrCreate(ShopifyCommercePermission::MANAGE, 'web');
    config()->set('capell-shopify-commerce.client_id', 'client-id');
    config()->set('capell-shopify-commerce.client_secret', 'client-secret');

    $this->actingAs(test()->createUserWithPermission(ShopifyCommercePermission::MANAGE));
});

it('rejects callbacks with a bad hmac', function (): void {
    $state = CreateShopifyOAuthStateAction::run('foo.myshopify.com', auth()->user());

    $this->get(route('capell-shopify-commerce.oauth.callback', [
        'code' => 'oauth-code',
        'shop' => 'foo.myshopify.com',
        'state' => $state,
        'timestamp' => '1700000000',
        'hmac' => 'bad-signature',
    ]))
        ->assertRedirect(route('filament.admin.pages.shopify-commerce'))
        ->assertSessionHasErrors();
});

it('rejects callbacks with an expired or missing state', function (): void {
    $query = signedShopifyCallbackQuery([
        'code' => 'oauth-code',
        'shop' => 'foo.myshopify.com',
        'state' => 'missing-state',
        'timestamp' => '1700000000',
    ]);

    $this->get(route('capell-shopify-commerce.oauth.callback', $query))
        ->assertRedirect(route('filament.admin.pages.shopify-commerce'))
        ->assertSessionHasErrors();
});

it('rejects callbacks when the oauth state belongs to another user', function (): void {
    $firstUser = auth()->user();
    $secondUser = test()->createUserWithPermission(ShopifyCommercePermission::MANAGE);
    $state = CreateShopifyOAuthStateAction::run('foo.myshopify.com', $firstUser);

    $this->actingAs($secondUser);

    $query = signedShopifyCallbackQuery([
        'code' => 'oauth-code',
        'shop' => 'foo.myshopify.com',
        'state' => $state,
        'timestamp' => '1700000000',
    ]);

    $this->get(route('capell-shopify-commerce.oauth.callback', $query))
        ->assertRedirect(route('filament.admin.pages.shopify-commerce'))
        ->assertSessionHasErrors();

    expect(ShopifyConnection::query()->exists())->toBeFalse();
});

it('requires shopify commerce permission for callbacks', function (): void {
    $this->actingAs(test()->createUser());

    $query = signedShopifyCallbackQuery([
        'code' => 'oauth-code',
        'shop' => 'foo.myshopify.com',
        'state' => 'state',
        'timestamp' => '1700000000',
    ]);

    $this->get(route('capell-shopify-commerce.oauth.callback', $query))
        ->assertForbidden();
});

it('creates an encrypted connection and queues sync after a valid callback', function (): void {
    Queue::fake();

    $state = CreateShopifyOAuthStateAction::run('foo.myshopify.com', auth()->user());

    Http::fake([
        'foo.myshopify.com/admin/oauth/access_token' => Http::response([
            'access_token' => 'shopify-admin-token',
            'scope' => 'read_products',
        ]),
    ]);

    $query = signedShopifyCallbackQuery([
        'code' => 'oauth-code',
        'shop' => 'foo.myshopify.com',
        'state' => $state,
        'timestamp' => '1700000000',
    ]);

    $this->get(route('capell-shopify-commerce.oauth.callback', $query))
        ->assertRedirect(route('filament.admin.pages.shopify-commerce'));

    $connection = ShopifyConnection::query()->firstOrFail();
    $storedToken = DB::table('shopify_connections')->where('id', $connection->getKey())->value('access_token');

    expect($connection->shop_domain)->toBe('foo.myshopify.com')
        ->and($connection->access_token)->toBe('shopify-admin-token')
        ->and($storedToken)->not->toBe('shopify-admin-token');

    SyncShopifyProductsAction::assertPushed(1, static fn (SyncShopifyProductsAction $action, array $parameters): bool => $parameters === [(int) $connection->getKey()]);
});

/**
 * @param  array<string, string>  $query
 * @return array<string, string>
 */
function signedShopifyCallbackQuery(array $query): array
{
    ksort($query);
    $query['hmac'] = hash_hmac(
        'sha256',
        http_build_query($query, '', '&', PHP_QUERY_RFC3986),
        (string) config('capell-shopify-commerce.client_secret'),
    );

    return $query;
}
