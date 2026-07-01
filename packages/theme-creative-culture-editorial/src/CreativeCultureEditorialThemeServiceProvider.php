<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\CreativeCultureEditorial;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\CreativeCultureEditorial\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class CreativeCultureEditorialThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'creative-culture-editorial';

    public static string $packageName = 'capell-app/theme-creative-culture-editorial';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Creative Culture Editorial',
            description: 'Creative culture editorial theme for art-directed projects, opinion, advice, culture stories, must-reads, events, popular tags, discipline browsing, and newsletter conversion.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/creative-culture-editorial.jpg',
            tags: ['Creative Editorial', 'Culture', 'Projects', 'Opinion', 'Events'],
            bestFit: ['Creative magazines', 'Design journals', 'Culture publishers', 'Arts organisations', 'Studio blogs'],
            includedSections: ['navigation', 'hero', 'must-reads', 'discipline-browsing', 'project-stories', 'opinion-block', 'events-tags', 'advice-culture', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Creative Culture Editorial',
                    description: 'Creative Culture Editorial visual preset for warm art direction, bold category labels, varied image-led story cards, quote-led opinion blocks, event/tag modules, discipline browsing, and newsletters.',
                    previewImage: '/vendor/capell/themes/creative-culture-editorial.jpg',
                    values: [
                        'primaryColor' => '#111111',
                        'accentColor' => '#e4512f',
                        'neutralColor' => '#201719',
                        'surfaceColor' => '#fff8ec',
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
            assets: ['css' => 'vendor/capell/themes/creative-culture-editorial.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-creative-culture-editorial');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-creative-culture-editorial');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-creative-culture-editorial::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(VendorAssetData::tailwindImport('resources/css/theme-creative-culture-editorial.css', self::$packageName));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-creative-culture-editorial::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-creative-culture-editorial::sections.hero', failLoudly: true),
            'must-reads' => new ViewSectionRenderer(self::THEME_KEY, 'must-reads', 'capell-theme-creative-culture-editorial::sections.must-reads', failLoudly: true),
            'discipline-browsing' => new ViewSectionRenderer(self::THEME_KEY, 'discipline-browsing', 'capell-theme-creative-culture-editorial::sections.discipline-browsing', failLoudly: true),
            'project-stories' => new ViewSectionRenderer(self::THEME_KEY, 'project-stories', 'capell-theme-creative-culture-editorial::sections.project-stories', failLoudly: true),
            'opinion-block' => new ViewSectionRenderer(self::THEME_KEY, 'opinion-block', 'capell-theme-creative-culture-editorial::sections.opinion-block', failLoudly: true),
            'events-tags' => new ViewSectionRenderer(self::THEME_KEY, 'events-tags', 'capell-theme-creative-culture-editorial::sections.events-tags', failLoudly: true),
            'advice-culture' => new ViewSectionRenderer(self::THEME_KEY, 'advice-culture', 'capell-theme-creative-culture-editorial::sections.advice-culture', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-creative-culture-editorial::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-creative-culture-editorial::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-creative-culture-editorial::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-creative-culture-editorial::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-creative-culture-editorial::sections.footer', failLoudly: true),
        ];
    }
}
