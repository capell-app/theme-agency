@php
    $items = data_get($section, 'items', data_get($section, 'posts', []));
@endphp

<section
    id="content-listing"
    class="csp-section"
>
    <div class="csp-section-inner">
        <p class="csp-kicker">
            {{ __('capell-theme-open-studio::sections.listing.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-open-studio::sections.listing.heading')) }}
        </h2>

        @if (is_iterable($items) && collect($items)->isNotEmpty())
            <div class="csp-index">
                @foreach ($items as $item)
                    @php
                        $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                        $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                        $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    @endphp

                    <article
                        class="csp-index-row {{ filled($itemImage) ? 'csp-index-row-media' : '' }}"
                    >
                        @if (filled($itemImage))
                            <img
                                src="{{ $itemImage }}"
                                alt="{{ $itemAlt }}"
                                loading="lazy"
                                decoding="async"
                                class="csp-index-thumb"
                            />
                        @endif

                        <div>
                            <p class="csp-project-meta">
                                {{ data_get($item, 'category', data_get($item, 'meta', '')) }}
                            </p>
                            <h3 style="margin: 0.35rem 0 0">
                                @if (filled($itemUrl))
                                    <a
                                        class="csp-title-link"
                                        href="{{ $itemUrl }}"
                                    >
                                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                                    </a>
                                @else
                                    {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                                @endif
                            </h3>
                        </div>
                        <p style="margin: 0; color: var(--csp-muted)">
                            {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                        </p>
                    </article>
                @endforeach
            </div>
        @else
            <p class="csp-lede">
                {{ __('capell-theme-open-studio::sections.listing.empty') }}
            </p>
        @endif
    </div>
</section>
