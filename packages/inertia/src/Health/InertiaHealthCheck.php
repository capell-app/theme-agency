<?php

declare(strict_types=1);

namespace Capell\Inertia\Health;

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Frontend\Support\Render\FrontendResponseRendererRegistry;
use Capell\Frontend\Support\Routing\FrontendRouteMiddlewareRegistry;
use Capell\Inertia\Actions\ResolveInertiaAdapterKeyAction;
use Capell\Inertia\Http\Middleware\HandleInertiaRequests;
use Capell\Inertia\Support\InertiaAdapterRegistry;
use Illuminate\Support\Collection;

final class InertiaHealthCheck implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    public function passes(): bool
    {
        return $this->runDiagnostics()->every(
            static fn (DoctorCheckResultData $result): bool => $result->passed,
        );
    }

    /**
     * @return Collection<int, DoctorCheckResultData>
     */
    public function runDiagnostics(): Collection
    {
        return collect([
            $this->rendererCheck(),
            $this->middlewareCheck(),
            $this->adapterReadinessCheck(),
        ]);
    }

    public function rendererRegistered(): bool
    {
        return app()->bound(FrontendResponseRendererRegistry::class)
            && resolve(FrontendResponseRendererRegistry::class)->has(FrontendRuntime::Inertia);
    }

    public function middlewareRegistered(): bool
    {
        return app()->bound(FrontendRouteMiddlewareRegistry::class)
            && in_array(HandleInertiaRequests::class, resolve(FrontendRouteMiddlewareRegistry::class)->all(), true);
    }

    public function adapterReadinessCheck(): DoctorCheckResultData
    {
        $configuredAdapter = ResolveInertiaAdapterKeyAction::run();
        $adapter = resolve(InertiaAdapterRegistry::class)->active();

        return new DoctorCheckResultData(
            label: 'Inertia adapter',
            passed: $adapter !== null,
            message: $adapter !== null
                ? sprintf(
                    'Configured Inertia adapter [%s] is registered by %s with build [%s/%s], %d component(s), and %d npm dependency(ies).',
                    $configuredAdapter,
                    $adapter->packageName,
                    $adapter->buildPath,
                    $adapter->entrypoint,
                    count($adapter->components),
                    count($adapter->npmDependencies),
                )
                : sprintf('Configured Inertia adapter [%s] is not registered.', $configuredAdapter),
            remediation: $adapter !== null
                ? null
                : 'Install and boot an Inertia adapter package, or set capell-inertia.adapter to a registered adapter key.',
        );
    }

    private function rendererCheck(): DoctorCheckResultData
    {
        $passed = $this->rendererRegistered();

        return new DoctorCheckResultData(
            label: 'Inertia renderer',
            passed: $passed,
            message: $passed
                ? 'The Inertia frontend renderer is registered.'
                : 'The Inertia frontend renderer is not registered.',
            remediation: $passed
                ? null
                : 'Ensure the Capell Inertia service provider is loaded.',
        );
    }

    private function middlewareCheck(): DoctorCheckResultData
    {
        $passed = $this->middlewareRegistered();

        return new DoctorCheckResultData(
            label: 'Inertia middleware',
            passed: $passed,
            message: $passed
                ? 'The Inertia frontend middleware is registered.'
                : 'The Inertia frontend middleware is not registered.',
            remediation: $passed
                ? null
                : 'Ensure the Capell Inertia service provider registers the frontend route middleware.',
        );
    }
}
