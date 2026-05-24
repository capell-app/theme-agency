<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Actions\OAuth;

use Capell\ShopifyCommerce\Models\ShopifyOAuthState;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

final class CreateShopifyOAuthStateAction
{
    use AsAction;

    public function handle(string $shopDomain, Authenticatable $user, ?int $siteId = null): string
    {
        $nonce = Str::random(40);
        $ttlSeconds = (int) config('capell-shopify-commerce.state_ttl_seconds', 600);

        ShopifyOAuthState::query()->create([
            'nonce' => $nonce,
            'shop_domain' => $shopDomain,
            'site_id' => $siteId,
            'user_id' => $user->getAuthIdentifier(),
            'expires_at' => now()->addSeconds(max(60, $ttlSeconds)),
        ]);

        return $nonce;
    }
}
