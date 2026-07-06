@php
    /**
     * material-swatch-studies (Wave 4a signature widget #3, replaces the old
     * product-comparison-table): token-driven swatch cards whose finish/weight
     * render via CSS custom properties rather than bespoke per-swatch markup.
     * Any before/after comparison reuses the shared Wave 2.6 compare-slider.js
     * module (`role="slider"` + arrow keys) instead of new comparison JS —
     * nothing runs unless `[data-compare-slider]` is present, so a swatch
     * without a `before`/`after` pair renders as a plain card.
     */
    $heading = data_get($section, 'heading', __('capell-theme-art-paper::sections.credits.heading'));
    $swatches = collect(data_get($section, 'items', data_get($section, 'stories', [
        ['title' => __('capell-theme-art-paper::sections.credits.lighting_title'), 'summary' => __('capell-theme-art-paper::sections.credits.lighting_summary'), 'meta' => __('capell-theme-art-paper::sections.credits.lighting_meta')],
        ['title' => __('capell-theme-art-paper::sections.credits.materials_title'), 'summary' => __('capell-theme-art-paper::sections.credits.materials_summary'), 'meta' => __('capell-theme-art-paper::sections.credits.materials_meta')],
        ['title' => __('capell-theme-art-paper::sections.credits.books_title'), 'summary' => __('capell-theme-art-paper::sections.credits.books_summary'), 'meta' => __('capell-theme-art-paper::sections.credits.books_meta')],
    ])))->take(50);
@endphp

<section
    id="product-credits"
    class="dlm-section"
    data-widget="material-swatch-studies"
>
    <div class="dlm-section-inner">
        <p class="dlm-kicker">
            {{ __('capell-theme-art-paper::sections.credits.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <div class="dlm-swatch-grid">
            @foreach ($swatches as $swatch)
                <article
                    class="dlm-swatch-card"
                    style="--dlm-swatch-color: {{ data_get($swatch, 'swatchColor', data_get($swatch, 'color', 'var(--dlm-accent)')) }};"
                    data-finish="{{ data_get($swatch, 'finish', 'matte') }}"
                    data-weight="{{ data_get($swatch, 'weight', 'medium') }}"
                >
                    @if (filled(data_get($swatch, 'beforeImage')) && filled(data_get($swatch, 'afterImage')))
                        <div
                            class="dlm-swatch-compare"
                            data-compare-slider
                            data-compare-slider-initial="50"
                        >
                            <img
                                class="dlm-swatch-compare-layer"
                                src="{{ data_get($swatch, 'beforeImage') }}"
                                alt="{{ data_get($swatch, 'title', '') }} — before"
                                loading="lazy"
                                decoding="async"
                            />
                            <img
                                class="dlm-swatch-compare-layer dlm-swatch-compare-layer-after"
                                src="{{ data_get($swatch, 'afterImage') }}"
                                alt="{{ data_get($swatch, 'title', '') }} — after"
                                loading="lazy"
                                decoding="async"
                            />
                            <span
                                class="dlm-swatch-compare-handle"
                                data-compare-slider-handle
                                role="slider"
                                tabindex="0"
                                aria-orientation="horizontal"
                                aria-label="{{ data_get($swatch, 'title', '') }} before and after"
                                aria-valuemin="0"
                                aria-valuemax="100"
                                aria-valuenow="50"
                            ></span>
                        </div>
                    @else
                        <div
                            class="dlm-swatch-tile"
                            aria-hidden="true"
                        ></div>
                    @endif
                    <p class="dlm-meta">
                        {{ data_get($swatch, 'meta', data_get($swatch, 'category', '')) }}
                    </p>
                    <h3>
                        {{ data_get($swatch, 'title', data_get($swatch, 'name', '')) }}
                    </h3>
                    <p>
                        {{ data_get($swatch, 'summary', data_get($swatch, 'description', '')) }}
                    </p>
                    <p class="dlm-swatch-specs">
                        <span
                            >{{ ucfirst((string) data_get($swatch, 'finish', 'matte')) }}</span
                        >
                        <span aria-hidden="true">·</span>
                        <span
                            >{{ ucfirst((string) data_get($swatch, 'weight', 'medium')) }}</span
                        >
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
