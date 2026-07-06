{{--
    Variant: cultural-dispatch-timeline / compact. Tighter spine spacing and
    a single-line entry header (date + title on one row) for issues that want
    the timeline as a dense sidebar-style rail rather than the default
    full-width spine. See the base view for the Foundation-primitive
    provenance note (§0.7).
--}}
@php
    $heading = data_get($section, 'heading', __('capell-theme-far-field::sections.dispatch_timeline.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-far-field::sections.dispatch_timeline.summary'));
    $entries = collect(data_get($section, 'items', [
        ['date' => '2026-01-06', 'meta' => __('capell-theme-far-field::sections.dispatch_timeline.entry_one_meta'), 'title' => __('capell-theme-far-field::sections.dispatch_timeline.entry_one_title'), 'summary' => __('capell-theme-far-field::sections.dispatch_timeline.entry_one_summary')],
        ['date' => '2026-02-14', 'meta' => __('capell-theme-far-field::sections.dispatch_timeline.entry_two_meta'), 'title' => __('capell-theme-far-field::sections.dispatch_timeline.entry_two_title'), 'summary' => __('capell-theme-far-field::sections.dispatch_timeline.entry_two_summary')],
        ['date' => '2026-03-21', 'meta' => __('capell-theme-far-field::sections.dispatch_timeline.entry_three_meta'), 'title' => __('capell-theme-far-field::sections.dispatch_timeline.entry_three_title'), 'summary' => __('capell-theme-far-field::sections.dispatch_timeline.entry_three_summary')],
    ]))->take(50);
@endphp

<section
    class="gcm-section"
    id="cultural-dispatch-timeline"
    data-widget="cultural-dispatch-timeline"
    data-variant="compact"
>
    <div class="gcm-section-inner">
        <div class="gcm-intro">
            <p class="gcm-kicker">
                {{ __('capell-theme-far-field::sections.dispatch_timeline.kicker') }}
            </p>
            <h2>{{ $heading }}</h2>
            <p class="gcm-lede">{{ $summary }}</p>
        </div>

        <ol class="gcm-dispatch-timeline gcm-dispatch-timeline-compact">
            @foreach ($entries as $entry)
                @php
                    $entryDate = (string) data_get($entry, 'date', '');
                    $entryTitle = (string) data_get($entry, 'title', '');
                    $entryUrl = (string) data_get($entry, 'url', '');
                @endphp

                <li
                    class="gcm-dispatch-timeline-entry gcm-dispatch-timeline-entry-compact"
                >
                    <div
                        class="gcm-dispatch-timeline-marker"
                        aria-hidden="true"
                    ></div>
                    <div class="gcm-dispatch-timeline-content">
                        <h3 class="gcm-dispatch-timeline-inline-heading">
                            @if ($entryDate !== '')
                                <time
                                    datetime="{{ $entryDate }}"
                                    >{{ $entryDate }}</time
                                >
                            @endif
                            @if ($entryUrl !== '')
                                <a
                                    class="gcm-title-link"
                                    href="{{ $entryUrl }}"
                                >
                                    {{ $entryTitle }}
                                </a>
                            @else
                                {{ $entryTitle }}
                            @endif
                        </h3>
                        <p class="gcm-meta">{{ data_get($entry, 'meta', '') }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</section>
