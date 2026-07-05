<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\GoldRush;

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Enums\VendorAssetEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\FoundationTheme\Rendering\ChromeSplitBladeThemeRenderer;
use Capell\FoundationTheme\Support\Editor\StandardThemeEditorSchema;
use Capell\ThemeStudio\GoldRush\Console\Commands\DemoCommand;
use Illuminate\Support\ServiceProvider;
use Override;

final class GoldRushThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'gold-rush';

    public static string $packageName = 'capell-app/theme-gold-rush';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Gold Rush',
            description: 'Numeric scores, segmented judging, winner-of-the-day, public votes — an awards scoreboard with the tension left in. May the best entry win.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/gold-rush.jpg',
            tags: ['Awards', 'Scoreboard', 'Nominations', 'Judging', 'Showcase'],
            bestFit: ['Design awards sites', 'Nominee showcases', 'Voting galleries', 'Judged portfolio directories', 'Creative rankings'],
            includedSections: ['navigation', 'hero', 'winner-hero', 'score-criteria', 'newest-nominees', 'previous-winners', 'voting-status', 'creator-credits', 'proof', 'content-listing', 'newsletter', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Gold Rush',
                    description: 'Gold Rush visual preset for warm off-white rankings with compact uppercase labels, numeric scores, segmented UI/UX/innovation/overall criteria, winner of the day, newest nominees, previous winners, public voting states, creator credits, and precise score cards.',
                    previewImage: '/vendor/capell/themes/gold-rush.jpg',
                    values: [
                        'primaryColor' => '#241f1b',
                        'accentColor' => '#c2410c',
                        'neutralColor' => '#5f5147',
                        'surfaceColor' => '#fbf3e7',
                        'foregroundColor' => '#241f1b',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'scoreboard',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'score-media',
                        'radius' => 'md',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'compact',
                    ],
                ),
                new ThemePresetData(
                    key: 'nightcast',
                    name: 'Nightcast',
                    description: 'Nightcast visual preset for a cool night-broadcast scoreboard with deep navy surfaces, cyan scoring accents, tightly packed criteria cards, and a charged after-dark jury-room mood.',
                    previewImage: '/vendor/capell/themes/gold-rush.jpg',
                    values: [
                        'primaryColor' => '#e8f3ff',
                        'accentColor' => '#22d3ee',
                        'neutralColor' => '#7d8ba1',
                        'surfaceColor' => '#0b1220',
                        'foregroundColor' => '#e8f3ff',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'snug',
                        'alignment' => 'left',
                        'cardStyle' => 'scoreboard',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'energetic',
                        'mediaTreatment' => 'score-media',
                        'radius' => 'sm',
                        'headingScale' => 'compact',
                        'cardDensity' => 'compact',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/gold-rush.css'],
            runtime: FrontendRuntime::Blade,
            frontend: [
                'editor' => StandardThemeEditorSchema::definition(),
            ],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-gold-rush');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-gold-rush');
        $this->registerVendorCssAssets();

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new ChromeSplitBladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-gold-rush::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(new VendorAssetData(
            type: VendorAssetEnum::TailwindImport,
            value: 'resources/css/theme-gold-rush.css',
            packageName: self::$packageName,
            condition: 'theme-css:gold-rush',
        ));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * @return array<string, SectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-gold-rush::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-gold-rush::sections.hero', failLoudly: true),
            'winner-hero' => new ViewSectionRenderer(self::THEME_KEY, 'winner-hero', 'capell-theme-gold-rush::sections.winner-hero', failLoudly: true),
            'score-criteria' => new ViewSectionRenderer(self::THEME_KEY, 'score-criteria', 'capell-theme-gold-rush::sections.score-criteria', failLoudly: true),
            'newest-nominees' => new ViewSectionRenderer(self::THEME_KEY, 'newest-nominees', 'capell-theme-gold-rush::sections.newest-nominees', failLoudly: true),
            'previous-winners' => new ViewSectionRenderer(self::THEME_KEY, 'previous-winners', 'capell-theme-gold-rush::sections.previous-winners', failLoudly: true),
            'voting-status' => new ViewSectionRenderer(self::THEME_KEY, 'voting-status', 'capell-theme-gold-rush::sections.voting-status', failLoudly: true),
            'creator-credits' => new ViewSectionRenderer(self::THEME_KEY, 'creator-credits', 'capell-theme-gold-rush::sections.creator-credits', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-gold-rush::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-gold-rush::sections.content-listing', failLoudly: true),
            'newsletter' => new ViewSectionRenderer(self::THEME_KEY, 'newsletter', 'capell-theme-gold-rush::sections.newsletter', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-gold-rush::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-gold-rush::sections.footer', failLoudly: true),
        ];
    }
}
