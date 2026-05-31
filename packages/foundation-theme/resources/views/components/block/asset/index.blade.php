@php
    use Capell\Core\Enums\AssetComponentEnum;
    use Capell\Core\Facades\CapellCore;
    use Capell\FoundationTheme\Support\ResponsiveAssetLayoutOptions;
    use Capell\Frontend\Contracts\AssetsRegistryInterface;
    use Capell\Frontend\Facades\Frontend;
    use Capell\LayoutBuilder\Enums\ResponsiveLayoutPattern;

    $theme = Frontend::theme();
@endphp

@props([
    'color' => $block->getMeta('color', 'dark'),
    'container',
    'containerKey',
    'containerWidth' => null,
    'block',
    'blockIndex' => null,
    'loop',
    'total' => $block->assets->count(),
    'block' => $block,
    'blockIndex' => $blockIndex,
    'maxWidth' => $block->getMeta('max_width'),
    'withChildCount' => (bool) $block->getMeta('with_child_count'),
    'withImage' => (bool) $block->getMeta('with_image', true),
    'withParent' => (bool) $block->getMeta('with_parent'),
    'withDate' => (bool) $block->getMeta('with_date'),
    'withSummary' => (bool) $block->getMeta('with_summary'),
    'spacing' => $block->getMeta('spacing', true),
    'columns' => (int) $block->getMeta('columns'),
    'headingSize' => $block->getMeta('heading_size'),
    'imagePosition' => $block->getMeta('image_position', 'left'),
    'responsiveLayoutOptions' => ResponsiveAssetLayoutOptions::fromBlock($block, $total),
])
@php
    $responsiveLayoutPattern = $responsiveLayoutOptions->pattern;
    $assetLayoutKey = sprintf('%s-%s-%s', $containerKey, $block->id ?? $block->key, $loop->index);
    $assetGridId = "asset-grid-{$assetLayoutKey}";
    $assetCarouselId = "asset-carousel-{$assetLayoutKey}";
    $maxWidthStyle = $maxWidth && ! in_array($maxWidth, ['none', 'sm', 'md', 'lg', 'xl'], true)
        ? '--max-max-width: ' . $maxWidth . ';'
        : '';
@endphp

@if ($block->assets->isNotEmpty() || ! config('capell-layout-builder.block.skip_render_empty', true))
    <x-capell-foundation-theme::block.wrapper
        class="site-block-asset block-assets block-assets-grid"
        :$container
        :$containerKey
        :$containerWidth
        :index="$loop->index"
        :block="$block"
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
            @if ($responsiveLayoutPattern->usesMobileCarousel())
                <div
                    wire:ignore
                    data-carousel="1"
                    data-carousel-align="{{ $responsiveLayoutOptions->carouselAlign() }}"
                    data-carousel-autoplay="{{ (int) $responsiveLayoutOptions->carouselAutoPlay }}"
                    data-carousel-autoplay-delay="{{ $responsiveLayoutOptions->carouselAutoDelay }}"
                    data-carousel-disable-on-interaction="{{ (int) $responsiveLayoutOptions->carouselDisableOnInteraction }}"
                    data-carousel-drag="{{ (int) $responsiveLayoutOptions->carouselDrag }}"
                    data-carousel-effect="slide"
                    data-carousel-highlight-active="{{ (int) $responsiveLayoutOptions->highlightActive }}"
                    data-carousel-id="{{ $assetCarouselId }}"
                    data-carousel-loop="{{ (int) $responsiveLayoutOptions->carouselLoop() }}"
                    data-carousel-navigation="{{ (int) $responsiveLayoutOptions->carouselArrows }}"
                    data-carousel-pagination="{{ (int) $responsiveLayoutOptions->carouselPagination }}"
                    data-carousel-pause-on-hover="{{ (int) $responsiveLayoutOptions->carouselPauseOnHover }}"
                    data-carousel-rewind="{{ (int) $responsiveLayoutOptions->carouselRewind }}"
                    data-carousel-rows="{{ $responsiveLayoutOptions->carouselRows }}"
                    data-carousel-speed="{{ $responsiveLayoutOptions->carouselSpeed }}"
                    data-carousel-touch="{{ (int) $responsiveLayoutOptions->carouselTouch }}"
                    data-carousel-watch-overflow="1"
                    data-carousel-breakpoints="{{ $responsiveLayoutOptions->carouselBreakpointsJson() }}"
                    @class([
                        'block-assets-carousel',
                        'md:hidden' => $responsiveLayoutPattern === ResponsiveLayoutPattern::DesktopGridMobileCarousel,
                        'swiper' => $total > 1,
                    ])
                >
                    <div class="swiper-wrapper">
                        @foreach ($block->assets as $asset)
                            <div class="swiper-slide h-auto">
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
                                    :heading-size="$headingSize"
                                    :image-position="$imagePosition"
                                    :with-parent="$withParent"
                                    :with-summary="$withSummary"
                                    class="block-asset h-full"
                                />
                            </div>
                        @endforeach
                    </div>

                    @if ($total > 1)
                        <div
                            data-carousel-controls="{{ $assetCarouselId }}"
                            class="swiper-controls mt-4 flex justify-center"
                        >
                            <div
                                class="swiper-pagination pointer-events-auto flex justify-center"
                            ></div>
                        </div>
                    @endif
                </div>
            @endif

            @if ($responsiveLayoutPattern->usesDesktopGrid())
                @if ($responsiveLayoutOptions->shouldUseResponsiveGrid())
                    {!! $responsiveLayoutOptions->gridRowsStyle($assetGridId) !!}
                @endif

                <div
                    id="{{ $assetGridId }}"
                    style="{{ $responsiveLayoutOptions->shouldUseResponsiveGrid() ? $responsiveLayoutOptions->gridColumnsStyle($maxWidthStyle) : '--columns: ' . ($columns ?: $total) . ';' . $maxWidthStyle }}"
                    @class([
                        'grid',
                        'md:grid-cols-[repeat(var(--columns),minmax(0,1fr))]' => ! $responsiveLayoutOptions->shouldUseResponsiveGrid(),
                        'sm:grid-cols-[repeat(var(--columns-sm),minmax(0,1fr))] md:grid-cols-[repeat(var(--columns-md),minmax(0,1fr))] lg:grid-cols-[repeat(var(--columns-lg),minmax(0,1fr))] xl:grid-cols-[repeat(var(--columns-xl),minmax(0,1fr))]' => $responsiveLayoutOptions->shouldUseResponsiveGrid(),
                        'hidden md:grid' => $responsiveLayoutPattern === ResponsiveLayoutPattern::DesktopGridMobileCarousel,
                        'mx-auto' => $maxWidth,
                        $maxWidth ? match ($maxWidth) {
                            'none' => 'max-w-none',
                            'sm' => 'max-w-sm',
                            'md' => 'max-w-md',
                            'lg' => 'max-w-lg',
                            'xl' => 'max-w-xl',
                            '2xl' => 'max-w-2xl',
                            '3xl' => 'max-w-3xl',
                            default => 'max-w-[var(--max-max-width)]',
                        } : '',
                        'gap-x-8 gap-y-6 lg:gap-x-10 lg:gap-y-10' => $spacing && $spacing !== 'none',
                        'sm:grid-cols-2' => ! $responsiveLayoutOptions->shouldUseResponsiveGrid() && $total >= 2 && $columns === 0,
                        'md:grid-cols-2' => ! $responsiveLayoutOptions->shouldUseResponsiveGrid() && $total >= 2 && $columns !== 0 && $total <= $columns,
                        'lg:grid-cols-4' => ! $responsiveLayoutOptions->shouldUseResponsiveGrid() && $total >= 4 && $columns !== 0 && $total <= $columns,
                        '2xl:grid-cols-6' => ! $responsiveLayoutOptions->shouldUseResponsiveGrid() && $total >= 6 && $columns !== 0 && $total <= $columns,
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
                            :heading-size="$headingSize"
                            :image-position="$imagePosition"
                            :with-parent="$withParent"
                            :with-summary="$withSummary"
                            class="block-asset"
                        />
                    @endforeach
                </div>
            @endif
        @endif
    </x-capell-foundation-theme::block.wrapper>
@endif
