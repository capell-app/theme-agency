<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Console\Commands;

use Capell\ShopifyCommerce\Actions\OAuth\PruneExpiredShopifyOAuthStatesAction;
use Illuminate\Console\Command;

final class PruneExpiredShopifyOAuthStatesCommand extends Command
{
    protected $signature = 'capell-shopify-commerce:prune-oauth-states';

    protected $description = 'Prune expired Shopify OAuth state rows.';

    public function handle(): int
    {
        $deletedCount = PruneExpiredShopifyOAuthStatesAction::run();

        $this->components->info(trans_choice(
            'capell-shopify-commerce::capell-shopify-commerce.commands.oauth_state_prune.pruned',
            $deletedCount,
            ['count' => $deletedCount],
        ));

        return self::SUCCESS;
    }
}
