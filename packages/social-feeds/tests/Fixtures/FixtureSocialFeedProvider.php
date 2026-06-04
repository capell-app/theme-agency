<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Tests\Fixtures;

use Capell\SocialFeeds\Contracts\SocialFeedProvider;
use Capell\SocialFeeds\Data\SocialFeedPostData;
use Capell\SocialFeeds\Enums\SocialAuthStrategy;
use Capell\SocialFeeds\Models\SocialFeedConnection;

final class FixtureSocialFeedProvider implements SocialFeedProvider
{
    public function key(): string
    {
        return 'fixture';
    }

    public function label(): string
    {
        return 'Fixture';
    }

    public function authStrategy(): SocialAuthStrategy
    {
        return SocialAuthStrategy::None;
    }

    /**
     * @return array<string, mixed>
     */
    public function credentialSchema(): array
    {
        return [];
    }

    /**
     * @return array<int, SocialFeedPostData>
     */
    public function fetch(SocialFeedConnection $connection, int $limit): array
    {
        return [];
    }
}
