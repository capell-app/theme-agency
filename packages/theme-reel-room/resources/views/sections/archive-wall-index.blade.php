@php
    /**
     * archive-wall-index (Wave 4b signature widget): a dense grid of archive
     * stills with varied deterministic spans, like a contact-sheet wall.
     * Guardrail §0.1: spans are NEVER randomised at render — the pattern is
     * seeded from a hash of the section's own payload (heading + item
     * titles), so html-cache always serves an identical wall for identical
     * content, and the rotation table below is fixed so the seed can only
     * choose an offset into it, never invent a span that could overflow the
     * grid. Payload cap §0.3: grids are capped at 50 items.
     */
    $heading = data_get($section, 'heading', __('capell-theme-reel-room::sections.archive_wall_index.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-reel-room::sections.archive_wall_index.summary'));
    $items = collect(data_get($section, 'items', []))->take(50);

    $layoutSeedSource = $heading . '|' . $items->map(fn (mixed $item): string => (string) data_get($item, 'title', ''))->implode('|');
    $layoutSeed = hexdec(substr(md5($layoutSeedSource), 0, 8));

    // Fixed rotation table of [column-span, row-span] pairs; the seed only
    // picks an offset into this table, never invents a new span.
    $wallSpans = [[1, 1], [2, 1], [1, 2], [1, 1], [1, 1], [2, 2]];
    $spanCount = count($wallSpans);
@endphp

<section
    id="archive-wall-index"
    class="mva-section"
    data-widget="archive-wall-index"
>
    <div class="mva-section-inner">
        <p class="mva-kicker">
            {{ __('capell-theme-reel-room::sections.archive_wall_index.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="mva-lede">{{ $summary }}</p>

        @if ($items->isNotEmpty())
            <div
                class="mva-wall"
                style="margin-top: 2rem"
                data-layout-seed="{{ $layoutSeed }}"
            >
                @foreach ($items as $item)
                    @php
                        $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                        $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                        $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                        $spanIndex = ($layoutSeed + $loop->index) % $spanCount;
                        [$columnSpan, $rowSpan] = $wallSpans[$spanIndex];
                    @endphp

                    <a
                        href="{{ $itemUrl ?? '#' }}"
                        class="mva-wall-tile"
                        style="--mva-wall-col-span: {{ $columnSpan }}; --mva-wall-row-span: {{ $rowSpan }};"
                    >
                        @if (filled($itemImage))
                            <img
                                src="{{ $itemImage }}"
                                alt="{{ $itemAlt }}"
                                loading="lazy"
                                decoding="async"
                                class="mva-wall-tile-image"
                            />
                        @endif
                        <span class="mva-wall-tile-caption">
                            {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                        </span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>
