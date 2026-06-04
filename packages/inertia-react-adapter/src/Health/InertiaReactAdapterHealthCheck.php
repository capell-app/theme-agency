<?php

declare(strict_types=1);

namespace Capell\InertiaReactAdapter\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Inertia\Support\InertiaAdapterRegistry;
use Capell\InertiaReactAdapter\Providers\InertiaReactAdapterServiceProvider;

final class InertiaReactAdapterHealthCheck implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    public function passes(): bool
    {
        return app()->bound(InertiaAdapterRegistry::class)
            && resolve(InertiaAdapterRegistry::class)->get(InertiaReactAdapterServiceProvider::ADAPTER_KEY) !== null;
    }
}
