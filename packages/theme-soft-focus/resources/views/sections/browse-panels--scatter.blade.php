{{--
    browse-panels-scatter (Wave 4c signature widget #1, headline mechanic
    "scatter light table"): the three browse panels are laid on the light
    table with an organic overlap instead of the calm even grid the default
    variant uses. Guardrail §0.1: no Math.random() anywhere — each panel's
    rotation, inline offset, block offset, and stacking order are derived
    from a crc32 hash of the page seed plus the item's own identity, so the
    same payload always produces the same layout and html-cache can serve
    one HTML response to everyone. Guardrail §0.5: click-to-front is CSS-only
    (no JS budget spent) — every panel is a natively focusable <a>/<article
    tabindex="0">, and `:focus-within`/`:focus-visible` raises its z-index
    above its scattered siblings, so the mechanic works with keyboard-only
    navigation in normal DOM/tab order despite the visual overlap. Reduced
    motion keeps the scattered positions themselves (they are static, not an
    animation) and only removes the hover/focus settle transition.
--}}
@php
    $pageSeed = (string) data_get($section, 'pageSeed', data_get($section, 'slug', 'soft-focus-browse-panels'));
    $items = collect(data_get($section, 'items', [
        ['title' => __('capell-theme-soft-focus::sections.panels.style_title'), 'summary' => __('capell-theme-soft-focus::sections.panels.style_summary'), 'url' => '#style-type-categories'],
        ['title' => __('capell-theme-soft-focus::sections.panels.type_title'), 'summary' => __('capell-theme-soft-focus::sections.panels.type_summary'), 'url' => '#style-type-categories'],
        ['title' => __('capell-theme-soft-focus::sections.panels.random_title'), 'summary' => __('capell-theme-soft-focus::sections.panels.random_summary'), 'url' => '#random-best-of'],
    ]))
        ->filter(fn (mixed $item): bool => filled(data_get($item, 'title', data_get($item, 'name'))))
        ->values();

    $scatteredItems = $items->map(function (array $item, int $index) use ($pageSeed): array {
        $identity = (string) (data_get($item, 'title') ?? data_get($item, 'name') ?? $index);
        $seed = crc32($pageSeed . '::browse-panels-scatter::' . $identity . '::' . $index);

        return [
            'item' => $item,
            'index' => $index,
            'rotation' => (($seed % 700) / 100) - 3.5,
            'offsetInline' => (($seed >> 4) % 240) - 120,
            'offsetBlock' => (($seed >> 9) % 160) - 80,
            'zIndex' => 10 + ($seed % 20),
        ];
    });
@endphp

<section
    id="browse-panels"
    class="qwg-section qwg-scatter-section"
    data-widget="browse-panels-scatter"
    data-variant="scatter"
>
    <div class="qwg-section-inner">
        <p class="qwg-kicker">
            {{ __('capell-theme-soft-focus::sections.panels.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-soft-focus::sections.panels.heading')) }}
        </h2>
        <p class="qwg-lede">
            {{ data_get($section, 'summary', __('capell-theme-soft-focus::sections.panels.summary')) }}
        </p>

        <div
            class="qwg-scatter-table"
            data-scatter-seed="{{ $pageSeed }}"
        >
            @foreach ($scatteredItems as $entry)
                @php
                    $item = $entry['item'];
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                    $itemTitle = data_get($item, 'title', data_get($item, 'name', ''));
                @endphp

                <article
                    class="qwg-panel qwg-scatter-tile"
                    tabindex="0"
                    style="--qwg-scatter-rotate: {{ $entry['rotation'] }}deg; --qwg-scatter-x: {{ $entry['offsetInline'] }}px; --qwg-scatter-y: {{ $entry['offsetBlock'] }}px; --qwg-scatter-z: {{ $entry['zIndex'] }};"
                    data-scatter-tile
                >
                    <span
                        class="qwg-panel-index"
                        aria-hidden="true"
                    >
                        {{ str_pad((string) ($entry['index'] + 1), 2, '0', STR_PAD_LEFT) }}
                    </span>
                    <h3>
                        @if (filled($itemUrl))
                            <a
                                class="qwg-title-link"
                                href="{{ $itemUrl }}"
                            >
                                {{ $itemTitle }}
                            </a>
                        @else
                            {{ $itemTitle }}
                        @endif
                    </h3>
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
