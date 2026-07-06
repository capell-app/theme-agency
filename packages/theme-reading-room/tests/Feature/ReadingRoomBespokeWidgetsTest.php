<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\Core\Models\Layout;
use Capell\Core\Models\Page;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Theme;
use Capell\Core\Support\Renderables\RenderableRegistry;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\FoundationTheme\Data\ThemeDemoInstallData;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;
use Capell\LayoutBuilder\Models\Widget;
use Capell\LayoutBuilder\Support\Creator\WidgetCreator;
use Capell\LayoutBuilder\Support\LayoutAreas\LayoutAreaRegistry;
use Capell\ThemeStudio\ReadingRoom\Enums\WidgetComponentEnum;
use Capell\ThemeStudio\ReadingRoom\ReadingRoomThemeServiceProvider;
use Capell\ThemeStudio\ReadingRoom\Support\Demo\ReadingRoomDemoContent;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;

use function Pest\Laravel\get;

/*
|--------------------------------------------------------------------------
| Reading Room bespoke widgets, docs-sidebar area, header/footer chrome seam
|--------------------------------------------------------------------------
|
| Mirrors NightShiftBespokeWidgetsTest's structure for the layout-native
| pattern, extended with a docs-sidebar Layout Builder area assertion (Wave
| 6, §E) this theme introduces.
|
*/

function bootReadingRoomThemeForBespokeWidgetTests(): void
{
    CapellCore::forcePackageInstalled(ReadingRoomThemeServiceProvider::$packageName);

    View::addNamespace('capell-theme-reading-room', dirname(__DIR__, 2) . '/resources/views');
    Lang::addNamespace('capell-theme-reading-room', dirname(__DIR__, 2) . '/resources/lang');

    $registry = resolve(ThemeRegistry::class);
    $provider = new ReadingRoomThemeServiceProvider(app());
    $provider->register();
    $provider->boot($registry);
}

it('registers the brand-new docs-sidebar layout area scoped to reading-room only', function (): void {
    bootReadingRoomThemeForBespokeWidgetTests();

    $areaRegistry = resolve(LayoutAreaRegistry::class);

    expect($areaRegistry->options(ReadingRoomThemeServiceProvider::THEME_KEY))
        ->toHaveKey(ReadingRoomThemeServiceProvider::DOCS_SIDEBAR_AREA);

    // Not a global area: another theme's own area options must not see it.
    expect($areaRegistry->options('night-shift'))
        ->not->toHaveKey(ReadingRoomThemeServiceProvider::DOCS_SIDEBAR_AREA);

    CapellCore::clearPackages();
});

it('sets header_file and footer_file defaults on a seeded Reading Room Theme via ReadingRoomThemeInterceptor', function (): void {
    bootReadingRoomThemeForBespokeWidgetTests();

    ThemeDemoPageInstaller::run(
        data: new ThemeDemoInstallData(
            siteNames: ['Reading Room Chrome Test'],
            languageCodes: ['en'],
            baseUrl: 'https://reading-room.chrome-test.test',
        ),
        themeKey: ReadingRoomThemeServiceProvider::THEME_KEY,
        themeName: 'Reading Room',
        contentProvider: new ReadingRoomDemoContent,
    );

    $theme = Theme::query()->where('key', ReadingRoomThemeServiceProvider::THEME_KEY)->firstOrFail();

    expect($theme->meta['header_file'] ?? null)->toBe('capell-theme-reading-room::header.index')
        ->and($theme->meta['footer_file'] ?? null)->toBe('capell-theme-reading-room::footer');

    CapellCore::clearPackages();
    resolve(ThemeRegistry::class)->reset();
});

it('renders real header and footer chrome on the seeded homepage through the header_file/footer_file seam', function (): void {
    bootReadingRoomThemeForBespokeWidgetTests();

    ThemeDemoPageInstaller::run(
        data: new ThemeDemoInstallData(
            siteNames: ['Reading Room Chrome Render Test'],
            languageCodes: ['en'],
            baseUrl: 'https://reading-room.chrome-render-test.test',
        ),
        themeKey: ReadingRoomThemeServiceProvider::THEME_KEY,
        themeName: 'Reading Room',
        contentProvider: new ReadingRoomDemoContent,
    );

    $homepage = Page::query()
        ->where('meta->theme_demo->theme_key', ReadingRoomThemeServiceProvider::THEME_KEY)
        ->where('meta->theme_demo->surface', 'homepage')
        ->firstOrFail();

    $homepage->loadMissing(['pageUrl.siteDomain', 'translations']);

    $pageUrl = $homepage->pageUrl;

    throw_unless($pageUrl instanceof PageUrl, RuntimeException::class, 'Expected the seeded homepage to have a PageUrl.');

    $response = get($pageUrl->full_url);

    $response->assertOk();

    $html = $response->getContent();

    expect($html)->toBeString();

    expect($html)
        ->toContain('<header')
        ->toContain('<footer')
        ->toContain('id="main-content"')
        ->toContain('Reading Room Chrome Render Test')
        ->not->toContain('capell-app/theme-reading-room')
        ->not->toContain('authoring');

    CapellCore::clearPackages();
    resolve(ThemeRegistry::class)->reset();
});

it('renders each bespoke widget view with real seeded copy and expected content fragments', function (): void {
    bootReadingRoomThemeForBespokeWidgetTests();

    $searchWidget = resolve(WidgetCreator::class)->bespokeContentWidget(
        key: 'reading-room-search-spotlight-hero-render-test-1',
        name: 'Search hero render test',
        component: WidgetComponentEnum::SearchSpotlightHero->value,
        meta: [
            'variant' => 'full',
            'heading' => 'Find what you need, fast',
            'summary' => 'Search the whole library.',
            'quickLinks' => [['tag' => 'Guide', 'label' => 'Getting started', 'url' => '#getting-started']],
        ],
    );

    $treeWidget = resolve(WidgetCreator::class)->bespokeContentWidget(
        key: 'reading-room-doc-tree-sidebar-render-test-1',
        name: 'Doc tree render test',
        component: WidgetComponentEnum::DocTreeSidebar->value,
        meta: [
            'variant' => 'nested',
            'heading' => 'Documentation',
            'tree' => [['label' => 'Getting started', 'url' => '#getting-started']],
        ],
    );

    $admonitionWidget = resolve(WidgetCreator::class)->bespokeContentWidget(
        key: 'reading-room-callout-admonition-system-render-test-1',
        name: 'Admonitions render test',
        component: WidgetComponentEnum::CalloutAdmonitionSystem->value,
        meta: [
            'variant' => 'block',
            'admonitions' => [['kind' => 'tip', 'body' => 'Search accepts partial matches.']],
        ],
    );

    $searchHtml = view('capell-theme-reading-room::widget.search-spotlight-hero', ['widget' => $searchWidget])->render();
    $treeHtml = view('capell-theme-reading-room::widget.doc-tree-sidebar', ['widget' => $treeWidget])->render();
    $admonitionHtml = view('capell-theme-reading-room::widget.callout-admonition-system', ['widget' => $admonitionWidget])->render();

    expect($searchHtml)
        ->toContain('Find what you need, fast')
        ->toContain('Getting started')
        ->not->toContain('capell-app/theme-reading-room');

    expect($treeHtml)
        ->toContain('Getting started')
        ->not->toContain('capell-app/theme-reading-room');

    expect($admonitionHtml)
        ->toContain('Search accepts partial matches.')
        ->not->toContain('capell-app/theme-reading-room');

    CapellCore::clearPackages();
});

it('throws a clear error naming the missing key when a widget references an unregistered bespoke component', function (): void {
    bootReadingRoomThemeForBespokeWidgetTests();

    $registry = resolve(RenderableRegistry::class);

    expect($registry->get('layout-widget', WidgetComponentEnum::DocTreeSidebar->value)->blade)
        ->toBe('capell-theme-reading-room::widget.doc-tree-sidebar');

    expect(fn () => $registry->get('layout-widget', 'capell.widget.reading-room.does-not-exist'))
        ->toThrow(InvalidArgumentException::class, 'Renderable [capell.widget.reading-room.does-not-exist] of type [layout-widget] is not registered.');

    CapellCore::clearPackages();
});

it('seeds real Layout containers and a page-content Widget through ThemeDemoPageInstaller', function (): void {
    bootReadingRoomThemeForBespokeWidgetTests();

    ThemeDemoPageInstaller::run(
        data: new ThemeDemoInstallData(
            siteNames: ['Reading Room Definition Test'],
            languageCodes: ['en'],
            baseUrl: 'https://reading-room.definition-test.test',
        ),
        themeKey: ReadingRoomThemeServiceProvider::THEME_KEY,
        themeName: 'Reading Room',
        contentProvider: new ReadingRoomDemoContent,
    );

    $homepage = Page::query()
        ->where('meta->theme_demo->theme_key', ReadingRoomThemeServiceProvider::THEME_KEY)
        ->where('meta->theme_demo->surface', 'homepage')
        ->firstOrFail();

    $layout = $homepage->layout;

    throw_unless($layout instanceof Layout, RuntimeException::class, 'Expected the seeded homepage to have a Layout.');

    $containers = $layout->containers;
    throw_unless(is_array($containers), RuntimeException::class, 'Expected the seeded Layout to carry a containers array.');

    $mainWidgets = data_get($containers, 'main.widgets', []);
    throw_unless(is_array($mainWidgets), RuntimeException::class, 'Expected the main container to carry a widgets array.');

    $mainWidgetKeys = array_column($mainWidgets, 'widget_key');

    expect($mainWidgetKeys)->toContain('page-content')
        ->toContain('reading-room-search-spotlight-hero-homepage-1')
        ->toContain('reading-room-doc-tree-sidebar-homepage-1')
        ->toContain('reading-room-callout-admonition-system-homepage-1')
        ->toContain('reading-room-version-changelog-surfaces-homepage-1');

    $searchWidget = Widget::query()->where('key', 'reading-room-search-spotlight-hero-homepage-1')->firstOrFail();

    expect($searchWidget->component)->toBe('capell.widget.reading-room.search-spotlight-hero')
        ->and($searchWidget->meta['heading'] ?? null)->toBe('Find what you need, fast');

    CapellCore::clearPackages();
    resolve(ThemeRegistry::class)->reset();
});

it('creates a bespoke content widget through WidgetCreator with caller-supplied key, component, and meta', function (): void {
    bootReadingRoomThemeForBespokeWidgetTests();

    $widget = resolve(WidgetCreator::class)->bespokeContentWidget(
        key: 'reading-room-version-changelog-surfaces-standalone-test-1',
        name: 'Standalone changelog test',
        component: WidgetComponentEnum::VersionChangelogSurfaces->value,
        meta: ['heading' => 'Test heading', 'entries' => []],
    );

    expect($widget)->toBeInstanceOf(Widget::class)
        ->and($widget->key)->toBe('reading-room-version-changelog-surfaces-standalone-test-1')
        ->and($widget->component)->toBe(WidgetComponentEnum::VersionChangelogSurfaces->value)
        ->and($widget->meta['heading'] ?? null)->toBe('Test heading');

    CapellCore::clearPackages();
});
