<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Providers\Drivers;

final class LinkedInFeedProvider extends AbstractConfiguredProvider
{
    public function key(): string
    {
        return 'linkedin';
    }

    public function label(): string
    {
        return 'LinkedIn';
    }
}
