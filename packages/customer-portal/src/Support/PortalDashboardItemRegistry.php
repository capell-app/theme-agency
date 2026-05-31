<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Support;

use Capell\CustomerPortal\Contracts\PortalDashboardItemProvider;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

class PortalDashboardItemRegistry
{
    /**
     * @var array<string, class-string<PortalDashboardItemProvider>|PortalDashboardItemProvider>
     */
    private array $providers = [];

    public function __construct(private readonly Container $container) {}

    /**
     * @param  class-string<PortalDashboardItemProvider>|PortalDashboardItemProvider  $provider
     */
    public function register(string $key, string|PortalDashboardItemProvider $provider): self
    {
        throw_if($key === '', InvalidArgumentException::class, 'Dashboard item provider key cannot be empty.');

        $this->providers[$key] = $provider;

        return $this;
    }

    /**
     * @return list<PortalDashboardItemProvider>
     */
    public function providers(): array
    {
        $providers = [];

        foreach ($this->providers as $provider) {
            if ($provider instanceof PortalDashboardItemProvider) {
                $providers[] = $provider;

                continue;
            }

            $resolvedProvider = $this->container->make($provider);

            if (! $resolvedProvider instanceof PortalDashboardItemProvider) {
                throw new InvalidArgumentException(sprintf(
                    'Portal dashboard provider [%s] must implement [%s].',
                    $provider,
                    PortalDashboardItemProvider::class,
                ));
            }

            $providers[] = $resolvedProvider;
        }

        return $providers;
    }
}
