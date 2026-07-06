{{--
    taxonomy-grid-browser (Wave 4c signature widget #1, headline mechanic
    "tessellation grid"): a dense taxonomy reflow grid. Guardrail §0.1: the
    reshuffle is never Math.random() at render or in the client — every card
    carries its full facet record as data attributes plus a deterministic
    `data-sort-key` seeded from a crc32 hash of the page seed and the item's
    own identity, so html-cache always serves identical HTML and the same
    facet click always produces the same order. Guardrail §0.3: capped at 50
    items. Guardrail §0.8: reflow uses CSS grid auto-fit only — no CSS
    masonry (`columns`), so there is no masonry fallback to maintain.
--}}
@php
    $heading = data_get($section, 'heading', __('capell-theme-field-guide::sections.grid_browser.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-field-guide::sections.grid_browser.summary'));
    $pageSeed = (string) data_get($section, 'pageSeed', data_get($section, 'slug', 'field-guide-grid-browser'));
    $facetFilters = collect(data_get($section, 'facets', []))
        ->filter(fn (mixed $facetGroup): bool => filled(data_get($facetGroup, 'facet')))
        ->values();
    $items = collect(data_get($section, 'items', []))
        ->filter(fn (mixed $item): bool => filled(data_get($item, 'title', data_get($item, 'name'))))
        ->take(50)
        ->values();
    $orderedItems = $items
        ->map(fn (array $item, int $index): array => [
            'item' => $item,
            'sortKey' => crc32($pageSeed . '::' . (string) (data_get($item, 'id') ?? data_get($item, 'title') ?? $index) . '::' . $index),
            'originalIndex' => $index,
        ])
        ->sort(fn (array $left, array $right): int => $left['sortKey'] <=> $right['sortKey'] ?: $left['originalIndex'] <=> $right['originalIndex'])
        ->values();
@endphp

<section
    id="taxonomy-grid-browser"
    class="fga-section"
    data-widget="taxonomy-grid-browser"
>
    <div class="fga-section-inner">
        <div class="fga-section-head">
            <div class="fga-section-head-copy">
                <p class="fga-kicker">
                    {{ __('capell-theme-field-guide::sections.grid_browser.kicker') }}
                </p>
                <h2>{{ $heading }}</h2>
                <p class="fga-lede">{{ $summary }}</p>
            </div>
            <p class="fga-mono-note">
                {{ __('capell-theme-field-guide::sections.grid_browser.count_note') }}
            </p>
        </div>

        @if ($facetFilters->isNotEmpty())
            <div
                class="fga-grid-browser-toolbar"
                role="group"
                aria-label="{{ __('capell-theme-field-guide::sections.grid_browser.filter_label') }}"
            >
                <button
                    type="button"
                    class="fga-grid-browser-filter is-active"
                    data-grid-browser-facet="all"
                    aria-pressed="true"
                >
                    {{ __('capell-theme-field-guide::sections.grid_browser.all_label') }}
                </button>
                @foreach ($facetFilters as $facetGroup)
                    <button
                        type="button"
                        class="fga-grid-browser-filter"
                        data-grid-browser-facet="{{ data_get($facetGroup, 'facet') }}"
                        aria-pressed="false"
                    >
                        {{ data_get($facetGroup, 'label', data_get($facetGroup, 'facet')) }}
                    </button>
                @endforeach
            </div>
        @endif

        <div
            class="fga-tessellation-grid"
            data-grid-browser
            data-grid-browser-seed="{{ $pageSeed }}"
        >
            @foreach ($orderedItems as $entry)
                @php
                    $item = $entry['item'];
                    $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                    $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                    $itemFacets = collect(data_get($item, 'tags', []))->pluck('facet')->filter()->implode(' ');
                @endphp

                <article
                    class="fga-capture fga-tessellation-tile"
                    style="--fga-tile-order: {{ $entry['originalIndex'] }}"
                    data-grid-browser-item
                    data-grid-browser-facets="{{ $itemFacets }}"
                >
                    @if (filled($itemImage))
                        <img
                            src="{{ $itemImage }}"
                            alt="{{ $itemAlt }}"
                            loading="lazy"
                            decoding="async"
                            class="fga-capture-media"
                        />
                    @else
                        <div
                            class="fga-capture-media fga-capture-media-empty"
                            aria-hidden="true"
                        ></div>
                    @endif
                    <div class="fga-capture-body">
                        <div class="fga-capture-title-row">
                            <h3>
                                @if (filled($itemUrl))
                                    <a
                                        class="fga-title-link"
                                        href="{{ $itemUrl }}"
                                    >
                                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                                    </a>
                                @else
                                    {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                                @endif
                            </h3>
                            <span class="fga-capture-index">
                                {{ str_pad((string) ($entry['originalIndex'] + 1), 2, '0', STR_PAD_LEFT) }}
                            </span>
                        </div>
                        @if (filled(data_get($item, 'summary', data_get($item, 'description'))))
                            <p>
                                {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                            </p>
                        @endif

                        @if (is_iterable(data_get($item, 'tags', [])) && collect(data_get($item, 'tags', []))->isNotEmpty())
                            <ul class="fga-chip-row">
                                @foreach (data_get($item, 'tags', []) as $tag)
                                    <li>
                                        <span
                                            class="fga-chip fga-chip-{{ data_get($tag, 'facet', 'type') }}"
                                        >
                                            {{ data_get($tag, 'label', is_string($tag) ? $tag : '') }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <p
            class="fga-mono-note fga-grid-browser-empty"
            data-grid-browser-empty
            hidden
        >
            {{ __('capell-theme-field-guide::sections.grid_browser.no_matches') }}
        </p>
    </div>
</section>
