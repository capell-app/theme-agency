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
    $heading = data_get($section, 'heading');
    $summary = data_get($section, 'summary');
    $items = collect(data_get($section, 'items', []))
        ->filter(fn (mixed $item): bool => filled(data_get($item, 'title', data_get($item, 'name'))))
        ->take(50)
        ->values();

    $layoutSeedSource = $heading . '|' . $items->map(fn (mixed $item): string => (string) data_get($item, 'title', ''))->implode('|');
    $layoutSeed = hexdec(substr(md5($layoutSeedSource), 0, 8));

    // Fixed rotation table of [column-span, row-span] pairs -- the seed only
    // picks an offset into this table, never invents a new span (§0.1).
    $wallSpans = [[1, 1], [2, 1], [1, 1], [1, 2], [1, 1], [2, 2]];
    $spanCount = count($wallSpans);
@endphp

@if ($items->isNotEmpty())
    <section
        id="portfolio-grid"
        class="ppc-section"
        data-widget="portfolio-grid-gallery-wall"
    >
        <div class="ppc-section-inner">
            @if (filled(data_get($section, 'kicker')))
                <p class="ppc-kicker">{{ data_get($section, 'kicker') }}</p>
            @endif

            @if (filled($heading))
                <h2>{{ $heading }}</h2>
            @endif

            @if (filled($summary))
                <p class="ppc-lede">{{ $summary }}</p>
            @endif

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
                                    <x-capell::image-source
                                        :image="$itemImage"
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
                        @if (filled(data_get($item, 'summary', data_get($item, 'description'))))
                            <p>{{ data_get($item, 'summary', data_get($item, 'description')) }}</p>
                        @endif
                        @if (filled(data_get($item, 'meta', data_get($item, 'category'))) || filled(data_get($item, 'care_note')))
                            <div class="ppc-card-meta-row">
                                @if (filled(data_get($item, 'meta', data_get($item, 'category'))))
                                    <span class="ppc-chip">
                                        {{ data_get($item, 'meta', data_get($item, 'category')) }}
                                    </span>
                                @endif

                                @if (filled(data_get($item, 'care_note')))
                                    <span class="ppc-meta">
                                        {{ data_get($item, 'care_note') }}
                                    </span>
                                @endif
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif
