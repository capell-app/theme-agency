@php
    /**
     * irregular-index-grid (field station signature widget): a wall of index
     * rows whose stagger/rotation reads as "controlled inconsistency" rather
     * than a repeating template. Guardrail §0.1: the offset pattern is NEVER
     * randomised at render — it is a deterministic sequence seeded from a
     * hash of the section's own payload (heading + item titles), so
     * html-cache always serves the same irregular layout for the same
     * content. Guardrail §0.3: index rows are capped at 50 entries.
     */
    $heading = data_get($section, 'heading', __('capell-theme-off-grid::sections.index.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-off-grid::sections.index.summary'));
    $items = collect(data_get($section, 'items', []))->take(50);

    $layoutSeedSource = $heading . '|' . $items->map(fn (mixed $item): string => (string) data_get($item, 'title', data_get($item, 'name', '')))->implode('|');
    $layoutSeed = hexdec(substr(md5($layoutSeedSource), 0, 8));

    // Deterministic stagger table: each entry is [inline-shift-rem, rotate-deg].
    // The seed only chooses a rotation offset into this fixed table — it never
    // invents new shifts, so a row can never drift off the readable measure.
    $staggerSteps = [[0, 0], [1.25, -0.4], [0.5, 0.3], [2, -0.2], [0.25, 0.5], [1.5, 0]];
    $stepCount = count($staggerSteps);
@endphp

<section
    id="irregular-index"
    class="rwi-section"
    data-widget="irregular-index-grid"
>
    <div class="rwi-section-inner">
        <span class="rwi-index-tag">02</span>
        <p class="rwi-kicker">
            {{ __('capell-theme-off-grid::sections.index.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="rwi-lede">{{ $summary }}</p>

        <div
            class="rwi-index-list"
            style="margin-top: 2rem"
            data-layout-seed="{{ $layoutSeed }}"
        >
            @foreach ($items as $item)
                @php
                    $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                    $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                    $stepIndex = ($layoutSeed + $loop->index) % $stepCount;
                    [$inlineShift, $rotateDeg] = $staggerSteps[$stepIndex];
                @endphp

                <article
                    class="rwi-index-row"
                    style="--rwi-seed-shift: {{ $inlineShift }}rem; --rwi-seed-rotate: {{ $rotateDeg }}deg;"
                >
                    <span
                        class="rwi-index-numeral"
                        aria-hidden="true"
                    >
                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                    </span>
                    <div>
                        @if (filled($itemImage))
                            <img
                                src="{{ $itemImage }}"
                                alt="{{ $itemAlt ?: data_get($item, 'title', data_get($item, 'name', '')) }}"
                                loading="lazy"
                                decoding="async"
                                class="rwi-index-thumb"
                            />
                        @endif

                        <p class="rwi-meta">
                            {{ data_get($item, 'meta', __('capell-theme-off-grid::sections.index.default_meta')) }}
                        </p>
                        <h3>
                            @if (filled($itemUrl))
                                <a
                                    class="rwi-title-link"
                                    href="{{ $itemUrl }}"
                                >
                                    {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                                </a>
                            @else
                                {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                            @endif
                        </h3>
                        <p>
                            {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                        </p>
                        <p class="rwi-index-note">
                            {{ data_get($item, 'care_note', __('capell-theme-off-grid::sections.index.care_note')) }}
                        </p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
