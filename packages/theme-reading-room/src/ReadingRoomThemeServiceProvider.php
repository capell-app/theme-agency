<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\ReadingRoom;

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
use Capell\LayoutBuilder\Support\LayoutAreas\LayoutAreaRegistry;
use Capell\ThemeStudio\ReadingRoom\Console\Commands\ReadingRoomDemoCommand;
use Capell\ThemeStudio\ReadingRoom\Enums\WidgetComponentEnum;
use Capell\ThemeStudio\ReadingRoom\Support\Interceptors\Themes\ReadingRoomThemeInterceptor;
use Illuminate\Support\ServiceProvider;
use Override;

/**
 * Definition-only, layout-native theme provider for the Reading Room
 * docs/knowledge-base theme (Wave 6), modelled on
 * `Capell\ThemeStudio\NightShift\NightShiftThemeServiceProvider`. Registers a
 * `ThemeDefinitionData`, the shared `header`/`footer` layout areas, a
 * brand-new theme-scoped `docs-sidebar` layout area for the doc-tree
 * navigation, and Reading Room's seven bespoke
 * `capell.widget.reading-room.*` layout-builder widget keys. It registers no
 * `ThemeRenderer` or section renderers, so every public surface renders
 * through the shared `x-capell::layout` + layout-builder container pipeline
 * instead of a theme-owned page shell.
 */
final class ReadingRoomThemeServiceProvider extends ServiceProvider
{
    use RegistersLayoutNativeThemeDefaults;

    public const string THEME_KEY = 'reading-room';

    /**
     * The new Layout Builder area this theme introduces (Wave 6, §E) for the
     * doc-tree navigation sidebar rendered by the `doc-tree-sidebar` widget.
     * Registered scoped to `THEME_KEY` via `LayoutAreaRegistry::register()`
     * (the same mechanism `RegistersLayoutNativeThemeDefaults::registerStandardLayoutAreas()`
     * uses for the shared, global `header`/`footer` areas) so it becomes
     * selectable by an admin placing widgets in the Layout Builder for this
     * theme only, without touching any other theme's area options.
     */
    public const string DOCS_SIDEBAR_AREA = 'docs-sidebar';

    public static string $packageName = 'capell-app/theme-reading-room';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Reading Room',
            description: 'A quiet, scannable reading surface for documentation and knowledge-base sites: a collapsible doc-tree sidebar, search-first landing, and serif-option long-form typography built for minimized CTAs.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/reading-room.jpg',
            tags: ['Docs', 'Knowledge Base', 'Developer Docs', 'Scanability', 'Search-first'],
            bestFit: ['Product documentation', 'Developer/API references', 'Internal knowledge bases', 'Help centers', 'Technical handbooks'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Reading Room',
                    description: 'Reading Room visual preset for paper-light surfaces, ink-toned headings, quiet borders, an optional serif reading face, and minimized-CTA doc-tree/search/admonition vocabulary.',
                    previewImage: '/vendor/capell/themes/reading-room.jpg',
                    values: [
                        'primaryColor' => '#1c1917',
                        'accentColor' => '#0f6e5e',
                        'neutralColor' => '#f5f3ef',
                        'surfaceColor' => '#fbfaf7',
                        'foregroundColor' => '#1c1917',
                        'headingFont' => 'source-serif',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'subtle',
                        'navigationStyle' => 'quiet',
                        'layoutPresentation' => 'structured',
                        'motionIntensity' => 'minimal',
                        'mediaTreatment' => 'natural',
                        'radius' => 'sm',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'comfortable',
                    ],
                ),
                new ThemePresetData(
                    key: 'late-edition',
                    name: 'Late Edition',
                    description: 'Late Edition visual preset for Reading Room: a dark, ink-and-paper-at-night reading register with the same serif-option heading face and scanable doc-tree/search/admonition vocabulary.',
                    previewImage: '/vendor/capell/themes/reading-room.jpg',
                    values: [
                        'primaryColor' => '#e7e3da',
                        'accentColor' => '#3ddc97',
                        'neutralColor' => '#17140f',
                        'surfaceColor' => '#100e0a',
                        'foregroundColor' => '#e7e3da',
                        'headingFont' => 'source-serif',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'alignment' => 'left',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'quiet',
                        'layoutPresentation' => 'structured',
                        'motionIntensity' => 'minimal',
                        'mediaTreatment' => 'natural',
                        'radius' => 'sm',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'comfortable',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/reading-room.css'],
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
            $this->commands([ReadingRoomDemoCommand::class]);
        }

        if (! CapellCore::isPackageInstalled(self::$packageName)) {
            return;
        }

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-reading-room');

        $this->registerThemeViewNamespace('capell-theme-reading-room', __DIR__ . '/../resources/views');

        $this->registerVendorCssAssets();
        $this->registerLayoutAreas();
        $this->registerBespokeWidgetRenderables();
        $this->registerModelInterceptors();

        // Definition-only registration: Reading Room ships no ThemeRenderer
        // or section renderers. Public pages render through the shared
        // `x-capell::layout` + layout-builder container pipeline instead of
        // a theme-owned page shell.
        $registry->register(definition: self::definition());
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(new VendorAssetData(
            type: VendorAssetEnum::TailwindImport,
            value: 'resources/css/theme-reading-room.css',
            packageName: self::$packageName,
            condition: 'theme-css:reading-room',
        ));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * Registers the shared, global `header`/`footer` layout areas via
     * `RegistersLayoutNativeThemeDefaults::registerStandardLayoutAreas()`,
     * plus Reading Room's own brand-new `docs-sidebar` area (Wave 6, §E),
     * scoped to `THEME_KEY` so it only becomes a selectable Layout Builder
     * area option for Reading Room pages, not for any other active theme.
     *
     * `LayoutAreaRegistry::register()` accepts an optional `$themeKey` for
     * exactly this purpose (see its own doc comment): a `null`/omitted
     * `$themeKey` registers into the shared global scope every theme sees
     * (used for `header`/`footer` above), while a non-null `$themeKey`
     * registers into that theme's own scope only, layered over the global
     * scope at read time via `LayoutAreaRegistry::areasForTheme()`. This is
     * the existing, generalised mechanism — no changes to
     * `packages/layout-builder` itself were necessary to add a new area.
     *
     * Uses the same `afterResolving`/`resolved` double-registration dance as
     * `registerStandardLayoutAreas()` for boot-order safety.
     */
    private function registerLayoutAreas(): void
    {
        $this->registerStandardLayoutAreas();

        $register = function (LayoutAreaRegistry $registry): void {
            $registry->register(
                self::DOCS_SIDEBAR_AREA,
                __('capell-theme-reading-room::generic.docs_sidebar_area'),
                self::THEME_KEY,
            );
        };

        $this->app->afterResolving(LayoutAreaRegistry::class, $register);

        if ($this->app->resolved(LayoutAreaRegistry::class)) {
            $register($this->app->make(LayoutAreaRegistry::class));
        }
    }

    /**
     * Registers Reading Room's own bespoke layout-builder widget component
     * keys (`capell.widget.reading-room.{doc-tree-sidebar,in-article-toc-scroll-spy,search-spotlight-hero,version-changelog-surfaces,api-reference-parameter-table,callout-admonition-system,feedback-footer}`)
     * against the shared `RenderableRegistry`, mirroring
     * `NightShiftThemeServiceProvider::registerBespokeWidgetRenderables()`.
     *
     * These seven keys are new and owned solely by Reading Room — registering
     * brand-new keys here is additive and cannot collide with or change any
     * other theme's rendering.
     *
     * Defensive registration, NOT graceful degradation at render time: each
     * blade target is only registered if `view()->exists()` for it, mirroring
     * Night Shift's own defensive check.
     */
    private function registerBespokeWidgetRenderables(): void
    {
        $registry = resolve(RenderableRegistry::class);

        $blade = [
            WidgetComponentEnum::DocTreeSidebar->value => 'capell-theme-reading-room::widget.doc-tree-sidebar',
            WidgetComponentEnum::InArticleTocScrollSpy->value => 'capell-theme-reading-room::widget.in-article-toc-scroll-spy',
            WidgetComponentEnum::SearchSpotlightHero->value => 'capell-theme-reading-room::widget.search-spotlight-hero',
            WidgetComponentEnum::VersionChangelogSurfaces->value => 'capell-theme-reading-room::widget.version-changelog-surfaces',
            WidgetComponentEnum::ApiReferenceParameterTable->value => 'capell-theme-reading-room::widget.api-reference-parameter-table',
            WidgetComponentEnum::CalloutAdmonitionSystem->value => 'capell-theme-reading-room::widget.callout-admonition-system',
            WidgetComponentEnum::FeedbackFooter->value => 'capell-theme-reading-room::widget.feedback-footer',
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
     * Registers {@see ReadingRoomThemeInterceptor}, scoped to
     * `self::THEME_KEY` so it only fires for a Theme row keyed
     * `reading-room` — mirrors
     * `NightShiftThemeServiceProvider::registerModelInterceptors()`.
     */
    private function registerModelInterceptors(): void
    {
        CapellCore::registerModelInterceptor(Theme::class, interceptorClass: ReadingRoomThemeInterceptor::class, key: self::THEME_KEY);
    }
}
