<?php

declare(strict_types=1);

namespace Capell\FilamentPeek\Concerns;

use Capell\FilamentPeek\Providers\FilamentPeekServiceProvider;
use Filament\Facades\Filament;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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
