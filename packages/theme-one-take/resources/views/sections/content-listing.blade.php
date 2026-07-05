@php
    $items = data_get($section, 'items', data_get($section, 'posts', []));
@endphp

<section
    id="content-listing"
    class="ops-section"
>
    <div class="ops-section-inner">
        <p class="ops-kicker">
            {{ __('capell-theme-one-take::sections.listing.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-one-take::sections.listing.heading')) }}
        </h2>
        @if (filled(data_get($section, 'summary')))
            <p class="ops-lede">{{ data_get($section, 'summary') }}</p>
        @endif

        @if (is_iterable($items) && collect($items)->isNotEmpty())
            <div class="ops-index">
                @foreach ($items as $item)
                    @php
                        $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                        $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                        $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    @endphp

                    <article
                        class="ops-index-row {{ filled($itemImage) ? 'ops-index-row-media' : '' }}"
                    >
                        @if (filled($itemImage))
                            <img
                                src="{{ $itemImage }}"
                                alt="{{ $itemAlt }}"
                                width="128"
                                height="171"
                                loading="lazy"
                                decoding="async"
                                class="ops-index-thumb"
                            />
                        @endif

                        <div>
                            <p class="ops-meta">
                                {{ data_get($item, 'category', data_get($item, 'meta', '')) }}
                            </p>
                            <h3>
                                @if (filled($itemUrl))
                                    <a
                                        class="ops-title-link"
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
            <p class="ops-lede">
                {{ __('capell-theme-one-take::sections.listing.empty') }}
            </p>
        @endif
    </div>
</section>
