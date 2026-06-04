<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Providers\Drivers;

final class YouTubeFeedProvider extends AbstractConfiguredProvider
{
    public function key(): string
    {
        return 'youtube';
    }

    public function label(): string
    {
        return 'YouTube';
    }
}
