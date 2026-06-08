<?php

declare(strict_types=1);

namespace Capell\Contacts\Support;

use Closure;
use Illuminate\Cache\TaggedCache;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Throwable;

final class ContactsOverviewStatsCache
{
    private const string TAG = 'contacts';

    /**
     * @param  Closure(): array{contacts: int, organisations: int, open_leads: int, activities: int}  $callback
     * @return array{contacts: int, organisations: int, open_leads: int, activities: int}
     */
    public static function remember(?int $siteId, Closure $callback): array
    {
        $ttl = max(1, self::ttlSeconds());

        return self::store()->remember(self::key($siteId), now()->addSeconds($ttl), $callback);
    }

    public static function flushForSite(?int $siteId): void
    {
        self::store()->forget(self::key(null));

        if ($siteId !== null) {
            self::store()->forget(self::key($siteId));
        }

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

    private static function key(?int $siteId): string
    {
        return 'capell-contacts:overview-stats:' . ($siteId === null ? 'global' : 'site-' . $siteId);
    }

    private static function ttlSeconds(): int
    {
        $ttl = config('capell-contacts.overview_stats_cache_ttl_seconds', 300);

        if (is_int($ttl)) {
            return $ttl;
        }

        return is_string($ttl) && ctype_digit($ttl) ? (int) $ttl : 300;
    }
}
