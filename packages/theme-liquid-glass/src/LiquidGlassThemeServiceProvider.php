<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\LiquidGlass;

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
use Capell\ThemeStudio\LiquidGlass\Console\Commands\DemoCommand;
use Capell\ThemeStudio\LiquidGlass\Enums\WidgetComponentEnum;
use Capell\ThemeStudio\LiquidGlass\Support\Interceptors\Themes\LiquidGlassThemeInterceptor;
use Illuminate\Support\ServiceProvider;
use Override;

class LiquidGlassThemeServiceProvider extends ServiceProvider
{
    use RegistersLayoutNativeThemeDefaults;

    public const string THEME_KEY = 'liquid-glass';

    public const string PUBLIC_PREVIEW_IMAGE = '/vendor/capell/themes/liquid-glass.svg';

    public static string $packageName = 'capell-app/theme-liquid-glass';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Liquid Glass',
            description: 'A free modern glass interface with translucent panels, crisp content rhythm, and warm accent actions.',
            package: self::$packageName,
            previewImage: self::PUBLIC_PREVIEW_IMAGE,
            tags: ['Glass', 'Modern', 'Launch'],
            bestFit: ['Modern service sites', 'Product launches', 'Design-led teams'],
            presets: [
                new ThemePresetData(
                    key: 'clarity',
                    name: 'Clarity',
                    description: 'Bright glass surfaces, teal structure, and warm action accents.',
                    previewImage: self::PUBLIC_PREVIEW_IMAGE,
                    values: [
                        'primaryColor' => '#0f766e',
                        'accentColor' => '#f97316',
                        'neutralColor' => '#24313a',
                        'surfaceColor' => '#f3fbfa',
                        'foregroundColor' => '#10202a',
                        'headingFont' => 'manrope',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'cardStyle' => 'elevated',
                        'navigationStyle' => 'standard',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'framed',
                        'radius' => 'xl',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'comfortable',
                        'overlayTreatment' => 'subtle',
                    ],
                ),
                new ThemePresetData(
                    key: 'prism',
                    name: 'Prism',
                    description: 'Fresh cyan glass, rose highlights, and spacious editorial cards.',
                    previewImage: self::PUBLIC_PREVIEW_IMAGE,
                    values: [
                        'primaryColor' => '#0e7490',
                        'accentColor' => '#db2777',
                        'neutralColor' => '#1f2933',
                        'surfaceColor' => '#edf7fb',
                        'foregroundColor' => '#111827',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'spacious',
                        'cardStyle' => 'layered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'immersive',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'framed',
                        'radius' => 'xl',
                        'headingScale' => 'expressive',
                        'cardDensity' => 'spacious',
                        'overlayTreatment' => 'subtle',
                    ],
                ),
                new ThemePresetData(
                    key: 'graphite',
                    name: 'Graphite',
                    description: 'Dark glass panels with amber contrast and compact information density.',
                    previewImage: self::PUBLIC_PREVIEW_IMAGE,
                    values: [
                        'primaryColor' => '#334155',
                        'accentColor' => '#f59e0b',
                        'neutralColor' => '#111827',
                        'surfaceColor' => '#151917',
                        'foregroundColor' => '#f7faf7',
                        'headingFont' => 'manrope',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'minimal',
                        'layoutPresentation' => 'structured',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'framed',
                        'radius' => 'lg',
                        'headingScale' => 'compact',
                        'cardDensity' => 'compact',
                        'overlayTreatment' => 'strong',
                    ],
                ),
            ],
            frontend: [
                'editor' => StandardThemeEditorSchema::definition(),
            ],
            assets: ['css' => 'vendor/capell/themes/liquid-glass.css'],
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

            $this->publishes([
                __DIR__ . '/../docs/assets/marketplace/extension-card.svg' => public_path(ltrim(self::PUBLIC_PREVIEW_IMAGE, '/')),
            ], 'capell-theme-liquid-glass-assets');
        }

        if (! CapellCore::isPackageInstalled(self::$packageName)) {
            return;
        }

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-liquid-glass');

        // See RegistersLayoutNativeThemeDefaults::registerThemeViewNamespace()'s
        // docblock for why this theme needs both a plain view namespace AND
        // an anonymous-component namespace registered for the same views —
        // in short, the `Theme::meta.header_file` / `footer_file`
        // chrome-override seam (documented further below, at
        // registerLayoutAreas()'s docblock) resolves through
        // `<x-dynamic-component>`, which only consults the component
        // namespace, not the plain view namespace.
        $this->registerThemeViewNamespace('capell-theme-liquid-glass', __DIR__ . '/../resources/views');

        $this->registerVendorCssAssets();
        $this->registerLayoutAreas();
        $this->registerBespokeWidgetRenderables();
        $this->registerModelInterceptors();

        // Definition-only registration: Liquid Glass no longer ships a
        // ThemeRenderer or section renderers. Public pages render through
        // the shared `x-capell::layout` + layout-builder container pipeline
        // instead of this package's own page shell, so
        // ThemeRegistry::hasRenderer(self::THEME_KEY) is false from here on.
        $registry->register(definition: self::definition());
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(
            new VendorAssetData(
                type: VendorAssetEnum::TailwindImport,
                value: 'resources/css/theme-liquid-glass.css',
                packageName: self::$packageName,
                condition: 'theme-css:liquid-glass',
            ),
        );

        CapellCore::registerVendorAsset(
            VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName),
        );
    }

    /**
     * Registers this theme's layout-builder areas: `header` and `footer`,
     * via `RegistersLayoutNativeThemeDefaults::registerStandardLayoutAreas()`,
     * which mirrors `FoundationThemeServiceProvider::registerLayoutAreas()`
     * exactly (global scope — omitting `$themeKey` — since these are the
     * same two areas every theme shares, not a Liquid-Glass-only region).
     *
     * These areas are rendered by this package's own
     * `resources/views/header/index.blade.php` and
     * `resources/views/footer.blade.php`, wired in as the active `Theme`
     * row's `meta.header_file` / `meta.footer_file` (see
     * `x-capell::layout.index`'s `<x-dynamic-component>` fallback — the
     * real, already-built per-theme chrome override seam, not a Blade
     * view-chain override of the `capell::header.index` / `capell::footer.index`
     * component tags, which are registered as *class-aliased* components
     * (`Blade::component(...)`) and therefore cannot be overridden by
     * placing a same-named view earlier in a namespace's view-chain: Laravel's
     * `ComponentTagCompiler::componentClass()` resolves a registered class
     * alias unconditionally, before any anonymous-view/namespace guessing
     * ever runs).
     *
     * `meta.header_file` / `meta.footer_file` are set by
     * {@see LiquidGlassThemeInterceptor} whenever a `liquid-glass`-keyed
     * `Theme` row is created (see {@see registerModelInterceptors()}), not
     * here — this method only makes the `header` / `footer` areas
     * selectable by LayoutAreaRegistry so an admin can place widgets (e.g. a
     * navigation widget) into them, exactly like Foundation's own
     * header/footer areas.
     *
     * NOTE on scope (deliberately deferred, not overlooked): the `hero`,
     * `features`, `proof`, and `content-listing` sections are NOT given
     * bespoke Liquid Glass treatment in this pilot. Those four map to
     * *shared* foundation widget views (`capell.widget.hero`,
     * `capell.widget.asset.features`, `capell.widget.asset.testimonials`,
     * `capell.widget.page.latest`) resolved through the single, global,
     * theme-unaware `Capell\LayoutBuilder\Models\Widget::getComponent()` ->
     * `RenderableRegistry` lookup — there is no per-theme scoping anywhere in
     * that resolution path today. Re-registering those same keys here would
     * silently change hero/features/testimonials/latest-pages rendering for
     * every other currently-active theme, not just Liquid Glass; inventing a
     * new theme-scoped override seam inside `Widget::getComponent()` /
     * `RenderableRegistry` is a real, separate, cross-cutting design decision
     * that needs its own review, not something to bolt on inside a single
     * theme's pilot conversion. Liquid Glass intentionally uses Foundation's
     * shared views verbatim for these four sections for now. Raise per-theme
     * widget-view override support explicitly before the next themes in this
     * program need bespoke hero/feature/proof/listing treatment of their own.
     */
    private function registerLayoutAreas(): void
    {
        $this->registerStandardLayoutAreas();
    }

    /**
     * Registers Liquid Glass's own bespoke layout-builder widget component
     * keys (`capell.widget.liquid-glass.{cta,showcase,presets,glass-feature-card,
     * translucent-stat-band,layered-depth-hero,refraction-grid,floating-glass-nav}`)
     * against the shared `RenderableRegistry`, mirroring the established
     * pattern `Capell\Blog\Providers\BlogServiceProvider::registerWidgetRenderables()`
     * and `Capell\LayoutBuilder\Support\LayoutBuilderCoreRegistrar` already
     * use for their own widget component enums. The last five keys are Wave
     * 4c's (§D "glassmorphism showcase") signature widgets, added on top of
     * the original three.
     *
     * These keys are new and owned solely by Liquid Glass — unlike the
     * shared `capell.widget.hero` / `asset.features` / `asset.testimonials` /
     * `page.latest` keys documented in {@see registerLayoutAreas()}'s
     * "NOTE on scope", registering brand-new keys here is additive and
     * cannot collide with or change any other theme's rendering.
     *
     * Defensive registration, NOT graceful degradation at render time: each
     * blade target is only registered if `view()->exists()` for it,
     * mirroring the same defensive check
     * `Capell\ContentSections\Support\SectionPublicLayoutWidgetPayloadContributor::renderSection()`
     * already uses before trusting a dynamically-resolved component view.
     * This only prevents registering a *dangling* renderable (one whose
     * blade view does not exist) during this package's own boot.
     *
     * It does NOT protect a stale `Widget` row that still references one of
     * these keys after this package's views are removed (e.g. mid-uninstall,
     * or after a downgrade). That row's `Widget::getComponent()` call goes
     * through `Capell\Core\Actions\Renderables\ResolveRenderableComponentAction`
     * to `RenderableRegistry::get()`, which throws `InvalidArgumentException`
     * naming the missing key when nothing was ever registered for it.
     * `Capell\LayoutBuilder`'s `container.blade.php` has no catch for this —
     * its `if (! $component) continue;` null-skip is unreachable via this
     * path, since `getComponent()` never returns null on a miss; it throws
     * first. In practice a stale bespoke-widget key therefore causes a 500 on
     * the public page, not a silent skip. Changing that shared
     * throw-vs-catch-and-skip behaviour in `RenderableRegistry` /
     * `container.blade.php` is a separate, cross-cutting layout-builder
     * decision affecting every widget type, not just this theme's three
     * bespoke ones — out of scope here.
     */
    private function registerBespokeWidgetRenderables(): void
    {
        $registry = resolve(RenderableRegistry::class);

        $blade = [
            WidgetComponentEnum::Cta->value => 'capell-theme-liquid-glass::widget.cta',
            WidgetComponentEnum::Showcase->value => 'capell-theme-liquid-glass::widget.showcase',
            WidgetComponentEnum::Presets->value => 'capell-theme-liquid-glass::widget.presets',
            WidgetComponentEnum::GlassFeatureCard->value => 'capell-theme-liquid-glass::widget.glass-feature-card',
            WidgetComponentEnum::TranslucentStatBand->value => 'capell-theme-liquid-glass::widget.translucent-stat-band',
            WidgetComponentEnum::LayeredDepthHero->value => 'capell-theme-liquid-glass::widget.layered-depth-hero',
            WidgetComponentEnum::RefractionGrid->value => 'capell-theme-liquid-glass::widget.refraction-grid',
            WidgetComponentEnum::FloatingGlassNav->value => 'capell-theme-liquid-glass::widget.floating-glass-nav',
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
     * Registers {@see LiquidGlassThemeInterceptor}, scoped to
     * `self::THEME_KEY` so it only fires for a Theme row keyed
     * `liquid-glass` — see that class's docblock for why this is scoped
     * (unlike `FoundationThemeInterceptor`, which is unscoped) and for the
     * `header_file` / `footer_file` defaults it sets.
     */
    private function registerModelInterceptors(): void
    {
        CapellCore::registerModelInterceptor(Theme::class, interceptorClass: LiquidGlassThemeInterceptor::class, key: self::THEME_KEY);
    }
}
