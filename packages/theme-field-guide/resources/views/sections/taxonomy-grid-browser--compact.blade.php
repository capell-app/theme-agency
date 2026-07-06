{{--
    taxonomy-grid-browser (compact variant): identical deterministic ordering
    and reshuffle contract as the default view, but a tighter grid (no toolbar
    header rail) for pages that already carry the facet board (e.g. the
    directory page, which places taxonomy-navigation directly above it).
--}}
@php
    $heading = data_get($section, 'heading', __('capell-theme-field-guide::sections.grid_browser.heading'));
    $pageSeed = (string) data_get($section, 'pageSeed', data_get($section, 'slug', 'field-guide-grid-browser'));
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
    class="fga-section fga-section-inner-tight"
    data-widget="taxonomy-grid-browser"
    data-variant="compact"
>
    <div class="fga-section-inner fga-section-inner-tight">
        <div class="fga-section-head">
            <div class="fga-section-head-copy">
                <p class="fga-kicker">
                    {{ __('capell-theme-field-guide::sections.grid_browser.kicker') }}
                </p>
                <h2>{{ $heading }}</h2>
            </div>
        </div>

        <div
            class="fga-tessellation-grid fga-tessellation-grid-compact"
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
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
