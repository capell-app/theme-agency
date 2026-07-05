@php
    $items = data_get($section, 'items', [
        ['value' => __('capell-theme-launch-pad::sections.proof.care_value'), 'label' => __('capell-theme-launch-pad::sections.proof.care_label')],
        ['value' => __('capell-theme-launch-pad::sections.proof.materials_value'), 'label' => __('capell-theme-launch-pad::sections.proof.materials_label')],
        ['value' => __('capell-theme-launch-pad::sections.proof.stores_value'), 'label' => __('capell-theme-launch-pad::sections.proof.stores_label')],
    ]);
@endphp

<section
    id="proof"
    class="lga-section"
>
    <div class="lga-section-inner">
        <p class="lga-eyebrow">
            {{ __('capell-theme-launch-pad::sections.proof.kicker') }}
        </p>
        <h2>
            {{ data_get($section, 'heading', __('capell-theme-launch-pad::sections.proof.heading')) }}
        </h2>
        <p class="lga-lede">
            {{ data_get($section, 'summary', __('capell-theme-launch-pad::sections.proof.summary')) }}
        </p>

        <div class="lga-grid">
            @foreach ($items as $item)
                <article class="lga-card">
                    <span class="lga-stat-value">
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
