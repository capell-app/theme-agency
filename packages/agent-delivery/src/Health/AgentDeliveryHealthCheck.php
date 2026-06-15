<?php

declare(strict_types=1);

namespace Capell\AgentDelivery\Health;

use Capell\AgentDelivery\Support\AgentDeliveryRegistry;
use Capell\AgentDelivery\Support\SiteDiscovery\AgentDeliveryGeneratedOutputCoverageSource;
use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\SiteDiscovery\Contracts\GeneratedOutputCoverageSource;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

final class AgentDeliveryHealthCheck implements ChecksExtensionHealth
{
    public static function compatibleCapellApiVersion(): string
    {
        return '^4.0';
    }

    /**
     * @return Collection<int, DoctorCheckResultData>
     */
    public static function runDiagnostics(): Collection
    {
        $check = new self;

        return collect([
            $check->routeRegistrationCheck(),
            $check->rateLimiterCheck(),
            $check->registryBindingCheck(),
            $check->siteDiscoveryCoverageCheck(),
        ]);
    }

    public static function passed(): bool
    {
        return self::runDiagnostics()
            ->every(static fn (DoctorCheckResultData $result): bool => $result->passed);
    }

    public function routeRegistrationCheck(): DoctorCheckResultData
    {
        $missingRoutes = collect([
            'capell-agent-delivery.pages.index',
            'capell-agent-delivery.pages.manifest',
            'capell-agent-delivery.pages.chunks',
        ])
            ->reject(fn (string $routeName): bool => Route::has($routeName))
            ->values()
            ->all();

        return new DoctorCheckResultData(
            label: 'Agent Delivery public routes',
            passed: $missingRoutes === [],
            message: $missingRoutes === []
                ? 'All Agent Delivery public JSON routes are registered.'
                : 'Missing routes: ' . implode(', ', $missingRoutes) . '.',
            remediation: $missingRoutes === []
                ? null
                : 'Ensure AgentDeliveryServiceProvider loads the package route file.',
        );
    }

    public function rateLimiterCheck(): DoctorCheckResultData
    {
        $limiter = RateLimiter::limiter('capell-agent-delivery');
        $manifestRoute = Route::getRoutes()->getByName('capell-agent-delivery.pages.manifest');
        $middleware = $manifestRoute?->gatherMiddleware() ?? [];
        $hasMiddleware = in_array('throttle:capell-agent-delivery', $middleware, true);
        $passed = $limiter instanceof Closure && $hasMiddleware;

        return new DoctorCheckResultData(
            label: 'Agent Delivery rate limiting',
            passed: $passed,
            message: $passed
                ? 'The Agent Delivery rate limiter is registered and applied to public endpoints.'
                : 'The Agent Delivery rate limiter is missing or not applied to public endpoints.',
            remediation: $passed
                ? null
                : 'Keep capell-agent-delivery.public_pages.rate_limit_middleware set to throttle:capell-agent-delivery unless another public limiter is configured.',
        );
    }

    public function registryBindingCheck(): DoctorCheckResultData
    {
        $bound = app()->bound(AgentDeliveryRegistry::class);

        return new DoctorCheckResultData(
            label: 'Agent Delivery contributor registry',
            passed: $bound,
            message: $bound
                ? 'The Agent Delivery contributor registry is bound.'
                : 'The Agent Delivery contributor registry is not bound.',
            remediation: $bound
                ? null
                : 'Ensure AgentDeliveryServiceProvider registers AgentDeliveryRegistry as a singleton.',
        );
    }

    public function siteDiscoveryCoverageCheck(): DoctorCheckResultData
    {
        if (! interface_exists(GeneratedOutputCoverageSource::class)) {
            return new DoctorCheckResultData(
                label: 'Agent Delivery Site Discovery coverage',
                passed: true,
                message: 'Site Discovery is not installed; coverage source registration is optional.',
            );
        }

        $registered = collect(app()->tagged(GeneratedOutputCoverageSource::TAG))
            ->contains(fn (mixed $source): bool => $source instanceof AgentDeliveryGeneratedOutputCoverageSource);

        return new DoctorCheckResultData(
            label: 'Agent Delivery Site Discovery coverage',
            passed: $registered,
            message: $registered
                ? 'Agent Delivery generated output coverage is registered with Site Discovery.'
                : 'Agent Delivery generated output coverage is not registered with Site Discovery.',
            remediation: $registered
                ? null
                : 'Ensure AgentDeliveryServiceProvider tags AgentDeliveryGeneratedOutputCoverageSource when Site Discovery is installed.',
        );
    }
}
