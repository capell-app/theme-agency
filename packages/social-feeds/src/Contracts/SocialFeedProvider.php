<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Contracts;

use Capell\SocialFeeds\Data\SocialFeedPostData;
use Capell\SocialFeeds\Enums\SocialAuthStrategy;
use Capell\SocialFeeds\Models\SocialFeedConnection;

interface SocialFeedProvider
{
    public function key(): string;

    public function label(): string;

    public function authStrategy(): SocialAuthStrategy;

    /**
     * @return array<string, mixed>
     */
    public function credentialSchema(): array;

    /**
     * @return array<int, SocialFeedPostData>
     */
    public function fetch(SocialFeedConnection $connection, int $limit): array;
}
