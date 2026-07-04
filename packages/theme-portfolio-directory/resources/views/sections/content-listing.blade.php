@php
    $items = data_get($section, 'items', data_get($section, 'posts', []));
@endphp

<section
    id="content-listing"
    class="pfd-section"
>
    <div class="pfd-section-inner">
        <p class="pfd-eyebrow">
            {{ data_get($section, 'eyebrow', __('capell-theme-portfolio-directory::sections.listing.eyebrow')) }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-portfolio-directory::sections.listing.heading')) }}
        </h2>
        @if (filled(data_get($section, 'summary')))
            <p class="pfd-lede">{{ data_get($section, 'summary') }}</p>
        @endif

        @if (is_iterable($items) && collect($items)->isNotEmpty())
            <div class="pfd-index">
                @foreach ($items as $item)
                    @php
                        $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                        $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                        $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    @endphp

                    <article class="pfd-index-row">
                        @if (filled($itemImage))
                            <img
                                src="{{ $itemImage }}"
                                alt="{{ $itemAlt }}"
                                loading="lazy"
                                decoding="async"
                                class="pfd-photo pfd-index-thumb"
                            />
                        @else
                            <div
                                class="pfd-photo pfd-photo-empty pfd-index-thumb"
                                aria-hidden="true"
                            ></div>
                        @endif

                        <div>
                            <p class="pfd-meta">
                                {{ data_get($item, 'category', data_get($item, 'meta', '')) }}
                            </p>
                            <h3>
                                @if (filled($itemUrl))
                                    <a
                                        class="pfd-title-link"
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
            <div class="pfd-empty-state">
                <span
                    class="pfd-empty-state-mark"
                    aria-hidden="true"
                ></span>
                <p class="pfd-empty-state-title">
                    {{ __('capell-theme-portfolio-directory::sections.listing.empty_title') }}
                </p>
                <p>
                    {{ __('capell-theme-portfolio-directory::sections.listing.empty') }}
                </p>
            </div>
        @endif
    </div>
</section>
