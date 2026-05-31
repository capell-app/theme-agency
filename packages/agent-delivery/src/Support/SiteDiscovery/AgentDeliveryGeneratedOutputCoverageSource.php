<?php

declare(strict_types=1);

namespace Capell\AgentDelivery\Support\SiteDiscovery;

use Capell\Core\Models\PageUrl;
use Capell\SiteDiscovery\Contracts\GeneratedOutputCoverageSource;
use Capell\SiteDiscovery\Data\PublicUrlRegistryEntryData;
use Capell\SiteDiscovery\Enums\PublicUrlIndexability;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

final class AgentDeliveryGeneratedOutputCoverageSource implements GeneratedOutputCoverageSource
{
    public function key(): string
    {
        return GeneratedOutputCoverageSource::AGENT_DELIVERY;
    }

    /**
     * @param  Collection<int, PublicUrlRegistryEntryData>  $registryEntries
     * @return Collection<int, string>
     */
    public function coveredUrls(Collection $registryEntries): Collection
    {
        if (! Schema::hasTable((new PageUrl)->getTable())) {
            return collect();
        }

        $registryUrlLookup = $registryEntries
            ->filter(fn (PublicUrlRegistryEntryData $entry): bool => $entry->indexability === PublicUrlIndexability::Indexable && $entry->isAiDiscoveryEligible)
            ->pluck('canonicalUrl')
            ->mapWithKeys(fn (string $url): array => [$url => true]);

        if ($registryUrlLookup->isEmpty()) {
            return collect();
        }

        return PageUrl::query()
            ->where('status', true)
            ->whereNull('type')
            ->with('siteDomain')
            ->get()
            ->map(fn (PageUrl $pageUrl): string => $pageUrl->full_url)
            ->filter(fn (string $url): bool => $registryUrlLookup->has($url))
            ->unique()
            ->values();
    }
}
