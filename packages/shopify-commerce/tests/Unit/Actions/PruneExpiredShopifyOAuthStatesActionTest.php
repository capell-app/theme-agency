<?php

declare(strict_types=1);

use Capell\ShopifyCommerce\Actions\OAuth\PruneExpiredShopifyOAuthStatesAction;
use Capell\ShopifyCommerce\Models\ShopifyOAuthState;

it('prunes expired shopify oauth state rows and leaves future rows', function (): void {
    ShopifyOAuthState::query()->create([
        'nonce' => 'expired-state',
        'shop_domain' => 'expired.myshopify.com',
        'user_id' => 1,
        'expires_at' => now()->subMinute(),
    ]);

    ShopifyOAuthState::query()->create([
        'nonce' => 'future-state',
        'shop_domain' => 'future.myshopify.com',
        'user_id' => 1,
        'expires_at' => now()->addMinute(),
    ]);

    ShopifyOAuthState::query()->create([
        'nonce' => 'unknown-expiry-state',
        'shop_domain' => 'unknown.myshopify.com',
        'user_id' => 1,
        'expires_at' => null,
    ]);

    expect(PruneExpiredShopifyOAuthStatesAction::run())->toBe(1)
        ->and(ShopifyOAuthState::query()->pluck('nonce')->all())->toContain('future-state', 'unknown-expiry-state')
        ->and(ShopifyOAuthState::query()->where('nonce', 'expired-state')->exists())->toBeFalse();
});
