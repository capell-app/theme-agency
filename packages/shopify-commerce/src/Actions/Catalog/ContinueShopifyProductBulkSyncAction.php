<?php

declare(strict_types=1);

namespace Capell\ShopifyCommerce\Actions\Catalog;

use Capell\ShopifyCommerce\Models\ShopifyConnection;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Decorators\JobDecorator;

final class ContinueShopifyProductBulkSyncAction
{
    use AsAction;

    /**
     * @var list<string>
     */
    private const array PENDING_STATUSES = [
        'CREATED',
        'RUNNING',
        'CANCELING',
    ];

    public int $jobTries = 120;

    public function handle(ShopifyConnection|int $connection): string
    {
        return $this->continueSync($connection);
    }

    public function asJob(JobDecorator $job, ShopifyConnection|int $connection): string
    {
        $status = $this->continueSync($connection);

        if (in_array($status, self::PENDING_STATUSES, true)) {
            $job->release($this->pollDelaySeconds());
        }

        return $status;
    }

    /**
     * @return array<int, WithoutOverlapping>
     */
    public function getJobMiddleware(ShopifyConnection|int $connection): array
    {
        $connectionId = $connection instanceof ShopifyConnection ? (int) $connection->getKey() : $connection;

        return [
            (new WithoutOverlapping($this->lockKey($connectionId), $this->pollDelaySeconds()))->expireAfter(21_600),
        ];
    }

    private function continueSync(ShopifyConnection|int $connection): string
    {
        $status = PollShopifyProductBulkSyncAction::run($connection);

        if ($status === 'COMPLETED') {
            ImportShopifyProductBulkSyncAction::run($connection);
        }

        return $status;
    }

    private function pollDelaySeconds(): int
    {
        return max(1, (int) config('capell-shopify-commerce.bulk_sync_poll_delay_seconds', 15));
    }

    private function lockKey(int $connectionId): string
    {
        return sprintf('capell-shopify-commerce.sync.continue.%d', $connectionId);
    }
}
