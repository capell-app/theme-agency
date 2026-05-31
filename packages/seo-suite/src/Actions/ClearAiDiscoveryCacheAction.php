<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Actions;

use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\SeoSuite\Enums\AiDiscoveryStatusEnum;
use Capell\SeoSuite\Models\AiDiscoverySnapshot;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static int run(Site $site, ?Language $language = null, ?Page $page = null)
 */
final class ClearAiDiscoveryCacheAction
{
    use AsAction;

    public function handle(Site $site, ?Language $language = null, ?Page $page = null): int
    {
        $languageId = $language instanceof Language ? $language->getKey() : null;
        $pageId = $page instanceof Page ? $page->getKey() : null;

        $snapshots = AiDiscoverySnapshot::query()
            ->where('site_id', $site->getKey())
            ->when($languageId !== null, fn (Builder $query): Builder => $query->where('language_id', $languageId))
            ->when($pageId !== null, fn (Builder $query): Builder => $query->where(
                fn (Builder $pageQuery): Builder => $pageQuery
                    ->where('page_id', $pageId)
                    ->orWhereNull('page_id'),
            ))
            ->get();

        $snapshots->each(function (AiDiscoverySnapshot $snapshot): void {
            Cache::forget($snapshot->cache_key);
            $snapshot->update(['status' => AiDiscoveryStatusEnum::Stale->value]);
        });

        return $snapshots->count();
    }
}
