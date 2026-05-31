<?php

declare(strict_types=1);

namespace Capell\AccessGate\Console\Commands;

use Capell\AccessGate\Http\Middleware\AccessGateMiddleware;
use Capell\AccessGate\Models\Area;
use Capell\Core\Data\Diagnostics\DoctorCheckResultData;
use Capell\Core\Data\Diagnostics\DoctorReportData;
use Illuminate\Console\Command;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Command\Command as CommandAlias;
use Throwable;

final class AccessGateDoctorCommand extends Command
{
    protected $signature = 'capell:access-gate-doctor
        {--json : Output a machine-readable JSON health report}';

    protected $description = 'Check Access Gate configuration and safety requirements.';

    public function handle(Router $router): int
    {
        $checks = collect([
            $this->checkDatabase(),
            $this->checkMiddleware($router),
            $this->checkCookies(),
            $this->checkClaimHosts(),
            $this->checkSiteScopedAreas(),
        ]);

        $report = new DoctorReportData(
            status: $checks->every(fn (DoctorCheckResultData $check): bool => $check->passed) ? 'passed' : 'failed',
            checks: $checks->values(),
        );

        if ($this->option('json')) {
            $this->line(json_encode($report->toArray(), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

            return $report->passed() ? CommandAlias::SUCCESS : CommandAlias::FAILURE;
        }

        $this->outputChecks($checks);

        if (! $report->passed()) {
            $this->error(__('capell-access-gate::doctor.failed', ['count' => $checks->where('passed', false)->count()]));

            return CommandAlias::FAILURE;
        }

        $this->info(__('capell-access-gate::doctor.passed'));

        return CommandAlias::SUCCESS;
    }

    private function checkDatabase(): DoctorCheckResultData
    {
        $connection = config('access-gate.connection');
        $connectionName = is_string($connection) && $connection !== '' ? $connection : config('database.default');

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

    private function checkMiddleware(Router $router): DoctorCheckResultData
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

    private function checkCookies(): DoctorCheckResultData
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

    private function configString(string $key, string $default): string
    {
        return config($key, $default);
    }

    private function checkClaimHosts(): DoctorCheckResultData
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

    private function checkSiteScopedAreas(): DoctorCheckResultData
    {
        if (! Schema::hasTable((new Area)->getTable()) || ! Schema::hasColumn((new Area)->getTable(), 'site_id')) {
            return new DoctorCheckResultData(
                label: 'Access Gate site-scoped areas',
                passed: true,
                message: __('capell-access-gate::doctor.site_scoped_areas.not_enabled'),
            );
        }

        if (! Schema::hasTable('sites')) {
            return new DoctorCheckResultData(
                label: 'Access Gate site-scoped areas',
                passed: true,
                message: __('capell-access-gate::doctor.site_scoped_areas.sites_missing'),
            );
        }

        $siteIds = DB::table('sites')->pluck('id')->map(fn (mixed $siteId): int => (int) $siteId)->all();

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
     * @param  Collection<int, DoctorCheckResultData>  $checks
     */
    private function outputChecks(Collection $checks): void
    {
        $checks->each(function (DoctorCheckResultData $check): void {
            if ($check->passed) {
                $this->info($check->message);

                return;
            }

            $this->error($check->message);

            if ($check->remediation !== null && $check->remediation !== '') {
                $this->line($check->remediation);
            }
        });
    }

    /**
     * @param  array<array-key, mixed>  $middleware
     * @param  list<string>  $aliases
     */
    private function firstMiddlewarePosition(array $middleware, array $aliases): ?int
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
    private function pageCacheAliases(): array
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

    private function priorityRunsAccessGateBeforePageCache(Router $router): bool
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
    private function pageCacheMiddlewarePriorityNames(Router $router): array
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
}
