@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-quiet-web-gallery::sections.categories.minimal_title'), 'summary' => __('capell-theme-quiet-web-gallery::sections.categories.minimal_summary'), 'count' => __('capell-theme-quiet-web-gallery::sections.categories.minimal_count')],
        ['title' => __('capell-theme-quiet-web-gallery::sections.categories.editorial_title'), 'summary' => __('capell-theme-quiet-web-gallery::sections.categories.editorial_summary'), 'count' => __('capell-theme-quiet-web-gallery::sections.categories.editorial_count')],
        ['title' => __('capell-theme-quiet-web-gallery::sections.categories.portfolio_title'), 'summary' => __('capell-theme-quiet-web-gallery::sections.categories.portfolio_summary'), 'count' => __('capell-theme-quiet-web-gallery::sections.categories.portfolio_count')],
    ]);
@endphp

<section
    id="style-type-categories"
    class="qwg-section qwg-section-field"
>
    <div class="qwg-section-inner">
        <p class="qwg-kicker">
            {{ __('capell-theme-quiet-web-gallery::sections.categories.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-quiet-web-gallery::sections.categories.heading')) }}
        </h2>
        <p class="qwg-lede">
            {{ data_get($section, 'summary', __('capell-theme-quiet-web-gallery::sections.categories.summary')) }}
        </p>

        <div class="qwg-index">
            @foreach ($items as $item)
                @php
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                @endphp

                <article class="qwg-index-row">
                    <div>
                        <h3>
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
                        </h3>
                        <p class="qwg-meta">
                            {{ data_get($item, 'count', '') }}
                        </p>
                    </div>
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
