<?php

declare(strict_types=1);

namespace Capell\AgentDelivery\Health;

use Capell\AgentDelivery\Http\Controllers\AbstractAgentDeliveryController;
use Capell\AgentDelivery\Support\AgentDeliveryRegistry;
use Capell\AgentDelivery\Support\SiteDiscovery\AgentDeliveryGeneratedOutputCoverageSource;
use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\SiteDiscovery\Contracts\GeneratedOutputCoverageSource;
use Closure;
use Illuminate\Routing\Route as RoutingRoute;
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
            $check->jsonResponseHeaderCheck(),
            $check->chunkBudgetConfigurationCheck(),
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
        $missingRoutes = collect($this->publicRouteNames())
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

    public function chunkBudgetConfigurationCheck(): DoctorCheckResultData
    {
        $targetWords = config('capell-agent-delivery.public_pages.chunk_target_words', 160);
        $overlapWords = config('capell-agent-delivery.public_pages.chunk_overlap_words', 30);
        $maxRecommendedChunks = config('capell-agent-delivery.public_pages.chunk_max_recommended_chunks', 40);

        $passed = $this->isPositiveInteger($targetWords)
            && $this->isNonNegativeInteger($overlapWords)
            && $this->integerValue($overlapWords) < $this->integerValue($targetWords)
            && $this->isPositiveInteger($maxRecommendedChunks);

        return new DoctorCheckResultData(
            label: 'Agent Delivery chunk budget configuration',
            passed: $passed,
            message: $passed
                ? 'Agent Delivery chunk target, overlap, and recommended maximum chunk count are valid.'
                : 'Agent Delivery chunk budget configuration is invalid.',
            remediation: $passed
                ? null
                : 'Keep chunk_target_words above zero, chunk_overlap_words at zero or above but below the target, and chunk_max_recommended_chunks above zero.',
        );
    }

    public function jsonResponseHeaderCheck(): DoctorCheckResultData
    {
        $requiredHeaders = [
            AbstractAgentDeliveryController::HEADER_VERSION,
            AbstractAgentDeliveryController::HEADER_CACHE_TAGS,
            AbstractAgentDeliveryController::HEADER_CACHE_VARIATION,
            'Cache-Control',
            'ETag',
        ];
        $missingHeaders = collect($requiredHeaders)
            ->filter(static fn (string $header): bool => $header === '')
            ->values()
            ->all();
        $routesWithoutSharedController = collect($this->publicRouteNames())
            ->reject(fn (string $routeName): bool => $this->routeUsesAgentDeliveryController($routeName))
            ->values()
            ->all();
        $passed = $missingHeaders === [] && $routesWithoutSharedController === [];

        return new DoctorCheckResultData(
            label: 'Agent Delivery JSON response headers',
            passed: $passed,
            message: $passed
                ? 'Agent Delivery cacheable JSON responses declare version, cache, conditional request, and variation headers.'
                : 'Agent Delivery cacheable JSON response headers are incomplete.',
            remediation: $passed
                ? null
                : 'Keep cacheable Agent Delivery responses on AbstractAgentDeliveryController::cacheableJson so cache and variation headers stay consistent.',
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

    /**
     * @return list<string>
     */
    private function publicRouteNames(): array
    {
        return [
            'capell-agent-delivery.discovery',
            'capell-agent-delivery.pages.index',
            'capell-agent-delivery.pages.manifest',
            'capell-agent-delivery.pages.chunks',
        ];
    }

    private function routeUsesAgentDeliveryController(string $routeName): bool
    {
        $route = Route::getRoutes()->getByName($routeName);

        if (! $route instanceof RoutingRoute) {
            return false;
        }

        $controller = $route->getAction('controller');

        if (! is_string($controller) || $controller === '') {
            return false;
        }

        $controllerClass = explode('@', $controller, 2)[0];

        return is_a($controllerClass, AbstractAgentDeliveryController::class, true);
    }

    private function isPositiveInteger(mixed $value): bool
    {
        return is_int($value) ? $value > 0 : is_numeric($value) && $this->integerValue($value) > 0;
    }

    private function isNonNegativeInteger(mixed $value): bool
    {
        return is_int($value) ? $value >= 0 : is_numeric($value) && $this->integerValue($value) >= 0;
    }

    private function integerValue(mixed $value): int
    {
        return (int) (is_scalar($value) ? $value : 0);
    }
}
