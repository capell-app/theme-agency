@php
    $items = data_get($section, 'items', data_get($section, 'posts', []));
@endphp

<section
    id="content-listing"
    class="qwg-section"
>
    <div class="qwg-section-inner">
        <p class="qwg-kicker">
            {{ __('capell-theme-soft-focus::sections.listing.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-soft-focus::sections.listing.heading')) }}
        </h2>
        @if (filled(data_get($section, 'summary')))
            <p class="qwg-lede">{{ data_get($section, 'summary') }}</p>
        @endif

        @if (is_iterable($items) && collect($items)->isNotEmpty())
            <div class="qwg-index">
                @foreach ($items as $item)
                    @php
                        $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                        $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                        $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    @endphp

                    <article
                        class="qwg-index-row {{ filled($itemImage) ? 'qwg-index-row-media' : '' }}"
                    >
                        @if (filled($itemImage))
                            <img
                                src="{{ $itemImage }}"
                                alt="{{ $itemAlt }}"
                                loading="lazy"
                                decoding="async"
                                class="qwg-index-thumb"
                            />
                        @endif

                        <div>
                            <p class="qwg-meta">
                                {{ data_get($item, 'category', data_get($item, 'meta', '')) }}
                            </p>
                            <h3>
                                @if (filled($itemUrl))
                                    <a
                                        class="qwg-title-link"
                                        href="{{ $itemUrl }}"
                                    >
                                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                                    </a>
                                @else
                                    {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                                @endif
                            </h3>
                        </div>
                        <p>
                            {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                        </p>
                    </article>
                @endforeach
            </div>
        @else
            <p class="qwg-lede">
                {{ __('capell-theme-soft-focus::sections.listing.empty') }}
            </p>
        @endif
    </div>
</section>
