<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Support;

use Closure;
use Illuminate\Cache\TaggedCache;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Throwable;

final class PrivacyCenterOverviewStatsCache
{
    private const string KEY = 'capell-privacy-center:overview-stats';

    private const string TAG = 'privacy-center';

    /**
     * @param  Closure(): array{consent_records: int, granted_consents: int, open_privacy_requests: int, active_retention_rules: int}  $callback
     * @return array{consent_records: int, granted_consents: int, open_privacy_requests: int, active_retention_rules: int}
     */
    public static function remember(Closure $callback): array
    {
        $ttl = max(1, self::integerConfig('capell-privacy-center.overview_stats_cache_ttl_seconds', 300));

        return self::store()->remember(self::KEY, now()->addSeconds($ttl), $callback);
    }

    public static function flush(): void
    {
        self::store()->forget(self::KEY);

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

    private static function integerConfig(string $key, int $fallback): int
    {
        $value = config($key, $fallback);

        return is_numeric($value) ? (int) $value : $fallback;
    }
}
