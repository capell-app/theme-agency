<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Support\PublicUrls;

use Capell\CampaignStudio\Models\CampaignLandingPage;
use Capell\CampaignStudio\Providers\CampaignStudioServiceProvider;
use Capell\Core\Models\Language;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Site;
use Capell\SiteDiscovery\Contracts\PublicUrlContributor;
use Capell\SiteDiscovery\Data\PublicUrlData;
use Capell\SiteDiscovery\Enums\PublicUrlContentType;
use Illuminate\Support\Collection;

final class CampaignLandingPagePublicUrlContributor implements PublicUrlContributor
{
    /**
     * @return Collection<int, PublicUrlData>
     */
    public function publicUrls(): Collection
    {
        /** @var Collection<string, PublicUrlData> $urls */
        $urls = collect();

        CampaignLandingPage::query()
            ->with(['page.pageUrls.siteDomain', 'page.pageUrls.language', 'page.site', 'page.translation'])
            ->chunkById(100, function (Collection $landingPages) use ($urls): void {
                $landingPages
                    ->flatMap(fn (CampaignLandingPage $landingPage): Collection => $this->publicUrlsForLandingPage($landingPage))
                    ->each(function (PublicUrlData $url) use ($urls): void {
                        $urls->put($this->urlKey($url), $url);
                    });
            });

        return $urls->values();
    }

    /**
     * @return Collection<int, PublicUrlData>
     */
    private function publicUrlsForLandingPage(CampaignLandingPage $landingPage): Collection
    {
        $page = $landingPage->page;

        if ($page === null || ! $page->site instanceof Site) {
            return collect();
        }

        return $page->pageUrls
            ->map(function (PageUrl $pageUrl) use ($page): ?PublicUrlData {
                if ($pageUrl->status !== true || $pageUrl->type !== null || ! $pageUrl->language instanceof Language) {
                    return null;
                }

                return new PublicUrlData(
                    canonicalUrl: $pageUrl->full_url,
                    sourcePackage: CampaignStudioServiceProvider::$packageName,
                    site: $page->site,
                    language: $pageUrl->language,
                    routeName: 'capell.pages.show',
                    lastModified: $page->updated_at,
                    contentType: PublicUrlContentType::Page,
                    isSitemapEligible: true,
                    isAiDiscoveryEligible: true,
                    priority: is_numeric($page->meta['priority'] ?? null) ? number_format((float) $page->meta['priority'], 1, '.', '') : null,
                    changeFrequency: is_string($page->meta['changefreq'] ?? null) ? $page->meta['changefreq'] : null,
                    title: $page->translation->label ?? $page->name,
                );
            })
            ->filter(fn (?PublicUrlData $url): bool => $url instanceof PublicUrlData)
            ->values();
    }

    private function urlKey(PublicUrlData $url): string
    {
        return $url->site->getKey() . '|' . $url->language->getKey() . '|' . $url->canonicalUrl;
    }
}
