@php
    $items = data_get($section, 'items', [
        ['value' => __('capell-theme-quiet-luxury-retail::sections.proof.care_value'), 'label' => __('capell-theme-quiet-luxury-retail::sections.proof.care_label')],
        ['value' => __('capell-theme-quiet-luxury-retail::sections.proof.materials_value'), 'label' => __('capell-theme-quiet-luxury-retail::sections.proof.materials_label')],
        ['value' => __('capell-theme-quiet-luxury-retail::sections.proof.stores_value'), 'label' => __('capell-theme-quiet-luxury-retail::sections.proof.stores_label')],
    ]);
@endphp

<section class="luxury-section">
    <div class="luxury-section-inner">
        <p class="luxury-kicker">
            {{ __('capell-theme-quiet-luxury-retail::sections.proof.kicker') }}
        </p>
        <div class="luxury-grid">
            @foreach ($items as $item)
                <article class="luxury-card">
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
