<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\LocalServices;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\LocalServices\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class LocalServicesThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'local-services';

    public static string $packageName = 'capell-app/theme-local-services';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Local Services',
            description: 'Quote-led service business theme for local operators, trades, clinics, and consultancies.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/local-services.jpg',
            tags: ['Services', 'Local SEO', 'Lead generation'],
            bestFit: ['Service businesses', 'Local operators', 'Quote-led teams'],
            includedSections: ['navigation', 'hero', 'features', 'services', 'service-packages', 'service-areas', 'locality-proof', 'proof', 'reviews-testimonials', 'trust-badges', 'opening-hours', 'structured-data', 'content-listing', 'quote-form', 'quote-estimator', 'case-studies', 'before-after-gallery', 'resources', 'contact', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: 'local-services',
                    name: 'Local Services',
                    description: 'Quote-led service business theme for local operators, trades, clinics, and consultancies.',
                    previewImage: '/vendor/capell/themes/local-services.jpg',
                    values: [
                        'primaryColor' => '#0f766e',
                        'accentColor' => '#f97316',
                        'neutralColor' => '#13231f',
                        'surfaceColor' => '#f7fbf8',
                        'foregroundColor' => '#13231f',
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
            assets: ['css' => 'vendor/capell/themes/local-services.css'],
            runtime: FrontendRuntime::Blade,
            // Theme Studio uses "default" as the runtime inheritance key; capell.json records the Foundation package dependency.
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

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-local-services');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-local-services');
        $this->loadScreenshotFixtureRoutes();

        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindImport('resources/css/theme-local-services.css', self::$packageName),
        );

        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName),
        );

        $blogAvailable = CapellCore::isPackageInstalled('capell-app/blog');
        $bookingsAvailable = CapellCore::isPackageInstalled('capell-app/bookings');
        $formBuilderAvailable = CapellCore::isPackageInstalled('capell-app/form-builder');

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-local-services::page',
                sectionRenderers: [],
            ),
            sectionRenderers: collect(self::definition()->includedSections)
                ->map(fn (string $sectionKey): ?ViewSectionRenderer => $this->sectionRenderer(
                    $sectionKey,
                    $this->optionalSectionIntegrations($blogAvailable, $bookingsAvailable, $formBuilderAvailable),
                ))
                ->filter()
                ->values()
                ->all(),
        );
    }

    private function loadScreenshotFixtureRoutes(): void
    {
        if (filter_var(getenv('CAPELL_THEME_LOCAL_SERVICES_SCREENSHOT_FIXTURES_ENABLED'), FILTER_VALIDATE_BOOL) !== true) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/screenshot-fixtures.php');
    }

    /**
     * @param  array<string, array<string, bool>>  $optionalIntegrations
     */
    private function sectionRenderer(string $sectionKey, array $optionalIntegrations): ?ViewSectionRenderer
    {
        if ($this->isFoundationSection($sectionKey)) {
            return null;
        }

        $view = 'capell-theme-local-services::sections.' . $sectionKey;

        if (! view()->exists($view)) {
            return null;
        }

        if (array_key_exists($sectionKey, $optionalIntegrations)) {
            return new ViewSectionRenderer(
                self::THEME_KEY,
                $sectionKey,
                $view,
                true,
                $optionalIntegrations[$sectionKey],
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
        return in_array($sectionKey, ['navigation', 'footer'], true);
    }

    /**
     * @return array<string, array<string, bool>>
     */
    private function optionalSectionIntegrations(bool $blogAvailable, bool $bookingsAvailable, bool $formBuilderAvailable): array
    {
        return [
            'quote-form' => ['bookingsAvailable' => $bookingsAvailable, 'formBuilderAvailable' => $formBuilderAvailable],
            'quote-estimator' => ['formBuilderAvailable' => $formBuilderAvailable],
            'resources' => ['blogAvailable' => $blogAvailable],
        ];
    }
}
