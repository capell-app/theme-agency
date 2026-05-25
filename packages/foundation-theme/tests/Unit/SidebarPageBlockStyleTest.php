<?php

declare(strict_types=1);

use Capell\Core\Database\Factories\MediaFactory;
use Capell\Core\Enums\MediaCollectionEnum;
use Capell\Core\Models\Language;
use Capell\Core\Models\Layout;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\FoundationTheme\Actions\BuildAssetBannerItemsAction;
use Capell\FoundationTheme\Actions\BuildBannerImageRenderDataAction;
use Capell\FoundationTheme\Actions\ResolveLoadedBlockBackgroundImageAction;
use Capell\FoundationTheme\Livewire\Block\AbstractBlock as LivewireBlock;
use Capell\FoundationTheme\View\Components\Block\Page\AbstractPagesBlock;
use Capell\FoundationTheme\View\Components\Block\Page\Breadcrumbs as BreadcrumbsBlock;
use Capell\FoundationTheme\View\Components\Block\Page\Content as ContentBlock;
use Capell\Frontend\Data\FrontendContext;
use Capell\Frontend\Facades\Frontend;
use Capell\Frontend\Support\CapellFrontendContext;
use Capell\LayoutBuilder\Models\Widget;
use Capell\LayoutBuilder\Models\WidgetAsset;
use Capell\LayoutBuilder\Support\Livewire\OpaqueBlockReference;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Blaze\Blaze;

test('sidebar page blocks expose stable styling and current page hooks', function (): void {
    $component = new class(container: [], containerKey: 'sidebar', blockIndex: 0, loop: (object) ['index' => 0], block: new Widget(['key' => 'pages', 'name' => 'Pages', 'meta' => ['view_file' => 'capell::components.no-results']])) extends AbstractPagesBlock
    {
        protected function mountBlock(): void
        {
            $this->pages = new Collection([
                ['title' => 'Current page', 'url' => '/current'],
            ]);
        }
    };

    $view = $component->render();

    expect($view)->toBeInstanceOf(View::class)
        ->and($view->getData())->toHaveKey('pages')
        ->and($view->getData()['pages'])->toHaveCount(1)
        ->and($view->getData()['containerKey'])->toBe('sidebar');
});

test('banner image render data uses only preloaded block media', function (): void {
    $media = MediaFactory::new()->make([
        'collection_name' => MediaCollectionEnum::BackgroundImage->value,
    ]);
    $block = new Widget(['key' => 'banner', 'meta' => []]);
    $block->setRelation('media', new Collection([$media]));

    $renderData = BuildBannerImageRenderDataAction::run($block, null, null, false, false);

    expect($renderData->backgroundImage)->toBe($media);
});

test('banner image render data does not lazy-load block media', function (): void {
    $block = Widget::factory()->create(['key' => 'banner', 'meta' => []]);

    DB::enableQueryLog();

    $renderData = BuildBannerImageRenderDataAction::run($block, null, null, false, false);

    expect($renderData->backgroundImage)->toBeNull()
        ->and(DB::getQueryLog())->toBe([]);

    DB::disableQueryLog();
});

test('block wrapper background image resolution does not lazy-load media', function (): void {
    $block = Widget::factory()->create(['key' => 'section', 'meta' => []]);

    DB::enableQueryLog();

    $backgroundImage = ResolveLoadedBlockBackgroundImageAction::run($block);

    expect($backgroundImage)->toBeNull()
        ->and(DB::getQueryLog())->toBe([]);

    DB::disableQueryLog();
});

test('asset banner render data uses only loaded relations', function (): void {
    $media = MediaFactory::new()->make([
        'collection_name' => MediaCollectionEnum::Image->value,
    ]);
    $blockAsset = WidgetAsset::factory()->make([
        'asset_type' => Widget::class,
    ]);
    $blockAsset->setRelation('media', new Collection([$media]));

    $block = new Widget(['key' => 'asset-banners', 'meta' => []]);
    $block->setRelation('assets', new Collection([$blockAsset]));

    $items = BuildAssetBannerItemsAction::run($block);

    expect($items)->toHaveCount(1)
        ->and($items->first()->image)->toBe($media);
});

test('asset banner render data uses linked page loaded on the asset model', function (): void {
    $linkedPage = new Page;
    $linkedPage->setRelation('pageUrl', (object) ['full_url' => '/linked-page']);
    $linkedPage->setRelation('translation', (object) ['link_text' => 'Read more']);

    $asset = new Widget(['key' => 'linked-asset']);
    $asset->setRelation('linkedPage', $linkedPage);

    $blockAsset = WidgetAsset::factory()->make([
        'asset_type' => Widget::class,
    ]);
    $blockAsset->setRelation('asset', $asset);

    $block = new Widget(['key' => 'asset-banners', 'meta' => []]);
    $block->setRelation('assets', new Collection([$blockAsset]));

    $items = BuildAssetBannerItemsAction::run($block);

    expect($items)->toHaveCount(1)
        ->and($items->first()->url)->toBe('/linked-page')
        ->and($items->first()->linkText)->toBe('Read more');
});

test('asset banner render data does not lazy-load relations', function (): void {
    $block = Widget::factory()->create(['key' => 'asset-banners', 'meta' => []]);

    DB::enableQueryLog();

    $items = BuildAssetBannerItemsAction::run($block);

    expect($items)->toHaveCount(0)
        ->and(DB::getQueryLog())->toBe([]);

    DB::disableQueryLog();
});

test('public livewire blocks expose only an opaque reference as public state', function (): void {
    $reflection = new ReflectionClass(LivewireBlock::class);
    $publicProperties = collect($reflection->getProperties(ReflectionProperty::IS_PUBLIC))
        ->map(fn (ReflectionProperty $property): string => $property->getName())
        ->all();

    expect($publicProperties)
        ->toContain('blockReference')
        ->not->toContain('container')
        ->not->toContain('block')
        ->not->toContain('blockData')
        ->not->toContain('loop');
});

test('layout livewire blocks preserve extension block data mount parameters', function (): void {
    $view = file_get_contents(dirname(__DIR__, 2) . '/resources/views/components/layout/block.blade.php');

    expect($view)
        ->toContain('OpaqueBlockReference::encode')
        ->toContain("'blockReference' => \$blockReference")
        ->toContain("'block_data' => \$blockData")
        ->not->toContain("'blockData' => \$blockData");
});

test('opaque block references do not expose raw public context', function (): void {
    $reference = OpaqueBlockReference::encode([
        'container_key' => 'main',
        'block_key' => 'private-livewire-block',
        'page_id' => 123,
        'site_id' => 456,
    ]);

    expect($reference)
        ->not->toContain('container_key')
        ->not->toContain('private-livewire-block')
        ->not->toContain('page_id')
        ->not->toContain('site_id')
        ->and(OpaqueBlockReference::decode($reference))
        ->toMatchArray([
            'container_key' => 'main',
            'block_key' => 'private-livewire-block',
            'page_id' => 123,
            'site_id' => 456,
        ]);
});

test('public livewire blocks resolve the scoped layout block clone', function (): void {
    $language = Language::factory()->create();
    $site = Site::factory()->create(['language_id' => $language->getKey()]);
    $block = Widget::factory()->create(['key' => 'featured-pages']);
    $firstOccurrenceAsset = Page::factory()->site($site)->withTranslations($language)->create(['name' => 'First occurrence']);
    $secondOccurrenceAsset = Page::factory()->site($site)->withTranslations($language)->create(['name' => 'Second occurrence']);
    $layout = Layout::factory()->site($site)->create([
        'containers' => [
            'main' => [
                'blocks' => [
                    ['block_key' => $block->key, 'occurrence' => 1],
                    ['block_key' => $block->key, 'occurrence' => 2, 'meta' => ['show_page_title' => true]],
                ],
            ],
        ],
    ]);
    $page = Page::factory()->site($site)->layout($layout)->withTranslations($language)->create();

    WidgetAsset::factory()
        ->block($block)
        ->asset($firstOccurrenceAsset)
        ->page($page, 'main', 1)
        ->create();

    WidgetAsset::factory()
        ->block($block)
        ->asset($secondOccurrenceAsset)
        ->page($page, 'main', 2)
        ->create();

    app()->instance(CapellFrontendContext::class, new CapellFrontendContext(new FrontendContext(
        site: $site,
        language: $language,
        page: $page,
        layout: $layout,
        theme: null,
        params: [],
        slug: null,
    )));

    $component = new class extends LivewireBlock
    {
        /** @var list<int> */
        public array $assetIds = [];

        protected function mountBlock(): void
        {
            $this->assetIds = $this->block()->assets
                ->pluck('asset_id')
                ->map(fn (mixed $assetId): int => (int) $assetId)
                ->values()
                ->all();
        }
    };

    $component->mount(OpaqueBlockReference::encode([
        'container_key' => 'main',
        'block_key' => $block->key,
        'language_id' => $language->getKey(),
        'layout_id' => $layout->getKey(),
        'occurrence' => 2,
        'page_id' => $page->getKey(),
        'page_type' => $page->getMorphClass(),
        'site_id' => $site->getKey(),
        'block_index' => 1,
    ]));

    expect($component->assetIds)->toBe([(int) $secondOccurrenceAsset->getKey()]);

    $renderData = $component->render()->getData();

    expect($renderData['container'])->toHaveKey('blocks')
        ->and($renderData['blockData']['meta']['show_page_title'])->toBeTrue()
        ->and($renderData['blockData']['occurrence'])->toBe(2);

    app()->instance(CapellFrontendContext::class, new CapellFrontendContext(new FrontendContext(
        site: null,
        language: null,
        page: null,
        layout: null,
        theme: null,
        params: [],
        slug: null,
    )));
    Frontend::clearResolvedInstance(CapellFrontendContext::class);

    $hydratedComponent = new class extends LivewireBlock
    {
        protected function mountBlock(): void {}
    };

    $hydratedComponent->mount(OpaqueBlockReference::encode([
        'container_key' => 'main',
        'block_key' => $block->key,
        'language_id' => $language->getKey(),
        'layout_id' => $layout->getKey(),
        'occurrence' => 2,
        'page_id' => $page->getKey(),
        'page_type' => $page->getMorphClass(),
        'site_id' => $site->getKey(),
        'block_data' => [
            'meta' => ['show_page_title' => true],
            'tracking_key' => 'kept-in-reference',
        ],
        'block_index' => 1,
    ]));
    $hydratedData = $hydratedComponent->render()->getData();

    expect($hydratedData['blockData']['tracking_key'])->toBe('kept-in-reference')
        ->and($hydratedData['blockData']['meta']['show_page_title'])->toBeTrue();
});

test('public livewire blocks reject references without scoped page and site ids', function (): void {
    $language = Language::factory()->create();
    $site = Site::factory()->create(['language_id' => $language->getKey()]);
    $block = Widget::factory()->create(['key' => 'legacy-featured-pages']);
    $layout = Layout::factory()->site($site)->create([
        'containers' => [
            'main' => [
                'blocks' => [
                    ['block_key' => $block->key, 'occurrence' => 1],
                ],
            ],
        ],
    ]);

    $component = new class extends LivewireBlock
    {
        protected function mountBlock(): void
        {
            $this->block();
        }
    };

    expect(fn (): null => $component->mount(OpaqueBlockReference::encode([
        'container_key' => 'main',
        'block_key' => $block->key,
        'language_id' => $language->getKey(),
        'layout_id' => $layout->getKey(),
        'occurrence' => 1,
        'block_index' => 0,
    ])))
        ->toThrow(Exception::class, 'Widget reference is invalid');
});

test('public livewire blocks can hydrate blocks from global layouts', function (): void {
    $language = Language::factory()->create();
    $site = Site::factory()->create(['language_id' => $language->getKey()]);
    $block = Widget::factory()->create(['key' => 'global-featured-pages']);
    $layout = Layout::factory()->create([
        'site_id' => null,
        'containers' => [
            'main' => [
                'blocks' => [
                    [
                        'block_key' => $block->key,
                        'occurrence' => 1,
                        'meta' => [
                            'page_content' => ['title', 'content'],
                            'show_page_title' => true,
                        ],
                    ],
                ],
            ],
        ],
    ]);
    $page = Page::factory()->site($site)->layout($layout)->withTranslations($language)->create();

    app()->instance(CapellFrontendContext::class, new CapellFrontendContext(new FrontendContext(
        site: $site,
        language: $language,
        page: $page,
        layout: null,
        theme: null,
        params: [],
        slug: null,
    )));
    Frontend::clearResolvedInstance(CapellFrontendContext::class);

    $component = new class extends LivewireBlock
    {
        public string $resolvedKey = '';

        protected function mountBlock(): void
        {
            $this->resolvedKey = $this->block()->key;
        }
    };

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

    expect($component->resolvedKey)->toBe('global-featured-pages');
});

test('public livewire blocks reject global layout references replayed under another site', function (): void {
    $language = Language::factory()->create();
    $referenceSite = Site::factory()->create(['language_id' => $language->getKey()]);
    $currentSite = Site::factory()->create(['language_id' => $language->getKey()]);
    $block = Widget::factory()->create(['key' => 'global-cross-site-pages']);
    $layout = Layout::factory()->create([
        'site_id' => null,
        'containers' => [
            'main' => [
                'blocks' => [
                    ['block_key' => $block->key, 'occurrence' => 1],
                ],
            ],
        ],
    ]);
    $referencePage = Page::factory()->site($referenceSite)->layout($layout)->withTranslations($language)->create();
    $currentPage = Page::factory()->site($currentSite)->layout($layout)->withTranslations($language)->create();

    app()->instance(CapellFrontendContext::class, new CapellFrontendContext(new FrontendContext(
        site: $currentSite,
        language: $language,
        page: $currentPage,
        layout: null,
        theme: null,
        params: [],
        slug: null,
    )));
    Frontend::clearResolvedInstance(CapellFrontendContext::class);

    $component = new class extends LivewireBlock
    {
        protected function mountBlock(): void
        {
            $this->block();
        }
    };

    expect(fn (): null => $component->mount(OpaqueBlockReference::encode([
        'container_key' => 'main',
        'block_key' => $block->key,
        'language_id' => $language->getKey(),
        'layout_id' => $layout->getKey(),
        'occurrence' => 1,
        'page_id' => $referencePage->getKey(),
        'page_type' => $referencePage->getMorphClass(),
        'site_id' => $referenceSite->getKey(),
        'block_index' => 0,
    ])))
        ->toThrow(Exception::class, 'Widget not found');
});

test('public livewire page content blocks render from encrypted context without ambient frontend state', function (): void {
    $language = Language::factory()->create();
    $site = Site::factory()->create(['language_id' => $language->getKey()]);
    $block = Widget::factory()->create([
        'key' => 'page-content',
        'meta' => ['view_file' => 'capell-foundation-theme::components.block.page.content'],
    ]);
    $layout = Layout::factory()->site($site)->create([
        'containers' => [
            'main' => [
                'blocks' => [
                    ['block_key' => $block->key, 'occurrence' => 1],
                ],
            ],
        ],
    ]);
    $page = Page::factory()->site($site)->layout($layout)->withTranslations($language, [
        'title' => 'Hydrated page title',
        'content' => '<p>Hydrated page content</p>',
    ])->create();

    app()->instance(CapellFrontendContext::class, new CapellFrontendContext(new FrontendContext(
        site: null,
        language: null,
        page: null,
        layout: null,
        theme: null,
        params: [],
        slug: null,
    )));
    Frontend::clearResolvedInstance(CapellFrontendContext::class);

    $component = new class extends LivewireBlock
    {
        protected static string $defaultView = 'capell-foundation-theme::components.block.page.content';

        protected function mountBlock(): void {}
    };

    $component->mount(OpaqueBlockReference::encode([
        'container_key' => 'main',
        'block_key' => $block->key,
        'language_id' => $language->getKey(),
        'layout_id' => $layout->getKey(),
        'occurrence' => 1,
        'page_id' => $page->getKey(),
        'page_type' => $page->getMorphClass(),
        'site_id' => $site->getKey(),
        'block_data' => [
            'meta' => [
                'page_content' => ['title', 'content'],
                'show_page_title' => true,
            ],
        ],
        'block_index' => 0,
    ]));

    $view = $component->render();
    $renderData = $view->getData();
    $renderedPage = $renderData['pageRecord'];
    $wasBlazeEnabled = Blaze::isEnabled();
    Blaze::disable();

    try {
        $html = $view->render();
    } finally {
        if ($wasBlazeEnabled) {
            Blaze::enable();
        }
    }

    expect($renderedPage->translation->title)->toBe('Hydrated page title')
        ->and($renderedPage->translation->content)->toBe('<p>Hydrated page content</p>')
        ->and($renderData['blockData']['meta']['page_content'])->toBe(['title', 'content'])
        ->and($html)->toContain('Hydrated page title')
        ->and($html)->toContain('Hydrated page content')
        ->and(file_get_contents(dirname(__DIR__, 2) . '/resources/views/components/block/page/content.blade.php'))
        ->not->toContain('Frontend::page()');
});

test('breadcrumbs render data does not lazy-load optional page and site relations', function (): void {
    $language = Language::factory()->create();
    $site = Site::factory()->create(['language_id' => $language->getKey()]);
    $page = Page::factory()->site($site)->create();
    $block = new Widget(['key' => 'breadcrumbs', 'name' => 'Breadcrumbs', 'meta' => ['view_file' => 'capell-foundation-theme::components.block.page.breadcrumbs']]);

    app()->instance(CapellFrontendContext::class, new CapellFrontendContext(new FrontendContext(
        site: $site,
        language: $language,
        page: $page,
        layout: null,
        theme: null,
        params: [],
        slug: null,
    )));
    Frontend::clearResolvedInstance(CapellFrontendContext::class);

    $previous = EloquentModel::preventsLazyLoading();
    EloquentModel::preventLazyLoading();

    try {
        $component = new BreadcrumbsBlock(
            container: [],
            containerKey: 'main',
            blockIndex: 0,
            loop: (object) ['index' => 0],
            block: $block,
        );

        expect($component->render())->toBeInstanceOf(View::class);
    } finally {
        EloquentModel::preventLazyLoading($previous);
    }
});

test('content page block ignores contextless hydration when resolving next previous links', function (): void {
    app()->instance(CapellFrontendContext::class, new CapellFrontendContext(new FrontendContext(
        site: null,
        language: null,
        page: null,
        layout: null,
        theme: null,
        params: [],
        slug: null,
    )));
    Frontend::clearResolvedInstance(CapellFrontendContext::class);

    $block = new Widget(['key' => 'content', 'name' => 'Content', 'meta' => ['view_file' => 'capell::components.no-results']]);

    $component = new ContentBlock(
        container: [],
        containerKey: 'main',
        blockIndex: 0,
        loop: (object) ['index' => 0],
        block: $block,
    );

    expect($component->previousPage)->toBeNull()
        ->and($component->nextPage)->toBeNull();
});

test('public livewire blocks reject references from another frontend site', function (): void {
    $language = Language::factory()->create();
    $currentSite = Site::factory()->create(['language_id' => $language->getKey()]);
    $otherSite = Site::factory()->create(['language_id' => $language->getKey()]);
    $block = Widget::factory()->create(['key' => 'featured-pages']);
    $layout = Layout::factory()->site($otherSite)->create([
        'containers' => [
            'main' => [
                'blocks' => [
                    ['block_key' => $block->key, 'occurrence' => 1],
                ],
            ],
        ],
    ]);
    $page = Page::factory()->site($currentSite)->withTranslations($language)->create();

    app()->instance(CapellFrontendContext::class, new CapellFrontendContext(new FrontendContext(
        site: $currentSite,
        language: $language,
        page: $page,
        layout: null,
        theme: null,
        params: [],
        slug: null,
    )));

    $component = new class extends LivewireBlock
    {
        protected function mountBlock(): void
        {
            $this->block();
        }
    };

    expect(fn (): null => $component->mount(OpaqueBlockReference::encode([
        'container_key' => 'main',
        'block_key' => $block->key,
        'language_id' => $language->getKey(),
        'layout_id' => $layout->getKey(),
        'occurrence' => 1,
        'page_id' => $page->getKey(),
        'page_type' => $page->getMorphClass(),
        'site_id' => $currentSite->getKey(),
        'block_index' => 0,
    ])))
        ->toThrow(Exception::class, 'Widget not found');
});
