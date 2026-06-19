@php
    $items = data_get($section, 'items', [
        ['title' => __('capell-theme-premium-product-story::sections.specs.display_title'), 'summary' => __('capell-theme-premium-product-story::sections.specs.display_summary')],
        ['title' => __('capell-theme-premium-product-story::sections.specs.power_title'), 'summary' => __('capell-theme-premium-product-story::sections.specs.power_summary')],
        ['title' => __('capell-theme-premium-product-story::sections.specs.finish_title'), 'summary' => __('capell-theme-premium-product-story::sections.specs.finish_summary')],
    ]);
@endphp

<section
    class="product-section"
    style="background: var(--product-field)"
>
    <div class="product-section-inner">
        <p class="product-kicker">
            {{ __('capell-theme-premium-product-story::sections.specs.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-premium-product-story::sections.specs.heading')) }}
        </h2>
        <p class="product-lede">
            {{ data_get($section, 'summary', __('capell-theme-premium-product-story::sections.specs.summary')) }}
        </p>
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
