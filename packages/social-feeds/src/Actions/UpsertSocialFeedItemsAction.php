<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Actions;

use Capell\SocialFeeds\Data\SocialFeedPostData;
use Capell\SocialFeeds\Models\SocialFeedConnection;
use Capell\SocialFeeds\Models\SocialFeedItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

final class UpsertSocialFeedItemsAction
{
    use AsAction;

    /**
     * @param  array<int, SocialFeedPostData>  $posts
     */
    public function handle(SocialFeedConnection $connection, array $posts): int
    {
        $now = now();
        $rows = array_map(
            static fn (SocialFeedPostData $post): array => [
                'connection_id' => $connection->getKey(),
                'provider' => $connection->provider,
                'external_id' => $post->externalId,
                'type' => $post->type->value,
                'text' => $post->text,
                'permalink' => $post->permalink,
                'media_url' => $post->mediaUrl,
                'thumbnail_url' => $post->thumbnailUrl,
                'author_name' => $post->authorName,
                'author_avatar_url' => $post->authorAvatarUrl,
                'raw' => $post->raw === [] ? null : json_encode($post->raw, JSON_THROW_ON_ERROR),
                'published_at' => $post->publishedAt,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            $posts,
        );

        if ($rows === []) {
            return 0;
        }

        SocialFeedItem::query()->upsert(
            $rows,
            ['connection_id', 'external_id'],
            ['type', 'text', 'permalink', 'media_url', 'thumbnail_url', 'author_name', 'author_avatar_url', 'raw', 'published_at', 'updated_at'],
        );

        $this->prune($connection);

        return count($rows);
    }

    private function prune(SocialFeedConnection $connection): void
    {
        $retentionItems = max(1, (int) config('capell-social-feeds.retention_items', 200));

        /** @var Collection<int, int> $keptIds */
        $keptIds = $connection->items()
            ->latest('published_at')
            ->latest('id')
            ->limit($retentionItems)
            ->pluck('id');

        $connection->items()
            ->when($keptIds->isNotEmpty(), static fn (Builder $query): Builder => $query->whereNotIn('id', $keptIds->all()))
            ->delete();
    }
}
