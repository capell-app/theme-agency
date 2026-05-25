<?php

declare(strict_types=1);

namespace Capell\FilamentPeek\Actions;

use Capell\Core\Models\Page;
use Capell\FilamentPeek\Data\PagePreviewSnapshotData;
use Capell\FilamentPeek\Providers\FilamentPeekServiceProvider;
use Capell\PublishingStudio\WorkspaceContext;
use Filament\Facades\Filament;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

final class CreatePagePreviewSnapshotAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $formState
     * @return array{snapshot: PagePreviewSnapshotData, url: string}
     */
    public function handle(Page $page, array $formState): array
    {
        $user = $this->currentUser();

        abort_unless($user instanceof Model, 403);

        $token = Str::random(48);
        $snapshot = new PagePreviewSnapshotData(
            token: $token,
            userId: $user->getAuthIdentifier(),
            pageId: (int) $page->getKey(),
            formState: $formState,
            workspaceId: $this->currentWorkspaceId(),
            path: $page->pageUrl?->url,
            layoutBuilderState: resolve(StoreLayoutBuilderPreviewStateAction::class)->resolve($page, $user),
        );

        Cache::store($this->cacheStore())->put(
            $this->cacheKey($token),
            $snapshot->toArray(),
            now()->addMinutes($this->ttlMinutes()),
        );

        return [
            'snapshot' => $snapshot,
            'url' => URL::temporarySignedRoute(
                'capell-filament-peek.preview',
                now()->addMinutes($this->ttlMinutes()),
                ['token' => $token],
            ),
        ];
    }

    public function find(string $token): ?PagePreviewSnapshotData
    {
        $payload = Cache::store($this->cacheStore())->get($this->cacheKey($token));

        if (! is_array($payload)) {
            return null;
        }

        return PagePreviewSnapshotData::from($payload);
    }

    private function currentUser(): ?Authenticatable
    {
        $filamentUser = Filament::auth()->user();

        if ($filamentUser instanceof Authenticatable) {
            return $filamentUser;
        }

        $user = Auth::user();

        return $user instanceof Authenticatable ? $user : null;
    }

    private function currentWorkspaceId(): ?int
    {
        if (! class_exists(WorkspaceContext::class)) {
            return null;
        }

        $workspaceId = WorkspaceContext::currentId();

        return is_int($workspaceId) ? $workspaceId : null;
    }

    private function cacheKey(string $token): string
    {
        return FilamentPeekServiceProvider::$name . ':snapshot:' . $token;
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
