<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Support;

use Capell\SocialFeeds\Contracts\SocialFeedProviderProvider;
use Capell\SocialFeeds\Providers\Drivers\BlueskyFeedProvider;
use Capell\SocialFeeds\Providers\Drivers\FacebookFeedProvider;
use Capell\SocialFeeds\Providers\Drivers\InstagramFeedProvider;
use Capell\SocialFeeds\Providers\Drivers\LinkedInFeedProvider;
use Capell\SocialFeeds\Providers\Drivers\RssFeedProvider;
use Capell\SocialFeeds\Providers\Drivers\TikTokFeedProvider;
use Capell\SocialFeeds\Providers\Drivers\XFeedProvider;
use Capell\SocialFeeds\Providers\Drivers\YouTubeFeedProvider;

final class DefaultSocialFeedProviderProvider implements SocialFeedProviderProvider
{
    public function registerProviders(SocialFeedProviderRegistry $registry): void
    {
        foreach ([
            new RssFeedProvider,
            new TikTokFeedProvider,
            new YouTubeFeedProvider,
            new BlueskyFeedProvider,
            new InstagramFeedProvider,
            new FacebookFeedProvider,
            new LinkedInFeedProvider,
            new XFeedProvider,
        ] as $provider) {
            $registry->register($provider);
        }
    }
}
