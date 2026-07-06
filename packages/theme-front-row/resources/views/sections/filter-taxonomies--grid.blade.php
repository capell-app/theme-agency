@php
    /**
     * filter-taxonomies-grid (Wave 4c signature widget): the discipline
     * filters double as a small salon wall of their own -- each chip sits
     * above a proportionally sized tile, its span deterministically weighted
     * by the discipline's own item count (more entries -> a wider tile),
     * never randomised (§0.1). Falls back cleanly to a single-span tile when
     * no count is supplied.
     */
    $heading = data_get($section, 'heading', __('capell-theme-front-row::sections.topics.heading'));
    $items = collect(data_get($section, 'items', [
        ['title' => __('capell-theme-front-row::sections.topics.product_title'), 'summary' => __('capell-theme-front-row::sections.topics.product_summary')],
        ['title' => __('capell-theme-front-row::sections.topics.design_title'), 'summary' => __('capell-theme-front-row::sections.topics.design_summary')],
        ['title' => __('capell-theme-front-row::sections.topics.advice_title'), 'summary' => __('capell-theme-front-row::sections.topics.advice_summary')],
        ['title' => __('capell-theme-front-row::sections.topics.culture_title'), 'summary' => __('capell-theme-front-row::sections.topics.culture_summary')],
    ]))->take(50)->values();

    $counts = $items
        ->map(fn (mixed $item): int => (int) data_get($item, 'count', 0))
        ->filter(fn (int $count): bool => $count > 0);
    $maxCount = $counts->isNotEmpty() ? $counts->max() : 0;
@endphp

<section
    id="filters"
    class="ppc-section"
    data-widget="filter-taxonomies-grid"
>
    <div class="ppc-section-inner">
        <p class="ppc-kicker">
            {{ __('capell-theme-front-row::sections.topics.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>

        <div
            class="ppc-filters"
            role="list"
        >
            @foreach ($items as $item)
                @php
                    $filterUrl = data_get($item, 'url', data_get($item, 'href', '#filters'));
                    $count = data_get($item, 'count');
                @endphp

                <a
                    class="ppc-filter-chip"
                    href="{{ $filterUrl }}"
                    role="listitem"
                >
                    {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    @if (filled($count))
                        <span class="ppc-filter-chip-count">{{ $count }}</span>
                    @endif
                </a>
            @endforeach
        </div>

        <div
            class="ppc-grid ppc-wall-taxonomy-grid"
            role="list"
        >
            @foreach ($items as $item)
                @php
                    $itemCount = (int) data_get($item, 'count', 0);
                    $columnSpan = $maxCount > 0 && $itemCount >= (int) ceil($maxCount * 0.6) ? 2 : 1;
                @endphp

                <article
                    class="ppc-card ppc-wall-taxonomy-tile"
                    style="--ppc-taxonomy-col-span: {{ $columnSpan }}"
                    role="listitem"
                >
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
