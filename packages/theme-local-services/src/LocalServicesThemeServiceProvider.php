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
            includedSections: ['navigation', 'hero', 'services', 'service-areas', 'proof', 'quote-form', 'case-studies', 'resources', 'contact', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: 'local-services',
                    name: 'Local Services',
                    description: 'Quote-led service business theme for local operators, trades, clinics, and consultancies.',
                    previewImage: '/vendor/capell/themes/local-services.jpg',
                    values: [
                        'primaryColor' => '#0f766e',
                        'accentColor' => '#f97316',
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
            assets: ['css' => 'vendor/capell/themes/local-services.css'],
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

        if (! CapellCore::hasPackage(self::$packageName)) {
            return;
        }

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-local-services');

        CapellCore::registerVendorAsset(new VendorAssetData(
            package: self::$packageName,
            path: 'resources/css/theme-local-services.css',
            type: 'css',
        ));

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-local-services::page',
                sectionRenderers: [],
            ),
            sectionRenderers: array_map(
                fn (string $sectionKey): ViewSectionRenderer => new ViewSectionRenderer(
                    themeKey: self::THEME_KEY,
                    sectionKey: $sectionKey,
                    view: 'capell-theme-local-services::sections.' . $sectionKey,
                ),
                self::definition()->includedSections,
            ),
        );
    }
}
