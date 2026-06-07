<?php

declare(strict_types=1);

namespace Capell\FilamentPeek\Actions;

use Capell\Core\Models\Page;
use Capell\FilamentPeek\Concerns\ResolvesPreviewContext;
use Capell\FilamentPeek\Data\PagePreviewSnapshotData;
use Capell\PublishingStudio\WorkspaceContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static array{snapshot: PagePreviewSnapshotData, url: string} run(Page $page, array<string, mixed> $formState)
 */
final class CreatePagePreviewSnapshotAction
{
    use AsAction;
    use ResolvesPreviewContext;

    /**
     * @param  array<string, mixed>  $formState
     * @return array{snapshot: PagePreviewSnapshotData, url: string}
     */
    public function handle(Page $page, array $formState): array
    {
        $user = $this->currentPreviewUser();

        abort_unless($user instanceof Model, 403);

        $token = Str::random(48);
        $snapshot = new PagePreviewSnapshotData(
            token: $token,
            userId: $user->getAuthIdentifier(),
            pageId: (int) $page->getKey(),
            formState: $formState,
            workspaceId: $this->currentWorkspaceId(),
            layoutBuilderState: resolve(StoreLayoutBuilderPreviewStateAction::class)->resolve($page, $user),
        );

        $payload = $snapshot->toArray();

        $this->assertPreviewPayloadWithinLimit($payload, 'page_preview_snapshot');

        Cache::store($this->previewCacheStore())->put(
            $this->snapshotCacheKey($token),
            $payload,
            now()->addMinutes($this->previewTtlMinutes()),
        );

        return [
            'snapshot' => $snapshot,
            'url' => URL::temporarySignedRoute(
                'capell-filament-peek.preview',
                now()->addMinutes($this->previewTtlMinutes()),
                ['token' => $token],
            ),
        ];
    }

    private function currentWorkspaceId(): ?int
    {
        if (! class_exists(WorkspaceContext::class)) {
            return null;
        }

        $workspaceId = WorkspaceContext::currentId();

        return is_int($workspaceId) ? $workspaceId : null;
    }
}
