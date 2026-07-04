<?php

declare(strict_types=1);

use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Enums\PageTypeEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Models\Blueprint;
use Capell\Core\Models\Layout;
use Capell\Core\Models\Page;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Site;
use Capell\Core\Models\Theme;
use Capell\Core\Support\Manifest\CapellManifestData;
use Capell\Core\Support\PackageRegistry\CapellPackageRegistry;
use Capell\Core\ThemeStudio\Contracts\ThemePageAdapter;
use Capell\Core\ThemeStudio\Contracts\ThemeRuntimeSettings;
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
use Capell\Core\ThemeStudio\Settings\ThemeStudioSettings;
use Capell\Core\ThemeStudio\Theme\ThemePageAdapterRegistry;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\FoundationTheme\Providers\FoundationThemeServiceProvider;
use Capell\Frontend\Facades\Frontend;
use Capell\Tests\Packages\Fixtures\ThemeFrontendStringSectionRenderer;
use Capell\ThemeStudio\CaseStudyPlatform\CaseStudyPlatformThemeServiceProvider;
use Capell\ThemeStudio\DarkProductSystem\DarkProductSystemThemeServiceProvider;
use Capell\ThemeStudio\DenseNewsAnalysis\DenseNewsAnalysisThemeServiceProvider;
use Capell\ThemeStudio\ExperimentalDirectory\ExperimentalDirectoryThemeServiceProvider;
use Capell\ThemeStudio\PremiumPortfolioCollection\PremiumPortfolioCollectionThemeServiceProvider;
use Capell\ThemeStudio\RawIndex\RawIndexThemeServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Testing\TestResponse;

require_once __DIR__ . '/PublicOutputSafety.php';

/**
 * @return array<string, array{provider: class-string<ServiceProvider>, package: string}>
 */
function themeFrontendFirstPartyThemes(): array
{
    return [
        'default' => ['provider' => FoundationThemeServiceProvider::class, 'package' => FoundationThemeServiceProvider::$packageName],
        'case-study-platform' => ['provider' => CaseStudyPlatformThemeServiceProvider::class, 'package' => CaseStudyPlatformThemeServiceProvider::$packageName],
        'dark-product-system' => ['provider' => DarkProductSystemThemeServiceProvider::class, 'package' => DarkProductSystemThemeServiceProvider::$packageName],
        'dense-news-analysis' => ['provider' => DenseNewsAnalysisThemeServiceProvider::class, 'package' => DenseNewsAnalysisThemeServiceProvider::$packageName],
        'experimental-directory' => ['provider' => ExperimentalDirectoryThemeServiceProvider::class, 'package' => ExperimentalDirectoryThemeServiceProvider::$packageName],
        'premium-portfolio-collection' => ['provider' => PremiumPortfolioCollectionThemeServiceProvider::class, 'package' => PremiumPortfolioCollectionThemeServiceProvider::$packageName],
        'raw-index' => ['provider' => RawIndexThemeServiceProvider::class, 'package' => RawIndexThemeServiceProvider::$packageName],
    ];
}

function themeFrontendBootTheme(string $themeKey): void
{
    themeFrontendRegisterFoundationRenderer();

    $themes = themeFrontendFirstPartyThemes();

    throw_unless(array_key_exists($themeKey, $themes), RuntimeException::class, sprintf('Unknown theme key [%s].', $themeKey));

    $theme = $themes[$themeKey];

    CapellCore::forcePackageInstalled($theme['package']);

    $provider = new $theme['provider'](app());
    $provider->register();

    if (method_exists($provider, 'boot')) {
        app()->call(static fn (): mixed => $provider->boot(resolve(ThemeRegistry::class)));
    }

    themeFrontendRegisterStringRendererForTheme($themeKey);
}

function themeFrontendRegisterFoundationRenderer(): void
{
    $registry = resolve(ThemeRegistry::class);
    View::addNamespace('theme-frontend-test', dirname(__DIR__) . '/Fixtures/theme-frontend-route');
    themeFrontendRegisterFoundationPackageManifest();

    if ($registry->has('default')) {
        return;
    }

    $sectionRenderers = collect(['navigation', 'hero', 'proof', 'features', 'content-listing', 'cta', 'footer'])
        ->mapWithKeys(fn (string $sectionKey): array => [
            $sectionKey => new ThemeFrontendStringSectionRenderer('default', $sectionKey),
        ])
        ->all();

    $registry->register(
        definition: new ThemeDefinitionData(
            key: 'default',
            name: 'Foundation',
            description: 'Default foundation test renderer.',
            package: FoundationThemeServiceProvider::$packageName,
            previewImage: '',
            tags: [],
            bestFit: [],
            includedSections: array_keys($sectionRenderers),
            presets: [
                new ThemePresetData(
                    key: 'boardroom',
                    name: 'Boardroom',
                    description: 'Foundation test preset.',
                    previewImage: '',
                    values: ['primaryColor' => '#1a2d6d', 'accentColor' => '#92400e'],
                ),
            ],
            runtime: FrontendRuntime::Blade,
        ),
        themeRenderer: new BladeThemeRenderer('default', 'theme-frontend-test::theme-layout', $sectionRenderers),
        sectionRenderers: array_values($sectionRenderers),
    );
}

function themeFrontendRegisterFoundationPackageManifest(): void
{
    $manifestPath = dirname(__DIR__, 3) . '/packages/theme-foundation/capell.json';
    $manifest = json_decode((string) file_get_contents($manifestPath), true, flags: JSON_THROW_ON_ERROR);

    throw_unless(is_array($manifest), RuntimeException::class, 'Foundation theme manifest must decode to an array.');

    $manifestData = CapellManifestData::fromArray(
        themeFrontendStringKeyedArray($manifest),
        dirname($manifestPath),
    );

    CapellCore::registerManifestPackage($manifestData);
    CapellCore::forcePackageInstalled(FoundationThemeServiceProvider::$packageName);

    $packageRegistry = resolve(CapellPackageRegistry::class);
    $packageRegistry->fill([
        ...$packageRegistry->all(),
        FoundationThemeServiceProvider::$packageName => $manifestData,
        'default' => $manifestData,
    ]);
}

/**
 * @param  array<array-key, mixed>  $items
 * @return array<string, mixed>
 */
function themeFrontendStringKeyedArray(array $items): array
{
    $stringKeyedItems = [];

    foreach ($items as $key => $value) {
        throw_unless(is_string($key), RuntimeException::class, 'Expected manifest keys to be strings.');

        $stringKeyedItems[$key] = $value;
    }

    return $stringKeyedItems;
}

function themeFrontendRegisterStringRendererForTheme(string $themeKey): void
{
    $registry = resolve(ThemeRegistry::class);
    View::addNamespace('theme-frontend-test', dirname(__DIR__) . '/Fixtures/theme-frontend-route');

    if (! $registry->has($themeKey)) {
        return;
    }

    $definition = $registry->definition($themeKey);
    $sectionRenderers = collect(['navigation', 'hero', 'proof', 'features', 'content-listing', 'cta', 'footer'])
        ->mapWithKeys(fn (string $sectionKey): array => [
            $sectionKey => new ThemeFrontendStringSectionRenderer($themeKey, $sectionKey),
        ])
        ->all();

    $registry->register(
        definition: $definition,
        themeRenderer: new BladeThemeRenderer($themeKey, 'theme-frontend-test::theme-layout', $sectionRenderers),
        sectionRenderers: array_values($sectionRenderers),
    );
}

function themeFrontendRegisterFixtureAdapter(string $themeKey): void
{
    resolve(ThemePageAdapterRegistry::class)->register(
        $themeKey,
        fn (): ThemePageAdapter => new class implements ThemePageAdapter
        {
            public function currentPage(): ThemePageData
            {
                $page = Frontend::page();
                $renderData = is_array($page?->meta) ? data_get($page->meta, 'theme_frontend.render_data', []) : [];
                $renderData = is_array($renderData) ? $renderData : [];

                $title = (string) data_get($renderData, 'title', $page->name ?? 'Theme Route Smoke');

                return new ThemePageData(
                    title: $title,
                    brand: resolve(ThemeRuntimeSettings::class)->brandProfile(),
                    sections: [
                        HeroSectionData::from([
                            'heading' => (string) data_get($renderData, 'hero.heading', $title),
                            'summary' => (string) data_get($renderData, 'hero.summary', 'Theme route smoke content.'),
                            'actions' => [['label' => 'Primary action', 'url' => '#content', 'style' => 'primary']],
                        ]),
                        ProofSectionData::from([
                            'heading' => 'Route proof points',
                            'items' => [
                                ['metric' => '200', 'name' => 'Route', 'summary' => 'The public route rendered successfully.'],
                                ['metric' => '0', 'name' => 'Leaks', 'summary' => 'Authoring tokens stay out of public HTML.'],
                            ],
                        ]),
                        FeatureSectionData::from([
                            'heading' => 'Rendered section output',
                            'features' => [
                                ['title' => 'Theme wrapper', 'description' => 'Blade renderer returned theme HTML.'],
                                ['title' => 'Runtime tokens', 'description' => 'Theme Studio runtime was resolved.'],
                            ],
                        ]),
                        ContentListingSectionData::from([
                            'heading' => 'Route-backed listing',
                            'items' => [
                                ['title' => 'Route smoke item', 'summary' => 'Expected repeated section output.', 'url' => '#item'],
                            ],
                        ]),
                        CtaSectionData::from([
                            'heading' => 'Theme route smoke CTA',
                            'summary' => 'Final section confirms the page reached the theme renderer.',
                            'actions' => [['label' => 'Finish', 'url' => '#top', 'style' => 'secondary']],
                        ]),
                    ],
                    navigation: new NavigationData(brandName: 'Theme Route Smoke', items: [
                        ['label' => 'Content', 'url' => '#content'],
                    ]),
                    footer: new FooterData(brandName: 'Theme Route Smoke'),
                );
            }
        },
    );
}

/**
 * @param  array<string, mixed>  $brandProfile
 * @param  array<string, array<string, mixed>>  $themeOverrides
 */
function themeFrontendConfigureRuntime(
    string $themeKey,
    ?string $presetKey = null,
    array $brandProfile = [],
    array $themeOverrides = [],
): void {
    $settings = resolve(ThemeStudioSettings::class);
    $definition = resolve(ThemeRegistry::class)->definition($themeKey);
    $presetKey ??= $definition->presets[0]->key;

    $settings->activeTheme = $themeKey;
    $settings->activePreset = $presetKey;
    $settings->brandProfile = [
        ...BrandProfileData::from($settings->brandProfile)->toArray(),
        ...$brandProfile,
    ];
    $settings->themeOverrides = $themeOverrides;
    $settings->save();
}

function themeFrontendCreatePage(string $themeKey, ?string $presetKey = null): PageUrl
{
    themeFrontendBootTheme($themeKey);
    themeFrontendRegisterFixtureAdapter($themeKey);
    themeFrontendConfigureRuntime($themeKey, $presetKey);
    View::addNamespace('theme-frontend-test', dirname(__DIR__) . '/Fixtures/theme-frontend-route');

    $theme = Theme::factory()->create(['key' => $themeKey, 'name' => ucfirst(str_replace('-', ' ', $themeKey))]);
    $layout = Layout::factory()->default()->create([
        'meta' => [
            'layout_file' => 'theme-frontend-test::layout',
            'master_file' => 'theme-frontend-test::page',
        ],
    ]);
    $pageType = Blueprint::query()->pageType()->where('key', PageTypeEnum::Default->value)->first()
        ?? PageTypeEnum::Default->createPageType();
    $site = Site::factory()
        ->theme($theme)
        ->withTranslations(siteDomainData: [
            'default' => true,
            'domain' => $themeKey . '.theme-route.test',
            'path' => null,
            'scheme' => 'https',
            'status' => true,
        ])
        ->create();

    $page = Page::factory()
        ->site($site)
        ->layout($layout)
        ->withTranslations(
            languages: $site->language,
            data: [
                'content' => '<p>Expected route smoke content for ' . e($themeKey) . '.</p>',
                'title' => ucfirst(str_replace('-', ' ', $themeKey)) . ' Route Smoke',
            ],
            slug: 'theme-route-smoke',
        )
        ->type($pageType)
        ->state([
            'meta' => [
                'rendering_strategy' => 'blade',
                'theme_frontend' => [
                    'render_data' => [
                        'title' => ucfirst(str_replace('-', ' ', $themeKey)) . ' Route Smoke',
                        'hero' => [
                            'heading' => ucfirst(str_replace('-', ' ', $themeKey)) . ' Route Smoke',
                            'summary' => 'Expected route smoke content for ' . $themeKey . '.',
                        ],
                    ],
                ],
            ],
        ])
        ->create();

    $page->loadMissing(['pageUrl.siteDomain', 'translations', 'type']);

    expect($page->type?->key)->toBe(PageTypeEnum::Default->value);
    expect($page->pageUrl)->toBeInstanceOf(PageUrl::class);

    return $page->pageUrl;
}

function themeFrontendMigrateHtmlCacheTables(): void
{
    foreach ([
        '2026_05_10_190854_01_create_cached_model_urls_table' => 'cached_model_urls',
        '2026_05_14_000001_create_stale_cached_urls_table' => 'stale_cached_urls',
    ] as $migration => $table) {
        if (Schema::hasTable($table)) {
            continue;
        }

        $migrationInstance = require dirname(__DIR__, 3) . '/packages/html-cache/database/migrations/' . $migration . '.php';

        if (is_object($migrationInstance) && method_exists($migrationInstance, 'up')) {
            $migrationInstance->up();
        }
    }

    if (Schema::hasTable('cached_model_urls') && ! Schema::hasColumn('cached_model_urls', 'hit_count')) {
        $migrationInstance = require dirname(__DIR__, 3) . '/packages/html-cache/database/migrations/2026_06_07_000001_add_telemetry_to_cached_model_urls_table.php';

        if (is_object($migrationInstance) && method_exists($migrationInstance, 'up')) {
            $migrationInstance->up();
        }
    }
}

/**
 * @return array<int, string>
 */
function themeFrontendForbiddenPublicTokens(): array
{
    return [
        'CapellFrontendAuthoring',
        'capell-authoring',
        'authoring/regions',
        'edit_url',
        'recordKey',
        'capell-frontend-authoring',
        'signed editor',
        'signed_editor',
        'data-capell-authoring',
    ];
}

function assertThemeFrontendPublicHtmlIsSafe(TestResponse|string $response): void
{
    assertCapellPublicOutputIsSafe($response, 'theme frontend HTML');
}
