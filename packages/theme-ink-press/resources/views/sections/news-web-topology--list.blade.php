@php
    // §0.3 payload cap: related-article carousels <= 20.
    $center = data_get($section, 'center', [
        'title' => __('capell-theme-ink-press::sections.topology.center_title'),
        'summary' => __('capell-theme-ink-press::sections.topology.center_summary'),
    ]);
    $satellites = collect(data_get($section, 'satellites', [
        ['title' => __('capell-theme-ink-press::sections.topology.satellite_one_title'), 'relevance' => 82, 'meta' => __('capell-theme-ink-press::sections.topology.satellite_one_meta')],
        ['title' => __('capell-theme-ink-press::sections.topology.satellite_two_title'), 'relevance' => 64, 'meta' => __('capell-theme-ink-press::sections.topology.satellite_two_meta')],
        ['title' => __('capell-theme-ink-press::sections.topology.satellite_three_title'), 'relevance' => 47, 'meta' => __('capell-theme-ink-press::sections.topology.satellite_three_meta')],
        ['title' => __('capell-theme-ink-press::sections.topology.satellite_four_title'), 'relevance' => 33, 'meta' => __('capell-theme-ink-press::sections.topology.satellite_four_meta')],
    ]))->take(20)->values()
        ->sortByDesc(static fn (array $satellite): int => (int) data_get($satellite, 'relevance', 0))
        ->values();
@endphp

{{-- Explicit "list" variant: always the plain relevance-ordered vertical
     list, for narrow Layout Builder columns where the orbital graph would
     never have room to breathe regardless of viewport. --}}
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
        </div>

        <article class="dnews-topology-center dnews-topology-center-flush">
            <p class="dnews-meta dnews-meta-accent">
                {{ __('capell-theme-ink-press::sections.topology.center_label') }}
            </p>
            <h3>{{ data_get($center, 'title', '') }}</h3>
            <p>{{ data_get($center, 'summary', '') }}</p>
        </article>

        <ul
            class="dnews-topology-fallback-list dnews-topology-fallback-list-always"
        >
            @foreach ($satellites as $satellite)
                @php
                    $listUrl = data_get($satellite, 'url', data_get($satellite, 'href'));
                    $relevance = max(0, min(100, (int) data_get($satellite, 'relevance', 50)));
                @endphp

                <li>
                    <p class="dnews-meta">
                        {{ data_get($satellite, 'meta', '') }} · {{ $relevance }}{{ __('capell-theme-ink-press::sections.topology.relevance_suffix') }}
                    </p>
                    <h4>
                        @if ($listUrl !== null)
                            <a
                                class="dnews-title-link"
                                href="{{ $listUrl }}"
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
