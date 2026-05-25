@php
    use Capell\FoundationTheme\Actions\BuildBlockAssetRenderDataAction;
    use Capell\Frontend\Facades\Frontend;
    use Spatie\Image\Image;
    use Spatie\MediaLibrary\MediaCollections\Models\Media;

    $theme = Frontend::theme();
@endphp

@props([
    'carouselAlign' => $block->getMeta('carousel_align', 'center'),
    'carouselArrows' => (bool) $block->getMeta('carousel_arrows', true),
    'carouselAutoPlay' => (bool) $block->getMeta('carousel_auto_play', true),
    'carouselAutoDelay' => (int) $block->getMeta('carousel_auto_delay', 5000),
    'carouselButtonClass' => 'hover:bg-primary focus:bg-primary pointer-events-auto bg-white/80 shadow-md transition hover:text-white focus:text-white disabled:pointer-events-none disabled:opacity-50',
    'carouselDisableOnInteraction' => (bool) $block->getMeta('carousel_disable_on_interaction', true),
    'carouselDrag' => (bool) $block->getMeta('carousel_drag', true),
    'carouselEffect' => $block->getMeta('carousel_effect', 'slide'),
    'carouselFade' => (bool) $block->getMeta('carousel_fade', false),
    'carouselLoop' => (bool) $block->getMeta('carousel_loop', true),
    'carouselPagination' => (bool) $block->getMeta('carousel_pagination', false),
    'carouselPauseOnHover' => (bool) $block->getMeta('carousel_pause_on_hover', true),
    'carouselRewind' => (bool) $block->getMeta('carousel_rewind', false),
    'carouselSpeed' => (int) $block->getMeta('carousel_speed', 300),
    'carouselTouch' => $block->getMeta('carousel_touch'),
    'carouselWheel' => (bool) $block->getMeta('carousel_wheel', true),
    'color' => $block->getMeta('color', 'dark'),
    'container',
    'containerKey',
    'containerWidth' => null,
    'showPageContent' => $blockData['meta']['show_page_content'] ?? false,
    'showPageTitle' => $blockData['meta']['show_page_title'] ?? false,
    'loop',
    'rounded' => (bool) $theme->getMeta('rounded_images'),
    'total' => $block->assets->count(),
    'block',
])
@php
    $carouselId = sprintf('carousel-%s-%s', $block->id ?? $block->key, $loop->index);
    $carouselEffect = $carouselFade ? 'fade' : $carouselEffect;
@endphp

@if ($block->assets->isNotEmpty() || ! config('capell-layout-builder.block.skip_render_empty', true))
    <x-capell-foundation-theme::block.wrapper
        class="capell-asset-carousel block-media-carousel"
        :$container
        :$containerKey
        :$containerWidth
        :index="$loop->index"
        :$block
    >
        @if (($block->translation && ($block->translation->title || $block->translation->content))
             || ($showPageTitle && $page->translation->title)
             || ($showPageContent && $page->translation->content))
            <div class="container mb-8">
                <x-capell::content
                    :compact="true"
                    :content="$block->translation->content ?? ($showPageContent ? $page->translation->content : null)"
                    :content-type="$block->translation->content ? $block->type->content_structure : ($showPageContent ? $page->type->content_structure : null)"
                    :divider="$block->getMeta('content_divider')"
                    :color="$color"
                    :muted="in_array($containerKey, $theme->secondary_containers)"
                    :title="$block->translation->title ?? ($showPageTitle ? $page->translation->title : null)"
                    :text-align="$block->getMeta('align')"
                    :heading-style="$block->getMeta('heading_style')"
                    :heading-tag="$showPageTitle ? 'h1' : null"
                />
            </div>
        @endif

        <div
            wire:ignore
            data-auto="{{ (int) $carouselAutoPlay }}"
            data-carousel="1"
            data-carousel-align="{{ $carouselAlign }}"
            data-carousel-autoplay="{{ (int) $carouselAutoPlay }}"
            data-carousel-autoplay-delay="{{ $carouselAutoDelay }}"
            data-carousel-disable-on-interaction="{{ (int) $carouselDisableOnInteraction }}"
            data-carousel-drag="{{ (int) $carouselDrag }}"
            data-carousel-effect="{{ $carouselEffect }}"
            data-carousel-id="{{ $carouselId }}"
            data-loop="{{ (int) $carouselLoop }}"
            data-delay="{{ $carouselAutoDelay }}"
            data-align="{{ $carouselAlign }}"
            data-drag="{{ (int) $carouselDrag }}"
            data-carousel-loop="{{ (int) $carouselLoop }}"
            data-carousel-navigation="{{ (int) $carouselArrows }}"
            data-carousel-pagination="{{ (int) $carouselPagination }}"
            data-carousel-pause-on-hover="{{ (int) $carouselPauseOnHover }}"
            data-carousel-rewind="{{ (int) $carouselRewind }}"
            data-carousel-speed="{{ $carouselSpeed }}"
            data-carousel-watch-overflow="1"
            data-carousel-wheel="{{ (int) $carouselWheel }}"
            data-wheel="{{ (int) $carouselWheel }}"
            data-fade="{{ (int) $carouselFade }}"
            @if ($carouselTouch !== null)
                data-carousel-touch="{{ (int) $carouselTouch }}"
            @endif
            data-carousel-breakpoints='{
            "992": {
                "slidesPerView": "auto",
                "spaceBetween": 36
            },
            "768": {
                "slidesPerView": "auto",
                "spaceBetween": 24
            },
            "320": {
                "slidesPerView": 1,
                "spaceBetween": 0
            }
        }'
            data-breakpoint='{
            "992": {
                "slidesPerView": "auto",
                "spaceBetween": 36
            },
            "768": {
                "slidesPerView": "auto",
                "spaceBetween": 24
            },
            "320": {
                "slidesPerView": 1,
                "spaceBetween": 0
            }
        }'
            @class(['relative py-10', 'swiper' => $total > 1])
            style="--swiper-navigation-sides-offset: 0"
        >
            <div class="swiper-wrapper w-full">
                @foreach ($block->assets as $blockAsset)
                    {{-- format-ignore-start --}}
                @php
                    /** @var Media|null $media */
                    $assetRenderData = BuildBlockAssetRenderDataAction::run($blockAsset);
                    $media = $assetRenderData->image;
                    if (! $media) {
                        throw new RuntimeException('Image not found for BlockAsset: ' . $blockAsset->asset_type . ' ' . $blockAsset->id);
                    }

                    $imageWidth = $media->getCustomProperty('width');
                    $imageHeight = $media->getCustomProperty('height');

                    if (Str::startsWith($media->mime_type, 'image/') && (! $imageWidth || ! $imageHeight)) {
                        $image = Image::load($media->getPath());

                        $imageWidth = $image->getWidth();
                        $imageHeight = $image->getHeight();
                    } else {
                        $imageHeight = 400;
                        $imageWidth = 400;
                    }

                    $width = 400;
                    $height = floor($width * ($imageHeight / $imageWidth));
                @endphp
                {{-- format-ignore-end --}}
                    <div
                        @class([
                            'swiper-slide block-media-item group relative h-64 overflow-hidden text-center text-white',
                            'rounded-lg' => $rounded,
                        ])
                        role="button"
                        tabindex="0"
                        onkeydown="
                            if (event.key === 'Enter' || event.key === ' ') {
                                event.preventDefault()
                                this.querySelector(
                                    '[data-lightbox], .lightbox',
                                )?.click()
                            }
                        "
                    >
                        <x-capell::media
                            :class="'swiper-slide-img object-cover h-64 mx-auto bg-gray-50 transition-transform duration-300 group-hover:scale-105 group-focus:scale-105' . ($theme->withDarkMode ? ' dark:bg-gray-900' : '')"
                            :$loop
                            :media="$media"
                            :alt="$assetRenderData->alt"
                            :width="$width"
                            :height="$height"
                            sizes="(max-width: 640px) 80vw, 20w"
                            lightbox="true"
                            rounded="true"
                        />
                        @if ($assetRenderData->title)
                            <div
                                class="pointer-events-none absolute inset-x-0 bottom-0 flex translate-y-full transform items-center justify-center bg-gray-600/75 px-2 py-4 text-sm leading-none leading-tight font-medium break-words text-white opacity-0 transition-all duration-300 group-hover:translate-y-0 group-hover:opacity-100 group-focus:translate-y-0 group-focus:opacity-100"
                            >
                                {{ $assetRenderData->title }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            @if ($total > 1)
                <div
                    data-carousel-controls="{{ $carouselId }}"
                    class="swiper-controls pointer-events-none absolute inset-0 z-50 flex items-center justify-between"
                >
                    @if ($carouselArrows)
                        <button
                            aria-label="{{ __('capell-frontend::generic.previous') }}"
                            @class([
                                'swiper-button-prev rounded-r-md',
                                $carouselButtonClass,
                            ])
                            style="width: 50px; height: 60px; margin-top: -30px"
                        ></button>
                        <button
                            aria-label="{{ __('capell-frontend::generic.next') }}"
                            @class([
                                'swiper-button-next rounded-l-md',
                                $carouselButtonClass,
                            ])
                            style="width: 50px; height: 60px; margin-top: -30px"
                        ></button>
                    @endif

                    @if ($carouselPagination)
                        <div
                            class="swiper-pagination pointer-events-auto absolute bottom-2 left-1/2 flex -translate-x-1/2 justify-center pt-4 select-none"
                            wire:ignore
                        ></div>
                    @endif
                </div>
            @endif
        </div>
    </x-capell-foundation-theme::block.wrapper>
@endif
