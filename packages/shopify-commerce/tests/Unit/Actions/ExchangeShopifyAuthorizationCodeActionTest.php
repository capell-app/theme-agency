<?php

declare(strict_types=1);

use Capell\ShopifyCommerce\Actions\OAuth\ExchangeShopifyAuthorizationCodeAction;
use Capell\ShopifyCommerce\Data\ShopifyTokenExchangeResponseData;
use Capell\ShopifyCommerce\Exceptions\ShopifyOAuthException;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;

it('exchanges a shopify authorization code for token data', function (): void {
    config()->set('capell-shopify-commerce.client_id', 'client-id');
    config()->set('capell-shopify-commerce.client_secret', 'client-secret');
    config()->set('capell-shopify-commerce.http_timeout', 12);

    $timeouts = [];

    Http::fake(function (ClientRequest $request, array $options) use (&$timeouts): PromiseInterface {
        $timeouts[] = $options['timeout'] ?? null;

        return Http::response([
            'access_token' => 'admin-token',
            'scope' => 'read_products,read_inventory',
        ]);
    });

    $tokenData = ExchangeShopifyAuthorizationCodeAction::run('foo.myshopify.com', 'oauth-code');

    expect($tokenData)->toBeInstanceOf(ShopifyTokenExchangeResponseData::class)
        ->and($tokenData->accessToken)->toBe('admin-token')
        ->and($tokenData->scopes)->toBe(['read_products', 'read_inventory'])
        ->and($timeouts)->toBe([12]);
});

it('throws when token exchange fails or returns no token', function (): void {
    Http::fake([
        'foo.myshopify.com/admin/oauth/access_token' => Http::response([], 422),
    ]);

    expect(fn (): ShopifyTokenExchangeResponseData => ExchangeShopifyAuthorizationCodeAction::run('foo.myshopify.com', 'bad-code'))
        ->toThrow(ShopifyOAuthException::class);

    Http::fake([
        'foo.myshopify.com/admin/oauth/access_token' => Http::response(['scope' => 'read_products']),
    ]);

    expect(fn (): ShopifyTokenExchangeResponseData => ExchangeShopifyAuthorizationCodeAction::run('foo.myshopify.com', 'empty-token'))
        ->toThrow(ShopifyOAuthException::class);
});
