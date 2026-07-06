@php
    $heading = (string) ($widget->getMeta('heading') ?? __('capell-theme-main-stage::generic.archive.kicker'));
    $summary = (string) ($widget->getMeta('summary') ?? '');
    $variant = (string) ($widget->getMeta('variant') ?? 'timeline');
    $editions = is_array($widget->getMeta('editions')) ? $widget->getMeta('editions') : [];
@endphp

{{--
    `past-editions-archive` — Part 2 §E signature widget. A curated,
    editorially-authored list of previous editions of the event (year,
    headline stat, recap link) — a shared timeline-primitive skin (§0.7:
    timelines are a shared, Foundation-owned primitive; this is Main
    Stage's token-skinned application of it, not a competing bespoke
    implementation).

    Two variants: `timeline` (a vertical spine with alternating year
    markers) and `grid` (a card grid, one card per edition — useful once
    there are enough past editions that a spine gets too tall).

    Payload cap: editions list stays well under the ≤50 grid cap (§0.3) in
    practice — a yearly event rarely has more than a handful of editions.
--}}
<section
    id="past-editions"
    class="mst-shell mst-section"
>
    <div class="mst-section-inner">
        <div class="mst-heading-row">
            <div>
                <p class="mst-eyebrow">{{ __('capell-theme-main-stage::generic.archive.kicker') }}</p>
                <h2>{{ $heading }}</h2>
                @if ($summary !== '')
                    <p class="mst-lede">{{ $summary }}</p>
                @endif
            </div>
        </div>

        <div
            class="mst-archive mst-archive--{{ $variant === 'grid' ? 'grid' : 'timeline' }}"
        >
            @foreach ($editions as $edition)
                <article class="mst-archive-edition">
                    <p class="mst-archive-year">{{ data_get($edition, 'year', '') }}</p>
                    <h3>{{ data_get($edition, 'title', '') }}</h3>
                    <p class="mst-archive-stat">{{ data_get($edition, 'stat', '') }}</p>
                    <p>{{ data_get($edition, 'summary', '') }}</p>
                    @if (data_get($edition, 'url'))
                        <a
                            href="{{ data_get($edition, 'url') }}"
                            class="mst-link-quiet"
                            >{{ data_get($edition, 'linkLabel', 'View recap') }}</a
                        >
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
