@php
    $items = data_get($section, 'items', [
        ['value' => __('capell-theme-quiet-web-gallery::sections.proof.calm_value'), 'label' => __('capell-theme-quiet-web-gallery::sections.proof.calm_label')],
        ['value' => __('capell-theme-quiet-web-gallery::sections.proof.curated_value'), 'label' => __('capell-theme-quiet-web-gallery::sections.proof.curated_label')],
        ['value' => __('capell-theme-quiet-web-gallery::sections.proof.archive_value'), 'label' => __('capell-theme-quiet-web-gallery::sections.proof.archive_label')],
    ]);
@endphp

<section class="qwg-section qwg-section-field">
    <div class="qwg-section-inner">
        <p class="qwg-kicker">
            {{ __('capell-theme-quiet-web-gallery::sections.proof.kicker') }}
        </p>
        @if (filled(data_get($section, 'heading')))
            <h2>{{ data_get($section, 'heading') }}</h2>
        @endif

        @if (filled(data_get($section, 'summary')))
            <p class="qwg-lede">{{ data_get($section, 'summary') }}</p>
        @endif

        <div class="qwg-grid">
            @foreach ($items as $item)
                <article class="qwg-card">
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
