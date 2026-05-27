<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Actions;

use Capell\Core\Actions\Content\ExtractTextContentAction;
use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\Core\Models\Translation;
use Capell\Frontend\Actions\ResolvePageCanonicalUrlAction;
use Capell\Frontend\Actions\ResolvePageRobotsDirectivesAction;
use Capell\SeoSuite\Data\AiReadinessIssueData;
use Capell\SeoSuite\Enums\RobotsDirectiveEnum;
use Capell\SeoSuite\Models\AiDiscoveryPageProfile;
use Capell\SeoSuite\Models\AiDiscoverySiteProfile;
use Capell\SeoSuite\Settings\SeoSuiteSettings;
use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

/**
 * @method static Collection<int, AiReadinessIssueData> run(Page $page, Site $site, Language $language)
 */
final class BuildAiReadinessAuditAction
{
    use AsAction;

    /**
     * @return Collection<int, AiReadinessIssueData>
     */
    public function handle(Page $page, Site $site, Language $language): Collection
    {
        if (! $this->auditEnabled()) {
            return collect();
        }

        $page->loadMissing([
            'translation' => fn (BuilderContract $query): BuilderContract => $query->where('language_id', $language->getKey()),
            'pageUrl' => fn (BuilderContract $query): BuilderContract => $query->where('language_id', $language->getKey()),
            'canonicalPage.pageUrls.siteDomain',
        ]);

        $siteProfile = $this->siteProfile($site, $language);
        $pageProfile = $this->pageProfile($page, $site, $language);

        $issues = collect();
        $translation = $page->translation;

        if (trim((string) $pageProfile->summary) === '') {
            $issues->push($this->issue('missing_summary', 'warning', 'Add an AI summary for this page.', $page));
        }

        if (mb_strlen($this->title($page, $translation)) < 30) {
            $issues->push($this->issue('weak_title', 'warning', 'Use a clearer, more specific title.', $page));
        }

        if ($this->canonicalUrl($page, $language) === '') {
            $issues->push($this->issue('missing_canonical', 'warning', 'Add a canonical URL or ensure the page URL is available.', $page));
        }

        if (! $this->hasSchema($page, $translation)) {
            $issues->push($this->issue('missing_schema', 'notice', 'Add schema for entity clarity where appropriate.', $page));
        }

        if (trim($this->extractTextContent($translation)) === '') {
            $issues->push($this->issue('js_only_content', 'warning', 'Expose meaningful server-rendered text content.', $page));
        }

        if (! $siteProfile->markdown_pages_enabled) {
            $issues->push($this->issue('missing_markdown_view', 'warning', 'Enable Markdown page views for this site language.', $page));
        }

        if ($this->duplicateTitleExists($page, $site, $language, $this->title($page, $translation))) {
            $issues->push($this->issue('duplicate_entity_name', 'notice', 'Another page is using the same entity title.', $page));
        }

        if (in_array(RobotsDirectiveEnum::NoIndex->value, ResolvePageRobotsDirectivesAction::run($page, $language), true)) {
            $issues->push($this->issue('excluded_by_noindex', 'warning', 'This page is excluded by noindex.', $page));
        }

        return $issues->values();
    }

    private function issue(string $key, string $severity, string $message, Page $page): AiReadinessIssueData
    {
        return new AiReadinessIssueData($key, $severity, $message, (int) $page->getKey());
    }

    private function title(Page $page, ?Translation $translation): string
    {
        $meta = (array) $translation?->meta;

        return trim(strip_tags((string) ($meta['title'] ?? $translation->title ?? $page->name)));
    }

    private function canonicalUrl(Page $page, Language $language): string
    {
        return trim(ResolvePageCanonicalUrlAction::run($page, $language) ?? '');
    }

    private function hasSchema(Page $page, ?Translation $translation): bool
    {
        $pageMeta = (array) $page->meta;
        $translationMeta = (array) $translation?->meta;

        return filled($pageMeta['schema'] ?? null)
            || filled($pageMeta['schema_templates'] ?? null)
            || filled($translationMeta['schema'] ?? null)
            || filled($translationMeta['schema_templates'] ?? null);
    }

    /**
     * @return array<array-key, mixed>
     */
    private function extractableContent(?Translation $translation): string|array|null
    {
        $content = $translation?->content;

        return is_string($content) || is_array($content) ? $content : null;
    }

    private function extractTextContent(?Translation $translation): string
    {
        return ExtractTextContentAction::run($this->extractableContent($translation));
    }

    private function duplicateTitleExists(Page $page, Site $site, Language $language, string $title): bool
    {
        if ($title === '') {
            return false;
        }

        return Page::query()
            ->where('site_id', $site->getKey())
            ->whereKeyNot($page->getKey())
            ->whereHas('translations', function (BuilderContract $query) use ($language, $title): void {
                $query
                    ->where('language_id', $language->getKey())
                    ->where(
                        fn (BuilderContract $titleQuery): BuilderContract => $titleQuery
                            ->where('title', $title)
                            ->orWhere('meta->title', $title),
                    );
            })
            ->exists();
    }

    private function siteProfile(Site $site, Language $language): AiDiscoverySiteProfile
    {
        $cacheKey = sprintf('%s:%s', $site->getKey(), $language->getKey());
        $cachedProfiles = request()->attributes->get('capell.seo_suite.ai_readiness_site_profiles', []);
        $cachedProfile = is_array($cachedProfiles) ? ($cachedProfiles[$cacheKey] ?? null) : null;

        if ($cachedProfile instanceof AiDiscoverySiteProfile) {
            return $cachedProfile;
        }

        $profile = AiDiscoverySiteProfile::query()
            ->where('site_id', $site->getKey())
            ->where('language_id', $language->getKey())
            ->first();

        if ($profile instanceof AiDiscoverySiteProfile) {
            $cachedProfiles = is_array($cachedProfiles) ? $cachedProfiles : [];
            $cachedProfiles[$cacheKey] = $profile;
            request()->attributes->set('capell.seo_suite.ai_readiness_site_profiles', $cachedProfiles);

            return $profile;
        }

        $profile = new AiDiscoverySiteProfile([
            'site_id' => $site->getKey(),
            'language_id' => $language->getKey(),
            'markdown_pages_enabled' => true,
            'default_include_pages' => true,
        ]);

        $cachedProfiles = is_array($cachedProfiles) ? $cachedProfiles : [];
        $cachedProfiles[$cacheKey] = $profile;
        request()->attributes->set('capell.seo_suite.ai_readiness_site_profiles', $cachedProfiles);

        return $profile;
    }

    private function pageProfile(Page $page, Site $site, Language $language): AiDiscoveryPageProfile
    {
        $cacheKey = sprintf('%s:%s:%s', $page->getKey(), $site->getKey(), $language->getKey());
        $cachedProfiles = request()->attributes->get('capell.seo_suite.ai_readiness_page_profiles', []);
        $cachedProfile = is_array($cachedProfiles) ? ($cachedProfiles[$cacheKey] ?? null) : null;

        if ($cachedProfile instanceof AiDiscoveryPageProfile) {
            return $cachedProfile;
        }

        $profile = AiDiscoveryPageProfile::query()
            ->where('page_id', $page->getKey())
            ->where('site_id', $site->getKey())
            ->where('language_id', $language->getKey())
            ->first();

        if ($profile instanceof AiDiscoveryPageProfile) {
            $cachedProfiles = is_array($cachedProfiles) ? $cachedProfiles : [];
            $cachedProfiles[$cacheKey] = $profile;
            request()->attributes->set('capell.seo_suite.ai_readiness_page_profiles', $cachedProfiles);

            return $profile;
        }

        $profile = new AiDiscoveryPageProfile([
            'page_id' => $page->getKey(),
            'site_id' => $site->getKey(),
            'language_id' => $language->getKey(),
            'include_in_ai_index' => true,
            'section' => 'Pages',
            'priority' => 500,
        ]);

        $cachedProfiles = is_array($cachedProfiles) ? $cachedProfiles : [];
        $cachedProfiles[$cacheKey] = $profile;
        request()->attributes->set('capell.seo_suite.ai_readiness_page_profiles', $cachedProfiles);

        return $profile;
    }

    private function auditEnabled(): bool
    {
        $cacheKey = 'capell.seo_suite.ai_readiness_audit_enabled';
        $cached = request()->attributes->get($cacheKey);

        if (is_bool($cached)) {
            return $cached;
        }

        try {
            $enabled = resolve(SeoSuiteSettings::class)->ai_discovery_audit_enabled;
        } catch (Throwable) {
            $enabled = true;
        }

        request()->attributes->set($cacheKey, $enabled);

        return $enabled;
    }
}
