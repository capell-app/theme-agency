@php
    $items = data_get($section, 'items', [
        ['value' => __('capell-theme-wild-card::sections.proof.care_value'), 'label' => __('capell-theme-wild-card::sections.proof.care_label')],
        ['value' => __('capell-theme-wild-card::sections.proof.materials_value'), 'label' => __('capell-theme-wild-card::sections.proof.materials_label')],
        ['value' => __('capell-theme-wild-card::sections.proof.stores_value'), 'label' => __('capell-theme-wild-card::sections.proof.stores_label')],
    ]);
@endphp

<section class="exd-section">
    <div class="exd-section-inner">
        <p class="exd-kicker">
            {{ __('capell-theme-wild-card::sections.proof.kicker') }}
        </p>
        @if (filled(data_get($section, 'heading')))
            <h2>{{ data_get($section, 'heading') }}</h2>
        @endif

        @if (filled(data_get($section, 'summary')))
            <p class="exd-lede">{{ data_get($section, 'summary') }}</p>
        @endif

        <div class="exd-grid">
            @foreach ($items as $item)
                <article class="exd-card">
                    <div class="exd-card-body">
                        <h3>
                            {{ data_get($item, 'value', data_get($item, 'title', '')) }}
                        </h3>
                        <p>
                            {{ data_get($item, 'label', data_get($item, 'summary', '')) }}
                        </p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
