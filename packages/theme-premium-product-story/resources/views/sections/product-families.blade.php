@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-premium-product-story::sections.families.pro_title'), 'summary' => __('capell-theme-premium-product-story::sections.families.pro_summary')],
        ['title' => __('capell-theme-premium-product-story::sections.families.air_title'), 'summary' => __('capell-theme-premium-product-story::sections.families.air_summary')],
        ['title' => __('capell-theme-premium-product-story::sections.families.mini_title'), 'summary' => __('capell-theme-premium-product-story::sections.families.mini_summary')],
    ]);
@endphp

<section class="product-section">
    <div class="product-section-inner product-split">
        <div>
            <p class="product-kicker">
                {{ __('capell-theme-premium-product-story::sections.families.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-premium-product-story::sections.families.heading')) }}
            </h2>
            <p class="product-lede">
                {{ data_get($section, 'summary', __('capell-theme-premium-product-story::sections.families.summary')) }}
            </p>
        </div>
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
