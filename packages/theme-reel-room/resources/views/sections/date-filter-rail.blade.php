@php
    $heading = data_get($section, 'heading', __('capell-theme-reel-room::sections.date_filter_rail.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-reel-room::sections.date_filter_rail.summary'));
    $items = data_get($section, 'items', []);
@endphp

<section
    id="date-filter-rail"
    class="mva-section mva-section-raised"
>
    <div class="mva-section-inner">
        <p class="mva-kicker">
            {{ __('capell-theme-reel-room::sections.date_filter_rail.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="mva-lede">{{ $summary }}</p>

        <div
            class="mva-rail-layout"
            style="margin-top: 2rem"
        >
            <nav
                class="mva-rail"
                aria-label="{{ __('capell-theme-reel-room::sections.date_filter_rail.kicker') }}"
            >
                @foreach ($items as $item)
                    @php
                        $itemUrl = data_get($item, 'url', data_get($item, 'href', '#winner-list'));
                    @endphp

                    <a
                        class="mva-rail-link"
                        href="{{ $itemUrl }}"
                    >
                        <strong>
                            {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                        </strong>
                        <span>
                            {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                        </span>
                    </a>
                @endforeach
            </nav>

            <div class="mva-rail-panel">
                <p class="mva-kicker">
                    {{ __('capell-theme-reel-room::sections.date_filter_rail.panel_kicker') }}
                </p>
                <p
                    class="mva-lede"
                    style="margin: 0"
                >
                    {{ __('capell-theme-reel-room::sections.date_filter_rail.panel_note') }}
                </p>
            </div>
        </div>
    </div>
</section>
