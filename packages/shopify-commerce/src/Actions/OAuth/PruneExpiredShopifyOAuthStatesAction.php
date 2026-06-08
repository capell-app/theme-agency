<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Actions\OAuth;

use Capell\ShopifyCommerce\Models\ShopifyOAuthState;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static int run()
 */
final class PruneExpiredShopifyOAuthStatesAction
{
    use AsAction;

    public function handle(): int
    {
        $deletedCount = ShopifyOAuthState::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->delete();

        return is_numeric($deletedCount) ? (int) $deletedCount : 0;
    }
}
