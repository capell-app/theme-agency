@php
    $heading = data_get($section, 'heading', __('capell-theme-case-study-platform::sections.proof.heading'));
    $items = data_get($section, 'items', [
        ['value' => __('capell-theme-case-study-platform::sections.proof.case_studies_value'), 'label' => __('capell-theme-case-study-platform::sections.proof.case_studies_label')],
        ['value' => __('capell-theme-case-study-platform::sections.proof.readers_value'), 'label' => __('capell-theme-case-study-platform::sections.proof.readers_label')],
        ['value' => __('capell-theme-case-study-platform::sections.proof.reply_value'), 'label' => __('capell-theme-case-study-platform::sections.proof.reply_label')],
    ]);
@endphp

<section class="csp-section csp-section-field">
    <div class="csp-section-inner">
        <p class="csp-kicker">
            {{ __('capell-theme-case-study-platform::sections.proof.kicker') }}
        </p>
        <h2>{{ $heading }}</h2>

        <div class="csp-proof-grid">
            @foreach ($items as $item)
                <article>
                    <p class="csp-proof-value">
                        {{ data_get($item, 'value', data_get($item, 'title', '')) }}
                    </p>
                    <p class="csp-proof-label">
                        {{ data_get($item, 'label', data_get($item, 'summary', '')) }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
