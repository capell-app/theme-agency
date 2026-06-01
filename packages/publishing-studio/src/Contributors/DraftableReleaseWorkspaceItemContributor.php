<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Contributors;

use Capell\PublishingStudio\Contracts\CountableReleaseWorkspaceItemContributor;
use Capell\PublishingStudio\Data\ReleaseWorkspaceItemData;
use Capell\PublishingStudio\Models\Workspace;
use Capell\PublishingStudio\WorkspaceRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

final class DraftableReleaseWorkspaceItemContributor implements CountableReleaseWorkspaceItemContributor
{
    /**
     * @return list<ReleaseWorkspaceItemData>
     */
    public function itemsFor(Workspace $workspace): array
    {
        return $this->limitedItemsFor($workspace);
    }

    /**
     * @return list<ReleaseWorkspaceItemData>
     */
    public function limitedItemsFor(Workspace $workspace, ?int $limit = null): array
    {
        $items = [];

        foreach (WorkspaceRegistry::all() as $modelClass => $registeredDraftable) {
            unset($registeredDraftable);

            $model = new $modelClass;
            if (! $model instanceof Model) {
                continue;
            }

            if (! $model->getConnection()->getSchemaBuilder()->hasTable($model->getTable())) {
                continue;
            }

            $modelClass::query()
                ->withoutGlobalScopes()
                ->where('workspace_id', $workspace->id)
                ->orderBy($model->getKeyName())
                ->when($this->usesSoftDeletes($model), static fn (Builder $query): Builder => $query->withTrashed())
                ->when($limit !== null, static fn (Builder $query): Builder => $query->limit(max(0, $limit - count($items))))
                ->each(function (Model $record) use (&$items, $modelClass): void {
                    $items[] = new ReleaseWorkspaceItemData(
                        source: Str::headline(class_basename($modelClass)),
                        label: $this->labelFor($record),
                        modelClass: $modelClass,
                        modelId: $record->getKey(),
                        changeType: $this->changeTypeFor($record),
                        status: 'ready',
                        url: null,
                    );
                });

            if ($limit !== null && count($items) >= $limit) {
                break;
            }
        }

        return $items;
    }

    public function countFor(Workspace $workspace): int
    {
        $count = 0;

        foreach (WorkspaceRegistry::all() as $modelClass => $registeredDraftable) {
            unset($registeredDraftable);

            $model = new $modelClass;
            if (! $model instanceof Model) {
                continue;
            }

            if (! $model->getConnection()->getSchemaBuilder()->hasTable($model->getTable())) {
                continue;
            }

            $count += $modelClass::query()
                ->withoutGlobalScopes()
                ->where('workspace_id', $workspace->id)
                ->when($this->usesSoftDeletes($model), static fn (Builder $query): Builder => $query->withTrashed())
                ->count();
        }

        return $count;
    }

    private function labelFor(Model $record): string
    {
        foreach (['name', 'title', 'slug', 'key'] as $attribute) {
            $value = $record->getAttribute($attribute);

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return sprintf('%s #%s', class_basename($record), (string) $record->getKey());
    }

    private function changeTypeFor(Model $record): string
    {
        if ($this->isDeletedTombstone($record)) {
            return 'deleted';
        }

        return (int) ($record->getAttribute('shadowed_by_workspace_id') ?? 0) > 0 ? 'updated' : 'created';
    }

    private function isDeletedTombstone(Model $record): bool
    {
        return array_key_exists('deleted_at', $record->getAttributes())
            && $record->getAttribute('deleted_at') !== null;
    }

    private function usesSoftDeletes(Model $record): bool
    {
        $traitNames = array_map(
            static fn (string $traitName): string => ltrim($traitName, '\\'),
            class_uses_recursive($record),
        );

        return in_array(SoftDeletes::class, $traitNames, true);
    }
}
