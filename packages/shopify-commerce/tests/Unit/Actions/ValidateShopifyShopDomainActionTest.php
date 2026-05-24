<?php

declare(strict_types=1);

use Capell\ShopifyCommerce\Actions\OAuth\ValidateShopifyShopDomainAction;

it('accepts valid myshopify domains', function (): void {
    expect(ValidateShopifyShopDomainAction::run('foo.myshopify.com'))->toBeTrue()
        ->and(ValidateShopifyShopDomainAction::run('foo-bar1.myshopify.com'))->toBeTrue()
        ->and(ValidateShopifyShopDomainAction::run('Foo.myshopify.com'))->toBeTrue();
});

it('rejects non-shopify and unsafe domains', function (mixed $shop): void {
    expect(ValidateShopifyShopDomainAction::run($shop))->toBeFalse();
})->with([
    '',
    'foo.com',
    'evil.com.myshopify.com.attacker',
    'å.myshopify.com',
    '.myshopify.com',
]);

it('rejects non-string shop domains', function (): void {
    expect(ValidateShopifyShopDomainAction::run(['foo.myshopify.com']))->toBeFalse();
});
