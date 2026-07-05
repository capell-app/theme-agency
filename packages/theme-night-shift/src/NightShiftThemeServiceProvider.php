<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\NightShift;

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
use Capell\ThemeStudio\NightShift\Console\Commands\DemoCommand;
use Capell\ThemeStudio\NightShift\Enums\WidgetComponentEnum;
use Capell\ThemeStudio\NightShift\Support\Interceptors\Themes\NightShiftThemeInterceptor;
use Illuminate\Support\ServiceProvider;
use Override;

final class NightShiftThemeServiceProvider extends ServiceProvider
{
    use RegistersLayoutNativeThemeDefaults;

    public const string THEME_KEY = 'night-shift';

    public static string $packageName = 'capell-app/theme-night-shift';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'Night Shift',
            description: 'Near-black surfaces, refined borders, and product-UI shells for SaaS that works after dark. Roadmaps, changelogs, and security proof — all glowing quietly.',
            package: self::$packageName,
            previewImage: '/vendor/capell/themes/night-shift.jpg',
            tags: ['Dark SaaS', 'Product System', 'Workflows', 'Automation', 'Security'],
            bestFit: ['Product-led SaaS', 'Workflow platforms', 'Technical operations tools', 'AI-assisted work products', 'Team planning systems'],
            presets: [
                new ThemePresetData(
                    key: self::THEME_KEY,
                    name: 'Night Shift',
                    description: 'Night Shift visual preset for near-black surfaces, refined grey borders, soft functional glows, detailed product UI shells, inboxes, issues, roadmaps, reviews, automation, activity, planning, changelog, integrations, customer proof, and security.',
                    previewImage: '/vendor/capell/themes/night-shift.jpg',
                    values: [
                        'primaryColor' => '#07080d',
                        'accentColor' => '#8b9cff',
                        'neutralColor' => '#11131c',
                        'surfaceColor' => '#090a10',
                        'foregroundColor' => '#f4f6ff',
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
                new ThemePresetData(
                    key: 'ember',
                    name: 'Ember',
                    description: 'Ember visual preset for warm charcoal surfaces, molten amber accents, and a denser, high-energy operations console for automation, incident response, and security workflows.',
                    previewImage: '/vendor/capell/themes/night-shift.jpg',
                    values: [
                        'primaryColor' => '#0d0906',
                        'accentColor' => '#ff9d5c',
                        'neutralColor' => '#1c140d',
                        'surfaceColor' => '#0f0a07',
                        'foregroundColor' => '#fdf1e6',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'compact',
                        'alignment' => 'left',
                        'cardStyle' => 'flat',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'energetic',
                        'mediaTreatment' => 'illustrated',
                        'radius' => 'sm',
                        'headingScale' => 'bold',
                        'cardDensity' => 'dense',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/night-shift.css'],
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

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-night-shift');

        // See RegistersLayoutNativeThemeDefaults::registerThemeViewNamespace()'s
        // docblock for why this theme needs both a plain view namespace AND
        // an anonymous-component namespace registered for the same views —
        // mirrors LiquidGlassThemeServiceProvider's identical registration.
        $this->registerThemeViewNamespace('capell-theme-night-shift', __DIR__ . '/../resources/views');

        $this->registerVendorCssAssets();
        $this->registerLayoutAreas();
        $this->registerBespokeWidgetRenderables();
        $this->registerModelInterceptors();

        // Definition-only registration: Night Shift no longer ships a
        // ThemeRenderer or section renderers. Public pages render through
        // the shared `x-capell::layout` + layout-builder container pipeline
        // instead of this package's own page shell, so
        // ThemeRegistry::hasRenderer(self::THEME_KEY) is false from here on.
        $registry->register(definition: self::definition());
    }

    private function registerVendorCssAssets(): void
    {
        CapellCore::registerVendorAsset(new VendorAssetData(
            type: VendorAssetEnum::TailwindImport,
            value: 'resources/css/theme-night-shift.css',
            packageName: self::$packageName,
            condition: 'theme-css:night-shift',
        ));
        CapellCore::registerVendorAsset(VendorAssetData::tailwindSource('resources/views/**/*.blade.php', self::$packageName));
    }

    /**
     * Registers this theme's layout-builder areas: `header` and `footer`,
     * via `RegistersLayoutNativeThemeDefaults::registerStandardLayoutAreas()`,
     * mirroring `LiquidGlassThemeServiceProvider::registerLayoutAreas()`
     * exactly (global scope — omitting `$themeKey` — since these are the
     * same two areas every theme shares, not a Night-Shift-only region).
     *
     * These areas are rendered by this package's own
     * `resources/views/header/index.blade.php` and
     * `resources/views/footer.blade.php`, wired in as the active `Theme`
     * row's `meta.header_file` / `meta.footer_file` (see
     * `x-capell::layout.index`'s `<x-dynamic-component>` fallback).
     *
     * `meta.header_file` / `meta.footer_file` are set by
     * {@see NightShiftThemeInterceptor} whenever a `night-shift`-keyed
     * `Theme` row is created (see {@see registerModelInterceptors()}), not
     * here — this method only makes the `header` / `footer` areas
     * selectable by LayoutAreaRegistry so an admin can place widgets (e.g. a
     * navigation widget) into them, exactly like Foundation's own
     * header/footer areas.
     *
     * NOTE on scope (deliberately deferred, not overlooked): `hero`,
     * `system-hero`, `agents-automation`, `planning-roadmap`, `proof`,
     * `content-listing`, `newsletter`, and `cta` are NOT given bespoke
     * Night Shift treatment in this conversion. Those map to *shared*
     * foundation widget views resolved through the single, global,
     * theme-unaware `Capell\LayoutBuilder\Models\Widget::getComponent()` ->
     * `RenderableRegistry` lookup — there is no per-theme scoping anywhere in
     * that resolution path today. Re-registering those same keys here would
     * silently change hero/features/proof/listing rendering for every other
     * currently-active theme, not just Night Shift; inventing a new
     * theme-scoped override seam inside `Widget::getComponent()` /
     * `RenderableRegistry` is a real, separate, cross-cutting design decision
     * that needs its own review, not something to bolt on inside a single
     * theme's conversion — see `LiquidGlassThemeServiceProvider`'s identical
     * documented scope decision. Night Shift intentionally uses Foundation's
     * shared views verbatim for these sections for now.
     */
    private function registerLayoutAreas(): void
    {
        $this->registerStandardLayoutAreas();
    }

    /**
     * Registers Night Shift's own bespoke layout-builder widget component
     * keys (`capell.widget.night-shift.{changelog-integrations,workflow-rails,security-proof}`)
     * against the shared `RenderableRegistry`, mirroring
     * `LiquidGlassThemeServiceProvider::registerBespokeWidgetRenderables()`.
     *
     * These three keys are new and owned solely by Night Shift — registering
     * brand-new keys here is additive and cannot collide with or change any
     * other theme's rendering.
     *
     * Defensive registration, NOT graceful degradation at render time: each
     * blade target is only registered if `view()->exists()` for it, mirroring
     * the same defensive check Liquid Glass's own registration already uses.
     * This only prevents registering a *dangling* renderable during this
     * package's own boot; it does NOT protect a stale `Widget` row that
     * still references one of these keys after this package's views are
     * removed — see `LiquidGlassThemeServiceProvider::registerBespokeWidgetRenderables()`'s
     * docblock for the full chain (`RenderableRegistry::get()` throws naming
     * the missing key rather than degrading gracefully).
     */
    private function registerBespokeWidgetRenderables(): void
    {
        $registry = resolve(RenderableRegistry::class);

        $blade = [
            WidgetComponentEnum::ChangelogIntegrations->value => 'capell-theme-night-shift::widget.changelog-integrations',
            WidgetComponentEnum::WorkflowRails->value => 'capell-theme-night-shift::widget.workflow-rails',
            WidgetComponentEnum::SecurityProof->value => 'capell-theme-night-shift::widget.security-proof',
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
     * Registers {@see NightShiftThemeInterceptor}, scoped to
     * `self::THEME_KEY` so it only fires for a Theme row keyed
     * `night-shift` — see that class's docblock for why this is scoped
     * (unlike `FoundationThemeInterceptor`, which is unscoped) and for the
     * `header_file` / `footer_file` defaults it sets.
     */
    private function registerModelInterceptors(): void
    {
        CapellCore::registerModelInterceptor(Theme::class, interceptorClass: NightShiftThemeInterceptor::class, key: self::THEME_KEY);
    }
}
