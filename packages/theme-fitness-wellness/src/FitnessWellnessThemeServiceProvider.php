<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\FitnessWellness;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\FitnessWellness\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class FitnessWellnessThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'fitness-wellness';

    public static string $packageName = 'capell-app/theme-fitness-wellness';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Fitness & Wellness',
            description: 'A coached strength floor, 40+ studio classes a week, and recovery rooms under one roof in central Leeds. Book a free trial session and feel the difference in a week.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/fitness-wellness.jpg',
            tags: ['Fitness', 'Gym', 'Membership', 'Dark', 'Energetic'],
            bestFit: ['Boutique gyms', 'Fitness studios', 'CrossFit boxes', 'Personal trainers', 'Yoga & wellness studios'],
            includedSections: ['navigation', 'hero', 'features', 'proof', 'content-listing', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Fitness & Wellness',
                    description: 'Fitness & Wellness visual preset for Capell Theme Studio.',
                    previewImage: '/vendor/capell/themes/fitness-wellness.jpg',
                    values: [
                        'primaryColor' => '#84cc16',
                        'accentColor' => '#f97316',
                        'neutralColor' => '#18181b',
                        'surfaceColor' => '#0f1115',
                        'foregroundColor' => '#f4f4f5',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'elevated',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'immersive',
                        'motionIntensity' => 'expressive',
                        'mediaTreatment' => 'framed',
                        'radius' => 'lg',
                        'headingScale' => 'dramatic',
                        'cardDensity' => 'comfortable',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/fitness-wellness.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-fitness-wellness');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-fitness-wellness');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-fitness-wellness::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-fitness-wellness.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-fitness-wellness::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-fitness-wellness::sections.hero', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-fitness-wellness::sections.features', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-fitness-wellness::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-fitness-wellness::sections.content-listing', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-fitness-wellness::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-fitness-wellness::sections.footer', failLoudly: true),
        ];
    }
}
