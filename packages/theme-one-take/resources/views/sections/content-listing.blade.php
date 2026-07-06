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
                    <article
                        class="ops-index-row {{ filled(data_get($item, 'image', data_get($item, 'imageUrl'))) ? 'ops-index-row-media' : '' }}"
                    >
                        @if (filled(data_get($item, 'image', data_get($item, 'imageUrl'))))
                            <img
                                src="{{ data_get($item, 'image', data_get($item, 'imageUrl')) }}"
                                alt="{{ data_get($item, 'imageAlt', data_get($item, 'title', '')) }}"
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
                                @if (filled(data_get($item, 'url', data_get($item, 'href'))))
                                    <a
                                        class="ops-title-link"
                                        href="{{ data_get($item, 'url', data_get($item, 'href')) }}"
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
