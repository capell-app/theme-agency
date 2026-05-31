<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Actions;

use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\Core\Models\Translation;
use Capell\Frontend\Actions\ResolvePageCanonicalUrlAction;
use Capell\SeoSuite\Data\PageIntelligenceSummaryData;
use Capell\SeoSuite\Data\SearchConsoleRankingRowData;
use Capell\SeoSuite\Models\SearchConsoleQueryMetric;
use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static PageIntelligenceSummaryData run(Page $page, Site $site, Language $language)
 */
final class BuildPageIntelligenceSummaryAction
{
    use AsAction;

    public function handle(Page $page, Site $site, Language $language): PageIntelligenceSummaryData
    {
        $page->loadMissing([
            'translation' => fn (BuilderContract $query): BuilderContract => $query->where('language_id', $language->id),
            'pageUrl' => fn (BuilderContract $query): BuilderContract => $query->where('language_id', $language->id),
            'pageUrl.siteDomain',
        ]);

        $url = ResolvePageCanonicalUrlAction::run($page, $language) ?? $page->pageUrl?->full_url;
        $targetKeywords = $this->targetKeywords($page->translation);

        if (! is_string($url) || trim($url) === '') {
            return new PageIntelligenceSummaryData(
                targetKeywords: $targetKeywords,
                rankingRows: [],
                opportunities: [],
            );
        }

        return new PageIntelligenceSummaryData(
            targetKeywords: $targetKeywords,
            rankingRows: $this->rankingRows($site, $url),
            opportunities: array_values(BuildSeoIntelligenceOpportunitiesAction::run(limit: 10, url: $url)->all()),
        );
    }

    /**
     * @return list<string>
     */
    private function targetKeywords(?Translation $translation): array
    {
        if (! $translation instanceof Translation) {
            return [];
        }

        return NormalizeTargetKeywordsAction::run($translation->getMeta('keywords'));
    }

    /**
     * @return list<SearchConsoleRankingRowData>
     */
    private function rankingRows(Site $site, string $url): array
    {
        return array_values(SearchConsoleQueryMetric::query()
            ->latestWindow()
            ->where('site_id', $site->getKey())
            ->where('url_hash', hash('sha256', $url))
            ->orderByDesc('impressions')
            ->limit(10)
            ->get()
            ->map(fn (SearchConsoleQueryMetric $metric): SearchConsoleRankingRowData => new SearchConsoleRankingRowData(
                siteId: (int) $site->getKey(),
                query: $metric->query,
                url: $metric->url,
                windowStart: $metric->window_start,
                windowEnd: $metric->window_end,
                clicks: $metric->clicks,
                impressions: $metric->impressions,
                ctr: (float) ($metric->ctr ?? 0.0),
                averagePosition: (float) ($metric->average_position ?? 0.0),
                previousClicks: $metric->previous_clicks,
                previousImpressions: $metric->previous_impressions,
                previousCtr: (float) ($metric->previous_ctr ?? 0.0),
                previousAveragePosition: (float) ($metric->previous_average_position ?? 0.0),
                clickDelta: $metric->click_delta,
                impressionDelta: $metric->impression_delta,
                positionDelta: (float) ($metric->position_delta ?? 0.0),
            ))
            ->values()
            ->all());
    }
}
