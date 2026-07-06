{{--
    archive-wall-text, `stacked` variant: a single-column stacked read
    (rows full-width, image above body) rather than the default multi-column
    masonry wall — for placements narrower than the homepage (e.g. a detail
    page rail) where the column layout would feel cramped.
--}}

@php
    $items = data_get($section, 'items', []);
@endphp

<section
    id="archive-wall"
    class="rwi-section"
    data-widget="archive-wall-text"
    data-variant="stacked"
>
    <div class="rwi-section-inner">
        <span class="rwi-index-tag">01</span>
        <p class="rwi-kicker">
            {{ __('capell-theme-off-grid::sections.wall.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-off-grid::sections.wall.heading')) }}
        </h2>
        <p class="rwi-lede">
            {{ data_get($section, 'summary', __('capell-theme-off-grid::sections.wall.summary')) }}
        </p>

        @if (is_iterable($items) && collect($items)->isNotEmpty())
            <div
                class="rwi-wall rwi-wall-stacked"
                style="margin-top: 2rem"
            >
                @foreach ($items as $item)
                    @php
                        $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                        $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                        $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                    @endphp

                    <article class="rwi-wall-item rwi-wall-item-stacked">
                        @if (filled($itemImage))
                            <img
                                src="{{ $itemImage }}"
                                alt="{{ $itemAlt ?: data_get($item, 'title', data_get($item, 'name', '')) }}"
                                loading="lazy"
                                decoding="async"
                                class="rwi-wall-media"
                            />
                        @endif

                        <div class="rwi-wall-body">
                            <p class="rwi-wall-meta">
                                {{ data_get($item, 'meta', __('capell-theme-off-grid::sections.wall.entry_meta')) }}
                            </p>
                            <h3>
                                @if (filled($itemUrl))
                                    <a
                                        class="rwi-title-link"
                                        href="{{ $itemUrl }}"
                                    >
                                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                                    </a>
                                @else
                                    {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                                @endif
                            </h3>
                            <p>
                                {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                            </p>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <p
                class="rwi-lede"
                style="margin-top: 2rem"
            >
                {{ data_get($section, 'empty', __('capell-theme-off-grid::sections.listing.empty')) }}
            </p>
        @endif
    </div>
</section>
