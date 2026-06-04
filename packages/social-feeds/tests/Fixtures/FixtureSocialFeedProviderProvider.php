<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Tests\Fixtures;

use Capell\SocialFeeds\Contracts\SocialFeedProviderProvider;
use Capell\SocialFeeds\Support\SocialFeedProviderRegistry;

final class FixtureSocialFeedProviderProvider implements SocialFeedProviderProvider
{
    public function registerProviders(SocialFeedProviderRegistry $registry): void
    {
        $registry->register(new FixtureSocialFeedProvider);
    }
}
