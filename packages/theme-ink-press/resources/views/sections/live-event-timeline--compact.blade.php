@php
    // §0.3 payload cap: timelines <= 50 items; the compact variant additionally
    // trims to the 4 most recent dispatches for tight Layout Builder columns.
    $heading = data_get($section, 'heading', __('capell-theme-ink-press::sections.live.heading'));
    $dispatches = collect(data_get($section, 'items', [
        ['title' => __('capell-theme-ink-press::sections.live.sequence_title'), 'summary' => __('capell-theme-ink-press::sections.live.sequence_summary')],
        ['title' => __('capell-theme-ink-press::sections.live.caption_title'), 'summary' => __('capell-theme-ink-press::sections.live.caption_summary')],
    ]))->take(4);
@endphp

<section
    id="live-event-timeline"
    class="dnews-section dnews-section-dark"
>
    <div class="dnews-section-inner">
        <div class="dnews-section-head dnews-section-head-flush">
            <p class="dnews-kicker">
                {{ __('capell-theme-ink-press::sections.live.kicker') }}
            </p>
            <h2>{{ $heading }}</h2>
        </div>

        <div
            class="dnews-timeline dnews-timeline-compact"
            data-scroll-progress
        >
            <div
                class="dnews-timeline-progress"
                aria-hidden="true"
            ></div>
            <ol class="dnews-timeline-feed">
                @foreach ($dispatches as $dispatch)
                    <li class="dnews-timeline-item">
                        <span
                            class="dnews-timeline-marker"
                            aria-hidden="true"
                        ></span>
                        <h3>
                            {{ data_get($dispatch, 'title', data_get($dispatch, 'name', '')) }}
                        </h3>
                        <p class="dnews-meta">
                            {{ data_get($dispatch, 'meta', data_get($dispatch, 'time', __('capell-theme-ink-press::sections.live.update_label'))) }}
                        </p>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
</section>
