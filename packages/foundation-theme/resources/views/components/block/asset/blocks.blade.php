<?php
use Capell\Frontend\Facades\Frontend;

$theme = Frontend::theme();
?>

@php
    use Capell\Core\Enums\AssetComponentEnum;
    use Capell\Core\Facades\CapellCore;
    use Capell\FoundationTheme\Actions\BuildBlockAssetRenderDataAction;
    use Capell\Frontend\Contracts\AssetsRegistryInterface;
@endphp

@props([
    'color' => $block->getMeta('color', 'dark'),
    'container',
    'containerKey',
    'containerWidth' => null,
    'loop',
    'total' => $block->assets->count(),
    'block',
    'blockIndex',
    'withChildCount' => (bool) $block->getMeta('with_child_count'),
    'withImage' => (bool) $block->getMeta('with_image', true),
    'withParent' => (bool) $block->getMeta('with_parent'),
    'withDate' => (bool) $block->getMeta('with_date'),
    'withSummary' => (bool) $block->getMeta('with_summary'),
    'spacing' => $block->getMeta('spacing', true),
    'columns' => (int) $block->getMeta('columns'),
])

@if ($block->assets->isNotEmpty() || ! config('capell-layout-builder.block.skip_render_empty', true))
    <x-capell-foundation-theme::block.wrapper
        class="site-block-asset block-assets-blocks relative"
        :$container
        :$containerKey
        :$containerWidth
        :index="$loop->index"
        :$block
        container-class="space-y-6 md:space-y-10"
    >
        @if ($block->translation)
            <x-capell::content
                :compact="true"
                :content="$block->translation->content"
                :content-type="$block->type->content_structure"
                :color="$color"
                :divider="$block->getMeta('content_divider')"
                :muted="in_array($containerKey, $theme->secondary_containers)"
                :title="$block->translation->title"
                :text-align="$block->getMeta('align')"
                :heading-style="$block->getMeta('heading_style')"
            />
        @endif

        @if ($block->assets->isNotEmpty())
            <div>
                @php
                    $firstAssetRenderData = BuildBlockAssetRenderDataAction::run($block->assets->first());
                    $lastAssetRenderData = BuildBlockAssetRenderDataAction::run($block->assets->last());
                @endphp

                @if ($color = ($firstAssetRenderData->meta['color'] ?? null))
                    <x-capell-foundation-theme::block.asset.extended-background
                        :$color
                        position="left"
                    />
                @endif

                <div
                    style="--columns: {{ $columns ?: $total }}"
                    @class([
                        'grid md:grid-cols-[repeat(var(--columns),minmax(0,1fr))]',
                        'gap-x-8 gap-y-6 lg:gap-x-10 lg:gap-y-10' => $spacing && $spacing !== 'none',
                        'sm:grid-cols-2' => $total >= 2 && $columns === 0,
                        'md:grid-cols-2' => $total >= 2 && $columns !== 0 && $total <= $columns,
                        'lg:grid-cols-4' => $total >= 4 && $columns !== 0 && $total <= $columns,
                        '2xl:grid-cols-6' => $total >= 6 && $columns !== 0 && $total <= $columns,
                    ])
                >
                    @foreach ($block->assets as $asset)
                        <x-dynamic-component
                            :component="app(AssetsRegistryInterface::class)->getAsset($asset['asset_type'])->component"
                            :componentItem="$block->getMeta('component_item', AssetComponentEnum::Card->value)"
                            :$container
                            :$containerKey
                            :$loop
                            :asset="$asset->asset"
                            :with-child-count="$withChildCount"
                            :with-date="$withDate"
                            :with-image="$withImage"
                            :with-parent="$withParent"
                            :with-summary="$withSummary"
                            class="block-block-item"
                        />
                    @endforeach
                </div>
                @if ($color = ($lastAssetRenderData->meta['color'] ?? null))
                    <x-capell-foundation-theme::block.asset.extended-background
                        :$color
                        position="right"
                    />
                @endif
            </div>
        @endif
    </x-capell-foundation-theme::block.wrapper>
@endif
