<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Tests\Fixtures;

use Capell\SocialFeeds\Contracts\SocialFeedHostResolver;

final readonly class StaticSocialFeedHostResolver implements SocialFeedHostResolver
{
    /**
     * @param  array<string, list<string>>  $addresses
     */
    public function __construct(private array $addresses) {}

    /**
     * @return list<string>
     */
    public function resolve(string $host): array
    {
        return $this->addresses[$host] ?? [];
    }
}
