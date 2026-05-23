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
     * @param  array<string, mixed>|null  $originalAssets
     * @param  array<string, mixed>  $selectedRecords
     */
    public function handle(
        Pageable $page,
        Layout $layout,
        ?array $containers,
        array $assets = [],
        ?array $originalAssets = null,
        array $selectedRecords = [],
    ): void {
        if (! $page instanceof Model) {
            return;
        }

        $user = $this->currentUser();

        if (! $user instanceof Model) {
            return;
        }

        $state = new LayoutBuilderPreviewStateData(
            layoutId: (int) $layout->getKey(),
            containers: $containers ?? [],
            assets: $assets,
            originalAssets: $originalAssets,
            selectedRecords: $selectedRecords,
        );

        Cache::store($this->cacheStore())->put(
            $this->cacheKey($page, $user),
            $state->toArray(),
            now()->addMinutes($this->ttlMinutes()),
        );
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
}
