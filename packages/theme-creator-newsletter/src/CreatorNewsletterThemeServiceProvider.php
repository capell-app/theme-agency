<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\CreatorNewsletter;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\CreatorNewsletter\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class CreatorNewsletterThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'creator-newsletter';

    public static string $packageName = 'capell-app/theme-creator-newsletter';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Creator Newsletter',
            description: 'Creator Newsletter gives Capell sites a focused frontend theme with portable content, safe public output, and theme-specific visual tokens.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/creator-newsletter.jpg',
            tags: ['Newsletter', 'Creator', 'Subscribe', 'Warm', 'Conversion'],
            bestFit: ['Newsletter creators', 'Solo media brands', 'Independent writers', 'Audience-first creators'],
            includedSections: ['navigation', 'subscribe-hero', 'features', 'archive', 'testimonials', 'sponsors', 'about-author', 'content-listing', 'cta', 'footer', 'hero', 'proof'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Creator Newsletter',
                    description: 'Creator Newsletter visual preset for Capell Theme Studio.',
                    previewImage: '/vendor/capell/themes/creator-newsletter.jpg',
                    values: [
                        'primaryColor' => '#e11d48',
                        'accentColor' => '#7c3aed',
                        'neutralColor' => '#2a1620',
                        'surfaceColor' => '#fff8f6',
                        'foregroundColor' => '#2a1620',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'alignment' => 'center',
                        'cardStyle' => 'elevated',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'structured',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'flat',
                        'radius' => 'xl',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'comfortable',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/creator-newsletter.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-creator-newsletter');
        $this->loadScreenshotFixtureRoutes();
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-creator-newsletter');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-creator-newsletter::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function loadScreenshotFixtureRoutes(): void
    {
        if (filter_var(getenv('CAPELL_THEME_CREATOR_NEWSLETTER_SCREENSHOT_FIXTURES_ENABLED'), FILTER_VALIDATE_BOOL) !== true) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/screenshot-fixtures.php');
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-creator-newsletter.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-creator-newsletter::sections.navigation', failLoudly: true),
            'subscribe-hero' => new ViewSectionRenderer(self::THEME_KEY, 'subscribe-hero', 'capell-theme-creator-newsletter::sections.subscribe-hero', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-creator-newsletter::sections.features', failLoudly: true),
            'archive' => new ViewSectionRenderer(self::THEME_KEY, 'archive', 'capell-theme-creator-newsletter::sections.archive', failLoudly: true),
            'testimonials' => new ViewSectionRenderer(self::THEME_KEY, 'testimonials', 'capell-theme-creator-newsletter::sections.testimonials', failLoudly: true),
            'sponsors' => new ViewSectionRenderer(self::THEME_KEY, 'sponsors', 'capell-theme-creator-newsletter::sections.sponsors', failLoudly: true),
            'about-author' => new ViewSectionRenderer(self::THEME_KEY, 'about-author', 'capell-theme-creator-newsletter::sections.about-author', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-creator-newsletter::sections.content-listing', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-creator-newsletter::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-creator-newsletter::sections.footer', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-creator-newsletter::sections.hero', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-creator-newsletter::sections.proof', failLoudly: true),
        ];
    }
}
