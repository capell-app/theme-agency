<?php

declare(strict_types=1);

namespace Capell\PublicActions\Support;

use Capell\PublicActions\Contracts\PublicActionSpamProtectionAdapter;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

final class PublicActionSpamProtectionAdapterRegistry
{
    /** @var array<string, class-string<PublicActionSpamProtectionAdapter>|PublicActionSpamProtectionAdapter> */
    private array $adapters = [];

    public function __construct(
        private readonly Container $container,
    ) {}

    /**
     * @param  class-string<PublicActionSpamProtectionAdapter>|PublicActionSpamProtectionAdapter  $adapter
     */
    public function register(string $key, string|PublicActionSpamProtectionAdapter $adapter): void
    {
        $this->adapters[$key] = $adapter;
    }

    /**
     * @return list<PublicActionSpamProtectionAdapter>
     */
    public function enabled(): array
    {
        $enabled = config('capell-public-actions.spam_protection.enabled', ['honeypot']);

        if (! is_array($enabled)) {
            return [];
        }

        $adapters = [];

        foreach ($enabled as $key) {
            if (! is_string($key)) {
                continue;
            }

            if (trim($key) === '') {
                continue;
            }

            $adapters[] = $this->resolve($key);
        }

        return $adapters;
    }

    public function resolve(string $key): PublicActionSpamProtectionAdapter
    {
        $adapter = $this->adapters[$key] ?? null;

        if ($adapter instanceof PublicActionSpamProtectionAdapter) {
            return $adapter;
        }

        if (is_string($adapter)) {
            $resolved = $this->container->make($adapter);

            if ($resolved instanceof PublicActionSpamProtectionAdapter) {
                return $resolved;
            }
        }

        throw new InvalidArgumentException(sprintf('Public action spam protection adapter [%s] is not registered.', $key));
    }
}
