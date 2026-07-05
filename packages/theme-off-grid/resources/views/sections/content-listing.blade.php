@php
    $items = data_get($section, 'items', data_get($section, 'posts', []));
@endphp

<section
    id="content-listing"
    class="rwi-section"
>
    <div class="rwi-section-inner">
        <p class="rwi-kicker">
            {{ __('capell-theme-off-grid::sections.listing.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-off-grid::sections.listing.heading')) }}
        </h2>
        @if (filled(data_get($section, 'summary')))
            <p class="rwi-lede">{{ data_get($section, 'summary') }}</p>
        @endif

        @if (is_iterable($items) && collect($items)->isNotEmpty())
            <div
                class="rwi-grid"
                style="margin-top: 2rem"
            >
                @foreach ($items as $item)
                    @php
                        $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                        $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                        $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    @endphp

                    <article class="rwi-card">
                        @if (filled($itemImage))
                            <img
                                src="{{ $itemImage }}"
                                alt="{{ $itemAlt ?: data_get($item, 'title', data_get($item, 'name', '')) }}"
                                loading="lazy"
                                decoding="async"
                                class="rwi-card-media"
                            />
                        @endif

                        <p class="rwi-meta">
                            {{ data_get($item, 'category', data_get($item, 'meta', '')) }}
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
                    </article>
                @endforeach
            </div>
        @else
            <p
                class="rwi-lede"
                style="margin-top: 2rem"
            >
                {{ __('capell-theme-off-grid::sections.listing.empty') }}
            </p>
        @endif
    </div>
</section>
