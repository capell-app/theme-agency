<?php

declare(strict_types=1);

namespace Capell\FrontendAuthoring\Actions;

use Capell\HtmlCache\Models\CachedModelUrl;
use Illuminate\Database\Eloquent\Model;
use Lorisleiva\Actions\Concerns\AsObject;

class CollectAffectedCachedUrlsAction
{
    use AsObject;

    /**
     * @return list<string>
     */
    public function handle(Model $model): array
    {
        return array_values(CachedModelUrl::query()
            ->where('cacheable_type', $model->getMorphClass())
            ->where('cacheable_id', $model->getKey())
            ->pluck('url')
            ->filter(fn (mixed $url): bool => is_string($url) && $url !== '')
            ->unique()
            ->values()
            ->all());
    }
}
