<?php

declare(strict_types=1);

use Capell\Core\Data\PackageData;
use Capell\Core\Enums\PackageTypeEnum;
use Capell\Core\Events\PackageInstalled;
use Capell\Core\Events\PackageUninstalled;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Models\Blueprint;
use Capell\Core\Models\Language;
use Capell\Core\Models\Layout;
use Capell\Core\Models\Media;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\Core\Models\SiteDomain;
use Capell\Core\Models\Theme;
use Capell\Core\Support\Tailwind\TailwindAssetsRegistry;
use Capell\FoundationTheme\Actions\BlockIsSlotAction;
use Capell\FoundationTheme\Actions\BuildBannerImageRenderDataAction;
use Capell\FoundationTheme\Actions\BuildLayoutNeighborLinksDataAction;
use Capell\FoundationTheme\Actions\MarkPrimaryHeadingRenderedAction;
use Capell\FoundationTheme\Actions\ResolveLoadedLayoutContainerBackgroundImageAction;
use Capell\FoundationTheme\Console\Commands\GenerateTailwindAssetsCommand;
use Capell\FoundationTheme\Filament\Settings\FoundationThemeSettingsSchema;
use Capell\FoundationTheme\Listeners\RunTailwindAssetsOnPackageChange;
use Capell\FoundationTheme\Livewire\Assets\Table\PageAssets;
use Capell\FoundationTheme\Livewire\Block\Pages as LivewirePages;
use Capell\FoundationTheme\Providers\AdminServiceProvider;
use Capell\FoundationTheme\Providers\FoundationThemeServiceProvider;
use Capell\FoundationTheme\Settings\FoundationThemeSettings;
use Capell\FoundationTheme\Settings\FoundationThemeSettingsMigrationProvider;
use Capell\FoundationTheme\Support\Blade\BladeDirectives;
use Capell\FoundationTheme\Support\Interceptors\Themes\FoundationThemeInterceptor;
use Capell\FoundationTheme\Support\Media\CapellUrlGenerator;
use Capell\FoundationTheme\Support\Tailwind\TailwindAssetsGenerator;
use Capell\FoundationTheme\View\Components\Actions as ActionsComponent;
use Capell\FoundationTheme\View\Components\Block\Asset;
use Capell\FoundationTheme\View\Components\Block\Asset\Accordion;
use Capell\FoundationTheme\View\Components\Block\Asset\Carousel;
use Capell\FoundationTheme\View\Components\Block\Navigation;
use Capell\FoundationTheme\View\Components\Block\Page\Children;
use Capell\FoundationTheme\View\Components\Block\Page\Content;
use Capell\FoundationTheme\View\Components\Block\Page\Latest;
use Capell\FoundationTheme\View\Components\Block\Page\Pages;
use Capell\FoundationTheme\View\Components\Block\Page\Siblings;
use Capell\FoundationTheme\View\Components\Footer\Index as FooterIndex;
use Capell\FoundationTheme\View\Components\Footer\LatestPages;
use Capell\FoundationTheme\View\Components\Layout\Index;
use Capell\FoundationTheme\View\Components\Layout\Main;
use Capell\Frontend\Support\State\FrontendState;
use Capell\LayoutBuilder\Models\Widget;
use Capell\LayoutBuilder\Support\Livewire\OpaqueBlockReference;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

it('generates tailwind assets from configured app sources and packages', function (): void {
    $targetPath = sys_get_temp_dir() . '/capell-foundation-theme-coverage/frontend.css';

    config([
        'capell-foundation-theme.tailwind' => [
            'imports' => ['@tailwindcss/forms'],
            'plugins' => ['@tailwindcss/typography'],
            'sources' => ['resources/views/**/*.blade.php'],
            'validate_sources' => false,
        ],
    ]);

    $generated = (new TailwindAssetsGenerator(new Filesystem))->generate($targetPath);
    $css = (string) file_get_contents($targetPath);

    capell_expect($generated)->toBe([$targetPath])
        ->and($css)->toContain('@import "tailwindcss";')
        ->and($css)->toContain('@import "@tailwindcss/forms";')
        ->and($css)->toContain('@plugin "@tailwindcss/typography";')
        ->and($css)->toContain('@source "');
});

it('registers foundation theme provider runtime services and package boot hooks', function (): void {
    config([
        'capell-foundation-theme.blaze.enabled' => false,
        'capell-foundation-theme.npm_dependencies' => [
            '' => '^1.0',
            'invalid-version' => '',
            123 => '^1.0',
            'swiper' => '^11.0',
        ],
    ]);

    CapellCore::forcePackageInstalled(FoundationThemeServiceProvider::$packageName);

    $provider = new FoundationThemeServiceProvider(app());
    $provider->packageRegistered();
    $provider->packageBooted();

    capell_expect(resolve('capell.tailwind.generator'))->toBeInstanceOf(TailwindAssetsGenerator::class)
        ->and(config('media-library.url_generator'))->toBe(CapellUrlGenerator::class);
});

it('collects default tailwind assets without writing files', function (): void {
    config([
        'capell-foundation-theme.tailwind.output_css' => 'resources/css/capell/frontend.css',
        'capell-foundation-theme.tailwind.imports' => ['swiper/css'],
        'capell-foundation-theme.tailwind.plugins' => ['@tailwindcss/forms'],
        'capell-foundation-theme.tailwind.sources' => ['resources/views/**/*.blade.php'],
    ]);

    $registry = (new TailwindAssetsGenerator(new Filesystem))->collect();

    capell_expect($registry)->toBeInstanceOf(TailwindAssetsRegistry::class)
        ->and($registry->imports())->toContain('swiper/css')
        ->and($registry->plugins())->toContain('@tailwindcss/forms')
        ->and($registry->sources())->not->toBeEmpty()
        ->and($registry->themeColors())->not->toBeEmpty();
});

it('runs foundation tailwind command report generate and package-change listener paths', function (): void {
    $targetPath = sys_get_temp_dir() . '/capell-foundation-theme-command/frontend.css';

    $this->artisan(GenerateTailwindAssetsCommand::class, ['--report' => true])
        ->assertSuccessful()
        ->expectsOutputToContain('Tailwind assets report:');

    $this->artisan(GenerateTailwindAssetsCommand::class, ['--output-path' => $targetPath])
        ->assertSuccessful()
        ->expectsOutputToContain('Generated Tailwind assets at');

    $package = new PackageData(
        name: FoundationThemeServiceProvider::$packageName,
        type: PackageTypeEnum::Theme,
        serviceProviderClass: FoundationThemeServiceProvider::class,
        path: __DIR__,
    );
    $listener = new RunTailwindAssetsOnPackageChange;
    $listener->handleInstalled(new PackageInstalled($package));
    $listener->handleUninstalled(new PackageUninstalled($package));
});

it('builds banner image render data for empty and rounded blocks', function (): void {
    $block = new Widget;
    $block->meta = ['actions' => [['label' => 'Start']]];

    $data = BuildBannerImageRenderDataAction::run(
        block: $block,
        content: '',
        title: '',
        rounded: true,
        reverseOrder: true,
    );

    capell_expect($data->backgroundImage)->toBeNull()
        ->and($data->actions)->toBe([['label' => 'Start']])
        ->and($data->hasContent)->toBeTrue()
        ->and($data->imageRoundedClass)->toBe(' rounded-r-lg');
});

it('declares foundation settings schema and settings migrations', function (): void {
    $components = FoundationThemeSettingsSchema::make(Schema::make());
    $performanceComponents = foundationThemeCoverageChildComponents(foundationThemeCoverageChildComponents($components[0])[0]);
    $designTokenComponents = foundationThemeCoverageChildComponents(foundationThemeCoverageChildComponents($components[1])[0]);
    $provider = new FoundationThemeSettingsMigrationProvider;

    capell_expect($components)->toHaveCount(2)
        ->and($components[0])->toBeInstanceOf(Section::class)
        ->and($components[1])->toBeInstanceOf(Section::class)
        ->and($performanceComponents)->toHaveCount(2)
        ->and($performanceComponents[0])->toBeInstanceOf(Checkbox::class)
        ->and($performanceComponents[1])->toBeInstanceOf(Checkbox::class)
        ->and($designTokenComponents)->toHaveCount(14)
        ->and($designTokenComponents[0])->toBeInstanceOf(ColorPicker::class)
        ->and($designTokenComponents[11])->toBeInstanceOf(ColorPicker::class)
        ->and($designTokenComponents[12])->toBeInstanceOf(Select::class)
        ->and($designTokenComponents[13])->toBeInstanceOf(Select::class)
        ->and($provider->getSettingMigrations())->toBe([
            '2026_05_10_190850_01_create_foundation_theme_settings',
            '2026_05_23_160819_add_foundation_theme_design_tokens',
            '2026_05_23_161002_refresh_foundation_theme_design_token_defaults',
            '2026_05_23_170001_add_foundation_theme_composition_tokens',
            '2026_05_23_171201_quiet_foundation_theme_composition_palette',
        ])
        ->and($provider->migrations())->toBe([
            '2026_05_10_190850_01_create_foundation_theme_settings',
            '2026_05_23_160819_add_foundation_theme_design_tokens',
            '2026_05_23_161002_refresh_foundation_theme_design_token_defaults',
            '2026_05_23_170001_add_foundation_theme_composition_tokens',
            '2026_05_23_171201_quiet_foundation_theme_composition_palette',
        ])
        ->and(FoundationThemeSettings::group())->toBe('foundation_theme')
        ->and(FoundationThemeSettings::schema())->toBe(FoundationThemeSettingsSchema::class)
        ->and(FoundationThemeSettings::sectionSpacingCssValueFor(null))->toBe(FoundationThemeSettings::SECTION_SPACING_OPTIONS['relaxed'])
        ->and(FoundationThemeSettings::widgetGapCssValueFor(null))->toBe(FoundationThemeSettings::WIDGET_GAP_OPTIONS['balanced']);

    (new AdminServiceProvider(app()))->register();
});

it('compiles foundation blade directives across build tools and buffer expressions', function (): void {
    BladeDirectives::register();

    config(['capell-foundation-theme.asset_build_tool' => 'asset']);

    $assetHtml = Blade::render('@buildAssets(["frontend.css", "frontend.js"], "vendor/capell")');
    $buffer = Blade::render(<<<'BLADE'
@capellBuffer($renderCard, string $title, array $attributes = ['data-value' => 'a,b'])
<span>{{ $title }} {{ $attributes['data-value'] }}</span>
@endcapellBuffer
{{ $renderCard('Hello') }}
BLADE);

    capell_expect($assetHtml)
        ->toContain('<link rel="stylesheet" href="http://localhost/vendor/capell/frontend.css">')
        ->toContain('<script src="http://localhost/vendor/capell/frontend.js"></script>')
        ->and($buffer)->toContain('<span>Hello a,b</span>');
});

it('throws for buffer directives without a target variable', function (): void {
    BladeDirectives::register();

    Blade::compileString('@capellBuffer()');
})->throws(InvalidArgumentException::class, 'The @capellBuffer directive requires a target variable.');

it('fills foundation theme defaults without overwriting existing theme meta', function (): void {
    $data = (new FoundationThemeInterceptor)->beforeCreate([
        'meta' => [
            'footer_spacing' => 'relaxed',
            'sticky_header' => false,
        ],
    ]);

    capell_expect($data['meta'])
        ->toHaveKey('assets', ['resources/css/capell/frontend.css'])
        ->toHaveKey('assets_path', 'build')
        ->toHaveKey('footer_spacing', 'relaxed')
        ->toHaveKey('sticky_header', false)
        ->toHaveKey('dark_mode_toggle', true);
});

it('covers small foundation layout helper actions', function (): void {
    $slotBlock = new Widget;
    $slotBlock->meta = ['type' => 'slot', 'name' => 'Sidebar'];

    $plainBlock = new Widget;
    $plainBlock->meta = [];

    MarkPrimaryHeadingRenderedAction::run();

    capell_expect(BlockIsSlotAction::run($slotBlock))->toBeTrue()
        ->and(BlockIsSlotAction::run($plainBlock))->toBeFalse();
});

it('skips empty asset blocks without touching frontend context', function (string $componentClass): void {
    config(['capell-layout-builder.block.skip_render_empty' => true]);

    $block = new Widget;
    $block->setRelation('assets', collect());

    $component = new $componentClass(
        container: [],
        containerKey: 'main',
        blockIndex: 0,
        loop: new stdClass,
        block: $block,
    );

    capell_expect($component->render())->toBe('');
})->with([
    'asset' => [Asset::class],
    'carousel' => [Carousel::class],
    'accordion' => [Accordion::class],
]);

it('reports latest footer pages when explicit pages are provided', function (): void {
    $emptyComponent = new LatestPages(headingClass: 'font-semibold', pages: collect());
    $filledComponent = new LatestPages(headingClass: 'font-semibold', pages: collect([new Page]));

    capell_expect($emptyComponent->hasPages())->toBeFalse()
        ->and($filledComponent->hasPages())->toBeTrue()
        ->and($filledComponent->render()->name())->toBe('capell::components.footer.latest-pages');
});

it('does not build neighbor links when page meta disables them', function (): void {
    $page = new Page;
    $page->meta = ['with_next_prev' => false];

    $neighbors = BuildLayoutNeighborLinksDataAction::run($page, new Site, new Language);

    capell_expect($neighbors->previousPage)->toBeNull()
        ->and($neighbors->nextPage)->toBeNull()
        ->and($neighbors->shouldRender())->toBeFalse();
});

it('resolves loaded layout container background images defensively', function (): void {
    $layout = new Layout;
    $media = new Media;
    $media->collection_name = 'main-background';

    capell_expect(ResolveLoadedLayoutContainerBackgroundImageAction::run($layout, 'main'))->toBeNull();

    $layout->setRelation('media', 'not-a-collection');

    capell_expect(ResolveLoadedLayoutContainerBackgroundImageAction::run($layout, 'main'))->toBeNull();

    $layout->setRelation('media', collect([$media]));

    capell_expect(ResolveLoadedLayoutContainerBackgroundImageAction::run($layout, 'main'))->toBe($media)
        ->and(ResolveLoadedLayoutContainerBackgroundImageAction::run($layout, 'sidebar'))->toBeNull();
});

it('rewrites media urls to the active frontend root or configured site base', function (): void {
    Storage::fake('public');
    config([
        'media-library.version_urls' => false,
        'capell-foundation-theme.use_site_domain_for_media' => true,
        'capell-foundation-theme.site_base_url' => 'https://cdn.example.test',
        'capell-foundation-theme.local_storage_url' => '',
    ]);

    $media = new Spatie\MediaLibrary\MediaCollections\Models\Media;
    $media->disk = 'public';
    $media->conversions_disk = 'public';
    $media->file_name = 'hero image.jpg';
    $media->setAttribute('updated_at', now());

    $pathGenerator = new class implements PathGenerator
    {
        public function getPath(Spatie\MediaLibrary\MediaCollections\Models\Media $media): string
        {
            return 'media/';
        }

        public function getPathForConversions(Spatie\MediaLibrary\MediaCollections\Models\Media $media): string
        {
            return 'media/conversions/';
        }

        public function getPathForResponsiveImages(Spatie\MediaLibrary\MediaCollections\Models\Media $media): string
        {
            return 'media/responsive';
        }
    };

    $generator = (new CapellUrlGenerator(resolve(Repository::class)))
        ->setMedia($media)
        ->setPathGenerator($pathGenerator);

    capell_expect($generator->getUrl())->toContain('https://cdn.example.test')
        ->and($generator->getResponsiveImagesDirectoryUrl())->toBe('https://cdn.example.test/storage/media/responsive/');

    $domain = new SiteDomain([
        'scheme' => 'https',
        'domain' => 'active.example.test',
    ]);
    resolve(FrontendState::class)->withDomain($domain);

    capell_expect($generator->getUrl())->toContain('https://active.example.test');
});

it('skips empty navigation and page listing blocks without public markup', function (string $componentClass): void {
    [$language, $site, $theme, $layout, $page] = foundationThemeCoverageFrontendContext();
    $hiddenType = new Blueprint;
    $hiddenType->meta = ['hidden' => true];

    $page->setRelation('type', $hiddenType);
    $page->setRelation('layout', $layout);

    resolve(FrontendState::class)
        ->withLanguage($language)
        ->withSite($site)
        ->withTheme($theme)
        ->withLayout($layout)
        ->withPage($page);

    $block = Widget::factory()->create([
        'key' => 'coverage-block',
        'meta' => [],
    ]);
    $block->setRelation('assets', new EloquentCollection);

    $component = new $componentClass(
        container: [],
        containerKey: 'main',
        blockIndex: 0,
        loop: new stdClass,
        block: $block,
    );

    capell_expect($component->render())->toBe('');
})->with([
    'navigation' => [Navigation::class],
    'children' => [Children::class],
    'siblings' => [Siblings::class],
    'latest' => [Latest::class],
    'pages' => [Pages::class],
]);

it('renders page content and layout components from frontend context', function (): void {
    [$language, $site, $theme, $layout, $page] = foundationThemeCoverageFrontendContext([
        'with_next_prev' => false,
        'final_cta' => ['label' => 'Start'],
    ]);

    resolve(FrontendState::class)
        ->withLanguage($language)
        ->withSite($site)
        ->withTheme($theme)
        ->withLayout($layout)
        ->withPage($page);

    $block = Widget::factory()->create([
        'key' => 'content-block',
        'meta' => [],
    ]);
    $block->setRelation('assets', new EloquentCollection);

    $content = new Content(
        container: [],
        containerKey: 'main',
        blockIndex: 0,
        loop: new stdClass,
        block: $block,
    );
    $index = new Index;
    $main = new Main(
        layout: $layout,
        page: $page,
        theme: ['container' => 'wide'],
    );

    capell_expect($content->previousPage)->toBeNull()
        ->and($content->nextPage)->toBeNull()
        ->and($content->render()->name())->toBe('capell-foundation-theme::components.block.page.content')
        ->and($index->render()->name())->toBe('capell::components.layout.index')
        ->and($index->isSystemPageLayout)->toBeFalse()
        ->and($main->render()->name())->toBe('capell::components.layout.main')
        ->and($main->finalCta)->toBe(['label' => 'Start']);
});

it('resolves action component links and public action payloads', function (): void {
    [$language, $site, $theme, $layout, $page] = foundationThemeCoverageFrontendContext();

    resolve(FrontendState::class)
        ->withLanguage($language)
        ->withSite($site)
        ->withTheme($theme)
        ->withLayout($layout)
        ->withPage($page);

    Route::post('/public-action', static fn (): string => 'ok')->name('capell-public-actions.submit');

    $component = new ActionsComponent(actions: [
        ['type' => 'link', 'url' => 'https://example.test', 'label' => 'External'],
        [
            'type' => 'video_popup',
            'video_url' => 'https://capell.app/storage/videos/capell-laravel-foundation.mp4',
            'label' => 'Watch video',
        ],
        [
            'type' => 'public_action',
            'public_action_key' => 'request-access',
            'label' => 'Request access',
            'access_gate_area' => 'beta',
            'source_id' => 'hero',
        ],
        ['type' => 'link', 'url' => ''],
        'ignored',
    ]);

    capell_expect($component->resolvedActions)->not->toBeEmpty()
        ->and($component->resolvedActions[0]['kind'])->toBe('link')
        ->and($component->resolvedActions[1]['kind'])->toBe('video_popup')
        ->and($component->resolvedActions[1]['video_url'])->toBe('https://capell.app/storage/videos/capell-laravel-foundation.mp4')
        ->and($component->render()->name())->toBe('capell-foundation-theme::components.actions.index');
});

it('renders video popup actions as lightbox triggers', function (): void {
    $view = file_get_contents(dirname(__DIR__, 2) . '/resources/views/components/actions/index.blade.php');

    capell_expect($view)
        ->toContain('($action[\'kind\'] ?? null) === \'video_popup\'')
        ->toContain("'lightbox action-item max-w-full whitespace-normal'")
        ->toContain(':data-lightbox="$action[\'video_url\']"')
        ->toContain('data-type="video"')
        ->toContain(':data-title="$action[\'label\'] ?? \'\'"');
});

it('builds footer context and table asset record keys', function (): void {
    [$language, $site, $theme, $layout, $page] = foundationThemeCoverageFrontendContext();
    $site->setRelation('siteDomain', null);

    resolve(FrontendState::class)
        ->withLanguage($language)
        ->withSite($site)
        ->withTheme($theme)
        ->withLayout($layout)
        ->withPage($page);

    $footer = new FooterIndex;
    $table = new PageAssets;

    capell_expect($footer->render()->name())->toBe('capell::components.footer.index')
        ->and($footer->hasFooterMenu)->toBeFalse()
        ->and($table->getTableRecordKey(['id' => 123]))->toBe('123')
        ->and(PageAssets::getResource())->toBeString();
});

it('builds page asset table queries with scoped exclusions', function (): void {
    $table = new PageAssets;
    $table->tableArguments = ['pageId' => 10];
    $table->existingRecords = [20, 30];

    $method = new ReflectionMethod(PageAssets::class, 'getTableQuery');
    $query = $method->invoke($table);

    capell_expect($query->toSql())->toContain('not')
        ->and($query->getEagerLoads())->toHaveKeys([
            'translations.language',
            'ancestors.type',
            'creator',
            'layout',
            'image',
            'media',
            'editor',
            'site.siteDomains',
            'type',
        ]);
});

it('hydrates livewire page blocks from opaque references and skips empty selections', function (): void {
    [$language, $site, $theme, $layout, $page] = foundationThemeCoverageFrontendContext();

    $block = Widget::factory()->create([
        'key' => 'livewire-pages',
        'meta' => [
            'pagination' => true,
            'limit' => 3,
        ],
    ]);
    $layout->containers = [
        'main' => [
            'blocks' => [
                [
                    'block_key' => $block->key,
                    'occurrence' => 1,
                ],
            ],
        ],
    ];
    $layout->save();

    resolve(FrontendState::class)
        ->withLanguage($language)
        ->withSite($site)
        ->withTheme($theme)
        ->withLayout($layout)
        ->withPage($page);

    $component = new LivewirePages;
    $component->mount(OpaqueBlockReference::encode([
        'container_key' => 'main',
        'block_key' => $block->key,
        'language_id' => $language->getKey(),
        'layout_id' => $layout->getKey(),
        'occurrence' => 1,
        'page_id' => $page->getKey(),
        'page_type' => $page->getMorphClass(),
        'site_id' => $site->getKey(),
        'block_index' => 0,
    ]));

    capell_expect($component->render())->toBe('<div style="display: none"></div>')
        ->and(LivewirePages::getViewName())->toBe('capell-foundation-theme::components.block.asset.pages')
        ->and(LivewirePages::getBlockByKey($block->key)?->is($block))->toBeTrue();
});

/**
 * @return array<int, object>
 */
function foundationThemeCoverageChildComponents(object $component): array
{
    $reflectionProperty = new ReflectionProperty($component, 'childComponents');
    $childComponents = $reflectionProperty->getValue($component);

    return $childComponents['default'] ?? [];
}

/**
 * @param  array<string, mixed>  $pageMeta
 * @return array{0: Language, 1: Site, 2: Theme, 3: Layout, 4: Page}
 */
function foundationThemeCoverageFrontendContext(array $pageMeta = []): array
{
    $language = Language::factory()->english()->create();
    $theme = Theme::factory()->defaultMeta()->create();
    $site = Site::factory()
        ->language($language)
        ->theme($theme)
        ->withTranslations($language, ['title' => 'Foundation Site'])
        ->create();
    $layout = Layout::factory()->site($site)->create([
        'admin' => [],
    ]);
    $page = Page::factory()
        ->site($site)
        ->layout($layout)
        ->withTranslations($language, [
            'title' => 'Foundation Page',
            'content' => '<p>Foundation content.</p>',
        ])
        ->create([
            'meta' => $pageMeta,
        ]);

    $site->load('translation');
    $page->load('translation');

    return [$language, $site, $theme, $layout, $page];
}
