<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Actions;

use Capell\Core\Enums\UrlTypeEnum;
use Capell\Core\Models\Page;
use Capell\Core\Models\PageUrl;
use Capell\SeoSuite\Data\RedirectOpportunityData;
use Capell\SeoSuite\Models\BrokenLink;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

/**
 * @method static list<RedirectOpportunityData> run(?int $siteId = null, ?int $languageId = null, ?int $pageId = null)
 */
final class BuildRedirectOpportunityReportAction
{
    use AsAction;

    /**
     * @return list<RedirectOpportunityData>
     */
    public function handle(?int $siteId = null, ?int $languageId = null, ?int $pageId = null): array
    {
        $pageQuery = $this->applyPageScope(Page::query(), $siteId, $languageId)
            ->select('id');

        return array_values(BrokenLink::query()
            ->where('http_status', '>=', 400)
            ->when($pageId !== null, fn (Builder $query): Builder => $query->where('page_id', $pageId))
            ->whereIn('page_id', $pageQuery)
            ->with([
                'page.site',
                'page.pageUrls.siteDomain',
                'page.translations',
            ])
            ->get()
            ->groupBy('target_url')
            ->map(fn (Collection $brokenLinks, string $targetUrl): RedirectOpportunityData => $this->buildOpportunity(
                sourceUrl: $targetUrl,
                brokenLinks: $brokenLinks,
                languageId: $languageId,
            ))
            ->sortByDesc(fn (RedirectOpportunityData $opportunity): int => $opportunity->hits)
            ->values()
            ->all());
    }

    /**
     * @param  Builder<PageUrl>  $query
     */
    private function applyNonRedirectUrlScope(Builder $query): void
    {
        $query->whereNull('type')
            ->orWhere('type', '!=', UrlTypeEnum::Redirect->value);
    }

    /**
     * @param  Builder<Page>  $query
     * @return Builder<Page>
     */
    private function applyPageScope(Builder $query, ?int $siteId, ?int $languageId): Builder
    {
        if ($siteId !== null) {
            $query->where('site_id', $siteId);
        }

        if ($languageId !== null) {
            $this->applyPageLanguageScope($query, $languageId);
        }

        return $query;
    }

    /**
     * @param  Builder<Page>  $query
     * @return Builder<Page>
     */
    private function applyPageLanguageScope(Builder $query, int $languageId): Builder
    {
        return $query->where(
            fn (Builder $query): Builder => $query
                ->whereRelation('pageUrls', 'language_id', $languageId)
                ->orWhereRelation('translations', 'language_id', $languageId),
        );
    }

    /**
     * @param  Collection<int, BrokenLink>  $brokenLinks
     */
    private function buildOpportunity(string $sourceUrl, Collection $brokenLinks, ?int $languageId): RedirectOpportunityData
    {
        $firstBrokenLink = $brokenLinks->first();
        $page = $firstBrokenLink?->page;
        $pageUrl = $page?->pageUrls
            ?->when($languageId !== null, fn (Collection $pageUrls): Collection => $pageUrls->where('language_id', $languageId))
            ->first();
        $translation = $page?->translations
            ?->when($languageId !== null, fn (Collection $translations): Collection => $translations->where('language_id', $languageId))
            ->first();

        $siteId = $page?->site_id;
        $languageId = $pageUrl->language_id ?? $translation?->language_id;
        $resolvedSiteId = is_int($siteId) || ctype_digit((string) $siteId) ? (int) $siteId : null;
        $resolvedLanguageId = is_int($languageId) || ctype_digit((string) $languageId) ? (int) $languageId : null;

        return new RedirectOpportunityData(
            sourceUrl: $sourceUrl,
            hits: $brokenLinks->count(),
            siteId: $resolvedSiteId,
            languageId: $resolvedLanguageId,
            suggestedTargetUrl: $this->findDirectTargetUrl($sourceUrl, $resolvedSiteId, $resolvedLanguageId),
            pageName: $page?->name,
        );
    }

    private function findDirectTargetUrl(string $sourceUrl, ?int $siteId, ?int $languageId): ?string
    {
        if ($siteId === null || $languageId === null) {
            return null;
        }

        $candidatePath = $this->candidatePath($sourceUrl);

        $candidateUrls = PageUrl::query()
            ->where('site_id', $siteId)
            ->where('language_id', $languageId)
            ->where('status', true)
            ->where($this->applyNonRedirectUrlScope(...))
            ->when(
                $candidatePath !== null,
                fn (Builder $query): Builder => $query->whereIn('url', [$sourceUrl, $candidatePath]),
                fn (Builder $query): Builder => $query->where('url', $sourceUrl),
            )
            ->with('siteDomain')
            ->get();

        $sourceIsRelative = str_starts_with($sourceUrl, '/');

        foreach ($candidateUrls as $candidateUrl) {
            if ($sourceIsRelative && $candidateUrl->url === $sourceUrl) {
                return $candidateUrl->url;
            }

            try {
                if ($candidateUrl->full_url === mb_rtrim($sourceUrl, '/')) {
                    return $candidateUrl->url;
                }
            } catch (Throwable) {
                continue;
            }
        }

        return null;
    }

    private function candidatePath(string $sourceUrl): ?string
    {
        if (str_starts_with($sourceUrl, '/')) {
            return $sourceUrl;
        }

        $path = parse_url($sourceUrl, PHP_URL_PATH);

        if (! is_string($path) || $path === '') {
            return null;
        }

        $query = parse_url($sourceUrl, PHP_URL_QUERY);

        if (is_string($query) && $query !== '') {
            return $path . '?' . $query;
        }

        return $path;
    }
}
