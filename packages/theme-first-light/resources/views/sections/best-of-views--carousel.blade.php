{{--
    best-of-views, `carousel` variant realising best-of-views-carousel: the
    ranked list becomes a swipeable Swiper carousel driven entirely by
    data-carousel-* attributes (shared carousel.js contract), capped at 20
    items per the platform §0.3 payload guardrail. Each slide is also a
    `.lightbox` trigger in the `curation-feed` group so the reel's shared
    lightbox opens straight from the "most viewed" rail.
--}}

@php
    $items = collect(data_get($section, 'items', []))
        ->filter(fn (mixed $item): bool => filled(data_get($item, 'title', data_get($item, 'name'))))
        ->take(20)
        ->values();
    $buttonLabel = data_get($section, 'button', data_get($section, 'label'));
    $buttonUrl = data_get($section, 'buttonUrl', data_get($section, 'url', '#best-of-views'));
@endphp

<section
    id="best-of-views"
    class="mcf-section"
    data-widget="best-of-views-carousel"
    data-variant="carousel"
>
    <div class="mcf-section-inner">
        <div class="mcf-heading-row">
            <div>
                <p class="mcf-kicker">
                    {{ data_get($section, 'kicker', __('capell-theme-first-light::sections.best_of.kicker')) }}
                </p>
                <h2>
                    {{ data_get($section, 'heading', __('capell-theme-first-light::sections.best_of.heading')) }}
                </h2>
                @if (filled(data_get($section, 'summary')))
                    <p class="mcf-lede">{{ data_get($section, 'summary') }}</p>
                @endif
            </div>

            <div
                class="swiper-controls mcf-carousel-controls"
                data-carousel-controls="first-light-best-of"
            >
                <button
                    type="button"
                    class="swiper-button-prev mcf-carousel-arrow"
                    aria-label="{{ __('capell-theme-first-light::sections.best_of.carousel_prev') }}"
                ></button>
                <button
                    type="button"
                    class="swiper-button-next mcf-carousel-arrow"
                    aria-label="{{ __('capell-theme-first-light::sections.best_of.carousel_next') }}"
                ></button>
            </div>
        </div>

        @if ($items->isNotEmpty())
            <div
                class="swiper mcf-best-of-carousel"
                data-carousel-id="first-light-best-of"
                data-carousel-per-view="1"
                data-carousel-navigation="true"
                data-carousel-pagination="true"
                data-carousel-breakpoints='{"640":{"slidesPerView":2},"1024":{"slidesPerView":3}}'
            >
                <div class="swiper-wrapper">
                    @foreach ($items as $item)
                        @php
                            $itemTitle = data_get($item, 'title', data_get($item, 'name', ''));
                            $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                            $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                            $itemAlt = data_get($item, 'imageAlt', $itemTitle);
                            $itemMeta = data_get($item, 'meta', data_get($item, 'views'));
                        @endphp

                        <article class="swiper-slide mcf-best-of-slide">
                            @if (filled($itemImage))
                                <a
                                    href="{{ $itemImage }}"
                                    class="lightbox mcf-contact-trigger"
                                    data-lightbox="{{ $itemImage }}"
                                    data-group="curation-feed"
                                    data-type="image"
                                    data-title="{{ $itemTitle }}"
                                    aria-label="{{ __('capell-theme-first-light::sections.grid.open_lightbox', ['title' => $itemTitle]) }}"
                                >
                                    <img
                                        src="{{ $itemImage }}"
                                        alt="{{ $itemAlt }}"
                                        loading="lazy"
                                        decoding="async"
                                        class="mcf-best-of-media"
                                    />
                                </a>
                            @else
                                <span
                                    class="mcf-best-of-media mcf-capture-media-empty"
                                    aria-hidden="true"
                                ></span>
                            @endif

                            <div class="mcf-best-of-body">
                                <h3>
                                    @if (filled($itemUrl))
                                        <a
                                            class="mcf-title-link"
                                            href="{{ $itemUrl }}"
                                        >
                                            {{ $itemTitle }}
                                        </a>
                                    @else
                                        {{ $itemTitle }}
                                    @endif
                                </h3>
                                @if (filled(data_get($item, 'summary', data_get($item, 'description'))))
                                    <p>{{ data_get($item, 'summary', data_get($item, 'description')) }}</p>
                                @endif
                                @if (filled($itemMeta))
                                    <span
                                        class="mcf-meta"
                                        >{{ $itemMeta }}</span
                                    >
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="swiper-pagination mcf-best-of-pagination"></div>
            </div>
        @endif

        @if (filled($buttonLabel))
            <div class="mcf-actions">
                <a
                    class="mcf-button mcf-button-secondary"
                    href="{{ $buttonUrl }}"
                >
                    {{ $buttonLabel }}
                </a>
            </div>
        @endif
    </div>
</section>
