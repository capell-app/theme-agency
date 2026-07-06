@php
    /**
     * before-after-comparison — reuses the shared Wave 2.6 `compare-slider.js`
     * module (`role="slider"` + arrow keys) instead of new comparison JS;
     * nothing runs unless `[data-compare-slider]` is present. Mirrors
     * `theme-art-paper`'s `product-credits.blade.php` usage.
     *
     * Two variants (theme-bar criterion 3), branched on payload `variant`:
     * "default" (single large before/after pair) and "gallery" (a grid of
     * several smaller before/after pairs — one job per card).
     */
    $heading = $widget->getMeta('heading', __('capell-theme-call-out::sections.before_after.heading'));
    $summary = $widget->getMeta('summary', __('capell-theme-call-out::sections.before_after.summary'));
    $variant = $widget->getMeta('variant', 'default');
    $jobs = collect($widget->getMeta('jobs', []))->take(20);
@endphp

<section
    id="before-after-comparison"
    class="rco-shell rco-section"
    data-widget="before-after-comparison"
    data-variant="{{ $variant }}"
>
    <div class="rco-section-inner">
        <h2>{{ $heading }}</h2>
        <p class="rco-section-summary">{{ $summary }}</p>

        <div
            class="rco-before-after-grid rco-before-after-grid--{{ $variant }}"
        >
            @foreach ($jobs as $job)
                <figure class="rco-before-after-card">
                    <div
                        class="rco-before-after-compare"
                        data-compare-slider
                        data-compare-slider-initial="50"
                    >
                        <img
                            class="rco-before-after-layer"
                            src="{{ data_get($job, 'beforeImage', '') }}"
                            alt="{{ data_get($job, 'title', '') }} — {{ __('capell-theme-call-out::sections.before_after.before_label') }}"
                            loading="lazy"
                            decoding="async"
                        />
                        <img
                            class="rco-before-after-layer rco-before-after-layer-after"
                            src="{{ data_get($job, 'afterImage', '') }}"
                            alt="{{ data_get($job, 'title', '') }} — {{ __('capell-theme-call-out::sections.before_after.after_label') }}"
                            loading="lazy"
                            decoding="async"
                        />
                        <span
                            class="rco-before-after-handle"
                            data-compare-slider-handle
                            role="slider"
                            tabindex="0"
                            aria-orientation="horizontal"
                            aria-label="{{ data_get($job, 'title', '') }} before and after"
                            aria-valuemin="0"
                            aria-valuemax="100"
                            aria-valuenow="50"
                        ></span>
                    </div>
                    <figcaption>
                        <h3>{{ data_get($job, 'title', '') }}</h3>
                        <p>{{ data_get($job, 'summary', '') }}</p>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
