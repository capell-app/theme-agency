<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Providers\Drivers;

final class XFeedProvider extends AbstractConfiguredProvider
{
    public function key(): string
    {
        return 'x';
    }

    public function label(): string
    {
        return 'X';
    }
}
