@php
    $items = data_get($section, 'items', [
        ['value' => __('capell-theme-far-field::sections.proof.reach_value'), 'label' => __('capell-theme-far-field::sections.proof.reach_label')],
        ['value' => __('capell-theme-far-field::sections.proof.desks_value'), 'label' => __('capell-theme-far-field::sections.proof.desks_label')],
        ['value' => __('capell-theme-far-field::sections.proof.daily_value'), 'label' => __('capell-theme-far-field::sections.proof.daily_label')],
    ]);
@endphp

<section class="gcm-section">
    <div class="gcm-section-inner">
        <div class="gcm-intro">
            <p class="gcm-kicker">
                {{ __('capell-theme-far-field::sections.proof.kicker') }}
            </p>
            <h2>
                {{ data_get($section, 'heading', __('capell-theme-far-field::sections.proof.heading')) }}
            </h2>
            @if (data_get($section, 'summary', '') !== '')
                <p class="gcm-lede">{{ data_get($section, 'summary') }}</p>
            @endif
        </div>
        <div class="gcm-stats">
            @foreach ($items as $item)
                <article class="gcm-stat">
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
