<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Support;

use Capell\SocialFeeds\Contracts\SocialFeedProvider;
use InvalidArgumentException;

final class SocialFeedProviderRegistry
{
    /**
     * @var array<string, SocialFeedProvider>
     */
    private array $providers = [];

    public function register(SocialFeedProvider $provider): void
    {
        $key = $provider->key();

        if (isset($this->providers[$key])) {
            throw new InvalidArgumentException(sprintf('Social feed provider [%s] is already registered.', $key));
        }

        $this->providers[$key] = $provider;
    }

    /**
     * @return array<string, SocialFeedProvider>
     */
    public function all(): array
    {
        return $this->providers;
    }

    public function get(string $key): ?SocialFeedProvider
    {
        return $this->providers[$key] ?? null;
    }

    public function getOrFail(string $key): SocialFeedProvider
    {
        return $this->get($key)
            ?? throw new InvalidArgumentException(sprintf('Social feed provider [%s] is not registered.', $key));
    }

    public function has(string $key): bool
    {
        return isset($this->providers[$key]);
    }
}
