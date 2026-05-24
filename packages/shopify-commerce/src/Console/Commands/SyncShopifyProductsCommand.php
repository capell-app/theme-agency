<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Console\Commands;

use Capell\ShopifyCommerce\Actions\Catalog\SyncShopifyProductsAction;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Illuminate\Console\Command;

final class SyncShopifyProductsCommand extends Command
{
    protected $signature = 'capell-shopify-commerce:sync {connection? : Shopify connection id}';

    protected $description = 'Sync Shopify products into the local catalog cache.';

    public function handle(): int
    {
        $connectionId = $this->argument('connection');
        $connection = is_numeric($connectionId)
            ? ShopifyConnection::query()->find((int) $connectionId)
            : ShopifyConnection::query()->where('status', 'active')->latest('id')->first();

        if (! $connection instanceof ShopifyConnection) {
            $this->error('No active Shopify connection was found.');

            return self::FAILURE;
        }

        $bulkOperationId = SyncShopifyProductsAction::run($connection);

        $this->info($bulkOperationId === null || $bulkOperationId === ''
            ? 'No Shopify sync was started.'
            : sprintf('Queued Shopify bulk product sync %s.', $bulkOperationId));

        return self::SUCCESS;
    }
}
