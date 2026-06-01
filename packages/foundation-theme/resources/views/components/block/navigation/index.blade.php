@php
    use Capell\Navigation\Data\NavigationItemRenderData;
    use Illuminate\Support\Collection;

    $themeSecondaryContainers = data_get($theme, 'secondary_containers', []);
@endphp

@props([
    'columns' => $container['meta']['override_columns'] ?? $block->getMeta('columns', 3),
    'container',
    'containerKey',
    'containerWidth' => null,
    'groupItems' => $blockData['meta']['group_items'] ?? false,
    'showPageContent' => $blockData['meta']['show_page_content'] ?? false,
    'showPageTitle' => $blockData['meta']['show_page_title'] ?? false,
    'items' => [],
    'headingContent' => null,
    'headingContentStructure' => null,
    'headingTitle' => null,
    'listComponent' => 'capell::list',
    'loop',
    'block',
])
@if ($items->isNotEmpty() || ! config('capell-layout-builder.block.skip_render_empty', true))
    <x-capell-foundation-theme::block.wrapper
        class="capell-block-navigation block-navigation"
        :$container
        :$containerKey
        :$containerWidth
        :index="$loop->index"
        :$block
    >
        @if ($headingTitle || $headingContent)
            <x-capell::content
                class="mb-5"
                :compact="true"
                :content="$headingContent"
                :content-type="$headingContentStructure"
                :divider="$block->getMeta('content_divider')"
                :muted="in_array($containerKey, $themeSecondaryContainers, true)"
                :text-align="$block->getMeta('align')"
                :title="$headingTitle"
                :heading-style="$block->getMeta('heading_style')"
                :heading-tag="$showPageTitle ? 'h1' : null"
            />
        @endif

        @if ($groupItems && count($items) > 5)
            <div class="grid md:grid-cols-2">
                @php
                    /**
                     * @var Collection<NavigationItemRenderData> $items
                     */
                    $half = (int) ceil(count($items) / $columns);

                    /**
                     * @var Collection<Collection<NavigationItemRenderData>> $chunks
                     */
                    $chunks = $items->chunk($half);
                @endphp

                @foreach ($chunks as $chunk)
                    <x-dynamic-component
                        :component="$listComponent"
                        class="block-navigation-list"
                    >
                        @foreach ($chunk as $item)
                            <x-dynamic-component
                                :component="$item instanceof NavigationItemRenderData ? ($item->componentItem ?: 'capell::list.item') : (! empty($item->data['component_item']) ? $item->data['component_item'] : 'capell::list.item')"
                                class="block-navigation-item"
                                :$item
                            />
                        @endforeach
                    </x-dynamic-component>
                @endforeach
            </div>
        @else
            <x-dynamic-component
                :component="$listComponent"
                class="block-navigation-list block-navigation-lit-children text-sm"
            >
                @foreach ($items as $item)
                    <x-dynamic-component
                        :component="$item instanceof NavigationItemRenderData ? ($item->componentItem ?: 'capell::list.item') : (! empty($item->data['component_item']) ? $item->data['component_item'] : 'capell::list.item')"
                        :$item
                        class="block-navigation-item block-navigation-child-item"
                    />
                @endforeach
            </x-dynamic-component>
        @endif
    </x-capell-foundation-theme::block.wrapper>
@endif
