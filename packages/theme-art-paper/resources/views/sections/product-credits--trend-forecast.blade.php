{{--
    trend-forecast-infographic (Wave 4a signature widget #4): a colour-swatch
    trend report, selected via the `product-credits` section's
    `trend-forecast` variant. Each row renders a small palette strip via CSS
    custom properties plus a share bar driven entirely by payload numbers
    (never client-computed), keeping the widget deterministic and cache-safe.
--}}

<section
    id="product-credits"
    class="dlm-section"
    data-widget="trend-forecast-infographic"
>
    <div class="dlm-section-inner">
        <p class="dlm-kicker">
            {{ __('capell-theme-art-paper::sections.credits.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-art-paper::sections.credits.heading')) }}
        </h2>
        <ul class="dlm-trend-forecast">
            @foreach (collect(data_get($section, 'items', data_get($section, 'forecasts', [])))->take(50) as $forecast)
                <li class="dlm-trend-forecast-row">
                    <div
                        class="dlm-trend-forecast-palette"
                        aria-hidden="true"
                    >
                        @foreach (collect(data_get($forecast, 'palette', []))->take(6) as $swatchColor)
                            <span
                                style="--dlm-trend-swatch: {{ $swatchColor }};"
                            ></span>
                        @endforeach
                    </div>
                    <div class="dlm-trend-forecast-copy">
                        <h3>
                            {{ data_get($forecast, 'title', data_get($forecast, 'name', '')) }}
                        </h3>
                        <p>{{ data_get($forecast, 'summary', '') }}</p>
                    </div>
                    <div class="dlm-trend-forecast-share">
                        <div
                            class="dlm-trend-forecast-bar"
                            role="img"
                            aria-label="{{ data_get($forecast, 'title', '') }}: {{ max(0, min(100, (int) data_get($forecast, 'share', 0))) }}% of featured palettes this season"
                            style="--dlm-trend-share: {{ max(0, min(100, (int) data_get($forecast, 'share', 0))) }}%;"
                        ></div>
                        <span class="dlm-meta"
                            >{{ max(0, min(100, (int) data_get($forecast, 'share', 0))) }}%</span
                        >
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</section>
