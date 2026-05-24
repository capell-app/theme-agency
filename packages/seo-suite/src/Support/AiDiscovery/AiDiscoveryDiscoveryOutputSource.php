<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Support\AiDiscovery;

use Capell\Core\Models\Language;
use Capell\Core\Models\Site;
use Capell\Core\Models\SiteDomain;
use Capell\SeoSuite\Actions\ResolveAiDiscoveryProfileAction;
use Capell\SeoSuite\Enums\AiDiscoveryStatusEnum;
use Capell\SeoSuite\Models\AiDiscoverySiteProfile;
use Capell\SiteDiscovery\Contracts\DiscoveryOutputSource;
use Capell\SiteDiscovery\Data\DiscoveryOutputData;
use Illuminate\Support\Collection;
use LogicException;

final class AiDiscoveryDiscoveryOutputSource implements DiscoveryOutputSource
{
    /**
     * @return Collection<int, DiscoveryOutputData>
     */
    public function discover(Site $site, Language $language, ?SiteDomain $domain = null): Collection
    {
        $siteProfile = ResolveAiDiscoveryProfileAction::run($site, $language);

        throw_unless($siteProfile instanceof AiDiscoverySiteProfile, LogicException::class, 'Resolving an AI Discovery site profile returned an unexpected page profile.');

        $siteDomain = $domain ?? $site->siteDomain;

        if (! $siteDomain instanceof SiteDomain) {
            return collect();
        }

        return $this->outputsForProfile($siteProfile, $siteDomain);
    }

    /**
     * @return Collection<int, DiscoveryOutputData>
     */
    public function outputsForProfile(AiDiscoverySiteProfile $siteProfile, SiteDomain $domain): Collection
    {
        if ($siteProfile->status === AiDiscoveryStatusEnum::Disabled) {
            return collect();
        }

        $baseUrl = rtrim($domain->full_url, '/');
        $outputs = [];

        if ($siteProfile->llms_txt_enabled) {
            $outputs[] = new DiscoveryOutputData(
                key: 'seo-suite.llms-txt',
                url: $baseUrl . '/llms.txt',
                contentType: 'text/markdown; charset=utf-8',
                label: 'llms.txt',
                description: 'Public AI discovery index for this site and language.',
            );
        }

        if ($siteProfile->llms_full_txt_enabled) {
            $outputs[] = new DiscoveryOutputData(
                key: 'seo-suite.llms-full-txt',
                url: $baseUrl . '/llms-full.txt',
                contentType: 'text/markdown; charset=utf-8',
                label: 'llms-full.txt',
                description: 'Optional expanded public AI discovery document for this site and language.',
            );
        }

        if ($siteProfile->markdown_pages_enabled) {
            $outputs[] = new DiscoveryOutputData(
                key: 'seo-suite.page-markdown-index',
                url: $baseUrl . '/index.md',
                contentType: 'text/markdown; charset=utf-8',
                label: 'Markdown page index',
                description: 'Public Markdown representation of the resolved homepage.',
            );
        }

        return collect($outputs);
    }
}
