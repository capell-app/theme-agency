<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Console\Commands;

use Capell\ShopifyCommerce\Actions\Catalog\SyncShopifyProductsAction;
use Capell\ShopifyCommerce\Enums\ShopifyConnectionStatus;
use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

final class SyncShopifyProductsCommand extends Command
{
    protected $signature = 'capell-shopify-commerce:sync
        {connection? : Shopify connection id}
        {--all : Sync every active Shopify connection}
        {--site= : Restrict --all to one site id}';

    protected $description = 'Sync Shopify products into the local catalog cache.';

    public function handle(): int
    {
        $connectionId = $this->argument('connection');

        if ($this->option('all') === true) {
            return $this->syncAllConnections();
        }

        $connection = is_numeric($connectionId)
            ? ShopifyConnection::query()->find((int) $connectionId)
            : $this->activeConnectionsQuery()->latest('id')->first();

        if (! $connection instanceof ShopifyConnection) {
            $this->error('No active Shopify connection was found.');

            return self::FAILURE;
        }

        $this->syncConnection($connection);

        return self::SUCCESS;
    }

    private function syncAllConnections(): int
    {
        $startedCount = 0;

        $this->activeConnectionsQuery()
            ->orderBy('id')
            ->each(function (ShopifyConnection $connection) use (&$startedCount): void {
                if ($this->syncConnection($connection)) {
                    $startedCount++;
                }
            });

        if ($startedCount === 0) {
            $this->warn('No Shopify syncs were started.');
        }

        return self::SUCCESS;
    }

    private function syncConnection(ShopifyConnection $connection): bool
    {
        $bulkOperationId = SyncShopifyProductsAction::run($connection);

        $this->info($bulkOperationId === null || $bulkOperationId === ''
            ? 'No Shopify sync was started.'
            : sprintf('Queued Shopify bulk product sync %s.', $bulkOperationId));

        return is_string($bulkOperationId) && $bulkOperationId !== '';
    }

    /**
     * @return Builder<ShopifyConnection>
     */
    private function activeConnectionsQuery(): Builder
    {
        return ShopifyConnection::query()
            ->where('status', ShopifyConnectionStatus::Active->value)
            ->when(is_numeric($this->option('site')), fn (Builder $query): Builder => $query->where('site_id', (int) $this->option('site')));
    }
}
