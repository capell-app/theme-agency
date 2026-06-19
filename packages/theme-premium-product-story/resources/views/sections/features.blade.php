@php
    $heading = data_get($section, 'heading', __('capell-theme-premium-product-story::sections.features.heading'));
    $summary = data_get($section, 'summary', __('capell-theme-premium-product-story::sections.features.summary'));
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-premium-product-story::sections.features.pro_title'), 'summary' => __('capell-theme-premium-product-story::sections.features.pro_summary'), 'meta' => __('capell-theme-premium-product-story::sections.features.pro_meta')],
        ['title' => __('capell-theme-premium-product-story::sections.features.air_title'), 'summary' => __('capell-theme-premium-product-story::sections.features.air_summary'), 'meta' => __('capell-theme-premium-product-story::sections.features.air_meta')],
        ['title' => __('capell-theme-premium-product-story::sections.features.mini_title'), 'summary' => __('capell-theme-premium-product-story::sections.features.mini_summary'), 'meta' => __('capell-theme-premium-product-story::sections.features.mini_meta')],
    ]);
@endphp

<section class="product-section">
    <div class="product-section-inner">
        <p class="product-kicker">
            {{ __('capell-theme-premium-product-story::sections.features.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>
        <p class="product-lede">{{ $summary }}</p>

        <div class="product-grid">
            @foreach ($items as $item)
                <article class="product-card product-showcase-card">
                    <h3>
                        {{ data_get($item, 'title', data_get($item, 'name', '')) }}
                    </h3>
                    <p>
                        {{ data_get($item, 'summary', data_get($item, 'description', '')) }}
                    </p>
                    <div class="product-showcase-specs">
                        <span>
                            {{ data_get($item, 'meta', data_get($item, 'category', __('capell-theme-premium-product-story::sections.features.default_meta'))) }}
                        </span>
                        <span>
                            {{ data_get($item, 'care_note', __('capell-theme-premium-product-story::sections.features.care_note')) }}
                        </span>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
