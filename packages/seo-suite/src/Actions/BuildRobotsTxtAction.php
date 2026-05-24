<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Actions;

use Capell\Core\Models\Site;
use Capell\Core\Models\SiteDomain;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static string run(?Site $site = null)
 */
final class BuildRobotsTxtAction
{
    use AsAction;

    public function handle(?Site $site = null): string
    {
        $sections = array_filter([
            $this->standardRules($site),
            trim(BuildAiRobotsTxtRulesAction::run($site)),
        ], static fn (string $section): bool => $section !== '');

        return implode("\n\n", $sections) . "\n";
    }

    private function standardRules(?Site $site): string
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
        ];

        foreach ($this->sitemapUrls($site) as $sitemapUrl) {
            $lines[] = 'Sitemap: ' . $sitemapUrl;
        }

        return implode("\n", $lines);
    }

    /**
     * @return array<int, string>
     */
    private function sitemapUrls(?Site $site): array
    {
        if (! $site instanceof Site) {
            return [];
        }

        $site->loadMissing(['siteDomains' => fn ($query) => $query->enabled()->with('language')]);

        $configuredPath = config('capell.sitemap.xml_path', '/sitemap-xml');
        $xmlPath = '/' . ltrim(is_string($configuredPath) ? $configuredPath : '/sitemap-xml', '/');

        return $site->siteDomains
            ->filter(fn (SiteDomain $siteDomain): bool => $siteDomain->status)
            ->map(fn (SiteDomain $siteDomain): string => $siteDomain->full_url !== null
                ? rtrim($siteDomain->full_url, '/') . $xmlPath
                : null)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
