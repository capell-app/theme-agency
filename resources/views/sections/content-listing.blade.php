@php
    $items = data_get($section, 'items', data_get($section, 'posts', []));
@endphp

<section
    id="content-listing"
    class="ppc-section"
>
    <div class="ppc-section-inner">
        <p class="ppc-kicker">
            {{ __('capell-theme-agency::sections.listing.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-agency::sections.listing.heading')) }}
        </h2>
        @if (is_iterable($items) && collect($items)->isNotEmpty())
            <div class="ppc-index">
                @foreach ($items as $item)
                    @php
                        $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                        $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                        $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    @endphp

                    <article
                        class="ppc-index-row {{ filled($itemImage) ? 'ppc-index-row-media' : '' }}"
                    >
                        @if (filled($itemImage))
                            <img
                                src="{{ $itemImage }}"
                                alt="{{ $itemAlt }}"
                                loading="lazy"
                                decoding="async"
                                width="176"
                                height="132"
                                class="ppc-index-thumb"
                            />
                        @endif

                        <div>
                            <p class="ppc-meta">
                                {{ data_get($item, 'category', data_get($item, 'meta', '')) }}
                            </p>
                            <h3>
                                @if (filled($itemUrl))
                                    <a
                                        class="ppc-title-link"
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
            <p class="ppc-lede">
                {{ __('capell-theme-agency::sections.listing.empty') }}
            </p>
        @endif
    </div>
</section>
