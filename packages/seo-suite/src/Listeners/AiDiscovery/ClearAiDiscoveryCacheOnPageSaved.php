<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Listeners\AiDiscovery;

use Capell\Core\Events\PageSaved;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\SeoSuite\Actions\ClearAiDiscoveryCacheAction;

class ClearAiDiscoveryCacheOnPageSaved
{
    public function handle(PageSaved $event): void
    {
        $page = $event->page;

        if (! $page instanceof Page) {
            return;
        }

        $site = $page->relationLoaded('site')
            ? $page->getRelation('site')
            : Site::query()->find($page->site_id);

        if (! $site instanceof Site) {
            return;
        }

        ClearAiDiscoveryCacheAction::run($site, page: $page);
    }
}
