@php
    $items = data_get($section, 'items', [
        ['value' => __('capell-theme-premium-product-story::sections.proof.care_value'), 'label' => __('capell-theme-premium-product-story::sections.proof.care_label')],
        ['value' => __('capell-theme-premium-product-story::sections.proof.materials_value'), 'label' => __('capell-theme-premium-product-story::sections.proof.materials_label')],
        ['value' => __('capell-theme-premium-product-story::sections.proof.stores_value'), 'label' => __('capell-theme-premium-product-story::sections.proof.stores_label')],
    ]);
@endphp

<section class="product-section">
    <div class="product-section-inner">
        <p class="product-kicker">
            {{ __('capell-theme-premium-product-story::sections.proof.kicker') }}
        </p>
        <div class="product-grid">
            @foreach ($items as $item)
                <article class="product-card">
                    <h3>
                        {{ data_get($item, 'value', data_get($item, 'title', '')) }}
                    </h3>
                    <p>
                        {{ data_get($item, 'label', data_get($item, 'summary', '')) }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
