<?php

declare(strict_types=1);

namespace Capell\FilamentPeek\Actions;

use Capell\Core\Contracts\Pageable;
use Capell\Core\Models\Layout;
use Capell\FilamentPeek\Concerns\ResolvesPreviewContext;
use Capell\FilamentPeek\Contracts\StoresLayoutBuilderPreviewState;
use Capell\FilamentPeek\Data\LayoutBuilderPreviewStateData;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\Concerns\AsAction;

final class StoreLayoutBuilderPreviewStateAction implements StoresLayoutBuilderPreviewState
{
    use AsAction;
    use ResolvesPreviewContext;

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

        $user = $this->currentPreviewUser();

        if (! $user instanceof Authenticatable) {
            return;
        }

        $layoutId = $this->modelIntKey($layout);
        $signature = $this->signature($layoutId, $containers ?? [], $assets);
        $cache = Cache::store($this->previewCacheStore());
        $cacheKey = $this->layoutBuilderPreviewCacheKey($page, $user);
        $existingPayload = $cache->get($cacheKey);

        if (is_array($existingPayload) && ($existingPayload['signature'] ?? null) === $signature) {
            return;
        }

        $state = new LayoutBuilderPreviewStateData(
            layoutId: $layoutId,
            containers: $containers ?? [],
            assets: $assets,
            signature: $signature,
        );

        $payload = $state->toArray();

        $this->assertPreviewPayloadWithinLimit($payload, 'layout_builder_preview_state');

        $cache->put(
            $cacheKey,
            $payload,
            now()->addMinutes($this->previewTtlMinutes()),
        );
    }

    public function clear(Pageable $page, ?Authenticatable $user = null): void
    {
        if (! $page instanceof Model) {
            return;
        }

        $user ??= $this->currentPreviewUser();

        if (! $user instanceof Authenticatable) {
            return;
        }

        Cache::store($this->previewCacheStore())->forget($this->layoutBuilderPreviewCacheKey($page, $user));
    }

    public function resolve(Pageable $page, Authenticatable $user): ?LayoutBuilderPreviewStateData
    {
        if (! $page instanceof Model) {
            return null;
        }

        $payload = Cache::store($this->previewCacheStore())->get($this->layoutBuilderPreviewCacheKey($page, $user));

        if (! is_array($payload)) {
            return null;
        }

        return LayoutBuilderPreviewStateData::from($payload);
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
