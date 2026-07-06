<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\MainStage;

use Capell\Core\Data\RenderableDefinitionData;
use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Enums\VendorAssetEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Models\Theme;
use Capell\Core\Support\Renderables\RenderableRegistry;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\FoundationTheme\Support\Editor\StandardThemeEditorSchema;
use Capell\FoundationTheme\Support\Providers\RegistersLayoutNativeThemeDefaults;
use Capell\ThemeStudio\MainStage\Console\Commands\DemoCommand;
use Capell\ThemeStudio\MainStage\Enums\WidgetComponentEnum;
use Capell\ThemeStudio\MainStage\Support\Interceptors\Themes\MainStageThemeInterceptor;
use Illuminate\Support\ServiceProvider;
use Override;

/**
 * Layout-native (definition-only) theme provider for Main Stage, the
 * events-conference vertical theme (Wave 7, Part 2 §E). Registers no
 * `ThemeRenderer` or section renderers — every surface renders through the
 * shared `x-capell::layout` + layout-builder container pipeline, mirroring
 * `NightShiftThemeServiceProvider` and `LiquidGlassThemeServiceProvider`
 * exactly.
 */
final class MainStageThemeServiceProvider extends ServiceProvider
{
    use RegistersLayoutNativeThemeDefaults;

    public const string THEME_KEY = 'main-stage';

    public static string $packageName = 'capell-app/theme-main-stage';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Main Stage',
            description: 'Poster-bold surfaces for conferences and live events: agenda grids, speaker walls, multi-colour ticket tiers, and countdown FOMO built for a single big moment.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/main-stage.jpg',
            tags: ['Events', 'Conference', 'Tickets', 'Agenda', 'Sponsors'],
            bestFit: ['Conferences and summits', 'Multi-track events', 'Ticketed live events', 'Community meetups', 'Hybrid and replay events'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Main Stage',
                    description: 'Main Stage visual preset for a bold poster register: saturated primary accent, oversized display type, and multi-colour ticket-tier ribbons for the agenda, speaker wall, tickets, countdown, venue, sponsors, and archive.',
                    previewImage: '/vendor/capell/themes/main-stage.jpg',
                    values: [
                        'primaryColor' => '#0f0a1f',
                        'accentColor' => '#ff3868',
                        'neutralColor' => '#171025',
                        'surfaceColor' => '#130d21',
                        'foregroundColor' => '#fbf7ff',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'energetic',
                        'mediaTreatment' => 'illustrated',
                        'radius' => 'md',
                        'headingScale' => 'bold',
                        'cardDensity' => 'airy',
                    ],
                ),
                new ThemePresetData(
                    key: 'green-room',
                    name: 'Green Room',
                    description: 'Green Room visual preset for a dark backstage register: near-black surfaces, cooler restrained accent, and a denser production-console feel for run-of-show, speaker ops, and sponsor logistics.',
                    previewImage: '/vendor/capell/themes/main-stage.jpg',
                    values: [
                        'primaryColor' => '#07080b',
                        'accentColor' => '#37e6b0',
                        'neutralColor' => '#0d1013',
                        'surfaceColor' => '#0a0c0f',
                        'foregroundColor' => '#eef6f2',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'compact',
                        'alignment' => 'left',
                        'cardStyle' => 'flat',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'structured',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'illustrated',
                        'radius' => 'sm',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'dense',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/main-stage.css'],
            runtime: FrontendRuntime::Blade,
            extends: 'default',
            frontend: [
                'editor' => StandardThemeEditorSchema::definition(),
            ],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-main-stage');

        $this->registerThemeViewNamespace('capell-theme-main-stage', __DIR__ . '/../resources/views');

        $this->registerVendorCssAssets();
        $this->registerLayoutAreas();
        $this->registerBespokeWidgetRenderables();
        $this->registerModelInterceptors();

        // Definition-only registration: Main Stage ships no ThemeRenderer or
        // section renderers. Public pages render through the shared
        // `x-capell::layout` + layout-builder container pipeline instead of
        // this package's own page shell, so
        // ThemeRegistry::hasRenderer(self::THEME_KEY) is false from here on.
        $registry->register(definition: self::definition());
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(new VendorAssetData(
            type: VendorAssetEnum::TailwindImport,
            value: 'resources/css/theme-main-stage.css',
            packageName: self::$packageName,
            condition: 'theme-css:main-stage',
        ));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * Registers this theme's layout-builder areas: `header` and `footer`,
     * via `RegistersLayoutNativeThemeDefaults::registerStandardLayoutAreas()`,
     * mirroring `NightShiftThemeServiceProvider::registerLayoutAreas()`
     * exactly (global scope — omitting `$themeKey` — since these are the
     * same two areas every theme shares, not a Main-Stage-only region).
     *
     * NOTE on scope (deliberately deferred, not overlooked): only the eight
     * bespoke §E widgets below get Main Stage treatment in this conversion.
     * Foundation's shared `hero`, `proof`, `content-listing`, `cta`, and
     * `newsletter` widget views are used verbatim for now, for the same
     * "no per-theme override seam yet" reason documented at length in
     * `NightShiftThemeServiceProvider::registerLayoutAreas()`.
     */
    private function registerLayoutAreas(): void
    {
        $this->registerStandardLayoutAreas();
    }

    /**
     * Registers Main Stage's own bespoke layout-builder widget component
     * keys (`capell.widget.main-stage.*`) against the shared
     * `RenderableRegistry`, mirroring
     * `NightShiftThemeServiceProvider::registerBespokeWidgetRenderables()`.
     *
     * These eight keys are new and owned solely by Main Stage — registering
     * brand-new keys here is additive and cannot collide with or change any
     * other theme's rendering.
     *
     * Defensive registration, NOT graceful degradation at render time: each
     * blade target is only registered if `view()->exists()` for it.
     */
    private function registerBespokeWidgetRenderables(): void
    {
        $registry = resolve(RenderableRegistry::class);

        $blade = [
            WidgetComponentEnum::AgendaGrid->value => 'capell-theme-main-stage::widget.agenda-grid-days-tracks-rooms',
            WidgetComponentEnum::SpeakerWall->value => 'capell-theme-main-stage::widget.speaker-wall-hover-bios',
            WidgetComponentEnum::TicketTierComparison->value => 'capell-theme-main-stage::widget.ticket-tier-comparison',
            WidgetComponentEnum::CountdownBand->value => 'capell-theme-main-stage::widget.countdown-band',
            WidgetComponentEnum::VenueTravelPanels->value => 'capell-theme-main-stage::widget.venue-travel-panels',
            WidgetComponentEnum::SponsorTierWalls->value => 'capell-theme-main-stage::widget.sponsor-tier-walls',
            WidgetComponentEnum::LiveNowReplayState->value => 'capell-theme-main-stage::widget.live-now-replay-state',
            WidgetComponentEnum::PastEditionsArchive->value => 'capell-theme-main-stage::widget.past-editions-archive',
        ];

        foreach (WidgetComponentEnum::cases() as $widgetComponent) {
            $bladeView = $blade[$widgetComponent->value];

            if (! view()->exists($bladeView)) {
                continue;
            }

            $registry->register(new RenderableDefinitionData(
                key: $widgetComponent->value,
                type: 'layout-widget',
                blade: $bladeView,
            ));
        }
    }

    /**
     * Registers {@see MainStageThemeInterceptor}, scoped to
     * `self::THEME_KEY` so it only fires for a Theme row keyed
     * `main-stage`.
     */
    private function registerModelInterceptors(): void
    {
        CapellCore::registerModelInterceptor(Theme::class, interceptorClass: MainStageThemeInterceptor::class, key: self::THEME_KEY);
    }
}
