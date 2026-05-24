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
                    'summary' => sprintf('Homepage preview content for the %s theme.', $themeName),
                    'navigation' => ['brandName' => $brandName, 'items' => $navigationItems, 'ctaLabel' => 'Contact', 'ctaUrl' => '#contact'],
                    'hero' => [
                        'heading' => sprintf('Launch a polished %s site', Str::lower($themeName)),
                        'eyebrow' => $themeName,
                        'summary' => 'A complete first screen with semantic copy and remote media ready for preview rendering.',
                        'actions' => $actions,
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => sprintf('%s homepage media example', $themeName),
                    ],
                    'features' => $this->items($media['listing'], 'Homepage module'),
                    'proof' => $this->proof($media['proof']),
                    'cta' => ['heading' => 'Turn this preview into a real site', 'summary' => 'CTA copy is stored as data, not presentation markup.', 'actions' => $actions],
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
                content: $this->content('Contact preview', 'A contact page sample with practical office, appointment, or enquiry copy.', $media['contact'][0]),
                renderData: [
                    'summary' => 'Contact page render data with contact-focused CTA and image media.',
                    'hero' => ['heading' => 'Start a conversation', 'summary' => 'Preview enquiry copy with remote contact media.', 'mediaUrl' => $media['contact'][0]],
                    'actions' => [['label' => 'Send enquiry', 'url' => 'mailto:hello@example.test', 'style' => 'primary']],
                ],
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

    /**
     * @param  array<int, string>  $imageUrls
     * @return array<int, array<string, string>>
     */
    private function items(array $imageUrls, string $label, ?string $baseUrl = null): array
    {
        return array_map(
            static fn (string $imageUrl, int $index): array => [
                'title' => sprintf('%s %d', $label, $index + 1),
                'summary' => 'Preview item copy that can render as a card, row, or teaser.',
                'url' => ($baseUrl ?? '') . '#item-' . ($index + 1),
                'image' => $imageUrl,
                'imageUrl' => $imageUrl,
                'type' => 'Preview',
            ],
            $imageUrls,
            array_keys($imageUrls),
        );
    }

    /**
     * @param  array<int, string>  $imageUrls
     * @return array<int, array<string, string>>
     */
    private function proof(array $imageUrls): array
    {
        return array_map(
            static fn (string $imageUrl, int $index): array => [
                'metric' => sprintf('%d ready surfaces', $index + 4),
                'name' => 'Preview proof',
                'quote' => 'Structured proof data with portable media.',
                'image' => $imageUrl,
            ],
            $imageUrls,
            array_keys($imageUrls),
        );
    }
}
