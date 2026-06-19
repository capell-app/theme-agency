@php
    $items = data_get($section, 'items', [
        ['value' => __('capell-theme-bold-sport-commerce::sections.proof.care_value'), 'label' => __('capell-theme-bold-sport-commerce::sections.proof.care_label')],
        ['value' => __('capell-theme-bold-sport-commerce::sections.proof.materials_value'), 'label' => __('capell-theme-bold-sport-commerce::sections.proof.materials_label')],
        ['value' => __('capell-theme-bold-sport-commerce::sections.proof.stories_value'), 'label' => __('capell-theme-bold-sport-commerce::sections.proof.stories_label')],
    ]);
@endphp

<section class="sport-section">
    <div class="sport-section-inner">
        <p class="sport-kicker">
            {{ __('capell-theme-bold-sport-commerce::sections.proof.kicker') }}
        </p>
        <div class="sport-grid">
            @foreach ($items as $item)
                <article class="sport-card">
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
