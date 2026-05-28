<?php

declare(strict_types=1);

namespace Capell\FoundationTheme\Support\Demo;

use Capell\Core\Actions\CreateDefaultLanguagesAction;
use Capell\Core\Actions\CreateThemeAction;
use Capell\Core\Actions\SetupPageUrlsAction;
use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\Core\Models\Blueprint;
use Capell\Core\Models\Language;
use Capell\Core\Models\Layout;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\Core\Models\Theme;
use Capell\Core\Support\Creator\BlueprintCreator;
use Capell\Core\Support\Creator\PageCreator;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsObject;

final class ThemeDemoPageInstaller
{
    use AsObject;

    public function handle(ThemeDemoInstallData $data, string $themeKey, string $themeName): int
    {
        $theme = $this->ensureTheme($themeKey, $themeName);
        $languages = $this->resolveLanguages($data);
        $sites = $this->resolveSites($data, $theme, $languages);
        $definitions = $this->definitions($themeKey, $themeName, $data->baseUrl);

        foreach ($sites as $site) {
            $this->installForSite($site, $languages, $definitions, $themeKey, $data->force);
        }

        return Command::SUCCESS;
    }

    private function ensureTheme(string $themeKey, string $themeName): Theme
    {
        return CreateThemeAction::run(key: $themeKey, name: $themeName);
    }

    /**
     * @return EloquentCollection<int, Language>
     */
    private function resolveLanguages(ThemeDemoInstallData $data): EloquentCollection
    {
        $languageCodes = $data->languageCodes;

        if ($languageCodes === []) {
            $defaultCode = Language::query()->default()->value('code');
            $languageCodes = [is_string($defaultCode) && $defaultCode !== '' ? $defaultCode : 'en'];
        }

        return CreateDefaultLanguagesAction::run($languageCodes);
    }

    /**
     * @param  EloquentCollection<int, Language>  $languages
     * @return EloquentCollection<int, Site>
     */
    private function resolveSites(ThemeDemoInstallData $data, Theme $theme, EloquentCollection $languages): EloquentCollection
    {
        $siteNames = $data->siteNames;

        if ($siteNames === []) {
            $defaultSiteName = Site::query()->default()->value('name');
            $siteNames = [is_string($defaultSiteName) && $defaultSiteName !== '' ? $defaultSiteName : 'Demo'];
        }

        $siteType = resolve(BlueprintCreator::class)->createSiteType();
        $sites = new EloquentCollection;

        foreach ($siteNames as $siteName) {
            $sites->push($this->ensureSite($siteName, $theme, $siteType, $languages, $data));
        }

        return $sites;
    }

    /**
     * @param  EloquentCollection<int, Language>  $languages
     */
    private function ensureSite(
        string $siteName,
        Theme $theme,
        Blueprint $siteType,
        EloquentCollection $languages,
        ThemeDemoInstallData $data,
    ): Site {
        $primaryLanguage = $languages->first();

        if (! $primaryLanguage instanceof Language) {
            $primaryLanguage = CreateDefaultLanguagesAction::run(['en'])->first();
        }

        /** @var Site $site */
        $site = Site::query()->firstOrNew(['name' => $siteName]);
        $site->fill([
            'blueprint_id' => $site->blueprint_id ?? $siteType->getKey(),
            'language_id' => $site->language_id ?? $primaryLanguage?->getKey(),
            'theme_id' => $theme->getKey(),
            'status' => true,
            'default' => $site->exists ? $site->default : ! Site::query()->default()->exists(),
        ]);
        $site->save();

        foreach ($languages as $languageIndex => $language) {
            $site->translations()->updateOrCreate(
                ['language_id' => $language->getKey()],
                [
                    'title' => $siteName,
                    'content' => '<p>' . e($siteName) . ' demo site.</p>',
                    'meta' => [
                        'description' => sprintf('%s theme demo site for preview content.', $siteName),
                        'footer_copy' => sprintf('<p>%s demo content.</p>', $siteName),
                    ],
                ],
            );

            $site->siteDomains()->updateOrCreate(
                ['language_id' => $language->getKey()],
                [
                    ...$this->siteDomainData($data->baseUrl, $siteName, (string) $language->code, $languageIndex),
                    'default' => $languageIndex === 0,
                    'status' => true,
                ],
            );
        }

        return $site->refresh();
    }

    /**
     * @return array{scheme: string|null, domain: string|null, path: string|null}
     */
    private function siteDomainData(string $baseUrl, string $siteName, string $languageCode, int $languageIndex): array
    {
        $scheme = parse_url($baseUrl, PHP_URL_SCHEME);
        $domain = parse_url($baseUrl, PHP_URL_HOST);
        $path = parse_url($baseUrl, PHP_URL_PATH);
        $pathParts = array_values(array_filter(explode('/', is_string($path) ? trim($path, '/') : '')));

        if ($languageIndex > 0) {
            $pathParts[] = $languageCode;
        }

        if (Site::query()->count() > 1) {
            $pathParts[] = Str::slug($siteName);
        }

        return [
            'scheme' => is_string($scheme) && $scheme !== '' ? $scheme : null,
            'domain' => is_string($domain) && $domain !== '' ? $domain : null,
            'path' => $pathParts === [] ? null : '/' . implode('/', $pathParts),
        ];
    }

    /**
     * @param  EloquentCollection<int, Language>  $languages
     * @param  array<int, ThemeDemoPageDefinition>  $definitions
     */
    private function installForSite(
        Site $site,
        EloquentCollection $languages,
        array $definitions,
        string $themeKey,
        bool $force,
    ): void {
        $creator = resolve(PageCreator::class);

        foreach ($definitions as $index => $definition) {
            $this->updateExistingPageLayout($site, $definition);

            /** @var Page $page */
            $page = $creator->createPage([
                'name' => $definition->name,
                'type_key' => $definition->type,
                'layout_key' => $definition->layout,
                'visible_from' => now()->subDay()->format('Y-m-d'),
                'meta' => [
                    'theme_demo' => [
                        'theme_key' => $themeKey,
                        'surface' => $definition->surface,
                        'render_data' => $definition->renderData,
                    ],
                    'robots' => ['noindex' => $definition->surface === 'not-found'],
                ],
                'translations' => $this->translations($languages, $definition),
            ], $site, $languages);

            if ($force || $page->order === null) {
                $page->forceFill(['order' => $index + 1])->save();
            }

            SetupPageUrlsAction::run($page);
        }
    }

    private function updateExistingPageLayout(Site $site, ThemeDemoPageDefinition $definition): void
    {
        $layout = Layout::query()->firstWhere('key', $definition->layout->value);

        if (! $layout instanceof Layout) {
            return;
        }

        Page::query()
            ->where('site_id', $site->getKey())
            ->where('name', $definition->name)
            ->update(['layout_id' => $layout->getKey()]);
    }

    /**
     * @param  EloquentCollection<int, Language>  $languages
     * @return array<string, array<string, mixed>>
     */
    private function translations(EloquentCollection $languages, ThemeDemoPageDefinition $definition): array
    {
        $translations = [];

        foreach ($languages as $language) {
            $translations[(string) $language->code] = [
                'title' => $definition->title,
                'content' => $definition->content,
                'summary' => $definition->renderData['summary'] ?? null,
                'meta' => [
                    'description' => $definition->renderData['summary'] ?? null,
                    'hero' => $definition->renderData['hero']['summary'] ?? null,
                    'hero_title' => $definition->renderData['hero']['heading'] ?? $definition->title,
                    'label' => $definition->title,
                    'link_text' => $definition->renderData['link_text'] ?? 'View preview',
                    'slug' => $definition->slug,
                    'theme_demo' => $definition->renderData,
                ],
            ];
        }

        return $translations;
    }

    /**
     * @return array<int, ThemeDemoPageDefinition>
     */
    private function definitions(string $themeKey, string $themeName, string $baseUrl): array
    {
        $media = ThemeDemoMedia::groupedForTheme($themeKey);
        $profile = $this->profile($themeKey, $themeName);
        $brandName = $themeName . ' Demo';
        $basePath = rtrim($baseUrl, '/');

        $navigationItems = [
            ['label' => 'Home', 'url' => '#home'],
            ['label' => 'Directory', 'url' => '#directory'],
            ['label' => 'Contact', 'url' => '#contact'],
        ];
        $actions = [
            ['label' => 'View preview', 'url' => '#directory', 'style' => 'primary'],
            ['label' => 'Contact team', 'url' => '#contact', 'style' => 'secondary'],
        ];
        $footerColumns = [
            [
                'heading' => 'Preview',
                'links' => [
                    ['label' => 'Homepage', 'url' => '#home'],
                    ['label' => 'Directory', 'url' => '#directory'],
                ],
            ],
            [
                'heading' => 'Content',
                'links' => [
                    ['label' => 'Article', 'url' => '#article'],
                    ['label' => 'Empty state', 'url' => '#empty'],
                ],
            ],
            [
                'heading' => 'Support',
                'links' => [
                    ['label' => 'Contact', 'url' => '#contact'],
                    ['label' => '404', 'url' => '#not-found'],
                ],
            ],
        ];

        return [
            new ThemeDemoPageDefinition(
                surface: 'homepage',
                name: $brandName . ' Home',
                title: $brandName . ' Homepage',
                slug: 'theme-' . $themeKey,
                content: $this->content(
                    sprintf('%s homepage preview', $themeName),
                    'A portable homepage content sample with hero copy, proof points, navigation, CTA, footer data, and media references.',
                    $media['hero'][0],
                ),
                renderData: [
                    'summary' => $profile['summary'],
                    'navigation' => ['brandName' => $brandName, 'items' => $navigationItems, 'ctaLabel' => 'Contact', 'ctaUrl' => '#contact'],
                    'hero' => [
                        'heading' => $profile['heroHeading'],
                        'eyebrow' => $themeName,
                        'summary' => $profile['heroSummary'],
                        'actions' => $actions,
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => sprintf('%s homepage media example', $themeName),
                    ],
                    'features_heading' => $profile['featuresHeading'],
                    'features_summary' => $profile['featuresSummary'],
                    'features' => $this->items($media['listing'], 'Homepage module', null, $profile['features']),
                    'spotlight' => [
                        'heading' => $profile['spotlightHeading'],
                        'summary' => $profile['spotlightSummary'],
                        'items' => $this->items($this->galleryMedia($media), 'Spotlight', null, $profile['spotlight']),
                        'variant' => 'spotlight',
                    ],
                    'gallery' => [
                        'heading' => $profile['galleryHeading'],
                        'summary' => $profile['gallerySummary'],
                        'items' => $this->items($this->galleryMedia($media), 'Gallery frame', null, $profile['gallery']),
                        'variant' => 'gallery',
                    ],
                    'items_heading' => $profile['pathwaysHeading'],
                    'items_summary' => $profile['pathwaysSummary'],
                    'items_variant' => 'pathways',
                    'items' => $this->items($this->galleryMedia($media), 'Pathway', null, $profile['pathways']),
                    'proof' => [
                        'heading' => $profile['proofHeading'],
                        'summary' => $profile['proofSummary'],
                        'items' => $this->proof($media['proof'], $profile['proof']),
                    ],
                    'cta' => ['heading' => $profile['ctaHeading'], 'summary' => $profile['ctaSummary'], 'actions' => $actions],
                    'footer' => ['brandName' => $brandName, 'summary' => 'Footer links and copy for preview rendering.', 'columns' => $footerColumns],
                    'image_urls' => ThemeDemoMedia::forTheme($themeKey),
                ],
                type: PageTypeEnum::Home,
                layout: LayoutEnum::Home,
            ),
            new ThemeDemoPageDefinition(
                surface: 'directory',
                name: $brandName . ' Directory',
                title: $brandName . ' Directory',
                slug: 'theme-' . $themeKey . '-directory',
                content: $this->content('Directory preview', 'A listing page sample for services, resources, products, or locations.', $media['listing'][0]),
                renderData: [
                    'summary' => 'Directory data includes cards, summaries, links, and image URLs.',
                    'heading' => 'Browse preview entries',
                    'items' => $this->items($media['listing'], 'Directory item', $basePath),
                    'navigation' => $navigationItems,
                    'footer' => $footerColumns,
                ],
                layout: LayoutEnum::Results,
            ),
            new ThemeDemoPageDefinition(
                surface: 'detail',
                name: $brandName . ' Detail',
                title: $brandName . ' Detail Article',
                slug: 'theme-' . $themeKey . '-detail',
                content: $this->content('Article-style preview', 'A detail page sample with editorial copy, proof, and a referenced feature image.', $media['detail'][0]),
                renderData: [
                    'summary' => 'Detail page render data for an article, case study, product story, or service page.',
                    'hero' => ['heading' => 'Detail page story', 'summary' => 'Article-style page data for long-form previews.', 'mediaUrl' => $media['detail'][0]],
                    'related' => $this->items($media['listing'], 'Related item', $basePath),
                ],
            ),
            new ThemeDemoPageDefinition(
                surface: 'contact',
                name: $brandName . ' Contact',
                title: $brandName . ' Contact',
                slug: 'theme-' . $themeKey . '-contact',
                content: $this->contactContent(),
                renderData: [
                    'summary' => 'Contact page render data with routing cards, expectation details, and a static enquiry form.',
                    'hero' => ['heading' => 'Start the right conversation', 'summary' => 'Route project scoping, support, migrations, and partnerships to the right team.', 'mediaUrl' => $media['contact'][0]],
                    'actions' => [['label' => 'Send enquiry', 'url' => '#contact-form', 'style' => 'primary']],
                ],
                layout: LayoutEnum::System,
            ),
            new ThemeDemoPageDefinition(
                surface: 'empty',
                name: $brandName . ' Empty State',
                title: $brandName . ' Empty State',
                slug: 'theme-' . $themeKey . '-empty',
                content: $this->content('Empty state preview', 'A graceful empty state for searches, filtered directories, catalogs, or resource hubs.', $media['cta'][0]),
                renderData: [
                    'summary' => 'Empty state data for no-results previews.',
                    'hero' => ['heading' => 'Nothing to show yet', 'summary' => 'Empty states still carry helpful copy and a next action.', 'mediaUrl' => $media['cta'][0]],
                    'actions' => $actions,
                    'items' => [],
                ],
            ),
            new ThemeDemoPageDefinition(
                surface: 'not-found',
                name: $brandName . ' 404',
                title: $brandName . ' Page Not Found',
                slug: 'theme-' . $themeKey . '-404',
                content: $this->content('404 preview', 'A not-found page sample with plain copy and a route back to useful content.', $media['cta'][0]),
                renderData: [
                    'summary' => '404 page data with recovery links and CTA copy.',
                    'hero' => ['heading' => 'Page not found', 'summary' => 'Help visitors recover with useful links and clear next steps.', 'mediaUrl' => $media['cta'][0]],
                    'actions' => [['label' => 'Return home', 'url' => '/', 'style' => 'primary']],
                ],
                type: PageTypeEnum::NotFound,
                layout: LayoutEnum::System,
            ),
            new ThemeDemoPageDefinition(
                surface: 'cta',
                name: $brandName . ' CTA',
                title: $brandName . ' CTA',
                slug: 'theme-' . $themeKey . '-cta',
                content: $this->content('CTA preview', 'A focused conversion page sample with semantic copy and action data.', $media['cta'][0]),
                renderData: [
                    'summary' => 'CTA page data for conversion-focused preview surfaces.',
                    'cta' => ['heading' => 'Ready for the next step?', 'summary' => 'This CTA is data-backed and presentation-agnostic.', 'actions' => $actions],
                    'mediaUrl' => $media['cta'][0],
                ],
            ),
        ];
    }

    private function content(string $heading, string $summary, string $imageUrl): string
    {
        return sprintf(
            '<h2>%s</h2><p>%s</p><p>Example media URL: <a href="%s">%s</a></p>',
            e($heading),
            e($summary),
            e($imageUrl),
            e($imageUrl),
        );
    }

    private function contactContent(): string
    {
        return <<<'HTML'
<style>
    .theme-demo-contact-page {
        background:
            linear-gradient(135deg, rgba(15, 118, 110, 0.08), transparent 34rem),
            #faf8ff;
        color: #131b2e;
        margin: 0;
        padding: clamp(48px, 8vw, 96px) clamp(20px, 6vw, 72px);
    }

    .theme-demo-contact-shell {
        display: grid;
        gap: clamp(32px, 5vw, 72px);
        margin: 0 auto;
        max-width: 1240px;
    }

    .theme-demo-contact-gateway {
        display: grid;
        gap: clamp(32px, 5vw, 64px);
    }

    .theme-demo-contact-intro {
        display: grid;
        gap: 28px;
    }

    .theme-demo-contact-eyebrow,
    .theme-demo-contact-routing article > p:first-child,
    .theme-demo-contact-form label {
        color: #0f766e;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0;
        margin: 0;
        text-transform: uppercase;
    }

    .theme-demo-contact-page h2,
    .theme-demo-contact-page h3 {
        letter-spacing: 0;
    }

    .theme-demo-contact-page h2 {
        color: #131b2e;
        font-size: clamp(2rem, 5vw, 3.75rem);
        font-weight: 850;
        line-height: 1;
        margin: 0;
        max-width: 12ch;
    }

    .theme-demo-contact-lede {
        color: #475569;
        font-size: 1.08rem;
        line-height: 1.7;
        margin: 0;
        max-width: 44rem;
    }

    .theme-demo-contact-routing {
        display: grid;
        gap: 0;
        border-top: 1px solid rgba(148, 163, 184, 0.42);
    }

    .theme-demo-contact-routing article {
        border-bottom: 1px solid rgba(148, 163, 184, 0.42);
        display: grid;
        gap: 12px;
        padding: 22px 0;
    }

    .theme-demo-contact-routing h3 {
        color: #131b2e;
        font-size: 1.15rem;
        line-height: 1.2;
        margin: 0;
    }

    .theme-demo-contact-routing article > p:last-child {
        color: #475569;
        line-height: 1.65;
        margin: 0;
    }

    .theme-demo-contact-details {
        border-bottom: 1px solid rgba(148, 163, 184, 0.42);
        border-top: 1px solid rgba(148, 163, 184, 0.42);
        display: grid;
        gap: 18px;
        padding: 24px 0;
    }

    .theme-demo-contact-details h3 {
        color: #131b2e;
        font-size: clamp(1.45rem, 3vw, 2rem);
        font-weight: 850;
        line-height: 1.1;
        margin: 0;
    }

    .theme-demo-contact-details p,
    .theme-demo-contact-expectations p {
        color: #475569;
        line-height: 1.65;
        margin: 0;
    }

    .theme-demo-contact-form {
        background: rgba(255, 255, 255, 0.92);
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 8px;
        box-shadow: 0 28px 90px rgba(15, 23, 42, 0.12);
        display: grid;
        gap: 16px;
        padding: clamp(22px, 4vw, 34px);
    }

    .theme-demo-contact-form-header {
        display: grid;
        gap: 8px;
        margin-bottom: 8px;
    }

    .theme-demo-contact-form-header h3 {
        color: #131b2e;
        font-size: 1.65rem;
        font-weight: 850;
        line-height: 1.12;
        margin: 0;
    }

    .theme-demo-contact-form-header p {
        color: #64748b;
        line-height: 1.6;
        margin: 0;
    }

    .theme-demo-contact-field {
        display: grid;
        gap: 8px;
    }

    .theme-demo-contact-form input,
    .theme-demo-contact-form select,
    .theme-demo-contact-form textarea {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        color: #0f172a;
        font: inherit;
        min-height: 46px;
        padding: 10px 12px;
        width: 100%;
    }

    .theme-demo-contact-form textarea {
        min-height: 120px;
        resize: vertical;
    }

    .theme-demo-contact-form button {
        align-items: center;
        background: #0f766e;
        border: 0;
        border-radius: 8px;
        color: #ffffff;
        display: inline-flex;
        font: inherit;
        font-weight: 800;
        justify-content: center;
        min-height: 48px;
        padding: 12px 18px;
    }

    .theme-demo-contact-expectations {
        border-top: 1px solid rgba(148, 163, 184, 0.42);
        display: grid;
        gap: 0;
        margin-top: 8px;
    }

    .theme-demo-contact-expectations p {
        border-bottom: 1px solid rgba(148, 163, 184, 0.42);
        padding: 16px 0;
    }

    .theme-demo-contact-expectations strong {
        color: #131b2e;
    }

    @media (min-width: 760px) {
        .theme-demo-contact-routing article,
        .theme-demo-contact-details {
            grid-template-columns: minmax(10rem, 0.48fr) minmax(0, 1fr);
        }

        .theme-demo-contact-expectations {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .theme-demo-contact-expectations p {
            border-bottom: 0;
            border-left: 1px solid rgba(148, 163, 184, 0.42);
            padding: 0 20px;
        }

        .theme-demo-contact-expectations p:first-child {
            border-left: 0;
            padding-left: 0;
        }
    }

    @media (min-width: 1024px) {
        .theme-demo-contact-gateway {
            grid-template-columns: minmax(0, 1fr) minmax(22rem, 0.72fr);
            align-items: start;
        }

        .theme-demo-contact-form {
            position: sticky;
            top: 32px;
        }
    }
</style>
<section id="contact" class="theme-demo-contact-page">
    <div class="theme-demo-contact-shell">
        <div class="theme-demo-contact-gateway">
            <div class="theme-demo-contact-intro">
                <p class="theme-demo-contact-eyebrow">Contact</p>
                <h2>Start the right conversation</h2>
                <p class="theme-demo-contact-lede">Tell us what you are planning, fixing, moving, or partnering on. One contact page routes project scoping, technical support, migrations, and partnerships to the right Capell team.</p>

                <div class="theme-demo-contact-details">
                    <h3>Capell Studio, London</h3>
                    <p>Remote-first delivery with UK timezone handover. Send an enquiry and the contact form routes it into the right follow-up path.</p>
                </div>

                <div class="theme-demo-contact-routing">
                    <article>
                        <p>Project scoping</p>
                        <div>
                            <h3>New implementations</h3>
                            <p>Plan content models, package boundaries, layouts, and launch checks before the build starts.</p>
                        </div>
                    </article>
                    <article>
                        <p>Support</p>
                        <div>
                            <h3>Existing site help</h3>
                            <p>Route production issues, editor workflow questions, and package troubleshooting to the right owner.</p>
                        </div>
                    </article>
                    <article>
                        <p>Migration planning</p>
                        <div>
                            <h3>Move from legacy CMSs</h3>
                            <p>Map pages, redirects, media, structured fields, and verification work into a clear migration path.</p>
                        </div>
                    </article>
                    <article>
                        <p>Partnerships</p>
                        <div>
                            <h3>Agency and technology work</h3>
                            <p>Discuss delivery partnerships, packaged integrations, and repeatable theme or content operations.</p>
                        </div>
                    </article>
                </div>
            </div>

            <form id="contact-form" class="theme-demo-contact-form theme-demo-contact-form-panel" method="post" action="#">
                <div class="theme-demo-contact-form-header">
                    <p class="theme-demo-contact-eyebrow">Contact form</p>
                    <h3>Send an enquiry</h3>
                    <p>Share the context once. We will route it to the right delivery, support, migration, or partnership lead.</p>
                </div>

                <div class="theme-demo-contact-field">
                    <label for="theme-demo-contact-name">Name</label>
                    <input id="theme-demo-contact-name" name="name" type="text" autocomplete="name">
                </div>
                <div class="theme-demo-contact-field">
                    <label for="theme-demo-contact-email">Work email</label>
                    <input id="theme-demo-contact-email" name="email" type="email" autocomplete="email">
                </div>
                <div class="theme-demo-contact-field">
                    <label for="theme-demo-contact-company">Company</label>
                    <input id="theme-demo-contact-company" name="company" type="text" autocomplete="organization">
                </div>
                <div class="theme-demo-contact-field">
                    <label for="theme-demo-contact-topic">Topic</label>
                    <select id="theme-demo-contact-topic" name="topic">
                        <option>Project scoping</option>
                        <option>Support</option>
                        <option>Migration planning</option>
                        <option>Partnerships</option>
                    </select>
                </div>
                <div class="theme-demo-contact-field">
                    <label for="theme-demo-contact-message">Message</label>
                    <textarea id="theme-demo-contact-message" name="message" rows="5"></textarea>
                </div>
                <button type="button">Send enquiry</button>
            </form>
        </div>

        <div class="theme-demo-contact-expectations">
            <p><strong>Response:</strong> Within 4 business hours</p>
            <p><strong>Location:</strong> London, UK and remote-first</p>
            <p><strong>Handover:</strong> Directly routed to the right team</p>
        </div>
    </div>
</section>
HTML;
    }

    /**
     * @param  array<int, string>  $imageUrls
     * @param  array<int, array{title: string, summary: string, type?: string}>  $copy
     * @return array<int, array<string, string>>
     */
    private function items(array $imageUrls, string $label, ?string $baseUrl = null, array $copy = []): array
    {
        return array_map(
            static fn (string $imageUrl, int $index): array => [
                'title' => $copy[$index]['title'] ?? sprintf('%s %d', $label, $index + 1),
                'summary' => $copy[$index]['summary'] ?? 'Preview item copy that can render as a card, row, or teaser.',
                'url' => ($baseUrl ?? '') . '#item-' . ($index + 1),
                'image' => $imageUrl,
                'imageUrl' => $imageUrl,
                'type' => $copy[$index]['type'] ?? 'Preview',
            ],
            $imageUrls,
            array_keys($imageUrls),
        );
    }

    /**
     * @param  array{hero: array<int, string>, listing: array<int, string>, detail: array<int, string>, proof: array<int, string>, contact: array<int, string>, cta: array<int, string>}  $media
     * @return array<int, string>
     */
    private function galleryMedia(array $media): array
    {
        return array_slice(array_values(array_unique(array_merge(
            $media['listing'],
            $media['detail'],
            $media['proof'],
            $media['cta'],
        ))), 0, 4);
    }

    /**
     * @return array{
     *     summary: string,
     *     heroHeading: string,
     *     heroSummary: string,
     *     featuresHeading: string,
     *     featuresSummary: string,
     *     features: array<int, array{title: string, summary: string, type?: string}>,
     *     spotlightHeading: string,
     *     spotlightSummary: string,
     *     spotlight: array<int, array{title: string, summary: string, type?: string}>,
     *     galleryHeading: string,
     *     gallerySummary: string,
     *     gallery: array<int, array{title: string, summary: string, type?: string}>,
     *     pathwaysHeading: string,
     *     pathwaysSummary: string,
     *     pathways: array<int, array{title: string, summary: string, type?: string}>,
     *     proofHeading: string,
     *     proofSummary: string,
     *     proof: array<int, array{metric: string, name: string, quote: string}>,
     *     ctaHeading: string,
     *     ctaSummary: string
     * }
     */
    private function profile(string $themeKey, string $themeName): array
    {
        $defaultProfile = [
            'summary' => sprintf('Homepage preview content for the %s theme.', $themeName),
            'heroHeading' => sprintf('Launch a polished %s site', Str::lower($themeName)),
            'heroSummary' => 'A complete first screen with semantic copy and remote media ready for preview rendering.',
            'featuresHeading' => 'Featured modules',
            'featuresSummary' => sprintf('Homepage preview content for the %s theme.', $themeName),
            'features' => [],
            'spotlightHeading' => sprintf('%s buying moments', $themeName),
            'spotlightSummary' => 'Tabbed spotlight panels let visitors compare the theme\'s strongest content moments without leaving the page.',
            'spotlight' => $this->defaultSpotlightItems($themeName),
            'galleryHeading' => sprintf('%s layout gallery', $themeName),
            'gallerySummary' => 'A carousel-ready media section for campaigns, featured work, resources, and proof surfaces.',
            'gallery' => $this->defaultGalleryItems($themeName),
            'pathwaysHeading' => sprintf('Launch paths for %s sites', Str::lower($themeName)),
            'pathwaysSummary' => 'Accordion-style pathways give buyers and editors clear ways to imagine the theme beyond one homepage.',
            'pathways' => $this->defaultPathwayItems($themeName),
            'proofHeading' => 'Proof that the theme can carry real pages',
            'proofSummary' => 'Evidence blocks pair outcomes with media so previews feel closer to launchable sites.',
            'proof' => $this->defaultProofItems($themeName),
            'ctaHeading' => 'Turn this preview into a real site',
            'ctaSummary' => 'CTA copy is stored as data, not presentation markup.',
        ];

        $profiles = [
            'agency' => [
                'summary' => 'A high-contrast agency homepage with campaign proof, project cards, and decisive conversion paths.',
                'heroHeading' => 'Win sharper briefs with a bolder agency site',
                'heroSummary' => 'Lead with portfolio-grade media, crisp positioning, and reusable proof blocks that can survive real client edits.',
                'featuresHeading' => 'Built for teams selling creative judgment',
                'featuresSummary' => 'The page gives agencies enough visual rhythm for brand work without trapping content inside a one-off template.',
                'features' => [
                    ['title' => 'Campaign-grade hero', 'summary' => 'Oversized type, confident contrast, and media framing make the first screen feel intentional.', 'type' => 'Hero'],
                    ['title' => 'Proof-led modules', 'summary' => 'Reusable proof cards let studios show outcomes, quotes, and momentum without custom markup.', 'type' => 'Proof'],
                    ['title' => 'Case-study pathways', 'summary' => 'Directory and detail surfaces route visitors into services, work, and contact naturally.', 'type' => 'Work'],
                ],
                'ctaHeading' => 'Package the next agency launch',
                'ctaSummary' => 'Use this theme when the public site needs to feel confident before a single custom component is written.',
            ],
            'corporate' => [
                'summary' => 'A composed corporate homepage for service lines, trust signals, reports, and stakeholder journeys.',
                'heroHeading' => 'Present a serious organisation with less ceremony',
                'heroSummary' => 'Structured sections keep corporate content credible, scan-friendly, and easy to govern across departments.',
                'featuresHeading' => 'Designed for clarity under review',
                'featuresSummary' => 'The theme balances restrained visuals with enough polish for leadership, investor, and service pages.',
                'features' => [
                    ['title' => 'Executive first screen', 'summary' => 'Calm hierarchy and architectural media establish scale without feeling like a generic brochure.', 'type' => 'Positioning'],
                    ['title' => 'Governed content cards', 'summary' => 'Service, report, and resource cards stay consistent when multiple teams publish.', 'type' => 'Governance'],
                    ['title' => 'Trust pathways', 'summary' => 'Proof, directory, and CTA sections help visitors move from evaluation to enquiry.', 'type' => 'Trust'],
                ],
                'ctaHeading' => 'Ship a corporate surface that holds up',
                'ctaSummary' => 'Start from a theme that looks credible in reviews and remains maintainable after launch.',
            ],
            'commerce' => [
                'summary' => 'An editorial commerce homepage for collections, product stories, proof, and campaign merchandising.',
                'heroHeading' => 'Make commerce feel curated, not catalogued',
                'heroSummary' => 'Blend editorial storytelling with product pathways so collections, offers, and content support each other.',
                'featuresHeading' => 'A richer storefront rhythm',
                'featuresSummary' => 'Commerce pages need more than product grids; this theme gives campaigns room to breathe.',
                'features' => [
                    ['title' => 'Collection storytelling', 'summary' => 'Hero and card sections frame products around season, value, and use case.', 'type' => 'Merchandising'],
                    ['title' => 'Product proof', 'summary' => 'Proof modules support reviews, guarantees, stock cues, and buyer confidence.', 'type' => 'Conversion'],
                    ['title' => 'Content-led browsing', 'summary' => 'Directory and detail pages connect categories, guides, and featured products.', 'type' => 'Discovery'],
                ],
                'ctaHeading' => 'Turn browsing into a stronger buying path',
                'ctaSummary' => 'Use editorial commerce when a store needs premium context around the catalogue.',
            ],
            'education' => [
                'summary' => 'A course-led homepage for programmes, instructors, cohorts, open days, and enrolment journeys.',
                'heroHeading' => 'Turn programmes into a confident enrolment journey',
                'heroSummary' => 'Show learning outcomes, cohort energy, and course pathways with content editors can update every term.',
                'featuresHeading' => 'Everything learners need before they apply',
                'featuresSummary' => 'The theme makes courses, instructors, resources, and enrolment actions feel connected.',
                'features' => [
                    ['title' => 'Course discovery', 'summary' => 'Programme cards can highlight level, format, start dates, and outcomes.', 'type' => 'Courses'],
                    ['title' => 'Instructor credibility', 'summary' => 'People-led sections give teaching teams and subject experts proper space.', 'type' => 'Faculty'],
                    ['title' => 'Enrolment prompts', 'summary' => 'CTA and form-ready sections keep applications, open days, and enquiries close.', 'type' => 'Conversion'],
                ],
                'ctaHeading' => 'Open the next cohort with a better first impression',
                'ctaSummary' => 'Use the education theme when course content needs structure and a premium public face.',
            ],
            'healthcare' => [
                'summary' => 'A healthcare homepage for services, clinicians, appointment paths, local proof, and patient reassurance.',
                'heroHeading' => 'Help patients choose the right care faster',
                'heroSummary' => 'Create calm, trustworthy healthcare pages with clear service paths and appointment-focused actions.',
                'featuresHeading' => 'Patient-centred sections',
                'featuresSummary' => 'The theme gives clinical teams enough structure for services, proof, people, and booking.',
                'features' => [
                    ['title' => 'Service routing', 'summary' => 'Guide patients from symptoms or service areas into the right next step.', 'type' => 'Services'],
                    ['title' => 'Clinician trust', 'summary' => 'Feature care teams, accreditations, and reassurance without clutter.', 'type' => 'Trust'],
                    ['title' => 'Booking-ready CTAs', 'summary' => 'Keep enquiry and appointment paths visible across the public journey.', 'type' => 'Access'],
                ],
                'ctaHeading' => 'Make the care pathway easier to act on',
                'ctaSummary' => 'Use healthcare when public pages need warmth, clarity, and operational discipline.',
            ],
            'knowledge' => [
                'summary' => 'A knowledge-base homepage for topic hubs, featured resources, search, authors, and editorial depth.',
                'heroHeading' => 'Make expertise easier to browse and trust',
                'heroSummary' => 'Present guides, research, resources, and authors as a coherent content product instead of a loose archive.',
                'featuresHeading' => 'A home for serious content libraries',
                'featuresSummary' => 'Knowledge pages need search, structure, and editorial signals that reward repeat visitors.',
                'features' => [
                    ['title' => 'Topic pathways', 'summary' => 'Hub sections group resources around intent, stage, or audience.', 'type' => 'Taxonomy'],
                    ['title' => 'Featured thinking', 'summary' => 'Editorial cards make important guides and reports feel current.', 'type' => 'Editorial'],
                    ['title' => 'Author trust', 'summary' => 'People and proof modules reinforce why the content is worth reading.', 'type' => 'Authority'],
                ],
                'ctaHeading' => 'Turn the resource library into a product',
                'ctaSummary' => 'Use the knowledge theme when content is a reason to return, not a support appendix.',
            ],
            'local-services' => [
                'summary' => 'A quote-led local services homepage for service areas, reviews, cases, contact, and fast enquiry.',
                'heroHeading' => 'Convert local intent into booked work',
                'heroSummary' => 'Make services, coverage areas, proof, and quote requests obvious for visitors who need help now.',
                'featuresHeading' => 'Built for high-intent local journeys',
                'featuresSummary' => 'The theme keeps credibility, geography, and contact paths visible without feeling like a template.',
                'features' => [
                    ['title' => 'Service-area clarity', 'summary' => 'Show where the team works and which jobs are a good fit.', 'type' => 'Local SEO'],
                    ['title' => 'Quote-first flow', 'summary' => 'CTA and contact sections support fast enquiries without burying details.', 'type' => 'Lead gen'],
                    ['title' => 'Case proof', 'summary' => 'Before-and-after style cards and reviews make the business feel real.', 'type' => 'Proof'],
                ],
                'ctaHeading' => 'Make the next quote request easier',
                'ctaSummary' => 'Use local services when a business needs trust, coverage, and conversion in the same screen.',
            ],
            'nonprofit' => [
                'summary' => 'An impact-led nonprofit homepage for campaigns, giving, volunteering, events, and community stories.',
                'heroHeading' => 'Show the impact before asking for support',
                'heroSummary' => 'Lead with mission, outcomes, and ways to help so supporters can understand and act quickly.',
                'featuresHeading' => 'Campaign-ready civic pages',
                'featuresSummary' => 'Nonprofit content needs emotion, proof, and practical next steps in equal measure.',
                'features' => [
                    ['title' => 'Impact proof', 'summary' => 'Metrics and story cards connect donations and volunteering to real outcomes.', 'type' => 'Impact'],
                    ['title' => 'Campaign paths', 'summary' => 'Give each campaign, event, and appeal a structured route from awareness to action.', 'type' => 'Campaigns'],
                    ['title' => 'Supporter actions', 'summary' => 'Donation, volunteer, newsletter, and contact CTAs can sit together without confusion.', 'type' => 'Action'],
                ],
                'ctaHeading' => 'Make support feel immediate and useful',
                'ctaSummary' => 'Use nonprofit when public pages need to move people from belief to action.',
            ],
            'portfolio' => [
                'summary' => 'A portfolio homepage for selected work, case studies, services, testimonials, media kits, and newsletters.',
                'heroHeading' => 'Make the work feel selective and worth hiring',
                'heroSummary' => 'Give creators, consultants, and studios a premium public surface for proof, perspective, and enquiries.',
                'featuresHeading' => 'Portfolio structure beyond a grid',
                'featuresSummary' => 'The theme gives work, services, speaking, and newsletter surfaces a consistent editorial frame.',
                'features' => [
                    ['title' => 'Selected work', 'summary' => 'Feature strong projects without making every page a custom case study.', 'type' => 'Work'],
                    ['title' => 'Service framing', 'summary' => 'Explain what someone can hire you for while keeping the page visually led.', 'type' => 'Services'],
                    ['title' => 'Personal proof', 'summary' => 'Testimonials, media, and newsletter prompts support authority without clutter.', 'type' => 'Authority'],
                ],
                'ctaHeading' => 'Turn attention into the right enquiry',
                'ctaSummary' => 'Use portfolio when taste, trust, and a clear next step all need to be visible.',
            ],
            'saas' => [
                'summary' => 'A product-led SaaS homepage for dashboards, comparison, calculators, proof, and demo requests.',
                'heroHeading' => 'Make product value visible before the demo',
                'heroSummary' => 'Lead with outcome, interface context, proof, and clear paths into evaluation.',
                'featuresHeading' => 'A serious SaaS landing rhythm',
                'featuresSummary' => 'The theme supports product storytelling, evaluation content, and conversion without a marketing-site rebuild.',
                'features' => [
                    ['title' => 'Product proof', 'summary' => 'Dashboard media, metrics, and proof sections make the offer tangible.', 'type' => 'Product'],
                    ['title' => 'Evaluation paths', 'summary' => 'Comparison and directory sections guide buyers through use cases and objections.', 'type' => 'Buying'],
                    ['title' => 'Demo conversion', 'summary' => 'CTA and contact pages keep high-intent visitors moving.', 'type' => 'Pipeline'],
                ],
                'ctaHeading' => 'Give the sales motion a better public surface',
                'ctaSummary' => 'Use SaaS when product, proof, and demo conversion need to move together.',
            ],
        ];

        return array_replace($defaultProfile, $profiles[$themeKey] ?? []);
    }

    /**
     * @return array<int, array{title: string, summary: string, type: string}>
     */
    private function defaultSpotlightItems(string $themeName): array
    {
        return [
            [
                'title' => sprintf('%s first impression', $themeName),
                'summary' => 'Show the theme\'s strongest hero, media, and proof treatment as one focused buyer-facing story.',
                'type' => 'Moment',
            ],
            [
                'title' => sprintf('%s content depth', $themeName),
                'summary' => 'Use tabbed panels to preview how directory, detail, resource, or product content carries the same premium system.',
                'type' => 'Depth',
            ],
            [
                'title' => sprintf('%s conversion route', $themeName),
                'summary' => 'Keep contact, CTA, proof, and next-step copy close together so the theme feels complete beyond the first scroll.',
                'type' => 'Action',
            ],
            [
                'title' => sprintf('%s editor handoff', $themeName),
                'summary' => 'The layout stays interactive while the content remains portable render data that editors can safely own.',
                'type' => 'Governance',
            ],
        ];
    }

    /**
     * @return array<int, array{title: string, summary: string, type: string}>
     */
    private function defaultGalleryItems(string $themeName): array
    {
        return [
            [
                'title' => sprintf('%s homepage rhythm', $themeName),
                'summary' => 'Hero, proof, gallery, content, and CTA sections work together as a launch-ready page.',
                'type' => 'Homepage',
            ],
            [
                'title' => sprintf('%s content surface', $themeName),
                'summary' => 'Directory and detail previews give editors realistic surfaces beyond the first screen.',
                'type' => 'Content',
            ],
            [
                'title' => sprintf('%s conversion path', $themeName),
                'summary' => 'Contact, CTA, and empty-state pages keep visitor journeys complete across the theme.',
                'type' => 'Conversion',
            ],
            [
                'title' => sprintf('%s media system', $themeName),
                'summary' => 'Carousel-ready media creates premium movement while staying data-driven and reusable.',
                'type' => 'Gallery',
            ],
        ];
    }

    /**
     * @return array<int, array{title: string, summary: string, type: string}>
     */
    private function defaultPathwayItems(string $themeName): array
    {
        return [
            [
                'title' => 'Start with the homepage',
                'summary' => sprintf('Use the %s first screen, feature rhythm, gallery, and proof sections as a complete launchable starting point.', $themeName),
                'type' => 'Launch',
            ],
            [
                'title' => 'Add directory depth',
                'summary' => 'Turn services, courses, resources, products, campaigns, or work into browsable cards without custom page code.',
                'type' => 'Browse',
            ],
            [
                'title' => 'Connect conversion pages',
                'summary' => 'Pair CTA, contact, and empty-state surfaces so the theme handles real visitor journeys, not just visual previews.',
                'type' => 'Convert',
            ],
            [
                'title' => 'Keep content portable',
                'summary' => 'The theme owns presentation while the public route consumes safe, hydrated render data from Capell records.',
                'type' => 'Govern',
            ],
        ];
    }

    /**
     * @return array<int, array{metric: string, name: string, quote: string}>
     */
    private function defaultProofItems(string $themeName): array
    {
        return [
            [
                'metric' => '7 public surfaces',
                'name' => sprintf('%s preview set', $themeName),
                'quote' => 'Homepage, directory, detail, contact, empty, CTA, and not-found pages render from one portable content model.',
            ],
            [
                'metric' => '4 media patterns',
                'name' => 'Richer preview system',
                'quote' => 'Hero, feature cards, gallery carousel, and proof media make the theme feel closer to a paid package.',
            ],
            [
                'metric' => '0 editor leaks',
                'name' => 'Public-safe rendering',
                'quote' => 'The public route consumes hydrated render data without exposing authoring metadata or admin concerns.',
            ],
        ];
    }

    /**
     * @param  array<int, string>  $imageUrls
     * @param  array<int, array{metric: string, name: string, quote: string}>  $copy
     * @return array<int, array<string, string>>
     */
    private function proof(array $imageUrls, array $copy = []): array
    {
        return array_map(
            static fn (string $imageUrl, int $index): array => [
                'metric' => $copy[$index]['metric'] ?? sprintf('%d ready surfaces', $index + 4),
                'name' => $copy[$index]['name'] ?? 'Preview proof',
                'quote' => $copy[$index]['quote'] ?? 'Structured proof data with portable media.',
                'image' => $imageUrl,
            ],
            $imageUrls,
            array_keys($imageUrls),
        );
    }
}
