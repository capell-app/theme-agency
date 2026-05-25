@php
    use Capell\Core\Actions\ResolveRenderableComponentAction;
    use Capell\Core\Enums\AssetComponentEnum;
    use Capell\Core\Enums\RenderableTypeEnum;
    use Capell\Core\Facades\CapellCore;
    use Capell\Frontend\Facades\Frontend;

    $language = Frontend::language();
    $page = Frontend::page();
    $theme = Frontend::theme();
@endphp

@props([
    'columns' => $container['meta']['override_columns'] ?? $block->getMeta('columns', 3),
    'componentItem' => $block->getMeta('component_item', AssetComponentEnum::Card->value),
    'container',
    'containerKey',
    'containerWidth' => null,
    'index',
    'loop',
    'showPageContent' => $blockData['meta']['show_page_content'] ?? false,
    'showPageTitle' => $blockData['meta']['show_page_title'] ?? false,
    'size' => $block->getMeta('size', $containerKey === 'sidebar' ? 'sm' : null),
    'spacing' => $block->getMeta('spacing', $containerKey === 'sidebar' ? 'md' : 'lg'),
    'block',
    'withChildCount' => (bool) $block->getMeta('with_child_count'),
    'withDate' => (bool) $block->getMeta('with_date'),
    'withImage' => (bool) $block->getMeta('with_image'),
    'withParent' => (bool) $block->getMeta('with_parent'),
    'withSummary' => (bool) $block->getMeta('with_summary'),
])
@php
    $pages ??= $block->assets
        ->map(fn (object $blockAsset): ?object => $blockAsset->asset)
        ->filter()
        ->values();

    if ($componentItem === 'capell::list.item') {
        $componentItem = AssetComponentEnum::Card->value;
    }

    $componentItem = ResolveRenderableComponentAction::run(RenderableTypeEnum::Asset, $componentItem);
@endphp

<div class="capell-asset-pages contents">
    @if ($pages->isNotEmpty() || ! config('capell-layout-builder.block.skip_render_empty', true))
        <x-capell-foundation-theme::block.wrapper
            class="block-pages widget-pages"
            container-class="space-y-4"
            :$container
            :$containerKey
            :$containerWidth
            :index="$loop->index"
            :$block
        >
            @php
                $showTitle = $block->getMeta("container_options.{$containerKey}.hide_title") !== true
                    && ($block->translation?->title || ($showPageTitle && $page->translation->title));
                $showContent = $block->getMeta("container_options.{$containerKey}.hide_content") !== true
                    && ($block->translation?->content || ($showPageContent && $page->translation->content));
            @endphp

            @if ($showTitle || $showContent)
                <x-capell::content
                    class="block-content"
                    :compact="true"
                    :content="$showContent ? ($block->translation->content ?: ($showPageContent ? $page->translation->content : null)) : null"
                    :content-type="$block->translation->content ? $block->type->content_structure : ($showPageContent ? $page->type->content_structure : null)"
                    :divider="$block->getMeta('content_divider')"
                    :muted="in_array($containerKey, $theme->secondary_containers)"
                    :text-align="$block->getMeta('align')"
                    :title="$showTitle ? ($block->translation->title ?: ($showPageTitle ? $page->translation->title : null)) : null"
                    :heading-style="$block->getMeta('heading_style')"
                    :heading-tag="$showPageTitle ? 'h1' : null"
                />
            @endif

            @if (! $pages || $pages->isEmpty())
                <x-capell::no-results>
                    {!! $block->translation?->getMeta('no_results') ?: __('capell-layout-builder::generic.no_pages_found') !!}
                </x-capell::no-results>
            @else
                <div
                    @class([
                        'grid',
                        ...$containerKey === 'sidebar' && (! $columns && $columns !== 0)
                        ? [
                            'divide-y divide-gray-100 [&>*:not(:first-child)]:pt-4 [&>*:not(:last-child)]:pb-4',
                        ]
                        : [
                            '[&>*:not(:first-child)]:pt-2 [&>*:not(:last-child)]:pb-2' => $spacing === 'sm' && (! $columns && $columns !== 0),
                            '[&>*:not(:first-child)]:pt-4 [&>*:not(:last-child)]:pb-4' => $spacing === 'lg' && (! $columns && $columns !== 0),
                            '[&>*:not(:first-child)]:pt-6 [&>*:not(:last-child)]:pb-6' => $spacing === 'md' && (! $columns && $columns !== 0),
                            'gap-2 @lg:gap-x-4 @lg:gap-y-4' => $spacing === 'sm' && $columns,
                            'gap-6 @lg:gap-x-8 @lg:gap-y-8' => $spacing === 'md' && $columns,
                            'gap-8 @lg:gap-x-10 @lg:gap-y-10' => $spacing === 'lg' && $columns,
                            '@3xl:grid-cols-2' => $columns > 1 && count($pages) >= 2,
                            '@8xl:grid-cols-3' => $columns > 2 && count($pages) >= 3,
                        ],
                    ])
                >
                    @foreach ($pages as $item)
                        <x-dynamic-component
                            :component="$componentItem"
                            :class="$block->key . '-page-item'"
                            :$container
                            :$containerKey
                            :count="$withChildCount ? $item->children_count : null"
                            :icon="(bool) $block->getMeta('icon')"
                            :image="$withImage ? $item->image : null"
                            :$loop
                            :parent="$withParent && method_exists($item, 'loadParent') ? $item->loadParent($language) : null"
                            :publish-date="$withDate ? $item->getPublishDate() : null"
                            :$size
                            :summary="$item->translation->summary"
                            :title="$item->translation->title"
                            :url="$item->pageUrl->full_url"
                            :$withSummary
                        />
                    @endforeach
                </div>

                @if (method_exists($pages, 'total'))
                    <x-capell::pagination :results="$pages" />
                @endif
            @endif
        </x-capell-foundation-theme::block.wrapper>
    @endif
</div>
