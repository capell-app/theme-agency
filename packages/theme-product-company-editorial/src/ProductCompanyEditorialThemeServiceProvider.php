<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\ProductCompanyEditorial;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\ProductCompanyEditorial\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class ProductCompanyEditorialThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'product-company-editorial';

    public static string $packageName = 'capell-app/theme-product-company-editorial';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Product Company Editorial',
            description: 'Product-company editorial theme for featured posts, product updates, engineering essays, design stories, templates, culture notes, author metadata, and newsletter conversion.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/product-company-editorial.jpg',
            tags: ['Editorial', 'Product Updates', 'Design', 'Engineering', 'Templates'],
            bestFit: ['Product companies', 'Design tools', 'Developer platforms', 'Creative software teams', 'Startup blogs'],
            includedSections: ['navigation', 'hero', 'featured-posts', 'topic-areas', 'story-cards', 'product-updates', 'template-stories', 'author-callouts', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Product Company Editorial',
                    description: 'Product Company Editorial visual preset for dark mastheads, topic labels, author metadata, colorful editorial thumbnails, product updates, templates, culture stories, and newsletter signup.',
                    previewImage: '/vendor/capell/themes/product-company-editorial.jpg',
                    values: [
                        'primaryColor' => '#111111',
                        'accentColor' => '#7c5cff',
                        'neutralColor' => '#18151f',
                        'surfaceColor' => '#fbfaf7',
                        'foregroundColor' => '#111111',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'illustrated',
                        'radius' => 'md',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'airy',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/product-company-editorial.css'],
            runtime: FrontendRuntime::Blade,
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

        if (! CapellCore::isPackageInstalled(self::$packageName)) {
            return;
        }

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-product-company-editorial');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-product-company-editorial');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-product-company-editorial::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-product-company-editorial.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-product-company-editorial::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-product-company-editorial::sections.hero', failLoudly: true),
            'featured-posts' => new ViewSectionRenderer(self::THEME_KEY, 'featured-posts', 'capell-theme-product-company-editorial::sections.featured-posts', failLoudly: true),
            'topic-areas' => new ViewSectionRenderer(self::THEME_KEY, 'topic-areas', 'capell-theme-product-company-editorial::sections.topic-areas', failLoudly: true),
            'story-cards' => new ViewSectionRenderer(self::THEME_KEY, 'story-cards', 'capell-theme-product-company-editorial::sections.story-cards', failLoudly: true),
            'product-updates' => new ViewSectionRenderer(self::THEME_KEY, 'product-updates', 'capell-theme-product-company-editorial::sections.product-updates', failLoudly: true),
            'template-stories' => new ViewSectionRenderer(self::THEME_KEY, 'template-stories', 'capell-theme-product-company-editorial::sections.template-stories', failLoudly: true),
            'author-callouts' => new ViewSectionRenderer(self::THEME_KEY, 'author-callouts', 'capell-theme-product-company-editorial::sections.author-callouts', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-product-company-editorial::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-product-company-editorial::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-product-company-editorial::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-product-company-editorial::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-product-company-editorial::sections.footer', failLoudly: true),
        ];
    }
}
