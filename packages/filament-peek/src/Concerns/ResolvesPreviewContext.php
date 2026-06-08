<?php

declare(strict_types=1);

namespace Capell\FilamentPeek\Concerns;

use Capell\FilamentPeek\Providers\FilamentPeekServiceProvider;
use Filament\Facades\Filament;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use JsonException;
use RuntimeException;

trait ResolvesPreviewContext
{
    protected function currentPreviewUser(): ?Authenticatable
    {
        $filamentUser = Filament::auth()->user();

        if ($filamentUser instanceof Authenticatable) {
            return $filamentUser;
        }

        $user = Auth::user();

        return $user instanceof Authenticatable ? $user : null;
    }

    protected function previewCacheStore(): ?string
    {
        $store = config('capell-filament-peek.preview.cache_store');

        return is_string($store) && $store !== '' ? $store : null;
    }

    protected function previewTtlMinutes(): int
    {
        return $this->configInt('capell-filament-peek.preview.ttl_minutes', 15, 1);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function assertPreviewPayloadWithinLimit(array $payload, string $payloadType): void
    {
        $maxKilobytes = $this->configInt('capell-filament-peek.preview.max_payload_kb', 512, 0);

        if ($maxKilobytes === 0) {
            return;
        }

        try {
            $encoded = json_encode($payload, JSON_THROW_ON_ERROR);
        } catch (JsonException $jsonException) {
            Log::warning('Filament Peek preview payload could not be encoded before caching.', [
                'payload_type' => $payloadType,
                'exception' => $jsonException::class,
            ]);

            throw new RuntimeException(__('capell-filament-peek::errors.payload_invalid'), $jsonException->getCode(), previous: $jsonException);
        }

        $bytes = strlen($encoded);
        $maxBytes = $maxKilobytes * 1024;

        if ($bytes <= $maxBytes) {
            return;
        }

        Log::warning('Filament Peek preview payload exceeded the configured cache limit.', [
            'payload_type' => $payloadType,
            'bytes' => $bytes,
            'max_bytes' => $maxBytes,
        ]);

        throw new RuntimeException(__('capell-filament-peek::errors.payload_too_large'));
    }

    protected function snapshotCacheKey(string $token): string
    {
        return FilamentPeekServiceProvider::$name . ':snapshot:' . $token;
    }

    protected function layoutBuilderPreviewCacheKey(Model $page, Authenticatable $user): string
    {
        return implode(':', [
            FilamentPeekServiceProvider::$name,
            'layout-builder',
            $this->previewUserIdentifierString($user),
            $page->getMorphClass(),
            $this->modelKeyString($page),
        ]);
    }

    protected function modelIntKey(Model $model): int
    {
        $key = $model->getKey();

        if (is_int($key)) {
            return $key;
        }

        if (is_string($key) && ctype_digit($key)) {
            return (int) $key;
        }

        throw new RuntimeException('Expected preview model to have an integer key.');
    }

    protected function previewUserIdentifier(Authenticatable $user): int|string
    {
        $identifier = $user->getAuthIdentifier();

        if (is_int($identifier) || is_string($identifier)) {
            return $identifier;
        }

        throw new RuntimeException('Expected preview user to have a scalar auth identifier.');
    }

    protected function previewUserIdentifierString(Authenticatable $user): string
    {
        return (string) $this->previewUserIdentifier($user);
    }

    protected function modelKeyString(Model $model): string
    {
        return (string) $this->modelIntKey($model);
    }

    private function configInt(string $key, int $default, int $minimum): int
    {
        $value = config($key, $default);

        if (is_int($value)) {
            return max($minimum, $value);
        }

        return is_string($value) && ctype_digit($value)
            ? max($minimum, (int) $value)
            : max($minimum, $default);
    }
}
