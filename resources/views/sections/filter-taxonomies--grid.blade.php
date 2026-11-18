@php
    use Capell\Core\Support\Security\PublicUrlSanitizer;

    /**
     * filter-taxonomies-grid (Wave 4c signature widget): the discipline
     * filters double as a small salon wall of their own -- each chip sits
     * above a proportionally sized tile, its span deterministically weighted
     * by the discipline's own item count (more entries -> a wider tile),
     * never randomised (§0.1). Falls back cleanly to a single-span tile when
     * no count is supplied.
     */
    $heading = data_get($section, 'heading');
    $items = collect(data_get($section, 'items', []))
        ->filter(fn (mixed $item): bool => filled(data_get($item, 'title', data_get($item, 'name'))))
        ->take(50)
        ->values();

    $counts = $items
        ->map(fn (mixed $item): int => (int) data_get($item, 'count', 0))
        ->filter(fn (int $count): bool => $count > 0);
    $maxCount = $counts->isNotEmpty() ? $counts->max() : 0;
@endphp

@if ($items->isNotEmpty())
    <section
        id="filters"
        class="ppc-section"
        data-widget="filter-taxonomies-grid"
    >
        <div class="ppc-section-inner">
            @if (filled(data_get($section, 'kicker')))
                <p class="ppc-kicker">{{ data_get($section, 'kicker') }}</p>
            @endif

            @if (filled($heading))
                <h2>{{ $heading }}</h2>
            @endif

            <div
                class="ppc-filters"
                role="list"
            >
                @foreach ($items as $item)
                    @php
                        $filterUrl = PublicUrlSanitizer::sanitize(data_get($item, 'url', data_get($item, 'href')));
                        $chipTag = filled($filterUrl) ? 'a' : 'span';
                        $count = data_get($item, 'count');
                    @endphp

                    <{{ $chipTag }}
                        class="ppc-filter-chip"
                        @if (filled($filterUrl)) href="{{ $filterUrl }}" @endif
                        role="listitem"
                    >
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                        @if (filled($count))
                            <span
                                class="ppc-filter-chip-count"
                                >{{ $count }}</span
                            >
                        @endif
                    </{{ $chipTag }}>
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
                        @if (filled(data_get($item, 'summary')))
                            <p>{{ data_get($item, 'summary') }}</p>
                        @endif
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif
