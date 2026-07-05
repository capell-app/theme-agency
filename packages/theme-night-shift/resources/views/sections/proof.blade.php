@php
    $items = data_get($section, 'items', [
        ['value' => __('capell-theme-night-shift::sections.proof.care_value'), 'label' => __('capell-theme-night-shift::sections.proof.care_label')],
        ['value' => __('capell-theme-night-shift::sections.proof.materials_value'), 'label' => __('capell-theme-night-shift::sections.proof.materials_label')],
        ['value' => __('capell-theme-night-shift::sections.proof.stores_value'), 'label' => __('capell-theme-night-shift::sections.proof.stores_label')],
    ]);
@endphp

<section
    id="proof"
    class="dps-section"
>
    <div class="dps-section-inner">
        <p class="dps-eyebrow">
            {{ __('capell-theme-night-shift::sections.proof.kicker') }}
        </p>
        @if (filled(data_get($section, 'heading')))
            <h2>{{ data_get($section, 'heading') }}</h2>
        @endif

        @if (filled(data_get($section, 'summary')))
            <p class="dps-lede">{{ data_get($section, 'summary') }}</p>
        @endif

        <div
            class="dps-grid"
            style="margin-top: clamp(2rem, 4vw, 3rem)"
        >
            @foreach ($items as $item)
                <article class="dps-card">
                    <span class="dps-stat-value">
                        {{ data_get($item, 'value', data_get($item, 'title', '')) }}
                    </span>
                    <p>
                        {{ data_get($item, 'label', data_get($item, 'summary', '')) }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
