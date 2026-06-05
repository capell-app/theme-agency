<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Console\Commands;

use Capell\ShopifyCommerce\Actions\Customers\SyncShopifyCustomersAction;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Illuminate\Console\Command;

final class SyncShopifyCustomersCommand extends Command
{
    protected $signature = 'capell-shopify-commerce:sync-customers {connection? : Shopify connection id}';

    protected $description = 'Sync Shopify customers into the local customer cache.';

    public function handle(): int
    {
        $connectionId = $this->argument('connection');
        $connection = is_numeric($connectionId)
            ? ShopifyConnection::query()->find((int) $connectionId)
            : ShopifyConnection::query()->where('status', 'active')->latest('id')->first();

        if (! $connection instanceof ShopifyConnection) {
            $this->error(__('capell-shopify-commerce::capell-shopify-commerce.commands.customer_sync.no_connection'));

            return self::FAILURE;
        }

        $syncedCount = SyncShopifyCustomersAction::run($connection);

        $this->info(trans_choice(
            'capell-shopify-commerce::capell-shopify-commerce.commands.customer_sync.synced',
            $syncedCount,
            ['count' => $syncedCount],
        ));

        return self::SUCCESS;
    }
}
