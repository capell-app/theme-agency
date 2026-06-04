<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Providers\Drivers;

final class TikTokFeedProvider extends AbstractConfiguredProvider
{
    public function key(): string
    {
        return 'tiktok';
    }

    public function label(): string
    {
        return 'TikTok';
    }
}
