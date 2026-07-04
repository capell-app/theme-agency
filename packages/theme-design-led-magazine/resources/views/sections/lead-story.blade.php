@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-design-led-magazine::sections.lead.house_title'), 'summary' => __('capell-theme-design-led-magazine::sections.lead.house_summary')],
        ['title' => __('capell-theme-design-led-magazine::sections.lead.studio_title'), 'summary' => __('capell-theme-design-led-magazine::sections.lead.studio_summary')],
        ['title' => __('capell-theme-design-led-magazine::sections.lead.city_title'), 'summary' => __('capell-theme-design-led-magazine::sections.lead.city_summary')],
    ]);
    $featured = collect($items)->first(fn (mixed $item): bool => filled(data_get($item, 'image', data_get($item, 'imageUrl'))));
    $featuredImage = data_get($featured, 'image', data_get($featured, 'imageUrl'));
    $featuredAlt = data_get($featured, 'imageAlt', data_get($featured, 'title', ''));
@endphp

<section
    id="lead-story"
    class="dlm-section"
>
    <div class="dlm-section-inner">
        <div class="dlm-split">
            <div>
                <p class="dlm-kicker">
                    {{ __('capell-theme-design-led-magazine::sections.lead.kicker') }}
                </p>
                <h2>
                    {{ data_get($section, 'heading', __('capell-theme-design-led-magazine::sections.lead.heading')) }}
                </h2>
                <p class="dlm-lede">
                    {{ data_get($section, 'summary', __('capell-theme-design-led-magazine::sections.lead.summary')) }}
                </p>
            </div>
            <figure class="dlm-plate">
                <div class="dlm-plate-frame">
                    @if (filled($featuredImage))
                        <img
                            src="{{ $featuredImage }}"
                            alt="{{ $featuredAlt }}"
                            loading="lazy"
                            decoding="async"
                            class="dlm-plate-media"
                        />
                    @else
                        <div
                            class="dlm-plate-media dlm-plate-media-empty dlm-plate-arch"
                            aria-hidden="true"
                        ></div>
                    @endif
                </div>
                <figcaption class="dlm-plate-caption">
                    <span class="dlm-plate-number">
                        {{ __('capell-theme-design-led-magazine::sections.plate.figure') }}
                        02
                    </span>
                    <span>
                        {{ filled($featuredAlt) ? $featuredAlt : data_get(collect($items)->first(), 'title', '') }}
                    </span>
                </figcaption>
            </figure>
        </div>

        <div class="dlm-index">
            @foreach ($items as $item)
                @php
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                @endphp

                <article class="dlm-index-row">
                    <span
                        class="dlm-numeral"
                        aria-hidden="true"
                    >
                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                    </span>
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
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
