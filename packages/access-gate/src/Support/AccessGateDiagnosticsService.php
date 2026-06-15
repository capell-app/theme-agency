<?php

declare(strict_types=1);

namespace Capell\AccessGate\Support;

use Capell\AccessGate\Contracts\AccessRequestMethod;
use Capell\AccessGate\Contracts\RegistrationField;
use Capell\AccessGate\Http\Middleware\AccessGateMiddleware;
use Capell\AccessGate\Models\Area;
use Capell\AccessGate\Providers\AccessGateServiceProvider;
use Capell\AccessGate\Support\CustomerPortal\AccessGatePortalSelfServiceItemProvider;
use Capell\AccessGate\Support\Payments\AccessGatePaymentFulfillmentHandler;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Core\Facades\CapellCore;
use Capell\CustomerPortal\Contracts\PortalSelfServiceItemProvider;
use Capell\CustomerPortal\Support\PortalSelfServiceItemRegistry;
use Capell\Frontend\Enums\RenderHookLocation;
use Capell\Frontend\Support\Render\RenderHookRegistry;
use Capell\Payments\Contracts\PaymentFulfillmentHandler;
use Illuminate\Database\Schema\Builder;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class AccessGateDiagnosticsService
{
    public function checkDatabase(): DoctorCheckResultData
    {
        $connectionName = $this->connectionName();

        try {
            DB::connection($connectionName)->getPdo();
        } catch (Throwable $throwable) {
            return new DoctorCheckResultData(
                label: 'Access Gate database',
                passed: false,
                message: __('capell-access-gate::doctor.database.unreachable', ['connection' => $connectionName]),
                remediation: $throwable->getMessage(),
            );
        }

        $missingTables = collect([
            'access_gate_areas',
            'access_gate_registrations',
            'access_gate_grants',
            'access_gate_claim_tokens',
            'access_gate_browser_tokens',
            'access_gate_events',
        ])->reject(fn (string $table): bool => Schema::connection($connectionName)->hasTable($table));

        if ($missingTables->isNotEmpty()) {
            return new DoctorCheckResultData(
                label: 'Access Gate database',
                passed: false,
                message: __('capell-access-gate::doctor.database.missing_tables', [
                    'tables' => $missingTables->implode(', '),
                ]),
            );
        }

        return new DoctorCheckResultData(
            label: 'Access Gate database',
            passed: true,
            message: __('capell-access-gate::doctor.database.ok', ['connection' => $connectionName]),
        );
    }

    public function checkMiddleware(Router $router): DoctorCheckResultData
    {
        if (! array_key_exists('access-gate', $router->getMiddleware())) {
            return new DoctorCheckResultData(
                label: 'Access Gate middleware',
                passed: false,
                message: __('capell-access-gate::doctor.middleware.alias_missing'),
            );
        }

        $webMiddleware = $router->getMiddlewareGroups()['web'] ?? [];
        $accessGatePosition = $this->firstMiddlewarePosition($webMiddleware, ['access-gate']);
        $pageCachePosition = $this->firstMiddlewarePosition($webMiddleware, $this->pageCacheAliases());

        if ($pageCachePosition !== null && $this->priorityRunsAccessGateBeforePageCache($router)) {
            return new DoctorCheckResultData(
                label: 'Access Gate middleware',
                passed: true,
                message: __('capell-access-gate::doctor.middleware.ok'),
            );
        }

        if ($pageCachePosition !== null && $accessGatePosition !== null && $accessGatePosition > $pageCachePosition) {
            return new DoctorCheckResultData(
                label: 'Access Gate middleware',
                passed: false,
                message: __('capell-access-gate::doctor.middleware.page_cache_before_gate'),
            );
        }

        if ($pageCachePosition !== null && $accessGatePosition === null) {
            return new DoctorCheckResultData(
                label: 'Access Gate middleware',
                passed: false,
                message: __('capell-access-gate::doctor.middleware.route_level_required'),
            );
        }

        return new DoctorCheckResultData(
            label: 'Access Gate middleware',
            passed: true,
            message: __('capell-access-gate::doctor.middleware.ok'),
        );
    }

    public function checkCookies(): DoctorCheckResultData
    {
        $sameSite = strtolower($this->configString('access-gate.cookies.browser_token.same_site', 'lax'));
        $secure = config('access-gate.cookies.browser_token.secure');

        if (! in_array($sameSite, ['lax', 'strict', 'none'], true)) {
            return new DoctorCheckResultData(
                label: 'Access Gate cookies',
                passed: false,
                message: __('capell-access-gate::doctor.cookies.invalid_same_site'),
            );
        }

        if ($sameSite === 'none' && $secure !== true) {
            return new DoctorCheckResultData(
                label: 'Access Gate cookies',
                passed: false,
                message: __('capell-access-gate::doctor.cookies.none_requires_secure'),
            );
        }

        if (app()->environment('production') && $secure !== true) {
            return new DoctorCheckResultData(
                label: 'Access Gate cookies',
                passed: true,
                message: __('capell-access-gate::doctor.cookies.production_secure'),
            );
        }

        return new DoctorCheckResultData(
            label: 'Access Gate cookies',
            passed: true,
            message: __('capell-access-gate::doctor.cookies.ok'),
        );
    }

    public function checkClaimHosts(): DoctorCheckResultData
    {
        $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);

        if (! is_string($appHost) || $appHost === '') {
            return new DoctorCheckResultData(
                label: 'Access Gate claim hosts',
                passed: true,
                message: __('capell-access-gate::doctor.claim_hosts.app_url_missing'),
            );
        }

        $areasWithMissingHost = Area::query()
            ->get()
            ->filter(function (Area $area) use ($appHost): bool {
                $claimHosts = $area->claim_url_hosts ?? [];

                return $claimHosts !== [] && ! in_array($appHost, $claimHosts, true);
            });

        if ($areasWithMissingHost->isNotEmpty()) {
            return new DoctorCheckResultData(
                label: 'Access Gate claim hosts',
                passed: true,
                message: __('capell-access-gate::doctor.claim_hosts.app_host_not_listed', [
                    'areas' => $areasWithMissingHost->pluck('key')->implode(', '),
                ]),
            );
        }

        return new DoctorCheckResultData(
            label: 'Access Gate claim hosts',
            passed: true,
            message: __('capell-access-gate::doctor.claim_hosts.ok'),
        );
    }

    public function checkSiteScopedAreas(): DoctorCheckResultData
    {
        $schema = $this->schema();

        if (! $schema->hasTable((new Area)->getTable()) || ! $schema->hasColumn((new Area)->getTable(), 'site_id')) {
            return new DoctorCheckResultData(
                label: 'Access Gate site-scoped areas',
                passed: true,
                message: __('capell-access-gate::doctor.site_scoped_areas.not_enabled'),
            );
        }

        if (! $schema->hasTable('sites')) {
            return new DoctorCheckResultData(
                label: 'Access Gate site-scoped areas',
                passed: true,
                message: __('capell-access-gate::doctor.site_scoped_areas.sites_missing'),
            );
        }

        $siteIds = DB::connection($this->connectionName())
            ->table('sites')
            ->pluck('id')
            ->map(fn (mixed $siteId): int => (int) $siteId)
            ->all();

        if ($siteIds === []) {
            return new DoctorCheckResultData(
                label: 'Access Gate site-scoped areas',
                passed: true,
                message: __('capell-access-gate::doctor.site_scoped_areas.no_sites'),
            );
        }

        $missingAreaKeys = Area::query()
            ->select('key')
            ->whereNotNull('site_id')
            ->distinct()
            ->pluck('key')
            ->filter(function (string $key) use ($siteIds): bool {
                if (Area::query()->where('key', $key)->whereNull('site_id')->exists()) {
                    return false;
                }

                $configuredSiteIds = Area::query()
                    ->where('key', $key)
                    ->whereNotNull('site_id')
                    ->pluck('site_id')
                    ->map(fn (mixed $siteId): int => (int) $siteId)
                    ->all();

                return array_diff($siteIds, $configuredSiteIds) !== [];
            });

        if ($missingAreaKeys->isNotEmpty()) {
            return new DoctorCheckResultData(
                label: 'Access Gate site-scoped areas',
                passed: false,
                message: __('capell-access-gate::doctor.site_scoped_areas.missing_site_config', [
                    'areas' => $missingAreaKeys->implode(', '),
                ]),
                remediation: __('capell-access-gate::doctor.site_scoped_areas.missing_site_config_remediation'),
            );
        }

        return new DoctorCheckResultData(
            label: 'Access Gate site-scoped areas',
            passed: true,
            message: __('capell-access-gate::doctor.site_scoped_areas.ok'),
        );
    }

    public function checkRegistrationConfiguration(): DoctorCheckResultData
    {
        $invalidMethods = $this->invalidConfiguredClasses(
            'access-gate.registration.identity_methods',
            AccessRequestMethod::class,
        );
        $invalidFields = $this->invalidConfiguredClasses(
            'access-gate.registration.fields',
            RegistrationField::class,
        );

        if ($invalidMethods !== [] || $invalidFields !== []) {
            return new DoctorCheckResultData(
                label: 'Access Gate registration configuration',
                passed: false,
                message: __('capell-access-gate::doctor.registration_configuration.invalid', [
                    'methods' => $invalidMethods === [] ? __('capell-access-gate::doctor.none') : implode(', ', $invalidMethods),
                    'fields' => $invalidFields === [] ? __('capell-access-gate::doctor.none') : implode(', ', $invalidFields),
                ]),
                remediation: __('capell-access-gate::doctor.registration_configuration.invalid_remediation'),
            );
        }

        $methodKeys = array_keys(resolve(AccessRequestMethodRegistry::class)->all());
        $fieldKeys = array_keys(resolve(RegistrationFieldRegistry::class)->all());

        return new DoctorCheckResultData(
            label: 'Access Gate registration configuration',
            passed: true,
            message: __('capell-access-gate::doctor.registration_configuration.ok', [
                'methods' => $methodKeys === [] ? __('capell-access-gate::doctor.none') : implode(', ', $methodKeys),
                'fields' => $fieldKeys === [] ? __('capell-access-gate::doctor.none') : implode(', ', $fieldKeys),
            ]),
        );
    }

    public function checkRouteThrottles(Router $router): DoctorCheckResultData
    {
        $missing = collect([
            'access-gate-request' => $this->hasRateLimiter('access-gate-request')
                && $this->routeUsesMiddleware($router, 'capell-access-gate.request.store', 'throttle:access-gate-request'),
            'access-gate-logout' => $this->hasRateLimiter('access-gate-logout')
                && $this->routeUsesMiddleware($router, 'capell-access-gate.logout', 'throttle:access-gate-logout'),
        ])
            ->filter(static fn (bool $configured): bool => ! $configured)
            ->keys()
            ->all();

        if ($missing !== []) {
            return new DoctorCheckResultData(
                label: 'Access Gate route throttles',
                passed: false,
                message: __('capell-access-gate::doctor.route_throttles.missing', [
                    'limiters' => implode(', ', $missing),
                ]),
                remediation: __('capell-access-gate::doctor.route_throttles.missing_remediation'),
            );
        }

        return new DoctorCheckResultData(
            label: 'Access Gate route throttles',
            passed: true,
            message: __('capell-access-gate::doctor.route_throttles.ok'),
        );
    }

    public function checkPaymentFulfillmentHandler(): DoctorCheckResultData
    {
        if (! interface_exists(PaymentFulfillmentHandler::class) || ! $this->accessGatePackageInstalled()) {
            return new DoctorCheckResultData(
                label: 'Access Gate payment fulfillment',
                passed: true,
                message: __('capell-access-gate::doctor.payment_fulfillment.skipped'),
            );
        }

        $registered = collect(app()->tagged(PaymentFulfillmentHandler::TAG))
            ->contains(static fn (mixed $handler): bool => $handler instanceof AccessGatePaymentFulfillmentHandler);

        if (! $registered) {
            return new DoctorCheckResultData(
                label: 'Access Gate payment fulfillment',
                passed: false,
                message: __('capell-access-gate::doctor.payment_fulfillment.missing'),
                remediation: __('capell-access-gate::doctor.payment_fulfillment.missing_remediation'),
            );
        }

        return new DoctorCheckResultData(
            label: 'Access Gate payment fulfillment',
            passed: true,
            message: __('capell-access-gate::doctor.payment_fulfillment.ok'),
        );
    }

    public function checkCustomerPortalProvider(): DoctorCheckResultData
    {
        if (! class_exists(PortalSelfServiceItemRegistry::class)
            || ! interface_exists(PortalSelfServiceItemProvider::class)
            || ! app()->bound(PortalSelfServiceItemRegistry::class)
            || ! $this->accessGatePackageInstalled()) {
            return new DoctorCheckResultData(
                label: 'Access Gate customer portal',
                passed: true,
                message: __('capell-access-gate::doctor.customer_portal.skipped'),
            );
        }

        $registered = collect(app(PortalSelfServiceItemRegistry::class)->providers())
            ->contains(static fn (PortalSelfServiceItemProvider $provider): bool => $provider instanceof AccessGatePortalSelfServiceItemProvider);

        if (! $registered) {
            return new DoctorCheckResultData(
                label: 'Access Gate customer portal',
                passed: false,
                message: __('capell-access-gate::doctor.customer_portal.missing'),
                remediation: __('capell-access-gate::doctor.customer_portal.missing_remediation'),
            );
        }

        return new DoctorCheckResultData(
            label: 'Access Gate customer portal',
            passed: true,
            message: __('capell-access-gate::doctor.customer_portal.ok'),
        );
    }

    public function checkAnnouncementHookCacheSafety(): DoctorCheckResultData
    {
        if (! class_exists(RenderHookRegistry::class) || ! app()->bound(RenderHookRegistry::class) || ! $this->accessGatePackageInstalled()) {
            return new DoctorCheckResultData(
                label: 'Access Gate announcement hook cache safety',
                passed: true,
                message: __('capell-access-gate::doctor.announcement_hook.skipped'),
            );
        }

        $bodyStartContributions = app(RenderHookRegistry::class)->contributions()[RenderHookLocation::BodyStart->value] ?? [];
        $registeredCacheSafeHook = collect($bodyStartContributions)
            ->contains(static fn (array $contribution): bool => ($contribution['owner'] ?? null) === AccessGateServiceProvider::$packageName
                && ($contribution['key'] ?? null) === 'announcement-bar'
                && ($contribution['cacheSafe'] ?? false) === true);

        if (! $registeredCacheSafeHook) {
            return new DoctorCheckResultData(
                label: 'Access Gate announcement hook cache safety',
                passed: false,
                message: __('capell-access-gate::doctor.announcement_hook.missing'),
                remediation: __('capell-access-gate::doctor.announcement_hook.missing_remediation'),
            );
        }

        return new DoctorCheckResultData(
            label: 'Access Gate announcement hook cache safety',
            passed: true,
            message: __('capell-access-gate::doctor.announcement_hook.ok'),
        );
    }

    /**
     * @return Collection<int, DoctorCheckResultData>
     */
    public function runAllChecks(Router $router): Collection
    {
        return collect([
            $this->checkDatabase(),
            $this->checkMiddleware($router),
            $this->checkCookies(),
            $this->checkClaimHosts(),
            $this->checkSiteScopedAreas(),
            $this->checkRegistrationConfiguration(),
            $this->checkRouteThrottles($router),
            $this->checkPaymentFulfillmentHandler(),
            $this->checkCustomerPortalProvider(),
            $this->checkAnnouncementHookCacheSafety(),
        ]);
    }

    public function allChecksPassed(Router $router): bool
    {
        return $this->runAllChecks($router)
            ->every(fn (DoctorCheckResultData $check): bool => $check->passed);
    }

    /**
     * @param  array<array-key, mixed>  $middleware
     * @param  list<string>  $aliases
     */
    public function firstMiddlewarePosition(array $middleware, array $aliases): ?int
    {
        foreach ($middleware as $position => $middlewareName) {
            if (! is_string($middlewareName)) {
                continue;
            }

            $middlewareAlias = str($middlewareName)->before(':')->toString();

            if (in_array($middlewareAlias, $aliases, true)) {
                return $position;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    public function pageCacheAliases(): array
    {
        $aliases = config('access-gate.middleware.page_cache_aliases', []);

        if (! is_array($aliases)) {
            return [];
        }

        return array_values(collect($aliases)
            ->filter(fn (mixed $alias): bool => is_string($alias) && $alias !== '')
            ->values()
            ->all());
    }

    public function priorityRunsAccessGateBeforePageCache(Router $router): bool
    {
        $accessGatePriority = $this->firstMiddlewarePosition($router->middlewarePriority, [
            AccessGateMiddleware::class,
            'access-gate',
        ]);
        $pageCachePriority = $this->firstMiddlewarePosition(
            $router->middlewarePriority,
            $this->pageCacheMiddlewarePriorityNames($router),
        );

        return $accessGatePriority !== null
            && $pageCachePriority !== null
            && $accessGatePriority < $pageCachePriority;
    }

    /**
     * @return list<string>
     */
    public function pageCacheMiddlewarePriorityNames(Router $router): array
    {
        $registeredMiddleware = $router->getMiddleware();

        return array_values(collect($this->pageCacheAliases())
            ->flatMap(fn (string $alias): array => array_values(array_filter([
                $alias,
                $registeredMiddleware[$alias] ?? null,
            ], is_string(...))))
            ->values()
            ->all());
    }

    private function configString(string $key, string $default): string
    {
        $value = config($key, $default);

        return is_string($value) ? $value : $default;
    }

    /**
     * @param  class-string  $contract
     * @return list<string>
     */
    private function invalidConfiguredClasses(string $configKey, string $contract): array
    {
        $configured = config($configKey, []);

        if (! is_array($configured)) {
            return [$configKey];
        }

        return array_values(collect($configured)
            ->reject(static fn (mixed $class): bool => is_string($class) && is_a($class, $contract, true))
            ->map(static fn (mixed $class): string => is_scalar($class) ? (string) $class : get_debug_type($class))
            ->all());
    }

    private function hasRateLimiter(string $name): bool
    {
        return RateLimiter::limiter($name) !== null;
    }

    private function routeUsesMiddleware(Router $router, string $routeName, string $middleware): bool
    {
        $route = $router->getRoutes()->getByName($routeName);

        if ($route === null) {
            return false;
        }

        return in_array($middleware, $route->gatherMiddleware(), true);
    }

    private function accessGatePackageInstalled(): bool
    {
        return class_exists(CapellCore::class)
            && CapellCore::isPackageInstalled(AccessGateServiceProvider::$packageName);
    }

    private function connectionName(): string
    {
        $connection = config('access-gate.connection');
        $defaultConnection = config('database.default');

        if (is_string($connection) && $connection !== '') {
            return $connection;
        }

        return is_string($defaultConnection) && $defaultConnection !== '' ? $defaultConnection : 'default';
    }

    private function schema(): Builder
    {
        return Schema::connection($this->connectionName());
    }
}
