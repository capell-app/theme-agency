@php
    // §0.1 determinism: satellite angle/radius are pure CSS calc() derived
    // from the payload's `relevance` (0-100) and list position — no
    // Math.random(), no client re-layout, identical HTML for every viewer of
    // the cached page. §0.3 payload cap: related-article carousels <= 20.
    $center = data_get($section, 'center', [
        'title' => __('capell-theme-ink-press::sections.topology.center_title'),
        'summary' => __('capell-theme-ink-press::sections.topology.center_summary'),
    ]);
    $satellites = collect(data_get($section, 'satellites', [
        ['title' => __('capell-theme-ink-press::sections.topology.satellite_one_title'), 'relevance' => 82, 'meta' => __('capell-theme-ink-press::sections.topology.satellite_one_meta')],
        ['title' => __('capell-theme-ink-press::sections.topology.satellite_two_title'), 'relevance' => 64, 'meta' => __('capell-theme-ink-press::sections.topology.satellite_two_meta')],
        ['title' => __('capell-theme-ink-press::sections.topology.satellite_three_title'), 'relevance' => 47, 'meta' => __('capell-theme-ink-press::sections.topology.satellite_three_meta')],
        ['title' => __('capell-theme-ink-press::sections.topology.satellite_four_title'), 'relevance' => 33, 'meta' => __('capell-theme-ink-press::sections.topology.satellite_four_meta')],
    ]))->take(20)->values();
    $satelliteCount = max($satellites->count(), 1);
@endphp

<section
    id="news-web-topology"
    class="dnews-section"
>
    <div class="dnews-section-inner">
        <div class="dnews-section-head">
            <p class="dnews-kicker">
                {{ __('capell-theme-ink-press::sections.topology.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-ink-press::sections.topology.heading')) }}
            </h2>
            @if (data_get($section, 'summary') !== null)
                <p class="dnews-lede">{{ data_get($section, 'summary') }}</p>
            @endif
        </div>

        {{-- Orbital graph: desktop/tablet layout, deterministic CSS
             positioning. A `@media` breakpoint (not JS device detection)
             swaps to the plain vertical list below on touch/narrow
             viewports per §0.8. --}}
        <div
            class="dnews-topology"
            style="--dnews-topology-count: {{ $satelliteCount }}"
        >
            <article class="dnews-topology-center">
                <p class="dnews-meta dnews-meta-accent">
                    {{ __('capell-theme-ink-press::sections.topology.center_label') }}
                </p>
                <h3>{{ data_get($center, 'title', '') }}</h3>
                <p>{{ data_get($center, 'summary', '') }}</p>
            </article>

            <ul class="dnews-topology-orbit">
                @foreach ($satellites as $satellite)
                    @php
                        $relevance = max(0, min(100, (int) data_get($satellite, 'relevance', 50)));
                        $satelliteUrl = data_get($satellite, 'url', data_get($satellite, 'href'));
                    @endphp

                    <li
                        class="dnews-topology-satellite"
                        style="--dnews-topology-index: {{ $loop->index }}; --dnews-topology-relevance: {{ $relevance }}"
                    >
                        <span
                            class="dnews-topology-thread"
                            aria-hidden="true"
                        ></span>
                        <article>
                            <h4>
                                @if ($satelliteUrl !== null)
                                    <a
                                        class="dnews-title-link"
                                        href="{{ $satelliteUrl }}"
                                    >
                                        {{ data_get($satellite, 'title', data_get($satellite, 'name', '')) }}
                                    </a>
                                @else
                                    {{ data_get($satellite, 'title', data_get($satellite, 'name', '')) }}
                                @endif
                            </h4>
                            <p class="dnews-meta">
                                {{ data_get($satellite, 'meta', '') }} · {{ $relevance }}{{ __('capell-theme-ink-press::sections.topology.relevance_suffix') }}
                            </p>
                        </article>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Vertical-list fallback: a `@media (max-width) or (pointer: coarse)`
             breakpoint swap in CSS (see .dnews-topology-fallback-list) hides
             the orbital graph and reveals this plain list instead — a CSS
             display toggle, not JS device detection, per §0.8. Both lists
             carry real content so neither is ever aria-hidden. --}}
        <ul class="dnews-topology-fallback-list">
            @foreach ($satellites as $satellite)
                @php
                    $fallbackUrl = data_get($satellite, 'url', data_get($satellite, 'href'));
                @endphp

                <li>
                    <p class="dnews-meta">{{ data_get($satellite, 'meta', '') }}</p>
                    <h4>
                        @if ($fallbackUrl !== null)
                            <a
                                class="dnews-title-link"
                                href="{{ $fallbackUrl }}"
                            >
                                {{ data_get($satellite, 'title', data_get($satellite, 'name', '')) }}
                            </a>
                        @else
                            {{ data_get($satellite, 'title', data_get($satellite, 'name', '')) }}
                        @endif
                    </h4>
                </li>
            @endforeach
        </ul>
    </div>
</section>
