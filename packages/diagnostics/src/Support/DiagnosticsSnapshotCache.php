<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Support;

use Closure;
use Illuminate\Cache\Repository;
use Illuminate\Cache\TaggedCache;
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
        $ttl = max(1, (int) config('capell-diagnostics.expensive_scan_cache_ttl_seconds', 300));

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
}
