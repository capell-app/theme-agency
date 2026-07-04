@php
    $items = data_get($section, 'items', []);
@endphp

<section
    id="archive-wall"
    class="rwi-section"
>
    <div class="rwi-section-inner">
        <span class="rwi-index-tag">01</span>
        <p class="rwi-kicker">
            {{ __('capell-theme-raw-index::sections.wall.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-raw-index::sections.wall.heading')) }}
        </h2>
        <p class="rwi-lede">
            {{ data_get($section, 'summary', __('capell-theme-raw-index::sections.wall.summary')) }}
        </p>

        @if (is_iterable($items) && collect($items)->isNotEmpty())
            <div
                class="rwi-wall"
                style="margin-top: 2rem"
            >
                @foreach ($items as $item)
                    @php
                        $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                        $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                        $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                    @endphp

                    <article class="rwi-wall-item">
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
                                {{ data_get($item, 'meta', __('capell-theme-raw-index::sections.wall.entry_meta')) }}
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
                {{ data_get($section, 'empty', __('capell-theme-raw-index::sections.listing.empty')) }}
            </p>
        @endif
    </div>
</section>
