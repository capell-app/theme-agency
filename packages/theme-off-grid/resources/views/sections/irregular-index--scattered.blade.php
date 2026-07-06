{{--
    irregular-index-grid, `scattered` variant: the same seeded stagger table
    pushed further — wider inline shifts and rotation reads as a pinned-up
    corkboard rather than a tidy ledger. Still fully deterministic (§0.1):
    the seed comes from the section's own payload hash, never Math.random(),
    so html-cache serves an identical scatter to every visitor.
--}}

@php
    $heading = data_get($section, 'heading', __('capell-theme-off-grid::sections.index.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-off-grid::sections.index.summary'));
    $items = collect(data_get($section, 'items', []))->take(50);

    $layoutSeedSource = $heading . '|' . $items->map(fn (mixed $item): string => (string) data_get($item, 'title', data_get($item, 'name', '')))->implode('|');
    $layoutSeed = hexdec(substr(md5($layoutSeedSource), 0, 8));

    // Wider deterministic stagger table for the corkboard read.
    $staggerSteps = [[0, -1.2], [2.5, 0.8], [1, -0.6], [3.25, 1.1], [0.5, -0.9], [2, 0.5]];
    $stepCount = count($staggerSteps);
@endphp

<section
    id="irregular-index"
    class="rwi-section"
    data-widget="irregular-index-grid"
    data-variant="scattered"
>
    <div class="rwi-section-inner">
        <span class="rwi-index-tag">02</span>
        <p class="rwi-kicker">
            {{ __('capell-theme-off-grid::sections.index.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="rwi-lede">{{ $summary }}</p>

        <div
            class="rwi-index-list rwi-index-list-scattered"
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
                    class="rwi-index-row rwi-index-row-scattered"
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
