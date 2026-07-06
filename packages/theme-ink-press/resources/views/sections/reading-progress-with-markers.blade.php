@php
    // §0.3 payload cap: chapter markers are a short curated list, well under
    // any of the §0.3 caps (grids/timelines) — no explicit cap needed here.
    // §0.1 determinism: marker positions are plain document order, not
    // computed/randomised — no per-request layout to seed.
    $markers = collect(data_get($section, 'markers', [
        ['label' => __('capell-theme-ink-press::sections.progress.marker_lead'), 'anchor' => 'top-stories'],
        ['label' => __('capell-theme-ink-press::sections.progress.marker_live'), 'anchor' => 'live-event-timeline'],
        ['label' => __('capell-theme-ink-press::sections.progress.marker_opinion'), 'anchor' => 'opinion-grid-with-bylines'],
    ]));
@endphp

{{-- §0.5 a11y: CSS `scroll-timeline`/`view-timeline` drives the fill bar
     where supported (see .dnews-reading-progress-fill); the shared
     scroll-spy.js module supplies `aria-current` bookkeeping via
     IntersectionObserver as the accessible fallback everywhere else, exactly
     the pattern documented in its own header comment. --}}
<nav
    id="reading-progress-with-markers"
    class="dnews-reading-progress"
    data-scroll-spy
    aria-label="{{ __('capell-theme-ink-press::sections.progress.aria_label') }}"
>
    <div
        class="dnews-reading-progress-track"
        aria-hidden="true"
    >
        <div class="dnews-reading-progress-fill"></div>
    </div>
    <ol class="dnews-reading-progress-markers">
        @foreach ($markers as $marker)
            <li>
                <a href="#{{ data_get($marker, 'anchor', '#') }}">
                    {{ data_get($marker, 'label', '') }}
                </a>
            </li>
        @endforeach
    </ol>
</nav>
