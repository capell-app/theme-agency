{{--
    Signature widget: cultural-dispatch-timeline (Part 2 §B, far-field).

    Programme note (§0.7): timelines are specified as a shared,
    Foundation-owned primitive rather than a bespoke per-theme
    reimplementation. As of this Wave-4a pass Foundation has not yet
    extracted that primitive (Wave 2.7 has not landed a timeline component;
    only `tabs.js`/`scroll-spy.js` exist under
    `packages/theme-foundation/resources/js/widgets`), and this task is
    scoped to `packages/theme-far-field` only -- touching
    `packages/theme-foundation` is explicitly out of scope for this change.
    This view is therefore a self-contained far-field skin built to the same
    shape a Foundation primitive would need (ordered dispatch entries with a
    date/kicker/heading/summary, vertical spine, `<time>` elements), so a
    future Foundation extraction can lift this markup with minimal changes.
    Flagged as outstanding follow-up work.

    Capped at 50 entries per §0.3 (timelines <= 50).
--}}
@php
    $heading = data_get($section, 'heading', __('capell-theme-far-field::sections.dispatch_timeline.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-far-field::sections.dispatch_timeline.summary'));
    $entries = collect(data_get($section, 'items', [
        ['date' => '2026-01-06', 'meta' => __('capell-theme-far-field::sections.dispatch_timeline.entry_one_meta'), 'title' => __('capell-theme-far-field::sections.dispatch_timeline.entry_one_title'), 'summary' => __('capell-theme-far-field::sections.dispatch_timeline.entry_one_summary')],
        ['date' => '2026-02-14', 'meta' => __('capell-theme-far-field::sections.dispatch_timeline.entry_two_meta'), 'title' => __('capell-theme-far-field::sections.dispatch_timeline.entry_two_title'), 'summary' => __('capell-theme-far-field::sections.dispatch_timeline.entry_two_summary')],
        ['date' => '2026-03-21', 'meta' => __('capell-theme-far-field::sections.dispatch_timeline.entry_three_meta'), 'title' => __('capell-theme-far-field::sections.dispatch_timeline.entry_three_title'), 'summary' => __('capell-theme-far-field::sections.dispatch_timeline.entry_three_summary')],
    ]))->take(50);
    $variant = (string) data_get($section, 'variant', 'default');
@endphp

<section
    class="gcm-section"
    id="cultural-dispatch-timeline"
    data-widget="cultural-dispatch-timeline"
    data-variant="{{ $variant }}"
>
    <div class="gcm-section-inner">
        <div class="gcm-intro">
            <p class="gcm-kicker">
                {{ __('capell-theme-far-field::sections.dispatch_timeline.kicker') }}
            </p>
            <h2>{{ $heading }}</h2>
            <p class="gcm-lede">{{ $summary }}</p>
        </div>

        <ol class="gcm-dispatch-timeline">
            @foreach ($entries as $entry)
                @php
                    $entryDate = (string) data_get($entry, 'date', '');
                    $entryTitle = (string) data_get($entry, 'title', '');
                    $entryUrl = (string) data_get($entry, 'url', '');
                @endphp

                <li class="gcm-dispatch-timeline-entry">
                    <div
                        class="gcm-dispatch-timeline-marker"
                        aria-hidden="true"
                    ></div>
                    <div class="gcm-dispatch-timeline-content">
                        <p class="gcm-meta">
                            @if ($entryDate !== '')
                                <time
                                    datetime="{{ $entryDate }}"
                                    >{{ $entryDate }}</time
                                >
                                &middot;
                            @endif
                            {{ data_get($entry, 'meta', '') }}
                        </p>
                        <h3>
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
                        <p>{{ data_get($entry, 'summary', '') }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</section>
