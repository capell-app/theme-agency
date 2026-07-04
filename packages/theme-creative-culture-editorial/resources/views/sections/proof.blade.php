@php
    $items = data_get($section, 'items', [
        ['value' => __('capell-theme-creative-culture-editorial::sections.proof.care_value'), 'label' => __('capell-theme-creative-culture-editorial::sections.proof.care_label')],
        ['value' => __('capell-theme-creative-culture-editorial::sections.proof.materials_value'), 'label' => __('capell-theme-creative-culture-editorial::sections.proof.materials_label')],
        ['value' => __('capell-theme-creative-culture-editorial::sections.proof.stores_value'), 'label' => __('capell-theme-creative-culture-editorial::sections.proof.stores_label')],
    ]);
@endphp

<section class="cce-section">
    <div class="cce-section-inner">
        <p class="cce-kicker">
            {{ __('capell-theme-creative-culture-editorial::sections.proof.kicker') }}
        </p>
        @if (filled(data_get($section, 'heading')))
            <h2>{{ data_get($section, 'heading') }}</h2>
        @endif

        @if (filled(data_get($section, 'summary')))
            <p class="cce-lede">{{ data_get($section, 'summary') }}</p>
        @endif

        <div class="cce-grid">
            @foreach ($items as $item)
                <article class="cce-card">
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
