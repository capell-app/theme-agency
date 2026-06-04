<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Providers\Drivers;

use Capell\SocialFeeds\Contracts\SocialFeedProvider;
use Capell\SocialFeeds\Data\SocialFeedPostData;
use Capell\SocialFeeds\Enums\SocialAuthStrategy;
use Capell\SocialFeeds\Models\SocialFeedConnection;
use Illuminate\Support\Arr;
use InvalidArgumentException;

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
        $feedUrl = Arr::get($connection->credentials ?? [], 'feed_url');

        if (! is_string($feedUrl) || $feedUrl === '') {
            throw new InvalidArgumentException(sprintf('Social feed provider [%s] requires a feed_url credential until its native API bridge is configured.', $this->key()));
        }

        return (new RssFeedProvider)->fetch($connection, $limit);
    }
}
