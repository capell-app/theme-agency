@php
    $items = data_get($section, 'items', [
        ['value' => __('capell-theme-premium-portfolio-collection::sections.proof.care_value'), 'label' => __('capell-theme-premium-portfolio-collection::sections.proof.care_label')],
        ['value' => __('capell-theme-premium-portfolio-collection::sections.proof.materials_value'), 'label' => __('capell-theme-premium-portfolio-collection::sections.proof.materials_label')],
        ['value' => __('capell-theme-premium-portfolio-collection::sections.proof.stores_value'), 'label' => __('capell-theme-premium-portfolio-collection::sections.proof.stores_label')],
    ]);
@endphp

<section class="ppc-section">
    <div class="ppc-section-inner">
        <p class="ppc-kicker">
            {{ __('capell-theme-premium-portfolio-collection::sections.proof.kicker') }}
        </p>
        @if (filled(data_get($section, 'heading')))
            <h2>{{ data_get($section, 'heading') }}</h2>
        @endif

        @if (filled(data_get($section, 'summary')))
            <p class="ppc-lede">{{ data_get($section, 'summary') }}</p>
        @endif

        <div class="ppc-grid">
            @foreach ($items as $item)
                <article class="ppc-card">
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
