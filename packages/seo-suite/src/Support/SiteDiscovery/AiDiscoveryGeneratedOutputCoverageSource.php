<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Support\SiteDiscovery;

use Capell\Core\Models\Page;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\SiteDomain;
use Capell\SeoSuite\Enums\AiDiscoveryStatusEnum;
use Capell\SeoSuite\Models\AiDiscoveryPageProfile;
use Capell\SeoSuite\Models\AiDiscoverySiteProfile;
use Capell\SiteDiscovery\Contracts\GeneratedOutputCoverageSource;
use Capell\SiteDiscovery\Data\PublicUrlRegistryEntryData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

final class AiDiscoveryGeneratedOutputCoverageSource implements GeneratedOutputCoverageSource
{
    public function key(): string
    {
        return GeneratedOutputCoverageSource::AI_DISCOVERY;
    }

    /**
     * @param  Collection<int, PublicUrlRegistryEntryData>  $registryEntries
     * @return Collection<int, string>
     */
    public function coveredUrls(Collection $registryEntries): Collection
    {
        if (! Schema::hasTable((new AiDiscoveryPageProfile)->getTable()) || ! Schema::hasTable((new AiDiscoverySiteProfile)->getTable())) {
            return collect();
        }

        $enabledProfileKeys = AiDiscoverySiteProfile::query()
            ->where(function (Builder $query): void {
                $query
                    ->where('llms_txt_enabled', true)
                    ->orWhere('llms_full_txt_enabled', true)
                    ->orWhere('markdown_pages_enabled', true);
            })
            ->where('status', '!=', AiDiscoveryStatusEnum::Disabled->value)
            ->get(['site_id', 'language_id'])
            ->mapWithKeys(fn (AiDiscoverySiteProfile $profile): array => [
                $this->profileKey((int) $profile->site_id, (int) $profile->language_id) => true,
            ]);

        if ($enabledProfileKeys->isEmpty()) {
            return collect();
        }

        return AiDiscoveryPageProfile::query()
            ->where('include_in_ai_index', true)
            ->with(['page.pageUrl.siteDomain'])
            ->get()
            ->filter(fn (AiDiscoveryPageProfile $profile): bool => $enabledProfileKeys->has(
                $this->profileKey((int) $profile->site_id, (int) $profile->language_id),
            ))
            ->map(fn (AiDiscoveryPageProfile $profile): ?string => $this->pageUrl($profile->page))
            ->filter(fn (?string $url): bool => $url !== null)
            ->unique()
            ->values();
    }

    private function profileKey(int $siteId, int $languageId): string
    {
        return $siteId . ':' . $languageId;
    }

    private function pageUrl(?Page $page): ?string
    {
        $pageUrl = $page?->pageUrl;

        if (! $pageUrl instanceof PageUrl) {
            return null;
        }

        if ($pageUrl->relationLoaded('siteDomain') && ! $pageUrl->siteDomain instanceof SiteDomain) {
            return null;
        }

        return $pageUrl->full_url;
    }
}
