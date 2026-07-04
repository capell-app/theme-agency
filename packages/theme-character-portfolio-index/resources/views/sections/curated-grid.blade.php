@php
    $heading = data_get($section, 'heading', __('capell-theme-character-portfolio-index::sections.stories.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-character-portfolio-index::sections.stories.summary'));
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-character-portfolio-index::sections.stories.product_title'), 'summary' => __('capell-theme-character-portfolio-index::sections.stories.product_summary'), 'meta' => __('capell-theme-character-portfolio-index::sections.stories.product_meta')],
        ['title' => __('capell-theme-character-portfolio-index::sections.stories.design_title'), 'summary' => __('capell-theme-character-portfolio-index::sections.stories.design_summary'), 'meta' => __('capell-theme-character-portfolio-index::sections.stories.design_meta')],
        ['title' => __('capell-theme-character-portfolio-index::sections.stories.advice_title'), 'summary' => __('capell-theme-character-portfolio-index::sections.stories.advice_summary'), 'meta' => __('capell-theme-character-portfolio-index::sections.stories.advice_meta')],
    ]);
@endphp

<section
    id="curated-grid"
    class="cpi-section"
>
    <div class="cpi-section-inner">
        <p class="cpi-kicker">
            {{ __('capell-theme-character-portfolio-index::sections.stories.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="cpi-lede">{{ $summary }}</p>

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
                                    class="cpi-plate-media"
                                />
                            @else
                                <div
                                    class="cpi-plate-media cpi-plate-media-empty"
                                    aria-hidden="true"
                                ></div>
                            @endif
                        </div>
                    </figure>
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
                    <p class="cpi-meta">
                        {{ data_get($item, 'meta', data_get($item, 'category', __('capell-theme-character-portfolio-index::sections.stories.default_meta'))) }}
                    </p>
                    <p>
                        {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                    </p>
                    <span class="cpi-care-note">
                        {{ data_get($item, 'care_note', __('capell-theme-character-portfolio-index::sections.stories.care_note')) }}
                    </span>
                </article>
            @endforeach
        </div>
    </div>
</section>
