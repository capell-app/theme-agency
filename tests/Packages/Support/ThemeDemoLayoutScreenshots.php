<?php

declare(strict_types=1);

use Capell\Core\Actions\SetupPageUrlsAction;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Site;
use Capell\Core\Support\Creator\PageCreator;
use Capell\Core\ThemeStudio\Contracts\ThemePageAdapter;
use Capell\Core\ThemeStudio\Contracts\ThemeSection;
use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Capell\Core\ThemeStudio\Data\ContentListingSectionData;
use Capell\Core\ThemeStudio\Data\CtaSectionData;
use Capell\Core\ThemeStudio\Data\FeatureSectionData;
use Capell\Core\ThemeStudio\Data\FooterData;
use Capell\Core\ThemeStudio\Data\HeroSectionData;
use Capell\Core\ThemeStudio\Data\NavigationData;
use Capell\Core\ThemeStudio\Data\ProofSectionData;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePageData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemePageAdapterRegistry;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Providers\FoundationThemeServiceProvider;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\Frontend\Facades\Frontend;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Testing\TestResponse;

use function Pest\Laravel\get;

use Symfony\Component\Process\Process;

/**
 * @param  class-string<ServiceProvider>  $providerClass
 * @param  class-string  $installerClass
 * @return Collection<int, Page>
 */
function installThemeDemoScreenshotFixture(
    string $themeKey,
    string $packageName,
    string $providerClass,
    string $installerClass,
): Collection {
    CapellCore::forcePackageInstalled(FoundationThemeServiceProvider::$packageName);

    $foundationProvider = new FoundationThemeServiceProvider(app());
    $foundationProvider->register();
    $foundationProvider->boot();

    CapellCore::forcePackageInstalled($packageName);

    $provider = new $providerClass(app());

    expect($provider)->toBeInstanceOf(ServiceProvider::class);

    $provider->register();
    app()->call([$provider, 'boot'], ['themeRegistry' => resolve(ThemeRegistry::class)]);

    registerThemeDemoScreenshotPageAdapter($themeKey);

    $exitCode = $installerClass::run(new ThemeDemoInstallData(
        siteNames: ['Snapshot'],
        languageCodes: ['en'],
        baseUrl: 'https://demo.test',
        force: true,
    ));

    expect((int) $exitCode)->toBe(Command::SUCCESS);

    $pages = Page::query()
        ->with(['layout', 'pageUrl.siteDomain', 'translations', 'type'])
        ->where('meta->theme_demo->theme_key', $themeKey)
        ->orderBy('order')
        ->orderBy('id')
        ->get();

    createThemeDemoScreenshotMaintenancePage($themeKey, $pages);
    createThemeDemoScreenshotSystemPage($themeKey, $pages);

    $pages = Page::query()
        ->with(['layout', 'pageUrl.siteDomain', 'translations', 'type'])
        ->where('meta->theme_demo->theme_key', $themeKey)
        ->orderBy('order')
        ->orderBy('id')
        ->get();

    enrichThemeDemoScreenshotFixture($themeKey, $pages);

    return $pages->fresh(['layout', 'pageUrl.siteDomain', 'translations', 'type']);
}

/**
 * @return Collection<int, Page>
 */
function installFoundationThemeDemoScreenshotFixture(): Collection
{
    $themeKey = 'default';

    CapellCore::forcePackageInstalled(FoundationThemeServiceProvider::$packageName);

    $provider = new FoundationThemeServiceProvider(app());
    $provider->register();
    $provider->boot();

    view()->addNamespace('capell-foundation-theme', themeDemoRepositoryPath('packages/foundation-theme/resources/views'));
    view()->addNamespace('capell', themeDemoRepositoryPath('packages/foundation-theme/resources/views'));

    registerFoundationThemeDemoScreenshotRenderer();
    registerThemeDemoScreenshotPageAdapter($themeKey);

    $exitCode = ThemeDemoPageInstaller::run(new ThemeDemoInstallData(
        siteNames: ['Snapshot'],
        languageCodes: ['en'],
        baseUrl: 'https://demo.test',
        force: true,
    ), $themeKey, 'Foundation');

    expect($exitCode)->toBe(Command::SUCCESS);

    $pages = Page::query()
        ->with(['layout', 'pageUrl.siteDomain', 'translations', 'type'])
        ->where('meta->theme_demo->theme_key', $themeKey)
        ->orderBy('order')
        ->orderBy('id')
        ->get();

    createThemeDemoScreenshotMaintenancePage($themeKey, $pages);
    createThemeDemoScreenshotSystemPage($themeKey, $pages);

    $pages = Page::query()
        ->with(['layout', 'pageUrl.siteDomain', 'translations', 'type'])
        ->where('meta->theme_demo->theme_key', $themeKey)
        ->orderBy('order')
        ->orderBy('id')
        ->get();

    enrichThemeDemoScreenshotFixture($themeKey, $pages);

    return $pages->fresh(['layout', 'pageUrl.siteDomain', 'translations', 'type']);
}

function registerFoundationThemeDemoScreenshotRenderer(): void
{
    $registry = resolve(ThemeRegistry::class);

    if ($registry->has('default')) {
        return;
    }

    $sectionRenderers = [
        'navigation' => new ViewSectionRenderer('default', 'navigation', 'capell-theme-corporate::sections.navigation'),
        'hero' => new ViewSectionRenderer('default', 'hero', 'capell-theme-corporate::sections.hero'),
        'features' => new ViewSectionRenderer('default', 'features', 'capell-theme-corporate::sections.features'),
        'proof' => new ViewSectionRenderer('default', 'proof', 'capell-theme-corporate::sections.proof'),
        'content-listing' => new ViewSectionRenderer('default', 'content-listing', 'capell-theme-corporate::sections.content-listing'),
        'cta' => new ViewSectionRenderer('default', 'cta', 'capell-theme-corporate::sections.cta'),
        'footer' => new ViewSectionRenderer('default', 'footer', 'capell-theme-corporate::sections.footer'),
    ];

    view()->addNamespace('capell-theme-corporate', themeDemoRepositoryPath('packages/theme-corporate/resources/views'));

    $registry->register(
        definition: new ThemeDefinitionData(
            key: 'default',
            name: 'Foundation',
            description: 'Default frontend rendering with Foundation assets and no child theme.',
            package: FoundationThemeServiceProvider::$packageName,
            previewImage: '/vendor/capell/themes/foundation.jpg',
            tags: ['Foundation'],
            bestFit: ['Default frontend'],
            includedSections: array_keys($sectionRenderers),
            presets: [
                new ThemePresetData(
                    key: 'boardroom',
                    name: 'Foundation',
                    description: 'Neutral foundation preset used by route-backed demo screenshots.',
                    previewImage: '/vendor/capell/themes/foundation.jpg',
                ),
            ],
            runtime: FrontendRuntime::Blade,
        ),
        themeRenderer: new BladeThemeRenderer(
            themeKey: 'default',
            layoutView: 'capell-theme-corporate::page',
            sectionRenderers: $sectionRenderers,
        ),
        sectionRenderers: array_values($sectionRenderers),
    );
}

/**
 * @param  Collection<int, Page>  $pages
 * @param  array<string, array{type: string, layout: string}>  $expectedTypesAndLayoutsBySurface
 */
function assertThemeDemoLayoutScreenshots(string $themeKey, Collection $pages, array $expectedTypesAndLayoutsBySurface): void
{
    cleanThemeDemoScreenshotDirectory($themeKey);

    expect($pages)->toHaveCount(count($expectedTypesAndLayoutsBySurface));
    expect($pages->map(fn (Page $page): ?string => $page->type?->key)->unique()->values()->all())
        ->toContain(...array_map(fn (PageTypeEnum $type): string => $type->value, PageTypeEnum::cases()));
    expect($pages->map(fn (Page $page): ?string => $page->layout?->key)->unique()->values()->all())
        ->toContain(...array_map(fn (LayoutEnum $layout): string => $layout->value, LayoutEnum::cases()));

    $manifest = [
        'viewport' => ['width' => 1440, 'height' => 1100],
        'concurrency' => 2,
        'entries' => [],
    ];

    foreach ($expectedTypesAndLayoutsBySurface as $surface => $expected) {
        $page = $pages->first(
            fn (Page $candidate): bool => data_get($candidate->meta, 'theme_demo.surface') === $surface,
        );

        expect($page)->toBeInstanceOf(Page::class);
        expect($page->type?->key)->toBe($expected['type']);
        expect($page->layout?->key)->toBe($expected['layout']);
        expect($page->pageUrl)->toBeInstanceOf(PageUrl::class);

        $html = themeDemoScreenshotHtml($themeKey, $page, $surface);

        $htmlPath = themeDemoScreenshotHtmlPath($themeKey, $surface, $expected['type'], $expected['layout']);
        file_put_contents($htmlPath, $html);

        assertThemeDemoScreenshotHtmlContainsExpectedSurface($surface, $html);

        $manifest['entries'][] = [
            'surface' => $surface,
            'type' => $expected['type'],
            'layout' => $expected['layout'],
            'htmlPath' => $htmlPath,
            'screenshotPath' => themeDemoScreenshotPath($themeKey, $surface, $expected['type'], $expected['layout']),
            'viewport' => themeDemoScreenshotViewport($surface),
        ];
    }

    foreach (themeDemoExtraScreenshotEntries($themeKey) as $entry) {
        $htmlPath = themeDemoScreenshotHtmlPath($themeKey, $entry['surface'], $entry['type'], $entry['layout']);
        file_put_contents($htmlPath, $entry['html']);

        expect($entry['html'])->toContain($entry['expectedText']);

        $manifest['entries'][] = [
            'surface' => $entry['surface'],
            'type' => $entry['type'],
            'layout' => $entry['layout'],
            'htmlPath' => $htmlPath,
            'screenshotPath' => themeDemoScreenshotPath($themeKey, $entry['surface'], $entry['type'], $entry['layout']),
            'viewport' => themeDemoScreenshotViewport($entry['surface']),
        ];
    }

    $result = runThemeDemoScreenshotCapture($themeKey, $manifest);
    $expectedScreenshotNames = collect($manifest['entries'])
        ->map(fn (array $entry): string => basename($entry['screenshotPath']))
        ->sort()
        ->values()
        ->all();
    $actualScreenshotNames = collect(glob(themeDemoRepositoryPath('tests/Packages/Fixtures/theme-demo-layout-screenshots/' . $themeKey . '/*.png')) ?: [])
        ->map(fn (string $path): string => basename($path))
        ->sort()
        ->values()
        ->all();

    expect($result['entries'])->toHaveCount(count($manifest['entries']));
    expect(count($result['entries']))->toBeGreaterThanOrEqual(themeDemoMinimumScreenshotCount());
    expect($actualScreenshotNames)->toBe($expectedScreenshotNames);
    expect(array_sum(array_map(
        fn (array $entry): int => (int) ($entry['imageCount'] ?? 0),
        $result['entries'],
    )))->toBeGreaterThan(0);

    foreach ($result['entries'] as $entry) {
        expect($entry['screenshotPath'])->toBeFile()
            ->and($entry['loadedImageCount'])->toBe($entry['imageCount'])
            ->and($entry['blank'])->toBeFalse();
    }
}

function themeDemoScreenshotHtml(string $themeKey, Page $page, string $surface): string
{
    if (themeDemoScreenshotUsesRouteBackedHtml($page)) {
        return themeDemoScreenshotRouteBackedHtml($page, $surface);
    }

    return themeDemoScreenshotRenderedHtml($themeKey, $page, $surface);
}

function themeDemoScreenshotUsesRouteBackedHtml(Page $page): bool
{
    return $page->type?->key === PageTypeEnum::Default->value
        || $page->type?->key === PageTypeEnum::Home->value;
}

function themeDemoScreenshotRouteBackedHtml(Page $page, string $surface): string
{
    expect($page->pageUrl)->toBeInstanceOf(PageUrl::class);

    $response = get($page->pageUrl->full_url);
    $statusCode = 200;

    if ($response->baseResponse->getStatusCode() !== $statusCode) {
        throw new RuntimeException(themeDemoScreenshotRouteFailureMessage($surface, $page, $response, $statusCode));
    }

    $response->assertStatus($statusCode);
    assertThemeDemoRouteHtmlIsPublicSafe($response);

    return (string) $response->getContent();
}

function themeDemoScreenshotRouteFailureMessage(
    string $surface,
    Page $page,
    TestResponse $response,
    int $expectedStatusCode,
): string {
    $exceptionSummary = 'none';

    return sprintf(
        'Expected %s route [%s] to return %d, received %d. Type [%s] meta: %s. Exception: %s',
        $surface,
        $page->pageUrl->full_url ?? 'missing',
        $expectedStatusCode,
        $response->baseResponse->getStatusCode(),
        (string) $page->type?->key,
        json_encode($page->type?->meta, JSON_THROW_ON_ERROR),
        $exceptionSummary,
    );
}

function assertThemeDemoRouteHtmlIsPublicSafe(TestResponse $response): void
{
    expect($response->getContent())
        ->not->toContain('CapellFrontendAuthoring')
        ->not->toContain('capell-authoring')
        ->not->toContain('authoring/regions')
        ->not->toContain('edit_url')
        ->not->toContain('recordKey')
        ->not->toContain('capell-frontend-authoring');
}

function registerThemeDemoScreenshotPageAdapter(string $themeKey): void
{
    resolve(ThemePageAdapterRegistry::class)->register(
        $themeKey,
        fn (): ThemePageAdapter => new class implements ThemePageAdapter
        {
            public function currentPage(): ThemePageData
            {
                $page = Frontend::page();

                if ($page instanceof Page) {
                    return themeDemoScreenshotPageData($page, themeDemoScreenshotRenderData($page));
                }

                return new ThemePageData(
                    title: 'Theme Demo',
                    brand: new BrandProfileData,
                    sections: [
                        HeroSectionData::from([
                            'heading' => 'Theme Demo',
                            'summary' => 'Demo content is unavailable for this request.',
                        ]),
                    ],
                    navigation: new NavigationData(brandName: 'Theme Demo'),
                    footer: new FooterData(brandName: 'Theme Demo'),
                );
            }
        },
    );
}

function themeDemoScreenshotRenderedHtml(string $themeKey, Page $page, string $surface): string
{
    if ($surface === 'contact') {
        return themeDemoScreenshotContactLayoutHtml($page);
    }

    return resolve(ThemeRegistry::class)
        ->renderer($themeKey)
        ->render(themeDemoScreenshotPageData($page, themeDemoScreenshotRenderData($page)));
}

function themeDemoScreenshotPageSurface(Page $page): ?string
{
    $surface = data_get($page->meta, 'theme_demo.surface');

    if (is_string($surface)) {
        return $surface;
    }

    $meta = $page->meta;

    if (is_string($meta)) {
        $decoded = json_decode($meta, true);

        return is_array($decoded) ? data_get($decoded, 'theme_demo.surface') : null;
    }

    return null;
}

function themeDemoScreenshotContactLayoutHtml(Page $page): string
{
    $site = Site::query()->find($page->site_id);

    expect($site)->toBeInstanceOf(Site::class);

    $page->loadMissing(['translation']);
    $site->loadMissing(['translation', 'defaultDomain', 'siteDomain']);

    view()->addNamespace('capell-foundation-theme', themeDemoRepositoryPath('packages/foundation-theme/resources/views'));
    view()->addNamespace('capell', themeDemoRepositoryPath('packages/foundation-theme/resources/views'));

    return '<div data-screenshot-surface="contact">' . Blade::render((string) file_get_contents(
        themeDemoRepositoryPath('packages/foundation-theme/resources/views/components/demo/contact-page.blade.php'),
    ), [
        'page' => $page,
        'site' => $site,
    ]) . '</div>';
}

function assertThemeDemoScreenshotHtmlContainsExpectedSurface(string $surface, string $html): void
{
    expect($html)->toContain(themeDemoScreenshotExpectedText($surface));
}

/**
 * @return array<int, array{surface: string, type: string, layout: string, html: string, expectedText: string}>
 */
function themeDemoExtraScreenshotEntries(string $themeKey): array
{
    $viewNamespace = 'capell-theme-' . $themeKey;
    $articles = themeDemoScreenshotBlogArticles();
    $entries = themeDemoGenericReviewScreenshotEntries($themeKey);

    if ($themeKey === 'default') {
        return $entries;
    }

    if ($themeKey === 'commerce') {
        $entries[] = themeDemoCommerceSectionsScreenshotEntry();
    }

    if ($themeKey === 'healthcare') {
        $entries[] = themeDemoHealthcareSectionsScreenshotEntry();
        $entries[] = themeDemoHealthcareContactScreenshotEntry();
    }

    if ($themeKey === 'saas') {
        $entries[] = themeDemoSaasSectionsScreenshotEntry();
    }

    if ($themeKey === 'portfolio') {
        $entries[] = themeDemoPortfolioSectionsScreenshotEntry();
    }

    if ($themeKey === 'education') {
        $entries[] = themeDemoEducationSectionsScreenshotEntry();
    }

    if ($themeKey === 'nonprofit') {
        $entries[] = themeDemoNonprofitSectionsScreenshotEntry();
    }

    if ($themeKey === 'local-services') {
        $entries[] = themeDemoLocalServicesSectionsScreenshotEntry();
    }

    if ($themeKey === 'knowledge') {
        $entries[] = themeDemoKnowledgeSectionsScreenshotEntry();
    }

    if ($themeKey === 'corporate') {
        $entries[] = themeDemoCorporateSectionsScreenshotEntry();
    }

    if ($themeKey === 'agency') {
        $entries[] = themeDemoAgencySectionsScreenshotEntry();
    }

    if (view()->exists($viewNamespace . '::blog.index')) {
        $entries[] = [
            'surface' => 'blog-index',
            'type' => 'blog',
            'layout' => 'index',
            'html' => view($viewNamespace . '::blog.index', [
                'blogAvailable' => true,
                'articles' => $articles,
                'heading' => 'Screenshot demo blog index',
                'summary' => 'Screenshot demo blog index with enough entries to review cards, pagination, filters, and sidebar treatments.',
                'total' => 42,
                'from' => 1,
                'to' => count($articles),
                'searchEnabled' => true,
            ])->render(),
            'expectedText' => 'Screenshot demo blog index',
        ];
    }

    if (view()->exists($viewNamespace . '::blog.article')) {
        $entries[] = [
            'surface' => 'blog-article',
            'type' => 'blog',
            'layout' => 'article',
            'html' => view($viewNamespace . '::blog.article', [
                'blogAvailable' => true,
                'title' => 'Screenshot demo blog article',
                'summary' => 'Screenshot demo blog article with long-form copy, navigation, and suggestions.',
                'body' => 'This article body is intentionally plain text for screenshot review. It checks line length, typography, spacing, and article container contrast without requiring the Blog package.',
                'archiveUrl' => '#blog',
                'previousArticle' => $articles[0],
                'nextArticle' => $articles[1],
                'suggestions' => array_slice($articles, 2, 3),
            ])->render(),
            'expectedText' => 'Screenshot demo blog article',
        ];
    }

    return $entries;
}

/**
 * @return array{surface: string, type: string, layout: string, html: string, expectedText: string}
 */
function themeDemoCommerceSectionsScreenshotEntry(): array
{
    $navigation = new NavigationData(
        brandName: 'Commerce Demo',
        items: [
            ['label' => 'Catalog', 'url' => '#catalog'],
            ['label' => 'Products', 'url' => '#products'],
            ['label' => 'Guides', 'url' => '#guides'],
        ],
        ctaLabel: 'Shop',
        ctaUrl: '#shop',
    );

    $actions = [
        ['label' => 'View catalog', 'url' => '#catalog', 'style' => 'primary'],
        ['label' => 'Plan campaign', 'url' => '#campaign', 'style' => 'secondary'],
    ];

    $productFinder = new class implements ThemeSection
    {
        public function key(): string
        {
            return 'product-finder';
        }

        public function fallbackKey(): ?string
        {
            return null;
        }

        /**
         * @return array<string, mixed>
         */
        public function toViewData(): array
        {
            return [
                'section' => (object) [
                    'heading' => 'Find the right buying path',
                    'summary' => 'Product finder sections should expose useful retail filters and shopping intent.',
                    'items' => [
                        ['group' => 'Use', 'options' => ['Everyday', 'Travel', 'Gifting']],
                        ['group' => 'Margin', 'options' => ['Hero stock', 'Bundle', 'Clearance']],
                        ['group' => 'Season', 'options' => ['Launch', 'Peak', 'Evergreen']],
                    ],
                ],
            ];
        }
    };

    $collections = new class implements ThemeSection
    {
        public function key(): string
        {
            return 'collections';
        }

        public function fallbackKey(): ?string
        {
            return null;
        }

        /**
         * @return array<string, mixed>
         */
        public function toViewData(): array
        {
            return [
                'section' => (object) [
                    'heading' => 'Merchandised collection cards',
                    'summary' => 'Collections should feel editorial, image-led, and clearly shoppable.',
                    'items' => themeDemoScreenshotListingItems([], 'commerce-sections', 6),
                ],
            ];
        }
    };

    $productGrid = new class implements ThemeSection
    {
        public function key(): string
        {
            return 'product-grid';
        }

        public function fallbackKey(): ?string
        {
            return null;
        }

        /**
         * @return array<string, mixed>
         */
        public function toViewData(): array
        {
            return [
                'section' => (object) [
                    'heading' => 'Premium stock cards',
                    'summary' => 'Product cards should support dense retail scanning without losing visual warmth.',
                    'features' => collect(themeDemoScreenshotFeatures('commerce-sections', 8))
                        ->map(fn (array $feature, int $index): array => $feature + [
                            'price' => '$' . (48 + ($index * 12)),
                        ])
                        ->all(),
                ],
            ];
        }
    };

    $comparison = new class implements ThemeSection
    {
        public function key(): string
        {
            return 'comparison';
        }

        public function fallbackKey(): ?string
        {
            return null;
        }

        /**
         * @return array<string, mixed>
         */
        public function toViewData(): array
        {
            return [
                'section' => (object) [
                    'heading' => 'Compare buying missions',
                    'summary' => 'Comparison sections should help shoppers choose by intent, not only by feature.',
                    'items' => [
                        ['title' => 'New arrivals', 'summary' => 'High-velocity launch stock with fresh merchandising cues.'],
                        ['title' => 'Giftable bundles', 'summary' => 'Curated sets that increase average order value.'],
                        ['title' => 'Editorial staples', 'summary' => 'Evergreen products supported by buying guides.'],
                    ],
                ],
            ];
        }
    };

    $catalog = new class implements ThemeSection
    {
        public function key(): string
        {
            return 'catalog';
        }

        public function fallbackKey(): ?string
        {
            return null;
        }

        /**
         * @return array<string, mixed>
         */
        public function toViewData(): array
        {
            return [
                'section' => (object) [
                    'heading' => 'Catalog connection surface',
                    'summary' => 'Catalog panels should look intentional whether Shopify is installed or not.',
                    'items' => [
                        ['title' => 'Live inventory'],
                        ['title' => 'Editorial collections'],
                        ['title' => 'Campaign stock'],
                    ],
                ],
            ];
        }
    };

    $blogTeaser = new class implements ThemeSection
    {
        public function key(): string
        {
            return 'blog-teaser';
        }

        public function fallbackKey(): ?string
        {
            return null;
        }

        /**
         * @return array<string, mixed>
         */
        public function toViewData(): array
        {
            return [
                'section' => (object) [
                    'heading' => 'Retail buying guides',
                    'summary' => 'Resource cards should support commerce content without leaking package state.',
                    'items' => [
                        ['title' => 'Build a seasonal buying guide', 'summary' => 'Show shoppers how to choose by use case.', 'url' => '#guide-1'],
                        ['title' => 'Bundle stock without clutter', 'summary' => 'Keep related products easy to compare.', 'url' => '#guide-2'],
                        ['title' => 'Turn proof into product confidence', 'summary' => 'Use retail proof close to conversion paths.', 'url' => '#guide-3'],
                    ],
                ],
            ];
        }
    };

    $page = new ThemePageData(
        title: 'Screenshot demo commerce sections',
        brand: new BrandProfileData,
        sections: [
            HeroSectionData::from([
                'heading' => 'Screenshot demo commerce sections',
                'summary' => 'Commerce-specific sections check catalog panels, product grids, collection cards, retail proof, resources, and conversion surfaces.',
                'actions' => $actions,
            ]),
            $catalog,
            $productFinder,
            $productGrid,
            $collections,
            $comparison,
            ProofSectionData::from([
                'heading' => 'Retail proof ledger',
                'summary' => 'Proof should fit the merchandising story and stay fully visible on desktop.',
                'items' => themeDemoScreenshotProofItems(),
            ]),
            $blogTeaser,
            CtaSectionData::from([
                'heading' => 'Plan the next retail campaign',
                'summary' => 'Commerce CTAs should feel like a product and merchandising conversion path.',
                'actions' => $actions,
                'metrics' => [
                    ['value' => '24h', 'label' => 'Launch plan'],
                    ['value' => '3x', 'label' => 'Bundle paths'],
                    ['value' => '12', 'label' => 'Stock stories'],
                ],
            ]),
        ],
        navigation: $navigation,
        footer: new FooterData(
            brandName: $navigation->brandName,
            columns: [['heading' => 'Commerce', 'links' => $navigation->items]],
        ),
    );

    return [
        'surface' => 'commerce-sections',
        'type' => 'theme',
        'layout' => 'sections',
        'html' => resolve(ThemeRegistry::class)->renderer('commerce')->render($page),
        'expectedText' => 'Screenshot demo commerce sections',
    ];
}

/**
 * @return array{surface: string, type: string, layout: string, html: string, expectedText: string}
 */
function themeDemoHealthcareSectionsScreenshotEntry(): array
{
    $navigation = new NavigationData(
        brandName: 'Healthcare Demo',
        items: [
            ['label' => 'Services', 'url' => '#services'],
            ['label' => 'Clinicians', 'url' => '#clinicians'],
            ['label' => 'Resources', 'url' => '#resources'],
        ],
        ctaLabel: 'Book',
        ctaUrl: '#book',
    );

    $actions = [
        ['label' => 'Book appointment', 'url' => '#book', 'style' => 'primary'],
        ['label' => 'Find care', 'url' => '#services', 'style' => 'secondary'],
    ];

    $page = new ThemePageData(
        title: 'Screenshot demo healthcare sections',
        brand: new BrandProfileData,
        sections: [
            HeroSectionData::from([
                'heading' => 'Screenshot demo healthcare sections',
                'summary' => 'Healthcare-specific sections check service discovery, clinician cards, booking, sessions, care pathways, locations, resources, proof, and conversion surfaces.',
                'actions' => $actions,
            ]),
            themeDemoHealthcareGenericSection('service-finder', [
                'heading' => 'Find the right clinical route',
                'summary' => 'Service finder sections should help patients choose a route without feeling like a generic filter panel.',
                'items' => [
                    ['group' => 'Need', 'options' => ['GP', 'Diagnostics', 'Physio']],
                    ['group' => 'Access', 'options' => ['Same week', 'Remote', 'Specialist']],
                    ['group' => 'Patient', 'options' => ['Adult', 'Family', 'Corporate']],
                ],
            ]),
            themeDemoHealthcareGenericSection('services', [
                'heading' => 'Clinical service cards',
                'summary' => 'Service cards should show complete desktop grids while preserving mobile scanning.',
                'features' => themeDemoScreenshotFeatures('healthcare-sections', 8),
            ]),
            themeDemoHealthcareGenericSection('clinicians', [
                'heading' => 'Clinician pathway cards',
                'summary' => 'Clinician cards should feel like care routes rather than generic profile tiles.',
                'items' => [
                    ['type' => 'Consultant', 'title' => 'Dr Amara Patel', 'summary' => 'Rapid access medicine and referral planning.', 'url' => '#clinician-1'],
                    ['type' => 'Diagnostics', 'title' => 'Imaging team', 'summary' => 'Clear next steps after tests and scans.', 'url' => '#clinician-2'],
                    ['type' => 'Therapy', 'title' => 'Rehab clinic', 'summary' => 'Recovery plans for mobility and pain.', 'url' => '#clinician-3'],
                    ['type' => 'Virtual', 'title' => 'Remote care', 'summary' => 'Follow-up support after appointments.', 'url' => '#clinician-4'],
                ],
            ]),
            themeDemoHealthcareGenericSection('booking', [
                'heading' => 'Request the right appointment',
                'summary' => 'Booking sections should present useful service choices and next-step confidence.',
                'items' => [
                    ['title' => 'Same-week triage'],
                    ['title' => 'Specialist referral'],
                    ['title' => 'Remote follow-up'],
                ],
            ]),
            themeDemoHealthcareGenericSection('events', [
                'heading' => 'Care sessions and clinics',
                'summary' => 'Event fallbacks should look like intentional care sessions.',
                'items' => [
                    ['date' => 'Tue 09:30', 'title' => 'Heart health clinic', 'summary' => 'Consultant-led checks and advice.', 'url' => '#event-1'],
                    ['date' => 'Thu 14:00', 'title' => 'Physio assessment', 'summary' => 'Movement screening and treatment plans.', 'url' => '#event-2'],
                    ['date' => 'Fri 11:00', 'title' => 'Virtual medication review', 'summary' => 'Remote support for ongoing care.', 'url' => '#event-3'],
                ],
            ]),
            themeDemoHealthcareGenericSection('comparison', [
                'heading' => 'Compare care pathways',
                'summary' => 'Comparison sections should support patient choice, not plain feature grids.',
                'items' => [
                    ['title' => 'Rapid access', 'summary' => 'Same-week appointment with a clear referral path.'],
                    ['title' => 'Managed care', 'summary' => 'Ongoing plan with diagnostics, review, and follow-up.'],
                    ['title' => 'Remote support', 'summary' => 'Digital-first check-ins for lower-risk follow-up needs.'],
                    ['title' => 'Specialist route', 'summary' => 'Consultant-led pathway for complex conditions.'],
                ],
            ]),
            ProofSectionData::from([
                'heading' => 'Clinical trust indicators',
                'summary' => 'Proof should feel patient-safe and evidence-led.',
                'items' => themeDemoScreenshotProofItems(),
            ]),
            themeDemoHealthcareGenericSection('blog-teaser', [
                'heading' => 'Patient resource cards',
                'summary' => 'Resource cards should read like care guidance without leaking optional package state.',
                'items' => [
                    ['type' => 'Guide', 'title' => 'Preparing for a first consultation', 'summary' => 'What to bring and what to expect.', 'url' => '#resource-1'],
                    ['type' => 'Checklist', 'title' => 'Choosing the right service', 'summary' => 'Match symptoms and goals to care routes.', 'url' => '#resource-2'],
                    ['type' => 'Advice', 'title' => 'Aftercare questions', 'summary' => 'Follow-up prompts for recovery and review.', 'url' => '#resource-3'],
                ],
            ]),
            themeDemoHealthcareGenericSection('contact', [
                'heading' => 'Route contact by care need',
                'summary' => 'Contact cards should guide appointments, referrals, urgent access, and remote care.',
                'locations' => [
                    ['label' => 'Clinic', 'title' => 'Central clinic', 'summary' => 'Appointments and referrals.', 'phone' => '+44 20 0000 1000'],
                    ['label' => 'Urgent', 'title' => 'Rapid access', 'summary' => 'Priority questions and triage.', 'phone' => '+44 20 0000 2000'],
                    ['label' => 'Virtual', 'title' => 'Remote care', 'summary' => 'Digital consultations and follow-up.', 'phone' => '+44 20 0000 3000'],
                    ['label' => 'Partners', 'title' => 'Referral team', 'summary' => 'Clinical partner enquiries.', 'phone' => '+44 20 0000 4000'],
                ],
            ]),
            CtaSectionData::from([
                'heading' => 'Book the right care route',
                'summary' => 'Healthcare CTAs should move patients from uncertainty to the right next appointment.',
                'actions' => $actions,
            ]),
        ],
        navigation: $navigation,
        footer: new FooterData(
            brandName: $navigation->brandName,
            columns: [['heading' => 'Healthcare', 'links' => $navigation->items]],
        ),
    );

    return [
        'surface' => 'healthcare-sections',
        'type' => 'theme',
        'layout' => 'sections',
        'html' => resolve(ThemeRegistry::class)->renderer('healthcare')->render($page),
        'expectedText' => 'Screenshot demo healthcare sections',
    ];
}

/**
 * @param  array<string, mixed>  $viewData
 */
function themeDemoHealthcareGenericSection(string $key, array $viewData): ThemeSection
{
    return new readonly class($key, $viewData) implements ThemeSection
    {
        /**
         * @param  array<string, mixed>  $viewData
         */
        public function __construct(
            private string $sectionKey,
            private array $viewData,
        ) {}

        public function key(): string
        {
            return $this->sectionKey;
        }

        public function fallbackKey(): ?string
        {
            return null;
        }

        /**
         * @return array<string, mixed>
         */
        public function toViewData(): array
        {
            return ['section' => (object) $this->viewData];
        }
    };
}

/**
 * @return array{surface: string, type: string, layout: string, html: string, expectedText: string}
 */
function themeDemoSaasSectionsScreenshotEntry(): array
{
    $navigation = new NavigationData(
        brandName: 'SaaS Demo',
        items: [
            ['label' => 'Product', 'url' => '#product'],
            ['label' => 'Proof', 'url' => '#proof'],
            ['label' => 'Pricing', 'url' => '#pricing'],
        ],
        ctaLabel: 'Demo',
        ctaUrl: '#demo',
    );

    $actions = [
        ['label' => 'Start trial', 'url' => '#trial', 'style' => 'primary'],
        ['label' => 'Book demo', 'url' => '#demo', 'style' => 'secondary'],
    ];

    $comparison = new class implements ThemeSection
    {
        public function key(): string
        {
            return 'comparison';
        }

        public function fallbackKey(): ?string
        {
            return null;
        }

        /**
         * @return array<string, mixed>
         */
        public function toViewData(): array
        {
            return [
                'section' => (object) [
                    'heading' => 'Compare activation paths',
                    'summary' => 'Comparison sections should read like product decision support, not plain tables.',
                    'items' => [
                        ['title' => 'Self serve', 'summary' => 'Fast setup paths for high-intent trial teams.'],
                        ['title' => 'Sales assisted', 'summary' => 'Deeper onboarding for larger account motions.'],
                        ['title' => 'Expansion', 'summary' => 'Lifecycle nudges for teams ready to grow usage.'],
                    ],
                ],
            ];
        }
    };

    $calculator = new class implements ThemeSection
    {
        public function key(): string
        {
            return 'calculator';
        }

        public function fallbackKey(): ?string
        {
            return null;
        }

        /**
         * @return array<string, mixed>
         */
        public function toViewData(): array
        {
            return [
                'section' => (object) [
                    'heading' => 'Model the activation lift',
                    'summary' => 'Calculator sections should feel like a product planning surface.',
                    'items' => [
                        ['title' => 'Trial conversion', 'summary' => 'Improve trial-to-qualified-account movement.', 'metric' => '+18%'],
                        ['title' => 'Activation speed', 'summary' => 'Compress time-to-first-value for new teams.', 'metric' => '2.4x'],
                        ['title' => 'Expansion signal', 'summary' => 'Surface health scores before renewal risk appears.', 'metric' => '91%'],
                    ],
                ],
            ];
        }
    };

    $page = new ThemePageData(
        title: 'Screenshot demo SaaS sections',
        brand: new BrandProfileData,
        sections: [
            HeroSectionData::from([
                'heading' => 'Screenshot demo SaaS sections',
                'summary' => 'SaaS-specific sections check growth ledgers, resource pipelines, comparison, calculator, and conversion command surfaces.',
                'actions' => $actions,
            ]),
            ProofSectionData::from([
                'heading' => 'Growth proof ledger',
                'summary' => 'Proof should combine product telemetry, outcomes, and trust signals.',
                'items' => themeDemoScreenshotProofItems(),
            ]),
            FeatureSectionData::from([
                'heading' => 'Product workflow cards',
                'summary' => 'Feature cards should look like product capabilities rather than service cards.',
                'features' => themeDemoScreenshotFeatures('saas-sections', 6),
            ]),
            ContentListingSectionData::from([
                'heading' => 'Resource pipeline cards',
                'summary' => 'Listing cards should support resource scanning with product-led hierarchy.',
                'items' => themeDemoScreenshotListingItems([], 'saas-sections', 6),
            ]),
            $comparison,
            $calculator,
            CtaSectionData::from([
                'heading' => 'Launch the next growth experiment',
                'summary' => 'SaaS CTAs should feel like a product conversion surface with a clear pipeline.',
                'actions' => $actions,
            ]),
        ],
        navigation: $navigation,
        footer: new FooterData(
            brandName: $navigation->brandName,
            columns: [['heading' => 'Product', 'links' => $navigation->items]],
        ),
    );

    return [
        'surface' => 'saas-sections',
        'type' => 'theme',
        'layout' => 'sections',
        'html' => resolve(ThemeRegistry::class)->renderer('saas')->render($page),
        'expectedText' => 'Screenshot demo SaaS sections',
    ];
}

/**
 * @return array{surface: string, type: string, layout: string, html: string, expectedText: string}
 */
function themeDemoPortfolioSectionsScreenshotEntry(): array
{
    $navigation = new NavigationData(
        brandName: 'Portfolio Demo',
        items: [
            ['label' => 'Work', 'url' => '#work'],
            ['label' => 'Evidence', 'url' => '#evidence'],
            ['label' => 'Contact', 'url' => '#contact'],
        ],
        ctaLabel: 'Book',
        ctaUrl: '#contact',
    );

    $actions = [
        ['label' => 'View case studies', 'url' => '#work', 'style' => 'primary'],
        ['label' => 'Request media kit', 'url' => '#contact', 'style' => 'secondary'],
    ];

    $page = new ThemePageData(
        title: 'Screenshot demo portfolio sections',
        brand: new BrandProfileData,
        sections: [
            HeroSectionData::from([
                'heading' => 'Screenshot demo portfolio sections',
                'summary' => 'Portfolio-specific sections check editorial work cards, evidence ledgers, studio capabilities, and conversion surfaces.',
                'actions' => $actions,
            ]),
            ProofSectionData::from([
                'heading' => 'Evidence-led portfolio proof',
                'summary' => 'Proof blocks should feel like measurable creator outcomes instead of generic metric cards.',
                'items' => themeDemoScreenshotProofItems(),
            ]),
            FeatureSectionData::from([
                'heading' => 'Studio capability cards',
                'summary' => 'Capabilities should feel like a consultant or creator studio system.',
                'features' => themeDemoScreenshotFeatures('portfolio-sections', 6),
            ]),
            ContentListingSectionData::from([
                'heading' => 'Selected work index',
                'summary' => 'Listing cards should carry image-first case-study hierarchy and useful scanning cues.',
                'items' => themeDemoScreenshotListingItems([], 'portfolio-sections', 6),
            ]),
            CtaSectionData::from([
                'heading' => 'Plan the next portfolio story',
                'summary' => 'Portfolio CTAs should feel specific to creative direction and case-study planning.',
                'actions' => $actions,
            ]),
        ],
        navigation: $navigation,
        footer: new FooterData(
            brandName: $navigation->brandName,
            columns: [['heading' => 'Portfolio', 'links' => $navigation->items]],
        ),
    );

    return [
        'surface' => 'portfolio-sections',
        'type' => 'theme',
        'layout' => 'sections',
        'html' => resolve(ThemeRegistry::class)->renderer('portfolio')->render($page),
        'expectedText' => 'Screenshot demo portfolio sections',
    ];
}

/**
 * @return array{surface: string, type: string, layout: string, html: string, expectedText: string}
 */
function themeDemoEducationSectionsScreenshotEntry(): array
{
    $navigation = new NavigationData(
        brandName: 'Education Demo',
        items: [
            ['label' => 'Courses', 'url' => '#courses'],
            ['label' => 'Events', 'url' => '#events'],
            ['label' => 'Apply', 'url' => '#apply'],
        ],
        ctaLabel: 'Apply',
        ctaUrl: '#apply',
    );

    $page = new ThemePageData(
        title: 'Screenshot demo education sections',
        brand: new BrandProfileData,
        sections: [
            HeroSectionData::from([
                'heading' => 'Screenshot demo education sections',
                'summary' => 'Education-specific sections check course, instructor, event, enrolment, resource, and FAQ presentation.',
                'actions' => [['label' => 'Browse courses', 'url' => '#courses', 'style' => 'primary']],
            ]),
            themeDemoEducationGenericSection('course-catalog', 'Course catalogue'),
            themeDemoEducationGenericSection('instructors', 'Instructor team'),
            themeDemoEducationGenericSection('events', 'Open days and workshops'),
            themeDemoEducationGenericSection('enrolment-cta', 'Apply for the next cohort'),
            themeDemoEducationGenericSection('resources', 'Learning resources'),
            themeDemoEducationGenericSection('faq', 'Learner questions'),
            CtaSectionData::from([
                'heading' => 'Start with the right programme',
                'summary' => 'Education sections should support discovery and conversion together.',
                'actions' => [['label' => 'Apply now', 'url' => '#apply', 'style' => 'primary']],
            ]),
        ],
        navigation: $navigation,
        footer: new FooterData(
            brandName: $navigation->brandName,
            columns: [['heading' => 'Education', 'links' => $navigation->items]],
        ),
    );

    return [
        'surface' => 'education-sections',
        'type' => 'theme',
        'layout' => 'sections',
        'html' => resolve(ThemeRegistry::class)->renderer('education')->render($page),
        'expectedText' => 'Screenshot demo education sections',
    ];
}

function themeDemoEducationGenericSection(string $key, string $heading): ThemeSection
{
    return new class($key, $heading) implements ThemeSection
    {
        public function __construct(
            private readonly string $sectionKey,
            public readonly string $heading,
        ) {}

        public function key(): string
        {
            return $this->sectionKey;
        }

        public function fallbackKey(): ?string
        {
            return null;
        }

        /**
         * @return array<string, mixed>
         */
        public function toViewData(): array
        {
            return [
                'section' => $this,
                'heading' => $this->heading,
            ];
        }
    };
}

/**
 * @return array{surface: string, type: string, layout: string, html: string, expectedText: string}
 */
function themeDemoNonprofitSectionsScreenshotEntry(): array
{
    $navigation = new NavigationData(
        brandName: 'Nonprofit Demo',
        items: [
            ['label' => 'Impact', 'url' => '#impact'],
            ['label' => 'Campaigns', 'url' => '#campaigns'],
            ['label' => 'Support', 'url' => '#support'],
        ],
        ctaLabel: 'Donate',
        ctaUrl: '#donate',
    );

    $page = new ThemePageData(
        title: 'Screenshot demo nonprofit sections',
        brand: new BrandProfileData,
        sections: [
            HeroSectionData::from([
                'heading' => 'Screenshot demo nonprofit sections',
                'summary' => 'Nonprofit-specific sections check impact, campaign, supporter, event, story, and contact presentation.',
                'actions' => [['label' => 'Support the work', 'url' => '#support', 'style' => 'primary']],
            ]),
            themeDemoNonprofitGenericSection('impact', 'Impact evidence'),
            themeDemoNonprofitGenericSection('campaigns', 'Current campaigns'),
            themeDemoNonprofitGenericSection('volunteer-donate', 'Volunteer or donate'),
            themeDemoNonprofitGenericSection('events', 'Community events'),
            themeDemoNonprofitGenericSection('stories', 'Supporter stories'),
            themeDemoNonprofitGenericSection('contact', 'Route supporter questions'),
            CtaSectionData::from([
                'heading' => 'Back the next campaign',
                'summary' => 'Nonprofit sections should move supporters from belief to action.',
                'actions' => [['label' => 'Donate now', 'url' => '#donate', 'style' => 'primary']],
            ]),
        ],
        navigation: $navigation,
        footer: new FooterData(
            brandName: $navigation->brandName,
            columns: [['heading' => 'Nonprofit', 'links' => $navigation->items]],
        ),
    );

    return [
        'surface' => 'nonprofit-sections',
        'type' => 'theme',
        'layout' => 'sections',
        'html' => resolve(ThemeRegistry::class)->renderer('nonprofit')->render($page),
        'expectedText' => 'Screenshot demo nonprofit sections',
    ];
}

function themeDemoNonprofitGenericSection(string $key, string $heading): ThemeSection
{
    return new class($key, $heading) implements ThemeSection
    {
        public function __construct(
            private readonly string $sectionKey,
            public readonly string $heading,
        ) {}

        public function key(): string
        {
            return $this->sectionKey;
        }

        public function fallbackKey(): ?string
        {
            return null;
        }

        /**
         * @return array<string, mixed>
         */
        public function toViewData(): array
        {
            return [
                'section' => $this,
                'heading' => $this->heading,
            ];
        }
    };
}

/**
 * @return array{surface: string, type: string, layout: string, html: string, expectedText: string}
 */
function themeDemoLocalServicesSectionsScreenshotEntry(): array
{
    $navigation = new NavigationData(
        brandName: 'Local Services Demo',
        items: [
            ['label' => 'Services', 'url' => '#services'],
            ['label' => 'Areas', 'url' => '#areas'],
            ['label' => 'Quote', 'url' => '#quote'],
        ],
        ctaLabel: 'Request quote',
        ctaUrl: '#quote',
    );

    $page = new ThemePageData(
        title: 'Screenshot demo local service sections',
        brand: new BrandProfileData,
        sections: [
            HeroSectionData::from([
                'heading' => 'Screenshot demo local service sections',
                'summary' => 'Local-services-specific sections check dispatch, service routes, area coverage, quote intake, resources, and contact routing.',
                'actions' => [['label' => 'Request quote', 'url' => '#quote', 'style' => 'primary']],
            ]),
            FeatureSectionData::from([
                'heading' => 'Dispatch-ready service paths',
                'summary' => 'Feature cards should feel like local job routes and quote workflows.',
                'features' => themeDemoScreenshotFeatures('local-service-sections', 6),
            ]),
            ProofSectionData::from([
                'heading' => 'Local proof board',
                'summary' => 'Proof cards should read as service performance signals.',
                'items' => themeDemoScreenshotProofItems(),
            ]),
            ContentListingSectionData::from([
                'heading' => 'Service route cards',
                'summary' => 'Listing cards should show service, area, and availability cues.',
                'items' => themeDemoScreenshotListingItems([], 'local-service-sections', 4),
            ]),
            themeDemoLocalServicesGenericSection('services', 'Bookable services'),
            themeDemoLocalServicesGenericSection('service-areas', 'Live service areas'),
            themeDemoLocalServicesGenericSection('quote-form', 'Request a quote'),
            themeDemoLocalServicesGenericSection('case-studies', 'Recent local wins'),
            themeDemoLocalServicesGenericSection('resources', 'Service resources'),
            themeDemoLocalServicesGenericSection('contact', 'Route local enquiries'),
            CtaSectionData::from([
                'heading' => 'Turn the next enquiry into booked work',
                'summary' => 'Local Services sections should move from route, to quote, to confirmed service.',
                'actions' => [['label' => 'Check availability', 'url' => '#quote', 'style' => 'primary']],
            ]),
        ],
        navigation: $navigation,
        footer: new FooterData(
            brandName: $navigation->brandName,
            columns: [['heading' => 'Local services', 'links' => $navigation->items]],
        ),
    );

    return [
        'surface' => 'local-services-sections',
        'type' => 'theme',
        'layout' => 'sections',
        'html' => resolve(ThemeRegistry::class)->renderer('local-services')->render($page),
        'expectedText' => 'Screenshot demo local service sections',
    ];
}

function themeDemoLocalServicesGenericSection(string $key, string $heading): ThemeSection
{
    return new class($key, $heading) implements ThemeSection
    {
        public function __construct(
            private readonly string $sectionKey,
            public readonly string $heading,
        ) {}

        public function key(): string
        {
            return $this->sectionKey;
        }

        public function fallbackKey(): ?string
        {
            return null;
        }

        /**
         * @return array<string, mixed>
         */
        public function toViewData(): array
        {
            return [
                'section' => $this,
                'heading' => $this->heading,
            ];
        }
    };
}

/**
 * @return array{surface: string, type: string, layout: string, html: string, expectedText: string}
 */
function themeDemoKnowledgeSectionsScreenshotEntry(): array
{
    $navigation = new NavigationData(
        brandName: 'Knowledge Demo',
        items: [
            ['label' => 'Topics', 'url' => '#topics'],
            ['label' => 'Library', 'url' => '#library'],
            ['label' => 'Authors', 'url' => '#authors'],
        ],
        ctaLabel: 'Browse guides',
        ctaUrl: '#library',
    );

    $page = new ThemePageData(
        title: 'Screenshot demo knowledge sections',
        brand: new BrandProfileData,
        sections: [
            HeroSectionData::from([
                'heading' => 'Screenshot demo knowledge sections',
                'summary' => 'Knowledge-specific sections check research pathways, archive rows, topic hubs, search, newsletter, and editorial team presentation.',
                'actions' => [['label' => 'Browse guides', 'url' => '#library', 'style' => 'primary']],
            ]),
            FeatureSectionData::from([
                'heading' => 'Research pathway cards',
                'summary' => 'Feature cards should look like curated library entries and not generic marketing blocks.',
                'features' => themeDemoScreenshotFeatures('knowledge-sections', 6),
            ]),
            ProofSectionData::from([
                'heading' => 'Library evidence board',
                'summary' => 'Proof cards should read as content depth and discovery signals.',
                'items' => themeDemoScreenshotProofItems(),
            ]),
            ContentListingSectionData::from([
                'heading' => 'Archive result rows',
                'summary' => 'Listings should support scanning, saved-state cues, and resource metadata.',
                'items' => themeDemoScreenshotListingItems([], 'knowledge-sections', 4),
            ]),
            themeDemoKnowledgeGenericSection('topic-hubs', 'Topic hubs'),
            themeDemoKnowledgeGenericSection('featured-content', 'Featured research'),
            themeDemoKnowledgeGenericSection('resource-library', 'Resource library'),
            themeDemoKnowledgeGenericSection('search-listing', 'Search the archive'),
            themeDemoKnowledgeGenericSection('authors', 'Editorial contributors'),
            themeDemoKnowledgeGenericSection('newsletter', 'Weekly research digest'),
            CtaSectionData::from([
                'heading' => 'Keep the library moving',
                'summary' => 'Knowledge sections should move readers from discovery to saved resources and return visits.',
                'actions' => [['label' => 'Browse resources', 'url' => '#library', 'style' => 'primary']],
            ]),
        ],
        navigation: $navigation,
        footer: new FooterData(
            brandName: $navigation->brandName,
            columns: [['heading' => 'Knowledge', 'links' => $navigation->items]],
        ),
    );

    return [
        'surface' => 'knowledge-sections',
        'type' => 'theme',
        'layout' => 'sections',
        'html' => resolve(ThemeRegistry::class)->renderer('knowledge')->render($page),
        'expectedText' => 'Screenshot demo knowledge sections',
    ];
}

function themeDemoKnowledgeGenericSection(string $key, string $heading): ThemeSection
{
    return new class($key, $heading) implements ThemeSection
    {
        public function __construct(
            private readonly string $sectionKey,
            public readonly string $heading,
        ) {}

        public function key(): string
        {
            return $this->sectionKey;
        }

        public function fallbackKey(): ?string
        {
            return null;
        }

        /**
         * @return array<string, mixed>
         */
        public function toViewData(): array
        {
            return [
                'section' => $this,
                'heading' => $this->heading,
            ];
        }
    };
}

/**
 * @return array{surface: string, type: string, layout: string, html: string, expectedText: string}
 */
function themeDemoCorporateSectionsScreenshotEntry(): array
{
    $navigation = new NavigationData(
        brandName: 'Corporate Demo',
        items: [
            ['label' => 'Governance', 'url' => '#governance'],
            ['label' => 'Reports', 'url' => '#reports'],
            ['label' => 'Contact', 'url' => '#contact'],
        ],
        ctaLabel: 'Contact',
        ctaUrl: '#contact',
    );

    $page = new ThemePageData(
        title: 'Screenshot demo corporate sections',
        brand: new BrandProfileData,
        sections: [
            HeroSectionData::from([
                'heading' => 'Screenshot demo corporate sections',
                'summary' => 'Corporate-specific sections check governance cards, assurance metrics, board-ready listing rows, and formal conversion surfaces.',
                'actions' => [['label' => 'View reports', 'url' => '#reports', 'style' => 'primary']],
            ]),
            FeatureSectionData::from([
                'heading' => 'Governance operating model',
                'summary' => 'Feature cards should read as policy, risk, delivery, and reporting systems.',
                'features' => themeDemoScreenshotFeatures('corporate-sections', 6),
            ]),
            ProofSectionData::from([
                'heading' => 'Assurance evidence',
                'summary' => 'Proof cards should present sober board-grade metrics and outcomes.',
                'items' => themeDemoScreenshotProofItems(),
            ]),
            ContentListingSectionData::from([
                'heading' => 'Board register rows',
                'summary' => 'Listing cards should avoid cramped editorial cards and scan like formal register entries.',
                'items' => themeDemoScreenshotListingItems([], 'corporate-sections', 5),
            ]),
            CtaSectionData::from([
                'heading' => 'Move the next decision forward',
                'summary' => 'Corporate sections should support briefing, review, approval, and accountable follow-through.',
                'actions' => [['label' => 'Book advisory', 'url' => '#contact', 'style' => 'primary']],
            ]),
        ],
        navigation: $navigation,
        footer: new FooterData(
            brandName: $navigation->brandName,
            columns: [['heading' => 'Corporate', 'links' => $navigation->items]],
        ),
    );

    return [
        'surface' => 'corporate-sections',
        'type' => 'theme',
        'layout' => 'sections',
        'html' => resolve(ThemeRegistry::class)->renderer('corporate')->render($page),
        'expectedText' => 'Screenshot demo corporate sections',
    ];
}

/**
 * @return array{surface: string, type: string, layout: string, html: string, expectedText: string}
 */
function themeDemoAgencySectionsScreenshotEntry(): array
{
    $navigation = new NavigationData(
        brandName: 'Agency Demo',
        items: [
            ['label' => 'Work', 'url' => '#work'],
            ['label' => 'Studio', 'url' => '#studio'],
            ['label' => 'Launch', 'url' => '#launch'],
        ],
        ctaLabel: 'Start',
        ctaUrl: '#launch',
    );

    $page = new ThemePageData(
        title: 'Screenshot demo agency sections',
        brand: new BrandProfileData,
        sections: [
            HeroSectionData::from([
                'heading' => 'Screenshot demo agency sections',
                'summary' => 'Agency-specific sections check campaign boards, work-wall cards, proof reels, and launch-room conversion surfaces.',
                'actions' => [['label' => 'View work', 'url' => '#work', 'style' => 'primary']],
            ]),
            FeatureSectionData::from([
                'heading' => 'Campaign system cards',
                'summary' => 'Feature cards should feel like a studio production system, not generic service cards.',
                'features' => themeDemoScreenshotFeatures('agency-sections', 6),
            ]),
            ProofSectionData::from([
                'heading' => 'Studio proof wall',
                'summary' => 'Proof should combine visual confidence, campaign metrics, and clear outcomes.',
                'items' => themeDemoScreenshotProofItems(),
            ]),
            ContentListingSectionData::from([
                'heading' => 'Work wall cards',
                'summary' => 'Listing cards should use real media when present and high-quality studio boards otherwise.',
                'items' => themeDemoScreenshotListingItems([], 'agency-sections', 6),
            ]),
            CtaSectionData::from([
                'heading' => 'Open the next launch room',
                'summary' => 'Agency CTAs should feel campaign-led and immediate.',
                'actions' => [['label' => 'Start a brief', 'url' => '#launch', 'style' => 'primary']],
            ]),
        ],
        navigation: $navigation,
        footer: new FooterData(
            brandName: $navigation->brandName,
            columns: [['heading' => 'Agency', 'links' => $navigation->items]],
        ),
    );

    return [
        'surface' => 'agency-sections',
        'type' => 'theme',
        'layout' => 'sections',
        'html' => resolve(ThemeRegistry::class)->renderer('agency')->render($page),
        'expectedText' => 'Screenshot demo agency sections',
    ];
}

/**
 * @return array{surface: string, type: string, layout: string, html: string, expectedText: string}
 */
function themeDemoHealthcareContactScreenshotEntry(): array
{
    $section = new class(heading: 'Screenshot demo healthcare contact', summary: 'Dedicated healthcare contact cards verify the theme-specific contact section.', locations: [['label' => 'Clinic', 'title' => 'Central clinic', 'summary' => 'Appointments, referrals, and care plans routed from one place.', 'phone' => '+44 20 0000 1000'], ['label' => 'Urgent', 'title' => 'Rapid access', 'summary' => 'Fast-track support for priority care questions.', 'phone' => '+44 20 0000 2000'], ['label' => 'Virtual', 'title' => 'Remote care', 'summary' => 'Digital-first consultations and follow-up support.', 'phone' => '+44 20 0000 3000'], ['label' => 'Partners', 'title' => 'Referral team', 'summary' => 'Partnership and clinical referral enquiries.', 'phone' => '+44 20 0000 4000']]) implements ThemeSection
    {
        /**
         * @param  array<int, array<string, string>>  $locations
         */
        public function __construct(
            public string $heading,
            public ?string $summary,
            public array $locations,
        ) {}

        public function key(): string
        {
            return 'contact';
        }

        public function fallbackKey(): ?string
        {
            return null;
        }

        public function toViewData(): array
        {
            return ['section' => $this];
        }
    };

    $page = new ThemePageData(
        title: 'Screenshot demo healthcare contact',
        brand: new BrandProfileData,
        sections: [
            HeroSectionData::from([
                'heading' => 'Screenshot demo healthcare contact',
                'summary' => 'A healthcare-specific contact review uses the real contact section renderer.',
            ]),
            $section,
            CtaSectionData::from([
                'heading' => 'Book the right route',
                'summary' => 'This verifies the contact section and the healthcare CTA together.',
                'actions' => [['label' => 'Start contact', 'url' => '#contact', 'style' => 'primary']],
            ]),
        ],
        navigation: new NavigationData(brandName: 'Healthcare Demo'),
        footer: new FooterData(brandName: 'Healthcare Demo'),
    );

    return [
        'surface' => 'healthcare-contact-section',
        'type' => 'theme',
        'layout' => 'contact',
        'html' => resolve(ThemeRegistry::class)->renderer('healthcare')->render($page),
        'expectedText' => 'Screenshot demo healthcare contact',
    ];
}

/**
 * @return array<int, array{surface: string, type: string, layout: string, html: string, expectedText: string}>
 */
function themeDemoGenericReviewScreenshotEntries(string $themeKey): array
{
    return [
        themeDemoGenericReviewScreenshotEntry(
            themeKey: $themeKey,
            surface: 'visual-review',
            layout: 'full',
            expectedText: 'Screenshot demo visual review',
            title: 'Screenshot demo visual review',
            summary: 'A broad theme review surface checks hero, proof, feature, listing, CTA, navigation, and footer rhythm in one screenshot.',
        ),
        themeDemoGenericReviewScreenshotEntry(
            themeKey: $themeKey,
            surface: 'system-review',
            layout: 'system',
            expectedText: 'Screenshot demo system review',
            title: 'Screenshot demo system review',
            summary: 'A compact system-style review surface checks support copy, recovery actions, and smaller page structure.',
        ),
    ];
}

/**
 * @return array{surface: string, type: string, layout: string, html: string, expectedText: string}
 */
function themeDemoGenericReviewScreenshotEntry(
    string $themeKey,
    string $surface,
    string $layout,
    string $expectedText,
    string $title,
    string $summary,
): array {
    $actions = [
        ['label' => 'Primary action', 'url' => '#primary', 'style' => 'primary'],
        ['label' => 'Secondary action', 'url' => '#secondary', 'style' => 'secondary'],
    ];
    $navigation = new NavigationData(
        brandName: ucfirst(str_replace('-', ' ', $themeKey)) . ' Demo',
        items: [
            ['label' => 'Home', 'url' => '#home'],
            ['label' => 'Review', 'url' => '#review'],
            ['label' => 'Contact', 'url' => '#contact'],
        ],
        ctaLabel: 'Contact',
        ctaUrl: '#contact',
    );
    $page = new ThemePageData(
        title: $title,
        brand: new BrandProfileData,
        sections: [
            HeroSectionData::from([
                'heading' => $title,
                'summary' => $summary,
                'actions' => $actions,
            ]),
            ProofSectionData::from([
                'heading' => 'Review proof points',
                'summary' => 'The count test keeps the visual review set broad enough to be useful.',
                'items' => themeDemoScreenshotProofItems(),
            ]),
            FeatureSectionData::from([
                'heading' => 'Review feature cards',
                'summary' => 'Repeated cards catch missing titles, unreadable copy, and awkward spacing.',
                'features' => themeDemoScreenshotFeatures($surface, 4),
            ]),
            ContentListingSectionData::from([
                'heading' => 'Review listing cards',
                'summary' => 'Listing cards cover repeated link/card treatment outside the homepage.',
                'items' => themeDemoScreenshotListingItems([], $surface, 4),
            ]),
            CtaSectionData::from([
                'heading' => 'Review conversion section',
                'summary' => 'CTA treatment should be readable in every theme.',
                'actions' => $actions,
            ]),
        ],
        navigation: $navigation,
        footer: new FooterData(
            brandName: $navigation->brandName,
            columns: [['heading' => 'Review', 'links' => $navigation->items]],
        ),
    );

    return [
        'surface' => $surface,
        'type' => 'theme',
        'layout' => $layout,
        'html' => resolve(ThemeRegistry::class)->renderer($themeKey)->render($page),
        'expectedText' => $expectedText,
    ];
}

function themeDemoMinimumScreenshotCount(): int
{
    return 10;
}

/**
 * @return array<int, array<string, string>>
 */
function themeDemoScreenshotBlogArticles(): array
{
    return collect(range(1, 6))
        ->map(fn (int $number): array => [
            'title' => 'Screenshot blog article ' . $number,
            'summary' => 'Blog card copy for screenshot review across index, article navigation, and suggested reading.',
            'url' => '#blog-article-' . $number,
            'type' => 'Insight',
        ])
        ->all();
}

/**
 * @param  Collection<int, Page>  $pages
 */
function createThemeDemoScreenshotMaintenancePage(string $themeKey, Collection $pages): void
{
    $sourcePage = $pages->first();

    if (! $sourcePage instanceof Page || Page::query()
        ->where('site_id', $sourcePage->site_id)
        ->where('meta->theme_demo->theme_key', $themeKey)
        ->where('meta->theme_demo->surface', 'maintenance')
        ->exists()) {
        return;
    }

    $site = Site::query()->find($sourcePage->site_id);

    if (! $site instanceof Site) {
        return;
    }

    $languages = Language::query()->get();
    $themeName = ucfirst(str_replace('-', ' ', $themeKey));
    $renderData = [
        'summary' => 'Screenshot demo maintenance for the ' . $themeName . ' theme.',
        'hero' => [
            'heading' => $themeName . ' maintenance mode',
            'summary' => 'Screenshot demo maintenance with status copy, recovery actions, and supporting content.',
            'actions' => [
                ['label' => 'Check status', 'url' => '#status', 'style' => 'primary'],
                ['label' => 'Contact support', 'url' => '#contact', 'style' => 'secondary'],
            ],
        ],
        'features_heading' => 'Maintenance status checks',
        'features_summary' => 'System pages need the same visual coverage as content pages.',
        'features' => themeDemoScreenshotFeatures('maintenance', 3),
        'proof' => [
            'heading' => 'Maintenance proof points',
            'items' => themeDemoScreenshotProofItems(),
        ],
        'cta' => [
            'heading' => 'Back online soon',
            'summary' => 'Maintenance screenshots verify recovery messaging and action styling.',
            'actions' => [
                ['label' => 'Return home', 'url' => '#home', 'style' => 'primary'],
            ],
        ],
    ];

    /** @var Page $page */
    $page = resolve(PageCreator::class)->createPage([
        'name' => $themeName . ' Demo Maintenance',
        'type_key' => PageTypeEnum::Maintenance,
        'layout_key' => LayoutEnum::System,
        'visible_from' => now()->subDay()->format('Y-m-d'),
        'meta' => [
            'theme_demo' => [
                'theme_key' => $themeKey,
                'surface' => 'maintenance',
                'render_data' => $renderData,
            ],
            'robots' => ['noindex' => true],
        ],
        'translations' => $languages
            ->mapWithKeys(fn (Language $language): array => [
                (string) $language->code => [
                    'title' => $themeName . ' Demo Maintenance',
                    'content' => '<h2>Maintenance preview</h2><p>Maintenance mode screenshot content.</p>',
                    'summary' => $renderData['summary'],
                    'meta' => [
                        'description' => $renderData['summary'],
                        'hero' => $renderData['hero']['summary'],
                        'hero_title' => $renderData['hero']['heading'],
                        'label' => $themeName . ' Demo Maintenance',
                        'link_text' => 'Check status',
                        'slug' => 'theme-' . $themeKey . '-maintenance',
                        'theme_demo' => $renderData,
                    ],
                ],
            ])
            ->all(),
    ], $site, $languages);

    $page->forceFill(['order' => 8])->save();
    SetupPageUrlsAction::run($page);
}

/**
 * @param  Collection<int, Page>  $pages
 */
function createThemeDemoScreenshotSystemPage(string $themeKey, Collection $pages): void
{
    $sourcePage = $pages->first();

    if (! $sourcePage instanceof Page || Page::query()
        ->where('site_id', $sourcePage->site_id)
        ->where('meta->theme_demo->theme_key', $themeKey)
        ->where('meta->theme_demo->surface', 'system')
        ->exists()) {
        return;
    }

    $site = Site::query()->find($sourcePage->site_id);

    if (! $site instanceof Site) {
        return;
    }

    $languages = Language::query()->get();
    $themeName = ucfirst(str_replace('-', ' ', $themeKey));
    $renderData = [
        'summary' => 'Screenshot demo system page for the ' . $themeName . ' theme.',
        'hero' => [
            'heading' => $themeName . ' system page',
            'summary' => 'Screenshot demo system page with operational copy, recovery actions, and support context.',
            'actions' => [
                ['label' => 'Open support', 'url' => '#support', 'style' => 'primary'],
                ['label' => 'View status', 'url' => '#status', 'style' => 'secondary'],
            ],
        ],
        'features_heading' => 'System page checks',
        'features_summary' => 'System page type screenshots make the core type and layout matrix explicit.',
        'features' => themeDemoScreenshotFeatures('system', 3),
        'cta' => [
            'heading' => 'System page recovery action',
            'summary' => 'System pages need the same readable CTA treatment as marketing pages.',
            'actions' => [
                ['label' => 'Continue', 'url' => '#continue', 'style' => 'primary'],
            ],
        ],
    ];

    /** @var Page $page */
    $page = resolve(PageCreator::class)->createPage([
        'name' => $themeName . ' Demo System',
        'type_key' => PageTypeEnum::System,
        'layout_key' => LayoutEnum::System,
        'visible_from' => now()->subDay()->format('Y-m-d'),
        'meta' => [
            'theme_demo' => [
                'theme_key' => $themeKey,
                'surface' => 'system',
                'render_data' => $renderData,
            ],
            'robots' => ['noindex' => true],
        ],
        'translations' => $languages
            ->mapWithKeys(fn (Language $language): array => [
                (string) $language->code => [
                    'title' => $themeName . ' Demo System',
                    'content' => '<h2>System page preview</h2><p>System page screenshot content.</p>',
                    'summary' => $renderData['summary'],
                    'meta' => [
                        'description' => $renderData['summary'],
                        'hero' => $renderData['hero']['summary'],
                        'hero_title' => $renderData['hero']['heading'],
                        'label' => $themeName . ' Demo System',
                        'link_text' => 'Open support',
                        'slug' => 'theme-' . $themeKey . '-system',
                        'theme_demo' => $renderData,
                    ],
                ],
            ])
            ->all(),
    ], $site, $languages);

    $page->forceFill(['order' => 9])->save();
    SetupPageUrlsAction::run($page);
}

/**
 * @param  Collection<int, Page>  $pages
 */
function enrichThemeDemoScreenshotFixture(string $themeKey, Collection $pages): void
{
    foreach ($pages as $page) {
        $surface = data_get($page->meta, 'theme_demo.surface');

        if (! is_string($surface)) {
            continue;
        }

        $renderData = themeDemoScreenshotRenderData($page);
        $page->meta = array_replace_recursive(
            is_array($page->meta) ? $page->meta : [],
            [
                'theme_demo' => [
                    'render_data' => themeDemoScreenshotEnrichedRenderData($themeKey, $surface, $renderData),
                ],
            ],
        );
        $page->save();
    }
}

/**
 * @return array<string, mixed>
 */
function themeDemoScreenshotRenderData(Page $page): array
{
    $renderData = data_get($page->meta, 'theme_demo.render_data');

    if (is_array($renderData)) {
        return $renderData;
    }

    $translationRenderData = data_get($page->translations->first()?->meta, 'theme_demo');

    return is_array($translationRenderData) ? $translationRenderData : [];
}

/**
 * @param  array<string, mixed>  $renderData
 * @return array<string, mixed>
 */
function themeDemoScreenshotEnrichedRenderData(string $themeKey, string $surface, array $renderData): array
{
    $themeName = ucfirst(str_replace('-', ' ', $themeKey));
    $expectedText = themeDemoScreenshotExpectedText($surface);
    $mediaUrls = themeDemoScreenshotMediaUrls($renderData);
    $primaryMedia = $mediaUrls[0] ?? data_get($renderData, 'hero.mediaUrl', data_get($renderData, 'mediaUrl'));
    $actions = [
        ['label' => 'View preview', 'url' => '#preview', 'style' => 'primary'],
        ['label' => 'Talk to team', 'url' => '#contact', 'style' => 'secondary'],
        ['label' => 'Compare layouts', 'url' => '#layouts', 'style' => 'secondary'],
    ];

    $base = array_replace_recursive($renderData, [
        'summary' => $expectedText . ' for the ' . $themeName . ' theme.',
        'actions' => $actions,
    ]);

    $base['hero'] = array_replace_recursive(
        is_array(data_get($renderData, 'hero')) ? data_get($renderData, 'hero') : [],
        [
            'heading' => themeDemoScreenshotHeroHeading($themeName, $surface),
            'summary' => $expectedText . ' with richer seeded content, media, actions, and supporting sections.',
            'actions' => $actions,
            'mediaUrl' => is_string($primaryMedia) ? $primaryMedia : null,
            'mediaAlt' => $themeName . ' screenshot demo media',
        ],
    );

    return array_replace_recursive($base, themeDemoScreenshotSurfaceRenderData($surface, $mediaUrls, $actions));
}

/**
 * @param  array<int, string>  $mediaUrls
 * @param  array<int, array<string, string>>  $actions
 * @return array<string, mixed>
 */
function themeDemoScreenshotSurfaceRenderData(string $surface, array $mediaUrls, array $actions): array
{
    return match ($surface) {
        'homepage' => [
            'features_heading' => 'Homepage feature system',
            'features_summary' => 'The homepage screenshot is deliberately deep enough to show full theme rhythm.',
            'features' => themeDemoScreenshotFeatures($surface, 9),
            'proof' => [
                'heading' => 'Homepage proof points',
                'summary' => 'Metrics and evidence blocks sit close to the homepage hero.',
                'items' => themeDemoScreenshotProofItems(),
            ],
            'heading' => 'Featured homepage entries',
            'items' => themeDemoScreenshotListingItems($mediaUrls, $surface, 6),
            'cta' => [
                'heading' => 'Screenshot demo homepage conversion band',
                'summary' => 'The homepage screenshot includes a full final conversion section.',
                'actions' => $actions,
            ],
        ],
        'directory' => [
            'heading' => 'Directory result cards',
            'items' => themeDemoScreenshotListingItems($mediaUrls, $surface, 8),
            'cta' => [
                'heading' => 'Filter the directory',
                'summary' => 'Directory screenshots focus on repeated cards, images, and scanning density.',
                'actions' => [$actions[0]],
            ],
        ],
        'detail' => [
            'proof' => [
                'heading' => 'Detail page facts',
                'summary' => 'Detail screenshots need rich supporting context without looking like an index.',
                'items' => array_slice(themeDemoScreenshotProofItems(), 0, 3),
            ],
            'features_heading' => 'Detail page sections',
            'features_summary' => 'Long-form pages check compact content blocks after the hero.',
            'features' => themeDemoScreenshotFeatures($surface, 3),
            'cta' => [
                'heading' => 'Continue from the detail page',
                'summary' => 'A detail page CTA checks action spacing after long copy.',
                'actions' => [$actions[0], $actions[1]],
            ],
        ],
        'contact' => [
            'features_heading' => 'Contact routing options',
            'features_summary' => 'Contact screenshots check forms, routing cards, and support details.',
            'features' => [
                ['title' => 'Project scoping', 'description' => 'Route new builds and content-model planning to the right team.', 'icon' => 'Scope'],
                ['title' => 'Support', 'description' => 'Surface help paths for existing sites without losing contact clarity.', 'icon' => 'Help'],
                ['title' => 'Partnerships', 'description' => 'Keep commercial and agency enquiries visible in the same layout.', 'icon' => 'Partner'],
            ],
            'cta' => [
                'heading' => 'Send an enquiry',
                'summary' => 'This static demo form checks the visual contact layout without submitting data.',
                'actions' => [$actions[0]],
            ],
        ],
        'empty' => [
            'heading' => 'Empty state results',
            'items' => [],
            'cta' => [
                'heading' => 'Reset the empty state',
                'summary' => 'Empty screenshots focus on readable recovery messaging.',
                'actions' => [$actions[0]],
            ],
        ],
        'not-found' => [
            'features_heading' => 'Recovery links',
            'features_summary' => '404 pages need clear navigation choices and calm spacing.',
            'features' => themeDemoScreenshotFeatures($surface, 3),
            'cta' => [
                'heading' => 'Find the right page',
                'summary' => 'A compact recovery CTA checks system layout action styling.',
                'actions' => [$actions[0], $actions[1]],
            ],
        ],
        'maintenance' => [
            'proof' => [
                'heading' => 'Maintenance status',
                'summary' => 'Status screenshots need short operational proof points.',
                'items' => array_slice(themeDemoScreenshotProofItems(), 0, 3),
            ],
            'cta' => [
                'heading' => 'Back online soon',
                'summary' => 'Maintenance screenshots verify recovery messaging and action styling.',
                'actions' => [$actions[0]],
            ],
        ],
        'system' => [
            'features_heading' => 'System page checks',
            'features_summary' => 'System page screenshots make operational support layouts explicit.',
            'features' => themeDemoScreenshotFeatures($surface, 4),
            'cta' => [
                'heading' => 'System page recovery action',
                'summary' => 'System pages need the same readable CTA treatment as marketing pages.',
                'actions' => [$actions[0]],
            ],
        ],
        'cta' => [
            'proof' => [
                'heading' => 'CTA evidence',
                'summary' => 'Conversion-only screenshots check proof blocks and action rhythm.',
                'items' => array_slice(themeDemoScreenshotProofItems(), 0, 2),
            ],
            'cta' => [
                'heading' => 'Screenshot demo CTA',
                'summary' => 'CTA pages should feel purposeful rather than another generic content page.',
                'actions' => $actions,
            ],
        ],
        default => [
            'features' => themeDemoScreenshotFeatures($surface, 4),
            'items' => themeDemoScreenshotListingItems($mediaUrls, $surface, 4),
        ],
    };
}

function themeDemoScreenshotExpectedText(string $surface): string
{
    return match ($surface) {
        'homepage' => 'Screenshot demo homepage',
        'directory' => 'Screenshot demo directory',
        'detail' => 'Screenshot demo detail',
        'contact' => 'Screenshot demo contact',
        'empty' => 'Screenshot demo empty state',
        'not-found' => 'Screenshot demo not found',
        'maintenance' => 'Screenshot demo maintenance',
        'system' => 'Screenshot demo system page',
        'cta' => 'Screenshot demo CTA',
        default => 'Screenshot demo page',
    };
}

function themeDemoScreenshotHeroHeading(string $themeName, string $surface): string
{
    return match ($surface) {
        'homepage' => $themeName . ' homepage screenshot demo',
        'directory' => 'Browse ' . $themeName . ' demo entries',
        'detail' => $themeName . ' long-form detail preview',
        'contact' => 'Start the ' . $themeName . ' conversation',
        'empty' => 'No ' . $themeName . ' results yet',
        'not-found' => $themeName . ' page not found',
        'maintenance' => $themeName . ' maintenance mode',
        'system' => $themeName . ' system page',
        'cta' => 'Convert with the ' . $themeName . ' theme',
        default => $themeName . ' screenshot demo',
    };
}

/**
 * @param  array<string, mixed>  $renderData
 * @return array<int, string>
 */
function themeDemoScreenshotMediaUrls(array $renderData): array
{
    $imageUrls = data_get($renderData, 'image_urls');

    if (is_array($imageUrls)) {
        return collect($imageUrls)
            ->filter(fn (mixed $url): bool => is_string($url) && $url !== '')
            ->values()
            ->all();
    }

    return collect([
        data_get($renderData, 'hero.mediaUrl'),
        data_get($renderData, 'mediaUrl'),
    ])
        ->filter(fn (mixed $url): bool => is_string($url) && $url !== '')
        ->values()
        ->all();
}

/**
 * @return array<int, array{title: string, description: string, icon: string}>
 */
function themeDemoScreenshotFeatures(string $surface, int $count = 6): array
{
    return collect(range(1, $count))
        ->map(fn (int $number): array => [
            'title' => 'Screenshot feature ' . $number,
            'description' => ucfirst($surface) . ' demo copy checks card spacing, wrapping, and section density.',
            'icon' => 'Demo ' . $number,
        ])
        ->all();
}

/**
 * @return array<int, array{metric: string, name: string, summary: string}>
 */
function themeDemoScreenshotProofItems(): array
{
    return [
        ['metric' => '12', 'name' => 'Layouts', 'summary' => 'Page type and layout combinations stay visible.'],
        ['metric' => '35', 'name' => 'Screens', 'summary' => 'Every first-party theme surface gets a PNG.'],
        ['metric' => '100%', 'name' => 'Demo data', 'summary' => 'Screenshots render seeded content instead of fallbacks.'],
        ['metric' => '1x', 'name' => 'Install', 'summary' => 'Each theme demo is installed once per test file.'],
    ];
}

/**
 * @param  array<int, string>  $mediaUrls
 * @return array<int, array<string, string>>
 */
function themeDemoScreenshotListingItems(array $mediaUrls, string $surface, int $count = 4): array
{
    return collect(range(1, $count))
        ->map(function (int $number) use ($mediaUrls, $surface): array {
            $mediaUrl = $mediaUrls[($number - 1) % max(1, count($mediaUrls))] ?? null;

            return array_filter([
                'title' => 'Screenshot listing item ' . $number,
                'summary' => ucfirst($surface) . ' listing copy proves repeated cards render with real fixture data.',
                'url' => '#screenshot-item-' . $number,
                'type' => 'Demo',
                'imageUrl' => $mediaUrl,
                'mediaUrl' => $mediaUrl,
            ], fn (?string $value): bool => $value !== null);
        })
        ->all();
}

/**
 * @param  array<string, mixed>  $renderData
 */
function themeDemoScreenshotPageData(Page $page, array $renderData): ThemePageData
{
    $translation = $page->translations->first();
    $title = is_string($translation?->title) && $translation->title !== '' ? $translation->title : $page->name;
    $navigation = themeDemoScreenshotNavigation($renderData);

    return new ThemePageData(
        title: $title,
        brand: new BrandProfileData,
        sections: themeDemoScreenshotSections($renderData, $title),
        navigation: $navigation,
        footer: themeDemoScreenshotFooter($renderData, $navigation),
    );
}

/**
 * @param  array<string, mixed>  $renderData
 * @return array<int, ThemeSection>
 */
function themeDemoScreenshotSections(array $renderData, string $title): array
{
    $sections = [];
    $hero = data_get($renderData, 'hero');

    if (is_array($hero)) {
        $sections[] = HeroSectionData::from([
            'heading' => data_get($hero, 'heading', $title),
            'eyebrow' => data_get($hero, 'eyebrow'),
            'summary' => data_get($hero, 'summary', data_get($renderData, 'summary')),
            'actions' => data_get($hero, 'actions', data_get($renderData, 'actions', [])),
            'mediaUrl' => data_get($hero, 'mediaUrl', data_get($renderData, 'mediaUrl')),
            'mediaAlt' => data_get($hero, 'mediaAlt'),
        ]);
    }

    $proof = data_get($renderData, 'proof');

    if (is_array($proof) && is_array(data_get($proof, 'items'))) {
        $sections[] = ProofSectionData::from([
            'heading' => data_get($proof, 'heading', 'Proof points'),
            'summary' => data_get($proof, 'summary'),
            'items' => data_get($proof, 'items', []),
        ]);
    }

    $features = data_get($renderData, 'features');

    if (is_array($features) && $features !== []) {
        $sections[] = FeatureSectionData::from([
            'heading' => data_get($renderData, 'features_heading', 'Featured modules'),
            'summary' => data_get($renderData, 'features_summary', data_get($renderData, 'summary')),
            'features' => collect($features)
                ->filter(fn (mixed $feature): bool => is_array($feature))
                ->map(fn (array $feature): array => [
                    'title' => (string) data_get($feature, 'title', data_get($feature, 'name', 'Feature')),
                    'description' => (string) data_get($feature, 'description', data_get($feature, 'summary', '')),
                ])
                ->values()
                ->all(),
        ]);
    }

    $items = data_get($renderData, 'items');

    if (is_array($items)) {
        $sections[] = ContentListingSectionData::from([
            'heading' => data_get($renderData, 'heading', 'Browse entries'),
            'summary' => data_get($renderData, 'summary'),
            'items' => $items,
        ]);
    }

    $cta = data_get($renderData, 'cta');

    if (is_array($cta)) {
        $sections[] = CtaSectionData::from($cta);
    }

    if ($sections === []) {
        $sections[] = HeroSectionData::from([
            'heading' => $title,
            'summary' => data_get($renderData, 'summary'),
            'actions' => data_get($renderData, 'actions', []),
            'mediaUrl' => data_get($renderData, 'mediaUrl'),
        ]);
    }

    return $sections;
}

/**
 * @param  array<string, mixed>  $renderData
 */
function themeDemoScreenshotNavigation(array $renderData): NavigationData
{
    $navigation = data_get($renderData, 'navigation');

    if (is_array($navigation) && array_is_list($navigation)) {
        return new NavigationData(brandName: 'Theme Demo', items: $navigation);
    }

    if (is_array($navigation)) {
        return NavigationData::from([
            'brandName' => data_get($navigation, 'brandName', 'Theme Demo'),
            'items' => data_get($navigation, 'items', []),
            'ctaLabel' => data_get($navigation, 'ctaLabel'),
            'ctaUrl' => data_get($navigation, 'ctaUrl'),
        ]);
    }

    return new NavigationData(
        brandName: 'Theme Demo',
        items: [
            ['label' => 'Home', 'url' => '#home'],
            ['label' => 'Directory', 'url' => '#directory'],
            ['label' => 'Contact', 'url' => '#contact'],
        ],
    );
}

/**
 * @param  array<string, mixed>  $renderData
 */
function themeDemoScreenshotFooter(array $renderData, NavigationData $navigation): FooterData
{
    $footer = data_get($renderData, 'footer');

    if (is_array($footer) && array_is_list($footer)) {
        return new FooterData(brandName: $navigation->brandName, columns: $footer);
    }

    if (is_array($footer)) {
        return FooterData::from([
            'brandName' => data_get($footer, 'brandName', $navigation->brandName),
            'summary' => data_get($footer, 'summary'),
            'columns' => data_get($footer, 'columns', []),
        ]);
    }

    return new FooterData(
        brandName: $navigation->brandName,
        columns: [['heading' => 'Content', 'links' => $navigation->items]],
    );
}

/**
 * @param  array<string, mixed>  $manifest
 * @return array<string, mixed>
 */
function runThemeDemoScreenshotCapture(string $themeKey, array $manifest): array
{
    $manifestPath = storage_path('framework/testing/theme-demo-layout-screenshots/' . $themeKey . '-manifest.json');
    $resultPath = storage_path('framework/testing/theme-demo-layout-screenshots/' . $themeKey . '-result.json');
    $manifest['cssPath'] = buildThemeDemoScreenshotCss($themeKey);

    if (! is_dir(dirname($manifestPath))) {
        mkdir(dirname($manifestPath), 0775, true);
    }

    file_put_contents($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));

    $process = new Process([
        'node',
        themeDemoRepositoryPath('scripts/capture-theme-demo-layout-screenshots.mjs'),
        $manifestPath,
        $resultPath,
    ], themeDemoRepositoryPath(), timeout: 120);
    $process->run();

    if (! $process->isSuccessful()) {
        throw new RuntimeException($process->getErrorOutput() . $process->getOutput());
    }

    return json_decode((string) file_get_contents($resultPath), true, flags: JSON_THROW_ON_ERROR);
}

function buildThemeDemoScreenshotCss(string $themeKey): string
{
    $directory = storage_path('framework/testing/theme-demo-layout-screenshots/css');
    $inputPath = $directory . '/' . $themeKey . '-input.css';
    $outputPath = $directory . '/' . $themeKey . '.css';
    $htmlSource = storage_path('framework/testing/theme-demo-layout-screenshots/html/' . $themeKey . '-*.html');

    if (! is_dir($directory)) {
        mkdir($directory, 0775, true);
    }

    file_put_contents($inputPath, implode("\n", [
        '@import "tailwindcss";',
        ...themeDemoScreenshotTailwindImports($themeKey),
        ...themeDemoScreenshotTailwindSources($themeKey, $htmlSource),
        '@source "' . str_replace('\\', '/', $htmlSource) . '";',
        '@layer base {',
        '    html { background: #ffffff; }',
        '    body { margin: 0; }',
        '}',
        '',
    ]));

    $process = new Process([
        themeDemoRepositoryPath('node_modules/.bin/tailwindcss'),
        '--input',
        $inputPath,
        '--output',
        $outputPath,
        '--minify',
        '--cwd',
        themeDemoRepositoryPath(),
    ], themeDemoRepositoryPath(), timeout: 60);
    $process->run();

    if (! $process->isSuccessful()) {
        throw new RuntimeException($process->getErrorOutput() . $process->getOutput());
    }

    return $outputPath;
}

/**
 * @return array<int, string>
 */
function themeDemoScreenshotTailwindImports(string $themeKey): array
{
    return collect([
        themeDemoRepositoryPath('packages/foundation-theme/resources/css/foundation-theme.css'),
        themeDemoScreenshotThemeCssPath($themeKey),
    ])
        ->filter(fn (?string $path): bool => is_string($path) && is_file($path))
        ->map(fn (string $path): string => '@import "' . str_replace('\\', '/', $path) . '";')
        ->values()
        ->all();
}

/**
 * @return array<int, string>
 */
function themeDemoScreenshotTailwindSources(string $themeKey, string $htmlSource): array
{
    return collect([
        themeDemoRepositoryPath('packages/foundation-theme/resources/views/**/*.blade.php'),
        themeDemoRepositoryPath('packages/theme-' . $themeKey . '/resources/views/**/*.blade.php'),
        $htmlSource,
    ])
        ->filter(fn (string $path): bool => str_contains($path, '*') || file_exists($path))
        ->unique()
        ->map(fn (string $path): string => '@source "' . str_replace('\\', '/', $path) . '";')
        ->values()
        ->all();
}

function themeDemoScreenshotThemeCssPath(string $themeKey): ?string
{
    return match ($themeKey) {
        'agency' => themeDemoRepositoryPath('packages/theme-agency/resources/css/theme-agency.css'),
        'commerce' => themeDemoRepositoryPath('packages/theme-commerce/resources/css/theme-commerce.css'),
        'corporate' => themeDemoRepositoryPath('packages/theme-corporate/resources/css/theme-corporate.css'),
        'healthcare' => themeDemoRepositoryPath('packages/theme-healthcare/resources/css/theme-healthcare.css'),
        'saas' => themeDemoRepositoryPath('packages/theme-saas/resources/css/theme-saas.css'),
        default => null,
    };
}

function cleanThemeDemoScreenshotDirectory(string $themeKey): void
{
    $directory = themeDemoRepositoryPath('tests/Packages/Fixtures/theme-demo-layout-screenshots/' . $themeKey);

    if (! is_dir($directory)) {
        return;
    }

    foreach (glob($directory . '/*.png') ?: [] as $path) {
        unlink($path);
    }
}

function themeDemoScreenshotHtmlPath(string $themeKey, string $surface, string $type, string $layout): string
{
    $path = storage_path('framework/testing/theme-demo-layout-screenshots/html/' . themeDemoScreenshotName($themeKey, $surface, $type, $layout) . '.html');

    if (! is_dir(dirname($path))) {
        mkdir(dirname($path), 0775, true);
    }

    return $path;
}

function themeDemoScreenshotPath(string $themeKey, string $surface, string $type, string $layout): string
{
    $path = themeDemoRepositoryPath('tests/Packages/Fixtures/theme-demo-layout-screenshots/' . $themeKey . '/' . themeDemoScreenshotName($themeKey, $surface, $type, $layout) . '.png');

    if (! is_dir(dirname($path))) {
        mkdir(dirname($path), 0775, true);
    }

    return $path;
}

/**
 * @return array{width: int, height: int}
 */
function themeDemoScreenshotViewport(string $surface): array
{
    if ($surface === 'homepage') {
        return ['width' => 1440, 'height' => 2200];
    }

    return ['width' => 1440, 'height' => 1100];
}

function themeDemoScreenshotName(string $themeKey, string $surface, string $type, string $layout): string
{
    return implode('-', [$themeKey, $surface, 'type-' . $type, 'layout-' . $layout]);
}

function themeDemoRepositoryPath(string $path = ''): string
{
    $root = dirname(__DIR__, 3);

    return $path === '' ? $root : $root . '/' . ltrim($path, '/');
}
