<?php

declare(strict_types=1);

namespace Capell\GA4Reports\Support;

use Capell\GA4Reports\Data\GA4ReportsOverviewData;
use Capell\GA4Reports\Data\GA4ReportsTopPageData;
use Capell\GA4Reports\Data\GA4ReportsTrendPointData;
use Capell\GA4Reports\Data\GA4ReportsWindowData;
use Closure;
use DateTimeInterface;
use Illuminate\Cache\Repository;
use Illuminate\Cache\TaggedCache;
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
        return self::store()->remember(self::key('overview', $window), self::expiresAt(), $callback);
    }

    /**
     * @param  Closure(): list<GA4ReportsTrendPointData>  $callback
     * @return list<GA4ReportsTrendPointData>
     */
    public static function rememberTrend(GA4ReportsWindowData $window, Closure $callback): array
    {
        return self::store()->remember(self::key('trend', $window), self::expiresAt(), $callback);
    }

    /**
     * @param  Closure(): list<GA4ReportsTopPageData>  $callback
     * @return list<GA4ReportsTopPageData>
     */
    public static function rememberTopPages(GA4ReportsWindowData $window, int $limit, Closure $callback): array
    {
        return self::store()->remember(self::key('top-pages', $window, ['limit' => $limit]), self::expiresAt(), $callback);
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
        $ttl = max(1, (int) config('capell-ga4-reports.dashboard_cache_ttl_seconds', 300));

        return now()->addSeconds($ttl);
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
