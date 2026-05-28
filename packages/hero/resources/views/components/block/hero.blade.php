<?php
use Capell\Frontend\Facades\Frontend;

$page = Frontend::page();
$site = Frontend::site();
$theme = Frontend::theme();
?>

@props([
    'backgroundColor' => $block->getMeta('background_color'),
    'containerKey',
    'containerIndex',
    'color' => $block->getMeta('color', $theme->getMeta('color')),
    'carouselAlign' => $block->getMeta('carousel_align', 'center'),
    'carouselArrows' => (bool) $block->getMeta('carousel_arrows', true),
    'carouselAutoPlay' => (bool) $block->getMeta('carousel_auto_play', true),
    'carouselAutoDelay' => (int) $block->getMeta('carousel_auto_delay', 8000),
    'carouselButtonClass' => 'hover:bg-primary focus:bg-primary pointer-events-auto bg-white/80 shadow-md transition hover:text-white focus:text-white disabled:pointer-events-none disabled:opacity-50',
    'carouselDisableOnInteraction' => (bool) $block->getMeta('carousel_disable_on_interaction', true),
    'carouselDrag' => (bool) $block->getMeta('carousel_drag', true),
    'carouselEffect' => $block->getMeta('carousel_effect', 'slide'),
    'carouselFade' => (bool) $block->getMeta('carousel_fade', false),
    'carouselLoop' => (bool) $block->getMeta('carousel_loop', true),
    'carouselPagination' => (bool) $block->getMeta('carousel_pagination', true),
    'carouselPauseOnHover' => (bool) $block->getMeta('carousel_pause_on_hover', true),
    'carouselRewind' => (bool) $block->getMeta('carousel_rewind', false),
    'carouselSpeed' => (int) $block->getMeta('carousel_speed', 300),
    'carouselTouch' => $block->getMeta('carousel_touch'),
    'carouselWheel' => (bool) $block->getMeta('carousel_wheel', true),
    'heroContent' => null,
    'loop',
    'total' => $block->assets->count(),
    'slideClass' => '',
    'block',
    'blockIndex',
])
{{-- format-ignore-start --}}
@php
    use Capell\LayoutBuilder\Actions\GetBlockContainerWidthAction;
    use Illuminate\Contracts\Pagination\LengthAwarePaginator;
    use Capell\Frontend\Actions\GetPageVariablesAction;
    use Capell\Frontend\Actions\RenderHtmlContentAction;
    use Capell\Hero\Actions\ResolveHeroBackgroundDataAction;
    use Capell\Hero\Actions\ResolveHeroMediaDataAction;
    use Capell\Hero\Data\HeroAssetSlideData;

    if ($containerIndex === 0 && $theme->getMeta('header_position') === 'fixed') {
        $slideClass .= ' pt-20 lg:pt-32';
    }

    $height = match($block->getMeta('height')) {
        'small' => '24em',
        'medium' => '36em',
        'large' => '60vh',
        default => null,
    };

    $containerClass = GetBlockContainerWidthAction::run($block);

    $pageVariables = collect(GetPageVariablesAction::run())
        ->filter(static fn (mixed $value): bool => is_scalar($value) || $value === null)
        ->map(static fn (mixed $value): string => (string) $value)
        ->all();

    $contentAlign = $block->getMeta('content_align', 'center');
    $contentWidth = $block->getMeta('content_width', 'balanced');
    $mediaSize = $block->getMeta('media_size', 'default');
    $mediaPosition = $block->getMeta('media_position', 'right');

    $contentAlignmentClass = match ($contentAlign) {
        'left', 'start' => 'items-start text-left',
        default => 'items-center text-center',
    };

    $contentWidthClass = match ($contentWidth) {
        'compact' => 'max-w-[42rem]',
        'wide' => 'max-w-[72rem]',
        default => 'max-w-[min(62rem,100%)]',
    };

    $pageHeroTitle = $page->translation->getMeta('hero_title');
    $pageHeroTitle = is_string($pageHeroTitle) && $pageHeroTitle !== '' ? __($pageHeroTitle, $pageVariables) : null;
    $paginationResults = $results ?? Frontend::getFrontendData('pagination_results');
@endphp
{{-- format-ignore-end --}}
@if ($block->assets->isNotEmpty() || $pageHeroTitle || $page->translation->getMeta('hero') || $paginationResults instanceof LengthAwarePaginator || ! config('capell-layout-builder.block.skip_render_empty', true))
    <section
        @class([
            'capell-block',
            'block-hero relative z-10 grid w-full',
            'mb-10' => ! $loop->last,
            'mt-10' => ! $loop->first,
            'bg-[#fbfaf7] text-[#1f2923] dark:bg-[#fbfaf7] dark:text-[#1f2923]' => $color === 'light',
            'bg-gray-800 dark:bg-gray-900' => $color === 'dark',
            'min-h-[calc(100vh-var(--header-height))]' => $height === 'full',
        ])
        @style([
            "min-height: {$height}" => filled($height) && $height !== 'full',
        ])
    >
        <x-capell-hero::hero.wrapper
            :key="$containerKey . '-block-' . $blockIndex"
            :total="$total"
            :carousel-align="$carouselAlign"
            :carousel-arrows="$carouselArrows"
            :carousel-auto-play="$carouselAutoPlay"
            :carousel-auto-delay="$carouselAutoDelay"
            :carousel-button-class="$carouselButtonClass"
            :carousel-disable-on-interaction="$carouselDisableOnInteraction"
            :carousel-drag="$carouselDrag"
            :carousel-effect="$carouselEffect"
            :carousel-fade="$carouselFade"
            :carousel-loop="$carouselLoop"
            :carousel-pagination="$carouselPagination"
            :carousel-pause-on-hover="$carouselPauseOnHover"
            :carousel-rewind="$carouselRewind"
            :carousel-speed="$carouselSpeed"
            :carousel-touch="$carouselTouch"
            :carousel-wheel="$carouselWheel"
        >
            @if ($block->assets->isNotEmpty())
                @foreach ($block->assets as $blockAsset)
                    {{-- format-ignore-start --}}
                @php
                    /** @var \Capell\LayoutBuilder\Models\WidgetAsset $blockAsset */
                    $isFirstSlide = $loop->first;
                    $slide = HeroAssetSlideData::fromBlockAsset($blockAsset, $block, $color);
                    $heroBackground = ResolveHeroBackgroundDataAction::run($theme, $block, $blockAsset);
                    $heroMedia = ResolveHeroMediaDataAction::run($theme, $block, $blockAsset);
                @endphp
                {{-- format-ignore-end --}}
                    <x-capell-hero::hero.slide
                        :hero-background="$heroBackground"
                        :hero-media="$heroMedia"
                        :background-image="$slide->backgroundImage"
                        :background-color="$slide->asset->getMeta('background_color', $backgroundColor)"
                        :background-size="$slide->asset->getMeta('background_size', $block->getMeta('background_size', 'cover'))"
                        :background-position="$slide->asset->getMeta('background_position', $block->getMeta('background_position', 'center'))"
                        :background-attachment="$slide->asset->getMeta('background_attachment', $block->getMeta('background_attachment', 'scroll'))"
                        :background-repeat="$slide->asset->getMeta('background_repeat', $block->getMeta('background_repeat', 'no-repeat'))"
                        :background-overlay="$slide->backgroundImage && $slide->asset->translation ? $slide->color : ''"
                        :first="$isFirstSlide"
                        :total="$total"
                        :title="$slide->asset->translation->title"
                        :color="$slide->color"
                        :container-class="$containerClass->getContainerClass()"
                        :class="$slideClass"
                    >
                        <div
                            @class([
                                '@container grid max-w-full min-w-0 gap-4 gap-x-10 gap-y-8 py-14 select-text lg:gap-x-16 lg:py-24',
                                'lg:grid-cols-12' => $slide->images?->isNotEmpty(),
                            ])
                        >
                            <div
                                @class([
                                    'flex max-w-full min-w-0 flex-col justify-center',
                                    $contentAlignmentClass => ! $slide->images?->isNotEmpty(),
                                    'items-start text-left' => $slide->images?->isNotEmpty(),
                                    'lg:col-span-5 xl:col-span-7' => $slide->images?->isNotEmpty() && $mediaSize !== 'compact',
                                    'lg:col-span-7 xl:col-span-8' => $slide->images?->isNotEmpty() && $mediaSize === 'compact',
                                    'lg:order-2' => $slide->images?->isNotEmpty() && $mediaPosition === 'left',
                                    'py-[4vh]' => ! $slide->asset->image && ! $slide->backgroundImage,
                                ])
                            >
                                @if ($slide->asset)
                                    <x-capell-hero::hero.content
                                        :title="$slide->asset->translation->title"
                                        :heading-size="$isFirstSlide ? 'h1' : 'h2'"
                                        :url="$slide->url"
                                        :color="$slide->color"
                                        :size="! $slide->images?->isNotEmpty() ? 'lg' : 'md'"
                                        :content_class="'hero-content prose w-full ' . $contentWidthClass"
                                    >
                                        {!! RenderHtmlContentAction::run((string) $slide->asset->translation->content, ['page' => $page, 'site' => $site]) !!}

                                        @if ($slide->asset->getMeta('link_text'))
                                            <a
                                                class="text-link hover:text-primary font-medium no-underline focus:underline"
                                                href="{{ $slide->url }}"
                                                wire:navigate
                                            >
                                                @svg('heroicon-s-chevron-right', 'mr-2 inline-block h-6 w-6')
                                                {{ $slide->asset->getMeta('link_text') }}
                                            </a>
                                        @endif

                                        @if ($isFirstSlide && $heroContent)
                                            {{ $heroContent }}
                                        @endif

                                        @if ($isFirstSlide && $paginationResults instanceof LengthAwarePaginator && $paginationResults->hasPages())
                                            @php
                                                Frontend::setFrontendData('has_pagination_summary', true);
                                            @endphp

                                            <x-capell::pagination.hero-summary
                                                :results="$paginationResults"
                                                class="mt-5"
                                            />
                                        @endif
                                    </x-capell-hero::hero.content>
                                @endif

                                @if ($slide->asset->related?->isNotEmpty())
                                    <x-capell-hero::hero.related
                                        class="w-full"
                                        :related="$slide->asset->related"
                                        :key="$containerKey . '-block-' . $blockIndex . '-related'"
                                    />
                                @endif

                                @if ($slide->asset->getMeta('actions'))
                                    <x-capell::actions
                                        class="hero-actions mt-8 w-full"
                                        style="
                                            max-width: min(
                                                100%,
                                                calc(100vw - 12vw)
                                            );
                                        "
                                        :actions="$slide->asset->getMeta('actions')"
                                        :color="$slide->color"
                                        action-item-class="hero-action-item"
                                    />
                                @endif
                            </div>

                            @if ($slide->images?->isNotEmpty())
                                <div
                                    @class([
                                        'relative z-30 flex w-full max-w-full min-w-0 items-center overflow-hidden',
                                        'lg:col-span-6 xl:col-span-5' => $mediaSize !== 'compact',
                                        'lg:col-span-5 xl:col-span-4' => $mediaSize === 'compact',
                                        'lg:order-1' => $mediaPosition === 'left',
                                    ])
                                >
                                    @foreach ($slide->images as $media)
                                        @if ($loop->first)
                                            <x-capell::media
                                                format="webp"
                                                :media="$media"
                                                :alt="$slide->asset->translation->title"
                                                :width="420"
                                                :fetchpriority="$isFirstSlide ? 'high' : null"
                                                @class([
                                                    'hero-slide-img h-full max-h-[40vh] w-full max-w-full min-w-0 object-cover object-center',
                                                    'lg:max-h-[400px]' => $mediaSize !== 'compact',
                                                    'lg:max-h-[320px]' => $mediaSize === 'compact',
                                                ])
                                                loading="{{ $isFirstSlide ? 'eager' : 'lazy' }}"
                                                sizes="(min-width: 1024px) 38vw, 88vw"
                                            />
                                            @continue
                                        @endif

                                        <div
                                            class="absolute -bottom-4 left-4 z-12 w-2/3 rounded-lg bg-gray-200 shadow-lg lg:-left-8 dark:bg-gray-800"
                                        >
                                            <x-capell::media
                                                format="webp"
                                                :media="$media"
                                                :alt="$slide->asset->translation->title"
                                                @class([
                                                    'hero-slide-img h-full max-h-[40vh] w-full max-w-full min-w-0 object-cover object-center',
                                                    'lg:max-h-[400px]' => $mediaSize !== 'compact',
                                                    'lg:max-h-[320px]' => $mediaSize === 'compact',
                                                ])
                                                loading="lazy"
                                            />
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </x-capell-hero::hero.slide>
                @endforeach
            @elseif ($pageHeroTitle || $page->translation->getMeta('hero') || $paginationResults instanceof LengthAwarePaginator)
                <x-capell-hero::hero.slide
                    :hero-background="ResolveHeroBackgroundDataAction::run($theme, $block)"
                    :hero-media="ResolveHeroMediaDataAction::run($theme, $block)"
                    :background-image="$block->image"
                    :background-color="$block->getMeta('background_color', $theme->getMeta('background_color'))"
                    :background-size="$block->getMeta('background_size', 'cover')"
                    :background-position="$block->getMeta('background_position', 'center')"
                    :background-attachment="$block->getMeta('background_attachment', 'scroll')"
                    :background-repeat="$block->getMeta('background_repeat', 'no-repeat')"
                    :first="true"
                    :total="1"
                    :color="$color"
                    container-class="container"
                >
                    <div class="@lg:py-12 flex items-center py-12 select-text">
                        <x-capell-hero::hero.content
                            :title="$pageHeroTitle ?: ($block->translation ? __($block->translation->title, $pageVariables) : null)"
                            :color="$color"
                            size="md"
                            :content_class="'hero-content prose w-full ' . $contentWidthClass"
                            @class([
                                'hero-page-content',
                                'mx-auto' => $contentAlign === 'center',
                            ])
                        >
                            @if ($page->translation->getMeta('hero'))
                                {!! RenderHtmlContentAction::run((string) __($page->translation->getMeta('hero'), $pageVariables), ['page' => $page, 'site' => $site]) !!}
                            @endif

                            @if ($paginationResults instanceof LengthAwarePaginator && $paginationResults->hasPages())
                                @php
                                    Frontend::setFrontendData('has_pagination_summary', true);
                                @endphp

                                <x-capell::pagination.hero-summary
                                    :results="$paginationResults"
                                />
                            @endif
                        </x-capell-hero::hero.content>
                    </div>
                </x-capell-hero::hero.slide>
            @endif
        </x-capell-hero::hero.wrapper>
    </section>
@endif
