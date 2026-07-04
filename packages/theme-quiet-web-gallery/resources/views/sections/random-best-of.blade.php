@php
    $item = data_get($section, 'items.0', [
        'title' => __('capell-theme-quiet-web-gallery::sections.spotlight.pick_title'),
        'summary' => __('capell-theme-quiet-web-gallery::sections.spotlight.pick_summary'),
    ]);
    $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
    $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
@endphp

<section
    id="random-best-of"
    class="qwg-section qwg-section-dark"
>
    <div class="qwg-section-inner qwg-split">
        <figure class="qwg-frame">
            <div class="qwg-frame-mat">
                @if (filled($itemImage))
                    <img
                        src="{{ $itemImage }}"
                        alt="{{ $itemAlt }}"
                        loading="lazy"
                        decoding="async"
                        class="qwg-frame-media qwg-frame-media-wide"
                    />
                @else
                    <div
                        class="qwg-frame-media qwg-frame-media-wide"
                        aria-hidden="true"
                    ></div>
                @endif
            </div>
            <figcaption class="qwg-frame-caption">
                <span class="qwg-frame-number">
                    {{ __('capell-theme-quiet-web-gallery::sections.spotlight.label') }}
                </span>
                <span>
                    {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                </span>
            </figcaption>
        </figure>

        <div>
            <p class="qwg-kicker">
                {{ __('capell-theme-quiet-web-gallery::sections.spotlight.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-quiet-web-gallery::sections.spotlight.heading')) }}
            </h2>
            <p class="qwg-lede">
                {{ data_get($section, 'summary', __('capell-theme-quiet-web-gallery::sections.spotlight.summary')) }}
            </p>
            <p class="qwg-lede">
                {{ data_get($item, 'summary', '') }}
            </p>
        </div>
    </div>
</section>
