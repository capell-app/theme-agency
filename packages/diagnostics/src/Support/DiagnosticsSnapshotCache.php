<?php

declare(strict_types=1);

namespace Capell\Diagnostics\Support;

use Closure;
use Illuminate\Cache\TaggedCache;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Spatie\LaravelData\Data;
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

    /**
     * @template TData of Data
     *
     * @param  class-string<TData>  $dataClass
     * @param  Closure(): TData  $callback
     * @return TData
     */
    public static function rememberData(string $key, string $dataClass, Closure $callback): Data
    {
        $cacheKey = self::key($key);
        $store = self::store();
        $payload = $store->get($cacheKey);

        if (is_array($payload)) {
            try {
                $data = $dataClass::from($payload);

                if ($data instanceof $dataClass) {
                    return $data;
                }
            } catch (Throwable) {
                //
            }
        }

        if ($payload !== null) {
            $store->forget($cacheKey);
        }

        $data = $callback();

        $store->put($cacheKey, $data->toArray(), now()->addSeconds(self::ttlSeconds()));

        return $data;
    }

    /**
     * @template TData of Data
     *
     * @param  class-string<TData>  $dataClass
     * @param  Closure(): array<int, TData>  $callback
     * @return array<int, TData>
     */
    public static function rememberDataList(string $key, string $dataClass, Closure $callback): array
    {
        $cacheKey = self::key($key);
        $store = self::store();
        $payload = $store->get($cacheKey);

        if (is_array($payload)) {
            try {
                $items = [];

                foreach ($payload as $itemPayload) {
                    if (! is_array($itemPayload)) {
                        $items = null;

                        break;
                    }

                    $item = $dataClass::from($itemPayload);

                    if (! $item instanceof $dataClass) {
                        $items = null;

                        break;
                    }

                    $items[] = $item;
                }

                if (is_array($items)) {
                    return $items;
                }
            } catch (Throwable) {
                //
            }
        }

        if ($payload !== null) {
            $store->forget($cacheKey);
        }

        $items = $callback();

        $store->put(
            $cacheKey,
            array_map(fn (Data $item): array => $item->toArray(), $items),
            now()->addSeconds(self::ttlSeconds()),
        );

        return $items;
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

    private static function key(string $key): string
    {
        return 'capell-diagnostics:snapshot:' . $key;
    }
}
