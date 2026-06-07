<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Actions\OAuth;

use Capell\ShopifyCommerce\Models\ShopifyOAuthState;
use Lorisleiva\Actions\Concerns\AsAction;

final class PruneExpiredShopifyOAuthStatesAction
{
    use AsAction;

    public function handle(): int
    {
        return ShopifyOAuthState::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->delete();
    }
}
