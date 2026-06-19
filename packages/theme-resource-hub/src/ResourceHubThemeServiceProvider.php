<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\ResourceHub;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\ResourceHub\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class ResourceHubThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'resource-hub';

    public static string $packageName = 'capell-app/theme-resource-hub';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Resource Hub',
            description: 'Resource Hub theme for landing-page inspiration and education hubs with crisp search, category shortcuts, website examples, social images, templates, courses, books, learning resources, consent-friendly embeds, and archive-ready sections.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/resource-hub.jpg',
            tags: ['Resource Hub', 'Landing Pages', 'Education', 'Templates', 'Archives'],
            bestFit: ['Landing page inspiration hubs', 'Website example libraries', 'Template and course directories', 'Marketing education sites', 'Creator resource archives'],
            includedSections: ['navigation', 'hero', 'utility-hero', 'category-navigation', 'website-examples', 'social-templates', 'courses-books', 'learning-resources', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Resource Hub',
                    description: 'Resource Hub visual preset for a bright white education hub with crisp typography, search-led hero, category shortcuts, varied archive card densities, website screenshots, video thumbnails, templates, courses, books, consent-friendly embeds, and newsletter capture.',
                    previewImage: '/vendor/capell/themes/resource-hub.jpg',
                    values: [
                        'primaryColor' => '#101828',
                        'accentColor' => '#2563eb',
                        'neutralColor' => '#475467',
                        'surfaceColor' => '#ffffff',
                        'foregroundColor' => '#101828',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'screenshot',
                        'radius' => 'md',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'airy',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/resource-hub.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-resource-hub');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-resource-hub');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-resource-hub::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-resource-hub.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-resource-hub::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-resource-hub::sections.hero', failLoudly: true),
            'utility-hero' => new ViewSectionRenderer(self::THEME_KEY, 'utility-hero', 'capell-theme-resource-hub::sections.utility-hero', failLoudly: true),
            'category-navigation' => new ViewSectionRenderer(self::THEME_KEY, 'category-navigation', 'capell-theme-resource-hub::sections.category-navigation', failLoudly: true),
            'website-examples' => new ViewSectionRenderer(self::THEME_KEY, 'website-examples', 'capell-theme-resource-hub::sections.website-examples', failLoudly: true),
            'social-templates' => new ViewSectionRenderer(self::THEME_KEY, 'social-templates', 'capell-theme-resource-hub::sections.social-templates', failLoudly: true),
            'courses-books' => new ViewSectionRenderer(self::THEME_KEY, 'courses-books', 'capell-theme-resource-hub::sections.courses-books', failLoudly: true),
            'learning-resources' => new ViewSectionRenderer(self::THEME_KEY, 'learning-resources', 'capell-theme-resource-hub::sections.learning-resources', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-resource-hub::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-resource-hub::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-resource-hub::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-resource-hub::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-resource-hub::sections.footer', failLoudly: true),
        ];
    }
}
