<?php

declare(strict_types=1);

namespace Capell\AccessGate\Support;

use Capell\AccessGate\Http\Middleware\AccessGateMiddleware;
use Capell\AccessGate\Models\Area;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Illuminate\Database\Schema\Builder;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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
