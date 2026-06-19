@php
    $items = data_get($section, 'items', [
        ['value' => __('capell-theme-outdoor-mission::sections.proof.repairs_value'), 'label' => __('capell-theme-outdoor-mission::sections.proof.repairs_label')],
        ['value' => __('capell-theme-outdoor-mission::sections.proof.materials_value'), 'label' => __('capell-theme-outdoor-mission::sections.proof.materials_label')],
        ['value' => __('capell-theme-outdoor-mission::sections.proof.campaigns_value'), 'label' => __('capell-theme-outdoor-mission::sections.proof.campaigns_label')],
    ]);
@endphp

<section class="outdoor-section">
    <div class="outdoor-section-inner">
        <p class="outdoor-kicker">
            {{ __('capell-theme-outdoor-mission::sections.proof.kicker') }}
        </p>
        <div class="outdoor-grid">
            @foreach ($items as $item)
                <article class="outdoor-card">
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
