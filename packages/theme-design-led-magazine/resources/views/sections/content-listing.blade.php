@php
    $items = data_get($section, 'items', data_get($section, 'posts', []));
@endphp

<section
    id="content-listing"
    class="dlm-section"
>
    <div class="dlm-section-inner">
        <p class="dlm-kicker">
            {{ __('capell-theme-design-led-magazine::sections.listing.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-design-led-magazine::sections.listing.heading')) }}
        </h2>
        @if (filled(data_get($section, 'summary')))
            <p class="dlm-lede">{{ data_get($section, 'summary') }}</p>
        @endif

        @if (is_iterable($items) && collect($items)->isNotEmpty())
            <div class="dlm-index">
                @foreach ($items as $item)
                    @php
                        $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                        $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                        $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    @endphp

                    <article
                        class="dlm-index-row {{ filled($itemImage) ? 'dlm-index-row-media' : '' }}"
                    >
                        <span
                            class="dlm-numeral"
                            aria-hidden="true"
                        >
                            {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                        </span>
                        @if (filled($itemImage))
                            <img
                                src="{{ $itemImage }}"
                                alt="{{ $itemAlt }}"
                                loading="lazy"
                                decoding="async"
                                class="dlm-index-thumb"
                            />
                        @endif

                        <div>
                            <p class="dlm-meta">
                                {{ data_get($item, 'category', data_get($item, 'meta', '')) }}
                            </p>
                            <h3>
                                @if (filled($itemUrl))
                                    <a
                                        class="dlm-title-link"
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
            <div class="dlm-empty">
                <span
                    class="dlm-empty-mark"
                    aria-hidden="true"
                >
                    {{ __('capell-theme-design-led-magazine::sections.listing.empty_mark') }}
                </span>
                <p class="dlm-empty-note">
                    {{ __('capell-theme-design-led-magazine::sections.listing.empty_note') }}
                </p>
                <a
                    class="dlm-button dlm-button-secondary"
                    href="/"
                >
                    {{ __('capell-theme-design-led-magazine::sections.listing.empty_cta') }}
                </a>
            </div>
        @endif
    </div>
</section>
