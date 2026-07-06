@php
    $heading = (string) ($widget->getMeta('heading') ?? __('capell-theme-main-stage::generic.venue.kicker'));
    $summary = (string) ($widget->getMeta('summary') ?? '');
    $variant = (string) ($widget->getMeta('variant') ?? 'panels');
    $panels = is_array($widget->getMeta('panels')) ? $widget->getMeta('panels') : [];
@endphp

{{--
    `venue-travel-panels` — Part 2 §E signature widget. A set of
    editorially-authored panels covering venue address, getting there
    (transit/parking), and accommodation — no map SDK dependency (§0.8:
    modern-CSS/JS-heavy embeds are enhancement-only elsewhere in the fleet;
    this widget avoids the dependency entirely rather than requiring one).

    Two variants: `panels` (side-by-side card grid) and `map-list` (a single
    stacked column, each panel full-width — used when a theme wants venue
    information to read top-to-bottom rather than scanned as a grid).
--}}
<section
    id="venue"
    class="mst-shell mst-section"
>
    <div class="mst-section-inner">
        <div class="mst-heading-row">
            <div>
                <p class="mst-eyebrow">{{ __('capell-theme-main-stage::generic.venue.kicker') }}</p>
                <h2>{{ $heading }}</h2>
                @if ($summary !== '')
                    <p class="mst-lede">{{ $summary }}</p>
                @endif
            </div>
        </div>

        <div
            class="mst-venue-panels mst-venue-panels--{{ $variant === 'map-list' ? 'map-list' : 'panels' }}"
        >
            @foreach ($panels as $panel)
                <article class="mst-venue-panel">
                    <p class="mst-venue-panel-icon" aria-hidden="true">{{ data_get($panel, 'icon', '') }}</p>
                    <h3>{{ data_get($panel, 'title', '') }}</h3>
                    <p>{{ data_get($panel, 'summary', '') }}</p>
                    @if (data_get($panel, 'url'))
                        <a
                            href="{{ data_get($panel, 'url') }}"
                            class="mst-link-quiet"
                            >{{ data_get($panel, 'linkLabel', 'Learn more') }}</a
                        >
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
