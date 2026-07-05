@php
    $items = data_get($section, 'items', [
        ['value' => __('capell-theme-art-paper::sections.proof.care_value'), 'label' => __('capell-theme-art-paper::sections.proof.care_label')],
        ['value' => __('capell-theme-art-paper::sections.proof.materials_value'), 'label' => __('capell-theme-art-paper::sections.proof.materials_label')],
        ['value' => __('capell-theme-art-paper::sections.proof.stores_value'), 'label' => __('capell-theme-art-paper::sections.proof.stores_label')],
    ]);
@endphp

<section class="dlm-section">
    <div class="dlm-section-inner">
        <p class="dlm-kicker">
            {{ __('capell-theme-art-paper::sections.proof.kicker') }}
        </p>
        @if (filled(data_get($section, 'heading')))
            <h2>{{ data_get($section, 'heading') }}</h2>
        @endif

        @if (filled(data_get($section, 'summary')))
            <p class="dlm-lede">{{ data_get($section, 'summary') }}</p>
        @endif

        <div class="dlm-grid">
            @foreach ($items as $item)
                <article class="dlm-card">
                    <span
                        class="dlm-numeral"
                        aria-hidden="true"
                    >
                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                    </span>
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
