<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Knowledge;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\Knowledge\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class KnowledgeThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'knowledge';

    public static string $packageName = 'capell-app/theme-knowledge';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Knowledge',
            description: 'Editorial and resource-library theme for knowledge bases, publishers, and content-led teams.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/knowledge.jpg',
            tags: ['Editorial', 'Resources', 'Search'],
            bestFit: ['Knowledge bases', 'Resource hubs', 'Content teams'],
            includedSections: ['navigation', 'hero', 'topic-hubs', 'featured-content', 'resource-library', 'search-listing', 'newsletter', 'authors', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: 'knowledge',
                    name: 'Knowledge',
                    description: 'Editorial and resource-library theme for knowledge bases, publishers, and content-led teams.',
                    previewImage: '/vendor/capell/themes/knowledge.jpg',
                    values: [
                        'primaryColor' => '#1d4ed8',
                        'accentColor' => '#f59e0b',
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
            assets: ['css' => 'vendor/capell/themes/knowledge.css'],
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

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-knowledge');

        CapellCore::registerVendorAsset(new VendorAssetData(
            package: self::$packageName,
            path: 'resources/css/theme-knowledge.css',
            type: 'css',
        ));

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-knowledge::page',
                sectionRenderers: [],
            ),
            sectionRenderers: array_map(
                fn (string $sectionKey): ViewSectionRenderer => new ViewSectionRenderer(
                    themeKey: self::THEME_KEY,
                    sectionKey: $sectionKey,
                    view: 'capell-theme-knowledge::sections.' . $sectionKey,
                ),
                self::definition()->includedSections,
            ),
        );
    }
}
