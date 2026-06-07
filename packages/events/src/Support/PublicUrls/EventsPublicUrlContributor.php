<?php

declare(strict_types=1);

namespace Capell\Events\Support\PublicUrls;

use Capell\Core\Models\Language;
use Capell\Core\Models\Site;
use Capell\Core\Models\SiteDomain;
use Capell\Events\Actions\QueryPublicEventOccurrencesAction;
use Capell\Events\Models\EventOccurrence;
use Capell\Events\Providers\EventsServiceProvider;
use Capell\SiteDiscovery\Contracts\PublicUrlContributor;
use Capell\SiteDiscovery\Data\PublicUrlData;
use Capell\SiteDiscovery\Enums\PublicUrlContentType;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

final class EventsPublicUrlContributor implements PublicUrlContributor
{
    /**
     * @return Collection<int, PublicUrlData>
     */
    public function publicUrls(): Collection
    {
        return SiteDomain::query()
            ->with(['site', 'language'])
            ->get()
            ->filter(fn (SiteDomain $domain): bool => $domain->site instanceof Site && $domain->language instanceof Language)
            ->flatMap(fn (SiteDomain $domain): Collection => $this->publicUrlsForDomain($domain))
            ->unique(fn (PublicUrlData $url): string => $url->site->getKey() . '|' . $url->language->getKey() . '|' . $url->canonicalUrl)
            ->values();
    }

    /**
     * @return Collection<int, PublicUrlData>
     */
    private function publicUrlsForDomain(SiteDomain $domain): Collection
    {
        $site = $domain->site;
        $language = $domain->language;

        if (! $site instanceof Site || ! $language instanceof Language) {
            return collect();
        }

        return QueryPublicEventOccurrencesAction::run(
            site: $site,
            startsAt: CarbonImmutable::now()->subWeek(),
            endsAt: CarbonImmutable::now()->addYear(),
        )
            ->map(fn (EventOccurrence $occurrence): ?PublicUrlData => $this->publicUrlForOccurrence($occurrence, $site, $language))
            ->filter(fn (?PublicUrlData $url): bool => $url instanceof PublicUrlData)
            ->values();
    }

    private function publicUrlForOccurrence(EventOccurrence $occurrence, Site $site, Language $language): ?PublicUrlData
    {
        $url = $occurrence->loadMissing('event.pageUrl')->occurrenceUrl();

        if ($url === null || $url === '') {
            return null;
        }

        return new PublicUrlData(
            canonicalUrl: $url,
            sourcePackage: EventsServiceProvider::$packageName,
            site: $site,
            language: $language,
            routeName: 'capell-events.event',
            lastModified: $occurrence->updated_at,
            contentType: PublicUrlContentType::Page,
            isSitemapEligible: true,
            isAiDiscoveryEligible: true,
            changeFrequency: 'daily',
            title: $occurrence->event->name,
        );
    }
}
