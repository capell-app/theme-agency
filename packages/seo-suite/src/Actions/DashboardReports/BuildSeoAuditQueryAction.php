<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Actions\DashboardReports;

use Capell\Admin\Support\SiteScope;
use Capell\Core\Models\Page;
use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\Actions\Concerns\AsAction;

final class BuildSeoAuditQueryAction
{
    use AsAction;

    /**
     * @return Builder<Page>
     */
    public function handle(): Builder
    {
        $query = Page::query()
            ->with([
                'blueprint',
                'pageUrl.siteDomain',
                'site.language',
                'seoSnapshots',
                'translation.language',
                'translations.language',
            ]);

        return SiteScope::applyForCurrentActor($query, denyWhenMissingActor: true);
    }
}
