<?php

declare(strict_types=1);

namespace Capell\FilamentPeek\Actions;

use Capell\Core\Contracts\Pageable;
use Capell\Core\Models\Layout;
use Capell\FilamentPeek\Data\LayoutBuilderPreviewStateData;
use Capell\FilamentPeek\Providers\FilamentPeekServiceProvider;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\Concerns\AsAction;

final class StoreLayoutBuilderPreviewStateAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>|null  $containers
     * @param  array<string, mixed>  $assets
     */
    public function handle(
        Pageable $page,
        Layout $layout,
        ?array $containers,
        array $assets = [],
    ): void {
        if (! $page instanceof Model) {
            return;
        }

        $user = $this->currentUser();

        if (! $user instanceof Model) {
            return;
        }

        $signature = $this->signature((int) $layout->getKey(), $containers ?? [], $assets);
        $cache = Cache::store($this->cacheStore());
        $cacheKey = $this->cacheKey($page, $user);
        $existingPayload = $cache->get($cacheKey);

        if (is_array($existingPayload) && ($existingPayload['signature'] ?? null) === $signature) {
            return;
        }

        $state = new LayoutBuilderPreviewStateData(
            layoutId: (int) $layout->getKey(),
            containers: $containers ?? [],
            assets: $assets,
            signature: $signature,
        );

        $cache->put(
            $cacheKey,
            $state->toArray(),
            now()->addMinutes($this->ttlMinutes()),
        );
    }

    public function clear(Pageable $page, ?Model $user = null): void
    {
        if (! $page instanceof Model) {
            return;
        }

        $user ??= $this->currentUser();

        if (! $user instanceof Model) {
            return;
        }

        Cache::store($this->cacheStore())->forget($this->cacheKey($page, $user));
    }

    public function resolve(Pageable $page, Model $user): ?LayoutBuilderPreviewStateData
    {
        if (! $page instanceof Model) {
            return null;
        }

        $payload = Cache::store($this->cacheStore())->get($this->cacheKey($page, $user));

        if (! is_array($payload)) {
            return null;
        }

        return LayoutBuilderPreviewStateData::from($payload);
    }

    private function currentUser(): ?Model
    {
        $filamentUser = Filament::auth()->user();

        if ($filamentUser instanceof Model) {
            return $filamentUser;
        }

        $user = Auth::user();

        return $user instanceof Model ? $user : null;
    }

    private function cacheKey(Model $page, Model $user): string
    {
        return implode(':', [
            FilamentPeekServiceProvider::$name,
            'layout-builder',
            $user->getAuthIdentifier(),
            $page->getMorphClass(),
            $page->getKey(),
        ]);
    }

    private function ttlMinutes(): int
    {
        return max(1, (int) config('capell-filament-peek.preview.ttl_minutes', 15));
    }

    private function cacheStore(): ?string
    {
        $store = config('capell-filament-peek.preview.cache_store');

        return is_string($store) && $store !== '' ? $store : null;
    }

    /**
     * @param  array<string, mixed>  $containers
     * @param  array<string, mixed>  $assets
     */
    private function signature(int $layoutId, array $containers, array $assets): string
    {
        return hash('sha256', json_encode([
            'layout_id' => $layoutId,
            'containers' => $containers,
            'assets' => $assets,
        ], JSON_THROW_ON_ERROR));
    }
}
