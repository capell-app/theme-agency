<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Actions;

use Capell\SocialFeeds\Enums\SocialFeedConnectionStatus;
use Capell\SocialFeeds\Models\SocialFeedConnection;
use Capell\SocialFeeds\Support\SocialFeedProviderRegistry;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

final class SyncSocialFeedConnectionAction
{
    use AsAction;

    public function __construct(private readonly SocialFeedProviderRegistry $providers) {}

    public function handle(SocialFeedConnection|int $connection, ?int $limit = null): int
    {
        $connection = is_int($connection) ? SocialFeedConnection::query()->findOrFail($connection) : $connection;

        if (! $connection->isConnected()) {
            return 0;
        }

        $connection->forceFill([
            'sync_status' => 'running',
            'last_sync_started_at' => now(),
            'last_sync_error' => null,
        ])->save();

        try {
            $provider = $this->providers->getOrFail($connection->provider);
            $posts = $provider->fetch($connection, $limit ?? (int) config('capell-social-feeds.default_limit', 12));
            $count = UpsertSocialFeedItemsAction::run($connection, $posts);

            $connection->forceFill([
                'status' => SocialFeedConnectionStatus::Connected,
                'sync_status' => 'idle',
                'last_synced_at' => now(),
                'last_sync_error' => null,
            ])->save();

            return $count;
        } catch (Throwable $throwable) {
            $connection->forceFill([
                'status' => SocialFeedConnectionStatus::Error,
                'sync_status' => 'failed',
                'last_sync_error' => RedactSocialFeedSyncErrorAction::run($throwable, $connection),
            ])->save();

            report($throwable);

            return 0;
        }
    }

    /**
     * @return array<int, WithoutOverlapping>
     */
    public function getJobMiddleware(SocialFeedConnection|int $connection): array
    {
        $connectionId = $connection instanceof SocialFeedConnection ? (int) $connection->getKey() : $connection;

        return [
            (new WithoutOverlapping(sprintf('capell-social-feeds.sync.%d', $connectionId)))->expireAfter(3600),
        ];
    }
}
