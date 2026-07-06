@php
    $markers = collect(data_get($section, 'markers', [
        ['label' => __('capell-theme-ink-press::sections.progress.marker_lead'), 'anchor' => 'top-stories'],
        ['label' => __('capell-theme-ink-press::sections.progress.marker_live'), 'anchor' => 'live-event-timeline'],
        ['label' => __('capell-theme-ink-press::sections.progress.marker_opinion'), 'anchor' => 'opinion-grid-with-bylines'],
    ]));
@endphp

{{-- Minimal variant: fill bar only, chapter markers collapse into a single
     "jump to next chapter" link for narrow Layout Builder columns. --}}
<nav
    id="reading-progress-with-markers"
    class="dnews-reading-progress dnews-reading-progress-minimal"
    data-scroll-spy
    aria-label="{{ __('capell-theme-ink-press::sections.progress.aria_label') }}"
>
    <div
        class="dnews-reading-progress-track"
        aria-hidden="true"
    >
        <div class="dnews-reading-progress-fill"></div>
    </div>
    @if ($markers->isNotEmpty())
        <a
            class="dnews-reading-progress-next"
            href="#{{ data_get($markers->first(), 'anchor', '#') }}"
        >
            {{ data_get($markers->first(), 'label', '') }}
        </a>
    @endif
</nav>
