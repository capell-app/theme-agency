<?php

declare(strict_types=1);

use Capell\ShopifyCommerce\Models\ShopifyOAuthState;
use Capell\ShopifyCommerce\Support\Permissions\ShopifyCommercePermission;
use Capell\Tests\Support\Concerns\CreatesAdminUser;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;

uses(CreatesAdminUser::class);

beforeEach(function (): void {
    Permission::findOrCreate(ShopifyCommercePermission::MANAGE, 'web');
    config()->set('capell-shopify-commerce.client_id', 'client-id');
    config()->set('capell-shopify-commerce.client_secret', 'client-secret');

    Route::get('/login', static fn (): string => 'login')->name('login');
});

it('requires authentication for shopify install', function (): void {
    $this->get(route('capell-shopify-commerce.oauth.install', ['shop' => 'foo.myshopify.com']))
        ->assertRedirect();
});

it('rejects invalid shop domains', function (): void {
    $this->actingAs(test()->createUserWithPermission(ShopifyCommercePermission::MANAGE));

    $this->get(route('capell-shopify-commerce.oauth.install', ['shop' => 'evil.com']))
        ->assertStatus(422);
});

it('requires shopify commerce permission for installs', function (): void {
    $this->actingAs(test()->createUser());

    $this->get(route('capell-shopify-commerce.oauth.install', ['shop' => 'foo.myshopify.com']))
        ->assertForbidden();
});

it('redirects valid installs to shopify and stores oauth state', function (): void {
    $this->actingAs(test()->createUserWithPermission(ShopifyCommercePermission::MANAGE));

    $response = $this->get(route('capell-shopify-commerce.oauth.install', ['shop' => 'foo.myshopify.com']));

    $response->assertRedirect();

    $redirectUrl = $response->baseResponse->headers->get('Location');

    expect($redirectUrl)->toStartWith('https://foo.myshopify.com/admin/oauth/authorize?')
        ->and($redirectUrl)->toContain('client_id=client-id')
        ->and($redirectUrl)->toContain('scope=read_products')
        ->and(ShopifyOAuthState::query()->where('shop_domain', 'foo.myshopify.com')->exists())->toBeTrue();
});
