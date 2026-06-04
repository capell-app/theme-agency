<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Providers\Drivers;

final class FacebookFeedProvider extends AbstractConfiguredProvider
{
    public function key(): string
    {
        return 'facebook';
    }

    public function label(): string
    {
        return 'Facebook';
    }
}
