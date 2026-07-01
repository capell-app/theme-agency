<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\PortfolioDirectory;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\PortfolioDirectory\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class PortfolioDirectoryThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'portfolio-directory';

    public static string $packageName = 'capell-app/theme-portfolio-directory';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Portfolio Directory',
            description: 'Portfolio directory theme for designers, developers, studios, large preview tiles, role and medium filters, featured portfolios, resume resources, articles, curated lists, and related work.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/portfolio-directory.jpg',
            tags: ['Portfolio', 'Directory', 'Designers', 'Developers', 'Resources'],
            bestFit: ['Portfolio directories', 'Designer galleries', 'Developer portfolios', 'Studio showcases', 'Career resource hubs'],
            includedSections: ['navigation', 'hero', 'directory-hero', 'role-filters', 'portfolio-grid', 'resume-resources', 'curated-lists', 'profile-detail', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Portfolio Directory',
                    description: 'Portfolio Directory visual preset for clean white galleries, energetic accent color, large preview tiles, simple role and medium filters, featured portfolios, resume resources, articles, curated lists, gallery details, social links, and related portfolios.',
                    previewImage: '/vendor/capell/themes/portfolio-directory.jpg',
                    values: [
                        'primaryColor' => '#111111',
                        'accentColor' => '#ff6b35',
                        'neutralColor' => '#141414',
                        'surfaceColor' => '#ffffff',
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
            assets: ['css' => 'vendor/capell/themes/portfolio-directory.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-portfolio-directory');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-portfolio-directory');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-portfolio-directory::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-portfolio-directory.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-portfolio-directory::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-portfolio-directory::sections.hero', failLoudly: true),
            'directory-hero' => new ViewSectionRenderer(self::THEME_KEY, 'directory-hero', 'capell-theme-portfolio-directory::sections.directory-hero', failLoudly: true),
            'role-filters' => new ViewSectionRenderer(self::THEME_KEY, 'role-filters', 'capell-theme-portfolio-directory::sections.role-filters', failLoudly: true),
            'portfolio-grid' => new ViewSectionRenderer(self::THEME_KEY, 'portfolio-grid', 'capell-theme-portfolio-directory::sections.portfolio-grid', failLoudly: true),
            'resume-resources' => new ViewSectionRenderer(self::THEME_KEY, 'resume-resources', 'capell-theme-portfolio-directory::sections.resume-resources', failLoudly: true),
            'curated-lists' => new ViewSectionRenderer(self::THEME_KEY, 'curated-lists', 'capell-theme-portfolio-directory::sections.curated-lists', failLoudly: true),
            'profile-detail' => new ViewSectionRenderer(self::THEME_KEY, 'profile-detail', 'capell-theme-portfolio-directory::sections.profile-detail', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-portfolio-directory::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-portfolio-directory::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-portfolio-directory::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-portfolio-directory::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-portfolio-directory::sections.footer', failLoudly: true),
        ];
    }
}
