<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Support;

use Capell\CustomerPortal\Contracts\PortalProfileProvider;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

class PortalProfileProviderRegistry
{
    /**
     * @var array<string, class-string<PortalProfileProvider>|PortalProfileProvider>
     */
    private array $providers = [];

    public function __construct(private readonly Container $container) {}

    /**
     * @param  class-string<PortalProfileProvider>|PortalProfileProvider  $provider
     */
    public function register(string $key, string|PortalProfileProvider $provider): self
    {
        throw_if($key === '', InvalidArgumentException::class, 'Profile provider key cannot be empty.');

        $this->providers[$key] = $provider;

        return $this;
    }

    /**
     * @return list<PortalProfileProvider>
     */
    public function providers(): array
    {
        $providers = [];

        foreach ($this->providers as $provider) {
            if ($provider instanceof PortalProfileProvider) {
                $providers[] = $provider;

                continue;
            }

            $resolvedProvider = $this->container->make($provider);

            if (! $resolvedProvider instanceof PortalProfileProvider) {
                throw new InvalidArgumentException(sprintf(
                    'Portal profile provider [%s] must implement [%s].',
                    $provider,
                    PortalProfileProvider::class,
                ));
            }

            $providers[] = $resolvedProvider;
        }

        return $providers;
    }
}
