@php
    /**
     * featured-portfolios-hero (Wave 4c signature widget), `parallax` variant:
     * the salon-wall float treatment for the theme's headline mechanic --
     * each featured plate rides a shallow parallax lift as the section
     * scrolls into view, driven by CSS scroll-driven animation
     * (animation-timeline: scroll()) so no JS scroll listener runs (§0.4).
     * Motion tier: subtle, capped at 20px of vertical travel (§0.6). The
     * `@supports (animation-timeline: scroll())` guard keeps every browser
     * without the feature -- and every visitor with
     * prefers-reduced-motion: reduce -- on the static, fully-composed
     * layout beneath: nothing about reading the collection depends on the
     * parallax running (§0.5).
     */
    $heading = data_get($section, 'heading', __('capell-theme-agency::sections.featured.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-agency::sections.featured.summary'));
    $items = collect(data_get($section, 'items', [
        ['title' => __('capell-theme-agency::sections.featured.launch_title'), 'summary' => __('capell-theme-agency::sections.featured.launch_summary')],
        ['title' => __('capell-theme-agency::sections.featured.essay_title'), 'summary' => __('capell-theme-agency::sections.featured.essay_summary')],
        ['title' => __('capell-theme-agency::sections.featured.template_title'), 'summary' => __('capell-theme-agency::sections.featured.template_summary')],
    ]))->take(50)->values();

    // Fixed float-depth table (never randomised) -- the seed only chooses an
    // offset into it, giving each plate a different but stable float depth so
    // the wall reads as "hand-hung" rather than a mechanical repeat (§0.1).
    $floatDepths = [0, 12, 20, 8, 16, 4];
    $depthCount = count($floatDepths);
@endphp

<section
    id="featured"
    class="ppc-section ppc-wall-section"
    data-widget="featured-portfolios-hero"
>
    <div class="ppc-section-inner">
        <p class="ppc-kicker">
            {{ __('capell-theme-agency::sections.featured.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="ppc-lede">{{ $summary }}</p>

        <div class="ppc-grid ppc-wall-float-grid">
            @foreach ($items as $item)
                @php
                    $itemImage = data_get($item, 'image', data_get($item, 'imageUrl'));
                    $itemAlt = data_get($item, 'imageAlt', data_get($item, 'title', ''));
                    $itemUrl = data_get($item, 'url', data_get($item, 'href'));
                    $award = data_get($item, 'award');
                    $depthIndex = $loop->index % $depthCount;
                    $floatDepth = $floatDepths[$depthIndex];
                @endphp

                <article
                    class="ppc-card ppc-wall-float-card"
                    style="--ppc-float-depth: {{ $floatDepth }}px"
                >
                    <figure class="ppc-plate">
                        <div class="ppc-plate-frame">
                            @if (filled($award))
                                <span class="ppc-badge">{{ $award }}</span>
                            @endif

                            @if (filled($itemImage))
                                <img
                                    src="{{ $itemImage }}"
                                    alt="{{ $itemAlt }}"
                                    loading="lazy"
                                    decoding="async"
                                    width="400"
                                    height="300"
                                    class="ppc-plate-media"
                                />
                            @else
                                <div
                                    class="ppc-plate-media ppc-plate-media-empty"
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
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
