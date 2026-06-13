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
        $cacheKey = self::key('overview', $window);
        $store = self::store();
        $payload = $store->get($cacheKey);

        if (is_array($payload)) {
            try {
                return GA4ReportsOverviewData::from($payload);
            } catch (Throwable) {
                //
            }
        }

        if ($payload !== null) {
            $store->forget($cacheKey);
        }

        $overview = $callback();

        $store->put($cacheKey, $overview->toArray(), self::expiresAt());

        return $overview;
    }

    /**
     * @param  Closure(): list<GA4ReportsTrendPointData>  $callback
     * @return list<GA4ReportsTrendPointData>
     */
    public static function rememberTrend(GA4ReportsWindowData $window, Closure $callback): array
    {
        $cacheKey = self::key('trend', $window);
        $store = self::store();
        $payload = $store->get($cacheKey);

        if (is_array($payload)) {
            $trend = self::trendPoints($payload);

            if (is_array($trend)) {
                return $trend;
            }
        }

        if ($payload !== null) {
            $store->forget($cacheKey);
        }

        $trend = $callback();

        $store->put($cacheKey, array_map(fn (GA4ReportsTrendPointData $point): array => $point->toArray(), $trend), self::expiresAt());

        return $trend;
    }

    /**
     * @param  Closure(): list<GA4ReportsTopPageData>  $callback
     * @return list<GA4ReportsTopPageData>
     */
    public static function rememberTopPages(GA4ReportsWindowData $window, int $limit, Closure $callback): array
    {
        $cacheKey = self::key('top-pages', $window, ['limit' => $limit]);
        $store = self::store();
        $payload = $store->get($cacheKey);

        if (is_array($payload)) {
            $topPages = self::topPages($payload);

            if (is_array($topPages)) {
                return $topPages;
            }
        }

        if ($payload !== null) {
            $store->forget($cacheKey);
        }

        $topPages = $callback();

        $store->put($cacheKey, array_map(fn (GA4ReportsTopPageData $page): array => $page->toArray(), $topPages), self::expiresAt());

        return $topPages;
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
            if (! is_array($point)) {
                return null;
            }

            try {
                $points[] = GA4ReportsTrendPointData::from($point);
            } catch (Throwable) {
                return null;
            }
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
            if (! is_array($page)) {
                return null;
            }

            try {
                $pages[] = GA4ReportsTopPageData::from($page);
            } catch (Throwable) {
                return null;
            }
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
