@php
    $items = data_get($section, 'items', data_get($section, 'posts', []));
@endphp

<section
    id="content-listing"
    class="dps-section"
>
    <div class="dps-section-inner">
        <p class="dps-eyebrow">
            {{ __('capell-theme-dark-product-system::sections.listing.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-dark-product-system::sections.listing.heading')) }}
        </h2>
        @if (filled(data_get($section, 'summary')))
            <p class="dps-lede">{{ data_get($section, 'summary') }}</p>
        @endif

        @if (is_iterable($items) && collect($items)->isNotEmpty())
            <div
                class="dps-grid dps-grid-2"
                style="margin-top: clamp(2rem, 4vw, 3rem)"
            >
                @foreach ($items as $item)
                    @php
                        $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                        $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                        $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    @endphp

                    <article class="dps-card dps-listing-card">
                        @if (filled($itemImage))
                            <img
                                src="{{ $itemImage }}"
                                alt="{{ $itemAlt }}"
                                loading="lazy"
                                decoding="async"
                                class="dps-listing-media"
                            />
                        @endif

                        <div class="dps-listing-body">
                            <p class="dps-meta">
                                {{ data_get($item, 'category', data_get($item, 'meta', '')) }}
                            </p>
                            <h3>
                                @if (filled($itemUrl))
                                    <a
                                        class="dps-title-link"
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
            <p class="dps-lede">
                {{ __('capell-theme-dark-product-system::sections.listing.empty') }}
            </p>
        @endif
    </div>
</section>
