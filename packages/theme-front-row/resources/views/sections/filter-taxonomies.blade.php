@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-front-row::sections.topics.product_title'), 'summary' => __('capell-theme-front-row::sections.topics.product_summary')],
        ['title' => __('capell-theme-front-row::sections.topics.design_title'), 'summary' => __('capell-theme-front-row::sections.topics.design_summary')],
        ['title' => __('capell-theme-front-row::sections.topics.advice_title'), 'summary' => __('capell-theme-front-row::sections.topics.advice_summary')],
        ['title' => __('capell-theme-front-row::sections.topics.culture_title'), 'summary' => __('capell-theme-front-row::sections.topics.culture_summary')],
    ]);
@endphp

<section
    id="filters"
    class="ppc-section"
>
    <div class="ppc-section-inner">
        <p class="ppc-kicker">
            {{ __('capell-theme-front-row::sections.topics.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-front-row::sections.topics.heading')) }}
        </h2>

        <div class="ppc-filters">
            @foreach ($items as $item)
                @php
                    $filterUrl = data_get($item, 'url', data_get($item, 'href', '#filters'));
                    $count = data_get($item, 'count');
                @endphp

                <a
                    class="ppc-filter-chip"
                    href="{{ $filterUrl }}"
                >
                    {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    @if (filled($count))
                        <span class="ppc-filter-chip-count">{{ $count }}</span>
                    @endif
                </a>
            @endforeach
        </div>

        <div class="ppc-grid">
            @foreach ($items as $item)
                <article class="ppc-card">
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
