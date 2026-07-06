{{--
    Variant: destination-atlas / tabs. Same payload-driven atlas, but the map
    and the card rail are placed behind a `role="tablist"` pair for narrow
    viewports (§B far-field spec: "map/list tabs for mobile"). Markup follows
    the shared Foundation `tabs.js` contract (Wave 2.6) exactly -- themes
    that already load that module get working tabs with no per-theme JS;
    this view adds none of its own. Without JS the tablist still renders
    both panels' content in document order (progressive enhancement, no
    hidden-by-default panel breakage).
--}}
@php
    $heading = data_get($section, 'heading', __('capell-theme-far-field::sections.cities.heading'));
    $summary = data_get($section, 'summary', '');
    $items = collect(data_get($section, 'items', [
        ['title' => __('capell-theme-far-field::sections.cities.tokyo_title'), 'summary' => __('capell-theme-far-field::sections.cities.tokyo_summary'), 'x' => 84, 'y' => 38],
        ['title' => __('capell-theme-far-field::sections.cities.copenhagen_title'), 'summary' => __('capell-theme-far-field::sections.cities.copenhagen_summary'), 'x' => 48, 'y' => 20],
        ['title' => __('capell-theme-far-field::sections.cities.lisbon_title'), 'summary' => __('capell-theme-far-field::sections.cities.lisbon_summary'), 'x' => 38, 'y' => 34],
        ['title' => __('capell-theme-far-field::sections.cities.mexico_title'), 'summary' => __('capell-theme-far-field::sections.cities.mexico_summary'), 'x' => 14, 'y' => 48],
    ]))->take(8);
@endphp

<section
    class="gcm-section"
    id="city-guides"
    data-widget="destination-atlas"
    data-variant="tabs"
>
    <div class="gcm-section-inner">
        <div class="gcm-intro">
            <p class="gcm-kicker">
                {{ __('capell-theme-far-field::sections.cities.kicker') }}
            </p>
            <h2>{{ $heading }}</h2>
            @if ($summary !== '')
                <p class="gcm-lede">{{ $summary }}</p>
            @endif
        </div>

        <div
            class="gcm-atlas-tablist"
            role="tablist"
            aria-label="{{ __('capell-theme-far-field::sections.cities.tablist_aria_label') }}"
        >
            <button
                type="button"
                role="tab"
                id="gcm-atlas-tab-map"
                aria-controls="gcm-atlas-panel-map"
                aria-selected="true"
                tabindex="0"
                class="gcm-atlas-tab"
            >
                {{ __('capell-theme-far-field::sections.cities.tab_map') }}
            </button>
            <button
                type="button"
                role="tab"
                id="gcm-atlas-tab-list"
                aria-controls="gcm-atlas-panel-list"
                aria-selected="false"
                tabindex="-1"
                class="gcm-atlas-tab"
            >
                {{ __('capell-theme-far-field::sections.cities.tab_list') }}
            </button>
        </div>

        <div class="gcm-atlas">
            <div
                id="gcm-atlas-panel-map"
                role="tabpanel"
                aria-labelledby="gcm-atlas-tab-map"
                class="gcm-atlas-map"
            >
                <svg
                    viewBox="0 0 100 60"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                    focusable="false"
                >
                    <rect
                        x="1"
                        y="1"
                        width="98"
                        height="58"
                        rx="3"
                        class="gcm-atlas-frame"
                    />
                    <path
                        d="M6 40 Q 24 20 48 30 T 94 24"
                        class="gcm-atlas-route"
                    />
                </svg>

                @foreach ($items as $item)
                    @php
                        $pinX = (float) data_get($item, 'x', 50);
                        $pinY = (float) data_get($item, 'y', 30);
                        $destinationTitle = (string) data_get($item, 'title', data_get($item, 'name', ''));
                    @endphp

                    <a
                        href="#gcm-destination-tab-{{ $loop->index }}"
                        class="gcm-atlas-pin"
                        style="--gcm-pin-x: {{ $pinX }}%; --gcm-pin-y: {{ $pinY }}%;"
                        data-pin-index="{{ $loop->index }}"
                        aria-label="{{ $destinationTitle }}"
                    >
                        <span aria-hidden="true"></span>
                    </a>
                @endforeach
            </div>

            <ol
                id="gcm-atlas-panel-list"
                role="tabpanel"
                aria-labelledby="gcm-atlas-tab-list"
                class="gcm-atlas-rail"
                hidden
            >
                @foreach ($items as $item)
                    @php
                        $guideTitle = (string) data_get($item, 'title', data_get($item, 'name', ''));
                        $guideUrl = (string) data_get($item, 'url', data_get($item, 'href', ''));
                    @endphp

                    <li
                        class="gcm-atlas-card"
                        id="gcm-destination-tab-{{ $loop->index }}"
                        data-card-index="{{ $loop->index }}"
                    >
                        <span
                            class="gcm-byline-mark"
                            aria-hidden="true"
                        >
                            {{ mb_substr(trim($guideTitle) !== '' ? trim($guideTitle) : 'A', 0, 1) }}
                        </span>
                        <h3>
                            @if ($guideUrl !== '')
                                <a
                                    class="gcm-title-link"
                                    href="{{ $guideUrl }}"
                                >
                                    {{ $guideTitle }}
                                </a>
                            @else
                                {{ $guideTitle }}
                            @endif
                        </h3>
                        <p>{{ data_get($item, 'summary', '') }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
</section>
