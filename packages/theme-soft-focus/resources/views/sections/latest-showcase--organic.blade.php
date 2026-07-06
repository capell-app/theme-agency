{{--
    latest-showcase-organic (Wave 4c signature widget #3, headline mechanic
    "scatter light table" applied to the recently-hung showcase): the same
    seeded scatter as browse-panels-scatter/style-type-categories-scattered,
    over the framed showcase captures instead of the calm even two-up wall
    the default variant uses. Guardrail §0.1: crc32-seeded rotation/offset/
    z-index only, no Math.random(). Guardrail §0.3: capped at 50 items.
    Guardrail §0.5: click-to-front via native focus, keyboard tab order
    unaffected by the visual overlap.
--}}
@php
    $pageSeed = (string) data_get($section, 'pageSeed', data_get($section, 'slug', 'soft-focus-latest-showcase'));
    $heading = data_get($section, 'heading', __('capell-theme-soft-focus::sections.showcase.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-soft-focus::sections.showcase.summary'));
    $items = collect(data_get($section, 'items', [
        ['title' => __('capell-theme-soft-focus::sections.showcase.marlow_title'), 'summary' => __('capell-theme-soft-focus::sections.showcase.marlow_summary'), 'meta' => __('capell-theme-soft-focus::sections.showcase.marlow_meta')],
        ['title' => __('capell-theme-soft-focus::sections.showcase.tideline_title'), 'summary' => __('capell-theme-soft-focus::sections.showcase.tideline_summary'), 'meta' => __('capell-theme-soft-focus::sections.showcase.tideline_meta')],
    ]))
        ->filter(fn (mixed $item): bool => filled(data_get($item, 'title', data_get($item, 'name'))))
        ->take(50)
        ->values();

    $scatteredItems = $items->map(function (array $item, int $index) use ($pageSeed): array {
        $identity = (string) (data_get($item, 'title') ?? data_get($item, 'name') ?? $index);
        $seed = crc32($pageSeed . '::latest-showcase-organic::' . $identity . '::' . $index);

        return [
            'item' => $item,
            'index' => $index,
            'rotation' => (($seed % 600) / 100) - 3,
            'offsetInline' => (($seed >> 5) % 220) - 110,
            'offsetBlock' => (($seed >> 10) % 150) - 75,
            'zIndex' => 10 + ($seed % 20),
        ];
    });
@endphp

<section
    id="latest-showcase"
    class="qwg-section qwg-scatter-section"
    data-widget="latest-showcase-organic"
    data-variant="organic"
>
    <div class="qwg-section-inner">
        <p class="qwg-kicker">
            {{ __('capell-theme-soft-focus::sections.showcase.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="qwg-lede">{{ $summary }}</p>

        <div
            class="qwg-scatter-table qwg-scatter-table-showcase"
            data-scatter-seed="{{ $pageSeed }}"
        >
            @foreach ($scatteredItems as $entry)
                @php
                    $item = $entry['item'];
                    $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                    $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                @endphp

                <figure
                    class="qwg-frame qwg-scatter-tile"
                    tabindex="0"
                    style="--qwg-scatter-rotate: {{ $entry['rotation'] }}deg; --qwg-scatter-x: {{ $entry['offsetInline'] }}px; --qwg-scatter-y: {{ $entry['offsetBlock'] }}px; --qwg-scatter-z: {{ $entry['zIndex'] }};"
                    data-scatter-tile
                >
                    <div class="qwg-frame-mat">
                        @if (filled($itemImage))
                            <img
                                src="{{ $itemImage }}"
                                alt="{{ $itemAlt }}"
                                loading="lazy"
                                decoding="async"
                                class="qwg-frame-media"
                            />
                        @else
                            <div
                                class="qwg-frame-media"
                                aria-hidden="true"
                            ></div>
                        @endif
                    </div>
                    <figcaption class="qwg-frame-caption">
                        <span>
                            <strong>
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
                            </strong>
                            {{ data_get($item, 'summary', '') }}
                        </span>
                        <span class="qwg-frame-number">
                            {{ data_get($item, 'meta', data_get($item, 'category', __('capell-theme-soft-focus::sections.showcase.default_meta'))) }}
                        </span>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
