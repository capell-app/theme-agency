@php
    /**
     * service-area-map-grid — locality coverage grid. Deliberately NO map or
     * geo dependency (§0.8 modern-CSS guardrail spirit extended to "no heavy
     * client library either"): a pure token-styled grid of service-area
     * names/postcodes read straight from the payload.
     *
     * Two variants (theme-bar criterion 3), branched on payload `variant`:
     * "default" (grid of named areas) and "postcode" (grid rows pairing an
     * area name with its postcode/zip prefix, for trades that quote by
     * postcode district rather than town name).
     */
    $heading = $widget->getMeta('heading', __('capell-theme-call-out::sections.service_areas.heading'));
    $summary = $widget->getMeta('summary', __('capell-theme-call-out::sections.service_areas.summary'));
    $variant = $widget->getMeta('variant', 'default');
    $areas = collect($widget->getMeta('areas', []))->take(50);
@endphp

<section
    id="service-area-map-grid"
    class="rco-shell rco-section"
    data-widget="service-area-map-grid"
    data-variant="{{ $variant }}"
>
    <div class="rco-section-inner">
        <h2>{{ $heading }}</h2>
        <p class="rco-section-summary">{{ $summary }}</p>

        <ul class="rco-service-area-grid">
            @foreach ($areas as $area)
                <li class="rco-service-area-card">
                    <span class="rco-service-area-name">
                        {{ data_get($area, 'name', '') }}
                    </span>

                    @if ($variant === 'postcode' && filled(data_get($area, 'postcode')))
                        <span class="rco-service-area-postcode">
                            {{ data_get($area, 'postcode') }}
                        </span>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
</section>
