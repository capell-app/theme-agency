<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Portfolio;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\Portfolio\Console\Commands\DemoCommand;
use Capell\ThemeStudio\Portfolio\Rendering\PackageAwareSectionRenderer;
use Illuminate\Support\ServiceProvider;
use Override;

final class PortfolioThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'portfolio';

    public static string $packageName = 'capell-app/theme-portfolio';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Portfolio',
            description: 'Creator and consultant portfolio theme for work, case studies, services, media kits, and newsletters.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/portfolio.jpg',
            tags: ['Portfolio', 'Case studies', 'Personal brand'],
            bestFit: ['Creators', 'Consultants', 'Independent studios'],
            includedSections: ['navigation', 'hero', 'features', 'proof', 'content-listing', 'work-grid', 'case-studies', 'services', 'testimonials', 'speaking-media-kit', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: 'portfolio',
                    name: 'Portfolio',
                    description: 'Creator and consultant portfolio theme for work, case studies, services, media kits, and newsletters.',
                    previewImage: '/vendor/capell/themes/portfolio.jpg',
                    values: [
                        'primaryColor' => '#7c2d12',
                        'accentColor' => '#f43f5e',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'structured',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'framed',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/portfolio.css'],
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

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-portfolio');

        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindImport('resources/css/theme-portfolio.css', self::$packageName),
        );

        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName),
        );

        $contentSectionsAvailable = CapellCore::isPackageInstalled('capell-app/content-sections');
        $mediaLibraryAvailable = CapellCore::isPackageInstalled('capell-app/media-library');
        $newsletterAvailable = CapellCore::isPackageInstalled('capell-app/newsletter');

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-portfolio::page',
                sectionRenderers: [],
            ),
            sectionRenderers: collect(self::definition()->includedSections)
                ->map(fn (string $sectionKey): ViewSectionRenderer|PackageAwareSectionRenderer|null => $this->sectionRenderer(
                    $sectionKey,
                    $this->optionalSectionIntegrations($contentSectionsAvailable, $mediaLibraryAvailable, $newsletterAvailable),
                ))
                ->filter()
                ->values()
                ->all(),
        );
    }

    /**
     * @param  array<string, array<string, bool>>  $optionalIntegrations
     */
    private function sectionRenderer(string $sectionKey, array $optionalIntegrations): ViewSectionRenderer|PackageAwareSectionRenderer|null
    {
        if ($this->isFoundationSection($sectionKey)) {
            return null;
        }

        $view = 'capell-theme-portfolio::sections.' . $sectionKey;

        if (! view()->exists($view)) {
            return null;
        }

        if (array_key_exists($sectionKey, $optionalIntegrations)) {
            return new PackageAwareSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: $sectionKey,
                view: $view,
                integrations: $optionalIntegrations[$sectionKey],
                failLoudly: true,
            );
        }

        return new ViewSectionRenderer(
            themeKey: self::THEME_KEY,
            sectionKey: $sectionKey,
            view: $view,
            failLoudly: true,
        );
    }

    private function isFoundationSection(string $sectionKey): bool
    {
        return in_array($sectionKey, ['navigation', 'hero', 'features', 'proof', 'content-listing', 'cta', 'footer'], true);
    }

    /**
     * @return array<string, array<string, bool>>
     */
    private function optionalSectionIntegrations(bool $contentSectionsAvailable, bool $mediaLibraryAvailable, bool $newsletterAvailable): array
    {
        return [
            'case-studies' => ['contentSectionsAvailable' => $contentSectionsAvailable],
            'work-grid' => ['mediaLibraryAvailable' => $mediaLibraryAvailable],
            'newsletter' => ['newsletterAvailable' => $newsletterAvailable],
        ];
    }
}
