{{--
    process-notes-timeline — compact variant: a single-column dense list
    (no spine marker column) for narrower Layout Builder placements; keeps
    the same scroll-drawn spine CSS hook off, since there is no room for a
    visible rail at this density.
--}}
@php
    $heading = data_get($section, 'heading', __('capell-theme-open-studio::sections.process_timeline.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-open-studio::sections.process_timeline.summary'));
    $items = collect(data_get($section, 'items', []))->take(50)->values();

    if ($items->isEmpty()) {
        $items = collect([
            ['title' => __('capell-theme-open-studio::sections.process_timeline.entry_title'), 'summary' => __('capell-theme-open-studio::sections.process_timeline.entry_summary')],
        ]);
    }
@endphp

<section
    id="process-notes-timeline"
    class="csp-section"
>
    <div class="csp-section-inner">
        <p class="csp-kicker">
            {{ __('capell-theme-open-studio::sections.process_timeline.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="csp-lede">{{ $summary }}</p>

        <ol
            class="csp-process-spine csp-process-spine-compact"
            data-process-notes-timeline
        >
            @foreach ($items as $item)
                <li
                    class="csp-process-spine-item csp-process-spine-item-compact"
                >
                    <p class="csp-process-spine-meta">
                        {{ data_get($item, 'meta', data_get($item, 'date', '')) }}
                    </p>
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>
                        {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                    </p>
                </li>
            @endforeach
        </ol>
    </div>
</section>
