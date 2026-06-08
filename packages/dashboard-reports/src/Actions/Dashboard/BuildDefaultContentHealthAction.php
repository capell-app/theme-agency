<?php

declare(strict_types=1);

namespace Capell\DashboardReports\Actions\Dashboard;

use Capell\Admin\Data\Dashboard\ContentHealthData;
use Capell\Admin\Data\Dashboard\ContentHealthIssueData;
use Capell\Admin\Filament\Resources\Pages\PageResource;
use Capell\Admin\Support\AdminPanelEntrypoint;
use Capell\Admin\Support\SiteScope;
use Capell\Core\Models\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsObject;
use Spatie\LaravelData\DataCollection;
use Throwable;

/**
 * @method static ContentHealthData run(int $staleDays = self::DEFAULT_STALE_DAYS)
 */
final class BuildDefaultContentHealthAction
{
    use AsObject;

    public const int DEFAULT_STALE_DAYS = 90;

    public const string PAGE_TABLE_FILTER_KEY = 'dashboard_reports_health';

    public function handle(int $staleDays = self::DEFAULT_STALE_DAYS): ContentHealthData
    {
        $issues = [
            $this->makeIssue('scheduled_pages', __('capell-dashboard-reports::dashboard.issue_scheduled_pages'), $this->basePageQuery()->pending()->count()),
            $this->makeIssue('expired_pages', __('capell-dashboard-reports::dashboard.issue_expired_pages'), $this->basePageQuery()->expired()->count()),
            $this->makeIssue('pages_without_urls', __('capell-dashboard-reports::dashboard.issue_pages_without_urls'), $this->pagesWithoutUrlsCount()),
            $this->makeIssue('stale_pages', __('capell-dashboard-reports::dashboard.issue_stale_pages', ['days' => $staleDays]), $this->stalePagesCount($staleDays)),
        ];

        return new ContentHealthData(
            issues: ContentHealthIssueData::collect($issues, DataCollection::class),
        );
    }

    private function pagesWithoutUrlsCount(): int
    {
        return $this->basePageQuery()
            ->whereDoesntHave('pageUrls')
            ->count();
    }

    private function stalePagesCount(int $staleDays): int
    {
        return $this->basePageQuery()
            ->publishedDate()
            ->where((new Page)->qualifyColumn('updated_at'), '<', now()->subDays($staleDays))
            ->count();
    }

    private function makeIssue(string $id, string $label, int $count): ContentHealthIssueData
    {
        return new ContentHealthIssueData(
            id: $id,
            label: $label,
            count: $count,
            filterUrl: $this->pageIndexUrl($id),
        );
    }

    private function pageIndexUrl(string $issueId): string
    {
        try {
            $url = PageResource::getUrl('index');
        } catch (Throwable) {
            $url = url(trim(trim(AdminPanelEntrypoint::path(), '/') . '/pages', '/'));
        }

        return $url . (str_contains($url, '?') ? '&' : '?') . Arr::query([
            'tableFilters' => [
                self::PAGE_TABLE_FILTER_KEY => [
                    'value' => $issueId,
                ],
            ],
        ]);
    }

    /**
     * @return Builder<Page>
     */
    private function basePageQuery(): Builder
    {
        /** @var Builder<Page> $query */
        $query = SiteScope::applyForCurrentActor(Page::query(), denyWhenMissingActor: true);

        return $query;
    }
}
