@php
    $items = data_get($section, 'items', [
        ['value' => __('capell-theme-one-page-showcase::sections.proof.care_value'), 'label' => __('capell-theme-one-page-showcase::sections.proof.care_label')],
        ['value' => __('capell-theme-one-page-showcase::sections.proof.materials_value'), 'label' => __('capell-theme-one-page-showcase::sections.proof.materials_label')],
        ['value' => __('capell-theme-one-page-showcase::sections.proof.stores_value'), 'label' => __('capell-theme-one-page-showcase::sections.proof.stores_label')],
    ]);
@endphp

<section class="ops-section">
    <div class="ops-section-inner">
        <p class="ops-kicker">
            {{ __('capell-theme-one-page-showcase::sections.proof.kicker') }}
        </p>
        @if (filled(data_get($section, 'heading')))
            <h2>{{ data_get($section, 'heading') }}</h2>
        @endif

        @if (filled(data_get($section, 'summary')))
            <p class="ops-lede">{{ data_get($section, 'summary') }}</p>
        @endif

        <div class="ops-grid">
            @foreach ($items as $item)
                <article class="ops-card">
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
