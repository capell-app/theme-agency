<?php

declare(strict_types=1);

namespace Capell\Newsletter\Support\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Throwable;

final class ProviderHttpRetry
{
    public static function apply(PendingRequest $request): PendingRequest
    {
        return $request->retry(
            self::retryTimes(),
            self::delay(...),
            throw: false,
        );
    }

    private static function retryTimes(): int
    {
        return max(1, self::integerConfig('capell-newsletter.http.retry_times', 3));
    }

    private static function delay(int $attempt, mixed $exception): int
    {
        if ($exception instanceof RequestException && in_array($exception->response->status(), [429, 503], true)) {
            return self::retryAfterDelay($exception) ?? self::fallbackDelay();
        }

        return self::fallbackDelay();
    }

    private static function retryAfterDelay(RequestException $exception): ?int
    {
        $retryAfter = $exception->response->header('Retry-After');

        if (! is_string($retryAfter) || trim($retryAfter) === '') {
            return null;
        }

        $retryAfter = trim($retryAfter);
        $delayMs = ctype_digit($retryAfter)
            ? ((int) $retryAfter) * 1000
            : self::dateDelay($retryAfter);

        return min(max(0, $delayMs), self::maxRetryAfterDelay());
    }

    private static function dateDelay(string $retryAfter): int
    {
        try {
            return (int) max(0, CarbonImmutable::now()->diffInMilliseconds(CarbonImmutable::parse($retryAfter), false));
        } catch (Throwable) {
            return self::fallbackDelay();
        }
    }

    private static function fallbackDelay(): int
    {
        return max(0, self::integerConfig('capell-newsletter.http.retry_delay_ms', 500));
    }

    private static function maxRetryAfterDelay(): int
    {
        return max(0, self::integerConfig('capell-newsletter.http.retry_after_max_ms', 60000));
    }

    private static function integerConfig(string $key, int $default): int
    {
        $value = config($key, $default);

        return is_numeric($value) ? (int) $value : $default;
    }
}
