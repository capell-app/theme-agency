@php
    $heading = data_get($section, 'heading', __('capell-theme-front-row::sections.stories.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-front-row::sections.stories.summary'));
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-front-row::sections.stories.product_title'), 'summary' => __('capell-theme-front-row::sections.stories.product_summary'), 'meta' => __('capell-theme-front-row::sections.stories.product_meta')],
        ['title' => __('capell-theme-front-row::sections.stories.design_title'), 'summary' => __('capell-theme-front-row::sections.stories.design_summary'), 'meta' => __('capell-theme-front-row::sections.stories.design_meta')],
        ['title' => __('capell-theme-front-row::sections.stories.advice_title'), 'summary' => __('capell-theme-front-row::sections.stories.advice_summary'), 'meta' => __('capell-theme-front-row::sections.stories.advice_meta')],
    ]);
@endphp

<section
    id="portfolio-grid"
    class="ppc-section"
>
    <div class="ppc-section-inner">
        <p class="ppc-kicker">
            {{ __('capell-theme-front-row::sections.stories.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="ppc-lede">{{ $summary }}</p>

        <div class="ppc-grid">
            @foreach ($items as $item)
                @php
                    $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                    $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                @endphp

                <article class="ppc-card">
                    <figure class="ppc-plate">
                        <div class="ppc-plate-frame">
                            @if (filled($itemImage))
                                <img
                                    src="{{ $itemImage }}"
                                    alt="{{ $itemAlt }}"
                                    loading="lazy"
                                    decoding="async"
                                    width="400"
                                    height="300"
                                    class="ppc-plate-media"
                                />
                            @else
                                <div
                                    class="ppc-plate-media ppc-plate-media-empty"
                                    aria-hidden="true"
                                ></div>
                            @endif
                        </div>
                    </figure>
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
                    <p>
                        {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                    </p>
                    <div class="ppc-card-meta-row">
                        <span class="ppc-chip">
                            {{ data_get($item, 'meta', data_get($item, 'category', __('capell-theme-front-row::sections.stories.default_meta'))) }}
                        </span>
                        <span class="ppc-meta">
                            {{ data_get($item, 'care_note', __('capell-theme-front-row::sections.stories.care_note')) }}
                        </span>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
