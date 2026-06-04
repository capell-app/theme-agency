<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Contracts;

use Capell\SocialFeeds\Support\SocialFeedProviderRegistry;

interface SocialFeedProviderProvider
{
    public const string TAG = 'capell.social_feeds.provider_providers';

    public function registerProviders(SocialFeedProviderRegistry $registry): void;
}
