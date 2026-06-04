<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Providers\Drivers;

use Capell\SocialFeeds\Contracts\SocialFeedProvider;
use Capell\SocialFeeds\Data\SocialFeedPostData;
use Capell\SocialFeeds\Enums\SocialAuthStrategy;
use Capell\SocialFeeds\Models\SocialFeedConnection;

abstract class AbstractConfiguredProvider implements SocialFeedProvider
{
    public function authStrategy(): SocialAuthStrategy
    {
        return SocialAuthStrategy::ApiKey;
    }

    /**
     * @return array<string, mixed>
     */
    public function credentialSchema(): array
    {
        return [
            'api_key' => [
                'type' => 'password',
                'required' => false,
            ],
            'feed_url' => [
                'type' => 'url',
                'required' => false,
            ],
            'handle' => [
                'type' => 'text',
                'required' => false,
            ],
        ];
    }

    /**
     * @return array<int, SocialFeedPostData>
     */
    public function fetch(SocialFeedConnection $connection, int $limit): array
    {
        return [];
    }
}
