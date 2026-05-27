<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Actions;

use Capell\PublishingStudio\Models\PublishingRevision;
use Capell\PublishingStudio\Support\WorkspaceSchema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

final class ListPublishingRevisionsAction
{
    use AsAction;

    /**
     * @param  class-string<Model>|null  $revisionableType
     * @return Collection<int, PublishingRevision>
     */
    public function handle(
        ?Model $revisionable = null,
        ?string $revisionableType = null,
        ?int $revisionableId = null,
        ?string $revisionableUuid = null,
    ): Collection {
        if ($revisionable instanceof Model) {
            $revisionableType ??= $revisionable::class;
            $revisionableId ??= $revisionable->getKey() === null ? null : (int) $revisionable->getKey();
            $revisionableUuid ??= $this->uuidFor($revisionable);
        }

        if (! WorkspaceSchema::hasTable((new PublishingRevision)->getTable())) {
            return collect();
        }

        return PublishingRevision::query()
            ->with(['workspace.creator', 'publishedVersion', 'actor'])
            ->when(
                $revisionableType !== null,
                fn (Builder $query): Builder => $query->where('revisionable_type', $revisionableType),
            )
            ->when(
                $revisionableUuid !== null && $revisionableUuid !== '',
                fn (Builder $query): Builder => $query->where('revisionable_uuid', $revisionableUuid),
                fn (Builder $query): Builder => $query->when(
                    $revisionableId !== null,
                    fn (Builder $query): Builder => $query->where('revisionable_id', $revisionableId),
                ),
            )
            ->latest('version')
            ->latest('id')
            ->get();
    }

    private function uuidFor(Model $model): ?string
    {
        $attributes = $model->getAttributes();

        if (! array_key_exists('uuid', $attributes)) {
            return null;
        }

        $uuid = $attributes['uuid'];

        return is_string($uuid) && $uuid !== '' ? $uuid : null;
    }
}
