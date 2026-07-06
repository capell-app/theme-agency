{{--
    style-type-categories-scattered (Wave 4c signature widget #2, headline
    mechanic "scatter light table" applied to category tiles): same seeded
    scatter technique as browse-panels-scatter, applied to the style/type
    category index instead of the quiet even rows the default variant uses.
    Guardrail §0.1: deterministic crc32-seeded rotation/offset/z-index, no
    Math.random(). Guardrail §0.5: click-to-front via native focus (tabindex
    + :focus-within), keyboard-reachable in DOM order despite the overlap.
--}}
@php
    $pageSeed = (string) data_get($section, 'pageSeed', data_get($section, 'slug', 'soft-focus-style-type-categories'));
    $items = collect(data_get($section, 'items', [
        ['title' => __('capell-theme-soft-focus::sections.categories.minimal_title'), 'summary' => __('capell-theme-soft-focus::sections.categories.minimal_summary'), 'count' => __('capell-theme-soft-focus::sections.categories.minimal_count')],
        ['title' => __('capell-theme-soft-focus::sections.categories.editorial_title'), 'summary' => __('capell-theme-soft-focus::sections.categories.editorial_summary'), 'count' => __('capell-theme-soft-focus::sections.categories.editorial_count')],
        ['title' => __('capell-theme-soft-focus::sections.categories.portfolio_title'), 'summary' => __('capell-theme-soft-focus::sections.categories.portfolio_summary'), 'count' => __('capell-theme-soft-focus::sections.categories.portfolio_count')],
    ]))
        ->filter(fn (mixed $item): bool => filled(data_get($item, 'title', data_get($item, 'name'))))
        ->values();

    $scatteredItems = $items->map(function (array $item, int $index) use ($pageSeed): array {
        $identity = (string) (data_get($item, 'title') ?? data_get($item, 'name') ?? $index);
        $seed = crc32($pageSeed . '::style-type-categories-scattered::' . $identity . '::' . $index);

        return [
            'item' => $item,
            'index' => $index,
            'rotation' => (($seed % 500) / 100) - 2.5,
            'offsetInline' => (($seed >> 3) % 200) - 100,
            'offsetBlock' => (($seed >> 8) % 130) - 65,
            'zIndex' => 10 + ($seed % 20),
        ];
    });
@endphp

<section
    id="style-type-categories"
    class="qwg-section qwg-section-field qwg-scatter-section"
    data-widget="style-type-categories-scattered"
    data-variant="scattered"
>
    <div class="qwg-section-inner">
        <p class="qwg-kicker">
            {{ __('capell-theme-soft-focus::sections.categories.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-soft-focus::sections.categories.heading')) }}
        </h2>
        <p class="qwg-lede">
            {{ data_get($section, 'summary', __('capell-theme-soft-focus::sections.categories.summary')) }}
        </p>

        <div
            class="qwg-scatter-table qwg-scatter-table-categories"
            data-scatter-seed="{{ $pageSeed }}"
        >
            @foreach ($scatteredItems as $entry)
                @php
                    $item = $entry['item'];
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                @endphp

                <article
                    class="qwg-index-row qwg-scatter-tile"
                    tabindex="0"
                    style="--qwg-scatter-rotate: {{ $entry['rotation'] }}deg; --qwg-scatter-x: {{ $entry['offsetInline'] }}px; --qwg-scatter-y: {{ $entry['offsetBlock'] }}px; --qwg-scatter-z: {{ $entry['zIndex'] }};"
                    data-scatter-tile
                >
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
