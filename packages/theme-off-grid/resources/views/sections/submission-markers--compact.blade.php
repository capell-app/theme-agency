{{--
    submission-markers, `compact` variant: the same intake-stamp entries
    collapsed into a single dense row rather than the default grid — for
    placements with less vertical room (e.g. stacked directly under a form).
--}}

@php
    $heading = data_get($section, 'heading', __('capell-theme-off-grid::sections.markers.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-off-grid::sections.markers.summary'));
    $items = data_get($section, 'items', []);
    $label = data_get($section, 'label', __('capell-theme-off-grid::sections.markers.submit_button'));
    $url = data_get($section, 'url', '/');
@endphp

<section
    id="submission-markers"
    class="rwi-section rwi-section-dark"
    data-widget="submission-markers"
    data-variant="compact"
>
    <div class="rwi-section-inner">
        <span class="rwi-index-tag">04</span>
        <p class="rwi-kicker">
            {{ __('capell-theme-off-grid::sections.markers.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="rwi-lede">{{ $summary }}</p>

        <div
            class="rwi-marker-grid rwi-marker-grid-compact"
            style="margin-top: 2rem"
        >
            @foreach ($items as $item)
                <article
                    class="rwi-marker rwi-marker-compact"
                    data-stamp="{{ data_get($item, 'stamp', __('capell-theme-off-grid::sections.markers.kicker')) }}"
                >
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                </article>
            @endforeach
        </div>

        <div class="rwi-actions">
            <a
                class="rwi-button"
                href="{{ $url }}"
            >
                {{ $label }}
            </a>
        </div>
    </div>
</section>
