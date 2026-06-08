<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Support;

use Closure;
use Illuminate\Cache\TaggedCache;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Throwable;

final class DiagnosticsSnapshotCache
{
    private const string TAG = 'diagnostics';

    /**
     * @template TValue
     *
     * @param  Closure(): TValue  $callback
     * @return TValue
     */
    public static function remember(string $key, Closure $callback): mixed
    {
        $ttl = self::ttlSeconds();

        return self::store()->remember('capell-diagnostics:snapshot:' . $key, now()->addSeconds($ttl), $callback);
    }

    public static function flush(): void
    {
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

    private static function ttlSeconds(): int
    {
        $ttl = config('capell-diagnostics.expensive_scan_cache_ttl_seconds', 300);

        if (is_int($ttl)) {
            return max(1, $ttl);
        }

        return is_string($ttl) && ctype_digit($ttl) ? max(1, (int) $ttl) : 300;
    }
}
