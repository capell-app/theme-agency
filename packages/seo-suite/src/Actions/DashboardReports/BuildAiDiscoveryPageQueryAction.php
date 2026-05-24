<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Actions\DashboardReports;

use Capell\Admin\Support\SiteScope;
use Capell\Core\Models\Page;
use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static Builder<Page> run()
 */
final class BuildAiDiscoveryPageQueryAction
{
    use AsAction;

    /**
     * @return Builder<Page>
     */
    public function handle(): Builder
    {
        $query = Page::query()
            ->with([
                'pageUrl.siteDomain',
                'pageUrls.siteDomain',
                'site.language',
                'translation.language',
                'translations.language',
            ]);

        return SiteScope::applyForCurrentActor($query, denyWhenMissingActor: true);
    }
}
