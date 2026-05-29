<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Actions;

use Capell\Admin\Support\SiteScope;
use Capell\Core\Models\Page;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Translation;
use Capell\SeoSuite\Data\SeoOpportunityRowData;
use Capell\SeoSuite\Enums\RobotsDirectiveEnum;
use Capell\SeoSuite\Enums\SeoOpportunityTypeEnum;
use Capell\SeoSuite\Models\PageSeoSnapshot;
use Capell\SeoSuite\Models\SearchConsoleQueryMetric;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static Collection<int, SeoOpportunityRowData> run(int $limit = 25, ?string $url = null)
 */
final class BuildSeoIntelligenceOpportunitiesAction
{
    use AsAction;

    private const int MEANINGFUL_IMPRESSIONS = 100;

    private const int HIGH_IMPRESSIONS = 500;

    private const float WEAK_CTR = 0.02;

    /**
     * @return Collection<int, SeoOpportunityRowData>
     */
    public function handle(int $limit = 25, ?string $url = null): Collection
    {
        return collect()
            ->merge($this->metricOpportunities($url))
            ->merge($this->cannibalizationOpportunities($url))
            ->merge($this->missingTargetOpportunities($url))
            ->merge($this->technicalDriftOpportunities($url))
            ->sortByDesc(fn (SeoOpportunityRowData $row): int => $row->priority)
            ->take($limit)
            ->values();
    }

    /**
     * @return Collection<int, SeoOpportunityRowData>
     */
    private function metricOpportunities(?string $url): Collection
    {
        $query = SiteScope::applyForCurrentActor(SearchConsoleQueryMetric::query(), denyWhenMissingActor: true)
            ->latestWindow();

        if ($url !== null) {
            $query->where('url_hash', hash('sha256', $url));
        }

        return $query
            ->orderByDesc('impressions')
            ->limit(250)
            ->get()
            ->flatMap(function (SearchConsoleQueryMetric $metric): array {
                $rows = [];

                if ($metric->average_position >= 4.0 && $metric->average_position <= 20.0 && $metric->impressions >= self::MEANINGFUL_IMPRESSIONS) {
                    $rows[] = $this->row(
                        type: SeoOpportunityTypeEnum::QuickWin,
                        metric: $metric,
                        message: __('capell-seo-suite::generic.seo_opportunity_quick_win_message'),
                        priority: 80 + min(20, (int) floor($metric->impressions / 500)),
                    );
                }

                if ($metric->impressions >= self::HIGH_IMPRESSIONS && $metric->ctr < self::WEAK_CTR) {
                    $rows[] = $this->row(
                        type: SeoOpportunityTypeEnum::CtrOpportunity,
                        metric: $metric,
                        message: __('capell-seo-suite::generic.seo_opportunity_ctr_message'),
                        priority: 70 + min(20, (int) floor($metric->impressions / 1000)),
                    );
                }

                if ($metric->click_delta < 0 || $metric->impression_delta < 0 || $metric->position_delta > 1.0) {
                    $rows[] = $this->row(
                        type: SeoOpportunityTypeEnum::Declining,
                        metric: $metric,
                        message: __('capell-seo-suite::generic.seo_opportunity_declining_message'),
                        priority: 65 + min(20, abs($metric->click_delta)),
                    );
                }

                return $rows;
            })
            ->values();
    }

    /**
     * @return Collection<int, SeoOpportunityRowData>
     */
    private function cannibalizationOpportunities(?string $url): Collection
    {
        $metrics = SiteScope::applyForCurrentActor(SearchConsoleQueryMetric::query(), denyWhenMissingActor: true)
            ->latestWindow()
            ->where('impressions', '>=', self::MEANINGFUL_IMPRESSIONS)
            ->orderByDesc('impressions')
            ->limit(500)
            ->get();

        if ($url !== null) {
            $metrics = $metrics->filter(fn (SearchConsoleQueryMetric $metric): bool => $metric->url === $url);
        }

        return $metrics
            ->groupBy(fn (SearchConsoleQueryMetric $metric): string => $metric->site_id . ':' . $metric->query)
            ->filter(fn (Collection $group): bool => $group->pluck('url')->unique()->count() > 1)
            ->map(function (Collection $group): SeoOpportunityRowData {
                /** @var SearchConsoleQueryMetric $metric */
                $metric = $group->sortByDesc('impressions')->first();

                return new SeoOpportunityRowData(
                    type: SeoOpportunityTypeEnum::Cannibalization,
                    query: $metric->query,
                    url: $metric->url,
                    message: __('capell-seo-suite::generic.seo_opportunity_cannibalization_message'),
                    priority: 90,
                    impressions: (int) $group->sum('impressions'),
                    clicks: (int) $group->sum('clicks'),
                    ctr: (float) $metric->ctr,
                    averagePosition: $metric->average_position,
                    urls: $group->pluck('url')->unique()->values()->all(),
                );
            })
            ->values();
    }

    /**
     * @return Collection<int, SeoOpportunityRowData>
     */
    private function missingTargetOpportunities(?string $url): Collection
    {
        return $this->pageTargets($url)
            ->reject(fn (array $target): bool => $this->targetHasVisibility($target['site_id'], $target['url'], $target['keyword']))
            ->map(fn (array $target): SeoOpportunityRowData => new SeoOpportunityRowData(
                type: SeoOpportunityTypeEnum::MissingTargetVisibility,
                query: $target['keyword'],
                url: $target['url'],
                message: __('capell-seo-suite::generic.seo_opportunity_missing_target_message'),
                priority: 60,
            ))
            ->values();
    }

    /**
     * @return Collection<int, SeoOpportunityRowData>
     */
    private function technicalDriftOpportunities(?string $url): Collection
    {
        return $this->pageTargets($url)
            ->filter(fn (array $target): bool => $this->hasTechnicalConflict($target))
            ->map(fn (array $target): SeoOpportunityRowData => new SeoOpportunityRowData(
                type: SeoOpportunityTypeEnum::TechnicalDrift,
                query: $target['keyword'],
                url: $target['url'],
                message: __('capell-seo-suite::generic.seo_opportunity_technical_drift_message'),
                priority: 75,
            ))
            ->values();
    }

    /**
     * @return Collection<int, array{site_id: int, language_id: int, keyword: string, url: string, page: Page, translation: Translation}>
     */
    private function pageTargets(?string $url): Collection
    {
        $pageUrlQuery = PageUrl::query()
            ->with(['pageable', 'siteDomain', 'translation'])
            ->where('pageable_type', (new Page)->getMorphClass())
            ->where('status', true);

        if ($url !== null) {
            $pageUrlQuery->where(function (Builder $query) use ($url): void {
                $query->where('url', parse_url($url, PHP_URL_PATH) ?: $url);
            });
        }

        /** @var Collection<int, PageUrl> $pageUrls */
        $pageUrls = SiteScope::applyForCurrentActor($pageUrlQuery, denyWhenMissingActor: true)
            ->limit(500)
            ->get();

        return $pageUrls->flatMap(function (PageUrl $pageUrl): array {
            $page = $pageUrl->pageable;

            if (! $page instanceof Page) {
                return [];
            }

            $translation = $pageUrl->translation;

            if (! $translation instanceof Translation) {
                return [];
            }

            $keywords = NormalizeTargetKeywordsAction::run($translation->getMeta('keywords'));

            return array_map(fn (string $keyword): array => [
                'site_id' => (int) $pageUrl->site_id,
                'language_id' => (int) $pageUrl->language_id,
                'keyword' => $keyword,
                'url' => $pageUrl->full_url,
                'page' => $page,
                'translation' => $translation,
            ], $keywords);
        });
    }

    private function targetHasVisibility(int $siteId, string $url, string $keyword): bool
    {
        return SearchConsoleQueryMetric::query()
            ->latestWindow()
            ->where('site_id', $siteId)
            ->where('url_hash', hash('sha256', $url))
            ->where('query_hash', hash('sha256', $keyword))
            ->exists();
    }

    /**
     * @param  array{site_id: int, language_id: int, keyword: string, url: string, page: Page, translation: Translation}  $target
     */
    private function hasTechnicalConflict(array $target): bool
    {
        $page = $target['page'];
        $translation = $target['translation'];

        $hasTraffic = SearchConsoleQueryMetric::query()
            ->latestWindow()
            ->where('site_id', $target['site_id'])
            ->where('url_hash', hash('sha256', $target['url']))
            ->where(function (Builder $query): void {
                $query->where('clicks', '>', 0)->orWhere('impressions', '>', 0);
            })
            ->exists();

        $snapshot = PageSeoSnapshot::query()
            ->where('page_id', $page->getKey())
            ->where('site_id', $target['site_id'])
            ->where('language_id', $target['language_id'])
            ->latest('computed_at')
            ->latest('id')
            ->first();

        if ($snapshot instanceof PageSeoSnapshot && in_array('warning', [$snapshot->robots_status, $snapshot->canonical_status], true)) {
            return $hasTraffic;
        }

        $robots = $translation->getMeta('robots');

        return $hasTraffic && is_array($robots) && in_array(RobotsDirectiveEnum::NoIndex->value, $robots, true);
    }

    private function row(SeoOpportunityTypeEnum $type, SearchConsoleQueryMetric $metric, string $message, int $priority): SeoOpportunityRowData
    {
        return new SeoOpportunityRowData(
            type: $type,
            query: $metric->query,
            url: $metric->url,
            message: $message,
            priority: $priority,
            impressions: $metric->impressions,
            clicks: $metric->clicks,
            ctr: $metric->ctr,
            averagePosition: $metric->average_position,
        );
    }
}
