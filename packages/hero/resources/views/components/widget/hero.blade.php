@props([
    'hero',
    'containerKey',
    'widgetIndex',
    'loop',
    'heroContent' => null,
])

@if ($hero?->shouldRender)
    <section
        @class([
            'capell-widget',
            'widget-hero relative z-10 grid w-full',
            'mb-10' => ! $loop->last,
            'mt-10' => ! $loop->first,
            'bg-[#fbfaf7] text-[#1f2923] dark:bg-[#fbfaf7] dark:text-[#1f2923]' => $hero->color === 'light',
            'bg-gray-800 dark:bg-gray-900' => $hero->color === 'dark',
            'min-h-[calc(100vh-var(--header-height))]' => $hero->height === 'full',
        ])
        @style([
            "min-height: {$hero->height}" => filled($hero->height) && $hero->height !== 'full',
        ])
    >
        <x-capell-hero::hero.wrapper
            :key="$containerKey . '-widget-' . $widgetIndex"
            :total="$hero->total"
            :carousel-align="$hero->carousel['align']"
            :carousel-arrows="$hero->carousel['arrows']"
            :carousel-auto-play="$hero->carousel['autoPlay']"
            :carousel-auto-delay="$hero->carousel['autoDelay']"
            :carousel-button-class="$hero->carousel['buttonClass']"
            :carousel-disable-on-interaction="$hero->carousel['disableOnInteraction']"
            :carousel-drag="$hero->carousel['drag']"
            :carousel-effect="$hero->carousel['effect']"
            :carousel-fade="$hero->carousel['fade']"
            :carousel-loop="$hero->carousel['loop']"
            :carousel-pagination="$hero->carousel['pagination']"
            :carousel-pause-on-hover="$hero->carousel['pauseOnHover']"
            :carousel-rewind="$hero->carousel['rewind']"
            :carousel-speed="$hero->carousel['speed']"
            :carousel-touch="$hero->carousel['touch']"
            :carousel-wheel="$hero->carousel['wheel']"
        >
            @if ($hero->slides->isNotEmpty())
                @foreach ($hero->slides as $slide)
                    <x-capell-hero::hero.slide
                        :hero-background="$slide->heroBackground"
                        :background-key="$containerKey . '-' . $widgetIndex . '-' . $loop->index"
                        :hero-media="$slide->heroMedia"
                        :background-image="$slide->backgroundImage"
                        :background-color="$slide->backgroundColor ?? $hero->backgroundColor"
                        :background-size="$slide->backgroundSize"
                        :background-position="$slide->backgroundPosition"
                        :background-attachment="$slide->backgroundAttachment"
                        :background-repeat="$slide->backgroundRepeat"
                        :background-overlay="$slide->backgroundImage && $slide->title ? $slide->color : ''"
                        :first="$loop->first"
                        :total="$hero->total"
                        :title="$slide->title"
                        :color="$slide->color"
                        :container-class="$hero->containerClass"
                        :class="$hero->slideClass"
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
                                    $hero->contentAlignmentClass => ! $slide->images?->isNotEmpty(),
                                    'items-start text-left' => $slide->images?->isNotEmpty(),
                                    'lg:col-span-5 xl:col-span-7' => $slide->images?->isNotEmpty() && $hero->mediaSize !== 'compact',
                                    'lg:col-span-7 xl:col-span-8' => $slide->images?->isNotEmpty() && $hero->mediaSize === 'compact',
                                    'lg:order-2' => $slide->images?->isNotEmpty() && $hero->mediaPosition === 'left',
                                    'py-[4vh]' => ! $slide->image && ! $slide->backgroundImage,
                                ])
                            >
                                <x-capell-hero::hero.content
                                    :title="$slide->title"
                                    :heading-size="$loop->first ? 'h1' : 'h2'"
                                    :url="$slide->url"
                                    :color="$slide->color"
                                    :size="! $slide->images?->isNotEmpty() ? 'lg' : 'md'"
                                    :content_class="'hero-content prose w-full ' . $hero->contentWidthClass"
                                >
                                    {!! $slide->contentHtml !!}

                                    @if ($slide->linkText)
                                        <a
                                            class="text-link hover:text-primary font-medium no-underline focus:underline"
                                            href="{{ $slide->url }}"
                                            wire:navigate
                                        >
                                            @svg('heroicon-s-chevron-right', 'mr-2 inline-block h-6 w-6')
                                            {{ $slide->linkText }}
                                        </a>
                                    @endif

                                    @if ($loop->first && $heroContent)
                                        {{ $heroContent }}
                                    @endif

                                    @if ($loop->first && $hero->hasPaginationSummary)
                                        <x-capell::pagination.hero-summary
                                            :results="$hero->paginationResults"
                                            class="mt-5"
                                        />
                                    @endif
                                </x-capell-hero::hero.content>

                                @if ($slide->related->isNotEmpty())
                                    <x-capell-hero::hero.related
                                        class="w-full"
                                        :related="$slide->related"
                                        :key="$containerKey . '-widget-' . $widgetIndex . '-related'"
                                    />
                                @endif

                                @if ($slide->actions)
                                    <x-capell::actions
                                        class="hero-actions mt-8 w-full"
                                        style="
                                            max-width: min(
                                                100%,
                                                calc(100vw - 12vw)
                                            );
                                        "
                                        :actions="$slide->actions"
                                        :color="$slide->color"
                                        action-item-class="hero-action-item"
                                    />
                                @endif
                            </div>

                            @if ($slide->images?->isNotEmpty())
                                <div
                                    @class([
                                        'relative z-30 flex w-full max-w-full min-w-0 items-center overflow-hidden',
                                        'lg:col-span-6 xl:col-span-5' => $hero->mediaSize !== 'compact',
                                        'lg:col-span-5 xl:col-span-4' => $hero->mediaSize === 'compact',
                                        'lg:order-1' => $hero->mediaPosition === 'left',
                                    ])
                                >
                                    @foreach ($slide->images as $media)
                                        @if ($loop->first)
                                            <x-capell::media
                                                format="webp"
                                                :media="$media"
                                                :alt="$slide->title"
                                                :width="420"
                                                :fetchpriority="$loop->parent->first ? 'high' : null"
                                                @class([
                                                    'hero-slide-img h-full max-h-[40vh] w-full max-w-full min-w-0 object-cover object-center',
                                                    'lg:max-h-[400px]' => $hero->mediaSize !== 'compact',
                                                    'lg:max-h-[320px]' => $hero->mediaSize === 'compact',
                                                ])
                                                loading="{{ $loop->parent->first ? 'eager' : 'lazy' }}"
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
                                                :alt="$slide->title"
                                                @class([
                                                    'hero-slide-img h-full max-h-[40vh] w-full max-w-full min-w-0 object-cover object-center',
                                                    'lg:max-h-[400px]' => $hero->mediaSize !== 'compact',
                                                    'lg:max-h-[320px]' => $hero->mediaSize === 'compact',
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
            @elseif ($hero->pageHeroTitle || $hero->pageHeroContentHtml || $hero->paginationResults)
                <x-capell-hero::hero.slide
                    :hero-background="$hero->pageHeroBackground"
                    :background-key="$containerKey . '-' . $widgetIndex . '-page'"
                    :hero-media="$hero->pageHeroMedia"
                    :background-image="$hero->pageBackgroundImage"
                    :background-color="$hero->pageBackgroundColor"
                    :background-size="$hero->pageBackgroundSize"
                    :background-position="$hero->pageBackgroundPosition"
                    :background-attachment="$hero->pageBackgroundAttachment"
                    :background-repeat="$hero->pageBackgroundRepeat"
                    :first="true"
                    :total="1"
                    @lgguIOmFQ0gbqZ3dp2nB
                    container-class="container"
                >
                    <div class="@lg:py-12 flex items-center py-12 select-text">
                        <x-capell-hero::hero.content
                            :title="$hero->pageHeroTitle ?: $hero->widgetFallbackTitle"
                            :color="$hero->color"
                            size="md"
                            :content_class="'hero-content prose w-full ' . $hero->contentWidthClass"
                            @class([
                                'hero-page-content',
                                'mx-auto' => $hero->contentAlignmentClass === 'items-center text-center',
                            ])
                        >
                            @if ($hero->pageHeroContentHtml)
                                {!! $hero->pageHeroContentHtml !!}
                            @endif

                            @if ($hero->hasPaginationSummary)
                                <x-capell::pagination.hero-summary
                                    :results="$hero->paginationResults"
                                />
                            @endif
                        </x-capell-hero::hero.content>
                    </div>
                </x-capell-hero::hero.slide>
            @endif
        </x-capell-hero::hero.wrapper>
    </section>
@endif
