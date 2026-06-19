@php
    $items = data_get($section, 'items', [
        ['value' => __('capell-theme-minimal-fashion::sections.proof.care_value'), 'label' => __('capell-theme-minimal-fashion::sections.proof.care_label')],
        ['value' => __('capell-theme-minimal-fashion::sections.proof.materials_value'), 'label' => __('capell-theme-minimal-fashion::sections.proof.materials_label')],
        ['value' => __('capell-theme-minimal-fashion::sections.proof.stores_value'), 'label' => __('capell-theme-minimal-fashion::sections.proof.stores_label')],
    ]);
@endphp

<section class="fashion-section">
    <div class="fashion-section-inner">
        <p class="fashion-kicker">
            {{ __('capell-theme-minimal-fashion::sections.proof.kicker') }}
        </p>
        <div class="fashion-grid">
            @foreach ($items as $item)
                <article class="fashion-card">
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
