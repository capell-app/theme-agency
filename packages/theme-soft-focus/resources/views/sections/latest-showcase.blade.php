@php
    $heading = data_get($section, 'heading', __('capell-theme-soft-focus::sections.showcase.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-soft-focus::sections.showcase.summary'));
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-soft-focus::sections.showcase.marlow_title'), 'summary' => __('capell-theme-soft-focus::sections.showcase.marlow_summary'), 'meta' => __('capell-theme-soft-focus::sections.showcase.marlow_meta')],
        ['title' => __('capell-theme-soft-focus::sections.showcase.tideline_title'), 'summary' => __('capell-theme-soft-focus::sections.showcase.tideline_summary'), 'meta' => __('capell-theme-soft-focus::sections.showcase.tideline_meta')],
    ]);
@endphp

<section
    id="latest-showcase"
    class="qwg-section"
>
    <div class="qwg-section-inner">
        <p class="qwg-kicker">
            {{ __('capell-theme-soft-focus::sections.showcase.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="qwg-lede">{{ $summary }}</p>

        <div class="qwg-wall-grid">
            @foreach ($items as $item)
                @php
                    $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                    $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                @endphp

                <figure class="qwg-frame">
                    <div class="qwg-frame-mat">
                        @if (filled($itemImage))
                            <img
                                src="{{ $itemImage }}"
                                alt="{{ $itemAlt }}"
                                loading="lazy"
                                decoding="async"
                                class="qwg-frame-media"
                            />
                        @else
                            <div
                                class="qwg-frame-media"
                                aria-hidden="true"
                            ></div>
                        @endif
                    </div>
                    <figcaption class="qwg-frame-caption">
                        <span>
                            <strong>
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
                            </strong>
                            {{ data_get($item, 'summary', '') }}
                        </span>
                        <span class="qwg-frame-number">
                            {{ data_get($item, 'meta', data_get($item, 'category', __('capell-theme-soft-focus::sections.showcase.default_meta'))) }}
                        </span>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
