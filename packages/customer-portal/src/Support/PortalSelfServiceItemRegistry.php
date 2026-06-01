<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Support;

use Capell\CustomerPortal\Contracts\PortalSelfServiceItemProvider;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

final class PortalSelfServiceItemRegistry
{
    /**
     * @var array<string, class-string<PortalSelfServiceItemProvider>|PortalSelfServiceItemProvider>
     */
    private array $providers = [];

    public function __construct(private readonly Container $container) {}

    /**
     * @param  class-string<PortalSelfServiceItemProvider>|PortalSelfServiceItemProvider  $provider
     */
    public function register(string $key, string|PortalSelfServiceItemProvider $provider): self
    {
        throw_if($key === '', InvalidArgumentException::class, 'Self-service item provider key cannot be empty.');

        $this->providers[$key] = $provider;

        return $this;
    }

    /**
     * @return list<PortalSelfServiceItemProvider>
     */
    public function providers(): array
    {
        $providers = [];

        foreach ($this->providers as $provider) {
            if ($provider instanceof PortalSelfServiceItemProvider) {
                $providers[] = $provider;

                continue;
            }

            $resolvedProvider = $this->container->make($provider);

            if (! $resolvedProvider instanceof PortalSelfServiceItemProvider) {
                throw new InvalidArgumentException(sprintf(
                    'Portal self-service provider [%s] must implement [%s].',
                    $provider,
                    PortalSelfServiceItemProvider::class,
                ));
            }

            $providers[] = $resolvedProvider;
        }

        return $providers;
    }
}
