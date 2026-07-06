@php
    /**
     * date-filter-rail --compact variant: the same era/discipline filter
     * links as the default vertical rail, rendered as a horizontal chip row
     * for narrower archive sub-pages where a full sidebar rail would crowd
     * the content column.
     */
    $heading = data_get($section, 'heading', __('capell-theme-reel-room::sections.date_filter_rail.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-reel-room::sections.date_filter_rail.summary'));
    $items = data_get($section, 'items', []);
@endphp

<section
    id="date-filter-rail"
    class="mva-section mva-section-raised"
    data-widget="date-filter-rail"
    data-variant="compact"
>
    <div class="mva-section-inner">
        <p class="mva-kicker">
            {{ __('capell-theme-reel-room::sections.date_filter_rail.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="mva-lede">{{ $summary }}</p>

        <nav
            class="mva-rail-chips"
            style="margin-top: 2rem"
            aria-label="{{ __('capell-theme-reel-room::sections.date_filter_rail.kicker') }}"
        >
            @foreach ($items as $item)
                @php
                    $itemUrl = data_get($item, 'url', data_get($item, 'href', '#winner-list'));
                @endphp

                <a
                    class="mva-rail-chip"
                    href="{{ $itemUrl }}"
                >
                    {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                </a>
            @endforeach
        </nav>
    </div>
</section>
