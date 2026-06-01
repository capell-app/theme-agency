<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Support;

use Capell\CustomerPortal\Contracts\PortalPreferencesProvider;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

class PortalPreferencesProviderRegistry
{
    /**
     * @var array<string, class-string<PortalPreferencesProvider>|PortalPreferencesProvider>
     */
    private array $providers = [];

    public function __construct(private readonly Container $container) {}

    /**
     * @param  class-string<PortalPreferencesProvider>|PortalPreferencesProvider  $provider
     */
    public function register(string $key, string|PortalPreferencesProvider $provider): self
    {
        throw_if($key === '', InvalidArgumentException::class, 'Preferences provider key cannot be empty.');

        $this->providers[$key] = $provider;

        return $this;
    }

    /**
     * @return list<PortalPreferencesProvider>
     */
    public function providers(): array
    {
        $providers = [];

        foreach ($this->providers as $provider) {
            if ($provider instanceof PortalPreferencesProvider) {
                $providers[] = $provider;

                continue;
            }

            $resolvedProvider = $this->container->make($provider);

            if (! $resolvedProvider instanceof PortalPreferencesProvider) {
                throw new InvalidArgumentException(sprintf(
                    'Portal preferences provider [%s] must implement [%s].',
                    $provider,
                    PortalPreferencesProvider::class,
                ));
            }

            $providers[] = $resolvedProvider;
        }

        return $providers;
    }
}
