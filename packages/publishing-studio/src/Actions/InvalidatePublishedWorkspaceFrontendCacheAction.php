<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Actions;

use Capell\Core\Contracts\Pageable;
use Capell\Frontend\Support\Cache\PageCacheInvalidator;
use Illuminate\Database\Eloquent\Model;
use Lorisleiva\Actions\Concerns\AsAction;

class InvalidatePublishedWorkspaceFrontendCacheAction
{
    use AsAction;

    /**
     * @param  array<class-string<Model>, array<int, int>>  $publishedModelIds
     */
    public function handle(array $publishedModelIds): void
    {
        if ($publishedModelIds === [] || ! app()->bound(PageCacheInvalidator::class)) {
            return;
        }

        $invalidator = resolve(PageCacheInvalidator::class);

        foreach ($publishedModelIds as $modelClass => $modelIds) {
            $uniqueModelIds = array_values(array_unique(array_map(intval(...), $modelIds)));
            if ($uniqueModelIds === []) {
                continue;
            }

            if (! is_a($modelClass, Model::class, true)) {
                continue;
            }

            /** @var class-string<Model> $modelClass */
            $modelClass::query()
                ->withoutGlobalScopes()
                ->whereIn((new $modelClass)->getKeyName(), $uniqueModelIds)
                ->with('translations')
                ->chunkById(100, function ($models) use ($invalidator): void {
                    foreach ($models as $model) {
                        if ($model instanceof Pageable) {
                            $invalidator->onSaved($model);
                        }
                    }
                });
        }
    }
}
