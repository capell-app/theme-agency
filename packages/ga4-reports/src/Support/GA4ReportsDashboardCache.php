<?php

declare(strict_types=1);

namespace Capell\GA4Reports\Support;

use Capell\GA4Reports\Data\GA4ReportsOverviewData;
use Capell\GA4Reports\Data\GA4ReportsTopPageData;
use Capell\GA4Reports\Data\GA4ReportsTrendPointData;
use Capell\GA4Reports\Data\GA4ReportsWindowData;
use Closure;
use DateTimeInterface;
use Illuminate\Cache\TaggedCache;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Throwable;

final class GA4ReportsDashboardCache
{
    private const string TAG = 'ga4-reports';

    /**
     * @param  Closure(): GA4ReportsOverviewData  $callback
     */
    public static function rememberOverview(GA4ReportsWindowData $window, Closure $callback): GA4ReportsOverviewData
    {
        $overview = self::store()->remember(self::key('overview', $window), self::expiresAt(), $callback);

        return $overview instanceof GA4ReportsOverviewData ? $overview : $callback();
    }

    /**
     * @param  Closure(): list<GA4ReportsTrendPointData>  $callback
     * @return list<GA4ReportsTrendPointData>
     */
    public static function rememberTrend(GA4ReportsWindowData $window, Closure $callback): array
    {
        $trend = self::store()->remember(self::key('trend', $window), self::expiresAt(), $callback);

        return self::trendPoints($trend) ?? $callback();
    }

    /**
     * @param  Closure(): list<GA4ReportsTopPageData>  $callback
     * @return list<GA4ReportsTopPageData>
     */
    public static function rememberTopPages(GA4ReportsWindowData $window, int $limit, Closure $callback): array
    {
        $topPages = self::store()->remember(self::key('top-pages', $window, ['limit' => $limit]), self::expiresAt(), $callback);

        return self::topPages($topPages) ?? $callback();
    }

    public static function flushForWindow(GA4ReportsWindowData $window): void
    {
        self::store()->forget(self::key('overview', $window));
        self::store()->forget(self::key('trend', $window));
        self::store()->forget(self::key('top-pages', $window, ['limit' => 10]));
        self::store()->forget(self::key('top-pages', $window, ['limit' => 100]));

        try {
            Cache::tags([self::TAG])->flush();
        } catch (Throwable) {
            //
        }
    }

    private static function store(): Repository
    {
        try {
            $store = Cache::tags([self::TAG]);

            if ($store instanceof TaggedCache) {
                return $store;
            }
        } catch (Throwable) {
            //
        }

        return Cache::store();
    }

    private static function expiresAt(): DateTimeInterface
    {
        $configuredTtl = config('capell-ga4-reports.dashboard_cache_ttl_seconds', 300);
        $ttl = is_numeric($configuredTtl) ? max(1, (int) $configuredTtl) : 300;

        return now()->addSeconds($ttl);
    }

    /**
     * @return list<GA4ReportsTrendPointData>|null
     */
    private static function trendPoints(mixed $value): ?array
    {
        if (! is_array($value)) {
            return null;
        }

        $points = [];

        foreach ($value as $point) {
            if (! $point instanceof GA4ReportsTrendPointData) {
                return null;
            }

            $points[] = $point;
        }

        return $points;
    }

    /**
     * @return list<GA4ReportsTopPageData>|null
     */
    private static function topPages(mixed $value): ?array
    {
        if (! is_array($value)) {
            return null;
        }

        $pages = [];

        foreach ($value as $page) {
            if (! $page instanceof GA4ReportsTopPageData) {
                return null;
            }

            $pages[] = $page;
        }

        return $pages;
    }

    /**
     * @param  array<string, scalar>  $parts
     */
    private static function key(string $type, GA4ReportsWindowData $window, array $parts = []): string
    {
        $segments = [
            'capell-ga4-reports',
            'dashboard',
            $type,
            $window->propertyId,
            $window->startsAt->toDateString(),
            $window->endsAt->toDateString(),
        ];

        foreach ($parts as $name => $value) {
            $segments[] = $name . '-' . $value;
        }

        return implode(':', $segments);
    }
}
