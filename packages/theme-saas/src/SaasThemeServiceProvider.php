<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Saas;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemePageAdapterRegistry;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\Saas\Console\Commands\DemoCommand;
use Capell\ThemeStudio\Saas\Rendering\BlogSectionRenderer;
use Capell\ThemeStudio\Saas\ThemeStudio\Adapters\SaasThemePageAdapter;
use Illuminate\Support\ServiceProvider;
use Override;

class SaasThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'saas';

    public static string $packageName = 'capell-app/theme-saas';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'SaaS',
            description: 'Product-led SaaS layouts with feature discovery, comparison, calculator, and blog-ready growth content.',
            package: 'capell-app/theme-saas',
            previewImage: '/vendor/capell/themes/saas.jpg',
            tags: ['Product', 'Conversion', 'Growth'],
            bestFit: ['Software products', 'Startups', 'Subscription services'],
            includedSections: ['navigation', 'hero', 'features', 'proof', 'logos', 'testimonials', 'faq', 'content-listing', 'comparison', 'calculator', 'pricing', 'docs-onboarding', 'demo-request', 'cta', 'footer', 'blog'],
            presets: [
                new ThemePresetData(
                    key: 'saas',
                    name: 'SaaS',
                    description: 'Product-led conversion direction for landing, article, blog, and feature screens.',
                    previewImage: '/vendor/capell/themes/saas.jpg',
                    values: [
                        'primaryColor' => '#2563eb',
                        'accentColor' => '#06b6d4',
                        'neutralColor' => '#0f172a',
                        'surfaceColor' => '#f8fafc',
                        'foregroundColor' => '#0f172a',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'structured',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'framed',
                        'radius' => 'md',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'compact',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/saas.css'],
            runtime: FrontendRuntime::Blade,
            // Theme Studio inherits section fallbacks from the runtime default; capell.json no longer declares package-level inheritance because the default fallback lives in capell-app/frontend.
            extends: 'default',
        );
    }

    #[Override]
    public function register(): void {}

    public function boot(ThemeRegistry $registry): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([DemoCommand::class]);
        }

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-saas');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-saas');
        $this->loadScreenshotFixtureRoutes();

        if (! CapellCore::isPackageInstalled(self::$packageName)) {
            return;
        }

        $this->registerVendorCssAssets();
        $this->app->make(ThemePageAdapterRegistry::class)
            ->register(self::THEME_KEY, SaasThemePageAdapter::class);

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-saas::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function loadScreenshotFixtureRoutes(): void
    {
        if (filter_var(getenv('CAPELL_THEME_SAAS_SCREENSHOT_FIXTURES_ENABLED'), FILTER_VALIDATE_BOOL) !== true) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/screenshot-fixtures.php');
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindImport('resources/css/theme-saas.css', self::$packageName),
        );

        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName),
        );
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        $blogAvailable = CapellCore::isPackageInstalled('capell-app/blog');
        $contentSectionsAvailable = CapellCore::isPackageInstalled('capell-app/content-sections');
        $documentLifecycleAvailable = CapellCore::isPackageInstalled('capell-app/document-lifecycle');
        $formBuilderAvailable = CapellCore::isPackageInstalled('capell-app/form-builder');

        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-saas::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-saas::sections.hero', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-saas::sections.features', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-saas::sections.proof', failLoudly: true),
            'logos' => new ViewSectionRenderer(self::THEME_KEY, 'logos', 'capell-theme-saas::sections.logos', failLoudly: true),
            'testimonials' => new ViewSectionRenderer(self::THEME_KEY, 'testimonials', 'capell-theme-saas::sections.testimonials', failLoudly: true),
            'faq' => new ViewSectionRenderer(self::THEME_KEY, 'faq', 'capell-theme-saas::sections.faq', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-saas::sections.content-listing', failLoudly: true),
            'comparison' => new ViewSectionRenderer(self::THEME_KEY, 'comparison', 'capell-theme-saas::sections.comparison', failLoudly: true),
            'calculator' => new ViewSectionRenderer(self::THEME_KEY, 'calculator', 'capell-theme-saas::sections.calculator', failLoudly: true),
            'pricing' => new ViewSectionRenderer(self::THEME_KEY, 'pricing', 'capell-theme-saas::sections.pricing', true, ['contentSectionsAvailable' => $contentSectionsAvailable]),
            'docs-onboarding' => new ViewSectionRenderer(self::THEME_KEY, 'docs-onboarding', 'capell-theme-saas::sections.docs-onboarding', true, ['documentLifecycleAvailable' => $documentLifecycleAvailable]),
            'demo-request' => new ViewSectionRenderer(self::THEME_KEY, 'demo-request', 'capell-theme-saas::sections.demo-request', true, ['formBuilderAvailable' => $formBuilderAvailable]),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-saas::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-saas::sections.footer', failLoudly: true),
            'blog' => new BlogSectionRenderer(self::THEME_KEY, $blogAvailable, failLoudly: true),
        ];
    }
}
