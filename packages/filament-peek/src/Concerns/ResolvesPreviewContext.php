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
        return max(1, (int) config('capell-filament-peek.preview.ttl_minutes', 15));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function assertPreviewPayloadWithinLimit(array $payload, string $payloadType): void
    {
        $maxKilobytes = max(0, (int) config('capell-filament-peek.preview.max_payload_kb', 512));

        if ($maxKilobytes === 0) {
            return;
        }

        try {
            $encoded = json_encode($payload, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            Log::warning('Filament Peek preview payload could not be encoded before caching.', [
                'payload_type' => $payloadType,
                'exception' => $exception::class,
            ]);

            throw new RuntimeException(__('capell-filament-peek::errors.payload_invalid'), previous: $exception);
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
            $user->getAuthIdentifier(),
            $page->getMorphClass(),
            $page->getKey(),
        ]);
    }
}
