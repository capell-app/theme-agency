<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Contracts;

interface SocialFeedHostResolver
{
    /**
     * @return list<string>
     */
    public function resolve(string $host): array;
}
