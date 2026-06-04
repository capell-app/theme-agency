<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Actions;

use Capell\SocialFeeds\Data\SocialFeedRenderData;
use Capell\SocialFeeds\Data\SocialFeedRenderItemData;
use Capell\SocialFeeds\Data\SocialFeedWidgetConfigData;
use Capell\SocialFeeds\Models\SocialFeedItem;
use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\Actions\Concerns\AsAction;

final class FetchSocialFeedRenderDataAction
{
    use AsAction;

    public function handle(SocialFeedWidgetConfigData $config): SocialFeedRenderData
    {
        $items = SocialFeedItem::query()
            ->select([
                'provider',
                'type',
                'text',
                'permalink',
                'media_url',
                'thumbnail_url',
                'author_name',
                'author_avatar_url',
                'published_at',
            ])
            ->when($config->connectionId !== null, static fn (Builder $query): Builder => $query->where('connection_id', $config->connectionId))
            ->when($config->provider !== null, static fn (Builder $query): Builder => $query->where('provider', $config->provider))
            ->latest('published_at')
            ->latest('id')
            ->limit($config->limit)
            ->get()
            ->map(static fn (SocialFeedItem $item): SocialFeedRenderItemData => new SocialFeedRenderItemData(
                provider: $item->provider,
                type: $item->type,
                text: $item->text,
                permalink: $item->permalink,
                mediaUrl: $item->media_url,
                thumbnailUrl: $item->thumbnail_url,
                authorName: $item->author_name,
                authorAvatarUrl: $item->author_avatar_url,
                publishedAt: $item->published_at,
            ))
            ->all();

        return new SocialFeedRenderData($config, $items);
    }
}
