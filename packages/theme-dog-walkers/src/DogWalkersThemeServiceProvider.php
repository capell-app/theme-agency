<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\DogWalkers;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\DogWalkers\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class DogWalkersThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'dog-walkers';

    public static string $packageName = 'capell-app/theme-dog-walkers';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Dog Walkers',
            description: 'Warm, trust-led pet care theme for independent dog walkers, sitters, and small neighbourhood teams.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/dog-walkers.jpg',
            tags: ['Pet care', 'Dog walking', 'Local services'],
            bestFit: ['Dog walkers', 'Pet sitters', 'Neighbourhood pet-care teams'],
            includedSections: ['navigation', 'hero', 'features', 'walk-options', 'route-board', 'safety-checklist', 'service-areas', 'meet-the-walkers', 'proof', 'reviews-testimonials', 'opening-hours', 'faq', 'structured-data', 'content-listing', 'resources', 'enquiry-form', 'contact', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: 'dog-walkers',
                    name: 'Dog Walkers',
                    description: 'Neighbourhood pet-care presentation for reliable walks, calm handoffs, local coverage, and enquiry conversion.',
                    previewImage: '/vendor/capell/themes/dog-walkers.jpg',
                    values: [
                        'primaryColor' => '#0f5f4a',
                        'accentColor' => '#e86f4b',
                        'neutralColor' => '#17342c',
                        'surfaceColor' => '#f4fbf6',
                        'foregroundColor' => '#13231f',
                        'headingFont' => 'sora',
                        'bodyFont' => 'ibm-plex-sans',
                        'spacing' => 'balanced',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'structured',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'editorial',
                        'radius' => 'md',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'compact',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/dog-walkers.css'],
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

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-dog-walkers');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-dog-walkers');

        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindImport('resources/css/theme-dog-walkers.css', self::$packageName),
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
                layoutView: 'capell-theme-dog-walkers::page',
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

    /**
     * @param  array<string, array<string, bool>>  $optionalIntegrations
     */
    private function sectionRenderer(string $sectionKey, array $optionalIntegrations): ?ViewSectionRenderer
    {
        if ($this->isFoundationSection($sectionKey)) {
            return new ViewSectionRenderer(
                themeKey: self::THEME_KEY,
                sectionKey: $sectionKey,
                view: 'capell-foundation-theme::theme.chrome.' . $sectionKey,
                failLoudly: true,
            );
        }

        $view = 'capell-theme-dog-walkers::sections.' . $sectionKey;

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
            'enquiry-form' => ['bookingsAvailable' => $bookingsAvailable, 'formBuilderAvailable' => $formBuilderAvailable],
            'faq' => ['formBuilderAvailable' => $formBuilderAvailable],
            'resources' => ['blogAvailable' => $blogAvailable],
        ];
    }
}
