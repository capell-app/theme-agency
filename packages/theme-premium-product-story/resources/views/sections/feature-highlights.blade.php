@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-premium-product-story::sections.highlights.display_title'), 'summary' => __('capell-theme-premium-product-story::sections.highlights.display_summary')],
        ['title' => __('capell-theme-premium-product-story::sections.highlights.performance_title'), 'summary' => __('capell-theme-premium-product-story::sections.highlights.performance_summary')],
        ['title' => __('capell-theme-premium-product-story::sections.highlights.camera_title'), 'summary' => __('capell-theme-premium-product-story::sections.highlights.camera_summary')],
        ['title' => __('capell-theme-premium-product-story::sections.highlights.privacy_title'), 'summary' => __('capell-theme-premium-product-story::sections.highlights.privacy_summary')],
    ]);
@endphp

<section class="product-section">
    <div class="product-section-inner">
        <p class="product-kicker">
            {{ __('capell-theme-premium-product-story::sections.highlights.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-premium-product-story::sections.highlights.heading')) }}
        </h2>
        <div class="product-grid">
            @foreach ($items as $item)
                <article class="product-card">
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>{{ data_get($item, 'summary', '') }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
