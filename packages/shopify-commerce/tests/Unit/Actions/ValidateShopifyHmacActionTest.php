<?php

declare(strict_types=1);

use Capell\ShopifyCommerce\Actions\OAuth\ValidateShopifyHmacAction;

it('validates shopify hmac signatures over sorted query values', function (): void {
    $secret = 'shopify-secret';
    $query = [
        'shop' => 'foo.myshopify.com',
        'timestamp' => '1700000000',
        'code' => 'oauth-code',
        'state' => 'nonce',
    ];

    ksort($query);
    $query['hmac'] = hash_hmac('sha256', http_build_query($query, '', '&', PHP_QUERY_RFC3986), $secret);

    expect(ValidateShopifyHmacAction::run($query, $secret))->toBeTrue();
});

it('rejects mutated hmac signatures', function (): void {
    $secret = 'shopify-secret';
    $query = [
        'shop' => 'foo.myshopify.com',
        'timestamp' => '1700000000',
        'code' => 'oauth-code',
        'state' => 'nonce',
        'hmac' => 'bad-signature',
    ];

    expect(ValidateShopifyHmacAction::run($query, $secret))->toBeFalse()
        ->and(ValidateShopifyHmacAction::run($query, ''))->toBeFalse();
});
