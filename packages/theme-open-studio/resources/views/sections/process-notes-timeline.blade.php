{{--
    process-notes-timeline — a vertical spine that visually "draws on scroll"
    alongside the numbered process steps, layering on top of the existing
    process-notes section rather than replacing it (that section stays the
    simple two-up; this is the dedicated scroll-narrative spine widget named
    in Part 2 §A). CSS `animation-timeline: view()` drives the spine reveal
    where supported (§0.8 enhancement-only); the spine renders fully drawn as
    a static fallback everywhere else, so the content is never gated behind
    the animation. Timeline entries capped at fifty per §0.3.
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
            class="csp-process-spine"
            data-process-notes-timeline
        >
            @foreach ($items as $item)
                <li class="csp-process-spine-item">
                    <span
                        class="csp-process-spine-marker"
                        aria-hidden="true"
                    ></span>
                    <div class="csp-process-spine-body">
                        <p class="csp-process-spine-meta">
                            {{ data_get($item, 'meta', data_get($item, 'date', '')) }}
                        </p>
                        <h3>
                            {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                        </h3>
                        <p>
                            {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                        </p>
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</section>
