<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Providers\Drivers;

final class BlueskyFeedProvider extends AbstractConfiguredProvider
{
    public function key(): string
    {
        return 'bluesky';
    }

    public function label(): string
    {
        return 'Bluesky';
    }
}
