<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\Core\Models\Page;
use Capell\Core\Models\Theme;
use Capell\Core\Support\Renderables\RenderableRegistry;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\LayoutBuilder\Models\Widget;
use Capell\LayoutBuilder\Support\Creator\WidgetCreator;
use Capell\ThemeStudio\LiquidGlass\Enums\WidgetComponentEnum;
use Capell\ThemeStudio\LiquidGlass\LiquidGlassThemeServiceProvider;
use Capell\ThemeStudio\LiquidGlass\Support\Demo\LiquidGlassDemoContent;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;

use function Pest\Laravel\get;

/*
|--------------------------------------------------------------------------
| Liquid Glass bespoke widgets, header/footer chrome seam (task C2)
|--------------------------------------------------------------------------
|
| Unlike LiquidGlassVisualProofTest, this file does NOT call
| layoutNativeDisableThemeChrome() — the whole point here is to exercise the
| real header/footer chrome path (LiquidGlassThemeInterceptor's
| meta.header_file / meta.footer_file defaults, consumed by
| x-capell::layout.index's <x-dynamic-component> fallback), which that
| helper deliberately disables for unrelated reasons (see its docblock).
|
*/

function bootLiquidGlassThemeForBespokeWidgetTests(): void
{
    CapellCore::forcePackageInstalled(LiquidGlassThemeServiceProvider::$packageName);

    View::addNamespace('capell-theme-liquid-glass', dirname(__DIR__, 2) . '/resources/views');
    Lang::addNamespace('capell-theme-liquid-glass', dirname(__DIR__, 2) . '/resources/lang');

    $registry = resolve(ThemeRegistry::class);
    $provider = new LiquidGlassThemeServiceProvider(app());
    $provider->register();
    $provider->boot($registry);
}

it('sets header_file and footer_file defaults on a seeded Liquid Glass Theme via LiquidGlassThemeInterceptor', function (): void {
    bootLiquidGlassThemeForBespokeWidgetTests();

    ThemeDemoPageInstaller::run(
        data: new ThemeDemoInstallData(
            siteNames: ['Liquid Glass Chrome Test'],
            languageCodes: ['en'],
            baseUrl: 'https://liquid-glass.chrome-test.test',
        ),
        themeKey: LiquidGlassThemeServiceProvider::THEME_KEY,
        themeName: 'Liquid Glass',
        contentProvider: new LiquidGlassDemoContent,
    );

    $theme = Theme::query()->where('key', LiquidGlassThemeServiceProvider::THEME_KEY)->firstOrFail();

    expect($theme->meta['header_file'] ?? null)->toBe('capell-theme-liquid-glass::header.index')
        ->and($theme->meta['footer_file'] ?? null)->toBe('capell-theme-liquid-glass::footer');

    CapellCore::clearPackages();
    resolve(ThemeRegistry::class)->reset();
});

/*
 * NOTE: this only asserts header/footer chrome, not layout-builder widget
 * content, in the real HTTP response. A pre-existing, unrelated gap in
 * `Capell\Frontend\Support\Render\BladeFrontendResponseRenderer::layoutBuilderSlot()`
 * (confirmed independently of this task — reproduces with the existing
 * `layoutNativeThemeCreatePage()` helper too, for the shared `page-content`
 * widget, not just Liquid Glass's bespoke ones) means layout-builder
 * container widgets do not currently render at all through a real,
 * non-Livewire HTTP hit for ANY layout-native theme — flagged separately for
 * a dedicated fix rather than worked around here. Widget rendering itself
 * (real seeded copy through the real Blade view) is proven directly below
 * instead, by rendering each bespoke widget's view with a real `Widget`
 * model.
 */
it('renders real header and footer chrome on the seeded homepage through the header_file/footer_file seam', function (): void {
    bootLiquidGlassThemeForBespokeWidgetTests();

    ThemeDemoPageInstaller::run(
        data: new ThemeDemoInstallData(
            siteNames: ['Liquid Glass Chrome Render Test'],
            languageCodes: ['en'],
            baseUrl: 'https://liquid-glass.chrome-render-test.test',
        ),
        themeKey: LiquidGlassThemeServiceProvider::THEME_KEY,
        themeName: 'Liquid Glass',
        contentProvider: new LiquidGlassDemoContent,
    );

    $homepage = Page::query()
        ->where('meta->theme_demo->theme_key', LiquidGlassThemeServiceProvider::THEME_KEY)
        ->where('meta->theme_demo->surface', 'homepage')
        ->firstOrFail();

    $homepage->loadMissing(['pageUrl.siteDomain', 'translations']);

    $response = get($homepage->pageUrl->full_url);

    $response->assertOk();

    $html = $response->getContent();

    expect($html)->toBeString();

    // Header/footer chrome rendered through the real header_file/footer_file seam
    // (LiquidGlassThemeInterceptor -> Theme::meta -> x-capell::layout.index's
    // <x-dynamic-component> fallback -> this package's own header/footer views).
    expect($html)
        ->toContain('<header')
        ->toContain('<footer')
        ->toContain('id="main-content"')
        ->toContain('Liquid Glass Chrome Render Test')
        ->not->toContain('capell-app/theme-liquid-glass')
        ->not->toContain('authoring');

    CapellCore::clearPackages();
    resolve(ThemeRegistry::class)->reset();
});

it('renders each bespoke widget view with real seeded copy and expected content fragments', function (): void {
    bootLiquidGlassThemeForBespokeWidgetTests();

    $demoContent = new LiquidGlassDemoContent;
    $homepageCopy = $demoContent->sectionCopy('homepage');
    $showcaseSection = collect($homepageCopy)->firstWhere('type', 'showcase');
    $presetsSection = collect($homepageCopy)->firstWhere('type', 'presets');
    $ctaSection = collect($homepageCopy)->firstWhere('type', 'cta');

    $ctaWidget = resolve(WidgetCreator::class)->bespokeContentWidget(
        key: 'liquid-glass-cta-render-test-1',
        name: 'CTA render test',
        component: WidgetComponentEnum::Cta->value,
        meta: $ctaSection,
    );
    $showcaseWidget = resolve(WidgetCreator::class)->bespokeContentWidget(
        key: 'liquid-glass-showcase-render-test-1',
        name: 'Showcase render test',
        component: WidgetComponentEnum::Showcase->value,
        meta: $showcaseSection,
    );
    $presetsWidget = resolve(WidgetCreator::class)->bespokeContentWidget(
        key: 'liquid-glass-presets-render-test-1',
        name: 'Presets render test',
        component: WidgetComponentEnum::Presets->value,
        meta: $presetsSection,
    );

    $ctaHtml = view('capell-theme-liquid-glass::widget.cta', ['widget' => $ctaWidget])->render();
    $showcaseHtml = view('capell-theme-liquid-glass::widget.showcase', ['widget' => $showcaseWidget])->render();
    $presetsHtml = view('capell-theme-liquid-glass::widget.presets', ['widget' => $presetsWidget])->render();

    expect($ctaHtml)
        ->toContain('Bring your pages onto the glass')
        ->toContain('A warm, focused call to action that stays native to the translucent theme.')
        ->not->toContain('capell-app/theme-liquid-glass');

    expect($showcaseHtml)
        ->toContain('Teams that ship on the glass')
        ->toContain('Marlow Studio launch')
        ->not->toContain('capell-app/theme-liquid-glass');

    expect($presetsHtml)
        ->toContain('One glass system, three token-driven presets')
        ->toContain('Clarity')
        ->toContain('Bright product launches')
        ->not->toContain('capell-app/theme-liquid-glass');

    CapellCore::clearPackages();
});

it('creates distinctly-keyed cta widgets per surface so copy does not clobber across surfaces', function (): void {
    bootLiquidGlassThemeForBespokeWidgetTests();

    ThemeDemoPageInstaller::run(
        data: new ThemeDemoInstallData(
            siteNames: ['Liquid Glass Widget Keys Test'],
            languageCodes: ['en'],
            baseUrl: 'https://liquid-glass.widget-keys-test.test',
        ),
        themeKey: LiquidGlassThemeServiceProvider::THEME_KEY,
        themeName: 'Liquid Glass',
        contentProvider: new LiquidGlassDemoContent,
    );

    $homepageCta = Widget::query()->firstWhere('key', 'liquid-glass-cta-homepage-1');
    $contactCta = Widget::query()->firstWhere('key', 'liquid-glass-cta-contact-1');

    expect($homepageCta)->toBeInstanceOf(Widget::class)
        ->and($contactCta)->toBeInstanceOf(Widget::class)
        ->and($homepageCta->meta['heading'] ?? null)->toBe('Bring your pages onto the glass')
        ->and($contactCta->meta['heading'] ?? null)->toBe('Bring your pages onto the glass')
        ->and($homepageCta->getKey())->not->toBe($contactCta->getKey());

    // Both surfaces render the same recovered copy today (see sectionCopy()),
    // but through genuinely distinct Widget rows, not a shared singleton —
    // proven by their independent primary keys above.
    CapellCore::clearPackages();
    resolve(ThemeRegistry::class)->reset();
});

it('throws a clear error naming the missing key when a widget references an unregistered bespoke component', function (): void {
    bootLiquidGlassThemeForBespokeWidgetTests();

    $registry = resolve(RenderableRegistry::class);

    // The real, currently-shipped views resolve fine.
    expect($registry->get('layout-widget', WidgetComponentEnum::Cta->value)->blade)
        ->toBe('capell-theme-liquid-glass::widget.cta');

    // Simulate a stale reference to a component whose view genuinely does
    // not exist (e.g. the theme package's views were partially removed, or
    // the theme was switched away in a way that stripped its view
    // namespace): registerBespokeWidgetRenderables()'s view()->exists()
    // guard prevents this package from ever registering such a key, so
    // RenderableRegistry::get() throws — naming the missing key and type in
    // its message — rather than returning a dangling reference. This is NOT
    // graceful degradation: a real stale `Widget` row pointing at this key
    // would hit this same throw via `Widget::getComponent()` ->
    // `ResolveRenderableComponentAction`, and `container.blade.php` has no
    // catch for it, so the public page 500s. See
    // `LiquidGlassThemeServiceProvider::registerBespokeWidgetRenderables()`'s
    // docblock for the full chain.
    expect(fn () => $registry->get('layout-widget', 'capell.widget.liquid-glass.does-not-exist'))
        ->toThrow(InvalidArgumentException::class, 'Renderable [capell.widget.liquid-glass.does-not-exist] of type [layout-widget] is not registered.');

    CapellCore::clearPackages();
});

it('creates a bespoke content widget through WidgetCreator with caller-supplied key, component, and meta', function (): void {
    bootLiquidGlassThemeForBespokeWidgetTests();

    $widget = resolve(WidgetCreator::class)->bespokeContentWidget(
        key: 'liquid-glass-cta-standalone-test-1',
        name: 'Standalone CTA test',
        component: WidgetComponentEnum::Cta->value,
        meta: ['heading' => 'Test heading', 'summary' => 'Test summary'],
    );

    expect($widget)->toBeInstanceOf(Widget::class)
        ->and($widget->key)->toBe('liquid-glass-cta-standalone-test-1')
        ->and($widget->component)->toBe(WidgetComponentEnum::Cta->value)
        ->and($widget->meta['heading'] ?? null)->toBe('Test heading')
        ->and($widget->meta['summary'] ?? null)->toBe('Test summary');

    CapellCore::clearPackages();
});
