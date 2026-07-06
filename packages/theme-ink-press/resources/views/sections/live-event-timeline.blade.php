@php
    // §0.3 payload cap: timelines <= 50 items.
    $heading = data_get($section, 'heading', __('capell-theme-ink-press::sections.live.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-ink-press::sections.live.summary'));
    $dispatches = collect(data_get($section, 'items', [
        ['title' => __('capell-theme-ink-press::sections.live.sequence_title'), 'summary' => __('capell-theme-ink-press::sections.live.sequence_summary')],
        ['title' => __('capell-theme-ink-press::sections.live.caption_title'), 'summary' => __('capell-theme-ink-press::sections.live.caption_summary')],
    ]))->take(50);
@endphp

<section
    id="live-event-timeline"
    class="dnews-section dnews-section-dark"
>
    <div class="dnews-section-inner dnews-split">
        <div>
            <div class="dnews-section-head dnews-section-head-flush">
                <p class="dnews-kicker">
                    {{ __('capell-theme-ink-press::sections.live.kicker') }}
                </p>
                <h2>{{ $heading }}</h2>
                <p class="dnews-lede">{{ $summary }}</p>
            </div>
            <div class="dnews-actions">
                <a
                    class="dnews-button"
                    href="{{ data_get($section, 'url', '#live-event-timeline') }}"
                >
                    {{ data_get($section, 'label', __('capell-theme-ink-press::sections.live.button')) }}
                </a>
            </div>
        </div>

        {{-- §0.5 a11y: scroll-driven progress bar (CSS animation-timeline: scroll())
             with no polling; a static rail with no fill is the graceful
             fallback when the browser lacks scroll-timeline support. --}}
        <div
            class="dnews-timeline"
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
                        <p>{{ data_get($dispatch, 'summary', '') }}</p>
                        <p class="dnews-meta">
                            {{ data_get($dispatch, 'meta', data_get($dispatch, 'time', __('capell-theme-ink-press::sections.live.update_label'))) }}
                        </p>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
</section>
