<?php

declare(strict_types=1);

use Capell\ShopifyCommerce\Actions\OAuth\BuildShopifyAuthorizeUrlAction;

it('builds the shopify authorize url', function (): void {
    config()->set('capell-shopify-commerce.client_id', 'client-id');

    $url = BuildShopifyAuthorizeUrlAction::run(
        shopDomain: 'foo.myshopify.com',
        scopes: ['read_products', 'read_inventory'],
        state: 'nonce-value',
        redirectUri: 'https://example.test/capell/oauth/shopify/callback',
    );

    expect($url)->toStartWith('https://foo.myshopify.com/admin/oauth/authorize?')
        ->and($url)->toContain('client_id=client-id')
        ->and($url)->toContain('scope=read_products%2Cread_inventory')
        ->and($url)->toContain('state=nonce-value')
        ->and($url)->toContain('redirect_uri=https%3A%2F%2Fexample.test%2Fcapell%2Foauth%2Fshopify%2Fcallback');
});
