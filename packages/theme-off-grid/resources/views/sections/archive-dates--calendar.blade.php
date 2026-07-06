{{--
    archive-calendar (field station signature widget): the same monthly
    archive-dates payload rendered as a genuine calendar grid — a 7-column
    week header and a deterministic day-cell layout — rather than the
    default ledger rows. Each month entry occupies a day cell whose position
    is derived from its own index (deterministic, §0.1: no client re-layout,
    no Math.random()). Payload cap §0.3: timelines/lists <= 50 entries, so
    at most 50 day-cells are ever marked.
--}}

@php
    $heading = data_get($section, 'heading', __('capell-theme-off-grid::sections.dates.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-off-grid::sections.dates.summary'));
    $items = collect(data_get($section, 'items', data_get($section, 'stories', [])))->take(50);
    $weekLabels = [
        __('capell-theme-off-grid::sections.dates.week.mon'),
        __('capell-theme-off-grid::sections.dates.week.tue'),
        __('capell-theme-off-grid::sections.dates.week.wed'),
        __('capell-theme-off-grid::sections.dates.week.thu'),
        __('capell-theme-off-grid::sections.dates.week.fri'),
        __('capell-theme-off-grid::sections.dates.week.sat'),
        __('capell-theme-off-grid::sections.dates.week.sun'),
    ];
@endphp

<section
    id="archive-dates"
    class="rwi-section"
    data-widget="archive-calendar"
    data-variant="calendar"
>
    <div class="rwi-section-inner">
        <span class="rwi-index-tag">05</span>
        <p class="rwi-kicker">
            {{ __('capell-theme-off-grid::sections.dates.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="rwi-lede">{{ $summary }}</p>

        <div
            class="rwi-calendar"
            style="margin-top: 2rem"
            role="table"
            aria-label="{{ $heading }}"
        >
            <div
                class="rwi-calendar-week"
                role="row"
            >
                @foreach ($weekLabels as $weekLabel)
                    <span
                        class="rwi-calendar-weekday"
                        role="columnheader"
                    >
                        {{ $weekLabel }}
                    </span>
                @endforeach
            </div>

            <div
                class="rwi-calendar-grid"
                role="row"
            >
                @foreach ($items as $item)
                    <article
                        class="rwi-calendar-cell"
                        role="cell"
                        style="--rwi-calendar-cell: {{ ($loop->index % 7) + 1 }};"
                    >
                        <span class="rwi-calendar-date">
                            {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                        </span>
                        <p class="rwi-meta">
                            {{ data_get($item, 'meta', data_get($item, 'category', '')) }}
                        </p>
                        <p>
                            {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                        </p>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
