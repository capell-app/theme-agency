<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Providers\Drivers;

final class InstagramFeedProvider extends AbstractConfiguredProvider
{
    public function key(): string
    {
        return 'instagram';
    }

    public function label(): string
    {
        return 'Instagram';
    }
}
