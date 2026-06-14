<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Actions;

use Capell\SocialFeeds\Enums\SocialFeedConnectionStatus;
use Capell\SocialFeeds\Models\SocialFeedConnection;
use Lorisleiva\Actions\Concerns\AsAction;

final class SyncSocialFeedConnectionsAction
{
    use AsAction;

    public function handle(?int $connectionId = null, ?int $limit = null): int
    {
        if ($connectionId !== null) {
            return SyncSocialFeedConnectionAction::run(
                SocialFeedConnection::query()->findOrFail($connectionId),
                $limit,
            );
        }

        return SocialFeedConnection::query()
            ->where('status', SocialFeedConnectionStatus::Connected)
            ->orderBy('id')
            ->get()
            ->sum(static fn (SocialFeedConnection $connection): int => SyncSocialFeedConnectionAction::run($connection, $limit));
    }
}
