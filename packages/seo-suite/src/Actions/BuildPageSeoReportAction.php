<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Actions;

use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\Core\Models\Translation;
use Capell\Frontend\Actions\ResolvePageCanonicalUrlAction;
use Capell\Frontend\Actions\ResolvePageRobotsDirectivesAction;
use Capell\SeoSuite\Data\InternalLinkSuggestionData;
use Capell\SeoSuite\Data\PageSeoReportData;
use Capell\SeoSuite\Data\RedirectOpportunityData;
use Capell\SeoSuite\Data\SchemaTemplateReportData;
use Capell\SeoSuite\Data\SearchConsoleInsightData;
use Capell\SeoSuite\Data\SeoIssueData;
use Capell\SeoSuite\Data\SeoPreviewData;
use Capell\SeoSuite\Data\SocialMetaData;
use Capell\SeoSuite\Enums\RobotsDirectiveEnum;
use Capell\SeoSuite\Enums\SeoCheckKeyEnum;
use Capell\SeoSuite\Enums\SeoIssueSeverityEnum;
use Capell\SeoSuite\Settings\SeoSuiteSettings;
use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

/**
 * @method static PageSeoReportData run(Page $page, Site $site, Language $language)
 */
final class BuildPageSeoReportAction
{
    use AsAction;

    public function handle(Page $page, Site $site, Language $language): PageSeoReportData
    {
        $page->load([
            'translation' => fn (BuilderContract $query): BuilderContract => $query->where('language_id', $language->id),
            'pageUrl' => fn (BuilderContract $query): BuilderContract => $query->where('language_id', $language->id),
            'pageUrl.siteDomain',
            'canonicalPage.pageUrls.siteDomain',
            'image',
            'site',
            'socialImage',
            'translations',
        ]);

        $site->load([
            'image',
            'translation' => fn (BuilderContract $query): BuilderContract => $query->where('language_id', $language->id),
        ]);

        $issues = [];
        $settings = resolve(SeoSuiteSettings::class);
        $metaTitle = $this->metaValue($page, 'title');
        $metaDescription = $this->metaValue($page, 'description');

        if ($settings->seo_audit_enabled && $settings->seo_check_meta_title) {
            $this->addLengthIssue(
                issues: $issues,
                key: SeoCheckKeyEnum::MetaTitle,
                value: $metaTitle,
                minimum: 30,
                maximum: 70,
                missingMessage: __('capell-seo-suite::generic.seo_issue_meta_title_missing'),
                shortMessage: __('capell-seo-suite::generic.seo_issue_meta_title_short'),
                longMessage: __('capell-seo-suite::generic.seo_issue_meta_title_long'),
            );
        }

        if ($settings->seo_audit_enabled && $settings->seo_check_meta_description) {
            $this->addLengthIssue(
                issues: $issues,
                key: SeoCheckKeyEnum::MetaDescription,
                value: $metaDescription,
                minimum: 50,
                maximum: 160,
                missingMessage: __('capell-seo-suite::generic.seo_issue_meta_description_missing'),
                shortMessage: __('capell-seo-suite::generic.seo_issue_meta_description_short'),
                longMessage: __('capell-seo-suite::generic.seo_issue_meta_description_long'),
            );
        }

        if ($settings->seo_audit_enabled && $settings->seo_check_duplicate_title && $metaTitle !== null && $this->duplicateTitleExists($page, $site, $language, $metaTitle)) {
            $issues[] = new SeoIssueData(
                key: SeoCheckKeyEnum::DuplicateTitle,
                severity: SeoIssueSeverityEnum::Warning,
                message: __('capell-seo-suite::generic.seo_issue_duplicate_title'),
            );
        }

        $robotsDirectives = array_values(ResolvePageRobotsDirectivesAction::run($page, $language));

        if ($settings->seo_audit_enabled && $this->hasNoIndexDirective($robotsDirectives)) {
            $issues[] = new SeoIssueData(
                key: SeoCheckKeyEnum::Robots,
                severity: SeoIssueSeverityEnum::Warning,
                message: __('capell-seo-suite::generic.seo_issue_robots_noindex'),
            );
        }

        $previewUrl = $this->previewUrl($page);
        $canonicalUrl = ResolvePageCanonicalUrlAction::run($page, $language) ?? $previewUrl;
        $socialMeta = BuildSocialMetaAction::run($page, $site, $language);
        $internalLinkSuggestions = SuggestInternalLinksAction::run($page, $site, $language);
        $schemaDashboardReports = BuildSchemaTemplateReportAction::run($page, $site, $language);
        $redirectOpportunities = BuildRedirectOpportunityReportAction::run($site->id, $language->id, (int) $page->getKey());
        $searchConsoleInsights = array_values(BuildPageSearchConsoleInsightsAction::run($page));

        if ($settings->seo_audit_enabled) {
            $this->addTechnicalIssues(
                issues: $issues,
                page: $page,
                site: $site,
                language: $language,
                previewUrl: $previewUrl,
                canonicalUrl: $canonicalUrl,
                socialMeta: $socialMeta,
                internalLinkSuggestions: $internalLinkSuggestions,
                schemaDashboardReports: $schemaDashboardReports,
                redirectOpportunities: $redirectOpportunities,
                searchConsoleInsights: $searchConsoleInsights,
            );
        }

        $searchTitle = $metaTitle
            ?? $this->stringValue($page->translation?->title)
            ?? $this->stringValue($page->translation?->label)
            ?? $this->stringValue($page->name)
            ?? '';
        $searchDescription = $metaDescription ?? '';
        $siteName = $this->stringValue($site->translation?->title);

        $searchPreview = new SeoPreviewData(
            title: $searchTitle,
            description: $searchDescription,
            url: $previewUrl,
            siteName: $siteName,
        );

        $socialPreview = new SeoPreviewData(
            title: $socialMeta->title !== '' ? $socialMeta->title : $searchTitle,
            description: $socialMeta->description !== '' ? $socialMeta->description : $searchDescription,
            url: $previewUrl,
            imageUrl: $socialMeta->imageUrl,
            siteName: $siteName,
        );

        return new PageSeoReportData(
            score: CalculateSeoScoreAction::run($issues),
            searchPreview: $searchPreview,
            socialPreview: $socialPreview,
            issues: $issues,
            passedChecks: $this->passedChecks($issues, $settings),
            internalLinkSuggestions: $internalLinkSuggestions,
            schemaDashboardReports: $schemaDashboardReports,
            redirectOpportunities: $redirectOpportunities,
            searchConsoleInsights: $searchConsoleInsights,
            canonicalUrl: $canonicalUrl,
            robotsDirectives: $robotsDirectives,
            intelligenceSummary: BuildPageIntelligenceSummaryAction::run($page, $site, $language),
        );
    }

    private function metaValue(Page $page, string $key): ?string
    {
        $translation = $page->translation;

        if (! $translation instanceof Translation) {
            return null;
        }

        $value = $translation->getMeta($key);

        return $this->stringValue($value);
    }

    /**
     * @param  list<SeoIssueData>  $issues
     */
    private function addLengthIssue(
        array &$issues,
        SeoCheckKeyEnum $key,
        ?string $value,
        int $minimum,
        int $maximum,
        string $missingMessage,
        string $shortMessage,
        string $longMessage,
    ): void {
        if ($value === null) {
            $issues[] = new SeoIssueData(
                key: $key,
                severity: SeoIssueSeverityEnum::Critical,
                message: $missingMessage,
            );

            return;
        }

        $length = mb_strlen($value);

        if ($length < $minimum) {
            $issues[] = new SeoIssueData(
                key: $key,
                severity: SeoIssueSeverityEnum::Warning,
                message: $shortMessage,
            );

            return;
        }

        if ($length > $maximum) {
            $issues[] = new SeoIssueData(
                key: $key,
                severity: SeoIssueSeverityEnum::Warning,
                message: $longMessage,
            );
        }
    }

    private function duplicateTitleExists(Page $page, Site $site, Language $language, string $title): bool
    {
        return Page::query()
            ->where('site_id', $site->id)
            ->whereKeyNot($page->getKey())
            ->whereHas('translations', function (BuilderContract $query) use ($language, $title): void {
                $query
                    ->where('language_id', $language->id)
                    ->where('meta->title', $title);
            })
            ->exists();
    }

    /**
     * @param  list<string>  $robotsDirectives
     */
    private function hasNoIndexDirective(array $robotsDirectives): bool
    {
        return in_array(RobotsDirectiveEnum::NoIndex->value, $robotsDirectives, true);
    }

    /**
     * @param  list<SeoIssueData>  $issues
     * @param  list<InternalLinkSuggestionData>  $internalLinkSuggestions
     * @param  list<SchemaTemplateReportData>  $schemaDashboardReports
     * @param  list<RedirectOpportunityData>  $redirectOpportunities
     * @param  list<SearchConsoleInsightData>  $searchConsoleInsights
     */
    private function addTechnicalIssues(
        array &$issues,
        Page $page,
        Site $site,
        Language $language,
        string $previewUrl,
        ?string $canonicalUrl,
        SocialMetaData $socialMeta,
        array $internalLinkSuggestions,
        array $schemaDashboardReports,
        array $redirectOpportunities,
        array $searchConsoleInsights,
    ): void {
        if ($canonicalUrl === null || trim($canonicalUrl) === '') {
            $issues[] = $this->issue(SeoCheckKeyEnum::Canonical, SeoIssueSeverityEnum::Warning, 'seo_issue_canonical_missing');
        }

        if ($previewUrl === '') {
            $issues[] = $this->issue(SeoCheckKeyEnum::Sitemap, SeoIssueSeverityEnum::Warning, 'seo_issue_sitemap_url_missing');
        }

        if ($socialMeta->imageUrl === null || trim($socialMeta->imageUrl) === '') {
            $issues[] = $this->issue(SeoCheckKeyEnum::SocialImage, SeoIssueSeverityEnum::Notice, 'seo_issue_social_image_missing');
        } elseif ($socialMeta->imageAlt === null || trim((string) $socialMeta->imageAlt) === '') {
            $issues[] = $this->issue(SeoCheckKeyEnum::ImageAltText, SeoIssueSeverityEnum::Warning, 'seo_issue_image_alt_text_missing');
        }

        $schemaIssue = $this->schemaIssue($schemaDashboardReports);
        if ($schemaIssue instanceof SeoIssueData) {
            $issues[] = $schemaIssue;
        }

        if ($internalLinkSuggestions === []) {
            $issues[] = $this->issue(SeoCheckKeyEnum::InternalLinks, SeoIssueSeverityEnum::Notice, 'seo_issue_internal_links_missing');
        }

        if ($redirectOpportunities !== []) {
            $issues[] = $this->issue(SeoCheckKeyEnum::Redirects, SeoIssueSeverityEnum::Warning, 'seo_issue_redirects_available');
            $issues[] = $this->issue(SeoCheckKeyEnum::BrokenLinks, SeoIssueSeverityEnum::Warning, 'seo_issue_broken_links_found');
        }

        $searchConsoleIssue = $this->searchConsoleIssue($searchConsoleInsights);
        if ($searchConsoleIssue instanceof SeoIssueData) {
            $issues[] = $searchConsoleIssue;
        }

        if (! $this->hasRequestedTranslation($page, $language)) {
            $issues[] = $this->issue(SeoCheckKeyEnum::TranslationCoverage, SeoIssueSeverityEnum::Warning, 'seo_issue_translation_coverage_missing');
        }

        if (! $this->isAiDiscoveryReady($page, $site, $language)) {
            $issues[] = $this->issue(SeoCheckKeyEnum::LlmsTxt, SeoIssueSeverityEnum::Notice, 'seo_issue_llms_txt_unavailable');
        }
    }

    private function issue(SeoCheckKeyEnum $key, SeoIssueSeverityEnum $severity, string $messageKey): SeoIssueData
    {
        return new SeoIssueData(
            key: $key,
            severity: $severity,
            message: __('capell-seo-suite::generic.' . $messageKey),
        );
    }

    /**
     * @param  list<SchemaTemplateReportData>  $schemaDashboardReports
     */
    private function schemaIssue(array $schemaDashboardReports): ?SeoIssueData
    {
        if ($schemaDashboardReports === []) {
            return $this->issue(SeoCheckKeyEnum::Schema, SeoIssueSeverityEnum::Warning, 'seo_issue_schema_missing');
        }

        foreach ($schemaDashboardReports as $schemaReport) {
            if (! $schemaReport instanceof SchemaTemplateReportData) {
                continue;
            }

            if ($schemaReport->severity === SeoIssueSeverityEnum::Critical) {
                return $this->issue(SeoCheckKeyEnum::Schema, SeoIssueSeverityEnum::Critical, 'seo_issue_schema_required_fields_missing');
            }

            if ($schemaReport->severity === SeoIssueSeverityEnum::Warning || $schemaReport->missingFields !== [] || $schemaReport->warnings !== []) {
                return $this->issue(SeoCheckKeyEnum::Schema, SeoIssueSeverityEnum::Warning, 'seo_issue_schema_warnings');
            }
        }

        return null;
    }

    /**
     * @param  list<SearchConsoleInsightData>  $searchConsoleInsights
     */
    private function searchConsoleIssue(array $searchConsoleInsights): ?SeoIssueData
    {
        if ($searchConsoleInsights === []) {
            return $this->issue(SeoCheckKeyEnum::SearchConsole, SeoIssueSeverityEnum::Notice, 'seo_issue_search_console_missing');
        }

        foreach ($searchConsoleInsights as $insight) {
            if (! $insight instanceof SearchConsoleInsightData) {
                continue;
            }

            if ($insight->severity !== SeoIssueSeverityEnum::Passed) {
                return new SeoIssueData(
                    key: SeoCheckKeyEnum::SearchConsole,
                    severity: $insight->severity,
                    message: $insight->message,
                );
            }
        }

        return null;
    }

    private function hasRequestedTranslation(Page $page, Language $language): bool
    {
        foreach ($page->translations as $translation) {
            if ($translation instanceof Translation && (int) $translation->language_id === (int) $language->getKey()) {
                return true;
            }
        }

        return false;
    }

    private function isAiDiscoveryReady(Page $page, Site $site, Language $language): bool
    {
        try {
            return PageIsDiscoverableForAiDiscoveryAction::run($page, $site, $language);
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @param  list<SeoIssueData>  $issues
     * @return list<SeoIssueData>
     */
    private function passedChecks(array $issues, SeoSuiteSettings $settings): array
    {
        if (! $settings->seo_audit_enabled) {
            return [];
        }

        $issueKeys = collect($issues)->map(fn (SeoIssueData $issue): SeoCheckKeyEnum => $issue->key);
        $passedChecks = [];

        foreach ($this->enabledChecks($settings) as $checkKey) {
            if ($issueKeys->contains($checkKey)) {
                continue;
            }

            $passedChecks[] = new SeoIssueData(
                key: $checkKey,
                severity: SeoIssueSeverityEnum::Passed,
                message: __('capell-seo-suite::generic.seo_check_passed', ['check' => $checkKey->getLabel()]),
            );
        }

        return $passedChecks;
    }

    /**
     * @return list<SeoCheckKeyEnum>
     */
    private function enabledChecks(SeoSuiteSettings $settings): array
    {
        $checks = [];

        if ($settings->seo_check_meta_title) {
            $checks[] = SeoCheckKeyEnum::MetaTitle;
        }

        if ($settings->seo_check_meta_description) {
            $checks[] = SeoCheckKeyEnum::MetaDescription;
        }

        if ($settings->seo_check_duplicate_title) {
            $checks[] = SeoCheckKeyEnum::DuplicateTitle;
        }

        $checks[] = SeoCheckKeyEnum::SocialImage;
        $checks[] = SeoCheckKeyEnum::Canonical;
        $checks[] = SeoCheckKeyEnum::Robots;
        $checks[] = SeoCheckKeyEnum::ImageAltText;
        $checks[] = SeoCheckKeyEnum::InternalLinks;
        $checks[] = SeoCheckKeyEnum::Schema;
        $checks[] = SeoCheckKeyEnum::BrokenLinks;
        $checks[] = SeoCheckKeyEnum::Redirects;
        $checks[] = SeoCheckKeyEnum::TranslationCoverage;
        $checks[] = SeoCheckKeyEnum::Sitemap;
        $checks[] = SeoCheckKeyEnum::LlmsTxt;
        $checks[] = SeoCheckKeyEnum::SearchConsole;

        return $checks;
    }

    private function previewUrl(Page $page): string
    {
        if ($page->pageUrl === null) {
            return '';
        }

        try {
            return $this->stringValue($page->pageUrl->full_url) ?? $this->stringValue($page->pageUrl->url) ?? '';
        } catch (Throwable) {
            return $this->stringValue($page->pageUrl->url) ?? '';
        }
    }

    private function stringValue(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $stringValue = trim(strip_tags((string) $value));

        return $stringValue !== '' ? $stringValue : null;
    }
}
