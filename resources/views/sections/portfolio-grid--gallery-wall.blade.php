@php
    use Capell\Core\Support\Security\PublicUrlSanitizer;

    /**
     * portfolio-grid-gallery-wall (Wave 4c signature widget): the theme's
     * headline mechanic in full -- a curated "salon hang" wall where some
     * plates span two columns or two rows, deterministically, so the wall
     * reads as hand-composed rather than a mechanical repeating grid.
     * Guardrail §0.1: the layout is NEVER randomised at render -- the span
     * pattern is seeded from an md5 hash of the section's own payload
     * (heading + item titles), so html-cache always serves an identical wall
     * for identical content, and the rotation table below is fixed so the
     * seed can only choose an offset into it, never invent a span that could
     * overflow the grid. Payload cap §0.3: grids are capped at 50 items.
     */
    $heading = data_get($section, 'heading', __('capell-theme-agency::sections.stories.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-agency::sections.stories.summary'));
    $items = collect(data_get($section, 'items', [
        ['title' => __('capell-theme-agency::sections.stories.product_title'), 'summary' => __('capell-theme-agency::sections.stories.product_summary'), 'meta' => __('capell-theme-agency::sections.stories.product_meta')],
        ['title' => __('capell-theme-agency::sections.stories.design_title'), 'summary' => __('capell-theme-agency::sections.stories.design_summary'), 'meta' => __('capell-theme-agency::sections.stories.design_meta')],
        ['title' => __('capell-theme-agency::sections.stories.advice_title'), 'summary' => __('capell-theme-agency::sections.stories.advice_summary'), 'meta' => __('capell-theme-agency::sections.stories.advice_meta')],
    ]))->take(50)->values();

    $layoutSeedSource = $heading . '|' . $items->map(fn (mixed $item): string => (string) data_get($item, 'title', ''))->implode('|');
    $layoutSeed = hexdec(substr(md5($layoutSeedSource), 0, 8));

    // Fixed rotation table of [column-span, row-span] pairs -- the seed only
    // picks an offset into this table, never invents a new span (§0.1).
    $wallSpans = [[1, 1], [2, 1], [1, 1], [1, 2], [1, 1], [2, 2]];
    $spanCount = count($wallSpans);
@endphp

<section
    id="portfolio-grid"
    class="ppc-section"
    data-widget="portfolio-grid-gallery-wall"
>
    <div class="ppc-section-inner">
        <p class="ppc-kicker">{{ __('capell-theme-agency::sections.stories.kicker') }}</p>
        <h2>{{ $heading }}</h2>
        <p class="ppc-lede">{{ $summary }}</p>

        <div
            class="ppc-wall"
            data-layout-seed="{{ $layoutSeed }}"
            role="list"
        >
            @foreach ($items as $item)
                @php
                    $itemImage = PublicUrlSanitizer::sanitize(data_get($item, 'image', data_get($item, 'imageUrl')));
                    $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    $itemUrl = PublicUrlSanitizer::sanitize(data_get($item, 'url', data_get($item, 'href')));
                    $spanIndex = ($layoutSeed + $loop->index) % $spanCount;
                    [$columnSpan, $rowSpan] = $wallSpans[$spanIndex];
                @endphp

                <article
                    @if (filled(data_get($item, 'id'))) id="{{ data_get($item, 'id') }}" @endif
                    class="ppc-card ppc-wall-card"
                    style="--ppc-wall-col-span: {{ $columnSpan }}; --ppc-wall-row-span: {{ $rowSpan }};"
                    role="listitem"
                >
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
                                    class="ppc-plate-media ppc-wall-card-media"
                                />
                            @else
                                <div
                                    class="ppc-plate-media ppc-plate-media-empty ppc-wall-card-media"
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
                    <p>{{ data_get($item, 'summary', data_get($item, 'description', '')) }}</p>
                    <div class="ppc-card-meta-row">
                        <span class="ppc-chip">
                            {{ data_get($item, 'meta', data_get($item, 'category', __('capell-theme-agency::sections.stories.default_meta'))) }}
                        </span>
                        <span class="ppc-meta">
                            {{ data_get($item, 'care_note', __('capell-theme-agency::sections.stories.care_note')) }}
                        </span>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
