{{--
    Signature widget: destination-atlas (Part 2 §B, far-field; replaces
    destination-grid-with-guides). An inline SVG region map -- pins
    positioned by payload lon/lat percentages, no MapboxGL or client
    geocoding -- paired with an adjacent card rail. Hovering or focusing a
    pin highlights the matching card via a pure-CSS adjacent-sibling
    `:has()` selector (§0.8: modern-CSS enhancement-only); browsers without
    `:has()` support simply keep the static, still fully readable map + rail
    with no broken functionality. Everything is payload-driven; caps at 8
    destinations (well under the §0.3 grid cap of 50).
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
    $variant = (string) data_get($section, 'variant', 'default');
@endphp

<section
    class="gcm-section"
    id="city-guides"
    data-widget="destination-atlas"
    data-variant="{{ $variant }}"
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

        <div class="gcm-atlas">
            <div
                class="gcm-atlas-map"
                role="img"
                aria-label="{{ __('capell-theme-far-field::sections.cities.map_aria_label') }}"
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
                        href="#gcm-destination-{{ $loop->index }}"
                        class="gcm-atlas-pin"
                        style="--gcm-pin-x: {{ $pinX }}%; --gcm-pin-y: {{ $pinY }}%;"
                        data-pin-index="{{ $loop->index }}"
                        aria-label="{{ $destinationTitle }}"
                    >
                        <span aria-hidden="true"></span>
                    </a>
                @endforeach
            </div>

            <ol class="gcm-atlas-rail">
                @foreach ($items as $item)
                    @php
                        $guideTitle = (string) data_get($item, 'title', data_get($item, 'name', ''));
                        $guideUrl = (string) data_get($item, 'url', data_get($item, 'href', ''));
                    @endphp

                    <li
                        class="gcm-atlas-card"
                        id="gcm-destination-{{ $loop->index }}"
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
