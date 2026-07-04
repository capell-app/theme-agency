@php
    $heading = data_get($section, 'heading', __('capell-theme-character-portfolio-index::sections.events.heading'));
    $items = data_get($section, 'items', data_get($section, 'stories', [
        ['title' => __('capell-theme-character-portfolio-index::sections.events.planning_title'), 'summary' => __('capell-theme-character-portfolio-index::sections.events.planning_summary'), 'meta' => __('capell-theme-character-portfolio-index::sections.events.planning_meta')],
        ['title' => __('capell-theme-character-portfolio-index::sections.events.workshop_title'), 'summary' => __('capell-theme-character-portfolio-index::sections.events.workshop_summary'), 'meta' => __('capell-theme-character-portfolio-index::sections.events.workshop_meta')],
        ['title' => __('capell-theme-character-portfolio-index::sections.events.systems_title'), 'summary' => __('capell-theme-character-portfolio-index::sections.events.systems_summary'), 'meta' => __('capell-theme-character-portfolio-index::sections.events.systems_meta')],
    ]));
@endphp

<section
    id="related-recommendations"
    class="cpi-section"
>
    <div class="cpi-section-inner">
        <p class="cpi-kicker">
            {{ __('capell-theme-character-portfolio-index::sections.events.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        @if (filled(data_get($section, 'summary')))
            <p class="cpi-lede">{{ data_get($section, 'summary') }}</p>
        @endif

        <div class="cpi-grid">
            @foreach ($items as $item)
                @php
                    $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                    $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                @endphp

                <article class="cpi-card">
                    <figure class="cpi-plate">
                        <div class="cpi-plate-frame">
                            @if (filled($itemImage))
                                <img
                                    src="{{ $itemImage }}"
                                    alt="{{ $itemAlt }}"
                                    loading="lazy"
                                    decoding="async"
                                    class="cpi-plate-media cpi-plate-media-square"
                                />
                            @else
                                <div
                                    class="cpi-plate-media cpi-plate-media-square cpi-plate-media-empty"
                                    aria-hidden="true"
                                ></div>
                            @endif
                        </div>
                    </figure>
                    <p class="cpi-meta">
                        {{ data_get($item, 'meta', data_get($item, 'category', '')) }}
                    </p>
                    <h3>
                        @if (filled($itemUrl))
                            <a
                                class="cpi-title-link"
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
                </article>
            @endforeach
        </div>
    </div>
</section>
