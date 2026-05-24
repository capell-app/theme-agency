<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Nonprofit;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\Nonprofit\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class NonprofitThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'nonprofit';

    public static string $packageName = 'capell-app/theme-nonprofit';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Nonprofit',
            description: 'Impact-led civic and charity theme for campaigns, donations, volunteering, and community stories.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/nonprofit.jpg',
            tags: ['Impact', 'Campaigns', 'Donations'],
            bestFit: ['Charities', 'Civic organisations', 'Campaign teams'],
            includedSections: ['navigation', 'hero', 'impact', 'campaigns', 'volunteer-donate', 'events', 'stories', 'contact', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: 'nonprofit',
                    name: 'Nonprofit',
                    description: 'Impact-led civic and charity theme for campaigns, donations, volunteering, and community stories.',
                    previewImage: '/vendor/capell/themes/nonprofit.jpg',
                    values: [
                        'primaryColor' => '#166534',
                        'accentColor' => '#eab308',
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
            assets: ['css' => 'vendor/capell/themes/nonprofit.css'],
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

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-nonprofit');

        CapellCore::registerVendorAsset(new VendorAssetData(
            package: self::$packageName,
            path: 'resources/css/theme-nonprofit.css',
            type: 'css',
        ));

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-nonprofit::page',
                sectionRenderers: [],
            ),
            sectionRenderers: array_map(
                fn (string $sectionKey): ViewSectionRenderer => new ViewSectionRenderer(
                    themeKey: self::THEME_KEY,
                    sectionKey: $sectionKey,
                    view: 'capell-theme-nonprofit::sections.' . $sectionKey,
                ),
                self::definition()->includedSections,
            ),
        );
    }
}
