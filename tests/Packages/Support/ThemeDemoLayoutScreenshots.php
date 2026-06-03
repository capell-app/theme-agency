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
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
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

    if (method_exists($provider, 'boot')) {
        app()->call(static fn (): mixed => $provider->boot(resolve(ThemeRegistry::class)));
    }

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

        $page = capell_test_instance($page, Page::class);

        expect($page->type?->key)->toBe($expected['type']);
        expect($page->layout?->key)->toBe($expected['layout']);
        expect($page->pageUrl)->toBeInstanceOf(PageUrl::class);

        $html = themeDemoScreenshotHtml($themeKey, $page, $surface);

        $htmlPath = themeDemoScreenshotHtmlPath($themeKey, $surface, $expected['type'], $expected['layout']);
        file_put_contents($htmlPath, $html);

        assertThemeDemoScreenshotHtmlContainsExpectedSurface($themeKey, $surface, $html);
        assertThemeDemoScreenshotHtmlAvoidsPlaceholderCopy($html);

        $manifest['entries'][] = [
            'surface' => $surface,
            'type' => $expected['type'],
            'layout' => $expected['layout'],
            'htmlPath' => $htmlPath,
            'screenshotPath' => themeDemoScreenshotPath($themeKey, $surface, $expected['type'], $expected['layout']),
            'viewport' => themeDemoScreenshotViewport($surface),
        ];

        if (themeDemoScreenshotNeedsMobileVariant($themeKey, $surface)) {
            $manifest['entries'][] = themeDemoMobileScreenshotEntry(
                $themeKey,
                $surface,
                $expected['type'],
                $expected['layout'],
                $htmlPath,
            );
        }
    }

    foreach (themeDemoExtraScreenshotEntries($themeKey) as $entry) {
        $htmlPath = themeDemoScreenshotHtmlPath($themeKey, $entry['surface'], $entry['type'], $entry['layout']);
        file_put_contents($htmlPath, $entry['html']);

        expect($entry['html'])->toContain($entry['expectedText']);
        assertThemeDemoScreenshotHtmlAvoidsPlaceholderCopy($entry['html']);

        $manifest['entries'][] = [
            'surface' => $entry['surface'],
            'type' => $entry['type'],
            'layout' => $entry['layout'],
            'htmlPath' => $htmlPath,
            'screenshotPath' => themeDemoScreenshotPath($themeKey, $entry['surface'], $entry['type'], $entry['layout']),
            'viewport' => themeDemoScreenshotViewport($entry['surface']),
        ];

        if (themeDemoScreenshotNeedsMobileVariant($themeKey, $entry['surface'])) {
            $manifest['entries'][] = themeDemoMobileScreenshotEntry(
                $themeKey,
                $entry['surface'],
                $entry['type'],
                $entry['layout'],
                $htmlPath,
            );
        }
    }

    if (getenv('ACT') === 'true') {
        test()->markTestSkipped('Theme demo screenshot capture requires native Chromium; local act amd64 emulation crashes Chromium.');
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
            ->and($entry['blank'])->toBeFalse()
            ->and($entry['horizontalOverflow'])->toBeFalse(
                'Screenshot has top-level horizontal overflow: ' . json_encode($entry['overflowingElements'] ?? [], JSON_THROW_ON_ERROR),
            );

        assertThemeDemoScreenshotImageDimensions($entry);
    }
}

/**
 * @return array{surface: string, type: string, layout: string, htmlPath: string, screenshotPath: string, viewport: array{width: int, height: int}}
 */
function themeDemoMobileScreenshotEntry(
    string $themeKey,
    string $surface,
    string $type,
    string $layout,
    string $htmlPath,
): array {
    $mobileSurface = $surface . '-mobile';

    return [
        'surface' => $mobileSurface,
        'type' => $type,
        'layout' => $layout,
        'htmlPath' => $htmlPath,
        'screenshotPath' => themeDemoScreenshotPath($themeKey, $mobileSurface, $type, $layout),
        'viewport' => themeDemoMobileScreenshotViewport(),
    ];
}

function themeDemoScreenshotNeedsMobileVariant(string $themeKey, string $surface): bool
{
    if (! themeDemoScreenshotIsPremiumTheme($themeKey)) {
        return false;
    }

    return $surface === 'homepage' || $surface === $themeKey . '-sections';
}

function themeDemoScreenshotIsPremiumTheme(string $themeKey): bool
{
    return in_array($themeKey, [
        'commerce',
        'education',
        'healthcare',
        'knowledge',
        'local-services',
        'nonprofit',
        'portfolio',
        'saas',
    ], true);
}

/**
 * @param  array{screenshotPath: string, viewport?: array{width?: int, height?: int}}  $entry
 */
function assertThemeDemoScreenshotImageDimensions(array $entry): void
{
    $dimensions = getimagesize($entry['screenshotPath']);

    expect($dimensions)->toBeArray();

    $expectedWidth = (int) data_get($entry, 'viewport.width', 1440);
    $minimumHeight = (int) data_get($entry, 'viewport.height', 1100);
    $maximumHeight = $expectedWidth < 768
        ? max($minimumHeight * 14, 16000)
        : max($minimumHeight * 8, 12000);

    expect($dimensions[0] ?? null)
        ->toBe($expectedWidth)
        ->and($dimensions[1] ?? null)
        ->toBeGreaterThanOrEqual($minimumHeight)
        ->toBeLessThanOrEqual($maximumHeight);
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
    $pageUrl = capell_test_instance($page->pageUrl, PageUrl::class);

    $response = get($pageUrl->full_url);
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
    $exceptionSummary = $response->exceptions
        ->map(fn (Throwable $exception): string => sprintf(
            '%s: %s at %s:%d',
            $exception::class,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
        ))
        ->implode(' | ') ?: 'none';

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
                    title: 'Theme content unavailable',
                    brand: new BrandProfileData,
                    sections: [
                        HeroSectionData::from([
                            'heading' => 'Theme content unavailable',
                            'summary' => 'Public content is unavailable for this request.',
                        ]),
                    ],
                    navigation: new NavigationData(brandName: 'Capell Foundation'),
                    footer: new FooterData(brandName: 'Capell Foundation'),
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

    $site = capell_test_instance($site, Site::class);

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

function assertThemeDemoScreenshotHtmlContainsExpectedSurface(string $themeKey, string $surface, string $html): void
{
    expect($html)->toContain(themeDemoScreenshotExpectedText($surface, $themeKey));
}

function assertThemeDemoScreenshotHtmlAvoidsPlaceholderCopy(string $html): void
{
    $forbiddenPhrases = [
        'Screenshot demo',
        'Screenshot blog',
        'Theme Demo',
        'Demo content is unavailable',
        'Featured demo',
        'demo entries',
        'demo entry',
        'demo copy',
        'Theme visual review surface',
        'Theme system review surface',
        'Primary action',
        'Secondary action',
        'Review conversion section',
        'Theme review unavailable',
        'Review content is unavailable',
        'Full-page review',
        'Support-page review',
        'Premium homepage review',
        'Premium directory review',
        'Premium detail review',
        'Premium contact review',
        'Premium empty-state review',
        'Premium recovery review',
        'Premium maintenance review',
        'Premium system-page review',
        'Premium conversion review',
        'Premium theme review',
        'screenshot review',
        'screenshot content',
        'theme fixture',
        'fixture data',
        'real fixture',
        'review copy',
    ];

    foreach ($forbiddenPhrases as $phrase) {
        expect($html)->not->toContain($phrase);
    }
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
                'heading' => 'Editorial archive',
                'summary' => 'A seeded archive with enough entries to check cards, pagination, filters, and sidebar treatments.',
                'total' => 42,
                'from' => 1,
                'to' => count($articles),
                'searchEnabled' => true,
            ])->render(),
            'expectedText' => 'Editorial archive',
        ];
    }

    if (view()->exists($viewNamespace . '::blog.article')) {
        $entries[] = [
            'surface' => 'blog-article',
            'type' => 'blog',
            'layout' => 'article',
            'html' => view($viewNamespace . '::blog.article', [
                'blogAvailable' => true,
                'title' => 'Long-form article',
                'summary' => 'A seeded article with long-form copy, navigation, and suggested reading.',
                'body' => 'This article body is intentionally plain text for article layout checks. It checks line length, typography, spacing, and article container contrast without requiring the Blog package.',
                'archiveUrl' => '#blog',
                'previousArticle' => $articles[0],
                'nextArticle' => $articles[1],
                'suggestions' => array_slice($articles, 2, 3),
            ])->render(),
            'expectedText' => 'Long-form article',
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
        brandName: themeDemoScreenshotBrandName('commerce'),
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
                    'items' => themeDemoScreenshotListingItems([], 'commerce-sections', 6, 'commerce'),
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
                    'features' => collect(themeDemoScreenshotFeatures('commerce-sections', 8, 'commerce'))
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
        title: 'Commerce merchandising section suite',
        brand: new BrandProfileData,
        sections: [
            HeroSectionData::from([
                'heading' => 'Commerce merchandising section suite',
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
                'items' => themeDemoScreenshotProofItems('commerce'),
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
        'expectedText' => 'Commerce merchandising section suite',
    ];
}

/**
 * @return array{surface: string, type: string, layout: string, html: string, expectedText: string}
 */
function themeDemoHealthcareSectionsScreenshotEntry(): array
{
    $navigation = new NavigationData(
        brandName: themeDemoScreenshotBrandName('healthcare'),
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
        title: 'Healthcare care pathway section suite',
        brand: new BrandProfileData,
        sections: [
            HeroSectionData::from([
                'heading' => 'Healthcare care pathway section suite',
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
                'features' => themeDemoScreenshotFeatures('healthcare-sections', 8, 'healthcare'),
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
                    ['date' => 'Fri 11:00', 'title' => 'Virtual medication check-in', 'summary' => 'Remote support for ongoing care.', 'url' => '#event-3'],
                ],
            ]),
            themeDemoHealthcareGenericSection('comparison', [
                'heading' => 'Compare care pathways',
                'summary' => 'Comparison sections should support patient choice, not plain feature grids.',
                'items' => [
                    ['title' => 'Rapid access', 'summary' => 'Same-week appointment with a clear referral path.'],
                    ['title' => 'Managed care', 'summary' => 'Ongoing plan with diagnostics, assessment, and follow-up.'],
                    ['title' => 'Remote support', 'summary' => 'Digital-first check-ins for lower-risk follow-up needs.'],
                    ['title' => 'Specialist route', 'summary' => 'Consultant-led pathway for complex conditions.'],
                ],
            ]),
            ProofSectionData::from([
                'heading' => 'Clinical trust indicators',
                'summary' => 'Proof should feel patient-safe and evidence-led.',
                'items' => themeDemoScreenshotProofItems('healthcare'),
            ]),
            themeDemoHealthcareGenericSection('blog-teaser', [
                'heading' => 'Patient resource cards',
                'summary' => 'Resource cards should read like care guidance without leaking optional package state.',
                'items' => [
                    ['type' => 'Guide', 'title' => 'Preparing for a first consultation', 'summary' => 'What to bring and what to expect.', 'url' => '#resource-1'],
                    ['type' => 'Checklist', 'title' => 'Choosing the right service', 'summary' => 'Match symptoms and goals to care routes.', 'url' => '#resource-2'],
                    ['type' => 'Advice', 'title' => 'Aftercare questions', 'summary' => 'Follow-up prompts for recovery and next steps.', 'url' => '#resource-3'],
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
        'expectedText' => 'Healthcare care pathway section suite',
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
        brandName: themeDemoScreenshotBrandName('saas'),
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
        title: 'SaaS product workflow section suite',
        brand: new BrandProfileData,
        sections: [
            HeroSectionData::from([
                'heading' => 'SaaS product workflow section suite',
                'summary' => 'SaaS-specific sections check growth ledgers, resource pipelines, comparison, calculator, and conversion command surfaces.',
                'actions' => $actions,
            ]),
            ProofSectionData::from([
                'heading' => 'Growth proof ledger',
                'summary' => 'Proof should combine product telemetry, outcomes, and trust signals.',
                'items' => themeDemoScreenshotProofItems('saas'),
            ]),
            FeatureSectionData::from([
                'heading' => 'Product workflow cards',
                'summary' => 'Feature cards should look like product capabilities rather than service cards.',
                'features' => themeDemoScreenshotFeatures('saas-sections', 6, 'saas'),
            ]),
            ContentListingSectionData::from([
                'heading' => 'Resource pipeline cards',
                'summary' => 'Listing cards should support resource scanning with product-led hierarchy.',
                'items' => themeDemoScreenshotListingItems([], 'saas-sections', 6, 'saas'),
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
        'expectedText' => 'SaaS product workflow section suite',
    ];
}

/**
 * @return array{surface: string, type: string, layout: string, html: string, expectedText: string}
 */
function themeDemoPortfolioSectionsScreenshotEntry(): array
{
    $navigation = new NavigationData(
        brandName: themeDemoScreenshotBrandName('portfolio'),
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
        title: 'Portfolio case study section suite',
        brand: new BrandProfileData,
        sections: [
            HeroSectionData::from([
                'heading' => 'Portfolio case study section suite',
                'summary' => 'Portfolio-specific sections check editorial work cards, evidence ledgers, studio capabilities, and conversion surfaces.',
                'actions' => $actions,
            ]),
            themeDemoPortfolioGenericSection('work-grid', 'Selected work board', 6),
            themeDemoPortfolioGenericSection('case-studies', 'Case-study carousel', 6),
            themeDemoPortfolioGenericSection('services', 'Studio services', 3),
            themeDemoPortfolioGenericSection('testimonials', 'Client outcome notes'),
            themeDemoPortfolioGenericSection('speaking-media-kit', 'Media kit and speaking package'),
            themeDemoPortfolioGenericSection('newsletter', 'Audience and newsletter path'),
            ProofSectionData::from([
                'heading' => 'Evidence-led portfolio proof',
                'summary' => 'Proof widgets should feel like measurable creator outcomes instead of generic metric cards.',
                'items' => themeDemoScreenshotProofItems('portfolio'),
            ]),
            FeatureSectionData::from([
                'heading' => 'Studio capability cards',
                'summary' => 'Capabilities should feel like a consultant or creator studio system.',
                'features' => themeDemoScreenshotFeatures('portfolio-sections', 6, 'portfolio'),
            ]),
            ContentListingSectionData::from([
                'heading' => 'Selected work index',
                'summary' => 'Listing cards should carry image-first case-study hierarchy and useful scanning cues.',
                'items' => themeDemoScreenshotListingItems([], 'portfolio-sections', 6, 'portfolio'),
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
        'expectedText' => 'Portfolio case study section suite',
    ];
}

function themeDemoPortfolioGenericSection(string $key, string $heading, int $itemCount = 0): ThemeSection
{
    return new readonly class($key, $heading, $itemCount) implements ThemeSection
    {
        public function __construct(
            private string $sectionKey,
            public string $heading,
            private int $itemCount,
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
                'section' => (object) [
                    'heading' => $this->heading,
                    'items' => $this->itemCount > 0
                        ? themeDemoScreenshotListingItems([], 'portfolio-' . $this->sectionKey, $this->itemCount, 'portfolio')
                        : [],
                ],
                'heading' => $this->heading,
            ];
        }
    };
}

/**
 * @return array{surface: string, type: string, layout: string, html: string, expectedText: string}
 */
function themeDemoEducationSectionsScreenshotEntry(): array
{
    $navigation = new NavigationData(
        brandName: themeDemoScreenshotBrandName('education'),
        items: [
            ['label' => 'Courses', 'url' => '#courses'],
            ['label' => 'Events', 'url' => '#events'],
            ['label' => 'Apply', 'url' => '#apply'],
        ],
        ctaLabel: 'Apply',
        ctaUrl: '#apply',
    );

    $page = new ThemePageData(
        title: 'Education course pathway section suite',
        brand: new BrandProfileData,
        sections: [
            HeroSectionData::from([
                'heading' => 'Education course pathway section suite',
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
        'expectedText' => 'Education course pathway section suite',
    ];
}

function themeDemoEducationGenericSection(string $key, string $heading): ThemeSection
{
    return new readonly class($key, $heading) implements ThemeSection
    {
        public function __construct(
            private string $sectionKey,
            public string $heading,
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
        brandName: themeDemoScreenshotBrandName('nonprofit'),
        items: [
            ['label' => 'Impact', 'url' => '#impact'],
            ['label' => 'Campaigns', 'url' => '#campaigns'],
            ['label' => 'Support', 'url' => '#support'],
        ],
        ctaLabel: 'Donate',
        ctaUrl: '#donate',
    );

    $page = new ThemePageData(
        title: 'Nonprofit impact section suite',
        brand: new BrandProfileData,
        sections: [
            HeroSectionData::from([
                'heading' => 'Nonprofit impact section suite',
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
        'expectedText' => 'Nonprofit impact section suite',
    ];
}

function themeDemoNonprofitGenericSection(string $key, string $heading): ThemeSection
{
    return new readonly class($key, $heading) implements ThemeSection
    {
        public function __construct(
            private string $sectionKey,
            public string $heading,
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
        brandName: themeDemoScreenshotBrandName('local-services'),
        items: [
            ['label' => 'Services', 'url' => '#services'],
            ['label' => 'Areas', 'url' => '#areas'],
            ['label' => 'Quote', 'url' => '#quote'],
        ],
        ctaLabel: 'Request quote',
        ctaUrl: '#quote',
    );

    $page = new ThemePageData(
        title: 'Local service booking section suite',
        brand: new BrandProfileData,
        sections: [
            HeroSectionData::from([
                'heading' => 'Local service booking section suite',
                'summary' => 'Local-services-specific sections check dispatch, service routes, area coverage, quote intake, resources, and contact routing.',
                'actions' => [['label' => 'Request quote', 'url' => '#quote', 'style' => 'primary']],
            ]),
            FeatureSectionData::from([
                'heading' => 'Dispatch-ready service paths',
                'summary' => 'Feature cards should feel like local job routes and quote workflows.',
                'features' => themeDemoScreenshotFeatures('local-service-sections', 6, 'local-services'),
            ]),
            ProofSectionData::from([
                'heading' => 'Local proof board',
                'summary' => 'Proof cards should read as service performance signals.',
                'items' => themeDemoScreenshotProofItems('local-services'),
            ]),
            ContentListingSectionData::from([
                'heading' => 'Service route cards',
                'summary' => 'Listing cards should show service, area, and availability cues.',
                'items' => themeDemoScreenshotListingItems([], 'local-service-sections', 4, 'local-services'),
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
        'expectedText' => 'Local service booking section suite',
    ];
}

function themeDemoLocalServicesGenericSection(string $key, string $heading): ThemeSection
{
    return new readonly class($key, $heading) implements ThemeSection
    {
        public function __construct(
            private string $sectionKey,
            public string $heading,
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
        brandName: themeDemoScreenshotBrandName('knowledge'),
        items: [
            ['label' => 'Topics', 'url' => '#topics'],
            ['label' => 'Library', 'url' => '#library'],
            ['label' => 'Authors', 'url' => '#authors'],
        ],
        ctaLabel: 'Browse guides',
        ctaUrl: '#library',
    );

    $page = new ThemePageData(
        title: 'Knowledge archive section suite',
        brand: new BrandProfileData,
        sections: [
            HeroSectionData::from([
                'heading' => 'Knowledge archive section suite',
                'summary' => 'Knowledge-specific sections check research pathways, archive rows, topic hubs, search, newsletter, and editorial team presentation.',
                'actions' => [['label' => 'Browse guides', 'url' => '#library', 'style' => 'primary']],
            ]),
            FeatureSectionData::from([
                'heading' => 'Research pathway cards',
                'summary' => 'Feature cards should look like curated library entries and not generic marketing widgets.',
                'features' => themeDemoScreenshotFeatures('knowledge-sections', 6, 'knowledge'),
            ]),
            ProofSectionData::from([
                'heading' => 'Library evidence board',
                'summary' => 'Proof cards should read as content depth and discovery signals.',
                'items' => themeDemoScreenshotProofItems('knowledge'),
            ]),
            ContentListingSectionData::from([
                'heading' => 'Archive result rows',
                'summary' => 'Listings should support scanning, saved-state cues, and resource metadata.',
                'items' => themeDemoScreenshotListingItems([], 'knowledge-sections', 4, 'knowledge'),
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
        'expectedText' => 'Knowledge archive section suite',
    ];
}

function themeDemoKnowledgeGenericSection(string $key, string $heading): ThemeSection
{
    return new readonly class($key, $heading) implements ThemeSection
    {
        public function __construct(
            private string $sectionKey,
            public string $heading,
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
        brandName: themeDemoScreenshotBrandName('corporate'),
        items: [
            ['label' => 'Governance', 'url' => '#governance'],
            ['label' => 'Reports', 'url' => '#reports'],
            ['label' => 'Contact', 'url' => '#contact'],
        ],
        ctaLabel: 'Contact',
        ctaUrl: '#contact',
    );

    $page = new ThemePageData(
        title: 'Corporate boardroom section suite',
        brand: new BrandProfileData,
        sections: [
            HeroSectionData::from([
                'heading' => 'Corporate boardroom section suite',
                'summary' => 'Corporate-specific sections check governance cards, assurance metrics, board-ready listing rows, and formal conversion surfaces.',
                'actions' => [['label' => 'View reports', 'url' => '#reports', 'style' => 'primary']],
            ]),
            FeatureSectionData::from([
                'heading' => 'Governance operating model',
                'summary' => 'Feature cards should read as policy, risk, delivery, and reporting systems.',
                'features' => themeDemoScreenshotFeatures('corporate-sections', 6, 'corporate'),
            ]),
            ProofSectionData::from([
                'heading' => 'Assurance evidence',
                'summary' => 'Proof cards should present sober board-grade metrics and outcomes.',
                'items' => themeDemoScreenshotProofItems('corporate'),
            ]),
            ContentListingSectionData::from([
                'heading' => 'Board register rows',
                'summary' => 'Listing cards should avoid cramped editorial cards and scan like formal register entries.',
                'items' => themeDemoScreenshotListingItems([], 'corporate-sections', 5, 'corporate'),
            ]),
            CtaSectionData::from([
                'heading' => 'Move the next decision forward',
                'summary' => 'Corporate sections should support briefing, approval, and accountable follow-through.',
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
        'expectedText' => 'Corporate boardroom section suite',
    ];
}

/**
 * @return array{surface: string, type: string, layout: string, html: string, expectedText: string}
 */
function themeDemoAgencySectionsScreenshotEntry(): array
{
    $navigation = new NavigationData(
        brandName: themeDemoScreenshotBrandName('agency'),
        items: [
            ['label' => 'Work', 'url' => '#work'],
            ['label' => 'Studio', 'url' => '#studio'],
            ['label' => 'Launch', 'url' => '#launch'],
        ],
        ctaLabel: 'Start',
        ctaUrl: '#launch',
    );

    $page = new ThemePageData(
        title: 'Agency campaign section suite',
        brand: new BrandProfileData,
        sections: [
            HeroSectionData::from([
                'heading' => 'Agency campaign section suite',
                'summary' => 'Agency-specific sections check campaign boards, work-wall cards, proof reels, and launch-room conversion surfaces.',
                'actions' => [['label' => 'View work', 'url' => '#work', 'style' => 'primary']],
            ]),
            FeatureSectionData::from([
                'heading' => 'Campaign system cards',
                'summary' => 'Feature cards should feel like a studio production system, not generic service cards.',
                'features' => themeDemoScreenshotFeatures('agency-sections', 6, 'agency'),
            ]),
            ProofSectionData::from([
                'heading' => 'Studio proof wall',
                'summary' => 'Proof should combine visual confidence, campaign metrics, and clear outcomes.',
                'items' => themeDemoScreenshotProofItems('agency'),
            ]),
            ContentListingSectionData::from([
                'heading' => 'Work wall cards',
                'summary' => 'Listing cards should use real media when present and high-quality studio boards otherwise.',
                'items' => themeDemoScreenshotListingItems([], 'agency-sections', 6, 'agency'),
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
        'expectedText' => 'Agency campaign section suite',
    ];
}

/**
 * @return array{surface: string, type: string, layout: string, html: string, expectedText: string}
 */
function themeDemoHealthcareContactScreenshotEntry(): array
{
    $section = new class(heading: 'Healthcare contact route check', summary: 'Dedicated healthcare contact cards verify the theme-specific contact section.', locations: [['label' => 'Clinic', 'title' => 'Central clinic', 'summary' => 'Appointments, referrals, and care plans routed from one place.', 'phone' => '+44 20 0000 1000'], ['label' => 'Urgent', 'title' => 'Rapid access', 'summary' => 'Fast-track support for priority care questions.', 'phone' => '+44 20 0000 2000'], ['label' => 'Virtual', 'title' => 'Remote care', 'summary' => 'Digital-first consultations and follow-up support.', 'phone' => '+44 20 0000 3000'], ['label' => 'Partners', 'title' => 'Referral team', 'summary' => 'Partnership and clinical referral enquiries.', 'phone' => '+44 20 0000 4000']]) implements ThemeSection
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
        title: 'Healthcare contact route check',
        brand: new BrandProfileData,
        sections: [
            HeroSectionData::from([
                'heading' => 'Healthcare contact route check',
                'summary' => 'A healthcare-specific contact route uses the real contact section renderer.',
            ]),
            $section,
            CtaSectionData::from([
                'heading' => 'Book the right route',
                'summary' => 'This verifies the contact section and the healthcare CTA together.',
                'actions' => [['label' => 'Start contact', 'url' => '#contact', 'style' => 'primary']],
            ]),
        ],
        navigation: new NavigationData(brandName: themeDemoScreenshotBrandName('healthcare')),
        footer: new FooterData(brandName: themeDemoScreenshotBrandName('healthcare')),
    );

    return [
        'surface' => 'healthcare-contact-section',
        'type' => 'theme',
        'layout' => 'contact',
        'html' => resolve(ThemeRegistry::class)->renderer('healthcare')->render($page),
        'expectedText' => 'Healthcare contact route check',
    ];
}

/**
 * @return array<int, array{surface: string, type: string, layout: string, html: string, expectedText: string}>
 */
function themeDemoGenericReviewScreenshotEntries(string $themeKey): array
{
    $copy = themeDemoScreenshotThemeCopy($themeKey);
    $visualTitle = 'Explore ' . $copy['plural'];
    $systemTitle = ucfirst((string) $copy['singular']) . ' support route';

    return [
        themeDemoGenericReviewScreenshotEntry(
            themeKey: $themeKey,
            surface: 'visual-review',
            layout: 'full',
            expectedText: $visualTitle,
            title: $visualTitle,
            summary: 'A full-page composition checks hero, proof, ' . $copy['feature'] . ' cards, listings, CTA rhythm, navigation, and footer treatment.',
        ),
        themeDemoGenericReviewScreenshotEntry(
            themeKey: $themeKey,
            surface: 'system-review',
            layout: 'system',
            expectedText: $systemTitle,
            title: $systemTitle,
            summary: 'A compact support surface checks recovery copy, operational actions, and smaller page structure for this buyer journey.',
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
    $copy = themeDemoScreenshotThemeCopy($themeKey);
    $actions = [
        ['label' => 'Open ' . $copy['singular'], 'url' => '#primary', 'style' => 'primary'],
        ['label' => 'Compare ' . $copy['plural'], 'url' => '#secondary', 'style' => 'secondary'],
    ];
    $navigation = new NavigationData(
        brandName: themeDemoScreenshotBrandName($themeKey),
        items: [
            ['label' => 'Home', 'url' => '#home'],
            ['label' => 'Proof', 'url' => '#proof'],
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
                'heading' => $copy['proofHeading'],
                'summary' => $copy['proofSummary'],
                'items' => themeDemoScreenshotProofItems($themeKey),
            ]),
            FeatureSectionData::from([
                'heading' => $copy['featuresHeading'],
                'summary' => $copy['featuresSummary'],
                'features' => themeDemoScreenshotFeatures($surface, 4, $themeKey),
            ]),
            ContentListingSectionData::from([
                'heading' => $copy['listingHeading'],
                'summary' => $copy['listingSummary'],
                'items' => themeDemoScreenshotListingItems([], $surface, 4, $themeKey),
            ]),
            CtaSectionData::from([
                'heading' => 'Move visitors through ' . $copy['plural'],
                'summary' => 'CTA treatment should feel specific to this workflow while staying readable in every theme.',
                'actions' => $actions,
            ]),
        ],
        navigation: $navigation,
        footer: new FooterData(
            brandName: $navigation->brandName,
            columns: [['heading' => 'Explore', 'links' => $navigation->items]],
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
            'title' => 'Editorial article ' . $number,
            'summary' => 'Blog card copy for editorial layout across index, article navigation, and suggested reading.',
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
    $themeName = themeDemoScreenshotThemeName($themeKey);
    $copy = themeDemoScreenshotThemeCopy($themeKey);
    $renderData = [
        'summary' => themeDemoScreenshotExpectedText('maintenance', $themeKey) . ' with status copy, recovery actions, and supporting content.',
        'hero' => [
            'heading' => $themeName . ' maintenance mode',
            'summary' => 'Maintenance mode with status copy, recovery actions, and supporting content.',
            'actions' => [
                ['label' => 'Check status', 'url' => '#status', 'style' => 'primary'],
                ['label' => 'Contact support', 'url' => '#contact', 'style' => 'secondary'],
            ],
        ],
        'features_heading' => $copy['maintenanceHeading'],
        'features_summary' => $copy['maintenanceSummary'],
        'features' => themeDemoScreenshotFeatures('maintenance', 3, $themeKey),
        'proof' => [
            'heading' => $copy['detailProofHeading'],
            'items' => themeDemoScreenshotProofItems($themeKey),
        ],
        'cta' => [
            'heading' => $copy['maintenanceCtaHeading'],
            'summary' => $copy['maintenanceCtaSummary'],
            'actions' => [
                ['label' => 'Return home', 'url' => '#home', 'style' => 'primary'],
            ],
        ],
    ];

    /** @var Page $page */
    $page = resolve(PageCreator::class)->createPage([
        'name' => $themeName . ' Maintenance Status',
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
                    'title' => $themeName . ' Maintenance Status',
                    'content' => '<h2>Maintenance preview</h2><p>Maintenance mode status content.</p>',
                    'summary' => $renderData['summary'],
                    'meta' => [
                        'description' => $renderData['summary'],
                        'hero' => $renderData['hero']['summary'],
                        'hero_title' => $renderData['hero']['heading'],
                        'label' => $themeName . ' Maintenance Status',
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
    $themeName = themeDemoScreenshotThemeName($themeKey);
    $copy = themeDemoScreenshotThemeCopy($themeKey);
    $renderData = [
        'summary' => themeDemoScreenshotExpectedText('system', $themeKey) . ' with operational copy, recovery actions, and support context.',
        'hero' => [
            'heading' => $themeName . ' system page',
            'summary' => 'System page with operational copy, recovery actions, and support context.',
            'actions' => [
                ['label' => 'Open support', 'url' => '#support', 'style' => 'primary'],
                ['label' => 'View status', 'url' => '#status', 'style' => 'secondary'],
            ],
        ],
        'features_heading' => $copy['systemHeading'],
        'features_summary' => $copy['systemSummary'],
        'features' => themeDemoScreenshotFeatures('system', 3, $themeKey),
        'cta' => [
            'heading' => $copy['systemCtaHeading'],
            'summary' => $copy['systemCtaSummary'],
            'actions' => [
                ['label' => 'Continue', 'url' => '#continue', 'style' => 'primary'],
            ],
        ],
    ];

    /** @var Page $page */
    $page = resolve(PageCreator::class)->createPage([
        'name' => $themeName . ' System Support',
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
                    'title' => $themeName . ' System Support',
                    'content' => '<h2>System page preview</h2><p>System page support content.</p>',
                    'summary' => $renderData['summary'],
                    'meta' => [
                        'description' => $renderData['summary'],
                        'hero' => $renderData['hero']['summary'],
                        'hero_title' => $renderData['hero']['heading'],
                        'label' => $themeName . ' System Support',
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
    $themeName = themeDemoScreenshotThemeName($themeKey);
    $expectedText = themeDemoScreenshotExpectedText($surface, $themeKey);
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
        'navigation' => [
            'brandName' => themeDemoScreenshotBrandName($themeKey),
            'items' => [
                ['label' => 'Work', 'url' => '#work'],
                ['label' => 'Proof', 'url' => '#proof'],
                ['label' => 'Contact', 'url' => '#contact'],
            ],
            'ctaLabel' => 'Enquire',
            'ctaUrl' => '#contact',
        ],
        'footer' => [
            'brandName' => themeDemoScreenshotBrandName($themeKey),
            'summary' => $expectedText . ' keeps this buyer journey close to a real public site.',
            'columns' => [
                [
                    'heading' => 'Explore',
                    'links' => [
                        ['label' => 'Work', 'url' => '#work'],
                        ['label' => 'Proof', 'url' => '#proof'],
                        ['label' => 'Contact', 'url' => '#contact'],
                    ],
                ],
            ],
        ],
    ]);

    $base['hero'] = array_replace_recursive(
        is_array(data_get($renderData, 'hero')) ? data_get($renderData, 'hero') : [],
        [
            'heading' => themeDemoScreenshotHeroHeading($themeKey, $themeName, $surface),
            'summary' => $expectedText . ' with richer seeded content, media, actions, and supporting sections.',
            'actions' => $actions,
            'mediaUrl' => is_string($primaryMedia) ? $primaryMedia : null,
            'mediaAlt' => $themeName . ' showcase media',
        ],
    );

    return array_replace_recursive($base, themeDemoScreenshotSurfaceRenderData($themeKey, $surface, $mediaUrls, $actions));
}

/**
 * @param  array<int, string>  $mediaUrls
 * @param  array<int, array<string, string>>  $actions
 * @return array<string, mixed>
 */
function themeDemoScreenshotSurfaceRenderData(string $themeKey, string $surface, array $mediaUrls, array $actions): array
{
    $copy = themeDemoScreenshotThemeCopy($themeKey);

    return match ($surface) {
        'homepage' => [
            'features_heading' => $copy['featuresHeading'],
            'features_summary' => $copy['featuresSummary'],
            'features' => themeDemoScreenshotFeatures($surface, 9, $themeKey),
            'proof' => [
                'heading' => $copy['proofHeading'],
                'summary' => $copy['proofSummary'],
                'items' => themeDemoScreenshotProofItems($themeKey),
            ],
            'heading' => $copy['listingHeading'],
            'items' => themeDemoScreenshotListingItems($mediaUrls, $surface, 6, $themeKey),
            'cta' => [
                'heading' => $copy['ctaHeading'],
                'summary' => $copy['ctaSummary'],
                'actions' => $actions,
            ],
        ],
        'directory' => [
            'heading' => $copy['directoryHeading'],
            'items' => themeDemoScreenshotListingItems($mediaUrls, $surface, 8, $themeKey),
            'cta' => [
                'heading' => $copy['directoryCtaHeading'],
                'summary' => $copy['directorySummary'],
                'actions' => [$actions[0]],
            ],
        ],
        'detail' => [
            'proof' => [
                'heading' => $copy['detailProofHeading'],
                'summary' => $copy['detailProofSummary'],
                'items' => array_slice(themeDemoScreenshotProofItems($themeKey), 0, 3),
            ],
            'features_heading' => $copy['detailFeaturesHeading'],
            'features_summary' => $copy['detailFeaturesSummary'],
            'features' => themeDemoScreenshotFeatures($surface, 3, $themeKey),
            'cta' => [
                'heading' => $copy['detailCtaHeading'],
                'summary' => $copy['detailCtaSummary'],
                'actions' => [$actions[0], $actions[1]],
            ],
        ],
        'contact' => [
            'features_heading' => $copy['contactHeading'],
            'features_summary' => $copy['contactSummary'],
            'features' => $copy['contactRoutes'],
            'cta' => [
                'heading' => $copy['contactCtaHeading'],
                'summary' => $copy['contactCtaSummary'],
                'actions' => [$actions[0]],
            ],
        ],
        'empty' => [
            'heading' => $copy['emptyHeading'],
            'items' => [],
            'cta' => [
                'heading' => $copy['emptyCtaHeading'],
                'summary' => $copy['emptySummary'],
                'actions' => [$actions[0]],
            ],
        ],
        'not-found' => [
            'features_heading' => $copy['recoveryHeading'],
            'features_summary' => $copy['recoverySummary'],
            'features' => themeDemoScreenshotFeatures($surface, 3, $themeKey),
            'cta' => [
                'heading' => $copy['recoveryCtaHeading'],
                'summary' => $copy['recoveryCtaSummary'],
                'actions' => [$actions[0], $actions[1]],
            ],
        ],
        'maintenance' => [
            'proof' => [
                'heading' => $copy['maintenanceHeading'],
                'summary' => $copy['maintenanceSummary'],
                'items' => array_slice(themeDemoScreenshotProofItems($themeKey), 0, 3),
            ],
            'cta' => [
                'heading' => $copy['maintenanceCtaHeading'],
                'summary' => $copy['maintenanceCtaSummary'],
                'actions' => [$actions[0]],
            ],
        ],
        'system' => [
            'features_heading' => $copy['systemHeading'],
            'features_summary' => $copy['systemSummary'],
            'features' => themeDemoScreenshotFeatures($surface, 4, $themeKey),
            'cta' => [
                'heading' => $copy['systemCtaHeading'],
                'summary' => $copy['systemCtaSummary'],
                'actions' => [$actions[0]],
            ],
        ],
        'cta' => [
            'proof' => [
                'heading' => $copy['ctaProofHeading'],
                'summary' => $copy['ctaProofSummary'],
                'items' => array_slice(themeDemoScreenshotProofItems($themeKey), 0, 2),
            ],
            'cta' => [
                'heading' => $copy['ctaHeading'],
                'summary' => $copy['ctaSummary'],
                'actions' => $actions,
            ],
        ],
        default => [
            'features' => themeDemoScreenshotFeatures($surface, 4, $themeKey),
            'items' => themeDemoScreenshotListingItems($mediaUrls, $surface, 4, $themeKey),
        ],
    };
}

function themeDemoScreenshotExpectedText(string $surface, ?string $themeKey = null): string
{
    if ($themeKey !== null) {
        $copy = themeDemoScreenshotThemeCopy($themeKey);
        $themeName = themeDemoScreenshotThemeName($themeKey);

        return match ($surface) {
            'homepage' => 'Featured ' . $copy['plural'],
            'directory' => 'Browse ' . $copy['plural'],
            'detail' => ucfirst((string) $copy['singular']) . ' detail preview',
            'contact' => 'Start the ' . $copy['singular'] . ' conversation',
            'empty' => 'No ' . $copy['plural'] . ' yet',
            'not-found' => $themeName . ' page not found',
            'maintenance' => $themeName . ' maintenance mode',
            'system' => $themeName . ' system page',
            'cta' => 'Convert with ' . $copy['plural'],
            default => 'Explore ' . $copy['plural'],
        };
    }

    return match ($surface) {
        'homepage' => 'Featured foundation entries',
        'directory' => 'Browse foundation entries',
        'detail' => 'Foundation entry detail preview',
        'contact' => 'Start the foundation entry conversation',
        'empty' => 'No foundation entries yet',
        'not-found' => 'Capell Foundation page not found',
        'maintenance' => 'Capell Foundation maintenance mode',
        'system' => 'Capell Foundation system page',
        'cta' => 'Convert with foundation entries',
        default => 'Explore foundation entries',
    };
}

function themeDemoScreenshotThemeName(string $themeKey): string
{
    return ucfirst(str_replace('-', ' ', $themeKey));
}

function themeDemoScreenshotBrandName(string $themeKey): string
{
    return match ($themeKey) {
        'agency' => 'Northstar Studio',
        'commerce' => 'Harbour Goods',
        'corporate' => 'Alder Group',
        'education' => 'Pathway School',
        'healthcare' => 'Cedar Clinic',
        'knowledge' => 'Archive House',
        'local-services' => 'Ready Local',
        'nonprofit' => 'Common Good',
        'portfolio' => 'Atelier North',
        'saas' => 'SignalOps',
        default => 'Capell Foundation',
    };
}

function themeDemoScreenshotSurfaceLabel(string $surface): string
{
    return match ($surface) {
        'homepage' => 'Homepage composition',
        'directory' => 'Directory layout',
        'detail' => 'Detail layout',
        'contact' => 'Contact route',
        'empty' => 'Empty-state recovery',
        'not-found' => 'Recovery route',
        'maintenance' => 'Maintenance route',
        'system' => 'System page',
        'cta' => 'Conversion route',
        'visual-review' => 'Full-page composition',
        'system-review' => 'Support-page route',
        'commerce-sections' => 'Commerce section suite',
        'healthcare-sections' => 'Healthcare section suite',
        'saas-sections' => 'SaaS section suite',
        'portfolio-sections' => 'Portfolio section suite',
        'education-sections' => 'Education section suite',
        'nonprofit-sections' => 'Nonprofit section suite',
        'local-service-sections' => 'Local service section suite',
        'knowledge-sections' => 'Knowledge section suite',
        'corporate-sections' => 'Corporate section suite',
        'agency-sections' => 'Agency section suite',
        default => ucfirst(str_replace('-', ' ', $surface)),
    };
}

function themeDemoScreenshotHeroHeading(string $themeKey, string $themeName, string $surface): string
{
    $copy = themeDemoScreenshotThemeCopy($themeKey);

    return match ($surface) {
        'homepage' => $copy['homepageHeroHeading'],
        'directory' => $copy['directoryHeroHeading'],
        'detail' => $copy['detailHeroHeading'],
        'contact' => $copy['contactHeroHeading'],
        'empty' => $copy['emptyHeroHeading'],
        'not-found' => $themeName . ' page not found',
        'maintenance' => $themeName . ' maintenance mode',
        'system' => $themeName . ' system page',
        'cta' => $copy['ctaHeroHeading'],
        default => $themeName . ' premium showcase',
    };
}

/**
 * @return array<string, mixed>
 */
function themeDemoScreenshotThemeCopy(string $themeKey): array
{
    $copy = [
        'agency' => [
            'singular' => 'campaign room',
            'plural' => 'campaign rooms',
            'feature' => 'creative route',
            'proof' => [
                ['metric' => '3', 'name' => 'Launch tracks', 'summary' => 'Campaign, content, and conversion routes stay visible.'],
                ['metric' => '8', 'name' => 'Asset drops', 'summary' => 'Creative assets stay grouped for quick sign-off.'],
                ['metric' => '24h', 'name' => 'Launch room', 'summary' => 'Short-cycle work has an obvious next step.'],
                ['metric' => 'Live', 'name' => 'Campaign state', 'summary' => 'Visitors can see what is ready now.'],
            ],
            'contactRoutes' => [
                ['title' => 'Campaign brief', 'description' => 'Route new launch and content briefs into a focused scoping path.', 'icon' => 'Brief'],
                ['title' => 'Production support', 'description' => 'Give active retainers a clear way to request delivery help.', 'icon' => 'Ops'],
                ['title' => 'Partner enquiry', 'description' => 'Keep collaborations visible without diluting the primary lead path.', 'icon' => 'Collab'],
            ],
        ],
        'commerce' => [
            'singular' => 'buying path',
            'plural' => 'merchandise stories',
            'feature' => 'retail signal',
            'proof' => [
                ['metric' => '12', 'name' => 'Collections', 'summary' => 'Merchandised groups stay visible across the journey.'],
                ['metric' => '35', 'name' => 'Product cards', 'summary' => 'Dense retail cards remain readable.'],
                ['metric' => '100%', 'name' => 'Stock proof', 'summary' => 'Promotional and product evidence sit together.'],
                ['metric' => '1k', 'name' => 'Buyer paths', 'summary' => 'Browsing and conversion routes stay connected.'],
            ],
            'contactRoutes' => [
                ['title' => 'Trade order', 'description' => 'Route wholesale, bulk, and buying enquiries to the right team.', 'icon' => 'Trade'],
                ['title' => 'Product support', 'description' => 'Keep sizing, delivery, and stock questions close to conversion.', 'icon' => 'Help'],
                ['title' => 'Partnerships', 'description' => 'Separate campaign and collaboration enquiries from shopper support.', 'icon' => 'Partner'],
            ],
        ],
        'corporate' => [
            'singular' => 'board paper',
            'plural' => 'board papers',
            'feature' => 'governance check',
            'proof' => [
                ['metric' => '12', 'name' => 'Briefing packs', 'summary' => 'Formal decision material stays structured.'],
                ['metric' => '35', 'name' => 'Service notes', 'summary' => 'Dense operational pages keep their hierarchy.'],
                ['metric' => '100%', 'name' => 'Approval trail', 'summary' => 'Evidence remains visible without marketing noise.'],
                ['metric' => '1x', 'name' => 'Decision path', 'summary' => 'The next action stays explicit.'],
            ],
            'contactRoutes' => [
                ['title' => 'Procurement', 'description' => 'Route formal buying and supplier questions cleanly.', 'icon' => 'Procure'],
                ['title' => 'Investor support', 'description' => 'Keep governance, reporting, and stakeholder requests distinct.', 'icon' => 'IR'],
                ['title' => 'Service desk', 'description' => 'Give existing customers a restrained support route.', 'icon' => 'Desk'],
            ],
        ],
        'education' => [
            'singular' => 'learning pathway',
            'plural' => 'course pathways',
            'feature' => 'learner step',
            'proof' => [
                ['metric' => '12', 'name' => 'Modules', 'summary' => 'Course structure stays clear before enrolment.'],
                ['metric' => '35', 'name' => 'Learners', 'summary' => 'Cohort proof sits close to programme content.'],
                ['metric' => '100%', 'name' => 'Outcome led', 'summary' => 'Every card points to a clear learner result.'],
                ['metric' => '1:1', 'name' => 'Mentor path', 'summary' => 'Support routes are visible before application.'],
            ],
            'contactRoutes' => [
                ['title' => 'Programme advice', 'description' => 'Route course questions to admissions and teaching teams.', 'icon' => 'Course'],
                ['title' => 'Learner support', 'description' => 'Keep support and accessibility requests easy to find.', 'icon' => 'Care'],
                ['title' => 'Partnerships', 'description' => 'Separate employer and school enquiries from enrolment.', 'icon' => 'Partner'],
            ],
        ],
        'healthcare' => [
            'singular' => 'care route',
            'plural' => 'service pathways',
            'feature' => 'clinical signal',
            'proof' => [
                ['metric' => '12', 'name' => 'Care routes', 'summary' => 'Service pathways stay clear and calm.'],
                ['metric' => '35', 'name' => 'Clinicians', 'summary' => 'People and appointments remain connected.'],
                ['metric' => '100%', 'name' => 'Patient-ready', 'summary' => 'Critical details avoid low-contrast treatment.'],
                ['metric' => '1x', 'name' => 'Booking path', 'summary' => 'The next clinical step is visible.'],
            ],
            'contactRoutes' => [
                ['title' => 'Appointment route', 'description' => 'Send patients toward the right service or clinic location.', 'icon' => 'Appt'],
                ['title' => 'Clinical support', 'description' => 'Keep urgent and non-urgent support routes visually distinct.', 'icon' => 'Care'],
                ['title' => 'Referral enquiry', 'description' => 'Separate referrer and partnership enquiries from patient booking.', 'icon' => 'Refer'],
            ],
        ],
        'knowledge' => [
            'singular' => 'research brief',
            'plural' => 'archive entries',
            'feature' => 'source check',
            'proof' => [
                ['metric' => '420+', 'name' => 'Guides', 'summary' => 'Large libraries still need a strong entry point.'],
                ['metric' => '36', 'name' => 'Topics', 'summary' => 'Facet and topic structures remain visible.'],
                ['metric' => '12k', 'name' => 'Saved', 'summary' => 'Reader intent supports conversion paths.'],
                ['metric' => '1x', 'name' => 'Editorial queue', 'summary' => 'Editorial state stays clear.'],
            ],
            'contactRoutes' => [
                ['title' => 'Editorial query', 'description' => 'Route topic, source, and correction requests to editors.', 'icon' => 'Edit'],
                ['title' => 'Research support', 'description' => 'Give readers a clear path for deeper help.', 'icon' => 'Source'],
                ['title' => 'Sponsorship', 'description' => 'Separate commercial enquiries from editorial contact.', 'icon' => 'Sponsor'],
            ],
        ],
        'local-services' => [
            'singular' => 'service call',
            'plural' => 'local jobs',
            'feature' => 'dispatch step',
            'proof' => [
                ['metric' => '24h', 'name' => 'Quote window', 'summary' => 'Service response promises stay prominent.'],
                ['metric' => '18', 'name' => 'Postcodes', 'summary' => 'Local coverage remains easy to scan.'],
                ['metric' => '07', 'name' => 'Crews', 'summary' => 'Dispatch capacity supports trust.'],
                ['metric' => '1x', 'name' => 'Job route', 'summary' => 'The next booking step is obvious.'],
            ],
            'contactRoutes' => [
                ['title' => 'Urgent job', 'description' => 'Route time-sensitive work toward the fastest response path.', 'icon' => 'Now'],
                ['title' => 'Planned quote', 'description' => 'Keep larger estimates and surveys distinct from urgent work.', 'icon' => 'Quote'],
                ['title' => 'Service area', 'description' => 'Help visitors check coverage before they submit details.', 'icon' => 'Area'],
            ],
        ],
        'nonprofit' => [
            'singular' => 'impact story',
            'plural' => 'campaign stories',
            'feature' => 'supporter route',
            'proof' => [
                ['metric' => '12%', 'name' => 'Target left', 'summary' => 'Campaign progress remains visible.'],
                ['metric' => '84%', 'name' => 'Funded', 'summary' => 'Donation momentum is easy to understand.'],
                ['metric' => '31', 'name' => 'Volunteers', 'summary' => 'Action paths go beyond donation.'],
                ['metric' => '1x', 'name' => 'Impact route', 'summary' => 'Visitors can choose a useful next step.'],
            ],
            'contactRoutes' => [
                ['title' => 'Donate', 'description' => 'Keep donation questions close to campaign proof.', 'icon' => 'Give'],
                ['title' => 'Volunteer', 'description' => 'Route practical help without hiding financial support.', 'icon' => 'Help'],
                ['title' => 'Partnerships', 'description' => 'Separate funder and organisation enquiries from supporter contact.', 'icon' => 'Ally'],
            ],
        ],
        'portfolio' => [
            'singular' => 'case file',
            'plural' => 'studio cases',
            'feature' => 'outcome note',
            'proof' => [
                ['metric' => '+42%', 'name' => 'Outcome lift', 'summary' => 'Case-study proof stays attached to work.'],
                ['metric' => '120+', 'name' => 'Assets', 'summary' => 'Project evidence can be visually dense.'],
                ['metric' => '5h', 'name' => 'Proof deck', 'summary' => 'Short proof widgets support a premium studio flow.'],
                ['metric' => '1x', 'name' => 'Case path', 'summary' => 'Visitors move from work to enquiry cleanly.'],
            ],
            'contactRoutes' => [
                ['title' => 'New brief', 'description' => 'Route serious project enquiries into case-study-led scoping.', 'icon' => 'Brief'],
                ['title' => 'Media request', 'description' => 'Keep speaking and press enquiries visible but secondary.', 'icon' => 'Media'],
                ['title' => 'Collaboration', 'description' => 'Separate partner enquiries from direct client work.', 'icon' => 'Collab'],
            ],
        ],
        'saas' => [
            'singular' => 'product workflow',
            'plural' => 'activation paths',
            'feature' => 'product signal',
            'proof' => [
                ['metric' => '12', 'name' => 'Workflows', 'summary' => 'Feature density stays product-led.'],
                ['metric' => '35', 'name' => 'Teams', 'summary' => 'Proof widgets support trial confidence.'],
                ['metric' => '100%', 'name' => 'Setup path', 'summary' => 'Activation and support stay connected.'],
                ['metric' => '1x', 'name' => 'Demo route', 'summary' => 'Conversion remains obvious after detail content.'],
            ],
            'contactRoutes' => [
                ['title' => 'Demo request', 'description' => 'Route qualified prospects toward a product walkthrough.', 'icon' => 'Demo'],
                ['title' => 'Support', 'description' => 'Keep customer help distinct from sales conversion.', 'icon' => 'Help'],
                ['title' => 'Partnerships', 'description' => 'Separate integration and channel enquiries from trials.', 'icon' => 'API'],
            ],
        ],
    ][$themeKey] ?? [
        'singular' => 'foundation entry',
        'plural' => 'foundation entries',
        'feature' => 'foundation widget',
        'proof' => themeDemoScreenshotProofItems(),
        'contactRoutes' => [
            ['title' => 'Project scoping', 'description' => 'Route new builds and content-model planning to the right team.', 'icon' => 'Scope'],
            ['title' => 'Support', 'description' => 'Surface help paths for existing sites without losing contact clarity.', 'icon' => 'Help'],
            ['title' => 'Partnerships', 'description' => 'Keep commercial and agency enquiries visible in the same layout.', 'icon' => 'Partner'],
        ],
    ];

    $singular = $copy['singular'];
    $plural = $copy['plural'];
    $feature = $copy['feature'];

    return $copy + [
        'homepageHeroHeading' => 'Featured ' . $plural,
        'directoryHeroHeading' => 'Browse ' . $plural,
        'detailHeroHeading' => ucfirst($singular) . ' detail preview',
        'contactHeroHeading' => 'Start the ' . $singular . ' conversation',
        'emptyHeroHeading' => 'No ' . $plural . ' yet',
        'ctaHeroHeading' => 'Convert with ' . $plural,
        'featuresHeading' => ucfirst($feature) . ' cards',
        'featuresSummary' => 'Repeated ' . $feature . ' cards check wrapping, hierarchy, and theme-specific scanning.',
        'proofHeading' => ucfirst($singular) . ' proof points',
        'proofSummary' => 'Metrics and evidence stay tied to the buyer workflow for this theme.',
        'listingHeading' => 'Featured ' . $plural,
        'listingSummary' => ucfirst($plural) . ' cover repeated card treatment outside the homepage.',
        'directoryHeading' => ucfirst($plural),
        'directoryCtaHeading' => 'Filter ' . $plural,
        'directorySummary' => 'Directory pages focus on repeated ' . $singular . ' cards, imagery, and scanning density.',
        'detailProofHeading' => ucfirst($singular) . ' facts',
        'detailProofSummary' => 'Detail pages need supporting proof without turning into another index.',
        'detailFeaturesHeading' => ucfirst($singular) . ' sections',
        'detailFeaturesSummary' => 'Long-form pages check compact content blocks after the hero.',
        'detailCtaHeading' => 'Continue from this ' . $singular,
        'detailCtaSummary' => 'A detail page CTA checks action spacing after richer content.',
        'contactHeading' => ucfirst($singular) . ' enquiry routes',
        'contactSummary' => 'Contact pages check routing cards, support details, and form-adjacent decisions.',
        'contactCtaHeading' => 'Send a ' . $singular . ' enquiry',
        'contactCtaSummary' => 'This static demo form checks contact layout without submitting data.',
        'emptyHeading' => 'No visible ' . $plural,
        'emptyCtaHeading' => 'Reset the ' . $singular . ' search',
        'emptySummary' => 'Empty states focus on readable recovery messaging for this theme workflow.',
        'recoveryHeading' => ucfirst($singular) . ' recovery links',
        'recoverySummary' => '404 pages need clear navigation choices that still match the theme lane.',
        'recoveryCtaHeading' => 'Find the right ' . $singular,
        'recoveryCtaSummary' => 'A compact recovery CTA checks system layout action styling.',
        'maintenanceHeading' => ucfirst($singular) . ' maintenance status',
        'maintenanceSummary' => 'Status pages need short operational proof points.',
        'maintenanceCtaHeading' => ucfirst($singular) . ' updates resume soon',
        'maintenanceCtaSummary' => 'Maintenance pages verify recovery messaging and action styling.',
        'systemHeading' => ucfirst($singular) . ' system checks',
        'systemSummary' => 'System pages make operational support layouts explicit for this theme.',
        'systemCtaHeading' => ucfirst($singular) . ' recovery action',
        'systemCtaSummary' => 'System pages need the same readable CTA treatment as marketing pages.',
        'ctaProofHeading' => ucfirst($singular) . ' conversion evidence',
        'ctaProofSummary' => 'Conversion-only pages check proof widgets and action rhythm.',
        'ctaHeading' => 'Move visitors through ' . $plural,
        'ctaSummary' => 'CTA pages should feel like a domain-specific next step, not another generic content page.',
    ];
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
function themeDemoScreenshotFeatures(string $surface, int $count = 6, ?string $themeKey = null): array
{
    $copy = $themeKey === null ? null : themeDemoScreenshotThemeCopy($themeKey);
    $feature = is_array($copy) ? $copy['feature'] : 'foundation widget';
    $surfaceLabel = themeDemoScreenshotSurfaceLabel($surface);

    return collect(range(1, $count))
        ->map(fn (int $number): array => [
            'title' => ucfirst((string) $feature) . ' ' . $number,
            'description' => $surfaceLabel . ' checks ' . $feature . ' spacing, wrapping, and section density.',
            'icon' => 'Step ' . $number,
        ])
        ->all();
}

/**
 * @return array<int, array{metric: string, name: string, summary: string}>
 */
function themeDemoScreenshotProofItems(?string $themeKey = null): array
{
    if ($themeKey !== null) {
        $copy = themeDemoScreenshotThemeCopy($themeKey);

        if (isset($copy['proof']) && is_array($copy['proof'])) {
            return $copy['proof'];
        }
    }

    return [
        ['metric' => '12', 'name' => 'Layouts', 'summary' => 'Page type and layout combinations stay visible.'],
        ['metric' => '35', 'name' => 'Screens', 'summary' => 'Every first-party theme surface gets a PNG.'],
        ['metric' => '100%', 'name' => 'Seeded data', 'summary' => 'Screenshots render seeded content instead of fallbacks.'],
        ['metric' => '1x', 'name' => 'Install', 'summary' => 'Each theme surface is installed once per test file.'],
    ];
}

/**
 * @param  array<int, string>  $mediaUrls
 * @return array<int, array<string, string>>
 */
function themeDemoScreenshotListingItems(array $mediaUrls, string $surface, int $count = 4, ?string $themeKey = null): array
{
    $copy = $themeKey === null ? null : themeDemoScreenshotThemeCopy($themeKey);
    $singular = is_array($copy) ? $copy['singular'] : 'foundation entry';
    $surfaceLabel = themeDemoScreenshotSurfaceLabel($surface);
    $listingMediaUrls = $mediaUrls;

    if ($listingMediaUrls === [] && $themeKey !== null) {
        $listingMediaUrls = ThemeDemoMedia::groupedForTheme($themeKey)['listing'];
    }

    return collect(range(1, $count))
        ->map(function (int $number) use ($listingMediaUrls, $singular, $surfaceLabel): array {
            $mediaUrl = $listingMediaUrls[($number - 1) % max(1, count($listingMediaUrls))] ?? null;

            return array_filter([
                'title' => ucfirst((string) $singular) . ' ' . $number,
                'summary' => $surfaceLabel . ' proves repeated ' . $singular . ' cards render with realistic public content.',
                'url' => '#item-' . $number,
                'type' => ucfirst((string) $singular),
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
        return new NavigationData(brandName: 'Capell Foundation', items: $navigation);
    }

    if (is_array($navigation)) {
        return NavigationData::from([
            'brandName' => data_get($navigation, 'brandName', 'Capell Foundation'),
            'items' => data_get($navigation, 'items', []),
            'ctaLabel' => data_get($navigation, 'ctaLabel'),
            'ctaUrl' => data_get($navigation, 'ctaUrl'),
        ]);
    }

    return new NavigationData(
        brandName: 'Capell Foundation',
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
        'education' => themeDemoRepositoryPath('packages/theme-education/resources/css/theme-education.css'),
        'healthcare' => themeDemoRepositoryPath('packages/theme-healthcare/resources/css/theme-healthcare.css'),
        'knowledge' => themeDemoRepositoryPath('packages/theme-knowledge/resources/css/theme-knowledge.css'),
        'local-services' => themeDemoRepositoryPath('packages/theme-local-services/resources/css/theme-local-services.css'),
        'nonprofit' => themeDemoRepositoryPath('packages/theme-nonprofit/resources/css/theme-nonprofit.css'),
        'portfolio' => themeDemoRepositoryPath('packages/theme-portfolio/resources/css/theme-portfolio.css'),
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

/**
 * @return array{width: int, height: int}
 */
function themeDemoMobileScreenshotViewport(): array
{
    return ['width' => 390, 'height' => 1200];
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
