<?php

declare(strict_types=1);

namespace Capell\AiCreator\Actions;

use Capell\AiCreator\Data\AiCreatorPackageRecommendationData;
use Capell\AiCreator\Enums\AiCreatorRecommendationLevel;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

final class RecommendAiCreatorPackagesAction
{
    use AsAction;

    /**
     * @return list<AiCreatorPackageRecommendationData>
     */
    public function handle(string $intent): array
    {
        $normalizedIntent = Str::of($intent)->lower()->squish()->toString();
        $recommendations = [];

        if ($this->mentionsAny($normalizedIntent, ['layout', 'landing page', 'homepage', 'section', 'widget', 'block', 'page builder', 'compose'])) {
            $recommendations[] = new AiCreatorPackageRecommendationData(
                package: 'capell-app/layout-builder',
                level: AiCreatorRecommendationLevel::Required,
                reason: 'Complex page composition needs Layout Builder containers, widgets, and scoped widget assets.',
                consequence: 'AI Creator can still draft copy, but it cannot apply a structured page layout without Layout Builder.',
            );
        }

        if ($this->mentionsAny($normalizedIntent, ['theme', 'client theme', 'visual system', 'brand', 'design'])) {
            $recommendations[] = new AiCreatorPackageRecommendationData(
                package: 'capell-app/theme-foundation',
                level: AiCreatorRecommendationLevel::Required,
                reason: 'App-local client themes should extend Foundation Theme before premium theme work starts.',
                consequence: 'Theme work can be previewed as a file bundle, but it should not be applied without the theme baseline.',
            );
        }

        $recommendations = [
            ...$recommendations,
            ...$this->recommendedContentPackages($normalizedIntent),
            ...$this->recommendedGrowthPackages($normalizedIntent),
        ];

        return $this->uniqueByPackage($recommendations);
    }

    /**
     * @return list<AiCreatorPackageRecommendationData>
     */
    private function recommendedContentPackages(string $intent): array
    {
        $recommendations = [];

        if ($this->mentionsAny($intent, ['section', 'testimonial', 'faq', 'faqs', 'feature', 'service block', 'landing'])) {
            $recommendations[] = new AiCreatorPackageRecommendationData(
                package: 'capell-app/content-sections',
                level: AiCreatorRecommendationLevel::Recommended,
                reason: 'Reusable content sections keep marketing blocks editable across pages.',
                consequence: 'The plan can continue, but repeated sections may become one-off page content.',
            );
        }

        if ($this->mentionsAny($intent, ['service', 'location', 'team', 'case study', 'resource', 'product'])) {
            $recommendations[] = new AiCreatorPackageRecommendationData(
                package: 'capell-app/structured-content-library',
                level: AiCreatorRecommendationLevel::Recommended,
                reason: 'Structured Content Library keeps business records reusable instead of storing them as loose page HTML.',
                consequence: 'The plan can continue, but business records may be harder to reuse across themes and pages.',
            );
        }

        if ($this->mentionsAny($intent, ['article', 'articles', 'blog', 'news', 'insight', 'author', 'archive'])) {
            $recommendations[] = new AiCreatorPackageRecommendationData(
                package: 'capell-app/blog',
                level: AiCreatorRecommendationLevel::Recommended,
                reason: 'Blog provides article workflows, archives, author pages, tags, and sitemap contributions.',
                consequence: 'AI Creator can draft page content, but article publishing workflows will be missing.',
            );
        }

        if ($this->mentionsAny($intent, ['form', 'contact', 'quote', 'lead', 'signup', 'submission', 'enquiry'])) {
            $recommendations[] = new AiCreatorPackageRecommendationData(
                package: 'capell-app/form-builder',
                level: AiCreatorRecommendationLevel::Recommended,
                reason: 'Form Builder turns generated conversion sections into managed forms and submissions.',
                consequence: 'The plan can include static contact paths, but not editor-managed submissions.',
            );
        }

        if ($this->mentionsAny($intent, ['newsletter', 'subscriber', 'audience', 'email updates'])) {
            $recommendations[] = new AiCreatorPackageRecommendationData(
                package: 'capell-app/newsletter',
                level: AiCreatorRecommendationLevel::Recommended,
                reason: 'Newsletter adds audience capture, subscription state, imports, and public subscription routes.',
                consequence: 'Signup copy can be drafted, but subscriber workflows will not be available.',
            );
        }

        if ($this->mentionsAny($intent, ['event', 'venue', 'calendar', 'registration', 'ical'])) {
            $recommendations[] = new AiCreatorPackageRecommendationData(
                package: 'capell-app/events',
                level: AiCreatorRecommendationLevel::Recommended,
                reason: 'Events adds event records, venues, occurrences, registrations, calendar pages, and iCalendar feeds.',
                consequence: 'Event copy can be drafted, but date-aware event workflows will be missing.',
            );
        }

        if ($this->mentionsAny($intent, ['image', 'gallery', 'media', 'download', 'asset'])) {
            $recommendations[] = new AiCreatorPackageRecommendationData(
                package: 'capell-app/media-library',
                level: AiCreatorRecommendationLevel::Recommended,
                reason: 'Media Library provides managed images, documents, downloads, and gallery assets.',
                consequence: 'The plan can reference media needs, but it cannot attach managed assets.',
            );
        }

        if ($this->mentionsAny($intent, ['alt text', 'media metadata', 'image suggestion', 'image cleanup'])) {
            $recommendations[] = new AiCreatorPackageRecommendationData(
                package: 'capell-app/media-ai',
                level: AiCreatorRecommendationLevel::Recommended,
                reason: 'Media AI adds AI-assisted media metadata, alt text, and cleanup workflows.',
                consequence: 'Media still works, but image intelligence remains manual.',
            );
        }

        return $recommendations;
    }

    /**
     * @return list<AiCreatorPackageRecommendationData>
     */
    private function recommendedGrowthPackages(string $intent): array
    {
        $recommendations = [];

        if ($this->mentionsAny($intent, ['seo', 'metadata', 'structured data', 'launch', 'landing page', 'campaign'])) {
            $recommendations[] = new AiCreatorPackageRecommendationData(
                package: 'capell-app/seo-suite',
                level: AiCreatorRecommendationLevel::Recommended,
                reason: 'SEO Suite adds metadata drafts, structured data, audits, sitemaps, and AI-assisted SEO follow-up.',
                consequence: 'Pages can be created, but search readiness checks and structured SEO output will be limited.',
            );
        }

        if ($this->mentionsAny($intent, ['review', 'approval', 'schedule', 'release', 'workspace', 'publish'])) {
            $recommendations[] = new AiCreatorPackageRecommendationData(
                package: 'capell-app/publishing-studio',
                level: AiCreatorRecommendationLevel::Recommended,
                reason: 'Publishing Studio adds review, scheduling, approvals, release workspaces, and controlled publishing.',
                consequence: 'AI-created changes can be saved, but editorial release controls will be weaker.',
            );
        }

        if ($this->mentionsAny($intent, ['menu', 'navigation', 'footer', 'breadcrumb', 'site structure'])) {
            $recommendations[] = new AiCreatorPackageRecommendationData(
                package: 'capell-app/navigation',
                level: AiCreatorRecommendationLevel::Recommended,
                reason: 'Navigation lets AI-created pages be placed into menus, footers, breadcrumbs, and site structure.',
                consequence: 'Pages can exist, but editors will need to wire navigation manually.',
            );
        }

        if ($this->mentionsAny($intent, ['search', 'resource library', 'documentation', 'large content'])) {
            $recommendations[] = new AiCreatorPackageRecommendationData(
                package: 'capell-app/search',
                level: AiCreatorRecommendationLevel::Recommended,
                reason: 'Search improves resource libraries, documentation sites, blogs, and larger content collections.',
                consequence: 'Generated content can publish, but public search and search insights will be missing.',
            );
        }

        if ($this->mentionsAny($intent, ['campaign', 'launch', 'promotion'])) {
            $recommendations[] = new AiCreatorPackageRecommendationData(
                package: 'capell-app/campaign-studio',
                level: AiCreatorRecommendationLevel::Recommended,
                reason: 'Campaign Studio supports campaign landing pages, launches, and coordinated promotional workflows.',
                consequence: 'The content can be created, but campaign coordination remains manual.',
            );
        }

        if ($this->mentionsAny($intent, ['inline edit', 'frontend authoring', 'edit on page'])) {
            $recommendations[] = new AiCreatorPackageRecommendationData(
                package: 'capell-app/frontend-authoring',
                level: AiCreatorRecommendationLevel::Optional,
                reason: 'Frontend Authoring adds authenticated post-load inline editing controls for admins.',
                consequence: 'Admins can still edit in Capell Admin, but not directly from the rendered page.',
            );
        }

        if ($this->mentionsAny($intent, ['performance', 'cache', 'high traffic', 'speed'])) {
            $recommendations[] = new AiCreatorPackageRecommendationData(
                package: 'capell-app/html-cache',
                level: AiCreatorRecommendationLevel::Recommended,
                reason: 'HTML Cache helps generated public pages serve quickly and safely after review.',
                consequence: 'Pages can publish, but high-traffic static delivery is not enabled.',
            );

            $recommendations[] = new AiCreatorPackageRecommendationData(
                package: 'capell-app/frontend-optimizer',
                level: AiCreatorRecommendationLevel::Recommended,
                reason: 'Frontend Optimizer improves CSS and JavaScript delivery for public pages.',
                consequence: 'Generated pages can work, but frontend delivery may be less efficient.',
            );
        }

        if ($this->mentionsAny($intent, ['analytics', 'measure', 'growth', 'visibility', 'traffic', 'report'])) {
            $recommendations[] = new AiCreatorPackageRecommendationData(
                package: 'capell-app/insights',
                level: AiCreatorRecommendationLevel::Recommended,
                reason: 'Insights helps measure the performance of AI-created pages after launch.',
                consequence: 'The plan can launch, but performance feedback will be thinner.',
            );

            $recommendations[] = new AiCreatorPackageRecommendationData(
                package: 'capell-app/ga4-reports',
                level: AiCreatorRecommendationLevel::Optional,
                reason: 'GA4 Reports adds analytics snapshots for post-launch visibility.',
                consequence: 'Teams can still use external analytics, but Capell will show less context.',
            );

            $recommendations[] = new AiCreatorPackageRecommendationData(
                package: 'capell-app/site-monitor',
                level: AiCreatorRecommendationLevel::Optional,
                reason: 'Site Monitor tracks operational visibility after generated pages go live.',
                consequence: 'The site can publish, but monitoring remains outside the creation workflow.',
            );
        }

        return $recommendations;
    }

    /**
     * @param  list<string>  $needles
     */
    private function mentionsAny(string $intent, array $needles): bool
    {
        foreach ($needles as $needle) {
            $pattern = sprintf('/(?<![[:alnum:]])%s(?![[:alnum:]])/u', preg_quote($needle, '/'));

            if (preg_match($pattern, $intent) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<AiCreatorPackageRecommendationData>  $recommendations
     * @return list<AiCreatorPackageRecommendationData>
     */
    private function uniqueByPackage(array $recommendations): array
    {
        $unique = [];

        foreach ($recommendations as $recommendation) {
            $unique[$recommendation->package] = $recommendation;
        }

        return array_values($unique);
    }
}
