@php
    $items = data_get($section, 'items', [
        ['value' => __('capell-theme-character-portfolio-index::sections.proof.care_value'), 'label' => __('capell-theme-character-portfolio-index::sections.proof.care_label')],
        ['value' => __('capell-theme-character-portfolio-index::sections.proof.materials_value'), 'label' => __('capell-theme-character-portfolio-index::sections.proof.materials_label')],
        ['value' => __('capell-theme-character-portfolio-index::sections.proof.stores_value'), 'label' => __('capell-theme-character-portfolio-index::sections.proof.stores_label')],
    ]);
@endphp

<section
    id="proof"
    class="cpi-section"
>
    <div class="cpi-section-inner">
        <p class="cpi-kicker">
            {{ __('capell-theme-character-portfolio-index::sections.proof.kicker') }}
        </p>
        @if (filled(data_get($section, 'heading')))
            <h2>{{ data_get($section, 'heading') }}</h2>
        @endif

        @if (filled(data_get($section, 'summary')))
            <p class="cpi-lede">{{ data_get($section, 'summary') }}</p>
        @endif

        <div class="cpi-grid">
            @foreach ($items as $item)
                <article class="cpi-card">
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
